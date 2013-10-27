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
define('LIBRARY_PATH', './includes');

// Pull in our autoloader, which is the only thing we need to include.
require_once 'includes/autoloader.php';
require_once 'vendor/autoload.php';

$twig_loader = new Twig_Loader_Filesystem('includes/AB/Chroma/Views');
$twig = new Twig_Environment($twig_loader, [ 'auto_reload' => true, 'cache' => 'cache/templates' ]);

$request = new \AB\Chroma\RequestContext($_SERVER);

// Easy, right?
$dispatcher = new \AB\Chroma\Dispatcher($request);
$dispatcher->renderer = $twig;
$dispatcher->dispatch();
