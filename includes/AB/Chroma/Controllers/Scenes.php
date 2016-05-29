<?php
/**
 * Scenes controller.
 *
 * PHP Version 5
 *
 * @category  Glow
 * @package   Glow
 * @author    Aaron Bieber <aaron@aaronbieber.com>
 * @copyright 2016 All Rights Reserved
 * @license   GNU GPLv3
 * @version   GIT: $Id$
 * @link      http://github.com/aaronbieber/glow
 */
namespace AB\Chroma\Controllers;

class Scenes extends Base
{
    public function __construct()
    {
        parent::__construct();

        // Get our scenes.
        $this->scenes = new \AB\Chroma\Scenes();
        $this->scenes->load();
    }

    public function get()
    {
        $this->render($this->scenes->asArray(), Base::FORMAT_JSON);
    }

    public function put()
    {
        // Index the scene sort values by scene ID.
        $scenes = [];
        foreach ($this->params as $model) {
            $scenes[$model->id] = $model->sort;
        }

        // Set the sort values of the actual scenes to their new sort values.
        foreach ($this->scenes as $scene) {
            $scene->sort = $scenes[$scene->id];
        }

        if ($this->scenes->save()) {
            $this->render([ 'success' => true ], Base::FORMAT_JSON);
        }
    }

  /**
   * Select a scene by name (/scenes/by_name/:name)
   *
   * @return void
   */
    public function post($name = null)
    {
        $name = str_replace('+', ' ', $name);
        $scene = $this->scenes->findByName($name, \AB\Chroma\Scenes::FLAG_ICASE);
        if ($scene) {
            $scene->set();
        }

        $this->render([ 'success' => true ], Base::FORMAT_JSON);
    }
}
