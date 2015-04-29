<?php
namespace db;
/**
 * @descript  Database Conenction
 * @author    Kerash <kerashman@gmail.com>
 * @date      2015/04/26
 * @last      2015/04/29
 * @version   v1.0b
 * @history   
 *
 * $DBSource definition struct
 * array(
 *     "key" => array("host","dbname","user","password",["dbtype"])
 *     "key2"=> array("host","dbname","user","password",["dbtype"])
 * );
 * 
 */

class Database {
    private static $DBSource = null;
    private static $ActiveConnection = array();
    private static $instance;

    public static function loadConnection( $source ) {
        self::$DBSource = $source;
    }

    public static function getInstance() {
        if(self::$instance == null) {
            try {
                self::$instance = new Database;
            } catch(\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }
        return self::$instance;
    }

    public function getConnection($source) {
        if(!isset(self::$DBSource)) {
            throw new \Exception("Define the database connect information first. (use Database::loadConnection([Array])");
        }
        $SourceDefine = self::$DBSource[$source];
        if(!$SourceDefine) {
            return null;
        }

        $ActiveKey = str_replace(".","_",$SourceDefine["host"]).$SourceDefine["user"];
        if( !isset($ActiveConnection[$ActiveKey] )) {
            $myPdo = new MyPdo($SourceDefine["host"], $SourceDefine["dbname"], $SourceDefine["user"], $SourceDefine["password"],$SourceDefine["dbtype"]);
            $ActiveConnection[$ActiveKey] = $myPdo;
        }
        return $ActiveConnection[$ActiveKey];
    }
}
