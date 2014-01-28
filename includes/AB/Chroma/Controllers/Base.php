<?php
/**
 *
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
class Base {
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

    $this->params = array_merge($_GET, $_POST);
  }

  protected function param($param) {
    return !empty($this->params[$param]) ? $this->params[$param] : null;
  }

  protected function render($params, $format = self::FORMAT_HTML) {
    if ($format == self::FORMAT_HTML) {
      $view_name = str_replace('AB\\Chroma\\Controllers\\', '', get_class($this)) .
        DIRECTORY_SEPARATOR . strtolower($_SERVER['REQUEST_METHOD']) . '.html';
      echo $this->renderer->render($view_name, $params);
    } elseif ($format == self::FORMAT_JSON) {
      echo json_encode($params);
    }
  }
}
