<?php

require_once 'inc/config.inc.php';

libxml_use_internal_errors(true);

ob_start();


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
for ($i = 1; $i <= MAX_PAGES_RETRIEVE; ++$i) {

    $places = [];

    // Change pagination
    $url      = replace_get_parameter($_URL, 'o', $i);
    $html     = fetch_url_content($url);

    // Load DOM
    $dom      = new DOMDocument();
    $dom->loadHTML($html);
    $domXpath = new DomXPath($dom);

    $annonces = fetch_annonces($domXpath);

    if ($annonces->length == 0) {
        break;
    }
    foreach ($annonces as $j => $e) {
        $key = ($j + 1) * $i;
        $annonce     = fetch_annonce_info($domXpath, $e);
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

    sleep(SLEEP_BETWEEN_PAGES);

}


// ===
// /END of script
// ===
$return['status']  = true;
$return['message'] = 'ok';
$return['datas']   = $datas;
ob_clean();


header('Content-Type: application/json; charset=utf-8');
exit(json_encode($return));

