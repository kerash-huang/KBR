<?php
namespace ezapi;

class Base {

    private $Config;

    private $EnvDebug = false;
    /**
     * 取得呼叫的方法
     * 由 AcceptMethod 可以限制方法是否可用
     *
     */
    protected $AcceptMethod  = array("GET", "POST");
    public    $RequestMethod = "";

    /**
     * 預設的控制器，如果沒有 Route 會預設怎麼走
     * 
     */
    public $DefaultController = "";
    public $DefaultAction     = "";

    /**
     * PHP 系統環境變數
     */
    public $ServerParam;
    
    /**
     * Route 物件
     * @var null
     */
    public $APIRoute = null;

    /**
     * API 參數路徑
     * @var null
     */
    public $APIBasePath = null;
    public $APIControllerPath = null;
    public $APIViewPath = null;

    public $BaseUrl = "";


    function __construct($config) {
        if($this->EnvDebug) {
            error_reporting(E_ALL);
            ini_set("display_errors",1);
        }

        $this->APIBasePath       = __DIR__."/";
        $this->APIControllerPath = $this->APIBasePath."controller/";
        $this->APIViewPath       = $this->APIBasePath."view/";
        
        $this->BaseUrl = "http://".$_SERVER["HTTP_HOST"]."/".dirname($_SERVER["SCRIPT_NAME"])."/";

        $this->Config        = $config;
        $this->ServerParam   = filter_input_array(INPUT_SERVER);
        $this->RequestMethod = strtoupper($_SERVER["REQUEST_METHOD"]);
        
        /**
         *  假如呼叫方法不在可接受的方式裡面，跳出 405 警告
         */
        if(!in_array($this->RequestMethod, $this->AcceptMethod)) {
            $this->error(405);
            die();
        }
    }

    /**
     * 開啟 Debug 
     */
    function OpenDebug() {
        $this->EnvDebug = true;
    }

    /**
     * 顯示 PHP Debug 訊息
     */
    function ShowPHPDebugMessage() {
        error_reporting(E_ALL);
        ini_set("display_errors",1);
    }
    /**
     * HTTP 警告訊息
     * @param  integer $status_code 
     * @return [type]              
     */
    function error($status_code) {

        header(Http::GetStatus($status_code));
        echo "<h1>".Http::GetStatusText($status_code)."</h1>";

    }
}