<?php
error_reporting(E_ALL);
ini_set('display_errors', 'off');
ini_set('log_errors', '1');
ini_set('error_log', 'log/php_errors.log');

if (! extension_loaded('openssl')) {
    die('You must enable the openssl extension.');
}

session_cache_limiter(false);
session_start();

if (preg_match('/\.(?:png|jpg|jpeg|gif|txt|css|js)$/', $_SERVER["REQUEST_URI"]))
    return false; // serve the requested resource as-is.
else {
    $app = require __DIR__ . '/../src/app.php';
    $app->run();
}
