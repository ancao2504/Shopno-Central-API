<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require '../../vendor/autoload.php';

include_once '../../authorization.php';
if (authorization($conn) == true){  
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
      $_POST = json_decode(file_get_contents('php://input'), true);

      $Pax = $_POST['pax'];
      $bookingId = $_POST['bookingId'];
      $AirlinesPNR = $_POST['airlinePnr'];
      $gdsPNR = $_POST['gdsPnr'];
      $system = $_POST['system'];
      $actionBy = $_POST['actionBy'];
      $vendor = $_POST['vendor'];
      $invoice = $_POST['invoice'];
      $gdsSegment = $_POST['gdsSegment'];
      $ticketCoupon = str_replace("'", "''", $_POST['ticketCoupon']);
      $lossAmount = $_POST['lossAmount'];
      $itaNumber = $_POST['itanumber'];
      $pccnumber = $_POST['pccnumber'];
      $LossProfitExplanation = $_POST['lossProfitExplanation'];
      $Remarks = $_POST['remarks'];
      $systemPNR = $_POST['systemPNR'];
      $ticketingclass = $_POST['ticketingclass'];
      $clientinvioce = $_POST['clientinvioce'];
      $clientinviocecommiss = $_POST['clientinviocecommiss'];
      $vendorinvioce = $_POST['vendorinvioce'];
      $vendorinviocecommiss = $_POST['vendorinviocecommiss'];
      $losscommisssion = $_POST['losscommisssion'];

      $bookingdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `booking` where bookingId='$bookingId'"));
      $status = $bookingdata['status'];
      //echo $status;
      if ($status == "Ticketed") {
        $response['status'] = "success";
        $response['message'] = "Already Ticketed";
        echo json_encode($response);
      }elseif ($status == "Cancelled") {
        $response['status'] = "success";
        $response['message'] = "Already Cancelled";
        echo json_encode($response);
      } else if ($status == 'Issue In Processing') {
      
          $ticketId = "";
          $sql1 = "SELECT * FROM ticketed ORDER BY ticketId DESC LIMIT 1";
          $result = $conn->query($sql1);
          if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                  $outputString = preg_replace('/[^0-9]/', '', $row["ticketId"]);
                  $number = (int) $outputString + 1;
                  $ticketId = "FFT$number";
              }
          } else {
              $ticketId = "FFT1000";
          }

          //Agent Info

          $bookingdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `booking` where bookingId='$bookingId'"));

          $agentId = $bookingdata['agentId'];
          $subagentId = $bookingdata['subagentId'];
          $staffId = $bookingdata['staffId'];
          $From = $bookingdata['deptFrom'];
          $To = $bookingdata['arriveTo'];
          $Route = $From .' - '. $To;
          $Airlines = $bookingdata['airlines'];
          $Cost = $bookingdata['netCost'];
          $gds = $bookingdata['gds'];
          $pnr = $bookingdata['pnr'];
          $name = $bookingdata['name'];
          $adultBag = $bookingdata['adultBag'];
          $childBag = $bookingdata['childBag'];
          $infantBag = $bookingdata['infantBag'];
          $tripType = $bookingdata['tripType'];
          $travelDate = $bookingdata['travelDate'];
          $bookingDate = $bookingdata['bookedAt'];
          $Baggage = "$adultBag $childBag $infantBag";
          $adultCount = $bookingdata['adultCount'];
          $childCount = $bookingdata['childCount'];
          $infantCount = $bookingdata['infantCount'];

          $adultCostBase = $bookingdata['adultCostBase'];
          $childCostBase = $bookingdata['childCostBase'];
          $infantCostBase = $bookingdata['infantCostBase'];

          $adultCostTax = $bookingdata['adultCostTax'];
          $childCostTax = $bookingdata['childCostTax'];
          $infantCostTax = $bookingdata['infantCostTax'];

          $grossCost = $bookingdata['grossCost'];
          $netCost = $bookingdata['netCost'];
          $discount = $grossCost - $netCost;

          // if(empty($gdsPNR) && empty($system)){
          //   $gds = $bookingdata['gds'];
          //   $pnr = $bookingdata['pnr'];
          // }else if(empty($gdsPNR) && empty($system)){
          //   $gds = $system;
          //   $pnr = $gdsPNR;
          // }

          if ($subagentId !='') {
              //Mail Data
              $result1 = $conn->query("SELECT * FROM wl_content where agentId='$agentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);

              $agentCompanyName = $result1[0]['company_name'];
              $agentCompanyLogo = $result1[0]['companyImage'];
              $agentCompanyEmail = $result1[0]['email'];
              $agentCompanyPhone = $result1[0]['phone'];
              $agentCompanyAddress = $result1[0]['address'];
              $agentCompanyWebsiteLink = $result1[0]['websitelink'];
              $agentCompanyFbLink = $result1[0]['fb_link'];
              $agentCompanyLinkedinLink = $result1[0]['linkedin_link'];
              $agentCompanyWhatsappNum = $result1[0]['whatsapp_num'];

              // Subagent data for mail

              $result = $conn->query("SELECT * FROM subagent where agentId='$agentId' AND subagentId='$subagentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
              $subAgentEmail = $result[0]['email'];
              $subAgentCompanyName = $result[0]['company'];
          }

          $Refundable = '';

          if ($bookingdata['refundable']) {
              $Refundable = 'Refundable';
          } else {
              $Refundable = 'Non Refundable';
          }

          $Logo = "https://tbbd-flight.s3.ap-southeast-1.amazonaws.com/airlines-logo/";

          if ($tripType == 'oneway' || $tripType == '1') {
              $FlightDetails = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `segment_one_way` where pnr='$pnr'"));
              $segments = $FlightDetails['segment'];

              if ($segments == 1) {
                  $transit1 = $FlightDetails['transit1'];
                  $marketingCareer1 = $FlightDetails['marketingCareer1'];
                  $marketingCareerName1 = $FlightDetails['marketingCareerName1'];
                  $marketingFlight1 = $FlightDetails['marketingFlight1'];
                  $operatingCareer1 = $FlightDetails['operatingCareer1'];
                  $departure1 = $FlightDetails['departure1'];
                  $arrival1 = $FlightDetails['arrival1'];
                  $departureLocation1 = $FlightDetails['departureLocation1'];
                  $arrivalLocation1 = $FlightDetails['arrivalLocation1'];
                  $departureAirport1 = $FlightDetails['departureAirport1'];
                  $arrivalAirport1 = $FlightDetails['arrivalAirport1'];
                  $flightDuration1 = $FlightDetails['flightDuration1'];

                  $departureTime1 = $FlightDetails['departureTime1'];
                  $departureTime1 = date('D d M Y h:i A', strtotime($departureTime1));

                  $arrivalTime1 = $FlightDetails['arrivalTime1'];
                  $arrivalTime1 = date('D d M Y h:i A', strtotime($arrivalTime1));

                  $flightsData = '<tr style="font-size: 12px;font-family: sans-serif; ">


      <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align: top;font-family: sans-serif;   border:1px solid#c7c7c7;">
      <img width="25px" src="' . $Logo . '' . $marketingCareer1 . '.png" alt="">
      <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $marketingCareerName1 . '</p>
      <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $marketingCareer1 . ' ' . $marketingFlight1 . '</p>
      </td>
        <td style=" border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $departure1 . '] ' . $departureLocation1 . ' ' . $departureAirport1 . '</td>
        <td style=" border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $arrival1 . '] ' . $arrivalLocation1 . ' ' . $arrivalAirport1 . '</td>
        <td style="border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif; font-weight: 600;color: #222222;">' . $departureTime1 . '</td>
        <td style="border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif; font-weight: 600;color: #222222;">' . $arrivalTime1 . ' </td>
        <td style="border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif; font-weight: 600;color: #222222;"><span style="color:#222222">Baggage:</span> ' . $Baggage . ' </span> <br>  <span style="color:#222222">Duration:</span> ' . $flightDuration1 . ' </td>
      </tr>';
              } elseif ($segments == 2) {
                  $transit1 = $FlightDetails['transit1'];
                  $marketingCareer1 = $FlightDetails['marketingCareer1'];
                  $marketingCareerName1 = $FlightDetails['marketingCareerName1'];
                  $marketingFlight1 = $FlightDetails['marketingFlight1'];
                  $operatingCareer1 = $FlightDetails['operatingCareer1'];
                  $departure1 = $FlightDetails['departure1'];
                  $arrival1 = $FlightDetails['arrival1'];
                  $departureLocation1 = $FlightDetails['departureLocation1'];
                  $arrivalLocation1 = $FlightDetails['arrivalLocation1'];
                  $departureAirport1 = $FlightDetails['departureAirport1'];
                  $arrivalAirport1 = $FlightDetails['arrivalAirport1'];
                  $flightDuration1 = $FlightDetails['flightDuration1'];
                  $departureTime1 = $FlightDetails['departureTime1'];
                  $departureTime1 = date('D d M Y h:i A', strtotime($departureTime1));

                  $arrivalTime1 = $FlightDetails['arrivalTime1'];
                  $arrivalTime1 = date('D d M Y h:i A', strtotime($arrivalTime1));

                  $transit2 = $FlightDetails['transit2'];
                  $marketingCareer2 = $FlightDetails['marketingCareer2'];
                  $marketingCareerName2 = $FlightDetails['marketingCareerName2'];
                  $marketingFlight2 = $FlightDetails['marketingFlight2'];
                  $operatingCareer2 = $FlightDetails['operatingCareer2'];
                  $departure2 = $FlightDetails['departure2'];
                  $arrival2 = $FlightDetails['arrival2'];
                  $departureLocation2 = $FlightDetails['departureLocation2'];
                  $arrivalLocation2 = $FlightDetails['arrivalLocation2'];
                  $departureAirport2 = $FlightDetails['departureAirport2'];
                  $arrivalAirport2 = $FlightDetails['arrivalAirport2'];
                  $flightDuration2 = $FlightDetails['flightDuration2'];
                  $departureTime2 = $FlightDetails['departureTime2'];
                  $departureTime2 = date('D d M Y h:i A', strtotime($departureTime2));
                  $arrivalTime2 = $FlightDetails['arrivalTime2'];
                  $arrivalTime2 = date('D d M Y h:i A', strtotime($arrivalTime2));

                  $flightsData = '<tr style="font-size: 12px;font-family: sans-serif;">
        <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align: top;font-family: sans-serif;   border:1px solid#c7c7c7;">
          <img width="25px" src="' . $Logo . '' . $marketingCareer1 . '.png" alt="">
          <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $marketingCareerName1 . '</p>
          <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $marketingCareer1 . ' ' . $marketingFlight1 . '</p>
      </td>
        <td style=" border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif; font-weight: 600;color: #222222;">[' . $departure1 . '] ' . $departureLocation1 . ' ' . $departureAirport1 . '</td>
        <td style=" border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif; font-weight: 600;color: #222222;">[' . $arrival1 . '] ' . $arrivalLocation1 . ' ' . $arrivalAirport1 . '</td>
        <td style=" border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif; font-weight: 600;color: #222222;">' . $departureTime1 . '</td>
        <td style=" border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif; font-weight: 600;color: #222222;">' . $arrivalTime1 . ' </td>
        <td style=" border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif; font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;>Cabin:</span>7Kg <br> <span style="color:#222222">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222">Economy</span> <br> <span
      </tr>

      <tr style="font-size: 12px;font-family: sans-serif;">
        <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align: top;font-family: sans-serif;   border:1px solid#c7c7c7;">
          <img width="25px" src="' . $Logo . '' . $marketingCareer2 . '.png" alt="">
          <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $marketingCareerName2 . '</p>
          <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $marketingCareer2 . ' ' . $marketingFlight2 . '</p>
      </td>
        <td style=" border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif; font-weight: 600;color: #222222;">[' . $departure2 . '] ' . $departureLocation2 . ' ' . $departureAirport2 . '</td>
        <td style=" border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif; font-weight: 600;color: #222222;">[' . $arrival2 . '] ' . $arrivalLocation2 . ' ' . $arrivalAirport2 . '</td>
        <td style=" border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif; font-weight: 600;color: #222222;">' . $departureTime2 . '</td>
        <td style=" border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif; font-weight: 600;color: #222222;">' . $arrivalTime2 . ' </td>
        <td style=" border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif; font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;>Cabin:</span>7Kg <br> <span style="color:#222222">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222">Economy</span> <br> <span
      </tr>';
              } elseif ($segments == 3) {
                  $transit1 = $FlightDetails['transit1'];
                  $marketingCareer1 = $FlightDetails['marketingCareer1'];
                  $marketingCareerName1 = $FlightDetails['marketingCareerName1'];
                  $marketingFlight1 = $FlightDetails['marketingFlight1'];
                  $operatingCareer1 = $FlightDetails['operatingCareer1'];
                  $departure1 = $FlightDetails['departure1'];
                  $arrival1 = $FlightDetails['arrival1'];
                  $departureLocation1 = $FlightDetails['departureLocation1'];
                  $arrivalLocation1 = $FlightDetails['arrivalLocation1'];
                  $departureAirport1 = $FlightDetails['departureAirport1'];
                  $arrivalAirport1 = $FlightDetails['arrivalAirport1'];
                  $flightDuration1 = $FlightDetails['flightDuration1'];
                  $departureTime1 = $FlightDetails['departureTime1'];
                  $departureTime1 = date('D d M Y h:i A', strtotime($departureTime1));
                  $arrivalTime1 = $FlightDetails['arrivalTime1'];
                  $arrivalTime1 = date('D d M Y h:i A', strtotime($arrivalTime1));

                  $transit2 = $FlightDetails['transit2'];
                  $marketingCareer2 = $FlightDetails['marketingCareer2'];
                  $marketingCareerName2 = $FlightDetails['marketingCareerName2'];
                  $marketingFlight2 = $FlightDetails['marketingFlight2'];
                  $operatingCareer2 = $FlightDetails['operatingCareer2'];
                  $departure2 = $FlightDetails['departure2'];
                  $arrival2 = $FlightDetails['arrival2'];
                  $departureLocation2 = $FlightDetails['departureLocation2'];
                  $arrivalLocation2 = $FlightDetails['arrivalLocation2'];
                  $departureAirport2 = $FlightDetails['departureAirport2'];
                  $arrivalAirport2 = $FlightDetails['arrivalAirport2'];
                  $flightDuration2 = $FlightDetails['flightDuration2'];
                  $departureTime2 = $FlightDetails['departureTime2'];
                  $departureTime2 = date('D d M Y h:i A', strtotime($departureTime2));
                  $arrivalTime2 = $FlightDetails['arrivalTime2'];
                  $arrivalTime2 = date('D d M Y h:i A', strtotime($arrivalTime2));

                  $marketingCareer3 = $FlightDetails['marketingCareer3'];
                  $marketingCareerName3 = $FlightDetails['marketingCareerName3'];
                  $marketingFlight3 = $FlightDetails['marketingFlight3'];
                  $operatingCareer3 = $FlightDetails['operatingCareer3'];
                  $departure3 = $FlightDetails['departure3'];
                  $arrival3 = $FlightDetails['arrival3'];
                  $departureLocation3 = $FlightDetails['departureLocation3'];
                  $arrivalLocation3 = $FlightDetails['arrivalLocation3'];
                  $departureAirport3 = $FlightDetails['departureAirport3'];
                  $arrivalAirport3 = $FlightDetails['arrivalAirport3'];
                  $flightDuration3 = $FlightDetails['flightDuration3'];
                  $departureTime3 = $FlightDetails['departureTime3'];
                  $departureTime3 = date('D d M Y h:i A', strtotime($departureTime3));
                  $arrivalTime3 = $FlightDetails['arrivalTime3'];
                  $arrivalTime3 = date('D d M Y h:i A', strtotime($arrivalTime3));

                  $flightsData = '
      <tr style="font-size: 12px;font-family: sans-serif; ">
      <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align: top;font-family: sans-serif;   border:1px solid#c7c7c7;">
      <img width="25px" src="' . $Logo . '' . $marketingCareer1 . '.png" alt="">
      <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $marketingCareerName1 . '</p>
      <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $marketingCareer1 . ' ' . $marketingFlight1 . '</p>
  </td>
      <td style="  border:1px solid#c7c7c7;
              text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $departure1 . '] ' . $departureLocation1 . ' ' . $departureAirport1 . '</td>
      <td style="  border:1px solid#c7c7c7;
              text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $arrival1 . '] ' . $arrivalLocation1 . ' ' . $arrivalAirport1 . '</td>
      <td style="  border:1px solid#c7c7c7;
              text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $departureTime1 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
      <td style="  border:1px solid#c7c7c7;
              text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $arrivalTime1 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
      <td style="  border:1px solid#c7c7c7;
              text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;">
      <span style="color:#222222; font-weight: 600;">Cabin:</span> 7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br><span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $flightDuration1 . ' </td>
    </tr>

      <tr style="font-size: 12px;font-family: sans-serif; ">

      <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align: top;font-family: sans-serif;   border:1px solid#c7c7c7;">
      <img width="25px" src="' . $Logo . '' . $marketingCareer2 . '.png" alt="">
      <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $marketingCareerName2 . '</p>
      <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $marketingCareer2 . ' ' . $marketingFlight2 . '</p>
      </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $departure2 . '] ' . $departureLocation2 . ' ' . $departureAirport2 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $arrival2 . '] ' . $arrivalLocation2 . ' ' . $arrivalAirport2 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $departureTime2 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $arrivalTime2 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;">
        <span style="color:#222222; font-weight: 600;">Cabin:</span> 7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br><span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $flightDuration2 . ' </td>
      </tr>

      <tr style="font-size: 12px;font-family: sans-serif; ">

      <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align: top;font-family: sans-serif;   border:1px solid#c7c7c7;">
      <img width="25px" src="' . $Logo . '' . $marketingCareer3 . '.png" alt="">
      <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $marketingCareerName3 . '</p>
      <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $marketingCareer3 . ' ' . $marketingFlight3 . '</p>
      </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $departure3 . '] ' . $departureLocation3 . ' ' . $departureAirport3 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $arrival3 . '] ' . $arrivalLocation3 . ' ' . $arrivalAirport3 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $departureTime3 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $arrivalTime3 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;">
        <span style="color:#222222; font-weight: 600;">Cabin:</span> 7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br><span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $flightDuration3 . ' </td>
      </tr>';
              }
          } elseif ($tripType == 'return' || $tripType == '2') {
              $FlightDetails = mysqli_query($conn, "SELECT * FROM `segment_return_way` where pnr='$pnr'")->fetch_all(MYSQLI_ASSOC);
            // print_r($FlightDetails);
              $segments = $FlightDetails['segment'];

              if ($segments == 1) {
                  $goTransit1 = $FlightDetails['goTransit1'];
                  $goMarketingCareer1 = $FlightDetails['goMarketingCareer1'];
                  $goMarketingCareerName1 = $FlightDetails['goMarketingCareerName1'];
                  $goMarketingFlight1 = $FlightDetails['goMarketingFlight1'];
                  $goOperatingCareer1 = $FlightDetails['goOperatingCareer1'];
                  $goDeparture1 = $FlightDetails['goDeparture1'];
                  $goArrival1 = $FlightDetails['goArrival1'];
                  $goDepartureLocation1 = $FlightDetails['goDepartureLocation1'];
                  $goArrivalLocation1 = $FlightDetails['goArrivalLocation1'];
                  $goDepartureAirport1 = $FlightDetails['goDepartureAirport1'];
                  $goArrivalAirport1 = $FlightDetails['goArrivalAirport1'];
                  $goFlightDuration1 = $FlightDetails['goFlightDuration1'];
                  $goDepartureTime1 = $FlightDetails['goDepartureTime1'];
                  $goDepartureTime1 = date('D d M Y h:i A', strtotime($goDepartureTime1));
                  $goArrivalTime1 = $FlightDetails['goArrivalTime1'];
                  $goArrivalTime1 = date('D d M Y h:i A', strtotime($goArrivalTime1));

                  $backTransit1 = $FlightDetails['backTransit1'];
                  $backMarketingCareer1 = $FlightDetails['backMarketingCareer1'];
                  $backMarketingCareerName1 = $FlightDetails['backMarketingCareerName1'];
                  $backMarketingFlight1 = $FlightDetails['backMarketingFlight1'];
                  $backOperatingCareer1 = $FlightDetails['backOperatingCareer1'];
                  $backDeparture1 = $FlightDetails['backDeparture1'];
                  $backArrival1 = $FlightDetails['backArrival1'];
                  $backDepartureLocation1 = $FlightDetails['backDepartureLocation1'];
                  $backArrivalLocation1 = $FlightDetails['backArrivalLocation1'];
                  $backDepartureAirport1 = $FlightDetails['backDepartureAirport1'];
                  $backArrivalAirport1 = $FlightDetails['backArrivalAirport1'];
                  $backFlightDuration1 = $FlightDetails['backFlightDuration1'];
                  $backDepartureTime1 = $FlightDetails['backDepartureTime1'];
                  $backDepartureTime1 = date('D d M Y h:i A', strtotime($backDepartureTime1));
                  $backArrivalTime1 = $FlightDetails['backArrivalTime1'];
                  $backArrivalTime1 = date('D d M Y h:i A', strtotime($backArrivalTime1));

                  $flightsData = '
        <tr style="font-size: 12px;font-family: sans-serif; ">

        <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align:top;font-family: sans-serif;   border:1px solid#c7c7c7;">
        <img width="25px" src="' . $Logo . '' . $goMarketingCareer1 . '.png" alt="">
        <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $goMarketingCareerName1 . '</p>
        <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $goMarketingCareer1 . ' ' . $goMarketingFlight1 . '</p>
        </td>


        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $goDeparture1 . '] ' . $goDepartureLocation1 . ' ' . $goDepartureAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $goArrival1 . '] ' . $goArrivalLocation1 . ' ' . $goArrivalAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $goDepartureTime1 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"> ' . $goArrivalTime1 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;">Cabin:</span>7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $goFlightDuration1 . ' </td>
      </tr>


        <tr style="font-size: 12px;font-family: sans-serif; ">

        <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align:top;font-family: sans-serif;   border:1px solid#c7c7c7;">
        <img width="25px" src="' . $Logo . '' . $backMarketingCareer1 . '.png" alt="">
        <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $backMarketingCareerName1 . '</p>
        <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $backMarketingCareer1 . ' ' . $backMarketingFlight1 . '</p>
        </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backDeparture1 . '] ' . $backDepartureLocation1 . ' ' . $backDepartureAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backArrival1 . '] ' . $backArrivalLocation1 . ' ' . $backArrivalAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $backDepartureTime1 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"> ' . $backArrivalTime1 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;">Cabin:</span>7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $backFlightDuration1 . ' </td>
      </tr>';
              } elseif ($segments == 2) {
                  $goTransit1 = $FlightDetails['goTransit1'];
                  $goMarketingCareer1 = $FlightDetails['goMarketingCareer1'];
                  $goMarketingCareerName1 = $FlightDetails['goMarketingCareerName1'];
                  $goMarketingFlight1 = $FlightDetails['goMarketingFlight1'];
                  $goOperatingCareer1 = $FlightDetails['goOperatingCareer1'];
                  $goDeparture1 = $FlightDetails['goDeparture1'];
                  $goArrival1 = $FlightDetails['goArrival1'];
                  $goDepartureLocation1 = $FlightDetails['goDepartureLocation1'];
                  $goArrivalLocation1 = $FlightDetails['goArrivalLocation1'];
                  $goDepartureAirport1 = $FlightDetails['goDepartureAirport1'];
                  $goArrivalAirport1 = $FlightDetails['goArrivalAirport1'];
                  $goFlightDuration1 = $FlightDetails['goFlightDuration1'];
                  $goDepartureTime1 = $FlightDetails['goDepartureTime1'];
                  $goDepartureTime1 = date('D d M Y h:i A', strtotime($goDepartureTime1));
                  $goArrivalTime1 = $FlightDetails['goArrivalTime1'];
                  $goArrivalTime1 = date('D d M Y h:i A', strtotime($goArrivalTime1));

                  $goTransit2 = $FlightDetails['goTransit2'];
                  $goMarketingCareer2 = $FlightDetails['goMarketingCareer2'];
                  $goMarketingCareerName2 = $FlightDetails['goMarketingCareerName2'];
                  $goMarketingFlight2 = $FlightDetails['goMarketingFlight2'];
                  $goOperatingCareer2 = $FlightDetails['goOperatingCareer2'];
                  $goDeparture2 = $FlightDetails['goDeparture2'];
                  $goArrival2 = $FlightDetails['goArrival2'];
                  $goDepartureLocation2 = $FlightDetails['goDepartureLocation2'];
                  $goArrivalLocation2 = $FlightDetails['goArrivalLocation2'];
                  $goDepartureAirport2 = $FlightDetails['goDepartureAirport2'];
                  $goArrivalAirport2 = $FlightDetails['goArrivalAirport2'];
                  $goFlightDuration2 = $FlightDetails['goFlightDuration2'];
                  $goDepartureTime2 = $FlightDetails['goDepartureTime2'];
                  $goDepartureTime2 = date('D d M Y h:i A', strtotime($goDepartureTime2));
                  $goArrivalTime2 = $FlightDetails['goArrivalTime2'];
                  $goArrivalTime2 = date('D d M Y h:i A', strtotime($goArrivalTime2));

                  $backTransit1 = $FlightDetails['backTransit1'];
                  $backMarketingCareer1 = $FlightDetails['backMarketingCareer1'];
                  $backMarketingCareerName1 = $FlightDetails['backMarketingCareerName1'];
                  $backMarketingFlight1 = $FlightDetails['backMarketingFlight1'];
                  $backOperatingCareer1 = $FlightDetails['backOperatingCareer1'];
                  $backDeparture1 = $FlightDetails['backDeparture1'];
                  $backArrival1 = $FlightDetails['backArrival1'];
                  $backDepartureLocation1 = $FlightDetails['backDepartureLocation1'];
                  $backArrivalLocation1 = $FlightDetails['backArrivalLocation1'];
                  $backDepartureAirport1 = $FlightDetails['backDepartureAirport1'];
                  $backArrivalAirport1 = $FlightDetails['backArrivalAirport1'];
                  $backFlightDuration1 = $FlightDetails['backFlightDuration1'];
                  $backDepartureTime1 = $FlightDetails['backDepartureTime1'];
                  $backDepartureTime1 = date('D d M Y h:i A', strtotime($backDepartureTime1));
                  $backArrivalTime1 = $FlightDetails['backArrivalTime1'];
                  $backArrivalTime1 = date('D d M Y h:i A', strtotime($backArrivalTime1));

                  $backTransit2 = $FlightDetails['backTransit2'];
                  $backMarketingCareer2 = $FlightDetails['backMarketingCareer2'];
                  $backMarketingCareerName2 = $FlightDetails['backMarketingCareerName2'];
                  $backMarketingFlight2 = $FlightDetails['backMarketingFlight2'];
                  $backOperatingCareer2 = $FlightDetails['backOperatingCareer2'];
                  $backDeparture2 = $FlightDetails['backDeparture2'];
                  $backArrival2 = $FlightDetails['backArrival2'];
                  $backDepartureLocation2 = $FlightDetails['backDepartureLocation2'];
                  $backArrivalLocation2 = $FlightDetails['backArrivalLocation2'];
                  $backDepartureAirport2 = $FlightDetails['backDepartureAirport2'];
                  $backArrivalAirport2 = $FlightDetails['backArrivalAirport2'];
                  $backFlightDuration2 = $FlightDetails['backFlightDuration2'];
                  $backDepartureTime2 = $FlightDetails['backDepartureTime2'];
                  $backDepartureTime2 = date('D d M Y h:i A', strtotime($backDepartureTime2));
                  $backArrivalTime2 = $FlightDetails['backArrivalTime2'];
                  $backArrivalTime2 = date('D d M Y h:i A', strtotime($backArrivalTime2));

                  $flightsData = '<tr style="font-size: 12px;font-family: sans-serif; ">

        <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align:top;font-family: sans-serif;   border:1px solid#c7c7c7;">
        <img width="25px" src="' . $Logo . '' . $goMarketingCareer1 . '.png" alt="">
        <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $goMarketingCareerName1 . '</p>
        <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $goMarketingCareer1 . ' ' . $goMarketingFlight1 . '</p>
        </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $goDeparture1 . '] ' . $goDepartureLocation1 . ' ' . $goDepartureAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $goArrival1 . '] ' . $goArrivalLocation1 . ' ' . $goArrivalAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $goDepartureTime1 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $goArrivalTime1 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;">Cabin:</span>7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $goFlightDuration1 . ' </td>
      </tr>
      <tr style="font-size: 12px;font-family: sans-serif; ">

      <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align: top;font-family: sans-serif;   border:1px solid#c7c7c7;">
      <img width="25px" src="' . $Logo . '' . $goMarketingCareer2 . '.png" alt="">
      <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $goMarketingCareerName2 . '</p>
      <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $goMarketingCareer2 . ' ' . $goMarketingFlight2 . '</p>
      </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span  style="color:#222222; font-weight: 600;">[' . $goDeparture2 . '] ' . $goDepartureLocation2 . ' ' . $goDepartureAirport2 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span  style="color:#222222; font-weight: 600;">[' . $goArrival2 . '] ' . $goArrivalLocation2 . ' ' . $goArrivalAirport2 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $goDepartureTime2 . '<span  style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"> ' . $goArrivalTime2 . '<span  style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span  style="color:#222222; font-weight: 600;">Cabin:</span>7Kg <br> <span  style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span  style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $goFlightDuration2 . ' </td>
      </tr>

      <tr style="font-size: 12px;font-family: sans-serif; ">
      <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align: top;font-family: sans-serif;   border:1px solid#c7c7c7;">
      <img width="25px" src="' . $Logo . '' . $backMarketingCareer1 . '.png" alt="">
      <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $backMarketingCareerName1 . '</p>
      <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $backMarketingCareer1 . ' ' . $backMarketingFlight1 . '</p>
      </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backDeparture1 . '] ' . $backDepartureLocation1 . ' ' . $backDepartureAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backArrival1 . '] ' . $backArrivalLocation1 . ' ' . $backArrivalAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $backDepartureTime1 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"> ' . $backArrivalTime1 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;">Cabin:</span> 7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $backFlightDuration1 . ' </td>
      </tr>
      <tr style="font-size: 12px;font-family: sans-serif; ">

      <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align: top;font-family: sans-serif;   border:1px solid#c7c7c7;">
      <img width="25px" src="' . $Logo . '' . $backMarketingCareer2 . '.png" alt="">
      <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $backMarketingCareerName2 . '</p>
      <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $backMarketingCareer2 . ' ' . $backMarketingFlight2 . '</p>
      </td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backDeparture2 . '] ' . $backDepartureLocation2 . ' ' . $backDepartureAirport2 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backArrival2 . '] ' . $backArrivalLocation2 . ' ' . $backArrivalAirport2 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $backDepartureTime2 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"> ' . $backArrivalTime2 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;">Cabin:</span>7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $backFlightDuration2 . ' </td>
      </tr>';
              } elseif ($segments == 3) {
                  $goTransit1 = $FlightDetails['goTransit1'];
                  $goMarketingCareer1 = $FlightDetails['goMarketingCareer1'];
                  $goMarketingCareerName1 = $FlightDetails['goMarketingCareerName1'];
                  $goMarketingFlight1 = $FlightDetails['goMarketingFlight1'];
                  $goOperatingCareer1 = $FlightDetails['goOperatingCareer1'];
                  $goDeparture1 = $FlightDetails['goDeparture1'];
                  $goArrival1 = $FlightDetails['goArrival1'];
                  $goDepartureLocation1 = $FlightDetails['goDepartureLocation1'];
                  $goArrivalLocation1 = $FlightDetails['goArrivalLocation1'];
                  $goDepartureAirport1 = $FlightDetails['goDepartureAirport1'];
                  $goArrivalAirport1 = $FlightDetails['goArrivalAirport1'];
                  $goFlightDuration1 = $FlightDetails['goFlightDuration1'];
                  $goDepartureTime1 = $FlightDetails['goDepartureTime1'];
                  $goDepartureTime1 = date('D d M Y h:i A', strtotime($goDepartureTime1));
                  $goArrivalTime1 = $FlightDetails['goArrivalTime1'];
                  $goArrivalTime1 = date('D d M Y h:i A', strtotime($goArrivalTime1));

                  $goTransit2 = $FlightDetails['goTransit2'];
                  $goMarketingCareer2 = $FlightDetails['goMarketingCareer2'];
                  $goMarketingCareerName2 = $FlightDetails['goMarketingCareerName2'];
                  $goMarketingFlight2 = $FlightDetails['goMarketingFlight2'];
                  $goOperatingCareer2 = $FlightDetails['goOperatingCareer2'];
                  $goDeparture2 = $FlightDetails['goDeparture2'];
                  $goArrival2 = $FlightDetails['goArrival2'];
                  $goDepartureLocation2 = $FlightDetails['goDepartureLocation2'];
                  $goArrivalLocation2 = $FlightDetails['goArrivalLocation2'];
                  $goDepartureAirport2 = $FlightDetails['goDepartureAirport2'];
                  $goArrivalAirport2 = $FlightDetails['goArrivalAirport2'];
                  $goFlightDuration2 = $FlightDetails['goFlightDuration2'];
                  $goDepartureTime2 = $FlightDetails['goDepartureTime2'];
                  $goDepartureTime2 = date('D d M Y h:i A', strtotime($goDepartureTime2));
                  $goArrivalTime2 = $FlightDetails['goArrivalTime2'];
                  $goArrivalTime2 = date('D d M Y h:i A', strtotime($goArrivalTime2));

                  $goMarketingCareer3 = $FlightDetails['goMarketingCareer3'];
                  $goMarketingCareerName3 = $FlightDetails['goMarketingCareerName3'];
                  $goMarketingFlight3 = $FlightDetails['goMarketingFlight3'];
                  $goOperatingCareer3 = $FlightDetails['goOperatingCareer3'];
                  $goDeparture3 = $FlightDetails['goDeparture3'];
                  $goArrival3 = $FlightDetails['goArrival3'];
                  $goDepartureLocation3 = $FlightDetails['goDepartureLocation3'];
                  $goArrivalLocation3 = $FlightDetails['goArrivalLocation3'];
                  $goDepartureAirport3 = $FlightDetails['goDepartureAirport3'];
                  $goArrivalAirport3 = $FlightDetails['goArrivalAirport3'];
                  $goFlightDuration3 = $FlightDetails['goFlightDuration3'];
                  $goDepartureTime3 = $FlightDetails['goDepartureTime3'];
                  $goDepartureTime3 = date('D d M Y h:i A', strtotime($goDepartureTime3));
                  $goArrivalTime3 = $FlightDetails['goArrivalTime3'];
                  $goArrivalTime3 = date('D d M Y h:i A', strtotime($goArrivalTime3));

                  $backTransit1 = $FlightDetails['backTransit1'];
                  $backMarketingCareer1 = $FlightDetails['backMarketingCareer1'];
                  $backMarketingCareerName1 = $FlightDetails['backMarketingCareerName1'];
                  $backMarketingFlight1 = $FlightDetails['backMarketingFlight1'];
                  $backOperatingCareer1 = $FlightDetails['backOperatingCareer1'];
                  $backDeparture1 = $FlightDetails['backDeparture1'];
                  $backArrival1 = $FlightDetails['backArrival1'];
                  $backDepartureLocation1 = $FlightDetails['backDepartureLocation1'];
                  $backArrivalLocation1 = $FlightDetails['backArrivalLocation1'];
                  $backDepartureAirport1 = $FlightDetails['backDepartureAirport1'];
                  $backArrivalAirport1 = $FlightDetails['backArrivalAirport1'];
                  $backFlightDuration1 = $FlightDetails['backFlightDuration1'];
                  $backDepartureTime1 = $FlightDetails['backDepartureTime1'];
                  $backDepartureTime1 = date('D d M Y h:i A', strtotime($backDepartureTime1));
                  $backArrivalTime1 = $FlightDetails['backArrivalTime1'];
                  $backArrivalTime1 = date('D d M Y h:i A', strtotime($backArrivalTime1));

                  $backTransit2 = $FlightDetails['backTransit2'];
                  $backMarketingCareer2 = $FlightDetails['backMarketingCareer2'];
                  $backMarketingCareerName2 = $FlightDetails['backMarketingCareerName2'];
                  $backMarketingFlight2 = $FlightDetails['backMarketingFlight2'];
                  $backOperatingCareer2 = $FlightDetails['backOperatingCareer2'];
                  $backDeparture2 = $FlightDetails['backDeparture2'];
                  $backArrival2 = $FlightDetails['backArrival2'];
                  $backDepartureLocation2 = $FlightDetails['backDepartureLocation2'];
                  $backArrivalLocation2 = $FlightDetails['backArrivalLocation2'];
                  $backDepartureAirport2 = $FlightDetails['backDepartureAirport2'];
                  $backArrivalAirport2 = $FlightDetails['backArrivalAirport2'];
                  $backFlightDuration2 = $FlightDetails['backFlightDuration2'];
                  $backDepartureTime2 = $FlightDetails['backDepartureTime2'];
                  $backDepartureTime2 = date('D d M Y h:i A', strtotime($backDepartureTime2));
                  $backArrivalTime2 = $FlightDetails['backArrivalTime2'];
                  $backArrivalTime2 = date('D d M Y h:i A', strtotime($backArrivalTime2));

                  $backMarketingCareer3 = $FlightDetails['backMarketingCareer3'];
                  $backMarketingCareerName3 = $FlightDetails['backMarketingCareerName3'];
                  $backMarketingFlight3 = $FlightDetails['backMarketingFlight3'];
                  $backOperatingCareer3 = $FlightDetails['backOperatingCareer3'];
                  $backDeparture3 = $FlightDetails['backDeparture3'];
                  $backArrival3 = $FlightDetails['backArrival3'];
                  $backDepartureLocation3 = $FlightDetails['backDepartureLocation3'];
                  $backArrivalLocation3 = $FlightDetails['backArrivalLocation3'];
                  $backDepartureAirport3 = $FlightDetails['backDepartureAirport3'];
                  $backArrivalAirport3 = $FlightDetails['backArrivalAirport3'];
                  $backFlightDuration3 = $FlightDetails['backFlightDuration3'];
                  $backDepartureTime3 = $FlightDetails['backDepartureTime3'];
                  $backDepartureTime3 = date('D d M Y h:i A', strtotime($backDepartureTime3));
                  $backArrivalTime3 = $FlightDetails['backArrivalTime3'];
                  $backArrivalTime3 = date('D d M Y h:i A', strtotime($backArrivalTime3));

                  $flightsData = '<tr style="font-size: 12px;font-family: sans-serif;">

        <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align:top;font-family: sans-serif;   border:1px solid#c7c7c7;">
        <img width="25px" src="' . $Logo . '' . $goMarketingCareer1 . '.png" alt="">
        <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $goMarketingCareerName1 . '</p>
        <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $goMarketingCareer1 . ' ' . $goMarketingFlight1 . '</p>
        </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $goDeparture1 . '] ' . $goDepartureLocation1 . ' ' . $goDepartureAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $goArrival1 . '] ' . $goArrivalLocation1 . ' ' . $goArrivalAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $goDepartureTime1 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $goArrivalTime1 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;">
        <span style="color:#222222; font-weight: 600;">Cabin:</span>100Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $goFlightDuration1 . ' </td>
      </tr>

      <tr style="font-size: 12px;font-family: sans-serif;">
      <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align:top;font-family: sans-serif;   border:1px solid#c7c7c7;">
      <img width="25px" src="' . $Logo . '' . $goMarketingCareer2 . '.png" alt="">
      <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $goMarketingCareerName2 . '</p>
      <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $goMarketingCareer2 . ' ' . $goMarketingFlight2 . '</p>
      </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $goDeparture2 . '] ' . $goDepartureLocation2 . ' ' . $goDepartureAirport2 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $goArrival2 . '] ' . $goArrivalLocation2 . ' ' . $goArrivalAirport2 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $goDepartureTime2 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"> ' . $goArrivalTime2 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;">Cabin:</span>7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $goFlightDuration2 . ' </td>
      </tr>

      <tr style="font-size: 12px;font-family: sans-serif;">
      <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align:top;font-family: sans-serif;   border:1px solid#c7c7c7;">
      <img width="25px" src="' . $Logo . '' . $goMarketingCareer3 . '.png" alt="">
      <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $goMarketingCareerName3 . '</p>
      <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $goMarketingCareer3 . ' ' . $goMarketingFlight3 . '</p>
      </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $goDeparture3 . '] ' . $goDepartureLocation3 . ' ' . $goDepartureAirport3 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $goArrival3 . '] ' . $goArrivalLocation3 . ' ' . $goArrivalAirport3 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $goDepartureTime3 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"> ' . $goArrivalTime3 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;">Cabin:</span>7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $goFlightDuration3 . ' </td>
      </tr>

      <tr style="font-size: 12px;font-family: sans-serif;">


      <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align:top;font-family: sans-serif;   border:1px solid#c7c7c7;">
      <img width="25px" src="' . $Logo . '' . $backMarketingCareer1 . '.png" alt="">
      <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $backMarketingCareerName1 . '</p>
      <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $backMarketingCareer1 . ' ' . $backMarketingFlight1 . '</p>
      </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backDeparture1 . '] ' . $backDepartureLocation1 . ' ' . $backDepartureAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backArrival1 . '] ' . $backArrivalLocation1 . ' ' . $backArrivalAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $backDepartureTime1 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"> ' . $backArrivalTime1 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;">Cabin:</span>7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $backFlightDuration1 . ' </td>
      </tr>

      <tr style="font-size: 12px;font-family: sans-serif;">
      <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align:top;font-family: sans-serif;   border:1px solid#c7c7c7;">
      <img width="25px" src="' . $Logo . '' . $backMarketingCareer2 . '.png" alt="">
      <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $backMarketingCareerName2 . '</p>
      <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $backMarketingCareer2 . ' ' . $backMarketingFlight2 . '</p>
      </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backDeparture2 . '] ' . $backDepartureLocation2 . ' ' . $backDepartureAirport2 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backArrival2 . '] ' . $backArrivalLocation2 . ' ' . $backArrivalAirport2 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $backDepartureTime2 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"> ' . $backArrivalTime2 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;">Cabin:</span>7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $backFlightDuration2 . ' </td>
      </tr>

      <tr style="font-size: 12px;font-family: sans-serif;">

      <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align:top;font-family: sans-serif;   border:1px solid#c7c7c7;">
      <img width="25px" src="' . $Logo . '' . $backMarketingCareer3 . '.png" alt="">
      <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $backMarketingCareerName3 . '</p>
      <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $backMarketingCareer3 . ' ' . $backMarketingFlight3 . '</p>
      </td>

        <td style="border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backDeparture3 . '] ' . $backDepartureLocation3 . ' ' . $backDepartureAirport3 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backArrival3 . '] ' . $backArrivalLocation3 . ' ' . $backArrivalAirport3 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $backDepartureTime3 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"> ' . $backArrivalTime3 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;">Cabin:</span>7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $backFlightDuration3 . ' </td>
      </tr>';
              } elseif ($segments == 12) {
                  $goTransit1 = $FlightDetails['goTransit1'];
                  $goMarketingCareer1 = $FlightDetails['goMarketingCareer1'];
                  $goMarketingCareerName1 = $FlightDetails['goMarketingCareerName1'];
                  $goMarketingFlight1 = $FlightDetails['goMarketingFlight1'];
                  $goOperatingCareer1 = $FlightDetails['goOperatingCareer1'];
                  $goDeparture1 = $FlightDetails['goDeparture1'];
                  $goArrival1 = $FlightDetails['goArrival1'];
                  $goDepartureLocation1 = $FlightDetails['goDepartureLocation1'];
                  $goArrivalLocation1 = $FlightDetails['goArrivalLocation1'];
                  $goDepartureAirport1 = $FlightDetails['goDepartureAirport1'];
                  $goArrivalAirport1 = $FlightDetails['goArrivalAirport1'];
                  $goFlightDuration1 = $FlightDetails['goFlightDuration1'];
                  $goDepartureTime1 = $FlightDetails['goDepartureTime1'];
                  $goDepartureTime1 = date('D d M Y h:i A', strtotime($goDepartureTime1));
                  $goArrivalTime1 = $FlightDetails['goArrivalTime1'];
                  $goArrivalTime1 = date('D d M Y h:i A', strtotime($goArrivalTime1));

                  $backTransit1 = $FlightDetails['backTransit1'];
                  $backMarketingCareer1 = $FlightDetails['backMarketingCareer1'];
                  $backMarketingCareerName1 = $FlightDetails['backMarketingCareerName1'];
                  $backMarketingFlight1 = $FlightDetails['backMarketingFlight1'];
                  $backOperatingCareer1 = $FlightDetails['backOperatingCareer1'];
                  $backDeparture1 = $FlightDetails['backDeparture1'];
                  $backArrival1 = $FlightDetails['backArrival1'];
                  $backDepartureLocation1 = $FlightDetails['backDepartureLocation1'];
                  $backArrivalLocation1 = $FlightDetails['backArrivalLocation1'];
                  $backDepartureAirport1 = $FlightDetails['backDepartureAirport1'];
                  $backArrivalAirport1 = $FlightDetails['backArrivalAirport1'];
                  $backFlightDuration1 = $FlightDetails['backFlightDuration1'];
                  $backDepartureTime1 = $FlightDetails['backDepartureTime1'];
                  $backDepartureTime1 = date('D d M Y h:i A', strtotime($backDepartureTime1));
                  $backArrivalTime1 = $FlightDetails['backArrivalTime1'];
                  $backArrivalTime1 = date('D d M Y h:i A', strtotime($backArrivalTime1));

                  $backTransit2 = $FlightDetails['backTransit2'];
                  $backMarketingCareer2 = $FlightDetails['backMarketingCareer2'];
                  $backMarketingCareerName2 = $FlightDetails['backMarketingCareerName2'];
                  $backMarketingFlight2 = $FlightDetails['backMarketingFlight2'];
                  $backOperatingCareer2 = $FlightDetails['backOperatingCareer2'];
                  $backDeparture2 = $FlightDetails['backDeparture2'];
                  $backArrival2 = $FlightDetails['backArrival2'];
                  $backDepartureLocation2 = $FlightDetails['backDepartureLocation2'];
                  $backArrivalLocation2 = $FlightDetails['backArrivalLocation2'];
                  $backDepartureAirport2 = $FlightDetails['backDepartureAirport2'];
                  $backArrivalAirport2 = $FlightDetails['backArrivalAirport2'];
                  $backFlightDuration2 = $FlightDetails['backFlightDuration2'];
                  $backDepartureTime2 = $FlightDetails['backDepartureTime2'];
                  $backDepartureTime2 = date('D d M Y h:i A', strtotime($backDepartureTime2));
                  $backArrivalTime2 = $FlightDetails['backArrivalTime2'];
                  $backArrivalTime2 = date('D d M Y h:i A', strtotime($backArrivalTime2));

                  $flightsData = '
        <tr style="font-size: 12px;font-family: sans-serif; ">
        <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align:top;font-family: sans-serif;   border:1px solid#c7c7c7;">
        <img width="25px" src="' . $Logo . '' . $goMarketingCareer1 . '.png" alt="">
        <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $goMarketingCareerName1 . '</p>
        <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $goMarketingCareer1 . ' ' . $goMarketingFlight1 . '</p>
        </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $goDeparture1 . '] ' . $goDepartureLocation1 . ' ' . $goDepartureAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $goArrival1 . '] ' . $goArrivalLocation1 . ' ' . $goArrivalAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $goDepartureTime1 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"> ' . $goArrivalTime1 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;">Cabin:</span>7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $goFlightDuration1 . ' </td>
      </tr>


        <tr style="font-size: 12px;font-family: sans-serif; ">
        <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align:top;font-family: sans-serif;   border:1px solid#c7c7c7;">
        <img width="25px" src="' . $Logo . '' . $backMarketingCareer1 . '.png" alt="">
        <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $backMarketingCareerName1 . '</p>
        <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $backMarketingCareer1 . ' ' . $backMarketingFlight1 . '</p>
        </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backDeparture1 . '] ' . $backDepartureLocation1 . ' ' . $backDepartureAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backArrival1 . '] ' . $backArrivalLocation1 . ' ' . $backArrivalAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $backDepartureTime1 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"> ' . $backArrivalTime1 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;">Cabin:</span>7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $backFlightDuration1 . ' </td>
      </tr>

      <tr style="font-size: 12px;font-family: sans-serif;">
      <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align:top;font-family: sans-serif;   border:1px solid#c7c7c7;">
      <img width="25px" src="' . $Logo . '' . $backMarketingCareer2 . '.png" alt="">
      <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $backMarketingCareerName2 . '</p>
      <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $backMarketingCareer2 . ' ' . $backMarketingFlight2 . '</p>
      </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backDeparture2 . '] ' . $backDepartureLocation2 . ' ' . $backDepartureAirport2 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backArrival2 . '] ' . $backArrivalLocation2 . ' ' . $backArrivalAirport2 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $backDepartureTime2 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"> ' . $backArrivalTime2 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;">Cabin:</span>7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $backFlightDuration2 . ' </td>
      </tr>';
              } elseif ($segments == 21) {
                  $goTransit1 = $FlightDetails['goTransit1'];
                  $goMarketingCareer1 = $FlightDetails['goMarketingCareer1'];
                  $goMarketingCareerName1 = $FlightDetails['goMarketingCareerName1'];
                  $goMarketingFlight1 = $FlightDetails['goMarketingFlight1'];
                  $goOperatingCareer1 = $FlightDetails['goOperatingCareer1'];
                  $goDeparture1 = $FlightDetails['goDeparture1'];
                  $goArrival1 = $FlightDetails['goArrival1'];
                  $goDepartureLocation1 = $FlightDetails['goDepartureLocation1'];
                  $goArrivalLocation1 = $FlightDetails['goArrivalLocation1'];
                  $goDepartureAirport1 = $FlightDetails['goDepartureAirport1'];
                  $goArrivalAirport1 = $FlightDetails['goArrivalAirport1'];
                  $goFlightDuration1 = $FlightDetails['goFlightDuration1'];
                  $goDepartureTime1 = $FlightDetails['goDepartureTime1'];
                  $goDepartureTime1 = date('D d M Y h:i A', strtotime($goDepartureTime1));
                  $goArrivalTime1 = $FlightDetails['goArrivalTime1'];
                  $goArrivalTime1 = date('D d M Y h:i A', strtotime($goArrivalTime1));

                  $goTransit2 = $FlightDetails['goTransit2'];
                  $goMarketingCareer2 = $FlightDetails['goMarketingCareer2'];
                  $goMarketingCareerName2 = $FlightDetails['goMarketingCareerName2'];
                  $goMarketingFlight2 = $FlightDetails['goMarketingFlight2'];
                  $goOperatingCareer2 = $FlightDetails['goOperatingCareer2'];
                  $goDeparture2 = $FlightDetails['goDeparture2'];
                  $goArrival2 = $FlightDetails['goArrival2'];
                  $goDepartureLocation2 = $FlightDetails['goDepartureLocation2'];
                  $goArrivalLocation2 = $FlightDetails['goArrivalLocation2'];
                  $goDepartureAirport2 = $FlightDetails['goDepartureAirport2'];
                  $goArrivalAirport2 = $FlightDetails['goArrivalAirport2'];
                  $goFlightDuration2 = $FlightDetails['goFlightDuration2'];
                  $goDepartureTime2 = $FlightDetails['goDepartureTime2'];
                  $goDepartureTime2 = date('D d M Y h:i A', strtotime($goDepartureTime2));
                  $goArrivalTime2 = $FlightDetails['goArrivalTime2'];
                  $goArrivalTime2 = date('D d M Y h:i A', strtotime($goArrivalTime2));

                  $backTransit1 = $FlightDetails['backTransit1'];
                  $backMarketingCareer1 = $FlightDetails['backMarketingCareer1'];
                  $backMarketingCareerName1 = $FlightDetails['backMarketingCareerName1'];
                  $backMarketingFlight1 = $FlightDetails['backMarketingFlight1'];
                  $backOperatingCareer1 = $FlightDetails['backOperatingCareer1'];
                  $backDeparture1 = $FlightDetails['backDeparture1'];
                  $backArrival1 = $FlightDetails['backArrival1'];
                  $backDepartureLocation1 = $FlightDetails['backDepartureLocation1'];
                  $backArrivalLocation1 = $FlightDetails['backArrivalLocation1'];
                  $backDepartureAirport1 = $FlightDetails['backDepartureAirport1'];
                  $backArrivalAirport1 = $FlightDetails['backArrivalAirport1'];
                  $backFlightDuration1 = $FlightDetails['backFlightDuration1'];
                  $backDepartureTime1 = $FlightDetails['backDepartureTime1'];
                  $backDepartureTime1 = date('D d M Y h:i A', strtotime($backDepartureTime1));
                  $backArrivalTime1 = $FlightDetails['backArrivalTime1'];
                  $backArrivalTime1 = date('D d M Y h:i A', strtotime($backArrivalTime1));

                  $flightsData = '
        <tr style="font-size: 12px;font-family: sans-serif; ">
        <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align:top;font-family: sans-serif;   border:1px solid#c7c7c7;">
        <img width="25px" src="' . $Logo . '' . $goMarketingCareer1 . '.png" alt="">
        <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $goMarketingCareerName1 . '</p>
        <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $goMarketingCareer1 . ' ' . $goMarketingFlight1 . '</p>
        </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $goDeparture1 . '] ' . $goDepartureLocation1 . ' ' . $goDepartureAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $goArrival1 . '] ' . $goArrivalLocation1 . ' ' . $goArrivalAirport1 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $goDepartureTime1 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"> ' . $goArrivalTime1 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;">Cabin:</span>7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $goFlightDuration1 . ' </td>
      </tr>

      <tr style="font-size: 12px;font-family: sans-serif;">
      <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align:top;font-family: sans-serif;   border:1px solid#c7c7c7;">
      <img width="25px" src="' . $Logo . '' . $goMarketingCareer2 . '.png" alt="">
      <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $goMarketingCareerName2 . '</p>
      <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $goMarketingCareer2 . ' ' . $goMarketingFlight2 . '</p>
      </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $goDeparture2 . '] ' . $goDepartureLocation2 . ' ' . $goDepartureAirport2 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $goArrival2 . '] ' . $goArrivalLocation2 . ' ' . $goArrivalAirport2 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $goDepartureTime2 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"> ' . $goArrivalTime2 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;">Cabin:</span>7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $goFlightDuration2 . ' </td>
      </tr>


      <tr style="font-size: 12px;font-family: sans-serif;">
      <td style="text-align: left; padding-top:10px; padding-left:10px; width: 15%; vertical-align:top;font-family: sans-serif;   border:1px solid#c7c7c7;">
      <img width="25px" src="' . $Logo . '' . $backMarketingCareer2 . '.png" alt="">
      <p style="margin-top:0px; font-family: sans-serif;font-weight: 600;  ">' . $backMarketingCareerName2 . '</p>
      <p style="margin-top:-10px;font-family: sans-serif; color:#222222;font-weight: 600;">' . $backMarketingCareer2 . ' ' . $backMarketingFlight2 . '</p>
      </td>

        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backDeparture2 . '] ' . $backDepartureLocation2 . ' ' . $backDepartureAirport2 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="color:#222222; font-weight: 600;">[' . $backArrival2 . '] ' . $backArrivalLocation2 . ' ' . $backArrivalAirport2 . '</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $backDepartureTime2 . '<span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"> ' . $backArrivalTime2 . ' <span style="color:#222222; font-weight: 600;"></span> 09:55</td>
        <td style="  border:1px solid#c7c7c7;
                text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;"><span style="text-align: left; padding-top:10px;"><span style="color:#222222; font-weight: 600;">Cabin:</span>7Kg <br> <span style="color:#222222; font-weight: 600;">Baggage:</span> ' . $Baggage . ' </span> <br> <span style="color:#222222; font-weight: 600;">Economy</span> <br> <span style="color:#222222; font-weight: 600;">Duration:</span> ' . $backFlightDuration2 . ' </td>
      </tr>';
              }
          }

          $agentdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `agent` where agentId='$agentId'"));
          $companyName = $agentdata['company'];
          $companyEmail = $agentdata['email'];
          $companyPhone = $agentdata['phone'];
          $companyAddress = $agentdata['companyadd'];
          $companyImage = $agentdata['companyImage'];

          $staffdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `staffList` where staffId='$staffId' AND agentId='$agentId'"));
          if (!empty($staffdata)) {
              $staffName = $staffdata['name'];
              $Message = "Your Staff $staffName";
              $Message1 = "Our Staff $staffName";
          } else {
              $staffName = "Agent";
              $Message = "You ";
              $Message1 = "Our ";
          }

          $DateTime = date("D d M Y h:i A");

          $createdTime = date('Y-m-d H:i:s');

          $paxDetails = array();

          if ($Pax > 0) {
              for ($i = 0; $i < $Pax; $i++) {
                  ${'fullname' . $i} = $_POST['traveller'][$i]["passengerName"];
                  ${'eticket' . $i} = $_POST['traveller'][$i]["ticketno"];
                  ${'passport' . $i} = $_POST['traveller'][$i]["passportno"];
                  ${'pType' . $i} = $_POST['traveller'][$i]["pType"];
                  ${'gender' . $i} = $_POST['traveller'][$i]["gender"];

                  if (${'gender' . $i} == 'Male' && ${'pType' . $i} == 'ADT') {
                      ${'title' . $i} = 'MR';
                  } elseif (${'gender' . $i} == 'Male' && ${'pType' . $i} == 'CNN') {
                      ${'title' . $i} = 'MSTR';
                  } elseif (${'gender' . $i} == 'Male' && ${'pType' . $i} == 'INF') {
                      ${'title' . $i} = 'MSTR';
                  } elseif (${'gender' . $i} == 'Female' && ${'pType' . $i} == 'ADT') {
                      ${'title' . $i} = 'MS';
                  } elseif (${'gender' . $i} == 'Female' && ${'pType' . $i} == 'CNN') {
                      ${'title' . $i} = 'MISS';
                  } elseif (${'gender' . $i} == 'Female' && ${'pType' . $i} == 'INF') {
                      ${'title' . $i} = 'MISS';
                  }

                  ${'sql' . $i} = "INSERT IGNORE INTO `ticketed`(`agentId`,`bookingId`, `ticketId`,`gds`,`gdsPnr`,`airlinesPnr`, `ticketno`,`prefix`, `passengerName`, `passportno`, `pType`, `gender`,`ticketedAt`,`ticketedBy`)
            VALUES ('$agentId','$bookingId','$ticketId','$system','$gdsPNR','$AirlinesPNR','${'eticket' . $i}','${'title' . $i}','${'fullname' . $i}','${'passport' . $i}','${'pType' . $i}','${'gender' . $i}','$createdTime','$actionBy')";
                  if ($conn->query(${'sql' . $i}) === true) {
                      $Paxdata = '<tr style=" border:1px solid#c7c7c7;font-size: 12px; border-bottom: 1px solid#c7c7c7; font-family: sans-serif;">
                          <td style="border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px;padding-bottom: 10px; vertical-align: top; font-family: sans-serif; font-weight: 600; color: #222222;">' . ${'title' . $i} . ' ' . ${'fullname' . $i} . '</td>
                            <td style="border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px;padding-bottom: 10px; vertical-align: top; font-family: sans-serif; font-weight: 600; color: #222222;">' . ${'gender' . $i} . '</td>
                            <td style="border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px;padding-bottom: 10px; vertical-align: top; font-family: sans-serif; font-weight: 600; color: #222222;">' . ${'pType' . $i} . '</td>
                            <td style="border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px;padding-bottom: 10px; vertical-align: top; font-family: sans-serif; font-weight: 600; color: #222222;">' . ${'passport' . $i} . '</td>
                            <td style="border:1px solid#c7c7c7;text-align: left; padding-top:10px; padding-left:10px;padding-bottom: 10px; vertical-align: top; font-family: sans-serif; font-weight: 600; color: #222222;">' . ${'eticket' . $i} . '</td>
                          </tr>';

                      array_push($paxDetails, $Paxdata);
                  }
              }
          }

          $PaxDatas = implode(' ', $paxDetails);

          $sqlUpdate = "UPDATE `booking` SET `status`='Ticketed',`vendor`='$vendor',`invoice`='$invoice',`ticketId`='$ticketId',`airlinesPNR`='$AirlinesPNR',`lastUpdated`='$createdTime',`gds_segment`='$gdsSegment', `ticketCoupon`='$ticketCoupon', `lossAmount`='$lossAmount',`itanumber`='$itaNumber', `pccnumber`='$pccnumber',`LossProfitExplanation`='$LossProfitExplanation',`Remarks`='$Remarks',`systemPNR`='$systemPNR',`system`='$system',`ticketingclass`='$ticketingclass',`clientinvioce`='$clientinvioce',`clientinviocecommiss`='$clientinviocecommiss', `vendorinvioce`='$vendorinvioce', `vendorinviocecommiss`='$vendorinviocecommiss',`losscommisssion`='$losscommisssion' where bookingId='$bookingId'";

          if ($conn->query($sqlUpdate) === true) {
              $conn->query("INSERT IGNORE INTO `activityLog`(`ref`,`agentId`,`status`,`actionRef`,`actionBy`, `actionAt`)
                      VALUES ('$bookingId','$agentId','Ticketed','$ticketId','$actionBy','$createdTime')");

              $ticketCopy = '
    <!DOCTYPE html>
    <html lang="en">
      <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Ticketed With Price</title>
      </head>

      <!-- <style>
        table, th, td {
        border-collapse: collapse;
        /* border: 1px solid black; */
        padding: 0;
      }
      </style> -->
      <body style="width: 100%; height: 100vh; font-family: sans-serif;">

      <table style="width:100%; font-family: sans-serif;">
      <tr>
        <td style="text-align: right;width:180px;">
        <img height="75px" src="' . $companyImage . '" alt="">
      </td>
      </tr>
    </table>
        <table style="width:100%; ">
            <tr>
              <td>
              <span style="width:420px; font-size: 20px; font-weight: 600; font-family: sans-serif;">' . $companyName . '</span>
                <p style="font-size: 13px;width:280px; font-weight: 600; font-family: sans-serif; color: #8c8c8c;">' . $companyAddress . '</p>
                    <p style="font-size: 13px;margin-top:-10px; font-weight: 600; font-family: sans-serif; color: #8c8c8c;> <span style="color: #222222;">Email: </span>' . $companyEmail . ' </p>
                    <p style="font-size: 13px;margin-top:-10px; font-weight: 600;color: #8c8c8c;"><span style="color: #222222;">Phone: </span> ' . $companyPhone . '
                    </p>
            </td>
              <td style="text-align: right; font-size: 45px; font-weight: 600; color:#222222; opacity: 20%;font-family: sans-serif;">e-Ticket</td>
            </tr>
          </table>

          <table style="width:100%; margin-top: 25px; font-family: sans-serif;">
            <tr>
                <td>
                  <p style="padding-right: 5px; font-size: 14px; font-weight: 500; font-family: sans-serif;color: #222222;">Reference: ' . $bookingId . '</p>
                </td>
                <td>
                  <p style="padding-right: 5px; font-size: 14px; font-weight: 500; font-family: sans-serif;color: #222222;">Booking Date: ' . date("d M Y H:i A", strtotime($bookingDate)) . '</p>
                </td>
                <td>
                  <p style="padding-right: 5px; font-size: 14px; font-weight: 500; font-family: sans-serif;color: #222222;">Airlines PNR: ' . $AirlinesPNR . '</p>
                </td>
                <td>
                  <p style="padding-right: 5px; font-size: 14px; font-weight: 500; font-family: sans-serif;color: #222222;">' . strtoupper($tripType) . ' | ' . $Refundable . '</p>
                </td>
              </tr>
          </table>

          <div>
            <p style="font-weight:bold; color:#003566; font-family: sans-serif; font-size: 15px;">PASSENGER DETAILS</p>
            <table style="width:100%; margin-top: 25px; text-align: left; border-collapse: collapse; font-family: sans-serif;">
              <tr style="font-size: 12px;border:1px solid #c7c7c7;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif;">
                  <th style="border:1px solid#c7c7c7;padding-left: 10px;padding: 10px; text-align: left; font-family: sans-serif;">Passenger Name</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px;padding: 10px; text-align: left; font-family: sans-serif;">Passenger Gender</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px;padding: 10px; text-align: left; font-family: sans-serif;">Passenger Type</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px;padding: 10px; text-align: left; font-family: sans-serif;">Passport Number</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px;padding: 10px; text-align: left; font-family: sans-serif;">Ticket Number</th>
                </tr>
                ' . $PaxDatas . '
            </table>
          </div>

          <div style="style="margin-top:40px;font-family: sans-serif;">
            <p style="font-weight:bold; color:#003566;font-family: sans-serif;  font-size: 15px;">FLIGHT ITINERARIES</p>
            <table style="width:100%; margin-top: 25px; text-align: left; border-collapse: collapse;font-family: sans-serif;   ">
              <tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Flight</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Depart From</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Arrival To</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Depart At</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Arrive At</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Info</th>

                </tr>
                ' . $flightsData . '
            </table>
          </div>


        </div>
      </body>
    </html>';

              if ($adultCount > 0 && $childCount > 0 && $infantCount > 0) {
                  $PriceBreakdown = '<tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">Adult x' . $adultCount . '</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $adultCostBase . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $adultCostTax . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . (($adultCostBase + $adultCostTax) * $adultCount) . ' BDT</td>
              </tr>
              <tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">Child x' . $childCount . '</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $childCostBase . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $childCostTax . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . (($childCostBase + $childCostTax) * $childCount) . ' BDT</td>
              </tr>
              <tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">Infant x' . $infantCount . '</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $infantCostBase . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $infantCostTax . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . (($infantCostBase + $infantCostTax) * $infantCount) . ' BDT</td>
              </tr>';
                  $baseFareCost = $adultCostBase + $childCostBase + $infantCostBase;
                  $taxcost = $adultCostTax + $childCostTax + $infantCostTax;
                  $totalFare = $adultCostBase + $childCostBase + $infantCostBase + $adultCostTax + $childCostTax + $infantCostTax;
              } elseif ($adultCount > 0 && $childCount > 0) {
                  $PriceBreakdown = '<tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">Adult x' . $adultCount . '</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $adultCostBase . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $adultCostTax . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . (($adultCostBase + $adultCostTax) * $adultCount) . ' BDT</td>
              </tr>
              <tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">Child x' . $childCount . '</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $childCostBase . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $childCostTax . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . (($childCostBase + $childCostTax) * $childCount) . ' BDT</td>
              </tr>';
                  $baseFareCost = $adultCostBase + $childCostBase;
                  $taxcost = $adultCostTax + $childCostTax;
                  $totalFare = $adultCostBase + $childCostBase + $adultCostTax + $childCostTax;
              } elseif ($adultCount > 0 && $infantCount > 0) {
                  $PriceBreakdown = '<tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">Adult x' . $adultCount . '</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $adultCostBase . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $adultCostTax . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . (($adultCostBase + $adultCostTax) * $adultCount) . ' BDT</td>
              </tr>

              <tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">Infant x' . $infantCount . '</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $infantCostBase . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $infantCostTax . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . (($infantCostBase + $infantCostTax) * $infantCount) . ' BDT</td>
              </tr>';
                  $baseFareCost = $adultCostBase + $infantCostBase;
                  $taxCost = $adultCostTax + $infantCostTax;
                  $totalFare = $adultCostBase + $infantCostBase + $adultCostTax + $infantCostTax;
              } else {
                  $PriceBreakdown = '<tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">Adult x' . $adultCount . '</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $adultCostBase . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $adultCostTax . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . (($adultCostBase + $adultCostTax) * $adultCount) . ' BDT</td>
              </tr>';
                  $baseFareCost = $adultCostBase;
                  $taxCost = $adultCostTax;
                  $totalFare = $adultCostBase + $adultCostTax;
              }

              $InvoiceCopy = '<html>
    <head>
      <meta charset="UTF-8" />
      <meta http-equiv="X-UA-Compatible" content="IE=edge" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title>Ticketed With Price</title>
    </head>


    <body style="width: 100%; height: 100vh; font-family: sans-serif;">

      <table style="width:100%; font-family: sans-serif;">
          <tr>
            <td style="text-align: right; width:180px;">
              <img height="75px" src="https://cdn.flyfarint.com/logo-black.png" alt="">
          </td>
          </tr>
        </table>
      <table style="width:100%; font-family: sans-serif; ">
          <tr>
            <td>
            <span style="font-size: 20px; font-weight: 600; font-family: sans-serif;">Fly Far International</span>
              <p style="font-size: 13px;width:280px; font-weight: 600; font-family: sans-serif; color: #8c8c8c;">Ka-9/A, Hazi Abdul Latif Mantion,
                  Bashundhara Rd, Dhaka 1229</p>
                  <p style="font-size: 13px;margin-top:-10px; font-weight: 600; font-family: sans-serif; color: #8c8c8c;"><span style="color: #222222;">Email: </span>support@flyfarint.com </p>
                  <p style="font-size: 13px;margin-top:-10px; font-weight: 600; font-family: sans-serif; color: #8c8c8c;"><span style="color: #222222;">Phone: </span> 09606912912
                  </p>
          </td>
          <td style="text-align: right; font-size: 45px; font-weight: 600; color:#222222; opacity: 20%;font-family: sans-serif;">Agent Invoice</td>
            </tr>
        </table>

        <table style="width:100%; margin-top: 25px; font-family: sans-serif;">
          <tr>
                <td>
                  <p style="font-size: 14px; font-weight: 500; font-family: sans-serif;color: #222222;">Reference: ' . $bookingId . '</p>
                </td>
                <td>
                  <p style="font-size: 14px; font-weight: 500; font-family: sans-serif;color: #222222;">Booking Date: ' . date("d M Y H:i A", strtotime($bookingDate)) . '</p>
                </td>
                <td>
                  <p style="font-size: 14px; font-weight: 500; font-family: sans-serif;color: #222222;">Airlines PNR: ' . $AirlinesPNR . '</p>
                </td>
                <td>
                  <p style="font-size: 14px; font-weight: 500; font-family: sans-serif;color: #222222;">' . strtoupper($tripType) . ' | ' . $Refundable . '</p>
                </td>
              </tr>
        </table>

        <div>
          <p style="font-weight:bold; color:#003566; font-family: sans-serif; font-size: 15px;">PASSENGER DETAILS</p>
          <table style="width:100%; margin-top: 25px; text-align: left; border-collapse: collapse; font-family: sans-serif;">
            <tr style="font-size: 12px;border:1px solid #c7c7c7;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif;">
                <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Passenger Name</th>
                <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Passenger Gender</th>
                <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Passenger Type</th>
                <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Passport Number</th>
                <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Ticket Number</th>
              </tr>
            ' . $PaxDatas . '
          </table>
        </div>

        <div style="margin-top:40px;font-family: sans-serif;">
          <p style="font-weight:bold; color:#003566;font-family: sans-serif;  font-size: 15px;">FLIGHT ITENRARIES</p>
          <table style="width:100%; margin-top: 25px; text-align: left; border-collapse: collapse;font-family: sans-serif;">
            <tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Flight</th>
                <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Depart From</th>
                <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Arrival To</th>
                <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Depart At</th>
                <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Arrive At</th>
                <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Info</th>
              </tr>
              ' . $flightsData . '
          </table>
        </div>


        <div style="margin-top:40px;font-family: sans-serif; ">
          <p style="font-weight:bold; color:#003566;font-family: sans-serif;  font-size: 15px;">PRICE BREAKDOWN</p>

          <table style="width:100%; margin-top: 25px; text-align: left; border-collapse: collapse;font-family: sans-serif; ">
              <tr  style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Passenger</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Base Fare</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Tax</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Total Fare</th>
              </tr>
              ' . $PriceBreakdown . '
          </table>
        </div>

        <div  style="margin-top:40px;font-family: sans-serif; border: 1px solid#c7c7c7; width: 50%; padding: 10px; ">
          <table style="width:100%; text-align: left; border-collapse: collapse;font-family: sans-serif; font-size: 12px; font-weight: 600; color: #222222;">
              <tr>
                  <th></th>
                  <th></th>
                </tr>
                <tr >
                  <td  style="padding-bottom:8px;">Base fare total amount
                  </td>
                  <td style="padding-bottom:8px;text-align: right; ">' . $baseFareCost . ' BDT</td>
                </tr>
                <tr>
                  <td style="padding-bottom:8px;padding-bottom:8px;">Tax</td>
                  <td style="text-align: right;">' . $taxCost . ' BDT</td>
                </tr>
                <tr>
                  <td style="padding-bottom:8px;">Discount
                  </td>
                  <td style="padding-bottom:8px;text-align: right;">' . $discount . ' BDT</td>
                </tr>
                <tr style="color:#222222">
                  <td  style="padding-top:15px; font-weight: 600;font-size: 14px;">Agent Total Ticket Invoice Amount
                  </td>
                  <td style="padding-top:15px;text-align: right; font-weight: 600;font-size: 14px;">' . $netCost . ' BDT
                  </td>
                </tr>
          </table>
        </div>
      </div>
    </body>
  </html>';

              //ticket Copy
              $options = new Options();
              $options->set('isRemoteEnabled', true);
              $dompdf = new Dompdf($options);
              $dompdf->loadHtml($ticketCopy);
              $dompdf->setPaper('A4', 'portrait');
              $dompdf->render();
              $fileatt = $dompdf->output();

              $filename = 'Ticket Copy.pdf';
              $encoding = 'base64';
              $type = 'application/pdf';

              //Invoice Copy
              $options1 = new Options();
              $options1->set('isRemoteEnabled', true);
              $dompdf1 = new Dompdf($options1);
              $dompdf1->loadHtml($InvoiceCopy);
              $dompdf1->setPaper('A4', 'portrait');
              $dompdf1->render();
              $fileatt1 = $dompdf1->output();

              $filename1 = 'Invoice Copy.pdf';
              $encoding1 = 'base64';
              $type1 = 'application/pdf';

              $AgentEmail = '<!DOCTYPE html>
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
                  Ticket Booking Confirmed
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
                              Dear ' . $companyName . ', Your Ticket is booked successfully, Thank you again for booking with Fly Far International.
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
                                Booking Id:
                                <a style="color: #003566" href="http://" target="_blank"
                                  >' . $bookingId . '</a
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
                                Destination: <span style="color: #dc143c">' . $From . ' - ' . $To . ', ' . $tripType . ' </span>
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
                                Travel Date:  <span style="color: #dc143c">' . $travelDate . '	</span>
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
                                Airline: <span style="color: #dc143c">' . $Airlines . '	</span>
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
                                Pax:  <span style="color: #dc143c">' . $Pax . '</span>
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
                                Cost:  <span style="color: #dc143c">' . $Cost . ' BDT
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
                  $mail->Host = 'b2b.flyfarint.com';
                  $mail->SMTPAuth = true;
                  $mail->Username = 'ticketed@b2b.flyfarint.com';
                  $mail->Password = '123Next2$';
                  $mail->SMTPSecure = 'ssl';
                  $mail->Port = 465;

                  //Recipients
                  $mail->setFrom('ticketed@flyfarint.com', 'Fly Far Int');
                  $mail->addAddress("$companyEmail", "AgentId : $agentId");
                  $mail->addCC('otaoperation@flyfarint.com');
                  // $mail->addCC('habib@flyfarint.com');
                  $mail->addCC('afridi@flyfarint.com');

                  $mail->isHTML(true);
                  $mail->Subject = "Ticketing Request Approved - $companyName";
                  $mail->Body = $AgentEmail;
                  $mail->AddStringAttachment($fileatt, $filename, $encoding, $type);
                  $mail->AddStringAttachment($fileatt1, $filename1, $encoding1, $type1);

                  if (!$mail->Send()) {
                      echo "Mailer Error: " . $mail->ErrorInfo;
                  }
              } catch (Exception $e) {
                  $response['status'] = "error";
                  $response['message'] = "Mail Doesn't Send";
              }
                  $fileatCoupen = $ticketCoupon;
                  $Coupenfilename = 'TicketCoupon.txt';
                  $encoding3 = 'base64';
                  $type3 = 'text/plain';

                  if($lossAmount > 0){
                    $amountText = "Profit Amount";
                  }else{
                    $amountText = "Loss Amount";

                    $OwnerEmail = '
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
                                PNR '.$gdsPNR.' With Loss '.$lossAmount.' BDT
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
                                Dear Fly Far International, Our ' . $Route . ', ' . $tripType . ' ticket by ' . $Airlines . ' new Booking Ticket Request has been Confirmed.
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
                            background-color: white;

                          "
                        >
                        Ticketed By: <span style="color: #dc143c">' . $companyName . '</span>
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
                                    >' . $bookingId . '</a
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
                                Destination: <span style="color: #dc143c">' . $Route . ' - ' . $From . '</span>
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
                                Travel Date:  <span style="color: #dc143c">' . $travelDate . '	</span>
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
                                Airline: <span style="color: #dc143c"> ' . $Airlines . '	</span>
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
                                Pax:  <span style="color: #dc143c">' . $Pax . '</span>
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
                            Vendor Name:  <span style="color: #dc143c"> ' . $vendor . '
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
                              Agent Invoice:  <span style="color: #dc143c"> ' . $netCost . ' BDT
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
                            Vendor Invoice:  <span style="color: #dc143c"> ' . $invoice . ' BDT
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
                          '.$amountText.':  <span style="color: #dc143c"> ' . $lossAmount . ' BDT
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
                                ' . $companyName . '

                                </td>
                              </tr>
                            </table>


                          </div>
                        </div>
                      </body>
                    </html>

                    ';

                  $mail5 = new PHPMailer();

                  try {
                      $mail5->isSMTP();
                      $mail5->Host = 'b2b.flyfarint.com';
                      $mail5->SMTPAuth = true;
                      $mail5->Username = 'ticketed@b2b.flyfarint.com';
                      $mail5->Password = '123Next2$';
                      $mail5->SMTPSecure = 'ssl';
                      $mail5->Port = 465;

                      //Recipients
                      $mail5->setFrom('ticketed@flyfarint.com', $companyName);
                      $mail5->addAddress('otaoperation@flyfarint.com');
                      $mail5->addCC('habib@flyfarint.com');
                      $mail5->addCC('afridi@flyfarint.com');

                      $mail5->isHTML(true);
                      $mail5->Subject = "Loss Alert for $bookingId // $vendor // $lossAmount";
                      $mail5->Body = $OwnerEmail;

                      if (!$mail5->Send()) {
                          echo "Mailer Error: " . $mail5->ErrorInfo;
                      }
                  } catch (Exception $e) {
                      $response['status'] = "error";
                      $response['message'] = "Mail Doesn't Send";
                  }

                  }
              
              $OwnerEmail = '
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
                                Ticketed || ' . $bookingId . ' || ' . $companyName . ' || ' . $vendor . ' || ' . $system . ' || ' . $gdsSegment . ' || ' . $lossAmount . ' BDT
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
                                Dear Fly Far International, Our ' . $Route . ', ' . $tripType . ' ticket by ' . $Airlines . ' new Booking Ticket Request has been Confirmed.
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
                            background-color: white;

                          "
                        >
                        Ticketed By: <span style="color: #dc143c">' . $companyName . '</span>
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
                                    >' . $bookingId . '</a
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
                                Destination: <span style="color: #dc143c">' . $Route . ' - ' . $From . '</span>
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
                                Travel Date:  <span style="color: #dc143c">' . $travelDate . '	</span>
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
                                Airline: <span style="color: #dc143c"> ' . $Airlines . '	</span>
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
                                Pax:  <span style="color: #dc143c">' . $Pax . '</span>
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
                            Vendor Name:  <span style="color: #dc143c"> ' . $vendor . '
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
                              Agent Invoice:  <span style="color: #dc143c"> ' . $netCost . ' BDT
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
                            Vendor Invoice:  <span style="color: #dc143c"> ' . $invoice . ' BDT
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
                          '.$amountText.':  <span style="color: #dc143c"> ' . $lossAmount . ' BDT
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
                                ' . $companyName . '

                                </td>
                              </tr>
                            </table>


                          </div>
                        </div>
                      </body>
                    </html>

                    ';

                  $mail4 = new PHPMailer();

                  try {
                      $mail4->isSMTP();
                      $mail4->Host = 'b2b.flyfarint.com';
                      $mail4->SMTPAuth = true;
                      $mail4->Username = 'ticketed@b2b.flyfarint.com';
                      $mail4->Password = '123Next2$';
                      $mail4->SMTPSecure = 'ssl';
                      $mail4->Port = 465;

                      //Recipients
                      $mail4->setFrom('ticketed@flyfarint.com', $companyName);
                      $mail4->addAddress('otaoperation@flyfarint.com');
                      $mail4->addCC('habib@flyfarint.com');
                      $mail4->addCC('afridi@flyfarint.com');

                      $mail4->isHTML(true);
                      $mail4->Subject = "Ticketed || $bookingId || $companyName || $vendor || $system || $gdsSegment || $lossAmount' BDT";
                      $mail4->Body = $OwnerEmail;
                      $mail4->AddStringAttachment($fileatt, $filename, $encoding, $type);
                      $mail4->AddStringAttachment($fileatt1, $filename1, $encoding1, $type1);
                      $mail4->AddStringAttachment($fileatCoupen, $Coupenfilename, $encoding3, $type3);

                      if (!$mail4->Send()) {
                          echo "Mailer Error: " . $mail4->ErrorInfo;
                      }
                  } catch (Exception $e) {
                      $response['status'] = "error";
                      $response['message'] = "Mail Doesn't Send";
                  }
              
              

              if (!empty($subagentId)) {
                  // ticket Copy

                  $ticketCopy = '
    <!DOCTYPE html>
    <html lang="en">
      <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Ticketed With Price</title>
      </head>

      <!-- <style>
        table, th, td {
        border-collapse: collapse;
        /* border: 1px solid black; */
        padding: 0;
      }
      </style> -->
      <body style="width: 100%; height: 100vh; font-family: sans-serif;">

      <table style="width:100%; font-family: sans-serif;">
      <tr>
        <td style="text-align: right;width:180px;">
      
      </td>
      </tr>
    </table>
        <table style="width:100%; ">
            <tr>
              <td>
              <span style="width:420px; font-size: 20px; font-weight: 600; font-family: sans-serif;">' . $agentCompanyName . '</span>
                <p style="font-size: 13px;width:280px; font-weight: 600; font-family: sans-serif; color: #8c8c8c;">' . $agentCompanyAddress . '</p>
                    <p style="font-size: 13px;margin-top:-10px; font-weight: 600; font-family: sans-serif; color: #8c8c8c;> <span style="color: #222222;">Email: </span>' . $agentCompanyEmail . ' </p>
                    <p style="font-size: 13px;margin-top:-10px; font-weight: 600;color: #8c8c8c;"><span style="color: #222222;">Phone: </span> ' . $agentCompanyPhone . '
                    </p>
            </td>
              <td style="text-align: right; font-size: 45px; font-weight: 600; color:#222222; opacity: 20%;font-family: sans-serif;">e-Ticket</td>
            </tr>
          </table>

          <table style="width:100%; margin-top: 25px; font-family: sans-serif;">
            <tr>
                <td>
                  <p style="padding-right: 5px; font-size: 14px; font-weight: 500; font-family: sans-serif;color: #222222;">Reference: ' . $bookingId . '</p>
                </td>
                <td>
                  <p style="padding-right: 5px; font-size: 14px; font-weight: 500; font-family: sans-serif;color: #222222;">Booking Date: ' . date("d M Y H:i A", strtotime($bookingDate)) . '</p>
                </td>
                <td>
                  <p style="padding-right: 5px; font-size: 14px; font-weight: 500; font-family: sans-serif;color: #222222;">Airlines PNR: ' . $AirlinesPNR . '</p>
                </td>
                <td>
                  <p style="padding-right: 5px; font-size: 14px; font-weight: 500; font-family: sans-serif;color: #222222;">' . strtoupper($tripType) . ' | ' . $Refundable . '</p>
                </td>
              </tr>
          </table>

          <div>
            <p style="font-weight:bold; color:#003566; font-family: sans-serif; font-size: 15px;">PASSENGER DETAILS</p>
            <table style="width:100%; margin-top: 25px; text-align: left; border-collapse: collapse; font-family: sans-serif;">
              <tr style="font-size: 12px;border:1px solid #c7c7c7;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif;">
                  <th style="border:1px solid#c7c7c7;padding-left: 10px;padding: 10px; text-align: left; font-family: sans-serif;">Passenger Name</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px;padding: 10px; text-align: left; font-family: sans-serif;">Passenger Gender</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px;padding: 10px; text-align: left; font-family: sans-serif;">Passenger Type</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px;padding: 10px; text-align: left; font-family: sans-serif;">Passport Number</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px;padding: 10px; text-align: left; font-family: sans-serif;">Ticket Number</th>
                </tr>
                ' . $PaxDatas . '
            </table>
          </div>

          <div style="style="margin-top:40px;font-family: sans-serif;">
            <p style="font-weight:bold; color:#003566;font-family: sans-serif;  font-size: 15px;">FLIGHT ITINERARIES</p>
            <table style="width:100%; margin-top: 25px; text-align: left; border-collapse: collapse;font-family: sans-serif;   ">
              <tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Flight</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Depart From</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Arrival To</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Depart At</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Arrive At</th>
                  <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Info</th>

                </tr>
                ' . $flightsData . '
            </table>
          </div>


        </div>
      </body>
    </html>';

                  if ($adultCount > 0 && $childCount > 0 && $infantCount > 0) {
                      $PriceBreakdown = '<tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">Adult x' . $adultCount . '</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $adultCostBase . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $adultCostTax . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . (($adultCostBase + $adultCostTax) * $adultCount) . ' BDT</td>
              </tr>
              <tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">Child x' . $childCount . '</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $childCostBase . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $childCostTax . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . (($childCostBase + $childCostTax) * $childCount) . ' BDT</td>
              </tr>
              <tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">Infant x' . $infantCount . '</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $infantCostBase . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $infantCostTax . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . (($infantCostBase + $infantCostTax) * $infantCount) . ' BDT</td>
              </tr>';
                      $baseFareCost = $adultCostBase + $childCostBase + $infantCostBase;
                      $taxcost = $adultCostTax + $childCostTax + $infantCostTax;
                      $totalFare = $adultCostBase + $childCostBase + $infantCostBase + $adultCostTax + $childCostTax + $infantCostTax;
                  } elseif ($adultCount > 0 && $childCount > 0) {
                      $PriceBreakdown = '<tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">Adult x' . $adultCount . '</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $adultCostBase . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $adultCostTax . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . (($adultCostBase + $adultCostTax) * $adultCount) . ' BDT</td>
              </tr>
              <tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">Child x' . $childCount . '</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $childCostBase . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $childCostTax . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . (($childCostBase + $childCostTax) * $childCount) . ' BDT</td>
              </tr>';
                      $baseFareCost = $adultCostBase + $childCostBase;
                      $taxcost = $adultCostTax + $childCostTax;
                      $totalFare = $adultCostBase + $childCostBase + $adultCostTax + $childCostTax;
                  } elseif ($adultCount > 0 && $infantCount > 0) {
                      $PriceBreakdown = '<tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">Adult x' . $adultCount . '</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $adultCostBase . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $adultCostTax . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . (($adultCostBase + $adultCostTax) * $adultCount) . ' BDT</td>
              </tr>

              <tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">Infant x' . $infantCount . '</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $infantCostBase . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $infantCostTax . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . (($infantCostBase + $infantCostTax) * $infantCount) . ' BDT</td>
              </tr>';
                      $baseFareCost = $adultCostBase + $infantCostBase;
                      $taxCost = $adultCostTax + $infantCostTax;
                      $totalFare = $adultCostBase + $infantCostBase + $adultCostTax + $infantCostTax;
                  } else {
                      $PriceBreakdown = '<tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">Adult x' . $adultCount . '</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $adultCostBase . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . $adultCostTax . ' BDT</td>
                  <td style="  border:1px solid#c7c7c7;
                  text-align: left; padding-top:10px; padding-left:10px; padding-bottom: 10px; vertical-align: top;font-family: sans-serif;font-weight: 600;color: #222222;">' . (($adultCostBase + $adultCostTax) * $adultCount) . ' BDT</td>
              </tr>';
                      $baseFareCost = $adultCostBase;
                      $taxCost = $adultCostTax;
                      $totalFare = $adultCostBase + $adultCostTax;
                  }




                  //Invoice Copy
                  $InvoiceCopy = '<html>
                <head>
                  <meta charset="UTF-8" />
                  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                  <title>Ticketed With Price</title>
                </head>
              
              
                <body style="width: 100%; height: 100vh; font-family: sans-serif;">
              
                  <table style="width:100%; font-family: sans-serif;">
                      <tr>
                        <td style="text-align: right; width:180px;">
                      </td>
                      </tr>
                    </table>
                  <table style="width:100%; font-family: sans-serif; ">
                      <tr>
                        <td>
                        <span style="font-size: 20px; font-weight: 600; font-family: sans-serif;">' . $agentCompanyName. '</span>
                          <p style="font-size: 13px;width:280px; font-weight: 600; font-family: sans-serif; color: #8c8c8c;">'.$agentCompanyAddress.'</p>
                              <p style="font-size: 13px;margin-top:-10px; font-weight: 600; font-family: sans-serif; color: #8c8c8c;"><span style="color: #222222;">Email: </span>'. $agentCompanyEmail .'</p>
                              <p style="font-size: 13px;margin-top:-10px; font-weight: 600; font-family: sans-serif; color: #8c8c8c;"><span style="color: #222222;">Phone: </span> '.$agentCompanyPhone.'
                              </p>
                      </td>
                      <td style="text-align: right; font-size: 45px; font-weight: 600; color:#222222; opacity: 20%;font-family: sans-serif;">Agent Invoice</td>
                        </tr>
                    </table>
              
                    <table style="width:100%; margin-top: 25px; font-family: sans-serif;">
                      <tr>
                            <td>
                              <p style="font-size: 14px; font-weight: 500; font-family: sans-serif;color: #222222;">Reference: ' . $bookingId . '</p>
                            </td>
                            <td>
                              <p style="font-size: 14px; font-weight: 500; font-family: sans-serif;color: #222222;">Booking Date: ' . date("d M Y H:i A", strtotime($bookingDate)) . '</p>
                            </td>
                            <td>
                              <p style="font-size: 14px; font-weight: 500; font-family: sans-serif;color: #222222;">Airlines PNR: ' . $AirlinesPNR . '</p>
                            </td>
                            <td>
                              <p style="font-size: 14px; font-weight: 500; font-family: sans-serif;color: #222222;">' . strtoupper($tripType) . ' | ' . $Refundable . '</p>
                            </td>
                          </tr>
                    </table>
              
                    <div>
                      <p style="font-weight:bold; color:#003566; font-family: sans-serif; font-size: 15px;">PASSENGER DETAILS</p>
                      <table style="width:100%; margin-top: 25px; text-align: left; border-collapse: collapse; font-family: sans-serif;">
                        <tr style="font-size: 12px;border:1px solid #c7c7c7;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif;">
                            <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Passenger Name</th>
                            <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Passenger Gender</th>
                            <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Passenger Type</th>
                            <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Passport Number</th>
                            <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Ticket Number</th>
                          </tr>
                        ' . $PaxDatas . '
                      </table>
                    </div>
              
                    <div style="margin-top:40px;font-family: sans-serif;">
                      <p style="font-weight:bold; color:#003566;font-family: sans-serif;  font-size: 15px;">FLIGHT ITENRARIES</p>
                      <table style="width:100%; margin-top: 25px; text-align: left; border-collapse: collapse;font-family: sans-serif;">
                        <tr style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                            <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Flight</th>
                            <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Depart From</th>
                            <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Arrival To</th>
                            <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Depart At</th>
                            <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Arrive At</th>
                            <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left;padding:10px; font-family: sans-serif;">Info</th>
                          </tr>
                          ' . $flightsData . '
                      </table>
                    </div>
              
              
                    <div style="margin-top:40px;font-family: sans-serif; ">
                      <p style="font-weight:bold; color:#003566;font-family: sans-serif;  font-size: 15px;">PRICE BREAKDOWN</p>
              
                      <table style="width:100%; margin-top: 25px; text-align: left; border-collapse: collapse;font-family: sans-serif; ">
                          <tr  style="font-size: 12px;color:#222222; height: 33px; font-weight: 400;font-family: sans-serif; ">
                              <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Passenger</th>
                              <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Base Fare</th>
                              <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Tax</th>
                              <th style="border:1px solid#c7c7c7;padding-left: 10px; text-align: left; padding: 10px;font-family: sans-serif;">Total Fare</th>
                          </tr>
                          ' . $PriceBreakdown . '
                      </table>
                    </div>
              
                    <div  style="margin-top:40px;font-family: sans-serif; border: 1px solid#c7c7c7; width: 50%; padding: 10px; ">
                      <table style="width:100%; text-align: left; border-collapse: collapse;font-family: sans-serif; font-size: 12px; font-weight: 600; color: #222222;">
                          <tr>
                              <th></th>
                              <th></th>
                            </tr>
                            <tr >
                              <td  style="padding-bottom:8px;">Base fare total amount
                              </td>
                              <td style="padding-bottom:8px;text-align: right; ">' . $baseFareCost . ' BDT</td>
                            </tr>
                            <tr>
                              <td style="padding-bottom:8px;padding-bottom:8px;">Tax</td>
                              <td style="text-align: right;">' . $taxCost . ' BDT</td>
                            </tr>
                            <tr>
                              <td style="padding-bottom:8px;">Discount
                              </td>
                              <td style="padding-bottom:8px;text-align: right;">' . $discount . ' BDT</td>
                            </tr>
                            <tr style="color:#222222">
                              <td  style="padding-top:15px; font-weight: 600;font-size: 14px;">Agent Total Ticket Invoice Amount
                              </td>
                              <td style="padding-top:15px;text-align: right; font-weight: 600;font-size: 14px;">' . $netCost . ' BDT
                              </td>
                            </tr>
                      </table>
                    </div>
                  </div>
                </body>
              </html>';

                  //ticket Copy
                  $options = new Options();
                  $options->set('isRemoteEnabled', true);
                  $dompdf = new Dompdf($options);
                  $dompdf->loadHtml($ticketCopy);
                  $dompdf->setPaper('A4', 'portrait');
                  $dompdf->render();
                  $fileatt = $dompdf->output();

                  $filename = 'Ticket Copy.pdf';
                  $encoding = 'base64';
                  $type = 'application/pdf';


                  //Invoice Copy
                  $options1 = new Options();
                  $options1->set('isRemoteEnabled', true);
                  $dompdf1 = new Dompdf($options1);
                  $dompdf1->loadHtml($InvoiceCopy);
                  $dompdf1->setPaper('A4', 'portrait');
                  $dompdf1->render();
                  $fileatt1 = $dompdf1->output();

                  $filename1 = 'Invoice Copy.pdf';
                  $encoding1 = 'base64';
                  $type1 = 'application/pdf';

                  //agent mail
                  $AgentEmail = '
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
                            <a style="text-decoration: none; color:#ffffff; font-family: sans-serif; font-size: 25px; padding-top: 20px;"  href="https://www.' . $agentCompanyWebsiteLink . '">' . $agentCompanyName . '</a>

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
                                  Ticket Confirmed
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
                                  Dear ' . $subAgentCompanyName . ', Your Ticket is Confirmed. Thank you
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
                                Booking ID: <span> ' . $bookingId . '</span>
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
                                ' . $agentCompanyName . '
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

                  $mail2 = new PHPMailer();

                  try {
                      $mail2->isSMTP();
                      $mail2->Host = 'b2b.flyfarint.com';
                      $mail2->SMTPAuth = true;
                      $mail2->Username = 'ticketwl@mailservice.center';
                      $mail2->Password = '123Next2$';
                      $mail2->SMTPSecure = 'ssl';
                      $mail2->Port = 465;

                      //Recipients
                      $mail2->setFrom('ticketwl@mailservice.center', $agentCompanyName);
                      $mail2->addAddress("$subAgentEmail", "SubAgentId : $subagentId");
                      $mail2->addCC('otaoperation@flyfarint.com');
                    
                      // $mail2->addCC('habib@flyfarint.com');
                      $mail2->addCC('afridi@flyfarint.com');

                      $mail2->isHTML(true);
                      $mail2->Subject = "Ticketing Request Approved By $agentCompanyName";
                      $mail2->Body = $AgentEmail;
                      $mail2->AddStringAttachment($fileatt, $filename, $encoding, $type);
                      $mail2->AddStringAttachment($fileatt1, $filename1, $encoding1, $type1);

                      if (!$mail2->Send()) {
                          echo "Mailer Error: " . $mail2->ErrorInfo;
                      }
                  } catch (Exception $e) {
                      $response['status'] = "error";
                      $response['message'] = "Mail Doesn't Send";
                  }

                  //subagent mail
                  $subAgentEmail = '
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
                              New Booking Ticketed Confirmed
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
                              Dear ' . $agentCompanyName . ', Your ' . $Route . ', ' . $tripType . ' by ' . $Airlines  . ' Airlines new Booking Ticket Request has been Confirmed.
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
                          background-color: white;
          
                        "
                      >
                      Ticketed By: <span style="color: #dc143c">' . $agentCompanyName.'</span>
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
                                  >' . $bookingId . '</a
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
                              Destination: <span style="color: #dc143c">' . $Route . ' - ' . $From . '</span>
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
                              Travel Date:  <span style="color: #dc143c">' . $travelDate . '	</span>
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
                              Airline: <span style="color: #dc143c"> ' . $Airlines . '	</span>
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
                              Pax:  <span style="color: #dc143c">' . $Pax . '</span>
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
                              Cost:  <span style="color: #dc143c"> ' . $netCost . ' BDT
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
                  Ka 11/2A, Bashundhora R/A Road, Jagannathpur, Dhaka 1229
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
                      $mail3->Host = 'b2b.flyfarint.com';
                      $mail3->SMTPAuth = true;
                      $mail3->Username = 'ticketed@b2b.flyfarint.com';
                      $mail3->Password = '123Next2$';
                      $mail3->SMTPSecure = 'ssl';
                      $mail3->Port = 465;

                      //Recipients
                      $mail3->setFrom('ticketed@flyfarint.com', 'Fly Far International');
                      $mail3->addAddress("$agentCompanyEmail", "AgentId : $agentId");
                      //$mail3->addCC('otaoperation@flyfarint.com');
                      $mail3->addCC('habib@flyfarint.com');
                      $mail3->addCC('afridi@flyfarint.com');

                      $mail3->isHTML(true);
                      $mail3->Subject = "Ticketing Request Approved by Fly Far International";
                      $mail3->Body = $subAgentEmail;
                      $mail3->AddStringAttachment($fileatt, $filename, $encoding, $type);
                      $mail3->AddStringAttachment($fileatt1, $filename1, $encoding1, $type1);

                      if (!$mail3->Send()) {
                          echo "Mailer Error: " . $mail3->ErrorInfo;
                      }
                  } catch (Exception $e) {
                      $response['status'] = "error";
                      $response['message'] = "Mail Doesn't Send";
                  }


                  $fileatCoupen = $ticketCoupon;
                  $Coupenfilename = 'TicketCoupon.txt';
                  $encoding3 = 'base64';
                  $type3 = 'text/plain';
                  
                  //Owner mail
                  $OwnerEmail = '
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
                                New Booking Ticketed Confirmed
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
                                Dear Fly Far International, Our ' . $Route . ', ' . $tripType . ' ticket by ' . $Airlines . ' new Booking Ticket Request has been Confirmed.
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
                            background-color: white;

                          "
                        >
                        Ticketed By: <span style="color: #dc143c">' . $agentCompanyName . '</span>
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
                                    >' . $bookingId . '</a
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
                                Destination: <span style="color: #dc143c">' . $Route . ' - ' . $From . '</span>
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
                                Travel Date:  <span style="color: #dc143c">' . $travelDate . '	</span>
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
                                Airline: <span style="color: #dc143c"> ' . $Airlines . '	</span>
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
                                Pax:  <span style="color: #dc143c">' . $Pax . '</span>
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
                                Cost:  <span style="color: #dc143c"> ' . $netCost . ' BDT
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
                                ' . $agentCompanyName . '

                                </td>
                              </tr>
                            </table>


                          </div>
                        </div>
                      </body>
                    </html>

                    ';


                  $mail4 = new PHPMailer();

                  try {
                      $mail4->isSMTP();
                      $mail4->Host = 'b2b.flyfarint.com';
                      $mail4->SMTPAuth = true;
                      $mail4->Username = 'ticketed@b2b.flyfarint.com';
                      $mail4->Password = '123Next2$';
                      $mail4->SMTPSecure = 'ssl';
                      $mail4->Port = 465;

                      //Recipients
                      $mail4->setFrom('ticketed@flyfarint.com', $agentCompanyName);
                      $mail4->addAddress('otaoperation@flyfarint.com');
                      $mail4->addCC('habib@flyfarint.com');
                      $mail4->addCC('afridi@flyfarint.com');

                      $mail4->isHTML(true);
                      $mail4->Subject = "Ticketing Request Approved by $agentCompanyName";
                      $mail4->Body = $OwnerEmail;
                      $mail4->AddStringAttachment($fileatt, $filename, $encoding, $type);
                      $mail4->AddStringAttachment($fileatt1, $filename1, $encoding1, $type1);
                      $mail4->AddStringAttachment($fileatCoupen, $Coupenfilename, $encoding3, $type3);

                      if (!$mail4->Send()) {
                          echo "Mailer Error: " . $mail4->ErrorInfo;
                      }
                  } catch (Exception $e) {
                      $response['status'] = "error";
                      $response['message'] = "Mail Doesn't Send";
                  }


                  //Agent owner email
                //   $AgentOwnerEmail='
                //   <!DOCTYPE html>
                //   <html lang="en">
                //     <head>
                //       <meta charset="UTF-8" />
                //       <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                //       <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                //       <title>Deposit Request
                //   </title>
                //     </head>
                //     <body>
                //       <div
                //         class="div"
                //         style="
                //           width: 650px;
                //           height: 100vh;
                //           margin: 0 auto;
                //         "
                //       >
                //         <div
                //           style="
                //             width: 650px;
                //             height: 200px;
                //             background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
                //             border-radius: 20px 0px  20px  0px;

                //           "
                //         >
                //           <table
                //             border="0"
                //             cellpadding="0"
                //             cellspacing="0"
                //             align="center"
                //             style="
                //               border-collapse: collapse;
                //               border-spacing: 0;
                //               padding: 0;
                //               width: 650px;
                //               border-radius: 10px;

                //             "
                //           >
                //             <tr>
                //               <td
                //                 align="center"
                //                 valign="top"
                //                 style="
                //                   border-collapse: collapse;
                //                   border-spacing: 0;
                //                   color: #000000;
                //                   font-family: sans-serif;
                //                   font-weight: bold;
                //                   font-size: 20px;
                //                   line-height: 38px;
                //                   padding-top: 30px;
                //                   padding-bottom: 10px;
                //                 "
                //               >
                //                 <a href="https://www.flyfarint.com/"
                //                   ><img
                //                   src="https://cdn.flyfarint.com/logo.png"
                //                     width="130px"
                //                 /></a>

                //               </td>
                //             </tr>
                //           </table>

                //           <table
                //             border="0"
                //             cellpadding="0"
                //             cellspacing="0"
                //             align="center"
                //             bgcolor="white"
                //             style="
                //               border-collapse: collapse;
                //               border-spacing: 0;
                //               padding: 0;
                //               width: 550px;
                //             "
                //           >
                //             <tr>
                //               <td
                //                 align="center"
                //                 valign="top"
                //                 style="
                //                   border-collapse: collapse;
                //                   border-spacing: 0;
                //                   color: #000000;
                //                   font-family: sans-serif;
                //                   text-align: left;
                //                   padding-left: 20px;
                //                   font-weight: bold;
                //                   font-size: 19px;
                //                   line-height: 38px;
                //                   padding-top: 20px;
                //                   background-color: white;


                //                 "
                //               >
                //               New Booking Ticketed Confirmed
                //           </td>
                //             </tr>
                //             <tr>
                //               <td
                //                 align="center"
                //                 valign="top"
                //                 style="
                //                   border-collapse: collapse;
                //                   border-spacing: 0;
                //                   font-family: sans-serif;
                //                   text-align: left;
                //                   padding-left: 20px;
                //                   font-weight: bold;
                //                   padding-top: 15px;
                //                   font-size: 12px;
                //                   line-height: 18px;
                //                   color: #929090;
                //                   padding-right: 20px;
                //                   background-color: white;

                //                 "
                //               >
                //               Dear ' . $agentCompanyName . ', Your ' . $Route . ', ' . $tripType . ' by ' . $Airlines  . ' Airlines new Booking Ticket Request has been Confirmed.
                //       </td>     </tr>

                //       <tr>
                //       <td
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
                //           padding-top: 20px;
                //           width: 100%;
                //           background-color: white;

                //         "
                //       >
                //       Ticketed By: <span style="color: #dc143c">' . $agentCompanyName.'</span>
                //       </td>
                //     </tr>

                //             <tr>
                //               <td
                //                 align="center"
                //                 valign="top"
                //                 style="
                //                   border-collapse: collapse;
                //                   border-spacing: 0;
                //                   color: #000000;
                //                   font-family: sans-serif;
                //                   text-align: left;
                //                   padding-left: 20px;
                //                   font-weight: bold;
                //                   padding-top: 20px;
                //                   font-size: 13px;
                //                   line-height: 18px;
                //                   color: #929090;
                //                   padding-top: 20px;
                //                   width: 100%;
                //                 "
                //               >
                //                 Booking Id:
                //                 <a style="color: #003566" href="http://" target="_blank"
                //                   >' . $bookingId . '</a
                //                 >
                //               </td>
                //             </tr>


                //             <tr>
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
                //                 padding-top: 20px;
                //                 width: 100%;
                //                 background-color: white;

                //               "
                //             >
                //                Destination: <span style="color: #dc143c">' . $Route . ' - ' . $From . '</span>
                //             </td>
                //           </tr>

                //                               <tr>
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
                //                Travel Date:  <span style="color: #dc143c">' . $travelDate . '	</span>
                //             </td>
                //           </tr>
                //                                         <tr>
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
                //                Airline: <span style="color: #dc143c"> ' . $Airlines . '	</span>
                //             </td>
                //           </tr>

                //                                                   <tr>
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
                //                Pax:  <span style="color: #dc143c">' . $Pax . '</span>
                //             </td>
                //           </tr>

                //                                                             <tr>
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
                //                Cost:  <span style="color: #dc143c"> ' . $netCost . ' BDT
                // </span>
                //             </td>
                //           </tr>




                //                <tr>
                //               <td
                //                 align="center"
                //                 valign="top"
                //                 style="
                //                   border-collapse: collapse;
                //                   border-spacing: 0;
                //                   color: #000000;
                //                   font-family: sans-serif;
                //                   text-align: left;
                //                   padding-left: 20px;
                //                   font-weight: bold;
                //                   padding-top: 20px;
                //                   font-size: 13px;
                //                   line-height: 18px;
                //                   color: #929090;
                //                   padding-top: 20px;
                //                   width: 100%;
                //                   background-color: white;

                //                 "
                //               >
                //                      If you have any questions, just contact us we are always happy to
                //                 help you out.
                //               </td>
                //             </tr>


                //                <tr>
                //               <td
                //                 align="center"
                //                 valign="top"
                //                 style="
                //                   border-collapse: collapse;
                //                   border-spacing: 0;
                //                   color: #000000;
                //                   font-family: sans-serif;
                //                   text-align: left;
                //                   padding-left: 20px;
                //                   font-weight: bold;
                //                   padding-top: 20px;
                //                   font-size: 13px;
                //                   line-height: 18px;
                //                   color: #929090;
                //                   padding-top: 20px;
                //                   width: 100%;
                //                   background-color: white;

                //                 "
                //               >
                //                  Sincerely,

                //               </td>
                //             </tr>

                //                <tr>
                //               <td
                //                 align="center"
                //                 valign="top"
                //                 style="
                //                   border-collapse: collapse;
                //                   border-spacing: 0;
                //                   color: #000000;
                //                   font-family: sans-serif;
                //                   text-align: left;
                //                   padding-left: 20px;
                //                   font-weight: bold;
                //                   font-size: 13px;
                //                   line-height: 18px;
                //                   color: #929090;
                //                   width: 100%;
                //                   background-color: white;
                //                   padding-bottom: 20px

                //                 "
                //               >

                //               Fly Far International
                //               </td>
                //             </tr>


                //             <tr>
                //               <td
                //                 align="center"
                //                 valign="top"
                //                 style="
                //                   border-collapse: collapse;
                //                   border-spacing: 0;
                //                   color: #ffffff;
                //                   font-family: sans-serif;
                //                   text-align: center;
                //                   font-weight: 600;
                //                   font-size: 14px;
                //                   color: #ffffff;
                //                   padding-top: 15px;
                //                   background-color: #dc143c;
                //                 "
                //               >
                //                 Need more help?
                //               </td>
                //             </tr>

                //             <tr>
                //               <td
                //                 align="center"
                //                 valign="top"
                //                 style="
                //                   border-collapse: collapse;
                //                   border-spacing: 0;
                //                   color: #ffffff;
                //                   font-family: sans-serif;
                //                   text-align: center;
                //                   font-size: 12px;
                //                   color: #ffffff;
                //                   padding-top: 8px;
                //                   padding-bottom: 20px;
                //                   padding-left: 30px;
                //                   padding-right: 30px;
                //                   background-color: #dc143c;


                //                 "
                //               >
                //                 Mail us at
                //                 <a
                //                   style="color: white; font-size: 13px; text-decoration: none"
                //                   href="http://"
                //                   target="_blank"
                //                   >support@flyfarint.com
                //                 </a>
                //                 agency or Call us at 09606912912
                //               </td>
                //             </tr>

                //             <tr>
                //               <td
                //                 valign="top"
                //                 align="left"
                //                 style="
                //                   border-collapse: collapse;
                //                   border-spacing: 0;
                //                   color: #000000;
                //                   font-family: sans-serif;
                //                   text-align: left;
                //                   font-weight: bold;
                //                   font-size: 12px;
                //                   line-height: 18px;
                //                   color: #929090;
                //                 "
                //               >

                //               <p> <a
                //                   style="
                //                     font-weight: bold;
                //                     font-size: 12px;
                //                     line-height: 15px;
                //                     color: #222222;

                //                   "
                //                   href="https://www.flyfarint.com/terms"
                //                   >Terms & Conditions</a
                //                 >
                //                 <a
                //                   style="
                //                     font-weight: bold;
                //                     font-size: 12px;
                //                     line-height: 15px;
                //                     color: #222222;
                //                     padding-left: 10px;
                //                   "
                //                   href="https://www.flyfarint.com/privacy"
                //                   >Privacy Policy</a
                //                 ></p>
                //               </td>
                //             </tr>
                //             <tr>
                //               <td
                //                 align="center"
                //                 valign="top"
                //                 style="
                //                   border-collapse: collapse;
                //                   border-spacing: 0;
                //                   font-family: sans-serif;
                //                   text-align: center;
                //                   padding-left: 20px;
                //                   font-weight: bold;
                //                   font-size: 12px;
                //                   line-height: 18px;
                //                   color: #929090;
                //                   padding-right: 20px;
                //                 "
                //               >
                //                   <a href="https://www.facebook.com/FlyFarInternational/ "
                //                   ><img
                //                     src="https://cdn.flyfarint.com/fb.png"
                //                     width="25px"
                //                     style="margin: 10px"
                //                 /></a>
                //                 <a href="http:// "
                //                   ><img
                //                     src="https://cdn.flyfarint.com/lin.png"
                //                     width="25px"
                //                     style="margin: 10px"
                //                 /></a>
                //                 <a href="http:// "
                //                   ><img
                //                     src="https://cdn.flyfarint.com/wapp.png "
                //                     width="25px"
                //                     style="margin: 10px"
                //                 /></a>
                //               </td>
                //             </tr>

                //                       <tr>
                //               <td
                //                 align="center"
                //                 valign="top"
                //                 style="
                //                   border-collapse: collapse;
                //                   border-spacing: 0;
                //                   color: #929090;
                //                   font-family: sans-serif;
                //                   text-align: center;
                //                   font-weight: 500;
                //                   font-size: 12px;
                //                   padding-top:5px;
                //                   padding-bottom:5px;
                //                   padding-left:10px;
                //                   padding-right: 10px;
                //                 "
                //               >
                //   Ka 11/2A, Bashundhora R/A Road, Jagannathpur, Dhaka 1229
                //    </td>
                //             </tr>



                //           </table>


                //         </div>
                //       </div>
                //     </body>
                //   </html>


                //   ';

                //   $mail5 = new PHPMailer();

                //   try {
                //       $mail5->isSMTP();
                //       $mail5->Host = 'b2b.flyfarint.com';
                //       $mail5->SMTPAuth = true;
                //       $mail5->Username = 'ticketed@b2b.flyfarint.com';
                //       $mail5->Password = '123Next2$';
                //       $mail5->SMTPSecure = 'ssl';
                //       $mail5->Port = 465;

                //       //Recipients
                //       $mail5->setFrom('ticketed@flyfarint.com', 'Fly Far International');
                //       $mail5->addAddress($agentCompanyEmail);
                //       $mail5->addCC('habib@flyfarint.com');
                //       $mail5->addCC('afridi@flyfarint.com');

                //       $mail5->isHTML(true);
                //       $mail5->Subject = "Ticketing Request Approved by Fly Far International";
                //       $mail5->Body = $AgentOwnerEmail;
                //       $mail5->AddStringAttachment($fileatt, $filename, $encoding, $type);
                //       $mail5->AddStringAttachment($fileatt1, $filename1, $encoding1, $type1);

                //       if (!$mail5->Send()) {
                //           echo "Mailer Error: " . $mail5->ErrorInfo;
                //       }

                //   } catch (Exception $e) {
                //       $response['status'] = "error";
                //       $response['message'] = "Mail Doesn't Send";
                //   }
                // }



                  //Owner maill
                  // $OwnerEmail = '<!DOCTYPE html>
                  //   <html lang="en">
                  //     <head>
                  //       <meta charset="UTF-8" />
                  //       <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                  //       <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                  //       <title>Deposit Request
                  //   </title>
                  //     </head>
                  //     <body>

                  //       <div
                  //         class="div"
                  //         style="
                  //           width: 650px;
                  //           height: 100vh;
                  //           margin: 0 auto;
                  //         "
                  //       >
                  //         <div
                  //           style="
                  //             width: 650px;
                  //             height: 200px;
                  //             background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
                  //             border-radius: 20px 0px  20px  0px;

                  //           "
                  //         >
                  //           <table
                  //             border="0"
                  //             cellpadding="0"
                  //             cellspacing="0"
                  //             align="center"
                  //             style="
                  //               border-collapse: collapse;
                  //               border-spacing: 0;
                  //               padding: 0;
                  //               width: 650px;
                  //               border-radius: 10px;

                  //             "
                  //           >
                  //             <tr>
                  //               <td
                  //                 align="center"
                  //                 valign="top"
                  //                 style="
                  //                   border-collapse: collapse;
                  //                   border-spacing: 0;
                  //                   color: #000000;
                  //                   font-family: sans-serif;
                  //                   font-weight: bold;
                  //                   font-size: 20px;
                  //                   line-height: 38px;
                  //                   padding-top: 30px;
                  //                   padding-bottom: 10px;
                  //                 "
                  //               >
                  //                 <a href="https://www.flyfarint.com/"
                  //                   ><img
                  //                   src="https://cdn.flyfarint.com/logo.png"
                  //                     width="130px"
                  //                 /></a>

                  //               </td>
                  //             </tr>
                  //           </table>

                  //           <table
                  //             border="0"
                  //             cellpadding="0"
                  //             cellspacing="0"
                  //             align="center"
                  //             bgcolor="white"
                  //             style="
                  //               border-collapse: collapse;
                  //               border-spacing: 0;
                  //               padding: 0;
                  //               width: 550px;
                  //             "
                  //           >
                  //             <tr>
                  //               <td
                  //                 align="center"
                  //                 valign="top"
                  //                 style="
                  //                   border-collapse: collapse;
                  //                   border-spacing: 0;
                  //                   color: #000000;
                  //                   font-family: sans-serif;
                  //                   text-align: left;
                  //                   padding-left: 20px;
                  //                   font-weight: bold;
                  //                   font-size: 19px;
                  //                   line-height: 38px;
                  //                   padding-top: 20px;
                  //                   background-color: white;


                  //                 "
                  //               >

                  //               Ticket Confirmed

                  //           </td>
                  //             </tr>
                  //             <tr>
                  //               <td
                  //                 align="center"
                  //                 valign="top"
                  //                 style="
                  //                   border-collapse: collapse;
                  //                   border-spacing: 0;
                  //                   font-family: sans-serif;
                  //                   text-align: left;
                  //                   padding-left: 20px;
                  //                   font-weight: bold;
                  //                   padding-top: 15px;
                  //                   font-size: 12px;
                  //                   line-height: 18px;
                  //                   color: #929090;
                  //                   padding-right: 20px;
                  //                   background-color: white;

                  //                 "
                  //               >
                  //               Dear Fly Far International, Thank you for accepting our ticket issue request. Our Ticket is Confirmed at ' . $DateTime . '.</td>
                  //             </tr>

                  //                       <tr>
                  //               <td
                  //                 align="center"
                  //                 valign="top"
                  //                 style="
                  //                   border-collapse: collapse;
                  //                   border-spacing: 0;
                  //                   color: #000000;
                  //                   font-family: sans-serif;
                  //                   text-align: left;
                  //                   padding-left: 20px;
                  //                   font-weight: bold;
                  //                   padding-top: 20px;
                  //                   font-size: 13px;
                  //                   line-height: 18px;
                  //                   color: #929090;
                  //                   padding-top: 20px;
                  //                   width: 100%;
                  //                   background-color: white;
                  //                 "
                  //               >
                  //               Ticketed By <span style="color:#003566 ;">' . $actionBy . ', Fly Far International </span>
                  //               </td>
                  //             </tr>



                  //               <tr>
                  //               <td
                  //                 align="center"
                  //                 valign="top"
                  //                 style="
                  //                   border-collapse: collapse;
                  //                   border-spacing: 0;
                  //                   color: #000000;
                  //                   font-family: sans-serif;
                  //                   text-align: left;
                  //                   padding-left: 20px;
                  //                   font-weight: bold;
                  //                   padding-top: 20px;
                  //                   font-size: 13px;
                  //                   line-height: 18px;
                  //                   color: #929090;
                  //                   padding-top: 20px;
                  //                   width: 100%;
                  //                   background-color: white;

                  //                 "
                  //               >
                  //               Booking Id:
                  //               <a style="color: #003566; padding-right: 12px" href="http://" target="_blank"
                  //                 >' . $bookingId . '</a
                  //               >
                  //               System:
                  //               <span style="color: #003566; padding-right: 12px" href="http://" target="_blank"
                  //                 >' . $gds . '</span
                  //               >
                  //               System PNR:
                  //               <span style="color: #003566" href="http://" target="_blank"
                  //                 >' . $pnr . '</span
                  //               >
                  //               </td>
                  //             </tr>

                  //                                 <tr>
                  //               <td
                  //                 align="center"
                  //                 valign="top"
                  //                 style="
                  //                   border-collapse: collapse;
                  //                   border-spacing: 0;
                  //                   color: #000000;
                  //                   font-family: sans-serif;
                  //                   text-align: left;
                  //                   padding-left: 20px;
                  //                   font-weight: bold;
                  //                   padding-top: 20px;
                  //                   font-size: 13px;
                  //                   line-height: 18px;
                  //                   color: #929090;
                  //                   padding-top: 20px;
                  //                   width: 100%;
                  //                   background-color: white;

                  //                 "
                  //               >
                  //                  Destination: <span style="color: #dc143c">' . $From . ' - ' . $To . ', ' . $tripType . '</span>
                  //               </td>
                  //             </tr>

                  //                                 <tr>
                  //               <td
                  //                 align="center"
                  //                 valign="top"
                  //                 style="
                  //                   border-collapse: collapse;
                  //                   border-spacing: 0;
                  //                   color: #000000;
                  //                   font-family: sans-serif;
                  //                   text-align: left;
                  //                   padding-left: 20px;
                  //                   font-weight: bold;
                  //                   padding-top: 20px;
                  //                   font-size: 13px;
                  //                   line-height: 18px;
                  //                   color: #929090;
                  //                   padding-top: 10px;
                  //                   width: 100%;
                  //                   background-color: white;

                  //                 "
                  //               >
                  //                  Travel Date:  <span style="color: #dc143c">' . $travelDate . '</span>
                  //             </td>
                  //             </tr>
                  //             <tr>
                  //               <td
                  //                 align="center"
                  //                 valign="top"
                  //                 style="
                  //                   border-collapse: collapse;
                  //                   border-spacing: 0;
                  //                   color: #000000;
                  //                   font-family: sans-serif;
                  //                   text-align: left;
                  //                   padding-left: 20px;
                  //                   font-weight: bold;
                  //                   padding-top: 20px;
                  //                   font-size: 13px;
                  //                   line-height: 18px;
                  //                   color: #929090;
                  //                   padding-top: 10px;
                  //                   width: 100%;
                  //                   background-color: white;

                  //                 "
                  //               >
                  //                  Airline: <span style="color: #dc143c">' . $Airlines . '	</span>
                  //               </td>
                  //             </tr>

                  //                                                     <tr>
                  //               <td
                  //                 align="center"
                  //                 valign="top"
                  //                 style="
                  //                   border-collapse: collapse;
                  //                   border-spacing: 0;
                  //                   color: #000000;
                  //                   font-family: sans-serif;
                  //                   text-align: left;
                  //                   padding-left: 20px;
                  //                   font-weight: bold;
                  //                   padding-top: 20px;
                  //                   font-size: 13px;
                  //                   line-height: 18px;
                  //                   color: #929090;
                  //                   padding-top: 10px;
                  //                   width: 100%;
                  //                   background-color: white;

                  //                 "
                  //               >
                  //                  Pax:  <span style="color: #dc143c">' . $Pax . '</span>
                  //               </td>
                  //             </tr>

                  //                                                               <tr>
                  //               <td
                  //                 align="center"
                  //                 valign="top"
                  //                 style="
                  //                   border-collapse: collapse;
                  //                   border-spacing: 0;
                  //                   color: #000000;
                  //                   font-family: sans-serif;
                  //                   text-align: left;
                  //                   padding-left: 20px;
                  //                   font-weight: bold;
                  //                   padding-top: 20px;
                  //                   font-size: 13px;
                  //                   line-height: 18px;
                  //                   color: #929090;
                  //                   padding-top: 10px;
                  //                   width: 100%;
                  //                   background-color: white;

                  //                 "
                  //               >
                  //                  Cost:  <span style="color: #dc143c">' . $Cost . ' BDT
                  //   </span>
                  //               </td>
                  //             </tr>




                  //                       <tr>
                  //               <td
                  //                 align="center"
                  //                 valign="top"
                  //                 style="
                  //                   border-collapse: collapse;
                  //                   border-spacing: 0;
                  //                   font-family: sans-serif;
                  //                   text-align: left;
                  //                   padding-left: 20px;
                  //                   font-weight: bold;
                  //                   padding-top: 15px;
                  //                   font-size: 12px;
                  //                   line-height: 18px;
                  //                   color: #929090;
                  //                   padding-right: 20px;
                  //                   background-color: white;

                  //                 "
                  //               >
                  //            </td>
                  //             </tr>



                  //                <tr>
                  //               <td
                  //                 align="center"
                  //                 valign="top"
                  //                 style="
                  //                   border-collapse: collapse;
                  //                   border-spacing: 0;
                  //                   color: #000000;
                  //                   font-family: sans-serif;
                  //                   text-align: left;
                  //                   padding-left: 20px;
                  //                   font-weight: bold;
                  //                   padding-top: 20px;
                  //                   font-size: 13px;
                  //                   line-height: 18px;
                  //                   color: #929090;
                  //                   padding-top: 20px;
                  //                   width: 100%;
                  //                   background-color: white;

                  //                 "
                  //               >
                  //                  Sincerely,

                  //               </td>
                  //             </tr>

                  //                <tr>
                  //               <td
                  //                 align="center"
                  //                 valign="top"
                  //                 style="
                  //                   border-collapse: collapse;
                  //                   border-spacing: 0;
                  //                   color: #000000;
                  //                   font-family: sans-serif;
                  //                   text-align: left;
                  //                   padding-left: 20px;
                  //                   font-weight: bold;
                  //                   font-size: 13px;
                  //                   line-height: 18px;
                  //                   color: #929090;
                  //                   width: 100%;
                  //                   background-color: white;
                  //                   padding-bottom: 20px

                  //                 "
                  //               >
                  //               ' . $companyName . '

                  //               </td>
                  //             </tr>

                  //         </div>
                  //       </div>
                  //     </body>
                  //   </html>
                  //   ';

                  // $mail1 = new PHPMailer();

                  // try {
                  //     $mail1->isSMTP();
                  //     $mail1->Host = 'b2b.flyfarint.com';
                  //     $mail1->SMTPAuth = true;
                  //     $mail1->Username = 'ticketed@b2b.flyfarint.com';
                  //     $mail1->Password = '123Next2$';
                  //     $mail1->SMTPSecure = 'ssl';
                  //     $mail1->Port = 465;

                  //     //Recipients
                  //     $mail1->setFrom('ticketed@flyfarint.com', 'Fly Far Int');
                  //     $mail1->addAddress("otaoperation@flyfarint.com", "Ticketed");

                  //     $mail1->AddStringAttachment($fileatt, $filename, $encoding, $type);

                  //     $mail1->isHTML(true);
                  //     $mail1->Subject = "New Ticketing Issue Request Approved by - $companyName";
                  //     $mail1->Body = $OwnerEmail;
                  //     $mail1->AddStringAttachment($fileatt, $filename, $encoding, $type);
                  //     $mail1->AddStringAttachment($fileatt1, $filename1, $encoding1, $type1);

                  //     if (!$mail1->Send()) {
                  //         $response['status'] = "success";
                  //         $response['message'] = "Ticketed Successfully";
                  //         $response['error'] = "Email Not Send";
                  //     } else {
                  //         $response['status'] = "success";
                  //         $response['message'] = "Ticketed Successfully";
                  //     }

                  // } catch (Exception $e) {
                  //     $response['status'] = "error";
                  //     $response['message'] = "Mail Doesn't Send";
                  // }

            
              }

              $response['status'] = "success";
              $response['message'] = "Ticketed Successfully";
              echo json_encode($response);
          } 
      }
  }

}else{
  authorization($conn);
}