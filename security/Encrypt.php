<?php
namespace security;
class Encrypt {

    public static function DES_CBC($string, $key, $iv = "", $base64 = true) {
        $blockSize = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC);
        $string = self::pkcs5padding($string, $blockSize);
        $encoded = mcrypt_encrypt(MCRYPT_DES, $key, $string, MCRYPT_MODE_CBC, $iv);
        if($base64) {
            $encoded = base64_encode($encoded);
        }
        return $encoded;
    }

    public static function pkcs5padding($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    
}