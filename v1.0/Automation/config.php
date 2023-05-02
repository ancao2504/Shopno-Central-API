<?php
    date_default_timezone_set('Asia/Dhaka');
    // Generates a b2b server name.

    // $servername = "34.143.224.61";
    // $username = "root";
    // $password = "3sXo]`XDx$;4N)v,";
    // $dbname = "b2b";

    // $servername = "localhost";
    // $username = "root";
    // $password = "";
    // $dbname = "b2b1";

    $servername = "b2b.flyfarint.com";
    $username = "flyfarin_erp";
    $password = "@Kayes70455";
    $dbname = "flyfarin_b2b";

    // $servername = "server.flyfarint.net";
    // $username = "fahimffi_b2b";
    // $password = "@Kayes70455";
    // $dbname = "fahimffi_b2b";
    
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }else{
        echo '';
    }
    
?>