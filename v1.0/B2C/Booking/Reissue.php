<?php

require '../../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require "../../../vendor/autoload.php";

if (array_key_exists('request', $_GET)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $_POST = json_decode(file_get_contents('php://input'), true);

        $reissueId = "";
        $sql1 = "SELECT * FROM reissue ORDER BY reissueId DESC LIMIT 1";
        $result = $conn->query($sql1);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["reissueId"]);
                $number = (int) $outputString + 1;
                $reissueId = "FFRI$number";
            }
        } else {
            $reissueId = "FFRI1000";
        }

        $paxDetails = $_POST['passengerData'];

        $agentId = $_POST["agentId"];
        $userId = $_POST["userId"];
        $bookingId = $_POST["bookingId"];
        $staffId = $_POST["staffId"];
        $Pax = count($_POST['passengerData']);
        $passengerData = $_POST["passengerData"];
        $requestedBy = $_POST["requestedBy"];
        $reissuedate = $_POST["date"];

        $PaxData = array();
        $passData = array();
        foreach ($paxDetails as $paxDet) {
            $name = $paxDet['name'];
            $ticket = $paxDet['ticket'];
            //$gender = $paxDet['gender'];
            //$passenType = $paxDet['type'];

            $passengerDataHTML = '
    <tr>
      <td
        style="
          padding-left: 20px;
          padding-bottom: 5px;
          vertical-align: top;
        "
      >
      ' . $name . '
      </td>
      <td
        style="
          padding-left: 20px;
          padding-bottom: 5px;
          vertical-align: top;
        "
      >
        ' . $ticket . '
      </td>
    </tr>';

            $data = "($name-$ticket)";
            array_push($PaxData, $data);
            array_push($passData, $passengerDataHTML);
        }

        $dataPax = implode('', $PaxData);
        $passengerDataTable = implode('', $passData);

        $createdTime = date('Y-m-d H:i:s');

        $DateTime = date("D d M Y h:i A");

        if (isset($bookingId)) {
 
          $rowTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);
          
          if (!empty($rowTravelDate)) {
              $travelDate = $rowTravelDate[0]['travelDate'];
              $pax = $rowTravelDate[0]['pax'];
              $Type = $rowTravelDate[0]['tripType'];
              $Airlines = $rowTravelDate[0]['airlines'];
              $gds = $rowTravelDate[0]['gds'];
              $pnr = $rowTravelDate[0]['pnr'];
              $TicketId = $rowTravelDate[0]['ticketId'];
              $TicketCost = $rowTravelDate[0]['netCost'];
              $arriveTo = $rowTravelDate[0]['arriveTo'];
              $deptFrom = $rowTravelDate[0]['deptFrom'];
              $tripType = $rowTravelDate[0]['tripType'];
          }
      }



        if (isset($agentId)) {
            $sql1 = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
            $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

            if (!empty($row1)) {
                $agentEmail = $row1['email'];
                $companyname = $row1['company'];
            }
        }

        $sql = "INSERT INTO `reissue`(`reissueId`, `agentId`, `userId`,`quotation`, `bookingId`, `ticketId`,`passengerDetails`,`reissueDate`,`status`, `requestedBy`, `requestedAt`)
             VALUES ('$reissueId','$agentId','$userId','xyz', '$bookingId','$TicketId','$dataPax','$reissuedate','pending','$requestedBy','$createdTime')";

        if ($conn->query($sql) === true) {
            $conn->query("UPDATE `booking` SET `status`='Reissue In Processing',`reissueId`='$reissueId',`lastUpdated`='$createdTime' where bookingId='$bookingId'");
            $conn->query("INSERT INTO `activityLog`(`ref`,`agentId`,`status`,`remarks`,`actionRef`,`actionBy`,`actionAt`)
            VALUES ('$bookingId','$agentId','Reissue In Processing','$userId Send Reissue Request','$reissueId','$requestedBy','$createdTime')");

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
                                New Booking Reissue Request
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
                                Dear ' . $userName . ', Your Booking Reissue Request has been placed please wait for a while.
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
                $mail->Username = 'refundwl@mailservice.center';
                $mail->Password = '123Next2$';
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                //Recipients
                $mail->setFrom("refundwl@mailservice.center", $agentCompany_name);
                $mail->addAddress("$userEmail", "AgentId : $AgentId");
                $mail->addCC('habib@flyfarint.com');
                $mail->addCC('afridi@flyfarint.com');

                $mail->isHTML(true);
                $mail->Subject = "New Booking Reissue Request by $userName";
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
                      Ticket Reissue
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
                      Dear ' . $companyname . ', You has been Request for Reissue a Ticket. Thank
                      you for stay connected with Fly Far International.
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
                      Reissue Request By:
                      <span style="color: #003566" href="http://" target="_blank"
                        >' . $requestedBy . '</span
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
                      Booking Id:
                      <a style="color: #003566" href="http://" target="_blank"
                        >' . $bookingId . '</a
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
                    font-size: 13px;
                    line-height: 18px;
                    color: #929090;
                    border-collapse: collapse;
                    border-spacing: 0;
                    width: 550px;
                    font-family: sans-serif;
                    font-weight: bold;
                  "
                ><tr>
                <th
                style="
                  color: #dc143c;
                  text-align: left;
                  padding-left: 20px;
                  vertical-align: top;
                  padding-bottom: 5px;
                  padding-top: 20px;
                "
              >
                Passenger Name
              </th>
              <th
                style="
                  color: #dc143c;
                  text-align: left;
                  padding-left: 20px;
                  vertical-align: top;
                  padding-bottom: 5px;
                  padding-top: 20px;
                "
              >
                e-Ticket No
              </th>
            </tr>
                  ' . $passengerDataTable . '
                </table>

                <table
                  border="0"
                  cellpadding="0"
                  cellspacing="0"
                  align="center"
                  bgcolor="white"
                  style="
                    font-size: 13px;
                    line-height: 18px;
                    color: #929090;
                    border-collapse: collapse;
                    border-spacing: 0;
                    width: 550px;
                    font-family: sans-serif;
                    font-weight: bold;
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
                        padding-top: 20px;
                        font-size: 13px;
                        line-height: 18px;
                        color: #929090;
                        padding-top: 20px;
                        width: 100%;
                        background-color: white;
                      "
                    >
                      Destination:
                      <span style="color: #dc143c">' . $deptFrom . '-' . $arriveTo . ', ' . $Type . '</span>
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
                      Reissue Date:
                      <span style="color: #dc143c">' . $reissuedate . '</span>
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
                      Airline: <span style="color: #dc143c">' . $Airlines . '</span>
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
                      Pax: <span style="color: #dc143c">' . $pax . '</span>
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

                    $mail1 = new PHPMailer();

                    try {
                        $mail1->isSMTP();
                        $mail1->Host = 'b2b.flyfarint.com';
                        $mail1->SMTPAuth = true;
                        $mail1->Username = 'reissue@b2b.flyfarint.com';
                        $mail1->Password = '123Next2$';
                        $mail1->SMTPSecure = 'ssl';
                        $mail1->Port = 465;

                        //Recipients
                        $mail1->setFrom('reissue@flyfarint.com', 'Fly Far International');
                        $mail1->addAddress("$agentEmail", "AgentId : $agentId");
                        $mail1->addCC('otaoperation@flyfarint.com');
                        $mail1->addCC('habib@flyfarint.com');

                        $mail1->isHTML(true);
                        $mail1->Subject = "Ticket Reissue Request Confirmation - $companyname";
                        $mail1->Body = $agentMailFormOwner;
                        if (!$mail1->Send()) {
                            echo "Mailer Error: " . $mail1->ErrorInfo;
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
                Ticket Reissue
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
                            Dear Fly Far International, We are Requested for Reissue a Ticket.
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
                              Reissue Request From:
                              <span style="color: #003566" href="http://" target="_blank"
                                > WhiteLabel B2C </span>

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
                                >' . $bookingId . '</a
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
                          font-size: 13px;
                          line-height: 18px;
                          color: #929090;
                          border-collapse: collapse;
                          border-spacing: 0;
                          width: 550px;
                          font-family: sans-serif;
                          font-weight: bold;
                        "
                      ><tr>
                      <th
                      style="
                        color: #dc143c;
                        text-align: left;
                        padding-left: 20px;
                        vertical-align: top;
                        padding-bottom: 5px;
                        padding-top: 20px;
                      "
                    >
                      Passenger Name
                    </th>
                    <th
                      style="
                        color: #dc143c;
                        text-align: left;
                        padding-left: 20px;
                        vertical-align: top;
                        padding-bottom: 5px;
                        padding-top: 20px;
                      "
                    >
                      e-Ticket No
                    </th>
                  </tr>
                        ' . $passengerDataTable . '
                      </table>

                        <table
                        border="0"
                        cellpadding="0"
                        cellspacing="0"
                        align="center"
                        bgcolor="white"
                        style="
                          font-size: 13px;
                          line-height: 18px;
                          color: #929090;
                          border-collapse: collapse;
                          border-spacing: 0;
                          width: 550px;
                          font-family: sans-serif;
                          font-weight: bold;
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
                              Reissue Date:  <span style="color: #dc143c">' . $reissuedate . '</span>
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
                              Airline: <span style="color: #dc143c">' . $Airlines . '</span>
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
                              '.$agentCompany_name.'

                            </td>
                          </tr>
                        </table>


                      </div>
                    </div>
                  </body>
                </html>
                ';

            $mail2 = new PHPMailer();

            try {
                $mail2->isSMTP();
                $mail2->Host = 'flyfarint.com';
                $mail2->SMTPAuth = true;
                $mail2->Username = 'reissue@b2b.flyfarint.com';
                $mail2->Password = '123Next2$';
                $mail2->SMTPSecure = 'ssl';
                $mail2->Port = 465;

                //Recipients
                $mail2->setFrom('reissue@flyfarint.com', $agentCompany_name);
                $mail2->addAddress("otaoperation@flyfarint.com", "Reissue Ticket Request");
                $mail2->addCC('habib@flyfarint.com');

                $mail2->isHTML(true);
                $mail2->Subject = "New Ticket Reissue Request by - $companyname";
                $mail2->Body = $OwnerMailFormAgent;

                if (!$mail2->Send()) {
                    $response['status'] = "success";
                    $response['InvoiceId'] = "$reissueId";
                    $response['message'] = "Ticket Reissue Request Successfully";
                    $response['error'] = "Ticket Reissue Request Successfully";
                } else {
                    $response['status'] = "success";
                    $response['InvoiceId'] = "$reissueId";
                    $response['message'] = "Ticket Reissue Request Successfully";
                }

            } catch (Exception $e1) {
                $response['status'] = "error";
                $response['message'] = "Mail Doesn't Send";
            }

            echo json_encode($response);

        }

    }
} else if (array_key_exists('quotationconfirm', $_GET)) {
    $_POST = json_decode(file_get_contents('php://input'), true);
    $agentId = $_POST['agentId'];
    $bookingId = $_POST['bookingId'];
    $createdTime = date("Y-m-d H:i:s");
    $checker = $conn->query("SELECT * FROM reissue WHERE bookingId='$bookingId' AND agentId='$agentId'")->fetch_all(MYSQLI_ASSOC);
    if (!empty($checker)) {
        $status = $checker[0]['status'];
        if ($status == 'quotation confirm') {
            $response['status'] = 'error';
            $response['message'] = 'Already Quotation Confirm';
        } else {
            $sql = "UPDATE `reissue` SET `status`='quotation confirm',`actionBy`='$agentId',`actionAt`='$createdTime' WHERE bookingId='$bookingId' AND agentId='$agentId'";
            if ($conn->query($sql) == true) {
                $response['status'] = "success";
                $response['message'] = 'Quotation Confirm';
            } else {
                $response['status'] = "error";
                $response['message'] = 'Query Failed';
            }
        }
    } else {
        $response['status'] = "error";
        $response['message'] = 'Invalid Id';
    }
    echo json_encode($response);

} else if (array_key_exists('quotationrejected', $_GET)) {
    $_POST = json_decode(file_get_contents('php://input'), true);
    $agentId = $_POST['agentId'];
    $bookingId = $_POST['bookingId'];
    $createdTime = date("Y-m-d H:i:s");
    $checker = $conn->query("SELECT * FROM reissue WHERE bookingId='$bookingId' AND agentId='$agentId'")->fetch_all(MYSQLI_ASSOC);
    if (!empty($checker)) {
        $status = $checker[0]['status'];
        if ($status == 'quotation rejected') {
            $response['status'] = 'error';
            $response['message'] = 'Already Quotation Rejected';
        } else {
            $sql = "UPDATE `reissue` SET `status`='quotation rejected',`actionBy`='$agentId',`actionAt`='$createdTime' WHERE bookingId='$bookingId' AND agentId='$agentId'";
            if ($conn->query($sql) == true) {
                $response['status'] = "success";
                $response['message'] = 'Quotation Rejected Successfully';
            } else {
                $response['status'] = "error";
                $response['message'] = 'Query Failed';
            }
        }
    } else {
        $response['status'] = "error";
        $response['message'] = 'Invalid Id';
    }
    echo json_encode($response);

}
