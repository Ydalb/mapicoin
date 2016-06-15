<?php

// ===
// Configuration
// ===

define('DEBUG',               false);

define('MAX_PAGES_RETRIEVE',  1);
define('SLEEP_BETWEEN_PAGES', 1);


// Dislay debug ?
if (DEBUG || in_array($_SERVER['SERVER_ADDR'], array('127.0.0.1', '192.168.99.100'))) {
	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
}


// ===
// Requires
// ===
require_once 'functions.inc.php';