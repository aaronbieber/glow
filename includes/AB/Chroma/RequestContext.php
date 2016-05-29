<?php
/**
 * A discrete request coming into the framework.
 *
 * PHP Version 5
 *
 * @category  Glow
 * @package   Glow
 * @author    Aaron Bieber <aaron@aaronbieber.com>
 * @copyright 2016 All Rights Reserved
 * @license   GNU GPLv3
 * @version   GIT: $Id$
 * @link      http://github.com/aaronbieber/glow
 */
namespace AB\Chroma;

class RequestContext
{
    public $controller = 'Home';
    public $action = 'index';
    public $method = '';
    public $args = [];
    public $params = '';

    public function __construct($server_context)
    {
        $this->method = strtolower($server_context['REQUEST_METHOD']);
        $this->params = array_merge($_GET, $_POST);
        $uri = $server_context['REQUEST_URI'];

        // Remove leading and trailing slashes, if any.
        $uri = trim($uri, '/');

        // If there is a query string, chop it off.
        if (strpos($uri, '?') !== false) {
            //parse_str(substr($uri, strpos($uri, '?') + 1), $this->params);
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        // If the URI is empty, leave the defaults and return.
        if (empty($uri)) {
            return;
        }

        // Separate the path components.
        $uri_parts = explode('/', $uri);

        // Populate the controller/action/args.
        if (!empty($uri_parts)) {
            $this->controller = ucwords(array_shift($uri_parts));
        }
        if (!empty($uri_parts)) {
            $this->action = array_shift($uri_parts);
        }
        if (!empty($uri_parts)) {
            $this->args = $uri_parts;
        }
    }
}
