<?php

// ===
// Functions
// ===

function detect_ads_site() {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    // Removing mapicoin.[fr|com]
    $host = str_replace(
        ['.mapicoin.fr', '.mapicoin.com'],
        '',
        $host
    );

    switch ($host) {
        case 'craigslist.dev':
        case 'craigslist':
            return SITE_CRAIGSLIST;
            break;
        case 'gumtree.dev':
        case 'gumtree':
            return SITE_GUMTREE;
            break;
        default:
            return SITE_LEBONCOIN;
            break;
    }
}



function set_location_in_cache($location = '', $coords = array()) {
    global $_MYSQLI;
    /* Crée une requête préparée */
    if (!($stmt = $_MYSQLI->prepare("
        INSERT INTO geocode (id,location,lat,lng)
            VALUES (?,?,?,?)
        ON DUPLICATE KEY
            UPDATE lat=?, lng=?"
        ))) {
        return false;
    }
    /* Lecture des marqueurs */
    $id = md5($location);
    $stmt->bind_param(
        "ssssdd",
        $id,
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
    global $_MYSQLI;
    /* Crée une requête préparée */
    if (!($stmt = $_MYSQLI->prepare("SELECT lat,lng FROM geocode WHERE id=? LIMIT 1"))) {
        return false;
    }
    /* Lecture des marqueurs */
    $id = md5($location);
    $stmt->bind_param("s", $id);
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
 * Convert a leboncoin date to timestamp
 */
function convert_date_to_timestamp($date) {
    $tmp = explode(',', $date);
    if (!isset($tmp[1])) {
        return false;
    }
    $jour  = trim(strtolower($tmp[0]));
    $heure = trim($tmp[1]);
    if ($jour == "aujourd'hui") {
        $jour = date('d F');
    } elseif ($jour == 'hier') {
        $jour = date('d F', strtotime('-1 day'));
    }

    // On converti les dates leboncoin en EN
    $replaces = [
        'janvier'   => 'january',
        'février'   => 'february',
        'mars'      => 'march',
        'avril'     => 'april',
        'mai'       => 'may',
        'juin'      => 'june',
        'juillet'   => 'july',
        'août'      => 'august',
        'septembre' => 'september',
        'octobre'   => 'october',
        'novembre'  => 'november',
        'décembre'  => 'december',
    ];

    $date = sprintf('%s %d %s', $jour, date('Y'), $heure);
    $date = str_ireplace(array_keys($replaces), array_values($replaces), $date);

    return strtotime($date);
}

/**
 * Get lat & lng from places (bulk /!\) using mapquest
 */
function convert_places_to_latlng($places = array()) {
    global $_CONFIG;
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
            $tmp    .= ',+France';
            $query  = sprintf($geocoder, $tmp);
            $result = json_decode(file_get_contents($query));
            if (count($result->results) == 0) {
                $lat = 46.5002839;
                $lng = 2.7915620;
            } else {
                $json   = $result->results[0];
                $lat    = $json->geometry->location->lat;
                $lng    = $json->geometry->location->lng;
                // set_location_in_cache(
                //     $place,
                //     [$lat, $lng]
                // );
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


