<?php
namespace ezapi;

class Router {
    public $RoutePath = array();

    /**
     * 儲存以 GET 方法呼叫的 Route
     * @param  string $route
     * @param  array  $param
     * @return [type]
     */
    public function get($route, $param = null) {
        $default_param = array("controller"=>"", "action"=>"");
        foreach($default_param as $key => $NOT_USE) {
            $default_param[$key] = isset($param[$key])?$param[$key]:"";
        }
        $this->RoutePath["GET"][$route] = $default_param;
    }

    /**
     * 儲存以 POST 方法呼叫的 Route
     * @param  string $route
     * @param  array  $param
     * @return [type]
     */
    public function post($route, $param = null) {
        $default_param = array("controller"=>"", "action"=>"", "get"=>null);
        foreach($default_param as $key => $NOT_USE) {
            $default_param[$key] = isset($param[$key])?$param[$key]:"";
        }
        $this->RoutePath["POST"][$route] = $default_param;
    }

    /**
     * 比對 Route 匹配結果
     * 先做完全一樣的比對，如果找不到再用條件式搜尋
     * example:
     *    [Url Path]
     *      /home/hi
     *    [Route]
     *      /home
     *      /home/:name?qqq=xxx
     *
     *
     * @param  string $method
     * @param  string $route
     * @return Object
     */
    public function find($method, $route) {
        // 檢查呼叫的 http method 是不是有定義
        if(isset($this->RoutePath[$method])) {
            // 檢查是否存在完全匹配定義的 route
            if(isset($this->RoutePath[$method][$route])) {
                $RouteData = $this->RoutePath[$method][$route];
                return $RouteData;
            } else {
                // 以分隔符號切割每個參數
                $request_route_data = explode("/" , $route);
                $request_route_len  = count($request_route_data);

                uksort($this->RoutePath[$method], array($this, "sortroute"));
                $routelist = $this->RoutePath[$method];

                foreach($routelist as $def => $data) {
                    $route_param_data = explode("/", $def);

                    $route_param_data = array_values(array_filter($route_param_data, 'strlen'));

                    if( count($route_param_data) < $request_route_len )
                        continue;

                    if( count($route_param_data) > $request_route_len )
                        break;

                    $tmp_data_container = array();

                    $next = false;

                    foreach($route_param_data as $pos => $key) {
                        if($key[0] == ":") {
                            $tmp_data_container[substr($key,1)] = $request_route_data[$pos];
                        } else {
                            if($key != $request_route_data[$pos]) {
                                $next = true;
                                break;
                            } else {

                            }
                        }
                    }

                    // jump to next route condition
                    if($next === true) {
                        continue;
                    }
                    foreach ($tmp_data_container as $name => $value) {
                        Common::SetParam($name, $value);
                    }
                    return $data;
                }
                return null;
            }

        } else {
            return null;
        }
    }

    /**
     * 排序比較用
     * @param  string &$a
     * @param  string &$b
     * @return [type]
     */
    private function sortroute(&$a, &$b) {
        if(strlen($a)>strlen($b)) {
            return 1;
        } else {
            return 0;
        }
    }

}