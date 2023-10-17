<?php

include "../config.php";
include "Token.php";
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
	$_POST = json_decode(file_get_contents('php://input'), true);
	$TripType = isset($_POST['tripType']) ? $_POST['tripType'] : "";
	

	$today_date = date('Y-m-d');
	$CurrentDateTime = date('Y-m-d', strtotime($today_date . ' +1 day'));


	$control = mysqli_query($conn,"SELECT * FROM control where id=1");
	$controlrow = mysqli_fetch_array($control,MYSQLI_ASSOC);

	if(!empty($controlrow)){
		$Sabre = $controlrow['sabre'];
		$MistyFly = $controlrow['mistyfly'];
		$Galileo = $controlrow['galileo'];										
	}

	if(!empty($_POST)){
		if($Sabre == 1){
			$SabreRequest = SabreJsonRequest($_POST);
			$SabreData = SabreDataFetch($SabreToken, $SabreRequest);
			$SabreDataMapping = SabreDataMapping($SabreData);
		}
		if($MistyFly == 2){
			
		}
		if($Galileo == 1){
			$GalileoRequest = GalileoRequest($_POST);
			$GalileoData = GalileoDataFetch($GalileoRequest);
			$GalileoDataMapping = GalileoDataMapping($GalileoData);
		}
	}else{
		$response ['status']='error';
		$response['message'] = 'Invalid Request';
		
		echo  json_encode($response);
	}

	if($Sabre == 1 && $Galileo == 1){
		$Priority_Array = array();
		$All = array(); 

		$All = array_merge($SabreDataMapping, $GalileoDataMapping);
		array_multisort(array_column($All, 'AgentFare'), SORT_ASC, $All);
	
		// $Priority = "Sabre";

		// if($Priority == "Sabre"){
		// 	foreach($SabreDataMapping as $SabreDS){
		// 		$Carrier = $SabreDS['Carrier'];
		// 		$FlightNo = $SabreDS['AllLegs'][0]['Segments'][0]['MarketingFlightNumber'];
		// 		$BookingCode = $SabreDS['AllLegs'][0]['Segments'][0]['SegmentCode']['bookingCode'];

		// 		$index=0;
		// 		foreach($GalileoDataMapping as $GallData){
		// 			$index++;
		// 			$GLCarrier = $GallData['Carrier'];
		// 			$GLFlightNo = $GallData['AllLegs'][0]['Segments'][0]['MarketingFlightNumber'];
		// 			$GLBookingCode = $GallData['AllLegs'][0]['Segments'][0]['SegmentCode']['bookingCode'];

		// 			if($Carrier != $GLCarrier && $FlightNo != $GLFlightNo && $BookingCode != $GLBookingCode){
		// 				unset($GalileoDataMapping[$index-1]);
		// 			}

		// 		}
		// 	}

		// 	$All = array_merge($SabreDataMapping, $GalileoDataMapping);

		// }else if($Priority == "Galileo"){
		// 	foreach($GalileoDataMapping as $SabreDS){
		// 		$Carrier = $SabreDS['Carrier'];
		// 		$FlightNo = $SabreDS['AllLegs'][0]['Segments'][0]['MarketingFlightNumber'];
		// 		$BookingCode = $SabreDS['AllLegs'][0]['Segments'][0]['SegmentCode']['bookingCode'];
		// 		$index=0;
		// 		foreach($SabreDataMapping as $GallData){
		// 			$index++;
		// 			$GLCarrier = $GallData['Carrier'];
		// 			$GLFlightNo = $GallData['AllLegs'][0]['Segments'][0]['MarketingFlightNumber'];
		// 			$GLBookingCode = $GallData['AllLegs'][0]['Segments'][0]['SegmentCode']['bookingCode'];

		// 			if($Carrier != $GLCarrier && $FlightNo != $GLFlightNo && $BookingCode != $GLBookingCode){
		// 				unset($SabreDataMapping[$index-1]);
		// 			}

		// 		}
		// 	}

		// 	$All = array_merge($GalileoDataMapping, $SabreDataMapping);
		// }

		foreach($All as $all){
			$SingleFlights = array("System"=>"Sabre",
								"Carrier"=> $ValidatingCarrier,
								"CarrierName"=>  $CarrierName,
								"BaseFare"=> $BaseFare,
								"Taxes"=> $Taxes,
								"TotalFare"=> $TotalFare,
								"AgentFare"=> $AgentFare,
								"TimeLimit"=> $TimeLimit,
								"nonRefundable"=> $nonRefundable,
								"PriceBreakDown"=> $PriceBreakDown,
								"AllLegs"=> $AllLegsInfo);
			
		}


		$json_string = json_encode($All, JSON_PRETTY_PRINT);
		print_r($json_string);
	}
	

}

function SabreJsonRequest($_Req){
	$Connection = isset($_Req['connection']) ? $_Req['connection'] : '2';
	$CabinClass = isset($_Req['cabinclass']) ? $_Req['cabinclass'] : 'Y';
	$AdultCount =  isset($_Req['adultCount']) ? $_Req['adultCount'] : 1;
	$ChildCount =  isset($_Req['childCount']) ? $_Req['childCount'] : 0;
	$InfantCount = isset($_Req['infantCount']) ? $_Req['infantCount'] : 0;
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
			$DepFrom = $Segment['DepFrom'];
			$ArrTo = $Segment['ArrTo'];
			$DepDate = $Segment['Date'].'T00:00:00';
			
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

function GalileoRequest($_Req){
	$Connection = isset($_Req['connection']) ? $_Req['connection'] : '2';
	$CabinClass = isset($_Req['cabinclass']) ? $_Req['cabinclass'] : 'Economy';
	$AdultCount =  isset($_Req['adultCount']) ? $_Req['adultCount'] : 1;
	$ChildCount =  isset($_Req['childCount']) ? $_POST['childCount'] : 0;
	$InfantCount = isset($_Req['infantCount']) ? $_Req['infantCount'] : 0;
	$Segments =   $_Req['segments'];

	$Gallpax= array();
				
	if($AdultCount > 0 && $ChildCount> 0 && $InfantCount> 0){
	
		for($i = 1; $i <= $AdultCount ; $i++){
			$adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v50_0" Code="ADT" BookingTravelerRef="1'.$i.'" />';
			array_push($Gallpax, $adultcount);
		}
		for($i = 1; $i <= $ChildCount ; $i++){
			$childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v50_0" Code="CNN" Age="07" BookingTravelerRef="2'.$i.'" />';
			array_push($Gallpax, $childcount);
		}
		for($i = 1; $i <= $InfantCount ; $i++){
			$infantscount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v50_0" Code="INF" Age="1" DOB="2021-06-04" BookingTravelerRef="3'.$i.'" />';
			array_push($Gallpax, $infantscount);
		
		}

			
	}else if($AdultCount > 0 && $ChildCount > 0){

		for($i = 1; $i <= $AdultCount ; $i++){
			$adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v50_0" Code="ADT" BookingTravelerRef="ADT1'.$i.'" />';
			array_push($Gallpax, $adultcount);
		}
		for($i = 1; $i <= $ChildCount ; $i++){
			$childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v50_0" Code="CNN" Age="04" BookingTravelerRef="CNN2'.$i.'" />';
			array_push($Gallpax, $childcount);
		}
	
	}else if($AdultCount > 0 && $InfantCount > 0){
	
		for($i = 1; $i <= $InfantCount ; $i++){
			$adultdata ='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v50_0" Code="ADT" BookingTravelerRef="ADT1'.$i.'" />';
			array_push($Gallpax, $adultdata);
		}
		for($i = 1; $i <= $InfantCount ; $i++){
			$infantdata = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v50_0" Code="INF" Age="1" BookingTravelerRef="INF3'.$i.'" />';
			array_push($Gallpax, $infantdata);	
		}

	}else{
		for($i = 1; $i <= $AdultCount ; $i++){
			$adultdata ='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v50_0" Code="ADT" BookingTravelerRef="ADT1'.$i.'" />';
			array_push($Gallpax, $adultdata);
		}
	}

	$PassengerData = implode(" ",$Gallpax);						
	$SegmentList = array();

	$i=0;
	foreach($Segments as $Segment){
		$i++;
		$DepFrom = $Segment['DepFrom'];
		$ArrTo = $Segment['ArrTo'];
		$DepDate = $Segment['Date'];

		$SingleSegment = <<<EOM
			<SearchAirLeg>
				<SearchOrigin>
					<CityOrAirport xmlns="http://www.travelport.com/schema/common_v50_0" Code="$DepFrom" PreferCity="true" />
				</SearchOrigin>
				<SearchDestination>
					<CityOrAirport xmlns="http://www.travelport.com/schema/common_v50_0" Code="$ArrTo" PreferCity="true" />
				</SearchDestination>
				<SearchDepTime PreferredTime="$DepDate" />
			</SearchAirLeg>
		EOM;
		

		array_push($SegmentList, $SingleSegment );
	}

	$SegmentData = implode(" ",$SegmentList);

	$GalileoRequest = <<<EOM
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
		<soapenv:Header/>
		<soapenv:Body>
			<LowFareSearchReq xmlns="http://www.travelport.com/schema/air_v50_0" TraceId="FFI-KayesFahim" TargetBranch="P4218912" ReturnUpsellFare="true">
					<BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v50_0" OriginApplication="uAPI" />
						$SegmentData
					<AirSearchModifiers>
						<PreferredProviders>
						<Provider xmlns="http://www.travelport.com/schema/common_v50_0" Code="1G" />
						</PreferredProviders>
					</AirSearchModifiers>
						$PassengerData
					<AirPricingModifiers>
						<AirPricingModifiers ETicketability="Required" FaresIndicator="PublicAndPrivateFares" />
					</AirPricingModifiers>
				</LowFareSearchReq>
		</soapenv:Body>
		</soapenv:Envelope>
	EOM;
	

	return  $GalileoRequest;

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

function GalileoDataFetch($GalileoRequest){

	$curl = curl_init();
	curl_setopt_array($curl, array(
	CURLOPT_URL => 'https://apac.universal-api.travelport.com/B2BGateway/connect/uAPI/AirService',
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => '',
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 0,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => 'POST',
	CURLOPT_POSTFIELDS => $GalileoRequest,
	CURLOPT_HTTPHEADER => array(
		'Content-Type: application/xml',
		'Authorization: Basic VW5pdmVyc2FsIEFQSS91QVBJNDQ0NDgzNzY1NS04M2ZlNTEwMTpLL3MzLTVTeTRj'
		),
	));

	$GalileoResponse = curl_exec($curl);
	curl_close($curl);

	if(!empty($GalileoResponse)){
	
		//$GalileoResponse = file_get_contents("data.xml");
		$GalileoResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $GalileoResponse);
		$xml = new SimpleXMLElement($GalileoResult); /// to do 
				
		if(isset($xml->xpath('//airLowFareSearchRsp')[0])){
			$body = $xml->xpath('//airLowFareSearchRsp')[0];	
			$result = json_decode(json_encode((array)$body), TRUE);
			return $result;
		}else{
			$return = array();
			return $return;
		}
	}else{
		
	}
}

function SabreDataMapping($SabreData){
	$System = 'SABRE';
	$AllFlights = $SabreData['groupedItineraryResponse']['itineraryGroups'][0]['itineraries'];
	$AllBaggage = $SabreData['groupedItineraryResponse']['baggageAllowanceDescs'];
	$AllLegDescs  = $SabreData['groupedItineraryResponse']['legDescs'];
	$AllscheduleDescs  = $SabreData['groupedItineraryResponse']['scheduleDescs'];

	$FlightItenary = array();
	foreach($AllFlights as $flights){

		$AllPassenger = $flights['pricingInformation'][0]['fare']['passengerInfoList'];
	
		//Single Info
		$ValidatingCarrier =  $flights['pricingInformation'][0]['fare']['validatingCarrierCode'];
		
		
		$CarrierName =  Airlines($ValidatingCarrier);
		$BaseFare  = $flights['pricingInformation'][0]['fare']['totalFare']['equivalentAmount'];
		$Taxes  = $flights['pricingInformation'][0]['fare']['totalFare']['totalTaxAmount'];
		$TotalFare  = $flights['pricingInformation'][0]['fare']['totalFare']['totalPrice'];
		$nonRefundable = $flights['pricingInformation'][0]['fare']['passengerInfoList'][0]['passengerInfo']['nonRefundable'];
		$AgentFare = $BaseFare + $Taxes;
		//$AgentFare = FareRulesPolicy($System, $ValidatingCarrier,$BaseFare, $Taxes);

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
			$AllSegmentCode = isset($AllRoutes[$i-1]['segments']) ? $AllRoutes[$i-1]['segments'] : $AllRoutes[0]['segments'];
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
										 "MarketingCarrierName"=>  Airlines($Schedules['carrier']['marketing']),
										 "MarketingFlightNumber"=> $Schedules['carrier']['marketingFlightNumber'],
										 "OperatingCarrier"=> $Schedules['carrier']['operating'],
										 "OperatingFlightNumber"=> $Schedules['carrier']['operatingFlightNumber'],
										 "OperatingCarrierName"=>  Airlines($Schedules['carrier']['operating']),
										 "DepFrom"=> $Schedules['departure']['airport'],
										 "DepAirPort"=> AirportName($Schedules['departure']['airport']),
										 "DepLocation"=> AirportLocation($Schedules['departure']['airport']),
										 "DepTime"=> $DepDate.'T'.$Schedules['departure']['time'],
										 "ArrTo"=> $Schedules['arrival']['airport'],
										 "ArrAirPort"=> AirportName($Schedules['arrival']['airport']),
										 "ArrLocation"=> AirportLocation($Schedules['arrival']['airport']),
										 "ArrTime"=> $ArrDate.'T'.$Schedules['arrival']['time'],
										 "OperatedBy"=> Airlines($OperatedBy),
										 "StopCount"=> $Schedules['stopCount'],
									     "Duration"=> $Schedules['elapsedTime'],
										 "SegmentCode"=> $SegmentCode);
									
				array_push($Segments, $SingleSegments);
			
			}

			
			$AllSegments["Segments"]= $Segments;

			array_push($AllLegsInfo, $AllSegments);
		}

		
		$SingleFlights = array("System"=>"Sabre",
								"Carrier"=> $ValidatingCarrier,
								"CarrierName"=>  $CarrierName,
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

	return $FlightItenary;
	
}

function GalileoDataMapping($GalileoData){
	
	if(!empty($GalileoData)){
		$System = "Galileo";
		$AllFlight = $GalileoData['airAirPricePointList']['airAirPricePoint'];
		$airAirSegmentList =  $GalileoData['airAirSegmentList']['airAirSegment'];
		$airFareInfoList = $GalileoData['airFareInfoList']['airFareInfo'];

		$airAirSegment = array();
		$AllBaggage = array();
		
		if(isset($airFareInfoList[0])){
			foreach($airFareInfoList as $airFareInfos){
				$key = $airFareInfos['@attributes']['Key'];
				$FareBasis =  $airFareInfos['@attributes']['FareBasis'];

				if(isset($airFareInfos['airBaggageAllowance']['airNumberOfPieces'])){
					$Baggage = $airFareInfos['airBaggageAllowance']['airNumberOfPieces'];
				}else if($airFareInfos['airBaggageAllowance']['airMaxWeight']){
					$Value = $airFareInfos['airBaggageAllowance']['airMaxWeight']['@attributes']['Value'];
					$Unit = $airFareInfos['airBaggageAllowance']['airMaxWeight']['@attributes']['Unit'];
					$Baggage = "$Value $Unit";
				}else{
					$Baggage = "No Baggagge";
				}
				
				$AllBaggage[$key] = array('key'=> $key,
										'Bags' => $Baggage,
										'FareBasisCode' => $FareBasis);
			}
			
		}


		if(isset($airAirSegmentList[0])){
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
					$TravelTime = 0;	
				}else{		
					$TravelTime = 0;
				}
				
				$airAirSegment[$key] = array(
									'Key'=> "$key",
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
		}

		$FlightItenary = array();
		foreach($AllFlight as $flights){

			$FlightData = isset($flights['airAirPricingInfo']['@attributes']) ?
							$flights['airAirPricingInfo'] : $flights['airAirPricingInfo'][0];
			
			
			$ValidatingCarrier = $FlightData['@attributes']['PlatingCarrier'];

		
			$CarrierName =  Airlines($ValidatingCarrier);
			$BaseFare  = (int) filter_var($flights['@attributes']['EquivalentBasePrice'], FILTER_SANITIZE_NUMBER_INT);
			$Taxes  = (int)filter_var($flights['@attributes']['Taxes'], FILTER_SANITIZE_NUMBER_INT);
			$TotalFare  = (int) filter_var($flights['@attributes']['TotalPrice'], FILTER_SANITIZE_NUMBER_INT);
			$TimeLimit =$FlightData['@attributes']['LatestTicketingTime'];
			$nonRefundable = isset($FlightData['@attributes']['Refundable']) ? $FlightData['@attributes']['Refundable'] : false;
			$AgentFare = $BaseFare + $Taxes;
			//$AgentFare = FareRulesPolicy($System, $ValidatingCarrier,$BaseFare, $Taxes);
			
			$PriceBreakDown =array();
			$AllPassenger = array();
			if(isset($flights['airAirPricingInfo']['@attributes'])){			
				array_push($AllPassenger, $flights['airAirPricingInfo']);
			}else{
				$AllPassenger  = $flights['airAirPricingInfo'];
			}
			
			
			foreach($AllPassenger as $allPassenger){
				$PaxType =	$allPassenger['airPassengerType']['@attributes']['Code'];
				if($PaxType == 'ADT'){
					$PerPaxCount = $_POST['adultCount'];
				}else if($PaxType == 'CNN'){
					$PerPaxCount = $_POST['childCount'];
				}else if($PaxType == 'INF'){
					$PerPaxCount = $_POST['infantCount'];
				}
				$paxCount = $PerPaxCount;
				$PaxtotalFare =	(int)filter_var($allPassenger['@attributes']['TotalPrice'], FILTER_SANITIZE_NUMBER_INT);
				$totalTaxAmount =	(int)filter_var($allPassenger['@attributes']['Taxes'], FILTER_SANITIZE_NUMBER_INT);
				$PaxequivalentAmount =	(int)filter_var($allPassenger['@attributes']['EquivalentBasePrice'], FILTER_SANITIZE_NUMBER_INT);

				$BaggageAllowance = $allPassenger['airFareInfoRef'];
				
				$Baggage = array();
				foreach($BaggageAllowance as $baggageAllowance){
					
					$AirFareKey  = isset($baggageAllowance['@attributes']['Key']) ? 
					$baggageAllowance['@attributes']['Key'] : $baggageAllowance['Key'];
				
					$FareBasisCode =  $AllBaggage[$AirFareKey]['FareBasisCode'];					
					$Allowance = $AllBaggage[$AirFareKey]['Bags'];
					$SinglePaxBag = array("FareBasis"=> $FareBasisCode,
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
			
			$AllFlightOption = isset($flights['airAirPricingInfo'][0]['airFlightOptionsList']['airFlightOption'])
							? $flights['airAirPricingInfo'][0]['airFlightOptionsList']['airFlightOption']
							: $flights['airAirPricingInfo']['airFlightOptionsList']['airFlightOption'];

			$SingleFlight = array();
			if(isset($AllFlightOption['@attributes'])){
				
				array_push($SingleFlight, $AllFlightOption);
			}else{
				$SingleFlight  = $AllFlightOption;
			}
			$AllLegsInfo = array();
			$s=0;
			foreach($SingleFlight as $singlesegment){
				$s++;						
				$AllSegments = array("DepDate"=> $_POST['segments'][$s-1]['Date'],
									"DepFrom"=> $singlesegment['@attributes']['Origin'],
									"ArrTo"=> $singlesegment['@attributes']['Destination']);

				$Segments = array();

				$SingleLegDes = isset($singlesegment['airOption']['airBookingInfo']) ?
									$singlesegment['airOption']['airBookingInfo'] : $singlesegment['airOption'][0]['airBookingInfo'];
				$SingleLegCount = isset($singlesegment['airOption']['airBookingInfo']) ? 
									count($singlesegment['airOption']['airBookingInfo']) :
									count($singlesegment['airOption'][0]['airBookingInfo']);

				if($SingleLegCount == 1){
					$SegmentRef = $SingleLegDes['@attributes']['SegmentRef'];
					$FareInfoRef = $SingleLegDes['@attributes']['FareInfoRef'];
					$SegmentCode = $SingleLegDes['@attributes'];
					
					$SingleSegments  = array("MarketingCarrier"=> $airAirSegment[$SegmentRef]['Carrier'],
											"MarketingCarrierName"=>  Airlines($airAirSegment[$SegmentRef]['Carrier']),
											"MarketingFlightNumber"=> $airAirSegment[$SegmentRef]['FlightNumber'],
											"OperatingCarrier"=> $airAirSegment[$SegmentRef]['Carrier'],
											"OperatingFlightNumber"=> $airAirSegment[$SegmentRef]['FlightNumber'],
											"OperatingFlightName"=>  Airlines($airAirSegment[$SegmentRef]['Carrier']),
											"DepFrom"=> $airAirSegment[$SegmentRef]['Origin'],
											"DepAirPort"=> AirportName($airAirSegment[$SegmentRef]['Origin']),
											"DepLocation"=> AirportLocation($airAirSegment[$SegmentRef]['Origin']),
											"DepTime"=> $airAirSegment[$SegmentRef]['DepartureTime'],
											"ArrTo"=> $airAirSegment[$SegmentRef]['Destination'],
											"ArrAirPort"=> AirportName($airAirSegment[$SegmentRef]['Destination']),
											"ArrLocation"=> AirportLocation($airAirSegment[$SegmentRef]['Destination']),
											"ArrTime"=> $airAirSegment[$SegmentRef]['ArrivalTime'],
											"Group"=> $airAirSegment[$SegmentRef]['Group'],
											"OperatedBy"=> Airlines($airAirSegment[$SegmentRef]['Carrier']),
											"StopCount"=> $airAirSegment[$SegmentRef]['ChangeOfPlane'],
											"Duration"=> $airAirSegment[$SegmentRef]['FlightTime'],
											"FareBasis"=> $AllBaggage[$FareInfoRef]['FareBasisCode'],
											"SegmentCode"=> $SegmentCode);
										
					array_push($Segments, $SingleSegments);
					
				}else if($SingleLegCount > 1){
					foreach($SingleLegDes as $legDesc){

					$SegmentRef = $legDesc['@attributes']['SegmentRef'];
					$FareInfoRef = $legDesc['@attributes']['FareInfoRef'];
					$SegmentCode = $legDesc['@attributes'];
					
					$SingleSegments  = array("MarketingCarrier"=> $airAirSegment[$SegmentRef]['Carrier'],
											"MarketingCarrierName"=>  Airlines($airAirSegment[$SegmentRef]['Carrier']),
											"MarketingFlightNumber"=> $airAirSegment[$SegmentRef]['FlightNumber'],
											"OperatingCarrier"=> $airAirSegment[$SegmentRef]['Carrier'],
											"OperatingFlightNumber"=> $airAirSegment[$SegmentRef]['FlightNumber'],
											"OperatingFlightName"=>  Airlines($airAirSegment[$SegmentRef]['Carrier']),
											"DepFrom"=> $airAirSegment[$SegmentRef]['Origin'],
											"DepAirPort"=> AirportName($airAirSegment[$SegmentRef]['Origin']),
											"DepLocation"=> AirportLocation($airAirSegment[$SegmentRef]['Origin']),
											"DepTime"=> $airAirSegment[$SegmentRef]['DepartureTime'],
											"ArrTo"=> $airAirSegment[$SegmentRef]['Destination'],
											"ArrAirPort"=> AirportName($airAirSegment[$SegmentRef]['Destination']),
											"ArrLocation"=> AirportLocation($airAirSegment[$SegmentRef]['Destination']),
											"ArrTime"=> $airAirSegment[$SegmentRef]['ArrivalTime'],
											"Group"=> $airAirSegment[$SegmentRef]['Group'],
											"OperatedBy"=> Airlines($airAirSegment[$SegmentRef]['Carrier']),
											"StopCount"=> $airAirSegment[$SegmentRef]['ChangeOfPlane'],
											"Duration"=> $airAirSegment[$SegmentRef]['FlightTime'],
											"FareBasis"=> $AllBaggage[$FareInfoRef]['FareBasisCode'],
											"SegmentCode"=> $SegmentCode);
										
					array_push($Segments, $SingleSegments);
				
					}
					
				}
				

				$AllSegments["Segments"]= $Segments;

				array_push($AllLegsInfo, $AllSegments);
			
			}

			$SingleFlights = array("System"=>"Galileo",
									"Carrier"=> $ValidatingCarrier,
									"CarrierName"=>  $CarrierName,
									"BaseFare"=> $BaseFare,
									"Taxes"=> $Taxes,
									"TotalFare"=> $TotalFare,
									"AgentFare"=> $AgentFare,
									"TimeLimit"=> $TimeLimit,
									"nonRefundable"=> $nonRefundable,
									"PriceBreakDown"=> $PriceBreakDown,
									"AllLegs"=> $AllLegsInfo
								);

			array_push($FlightItenary, $SingleFlights);
		}

		return $FlightItenary;
	}else{
		$FlightItenary = array();
		return $FlightItenary;
	}
	
}

function AirportName($AirPortCode){
	$AirPortData = file_get_contents('../AirMaterials/airports.json');
	$data = json_decode($AirPortData,true);
		foreach ($data as $item) {
			if ($item['code'] == $AirPortCode) {
				return $item['name'];
			}
		}
}

function AirportLocation($AirPortCode){
	$AirPortData = file_get_contents('../AirMaterials/airports.json');
	$data = json_decode($AirPortData,true);
		foreach ($data as $item) {
			if ($item['code'] == $AirPortCode) {
				return $item['cityName'].' '.$item['countryCode'];
			}
		}
}

function Airlines($AirlinesCode){

	$str = file_get_contents('../AirMaterials/airlines.json');
	$data = json_decode($str, true);
	foreach ($data as $item) {
		if ($item['code'] == $AirlinesCode) {
			return $item['name'];
		}
	}
}


function FareRulesPolicy($comissionvalue, $FareCurrency, $Ait, $BaseFare, $Taxes){

	
	$TotalPrice  = ($BaseFare * (1-((float)$comissionvalue / 100)) + $Taxes) + (($BaseFare +  $Taxes) * $Ait);

	$AgentPrice = CurrencyConversation($TotalPrice, $FareCurrency);

	return $AgentPrice;

}

function CurrencyConversation($TotalPrice, $FareCurrency){
	include "../config.php";
		
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