<?php
namespace AB\Chroma\Controllers;

class SceneActionHandler extends Base {
  public function __construct() {
    parent::__construct();

    // Get our scenes.
    $this->_scenes = new \AB\Chroma\Scenes();
    $this->_scenes->load();
  }

  public function post($scene_id) {
    $scene = $this->_scenes->scenes[$scene_id];
    $Lights = new \AB\Chroma\Lights();

    $light_settings = $scene->as_settings_array();
    foreach ($light_settings as $id => $state) {
      $ret = $Lights->set_state($state, $id);
      if (!$ret) {
        $this->render(['success' => false], Base::FORMAT_JSON);
      }
    }

    $this->render(['success' => true], Base::FORMAT_JSON);
  }
}
