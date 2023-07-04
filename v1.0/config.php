<?php
// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname="flyfarin_shopno";

// Shopno Live Database
$servername = "flyfarint.com";
$username = "flyfarin_shopno";
$password = "*04ruXfEfq";
$dbname = "flyfarin_shopno";


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
  
}

?>
