<?php

include('../config.php');

if( APP_DEBUG ){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

include('../classes/Query.php');

$request = $_SERVER['REQUEST_URI'];

readfile('theme/header.html');

if($request == '/dg-warehouse-scan/'){
    include('theme/functions/warehouse.php');
}else{
    readfile('theme/404.html');
}

readfile('theme/footer.html');