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

    foreach ($scene->lights as $light) {
      if ($light->power == false) {
        $state = [
          'power' => false
        ];
      } elseif ($light->colormode == 'ct') {
        $state = [
          'power'  => true,
          'bri' => $light->bri,
          'ct'  => $light->ct
        ];
      } elseif ($light->colormode == 'hs') {
        $state = [
          'power'  => true,
          'hue' => $light->hue,
          'sat' => $light->sat,
          'bri' => $light->bri
        ];
      }

      $ret = $Lights->set_state($state, $light->id);
      if (!$ret) {
        $this->render(['success' => false], Base::FORMAT_JSON);
      }
    }
    $this->render(['success' => true], Base::FORMAT_JSON);
  }
}
