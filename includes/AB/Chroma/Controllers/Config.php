<?php
/**
 * Controller to handle all configuration-related requests.
 *
 * PHP Version 5
 *
 * @author    Aaron Bieber <aaron@aaronbieber.com>
 * @copyright 2015 Aaron Bieber, All Rights Reserved
 */
namespace AB\Chroma\Controllers;

class Config extends Base {
  public function get() {
    echo "Configuration*";
    $config = \AB\Chroma\Config::get_instance();
    $this->render($config->get_all(), Base::FORMAT_JSON);
  }
}