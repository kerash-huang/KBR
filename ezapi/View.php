<?php
namespace ezapi;

class View {
    private static $CSSStore = array();
    private static $JSStore  = array();

    /**
     * 載入 CSS 檔案
     * 
     * @param string $CSSFiles [description]
     */
    public static function LoadCSS($CSSFiles = "") {
        if(is_array($CSSFiles)) {
            foreach($CSSFiles as $f) {
                if(trim($f)!="") {
                    if(!in_array($f, self::$CSSStore)) {
                        array_push(self::$CSSStore, $f);
                    }
                }
            }
        } else if(is_string($CSSFiles) and trim($CSSFiles)!="") {
            if(!in_array($CSSFiles, self::$CSSStore)) {
                array_push(self::$CSSStore, $CSSFiles);
            }
        } else {
            return false;
        }
    }

    /**
     * 載入 Script 檔案
     * 
     * @param string $ScriptFiles [description]
     */
    public static function LoadScript($ScriptFiles = "") {
        if(is_array($ScriptFiles)) {
            foreach($ScriptFiles as $f) {
                if(trim($f)!="") {
                    if(!in_array($f, self::$JSStore)) {
                        array_push(self::$JSStore, $f);
                    }
                }
            }
        } else if(is_string($ScriptFiles) and trim($ScriptFiles)!="") {
            if(!in_array($ScriptFiles, self::$JSStore)) {
                array_push(self::$JSStore, $ScriptFiles);
            }
        } else {
            return false;
        }
    }

    public static function GetScript() {

    }

    public static function GetCSS() {

    }

    /**
     * 載入 Template 檔案
     * 
     * @param string $TemplateFile [description]
     * @param string $ViewFolder   [description]
     */
    public static function Render($TemplateFile="", $ViewFolder = "") {
        global $APIInst;

        $TargetViewFolder = $APIInst->APIViewPath;
        if(trim($ViewFolder) != "") {
            $TargetViewFolder = $ViewFolder;
        }
        if(file_exists($TargetViewFolder."/{$TemplateFile}.php")) {
            require_once $TargetViewFolder."/{$TemplateFile}.php"; 
        } else {
            $APIInst->error(404);
        }
    }


}