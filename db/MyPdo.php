<?php
namespace db;
class MyPdo extends Database {
    public  $handle;
    private $pdo_fetch_type = \PDO::FETCH_ASSOC;
    private $calc_row = false;

    function __construct($host, $database, $user, $password, $dbtype = "mysql") {
        $dsn = "{$dbtype}:dbname={$database};host={$host}";
        try {
            $this->handle = new \PDO($dsn, $user, $password);
        } catch (\PDOException $e) {
            parent::Error(__FUNCTION_NAME__, "[Error:".__CLASS__."] ".$e->getMessage());
            return false;
        }
    }

    function __destruct() {
        $this->handle = null;
    }

    public function StartTransaction(){
        try {
            $this->handle->beginTransaction();
        } catch(\PDOException $e) {

        }
    }
    public function EndTransaction($cancel = false){
        try {
            if($cancel) {
                $this->handle->rollBack();
            } else {
                $this->handle->commit();
            }
        }
    }


    /**
     * executing 'SELECT' sql
     * @param  [type]  $column          [description]
     * @param  [type]  $table           [description]
     * @param  string  $where_condition [description]
     * @param  string  $order           [description]
     * @param  string  $limit           [description]
     * @param  boolean $is_query_show   [description]
     * @return [type]                   [description]
     */
    public function select($column, $table, $where_condition = "", $order = "", $limit = "" , $is_query_show = false){
        $query_column = $this->Parser("column", $column);
        $query_table  = $this->Parser("table" , $table);

        $query_string = "SELECT {$query_column} FROM {$query_table}";
        if(!empty($where_condition)) {
            $query_condition = $this->ParserCondition($where_condition);
            $query_string .= " WHERE {$query_condition} ";
        }
        if(!empty($order)) {
            $query_order  = $this->Parser("order" , $order);
            $query_string .= " ORDER BY {$query_order}";
        }
        if(!empty($limit)) {
            $query_limit  = $this->Parser("limit" , $limit);
            $query_string .= " {$query_limit}";
        }

        try {
            $stmt = $link->prepare($query_string);
            if($stmt) {
                if(is_array($where_condition)) {
                    $stmt->execute($where_condition);
                } else {
                    $stmt->execute();
                }
                $result = $stmt->fetchAll($this->pdo_fetch_type);
            } else {
                return null; 
            }
            if(count($result)==0) {
                return null;
            }
        } catch (PDOException $e) {
            return false;
        }

    }
    public function insert($table, $column, $data = "", $is_query_show = false) {

    }
    public function update($table, $column, $where_condition, $is_query_show = false){

    }
    public function delete($table, $where_condition = false){

    }
    public function select_union($column_tables, $) {

    }

    public function query($sql_query, $is_query_show = false) {
        try{
            $Connector = $this->handle;
            $sql_query = trim($sql_query);
            if($is_query_show) {
                echo $sql_query;
            }
            $stmt  = $Connector->prepare($sql_query);
            if($stmt) {
                $ExecuteReturn = $stmt->execute();
                if($ExecuteReturn) {
                    if(preg_match("/^(select|show)/i",$sql_query))  {
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

    /**
     * 拆解 Condition
     * @param [type] $condition [description]
     */
    private function ParserCondition(&$condition) {
        $return_condition = "";
        if(is_array($condition)) {
            $copiedCond = $condition;
            foreach($condition as $ColumnIndex => $cond) {
                if($cond==="")
                { 
                    unset($copiedCond[$ColumnIndex]);  continue; 
                }
                $return_condition .= " `{$ColumnIndex}` = :{$ColumnIndex} and";
                unset($copiedCond[$ColumnIndex]);
                $copiedCond[":{$ColumnIndex}"] = trim($cond);
            }
            $condition = $copiedCond;
            $return_condition = preg_replace("/and$/","",$return_condition);
        } else if(is_string($condition)) {
            $return_condition = $condition;
        } else {
            $return_condition = "";
        }
        return $return_condition;
        

        
    }

    /**
     * 處理 sql 帶入的參數
     *  - column  [string, {col1,col2,col3}]
     *  - table   [string]
     *  - option  [string, {group: "column", order:"column desc|asc"}]
     *  - limit   [string, [0,1], *default30]
     * @param [type] $type [description]
     * @param [type] $data [description]
     */
    private function Parser($type, $data) {
        $return = "";
        switch(strtolower($type)) {
            case "column":
                if(is_array($data)) {
                    foreach($data as $_d) {
                        $return .= "`{$_d}`,";
                    }
                    $return = rtrim($_d,",");
                } else if(is_string($data)) {
                    $return = $data;
                }
            break;
            case "table":
                if(is_array($data)) {
                    foreach($data as $_d) {
                        $return .= "`{$_d}`,";
                    }
                    $return = rtrim($_d,",");
                } else if(is_string($data)) {
                    $return = $data;
                }
            break;
            case "option":
            break;
            case "limit":
                if(is_array($data) and count($data)===2) {
                    $return = "{$data[0]},{$data[1]}";
                    $return = " LIMIT $return ";
                } else if(is_string($data)) {
                    $return = $data;
                } else {
                    $return = "LIMIT 30";
                }
            break;
        }
        return $return;
    }
}
