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

    public function load(\AB\Chroma\Lights $lights = null)
    {
        $scenes_yaml = file_get_contents('data/scenes.yml');
        $this->fromArray(\yaml_parse($scenes_yaml), $lights);
        usort($this->models, array($this, 'compareSceneSorts'));
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
        $scenes = [];

        if ($lights instanceof \AB\Chroma\Lights) {
            $new_lights = [];
            foreach ($lights as $light) {
                $new_light = $light->asArray();
                $new_light['included'] = false;
                $new_lights[] = $new_light;
            }
            $lights = $new_lights;
        }

        foreach ($self_array as $scene) {
            array_walk(
                $scene['lights'],
                function (&$light) {
                    $light['included'] = true;
                }
            );

            $new_scene         = $this->getModel();
            $new_scene->id     = $scene['id'];
            $new_scene->name   = $scene['name'];
            $new_scene->sort   = $scene['sort'];
            $new_scene->fromArray($lights);
            $new_scene->replaceFromArray($scene['lights']);

            $scenes[] = $new_scene;
        }

        $this->models = $scenes;
    }

    public function asArray()
    {
        $self_array = [];

        foreach ($this as $scene) {
            $self_array[] = [
                'id'     => $scene->id,
                'name'   => $scene->name,
                'sort'   => $scene->sort,
                'lights' => $scene->asArray()
            ];
        }

        return $self_array;
    }

    protected function getModel()
    {
        return new Scene();
    }
}
