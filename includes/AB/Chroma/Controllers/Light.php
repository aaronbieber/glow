<?php
namespace AB\Chroma\Controllers;

class Light extends Base {
  public function post($light_id) {
    $light = new \AB\Chroma\Light([
        'id'    => $light_id,
        'ct'    => $this->param('ct'),
        'hue'   => $this->param('hue'),
        'sat'   => $this->param('sat'),
        'bri'   => $this->param('bri')
    ]);

    // Only set the power attribute if it was supplied.
    if ($this->param('power') !== null) {
      $light->power = (bool) $this->param('power');
    }

    $light->save();

    $this->render(['success' => true], Base::FORMAT_JSON);
  }
}
