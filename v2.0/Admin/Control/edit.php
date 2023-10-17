<?php
include "../../config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
require '../../vendor/autoload.php'; 

include_once '../../authorization.php';
if (authorization($conn) == true){ 
  if ($_SERVER["REQUEST_METHOD"] == "POST"){
              
          $_POST = json_decode(file_get_contents('php://input'), true);
              $key = $_POST['key'];
              $value = $_POST['value'];
              $reason = $_POST['reason'];
              $updated_by = $_POST['created_by'];
              $updated_at = date('Y-m-d H:i:s');
                          
          $updatesql = "UPDATE `control` SET `$key`='$value', `reason`='$reason', `updated_by`='$updated_by',`updated_at`='$updated_at' WHERE id='1'";
      
          if ($conn->query($updatesql) === true) {
              
              $turn = $value == 1? "On": "Off";
              $Email = '
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
                                  font-size: 18px;
                                  line-height: 38px;
                                  padding-top: 20px;
                                  background-color: white;
                                "
                              >
                                ' . $key . ' Disable by ' . $updated_by . '
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
                                Reason:
                                <span style="color: #003566" href="http://" target="_blank"
                                  >' . $reason . '</span
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
                                Time: <span style="color: #dc143c">' . $updated_at . ' </span>
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
                                Fly Far Tech Team
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
                  $mail->Username = 'job@b2b.flyfarint.com';
                  $mail->Password = '123Next2$';
                  $mail->SMTPSecure = 'ssl';
                  $mail->Port = 465;
      
                  //Recipients
                  $mail->setFrom('warning@b2b.flyfarint.com', "GDS $key $turn");
                  $mail->addAddress("ceo@flyfarint.com", "Fly Far International");
                  $mail->addCC('sadman@flyfarint.com');
                  $mail->addCC('habib@flyfarint.com');
                  $mail->addCC('afridi@flyfarint.com');
      
                  $mail->isHTML(true);
                  $mail->Subject = "GDS $key $turn";
                  $mail->Body = $Email;
                  if (!$mail->Send()) {
                      echo "Mailer Error: " . $mail->ErrorInfo;
                  } else {
                      $response['status'] = "success";
                      $response['message'] = "Updated Successfully";
                  }
      
              } catch (Exception $e) {
                  $response['status'] = "error";
                  $response['message'] = "Mail Doesn't Send";
              }
      
          } else {
              $response['status'] = "error";
              $response['message'] = "Updated failed";
          }
              
      echo json_encode($response);
  }else if(array_key_exists("priority", $_GET)){
      if ($_SERVER["REQUEST_METHOD"] == "POST"){
              
          $_POST = json_decode(file_get_contents('php://input'), true);
              $priority1 = $_POST['priority'];
              $updated_by = $_POST['created_by'];
              $updated_at = date('Y-m-d H:i:s');
                          
          $updatesql = "UPDATE `control` SET `priority`='$priority1', `updated_by`='$updated_by',`updated_at`='$updated_at' WHERE id='1'";
      
          if ($conn->query($updatesql) === true) {
              
              $turn = $value == 1? "On": "Off";
              $Email = '
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
                                  font-size: 18px;
                                  line-height: 38px;
                                  padding-top: 20px;
                                  background-color: white;
                                "
                              >
                                ' . $key . ' Disable by ' . $updated_by . '
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
                                Reason:
                                <span style="color: #003566" href="http://" target="_blank"
                                  >' . $reason . '</span
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
                                Time: <span style="color: #dc143c">' . $updated_at . ' </span>
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
                                Fly Far Tech Team
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
                  $mail->Username = 'job@b2b.flyfarint.com';
                  $mail->Password = '123Next2$';
                  $mail->SMTPSecure = 'ssl';
                  $mail->Port = 465;
      
                  //Recipients
                  $mail->setFrom('warning@b2b.flyfarint.com', "GDS $key $turn");
                  $mail->addAddress("ceo@flyfarint.com", "Fly Far International");
                  $mail->addCC('sadman@flyfarint.com');
                  $mail->addCC('habib@flyfarint.com');
                  $mail->addCC('afridi@flyfarint.com');
      
                  $mail->isHTML(true);
                  $mail->Subject = "GDS $key $turn";
                  $mail->Body = $Email;
                  if (!$mail->Send()) {
                      echo "Mailer Error: " . $mail->ErrorInfo;
                  } else {
                      $response['status'] = "success";
                      $response['message'] = "Updated Successfully";
                  }
      
              } catch (Exception $e) {
                  $response['status'] = "error";
                  $response['message'] = "Mail Doesn't Send";
              }
      
          } else {
              $response['status'] = "error";
              $response['message'] = "Updated failed";
          }
              
      echo json_encode($response);
  }
  }
}else{
  authorization($conn);
}