<?php
namespace AB\Chroma\Controllers;

class SceneActionHandler extends Base {
  public function post($scene_id) {
    // Load all scenes (from YAML file on disk).
    $scenes = new \AB\Chroma\Scenes();
    $scenes->load();

    // Grab the scene we want to use.
    $scene = $scenes->find_by_id($scene_id);
    // Save each light in the scene, effectively setting it.
    $scene->set();

    $this->render(['success' => true, 'id' => $scene->id, 'name' => $scene->name ], Base::FORMAT_JSON);
  }
}
