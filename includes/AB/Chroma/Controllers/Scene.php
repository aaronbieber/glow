<?php
namespace AB\Chroma\Controllers;

class Scene extends Base {
  private $_scenes;

  public function __construct() {
    parent::__construct();

    // Get our scenes.
    $this->_scenes = new \AB\Chroma\Scenes();
    $this->_scenes->load();
  }

  /**
   * Create a new scene.
   *
   * @return void
   */
  public function post() {
    // What is our new scene ID?
    $scene_id = max(array_keys($this->_scenes->scenes));
    $scene_id++;

    $this->_scenes->scenes[$scene_id] = new \AB\Chroma\Scene();
    $this->_scenes->scenes[$scene_id]->name = 'Untitled Scene';

    // Load up the current lights.
    $Lights = new \AB\Chroma\Lights();
    foreach ($Lights->lights as $light) {
      $this->_scenes->scenes[$scene_id]->lights[$light->id] = $light;
    }

    $this->_scenes->save();
    $this->render(['success' => true], Base::FORMAT_JSON);
  }

  /**
   * Edit a scene (patch).
   *
   * @return void
   */
  public function patch($scene_id) {
    $scene = $this->_scenes->find_by_id($scene_id);
    $updates = ['scene' => $scene_id];

    /* Indiscriminately set all scene values that exist in the post. Right now, the only value that can be set at
     * the scene level is "name." */
    foreach ($this->params as $key => $value) {
      if (isset($scene->{$key})) {
        $scene->{$key} = $value;
        $updates[$key] = $value;
        unset($this->params[$key]);
      }
    }

    if (!empty($this->params['light'])) {
      $light_id = $this->params['light'];
      foreach ($this->params as $key => $value) {
        if (isset($scene->lights[$light_id]->{$key})) {
          if (is_numeric($value)) {
            $value = (int) $value;
          } elseif ($value == 'true') {
            $value = true;
          } elseif ($value == 'false') {
            $value = false;
          }
          $scene->lights[$light_id]->{$key} = $value;
          unset($this->params[$key]);
        }
      }
    }

    $this->_scenes->save();
  }
}
