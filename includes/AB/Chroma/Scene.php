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

class Scene
{
    public $name = '';
    public $lights = [];
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

  /**
   * Function asArray
   *
   * @return array Array of light data.
   */
    public function asArray()
    {
        $lights = [];
        foreach ($this->lights as $light) {
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

    public function set()
    {
        foreach ($this->lights as $light) {
            $light->save();
            usleep(100000);
        }
    }
}
