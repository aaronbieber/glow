<?php
namespace AB\Chroma;

class Scenes extends Collection {
  const FLAG_ICASE = 1;

  /**
   * Get the Scene with the given ID, and leave the internal pointer pointed at that scene object.
   *
   * @param int $id The ID of the Scene you're looking for.
   *
   * @return bool|\AB\Chroma\Scene The Scene with the ID given, or FALSE if not found.
   */
  public function find_by_id($id) {
    foreach($this as $scene) {
      if ($scene->id == $id) {
        return $scene;
      }
    }
    return false;
  }

  public function find_by_name($name, $flags = null) {
    $name = ($flags & self::FLAG_ICASE) ? strtolower($name) : $name;
    foreach($this as $scene) {
      $scene_name = ($flags & self::FLAG_ICASE) ? strtolower($scene->name) : $scene->name;
      if ($scene_name == $name) {
        return $scene;
      }
    }
    return false;
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
        $scene_lights[] = $light->as_array();
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
