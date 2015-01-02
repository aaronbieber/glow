<?php
/**
 * The base class for collections of models.
 *
 * @copyright 2015 Aaron Bieber, All Rights Reserved
 */
namespace AB\Chroma;

abstract class Collection implements \Iterator, \ArrayAccess {
  protected $models = [];
  protected $position = 0;

  public function rewind() {
    $this->position = 0;
  }

  public function current() {
    return $this->models[$this->position];
  }

  public function key() {
    return $this->position;
  }

  public function next() {
    if (++$this->position >= count($this->models)) {
      return false;
    } else {
      return $this->models[$this->position];
    }
  }

  public function valid() {
    return isset($this->models[$this->position]);
  }

  public function offsetSet($offset, $value) {
    if (is_null($offset)) {
      $this->models[] = $value;
    } else {
      $this->models[$offset] = $value;
    }
  }

  public function offsetExists($offset) {
    return isset($this->models[$offset]);
  }

  public function offsetUnset($offset) {
    unset($this->models[$offset]);
  }

  public function offsetGet($offset) {
    return isset($this->models[$offset]) ? $this->models[$offset] : null;
  }
}