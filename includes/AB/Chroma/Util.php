<?php
/**
 * Chroma utility functions
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

class Util
{
  /**
   * Do not allow construction.
   *
   * @return void
   */
    private function __construct()
    {
    }


  /**
   * Documentation
   *
   * @return void
   */
    public static function d($value)
    {
        echo '<pre>';
        var_dump($value);
        echo '</pre>';
    }
}
