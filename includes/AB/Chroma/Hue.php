<?php
/**
 * Interface to the Hue system itself. This encapsulates all of the network API stuff.
 *
 * PHP Version 5
 *
 * @author    Aaron Bieber <aaron@aaronbieber.com>
 * @copyright 2015 All Rights Reserved
 */
namespace AB\Chroma;

class Hue {
  protected static $instance = null;
  protected $config;
  protected $bridge_ip;
  protected $api_key;

  public function __construct() {
    $this->config    = Config::get_instance();
    $this->bridge_ip = $this->config->get('bridge_ip');
    $this->api_key   = $this->config->get('api_key');
  }

  public static function get_instance() {
    if (static::$instance === null) {
      static::$instance = new static();
    }

    return static::$instance;
  }

  private function get_service_url($path = '/') {
    if (substr($path, 0, 1) != '/') {
      $path = "/$path";
    }

    return 'http://' . $this->bridge_ip . '/api/' . $this->api_key . $path;
  }

  public function set_light_state($light_id, Array $state) {
    // Translate values.
    if (isset($state['power'])) {
      $state['on'] = (bool) $state['power'];
      unset($state['power']);
    }
    $state_json = \json_encode($state);

    $service_url = $this->get_service_url('/lights/' . $light_id . '/state');
    $ch = curl_init($service_url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $state_json);

    $response = curl_exec($ch);

    if ($response === false) {
      $info = curl_getinfo($ch);
      curl_close($ch);
      return false;
    }

    curl_close($ch);
    return true;
  }

  public function get_light_state($light_id) {
    $service_url = $this->get_service_url('/lights/' . $light_id . '/');
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

  public function get_lights() {
    $service_url = $this->get_service_url('/lights/');
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

    return \json_decode($response, true);
  }
}