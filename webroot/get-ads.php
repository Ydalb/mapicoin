<?php

require_once '../inc/config.inc.php';

// Pour la gestion des dates FR
date_default_timezone_set('Europe/Paris');
set_time_limit(60);
libxml_use_internal_errors(true);

ob_start();


// ===
// Default return
// ===
$return = [
    'status'  => false,
    'message' => 'ko',
    'title'   => "Liste des résultats",
    'datas'   => null,
];

// ===
// Check URL
// ===
$_URL = $_POST['u'] ?? $_GET['u'] ?? null;
$_URL = trim($_URL);
if (!preg_match('#^https?://#i', $_URL)) {
    $_URL = 'https://'.$_URL;
}
if (!preg_match('#^https?://www\.leboncoin\.fr/.+#i', $_URL)) {
    $return['message'] = sprintf(
        "L'URL %s est incorrecte.

Veuillez renseigner une URL de recherche leboncoin.

Exemple : https://www.leboncoin.fr/voitures/offres/bretagne/bonnes_affaires/",
        $_URL
    );
    exit(json_encode($return));
}

// ===
// Let's browse pages !
// ===
$datas = [];
$title = null;
for ($i = 1; $i <= MAX_PAGES_RETRIEVE; ++$i) {

    $places = [];

    // Change pagination
    $url      = replace_get_parameter($_URL, 'o', $i);
    $html     = fetch_url_content($url);

    // Load DOM
    $dom      = new DOMDocument();
    $dom->loadHTML($html);
    $domXpath = new DomXPath($dom);

    // Fetch main title once
    if (!$title) {
        $title           = fetch_page_title($domXpath);
        $title           = explode('-', $title);
        $title           = trim($title[0]);
        $return['title'] = $title;
    }

    $annonces = fetch_annonces($domXpath);

    if ($annonces->length == 0) {
        break;
    }
    foreach ($annonces as $j => $e) {
        // Key is very important as we are mixing result pages ($i, $j)
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
        $datas[$k]['latlng'] = $ll;
    }

    // sleep(SLEEP_BETWEEN_PAGES);

}


// ===
// /END of script
// ===
$return['status']  = true;
$return['message'] = 'ok';
$return['datas']   = $datas;

ob_clean();
header('Content-Type: application/json; charset=utf-8');

if (!($encode = json_encode($return))) {
    exit(json_encode([
        'status' => false,
        'message' => json_last_error_msg(),
    ]));
}

exit($encode);

