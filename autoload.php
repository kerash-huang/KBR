<?php
function autoload( $class ) {
    $basic_folder = __DIR__;
    $class = ltrim($class, '\\');
    $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);

    if(file_exists($basic_folder.DIRECTORY_SEPARATOR.$class.".php")){
        require_once $basic_folder.DIRECTORY_SEPARATOR.$class.".php";
    } else {

    }
}

spl_autoload_register('autoload');