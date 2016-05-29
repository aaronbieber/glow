<?php
/**
 * Lights controller.
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

class Lights extends Base
{
    private $lights;

    public function __construct()
    {
        parent::__construct();

        // Get our lights.
        $this->lights = new \AB\Chroma\Lights();
    }

    public function get()
    {
        $this->render($this->lights->asArray(), Base::FORMAT_JSON);
    }
}
