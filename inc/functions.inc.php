<?php

// ===
// Functions
// ===

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
 * Return annonce info from DOMElement (using xpath)
 */
function fetch_annonce_info($domXpath, $domElement) {
    $return = [
        'url'           => null,
        'title'         => null,
        'picture'       => null,
        'picture_count' => null,
        'location'      => null,
        'price'         => null,
        'date'          => null,
        'pro'           => null,
    ];
    // url
    $tmp = $domXpath->query(
        './/a[@class="list_item clearfix trackable"]/@href',
        $domElement
    );
    $return['url'] = 'https:'.$tmp->item(0)->nodeValue;

    // title
    $tmp = $domXpath->query(
        './/h2[@class="item_title"]/text()',
        $domElement
    );
    $return['title'] = trim($tmp->item(0)->nodeValue);

    // picture
    $tmp = $domXpath->query(
        './/span[@class="lazyload"]/@data-imgsrc',
        $domElement
    );
    $return['picture'] = 'https:'.trim(@$tmp->item(0)->nodeValue ?? '//static.leboncoin.fr/img/no-picture.png');

    // picture_count
    $tmp = $domXpath->query(
        './/span[@class="item_imageNumber"]/span/text()',
        $domElement
    );
    $return['picture_count'] = trim(@$tmp->item(0)->nodeValue ?? 0);

    // pro
    $tmp = $domXpath->query(
        './/span[@class="ispro"]/text()',
        $domElement
    );
    $tmp = trim(@$tmp->item(0)->nodeValue ?? null);
    $return['pro'] = preg_replace('#\s+#i', ' ', $tmp);

    // location
    $tmp = $domXpath->query(
        '(.//p[@class="item_supp"])[2]/text()',
        $domElement
    );
    $tmp = trim($tmp->item(0)->nodeValue);
    $return['location'] = preg_replace('#\s+#i', ' ', $tmp);

    // price
    $tmp = $domXpath->query(
        './/h3[@class="item_price"]/text()',
        $domElement
    );
    $return['price'] = trim(@$tmp->item(0)->nodeValue ?? '');

    // date
    $tmp = $domXpath->query(
        './/aside[@class="item_absolute"]/p[@class="item_supp"]/text()',
        $domElement
    );
    $return['date'] = trim($tmp->item(0)->nodeValue);

    return $return;
}

/**
 * Return DOMElements of annonces
 */
function fetch_annonces($domXpath) {
    return $domXpath->query(
        '//section[@class="tabsContent block-white dontSwitch"]/ul/li'
    );
}

/**
 * Return DOMElements of title
 */
function fetch_page_title($domXpath) {
    return $domXpath->query(
        '//head/title/text()'
    )->item(0)->nodeValue;
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
 * Return HTML content of specific url
 */
function fetch_url_content($url) {
    $headers = array(
      'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.12) Gecko/20101026 Firefox/3.6.12',
      'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
      'Accept-Language: en-us,en;q=0.5',
      //'Accept-Encoding: gzip,deflate',
      'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7',
      'Keep-Alive: 115',
      'Connection: keep-alive',
    );

    $ch  = curl_init();
    curl_setopt($ch, CURLOPT_VERBOSE,        false);
    curl_setopt($ch, CURLOPT_URL,            $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $data = curl_exec($ch);

    // Conversion
    $data = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($data));



    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code != 200) {
        printf("HTTP CODE != 200 : Aborting\n");
        return false;
    }

    return $data;
}

/**
 * Replace a specific GET parameter from a URL
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

