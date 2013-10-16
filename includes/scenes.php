<?php

class Scenes implements Iterator {
  public $scenes = [];
  private $_position = 0;

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

  public function load_scenes() {
    $scenes_yaml = file_get_contents('scenes.yml');
    $this->scenes = yaml_parse($scenes_yaml);
  }

  public function save_scenes() {
    $scenes_yaml = yaml_emit($this->scenes);
    $fp = fopen('scenes.yml', 'w');
    fwrite($fp, $scenes_yaml);
    fclose($fp);
  }
}
