<?php
    date_default_timezone_set('Asia/Dhaka');

    // $servername = "localhost";
    // $username = "root";
    // $password = "";
    // $dbname = "b2b";

    // $servername = "b2b.flyfarint.com";
    // $username = "flyfarin_erp";
    // $password = "@Kayes70455";
    // $dbname = "flyfarin_erp";

    $servername = "server.flyfarint.net";
    $username = "fahimffi_b2b";
    $password = "@Kayes70455";
    $dbname = "fahimffi_erp";
    
    // Create connection
    $erpconn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($erpconn->connect_error) {
      die("Connection failed: " . $erpconn->connect_error);
    }else{
        echo ' ';
    }
    
?>