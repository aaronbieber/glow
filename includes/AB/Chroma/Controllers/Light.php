<?php
namespace AB\Chroma\Controllers;

class Light extends Base {
  public function get($light_id) {
    $light = new \AB\Chroma\Light();
    $light->load_by_id($light_id);
    $this->render($light->as_array(), Base::FORMAT_JSON);
  }

  public function post($light_id) {
    $light = new \AB\Chroma\Light();
    $light->populate($this->params);

    // Only set the power attribute if it was supplied.
    if ($this->param('power') !== null) {
      $light->power = (bool) $this->param('power');
    }

    $light->save();

    $this->render($light->as_array(), Base::FORMAT_JSON);
  }
}
