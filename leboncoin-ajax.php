<?php

// ini_set('display_errors', 0);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
libxml_use_internal_errors(true);

header('Content-Type: application/json; charset=utf-8');

// ===
// Some vars
// ===
$_MAX_PAGES    = 3;
$_SLEEP        = 1;


// ===
// Default return
// ===
$return = [
    'status'  => false,
    'message' => 'ko',
    'datas'   => null,
];

// ===
// Check URL
// ===
$_URL = $_POST['u'] ?? $_GET['u'] ?? null;
if (!$_URL || !preg_match('#https://www\.leboncoin\.fr/.+#i', $_URL)) {
    $return['message'] = "Veuillez renseigner une URL de recherche leboncoin";
    exit(json_encode($return));
}

// ===
// Let's browse pages !
// ===
$datas = [];
for ($i = 1; $i <= $_MAX_PAGES; ++$i) {

    $places = [];

    // Change pagination
    $url      = replace_get_parameter($_URL, 'o', $i);
    $html     = fetch_url_content($url);

    // Load DOM
    $dom   = new DOMDocument();
    $dom->loadHTML($html);
    $xpath = new DomXPath($dom);

    $annonces = fetch_annonces($xpath);

    if ($annonces->length == 0) {
        break;
    }
    foreach ($annonces as $j => $e) {
        $key = ($j + 1) * $i;
        $annonce     = fetch_annonce_info($xpath, $e);
        $datas[$key] = $annonce;
        if ($annonce['location']) {
            $places[$key] = $annonce['location'];
        }
    }

    // ===
    // Fetch lat & lng
    // ===
    $latlng = convert_places_to_latlng($places);
    if (!$latlng) {
        $return['message'] = "Impossible de récupérer les coordonnées GPS des annonces.";
        exit(json_encode($return));
    }
    foreach ($latlng as $k => $ll) {
        $key                   = ($k + 1) * $i;
        $datas[$key]['latlng'] = $ll;
    }

    sleep($_SLEEP);

}


// ===
// /END of script
// ===
$return['status']  = true;
$return['message'] = 'ok';
$return['datas']   = $datas;
exit(json_encode($return));




// ===
// Functions
// ===


/**
 * Return annonce info from DOMElement (using xpath)
 */
function fetch_annonce_info($xpath, $element) {
    $return = [
        'url'      => null,
        'title'    => null,
        'picture'  => null,
        'location' => null,
        'price'    => null,
        'date'     => null,
        'pro'      => null,
    ];
    // url
    $tmp = $xpath->query('.//a[@class="list_item clearfix trackable"]/@href', $element);
    $return['url'] = 'https:'.$tmp->item(0)->nodeValue;

    // title
    $tmp = $xpath->query('.//h2[@class="item_title"]/text()', $element);
    $return['title'] = trim($tmp->item(0)->nodeValue);

    // picture
    $tmp = $xpath->query('.//span[@class="lazyload"]/@data-imgsrc', $element);
    $return['picture'] = 'https:'.trim(@$tmp->item(0)->nodeValue ?? '//static.leboncoin.fr/img/no-picture.png');

    // pro
    $tmp = $xpath->query('.//span[@class="ispro"]/text()', $element);
    $tmp = trim(@$tmp->item(0)->nodeValue ?? null);
    $return['pro'] = preg_replace('#\s+#i', ' ', $tmp);

    // location
    $tmp = $xpath->query('(.//p[@class="item_supp"])[2]/text()', $element);
    $tmp = trim($tmp->item(0)->nodeValue);
    $return['location'] = preg_replace('#\s+#i', ' ', $tmp);

    // price
    $tmp = $xpath->query('.//h3[@class="item_price"]/text()', $element);
    $return['price'] = trim(@$tmp->item(0)->nodeValue ?? '');

    // date
    $tmp = $xpath->query('.//aside[@class="item_absolute"]/p[@class="item_supp"]/text()', $element);
    $return['date'] = trim($tmp->item(0)->nodeValue);

    return $return;
}

/**
 * Return DOMElements of annonces
 */
function fetch_annonces($xpath) {
    $xpathMain = '//section[@class="tabsContent block-white dontSwitch"]/ul/li';
    return $xpath->query($xpathMain);
}


/**
 * Get lat & lng from places (bulk /!\) using mapquest
 */
function convert_places_to_latlng($places = array()) {
    $mapquest_key = 'uHGLveStgjQ2A1mstajUJGYUlEpkJ6B2';
    $base_url     = sprintf(
        'http://www.mapquestapi.com/geocoding/v1/batch?key=%s&thumbMaps=false&maxResults=40',
        $mapquest_key
    );

    // Add location
    foreach ($places as $place) {
        $base_url .= '&location='.urlencode($place.' (FR)');
    }

    $tmp = fetch_url_content($base_url);
    if (!($json = json_decode($tmp, true))) {
        return false;
    }
    if (!isset($json['info']['statuscode']) || $json['info']['statuscode'] != 0) {
        return false;
    }
    if (!isset($json['results']) || count($json['results']) == 0) {
        return false;
    }

    $return = [];
    foreach ($json['results'] as $i => $r) {
        $return[$i] = $r['locations'][0]['latLng'];
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
    curl_setopt($ch, CURLOPT_VERBOSE,        true);
    curl_setopt($ch, CURLOPT_URL,            $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_HTTPHEADER,     $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $data = curl_exec($ch);
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

