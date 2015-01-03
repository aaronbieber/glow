<?php
/**
 * Lights functions
 *
 * PHP Version 5
 *
 * @author    Aaron Bieber <aaron@aaronbieber.com>
 * @copyright 2013 All Rights Reserved
 * @version   git: $Id$
 */
namespace AB\Chroma;

class Lights extends Collection {
  private $hue;
  private $bridge_ip = '192.168.10.30';

  public function __construct() {
    $this->hue = Hue::get_instance();
    $this->load_lights();
    usort($this->models, [ $this, 'light_name_compare' ]);
  }

  private function light_name_compare($a, $b) {
    return strcmp($a->name, $b->name);
  }

  public function set_state(Array $state, $light_id = 0) {
    if ($light_id == 0) {
      $success = true;
      foreach ($this as $light) {
        $ret = $this->hue->set_light_state($light->id, $state);
        if (!$ret) {
          $success = false;
          break;
        }
        usleep(100000);
      }
      return $success;
    } else {
      return $this->hue->set_light_state($light_id, $state);
    }
  }

  public function as_array() {
    $lights_array = [];

    // Create an array of each of the lights converted to an array. Simple.
    foreach ($this->models as $light) {
      $lights_array[] = $light->as_array();
    }

    return $lights_array;
  }

  private function load_lights() {
    $response = $this->hue->get_lights();

    foreach($response as $light_id => $light_data) {
      $light = new Light();
      $light->id = $light_id;
      $light->name = $light_data['name'];
      $light->load_state($this->hue->get_light_state($light_id));
      $this->models[] = $light;
    }
  }
}
