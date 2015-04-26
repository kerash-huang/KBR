<?php
namespace db;
/**
 * Database Conenction
 * @author  Kerash <kerashman@gmail.com>
 * @date    2015/04/26
 *
 * DBSource Definition
 * array(
 *     "key" => array("host","dbname","user","password")
 *     "key2"=> array("host","dbname","user","password")
 * );
 *
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
            } catch(Exception $e) {
                throw new Exception($e->getMessage());
            }
        }
        return self::$instance;
    }

    public function getConnection($source) {
        if(!isset($DBSource)) {
            throw new Exception("Define the database connect information first. (use Database::loadConnection([Array])");
        }
        $SourceDefine = $DBSource[$source];
        if($SourceDefine) {
            return null;
        }
        $ActiveKey = $SourceDefine["host"].$SourceDefine["user"];
        if( !isset($ActiveConnection[$ActiveKey] )) {
            
        }
        return $ActiveConnection[$source];
    }

    abstract public function select($Column, $Table, $Condition, $Order, $Limit, $Connect, $isQueryShow);
    abstract public function insert($Table, $Column, $Data, $Connect, $isQueryShow);
    abstract public function update($Table, $Column, $Condition, $Connect, $isQueryShow);
    abstract public function delete($Table, $Condition, $Connect, $isQueryShow);
}
