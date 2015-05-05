<?php
/**
 * Basic dispatcher. This script receives all requests from the browser and parses the URI to figure out where to send
 * them. Think of it as a super-lightweight RESTful router.
 *
 * PHP Version 5
 *
 * @author    Aaron Bieber <aaron@aaronbieber.com>
 * @copyright 2013 Aaron Bieber, All Rights Reserved
 */

// Define the base library path for the application.
define('LIBRARY_PATH', '/var/www/glow/htdocs/includes');

// Pull in our autoloader, which is the only thing we need to include.
require_once 'vendor/autoload.php';
require_once 'includes/autoloader.php';

Flight::route('/', function() {
  $c = new \AB\Chroma\Controllers\Home();
  $c->get();
});

Flight::route('/heartbeat/@mac', function($mac) {
  echo "$mac\n";
});

Flight::route('GET /lights', function() {
  $c = new \AB\Chroma\Controllers\Lights();
  $c->get();
});

Flight::route('GET /light/@number', function($number) {
  $c = new \AB\Chroma\Controllers\Light();
  $c->get($number);
});

Flight::route('PUT|POST /light/@number', function($number) {
  $c = new \AB\Chroma\Controllers\Light();
  $c->post($number);
});

Flight::route('POST /scene/@scene/choose', function($scene) {
  $c = new \AB\Chroma\Controllers\SceneActionHandler();
  $c->post($scene);
});

Flight::route('POST /scene/choose', function() {
  $c = new \AB\Chroma\Controllers\SceneActionHandler();
  $c->post($scene);
});

Flight::route('POST /scenes/by_name/@scene', function($scene) {
  $c = new \AB\Chroma\Controllers\Scenes();
  $c->post($scene);
});

Flight::route('POST /scene', function() {
  $c = new \AB\Chroma\Controllers\Scene();
  $c->post();
});

Flight::route('PUT|POST /scene/@scene', function($scene) {
  $c = new \AB\Chroma\Controllers\Scene();
  $c->put($scene);
});

Flight::route('GET /scenes', function() {
  $c = new \AB\Chroma\Controllers\Scenes();
  $c->get();
});

Flight::route('PUT /scenes', function() {
  $c = new \AB\Chroma\Controllers\Scenes();
  $c->put();
});

Flight::route('GET /config', function() {
  $c = new \AB\Chroma\Controllers\Config();
  $c->get();
});

Flight::start();
