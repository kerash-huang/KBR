<?php
namespace ezapi;

class Cache {

    private $base_cache_path = __DIR__."/cache/";   

    public static function SetFileCache( $filename, $content ) {

        return file_put_contents($base_cache_path.$filename, $content);

    }







}