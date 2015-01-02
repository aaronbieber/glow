<?php
namespace AB\Chroma;

class Scenes implements \Iterator, \ArrayAccess {
  /**
   * @var array Scene objects.
   */
  public $scenes = [];

  /**
   * Rewind to the start.
   *
   * @return First Scene object in the collection.
   */
  public function rewind() {
    return reset($this->scenes);
  }

  /**
   * Get the current Scene object.
   *
   * @return \AB\Chroma\Scene Current Scene object in the collection.
   */
  public function current() {
    return current($this->scenes);
  }

  /**
   * Return the key of the current Scene item in the collection.
   *
   * @return string The current key.
   */
  public function key() {
    return key($this->scenes);
  }

  public function next() {
    return next($this->scenes);
  }

  public function valid() {
    return key($this->scenes) !== null;
  }

  public function offsetSet($offset, $value) {
    if (is_null($offset)) {
      $this->scenes[] = $value;
    } else {
      $this->scenes[$offset] = $value;
    }
  }

  public function offsetExists($offset) {
    return isset($this->scenes[$offset]);
  }

  public function offsetUnset($offset) {
    unset($this->scenes[$offset]);
  }

  public function offsetGet($offset) {
    return isset($this->scenes[$offset]) ? $this->scenes[$offset] : null;
  }

  public function get_key_by_id($id) {
    reset($this->scenes);

    do {
      $scene = current($this->scenes);
      if ($scene->id == $id) {
        return key($this->scenes);
      }
    } while (next($this->scenes) !== false);

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
      return $this->scenes[$index];
    } else {
      return false;
    }
  }

  public function set_by_id($id, $value) {
    if ($index = $this->get_key_by_id($id)) {
      $this->scenes[$index] = $value;
      return true;
    } else {
      return false;
    }
  }

  public function load() {
    $scenes_yaml = file_get_contents('data/scenes.yml');
    $this->scenes = $this->from_array(\yaml_parse($scenes_yaml));
    usort($this->scenes, array($this, '_compare_scene_names'));
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

    foreach ($this->scenes as $scene_id => $scene) {
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
