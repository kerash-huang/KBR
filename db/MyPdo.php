<?php
namespace db;
class MyPdo extends Database {
    public  $handle;
    private $pdo_fetch_type = \PDO::FETCH_ASSOC;

    function __construct($host, $database, $user, $password, $dbtype = "mysql") {
        $dsn = "{$dbtype}:dbname={$database};host={$host}";
        try {
            $this->handle = new \PDO($dsn, $user, $password);
        } catch (PDOException $e) {
            die("[Error:".__CLASS__."] ".$e->getMessage());
        }
    }

    public function select($Column, $Table, $Condition = "", $Order = "", $Limit ="" , $Connect = null, $isQueryShow = false){

    }
    public function insert($Table, $Column, $Data, $Connect, $isQueryShow){

    }
    public function update($Table, $Column, $Condition, $Connect, $isQueryShow){

    }
    public function delete($Table, $Condition, $Connect, $isQueryShow){

    }
    public function query($Query, $Connect = null, $isQueryShow = false) {
        try{
            $Connector = $Connect ? $Connect : $this->handle;
            $Query = trim($Query);
            if($isQueryShow) {

            }
            $stmt  = $Connector->prepare($Query);
            if($stmt) {
                $ExecuteReturn = $stmt->execute();
                if($ExecuteReturn) {
                    if(preg_match("/^(select|show)/i",$Query))  {
                        $Result = $stmt->fetchAll( $this->pdo_fetch_type );
                        if($Result) {
                            return $Result;
                        } else {
                            return false;
                        }
                    } else {
                        return true;
                    }
                } else {
                    return false; 
                }
           } else {
               return false;
           }
        } catch(\Exception $ex) {
            return false;
        }
    }
}
