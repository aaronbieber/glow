<?php
namespace AB\Chroma;

class Light {
  public $id = 0;
  public $name = '';
  public $power = true;
  public $colormode = 'ct';
  public $ct = null;
  public $hue = null;
  public $sat = null;
  public $bri = null;
  public $hex = null;
  private $hue_interface;

  public function __construct($state = []) {
    if (!empty($state)) {
      $this->load_state($state);
      $this->hex = $this->as_hex();
    }
    $this->hue_interface = Hue::get_instance();
  }

  public function load_state(Array $state) {
    foreach($state as $setting => $value) {
      if (property_exists($this, $setting)) {
        $this->{$setting} = $value;
      }
    }
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
    $light_array = get_object_vars($this);

    // Inject the hex value for this light as well.
    $light_array['hex'] = $this->as_hex();

    return $light_array;
  }

  public function as_hex() {
    // Exception if the light is off.
    if (!$this->power) {
      return '#000000';
    }

    if ($this->colormode == 'ct') {
      return $this->_ct_to_hex($this->ct);
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

  private function _ct_to_hex($ct) {
    $percent = (($ct - 153) / 347) * 100;

    $first  = [ 158, 175, 213 ];
    $last   = [ 213, 183, 160 ];
    $deltas = [ ($last[0] - $first[0]) / 100, ($last[1] - $first[1]) / 100, ($last[2] - $first[2]) / 100 ];
    $color  = '#' .
      sprintf('%02s', dechex(floor($first[0] + $percent * $deltas[0]))) .
      sprintf('%02s', dechex(floor($first[1] + $percent * $deltas[1]))) .
      sprintf('%02s', dechex(floor($first[2] + $percent * $deltas[2])));

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
