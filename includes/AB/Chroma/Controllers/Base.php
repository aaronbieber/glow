<?php
/**
 * The basis of all controllers, but you must extend it to use it.
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
namespace AB\Chroma\Controllers;

/**
 * Class Base
 *
 * @author Aaron Bieber <aaron@aaronbieber.com>
 */
abstract class Base
{
    const FORMAT_JSON = 'json';
    const FORMAT_HTML = 'html';

    const HTTP_OK = 200;
    const HTTP_BAD_REQUEST = 400;
    const HTTP_NOT_FOUND = 404;
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    protected $http_status_messages = [
      self::HTTP_OK => 'OK',
      self::HTTP_BAD_REQUEST => 'Bad Request',
      self::HTTP_NOT_FOUND => 'Not Found',
      self::HTTP_INTERNAL_SERVER_ERROR => 'Internal Server Error'
    ];

    protected $args = [];
    protected $params = [];
    protected $method = '';
    protected $renderer = null;

    public $auto_render = true;

    public function __construct()
    {
        $twig_loader = new \Twig_Loader_Filesystem(LIBRARY_PATH . '/AB/Chroma/Views');
        $this->renderer = new \Twig_Environment($twig_loader, [ 'auto_reload' => true, 'cache' => 'cache/templates' ]);

        // Merge GET and POST for "normal" request types (we can safely do this all the time).
        $this->params = array_merge($_GET, $_POST);

        // If the request type is one of PUT, PATCH, DELETE, get the raw request body data and parse it.
        if ($_SERVER['REQUEST_METHOD'] == 'PUT'
        || $_SERVER['REQUEST_METHOD'] == 'PATCH'
        ) {
            parse_str(file_get_contents('php://input'), $raw_params);
            $this->params = array_merge($this->params, $raw_params);
        } elseif (strtolower($_SERVER['CONTENT_TYPE']) == 'application/json') {
            $raw_json = file_get_contents('php://input');
            $json_data = json_decode($raw_json);
            $this->params = array_merge($this->params, (array) $json_data);
        }
    }

    protected function param($param)
    {
        return isset($this->params[$param]) ? $this->params[$param] : null;
    }

    protected function renderError($params, $format = self::FORMAT_HTML, $code = self::HTTP_INTERNAL_SERVER_ERROR)
    {
        if (!in_array($code, $this->http_status_messages)) {
            $code = self::HTTP_INTERNAL_SERVER_ERROR;
        }

        header(sprintf('HTTP/1.1 %s %s', $code, $this->http_status_messages[$code]));
        $this->render($params, $format);
    }

    protected function render($params, $format = self::FORMAT_HTML)
    {
        if ($format == self::FORMAT_HTML) {
            $view_name = str_replace('AB\\Chroma\\Controllers\\', '', get_class($this)) .
            DIRECTORY_SEPARATOR . strtolower($_SERVER['REQUEST_METHOD']) . '.html';
            echo $this->renderer->render($view_name, $params);
        } elseif ($format == self::FORMAT_JSON) {
            header('Content-Type: application/json');
            echo json_encode($params);
        }
    }
}
