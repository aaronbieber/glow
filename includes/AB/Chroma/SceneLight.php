<?php
/**
 * A single light object for use in a scene. Contains scene-specific properties and
 * methods.
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

class SceneLight extends Light
{
    public $included = false;

    public function asArray()
    {
        // Get all public properties as an associative array.
        $light_array = parent::asArray();
        $light_array['included'] = $this->included;
        return $light_array;
    }
}
