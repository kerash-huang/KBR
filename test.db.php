<?php
require_once "autoload.php";

/**
 * import.sql
 *
    CREATE TABLE IF NOT EXISTS `mytable` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(32) NOT NULL,
      PRIMARY KEY (`id`)
    ) 
    ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
 */

$db_data = array (
    "local" => array ("dbtype"=>"mysql", "host"=>"127.0.0.1", "dbname"=>"test", "user"=>"root", "password"=>"admin")
);

db\Database::loadConnection($db_data);
$testdb = db\Database::getInstance()->getConnection("local");

db\Database::SetErrorLevel(ERROR_FILE);
// $testdb->query("INSERT INTO mytable (id, name) VALUES (null, 'Hello') ");

$SelectResult = $testdb->select("*", "mytable", " id < 5 " , array("order"=>"id desc"),"",1);
html_pre($SelectResult);


function html_pre($content, $dump = 1) {
    echo "<pre>";
    if($dump) {
        var_dump($content);
    } else {
        echo $content;
    }
    echo "</pre>";
}