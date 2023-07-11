<?php
//Local Database
// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname="flyfarin_shopno";


// $servername = "flyfarint.com";
//   $username = "flyfarin_erp";
//   $password = "@Kayes70455";
//   $dbname = "flyfarin_b2bv3";



$servername = "localhost";
$username = "root";
$password = "";
$dbname="flyfarin_b2bV3";


// // Shopno Live Database
// $servername = "flyfarint.com";
// $username = "flyfarin_shopno";
// $password = "*04ruXfEfq";
// $dbname = "flyfarin_shopno";





// // FlyWay Live Database
      // $servername = "200.69.23.30";
      // $username = "flyfarin_flyway";
      // $password = "@Flyway321";
      // $dbname = "flyfarin_flyway";


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
  
}

?>
