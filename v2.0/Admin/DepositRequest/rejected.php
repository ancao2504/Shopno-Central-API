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
  if ($_SERVER["REQUEST_METHOD"] == "POST"){
          
      $_POST = json_decode(file_get_contents('php://input'), true);
    
        $id = $_POST['id'];
        $agentId =  $_POST['agentId'];
        $actionBy = $_POST['actionBy'];
        $reason = $_POST['reason'];

        //Agent Info
        $agentdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `agent` where agentId='$agentId'")); 
        $companyName = $agentdata['company'];
        $companyEmail = $agentdata['email'];

                  
        //Last Amount     
        $amountsql = "SELECT lastAmount, deposit FROM `agent_ledger` WHERE agentId='$agentId' ORDER BY id DESC LIMIT 1";
        $result1 = mysqli_query($conn, $amountsql);
        $data1 = mysqli_fetch_array($result1); 

        $lastAmount = $data1['lastAmount'];
        $deposit = $data1['deposit'];
        $afterDeposit = $lastAmount + $deposit;

        //Data
        $fetch = "SELECT * FROM deposit_request WHERE id='$id'";
        $result = mysqli_query($conn, $fetch);
        $data = mysqli_fetch_array($result);
            
        $id = $data['id'];
        $agentId = $data['agentId'];
        $staffId = $data['staffId'];

        $depositId = $data['depositId'];    
        $transactionId= $data['transactionId'];
        $ref= $data['ref'];
        $amount = $data['amount'];
        $paymentwaymethod = $data['paymentmethod'];
        $paymentway = $data['paymentway'];
      

        $newAmount = $lastAmount + $amount;  

        $Time = date("Y-m-d H:i:s");
        $DateTime = date("D d M Y h:i A");


        $staffName="";
        $staffsql = mysqli_query($conn, "SELECT * FROM `staffList` where staffId='$staffId'");
        $staffdata = mysqli_fetch_array($staffsql); 
        if(!empty($staffdata)){
          $staffName = $staffdata['name'];
          $Message="Dear $companyName, Your Deposit Request has been
          Rejected amount of $amount BDT on $Time"; 

                $Message1="Dear Fly Far International,  Our
                requested deposit request amount of $amount BDT on $Time which has been rejected"; 
        }else{
          $Message="Dear $companyName, Your Deposit Request has been
                Rejected amount of $amount BDT on $DateTime"; 

                $Message1="Dear Fly Far International, Our Stuff $staffName has been
                requested for deposit request amount of $amount BDT on $Time which has been rejected"; 
        }   
          

        $sql1 = "UPDATE deposit_request SET status='rejected', remarks='$reason',rejectBy='$actionBy',actionAt='$Time' WHERE id='$id' "; 

        if($conn->query($sql1) === TRUE){
          
                if($conn->query($sql1) === TRUE){
                  $AgentMail ='
                  <!DOCTYPE html>
                  <html lang="en">
                    <head>
                      <meta charset="UTF-8" />
                      <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                      <title>Deposit Request Rejected
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
                                Deposit Request Rejected
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
                              Dear '.$companyName.', Your Deposit Request has been
                Rejected amount of '.$amount.' BDT on '.$DateTime.'               
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
                                Rejected Reason: <span style="color: #dc143c"> '.$reason.' </span>
                              
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
                                <a style="color: #003566" href="http://" target="_blank"
                                  >'.$depositId.'</a
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
                      $mail->Username   = 'deposit@b2b.flyfarint.com';                    
                      $mail->Password   = '123Next2$';                            
                      $mail->SMTPSecure = 'ssl';            
                      $mail->Port       = 465;                                    

                      //Recipients
                      $mail->setFrom('deposit@flyfarint.com', 'Fly Far Int');
                      $mail->addAddress("$companyEmail", "AgentId : $agentId");
                      $mail->addCC('ceo@flyfarint.com');
                      $mail->addCC('sadman@flyfarint.com');
                      $mail->addCC("afridi@flyfarint.com");
                      $mail->addCC("parvez@flyfarint.com");
                      
                      $mail->isHTML(true);                                  
                      $mail->Subject = "Deposit request rejected - $companyName";
                      $mail->Body    = $AgentMail;


                      if(!$mail->Send()) {
                          echo "Mailer Error: " . $mail->ErrorInfo;
                      }
                                                                        
                  }catch (Exception $e) {
                      $response['status']="error";
                      $response['message']="Mail Doesn't Send"; 
                  } 


                //Owner maill
                $OwnerMail = '

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
                
                            Deposit Request Rejected
                
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
                            Dear Fly Far International, Our Deposit Request has been
                            Rejected amount of '.$amount.' BDT on '.$DateTime.'             
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
                              Rejected Reason: <span style="color: #dc143c"> '.$reason.' </span>
                            
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
                              <a style="color: #003566" href="http://" target="_blank"
                                >'.$depositId.'</a
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
                            Rejected By:
                            <a style="color: #003566" href="http://" target="_blank"
                              >'.$actionBy.', Fly Far International </a
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
                      $mail1->Username   = 'deposit@b2b.flyfarint.com';                    
                      $mail1->Password   = '123Next2$';                            
                      $mail1->SMTPSecure = 'ssl';            
                      $mail1->Port       = 465;                                    

                      //Recipients
                      $mail1->setFrom('deposit@flyfarint.com', 'Fly Far Int');
                      $mail1->addAddress("otaoperation@flyfarint.com", "Deposit Rejection");
                      

                      
                      $mail1->isHTML(true);                                  
                      $mail1->Subject = "New Deposit Request Rejected for- $companyName";
                      $mail1->Body    = $OwnerMail;


                      if(!$mail1->Send()) {
                          $response['status']="success";
                          $response['message']="Deposit Rejected Successful";
                          $response['error']="Email Not Send";
                      } else {
                          $response['status']="success";
                          $response['message']="Deposit Rejected Successful";
                      }
                          
                      
                                                    
                  }catch (Exception $e) {
                      $response['status']="error";
                      $response['message']="Mail Doesn't Send"; 
                  } 
                                
              }
          
              echo json_encode($response);
              
        }
  }
}else{
  authorization($conn);
}
        
        
?>