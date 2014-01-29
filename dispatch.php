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
require_once 'includes/autoloader.php';
require_once 'vendor/autoload.php';

ToroHook::add('404', function() { echo "Not found."; });

Toro::serve(array(
  '/'                     => '\\AB\\Chroma\\Controllers\\Home',
  '/lights'               => '\\AB\\Chroma\\Controllers\\Lights',
  '/light/:number'        => '\\AB\\Chroma\\Controllers\\Light',
  '/scenes'               => '\\AB\\Chroma\\Controllers\\Scene',
  '/scene/:number'        => '\\AB\\Chroma\\Controllers\\Scene',
  '/scene/:number/choose' => '\\AB\\Chroma\\Controllers\\SceneActionHandler'
));
