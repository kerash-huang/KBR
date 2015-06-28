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

// $APIInst->AddRoute("get" , "/",            array("controller"=>"Index","action"=>"Home"));
// $APIInst->AddRoute("get" , "/home",        array("controller"=>"Home"));
// $APIInst->AddRoute("get" , "/home/:name",  array("controller"=>"Home","action"=>"WhoAmI"));

$APIInst->AddRoute("get" , "/emb",  array("controller"=>"Emb","action"=>"AllList"));
$APIInst->AddRoute("get" , "/emb/:condition",  array("controller"=>"Emb","action"=>"FindAll"));
$APIInst->AddRoute("get" , "/emb/Name/:name", array("controller"=>"Emb","action"=>"FindName"));
$APIInst->AddRoute("get" , "/emb/Hospital/:name", array("controller"=>"Emb","action"=>"FindHospital"));

// $APIInst->AddRoute("post", "/user/create", array("controller"=>"home","action"=>"create"));
// 必要呼叫 [*MUST]
$APIInst->Run();