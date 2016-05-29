<?php
/**
 * The Home controller basically handles all of the page display.
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

/**
 * Class Home
 *
 * @author Aaron Bieber <aaron@aaronbieber.com>
 */
class Home extends Base
{
    private $scenes;
    private $lights;

    public function __construct()
    {
        parent::__construct();
        // Get our scenes.
        $this->scenes = new \AB\Chroma\Scenes();
        $this->scenes->load();

        // Get our lights.
        $this->lights = new \AB\Chroma\Lights();
    }

  /**
   * Function index
   */
    public function get()
    {
        // Create a flat array of light data, which we will then manipulate and append to.
        $lights = [];

        // Transform or append properties as necessary for the view.
        foreach ($this->lights as $light) {
            $light_array = $light->asArray();

            // Set the swatch text based on settings.
            if (empty($light->ct)
            && ( empty($light->hue)
              || empty($light->sat)
               )
            ) {
                $light_array['swatch_text'] = '?';
            } else {
                $light_array['swatch_text'] = ' ';
            }

            $ct_active = $hs_active = false;
            $ct_active_data = $hs_active_data = 'false';
            if ($light->colormode == 'ct') {
                $ct_active = true;
                $ct_active_data = 'true';
            } elseif ($light->colormode == 'hs') {
                $hs_active = true;
                $hs_active_data = 'true';

                $hue = floor(($light->hue * 255) / 65535);
                $sat = floor(($light->sat * 100) / 255);
                $bri = floor(($light->bri * 100) / 255);
            }

            $lights[] = $light_array;
        }

        /* All lights row!
         *
         * The "All Lights" row should reflect the value of all lights only if they
         * are the same, and some defaults otherwise. What we do is snag all of the
         * attributes of the first light by index, then loop over each of the
         * remaining lights comparing all of their values to those of the first
         * light. As soon as any value differs, remove it from the final state array,
         * then use the final state array to draw the All Lights row.
         */
        $all_state = $lights[0];

        for ($i = 1; $i < count($lights); $i++) {
            $light = $lights[$i];

            /* Compare all other attributes. If the attribute is different, remove it
             * from the final "state" array. Ultimately, the "state" array will contain
             * only the attributes that are identical across all lights.
             */
            foreach ($all_state as $attr => $value) {
                if ($all_state[$attr] != $light[$attr]) {
                    unset($all_state[$attr]);
                }
            }
        }

        // Set the swatch text based on settings.
        if (empty($all_state['ct'])
        && ( empty($all_state['hue'])
        || empty($all_state['sat'])
         )
        ) {
            $all_state['hex'] = '#efefef';
            $all_state['swatch_text'] = '?';
        } else {
            $all_state['swatch_text'] = ' ';
        }

        // Synthesize the name for this light, which is displayed in the row.
        $all_state['name'] = 'All Lights';
        $all_state['id'] = 0;

        // Append it to the lights array.
        $lights[] = $all_state;

        $this->render([
        'lights' => $lights,
        'scenes' => $this->scenes->asArray()
        ]);
    }
}
