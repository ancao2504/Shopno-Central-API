<?php

require 'config.php';
require 'emailfunction.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

$Today = date("Y-m-d H:i");

$sql = "SELECT * FROM `booking` where status ='Hold'";
  $result = $conn->query($sql);

  $return_arr = array();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){ 
        $bookingId = $row['bookingId'];
        $agentId = $row['agentId'];
        $pnr = $row['pnr'];
        $gds = $row['gds'];
        $timelimit = $row['timeLimit'];
        
       
    //AgentMail 
    $agentdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `agent` where agentId='$agentId'"));
    $companyName = $agentdata['company'];
    $companyEmail = $agentdata['email'];

    $bookingdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `booking` where bookingId='$bookingId'")); 
    
    $From = $bookingdata['deptFrom'];
    $To = $bookingdata['arriveTo'];
    $tripType = $bookingdata['tripType'];
    $Airlines = $bookingdata['airlines'];        
        

    //staff Info
    $staffName = "";
    $staffsql = mysqli_query($conn, "SELECT * FROM `staffList` where agentId='$agentId'");
    $staffdata = mysqli_fetch_array($staffsql);
    if (isset($staffdata['name'])){
      $staffName = "Your Staff ".$staffdata['name'];
    }else{
      $staffName = "Agent";
    }

        $now = new DateTime();
        $future_date = new DateTime($timelimit);        
        $interval = $future_date->diff($now);
        

        $remainHours = $interval->h;
        $remainMinutes = $interval->i;
        
        

        if (($remainHours == 0 && $remainMinutes < 5) || ($remainHours == 1 && $remainMinutes < 5) ||
            ($remainHours == 2 && $remainMinutes < 5) || ($remainHours == 3 && $remainMinutes < 5) ||
            ($remainHours == 4 && $remainMinutes < 5) || ($remainHours == 5 && $remainMinutes < 5) ||
            ($remainHours == 6 && $remainMinutes < 5) || ($remainHours == 7 && $remainMinutes < 5)) {

            $timeLeft = $interval->format("%h hours, %i minutes, %s seconds");              
                      
            
            $timelimit = new DateTime($timelimit);
            $formattedDate = $timelimit->format('jS M Y');
            $formattedTime = $timelimit->format('h:i A');


            $header = $subject = "Booking Expired Reminder";
            $property = "Booking ID: ";
            $data = $bookingId;

            $adminMessage = " Our Booking Request will be expired on 
            $formattedDate on $formattedTime.";

            $agentMessage = " Your Booking Request will be expired on 
            $formattedDate on $formattedTime. please issue your ticket before 
            time limit, otherwise your ticket will be cancel automatically";

            sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);
            sendToAgent($subject, $agentMessage, $agentId, $header, $property, $data);
        }     
    }
  }else{
    
  }

?>