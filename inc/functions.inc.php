<?php

// ===
// Functions
// ===


/**
 * Return annonce info from DOMElement (using xpath)
 */
function fetch_annonce_info($domXpath, $domElement) {
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

