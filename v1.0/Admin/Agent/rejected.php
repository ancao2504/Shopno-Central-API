<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require '../../vendor/autoload.php';

if (array_key_exists("agentId", $_GET) && array_key_exists("actionBy", $_GET)) {
    $agentId = $_GET['agentId'];
    $actionBy = $_GET['actionBy'];

    $data = $conn->query("SELECT * FROM agent WHERE agentId='$agentId'")->fetch_all(MYSQLI_ASSOC);
    if (!empty($data)) {
        $companyname = $data[0]['company'];
        $agentEmail = $data[0]['email'];
        $agentId = $data[0]['agentId'];
        $password = $data[0]['password'];
        $companyadd = $data[0]['companyadd'];
        $agentName = $data[0]['name'];
        $Status = $data[0]['status'];
        $createdTime = date("Y-m-d H:i:s");

        if ($Status == 'rejected') {
            $response['status'] = "error";
            $response['message'] = "Agent Already Rejected";
        } else {
            $sql = "UPDATE `agent` SET `status`='rejected' WHERE agentId='$agentId'";

            if ($conn->query($sql) === true) {
                $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                    VALUES ('$agentId','$agentId','Rejected',' ','$actionBy','$createdTime')");
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
                              Agent Request Rejected
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
                              Dear ' . $companyname . ', Due to lack of valid information, your Agent Register Request has been cancelled.
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
                $mail = new PHPMailer();

                try {
                    $mail->isSMTP();
                    $mail->Host = 'mail.flyfarint.net';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'agent@mailcenter.flyfarint.com';
                    $mail->Password = '123Next2$';
                    $mail->SMTPSecure = 'ssl';
                    $mail->Port = 465;

                    //Recipients
                    $mail->setFrom('agent@mailcenter.flyfarint.com', 'Flyway International');
                    $mail->addAddress("$agentEmail", "AgentId : $agentId");
                    $mail->addCC("ceo@flyfarint.com");
                    $mail->addCC("habib@flyfarint.com");
                    $mail->addCC("afridi@flyfarint.com");

                    $mail->isHTML(true);
                    $mail->Subject = "Agent Request Rejected - $companyname";
                    $mail->Body = $OwnerEmail;
                    print_r($mail);

                    if (!$mail->Send()) {
                        $response['action'] = "success";
                        $response['message'] = "Agent Rejected Successfully";
                        $response["error"] = "Mail Not Send";
                    } else {
                        $response['action'] = "success";
                        $response['message'] = "Agent Rejected Successfully";
                    }

                } catch (Exception $e) {
                    $response['status'] = "error";
                    $response['message'] = "Mail Doesn't Send";
                }

                $response['action'] = "success";
                $response['message'] = "Agent Rejected Successfully";
            }
        }

    } else {
        $response['action'] = "error";
        $response['message'] = "Agent Not Found";
    }
    echo json_encode($response);
}
