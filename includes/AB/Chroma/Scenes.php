<?php
namespace AB\Chroma;

class Scenes extends Collection {
  public function get_key_by_id($id) {
    reset($this->models);

    do {
      $scene = current($this->models);
      if ($scene->id == $id) {
        return key($this->models);
      }
    } while (next($this->models) !== false);

    // The scene was not found.
    return false;
  }

  /**
   * Get the Scene with the given ID, and leave the internal pointer pointed at that scene object.
   *
   * @param int $id The ID of the Scene you're looking for.
   *
   * @return bool|\AB\Chroma\Scene The Scene with the ID given, or FALSE if not found.
   */
  public function get_by_id($id) {
    if ($index = $this->get_key_by_id($id)) {
      return $this->models[$index];
    } else {
      return false;
    }
  }

  public function set_by_id($id, $value) {
    if ($index = $this->get_key_by_id($id)) {
      $this->models[$index] = $value;
      return true;
    } else {
      return false;
    }
  }

  public function load() {
    $scenes_yaml = file_get_contents('data/scenes.yml');
    $this->models = $this->from_array(\yaml_parse($scenes_yaml));
    usort($this->models, array($this, '_compare_scene_names'));
  }

  public function save() {
    $scenes_yaml = yaml_emit($this->as_array());
    $fp = fopen('data/scenes.yml', 'w');
    fwrite($fp, $scenes_yaml);
    fclose($fp);
  }

  private function _compare_scene_names($a, $b) {
    return strcmp($a->name, $b->name);
  }

  private function from_array($self_array) {
    $scenes = [];

    foreach ($self_array as $scene) {
      $scene_id = $scene['id'];
      $scenes[$scene_id] = new Scene();
      $scenes[$scene_id]->id = $scene_id;
      $scenes[$scene_id]->name = $scene['name'];

      foreach ($scene['lights'] as $light) {
        $light_id = $light['id'];
        $scenes[$scene_id]->lights[$light_id] = new Light([
          'id'        => $light_id,
          'power'     => (bool) $light['power'],
          'colormode' => $light['colormode'],
          'ct'        => $light['ct'],
          'hue'       => $light['hue'],
          'sat'       => $light['sat'],
          'bri'       => $light['bri']
        ]);
      }
    }

    return $scenes;
  }

  public function as_array() {
    $self_array = [];

    foreach ($this->models as $scene_id => $scene) {
      $scene_lights = [];
      foreach ($scene->lights as $light) {
        $scene_lights[$light->id] = $light->as_array();
      }
      $self_array[$scene_id] = [
        'id'     => $scene_id,
        'name'   => $scene->name,
        'lights' => $scene_lights
      ];
    }

    return $self_array;
  }
}
