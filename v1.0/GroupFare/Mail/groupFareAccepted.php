<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require("../vendor/autoload.php");

if ($_SERVER["REQUEST_METHOD"] == "POST"){
        
    $_POST = json_decode(file_get_contents('php://input'), true);
 
  if ($conn->query($sql) === TRUE) {
    if ($conn->query($sqlUpdate) === TRUE) {
      
    }

    
    $agentMail ='
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
              Group Fare Request Accepted
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
              Dear Go Zayan,Your Grpup fare Booking Request Dhaka to Dubai has
              been on accepted. Thank you again for Booking with Fly Far
              International
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
              Requested by By
              <span style="color: #003566"> Kayes Fahim, Go Zayan </span>
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
                padding-top: 10px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                width: 100%;
              "
            >
              <p>
                Group Fare Booking Id:
                <a
                  style="color: #003566; padding-right: 10px"
                  href="http://"
                  target="_blank"
                  >FFIB515651515</a
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
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                width: 100%;
              "
            >
              <p>
                Number of Group Fare: 
                <a
                  style="color: #003566; padding-right: 10px"
                  href="http://"
                  target="_blank"
                  >14</a
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
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                width: 100%;
              "
            >
              <p>
                Ticket Price:
                <span style="color: #003566; padding-right: 10px"
                  >88,000 BDT</span
                >

                Current Balance:
                <span style="color: #003566; padding-right: 10px"
                  >8,000 BDT</span
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
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 10px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                width: 100%;
              "
            >
              Please allow us some time to review your booking Information; we
              will get back to you as soon as possible.
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
              If you have any questions, just contact us we are alwayes happy to
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
              Go Zayan
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
                          
                $mail = new PHPMailer();

                try {
                    $mail->isSMTP();                                    
                    $mail->Host       = 'b2b.flyfarint.com';                     
                    $mail->SMTPAuth   = true;                                  
                    $mail->Username   = 'void@b2b.flyfarint.com';                    
                    $mail->Password   = '123Next2$';                            
                    $mail->SMTPSecure = 'ssl';            
                    $mail->Port       = 465;                                    

                    //Recipients
                    $mail->setFrom('void@flyfarint.com', 'Flyway International');
                    $mail->addAddress("$agentEmail", "AgentId : $agentId");
                    $mail->addCC('otaoperation@flyfarint.com');
                    
                    $mail->isHTML(true);                                  
                    $mail->Subject = "Ticket Void Request Confirmation - $companyname";
                    $mail->Body    = $agentMail;
                    if(!$mail->Send()) {
                        echo "Mailer Error: " . $mail->ErrorInfo;
                    }else{
                      
                    }
                                                                         
                }catch (Exception $e) {
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

            Group Fare Booking Request Accepted

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
Dear Flyway International, We been requested for Dhaka to Dubai Group Fare on <span style="color: #DC143C"> Biman Bangladesh </span> on 12th Oct 2022. which has been accepted
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
            Approve By: <span style="color:#003566 ;">  Kayes Fahim, Go Zayan </span>
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
              Booking Id:
              <a style="color: #003566; padding-right: 12px" href="http://" target="_blank"
                >FFIB515651515</a
              >
              System:
              <span style="color: #003566; padding-right: 12px" href="http://" target="_blank"
                >GDX</span
              >
              System PNR:
              <span style="color: #003566" href="http://" target="_blank"
                >FFIB515651515</span
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
               No of Group Fare: <span style="color: #dc143c">10</span> 
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
               Destination: <span style="color: #dc143c">Dhaka - Dubai</span> 
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
               Travel Date:  <span style="color: #dc143c">12th Oct 2022 - 28th Oct 2022	</span> 
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
               Airline: <span style="color: #dc143c">Biman Bangladesh	</span> 
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
               Pax:  <span style="color: #dc143c">10</span> 
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
               Cost:  <span style="color: #dc143c">20,000 BDT
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
As a result, We pray and hope that you would accept our Ticket Issue request as soon as possible and obligue accordingly.
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
              Flyway International

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
                    $mail1->Username   = 'void@b2b.flyfarint.com';                    
                    $mail1->Password   = '123Next2$';                            
                    $mail1->SMTPSecure = 'ssl';            
                    $mail1->Port       = 465;                                    

                    //Recipients
                    $mail1->setFrom('void@flyfarint.com', 'Flyway International');
                     $mail1->addAddress("otaoperation@flyfarint.com", "Void Ticket Request");
                    
                    
                    $mail1->isHTML(true);                                  
                    $mail1->Subject = "New Ticket Void Request by - $companyname";
                    $mail1->Body    = $OwnerMail;


                    if(!$mail1->Send()) {
                            $response['status']="success";
                            $response['InvoiceId']="$voidId";
                            $response['message']="Ticketing Void Request Successfully";
                            $response['error']="Ticketing Void Request Successfully";
                    } else {
                            $response['status']="success";
                            $response['InvoiceId']="$voidId";
                            $response['message']="Ticket Void Request Successfully";
                    }
                                                                         
                }catch (Exception $e1) {
                    $response['status']="error";
                    $response['message']="Mail Doesn't Send"; 
                } 

      echo json_encode($response);
    
    }

  }
  





