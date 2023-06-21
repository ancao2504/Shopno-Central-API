<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;

require '../../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $_POST = json_decode(file_get_contents('php://input'), true);

    $depositId = $_POST['depositId'];
    $agentId = $_POST['agentId'];
    $actionBy = $_POST['actionBy'];

    //Data

    $data = ($conn->query("SELECT * FROM deposit_request WHERE agentId='$agentId' AND depositId='$depositId'")->fetch_all(MYSQLI_ASSOC))[0];

    if (!empty( $data)) {

        $id = $data['id'];
        $agentId = $data['agentId'];
        $depositId = $data['depositId'];
        $transactionId = $data['transactionId'];
        $staffId = $data['staffId'];
        $ref = $data['ref'];
        $amount = $data['amount'];
        $paymentwaymethod = $data['paymentmethod'];
        $paymentway = $data['paymentway'];
        $sender = $data['sender'];
        $status = $data['status'];
        $reciever = $data['reciever'];
        $chequeIssueDate = $data['chequeIssueDate'];
        $createdAt = date("Y-m-d H:i:s");


        if ($status == "pending") {
          

            //Agent Info
            $agentdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `agent` where agentId='$agentId'"));
            $companyName = $agentdata['company'];
            $companyEmail = $agentdata['email'];

            //staff Info
            $staffName = "";
            $staffsql = mysqli_query($conn, "SELECT * FROM `staffList` where staffId='$staffId' AND agentId='$agentId'");
            $staffdata = mysqli_fetch_array($staffsql);
            if (isset($staffdata['name'])) {
                $staffName = $staffdata['name'];
            } else {
                $staffName = "Agent";
            }

            if (empty($staffdata) && !empty($agentId)) {
                $Message = "Approved By: Flyway International";
            } else if (!empty($staffdata) && !empty($agentId)) {
                $Message = "Approved By: $actionBy, Flyway International";
            }

            $DateTime = date("D d M Y h:i A");

            //Last Amount
            $amountsql = "SELECT lastAmount, deposit FROM `agent_ledger` WHERE agentId='$agentId' ORDER BY id DESC LIMIT 1";
            $result1 = mysqli_query($conn, $amountsql);
            $data1 = mysqli_fetch_array($result1);

            $deposit = $data1['deposit'];

            $lastAmount = 0;
            if (!empty($data1['lastAmount'])) {
                $lastAmount = $data1['lastAmount'];
            } else {
                $lastAmount = 0;
            }

            // $lastAmount = $data1['lastAmount'];

            $afterDeposit = $lastAmount + $deposit;
            $newAmount = $lastAmount + $amount;

            if ($newAmount >= 0) {
                $conn->query("UPDATE `agent` SET `credit`='0' where agentId='$agentId'");
            }

            $createdTime = date("Y-m-d H:i:s");

            $sql_query = "INSERT INTO `agent_ledger`(`agentId`,`staffId`,`deposit`, `lastAmount`,`details`, `transactionId`,`reference`,`createdAt`)
                    VALUES ('$agentId','$staffId','$amount','$newAmount','$amount TK Deposit By $staffName successfully','$transactionId','$depositId','$createdAt')";

            if ($conn->query($sql_query) === true) {
                $conn->query("UPDATE deposit_request SET status='approved', approvedBy='$actionBy', actionAt='$createdTime'  WHERE agentId='$agentId' AND depositId='$depositId' ");
                $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
              VALUES ('$depositId','$agentId','Approved','Deposited $amount','$actionBy','$createdTime')");

                $html;
                if ($paymentway == 'bankTransfer') {
                    $html = '<tr>
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
         Send by:  <span style="color: #dc143c">' . $sender . '</span>
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
       Receive By:  <span style="color: #dc143c">' . $reciever . '</span>
    </td>
  </tr>';
                } else if ($paymentway == 'Cheque') {
                    $html = '<tr>
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
         Check Number:  <span style="color: #dc143c">' . $transactionId . '</span>
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
       Bank Name:  <span style="color: #dc143c">' . $paymentwaymethod . '</span>
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
     Check Issue Date:  <span style="color: #dc143c">' . $chequeIssueDate . '</span>
  </td>
</tr>
  ';
                } else if ($paymentway == 'Cash') {
                    $html = '
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
       Sender Name:  <span style="color: #dc143c">' . $sender . '</span>
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
     Receiver Name:  <span style="color: #dc143c">' . $reciever . '</span>
  </td>
</tr>
  ';
                } else if ($paymentway == 'mobileTransfer') {
                    $html = '
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
     Payment Method:  <span style="color: #dc143c">' . $paymentwaymethod . '</span>
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
Pay Using Account Number:  <span style="color: #dc143c">' . $sender . '</span>
</td>
</tr>
';
                }

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
             <div
               style="
                 width: 650px;
                 height: 150px;
                 background: #c5e0ff;
                 border-radius: 10px;
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
                   <img src="https://flyway.api.flyfarint.com/asset/ownerlogo/Logo.png" width="100" height="80" />
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
                   Deposit Request Approved
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
                     Dear Flyway International, We Send you new deposit request amount of '.$amount.' BDT, Which has been approved
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
               Deposit ID:
               <a style="color: #2564b8" href="http://" target="_blank"
                 >'.$depositId.'</a
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
                     '.$companyName.'
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
        $mail->Username = 'deposit@b2b.flyfarint.com';
        $mail->Password = '123Next2$';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        //Recipients
        $mail->setFrom('deposit@flyfarint.com', 'Flyway International');
        $mail->addAddress("afridi@flyfarint.com", "AgentId : $agentId");
        $mail->addCC('habib@flyfarint.com');

        $mail->isHTML(true);
        $mail->Subject = "Deposit Request Approval - $companyName";
        $mail->Body = $AgentEmail;

        if (!$mail->Send()) {
          echo "Mailer Error: " . $mail->ErrorInfo;
        }

      } catch (Exception $e) {
            $response['status'] = "error";
            $response['message'] = "Mail Doesn't Send";
        }

    
                $OwnerEmail = '
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
                          background: #c5e0ff;
                          border-radius: 10px;
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
                        <img src="https://flyway.api.flyfarint.com/asset/ownerlogo/Logo.png" width="100" height="80" />
                
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
                            Deposit Request Approve 
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
                            Dear '.$companyName.', Your new deposit request amount of '.$amount.' BDT has been accepted, Thank you</td>
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
                           Deposit ID: <span>'.$depositId.'</span>
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
                                >reservation@flywayint.com
                              </a>
                              agency or Call us at 01400001101-04
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
                  href="https://flywaytravel.com.bd/terms"
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
                  href="https://flywaytravel.com.bd/privacy"
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
                            <a href="https://m.facebook.com/flywayt"
                              ><img
                                src="https://cdn.flyfarint.com/fb.png"
                                width="25px"
                                style="margin: 10px"
                            /></a>
                            <a href="https://www.linkedin.com/company/flyway.travel"
                              ><img
                                src="https://cdn.flyfarint.com/lin.png"
                                width="25px"
                                style="margin: 10px"
                            /></a>
                            <a href="https://wa.me/+8801400001101"
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
                              Ka 11/2A, Bashundhora R/A Road, Jagannathpur, Dhaka 1229
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
                  $mail1->Username = 'deposit@b2b.flyfarint.com';
                  $mail1->Password = '123Next2$';
                  $mail1->SMTPSecure = 'ssl';
                  $mail1->Port = 465;

                  //Recipients
                  $mail1->setFrom('deposit@flyfarint.com', 'Flyway International');
                  $mail1->addAddress("afridi@flyfarint.com", "AgentId : $agentId");
                  $mail1->addCC('habib@flyfarint.com');

                  $mail1->isHTML(true);
                  $mail1->Subject = "New Deposit Request Approved for - $companyName";
                  $mail1->Body = $OwnerEmail;

                  if (!$mail1->Send()) {
                    $response['status'] = "success";
                    $response['message'] = "Deposit Approved Successful";
                    $response['error'] = "Email Not Send";
                  } else {
                    $response['status'] = "success";
                    $response['message'] = "Deposit Approved Successful";
                  }

                } catch (Exception $e) {
                  $response['status'] = "error";
                  $response['message'] = "Mail Doesn't Send";
                }
               
            }
            

            

        } else if ($status == "approved") {
          $response['status'] = "error";
          $response['message'] = "Deposit Already Approved";
        }else if ($status == "rejected") {
          $response['status'] = "error";
          $response['message'] = "Deposit Already Rejected";
        }
    } else {
        $response['status'] = "error";
        $response['message'] = "Agent Not Found";
       

    }
    echo json_encode($response);

}
$conn->close();
?>



