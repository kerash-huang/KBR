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
$testdb->query("INSERT INTO mytable (id, name) VALUES (null, 'Hello') ");
