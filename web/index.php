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
setcookie("PHPSESSID", session_id(), 0, "/", "", FALSE, TRUE);

$timeout = 1200; // Number of seconds until it times out.
     
// Check if the timeout field exists.
if(isset($_SESSION['timeout'])) {
    // See if the number of seconds since the last
    // visit is larger than the timeout period.
    $duration = time() - (int)$_SESSION['timeout'];
    if($duration > $timeout) {
        unset($_SESSION['user']);
        }
    }
     
// Update the timout field with the current time.
$_SESSION['timeout'] = time();

if (preg_match('/\.(?:png|jpg|jpeg|gif|txt|css|js)$/', $_SERVER["REQUEST_URI"]))
    return false; // serve the requested resource as-is.
else {
    $app = require __DIR__ . '/../src/app.php';
    $app->run();
}
