<?php
namespace AB\Chroma\Controllers;

class Lights extends Base {
  private $lights;

  public function __construct() {
    parent::__construct();

    // Get our lights.
    $this->lights = new \AB\Chroma\Lights();
  }

  public function get() {
    $this->render($this->lights->as_array(), Base::FORMAT_JSON);
  }
}
