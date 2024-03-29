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


    if ($_SERVER["REQUEST_METHOD"] == "POST"){

        $_POST = json_decode($_REQUEST['data'], true);
        
        $fileName  =  $_FILES['file']['name'];
        $tempPath  =  $_FILES['file']['tmp_name'];
        $fileSize  =  $_FILES['file']['size'];

        
        $othersId ="";
        $sql1 = "SELECT * FROM bookingothers ORDER BY othersId DESC LIMIT 1";
        $result = $conn->query($sql1);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["othersId"]); 
                $number= (int)$outputString + 1;
                $othersId = "STO$number"; 								
            }
        } else {
                $othersId ="STO1000";
        }

       
        $agentId = $_POST['agentId'];

        $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
        $data = mysqli_fetch_array($query,MYSQLI_ASSOC);

        $companyname = $data['company'];
        $compnayphone = $data['phone'];
        $agentEmail = $data['email'];
        
        $reference = $_POST['reference'];
        $amount = $_POST['amount'];
        $description =  str_replace("'", "''",$_POST['description']);
        $serviceType = $_POST['serviceType'];
        $createdBy = $_POST['createdBy'];
        $createdAt = date('Y-m-d H:i:s');


        if(empty($fileName)){
	$errorMSG = json_encode(array("message" => "please select image", "status" => false));	
	echo $errorMSG;
}else{
	$upload_path = "../../../asset/Admin/OthersDocuments/$agentId/"; // set upload folder path 
	
	if (!file_exists($upload_path)) {
    	mkdir($upload_path, 0777, true);
	}
	
	$fileExt = strtolower(pathinfo($fileName,PATHINFO_EXTENSION)); // get image extension
		
	// valid image extensions
	$valid_extensions = array('png', 'PNG','jpeg','JPG','jpg', 'JPEG','pdf','PDF'); 

  $renameFile ="$description-$createdAt-attachment.$fileExt";
  $attach = "$upload_path/".$renameFile;
	
					
	// allow valid image file formats
	if(in_array($fileExt, $valid_extensions)){				
		//check file not exist our upload folder path
		if(!file_exists($upload_path . $fileName)){
				if($fileSize < 10000000){
					move_uploaded_file($tempPath, $upload_path . $renameFile); 
				}
				else{		
					$errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 10 MB size", "status" => "error"));	
					echo $errorMSG;
				}
		}
		else
		{		
			// check file size '5MB'
			if($fileSize < 10000000){
				move_uploaded_file($tempPath, $upload_path . $renameFile);
			}
			else{		
				$errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 10 MB size", "status" => "error"));	
				echo $errorMSG;
			}
		}
	}
	else
	{		
		$errorMSG = json_encode(array("message" => "Sorry, only  PNG,PDF,JPG files are allowed", "status" => "error"));	
		echo $errorMSG;		
	}
}
		

if(!isset($errorMSG)){
    $fileUrl = "https://shopno.api.flyfarint.com/asset/Admin/OthersDocuments/$agentId/$renameFile";
    
	$sql = "INSERT INTO `bookingothers`(
            `othersId`,
            `agentId`,
            `reference`,
            `amount`,
            `description`,
            `serviceType`,
            `attachment`,
            `companyname`,
            `companyphone`,
            `createdBy`,
            `createdAt`
        )
        VALUES(
            '$othersId',
            '$agentId',
            '$reference',
            '$amount',
            '$description',
            '$serviceType',
            '$fileUrl',
            '$companyname',
            '$compnayphone',
            '$createdBy',
            '$createdAt'
        )";

        $sql1 = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' 
            ORDER BY id DESC LIMIT 1");
        $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);        
        if(!empty($row1)){
            $lastAmount = $row1['lastAmount'];							
        }

        
        if ($lastAmount >= $amount) {
          $colum = '';
          if ($serviceType == 'Void') {
              $colum = 'void';
              $newBalance = $lastAmount + $amount;
          } elseif ($serviceType == 'Reissue') {
              $colum = 'reissue';
              $newBalance = $lastAmount - $amount;
          } elseif ($serviceType == 'Return') {
              $colum = 'returnMoney';
              $newBalance = $lastAmount - $amount;
          } elseif ($serviceType == 'Air Ticket') {
              $colum = 'purchase';
              $newBalance = $lastAmount - $amount;
          }
      } else {
          $response['status'] = 'error';
          $response['message'] = 'Your invoice has been failed due to insufficient balanced';
          echo json_encode($response);
          exit();
      }

      if ($serviceType == 'Bonus') {
          $colum = 'bonus';
          $newBalance = $lastAmount + $amount;
      } elseif ($serviceType == 'Refund') {
          $colum = 'refund';
          $newBalance = $lastAmount + $amount;
      } else {
          $colum = 'others';
          $newBalance = $lastAmount - $amount;
      }

       
        if ($conn->query($sql) === TRUE) {

            $LeaderUpdate = "INSERT INTO `agent_ledger`(`agentId`,`$colum`, `lastAmount`, `transactionId`, `details`, `reference`, `actionBy`, `createdAt`)
                VALUES ('$agentId','$amount','$newBalance','$reference','$description','$othersId','$createdBy','$createdAt')";
                
if ($conn->query($LeaderUpdate) === true) {

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
                  Ticket Request Approved
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
                  Dear '.$companyname.', You have been Requested for Air a Ticket which has
                  been accepted. Thank you for stay connected with Fly Far
                  International.
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
                  Others Id:
                  <a style="color: #003566" href="http://" target="_blank"
                    >'.$othersId.'</a
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
                  Description:
                  <span style="color: #dc143c">'.$description.'</span>
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
                  Service Type: <span style="color: #dc143c">'.$serviceType.'</span>
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
                  Cost: <span style="color: #dc143c">'.$amount.'</span>
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
        $mail->Username   = 'reissue@b2b.flyfarint.com';
        $mail->Password   = '123Next2$';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        //Recipients
        $mail->setFrom('reissue@flyfarint.com', 'Flyway International');
        $mail->addAddress("$agentEmail", "AgentId : $agentId");
        $mail->addCC('otaoperation@flyfarint.com');        
        $mail->addAttachment($attach);

        $mail->isHTML(true);
        $mail->Subject = "Ticket Request Approve Confirmation - $companyname";
        $mail->Body    = $agentMail;
        if (!$mail->Send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        }
    } catch (Exception $e) {
       
    }

      $OwnerMail ='
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
              Ticket Confirmation
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
              Dear Flyway International, We have Requested for Air Ticket Which
              has been Approved.
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
              Ticket Request Approved By:
              <span style="color: #003566" href="http://" target="_blank"
                >'.$createdBy.'</span
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
              "
            >
              Others Id:
              <a style="color: #003566" href="http://" target="_blank"
                >'.$othersId.'</a
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
              Description:
              <span style="color: #dc143c">'.$description.'</span>
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
              Service Type: <span style="color: #dc143c">'.$serviceType.'</span>
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
              Cost: <span style="color: #dc143c">'.$amount.'</span>
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
              '.$companyname.'
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
        $mail1->Host       = 'b2b.flyfarint.com';
        $mail1->SMTPAuth   = true;
        $mail1->Username   = 'reissue@b2b.flyfarint.com';
        $mail1->Password   = '123Next2$';
        $mail1->SMTPSecure = 'ssl';
        $mail1->Port       = 465;

        //Recipients
        $mail1->setFrom('reissue@flyfarint.com', 'Flyway International');
        $mail1->addAddress("otaoperation@flyfarint.com", "Reissue Ticket Request");       
        $mail1->addAttachment($attach);


        $mail1->isHTML(true);
        $mail1->Subject = "New Ticket Request Approve by - $companyname";
        $mail1->Body    = $OwnerMail;


        if (!$mail1->Send()) {
            $response['status']="success";
            $response['InvoiceId']="$othersId";
            $response['message']="Ticket Reissue Request Successfully";
            $response['error']="Ticket Reissue Request Successfully";
             echo json_encode($response);
        } else {
            $response['status']="success";
            $response['InvoiceId']="$othersId";
            $response['message']="Ticket Reissue Request Successfully";
             echo json_encode($response);
             
        }
    } catch (Exception $e1) {
        $response['status']="error";
        $response['message']="Mail Doesn't Send";
         echo json_encode($response);
    }
}

                 
            }
                    
        }


}

        
    
        
    
?>