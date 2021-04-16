<?php

// Apply main config constants
include('../config.php');

// Enable debugging system if enabled
if( APP_DEBUG ){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// May need to write an autoloader for classes, but this will do for now
include('../classes/Query.php');

// ROUTER
// Get request URI, which is / if hosted in the root or /your-project-folder/ if hosted in project folder
$request = $_SERVER['REQUEST_URI'];

// Get header for every file. Can use include('theme/header.php') if need php processing.
readfile('theme/header.html');

// Set out routes
if($request == '/'.APP_FOLDER.'/'){
    include('theme/functions/warehouse.php');
}else{
    include('theme/404.php');
}

// Get footer for every file. Can use include('theme/footer.php') if need php processing.
readfile('theme/footer.html');
