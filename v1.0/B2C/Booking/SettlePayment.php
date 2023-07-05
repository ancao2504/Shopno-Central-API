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
    
    $agentId = $_POST['agentId'];
    $bookingId = $_POST['bookingId'];
    $staffId = isset($_POST['staffId']) ? $_POST['staffId'] : 'Agent';

    $createdTime = date("Y-m-d H:i:s");

      $staffName='';
      $staffsql2 = mysqli_query($conn,"SELECT * FROM `staffList` where agentId = '$agentId' AND staffId='$staffId'");
        $staffrow2 = mysqli_fetch_array($staffsql2,MYSQLI_ASSOC);  
        

    if(!empty($staffrow2)){
          $IssueBy = $staffrow2['name'];
        }else{
          $IssueBy = 'Agent';
      }


    if(isset($bookingId)){
      $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
      $rowTravelDate = mysqli_fetch_array($sqlTravelDate,MYSQLI_ASSOC);
      
      $dueAmount=0;
      if(!empty($rowTravelDate)){
        $travelDate = $rowTravelDate['travelDate'];
        $pax = $rowTravelDate['pax'];
        $Type = $rowTravelDate['tripType'];
        $Airlines = $rowTravelDate["airlines"];
        $From = $rowTravelDate['deptFrom'];
        $To = $rowTravelDate['arriveTo'];
        $Route = "$From - $To";
        $PNR = $rowTravelDate['pnr'];
        $GDS = $rowTravelDate['gds'];
        $Status = $rowTravelDate['status'];
        $netCost = $rowTravelDate["netCost"];
        $PPstatus = $rowTravelDate['PPstatus'];
        $isPartial = $rowTravelDate['isPartial'];
        $paidAmount = $rowTravelDate['paidAmount'];
        $dueAmount = $rowTravelDate['dueAmount'];     							
      } 
    }

    if(isset($PPstatus) && !empty($PPstatus) && ($PPstatus == 'unpaid')){

        $sql1 = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' 
            ORDER BY id DESC LIMIT 1");
        $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);        
        if(!empty($row1)){
            $lastAmount = $row1['lastAmount'];			
        }

        $Remarks = "Paid Due Amount : $dueAmount";


        if($lastAmount >= $dueAmount){
            $newBalance = $lastAmount - $dueAmount;

            $LeaderUpdate = "INSERT INTO `agent_ledger`(`PPstatus`,`agentId`,`purchase`, `lastAmount`, `transactionId`, `details`, `reference`,`actionBy`,`createdAt`)
            VALUES ('Settle','$agentId','$dueAmount','$newBalance','$bookingId','$Type Air Ticket $Route - $Airlines - Paid Due Amount. Settle Payment Of $bookingId','$bookingId','$staffName','$createdTime')";

            if ($conn->query($LeaderUpdate) === TRUE) {
                $conn->query("UPDATE `agent_ledger` SET `PPstatus`='paid' where reference='$bookingId'");
                $conn->query("UPDATE `subagent_ledger` SET `PPstatus`='paid' where reference='$bookingId'");
                
                $conn->query("INSERT INTO `activityLog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`,`actionAt`)
                                VALUES ('$bookingId','$agentId','Settle Purchase','$Remarks','$IssueBy','$createdTime')");
            
                $conn->query("UPDATE `booking` SET `PPstatus`='paid',`paidAmount`='$paidAmount',`dueDate`='' where bookingId='$bookingId'");

                $response['status'] = 'success';
                $response['message'] ='Payment Settle Successfully';

                echo json_encode($response);
            }
            
        }else{
            $response['status'] = 'success';
            $response['mesage'] ='Insufficient funds';

            echo json_encode($response);
        }
    }else{
      $response['status'] ='error';
      $response['message'] ='You already paid';

      echo json_encode($response);
    }

}