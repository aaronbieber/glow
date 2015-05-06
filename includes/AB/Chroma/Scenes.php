<?php
namespace AB\Chroma;

class Scenes extends Collection {
  const FLAG_ICASE = 1;
  public $errors = [];

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
    usort($this->models, array($this, 'compare_scene_sorts'));

    foreach ($this as $scene) {
      usort($scene->lights, array($this, 'compare_light_names'));
    }
  }

  public function save() {
    if ($this->validate()) {
      $scenes_yaml = yaml_emit($this->as_array());
      $fp = fopen('data/scenes.yml', 'w');
      fwrite($fp, $scenes_yaml);
      fclose($fp);
      return true;
    }

    return false;
  }

  public function validate() {
    foreach ($this->models as $scene) {
      if (!$scene->validate()) {
        $this->errors[] = 'Errors in scene ' . $scene->id . ': ' . implode(', ', $scene->errors);
      }
    }

    return empty($this->errors);
  }

  private function compare_scene_names($a, $b) {
    return strcmp($a->name, $b->name);
  }

  private function compare_scene_sorts($a, $b) {
    if ($a->sort == $b->sort) return 0;
    if ($a->sort < $b->sort)  return -1;
    if ($a->sort > $b->sort)  return 1;
  }

  private function compare_light_names($a, $b) {
    return strcmp($a->name, $b->name);
  }

  private function from_array($self_array) {
    $scenes = [];

    foreach ($self_array as $scene) {
      $new_scene       = new Scene();
      $new_scene->id   = $scene['id'];
      $new_scene->name = $scene['name'];
      $new_scene->sort = $scene['sort'];

      foreach ($scene['lights'] as $light) {
        $light_id = $light['id'];
        $new_scene->lights[$light_id] = new Light([
          'id'        => $light_id,
          'name'      => $light['name'],
          'power'     => (bool) $light['power'],
          'colormode' => $light['colormode'],
          'ct'        => $light['ct'],
          'hue'       => $light['hue'],
          'sat'       => $light['sat'],
          'bri'       => $light['bri']
        ]);
      }

      $scenes[] = $new_scene;
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
        'id'     => $scene->id,
        'name'   => $scene->name,
        'sort'   => $scene->sort,
        'lights' => $scene_lights
      ];
    }

    return $self_array;
  }
}
