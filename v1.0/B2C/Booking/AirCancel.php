<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require "../../vendor/autoload.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $_POST = json_decode(file_get_contents('php://input'), true);
    $bookingId = $_POST["bookingId"];
    $platform = $_POST["platform"];
    $cancelBy = $_POST['cancelBy'];

    $createdTime = date("Y-m-d H:i:s");

    $query = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId' AND platform='$platform'");
    $data = mysqli_fetch_assoc($query);
    $userId = $data["userId"];
    $staffId = $data["staffId"];
    $status = $data["status"];
    $pnr = $data['pnr'];
    $system = $data['gds'];
    $deptFrom = $data['deptFrom'];
    $arriveTo = $data['arriveTo'];
    $airlines = $data['airlines'];
    $tripType = $data['tripType'];
    $route = "$deptFrom - $arriveTo";

    // //Mail Data
    // $result1 = $conn->query("SELECT * FROM agent where userId='$userId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);

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

    // $result = $conn->query("SELECT * FROM subagent where userId='$userId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
    // $subAgentEmail = $result[0]['email'];
    // $subAgentCompanyName = $result[0]['company'];

    if ($status == 'Hold' || $status == 'Issue In Processing') {

        $DateTime = date("D d M Y h:i A");

        $queryAgent = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
        $dataAgent = mysqli_fetch_assoc($query);
        $companyname = $data["userId"];

        $staffdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `staffList` where staffId='$staffId' AND userId='$userId'"));

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
                    'Content-Type: application/json',
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
                    "Authorization: Bearer $FlyhubToken",
                ),
            ));

            $FlyHubresponse = curl_exec($curl);

            curl_close($curl);

            $sql = "UPDATE `booking` SET `status`='Cancelled' where bookingId='$bookingId'";

            if (isset($agentId)) {
                $sql1 = mysqli_query($conn, "SELECT * FROM agent WHERE userId='$userId'");
                $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                if (!empty($row1)) {
                    $agentEmail = $row1['email'];
                    $companyname = $row1['company'];
                }

            }

            if ($conn->query($sql) === true) {
                $conn->query("INSERT INTO `activitylog`(`ref`,`userId`,`status`,`remarks`,`platform`,`actionBy`, `actionAt`)
                             VALUES ('$bookingId','$userId','Cancalled',' ','$platform','$cancelBy','$createdTime')");

                //agent Mail sending
              //   $AgentMail = '
              //   <!DOCTYPE html>
              //   <html lang="en">
              //     <head>
              //       <meta charset="UTF-8" />
              //       <meta http-equiv="X-UA-Compatible" content="IE=edge" />
              //       <meta name="viewport" content="width=device-width, initial-scale=1.0" />
              //       <title>Deposit Request</title>
              //     </head>
              //     <body>
              //       <div class="div" style="width: 650px; height: 100vh; margin: 0 auto">
              //         <div
              //           style="
              //             width: 650px;
              //             height: 150px;
              //             background: #FFA84D;
              //           "
              //         >
              //         <table
              //         border="0"
              //         cellpadding="0"
              //         cellspacing="0"
              //         align="center"
              //         style="
              //           border-collapse: collapse;
              //           border-spacing: 0;
              //           padding: 0;
              //           width: 650px;
              //           border-radius: 10px;
              //         "
              //       >
              //         <tr>
              //           <td
              //             align="center"
              //             valign="top"
              //             style="
              //               border-collapse: collapse;
              //               border-spacing: 0;
              //               color: #000000;
              //               font-family: sans-serif;
              //               font-weight: bold;
              //               font-size: 20px;
              //               line-height: 38px;
              //               padding-top: 20px;
              //               padding-bottom: 10px;

              //             "
              //           >
              //             <a style="text-decoration: none; color:#2564B8; font-family: sans-serif; font-size: 25px; padding-top: 20px;"  href="https://www.' . $agentCompanyWebsiteLink . '">' . $agentCompanyName . '</a>

              //           </td>
              //         </tr>
              //       </table>

              //           <table
              //             border="0"
              //             cellpadding="0"
              //             cellspacing="0"
              //             align="center"
              //             bgcolor="white"
              //             style="
              //               border-collapse: collapse;
              //               border-spacing: 0;
              //               padding: 0;
              //               width: 550px;
              //             "
              //           >
              //             <tr>
              //               <td
              //                 align="center"
              //                 valign="top"
              //                 style="
              //                   border-collapse: collapse;
              //                   border-spacing: 0;
              //                   color: #000000;
              //                   font-family: sans-serif;
              //                   text-align: left;
              //                   padding-left: 20px;
              //                   font-weight: bold;
              //                   font-size: 19px;
              //                   line-height: 38px;
              //                   padding-top: 10px;
              //                   background-color: white;
              //                 "
              //               >
              //               Booking Cancelled
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
              //                   text-align: left;
              //                   padding-left: 20px;
              //                   font-weight: bold;
              //                   padding-top: 15px;
              //                   font-size: 12px;
              //                   line-height: 18px;
              //                   color: #929090;
              //                   padding-right: 20px;
              //                   background-color: white;
              //                 "
              //               >
              //                 Dear ' . $subAgentCompanyName . ', Your Booking request has been Canaled.
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
              //                   text-align: left;
              //                   padding-left: 20px;
              //                   font-weight: bold;
              //                   padding-top: 5px;
              //                   font-size: 12px;
              //                   line-height: 18px;
              //                   color: #2564B8;
              //                   padding-right: 20px;
              //                   background-color: white;
              //                 "
              //               >
              //                 Booking ID: <span>' . $bookingId . '</span>
              //             </tr>




              //             <tr>
              //               <td
              //                 align="center"
              //                 valign="top"
              //                 style="
              //                   border-collapse: collapse;
              //                   border-spacing: 0;
              //                   color: #000000;
              //                   font-family: sans-serif;
              //                   text-align: left;
              //                   padding-left: 20px;
              //                   font-weight: bold;
              //                   padding-top: 20px;
              //                   font-size: 12px;
              //                   line-height: 18px;
              //                   color: #929090;
              //                   padding-top: 20px;
              //                   width: 100%;
              //                   background-color: white;
              //                 "
              //               >
              //                 If you have any questions, just contact us we are always happy to
              //                 help you out.
              //               </td>
              //             </tr>

              //             <tr>
              //               <td
              //                 align="center"
              //                 valign="top"
              //                 style="
              //                   border-collapse: collapse;
              //                   border-spacing: 0;
              //                   color: #000000;
              //                   font-family: sans-serif;
              //                   text-align: left;
              //                   padding-left: 20px;
              //                   font-weight: bold;
              //                   padding-top: 20px;
              //                   font-size: 12px;
              //                   line-height: 18px;
              //                   color: #929090;
              //                   padding-top: 20px;
              //                   width: 100%;
              //                   background-color: white;
              //                 "
              //               >
              //                 Sincerely,
              //               </td>
              //             </tr>

              //             <tr>
              //               <td
              //                 align="center"
              //                 valign="top"
              //                 style="
              //                   border-collapse: collapse;
              //                   border-spacing: 0;
              //                   color: #000000;
              //                   font-family: sans-serif;
              //                   text-align: left;
              //                   padding-left: 20px;
              //                   font-weight: bold;
              //                   font-size: 12px;
              //                   line-height: 18px;
              //                   color: #929090;
              //                   width: 100%;
              //                   background-color: white;
              //                   padding-bottom: 20px;
              //                 "
              //               >
              //               ' . $agentCompanyName . '
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
              //                   font-weight: 600;
              //                   font-size: 14px;
              //                   color: #ffffff;
              //                   padding-top: 15px;
              //                   background-color: #2564B8;
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
              //                   background-color: #2564B8;
              //                 "
              //               >
              //                 Mail us at
              //                 <a
              //                   style="color: white; font-size: 13px; text-decoration: none"
              //                   href="http://"
              //                   target="_blank"
              //                   >' . $agentCompanyEmail . '
              //                 </a>
              //                 agency or Call us at ' . $agentCompanyPhone . '
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
              //                 <p>
              //                   <a
              //                     style="
              //                       font-weight: bold;
              //                       font-size: 12px;
              //                       line-height: 15px;
              //                       color: #222222;
              //                     "
              //                     href="https://www.' . $agentCompanyWebsiteLink . '/termsandcondition"
              //                         >Tearms & Conditions</a
              //                   >
              //                   <a
              //                     style="
              //                       font-weight: bold;
              //                       font-size: 12px;
              //                       line-height: 15px;
              //                       color: #222222;
              //                       padding-left: 10px;
              //                     "
              //                     href="https://www.' . $agentCompanyWebsiteLink . '/privacypolicy"
              //                         >Privacy Policy</a
              //                       >
              //                 </p>
              //               </td>
              //             </tr>
              //             <tr>
              //                   <td
              //                       align="center"
              //                       valign="top"
              //                       style="
              //                       border-collapse: collapse;
              //                       border-spacing: 0;
              //                       font-family: sans-serif;
              //                       text-align: center;
              //                       padding-left: 20px;
              //                       font-weight: bold;
              //                       font-size: 12px;
              //                       line-height: 18px;
              //                       color: #929090;
              //                       padding-right: 20px;
              //                       "
              //                   >
              //                       <a href="' . $agentCompanyFbLink . ' "
              //                       ><img
              //                         src="https://cdn.flyfarint.com/fb.png"
              //                         width="25px"
              //                         style="margin: 10px"
              //                       /></a>
              //                       <a href="' . $agentCompanyLinkedinLink . ' "
              //                       ><img
              //                         src="https://cdn.flyfarint.com/lin.png"
              //                         width="25px"
              //                         style="margin: 10px"
              //                       /></a>
              //                       <a href="' . $agentCompanyWhatsappNum . ' "
              //                       ><img
              //                         src="https://cdn.flyfarint.com/wapp.png "
              //                         width="25px"
              //                         style="margin: 10px"
              //                       /></a>
              //                   </td>
              //                 </tr>

              //             <tr>
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
              //                   padding-top: 5px;
              //                   padding-bottom: 5px;
              //                   padding-left: 10px;
              //                   padding-right: 10px;
              //                 "
              //               >
              //                 ' . $agentCompanyAddress . '
              //               </td>
              //             </tr>
              //           </table>
              //         </div>
              //       </div>
              //     </body>
              //   </html>
              // ';

                // $mail = new PHPMailer();

                // try {
                //     $mail->isSMTP();
                //     $mail->Host = 'flyfarint.com';
                //     $mail->SMTPAuth = true;
                //     $mail->Username = 'bookingwl@mailservice.center';
                //     $mail->Password = '123Next2$';
                //     $mail->SMTPSecure = 'ssl';
                //     $mail->Port = 465;

                //     //Recipients
                //     $mail->setFrom('bookingwl@mailservice.center', $agentCompanyName);
                //     $mail->addAddress("$subAgentEmail", "AgentId : $agentId");
                   
                //     // $mail->addCC('habib@flyfarint.com');
                //     // $mail->addCC('afridi@flyfarint.com');

                //     $mail->isHTML(true);
                //     $mail->Subject = "Booking Cancel by $agentCompanyName";
                //     $mail->Body = $AgentMail;

                //     if (!$mail->Send()) {
                //         echo "Mailer Error: " . $mail->ErrorInfo;
                //     }

                // } catch (Exception $e) {

                // }


                 ///owner email

        // $OwnerEmailFromAgent = '
        // <!DOCTYPE html>
        // <html lang="en">
        //   <head>
        //     <meta charset="UTF-8" />
        //     <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        //     <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        //     <title>Deposit Request
        // </title>
        //   </head>
        //   <body>
        //     <div
        //       class="div"
        //       style="
        //         width: 650px;
        //         height: 100vh;
        //         margin: 0 auto;
        //       "
        //     >
        //       <div
        //         style="
        //           width: 650px;
        //           height: 200px;
        //           background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
        //           border-radius: 20px 0px  20px  0px;

        //         "
        //       >
        //         <table
        //           border="0"
        //           cellpadding="0"
        //           cellspacing="0"
        //           align="center"
        //           style="
        //             border-collapse: collapse;
        //             border-spacing: 0;
        //             padding: 0;
        //             width: 650px;
        //             border-radius: 10px;

        //           "
        //         >
        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 font-weight: bold;
        //                 font-size: 20px;
        //                 line-height: 38px;
        //                 padding-top: 30px;
        //                 padding-bottom: 10px;
        //               "
        //             >
        //               <a href="https://www.flyfarint.com/"
        //                 ><img
        //                 src="https://cdn.flyfarint.com/logo.png"
        //                   width="130px"
        //               /></a>

        //             </td>
        //           </tr>
        //         </table>

        //         <table
        //           border="0"
        //           cellpadding="0"
        //           cellspacing="0"
        //           align="center"
        //           bgcolor="white"
        //           style="
        //             border-collapse: collapse;
        //             border-spacing: 0;
        //             padding: 0;
        //             width: 550px;
        //           "
        //         >
        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 font-size: 19px;
        //                 line-height: 38px;
        //                 padding-top: 20px;
        //                 background-color: white;


        //               "
        //             >
        //             Booking Cancel
        //         </td>
        //           </tr>
        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 padding-top: 15px;
        //                 font-size: 12px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-right: 20px;
        //                 background-color: white;

        //               "
        //             >
        //             Dear Fly Far International, Our ' .$route . ', ' . $tripType . ' ticket by ' . $airlines . ' booking request has been Cancelled.
        //     </td>     </tr>
                  
        //     <tr>
        //     <td
        //       align="center"
        //       valign="top"
        //       style="
        //         border-collapse: collapse;
        //         border-spacing: 0;
        //         color: #000000;
        //         font-family: sans-serif;
        //         text-align: left;
        //         padding-left: 20px;
        //         font-weight: bold;
        //         padding-top: 20px;
        //         font-size: 13px;
        //         line-height: 18px;
        //         color: #929090;
        //         padding-top: 20px;
        //         width: 100%;
        //         background-color: white;

        //       "
        //     >
        //     Cancelled By: <span style="color: #dc143c">' .$agentCompanyName. '</span>
        //     </td>
        //   </tr>

        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 padding-top: 20px;
        //                 font-size: 13px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-top: 20px;
        //                 width: 100%;
        //               "
        //             >
        //               Booking Id:
        //               <a style="color: #003566" href="http://" target="_blank"
        //                 >' . $bookingId . '</a
        //               >
        //             </td>
        //           </tr>
    
        //          <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 padding-top: 20px;
        //                 font-size: 13px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-top: 20px;
        //                 width: 100%;
        //                 background-color: white;

        //               "
        //             >
        //                    If you have any questions, just contact us we are always happy to
        //               help you out.
        //             </td>
        //           </tr>


        //              <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 padding-top: 20px;
        //                 font-size: 13px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-top: 20px;
        //                 width: 100%;
        //                 background-color: white;

        //               "
        //             >
        //                Sincerely,

        //             </td>
        //           </tr>

        //              <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 font-size: 13px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 width: 100%;
        //                 background-color: white;
        //                 padding-bottom: 20px

        //               "
        //             >
        //              ' . $agentCompanyName . '

        //             </td>
        //           </tr>
        //         </table>


        //       </div>
        //     </div>
        //   </body>
        // </html>

        // ';
        // $mail = new PHPMailer();

        // try {
        //     $mail->isSMTP();
        //     $mail->Host = 'flyfarint.com';
        //     $mail->SMTPAuth = true;
        //     $mail->Username = 'reissue@b2b.flyfarint.com';
        //     $mail->Password = '123Next2$';
        //     $mail->SMTPSecure = 'ssl'; 
        //     $mail->Port = 465;

        //     //Recipients
        //     $mail->setFrom("reissue@flyfarint.com", "Fly Far International");
        //     $mail->addAddress("otaoperation@flyfarint.com", "AgentId : $agentId");
        //     $mail->addCC('habib@flyfarint.com');
        //     $mail->addCC('afridi@flyfarint.com');

        //     $mail->isHTML(true);
        //     $mail->Subject = " New Booking Cancelled by $agentCompanyName";
        //     $mail->Body = $OwnerEmailFromAgent;
    
        //     if (!$mail->Send()) {
        //         echo "Mailer Error: " . $mail->ErrorInfo;
        //     }

        // } catch (Exception $e) {

        // }

        // Owner Agent Email
        // $AgentEmailFromOwner= '
        // <!DOCTYPE html>
        // <html lang="en">
        //   <head>
        //     <meta charset="UTF-8" />
        //     <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        //     <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        //     <title>Deposit Request
        // </title>
        //   </head>
        //   <body>
        //     <div
        //       class="div"
        //       style="
        //         width: 650px;
        //         height: 100vh;
        //         margin: 0 auto;
        //       "
        //     >
        //       <div
        //         style="
        //           width: 650px;
        //           height: 200px;
        //           background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
        //           border-radius: 20px 0px  20px  0px;

        //         "
        //       >
        //         <table
        //           border="0"
        //           cellpadding="0"
        //           cellspacing="0"
        //           align="center"
        //           style="
        //             border-collapse: collapse;
        //             border-spacing: 0;
        //             padding: 0;
        //             width: 650px;
        //             border-radius: 10px;

        //           "
        //         >
        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 font-weight: bold;
        //                 font-size: 20px;
        //                 line-height: 38px;
        //                 padding-top: 30px;
        //                 padding-bottom: 10px;
        //               "
        //             >
        //               <a href="https://www.flyfarint.com/"
        //                 ><img
        //                 src="https://cdn.flyfarint.com/logo.png"
        //                   width="130px"
        //               /></a>

        //             </td>
        //           </tr>
        //         </table>

        //         <table
        //           border="0"
        //           cellpadding="0"
        //           cellspacing="0"
        //           align="center"
        //           bgcolor="white"
        //           style="
        //             border-collapse: collapse;
        //             border-spacing: 0;
        //             padding: 0;
        //             width: 550px;
        //           "
        //         >
        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 font-size: 19px;
        //                 line-height: 38px;
        //                 padding-top: 20px;
        //                 background-color: white;


        //               "
        //             >
        //             New Booking Cancelled
        //         </td>
        //           </tr>
        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 padding-top: 15px;
        //                 font-size: 12px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-right: 20px;
        //                 background-color: white;

        //               "
        //             >
        //             Dear ' . $agentCompanyName . ', Your ' . $route . ', ' . $tripType . ' Ticket Booking on ' . $airlines  . ' Airlines new booking Request has been Cancelled.
        //     </td>     </tr>

        //     <tr>
        //     <td
        //       align="center"
        //       valign="top"
        //       style="
        //         border-collapse: collapse;
        //         border-spacing: 0;
        //         color: #000000;
        //         font-family: sans-serif;
        //         text-align: left;
        //         padding-left: 20px;
        //         font-weight: bold;
        //         padding-top: 20px;
        //         font-size: 13px;
        //         line-height: 18px;
        //         color: #929090;
        //         padding-top: 20px;
        //         width: 100%;
        //         background-color: white;

        //       "
        //     >
        //     Cancelled By: <span style="color: #dc143c">' . $agentCompanyName.'</span>
        //     </td>
        //   </tr>

        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 padding-top: 20px;
        //                 font-size: 13px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-top: 20px;
        //                 width: 100%;
        //               "
        //             >
        //               Booking Id:
        //               <a style="color: #003566" href="http://" target="_blank"
        //                 >' . $bookingId . '</a
        //               >
        //             </td>
        //           </tr>

        //              <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 padding-top: 20px;
        //                 font-size: 13px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-top: 20px;
        //                 width: 100%;
        //                 background-color: white;

        //               "
        //             >
        //                    If you have any questions, just contact us we are always happy to
        //               help you out.
        //             </td>
        //           </tr>


        //              <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 padding-top: 20px;
        //                 font-size: 13px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-top: 20px;
        //                 width: 100%;
        //                 background-color: white;

        //               "
        //             >
        //                Sincerely,

        //             </td>
        //           </tr>

        //              <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 font-size: 13px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 width: 100%;
        //                 background-color: white;
        //                 padding-bottom: 20px

        //               "
        //             >

        //             Fly Far International
        //             </td>
        //           </tr>


        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #ffffff;
        //                 font-family: sans-serif;
        //                 text-align: center;
        //                 font-weight: 600;
        //                 font-size: 14px;
        //                 color: #ffffff;
        //                 padding-top: 15px;
        //                 background-color: #dc143c;
        //               "
        //             >
        //               Need more help?
        //             </td>
        //           </tr>

        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #ffffff;
        //                 font-family: sans-serif;
        //                 text-align: center;
        //                 font-size: 12px;
        //                 color: #ffffff;
        //                 padding-top: 8px;
        //                 padding-bottom: 20px;
        //                 padding-left: 30px;
        //                 padding-right: 30px;
        //                 background-color: #dc143c;


        //               "
        //             >
        //               Mail us at
        //               <a
        //                 style="color: white; font-size: 13px; text-decoration: none"
        //                 href="http://"
        //                 target="_blank"
        //                 >support@flyfarint.com
        //               </a>
        //               agency or Call us at 09606912912
        //             </td>
        //           </tr>

        //           <tr>
        //             <td
        //               valign="top"
        //               align="left"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 font-weight: bold;
        //                 font-size: 12px;
        //                 line-height: 18px;
        //                 color: #929090;
        //               "
        //             >

        //             <p> <a
        //                 style="
        //                   font-weight: bold;
        //                   font-size: 12px;
        //                   line-height: 15px;
        //                   color: #222222;

        //                 "
        //                 href="https://www.flyfarint.com/terms"
        //                 >Terms & Conditions</a
        //               >
        //               <a
        //                 style="
        //                   font-weight: bold;
        //                   font-size: 12px;
        //                   line-height: 15px;
        //                   color: #222222;
        //                   padding-left: 10px;
        //                 "
        //                 href="https://www.flyfarint.com/privacy"
        //                 >Privacy Policy</a
        //               ></p>
        //             </td>
        //           </tr>
        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 font-family: sans-serif;
        //                 text-align: center;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 font-size: 12px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-right: 20px;
        //               "
        //             >
        //                 <a href="https://www.facebook.com/FlyFarInternational/ "
        //                 ><img
        //                   src="https://cdn.flyfarint.com/fb.png"
        //                   width="25px"
        //                   style="margin: 10px"
        //               /></a>
        //               <a href="http:// "
        //                 ><img
        //                   src="https://cdn.flyfarint.com/lin.png"
        //                   width="25px"
        //                   style="margin: 10px"
        //               /></a>
        //               <a href="http:// "
        //                 ><img
        //                   src="https://cdn.flyfarint.com/wapp.png "
        //                   width="25px"
        //                   style="margin: 10px"
        //               /></a>
        //             </td>
        //           </tr>

        //                     <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #929090;
        //                 font-family: sans-serif;
        //                 text-align: center;
        //                 font-weight: 500;
        //                 font-size: 12px;
        //                 padding-top:5px;
        //                 padding-bottom:5px;
        //                 padding-left:10px;
        //                 padding-right: 10px;
        //               "
        //             >
        // Ka 11/2A, Bashundhora R/A Road, Jagannathpur, Dhaka 1229
        //  </td>
        //           </tr>



        //         </table>


        //       </div>
        //     </div>
        //   </body>
        // </html>


        // ';

        // $mail3 = new PHPMailer();

        // try {
        //     $mail3->isSMTP();
        //     $mail3->Host = 'flyfarint.com';
        //     $mail3->SMTPAuth = true;
        //     $mail3->Username = 'reissue@b2b.flyfarint.com';
        //     $mail3->Password = '123Next2$';
        //     $mail3->SMTPSecure = 'ssl';
        //     $mail3->Port = 465;

        //     //Recipients
        //     $mail3->setFrom("reissue@flyfarint.com", "Fly Far International");
        //     $mail3->addAddress("$agentCompanyEmail", "AgentId : $agentId");
        //     $mail3->addCC('habib@flyfarint.com');
        //     $mail3->addCC('afridi@flyfarint.com');

        //     $mail3->isHTML(true);
        //     $mail3->Subject = "Booking Cancelled by Fly Far International";
        //     $mail3->Body = $AgentEmailFromOwner;
        //     //print_r($mail);
        //     if (!$mail3->Send()) {
        //         echo "Mailer Error: " . $mail3->ErrorInfo;
        //     }

        // } catch (Exception $e) { 

        // }


                // $SubAgentMail = '
                // <!DOCTYPE html>
                // <html lang="en">
                //   <head>
                //     <meta charset="UTF-8" />
                //     <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                //     <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                //     <title>Deposit Request
                // </title>
                //   </head>
                //   <body>
                //     <div
                //       class="div"
                //       style="
                //         width: 650px;
                //         height: 100vh;
                //         margin: 0 auto;
                //       "
                //     >
                //       <div
                //         style="
                //           width: 650px;
                //           height: 150px;
                //           background:#FFA84D;
                //         "
                //       >
                //       <table
                //       border="0"
                //       cellpadding="0"
                //       cellspacing="0"
                //       align="center"
                //       style="
                //         border-collapse: collapse;
                //         border-spacing: 0;
                //         padding: 0;
                //         width: 650px;
                //         border-radius: 10px;
                //       "
                //     >
                //       <tr>
                //         <td
                //           align="center"
                //           valign="top"
                //           style="
                //             border-collapse: collapse;
                //             border-spacing: 0;
                //             color: #000000;
                //             font-family: sans-serif;
                //             font-weight: bold;
                //             font-size: 20px;
                //             line-height: 38px;
                //             padding-top: 20px;
                //             padding-bottom: 10px;

                //           "
                //         >
                //         <a style="text-decoration: none; color:#2564B8; font-family: sans-serif; font-size: 25px; padding-top: 20px;" href="https://www.' . $agentCompanyWebsiteLink . '">' . $agentCompanyName . '</a>

                //         </td>
                //       </tr>
                //     </table>

                //         <table
                //           border="0"
                //           cellpadding="0"
                //           cellspacing="0"
                //           align="center"
                //           bgcolor="white"
                //           style="
                //             border-collapse: collapse;
                //             border-spacing: 0;
                //             padding: 0;
                //             width: 550px;
                //           "
                //         >
                //           <tr>
                //             <td
                //               align="center"
                //               valign="top"
                //               style="
                //                 border-collapse: collapse;
                //                 border-spacing: 0;
                //                 color: #000000;
                //                 font-family: sans-serif;
                //                 text-align: left;
                //                 padding-left: 20px;
                //                 font-weight: bold;
                //                 font-size: 19px;
                //                 line-height: 38px;
                //                 padding-top: 15px;
                //                 background-color: white;



                //               "
                //             >
                //             Booking Cancelled
                //         </td>
                //           </tr>
                //           <tr>
                //             <td
                //               align="center"
                //               valign="top"
                //               style="
                //                 border-collapse: collapse;
                //                 border-spacing: 0;
                //                 font-family: sans-serif;
                //                 text-align: left;
                //                 padding-left: 20px;
                //                 font-weight: bold;
                //                 padding-top: 10px;
                //                 font-size: 12px;
                //                 line-height: 18px;
                //                 color: #929090;
                //                 padding-right: 20px;
                //                 background-color: white;

                //               "
                //             >
                //             Dear ' . $agentCompanyName . ' , Our Booking request has been Canaled.
                //           </td>
                //           </tr>

                //           <tr>
                //             <td
                //               align="center"
                //               valign="top"
                //               style="
                //                 border-collapse: collapse;
                //                 border-spacing: 0;
                //                 color: #000000;
                //                 font-family: sans-serif;
                //                 text-align: left;
                //                 padding-left: 20px;
                //                 font-weight: bold;
                //                 padding-top: 20px;
                //                 font-size: 13px;
                //                 line-height: 18px;
                //                 color: #929090;
                //                 padding-top: 20px;
                //                 width: 100%;
                //               "
                //             >
                //             Booking Id:
                //               <a style="color: #2564B8" href="http://" target="_blank"
                //                 >' . $bookingId . '</a
                //               >
                //             </td>
                //           </tr>



                //              <tr>
                //             <td
                //               align="center"
                //               valign="top"
                //               style="
                //                 border-collapse: collapse;
                //                 border-spacing: 0;
                //                 color: #000000;
                //                 font-family: sans-serif;
                //                 text-align: left;
                //                 padding-left: 20px;
                //                 font-weight: bold;
                //                 padding-top: 20px;
                //                 font-size: 12px;
                //                 line-height: 18px;
                //                 color: #929090;
                //                 padding-top: 20px;
                //                 width: 100%;
                //                 background-color: white;

                //               "
                //             >
                //                Sincerely,

                //             </td>
                //           </tr>

                //              <tr>
                //             <td
                //               align="center"
                //               valign="top"
                //               style="
                //                 border-collapse: collapse;
                //                 border-spacing: 0;
                //                 color: #000000;
                //                 font-family: sans-serif;
                //                 text-align: left;
                //                 padding-left: 20px;
                //                 font-weight: bold;
                //                 font-size: 12px;
                //                 line-height: 18px;
                //                 color: #929090;
                //                 width: 100%;
                //                 background-color: white;
                //                 padding-bottom: 20px

                //               "
                //             >
                //               ' . $subAgentCompanyName . '

                //             </td>
                //           </tr>
                //         </table>


                //       </div>
                //     </div>
                //   </body>
                // </html>
                // ';

                // $mail = new PHPMailer();

                // try {
                //     $mail->isSMTP();
                //     $mail->Host = 'flyfarint.com';
                //     $mail->SMTPAuth = true;
                //     $mail->Username = 'reissue@b2b.flyfarint.com';
                //     $mail->Password = '123Next2$';
                //     $mail->SMTPSecure = 'ssl';
                //     $mail->Port = 465;

                //     //Recipients
                //     $mail->setFrom("reissue@flyfarint.com", $agentCompanyName);
                //     $mail->addAddress("$subAgentEmail", "AgentId : $agentId");
                //     // $mail->addCC('habib@flyfarint.com');
                //     // $mail->addCC('afridi@flyfarint.com');

                //     $mail->isHTML(true);
                //     $mail->Subject = "New Booking Cancel by $subAgentCompanyName";
                //     $mail->Body = $SubAgentMail;


                //     if (!$mail->Send()) {
                //         $response['status'] = "success";
                //         $response['BookingId'] = "$bookingId";
                //         $response['message'] = "Booking Cancelled Successfully";
                //         $response['error'] = "Email Not Send Successfully";
                //     } else {
                //         $response['status'] = "success";
                //         $response['BookingId'] = "$bookingId";
                //         $response['message'] = "Booking Cancelled Successfully";
                //     }

                // } catch (Exception $e) {
                //     $response['status'] = "error";
                //     $response['message'] = "Mail Doesn't Send";
                // }

                // echo json_encode($response);

            }

        } else if ($system == "Sabre") {
            try {

                $client_id= base64_encode("V1:351640:27YK:AA");
                $client_secret = base64_encode("spt5164");

                $token = base64_encode($client_id . ":" . $client_secret);
                $data = 'grant_type=client_credentials';

                $headers = array(
                    'Authorization: Basic ' . $token,
                    'Accept: /',
                    'Content-Type: application/x-www-form-urlencoded',
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
                    "Authorization: Bearer $access_token",
                ),
            ));

            $SabreResponse = curl_exec($curl);

            curl_close($curl);
            $sql = "UPDATE `booking` SET `status`='Cancelled' where bookingId='$bookingId'";

            if (isset($agentId)) { 
                $sql1 = mysqli_query($conn, "SELECT * FROM agent WHERE userId='$userId'");
                $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                if (!empty($row1)) {
                    $agentEmail = $row1['email'];
                    $companyname = $row1['company'];
                }

            }

            if ($conn->query($sql) === true) {
                $conn->query("INSERT INTO `activitylog`(`ref`,`userId`,`status`,`remarks`,`platform`,`actionBy`, `actionAt`)
                             VALUES ('$bookingId','$userId','Cancelled',' ','$platform','$cancelBy','$createdTime')");

                     //agent Mail sending
              //   $AgentMail = '
              //   <!DOCTYPE html>
              //   <html lang="en">
              //     <head>
              //       <meta charset="UTF-8" />
              //       <meta http-equiv="X-UA-Compatible" content="IE=edge" />
              //       <meta name="viewport" content="width=device-width, initial-scale=1.0" />
              //       <title>Deposit Request</title>
              //     </head>
              //     <body>
              //       <div class="div" style="width: 650px; height: 100vh; margin: 0 auto">
              //         <div
              //           style="
              //             width: 650px;
              //             height: 150px;
              //             background: #FFA84D;
              //           "
              //         >
              //         <table
              //         border="0"
              //         cellpadding="0"
              //         cellspacing="0"
              //         align="center"
              //         style="
              //           border-collapse: collapse;
              //           border-spacing: 0;
              //           padding: 0;
              //           width: 650px;
              //           border-radius: 10px;
              //         "
              //       >
              //         <tr>
              //           <td
              //             align="center"
              //             valign="top"
              //             style="
              //               border-collapse: collapse;
              //               border-spacing: 0;
              //               color: #000000;
              //               font-family: sans-serif;
              //               font-weight: bold;
              //               font-size: 20px;
              //               line-height: 38px;
              //               padding-top: 20px;
              //               padding-bottom: 10px;

              //             "
              //           >
              //             <a style="text-decoration: none; color:#2564B8; font-family: sans-serif; font-size: 25px; padding-top: 20px;"  href="https://www.' . $agentCompanyWebsiteLink . '">' . $agentCompanyName . '</a>

              //           </td>
              //         </tr>
              //       </table>

              //           <table
              //             border="0"
              //             cellpadding="0"
              //             cellspacing="0"
              //             align="center"
              //             bgcolor="white"
              //             style="
              //               border-collapse: collapse;
              //               border-spacing: 0;
              //               padding: 0;
              //               width: 550px;
              //             "
              //           >
              //             <tr>
              //               <td
              //                 align="center"
              //                 valign="top"
              //                 style="
              //                   border-collapse: collapse;
              //                   border-spacing: 0;
              //                   color: #000000;
              //                   font-family: sans-serif;
              //                   text-align: left;
              //                   padding-left: 20px;
              //                   font-weight: bold;
              //                   font-size: 19px;
              //                   line-height: 38px;
              //                   padding-top: 10px;
              //                   background-color: white;
              //                 "
              //               >
              //               Booking Cancelled
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
              //                   text-align: left;
              //                   padding-left: 20px;
              //                   font-weight: bold;
              //                   padding-top: 15px;
              //                   font-size: 12px;
              //                   line-height: 18px;
              //                   color: #929090;
              //                   padding-right: 20px;
              //                   background-color: white;
              //                 "
              //               >
              //                 Dear ' . $subAgentCompanyName . ', Your Booking request has been Canaled.
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
              //                   text-align: left;
              //                   padding-left: 20px;
              //                   font-weight: bold;
              //                   padding-top: 5px;
              //                   font-size: 12px;
              //                   line-height: 18px;
              //                   color: #2564B8;
              //                   padding-right: 20px;
              //                   background-color: white;
              //                 "
              //               >
              //                 Booking ID: <span>' . $bookingId . '</span>
              //             </tr>




              //             <tr>
              //               <td
              //                 align="center"
              //                 valign="top"
              //                 style="
              //                   border-collapse: collapse;
              //                   border-spacing: 0;
              //                   color: #000000;
              //                   font-family: sans-serif;
              //                   text-align: left;
              //                   padding-left: 20px;
              //                   font-weight: bold;
              //                   padding-top: 20px;
              //                   font-size: 12px;
              //                   line-height: 18px;
              //                   color: #929090;
              //                   padding-top: 20px;
              //                   width: 100%;
              //                   background-color: white;
              //                 "
              //               >
              //                 If you have any questions, just contact us we are always happy to
              //                 help you out.
              //               </td>
              //             </tr>

              //             <tr>
              //               <td
              //                 align="center"
              //                 valign="top"
              //                 style="
              //                   border-collapse: collapse;
              //                   border-spacing: 0;
              //                   color: #000000;
              //                   font-family: sans-serif;
              //                   text-align: left;
              //                   padding-left: 20px;
              //                   font-weight: bold;
              //                   padding-top: 20px;
              //                   font-size: 12px;
              //                   line-height: 18px;
              //                   color: #929090;
              //                   padding-top: 20px;
              //                   width: 100%;
              //                   background-color: white;
              //                 "
              //               >
              //                 Sincerely,
              //               </td>
              //             </tr>

              //             <tr>
              //               <td
              //                 align="center"
              //                 valign="top"
              //                 style="
              //                   border-collapse: collapse;
              //                   border-spacing: 0;
              //                   color: #000000;
              //                   font-family: sans-serif;
              //                   text-align: left;
              //                   padding-left: 20px;
              //                   font-weight: bold;
              //                   font-size: 12px;
              //                   line-height: 18px;
              //                   color: #929090;
              //                   width: 100%;
              //                   background-color: white;
              //                   padding-bottom: 20px;
              //                 "
              //               >
              //               ' . $agentCompanyName . '
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
              //                   font-weight: 600;
              //                   font-size: 14px;
              //                   color: #ffffff;
              //                   padding-top: 15px;
              //                   background-color: #2564B8;
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
              //                   background-color: #2564B8;
              //                 "
              //               >
              //                 Mail us at
              //                 <a
              //                   style="color: white; font-size: 13px; text-decoration: none"
              //                   href="http://"
              //                   target="_blank"
              //                   >' . $agentCompanyEmail . '
              //                 </a>
              //                 agency or Call us at ' . $agentCompanyPhone . '
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
              //                 <p>
              //                   <a
              //                     style="
              //                       font-weight: bold;
              //                       font-size: 12px;
              //                       line-height: 15px;
              //                       color: #222222;
              //                     "
              //                     href="https://www.' . $agentCompanyWebsiteLink . '/termsandcondition"
              //                         >Tearms & Conditions</a
              //                   >
              //                   <a
              //                     style="
              //                       font-weight: bold;
              //                       font-size: 12px;
              //                       line-height: 15px;
              //                       color: #222222;
              //                       padding-left: 10px;
              //                     "
              //                     href="https://www.' . $agentCompanyWebsiteLink . '/privacypolicy"
              //                         >Privacy Policy</a
              //                       >
              //                 </p>
              //               </td>
              //             </tr>
              //             <tr>
              //                   <td
              //                       align="center"
              //                       valign="top"
              //                       style="
              //                       border-collapse: collapse;
              //                       border-spacing: 0;
              //                       font-family: sans-serif;
              //                       text-align: center;
              //                       padding-left: 20px;
              //                       font-weight: bold;
              //                       font-size: 12px;
              //                       line-height: 18px;
              //                       color: #929090;
              //                       padding-right: 20px;
              //                       "
              //                   >
              //                       <a href="' . $agentCompanyFbLink . ' "
              //                       ><img
              //                         src="https://cdn.flyfarint.com/fb.png"
              //                         width="25px"
              //                         style="margin: 10px"
              //                       /></a>
              //                       <a href="' . $agentCompanyLinkedinLink . ' "
              //                       ><img
              //                         src="https://cdn.flyfarint.com/lin.png"
              //                         width="25px"
              //                         style="margin: 10px"
              //                       /></a>
              //                       <a href="' . $agentCompanyWhatsappNum . ' "
              //                       ><img
              //                         src="https://cdn.flyfarint.com/wapp.png "
              //                         width="25px"
              //                         style="margin: 10px"
              //                       /></a>
              //                   </td>
              //                 </tr>

              //             <tr>
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
              //                   padding-top: 5px;
              //                   padding-bottom: 5px;
              //                   padding-left: 10px;
              //                   padding-right: 10px;
              //                 "
              //               >
              //                 ' . $agentCompanyAddress . '
              //               </td>
              //             </tr>
              //           </table>
              //         </div>
              //       </div>
              //     </body>
              //   </html>
              // ';

                // $mail = new PHPMailer();

                // try {
                //     $mail->isSMTP();
                //     $mail->Host = 'flyfarint.com';
                //     $mail->SMTPAuth = true;
                //     $mail->Username = 'bookingcancel@b2b.flyfarint.com';
                //     $mail->Password = '123Next2$';
                //     $mail->SMTPSecure = 'ssl';
                //     $mail->Port = 465;

                //     //Recipients
                //     $mail->setFrom('bookingcancel@flyfarint.com', $agentCompanyName);
                //     $mail->addAddress("$subAgentEmail", "AgentId : $agentId");
                //     // $mail->addCC('habib@flyfarint.com');
                //     // $mail->addCC('afridi@flyfarint.com');

                //     $mail->isHTML(true);
                //     $mail->Subject = "Booking Cancel by $agentCompanyName";
                //     $mail->Body = $AgentMail;

                //     if (!$mail->Send()) {
                //         echo "Mailer Error: " . $mail->ErrorInfo;
                //     }

                // } catch (Exception $e) {

                // }


                 ///owner email

        // $OwnerEmail = '
        // <!DOCTYPE html>
        // <html lang="en">
        //   <head>
        //     <meta charset="UTF-8" />
        //     <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        //     <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        //     <title>Deposit Request
        // </title>
        //   </head>
        //   <body>
        //     <div
        //       class="div"
        //       style="
        //         width: 650px;
        //         height: 100vh;
        //         margin: 0 auto;
        //       "
        //     >
        //       <div
        //         style="
        //           width: 650px;
        //           height: 200px;
        //           background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
        //           border-radius: 20px 0px  20px  0px;

        //         "
        //       >
        //         <table
        //           border="0"
        //           cellpadding="0"
        //           cellspacing="0"
        //           align="center"
        //           style="
        //             border-collapse: collapse;
        //             border-spacing: 0;
        //             padding: 0;
        //             width: 650px;
        //             border-radius: 10px;

        //           "
        //         >
        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 font-weight: bold;
        //                 font-size: 20px;
        //                 line-height: 38px;
        //                 padding-top: 30px;
        //                 padding-bottom: 10px;
        //               "
        //             >
        //               <a href="https://www.flyfarint.com/"
        //                 ><img
        //                 src="https://cdn.flyfarint.com/logo.png"
        //                   width="130px"
        //               /></a>

        //             </td>
        //           </tr>
        //         </table>

        //         <table
        //           border="0"
        //           cellpadding="0"
        //           cellspacing="0"
        //           align="center"
        //           bgcolor="white"
        //           style="
        //             border-collapse: collapse;
        //             border-spacing: 0;
        //             padding: 0;
        //             width: 550px;
        //           "
        //         >
        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 font-size: 19px;
        //                 line-height: 38px;
        //                 padding-top: 20px;
        //                 background-color: white;


        //               "
        //             >
        //             Booking Cancel
        //         </td>
        //           </tr>
        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 padding-top: 15px;
        //                 font-size: 12px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-right: 20px;
        //                 background-color: white;

        //               "
        //             >
        //             Dear Fly Far International, Our ' .$route . ', ' . $tripType . ' ticket by ' . $airlines . ' booking request has been Cancelled.
        //     </td>     </tr>
                  
        //     <tr>
        //     <td
        //       align="center"
        //       valign="top"
        //       style="
        //         border-collapse: collapse;
        //         border-spacing: 0;
        //         color: #000000;
        //         font-family: sans-serif;
        //         text-align: left;
        //         padding-left: 20px;
        //         font-weight: bold;
        //         padding-top: 20px;
        //         font-size: 13px;
        //         line-height: 18px;
        //         color: #929090;
        //         padding-top: 20px;
        //         width: 100%;
        //         background-color: white;

        //       "
        //     >
        //     Cancelled By: <span style="color: #dc143c">' .$agentCompanyName. '</span>
        //     </td>
        //   </tr>

        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 padding-top: 20px;
        //                 font-size: 13px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-top: 20px;
        //                 width: 100%;
        //               "
        //             >
        //               Booking Id:
        //               <a style="color: #003566" href="http://" target="_blank"
        //                 >' . $bookingId . '</a
        //               >
        //             </td>
        //           </tr>
    
        //          <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 padding-top: 20px;
        //                 font-size: 13px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-top: 20px;
        //                 width: 100%;
        //                 background-color: white;

        //               "
        //             >
        //                    If you have any questions, just contact us we are always happy to
        //               help you out.
        //             </td>
        //           </tr>


        //              <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 padding-top: 20px;
        //                 font-size: 13px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-top: 20px;
        //                 width: 100%;
        //                 background-color: white;

        //               "
        //             >
        //                Sincerely,

        //             </td>
        //           </tr>

        //              <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 font-size: 13px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 width: 100%;
        //                 background-color: white;
        //                 padding-bottom: 20px

        //               "
        //             >
        //              ' . $agentCompanyName . '

        //             </td>
        //           </tr>
        //         </table>


        //       </div>
        //     </div>
        //   </body>
        // </html>

        // ';
        // $mail = new PHPMailer();

        // try {
        //     $mail->isSMTP();
        //     $mail->Host = 'flyfarint.com';
        //     $mail->SMTPAuth = true;
        //     $mail->Username = 'reissue@b2b.flyfarint.com';
        //     $mail->Password = '123Next2$';
        //     $mail->SMTPSecure = 'ssl'; 
        //     $mail->Port = 465;

        //     //Recipients
        //     $mail->setFrom("reissue@flyfarint.com", "Fly Far International");
        //     $mail->addAddress("$agentCompanyEmail", "AgentId : $agentId");
        //     // $mail->addCC('habib@flyfarint.com');
        //     // $mail->addCC('afridi@flyfarint.com');

        //     $mail->isHTML(true);
        //     $mail->Subject = " New Booking Cancelled by $agentCompanyName";
        //     $mail->Body = $OwnerEmail;
    
        //     if (!$mail->Send()) {
        //         echo "Mailer Error: " . $mail->ErrorInfo;
        //     }

        // } catch (Exception $e) {

        // }

        // Owner Agent Email
        // $OwnerAgentEmail = '
        // <!DOCTYPE html>
        // <html lang="en">
        //   <head>
        //     <meta charset="UTF-8" />
        //     <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        //     <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        //     <title>Deposit Request
        // </title>
        //   </head>
        //   <body>
        //     <div
        //       class="div"
        //       style="
        //         width: 650px;
        //         height: 100vh;
        //         margin: 0 auto;
        //       "
        //     >
        //       <div
        //         style="
        //           width: 650px;
        //           height: 200px;
        //           background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
        //           border-radius: 20px 0px  20px  0px;

        //         "
        //       >
        //         <table
        //           border="0"
        //           cellpadding="0"
        //           cellspacing="0"
        //           align="center"
        //           style="
        //             border-collapse: collapse;
        //             border-spacing: 0;
        //             padding: 0;
        //             width: 650px;
        //             border-radius: 10px;

        //           "
        //         >
        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 font-weight: bold;
        //                 font-size: 20px;
        //                 line-height: 38px;
        //                 padding-top: 30px;
        //                 padding-bottom: 10px;
        //               "
        //             >
        //               <a href="https://www.flyfarint.com/"
        //                 ><img
        //                 src="https://cdn.flyfarint.com/logo.png"
        //                   width="130px"
        //               /></a>

        //             </td>
        //           </tr>
        //         </table>

        //         <table
        //           border="0"
        //           cellpadding="0"
        //           cellspacing="0"
        //           align="center"
        //           bgcolor="white"
        //           style="
        //             border-collapse: collapse;
        //             border-spacing: 0;
        //             padding: 0;
        //             width: 550px;
        //           "
        //         >
        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 font-size: 19px;
        //                 line-height: 38px;
        //                 padding-top: 20px;
        //                 background-color: white;


        //               "
        //             >
        //             New Booking Cancelled
        //         </td>
        //           </tr>
        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 padding-top: 15px;
        //                 font-size: 12px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-right: 20px;
        //                 background-color: white;

        //               "
        //             >
        //             Dear ' . $agentCompanyName . ', Your ' . $route . ', ' . $tripType . ' Ticket Booking on ' . $airlines  . ' Airlines new booking Request has been Cancelled.
        //     </td>     </tr>

        //     <tr>
        //     <td
        //       align="center"
        //       valign="top"
        //       style="
        //         border-collapse: collapse;
        //         border-spacing: 0;
        //         color: #000000;
        //         font-family: sans-serif;
        //         text-align: left;
        //         padding-left: 20px;
        //         font-weight: bold;
        //         padding-top: 20px;
        //         font-size: 13px;
        //         line-height: 18px;
        //         color: #929090;
        //         padding-top: 20px;
        //         width: 100%;
        //         background-color: white;

        //       "
        //     >
        //     Cancelled By: <span style="color: #dc143c">' . $agentCompanyName.'</span>
        //     </td>
        //   </tr>

        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 padding-top: 20px;
        //                 font-size: 13px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-top: 20px;
        //                 width: 100%;
        //               "
        //             >
        //               Booking Id:
        //               <a style="color: #003566" href="http://" target="_blank"
        //                 >' . $bookingId . '</a
        //               >
        //             </td>
        //           </tr>

        //              <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 padding-top: 20px;
        //                 font-size: 13px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-top: 20px;
        //                 width: 100%;
        //                 background-color: white;

        //               "
        //             >
        //                    If you have any questions, just contact us we are always happy to
        //               help you out.
        //             </td>
        //           </tr>


        //              <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 padding-top: 20px;
        //                 font-size: 13px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-top: 20px;
        //                 width: 100%;
        //                 background-color: white;

        //               "
        //             >
        //                Sincerely,

        //             </td>
        //           </tr>

        //              <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 font-size: 13px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 width: 100%;
        //                 background-color: white;
        //                 padding-bottom: 20px

        //               "
        //             >

        //             Fly Far International
        //             </td>
        //           </tr>


        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #ffffff;
        //                 font-family: sans-serif;
        //                 text-align: center;
        //                 font-weight: 600;
        //                 font-size: 14px;
        //                 color: #ffffff;
        //                 padding-top: 15px;
        //                 background-color: #dc143c;
        //               "
        //             >
        //               Need more help?
        //             </td>
        //           </tr>

        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #ffffff;
        //                 font-family: sans-serif;
        //                 text-align: center;
        //                 font-size: 12px;
        //                 color: #ffffff;
        //                 padding-top: 8px;
        //                 padding-bottom: 20px;
        //                 padding-left: 30px;
        //                 padding-right: 30px;
        //                 background-color: #dc143c;


        //               "
        //             >
        //               Mail us at
        //               <a
        //                 style="color: white; font-size: 13px; text-decoration: none"
        //                 href="http://"
        //                 target="_blank"
        //                 >support@flyfarint.com
        //               </a>
        //               agency or Call us at 09606912912
        //             </td>
        //           </tr>

        //           <tr>
        //             <td
        //               valign="top"
        //               align="left"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #000000;
        //                 font-family: sans-serif;
        //                 text-align: left;
        //                 font-weight: bold;
        //                 font-size: 12px;
        //                 line-height: 18px;
        //                 color: #929090;
        //               "
        //             >

        //             <p> <a
        //                 style="
        //                   font-weight: bold;
        //                   font-size: 12px;
        //                   line-height: 15px;
        //                   color: #222222;

        //                 "
        //                 href="https://www.flyfarint.com/terms"
        //                 >Terms & Conditions</a
        //               >
        //               <a
        //                 style="
        //                   font-weight: bold;
        //                   font-size: 12px;
        //                   line-height: 15px;
        //                   color: #222222;
        //                   padding-left: 10px;
        //                 "
        //                 href="https://www.flyfarint.com/privacy"
        //                 >Privacy Policy</a
        //               ></p>
        //             </td>
        //           </tr>
        //           <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 font-family: sans-serif;
        //                 text-align: center;
        //                 padding-left: 20px;
        //                 font-weight: bold;
        //                 font-size: 12px;
        //                 line-height: 18px;
        //                 color: #929090;
        //                 padding-right: 20px;
        //               "
        //             >
        //                 <a href="https://www.facebook.com/FlyFarInternational/ "
        //                 ><img
        //                   src="https://cdn.flyfarint.com/fb.png"
        //                   width="25px"
        //                   style="margin: 10px"
        //               /></a>
        //               <a href="http:// "
        //                 ><img
        //                   src="https://cdn.flyfarint.com/lin.png"
        //                   width="25px"
        //                   style="margin: 10px"
        //               /></a>
        //               <a href="http:// "
        //                 ><img
        //                   src="https://cdn.flyfarint.com/wapp.png "
        //                   width="25px"
        //                   style="margin: 10px"
        //               /></a>
        //             </td>
        //           </tr>

        //                     <tr>
        //             <td
        //               align="center"
        //               valign="top"
        //               style="
        //                 border-collapse: collapse;
        //                 border-spacing: 0;
        //                 color: #929090;
        //                 font-family: sans-serif;
        //                 text-align: center;
        //                 font-weight: 500;
        //                 font-size: 12px;
        //                 padding-top:5px;
        //                 padding-bottom:5px;
        //                 padding-left:10px;
        //                 padding-right: 10px;
        //               "
        //             >
        // Ka 11/2A, Bashundhora R/A Road, Jagannathpur, Dhaka 1229
        //  </td>
        //           </tr>



        //         </table>


        //       </div>
        //     </div>
        //   </body>
        // </html>


        // ';

        // $mail = new PHPMailer();

        // try {
        //     $mail->isSMTP();
        //     $mail->Host = 'flyfarint.com';
        //     $mail->SMTPAuth = true;
        //     $mail->Username = 'reissue@b2b.flyfarint.com';
        //     $mail->Password = '123Next2$';
        //     $mail->SMTPSecure = 'ssl';
        //     $mail->Port = 465;

        //     //Recipients
        //     $mail->setFrom("reissue@flyfarint.com", "Fly Far International");
        //     $mail->addAddress("$subAgentEmail", "AgentId : $agentId");
        //     // $mail->addCC('habib@flyfarint.com');
        //     // $mail->addCC('afridi@flyfarint.com');

        //     $mail->isHTML(true);
        //     $mail->Subject = "Booking Cancelled by Fly Far International";
        //     $mail->Body = $OwnerAgentEmail;
        //     //print_r($mail);
        //     if (!$mail->Send()) {
        //         echo "Mailer Error: " . $mail->ErrorInfo;
        //     }

        // } catch (Exception $e) {

        // }


                // $SubAgentMail = '
                // <!DOCTYPE html>
                // <html lang="en">
                //   <head>
                //     <meta charset="UTF-8" />
                //     <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                //     <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                //     <title>Deposit Request
                // </title>
                //   </head>
                //   <body>
                //     <div
                //       class="div"
                //       style="
                //         width: 650px;
                //         height: 100vh;
                //         margin: 0 auto;
                //       "
                //     >
                //       <div
                //         style="
                //           width: 650px;
                //           height: 150px;
                //           background:#FFA84D;
                //         "
                //       >
                //       <table
                //       border="0"
                //       cellpadding="0"
                //       cellspacing="0"
                //       align="center"
                //       style="
                //         border-collapse: collapse;
                //         border-spacing: 0;
                //         padding: 0;
                //         width: 650px;
                //         border-radius: 10px;
                //       "
                //     >
                //       <tr>
                //         <td
                //           align="center"
                //           valign="top"
                //           style="
                //             border-collapse: collapse;
                //             border-spacing: 0;
                //             color: #000000;
                //             font-family: sans-serif;
                //             font-weight: bold;
                //             font-size: 20px;
                //             line-height: 38px;
                //             padding-top: 20px;
                //             padding-bottom: 10px;

                //           "
                //         >
                //         <a style="text-decoration: none; color:#2564B8; font-family: sans-serif; font-size: 25px; padding-top: 20px;" href="https://www.' . $agentCompanyWebsiteLink . '">' . $agentCompanyName . '</a>

                //         </td>
                //       </tr>
                //     </table>

                //         <table
                //           border="0"
                //           cellpadding="0"
                //           cellspacing="0"
                //           align="center"
                //           bgcolor="white"
                //           style="
                //             border-collapse: collapse;
                //             border-spacing: 0;
                //             padding: 0;
                //             width: 550px;
                //           "
                //         >
                //           <tr>
                //             <td
                //               align="center"
                //               valign="top"
                //               style="
                //                 border-collapse: collapse;
                //                 border-spacing: 0;
                //                 color: #000000;
                //                 font-family: sans-serif;
                //                 text-align: left;
                //                 padding-left: 20px;
                //                 font-weight: bold;
                //                 font-size: 19px;
                //                 line-height: 38px;
                //                 padding-top: 15px;
                //                 background-color: white;



                //               "
                //             >
                //             Booking Cancelled
                //         </td>
                //           </tr>
                //           <tr>
                //             <td
                //               align="center"
                //               valign="top"
                //               style="
                //                 border-collapse: collapse;
                //                 border-spacing: 0;
                //                 font-family: sans-serif;
                //                 text-align: left;
                //                 padding-left: 20px;
                //                 font-weight: bold;
                //                 padding-top: 10px;
                //                 font-size: 12px;
                //                 line-height: 18px;
                //                 color: #929090;
                //                 padding-right: 20px;
                //                 background-color: white;

                //               "
                //             >
                //             Dear ' . $agentCompanyName . ' , Our Booking request has been Canaled.
                //           </td>
                //           </tr>

                //           <tr>
                //             <td
                //               align="center"
                //               valign="top"
                //               style="
                //                 border-collapse: collapse;
                //                 border-spacing: 0;
                //                 color: #000000;
                //                 font-family: sans-serif;
                //                 text-align: left;
                //                 padding-left: 20px;
                //                 font-weight: bold;
                //                 padding-top: 20px;
                //                 font-size: 13px;
                //                 line-height: 18px;
                //                 color: #929090;
                //                 padding-top: 20px;
                //                 width: 100%;
                //               "
                //             >
                //             Booking Id:
                //               <a style="color: #2564B8" href="http://" target="_blank"
                //                 >' . $bookingId . '</a
                //               >
                //             </td>
                //           </tr>



                //              <tr>
                //             <td
                //               align="center"
                //               valign="top"
                //               style="
                //                 border-collapse: collapse;
                //                 border-spacing: 0;
                //                 color: #000000;
                //                 font-family: sans-serif;
                //                 text-align: left;
                //                 padding-left: 20px;
                //                 font-weight: bold;
                //                 padding-top: 20px;
                //                 font-size: 12px;
                //                 line-height: 18px;
                //                 color: #929090;
                //                 padding-top: 20px;
                //                 width: 100%;
                //                 background-color: white;

                //               "
                //             >
                //                Sincerely,

                //             </td>
                //           </tr>

                //              <tr>
                //             <td
                //               align="center"
                //               valign="top"
                //               style="
                //                 border-collapse: collapse;
                //                 border-spacing: 0;
                //                 color: #000000;
                //                 font-family: sans-serif;
                //                 text-align: left;
                //                 padding-left: 20px;
                //                 font-weight: bold;
                //                 font-size: 12px;
                //                 line-height: 18px;
                //                 color: #929090;
                //                 width: 100%;
                //                 background-color: white;
                //                 padding-bottom: 20px

                //               "
                //             >
                //               ' . $subAgentCompanyName . '

                //             </td>
                //           </tr>
                //         </table>


                //       </div>
                //     </div>
                //   </body>
                // </html>

                // ';

                // $mail = new PHPMailer();

                // try {
                //     $mail->isSMTP();
                //     $mail->Host = 'flyfarint.com';
                //     $mail->SMTPAuth = true;
                //     $mail->Username = 'whitelabel@mailservice.center';
                //     $mail->Password = '123Next2$';
                //     $mail->SMTPSecure = 'ssl';
                //     $mail->Port = 465;

                //     //Recipients
                //     $mail->setFrom("whitelabel@mailservice.center", $agentCompanyName);
                //     $mail->addAddress("$subAgentEmail", "AgentId : $agentId");
                //     // $mail->addCC('habib@flyfarint.com');
                //     // $mail->addCC('afridi@flyfarint.com');
                    

                //     $mail->isHTML(true);
                //     $mail->Subject = "New Booking Cancel by $subAgentCompanyName";
                //     $mail->Body = $SubAgentMail;


                //     if (!$mail->Send()) {
                //         $response['status'] = "success";
                //         $response['BookingId'] = "$bookingId";
                //         $response['message'] = "Booking Cancelled Successfully";
                //         $response['error'] = "Email Not Send Successfully";
                //     } else {
                //         $response['status'] = "success";
                //         $response['BookingId'] = "$bookingId";
                //         $response['message'] = "Booking Cancelled Successfully";
                //     }

                // } catch (Exception $e) {
                //     $response['status'] = "error";
                //     $response['message'] = "Mail Doesn't Send";
                // }

                $response['status'] = "success";
                        $response['BookingId'] = "$bookingId";
                        $response['message'] = "Booking Cancelled Successfully";
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