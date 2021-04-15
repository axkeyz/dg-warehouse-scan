<?php

include('../config.php');

if( APP_DEBUG ){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

include('../classes/Query.php');

# Use php version if php (dynamic server-based rendering) is needed
// include('theme/header.php'); 
readfile('theme/header.html');
include('theme/body.php');
readfile('theme/footer.html');
// include('theme/footer.php');