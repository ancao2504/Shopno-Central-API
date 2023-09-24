<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $_POST = json_decode(file_get_contents('php://input'), true);

  // $agentId = $_POST["agentId"];
  $userId = $_POST["userId"];
  $bookingId = $_POST["bookingId"];

  $createdTime = date('Y-m-d H:i:s');

  $DateTime = date("D d M Y h:i A");

  //Mail Data
  // $result1 = $conn->query("SELECT * FROM B2C_wl_content where agentId='$agentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);

  // $agentCompanyName = $result1[0]['company_name'];
  // $agentCompanyLogo = $result1[0]['companyImage'];
  // $agentCompanyEmail = $result1[0]['email'];
  // $agentCompanyPhone = $result1[0]['phone'];
  // $agentCompanyAddress = $result1[0]['address'];
  // $agentCompanyWebsiteLink = $result1[0]['websitelink'];
  // $agentCompanyFbLink = $result1[0]['fb_link'];
  // $agentCompanyLinkedinLink = $result1[0]['linkedin_link'];
  // $agentCompanyWhatsappNum = $result1[0]['whatsapp_num'];

  // // agent data from mail

  // $result = $conn->query("SELECT * FROM subagent where agentId='$agentId' AND  userId='$userId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
  // $subAgentEmail = $result[0]['email'];
  // $subAgentCompanyName = $result[0]['company'];

  if (isset($bookingId)) {
    $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId' AND userId='$userId'");
    $rowTravelDate = mysqli_fetch_array($sqlTravelDate, MYSQLI_ASSOC);
    // echo json_encode($rowTravelDate); exit;
    if (!empty($rowTravelDate)) {
      $travelDate = $rowTravelDate['travelDate'];
      $pax = $rowTravelDate['pax'];
      $Type = $rowTravelDate['tripType'];
      $Airlines = $rowTravelDate["airlines"];
      $From = $rowTravelDate['deptFrom'];
      $To = $rowTravelDate['arriveTo'];
      $Route = "$From - $To";
      $PNR = $rowTravelDate['pnr'];
      $GDS = $rowTravelDate['gds'];
      $Status = $rowTravelDate['status'];
      $netCost = $rowTravelDate["netCost"];
      $subagentCost = $rowTravelDate['subagentCost'];
      $bookedBy = $rowTravelDate['bookedBy'];
    }
  }

  if ($Status == 'Issue In Processing') {
    $response['status'] = 'Success';
    $response['message'] = 'Your booking has been already Issue In Processing';
    echo json_encode($response);
    exit();
  }

  $creditBalance = 0;
  if (isset($userId)) {
    $sql1 = mysqli_query($conn, "SELECT * FROM agent WHERE userId='$userId'");
    $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

    if (!empty($row1)) {
      $userEmail = $row1['email'];
      // $companyname = $row1['company'];
      // $bonus = $row1['bonus'];
      // $creditBalance = $row1['credit'];
    }
  }

  //Agent Ledger
  $userleadgersql = mysqli_query($conn, "SELECT lastAmount FROM `agent_ledger` where userId = '$userId'
            ORDER BY id DESC LIMIT 1");

  $userrow1 = mysqli_fetch_array($userleadgersql, MYSQLI_ASSOC);

  if (!empty($row1)) {
    $userAmount = $userrow1['lastAmount'];
  } else {
    $userAmount = 0;
  }
  //Sub Agent Ledger
  // $subagentleadersql = mysqli_query($conn, "SELECT lastAmount FROM `subagent_ledger` where userId = '$userId'
  //           ORDER BY id DESC LIMIT 1");
  // $subagentrow1 = mysqli_fetch_array($subagentleadersql, MYSQLI_ASSOC);
  // if (!empty($row1)) {
    //   $subagentAmount = $subagentrow1['lastAmount'];
    // } else {
      //   $subagentAmount = 0;
      // }
      
  // echo $agentAmount;
  // echo ' <br/>';
  // echo $subagentAmount;
  // echo ' <br/>';
  // echo $netCost;
  // echo ' <br/>';
  // echo $subagentCost;
  echo "$userAmount" ."-". "$subagentCost";
  exit;
  $agentnewBalance=$userAmount-$subagentCost;
  if ($subagentCost> $userAmount) {
    echo("$userAmount-$subagentCost");
    $response['status'] = 'error';
    $response['message'] = 'Your booking has been failed due to insufficient balanced';
    echo json_encode($response);
    exit();
  } 

  $LeadgerUpdate = "INSERT INTO `agent_ledger`(`userId`,`purchase`, `lastAmount`, `transactionId`, `details`, `reference`,`actionBy`,`createdAt`)
    VALUES ('$userId','$subagentCost','$agentnewBalance','$bookingId','$Type Air Ticket $Route - $Airlines By $userId ','$bookingId','','$createdTime')";
  // echo json_encode($LeadgerUpdate); exit;
  if ($conn->query($LeadgerUpdate) === true) {

    // $conn->query("INSERT INTO `activityLog`(`ref`,`userId`,`status`,`actionBy`, `actionAt`)
    //                 VALUES ('$bookingId','$userId','Issue In Processing','$userId','$createdTime')");

    // $conn->query("INSERT INTO `income_statement`(`userId`,`agentId`,`bookingId`,`netCost`,`sellPrice`, `createdAt`)
    //                 VALUES ('$userId','$agentId','$bookingId','$netCost','$subagentCost','$createdTime')");

    $updateBooking="UPDATE `booking` SET `status`='Issue In Processing',`lastUpdated`='$createdTime' where bookingId='$bookingId'";

    // $subagentLedger = "INSERT INTO `subagent_ledger`(`agentId`,`userId`,`purchase`, `lastAmount`, `transactionId`, `details`, `reference`,`actionBy`,`platform`,`createdAt`)
    //     VALUES ('$agentId','$userId','$subagentCost','$subagentNewBalance','$bookingId','$Type Air Ticket $Route - $Airlines By $userId ','$bookingId','','WLB2C','$createdTime')";

    if ($conn->query($updateBooking)) {
      $response['InvoiceId'] = "$bookingId";
      $response['message'] = "Ticketing Request Successfully";
      $response['status'] = "success";
    } else {
      $response['status'] = "error";
      $response['InvoiceId'] = "$bookingId";
      $response['message'] = "Ticketing Request Successfully";
    }

    // Mail Sending

    // if ($userId != "") {

    //   //Information for Email Template
    //   $data = $conn->query("SELECT `email`, `company_name`,`websitelink`, `address`,`phone`,`fb_link`,`linkedin_link`, `whatsapp_num` FROM `B2C_wl_content` WHERE agentId='$agentId'")->fetch_all(MYSQLI_ASSOC);
    //   $agentEmail = $data[0]['email'];
    //   $agentPhone = $data[0]['phone'];
    //   $agentAddress = $data[0]['address'];
    //   $agentCompany_name = $data[0]['company_name'];
    //   $agentWebsiteLink = $data[0]['websitelink'];
    //   $agentFbLink = $data[0]['fb_link'];
    //   $agentLinkedInLink = $data[0]['linkedin_link'];
    //   $agentWhatsappNum = $data[0]['whatsapp_num'];

    //   $userData = $conn->query("SELECT `name`,`email` FROM `subagent` WHERE agentId = '$agentId' AND userId = '$userId'")->fetch_all(MYSQLI_ASSOC);
    //   $userName = $userData[0]['name'];
    //   $userEmail = $userData[0]['email'];

    //   $AgentEmail = '
    //               <!DOCTYPE html>
    //               <html lang="en">
    //                 <head>
    //                   <meta charset="UTF-8" />
    //                   <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    //                   <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    //                   <title>Deposit Request</title>
    //                 </head>
    //                 <body>
    //                   <div class="div" style="width: 650px; height: 100vh; margin: 0 auto">
    //                     <div style="width: 650px; height: 150px; background: #32d095">
    //                       <table
    //                         border="0"
    //                         cellpadding="0"
    //                         cellspacing="0"
    //                         align="center"
    //                         style="
    //                           border-collapse: collapse;
    //                           border-spacing: 0;
    //                           padding: 0;
    //                           width: 650px;
    //                           border-radius: 10px;
    //                         "
    //                       >
    //                         <tr>
    //                           <td
    //                             align="center"
    //                             valign="top"
    //                             style="
    //                               border-collapse: collapse;
    //                               border-spacing: 0;
    //                               color: #000000;
    //                               font-family: sans-serif;
    //                               font-weight: bold;
    //                               font-size: 20px;
    //                               line-height: 38px;
    //                               padding-top: 20px;
    //                               padding-bottom: 10px;
    //                             "
    //                           >
    //                             <a
    //                               style="
    //                                 text-decoration: none;
    //                                 color: #ffffff;
    //                                 font-family: sans-serif;
    //                                 font-size: 25px;
    //                                 padding-top: 20px;
    //                               "
    //                               href="https://www.' . $agentWebsiteLink . '/"
    //                             >
    //                               ' . $agentCompany_name . '</a
    //                             >
    //                           </td>
    //                         </tr>
    //                       </table>
                  
    //                       <table
    //                         border="0"
    //                         cellpadding="0"
    //                         cellspacing="0"
    //                         align="center"
    //                         bgcolor="white"
    //                         style="
    //                           border-collapse: collapse;
    //                           border-spacing: 0;
    //                           padding: 0;
    //                           width: 550px;
    //                         "
    //                       >
    //                         <tr>
    //                           <td
    //                             align="center"
    //                             valign="top"
    //                             style="
    //                               border-collapse: collapse;
    //                               border-spacing: 0;
    //                               color: #000000;
    //                               font-family: sans-serif;
    //                               text-align: left;
    //                               padding-left: 20px;
    //                               font-weight: bold;
    //                               font-size: 19px;
    //                               line-height: 38px;
    //                               padding-top: 10px;
    //                               background-color: white;
    //                             "
    //                           >
    //                           Booking Issue Request
    //                           </td>
    //                         </tr>
    //                         <tr>
    //                           <td
    //                             align="center"
    //                             valign="top"
    //                             style="
    //                               border-collapse: collapse;
    //                               border-spacing: 0;
    //                               font-family: sans-serif;
    //                               text-align: left;
    //                               padding-left: 20px;
    //                               font-weight: bold;
    //                               padding-top: 15px;
    //                               font-size: 12px;
    //                               line-height: 18px;
    //                               color: #929090;
    //                               padding-right: 20px;
    //                               background-color: white;
    //                             "
    //                           >
    //                           Dear ' . $userName . ', Your Issue Booking Request has been confirmed please wait for while for ticketed. Thank you.
    //                           </td>
    //                         </tr>
                  
    //                         <tr>
    //                           <td
    //                             align="center"
    //                             valign="top"
    //                             style="
    //                               border-collapse: collapse;
    //                               border-spacing: 0;
    //                               font-family: sans-serif;
    //                               text-align: left;
    //                               padding-left: 20px;
    //                               font-weight: bold;
    //                               padding-top: 15px;
    //                               font-size: 12px;
    //                               line-height: 18px;
    //                               color: #525371;
    //                               padding-right: 20px;
    //                               background-color: white;
    //                             "
    //                           >
    //                             Booking ID: <span>' . $bookingId . '</span>
    //                           </td>
    //                         </tr>
    //                         <tr>
    //                           <td
    //                             align="center"
    //                             valign="top"
    //                             style="
    //                               border-collapse: collapse;
    //                               border-spacing: 0;
    //                               color: #000000;
    //                               font-family: sans-serif;
    //                               text-align: left;
    //                               padding-left: 20px;
    //                               font-weight: bold;
    //                               padding-top: 20px;
    //                               font-size: 13px;
    //                               line-height: 18px;
    //                               color: #929090;
    //                               padding-top: 20px;
    //                               width: 100%;
    //                               background-color: white;
    //                             "
    //                           >
    //                             If you have any questions, just contact us we are always happy to
    //                             help you out.
    //                           </td>
    //                         </tr>
                  
    //                         <tr>
    //                           <td
    //                             align="center"
    //                             valign="top"
    //                             style="
    //                               border-collapse: collapse;
    //                               border-spacing: 0;
    //                               color: #000000;
    //                               font-family: sans-serif;
    //                               text-align: left;
    //                               padding-left: 20px;
    //                               font-weight: bold;
    //                               padding-top: 20px;
    //                               font-size: 13px;
    //                               line-height: 18px;
    //                               color: #929090;
    //                               padding-top: 20px;
    //                               width: 100%;
    //                               background-color: white;
    //                             "
    //                           >
    //                             Sincerely,
    //                           </td>
    //                         </tr>
                  
    //                         <tr>
    //                           <td
    //                             align="center"
    //                             valign="top"
    //                             style="
    //                               border-collapse: collapse;
    //                               border-spacing: 0;
    //                               color: #000000;
    //                               font-family: sans-serif;
    //                               text-align: left;
    //                               padding-left: 20px;
    //                               font-weight: bold;
    //                               font-size: 13px;
    //                               line-height: 18px;
    //                               color: #929090;
    //                               width: 100%;
    //                               background-color: white;
    //                               padding-bottom: 20px;
    //                             "
    //                           >
    //                            ' . $agentCompany_name . '
    //                           </td>
    //                         </tr>
                  
    //                         <tr>
    //                           <td
    //                             align="center"
    //                             valign="top"
    //                             style="
    //                               border-collapse: collapse;
    //                               border-spacing: 0;
    //                               color: #ffffff;
    //                               font-family: sans-serif;
    //                               text-align: center;
    //                               font-weight: 600;
    //                               font-size: 14px;
    //                               color: #ffffff;
    //                               padding-top: 15px;
    //                               background-color: #525371;
    //                             "
    //                           >
    //                             Need more help?
    //                           </td>
    //                         </tr>
                  
    //                         <tr>
    //                           <td
    //                             align="center"
    //                             valign="top"
    //                             style="
    //                               border-collapse: collapse;
    //                               border-spacing: 0;
    //                               color: #ffffff;
    //                               font-family: sans-serif;
    //                               text-align: center;
    //                               font-size: 12px;
    //                               color: #ffffff;
    //                               padding-top: 8px;
    //                               padding-bottom: 20px;
    //                               padding-left: 30px;
    //                               padding-right: 30px;
    //                               background-color: #525371;
    //                             "
    //                           >
    //                             Mail us at
    //                             <a
    //                               style="color: white; font-size: 13px; text-decoration: none"
    //                               href="http://"
    //                               target="_blank"
    //                               >' . $agentEmail . '
    //                             </a>
    //                             agency or Call us at ' . $agentPhone . '
    //                           </td>
    //                         </tr>
                  
    //                         <tr>
    //                           <td
    //                             valign="top"
    //                             align="left"
    //                             style="
    //                               border-collapse: collapse;
    //                               border-spacing: 0;
    //                               color: #000000;
    //                               font-family: sans-serif;
    //                               text-align: left;
    //                               font-weight: bold;
    //                               font-size: 12px;
    //                               line-height: 18px;
    //                               color: #929090;
    //                             "
    //                           >
    //                             <p>
    //                               <a
    //                                 style="
    //                                   font-weight: bold;
    //                                   font-size: 12px;
    //                                   line-height: 15px;
    //                                   color: #222222;
    //                                 "
    //                                 href="https://www.' . $agentWebsiteLink . '/termsandcondition"
    //                                 >Tearms & Conditions</a
    //                               >
    //                               <a
    //                                 style="
    //                                   font-weight: bold;
    //                                   font-size: 12px;
    //                                   line-height: 15px;
    //                                   color: #222222;
    //                                   padding-left: 10px;
    //                                 "
    //                                 href="https://www.' . $agentWebsiteLink . '/privacypolicy"
    //                                 >Privacy Policy</a
    //                               >
    //                             </p>
    //                           </td>
    //                         </tr>
    //                         <tr>
    //                           <td
    //                             align="center"
    //                             valign="top"
    //                             style="
    //                               border-collapse: collapse;
    //                               border-spacing: 0;
    //                               font-family: sans-serif;
    //                               text-align: center;
    //                               padding-left: 20px;
    //                               font-weight: bold;
    //                               font-size: 12px;
    //                               line-height: 18px;
    //                               color: #929090;
    //                               padding-right: 20px;
    //                             "
    //                           >
    //                             <a href="' . $agentFbLink . ' "
    //                               ><img
    //                                 src="https://cdn.flyfarint.com/fb.png"
    //                                 width="25px"
    //                                 style="margin: 10px"
    //                             /></a>
    //                             <a href="' . $agentLinkedInLink . ' "
    //                               ><img
    //                                 src="https://cdn.flyfarint.com/lin.png"
    //                                 width="25px"
    //                                 style="margin: 10px"
    //                             /></a>
    //                             <a href="' . $agentWhatsappNum . ' "
    //                               ><img
    //                                 src="https://cdn.flyfarint.com/wapp.png "
    //                                 width="25px"
    //                                 style="margin: 10px"
    //                             /></a>
    //                           </td>
    //                         </tr>
                  
    //                         <tr>
    //                           <td
    //                             align="center"
    //                             valign="top"
    //                             style="
    //                               border-collapse: collapse;
    //                               border-spacing: 0;
    //                               color: #929090;
    //                               font-family: sans-serif;
    //                               text-align: center;
    //                               font-weight: 500;
    //                               font-size: 12px;
    //                               padding-top: 5px;
    //                               padding-bottom: 5px;
    //                               padding-left: 10px;
    //                               padding-right: 10px;
    //                             "
    //                           >
    //                             ' . $agentAddress . '
    //                           </td>
    //                         </tr>
    //                       </table>
    //                     </div>
    //                   </div>
    //                 </body>
    //               </html>        
    //               ';

    //   $mail1 = new PHPMailer();

    //   try {
    //     $mail1->isSMTP();
    //     $mail1->Host = 'b2b.flyfarint.com';
    //     $mail1->SMTPAuth = true;
    //     $mail1->Username = 'bookingwl@mailservice.center';
    //     $mail1->Password = '123Next2$';
    //     $mail1->SMTPSecure = 'ssl';
    //     $mail1->Port = 465;

    //     //Recipients
    //     $mail1->setFrom("bookingwl@mailservice.center", $agentCompany_name);
    //     $mail1->addAddress("$userEmail", "AgentId : $agentId");
    //     $mail1->addCC('habib@flyfarint.com');
    //     $mail1->addCC('afridi@flyfarint.com');


    //     $mail1->isHTML(true);
    //     $mail1->Subject = "Booking Issue Request Confirmation by $userName";
    //     $mail1->Body = $AgentEmail;

    //     //print_r($mail);
    //     if (!$mail1->Send()) {
    //       echo "Mailer Error: " . $mail1->ErrorInfo;
    //     }
    //   } catch (Exception $e) {
    //   }
    // }

    // //Agent Mail

    // $AgentMail = '
    //     <!DOCTYPE html>
    //     <html lang="en">
    //       <head>
    //         <meta charset="UTF-8" />
    //         <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    //         <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    //         <title>Deposit Request
    //     </title>
    //       </head>
    //       <body>
    //         <div
    //           class="div"
    //           style="
    //             width: 650px;
    //             height: 100vh;
    //             margin: 0 auto;
    //           "
    //         >
    //           <div
    //             style="
    //               width: 650px;
    //               height: 200px;
    //               background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
    //               border-radius: 20px 0px  20px  0px;

    //             "
    //           >
    //             <table
    //               border="0"
    //               cellpadding="0"
    //               cellspacing="0"
    //               align="center"
    //               style="
    //                 border-collapse: collapse;
    //                 border-spacing: 0;
    //                 padding: 0;
    //                 width: 650px;
    //                 border-radius: 10px;

    //               "
    //             >
    //               <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     font-weight: bold;
    //                     font-size: 20px;
    //                     line-height: 38px;
    //                     padding-top: 30px;
    //                     padding-bottom: 10px;
    //                   "
    //                 >
    //                   <a href="https://www.flyfarint.com/"
    //                     ><img
    //                     src="https://cdn.flyfarint.com/logo.png"
    //                       width="130px"
    //                   /></a>

    //                 </td>
    //               </tr>
    //             </table>

    //             <table
    //               border="0"
    //               cellpadding="0"
    //               cellspacing="0"
    //               align="center"
    //               bgcolor="white"
    //               style="
    //                 border-collapse: collapse;
    //                 border-spacing: 0;
    //                 padding: 0;
    //                 width: 550px;
    //               "
    //             >
    //               <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     font-size: 19px;
    //                     line-height: 38px;
    //                     padding-top: 20px;
    //                     background-color: white;


    //                   "
    //                 >
    //     New Booking Issue Request
    //             </td>
    //               </tr>
    //               <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 15px;
    //                     font-size: 12px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-right: 20px;
    //                     background-color: white;

    //                   "
    //                 >
    //                 Dear Fly Far International, We Placed  new ticket issue request ' . $Route . ', ' . $Type . ' ticket by ' . $Airlines . '.
    //         </td>     </tr>



    //               <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 20px;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-top: 20px;
    //                     width: 100%;
    //                   "
    //                 >
    //                   Booking Id:
    //                   <a style="color: #003566" href="http://" target="_blank"
    //                     >' . $bookingId . '</a
    //                   >
    //                 </td>
    //               </tr>


    //                         <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 20px;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-top: 20px;
    //                     width: 100%;
    //                     background-color: white;

    //                   "
    //                 >
    //                    Destination: <span style="color: #dc143c">' . $Route . ' - ' . $From . '</span>
    //                 </td>
    //               </tr>

    //                                   <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 20px;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-top: 10px;
    //                     width: 100%;
    //                     background-color: white;

    //                   "
    //                 >
    //                    Travel Date:  <span style="color: #dc143c">' . $travelDate . '	</span>
    //                 </td>
    //               </tr>
    //                                             <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 20px;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-top: 10px;
    //                     width: 100%;
    //                     background-color: white;

    //                   "
    //                 >
    //                    Airline: <span style="color: #dc143c"> ' . $Airlines . '	</span>
    //                 </td>
    //               </tr>

    //                                                       <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 20px;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-top: 10px;
    //                     width: 100%;
    //                     background-color: white;

    //                   "
    //                 >
    //                    Pax:  <span style="color: #dc143c">' . $pax . '</span>
    //                 </td>
    //               </tr>

    //                                                                 <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 20px;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-top: 10px;
    //                     width: 100%;
    //                     background-color: white;

    //                   "
    //                 >
    //                    Cost:  <span style="color: #dc143c"> ' . $netCost . ' BDT
    //     </span>
    //                 </td>
    //               </tr>

    //                  <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 20px;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-top: 20px;
    //                     width: 100%;
    //                     background-color: white;

    //                   "
    //                 >
    //                        If you have any questions, just contact us we are always happy to
    //                   help you out.
    //                 </td>
    //               </tr>


    //                  <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 20px;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-top: 20px;
    //                     width: 100%;
    //                     background-color: white;

    //                   "
    //                 >
    //                    Sincerely,

    //                 </td>
    //               </tr>

    //                  <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     width: 100%;
    //                     background-color: white;
    //                     padding-bottom: 20px

    //                   "
    //                 >
    //                 Fly Far International
    //                 </td>
    //               </tr>
    //               <tr>
    //               <td
    //                 align="center"
    //                 valign="top"
    //                 style="
    //                   border-collapse: collapse;
    //                   border-spacing: 0;
    //                   color: #ffffff;
    //                   font-family: sans-serif;
    //                   text-align: center;
    //                   font-weight: 600;
    //                   font-size: 14px;
    //                   color: #ffffff;
    //                   padding-top: 15px;
    //                   background-color: #dc143c;
    //                 "
    //               >
    //                 Need more help?
    //               </td>
    //             </tr>

    //             <tr>
    //               <td
    //                 align="center"
    //                 valign="top"
    //                 style="
    //                   border-collapse: collapse;
    //                   border-spacing: 0;
    //                   color: #ffffff;
    //                   font-family: sans-serif;
    //                   text-align: center;
    //                   font-size: 12px;
    //                   color: #ffffff;
    //                   padding-top: 8px;
    //                   padding-bottom: 20px;
    //                   padding-left: 30px;
    //                   padding-right: 30px;
    //                   background-color: #dc143c;


    //                 "
    //               >
    //                 Mail us at
    //                 <a
    //                   style="color: white; font-size: 13px; text-decoration: none"
    //                   href="http://"
    //                   target="_blank"
    //                   >support@flyfarint.com
    //                 </a>
    //                 agency or Call us at 09606912912
    //               </td>
    //             </tr>

    //             <tr>
    //               <td
    //                 valign="top"
    //                 align="left"
    //                 style="
    //                   border-collapse: collapse;
    //                   border-spacing: 0;
    //                   color: #000000;
    //                   font-family: sans-serif;
    //                   text-align: left;
    //                   font-weight: bold;
    //                   font-size: 12px;
    //                   line-height: 18px;
    //                   color: #929090;
    //                 "
    //               >

    //               <p> <a
    //                   style="
    //                     font-weight: bold;
    //                     font-size: 12px;
    //                     line-height: 15px;
    //                     color: #222222;

    //                   "
    //                   href="https://www.flyfarint.com/terms"
    //                   >Terms & Conditions</a
    //                 >
    //                 <a
    //                   style="
    //                     font-weight: bold;
    //                     font-size: 12px;
    //                     line-height: 15px;
    //                     color: #222222;
    //                     padding-left: 10px;
    //                   "
    //                   href="https://www.flyfarint.com/privacy"
    //                   >Privacy Policy</a
    //                 ></p>
    //               </td>
    //             </tr>
    //             <tr>
    //               <td
    //                 align="center"
    //                 valign="top"
    //                 style="
    //                   border-collapse: collapse;
    //                   border-spacing: 0;
    //                   font-family: sans-serif;
    //                   text-align: center;
    //                   padding-left: 20px;
    //                   font-weight: bold;
    //                   font-size: 12px;
    //                   line-height: 18px;
    //                   color: #929090;
    //                   padding-right: 20px;
    //                 "
    //               >
    //                   <a href="https://www.facebook.com/FlyFarInternational/ "
    //                   ><img
    //                     src="https://cdn.flyfarint.com/fb.png"
    //                     width="25px"
    //                     style="margin: 10px"
    //                 /></a>
    //                 <a href="http:// "
    //                   ><img
    //                     src="https://cdn.flyfarint.com/lin.png"
    //                     width="25px"
    //                     style="margin: 10px"
    //                 /></a>
    //                 <a href="http:// "
    //                   ><img
    //                     src="https://cdn.flyfarint.com/wapp.png "
    //                     width="25px"
    //                     style="margin: 10px"
    //                 /></a>
    //               </td>
    //             </tr>

    //                       <tr>
    //               <td
    //                 align="center"
    //                 valign="top"
    //                 style="
    //                   border-collapse: collapse;
    //                   border-spacing: 0;
    //                   color: #929090;
    //                   font-family: sans-serif;
    //                   text-align: center;
    //                   font-weight: 500;
    //                   font-size: 12px;
    //                   padding-top:5px;
    //                   padding-bottom:5px;
    //                   padding-left:10px;
    //                   padding-right: 10px;
    //                 "
    //               >
    //   Ka 11/2A, Bashundhara R/A Road, Jagannathpur, Dhaka 1229
    //    </td>
    //             </tr>
    //             </table>


    //           </div>
    //         </div>
    //       </body>
    //     </html>

    //     ';

    // $mail = new PHPMailer();

    // try {
    //   $mail->isSMTP();
    //   $mail->Host = 'flyfarint.com';
    //   $mail->SMTPAuth = true;
    //   $mail->Username = 'reissue@b2b.flyfarint.com';
    //   $mail->Password = '123Next2$';
    //   $mail->SMTPSecure = 'ssl';
    //   $mail->Port = 465;

    //   //Recipients
    //   $mail->setFrom("reissue@flyfarint.com");
    //   $mail->addAddress("$agentCompanyEmail", "AgentId : $agentId");
    //   // $mail->addCC('habib@flyfarint.com');
    //   // $mail->addCC('afridi@flyfarint.com');

    //   $mail->isHTML(true);
    //   $mail->Subject = "Booking Issue Request Confirmation by $agentCompanyName";
    //   $mail->Body = $AgentMail;
    //   //print_r($mail);
    //   if (!$mail->Send()) {
    //     echo "Mailer Error: " . $mail->ErrorInfo;
    //   }
    // } catch (Exception $e) {
    // }

    // ///owner email

    // $OwnerEmail = '
    //     <!DOCTYPE html>
    //     <html lang="en">
    //       <head>
    //         <meta charset="UTF-8" />
    //         <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    //         <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    //         <title>Deposit Request
    //     </title>
    //       </head>
    //       <body>
    //         <div
    //           class="div"
    //           style="
    //             width: 650px;
    //             height: 100vh;
    //             margin: 0 auto;
    //           "
    //         >
    //           <div
    //             style="
    //               width: 650px;
    //               height: 200px;
    //               background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
    //               border-radius: 20px 0px  20px  0px;

    //             "
    //           >
    //             <table
    //               border="0"
    //               cellpadding="0"
    //               cellspacing="0"
    //               align="center"
    //               style="
    //                 border-collapse: collapse;
    //                 border-spacing: 0;
    //                 padding: 0;
    //                 width: 650px;
    //                 border-radius: 10px;

    //               "
    //             >
    //               <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     font-weight: bold;
    //                     font-size: 20px;
    //                     line-height: 38px;
    //                     padding-top: 30px;
    //                     padding-bottom: 10px;
    //                   "
    //                 >
    //                   <a href="https://www.flyfarint.com/"
    //                     ><img
    //                     src="https://cdn.flyfarint.com/logo.png"
    //                       width="130px"
    //                   /></a>

    //                 </td>
    //               </tr>
    //             </table>

    //             <table
    //               border="0"
    //               cellpadding="0"
    //               cellspacing="0"
    //               align="center"
    //               bgcolor="white"
    //               style="
    //                 border-collapse: collapse;
    //                 border-spacing: 0;
    //                 padding: 0;
    //                 width: 550px;
    //               "
    //             >
    //               <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     font-size: 19px;
    //                     line-height: 38px;
    //                     padding-top: 20px;
    //                     background-color: white;


    //                   "
    //                 >
    //     New Booking Issue Request
    //             </td>
    //               </tr>
    //               <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 15px;
    //                     font-size: 12px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-right: 20px;
    //                     background-color: white;

    //                   "
    //                 >
    //                 Dear Fly Far International, We Placed  new ticket issue request ' . $Route . ', ' . $Type . ' ticket by ' . $Airlines . '.
    //         </td>     </tr>



    //               <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 20px;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-top: 20px;
    //                     width: 100%;
    //                   "
    //                 >
    //                   Booking Id:
    //                   <a style="color: #003566" href="http://" target="_blank"
    //                     >' . $bookingId . '</a
    //                   >
    //                 </td>
    //               </tr>


    //                         <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 20px;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-top: 20px;
    //                     width: 100%;
    //                     background-color: white;

    //                   "
    //                 >
    //                    Destination: <span style="color: #dc143c">' . $Route . ' - ' . $From . '</span>
    //                 </td>
    //               </tr>

    //                                   <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 20px;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-top: 10px;
    //                     width: 100%;
    //                     background-color: white;

    //                   "
    //                 >
    //                    Travel Date:  <span style="color: #dc143c">' . $travelDate . '	</span>
    //                 </td>
    //               </tr>
    //                                             <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 20px;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-top: 10px;
    //                     width: 100%;
    //                     background-color: white;

    //                   "
    //                 >
    //                    Airline: <span style="color: #dc143c"> ' . $Airlines . '	</span>
    //                 </td>
    //               </tr>

    //                                                       <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 20px;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-top: 10px;
    //                     width: 100%;
    //                     background-color: white;

    //                   "
    //                 >
    //                    Pax:  <span style="color: #dc143c">' . $pax . '</span>
    //                 </td>
    //               </tr>

    //                                                                 <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 20px;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-top: 10px;
    //                     width: 100%;
    //                     background-color: white;

    //                   "
    //                 >
    //                    Cost:  <span style="color: #dc143c"> ' . $netCost . ' BDT
    //     </span>
    //                 </td>
    //               </tr>

    //                  <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 20px;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-top: 20px;
    //                     width: 100%;
    //                     background-color: white;

    //                   "
    //                 >
    //                        If you have any questions, just contact us we are always happy to
    //                   help you out.
    //                 </td>
    //               </tr>


    //                  <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     padding-top: 20px;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     padding-top: 20px;
    //                     width: 100%;
    //                     background-color: white;

    //                   "
    //                 >
    //                    Sincerely,

    //                 </td>
    //               </tr>

    //                  <tr>
    //                 <td
    //                   align="center"
    //                   valign="top"
    //                   style="
    //                     border-collapse: collapse;
    //                     border-spacing: 0;
    //                     color: #000000;
    //                     font-family: sans-serif;
    //                     text-align: left;
    //                     padding-left: 20px;
    //                     font-weight: bold;
    //                     font-size: 13px;
    //                     line-height: 18px;
    //                     color: #929090;
    //                     width: 100%;
    //                     background-color: white;
    //                     padding-bottom: 20px

    //                   "
    //                 >
    //                  ' . $agentCompanyName . '

    //                 </td>
    //               </tr>
    //             </table>


    //           </div>
    //         </div>
    //       </body>
    //     </html>

    //     ';
    // $mail = new PHPMailer();

    // try {
    //   $mail->isSMTP();
    //   $mail->Host = 'flyfarint.com';
    //   $mail->SMTPAuth = true;
    //   $mail->Username = 'reissue@b2b.flyfarint.com';
    //   $mail->Password = '123Next2$';
    //   $mail->SMTPSecure = 'ssl';
    //   $mail->Port = 465;

    //   //Recipients
    //   $mail->setFrom("reissue@flyfarint.com", $agentCompanyName);
    //   $mail->addAddress("otaoperation@flyfarint.com");
    //   // $mail->addCC('habib@flyfarint.com');
    //   // $mail->addCC('afridi@flyfarint.com');

    //   $mail->isHTML(true);
    //   $mail->Subject = "Booking Issue Request Confirmation by $agentCompanyName";
    //   $mail->Body = $OwnerEmail;
    //   //print_r($mail);
    //   if (!$mail->Send()) {
    //     echo "Mailer Error: " . $mail->ErrorInfo;
    //   }
    // } catch (Exception $e) {
    // }

    echo json_encode($response);
  }
}
