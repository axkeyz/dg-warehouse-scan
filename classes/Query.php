<?php

class Query{
    public $results;
    public $row_count;
    private $cols;
    private $connection_info = array( "Database"=>DB_DATABASE, "UID"=>DB_USERNAME, "PWD"=>DB_PASSWORD);
    private $conn;
    private $sql;

    public function __construct($query = false, $params = []){
        // Connects to database & performs query if parameters are filled
        $this->conn = sqlsrv_connect( DB_HOST, $this->connection_info);
        if($query){
            $this->sql = $query.json_encode($params);
            $this->search_database($query, $params);
        }
    }

    public function check_connection(){
        // Checks if connection up or down
        if( $this->conn ) {
            echo "Connection: TRUE.<br />";
        }else{
            echo "Connection: Down.<br />";
            die( $this->get_errors() );
        }
        sqlsrv_close( $this->conn );
    }

    public function get_errors(){
        // Get errors and return in nice format
        $errors = sqlsrv_errors();
        $errtxt = "Error occured: ";
            foreach( $errors as $error) {
            $errtxt .= $error["SQLSTATE"] . ", ";
            $errtxt .= $error["code"] . ", ";
            $errtxt .= $error["message"] . "<br>";
            }
        return $errtxt;
    }

    public function search_database($query, $params){
        // search using a query, if not initialised with class
        $cursor_type = array("Scrollable" => SQLSRV_CURSOR_KEYSET);  
        $results = sqlsrv_query( $this->conn, $query, $params, $cursor_type);

        if ($results === False){
            die( $this->get_errors() );
        }
        
        if(sqlsrv_has_rows($results)) {  
            $this->row_count = sqlsrv_num_rows($results);
            // $this->results = sqlsrv_fetch_array( $results, SQLSRV_FETCH_ASSOC);
            $result_obj = [];
            while( $row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC) ) {
                $result_obj[] = $row;
            }
            $this->results = $result_obj;

            $this->cols = sqlsrv_field_metadata($results);
        }
        // Free up database for others
        sqlsrv_free_stmt( $results );  
        sqlsrv_close( $this->conn );
        
        return $this->results;
    }

    public function get_results(){
        // get results
        return $this->results;
    }

    public function get_row_count(){
        // get row count
        return $this->row_count;
    }

    public function get_sql(){
        return $this->sql;
    }

    public function get_cols(){
        return $this->cols;
    }

}