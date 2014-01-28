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
    $state = array_filter([
      'power'     => $this->param('power') === null ? true : (bool) $this->param('power'),
      'ct'        => (int) $this->param('ct'),
      'hue'       => (int) $this->param('hue'),
      'sat'       => (int) $this->param('sat'),
      'bri'       => (int) $this->param('bri')
    ], function ($v) {
      return $v !== null;
    });

    $this->_lights->set_state($state, $light_id);

    //switch ($this->params['action']) {
    //  case 'update-hsl':
    //    if ( empty($this->params['hue'])
    //      || empty($this->params['sat'])
    //      || empty($this->params['bri'])
    //    ) {
    //      break;
    //    }

    //    // Set the light's HSL values.
    //    $state = [
    //      'hue' => (int) $_POST['hue'],
    //      'sat' => (int) $_POST['sat'],
    //      'bri' => (int) $_POST['bri']
    //    ];

    //    $this->_lights->set_state($state, $this->params['light']);
    //    break;

    //  case 'update-bri':
    //    if (empty($this->params['bri'])) {
    //      break;
    //    }

    //    // Set the light's brightness.
    //    $state = [ 'on' => true, 'bri' => (int) $this->params['bri'] ];
    //    $this->_lights->set_state($state, $this->params['light']);
    //    break;

    //  case 'update-ct':
    //    if (empty($this->params['ct'])) {
    //      break;
    //    }

    //    // Set the light's color temperature.
    //    $state = [ 'on' => true, 'ct' => (int) $this->params['ct'] ];
    //    $this->_lights->set_state($state, $this->params['light']);
    //    break;
    //}

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
