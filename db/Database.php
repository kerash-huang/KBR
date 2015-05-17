<?php
namespace db;
/**
 * @descript      Database Conenction
 * @author        Kerash <kerash@livemail.com>
 * @start         2015/04/26
 * @last-modify   2015/05/06
 * @version       v1.2.0 release
 *     
 * $DBSource definition struct
 * array(
 *     "key" => array("host","dbname","user","password",["dbtype"])
 *     "key2"=> array("host","dbname","user","password",["dbtype"])
 * );
 * 
 */

define("ERROR_ALL", 0x103, true);
define("ERROR_FILE", 0x105, true);
define("ERROR_EXCEPTION", 0x107, true);
define("ERROR_ECHO", 0x109, true);

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
        register_shutdown_function("db\Database::destruct");
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

    public static function destruct() {
        if(count(self::$ActiveConnection)>0) {
            foreach(self::$ActiveConnection as $name => $Connect) {
                $Connect->handle = null;
                unset(self::$ActiveConnection[$name]);
            }
        }
        return;
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
        switch(self::$ErrorLevel) {
            case ERROR_ALL:

            break;
            case ERROR_FILE:
                if(!file_exists(self::$ErrorLogDir)) {
                    mkdir(self::$ErrorLogDir,0777);
                }
                if(file_exists(self::$ErrorLogDir)) {
                    file_put_contents(self::$ErrorLogDir.self::$ErrorFile, "[{$_FuncName}] {$_Message}", FILE_APPEND);
                }
            break;
            case ERROR_EXCEPTION:
            break;
            case ERROR_ECHO:
                echo "<b>Database error message</b><br> [{$_FuncName}] {$_Message}";
            break;
        }
    }

}
