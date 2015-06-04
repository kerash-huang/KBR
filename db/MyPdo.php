<?php
namespace db;
class MyPdo extends Database {
    // PDO Object
    public  $handle;

    /**
     * 紀錄曾經建立連線過（作為 disconnect 後再重連時的判斷）
     */
    private $haveConnected = false;
    public  $pub_host, $pub_database;
    public  $pub_user, $pub_password;

    private $pdo_fetch_type = \PDO::FETCH_ASSOC;
    private $pdo_default_char = "UTF8";
    private $calc_row = false;

    private $result_row_num = 0;

    function __construct($host, $database, $user, $password, $dbtype = "mysql") {
        $this->pub_host     = $host;
        $this->pub_database = $database;
        $this->pub_user     = $user;
        $this->pub_password = $password;

        $dsn = "{$dbtype}:dbname={$database};host={$host}";
        try {
            $this->handle = new \PDO($dsn, $user, $password,
                array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->pdo_default_char}")
            );
            $this->handle->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false); // send statement twice for preventing sql injection
            $this->handle->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->haveConnected = true;
        } catch (\PDOException $e) {
            parent::_Error(__FUNCTION__, "[Error:".__CLASS__."] ".$e->getMessage());
            return false;
        }
    }

    /**
     * 中斷連線
     */
    function Disconnect() {
        // use empty null to close pdo connection
        $this->handle = null;
    }

    /**
     * 重新連線
     * 注：使用產生出此連線的資訊重新連線
     * connect alias ReConnect;
     */
    function connect() {
        $this->ReConnect();
    }

    function ReConnect() {
        if(!$this->haveConnected) {
            return false;
        }
        $this->handle = new \PDO($dsn, $user, $password,
            array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->pdo_default_char}")
        );
        $this->handle->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false); // send statement twice for preventing sql injection
        $this->handle->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
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
        if( !is_array($column) and trim($column) == "" ) {
            return false;
        } else if(is_array($column) and count($column)==0) {
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
            $query_string .= "{$query_condition} ";
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

    /**
     * 搜尋單一條件
     * @param  mixed  $column
     * @param  mixed  $table
     * @param  string  $where_condition
     * @param  string  $order
     * @param  string  $limit
     * @param  boolean $is_query_show
     * @return mixed
     */
    public function selectone($column, $table, $where_condition = "", $order = "", $limit = "" , $is_query_show = false) {
        $limit = " LIMIT 1 ";
        $result = $this->select($column, $table, $where_condition, $order, $limit, $is_query_show);
        if($result) {
            $result = $result[0];
            return $result;
        } else {
            return false;
        }

    }

    /**
     * executing 'INSERT' sql
     * INSERT INTO [TABLE] ([COLUMNS]) VALUES ([VALUES])
     *
     * Column and data pair
     * null + array(0=>xxx,1=>xxx)
     * null + array('x'=>xxx,'y'=>xxx)
     * null + ('a','b','c')
     * array(x,y,z) + array(xxx,xxx,xxx)
     * ('a','b','c') + ('1','2','3')
     *
     * @param  string  $table
     * @param  mixed   $column
     * @param  string  $data
     * @param  boolean $is_query_show
     * @return boolean
     */
    public function insert($table, $column, $data = "", $extend ="" , $is_query_show = false) {
        if(is_array($table) or empty($table)) {
            return false;
        }
        $query_table = $table;

        // if only two arguments, let 'data' be the 'column' value
        if(!empty($column) and empty($data)) {
            $data = $column;
            $column = "";
        }

        // column and data both no data
        if(empty($data)) {
            return false;
        }
        $query_column = "";
        $query_value  = "";

        $is_bind_param = false;
        $bind_param_array = array();

        // num of column and data not match
        if(is_array($column) and is_array($data) and count($column)!=count($data)) {
            return false;
        } else {
            if(empty($column)) {
                if(is_array($data)){
                    $is_bind_param = true;
                    $keys = array_keys($data);

                    if($keys[0]===0){
                        $query_value  = "(";
                        foreach($data as $val) {
                            $query_value .= "?,";
                            array_push($bind_param_array, $val);
                        }
                        $query_value = rtrim($query_value, ",").")";
                    } else {
                        $query_column = "(";
                        $query_value  = "(";
                        foreach($data as $col=>$val) {
                            $query_column .= "`{$col}`,";
                            $query_value  .= "?,";
                            array_push($bind_param_array, $val);
                        }
                        $query_value  = rtrim($query_value, ",").")";
                        $query_column = rtrim($query_column, ",").")";
                    }

                } else {
                    $query_value .= "({$data})";
                }
            } else if(is_array($column)) {
                $is_bind_param = true;
                $query_column = "(";
                foreach($column as $col) {
                    $query_column .= "`{$col}`,";
                }
                $query_column = rtrim($query_column, ",").")";
                $query_value  = "(";
                foreach($data as $val) {
                    $query_value  .= "?,";
                    array_push($bind_param_array, $val);
                }
                $query_value  = rtrim($query_value, ",").")";
            } else { // string
                $query_column = "({$column})";
                if(is_array($data)) {
                    $is_bind_param = true;
                    $query_value  = "(";
                    foreach($data as $val) {
                        $query_value  .= "?,";
                        array_push($bind_param_array, $val);
                    }
                    $query_value  = rtrim($query_value, ",").")";
                } else {
                    $query_value = "({$data})";
                }
            }
        }

        $query_string = "INSERT INTO {$query_table} ";
        if(trim($query_column)!="") {
            $query_string .= " {$query_column} ";
        }
        if(trim($query_value)=="") {
            return false;
        }
        $query_string .= " VALUES {$query_value} ";
        if(trim($extend)!="") {
            $query_string .= " {$extend} ";
        }
        if($is_query_show) $this->ShowQuery($query_string);
        try {
            $stmt = $this->handle->prepare($query_string);
            if($stmt) {

                if($is_bind_param) {
                    $stmt->execute($bind_param_array);
                } else {
                    $stmt->execute();
                }

                if($stmt->rowCount()>0) {
                    $AId = $this->handle->lastInsertId();
                    return $AId;
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
     * executing 'update' sql
     * @param  mixed  $table
     * @param  mixed  $data
     * @param  mixed  $where_condition
     * @param  boolean $is_query_show
     * @return boolean
     */
    public function update($table, $data, $where_condition, $is_query_show = false){
        if(empty($table) or empty($data)) {
            return false;
        }
        $query_table = $table;
        $is_bind_param = false;
        $query_data = "";
        $bind_param_array = array();
        if(is_array($data)) {
            $is_bind_param = true;
            foreach($data as $col=>$val) {
                $query_data .= "{$col}= ?,";
                array_push($bind_param_array, $val);
            }
            $query_data = rtrim($query_data,",");
        } else {
            $query_data = $data;
        }

        $query_condition = "";
        if(!empty($where_condition)) {
            if(is_array($where_condition)) {
                $is_bind_param = true;
                foreach($where_condition as $col=>$val) {
                    $query_condition .= "{$col}= ?,";
                    array_push($bind_param_array, $val);
                }
                $query_condition = rtrim($query_condition,",");
            } else {
                $query_condition = $where_condition;
            }
            $query_condition = "WHERE {$query_condition}";
        }

        $query_string = "UPDATE {$query_table} SET {$query_data} {$query_condition}";
        try {
            $stmt = $this->handle->prepare($query_string);
            if($stmt) {
                $success = $stmt->execute($is_bind_param?$bind_param_array:null);
                return $success;
            } else {
                return false;
            }
        } catch(\PDOException $e) {
            return false;
        }
    }

    /**
     * executing 'delete' sql
     * @param  mixed  $table
     * @param  mixed  $where_condition
     * @param  boolean $is_query_show
     * @return boolean
     */
    public function delete($table, $where_condition, $order = "", $limit = "", $is_query_show = false){
        if( empty($table) ) {
            return false;
        }
        $is_bind_param = false;
        $query_table = $table;
        $query_string = "DELETE FROM {$query_table} ";
        if(!empty($where_condition)) {
            if(is_array($where_condition)) {
                $is_bind_param = true;
            }
            $query_condition = $this->ParserCondition($where_condition);
            $query_string .= $query_condition;
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
                if($is_bind_param) {
                    $success = $stmt->execute($bind_param_array);
                } else {
                    $success = $stmt->execute();
                }
                return $success;
            } else {
                return false;
            }
        } catch(\PDOException $e) {
            return false;
        }
    }

    /**
     * query 的 alias
     * @param  string  $sql_query
     * @param  boolean $is_query_show
     * @return [type]
     */
    public function do_query($sql_query, $is_query_show = false) {
        return $this->query($sql_query, $is_query_show);
    }

    /**
     * 直接執行 sql 句
     * @param  string  $sql_query
     * @param  boolean $is_query_show
     * @return mixed
     */
    public function query($sql_query, $is_query_show = false) {
        try{
            $sql_query = trim($sql_query);
            if($is_query_show) $this->ShowQuery($sql_query);
            $stmt  = $this->handle->prepare($sql_query);
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
                    } else if(preg_match("/^insert/i",$sql_query)) {
                        $Result = $this->handle->lastInsertId();
                        return $Result;
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
     * @param mixed $condition
     * 如果 condition 是陣列, 那 condition 在處理後， Index 會變成 :{ColumnName}
     * p.s. 由於 update 的條件跟 update 的 data 可能為同欄位，所以不能用 :ColumnName 取代，需要用 ?
     *      無法使用此方法
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
            $condition = trim($condition);
            $return_condition = $condition;
        } else {
            $return_condition = "";
        }
        if(trim($return_condition)!="") {
            $return_condition =  " WHERE {$return_condition} ";
        }
        return $return_condition;



    }

    /**
     * 處理 sql 帶入的參數
     *  - column  [string, {col1,col2,col3}]
     *  - table   [string]
     *  - option  [string, {group: "column", order:"column desc|asc"}]
     *  - limit   [string, [0,1], *default30]
     * @param mixed $type
     * @param mixed $data
     */
    private function Parser($type, $data) {
        $return = "";
        switch(strtolower($type)) {
            case "column":
                if(is_array($data)) {
                    foreach($data as $_d) {
                        $return .= "`{$_d}`,";
                    }
                    $return = rtrim($return,",");
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
                        if(strpos($_d, ".")!==false) { // aaa.bbb
                            $_td = explode(".", $_d);
                            $return .= "`".trim($_td[0], "`")."`.`".trim($_td[1], "`")."`,";
                        } else {
                            $return .= "`{$_d}`,";
                        }
                    }
                    $return = rtrim($return,",");
                } else if(is_string($data)) {
                    $return = $data;
                }
            break;
            case "option": // order by, group by
                if(is_array($data)) {
                    if(isset($data["group"])) {
                        $return .= " GROUP BY {$data["group"]}";
                    }
                    if(isset($data["order"])) {
                        $return .= " ORDER BY {$data["order"]}";
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
