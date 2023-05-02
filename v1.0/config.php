<?php
    date_default_timezone_set('Asia/Dhaka');
    
    //FlyWay Localhost Database
    // $servername = "localhost";
    // $username = "root";
    // $password = "";
    // $dbname = "flyfarin_flyway";

    // FlyWay Live Database
      $servername = "flyfarint.com";
      $username = "flyfarin_shopno";
      $password = "*04ruXfEfq";
      $dbname = "flyfarin_shopno";

    
    
    // Create connection
   // $conn = new mysqli($servername, $username, $password, $dbname, $port);
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }else{
        echo '';
    }
    
?>