<?php
/**
 * Chroma utility functions
 *
 * PHP Version 5
 *
 * @author    Aaron Bieber <aaron@aaronbieber.com>
 * @copyright 2016 All Rights Reserved
 * @version   git: $Id$
 */
namespace AB\Chroma;

class Util {
  /**
   * Do not allow construction.
   *
   * @return void
   */
  private function __construct() {
  }


  /**
   * Documentation
   *
   * @return void
   */
  public static function d($value) {
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
  }
}