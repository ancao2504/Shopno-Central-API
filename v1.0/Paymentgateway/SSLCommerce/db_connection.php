<?php
date_default_timezone_set('Asia/Dhaka');


// Shopno Live Database
$servername = "flyfarint.com";
$username = "flyfarin_shopno";
$password = "*04ruXfEfq";
$dbname = "flyfarin_shopno";


$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo '';
}

?>