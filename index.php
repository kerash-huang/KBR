<?php
/**
 * Ezapi implement page.
 * This is a very sample page for demo ezapi lib
 * 
 * 
 */

error_reporting(E_ALL);
ini_set("display_errors",1);

require_once "autoload.php";

$APIInst = new ezapi\Api();

$APIInst->DefaultRoute("Index","HelloWorld");

$APIInst->AddRoute("get" , "/",            array("controller"=>"Index","action"=>"Home"));
$APIInst->AddRoute("get" , "/home",        array("controller"=>"Home"));
$APIInst->AddRoute("get" , "/home/:name",  array("controller"=>"Home","action"=>"WhoAmI"));
// $APIInst->AddRoute("post", "/user/create", array("controller"=>"home","action"=>"create"));
// 必要呼叫 [*MUST]
$APIInst->Run();