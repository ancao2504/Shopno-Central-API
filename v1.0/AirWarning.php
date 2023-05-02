<?php

require 'config.php';

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
                      
            
            $agentMail ='            
            <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Deposit Request</title>
  </head>
  <body>
    <div class="div" style="width: 650px; height: 100vh; margin: 0 auto">
      <div
        style="
          width: 650px;
          height: 200px;
          background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
          border-radius: 20px 0px 20px 0px;
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
                ><img src="https://cdn.flyfarint.com/logo.png" width="130px"
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
              Booking Expired Alert
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
              Dear '.$companyName.', '.$staffName.', Booking Request '.$From.' to
              '.$To.' '.$tripType.' Way on '.$Airlines.' will expired soon. Please
              Issue your ticket by given time limit time restriction.              
            </td>
          </tr>

          <tr>
            <td
              valign="center"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                font-weight: bold;
                font-size: 20px;
                line-height: 18px;
                color: #ffffff;
                padding: 20px;
                width: 5%;
                text-align: center;
              "
            >
            <span style="width: 5%; background-color:#dc143c;padding: 5px 10px; ">0</span>
            <span style="width: 5%; background-color:#dc143c;padding: 5px 10px; ">'.$remainHours.'</span>
            <span style="width: 5%; background-color:#b3cde6;padding: 5px 10px; ">:</span>
            <span style="width: 2%; background-color:#dc143c;padding: 5px 10px; ">0</span>
            <span style="width: 5%; background-color:#dc143c;padding: 5px 10px; ">'.$remainMinutes.'</span>
            <span style="width: 5%; background-color:#b3cde6;padding: 5px 10px; ">:</span>           
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
                >'.$bookingId.'</a
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
                padding-bottom: 20px;
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
              <p>
                <a
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
                >
              </p>
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
                padding-top: 5px;
                padding-bottom: 5px;
                padding-left: 10px;
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
                $mail->Host = 'mail.flyfarint.net';
                $mail->SMTPAuth = true;
                $mail->Username = 'job@b2b.flyfarint.com';
                $mail->Password = '123Next2$';
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                //Recipients
                $mail->setFrom('warning@b2b.flyfarint.com', 'Booking Expire Warning');
                $mail->addAddress("$companyEmail", "$companyEmail");

                $mail->isHTML(true);
                $mail->Subject = "$bookingId Expire Warning";
                $mail->Body = $agentMail;
                if (!$mail->Send()) {
                    echo "Mailer Error: " . $mail->ErrorInfo;
                } else {

                }

            } catch (Exception $e) {
                $response['status'] = "error";
                $response['message'] = "Mail Doesn't Send";
            }

            $ownerMail ='
            <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Deposit Request</title>
  </head>
  <body>
    <div class="div" style="width: 650px; height: 100vh; margin: 0 auto">
      <div
        style="
          width: 650px;
          height: 200px;
          background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
          border-radius: 20px 0px 20px 0px;
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
                ><img src="https://cdn.flyfarint.com/logo.png" width="130px"
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
              Booking Expired Alert
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
              Dear Flyway International, Booking Request '.$From.' to
              '.$To.' '.$tripType.' Way on '.$Airlines.' will be expired soon.
            </td>
          </tr>

          <tr>
            <td
              valign="center"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                font-weight: bold;
                font-size: 20px;
                line-height: 18px;
                color: #ffffff;
                padding: 20px;
                width: 5%;
                text-align: center;
              "
            >
            <span style="width: 5%; background-color:#dc143c;padding: 5px 10px; ">0</span>
            <span style="width: 5%; background-color:#dc143c;padding: 5px 10px; ">'.$remainHours.'</span>
            <span style="width: 5%; background-color:#b3cde6;padding: 5px 10px; ">:</span>
            <span style="width: 2%; background-color:#dc143c;padding: 5px 10px; ">0</span>
            <span style="width: 5%; background-color:#dc143c;padding: 5px 10px; ">'.$remainMinutes.'</span>
            <span style="width: 5%; background-color:#b3cde6;padding: 5px 10px; ">:</span>
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
                >'.$bookingId.'</a
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
                padding-bottom: 20px;
              "
            >
              Flyway International
            </td>
          </tr>

        </table>
      </div>
    </div>
  </body>
</html>
';

            $mail1 = new PHPMailer();
            try {
                $mail1->isSMTP();
                $mail1->Host = 'mail.flyfarint.net';
                $mail1->SMTPAuth = true;
                $mail1->Username = 'job@b2b.flyfarint.com';
                $mail1->Password = '123Next2$';
                $mail1->SMTPSecure = 'ssl';
                $mail1->Port = 465;

                //Recipients
                $mail1->setFrom('warning@b2b.flyfarint.com', 'Booking Expire Warning');
                $mail1->addAddress("otaoperation@flyfarint.com", "Bot");


                $mail1->isHTML(true);
                $mail1->Subject = "$bookingId Expire Warning";
                $mail1->Body = $ownerMail;
                if (!$mail1->Send()) {
                    echo "Mailer Error: " . $mail->ErrorInfo;
                } else {

                }

            } catch (Exception $e) {
                $response['status'] = "error";
                $response['message'] = "Mail Doesn't Send";
            }
        }     
    }
  }else{
    
  }

?>