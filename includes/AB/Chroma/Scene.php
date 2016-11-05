<?php
/**
 * A scene, comprising the settings of all lights in the system.
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
namespace AB\Chroma;

class Scene extends Collection
{
    public $name = '';
    public $sort = 0;

    public function __construct()
    {
    }

    public function validate()
    {
        if (empty($this->name)) {
            $this->errors[] = 'Name is empty!';
        }
        if (empty($this->lights)) {
            $this->errors[] = 'Scene has no lights!';
        }

        return empty($this->errors);
    }

    public function fromArray($lights)
    {
        foreach ($lights as $light) {
            $sceneLight = $this->getModel();

            $sceneLight->populate([
                'id'        => $light['id'],
                'name'      => $light['name'],
                'power'     => (bool) $light['power'],
                'colormode' => $light['colormode'],
                'ct'        => $light['ct'],
                'hue'       => $light['hue'],
                'sat'       => $light['sat'],
                'bri'       => $light['bri'],
                'included'  => array_key_exists('included', $light) ? $light['included'] : true
            ]);

            $this->models[] = $sceneLight;
        }
    }

    public function replaceOrInsertFromArray($lights)
    {
        if (!$this->replaceFromArray($lights)) {
            $new_light = $this->getModel();
            $new_light->populate($lights);
            $this->models[] = $new_light;
        }
    }

    public function replaceFromArray($lights)
    {
        if (empty($lights)) {
            return false;
        }

        // If we're given just one light, pretend we got a list with one light in it.
        if (array_key_exists('id', $lights)) {
            $lights = [$lights];
        }

        foreach ($lights as $light) {
            $index = $this->findById($light['id']);
            if ($index !== false) {
                $new_light = $this->getModel();
                $new_light->populate($light);
                $this->models[$index] = $new_light;

                return true;
            }
        }

        return false;
    }

    /**
     * Function asArray
     *
     * @return array Array of light data.
     */
    public function asArray()
    {
        $lights = [];
        foreach ($this as $light) {
            $lights[] = $light->asArray();
        }

        return $lights;
    }

    /**
     * Function asSettingsArray
     *
     * @return array An array suitable for setting light state.
     */
    public function asSettingsArray()
    {
        $lights_arrays   = $this->asArray();
        $lights_settings = [];

        // Remove unnecessary elements.
        foreach ($lights_arrays as $light) {
            $lights_settings[$light['id']] = [];
            foreach ($light as $setting => $value) {
                $acceptable_settings =
                array_merge(
                    ['power'],
                    $light['colormode'] == 'ct' ? ['ct', 'bri'] : ['hue', 'sat', 'bri']
                );

                if (in_array($setting, $acceptable_settings)) {
                    $lights_settings[$light['id']][$setting] = $value;
                }
            }
        }

        return $lights_settings;
    }

    public function findById($id)
    {
        for ($i = 0; $i < count($this); $i++) {
            if ($this->models[$i]->id == $id) {
                return $i;
            }
        }

        return false;
    }

    public function set()
    {
        foreach ($this->models as $light) {
            $light->save();
            usleep(100000);
        }
    }

    protected function getModel()
    {
        return new SceneLight();
    }
}
