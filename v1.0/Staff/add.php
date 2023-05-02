<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require("../vendor/autoload.php");


if ($_SERVER["REQUEST_METHOD"] == "POST"){
        
    $_POST = json_decode(file_get_contents('php://input'), true);

        $agentId  = $_POST['agentId'];
        $Name  = $_POST['Name'];
        $Email  = $_POST['Email'];
        $Designation  = $_POST['Designation'];
        $Phone  = $_POST['Phone'];
        $Role  = $_POST['Role'];      
        $Password = $_POST['Password'];

        $Date = date("Y-m-d H:i:s");
        $DateTime = date("D d M Y h:i A");

        $StaffId ="";
        $sql = "SELECT * FROM staffList where agentId='$agentId' ORDER BY id DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["staffId"]); 
                $number= (int)$outputString + 1;
                $StaffId = "FWST$number"; 								
            }
        } else {
            $StaffId ="FWST1000";
        }

        $checkAgent="SELECT * FROM agent WHERE agentId='$agentId'";
        $agentResultInto=mysqli_query($conn,$checkAgent);
        $agentData = mysqli_fetch_array($agentResultInto);

        if(!empty($agentData)){ 
          $agentName = $agentData['name'];
          $companyName = $agentData['company'];
        }

        $checkUser="SELECT * FROM staffList WHERE email='$Email'";
        $result=mysqli_query($conn,$checkUser);

        $checkAgent="SELECT * FROM agent WHERE email = '$Email'";
        $resultAgent=mysqli_query($conn,$checkAgent);

        if(mysqli_num_rows($result) <= 0 && mysqli_num_rows($resultAgent)> 0){                   
              $response['status']="error";
  	          $response['message']="User Already Exists as Agent";

        }else if(mysqli_num_rows($result)> 0){ 
            $response['status']="error";
  	        $response['message']="Staff Already Exists";       
        }else{
            $sql = "INSERT INTO `staffList`(
                `staffId`,
                `agentId`,
                `name`,
                `email`,
                `password`,
                `phone`,
                `status`,
                `designation`,
                `role`,
                `created`
              )
            VALUES(
                '$StaffId',
                '$agentId',
                '$Name',
                '$Email',
                '$Password',               
                '$Phone',
                'Active',
                '$Designation',
                '$Role',
                '$Date'
            )";

            if ($conn->query($sql) === TRUE) {
                $htmlBody ='
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
              Welcome! '.$Name.'
 
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
         Congratulations! You are now a registered Staff on '.$companyName.'. Please find your login credentials below.
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
             Company Name: <span style="color: #003566">'.$companyName.'</span>
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
                padding-top: 5px;
                width: 100%;
                background-color: white;

              "
            >
             Agent Name: <span style="color: #003566">'.$agentName.'</span>
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
                width: 100%;
                background-color: white;

              "
            >
             Staff ID: <span style="color: #003566">'.$StaffId.'</span>
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
                padding-top: 5px;
                width: 100%;
                background-color: white;

              "
            >
             Staff Name: <span style="color: #003566">'.$Name.', '.$Designation.'</span>
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
                padding-top: 5px;
                width: 100%;
                background-color: white;

              "
            >
             Role: <span style="color: #003566">'.$Role.'</span>
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
                padding-top: 5px;
                width: 100%;
                background-color: white;

              "
            >
             Username: <span style="color: #003566">'.$Email.'</span>
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
                padding-top: 5px;
                width: 100%;
                background-color: white;

              "
            >
             Password: <span style="color: #003566">'.$Password.'</span>
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
              '.$companyName.'

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
                    $mail->Host       = 'b2b.flyfarint.com';                     
                    $mail->SMTPAuth   = true;                                  
                    $mail->Username   = 'staff@b2b.flyfarint.com';                    
                    $mail->Password   = '123Next2$';                            
                    $mail->SMTPSecure = 'ssl';            
                    $mail->Port       = 465;                                    

                    //Recipients
                    $mail->setFrom('staff@flyfarint.com', 'Flyway International');
                    $mail->addAddress("$Email", "StaffId : $StaffId");
                    $mail->addCC("$Email", "AgentId : $agentId");
                    $mail->addCC('ceo@flyfarint.com');
                    $mail->addCC('sadman@flyfarint.com');
                    
                    
                    $mail->isHTML(true);                                  
                    $mail->Subject = "Add Staff Confirmation";
                    $mail->Body    = $htmlBody;


                    if(!$mail->Send()) {
                        echo "Mailer Error: " . $mail->ErrorInfo;
                    } else {
                        $response['status']="success";
                        $response['message']="Staff Added Successful";
                    }
                        
                    
                                                  
                }catch (Exception $e) {
                    $response['status']="error";
                    $response['message']="Mail Doesn't Send"; 
                } 
          
            } else {
                $response['status']="error";
  	            $response['message']="Added failed";
            }
        }
        
        echo json_encode($response);
    }else{
         echo json_encode("Data Missing");
    }


?>