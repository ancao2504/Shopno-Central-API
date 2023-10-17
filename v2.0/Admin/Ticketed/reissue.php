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

include_once '../../authorization.php';
if (authorization($conn) == true){  
  
  if (array_key_exists("bookingId", $_GET)) {
      $reissueId = $_GET["bookingId"];

    $sql = "SELECT * FROM `reissue` where bookingId='$reissueId'";
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
  }else if(array_key_exists('quotationsend', $_GET)){
    if($_SERVER["REQUEST_METHOD"] == "POST"){

      $_POST = json_decode(file_get_contents('php://input'), true);

          $bookingId = $_POST['bookingId'];
          $agentId = $_POST['agentId'];
          $actionBy = $_POST['actionBy'];
          $Quotation = $_POST['quotation'];
          $createdTime = date("Y-m-d H:i:s");

          if(isset($bookingId)){
              $checker = $conn->query("SELECT * FROM reissue WHERE bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);
              if(!empty($checker)){
                $status = $checker[0]['status'];
                if($status == 'quotation send'){
                  $response['status'] = 'error';
                  $response['message'] = "Already Quotation Sending";
                }else{
                  $sql="UPDATE `reissue` SET `status`='quotation send',`actionBy`='$actionBy',`actionAt`='$createdTime', `quotation`='$Quotation' WHERE bookingId='$bookingId' AND agentId='$agentId'";
                  if($conn->query($sql) == TRUE){
                    $response['status'] = "success";
                    $response['message'] = 'Quotation Sending Successfully';
                  }else{
                    $response['status'] = "error";
                    $response['message'] = 'Query Failed';
                  }
                  if($status == 'quotation confirm'){
                    $response['status'] = 'error';
                    $response['message'] = "Already Quotation Confirm";
                  }else if($status == 'quotation rejected'){
                    $response['status'] = 'error';
                    $response['message'] = "Already Quotation Rejected";
                  }
              }
              }
          }
          echo json_encode($response);

    }
  }else if(array_key_exists("approved", $_GET)){
      if ($_SERVER["REQUEST_METHOD"] == "POST"){
              
          $_POST = json_decode(file_get_contents('php://input'), true);
          
          $reissueId = $_POST['issueId'];
          $bookingId = $_POST['bookingId'];
          $agentId = $_POST['agentId'];
          $reissuecharge = $_POST['charge'];
          $actionBy = $_POST['actionBy'];
          $Quotation = $_POST['quotation'];


          $createdTime = date("Y-m-d H:i:s");

          $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
          $data = mysqli_fetch_assoc($query);
          $companyname = $data['company'];

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
                  $arriveTo = $rowTravelDate['arriveTo'];
                  $deptFrom = $rowTravelDate['deptFrom'];
                  $tripType = $rowTravelDate['tripType'];
                  $status = $rowTravelDate['status'];                                          
              } 
          }
          
        // if($status == 'Reissue In Processing'){}
          ///data for mail

          $result = $conn->query("SELECT * FROM subagent where subagentId='$subagentId' AND agentId= '$agentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
          $agentId = $result[0]['agentId'];
          $companyName = $result[0]['company'];
          $Password = $result[0]['password'];
          $Email = $result[0]['email'];

          $result1 = $conn->query("SELECT * FROM wl_content where agentId='$agentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
          $agentCompanyName = $result1[0]['company_name'];
          $agentCompanyLogo = $result1[0]['companyImage'];
          $agentCompanyEmail = $result1[0]['email'];
          $agentCompanyPhone = $result1[0]['phone'];
          $agentCompanyAddress = $result1[0]['address'];
          $agentCompanyWebsiteLink = $result1[0]['websitelink'];
          $agentCompanyFbLink = $result1[0]['fb_link'];
          $agentCompanyLinkedinLink = $result1[0]['linkedin_link'];
          $agentCompanyWhatsappNum = $result1[0]['whatsapp_num'];

          if(isset($agentId)){
              $sql1 = mysqli_query($conn,"SELECT * FROM agent WHERE agentId='$agentId'");
              $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);
      
              if(!empty($row1)){
                  $agentEmail = $row1['email'];
                  $companyname = $row1['company'];							
              } 
              
          }
      

          if(isset($reissueId)){
              $sqlvoid = mysqli_query($conn, "SELECT * FROM reissue WHERE bookingId='$bookingId'");
              $rowsqlvoid = mysqli_fetch_array($sqlvoid,MYSQLI_ASSOC);
              
              if(!empty($rowsqlvoid)){
                  $reissuerequestedBy = $rowsqlvoid['requestedBy'];
                  $reissuerequestedAt = $rowsqlvoid['requestedAt'];
                  
                  $passengerDetails = $rowsqlvoid['passengerDetails'];
                  $name = preg_replace('/[^A-Z]/', ' ', $passengerDetails);
                  $ticketno = preg_replace('/[^0-9]/', '', $passengerDetails);
                                                          
              } 
          }


          $checkBalanced = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' ORDER BY id DESC LIMIT 1");
          $rowcheckBalanced = mysqli_fetch_array($checkBalanced,MYSQLI_ASSOC);        
          if(!empty($rowcheckBalanced)){
              $lastAmount = $rowcheckBalanced['lastAmount'];							
          }

          $checker =$conn->query("SELECT * FROM reissue WHERE bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);
          $status = $checker[0]['status'];
          if($status == 'approved'){
              $response['status'] = 'error';
              $response['message'] = "Booking Reissue Already Approved";
          }else{
            $newBalance = $lastAmount - ($reissuecharge);

            $sql="UPDATE `reissue` SET `status`='approved',`charge`='$reissuecharge',`actionBy`='$actionBy',`actionAt`='$createdTime', `quotation`='$Quotation' WHERE bookingId='$bookingId' AND agentId='$agentId'";

            if($conn->query($sql) === TRUE){  
              $details= "Reissue $TicketId Ticket Invoice $Type Air Ticket $deptFrom - $arriveTo with carrier $Airlines was Requested By $reissuerequestedBy";
            
              $conn->query("INSERT INTO `agent_ledger`(`agentId`,`reissue`, `lastAmount`, `transactionId`, `details`, `reference`, `actionBy`, `createdAt`)
              VALUES ('$agentId','$reissuecharge','$newBalance','$bookingId','$details','$reissueId','$actionBy','$createdTime')");
              
              $conn->query("UPDATE `booking` SET `status`='Reissued',`lastUpdated`='$createdTime' where bookingId='$bookingId'");
              $conn->query("INSERT INTO `activityLog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                  VALUES ('$bookingId','$agentId','Reissued',' ','$actionBy','$createdTime')");
    
                  
              if(!empty($subagentId)){
    // agent Reissue message approval......................................................
                $AgentMail = '
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
                        height: 150px;
                        background: #FFA84D;
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
                          padding-top: 20px;
                          padding-bottom: 10px;
              
                        "
                      >
                      <a style="text-decoration: none; color:#2564B8; font-family: sans-serif; font-size: 25px; padding-top: 20px;" href="https://www.' . $agentCompanyWebsiteLink . '">' . $agentCompanyName . '</a>
                  
              
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
                            New Booking Reissue Request Accept
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
                            Dear '.$companyName. ', Your Booking Void Request has been Accepted.
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
                              padding-top: 5px;
                              font-size: 12px;
                              line-height: 18px;
                              color: #2564B8;
                              padding-right: 20px;
                              background-color: white;
                            "
                          >
                          Booking ID: <span> ' . $bookingId.'</span>
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
                              font-size: 12px;
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
                              font-size: 12px;
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
                              font-size: 12px;
                              line-height: 18px;
                              color: #929090;
                              width: 100%;
                              background-color: white;
                              padding-bottom: 20px;
                            "
                          >
                          '.$agentCompanyName.'
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
                              background-color: #2564B8;
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
                              background-color: #2564B8;
                            "
                          >
                            Mail us at
                            <a
                                              style="color: white; font-size: 13px; text-decoration: none"
                                              href="http://"
                                              target="_blank"
                                              > ' . $agentCompanyEmail . '
                                            </a>
                                            agency or Call us at ' . $agentCompanyPhone . '
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
                                                href="https://www.' . $agentCompanyWebsiteLink . '/termsandcondition"
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
                                                href="https://www.' . $agentCompanyWebsiteLink . '/privacypolicy"
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
                                            <a href="' . $agentCompanyFbLink . ' "
                                              ><img
                                                src="https://cdn.flyfarint.com/fb.png"
                                                width="25px"
                                                style="margin: 10px"
                                            /></a>
                                            <a href="' . $agentCompanyLinkedinLink . ' "
                                              ><img
                                                src="https://cdn.flyfarint.com/lin.png"
                                                width="25px"
                                                style="margin: 10px"
                                            /></a>
                                            <a href="' . $agentCompanyWhatsappNum . ' "
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
                                            ' . $agentCompanyAddress . '
                                          </td>
                              </tr>
                      </table>
                    </div>
                  </div>
                </body>
              </html>
    ';
                
                $mail3 = new PHPMailer();
    
                          try {
                              $mail3->isSMTP();
                              $mail3->Host       = 'b2b.flyfarint.com';
                              $mail3->SMTPAuth   = true;
                              $mail3->Username   = 'reissuewl@mailservice.center';
                              $mail3->Password   = '123Next2$';
                              $mail3->SMTPSecure = 'ssl';
                              $mail3->Port       = 465;
    
                              //Recipients
                              $mail3->setFrom('reissuewl@mailservice.center', $agentCompanyName);
                              $mail3->addAddress("$Email", "SubAgentId : $subagentId");
                              // $mail3->addCC('habib@flyfarint.com');
                              // $mail3->addCC('afridi@flyfarint.com');
                              
    
                              $mail3->isHTML(true);
                              $mail3->Subject = "Ticket Reissue Request Confirmation - $companyname";
                              $mail3->Body    = $AgentMail;
                              if (!$mail3->Send()) {
                                  echo "Mailer Error: " . $mail3->ErrorInfo;
                              } else {
                              }
                          } catch (Exception $e) {
                              $response['status']="error";
                              $response['message']="Mail Doesn't Send";
                          }
    
                
                          $SubAgentEmail ='
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
                                    height: 150px;
                                    background:#FFA84D;
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
                                      padding-top: 20px;
                                      padding-bottom: 10px;
              
                                    "
                                  >
                                    <a style="text-decoration: none; color:#2564B8; font-family: sans-serif; font-size: 25px; padding-top: 20px;" href="https://www.' . $agentCompanyWebsiteLink . '">' . $agentCompanyName . '</a>
              
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
                                      Dear ' . $agentCompanyName . ', Our Booking Reissue Request has been Accepted.
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
                                        color: #2564B8;
                                        padding-right: 20px;
                                        background-color: white;
                                      "
                                    >
                                      Booking ID: <span>' . $bookingId . '</span>
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
                                          font-size: 12px;
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
                                          font-size: 12px;
                                          line-height: 18px;
                                          color: #929090;
                                          width: 100%;
                                          background-color: white;
                                          padding-bottom: 20px
              
                                        "
                                      >
                                        ' . $companyName . '
              
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
                              $mail->Username   = 'ticketing@b2b.flyfarint.com';
                              $mail->Password   = '123Next2$';
                              $mail->SMTPSecure = 'ssl';
                              $mail->Port       = 465;
              
                              //Recipients
                              $mail->setFrom('ticketing@flyfarint.com', $agentCompanyName);
                              $mail->addAddress("$agentCompanyEmail", "AgentId : $agentId");
                              //$mail->addCC('otaoperation@flyfarint.com');
                              // $mail->addCC('habib@flyfarint.com');
                              // $mail->addCC('afridi@flyfarint.com');
              
                              $mail->isHTML(true);
                              $mail->Subject = "Booking Reissue Request Accept by $agentCompanyName";
                              $mail->Body    = $SubAgentEmail;
                              if (!$mail->Send()) {
                                  echo "Mailer Error: " . $mail->ErrorInfo;
                              } else {
                              }
                          } catch (Exception $e) {
                              $response['status']="error";
                              $response['message']="Mail Doesn't Send";
                          }
    
                        if(!$mail->Send()) {
                            $response['status']="success";
                            $response['message']="Booking Reissue Request Approved";
                            
                    } else {
                            $response['status']="success";
                            $response['message']="Booking Reissue Request  ";
                    }
                
              }
              
          }

      }
    
        echo json_encode($response);
      
    }
    
      

  }else if(array_key_exists("reject", $_GET)){
  
  
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $_POST = json_decode(file_get_contents('php://input'), true);
    
        $bookingId = $_POST['bookingId'];
        $actionBy = $_POST['actionBy'];
        $remarks = $_POST['remarks'];
    
        $createdTime = date("Y-m-d H:i:s"); 
        $DateTime = date("D d M Y h:i A");
    
        if(isset($bookingId)){
            $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
            $rowTravelDate = mysqli_fetch_array($sqlTravelDate,MYSQLI_ASSOC);
            
            if(!empty($rowTravelDate)){
              $travelDate = $rowTravelDate['travelDate'];
              $agentId = $rowTravelDate['agentId'];
              $staffId = $rowTravelDate['staffId'];
              $subAgentId = $rowTravelDate['subagentId'];
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
      
        
          if($subAgentId !=''){
            ///data for mail
    
            $result = $conn->query("SELECT * FROM subagent where subagentId='$subAgentId' AND agentId= '$agentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
            $agentId = $result[0]['agentId'];
            $companyName = $result[0]['company'];
            $Password = $result[0]['password'];
            $Email = $result[0]['email'];
    
            $result1 = $conn->query("SELECT * FROM wl_content where agentId='$agentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
            $agentCompanyName = $result1[0]['company_name'];
            $agentCompanyLogo = $result1[0]['companyImage'];
            $agentCompanyEmail = $result1[0]['email'];
            $agentCompanyPhone = $result1[0]['phone'];
            $agentCompanyAddress = $result1[0]['address'];
            $agentCompanyWebsiteLink = $result1[0]['websitelink'];
            $agentCompanyFbLink = $result1[0]['fb_link'];
            $agentCompanyLinkedinLink = $result1[0]['linkedin_link'];
            $agentCompanyWhatsappNum = $result1[0]['whatsapp_num'];
          }
    
      
          if(isset($agentId)){
              $sql1 = mysqli_query($conn,"SELECT * FROM agent WHERE agentId='$agentId'");
              $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);
      
              if(!empty($row1)){
                  $agentEmail = $row1['email'];
                  $companyname = $row1['company'];							
              } 
              
          }
          
          $checker =$conn->query("SELECT * FROM reissue WHERE bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);
          $status = $checker[0]['status'];
          if($status == 'rejected'){
              $response['status'] = 'error';
              $response['message'] = "Booking Reissue Already Rejected";
          }else{
            $sql="UPDATE `reissue` SET `status`='rejected',`actionBy`='$actionBy',`actionAt`='$createdTime' WHERE bookingId='$bookingId' AND agentId='$agentId'";
    
            if ($conn->query($sql) === true) {
          
              $conn->query("UPDATE `booking` SET `status`='Reissue Rejected',`lastUpdated`='$createdTime' WHERE bookingId='$bookingId'");
                $conn->query("INSERT INTO `activityLog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                        VALUES ('$bookingId','$agentId','Reissue Rejected','$remarks','$actionBy','$createdTime')");
        
                    if($subAgentId !=''){
                    $AgentMail = '
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
                              height: 150px;
                              background: #FFA84D;
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
                                padding-top: 20px;
                                padding-bottom: 10px;
                    
                              "
                            >
                            <a style="text-decoration: none; color:#2564B8; font-family: sans-serif; font-size: 25px; padding-top: 20px;" href="https://www.' . $agentCompanyWebsiteLink . '">' . $agentCompanyName . '</a>
                        
                    
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
                                New Booking Reissue Request Cancelled
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
                                  Dear '.$companyName. ', Your Booking Reissue Request has been Cancelled.
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
                                    padding-top: 5px;
                                    font-size: 12px;
                                    line-height: 18px;
                                    color: #2564B8;
                                    padding-right: 20px;
                                    background-color: white;
                                  "
                                >
                                Booking ID: <span> ' . $bookingId.'</span>
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
                                    font-size: 12px;
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
                                    font-size: 12px;
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
                                    font-size: 12px;
                                    line-height: 18px;
                                    color: #929090;
                                    width: 100%;
                                    background-color: white;
                                    padding-bottom: 20px;
                                  "
                                >
                                '.$agentCompanyName.'
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
                                    background-color: #2564B8;
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
                                    background-color: #2564B8;
                                  "
                                >
                                  Mail us at
                                  <a
                                                    style="color: white; font-size: 13px; text-decoration: none"
                                                    href="http://"
                                                    target="_blank"
                                                    > ' . $agentCompanyEmail . '
                                                  </a>
                                                  agency or Call us at ' . $agentCompanyPhone . '
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
                                                      href="https://www.' . $agentCompanyWebsiteLink . '/termsandcondition"
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
                                                      href="https://www.' . $agentCompanyWebsiteLink . '/privacypolicy"
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
                                                  <a href="' . $agentCompanyFbLink . ' "
                                                    ><img
                                                      src="https://cdn.flyfarint.com/fb.png"
                                                      width="25px"
                                                      style="margin: 10px"
                                                  /></a>
                                                  <a href="' . $agentCompanyLinkedinLink . ' "
                                                    ><img
                                                      src="https://cdn.flyfarint.com/lin.png"
                                                      width="25px"
                                                      style="margin: 10px"
                                                  /></a>
                                                  <a href="' . $agentCompanyWhatsappNum . ' "
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
                                                  ' . $agentCompanyAddress . '
                                                </td>
                                    </tr>
                            </table>
                          </div>
                        </div>
                      </body>
                    </html>
                    ';
                                
                                $mail3 = new PHPMailer();
                    
                                          try {
                                              $mail3->isSMTP();
                                              $mail3->Host       = 'b2b.flyfarint.com';
                                              $mail3->SMTPAuth   = true;
                                              $mail3->Username   = 'reissuewl@mailservice.center';
                                              $mail3->Password   = '123Next2$';
                                              $mail3->SMTPSecure = 'ssl';
                                              $mail3->Port       = 465;
                    
                                              //Recipients
                                              $mail3->setFrom('reissuewl@mailservice.center', $agentCompanyName);
                                              $mail3->addAddress("$Email", "SubAgentId : $subAgentId");
                                              // $mail->addCC('habib@flyfarint.com');
                                              // $mail->addCC('afridi@flyfarint.com');
                                            
                    
                                              $mail3->isHTML(true);
                                              $mail3->Subject = "New Booking Reissue Request Cancelled by $agentCompanyName";
                                              $mail3->Body    = $AgentMail;
                                              if (!$mail3->Send()) {
                                                  echo "Mailer Error: " . $mail3->ErrorInfo;
                                              } else {
                                              }
                                          } catch (Exception $e) {
                                              $response['status']="error";
                                              $response['message']="Mail Doesn't Send";
                                          }
        
        
                          $SubAgentEmail ='
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
                                    height: 150px;
                                    background:#FFA84D;
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
                                      padding-top: 20px;
                                      padding-bottom: 10px;
              
                                    "
                                  >
                                    <a style="text-decoration: none; color:#2564B8; font-family: sans-serif; font-size: 25px; padding-top: 20px;" href="https://www.' . $agentCompanyWebsiteLink . '">' . $agentCompanyName . '</a>
              
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
                                      Dear ' . $agentCompanyName . ', Our Booking Reissue Request has been Cancelled.
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
                                        color: #2564B8;
                                        padding-right: 20px;
                                        background-color: white;
                                      "
                                    >
                                      Booking ID: <span>' . $bookingId . '</span>
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
                                      font-size: 12px;
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
                                          font-size: 12px;
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
                                          font-size: 12px;
                                          line-height: 18px;
                                          color: #929090;
                                          width: 100%;
                                          background-color: white;
                                          padding-bottom: 20px
              
                                        "
                                      >
                                        ' . $companyName . '
              
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
                                  $mail->Username   = 'ticketing@b2b.flyfarint.com';
                                  $mail->Password   = '123Next2$';
                                  $mail->SMTPSecure = 'ssl';
                                  $mail->Port       = 465;
                  
                                  //Recipients
                                  $mail->setFrom('ticketing@flyfarint.com', $agentCompanyName);
                                  $mail->addAddress("$agentCompanyEmail", "AgentId : $agentId");
                                  //$mail->addCC('otaoperation@flyfarint.com');
                                  // $mail->addCC('habib@flyfarint.com');
                                  // $mail->addCC('afridi@flyfarint.com');
                  
                                  $mail->isHTML(true);
                                  $mail->Subject = "Booking Reissue Request Cancelled by $agentCompanyName";
                                  $mail->Body    = $SubAgentEmail;
                                
                              } catch (Exception $e) {
                                  $response['status']="error";
                                  $response['message']="Mail Doesn't Send";
                              }
                        if(!$mail->Send()) {
                                $response['status']="success";
                                $response['message']="Booking Reissue Request Reject Successful";
                                
                        } else {
                                $response['status']="success";
                                $response['message']="Booking Reissue Request Reject Successful";
                        }
                            
        
                  }
                  
                  
            }else{
              $response['status'] = 'error';
              $response['message'] = "Query Failed";
            }
          
          } 
      echo json_encode($response); 
                
  }
  }else if(array_key_exists("tobeconfirm", $_GET)){

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $_POST = json_decode(file_get_contents('php://input'), true);

      $bookingId = $_POST['bookingId'];
      $agentId = $_POST['agentId'];
      $staffId = $_POST["staffId"];
      $actionBy = $_POST['actionBy'];
      $remarks = $_POST['remarks'];

      $createdTime = date("Y-m-d H:i:s");
      $DateTime = date("D d M Y h:i A");

      if(isset($bookingId)){
          $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
          $rowTravelDate = mysqli_fetch_array($sqlTravelDate,MYSQLI_ASSOC);
          
          if(!empty($rowTravelDate)){
            $travelDate = $rowTravelDate['travelDate'];
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
    
    
        if(isset($agentId)){
            $sql1 = mysqli_query($conn,"SELECT * FROM agent WHERE agentId='$agentId'");
            $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);
    
            if(!empty($row1)){
                $agentEmail = $row1['email'];
                $companyname = $row1['company'];							
            } 
            
        }
    

      $sql="UPDATE `reissue` SET `status`='tobeconfirm',`actionBy`='$actionBy',`actionAt`='$createdTime' WHERE bookingId='$bookingId' AND agentId='$agentId'";

      if ($conn->query($sql) === true) {

        
        $conn->query("UPDATE `booking` SET `status`='Reissue To Be Corfirm',`lastUpdated`='$createdTime' WHERE bookingId='$bookingId'");
          $conn->query("INSERT INTO `activityLog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                  VALUES ('$bookingId','$agentId','Reissue To Be Corfirm','$remarks','$actionBy','$createdTime')");
                  
              $agentMail ='
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
              Ticket Reissue Request Cancel
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
                          Dear '.$companyname.', You has been Request for Void a Ticket which request has been cancelled. Thank you for stay connected with Fly Far International.
                  </td>     </tr>
              
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
                          >'.$actionBy.'</span>
                      
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
                            Destination: <span style="color: #dc143c">'.$deptFrom.'-'.$arriveTo.', '.$Type.'</span> 
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
                            Reissue Date:  <span style="color: #dc143c">'.$reissuerequestedAt.'</span> 
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
                            Pax:  <span style="color: #dc143c">'.$pax.'</span> 
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
                  $mail->Host       = 'b2b.flyfarint.com';
                  $mail->SMTPAuth   = true;
                  $mail->Username   = 'ticketing@b2b.flyfarint.com';
                  $mail->Password   = '123Next2$';
                  $mail->SMTPSecure = 'ssl';
                  $mail->Port       = 465;

                  //Recipients
                  $mail->setFrom('ticketing@flyfarint.com', 'Fly Far International');
                  $mail->addAddress("$agentEmail", "AgentId : $agentId");
                  $mail->addCC('otaoperation@flyfarint.com');

                  $mail->isHTML(true);
                  $mail->Subject = "Ticket Reissue Request Confirmation - $companyname";
                  $mail->Body    = $agentMail;
                  if (!$mail->Send()) {
                      echo "Mailer Error: " . $mail->ErrorInfo;
                  } else {
                  }
              } catch (Exception $e) {
                  $response['status']="error";
                  $response['message']="Mail Doesn't Send";
              }


              $OwnerMail ='                
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
  Ticket Reissue Request Cancel
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
              Dear Fly Far International, We are Requested for Void a Ticket Which has been Cancelled.   
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
                  >'.$actionBy.'</span>
              
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
                Destination: <span style="color: #dc143c">'.$deptFrom.'-'.$arriveTo.', '.$Type.'</span> 
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
                Travel Date:  <span style="color: #dc143c">'.$reissuerequestedAt.'	</span> 
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
                Airline: <span style="color: #dc143c">'.$Airlines.'	</span> 
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
                Pax:  <span style="color: #dc143c">'.$pax.'</span> 
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
          </table>


        </div>
      </div>
    </body>
  </html>

  ';

          //         $response['status'] = "success";
          //         $response['message'] = "Void Rejected Successfully";
          //     }else{
          //         $response['status'] = "success";
          //         $response['message'] = "Void Rejected Failed Successfully";
          //     }

              // }else{
          //     $response['status'] = "success";
          //         $response['message'] = "Void Rejected Failed Successfully";
              // }

              $mail1 = new PHPMailer();

              try {
                  $mail1->isSMTP();
                  $mail1->Host       = 'b2b.flyfarint.com';
                  $mail1->SMTPAuth   = true;
                  $mail1->Username   = 'ticketing@b2b.flyfarint.com';
                  $mail1->Password   = '123Next2$';
                  $mail1->SMTPSecure = 'ssl';
                  $mail1->Port       = 465;

                  //Recipients
                  $mail1->setFrom('ticketing@flyfarint.com', 'Fly Far International');
                  $mail1->addAddress("otaoperation@flyfarint.com", "Void Ticket Request");


                  $mail1->isHTML(true);
                  $mail1->Subject = "New Ticket Reissue Request by - $companyname";
                  $mail1->Body    = $OwnerMail;


                  if (!$mail1->Send()) {
                      $response['status']="success";
                      $response['InvoiceId']="$reissueId";
                      $response['message']="Void Rejected Successfully";
                      $response['error']="Reissue Rejected Successfully";
                  } else {
                      $response['status']="success";
                      $response['InvoiceId']="$reissueId";
                      $response['message']="Reissue Rejected Failed Successfully";
                  }
              } catch (Exception $e1) {
                  $response['status']="error";
                  $response['message']="Mail Doesn't Send";
              }

          echo json_encode($response);
          }
      }
  }else if(array_key_exists("confirmed", $_GET)){

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $_POST = json_decode(file_get_contents('php://input'), true);

      $bookingId = $_POST['bookingId'];
      $agentId = $_POST['agentId'];
      $staffId = $_POST["staffId"];
      $actionBy = $_POST['actionBy'];

      $createdTime = date("Y-m-d H:i:s");
      $DateTime = date("D d M Y h:i A");

      if(isset($bookingId)){
          $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
          $rowTravelDate = mysqli_fetch_array($sqlTravelDate,MYSQLI_ASSOC);
          
          if(!empty($rowTravelDate)){
            $travelDate = $rowTravelDate['travelDate'];
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
    
    
        if(isset($agentId)){
            $sql1 = mysqli_query($conn,"SELECT * FROM agent WHERE agentId='$agentId'");
            $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);
    
            if(!empty($row1)){
                $agentEmail = $row1['email'];
                $companyname = $row1['company'];							
            } 
            
        }
    

      $sql="UPDATE `reissue` SET `status`='tobeconfirm',`actionBy`='$actionBy',`actionAt`='$createdTime' WHERE bookingId='$bookingId' AND agentId='$agentId'";

      if ($conn->query($sql) === true) {

        
        $conn->query("UPDATE `booking` SET `status`='Reissue To Be Corfirmed',`lastUpdated`='$createdTime' WHERE bookingId='$bookingId'");
          $conn->query("INSERT INTO `activityLog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                  VALUES ('$bookingId','$agentId','Reissue To Be Corfirmed','$remarks','$actionBy','$createdTime')");
                  
              $agentMail ='
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
              Ticket Reissue Request Cancel
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
                          Dear '.$companyname.', You has been Request for Void a Ticket which request has been cancelled. Thank you for stay connected with Fly Far International.
                  </td>     </tr>
              
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
                          >'.$actionBy.'</span>
                      
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
                            Destination: <span style="color: #dc143c">'.$deptFrom.'-'.$arriveTo.', '.$Type.'</span> 
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
                            Reissue Date:  <span style="color: #dc143c">'.$reissuerequestedAt.'</span> 
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
                            Pax:  <span style="color: #dc143c">'.$pax.'</span> 
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
                  $mail->Host       = 'b2b.flyfarint.com';
                  $mail->SMTPAuth   = true;
                  $mail->Username   = 'ticketing@b2b.flyfarint.com';
                  $mail->Password   = '123Next2$';
                  $mail->SMTPSecure = 'ssl';
                  $mail->Port       = 465;

                  //Recipients
                  $mail->setFrom('ticketing@flyfarint.com', 'Fly Far International');
                  $mail->addAddress("$agentEmail", "AgentId : $agentId");
                  $mail->addCC('otaoperation@flyfarint.com');

                  $mail->isHTML(true);
                  $mail->Subject = "Ticket Reissue Request Confirmation - $companyname";
                  $mail->Body    = $agentMail;
                  if (!$mail->Send()) {
                      echo "Mailer Error: " . $mail->ErrorInfo;
                  } else {
                  }
              } catch (Exception $e) {
                  $response['status']="error";
                  $response['message']="Mail Doesn't Send";
              }


              $OwnerMail ='                
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
  Ticket Reissue Request Cancel
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
              Dear Fly Far International, We are Requested for Void a Ticket Which has been Cancelled.   
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
                  >'.$actionBy.'</span>
              
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
                Destination: <span style="color: #dc143c">'.$deptFrom.'-'.$arriveTo.', '.$Type.'</span> 
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
                Travel Date:  <span style="color: #dc143c">'.$reissuerequestedAt.'	</span> 
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
                Airline: <span style="color: #dc143c">'.$Airlines.'	</span> 
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
                Pax:  <span style="color: #dc143c">'.$pax.'</span> 
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
          </table>


        </div>
      </div>
    </body>
  </html>

  ';

          //         $response['status'] = "success";
          //         $response['message'] = "Void Rejected Successfully";
          //     }else{
          //         $response['status'] = "success";
          //         $response['message'] = "Void Rejected Failed Successfully";
          //     }

              // }else{
          //     $response['status'] = "success";
          //         $response['message'] = "Void Rejected Failed Successfully";
              // }

              $mail1 = new PHPMailer();

              try {
                  $mail1->isSMTP();
                  $mail1->Host       = 'b2b.flyfarint.com';
                  $mail1->SMTPAuth   = true;
                  $mail1->Username   = 'ticketing@b2b.flyfarint.com';
                  $mail1->Password   = '123Next2$';
                  $mail1->SMTPSecure = 'ssl';
                  $mail1->Port       = 465;

                  //Recipients
                  $mail1->setFrom('ticketing@flyfarint.com', 'Fly Far International');
                  $mail1->addAddress("otaoperation@flyfarint.com", "Void Ticket Request");


                  $mail1->isHTML(true);
                  $mail1->Subject = "New Ticket Reissue Request by - $companyname";
                  $mail1->Body    = $OwnerMail;


                  if (!$mail1->Send()) {
                      $response['status']="success";
                      $response['InvoiceId']="$reissueId";
                      $response['message']="Void Rejected Successfully";
                      $response['error']="Reissue Rejected Successfully";
                  } else {
                      $response['status']="success";
                      $response['InvoiceId']="$reissueId";
                      $response['message']="Reissue Rejected Failed Successfully";
                  }
              } catch (Exception $e1) {
                  $response['status']="error";
                  $response['message']="Mail Doesn't Send";
              }

          echo json_encode($response);
          }
      }
  }
}else{
  authorization($conn);
} 
  