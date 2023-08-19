<?php

include "../config.php";
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$All= array();

$control = mysqli_query($conn,"SELECT * FROM control where id=1");
$controlrow = mysqli_fetch_array($control,MYSQLI_ASSOC);

if(!empty($controlrow)){
	$Sabre = $controlrow['sabre'];
	// $Galileo = $controlrow['galileo'];
	$Galileo = 0;
	// $FlyHub = $controlrow['flyhub'];
	$FlyHub = 0;
	$gdsPrice = $controlrow['gdsPrice'];
	$farePrice = $controlrow['farePrice'];
}

$Airportsql =  "SELECT name, cityName,countryCode FROM airports WHERE";

print_r($_GET);

if(array_key_exists('tripType',$_GET)){
	
	$Way = $_GET['tripType'];
  
		if($Way == "return"){

			$Gallpax= array();
		
			if(array_key_exists("journeyfrom",$_GET) && array_key_exists("journeyto",$_GET) && array_key_exists("departuredate",$_GET)
				 && array_key_exists("returndate",$_GET)){
				$From = $_GET['journeyfrom'];
				$To = $_GET['journeyto'];
				$dDate = $_GET['departuredate'];
				$rDate = $_GET['returndate'];
				$DepartureDate = $dDate."T00:00:00";
				$ReturnDate = $rDate."T00:00:00";

				// Trip Type
				$fromsql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$From' ");
				$fromrow = mysqli_fetch_array($fromsql,MYSQLI_ASSOC);

				if(!empty($fromrow)){					
					$fromCountry = $fromrow['countryCode'];				
				}

				$tosql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$To' ");
				$torow = mysqli_fetch_array($tosql,MYSQLI_ASSOC);

				if(!empty($torow)){					
					$toCountry = $torow['countryCode'];				
				}

				if($fromCountry == "BD" && $toCountry =="BD"){
					$TripType = "Inbound";
				}else{
					$TripType = "Outbound";
				}
			
				
				if((array_key_exists("adult",$_GET)) && (array_key_exists("child",$_GET) && array_key_exists("infant",$_GET))){

					$adult = $_GET['adult'];
					$child = $_GET['child'];
					$infants = $_GET['infant'];

					$SeatReq = $adult + $child;

					if($adult > 0 && $child> 0 && $infants> 0){
					$SabreRequest = '{
								"Code": "ADT",
								"Quantity": '.$adult.'
							},
							{
								"Code": "C09",
								"Quantity": '.$child.'
							},
							{
								"Code": "INF",
								"Quantity": '.$infants.'
							}';
					
					for($i = 1; $i <= $adult ; $i++){
						$adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" />';
						array_push($Gallpax, $adultcount);
					}
					for($i = 1; $i <= $child ; $i++){
						$childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="CNN" Age="09" />';
						array_push($Gallpax,$childcount);
					}
					for($i = 1; $i <= $infants ; $i++){
						$infantscount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="INF" Age="1" />';
						array_push($Gallpax, $infantscount);
					
					}


							
					}else if($adult > 0 && $child > 0){

					$SabreRequest = '{
									"Code": "ADT",
									"Quantity": '.$adult.'
								},
								{
									"Code": "C09",
									"Quantity": '.$child.'
								}';

					for($i = 1; $i <= $adult ; $i++){
						$adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" />';
						array_push($Gallpax, $adultcount);
					}
					for($i = 1; $i <= $child ; $i++){
						$childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="CNN" Age="09" />';
						array_push($Gallpax,$childcount);
					}
					
					}else if($adult > 0 && $infants > 0){
					$SabreRequest = '{
								"Code": "ADT",
								"Quantity": '.$adult.'
								},
								{
									"Code": "INF",
									"Quantity": '.$infants.'
								}';
					for($i = 1; $i <= $adult ; $i++){
						$adultcount ='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" />';
						array_push($Gallpax, $adultcount);
					}
					for($i = 1; $i <= $infants ; $i++){
						$infantscount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="INF" Age="1" />';
						array_push($Gallpax, $infantscount);
					
					}

					}else{
					$SabreRequest = '{
								"Code": "ADT",
								"Quantity": '.$adult.'
							}';
					for($i = 1; $i <= $adult ; $i++){
						$adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" />';
						array_push($Gallpax, $adultcount);
					}

				}


echo($Sabre);
if($Sabre == 1) // Sabre Start


echo ("h");
try{
	$client_id= base64_encode("V1:351640:27YK:AA");
	$client_secret = base64_encode("spt5164");
	
	$token = base64_encode($client_id.":".$client_secret);
	
	$curl = curl_init();
	
	curl_setopt_array($curl, array(
	  CURLOPT_URL => 'https://api.platform.sabre.com/v2/auth/token',
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => '',
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 0,
	  CURLOPT_FOLLOWLOCATION => true,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => 'POST',
	  CURLOPT_POSTFIELDS =>'grant_type=client_credentials',
	  CURLOPT_HTTPHEADER => array(
		'Content-Type: application/x-www-form-urlencoded',
		"Authorization: Basic $token"
	  ),
	));
	$Tokenres = curl_exec($curl);
	curl_close($curl);
	print_r ($Tokenres);
	$resToken = json_decode($Tokenres, true);
	$access_token = $resToken['access_token'];


}catch (Exception $e){ 
	
}


$curl = curl_init();


if(isset($access_token)){

curl_setopt_array($curl, array(
//CURLOPT_URL => 'https://api-crt.cert.havail.sabre.com/v4/offers/shop',
CURLOPT_URL => 'https://api.platform.sabre.com/v4/offers/shop',
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => '',
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 0,
CURLOPT_FOLLOWLOCATION => true,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => 'POST',
CURLOPT_POSTFIELDS => '{
			"OTA_AirLowFareSearchRQ": {
				"Version": "4",
				"POS": {
					"Source": [{
							"PseudoCityCode": "FD3K",
							"RequestorID": {
								"Type": "1",
								"ID": "1",
								"CompanyName": {
									"Code": "TN"
								}
							}
						}
					]
				},
				"OriginDestinationInformation": [
					{
						"RPH": "1",
						"DepartureDateTime": "'.$DepartureDate.'",
						"OriginLocation": {
							"LocationCode": "'.$From.'"
						},
						"DestinationLocation": {
							"LocationCode": "'.$To.'"
						}
					},{
						"RPH": "2",
						"DepartureDateTime": "'.$ReturnDate.'",
						"OriginLocation": {
							"LocationCode": "'.$To.'"
						},
						"DestinationLocation": {
							"LocationCode": "'.$From.'"
						}
					}
				],
				"TravelPreferences": {
					"TPA_Extensions": {
						"DataSources": {
							"NDC": "Disable",
							"ATPCO": "Enable",
							"LCC": "Disable"
						},
				"PreferNDCSourceOnTie": {
				"Value": true
				}
					}
				},
				"TravelerInfoSummary": {
					"AirTravelerAvail": [{
							"PassengerTypeQuantity": ['.$SabreRequest.']
						}
					]       
				},
				"TPA_Extensions": {
					"IntelliSellTransaction": {
						"RequestType": {
							"Name": "100ITINS"
						}
					}
				}
			}
		}',
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Conversation-ID: 2021.01.DevStudio',
			"Authorization: Bearer $access_token",
		),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		$result = json_decode($response, true);

		if(isset($result['groupedItineraryResponse']['itineraryGroups'])){
			$SabreItenary = $result['groupedItineraryResponse']['itineraryGroups'];
			//print_r($SabreItenary);
		}
				
		//print_r($result);
		if(array_key_exists('groupedItineraryResponse', $result)){
			if($result['groupedItineraryResponse']['statistics']['itineraryCount'] > 0){
					if($To == 'DXB' || $From =='DXB'){

						if(isset($SabreItenary[0]['itineraries']) && isset($SabreItenary[1]['itineraries'])){
							if(count($SabreItenary[0]['itineraries']) > count($SabreItenary[1]['itineraries'])){                            
									$flightListSabre = $SabreItenary[0]['itineraries'];                                                      
							}else{                           
								$flightListSabre = $SabreItenary[1]['itineraries'];                           
							}
						}else{
							$flightListSabre = $SabreItenary[0]['itineraries'];
						}


					}else{
						$flightListSabre = $result['groupedItineraryResponse']['itineraryGroups'][0]['itineraries'];
						//echo count($flightList);
					}
					
					$scheduleDescs = $result['groupedItineraryResponse']['scheduleDescs'];
					$legDescs = $result['groupedItineraryResponse']['legDescs'];

					$Bag = $result['groupedItineraryResponse']['baggageAllowanceDescs'];

				}
			}         
		}
	}
}



		if(isset($flightListSabre)){
		$i = 0;
		foreach($flightListSabre as $var){
			$i++;
			$idd = $var['id'];
			$pricingSource = $var['pricingSource'];
			$vCarCode = $var['pricingInformation'][0]['fare']['validatingCarrierCode'];

			$sql = mysqli_query($conn,"SELECT name, commission FROM airlines WHERE code='$vCarCode' ");
			$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

			if(!empty($row)){
				$CarrieerName = $row['name']; 
				$fareRate= $row['commission'];	                      
			}

			if(isset($var['pricingInformation'][0]['fare']['lastTicketDate'])
				&& isset($var['pricingInformation'][0]['fare']['lastTicketTime'])){
					
				$lastTicketDate = $var['pricingInformation'][0]['fare']['lastTicketDate'];
				$lastTicketTime = $var['pricingInformation'][0]['fare']['lastTicketTime'];
				$timelimit = "$lastTicketDate $lastTicketTime";								
			}else{
					$timelimit = " ";
			}
			

			$passengerInfo = $var['pricingInformation'][0]['fare']['passengerInfoList'][0]['passengerInfo']; 
			$fareComponents = $passengerInfo['fareComponents'];

			$Class = $fareComponents[0]['segments'][0]['segment']['cabinCode'];  
			
			$PriceInfo = $var['pricingInformation'][0]['fare']['passengerInfoList'];
			
			if($fareRate == 7){	
				if($From != "DAC" && $vCarCode =="SV"){
					$baseFareAmount =  ceil(($var['pricingInformation'][0]['fare']['totalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
					$totalTaxAmount = ceil(($var['pricingInformation'][0]['fare']['totalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);							
					$totalFare = ceil(($var['pricingInformation'][0]['fare']['totalFare']['totalPrice'] / $gdsPrice) * $farePrice);
					
					$AgentPrice = floor((($baseFareAmount * 0.93) + $totalTaxAmount) + ($totalFare* 0.003));
					$Commission = $totalFare - $AgentPrice;

					//Price Break Down
					if($adult > 0 && $child > 0 &&  $infants > 0){
					
						
						$adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
						$adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);


						$childBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
						$childTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
						

						$infantBasePrice = ceil(($PriceInfo[2]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
						$infantTaxAmount = ceil(($PriceInfo[2]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
						

						$PriceBreakDown = array("0" =>
											array("BaseFare"=> "$adultBasePrice",
											"Tax"=> "$adultTaxAmount",
											"PaxCount"=> $adult,
											"PaxType"=> "ADT",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")
											,
											"1" =>
											array("BaseFare"=> "$childBasePrice",
											"Tax"=> "$childTaxAmount",
											"PaxCount"=> $child,
											"PaxType"=> "CNN",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0"),
											"2" =>
											array("BaseFare"=> "$infantBasePrice",
											"Tax"=> "$infantTaxAmount",
											"PaxCount"=> $infants,
											"PaxType"=> "INF",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")                                         

									);



					}else if($adult > 0 && $child > 0){
						$adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount']/ $gdsPrice) * $farePrice);
						$adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);


						$childBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
						$childTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount']  / $gdsPrice) * $farePrice);

					}else if($adult > 0 && $infants > 0){
						$adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
						$adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
						
						$infantBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
						$infantTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

						
					}else if($adult> 0){
													
						$adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount']/ $gdsPrice) * $farePrice);
						$adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
						
						$PriceBreakDown = array("0" =>
											array("BaseFare"=> "$adultBasePrice",
											"Tax"=> "$adultTaxAmount",
											"PaxCount"=> $adult,
											"PaxType"=> "ADT",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")												                                     

									);
					}
					
					
					
				}else if($From != "DAC" && $vCarCode =="SQ"){

					$baseFareAmount =  $var['pricingInformation'][0]['fare']['totalFare']['equivalentAmount'];
					$totalTaxAmount = $var['pricingInformation'][0]['fare']['totalFare']['totalTaxAmount'];							
					$totalFare = $var['pricingInformation'][0]['fare']['totalFare']['totalPrice'] ;
					
					$AgentPrice = $totalFare;
					$Commission = 0;

					
					if($adult > 0 && $child > 0 &&  $infants > 0){									
						
						$adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
						$adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
						

						$childBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
						$childTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
						
						$infantBasePrice = ceil(($PriceInfo[2]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
						$infantTaxAmount = ceil(($PriceInfo[2]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);


						$PriceBreakDown = array("0" =>
											array("BaseFare"=> "$adultBasePrice",
											"Tax"=> "$adultTaxAmount",
											"PaxCount"=> $adult,
											"PaxType"=> "ADT",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")
											,
											"1" =>
											array("BaseFare"=> "$childBasePrice",
											"Tax"=> "$childTaxAmount",
											"PaxCount"=> $child,
											"PaxType"=> "CNN",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0"),
											"2" =>
											array("BaseFare"=> "$infantBasePrice",
											"Tax"=> "$infantTaxAmount",
											"PaxCount"=> $infants,
											"PaxType"=> "INF",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")                                         

									);



					}else if($adult > 0 && $child > 0){
						$adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
						$adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

						$childBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
						$childTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);


						$PriceBreakDown = array("0" =>
											array("BaseFare"=> "$adultBasePrice",
											"Tax"=> "$adultTaxAmount",
											"PaxCount"=> $adult,
											"PaxType"=> "ADT",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")
											,
											"1" =>
											array("BaseFare"=> "$childBasePrice",
											"Tax"=> "$childTaxAmount",
											"PaxCount"=> $child,
											"PaxType"=> "CNN",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")                                      

									);

					}else if($adult > 0 && $infants > 0){
						$adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
						$adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
						
						$infantBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
						$infantTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount']  / $gdsPrice) * $farePrice);
						$PriceBreakDown = array("0" =>
											array("BaseFare"=> "$adultBasePrice",
											"Tax"=> "$adultTaxAmount",
											"PaxCount"=> $adult,
											"PaxType"=> "ADT",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")
											,
											"1" =>															
											array("BaseFare"=> "$infantBasePrice",
											"Tax"=> "$infantTaxAmount",
											"PaxCount"=> $infants,
											"PaxType"=> "INF",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")                                         

									);

					}else if($adult> 0){
													
						$adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount']/ $gdsPrice) * $farePrice);
						$adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
						
						$PriceBreakDown = array("0" =>
											array("BaseFare"=> "$adultBasePrice",
											"Tax"=> "$adultTaxAmount",
											"PaxCount"=> $adult,
											"PaxType"=> "ADT",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")												                                     

									);
					}
																							
				}else if($From != "DAC" && $vCarCode =="EY"){

						$baseFareAmount =  $var['pricingInformation'][0]['fare']['totalFare']['equivalentAmount'];
						$totalTaxAmount = $var['pricingInformation'][0]['fare']['totalFare']['totalTaxAmount'];							
						$totalFare = $var['pricingInformation'][0]['fare']['totalFare']['totalPrice'] ;
						
						$AgentPrice = $totalFare;
						$Commission = 0;

						
						if($adult > 0 && $child > 0 &&  $infants > 0){									
							
							$adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
							$adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
							

							$childBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
							$childTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
							
							$infantBasePrice = ceil(($PriceInfo[2]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
							$infantTaxAmount = ceil(($PriceInfo[2]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);


							$PriceBreakDown = array("0" =>
												array("BaseFare"=> "$adultBasePrice",
												"Tax"=> "$adultTaxAmount",
												"PaxCount"=> $adult,
												"PaxType"=> "ADT",
												"Discount"=> "0",
												"OtherCharges"=> "0",
												"ServiceFee"=> "0")
												,
												"1" =>
												array("BaseFare"=> "$childBasePrice",
												"Tax"=> "$childTaxAmount",
												"PaxCount"=> $child,
												"PaxType"=> "CNN",
												"Discount"=> "0",
												"OtherCharges"=> "0",
												"ServiceFee"=> "0"),
												"2" =>
												array("BaseFare"=> "$infantBasePrice",
												"Tax"=> "$infantTaxAmount",
												"PaxCount"=> $infants,
												"PaxType"=> "INF",
												"Discount"=> "0",
												"OtherCharges"=> "0",
												"ServiceFee"=> "0")                                         

										);



						}else if($adult > 0 && $child > 0){
							$adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
							$adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

							$childBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
							$childTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);


							$PriceBreakDown = array("0" =>
												array("BaseFare"=> "$adultBasePrice",
												"Tax"=> "$adultTaxAmount",
												"PaxCount"=> $adult,
												"PaxType"=> "ADT",
												"Discount"=> "0",
												"OtherCharges"=> "0",
												"ServiceFee"=> "0")
												,
												"1" =>
												array("BaseFare"=> "$childBasePrice",
												"Tax"=> "$childTaxAmount",
												"PaxCount"=> $child,
												"PaxType"=> "CNN",
												"Discount"=> "0",
												"OtherCharges"=> "0",
												"ServiceFee"=> "0")                                      

										);

						}else if($adult > 0 && $infants > 0){
							$adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
							$adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
							
							$infantBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
							$infantTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount']  / $gdsPrice) * $farePrice);
							$PriceBreakDown = array("0" =>
												array("BaseFare"=> "$adultBasePrice",
												"Tax"=> "$adultTaxAmount",
												"PaxCount"=> $adult,
												"PaxType"=> "ADT",
												"Discount"=> "0",
												"OtherCharges"=> "0",
												"ServiceFee"=> "0")
												,
												"1" =>															
												array("BaseFare"=> "$infantBasePrice",
												"Tax"=> "$infantTaxAmount",
												"PaxCount"=> $infants,
												"PaxType"=> "INF",
												"Discount"=> "0",
												"OtherCharges"=> "0",
												"ServiceFee"=> "0")                                         

										);

						}else if($adult> 0){
														
							$adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount']/ $gdsPrice) * $farePrice);
							$adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
							
							$PriceBreakDown = array("0" =>
												array("BaseFare"=> "$adultBasePrice",
												"Tax"=> "$adultTaxAmount",
												"PaxCount"=> $adult,
												"PaxType"=> "ADT",
												"Discount"=> "0",
												"OtherCharges"=> "0",
												"ServiceFee"=> "0")												                                     

										);
						}
																								
				}else{
									$baseFareAmount =  $var['pricingInformation'][0]['fare']['totalFare']['equivalentAmount'];
									$totalTaxAmount = $var['pricingInformation'][0]['fare']['totalFare']['totalTaxAmount'];							
									$totalFare = $var['pricingInformation'][0]['fare']['totalFare']['totalPrice'] ;
									
									$AgentPrice = floor((($baseFareAmount * 0.93) + $totalTaxAmount) + ($totalFare* 0.003));
									$Commission = $totalFare - $AgentPrice;

									if($adult > 0 && $child > 0 &&  $infants > 0){
										
										$adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
										$adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];
										

										$childBasePrice = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
										$childTaxAmount = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];
										
										$infantBasePrice = $PriceInfo[2]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
										$infantTaxAmount = $PriceInfo[2]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];


										$PriceBreakDown = array("0" =>
															array("BaseFare"=> "$adultBasePrice",
															"Tax"=> "$adultTaxAmount",
															"PaxCount"=> $adult,
															"PaxType"=> "ADT",
															"Discount"=> "0",
															"OtherCharges"=> "0",
															"ServiceFee"=> "0")
															,
															"1" =>
															array("BaseFare"=> "$childBasePrice",
															"Tax"=> "$childTaxAmount",
															"PaxCount"=> $child,
															"PaxType"=> "CNN",
															"Discount"=> "0",
															"OtherCharges"=> "0",
															"ServiceFee"=> "0"),
															"2" =>
															array("BaseFare"=> "$infantBasePrice",
															"Tax"=> "$infantTaxAmount",
															"PaxCount"=> $infants,
															"PaxType"=> "INF",
															"Discount"=> "0",
															"OtherCharges"=> "0",
															"ServiceFee"=> "0")                                         

													);



									}else if($adult > 0 && $child > 0){
										$adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
										$adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];
										

										$childBasePrice = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
										$childTaxAmount = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];
										

										$PriceBreakDown = array("0" =>
															array("BaseFare"=> "$adultBasePrice",
															"Tax"=> "$adultTaxAmount",
															"PaxCount"=> $adult,
															"PaxType"=> "ADT",
															"Discount"=> "0",
															"OtherCharges"=> "0",
															"ServiceFee"=> "0")
															,
															"1" =>
															array("BaseFare"=> "$childBasePrice",
															"Tax"=> "$childTaxAmount",
															"PaxCount"=> $child,
															"PaxType"=> "CNN",
															"Discount"=> "0",
															"OtherCharges"=> "0",
															"ServiceFee"=> "0")                                        

													);


									}else if($adult > 0 && $infants > 0){
										$adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
										$adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];
										
										
										$infantBasePrice = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
										$infantTaxAmount = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

										$PriceBreakDown = array("0" =>
															array("BaseFare"=> "$adultBasePrice",
															"Tax"=> "$adultTaxAmount",
															"PaxCount"=> $adult,
															"PaxType"=> "ADT",
															"Discount"=> "0",
															"OtherCharges"=> "0",
															"ServiceFee"=> "0")
															,
															"1" =>
															
															array("BaseFare"=> "$infantBasePrice",
															"Tax"=> "$infantTaxAmount",
															"PaxCount"=> $infants,
															"PaxType"=> "INF",
															"Discount"=> "0",
															"OtherCharges"=> "0",
															"ServiceFee"=> "0")                                         

													);

										
									}else if($adult> 0){
																	
										$adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
										$adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];
										
										$PriceBreakDown = array("0" =>
															array("BaseFare"=> "$adultBasePrice",
															"Tax"=> "$adultTaxAmount",
															"PaxCount"=> $adult,
															"PaxType"=> "ADT",
															"Discount"=> "0",
															"OtherCharges"=> "0",
															"ServiceFee"=> "0")												                                     

													);
									}
								}																							
			}else if($fareRate == 3){
				if($vCarCode == "FZ" || $vCarCode == "EY"){
					$baseFareAmount =  $var['pricingInformation'][0]['fare']['totalFare']['equivalentAmount'];
					$totalTaxAmount = $var['pricingInformation'][0]['fare']['totalFare']['totalTaxAmount'];							
					$totalFare = $var['pricingInformation'][0]['fare']['totalFare']['totalPrice'] ;
					
					$AgentPrice = $totalFare;
					$Commission = $totalFare - $AgentPrice;

					if($adult > 0 && $child > 0 &&  $infants > 0){
						
						$adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
						$adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];
						

						$childBasePrice = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
						$childTaxAmount = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];
						
						$infantBasePrice = $PriceInfo[2]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
						$infantTaxAmount = $PriceInfo[2]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];


						$PriceBreakDown = array("0" =>
											array("BaseFare"=> "$adultBasePrice",
											"Tax"=> "$adultTaxAmount",
											"PaxCount"=> $adult,
											"PaxType"=> "ADT",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")
											,
											"1" =>
											array("BaseFare"=> "$childBasePrice",
											"Tax"=> "$childTaxAmount",
											"PaxCount"=> $child,
											"PaxType"=> "CNN",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0"),
											"2" =>
											array("BaseFare"=> "$infantBasePrice",
											"Tax"=> "$infantTaxAmount",
											"PaxCount"=> $infants,
											"PaxType"=> "INF",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")                                         

									);



					}else if($adult > 0 && $child > 0){
						$adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
						$adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];
						

						$childBasePrice = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
						$childTaxAmount = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];
						

						$PriceBreakDown = array("0" =>
											array("BaseFare"=> "$adultBasePrice",
											"Tax"=> "$adultTaxAmount",
											"PaxCount"=> $adult,
											"PaxType"=> "ADT",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")
											,
											"1" =>
											array("BaseFare"=> "$childBasePrice",
											"Tax"=> "$childTaxAmount",
											"PaxCount"=> $child,
											"PaxType"=> "CNN",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")                                        

									);


					}else if($adult > 0 && $infants > 0){
						$adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
						$adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];
						
						
						$infantBasePrice = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
						$infantTaxAmount = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

						$PriceBreakDown = array("0" =>
											array("BaseFare"=> "$adultBasePrice",
											"Tax"=> "$adultTaxAmount",
											"PaxCount"=> $adult,
											"PaxType"=> "ADT",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")
											,
											"1" =>
											
											array("BaseFare"=> "$infantBasePrice",
											"Tax"=> "$infantTaxAmount",
											"PaxCount"=> $infants,
											"PaxType"=> "INF",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")                                         

									);

						
					}else if($adult> 0){
													
						$adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
						$adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];
						
						$PriceBreakDown = array("0" =>
											array("BaseFare"=> "$adultBasePrice",
											"Tax"=> "$adultTaxAmount",
											"PaxCount"=> $adult,
											"PaxType"=> "ADT",
											"Discount"=> "0",
											"OtherCharges"=> "0",
											"ServiceFee"=> "0")												                                     

									);
					}
				
				}
			}else{
				$baseFareAmount =  ceil(($var['pricingInformation'][0]['fare']['totalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
				$totalTaxAmount = ceil(($var['pricingInformation'][0]['fare']['totalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);							
				$totalFare = ceil(($var['pricingInformation'][0]['fare']['totalFare']['totalPrice'] / $gdsPrice) * $farePrice);
				
				$AgentPrice = floor((($baseFareAmount * 0.93) + $totalTaxAmount) + ($totalFare* 0.003));
				$Commission = $totalFare - $AgentPrice;	
				
				//Price Break Down
				if($adult > 0 && $child > 0 &&  $infants > 0){
					
					
					$adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
					$adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);


					$childBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
					$childTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
					

					$infantBasePrice = ceil(($PriceInfo[2]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
					$infantTaxAmount = ceil(($PriceInfo[2]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
					

					$PriceBreakDown = array("0" =>
										array("BaseFare"=> "$adultBasePrice",
										"Tax"=> "$adultTaxAmount",
										"PaxCount"=> $adult,
										"PaxType"=> "ADT",
										"Discount"=> "0",
										"OtherCharges"=> "0",
										"ServiceFee"=> "0")
										,
										"1" =>
										array("BaseFare"=> "$childBasePrice",
										"Tax"=> "$childTaxAmount",
										"PaxCount"=> $child,
										"PaxType"=> "CNN",
										"Discount"=> "0",
										"OtherCharges"=> "0",
										"ServiceFee"=> "0"),
										"2" =>
										array("BaseFare"=> "$infantBasePrice",
										"Tax"=> "$infantTaxAmount",
										"PaxCount"=> $infants,
										"PaxType"=> "INF",
										"Discount"=> "0",
										"OtherCharges"=> "0",
										"ServiceFee"=> "0")                                         

								);



				}else if($adult > 0 && $child > 0){
					$adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
					$adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];


					$childBasePrice = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
					$childTaxAmount = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

					$PriceBreakDown = array("0" =>
										array("BaseFare"=> "$adultBasePrice",
										"Tax"=> "$adultTaxAmount",
										"PaxCount"=> $adult,
										"PaxType"=> "ADT",
										"Discount"=> "0",
										"OtherCharges"=> "0",
										"ServiceFee"=> "0")
										,
										"1" =>
										array("BaseFare"=> "$childBasePrice",
										"Tax"=> "$childTaxAmount",
										"PaxCount"=> $adult,
										"PaxType"=> "CNN",
										"Discount"=> "0",
										"OtherCharges"=> "0",
										"ServiceFee"=> "0")
																					

								);

				}else if($adult > 0 && $infants > 0){
					$adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
					$adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];
					
					$infantBasePrice = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
					$infantTaxAmount = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

					$PriceBreakDown = array("0" =>
										array("BaseFare"=> "$adultBasePrice",
										"Tax"=> "$adultTaxAmount",
										"PaxCount"=> $adult,
										"PaxType"=> "ADT",
										"Discount"=> "0",
										"OtherCharges"=> "0",
										"ServiceFee"=> "0")
										,
										"1" =>
										
										array("BaseFare"=> "$infantBasePrice",
										"Tax"=> "$infantTaxAmount",
										"PaxCount"=> $adult,
										"PaxType"=> "INF",
										"Discount"=> "0",
										"OtherCharges"=> "0",
										"ServiceFee"=> "0")                                         

								);
				}else if($adult> 0){
												
					$adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
					$adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
					
					$PriceBreakDown = array("0" =>
										array("BaseFare"=> "$adultBasePrice",
										"Tax"=> "$adultTaxAmount",
										"PaxCount"=> $adult,
										"PaxType"=> "ADT",
										"Discount"=> "0",
										"OtherCharges"=> "0",
										"ServiceFee"=> "0")												                                     

					);
				}
			}

			
			$BegRef= $passengerInfo['baggageInformation'][0]['allowance']['ref'];           
			$BegId = $BegRef - 1;

			if($Class == 'Y'){
				$CabinClass = "Economy";
			}


			//$Bagage = $Bag[$BegRef]['weight'];

			if(isset($Bag[$BegId]['weight'])){
				$Bags = $Bag[$BegId]['weight'];
			}else if(isset($Bag[$BegId]['pieceCount'])){
				$Bags = $Bag[$BegId]['pieceCount'];
			}else{
				$Bags = "0";
			}

			$nonRefundable = $passengerInfo['nonRefundable'];
			if($nonRefundable == 1){
				$nonRef = "Non Refundable";
					
			}else{
				$nonRef = "Refundable";

			}


				
				//Go
				$ref1 = $var['legs'][0]['ref'];
				$id1 = $ref1 - 1;

				//Return
				$ref2 = $var['legs'][1]['ref'];
				$id2 = $ref2 - 1;

				//Segment Count
				$sgCount1 = count($legDescs[$id1]['schedules']); //echo $sgCount1;
				$sgCount2 = count($legDescs[$id2]['schedules']); //echo $sgCount2;


				//Go Flight Duration 1 
				$goTotalElapesd = $legDescs[$id1]['elapsedTime'];


				//Back Flight Duration 1 
				$backTotalElapesd = $legDescs[$id2]['elapsedTime'];


				
             //For Going Way
			if($sgCount1 ==  1 & $sgCount2 == 1){

				//Go 
				$golf1 = $legDescs[$id1]['schedules'][0]['ref'];
				$golegrefs = $golf1- 1;

				$godepartureTime =  substr($scheduleDescs[$golegrefs]['departure']['time'],0,5);
				$godpTime = date("D d M Y", strtotime($dDate." ".$godepartureTime));
				$godpTimedate = $dDate."T".$godepartureTime.':00';

				$goArrivalTime = substr($scheduleDescs[$golegrefs]['arrival']['time'],0,5);
				$goarrivalDate = 0;
				if(isset($scheduleDescs[$golegrefs]['arrival']['dateAdjustment'])){
					$goarrivalDate += 1;
				}


				if($goarrivalDate == 1){
					$goaDate = date('Y-m-d', strtotime("+1 day", strtotime($dDate)));
				}else{
					$goaDate = $dDate;
				}

				$goarrTime = date("D d M Y", strtotime($goaDate." ".$goArrivalTime));
				$goarrTimedate = $goaDate."T".$goArrivalTime.':00';

				$goArrivalTo = $scheduleDescs[$golegrefs]['arrival']['airport'];
				$goDepartureFrom = $scheduleDescs[$golegrefs]['departure']['airport'];
				$gomarkettingCarrier = $scheduleDescs[$golegrefs]['carrier']['marketing'];

				$goCrsql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
				$goCrrow = mysqli_fetch_array($goCrsql,MYSQLI_ASSOC);

				if(!empty($goCrrow)){
					$gomarkettingCarrierName = $goCrrow['name'];				
				}

				// Departure Country
				$goDeptsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
				$goDeptrow = mysqli_fetch_array($goDeptsql,MYSQLI_ASSOC);

				if(!empty($goDeptrow)){
					$godAirport = $goDeptrow['name'];
					$godCity = $goDeptrow['cityName'];
					$godCountry = $goDeptrow['countryCode'];				
				}

				// Arrival Country
				$goArrsql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
				$goArrrow = mysqli_fetch_array($goArrsql,MYSQLI_ASSOC);

				if(!empty($goArrrow)){
					$goaAirport = $goArrrow['name'];
					$goaCity = $goArrrow['cityName'];
					$goaCountry = $goArrrow['countryCode'];				
				}


				$gomarkettingFN = $scheduleDescs[$golegrefs]['carrier']['marketingFlightNumber'];				
				$gooperatingCarrier = $scheduleDescs[$golegrefs]['carrier']['operating'];
				if(isset($scheduleDescs[$golegrefs]['carrier']['operatingFlightNumber'])){
					$gooperatingFN = $scheduleDescs[$golegrefs]['carrier']['operatingFlightNumber'];
				}else{
					$gooperatingFN = 1;
				}

				if(isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
					$goSeat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
				}else if(!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
					$goSeat = "9";
				}

				if(isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])){
						$goBookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
				}else{
					$goBookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
				}

				

				$goElapsedTime = $legDescs[$id1]['elapsedTime'];
				$goTravelTime = floor($goElapsedTime / 60)."H ".($goElapsedTime - ((floor($goElapsedTime / 60)) * 60))."Min";



				//Return

				$backlf1 = $legDescs[$id2]['schedules'][0]['ref'];
				$backlegrefs = $backlf1 - 1;                           
				$backArrivalTime = substr($scheduleDescs[$backlegrefs]['arrival']['time'],0,5);

				$backarrivalDate = 0;
				if(isset($scheduleDescs[$backlegrefs]['arrival']['dateAdjustment'])){
					$backarrivalDate += 1;
				}

				if($backarrivalDate == 1){
					$backaDate = date('Y-m-d', strtotime("+1 day", strtotime($rDate)));
					}else{
					$backaDate = $rDate;
				}

				$backdepartureTime =  substr($scheduleDescs[$backlegrefs]['departure']['time'],0,5);                         
				$backdpTime = date("D d M Y", strtotime($rDate." ".$backdepartureTime));
				$backdpTimedate = $rDate."T".$backdepartureTime.':00';


				$backarrivalTime =  substr($scheduleDescs[$backlegrefs]['arrival']['time'],0,5);
				$backarrTime = date("D d M Y", strtotime($backaDate." ".$backarrivalTime));
				$backarrTimedate = $backaDate."T".$backarrivalTime.':00';

				$backArrivalTo = $scheduleDescs[$backlegrefs]['arrival']['airport'];
				$backDepartureFrom = $scheduleDescs[$backlegrefs]['departure']['airport'];
				$backmarkettingCarrier = $scheduleDescs[$backlegrefs]['carrier']['marketing']; 

				$backCrsql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
				$backCrrow = mysqli_fetch_array($backCrsql,MYSQLI_ASSOC);

				if(!empty($backCrrow)){
					$backmarkettingCarrierName = $backCrrow['name'];				
				}

				// Departure Country
				$backDeptsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
				$backDeptrow = mysqli_fetch_array($backDeptsql,MYSQLI_ASSOC);

				if(!empty($backDeptrow)){
					$backdAirport = $backDeptrow['name'];
					$backdCity = $backDeptrow['cityName'];
					$backdCountry = $backDeptrow['countryCode'];				
				}

				// Arivalr Country
				$backArrsql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo' ");
				$backArrrow = mysqli_fetch_array($backArrsql,MYSQLI_ASSOC);

				if(!empty($backArrrow)){
				$backaAirport = $backArrrow['name'];
				$backaCity = $backArrrow['cityName'];
				$backaCountry = $backArrrow['countryCode'];
				
				}


				$backmarkettingFN = $scheduleDescs[$backlegrefs]['carrier']['marketingFlightNumber'];
				$backoperatingCarrier = $scheduleDescs[$backlegrefs]['carrier']['operating'];

				if(isset($scheduleDescs[$backlegrefs]['carrier']['operatingFlightNumber'])){
					$backoperatingFN = $scheduleDescs[$backlegrefs]['carrier']['operatingFlightNumber'];
				}else{
					$backoperatingFN = 1;
				}

				if(isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])){
					$backSeat = $fareComponents[1]['segments'][0]['segment']['seatsAvailable'];
				}else{
					$backSeat = "9";
				}

				if(isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])){
						$backBookingCode = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
				}else{
					$backBookingCode = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
				}

				$backElapsedTime = $legDescs[$id2]['elapsedTime'];
				$backTravelTime = floor($backElapsedTime / 60)."H ".($backElapsedTime - ((floor($backElapsedTime / 60)) * 60))."Min";

				//transit Time 
				$transitDetails = array("go"=> array("transit1"=>"0"),
										"back"=> array("transit1"=>"0"));
				

				$segment = array("go" =>
								 array("0" =>
										array("marketingcareer"=> "$gomarkettingCarrier",
											  "marketingcareerName"=> "$gomarkettingCarrierName",
											  "marketingflight"=> "$gomarkettingFN",
												"operatingcareer"=> "$gooperatingCarrier",
												"operatingflight"=> "$gooperatingFN",
												"departure"=> "$goDepartureFrom",
												"departureAirport"=> "$godAirport",
												"departureLocation"=> "$godCity , $godCountry",                    
												"departureTime" => "$godpTimedate",
												"arrival"=> "$goArrivalTo",                   
												"arrivalTime" => "$goarrTimedate",
												"arrivalAirport"=> "$goaAirport",
												"arrivalLocation"=> "$goaCity , $goaCountry",
												"flightduration"=> "$goTravelTime",
												"bookingcode"=> "$goBookingCode",
												"seat"=> "$goSeat")																										

											),
								"back" => array("0" =>
											array("marketingcareer"=> "$backmarkettingCarrier",
											      "marketingcareerName"=> "$backmarkettingCarrierName",
													"marketingflight"=> "$backmarkettingFN",
													"operatingcareer"=> "$backoperatingCarrier",
													"operatingflight"=> "$backoperatingFN",
													"departure"=> "$backDepartureFrom",
													"departureAirport"=> "$backdAirport",
													"departureLocation"=> "$backdCity , $backdCountry",                    
													"departureTime" => "$backdpTimedate",
													"arrival"=> "$backArrivalTo",                   
													"arrivalTime" => "$backarrTimedate",
													"arrivalAirport"=> "$backaAirport",
													"arrivalLocation"=> "$backaCity , $backaCountry",
													"flightduration"=> "$backTravelTime",
													"bookingcode"=> "$backBookingCode",
													"seat"=> "$backSeat")													

											)
										);

				$basic = array("system" => "Sabre",
								"segment"=> "1",
								"triptype"=>$TripType,
								"career"=> "$vCarCode",
								"careerName" => "$CarrieerName",
								"lastTicketTime"=> "$timelimit",
								"BasePrice" => $baseFareAmount ,
								"Taxes" => $totalTaxAmount,
								"price" => "$AgentPrice",
								"clientPrice"=> "$totalFare",
								"comission"=> "$Commission",
								"pricebreakdown"=> $PriceBreakDown,
								"godeparture"=> "$From",                   
								"godepartureTime" => $godepartureTime,
								"godepartureDate" => $godpTime,
								"goarrival"=> "$To", 
								"goarrivalTime" => "$goArrivalTime",
								"goarrivalDate" => "$goarrTime",                
								"backdeparture"=> "$To",                   
								"backdepartureTime" => $backdepartureTime,
								"backdepartureDate" => $backdpTime,
								"backarrival"=> "$From", 
								"backarrivalTime" => "$backArrivalTime", 
								"backarrivalDate" => $backarrTime,  
								"goflightduration"=> "$goTravelTime",
								"backflightduration"=> "$backTravelTime",
								"transit"=> $transitDetails,								
								"bags" => "$Bags",
								"seat" => "$goSeat",
								"class" => "$CabinClass",
								"refundable"=> "$nonRef",
								"segments" => $segment
							);


			}else if($sgCount1 == 2 && $sgCount2 == 2){
				
				//Go 1
				$golf1 = $legDescs[$id1]['schedules'][0]['ref'];
				$golegrefs = $golf1- 1;

				$godepartureTime =  substr($scheduleDescs[$golegrefs]['departure']['time'],0,5);
				$godpTime = date("D d M Y", strtotime($dDate." ".$godepartureTime));
				$godpTimedate = $dDate."T".$godepartureTime.':00';

				$goArrivalTime = substr($scheduleDescs[$golegrefs]['arrival']['time'],0,5);
				$goarrivalDate = 0;
				if(isset($scheduleDescs[$golegrefs]['arrival']['dateAdjustment'])){
					$goarrivalDate += 1;
				}


				if($goarrivalDate == 1){
					$goaDate = date('Y-m-d', strtotime("+1 day", strtotime($dDate)));
				}else{
					$goaDate = $dDate;
				}

				$goarrTime = date("D d M Y", strtotime($goaDate." ".$goArrivalTime));
				$goarrTimedate = $goaDate."T".$goArrivalTime.':00';

				$goArrivalTo = $scheduleDescs[$golegrefs]['arrival']['airport'];
				$goDepartureFrom = $scheduleDescs[$golegrefs]['departure']['airport'];
				$gomarkettingCarrier = $scheduleDescs[$golegrefs]['carrier']['marketing'];

				$goCrsql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
				$goCrrow = mysqli_fetch_array($goCrsql,MYSQLI_ASSOC);

				if(!empty($goCrrow)){
					$gomarkettingCarrierName = $goCrrow['name'];				
				}

				// Departure Country
				$goDeptsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
				$goDeptrow = mysqli_fetch_array($goDeptsql,MYSQLI_ASSOC);

				if(!empty($goDeptrow)){
					$godAirport = $goDeptrow['name'];
					$godCity = $goDeptrow['cityName'];
					$godCountry = $goDeptrow['countryCode'];				
				}

				// Arrival Country
				$goArrsql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
				$goArrrow = mysqli_fetch_array($goArrsql,MYSQLI_ASSOC);

				if(!empty($goArrrow)){
					$goaAirport = $goArrrow['name'];
					$goaCity = $goArrrow['cityName'];
					$goaCountry = $goArrrow['countryCode'];				
				}


				$gomarkettingFN = $scheduleDescs[$golegrefs]['carrier']['marketingFlightNumber'];
				$gooperatingCarrier = $scheduleDescs[$golegrefs]['carrier']['operating'];
				if(isset($scheduleDescs[$golegrefs]['carrier']['operatingFlightNumber'])){
					$gooperatingFN = $scheduleDescs[$golegrefs]['carrier']['operatingFlightNumber'];
				}else{
					$gooperatingFN = $gomarkettingFN;
				}

				if(isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
					$goSeat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
				}else{
					$goSeat = "9";
				}

				if(isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])){
						$goBookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
				}else{
					$goBookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
				}

				$goElapsedTime = $scheduleDescs[$golegrefs]['elapsedTime'];
				$goTravelTime = floor($goElapsedTime / 60)."H ".($goElapsedTime - ((floor($goElapsedTime / 60)) * 60))."Min";
				

				
				//Go 2
				$golf2 = $legDescs[$id1]['schedules'][1]['ref'];
				$golegrefs2 = $golf2- 1;
				

				$goDepartureDate1 = 0;
				if(isset($legDescs[$id1]['schedules'][1]['departureDateAdjustment'])){
					$goDepartureDate1 += 1;
				}
				

				if($goDepartureDate1 == 1){
					$godepDate1 = date('Y-m-d', strtotime("+1 day", strtotime($dDate)));
				}else{
					$godepDate1 = $dDate;
				}


				$godepartureTime1 =  substr($scheduleDescs[$golegrefs2]['departure']['time'],0,5);
				$godpTime1 = date("D d M Y", strtotime($godepDate1." ".$godepartureTime1));
				$godpTimedate1 = $godepDate1."T".$godepartureTime1.':00';

				$goArrivalTime1 = substr($scheduleDescs[$golegrefs2]['arrival']['time'],0,5);
				
				$goarrivalDate1 = 0;
				if(isset($scheduleDescs[$golegrefs2]['arrival']['dateAdjustment'])){
					$goarrivalDate1 += 1;
				}


				if($goarrivalDate1 == 1){
					$goaDate1 = date('Y-m-d', strtotime("+1 day", strtotime($godepDate1)));
				}else{
					$goaDate1 = $godepDate1;
				}

				$goarrTime1 = date("D d M Y", strtotime($goaDate1." ".$goArrivalTime1));
				$goarrTimedate1 = $goaDate1."T".$goArrivalTime1.':00';

				$goArrivalTo1 = $scheduleDescs[$golegrefs2]['arrival']['airport'];
				$goDepartureFrom1 = $scheduleDescs[$golegrefs2]['departure']['airport'];
				$gomarkettingCarrier1 = $scheduleDescs[$golegrefs2]['carrier']['marketing'];

				$goCrsql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier1' ");
				$goCrrow1 = mysqli_fetch_array($goCrsql1,MYSQLI_ASSOC);

				if(!empty($goCrrow1)){
					$gomarkettingCarrierName1 = $goCrrow1['name'];				
				}

				// Departure Country
				$goDeptsql1 = mysqli_query($conn,"$Airportsql code='$goDepartureFrom1' ");
				$goDeptrow1 = mysqli_fetch_array($goDeptsql1,MYSQLI_ASSOC);

				if(!empty($goDeptrow1)){
					$godAirport1 = $goDeptrow1['name'];
					$godCity1 = $goDeptrow1['cityName'];
					$godCountry1 = $goDeptrow1['countryCode'];				
				}

				// Arrival Country
				$goArrsql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo1' ");
				$goArrrow1 = mysqli_fetch_array($goArrsql1,MYSQLI_ASSOC);

				if(!empty($goArrrow)){
					$goaAirport1 = $goArrrow1['name'];
					$goaCity1 = $goArrrow1['cityName'];
					$goaCountry1 = $goArrrow1['countryCode'];				
				}


				$gomarkettingFN1 = $scheduleDescs[$golegrefs2]['carrier']['marketingFlightNumber'];
				$gooperatingCarrier1 = $scheduleDescs[$golegrefs2]['carrier']['operating'];
				if(isset($scheduleDescs[$golegrefs2]['carrier']['operatingFlightNumber'])){
					$gooperatingFN1 = $scheduleDescs[$golegrefs2]['carrier']['operatingFlightNumber'];
				}else{
					$gooperatingFN1 = $gomarkettingFN1;
				}

				if(isset($fareComponents[0]['segments'][1]['segment']['seatsAvailable'])){
					$goSeat1 = $fareComponents[0]['segments'][1]['segment']['seatsAvailable'];
				}else{
					$goSeat1 = "9";
				}

				if(isset($fareComponents[0]['segments'][1]['segment']['bookingCode'])){
						$goBookingCode1 = $fareComponents[0]['segments'][1]['segment']['bookingCode'];
				}else{
					$goBookingCode1 = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
				}

				$goElapsedTime1 =  $scheduleDescs[$golegrefs2]['elapsedTime'];
				$goTravelTime1 = floor($goElapsedTime1 / 60)."H ".($goElapsedTime1 - ((floor($goElapsedTime1 / 60)) * 60))."Min";

				//Go Transit Time
				$goTransitTime = $goTotalElapesd - ($goElapsedTime + $goElapsedTime1);
				$goTransitDuration = floor($goTransitTime / 60)."H ".($goTransitTime - ((floor($goTransitTime / 60)) * 60))."Min";

				
				$goJourneyElapseTime = $goTotalElapesd;
				$goJourneyDuration = floor($goJourneyElapseTime / 60)."H ".($goJourneyElapseTime - ((floor($goJourneyElapseTime / 60)) * 60))."Min";
				

				
				//Return Back 1

				$backlf1 = $legDescs[$id2]['schedules'][0]['ref'];
				$backlegrefs = $backlf1 - 1;                           
				

				$backdepartureTime =  substr($scheduleDescs[$backlegrefs]['departure']['time'],0,5);                         
				$backdpTime = date("D d M Y", strtotime($rDate." ".$backdepartureTime));
				$backdpTimedate = $rDate."T".$backdepartureTime.':00';


		
				$backarrivalDate = 0;
				if(isset($scheduleDescs[$backlegrefs]['arrival']['dateAdjustment'])){
					$backarrivalDate += 1;
				}

				if($backarrivalDate == 1){
					$backaDate = date('Y-m-d', strtotime("+1 day", strtotime($rDate)));
				}else{
					$backaDate = $rDate;
				}

				$backarrivalTime =  substr($scheduleDescs[$backlegrefs]['arrival']['time'],0,5);
				$backarrTime = date("D d M Y", strtotime($backaDate." ".$backarrivalTime));
				$backarrTimedate = $backaDate."T".$backarrivalTime.':00';

				$backArrivalTo = $scheduleDescs[$backlegrefs]['arrival']['airport'];
				$backDepartureFrom = $scheduleDescs[$backlegrefs]['departure']['airport'];
				$backmarkettingCarrier = $scheduleDescs[$backlegrefs]['carrier']['marketing']; 

				$backCrsql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
				$backCrrow = mysqli_fetch_array($backCrsql,MYSQLI_ASSOC);

				if(!empty($backCrrow)){
					$backmarkettingCarrierName = $backCrrow['name'];				
				}

				// Departure Country
				$backDeptsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
				$backDeptrow = mysqli_fetch_array($backDeptsql,MYSQLI_ASSOC);

				if(!empty($backDeptrow)){
					$backdAirport = $backDeptrow['name'];
					$backdCity = $backDeptrow['cityName'];
					$backdCountry = $backDeptrow['countryCode'];				
				}

				// Arivalr Country
				$backArrsql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo' ");
				$backArrrow = mysqli_fetch_array($backArrsql,MYSQLI_ASSOC);

				if(!empty($backArrrow)){
					$backaAirport = $backArrrow['name'];
					$backaCity = $backArrrow['cityName'];
					$backaCountry = $backArrrow['countryCode'];				
				}


				$backmarkettingFN = $scheduleDescs[$backlegrefs]['carrier']['marketingFlightNumber'];
				$backoperatingCarrier = $scheduleDescs[$backlegrefs]['carrier']['operating'];
				$backoperatingFN = $scheduleDescs[$backlegrefs]['carrier']['operatingFlightNumber'];

				if(isset($scheduleDescs[$backlegrefs]['carrier']['operatingFlightNumber'])){
					$backoperatingFN = $scheduleDescs[$backlegrefs]['carrier']['operatingFlightNumber'];
				}else{
					$backoperatingFN = $backmarkettingFN;
				}

				if(isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])){
					$backSeat = $fareComponents[1]['segments'][0]['segment']['seatsAvailable'];
				}else{
					$backSeat = "9";
				}

				if(isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])){
						$backBookingCode = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
				}else{
					$backBookingCode = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
				}

				$backElapsedTime = $scheduleDescs[$backlegrefs]['elapsedTime'];
				$backTravelTime = floor($backElapsedTime / 60)."H ".($backElapsedTime - ((floor($backElapsedTime / 60)) * 60))."Min";
				
			
				//Return Back 2

				$backlf2 = $legDescs[$id2]['schedules'][1]['ref'];
				$backlegrefs2 = $backlf2 - 1;                              

				$backDepartureDate1 = 0;
				if(isset($legDescs[$id2]['schedules'][1]['departureDateAdjustment'])){
					$backDepartureDate1 += 1;
				}
				

				if($backDepartureDate1 == 1){
					$backdepDate1 = date('Y-m-d', strtotime("+1 day", strtotime($rDate)));
				}else{
					$backdepDate1 = $rDate;
				}

				

				$backdepartureTime1 =  substr($scheduleDescs[$backlegrefs2]['departure']['time'],0,5);                     
				$backdpTime1 = date("D d M Y", strtotime($backdepDate1." ".$backdepartureTime1));
				$backdpTimedate1 = $backdepDate1."T".$backdepartureTime1.':00';

				$backarrivalDate1 = 0;
				if(isset($scheduleDescs[$backlegrefs2]['arrival']['dateAdjustment'])){
					$backarrivalDate1 += 1;
				}

				if($backarrivalDate1 == 1){
					$backaDate1 = date('Y-m-d', strtotime("+1 day", strtotime($backdepDate1)));
				}else{
					$backaDate1 = $backdepDate1;
				}


				$backarrivalTime1 =  substr($scheduleDescs[$backlegrefs2]['arrival']['time'],0,5);
				$backarrTime1 = date("D d M Y", strtotime($backaDate1." ".$backarrivalTime1));
				$backarrTimedate1 = $backaDate1."T".$backarrivalTime1.':00';

				$backArrivalTo1 = $scheduleDescs[$backlegrefs2]['arrival']['airport'];
				$backDepartureFrom1 = $scheduleDescs[$backlegrefs2]['departure']['airport'];
				$backmarkettingCarrier1 = $scheduleDescs[$backlegrefs2]['carrier']['marketing']; 

				$backCrsql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier1' ");
				$backCrrow1 = mysqli_fetch_array($backCrsql1,MYSQLI_ASSOC);

				if(!empty($backCrrow1)){
					$backmarkettingCarrierName1 = $backCrrow1['name'];				
				}

				// Departure Country
				$backDeptsql1 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom1' ");
				$backDeptrow1 = mysqli_fetch_array($backDeptsql1,MYSQLI_ASSOC);

				if(!empty($backDeptrow1)){
					$backdAirport1 = $backDeptrow1['name'];
					$backdCity1 = $backDeptrow1['cityName'];
					$backdCountry1 = $backDeptrow1['countryCode'];				
				}

				// Arivalr Country
				$backArrsql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo1' ");
				$backArrrow1 = mysqli_fetch_array($backArrsql1,MYSQLI_ASSOC);

				if(!empty($backArrrow1)){
					$backaAirport1 = $backArrrow1['name'];
					$backaCity1 = $backArrrow1['cityName'];
					$backaCountry1 = $backArrrow1['countryCode'];
				
				}


				$backmarkettingFN1 = $scheduleDescs[$backlegrefs2]['carrier']['marketingFlightNumber'];
				$backoperatingCarrier1 = $scheduleDescs[$backlegrefs2]['carrier']['operating'];
				$backoperatingFN1 = $scheduleDescs[$backlegrefs2]['carrier']['operatingFlightNumber'];

				if(isset($scheduleDescs[$backlegrefs2]['carrier']['operatingFlightNumber'])){
					$backoperatingFN1 = $scheduleDescs[$backlegrefs2]['carrier']['operatingFlightNumber'];
				}else{
					$backoperatingFN1 = $backmarkettingFN1;
				}

				if(isset($fareComponents[1]['segments'][1]['segment']['seatsAvailable'])){
					$backSeat1 = $fareComponents[1]['segments'][1]['segment']['seatsAvailable'];
				}else{
					$backSeat1 = "9";
				}

				if(isset($fareComponents[1]['segments'][1]['segment']['bookingCode'])){
						$backBookingCode1 = $fareComponents[1]['segments'][1]['segment']['bookingCode'];
				}else{
					$backBookingCode1 = $fareComponents[1]['segments'][1]['segment']['bookingCode'];
				}



				$backElapsedTime1 = $scheduleDescs[$backlegrefs2]['elapsedTime'];
				$backTravelTime1 = floor($backElapsedTime1 / 60)."H ".($backElapsedTime1 - ((floor($backElapsedTime1 / 60)) * 60))."Min";

				//Back Transit Time//Go Trabsit Time
				$backTransitTime = $backTotalElapesd - ($backElapsedTime + $backElapsedTime1);
				$backTransitDuration = floor($backTransitTime / 60)."H ".($backTransitTime - ((floor($backTransitTime / 60)) * 60))."Min";

				
				$backJourneyElapseTime = $backTotalElapesd;
				$backJourneyDuration = floor($backJourneyElapseTime / 60)."H ".($backJourneyElapseTime - ((floor($backJourneyElapseTime / 60)) * 60))."Min";
				


				
				$transitDetails = array("go"=> array("transit1"=> $goTransitDuration),
										"back"=> array("transit1"=> $backTransitDuration));

								

				$segment = array("go" =>
								 array("0" =>
										array("marketingcareer"=> "$gomarkettingCarrier",
											  "marketingcareerName"=> "$gomarkettingCarrierName",
											  "marketingflight"=> "$gomarkettingFN",
												"operatingcareer"=> "$gooperatingCarrier",
												"operatingflight"=> "$gooperatingFN",
												"departure"=> "$goDepartureFrom",
												"departureAirport"=> "$godAirport",
												"departureLocation"=> "$godCity , $godCountry",                    
												"departureTime" => "$godpTimedate",
												"arrival"=> "$goArrivalTo",                   
												"arrivalTime" => "$goarrTimedate",
												"arrivalAirport"=> "$goaAirport",
												"arrivalLocation"=> "$goaCity , $goaCountry",
												"flightduration"=> "$goTravelTime",
												"bookingcode"=> "$goBookingCode",
												"seat"=> "$goSeat"),
										"1" =>
										array("marketingcareer"=> "$gomarkettingCarrier1",
											  "marketingcareerName"=> "$gomarkettingCarrierName1",
											  "marketingflight"=> "$gomarkettingFN1",
												"operatingcareer"=> "$gooperatingCarrier1",
												"operatingflight"=> "$gooperatingFN1",
												"departure"=> "$goDepartureFrom1",
												"departureAirport"=> "$godAirport1",
												"departureLocation"=> "$godCity , $godCountry1",                    
												"departureTime" => "$godpTimedate1",
												"arrival"=> "$goArrivalTo1",                   
												"arrivalTime" => "$goarrTimedate1",
												"arrivalAirport"=> "$goaAirport1",
												"arrivalLocation"=> "$goaCity1 , $goaCountry1",
												"flightduration"=> "$goTravelTime1",
												"bookingcode"=> "$goBookingCode1",
												"seat"=> "$goSeat1")																										

											),
								"back" => array("0" =>
													array("marketingcareer"=> "$backmarkettingCarrier",
														  "marketingcareerName"=> "$backmarkettingCarrierName",
															"marketingflight"=> "$backmarkettingFN",
															"operatingcareer"=> "$backoperatingCarrier",
															"operatingflight"=> "$backoperatingFN",
															"departure"=> "$backDepartureFrom",
															"departureAirport"=> "$backdAirport",
															"departureLocation"=> "$backdCity , $backdCountry",                    
															"departureTime" => "$backdpTimedate",
															"arrival"=> "$backArrivalTo",                   
															"arrivalTime" => "$backarrTimedate",
															"arrivalAirport"=> "$backaAirport",
															"arrivalLocation"=> "$backaCity , $backaCountry",
															"flightduration"=> "$backTravelTime",
															"bookingcode"=> "$backBookingCode",
															"seat"=> "$backSeat"),
												"1" =>
													array("marketingcareer"=> "$backmarkettingCarrier1",
														  "marketingcareerName"=> "$backmarkettingCarrierName1",
															"marketingflight"=> "$backmarkettingFN1",
															"operatingcareer"=> "$backoperatingCarrier1",
															"operatingflight"=> "$backoperatingFN1",
															"departure"=> "$backDepartureFrom1",
															"departureAirport"=> "$backdAirport1",
															"departureLocation"=> "$backdCity1 , $backdCountry1",                    
															"departureTime" => "$backdpTimedate1",
															"arrival"=> "$backArrivalTo1",                   
															"arrivalTime" => "$backarrTimedate1",
															"arrivalAirport"=> "$backaAirport1",
															"arrivalLocation"=> "$backaCity1 , $backaCountry1",
															"flightduration"=> "$backTravelTime1",
															"bookingcode"=> "$backBookingCode1",
															"seat"=> "$backSeat")													
													

											)
										);

				$basic = array("system" => "Sabre",
								"segment"=> "2",
								"triptype"=>$TripType,
								"career"=> "$vCarCode",
								"careerName" => "$CarrieerName",
								"lastTicketTime"=> "$timelimit",
								"BasePrice" => $baseFareAmount ,
								"Taxes" => $totalTaxAmount,
								"price" => "$AgentPrice",
								"clientPrice"=> "$totalFare",
								"comission"=> "$Commission",
								"pricebreakdown"=> $PriceBreakDown,
								"godeparture"=> "$From",                   
								"godepartureTime" => $godepartureTime,
								"godepartureDate" => $godpTime,
								"goarrival"=> "$To", 
								"goarrivalTime" => "$goArrivalTime1",
								"goarrivalDate" => "$goarrTime1",                
								"backdeparture"=> "$To",                   
								"backdepartureTime" => $backdepartureTime,
								"backdepartureDate" => $backdpTime,
								"backarrival"=> "$From", 
								"backarrivalTime" => "$backarrivalTime1", 
								"backarrivalDate" => $backarrTime1,  
								"goflightduration"=> "$goJourneyDuration",
								"backflightduration"=> "$backJourneyDuration",
								"transit"=> $transitDetails,								
								"bags" => "$Bags",
								"seat" => "$goSeat",
								"class" => "$CabinClass",
								"refundable"=> "$nonRef",
								"segments" => $segment
							);

			}else if($sgCount1 == 3 & $sgCount2 == 3){
				
				//Go 1
				$golf1 = $legDescs[$id1]['schedules'][0]['ref'];
				$golegrefs = $golf1- 1;

				$godepartureTime =  substr($scheduleDescs[$golegrefs]['departure']['time'],0,5);
				$godpTime = date("D d M Y", strtotime($dDate." ".$godepartureTime));
				$godpTimedate = $dDate."T".$godepartureTime.':00';

				$goArrivalTime = substr($scheduleDescs[$golegrefs]['arrival']['time'],0,5);
				$goarrivalDate = 0;
				if(isset($scheduleDescs[$golegrefs]['arrival']['dateAdjustment'])){
					$goarrivalDate += 1;
				}


				if($goarrivalDate == 1){
					$goaDate = date('Y-m-d', strtotime("+1 day", strtotime($dDate)));
					}else{
					$goaDate = date('Y-m-d', strtotime("+0 day", strtotime($dDate)));
				}

				$goarrTime = date("D d M Y", strtotime($goaDate." ".$goArrivalTime));
				$goarrTimedate = $goaDate."T".$goArrivalTime.':00';

				$goArrivalTo = $scheduleDescs[$golegrefs]['arrival']['airport'];
				$goDepartureFrom = $scheduleDescs[$golegrefs]['departure']['airport'];
				$gomarkettingCarrier = $scheduleDescs[$golegrefs]['carrier']['marketing'];

				$goCrsql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
				$goCrrow = mysqli_fetch_array($goCrsql,MYSQLI_ASSOC);

				if(!empty($goCrrow)){
					$gomarkettingCarrierName = $goCrrow['name'];				
				}

				// Departure Country
				$goDeptsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
				$goDeptrow = mysqli_fetch_array($goDeptsql,MYSQLI_ASSOC);

				if(!empty($goDeptrow)){
					$godAirport = $goDeptrow['name'];
					$godCity = $goDeptrow['cityName'];
					$godCountry = $goDeptrow['countryCode'];				
				}

				// Arrival Country
				$goArrsql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
				$goArrrow = mysqli_fetch_array($goArrsql,MYSQLI_ASSOC);

				if(!empty($goArrrow)){
					$goaAirport = $goArrrow['name'];
					$goaCity = $goArrrow['cityName'];
					$goaCountry = $goArrrow['countryCode'];				
				}


				$gomarkettingFN = $scheduleDescs[$golegrefs]['carrier']['marketingFlightNumber'];
				$gooperatingCarrier = $scheduleDescs[$golegrefs]['carrier']['operating'];
				if(isset($scheduleDescs[$golegrefs]['carrier']['operatingFlightNumber'])){
					$gooperatingFN = $scheduleDescs[$golegrefs]['carrier']['operatingFlightNumber'];
				}else{
					$gooperatingFN = 1;
				}

				if(isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
					$goSeat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
				}else if(!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
					$goSeat = "9";
				}

				if(isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])){
						$goBookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
				}else{
					$goBookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
				}

				$goElapsedTime = $scheduleDescs[$golegrefs]['elapsedTime'];
				$goTravelTime = floor($goElapsedTime / 60)."H ".($goElapsedTime - ((floor($goElapsedTime / 60)) * 60))."Min";
				

				
				//Go 2
				$golf2 = $legDescs[$id1]['schedules'][1]['ref'];
				$golegrefs2 = $golf2- 1;

				$godepartureTime1 =  substr($scheduleDescs[$golegrefs2]['departure']['time'],0,5);
				$godpTime1 = date("D d M Y", strtotime($dDate." ".$godepartureTime1));
				$godpTimedate1 = $dDate."T".$godepartureTime1.':00';

				$goArrivalTime1 = substr($scheduleDescs[$golegrefs2]['arrival']['time'],0,5);
				$goarrivalDate1 = 0;
				if(isset($scheduleDescs[$golegrefs2]['arrival']['dateAdjustment'])){
					$goarrivalDate1 += 1;
				}


				if($goarrivalDate1 == 1){
					$goaDate1 = date('Y-m-d', strtotime("+1 day", strtotime($dDate)));
					}else{
					$goaDate1 = date('Y-m-d', strtotime("+0 day", strtotime($dDate)));
				}

				$goarrTime1 = date("D d M Y", strtotime($goaDate1." ".$goArrivalTime1));
				$goarrTimedate1 = $goaDate."T".$goArrivalTime1.':00';

				$goArrivalTo1 = $scheduleDescs[$golegrefs2]['arrival']['airport'];
				$goDepartureFrom1 = $scheduleDescs[$golegrefs2]['departure']['airport'];
				$gomarkettingCarrier1 = $scheduleDescs[$golegrefs2]['carrier']['marketing'];

				$goCrsql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier1' ");
				$goCrrow1 = mysqli_fetch_array($goCrsql1,MYSQLI_ASSOC);

				if(!empty($goCrrow1)){
					$gomarkettingCarrierName1 = $goCrrow1['name'];				
				}

				// Departure Country
				$goDeptsql1 = mysqli_query($conn,"$Airportsql code='$goDepartureFrom1' ");
				$goDeptrow1 = mysqli_fetch_array($goDeptsql1,MYSQLI_ASSOC);

				if(!empty($goDeptrow1)){
					$godAirport1 = $goDeptrow1['name'];
					$godCity1 = $goDeptrow1['cityName'];
					$godCountry1 = $goDeptrow1['countryCode'];				
				}

				// Arrival Country
				$goArrsql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo1' ");
				$goArrrow1 = mysqli_fetch_array($goArrsql1,MYSQLI_ASSOC);

				if(!empty($goArrrow1)){
					$goaAirport1 = $goArrrow1['name'];
					$goaCity1 = $goArrrow1['cityName'];
					$goaCountry1 = $goArrrow1['countryCode'];				
				}


				$gomarkettingFN1 = $scheduleDescs[$golegrefs2]['carrier']['marketingFlightNumber'];
				$gooperatingCarrier1 = $scheduleDescs[$golegrefs2]['carrier']['operating'];
				if(isset($scheduleDescs[$golegrefs2]['carrier']['operatingFlightNumber'])){
					$gooperatingFN1 = $scheduleDescs[$golegrefs2]['carrier']['operatingFlightNumber'];
				}else{
					$gooperatingFN1 = 1;
				}

				if(isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])){
					$goSeat1 = $fareComponents[1]['segments'][0]['segment']['seatsAvailable'];
				}else{
					$goSeat1 = "9";
				}

				if(isset($fareComponents[0]['segments'][1]['segment']['bookingCode'])){
						$goBookingCode1 = $fareComponents[0]['segments'][1]['segment']['bookingCode'];
				}else{
					$goBookingCode1 = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
				}

				$goElapsedTime1 = $scheduleDescs[$golegrefs2]['elapsedTime'];
				$goTravelTime1 = floor($goElapsedTime1 / 60)."H ".($goElapsedTime1 - ((floor($goElapsedTime1 / 60)) * 60))."Min";
				

				
				//Go 3
				$golf3 = $legDescs[$id1]['schedules'][2]['ref'];
				$golegrefs3 = $golf3- 1;

				$godepartureTime2 =  substr($scheduleDescs[$golegrefs3]['departure']['time'],0,5);
				$godpTime2 = date("D d M Y", strtotime($dDate." ".$godepartureTime2));
				$godpTimedate2 = $dDate."T".$godepartureTime2.':00';

				$goArrivalTime2 = substr($scheduleDescs[$golegrefs2]['arrival']['time'],0,5);
				$goarrivalDate2 = 0;
				if(isset($scheduleDescs[$golegrefs2]['arrival']['dateAdjustment'])){
					$goarrivalDate2 += 1;
				}


				if($goarrivalDate2 == 1){
					$goaDate2 = date('Y-m-d', strtotime("+1 day", strtotime($dDate)));
					}else{
					$goaDate2 = date('Y-m-d', strtotime("+0 day", strtotime($dDate)));
				}

				$goarrTime2 = date("D d M Y", strtotime($goaDate2." ".$goArrivalTime2));
				$goarrTimedate2 = $goaDate2."T".$goArrivalTime2.':00';

				$goArrivalTo2 = $scheduleDescs[$golegrefs3]['arrival']['airport'];
				$goDepartureFrom2 = $scheduleDescs[$golegrefs3]['departure']['airport'];
				$gomarkettingCarrier2 = $scheduleDescs[$golegrefs3]['carrier']['marketing'];

				$goCrsql2 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier2' ");
				$goCrrow2 = mysqli_fetch_array($goCrsql2,MYSQLI_ASSOC);

				if(!empty($goCrrow2)){
					$gomarkettingCarrierName2 = $goCrrow2['name'];				
				}

				// Departure Country
				$goDeptsql2 = mysqli_query($conn,"$Airportsql code='$goDepartureFrom2' ");
				$goDeptrow2 = mysqli_fetch_array($goDeptsql2,MYSQLI_ASSOC);

				if(!empty($goDeptrow2)){
					$godAirport2 = $goDeptrow2['name'];
					$godCity2 = $goDeptrow2['cityName'];
					$godCountry2 = $goDeptrow2['countryCode'];				
				}

				// Arrival Country
				$goArrsql2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo2' ");
				$goArrrow2 = mysqli_fetch_array($goArrsql2,MYSQLI_ASSOC);

				if(!empty($goArrrow2)){
					$goaAirport2 = $goArrrow2['name'];
					$goaCity2 = $goArrrow2['cityName'];
					$goaCountry2 = $goArrrow2['countryCode'];				
				}


				$gomarkettingFN2 = $scheduleDescs[$golegrefs3]['carrier']['marketingFlightNumber'];
				$gooperatingCarrier2 = $scheduleDescs[$golegrefs3]['carrier']['operating'];
				if(isset($scheduleDescs[$golegrefs3]['carrier']['operatingFlightNumber'])){
					$gooperatingFN2 = $scheduleDescs[$golegrefs3]['carrier']['operatingFlightNumber'];
				}else{
					$gooperatingFN2 = 1;
				}

				if(isset($fareComponents[2]['segments'][0]['segment']['seatsAvailable'])){
					$goSeat2 = $fareComponents[2]['segments'][0]['segment']['seatsAvailable'];
				}else if(!isset($fareComponents[2]['segments'][0]['segment']['seatsAvailable'])){
					$goSeat2 = "9";
				}

				if(isset($fareComponents[0]['segments'][2]['segment']['bookingCode'])){
						$goBookingCode2 = $fareComponents[0]['segments'][2]['segment']['bookingCode'];
				}else{
					$goBookingCode2 = $fareComponents[0]['segments'][2]['segment']['bookingCode'];
				}

				//print_r($fareComponents);

				$goElapsedTime2 = $scheduleDescs[$golegrefs3]['elapsedTime'];
				$goTravelTime2 = floor($goElapsedTime1 / 60)."H ".($goElapsedTime1 - ((floor($goElapsedTime1 / 60)) * 60))."Min";

				// Go Transit1
				
				$goTransitTime = round(abs(strtotime($godpTimedate1) - strtotime($goarrTimedate)) / 60,2);
				$goTransitDuration = floor($goTransitTime / 60)."H ".($goTransitTime - ((floor($goTransitTime / 60)) * 60))."Min";

				// Go Transit 2
				$goTransitTime1 = round(abs(strtotime($godpTimedate2) - strtotime($goarrTimedate1)) / 60,2);
				$goTransitDuration1 = floor($goTransitTime1 / 60)."H ".($goTransitTime1 - ((floor($goTransitTime1 / 60)) * 60))."Min";


				$goJourneyElapseTime = $goElapsedTime + $goTransitTime +  $goTransitTime1 + $goElapsedTime1 + $goElapsedTime2;
				$goJourneyDuration = floor($goJourneyElapseTime / 60)."H ".($goJourneyElapseTime - ((floor($goJourneyElapseTime / 60)) * 60))."Min";
				


				//Back 1

				$backlf1 = $legDescs[$id2]['schedules'][0]['ref'];
				$backlegrefs = $backlf1 - 1;                           
				$backArrivalTime = substr($scheduleDescs[$backlegrefs]['arrival']['time'],0,5);

				$backarrivalDate = 0;
				if(isset($scheduleDescs[$backlegrefs]['arrival']['dateAdjustment'])){
					$backarrivalDate += 1;
				}

				if($backarrivalDate == 1){
					$backaDate = date('Y-m-d', strtotime("+1 day", strtotime($rDate)));
					}else{
					$backaDate = date('Y-m-d', strtotime("+0 day", strtotime($rDate)));
				}

				$backdepartureTime =  substr($scheduleDescs[$backlegrefs]['departure']['time'],0,5);                         
				$backdpTime = date("D d M Y", strtotime($rDate." ".$backdepartureTime));
				$backdpTimedate = $rDate."T".$backdepartureTime.':00';


				$backarrivalTime =  substr($scheduleDescs[$backlegrefs]['arrival']['time'],0,5);
				$backarrTime = date("D d M Y", strtotime($backaDate." ".$backarrivalTime));
				$backarrTimedate = $backaDate."T".$backarrivalTime.':00';

				$backArrivalTo = $scheduleDescs[$backlegrefs]['arrival']['airport'];
				$backDepartureFrom = $scheduleDescs[$backlegrefs]['departure']['airport'];
				$backmarkettingCarrier = $scheduleDescs[$backlegrefs]['carrier']['marketing']; 

				$backCrsql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
				$backCrrow = mysqli_fetch_array($backCrsql,MYSQLI_ASSOC);

				if(!empty($backCrrow)){
					$backmarkettingCarrierName = $backCrrow['name'];				
				}

				// Departure Country
				$backDeptsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
				$backDeptrow = mysqli_fetch_array($backDeptsql,MYSQLI_ASSOC);

				if(!empty($backDeptrow)){
					$backdAirport = $backDeptrow['name'];
					$backdCity = $backDeptrow['cityName'];
					$backdCountry = $backDeptrow['countryCode'];				
				}

				// Arivalr Country
				$backArrsql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo' ");
				$backArrrow = mysqli_fetch_array($backArrsql,MYSQLI_ASSOC);

				if(!empty($backArrrow)){
				$backaAirport = $backArrrow['name'];
				$backaCity = $backArrrow['cityName'];
				$backaCountry = $backArrrow['countryCode'];
				
				}


				$backmarkettingFN = $scheduleDescs[$backlegrefs]['carrier']['marketingFlightNumber'];
				$backoperatingCarrier = $scheduleDescs[$backlegrefs]['carrier']['operating'];
				$backoperatingFN = $scheduleDescs[$backlegrefs]['carrier']['operatingFlightNumber'];

				if(isset($scheduleDescs[$backlegrefs]['carrier']['operatingFlightNumber'])){
					$backoperatingFN = $scheduleDescs[$backlegrefs]['carrier']['operatingFlightNumber'];
				}else{
					$backoperatingFN = 1;
				}

				if(isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
					$backSeat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
				}else if(!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
					$backSeat = "9";
				}

				if(isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])){
						$backBookingCode = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
				}else{
					$backBookingCode = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
				}

				$backElapsedTime = $scheduleDescs[$backlegrefs]['elapsedTime'];
				$backTravelTime = floor($backElapsedTime / 60)."H ".($backElapsedTime - ((floor($backElapsedTime / 60)) * 60))."Min";
				

				//Return 2

				$backlf2 = $legDescs[$id2]['schedules'][1]['ref'];
				$backlegrefs2 = $backlf2 - 1;                           
				$backArrivalTime1 = substr($scheduleDescs[$backlegrefs2]['arrival']['time'],0,5);

				$backarrivalDate1 = 0;
				if(isset($scheduleDescs[$backlegrefs2]['arrival']['dateAdjustment'])){
					$backarrivalDate1 += 1;
				}

				if($backarrivalDate1 == 1){
					$backaDate1 = date('Y-m-d', strtotime("+1 day", strtotime($rDate)));
					}else{
					$backaDate1 = date('Y-m-d', strtotime("+0 day", strtotime($rDate)));
				}

				$backdepartureTime1 =  substr($scheduleDescs[$backlegrefs2]['departure']['time'],0,5);                         
				$backdpTime1 = date("D d M Y", strtotime($rDate." ".$backdepartureTime));
				$backdpTimedate1 = $rDate."T".$backdepartureTime.':00';


				$backarrivalTime1 =  substr($scheduleDescs[$backlegrefs2]['arrival']['time'],0,5);
				$backarrTime1 = date("D d M Y", strtotime($backaDate." ".$backarrivalTime));
				$backarrTimedate1 = $backaDate."T".$backarrivalTime.':00';

				$backArrivalTo1 = $scheduleDescs[$backlegrefs2]['arrival']['airport'];
				$backDepartureFrom1 = $scheduleDescs[$backlegrefs2]['departure']['airport'];
				$backmarkettingCarrier1 = $scheduleDescs[$backlegrefs2]['carrier']['marketing']; 

				$backCrsql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier1' ");
				$backCrrow1 = mysqli_fetch_array($backCrsql1,MYSQLI_ASSOC);

				if(!empty($backCrrow1)){
					$backmarkettingCarrierName1 = $backCrrow1['name'];				
				}

				// Departure Country
				$backDeptsql1 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom1' ");
				$backDeptrow1 = mysqli_fetch_array($backDeptsql1,MYSQLI_ASSOC);

				if(!empty($backDeptrow1)){
					$backdAirport1 = $backDeptrow1['name'];
					$backdCity1 = $backDeptrow1['cityName'];
					$backdCountry1 = $backDeptrow1['countryCode'];				
				}

				// Arivalr Country
				$backArrsql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo1' ");
				$backArrrow1 = mysqli_fetch_array($backArrsql1,MYSQLI_ASSOC);

				if(!empty($backArrrow1)){
				$backaAirport1 = $backArrrow1['name'];
				$backaCity1 = $backArrrow1['cityName'];
				$backaCountry1 = $backArrrow1['countryCode'];
				
				}


				$backmarkettingFN1 = $scheduleDescs[$backlegrefs2]['carrier']['marketingFlightNumber'];
				$backoperatingCarrier1 = $scheduleDescs[$backlegrefs2]['carrier']['operating'];
				$backoperatingFN1 = $scheduleDescs[$backlegrefs2]['carrier']['operatingFlightNumber'];

				if(isset($scheduleDescs[$backlegrefs2]['carrier']['operatingFlightNumber'])){
					$backoperatingFN1 = $scheduleDescs[$backlegrefs2]['carrier']['operatingFlightNumber'];
				}else{
					$backoperatingFN1 = 1;
				}

				if(isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])){
					$backSeat1 = $fareComponents[1]['segments'][0]['segment']['seatsAvailable'];
				}else if(!isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])){
					$backSeat1 = "9";
				}

				if(isset($fareComponents[1]['segments'][1]['segment']['bookingCode'])){
						$backBookingCode1 = $fareComponents[1]['segments'][1]['segment']['bookingCode'];
				}else{
					$backBookingCode1 = $fareComponents[1]['segments'][1]['segment']['bookingCode'];
				}

				$backElapsedTime1 = $scheduleDescs[$backlegrefs2]['elapsedTime'];
				$backTravelTime1 = floor($backElapsedTime1 / 60)."H ".($backElapsedTime1 - ((floor($backElapsedTime1 / 60)) * 60))."Min";


				//Return 3

				
				$backlf3 = $legDescs[$id2]['schedules'][2]['ref']; 
				$backlegrefs3 = $backlf3 - 1;                           
				$backArrivalTime2 = substr($scheduleDescs[$backlegrefs3]['arrival']['time'],0,5);

				$backarrivalDate2 = 0;
				if(isset($scheduleDescs[$backlegrefs3]['arrival']['dateAdjustment'])){
					$backarrivalDate2 += 1;
				}

				if($backarrivalDate2 == 1){
					$backaDate2 = date('Y-m-d', strtotime("+1 day", strtotime($rDate)));
					}else{
					$backaDate2 = date('Y-m-d', strtotime("+0 day", strtotime($rDate)));
				}

				$backdepartureTime2 =  substr($scheduleDescs[$backlegrefs3]['departure']['time'],0,5);                         
				$backdpTime2 = date("D d M Y", strtotime($rDate." ".$backdepartureTime2));
				$backdpTimedate2 = $rDate."T".$backdepartureTime2.':00';


				$backarrivalTime2 =  substr($scheduleDescs[$backlegrefs3]['arrival']['time'],0,5);
				$backarrTime2 = date("D d M Y", strtotime($backaDate." ".$backarrivalTime2));
				$backarrTimedate2 = $backaDate."T".$backarrivalTime2.':00';

				$backArrivalTo2 = $scheduleDescs[$backlegrefs3]['arrival']['airport'];
				$backDepartureFrom2 = $scheduleDescs[$backlegrefs3]['departure']['airport'];
				$backmarkettingCarrier2 = $scheduleDescs[$backlegrefs3]['carrier']['marketing']; 

				$backCrsql2 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier2' ");
				$backCrrow2 = mysqli_fetch_array($backCrsql2,MYSQLI_ASSOC);

				if(!empty($backCrrow2)){
					$backmarkettingCarrierName2 = $backCrrow2['name'];				
				}

				// Departure Country
				$backDeptsql2 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom2' ");
				$backDeptrow2 = mysqli_fetch_array($backDeptsql2,MYSQLI_ASSOC);

				if(!empty($backDeptrow2)){
					$backdAirport2 = $backDeptrow2['name'];
					$backdCity2 = $backDeptrow2['cityName'];
					$backdCountry2 = $backDeptrow2['countryCode'];				
				}

				// Arivalr Country
				$backArrsql2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo2' ");
				$backArrrow2 = mysqli_fetch_array($backArrsql2,MYSQLI_ASSOC);

				if(!empty($backArrrow2)){
					$backaAirport2 = $backArrrow2['name'];
					$backaCity2 = $backArrrow2['cityName'];
					$backaCountry2 = $backArrrow2['countryCode'];
					
				}


				$backmarkettingFN2 = $scheduleDescs[$backlegrefs3]['carrier']['marketingFlightNumber'];
				$backoperatingCarrier2 = $scheduleDescs[$backlegrefs3]['carrier']['operating'];
				$backoperatingFN2 = $scheduleDescs[$backlegrefs3]['carrier']['operatingFlightNumber'];

				if(isset($scheduleDescs[$backlegrefs3]['carrier']['operatingFlightNumber'])){
					$backoperatingFN2 = $scheduleDescs[$backlegrefs3]['carrier']['operatingFlightNumber'];
				}else{
					$backoperatingFN2 = 1;
				}

				if(isset($fareComponents[1]['segments'][2]['segment']['seatsAvailable'])){
					$backSeat2 = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
				}else if(!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
					$backSeat2 = "9";
				}

				if(isset($fareComponents[1]['segments'][2]['segment']['bookingCode'])){
						$backBookingCode2 = $fareComponents[1]['segments'][2]['segment']['bookingCode'];
				}else{
					$backBookingCode2 = $fareComponents[1]['segments'][2]['segment']['bookingCode'];
				}

				$backElapsedTime2 = $scheduleDescs[$backlegrefs3]['elapsedTime'];
				$backTravelTime2 = floor($backElapsedTime2 / 60)."H ".($backElapsedTime2 - ((floor($backElapsedTime2 / 60)) * 60))."Min";


				// Go Transit1
				
				$goTransitTime = round(abs(strtotime($godpTimedate1) - strtotime($goarrTimedate)) / 60,2);
				$goTransitDuration = floor($goTransitTime / 60)."H ".($goTransitTime - ((floor($goTransitTime / 60)) * 60))."Min";

				// Go Transit 2
				$goTransitTime1 = round(abs(strtotime($godpTimedate2) - strtotime($goarrTimedate1)) / 60,2);
				$goTransitDuration1 = floor($goTransitTime1 / 60)."H ".($goTransitTime1 - ((floor($goTransitTime1 / 60)) * 60))."Min";
				
			
				// Back Transit 1
				$backTransitTime = round(abs(strtotime($backdpTimedate1) - strtotime($backarrTimedate)) / 60,2);
				$backTransitDuration = floor($backTransitTime / 60)."H ".($backTransitTime - ((floor($backTransitTime / 60)) * 60))."Min";

				// Back Transit 2
				$backTransitTime1 = round(abs(strtotime($backdpTimedate2) - strtotime($backarrTimedate1)) / 60,2);
				$backTransitDuration1 = floor($backTransitTime1 / 60)."H ".($backTransitTime1 - ((floor($backTransitTime1 / 60)) * 60))."Min";


				$backJourneyElapseTime = $backElapsedTime + $backElapsedTime1 + $backElapsedTime2 + $backTransitTime + $backTransitTime1;
				$backJourneyDuration = floor($backJourneyElapseTime / 60)."H ".($backJourneyElapseTime - ((floor($backJourneyElapseTime / 60)) * 60))."Min";

				
				
				$transitDetails = array("go"=> array("transit1"=> $goTransitDuration,
													 "transit2"=> $goTransitDuration1),
										"back"=> array("transit1"=> $backTransitDuration,
													   "transit2"=> $backTransitDuration1)
										);

			
							

				$segment = array("go" =>
								 array("0" =>
										array("marketingcareer"=> "$gomarkettingCarrier",
										      "marketingcareerName"=> "$gomarkettingCarrierName",
											  "marketingflight"=> "$gomarkettingFN",
												"operatingcareer"=> "$gooperatingCarrier",
												"operatingflight"=> "$gooperatingFN",
												"departure"=> "$goDepartureFrom",
												"departureAirport"=> "$godAirport",
												"departureLocation"=> "$godCity , $godCountry",                    
												"departureTime" => "$godpTimedate",
												"arrival"=> "$goArrivalTo",                   
												"arrivalTime" => "$goarrTimedate",
												"arrivalAirport"=> "$goaAirport",
												"arrivalLocation"=> "$goaCity , $goaCountry",
												"flightduration"=> "$goTravelTime",
												"bookingcode"=> "$goBookingCode",
												"seat"=> "$goSeat"),
										"1" =>
										array("marketingcareer"=> "$gomarkettingCarrier1",
										"marketingcareerName"=> "$gomarkettingCarrierName1",
											  "marketingflight"=> "$gomarkettingFN1",
												"operatingcareer"=> "$gooperatingCarrier1",
												"operatingflight"=> "$gooperatingFN1",
												"departure"=> "$goDepartureFrom1",
												"departureAirport"=> "$godAirport1",
												"departureLocation"=> "$godCity , $godCountry1",                    
												"departureTime" => "$godpTimedate1",
												"arrival"=> "$goArrivalTo1",                   
												"arrivalTime" => "$goarrTimedate1",
												"arrivalAirport"=> "$goaAirport1",
												"arrivalLocation"=> "$goaCity1 , $goaCountry1",
												"flightduration"=> "$goTravelTime1",
												"bookingcode"=> "$goBookingCode1",
												"seat"=> "$goSeat1"),
										"2" =>
										array("marketingcareer"=> "$gomarkettingCarrier2",
											  "marketingcareerName"=> "$gomarkettingCarrierName2",
											  "marketingflight"=> "$gomarkettingFN2",
												"operatingcareer"=> "$gooperatingCarrier2",
												"operatingflight"=> "$gooperatingFN2",
												"departure"=> "$goDepartureFrom2",
												"departureAirport"=> "$godAirport2",
												"departureLocation"=> "$godCity2 , $godCountry2",                    
												"departureTime" => "$godpTimedate2",
												"arrival"=> "$goArrivalTo2",                   
												"arrivalTime" => "$goarrTimedate2",
												"arrivalAirport"=> "$goaAirport2",
												"arrivalLocation"=> "$goaCity2 , $goaCountry2",
												"flightduration"=> "$goTravelTime2",
												"bookingcode"=> "$goBookingCode2",
												"seat"=> "$goSeat2")																										

											),
								"back" => array("0" =>
													array("marketingcareer"=> "$backmarkettingCarrier",
													"marketingcareerName"=> "$backmarkettingCarrierName",
															"marketingflight"=> "$backmarkettingFN",
															"operatingcareer"=> "$backoperatingCarrier",
															"operatingflight"=> "$backoperatingFN",
															"departure"=> "$backDepartureFrom",
															"departureAirport"=> "$backdAirport",
															"departureLocation"=> "$backdCity , $backdCountry",                    
															"departureTime" => "$backdpTimedate",
															"arrival"=> "$backArrivalTo",                   
															"arrivalTime" => "$backarrTimedate",
															"arrivalAirport"=> "$backaAirport",
															"arrivalLocation"=> "$backaCity , $backaCountry",
															"flightduration"=> "$backTravelTime",
															"bookingcode"=> "$backBookingCode",
															"seat"=> "$backSeat"),
												"1" =>
													array("marketingcareer"=> "$backmarkettingCarrier1",
													"marketingcareerName"=> "$backmarkettingCarrierName1",
															"marketingflight"=> "$backmarkettingFN1",
															"operatingcareer"=> "$backoperatingCarrier1",
															"operatingflight"=> "$backoperatingFN1",
															"departure"=> "$backDepartureFrom1",
															"departureAirport"=> "$backdAirport1",
															"departureLocation"=> "$backdCity1 , $backdCountry1",                    
															"departureTime" => "$backdpTimedate1",
															"arrival"=> "$backArrivalTo1",                   
															"arrivalTime" => "$backarrTimedate1",
															"arrivalAirport"=> "$backaAirport1",
															"arrivalLocation"=> "$backaCity1 , $backaCountry1",
															"flightduration"=> "$backTravelTime1",
															"bookingcode"=> "$backBookingCode1",
															"seat"=> "$backSeat1"),
												"2" =>
													array("marketingcareer"=> "$backmarkettingCarrier2",
													"marketingcareerName"=> "$backmarkettingCarrierName2",
															"marketingflight"=> "$backmarkettingFN2",
															"operatingcareer"=> "$backoperatingCarrier2",
															"operatingflight"=> "$backoperatingFN2",
															"departure"=> "$backDepartureFrom2",
															"departureAirport"=> "$backdAirport2",
															"departureLocation"=> "$backdCity2 , $backdCountry2",                    
															"departureTime" => "$backdpTimedate2",
															"arrival"=> "$backArrivalTo2",                   
															"arrivalTime" => "$backarrTimedate2",
															"arrivalAirport"=> "$backaAirport2",
															"arrivalLocation"=> "$backaCity2 , $backaCountry2",
															"flightduration"=> "$backTravelTime2",
															"bookingcode"=> "$backBookingCode2",
															"seat"=> "$backSeat2")
													
													

											)
										);

				$basic = array("system" => "Sabre",
								"segment"=> "3",
								"triptype"=>$TripType,
								"career"=> "$vCarCode",
								"careerName" => "$CarrieerName",
								"lastTicketTime"=> "$timelimit",
								"BasePrice" => $baseFareAmount ,
								"Taxes" => $totalTaxAmount,
								"price" => "$AgentPrice",
								"clientPrice"=> "$totalFare",
								"comission"=> "$Commission",
								"pricebreakdown"=> $PriceBreakDown,
								"godeparture"=> "$From",                   
								"godepartureTime" => $godepartureTime,
								"godepartureDate" => $godpTime,
								"goarrival"=> "$To", 
								"goarrivalTime" => "$goArrivalTime2",
								"goarrivalDate" => "$goarrTime2",                
								"backdeparture"=> "$To",                   
								"backdepartureTime" => $backdepartureTime,
								"backdepartureDate" => $backdpTime,
								"backarrival"=> "$From", 
								"backarrivalTime" => "$backArrivalTime2", 
								"backarrivalDate" => $backarrTime2,  
								"goflightduration"=> "$goJourneyDuration",
								"backflightduration"=> "$backJourneyDuration",
								"transit"=> $transitDetails,								
								"bags" => "$Bags",
								"seat" => "$goSeat",
								"class" => "$CabinClass",
								"refundable"=> "$nonRef",
								"segments" => $segment
							);

				

			}else if($sgCount1 == 1 && $sgCount2 == 2){

				//Go 1
				$golf1 = $legDescs[$id1]['schedules'][0]['ref'];
				$golegrefs = $golf1- 1;

				$godepartureTime =  substr($scheduleDescs[$golegrefs]['departure']['time'],0,5);
				$godpTime = date("D d M Y", strtotime($dDate." ".$godepartureTime));
				$godpTimedate = $dDate."T".$godepartureTime.':00';

				$goArrivalTime = substr($scheduleDescs[$golegrefs]['arrival']['time'],0,5);
				$goarrivalDate = 0;
				if(isset($scheduleDescs[$golegrefs]['arrival']['dateAdjustment'])){
					$goarrivalDate += 1;
				}


				if($goarrivalDate == 1){
					$goaDate = date('Y-m-d', strtotime("+1 day", strtotime($dDate)));
					}else{
					$goaDate = date('Y-m-d', strtotime("+0 day", strtotime($dDate)));
				}

				$goarrTime = date("D d M Y", strtotime($goaDate." ".$goArrivalTime));
				$goarrTimedate = $goaDate."T".$goArrivalTime.':00';

				$goArrivalTo = $scheduleDescs[$golegrefs]['arrival']['airport'];
				$goDepartureFrom = $scheduleDescs[$golegrefs]['departure']['airport'];
				$gomarkettingCarrier = $scheduleDescs[$golegrefs]['carrier']['marketing'];

				$goCrsql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
				$goCrrow = mysqli_fetch_array($goCrsql,MYSQLI_ASSOC);

				if(!empty($goCrrow)){
					$gomarkettingCarrierName = $goCrrow['name'];				
				}

				// Departure Country
				$goDeptsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
				$goDeptrow = mysqli_fetch_array($goDeptsql,MYSQLI_ASSOC);

				if(!empty($goDeptrow)){
					$godAirport = $goDeptrow['name'];
					$godCity = $goDeptrow['cityName'];
					$godCountry = $goDeptrow['countryCode'];				
				}

				// Arrival Country
				$goArrsql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
				$goArrrow = mysqli_fetch_array($goArrsql,MYSQLI_ASSOC);

				if(!empty($goArrrow)){
					$goaAirport = $goArrrow['name'];
					$goaCity = $goArrrow['cityName'];
					$goaCountry = $goArrrow['countryCode'];				
				}


				$gomarkettingFN = $scheduleDescs[$golegrefs]['carrier']['marketingFlightNumber'];
				$gooperatingCarrier = $scheduleDescs[$golegrefs]['carrier']['operating'];
				if(isset($scheduleDescs[$golegrefs]['carrier']['operatingFlightNumber'])){
					$gooperatingFN = $scheduleDescs[$golegrefs]['carrier']['operatingFlightNumber'];
				}else{
					$gooperatingFN = 1;
				}

				if(isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
					$goSeat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
				}else if(!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
					$goSeat = "9";
				}

				if(isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])){
						$goBookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
				}else{
					$goBookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
				}

				$goElapsedTime = $legDescs[$id1]['elapsedTime'];
				$goTravelTime = floor($goElapsedTime / 60)."H ".($goElapsedTime - ((floor($goElapsedTime / 60)) * 60))."Min";
				

				
				//Return 1

				$backlf1 = $legDescs[$id2]['schedules'][0]['ref'];
				$backlegrefs = $backlf1 - 1;                           
				$backArrivalTime = substr($scheduleDescs[$backlegrefs]['arrival']['time'],0,5);

				$backarrivalDate = 0;
				if(isset($scheduleDescs[$backlegrefs]['arrival']['dateAdjustment'])){
					$backarrivalDate += 1;
				}

				if($backarrivalDate == 1){
					$backaDate = date('Y-m-d', strtotime("+1 day", strtotime($rDate)));
					}else{
					$backaDate = date('Y-m-d', strtotime("+0 day", strtotime($rDate)));
				}

				$backdepartureTime =  substr($scheduleDescs[$backlegrefs]['departure']['time'],0,5);                         
				$backdpTime = date("D d M Y", strtotime($rDate." ".$backdepartureTime));
				$backdpTimedate = $rDate."T".$backdepartureTime.':00';


				$backarrivalTime =  substr($scheduleDescs[$backlegrefs]['arrival']['time'],0,5);
				$backarrTime = date("D d M Y", strtotime($backaDate." ".$backarrivalTime));
				$backarrTimedate = $backaDate."T".$backarrivalTime.':00';

				$backArrivalTo = $scheduleDescs[$backlegrefs]['arrival']['airport'];
				$backDepartureFrom = $scheduleDescs[$backlegrefs]['departure']['airport'];
				$backmarkettingCarrier = $scheduleDescs[$backlegrefs]['carrier']['marketing']; 

				$backCrsql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
				$backCrrow = mysqli_fetch_array($backCrsql,MYSQLI_ASSOC);

				if(!empty($backCrrow)){
					$backmarkettingCarrierName = $backCrrow['name'];				
				}

				// Departure Country
				$backDeptsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
				$backDeptrow = mysqli_fetch_array($backDeptsql,MYSQLI_ASSOC);

				if(!empty($backDeptrow)){
					$backdAirport = $backDeptrow['name'];
					$backdCity = $backDeptrow['cityName'];
					$backdCountry = $backDeptrow['countryCode'];				
				}

				// Arivalr Country
				$backArrsql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo' ");
				$backArrrow = mysqli_fetch_array($backArrsql,MYSQLI_ASSOC);

				if(!empty($backArrrow)){
				$backaAirport = $backArrrow['name'];
				$backaCity = $backArrrow['cityName'];
				$backaCountry = $backArrrow['countryCode'];
				
				}


				$backmarkettingFN = $scheduleDescs[$backlegrefs]['carrier']['marketingFlightNumber'];
				$backoperatingCarrier = $scheduleDescs[$backlegrefs]['carrier']['operating'];
				$backoperatingFN = $scheduleDescs[$backlegrefs]['carrier']['operatingFlightNumber'];

				if(isset($scheduleDescs[$backlegrefs]['carrier']['operatingFlightNumber'])){
					$backoperatingFN = $scheduleDescs[$backlegrefs]['carrier']['operatingFlightNumber'];
				}else{
					$backoperatingFN = 1;
				}

				if(isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])){
					$backSeat = $fareComponents[1]['segments'][0]['segment']['seatsAvailable'];
				}else if(!isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])){
					$backSeat = "9";
				}

				if(isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])){
						$backBookingCode = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
				}else{
					$backBookingCode = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
				}

				$backElapsedTime = $scheduleDescs[$backlegrefs]['elapsedTime'];
				$backTravelTime = floor($backElapsedTime / 60)."H ".($backElapsedTime - ((floor($backElapsedTime / 60)) * 60))."Min";
				

				//Return 2

				$backlf2 = $legDescs[$id2]['schedules'][1]['ref'];
				$backlegrefs2 = $backlf2 - 1;                           
				$backArrivalTime1 = substr($scheduleDescs[$backlegrefs2]['arrival']['time'],0,5);

				$backarrivalDate1 = 0;
				if(isset($scheduleDescs[$backlegrefs2]['arrival']['dateAdjustment'])){
					$backarrivalDate1 += 1;
				}

				if($backarrivalDate1 == 1){
					$backaDate1 = date('Y-m-d', strtotime("+1 day", strtotime($rDate)));
					}else{
					$backaDate1 = date('Y-m-d', strtotime("+0 day", strtotime($rDate)));
				}

				$backdepartureTime1 =  substr($scheduleDescs[$backlegrefs2]['departure']['time'],0,5);                         
				$backdpTime1 = date("D d M Y", strtotime($rDate." ".$backdepartureTime1));
				$backdpTimedate1 = $rDate."T".$backdepartureTime1.':00';


				$backarrivalTime1 =  substr($scheduleDescs[$backlegrefs2]['arrival']['time'],0,5);
				$backarrTime1 = date("D d M Y", strtotime($backaDate." ".$backarrivalTime1));
				$backarrTimedate1 = $backaDate."T".$backarrivalTime1.':00';

				$backArrivalTo1 = $scheduleDescs[$backlegrefs2]['arrival']['airport'];
				$backDepartureFrom1 = $scheduleDescs[$backlegrefs2]['departure']['airport'];
				$backmarkettingCarrier1 = $scheduleDescs[$backlegrefs2]['carrier']['marketing']; 

				$backCrsql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier1' ");
				$backCrrow1 = mysqli_fetch_array($backCrsql1,MYSQLI_ASSOC);

				if(!empty($backCrrow1)){
					$backmarkettingCarrierName1 = $backCrrow1['name'];				
				}

				// Departure Country
				$backDeptsql1 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom1' ");
				$backDeptrow1 = mysqli_fetch_array($backDeptsql1,MYSQLI_ASSOC);

				if(!empty($backDeptrow1)){
					$backdAirport1 = $backDeptrow1['name'];
					$backdCity1 = $backDeptrow1['cityName'];
					$backdCountry1 = $backDeptrow1['countryCode'];				
				}

				// Arivalr Country
				$backArrsql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo1' ");
				$backArrrow1 = mysqli_fetch_array($backArrsql1,MYSQLI_ASSOC);

				if(!empty($backArrrow1)){
					$backaAirport1 = $backArrrow1['name'];
					$backaCity1 = $backArrrow1['cityName'];
					$backaCountry1 = $backArrrow1['countryCode'];
				
				}


				$backmarkettingFN1 = $scheduleDescs[$backlegrefs2]['carrier']['marketingFlightNumber'];
				$backoperatingCarrier1 = $scheduleDescs[$backlegrefs2]['carrier']['operating'];
				$backoperatingFN1 = $scheduleDescs[$backlegrefs2]['carrier']['operatingFlightNumber'];

				if(isset($scheduleDescs[$backlegrefs2]['carrier']['operatingFlightNumber'])){
					$backoperatingFN1 = $scheduleDescs[$backlegrefs2]['carrier']['operatingFlightNumber'];
				}else{
					$backoperatingFN1 = 1;
				}

				if(isset($fareComponents[1]['segments'][1]['segment']['seatsAvailable'])){
					$backSeat1 = $fareComponents[1]['segments'][1]['segment']['seatsAvailable'];
				}else if(!isset($fareComponents[1]['segments'][1]['segment']['seatsAvailable'])){
					$backSeat1 = "9";
				}

				if(isset($fareComponents[1]['segments'][1]['segment']['bookingCode'])){
						$backBookingCode1 = $fareComponents[1]['segments'][1]['segment']['bookingCode'];
				}else{
					$backBookingCode1 = $fareComponents[1]['segments'][1]['segment']['bookingCode'];
				}


							
				// Back Transit 1
				$backTransitTime = round(abs(strtotime($backdpTimedate1) - strtotime($backarrTimedate)) / 60,2);
				$backTransitDuration = floor($backTransitTime / 60)."H ".($backTransitTime - ((floor($backTransitTime / 60)) * 60))."Min";

								
				$transitDetails = array("go"=> array("transit1"=> "0"),
										"back"=> array("transit1"=> $backTransitDuration));


				

				$segment = array("go" =>
								 array("0" =>
										array("marketingcareer"=> "$gomarkettingCarrier",
										"marketingcareerName"=> "$gomarkettingCarrierName",
											  "marketingflight"=> "$gomarkettingFN",
												"operatingcareer"=> "$gooperatingCarrier",
												"operatingflight"=> "$gooperatingFN",
												"departure"=> "$goDepartureFrom",
												"departureAirport"=> "$godAirport",
												"departureLocation"=> "$godCity , $godCountry",                    
												"departureTime" => "$godpTimedate",
												"arrival"=> "$goArrivalTo",                   
												"arrivalTime" => "$goarrTimedate",
												"arrivalAirport"=> "$goaAirport",
												"arrivalLocation"=> "$goaCity , $goaCountry",
												"flightduration"=> "$goTravelTime",
												"bookingcode"=> "$goBookingCode",
												"seat"=> "$goSeat")
																																	

											),
								"back" => array("0" =>
													array("marketingcareer"=> "$backmarkettingCarrier",
													"marketingcareerName"=> "$backmarkettingCarrierName",
															"marketingflight"=> "$backmarkettingFN",
															"operatingcareer"=> "$backoperatingCarrier",
															"operatingflight"=> "$backoperatingFN",
															"departure"=> "$backDepartureFrom",
															"departureAirport"=> "$backdAirport",
															"departureLocation"=> "$backdCity , $backdCountry",                    
															"departureTime" => "$backdpTimedate",
															"arrival"=> "$backArrivalTo",                   
															"arrivalTime" => "$backarrTimedate",
															"arrivalAirport"=> "$backaAirport",
															"arrivalLocation"=> "$backaCity , $backaCountry",
															"flightduration"=> "$backTravelTime",
															"bookingcode"=> "$backBookingCode",
															"seat"=> "$backSeat"),
												"1" =>
													array("marketingcareer"=> "$backmarkettingCarrier1",
													"marketingcareerName"=> "$backmarkettingCarrierName1",
															"marketingflight"=> "$backmarkettingFN1",
															"operatingcareer"=> "$backoperatingCarrier1",
															"operatingflight"=> "$backoperatingFN1",
															"departure"=> "$backDepartureFrom1",
															"departureAirport"=> "$backdAirport1",
															"departureLocation"=> "$backdCity1 , $backdCountry1",                    
															"departureTime" => "$backdpTimedate1",
															"arrival"=> "$backArrivalTo1",                   
															"arrivalTime" => "$backarrTimedate1",
															"arrivalAirport"=> "$backaAirport1",
															"arrivalLocation"=> "$backaCity1 , $backaCountry1",
															"flightduration"=> "$backTravelTime1",
															"bookingcode"=> "$backBookingCode1",
															"seat"=> "$backSeat")
													
													

											)
										);

				$basic = array("system" => "Sabre",
								"segment"=> "12",
								"triptype"=>$TripType,
								"career"=> "$vCarCode",
								"careerName" => "$CarrieerName",
								"lastTicketTime"=> "$timelimit",
								"BasePrice" => $baseFareAmount ,
								"Taxes" => $totalTaxAmount,
								"price" => "$AgentPrice",
								"clientPrice"=> "$totalFare",
								"comission"=> "$Commission",
								"pricebreakdown"=> $PriceBreakDown,
								"godeparture"=> "$From",                   
								"godepartureTime" => $godepartureTime,
								"godepartureDate" => $godpTime,
								"goarrival"=> "$To", 
								"goarrivalTime" => "$goArrivalTime",
								"goarrivalDate" => "$goarrTime",                
								"backdeparture"=> "$To",                   
								"backdepartureTime" => $backdepartureTime1,
								"backdepartureDate" => $backdpTime1,
								"backarrival"=> "$From", 
								"backarrivalTime" => "$backArrivalTime1", 
								"backarrivalDate" => $backarrTime1,  
								"goflightduration"=> "$goTravelTime",
								"backflightduration"=> "$backTravelTime",
								"transit"=> $transitDetails,
								"bags" => "$Bags",
								"seat" => "$goSeat",
								"class" => "$CabinClass",
								"refundable"=> "$nonRef",
								"segments" => $segment
							);
				
				

			}else if($sgCount1 == 2 && $sgCount2 == 1){
				//Go 1
				$golf1 = $legDescs[$id1]['schedules'][0]['ref'];
				$golegrefs = $golf1- 1;

				$godepartureTime =  substr($scheduleDescs[$golegrefs]['departure']['time'],0,5);
				$godpTime = date("D d M Y", strtotime($dDate." ".$godepartureTime));
				$godpTimedate = $dDate."T".$godepartureTime.':00';

				$goArrivalTime = substr($scheduleDescs[$golegrefs]['arrival']['time'],0,5);
				$goarrivalDate = 0;
				if(isset($scheduleDescs[$golegrefs]['arrival']['dateAdjustment'])){
					$goarrivalDate += 1;
				}


				if($goarrivalDate == 1){
					$goaDate = date('Y-m-d', strtotime("+1 day", strtotime($dDate)));
					}else{
					$goaDate = date('Y-m-d', strtotime("+0 day", strtotime($dDate)));
				}

				$goarrTime = date("D d M Y", strtotime($goaDate." ".$goArrivalTime));
				$goarrTimedate = $goaDate."T".$goArrivalTime.':00';

				$goArrivalTo = $scheduleDescs[$golegrefs]['arrival']['airport'];
				$goDepartureFrom = $scheduleDescs[$golegrefs]['departure']['airport'];
				$gomarkettingCarrier = $scheduleDescs[$golegrefs]['carrier']['marketing'];

				$goCrsql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
				$goCrrow = mysqli_fetch_array($goCrsql,MYSQLI_ASSOC);

				if(!empty($goCrrow)){
					$gomarkettingCarrierName = $goCrrow['name'];				
				}

				// Departure Country
				$goDeptsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
				$goDeptrow = mysqli_fetch_array($goDeptsql,MYSQLI_ASSOC);

				if(!empty($goDeptrow)){
					$godAirport = $goDeptrow['name'];
					$godCity = $goDeptrow['cityName'];
					$godCountry = $goDeptrow['countryCode'];				
				}

				// Arrival Country
				$goArrsql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
				$goArrrow = mysqli_fetch_array($goArrsql,MYSQLI_ASSOC);

				if(!empty($goArrrow)){
					$goaAirport = $goArrrow['name'];
					$goaCity = $goArrrow['cityName'];
					$goaCountry = $goArrrow['countryCode'];				
				}


				$gomarkettingFN = $scheduleDescs[$golegrefs]['carrier']['marketingFlightNumber'];
				$gooperatingCarrier = $scheduleDescs[$golegrefs]['carrier']['operating'];
				if(isset($scheduleDescs[$golegrefs]['carrier']['operatingFlightNumber'])){
					$gooperatingFN = $scheduleDescs[$golegrefs]['carrier']['operatingFlightNumber'];
				}else{
					$gooperatingFN = 1;
				}

				if(isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
					$goSeat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
				}else if(!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
					$goSeat = "9";
				}

				if(isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])){
						$goBookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
				}else{
					$goBookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
				}

				$goElapsedTime = $scheduleDescs[$golegrefs]['elapsedTime'];
				$goTravelTime = floor($goElapsedTime / 60)."H ".($goElapsedTime - ((floor($goElapsedTime / 60)) * 60))."Min";
				

				
				//Go 2
				$golf2 = $legDescs[$id1]['schedules'][1]['ref'];
				$golegrefs2 = $golf2- 1;

				$godepartureTime1 =  substr($scheduleDescs[$golegrefs2]['departure']['time'],0,5);
				$godpTime1 = date("D d M Y", strtotime($dDate." ".$godepartureTime));
				$godpTimedate1 = $dDate."T".$godepartureTime1.':00';

				$goArrivalTime1 = substr($scheduleDescs[$golegrefs2]['arrival']['time'],0,5);
				$goarrivalDate1 = 0;
				if(isset($scheduleDescs[$golegrefs2]['arrival']['dateAdjustment'])){
					$goarrivalDate1 += 1;
				}


				if($goarrivalDate1 == 1){
					$goaDate1 = date('Y-m-d', strtotime("+1 day", strtotime($dDate)));
					}else{
					$goaDate1 = date('Y-m-d', strtotime("+0 day", strtotime($dDate)));
				}

				$goarrTime1 = date("D d M Y", strtotime($goaDate1." ".$goArrivalTime1));
				$goarrTimedate1 = $goaDate."T".$goArrivalTime1.':00';

				$goArrivalTo1 = $scheduleDescs[$golegrefs2]['arrival']['airport'];
				$goDepartureFrom1 = $scheduleDescs[$golegrefs2]['departure']['airport'];
				$gomarkettingCarrier1 = $scheduleDescs[$golegrefs2]['carrier']['marketing'];

				$goCrsql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier1' ");
				$goCrrow1 = mysqli_fetch_array($goCrsql1,MYSQLI_ASSOC);

				if(!empty($goCrrow1)){
					$gomarkettingCarrierName1 = $goCrrow1['name']; 				
				}

				// Departure Country
				$goDeptsql1 = mysqli_query($conn,"$Airportsql code='$goDepartureFrom1' ");
				$goDeptrow1 = mysqli_fetch_array($goDeptsql1,MYSQLI_ASSOC);

				if(!empty($goDeptrow1)){
					$godAirport1 = $goDeptrow1['name'];
					$godCity1 = $goDeptrow1['cityName'];
					$godCountry1 = $goDeptrow1['countryCode'];				
				}

				// Arrival Country
				$goArrsql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo1' ");
				$goArrrow1 = mysqli_fetch_array($goArrsql1,MYSQLI_ASSOC);

				if(!empty($goArrrow)){
					$goaAirport1 = $goArrrow1['name'];
					$goaCity1 = $goArrrow1['cityName'];
					$goaCountry1 = $goArrrow1['countryCode'];				
				}


				$gomarkettingFN1 = $scheduleDescs[$golegrefs2]['carrier']['marketingFlightNumber'];
				$gooperatingCarrier1 = $scheduleDescs[$golegrefs2]['carrier']['operating'];
				if(isset($scheduleDescs[$golegrefs2]['carrier']['operatingFlightNumber'])){
					$gooperatingFN1 = $scheduleDescs[$golegrefs2]['carrier']['operatingFlightNumber'];
				}else{
					$gooperatingFN1 = 1;
				}

				if(isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])){
					$goSeat1 = $fareComponents[1]['segments'][0]['segment']['seatsAvailable'];
				}else if(!isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])){
					$goSeat1 = "9";
				}

				if(isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])){
						$goBookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
				}else{
					$goBookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
				}

				$goElapsedTime1 = $scheduleDescs[$golegrefs2]['elapsedTime'];
				$goTravelTime1 = floor($goElapsedTime1 / 60)."H ".($goElapsedTime1 - ((floor($goElapsedTime1 / 60)) * 60))."Min";



				//Return 1

				$backlf1 = $legDescs[$id2]['schedules'][0]['ref'];
				$backlegrefs = $backlf1 - 1;                           
				$backArrivalTime = substr($scheduleDescs[$backlegrefs]['arrival']['time'],0,5);

				$backarrivalDate = 0;
				if(isset($scheduleDescs[$backlegrefs]['arrival']['dateAdjustment'])){
					$backarrivalDate += 1;
				}

				if($backarrivalDate == 1){
					$backaDate = date('Y-m-d', strtotime("+1 day", strtotime($rDate)));
					}else{
					$backaDate = date('Y-m-d', strtotime("+0 day", strtotime($rDate)));
				}

				$backdepartureTime =  substr($scheduleDescs[$backlegrefs]['departure']['time'],0,5);                         
				$backdpTime = date("D d M Y", strtotime($rDate." ".$backdepartureTime));
				$backdpTimedate = $rDate."T".$backdepartureTime.':00';


				$backarrivalTime =  substr($scheduleDescs[$backlegrefs]['arrival']['time'],0,5);
				$backarrTime = date("D d M Y", strtotime($backaDate." ".$backarrivalTime));
				$backarrTimedate = $backaDate."T".$backarrivalTime.':00';

				$backArrivalTo = $scheduleDescs[$backlegrefs]['arrival']['airport'];
				$backDepartureFrom = $scheduleDescs[$backlegrefs]['departure']['airport'];
				$backmarkettingCarrier = $scheduleDescs[$backlegrefs]['carrier']['marketing']; 

				$backCrsql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
				$backCrrow = mysqli_fetch_array($backCrsql,MYSQLI_ASSOC);

				if(!empty($backCrrow)){
					$backmarkettingCarrierName = $backCrrow['name'];				
				}

				// Departure Country
				$backDeptsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
				$backDeptrow = mysqli_fetch_array($backDeptsql,MYSQLI_ASSOC);

				if(!empty($backDeptrow)){
					$backdAirport = $backDeptrow['name'];
					$backdCity = $backDeptrow['cityName'];
					$backdCountry = $backDeptrow['countryCode'];				
				}

				// Arivalr Country
				$backArrsql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo' ");
				$backArrrow = mysqli_fetch_array($backArrsql,MYSQLI_ASSOC);

				if(!empty($backArrrow)){
				$backaAirport = $backArrrow['name'];
				$backaCity = $backArrrow['cityName'];
				$backaCountry = $backArrrow['countryCode'];
				
				}


				$backmarkettingFN = $scheduleDescs[$backlegrefs]['carrier']['marketingFlightNumber'];
				$backoperatingCarrier = $scheduleDescs[$backlegrefs]['carrier']['operating'];
				$backoperatingFN = $scheduleDescs[$backlegrefs]['carrier']['operatingFlightNumber'];

				if(isset($scheduleDescs[$backlegrefs]['carrier']['operatingFlightNumber'])){
					$backoperatingFN = $scheduleDescs[$backlegrefs]['carrier']['operatingFlightNumber'];
				}else{
					$backoperatingFN = 1;
				}

				if(isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
					$backSeat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
				}else if(!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
					$backSeat = "9";
				}

				if(isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])){
						$backBookingCode = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
				}else{
					$backBookingCode = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
				}

				$backElapsedTime = $legDescs[$id2]['elapsedTime'];
				$backTravelTime = floor($backElapsedTime / 60)."H ".($backElapsedTime - ((floor($backElapsedTime / 60)) * 60))."Min";

				// Go Transit1
				
				$goTransitTime = round(abs(strtotime($godpTimedate1) - strtotime($goarrTimedate)) / 60,2);
				$goTransitDuration = floor($goTransitTime / 60)."H ".($goTransitTime - ((floor($goTransitTime / 60)) * 60))."Min";
				
				
				
				$transitDetails = array("go"=> array("transit1"=> $goTransitDuration),
										"back"=> array("transit1"=> "0")
										);
				

				

				$segment = array("go" =>
								 array("0" =>
										array("marketingcareer"=> "$gomarkettingCarrier",
										"marketingcareerName"=> "$gomarkettingCarrierName",
											  "marketingflight"=> "$gomarkettingFN",
												"operatingcareer"=> "$gooperatingCarrier",
												"operatingflight"=> "$gooperatingFN",
												"departure"=> "$goDepartureFrom",
												"departureAirport"=> "$godAirport",
												"departureLocation"=> "$godCity , $godCountry",                    
												"departureTime" => "$godpTimedate",
												"arrival"=> "$goArrivalTo",                   
												"arrivalTime" => "$goarrTimedate",
												"arrivalAirport"=> "$goaAirport",
												"arrivalLocation"=> "$goaCity , $goaCountry",
												"flightduration"=> "$goTravelTime",
												"bookingcode"=> "$goBookingCode",
												"seat"=> "$goSeat"),
										"1" =>
										array("marketingcareer"=> "$gomarkettingCarrier1",
										"marketingcareerName"=> "$gomarkettingCarrierName1",
											  "marketingflight"=> "$gomarkettingFN1",
												"operatingcareer"=> "$gooperatingCarrier1",
												"operatingflight"=> "$gooperatingFN1",
												"departure"=> "$goDepartureFrom1",
												"departureAirport"=> "$godAirport1",
												"departureLocation"=> "$godCity , $godCountry1",                    
												"departureTime" => "$godpTimedate1",
												"arrival"=> "$goArrivalTo1",                   
												"arrivalTime" => "$goarrTimedate1",
												"arrivalAirport"=> "$goaAirport1",
												"arrivalLocation"=> "$goaCity1 , $goaCountry1",
												"flightduration"=> "$goTravelTime1",
												"bookingcode"=> "$goBookingCode1",
												"seat"=> "$goSeat1")																										

											),
								"back" => array("0" =>
													array("marketingcareer"=> "$backmarkettingCarrier",
													"marketingcareerName"=> "$backmarkettingCarrierName",
															"marketingflight"=> "$backmarkettingFN",
															"operatingcareer"=> "$backoperatingCarrier",
															"operatingflight"=> "$backoperatingFN",
															"departure"=> "$backDepartureFrom",
															"departureAirport"=> "$backdAirport",
															"departureLocation"=> "$backdCity , $backdCountry",                    
															"departureTime" => "$backdpTimedate",
															"arrival"=> "$backArrivalTo",                   
															"arrivalTime" => "$backarrTimedate",
															"arrivalAirport"=> "$backaAirport",
															"arrivalLocation"=> "$backaCity , $backaCountry",
															"flightduration"=> "$backTravelTime",
															"bookingcode"=> "$backBookingCode",
															"seat"=> "$backSeat")
													
													

											)
										);

				$basic = array("system" => "Sabre",
								"segment"=> "21",
								"triptype"=>$TripType,
								"career"=> "$vCarCode",
								"careerName" => "$CarrieerName",
								"lastTicketTime"=> "$timelimit",
								"BasePrice" => $baseFareAmount ,
								"Taxes" => $totalTaxAmount,
								"price" => "$AgentPrice",
								"clientPrice"=> "$totalFare",
								"comission"=> "$Commission",
								"pricebreakdown"=> $PriceBreakDown,
								"godeparture"=> "$From",                   
								"godepartureTime" => $godepartureTime,
								"godepartureDate" => $godpTime,
								"goarrival"=> "$To", 
								"goarrivalTime" => "$goArrivalTime1",
								"goarrivalDate" => "$goarrTime1",                
								"backdeparture"=> "$To",                   
								"backdepartureTime" => $backdepartureTime,
								"backdepartureDate" => $backdpTime,
								"backarrival"=> "$From", 
								"backarrivalTime" => "$backArrivalTime", 
								"backarrivalDate" => $backarrTime,  
								"goflightduration"=> "$goTravelTime",
								"backflightduration"=> "$backTravelTime",
								"transit"=> $transitDetails,
								"bags" => "$Bags",
								"seat" => "$goSeat",
								"class" => "$CabinClass",
								"refundable"=> "$nonRef",
								"segments" => $segment
							);

				
			}

			if(isset($basic)){
				array_push($All,$basic);
			} 
		

        }
			


		}

	} /// Sabre End
	
	
	
	if($Galileo == 1){	// Galileo Start

	//Galileo Api
	$Passenger = implode(" ",$Gallpax);
	
	//$TARGETBRANCH = 'P7182044'; //Cert
	//$CREDENTIALS = 'Universal API/uAPI5270664478-0c51bde6:2Td*m/F3M5'; //cert
	$TARGETBRANCH = 'P4218912';
	$CREDENTIALS = 'Universal API/uAPI4444837655-83fe5101:K/s3-5Sy4c'; 
	
	$message = <<<EOM
	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
	<soapenv:Header/>
	<soapenv:Body>
		<LowFareSearchReq xmlns="http://www.travelport.com/schema/air_v42_0" TraceId="FFI-KayesFahim" TargetBranch="$TARGETBRANCH" ReturnUpsellFare="true">
				<BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v42_0" OriginApplication="uAPI" />
				<SearchAirLeg>
					<SearchOrigin>
						<CityOrAirport xmlns="http://www.travelport.com/schema/common_v42_0" Code="$From" PreferCity="true" />
					</SearchOrigin>
					<SearchDestination>
						<CityOrAirport xmlns="http://www.travelport.com/schema/common_v42_0" Code="$To" PreferCity="true" />
					</SearchDestination>
					<SearchDepTime PreferredTime="$dDate" />
				</SearchAirLeg>
				<SearchAirLeg>
					<SearchOrigin>
						<CityOrAirport xmlns="http://www.travelport.com/schema/common_v42_0" Code="$To" PreferCity="true" />
					</SearchOrigin>
					<SearchDestination>
						<CityOrAirport xmlns="http://www.travelport.com/schema/common_v42_0" Code="$From" PreferCity="true" />
					</SearchDestination>
					<SearchDepTime PreferredTime="$rDate" />
				</SearchAirLeg>
				<AirSearchModifiers>
					<PreferredProviders>
					<Provider xmlns="http://www.travelport.com/schema/common_v42_0" Code="1G" />
					</PreferredProviders>
				</AirSearchModifiers>
					$Passenger
				<AirPricingModifiers>
					<AccountCodes>
					<AccountCode xmlns="http://www.travelport.com/schema/common_v42_0" Code="-" />
					</AccountCodes>
				</AirPricingModifiers>
			</LowFareSearchReq>
	</soapenv:Body>
	</soapenv:Envelope>
	EOM;

	//print_r($message);



	$auth = base64_encode("$CREDENTIALS"); 
	$soap_do = curl_init("https://apac.universal-api.travelport.com/B2BGateway/connect/uAPI/AirService");
	$header = array(
	"Content-Type: text/xml;charset=UTF-8", 
	"Accept: gzip,deflate", 
	"Cache-Control: no-cache", 
	"Pragma: no-cache", 
	"SOAPAction: \"\"",
	"Authorization: Basic $auth", 
	"Content-length: ".strlen($message),
	); 


	curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false); 
	curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false); 
	curl_setopt($soap_do, CURLOPT_POST, true ); 
	curl_setopt($soap_do, CURLOPT_POSTFIELDS, $message); 
	curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header); 
	curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
	$return = curl_exec($soap_do);
	curl_close($soap_do);

	//print_r($return);

	//$return = file_get_contents("res.xml") ;
	$response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $return);
	$xml = new SimpleXMLElement($response);
	if(isset($xml->xpath('//airLowFareSearchRsp')[0])){
		$body = $xml->xpath('//airLowFareSearchRsp')[0];
		
	$result = json_decode(json_encode((array)$body), TRUE); 

	
	//print_r($result);

	$TraceId = $result['@attributes']['TraceId'];
	$airFlightDetailsList = $result['airFlightDetailsList']['airFlightDetails'];  //print_r($airFlightDetailsList);
	$airAirSegmentList =  $result['airAirSegmentList']['airAirSegment']; //print_r($airFlightDetailsList);
	$airFareInfoList = $result['airFareInfoList']['airFareInfo']; //print_r($airFareInfoList);
	$airAirPricePointList = $result['airAirPricePointList']['airAirPricePoint']; // print_r($airFareInfoList);

	//print_r($airAirPricePointList);
	//print(count($airAirPricePointList));

	$flightList= array();
	$airAirSegment = array();
	$airFareInfo = array();
	$airList = array();

	foreach($airFlightDetailsList as $airFlightDetails){
		$key = $airFlightDetails['@attributes']['Key'];
		$TravelTime = $airFlightDetails['@attributes']['TravelTime'];
		$Equipment = $airFlightDetails['@attributes']['Equipment'];
		$flightList[$key] = array('key'=> "$key",
								'TravelTime' => $TravelTime,
							'Equipment' => $Equipment);
	}

	//print_r($flightList);

	foreach($airFareInfoList as $airFareInfos){
		$key = $airFareInfos['@attributes']['Key'];
		$FareBasis =  $airFareInfos['@attributes']['FareBasis'];

		if(isset($airFareInfos['airBaggageAllowance']['airNumberOfPieces'])){
			$Baggage = $airFareInfos['airBaggageAllowance']['airNumberOfPieces'];
		}else{
			$Value = $airFareInfos['airBaggageAllowance']['airMaxWeight']['@attributes']['Value'];
			$Unit = $airFareInfos['airBaggageAllowance']['airMaxWeight']['@attributes']['Unit'];
			$Baggage = "$Value $Unit";
		}
		
		$airFareInfo[$key] = array('key'=> $key,
								   'Bags' => $Baggage,
								   'FareBasisCode' => $FareBasis);
	}


	foreach($airAirSegmentList as $airSegment){
		
		$key = $airSegment['@attributes']['Key'];		
		$Carrier = $airSegment['@attributes']['Carrier'];
		$Origin = $airSegment['@attributes']['Origin'];
		$Destination = $airSegment['@attributes']['Destination'];
		$DepartureTime = $airSegment['@attributes']['DepartureTime'];
	    $ArrivalTime = $airSegment['@attributes']['ArrivalTime'];
		$FlightNumber = $airSegment['@attributes']['FlightNumber'];
		$FlightTime = $airSegment['@attributes']['FlightTime'];
		$AvailabilitySource = $airSegment['@attributes']['AvailabilitySource'];
		$Distance = $airSegment['@attributes']['Distance'];
		$Equipment = $airSegment['@attributes']['Equipment'];
		$ParticipantLevel = $airSegment['@attributes']['ParticipantLevel'];
		$PolledAvailabilityOption = $airSegment['@attributes']['PolledAvailabilityOption'];
		$Group = $airSegment['@attributes']['Group'];
		$ChangeOfPlane = $airSegment['@attributes']['ChangeOfPlane'];
		$AvailabilityDisplayType = $airSegment['@attributes']['AvailabilityDisplayType'];
		

		if(isset($airSegment['airFlightDetailsRef']['@attributes']['Key'])){
			$airFlightDetailsRef = $airSegment['airFlightDetailsRef']['@attributes']['Key'];
			$TravelTime = $flightList[$airFlightDetailsRef]['TravelTime'];	
		}else{		
			$TravelTime = 0;
		}
		
		$airAirSegment[$key] = array(
							'key'=> "$key",
							'Carrier' => $Carrier,
							'Origin'=> "$Origin",
							'Destination' => $Destination,
							'DepartureTime'=> "$DepartureTime",
							'ArrivalTime' => $ArrivalTime,
							'FlightNumber'=> $FlightNumber,
							'FlightTime'=> $FlightTime,
							'TravelTime'=> $TravelTime,
							'AvailabilitySource'=> $AvailabilitySource,
							'Distance' => $Distance,
							'Equipment'=> $Equipment,
							'ParticipantLevel' => $ParticipantLevel,
							'PolledAvailabilityOption'=> $PolledAvailabilityOption,
							'ChangeOfPlane' => $ChangeOfPlane,
							'Group' => $Group,
							'AvailabilityDisplayType' => $AvailabilityDisplayType
							
						);
	}



	//print_r($airAirSegment);
	
		foreach($airAirPricePointList as $airAirPricePoint){
								
			if($adult  > 0 && $child == 0){	
				$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'];
			}else{
				$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'][0];		
			}
			
			    $airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption'];			
				$key = $airAirPricePoint['@attributes']['Key'];
				$TotalPrice = $airAirPricePoint['@attributes']['TotalPrice'];
				$validatingCarrierCode = $airPricePointOptions['@attributes']['PlatingCarrier'];
				$Exact = (int) filter_var($TotalPrice, FILTER_SANITIZE_NUMBER_INT);
				$BasePrice = (int) filter_var($airAirPricePoint['@attributes']['EquivalentBasePrice'], FILTER_SANITIZE_NUMBER_INT);
				$Taxes = (int) filter_var($airAirPricePoint['@attributes']['Taxes'], FILTER_SANITIZE_NUMBER_INT);
				if(isset($airPricePointOptions['@attributes']['Refundable'])){
					$Refundable = "Refundable";
				}else{
					$Refundable = "Nonrefundable";

				}
				

				$sql = mysqli_query($conn,"SELECT nameBangla, name FROM airlines WHERE code='$validatingCarrierCode'");
				$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

				if(!empty($row)){
					$CarrieerName = $row['name'];		
				}

				if(isset($airPricePoint[0]['airOption'][0]) == TRUE){
					if(isset($airPricePoint[0]['airOption'][0]) == TRUE){
											
						if(isset($airPricePoint[0]['airOption'][0]['airBookingInfo']['@attributes']['FareInfoRef']) == True
							&& isset($airPricePoint[1]['airOption'][0]['airBookingInfo']['@attributes']['FareInfoRef']) == True){
							

							//go Leg 1						
							$goFareInfoRef = $airPricePoint[0]['airOption'][0]['airBookingInfo']['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][0]['airBookingInfo']['@attributes']['SegmentRef'];
							$goBags = $airFareInfo[$goFareInfoRef]['Bags'];						
							$goTravelTime = $airAirSegment[$goSegmentRef]['TravelTime'];												
							$goTravelTimeHm = floor($goTravelTime / 60)."H ".($goTravelTime - ((floor($goTravelTime / 60)) * 60))."Min";


							$goArrivalTo = $airAirSegment[$goSegmentRef]['Destination'];
							$goDepartureFrom = $airAirSegment[$goSegmentRef]['Origin'];

							$goArrivalTime = $airAirSegment[$goSegmentRef]['ArrivalTime'];
							$goDepartureTime = $airAirSegment[$goSegmentRef]['DepartureTime'];

							$godpTime = date("D d M Y", strtotime($goDepartureTime));
							$goarrTime = date("D d M Y", strtotime($goArrivalTime));
							
							$gomarkettingCarrier = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
							$gomkrow = mysqli_fetch_array($gomksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$gomarkettingCarrierName = $gomkrow['name'];		
							}

							// Departure Country
							$godsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
							$godrow = mysqli_fetch_array($godsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$godAirport = $godrow['name'];
								$godCity = $godrow['cityName'];
								$godCountry = $godrow['countryCode'];		
							}

							// Departure Country
							$goasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$goarow = mysqli_fetch_array($goasql,MYSQLI_ASSOC);

							if(!empty($goarow)){
								$goaAirport = $goarow['name'];
								$goaCity = $goarow['cityName'];
								$goaCountry = $goarow['countryCode'];
							}
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][0]['airBookingInfo']['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][0]['airBookingInfo']['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][0]['airBookingInfo']['@attributes']['CabinClass'];

							
							//Back Leg 1
							
							$backFareInfoRef = $airPricePoint[1]['airOption'][0]['airBookingInfo']['@attributes']['FareInfoRef'];
							$backSegmentRef = $airPricePoint[1]['airOption'][0]['airBookingInfo']['@attributes']['SegmentRef'];
							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];						
							$backTravelTime = $airAirSegment[$backSegmentRef]['TravelTime'];												
							$backTravelTimeHm = floor($backTravelTime / 60)."H ".($backTravelTime - ((floor($backTravelTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backdpTime = date("D d M Y", strtotime($backDepartureTime));
							$backarrTime = date("D d M Y", strtotime($backArrivalTime));
							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backmksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
							$backmkrow = mysqli_fetch_array($backmksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$backmarkettingCarrierName = $backmkrow['name'];		
							}

							// Departure Country
							$backdsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backdrow = mysqli_fetch_array($backdsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$backdAirport = $backdrow['name'];
								$backdCity = $backdrow['cityName'];
								$backdCountry = $backdrow['countryCode'];		
							}

							// Departure Country
							$backasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$backarow = mysqli_fetch_array($backasql,MYSQLI_ASSOC);

							if(!empty($backarow)){
								$backaAirport = $backarow['name'];
								$backaCity = $backarow['cityName'];
								$backaCountry = $backarow['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][0]['airBookingInfo']['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][0]['airBookingInfo']['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][0]['airBookingInfo']['@attributes']['CabinClass'];

						$segment = array("go" =>
									array("0" =>
											array("marketingcareer"=> "$gomarkettingCarrier",
												"marketingflight"=> "$gomarkettingFN",
													"operatingcareer"=> "$gomarkettingCarrier",
													"operatingflight"=> "$gomarkettingFN",
													"departure"=> "$goDepartureFrom",
													"departureAirport"=> "$godAirport",
													"departureLocation"=> "$godCity , $godCountry",                    
													"departureTime" => "$goDepartureTime",
													"arrival"=> "$goArrivalTo",                   
													"arrivalTime" => "$goArrivalTime",
													"arrivalAirport"=> "$goaAirport",
													"arrivalLocation"=> "$goaCity , $goaCountry",
													"flightduration"=> "$goTravelTime",
													"bookingcode"=> "$goBookingCode",
													"Baggage" => "$goBags",
													"seat"=> "$goSeat",
													"segmentDetails"=> $airAirSegment[$goSegmentRef])																										

												),
									"back" => array("0" =>
														array("marketingcareer"=> "$backmarkettingCarrier",
																"marketingflight"=> "$backmarkettingFN",
																"operatingcareer"=> "$backmarkettingCarrier",
																"operatingflight"=> "$backmarkettingFN",
																"departure"=> "$backDepartureFrom",
																"departureAirport"=> "$backdAirport",
																"departureLocation"=> "$backdCity , $backdCountry",                    
																"departureTime" => "$backDepartureTime",
																"arrival"=> "$backArrivalTo",                   
																"arrivalTime" => "$backArrivalTime",
																"arrivalAirport"=> "$backaAirport",
																"arrivalLocation"=> "$backaCity , $backaCountry",
																"flightduration"=> "$backTravelTime",
																"bookingcode"=> "$backBookingCode",
																"Baggage" => "$backBags",
																"seat"=> "$backSeat",
																"segmentDetails"=> $airAirSegment[$backSegmentRef])
														
														

												)
											);

					$basic = array("system" => "Galileo",
									"segment"=> "1",
									"career"=> "$validatingCarrierCode",
									"careerName" => "$CarrieerName",
									"baseprice" => "$BasePrice",
									"taxes" => "$Taxes",
									"price" => "$Exact",
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
									"godeparture"=> "$From",                   
									"godepartureTime" => $goDepartureTime,
									"godepartureDate" => $godpTime,
									"goarrival"=> "$To", 
									"goarrivalTime" => "$goArrivalTime",
									"goarrivalDate" => "$goarrTime",                
									"backdeparture"=> "$To",                   
									"backdepartureTime" => $backDepartureTime,
									"backdepartureDate" => $backdpTime,
									"backarrival"=> "$From", 
									"backarrivalTime" => "$backArrivalTime", 
									"backarrivalDate" => $backarrTime,  
									"goflightduration"=> "$goTravelTimeHm",
									"backflightduration"=> "$backTravelTimeHm",									
									"seat" => "$goSeat",
									"class" => "$goCabinClass",
									"refundable"=> "$Refundable ",
									"segments" => $segment
								);

							array_push($All,$basic);



						}else if(isset($airPricePoint[0]['airOption'][0]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == True &&
								isset($airPricePoint[1]['airOption'][0]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == True){
							
							//go Leg 1						
							$goFareInfoRef = $airPricePoint[0]['airOption'][0]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][0]['airBookingInfo'][0]['@attributes']['SegmentRef'];
							$goBags = $airFareInfo[$goFareInfoRef]['Bags'];						
							$goTravelTime = $airAirSegment[$goSegmentRef]['TravelTime'];												
							$goTravelTimeHm = floor($goTravelTime / 60)."H ".($goTravelTime - ((floor($goTravelTime / 60)) * 60))."Min";


							$goArrivalTo = $airAirSegment[$goSegmentRef]['Destination'];
							$goDepartureFrom = $airAirSegment[$goSegmentRef]['Origin'];

							$goArrivalTime = $airAirSegment[$goSegmentRef]['ArrivalTime'];
							$goDepartureTime = $airAirSegment[$goSegmentRef]['DepartureTime'];

							$godpTime = date("D d M Y", strtotime($goDepartureTime));
							$goarrTime = date("D d M Y", strtotime($goArrivalTime));
							
							$gomarkettingCarrier = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
							$gomkrow = mysqli_fetch_array($gomksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$gomarkettingCarrierName = $gomkrow['name'];		
							}

							// Departure Country
							$godsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
							$godrow = mysqli_fetch_array($godsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$godAirport = $godrow['name'];
								$godCity = $godrow['cityName'];
								$godCountry = $godrow['countryCode'];		
							}

							// Departure Country
							$goasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$goarow = mysqli_fetch_array($goasql,MYSQLI_ASSOC);

							if(!empty($goarow)){
								$goaAirport = $goarow['name'];
								$goaCity = $goarow['cityName'];
								$goaCountry = $goarow['countryCode'];
							}
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][0]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][0]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][0]['airBookingInfo'][0]['@attributes']['CabinClass'];

							//go Leg 2						
							$goFareInfoRef1 = $airPricePoint[0]['airOption'][0]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$goSegmentRef1 = $airPricePoint[0]['airOption'][0]['airBookingInfo'][1]['@attributes']['SegmentRef'];
							$goBags1 = $airFareInfo[$goFareInfoRef1]['Bags'];						
							$goTravelTime1 = $airAirSegment[$goSegmentRef1]['TravelTime'];												
							$goTravelTimeHm1 = floor($goTravelTime1 / 60)."H ".($goTravelTime1 - ((floor($goTravelTime1 / 60)) * 60))."Min";


							$goArrivalTo1 = $airAirSegment[$goSegmentRef1]['Destination'];
							$goDepartureFrom1 = $airAirSegment[$goSegmentRef1]['Origin'];

							$goArrivalTime1 = $airAirSegment[$goSegmentRef1]['ArrivalTime'];
							$goDepartureTime1 = $airAirSegment[$goSegmentRef1]['DepartureTime'];

							$godpTime1 = date("D d M Y", strtotime($goDepartureTime1));
							$goarrTime1 = date("D d M Y", strtotime($goArrivalTime1));
							
							$gomarkettingCarrier1 = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN1 = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier1' ");
							$gomkrow1 = mysqli_fetch_array($gomksql1,MYSQLI_ASSOC);

							if(!empty($gomkrow1)){
								$gomarkettingCarrierName1 = $gomkrow1['name'];		
							}

							// Departure Country
							$godsql1 = mysqli_query($conn,"$Airportsql code='$goDepartureFrom1' ");
							$godrow1 = mysqli_fetch_array($godsql1,MYSQLI_ASSOC);

							if(!empty($godrow1)){
								$godAirport1 = $godrow1['name'];
								$godCity1 = $godrow1['cityName'];
								$godCountry1 = $godrow1['countryCode'];		
							}

							// Departure Country
							$goasql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo1' ");
							$goarow1 = mysqli_fetch_array($goasql1,MYSQLI_ASSOC);

							if(!empty($goarow1)){
								$goaAirport1 = $goarow1['name'];
								$goaCity1 = $goarow1['cityName'];
								$goaCountry1 = $goarow1['countryCode'];
							}
							
							
							$goBookingCode1 = $airPricePoint[0]['airOption'][0]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$goSeat1 = $airPricePoint[0]['airOption'][0]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$goCabinClass1 = $airPricePoint[0]['airOption'][0]['airBookingInfo'][1]['@attributes']['CabinClass'];

							
							//Back Leg 1
							
							$backFareInfoRef = $airPricePoint[1]['airOption'][0]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$backSegmentRef = $airPricePoint[1]['airOption'][0]['airBookingInfo'][0]['@attributes']['SegmentRef'];
							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];						
							$backTravelTime = $airAirSegment[$backSegmentRef]['TravelTime'];												
							$backTravelTimeHm = floor($backTravelTime / 60)."H ".($backTravelTime - ((floor($backTravelTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backdpTime = date("D d M Y", strtotime($backDepartureTime));
							$backarrTime = date("D d M Y", strtotime($backArrivalTime));
							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backmksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
							$backmkrow = mysqli_fetch_array($backmksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$backmarkettingCarrierName = $backmkrow['name'];		
							}

							// Departure Country
							$backdsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backdrow = mysqli_fetch_array($backdsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$backdAirport = $backdrow['name'];
								$backdCity = $backdrow['cityName'];
								$backdCountry = $backdrow['countryCode'];		
							}

							// Departure Country
							$backasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$backarow = mysqli_fetch_array($backasql,MYSQLI_ASSOC);

							if(!empty($backarow)){
								$backaAirport = $backarow['name'];
								$backaCity = $backarow['cityName'];
								$backaCountry = $backarow['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][0]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][0]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][0]['airBookingInfo'][0]['@attributes']['CabinClass'];

							//Back Leg 2
							
							$backFareInfoRef1 = $airPricePoint[1]['airOption'][0]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$backSegmentRef1 = $airPricePoint[1]['airOption'][0]['airBookingInfo'][1]['@attributes']['SegmentRef'];
							$backBags1 = $airFareInfo[$backFareInfoRef1]['Bags'];						
							$backTravelTime1 = $airAirSegment[$backSegmentRef1]['TravelTime'];												
							$backTravelTimeHm1 = floor($backTravelTime1 / 60)."H ".($backTravelTime1 - ((floor($backTravelTime1 / 60)) * 60))."Min";


							$backArrivalTo1 = $airAirSegment[$backSegmentRef1]['Destination'];
							$backDepartureFrom1 = $airAirSegment[$backSegmentRef1]['Origin'];

							$backArrivalTime1 = $airAirSegment[$backSegmentRef1]['ArrivalTime'];
							$backDepartureTime1 = $airAirSegment[$backSegmentRef1]['DepartureTime'];

							$backdpTime1 = date("D d M Y", strtotime($backDepartureTime1));
							$backarrTime1 = date("D d M Y", strtotime($backArrivalTime1));
							
							$backmarkettingCarrier1 = $airAirSegment[$backSegmentRef1]['Carrier'];
							$backmarkettingFN1 = $airAirSegment[$backSegmentRef1]['FlightNumber'];

							$backmksql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier1' ");
							$backmkrow1 = mysqli_fetch_array($backmksql1,MYSQLI_ASSOC);

							if(!empty($backmkrow1)){
								$backmarkettingCarrierName1 = $backmkrow1['name'];		
							}

							// Departure Country
							$backdsql1 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom1' ");
							$backdrow1 = mysqli_fetch_array($backdsql1,MYSQLI_ASSOC);

							if(!empty($godrow1)){
								$backdAirport1 = $backdrow1['name'];
								$backdCity1 = $backdrow1['cityName'];
								$backdCountry1 = $backdrow1['countryCode'];		
							}

							// Departure Country
							$backasql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo1' ");
							$backarow1 = mysqli_fetch_array($backasql1,MYSQLI_ASSOC);

							if(!empty($backarow1)){
								$backaAirport1 = $backarow1['name'];
								$backaCity1 = $backarow1['cityName'];
								$backaCountry1 = $backarow1['countryCode'];
							}
							
							
							$backBookingCode1 = $airPricePoint[1]['airOption'][0]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$backSeat1 = $airPricePoint[1]['airOption'][0]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$backCabinClass1 = $airPricePoint[1]['airOption'][0]['airBookingInfo'][1]['@attributes']['CabinClass'];


							$segment = array("go" =>
											array("0" =>
													array("marketingcareer"=> "$gomarkettingCarrier",
														"marketingflight"=> "$gomarkettingFN",
															"operatingcareer"=> "$gomarkettingCarrier",
															"operatingflight"=> "$gomarkettingFN",
															"departure"=> "$goDepartureFrom",
															"departureAirport"=> "$godAirport",
															"departureLocation"=> "$godCity , $godCountry",                    
															"departureTime" => "$goDepartureTime",
															"arrival"=> "$goArrivalTo",                   
															"arrivalTime" => "$goArrivalTime",
															"arrivalAirport"=> "$goaAirport",
															"arrivalLocation"=> "$goaCity , $goaCountry",
															"flightduration"=> "$goTravelTime",
															"bookingcode"=> "$goBookingCode",
															"seat"=> "$goSeat",
															"bags" => "$goBags",
															"segmentDetails"=> $airAirSegment[$goSegmentRef]),
													"1" =>
													array("marketingcareer"=> "$gomarkettingCarrier1",
														"marketingflight"=> "$gomarkettingFN1",
															"operatingcareer"=> "$gomarkettingCarrier1",
															"operatingflight"=> "$gomarkettingFN1",
															"departure"=> "$goDepartureFrom1",
															"departureAirport"=> "$godAirport1",
															"departureLocation"=> "$godCity1 , $godCountry1",                    
															"departureTime" => "$goDepartureTime1",
															"arrival"=> "$goArrivalTo1",                   
															"arrivalTime" => "$goArrivalTime1",
															"arrivalAirport"=> "$goaAirport1",
															"arrivalLocation"=> "$goaCity1 , $goaCountry1",
															"flightduration"=> "$goTravelTime1",
															"bookingcode"=> "$goBookingCode1",
															"seat"=> "$goSeat1",
															"bags" => "$goBags1",
															"segmentDetails"=> $airAirSegment[$goSegmentRef1])																																				
												),
									"back" => array("0" =>
														array("marketingcareer"=> "$backmarkettingCarrier",
																"marketingflight"=> "$backmarkettingFN",
																"operatingcareer"=> "$backmarkettingCarrier",
																"operatingflight"=> "$backmarkettingFN",
																"departure"=> "$backDepartureFrom",
																"departureAirport"=> "$backdAirport",
																"departureLocation"=> "$backdCity , $backdCountry",                    
																"departureTime" => "$backDepartureTime",
																"arrival"=> "$backArrivalTo",                   
																"arrivalTime" => "$backArrivalTime",
																"arrivalAirport"=> "$backaAirport",
																"arrivalLocation"=> "$backaCity , $backaCountry",
																"flightduration"=> "$backTravelTime",
																"bookingcode"=> "$backBookingCode",
																"seat"=> "$backSeat",
																"bags" => "$backBags",
																"segmentDetails"=> $airAirSegment[$backSegmentRef]),
												"1" =>
														array("marketingcareer"=> "$backmarkettingCarrier1",
																"marketingflight"=> "$backmarkettingFN1",
																"operatingcareer"=> "$backmarkettingCarrier1",
																"operatingflight"=> "$backmarkettingFN1",
																"departure"=> "$backDepartureFrom1",
																"departureAirport"=> "$backdAirport1",
																"departureLocation"=> "$backdCity1 , $backdCountry1",                    
																"departureTime" => "$backDepartureTime1",
																"arrival"=> "$backArrivalTo1",                   
																"arrivalTime" => "$backArrivalTime1",
																"arrivalAirport"=> "$backaAirport1",
																"arrivalLocation"=> "$backaCity1 , $backaCountry1",
																"flightduration"=> "$backTravelTime1",
																"bookingcode"=> "$backBookingCode1",
																"seat"=> "$backSeat1",
																"bags" => "$backBags1",
																"class" => "$backCabinClass1",
																"segmentDetails"=> $airAirSegment[$backSegmentRef1])																									

												)
											);

							$basic = array("system" => "Galileo",
									"segment"=> "2",
									"career"=> "$validatingCarrierCode",
									"careerName" => "$CarrieerName",
									"baseprice" => "$BasePrice",
									"taxes" => "$Taxes",
									"price" => "$Exact",
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
									"godeparture"=> "$From",                   
									"godepartureTime" => $goDepartureTime,
									"godepartureDate" => $godpTime,
									"goarrival"=> "$To", 
									"goarrivalTime" => "$goArrivalTime1",
									"goarrivalDate" => "$goarrTime1",                
									"backdeparture"=> "$To",                   
									"backdepartureTime" => $backDepartureTime,
									"backdepartureDate" => $backdpTime,
									"backarrival"=> "$From", 
									"backarrivalTime" => "$backArrivalTime1", 
									"backarrivalDate" => $backarrTime1,  
									"goflightduration"=> "$goTravelTimeHm",
									"backflightduration"=> "$backTravelTimeHm",
																									
									"refundable"=> "$Refundable ",
									"segments" => $segment
								);

							array_push($All,$basic);
							
						}

					

					}if(isset($airPricePoint[0]['airOption'][1]) == TRUE && isset($airPricePoint[1]['airOption'][1]) == TRUE){
						if(isset($airPricePoint[0]['airOption'][1]['airBookingInfo']['@attributes']['FareInfoRef']) == True &&
						isset($airPricePoint[1]['airOption'][1]['airBookingInfo']['@attributes']['FareInfoRef']) == True
						){
							//go Leg 1
							
							$goFareInfoRef = $airPricePoint[0]['airOption'][1]['airBookingInfo']['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][1]['airBookingInfo']['@attributes']['SegmentRef'];
							$goBags = $airFareInfo[$goFareInfoRef]['Bags'];						
							$goTravelTime = $airAirSegment[$goSegmentRef]['TravelTime'];												
							$goTravelTimeHm = floor($goTravelTime / 60)."H ".($goTravelTime - ((floor($goTravelTime / 60)) * 60))."Min";


							$goArrivalTo = $airAirSegment[$goSegmentRef]['Destination'];
							$goDepartureFrom = $airAirSegment[$goSegmentRef]['Origin'];

							$goArrivalTime = $airAirSegment[$goSegmentRef]['ArrivalTime'];
							$goDepartureTime = $airAirSegment[$goSegmentRef]['DepartureTime'];

							$godpTime = date("D d M Y", strtotime($goDepartureTime));
							$goarrTime = date("D d M Y", strtotime($goArrivalTime));
							
							$gomarkettingCarrier = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
							$gomkrow = mysqli_fetch_array($gomksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$gomarkettingCarrierName = $gomkrow['name'];		
							}

							// Departure Country
							$godsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
							$godrow = mysqli_fetch_array($godsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$godAirport = $godrow['name'];
								$godCity = $godrow['cityName'];
								$godCountry = $godrow['countryCode'];		
							}

							// Departure Country
							$goasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$goarow = mysqli_fetch_array($goasql,MYSQLI_ASSOC);

							if(!empty($goarow)){
								$goaAirport = $goarow['name'];
								$goaCity = $goarow['cityName'];
								$goaCountry = $goarow['countryCode'];
							}
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][1]['airBookingInfo']['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][1]['airBookingInfo']['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][1]['airBookingInfo']['@attributes']['CabinClass'];

							
							//Back Leg 1
							
							$backFareInfoRef = $airPricePoint[1]['airOption'][1]['airBookingInfo']['@attributes']['FareInfoRef'];						
							$backSegmentRef = $airPricePoint[1]['airOption'][1]['airBookingInfo']['@attributes']['SegmentRef'];
							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];						
							$backTravelTime = $airAirSegment[$backSegmentRef]['TravelTime'];												
							$backTravelTimeHm = floor($backTravelTime / 60)."H ".($backTravelTime - ((floor($backTravelTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backdpTime = date("D d M Y", strtotime($backDepartureTime));
							$backarrTime = date("D d M Y", strtotime($backArrivalTime));
							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backmksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
							$backmkrow = mysqli_fetch_array($backmksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$backmarkettingCarrierName = $backmkrow['name'];		
							}

							// Departure Country
							$backdsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backdrow = mysqli_fetch_array($backdsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$backdAirport = $backdrow['name'];
								$backdCity = $backdrow['cityName'];
								$backdCountry = $backdrow['countryCode'];		
							}

							// Departure Country
							$backasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$backarow = mysqli_fetch_array($backasql,MYSQLI_ASSOC);

							if(!empty($backarow)){
								$backaAirport = $backarow['name'];
								$backaCity = $backarow['cityName'];
								$backaCountry = $backarow['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][1]['airBookingInfo']['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][1]['airBookingInfo']['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][1]['airBookingInfo']['@attributes']['CabinClass'];

						$segment = array("go" =>
									array("0" =>
											array("marketingcareer"=> "$gomarkettingCarrier",
												"marketingflight"=> "$gomarkettingFN",
													"operatingcareer"=> "$gomarkettingCarrier",
													"operatingflight"=> "$gomarkettingFN",
													"departure"=> "$goDepartureFrom",
													"departureAirport"=> "$godAirport",
													"departureLocation"=> "$godCity , $godCountry",                    
													"departureTime" => "$goDepartureTime",
													"arrival"=> "$goArrivalTo",                   
													"arrivalTime" => "$goArrivalTime",
													"arrivalAirport"=> "$goaAirport",
													"arrivalLocation"=> "$goaCity , $goaCountry",
													"flightduration"=> "$goTravelTime",
													"bookingcode"=> "$goBookingCode",
													"Baggage" => "$goBags",
													"seat"=> "$goSeat",
													"segmentDetails"=> $airAirSegment[$goSegmentRef])																										

												),
									"back" => array("0" =>
														array("marketingcareer"=> "$backmarkettingCarrier",
																"marketingflight"=> "$backmarkettingFN",
																"operatingcareer"=> "$backmarkettingCarrier",
																"operatingflight"=> "$backmarkettingFN",
																"departure"=> "$backDepartureFrom",
																"departureAirport"=> "$backdAirport",
																"departureLocation"=> "$backdCity , $backdCountry",                    
																"departureTime" => "$backDepartureTime",
																"arrival"=> "$backArrivalTo",                   
																"arrivalTime" => "$backArrivalTime",
																"arrivalAirport"=> "$backaAirport",
																"arrivalLocation"=> "$backaCity , $backaCountry",
																"flightduration"=> "$backTravelTime",
																"bookingcode"=> "$backBookingCode",
																"Baggage" => "$backBags",
																"seat"=> "$backSeat",
																"segmentDetails"=> $airAirSegment[$backSegmentRef])
														
														

												)
											);

					$basic = array("system" => "Galileo",
									"segment"=> "1",
									"career"=> "$validatingCarrierCode",
									"careerName" => "$CarrieerName",
									"baseprice" => "$BasePrice",
									"taxes" => "$Taxes",
									"price" => "$Exact",
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
									"godeparture"=> "$From",                   
									"godepartureTime" => $goDepartureTime,
									"godepartureDate" => $godpTime,
									"goarrival"=> "$To", 
									"goarrivalTime" => "$goArrivalTime",
									"goarrivalDate" => "$goarrTime",                
									"backdeparture"=> "$To",                   
									"backdepartureTime" => $backDepartureTime,
									"backdepartureDate" => $backdpTime,
									"backarrival"=> "$From", 
									"backarrivalTime" => "$backArrivalTime", 
									"backarrivalDate" => $backarrTime,  
									"goflightduration"=> "$goTravelTime",
									"backflightduration"=> "$backTravelTime",
									
									"seat" => "$goSeat",
									"class" => "$goCabinClass",
									"refundable"=> "$Refundable ",
									"segments" => $segment
								);

							array_push($All,$basic);



						}else if(isset($airPricePoint[0]['airOption'][1]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == True &&
								isset($airPricePoint[1]['airOption'][1]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == True){
							
							//go Leg 1						
							$goFareInfoRef = $airPricePoint[0]['airOption'][1]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][1]['airBookingInfo'][0]['@attributes']['SegmentRef'];
							$goBags = $airFareInfo[$goFareInfoRef]['Bags'];						
							$goTravelTime = $airAirSegment[$goSegmentRef]['TravelTime'];												
							$goTravelTimeHm = floor($goTravelTime / 60)."H ".($goTravelTime - ((floor($goTravelTime / 60)) * 60))."Min";


							$goArrivalTo = $airAirSegment[$goSegmentRef]['Destination'];
							$goDepartureFrom = $airAirSegment[$goSegmentRef]['Origin'];

							$goArrivalTime = $airAirSegment[$goSegmentRef]['ArrivalTime'];
							$goDepartureTime = $airAirSegment[$goSegmentRef]['DepartureTime'];

							$godpTime = date("D d M Y", strtotime($goDepartureTime));
							$goarrTime = date("D d M Y", strtotime($goArrivalTime));
							
							$gomarkettingCarrier = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
							$gomkrow = mysqli_fetch_array($gomksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$gomarkettingCarrierName = $gomkrow['name'];		
							}

							// Departure Country
							$godsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
							$godrow = mysqli_fetch_array($godsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$godAirport = $godrow['name'];
								$godCity = $godrow['cityName'];
								$godCountry = $godrow['countryCode'];		
							}

							// Departure Country
							$goasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$goarow = mysqli_fetch_array($goasql,MYSQLI_ASSOC);

							if(!empty($goarow)){
								$goaAirport = $goarow['name'];
								$goaCity = $goarow['cityName'];
								$goaCountry = $goarow['countryCode'];
							}
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][1]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][1]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][1]['airBookingInfo'][0]['@attributes']['CabinClass'];

							//go Leg 2						
							$goFareInfoRef1 = $airPricePoint[0]['airOption'][1]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$goSegmentRef1 = $airPricePoint[0]['airOption'][1]['airBookingInfo'][1]['@attributes']['SegmentRef'];
							$goBags1 = $airFareInfo[$goFareInfoRef1]['Bags'];						
							$goTravelTime1 = $airAirSegment[$goSegmentRef1]['TravelTime'];												
							$goTravelTimeHm1 = floor($goTravelTime1 / 60)."H ".($goTravelTime1 - ((floor($goTravelTime1 / 60)) * 60))."Min";


							$goArrivalTo1 = $airAirSegment[$goSegmentRef1]['Destination'];
							$goDepartureFrom1 = $airAirSegment[$goSegmentRef1]['Origin'];

							$goArrivalTime1 = $airAirSegment[$goSegmentRef1]['ArrivalTime'];
							$goDepartureTime1 = $airAirSegment[$goSegmentRef1]['DepartureTime'];

							$godpTime1 = date("D d M Y", strtotime($goDepartureTime1));
							$goarrTime1 = date("D d M Y", strtotime($goArrivalTime1));
							
							$gomarkettingCarrier1 = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN1 = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier1' ");
							$gomkrow1 = mysqli_fetch_array($gomksql1,MYSQLI_ASSOC);

							if(!empty($gomkrow1)){
								$gomarkettingCarrierName1 = $gomkrow1['name'];		
							}

							// Departure Country
							$godsql1 = mysqli_query($conn,"$Airportsql code='$goDepartureFrom1' ");
							$godrow1 = mysqli_fetch_array($godsql1,MYSQLI_ASSOC);

							if(!empty($godrow1)){
								$godAirport1 = $godrow1['name'];
								$godCity1 = $godrow1['cityName'];
								$godCountry1 = $godrow1['countryCode'];		
							}

							// Departure Country
							$goasql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo1' ");
							$goarow1 = mysqli_fetch_array($goasql1,MYSQLI_ASSOC);

							if(!empty($goarow1)){
								$goaAirport1 = $goarow1['name'];
								$goaCity1 = $goarow1['cityName'];
								$goaCountry1 = $goarow1['countryCode'];
							}
							
							
							$goBookingCode1 = $airPricePoint[0]['airOption'][1]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$goSeat1 = $airPricePoint[0]['airOption'][1]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$goCabinClass1 = $airPricePoint[0]['airOption'][1]['airBookingInfo'][1]['@attributes']['CabinClass'];

							
							//Back Leg 1
							
							$backFareInfoRef = $airPricePoint[1]['airOption'][1]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$backSegmentRef = $airPricePoint[1]['airOption'][1]['airBookingInfo'][0]['@attributes']['SegmentRef'];
							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];						
							$backTravelTime = $airAirSegment[$backSegmentRef]['TravelTime'];												
							$backTravelTimeHm = floor($backTravelTime / 60)."H ".($backTravelTime - ((floor($backTravelTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backdpTime = date("D d M Y", strtotime($backDepartureTime));
							$backarrTime = date("D d M Y", strtotime($backArrivalTime));
							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backmksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
							$backmkrow = mysqli_fetch_array($backmksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$backmarkettingCarrierName = $backmkrow['name'];		
							}

							// Departure Country
							$backdsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backdrow = mysqli_fetch_array($backdsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$backdAirport = $backdrow['name'];
								$backdCity = $backdrow['cityName'];
								$backdCountry = $backdrow['countryCode'];		
							}

							// Departure Country
							$backasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$backarow = mysqli_fetch_array($backasql,MYSQLI_ASSOC);

							if(!empty($backarow)){
								$backaAirport = $backarow['name'];
								$backaCity = $backarow['cityName'];
								$backaCountry = $backarow['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][1]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][1]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][1]['airBookingInfo'][0]['@attributes']['CabinClass'];

							//Back Leg 2
							
							$backFareInfoRef1 = $airPricePoint[1]['airOption'][1]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$backSegmentRef1 = $airPricePoint[1]['airOption'][1]['airBookingInfo'][1]['@attributes']['SegmentRef'];
							$backBags1 = $airFareInfo[$backFareInfoRef1]['Bags'];						
							$backTravelTime1 = $airAirSegment[$backSegmentRef1]['TravelTime'];												
							$backTravelTimeHm1 = floor($backTravelTime1 / 60)."H ".($backTravelTime1 - ((floor($backTravelTime1 / 60)) * 60))."Min";


							$backArrivalTo1 = $airAirSegment[$backSegmentRef1]['Destination'];
							$backDepartureFrom1 = $airAirSegment[$backSegmentRef1]['Origin'];

							$backArrivalTime1 = $airAirSegment[$backSegmentRef1]['ArrivalTime'];
							$backDepartureTime1 = $airAirSegment[$backSegmentRef1]['DepartureTime'];

							$backdpTime1 = date("D d M Y", strtotime($backDepartureTime1));
							$backarrTime1 = date("D d M Y", strtotime($backArrivalTime1));
							
							$backmarkettingCarrier1 = $airAirSegment[$backSegmentRef1]['Carrier'];
							$backmarkettingFN1 = $airAirSegment[$backSegmentRef1]['FlightNumber'];

							$backmksql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier1' ");
							$backmkrow1 = mysqli_fetch_array($backmksql1,MYSQLI_ASSOC);

							if(!empty($gomkrow1)){
								$backmarkettingCarrierName1 = $backmkrow1['name'];		
							}

							// Departure Country
							$backdsql1 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom1' ");
							$backdrow1 = mysqli_fetch_array($backdsql1,MYSQLI_ASSOC);

							if(!empty($godrow1)){
								$backdAirport1 = $backdrow1['name'];
								$backdCity1 = $backdrow1['cityName'];
								$backdCountry1 = $backdrow1['countryCode'];		
							}

							// Departure Country
							$backasql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo1' ");
							$backarow1 = mysqli_fetch_array($backasql1,MYSQLI_ASSOC);

							if(!empty($backarow1)){
								$backaAirport1 = $backarow1['name'];
								$backaCity1 = $backarow1['cityName'];
								$backaCountry1 = $backarow1['countryCode'];
							}
							
							
							$backBookingCode1 = $airPricePoint[1]['airOption'][1]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$backSeat1 = $airPricePoint[1]['airOption'][1]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$backCabinClass1 = $airPricePoint[1]['airOption'][1]['airBookingInfo'][1]['@attributes']['CabinClass'];


							$segment = array("go" =>
											array("0" =>
													array("marketingcareer"=> "$gomarkettingCarrier",
														"marketingflight"=> "$gomarkettingFN",
															"operatingcareer"=> "$gomarkettingCarrier",
															"operatingflight"=> "$gomarkettingFN",
															"departure"=> "$goDepartureFrom",
															"departureAirport"=> "$godAirport",
															"departureLocation"=> "$godCity , $godCountry",                    
															"departureTime" => "$goDepartureTime",
															"arrival"=> "$goArrivalTo",                   
															"arrivalTime" => "$goArrivalTime",
															"arrivalAirport"=> "$goaAirport",
															"arrivalLocation"=> "$goaCity , $goaCountry",
															"flightduration"=> "$goTravelTime",
															"bookingcode"=> "$goBookingCode",
															"seat"=> "$goSeat",
															"bags" => "$goBags",
															"class" => "$goCabinClass",
															"segmentDetails"=> $airAirSegment[$goSegmentRef]),
													"1" =>
													array("marketingcareer"=> "$gomarkettingCarrier1",
														"marketingflight"=> "$gomarkettingFN1",
															"operatingcareer"=> "$gomarkettingCarrier1",
															"operatingflight"=> "$gomarkettingFN1",
															"departure"=> "$goDepartureFrom1",
															"departureAirport"=> "$godAirport1",
															"departureLocation"=> "$godCity1 , $godCountry1",                    
															"departureTime" => "$goDepartureTime1",
															"arrival"=> "$goArrivalTo1",                   
															"arrivalTime" => "$goArrivalTime1",
															"arrivalAirport"=> "$goaAirport1",
															"arrivalLocation"=> "$goaCity1 , $goaCountry1",
															"flightduration"=> "$goTravelTime1",
															"bookingcode"=> "$goBookingCode1",
															"seat"=> "$goSeat1",
															"bags" => "$goBags1",
															"class" => "$goCabinClass1",
															"segmentDetails"=> $airAirSegment[$goSegmentRef1])																																				
												),
									"back" => array("0" =>
														array("marketingcareer"=> "$backmarkettingCarrier",
																"marketingflight"=> "$backmarkettingFN",
																"operatingcareer"=> "$backmarkettingCarrier",
																"operatingflight"=> "$backmarkettingFN",
																"departure"=> "$backDepartureFrom",
																"departureAirport"=> "$backdAirport",
																"departureLocation"=> "$backdCity , $backdCountry",                    
																"departureTime" => "$backDepartureTime",
																"arrival"=> "$backArrivalTo",                   
																"arrivalTime" => "$backArrivalTime",
																"arrivalAirport"=> "$backaAirport",
																"arrivalLocation"=> "$backaCity , $backaCountry",
																"flightduration"=> "$backTravelTime",
																"bookingcode"=> "$backBookingCode",
																"seat"=> "$backSeat",
																"bags" => "$backBags",
																"class" => "$backCabinClass",
																"segmentDetails"=> $airAirSegment[$backSegmentRef]),
												"1" =>
														array("marketingcareer"=> "$backmarkettingCarrier1",
																"marketingflight"=> "$backmarkettingFN1",
																"operatingcareer"=> "$backmarkettingCarrier1",
																"operatingflight"=> "$backmarkettingFN1",
																"departure"=> "$backDepartureFrom1",
																"departureAirport"=> "$backdAirport1",
																"departureLocation"=> "$backdCity1 , $backdCountry1",                    
																"departureTime" => "$backDepartureTime1",
																"arrival"=> "$backArrivalTo1",                   
																"arrivalTime" => "$backArrivalTime1",
																"arrivalAirport"=> "$backaAirport1",
																"arrivalLocation"=> "$backaCity1 , $backaCountry1",
																"flightduration"=> "$backTravelTime1",
																"bookingcode"=> "$backBookingCode1",
																"seat"=> "$backSeat1",
																"class" => "$backCabinClass1",
																"bags" => "$backBags1",
																"segmentDetails"=> $airAirSegment[$backSegmentRef1])																									

												)
											);

							$basic = array("system" => "Galileo",
									"segment"=> "2",
									"career"=> "$validatingCarrierCode",
									"careerName" => "$CarrieerName",
									"baseprice" => "$BasePrice",
									"taxes" => "$Taxes",
									"price" => "$Exact",
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
									"godeparture"=> "$From",                   
									"godepartureTime" => $goDepartureTime,
									"godepartureDate" => $godpTime,
									"goarrival"=> "$To", 
									"goarrivalTime" => "$goArrivalTime1",
									"goarrivalDate" => "$goarrTime1",                
									"backdeparture"=> "$To",                   
									"backdepartureTime" => $backDepartureTime,
									"backdepartureDate" => $backdpTime,
									"backarrival"=> "$From", 
									"backarrivalTime" => "$backArrivalTime1", 
									"backarrivalDate" => $backarrTime1,  
									"goflightduration"=> "$goTravelTime",
									"backflightduration"=> "$backTravelTime",
									"refundable"=> "$Refundable ",
									"segments" => $segment
								);

							array_push($All,$basic);
							
												
							
						}
							
					}if(isset($airPricePoint[0]['airOption'][2]) == TRUE && isset($airPricePoint[1]['airOption'][2]) == TRUE){
						if(isset($airPricePoint[0]['airOption'][2]['airBookingInfo']['@attributes']['FareInfoRef']) == True &&
						isset($airPricePoint[1]['airOption'][2]['airBookingInfo']['@attributes']['FareInfoRef']) == True
						){
							//go Leg 1
							
							$goFareInfoRef = $airPricePoint[0]['airOption'][2]['airBookingInfo']['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][2]['airBookingInfo']['@attributes']['SegmentRef'];
							$goBags = $airFareInfo[$goFareInfoRef]['Bags'];						
							$goTravelTime = $airAirSegment[$goSegmentRef]['TravelTime'];												
							$goTravelTimeHm = floor($goTravelTime / 60)."H ".($goTravelTime - ((floor($goTravelTime / 60)) * 60))."Min";


							$goArrivalTo = $airAirSegment[$goSegmentRef]['Destination'];
							$goDepartureFrom = $airAirSegment[$goSegmentRef]['Origin'];

							$goArrivalTime = $airAirSegment[$goSegmentRef]['ArrivalTime'];
							$goDepartureTime = $airAirSegment[$goSegmentRef]['DepartureTime'];

							$godpTime = date("D d M Y", strtotime($goDepartureTime));
							$goarrTime = date("D d M Y", strtotime($goArrivalTime));
							
							$gomarkettingCarrier = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
							$gomkrow = mysqli_fetch_array($gomksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$gomarkettingCarrierName = $gomkrow['name'];		
							}

							// Departure Country
							$godsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
							$godrow = mysqli_fetch_array($godsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$godAirport = $godrow['name'];
								$godCity = $godrow['cityName'];
								$godCountry = $godrow['countryCode'];		
							}

							// Departure Country
							$goasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$goarow = mysqli_fetch_array($goasql,MYSQLI_ASSOC);

							if(!empty($goarow)){
								$goaAirport = $goarow['name'];
								$goaCity = $goarow['cityName'];
								$goaCountry = $goarow['countryCode'];
							}
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][2]['airBookingInfo']['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][2]['airBookingInfo']['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][2]['airBookingInfo']['@attributes']['CabinClass'];

							
							//Back Leg 1
							
							$backFareInfoRef = $airPricePoint[1]['airOption'][2]['airBookingInfo']['@attributes']['FareInfoRef'];						
							$backSegmentRef = $airPricePoint[1]['airOption'][2]['airBookingInfo']['@attributes']['SegmentRef'];
							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];						
							$backTravelTime = $airAirSegment[$backSegmentRef]['TravelTime'];												
							$backTravelTimeHm = floor($backTravelTime / 60)."H ".($backTravelTime - ((floor($backTravelTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backdpTime = date("D d M Y", strtotime($backDepartureTime));
							$backarrTime = date("D d M Y", strtotime($backArrivalTime));
							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backmksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
							$backmkrow = mysqli_fetch_array($backmksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$backmarkettingCarrierName = $backmkrow['name'];		
							}

							// Departure Country
							$backdsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backdrow = mysqli_fetch_array($backdsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$backdAirport = $backdrow['name'];
								$backdCity = $backdrow['cityName'];
								$backdCountry = $backdrow['countryCode'];		
							}

							// Departure Country
							$backasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$backarow = mysqli_fetch_array($backasql,MYSQLI_ASSOC);

							if(!empty($backarow)){
								$backaAirport = $backarow['name'];
								$backaCity = $backarow['cityName'];
								$backaCountry = $backarow['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][2]['airBookingInfo']['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][2]['airBookingInfo']['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][2]['airBookingInfo']['@attributes']['CabinClass'];

						$segment = array("go" =>
									array("0" =>
											array("marketingcareer"=> "$gomarkettingCarrier",
												"marketingflight"=> "$gomarkettingFN",
													"operatingcareer"=> "$gomarkettingCarrier",
													"operatingflight"=> "$gomarkettingFN",
													"departure"=> "$goDepartureFrom",
													"departureAirport"=> "$godAirport",
													"departureLocation"=> "$godCity , $godCountry",                    
													"departureTime" => "$goDepartureTime",
													"arrival"=> "$goArrivalTo",                   
													"arrivalTime" => "$goArrivalTime",
													"arrivalAirport"=> "$goaAirport",
													"arrivalLocation"=> "$goaCity , $goaCountry",
													"flightduration"=> "$goTravelTime",
													"bookingcode"=> "$goBookingCode",
													"Baggage" => "$goBags",
													"seat"=> "$goSeat",
													"segmentDetails"=> $airAirSegment[$goSegmentRef])																										

												),
									"back" => array("0" =>
														array("marketingcareer"=> "$backmarkettingCarrier",
																"marketingflight"=> "$backmarkettingFN",
																"operatingcareer"=> "$backmarkettingCarrier",
																"operatingflight"=> "$backmarkettingFN",
																"departure"=> "$backDepartureFrom",
																"departureAirport"=> "$backdAirport",
																"departureLocation"=> "$backdCity , $backdCountry",                    
																"departureTime" => "$backDepartureTime",
																"arrival"=> "$backArrivalTo",                   
																"arrivalTime" => "$backArrivalTime",
																"arrivalAirport"=> "$backaAirport",
																"arrivalLocation"=> "$backaCity , $backaCountry",
																"flightduration"=> "$backTravelTime",
																"bookingcode"=> "$backBookingCode",
																"Baggage" => "$backBags",
																"seat"=> "$backSeat",
																"segmentDetails"=> $airAirSegment[$backSegmentRef])
														
														

												)
											);

					$basic = array("system" => "Galileo",
									"segment"=> "1",
									"career"=> "$validatingCarrierCode",
									"careerName" => "$CarrieerName",
									"baseprice" => "$BasePrice",
									"taxes" => "$Taxes",
									"price" => "$Exact",
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
									"godeparture"=> "$From",                   
									"godepartureTime" => $goDepartureTime,
									"godepartureDate" => $godpTime,
									"goarrival"=> "$To", 
									"goarrivalTime" => "$goArrivalTime",
									"goarrivalDate" => "$goarrTime",                
									"backdeparture"=> "$To",                   
									"backdepartureTime" => $backDepartureTime,
									"backdepartureDate" => $backdpTime,
									"backarrival"=> "$From", 
									"backarrivalTime" => "$backArrivalTime", 
									"backarrivalDate" => $backarrTime,  
									"goflightduration"=> "$goTravelTime",
									"backflightduration"=> "$backTravelTime",
									
									"seat" => "$goSeat",
									"class" => "$goCabinClass",
									"refundable"=> "$Refundable ",
									"segments" => $segment
								);

							array_push($All,$basic);



						}else if(isset($airPricePoint[0]['airOption'][2]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == True &&
								isset($airPricePoint[1]['airOption'][2]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == True){
							
							//go Leg 1						
							$goFareInfoRef = $airPricePoint[0]['airOption'][2]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][2]['airBookingInfo'][0]['@attributes']['SegmentRef'];
							$goBags = $airFareInfo[$goFareInfoRef]['Bags'];						
							$goTravelTime = $airAirSegment[$goSegmentRef]['TravelTime'];												
							$goTravelTimeHm = floor($goTravelTime / 60)."H ".($goTravelTime - ((floor($goTravelTime / 60)) * 60))."Min";


							$goArrivalTo = $airAirSegment[$goSegmentRef]['Destination'];
							$goDepartureFrom = $airAirSegment[$goSegmentRef]['Origin'];

							$goArrivalTime = $airAirSegment[$goSegmentRef]['ArrivalTime'];
							$goDepartureTime = $airAirSegment[$goSegmentRef]['DepartureTime'];

							$godpTime = date("D d M Y", strtotime($goDepartureTime));
							$goarrTime = date("D d M Y", strtotime($goArrivalTime));
							
							$gomarkettingCarrier = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
							$gomkrow = mysqli_fetch_array($gomksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$gomarkettingCarrierName = $gomkrow['name'];		
							}

							// Departure Country
							$godsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
							$godrow = mysqli_fetch_array($godsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$godAirport = $godrow['name'];
								$godCity = $godrow['cityName'];
								$godCountry = $godrow['countryCode'];		
							}

							// Departure Country
							$goasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$goarow = mysqli_fetch_array($goasql,MYSQLI_ASSOC);

							if(!empty($goarow)){
								$goaAirport = $goarow['name'];
								$goaCity = $goarow['cityName'];
								$goaCountry = $goarow['countryCode'];
							}
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][2]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][2]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][2]['airBookingInfo'][0]['@attributes']['CabinClass'];

							//go Leg 2						
							$goFareInfoRef1 = $airPricePoint[0]['airOption'][2]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$goSegmentRef1 = $airPricePoint[0]['airOption'][2]['airBookingInfo'][1]['@attributes']['SegmentRef'];
							$goBags1 = $airFareInfo[$goFareInfoRef1]['Bags'];						
							$goTravelTime1 = $airAirSegment[$goSegmentRef1]['TravelTime'];												
							$goTravelTimeHm1 = floor($goTravelTime1 / 60)."H ".($goTravelTime1 - ((floor($goTravelTime1 / 60)) * 60))."Min";


							$goArrivalTo1 = $airAirSegment[$goSegmentRef1]['Destination'];
							$goDepartureFrom1 = $airAirSegment[$goSegmentRef1]['Origin'];

							$goArrivalTime1 = $airAirSegment[$goSegmentRef1]['ArrivalTime'];
							$goDepartureTime1 = $airAirSegment[$goSegmentRef1]['DepartureTime'];

							$godpTime1 = date("D d M Y", strtotime($goDepartureTime1));
							$goarrTime1 = date("D d M Y", strtotime($goArrivalTime1));
							
							$gomarkettingCarrier1 = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN1 = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier1' ");
							$gomkrow1 = mysqli_fetch_array($gomksql1,MYSQLI_ASSOC);

							if(!empty($gomkrow1)){
								$gomarkettingCarrierName1 = $gomkrow1['name'];		
							}

							// Departure Country
							$godsql1 = mysqli_query($conn,"$Airportsql code='$goDepartureFrom1' ");
							$godrow1 = mysqli_fetch_array($godsql1,MYSQLI_ASSOC);

							if(!empty($godrow1)){
								$godAirport1 = $godrow1['name'];
								$godCity1 = $godrow1['cityName'];
								$godCountry1 = $godrow1['countryCode'];		
							}

							// Departure Country
							$goasql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo1' ");
							$goarow1 = mysqli_fetch_array($goasql1,MYSQLI_ASSOC);

							if(!empty($goarow1)){
								$goaAirport1 = $goarow1['name'];
								$goaCity1 = $goarow1['cityName'];
								$goaCountry1 = $goarow1['countryCode'];
							}
							
							
							$goBookingCode1 = $airPricePoint[0]['airOption'][2]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$goSeat1 = $airPricePoint[0]['airOption'][2]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$goCabinClass1 = $airPricePoint[0]['airOption'][2]['airBookingInfo'][1]['@attributes']['CabinClass'];

							
							//Back Leg 1
							
							$backFareInfoRef = $airPricePoint[1]['airOption'][2]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$backSegmentRef = $airPricePoint[1]['airOption'][2]['airBookingInfo'][0]['@attributes']['SegmentRef'];
							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];						
							$backTravelTime = $airAirSegment[$backSegmentRef]['TravelTime'];												
							$backTravelTimeHm = floor($backTravelTime / 60)."H ".($backTravelTime - ((floor($backTravelTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backdpTime = date("D d M Y", strtotime($backDepartureTime));
							$backarrTime = date("D d M Y", strtotime($backArrivalTime));
							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backmksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
							$backmkrow = mysqli_fetch_array($backmksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$backmarkettingCarrierName = $backmkrow['name'];		
							}

							// Departure Country
							$backdsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backdrow = mysqli_fetch_array($backdsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$backdAirport = $backdrow['name'];
								$backdCity = $backdrow['cityName'];
								$backdCountry = $backdrow['countryCode'];		
							}

							// Departure Country
							$backasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$backarow = mysqli_fetch_array($backasql,MYSQLI_ASSOC);

							if(!empty($backarow)){
								$backaAirport = $backarow['name'];
								$backaCity = $backarow['cityName'];
								$backaCountry = $backarow['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][2]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][2]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][2]['airBookingInfo'][0]['@attributes']['CabinClass'];

							//Back Leg 2
							
							$backFareInfoRef1 = $airPricePoint[1]['airOption'][2]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$backSegmentRef1 = $airPricePoint[1]['airOption'][2]['airBookingInfo'][1]['@attributes']['SegmentRef'];
							$backBags1 = $airFareInfo[$backFareInfoRef1]['Bags'];						
							$backTravelTime1 = $airAirSegment[$backSegmentRef1]['TravelTime'];												
							$backTravelTimeHm1 = floor($backTravelTime1 / 60)."H ".($backTravelTime1 - ((floor($backTravelTime1 / 60)) * 60))."Min";


							$backArrivalTo1 = $airAirSegment[$backSegmentRef1]['Destination'];
							$backDepartureFrom1 = $airAirSegment[$backSegmentRef1]['Origin'];

							$backArrivalTime1 = $airAirSegment[$backSegmentRef1]['ArrivalTime'];
							$backDepartureTime1 = $airAirSegment[$backSegmentRef1]['DepartureTime'];

							$backdpTime1 = date("D d M Y", strtotime($backDepartureTime1));
							$backarrTime1 = date("D d M Y", strtotime($backArrivalTime1));
							
							$backmarkettingCarrier1 = $airAirSegment[$backSegmentRef1]['Carrier'];
							$backmarkettingFN1 = $airAirSegment[$backSegmentRef1]['FlightNumber'];

							$backmksql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier1' ");
							$backmkrow1 = mysqli_fetch_array($backmksql1,MYSQLI_ASSOC);

							if(!empty($gomkrow1)){
								$backmarkettingCarrierName1 = $backmkrow1['name'];		
							}

							// Departure Country
							$backdsql1 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom1' ");
							$backdrow1 = mysqli_fetch_array($backdsql1,MYSQLI_ASSOC);

							if(!empty($godrow1)){
								$backdAirport1 = $backdrow1['name'];
								$backdCity1 = $backdrow1['cityName'];
								$backdCountry1 = $backdrow1['countryCode'];		
							}

							// Departure Country
							$backasql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo1' ");
							$backarow1 = mysqli_fetch_array($backasql1,MYSQLI_ASSOC);

							if(!empty($backarow1)){
								$backaAirport1 = $backarow1['name'];
								$backaCity1 = $backarow1['cityName'];
								$backaCountry1 = $backarow1['countryCode'];
							}
							
							
							$backBookingCode1 = $airPricePoint[1]['airOption'][2]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$backSeat1 = $airPricePoint[1]['airOption'][2]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$backCabinClass1 = $airPricePoint[1]['airOption'][2]['airBookingInfo'][1]['@attributes']['CabinClass'];


							$segment = array("go" =>
											array("0" =>
													array("marketingcareer"=> "$gomarkettingCarrier",
														"marketingflight"=> "$gomarkettingFN",
															"operatingcareer"=> "$gomarkettingCarrier",
															"operatingflight"=> "$gomarkettingFN",
															"departure"=> "$goDepartureFrom",
															"departureAirport"=> "$godAirport",
															"departureLocation"=> "$godCity , $godCountry",                    
															"departureTime" => "$goDepartureTime",
															"arrival"=> "$goArrivalTo",                   
															"arrivalTime" => "$goArrivalTime",
															"arrivalAirport"=> "$goaAirport",
															"arrivalLocation"=> "$goaCity , $goaCountry",
															"flightduration"=> "$goTravelTime",
															"bookingcode"=> "$goBookingCode",
															"seat"=> "$goSeat",
															"bags" => "$goBags",
															"class" => "$goCabinClass",
															"segmentDetails"=> $airAirSegment[$goSegmentRef]),
													"1" =>
													array("marketingcareer"=> "$gomarkettingCarrier1",
														"marketingflight"=> "$gomarkettingFN1",
															"operatingcareer"=> "$gomarkettingCarrier1",
															"operatingflight"=> "$gomarkettingFN1",
															"departure"=> "$goDepartureFrom1",
															"departureAirport"=> "$godAirport1",
															"departureLocation"=> "$godCity1 , $godCountry1",                    
															"departureTime" => "$goDepartureTime1",
															"arrival"=> "$goArrivalTo1",                   
															"arrivalTime" => "$goArrivalTime1",
															"arrivalAirport"=> "$goaAirport1",
															"arrivalLocation"=> "$goaCity1 , $goaCountry1",
															"flightduration"=> "$goTravelTime1",
															"bookingcode"=> "$goBookingCode1",
															"seat"=> "$goSeat1",
															"bags" => "$goBags1",
															"class" => "$goCabinClass1",
															"segmentDetails"=> $airAirSegment[$goSegmentRef1])																																				
												),
									"back" => array("0" =>
														array("marketingcareer"=> "$backmarkettingCarrier",
																"marketingflight"=> "$backmarkettingFN",
																"operatingcareer"=> "$backmarkettingCarrier",
																"operatingflight"=> "$backmarkettingFN",
																"departure"=> "$backDepartureFrom",
																"departureAirport"=> "$backdAirport",
																"departureLocation"=> "$backdCity , $backdCountry",                    
																"departureTime" => "$backDepartureTime",
																"arrival"=> "$backArrivalTo",                   
																"arrivalTime" => "$backArrivalTime",
																"arrivalAirport"=> "$backaAirport",
																"arrivalLocation"=> "$backaCity , $backaCountry",
																"flightduration"=> "$backTravelTime",
																"bookingcode"=> "$backBookingCode",
																"seat"=> "$backSeat",
																"bags" => "$backBags",
																"class" => "$backCabinClass",
																"segmentDetails"=> $airAirSegment[$backSegmentRef]),
												"1" =>
														array("marketingcareer"=> "$backmarkettingCarrier1",
																"marketingflight"=> "$backmarkettingFN1",
																"operatingcareer"=> "$backmarkettingCarrier1",
																"operatingflight"=> "$backmarkettingFN1",
																"departure"=> "$backDepartureFrom1",
																"departureAirport"=> "$backdAirport1",
																"departureLocation"=> "$backdCity1 , $backdCountry1",                    
																"departureTime" => "$backDepartureTime1",
																"arrival"=> "$backArrivalTo1",                   
																"arrivalTime" => "$backArrivalTime1",
																"arrivalAirport"=> "$backaAirport1",
																"arrivalLocation"=> "$backaCity1 , $backaCountry1",
																"flightduration"=> "$backTravelTime1",
																"bookingcode"=> "$backBookingCode1",
																"seat"=> "$backSeat1",
																"class" => "$backCabinClass1",
																"bags" => "$backBags1",
																"segmentDetails"=> $airAirSegment[$backSegmentRef1])																									

												)
											);

							$basic = array("system" => "Galileo",
									"segment"=> "2",
									"career"=> "$validatingCarrierCode",
									"careerName" => "$CarrieerName",
									"baseprice" => "$BasePrice",
									"taxes" => "$Taxes",
									"price" => "$Exact",
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
									"godeparture"=> "$From",                   
									"godepartureTime" => $goDepartureTime,
									"godepartureDate" => $godpTime,
									"goarrival"=> "$To", 
									"goarrivalTime" => "$goArrivalTime1",
									"goarrivalDate" => "$goarrTime1",                
									"backdeparture"=> "$To",                   
									"backdepartureTime" => $backDepartureTime,
									"backdepartureDate" => $backdpTime,
									"backarrival"=> "$From", 
									"backarrivalTime" => "$backArrivalTime1", 
									"backarrivalDate" => $backarrTime1,  
									"goflightduration"=> "$goTravelTime",
									"backflightduration"=> "$backTravelTime",
									"refundable"=> "$Refundable ",
									"segments" => $segment
								);

							array_push($All,$basic);
							
																		
						}
						
						
					}if(isset($airPricePoint[0]['airOption'][3]) == TRUE && isset($airPricePoint[1]['airOption'][3]) == TRUE){
						if(isset($airPricePoint[0]['airOption'][3]['airBookingInfo']['@attributes']['FareInfoRef']) == True &&
						isset($airPricePoint[1]['airOption'][3]['airBookingInfo']['@attributes']['FareInfoRef']) == True
						){
							//go Leg 1
							
							$goFareInfoRef = $airPricePoint[0]['airOption'][3]['airBookingInfo']['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][3]['airBookingInfo']['@attributes']['SegmentRef'];
							$goBags = $airFareInfo[$goFareInfoRef]['Bags'];						
							$goTravelTime = $airAirSegment[$goSegmentRef]['TravelTime'];												
							$goTravelTimeHm = floor($goTravelTime / 60)."H ".($goTravelTime - ((floor($goTravelTime / 60)) * 60))."Min";


							$goArrivalTo = $airAirSegment[$goSegmentRef]['Destination'];
							$goDepartureFrom = $airAirSegment[$goSegmentRef]['Origin'];

							$goArrivalTime = $airAirSegment[$goSegmentRef]['ArrivalTime'];
							$goDepartureTime = $airAirSegment[$goSegmentRef]['DepartureTime'];

							$godpTime = date("D d M Y", strtotime($goDepartureTime));
							$goarrTime = date("D d M Y", strtotime($goArrivalTime));
							
							$gomarkettingCarrier = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
							$gomkrow = mysqli_fetch_array($gomksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$gomarkettingCarrierName = $gomkrow['name'];		
							}

							// Departure Country
							$godsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
							$godrow = mysqli_fetch_array($godsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$godAirport = $godrow['name'];
								$godCity = $godrow['cityName'];
								$godCountry = $godrow['countryCode'];		
							}

							// Departure Country
							$goasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$goarow = mysqli_fetch_array($goasql,MYSQLI_ASSOC);

							if(!empty($goarow)){
								$goaAirport = $goarow['name'];
								$goaCity = $goarow['cityName'];
								$goaCountry = $goarow['countryCode'];
							}
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][3]['airBookingInfo']['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][3]['airBookingInfo']['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][3]['airBookingInfo']['@attributes']['CabinClass'];

							
							//Back Leg 1
							
							$backFareInfoRef = $airPricePoint[1]['airOption'][3]['airBookingInfo']['@attributes']['FareInfoRef'];						
							$backSegmentRef = $airPricePoint[1]['airOption'][3]['airBookingInfo']['@attributes']['SegmentRef'];
							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];						
							$backTravelTime = $airAirSegment[$backSegmentRef]['TravelTime'];												
							$backTravelTimeHm = floor($backTravelTime / 60)."H ".($backTravelTime - ((floor($backTravelTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backdpTime = date("D d M Y", strtotime($backDepartureTime));
							$backarrTime = date("D d M Y", strtotime($backArrivalTime));
							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backmksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
							$backmkrow = mysqli_fetch_array($backmksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$backmarkettingCarrierName = $backmkrow['name'];		
							}

							// Departure Country
							$backdsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backdrow = mysqli_fetch_array($backdsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$backdAirport = $backdrow['name'];
								$backdCity = $backdrow['cityName'];
								$backdCountry = $backdrow['countryCode'];		
							}

							// Departure Country
							$backasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$backarow = mysqli_fetch_array($backasql,MYSQLI_ASSOC);

							if(!empty($backarow)){
								$backaAirport = $backarow['name'];
								$backaCity = $backarow['cityName'];
								$backaCountry = $backarow['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][3]['airBookingInfo']['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][3]['airBookingInfo']['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][3]['airBookingInfo']['@attributes']['CabinClass'];

						$segment = array("go" =>
									array("0" =>
											array("marketingcareer"=> "$gomarkettingCarrier",
												"marketingflight"=> "$gomarkettingFN",
													"operatingcareer"=> "$gomarkettingCarrier",
													"operatingflight"=> "$gomarkettingFN",
													"departure"=> "$goDepartureFrom",
													"departureAirport"=> "$godAirport",
													"departureLocation"=> "$godCity , $godCountry",                    
													"departureTime" => "$goDepartureTime",
													"arrival"=> "$goArrivalTo",                   
													"arrivalTime" => "$goArrivalTime",
													"arrivalAirport"=> "$goaAirport",
													"arrivalLocation"=> "$goaCity , $goaCountry",
													"flightduration"=> "$goTravelTime",
													"bookingcode"=> "$goBookingCode",
													"Baggage" => "$goBags",
													"seat"=> "$goSeat",
													"segmentDetails"=> $airAirSegment[$goSegmentRef])																										

												),
									"back" => array("0" =>
														array("marketingcareer"=> "$backmarkettingCarrier",
																"marketingflight"=> "$backmarkettingFN",
																"operatingcareer"=> "$backmarkettingCarrier",
																"operatingflight"=> "$backmarkettingFN",
																"departure"=> "$backDepartureFrom",
																"departureAirport"=> "$backdAirport",
																"departureLocation"=> "$backdCity , $backdCountry",                    
																"departureTime" => "$backDepartureTime",
																"arrival"=> "$backArrivalTo",                   
																"arrivalTime" => "$backArrivalTime",
																"arrivalAirport"=> "$backaAirport",
																"arrivalLocation"=> "$backaCity , $backaCountry",
																"flightduration"=> "$backTravelTime",
																"bookingcode"=> "$backBookingCode",
																"Baggage" => "$backBags",
																"seat"=> "$backSeat",
																"segmentDetails"=> $airAirSegment[$backSegmentRef])
														
														

												)
											);

					$basic = array("system" => "Galileo",
									"segment"=> "1",
									"career"=> "$validatingCarrierCode",
									"careerName" => "$CarrieerName",
									"baseprice" => "$BasePrice",
									"taxes" => "$Taxes",
									"price" => "$Exact",
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
									"godeparture"=> "$From",                   
									"godepartureTime" => $goDepartureTime,
									"godepartureDate" => $godpTime,
									"goarrival"=> "$To", 
									"goarrivalTime" => "$goArrivalTime",
									"goarrivalDate" => "$goarrTime",                
									"backdeparture"=> "$To",                   
									"backdepartureTime" => $backDepartureTime,
									"backdepartureDate" => $backdpTime,
									"backarrival"=> "$From", 
									"backarrivalTime" => "$backArrivalTime", 
									"backarrivalDate" => $backarrTime,  
									"goflightduration"=> "$goTravelTime",
									"backflightduration"=> "$backTravelTime",
									
									"seat" => "$goSeat",
									"class" => "$goCabinClass",
									"refundable"=> "$Refundable ",
									"segments" => $segment
								);

							array_push($All,$basic);



						}else if(isset($airPricePoint[0]['airOption'][3]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == True &&
								isset($airPricePoint[1]['airOption'][3]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == True){
							
							//go Leg 1						
							$goFareInfoRef = $airPricePoint[0]['airOption'][3]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][3]['airBookingInfo'][0]['@attributes']['SegmentRef'];
							$goBags = $airFareInfo[$goFareInfoRef]['Bags'];						
							$goTravelTime = $airAirSegment[$goSegmentRef]['TravelTime'];												
							$goTravelTimeHm = floor($goTravelTime / 60)."H ".($goTravelTime - ((floor($goTravelTime / 60)) * 60))."Min";


							$goArrivalTo = $airAirSegment[$goSegmentRef]['Destination'];
							$goDepartureFrom = $airAirSegment[$goSegmentRef]['Origin'];

							$goArrivalTime = $airAirSegment[$goSegmentRef]['ArrivalTime'];
							$goDepartureTime = $airAirSegment[$goSegmentRef]['DepartureTime'];

							$godpTime = date("D d M Y", strtotime($goDepartureTime));
							$goarrTime = date("D d M Y", strtotime($goArrivalTime));
							
							$gomarkettingCarrier = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
							$gomkrow = mysqli_fetch_array($gomksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$gomarkettingCarrierName = $gomkrow['name'];		
							}

							// Departure Country
							$godsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
							$godrow = mysqli_fetch_array($godsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$godAirport = $godrow['name'];
								$godCity = $godrow['cityName'];
								$godCountry = $godrow['countryCode'];		
							}

							// Departure Country
							$goasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$goarow = mysqli_fetch_array($goasql,MYSQLI_ASSOC);

							if(!empty($goarow)){
								$goaAirport = $goarow['name'];
								$goaCity = $goarow['cityName'];
								$goaCountry = $goarow['countryCode'];
							}
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][3]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][3]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][3]['airBookingInfo'][0]['@attributes']['CabinClass'];

							//go Leg 2						
							$goFareInfoRef1 = $airPricePoint[0]['airOption'][3]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$goSegmentRef1 = $airPricePoint[0]['airOption'][3]['airBookingInfo'][1]['@attributes']['SegmentRef'];
							$goBags1 = $airFareInfo[$goFareInfoRef1]['Bags'];						
							$goTravelTime1 = $airAirSegment[$goSegmentRef1]['TravelTime'];												
							$goTravelTimeHm1 = floor($goTravelTime1 / 60)."H ".($goTravelTime1 - ((floor($goTravelTime1 / 60)) * 60))."Min";


							$goArrivalTo1 = $airAirSegment[$goSegmentRef1]['Destination'];
							$goDepartureFrom1 = $airAirSegment[$goSegmentRef1]['Origin'];

							$goArrivalTime1 = $airAirSegment[$goSegmentRef1]['ArrivalTime'];
							$goDepartureTime1 = $airAirSegment[$goSegmentRef1]['DepartureTime'];

							$godpTime1 = date("D d M Y", strtotime($goDepartureTime1));
							$goarrTime1 = date("D d M Y", strtotime($goArrivalTime1));
							
							$gomarkettingCarrier1 = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN1 = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier1' ");
							$gomkrow1 = mysqli_fetch_array($gomksql1,MYSQLI_ASSOC);

							if(!empty($gomkrow1)){
								$gomarkettingCarrierName1 = $gomkrow1['name'];		
							}

							// Departure Country
							$godsql1 = mysqli_query($conn,"$Airportsql code='$goDepartureFrom1' ");
							$godrow1 = mysqli_fetch_array($godsql1,MYSQLI_ASSOC);

							if(!empty($godrow1)){
								$godAirport1 = $godrow1['name'];
								$godCity1 = $godrow1['cityName'];
								$godCountry1 = $godrow1['countryCode'];		
							}

							// Departure Country
							$goasql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo1' ");
							$goarow1 = mysqli_fetch_array($goasql1,MYSQLI_ASSOC);

							if(!empty($goarow1)){
								$goaAirport1 = $goarow1['name'];
								$goaCity1 = $goarow1['cityName'];
								$goaCountry1 = $goarow1['countryCode'];
							}
							
							
							$goBookingCode1 = $airPricePoint[0]['airOption'][3]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$goSeat1 = $airPricePoint[0]['airOption'][3]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$goCabinClass1 = $airPricePoint[0]['airOption'][3]['airBookingInfo'][1]['@attributes']['CabinClass'];

							
							//Back Leg 1
							
							$backFareInfoRef = $airPricePoint[1]['airOption'][3]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$backSegmentRef = $airPricePoint[1]['airOption'][3]['airBookingInfo'][0]['@attributes']['SegmentRef'];
							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];						
							$backTravelTime = $airAirSegment[$backSegmentRef]['TravelTime'];												
							$backTravelTimeHm = floor($backTravelTime / 60)."H ".($backTravelTime - ((floor($backTravelTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backdpTime = date("D d M Y", strtotime($backDepartureTime));
							$backarrTime = date("D d M Y", strtotime($backArrivalTime));
							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backmksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
							$backmkrow = mysqli_fetch_array($backmksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$backmarkettingCarrierName = $backmkrow['name'];		
							}

							// Departure Country
							$backdsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backdrow = mysqli_fetch_array($backdsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$backdAirport = $backdrow['name'];
								$backdCity = $backdrow['cityName'];
								$backdCountry = $backdrow['countryCode'];		
							}

							// Departure Country
							$backasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$backarow = mysqli_fetch_array($backasql,MYSQLI_ASSOC);

							if(!empty($backarow)){
								$backaAirport = $backarow['name'];
								$backaCity = $backarow['cityName'];
								$backaCountry = $backarow['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][3]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][3]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][3]['airBookingInfo'][0]['@attributes']['CabinClass'];

							//Back Leg 2
							
							$backFareInfoRef1 = $airPricePoint[1]['airOption'][3]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$backSegmentRef1 = $airPricePoint[1]['airOption'][3]['airBookingInfo'][1]['@attributes']['SegmentRef'];
							$backBags1 = $airFareInfo[$backFareInfoRef1]['Bags'];						
							$backTravelTime1 = $airAirSegment[$backSegmentRef1]['TravelTime'];												
							$backTravelTimeHm1 = floor($backTravelTime1 / 60)."H ".($backTravelTime1 - ((floor($backTravelTime1 / 60)) * 60))."Min";


							$backArrivalTo1 = $airAirSegment[$backSegmentRef1]['Destination'];
							$backDepartureFrom1 = $airAirSegment[$backSegmentRef1]['Origin'];

							$backArrivalTime1 = $airAirSegment[$backSegmentRef1]['ArrivalTime'];
							$backDepartureTime1 = $airAirSegment[$backSegmentRef1]['DepartureTime'];

							$backdpTime1 = date("D d M Y", strtotime($backDepartureTime1));
							$backarrTime1 = date("D d M Y", strtotime($backArrivalTime1));
							
							$backmarkettingCarrier1 = $airAirSegment[$backSegmentRef1]['Carrier'];
							$backmarkettingFN1 = $airAirSegment[$backSegmentRef1]['FlightNumber'];

							$backmksql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier1' ");
							$backmkrow1 = mysqli_fetch_array($backmksql1,MYSQLI_ASSOC);

							if(!empty($gomkrow1)){
								$backmarkettingCarrierName1 = $backmkrow1['name'];		
							}

							// Departure Country
							$backdsql1 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom1' ");
							$backdrow1 = mysqli_fetch_array($backdsql1,MYSQLI_ASSOC);

							if(!empty($godrow1)){
								$backdAirport1 = $backdrow1['name'];
								$backdCity1 = $backdrow1['cityName'];
								$backdCountry1 = $backdrow1['countryCode'];		
							}

							// Departure Country
							$backasql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo1' ");
							$backarow1 = mysqli_fetch_array($backasql1,MYSQLI_ASSOC);

							if(!empty($backarow1)){
								$backaAirport1 = $backarow1['name'];
								$backaCity1 = $backarow1['cityName'];
								$backaCountry1 = $backarow1['countryCode'];
							}
							
							
							$backBookingCode1 = $airPricePoint[1]['airOption'][3]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$backSeat1 = $airPricePoint[1]['airOption'][3]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$backCabinClass1 = $airPricePoint[1]['airOption'][3]['airBookingInfo'][1]['@attributes']['CabinClass'];


							$segment = array("go" =>
											array("0" =>
													array("marketingcareer"=> "$gomarkettingCarrier",
														"marketingflight"=> "$gomarkettingFN",
															"operatingcareer"=> "$gomarkettingCarrier",
															"operatingflight"=> "$gomarkettingFN",
															"departure"=> "$goDepartureFrom",
															"departureAirport"=> "$godAirport",
															"departureLocation"=> "$godCity , $godCountry",                    
															"departureTime" => "$goDepartureTime",
															"arrival"=> "$goArrivalTo",                   
															"arrivalTime" => "$goArrivalTime",
															"arrivalAirport"=> "$goaAirport",
															"arrivalLocation"=> "$goaCity , $goaCountry",
															"flightduration"=> "$goTravelTime",
															"bookingcode"=> "$goBookingCode",
															"seat"=> "$goSeat",
															"bags" => "$goBags",
															"class" => "$goCabinClass",
															"segmentDetails"=> $airAirSegment[$goSegmentRef]),
													"1" =>
													array("marketingcareer"=> "$gomarkettingCarrier1",
														"marketingflight"=> "$gomarkettingFN1",
															"operatingcareer"=> "$gomarkettingCarrier1",
															"operatingflight"=> "$gomarkettingFN1",
															"departure"=> "$goDepartureFrom1",
															"departureAirport"=> "$godAirport1",
															"departureLocation"=> "$godCity1 , $godCountry1",                    
															"departureTime" => "$goDepartureTime1",
															"arrival"=> "$goArrivalTo1",                   
															"arrivalTime" => "$goArrivalTime1",
															"arrivalAirport"=> "$goaAirport1",
															"arrivalLocation"=> "$goaCity1 , $goaCountry1",
															"flightduration"=> "$goTravelTime1",
															"bookingcode"=> "$goBookingCode1",
															"seat"=> "$goSeat1",
															"bags" => "$goBags1",
															"class" => "$goCabinClass1",
															"segmentDetails"=> $airAirSegment[$goSegmentRef1])																																				
												),
									"back" => array("0" =>
														array("marketingcareer"=> "$backmarkettingCarrier",
																"marketingflight"=> "$backmarkettingFN",
																"operatingcareer"=> "$backmarkettingCarrier",
																"operatingflight"=> "$backmarkettingFN",
																"departure"=> "$backDepartureFrom",
																"departureAirport"=> "$backdAirport",
																"departureLocation"=> "$backdCity , $backdCountry",                    
																"departureTime" => "$backDepartureTime",
																"arrival"=> "$backArrivalTo",                   
																"arrivalTime" => "$backArrivalTime",
																"arrivalAirport"=> "$backaAirport",
																"arrivalLocation"=> "$backaCity , $backaCountry",
																"flightduration"=> "$backTravelTime",
																"bookingcode"=> "$backBookingCode",
																"seat"=> "$backSeat",
																"bags" => "$backBags",
																"class" => "$backCabinClass",
																"segmentDetails"=> $airAirSegment[$backSegmentRef]),
												"1" =>
														array("marketingcareer"=> "$backmarkettingCarrier1",
																"marketingflight"=> "$backmarkettingFN1",
																"operatingcareer"=> "$backmarkettingCarrier1",
																"operatingflight"=> "$backmarkettingFN1",
																"departure"=> "$backDepartureFrom1",
																"departureAirport"=> "$backdAirport1",
																"departureLocation"=> "$backdCity1 , $backdCountry1",                    
																"departureTime" => "$backDepartureTime1",
																"arrival"=> "$backArrivalTo1",                   
																"arrivalTime" => "$backArrivalTime1",
																"arrivalAirport"=> "$backaAirport1",
																"arrivalLocation"=> "$backaCity1 , $backaCountry1",
																"flightduration"=> "$backTravelTime1",
																"bookingcode"=> "$backBookingCode1",
																"seat"=> "$backSeat1",
																"class" => "$backCabinClass1",
																"bags" => "$backBags1",
																"segmentDetails"=> $airAirSegment[$backSegmentRef1])																									

												)
											);

							$basic = array("system" => "Galileo",
									"segment"=> "2",
									"career"=> "$validatingCarrierCode",
									"careerName" => "$CarrieerName",
									"baseprice" => "$BasePrice",
									"taxes" => "$Taxes",
									"price" => "$Exact",
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
									"godeparture"=> "$From",                   
									"godepartureTime" => $goDepartureTime,
									"godepartureDate" => $godpTime,
									"goarrival"=> "$To", 
									"goarrivalTime" => "$goArrivalTime1",
									"goarrivalDate" => "$goarrTime1",                
									"backdeparture"=> "$To",                   
									"backdepartureTime" => $backDepartureTime,
									"backdepartureDate" => $backdpTime,
									"backarrival"=> "$From", 
									"backarrivalTime" => "$backArrivalTime1", 
									"backarrivalDate" => $backarrTime1,  
									"goflightduration"=> "$goTravelTime",
									"backflightduration"=> "$backTravelTime",
									"refundable"=> "$Refundable ",
									"segments" => $segment
								);

							array_push($All,$basic);
							
																		
						}
						
						
					}if(isset($airPricePoint[0]['airOption'][4]) == TRUE && isset($airPricePoint[1]['airOption'][4]) == TRUE){
						if(isset($airPricePoint[0]['airOption'][4]['airBookingInfo']['@attributes']['FareInfoRef']) == True &&
						isset($airPricePoint[1]['airOption'][4]['airBookingInfo']['@attributes']['FareInfoRef']) == True
						){
							//go Leg 1
							
							$goFareInfoRef = $airPricePoint[0]['airOption'][4]['airBookingInfo']['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][4]['airBookingInfo']['@attributes']['SegmentRef'];
							$goBags = $airFareInfo[$goFareInfoRef]['Bags'];						
							$goTravelTime = $airAirSegment[$goSegmentRef]['TravelTime'];												
							$goTravelTimeHm = floor($goTravelTime / 60)."H ".($goTravelTime - ((floor($goTravelTime / 60)) * 60))."Min";


							$goArrivalTo = $airAirSegment[$goSegmentRef]['Destination'];
							$goDepartureFrom = $airAirSegment[$goSegmentRef]['Origin'];

							$goArrivalTime = $airAirSegment[$goSegmentRef]['ArrivalTime'];
							$goDepartureTime = $airAirSegment[$goSegmentRef]['DepartureTime'];

							$godpTime = date("D d M Y", strtotime($goDepartureTime));
							$goarrTime = date("D d M Y", strtotime($goArrivalTime));
							
							$gomarkettingCarrier = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
							$gomkrow = mysqli_fetch_array($gomksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$gomarkettingCarrierName = $gomkrow['name'];		
							}

							// Departure Country
							$godsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
							$godrow = mysqli_fetch_array($godsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$godAirport = $godrow['name'];
								$godCity = $godrow['cityName'];
								$godCountry = $godrow['countryCode'];		
							}

							// Departure Country
							$goasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$goarow = mysqli_fetch_array($goasql,MYSQLI_ASSOC);

							if(!empty($goarow)){
								$goaAirport = $goarow['name'];
								$goaCity = $goarow['cityName'];
								$goaCountry = $goarow['countryCode'];
							}
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][4]['airBookingInfo']['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][4]['airBookingInfo']['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][4]['airBookingInfo']['@attributes']['CabinClass'];

							
							//Back Leg 1
							
							$backFareInfoRef = $airPricePoint[1]['airOption'][4]['airBookingInfo']['@attributes']['FareInfoRef'];						
							$backSegmentRef = $airPricePoint[1]['airOption'][4]['airBookingInfo']['@attributes']['SegmentRef'];
							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];						
							$backTravelTime = $airAirSegment[$backSegmentRef]['TravelTime'];												
							$backTravelTimeHm = floor($backTravelTime / 60)."H ".($backTravelTime - ((floor($backTravelTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backdpTime = date("D d M Y", strtotime($backDepartureTime));
							$backarrTime = date("D d M Y", strtotime($backArrivalTime));
							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backmksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
							$backmkrow = mysqli_fetch_array($backmksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$backmarkettingCarrierName = $backmkrow['name'];		
							}

							// Departure Country
							$backdsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backdrow = mysqli_fetch_array($backdsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$backdAirport = $backdrow['name'];
								$backdCity = $backdrow['cityName'];
								$backdCountry = $backdrow['countryCode'];		
							}

							// Departure Country
							$backasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$backarow = mysqli_fetch_array($backasql,MYSQLI_ASSOC);

							if(!empty($backarow)){
								$backaAirport = $backarow['name'];
								$backaCity = $backarow['cityName'];
								$backaCountry = $backarow['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][4]['airBookingInfo']['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][4]['airBookingInfo']['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][4]['airBookingInfo']['@attributes']['CabinClass'];

						$segment = array("go" =>
									array("0" =>
											array("marketingcareer"=> "$gomarkettingCarrier",
												"marketingflight"=> "$gomarkettingFN",
													"operatingcareer"=> "$gomarkettingCarrier",
													"operatingflight"=> "$gomarkettingFN",
													"departure"=> "$goDepartureFrom",
													"departureAirport"=> "$godAirport",
													"departureLocation"=> "$godCity , $godCountry",                    
													"departureTime" => "$goDepartureTime",
													"arrival"=> "$goArrivalTo",                   
													"arrivalTime" => "$goArrivalTime",
													"arrivalAirport"=> "$goaAirport",
													"arrivalLocation"=> "$goaCity , $goaCountry",
													"flightduration"=> "$goTravelTime",
													"bookingcode"=> "$goBookingCode",
													"Baggage" => "$goBags",
													"seat"=> "$goSeat",
													"segmentDetails"=> $airAirSegment[$goSegmentRef])																										

												),
									"back" => array("0" =>
														array("marketingcareer"=> "$backmarkettingCarrier",
																"marketingflight"=> "$backmarkettingFN",
																"operatingcareer"=> "$backmarkettingCarrier",
																"operatingflight"=> "$backmarkettingFN",
																"departure"=> "$backDepartureFrom",
																"departureAirport"=> "$backdAirport",
																"departureLocation"=> "$backdCity , $backdCountry",                    
																"departureTime" => "$backDepartureTime",
																"arrival"=> "$backArrivalTo",                   
																"arrivalTime" => "$backArrivalTime",
																"arrivalAirport"=> "$backaAirport",
																"arrivalLocation"=> "$backaCity , $backaCountry",
																"flightduration"=> "$backTravelTime",
																"bookingcode"=> "$backBookingCode",
																"Baggage" => "$backBags",
																"seat"=> "$backSeat",
																"segmentDetails"=> $airAirSegment[$backSegmentRef])
														
														

												)
											);

					$basic = array("system" => "Galileo",
									"segment"=> "1",
									"career"=> "$validatingCarrierCode",
									"careerName" => "$CarrieerName",
									"baseprice" => "$BasePrice",
									"taxes" => "$Taxes",
									"price" => "$Exact",
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
									"godeparture"=> "$From",                   
									"godepartureTime" => $goDepartureTime,
									"godepartureDate" => $godpTime,
									"goarrival"=> "$To", 
									"goarrivalTime" => "$goArrivalTime",
									"goarrivalDate" => "$goarrTime",                
									"backdeparture"=> "$To",                   
									"backdepartureTime" => $backDepartureTime,
									"backdepartureDate" => $backdpTime,
									"backarrival"=> "$From", 
									"backarrivalTime" => "$backArrivalTime", 
									"backarrivalDate" => $backarrTime,  
									"goflightduration"=> "$goTravelTime",
									"backflightduration"=> "$backTravelTime",
									
									"seat" => "$goSeat",
									"class" => "$goCabinClass",
									"refundable"=> "$Refundable ",
									"segments" => $segment
								);

							array_push($All,$basic);



						}else if(isset($airPricePoint[0]['airOption'][4]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == True &&
								isset($airPricePoint[1]['airOption'][4]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == True){
							
							//go Leg 1						
							$goFareInfoRef = $airPricePoint[0]['airOption'][4]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][4]['airBookingInfo'][0]['@attributes']['SegmentRef'];
							$goBags = $airFareInfo[$goFareInfoRef]['Bags'];						
							$goTravelTime = $airAirSegment[$goSegmentRef]['TravelTime'];												
							$goTravelTimeHm = floor($goTravelTime / 60)."H ".($goTravelTime - ((floor($goTravelTime / 60)) * 60))."Min";


							$goArrivalTo = $airAirSegment[$goSegmentRef]['Destination'];
							$goDepartureFrom = $airAirSegment[$goSegmentRef]['Origin'];

							$goArrivalTime = $airAirSegment[$goSegmentRef]['ArrivalTime'];
							$goDepartureTime = $airAirSegment[$goSegmentRef]['DepartureTime'];

							$godpTime = date("D d M Y", strtotime($goDepartureTime));
							$goarrTime = date("D d M Y", strtotime($goArrivalTime));
							
							$gomarkettingCarrier = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
							$gomkrow = mysqli_fetch_array($gomksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$gomarkettingCarrierName = $gomkrow['name'];		
							}

							// Departure Country
							$godsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
							$godrow = mysqli_fetch_array($godsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$godAirport = $godrow['name'];
								$godCity = $godrow['cityName'];
								$godCountry = $godrow['countryCode'];		
							}

							// Departure Country
							$goasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$goarow = mysqli_fetch_array($goasql,MYSQLI_ASSOC);

							if(!empty($goarow)){
								$goaAirport = $goarow['name'];
								$goaCity = $goarow['cityName'];
								$goaCountry = $goarow['countryCode'];
							}
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][4]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][4]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][4]['airBookingInfo'][0]['@attributes']['CabinClass'];

							//go Leg 2						
							$goFareInfoRef1 = $airPricePoint[0]['airOption'][4]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$goSegmentRef1 = $airPricePoint[0]['airOption'][4]['airBookingInfo'][1]['@attributes']['SegmentRef'];
							$goBags1 = $airFareInfo[$goFareInfoRef1]['Bags'];						
							$goTravelTime1 = $airAirSegment[$goSegmentRef1]['TravelTime'];												
							$goTravelTimeHm1 = floor($goTravelTime1 / 60)."H ".($goTravelTime1 - ((floor($goTravelTime1 / 60)) * 60))."Min";


							$goArrivalTo1 = $airAirSegment[$goSegmentRef1]['Destination'];
							$goDepartureFrom1 = $airAirSegment[$goSegmentRef1]['Origin'];

							$goArrivalTime1 = $airAirSegment[$goSegmentRef1]['ArrivalTime'];
							$goDepartureTime1 = $airAirSegment[$goSegmentRef1]['DepartureTime'];

							$godpTime1 = date("D d M Y", strtotime($goDepartureTime1));
							$goarrTime1 = date("D d M Y", strtotime($goArrivalTime1));
							
							$gomarkettingCarrier1 = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN1 = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier1' ");
							$gomkrow1 = mysqli_fetch_array($gomksql1,MYSQLI_ASSOC);

							if(!empty($gomkrow1)){
								$gomarkettingCarrierName1 = $gomkrow1['name'];		
							}

							// Departure Country
							$godsql1 = mysqli_query($conn,"$Airportsql code='$goDepartureFrom1' ");
							$godrow1 = mysqli_fetch_array($godsql1,MYSQLI_ASSOC);

							if(!empty($godrow1)){
								$godAirport1 = $godrow1['name'];
								$godCity1 = $godrow1['cityName'];
								$godCountry1 = $godrow1['countryCode'];		
							}

							// Departure Country
							$goasql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo1' ");
							$goarow1 = mysqli_fetch_array($goasql1,MYSQLI_ASSOC);

							if(!empty($goarow1)){
								$goaAirport1 = $goarow1['name'];
								$goaCity1 = $goarow1['cityName'];
								$goaCountry1 = $goarow1['countryCode'];
							}
							
							
							$goBookingCode1 = $airPricePoint[0]['airOption'][4]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$goSeat1 = $airPricePoint[0]['airOption'][4]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$goCabinClass1 = $airPricePoint[0]['airOption'][4]['airBookingInfo'][1]['@attributes']['CabinClass'];

							
							//Back Leg 1
							
							$backFareInfoRef = $airPricePoint[1]['airOption'][4]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$backSegmentRef = $airPricePoint[1]['airOption'][4]['airBookingInfo'][0]['@attributes']['SegmentRef'];
							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];						
							$backTravelTime = $airAirSegment[$backSegmentRef]['TravelTime'];												
							$backTravelTimeHm = floor($backTravelTime / 60)."H ".($backTravelTime - ((floor($backTravelTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backdpTime = date("D d M Y", strtotime($backDepartureTime));
							$backarrTime = date("D d M Y", strtotime($backArrivalTime));
							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backmksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
							$backmkrow = mysqli_fetch_array($backmksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$backmarkettingCarrierName = $backmkrow['name'];		
							}

							// Departure Country
							$backdsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backdrow = mysqli_fetch_array($backdsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$backdAirport = $backdrow['name'];
								$backdCity = $backdrow['cityName'];
								$backdCountry = $backdrow['countryCode'];		
							}

							// Departure Country
							$backasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$backarow = mysqli_fetch_array($backasql,MYSQLI_ASSOC);

							if(!empty($backarow)){
								$backaAirport = $backarow['name'];
								$backaCity = $backarow['cityName'];
								$backaCountry = $backarow['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][4]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][4]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][4]['airBookingInfo'][0]['@attributes']['CabinClass'];

							//Back Leg 2
							
							$backFareInfoRef1 = $airPricePoint[1]['airOption'][4]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$backSegmentRef1 = $airPricePoint[1]['airOption'][4]['airBookingInfo'][1]['@attributes']['SegmentRef'];
							$backBags1 = $airFareInfo[$backFareInfoRef1]['Bags'];						
							$backTravelTime1 = $airAirSegment[$backSegmentRef1]['TravelTime'];												
							$backTravelTimeHm1 = floor($backTravelTime1 / 60)."H ".($backTravelTime1 - ((floor($backTravelTime1 / 60)) * 60))."Min";


							$backArrivalTo1 = $airAirSegment[$backSegmentRef1]['Destination'];
							$backDepartureFrom1 = $airAirSegment[$backSegmentRef1]['Origin'];

							$backArrivalTime1 = $airAirSegment[$backSegmentRef1]['ArrivalTime'];
							$backDepartureTime1 = $airAirSegment[$backSegmentRef1]['DepartureTime'];

							$backdpTime1 = date("D d M Y", strtotime($backDepartureTime1));
							$backarrTime1 = date("D d M Y", strtotime($backArrivalTime1));
							
							$backmarkettingCarrier1 = $airAirSegment[$backSegmentRef1]['Carrier'];
							$backmarkettingFN1 = $airAirSegment[$backSegmentRef1]['FlightNumber'];

							$backmksql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier1' ");
							$backmkrow1 = mysqli_fetch_array($backmksql1,MYSQLI_ASSOC);

							if(!empty($gomkrow1)){
								$backmarkettingCarrierName1 = $backmkrow1['name'];		
							}

							// Departure Country
							$backdsql1 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom1' ");
							$backdrow1 = mysqli_fetch_array($backdsql1,MYSQLI_ASSOC);

							if(!empty($godrow1)){
								$backdAirport1 = $backdrow1['name'];
								$backdCity1 = $backdrow1['cityName'];
								$backdCountry1 = $backdrow1['countryCode'];		
							}

							// Departure Country
							$backasql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo1' ");
							$backarow1 = mysqli_fetch_array($backasql1,MYSQLI_ASSOC);

							if(!empty($backarow1)){
								$backaAirport1 = $backarow1['name'];
								$backaCity1 = $backarow1['cityName'];
								$backaCountry1 = $backarow1['countryCode'];
							}
							
							
							$backBookingCode1 = $airPricePoint[1]['airOption'][4]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$backSeat1 = $airPricePoint[1]['airOption'][4]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$backCabinClass1 = $airPricePoint[1]['airOption'][4]['airBookingInfo'][1]['@attributes']['CabinClass'];


							$segment = array("go" =>
											array("0" =>
													array("marketingcareer"=> "$gomarkettingCarrier",
														"marketingflight"=> "$gomarkettingFN",
															"operatingcareer"=> "$gomarkettingCarrier",
															"operatingflight"=> "$gomarkettingFN",
															"departure"=> "$goDepartureFrom",
															"departureAirport"=> "$godAirport",
															"departureLocation"=> "$godCity , $godCountry",                    
															"departureTime" => "$goDepartureTime",
															"arrival"=> "$goArrivalTo",                   
															"arrivalTime" => "$goArrivalTime",
															"arrivalAirport"=> "$goaAirport",
															"arrivalLocation"=> "$goaCity , $goaCountry",
															"flightduration"=> "$goTravelTime",
															"bookingcode"=> "$goBookingCode",
															"seat"=> "$goSeat",
															"bags" => "$goBags",
															"class" => "$goCabinClass",
															"segmentDetails"=> $airAirSegment[$goSegmentRef]),
													"1" =>
													array("marketingcareer"=> "$gomarkettingCarrier1",
														"marketingflight"=> "$gomarkettingFN1",
															"operatingcareer"=> "$gomarkettingCarrier1",
															"operatingflight"=> "$gomarkettingFN1",
															"departure"=> "$goDepartureFrom1",
															"departureAirport"=> "$godAirport1",
															"departureLocation"=> "$godCity1 , $godCountry1",                    
															"departureTime" => "$goDepartureTime1",
															"arrival"=> "$goArrivalTo1",                   
															"arrivalTime" => "$goArrivalTime1",
															"arrivalAirport"=> "$goaAirport1",
															"arrivalLocation"=> "$goaCity1 , $goaCountry1",
															"flightduration"=> "$goTravelTime1",
															"bookingcode"=> "$goBookingCode1",
															"seat"=> "$goSeat1",
															"bags" => "$goBags1",
															"class" => "$goCabinClass1",
															"segmentDetails"=> $airAirSegment[$goSegmentRef1])																																				
												),
									"back" => array("0" =>
														array("marketingcareer"=> "$backmarkettingCarrier",
																"marketingflight"=> "$backmarkettingFN",
																"operatingcareer"=> "$backmarkettingCarrier",
																"operatingflight"=> "$backmarkettingFN",
																"departure"=> "$backDepartureFrom",
																"departureAirport"=> "$backdAirport",
																"departureLocation"=> "$backdCity , $backdCountry",                    
																"departureTime" => "$backDepartureTime",
																"arrival"=> "$backArrivalTo",                   
																"arrivalTime" => "$backArrivalTime",
																"arrivalAirport"=> "$backaAirport",
																"arrivalLocation"=> "$backaCity , $backaCountry",
																"flightduration"=> "$backTravelTime",
																"bookingcode"=> "$backBookingCode",
																"seat"=> "$backSeat",
																"bags" => "$backBags",
																"class" => "$backCabinClass",
																"segmentDetails"=> $airAirSegment[$backSegmentRef]),
												"1" =>
														array("marketingcareer"=> "$backmarkettingCarrier1",
																"marketingflight"=> "$backmarkettingFN1",
																"operatingcareer"=> "$backmarkettingCarrier1",
																"operatingflight"=> "$backmarkettingFN1",
																"departure"=> "$backDepartureFrom1",
																"departureAirport"=> "$backdAirport1",
																"departureLocation"=> "$backdCity1 , $backdCountry1",                    
																"departureTime" => "$backDepartureTime1",
																"arrival"=> "$backArrivalTo1",                   
																"arrivalTime" => "$backArrivalTime1",
																"arrivalAirport"=> "$backaAirport1",
																"arrivalLocation"=> "$backaCity1 , $backaCountry1",
																"flightduration"=> "$backTravelTime1",
																"bookingcode"=> "$backBookingCode1",
																"seat"=> "$backSeat1",
																"class" => "$backCabinClass1",
																"bags" => "$backBags1",
																"segmentDetails"=> $airAirSegment[$backSegmentRef1])																									

												)
											);

							$basic = array("system" => "Galileo",
									"segment"=> "2",
									"career"=> "$validatingCarrierCode",
									"careerName" => "$CarrieerName",
									"baseprice" => "$BasePrice",
									"taxes" => "$Taxes",
									"price" => "$Exact",
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
									"godeparture"=> "$From",                   
									"godepartureTime" => $goDepartureTime,
									"godepartureDate" => $godpTime,
									"goarrival"=> "$To", 
									"goarrivalTime" => "$goArrivalTime1",
									"goarrivalDate" => "$goarrTime1",                
									"backdeparture"=> "$To",                   
									"backdepartureTime" => $backDepartureTime,
									"backdepartureDate" => $backdpTime,
									"backarrival"=> "$From", 
									"backarrivalTime" => "$backArrivalTime1", 
									"backarrivalDate" => $backarrTime1,  
									"goflightduration"=> "$goTravelTime",
									"backflightduration"=> "$backTravelTime",
									"refundable"=> "$Refundable ",
									"segments" => $segment
								);

							array_push($All,$basic);
							
																		
						}
						
					}if(isset($airPricePoint[0]['airOption'][5]) == TRUE && isset($airPricePoint[0]['airOption'][5]) == TRUE){
						if(isset($airPricePoint[0]['airOption'][5]['airBookingInfo']['@attributes']['FareInfoRef']) == True &&
						isset($airPricePoint[1]['airOption'][5]['airBookingInfo']['@attributes']['FareInfoRef']) == True
						){
							//go Leg 1
							
							$goFareInfoRef = $airPricePoint[0]['airOption'][5]['airBookingInfo']['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][5]['airBookingInfo']['@attributes']['SegmentRef'];
							$goBags = $airFareInfo[$goFareInfoRef]['Bags'];						
							$goTravelTime = $airAirSegment[$goSegmentRef]['TravelTime'];												
							$goTravelTimeHm = floor($goTravelTime / 60)."H ".($goTravelTime - ((floor($goTravelTime / 60)) * 60))."Min";


							$goArrivalTo = $airAirSegment[$goSegmentRef]['Destination'];
							$goDepartureFrom = $airAirSegment[$goSegmentRef]['Origin'];

							$goArrivalTime = $airAirSegment[$goSegmentRef]['ArrivalTime'];
							$goDepartureTime = $airAirSegment[$goSegmentRef]['DepartureTime'];

							$godpTime = date("D d M Y", strtotime($goDepartureTime));
							$goarrTime = date("D d M Y", strtotime($goArrivalTime));
							
							$gomarkettingCarrier = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
							$gomkrow = mysqli_fetch_array($gomksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$gomarkettingCarrierName = $gomkrow['name'];		
							}

							// Departure Country
							$godsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
							$godrow = mysqli_fetch_array($godsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$godAirport = $godrow['name'];
								$godCity = $godrow['cityName'];
								$godCountry = $godrow['countryCode'];		
							}

							// Departure Country
							$goasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$goarow = mysqli_fetch_array($goasql,MYSQLI_ASSOC);

							if(!empty($goarow)){
								$goaAirport = $goarow['name'];
								$goaCity = $goarow['cityName'];
								$goaCountry = $goarow['countryCode'];
							}
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][5]['airBookingInfo']['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][5]['airBookingInfo']['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][5]['airBookingInfo']['@attributes']['CabinClass'];

							
							//Back Leg 1
							
							$backFareInfoRef = $airPricePoint[1]['airOption'][5]['airBookingInfo']['@attributes']['FareInfoRef'];						
							$backSegmentRef = $airPricePoint[1]['airOption'][5]['airBookingInfo']['@attributes']['SegmentRef'];
							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];						
							$backTravelTime = $airAirSegment[$backSegmentRef]['TravelTime'];												
							$backTravelTimeHm = floor($backTravelTime / 60)."H ".($backTravelTime - ((floor($backTravelTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backdpTime = date("D d M Y", strtotime($backDepartureTime));
							$backarrTime = date("D d M Y", strtotime($backArrivalTime));
							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backmksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
							$backmkrow = mysqli_fetch_array($backmksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$backmarkettingCarrierName = $backmkrow['name'];		
							}

							// Departure Country
							$backdsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backdrow = mysqli_fetch_array($backdsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$backdAirport = $backdrow['name'];
								$backdCity = $backdrow['cityName'];
								$backdCountry = $backdrow['countryCode'];		
							}

							// Departure Country
							$backasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$backarow = mysqli_fetch_array($backasql,MYSQLI_ASSOC);

							if(!empty($backarow)){
								$backaAirport = $backarow['name'];
								$backaCity = $backarow['cityName'];
								$backaCountry = $backarow['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][5]['airBookingInfo']['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][5]['airBookingInfo']['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][5]['airBookingInfo']['@attributes']['CabinClass'];

						$segment = array("go" =>
									array("0" =>
											array("marketingcareer"=> "$gomarkettingCarrier",
												"marketingflight"=> "$gomarkettingFN",
													"operatingcareer"=> "$gomarkettingCarrier",
													"operatingflight"=> "$gomarkettingFN",
													"departure"=> "$goDepartureFrom",
													"departureAirport"=> "$godAirport",
													"departureLocation"=> "$godCity , $godCountry",                    
													"departureTime" => "$goDepartureTime",
													"arrival"=> "$goArrivalTo",                   
													"arrivalTime" => "$goArrivalTime",
													"arrivalAirport"=> "$goaAirport",
													"arrivalLocation"=> "$goaCity , $goaCountry",
													"flightduration"=> "$goTravelTime",
													"bookingcode"=> "$goBookingCode",
													"Baggage" => "$goBags",
													"seat"=> "$goSeat",
													"segmentDetails"=> $airAirSegment[$goSegmentRef])																										

												),
									"back" => array("0" =>
														array("marketingcareer"=> "$backmarkettingCarrier",
																"marketingflight"=> "$backmarkettingFN",
																"operatingcareer"=> "$backmarkettingCarrier",
																"operatingflight"=> "$backmarkettingFN",
																"departure"=> "$backDepartureFrom",
																"departureAirport"=> "$backdAirport",
																"departureLocation"=> "$backdCity , $backdCountry",                    
																"departureTime" => "$backDepartureTime",
																"arrival"=> "$backArrivalTo",                   
																"arrivalTime" => "$backArrivalTime",
																"arrivalAirport"=> "$backaAirport",
																"arrivalLocation"=> "$backaCity , $backaCountry",
																"flightduration"=> "$backTravelTime",
																"bookingcode"=> "$backBookingCode",
																"Baggage" => "$backBags",
																"seat"=> "$backSeat",
																"segmentDetails"=> $airAirSegment[$backSegmentRef])
														
														

												)
											);

					$basic = array("system" => "Galileo",
									"segment"=> "1",
									"career"=> "$validatingCarrierCode",
									"careerName" => "$CarrieerName",
									"baseprice" => "$BasePrice",
									"taxes" => "$Taxes",
									"price" => "$Exact",
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
									"godeparture"=> "$From",                   
									"godepartureTime" => $goDepartureTime,
									"godepartureDate" => $godpTime,
									"goarrival"=> "$To", 
									"goarrivalTime" => "$goArrivalTime",
									"goarrivalDate" => "$goarrTime",                
									"backdeparture"=> "$To",                   
									"backdepartureTime" => $backDepartureTime,
									"backdepartureDate" => $backdpTime,
									"backarrival"=> "$From", 
									"backarrivalTime" => "$backArrivalTime", 
									"backarrivalDate" => $backarrTime,  
									"goflightduration"=> "$goTravelTime",
									"backflightduration"=> "$backTravelTime",									
									"seat" => "$goSeat",
									"class" => "$goCabinClass",
									"refundable"=> "$Refundable ",
									"segments" => $segment
								);

							array_push($All,$basic);



						}else if(isset($airPricePoint[0]['airOption'][5]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == True &&
								isset($airPricePoint[1]['airOption'][5]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == True){
							
							//go Leg 1						
							$goFareInfoRef = $airPricePoint[0]['airOption'][5]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][5]['airBookingInfo'][0]['@attributes']['SegmentRef'];
							$goBags = $airFareInfo[$goFareInfoRef]['Bags'];						
							$goTravelTime = $airAirSegment[$goSegmentRef]['TravelTime'];												
							$goTravelTimeHm = floor($goTravelTime / 60)."H ".($goTravelTime - ((floor($goTravelTime / 60)) * 60))."Min";


							$goArrivalTo = $airAirSegment[$goSegmentRef]['Destination'];
							$goDepartureFrom = $airAirSegment[$goSegmentRef]['Origin'];

							$goArrivalTime = $airAirSegment[$goSegmentRef]['ArrivalTime'];
							$goDepartureTime = $airAirSegment[$goSegmentRef]['DepartureTime'];

							$godpTime = date("D d M Y", strtotime($goDepartureTime));
							$goarrTime = date("D d M Y", strtotime($goArrivalTime));
							
							$gomarkettingCarrier = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
							$gomkrow = mysqli_fetch_array($gomksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$gomarkettingCarrierName = $gomkrow['name'];		
							}

							// Departure Country
							$godsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
							$godrow = mysqli_fetch_array($godsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$godAirport = $godrow['name'];
								$godCity = $godrow['cityName'];
								$godCountry = $godrow['countryCode'];		
							}

							// Departure Country
							$goasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$goarow = mysqli_fetch_array($goasql,MYSQLI_ASSOC);

							if(!empty($goarow)){
								$goaAirport = $goarow['name'];
								$goaCity = $goarow['cityName'];
								$goaCountry = $goarow['countryCode'];
							}
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][5]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][5]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][5]['airBookingInfo'][0]['@attributes']['CabinClass'];

							//go Leg 2						
							$goFareInfoRef1 = $airPricePoint[0]['airOption'][5]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$goSegmentRef1 = $airPricePoint[0]['airOption'][5]['airBookingInfo'][1]['@attributes']['SegmentRef'];
							$goBags1 = $airFareInfo[$goFareInfoRef1]['Bags'];						
							$goTravelTime1 = $airAirSegment[$goSegmentRef1]['TravelTime'];												
							$goTravelTimeHm1 = floor($goTravelTime1 / 60)."H ".($goTravelTime1 - ((floor($goTravelTime1 / 60)) * 60))."Min";


							$goArrivalTo1 = $airAirSegment[$goSegmentRef1]['Destination'];
							$goDepartureFrom1 = $airAirSegment[$goSegmentRef1]['Origin'];

							$goArrivalTime1 = $airAirSegment[$goSegmentRef1]['ArrivalTime'];
							$goDepartureTime1 = $airAirSegment[$goSegmentRef1]['DepartureTime'];

							$godpTime1 = date("D d M Y", strtotime($goDepartureTime1));
							$goarrTime1 = date("D d M Y", strtotime($goArrivalTime1));
							
							$gomarkettingCarrier1 = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN1 = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gomksql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier1' ");
							$gomkrow1 = mysqli_fetch_array($gomksql1,MYSQLI_ASSOC);

							if(!empty($gomkrow1)){
								$gomarkettingCarrierName1 = $gomkrow1['name'];		
							}

							// Departure Country
							$godsql1 = mysqli_query($conn,"$Airportsql code='$goDepartureFrom1' ");
							$godrow1 = mysqli_fetch_array($godsql1,MYSQLI_ASSOC);

							if(!empty($godrow1)){
								$godAirport1 = $godrow1['name'];
								$godCity1 = $godrow1['cityName'];
								$godCountry1 = $godrow1['countryCode'];		
							}

							// Departure Country
							$goasql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo1' ");
							$goarow1 = mysqli_fetch_array($goasql1,MYSQLI_ASSOC);

							if(!empty($goarow1)){
								$goaAirport1 = $goarow1['name'];
								$goaCity1 = $goarow1['cityName'];
								$goaCountry1 = $goarow1['countryCode'];
							}
							
							
							$goBookingCode1 = $airPricePoint[0]['airOption'][5]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$goSeat1 = $airPricePoint[0]['airOption'][5]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$goCabinClass1 = $airPricePoint[0]['airOption'][5]['airBookingInfo'][1]['@attributes']['CabinClass'];

							
							//Back Leg 1
							
							$backFareInfoRef = $airPricePoint[1]['airOption'][5]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$backSegmentRef = $airPricePoint[1]['airOption'][5]['airBookingInfo'][0]['@attributes']['SegmentRef'];
							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];						
							$backTravelTime = $airAirSegment[$backSegmentRef]['TravelTime'];												
							$backTravelTimeHm = floor($backTravelTime / 60)."H ".($backTravelTime - ((floor($backTravelTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backdpTime = date("D d M Y", strtotime($backDepartureTime));
							$backarrTime = date("D d M Y", strtotime($backArrivalTime));
							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backmksql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
							$backmkrow = mysqli_fetch_array($backmksql,MYSQLI_ASSOC);

							if(!empty($gomkrow)){
								$backmarkettingCarrierName = $backmkrow['name'];		
							}

							// Departure Country
							$backdsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backdrow = mysqli_fetch_array($backdsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$backdAirport = $backdrow['name'];
								$backdCity = $backdrow['cityName'];
								$backdCountry = $backdrow['countryCode'];		
							}

							// Departure Country
							$backasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$backarow = mysqli_fetch_array($backasql,MYSQLI_ASSOC);

							if(!empty($backarow)){
								$backaAirport = $backarow['name'];
								$backaCity = $backarow['cityName'];
								$backaCountry = $backarow['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][5]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][5]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][5]['airBookingInfo'][0]['@attributes']['CabinClass'];

							//Back Leg 2
							
							$backFareInfoRef1 = $airPricePoint[1]['airOption'][5]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$backSegmentRef1 = $airPricePoint[1]['airOption'][5]['airBookingInfo'][1]['@attributes']['SegmentRef'];
							$backBags1 = $airFareInfo[$backFareInfoRef1]['Bags'];						
							$backTravelTime1 = $airAirSegment[$backSegmentRef1]['TravelTime'];												
							$backTravelTimeHm1 = floor($backTravelTime1 / 60)."H ".($backTravelTime1 - ((floor($backTravelTime1 / 60)) * 60))."Min";


							$backArrivalTo1 = $airAirSegment[$backSegmentRef1]['Destination'];
							$backDepartureFrom1 = $airAirSegment[$backSegmentRef1]['Origin'];

							$backArrivalTime1 = $airAirSegment[$backSegmentRef1]['ArrivalTime'];
							$backDepartureTime1 = $airAirSegment[$backSegmentRef1]['DepartureTime'];

							$backdpTime1 = date("D d M Y", strtotime($backDepartureTime1));
							$backarrTime1 = date("D d M Y", strtotime($backArrivalTime1));
							
							$backmarkettingCarrier1 = $airAirSegment[$backSegmentRef1]['Carrier'];
							$backmarkettingFN1 = $airAirSegment[$backSegmentRef1]['FlightNumber'];

							$backmksql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier1' ");
							$backmkrow1 = mysqli_fetch_array($backmksql1,MYSQLI_ASSOC);

							if(!empty($gomkrow1)){
								$backmarkettingCarrierName1 = $backmkrow1['name'];		
							}

							// Departure Country
							$backdsql1 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom1' ");
							$backdrow1 = mysqli_fetch_array($backdsql1,MYSQLI_ASSOC);

							if(!empty($godrow1)){
								$backdAirport1 = $backdrow1['name'];
								$backdCity1 = $backdrow1['cityName'];
								$backdCountry1 = $backdrow1['countryCode'];		
							}

							// Departure Country
							$backasql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo1' ");
							$backarow1 = mysqli_fetch_array($backasql1,MYSQLI_ASSOC);

							if(!empty($backarow1)){
								$backaAirport1 = $backarow1['name'];
								$backaCity1 = $backarow1['cityName'];
								$backaCountry1 = $backarow1['countryCode'];
							}
							
							
							$backBookingCode1 = $airPricePoint[1]['airOption'][5]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$backSeat1 = $airPricePoint[1]['airOption'][5]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$backCabinClass1 = $airPricePoint[1]['airOption'][5]['airBookingInfo'][1]['@attributes']['CabinClass'];


							$segment = array("go" =>
											array("0" =>
													array("marketingcareer"=> "$gomarkettingCarrier",
														"marketingflight"=> "$gomarkettingFN",
															"operatingcareer"=> "$gomarkettingCarrier",
															"operatingflight"=> "$gomarkettingFN",
															"departure"=> "$goDepartureFrom",
															"departureAirport"=> "$godAirport",
															"departureLocation"=> "$godCity , $godCountry",                    
															"departureTime" => "$goDepartureTime",
															"arrival"=> "$goArrivalTo",                   
															"arrivalTime" => "$goArrivalTime",
															"arrivalAirport"=> "$goaAirport",
															"arrivalLocation"=> "$goaCity , $goaCountry",
															"flightduration"=> "$goTravelTime",
															"bookingcode"=> "$goBookingCode",
															"seat"=> "$goSeat",
															"bags" => "$goBags",
															"class" => "$goCabinClass",
															"segmentDetails"=> $airAirSegment[$goSegmentRef]),
													"1" =>
													array("marketingcareer"=> "$gomarkettingCarrier1",
														"marketingflight"=> "$gomarkettingFN1",
															"operatingcareer"=> "$gomarkettingCarrier1",
															"operatingflight"=> "$gomarkettingFN1",
															"departure"=> "$goDepartureFrom1",
															"departureAirport"=> "$godAirport1",
															"departureLocation"=> "$godCity1 , $godCountry1",                    
															"departureTime" => "$goDepartureTime1",
															"arrival"=> "$goArrivalTo1",                   
															"arrivalTime" => "$goArrivalTime1",
															"arrivalAirport"=> "$goaAirport1",
															"arrivalLocation"=> "$goaCity1 , $goaCountry1",
															"flightduration"=> "$goTravelTime1",
															"bookingcode"=> "$goBookingCode1",
															"seat"=> "$goSeat1",
															"bags" => "$goBags1",
															"class" => "$goCabinClass1",
															"segmentDetails"=> $airAirSegment[$goSegmentRef1])																																				
												),
									"back" => array("0" =>
														array("marketingcareer"=> "$backmarkettingCarrier",
																"marketingflight"=> "$backmarkettingFN",
																"operatingcareer"=> "$backmarkettingCarrier",
																"operatingflight"=> "$backmarkettingFN",
																"departure"=> "$backDepartureFrom",
																"departureAirport"=> "$backdAirport",
																"departureLocation"=> "$backdCity , $backdCountry",                    
																"departureTime" => "$backDepartureTime",
																"arrival"=> "$backArrivalTo",                   
																"arrivalTime" => "$backArrivalTime",
																"arrivalAirport"=> "$backaAirport",
																"arrivalLocation"=> "$backaCity , $backaCountry",
																"flightduration"=> "$backTravelTime",
																"bookingcode"=> "$backBookingCode",
																"seat"=> "$backSeat",
																"bags" => "$backBags",
																"class" => "$backCabinClass",
																"segmentDetails"=> $airAirSegment[$backSegmentRef]),
												"1" =>
														array("marketingcareer"=> "$backmarkettingCarrier1",
																"marketingflight"=> "$backmarkettingFN1",
																"operatingcareer"=> "$backmarkettingCarrier1",
																"operatingflight"=> "$backmarkettingFN1",
																"departure"=> "$backDepartureFrom1",
																"departureAirport"=> "$backdAirport1",
																"departureLocation"=> "$backdCity1 , $backdCountry1",                    
																"departureTime" => "$backDepartureTime1",
																"arrival"=> "$backArrivalTo1",                   
																"arrivalTime" => "$backArrivalTime1",
																"arrivalAirport"=> "$backaAirport1",
																"arrivalLocation"=> "$backaCity1 , $backaCountry1",
																"flightduration"=> "$backTravelTime1",
																"bookingcode"=> "$backBookingCode1",
																"seat"=> "$backSeat1",
																"class" => "$backCabinClass1",
																"bags" => "$backBags1",
																"segmentDetails"=> $airAirSegment[$backSegmentRef1])																									

												)
											);

							$basic = array("system" => "Galileo",
									"segment"=> "2",
									"career"=> "$validatingCarrierCode",
									"careerName" => "$CarrieerName",
									"baseprice" => "$BasePrice",
									"taxes" => "$Taxes",
									"price" => "$Exact",
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
									"godeparture"=> "$From",                   
									"godepartureTime" => $goDepartureTime,
									"godepartureDate" => $godpTime,
									"goarrival"=> "$To", 
									"goarrivalTime" => "$goArrivalTime1",
									"goarrivalDate" => "$goarrTime1",                
									"backdeparture"=> "$To",                   
									"backdepartureTime" => $backDepartureTime,
									"backdepartureDate" => $backdpTime,
									"backarrival"=> "$From", 
									"backarrivalTime" => "$backArrivalTime1", 
									"backarrivalDate" => $backarrTime1,  
									"goflightduration"=> "$goTravelTime",
									"backflightduration"=> "$backTravelTime",
									"refundable"=> "$Refundable ",
									"segments" => $segment
								);

							array_push($All,$basic);
							
																		
						}
						
					}
									
				}else if(isset($airPricePoint[0]['airOption']['airBookingInfo']) == TRUE && 
							isset($airPricePoint[1]['airOption']['airBookingInfo']) == TRUE){
					
					if(isset($airPricePoint[0]['airOption']['airBookingInfo'][0]) == TRUE 
								&& isset($airPricePoint[1]['airOption']['airBookingInfo'][0]) == TRUE){
									
						$sgcount1 = 0;
						$sgcount2 = 0;

						if(isset($airPricePoint[0]['airOption']['airBookingInfo'])){
							$sgcount1 = count($airPricePoint[0]['airOption']['airBookingInfo']); 			
						}
						if(isset($airPricePoint[1]['airOption']['airBookingInfo'])){
							
						$sgcount2 = count($airPricePoint[1]['airOption']['airBookingInfo']);	
						}else{
							$sgcount2 = 0; 
						}

						if($sgcount1 == 2 && $sgcount2 == 2){
							
							//Go Leg1
							
							$goFareInfoRef = $airPricePoint[0]['airOption']['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption']['airBookingInfo'][0]['@attributes']['SegmentRef'];
							
							$goBags = $airFareInfo[$goFareInfoRef]['Bags'];
							$goTravelTime = $airAirSegment[$goSegmentRef]['TravelTime'];
							$goTravelTimeHm = floor($goTravelTime / 60)."H ".($goTravelTime - ((floor($goTravelTime / 60)) * 60))."Min";

							$goFlightTime = $airAirSegment[$goSegmentRef]['FlightTime'];
							$goFlightTimeHm = floor($goFlightTime / 60)."H ".($goFlightTime - ((floor($goFlightTime / 60)) * 60))."Min";


							$goArrivalTo = $airAirSegment[$goSegmentRef]['Destination'];
							$goDepartureFrom = $airAirSegment[$goSegmentRef]['Origin'];

							$goArrivalTime = $airAirSegment[$goSegmentRef]['ArrivalTime'];
							$goDepartureTime = $airAirSegment[$goSegmentRef]['DepartureTime'];

							$gofromTime = substr($goDepartureTime,11, 19);
							$godpTime = date("D d M Y", strtotime(substr($goDepartureTime,0, 10)." ".$gofromTime));

							$gotoTime = substr($goArrivalTime,11, 19);
							$goarrTime = date("D d M Y", strtotime(substr($goArrivalTime,0, 10)." ".$gotoTime));

							
							$gomarkettingCarrier = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gosqlmk = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
							$gorowmk = mysqli_fetch_array($gosqlmk,MYSQLI_ASSOC);

							if(!empty($gorowmk)){
								$gomarkettingCarrierName = $gorowmk['name'];		
							}

							// Departure Country
							$godsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
							$godrow = mysqli_fetch_array($godsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$godAirport = $godrow['name'];
								$godCity = $godrow['cityName'];
								$godCountry = $godrow['countryCode'];		
							}

							// Departure Country
							$goasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$goarow = mysqli_fetch_array($goasql,MYSQLI_ASSOC);

							if(!empty($goarow)){
								$goaAirport = $goarow['name'];
								$goaCity = $goarow['cityName'];
								$goaCountry = $goarow['countryCode'];
							}
							
							
							$goBookingCode = $airPricePoint[0]['airOption']['airBookingInfo'][0]['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption']['airBookingInfo'][0]['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption']['airBookingInfo'][0]['@attributes']['CabinClass'];


							//Go Leg2
							
							$goFareInfoRef1 = $airPricePoint[0]['airOption']['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$goSegmentRef1 = $airPricePoint[0]['airOption']['airBookingInfo'][1]['@attributes']['SegmentRef'];
							
							$goBags1 = $airFareInfo[$goFareInfoRef1]['Bags'];
							$goTravelTime1 = $airAirSegment[$goSegmentRef1]['TravelTime'];
							$goTravelTimeHm1 = floor($goTravelTime1 / 60)."H ".($goTravelTime1 - ((floor($goTravelTime1 / 60)) * 60))."Min";

							$goFlightTime1 = $airAirSegment[$goSegmentRef1]['FlightTime'];
							$goFlightTimeHm1 = floor($goFlightTime1 / 60)."H ".($goFlightTime1 - ((floor($goFlightTime1 / 60)) * 60))."Min";


							$goArrivalTo1 = $airAirSegment[$goSegmentRef1]['Destination'];
							$goDepartureFrom1 = $airAirSegment[$goSegmentRef1]['Origin'];

							$goArrivalTime1 = $airAirSegment[$goSegmentRef1]['ArrivalTime'];
							$goDepartureTime1 = $airAirSegment[$goSegmentRef1]['DepartureTime'];

							$gofromTime1 = substr($goDepartureTime1,11, 19);
							$godpTime1 = date("D d M Y", strtotime(substr($goDepartureTime1,0, 10)." ".$gofromTime1));

							$gotoTime1 = substr($goArrivalTime1,11, 19);
							$goarrTime1 = date("D d M Y", strtotime(substr($goArrivalTime1,0, 10)." ".$gotoTime1));

							
							$gomarkettingCarrier1 = $airAirSegment[$goSegmentRef1]['Carrier'];
							$gomarkettingFN1 = $airAirSegment[$goSegmentRef1]['FlightNumber'];

							$gosql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier1' ");
							$gorow1 = mysqli_fetch_array($gosql1,MYSQLI_ASSOC);

							if(!empty($gorow1)){
								$gomarkettingCarrierName1 = $gorow1['name'];		
							}

							// Departure Country
							$godsql1 = mysqli_query($conn,"$Airportsql code='$goDepartureFrom1' ");
							$godrow1 = mysqli_fetch_array($godsql1,MYSQLI_ASSOC);

							if(!empty($godrow1)){
								$godAirport1 = $godrow1['name'];
								$godCity1 = $godrow1['cityName'];
								$godCountry1 = $godrow1['countryCode'];		
							}

							// Departure Country
							$goasql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo1' ");
							$goarow1 = mysqli_fetch_array($goasql1,MYSQLI_ASSOC);

							if(!empty($goarow1)){
								$goaAirport1 = $goarow1['name'];
								$goaCity1 = $goarow1['cityName'];
								$goaCountry1 = $goarow1['countryCode'];
							}
							
							
							$goBookingCode1 = $airPricePoint[0]['airOption']['airBookingInfo'][1]['@attributes']['BookingCode'];
							$goSeat1 = $airPricePoint[0]['airOption']['airBookingInfo'][1]['@attributes']['BookingCount'];
							$goCabinClass1 = $airPricePoint[0]['airOption']['airBookingInfo'][1]['@attributes']['CabinClass'];



							//Back Leg 1

							$backFareInfoRef = $airPricePoint[1]['airOption']['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$backSegmentRef = $airPricePoint[1]['airOption']['airBookingInfo'][0]['@attributes']['SegmentRef'];
							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];
							$backTravelTime = $airAirSegment[$backSegmentRef]['TravelTime'];
							$backTravelTimeHm = floor($backTravelTime / 60)."H ".($backTravelTime - ((floor($backTravelTime / 60)) * 60))."Min";

							$backFlightTime = $airAirSegment[$backSegmentRef]['FlightTime'];
							$backFlightTimeHm = floor($backFlightTime / 60)."H ".($backFlightTime - ((floor($backFlightTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backdpTime = date("D d M Y", strtotime($backDepartureTime));
							$backarrTime = date("D d M Y", strtotime($backArrivalTime));

							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backsql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
							$backrow = mysqli_fetch_array($backsql,MYSQLI_ASSOC);

							if(!empty($backrow)){
								$backmarkettingCarrierName = $backrow['name'];		
							}

							// Departure Country
							$backdsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backdrow = mysqli_fetch_array($backdsql,MYSQLI_ASSOC);

							if(!empty($backdrow)){
								$backdAirport = $backdrow['name'];
								$backdCity = $backdrow['cityName'];
								$backdCountry = $backdrow['countryCode'];		
							}

							// Arrival Country
							$backasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo' ");
							$backarow = mysqli_fetch_array($backasql,MYSQLI_ASSOC);

							if(!empty($backarow)){
								$backaAirport = $backarow['name'];
								$backaCity = $backarow['cityName'];
								$backaCountry = $backarow['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption']['airBookingInfo'][0]['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption']['airBookingInfo'][0]['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption']['airBookingInfo'][0]['@attributes']['CabinClass'];

							
							//Back Leg 2
							
							$backFareInfoRef1 = $airPricePoint[1]['airOption']['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$backSegmentRef1 = $airPricePoint[1]['airOption']['airBookingInfo'][1]['@attributes']['SegmentRef'];

							$backBags1 = $airFareInfo[$backFareInfoRef1]['Bags'];
							$backTravelTime1 = $airAirSegment[$backSegmentRef1]['TravelTime'];
							$backTravelTimeHm1 = floor($backTravelTime1 / 60)."H ".($backTravelTime1 - ((floor($backTravelTime1 / 60)) * 60))."Min";

							$backFlightTime1 = $airAirSegment[$backSegmentRef1]['FlightTime'];
							$backFlightTimeHm1 = floor($backFlightTime1 / 60)."H ".($backFlightTime1 - ((floor($backFlightTime1 / 60)) * 60))."Min";


							$backArrivalTo1 = $airAirSegment[$backSegmentRef1]['Destination'];
							$backDepartureFrom1 = $airAirSegment[$backSegmentRef1]['Origin'];

							$backArrivalTime1 = $airAirSegment[$backSegmentRef1]['ArrivalTime'];
							$backDepartureTime1 = $airAirSegment[$backSegmentRef1]['DepartureTime'];

							$backfromTime1 = substr($backDepartureTime1,11, 19);
							$backdpTime1 = date("D d M Y", strtotime(substr($backDepartureTime1,0, 10)." ".$backfromTime1));

							$backtoTime1 = substr($backArrivalTime1,11, 19);
							$backarrTime1 = date("D d M Y", strtotime(substr($backArrivalTime1,0, 10)." ".$backtoTime1));

							
							$backmarkettingCarrier1 = $airAirSegment[$backSegmentRef1]['Carrier'];
							$backmarkettingFN1 = $airAirSegment[$backSegmentRef1]['FlightNumber'];

							$backsqlmk = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier1' ");
							$backrowmk = mysqli_fetch_array($backsqlmk,MYSQLI_ASSOC);

							if(!empty($backrowmk1)){
								$backmarkettingCarrierName1 = $backrowmk1['name'];		
							}

							// Departure Country
							$backdsql1 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom1' ");
							$backdrow1 = mysqli_fetch_array($backdsql1,MYSQLI_ASSOC);

							if(!empty($backdrow1)){
								$backdAirport1 = $backdrow1['name'];
								$backdCity1 = $backdrow1['cityName'];
								$backdCountry1 = $backdrow1['countryCode'];		
							}

							// Departure Country
							$backasql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo1' ");
							$backarow1 = mysqli_fetch_array($backasql1,MYSQLI_ASSOC);

							if(!empty($backarow1)){
								$backaAirport1 = $backarow1['name'];
								$backaCity1 = $backarow1['cityName'];
								$backaCountry1 = $backarow1['countryCode'];
							}
							
							
							$backBookingCode1 = $airPricePoint[1]['airOption']['airBookingInfo'][1]['@attributes']['BookingCode'];
							$backSeat1 = $airPricePoint[1]['airOption']['airBookingInfo'][1]['@attributes']['BookingCount'];
							$backCabinClass1 = $airPricePoint[1]['airOption']['airBookingInfo'][1]['@attributes']['CabinClass'];
							
							$backTransits1 = $backTravelTime1 - ($backFlightTime + $backFlightTime1);
							$backTransitHm = floor($backTransits1 / 60)."H ".($backTransits1 - ((floor($backTransits1 / 60)) * 60))."Min";

							$backTransit = array("transit1"=> $backTransitHm);

							$segment = array("go" =>
											array("0" =>
													array("marketingcareer"=> "$gomarkettingCarrier",
														"marketingflight"=> "$gomarkettingFN",
															"operatingcareer"=> "$gomarkettingCarrier",
															"operatingflight"=> "$gomarkettingFN",
															"departure"=> "$goDepartureFrom",
															"departureAirport"=> "$godAirport",
															"departureLocation"=> "$godCity , $godCountry",                    
															"departureTime" => "$goDepartureTime",
															"arrival"=> "$goArrivalTo",                   
															"arrivalTime" => "$goArrivalTime",
															"arrivalAirport"=> "$goaAirport",
															"arrivalLocation"=> "$goaCity , $goaCountry",
															"flightduration"=> "$goTravelTime",
															"bookingcode"=> "$goBookingCode",
															"seat"=> "$goSeat",
															"bags" => "$goBags",
															"class" => "$goCabinClass",
															"segmentDetails"=> $airAirSegment[$goSegmentRef]),
													"1" =>
													array("marketingcareer"=> "$gomarkettingCarrier1",
														"marketingflight"=> "$gomarkettingFN1",
															"operatingcareer"=> "$gomarkettingCarrier1",
															"operatingflight"=> "$gomarkettingFN1",
															"departure"=> "$goDepartureFrom1",
															"departureAirport"=> "$godAirport1",
															"departureLocation"=> "$godCity1 , $godCountry1",                    
															"departureTime" => "$goDepartureTime1",
															"arrival"=> "$goArrivalTo1",                   
															"arrivalTime" => "$goArrivalTime1",
															"arrivalAirport"=> "$goaAirport1",
															"arrivalLocation"=> "$goaCity1 , $goaCountry1",
															"flightduration"=> "$goTravelTime1",
															"bookingcode"=> "$goBookingCode1",
															"seat"=> "$goSeat1",
															"bags" => "$goBags1",
															"class" => "$goCabinClass1",
															"segmentDetails"=> $airAirSegment[$goSegmentRef1])																																				
												),
											"back" => 
											array("0" =>
														array("marketingcareer"=> "$backmarkettingCarrier",
																"marketingflight"=> "$backmarkettingFN",
																"operatingcareer"=> "$backmarkettingCarrier",
																"operatingflight"=> "$backmarkettingFN",
																"departure"=> "$backDepartureFrom",
																"departureAirport"=> "$backdAirport",
																"departureLocation"=> "$backdCity , $backdCountry",                    
																"departureTime" => "$backDepartureTime",
																"arrival"=> "$backArrivalTo",                   
																"arrivalTime" => "$backArrivalTime",
																"arrivalAirport"=> "$backaAirport",
																"arrivalLocation"=> "$backaCity , $backaCountry",
																"flightduration"=> "$backTravelTime",
																"bookingcode"=> "$backBookingCode",
																"seat"=> "$backSeat",
																"bags" => "$backBags",
																"class" => "$backCabinClass",
																"segmentDetails"=> $airAirSegment[$backSegmentRef]),
												"1" =>
														array("marketingcareer"=> "$backmarkettingCarrier1",
																"marketingflight"=> "$backmarkettingFN1",
																"operatingcareer"=> "$backmarkettingCarrier1",
																"operatingflight"=> "$backmarkettingFN1",
																"departure"=> "$backDepartureFrom1",
																"departureAirport"=> "$backdAirport1",
																"departureLocation"=> "$backdCity1 , $backdCountry1",                    
																"departureTime" => "$backDepartureTime1",
																"arrival"=> "$backArrivalTo1",                   
																"arrivalTime" => "$backArrivalTime1",
																"arrivalAirport"=> "$backaAirport1",
																"arrivalLocation"=> "$backaCity1 , $backaCountry1",
																"flightduration"=> "$backTravelTime1",
																"bookingcode"=> "$backBookingCode1",
																"seat"=> "$backSeat1",
																"class" => "$backCabinClass1",
																"bags" => "$backBags1",
																"segmentDetails"=> $airAirSegment[$backSegmentRef1])																									

												)
											);

							$basic = array("system" => "Galileo",
									"segment"=> "2",
									"triptype"=>$TripType,
									"career"=> "$validatingCarrierCode",
									"careerName" => "$CarrieerName",
									"baseprice" => "$BasePrice",
									"taxes" => "$Taxes",
									"price" => "$Exact",
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
									"godeparture"=> "$From",                   
									"godepartureTime" => $goDepartureTime,
									"godepartureDate" => $godpTime,
									"goarrival"=> "$To", 
									"goarrivalTime" => "$goArrivalTime1",
									"goarrivalDate" => "$goarrTime1",                
									"backdeparture"=> "$To",                   
									"backdepartureTime" => $backDepartureTime,
									"backdepartureDate" => $backdpTime,
									"backarrival"=> "$From", 
									"backarrivalTime" => "$backArrivalTime1", 
									"backarrivalDate" => $backarrTime1,  
									"goflightduration"=> "$goTravelTime",
									"backflightduration"=> "$backTravelTime",
									"refundable"=> "$Refundable ",
									"segments" => $segment
								);

							array_push($All,$basic);


						}else if($sgcount1 == 3 && $sgcount2 == 3 ){

							//Go Leg 1
							
							$goFareInfoRef = $airPricePoint[0]['airOption']['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption']['airBookingInfo'][0]['@attributes']['SegmentRef'];
							
							$goBags = $airFareInfo[$goFareInfoRef]['Bags'];
							$goTravelTime = $airAirSegment[$goSegmentRef]['TravelTime'];
							$goTravelTimeHm = floor($goTravelTime / 60)."H ".($goTravelTime - ((floor($goTravelTime / 60)) * 60))."Min";

							$goFlightTime = $airAirSegment[$goSegmentRef]['FlightTime'];
							$goFlightTimeHm = floor($goFlightTime / 60)."H ".($goFlightTime - ((floor($goFlightTime / 60)) * 60))."Min";


							$goArrivalTo = $airAirSegment[$goSegmentRef]['Destination'];
							$goDepartureFrom = $airAirSegment[$goSegmentRef]['Origin'];

							$goArrivalTime = $airAirSegment[$goSegmentRef]['ArrivalTime'];
							$goDepartureTime = $airAirSegment[$goSegmentRef]['DepartureTime'];

							$gofromTime = substr($goDepartureTime,11, 19);
							$godpTime = date("D d M Y", strtotime(substr($goDepartureTime,0, 10)." ".$gofromTime));

							$gotoTime = substr($goArrivalTime,11, 19);
							$goarrTime = date("D d M Y", strtotime(substr($goArrivalTime,0, 10)." ".$gotoTime));

							
							$gomarkettingCarrier = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gosqlmk = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
							$gorowmk = mysqli_fetch_array($gosqlmk,MYSQLI_ASSOC);

							if(!empty($gorowmk)){
								$gomarkettingCarrierName = $gorowmk['name'];		
							}

							// Departure Country
							$godsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
							$godrow = mysqli_fetch_array($godsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$godAirport = $godrow['name'];
								$godCity = $godrow['cityName'];
								$godCountry = $godrow['countryCode'];		
							}

							// Departure Country
							$goasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$goarow = mysqli_fetch_array($goasql,MYSQLI_ASSOC);

							if(!empty($gorow2)){
								$goaAirport = $goarow['name'];
								$goaCity = $goarow['cityName'];
								$goaCountry = $goarow['countryCode'];
							}
							
							
							$goBookingCode = $airPricePoint[0]['airOption']['airBookingInfo'][0]['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption']['airBookingInfo'][0]['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption']['airBookingInfo'][0]['@attributes']['CabinClass'];


							//Go Leg 2
							
							$goFareInfoRef1 = $airPricePoint[0]['airOption']['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$goSegmentRef1 = $airPricePoint[0]['airOption']['airBookingInfo'][1]['@attributes']['SegmentRef'];
							
							$goBags1 = $airFareInfo[$goFareInfoRef1]['Bags'];
							$goTravelTime1 = $airAirSegment[$goSegmentRef1]['TravelTime'];
							$goTravelTimeHm1 = floor($goTravelTime1 / 60)."H ".($goTravelTime1 - ((floor($goTravelTime1 / 60)) * 60))."Min";

							$goFlightTime1 = $airAirSegment[$goSegmentRef1]['FlightTime'];
							$goFlightTimeHm1 = floor($goFlightTime1 / 60)."H ".($goFlightTime1 - ((floor($goFlightTime1 / 60)) * 60))."Min";


							$goArrivalTo1 = $airAirSegment[$goSegmentRef1]['Destination'];
							$goDepartureFrom1 = $airAirSegment[$goSegmentRef1]['Origin'];

							$goArrivalTime1 = $airAirSegment[$goSegmentRef1]['ArrivalTime'];
							$goDepartureTime1 = $airAirSegment[$goSegmentRef1]['DepartureTime'];

							$gofromTime1 = substr($goDepartureTime1,11, 19);
							$godpTime1 = date("D d M Y", strtotime(substr($goDepartureTime1,0, 10)." ".$gofromTime1));

							$gotoTime1 = substr($goArrivalTime1,11, 19);
							$goarrTime1 = date("D d M Y", strtotime(substr($goArrivalTime1,0, 10)." ".$gotoTime1));

							
							$gomarkettingCarrier1 = $airAirSegment[$goSegmentRef1]['Carrier'];
							$gomarkettingFN1 = $airAirSegment[$goSegmentRef1]['FlightNumber'];

							$gosql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier1' ");
							$gorow1 = mysqli_fetch_array($gosql1,MYSQLI_ASSOC);

							if(!empty($gorow1)){
								$gomarkettingCarrierName1 = $gorow1['name'];		
							}

							// Departure Country
							$godsql1 = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
							$godrow1 = mysqli_fetch_array($godsql1,MYSQLI_ASSOC);

							if(!empty($godrow1)){
								$godAirport1 = $godrow1['name'];
								$godCity1 = $godrow1['cityName'];
								$godCountry1 = $godrow1['countryCode'];		
							}

							// Departure Country
							$goasql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$goarow1 = mysqli_fetch_array($goasql1,MYSQLI_ASSOC);

							if(!empty($goarow1)){
								$goaAirport1 = $goarow1['name'];
								$goaCity1 = $goarow1['cityName'];
								$goaCountry1 = $goarow1['countryCode'];
							}
							
							
							$goBookingCode1 = $airPricePoint[0]['airOption']['airBookingInfo'][1]['@attributes']['BookingCode'];
							$goSeat1 = $airPricePoint[0]['airOption']['airBookingInfo'][1]['@attributes']['BookingCount'];
							$goCabinClass1 = $airPricePoint[0]['airOption']['airBookingInfo'][1]['@attributes']['CabinClass'];


							//Go Leg 3
							
							$goFareInfoRef2 = $airPricePoint[0]['airOption']['airBookingInfo'][2]['@attributes']['FareInfoRef'];
							$goSegmentRef2 = $airPricePoint[0]['airOption']['airBookingInfo'][2]['@attributes']['SegmentRef'];
							
							$goBags2 = $airFareInfo[$goFareInfoRef2]['Bags'];
							$goTravelTime2 = $airAirSegment[$goSegmentRef2]['TravelTime'];
							$goTravelTimeHm2 = floor($goTravelTime2 / 60)."H ".($goTravelTime2 - ((floor($goTravelTime2 / 60)) * 60))."Min";

							$goFlightTime2 = $airAirSegment[$goSegmentRef2]['FlightTime'];
							$goFlightTimeHm2 = floor($goFlightTime2 / 60)."H ".($goFlightTime2 - ((floor($goFlightTime2 / 60)) * 60))."Min";


							$goArrivalTo2 = $airAirSegment[$goSegmentRef2]['Destination'];
							$goDepartureFrom2 = $airAirSegment[$goSegmentRef2]['Origin'];

							$goArrivalTime2 = $airAirSegment[$goSegmentRef2]['ArrivalTime'];
							$goDepartureTime2 = $airAirSegment[$goSegmentRef2]['DepartureTime'];

							$gofromTime2 = substr($goDepartureTime2,11, 19);
							$godpTime2 = date("D d M Y", strtotime(substr($goDepartureTime2,0, 10)." ".$gofromTime2));

							$gotoTime2 = substr($goArrivalTime2,11, 19);
							$goarrTime2 = date("D d M Y", strtotime(substr($goArrivalTime2,0, 10)." ".$gotoTime2));

							
							$gomarkettingCarrier2 = $airAirSegment[$goSegmentRef2]['Carrier'];
							$gomarkettingFN2 = $airAirSegment[$goSegmentRef2]['FlightNumber'];

							$gosqlmk2 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier2' ");
							$gorowmk2 = mysqli_fetch_array($gosqlmk2,MYSQLI_ASSOC);

							if(!empty($gorowmk2)){
								$gomarkettingCarrierName2 = $gorowmk2['name'];		
							}

							// Departure Country
							$godsql2 = mysqli_query($conn,"$Airportsql code='$goDepartureFrom2' ");
							$godrow2 = mysqli_fetch_array($godsql2,MYSQLI_ASSOC);

							if(!empty($godrow2)){
								$godAirport2 = $godrow2['name'];
								$godCity2 = $godrow2['cityName'];
								$godCountry2 = $godrow2['countryCode'];		
							}

							// Departure Country
							$goasql2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo2' ");
							$goarow2 = mysqli_fetch_array($goasql2,MYSQLI_ASSOC);

							if(!empty($goarow2)){
								$goaAirport2 = $goarow2['name'];
								$goaCity2 = $goarow2['cityName'];
								$goaCountry2 = $goarow2['countryCode'];
							}
							
							
							$goBookingCode2 = $airPricePoint[0]['airOption']['airBookingInfo'][2]['@attributes']['BookingCode'];
							$goSeat2 = $airPricePoint[0]['airOption']['airBookingInfo'][2]['@attributes']['BookingCount'];
							$goCabinClass2 = $airPricePoint[0]['airOption']['airBookingInfo'][2]['@attributes']['CabinClass'];

							

							//Back Leg 1
							
							$backFareInfoRef1 = $airPricePoint[1]['airOption']['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$backSegmentRef1 = $airPricePoint[1]['airOption']['airBookingInfo'][0]['@attributes']['SegmentRef'];


							$backFlightTime1 = $airAirSegment[$backSegmentRef1]['FlightTime'];
							$backFlightTimeHm1 = floor($backFlightTime1 / 60)."H ".($backFlightTime1 - ((floor($backFlightTime1 / 60)) * 60))."Min";


							$backArrivalTo1 = $airAirSegment[$backSegmentRef1]['Destination'];
							$backDepartureFrom1 = $airAirSegment[$backSegmentRef1]['Origin'];

							$backArrivalTime1 = $airAirSegment[$backSegmentRef1]['ArrivalTime'];
							$DepartureTime1 = $airAirSegment[$backSegmentRef1]['DepartureTime'];

							$backfromTime1 = substr($backDepartureTime1,11, 19);
							$backdpTime1 = date("D d M Y", strtotime(substr($backDepartureTime1,0, 10)." ".$backfromTime1));

							$backtoTime1 = substr($backArrivalTime1,11, 19);
							$backarrTime1 = date("D d M Y", strtotime(substr($backArrivalTime1,0, 10)." ".$backtoTime1));

							
							$backmarkettingCarrier1 = $airAirSegment[$backSegmentRef1]['Carrier'];
							$backmarkettingFN1 = $airAirSegment[$backSegmentRef1]['FlightNumber'];

							$backsqlmk1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier1' ");
							$backrowmk1 = mysqli_fetch_array($backsqlmk1,MYSQLI_ASSOC);

							if(!empty($backrowmk1)){
								$backmarkettingCarrierName1 = $backrowmk1['name'];		
							}

							// Departure Country
							$backsqldp1 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom1' ");
							$backrowdp1 = mysqli_fetch_array($backsqldp1,MYSQLI_ASSOC);

							if(!empty($backrowdp1)){
								$backdAirport1 = $backrowdp1['name'];
								$backdCity1 = $backrowdp1['cityName'];
								$backdCountry1 = $backrowdp1['countryCode'];		
							}

							// Departure Country
							$backsqlar2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo1' ");
							$backrowar2 = mysqli_fetch_array($backsqlar2,MYSQLI_ASSOC);

							if(!empty($backrow2)){
								$backaAirport1 = $backrowar2['name'];
								$backaCity1 = $backrowar2['cityName'];
								$backaCountry1 = $backrowar2['countryCode'];
							}
							
							
							$backBookingCode1 = $airPricePoint[1]['airOption']['airBookingInfo'][0]['@attributes']['BookingCode'];
							$backSeat1 = $airPricePoint[1]['airOption']['airBookingInfo'][0]['@attributes']['BookingCount'];
							$backCabinClass1 = $airPricePoint[1]['airOption']['airBookingInfo'][0]['@attributes']['CabinClass'];
							
							// $backTransits1 = $backTravelTime1 - ($backFlightTime1 + $backFlightTime1);
							// $backTransitHm = floor($backTransits / 60)."H ".($backTransits - ((floor($backTransits / 60)) * 60))."Min";

							
							//Back Leg 2
							
							$backFareInfoRef1 = $airPricePoint[1]['airOption']['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$backSegmentRef1 = $airPricePoint[1]['airOption']['airBookingInfo'][1]['@attributes']['SegmentRef'];


							$backFlightTime1 = $airAirSegment[$backSegmentRef1]['FlightTime'];
							$backFlightTimeHm1 = floor($backFlightTime1 / 60)."H ".($backFlightTime1 - ((floor($backFlightTime1 / 60)) * 60))."Min";


							$backArrivalTo1 = $airAirSegment[$backSegmentRef1]['Destination'];
							$backDepartureFrom2 = $airAirSegment[$backSegmentRef1]['Origin'];

							$backArrivalTime2 = $airAirSegment[$backSegmentRef1]['ArrivalTime'];
							$DepartureTime2 = $airAirSegment[$backSegmentRef1]['DepartureTime'];

							$backfromTime2 = substr($backDepartureTime1,11, 19);
							$backdpTime1 = date("D d M Y", strtotime(substr($backDepartureTime2,0, 10)." ".$backfromTime1));

							$backtoTime1 = substr($backArrivalTime1,11, 19);
							$backarrTime1 = date("D d M Y", strtotime(substr($backArrivalTime1,0, 10)." ".$backtoTime1));

							
							$backmarkettingCarrier2 = $airAirSegment[$backSegmentRef1]['Carrier'];
							$backmarkettingFN1 = $airAirSegment[$backSegmentRef1]['FlightNumber'];

							$backsqlmk = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier1' ");
							$backrowmk = mysqli_fetch_array($backsqlmk,MYSQLI_ASSOC);

							if(!empty($backrowmk1)){
								$backmarkettingCarrierName1 = $backrowmk1['name'];		
							}

							// Departure Country
							$backsqldp1 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom1' ");
							$backrowdp1 = mysqli_fetch_array($backsqldp1,MYSQLI_ASSOC);

							if(!empty($backrow1)){
								$backdAirport1 = $backrowdp1['name'];
								$backdCity1 = $backrowdp1['cityName'];
								$backdCountry1 = $backrowdp1['countryCode'];		
							}

							// Departure Country
							$backsqlar2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo1' ");
							$backrowar2 = mysqli_fetch_array($backsqlar2,MYSQLI_ASSOC);

							if(!empty($backrow2)){
								$backaAirport1 = $backrowar2['name'];
								$backaCity1 = $backrowar2['cityName'];
								$backaCountry1 = $backrowar2['countryCode'];
							}
							
							
							$backBookingCode2 = $airPricePoint[1]['airOption']['airBookingInfo'][1]['@attributes']['BookingCode'];
							$backSeat2 = $airPricePoint[1]['airOption']['airBookingInfo'][1]['@attributes']['BookingCount'];
							$backCabinClass2 = $airPricePoint[1]['airOption']['airBookingInfo'][1]['@attributes']['CabinClass'];
							

							
							$backTransits = $backTravelTime - ($backFlightTime + $backFlightTime1);
							$backTransitHm = floor($backTransits / 60)."H ".($backTransits - ((floor($backTransits / 60)) * 60))."Min";



							$since_start1 =(new DateTime($DepartureTime1))->diff(new DateTime($ArrivalTime));
							$since_start2 =(new DateTime($DepartureTime2))->diff(new DateTime($ArrivalTime1));

							$Transit = array("transit1"=> "$since_start1->h H $since_start1->m Min",
											"transit2"=> "$since_start2->h H $since_start2->m Min");
							

							$segment = array("go" =>
											array("0" =>
													array("marketingcareer"=> "$gomarkettingCarrier",
														"marketingflight"=> "$gomarkettingFN",
															"operatingcareer"=> "$gomarkettingCarrier",
															"operatingflight"=> "$gomarkettingFN",
															"departure"=> "$goDepartureFrom",
															"departureAirport"=> "$godAirport",
															"departureLocation"=> "$godCity , $godCountry",                    
															"departureTime" => "$goDepartureTime",
															"arrival"=> "$goArrivalTo",                   
															"arrivalTime" => "$goArrivalTime",
															"arrivalAirport"=> "$goaAirport",
															"arrivalLocation"=> "$goaCity , $goaCountry",
															"flightduration"=> "$goTravelTime",
															"bookingcode"=> "$goBookingCode",
															"seat"=> "$goSeat",
															"bags" => "$goBags",
															"class" => "$goCabinClass",
															"segmentDetails"=> $airAirSegment[$goSegmentRef]),
													"1" =>
													array("marketingcareer"=> "$gomarkettingCarrier1",
														"marketingflight"=> "$gomarkettingFN1",
															"operatingcareer"=> "$gomarkettingCarrier1",
															"operatingflight"=> "$gomarkettingFN1",
															"departure"=> "$goDepartureFrom1",
															"departureAirport"=> "$godAirport1",
															"departureLocation"=> "$godCity1 , $godCountry1",                    
															"departureTime" => "$goDepartureTime1",
															"arrival"=> "$goArrivalTo1",                   
															"arrivalTime" => "$goArrivalTime1",
															"arrivalAirport"=> "$goaAirport1",
															"arrivalLocation"=> "$goaCity1 , $goaCountry1",
															"flightduration"=> "$goTravelTime1",
															"bookingcode"=> "$goBookingCode1",
															"seat"=> "$goSeat1",
															"bags" => "$goBags1",
															"class" => "$goCabinClass1",
															"segmentDetails"=> $airAirSegment[$goSegmentRef1]),
													"2" =>
													array("marketingcareer"=> "$gomarkettingCarrier2",
														"marketingflight"=> "$gomarkettingFN2",
															"operatingcareer"=> "$gomarkettingCarrier2",
															"operatingflight"=> "$gomarkettingFN2",
															"departure"=> "$goDepartureFrom2",
															"departureAirport"=> "$godAirport2",
															"departureLocation"=> "$godCity2 , $godCountry2",                    
															"departureTime" => "$goDepartureTime2",
															"arrival"=> "$goArrivalTo2",                   
															"arrivalTime" => "$goArrivalTime2",
															"arrivalAirport"=> "$goaAirport2",
															"arrivalLocation"=> "$goaCity2 , $goaCountry2",
															"flightduration"=> "$goTravelTime2",
															"bookingcode"=> "$goBookingCode2",
															"seat"=> "$goSeat2",
															"bags" => "$goBags2",
															"class" => "$goCabinClass2",
															"segmentDetails"=> $airAirSegment[$goSegmentRef2])
																																																			
												),
									"back" => array("0" =>
														array("marketingcareer"=> "$backmarkettingCarrier",
																"marketingflight"=> "$backmarkettingFN",
																"operatingcareer"=> "$backmarkettingCarrier",
																"operatingflight"=> "$backmarkettingFN",
																"departure"=> "$backDepartureFrom",
																"departureAirport"=> "$backdAirport",
																"departureLocation"=> "$backdCity , $backdCountry",                    
																"departureTime" => "$backDepartureTime",
																"arrival"=> "$backArrivalTo",                   
																"arrivalTime" => "$backArrivalTime",
																"arrivalAirport"=> "$backaAirport",
																"arrivalLocation"=> "$backaCity , $backaCountry",
																"flightduration"=> "$backTravelTime",
																"bookingcode"=> "$backBookingCode",
																"seat"=> "$backSeat",
																"bags" => "$backBags",
																"class" => "$backCabinClass",
																"segmentDetails"=> $airAirSegment[$backSegmentRef]),
												"1" =>
														array("marketingcareer"=> "$backmarkettingCarrier1",
																"marketingflight"=> "$backmarkettingFN1",
																"operatingcareer"=> "$backmarkettingCarrier1",
																"operatingflight"=> "$backmarkettingFN1",
																"departure"=> "$backDepartureFrom1",
																"departureAirport"=> "$backdAirport1",
																"departureLocation"=> "$backdCity1 , $backdCountry1",                    
																"departureTime" => "$backDepartureTime1",
																"arrival"=> "$backArrivalTo1",                   
																"arrivalTime" => "$backArrivalTime1",
																"arrivalAirport"=> "$backaAirport1",
																"arrivalLocation"=> "$backaCity1 , $backaCountry1",
																"flightduration"=> "$backTravelTime1",
																"bookingcode"=> "$backBookingCode1",
																"seat"=> "$backSeat1",
																"class" => "$backCabinClass1",
																"bags" => "$backBags1",
																"segmentDetails"=> $airAirSegment[$backSegmentRef1]),
												"2" =>
														array("marketingcareer"=> "$backmarkettingCarrier2",
																"marketingflight"=> "$backmarkettingFN2",
																"operatingcareer"=> "$backmarkettingCarrier2",
																"operatingflight"=> "$backmarkettingFN2",
																"departure"=> "$backDepartureFrom2",
																"departureAirport"=> "$backdAirport2",
																"departureLocation"=> "$backdCity2 , $backdCountry2",                    
																"departureTime" => "$backDepartureTime2",
																"arrival"=> "$backArrivalTo2",                   
																"arrivalTime" => "$backArrivalTime2",
																"arrivalAirport"=> "$backaAirport2",
																"arrivalLocation"=> "$backaCity2 , $backaCountry2",
																"flightduration"=> "$backTravelTime2",
																"bookingcode"=> "$backBookingCode2",
																"seat"=> "$backSeat2",
																"class" => "$backCabinClass2",
																"bags" => "$backBags2",
																"segmentDetails"=> $airAirSegment[$backSegmentRef2])																									

											),
											);

							$basic = array("system" => "Galileo",
									"segment"=> "3",
									"triptype"=>$TripType,
									"career"=> "$validatingCarrierCode",
									"careerName" => "$CarrieerName",
									"baseprice" => "$BasePrice",
									"taxes" => "$Taxes",
									"price" => "$Exact",
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
									"godeparture"=> "$From",                   
									"godepartureTime" => $goDepartureTime,
									"godepartureDate" => $godpTime,
									"goarrival"=> "$To", 
									"goarrivalTime" => "$goArrivalTime1",
									"goarrivalDate" => "$goarrTime1",                
									"backdeparture"=> "$To",                   
									"backdepartureTime" => $backDepartureTime,
									"backdepartureDate" => $backdpTime,
									"backarrival"=> "$From", 
									"backarrivalTime" => "$backArrivalTime1", 
									"backarrivalDate" => $backarrTime1,  
									"goflightduration"=> "$goTravelTime",
									"backflightduration"=> "$backTravelTime",
									"refundable"=> "$Refundable ",
									"segments" => $segment
								);

							array_push($All,$basic);

					}else if(isset($airPricePoint[0]['airOption']['airBookingInfo']['@attributes']['SegmentRef'])){


							//Go Leg1
							
							$goFareInfoRef = $airPricePoint[0]['airOption']['airBookingInfo']['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption']['airBookingInfo']['@attributes']['SegmentRef'];
							
							$goBags = $airFareInfo[$goFareInfoRef]['Bags'];
							$goTravelTime = $airAirSegment[$goSegmentRef]['TravelTime'];
							$goTravelTimeHm = floor($goTravelTime / 60)."H ".($goTravelTime - ((floor($goTravelTime / 60)) * 60))."Min";

							$goFlightTime = $airAirSegment[$goSegmentRef]['FlightTime'];
							$goFlightTimeHm = floor($goFlightTime / 60)."H ".($goFlightTime - ((floor($goFlightTime / 60)) * 60))."Min";


							$goArrivalTo = $airAirSegment[$goSegmentRef]['Destination'];
							$goDepartureFrom = $airAirSegment[$goSegmentRef]['Origin'];

							$goArrivalTime = $airAirSegment[$goSegmentRef]['ArrivalTime'];
							$goDepartureTime = $airAirSegment[$goSegmentRef]['DepartureTime'];

							$gofromTime = substr($goDepartureTime,11, 19);
							$godpTime = date("D d M Y", strtotime(substr($goDepartureTime,0, 10)." ".$gofromTime));

							$gotoTime = substr($goArrivalTime,11, 19);
							$goarrTime = date("D d M Y", strtotime(substr($goArrivalTime,0, 10)." ".$gotoTime));

							
							$gomarkettingCarrier = $airAirSegment[$goSegmentRef]['Carrier'];
							$gomarkettingFN = $airAirSegment[$goSegmentRef]['FlightNumber'];

							$gosqlmk = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$gomarkettingCarrier' ");
							$gorowmk = mysqli_fetch_array($gosqlmk,MYSQLI_ASSOC);

							if(!empty($gorowmk)){
								$gomarkettingCarrierName = $gorowmk['name'];		
							}

							// Departure Country
							$godsql = mysqli_query($conn,"$Airportsql code='$goDepartureFrom' ");
							$godrow = mysqli_fetch_array($godsql,MYSQLI_ASSOC);

							if(!empty($godrow)){
								$godAirport = $godrow['name'];
								$godCity = $godrow['cityName'];
								$godCountry = $godrow['countryCode'];		
							}

							// Departure Country
							$goasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$goArrivalTo' ");
							$goarow = mysqli_fetch_array($goasql,MYSQLI_ASSOC);

							if(!empty($goarow)){
								$goaAirport = $goarow['name'];
								$goaCity = $goarow['cityName'];
								$goaCountry = $goarow['countryCode'];
							}
							
							
							$goBookingCode = $airPricePoint[0]['airOption']['airBookingInfo']['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption']['airBookingInfo']['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption']['airBookingInfo']['@attributes']['CabinClass'];


							//Back Leg 1

							$backFareInfoRef = $airPricePoint[1]['airOption']['airBookingInfo']['@attributes']['FareInfoRef'];
							$backSegmentRef = $airPricePoint[1]['airOption']['airBookingInfo']['@attributes']['SegmentRef'];
							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];
							$backTravelTime = $airAirSegment[$backSegmentRef]['TravelTime'];
							$backTravelTimeHm = floor($backTravelTime / 60)."H ".($backTravelTime - ((floor($backTravelTime / 60)) * 60))."Min";

							$backFlightTime = $airAirSegment[$backSegmentRef]['FlightTime'];
							$backFlightTimeHm = floor($backFlightTime / 60)."H ".($backFlightTime - ((floor($backFlightTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backdpTime = date("D d M Y", strtotime($backDepartureTime));
							$backarrTime = date("D d M Y", strtotime($backArrivalTime));

							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backsql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
							$backrow = mysqli_fetch_array($backsql,MYSQLI_ASSOC);

							if(!empty($backrow)){
								$backmarkettingCarrierName = $backrow['name'];		
							}

							// Departure Country
							$backdsql = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backdrow = mysqli_fetch_array($backdsql,MYSQLI_ASSOC);

							if(!empty($backdrow)){
								$backdAirport = $backdrow['name'];
								$backdCity = $backdrow['cityName'];
								$backdCountry = $backdrow['countryCode'];		
							}

							// Arrival Country
							$backasql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo' ");
							$backarow = mysqli_fetch_array($backasql,MYSQLI_ASSOC);

							if(!empty($backarow)){
								$backaAirport = $backarow['name'];
								$backaCity = $backarow['cityName'];
								$backaCountry = $backarow['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption']['airBookingInfo']['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption']['airBookingInfo']['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption']['airBookingInfo']['@attributes']['CabinClass'];

							

							
							$segment = array("go" =>
									array("0" =>
											array("marketingcareer"=> "$gomarkettingCarrier",
												"marketingflight"=> "$gomarkettingFN",
													"operatingcareer"=> "$gomarkettingCarrier",
													"operatingflight"=> "$gomarkettingFN",
													"departure"=> "$goDepartureFrom",
													"departureAirport"=> "$godAirport",
													"departureLocation"=> "$godCity , $godCountry",                    
													"departureTime" => "$goDepartureTime",
													"arrival"=> "$goArrivalTo",                   
													"arrivalTime" => "$goArrivalTime",
													"arrivalAirport"=> "$goaAirport",
													"arrivalLocation"=> "$goaCity , $goaCountry",
													"flightduration"=> "$goTravelTime",
													"bookingcode"=> "$goBookingCode",
													"bags" => "$goBags",
													"seat" => "$goSeat",
													"class" => "$goCabinClass")																										

												),
									"back" => array("0" =>
														array("marketingcareer"=> "$backmarkettingCarrier",
																"marketingflight"=> "$backmarkettingFN",
																"operatingcareer"=> "$backmarkettingCarrier",
																"operatingflight"=> "$backmarkettingFN",
																"departure"=> "$backDepartureFrom",
																"departureAirport"=> "$backdAirport",
																"departureLocation"=> "$backdCity , $backdCountry",                    
																"departureTime" => "$backDepartureTime",
																"arrival"=> "$backArrivalTo",                   
																"arrivalTime" => "$backArrivalTime",
																"arrivalAirport"=> "$backaAirport",
																"arrivalLocation"=> "$backaCity , $backaCountry",
																"flightduration"=> "$backTravelTime",
																"bookingcode"=> "$backBookingCode",
																"bags" => "$backBags",
																"seat" => "$backSeat",
																"class" => "$backCabinClass")
														
														

												)
											);

					$basic = array("system" => "Galileo",
									"segment"=> "1",
									"triptype"=>$TripType,
									"career"=> "$validatingCarrierCode",
									"careerName" => "$CarrieerName",
									"baseprice" => "$BasePrice",
									"taxes" => "$Taxes",
									"price" => "$Exact",
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
									"godeparture"=> "$From",                   
									"godepartureTime" => $goDepartureTime,
									"godepartureDate" => $godpTime,
									"goarrival"=> "$To", 
									"goarrivalTime" => "$goArrivalTime1",
									"goarrivalDate" => "$goarrTime1",                
									"backdeparture"=> "$To",                   
									"backdepartureTime" => $backDepartureTime,
									"backdepartureDate" => $backdpTime,
									"backarrival"=> "$From", 
									"backarrivalTime" => "$backArrivalTime1", 
									"backarrivalDate" => $backarrTime1,  
									"goflightduration"=> "$goTravelTime",
									"backflightduration"=> "$backTravelTime",						
									"refundable"=> $Refundable,
									"segments" => $segment
								);
							
							array_push($All,$basic);
						
						}
					}
				}
		}
	}
}


if($FlyHub == 1){

	$FlyHubRequest ='{
	"AdultQuantity": "'.$adult.'",
	"ChildQuantity": "'.$child.'",
	"InfantQuantity": "'.$infants.'",
	"EndUserIp": "85.187.128.34",
	"JourneyType": "2",
	"Segments": [
		{
		"Origin": "'.$From.'",
		"Destination": "'.$To.'",
		"CabinClass": "1",
		"DepartureDateTime": "'.$dDate.'"
		},
		{
		"Origin": "'.$To.'",
		"Destination": "'.$From.'",
		"CabinClass": "1",
		"DepartureDateTime": "'.$rDate.'"
		}
	],
	"PreferredAirlines": [
		""
	]
	}';

	//echo $FlyHubRequest;


	//Fly Hub

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

	$response = curl_exec($curlflyhubauth);

	$TokenJson = json_decode($response,true);

	$FlyhubToken  = $TokenJson['TokenId'];

	$curlflyhusearch = curl_init();

	curl_setopt_array($curlflyhusearch, array(
	CURLOPT_URL => 'https://api.flyhub.com/api/v1/AirSearch',
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => '',
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 0,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => 'POST',
	CURLOPT_POSTFIELDS => $FlyHubRequest,
	CURLOPT_HTTPHEADER => array(
		'Content-Type: application/json',
		"Authorization: Bearer $FlyhubToken"
	),
	));

	$flyhubresponse = curl_exec($curlflyhusearch);

	curl_close($curlflyhusearch);

	//echo $flyhubresponse;
	
	// Decode the JSON file
	$Result = json_decode($flyhubresponse,true);

	
	$FlightListFlyHub = $Result['Results'];
	$SearchID = $Result['SearchId'];
	$FlyHubResponse = array();

	//print_r($FlightListFlyHub);
	
	foreach($FlightListFlyHub as $flight){
		$Validatingcarrier = $flight['Validatingcarrier'];

		$sql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$Validatingcarrier' ");
			$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

			if(!empty($row)){
				$CarrieerName = $row['name'];                       
			} 
		
		$segments = count($flight['segments']);
		$TotalFare = $flight['TotalFare'];
		$Hold = $flight['HoldAllowed'];

		if($adult>0 && $child>0 && $infants >0 ){
			$BasePrice = $flight['Fares'][0]['BaseFare'] * $adult + $flight['Fares'][1]['BaseFare'] * $child + $flight['Fares'][2]['BaseFare'] * $infants;
			$Taxes = $flight['Fares'][0]['Tax'] * $adult + $flight['Fares'][1]['Tax'] * $child + $flight['Fares'][2]['Tax'] * $infants;
			$Taxes += $flight['Fares'][0]['OtherCharges']* $adult + $flight['Fares'][1]['OtherCharges'] * $child + $flight['Fares'][2]['OtherCharges'] * $infants;		
			$Taxes +=  $flight['Fares'][0]['ServiceFee']* $adult + $flight['Fares'][1]['ServiceFee'] * $child + $flight['Fares'][2]['ServiceFee'] * $infants;
			
			$adultBasePrice = $flight['Fares'][0]['BaseFare'] + $flight['Fares'][0]['ServiceFee'];
			$adultTaxAmount = $flight['Fares'][0]['Tax'] + $flight['Fares'][0]['OtherCharges'];

			$childBasePrice = $flight['Fares'][1]['BaseFare'] + $flight['Fares'][1]['ServiceFee'];
			$childTaxAmount = $flight['Fares'][1]['Tax'] + $flight['Fares'][1]['OtherCharges'];

			$infantBasePrice = $flight['Fares'][2]['BaseFare'] + $flight['Fares'][2]['ServiceFee'];
			$infantTaxAmount = $flight['Fares'][2]['Tax'] + $flight['Fares'][2]['OtherCharges'];

			$PriceBreakDown = array("0" =>
							array("BaseFare"=> "$adultBasePrice",
								"Tax"=> "$adultTaxAmount",
								"PaxCount"=> $adult,
								"PaxType"=> "ADT",
								"Discount"=> "0",
								"OtherCharges"=> "0",
								"ServiceFee"=> "0")
								,
							"1" =>
							array("BaseFare"=> "$childBasePrice",
								"Tax"=> "$childTaxAmount",
								"PaxCount"=> $child,
								"PaxType"=> "CNN",
								"Discount"=> "0",
								"OtherCharges"=> "0",
								"ServiceFee"=> "0"),
							"2" =>
							array("BaseFare"=> "$infantBasePrice",
								"Tax"=> "$infantTaxAmount",
								"PaxCount"=> $infants,
								"PaxType"=> "INF",
								"Discount"=> "0",
								"OtherCharges"=> "0",
								"ServiceFee"=> "0")                                         
						);
						
			
		}else if($adult> 0 && $child> 0){
			$BasePrice = $flight['Fares'][0]['BaseFare'] * $adult + $flight['Fares'][1]['BaseFare']* $child ;
			$Taxes = $flight['Fares'][0]['Tax']  * $adult + $flight['Fares'][1]['Tax']* $child;
			$Taxes += $flight['Fares'][0]['OtherCharges'] * $adult + $flight['Fares'][1]['OtherCharges'] * $child ;
			$Taxes +=  $flight['Fares'][0]['ServiceFee'] * $adult + $flight['Fares'][1]['ServiceFee'] * $child ;

			$adultBasePrice = $flight['Fares'][0]['BaseFare'] + $flight['Fares'][0]['ServiceFee'];
			$adultTaxAmount = $flight['Fares'][0]['Tax'] + $flight['Fares'][0]['OtherCharges'];

			$childBasePrice = $flight['Fares'][1]['BaseFare'] + $flight['Fares'][1]['ServiceFee'];
			$childTaxAmount = $flight['Fares'][1]['Tax'] + $flight['Fares'][1]['OtherCharges'];

			$PriceBreakDown = array("0" =>
							array("BaseFare"=> "$adultBasePrice",
								"Tax"=> "$adultTaxAmount",
								"PaxCount"=> $adult,
								"PaxType"=> "ADT",
								"Discount"=> "0",
								"OtherCharges"=> "0",
								"ServiceFee"=> "0")
								,
							"1" =>
							array("BaseFare"=> "$childBasePrice",
								"Tax"=> "$childTaxAmount",
								"PaxCount"=> $child,
								"PaxType"=> "CNN",
								"Discount"=> "0",
								"OtherCharges"=> "0",
								"ServiceFee"=> "0")                                     
						);
			
			
			
		}else if($adult>0 && $infants>0){
			$BasePrice = $flight['Fares'][0]['BaseFare'] * $adult + $flight['Fares'][1]['BaseFare']* $infants ;
			$Taxes = $flight['Fares'][0]['Tax']  * $adult + $flight['Fares'][1]['Tax']* $infants;
			$Taxes += $flight['Fares'][0]['OtherCharges'] * $adult + $flight['Fares'][1]['OtherCharges'] * $infants ;
			$Taxes +=  $flight['Fares'][0]['ServiceFee'] * $adult + $flight['Fares'][1]['ServiceFee'] * $infants ;

			$adultBasePrice = $flight['Fares'][0]['BaseFare'] + $flight['Fares'][0]['ServiceFee'];
			$adultTaxAmount = $flight['Fares'][0]['Tax'] + $flight['Fares'][0]['OtherCharges'];


			$infantBasePrice = $flight['Fares'][1]['BaseFare'] + $flight['Fares'][1]['ServiceFee'];
			$infantTaxAmount = $flight['Fares'][1]['Tax'] + $flight['Fares'][1]['OtherCharges'];

			$PriceBreakDown = array("0" =>
							array("BaseFare"=> "$adultBasePrice",
								"Tax"=> "$adultTaxAmount",
								"PaxCount"=> $adult,
								"PaxType"=> "ADT",
								"Discount"=> "0",
								"OtherCharges"=> "0",
								"ServiceFee"=> "0"),
							"1" =>
							array("BaseFare"=> "$infantBasePrice",
								"Tax"=> "$infantTaxAmount",
								"PaxCount"=> $infants,
								"PaxType"=> "INF",
								"Discount"=> "0",
								"OtherCharges"=> "0",
								"ServiceFee"=> "0")                                         
						);
			

		}else if(isset($flight['Fares'][0])){
			$BasePrice = $flight['Fares'][0]['BaseFare']  * $adult;
			$Taxes = $flight['Fares'][0]['Tax']  * $adult;					
			$Taxes += $flight['Fares'][0]['OtherCharges']  * $adult;
		    $Taxes +=  $flight['Fares'][0]['ServiceFee']  * $adult;

			$adultBasePrice = $flight['Fares'][0]['BaseFare'] + $flight['Fares'][0]['ServiceFee'];
			$adultTaxAmount = $flight['Fares'][0]['Tax'] + $flight['Fares'][0]['OtherCharges'];
			$PriceBreakDown = array("0" =>
							array("BaseFare"=> "$adultBasePrice",
								"Tax"=> "$adultTaxAmount",
								"PaxCount"=> $adult,
								"PaxType"=> "ADT",
								"Discount"=> "0",
								"OtherCharges"=> "0",
								"ServiceFee"=> "0")								                                       
						);
		
		}


		$ClientFare = $BasePrice + $Taxes;
		$Commission = $ClientFare - $TotalFare;

		
		if($flight['IsRefundable'] == 1){
			$Refundable = "Refundable";
		}else{
			$Refundable = "Nonrefundable";
		}
		
		$Availabilty = $flight['Availabilty'];
		$ResultID = $flight['ResultID'];
		
		
		if($segments == 2){

			//Go Leg1
			$godAirportCode = $flight['segments'][0]['Origin']['Airport']['AirportCode'];
			$godAirportName = $flight['segments'][0]['Origin']['Airport']['AirportName'];
			$godCityName = $flight['segments'][0]['Origin']['Airport']['CityName'];
			$godCountryCode = $flight['segments'][0]['Origin']['Airport']['CountryCode'];

			$goaAirportCode = $flight['segments'][0]['Destination']['Airport']['AirportCode'];
			$goaAirportName = $flight['segments'][0]['Destination']['Airport']['AirportName'];
			$goaCityName = $flight['segments'][0]['Destination']['Airport']['CityName'];
			$goaCountryCode = $flight['segments'][0]['Destination']['Airport']['CountryCode'];

			$goDepTime = $flight['segments'][0]['Origin']['DepTime'];
			$goArrTime = $flight['segments'][0]['Destination']['ArrTime'];

			$goAirlineCode = $flight['segments'][0]['Airline']['AirlineCode'];
			$goAirlineName = $flight['segments'][0]['Airline']['AirlineName'];
			$goFlightNumber = $flight['segments'][0]['Airline']['FlightNumber'];
			$goBookingClass = $flight['segments'][0]['Airline']['BookingClass'];
			$goCabinClass= $flight['segments'][0]['Airline']['CabinClass'];
			$goOperatingCarrier = $flight['segments'][0]['Airline']['OperatingCarrier'];

			if(isset($flight['segments'][0]['baggageDetails'][0]['Checkin'])){				
				$goBaggage = $flight['segments'][0]['baggageDetails'][0]['Checkin'];
			} else {
				$goBaggage = 0;
			}

			
			//$goBaggage = $flight['segments'][0]['baggageDetails'][0]['Checkin'];
			$goJourneyDuration = $flight['segments'][0]['JourneyDuration'];
			$goDuration = floor($goJourneyDuration / 60)."H ".($goJourneyDuration - ((floor($goJourneyDuration / 60)) * 60))."Min";


			//Back Leg 1

			$backdAirportCode = $flight['segments'][1]['Origin']['Airport']['AirportCode'];
			$backdAirportName = $flight['segments'][1]['Origin']['Airport']['AirportName'];
			$backdCityName = $flight['segments'][1]['Origin']['Airport']['CityName'];
			$backdCountryCode = $flight['segments'][1]['Origin']['Airport']['CountryCode'];

			$backaAirportCode = $flight['segments'][1]['Destination']['Airport']['AirportCode'];
			$backaAirportName = $flight['segments'][1]['Destination']['Airport']['AirportName'];
			$backaCityName = $flight['segments'][1]['Destination']['Airport']['CityName'];
			$backaCountryCode = $flight['segments'][1]['Destination']['Airport']['CountryCode'];


			$backDepTime = $flight['segments'][1]['Origin']['DepTime'];
			$backArrTime = $flight['segments'][1]['Destination']['ArrTime'];

			$backAirlineCode = $flight['segments'][1]['Airline']['AirlineCode'];
			$backAirlineName = $flight['segments'][1]['Airline']['AirlineName'];
			$backFlightNumber = $flight['segments'][1]['Airline']['FlightNumber'];
			$backBookingClass = $flight['segments'][1]['Airline']['BookingClass'];
			$backCabinClass= $flight['segments'][1]['Airline']['CabinClass'];
			$backOperatingCarrier = $flight['segments'][1]['Airline']['OperatingCarrier'];

			if(isset($flight['segments'][1]['baggageDetails'][0]['Checkin'])){				
				$backBaggage = $flight['segments'][1]['baggageDetails'][0]['Checkin'];
			} else {
				$backBaggage = 0;
			}

			//$backBaggage = $flight['segments'][0]['baggageDetails'][1]['Checkin'];
			$backJourneyDuration = $flight['segments'][1]['JourneyDuration'];
			$backDuration = floor($backJourneyDuration / 60)."H ".($backJourneyDuration - ((floor($backJourneyDuration / 60)) * 60))."Min";


						
			$segment = array("go" =>
								 array("0" =>
										array("marketingcareer"=> "$goOperatingCarrier",
											  "marketingcareerName"=> "$goAirlineName",
											  "marketingflight"=> "$goFlightNumber ",
												"operatingcareer"=> "$goOperatingCarrier",
												"operatingflight"=> "$goFlightNumber",
												"departure"=> "$godAirportCode ",
												"departureAirport"=> "$godAirportName",
												"departureLocation"=> "$godCityName , $godCountryCode",                    
												"departureTime" => "$goDepTime",
												"arrival"=> "$goaAirportCode",                   
												"arrivalTime" => "$goArrTime",
												"arrivalAirport"=> "$goaAirportName",
												"arrivalLocation"=> "$goaCityName , $goaCountryCode",
												"flightduration"=> "$goDuration",
												"bookingcode"=> "$goBookingClass ",
												"seat"=> "$Availabilty")																										

											),
								"back" => array("0" =>
												array("marketingcareer"=> "$backOperatingCarrier",
													  "marketingcareerName"=> "$backAirlineName",
													   "marketingflight"=> "$backFlightNumber ",
														"operatingcareer"=> "$backOperatingCarrier",
														"operatingflight"=> "$backFlightNumber",
														"departure"=> "$backdAirportCode ",
														"departureAirport"=> "$backdAirportName",
														"departureLocation"=> "$backdCityName , $backdCountryCode",                    
														"departureTime" => "$backDepTime",
														"arrival"=> "$backaAirportCode",                   
														"arrivalTime" => "$backArrTime",
														"arrivalAirport"=> "$backaAirportName",
														"arrivalLocation"=> "$backaCityName , $backaCountryCode",
														"flightduration"=> "$backDuration",
														"bookingcode"=> "$backBookingClass ",
														"seat"=> "$Availabilty")																										

											)
										);

				$basic = array("system" => "FlyHub",
								"segment"=> "1",
								"triptype"=>$TripType,
								"career"=> "$Validatingcarrier",
								"careerName" => "$CarrieerName",
								"BasePrice" => "$BasePrice",
								"Taxes" => "$Taxes",
								"price" => "$TotalFare",
								"clientPrice"=> "$ClientFare",
								"comission"=> "$Commission",
								"pricebreakdown"=> $PriceBreakDown,
								"godeparture"=> "$From",                   
								"godepartureTime" => substr($goDepTime,11,19),
								"godepartureDate" => date("D d M Y", strtotime($goDepTime)),
								"goarrival"=> "$To", 
								"goarrivalTime" => substr($goArrTime,11,19),
								"goarrivalDate" => date("D d M Y", strtotime($goArrTime)),                
								"backdeparture"=> "$To",                   
								"backdepartureTime" => substr($backDepTime,11,19),
								"backdepartureDate" => date("D d M Y", strtotime($backDepTime)),
								"backarrival"=> "$From", 
								"backarrivalTime" => substr($backArrTime,11,19), 
								"backarrivalDate" => date("D d M Y", strtotime($backArrTime)),  
								"goflightduration"=> $goDuration,
								"backflightduration"=> $backDuration,
								"bags" => "$goBaggage|$backBaggage",
								"refundable"=> "$Refundable",
								"segments" => $segment,
								"hold"=> "$Hold",
								"SearchID" => $SearchID,
								"ResultID" => $ResultID
							);

				array_push($FlyHubResponse, $basic);


			
		}else if($segments == 4){

			//Go Leg1
			$godAirportCode = $flight['segments'][0]['Origin']['Airport']['AirportCode'];
			$godAirportName = $flight['segments'][0]['Origin']['Airport']['AirportName'];
			$godCityName = $flight['segments'][0]['Origin']['Airport']['CityName'];
			$godCountryCode = $flight['segments'][0]['Origin']['Airport']['CountryCode'];

			$goaAirportCode = $flight['segments'][0]['Destination']['Airport']['AirportCode'];
			$goaAirportName = $flight['segments'][0]['Destination']['Airport']['AirportName'];
			$goaCityName = $flight['segments'][0]['Destination']['Airport']['CityName'];
			$goaCountryCode = $flight['segments'][0]['Destination']['Airport']['CountryCode'];

			$goDepTime = $flight['segments'][0]['Origin']['DepTime'];
			$goArrTime = $flight['segments'][0]['Destination']['ArrTime'];

			$goAirlineCode = $flight['segments'][0]['Airline']['AirlineCode'];
			$goAirlineName = $flight['segments'][0]['Airline']['AirlineName'];
			$goFlightNumber = $flight['segments'][0]['Airline']['FlightNumber'];
			$goBookingClass = $flight['segments'][0]['Airline']['BookingClass'];
			$goCabinClass= $flight['segments'][0]['Airline']['CabinClass'];
			$goOperatingCarrier = $flight['segments'][0]['Airline']['OperatingCarrier'];

			if(isset($flight['segments'][0]['baggageDetails'][0]['Checkin'])){				
				$goBaggage = $flight['segments'][0]['baggageDetails'][0]['Checkin'];
			} else {
				$goBaggage = 0;
			}

			
			$goJourneyDuration = $flight['segments'][0]['JourneyDuration'];
			$goDuration = floor($goJourneyDuration / 60)."H ".($goJourneyDuration - ((floor($goJourneyDuration / 60)) * 60))."Min";

			//Go Leg1
			$godAirportCode1 = $flight['segments'][1]['Origin']['Airport']['AirportCode'];
			$godAirportName1 = $flight['segments'][1]['Origin']['Airport']['AirportName'];
			$godCityName1 = $flight['segments'][1]['Origin']['Airport']['CityName'];
			$godCountryCode1 = $flight['segments'][1]['Origin']['Airport']['CountryCode'];

			$goaAirportCode1 = $flight['segments'][1]['Destination']['Airport']['AirportCode'];
			$goaAirportName1 = $flight['segments'][1]['Destination']['Airport']['AirportName'];
			$goaCityName1 = $flight['segments'][1]['Destination']['Airport']['CityName'];
			$goaCountryCode1 = $flight['segments'][1]['Destination']['Airport']['CountryCode'];

			$goDepTime1 = $flight['segments'][1]['Origin']['DepTime'];
			$goArrTime1 = $flight['segments'][1]['Destination']['ArrTime'];

			$goAirlineCode1 = $flight['segments'][1]['Airline']['AirlineCode'];
			$goAirlineName1 = $flight['segments'][1]['Airline']['AirlineName'];
			$goFlightNumber1 = $flight['segments'][1]['Airline']['FlightNumber'];
			$goBookingClass1 = $flight['segments'][1]['Airline']['BookingClass'];
			$goCabinClass1 = $flight['segments'][1]['Airline']['CabinClass'];
			$goOperatingCarrier1 = $flight['segments'][1]['Airline']['OperatingCarrier'];

			if(isset($flight['segments'][1]['baggageDetails'][0]['Checkin'])){				
				$goBaggage1 = $flight['segments'][1]['baggageDetails'][0]['Checkin'];
			} else {
				$goBaggage1 = 0;
			}

			$goJourneyDuration1 = $flight['segments'][1]['JourneyDuration'];
			$goDuration1 = floor($goJourneyDuration / 60)."H ".($goJourneyDuration - ((floor($goJourneyDuration / 60)) * 60))."Min";


			//Back Leg 1

			$backdAirportCode = $flight['segments'][2]['Origin']['Airport']['AirportCode'];
			$backdAirportName = $flight['segments'][2]['Origin']['Airport']['AirportName'];
			$backdCityName = $flight['segments'][2]['Origin']['Airport']['CityName'];
			$backdCountryCode = $flight['segments'][2]['Origin']['Airport']['CountryCode'];

			$backaAirportCode = $flight['segments'][2]['Destination']['Airport']['AirportCode'];
			$backaAirportName = $flight['segments'][2]['Destination']['Airport']['AirportName'];
			$backaCityName = $flight['segments'][2]['Destination']['Airport']['CityName'];
			$backaCountryCode = $flight['segments'][2]['Destination']['Airport']['CountryCode'];


			$backDepTime = $flight['segments'][2]['Origin']['DepTime'];
			$backArrTime = $flight['segments'][2]['Destination']['ArrTime'];

			$backAirlineCode = $flight['segments'][2]['Airline']['AirlineCode'];
			$backAirlineName = $flight['segments'][2]['Airline']['AirlineName'];
			$backFlightNumber = $flight['segments'][2]['Airline']['FlightNumber'];
			$backBookingClass = $flight['segments'][2]['Airline']['BookingClass'];
			$backCabinClass= $flight['segments'][2]['Airline']['CabinClass'];
			$backOperatingCarrier = $flight['segments'][2]['Airline']['OperatingCarrier'];

			if(isset($flight['segments'][2]['baggageDetails'][0]['Checkin'])){				
				$backBaggage = $flight['segments'][2]['baggageDetails'][0]['Checkin'];
			} else {
				$backBaggage = 0;
			}
			
			
			$backJourneyDuration = $flight['segments'][2]['JourneyDuration'];
			$backDuration = floor($goJourneyDuration / 60)."H ".($goJourneyDuration - ((floor($goJourneyDuration / 60)) * 60))."Min";


			//Back Leg 2

			$backdAirportCode1 = $flight['segments'][3]['Origin']['Airport']['AirportCode'];
			$backdAirportName1 = $flight['segments'][3]['Origin']['Airport']['AirportName'];
			$backdCityName1 = $flight['segments'][3]['Origin']['Airport']['CityName'];
			$backdCountryCode1 = $flight['segments'][3]['Origin']['Airport']['CountryCode'];

			$backaAirportCode1 = $flight['segments'][3]['Destination']['Airport']['AirportCode'];
			$backaAirportName1 = $flight['segments'][3]['Destination']['Airport']['AirportName'];
			$backaCityName1 = $flight['segments'][3]['Destination']['Airport']['CityName'];
			$backaCountryCode1 = $flight['segments'][3]['Destination']['Airport']['CountryCode'];


			$backDepTime1 = $flight['segments'][3]['Origin']['DepTime'];
			$backArrTime1 = $flight['segments'][3]['Destination']['ArrTime'];

			$backAirlineCode1 = $flight['segments'][3]['Airline']['AirlineCode'];
			$backAirlineName1 = $flight['segments'][3]['Airline']['AirlineName'];
			$backFlightNumber1 = $flight['segments'][3]['Airline']['FlightNumber'];
			$backBookingClass1 = $flight['segments'][3]['Airline']['BookingClass'];
			$backCabinClass1 = $flight['segments'][3]['Airline']['CabinClass'];
			$backOperatingCarrier1 = $flight['segments'][3]['Airline']['OperatingCarrier'];

			if(isset($flight['segments'][3]['baggageDetails'][0]['Checkin'])){				
				$backBaggage1 = $flight['segments'][3]['baggageDetails'][0]['Checkin'];
			} else {
				$backBaggage1 = 0;
			}

			//$backBaggage1 = $flight['segments'][3]['baggageDetails'][0]['Checkin'];
			$backJourneyDuration1 = $flight['segments'][3]['JourneyDuration'];
			$backDuration1 = floor($goJourneyDuration / 60)."H ".($goJourneyDuration - ((floor($goJourneyDuration / 60)) * 60))."Min";

			
			// Go Transit1
				
			$goTransitTime = round(abs(strtotime($goDepTime1) - strtotime($goArrTime)) / 60,2);
			$goTransitDuration = floor($goTransitTime / 60)."H ".($goTransitTime - ((floor($goTransitTime / 60)) * 60))."Min";

			
				
			// Back Transit 1
			$backTransitTime = round(abs(strtotime($backDepTime1) - strtotime($backArrTime)) / 60,2);
			$backTransitDuration = floor($backTransitTime / 60)."H ".($backTransitTime - ((floor($backTransitTime / 60)) * 60))."Min";

			// go Journey
			$goJourneyTime = $goJourneyDuration + $goJourneyDuration1 + $goTransitTime;
			$goTotalDuration = floor($goJourneyTime / 60)."H ".($goJourneyTime - ((floor($goJourneyTime / 60)) * 60))."Min";

			// back Journey
			$backJourneyTime = $backJourneyDuration + $backJourneyDuration1 + $backTransitTime;
			$backTotalDuration = floor($backJourneyTime / 60)."H ".($backJourneyTime - ((floor($backJourneyTime / 60)) * 60))."Min";

			
			
			$transitDetails = array("go"=> array("transit1"=> $goTransitDuration),
									"back"=> array("transit1"=> $backTransitDuration)
									);

			$segment = array("go" =>
								 array("0" =>
										array("marketingcareer"=> "$goOperatingCarrier",
										      "marketingcareerName"=> "$goAirlineName",
											  "marketingflight"=> "$goFlightNumber ",
												"operatingcareer"=> "$goOperatingCarrier",
												"operatingflight"=> "$goFlightNumber",
												"departure"=> "$godAirportCode ",
												"departureAirport"=> "$godAirportName",
												"departureLocation"=> "$godCityName , $godCountryCode",                    
												"departureTime" => "$goDepTime",
												"arrival"=> "$goaAirportCode",                   
												"arrivalTime" => "$goArrTime",
												"arrivalAirport"=> "$goaAirportName",
												"arrivalLocation"=> "$goaCityName , $goaCountryCode",
												"flightduration"=> "$goDuration",
												"bookingcode"=> "$goBookingClass ",
												"seat"=> "$Availabilty"),
										"1" =>
										array("marketingcareer"=> "$goOperatingCarrier1",
										 	  "marketingcareerName"=> "$goAirlineName1",
											  "marketingflight"=> "$goFlightNumber1",
												"operatingcareer"=> "$goOperatingCarrier1",
												"operatingflight"=> "$goFlightNumber1",
												"departure"=> "$godAirportCode1",
												"departureAirport"=> "$godAirportName1",
												"departureLocation"=> "$godCityName1 , $godCountryCode1",                    
												"departureTime" => "$goDepTime1",
												"arrival"=> "$goaAirportCode1",                   
												"arrivalTime" => "$goArrTime1",
												"arrivalAirport"=> "$goaAirportName1",
												"arrivalLocation"=> "$goaCityName1 , $goaCountryCode1",
												"flightduration"=> "$goDuration1",
												"bookingcode"=> "$goBookingClass1",
												"seat"=> "$Availabilty")																										

											),
								"back" => array("0" =>
												array("marketingcareer"=> "$backOperatingCarrier",
												      "marketingcareerName"=> "$backAirlineName",
													  "marketingflight"=> "$backFlightNumber",
														"operatingcareer"=> "$backOperatingCarrier",
														"operatingflight"=> "$backFlightNumber",
														"departure"=> "$backdAirportCode ",
														"departureAirport"=> "$backdAirportName",
														"departureLocation"=> "$backdCityName , $backdCountryCode",                    
														"departureTime" => "$backDepTime",
														"arrival"=> "$backaAirportCode",                   
														"arrivalTime" => "$backArrTime",
														"arrivalAirport"=> "$backaAirportName",
														"arrivalLocation"=> "$backaCityName , $backaCountryCode",
														"flightduration"=> "$backDuration",
														"bookingcode"=> "$backBookingClass ",
														"seat"=> "$Availabilty"),
												"1" =>
												array("marketingcareer"=> "$backOperatingCarrier1",
												      "marketingcareerName"=> "$backAirlineName1",
													"marketingflight"=> "$backFlightNumber1",
														"operatingcareer"=> "$backOperatingCarrier1",
														"operatingflight"=> "$backFlightNumber1",
														"departure"=> "$backdAirportCode1",
														"departureAirport"=> "$backdAirportName1",
														"departureLocation"=> "$backdCityName1 , $backdCountryCode1",                    
														"departureTime" => "$backDepTime1",
														"arrival"=> "$backaAirportCode1",                   
														"arrivalTime" => "$backArrTime1",
														"arrivalAirport"=> "$backaAirportName1",
														"arrivalLocation"=> "$backaCityName1 , $backaCountryCode1",
														"flightduration"=> "$backDuration1",
														"bookingcode"=> "$backBookingClass1",
														"seat"=> "$Availabilty")																										

											)
										);

				$basic = array("system" => "FlyHub",
								"segment"=> "2",
								"triptype"=>$TripType,
								"career"=> "$Validatingcarrier",
								"careerName" => "$CarrieerName",
								"BasePrice" => "$BasePrice",
								"Taxes" => "$Taxes",
								"price" => "$TotalFare",
								"clientPrice"=> "$ClientFare",
								"comission"=> "$Commission",
								"pricebreakdown"=> $PriceBreakDown,
								"godeparture"=> "$From",                   
								"godepartureTime" => substr($goDepTime,11,19),
								"godepartureDate" => date("D d M Y", strtotime($goDepTime)),
								"goarrival"=> "$To", 
								"goarrivalTime" => substr($goArrTime1,11,19),
								"goarrivalDate" => date("D d M Y", strtotime($goArrTime1)),                
								"backdeparture"=> "$To",                   
								"backdepartureTime" => substr($backDepTime,11,19),
								"backdepartureDate" => date("D d M Y", strtotime($backDepTime)),
								"backarrival"=> "$From", 
								"backarrivalTime" => substr($backArrTime1,11,19), 
								"backarrivalDate" => date("D d M Y", strtotime($backArrTime1)),  
								"goflightduration"=> $goDuration,
								"backflightduration"=> $backDuration,
								"transit" => $transitDetails,
								"bags" => "$goBaggage | $backBaggage",
								"refundable"=> "$Refundable",
								"segments" => $segment,
								"hold"=> "$Hold",
								"SearchID" => $SearchID,
								"ResultID" => $ResultID
							);

										
						array_push($FlyHubResponse, $basic);


					}

				}
			}



			if($Sabre == 1 && $Galileo == 1 && $FlyHub ==  1){				
				$AllItenary = array_merge($FlyHubResponse, $All);
				array_multisort(array_column($AllItenary, 'price'), SORT_ASC, $AllItenary);
				$json_string = json_encode($AllItenary, JSON_PRETTY_PRINT);
				print_r($json_string);

			}else if($Sabre == 1 && $Galileo == 1){
				array_multisort(array_column($All, 'price'), SORT_ASC, $All);
				$json_string = json_encode($All, JSON_PRETTY_PRINT);
				print_r($json_string);
				
			}else if($Sabre == 1 && $FlyHub ==  1){
				$AllItenary = array_merge($FlyHubResponse, $All);
				array_multisort(array_column($AllItenary, 'price'), SORT_ASC, $AllItenary);
				$json_string = json_encode($AllItenary, JSON_PRETTY_PRINT);
				print_r($json_string);
			}else if($Galileo == 1 && $FlyHub ==  1){
				$AllItenary = array_merge($FlyHubResponse, $All);
				array_multisort(array_column($AllItenary, 'price'), SORT_ASC, $AllItenary);
				$json_string = json_encode($AllItenary, JSON_PRETTY_PRINT);
				print_r($json_string);
			}else if($Sabre == 1 || $Galileo == 1){
				$json_string = json_encode($All, JSON_PRETTY_PRINT);
				print_r($json_string);
				
			}else if($FlyHub ==  1){
				$json_string = json_encode($FlyHubResponse, JSON_PRETTY_PRINT);
				print_r($json_string);
				
			}
			

}

$conn->close();
?>