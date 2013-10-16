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
class Lights
{
  public $lights = [];

  public function __construct() {
    $this->lights = $this->_get_lights();
  }

  public function set_state(Array $state, $light_id = 0) {
    if ($light_id == 0) {
      foreach ($this->lights as $light) {
        $this->_set_light_state($light['id'], $state);
        usleep(100000);
      }
    } else {
      $this->_set_light_state($light_id, $state);
    }
  }

  private function _set_light_state($light_id, Array $state) {
    $service_url = 'http://192.168.10.81/api/abcdef101010/lights/' . $light_id . '/state';
    $ch = curl_init($service_url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($state));

    $response = curl_exec($ch);
    echo $response;
    if ($response === false) {
        $info = curl_getinfo($ch);
        curl_close($ch);
        die('error occured during curl exec. Additioanl info: ' . var_export($info));
    }
    curl_close($ch);
  }

  public function get_light_state($light_id) {
    $service_url = 'http://192.168.10.81/api/abcdef101010/lights/' . $light_id . '/';
    $ch = curl_init($service_url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $response = curl_exec($ch);
    if ($response === false) {
        $info = curl_getinfo($ch);
        curl_close($ch);
        die('Fatal error while retrieving lights state: ' . var_export($info));
    }
    curl_close($ch);

    $light_state = json_decode($response, true);
    if (!empty($light_state['state'])) {
      return $light_state['state'];
    }
  }

  private function _get_lights() {
    $service_url = 'http://192.168.10.81/api/abcdef101010/lights/';
    $ch = curl_init($service_url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

    $response = curl_exec($ch);
    if ($response === false) {
        $info = curl_getinfo($ch);
        curl_close($ch);
        die('Fatal error while retrieving lights state: ' . var_export($info));
    }
    curl_close($ch);

    $response = json_decode($response, true);
    $lights = [];
    foreach($response as $light_id => $light_data) {
      $light = new Light();
      $light->id = $light_id;
      $light->name = $light_data['name'];
      $light->load_state($this->get_light_state($light_id));
      $lights[] = $light;
    }

    return $lights;
  }
}
