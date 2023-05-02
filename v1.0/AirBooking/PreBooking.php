<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require("../vendor/autoload.php");

if ($_SERVER["REQUEST_METHOD"] == "POST"){
        
    $_POST = json_decode(file_get_contents('php://input'), true);

    if(!empty($_POST['pnr'] )){
      $Booking_Id ="";
      $sql1 = "SELECT * FROM booking ORDER BY bookingId DESC LIMIT 1";
      $result = $conn->query($sql1);
      if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
              $outputString = preg_replace('/[^0-9]/', '', $row["bookingId"]); 
              $number= (int)$outputString + 1;
              $Booking_Id = "FFB$number"; 								
          }
      } else {
              $Booking_Id ="FFB1000";
      }
      
      $agentId = $_POST["agentId"];
      $staffId = $_POST["staffId"];
      $System = $_POST["system"]; 
      $From = $_POST["from"];
      $To = $_POST["to"];
      $Airlines = $_POST["airlines"];
      $Type = $_POST["tripType"];
      $journeyType = $_POST["journeyType"];
      $Name = strtoupper($_POST["name"]);
      $Phone = $_POST["phone"];
      $Email = $_POST["email"];
      $Pnr = $_POST["pnr"];
      $Pax = $_POST["pax"];
      $Refundable = $_POST["refundable"];
      $adultCount = $_POST["adultcount"]; 
      $childCount = $_POST["childcount"]; 
      $infantCount = $_POST["infantcount"];
      $adultBag = $_POST["adultbag"]; 
      $childBag = $_POST["childbag"]; 
      $infantBag = $_POST["infantbag"];
      $netCost = $_POST["netcost"];    
      $adultCostBase = $_POST["adultcostbase"]; 
      $childCostBase = $_POST["childcostbase"]; 
      $infantCostBase = $_POST["infantcostbase"];
      $adultCostTax = $_POST["adultcosttax"]; 
      $childCostTax = $_POST["childcosttax"]; 
      $infantCostTax = $_POST["infantcosttax"];
      $grossCost = $_POST["grosscost"];
      $BaseFare = $_POST["basefare"];
      $taxFare = $_POST["tax"];
      $Coupon = $_POST["coupon"];
      
      if(isset($_POST["uId"])){
        $uId = $_POST["uId"];
      }else{
        $uId = '';
      }

      if(isset($_POST["travelDate"])){
        $travelDate = $_POST["travelDate"];
      }else{
        $travelDate = date("Y-m-d H:i", strtotime("+6 hours"));
      }
      
      if(empty($_POST["timeLimit"])){

        $JourneyDateTime = date_create($travelDate);
        $JourneyDTime = date_format($JourneyDateTime, "Y-m-d H:i");
        $diff_time= round(((strtotime($travelDate)) - strtotime(date("Y-m-d H:i")))/3600);

        if($diff_time > 146){
          $newTimeLimit = date("Y-m-d H:i", strtotime("+24 hours"));
        }else if($diff_time > 122 && $diff_time < 146){
          $newTimeLimit = date("Y-m-d H:i", strtotime("+22 hours"));
        }else if($diff_time > 98 && $diff_time < 122){
          $newTimeLimit = date("Y-m-d H:i", strtotime("+18 hours"));
        }else if ($diff_time > 84 && $diff_time < 98) {
          $newTimeLimit = date("Y-m-d H:i", strtotime("+16 hours"));
        }else if($diff_time > 84 && $diff_time < 98){
          $newTimeLimit = date("Y-m-d H:i", strtotime("+12 hours"));
        }else if($diff_time > 72 && $diff_time < 84){
          $newTimeLimit = date("Y-m-d H:i", strtotime("+6 hours"));
        }else if($diff_time > 24 && $diff_time < 72) {
          $newTimeLimit = date("Y-m-d H:i", strtotime("+5 hours"));
        }else if($diff_time >12 && $diff_time < 24){
          $newTimeLimit = date("Y-m-d H:i", strtotime("+3 hours"));
        }else if($diff_time > 6 && $diff_time < 12){
          $newTimeLimit = date("Y-m-d H:i", strtotime("+60 minutes"));
        }else if($diff_time > 4 && $diff_time < 6){
          $newTimeLimit = date("Y-m-d H:i", strtotime("+20 minutes"));
        }else if($diff_time >2 && $diff_time < 4){
          $newTimeLimit = date("Y-m-d H:i", strtotime("+10 minutes"));
        }else if($diff_time <2){
          $newTimeLimit = date("Y-m-d H:i", strtotime("+5 minutes"));
        }

        $LastTicketTime = $newTimeLimit;
              
      }else{
        $LastTicketTime = $_POST["timeLimit"];
      }

      $DateTime = date("D d M Y h:i A");

      
      
      $dateTime = date('Y-m-d H:i:s');
      if(isset($_POST["SearchID"]) && $_POST["ResultID"]){
              $searchId = $_POST["SearchID"];
              $resultId = $_POST["ResultID"];
      }else{
              $searchId = '';
              $resultId = '';
      }

      $couponSql = mysqli_query($conn,"SELECT * FROM coupon WHERE coupon='$Coupon'");
      $couponRow = mysqli_fetch_array($couponSql,MYSQLI_ASSOC);

      
      if(isset($agentId)){
          $sql1 = mysqli_query($conn,"SELECT * FROM agent WHERE agentId='$agentId'");
          $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);

          if(!empty($row1)){
              $agentEmail = $row1['email'];
              $companyname = $row1['company'];
              $Bonus = $row1['bonus'];				
          }          
      }

        $staffsql2 = mysqli_query($conn,"SELECT * FROM `staffList` where agentId = '$agentId' AND staffId='$staffId'");
        $staffrow2 = mysqli_fetch_array($staffsql2,MYSQLI_ASSOC);        
        if(!empty($staffrow2)){
            $staffName = $staffrow2['name'];
            $BookedBy = $staffrow2['name'];							
        }else{
          $BookedBy = "Agent";
        }

        


        if(empty($staffId) && !empty($agentId)){
            $Message = "Dear $companyname,  you have been requested for $From to $To $Type air ticket on $DateTime, career Name: <b>$Airlines</b>. Your booking request has been accepted. Your booking time limit  $LastTicketTime. Please issue your ticket before giving time limit. Thank you for booking with Flyway International ";
            $Booked = "Booked By: $companyname";
        }
        else if(!empty($staffId) && !empty($agentId)){
          $Message = "Dear $companyname, your staff $staffName has sent booking request $From to $To $Type  air ticket on $DateTime, Career Name: <b>$Airlines</b>. Your booking request has been accepted. Your need to immediately issue this ticket. Otherwise your booking request has been cancelled. Thank you for booking with Flyway International";

          $Booked = "Booked By: $staffName,  $companyname";
         }
        else if(!empty($staffId) && !empty($agentId) && !empty($LastTicketTime)){
            
          $Message = "Dear $companyname, your staff $staffName has sent booking request $From to $To $Type  air ticket on $DateTime, Career Name: <b>$Airlines</b>. Your booking request has been accepted. Your booking time limit  $LastTicketTime. Please issue your ticket before giving time limit. Thank you for booking with Flyway International";

          $Booked = "Booked By: $staffName,  $companyname";
        }

        $createdTime = date("Y-m-d H:i:s");
  
        



      $sql = "INSERT INTO `booking`(
                          `uid`,
                          `bookingId`,
                          `agentId`,
                          `staffId`,
                          `email`,
                          `phone`,
                          `name`,
                          `refundable`,
                          `pnr`,                        
                          `tripType`,
                          `journeyType`,
                          `pax`,
                          `adultBag`,
                          `childBag`,
                          `infantBag`,
                          `adultCount`,
                          `childCount`,
                          `infantCount`,
                          `netCost`,
                          `adultCostBase`,
                          `childCostBase`,
                          `infantCostBase`,
                          `adultCostTax`,
                          `childCostTax`,
                          `infantCostTax`,
                          `grossCost`,
                          `baseFare`,
                          `Tax`,
                          `deptFrom`,
                          `airlines`,
                          `arriveTo`,
                          `gds`,
                          `status`,
                          `coupon`,
                          `travelDate`,
                          `timeLimit`,
                          `bookedAt`,
                          `bookedBy`,
                          `searchId`,
                          `resultId`,
                          `lastUpdated`)
                          
  VALUES('$uId','$Booking_Id','$agentId','$staffId','$Email','$Phone','$Name','$Refundable','$Pnr','$Type','$journeyType','$Pax','$adultBag','$childBag','$infantBag','$adultCount','$childCount','$infantCount',
        '$netCost','$adultCostBase','$childCostBase','$infantCostBase','$adultCostTax','$childCostTax','$infantCostTax','$grossCost',
        '$BaseFare','$taxFare','$From','$Airlines','$To','$System','Hold','$Coupon','$travelDate','$LastTicketTime','$dateTime','$BookedBy','$searchId','$resultId','$createdTime')";
    
      if ($conn->query($sql) === TRUE) {
                $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`actionBy`, `actionAt`)
                             VALUES ('$Booking_Id','$agentId','Hold','$BookedBy','$dateTime')");
                         
                $AgentMail ='
                <!DOCTYPE html>
                <html lang="en">
                  <head>
                    <meta charset="UTF-8" />
                    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                    <title>Deposit Request 
                </title>
                  </head>
                  <body>
                    <div
                      class="div"
                      style="
                        width: 650px;
                        height: 100vh;
                        margin: 0 auto;
                      "
                    >
                      <div
                        style="
                          width: 650px;
                          height: 200px;
                          background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
                          border-radius: 20px 0px  20px  0px;
                
                        "
                      >
                        <table
                          border="0"
                          cellpadding="0"
                          cellspacing="0"
                          align="center"
                          style="
                            border-collapse: collapse;
                            border-spacing: 0;
                            padding: 0;
                            width: 650px;
                            border-radius: 10px;
                
                          "
                        >
                          <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #000000;
                                font-family: sans-serif;
                                font-weight: bold;
                                font-size: 20px;
                                line-height: 38px;
                                padding-top: 30px;
                                padding-bottom: 10px;
                              "
                            >
                              <a href="https://www.flyfarint.com/"
                                ><img
                                src="https://cdn.flyfarint.com/logo.png"
                                  width="130px"
                              /></a>
                
                            </td>
                          </tr>
                        </table>
                
                        <table
                          border="0"
                          cellpadding="0"
                          cellspacing="0"
                          align="center"
                          bgcolor="white"
                          style="
                            border-collapse: collapse;
                            border-spacing: 0;
                            padding: 0;
                            width: 550px;
                          "
                        >
                          <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #000000;
                                font-family: sans-serif;
                                text-align: left;
                                padding-left: 20px;
                                font-weight: bold;
                                font-size: 19px;
                                line-height: 38px;
                                padding-top: 20px;
                                background-color: white;
                
                
                              "
                            >
                              Booking Request
                            </td>
                          </tr>
                          <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                font-family: sans-serif;
                                text-align: left;
                                padding-left: 20px;
                                font-weight: bold;
                                padding-top: 15px;
                                font-size: 12px;
                                line-height: 18px;
                                color: #929090;
                                padding-right: 20px;
                                background-color: white;
                
                              "
                            >
                             '.$Message.'
                            </td>
                          </tr>
                
                          <tr>
                          <td
                            align="center"
                            valign="top"
                            style="
                              border-collapse: collapse;
                              border-spacing: 0;
                              color: #000000;
                              font-family: sans-serif;
                              text-align: left;
                              padding-left: 20px;
                              font-weight: bold;
                              padding-top: 20px;
                              font-size: 13px;
                              line-height: 18px;
                              color: #929090;
                              padding-top: 20px;
                              width: 100%;
                              background-color: white;
                            "
                          >
                          <span style="color:#003566 ;">  '.$Booked.' </span>
                          </td>
                        </tr>
              
                          
                          <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #000000;
                                font-family: sans-serif;
                                text-align: left;
                                padding-left: 20px;
                                font-weight: bold;
                                padding-top: 20px;
                                font-size: 13px;
                                line-height: 18px;
                                color: #929090;
                                padding-top: 20px;
                                width: 100%;
                              "
                            >
                              Booking Id:
                              <a style="color: #003566" href="http://" target="_blank"
                                >'.$Booking_Id.'</a
                              >
                            </td>
                          </tr>
                
                
                                                                  <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #000000;
                                font-family: sans-serif;
                                text-align: left;
                                padding-left: 20px;
                                font-weight: bold;
                                padding-top: 20px;
                                font-size: 13px;
                                line-height: 18px;
                                color: #929090;
                                padding-top: 20px;
                                width: 100%;
                                background-color: white;
                
                              "
                            >
                                   If you have any questions, just contact us we are always happy to
                              help you out.
                            </td>
                          </tr>
                
                
                             <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #000000;
                                font-family: sans-serif;
                                text-align: left;
                                padding-left: 20px;
                                font-weight: bold;
                                padding-top: 20px;
                                font-size: 13px;
                                line-height: 18px;
                                color: #929090;
                                padding-top: 20px;
                                width: 100%;
                                background-color: white;
                
                              "
                            >
                               Sincerely,
                
                            </td>
                          </tr>
                
                             <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #000000;
                                font-family: sans-serif;
                                text-align: left;
                                padding-left: 20px;
                                font-weight: bold;
                                font-size: 13px;
                                line-height: 18px;
                                color: #929090;
                                width: 100%;
                                background-color: white;
                                padding-bottom: 20px
                
                              "
                            >
                              Flyway International
                
                            </td>
                          </tr>
                
                     
                          <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #ffffff;
                                font-family: sans-serif;
                                text-align: center;
                                font-weight: 600;
                                font-size: 14px;
                                color: #ffffff;
                                padding-top: 15px;
                                background-color: #dc143c;
                              "
                            >
                              Need more help?
                            </td>
                          </tr>
                
                          <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #ffffff;
                                font-family: sans-serif;
                                text-align: center;
                                font-size: 12px;
                                color: #ffffff;
                                padding-top: 8px;
                                padding-bottom: 20px;
                                padding-left: 30px;
                                padding-right: 30px;
                                background-color: #dc143c;
                
                
                              "
                            >
                              Mail us at
                              <a
                                style="color: white; font-size: 13px; text-decoration: none"
                                href="http://"
                                target="_blank"
                                >support@flyfarint.com
                              </a>
                              agency or Call us at 09606912912
                            </td>
                          </tr>
                
                          <tr>
                            <td
                              valign="top"
                              align="left"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #000000;
                                font-family: sans-serif;
                                text-align: left;
                                font-weight: bold;
                                font-size: 12px;
                                line-height: 18px;
                                color: #929090;
                              "
                            >
                
                            <p> <a
                                style="
                                  font-weight: bold;
                                  font-size: 12px;
                                  line-height: 15px;
                                  color: #222222;
                
                                "
                                href="https://www.flyfarint.com/terms"
                                >Terms & Conditions</a
                              >
                              <a
                                style="
                                  font-weight: bold;
                                  font-size: 12px;
                                  line-height: 15px;
                                  color: #222222;
                                  padding-left: 10px;
                                "
                                href="https://www.flyfarint.com/privacy"
                                >Privacy Policy</a
                              ></p>   
                            </td>
                          </tr>
                          <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                font-family: sans-serif;
                                text-align: center;
                                padding-left: 20px;
                                font-weight: bold;
                                font-size: 12px;
                                line-height: 18px;
                                color: #929090;
                                padding-right: 20px;
                              "
                            >
                                <a href="https://www.facebook.com/FlyFarInternational/ "
                                ><img
                                  src="https://cdn.flyfarint.com/fb.png"
                                  width="25px"
                                  style="margin: 10px"
                              /></a>
                              <a href="http:// "
                                ><img
                                  src="https://cdn.flyfarint.com/lin.png"
                                  width="25px"
                                  style="margin: 10px"
                              /></a>
                              <a href="http:// "
                                ><img
                                  src="https://cdn.flyfarint.com/wapp.png "
                                  width="25px"
                                  style="margin: 10px"
                              /></a>
                            </td>
                          </tr>
                
                                    <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #929090;
                                font-family: sans-serif;
                                text-align: center;
                                font-weight: 500;
                                font-size: 12px;
                                padding-top:5px;
                                padding-bottom:5px;
                                padding-left:10px;
                                padding-right: 10px;
                              "
                            >
                Ka 11/2A, Bashundhara R/A Road, Jagannathpur, Dhaka 1229           
                 </td>
                          </tr>
                
                
                
                        </table>
                
                
                      </div>
                    </div>
                  </body>
                </html>
                
';
                          
                $mail = new PHPMailer();

                try {
                    $mail->isSMTP();                                    
                    $mail->Host       = 'b2b.flyfarint.com';                     
                    $mail->SMTPAuth   = true;                                  
                    $mail->Username   = 'booking@b2b.flyfarint.com';                    
                    $mail->Password   = '123Next2$';                            
                    $mail->SMTPSecure = 'ssl';            
                    $mail->Port       = 465;                                    

                    //Recipients
                    $mail->setFrom('booking@flyfarint.com', 'Fly Far Int');
                    $mail->addAddress("$agentEmail", "AgentId : $agentId");
                    $mail->addCC('otaoperation@flyfarint.com');
                   
                    $mail->isHTML(true);                                  
                    $mail->Subject = "Booking Request Confirmation - $companyname";
                    $mail->Body    = $AgentMail;


                    if(!$mail->Send()) {
                        echo "Mailer Error: " . $mail->ErrorInfo;
                    }
                                                                       
                }catch (Exception $e) {
                    $response['status']="error";
                    $response['message']="Mail Doesn't Send"; 
                } 


              //Agent maill   
                         
              $OwnerMail = '
              <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Deposit Request 
</title>
  </head>
  <body>
    <div
      class="div"
      style="
        width: 650px;
        height: 70vh;
        margin: 0 auto;
      "
    >
      <div
        style="
          width: 650px;
          height: 200px;
          background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
          border-radius: 20px 0px  20px  0px;

        "
      >
        <table
          border="0"
          cellpadding="0"
          cellspacing="0"
          align="center"
          style="
            border-collapse: collapse;
            border-spacing: 0;
            padding: 0;
            width: 650px;
            border-radius: 10px;

          "
        >
          <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                font-weight: bold;
                font-size: 20px;
                line-height: 38px;
                padding-top: 30px;
                padding-bottom: 10px;
              "
            >
              <a href="https://www.flyfarint.com/"
                ><img
                src="https://cdn.flyfarint.com/logo.png"
                  width="130px"
              /></a>

            </td>
          </tr>
        </table>

        <table
          border="0"
          cellpadding="0"
          cellspacing="0"
          align="center"
          bgcolor="white"
          style="
            border-collapse: collapse;
            border-spacing: 0;
            padding: 0;
            width: 550px;
          "
        >
          <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                font-size: 19px;
                line-height: 38px;
                padding-top: 20px;
                background-color: white;


              "
            >

            Booking Request 

        </td>
          </tr>
          <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 15px;
                font-size: 12px;
                line-height: 18px;
                color: #929090;
                padding-right: 20px;
                background-color: white;

              "
            >
            Dear Flyway International, We send you new booking request at '.$DateTime.'  
         </td>
          </tr>

                    <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 20px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                padding-top: 20px;
                width: 100%;
                background-color: white;
              "
            >
            <span style="color:#003566 ;">  '.$Booked.' </span>
            </td>
          </tr>


    
            <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 20px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                padding-top: 20px;
                width: 100%;
                background-color: white;

              "
            >
              Booking Id:
              <a style="color: #003566; padding-right: 12px" href="http://" target="_blank"
                >'.$Booking_Id.'</a
              >
              System:
              <span style="color: #003566; padding-right: 12px" href="http://" target="_blank"
                >'.$System.'</span
              >
              System PNR:
              <span style="color: #003566" href="http://" target="_blank"
                >'.$Pnr.'</span
              >
            </td>
          </tr>

                              <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 20px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                padding-top: 20px;
                width: 100%;
                background-color: white;

              "
            >
               Destination: <span style="color: #dc143c">'.$From.' to '.$To.' '.$Type.'</span> 
            </td>
          </tr>

                              <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 20px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                padding-top: 10px;
                width: 100%;
                background-color: white;

              "
            >
               Travel Date:  <span style="color: #dc143c">'.$travelDate.'	</span> 
            </td>
          </tr>
                                        <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 20px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                padding-top: 10px;
                width: 100%;
                background-color: white;

              "
            >
               Airline: <span style="color: #dc143c">'.$Airlines.'</span> 
            </td>
          </tr>

                                                  <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 20px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                padding-top: 10px;
                width: 100%;
                background-color: white;

              "
            >
               Pax:  <span style="color: #dc143c">'.$Pax.'</span> 
            </td>
          </tr>

                                                            <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 20px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                padding-top: 10px;
                width: 100%;
                background-color: white;

              "
            >
               Cost:  <span style="color: #dc143c">'.$netCost.'
</span> 
            </td>
          </tr>



             <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 20px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                padding-top: 20px;
                width: 100%;
                background-color: white;

              "
            >
               Sincerely,

            </td>
          </tr>

             <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                width: 100%;
                background-color: white;
                padding-bottom: 20px

              "
            >
              '.$companyname.'

            </td>
          </tr>

      </div>
    </div>
  </body>
</html>
              
';

              $mail1 = new PHPMailer();

              try {
                    $mail1->isSMTP();                                    
                    $mail1->Host       = 'b2b.flyfarint.com';                     
                    $mail1->SMTPAuth   = true;                                  
                    $mail1->Username   = 'booking@b2b.flyfarint.com';                    
                    $mail1->Password   = '123Next2$';                            
                    $mail1->SMTPSecure = 'ssl';            
                    $mail1->Port       = 465;                                    

                    //Recipients
                    $mail1->setFrom('booking@flyfarint.com', 'Fly Far Int');
                     $mail1->addAddress("otaoperation@flyfarint.com", "Booking");
                    
                    
                    $mail1->isHTML(true);                                  
                    $mail1->Subject = "New Booking Request Confirmation by - $companyname";
                    $mail1->Body    = $OwnerMail;


                    if(!$mail1->Send()) {
                         $response['status']="success";
                            $response['BookingId']="$Booking_Id";
                            $response['message']="Booking Successfully";
                            $response['error']="Email Not Send Successfully";
                    } else {
                         $response['status']="success";
                            $response['BookingId']="$Booking_Id";
                            $response['message']="Booking Successfully";
                    }
                                                                         
                }catch (Exception $e) {
                    $response['status']="error";
                    $response['message']="Mail Doesn't Send"; 
                } 
                              
            }
    }

    
    echo json_encode($response);

}