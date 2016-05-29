<?php
/**
 * Lights functions
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

class Lights extends Collection
{
    private $hue_interface;
    private $bridge_ip = '192.168.10.30';

    public function __construct()
    {
        $this->hue_interface = Hue::getInstance();
        $this->load();
        usort($this->models, [ $this, 'lightNameCompare' ]);
    }

    private function lightNameCompare($a, $b)
    {
        return strcmp($a->name, $b->name);
    }

    public function setState(array $state, $light_id = 0)
    {
        if ($light_id == 0) {
            $success = true;
            foreach ($this as $light) {
                $ret = $this->hue_interface->setLightState($light->id, $state);
                if (!$ret) {
                    $success = false;
                    break;
                }
                usleep(100000);
            }
            return $success;
        } else {
            return $this->hue_interface->setLightState($light_id, $state);
        }
    }

    public function asArray()
    {
        $lights_array = [];

        // Create an array of each of the lights converted to an array. Simple.
        foreach ($this->models as $light) {
            $lights_array[] = $light->asArray();
        }

        return $lights_array;
    }

    public function load()
    {
        $response = $this->hue_interface->getLights();

        foreach ($response as $light_id => $light_data) {
            $light = new Light();
            $light->id = $light_id;
            $light->populate($light_data);
            $this->models[] = $light;
        }
    }
}
