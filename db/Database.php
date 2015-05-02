<?php
namespace db;
/**
 * @descript  Database Conenction
 * @author    Kerash <kerashman@gmail.com>
 * @date      2015/04/26
 * @last      2015/05/02
 * @version   v1.0.1b
 * @history   
 *     v1.0.1b  build select function
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

    private $ErrorLevel = ERROR_FILE;
    private $ErrorFile  = "Database.Error.Logfile.log";

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

    public function Error($_FuncName, $_Message = "" ) {
        switch($this->ErrorLevel) {
            case ERROR_ALL:
            break;
            case ERROR_FILE:
            break;
            case ERROR_EXCEPTION:
            break;
            case ERROR_ECHO:
                echo "<b>Database error message</b><br> [{$_FuncName}] {$_Message}";
            break;
        }
    }
}
