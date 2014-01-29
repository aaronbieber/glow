<?php
namespace AB\Chroma\Controllers;

class Scenes extends Base {
  public function __construct() {
    parent::__construct();

    // Get our scenes.
    $this->_scenes = new \AB\Chroma\Scenes();
    $this->_scenes->load();
  }

  public function get($name = null) {
    if (!empty($name)) {
      foreach ($this->_scenes as $scene) {
        if (strtolower($scene->name) == strtolower($name)) {
          $this->render($scene, Base::FORMAT_JSON);
          return;
        }
      }
    } else {
      $this->render($this->_scenes->as_array(), Base::FORMAT_JSON);
    }
  }
}
