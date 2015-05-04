<?php
namespace db;
class MyPdo extends Database {
    public  $handle;
    
    public  $pub_host, $pub_database;

    private $pdo_fetch_type = \PDO::FETCH_ASSOC;
    private $calc_row = false;
    
    private $result_row_num = 0;

    function __construct($host, $database, $user, $password, $dbtype = "mysql") {
        $this->pub_host = $host;
        $this->pub_database = $database;

        $dsn = "{$dbtype}:dbname={$database};host={$host}";
        try {
            $this->handle = new \PDO($dsn, $user, $password);
        } catch (\PDOException $e) {
            parent::_Error(__FUNCTION_NAME__, "[Error:".__CLASS__."] ".$e->getMessage());
            return false;
        }
    }

    function __destruct() {
        $this->handle = null;
    }

    /**
     * 設定是否計算 select 行數
     */
    function SetCalcRows($calc) {
        $this->calc_row = $calc;
    }

    /**
     * 取得 found_calc_rows 計算結果的總數量
     */
    function GetResultRowNum() {
        return $this->result_row_num;
    }

    /**
     * Transaction 用
     * 開始呼叫 Transaction
     */
    public function StartTransaction(){
        try {
            $this->handle->beginTransaction();
        } catch(\PDOException $e) {

        }
    }

    /**
     * 結束 transaction
     * @param boolean $cancel 是否取消交易
     */
    public function EndTransaction($cancel = false){
        try {
            if($cancel) {
                $this->handle->rollBack();
            } else {
                $this->handle->commit();
            }
        } catch(\PDOException $e) {

        }
    }

    /**
     * executing 'SELECT' sql
     * @param  mixed  $column           
     * @param  mixed  $table            
     * @param  mixed  $where_condition  
     * @param  mixed  $order            
     * @param  mixed  $limit            
     * @param  boolean $is_query_show   
     * @return mixed                   
     */
    public function select($column, $table, $where_condition = "", $order = "", $limit = "" , $is_query_show = false){
        if( trim($column) == "" ) {
            return false;
        }
        if( trim($table) == "" ) {
            return false;
        }

        $query_column = $this->Parser("column", $column);

        $query_table  = $this->Parser("table" , $table);

        $query_string = "SELECT ".($this->calc_row?"SQL_CALC_FOUND_ROWS ":"")."{$query_column} FROM {$query_table}";

        if(!empty($where_condition)) {
            $query_condition = $this->ParserCondition($where_condition);
            $query_string .= " WHERE {$query_condition} ";
        }
        if(!empty($order)) {
            $query_order  = $this->Parser("option" , $order);
            $query_string .= " {$query_order}";
        }
        if(!empty($limit)) {
            $query_limit  = $this->Parser("limit" , $limit);
            $query_string .= " {$query_limit}";
        }

        if($is_query_show) $this->ShowQuery($query_string);

        try {
            $stmt = $this->handle->prepare($query_string);
            if($stmt) {
                if(is_array($where_condition)) {
                    $stmt->execute($where_condition);
                } else {
                    $stmt->execute();
                }
                $result = $stmt->fetchAll($this->pdo_fetch_type);

                if($this->calc_row) {
                    $row_stmt = $this->handle->prepare("SELECT FOUND_ROWS() AS CALCROW");

                    if($row_stmt) {
                        $exec = $row_stmt->execute();
                        $row_result = $row_stmt->fetchAll(\PDO::FETCH_NUM);
                        $this->result_row_num = $row_result[0][0];

                    } else {
                        $this->result_row_num = 0;
                    }
                }
            } else {
                return null;
            }
            if(count($result)==0) {
                return null;
            }
            return $result;
        } catch (\PDOException $e) {
            parent::_Error(__FUNCTION_NAME__, "[Error:".__CLASS__."] ".$e->getMessage());
            return false;
        }

    }
    public function insert($table, $column, $data = "", $is_query_show = false) {

    }
    public function update($table, $column, $where_condition, $is_query_show = false){

    }
    public function delete($table, $where_condition = false){

    }

    public function query($sql_query, $is_query_show = false) {
        try{
            $Connector = $this->handle;
            $sql_query = trim($sql_query);
            if($is_query_show) $this->ShowQuery($query_string);
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
        } catch(\PDOException $e) {
            return false;
        }
    }

    /**
     * 拆解 Condition
     * @param mixed $condition [description]
     * 如果 condition 是陣列, 那 condition 在處理後， Index 會變成 :{ColumnName}
     */
    private function ParserCondition(&$condition) {
        $return_condition = "";
        if(is_array($condition)) {
            $copiedCondition = $condition;
            foreach($condition as $ColumnName => $cond) {
                if(trim($cond)==="") {
                    unset($copiedCondition[$ColumnName]);
                    continue;
                }
                $return_condition .= " `{$ColumnName}` = :{$ColumnName} and";
                unset($copiedCondition[$ColumnName]);
                $copiedCondition[":{$ColumnName}"] = trim($cond);
            }
            if(trim($return_condition)==="") {
                return "";
            }
            $condition = $copiedCondition;
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
                    if($this->calc_row) {
                        $data = str_replace("SQL_CALC_FOUND_ROWS", "", $data);
                    }
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
            case "option": // order by, group by
                if(is_array($data)) {
                    if(isset($data["order"])) {
                        $return .= " ORDER BY {$data["order"]}";
                    }
                    if(isset($data["group"])) {
                        $return .= " GROUP BY {$data["group"]}";
                    }
                } else if(is_string($data)) {
                    $return = $data;
                } else {
                    $return = $data;
                }
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

    /**
     * 顯示 SQL 句
     * @param string $sql 
     */
    private function ShowQuery($sql="") {
        echo "<quoteblock>";
        echo "<b>[Query]</b> -> ";
        echo $sql."<br>";
        echo "</quoteblock>";
    }
}
