<?php
/**
 * Controller to handle all configuration-related requests.
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

class Config extends Base
{
    public function get()
    {
        echo "Configuration*";
        $config = \AB\Chroma\Config::getInstance();
        $this->render($config->getAll(), Base::FORMAT_JSON);
    }
}
