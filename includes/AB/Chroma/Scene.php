<?php
namespace AB\Chroma;

class Scene {
  public $name = '';
  public $lights = [];

  public function __construct() {
  }

  /**
   * Function as_array
   *
   * @return array Array of light data.
   */
  public function as_array() {
    $lights = [];
    foreach ($this->lights as $light) {
      $lights[] = $light->as_array();
    }

    return $lights;
  }

  /**
   * Function as_settings_array
   *
   * @return array An array suitable for setting light state.
   */
  public function as_settings_array() {
    $lights_arrays   = $this->as_array();
    $lights_settings = [];
    // Remove unnecessary elements.
    foreach ($lights_arrays as $light) {
      $lights_settings[$light['id']] = [];
      foreach ($light as $setting => $value) {

        $acceptable_settings =
          array_merge(
            ['power'],
            $light['colormode'] == 'ct' ? ['ct', 'bri'] : ['hue', 'sat', 'bri']
          );

        if (in_array($setting, $acceptable_settings)) {
          $lights_settings[$light['id']][$setting] = $value;
        }
      }
    }

    return $lights_settings;
  }
}
