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
	$Galileo =  $controlrow['galileo'];
	$FlyHub = $controlrow['flyhub'];							
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
			


		$FlyHubRequest ='{
		"AdultQuantity": "'.$adult.'",
		"ChildQuantity": "'.$child.'",
		"InfantQuantity": "'.$infants.'",
		"EndUserIp": "85.187.128.34",
		"JourneyType": "1",
		"Segments": [
			{
			"Origin": "'.$From.'",
			"Destination": "'.$To.'",
			"CabinClass": "Economy",
			"DepartureDateTime": "'.$Date.'"
			}
		],
		"PreferredAirlines": [
			""
		]
		}';


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
		
		// Decode the JSON file
		$Result = json_decode($flyhubresponse,true);
			
		$FlightListFlyHub = $Result['Results'];
		$SearchID = $Result['SearchId'];
		$FlyHubResponse = array();

		print_r($FlightListFlyHub);
		$f=0;
		foreach($FlightListFlyHub as $flight){
			$vCarCode = $flight['Validatingcarrier'];
			$segments = count($flight['segments']);
			$Refundable = $flight['IsRefundable'];
			$Hold = $flight['HoldAllowed'];

			if($adult> 0 && $child>0 && $infants >0 ){
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

			$markup = 0;
			if($vCarCode == '6E'){
				$markup = 500;
			}else{
			    $markup = 0;
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

			$TotalFare = (int)$flight['TotalFare'] + $markup;

			$ClientFare = $BasePrice + $Taxes + $markup;
			$Commission = $ClientFare - $TotalFare;

			

			if($flight['IsRefundable'] == 1){
				$Refundable = "Refundable";
			}else{
				$Refundable = "Nonrefundable";
			}
			$Availabilty = $flight['Availabilty'];		
			$ResultID = $flight['ResultID'];
 
		
			$uId = sha1(md5(time()).''.rand());
			
			if($segments == 1){
				$dAirportCode = $flight['segments'][0]['Origin']['Airport']['AirportCode'];
				$dAirportName = $flight['segments'][0]['Origin']['Airport']['AirportName'];
				$dCityName = $flight['segments'][0]['Origin']['Airport']['CityName'];
				$dCountryCode = $flight['segments'][0]['Origin']['Airport']['CountryCode'];

				$aAirportCode = $flight['segments'][0]['Destination']['Airport']['AirportCode'];
				$aAirportName = $flight['segments'][0]['Destination']['Airport']['AirportName'];
				$aCityName = $flight['segments'][0]['Destination']['Airport']['CityName'];
				$aCountryCode = $flight['segments'][0]['Destination']['Airport']['CountryCode'];


				$DepTime = $flight['segments'][0]['Origin']['DepTime'];
				$ArrTime = $flight['segments'][0]['Destination']['ArrTime'];

				$AirlineCode = $flight['segments'][0]['Airline']['AirlineCode'];
				$AirlineName = $flight['segments'][0]['Airline']['AirlineName'];
				$FlightNumber = $flight['segments'][0]['Airline']['FlightNumber'];
				$BookingClass = $flight['segments'][0]['Airline']['BookingClass'];
				$CabinClass= $flight['segments'][0]['Airline']['CabinClass'];
				$OperatingCarrier = $flight['segments'][0]['Airline']['OperatingCarrier'];

				if(isset($flight['segments'][0]['baggageDetails'][0]['Checkin'])){				
					$Baggage = $flight['segments'][0]['baggageDetails'][0]['Checkin'];
				} else {
					$Baggage = 0;
				}

				//$Baggage = $flight['segments'][0]['baggageDetails'][0]['Checkin'];
				
				$JourneyDuration = $flight['segments'][0]['JourneyDuration'];
				$Duration = floor($JourneyDuration / 60)."H ".($JourneyDuration - ((floor($JourneyDuration / 60)) * 60))."Min";

				$transitDetails = array("transit1" => "0");

				$segment = array("0" =>
									array("marketingcareer"=> "$AirlineCode",
									"marketingcareerName"=> "$AirlineName",
										"marketingflight"=> "$FlightNumber",
										"operatingcareer"=> "$OperatingCarrier",
										"operatingflight"=> "$FlightNumber",
										"departure"=> "$dAirportCode",
										"departureAirport"=> "$dAirportName ",
										"departureLocation"=> "$dCityName , $dCountryCode",                    
										"departureTime" => "$DepTime",
										"arrival"=> "$aAirportCode",                   
										"arrivalTime" => "$ArrTime",
										"arrivalAirport"=> "$aAirportName",
										"arrivalLocation"=> "$aCityName , $aCountryCode",
										"flightduration"=> "$Duration",
										"bookingcode"=> "$BookingClass",
										"seat"=> "$Availabilty")                                           
									);

								$basic = array("system"=>"FlyHub",
													"segment"=> "1",
													"uId"=> $uId,
													"triptype"=>$TripType,
													"career"=> "$vCarCode",
													"careerName" => "$CarrieerName",
													"BasePrice" => "$BasePrice",
													"Taxes" => "$Taxes",
													"price" => "$TotalFare",
													"clientPrice"=> "$ClientFare",
													"comission"=> "$Commission",
													"comissiontype"=> $ComissionType,
													"comissionvalue"=> $comissionvalue,
													"farecurrency"=> $FareCurrency,
													"airlinescomref"=> $comRef,
													"pricebreakdown"=> $PriceBreakDown,
													"departure"=> $dAirportCode,                   
													"departureTime" => substr($DepTime,11,5),
													"departureDate" => date("D d M Y", strtotime($DepTime)),
													"arrival"=> "$aAirportCode",                   
													"arrivalTime" => substr($ArrTime,11,5),
													"arrivalDate" => date("D d M Y", strtotime($ArrTime)),
													"flightduration"=> "$Duration",
													"transit" => $transitDetails,
													"bags" => "$Baggage",
													"seat" => "$Availabilty",
													"class" => "$CabinClass",
													"refundable"=> "$Refundable",
													"segments" => $segment,
													"hold"=> "$Hold",												
													"SearchID"=> $SearchID,
													"ResultID"=> $ResultID
													
											);
					array_push($FlyHubResponse, $basic);
				
			}else if($segments == 2){

				///Leg 1
				$dAirportCode = $flight['segments'][0]['Origin']['Airport']['AirportCode'];
				$dAirportName = $flight['segments'][0]['Origin']['Airport']['AirportName'];
				$dCityName = $flight['segments'][0]['Origin']['Airport']['CityName'];
				$dCountryCode = $flight['segments'][0]['Origin']['Airport']['CountryCode'];

				$aAirportCode = $flight['segments'][0]['Destination']['Airport']['AirportCode'];
				$aAirportName = $flight['segments'][0]['Destination']['Airport']['AirportName'];
				$aCityName = $flight['segments'][0]['Destination']['Airport']['CityName'];
				$aCountryCode = $flight['segments'][0]['Destination']['Airport']['CountryCode'];


				$DepTime = $flight['segments'][0]['Origin']['DepTime'];
				$ArrTime = $flight['segments'][0]['Destination']['ArrTime'];

				$AirlineCode = $flight['segments'][0]['Airline']['AirlineCode'];
				$AirlineName = $flight['segments'][0]['Airline']['AirlineName'];
				$FlightNumber = $flight['segments'][0]['Airline']['FlightNumber'];
				$BookingClass = $flight['segments'][0]['Airline']['BookingClass'];
				$CabinClass= $flight['segments'][0]['Airline']['CabinClass'];
				$OperatingCarrier = $flight['segments'][0]['Airline']['OperatingCarrier'];

				if(isset($flight['segments'][0]['baggageDetails'][0]['Checkin'])){				
					$Baggage = $flight['segments'][0]['baggageDetails'][0]['Checkin'];
				} else {
					$Baggage = 0;
				}
				$JourneyDuration = $flight['segments'][0]['JourneyDuration'];
				$Duration = floor($JourneyDuration / 60)."H ".($JourneyDuration - ((floor($JourneyDuration / 60)) * 60))."Min";


				// Leg 2
				$dAirportCode1 = $flight['segments'][1]['Origin']['Airport']['AirportCode'];
				$dAirportName1 = $flight['segments'][1]['Origin']['Airport']['AirportName'];
				$dCityName1 = $flight['segments'][1]['Origin']['Airport']['AirportCode'];
				$dCountryCode1 = $flight['segments'][1]['Origin']['Airport']['CountryCode'];

				$aAirportCode1 = $flight['segments'][1]['Destination']['Airport']['AirportCode'];
				$aAirportName1 = $flight['segments'][1]['Destination']['Airport']['AirportName'];
				$aCityName1 = $flight['segments'][1]['Destination']['Airport']['CityName'];
				$aCountryCode1 = $flight['segments'][1]['Destination']['Airport']['CountryCode'];


				$DepTime1 = $flight['segments'][1]['Origin']['DepTime'];
				$ArrTime1 = $flight['segments'][1]['Destination']['ArrTime'];

				$AirlineCode1 = $flight['segments'][1]['Airline']['AirlineCode'];
				$AirlineName1 = $flight['segments'][1]['Airline']['AirlineName'];
				$FlightNumber1 = $flight['segments'][1]['Airline']['FlightNumber'];
				$BookingClass1 = $flight['segments'][1]['Airline']['BookingClass'];
				$CabinClass1 = $flight['segments'][1]['Airline']['CabinClass'];
				$OperatingCarrier1 = $flight['segments'][1]['Airline']['OperatingCarrier'];


				$JourneyDuration1 = $flight['segments'][1]['JourneyDuration'];
				$Duration1 = floor($JourneyDuration1 / 60)."H ".($JourneyDuration1 - ((floor($JourneyDuration1 / 60)) * 60))."Min";

				
				$segment = array("0" =>
						array("marketingcareer"=> "$AirlineCode",
							"marketingcareerName"=> "$AirlineName",
							"marketingflight"=> "$FlightNumber",
							"operatingcareer"=> "$OperatingCarrier",
							"operatingflight"=> "$FlightNumber",
							"departure"=> "$dAirportCode",
							"departureAirport"=> "$dAirportName ",
							"departureLocation"=> "$dCityName , $dCountryCode",                    
							"departureTime" => "$DepTime",
							"arrival"=> "$aAirportCode",                   
							"arrivalTime" => "$ArrTime",
							"arrivalAirport"=> "$aAirportName",
							"arrivalLocation"=> "$aCityName , $aCountryCode",
							"flightduration"=> "$Duration",
							"bookingcode"=> "$BookingClass",
							"seat"=> "$Availabilty"),
					"1" =>
						array("marketingcareer"=> "$AirlineCode1",
						"marketingcareerName"=> "$AirlineName1",
							"marketingflight"=> "$FlightNumber1",
							"operatingcareer"=> "$OperatingCarrier1",
							"operatingflight"=> "$FlightNumber1",
							"departure"=> "$dAirportCode1",
							"departureAirport"=> "$dAirportName1",
							"departureLocation"=> "$dCityName1 , $dCountryCode1",                    
							"departureTime" => "$DepTime1",
							"arrival"=> "$aAirportCode1",                   
							"arrivalTime" => "$ArrTime1",
							"arrivalAirport"=> "$aAirportName1",
							"arrivalLocation"=> "$aCityName1 , $aCountryCode1",
							"flightduration"=> "$Duration1",
							"bookingcode"=> "$BookingClass1",
							"seat"=> "$Availabilty")                                          
					);

					$TransitTime = round(abs(strtotime($DepTime1) - strtotime($ArrTime)) / 60,2);
					$TransitDuration = floor($TransitTime / 60)."H ".($TransitTime - ((floor($TransitTime / 60)) * 60))."Min";
					
					$JourneyTime = $JourneyDuration + $JourneyDuration1 + $TransitTime;
					$TotalDuration = floor($JourneyTime / 60)."H ".($JourneyTime - ((floor($JourneyTime / 60)) * 60))."Min";
					
					$transitDetails = array("transit1"=> $TransitDuration);

					$basic = array("system"=>"FlyHub",
							"segment"=> "2",
							"uId"=> $uId,
							"triptype"=>$TripType,
							"career"=> "$vCarCode",
							"careerName" => "$CarrieerName",
							"BasePrice" => "$BasePrice",
							"Taxes" => "$Taxes",
							"price" => "$TotalFare",
							"clientPrice"=> "$ClientFare",
							"comission"=> "$Commission",
							"comissiontype"=> $ComissionType,
							"comissionvalue"=> $comissionvalue,
							"farecurrency"=> $FareCurrency,
							"airlinescomref"=> $comRef,
							"pricebreakdown"=> $PriceBreakDown,
							"departure"=> "$dAirportCode",                   
							"departureTime" => substr($DepTime,11,5),
							"departureDate" => date("D d M Y", strtotime($DepTime)),
							"arrival"=> "$aAirportCode1",                   
							"arrivalTime" => substr($ArrTime1,11,5),
							"arrivalDate" => date("D d M Y", strtotime($ArrTime1)),
							"flightduration"=> "$TotalDuration",
							"transit" => $transitDetails,
							"bags" => "$Baggage",
							"seat" => "$Availabilty",
							"class" => "$CabinClass",
							"refundable"=> "$Refundable",
							"segments" => $segment,
							"hold"=> "$Hold",												
							"SearchID"=> $SearchID,
							"ResultID"=> $ResultID);
										
					array_push($FlyHubResponse, $basic);


			}else if($segments == 3){

				///Leg 1
				$dAirportCode = $flight['segments'][0]['Origin']['Airport']['AirportCode'];
				$dAirportName = $flight['segments'][0]['Origin']['Airport']['AirportName'];
				$dCityName = $flight['segments'][0]['Origin']['Airport']['CityName'];
				$dCountryCode = $flight['segments'][0]['Origin']['Airport']['CountryCode'];

				$aAirportCode = $flight['segments'][0]['Destination']['Airport']['AirportCode'];
				$aAirportName = $flight['segments'][0]['Destination']['Airport']['AirportName'];
				$aCityName = $flight['segments'][0]['Destination']['Airport']['CityName'];
				$aCountryCode = $flight['segments'][0]['Destination']['Airport']['CountryCode'];


				$DepTime = $flight['segments'][0]['Origin']['DepTime'];
				$ArrTime = $flight['segments'][0]['Destination']['ArrTime'];

				$AirlineCode = $flight['segments'][0]['Airline']['AirlineCode'];
				$AirlineName = $flight['segments'][0]['Airline']['AirlineName'];
				$FlightNumber = $flight['segments'][0]['Airline']['FlightNumber'];
				$BookingClass = $flight['segments'][0]['Airline']['BookingClass'];
				$CabinClass= $flight['segments'][0]['Airline']['CabinClass'];
				$OperatingCarrier = $flight['segments'][0]['Airline']['OperatingCarrier'];

				if(isset($flight['segments'][0]['baggageDetails'][0]['Checkin'])){				
					$Baggage = $flight['segments'][0]['baggageDetails'][0]['Checkin'];
				} else {
					$Baggage = 0;
				}
				
				$JourneyDuration = $flight['segments'][0]['JourneyDuration'];
				$Duration = floor($JourneyDuration / 60)."H ".($JourneyDuration - ((floor($JourneyDuration / 60)) * 60))."Min";


				// Leg 2
				$dAirportCode1 = $flight['segments'][1]['Origin']['Airport']['AirportCode'];
				$dAirportName1 = $flight['segments'][1]['Origin']['Airport']['AirportName'];
				$dCityName1 = $flight['segments'][1]['Origin']['Airport']['CityName'];
				$dCountryCode1 = $flight['segments'][1]['Origin']['Airport']['CountryCode'];

				$aAirportCode1 = $flight['segments'][1]['Destination']['Airport']['AirportCode'];
				$aAirportName1 = $flight['segments'][1]['Destination']['Airport']['AirportName'];
				$aCityName1 = $flight['segments'][1]['Destination']['Airport']['CityName'];
				$aCountryCode1 = $flight['segments'][1]['Destination']['Airport']['CountryCode'];


				$DepTime1 = $flight['segments'][1]['Origin']['DepTime'];
				$ArrTime1 = $flight['segments'][1]['Destination']['ArrTime'];

				$AirlineCode1 = $flight['segments'][1]['Airline']['AirlineCode'];
				$AirlineName1 = $flight['segments'][1]['Airline']['AirlineName'];
				$FlightNumber1 = $flight['segments'][1]['Airline']['FlightNumber'];
				$BookingClass1 = $flight['segments'][1]['Airline']['BookingClass'];
				$CabinClass1 = $flight['segments'][1]['Airline']['CabinClass'];
				$OperatingCarrier1 = $flight['segments'][1]['Airline']['OperatingCarrier'];

				//$Baggage1 = $flight['segments'][1]['Baggage'];
				$JourneyDuration1 = $flight['segments'][1]['JourneyDuration'];
				$Duration1 = floor($JourneyDuration1 / 60)."H ".($JourneyDuration1 - ((floor($JourneyDuration1 / 60)) * 60))."Min";


				// Leg 3
				$dAirportCode2 = $flight['segments'][2]['Origin']['Airport']['AirportCode'];
				$dAirportName2 = $flight['segments'][2]['Origin']['Airport']['AirportName'];
				$dCityName2 = $flight['segments'][2]['Origin']['Airport']['AirportCode'];
				$dCountryCode2 = $flight['segments'][2]['Origin']['Airport']['CountryCode'];

				$aAirportCode2 = $flight['segments'][2]['Destination']['Airport']['AirportCode'];
				$aAirportName2 = $flight['segments'][2]['Destination']['Airport']['AirportName'];
				$aCityName2 = $flight['segments'][2]['Destination']['Airport']['CityName'];
				$aCountryCode2 = $flight['segments'][2]['Destination']['Airport']['CountryCode'];


				$DepTime2 = $flight['segments'][2]['Origin']['DepTime'];
				$ArrTime2 = $flight['segments'][2]['Destination']['ArrTime'];

				$AirlineCode2 = $flight['segments'][2]['Airline']['AirlineCode'];
				$AirlineName2 = $flight['segments'][2]['Airline']['AirlineName'];
				$FlightNumber2 = $flight['segments'][2]['Airline']['FlightNumber'];
				$BookingClass2 = $flight['segments'][2]['Airline']['BookingClass'];
				$CabinClass2 = $flight['segments'][2]['Airline']['CabinClass'];
				$OperatingCarrier2 = $flight['segments'][2]['Airline']['OperatingCarrier'];

				//$Baggag2 = $flight['segments'][2]['Baggage'];
				$JourneyDuration2 = $flight['segments'][2]['JourneyDuration'];
				$Duration2 = floor($JourneyDuration1 / 60)."H ".($JourneyDuration1 - ((floor($JourneyDuration1 / 60)) * 60))."Min";


				$segment = array("0" => array("marketingcareer"=> "$OperatingCarrier",
										"marketingcareerName"=> "$AirlineName",
										"marketingflight"=> "$FlightNumber",
										"operatingcareer"=> "$OperatingCarrier",
										"operatingflight"=> "$FlightNumber",
										"departure"=> "$dAirportCode",
										"departureAirport"=> "$dAirportName ",
										"departureLocation"=> "$dCityName , $dCountryCode",                    
										"departureTime" => "$DepTime",
										"arrival"=> "$aAirportCode",                   
										"arrivalTime" => "$ArrTime",
										"arrivalAirport"=> "$aAirportName",
										"arrivalLocation"=> "$aCityName , $aCountryCode",
										"flightduration"=> "$Duration",
										"bookingcode"=> "$BookingClass",
										"seat"=> "$Availabilty"),
								"1" =>
									array("marketingcareer"=> "$OperatingCarrier1",
										"marketingcareerName"=> "$AirlineName1",
										"marketingflight"=> "$FlightNumber1",
										"operatingcareer"=> "$OperatingCarrier1",
										"operatingflight"=> "$FlightNumber1",
										"departure"=> "$dAirportCode1",
										"departureAirport"=> "$dAirportName1 ",
										"departureLocation"=> "$dCityName1 , $dCountryCode1",                    
										"departureTime" => "$DepTime1",
										"arrival"=> "$aAirportCode1",                   
										"arrivalTime" => "$ArrTime1",
										"arrivalAirport"=> "$aAirportName1",
										"arrivalLocation"=> "$aCityName1 , $aCountryCode1",
										"flightduration"=> "$Duration1",
										"bookingcode"=> "$BookingClass1",
										"seat"=> "$Availabilty"),                                          
										
								"2" =>array("marketingcareer"=> "$OperatingCarrier2",
											"marketingcareerName"=> "$AirlineName2",
											"marketingflight"=> "$FlightNumber2",
											"operatingcareer"=> "$OperatingCarrier2",
											"operatingflight"=> "$FlightNumber2",
											"departure"=> "$dAirportCode2",
											"departureAirport"=> "$dAirportName2",
											"departureLocation"=> "$dCityName2 , $dCountryCode2",                    
											"departureTime" => "$DepTime2",
											"arrival"=> "$aAirportCode2",                   
											"arrivalTime" => "$ArrTime2",
											"arrivalAirport"=> "$aAirportName2",
											"arrivalLocation"=> "$aCityName2 , $aCountryCode2",
											"flightduration"=> "$Duration2",
											"bookingcode"=> "$BookingClass2",
											"seat"=> "$Availabilty"),								
									);

								$TransitTime = round(abs(strtotime($DepTime1) - strtotime($ArrTime)) / 60,2);
								$TransitDuration = floor($TransitTime / 60)."H ".($TransitTime - ((floor($TransitTime / 60)) * 60))."Min";

								$TransitTime1 = round(abs(strtotime($DepTime2) - strtotime($ArrTime1)) / 60,2);
								$TransitDuration1 = floor($TransitTime1 / 60)."H ".($TransitTime1 - ((floor($TransitTime1 / 60)) * 60))."Min";

								$JourneyTime = $JourneyDuration + $JourneyDuration1 + $JourneyDuration2 + $TransitTime + $TransitTime1;
								$TotalDuration = floor($JourneyTime / 60)."H ".($JourneyTime - ((floor($JourneyTime / 60)) * 60))."Min";
								
								$transitDetails = array("transit1"=> $TransitDuration,
														"transit2"=> $TransitDuration1);
								

								$basic = array("system"=>"FlyHub",
												"segment"=> "3",
												"uId"=> $uId,
												"triptype"=>$TripType,
												"career"=> $vCarCode,
												"careerName" => "$CarrieerName",
												"BasePrice" => "$BasePrice",
												"Taxes" => "$Taxes",
												"price" => "$TotalFare",
												"clientPrice"=> "$ClientFare",
												"comission"=> "$Commission",
												"comissiontype"=> $ComissionType,
												"comissionvalue"=> $comissionvalue,
												"farecurrency"=> $FareCurrency,
												"airlinescomref"=> $comRef,
												"pricebreakdown"=> $PriceBreakDown,
												"departure"=> "$dAirportCode",                   
												"departureTime" => substr($DepTime,11,5),
												"departureDate" => date("D d M Y", strtotime($DepTime)),
												"arrival"=> "$aAirportCode2",                   
												"arrivalTime" => substr($ArrTime2,11,5),
												"arrivalDate" => date("D d M Y", strtotime($ArrTime2)),
												"flightduration"=> "$TotalDuration",
												"transit" => $transitDetails,
												"bags" => "$Baggage",
												"seat" => "$Availabilty",
												"class" => "$CabinClass",
												"refundable"=> "$Refundable",
												"segments" => $segment,
												"hold"=> "$Hold",											
												"SearchID"=> $SearchID,
												"ResultID"=> $ResultID
												
													
											);
							array_push($FlyHubResponse, $basic);


			}
		}
					



	$json_string = json_encode($FlyHubResponse, JSON_PRETTY_PRINT);
	print_r($json_string);
	
			
}else if(array_key_exists("return",$_GET) && array_key_exists("journeyfrom",$_GET) && array_key_exists("journeyto",$_GET) && array_key_exists("departuredate",$_GET) &&
 	array_key_exists("returndate",$_GET) && array_key_exists("adult",$_GET) && (array_key_exists("child",$_GET) && array_key_exists("infant",$_GET))){
				
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

	$Result = json_decode($flyhubresponse,true);

	
	$FlightListFlyHub = $Result['Results'];
	$SearchID = $Result['SearchId'];
	$FlyHubResponse = array();

	$f=0;
	foreach($FlightListFlyHub as $flight){
	$f++;
		$Validatingcarrier = $flight['Validatingcarrier'];

		$sql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$Validatingcarrier' ");
		$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

		if(!empty($row)){
			$CarrieerName = $row['name'];                       
		} 
		
		$segments = count($flight['segments']);
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

		$markup = 0;
		if($Validatingcarrier == '6E' || 'SG' || 'J9'){
			$markup = 500;
		}

		$TotalFare = (int)$flight['TotalFare'] + $markup;

		$ClientFare = $BasePrice + $Taxes + $markup;
		$Commission = $ClientFare - $TotalFare;
		
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

		
		if($flight['IsRefundable'] == 1){
			$Refundable = "Refundable";
		}else{
			$Refundable = "Nonrefundable";
		}
		
		$Availabilty = $flight['Availabilty'];
		$ResultID = $flight['ResultID'];
		
		$uId = sha1(md5(time()).''.rand());
		
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
								"comissiontype"=> $ComissionType,
								"comissionvalue"=> $comissionvalue,
								"farecurrency"=> $FareCurrency,
								"airlinescomref"=> $comRef,
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
								"comissiontype"=> $ComissionType,
								"comissionvalue"=> $comissionvalue,
								"farecurrency"=> $FareCurrency,
								"airlinescomref"=> $comRef,
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

?>