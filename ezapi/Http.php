<?php
namespace ezapi;

class Http {

    private static $HttpStatusCodeStr = array(
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        500 => "Internal Server Error"
    );


    public static $_Curl_SSL = false;
    public static $_Curl_Timeout = 20;

    /**
     * 取得 HTTP/1.1 狀態標頭
     * @param [type] $status_code [description]
     */
    public static function GetStatus($status_code) {
        $ResponseHeader = "HTTP 1.1 ";
        if(isset(self::$HttpStatusCodeStr[$status_code])) {
            return $ResponseHeader." ". $status_code . " " . self::$HttpStatusCodeStr[$status_code];
        } else {
            return $ResponseHeader." ". $status_code;
        }
    }

    /**
     * 取得狀態文字
     * @param [type] $status_code [description]
     */
    public static function GetStatusText($status_code) {
        return self::$HttpStatusCodeStr[$status_code] ? self::$HttpStatusCodeStr[$status_code] : "";
    }

    public static function CurlGet($url) {

    }

    public static function CurlPost($url, $post_param) {
        
    }

}