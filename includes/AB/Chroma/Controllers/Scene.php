<?php
namespace AB\Chroma\Controllers;

class Scene extends Base {
  private $_scenes;

  public function pre_action() {
    // Get our scenes.
    $this->_scenes = new \AB\Chroma\Scenes();
    $this->_scenes->load();
  }

  public function choose() {
    $this->auto_render = false;

    if ($this->method == 'post') {
      $scene_id = (int) array_shift($this->args);

      if (is_numeric($scene_id)) {
        $scene = $this->_scenes->scenes[$scene_id];

        foreach ($scene->lights as $light) {
          if ($light->power == false) {
            $state = [
              'power' => false
            ];
          } elseif ($light->colormode == 'ct') {
            $state = [
              'power'  => true,
              'bri' => $light->bri,
              'ct'  => $light->ct
            ];
          } elseif ($light->colormode == 'hs') {
            $state = [
              'power'  => true,
              'hue' => $light->hue,
              'sat' => $light->sat,
              'bri' => $light->bri
            ];
          }

          $Lights = new \AB\Chroma\Lights();
          $ret = $Lights->set_state($state, $light->id);
          if (!$ret) {
            return ['success' => false];
          }
        } // foreach
      } // if is_numeric($scene_id)
      return true;
    } // if post
  } // choose()

  public function create() {
    $this->auto_render = false;

    // What is our new scene ID?
    $scene_id = max(array_keys($this->_scenes->scenes));
    $scene_id++;

    $this->_scenes->scenes[$scene_id] = new \AB\Chroma\Scene();
    $this->_scenes->scenes[$scene_id]->name = 'Untitled Scene';

    $Lights = new \AB\Chroma\Lights();
    foreach ($Lights->lights as $light) {
      $this->_scenes->scenes[$scene_id]->lights[$light->id] = $light;
    }

    $this->_scenes->save();

    return ['success' => true];
  }

  public function update() {
    $this->auto_render = false;

    if ($this->method == 'post') {
      $scene_id = (int) array_shift($this->args);
      if (is_numeric($scene_id)) {
        $scene = $this->_scenes->scenes[$scene_id];
        $updates = ['scene' => $scene_id];

        /* Indiscriminately set all scene values that exist in the post. Right now, the only value that can be set at
         * the scene level is "name." */
        foreach ($this->params as $key => $value) {
          if (isset($scene->{$key})) {
            $scene->{$key} = $value;
            $updates[$key] = $value;
            unset($this->params[$key]);
          }
        }

        if (!empty($this->params['light'])) {
          $light_id = $this->params['light'];
          foreach ($this->params as $key => $value) {
            if (isset($scene->lights[$light_id]->{$key})) {
              if (is_numeric($value)) {
                $value = (int) $value;
              } elseif ($value == 'true') {
                $value = true;
              } elseif ($value == 'false') {
                $value = false;
              }
              $scene->lights[$light_id]->{$key} = $value;
              unset($this->params[$key]);
            }
          }
        }

        $this->_scenes->save();

        return array_merge(['success' => true], $updates);
      }
    }
  }
}
