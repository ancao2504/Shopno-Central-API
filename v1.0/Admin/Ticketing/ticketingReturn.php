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

if ($_SERVER["REQUEST_METHOD"] == "POST"){
       
    $_POST = json_decode(file_get_contents('php://input'), true);  
    
    $bookingId = $_POST["bookingId"];
    $actionBy = $_POST["actionBy"];
    $remarks = $_POST["remarks"];    

    $sqlTicketing = mysqli_query($conn,"SELECT * FROM booking WHERE bookingId='$bookingId'");
    $rowTicketing = mysqli_fetch_array($sqlTicketing,MYSQLI_ASSOC);

    if(!empty($rowTicketing)){
        $agentId = $rowTicketing["agentId"];
        $subagentId = $rowTicketing["subagentId"];
        $bookingId = $rowTicketing["bookingId"];
        $staffId = $rowTicketing["staffId"];
        $Airlines = $rowTicketing["airlines"];
        $Type = $rowTicketing['tripType'];
        $netCost = $rowTicketing["netCost"];
        $status = $rowTicketing['status'];
        $subagentCost = $rowTicketing["subagentCost"];
        $travelDate = $rowTicketing['travelDate'];
        $From = $rowTicketing['deptFrom'];
        $To = $rowTicketing['arriveTo'];
        $Route = "$From - $To";      
        $pax = $rowTicketing['pax'];
        $GDS = $rowTicketing['gds'];
        $PNR = $rowTicketing['pnr'];
        $Bonus = $rowTicketing['bonus'];  

    }

  if($status == "Issue In Processing"){
    
    $createdTime = date('Y-m-d H:i:s');

    $DateTime = date("D d M Y h:i A");

  
    if(isset($agentId)){
        $sql1 = mysqli_query($conn,"SELECT * FROM agent WHERE agentId='$agentId'");
        $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);

        if(!empty($row1)){
            $agentEmail = $row1['email'];
            $companyname = $row1['company'];
            $bonusAmount = $row1['bonus'];							
        } 
        
    }

    $staffName;
    $staffsql2 = mysqli_query($conn,"SELECT * FROM `staffList` where agentId = '$agentId' AND staffId='$staffId'");
        $staffrow2 = mysqli_fetch_array($staffsql2,MYSQLI_ASSOC);  
        

        if(!empty($staffrow2)){
          $staffName = $staffrow2['name'];
            $Message = "Dear $companyname, Your staff $staffName  $Route  $Type on $Airlines, 
             air ticket booking issue request has been on process at $DateTime. Thank you
              again for booking with Flyway International";	
              
              $OwnerMessage = "Dear Flyway International, Our staff $staffName has been 
              requested for $Route $Type  on $Airlines,  air ticket which has been issue in
              Process on $DateTime";

              $IssueBy = $staffrow2['name'];
              $IssuetextBy = "Issue Request By: $IssueBy, $companyname";
        }else{
          $Message="Dear $companyname, Your $Route $Type  on $Airlines,
          air ticket booking issue request has been on process at $DateTime. Thank you
           again for booking with Flyway International";

           $OwnerMessage = "Dear Flyway International, We have been 
          requested for $Route $Type on $Airlines, air ticket which has been issue in
          process on at $DateTime";
          
           $IssuetextBy = "Issue Request By: $companyname";
      }
         

    $sql1 = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' 
            ORDER BY id DESC LIMIT 1");
        $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);        
        if(!empty($row1)){
            $lastAmount = $row1['lastAmount'];							
        }

        $newBalance = $lastAmount + $netCost;

  
         if($Bonus =="yes"){
              $BonusAmountadded = $bonusAmount + 100;
              $conn->query("UPDATE `agent` SET `bonus` = '$BonusAmountadded' where agentId = '$agentId'");
          }

    $sql = "INSERT INTO `agent_ledger`(`agentId`,`returnMoney`, `lastAmount`, `transactionId`, `details`, `reference`, `actionBy`, `createdAt`)
         VALUES ('$agentId','$netCost','$newBalance','$bookingId','Return Money $Type Air Ticket $Route - $Airlines','$bookingId','$actionBy','$createdTime')";

  if($conn->query($sql)){
    $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                    VALUES ('$bookingId','$agentId','Issue Rejecteed','$remarks','$actionBy','$createdTime')");
                    
    $conn->query("UPDATE `booking` SET `status`='Issue Rejected',`lastUpdated`='$createdTime' where bookingId='$bookingId'");

    

    if($GDS == "FlyHub"){

        $curlflyhubauth = curl_init();

        curl_setopt_array($curlflyhubauth, array(
        CURLOPT_URL => 'https://api.flyhub.com/api/v1/Authenticate',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
        "username": "ceo@flyfarint.com",
        "apikey": "ENex7c5Ge+0~SGc1t71iccr1xXacDPdK51g=iTm9SlL+de39HF"
        }',
        CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
        ),
        ));

        $Tokenresponse = curl_exec($curlflyhubauth);

        $TokenJson = json_decode($Tokenresponse,true);

        $FlyhubToken = $TokenJson['TokenId'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.flyhub.com/api/v1/AirCancel',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
            "BookingID": "'.$PNR.'"
        }',
        CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        "Authorization: Bearer $FlyhubToken"
        ),
        ));

        $FlyHubresponse = curl_exec($curl);

        curl_close($curl);

    }else if($GDS == "Sabre"){
      try{
        $client_id= base64_encode("V1:351640:27YK:AA");
		    $client_secret = base64_encode("spt5164");

      $token = base64_encode($client_id.":".$client_secret);
      $data='grant_type=client_credentials';

        $headers = array(
          'Authorization: Basic '.$token,
          'Accept: /',
          'Content-Type: application/x-www-form-urlencoded'
        );

        $ch = curl_init();
        //curl_setopt($ch,CURLOPT_URL,"https://api-crt.cert.havail.sabre.com/v2/auth/token");
        curl_setopt($ch,CURLOPT_URL,"https://api.platform.sabre.com/v2/auth/token");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        $resf = json_decode($res,1);
        $access_token = $resf['access_token'];

        //print_r($resf);

      }catch (Exception $e){
        
      }


    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.platform.sabre.com/v1/trip/orders/cancelBooking',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "confirmationId": "'.$PNR.'",
        "retrieveBooking": true,
        "cancelAll": true,
        "errorHandlingPolicy": "ALLOW_PARTIAL_CANCEL"
    }',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Conversation-ID: 2021.01.DevStudio',
        "Authorization: Bearer $access_token"
      ),
    ));

    $SabreResponse = curl_exec($curl);
      curl_close($curl);   
    } 

  
    $response['status']="success";
    $response['InvoiceId']="$bookingId";
    $response['message']="Ticketing Refund Successfully";

    }
  }else{
    $response['status']="error";
    $response['message']="Ticket Already Rejected";
  }
  echo json_encode($response);
    
}

  