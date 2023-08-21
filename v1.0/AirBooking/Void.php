<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require("../vendor/autoload.php");

if ($_SERVER["REQUEST_METHOD"] == "POST"){
        
    $_POST = json_decode(file_get_contents('php://input'), true);
    
    
    $voidId ="";
    $sql1 = "SELECT * FROM void ORDER BY voidId DESC LIMIT 1";
    $result = $conn->query($sql1);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $outputString = preg_replace('/[^0-9]/', '', $row["voidId"]); 
            $number= (int)$outputString + 1;
            $voidId = "STVD$number"; 								
        }
    } else {
            $voidId ="STVD1000";
    }
    
    $agentId = $_POST["agentId"];
    $bookingId = $_POST["bookingId"];
    $staffId = $_POST["staffId"];
    $requestedBy = $_POST["requestedBy"];
    $paxDetails = $_POST['passengerData'];

    $passData = array();
    foreach($paxDetails as $paxDet){
      $name = $paxDet['name'];
      $ticket = $paxDet['ticket'];
      
      $data = "($name-$ticket)";
      array_push($passData, $data);
    }

    $dataPax = implode('',$passData);
    
    
    $createdTime = date('Y-m-d H:i:s');
    $DateTime = date("D d M Y h:i A");


    if(isset($bookingId)){
      $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
      $rowTravelDate = mysqli_fetch_array($sqlTravelDate,MYSQLI_ASSOC);
      
      if(!empty($rowTravelDate)){
        $travelDate = $rowTravelDate['travelDate'];
        $pax = $rowTravelDate['pax'];
        $gds = $rowTravelDate['gds'];
        $pnr = $rowTravelDate['pnr'];
        $Type = $rowTravelDate['tripType'];
        $Airlines = $rowTravelDate['airlines'];
        $TicketId = $rowTravelDate['ticketId'];
        $TicketCost = $rowTravelDate['netCost'];
        $arriveTo = $rowTravelDate['arriveTo'];
        $deptFrom = $rowTravelDate['deptFrom'];
        $tripType = $rowTravelDate['tripType'];
        
        							
      } 
    }


    if(isset($agentId)){
        $sql1 = mysqli_query($conn,"SELECT * FROM agent WHERE agentId='$agentId'");
        $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);

        if(!empty($row1)){
            $agentEmail = $row1['email'];
            $companyname = $row1['company'];							
        } 
        
    }

    $staffName;
    $staffsql2 = mysqli_query($conn,"SELECT * FROM `staffList` where agentId = '$agentId' AND staffId='$staffId'");
        $staffrow2 = mysqli_fetch_array($staffsql2,MYSQLI_ASSOC);  
        
        if(!empty($staffrow2)){
          $staffName = $staffrow2['name'];
          $voidBy = $staffrow2['name'];
          $voidtextBy = "Void Request By: $voidBy, $companyname";
        }else{
          
           $voidtextBy = "Void Request By: $companyname";
      }

    $sql = "INSERT INTO `void`(`voidId`, `agentId`, `bookingId`,`ticketId`,`passengerDetails`,`status`,`requestedBy`,`requestedAt`)
             VALUES ('$voidId','$agentId','$bookingId','$TicketId','$dataPax','pending','$requestedBy','$createdTime')";
   
  if ($conn->query($sql) === TRUE) {
    $conn->query("UPDATE `booking` SET `status`='Void In Processing',`voidId`='$voidId',`lastUpdated`='$createdTime' where bookingId='$bookingId'");
    $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionRef`,`actionBy`,`actionAt`)
            VALUES ('$bookingId','$agentId','Void In Processing',' ','$voidId','$requestedBy','$createdTime')");

                $response['status']="success";
                $response['InvoiceId']="$voidId";
                $response['message']="Ticket Void Request Successfully";

      echo json_encode($response);
    
    }

  }
  