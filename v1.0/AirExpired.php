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
$DateTime = date("D d M Y h:i A");

$sql = "SELECT * FROM `booking` where status ='Hold' AND gds='Sabre' AND timeLimit BETWEEN '1970-12-12' AND '$Today'";
$result = $conn->query($sql);

  $return_arr = array();
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()){ 
        $agentId = $row['agentId'];
        $bookingId = $row['bookingId'];
        $pnr = $row['pnr'];
        $gds = $row['gds'];  

    $bookingdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `booking` where bookingId='$bookingId'")); 
    
    $From = $bookingdata['deptFrom'];
    $To = $bookingdata['arriveTo'];
    $tripType = $bookingdata['tripType'];
    $Airlines = $bookingdata['airlines'];
        
        

        //Agent Info
    $agentdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `agent` where agentId='$agentId'"));
    $companyName = $agentdata['company'];
    $companyEmail = $agentdata['email'];

    //staff Info
    $staffName = "";
    $staffsql = mysqli_query($conn, "SELECT * FROM `staffList` where agentId='$agentId'");
    $staffdata = mysqli_fetch_array($staffsql);
    if (isset($staffdata['name'])) {
      $staffName = "Your Staff ".$staffdata['name'];
    } else {
      $staffName = "Agent";
    }

        if($gds =='FlyHub'){

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
                "BookingID": "'.$pnr.'"
                }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: Bearer $FlyhubToken"
            ),
            ));

            $FlyHubresponse = curl_exec($curl);
            $FlyHubResult = json_decode($FlyHubresponse,true);

            curl_close($curl);

            $conn->query("UPDATE `booking` SET `status`='Cancelled' where pnr='$pnr'");
                
            
        }else if ($gds =='Sabre'){
        
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
                    "confirmationId": "'.$pnr.'",
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
                $SabreResponseResult  = json_decode($SabreResponse, true);
                curl_close($curl);

                $conn->query("UPDATE `booking`
                    SET `status`='Cancelled' where pnr='$pnr'");
                        
        }
        
        $conn->query("INSERT INTO `jobs`(`text`, `status`, `message`)
                    VALUES ('$bookingId','success','$bookingId Cancelled Done')");

    
    $subject = $header = "Booking Expired";
    $property = "Booking ID: ";
    $data = $bookingId;
    $adminMessage = "Our booking Request has been Expired.";
    $agentMessage = "Your Booking Request has been Expired.";

    sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);
    sendToAgent($subject, $agentMessage, $agentId, $header, $property, $data);
      
      
    }
  }else{
    
  }

?>