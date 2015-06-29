<?php
namespace ezapi\Controller;

use \ezapi as ez;

class Emb extends \ezapi\Controller {
    private static $cache_timeout = 600;
    private static $source_url = "http://tonyq.org/kptaipei/api-20150628.php";
    public static function PreLoad() {
    }

    public static function AllList() {
        global $APIInst;
        if(file_exists($APIInst->APIBasePath."cache/emblist.json")) {
            $result = file_get_contents($APIInst->APIBasePath."cache/emblist.json");
            $List = json_decode($result, true);
            if(!isset($List["data"]) or !isset($List["expire"]) or time() - $List["expire"] > self::$cache_timeout) {
                $result = ez\Http::CurlGet(self::$source_url);    
                file_put_contents($APIInst->APIBasePath."cache/emblist.json", $result);
            }
        } else {
            $result = ez\Http::CurlGet(self::$source_url);
            file_put_contents($APIInst->APIBasePath."cache/emblist.json", $result);
        }

        $RespData = json_decode($result,true);
        header("Content-type: application/json;charset='utf-8'");
        echo json_encode($RespData);
    }


    public static function FindAll() {
        global $APIInst;
        $Name = ez\Common::GetParam("condition");
        $PossibleName = array($Name);

        if(file_exists($APIInst->APIBasePath."cache/emblist.json")) {

            $result = file_get_contents($APIInst->APIBasePath."cache/emblist.json");
            $List = json_decode($result,true);
            // timeout 
            if(!isset($List["data"]) or !isset($List["expire"]) or time() - $List["expire"] > self::$cache_timeout) {
                $result = ez\Http::CurlGet(self::$source_url);
                $List = json_decode($result,true);
                $List["expire"] = time();
                file_put_contents($APIInst->APIBasePath."cache/emblist.json", json_encode($List));
            }
        } else {
            $result = ez\Http::CurlGet(self::$source_url);
            $List = json_decode($result,true);
            $List["expire"] = time();
            file_put_contents($APIInst->APIBasePath."cache/emblist.json", json_encode($List));
        }

        if(strpos($result, "○")!==false) {
            if(mb_strlen($Name,'utf-8')>2) {
                $UTF8FName = mb_substr($Name, 0, 1, 'utf-8');
                $UTF8EName = mb_substr($Name, 2, mb_strlen($Name)-1, 'utf-8');
                $ProtectedName = $UTF8FName."○".$UTF8EName;
                array_push($PossibleName,$ProtectedName);
            } else if(mb_strlen($Name,'utf-8')==2) {
                $UTF8FName = mb_substr($Name, 0, 1, 'utf-8');
                $ProtectedName = $UTF8FName."○";
                array_push($PossibleName,$ProtectedName);
                $UTF8EName = mb_substr($Name, 1, mb_strlen($Name)-1, 'utf-8');
                $ProtectedName = "○".$UTF8EName;
                array_push($PossibleName,$ProtectedName);
            } else {
            array_push($PossibleName,$Name);
            }
        } else {
            array_push($PossibleName,$Name);
        }

        unset($List["expire"]);
        $TryFind = true;
        if($TryFind!==false) {
            $List = json_decode($result,true);
            if($List) {
                $embData = $List["data"];
                $lastModify = $List["lastmodify"];
                $license = $List["license"];
                $source = $List["source"];

                // if(time()-strtotime($lastModify) > self::$cache_timeout) {
                //     $result = ez\Http::CurlGet("https://gist.githubusercontent.com/tony1223/098e45623c73274f7ae3/raw");
                //     file_put_contents($APIInst->APIBasePath."cache/emblist.json", $result);
                //     $List = json_decode($result,true);
                //     $embData = $List["data"];
                //     $lastModify = $List["lastmodify"];
                // }
                $RespData = array("lastmodify"=>$lastModify,"license"=>$license,"source"=>$source, "data"=>array());
                foreach($PossibleName as $ProtectedName) {
                    foreach($embData as $i=>$mD) {
                        if(strpos($mD["姓名"],$ProtectedName)!==false
                            or strpos($mD["收治單位"],$ProtectedName)!==false
                            ) {
                            $RespData["data"][$i] = $embData[$i];
                        }
                    }
                }
                // var_dump($RespData);
                sort($RespData["data"]);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($RespData);
            } else {
                $List = array();
            }
        } else {
            echo "{}";
        }


    }

    public static function FindName() {
        global $APIInst;
        $Name = ez\Common::GetParam("name");

        $PossibleName = array();

        if(mb_strlen($Name,'utf-8')>2) {
            $UTF8FName = mb_substr($Name, 0, 1, 'utf-8');
            $UTF8EName = mb_substr($Name, 2, mb_strlen($Name)-1, 'utf-8');
            $ProtectedName = $UTF8FName."○".$UTF8EName;
            array_push($PossibleName,$ProtectedName);
        } else if(mb_strlen($Name,'utf-8')==2) {
            $UTF8FName = mb_substr($Name, 0, 1, 'utf-8');
            $ProtectedName = $UTF8FName."○";
            array_push($PossibleName,$ProtectedName);
            $UTF8EName = mb_substr($Name, 1, mb_strlen($Name)-1, 'utf-8');
            $ProtectedName = "○".$UTF8EName;
            array_push($PossibleName,$ProtectedName);
        } else {
            array_push($PossibleName,$Name);
        }

        if(file_exists($APIInst->APIBasePath."cache/emblist.json")) {
            $result = file_get_contents($APIInst->APIBasePath."cache/emblist.json");
        } else {
            $result = ez\Http::CurlGet(self::$source_url);
            file_put_contents($APIInst->APIBasePath."cache/emblist.json", $result);
        }

        $TryFind = true;
        if($TryFind!==false) {
            $List = json_decode($result,true);
            if($List) {
                $embData = $List["data"];
                $lastModify = $List["lastmodify"];
                $license = $List["license"];
                $source = $List["source"];
                if(time()-strtotime($lastModify) > self::$cache_timeout) {
                    $result = ez\Http::CurlGet(self::$source_url);
                    file_put_contents($APIInst->APIBasePath."cache/emblist.json", $result);
                    $List = json_decode($result,true);
                    $embData = $List["data"];
                    $lastModify = $List["lastmodify"];
                }
                $RespData = array("lastmodify"=>$lastModify,"license"=>$license,"source"=>$source, "data"=>array());
                foreach($PossibleName as $ProtectedName) {
                    foreach($embData as $i=>$mD) {
                        if(strpos($mD["姓名"],$ProtectedName)!==false) {
                            $RespData["data"][$i] = $embData[$i];
                        }
                    }
                }
                // var_dump($RespData);
                sort($RespData["data"]);
                header("Content-type: application/json;charset='utf-8'");
                echo json_encode($RespData);
            } else {
                $List = array();
            }
        } else {
            echo "{}";
        }

    }


    public static function FindHospital() {
        global $APIInst;
        $Name = ez\Common::GetParam("name");

        $PossibleName = array();

        if(file_exists($APIInst->APIBasePath."cache/emblist.json")) {
            $result = file_get_contents($APIInst->APIBasePath."cache/emblist.json");
        } else {
            $result = ez\Http::CurlGet(self::$source_url);
            file_put_contents($APIInst->APIBasePath."cache/emblist.json", $result);
        }

        $TryFind = true;
        if($TryFind!==false) {
            $List = json_decode($result,true);
            if($List) {
                $embData = $List["data"];
                $lastModify = $List["lastmodify"];
                $license = $List["license"];
                $source = $List["source"];
                if(time()-strtotime($lastModify) > self::$cache_timeout) {
                    $result = ez\Http::CurlGet(self::$source_url);
                    file_put_contents($APIInst->APIBasePath."cache/emblist.json", $result);
                    $List = json_decode($result,true);
                    $embData = $List["data"];
                    $lastModify = $List["lastmodify"];
                }
                $RespData = array("lastmodify"=>$lastModify,"license"=>$license,"source"=>$source, "data"=>array());
                foreach($embData as $i=>$mD) {
                    if(strpos($mD["收治單位"],$Name)!==false) {
                        $RespData["data"][$i] = $embData[$i];
                    }
                }
                // var_dump($RespData);
                sort($RespData["data"]);
                header("Content-type: application/json;charset='utf-8'");
                echo json_encode($RespData);
            } else {
                $List = array();
            }
        } else {
            echo "{}";
        }

    }
}