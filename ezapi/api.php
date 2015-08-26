<?php
namespace ezapi;

class Api extends Base {
    
    function __construct($config = null) {
        if($config == null) {
            $config = new Config;
        }
        parent::__construct($config);
    }

    /**
     * 設定 Router
     * @param string $method
     * @param string $route
     * @param mixed  $param
     */
    public function AddRoute($method, $route, $param = null) {
        if($this->APIRoute === null) {
            $this->APIRoute = new Router;
        }
        call_user_func( array($this->APIRoute, $method) , $route, $param);
    }

    /**
     * 設定預設 Router
     * @param string $controller
     * @param string $action
     */
    public function DefaultRoute($controller, $action) {
        $this->DefaultController = $controller;
        $this->DefaultAction     = $action;
    }

    /**
     *  啟動 API
     *  程序必要呼叫的方法，如不呼叫則不會執行 EzAPI 的程序
     */
    public function Run() {
        $routePath = isset($this->ServerParam["PATH_INFO"]) ? $this->ServerParam["PATH_INFO"] : "";
        if($routePath !== "/") {
            $routePath = trim($routePath, "/");
        }

        if($this->APIRoute) {
            // 有指定過 Route
            // 搜尋有沒有定義 Route 的頁面
            $RouteData = $this->APIRoute->find($this->RequestMethod, $routePath);
            if(!$RouteData) {
                // 走預設 Route
                $this->doRoute($this->DefaultController, $this->DefaultAction);
            } else {
                $Controller = $RouteData["controller"];
                $Action     = $RouteData["action"];
                $this->doRoute($Controller, $Action);
            }
        } else {
            // 未指定，直接走預設的 Route
            $this->doRoute($this->DefaultController, $this->DefaultAction);
        }
        die();
    }

    private function doRoute($Controller, $Action) {

        // 預設 Router
        if(file_exists( $this->APIControllerPath . $Controller."Controller.php" ) ) {
            require_once ($this->APIControllerPath . $Controller."Controller.php");
            if( method_exists( __NAMESPACE__."\\Controller\\{$Controller}", $Action) ) {
                call_user_func( array( __NAMESPACE__."\\Controller\\{$Controller}" , $Action) );
            } else {
                $this->error(500);
            }
        } else {

            // Route 不存在就直接 404
            $this->error(404);
        }
    }

}
