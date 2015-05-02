<?php
namespace db;
/**
 * @descript  Database Conenction
 * @author    Kerash <kerashman@gmail.com>
 * @date      2015/04/26
 * @last      2015/05/02
 * @version   v1.0.2b
 *     
 * $DBSource definition struct
 * array(
 *     "key" => array("host","dbname","user","password",["dbtype"])
 *     "key2"=> array("host","dbname","user","password",["dbtype"])
 * );
 * 
 */

define("ERROR_ALL", 0x103);
define("ERROR_FILE", 0x105);
define("ERROR_EXCEPTION", 0x107);
define("ERROR_ECHO", 0x109);

class Database extends DBException {
    private static $DBSource = null;
    private static $ActiveConnection = array();
    private static $instance;

    private static $ErrorLevel = ERROR_FILE;
    private static $ErrorLogDir = "./C_DB_LOG/";
    private static $ErrorFile  = "Database.Error.Logfile.log";


    function __construct() {

    }

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

    public static function SetErrorLevel($level) {
        self::$ErrorLevel = $level;
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

    public function _Error($_FuncName, $_Message = "" ) {
        switch(self::ErrorLevel) {
            case ERROR_ALL:

            break;
            case ERROR_FILE:
                if(!file_exists(self::ErrorLogDir)) {
                    mkdir(self::ErrorLogDir);
                }
                file_put_contents(self::ErrorLogDir.self::ErrorFile, "[{$_FuncName}] {$_Message}", FILE_APPEND);
            break;
            case ERROR_EXCEPTION:
            break;
            case ERROR_ECHO:
                echo "<b>Database error message</b><br> [{$_FuncName}] {$_Message}";
            break;
        }
    }
}
