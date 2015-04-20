<?php
/**
 * The basis of all controllers, but you must extend it to use it.
 *
 * PHP Version 5
 *
 * @author    Aaron Bieber <aaron@aaronbieber.com>
 * @copyright 2013 Aaron Bieber, All Rights Reserved
 */
namespace AB\Chroma\Controllers;

/**
 * Class Base
 *
 * @author Aaron Bieber <aaron@aaronbieber.com>
 */
abstract class Base {
  const FORMAT_JSON = 'json';
  const FORMAT_HTML = 'html';

  protected $args = [];
  protected $params = [];
  protected $method = '';
  protected $renderer = null;

  public $auto_render = true;

  public function __construct() {
    $twig_loader = new \Twig_Loader_Filesystem(LIBRARY_PATH . '/AB/Chroma/Views');
    $this->renderer = new \Twig_Environment($twig_loader, [ 'auto_reload' => true, 'cache' => 'cache/templates' ]);

    // Merge GET and POST for "normal" request types (we can safely do this all the time).
    $this->params = array_merge($_GET, $_POST);

    // If the request type is one of PUT, PATCH, DELETE, get the raw request body data and parse it.
    if (   $_SERVER['REQUEST_METHOD'] == 'PUT'
        || $_SERVER['REQUEST_METHOD'] == 'PATCH'
    ) {
      parse_str(file_get_contents('php://input'), $raw_params);
      $this->params = array_merge($this->params, $raw_params);
    }
  }

  protected function param($param) {
    return isset($this->params[$param]) ? $this->params[$param] : null;
  }

  protected function render($params, $format = self::FORMAT_HTML) {
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
