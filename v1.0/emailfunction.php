<?php
include 'config.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require("vendor/autoload.php");

function sendToAdmin($subject, $message, $agentId, $header, $property, $data)
{
  global $conn;
  $sql = "SELECT `company`  FROM `agent` WHERE `agentId`='$agentId'";
  $row = $conn->query($sql)->fetch_assoc();
  $companyName = $row["company"];

  $emailBody =
    '<!DOCTYPE html>
      <html lang="en">
        <head>
          <meta charset="UTF-8" />
          <meta http-equiv="X-UA-Compatible" content="IE=edge" />
          <meta name="viewport" content="width=device-width, initial-scale=1.0" />
          <title>' . $header . '</title>
        </head>
        <body>
         
          <div class="div" style="width: 650px; height: 100vh; margin: 0 auto">
            <div style="width: 650px; height: 150px; background: #48a9f8; border-radius: 10px; ">
              
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
                   <a href="https://b2b.shopnotour.com//">
                    <img style="z-index: 10000;" src="https://shopno.api.flyfarint.com/asset/company/SHOPNO_TOUR_LOGO.png" alt="Logo" >
                   </a>
                    
                    
                  </td>
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
                  ' . $header . '
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
                    Dear Shopno Tour, ' . $message . '
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
                    ' . $property . '
                    <a style="color: #525371" href="http://" target="_blank"
                      >' . $data . '</a
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
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'reservation@flywayint.com';
    $mail->Password = 'bjldlttnukjamfsi';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;


    $mail->setFrom("reservation@flywayint.com", $companyName);
    $mail->addAddress('resflyway@gmail.com', 'Flyway Travel'); //Recipients


    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $emailBody;

    if (!$mail->Send()) {
      echo "Mailer Error: " . $mail->ErrorInfo;
    }

  } catch (Exception $e) {
    echo "Mailer Error: " . $e;
  }


}


function sendToAgent($subject, $message, $agentId, $header, $property, $data)
{
  global $conn;
  $sql = "SELECT `company`, `email`  FROM `agent` WHERE `agentId`='$agentId'";
  $row = $conn->query($sql)->fetch_assoc();
  $companyName = $row["company"];
  $agentEmailAdd = $row["email"];

  $emailBody = '<!DOCTYPE html>
  <html lang="en">
    <head>
      <meta charset="UTF-8" />
      <meta http-equiv="X-UA-Compatible" content="IE=edge" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>' . $header . '</title>
    </head>
    <body>
            <div class="div" style="width: 650px; height: 100vh; margin: 0 auto">
        <div style="width: 650px; height: 150px; background: #48a9f8; border-radius: 10px; ">
          
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
               <a href="https://b2b.shopnotour.com/">
                <img style="z-index: 10000;" src="https://shopno.api.flyfarint.com/asset/company/SHOPNO_TOUR_LOGO.png" alt="Logo" >
               </a>
                
                
              </td>
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
                ' . $header . '
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
                Dear ' . $companyName . ', ' . $message . '
                Accepted.
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
                  color: #525371;
                  padding-right: 20px;
                  background-color: white;
                "
              >
                ' . $property . ' <span>' . $data . '</span>
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
                Shopno Tour
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
                Ka 11/2A, Bashundhora R/A Road, Jagannathpur, Dhaka 1229
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
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'reservation@flywayint.com';
    $mail->Password = 'bjldlttnukjamfsi';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;


    $mail->setFrom("reservation@flywayint.com", 'Flyway Travel');
    $mail->addAddress($agentEmailAdd, $companyName); //Recipients


    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $emailBody;

    if (!$mail->Send()) {
      echo "Mailer Error: " . $mail->ErrorInfo;
    }

  } catch (Exception $e) {
    echo "Mailer Error: " . $e;
  }

}




?>