<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';

include_once '../../authorization.php';
if (authorization($conn) == true){

  if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $_POST = json_decode(file_get_contents('php://input'), true);

    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $recieveamount = isset($_POST['amount']) ? $_POST['amount'] :'';
    $actionBy = isset($_POST['actionBy']) ? $_POST['actionBy'] : '';


    //Data
    $fetch = "SELECT * FROM deposit_request WHERE id='$id'";
    $result = mysqli_query($conn, $fetch);
    $data = mysqli_fetch_array($result);

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
        $Message = "Approved By: Fly Far International";
      } else if (!empty($staffdata) && !empty($agentId)) {
        $Message = "Approved By: $actionBy, Fly Far International";
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

      if($newAmount >= 0){
        $conn->query("UPDATE `agent` SET `credit`='0' where agentId='$agentId'");
      }

      $createdTime = date("Y-m-d H:i:s");


      $sql_query = "INSERT INTO `agent_ledger`(`agentId`,`staffId`,`deposit`, `lastAmount`,`details`, `transactionId`,`reference`,`createdAt`)
                        VALUES ('$agentId','$staffId','$amount','$newAmount','$amount TK Deposit By $staffName successfully','$transactionId','$depositId','$createdAt')";

      if ($conn->query($sql_query) === TRUE) {
        $conn->query("UPDATE deposit_request SET status='approved', approvedBy='$actionBy', actionAt='$Time'  WHERE id='$id' ");
        $conn->query("INSERT INTO `activityLog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
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
                              Deposit Request Accepted
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
                              Dear ' . $companyName . ', Your Deposit request is accepted amount of ' . $amount . ' BDT on ' . $DateTime . '
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
                                Deposit ID:
                                <a style="color: #003566" href="http://" target="_blank"
                                  >' . $depositId . '</a
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
                                Previous Balance: <span style="color: #dc143c">' . $lastAmount . '</span> 
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
                                Deposit Request:  <span style="color: #dc143c">' . $amount . '	</span> 
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
                                After Deposit Balance: <span style="color: #dc143c">' . $newAmount . '	</span> 
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
                                  padding-top: 15px;
                                  background-color: #dc143c
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
                                  background-color: #dc143c
                  
                  
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
          $mail->Username = 'deposit@b2b.flyfarint.com';
          $mail->Password = '123Next2$';
          $mail->SMTPSecure = 'ssl';
          $mail->Port = 465;

          //Recipients
          $mail->setFrom('deposit@flyfarint.com', 'Fly Far Int');
          $mail->addAddress("$companyEmail", "AgentId : $agentId");
          $mail->addCC('ceo@flyfarint.com');
          $mail->addCC('sadman@flyfarint.com');
          



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


        //Agent maill
        $OwnerEmail = '
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
                                padding-top: 15px;
                                font-size: 12px;
                                line-height: 18px;
                                color: #929090;
                                padding-right: 20px;
                                background-color: white;
                
                              "
                            >
                            Dear Fly Far International, Thank you for accepting our deposit request at ' . $DateTime . '
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
                              Deposit Id:
                              <a style="color: #003566; padding-right: 15px" href="http://" target="_blank"
                                >' . $depositId . '</a
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
                              Approved by:
                              <a style="color: #003566; padding-right: 15px" href="http://" target="_blank"
                                >' . $actionBy . ', Fly Far International</a
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
                              Previous Balance	: <span style="color: #dc143c">' . $lastAmount . '</span> 
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
                              Deposit Balance:  <span style="color: #dc143c">' . $amount . '</span> 
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
                              Balance: <span style="color: #dc143c">' . $newAmount . '</span> 
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
                              Method:  <span style="color: #dc143c">' . $paymentway . '</span> 
                            </td>
                          </tr>

                          ' . $html . '

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
                            ' . $companyName . '              
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
          $mail1->Host = 'b2b.flyfarint.com';
          $mail1->SMTPAuth = true;
          $mail1->Username = 'deposit@b2b.flyfarint.com';
          $mail1->Password = '123Next2$';
          $mail1->SMTPSecure = 'ssl';
          $mail1->Port = 465;

          //Recipients
          $mail1->setFrom('deposit@flyfarint.com', 'Fly Far Int');
          $mail1->addAddress("otaoperation@flyfarint.com", "New Deposit");


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

      echo json_encode($response);

    }
  }else if($status == "approved"){
    $response['status'] = "success";
    $response['message'] = "Deposit already Approved";
    echo json_encode($response);
  }else{
    $response['status'] = "success";
    $response['message'] = "Deposit amount and recieve amount not same.";
    echo json_encode($response);
  }
}else{
  authorization($conn);
}

  
        
?>