<?php
function autoload($class) {
  $class = ltrim($class, '\\');
  $fileName  = '';
  $namespace = '';
  if ($lastNsPos = strripos($class, "\\")) {
    $namespace = substr($class, 0, $lastNsPos);
    $class = substr($class, $lastNsPos + 1);
    $fileName  = str_replace("\\", DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
  }
  $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';

  require LIBRARY_PATH . DIRECTORY_SEPARATOR . $fileName;
}

spl_autoload_register('autoload');
