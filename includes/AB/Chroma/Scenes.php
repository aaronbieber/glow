<?php
namespace AB\Chroma;

class Scenes implements \Iterator {
  public $scenes = [];

  public function rewind() {
    return reset($this->scenes);
  }

  public function current() {
    return current($this->scenes);
  }

  public function key() {
    return key($this->scenes);
  }

  public function next() {
    return next($this->scenes);
  }

  public function valid() {
    return key($this->scenes) !== null;
  }

  public function load() {
    $scenes_yaml = file_get_contents('scenes.yml');
    $this->from_array(\yaml_parse($scenes_yaml));
  }

  public function save() {
    $scenes_yaml = yaml_emit($this->as_array());
    $fp = fopen('scenes.yml', 'w');
    fwrite($fp, $scenes_yaml);
    fclose($fp);
  }

  public function from_array($self_array) {
    foreach ($self_array as $scene_id => $scene) {
      $this->scenes[$scene_id] = new Scene();
      $this->scenes[$scene_id]->id = $scene_id;
      $this->scenes[$scene_id]->name = $scene['name'];

      foreach ($scene['lights'] as $light_id => $light) {
        $this->scenes[$scene_id]->lights[$light_id] = new Light([
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
    }
  }

  public function as_array() {
    $self_array = [];

    foreach ($this->scenes as $scene_id => $scene) {
      $scene_lights = [];
      foreach ($scene->lights as $light) {
        $scene_lights[$light->id] = $light->as_array();
        //[
        //  'name'      => $light->name,
        //  'power'     => $light->power,
        //  'colormode' => $light->colormode,
        //  'ct'        => $light->ct,
        //  'hue'       => $light->hue,
        //  'sat'       => $light->sat,
        //  'bri'       => $light->bri
        //];
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
