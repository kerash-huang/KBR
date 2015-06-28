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

    public static function CurlGet($TargetUrl,$TLSPort = 443) {
        if(substr($TargetUrl,0,4)!="http"){ 
            $TargetUrl="http://".$TargetUrl;
        }
        $curl = curl_init();
        curl_setopt($curl,CURLOPT_URL,$TargetUrl);
        curl_setopt($curl,CURLOPT_HEADER,false);
        curl_setopt($curl,CURLOPT_ENCODING,"UTF-8");
        curl_setopt($curl,CURLOPT_HTTPHEADER,array("Content-type: text/html;charset=\"utf-8\""));
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT,40);
        curl_setopt($curl,CURLOPT_TIMEOUT,40);
        // curl_setopt($curl,CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

        if(substr($TargetUrl,0,5)=="https"){
            curl_setopt($curl, CURLOPT_PORT,$TLSPort);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);
        }
        $recall = curl_exec($curl);
        if(!$recall) {
            curl_close($curl);
            return "";
        }
        curl_close($curl);
        return $recall;

    }

    public static function CurlPost($TargetUrl, $post_param) {
        
    }


    public static function OutputHeader($header) {
        
    }
}