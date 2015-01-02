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
  private $bridge_ip = '192.168.10.30';

  public function __construct() {
    $this->models = $this->get_lights();
    usort($this->models, [ $this, 'light_name_compare' ]);
  }

  private function light_name_compare($a, $b) {
    return strcmp($a->name, $b->name);
  }

  public function set_state(Array $state, $light_id = 0) {
    if ($light_id == 0) {
      $success = true;
      foreach ($this as $light) {
        $ret = $this->_set_light_state($light->id, $state);
        if (!$ret) {
          $success = false;
          break;
        }
        usleep(100000);
      }
      return $success;
    } else {
      return $this->_set_light_state($light_id, $state);
    }
  }

  public function as_array() {
    $lights_array = [];

    // Create an array of each of the lights converted to an array. Simple.
    foreach ($this->models as $light) {
      var_dump($light);
      $lights_array[] = $light->as_array();
    }

    return $lights_array;
  }

  private function _set_light_state($light_id, Array $state) {
    // Translate values.
    if (isset($state['power'])) {
      $state['on'] = (bool) $state['power'];
      unset($state['power']);
    }
    $state_json = \json_encode($state);

    $service_url = 'http://' . $this->bridge_ip . '/api/abcdef101010/lights/' . $light_id . '/state';
    $ch = curl_init($service_url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $state_json);

    $response = curl_exec($ch);

    if ($response === false) {
      $info = curl_getinfo($ch);
      curl_close($ch);
      return false;
      // die('error occured during curl exec. Additioanl info: ' . var_export($info));
    }

    // Success!
    curl_close($ch);
    return true;
  }

  private function _get_light_state($light_id) {
    $service_url = 'http://' . $this->bridge_ip . '/api/abcdef101010/lights/' . $light_id . '/';
    $ch = curl_init($service_url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $response = curl_exec($ch);
    if ($response === false) {
      $info = curl_getinfo($ch);
      curl_close($ch);
      return false;
      //die('Fatal error while retrieving lights state: ' . var_export($info));
    }
    curl_close($ch);

    $light_state = \json_decode($response, true);

    if (!empty($light_state['state'])) {
      // Translate values.
      if (isset($light_state['state']['on'])) {
        $light_state['state']['power'] = $light_state['state']['on'];
        unset($light_state['state']['on']);
      }

      return $light_state['state'];
    }

    return false;
  }

  private function get_lights() {
    $service_url = 'http://' . $this->bridge_ip . '/api/abcdef101010/lights/';
    $ch = curl_init($service_url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $response = curl_exec($ch);
    if ($response === false) {
        $info = curl_getinfo($ch);
        curl_close($ch);
        return false;
    }
    curl_close($ch);

    $response = \json_decode($response, true);
    $lights = [];
    foreach($response as $light_id => $light_data) {
      $light = new Light();
      $light->id = $light_id;
      $light->name = $light_data['name'];
      $light->load_state($this->_get_light_state($light_id));
      $lights[] = $light;
    }

    return $lights;
  }
}
