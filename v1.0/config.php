<?php
//Local Database
// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname="flyfarin_shopno";


$servername = "flyfarint.com";
  $username = "flyfarin_erp";
  $password = "@Kayes70455";
  $dbname = "flyfarin_b2bv3";



// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname="flyfarin_b2bV3";


// Shopno Live Database
// $servername = "flyfarint.com";/* The lines of code you provided are setting the values for the
// username, password, and database name variables. These variables are
// used to establish a connection to a MySQL database. In this case, the
// username is set to "flyfarin_shopno", the password is set to
// "*04ruXfEfq", and the database name is also set to "flyfarin_shopno".
// These values are used later in the code to connect to the database
// server. */

// $username = "flyfarin_shopno";
// $password = "*04ruXfEfq";
// $dbname = "flyfarin_shopno";





// // FlyWay Live Database
//       $servername = "200.69.23.30";
//       $username = "flyfarin_flyway";
//       $password = "@Flyway321";
//       $dbname = "flyfarin_flyway";


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
  
}

?>
