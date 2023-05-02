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
    $Platform = $_POST["platform"];
    $cancelBy = $_POST['cancelBy'];

    $createdTime = date("Y-m-d H:i:s");

    $query = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
    $data = mysqli_fetch_assoc($query);
    $agentId = $data["agentId"];
    $pnr = $data['pnr'];
    $system = $data['gds'];
    

    if($system == "FlyHub"){

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
            "BookingID": "'.$pnr.'"
        }',
        CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        "Authorization: Bearer $FlyhubToken"
        ),
        ));

        $FlyHubresponse = curl_exec($curl);

        curl_close($curl);

        $sql = "UPDATE `booking` SET `status`='Cancelled',`lastUpdated`='$createdTime' where bookingId='$bookingId'";
        
        if(isset($agentId)){
            $sql1 = mysqli_query($conn,"SELECT * FROM agent WHERE agentId='$agentId'");
            $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);

            if(!empty($row1)){
                $agentEmail = $row1['email'];
                $companyname = $row1['company'];							
            } 
            
        }

        if ($conn->query($sql) === TRUE) {
            $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`platform`,`actionBy`, `actionAt`)
                             VALUES ('$bookingId','$agentId','Cancalled',' ','$Platform','$cancelBy','$createdTime')");
            $AgentMail ='';
                                
                    $mail = new PHPMailer();

                    try {
                        $mail->isSMTP();                                    
                        $mail->Host       = 'b2b.flyfarint.com';                     
                        $mail->SMTPAuth   = true;                                  
                        $mail->Username   = 'bookingcancel@b2b.flyfarint.com';                    
                        $mail->Password   = '123Next2$';                            
                        $mail->SMTPSecure = 'ssl';            
                        $mail->Port       = 465;                                    

                        //Recipients
                        $mail->setFrom('bookingcancel@flyfarint.com', 'Flyway International');
                        $mail->addAddress("$agentEmail", "AgentId : $agentId");
                        $mail->addCC('otaoperation@flyfarint.com');
                        
                        $mail->isHTML(true);                                  
                        $mail->Subject = "Booking Cancel - $companyname";
                        $mail->Body    = $htmlBody;
                       
                        
                                                        
                    }catch (Exception $e) {
                        
                    }
            $OwnerMail ='';
            $mail1 = new PHPMailer();

              try {
                    $mail1->isSMTP();                                    
                    $mail1->Host       = 'b2b.flyfarint.com';                     
                    $mail1->SMTPAuth   = true;                                  
                    $mail1->Username   = 'bookingcancel@b2b.flyfarint.com';                    
                    $mail1->Password   = '123Next2$';                            
                    $mail1->SMTPSecure = 'ssl';            
                    $mail1->Port       = 465;                                    

                    //Recipients
                    $mail1->setFrom('bookingcancel@flyfarint.com', 'Fly Far Int');
                     $mail1->addAddress("otaoperation@flyfarint.com", "Booking");
                    
                    
                    $mail1->isHTML(true);                                  
                    $mail1->Subject = "New booking request confirmation by - $companyname";
                    $mail1->Body    = $OwnerMail;


                    if(!$mail1->Send()) {
                         $response['status']="success";
                            $response['BookingId']="$bookingId";
                            $response['message']="Booking Cancalled Successfully";
                            $response['error']="Email Not Send Successfully";
                    } else {
                         $response['status']="success";
                            $response['BookingId']="$bookingId";
                            $response['message']="Booking Cancalled Successfully";
                    }
                                                                         
                }catch (Exception $e) {
                    $response['status']="error";
                    $response['message']="Mail Doesn't Send"; 
                }
                
            echo json_encode($response);

            
                   
            
            }
                
    }else if ($system == "Sabre") {
        try{

	$client_id= base64_encode("V1:396724:FD3K:AA");
	//$client_secret = base64_encode("280ff537"); //cert
	$client_secret = base64_encode("FlWy967"); //prod

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
    "confirmationId": "'.$pnr.'",
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
	$sql = "UPDATE `booking` SET `status`='Cancelled',`lastUpdated`='$createdTime' where bookingId='$bookingId'";
	
    if(isset($agentId)){
        $sql1 = mysqli_query($conn,"SELECT * FROM agent WHERE agentId='$agentId'");
        $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);

        if(!empty($row1)){
            $agentEmail = $row1['email'];
            $companyname = $row1['company'];							
        } 
        
    }

	
	if ($conn->query($sql) === TRUE) {
        $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`platform`,`actionBy`, `actionAt`)
                             VALUES ('$bookingId','$agentId','Cancalled',' ','$Platform','$cancelBy','$createdTime')");
        
		$htmlBody ="<html>
<head>
    <title>Booking Cancel</title>
</head>
<body style=' margin: 0;
padding: 0;
font-family: sans-serif; color: #fff;'>
    <div class='container'>

        <div class='card' style=' width: 900px;
        background: linear-gradient(121.52deg, #5D7F9E 0%, #003566 77.49%);
        overflow: hidden;
        position: relative;color: #fff;'>
            <div class='cardHead' style='width: 90%;
            margin: 30px auto;'>
                <div class='logo' style='        width: 110px;
                '>
                    <a href='https://www.flyfarint.com/'><img class='logoImg' width='100%' src='https://cdn.flyfarint.com/logo.png'></a> 
                </div>
                <h1 class='title' style='    color: white;
                font-weight: 500;
                font-size: 20px;
                margin-top: 4%;'>Booking Cancel </h1>
                   
            </div>

            <div class='cardBody' style='      width: 90%;
            margin: 20px auto;
            color: white;'>
                <h4 class='subTitle' style='  font-weight: 500;
                margin-bottom: 20px;'>Dear $companyname , </h4>
                
                <p class='text' style='        font-family: 400;
                font-size: 14px;
                margin: 16px 0;
                width: 90%;
                font-weight: 200;
                line-height: 24px;
                letter-spacing: 0.2px;'>Your Booking ID  <a href='' class='highlight' style='   color: #D1E9FF;
        font-size: 15px;
        margin: 0px 4px; 
        line-height: 24px;
        font-weight: 300 !important;'> $bookingId</a>  Which has been Cancelled.</p>

                <p class='text lastText' style='font-family: 400;
                font-size: 14px;
                margin: 16px 0;
                width: 90%;
                font-weight: 200;
                line-height: 24px;
                letter-spacing: 0.2px;
                margin-top: 50px !important;   
'>If you have any further questions or query, kindly mail us at <span class='highlight' class='highlight' style='color: #D1E9FF;
font-size: 15px;
margin: 0px 4px; '>support@flyfarint.com</span> <br> agency or call us at <span class='highlight' class='highlight' style='color: #D1E9FF;
            font-size: 15px;
            margin: 0px 4px; '>09606912912</span> </p>

            </div>
            <div
            class='cardFooter'
            style='width: 100%; text-align: center; margin-top: 5%'
          >
            <div class='social' style='margin: 0 auto'>
              <a href='https://www.facebook.com/FlyFarInternational/ '
                ><img
                  src='https://cdn.flyfarint.com/fb.png'
                  width='28px'
                  style='margin: 10px'
              /></a>
              <a href='http:// '
                ><img
                  src='https://cdn.flyfarint.com/lin.png'
                  width='28px'
                  style='margin: 10px'
              /></a>
              <a href='http:// '
                ><img
                  src='https://cdn.flyfarint.com/wapp.png '
                  width='28px'
                  style='margin: 10px'
              /></a>
            </div>
            <hr style='width: 60%; background-color: #d1e9ff; opacity: 50%' />
            <div class='address' style='color: #809ab3; text-align: center'>
              <h5>Ka 11/2A, Bashundhora R/A Road, Jagannathpur, Dhaka 1229.</h5>
            </div>
          </div>
        </div>
    </div>
</body>

</html>";
                          
                $mail = new PHPMailer();

                try {
                    $mail->isSMTP();                                    
                    $mail->Host       = 'b2b.flyfarint.com';                     
                    $mail->SMTPAuth   = true;                                  
                    $mail->Username   = 'bookingcancel@b2b.flyfarint.com';                    
                    $mail->Password   = '123Next2$';                            
                    $mail->SMTPSecure = 'ssl';            
                    $mail->Port       = 465;                                    

                    //Recipients
                    $mail->setFrom('bookingcancel@flyfarint.com', 'Flyway International');
                    $mail->addAddress("$agentEmail", "AgentId : $agentId");
                    $mail->addCC('otaoperation@flyfarint.com');
                    
                    $mail->isHTML(true);                                  
                    $mail->Subject = "Booking Cancel Confirmation - $companyname";
                    $mail->Body    = $htmlBody;


                    if(!$mail->Send()) {
                         $response['status']="success";
                            $response['BookingId']="$bookingId";
                            $response['message']="Booking Cancalled Successfully";
                            $response['error']="Email Not Send Successfully";
                    } else {
                         $response['status']="success";
                            $response['BookingId']="$bookingId";
                            $response['message']="Booking Cancalled Successfully";
                    }
                                                                          
                }catch (Exception $e) {
                    $response['status']="error";
                    $response['message']="Mail Doesn't Send"; 
                } 

                echo json_encode($response);
               
    }
        
    }

    

}else{
  echo json_encode("Data Missing");
}