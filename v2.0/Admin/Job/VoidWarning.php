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

$sql = "SELECT * FROM booking";
$result = $conn->query($sql);
//print_r($result);
if($result->num_rows>0){
    while($row = $result->fetch_assoc()){
if ($row['status']=='Void In Processing') {
    $AgentId = $row['agentId'];
    $BookingId = $row['bookingId'];
    $Destination = $row['deptFrom'] .' - '. $row['arriveTo'] .', '. $row['tripType'];
    $Airlines = $row['airlines'];
    $TraDate = $row['travelDate'];
    $TotalPAX = $row['pax'];
    $InvoiceCost = $row['netCost'];
    $BookingDate = $row['bookedAt'];
    
    $agentData = $conn->query("SELECT * FROM agent WHERE agentId='$AgentId'")->fetch_all(MYSQLI_ASSOC);
    $CompanyName = $agentData[0]['company'];
    $AgentName = $agentData[0]['name'];
    $Address = $agentData[0]['companyadd'];
    

    $currentTime = new DateTime();
    $depTime = new DateTime($BookingDate);
    $interval = $depTime->diff($currentTime);
    
    $remHours = $interval->h;
    $remMinutes = $interval->i;
    $TravelDate = date("D M Y H:i A", strtotime($TraDate));

    // echo $remHours;
    // echo $remMinutes;
    if($remHours !='0' && isset($remMinutes)){
        $RemainingTime = $remHours." h ".$remMinutes." m";
      }else{
    $RemainingTime = $remMinutes." m";
}

    if(($remHours > 0 && $remMinutes > 5) || ($remHours == 0 && $remMinutes == 10) || ($remHours == 0 && $remMinutes == 15) || ($remHours == 0 && $remMinutes == 20) || ($remHours == 0 && $remMinutes == 30) || ($remHours == 0 && $remMinutes == 40)|| ($remHours == 0 && $remMinutes == 45)|| ($remHours == 0 && $remMinutes == 50)|| ($remHours == 0 && $remMinutes == 55)){
        
        $ownerMail ='
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
                    Delay Service Alert for Pending Void
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
                    Agent ID:
                      <a style="color: #003566" href="http://" target="_blank"
                        >'.$AgentId.'</a
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
                    Agency Name:
                      <a style="color: #003566" href="http://" target="_blank"
                        >'.$CompanyName.'</a
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
                    Agent Name:
                      <a style="color: #003566" href="http://" target="_blank"
                        >'.$AgentName.'</a
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
                    Booking ID:
                      <a style="color: #003566" href="http://" target="_blank"
                        >'.$BookingId.'</a
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
                    Destination:
                      <a style="color: #003566" href="http://" target="_blank"
                        >'.$Destination.'</a
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
                    Airlines:
                      <a style="color: #003566" href="http://" target="_blank"
                        >'.$Airlines.'</a
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
                    Travel Date:
                      <a style="color: #003566" href="http://" target="_blank"
                        >'.$TravelDate.'</a
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
                    Total PAX:
                      <a style="color: #003566" href="http://" target="_blank"
                        >'.$TotalPAX.'</a
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
                    Invoice Cost:
                      <a style="color: #003566" href="http://" target="_blank"
                        >'.$InvoiceCost.' BDT</a
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
                        padding-bottom: 20px;
                      "
                    >
                      '.$CompanyName.'
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
            $mail1->Host = 'b2b.flyfarint.com';
            $mail1->SMTPAuth = true;
            $mail1->Username = 'job@b2b.flyfarint.com';
            $mail1->Password = '123Next2$';
            $mail1->SMTPSecure = 'ssl';
            $mail1->Port = 465;

            //Recipients
            $mail1->setFrom('warning@b2b.flyfarint.com', 'Void Approval Alert');
            $mail1->addAddress("otaoperation@flyfarint.com", "Fly Far International");
            $mail1->addCC('habib@flyfarint.com');


            $mail1->isHTML(true);
            $mail1->Subject = "$RemainingTime Void Request Pending Alert $BookingId";
            $mail1->Body = $ownerMail;
            if (!$mail1->Send()) {
                echo "Mailer Error: " . $mail1->ErrorInfo;
            } else {

            }

        } catch (Exception $e) {
            $response['status'] = "error";
            $response['message'] = "Mail Doesn't Send";
        }
    }
    echo json_encode($response);
 
}
    }
}
