<?php
namespace ezapi;

class Common {

    public static $Box = array();


    public static $_Public_Get  = array();
    public static $_Public_Post = array();

    /**
     * 設定共有參數
     * @param string $Prefix
     * @param string $Key
     * @param mixed  $Value
     */
    public static function SetParam($Key, $Value, $Prefix = "") {
        $IndexKey = $Prefix.$Key;
        self::$Box[$IndexKey] = htmlentities($Value, ENT_QUOTES, "utf-8");
    }

    /**
     * 取得共有參數
     * @param string $Prefix
     * @param string $Key
     */
    public static function GetParam($Key, $Prefix = "") {
        $IndexKey = $Prefix.$Key;
        if(isset(self::$Box[$IndexKey])) {
            return self::$Box[$IndexKey];
        } else {
            return null;
        }
    }
}