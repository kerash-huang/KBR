<?php
namespace ezapi;

class Config {

    public $APIMode = "path";

    function __construct() {
        
    }

    public function SetAPIMode($mode) {
        $this->APIMode = $mode;
    }

}