<?php

require '../../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require("../../../vendor/autoload.php");

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
    $userId = $_POST["userId"];
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
        $userId = $rowTravelDate['userId'];
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

///data for mail
    if(isset($agentId)){
        $sql1 = mysqli_query($conn,"SELECT * FROM agent WHERE agentId='$agentId'");
        $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);

        if(!empty($row1)){
            $agentEmail = $row1['email'];
            $companyname = $row1['company'];							
        } 
        
    }

    $voidtextBy = '';


    $sql = "INSERT INTO `void`(`voidId`, `agentId`,`userId`, `bookingId`,`ticketId`,`passengerDetails`,`status`,`requestedBy`,`requestedAt`)
             VALUES ('$voidId','$agentId','$userId','$bookingId','$TicketId','$dataPax','pending','$requestedBy','$createdTime')";
   
  if ($conn->query($sql) === TRUE) {
    $conn->query("UPDATE `booking` SET `status`='Void In Processing',`voidId`='$voidId',`lastUpdated`='$createdTime' where bookingId='$bookingId'");
    $conn->query("INSERT INTO `activityLog`(`ref`,`agentId`,`status`,`remarks`,`actionRef`,`actionBy`,`actionAt`)
            VALUES ('$bookingId','$agentId','Void In Processing','$userId Void request from WLB2C','$voidId','$requestedBy','$createdTime')");

        //Information for Email Template
        $data = $conn->query("SELECT `email`, `company_name`,`websitelink`, `address`,`phone`,`fb_link`,`linkedin_link`, `whatsapp_num` FROM `B2C_wl_content` WHERE agentId='$agentId'")->fetch_all(MYSQLI_ASSOC);
        $agentEmail = $data[0]['email'];
        $agentPhone = $data[0]['phone'];
        $agentAddress = $data[0]['address'];
        $agentCompany_name = $data[0]['company_name'];
        $agentWebsiteLink = $data[0]['websitelink'];
        $agentFbLink = $data[0]['fb_link'];
        $agentLinkedInLink = $data[0]['linkedin_link'];
        $agentWhatsappNum = $data[0]['whatsapp_num'];

        $userData = $conn->query("SELECT `name`,`email` FROM `subagent` WHERE agentId = '$agentId' AND userId = '$userId'")->fetch_all(MYSQLI_ASSOC);
        $userName = $userData[0]['name'];
        $userEmail = $userData[0]['email'];

        $AgentEmail = '
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
                      <div style="width: 650px; height: 150px; background: #32d095">
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
                                padding-top: 20px;
                                padding-bottom: 10px;
                              "
                            >
                              <a
                                style="
                                  text-decoration: none;
                                  color: #ffffff;
                                  font-family: sans-serif;
                                  font-size: 25px;
                                  padding-top: 20px;
                                "
                                href="https://www.' . $agentWebsiteLink . '/"
                              >
                                ' . $agentCompany_name . '</a
                              >
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
                                padding-top: 10px;
                                background-color: white;
                              "
                            >
                            New Booking Void Request
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
                            Dear ' . $userName . ', Your Booking Void Request has been placed please wait for a while.
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
                                color: #525371;
                                padding-right: 20px;
                                background-color: white;
                              "
                            >
                              Booking ID: <span>' . $bookingId . '</span>
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
                             ' . $agentCompany_name . '
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
                                background-color: #525371;
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
                                background-color: #525371;
                              "
                            >
                              Mail us at
                              <a
                                style="color: white; font-size: 13px; text-decoration: none"
                                href="http://"
                                target="_blank"
                                >' . $agentEmail . '
                              </a>
                              agency or Call us at ' . $agentPhone . '
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
                                  href="https://www.' . $agentWebsiteLink . '/termsandcondition"
                                  >Tearms & Conditions</a
                                >
                                <a
                                  style="
                                    font-weight: bold;
                                    font-size: 12px;
                                    line-height: 15px;
                                    color: #222222;
                                    padding-left: 10px;
                                  "
                                  href="https://www.' . $agentWebsiteLink . '/privacypolicy"
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
                              <a href="' . $agentFbLink . ' "
                                ><img
                                  src="https://cdn.flyfarint.com/fb.png"
                                  width="25px"
                                  style="margin: 10px"
                              /></a>
                              <a href="' . $agentLinkedInLink . ' "
                                ><img
                                  src="https://cdn.flyfarint.com/lin.png"
                                  width="25px"
                                  style="margin: 10px"
                              /></a>
                              <a href="' . $agentWhatsappNum . ' "
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
                              ' . $agentAddress . '
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
            $mail->Host = 'b2b.flyfarint.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'voidwl@mailservice.center';
            $mail->Password = '123Next2$';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            //Recipients
            $mail->setFrom("voidwl@mailservice.center", $agentCompany_name);
            $mail->addAddress("$userEmail", "AgentId : $AgentId");
            $mail->addCC('habib@flyfarint.com');
            $mail->addCC('afridi@flyfarint.com');

            $mail->isHTML(true);
            $mail->Subject = "New Booking Void Request by $userName";
            $mail->Body = $AgentEmail;

            //print_r($mail);
            if (!$mail->Send()) {
                echo "Mailer Error: " . $mail->ErrorInfo;
            }

        } catch (Exception $e) {

        }



$agentMailFormOwner = '
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
            padding-top: 15px;
            background-color: white;



          "
        >
        Ticket Void Request Confirmation
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
            padding-top: 10px;
            font-size: 12px;
            line-height: 18px;
            color: #929090;
            padding-right: 20px;
            background-color: white;

          "
        >
        Dear '.$agentCompany_name.', You has been Request for Void a Ticket. Thank you for stay connected with Fly Far International.
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
          <a style="color: #2564B8" href="http://" target="_blank"
            >' . $bookingId . '</a
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
              Refund Id:
              <a style="color: #003566" href="http://" target="_blank"
                >' . $refundId . '</a
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
               Destination: <span style="color: #dc143c">' . $deptFrom . '-' . $arriveTo . ', ' . $Type . '</span>
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
               Travel Date:  <span style="color: #dc143c">' . $travelDate . '	</span>
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
               Airline: <span style="color: #dc143c">' . $Airlines . '	</span>
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
               Pax:  <span style="color: #dc143c">' . $pax . '</span>
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
               Cost:  <span style="color: #dc143c">' . $TicketCost . '
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
             Fly Far International

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
            $mail->Host = 'b2b.flyfarint.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'void@b2b.flyfarint.com';
            $mail->Password = '123Next2$';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            //Recipients
            $mail->setFrom('void@flyfarint.com', 'Fly Far International');
            $mail->addAddress("$agentEmail", "AgentId : $agentId");
            $mail3->addCC('habib@flyfarint.com');
            $mail3->addCC('afridi@flyfarint.com');

            $mail->isHTML(true);
            $mail->Subject = "Ticket Void Request Confirmation by Fly Far International";
            $mail->Body = $agentMailFormOwner;
            if (!$mail->Send()) {
                echo "Mailer Error: " . $mail->ErrorInfo;
            } else {

            }

        } catch (Exception $e) {
            $response['status'] = "error";
            $response['message'] = "Mail Doesn't Send";
        }

        $OwnerMailFormAgent = '
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
      Ticket Void
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
                  Dear Fly Far International, We are Requested for Void a Ticket.
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

                    <span style="color: #003566" href="http://" target="_blank"
                      >' . $voidtextBy . '</span>

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
                Void Id:
                <a style="color: #003566; padding-right: 12px" href="http://" target="_blank"
                  >' . $voidId . '</a
                >
                System:
                <span style="color: #003566; padding-right: 12px" href="http://" target="_blank"
                  >' . $gds . '</span
                >
                System PNR:
                <span style="color: #003566" href="http://" target="_blank"
                  >' . $pnr . '</span
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
                     Destination: <span style="color: #dc143c">' . $deptFrom . '-' . $arriveTo . ', ' . $Type . '</span>
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
                     Travel Date:  <span style="color: #dc143c">' . $travelDate . '</span>
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
                     Airline: <span style="color: #dc143c">' . $Airlines . '	</span>
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
                     Pax:  <span style="color: #dc143c">' . $pax . '</span>
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
                     Cost:  <span style="color: #dc143c">' . $TicketCost . '
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
                  ' . $companyname . '
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
            $mail1->Host = 'b2b.flyfarint.com';
            $mail1->SMTPAuth = true;
            $mail1->Username = 'void@b2b.flyfarint.com';
            $mail1->Password = '123Next2$';
            $mail1->SMTPSecure = 'ssl';
            $mail1->Port = 465;

            //Recipients
            $mail1->setFrom('void@flyfarint.com', $agentCompany_name);
            $mail1->addAddress("otaoperation@flyfarint.com");
             $mail->addCC('habib@flyfarint.com');
             $mail->addCC('afridi@flyfarint.com');

            $mail1->isHTML(true);
            $mail1->Subject = "New Ticket Void Request by $agentCompany_name";
            $mail1->Body = $OwnerMailFormAgent;

            if (!$mail1->Send()) {
                $response['status'] = "success";
                $response['VoidId'] = "$voidId";
                $response['message'] = "Ticketing Void Request Successfully";
                $response['error'] = "Ticketing Void Request Successfully";
            } else {
                $response['status'] = "success";
                $response['VoidId'] = "$voidId";
                $response['message'] = "Ticket Void Request Successfully";
            }

        } catch (Exception $e1) {
            $response['status'] = "error";
            $response['message'] = "Mail Doesn't Send";
        }

      echo json_encode($response);
    
    }

  }
  