<?php
namespace db;
/**
 * @descript      Database Conenction
 * @author        Kerash <kerash@livemail.com>
 * @start         2015/04/26
 * @last-modify   2015/05/29 
 * @version       v1.2.7
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
        register_shutdown_function(array("db\Database","destruct"));
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

    public static function setErrorLevel($level) {
        self::$ErrorLevel = $level;
    }

    /**
     * 解構
     * @return [type]
     */
    public static function destruct() {
        if(count(self::$ActiveConnection)>0) {
            foreach(self::$ActiveConnection as $name => $Connect) {
                unset(self::$ActiveConnection[$name]);
            }
        }
        return;
    }

    /**
     * 取得資料庫連線實體
     * @param  string $source
     * @return [type]
     */
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

    /**
     * 中斷連線
     * @param mixed $source 連線名稱
     *
     */
    public function disconnectDb($source) {
        $source = (array)($source);
        foreach($source as $ln) {
            if(!isset(self::$DBSource[$ln])) {
                return true;
            } else {
                self::$DBSource[$ln]->Disconnect();
            }
        }
    }

    /**
     * 中斷目前所有的連線
     */
    public function disconnectAll() {
        foreach(self::$ActiveConnection as $Dblink) {
            $Dblink->Disconnect();
        }
    }

    public function _Error( $_FuncName, $_Message = "" ) {
        switch(self::$ErrorLevel) {
            case ERROR_ALL:
                $this->_Error_File($_FuncName, $_Message);
            break;
            case ERROR_FILE:
                $this->_Error_File($_FuncName, $_Message);
            break;
            case ERROR_EXCEPTION:

            break;
            case ERROR_ECHO:
                echo "<b>Database error message</b><br> [{$_FuncName}] {$_Message}";
            break;
        }
    }

    private function _Error_File( $_Fn, $_M) {
        if(!file_exists(self::$ErrorLogDir)) {
            mkdir(self::$ErrorLogDir,0777);
        }
        if(file_exists(self::$ErrorLogDir)) {
            file_put_contents(self::$ErrorLogDir.self::$ErrorFile, "[DbErrLog@".date("Y-m-d H:i:s")."]\n[Function:{$_Fn}]\n[Message]{$_M}\n", FILE_APPEND);
        }
    }

}
