<?php
function ezapi_autoload( $class ) {
    $basic_folder = __DIR__;
    $class = ltrim($class, '\\');
    $class = str_replace("\\", DIRECTORY_SEPARATOR, $class);
    $EzFile = $basic_folder.DIRECTORY_SEPARATOR.$class.".php";
    if(file_exists($EzFile)){
        require_once $EzFile;
    } else {
        echo "Fatal: file {$class} not found in system.";
    }
}

spl_autoload_register('ezapi_autoload');