<?php

require_once('config.php');

$serverName = DB_HOST;
$connectionInfo = array( "Database"=>DB_DATABASE, "UID"=>DB_USERNAME, "PWD"=>DB_PASSWORD);
$conn = sqlsrv_connect( $serverName, $connectionInfo);

if( $conn ) {
     echo "Connection: TRUE.<br />";
}else{
     echo "Connection: Down.<br />";
     die( print_r( sqlsrv_errors(), true));
}

function search_database($queryString, $params){
    $cursorType = array("Scrollable" => SQLSRV_CURSOR_KEYSET);  
    $results = sqlsrv_query( $conn, $queryString, $params, $cursorType);
}