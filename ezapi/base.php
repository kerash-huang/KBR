<?php
namespace ezapi;

class base {
    private $main, $sub;

    function __construct() {
        $server = filter_input_array(INPUT_SERVER);
        $method_query = $server[0];
    }
}