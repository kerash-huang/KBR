<?php
function __autoload ( $Class ) {

    require_once __DIR__.$Class;

}