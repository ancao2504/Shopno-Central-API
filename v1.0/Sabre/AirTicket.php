<?php
include "../config.php";
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("BookingID",$_GET)){
    $BookingID = $_GET['BookingID'];

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

 if($adult> 0 && $child > 0 && $infant > 0){
    $Passenger ='{
                    "Number": 1
                },
                
                {
                    "Number": 2
                },
                {
                    "Number": 3
                }';
 }else if ($adult> 0){
    $Passenger ='{
                    "Number": 1
                }';
}else{
    $Passenger ='{
                    "Number": 1
                },
                
                {
                    "Number": 2
                }';
}

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://api.platform.sabre.com/v1.2.1/air/ticket',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "AirTicketRQ":{
       "version":"1.2.1",
       "targetCity":"FD3K",
       "DesignatePrinter":{
          "Printers":{
             "Ticket":{
                "CountryCode":"BD"
             },
             "Hardcopy":{
                "LNIATA":"F719FC"
             },
             "InvoiceItinerary":{
                 "LNIATA":"F719FC"
             }
          }
       },
       "Itinerary":{
          "ID":"'.$BookingID.'"
       },
       "Ticketing":[
          {
             "MiscQualifiers":{
                "Commission":{
                   "Percent":7
                }
             },
            "PricingQualifiers": {
              "PriceQuote": [
                {
                  "Record": ['.$Passenger.']
                }
              ]
            }
          }         
      ],
       "PostProcessing":{
          "EndTransaction":{
             "Source":{
                "ReceivedFrom":"SABRE WEB"
             }
          }
       }
    }
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Conversation-ID: 2021.01.DevStudio',
    "Authorization: Bearer $access_token"
  ),
));

$response = curl_exec($curl);

curl_close($curl);
	$sql = "UPDATE `booking` SET `status`='Ticketed' where pnr='$BookingID'";
	if ($conn->query($sql) === TRUE) {
		echo $response;
	}
}else{
    echo json_encode("Error");
}