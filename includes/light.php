<?php

class Light
{
  public $id = 0;
  public $name = '';
  public $on = false;
  public $colormode = 'ct';
  public $ct = null;
  public $hue = null;
  public $sat = null;
  public $bri = null;

  public function __construct($state = []) {
    if (count($state)) {
      $this->load_state($state);
    }
  }

  public function load_state(Array $state) {
    foreach($state as $setting => $value) {
      if (property_exists($this, $setting)) {
        $this->{$setting} = $value;
      }
    }
  }

  public function as_hex() {
    return $this->_hsl_to_hex(
      $this->hue,
      $this->sat,
      $this->bri
    );
  }

  public function as_rgb() {
    $hue = ($this->hue * 255) / 65535;

    return $this->_hsl_to_rgb(
      $hue,
      $this->sat,
      $this->bri
    );
  }

  private function _hsl_to_hex($h, $s, $l) {
    $h = ($h * 255) / 65535;

    $rgb = $this->_hsl_to_rgb($h, $s, $l);

    //var_dump($rgb);

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
