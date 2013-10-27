<?php
/**
 *
 *
 * PHP Version 5
 *
 * @author    Aaron Bieber <aaron@aaronbieber.com>
 * @copyright 2013 Aaron Bieber, All Rights Reserved
 */
namespace AB\Chroma\Controllers;

/**
 * Class Base
 *
 * @author Aaron Bieber <aaron@aaronbieber.com>
 */
class Base {
  protected $args = [];
  protected $params = [];
  protected $method = '';
  public $auto_render = true;

  public function __construct(\AB\Chroma\Dispatcher $dispatcher) {
    $this->args = $dispatcher->request->args;
    $this->params = $dispatcher->request->params;
    $this->method = $dispatcher->request->method;
  }

  public function pre_action() {
    // No-op.
  }
}
