<?php
function __autoload ( $Class ) {
    $basic_folder = __DIR__;

    $Class = str_replace("\\", DIRECTORY_SEPARATOR, $Class);
    
    require_once $basic_folder.DIRECTORY_SEPARATOR.$Class.".php";
}