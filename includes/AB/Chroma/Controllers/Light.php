<?php
/**
 * Light controller
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

class Light extends Base
{
    public function get($light_id)
    {
        $light = new \AB\Chroma\Light();
        $light->loadById($light_id);
        $this->render($light->asArray(), Base::FORMAT_JSON);
    }

    public function post($light_id)
    {
        $light = new \AB\Chroma\Light();
        $light->loadById($light_id);
        $light->populate($this->params);

        // Only set the power attribute if it was supplied.
        if ($this->param('power') !== null) {
            $light->power = (bool) $this->param('power');
        }

        $light->save();

        $this->render($light->asArray(), Base::FORMAT_JSON);
    }
}
