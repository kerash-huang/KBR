<?php
/**
 * Ezapi implement page.
 * This is a very sample page for demo ezapi lib
 * 
 * 
 */
require_once "autoload.php";

$APIInst = new ezapi\Api();
$APIInst->DefaultRoute("Index","HelloWorld");



// 必要呼叫 [*MUST]
$APIInst->Run();