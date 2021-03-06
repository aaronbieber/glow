<?php
/**
 * Scene controller.
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

class Scene extends Base
{
    private $scenes;

    public function __construct()
    {
        parent::__construct();

        // Get all lights to overlay scenes onto.
        $lights = new \AB\Chroma\Lights();
        $lights->load();

        // Get our scenes.
        $this->scenes = new \AB\Chroma\Scenes();
        $this->scenes->load($lights);
    }

  /**
   * Create a new scene.
   *
   * @return void
   */
    public function post()
    {
        // What is our new scene ID?
        $scene_id = array_reduce(
            $this->scenes->asArray(),
            function ($carry, $item) {
                if ($item['id'] > $carry) {
                    return $item['id'];
                } else {
                    return $carry;
                }
            },
            0 // Initial value
        );
        $scene_id++;

        $new_scene = new \AB\Chroma\Scene();
        $new_scene->id = $scene_id;
        $new_scene->name = $this->params['name'];

        foreach ($this->params['lights'] as $light) {
            $light_model = new \AB\Chroma\Light();
            $light_model->populate((array) $light);
            $new_scene->lights[] = $light_model;
        }

        $this->scenes[] = $new_scene;
        var_dump($this->scenes);
        var_dump($this->scenes->save());
        //$this->render($this->scenes->asArray(), Base::FORMAT_JSON);
    }

    public function postLight($scene_id, $light_id)
    {
        $scene = $this->scenes->findById($scene_id);

        foreach ($scene as $light) {
            echo "{$light->name}: id: {$light->id}, pow: {$light->power}, bri: {$light->bri}, ct: {$light->ct}, hue: {$light->hue}, sat: {$light->sat}\n";
        }

        $scene->replaceOrInsertFromArray($this->params);

        foreach ($scene as $light) {
            echo "{$light->name}: id: {$light->id}, pow: {$light->power}, bri: {$light->bri}, ct: {$light->ct}, hue: {$light->hue}, sat: {$light->sat}\n";
        }

        // if ($this->scenes->save()) {
        //     $this->render($this->scenes->asArray(), Base::FORMAT_JSON);
        // } else {
        //     $this->renderError([ 'errors' => $this->scenes->errors ], Base::FORMAT_JSON);
        // }
    }

    /**
     * Edit a scene (patch).
     *
     * @return void
     */
    public function put($scene_id)
    {
        $scene = $this->scenes->findById($scene_id);

        /* Indiscriminately set all scene values that exist in the post. Right now, the only value that can be set at
         * the scene level is "name." */
        foreach ($this->params as $key => $value) {
            if (isset($scene->{$key}) && is_scalar($scene->{$key})) {
                $scene->{$key} = $value;
                unset($this->params[$key]);
            }
        }

        if (!empty($this->params['lights'])) {
            foreach ($this->params['lights'] as $light) {
                $light_id = $light->id;
                foreach ($light as $property => $value) {
                    if (isset($scene->lights[$light_id]->{$property})) {
                        if (is_numeric($value)) {
                            $value = (int) $value;
                        } elseif ($property == 'power') {
                            $value = (bool) $value;
                        }
                        $scene->lights[$light_id]->{$property} = $value;
                    }
                }
            }
        }

        if ($this->scenes->save()) {
            $this->render($this->scenes->asArray(), Base::FORMAT_JSON);
        } else {
            $this->renderError([ 'errors' => $this->scenes->errors ], Base::FORMAT_JSON);
        }
    }
}
