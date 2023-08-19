<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require("../../vendor/autoload.php");

if (array_key_exists("bookingId", $_GET)) {
    $bookingId = $_GET["bookingId"];

  $sql = "SELECT * FROM `refund` where bookingId='$bookingId'";
  $result = $conn->query($sql);
 
  $return_arr = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){     

      $agentId = $row['agentId'];
      $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
      $data = mysqli_fetch_assoc($query);
      $companyname = $data['company'];
          
      $response = $row;
      $response['companyname'] ="$companyname";   
      array_push($return_arr, $response);
    }
  }

  echo json_encode($return_arr);
}else if(array_key_exists("approved", $_GET)){
    if ($_SERVER["REQUEST_METHOD"] == "POST"){
            
        $_POST = json_decode(file_get_contents('php://input'), true);
        
        $refundId = $_POST['refundId'];
        $bookingId = $_POST['bookingId'];
        $agentId = $_POST['agentId'];
        $refundAmount = $_POST['penalty'];
        $actionBy = $_POST['actionBy'];

        $createdTime = date("Y-m-d H:i:s");

        if(isset($bookingId)){
            $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
            $rowTravelDate = mysqli_fetch_array($sqlTravelDate,MYSQLI_ASSOC);
            
            if(!empty($rowTravelDate)){
                $travelDate = $rowTravelDate['travelDate'];
                $subagentId = $rowTravelDate['subagentId'];
                $pax = $rowTravelDate['pax'];
                $gds = $rowTravelDate['gds'];
                $pnr = $rowTravelDate['pnr'];
                $Type = $rowTravelDate['tripType'];
                $Airlines = $rowTravelDate['airlines'];
                $TicketId = $rowTravelDate['ticketId'];
                $TicketCost = $rowTravelDate['netCost'];
                $subagentCost = $rowTravelDate['subagentCost'];
                $arriveTo = $rowTravelDate['arriveTo'];
                $deptFrom = $rowTravelDate['deptFrom'];
                $tripType = $rowTravelDate['tripType'];
                $status = $rowTravelDate['status'];                                          
            } 
        }

      if($status == 'Refund In Processing'){

        $Route = "$deptFrom - $arriveTo";

        if(isset($refundId)){
            $sqlvoid = mysqli_query($conn, "SELECT * FROM refund WHERE bookingId='$bookingId'");
            $rowsqlvoid = mysqli_fetch_array($sqlvoid,MYSQLI_ASSOC);
            
            if(!empty($rowsqlvoid)){
                $refundrequestedBy = $rowsqlvoid['requestedBy'];
                $refundrequestedAt = $rowsqlvoid['requestedAt'];                                                        
            } 
        }

        $RefundtextBy = '';

        $refundPenalty = $TicketCost - ($refundAmount);

        $checkBalanced = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' ORDER BY id DESC LIMIT 1");
        $rowcheckBalanced = mysqli_fetch_array($checkBalanced,MYSQLI_ASSOC);        
        if(!empty($rowcheckBalanced)){
            $lastAmount = $rowcheckBalanced['lastAmount'];	 						
        }

        $newBalance = $lastAmount + $refundAmount;
        

        $sarefundPenalty = $subagentCost - ($refundAmount);

        $subagentsql1 = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' AND subagentId ='$subagentId' 
            ORDER BY id DESC LIMIT 1");
        $subagentrow1 = mysqli_fetch_array($subagentsql1,MYSQLI_ASSOC);        
        if(!empty($subagentrow1)){
            $salastAmount = $subagentrow1['lastAmount'];							
        }

        $sanewBalance = $salastAmount + $refundAmount;
       

        $sql="UPDATE `refund` SET `status`='approved',`amountRefunded`='$refundAmount',`penaltyAmount`='$refundPenalty',`actionBy`='$actionBy',`actionAt`='$createdTime' WHERE bookingId='$bookingId' AND agentId='$agentId'";
       
        $conn->query("INSERT INTO `agent_ledger`(`agentId`,`refund`, `lastAmount`, `transactionId`, `details`, `reference`, `actionBy`, `createdAt`)
         VALUES ('$agentId','$refundAmount','$newBalance','$bookingId','Refunded Money $TicketId Ticket Invoice $Type Air Ticket $Route - $Airlines was Requested By $refundrequestedBy','$refundId','$actionBy','$createdTime')");
         
        $conn->query("UPDATE `booking` SET `status`='Refunded',`lastUpdated`='$createdTime' where bookingId='$bookingId'");
        $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                VALUES ('$bookingId','$agentId','Refunded','Refund Given $refundAmount','$actionBy','$createdTime')");
            
               
        if($conn->query($sql) === TRUE){
           

                $response['status']="success";
                $response['RefundId']="$refundId";
                $response['message']="Refund Approved Successfully";
    
              }  
        
      }else{
        $response['status']="error";
        $response['message']="Ticket Already Refunded";
      }

      echo json_encode($response);   
    }

    
}else if(array_key_exists("reject", $_GET)){

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $_POST = json_decode(file_get_contents('php://input'), true);

      $bookingId = $_POST['bookingId'];
      $agentId = $_POST['agentId'];
      $actionBy = $_POST['actionBy'];
      $remarks = $_POST['remarks'];

      $createdTime = date("Y-m-d H:i:s");

      $sql="UPDATE `refund` SET `status`='rejected',`actionBy`='$actionBy',`actionAt`='$createdTime' WHERE bookingId='$bookingId' AND agentId='$agentId'";

      if ($conn->query($sql) === true) {
          $sqlBooking ="UPDATE `booking` SET `status`='Refund Rejected',`lastUpdated`='$createdTime' WHERE bookingId='$bookingId'";
          $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                      VALUES ('$bookingId','$agentId','Refund Rejected','$remarks','$actionBy','$createdTime')");
          
          if ($conn->query($sqlBooking) === true) {
              $response['status'] = "success";
              $response['message'] = "Refund Rejected Successfully";
          }
                  echo json_encode($response);
      }
    }
}
else if (array_key_exists('quotationsend', $_GET)) {
  if ($_SERVER["REQUEST_METHOD"] == "POST") {

      $_POST = json_decode(file_get_contents('php://input'), true);

      $bookingId = $_POST['bookingId']; 
      $agentId = $_POST['agentId'];
      $ActionBy = isset($_POST['actionBy']) ? $_POST['actionBy'] : "";
      $QuotationText = $_POST['text'];
      $Amount = $_POST['amount'];
      $createdTime = date("Y-m-d H:i:s");
      $remarkAmount = "Service Fee: ".$Amount; 

      if (isset($bookingId)) {
          $checker = $conn->query("SELECT * FROM refund WHERE bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);
          if (!empty($checker)) {
              $status = $checker[0]['status'];
              if ($status == 'Refund Quotation Send') {
                  $response['status'] = 'error';
                  $response['message'] = "Already Quotation Sending";
              } else {
                  $sql = "UPDATE `refund` SET `status`='Refund Quotation Send',`actionAt`='$createdTime', `quottext`='$QuotationText', `quotamount`='$Amount' WHERE bookingId='$bookingId' AND agentId='$agentId'";

                  if ($conn->query($sql) == true) {
                    $sql1 = "INSERT INTO `activitylog` (`status`, `actionBy`, `remarks`, `ref`, `agentId`) VALUES ('Refund Quotation Send', '$ActionBy', '$remarkAmount', '$bookingId', '$agentId')";
                    $conn->query($sql1);

                    $sql = "UPDATE `booking` SET `status`='Refund Quotation Send' WHERE bookingId='$bookingId' AND agentId='$agentId'";
                    $conn->query($sql);
                      $response['status'] = "success";
                      $response['message'] = 'Refund Quotation Send';
                  } else {
                      $response['status'] = "error";
                      $response['message'] = 'Query Failed';
                  }
                  
              }
          }
      }
      echo json_encode($response);

  }
}

if(array_key_exists('getquotadata', $_GET)){
  if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $_POST = json_decode(file_get_contents('php://input'), true);
      $agentId = $_POST['agentId'];
      $bookingId = $_POST['bookingId'];

      $data = $conn->query("SELECT quottext, quotamount,status FROM `refund` WHERE `bookingId` = '$bookingId' AND agentId = '$agentId'")->fetch_all(MYSQLI_ASSOC);
      if (!empty($data)) {
          echo json_encode($data);
      } else {
          $response['status'] = "error";
          $response['message'] = "Data Not Found";
      }
  }

}
if (array_key_exists('option', $_GET)) {

  if ($_SERVER['REQUEST_METHOD'] == "POST") {
      $_POST = json_decode(file_get_contents('php://input'), true);
      $Agent = $_POST['agentId'];
      $BookingId = $_POST['bookingId'];
      $Status = $_POST['option'];

      $checker = $conn->query("SELECT status FROM `refund` WHERE `bookingId` = '$BookingId' AND `agentId`='$Agent'")->fetch_all(MYSQLI_ASSOC);
      $status = $checker[0]['status'];
      if ($status == "Refund Quotation Confirm") {
          $response['status'] = "error";
          $response['message'] = "Quotation Already Confirmed";
          echo json_encode($response);
      } else if($status == "Refund Quotation Reject"){
          $response['status'] = "error";
          $response['message'] = "Quotation Already Reject";
          echo json_encode($response);
      }else if($status == "Refund Quotation Send") {
          if ($Status == "yes") {
              $sql = "UPDATE refund SET status = 'Refund Quotation Confirm' WHERE agentId = '$Agent' AND bookingId = '$BookingId'";
              
              if ($conn->query($sql)) {
               
                  $sql2 = "UPDATE booking SET status = 'Refund Quotation Confirm' WHERE agentId = '$Agent' AND bookingId = '$BookingId'";
                  $conn->query($sql2);
                  $sql3 = mysqli_query($conn, "SELECT quotamount FROM `refund` where agentId = '$Agent' AND bookingId = '$BookingId'
            ORDER BY id DESC LIMIT 1");
                  $row1 = mysqli_fetch_array($sql3, MYSQLI_ASSOC);

                  if (!empty($row1)) {
                      $quotamount = $row1['quotamount'];
                  }

                  if (!empty($quotamount)) {
                      $sql4 = mysqli_query($conn, "SELECT netCost FROM `booking` where agentId = '$Agent'
              ORDER BY id DESC LIMIT 1");

                      $row1 = mysqli_fetch_array($sql4, MYSQLI_ASSOC);
                      if (!empty($row1)) {
                          $netCost = $row1['netCost'];
                      }
                      
                      $refundDateAmount = $netCost - $quotamount;
                      $sql5 = mysqli_query($conn, "SELECT lastAmount FROM `agent_ledger` where agentId = '$Agent'
                      ORDER BY id DESC LIMIT 1");
        
                              $row2 = mysqli_fetch_array($sql5, MYSQLI_ASSOC);
                              if (!empty($row2)) {
                                  $lastAmount = $row2['lastAmount'];
                              }

                            $newBalance = $refundDateAmount + $lastAmount;
                           
                      $sql = "UPDATE `agent_ledger` SET lastAmount='$newBalance' WHERE `agentId`='$Agent' AND `reference`= '$BookingId'";
            
                      if ($conn->query($sql)) {
                      
                          $response['status'] = "success";
                          $response['message'] = "Quotation Approved Successfully";
                          echo json_encode($response);
                      }
                  }else{
                    echo "error";
                  }
              } else {
                  echo json_encode(array('status' => 'error'));
              }
          }
          if ($Status == "no") {
            $sql = "UPDATE refund SET status = 'Refund Quotation Reject' WHERE bookingId = '$BookingId' AND agentId = '$Agent'";
            if ($conn->query($sql)) {
                $sql = "UPDATE booking SET status = 'Refund Quotation Reject' WHERE agentId = '$Agent' AND bookingId = '$BookingId'";
                $conn->query($sql);
                $response['status'] = "successs";
                $response['message'] = "Quotation Refund Rejected ";
            } else {
                $response['status'] = "error";
                $response['message'] = "Quotation Refund Rejected Failed Successfully";
            }
            echo json_encode($response);
        }
      }
  }

}


  