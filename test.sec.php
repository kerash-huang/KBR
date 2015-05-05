<?php
require_once "autoload.php";
$str = "this is a test";
$key = "test";

$ivbytes = array(72, 163, 99, 62, 219, 111, 163, 114);
$iv = implode(array_map("chr", $ivbytes));

echo security\Encrypt::DES_CBC($str, $key, $iv, true);