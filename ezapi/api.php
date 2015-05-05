<?php
namespace ezapi;

class api extends base {
    private static $instance = null;
    public $resp = array();
    private $pdo = null;
    public static function call() {
        if(self::$instance == null) {
            self::$instance = new api;
        }
        if(!method_exists("api", $method)){
            return self::ret(__LINE__, "method not found");
        } else {

            self::$instance->$method();
            $ret = self::$instance->resp;
            $err = isset($ret["error"])?$ret["error"]:__LINE__;
            unset($ret["error"]);
            return self::ret($err, $ret);
        }
    }

    function __construct() {
        $this->resp = array("error"=>__LINE__, "message"=>__CLASS__);
        parent::__construct();
    }

    public static function ret($err, $ret) {
        $return = array_merge( (array)array("error"=>$err) , (array)$ret);
        return json_encode($return);
    }
}
