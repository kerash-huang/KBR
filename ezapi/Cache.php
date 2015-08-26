<?php
namespace ezapi;

class Cache {
    private static $base_cache_path;

    private static $tmp_cache_box;

    /**
     * 設定快取
     * @param string $type
     * @param string $filename
     * @param mixed  $content
     */
    public static function SetCache( $type = "file" , $filename, $content ) {
        self::$base_cache_path = __DIR__."/cache/";

        if( $type === "file" )
            self::SetFileCache($filename, $content);
        else if( $type === "box" )
            self::SetTmpCache($filename, $content);

    }

    /**
     * 設定檔案快取
     * @param string $filename
     * @param mixed  $content
     */
    public static function SetFileCache( $filename, $content ) {
        $filename = strpos($filename, ".txt") === false ? $filename.".txt" : $filename ;
        return file_put_contents(self::$base_cache_path.$filename, $content);
    }

}