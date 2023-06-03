<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require("../vendor/autoload.php");
 
if(array_key_exists("email", $_GET)){
      
        $email = $_GET["email"];

        $sql = mysqli_query($conn,"SELECT * FROM agent WHERE email='$email'");
        $row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

        if(!empty($row)){
          
          $agentId = $row['agentId'];
          $companyName = $row['company'];

          $encryption = substr(md5(mt_rand()), 0, 50);                             
          $link = "https://flyfarint.com/resetpassword/$encryption";

          $AgentMail ='
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
                         Reset Password
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
          Dear '.$companyName.' We saw you recently requested a new password. We are here to help!
          
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
                      Please <a style="font-size: 13px; color: #003566" href="'.$link.'" target="_blank">click</a>  here to reset your Flyway International account. Link
                      valid for 10 minutes
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
          If you didn’t make this request then let us know immediately. we make your security seriously
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
                             If you have any questions, just contact us we are always ready to
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
              $mail->Username   = 'forgetpassword@b2b.flyfarint.com';                    
              $mail->Password   = '123Next2$';                            
              $mail->SMTPSecure = 'ssl';            
              $mail->Port       = 465;                                    

              //Recipients
              $mail->setFrom('forgetpassword@flyfarint.com', 'Flyway International');
              $mail->addAddress("$email", "AgentId : $agentId");
              $mail->addCC("parvez@flyfarint.com");
              $mail->addCC("afridi@flyfarint.com");
              
              $mail->isHTML(true);                                  
              $mail->Subject = "Reset Password Request - $companyName";
              $mail->Body    = $AgentMail;

            if(!$mail->Send()) {
                echo "Mailer Error: " . $mail->ErrorInfo;
            } else {
               $sql = "INSERT INTO `forgetpassword`(`agentId`, `email`, `link`, `isClick`, `expire`)
                         VALUES ('$agentId','$email','$encryption','0','36000')";
                if ($conn->query($sql) === TRUE) {
                    $response['status']="success";
                    $response['message']="Password Reset Link Send To Your Email";
        
                } else {
                    $response['status']="error";
                    $response['message']="Attempt failed";
                }
            }
                          
            
          } catch (Exception $e) {
              $response['status']="error";
              $response['message']="Mail Doesn't Send"; 
          } 
            
        }else{
          $response['status']="error";
          $response['message']="Email Not Found";
        }
        
        echo json_encode($response);
    
  }