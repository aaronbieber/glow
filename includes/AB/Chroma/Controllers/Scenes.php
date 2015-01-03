<?php
namespace AB\Chroma\Controllers;

class Scenes extends Base {
  public function __construct() {
    parent::__construct();

    // Get our scenes.
    $this->scenes = new \AB\Chroma\Scenes();
    $this->scenes->load();
  }

  public function get() {
    $this->render($this->scenes->as_array(), Base::FORMAT_JSON);
  }

  /**
   * Select a scene by name (/scenes/by_name/:name)
   *
   * @return void
   */
  public function post($name = null) {
    $name = str_replace('+', ' ', $name);
    $scene = $this->scenes->find_by_name($name, \AB\Chroma\Scenes::FLAG_ICASE);
    if ($scene) {
      $scene->set();
    }

    $this->render([ 'success' => true ], Base::FORMAT_JSON);
  }
}
