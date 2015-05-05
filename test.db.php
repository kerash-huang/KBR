<?php
require_once "autoload.php";
header("Content-type: text/html; charset='utf-8'");
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

// insert pair test
$r1 = $testdb->insert("mytable", array("name"), array("Test"));
echo "R1<br>";
html_pre($r1);

$r2 = $testdb->insert("mytable", array("name"=>"Test2"));
echo "R2<br>";
html_pre($r2);
$r3 = $testdb->insert("mytable", "`name`","'Test3'");
echo "R3<br>";
html_pre($r3);
$r4 = $testdb->insert("mytable", "", array(null,"中文"));
echo "R4<br>";
html_pre($r4);
$r5 = $testdb->insert("mytable", "", "null,'Test5'");
echo "R5<br>";
html_pre($r5);

$OneResult = $testdb->selectone("*", "mytable", "id>5");
var_dump($OneResult);


// $testdb->delete("mytable", "", "order by id asc", "limit 5");


// calc found rows
$testdb->SetCalcRows(true);
$SelectResult = $testdb->select("*", "mytable", "" , array("order"=>"id desc"), "limit 3");
$testdb->SetCalcRows(false);
html_pre($SelectResult);
echo "Found Rows:" . $testdb->GetResultRowNum();



function html_pre($content, $dump = 1) {
    echo "<pre>";
    if($dump) {
        var_dump($content);
    } else {
        echo $content;
    }
    echo "</pre>";
}