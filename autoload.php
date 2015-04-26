<?php
function __autoload ( $Class ) {
    $Class = str_replace("\\", DIRECTORY_SEPARATOR, $Class);
    require_once __DIR__.DIRECTORY_SEPARATOR.$Class.".php";
}