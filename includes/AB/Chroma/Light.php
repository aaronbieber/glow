<?php
namespace AB\Chroma;

class Light {
  public $id = 0;
  public $name = '';
  public $power = true;
  public $has_ct = true;
  public $has_hs = true;
  public $colormode = 'ct';
  public $ct = null;
  public $hue = null;
  public $sat = null;
  public $bri = null;
  public $hex = null;
  private $modelid;
  private $hue_interface;

  public function __construct($data = []) {
    if (!empty($data)) {
      $this->populate($data);
      $this->hex = $this->as_hex();
    }
    $this->hue_interface = Hue::get_instance();
  }

  public function populate(Array $data) {
    foreach($data as $setting => $value) {
      if (property_exists($this, $setting)) {
        $this->{$setting} = $value;
      }
    }

    if (!empty($data['state'])) {
      foreach($data['state'] as $setting => $value) {
        if (property_exists($this, $setting)) {
          $this->{$setting} = $value;
        } elseif ($setting == 'on') {
          $this->power = $value;
        }
      }
    }

    $capabilities = $this->_capabilities();
    $this->has_ct = $capabilities['ct'];
    $this->has_hs = $capabilities['hs'];
  }

  public function load_by_id($light_id) {
    $this->id = $light_id;
    $this->populate($this->hue_interface->get_light($light_id));
  }

  public function save() {
    if ($this->power === false) {
      // If power is off, we can't send any other values.
      $state = [ 'power' => false ];
    } else {
      if ($this->colormode == 'ct') {
        $state = [
            'ct' => $this->ct,
            'bri' => $this->bri
        ];
      } else {
        $state = [
            'hue' => $this->hue,
            'sat' => $this->sat,
            'bri' => $this->bri
        ];
      }

      // Cast all values to integers and remove nulls.
      $state = array_filter($state, function ($v) { return $v !== null; });
      $state = array_map(function ($v) { return (int) $v; }, $state);

      // Set power to an actual Boolean.
      $state['power'] = true;
    }

    $this->hue_interface->set_light_state($this->id, $state);
  }

  public function as_array() {
    // Get all public properties as an associative array.
    $light_array = [
      'id'        => $this->id,
      'name'      => $this->name,
      'power'     => $this->power,
      'has_ct'    => $this->has_ct,
      'has_hs'    => $this->has_hs,
      'colormode' => $this->colormode,
      'ct'        => $this->ct,
      'hue'       => $this->hue,
      'sat'       => $this->sat,
      'bri'       => $this->bri,
      'hex'       => $this->as_hex()
    ];

    return $light_array;
  }

  public function as_hex() {
    // Exception if the light is off.
    if (!$this->power) {
      return '#000000';
    }

    if (   !$this->_capabilities()['ct']
        && !$this->_capabilities()['hs']) {
      // Bulbs like Hue White cannot change color; they are fixed at 2700K, or 370 in the Mired system.
      return $this->_ct_to_hex(370, $this->bri);
    }

    if ($this->colormode == 'ct') {
      return $this->_ct_to_hex($this->ct, $this->bri);
    } else {
      return $this->_hsl_to_hex(
        $this->hue,
        $this->sat,
        $this->bri
      );
    }
  }

  public function as_rgb() {
    $hue = ($this->hue * 255) / 65535;

    return $this->_hsl_to_rgb(
      $hue,
      $this->sat,
      $this->bri
    );
  }

  private function _capabilities() {
    $capabilities = [
        'ct' => true,
        'hs' => true
    ];

    switch ($this->modelid) {
      case 'LWB006':
        // Hue White
        $capabilities['ct'] = false;
        $capabilities['hs'] = false;
        break;

      case 'LLC010':
        // Living Color Iris
        $capabilities['ct'] = false;
        break;

      case 'LCT001':
        // Standard Hue color
        break;
    }

    return $capabilities;
  }

  private function _ct_to_hex($ct, $bri) {
    $percent = (($ct - 153) / 347) * 100;
    $bri = (($bri * 160) / 255) + 95;
    $darken = 1 - ((255 - $bri) / 255);

    $first  = [ 158, 175, 213 ];
    $last   = [ 213, 183, 160 ];
    $deltas = [ ($last[0] - $first[0]) / 100, ($last[1] - $first[1]) / 100, ($last[2] - $first[2]) / 100 ];

    $r = floor(($first[0] + $percent * $deltas[0]) * $darken);
    $g = floor(($first[1] + $percent * $deltas[1]) * $darken);
    $b = floor(($first[2] + $percent * $deltas[2]) * $darken);

    $color  = '#' .
      sprintf('%02s', dechex($r)) .
      sprintf('%02s', dechex($g)) .
      sprintf('%02s', dechex($b));

    return $color;
  }

  private function _hsl_to_hex($h, $s, $l) {
    $h = ($h * 255) / 65535;

    $rgb = $this->_hsl_to_rgb($h, $s, $l);

    // Convert back to 255 scale.
    $rgb['r'] = floor($rgb['r']);
    $rgb['g'] = floor($rgb['g']);
    $rgb['b'] = floor($rgb['b']);

    // Convert to hex.
    $rgb['r'] = dechex($rgb['r']);
    $rgb['g'] = dechex($rgb['g']);
    $rgb['b'] = dechex($rgb['b']);

    foreach (['r', 'g', 'b'] as $key) {
      if (strlen($rgb[$key]) < 2) {
        $rgb[$key] = '0' . $rgb[$key];
      }
    }

    return '#' . $rgb['r'] . $rgb['g'] . $rgb['b'];
  }

  private function _hue_to_rgb($p, $q, $t) {
    if ($t < 0) $t += 1;
    if ($t > 1) $t -= 1;
    if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
    if ($t < 1/2) return $q;
    if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;

    return $p;
  }

  private function _hsl_to_rgb($h, $s, $l) {
    $r = $g = $b = 0;

    $h /= 255;
    $s /= 255;
    $l /= 255;

    if ($s == 0){
      $r = $g = $b = $l; // achromatic
    } else {

      $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
      $p = 2 * $l - $q;
      $r = $this->_hue_to_rgb($p, $q, $h + 1/3);
      $g = $this->_hue_to_rgb($p, $q, $h);
      $b = $this->_hue_to_rgb($p, $q, $h - 1/3);
    }

    return [
      'r' => $r * 255,
      'g' => $g * 255,
      'b' => $b * 255
    ];
  }
}
