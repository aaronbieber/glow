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
define('LIBRARY_PATH', '/var/www/lights/htdocs/includes');

// Pull in our autoloader, which is the only thing we need to include.
require_once 'vendor/autoload.php';
require_once 'includes/autoloader.php';

use AB\Chroma\Controllers\Home;
use AB\Chroma\Controllers\Light;
use AB\Chroma\Controllers\Lights;
use AB\Chroma\Controllers\Scene;
use AB\Chroma\Controllers\SceneActionHandler;
use AB\Chroma\Controllers\Scenes;

//Toro::serve(array(
//  '/'                               => '\\AB\\Chroma\\Controllers\\Home',
//  '/lights'                         => '\\AB\\Chroma\\Controllers\\Lights',
//  '/light/:number'                  => '\\AB\\Chroma\\Controllers\\Light',
//  '/scenes/by_name/([a-zA-Z0-9+]+)' => '\\AB\\Chroma\\Controllers\\Scenes',
//  '/scenes'                         => '\\AB\\Chroma\\Controllers\\Scenes',
//  '/scene/:number/choose'           => '\\AB\\Chroma\\Controllers\\SceneActionHandler',
//  '/scene/:number'                  => '\\AB\\Chroma\\Controllers\\Scene',
//  '/scene'                          => '\\AB\\Chroma\\Controllers\\Scene'
//));

Flight::route('/',                           function() { $c = new Home; $c->get(); });
Flight::route('/lights',                     function() { $c = new Lights; $c->get(); });
Flight::route('POST /light/@number',         function($number) { $c = new Light; $c->post($number); });
Flight::route('POST /scene/@scene/choose',   function($scene) { $c = new SceneActionHandler; $c->post($scene); });
Flight::route('POST /scenes/by_name/@scene', function($scene) { $c = new Scenes; $c->post($scene); });

Flight::start();
