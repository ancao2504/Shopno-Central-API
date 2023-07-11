<?php
include("../../config.php");

use Dompdf\Dompdf;
use Dompdf\Options;

use PHPMailer\PHPMailer\PHPMailer;

require '../../vendor/autoload.php';


$reportDate = date('jS F, Y', strtotime('-1 day'));
$sql = "SELECT a.company AS agency ,a.credit AS credit,b.*
FROM booking b
LEFT JOIN agent a ON b.agentId=a.agentId 
WHERE DATE(travelDate) >= CURDATE() 
AND 
DATE(travelDate) <= DATE_ADD(CURDATE(), INTERVAL 14 DAY)
ORDER BY travelDate ASC";

$result=$conn->query($sql)->fetch_all(MYSQLI_ASSOC);
// echo json_encode($result);



$tableData='';

foreach($result as $r)
{   
    $bookingId=$r['bookingId'];
    $agency=$r['agency'];
    $route=$r['deptFrom'].$r['arriveTo'];
    $pax=$r['pax'];
    $netCost=$r['netCost'];
    $flightDate=$r['travelDate'];
    $credit=$r["credit"];
    $credit=$r["credit"];

    $tableData=$tableData.'
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
                padding-top: 20px;
                font-size: 12px;
              "
            >
            '.$bookingId.'
            </td>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                padding-top: 20px;
                font-size: 12px;
              "
            >
             '.$agency.'
            </td>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                padding-top: 20px;
                font-size: 12px;
              "
            >
              '.$route.'
            </td>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                padding-top: 20px;
                font-size: 12px;
              "
            >
              '.$pax.'
            </td>

            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                padding-top: 20px;
                font-size: 12px;
              "
            >
              '.$netCost.'
            </td>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                padding-top: 20px;
                font-size: 12px;
              "
            >
              '.$flightDate.'
            </td>
            </tr>';
}


$statement='<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Owner Deposit Request</title>
  </head>
  <body>
    <div class="div" style="width: 650px; height: 45vh; margin: 0 auto">
      <div
        style="
          width: 800px;
          height: 180px;
          background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
        "
      >
        <table
          style="
            border-collapse: collapse;
            border-spacing: 0;
            padding: 0;
            width: 800px;
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
                font-family: sans-serif;
                text-align: center;
                font-weight: bold;
                font-size: 25px;
                line-height: 38px;
                padding-top: 30px;
                padding-bottom: 10px;
              "
            >
              <img
                width="150px"
                src="https://cdn.flyfarint.com/logo.png"
                alt=""
              />
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
            width: 720px;
            border-radius: 10px 10px 0px 0px;
          "
        >
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
                font-size: 22px;
                line-height: 38px;
                padding-top: 30px;
                padding-bottom: 20px;
              "
            >
              Upcomming Flights
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
            width: 720px;
            border-radius: 10px 10px 0px 0px;
          "
        >
          <tr style="background-color: #dc143c; color: white; ">
            <th
              align="center"
              valign="middle"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-size: 12px;
              "
            >
              Booking ID
            </th>
            <th
            align="center"
            valign="middle"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-size: 12px;
              "
            >
              Agency
            </th>
            <th
            align="center"
            valign="middle"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-size: 12px;
              "
            >
              Route
            </th>
            <th
            align="center"
            valign="middle"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-size: 12px;
              "
            >
              PAX
            </th>

            <th
            align="center"
            valign="middle"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-size: 12px;
              "
            >
              Net Cost
            </th>
            <th
            align="center"
            valign="middle"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                font-family: sans-serif;
                text-align: left;
                padding: 10px;
                font-size: 12px;
              "
            >
              Flight Date
            </th>
            </tr>
        '.$tableData.'
        </table>

        <table
          border="0"
          cellpadding="0"
          cellspacing="0"
          align="center"
          bgcolor="white"
          style="
            font-size: 13px;
            line-height: 18px;
            color: #929090;
            border-collapse: collapse;
            border-spacing: 0;
            width: 720px;
            font-family: sans-serif;
            font-weight: bold;
          "
        >
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
                padding-top: 20px;
                font-size: 12px;
                line-height: 18px;
                color: #929090;
                padding-top: 20px;
                width: 100%;
              "
            >
              Sincarely,
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
                padding-top: 20px;
                font-size: 12px;
                line-height: 18px;
                color: #929090;
                padding-top: 8px;
                padding-bottom: 30px;
                width: 100%;
              "
            >
              FLy Far International
            </td>
          </tr>
        </table>
      </div>
    </div>
  </body>
</html>';

// echo $statement;


$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($statement);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();
$fileatt = $dompdf->output();

$filename = 'Statment.pdf';
$encoding = 'base64';
$type = 'application/pdf';


$mail1 = new PHPMailer();
$mail1->isSMTP();
$mail1->Host       = 'b2b.flyfarint.com';
$mail1->SMTPAuth   = true;
$mail1->Username   = 'job@b2b.flyfarint.com';
$mail1->Password   = '123Next2$';
$mail1->SMTPSecure = 'ssl';
$mail1->Port       = 465;
                               

    //Recipients
$mail1->setFrom('job@b2b.flyfarint.com', 'Daily Ticketed Summary Report');
$mail1->addAddress("tgchiran23@gmail.com", "Daily Ticketed Summary Report");
// $mail1->addCC("fahim@flyfarint.com", "Daily Booking Summary Report");
// $mail1->addCC("afridi@flyfarint.com", "Daily Booking Summary Report");
// $mail1->addCC("ceo@flyfarint.com", "Daily Booking Summary Report");
// $mail1->addCC("sadman@flyfarint.com", "Daily Booking Summary Report");

$mail1->isHTML(true);                                  
$mail1->Subject = "Daily Ticketed Summary Report";
$mail1->Body    =  $statement;
$mail1->AddStringAttachment($fileatt, $filename, $encoding, $type);
if(!$mail1->Send()) {
    echo "Mailer Error: " . $mail1->ErrorInfo;
}

?>