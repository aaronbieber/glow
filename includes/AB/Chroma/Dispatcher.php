<?php
/**
 * Basic dispatcher. This script receives all requests from the browser and parses the URI to figure out where to send
 * them. Think of it as a super-lightweight RESTful router.
 *
 * PHP Version 5
 *
 * @author    Aaron Bieber <aaron@aaronbieber.com>
 * @copyright 2013 Aaron Bieber, All Rights Reserved
 */
namespace AB\Chroma;

/**
 * Class Dispatcher
 *
 * @author Aaron Bieber <aaron@aaronbieber.com>
 */
class Dispatcher {
  public $renderer = null;
  public $request = null;

  public function __construct($request_context) {
    $this->request = $request_context;
  }

  public function dispatch() {
    // Attempt to load the controller.
    $controller_name = '\\AB\\Chroma\\Controllers\\' . $this->request->controller;
    $controller = new $controller_name($this);

    // Call the pre-action.
    $controller->pre_action();

    // Call the action and receive its data.
    $render_data = $controller->{$this->request->action}();

    // Figure out what view name to use (the action can change the view name, optionally).
    if (!empty($controller->view)) {
      $view = $controller->view;
    } else {
      $view = $this->request->controller . '/' . $this->request->action;
    }
    $view = $view . '.html';

    // Finally, render the view and echo it.
    if ($controller->auto_render) {
      echo $this->renderer->render($view, $render_data);
    } else {
      header('Content-Type: application/json');
      echo json_encode($render_data);
    }
  }
}
