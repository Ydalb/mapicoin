<?php

// ===
// Configuration
// ===

define('DEBUG', false);

define('MAX_PAGES_RETRIEVE',  2);
define('SLEEP_BETWEEN_PAGES', 0);
define('USLEEP_BETWEEN_API_CALL', 200000); // 200ms

define('VERSION', file_get_contents(__DIR__.'/../VERSION'));

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
if (!($_CONFIG = json_decode(file_get_contents($parameters)))) {
    die("Couldn't read/decode parameters.json");
}
define('MYSQL_HOST',     $_CONFIG->mysql->host);
define('MYSQL_PORT',     $_CONFIG->mysql->port);
define('MYSQL_LOGIN',    $_CONFIG->mysql->login);
define('MYSQL_PASSWORD', $_CONFIG->mysql->password);
define('MYSQL_DATABASE', $_CONFIG->mysql->database);

$_MYSQLI = mysqli_connect(MYSQL_HOST, MYSQL_LOGIN, MYSQL_PASSWORD, MYSQL_DATABASE);
$_MYSQLI->set_charset("utf8");
// ===
// Requires
// ===
require_once 'functions.inc.php';
