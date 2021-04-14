<?php

include('../config.php');

if( APP_DEBUG ){
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

include('../classes/Query.php');

include('theme/header.php');
include('theme/body.php');
include('theme/footer.php');