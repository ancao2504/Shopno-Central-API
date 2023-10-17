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

          ///data for mail
          $sanewBalance = 0;
          if ($subagentId != '') {

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

              $sarefundPenalty = $subagentCost - ($refundAmount);

              $subagentsql1 = mysqli_query($conn,"SELECT lastAmount FROM `subagent_ledger` where agentId = '$agentId' AND subagentId ='$subagentId' 
                  ORDER BY id DESC LIMIT 1");         
              $subagentrow1 = mysqli_fetch_array($subagentsql1,MYSQLI_ASSOC);
        
              if(!empty($subagentrow1)){
                  $salastAmount = $subagentrow1['lastAmount'];							
              }else{
                  
              }

              $sanewBalance = (int)$salastAmount + (int)$refundAmount;
          }

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
          $conn->query("INSERT INTO `activityLog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                  VALUES ('$bookingId','$agentId','Refunded','Refund Given $refundAmount','$actionBy','$createdTime')");
              
                
          if($conn->query($sql) === TRUE){
              if (!empty($subagentId)) {

                  $conn->query("INSERT INTO `subagent_ledger`(`agentId`,`subagentId`,`refund`, `lastAmount`, `transactionId`, `details`, `reference`, `actionBy`, `createdAt`)
                  VALUES ('$agentId','$subagentId','$refundAmount','$sanewBalance','$bookingId','Refunded Money $TicketId Ticket Invoice $Type Air Ticket $Route - $Airlines was Requested By $refundrequestedBy','$refundId','$actionBy','$createdTime')");

                  $SubAgentMail = '
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
                            <a style="text-decoration: none; color:#ffffff; font-family: sans-serif; font-size: 25px; padding-top: 20px;" href="https://www.' . $agentCompanyWebsiteLink . '">' . $agentCompanyName . '</a>
              
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
                              Booking Refund Request Accepted
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
                                Dear ' . $companyName . ', Your Booking Refund Request has been Accepted.
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
                            Booking ID: <span>' .$bookingId . '</span>
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
                              ' . $agentCompanyName . '
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
                      $mail3->Host = 'b2b.flyfarint.com';
                      $mail3->SMTPAuth = true;
                      $mail3->Username = 'refundwl@mailservice.center';
                      $mail3->Password = '123Next2$';
                      $mail3->SMTPSecure = 'ssl';
                      $mail3->Port = 465;

                      //Recipients
                      $mail3->setFrom("refundwl@mailservice.center", $agentCompanyName);
                      $mail3->addAddress("$Email", "SubAgentId : $subagentId");
                      // $mail3->addCC('habib@flyfarint.com');
                      // $mail3->addCC('afridi@flyfarint.com');
                      

                      $mail3->isHTML(true);
                      $mail3->Subject = "New Booking Refund Request Approved by $agentCompanyName";
                      $mail3->Body = $SubAgentMail;
                      //print_r($mail);
                      if (!$mail3->Send()) {
                        echo "Mailer Error: " . $mail3->ErrorInfo;
                      }


                  } catch (Exception $e) {

                  }
              }

                              
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
                      Ticket Refund Request Approve
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
                                  Dear '.$agentCompanyName.', You are Requested for Refund a Ticket, Which has been Approve.   
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
                                      padding-top: 10px;
                                      width: 100%;
                                      background-color: white;
                      
                                    "
                                  >
                                    Cost:  <span style="color: #dc143c">'.$TicketCost.'
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
                      $mail->setFrom('ticketing@flyfarint.com', $companyName);
                      $mail->addAddress("$agentCompanyEmail", "AgentId : $agentId");
                      //$mail->addCC('otaoperation@flyfarint.com');
                      // $mail->addCC('habib@flyfarint.com');
                      // $mail->addCC('afridi@flyfarint.com');
      
                      $mail->isHTML(true);
                      $mail->Subject = "Booking Refund Request Approved by $agentCompanyName";
                      $mail->Body    = $agentMail;
                      if (!$mail->Send()) {
                          echo "Mailer Error: " . $mail->ErrorInfo;
                      } else {
                      }
                  } catch (Exception $e) {
                      
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
      Ticket Refund Request Approve
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
                  Dear Fly Far International, We are Requested for Refund a Ticket, Which has been Approve.   
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
                      padding-top: 10px;
                      width: 100%;
                      background-color: white;
      
                    "
                  >
                    Cost:  <span style="color: #dc143c">'.$TicketCost.'
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
                  '.$agentCompanyName.'
      
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
                  $mail1->Username   = 'ticketing@b2b.flyfarint.com';
                  $mail1->Password   = '123Next2$';
                  $mail1->SMTPSecure = 'ssl';
                  $mail1->Port       = 465;

                  //Recipients
                  $mail1->setFrom('ticketing@flyfarint.com', $agentCompanyName);
                  $mail1->addAddress("otaoperation@flyfarint.com");
                  // $mail1->addCC('habib@flyfarint.com');
                  // $mail1->addCC('afridi@flyfarint.com');

                  $mail1->isHTML(true);
                  $mail1->Subject = "New Ticket Refund Request Approved by $agentCompanyName";
                  $mail1->Body    = $OwnerMail;


                  if (!$mail1->Send()) {
                      $response['status']="success";
                      $response['RefundId']="$refundId";
                      $response['message']="Refund Approved Successfully";
                      $response['error']="Refund Approved Successfully";
                  } else {
                      $response['status']="success";
                      $response['RefundId']="$refundId";
                      $response['message']="Refund Approved Successfully";
                  }
              } catch (Exception $e1) {
                  $response['status']="error";
                  $response['message']="Mail Doesn't Send";
              }

              echo json_encode($response);     
          }
        }else{
          $response['status']="error";
          $response['message']="Ticket Already Refunded";
        }
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
          $conn->query("INSERT INTO `activityLog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                      VALUES ('$bookingId','$agentId','Refund Rejected','$remarks','$actionBy','$createdTime')");


        ///data for mail
        $bookingSql = mysqli_query($conn,"SELECT * FROM booking WHERE bookingId='$bookingId'");
        $bookingData = mysqli_fetch_array($bookingSql,MYSQLI_ASSOC);
        $subagentId = $bookingData['subagentId'];
        $deptFrom = $bookingData['deptFrom'];
        $arriveTo = $bookingData['arriveTo'];
        $Type = $bookingData['tripType'];
        $travelDate = $bookingData['travelDate'];
        $Airlines = $bookingData['airlines'];
        $pax = $bookingData['pax'];
        $TicketCost = $bookingData['netCost'];
        
        // echo $subagentId;
      


          
              if ($conn->query($sqlBooking) === true) {

                $result = $conn->query("SELECT * FROM subagent where subagentId='$subagentId' AND agentId= '$agentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
                $SubagentId="";
                if(!empty($result)){
                  $SubagentId  = $result[0]['subagentId'];
                  $agentId = $result[0]['agentId'];
                  $companyName = $result[0]['company'];
                  $Password = $result[0]['password'];
                  $Email = $result[0]['email'];
                }
      
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

                      if ($SubagentId !='') {
                          $subagentMail = '
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
                              <a style="text-decoration: none; color:#ffffff; font-family: sans-serif; font-size: 25px; padding-top: 20px;" href="https://www.'.$agentCompanyWebsiteLink.'">'.$agentCompanyName.'</a>
              
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
                              Booking Refund Request Cancelled
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
                                  Dear ' . $companyName . ', Your Booking Refund Request has been Cancelled.
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
                              ' . $agentCompanyName . '
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

                          $mail = new PHPMailer();

                          try {
                              $mail->isSMTP();
                              $mail->Host       = 'b2b.flyfarint.com';
                              $mail->SMTPAuth   = true;
                              $mail->Username   = 'refundwl@mailservice.center';
                              $mail->Password   = '123Next2$';
                              $mail->SMTPSecure = 'ssl';
                              $mail->Port       = 465; 

                              //Recipients
                              $mail->setFrom('refundwl@mailservice.center', $agentCompanyName);
                              $mail->addAddress("$Email", "SubAgentId : $subagentId");
                              $mail->addCC('otaoperation@flyfarint.com');
                              //$mail->addCC('habib@flyfarint.com');
                              // $mail->addCC('afridi@flyfarint.com');
                              

                              $mail->isHTML(true);
                              $mail->Subject = "Booking Refund Request Cancelled by $agentCompanyName";
                              $mail->Body    = $subagentMail;
                              if (!$mail->Send()) {
                                  echo "Mailer Error: " . $mail->ErrorInfo;
                              } else {
                              }
                          } catch (Exception $e) {
                              $response['status']="error";
                              $response['message']="Mail Doesn't Send";
                          }
                      }

                      $agentEmail = '
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
      Ticket Refund Request Cancelled
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
                  Dear '.$agentCompanyName.', You are Requested for Refund a Ticket, Which has been Cancelled.   
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
                      padding-top: 10px;
                      width: 100%;
                      background-color: white;
      
                    "
                  >
                    Cost:  <span style="color: #dc143c">'.$TicketCost.'
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

                      $mail2 = new PHPMailer();

                      try {
                          $mail2->isSMTP();
                          $mail2->Host       = 'b2b.flyfarint.com';
                          $mail2->SMTPAuth   = true;
                          $mail2->Username   = 'ticketing@b2b.flyfarint.com';
                          $mail2->Password   = '123Next2$';
                          $mail2->SMTPSecure = 'ssl';
                          $mail2->Port       = 465;

                          //Recipients
                          $mail2->setFrom('ticketing@flyfarint.com', "Fly Far International");
                          $mail2->addAddress("$agentCompanyEmail", "AgentId : $agentId");
                          $mail2->addCC('otaoperation@flyfarint.com');
                          // $mail->addCC('habib@flyfarint.com');
                          // $mail->addCC('afridi@flyfarint.com');

                          $mail2->isHTML(true);
                          $mail2->Subject = "Booking Refund Request Cancelled by Fly Far International";
                          $mail2->Body    = $agentEmail;
                          if (!$mail2->Send()) {
                              echo "Mailer Error: " . $mail2->ErrorInfo;
                          } else {
                          }
                      } catch (Exception $e) {
                          $response['status']="error";
                          $response['message']="Mail Doesn't Send";
                      }
                  } else {
                      $response['status'] = "success";
                      $response['message'] = "Refund Rejected Failed Successfully";
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
      Booking Refund Request Cancelled
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
                  Dear Fly Far International, We are Requested for Refund a Ticket, Which has been Cancelled.   
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
                      padding-top: 10px;
                      width: 100%;
                      background-color: white;
      
                    "
                  >
                    Cost:  <span style="color: #dc143c">'.$TicketCost.'
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
                  '.$agentCompanyName.'
      
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
                  $mail3->Username   = 'ticketing@b2b.flyfarint.com';
                  $mail3->Password   = '123Next2$';
                  $mail3->SMTPSecure = 'ssl';
                  $mail3->Port       = 465;

                  //Recipients
                  $mail3->setFrom('ticketing@flyfarint.com', $agentCompanyName);
                  $mail3->addAddress("otaoperation@flyfarint.com");
                  //$mail3->addCC('habib@flyfarint.com');
                  // $mail1->addCC('afridi@flyfarint.com');

                  $mail3->isHTML(true);
                  $mail3->Subject = "New Ticket Refund Request Cancelled for $agentCompanyName";
                  $mail3->Body    = $OwnerMail;

                  if (!$mail3->Send()) {
                              echo "Mailer Error: " . $mail3->ErrorInfo;
                          }
          
                      } catch (Exception $e) {
                          $response['status'] = "error";
                          $response['message'] = "Mail Doesn't Send";
                      }

                      $response['status'] = "success";
                      $response['message'] = "Refund Rejected";     
      }
      echo json_encode($response);
    }else{
        $response['status'] = "error";
        $response['message'] = "Refund Rejected Failed";
        echo json_encode($response);
    }
  }

}else{
  authorization($conn);
}

  