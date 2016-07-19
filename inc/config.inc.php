<?php

// ===
// Configuration
// ===

define('DEBUG', false);

define('MAX_PAGES_RETRIEVE',  2);
define('SLEEP_BETWEEN_PAGES', 1);
define('USLEEP_BETWEEN_API_CALL', 200000); // 200ms

define('VERSION', file_get_contents(__DIR__.'/../VERSION'));

define('SITE_LEBONCOIN',  'leboncoin');
define('SITE_GUMTREE',    'gumtree');
define('SITE_CRAIGSLIST', 'craigslist');
define('SITE_KIJIJI',     'kijiji');

$_SITES = [
    SITE_LEBONCOIN => [
        'country'          => 'France',
        // without .mapicoin.[fr|com]
        // default value anyway...
        'mapicoin-domains' => [],
    ],
    SITE_KIJIJI => [
        'country'          => 'Canada',
        // without .mapicoin.[fr|com]
        'mapicoin-domains' => [
            'kijiji',
            'kijiji.dev',
            'kijiji.dev2',
        ],
    ],
];


// Dislay debug ?
if (DEBUG) {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}


// ===
// Parameters
// ===
$parameters = __DIR__.'/parameters.json';
if (!file_exists($parameters)) {
    die("Couldn't find parameters.json");
}
if (!($_CONFIG = json_decode(file_get_contents($parameters)))) {
    die("Couldn't read/decode parameters.json");
}
$_MYSQLI = mysqli_connect(
    $_CONFIG->mysql->host,
    $_CONFIG->mysql->login,
    $_CONFIG->mysql->password,
    $_CONFIG->mysql->database,
    $_CONFIG->mysql->port
);
$_MYSQLI->set_charset($_CONFIG->mysql->charset);
// ===
// Requires
// ===
require_once 'functions.inc.php';

$_SITE = detect_ads_site();
