<?php

require_once '../inc/config.inc.php';

set_time_limit(60);
libxml_use_internal_errors(true);
// Pour la gestion des dates FR
// TODO : voir pour mettre dans le switch ? ou en fonction de l'utilisateur ?
date_default_timezone_set('Europe/Paris');

ob_start();

// ===
// Retrieve URL
// ===
$_URL = $_POST['u'] ?? $_GET['u'] ?? null;
$_URL = trim($_URL);

switch ($_SITE) {

    case SITE_CRAIGSLIST:
        require_once '../inc/crawlers/CraigslistCrawler.class.php';
        $crawler = new CraigslistCrawler($_URL);
        break;

    case SITE_GUMTREE:
        require_once '../inc/crawlers/GumtreeCrawler.class.php';
        $crawler = new GumtreeCrawler($_URL);
        break;

    // leboncoin
    default:
        require_once '../inc/crawlers/LeboncoinCrawler.class.php';
        $crawler = new LeboncoinCrawler($_URL);
        break;
}


// ===
// Default return
// ===
$return = [
    'status'  => false,
    'message' => 'ko',
    'title'   => "Liste des résultats",
    'datas'   => null,
];

if (!$crawler) {
    $return['message'] = sprintf(
        "L'URL %s ne semble pas correspondre à une URL de liste de résultats du site d'annonces %s",
        $_URL,
        ucfirst($_SITE)
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

    $crawler->fetchURLContent($i);

    // Load DOM
    // $dom      = new DOMDocument();
    // $dom->loadHTML($html);
    // $domXpath = new DomXPath($dom);

    // Fetch main title once
    if (!$title) {
        $title           = $crawler->fetchMainTitle();
        $title           = explode('-', $title);
        $title           = trim($title[0]);
        $return['title'] = $title;

    }

    $annonces = $crawler->getAds();

    if (!$annonces || $annonces->length == 0) {
        break;
    }
    foreach ($annonces as $j => $e) {
        // Key is very important as we are mixing result pages ($i, $j)
        $key         = ($j + 1) * $i;
        $annonce     = $crawler->getAdInfo($e);
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

