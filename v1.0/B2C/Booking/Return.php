<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require("../../vendor/autoload.php");

if ($_SERVER["REQUEST_METHOD"] == "POST"){
       
    $_POST = json_decode(file_get_contents('php://input'), true);  
    
    $bookingId = $_POST["bookingId"];
    $actionBy = $_POST["actionBy"];
    $remarks = $_POST["remarks"];    

    $sqlTicketing = mysqli_query($conn,"SELECT * FROM booking WHERE bookingId='$bookingId'");
    $rowTicketing = mysqli_fetch_array($sqlTicketing,MYSQLI_ASSOC);

    if(!empty($rowTicketing)){
  
        $agentId = $rowTicketing["agentId"];
        $subagentId = $rowTicketing["subagentId"];
        $bookingId = $rowTicketing["bookingId"];
        $staffId = $rowTicketing["staffId"];
        $Airlines = $rowTicketing["airlines"];
        $Type = $rowTicketing['tripType'];
        $netCost = $rowTicketing["netCost"];
        $subagentCost = $rowTicketing["subagentCost"];
        $travelDate = $rowTicketing['travelDate'];
        $From = $rowTicketing['deptFrom'];
        $To = $rowTicketing['arriveTo'];
        $Route = "$From - $To";      
        $pax = $rowTicketing['pax'];
        $GDS = $rowTicketing['gds'];
        $PNR = $rowTicketing['pnr'];
        $Bonus = $rowTicketing['bonus'];  

    }
    
    $createdTime = date('Y-m-d H:i:s');

    $DateTime = date("D d M Y h:i A");

         ///data for mail

         $result = $conn->query("SELECT * FROM subagent where subagentId='$subAgentId' AND agentId= '$agentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
         $agentId = $result[0]['agentId'];
         $companyName = $result[0]['company'];
         $Password = $result[0]['password'];
         $Email = $result[0]['email'];
 
         $result1 = $conn->query("SELECT * FROM wl_content where agentId='$agentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
         $result = $conn->query($sql);
         $agentCompanyName = $result1[0]['company_name'];
         $agentCompanyLogo = $result1[0]['companyImage'];
         $agentCompanyEmail = $result1[0]['email'];
         $agentCompanyPhone = $result1[0]['phone'];
         $agentCompanyAddress = $result1[0]['address'];
         $agentCompanyWebsiteLink = $result1[0]['websitelink'];
         $agentCompanyFbLink = $result1[0]['fb_link'];
         $agentCompanyLinkedinLink = $result1[0]['linkedin_link'];
         $agentCompanyWhatsappNum = $result1[0]['whatsapp_num']; 

    if(isset($agentId)){
        $sql1 = mysqli_query($conn,"SELECT * FROM agent WHERE agentId='$agentId'");
        $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);

        if(!empty($row1)){
            $agentEmail = $row1['email'];
            $companyname = $row1['company'];
            $bonusAmount = $row1['bonus'];							
        } 
        
    }
         

    $sql1 = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' 
            ORDER BY id DESC LIMIT 1");
        $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);        
        if(!empty($row1)){
            $lastAmount = $row1['lastAmount'];							
        }

        $newBalance = $lastAmount + $netCost;

        $subagentsql1 = mysqli_query($conn,"SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' AND subagentId ='$subagentId' 
            ORDER BY id DESC LIMIT 1");
        $subagentrow1 = mysqli_fetch_array($subagentsql1,MYSQLI_ASSOC);        
        if(!empty($subagentrow1)){
            $salastAmount = $subagentrow1['lastAmount'];							
        }

        $sanewBalance = $salastAmount + $subagentCost;
  
         if($Bonus =="yes"){
              $BonusAmountadded = $bonusAmount + 100;
              $conn->query("UPDATE `agent` SET `bonus` = '$BonusAmountadded' where agentId = '$agentId'");
          }

    $sql = "INSERT INTO `agent_ledger`(`agentId`,`returnMoney`, `lastAmount`, `transactionId`, `details`, `reference`, `actionBy`, `createdAt`)
         VALUES ('$agentId','$netCost','$newBalance','$bookingId','Return Money $Type Air Ticket $Route - $Airlines','$bookingId','$actionBy','$createdTime')";

if($conn->query($sql)){
    if($subagentId !=''){
      
      $conn->query("INSERT INTO `subagent_ledger`(`agentId`,`subagentId`,`returnMoney`, `lastAmount`, `transactionId`, `details`, `reference`, `actionBy`, `createdAt`)
         VALUES ('$agentId','$subagentId','$subagentCost','$sanewBalance','$bookingId','Return Money $Type Air Ticket $Route - $Airlines','$bookingId','$actionBy','$createdTime')");
    }
    $conn->query("INSERT INTO `activityLog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                    VALUES ('$bookingId','$agentId','Issue Rejecteed','$remarks','$actionBy','$createdTime')");
                    
    $conn->query("UPDATE `booking` SET `status`='Issue Rejected',`lastUpdated`='$createdTime' where bookingId='$bookingId'");

    if($GDS == "FlyHub"){

        $curlflyhubauth = curl_init();

        curl_setopt_array($curlflyhubauth, array(
        CURLOPT_URL => 'https://api.flyhub.com/api/v1/Authenticate',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
        "username": "ceo@flyfarint.com",
        "apikey": "ENex7c5Ge+0~SGc1t71iccr1xXacDPdK51g=iTm9SlL+de39HF"
        }',
        CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json'
        ),
        ));

        $Tokenresponse = curl_exec($curlflyhubauth);

        $TokenJson = json_decode($Tokenresponse,true);

        $FlyhubToken = $TokenJson['TokenId'];

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.flyhub.com/api/v1/AirCancel',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
            "BookingID": "'.$PNR.'"
        }',
        CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        "Authorization: Bearer $FlyhubToken"
        ),
        ));

        $FlyHubresponse = curl_exec($curl);

        curl_close($curl);

    }else if($GDS == "Sabre"){
      try{
        $client_id= base64_encode("V1:351640:27YK:AA");
        $client_secret = base64_encode("spt5164");

      $token = base64_encode($client_id.":".$client_secret);
      $data='grant_type=client_credentials';

        $headers = array(
          'Authorization: Basic '.$token,
          'Accept: /',
          'Content-Type: application/x-www-form-urlencoded'
        );

        $ch = curl_init();
        //curl_setopt($ch,CURLOPT_URL,"https://api-crt.cert.havail.sabre.com/v2/auth/token");
        curl_setopt($ch,CURLOPT_URL,"https://api.platform.sabre.com/v2/auth/token");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);
        $resf = json_decode($res,1);
        $access_token = $resf['access_token'];

        //print_r($resf);

      }catch (Exception $e){
        
      }


    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.platform.sabre.com/v1/trip/orders/cancelBooking',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "confirmationId": "'.$PNR.'",
        "retrieveBooking": true,
        "cancelAll": true,
        "errorHandlingPolicy": "ALLOW_PARTIAL_CANCEL"
    }',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Conversation-ID: 2021.01.DevStudio',
        "Authorization: Bearer $access_token"
      ),
    ));

    $SabreResponse = curl_exec($curl);
      curl_close($curl);   
    } 
    
    if($subagentId !=''){
      $SubagentEmail = '
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
          background: #FFA84D;
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
          <a style="text-decoration: none; color:#ffffff; font-family: sans-serif; font-size: 25px; padding-top: 20px;" href="https://www.'.$agentCompanyWebsiteLink.'/"> '.$agentCompanyName.'</a>

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
              Booking Issue Request Rejected 
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
              Dear '.$companyName.', Your Booking Issue Request has been Rejected
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
                color: #2564B8;
                padding-right: 20px;
                background-color: white;
              "
            >
             Booking ID: <span>'.$bookingId.'</span>
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
            '.$agentCompanyName.'
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
              >' . $agentCompanyEmail . '
            </a>
            agency or Call us at ' . $agentCompanyPhone . '
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
                href="https://www.' . $agentCompanyWebsiteLink . '/termsandcondition"
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
                href="https://www.' . $agentCompanyWebsiteLink . '/privacypolicy"
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
                 <a href="' . $agentCompanyFbLink . ' "
                 ><img
                    src="https://cdn.flyfarint.com/fb.png"
                    width="25px"
                    style="margin: 10px"
                 /></a>
                 <a href="' . $agentCompanyLinkedinLink . ' "
                 ><img
                    src="https://cdn.flyfarint.com/lin.png"
                    width="25px"
                    style="margin: 10px"
                 /></a>
                 <a href="' . $agentCompanyWhatsappNum . ' "
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
            ' . $agentCompanyAddress . '
          </td>
        </tr>

        </table>
      </div>
    </div>
  </body>
</html>
';

                $mail3 = new PHPMailer();

                try {
                    $mail3->isSMTP();                                    
                    $mail3->Host       = 'flyfarint.com';                     
                    $mail3->SMTPAuth   = true;                                  
                    $mail3->Username   = 'ticketing@b2b.flyfarint.com';                    
                    $mail3->Password   = '123Next2$';                            
                    $mail3->SMTPSecure = 'ssl';            
                    $mail3->Port       = 465;                                    

                    //Recipients
                    $mail3->setFrom('ticketing@flyfarint.com', $agentCompanyName);
                    $mail3->addAddress("$Email", "SubAgentId : $subAgentId");
                    // $mail3->addCC('habib@flyfarint.com');
                    // $mail3->addCC('afridi@flyfarint.com');

                    
                    $mail3->isHTML(true);                                  
                    $mail3->Subject = "Ticket Issue Request rejected by $agentCompanyName";
                    $mail3->Body    = $SubagentEmail;
                    if(!$mail3->Send()) {
                        echo "Mailer Error: " . $mail3->ErrorInfo;
                    }else{
                      
                    }
                                                                         
                }catch (Exception $e) {
                    $response['status']="error";
                    $response['message']="Mail Doesn't Send"; 
                } 


    }
    
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
Ticket Issue Request Rejected 
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
            Dear '.$companyname.', Your Ticket Booking issue request has been cancelled. Your refund amount of balance is already added in your Fly Far International wallet. Thank you for connected with Fly Far International.
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
          "
        >
          Refund Approved By:
          <span style="color: #003566" href="http://" target="_blank"
            >'.$actionBy.'</span>
        
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
              Booking Id:
              <a style="color: #003566" href="http://" target="_blank"
                >'.$bookingId.'</a
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
               Destination: <span style="color: #dc143c">'.$Route.',  '.$Type.'</span> 
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
               Travel Date:  <span style="color: #dc143c">	'.$travelDate.'</span> 
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
               Airline: <span style="color: #dc143c">'.$Airlines.'	</span> 
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
               Pax:  <span style="color: #dc143c">'.$pax.'</span> 
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
               Ticket Cost:  <span style="color: #dc143c">'.$netCost.'
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
               Refund Amount:  <span style="color: #dc143c">'.$netCost.'
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
                    $mail->Host       = 'flyfarint.com';                     
                    $mail->SMTPAuth   = true;                                  
                    $mail->Username   = 'ticketing@b2b.flyfarint.com';                    
                    $mail->Password   = '123Next2$';                            
                    $mail->SMTPSecure = 'ssl';            
                    $mail->Port       = 465;                                    

                    //Recipients
                    $mail->setFrom('ticketing@flyfarint.com', 'Fly Far International');
                    $mail->addAddress("$agentEmail", "AgentId : $agentId");
                    $mail->addCC('ceo@flyfarint.com');
                    $mail->addCC('sadman@flyfarint.com');                   
                    // $mail->addCC('habib@flyfarint.com');
                    // $mail->addCC('afridi@flyfarint.com');
                    
                    $mail->isHTML(true);                                  
                    $mail->Subject = "Ticket Issue Request rejected by Fly Far International";
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
Ticket Issue Request Rejected 
 
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
            Dear Fly Far International, You just cancelled our ticket issue request and refund our ticket amount .   
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
              Refund Approve By:
              <span style="color: #003566" href="http://" target="_blank"
                >'.$actionBy.'</span>
            
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
            Booking Id:
              <a style="color: #003566" href="http://" target="_blank"
                >'.$bookingId.'</a
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
               Destination: <span style="color: #dc143c">'.$Route.',  '.$Type.'</span> 
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
               Travel Date:  <span style="color: #dc143c">'.$travelDate.'	</span> 
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
               Airline: <span style="color: #dc143c">'.$Airlines.'	</span> 
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
               Pax:  <span style="color: #dc143c">'.$pax.'</span> 
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
               Ticket Cost:  <span style="color: #dc143c">'.$netCost.'
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
               Refund Amount:  <span style="color: #dc143c">'.$netCost.'
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
                    $mail1->Host       = 'flyfarint.com';                     
                    $mail1->SMTPAuth   = true;                                  
                    $mail1->Username   = 'ticketing@b2b.flyfarint.com';                    
                    $mail1->Password   = '123Next2$';                            
                    $mail1->SMTPSecure = 'ssl';            
                    $mail1->Port       = 465;                                    

                    //Recipients
                    $mail1->setFrom('ticketing@flyfarint.com', $agentCompanyName);
                     $mail1->addAddress("otaoperation@flyfarint.com");
                    //  $mail1->addCC('habib@flyfarint.com');
                    //  $mail1->addCC('afridi@flyfarint.com');
                    
                    $mail1->isHTML(true);                                  
                    $mail1->Subject = "Ticket Issue Request rejected for $agentCompanyName";
                    $mail1->Body    = $OwnerMail;


                    if(!$mail1->Send()) {
                            $response['status']="success";
                            $response['InvoiceId']="$bookingId";
                            $response['message']="Ticketing Refund Successfully";
                            $response['error']="Ticketing Refund Successfully";
                    } else {
                            $response['status']="success";
                            $response['InvoiceId']="$bookingId";
                            $response['message']="Ticketing Refund Successfully";
                    }
                                                                         
                }catch (Exception $e1) {
                    $response['status']="error";
                    $response['message']="Mail Doesn't Send"; 
                } 

      echo json_encode($response);
    }
    
}

  