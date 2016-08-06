<?php

// ===
// Functions
// ===

function detect_ads_site() {
    global $_SITES;
    $host = $_SERVER['HTTP_HOST'] ?? '';
    // Removing mapicoin.[fr|com]
    $host = str_replace(
        ['.mapicoin.fr', '.mapicoin.com'],
        '',
        $host
    );

    foreach ($_SITES as $site => $config) {
        $domains = $config['mapicoin-domains'] ?? [];
        foreach ($config['mapicoin-domains'] as $d) {
            if ($host == $d) {
                return $site;
            }
        }
    }
    return SITE_LEBONCOIN;
}


function stats_add_search($search) {
    global $_MYSQLI, $_SITE;
    /* Crée une requête préparée */
    if (!($stmt = $_MYSQLI->prepare("
        INSERT INTO `search` (id,site,search,count)
            VALUES (?,?,?,1)
        ON DUPLICATE KEY UPDATE
            count = (count + 1)"
        ))) {
        return false;
    }
    /* Lecture des marqueurs */
    $id = md5($search);
    $stmt->bind_param(
        "sss",
        $id,
        $_SITE,
        $search
    );
    /* Exécution de la requête */
    $stmt->execute();
    /* Fermeture du traitement */
    $stmt->close();
    return true;
}


function stats_get_searches() {
    global $_MYSQLI, $_SITE;
    /* Crée une requête préparée */
    if (!($stmt = $_MYSQLI->prepare("
        SELECT search,count,updated
        FROM search
        WHERE site = ?
        ORDER BY updated DESC"))) {
        return false;
    }
    $stmt->bind_param("s", $_SITE);
    $stmt->execute();
    $return = [];
    $stmt->bind_result($search,$count,$updated);
    /* Lecture des valeurs */
    while ($stmt->fetch()) {
        $return[] = [
            'search'  => $search,
            'count'   => $count,
            'updated' => $updated,
        ];
    }
    /* Fermeture du traitement */
    $stmt->close();

    return $return;
}


function set_location_in_cache($location = '', $coords = array()) {
    global $_MYSQLI, $_SITE;
    /* Crée une requête préparée */
    if (!($stmt = $_MYSQLI->prepare("
        INSERT INTO geocode (id,site,location,lat,lng)
            VALUES (?,?,?,?,?)
        ON DUPLICATE KEY
            UPDATE lat=?, lng=?"
        ))) {
        return false;
    }
    /* Lecture des marqueurs */
    $id = md5($location);
    $stmt->bind_param(
        "sssssdd",
        $id,
        $_SITE,
        $location,
        $coords[0],
        $coords[1],
        $coords[0],
        $coords[1]
    );
    /* Exécution de la requête */
    $stmt->execute();
    /* Fermeture du traitement */
    $stmt->close();

    return true;
}

function get_location_from_cache($location = '') {
    global $_MYSQLI, $_SITE;
    /* Crée une requête préparée */
    if (!($stmt = $_MYSQLI->prepare("SELECT lat,lng FROM geocode WHERE id=? AND site=? LIMIT 1"))) {
        return false;
    }
    /* Lecture des marqueurs */
    $id = md5($location);
    $stmt->bind_param("ss", $id, $_SITE);
    /* Exécution de la requête */
    $stmt->execute();
    /* Lecture des variables résultantes */
    $stmt->bind_result($lat,$lng);
    /* Récupération des valeurs */
    if (!$stmt->fetch()) {
        return null;
    }
    /* Fermeture du traitement */
    $stmt->close();

    return [$lat,$lng];
}

function get_location_from_bdd($location = '') {
    global $_MYSQLI;

    $tmp = explode('/', $location);
    if (!isset($tmp[1])) {
        return false;
    }

    $ville       = trim($tmp[0]);
    $departement = trim($tmp[1]);

    /* Crée une requête préparée */
    if (!($stmt = $_MYSQLI->prepare("
        SELECT latitude, longitude
        FROM city c
        JOIN departement d ON c.departement_code = d.departement_code
        WHERE
            (c.name=? OR CONCAT(c.article, ' ', c.name) = ? OR CONCAT(c.article, c.name) = ?)
            AND d.name = ?
        LIMIT 1"))) {
        error_log($_MYSQLI->error);
        return false;
    }
    // error_log(sprintf("
    //     SELECT latitude, longitude
    //     FROM city c
    //     JOIN departement d ON c.departement_code = d.departement_code
    //     WHERE
    //         (c.name=%s OR CONCAT(c.article, ' ', c.name) = %s)
    //         AND d.name = %s
    //     LIMIT 1", $ville, $ville, $departement));
    /* Lecture des marqueurs */
    $stmt->bind_param("ssss", $ville, $ville, $ville, $departement);
    /* Exécution de la requête */
    $stmt->execute();
    /* Lecture des variables résultantes */
    $stmt->bind_result($lat,$lng);
    /* Récupération des valeurs */
    if (!$stmt->fetch()) {
        return null;
    }
    /* Fermeture du traitement */
    $stmt->close();

    return [$lat,$lng];
}


/**
 * Get lat & lng from places (bulk /!\) using mapquest
 */
function convert_places_to_latlng($places = array()) {
    global $_CONFIG, $_SITES, $_SITE;
    $geocoder = sprintf(
        'https://maps.googleapis.com/maps/api/geocode/json?key=%s&address=%%s',
        $_CONFIG->api->google_server_key
    );

    $return = [];
    foreach ($places as $i => $place) {

        $cache = get_location_from_cache($place);

        if (!$cache) {
            $cache = get_location_from_bdd($place);
        }

        if (!$cache) {
            $tmp    = preg_replace("/[^\s\p{L}0-9]+/u", "", $place);
            $tmp    = str_replace(' / ', ', ', $tmp);
            $tmp    = preg_replace('/\s+/', '+', $tmp);
            $tmp    .= ',+'.$_SITES[$_SITE]['country'];
            $query  = sprintf($geocoder, $tmp);
            $result = json_decode(file_get_contents($query));
            if (count($result->results) == 0) {
                $lat = 46.5002839;
                $lng = 2.7915620;
            } else {
                $json   = $result->results[0];
                $lat    = $json->geometry->location->lat;
                $lng    = $json->geometry->location->lng;
                set_location_in_cache(
                    $place,
                    [$lat, $lng]
                );
                error_log("NEW LOCATION: ".$place);
            }
            // Don't overload google !
            usleep(USLEEP_BETWEEN_API_CALL);
        } else {
            $lat = $cache[0];
            $lng = $cache[1];
            // error_log("LOCATION FROM CACHE: ".$place);
        }
        $return[$i] = [
            'lat' => $lat,
            'lng' => $lng
        ];
    }

    return $return;
}

/**
 * Calcul la moyenne en ignorant $ignore pourcent(s) de valeur trop haute et trop basse
 */
function get_average_price(array $data, $ignore = 10) {
    $count = count($data);
    if ($count == 0) {
        return 0;
    }
    if ($count == 1) {
        $tmp = array_pop($data);
        // error_log(print_r($tmp, 1));
        return round($tmp['price_raw'], 0);
    }
    // Moyenne
    $moyenne = $count2 = 0;
    $offset  = round($count * $ignore / 100, 0);
    foreach ($data as $i => $e) {
        if (!isset($e['price_raw']) || $e['price_raw'] === '') {
            continue;
        }
        if ($i < $offset || $i > ($count - $offset)) {
            continue;
        }
        $moyenne += $e['price_raw'];
        ++$count2;
    }
    return round($moyenne / $count2, 0);
}


/**
 * Replace a specific GET parameter from a given URL
 */
function replace_get_parameter($url, $parameter, $value) {
    // parse the url
    $pathInfo        = parse_url($url);
    $queryString     = $pathInfo['query'] ?? '';
    // convert the query parameters to an array
    parse_str($queryString, $queryArray);
    // add the new query parameter into the array
    $queryArray[$parameter] = $value;
    // build the new query string
    $newQueryStr     = http_build_query($queryArray);
    return sprintf(
        '%s://%s%s?%s',
        $pathInfo['scheme'],
        $pathInfo['host'],
        $pathInfo['path'],
        $newQueryStr
    );
}

function time_elapsed_string($ptime)
{
    $etime = time() - $ptime;

    if ($etime < 1) {
        return "À l'instant";
    }

    $a = array( 365 * 24 * 60 * 60  =>  'année',
                 30 * 24 * 60 * 60  =>  'mois',
                      24 * 60 * 60  =>  'jour',
                           60 * 60  =>  'heure',
                                60  =>  'minute',
                                 1  =>  'seconde'
                );
    $a_plural = array( 'année'   => 'années',
                       'mois'  => 'months',
                       'jour'    => 'jours',
                       'heure'   => 'heures',
                       'minute' => 'minutes',
                       'seconde' => 'secondes'
                );

    foreach ($a as $secs => $str) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return "Il y a ".$r . ' ' . ($r > 1 ? $a_plural[$str] : $str);
        }
    }
}


