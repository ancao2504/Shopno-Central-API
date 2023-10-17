<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

 date_default_timezone_set('Asia/Dhaka');
 // $Host_Server = '192.241.136.125';

  $servername = "flyfarint.com";
  $username = "flyfarin_erp";
  $password = "@Kayes70455";
  $dbname = "flyfarin_b2bv3";
  
 
  $conn = new mysqli($servername, $username, $password, $dbname);
  
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }else{
      echo '';
  }

  $Date = date("D d M Y");


  $TodayTotalDeposit = $conn->query("SELECT sum(amount) as amount FROM deposit_request where createdAt >= CURRENT_DATE ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC)[0]['amount'];
  $TodayTotalDepositApproved = $conn->query("SELECT sum(amount) as amount from deposit_request where status='approved' AND createdAt >= CURRENT_DATE")->fetch_all(MYSQLI_ASSOC)[0]['amount'];
  $TodaySearch = $conn->query("SELECT * FROM search_history where searchTime>= CURRENT_DATE ORDER BY id DESC")->num_rows;
  $TodayBooking = $conn->query("SELECT * FROM booking where bookedAt>= CURRENT_DATE ORDER BY id DESC")->num_rows;
  $TodayTicketed = $conn->query("SELECT * FROM booking where status='Ticketed' AND bookedAt>= CURRENT_DATE ORDER BY id DESC")->num_rows;
  $TodaySegments = $conn->query("SELECT sum(gds_segment) as count from booking where status='Ticketed' AND bookedAt >= CURRENT_DATE")->fetch_all(MYSQLI_ASSOC)[0]['count'];
  $TodayTicketedAmount = $conn->query("SELECT sum(netCost) as amount from booking where status='Ticketed' AND bookedAt >= CURRENT_DATE")->fetch_all(MYSQLI_ASSOC)[0]['amount'];
  $TodayVendorAmount = $conn->query("SELECT sum(invoice) as amount from booking where status='Ticketed' AND bookedAt >= CURRENT_DATE")->fetch_all(MYSQLI_ASSOC)[0]['amount'];

  $TodayLossProfit = $conn->query("SELECT sum(lossAmount) as amount from booking where status='Ticketed' AND bookedAt >= CURRENT_DATE")->fetch_all(MYSQLI_ASSOC)[0]['amount'];




   $Email ='
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
                font-size: 18px;
                line-height: 38px;
                padding-top: 20px;
                background-color: white;
              "
            >
              Today Sales & Account Report on '.$Date.'
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
              Total Deposit:
              <span style="color: #003566" href="http://" target="_blank"
                >'.$TodayTotalDeposit.' BDT</span
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
              "
            >
              Total Deposit Approved :
              <a style="color: #003566" href="http://" target="_blank"
                >'.$TodayTotalDepositApproved.' BDT</a
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
              Total Search:
              <span style="color: #dc143c">'.$TodaySearch.'</span>
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
              Total Booking:
              <span style="color: #dc143c">'.$TodayBooking.' </span>
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
              Total Ticketed: <span style="color: #dc143c">'.$TodayTicketed.' </span>
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
              Total Segment <span style="color: #dc143c">'.$TodaySegments.'</span>
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
              Total Ticketed Amount <span style="color: #dc143c">'.$TodayTicketedAmount.' BDT </span>
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
              Total Vendor Amount
              <span style="color: #dc143c">'.$TodayVendorAmount.' BDT </span>
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
              Total Loss/Profit: <span style="color: #dc143c">'.$TodayLossProfit.' </span>
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
              Fly Far Tech Team
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
          $mail1->Host       = 'b2b.flyfarint.com';                     
          $mail1->SMTPAuth   = true;                                  
          $mail1->Username   = 'job@b2b.flyfarint.com';                    
          $mail1->Password   = '123Next2$';                            
          $mail1->SMTPSecure = 'ssl';            
          $mail1->Port       = 465;                                    

          //Recipients
          $mail1->setFrom('job@b2b.flyfarint.com', 'Report');
          $mail1->addAddress("otaoperation@flyfarint.com", "Report");
          $mail1->addCC("afridi@flyfarint.com", "Report");
          
          $mail1->isHTML(true);                                  
          $mail1->Subject = "Today Sales & Account Report on $Date";
          $mail1->Body    = $Email;
          if(!$mail1->Send()) {
              echo "Mailer Error: " . $mail1->ErrorInfo;
          }else{
            
          }
                                                                
      }catch (Exception $e) {
          $response['status']="error";
          $response['message']="Mail Doesn't Send"; 
      }

   
    
  

  




?>