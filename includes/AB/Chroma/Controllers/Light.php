<?php
namespace AB\Chroma\Controllers;

class Light extends Base {
  private $_lights;

  public function __construct() {
    parent::__construct();

    // Get our lights.
    $this->_lights = new \AB\Chroma\Lights();
  }

  public function post($light_id) {
    // If power is off, do nothing else (altering other values while power is off is not permitted).
    if ($this->param('power') !== null && $this->param('power') == false) {
      $state = [ 'power' => false ];
    } else {
      // Otherwise, generate a clean state by removing null values (those not provided in the request).
      $state = array_filter([
        'ct'        => $this->param('ct'),
        'hue'       => $this->param('hue'),
        'sat'       => $this->param('sat'),
        'bri'       => $this->param('bri')
      ], function ($v) {
        return $v !== null;
      });

      // Cast all non-Boolean values as integers.
      $state = array_map(function ($v) { return (int) $v; }, $state);
      $state['power'] = true;
    }

    $this->_lights->set_state($state, $light_id);

    $this->render(['success' => true], Base::FORMAT_JSON);
  }

  public function power($id) {
    // Nothing to render.
    $this->auto_render = false;

    if ($this->method == 'post') {
      $light_id = (int) array_shift($this->args);
      $power = array_shift($this->args);

      if (is_numeric($light_id) && in_array($power, ['on', 'off'])) {
        $power = ($power == 'on') ? true : false;
        $this->_lights->set_state([ 'on' => $power ], $light_id);
      }
    }

    return ['success' => true];
  }
}
