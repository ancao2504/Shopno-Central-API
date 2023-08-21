<?php

require '../config.php';
require '../emailfunction.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require("../vendor/autoload.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $_POST = json_decode(file_get_contents('php://input'), true);
  $bookingId = $_POST["bookingId"];
  $Platform = $_POST["platform"];
  $cancelBy = $_POST['cancelBy'];

  $createdTime = date("Y-m-d H:i:s");

  $query = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
  $data = mysqli_fetch_assoc($query);
  $agentId = $data["agentId"];
  $staffId = $data["staffId"];
  $status = $data["status"];
  $pnr = $data['pnr'];
  $system = $data['gds'];
  $deptFrom = $data['deptFrom'];
  $arriveTo = $data['arriveTo'];
  $airlines = $data['airlines'];
  $tripType = $data['tripType'];

  if ($status == 'Hold' || $status == 'Issue In Processing') {

    $DateTime = date("D d M Y h:i A");

    $queryAgent = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
    $dataAgent = mysqli_fetch_assoc($query);
    $companyname = $data["agentId"];

    $staffdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `staffList` where staffId='$staffId' AND agentId='$agentId'"));

    if (!empty($staffdata)) {
      $staffName = $staffdata['name'];
      $Message = "Cancelled By: $staffName, $companyname";
    } else {
      $Message = "Cancelled By: $companyname";
    }


    if ($system == "FlyHub") {

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
        CURLOPT_POSTFIELDS => '{
        "username": "ceo@flyfarint.com",
        "apikey": "ENex7c5Ge+0~SGc1t71iccr1xXacDPdK51g=iTm9SlL+de39HF"
        }',
        CURLOPT_HTTPHEADER => array(
          'Content-Type: application/json'
        ),
      ));

      $Tokenresponse = curl_exec($curlflyhubauth);

      $TokenJson = json_decode($Tokenresponse, true);

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
            "BookingID": "' . $pnr . '"
        }',
        CURLOPT_HTTPHEADER => array(
          'Content-Type: application/json',
          "Authorization: Bearer $FlyhubToken"
        ),
      ));

      $FlyHubresponse = curl_exec($curl);

      curl_close($curl);

      $sql = "UPDATE `booking` SET `status`='Cancelled' where bookingId='$bookingId'";

      if (isset($agentId)) {
        $sql1 = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
        $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

        if (!empty($row1)) {
          $agentEmail = $row1['email'];
          $companyname = $row1['company'];
        }
      }

      if ($conn->query($sql) === TRUE) {
        $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`platform`,`actionBy`, `actionAt`)
                             VALUES ('$bookingId','$agentId','Cancalled',' ','$Platform','$cancelBy','$createdTime')");



        $response['status'] = "success";
        $response['BookingId'] = "$bookingId";
        $response['message'] = "Booking Cancelled Successfully";

        echo json_encode($response);
      }
    } else if ($system == "Sabre") {
      try {

        $client_id = base64_encode("V1:351640:27YK:AA");
        $client_secret = base64_encode("spt5164");

        $token = base64_encode($client_id . ":" . $client_secret);
        $data = 'grant_type=client_credentials';

        $headers = array(
          'Authorization: Basic ' . $token,
          'Accept: /',
          'Content-Type: application/x-www-form-urlencoded'
        );

        $ch = curl_init();
        //curl_setopt($ch,CURLOPT_URL,"https://api-crt.cert.havail.sabre.com/v2/auth/token");
        curl_setopt($ch, CURLOPT_URL, "https://api.platform.sabre.com/v2/auth/token");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        $resf = json_decode($res, 1);
        $access_token = $resf['access_token'];

        //print_r($resf);

      } catch (Exception $e) {
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
        CURLOPT_POSTFIELDS => '{
      "confirmationId": "' . $pnr . '",
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
      $sql = "UPDATE `booking` SET `status`='Cancelled' where bookingId='$bookingId'";

      if (isset($agentId)) {
        $sql1 = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
        $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

        if (!empty($row1)) {
          $agentEmail = $row1['email'];
          $companyname = $row1['company'];
        }
      }


      if ($conn->query($sql) === TRUE) {
        $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`platform`,`actionBy`, `actionAt`)
                             VALUES ('$bookingId','$agentId','Cancelled',' ','$Platform','$cancelBy','$createdTime')");

        $subject = "Booking Cancelled";
        $header = $subject;
        $property = "Booking ID: ";
        $data = $bookingId;
        $adminMessage = "Your booking request has been caneled.";
        $agentMessage = "Our booking request has been caneled.";

        sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);
        sendToAgent($subject, $agentMessage, $agentId, $header, $property, $data);

        $response['status'] = "success";
        $response['BookingId'] = "$bookingId";
        $response['message'] = "Booking Cancalled Successfully";
      }
      echo json_encode($response);
    }
  } else if ($status == 'Cancelled') {
    $response['status'] = "success";
    $response['message'] = "Booking Already Cancelled";
    echo json_encode($response);
  } else {

    $response['status'] = "success";
    $response['message'] = "Cannot Cancelled Booking. Because BookingRef-$bookingId Already in $status";
    echo json_encode($response);
  }
} else {
  echo json_encode("Data Missing");
}
