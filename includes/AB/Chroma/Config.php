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

  public static function get_instance() {
    if (static::$instance === null) {
      static::$instance = new static();
    }

    return static::$instance;
  }

  private function load() {
    $config_yaml = file_get_contents('config/config.yml');
    $this->options = \yaml_parse($config_yaml);
  }

  public function get($key) {
    if (isset($this->options[$key])) {
      return $this->options[$key];
    } else {
      return false;
    }
  }

  public function get_all() {
    return $this->options;
  }
}