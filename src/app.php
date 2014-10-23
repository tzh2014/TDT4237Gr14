<?php
require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Oslo');

$app = new \Slim\Slim([
    'templates.path' => __DIR__.'/webapp/templates/',
    'debug' => false,
	'log.enabled' => true,
	'log.level' => \Slim\Log::DEBUG,
	'log.writer' => new tdt4237\webapp\LogWriter(),
    'view' => new \Slim\Views\Twig()
]);

$view = $app->view();
$view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
);

try {
    // Create (connect to) SQLite database in file
    $app->db = new PDO('sqlite:app.db');
    // Set errormode to exceptions
    $app->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $app->log->critical($e->getMessage());
    exit();
}

$ns ='tdt4237\\webapp\\controllers\\'; 

// Home page at http://localhost/
$app->get('/', $ns . 'IndexController:index');

// Login form
$app->get('/login', $ns . 'LoginController:index');
$app->post('/login', $ns . 'LoginController:login');

// Forgotten Password form
$app->get('/forgot', $ns . 'ForgottenPassController:index');
$app->post('/forgot', $ns . 'ForgottenPassController:sendEmail');
$app->post('/forgot/change', $ns . 'ForgottenPassController:changePass');
$app->get('/forgot/:hash', $ns . 'ForgottenPassController:verification');
$app->post('/forgot/:hash', $ns . 'ForgottenPassController:setNewPass');

// New user
$app->get('/user/new', $ns . 'UserController:index')->name('newuser');
$app->post('/user/new', $ns . 'UserController:create');

// Edit logged in user
$app->get('/user/edit', $ns . 'UserController:edit')->name('editprofile');
$app->post('/user/edit', $ns . 'UserController:edit');

// Show a user by name
$app->get('/user/:username', $ns . 'UserController:show')->name('showuser');

// Show all users
$app->get('/users', $ns . 'UserController:all');

// Log out
$app->get('/logout', $ns . 'UserController:logout')->name('logout');

// Admin restricted area
$app->get('/admin', $ns . 'AdminController:index')->name('admin');
$app->get('/admin/delete/:username', $ns . 'AdminController:delete');

// Movies
$app->get('/movies', $ns . 'MovieController:index')->name('movies');
$app->get('/movies/:movieid', $ns . 'MovieController:show');
$app->post('/movies/:movieid', $ns . 'MovieController:addReview');

return $app;
