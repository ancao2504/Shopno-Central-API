<?php
include("../../config.php");

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
  if(array_key_exists("agentId",$_GET) && array_key_exists("amount",$_GET) && array_key_exists('actionBy',$_GET)){
      
      $agentId = $_GET['agentId'];
      $amount = $_GET['amount'];
      $actionBy = $_GET['actionBy'];
      $createdTime = date("Y-m-d H:i:s");
      $DateTime = date("D d M Y h:i A");
      

      $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
      $data = mysqli_fetch_array($query,MYSQLI_ASSOC);

      $companyname = $data['company'];
      $compnayphone = $data['phone'];
      $agentEmail = $data['email'];
      $creditAm = $data['credit'];

      $newAmount=0;
      if($amount > $creditAm){
        $newAmount = $amount - $creditAm;
      }

      $amountsql = "SELECT lastAmount, deposit FROM `agent_ledger` WHERE agentId='$agentId' ORDER BY id DESC LIMIT 1";
      $result1 = mysqli_query($conn, $amountsql);
      $data1 = mysqli_fetch_array($result1);
      if(!empty($data1)){
        $lastAmount = $data1['lastAmount'];
      }else{
        $lastAmount = 0;
      }
        
              
      $sql = "INSERT INTO `agent_ledger`(`agentId`, `loan`, `lastAmount`, `details`,`actionBy`,`createdAt`)
                    VALUES ('$agentId','$newAmount','$lastAmount','Loan Given $newAmount By $actionBy','$actionBy','$createdTime')";
                    
      if ($conn->query($sql) === TRUE) {
        
        $conn->query("UPDATE `agent` SET credit='$amount', `creditby`='$actionBy' WHERE agentId='$agentId'");
        
        $conn->query("INSERT INTO `activityLog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                  VALUES ('$agentId','$agentId','Credited','Loan Given $newAmount','$actionBy','$createdTime')");

        $conn->query("INSERT INTO `notification`(`agentId`,`title`,`timedate`,`text`)
        VALUES('$agentId','$amount Taka Credited By $actionBy','$createdTime','$newAmount Credited to your account as loan. This amount will be deducted from your account when you deposit.')");


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
                          Credit Balance Added
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
                          Dear '.$companyname.', Your credit balance request is approved on '.$DateTime.'.
              
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
                  background-color: white;

                "
              >
                Approved By:
                <span style="color: #003566; padding-right: 15px"
                  >'.$actionBy.'</span
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
                              padding-top: 10px;
                              width: 100%;
                              background-color: white;
              
                            "
                          >
                            Credit Balance Amount:  <span style="color: #dc143c">'.$amount.'	</span> 
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
          $mail->Host       = 'b2b.flyfarint.com';
          $mail->SMTPAuth   = true;
          $mail->Username   = 'reissue@b2b.flyfarint.com';
          $mail->Password   = '123Next2$';
          $mail->SMTPSecure = 'ssl';
          $mail->Port       = 465;

          //Recipients
          $mail->setFrom('reissue@flyfarint.com', 'Fly Far International');
          $mail->addAddress("$agentEmail", "AgentId : $agentId");
          $mail->addCC('otaoperation@flyfarint.com');
          

          $mail->isHTML(true);
          $mail->Subject = "Credit Balance Request Approved - $companyname";
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
          height: 70vh;
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

              Credit Balance Request Approved

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
  Dear Fly Far International, Our Credit Balance request amount of '.$amount.' BDT has been approved on '.$DateTime.'.          </tr>



      
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
                Approved By:
                <span style="color: #003566; padding-right: 15px"
                  >'.$actionBy.'</span
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
                Credit Balance Amount: <span style="color: #dc143c">'.$amount.'</span> 
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
                '.$companyname.'
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
          $mail1->Host       = 'b2b.flyfarint.com';
          $mail1->SMTPAuth   = true;
          $mail1->Username   = 'reissue@b2b.flyfarint.com';
          $mail1->Password   = '123Next2$';
          $mail1->SMTPSecure = 'ssl';
          $mail1->Port       = 465;

          //Recipients
          $mail1->setFrom('reissue@flyfarint.com', 'Fly Far International');
          $mail1->addAddress("otaoperation@flyfarint.com", "Credit");
          


          $mail1->isHTML(true);
          $mail1->Subject = "New Credit Balance Approved by - $companyname";
          $mail1->Body    = $OwnerMail;


          if (!$mail1->Send()) {
              $response['status']="success";
              $response['message']="Credit Added Successfully";
              $response['error']="Credit Added Request Successfully";
          } else {
              $response['status']="success";
              $response['message']="Credit Added Successfully";
          }
      } catch (Exception $e1) {
          $response['status']="error";
          $response['message']="Mail Doesn't Send";
      }
  }

  echo json_encode($response);
              
  }           
}else{
  authorization($conn);
}

?>