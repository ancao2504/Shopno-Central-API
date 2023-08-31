<?php

require "../config.php";
require "sabretoken.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
	$_POST = json_decode(file_get_contents('php://input'), true);
	$TripType = isset($_POST['triptype']) ? $_POST['triptype'] : "Invalid Triptype";

	$today_date = date('Y-m-d');
	$CurrentDateTime = date('Y-m-d', strtotime($today_date . ' +1 day'));


	$control = mysqli_query($conn,"SELECT * FROM control where id=1");
	$controlrow = mysqli_fetch_array($control,MYSQLI_ASSOC);

	if(!empty($controlrow)){
		$Sabre = $controlrow['sabre'];
		$Sabre = 1;
		// $MistyFly = $controlrow['mistyfly'];
		$Galileo = $controlrow['galileo'];										
		$Galileo = 0;										
	}

	if(!empty($_POST)){
		if($Sabre == 1){
			$SabreRequest = SabreJsonRequest($_POST);
			$SabreData = SabreDataFetch($SabreToken, $SabreRequest);
			$SabreDataMapping = SabreDataMapping($SabreData);

			print_r($SabreDataMapping);
		}else if($MistyFly == 2){
			
		}else if($Galileo == 2){
			
		}
	}else{
		$response ['status']='error';
		$response['message'] = 'Invalid Request';
		
		return $response;
	}
	

}

function SabreJsonRequest($_Req){
	$Connection = isset($_Req['connection']) ? $_Req['connection'] : '2';
	$CabinClass = isset($_Req['cabinclass']) ? $_Req['cabinclass'] : 'Y';
	$AdultCount =  isset($_Req['adult']) ? $_Req['adult'] : 1;
	$ChildCount =  isset($_Req['child']) ? $_POST['child'] : 0;
	$InfantCount = isset($_Req['infant']) ? $_Req['infant'] : 0;
	$Segments =   $_Req['segments'];

	$SabreRequestPax = array();
	if($AdultCount > 0){
		$PaxQualtity = array(
							"Code"=> "ADT",
							"Quantity"=> $AdultCount
						);
		array_push($SabreRequestPax, $PaxQualtity);
	}if($ChildCount > 0){
		$PaxQualtity = array(
							"Code"=> "CNN",
							"Quantity"=> $ChildCount
						);
		array_push($SabreRequestPax, $PaxQualtity);
		
	}if($InfantCount > 0){
		$PaxQualtity = array(
							"Code"=> "INF",
							"Quantity"=> $InfantCount
						);
		array_push($SabreRequestPax, $PaxQualtity);
	}
		 				
							
			$SegmentList = array();

			$i=0;
			foreach($Segments as $Segment){
				$i++;
				$DepFrom = $Segment['depfrom'];
				$ArrTo = $Segment['arrto'];
				$DepDate = $Segment['depdate'].'T00:00:00';
				
				$SingleSegment = array(
						"RPH"=> "$i",
						"DepartureDateTime"=> $DepDate,
						"OriginLocation"=> array(
							"LocationCode"=> $DepFrom
						),
						"DestinationLocation"=> array(
							"LocationCode"=> $ArrTo
						)
				);

				array_push($SegmentList, $SingleSegment );
			}


			$SabreJsonRequest = '{
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
					"OriginDestinationInformation": '.json_encode($SegmentList).',
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
						},
						"CabinPref": [
							{
							"Cabin": "'.$CabinClass.'",
							"PreferLevel": "Preferred"
							}
						],
						"MaxStopsQuantity": '.$Connection.' 
					},
					"TravelerInfoSummary": {
						"AirTravelerAvail": [{
								"PassengerTypeQuantity": '.json_encode($SabreRequestPax).'
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

			return $SabreJsonRequest;

}


function SabreDataFetch($SabreToken, $SabreRequest){
		
		$curl = curl_init();
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
		CURLOPT_POSTFIELDS => $SabreRequest,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Conversation-ID: 2021.01.DevStudio',
			'Authorization: Bearer '.$SabreToken,
			),
		));

		$response = curl_exec($curl);
		$SearchResponse = json_decode($response, true);

		return $SearchResponse;

	
}

function SabreDataMapping($SabreData){
	$AllFlights = $SabreData['groupedItineraryResponse']['itineraryGroups'][0]['itineraries'];
	$AllBaggage = $SabreData['groupedItineraryResponse']['baggageAllowanceDescs'];
	$AllLegDescs  = $SabreData['groupedItineraryResponse']['legDescs'];
	$AllscheduleDescs  = $SabreData['groupedItineraryResponse']['scheduleDescs'];

	$FlightItenary = array();
	foreach($AllFlights as $flights){

		$AllPassenger = $flights['pricingInformation'][0]['fare']['passengerInfoList'];
	
		//Single Info
		$ValidatingCarrier =  $flights['pricingInformation'][0]['fare']['validatingCarrierCode'];
		$CarrierName = $ValidatingCarrier;
		$BaseFare  = $flights['pricingInformation'][0]['fare']['totalFare']['equivalentAmount'];
		$Taxes  = $flights['pricingInformation'][0]['fare']['totalFare']['totalTaxAmount'];
		$TotalFare  = $flights['pricingInformation'][0]['fare']['totalFare']['totalPrice'];
		$nonRefundable = $flights['pricingInformation'][0]['fare']['passengerInfoList'][0]['passengerInfo']['nonRefundable'];
		$AgentFare = '';

		if(isset($flights['pricingInformation'][0]['fare']['lastTicketDate'])){
				
			$lastTicketDate = $flights['pricingInformation'][0]['fare']['lastTicketDate'];
			$lastTicketTime = $flights['pricingInformation'][0]['fare']['lastTicketTime'];
			$TimeLimit = "$lastTicketDate $lastTicketTime";								
		}else{
			$TimeLimit = " ";
		}

		$PriceBreakDown =array();
		foreach($AllPassenger as $allPassenger){
			$PaxType =	$allPassenger['passengerInfo']['passengerType'];
			$paxCount = $allPassenger['passengerInfo']['passengerNumber'];
			$PaxtotalFare =	$allPassenger['passengerInfo']['passengerTotalFare']['totalFare'];
			$totalTaxAmount =	$allPassenger['passengerInfo']['passengerTotalFare']['totalTaxAmount'];
			$PaxequivalentAmount =	$allPassenger['passengerInfo']['passengerTotalFare']['equivalentAmount'];

			$BaggageAllowance = $allPassenger['passengerInfo']['baggageInformation'];
			
			$Baggage = array();
			foreach($BaggageAllowance as $baggageAllowance){
				$BagAirlineCode = $baggageAllowance['airlineCode'];
				$Allowance = $AllBaggage[$baggageAllowance['allowance']['ref']-1];

				$SinglePaxBag = array("Airline"=> $BagAirlineCode,
									  "Allowance"=> $Allowance);

				array_push($Baggage, $SinglePaxBag);			
			}
			
			$singlePassenger = array("PaxType"=> $PaxType,
									"BaseFare"=> $PaxequivalentAmount,
									"Taxes"=> $totalTaxAmount,
									"TotalFare"=> $PaxtotalFare,
									"PaxCount"=> $paxCount,
									"Bag"=> $Baggage);
			
			array_push($PriceBreakDown, $singlePassenger);
		}


		$i=0;
		$AllLegsInfo = array();
		$AllRoutes = $flights['pricingInformation'][0]['fare']['passengerInfoList'][0]['passengerInfo']['fareComponents'];
		foreach($flights['legs'] as $leg){
			$i++;
			$LegDescRef = $leg['ref']-1;
			$SingleLegDes = $AllLegDescs[$LegDescRef]['schedules'];

			$AllLegs = $SabreData['groupedItineraryResponse']['itineraryGroups'][0]['groupDescription']['legDescriptions'];
			$departureDate = $AllLegs[$i-1]['departureDate'];
			$AllSegments = array("DepDate"=> $AllLegs[$i-1]['departureDate'],
								 "DepFrom"=> $AllLegs[$i-1]['departureLocation'],
								 "ArrTo"=> $AllLegs[$i-1]['arrivalLocation']);
			
			$Segments = array();
			$AllSegmentCode = $AllRoutes[$i-1]['segments'];
			$x=0;
			foreach($SingleLegDes as $legDesc){
				$x++;
				$SchedulesRef = $legDesc['ref']-1;
				$Schedules = $AllscheduleDescs[$SchedulesRef];

				if(isset($legDesc['departureDateAdjustment'])){
					$DepDate = date('Y-m-d', strtotime("+1 day", strtotime($departureDate)));
				}else{
					$DepDate = $departureDate;
				}
				
				if(isset($Schedules['arrival']['dateAdjustment'])){
					$ArrDate = date('Y-m-d', strtotime("+1 day", strtotime($DepDate)));
				}else{
					$ArrDate = $DepDate;
				}

				$OperatedBy = isset($Schedules['arrival']['disclosure']) ?
									$Schedules['arrival']['disclosure'] :
								 	$Schedules['carrier']['operating'];
				
				$SegmentCode = $AllSegmentCode[0]['segment'];

				$SingleSegments  = array("MarketingCarrier"=> $Schedules['carrier']['marketing'],
										 "MarketingFlightNumber"=> $Schedules['carrier']['marketingFlightNumber'],
										 "OperatingCarrier"=> $Schedules['carrier']['operating'],
										 "OperatingFlightNumber"=> $Schedules['carrier']['operatingFlightNumber'],
										 "DepFrom"=> $Schedules['departure']['airport'],
										 "DepAirPort"=> $Schedules['departure']['airport'],
										 "DepTime"=> $DepDate.'T'.$Schedules['departure']['time'],
										 "ArrFrom"=> $Schedules['arrival']['airport'],
										 "ArrAirPort"=> $Schedules['arrival']['airport'],
										 "ArrTime"=> $ArrDate.'T'.$Schedules['arrival']['time'],
										 "OperatedBy"=> $OperatedBy,
										 "StopCount"=> $Schedules['stopCount'],
									     "Duration"=> $Schedules['elapsedTime'],
										 "SegmentCode"=> $SegmentCode);
									
				array_push($Segments, $SingleSegments);
			
			}

			
			$AllSegments["Segments"]= $Segments;

			array_push($AllLegsInfo, $AllSegments);
		}

		
		$SingleFlights = array("Carrier"=> $ValidatingCarrier,
								"CarrierName"=> $CarrierName,
								"BaseFare"=> $BaseFare,
								"Taxes"=> $Taxes,
								"TotalFare"=> $TotalFare,
								"AgentFare"=> $AgentFare,
								"TimeLimit"=> $TimeLimit,
								"nonRefundable"=> $nonRefundable,
								"PriceBreakDown"=> $PriceBreakDown,
								"AllLegs"=> $AllLegsInfo);

		array_push($FlightItenary, $SingleFlights);
	}

	return json_encode($FlightItenary);
	
}

function Airport($AirPortCode){
	include '../config.php';
	$sql = mysqli_query($conn,"SELECT name, cityName,countryCode FROM airports WHERE code='$AirPortCode' ");
	$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);
	return $row;
}

function Airlines($AirlinesCode){
	include '../config.php';
	$sql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$AirlinesCode' ");
	$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);
	return $row;
}




?>