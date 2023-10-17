<?php

include "../../config.php";
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$All= array();
$FlightType;

$control = mysqli_query($conn,"SELECT * FROM control where id=1");
$controlrow = mysqli_fetch_array($control,MYSQLI_ASSOC);

if(!empty($controlrow)){
	$Sabre = $controlrow['sabre'];						
}

$Airportsql =  "SELECT name, cityName,countryCode FROM airports WHERE";

  
if(array_key_exists("oneway",$_GET) && array_key_exists("journeyfrom",$_GET) && array_key_exists("journeyto",$_GET) && array_key_exists("departuredate",$_GET) && array_key_exists("adult",$_GET) && array_key_exists("child",$_GET) && array_key_exists("infant",$_GET)){
	$From = $_GET['journeyfrom'];
	$To = $_GET['journeyto'];
	$Date = $_GET['departuredate'];
	$ActualDate = $Date."T00:00:00";
	$adult = $_GET['adult'];
	$child = $_GET['child'];
	$infants = $_GET['infant'];


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


	$ComissionType = '';
	if($fromCountry == "BD" && $toCountry =="BD"){
		$ComissionType = "domestic";
	}else if($fromCountry != 'BD' && $toCountry != 'BD' ){
		$ComissionType = "sotto";
	}else if($fromCountry != 'BD' && $toCountry == 'BD'){
		$ComissionType = "sotti";
	}else if($fromCountry == 'BD' && $toCountry != 'BD'){
		$ComissionType = "sitti";
	}					
		


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
					
			
	}else if($adult > 0 && $child > 0){

	$SabreRequest = '{
					"Code": "ADT",
					"Quantity": '.$adult.'
				},
				{
					"Code": "C09",
					"Quantity": '.$child.'
				}';

	
	}else if($adult > 0 && $infants > 0){
	$SabreRequest = '{
				"Code": "ADT",
				"Quantity": '.$adult.'
				},
				{
					"Code": "INF",
					"Quantity": '.$infants.'
				}';
	

	}else{
		$SabreRequest = '{
					"Code": "ADT",
					"Quantity": '.$adult.'
				}';
	}

	$jsonreq = '{
		"OTA_AirLowFareSearchRQ": {
			"Version": "4",
			"POS": {
				"Source": [{
						"PseudoCityCode": "14KK",
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
			"OriginDestinationInformation": [{
					"RPH": "1",
					"DepartureDateTime": "'.$ActualDate.'",
					"OriginLocation": {
						"LocationCode": "'.$From.'"
					},
					"DestinationLocation": {
						"LocationCode": "'.$To.'"
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
						"Name": "50ITINS"
					} 
				}
			}
		}
	}';
	


	if($Sabre == 1){
		$client_id= base64_encode("V1:593072:14KK:AA");
		//$client_secret = base64_encode("280ff537"); //cert
		$client_secret = base64_encode("f270395"); //prod

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
            CURLOPT_POSTFIELDS => $jsonreq,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Conversation-ID: 2021.01.DevStudio',
                        'Authorization: Bearer '.$access_token,
            ),
			));

			$response = curl_exec($curl);
			curl_close($curl);
			$result = json_decode($response, true);
		}
			
			
		if(isset($result['groupedItineraryResponse']['statistics']['itineraryCount']) && $result['groupedItineraryResponse']['statistics']['itineraryCount'] > 0){
			$SabreItenary = $result['groupedItineraryResponse']['itineraryGroups'];
			$flightListSabre = $SabreItenary[0]['itineraries'];
			$scheduleDescs = $result['groupedItineraryResponse']['scheduleDescs'];
			$legDescs = $result['groupedItineraryResponse']['legDescs'];

			$Bag = $result['groupedItineraryResponse']['baggageAllowanceDescs'];
			
			foreach($flightListSabre as $var){

				$System = 'Sabre';
				$pricingSource = $var['pricingSource'];
				$vCarCode = $var['pricingInformation'][0]['fare']['validatingCarrierCode'];
				
				if(isset($var['pricingInformation'][0]['fare']['lastTicketDate'])
						&& isset($var['pricingInformation'][0]['fare']['lastTicketTime'])){
						
					$lastTicketDate = $var['pricingInformation'][0]['fare']['lastTicketDate'];
					$lastTicketTime = $var['pricingInformation'][0]['fare']['lastTicketTime'];
					$timelimit = "$lastTicketDate $lastTicketTime";								
				}else{
					$timelimit = " ";
				}


				$Commisionrow = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM airlines WHERE code='$vCarCode' "),MYSQLI_ASSOC);

				$comissionvalue;
				$FareCurrency;
				$comRef;
				if(!empty($Commisionrow)){
					$CarrieerName = $Commisionrow['name'];
					$fareRate= $Commisionrow['commission'];
					$FareCurrency =	$Commisionrow[$ComissionType.'currency'] != ''? $Commisionrow[$ComissionType.'currency'] : 'BDT';
					$comissionvalue = $Commisionrow["sabre".$ComissionType];
					$additional = $Commisionrow["sabreaddamount"];
					$comRef = $Commisionrow["ref_id"];						
				}else{
					$fareRate= 7;
					$FareCurrency = 'BDT';
					$comissionvalue = 0;
					$additional = 0;
					$comRef = 'NA';			
				}

				if($comissionvalue > 0){
					$Ait = 0.003;
				}else{
					$Ait = 0;
				}

				
				
				$passengerInfo =  $var['pricingInformation'][0]['fare']['passengerInfoList'][0]['passengerInfo'];
				$fareComponents = $passengerInfo['fareComponents'];

				$Class = $fareComponents[0]['segments'][0]['segment']['cabinCode'];			
				$BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
				$Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
				
				
				$PriceInfo = $var['pricingInformation'][0]['fare']['passengerInfoList'];

				$baseFareAmount = $var['pricingInformation'][0]['fare']['totalFare']['equivalentAmount'];
				$totalTaxAmount = $var['pricingInformation'][0]['fare']['totalFare']['totalTaxAmount'];							
				$totalFare = $var['pricingInformation'][0]['fare']['totalFare']['totalPrice'];
				
				$AgentPrice = FareRulesPolicy($comissionvalue, $FareCurrency, $Ait, $baseFareAmount, $totalTaxAmount) + $additional;
				$Commission = $totalFare - $AgentPrice;

				$diff = 0;
				$OtherCharges = 0;
				if($AgentPrice > $totalFare){
					$diff = $AgentPrice - $totalFare;
					$Pax = $adult + $child +  $infants;
					$OtherCharges = $diff / $Pax;
					$totalFare  = $AgentPrice;
				}


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
										"OtherCharges"=> "$OtherCharges",
										"ServiceFee"=> "0")
										,
										"1" =>
										array("BaseFare"=> "$childBasePrice",
										"Tax"=> "$childTaxAmount",
										"PaxCount"=> $child,
										"PaxType"=> "CNN",
										"Discount"=> "0",
										"OtherCharges"=> "$OtherCharges",
										"ServiceFee"=> "0"),
										"2" =>
										array("BaseFare"=> "$infantBasePrice",
										"Tax"=> "$infantTaxAmount",
										"PaxCount"=> $infants,
										"PaxType"=> "INF",
										"Discount"=> "0",
										"OtherCharges"=> "$OtherCharges",
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
										"OtherCharges"=> "$OtherCharges",
										"ServiceFee"=> "0")
										,
										"1" =>
										array("BaseFare"=> "$childBasePrice",
										"Tax"=> "$childTaxAmount",
										"PaxCount"=> $child,
										"PaxType"=> "CNN",
										"Discount"=> "0",
										"OtherCharges"=> "$OtherCharges",
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
										"OtherCharges"=> "$OtherCharges",
										"ServiceFee"=> "0")
										,
										"1" =>
										
										array("BaseFare"=> "$infantBasePrice",
										"Tax"=> "$infantTaxAmount",
										"PaxCount"=> $infants,
										"PaxType"=> "INF",
										"Discount"=> "0",
										"OtherCharges"=> "$OtherCharges",
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
										"OtherCharges"=> "$OtherCharges",
										"ServiceFee"=> "0")												                                     

								);
				}

				
									
				$Segment = $fareComponents[0]['segments'];

				if(isset($passengerInfo['baggageInformation'][0]['allowance']['ref'])){
					$BegRef= $passengerInfo['baggageInformation'][0]['allowance']['ref'];           
					$BegId = $BegRef - 1;
					if(isset($Bag[$BegId]['weight'])){
						$Bags = $Bag[$BegId]['weight'];
					}else if(isset($Bag[$BegId]['pieceCount'])){
						$Bags = $Bag[$BegId]['pieceCount'];
					}else{
						$Bags = "0";
					}
				}else{
					$Bags = "0";
				}

				if($Class == 'Y'){
					$CabinClass = "Economy";
				}
				

				$nonRefundable = $passengerInfo['nonRefundable'];
				if($nonRefundable == 1){
					$nonRef = "Nonrefundable";
			
				}else{
					$nonRef = "Refundable";
				}

				
				$ref = $var['legs'][0]['ref'];
				$id = $ref - 1;

				$sgCount = count($legDescs[$id]['schedules']);
				
				$ElapedTime = $legDescs[$id]['elapsedTime'];
				$JourneyDuration = floor($ElapedTime / 60)."H ".($ElapedTime - ((floor($ElapedTime / 60)) * 60))."Min";

				$uId = sha1(md5(time()).''.rand());
				
				if($sgCount ==  1){								

					$lf = $legDescs[$id]['schedules'][0]['ref'];
					$legref = $lf- 1;

					$ElapsedTime = $scheduleDescs[$legref]['elapsedTime'];
					$TravelTime = floor($ElapsedTime / 60)."H ".($ElapsedTime - ((floor($ElapsedTime / 60)) * 60))."Min";
						
					$ArrivalTime1 = $scheduleDescs[$legref]['arrival']['time'];
					$arrAt2 = substr($ArrivalTime1,0,5);

					$arrivalDate = 0;
					if(isset($scheduleDescs[$legref]['arrival']['dateAdjustment'])){
							$arrivalDate += 1;
					}


					if($arrivalDate == 1){
							$aDate = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
							}else{
							$aDate = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
						}



					$departureTime1 = $scheduleDescs[$legref]['departure']['time'];
					$depAt1 = substr($departureTime1,0,5);

					$fromTime1 = str_split($departureTime1, 8);
					$dpTime1 = date("D d M Y", strtotime($Date." ".$fromTime1[0]));

					$toTime1 = str_split($ArrivalTime1, 8);
					$arrTime2 = date("D d M Y", strtotime($aDate." ".$toTime1[0]));

					$ArrivalTo = $scheduleDescs[$legref]['arrival']['airport'];
					$DepartureFrom = $scheduleDescs[$legref]['departure']['airport'];


					$ArrivalTime = $scheduleDescs[$legref]['arrival']['time'];
					$departureTime = $scheduleDescs[$legref]['departure']['time'];
					$markettingCarrier = $scheduleDescs[$legref]['carrier']['marketing'];

					$sql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier' ");
					$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

					if(!empty($row)){
						$markettingCarrierName = $row['name'];								
					}

					// Departure Country
					$sql1 = mysqli_query($conn,"$Airportsql code='$DepartureFrom' ");
					$row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);

					if(!empty($row1)){
						$dAirport = $row1['name'];
						$dCity = $row1['cityName'];
						$dCountry = $row1['countryCode'];								
					}

					// Arrival Country
					$sql2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
					$row2 = mysqli_fetch_array($sql2,MYSQLI_ASSOC);

					if(!empty($row2)){
						$aAirport = $row2['name'];
						$aCity = $row2['cityName'];
						$aCountry = $row2['countryCode'];
					
					}


					$markettingFN = $scheduleDescs[$legref]['carrier']['marketingFlightNumber'];
					$operatingCarrier = $scheduleDescs[$legref]['carrier']['operating'];
					$operatingFN = $scheduleDescs[$legref]['carrier']['operatingFlightNumber'];

					$opsql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$operatingCarrier' ");
					$oprow = mysqli_fetch_array($opsql,MYSQLI_ASSOC);

					if(!empty($oprow)){
						$operatingCarrierName = $oprow['name'];								
					}

					if(isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
						$Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
					}else if(!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
						$Seat = "Available Seat Invisible";
					}

					$arrivalDate = 0;
					if(isset($scheduleDescs[$legref]['arrival']['dateAdjustment'])){
							$arrivalDate += 1;
					}


					if($arrivalDate == 1){
							$aDate = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
							}else{
							$aDate = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
					}


					$fromTime = str_split($departureTime, 8);
					$dpTime = $Date."T".$fromTime[0];

					$toTime = str_split($ArrivalTime, 8);
					$arrTime = $aDate."T".$toTime[0];

				//Array Push

				$stopOverDetails = array();

				if(isset($scheduleDescs[$legref]['hiddenStops'])){
					$hiddenStopOver = $scheduleDescs[$legref]['hiddenStops'];
				
					foreach($hiddenStopOver as $hidestop){
						$airpot = $hidestop['airport'];
						$duration = $hidestop['elapsedLayoverTime'];
						$time = date('H:i', mktime(0,$duration));
						$stopoverdetails = array("airport" => $airpot,
												"time"=> $time);
						array_push($stopOverDetails, $stopoverdetails);
					}
				}

				

				$segment = array("0" =>
									array("marketingcareer"=> "$markettingCarrier",
										"marketingcareerName"=> "$markettingCarrierName",
										"marketingflight"=> "$markettingFN",
										"operatingcareer"=> "$operatingCarrier",
										"operatingflight"=> "$operatingFN",
										"operatingCarrierName"=> "$operatingCarrierName",
										"departure"=> "$DepartureFrom",
										"departureAirport"=> "$dAirport ",
										"departureLocation"=> "$dCity , $dCountry",                    
										"departureTime" => "$dpTime",
										"arrival"=> "$ArrivalTo",                   
										"arrivalTime" => "$arrTime",
										"arrivalAirport"=> "$aAirport",
										"arrivalLocation"=> "$aCity , $aCountry",
										"flightduration"=> "$TravelTime",
										"bookingcode"=> "$BookingCode",
										"seat"=> "$Seat")                                           

								);
								
				$transitDetails = array("transit1" => "0");

				$basic = array("system"=>"Sabre",
									"segment"=> "1",
									"uId"=> $uId,
									"triptype"=>$TripType,
									"career"=> "$vCarCode",
									"careerName" => "$CarrieerName",
									"lastTicketTime"=> "$timelimit",
									"BasePrice" => "$baseFareAmount",
									"Taxes" => "$totalTaxAmount",
									"price" => "$AgentPrice",
									"clientPrice"=> "$totalFare",
									"comission"=> $Commission,
									"comissiontype"=> $ComissionType,
									"comissionvalue"=> $comissionvalue,
									"farecurrency"=> $FareCurrency,
									"airlinescomref"=> $comRef,
									"pricebreakdown"=> $PriceBreakDown,
									"departure"=> "$From",                   
									"departureTime" => "$depAt1",
									"departureDate" => "$dpTime1",
									"arrival"=> "$To",                   
									"arrivalTime" => "$arrAt2",
									"arrivalDate" => "$arrTime2",
									"flightduration"=> "$JourneyDuration",
									"bags" => "$Bags",
									"seat" => "$Seat",
									"class" => "$CabinClass",
									"refundable"=> "$nonRef",												 
									"segments" => $segment,
									"transit" => $transitDetails,
									"stopover"=> $stopOverDetails
									
							);
							

				}else if($sgCount ==  2){
					
				$lf = $legDescs[$id]['schedules'][0]['ref'];
				$legref = $lf- 1;

				$ArrivalTime1 = $scheduleDescs[$legref]['arrival']['time'];
				$arrAt2 = substr($ArrivalTime1,0,5);

				$arrivalDate1 = 0;
				if(isset($scheduleDescs[$legref]['arrival']['dateAdjustment'])){
						$arrivalDate1 += 1;
				}


				if($arrivalDate1 == 1){
						$aDate = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
						}else{
						$aDate = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
					}

					
				$ElapsedTime1 = $scheduleDescs[$legref]['elapsedTime'];
				$TravelTime1 = floor($ElapsedTime1 / 60)."H ".($ElapsedTime1 - ((floor($ElapsedTime1 / 60)) * 60))."Min";

										
				$ArrivalTime1 = $scheduleDescs[$legref]['arrival']['time'];
				$arrAt1 = substr($ArrivalTime1,0,5);

				$departureTime1 = $scheduleDescs[$legref]['departure']['time'];
				$depAt1 = substr($departureTime1,0,5);

				$fromTime1 = str_split($departureTime1, 8);
				$dpTime1 = date("D d M Y", strtotime($Date." ".$fromTime1[0]));

				$toTime1 = str_split($ArrivalTime1, 8);
				$arrTime1 = date("D d M Y", strtotime($aDate." ".$toTime1[0]));


				$ArrivalTo = $scheduleDescs[$legref]['arrival']['airport'];
					$DepartureFrom = $scheduleDescs[$legref]['departure']['airport'];


					$ArrivalTime = $scheduleDescs[$legref]['arrival']['time'];
					$departureTime = $scheduleDescs[$legref]['departure']['time'];
					

					

					// Departure Country
					$sql1 = mysqli_query($conn,"$Airportsql code='$DepartureFrom' ");
					$row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);

					if(!empty($row1)){
					$dAirport = $row1['name'];
					$dCity = $row1['cityName'];
					$dCountry = $row1['countryCode'];
					
					}

					// Departure Country
					$sql2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
					$row2 = mysqli_fetch_array($sql2,MYSQLI_ASSOC);

					if(!empty($row2)){
						$aAirport = $row2['name'];
						$aCity = $row2['cityName'];
						$aCountry = $row2['countryCode'];					
					}

					$markettingCarrier = $scheduleDescs[$legref]['carrier']['marketing'];
					$markettingFN = $scheduleDescs[$legref]['carrier']['marketingFlightNumber'];
					$operatingCarrier = $scheduleDescs[$legref]['carrier']['operating'];
					$operatingFN = $scheduleDescs[$legref]['carrier']['operatingFlightNumber'];

					$carriersql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier' ");
					$carrierrow = mysqli_fetch_array($carriersql,MYSQLI_ASSOC);

					if(!empty($carrierrow)){
						$markettingCarrierName = $carrierrow['name'];                                            
					}

					$opsql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$operatingCarrier' ");
					$oprow = mysqli_fetch_array($opsql,MYSQLI_ASSOC);

					if(!empty($oprow)){
						$operatingCarrierName = $oprow['name'];								
					}

					if(isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
						$Seat1 = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
					}
					

					if(isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])){
							$BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
					}else{
						$BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
					}
					


					$arrivalDate = 0;
					if(isset($scheduleDescs[$legref]['arrival']['dateAdjustment'])){
							$arrivalDate += 1;
					}


					if($arrivalDate == 1){
							$aDate = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
							}else{
							$aDate = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
					}

					$fromTime = str_split($departureTime, 8);
					$dpTime = $Date."T".$fromTime[0];

					$toTime = str_split($ArrivalTime, 8);
					$arrTime = $aDate."T".$toTime[0];


				//2nd Leg

					$lf2 = $legDescs[$id]['schedules'][1]['ref'];
					$legref1 = $lf2- 1;

			
					$dateAdjust2 = 0 ;
					if(isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])){
						$dateAdjust2 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
					}

					
					//Store Data
					if($dateAdjust2 == 1){
						$NewDate2 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
					}else{
						$NewDate2 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
					}

				$ElapsedTime2 = $scheduleDescs[$legref1]['elapsedTime'];
				$TravelTime2 = floor($ElapsedTime2 / 60)."H ".($ElapsedTime2 - ((floor($ElapsedTime2 / 60)) * 60))."Min";
					
				$ArrivalTime2 = $scheduleDescs[$legref1]['arrival']['time'];
				$arrAt2 = substr($ArrivalTime2,0,5);

				$departureTime2 = $scheduleDescs[$legref1]['departure']['time'];
				$depAt2 = substr($departureTime2,0,5);

				$fromTime2 = str_split($departureTime2, 8);
				$dpTime2 = date("D d M Y", strtotime($NewDate2." ".$fromTime2[0]));

				$toTime2 = str_split($ArrivalTime2, 8);
				$arrTime2 = date("D d M Y", strtotime($NewDate2." ".$toTime2[0]));

				$TransitInt = $ElapedTime - ($ElapsedTime1 + $ElapsedTime2);
				$Transit = floor($TransitInt / 60)."H ".($TransitInt - ((floor($TransitInt / 60)) * 60))."Min";

				$dateAdjust2 = 0;
					if(isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])){
							$dateAdjust2 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
					}

					//$descid = $scheduleDescs[$legref1]['id'];
					$ArrivalTo1 = $scheduleDescs[$legref1]['arrival']['airport']; //echo $ArrivalTo1;
					$DepartureFrom1 = $scheduleDescs[$legref1]['departure']['airport'];


					$ArrivalTime1 = $scheduleDescs[$legref1]['arrival']['time'];
					$departureTime1 = $scheduleDescs[$legref1]['departure']['time'];
					$markettingCarrier1 = $scheduleDescs[$legref1]['carrier']['marketing'];

					$carriersql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
					$carrierrow1 = mysqli_fetch_array($carriersql1,MYSQLI_ASSOC);

					if(!empty($carrierrow1)){
						$markettingCarrierName1 = $carrierrow1['name'];								
					}

					// Departure Country
					$sql3 = mysqli_query($conn,"$Airportsql code='$DepartureFrom1' ");
					$row3 = mysqli_fetch_array($sql3,MYSQLI_ASSOC);

					if(!empty($row3)){
						$dAirport1 = $row3['name'];
						$dCity1 = $row3['cityName'];
						$dCountry1 = $row3['countryCode'];								
					}

					// Departure Country
					$sql4 = mysqli_query($conn,"$Airportsql code='$ArrivalTo1' ");
					$row4 = mysqli_fetch_array($sql4,MYSQLI_ASSOC);

					if(!empty($row4)){
						$aAirport1 = $row4['name'];
						$aCity1 = $row4['cityName'];
						$aCountry1 = $row4['countryCode'];
					
					}


					$markettingFN1 = $scheduleDescs[$legref1]['carrier']['marketingFlightNumber'];
					$operatingCarrier1 = $scheduleDescs[$legref1]['carrier']['operating'];
					$operatingFN1 = $scheduleDescs[$legref1]['carrier']['operatingFlightNumber'];

					$opsql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$operatingCarrier1' ");
					$oprow1 = mysqli_fetch_array($opsql1,MYSQLI_ASSOC);

					if(!empty($oprow)){
						$operatingCarrierName1 = $oprow1['name'];								
					}

					if(isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
						$Seat2 = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
					}

					if(isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])){
							$BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
					}else{
						$BookingCode1 = $fareComponents[0]['segments'][1]['segment']['bookingCode'];
					}

					//Store Data
					if($dateAdjust2 == 1){
						$dDate2 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));                                                   
					}else{
						$dDate2 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
					}

					$arrivalDate2 = 0;
					if(isset($scheduleDescs[$legref1]['arrival']['dateAdjustment'])){
							$arrivalDate2 += 1;
					}


					if($arrivalDate2 == 1){
							$aDate2 = date('Y-m-d', strtotime("+1 day", strtotime($dDate2)));
							}else{
							$aDate2 = date('Y-m-d', strtotime("+0 day", strtotime($dDate2)));
					}


					$fromTime1 = str_split($departureTime1, 8);
					$depTime1 = $dDate2."T".$fromTime1[0];

					$toTime1 = str_split($ArrivalTime1, 8);
					$arrTime1 = $aDate2."T".$toTime1[0];

					//Array

					$transitDetails = array("transit1" => "$Transit");

					$segment = array("0" =>
										array("marketingcareer"=> "$markettingCarrier",
												"marketingcareerName"=> "$markettingCarrierName",
												"marketingflight"=> "$markettingFN",
												"operatingcareer"=> "$operatingCarrier",
												"operatingflight"=> "$operatingFN",
												"operatingCarrierName"=> "$operatingCarrierName",
												"departure"=> "$DepartureFrom",
												"departureAirport"=> "$dAirport ",
												"departureLocation"=> "$dCity , $dCountry",                    
												"departureTime" => "$dpTime",
												"arrival"=> "$ArrivalTo",                   
												"arrivalTime" => "$arrTime",
												"arrivalAirport"=> "$aAirport",
												"arrivalLocation"=> "$aCity , $aCountry",
												"flightduration"=> "$TravelTime1",
												"bookingcode"=> "$BookingCode",
												"seat"=> "$Seat1"),
									"1" =>
										array("marketingcareer"=> "$markettingCarrier1",
												"marketingcareerName"=> "$markettingCarrierName1",
												"marketingflight"=> "$markettingFN1",
												"operatingcareer"=> "$operatingCarrier1",
												"operatingflight"=> "$operatingFN1",
												"operatingCarrierName"=> "$operatingCarrierName1",
												"departure"=> "$DepartureFrom1",
												"departureAirport"=> "$dAirport1",
												"departureLocation"=> "$dCity1 , $dCountry1",                    
												"departureTime" => "$depTime1",
												"arrival"=> "$ArrivalTo1",                   
												"arrivalTime" => "$arrTime1",
												"arrivalAirport"=> "$aAirport1",
												"arrivalLocation"=> "$aCity1 , $aCountry1",
												"flightduration"=> "$TravelTime2",
												"bookingcode"=> "$BookingCode1",
												"seat"=> "$Seat2")
										
										

								);

				$basic = array("system" => "Sabre", 
									"segment"=> "2",
									"uId"=> $uId,
									"triptype"=>$TripType,
									"career"=> "$vCarCode",
									"careerName" => "$CarrieerName",
									"lastTicketTime"=> "$timelimit",
									"BasePrice" => "$baseFareAmount",
									"Taxes" => "$totalTaxAmount",
									"price" => "$AgentPrice",
									"clientPrice"=> "$totalFare",
									"comission"=> "$Commission",
									"comissiontype"=> $ComissionType,
									"comissionvalue"=> $comissionvalue,
									"farecurrency"=> $FareCurrency,
									"airlinescomref"=> $comRef,
									"pricebreakdown"=> $PriceBreakDown,
									"departure"=> "$From",                   
									"departureTime" => "$depAt1",
									"departureDate" => $dpTime1,
									"arrival"=> "$To",                   
									"arrivalTime" => "$arrAt2",
									"arrivalDate" => "$arrTime2",
									"flightduration"=> "$JourneyDuration",
									"bags" => "$Bags",
									"seat" => "$Seat",
									"class" => "$CabinClass",
									"refundable"=> "$nonRef",
									"segments" => $segment,												
									"transit" => $transitDetails
									
							);

				}else if($sgCount ==  3){

					
				$lf = $legDescs[$id]['schedules'][0]['ref'];
				$legref = $lf- 1;

				$ArrivalTime1 = $scheduleDescs[$legref]['arrival']['time'];
				$arrAt2 = substr($ArrivalTime1,0,5);

				$arrivalDate1 = 0;
				if(isset($scheduleDescs[$legref]['arrival']['dateAdjustment'])){
						$arrivalDate1 += 1;
				}


				if($arrivalDate1 == 1){
						$aDate = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
						}else{
						$aDate = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
					}

					
				$ElapsedTime1 = $scheduleDescs[$legref]['elapsedTime'];
				$TravelTime1 = floor($ElapsedTime1 / 60)."H ".($ElapsedTime1 - ((floor($ElapsedTime1 / 60)) * 60))."Min";

				$ArrivalTime1 = $scheduleDescs[$legref]['arrival']['time'];
				$arrAt1 = substr($ArrivalTime1,0,5);

				$departureTime1 = $scheduleDescs[$legref]['departure']['time'];
				$depAt1 = substr($departureTime1,0,5);

				$fromTime1 = str_split($departureTime1, 8);
				$depTimedate1 = date("D d M Y", strtotime($Date." ".$fromTime1[0]));

				$toTime1 = str_split($ArrivalTime1, 8);
				$arrTimedate1 = date("D d M Y", strtotime($aDate." ".$toTime1[0]));

				$ArrivalTo = $scheduleDescs[$legref]['arrival']['airport'];
					$DepartureFrom = $scheduleDescs[$legref]['departure']['airport'];


					$ArrivalTime = $scheduleDescs[$legref]['arrival']['time'];
					$departureTime = $scheduleDescs[$legref]['departure']['time'];
					$markettingCarrier = $scheduleDescs[$legref]['carrier']['marketing'];

					$carriersql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier' ");
					$carrierrow = mysqli_fetch_array($carriersql,MYSQLI_ASSOC);

					if(!empty($carrierrow)){
						$markettingCarrierName = $carrierrow['name'];                                            
					}

					// Departure Country
					$sql1 = mysqli_query($conn,"$Airportsql code='$DepartureFrom' ");
					$row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);

					if(!empty($row1)){
					$dAirport = $row1['name'];
					$dCity = $row1['cityName'];
					$dCountry = $row1['countryCode'];
					
					}

					// Departure Country
					$sql2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
					$row2 = mysqli_fetch_array($sql2,MYSQLI_ASSOC);

					if(!empty($row2)){
					$aAirport = $row2['name'];
					$aCity = $row2['cityName'];
					$aCountry = $row2['countryCode'];
					
					}


					$markettingFN = $scheduleDescs[$legref]['carrier']['marketingFlightNumber'];
					$operatingCarrier = $scheduleDescs[$legref]['carrier']['operating'];
					if(isset($scheduleDescs[$legref]['carrier']['operatingFlightNumber'])){
						$operatingFN = $scheduleDescs[$legref]['carrier']['operatingFlightNumber'];
					}else{
						$operatingFN = $scheduleDescs[$legref]['carrier']['marketingFlightNumber'];
					}

					$opsql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$operatingCarrier' ");
					$oprow = mysqli_fetch_array($opsql,MYSQLI_ASSOC);

					if(!empty($oprow)){
						$operatingCarrierName = $oprow['name'];								
					}

					if(isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
						$Seat1 = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
					}
					

					if(isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])){
							$BookingCode1 = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
					}else{
						$BookingCode1 = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
					}

					$arrivalDate = 0;
					if(isset($scheduleDescs[$legref]['arrival']['dateAdjustment'])){
							$arrivalDate += 1;
					}


					if($arrivalDate == 1){
							$aDate = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
							}else{
							$aDate = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
					}


					$fromTime = str_split($departureTime, 8);
					$dpTime = $Date."T".$fromTime[0];

					$toTime = str_split($ArrivalTime, 8);
					$arrTime = $aDate."T".$toTime[0];



				//2nd Leg

					$lf2 = $legDescs[$id]['schedules'][1]['ref'];
					$legref1 = $lf2- 1;

			
					$dateAdjust2 = 0 ;
					if(isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])){
						$dateAdjust2 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
					}

					
					//Store Data
					if($dateAdjust2 == 1){
						$NewDate2 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
					}else{
						$NewDate2 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
					}

				$ElapsedTime2 = $scheduleDescs[$legref1]['elapsedTime'];
				$TravelTime2 = floor($ElapsedTime2 / 60)."H ".($ElapsedTime2 - ((floor($ElapsedTime2 / 60)) * 60))."Min";
					
				$ArrivalTime2 = $scheduleDescs[$legref1]['arrival']['time'];
				$arrAt2 = substr($ArrivalTime2,0,5);

				$departureTime2 = $scheduleDescs[$legref1]['departure']['time'];
				$depAt2 = substr($departureTime2,0,5);

				$fromTime2 = str_split($departureTime2, 8);
				$depTimedate2 = date("D d M Y", strtotime($NewDate2." ".$fromTime2[0]));

				$toTime2 = str_split($ArrivalTime2, 8);
				$arrTimedate2 = date("D d M Y", strtotime($NewDate2." ".$toTime2[0]));


				$dateAdjust2 = 0;
					if(isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])){
							$dateAdjust2 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
					}

					$descid = $scheduleDescs[$legref1]['id'];
					$ArrivalTo1 = $scheduleDescs[$legref1]['arrival']['airport'];
					$DepartureFrom1 = $scheduleDescs[$legref1]['departure']['airport'];


					$ArrivalTime1 = $scheduleDescs[$legref1]['arrival']['time'];
					$departureTime1 = $scheduleDescs[$legref1]['departure']['time'];
					$markettingCarrier1 = $scheduleDescs[$legref1]['carrier']['marketing'];

					$carriersql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
					$carrierrow1 = mysqli_fetch_array($carriersql1,MYSQLI_ASSOC);

					if(!empty($carrierrow1)){
					$markettingCarrierName1 = $carrierrow1['name'];
					
					}

					// Departure Country
					$sql3 = mysqli_query($conn,"$Airportsql code='$DepartureFrom1' ");
					$row3 = mysqli_fetch_array($sql3,MYSQLI_ASSOC);

					if(!empty($row3)){
					$dAirport1 = $row3['name'];
					$dCity1 = $row3['cityName'];
					$dCountry1 = $row3['countryCode'];
					
					}

					// Departure Country
					$sql4 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo1' ");
					$row4 = mysqli_fetch_array($sql4,MYSQLI_ASSOC);

					if(!empty($row4)){
						$aAirport1 = $row4['name'];
						$aCity1 = $row4['cityName'];
						$aCountry1 = $row4['countryCode'];
					
					}


					$markettingFN1 = $scheduleDescs[$legref1]['carrier']['marketingFlightNumber'];
					$operatingCarrier1 = $scheduleDescs[$legref1]['carrier']['operating'];

					if(isset($scheduleDescs[$legref1]['carrier']['operatingFlightNumber'])){
						$operatingFN1 = $scheduleDescs[$legref1]['carrier']['operatingFlightNumber'];
					}else{
						$operatingFN1 = 0;
					}

					$opsql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$operatingCarrier1' ");
					$oprow1 = mysqli_fetch_array($opsql1,MYSQLI_ASSOC);

					if(!empty($oprow)){
						$operatingCarrierName1 = $oprow1['name'];								
					}

					if(isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
						$Seat2 = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
					}

					if(isset($fareComponents[0]['segments'][1]['segment']['bookingCode'])){
							$BookingCode1 = $fareComponents[0]['segments'][1]['segment']['bookingCode'];
					}else if(isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])){
							$BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
					}else{
						$BookingCode1 = $fareComponents[0]['segments'][1]['segment']['bookingCode'];
					}



					//Store Data
					if($dateAdjust2 == 1){
						$dDate2 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));                                                   
					}else{
						$dDate2 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
					}

					$arrivalDate2 = 0;
					if(isset($scheduleDescs[$legref1]['arrival']['dateAdjustment'])){
							$arrivalDate2 += 1;
					}


					if($arrivalDate2 == 1){
							$aDate2 = date('Y-m-d', strtotime("+1 day", strtotime($dDate2)));
							}else{
							$aDate2 = date('Y-m-d', strtotime("+0 day", strtotime($dDate2)));
					}


					$fromTime1 = str_split($departureTime1, 8);
					$dpTime1 = $dDate2."T".$fromTime1[0];

					$toTime1 = str_split($ArrivalTime1, 8);
					$arrTime1 = $aDate2."T".$toTime1[0];

				
				// 3rd Leg

				$lf3 = $legDescs[$id]['schedules'][2]['ref'];
				$legref2 = $lf3- 1;

			
					$dateAdjust3 = 0 ;
					if(isset($legDescs[$id]['schedules'][2]['departureDateAdjustment'])){
						$dateAdjust3 = $legDescs[$id]['schedules'][2]['departureDateAdjustment'];
					}

					
					//Store Data
					if($dateAdjust3 == 1){
						$NewDate3 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
					}else{
						$NewDate3 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
					}

				$ElapsedTime3 = $scheduleDescs[$legref2]['elapsedTime'];
				$TravelTime3 = floor($ElapsedTime3 / 60)."H ".($ElapsedTime3 - ((floor($ElapsedTime3 / 60)) * 60))."Min";
					
				$ArrivalTime3 = $scheduleDescs[$legref2]['arrival']['time'];
				$arrAt3 = substr($ArrivalTime3,0,5);

				$departureTime3 = $scheduleDescs[$legref2]['departure']['time'];
				$depAt3 = substr($departureTime3,0,5);

				$fromTime3 = str_split($departureTime3, 8);
				$depTimedate3 = date("D d M Y", strtotime($NewDate3." ".$fromTime3[0]));

				$toTime3 = str_split($ArrivalTime3, 8);
				$arrTimedate3 = date("D d M Y", strtotime($NewDate3." ".$toTime3[0]));
				
				
				$dateAdjust3 = 0;

					if(isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])){
							$dateAdjust3 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
					}

					$ArrivalTo2 = $scheduleDescs[$legref2]['arrival']['airport'];
					$DepartureFrom2 = $scheduleDescs[$legref2]['departure']['airport'];
					$ArrivalTime2 = $scheduleDescs[$legref2]['arrival']['time'];
					$departureTime2 = $scheduleDescs[$legref2]['departure']['time'];
					$markettingCarrier2 = $scheduleDescs[$legref2]['carrier']['marketing'];

					$carriersql2 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier2' ");
					$carrierrow2 = mysqli_fetch_array($carriersql2,MYSQLI_ASSOC);

					if(!empty($carrierrow2)){
						$markettingCarrierName2 = $carrierrow2['name'];                                               
					}

					// Departure Country
					$dsql3 = mysqli_query($conn,"$Airportsql code='$DepartureFrom2' ");
					$drow3 = mysqli_fetch_array($dsql3,MYSQLI_ASSOC);

					if(!empty($drow3)){
					$dAirport2 = $drow3['name'];
					$dCity2 = $drow3['cityName'];
					$dCountry2 = $drow3['countryCode'];
					
					}

					// Arrival Country
					$asql4 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo2' ");
					$arow4 = mysqli_fetch_array($asql4,MYSQLI_ASSOC);

					if(!empty($arow4)){
						$aAirport2 = $arow4['name'];
						$aCity2 = $arow4['cityName'];
						$aCountry2 = $arow4['countryCode'];
					
					}

					$markettingFN2 = $scheduleDescs[$legref2]['carrier']['marketingFlightNumber'];
					$operatingCarrier2 = $scheduleDescs[$legref2]['carrier']['operating'];

					if(isset($scheduleDescs[$legref2]['carrier']['operatingFlightNumber'])){
						$operatingFN2 = $scheduleDescs[$legref2]['carrier']['operatingFlightNumber'];
					}else{
						$operatingFN2 = $scheduleDescs[$legref2]['carrier']['marketingFlightNumber'];
					}

					$opsql2 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$operatingCarrier2' ");
					$oprow2 = mysqli_fetch_array($opsql2,MYSQLI_ASSOC);

					if(!empty($oprow2)){
						$operatingCarrierName2 = $oprow2['name'];								
					}
					

					if(isset($fareComponents[0]['segments'][2]['segment']['seatsAvailable'])){
						$Seat3 = $fareComponents[0]['segments'][2]['segment']['seatsAvailable'];
					}

					if(isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])){
							$BookingCode2 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
					}else if(isset($fareComponents[1]['segments'][1]['segment']['bookingCode'])){
							$BookingCode2 = $fareComponents[1]['segments'][1]['segment']['bookingCode'];
					}else{
						$BookingCode2 = $fareComponents[0]['segments'][2]['segment']['bookingCode'];
					}

					//Store Data
					if($dateAdjust3 == 1){
						$dDate3 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));                                                   
					}else{
						$dDate3 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
					}

					$arrivalDate3 = 0;
					if(isset($scheduleDescs[$legref2]['arrival']['dateAdjustment'])){
							$arrivalDate3 += 1;
					}


					if($arrivalDate3 == 1){
							$aDate3 = date('Y-m-d', strtotime("+1 day", strtotime($dDate3)));
							}else{
							$aDate3 = date('Y-m-d', strtotime("+0 day", strtotime($dDate3)));
					}


					$fromTime2 = str_split($departureTime2, 8);
					$dpTime2 = $dDate3."T".$fromTime2[0];

					$toTime2 = str_split($ArrivalTime2, 8);
					$arrTime2 = $aDate3."T".$toTime2[0];


								

					$segment = array("0" =>
										array("marketingcareer"=> "$markettingCarrier",
										"marketingcareerName"=> "$markettingCarrierName",
												"marketingflight"=> "$markettingFN",
												"operatingcareer"=> "$operatingCarrier",
												"operatingflight"=> "$operatingFN",
												"operatingCarrierName"=> "$operatingCarrierName",
												"departure"=> "$DepartureFrom",
												"departureAirport"=> "$dAirport ",
												"departureLocation"=> "$dCity , $dCountry",                    
												"departureTime" => "$dpTime",
												"arrival"=> "$ArrivalTo",                   
												"arrivalTime" => "$arrTime",
												"arrivalAirport"=> "$aAirport",
												"arrivalLocation"=> "$aCity , $aCountry",
												"flightduration"=> "$TravelTime1",
												"bookingcode"=> "$BookingCode",
												"seat"=> "$Seat"),
									"1" =>
										array("marketingcareer"=> "$markettingCarrier1",
										"marketingcareerName"=> "$markettingCarrierName1",
												"marketingflight"=> "$markettingFN1",
												"operatingcareer"=> "$operatingCarrier1",
												"operatingflight"=> "$operatingFN1",
												"operatingCarrierName1"=> "$operatingCarrierName1",
												"departure"=> "$DepartureFrom1",
												"departureAirport"=> "$dAirport1",
												"departureLocation"=> "$dCity1 , $dCountry1",                    
												"departureTime" => "$dpTime1",
												"arrival"=> "$ArrivalTo1",                   
												"arrivalTime" => "$arrTime1",
												"arrivalAirport"=> "$aAirport1",
												"arrivalLocation"=> "$aCity1 , $aCountry1",
												"flightduration"=> "$TravelTime2",
												"bookingcode"=> "$BookingCode1",
												"seat"=> "$Seat1"),
									"2" =>
										array("marketingcareer"=> "$markettingCarrier2",
												"marketingcareerName"=> "$markettingCarrierName2",
												"marketingflight"=> "$markettingFN2",
												"operatingcareer"=> "$operatingCarrier2",
												"operatingflight"=> "$operatingFN2",
												"operatingCarrierName"=> "$operatingCarrierName2",
												"departure"=> "$DepartureFrom2",
												"departureAirport"=> "$dAirport2",
												"departureLocation"=> "$dCity2 , $dCountry2",                    
												"departureTime" => "$dpTime2",
												"arrival"=> "$ArrivalTo2",                   
												"arrivalTime" => "$arrTime2",
												"arrivalAirport"=> "$aAirport2",
												"arrivalLocation"=> "$aCity2 , $aCountry2",
												"flightduration"=> "$TravelTime3",
												"bookingcode"=> "$BookingCode2",
												"seat"=> "$Seat2")													

								);
				$TransitTime = round(abs(strtotime($dpTime1) - strtotime($arrTime)) / 60,2);
				$TransitDuration = floor($TransitTime / 60)."H ".($TransitTime - ((floor($TransitTime / 60)) * 60))."Min";

				$TransitTime1 = round(abs(strtotime($dpTime2) - strtotime($arrTime1)) / 60,2);
				$TransitDuration1 = floor($TransitTime1 / 60)."H ".($TransitTime1 - ((floor($TransitTime1 / 60)) * 60))."Min";
				
				$transitDetails = array("transit1"=> $TransitDuration,
										"transit2"=> $TransitDuration1);

				$basic = array("system" =>"Sabre",
									"segment"=> "3",
									"uId"=> $uId,
									"triptype"=>$TripType,
									"career"=> "$vCarCode",
									"careerName" => "$CarrieerName",
									"lastTicketTime"=> "$timelimit",
									"BasePrice" => "$baseFareAmount",
									"Taxes" => "$totalTaxAmount",
									"price" => "$AgentPrice",
									"clientPrice"=> "$totalFare",
									"comission"=> "$Commission",
									"comissiontype"=> $ComissionType,
									"comissionvalue"=> $comissionvalue,
									"farecurrency"=> $FareCurrency,
									"airlinescomref"=> $comRef,
									"pricebreakdown"=> $PriceBreakDown,
									"departure"=> "$From",                   
									"departureTime" => "$depAt1",
									"departureDate" => "$depTimedate1",
									"arrival"=> "$To",                   
									"arrivalTime" => "$arrAt2",
									"arrivalDate" => "$arrTimedate3",
									"flightduration"=> "$JourneyDuration",
									"bags" => "$Bags",
									"seat" => "$Seat",
									"class" => "$CabinClass",
									"refundable"=> "$nonRef",
									"segments" => $segment,												
									"transit" => $transitDetails
									
							);

				}else if($sgCount == 4){
					
				$lf = $legDescs[$id]['schedules'][0]['ref'];
				$legref = $lf- 1;

				$ArrivalTime1 = $scheduleDescs[$legref]['arrival']['time'];
				$arrAt2 = substr($ArrivalTime1,0,5);

				$arrivalDate1 = 0;
				if(isset($scheduleDescs[$legref]['arrival']['dateAdjustment'])){
						$arrivalDate1 += 1;
				}


				if($arrivalDate1 == 1){
						$aDate = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
						}else{
						$aDate = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
					}

					
				$ElapsedTime1 = $scheduleDescs[$legref]['elapsedTime'];
				$TravelTime1 = floor($ElapsedTime1 / 60)."H ".($ElapsedTime1 - ((floor($ElapsedTime1 / 60)) * 60))."Min";

				$ArrivalTime1 = $scheduleDescs[$legref]['arrival']['time'];
				$arrAt1 = substr($ArrivalTime1,0,5);

				$departureTime1 = $scheduleDescs[$legref]['departure']['time'];
				$depAt1 = substr($departureTime1,0,5);

				$fromTime1 = str_split($departureTime1, 8);
				$dpTime1 = date("D d M Y", strtotime($Date." ".$fromTime1[0]));

				$toTime1 = str_split($ArrivalTime1, 8);
				$arrTime1 = date("D d M Y", strtotime($aDate." ".$toTime1[0]));

				$ArrivalTo = $scheduleDescs[$legref]['arrival']['airport'];
                $DepartureFrom = $scheduleDescs[$legref]['departure']['airport'];


                $ArrivalTime = $scheduleDescs[$legref]['arrival']['time'];
                $departureTime = $scheduleDescs[$legref]['departure']['time'];
                $markettingCarrier = $scheduleDescs[$legref]['carrier']['marketing'];

                $carriersql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                $carrierrow = mysqli_fetch_array($carriersql,MYSQLI_ASSOC);

                if(!empty($carrierrow)){
                    $markettingCarrierName = $carrierrow['name'];                                            
                }

                // Departure Country
                $sql1 = mysqli_query($conn,"$Airportsql code='$DepartureFrom' ");
                $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);

                if(!empty($row1)){
                $dAirport = $row1['name'];
                $dCity = $row1['cityName'];
                $dCountry = $row1['countryCode'];
                
                }

                // Departure Country
                $sql2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                $row2 = mysqli_fetch_array($sql2,MYSQLI_ASSOC);

                if(!empty($row2)){
                $aAirport = $row2['name'];
                $aCity = $row2['cityName'];
                $aCountry = $row2['countryCode'];
                
                }


                $markettingFN = $scheduleDescs[$legref]['carrier']['marketingFlightNumber'];
                $operatingCarrier = $scheduleDescs[$legref]['carrier']['operating'];
                if(isset($scheduleDescs[$legref]['carrier']['operatingFlightNumber'])){
                    $operatingFN = $scheduleDescs[$legref]['carrier']['operatingFlightNumber'];
                }else{
                    $operatingFN = $scheduleDescs[$legref]['carrier']['marketingFlightNumber'];
                }

                if(isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
                    $Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
                }
                

                if(isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])){
                        $BookingCode1 = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
                }else{
                    $BookingCode1 = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
                }

                $arrivalDate = 0;
                if(isset($scheduleDescs[$legref]['arrival']['dateAdjustment'])){
                        $arrivalDate += 1;
                }


                if($arrivalDate == 1){
                        $aDate = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                        }else{
                        $aDate = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                }


                $fromTime = str_split($departureTime, 8);
                $dpTime = $Date."T".$fromTime[0];

                $toTime = str_split($ArrivalTime, 8);
                $arrTime = $aDate."T".$toTime[0];



                //2nd Leg

                $lf2 = $legDescs[$id]['schedules'][1]['ref'];
                $legref1 = $lf2- 1;

        
                $dateAdjust2 = 0 ;
                if(isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])){
                    $dateAdjust2 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
                }

                
                //Store Data
                if($dateAdjust2 == 1){
                    $NewDate2 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                }else{
                    $NewDate2 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                }

				$ElapsedTime2 = $scheduleDescs[$legref1]['elapsedTime'];
				$TravelTime2 = floor($ElapsedTime2 / 60)."H ".($ElapsedTime2 - ((floor($ElapsedTime2 / 60)) * 60))."Min";
					
				$ArrivalTime2 = $scheduleDescs[$legref1]['arrival']['time'];
				$arrAt2 = substr($ArrivalTime2,0,5);

				$departureTime2 = $scheduleDescs[$legref1]['departure']['time'];
				$depAt2 = substr($departureTime2,0,5);

				$fromTime2 = str_split($departureTime2, 8);
				$dpTime2 = date("D d M Y", strtotime($NewDate2." ".$fromTime2[0]));

				$toTime2 = str_split($ArrivalTime2, 8);
				$arrTime2 = date("D d M Y", strtotime($NewDate2." ".$toTime2[0]));


				$dateAdjust2 = 0;
                if(isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])){
                        $dateAdjust2 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
                }

                $descid = $scheduleDescs[$legref1]['id'];
                $ArrivalTo1 = $scheduleDescs[$legref1]['arrival']['airport'];
                $DepartureFrom1 = $scheduleDescs[$legref1]['departure']['airport'];


                $ArrivalTime1 = $scheduleDescs[$legref1]['arrival']['time'];
                $departureTime1 = $scheduleDescs[$legref1]['departure']['time'];
                $markettingCarrier1 = $scheduleDescs[$legref1]['carrier']['marketing'];

                $carriersql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
                $carrierrow1 = mysqli_fetch_array($carriersql1,MYSQLI_ASSOC);

                if(!empty($carrierrow1)){
                $markettingCarrierName1 = $carrierrow1['name'];
                
                }

                // Departure Country
                $sql3 = mysqli_query($conn,"$Airportsql code='$DepartureFrom1' ");
                $row3 = mysqli_fetch_array($sql3,MYSQLI_ASSOC);

                if(!empty($row3)){
                $dAirport1 = $row3['name'];
                $dCity1 = $row3['cityName'];
                $dCountry1 = $row3['countryCode'];
                
                }

                // Departure Country
                $sql4 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo1' ");
                $row4 = mysqli_fetch_array($sql4,MYSQLI_ASSOC);

                if(!empty($row4)){
                    $aAirport1 = $row4['name'];
                    $aCity1 = $row4['cityName'];
                    $aCountry1 = $row4['countryCode'];
                
                }


                $markettingFN1 = $scheduleDescs[$legref1]['carrier']['marketingFlightNumber'];
                $operatingCarrier1 = $scheduleDescs[$legref1]['carrier']['operating'];

                if(isset($scheduleDescs[$legref1]['carrier']['operatingFlightNumber'])){
                    $operatingFN1 = $scheduleDescs[$legref1]['carrier']['operatingFlightNumber'];
                }else{
                    $operatingFN1 = 0;
                }

                $opsql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$operatingCarrier1' ");
                $oprow = mysqli_fetch_array($opsql,MYSQLI_ASSOC);

                if(!empty($oprow)){
                    $operatingCarrierName1 = $oprow['name'];								
                }

                if(isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])){
                    $Seat1 = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
                }

                if(isset($fareComponents[0]['segments'][1]['segment']['bookingCode'])){
                        $BookingCode1 = $fareComponents[0]['segments'][1]['segment']['bookingCode'];
                }else if(isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])){
                        $BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
                }else{
                    $BookingCode1 = $fareComponents[0]['segments'][1]['segment']['bookingCode'];
                }



                //Store Data
                if($dateAdjust2 == 1){
                    $dDate2 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));                                                   
                }else{
                    $dDate2 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                }

                $arrivalDate2 = 0;
                if(isset($scheduleDescs[$legref1]['arrival']['dateAdjustment'])){
                        $arrivalDate2 += 1;
                }


                if($arrivalDate2 == 1){
                        $aDate2 = date('Y-m-d', strtotime("+1 day", strtotime($dDate2)));
                        }else{
                        $aDate2 = date('Y-m-d', strtotime("+0 day", strtotime($dDate2)));
                }


                $fromTime1 = str_split($departureTime1, 8);
                $dpTime1 = $dDate2."T".$fromTime1[0];

                $toTime1 = str_split($ArrivalTime1, 8);
                $arrTime1 = $aDate2."T".$toTime1[0];

				
				// 4rth Leg

				$lf3 = $legDescs[$id]['schedules'][2]['ref'];
				$legref2 = $lf3- 1;

			
                $dateAdjust3 = 0 ;
                if(isset($legDescs[$id]['schedules'][2]['departureDateAdjustment'])){
                    $dateAdjust3 = $legDescs[$id]['schedules'][2]['departureDateAdjustment'];
                }

                
                //Store Data
                if($dateAdjust3 == 1){
                    $NewDate3 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                }else{
                    $NewDate3 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                }

				$ElapsedTime3 = $scheduleDescs[$legref2]['elapsedTime'];
				$TravelTime3 = floor($ElapsedTime3 / 60)."H ".($ElapsedTime3 - ((floor($ElapsedTime3 / 60)) * 60))."Min";
					
				$ArrivalTime3 = $scheduleDescs[$legref2]['arrival']['time'];
				$arrAt3 = substr($ArrivalTime3,0,5);

				$departureTime3 = $scheduleDescs[$legref2]['departure']['time'];
				$depAt3 = substr($departureTime3,0,5);

				$fromTime3 = str_split($departureTime3, 8);
				$depTime3 = date("D d M Y", strtotime($NewDate3." ".$fromTime3[0]));

				$toTime3 = str_split($ArrivalTime3, 8);
				$arrTime3 = date("D d M Y", strtotime($NewDate3." ".$toTime3[0]));
				
				
				$dateAdjust3 = 0;

                if(isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])){
                        $dateAdjust3 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
                }

                $ArrivalTo2 = $scheduleDescs[$legref2]['arrival']['airport'];
                $DepartureFrom2 = $scheduleDescs[$legref2]['departure']['airport'];
                $ArrivalTime2 = $scheduleDescs[$legref2]['arrival']['time'];
                $departureTime2 = $scheduleDescs[$legref2]['departure']['time'];
                $markettingCarrier2 = $scheduleDescs[$legref2]['carrier']['marketing'];

                $carriersql2 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier2' ");
                $carrierrow2 = mysqli_fetch_array($carriersql2,MYSQLI_ASSOC);

                if(!empty($carrierrow2)){
                    $markettingCarrierName2 = $carrierrow2['name'];                                               
                }

                // Departure Country
                $dsql3 = mysqli_query($conn,"$Airportsql code='$DepartureFrom2' ");
                $drow3 = mysqli_fetch_array($dsql3,MYSQLI_ASSOC);

                if(!empty($drow3)){
                $dAirport2 = $drow3['name'];
                $dCity2 = $drow3['cityName'];
                $dCountry2 = $drow3['countryCode'];
                
                }

                // Arrival Country
                $asql4 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo2' ");
                $arow4 = mysqli_fetch_array($asql4,MYSQLI_ASSOC);

                if(!empty($arow4)){
                    $aAirport2 = $arow4['name'];
                    $aCity2 = $arow4['cityName'];
                    $aCountry2 = $arow4['countryCode'];
                
                }

                $markettingFN2 = $scheduleDescs[$legref2]['carrier']['marketingFlightNumber'];
                $operatingCarrier2 = $scheduleDescs[$legref2]['carrier']['operating'];

                if(isset($scheduleDescs[$legref2]['carrier']['operatingFlightNumber'])){
                    $operatingFN2 = $scheduleDescs[$legref2]['carrier']['operatingFlightNumber'];
                }else{
                    $operatingFN2 = $scheduleDescs[$legref2]['carrier']['marketingFlightNumber'];
                }
                

                if(isset($fareComponents[0]['segments'][2]['segment']['seatsAvailable'])){
                    $Seat2 = $fareComponents[0]['segments'][2]['segment']['seatsAvailable'];
                }else{
                    $Seat2 = $Seat1;
                }

                if(isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])){
                        $BookingCode2 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
                }else if(isset($fareComponents[1]['segments'][1]['segment']['bookingCode'])){
                        $BookingCode2 = $fareComponents[1]['segments'][1]['segment']['bookingCode'];
                }else{
                    $BookingCode2 = $fareComponents[0]['segments'][2]['segment']['bookingCode'];
                }

                //Store Data
                if($dateAdjust3 == 1){
                    $dDate3 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));                                                   
                }else{
                    $dDate3 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                }

                $arrivalDate3 = 0;
                if(isset($scheduleDescs[$legref2]['arrival']['dateAdjustment'])){
                        $arrivalDate3 += 1;
                }


                if($arrivalDate3 == 1){
                        $aDate3 = date('Y-m-d', strtotime("+1 day", strtotime($dDate3)));
                        }else{
                        $aDate3 = date('Y-m-d', strtotime("+0 day", strtotime($dDate3)));
                }


                $fromTime2 = str_split($departureTime2, 8);
                $dpTime2 = $dDate3."T".$fromTime2[0];

                $toTime2 = str_split($ArrivalTime2, 8);
                $arrTime2 = $aDate3."T".$toTime2[0];


                // 4rth Leg

                $lf4 = $legDescs[$id]['schedules'][3]['ref'];
                $legref3 = $lf4- 1;

        
                $dateAdjust4 = 0 ;
                if(isset($legDescs[$id]['schedules'][3]['departureDateAdjustment'])){
                    $dateAdjust3 = $legDescs[$id]['schedules'][3]['departureDateAdjustment'];
                }

                
                //Store Data
                if($dateAdjust4 == 1){
                    $NewDate4 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                }else{
                    $NewDate4 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                }

                $ElapsedTime4 = $scheduleDescs[$legref3]['elapsedTime'];
                $TravelTime4 = floor($ElapsedTime4 / 60)."H ".($ElapsedTime4 - ((floor($ElapsedTime4 / 60)) * 60))."Min";
                    
                $ArrivalTime4 = $scheduleDescs[$legref3]['arrival']['time'];
                $arrAt4 = substr($ArrivalTime4,0,5);

                $departureTime4 = $scheduleDescs[$legref3]['departure']['time'];
                $depAt4 = substr($departureTime4,0,5);

                $fromTime4 = str_split($departureTime4, 8);
                $dpTime4 = date("D d M Y", strtotime($NewDate4." ".$fromTime4[0]));

                $toTime4 = str_split($ArrivalTime4, 8);
                $arrTime4 = date("D d M Y", strtotime($NewDate4." ".$toTime4[0]));
                
                
                $dateAdjust4 = 0;

                if(isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])){
                        $dateAdjust4 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
                }

                $ArrivalTo3 = $scheduleDescs[$legref3]['arrival']['airport'];
                $DepartureFrom3 = $scheduleDescs[$legref3]['departure']['airport'];
                $ArrivalTime3 = $scheduleDescs[$legref3]['arrival']['time'];
                $departureTime3 = $scheduleDescs[$legref3]['departure']['time'];
                $markettingCarrier3 = $scheduleDescs[$legref3]['carrier']['marketing'];

                $carriersql3 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier3' ");
                $carrierrow3 = mysqli_fetch_array($carriersql3,MYSQLI_ASSOC);

                if(!empty($carrierrow3)){
                    $markettingCarrierName3 = $carrierrow3['name'];                                               
                }

                // Departure Country
                $dsql4 = mysqli_query($conn,"$Airportsql code='$DepartureFrom3' ");
                $drow4 = mysqli_fetch_array($dsql4,MYSQLI_ASSOC);

                if(!empty($drow4)){
                $dAirport3 = $drow4['name'];
                $dCity3 = $drow4['cityName'];
                $dCountry3 = $drow4['countryCode'];
                
                }

                // Arrival Country
                $asql4 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo3' ");
                $arow4 = mysqli_fetch_array($asql4,MYSQLI_ASSOC);

                if(!empty($arow4)){
                    $aAirport3 = $arow4['name'];
                    $aCity3 = $arow4['cityName'];
                    $aCountry3 = $arow4['countryCode'];
                
                }

                $markettingFN3 = $scheduleDescs[$legref3]['carrier']['marketingFlightNumber'];
                $operatingCarrier3 = $scheduleDescs[$legref3]['carrier']['operating'];

                if(isset($scheduleDescs[$legref3]['carrier']['operatingFlightNumber'])){
                    $operatingFN3 = $scheduleDescs[$legref3]['carrier']['operatingFlightNumber'];
                }else{
                    $operatingFN3 = $scheduleDescs[$legref3]['carrier']['marketingFlightNumber'];
                }

                $opsql3 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$operatingCarrier2' ");
                $oprow3 = mysqli_fetch_array($opsql2,MYSQLI_ASSOC);

                if(!empty($oprow2)){
                    $operatingCarrierName3 = $oprow3['name'];								
                }
                

                if(isset($fareComponents[0]['segments'][3]['segment']['seatsAvailable'])){
                    $Seat3 = $fareComponents[0]['segments'][3]['segment']['seatsAvailable'];
                }else{
                    $Seat3 = $Seat1;
                }

                if(isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])){
                        $BookingCode3 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
                }else if(isset($fareComponents[1]['segments'][1]['segment']['bookingCode'])){
                        $BookingCode3 = $fareComponents[1]['segments'][1]['segment']['bookingCode'];
                }else{
                    $BookingCode3 = $fareComponents[0]['segments'][3]['segment']['bookingCode'];
                }

                //Store Data
                if($dateAdjust3 == 1){
                    $dDate4 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));                                                   
                }else{
                    $dDate4 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                }

                $arrivalDate4 = 0;
                if(isset($scheduleDescs[$legref3]['arrival']['dateAdjustment'])){
                        $arrivalDate4 += 1;
                }


                if($arrivalDate4 == 1){
                        $aDate4 = date('Y-m-d', strtotime("+1 day", strtotime($dDate4)));
                        }else{
                        $aDate4 = date('Y-m-d', strtotime("+0 day", strtotime($dDate4)));
                }


                $fromTime3 = str_split($departureTime3, 8);
                $dpTime3 = $dDate4."T".$fromTime3[0];

                $toTime3 = str_split($ArrivalTime3, 8);
                $arrTime3 = $aDate4."T".$toTime3[0];

					

                $segment = array("0" =>
                                    array("marketingcareer"=> "$markettingCarrier",
                                    "marketingcareerName"=> "$markettingCarrierName",
                                            "marketingflight"=> "$markettingFN",
                                            "operatingcareer"=> "$operatingCarrier",
                                            "operatingflight"=> "$operatingFN",
                                            "operatingCarrierName"=> "$operatingCarrierName",
                                            "departure"=> "$DepartureFrom",
                                            "departureAirport"=> "$dAirport ",
                                            "departureLocation"=> "$dCity , $dCountry",                    
                                            "departureTime" => "$dpTime",
                                            "arrival"=> "$ArrivalTo",                   
                                            "arrivalTime" => "$arrTime",
                                            "arrivalAirport"=> "$aAirport",
                                            "arrivalLocation"=> "$aCity , $aCountry",
                                            "flightduration"=> "$TravelTime1",
                                            "bookingcode"=> "$BookingCode",
                                            "seat"=> "$Seat"),
                                "1" =>
                                    array("marketingcareer"=> "$markettingCarrier1",
                                    "marketingcareerName"=> "$markettingCarrierName1",
                                            "marketingflight"=> "$markettingFN1",
                                            "operatingcareer"=> "$operatingCarrier1",
                                            "operatingflight"=> "$operatingFN1",
                                            "operatingCarrierName"=> "$operatingCarrierName1",
                                            "departure"=> "$DepartureFrom1",
                                            "departureAirport"=> "$dAirport1",
                                            "departureLocation"=> "$dCity1 , $dCountry1",                    
                                            "departureTime" => "$dpTime1",
                                            "arrival"=> "$ArrivalTo1",                   
                                            "arrivalTime" => "$arrTime1",
                                            "arrivalAirport"=> "$aAirport1",
                                            "arrivalLocation"=> "$aCity1 , $aCountry1",
                                            "flightduration"=> "$TravelTime2",
                                            "bookingcode"=> "$BookingCode1",
                                            "seat"=> "$Seat1"),
                                "2" =>
                                    array("marketingcareer"=> "$markettingCarrier2",
                                    "marketingcareerName"=> "$markettingCarrierName2",
                                            "marketingflight"=> "$markettingFN2",
                                            "operatingcareer"=> "$operatingCarrier2",
                                            "operatingflight"=> "$operatingFN2",
                                            "operatingCarrierName"=> "$operatingCarrierName2",
                                            "departure"=> "$DepartureFrom2",
                                            "departureAirport"=> "$dAirport2",
                                            "departureLocation"=> "$dCity2 , $dCountry2",                    
                                            "departureTime" => "$dpTime2",
                                            "arrival"=> "$ArrivalTo2",                   
                                            "arrivalTime" => "$arrTime2",
                                            "arrivalAirport"=> "$aAirport2",
                                            "arrivalLocation"=> "$aCity1 , $aCountry2",
                                            "flightduration"=> "$TravelTime3",
                                            "bookingcode"=> "$BookingCode2",
                                            "seat"=> "$Seat2"),
                                "3" =>
                                    array("marketingcareer"=> "$markettingCarrier3",
                                    "marketingcareerName"=> "$markettingCarrierName3",
                                            "marketingflight"=> "$markettingFN3",
                                            "operatingcareer"=> "$operatingCarrier3",
                                            "operatingflight"=> "$operatingFN3",
                                            "operatingCarrierName"=> "$operatingCarrierName3",
                                            "departure"=> "$DepartureFrom3",
                                            "departureAirport"=> "$dAirport3",
                                            "departureLocation"=> "$dCity3 , $dCountry3",                    
                                            "departureTime" => "$dpTime3",
                                            "arrival"=> "$ArrivalTo3",                   
                                            "arrivalTime" => "$arrTime3",
                                            "arrivalAirport"=> "$aAirport3",
                                            "arrivalLocation"=> "$aCity3 , $aCountry3",
                                            "flightduration"=> "$TravelTime4",
                                            "bookingcode"=> "$BookingCode3",
                                            "seat"=> "$Seat3")												

                            );
				$TransitTime = round(abs(strtotime($dpTime1) - strtotime($arrTime)) / 60,2);
				$TransitDuration = floor($TransitTime / 60)."H ".($TransitTime - ((floor($TransitTime / 60)) * 60))."Min";

				$TransitTime1 = round(abs(strtotime($dpTime2) - strtotime($arrTime1)) / 60,2);
				$TransitDuration1 = floor($TransitTime1 / 60)."H ".($TransitTime1 - ((floor($TransitTime1 / 60)) * 60))."Min";

				$TransitTime2 = round(abs(strtotime($dpTime3) - strtotime($arrTime2)) / 60,2);
				$TransitDuration2 = floor($TransitTime2 / 60)."H ".($TransitTime2 - ((floor($TransitTime2 / 60)) * 60))."Min";
				
				$transitDetails = array("transit1"=> $TransitDuration,
										"transit2"=> $TransitDuration1,
										"transit3"=> $TransitDuration2);

				$basic = array("system" =>"Sabre",
                                "segment"=> "4",
                                "uId"=> $uId,
                                "career"=> "$vCarCode",
                                "careerName" => "$CarrieerName",									
                                "BasePrice" => "$baseFareAmount",
                                "Taxes" => "$totalTaxAmount",
                                "price" => "$AgentPrice",
                                "clientPrice"=> "$totalFare",
                                "comission"=> "$Commission",
                                "comissiontype"=> $ComissionType,
                                "comissionvalue"=> $comissionvalue,
                                "farecurrency"=> $FareCurrency,
                                "airlinescomref"=> $comRef,
                                "pricebreakdown"=> $PriceBreakDown,
                                "departure"=> "$From",                   
                                "departureTime" => "$depAt1",
                                "departureDate" => "$dpTime1",
                                "arrival"=> "$To",                   
                                "arrivalTime" => "$arrAt3",
                                "arrivalDate" => "$arrTime3",
                                "flightduration"=> "$JourneyDuration",
                                "bags" => "$Bags",
                                "seat" => "$Seat",
                                "class" => "$CabinClass",
                                "refundable"=> "$nonRef",
                                "segments" => $segment,												
                                "transit" => $transitDetails                         
							);
					
				}

                array_push($All,$basic);
						
			}

            echo json_encode($All);

		}else{
			$json_string = json_encode($All, JSON_PRETTY_PRINT);
			print_r($json_string);
		}
	}
			
}else if(array_key_exists("return",$_GET) && array_key_exists("journeyfrom",$_GET) && array_key_exists("journeyto",$_GET) && array_key_exists("departuredate",$_GET) && array_key_exists("returndate",$_GET) && array_key_exists("adult",$_GET) && (array_key_exists("child",$_GET) && array_key_exists("infant",$_GET))){
				
	$From = $_GET['journeyfrom'];
	$To = $_GET['journeyto'];
	$dDate = $_GET['departuredate'];
	$rDate = $_GET['returndate'];
	$DepartureDate = $dDate."T00:00:00";
	$ReturnDate = $rDate."T00:00:00";

	$adult = $_GET['adult'];
	$child = $_GET['child'];
	$infants = $_GET['infant'];

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

	$ComissionType = '';
	if($fromCountry == "BD" && $toCountry =="BD"){
		$ComissionType = "domestic";
	}else if($fromCountry != 'BD' && $toCountry != 'BD' ){
		$ComissionType = "sotto";
	}else if($fromCountry != 'BD' && $toCountry == 'BD'){
		$ComissionType = "sotti";
	}else if($fromCountry == 'BD' && $toCountry != 'BD'){
		$ComissionType = "sitti";
	}	


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
		
	}else if($adult > 0 && $child > 0){

	$SabreRequest = '{
					"Code": "ADT",
					"Quantity": '.$adult.'
				},
				{
					"Code": "C09",
					"Quantity": '.$child.'
				}';

	
	}else if($adult > 0 && $infants > 0){
	$SabreRequest = '{
				"Code": "ADT",
				"Quantity": '.$adult.'
				},
				{
					"Code": "INF",
					"Quantity": '.$infants.'
				}';

	}else{
		$SabreRequest = '{
					"Code": "ADT",
					"Quantity": '.$adult.'
				}';
	}


	
	if($Sabre == 1){ // Sabre Start

		$client_id= base64_encode("V1:593072:14KK:AA");
		//$client_secret = base64_encode("280ff537"); //cert
		$client_secret = base64_encode("f270395");

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
							"PseudoCityCode": "14KK",
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
							"Name": "50ITINS"
						}
					}
				}
			}
		}',
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Conversation-ID: 2021.01.DevStudio',
			'Authorization: Bearer '.$access_token,
		),
		));

		$response = curl_exec($curl);
		//echo $response;

		curl_close($curl);
		$result = json_decode($response, true);

		if(isset($result['groupedItineraryResponse']['statistics']['itineraryCount']) && $result['groupedItineraryResponse']['statistics']['itineraryCount'] > 0){
			$SabreItenary = $result['groupedItineraryResponse']['itineraryGroups'];
			$flightListSabre = $SabreItenary[0]['itineraries'];
			$scheduleDescs = $result['groupedItineraryResponse']['scheduleDescs'];
			$legDescs = $result['groupedItineraryResponse']['legDescs'];

			$Bag = $result['groupedItineraryResponse']['baggageAllowanceDescs'];
			
			foreach($flightListSabre as $var){
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

				$baseFareAmount =  $var['pricingInformation'][0]['fare']['totalFare']['equivalentAmount'];
				$totalTaxAmount = $var['pricingInformation'][0]['fare']['totalFare']['totalTaxAmount'];							
				$totalFare = $var['pricingInformation'][0]['fare']['totalFare']['totalPrice'];
				

				$Commisionrow = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM airlines WHERE code='$vCarCode' "),MYSQLI_ASSOC);

				$comissionvalue;
				$FareCurrency;
				$comRef;
				if(!empty($Commisionrow)){
					$CarrieerName = $Commisionrow['name'];
					$fareRate= $Commisionrow['commission'];
					$FareCurrency =	$Commisionrow[$ComissionType.'currency'] != ''? $Commisionrow[$ComissionType.'currency'] : 'BDT';
					$comissionvalue = $Commisionrow["sabre".$ComissionType];
					$additional = $Commisionrow["sabreaddamount"];
					$comRef = $Commisionrow["ref_id"];						
				}else{
					$fareRate= 7;
					$FareCurrency = 'BDT';
					$comissionvalue = 0;
					$additional = 0;
					$comRef = 'NA';			
				}

				if($comissionvalue > 0){
					$Ait = 0.003;
				}else{
					$Ait = 0;
				}

				$AgentPrice = FareRulesPolicy($comissionvalue, $FareCurrency, $Ait, $baseFareAmount, $totalTaxAmount) + $additional;
				$Commission = $totalFare - $AgentPrice;

				$diff = 0;
				$OtherCharges = 0;
				if($AgentPrice > $totalFare){
					$diff = $AgentPrice - $totalFare;
					$Pax = $adult + $child +  $infants;
					$OtherCharges = $diff / $Pax;
					$totalFare  = $AgentPrice;
				}


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
										"OtherCharges"=> "$OtherCharges",
										"ServiceFee"=> "0")
										,
										"1" =>
										array("BaseFare"=> "$childBasePrice",
										"Tax"=> "$childTaxAmount",
										"PaxCount"=> $child,
										"PaxType"=> "CNN",
										"Discount"=> "0",
										"OtherCharges"=> "$OtherCharges",
										"ServiceFee"=> "0"),
										"2" =>
										array("BaseFare"=> "$infantBasePrice",
										"Tax"=> "$infantTaxAmount",
										"PaxCount"=> $infants,
										"PaxType"=> "INF",
										"Discount"=> "0",
										"OtherCharges"=> "$OtherCharges",
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
										"OtherCharges"=> "$OtherCharges",
										"ServiceFee"=> "0")
										,
										"1" =>
										array("BaseFare"=> "$childBasePrice",
										"Tax"=> "$childTaxAmount",
										"PaxCount"=> $child,
										"PaxType"=> "CNN",
										"Discount"=> "0",
										"OtherCharges"=> "$OtherCharges",
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
										"OtherCharges"=> "$OtherCharges",
										"ServiceFee"=> "0")
										,
										"1" =>
										
										array("BaseFare"=> "$infantBasePrice",
										"Tax"=> "$infantTaxAmount",
										"PaxCount"=> $infants,
										"PaxType"=> "INF",
										"Discount"=> "0",
										"OtherCharges"=> "$OtherCharges",
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
										"OtherCharges"=> "$OtherCharges",
										"ServiceFee"=> "0")												                                     

								);
				}

				
				$BegRef= $passengerInfo['baggageInformation'][0]['allowance']['ref'];           
				$BegId = $BegRef - 1;

				if($Class == 'Y'){
					$CabinClass = "Economy";
				}

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

				$uId = sha1(md5(time()).''.rand());
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
									"uId"=> $uId,
									"triptype"=>$TripType,
									"career"=> "$vCarCode",
									"careerName" => "$CarrieerName",
									"lastTicketTime"=> "$timelimit",
									"BasePrice" => $baseFareAmount ,
									"Taxes" => $totalTaxAmount,
									"price" => "$AgentPrice",
									"clientPrice"=> "$totalFare",
									"comission"=> "$Commission",
									"comissiontype"=> $ComissionType,
									"comissionvalue"=> $comissionvalue,
									"farecurrency"=> $FareCurrency,
									"airlinescomref"=> $comRef,
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
						$backBookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
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
									"uId"=> $uId,
									"triptype"=>$TripType,
									"career"=> "$vCarCode",
									"careerName" => "$CarrieerName",
									"lastTicketTime"=> "$timelimit",
									"BasePrice" => $baseFareAmount ,
									"Taxes" => $totalTaxAmount,
									"price" => "$AgentPrice",
									"clientPrice"=> "$totalFare",
									"comission"=> "$Commission",
									"comissiontype"=> $ComissionType,
									"comissionvalue"=> $comissionvalue,
									"farecurrency"=> $FareCurrency,
									"airlinescomref"=> $comRef,
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
						$goBookingCode2 = $fareComponents[0]['segments'][1]['segment']['bookingCode'];
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
						$backBookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
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
									"uId"=> $uId,
									"triptype"=>$TripType,
									"career"=> "$vCarCode",
									"careerName" => "$CarrieerName",
									"lastTicketTime"=> "$timelimit",
									"BasePrice" => $baseFareAmount ,
									"Taxes" => $totalTaxAmount,
									"price" => "$AgentPrice",
									"clientPrice"=> "$totalFare",
									"comission"=> "$Commission",
									"comissiontype"=> $ComissionType,
									"comissionvalue"=> $comissionvalue,
									"farecurrency"=> $FareCurrency,
									"airlinescomref"=> $comRef,
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
									"uId"=> $uId,
									"triptype"=>$TripType,
									"career"=> "$vCarCode",
									"careerName" => "$CarrieerName",
									"lastTicketTime"=> "$timelimit",
									"BasePrice" => $baseFareAmount ,
									"Taxes" => $totalTaxAmount,
									"price" => "$AgentPrice",
									"clientPrice"=> "$totalFare",
									"comission"=> "$Commission",
									"comissiontype"=> $ComissionType,
									"comissionvalue"=> $comissionvalue,
									"farecurrency"=> $FareCurrency,
									"airlinescomref"=> $comRef,
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
							"back" =>
							 array("0" =>
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
									"uId"=> $uId,
									"triptype"=>$TripType,
									"career"=> "$vCarCode",
									"careerName" => "$CarrieerName",
									"lastTicketTime"=> "$timelimit",
									"BasePrice" => $baseFareAmount ,
									"Taxes" => $totalTaxAmount,
									"price" => "$AgentPrice",
									"clientPrice"=> "$totalFare",
									"comission"=> "$Commission",
									"comissiontype"=> $ComissionType,
									"comissionvalue"=> $comissionvalue,
									"farecurrency"=> $FareCurrency,
									"airlinescomref"=> $comRef,
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
					array_push($All, $basic);
				}
				
			}
			$json_string = json_encode($All, JSON_PRETTY_PRINT);
			print_r($json_string);
		}else{
			$json_string = json_encode($All, JSON_PRETTY_PRINT);
			print_r($json_string);
		}
	}
	}
}

function FareRulesPolicy($comissionvalue, $FareCurrency, $Ait, $BaseFare, $Taxes){

	
	$TotalPrice  = ($BaseFare * (1-((int)$comissionvalue / 100)) + $Taxes) + (($BaseFare +  $Taxes) * $Ait);

	$AgentPrice = CurrencyConversation($TotalPrice, $FareCurrency);

	return $AgentPrice;

}

function CurrencyConversation($TotalPrice, $FareCurrency){
	include "../../config.php";
		
	$data = $conn->query("SELECT * FROM `fxconversion_rate` where currencyname='$FareCurrency' ");
	$PaymentRate = 0;
	$Sellingrate = 0;
	if ($data->num_rows > 0) {
		while ($row = $data->fetch_assoc()) {
			$PaymentRate = $row['paymentrate'];
			$Sellingrate = $row['sellingrate'];
			
			return floor(($TotalPrice / $PaymentRate) * $Sellingrate);

		}
	}else{
		return $TotalPrice;
	}

}

$conn->close();
?>