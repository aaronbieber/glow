<?php
namespace AB\Chroma\Controllers;

class Lights extends Base {
  private $_lights;

  public function __construct() {
    parent::__construct();

    // Get our lights.
    $this->_lights = new \AB\Chroma\Lights();
  }

  public function get() {
    $this->render($this->_lights->as_array(), Base::FORMAT_JSON);
  }
}
