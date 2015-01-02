<?php
namespace AB\Chroma\Controllers;

class Scenes extends Base {
  public function __construct() {
    parent::__construct();

    // Get our scenes.
    $this->_scenes = new \AB\Chroma\Scenes();
    $this->_scenes->load();
  }

  public function get() {
    $this->render($this->_scenes->as_array(), Base::FORMAT_JSON);
    return;

    if (!empty($name)) {
      if ($scene = $this->find($name)) {
        $this->render($scene, Base::FORMAT_JSON);
      } else {
        $this->render(['error' => 'Scene not found.'], Base::FORMAT_JSON);
      }
      return;
    } else {
      $this->render($this->_scenes->as_array(), Base::FORMAT_JSON);
    }
  }

  /**
   * Select a scene by name (/scenes/by_name/:name)
   *
   * @return void
   */
  public function post($name = null) {
    if (!empty($name)) {
      if ($scene = $this->find($name)) {
        $Lights = new \AB\Chroma\Lights();
        $light_settings = $scene->as_settings_array();

        foreach ($light_settings as $id => $state) {
          $ret = $Lights->set_state($state, $id);
          if (!$ret) {
            $this->render([ 'success' => false ], Base::FORMAT_JSON);
          }
        }
      }
    }

    $this->render([ 'success' => true ], Base::FORMAT_JSON);
  }

  protected function find($name) {
    if (!empty($name)) {
      $name = str_replace('+', ' ', $name);
      foreach ($this->_scenes as $scene) {
        if (strtolower($scene->name) == strtolower($name)) {
          return $scene;
        }
      }
    }
    return false;
  }
}
