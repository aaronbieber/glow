<?php
/**
 * A collection of Scene objects.
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

class Scenes extends Collection
{
    const FLAG_ICASE = 1;
    public $errors = [];

  /**
   * Get the Scene with the given ID, and leave the internal pointer pointed at that scene object.
   *
   * @param int $id The ID of the Scene you're looking for.
   *
   * @return bool|\AB\Chroma\Scene The Scene with the ID given, or FALSE if not found.
   */
    public function findById($id)
    {
        foreach ($this as $scene) {
            if ($scene->id == $id) {
                return $scene;
            }
        }
        return false;
    }

    public function findByName($name, $flags = null)
    {
        $name = ($flags & self::FLAG_ICASE) ? strtolower($name) : $name;
        foreach ($this as $scene) {
            $scene_name = ($flags & self::FLAG_ICASE) ? strtolower($scene->name) : $scene->name;
            if ($scene_name == $name) {
                return $scene;
            }
        }
        return false;
    }

    public function load(\AB\Chroma\Lights $lights = null)
    {
        $scenes_yaml = file_get_contents('data/scenes.yml');
        $this->models = $this->fromArray(\yaml_parse($scenes_yaml), $lights);
        usort($this->models, array($this, 'compareSceneSorts'));

        foreach ($this as $scene) {
            usort($scene->lights, array($this, 'compareLightNames'));
        }
    }

    public function save()
    {
        if ($this->validate()) {
            $scenes_yaml = yaml_emit($this->asArray());
            $fp = fopen('data/scenes.yml', 'w');
            fwrite($fp, $scenes_yaml);
            fclose($fp);
            return true;
        }

        return false;
    }

    public function validate()
    {
        foreach ($this->models as $scene) {
            if (!$scene->validate()) {
                $this->errors[] = 'Errors in scene ' . $scene->id . ': ' . implode(', ', $scene->errors);
            }
        }

        return empty($this->errors);
    }

    private function compareSceneNames($a, $b)
    {
        return strcmp($a->name, $b->name);
    }

    private function compareSceneSorts($a, $b)
    {
        if ($a->sort == $b->sort) {
            return 0;
        }
        if ($a->sort < $b->sort) {
            return -1;
        }
        if ($a->sort > $b->sort) {
            return 1;
        }
    }

    private function compareLightNames($a, $b)
    {
        return strcmp($a->name, $b->name);
    }

    private function fromArray($self_array, $lights = [])
    {
        if ($lights instanceof \AB\Chroma\Lights) {
            $new_lights = [];
            foreach ($lights as $light) {
                $new_light = new SceneLight();
                $new_light->populate($light->asArray());
                $new_light->included = false;
                $new_light->power = false;
                $new_lights[$light->id] = $new_light;
            }
            $lights = $new_lights;
        }
        $scenes = [];

        foreach ($self_array as $scene) {
            $new_scene         = new Scene();
            $new_scene->id     = $scene['id'];
            $new_scene->name   = $scene['name'];
            $new_scene->sort   = $scene['sort'];
            $new_scene->lights = $lights;

            foreach ($scene['lights'] as $light) {
                $light_id = $light['id'];
                $new_scene->lights[$light_id] = new SceneLight([
                'id'        => $light_id,
                'name'      => $light['name'],
                'power'     => (bool) $light['power'],
                'colormode' => $light['colormode'],
                'ct'        => $light['ct'],
                'hue'       => $light['hue'],
                'sat'       => $light['sat'],
                'bri'       => $light['bri'],
                'included'  => true
                ]);
            }

            $scenes[] = $new_scene;
        }

        return $scenes;
    }

    public function asArray()
    {
        $self_array = [];

        foreach ($this->models as $scene_id => $scene) {
            $scene_lights = [];
            foreach ($scene->lights as $light) {
                $scene_lights[] = $light->asArray();
            }
            $self_array[$scene_id] = [
            'id'     => $scene->id,
            'name'   => $scene->name,
            'sort'   => $scene->sort,
            'lights' => $scene_lights
            ];
        }

        return $self_array;
    }
}
