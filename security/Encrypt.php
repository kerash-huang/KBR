<?php
namespace security;
class Encrypt {

    public function DES_CBC($text, $key, $iv, $base64 = true) {
        
        $td        = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_CBC, '');
        $KeySize   = mcrypt_get_key_size(MCRYPT_DES,MCRYPT_MODE_CBC);
        $BlockSize = mcrypt_get_block_size(MCRYPT_DES,MCRYPT_MODE_CBC);

        $str = self::pkcs5padding($str, $BlockSize);

        $hashkey = sha1($key);

        mcrypt_generic_init($td, substr($hashkey, 0, $KeySize) , $iv);

        $enc = mcrypt_generic($td, $str);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        if($base64) {
            $enc = base64_encode($enc);
        }
        return $enc;
    }


    private function pkcs5padding($str, $blocksize) {
        $padsize = $blocksize - (strlen($str) % $blocksize);
        $str .= str_repeat(chr($padsize), $padsize);
        return $str;
    }








}