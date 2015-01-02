<?php
/**
 * Interface to configuration options.
 *
 * @copyright 2015 Aaron Bieber, All Rights Reserved
 */
namespace AB\Chroma;

class Config {
  private static $instance = null;
  public $options = [];

  public function __construct() {
    $this->load();
  }

  public function get_instance() {
    if (static::$instance === null) {
      static::$instance = new static();
    }

    return $this->instance;
  }

  private function load() {
    $config_yaml = file_get_contents('config/config.yml');
    $this->options = \yaml_parse($config_yaml);
    var_dump($this->options);
  }
}