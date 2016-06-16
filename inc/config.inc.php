<?php

// ===
// Configuration
// ===

define('DEBUG',               false);

define('MAX_PAGES_RETRIEVE',  3);
define('SLEEP_BETWEEN_PAGES', 1);

// Dislay debug ?
if (DEBUG || in_array($_SERVER['SERVER_ADDR'], array('127.0.0.1', '192.168.99.100'))) {
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
if (!($json = json_decode(file_get_contents($parameters)))) {
    die("Couldn't read/decode parameters.json");
}
define('MYSQL_HOST',     $json->mysql->host);
define('MYSQL_PORT',     $json->mysql->port);
define('MYSQL_LOGIN',    $json->mysql->login);
define('MYSQL_PASSWORD', $json->mysql->password);
define('MYSQL_DATABASE', $json->mysql->database);

// $_MYSQLI = mysqli_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PASSWORD, MYSQL_DATABASE);
$_CACHE  = true;
// Si on n'a pas de connexion mysql, tant pis, pas de cache
if (!$_MYSQLI) {
    $_CACHE = false;
}


// ===
// Requires
// ===
require_once 'functions.inc.php';