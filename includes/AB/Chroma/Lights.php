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

    public function fromArray($lights_data)
    {
        foreach ($lights_data as $light_data) {
            $light_id = $light_data['id'];
            $light = $this->getModel();
            $light->populate([
                'id'        => $light_id,
                'name'      => $light_data['name'],
                'power'     => (bool) $light_data['power'],
                'colormode' => $light_data['colormode'],
                'ct'        => $light_data['ct'],
                'hue'       => $light_data['hue'],
                'sat'       => $light_data['sat'],
                'bri'       => $light_data['bri'],
                'included'  => array_key_exists('included', $light_data) ? $light_data['included'] : true
            ]);
            $this->models[] = $light;
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
            $light = $this->getModel();
            $light->id = $light_id;
            $light->populate($light_data);
            $this->models[] = $light;
        }

        usort($this->models, [ $this, 'lightNameCompare' ]);
    }

    protected function getModel()
    {
        return new Light();
    }

    private function lightNameCompare($a, $b)
    {
        return strcmp($a->name, $b->name);
    }
}
