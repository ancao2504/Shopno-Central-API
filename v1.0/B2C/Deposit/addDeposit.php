<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
// use PHPMailer\PHPMailer\Exception; 

// require("../vendor/autoload.php");
    if(array_key_exists('userId', $_GET) && array_key_exists('sender', $_GET) && array_key_exists('ref', $_GET) 
        && array_key_exists('receiver', $_GET) && array_key_exists('way', $_GET) && array_key_exists('method', $_GET) 
        && array_key_exists('transactionId', $_GET) && array_key_exists('amount', $_GET) && array_key_exists('staffId', $_GET)){
        
        $userId = $_GET['userId'];    
        $sender = $_GET['sender'];
        $reciever = $_GET['receiver']; 
        $way = $_GET['way'];
        $method = $_GET['method'];
        $transactionId = $_GET['transactionId'];
        $amount = $_GET['amount'];
        $ckDate = $_GET['ckDate'];
        $ref = $_GET['ref'];

        $time = date("Y-m-d H:i:s");
        $DateTime = date("D d M Y h:i A");

        $staffId = $_GET['staffId'];

        $data = json_decode(file_get_contents("php://input"), true);
        
        $fileName  =  $_FILES['file']['name'];
        $tempPath  =  $_FILES['file']['tmp_name'];
        $fileSize  =  $_FILES['file']['size'];

        
        $DepositId ="";
        $sql = "SELECT * FROM deposit_request ORDER BY depositId DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["depositId"]); 
                $number= (int)$outputString + 1;
                $DepositId = "STD$number"; 								
            }
        } else {
            $DepositId ="STD1000";
        }

      

        if(isset($userId)){
            $sql1 = mysqli_query($conn,"SELECT * FROM agent WHERE userId='$userId'");
            $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);

            if(!empty($row1)){
                $agentEmail = $row1['email'];
                $companyname = $row1['company'];
                $Agentname = $row1['name'];						
            } 
            
        }


        $lastAmount;
        $sql2 = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where userId = '$userId' 
            ORDER BY id DESC LIMIT 1");
        $row2 = mysqli_fetch_array($sql2,MYSQLI_ASSOC);        
        if(!empty($row2)){
          $lastAmount = $row2['lastAmount'];						
        }else{
          $lastAmount = 0;
        }

        $newBalance = $lastAmount + $amount;
        
            $Message = "Deposit Request By: $companyname";
            $staffName ="Agent";

        if(empty($fileName)){
                $errorMSG = json_encode(array("message" => "please select image", "status" => false));	
                echo $errorMSG;
            }else{
                $upload_path = "../../../asset/B2C/$userId/Deposit/"; // set upload folder path 
                
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                
                $fileExt = strtolower(pathinfo($fileName,PATHINFO_EXTENSION)); // get image extension
                    
                // valid image extensions
                $valid_extensions = array('jpeg', 'jpg', 'png', 'pdf','PDF','JPG','PNG','JPEG', 'WEBP', 'webp'); 

                $renameFile ="$way-$method-$time-$amount.$fileExt";

                $attach = "$upload_path/".$renameFile;
                                
                // allow valid image file formats
                if(in_array($fileExt, $valid_extensions))
                {				
                    //check file not exist our upload folder path
                    if(!file_exists($upload_path . $fileName))
                    {
                        // check file size '5MB'
                        if($fileSize < 10000000){
                            move_uploaded_file($tempPath, $upload_path . $renameFile); 
                        }
                        else{		
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 10 MB size", "status" => false));	
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
                            $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 10 MB size", "status" => false));	
                            echo $errorMSG;
                        }
                    }
                }
                else
                {		
                    $errorMSG = json_encode(array("message" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed", "status" => false));	
                    echo $errorMSG;		
                }
            }
                    
            // if no error caused, continue ....
            if(!isset($errorMSG)){
                $attachment = $renameFile;
                
                $sql = "INSERT INTO `deposit_request`(
                `userId`,
                `staffId`,
                `depositId`,
                `sender`,
                `reciever`,
                `paymentway`,
                `paymentmethod`,
                `transactionId`,
                `amount`,
                `ref`,
                `chequeIssueDate`,
                `attachment`,
                `platform`,
                `status`,
                `depositBy`,
                `createdAt`
                )
                VALUES( 
                '$userId',
                '$staffId',
                '$DepositId',
                '$sender',
                '$reciever',
                '$way',
                '$method',
                '$transactionId',
                '$amount',
                '$ref',
                '$ckDate',
                '$attachment',
                'B2C',
                'pending',
                '$staffName',
                '$time')";

        if ($conn->query($sql) === TRUE) {


          $html;
//           if($way=='bankTransfer'){
//             $html = '<tr>
//             <td
//               align="center"
//               valign="top"
//               style="
//                 border-collapse: collapse;
//                 border-spacing: 0;
//                 color: #000000;
//                 font-family: sans-serif;
//                 text-align: left;
//                 padding-left: 20px;
//                 font-weight: bold;
//                 padding-top: 20px;
//                 font-size: 13px;
//                 line-height: 18px;
//                 color: #929090;
//                 padding-top: 10px;
//                 width: 100%;
//                 background-color: white;
       
//               "
//             >
//                Send by:  <span style="color: #dc143c">'.$sender.'</span> 
//             </td>
//           </tr>
       
       
//           <tr>
//           <td
//             align="center"
//             valign="top"
//             style="
//               border-collapse: collapse;
//               border-spacing: 0;
//               color: #000000;
//               font-family: sans-serif;
//               text-align: left;
//               padding-left: 20px;
//               font-weight: bold;
//               padding-top: 20px;
//               font-size: 13px;
//               line-height: 18px;
//               color: #929090;
//               padding-top: 10px;
//               width: 100%;
//               background-color: white;
       
//             "
//           >
//              Receive By:  <span style="color: #dc143c">'.$reciever.'</span> 
//           </td>
//         </tr>';
//           } else if($way=='Cheque'){
//             $html = '<tr>
//             <td
//               align="center"
//               valign="top"
//               style="
//                 border-collapse: collapse;
//                 border-spacing: 0;
//                 color: #000000;
//                 font-family: sans-serif;
//                 text-align: left;
//                 padding-left: 20px;
//                 font-weight: bold;
//                 padding-top: 20px;
//                 font-size: 13px;
//                 line-height: 18px;
//                 color: #929090;
//                 padding-top: 10px;
//                 width: 100%;
//                 background-color: white;
       
//               "
//             >
//                Check Number:  <span style="color: #dc143c">'.$transactionId.'</span> 
//             </td>
//           </tr>
       
       
//           <tr>
//           <td
//             align="center"
//             valign="top"
//             style="
//               border-collapse: collapse;
//               border-spacing: 0;
//               color: #000000;
//               font-family: sans-serif;
//               text-align: left;
//               padding-left: 20px;
//               font-weight: bold;
//               padding-top: 20px;
//               font-size: 13px;
//               line-height: 18px;
//               color: #929090;
//               padding-top: 10px;
//               width: 100%;
//               background-color: white;
       
//             "
//           >
//              Bank Name:  <span style="color: #dc143c">'.$method.'</span> 
//           </td>
//         </tr>
        
//         <tr>
//         <td
//           align="center"
//           valign="top"
//           style="
//             border-collapse: collapse;
//             border-spacing: 0;
//             color: #000000;
//             font-family: sans-serif;
//             text-align: left;
//             padding-left: 20px;
//             font-weight: bold;
//             padding-top: 20px;
//             font-size: 13px;
//             line-height: 18px;
//             color: #929090;
//             padding-top: 10px;
//             width: 100%;
//             background-color: white;
       
//           "
//         >
//            Check Issue Date:  <span style="color: #dc143c">'.$ckDate.'</span> 
//         </td>
//        </tr>
//         ';
//           }
       
//           else if($way=='Cash'){
//             $html = '
//           <tr>
//           <td
//             align="center"
//             valign="top"
//             style="
//               border-collapse: collapse;
//               border-spacing: 0;
//               color: #000000;
//               font-family: sans-serif;
//               text-align: left;
//               padding-left: 20px;
//               font-weight: bold;
//               padding-top: 20px;
//               font-size: 13px;
//               line-height: 18px;
//               color: #929090;
//               padding-top: 10px;
//               width: 100%;
//               background-color: white;
       
//             "
//           >
//              Sender Name:  <span style="color: #dc143c">'.$sender.'</span> 
//           </td>
//         </tr>
        
//         <tr>
//         <td
//           align="center"
//           valign="top"
//           style="
//             border-collapse: collapse;
//             border-spacing: 0;
//             color: #000000;
//             font-family: sans-serif;
//             text-align: left;
//             padding-left: 20px;
//             font-weight: bold;
//             padding-top: 20px;
//             font-size: 13px;
//             line-height: 18px;
//             color: #929090;
//             padding-top: 10px;
//             width: 100%;
//             background-color: white;
       
//           "
//         >
//            Receiver Name:  <span style="color: #dc143c">'.$reciever.'</span> 
//         </td>
//        </tr>
//         ';}   else if($way=='mobileTransfer'){
//           $html = '
//         <tr>
//         <td
//           align="center"
//           valign="top"
//           style="
//             border-collapse: collapse;
//             border-spacing: 0;
//             color: #000000;
//             font-family: sans-serif;
//             text-align: left;
//             padding-left: 20px;
//             font-weight: bold;
//             padding-top: 20px;
//             font-size: 13px;
//             line-height: 18px;
//             color: #929090;
//             padding-top: 10px;
//             width: 100%;
//             background-color: white;
       
//           "
//         >
//            Payment Method:  <span style="color: #dc143c">'.$method.'</span> 
//         </td>
//        </tr>
       
//        <tr>
//        <td
//         align="center"
//         valign="top"
//         style="
//           border-collapse: collapse;
//           border-spacing: 0; 
//           color: #000000;
//           font-family: sans-serif;
//           text-align: left;
//           padding-left: 20px;
//           font-weight: bold;
//           padding-top: 20px;
//           font-size: 13px;
//           line-height: 18px;
//           color: #929090;
//           padding-top: 10px;
//           width: 100%;
//           background-color: white;
       
//         "
//        >
//        Pay Using Account Number:  <span style="color: #dc143c">'.$sender.'</span> 
//        </td>
//        </tr>
//        ';}


//  $agentMail ='
//  <!DOCTYPE html>
//  <html lang="en">
//    <head>
//      <meta charset="UTF-8" />
//      <meta http-equiv="X-UA-Compatible" content="IE=edge" />
//      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
//      <title>Deposit Request</title>
//    </head>
//    <body>
//      <div class="div" style="width: 650px; height: 100vh; margin: 0 auto">
//        <div style="width: 650px; height: 150px; background: #c5e0ff;
//        border-radius: 10px;">
//          <table
//            border="0"
//            cellpadding="0"
//            cellspacing="0"
//            align="center"
//            style="
//              border-collapse: collapse;
//              border-spacing: 0;
//              padding: 0;
//              width: 650px;
//              border-radius: 10px;
//            "
//          >
//            <tr>
//              <td
//                align="center"
//                valign="top"
//                style="
//                  border-collapse: collapse;
//                  border-spacing: 0;
//                  color: #000000;
//                  font-family: sans-serif;
//                  font-weight: bold;
//                  font-size: 20px;
//                  line-height: 38px;
//                  padding-top: 20px;
//                  padding-bottom: 10px;
//                "
//              >
//              <img src="https://flyway.api.flyfarint.com/asset/ownerlogo/Logo.png" width="100" height="80" />
//              </td>
//            </tr>
//          </table>
 
//          <table
//            border="0"
//            cellpadding="0"
//            cellspacing="0"
//            align="center"
//            bgcolor="white"
//            style="
//              border-collapse: collapse;
//              border-spacing: 0;
//              padding: 0;
//              width: 550px;
//            "
//          >
//            <tr>
//              <td
//                align="center"
//                valign="top"
//                style="
//                  border-collapse: collapse;
//                  border-spacing: 0;
//                  color: #000000;
//                  font-family: sans-serif;
//                  text-align: left;
//                  padding-left: 20px;
//                  font-weight: bold;
//                  font-size: 19px;
//                  line-height: 38px;
//                  padding-top: 15px;
//                  background-color: white;
//                "
//              >
//                New Deposit Request
//              </td>
//            </tr>
//            <tr>
//              <td
//                align="center"
//                valign="top"
//                style="
//                  border-collapse: collapse;
//                  border-spacing: 0;
//                  font-family: sans-serif;
//                  text-align: left;
//                  padding-left: 20px;
//                  font-weight: bold;
//                  padding-top: 10px;
//                  font-size: 12px;
//                  line-height: 18px;
//                  color: #929090;
//                  padding-right: 20px;
//                  background-color: white;
//                "
//              >
//                Dear Flyway International, We Send you new deposit request amount
//                of '.$amount.' BDT.
//              </td>
//            </tr>
 
//            <tr>
//              <td
//                align="center"
//                valign="top"
//                style="
//                  border-collapse: collapse;
//                  border-spacing: 0;
//                  color: #000000;
//                  font-family: sans-serif;
//                  text-align: left;
//                  padding-left: 20px;
//                  font-weight: bold;
//                  padding-top: 20px;
//                  font-size: 13px;
//                  line-height: 18px;
//                  color: #929090;
//                  padding-top: 20px;
//                  width: 100%;
//                "
//              >
//                Deposit ID:
//                <a style="color: #2564b8" href="http://" target="_blank"
//                  >'.$DepositId.'</a
//                >
//              </td>
//            </tr>
 
//            <tr>
//              <td
//                align="center"
//                valign="top"
//                style="
//                  border-collapse: collapse;
//                  border-spacing: 0;
//                  color: #000000;
//                  font-family: sans-serif;
//                  text-align: left;
//                  padding-left: 20px;
//                  font-weight: bold;
//                  padding-top: 20px;
//                  font-size: 12px;
//                  line-height: 18px;
//                  color: #929090;
//                  padding-top: 20px;
//                  width: 100%;
//                  background-color: white;
//                "
//              >
//                Sincerely,
//              </td>
//            </tr>
 
//            <tr>
//              <td
//                align="center"
//                valign="top"
//                style="
//                  border-collapse: collapse;
//                  border-spacing: 0;
//                  color: #000000;
//                  font-family: sans-serif;
//                  text-align: left;
//                  padding-left: 20px;
//                  font-weight: bold;
//                  font-size: 12px;
//                  line-height: 18px;
//                  color: #929090;
//                  width: 100%;
//                  background-color: white;
//                  padding-bottom: 20px;
//                "
//              >
//               '.$companyname.'
//              </td>
//            </tr>
//          </table>
//        </div>
//      </div>
//    </body>
//  </html>
 
// '; 
            
                          
//                 $mail = new PHPMailer();

//                 try {
//                     $mail->isSMTP();                                    
//                     $mail->Host       = 'mail.flyfarint.net';                     
//                     $mail->SMTPAuth   = true;                                  
//                     $mail->Username   = 'deposit@flywaytravel.com.bd';                    
//                     $mail->Password   = '123Next2$';                        
//                     $mail->SMTPSecure = 'ssl'; 
//                     $mail->Port       = 465;
                                 

//                     //Recipients
//                     $mail->setFrom('deposit@flywaytravel.com.bd', "Flyway International");
//                     //$mail->addAddress("reservation@flywayint.com", "AgentId : $agentId");
//                     $mail->addAddress("afridi@flyfarint.com", "AgentId : $agentId");
//                     $mail->addCC('habib@flyfarint.com');

                   
//                     $mail->isHTML(true);                                  
//                     $mail->Subject = "Deposit Request Confirmation - $companyname";
//                     $mail->Body    = $agentMail;
//                     $mail->addAttachment($attach);


//                     if(!$mail->Send()) {
//                         echo "Mailer Error: " . $mail->ErrorInfo;
//                     }
                                                                         
//                 }catch (Exception $e) {
//                     $response['status']="error";
//                     $response['message']="Mail Doesn't Send"; 
//                 } 
                
//          $OwnerMail ='
//          <!DOCTYPE html>
//          <html lang="en">
//            <head>
//              <meta charset="UTF-8" />
//              <meta http-equiv="X-UA-Compatible" content="IE=edge" />
//              <meta name="viewport" content="width=device-width, initial-scale=1.0" />
//              <title>Deposit Request</title>
//            </head>
//            <body>
//              <div class="div" style="width: 650px; height: 100vh; margin: 0 auto">
//                <div
//                  style="
//                    width: 650px;
//                    height: 150px;
//                    background: #c5e0ff;
//                    border-radius: 10px;
//                  "
//                >
//                <table
//                border="0"
//                cellpadding="0"
//                cellspacing="0"
//                align="center"
//                style="
//                  border-collapse: collapse;
//                  border-spacing: 0;
//                  padding: 0;
//                  width: 650px;
//                  border-radius: 10px;
//                "
//              >
//                <tr>
//                  <td
//                    align="center"
//                    valign="top"
//                    style="
//                      border-collapse: collapse;
//                      border-spacing: 0;
//                      color: #000000;
//                      font-family: sans-serif;
//                      font-weight: bold;
//                      font-size: 20px;
//                      line-height: 38px;
//                      padding-top: 20px;
//                      padding-bottom: 10px;
         
//                    "
//                  >
//                  <img src="https://flyway.api.flyfarint.com/asset/ownerlogo/Logo.png" width="100" height="80" />
        
//                  </td>
//                </tr>
//              </table>
         
//                  <table
//                    border="0"
//                    cellpadding="0"
//                    cellspacing="0"
//                    align="center"
//                    bgcolor="white"
//                    style="
//                      border-collapse: collapse;
//                      border-spacing: 0;
//                      padding: 0;
//                      width: 550px;
//                    "
//                  >
//                    <tr>
//                      <td
//                        align="center"
//                        valign="top"
//                        style="
//                          border-collapse: collapse;
//                          border-spacing: 0;
//                          color: #000000;
//                          font-family: sans-serif;
//                          text-align: left;
//                          padding-left: 20px;
//                          font-weight: bold;
//                          font-size: 19px;
//                          line-height: 38px;
//                          padding-top: 10px;
//                          background-color: white;
//                        "
//                      >
//                        Deposit Request Confirmation 
//                      </td>
//                    </tr>
//                    <tr>
//                      <td
//                        align="center"
//                        valign="top"
//                        style="
//                          border-collapse: collapse;
//                          border-spacing: 0;
//                          font-family: sans-serif;
//                          text-align: left;
//                          padding-left: 20px;
//                          font-weight: bold;
//                          padding-top: 15px;
//                          font-size: 12px;
//                          line-height: 18px;
//                          color: #929090;
//                          padding-right: 20px;
//                          background-color: white;
//                        "
//                      >
//                        Dear '.$companyname.', Your new deposit request amount of '.$amount.' BDT has been placed, please wait for while, for added your deposit amount into your wallet.
//                      </td>
//                    </tr>
         
//                    <tr>
//                      <td
//                        align="center"
//                        valign="top"
//                        style="
//                          border-collapse: collapse;
//                          border-spacing: 0;
//                          font-family: sans-serif;
//                          text-align: left;
//                          padding-left: 20px;
//                          font-weight: bold;
//                          padding-top: 5px;
//                          font-size: 12px;
//                          line-height: 18px;
//                          color: #2564B8;
//                          padding-right: 20px;
//                          background-color: white;
//                        "
//                      >
//                       Deposit ID: <span>'.$DepositId.'</span>
//                    </tr>
         
         
//                    <tr>
//                      <td
//                        align="center"
//                        valign="top"
//                        style="
//                          border-collapse: collapse;
//                          border-spacing: 0;
//                          color: #000000;
//                          font-family: sans-serif;
//                          text-align: left;
//                          padding-left: 20px;
//                          font-weight: bold;
//                          padding-top: 20px;
//                          font-size: 12px;
//                          line-height: 18px;
//                          color: #929090;
//                          padding-top: 20px;
//                          width: 100%;
//                          background-color: white;
//                        "
//                      >
//                        If you have any questions, just contact us we are always happy to
//                        help you out.
//                      </td>
//                    </tr>
         
//                    <tr>
//                    <td
//                      align="center"
//                      valign="top"
//                      style="
//                        border-collapse: collapse;
//                        border-spacing: 0;
//                        color: #000000;
//                        font-family: sans-serif;
//                        text-align: left;
//                        padding-left: 20px;
//                        font-weight: bold;
//                        padding-top: 20px;
//                        font-size: 13px;
//                        line-height: 18px;
//                        color: #929090;
//                        padding-top: 20px;
//                        width: 100%;
//                        background-color: white;
//                      "
//                    >
//                      Sincerely,
//                    </td>
//                  </tr>
       
//                  <tr>
//                    <td
//                      align="center"
//                      valign="top"
//                      style="
//                        border-collapse: collapse;
//                        border-spacing: 0;
//                        color: #000000;
//                        font-family: sans-serif;
//                        text-align: left;
//                        padding-left: 20px;
//                        font-weight: bold;
//                        font-size: 13px;
//                        line-height: 18px;
//                        color: #929090;
//                        width: 100%;
//                        background-color: white;
//                        padding-bottom: 20px;
//                      "
//                    >
//                     Flyway International
//                    </td>
//                  </tr>
       
//                  <tr>
//                    <td
//                      align="center"
//                      valign="top"
//                      style="
//                        border-collapse: collapse;
//                        border-spacing: 0;
//                        color: #ffffff;
//                        font-family: sans-serif;
//                        text-align: center;
//                        font-weight: 600;
//                        font-size: 14px;
//                        color: #ffffff;
//                        padding-top: 15px;
//                        background-color: #2564B8;
//                      "
//                    >
//                      Need more help?
//                    </td>
//                  </tr>
       
//                  <tr>
//                    <td
//                      align="center"
//                      valign="top"
//                      style="
//                        border-collapse: collapse;
//                        border-spacing: 0;
//                        color: #ffffff;
//                        font-family: sans-serif;
//                        text-align: center;
//                        font-size: 12px;
//                        color: #ffffff;
//                        padding-top: 8px;
//                        padding-bottom: 20px;
//                        padding-left: 30px;
//                        padding-right: 30px;
//                        background-color: #2564B8;
//                      "
//                    >
//                      Mail us at
//                      <a
//                        style="color: white; font-size: 13px; text-decoration: none"
//                        href="http://"
//                        target="_blank"
//                        >reservation@flywayint.com
//                      </a>
//                      agency or Call us at 01400001101-04
//                    </td>
//                  </tr>
       
//                  <tr>
//                  <td
//                    valign="top"
//                    align="left"
//                    style="
//                      border-collapse: collapse;
//                      border-spacing: 0;
//                      color: #000000;
//                      font-family: sans-serif;
//                      text-align: left;
//                      font-weight: bold;
//                      font-size: 12px;
//                      line-height: 18px;
//                      color: #929090;
//                    "
//                  >
//                    <p>
//                      <a
//                        style="
//                          font-weight: bold;
//                          font-size: 12px;
//                          line-height: 15px;
//                          color: #222222;
//                        "
//                        href="https://flywaytravel.com.bd/terms"
//                        >Tearms & Conditions</a
//                      >
//                      <a
//                        style="
//                          font-weight: bold;
//                          font-size: 12px;
//                          line-height: 15px;
//                          color: #222222;
//                          padding-left: 10px;
//                        "
//                        href="https://flywaytravel.com.bd/privacy"
//                        >Privacy Policy</a
//                      >
//                    </p>
//                  </td>
//                </tr>
//                  <tr>
//                  <td
//                    align="center"
//                    valign="top"
//                    style="
//                      border-collapse: collapse;
//                      border-spacing: 0;
//                      font-family: sans-serif;
//                      text-align: center;
//                      padding-left: 20px;
//                      font-weight: bold;
//                      font-size: 12px;
//                      line-height: 18px;
//                      color: #929090;
//                      padding-right: 20px;
//                    "
//                  >
//                    <a href="https://m.facebook.com/flywayt"
//                      ><img
//                        src="https://cdn.flyfarint.com/fb.png"
//                        width="25px"
//                        style="margin: 10px"
//                    /></a>
//                    <a href="https://www.linkedin.com/company/flyway.travel"
//                      ><img
//                        src="https://cdn.flyfarint.com/lin.png"
//                        width="25px"
//                        style="margin: 10px"
//                    /></a>
//                    <a href="https://wa.me/+8801400001101"
//                      ><img
//                        src="https://cdn.flyfarint.com/wapp.png "
//                        width="25px"
//                        style="margin: 10px"
//                    /></a>
//                  </td>
//                </tr>
       
//                  <tr>
//                    <td
//                      align="center"
//                      valign="top"
//                      style="
//                        border-collapse: collapse;
//                        border-spacing: 0;
//                        color: #929090;
//                        font-family: sans-serif;
//                        text-align: center;
//                        font-weight: 500;
//                        font-size: 12px;
//                        padding-top: 5px;
//                        padding-bottom: 5px;
//                        padding-left: 10px;
//                        padding-right: 10px;
//                      "
//                    >
//                      Ka 11/2A, Bashundhora R/A Road, Jagannathpur, Dhaka 1229
//                    </td>
//                  </tr>
//                </table>
//              </div>
//            </div>
//          </body>
//        </html>
         

//         ';
      
                          
//                 $mail1 = new PHPMailer();

//                 try {
//                     $mail1->isSMTP();                                    
//                     $mail1->Host       = 'b2b.flyfarint.com';                     
//                     $mail1->SMTPAuth   = true;                                  
//                     $mail1->Username   = 'deposit@b2b.flyfarint.com';                    
//                     $mail1->Password   = '123Next2$';                            
//                     $mail1->SMTPSecure = 'ssl';            
//                     $mail1->Port       = 465;                                    

//                     //Recipients
//                     $mail1->setFrom('deposit@flyfarint.com', 'Flyway International');
//                     $mail1->addAddress("$agentEmail", "$agentId");
//                     $mail1->addCC("afridi@flyfarint.com");
//                     $mail1->addCC("habib@flyfarint.com");


                    
//                     $mail1->isHTML(true);                                  
//                     $mail1->Subject = "New Deposit Request by - $companyname";
//                     $mail1->Body    = $OwnerMail;
//                     $mail1->addAttachment($attach);


//                     if(!$mail1->Send()) {
//                         $response['status']="success";
//                         $response['DepositId']="$DepositId";
//                         $response['message']="Deposit Request Successfully";
//                         $response['error']="Mail Doesn't Send";
//                     } else {
//                             $response['status']="success";
//                             $response['DepositId']="$DepositId";
//                             $response['message']="Deposit Request Successfully";
//                     }
                                                                         
//                 }catch (Exception $e1) {
//                     $response['status']="error";
//                     $response['message']="Mail Doesn't Send"; 
//                 } 

                $response['status']="success";
                        $response['DepositId']="$DepositId";
                        $response['message']="Deposit Request Successfully";           
         
            }
       
        echo json_encode($response);
    }
}



?>