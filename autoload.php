<?php
function autoload( $class ) {
    $basic_folder = __DIR__;
    $class = ltrim($class, '\\');
    $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);
    require_once $basic_folder.DIRECTORY_SEPARATOR.$class.".php";
}
spl_autoload_register('autoload');