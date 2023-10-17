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
	$Galileo =  $controlrow['galileo'];						
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
		

		$Gallpax= array();
					
		if($adult > 0 && $child> 0 && $infants> 0){
		
				for($i = 1; $i <= $adult ; $i++){
					$adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v51_0" Code="ADT" BookingTravelerRef="1'.$i.'" />';
					array_push($Gallpax, $adultcount);
				}
				for($i = 1; $i <= $child ; $i++){
					$childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v51_0" Code="CHD" Age="06" BookingTravelerRef="2'.$i.'" />';
					array_push($Gallpax,$childcount);
				}
				for($i = 1; $i <= $infants ; $i++){
					$infantscount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v51_0" Code="INF" Age="1" BookingTravelerRef="3'.$i.'" />';
					array_push($Gallpax, $infantscount);
				
				}

				
		}else if($adult > 0 && $child > 0){

				for($i = 1; $i <= $adult ; $i++){
					$adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v51_0" Code="ADT" BookingTravelerRef="1'.$i.'" />';
					array_push($Gallpax, $adultcount);
				}
				for($i = 1; $i <= $child ; $i++){
					$childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v51_0" Code="CHD" Age="06" BookingTravelerRef="2'.$i.'" />';
					array_push($Gallpax,$childcount);
				}
		
		}else if($adult > 0 && $infants > 0){
		
				for($i = 1; $i <= $adult ; $i++){
					$adultcount ='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v51_0" Code="ADT" BookingTravelerRef="1'.$i.'" />';
					array_push($Gallpax, $adultcount);
				}
				for($i = 1; $i <= $infants ; $i++){
					$infantscount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v51_0" Code="INF" Age="1" BookingTravelerRef="3'.$i.'" />';
					array_push($Gallpax, $infantscount);
				
				}

		}else{

			for($i = 1; $i <= $adult ; $i++){
				$adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v51_0" Code="ADT" BookingTravelerRef="1'.$i.'" />';
				array_push($Gallpax, $adultcount);
			}
		}

				
		//Galileo Api
		$Passenger = implode(" ",$Gallpax);
		
		//$TARGETBRANCH = 'P7182044';
		//$CREDENTIALS = 'Universal API/uAPI5270664478-0c51bde6:2Td*m/F3M5'; cert
		
		$TARGETBRANCH = 'P4218912';
		$CREDENTIALS = 'Universal API/uAPI4444837655-83fe5101:K/s3-5Sy4c';  //Prod
		$Token  = base64_encode($CREDENTIALS);
		$message = <<<EOM
		<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
		<soapenv:Header/>
		<soapenv:Body>
			<LowFareSearchReq xmlns="http://www.travelport.com/schema/air_v51_0" TraceId="FFI-KayesFahim" TargetBranch="$TARGETBRANCH" ReturnUpsellFare="true">
					<BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v51_0" OriginApplication="uAPI" />
					<SearchAirLeg>
						<SearchOrigin>
							<CityOrAirport xmlns="http://www.travelport.com/schema/common_v51_0" Code="$From" PreferCity="true" />
						</SearchOrigin>
						<SearchDestination>
							<CityOrAirport xmlns="http://www.travelport.com/schema/common_v51_0" Code="$To" PreferCity="true" />
						</SearchDestination>
						<SearchDepTime PreferredTime="$Date" />
					</SearchAirLeg>
					<AirSearchModifiers>
						<PreferredProviders>
						<Provider xmlns="http://www.travelport.com/schema/common_v51_0" Code="ACH" />
						</PreferredProviders>
					</AirSearchModifiers>
						$Passenger
					<AirPricingModifiers>
						<AirPricingModifiers ETicketability="Required" FaresIndicator="PublicAndPrivateFares" />
					</AirPricingModifiers>
				</LowFareSearchReq>
		</soapenv:Body>
		</soapenv:Envelope>
		EOM;

		//echo $message;



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
		CURLOPT_POSTFIELDS =>  $message,
		CURLOPT_HTTPHEADER => array(
			"Authorization: Basic $Token",
			'Content-Type: application/xml'
		),
		));

		$return = curl_exec($curl);
		curl_close($curl);

		
		//$return = file_get_contents("AI.xml");
		$response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $return);
		$xml = new SimpleXMLElement($response); /// to do 
		
		if(isset($xml->xpath('//airLowFareSearchRsp')[0])){
			$body = $xml->xpath('//airLowFareSearchRsp')[0];
			
			$result = json_decode(json_encode((array)$body), TRUE); 

			$TraceId = $result['@attributes']['TraceId'];
			$airFlightDetailsList = $result['airFlightDetailsList']['airFlightDetails'];  //print_r($airFlightDetailsList);
			$airAirSegmentList =  $result['airAirSegmentList']['airAirSegment']; //print_r($airAirSegmentLis);
			$airFareInfoList = $result['airFareInfoList']['airFareInfo']; //print_r($airFareInfoList);
			$airAirPricePointList = $result['airAirPricePointList']['airAirPricePoint'];


			$Token = $xml->xpath('//airLowFareSearchRsp')[0];
			$HToken = $Token->xpath('//airHostTokenList')[0];
			$HostToken = $HToken->xpath('//common_v51_0HostToken');

			$airHostTokenList = json_decode(json_encode((array)$HostToken), TRUE);

			$flightList= array();
			$airAirSegment = array();
			$airFareInfo = array();
			$airList = array();
			$HostTokenList = array();



			foreach($airFlightDetailsList as $airFlightDetails){
					$key = $airFlightDetails['@attributes']['Key']; 
					$TravelTime = $airFlightDetails['@attributes']['TravelTime'];
					$Equipment = $airFlightDetails['@attributes']['Equipment'];
					$flightList[$key] = array('key'=> "$key",
											'TravelTime' => $TravelTime,
										'Equipment' => $Equipment);
			}
			
			foreach($airFareInfoList as $airFareInfos){
				$key = $airFareInfos['@attributes']['Key'];
				$FareBasis =  $airFareInfos['@attributes']['FareBasis'];

				if(isset($airFareInfos['airBaggageAllowance']['airNumberOfPieces'])){
					$Baggage = $airFareInfos['airBaggageAllowance']['airNumberOfPieces'];
				}else if(isset($airFareInfos['airBaggageAllowance']['airMaxWeight'])){
					$Value = $airFareInfos['airBaggageAllowance']['airMaxWeight']['@attributes']['Value'];
					$Unit = $airFareInfos['airBaggageAllowance']['airMaxWeight']['@attributes']['Unit'];
					$Baggage = "$Value $Unit";
				}else{
					$Baggage = "No Baggagge";
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
				$AvailabilitySource = ''; //$airSegment['@attributes']['AvailabilitySource'];
				$Distance = ''; // $airSegment['@attributes']['Distance'];
				$Equipment = $airSegment['@attributes']['Equipment'];
				$ParticipantLevel = ''; // $airSegment['@attributes']['ParticipantLevel'];
				$PolledAvailabilityOption = ''; // $airSegment['@attributes']['PolledAvailabilityOption'];
				$Group = $airSegment['@attributes']['Group'];
				$ChangeOfPlane = $airSegment['@attributes']['ChangeOfPlane'];
				$APISRequirementsRef = $airSegment['@attributes']['APISRequirementsRef'];
				$AvailabilityDisplayType = ''; // $airSegment['@attributes']['AvailabilityDisplayType'];
				

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
									"APISRequirements"=> $APISRequirementsRef,
									'ChangeOfPlane' => $ChangeOfPlane,
									'Group' => $Group,
									'AvailabilityDisplayType' => $AvailabilityDisplayType);
			}

			foreach($airHostTokenList as $airHostToken){
				$key = $airHostToken['@attributes']['Key']; 
				$Token = $airHostToken[0];
				$HostTokenList[$key] = array('Key'=> "$key",
										'HostToken' => $Token);
			}

			

			foreach($airAirPricePointList as $airAirPricePoint){

				$System = 'Indigo';
				$key = $airAirPricePoint['@attributes']['Key'];
				$vCarCode = "6E"; //echo $vCarCode;

				$Commisionrow = mysqli_fetch_array(mysqli_query($conn,"SELECT * FROM airlines WHERE code='$vCarCode' "),MYSQLI_ASSOC);

				$comissionvalue;
				$FareCurrency;
				$comRef;
				if(!empty($Commisionrow)){
					$CarrieerName = $Commisionrow['name'];
					$fareRate= $Commisionrow['commission'];
					$FareCurrency =	$Commisionrow[$ComissionType.'currency'] != ''? $Commisionrow[$ComissionType.'currency'] : 'BDT';
					$comissionvalue = $Commisionrow["galileo".$ComissionType];
					$additional = $Commisionrow["galileoaddamount"];
					$comRef = $Commisionrow["ref_id"];						
				}else{
					$CarrieerName = 'No Data';
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
				
				if(isset($airAirPricePoint['@attributes']['BasePrice'])){
					$BasePrice = (int) filter_var(substr($airAirPricePoint['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
				}else{
					$BasePrice = (int) filter_var(substr($airAirPricePoint['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
				}

				$Taxes = (int) filter_var(substr($airAirPricePoint['@attributes']['Taxes'],0,-2) , FILTER_SANITIZE_NUMBER_INT);
				$TotalPrice = (int) filter_var(substr($airAirPricePoint['@attributes']['TotalPrice'],0,-2), FILTER_SANITIZE_NUMBER_INT) + $additional;
				$AgentPrice = FareRulesPolicy($comissionvalue, $FareCurrency, $Ait, $BasePrice, $Taxes);
				
				$Commission = $TotalPrice - $AgentPrice;

				$diff = 0;
				$OtherCharges = 0;
				if($AgentPrice > $TotalPrice){
					$diff = $AgentPrice - $TotalPrice;
					$Pax = $adult + $child +  $infants;
					$OtherCharges = $diff / $Pax;
					$TotalPrice  = $AgentPrice;
				}

				$PriceBreakDown = array();
				
				if($adult  > 0 && $child > 0 && $infants > 0 ){	

					$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'][0];
					$airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption'];
					
					$adultPrice = $airAirPricePoint['airAirPricingInfo'][0];
					$aBasePrice = (int) filter_var(substr($adultPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$aPassengerTaxes = (int) filter_var(substr($adultPrice['@attributes']['Taxes'],0,-2), FILTER_SANITIZE_NUMBER_INT);

					$aAirFareInfoKey = $adultPrice['@attributes']['Key'];
					$aFareInfoRef = isset($adultPrice['airFareInfoRef'][0]['@attributes']['Key']) ?
								 $adultPrice['airFareInfoRef'][0]['@attributes']['Key'] : $adultPrice['airFareInfoRef']['@attributes']['Key'];

					
					$adultBreakDown = array("BaseFare"=> $aBasePrice,
											"Tax"=> $aPassengerTaxes,
											"PaxCount"=> $adult,
											"PaxType"=> 'ADT',
											"Discount"=> 0,
											"OtherCharges"=> 0,
											"ServiceFee"=> 0,
											"AirFareInfo"=> $aAirFareInfoKey,
											"FareInfoRef"=>  $aFareInfoRef);
					
					array_push($PriceBreakDown, $adultBreakDown);
						
					$childPrice = $airAirPricePoint['airAirPricingInfo'][1];
					$cBasePrice = (int) filter_var(substr($childPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$cPassengerTaxes = (int) filter_var(substr($childPrice['@attributes']['Taxes'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$cPaxType = $childPrice['airPassengerType']['@attributes']['Code'];
					$cAirFareInfoKey = $childPrice['@attributes']['Key'];
					$cFareInfoRef = isset($childPrice['airFareInfoRef'][0]['@attributes']['Key']) ? $childPrice['airFareInfoRef'][0]['@attributes']['Key'] : $childPrice['airFareInfoRef']['@attributes']['Key'];
					$childBreakDown = array("BaseFare"=> $cBasePrice,
											"Tax"=> $cPassengerTaxes,
											"PaxCount"=> $child,
											"PaxType"=> 'CHD',
											"Discount"=> 0,
											"OtherCharges"=> 0,
											"ServiceFee"=> 0,
											"AirFareInfo"=> $cAirFareInfoKey,
											"FareInfoRef"=>  $cFareInfoRef);
											
					array_push($PriceBreakDown, $childBreakDown);
						
						
					$infantsPrice = $airAirPricePoint['airAirPricingInfo'][2];
					$iBasePrice = (int) filter_var(substr($infantsPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$iPassengerTaxes = (int) filter_var(substr($infantsPrice['@attributes']['Fees'],0,-2), FILTER_SANITIZE_NUMBER_INT);

					$iAirFareInfoKey = $infantsPrice['@attributes']['Key'];
					$iFareInfoRef = isset($infantsPrice['airFareInfoRef'][0]['@attributes']['Key']) ? $infantsPrice['airFareInfoRef'][0]['@attributes']['Key'] : $infantsPrice['airFareInfoRef']['@attributes']['Key'];
					$infantBreakDown = array("BaseFare"=> $iBasePrice,
											"Tax"=> $iPassengerTaxes,
											"PaxCount"=> $infants,
											"PaxType"=> 'INF',
											"Discount"=> 0,
											"OtherCharges"=> 0,
											"ServiceFee"=> 0,
											"AirFareInfo"=> $iAirFareInfoKey,
											"FareInfoRef"=>  $iFareInfoRef);
											
					array_push($PriceBreakDown, $infantBreakDown);       
				}else if($adult  > 0 && $child > 0){
					$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'][0];
					$airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption'];
					
					$adultPrice = $airAirPricePoint['airAirPricingInfo'][0];
					$aBasePrice = (int) filter_var(substr($adultPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$aPassengerTaxes = (int) filter_var(substr($adultPrice['@attributes']['Taxes'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$aAirFareInfoKey = $adultPrice['@attributes']['Key'];
					$aFareInfoRef = isset($adultPrice['airFareInfoRef'][0]['@attributes']['Key']) ? $adultPrice['airFareInfoRef'][0]['@attributes']['Key'] : $adultPrice['airFareInfoRef']['@attributes']['Key'];
					
					$adultBreakDown = array("BaseFare"=> $aBasePrice,
											"Tax"=> $aPassengerTaxes,
											"PaxCount"=> $adult,
											"PaxType"=> 'ADT',
											"Discount"=> 0,
											"OtherCharges"=> 0,
											"ServiceFee"=> 0,
											"AirFareInfo"=> $aAirFareInfoKey,
											"FareInfoRef"=>  $aFareInfoRef);
					
					array_push($PriceBreakDown, $adultBreakDown);
						
					$childPrice = $airAirPricePoint['airAirPricingInfo'][1];
					$cBasePrice = (int) filter_var(substr($childPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$cPassengerTaxes = (int) filter_var(substr($childPrice['@attributes']['Taxes'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$cPaxType = $childPrice['airPassengerType']['@attributes']['Code'];
					$cAirFareInfoKey = $childPrice['@attributes']['Key'];
					$cFareInfoRef = isset($childPrice['airFareInfoRef'][0]['@attributes']['Key']) ? $childPrice['airFareInfoRef'][0]['@attributes']['Key'] : $childPrice['airFareInfoRef']['@attributes']['Key'];
					$childBreakDown = array("BaseFare"=> $cBasePrice,
											"Tax"=> $cPassengerTaxes,
											"PaxCount"=> $child,
											"PaxType"=> 'CHD',
											"Discount"=> 0,
											"OtherCharges"=> 0,
											"ServiceFee"=> 0,
											"AirFareInfo"=> $cAirFareInfoKey,
											"FareInfoRef"=>  $cFareInfoRef);
											
					array_push($PriceBreakDown, $childBreakDown);
				}else if($adult  > 0 && $infants > 0 ){
					$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'][0];
					$airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption'];
					
					$adultPrice = $airAirPricePoint['airAirPricingInfo'][0];
					$aBasePrice = (int) filter_var(substr($adultPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$aPassengerTaxes = (int) filter_var(substr($adultPrice['@attributes']['Taxes'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$aAirFareInfoKey = $adultPrice['@attributes']['Key'];
					$aFareInfoRef = isset($adultPrice['airFareInfoRef'][0]['@attributes']['Key']) ? $adultPrice['airFareInfoRef'][0]['@attributes']['Key'] : $adultPrice['airFareInfoRef']['@attributes']['Key'];
					$adultBreakDown = array("BaseFare"=> $aBasePrice,
											"Tax"=> $aPassengerTaxes,
											"PaxCount"=> $adult,
											"PaxType"=> 'ADT',
											"Discount"=> 0,
											"OtherCharges"=> 0,
											"ServiceFee"=> 0,
											"AirFareInfo"=> $aAirFareInfoKey,
											"FareInfoRef"=>  $aFareInfoRef, );
					
					array_push($PriceBreakDown, $adultBreakDown);
						
								
					$infantsPrice = $airAirPricePoint['airAirPricingInfo'][1];
					$iBasePrice = (int) filter_var(substr($infantsPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$iPassengerTaxes = (int) filter_var(substr($infantsPrice['@attributes']['Fees'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$iAirFareInfoKey = $infantsPrice['@attributes']['Key'];
					$iFareInfoRef = isset($infantsPrice['airFareInfoRef'][1]['@attributes']['Key']) ? $infantsPrice['airFareInfoRef'][1]['@attributes']['Key'] : $infantsPrice['airFareInfoRef']['@attributes']['Key'];
					$infantBreakDown = array("BaseFare"=> $iBasePrice,
											"Tax"=> $iPassengerTaxes,
											"PaxCount"=> $infants,
											"PaxType"=> 'INF',
											"Discount"=> 0,
											"OtherCharges"=> 0,
											"ServiceFee"=> 0,
											"AirFareInfo"=> $iAirFareInfoKey,
											"FareInfoRef"=>  $iFareInfoRef);
											
					array_push($PriceBreakDown, $infantBreakDown);
				}else if($adult  > 0){
					
					$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'];
					$airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption'];


					$adultPrice = $airAirPricePoint['airAirPricingInfo'];
					$aBasePrice = (int) filter_var(substr($adultPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$aPassengerTaxes = (int) filter_var(substr($airPricePointOptions['@attributes']['Taxes'],0,-2), FILTER_SANITIZE_NUMBER_INT);

					$aAirFareInfoKey = $adultPrice['@attributes']['Key'];
					$aFareInfoRef =  $adultPrice['airFareInfoRef'][0]['@attributes']['Key'];
					
					
					$adultBreakDown = array("BaseFare"=> $aBasePrice,
											"Tax"=> $aPassengerTaxes,
											"PaxCount"=> $adult,
											"PaxType"=> 'ADT',
											"Discount"=> 0,
											"OtherCharges"=> 0,
											"ServiceFee"=> 0,
											"AirFareInfo"=> $aAirFareInfoKey,
											"FareInfoRef"=>  $aFareInfoRef);
											
					array_push($PriceBreakDown, $adultBreakDown);
				}

								
				$LatestTicketingTime =  ''; //$airPricePointOptions['@attributes']['LatestTicketingTime'];
				
				if(isset($airPricePointOptions['airFareInfoRef'][0])){
					$airFareInfoRef =  $airPricePointOptions['airFareInfoRef'][0]['@attributes']['Key'];
				}else{
					$airFareInfoRef = $airPricePointOptions['airFareInfoRef']['@attributes']['Key'];
				}
				
				$airFareCalc =  ''; //$airPricePointOptions['airFareCalc'];

				if(isset($airPricePointOptions['airChangePenalty']['airAmount']) == TRUE){
					$airChangePenalty =  $airPricePointOptions['airChangePenalty']['airAmount'];
				}else if(isset($airPricePointOptions['airChangePenalty']['airPercentage']) == TRUE){
					$airChangePenalty =  ''; //$airPricePointOptions['airChangePenalty']['airPercentage'];
				}else{
					$airChangePenalty =  '';
				}

				if(isset($airPricePointOptions['airCancelPenalty']['airAmount']) == TRUE){
					$airCancelPenalty = ''; // $airPricePointOptions['airCancelPenalty']['airAmount'];
				}else if(isset($airPricePointOptions['airCancelPenalty']['airPercentage']) ==TRUE){
						$airCancelPenalty = ''; // $airPricePointOptions['airCancelPenalty']['airPercentage'];
				}else{
					$airCancelPenalty =  '';
				}
				
				
				$Refundable = "Nonrefundable";

				
				$From = $result['airRouteList']['airRoute']['airLeg']['@attributes']['Origin'];
				$To = $result['airRouteList']['airRoute']['airLeg']['@attributes']['Destination'];


				if(isset($airPricePoint['airOption'][0]) == TRUE){
					$op = 0;
					$sgcount = 1;
					if(isset($airPricePoint['airOption'][$op]['airBookingInfo'])){
						$sgcount = count($airPricePoint['airOption'][$op]['airBookingInfo']);
					}

					if($sgcount == 1){

						$FareInfoRef = $airPricePoint['airOption'][$op]['airBookingInfo']['@attributes']['FareInfoRef'];
						$SegmentRef = $airPricePoint['airOption'][$op]['airBookingInfo']['@attributes']['SegmentRef'];
						$HostTokenRef = $airPricePoint['airOption'][$op]['airBookingInfo']['@attributes']['HostTokenRef'];
						$HostToken = $HostTokenList[$HostTokenRef];
						
						$Bags = $airFareInfo[$FareInfoRef]['Bags'];
						$TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];
						$TravelTimeHm = floor($TravelTime / 60)."H ".($TravelTime - ((floor($TravelTime / 60)) * 60))."Min";

						$FlightTime = $airAirSegment[$SegmentRef]['FlightTime'];
						$FlightTimeHm = floor($FlightTime / 60)."H ".($FlightTime - ((floor($FlightTime / 60)) * 60))."Min";


						$ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
						$DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

						$ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
						$DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

						$dpTime = date("D d M Y", strtotime($DepartureTime));
						$arrTime = date("D d M Y", strtotime($ArrivalTime));

						
						$markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
						$markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

						$sql = mysqli_query($conn,"$Airportsql  code='$markettingCarrier' ");
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

						// Departure Country
						$sql2 = mysqli_query($conn,"$Airportsql  code='$ArrivalTo' ");
						$row2 = mysqli_fetch_array($sql2,MYSQLI_ASSOC);

						if(!empty($row2)){
							$aAirport = $row2['name'];
							$aCity = $row2['cityName'];
							$aCountry = $row2['countryCode'];
						}
						
						
						$BookingCode = $airPricePoint['airOption'][$op]['airBookingInfo']['@attributes']['BookingCode'];
						$Seat = $airPricePoint['airOption'][$op]['airBookingInfo']['@attributes']['BookingCount'];
						$CabinClass = $airPricePoint['airOption'][$op]['airBookingInfo']['@attributes']['CabinClass'];


						$segment = array(
									array("marketingcareer"=> "$markettingCarrier",
											"marketingflight"=> "$markettingFN",
											"operatingcareer"=> "$markettingCarrier",
											"operatingflight"=> "$markettingFN",
											"departure"=> "$DepartureFrom",
											"departureAirport"=> "$dAirport ",
											"departureLocation"=> "$dCity , $dCountry",                    
											"departureTime" => "$DepartureTime",
											"arrival"=> "$ArrivalTo",                   
											"arrivalTime" => "$ArrivalTime",
											"arrivalAirport"=> "$aAirport",
											"arrivalLocation"=> "$aCity , $aCountry",
											"flightduration"=> "$TravelTimeHm",
											"bookingcode"=> "$BookingCode",
											"seat"=> $Seat,
											'CabinClass' => $CabinClass,
											'FareInfoRef' => $FareInfoRef,
											'SegmentRef' => $SegmentRef,
											'HostToken' => $HostToken,
											'SegmentDetails' => $airAirSegment[$SegmentRef]

										)
									);
						$basic = array("system" =>"Indigo",
									"segment"=> "$sgcount",
									"triptype"=>$TripType,
									"career"=> $vCarCode,
									"careerName" => $CarrieerName,									
									"BasePrice" => $BasePrice ,
									"Taxes" => $Taxes,
									"price" => $AgentPrice,
									"clientPrice"=> $TotalPrice,
									"comission"=> $Commission,
									"comissiontype"=> $ComissionType,
									"comissionvalue"=> $comissionvalue,
									"farecurrency"=> $FareCurrency,
									"airlinescomref"=> $comRef,
									"pricebreakdown"=> $PriceBreakDown,
									"airChangePenalty " => $airChangePenalty ,
									"airCancelPenalty" => $airCancelPenalty,
									"airFareCalc " => $airFareCalc ,
									"FareBasisCode"=> $airFareInfo[$FareInfoRef]['FareBasisCode'],
									"airFareInfoRef" => $airFareInfoRef,
									"LatestTicketingTime" => $LatestTicketingTime,
									"departure"=> $From,                   
									"departureDate" => $dpTime,
									"departureTime" => substr($DepartureTime,11,5),
									"arrival"=> "$To",                   
									"arrivalTime" => substr($ArrivalTime,11,5),
									"arrivalDate" => "$arrTime",
									"flightduration"=> $TravelTimeHm,
									"bags" => $Bags,
									"seat" => $Seat,
									"class" => $CabinClass,
									"refundable"=> $Refundable,
									"segments" => $segment,
									"traceid" => $TraceId
								);


					}else if($sgcount == 2){
						//Leg1
						
						$FareInfoRef = $airPricePoint['airOption'][$op]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
						$SegmentRef = $airPricePoint['airOption'][$op]['airBookingInfo'][0]['@attributes']['SegmentRef'];
						$HostTokenRef = $airPricePoint['airOption'][$op]['airBookingInfo'][0]['@attributes']['HostTokenRef'];
						$HostToken = $HostTokenList[$HostTokenRef];
						
						$Bags = $airFareInfo[$FareInfoRef]['Bags'];
						$TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];
						$TravelTimeHm = floor($TravelTime / 60)."H ".($TravelTime - ((floor($TravelTime / 60)) * 60))."Min";

						$FlightTime = $airAirSegment[$SegmentRef]['FlightTime'];
						$FlightTimeHm = floor($FlightTime / 60)."H ".($FlightTime - ((floor($FlightTime / 60)) * 60))."Min";


						$ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
						$DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

						$ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
						$DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

						$fromTime = substr($DepartureTime,11, 19);
						$dpTime = date("D d M Y", strtotime(substr($DepartureTime,0, 10)." ".$fromTime));

						$toTime = substr($ArrivalTime,11, 19);
						$arrTime = date("D d M Y", strtotime(substr($ArrivalTime,0, 10)." ".$toTime));

						
						$markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
						$markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

						$sql = mysqli_query($conn,"$Airportsql  code='$markettingCarrier' ");
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

						// Departure Country
						$sql2 = mysqli_query($conn,"$Airportsql  code='$ArrivalTo' ");
						$row2 = mysqli_fetch_array($sql2,MYSQLI_ASSOC);

						if(!empty($row2)){
							$aAirport = $row2['name'];
							$aCity = $row2['cityName'];
							$aCountry = $row2['countryCode'];
						}
						
						
						$BookingCode = $airPricePoint['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCode'];
						$Seat = $airPricePoint['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCount'];
						$CabinClass = $airPricePoint['airOption'][$op]['airBookingInfo'][0]['@attributes']['CabinClass'];


						//Leg 2
						
						$FareInfoRef1 = $airPricePoint['airOption'][$op]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
						$SegmentRef1 = $airPricePoint['airOption'][$op]['airBookingInfo'][1]['@attributes']['SegmentRef'];
						$HostTokenRef1 = $airPricePoint['airOption'][$op]['airBookingInfo'][1]['@attributes']['HostTokenRef'];
						$HostToken1 = $HostTokenList[$HostTokenRef1];


						$FlightTime1 = $airAirSegment[$SegmentRef1]['FlightTime'];
						$FlightTimeHm1 = floor($FlightTime1 / 60)."H ".($FlightTime1 - ((floor($FlightTime1 / 60)) * 60))."Min";


						$ArrivalTo1 = $airAirSegment[$SegmentRef1]['Destination'];
						$DepartureFrom1 = $airAirSegment[$SegmentRef1]['Origin'];

						$ArrivalTime1 = $airAirSegment[$SegmentRef1]['ArrivalTime'];
						$DepartureTime1 = $airAirSegment[$SegmentRef1]['DepartureTime'];

						$fromTime1 = substr($DepartureTime1,11, 19);
						$dpTime1 = date("D d M Y", strtotime(substr($DepartureTime1,0, 10)." ".$fromTime1));

						$toTime1 = substr($ArrivalTime1,11, 19);
						$arrTime1 = date("D d M Y", strtotime(substr($ArrivalTime1,0, 10)." ".$toTime1));

						
						$markettingCarrier1 = $airAirSegment[$SegmentRef1]['Carrier'];
						$markettingFN1 = $airAirSegment[$SegmentRef1]['FlightNumber'];

						$sqlmk = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
						$rowmk = mysqli_fetch_array($sqlmk,MYSQLI_ASSOC);

						if(!empty($rowmk1)){
							$markettingCarrierName1 = $rowmk1['name'];		
						}

						// Departure Country
						$sqldp1 = mysqli_query($conn,"$Airportsql code='$DepartureFrom1' ");
						$rowdp1 = mysqli_fetch_array($sqldp1,MYSQLI_ASSOC);

						if(!empty($row1)){
							$dAirport1 = $rowdp1['name'];
							$dCity1 = $rowdp1['cityName'];
							$dCountry1 = $rowdp1['countryCode'];		
						}

						// Departure Country
						$sqlar2 = mysqli_query($conn,"$Airportsql  code='$ArrivalTo1' ");
						$rowar2 = mysqli_fetch_array($sqlar2,MYSQLI_ASSOC);

						if(!empty($row2)){
							$aAirport1 = $rowar2['name'];
							$aCity1 = $rowar2['cityName'];
							$aCountry1 = $rowar2['countryCode'];
						}
						
						
						$BookingCode1 = $airPricePoint['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCode'];
						$Seat1 = $airPricePoint['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCount'];
						$CabinClass1 = $airPricePoint['airOption'][$op]['airBookingInfo'][1]['@attributes']['CabinClass'];
						
						$Transits = $TravelTime - ($FlightTime + $FlightTime1);
						$TransitHm = floor($Transits / 60)."H ".($Transits - ((floor($Transits / 60)) * 60))."Min";

						$Transit = array("transit1"=> $TransitHm);

						$segment = array(
										array("marketingcareer"=> "$markettingCarrier",
												"marketingflight"=> "$markettingFN",
												"operatingcareer"=> "$markettingCarrier",
												"operatingflight"=> "$markettingFN",
												"departure"=> "$DepartureFrom",
												"departureAirport"=> "$dAirport ",
												"departureLocation"=> "$dCity , $dCountry",                    
												"departureTime" => "$DepartureTime",
												"arrival"=> "$ArrivalTo",                   
												"arrivalTime" => "$ArrivalTime",
												"arrivalAirport"=> "$aAirport",
												"arrivalLocation"=> "$aCity , $aCountry",
												"flightduration"=> "$FlightTimeHm",
												"bookingcode"=> "$BookingCode",
												"seat"=> "$Seat",
												'CabinClass' => $CabinClass,
												'FareInfoRef' => $FareInfoRef,
												'SegmentRef' => $SegmentRef,
												'HostToken' => $HostToken,
												'SegmentDetails' => $airAirSegment[$SegmentRef]

										),array("marketingcareer"=> "$markettingCarrier1",
												"marketingflight"=> "$markettingFN1",
												"operatingcareer"=> "$markettingCarrier1",
												"operatingflight"=> "$markettingFN1",
												"departure"=> "$DepartureFrom1",
												"departureAirport"=> "$dAirport1",
												"departureLocation"=> "$dCity1 , $dCountry1",                    
												"departureTime" => "$DepartureTime1",
												"arrival"=> "$ArrivalTo1",                   
												"arrivalTime" => "$ArrivalTime1",
												"arrivalAirport"=> "$aAirport1",
												"arrivalLocation"=> "$aCity1 , $aCountry1",
												"flightduration"=> "$FlightTimeHm1",
												"bookingcode"=> "$BookingCode1",
												"seat"=> "$Seat1",
												'CabinClass' => $CabinClass1,
												'FareInfoRef' => $FareInfoRef1,
												'SegmentRef' => $SegmentRef1,
												'HostToken' => $HostToken1,
												'SegmentDetails' => $airAirSegment[$SegmentRef1]

										)
									);
						$basic = array("system" =>"Indigo",
									"segment"=> "2",
									"triptype"=>$TripType,
									"career"=> $vCarCode,
									"careerName" => "$CarrieerName",
									
									"BasePrice" => $BasePrice ,
									"Taxes" => $Taxes,
									"price" => $AgentPrice,
									"clientPrice"=> $TotalPrice,
									"comission"=> $Commission,
									"comissiontype"=> $ComissionType,
									"comissionvalue"=> $comissionvalue,
									"farecurrency"=> $FareCurrency,
									"airlinescomref"=> $comRef,
									"pricebreakdown"=> $PriceBreakDown,
									"airChangePenalty " => $airChangePenalty ,
									"airCancelPenalty" => $airCancelPenalty,
									"airFareCalc " => $airFareCalc ,
									"FareBasisCode"=> $airFareInfo[$FareInfoRef]['FareBasisCode'],
									"airFareInfoRef" => $airFareInfoRef,
									"LatestTicketingTime" => $LatestTicketingTime,
									"departure"=> "$From",                   
									"departureTime" => substr($DepartureTime,11,5),
									"departureDate" => "$dpTime",
									"arrival"=> "$To",                   
									"arrivalTime" => $ArrivalTime1,
									"arrivalDate" => "$arrTime1",
									"flightduration"=> $TravelTimeHm,										
									"bags" => $Bags,
									"seat" => "$Seat",
									"class" => "$CabinClass",
									"refundable"=> $Refundable,
									"segments" => $segment,									
									"transit" => $Transit,
									"traceid" => $TraceId
								);


					}else if($sgcount == 3){

						//Leg1
						
						$FareInfoRef = $airPricePoint['airOption'][$op]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
						$SegmentRef = $airPricePoint['airOption'][$op]['airBookingInfo'][0]['@attributes']['SegmentRef'];
						$HostTokenRef = $airPricePoint['airOption'][$op]['airBookingInfo'][0]['@attributes']['HostTokenRef'];
						$HostToken = $HostTokenList[$HostTokenRef];
						
						$TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];
						$TravelTimeHm = floor($TravelTime / 60)."H ".($TravelTime - ((floor($TravelTime / 60)) * 60))."Min";

						$Bags = $airFareInfo[$FareInfoRef]['Bags'];

						$FlightTime = $airAirSegment[$SegmentRef]['FlightTime'];
						$FlightTimeHm = floor($FlightTime / 60)."H ".($FlightTime - ((floor($FlightTime / 60)) * 60))."Min";


						$ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
						$DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

						$ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
						$DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

						$fromTime = substr($DepartureTime,11, 19);
						$dpTime = date("D d M Y", strtotime(substr($DepartureTime,0, 10)." ".$fromTime));

						$toTime = substr($ArrivalTime,11, 19);
						$arrTime = date("D d M Y", strtotime(substr($ArrivalTime,0, 10)." ".$toTime));

						
						$markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
						$markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

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

						// Departure Country
						$sql2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
						$row2 = mysqli_fetch_array($sql2,MYSQLI_ASSOC);

						if(!empty($row2)){
							$aAirport = $row2['name'];
							$aCity = $row2['cityName'];
							$aCountry = $row2['countryCode'];
						}
						
						
						$BookingCode = $airPricePoint['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCode'];
						$Seat = $airPricePoint['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCount'];
						$CabinClass = $airPricePoint['airOption'][$op]['airBookingInfo'][0]['@attributes']['CabinClass'];


						//Leg 2
						
						$FareInfoRef1 = $airPricePoint['airOption'][$op]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
						$SegmentRef1 = $airPricePoint['airOption'][$op]['airBookingInfo'][1]['@attributes']['SegmentRef'];
						$HostTokenRef1 = $airPricePoint['airOption'][$op]['airBookingInfo'][1]['@attributes']['HostTokenRef'];
						$HostToken1 = $HostTokenList[$HostTokenRef1];


						$FlightTime1 = $airAirSegment[$SegmentRef1]['FlightTime'];
						$FlightTimeHm1 = floor($FlightTime1 / 60)."H ".($FlightTime1 - ((floor($FlightTime1 / 60)) * 60))."Min";


						$ArrivalTo1 = $airAirSegment[$SegmentRef1]['Destination'];
						$DepartureFrom1 = $airAirSegment[$SegmentRef1]['Origin'];

						$ArrivalTime1 = $airAirSegment[$SegmentRef1]['ArrivalTime'];
						$DepartureTime1 = $airAirSegment[$SegmentRef1]['DepartureTime'];

						$fromTime1 = substr($DepartureTime1,11, 19);
						$dpTime1 = date("D d M Y", strtotime(substr($DepartureTime1,0, 10)." ".$fromTime1));

						$toTime1 = substr($ArrivalTime1,11, 19);
						$arrTime1 = date("D d M Y", strtotime(substr($ArrivalTime1,0, 10)." ".$toTime1));

						
						$markettingCarrier1 = $airAirSegment[$SegmentRef1]['Carrier'];
						$markettingFN1 = $airAirSegment[$SegmentRef1]['FlightNumber'];

						$sqlmk = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
						$rowmk = mysqli_fetch_array($sqlmk,MYSQLI_ASSOC);

						if(!empty($rowmk1)){
							$markettingCarrierName1 = $rowmk1['name'];		
						}

						// Departure Country
						$sqldp1 = mysqli_query($conn,"$Airportsql code='$DepartureFrom1' ");
						$rowdp1 = mysqli_fetch_array($sqldp1,MYSQLI_ASSOC);

						if(!empty($row1)){
							$dAirport1 = $rowdp1['name'];
							$dCity1 = $rowdp1['cityName'];
							$dCountry1 = $rowdp1['countryCode'];		
						}

						// Departure Country
						$sqlar2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo1' ");
						$rowar2 = mysqli_fetch_array($sqlar2,MYSQLI_ASSOC);

						if(!empty($row2)){
							$aAirport1 = $rowar2['name'];
							$aCity1 = $rowar2['cityName'];
							$aCountry1 = $rowar2['countryCode'];
						}
						
						
						$BookingCode1 = $airPricePoint['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCode'];
						$Seat1 = $airPricePoint['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCount'];
						$CabinClass1 = $airPricePoint['airOption'][$op]['airBookingInfo'][1]['@attributes']['CabinClass'];


						//Leg 3
						
						$FareInfoRef2 = $airPricePoint['airOption'][$op]['airBookingInfo'][2]['@attributes']['FareInfoRef'];
						$SegmentRef2 = $airPricePoint['airOption'][$op]['airBookingInfo'][2]['@attributes']['SegmentRef'];
						$HostTokenRef2 = $airPricePoint['airOption'][$op]['airBookingInfo'][2]['@attributes']['HostTokenRef'];
						$HostToken2 = $HostTokenList[$HostTokenRef2];


						$FlightTime2 = $airAirSegment[$SegmentRef2]['FlightTime'];
						$FlightTimeHm2 = floor($FlightTime2 / 60)."H ".($FlightTime2 - ((floor($FlightTime2 / 60)) * 60))."Min";


						$ArrivalTo2 = $airAirSegment[$SegmentRef2]['Destination'];
						$DepartureFrom2 = $airAirSegment[$SegmentRef2]['Origin'];

						$ArrivalTime2 = $airAirSegment[$SegmentRef2]['ArrivalTime'];
						$DepartureTime2 = $airAirSegment[$SegmentRef2]['DepartureTime'];

						$fromTime2 = substr($DepartureTime2,11, 19);
						$dpTime2 = date("D d M Y", strtotime(substr($DepartureTime2,0, 10)." ".$fromTime2));

						$toTime2 = substr($ArrivalTime2,11, 19);
						$arrTime2 = date("D d M Y", strtotime(substr($ArrivalTime2,0, 10)." ".$toTime2));

						
						$markettingCarrier2 = $airAirSegment[$SegmentRef2]['Carrier'];
						$markettingFN2 = $airAirSegment[$SegmentRef2]['FlightNumber'];

						$sqlmk1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier2' ");
						$rowmk1 = mysqli_fetch_array($sqlmk1,MYSQLI_ASSOC);

						if(!empty($rowmk1)){
							$markettingCarrierName2 = $rowmk1['name'];		
						}

						// Departure Country
						$sqldp2 = mysqli_query($conn,"$Airportsql code='$DepartureFrom2' ");
						$rowdp2 = mysqli_fetch_array($sqldp2,MYSQLI_ASSOC);

						if(!empty($row1)){
							$dAirport2 = $rowdp2['name'];
							$dCity2 = $rowdp2['cityName'];
							$dCountry2 = $rowdp2['countryCode'];		
						}

						// Departure Country
						$sqlar2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo1' ");
						$rowar2 = mysqli_fetch_array($sqlar2,MYSQLI_ASSOC);

						if(!empty($row2)){
							$aAirport2 = $rowar2['name'];
							$aCity2 = $rowar2['cityName'];
							$aCountry2 = $rowar2['countryCode'];
						}
						
						
						$BookingCode2 = $airPricePoint['airOption'][$op]['airBookingInfo'][2]['@attributes']['BookingCode'];
						$Seat2 = $airPricePoint['airOption'][$op]['airBookingInfo'][2]['@attributes']['BookingCount'];
						$CabinClass2 = $airPricePoint['airOption'][$op]['airBookingInfo'][2]['@attributes']['CabinClass'];

						$since_start1 =(new DateTime($DepartureTime1))->diff(new DateTime($ArrivalTime));
						$since_start2 =(new DateTime($DepartureTime2))->diff(new DateTime($ArrivalTime1));

						$Transit = array("transit1"=> "$since_start1->h H $since_start1->m Min",
											"transit2"=> "$since_start2->h H $since_start2->m Min");
						

						$segment = array(
										array("marketingcareer"=> "$markettingCarrier",
												"marketingflight"=> "$markettingFN",
												"operatingcareer"=> "$markettingCarrier",
												"operatingflight"=> "$markettingFN",
												"departure"=> "$DepartureFrom",
												"departureAirport"=> "$dAirport ",
												"departureLocation"=> "$dCity , $dCountry",                    
												"departureTime" => $DepartureTime,
												"arrival"=> "$ArrivalTo",                   
												"arrivalTime" => $ArrivalTime,
												"arrivalAirport"=> "$aAirport",
												"arrivalLocation"=> "$aCity , $aCountry",
												"flightduration"=> "$FlightTimeHm",
												"bookingcode"=> "$BookingCode",
												"seat"=> "$Seat",
												'CabinClass' => $CabinClass,
												'FareInfoRef' => $FareInfoRef,
												'SegmentRef' => $SegmentRef,
												'HostToken' => $HostToken,
												'SegmentDetails' => $airAirSegment[$SegmentRef]

										),array("marketingcareer"=> "$markettingCarrier1",
												"marketingflight"=> "$markettingFN1",
												"operatingcareer"=> "$markettingCarrier1",
												"operatingflight"=> "$markettingFN1",
												"departure"=> "$DepartureFrom1",
												"departureAirport"=> "$dAirport1",
												"departureLocation"=> "$dCity1 , $dCountry",                    
												"departureTime" => $DepartureTime1,
												"arrival"=> "$ArrivalTo1",                   
												"arrivalTime" => $ArrivalTime1,
												"arrivalAirport"=> "$aAirport1",
												"arrivalLocation"=> "$aCity1 , $aCountry1",
												"flightduration"=> "$FlightTimeHm1",
												"bookingcode"=> "$BookingCode1",
												"seat"=> "$Seat1",
												'CabinClass' => $CabinClass1,
												'FareInfoRef' => $FareInfoRef1,
												'SegmentRef' => $SegmentRef1,
												'HostToken' => $HostToken1,
												'SegmentDetails' => $airAirSegment[$SegmentRef1]

									),
									array("marketingcareer"=> "$markettingCarrier2",
												"marketingflight"=> "$markettingFN2",
												"operatingcareer"=> "$markettingCarrier2",
												"operatingflight"=> "$markettingFN2",
												"departure"=> "$DepartureFrom2",
												"departureAirport"=> "$dAirport2",
												"departureLocation"=> "$dCity2 , $dCountry2",                    
												"departureTime" => $DepartureTime2,
												"arrival"=> "$ArrivalTo2",                   
												"arrivalTime" => $ArrivalTime2,
												"arrivalAirport"=> "$aAirport2",
												"arrivalLocation"=> "$aCity2 , $aCountry2",
												"flightduration"=> "$FlightTimeHm2",
												"bookingcode"=> "$BookingCode2",
												"seat"=> "$Seat2",
												'CabinClass' => $CabinClass2,
												'FareInfoRef' => $FareInfoRef2,
												'SegmentRef' => $SegmentRef2,
												'HostToken' => $HostToken2,
												'SegmentDetails' => $airAirSegment[$SegmentRef2]

										)
									);
						$basic = array("system" =>"Indigo",
									"segment"=> "3",
									"triptype"=>$TripType,
									"career"=> $vCarCode,
									"careerName" => "$CarrieerName",
									
									"BasePrice" => $BasePrice ,
									"Taxes" => $Taxes,
									"price" => $AgentPrice,
									"clientPrice"=> $TotalPrice,
									"comission"=> $Commission,
									"comissiontype"=> $ComissionType,
									"comissionvalue"=> $comissionvalue,
									"farecurrency"=> $FareCurrency,
									"airlinescomref"=> $comRef,
									"pricebreakdown"=> $PriceBreakDown,
									"airChangePenalty " => $airChangePenalty ,
									"airCancelPenalty" => $airCancelPenalty,
									"airFareCalc " => $airFareCalc ,
									"FareBasisCode"=> $airFareInfo[$FareInfoRef]['FareBasisCode'],
									"airFareInfoRef" => $airFareInfoRef,
									"LatestTicketingTime" => $LatestTicketingTime,
									"departure"=> "$From",                   
									"departureTime" => "$fromTime",
									"departureDate" => "$dpTime1",
									"arrival"=> "$To",                   
									"arrivalTime" => "$toTime2",
									"arrivalDate" => "$arrTime2",
									"flightduration"=> $TravelTimeHm,										
									"bags" => $Bags,
									"seat" => "$Seat",
									"class" => "$CabinClass",
									"refundable"=> $Refundable,
									"segments" => $segment,									
									"transit" => $Transit,
									"traceid" => $TraceId
								);


					}

					array_push($All,$basic);

						
				}else if(isset($airPricePoint['airOption']['airBookingInfo']) == TRUE){
					if(isset($airPricePoint['airOption']['airBookingInfo'][0]) == TRUE){
						$sgcount = count($airPricePoint['airOption']['airBookingInfo']);
						if($sgcount == 2){
							//Leg1
							
							$FareInfoRef = $airPricePoint['airOption']['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$SegmentRef = $airPricePoint['airOption']['airBookingInfo'][0]['@attributes']['SegmentRef'];
							$HostTokenRef = $airPricePoint['airOption']['airBookingInfo'][0]['@attributes']['HostTokenRef'];
							$HostToken = $HostTokenList[$HostTokenRef];
							
							$Bags = $airFareInfo[$FareInfoRef]['Bags'];
							$TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];
							$TravelTimeHm = floor($TravelTime / 60)."H ".($TravelTime - ((floor($TravelTime / 60)) * 60))."Min";

							$FlightTime = $airAirSegment[$SegmentRef]['FlightTime'];
							$FlightTimeHm = floor($FlightTime / 60)."H ".($FlightTime - ((floor($FlightTime / 60)) * 60))."Min";


							$ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
							$DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

							$ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
							$DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

							$fromTime = substr($DepartureTime,11, 19);
							$dpTime = date("D d M Y", strtotime(substr($DepartureTime,0, 10)." ".$fromTime));

							$toTime = substr($ArrivalTime,11, 19);
							$arrTime = date("D d M Y", strtotime(substr($ArrivalTime,0, 10)." ".$toTime));

							
							$markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
							$markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

							$sql = mysqli_query($conn,"$Airportsql  code='$markettingCarrier' ");
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

							// Departure Country
							$sql2 = mysqli_query($conn,"$Airportsql  code='$ArrivalTo' ");
							$row2 = mysqli_fetch_array($sql2,MYSQLI_ASSOC);

							if(!empty($row2)){
								$aAirport = $row2['name'];
								$aCity = $row2['cityName'];
								$aCountry = $row2['countryCode'];
							}
							
							
							$BookingCode = $airPricePoint['airOption']['airBookingInfo'][0]['@attributes']['BookingCode'];
							$Seat = $airPricePoint['airOption']['airBookingInfo'][0]['@attributes']['BookingCount'];
							$CabinClass = $airPricePoint['airOption']['airBookingInfo'][0]['@attributes']['CabinClass'];


							//Leg 2
							
							$FareInfoRef1 = $airPricePoint['airOption']['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$SegmentRef1 = $airPricePoint['airOption']['airBookingInfo'][1]['@attributes']['SegmentRef'];
							$HostTokenRef1 = $airPricePoint['airOption']['airBookingInfo'][1]['@attributes']['HostTokenRef'];
							$HostToken1 = $HostTokenList[$HostTokenRef1];


							$FlightTime1 = $airAirSegment[$SegmentRef1]['FlightTime'];
							$FlightTimeHm1 = floor($FlightTime1 / 60)."H ".($FlightTime1 - ((floor($FlightTime1 / 60)) * 60))."Min";


							$ArrivalTo1 = $airAirSegment[$SegmentRef1]['Destination'];
							$DepartureFrom1 = $airAirSegment[$SegmentRef1]['Origin'];

							$ArrivalTime1 = $airAirSegment[$SegmentRef1]['ArrivalTime'];
							$DepartureTime1 = $airAirSegment[$SegmentRef1]['DepartureTime'];

							$fromTime1 = substr($DepartureTime1,11, 19);
							$dpTime1 = date("D d M Y", strtotime(substr($DepartureTime1,0, 10)." ".$fromTime1));

							$toTime1 = substr($ArrivalTime1,11, 19);
							$arrTime1 = date("D d M Y", strtotime(substr($ArrivalTime1,0, 10)." ".$toTime1));

							
							$markettingCarrier1 = $airAirSegment[$SegmentRef1]['Carrier'];
							$markettingFN1 = $airAirSegment[$SegmentRef1]['FlightNumber'];

							$sqlmk = mysqli_query($conn,"$Airportsql  code='$markettingCarrier1' ");
							$rowmk = mysqli_fetch_array($sqlmk,MYSQLI_ASSOC);

							if(!empty($rowmk1)){
								$markettingCarrierName1 = $rowmk1['name'];		
							}

							// Departure Country
							$sqldp1 = mysqli_query($conn,"$Airportsql code='$DepartureFrom1' ");
							$rowdp1 = mysqli_fetch_array($sqldp1,MYSQLI_ASSOC);

							if(!empty($row1)){
								$dAirport1 = $rowdp1['name'];
								$dCity1 = $rowdp1['cityName'];
								$dCountry1 = $rowdp1['countryCode'];		
							}

							// Departure Country
							$sqlar2 = mysqli_query($conn,"$Airportsql  code='$ArrivalTo1' ");
							$rowar2 = mysqli_fetch_array($sqlar2,MYSQLI_ASSOC);

							if(!empty($row2)){
								$aAirport1 = $rowar2['name'];
								$aCity1 = $rowar2['cityName'];
								$aCountry1 = $rowar2['countryCode'];
							}
							
							
							$BookingCode1 = $airPricePoint['airOption']['airBookingInfo'][1]['@attributes']['BookingCode'];
							$Seat1 = $airPricePoint['airOption']['airBookingInfo'][1]['@attributes']['BookingCount'];
							$CabinClass1 = $airPricePoint['airOption']['airBookingInfo'][1]['@attributes']['CabinClass'];
							
							$Transits = $TravelTime - ($FlightTime + $FlightTime1);
							$TransitHm = floor($Transits / 60)."H ".($Transits - ((floor($Transits / 60)) * 60))."Min";

							$Transit = array("transit1"=> $TransitHm);

							$segment = array(
											array("marketingcareer"=> "$markettingCarrier",
													"marketingflight"=> "$markettingFN",
													"operatingcareer"=> "$markettingCarrier",
													"operatingflight"=> "$markettingFN",
													"departure"=> "$DepartureFrom",
													"departureAirport"=> "$dAirport ",
													"departureLocation"=> "$dCity , $dCountry",                    
													"departureTime" => "$DepartureTime",
													"arrival"=> "$ArrivalTo",                   
													"arrivalTime" => "$ArrivalTime",
													"arrivalAirport"=> "$aAirport",
													"arrivalLocation"=> "$aCity , $aCountry",
													"flightduration"=> "$FlightTimeHm",
													"bookingcode"=> "$BookingCode",
													"seat"=> "$Seat",
													'CabinClass' => $CabinClass,
													'FareInfoRef' => $FareInfoRef,
													'SegmentRef' => $SegmentRef,
													'HostToken' => $HostToken,
													'SegmentDetails' => $airAirSegment[$SegmentRef]

											),array("marketingcareer"=> "$markettingCarrier1",
													"marketingflight"=> "$markettingFN1",
													"operatingcareer"=> "$markettingCarrier1",
													"operatingflight"=> "$markettingFN1",
													"departure"=> "$DepartureFrom1",
													"departureAirport"=> "$dAirport1",
													"departureLocation"=> "$dCity1 , $dCountry1",                    
													"departureTime" => "$DepartureTime1",
													"arrival"=> "$ArrivalTo1",                   
													"arrivalTime" => "$ArrivalTime1",
													"arrivalAirport"=> "$aAirport1",
													"arrivalLocation"=> "$aCity1 , $aCountry1",
													"flightduration"=> "$FlightTimeHm1",
													"bookingcode"=> "$BookingCode1",
													"seat"=> "$Seat1",
													'CabinClass' => $CabinClass1,
													'FareInfoRef' => $FareInfoRef1,
													'SegmentRef' => $SegmentRef1,
													'HostToken' => $HostToken1,
													'SegmentDetails' => $airAirSegment[$SegmentRef1]

											)
										);
							$basic = array("system" =>"Indigo",
										"segment"=> "2",
										"triptype"=>$TripType,
										"career"=> $vCarCode,
										"careerName" => "$CarrieerName",
										
										"BasePrice" => $BasePrice ,
										"Taxes" => $Taxes,
										"price" => $AgentPrice,
										"clientPrice"=> $TotalPrice,
										"comission"=> $Commission,
										"comissiontype"=> $ComissionType,
										"comissionvalue"=> $comissionvalue,
										"farecurrency"=> $FareCurrency,
										"airlinescomref"=> $comRef,
										"pricebreakdown"=> $PriceBreakDown,
										"airChangePenalty " => $airChangePenalty ,
										"airCancelPenalty" => $airCancelPenalty,
										"airFareCalc " => $airFareCalc ,
										"FareBasisCode"=> $airFareInfo[$FareInfoRef]['FareBasisCode'],
										"airFareInfoRef" => $airFareInfoRef,
										"LatestTicketingTime" => $LatestTicketingTime,
										"departure"=> "$From",                   
										"departureTime" => substr($DepartureTime,11,5),
										"departureDate" => "$dpTime",
										"arrival"=> "$To",                   
										"arrivalTime" => $ArrivalTime1,
										"arrivalDate" => "$arrTime1",
										"flightduration"=> $TravelTimeHm,										
										"bags" => $Bags,
										"seat" => "$Seat",
										"class" => "$CabinClass",
										"refundable"=> $Refundable,
										"segments" => $segment,									
										"transit" => $Transit,
										"traceid" => $TraceId
									);


						}else if($sgcount == 3){

							//Leg1
							
							$FareInfoRef = $airPricePoint['airOption']['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$SegmentRef = $airPricePoint['airOption']['airBookingInfo'][0]['@attributes']['SegmentRef'];
							$HostTokenRef = $airPricePoint['airOption']['airBookingInfo'][0]['@attributes']['HostTokenRef'];
							$HostToken = $HostTokenList[$HostTokenRef];
							
							$TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];
							$TravelTimeHm = floor($TravelTime / 60)."H ".($TravelTime - ((floor($TravelTime / 60)) * 60))."Min";

							$Bags = $airFareInfo[$FareInfoRef]['Bags'];

							$FlightTime = $airAirSegment[$SegmentRef]['FlightTime'];
							$FlightTimeHm = floor($FlightTime / 60)."H ".($FlightTime - ((floor($FlightTime / 60)) * 60))."Min";


							$ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
							$DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

							$ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
							$DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

							$fromTime = substr($DepartureTime,11, 19);
							$dpTime = date("D d M Y", strtotime(substr($DepartureTime,0, 10)." ".$fromTime));

							$toTime = substr($ArrivalTime,11, 19);
							$arrTime = date("D d M Y", strtotime(substr($ArrivalTime,0, 10)." ".$toTime));

							
							$markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
							$markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

							$sql = mysqli_query($conn,"$Airportsql  code='$markettingCarrier' ");
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

							// Departure Country
							$sql2 = mysqli_query($conn,"$Airportsql  code='$ArrivalTo' ");
							$row2 = mysqli_fetch_array($sql2,MYSQLI_ASSOC);

							if(!empty($row2)){
								$aAirport = $row2['name'];
								$aCity = $row2['cityName'];
								$aCountry = $row2['countryCode'];
							}
							
							
							$BookingCode = $airPricePoint['airOption']['airBookingInfo'][0]['@attributes']['BookingCode'];
							$Seat = $airPricePoint['airOption']['airBookingInfo'][0]['@attributes']['BookingCount'];
							$CabinClass = $airPricePoint['airOption']['airBookingInfo'][0]['@attributes']['CabinClass'];


							//Leg 2
							
							$FareInfoRef1 = $airPricePoint['airOption']['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$SegmentRef1 = $airPricePoint['airOption']['airBookingInfo'][1]['@attributes']['SegmentRef'];
							$HostTokenRef1 = $airPricePoint['airOption']['airBookingInfo'][1]['@attributes']['HostTokenRef'];
							$HostToken1 = $HostTokenList[$HostTokenRef1];


							$FlightTime1 = $airAirSegment[$SegmentRef1]['FlightTime'];
							$FlightTimeHm1 = floor($FlightTime1 / 60)."H ".($FlightTime1 - ((floor($FlightTime1 / 60)) * 60))."Min";


							$ArrivalTo1 = $airAirSegment[$SegmentRef1]['Destination'];
							$DepartureFrom1 = $airAirSegment[$SegmentRef1]['Origin'];

							$ArrivalTime1 = $airAirSegment[$SegmentRef1]['ArrivalTime'];
							$DepartureTime1 = $airAirSegment[$SegmentRef1]['DepartureTime'];

							$fromTime1 = substr($DepartureTime1,11, 19);
							$dpTime1 = date("D d M Y", strtotime(substr($DepartureTime1,0, 10)." ".$fromTime1));

							$toTime1 = substr($ArrivalTime1,11, 19);
							$arrTime1 = date("D d M Y", strtotime(substr($ArrivalTime1,0, 10)." ".$toTime1));

							
							$markettingCarrier1 = $airAirSegment[$SegmentRef1]['Carrier'];
							$markettingFN1 = $airAirSegment[$SegmentRef1]['FlightNumber'];

							$sqlmk = mysqli_query($conn,"$Airportsql  code='$markettingCarrier1' ");
							$rowmk = mysqli_fetch_array($sqlmk,MYSQLI_ASSOC);

							if(!empty($rowmk1)){
								$markettingCarrierName1 = $rowmk1['name'];		
							}

							// Departure Country
							$sqldp1 = mysqli_query($conn,"$Airportsql code='$DepartureFrom1' ");
							$rowdp1 = mysqli_fetch_array($sqldp1,MYSQLI_ASSOC);

							if(!empty($row1)){
								$dAirport1 = $rowdp1['name'];
								$dCity1 = $rowdp1['cityName'];
								$dCountry1 = $rowdp1['countryCode'];		
							}

							// Departure Country
							$sqlar2 = mysqli_query($conn,"$Airportsql  code='$ArrivalTo1' ");
							$rowar2 = mysqli_fetch_array($sqlar2,MYSQLI_ASSOC);

							if(!empty($row2)){
								$aAirport1 = $rowar2['name'];
								$aCity1 = $rowar2['cityName'];
								$aCountry1 = $rowar2['countryCode'];
							}
							
							
							$BookingCode1 = $airPricePoint['airOption']['airBookingInfo'][1]['@attributes']['BookingCode'];
							$Seat1 = $airPricePoint['airOption']['airBookingInfo'][1]['@attributes']['BookingCount'];
							$CabinClass1 = $airPricePoint['airOption']['airBookingInfo'][1]['@attributes']['CabinClass'];


							//Leg 3
							
							$FareInfoRef2 = $airPricePoint['airOption']['airBookingInfo'][2]['@attributes']['FareInfoRef'];
							$SegmentRef2 = $airPricePoint['airOption']['airBookingInfo'][2]['@attributes']['SegmentRef'];
							$HostTokenRef2 = $airPricePoint['airOption']['airBookingInfo'][2]['@attributes']['HostTokenRef'];
							$HostToken2 = $HostTokenList[$HostTokenRef2];


							$FlightTime2 = $airAirSegment[$SegmentRef2]['FlightTime'];
							$FlightTimeHm2 = floor($FlightTime2 / 60)."H ".($FlightTime2 - ((floor($FlightTime2 / 60)) * 60))."Min";


							$ArrivalTo2 = $airAirSegment[$SegmentRef2]['Destination'];
							$DepartureFrom2 = $airAirSegment[$SegmentRef2]['Origin'];

							$ArrivalTime2 = $airAirSegment[$SegmentRef2]['ArrivalTime'];
							$DepartureTime2 = $airAirSegment[$SegmentRef2]['DepartureTime'];

							$fromTime2 = substr($DepartureTime2,11, 19);
							$dpTime2 = date("D d M Y", strtotime(substr($DepartureTime2,0, 10)." ".$fromTime2));

							$toTime2 = substr($ArrivalTime2,11, 19);
							$arrTime2 = date("D d M Y", strtotime(substr($ArrivalTime2,0, 10)." ".$toTime2));

							
							$markettingCarrier2 = $airAirSegment[$SegmentRef2]['Carrier'];
							$markettingFN2 = $airAirSegment[$SegmentRef2]['FlightNumber'];

							$sqlmk1 = mysqli_query($conn,"$Airportsql code='$markettingCarrier2' ");
							$rowmk1 = mysqli_fetch_array($sqlmk1,MYSQLI_ASSOC);

							if(!empty($rowmk1)){
								$markettingCarrierName2 = $rowmk1['name'];		
							}

							// Departure Country
							$sqldp2 = mysqli_query($conn,"$Airportsql code='$DepartureFrom2' ");
							$rowdp2 = mysqli_fetch_array($sqldp2,MYSQLI_ASSOC);

							if(!empty($row1)){
								$dAirport2 = $rowdp2['name'];
								$dCity2 = $rowdp2['cityName'];
								$dCountry2 = $rowdp2['countryCode'];		
							}

							// Departure Country
							$sqlar2 = mysqli_query($conn,"$Airportsql code='$ArrivalTo1' ");
							$rowar2 = mysqli_fetch_array($sqlar2,MYSQLI_ASSOC);

							if(!empty($row2)){
								$aAirport2 = $rowar2['name'];
								$aCity2 = $rowar2['cityName'];
								$aCountry2 = $rowar2['countryCode'];
							}
							
							
							$BookingCode2 = $airPricePoint['airOption']['airBookingInfo'][2]['@attributes']['BookingCode'];
							$Seat2 = $airPricePoint['airOption']['airBookingInfo'][2]['@attributes']['BookingCount'];
							$CabinClass2 = $airPricePoint['airOption']['airBookingInfo'][2]['@attributes']['CabinClass'];

							$since_start1 =(new DateTime($DepartureTime1))->diff(new DateTime($ArrivalTime));
							$since_start2 =(new DateTime($DepartureTime2))->diff(new DateTime($ArrivalTime1));

							$Transit = array("transit1"=> "$since_start1->h H $since_start1->m Min",
												"transit2"=> "$since_start2->h H $since_start2->m Min");
							

							$segment = array(
											array("marketingcareer"=> "$markettingCarrier",
													"marketingflight"=> "$markettingFN",
													"operatingcareer"=> "$markettingCarrier",
													"operatingflight"=> "$markettingFN",
													"departure"=> "$DepartureFrom",
													"departureAirport"=> "$dAirport ",
													"departureLocation"=> "$dCity , $dCountry",                    
													"departureTime" => $DepartureTime,
													"arrival"=> "$ArrivalTo",                   
													"arrivalTime" => $ArrivalTime,
													"arrivalAirport"=> "$aAirport",
													"arrivalLocation"=> "$aCity , $aCountry",
													"flightduration"=> "$FlightTimeHm",
													"bookingcode"=> "$BookingCode",
													"seat"=> "$Seat",
													'CabinClass' => $CabinClass,
													'FareInfoRef' => $FareInfoRef,
													'SegmentRef' => $SegmentRef,
													'HostToken' => $HostToken,
													'SegmentDetails' => $airAirSegment[$SegmentRef]

											),array("marketingcareer"=> "$markettingCarrier1",
													"marketingflight"=> "$markettingFN1",
													"operatingcareer"=> "$markettingCarrier1",
													"operatingflight"=> "$markettingFN1",
													"departure"=> "$DepartureFrom1",
													"departureAirport"=> "$dAirport1",
													"departureLocation"=> "$dCity1 , $dCountry",                    
													"departureTime" => $DepartureTime1,
													"arrival"=> "$ArrivalTo1",                   
													"arrivalTime" => $ArrivalTime1,
													"arrivalAirport"=> "$aAirport1",
													"arrivalLocation"=> "$aCity1 , $aCountry1",
													"flightduration"=> "$FlightTimeHm1",
													"bookingcode"=> "$BookingCode1",
													"seat"=> "$Seat1",
													'CabinClass' => $CabinClass1,
													'FareInfoRef' => $FareInfoRef1,
													'SegmentRef' => $SegmentRef1,
													'HostToken' => $HostToken1,
													'SegmentDetails' => $airAirSegment[$SegmentRef1]

										),
										array("marketingcareer"=> "$markettingCarrier2",
													"marketingflight"=> "$markettingFN2",
													"operatingcareer"=> "$markettingCarrier2",
													"operatingflight"=> "$markettingFN2",
													"departure"=> "$DepartureFrom2",
													"departureAirport"=> "$dAirport2",
													"departureLocation"=> "$dCity2 , $dCountry2",                    
													"departureTime" => $DepartureTime2,
													"arrival"=> "$ArrivalTo2",                   
													"arrivalTime" => $ArrivalTime2,
													"arrivalAirport"=> "$aAirport2",
													"arrivalLocation"=> "$aCity2 , $aCountry2",
													"flightduration"=> "$FlightTimeHm2",
													"bookingcode"=> "$BookingCode2",
													"seat"=> "$Seat2",
													'CabinClass' => $CabinClass2,
													'FareInfoRef' => $FareInfoRef2,
													'SegmentRef' => $SegmentRef2,
													'HostToken' => $HostToken2,
													'SegmentDetails' => $airAirSegment[$SegmentRef2]

											)
										);
							$basic = array("system" =>"Indigo",
										"segment"=> "3",
										"triptype"=>$TripType,
										"career"=> $vCarCode,
										"careerName" => "$CarrieerName",
										
										"BasePrice" => $BasePrice ,
										"Taxes" => $Taxes,
										"price" => $AgentPrice,
										"clientPrice"=> $TotalPrice,
										"comission"=> $Commission,
										"comissiontype"=> $ComissionType,
										"comissionvalue"=> $comissionvalue,
										"farecurrency"=> $FareCurrency,
										"airlinescomref"=> $comRef,
										"pricebreakdown"=> $PriceBreakDown,
										"airChangePenalty " => $airChangePenalty ,
										"airCancelPenalty" => $airCancelPenalty,
										"airFareCalc " => $airFareCalc ,
										"FareBasisCode"=> $airFareInfo[$FareInfoRef]['FareBasisCode'],
										"airFareInfoRef" => $airFareInfoRef,
										"LatestTicketingTime" => $LatestTicketingTime,
										"departure"=> "$From",                   
										"departureTime" => "$fromTime",
										"departureDate" => "$dpTime1",
										"arrival"=> "$To",                   
										"arrivalTime" => "$toTime2",
										"arrivalDate" => "$arrTime2",
										"flightduration"=> $TravelTimeHm,										
										"bags" => $Bags,
										"seat" => "$Seat",
										"class" => "$CabinClass",
										"refundable"=> $Refundable,
										"segments" => $segment,									
										"transit" => $Transit,
										"traceid" => $TraceId
									);


						}

						array_push($All,$basic);

					}else if(isset($airPricePoint['airOption']['airBookingInfo']['@attributes']['SegmentRef'])){
						
						$FareInfoRef = $airPricePoint['airOption']['airBookingInfo']['@attributes']['FareInfoRef'];
						$SegmentRef = $airPricePoint['airOption']['airBookingInfo']['@attributes']['SegmentRef'];
						$HostTokenRef = $airPricePoint['airOption']['airBookingInfo']['@attributes']['HostTokenRef'];
						$HostToken = $HostTokenList[$HostTokenRef];
							
						$Bags = $airFareInfo[$FareInfoRef]['Bags'];

						$FlightTime = $airAirSegment[$SegmentRef]['FlightTime'];
						$FlightTimeHm = floor($FlightTime / 60)."H ".($FlightTime - ((floor($FlightTime / 60)) * 60))."Min";


						$ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
						$DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

						$ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
						$DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];
						
						$dpTime = date("D d M Y", strtotime($DepartureTime));

						$arrTime = date("D d M Y", strtotime($ArrivalTime));

						
						$markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
						$markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

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

						// Departure Country
						$sql2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
						$row2 = mysqli_fetch_array($sql2,MYSQLI_ASSOC);

						if(!empty($row2)){
							$aAirport = $row2['name'];
							$aCity = $row2['cityName'];
							$aCountry = $row2['countryCode'];
						}
						
						
						$BookingCode = $airPricePoint['airOption']['airBookingInfo']['@attributes']['BookingCode'];
						$Seat = $airPricePoint['airOption']['airBookingInfo']['@attributes']['BookingCount'];
						$CabinClass = $airPricePoint['airOption']['airBookingInfo']['@attributes']['CabinClass'];


						$segment = array(
										array("marketingcareer"=> "$markettingCarrier",
												"marketingflight"=> "$markettingFN",
												"operatingcareer"=> "$markettingCarrier",
												"operatingflight"=> "$markettingFN",
												"departure"=> "$DepartureFrom",
												"departureAirport"=> "$dAirport ",
												"departureLocation"=> "$dCity , $dCountry",                    
												"departureTime" => $DepartureTime,
												"arrival"=> "$ArrivalTo",                   
												"arrivalTime" => "$ArrivalTime",
												"arrivalAirport"=> "$aAirport",
												"arrivalLocation"=> "$aCity , $aCountry",
												"flightduration"=> $FlightTimeHm,
												"bookingcode"=> "$BookingCode",
												"seat"=> "$Seat",
												'CabinClass' => $CabinClass,
												'FareInfoRef' => $FareInfoRef,
												'SegmentRef' => $SegmentRef,
												'HostToken' => $HostToken,
												'SegmentDetails' => $airAirSegment[$SegmentRef]

										)
									);
						$basic = array("system" =>"Indigo",
									"segment"=> "1",
									"triptype"=>$TripType,
									"career"=> $vCarCode,
									"careerName" => $CarrieerName,
									
									"BasePrice" => $BasePrice ,
									"Taxes" => $Taxes,
									"price" => $AgentPrice,
									"clientPrice"=> $TotalPrice,
									"comission"=> $Commission,
									"comissiontype"=> $ComissionType,
									"comissionvalue"=> $comissionvalue,
									"farecurrency"=> $FareCurrency,
									"airlinescomref"=> $comRef,
									"pricebreakdown"=> $PriceBreakDown,
									"airChangePenalty " => $airChangePenalty ,
									"airCancelPenalty" => $airCancelPenalty,
									"airFareCalc " => $airFareCalc ,
									"FareBasisCode"=> $airFareInfo[$FareInfoRef]['FareBasisCode'],
									"airFareInfoRef" => $airFareInfoRef,
									"LatestTicketingTime" => $LatestTicketingTime,
									"departure"=> $From,
									"departureTime" => substr($DepartureTime,11,5),                  
									"departureDate" => $dpTime,
									"arrival"=> "$To",                   
									"arrivalTime" => substr($ArrivalTime,11,5),
									"arrivalDate" => $dpTime,
									"flightduration"=> $FlightTimeHm,							
									"bags" => $Bags,
									"seat" => $Seat,
									"class" => $CabinClass,
									"refundable"=> $Refundable,
									"segments" => $segment,
									"traceid" => $TraceId	);

								array_push($All,$basic);
								
					}

				}
			}

		}
			
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

	$SeatReq = $adult + $child;

	$Gallpax = array();
	if($adult > 0 && $child> 0 && $infants> 0){
        for($i = 1; $i <= $adult ; $i++){
            $adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v51_0" Code="ADT" />';
            array_push($Gallpax, $adultcount);
        }
        for($i = 1; $i <= $child ; $i++){
            $childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v51_0" Code="CHD" Age="06" />';
            array_push($Gallpax,$childcount);
        }
        for($i = 1; $i <= $infants ; $i++){
            $infantscount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v51_0" Code="INF" Age="1" />';
            array_push($Gallpax, $infantscount);    
        }
		
	}else if($adult > 0 && $child > 0){
        for($i = 1; $i <= $adult ; $i++){
            $adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v51_0" Code="ADT" />';
            array_push($Gallpax, $adultcount);
        }
        for($i = 1; $i <= $child ; $i++){
            $childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v51_0" Code="CHD" Age="09" />';
            array_push($Gallpax,$childcount);
        }
	
	}else if($adult > 0 && $infants > 0){
        for($i = 1; $i <= $adult ; $i++){
            $adultcount ='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v51_0" Code="ADT" />';
            array_push($Gallpax, $adultcount);
        }
        for($i = 1; $i <= $infants ; $i++){
            $infantscount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v51_0" Code="INF" Age="1" />';
            array_push($Gallpax, $infantscount);  
        }

	}else{
		for($i = 1; $i <= $adult ; $i++){
			$adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v51_0" Code="ADT" />';
			array_push($Gallpax, $adultcount);
		}
	}
 
	
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
			<LowFareSearchReq xmlns="http://www.travelport.com/schema/air_v51_0" TraceId="FFI-KayesFahim" TargetBranch="$TARGETBRANCH" ReturnUpsellFare="true">
					<BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v51_0" OriginApplication="uAPI" />
					<SearchAirLeg>
						<SearchOrigin>
							<CityOrAirport xmlns="http://www.travelport.com/schema/common_v51_0" Code="$From" PreferCity="true" />
						</SearchOrigin>
						<SearchDestination>
							<CityOrAirport xmlns="http://www.travelport.com/schema/common_v51_0" Code="$To" PreferCity="true" />
						</SearchDestination>
						<SearchDepTime PreferredTime="$dDate" />
					</SearchAirLeg>
					<SearchAirLeg>
						<SearchOrigin>
							<CityOrAirport xmlns="http://www.travelport.com/schema/common_v51_0" Code="$To" PreferCity="true" />
						</SearchOrigin>
						<SearchDestination>
							<CityOrAirport xmlns="http://www.travelport.com/schema/common_v51_0" Code="$From" PreferCity="true" />
						</SearchDestination>
						<SearchDepTime PreferredTime="$rDate" />
					</SearchAirLeg>
					<AirSearchModifiers>
						<PreferredProviders>
						<Provider xmlns="http://www.travelport.com/schema/common_v51_0" Code="ACH" />
						</PreferredProviders>
					</AirSearchModifiers>
						$Passenger
					<AirPricingModifiers>
						<AccountCodes>
						<AccountCode xmlns="http://www.travelport.com/schema/common_v51_0" Code="-" />
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


		//$return = file_get_contents("test.xml");
		$response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $return);
		$xml = new SimpleXMLElement($response);
		if(isset($xml->xpath('//airLowFareSearchRsp')[0])){
			$body = $xml->xpath('//airLowFareSearchRsp')[0];
			
			$result = json_decode(json_encode((array)$body), TRUE);

			$TraceId = $result['@attributes']['TraceId'];
			$airFlightDetailsList = $result['airFlightDetailsList']['airFlightDetails'];  //print_r($airFlightDetailsList);
			$airAirSegmentList =  $result['airAirSegmentList']['airAirSegment']; //print_r($airAirSegmentList);
			$airFareInfoList = $result['airFareInfoList']['airFareInfo']; //print_r($airFareInfoList);
			$airAirPricePointList = $result['airAirPricePointList']['airAirPricePoint']; // print_r($airFareInfoList)

			$HToken = $body->xpath('//airHostTokenList')[0];
			$HostToken = $HToken->xpath('//common_v51_0HostToken');
			$airHostTokenList = json_decode(json_encode((array)$HostToken), TRUE);
			
			$flightList= array();
			$airAirSegment = array();
			$airFareInfo = array();
			$airList = array();
			$HostTokenList = array();

			foreach($airFlightDetailsList as $airFlightDetails){
				$key = $airFlightDetails['@attributes']['Key'];
				$TravelTime = $airFlightDetails['@attributes']['TravelTime'];
				$Equipment = $airFlightDetails['@attributes']['Equipment'];
				$flightList[$key] = array('key'=> "$key",
									'TravelTime' => $TravelTime,
									'Equipment' => $Equipment);
			}

			foreach($airFareInfoList as $airFareInfos){
				$key = $airFareInfos['@attributes']['Key'];
				$FareBasis =  $airFareInfos['@attributes']['FareBasis'];
				
				$airFareInfo[$key] = array('key'=> $key,
										'Bags' => "0KG",
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
				$AvailabilitySource = ''; //$airSegment['@attributes']['AvailabilitySource'];
				$Distance =  ''; // $airSegment['@attributes']['Distance'];
				$Equipment = $airSegment['@attributes']['Equipment'];
				$ParticipantLevel = ''; // $airSegment['@attributes']['ParticipantLevel'];
				$PolledAvailabilityOption = ''; // $airSegment['@attributes']['PolledAvailabilityOption'];
				$Group = $airSegment['@attributes']['Group'];
				$ChangeOfPlane = $airSegment['@attributes']['ChangeOfPlane'];
				$APISRequirementsRef = $airSegment['@attributes']['APISRequirementsRef'];
				$AvailabilityDisplayType = ''; // $airSegment['@attributes']['AvailabilityDisplayType'];
				

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
									"APISRequirements"=> $APISRequirementsRef,
									'ChangeOfPlane' => $ChangeOfPlane,
									'Group' => $Group,
									'AvailabilityDisplayType' => $AvailabilityDisplayType
									
								);
			}


			foreach($airHostTokenList as $airHostToken){
				$key = $airHostToken['@attributes']['Key']; 
				$Token = $airHostToken[0];
				$HostTokenList[$key] = array('Key'=> "$key",
										'HostToken' => $Token);
			}
					
	
			foreach($airAirPricePointList as $airAirPricePoint){
			
				$key = $airAirPricePoint['@attributes']['Key'];
				$TotalPrice = (int) filter_var(substr($airAirPricePoint['@attributes']['TotalPrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
				$vCarCode = '6E';

				if(isset($airAirPricePoint['@attributes']['BasePrice'])){
					$BasePrice = (int) filter_var(substr($airAirPricePoint['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
				}else{
					$BasePrice = (int) filter_var(substr($airAirPricePoint['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
				}
				$Taxes = (int) filter_var(substr($airAirPricePoint['@attributes']['Taxes'],0,-2), FILTER_SANITIZE_NUMBER_INT);


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

				$AgentPrice = FareRulesPolicy($comissionvalue, $FareCurrency, $Ait, $BasePrice, $Taxes)+ $additional;
				$TotalPrice = (int) filter_var(substr($airAirPricePoint['@attributes']['TotalPrice'],0,-2), FILTER_SANITIZE_NUMBER_INT) + $additional;
				
				$Commission = $TotalPrice - $AgentPrice;

				$diff = 0;
				$OtherCharges = 0;
				if($AgentPrice > $TotalPrice){
					$diff = $AgentPrice - $totalFare;
					$Pax = $adult + $child +  $infants;
					$OtherCharges = $diff / $Pax;
					$TotalPrice  = $AgentPrice;
				}

				//echo $AgentPrice;

				$PriceBreakDown = array();
				
				if($adult  > 0 && $child > 0 && $infants > 0 ){	
					$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'][0];
					$airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption'];
					
					$adultPrice = $airAirPricePoint['airAirPricingInfo'][0];
					$aBasePrice = isset($adultPrice['@attributes']['BasePrice']) ? (int) filter_var(substr($adultPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT) : (int) filter_var(substr($adultPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT) ;
					$aPassengerTaxes = (int) filter_var(substr($adultPrice['@attributes']['Taxes'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$aPaxType = $adultPrice['airPassengerType']['@attributes']['Code'];
					$aAirFareInfoKey = $adultPrice['@attributes']['Key'];
					$agoFareInfoRef = $adultPrice['airFareInfoRef'][0]['@attributes']['Key'];
					$abackFareInfoRef = $adultPrice['airFareInfoRef'][1]['@attributes']['Key'];
					
					$adultBreakDown = array("BaseFare"=> $aBasePrice,
							"Tax"=> $aPassengerTaxes,
							"PaxCount"=> $adult,
							"PaxType"=> $aPaxType,
							"Discount"=> 0,
							"OtherCharges"=> 0,
							"ServiceFee"=> 0,
							"AirFareInfo"=> $aAirFareInfoKey,
							"goFareInfoRef"=>  $agoFareInfoRef,
							"backFareInfoRef"=>  $abackFareInfoRef);
					
					array_push($PriceBreakDown, $adultBreakDown);
						
					$childPrice = $airAirPricePoint['airAirPricingInfo'][1];
					$cBasePrice = isset($childPrice['@attributes']['BasePrice']) ? (int) filter_var(substr($childPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT) : (int) filter_var(substr($childPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$cPassengerTaxes = (int) filter_var(substr($childPrice['@attributes']['Taxes'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$cPaxType = $childPrice['airPassengerType']['@attributes']['Code'];
					$cAirFareInfoKey = $childPrice['@attributes']['Key'];
					$cgoFareInfoRef = $childPrice['airFareInfoRef'][0]['@attributes']['Key'];
					$cbackFareInfoRef = $childPrice['airFareInfoRef'][1]['@attributes']['Key'];
					$childBreakDown = array("BaseFare"=> "$cBasePrice",
											"Tax"=> "$cPassengerTaxes",
											"PaxCount"=> $child,
											"PaxType"=> $cPaxType,
											"Discount"=> 0,
											"OtherCharges"=> 0,
											"ServiceFee"=> 0,
											"AirFareInfo"=> $cAirFareInfoKey,
											"goFareInfoRef"=>  $cgoFareInfoRef,
											"backFareInfoRef"=>  $cbackFareInfoRef );
											
					array_push($PriceBreakDown, $childBreakDown);
						
						
					$infantsPrice = $airAirPricePoint['airAirPricingInfo'][2];
					$iBasePrice = isset($infantsPrice['@attributes']['BasePrice']) ? (int) filter_var(substr($infantsPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT) :  (int) filter_var(substr($infantsPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$iPassengerTaxes = (int) filter_var(substr($infantsPrice['@attributes']['Fees'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$iPaxType = $infantsPrice['airPassengerType']['@attributes']['Code'];
					$iAirFareInfoKey = isset($infantsPrice['@attributes'][0]['Key']) ? $infantsPrice['@attributes'][0]['Key'] :
						$infantsPrice['@attributes']['Key'];
					$igoFareInfoRef = $childPrice['airFareInfoRef'][0]['@attributes']['Key'];
					$ibackFareInfoRef = $childPrice['airFareInfoRef'][1]['@attributes']['Key'];
					$infantBreakDown = array("BaseFare"=> $iBasePrice,
											"Tax"=> $iPassengerTaxes,
											"PaxCount"=> $infants,
											"PaxType"=> $iPaxType,
											"Discount"=> 0,
											"OtherCharges"=> 0,
											"ServiceFee"=> 0,
											"AirFareInfo"=> $iAirFareInfoKey,
											"goFareInfoRef"=>  $igoFareInfoRef,
											"backFareInfoRef"=>  $ibackFareInfoRef );
											
					array_push($PriceBreakDown, $infantBreakDown);       
				}else if($adult  > 0 && $child > 0){
					$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'][0];
					$airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption'];
					
					$adultPrice = $airAirPricePoint['airAirPricingInfo'][0];
					$aBasePrice = isset($adultPrice['@attributes']['BasePrice']) ? (int) filter_var(substr($adultPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT) : (int) filter_var(substr($adultPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT) ;
					$aPassengerTaxes = (int) filter_var(substr($adultPrice['@attributes']['Taxes'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$aPaxType = $adultPrice['airPassengerType']['@attributes']['Code'];
					$aAirFareInfoKey = $airAirPricePoint['airAirPricingInfo'][0]['@attributes']['Key'];
					$agoFareInfoRef = $airAirPricePoint['airAirPricingInfo'][0]['airFareInfoRef'][0]['@attributes']['Key'];
					$abackFareInfoRef = $airAirPricePoint['airAirPricingInfo'][0]['airFareInfoRef'][1]['@attributes']['Key'];
					
					$adultBreakDown = array("BaseFare"=> $aBasePrice,
											"Tax"=> $aPassengerTaxes,
											"PaxCount"=> $adult,
											"PaxType"=> $aPaxType,
											"Discount"=> 0,
											"OtherCharges"=> 0,
											"ServiceFee"=> 0,
											"AirFareInfo"=> $aAirFareInfoKey,
											"goFareInfoRef"=>  $agoFareInfoRef,
											"backFareInfoRef"=>  $abackFareInfoRef );
					
					array_push($PriceBreakDown, $adultBreakDown);
						
					$childPrice = $airAirPricePoint['airAirPricingInfo'][1];
					$cBasePrice = isset($childPrice['@attributes']['BasePrice']) ? (int) filter_var(substr($childPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT) : (int) filter_var(substr($childPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$cPassengerTaxes = (int) filter_var(substr($childPrice['@attributes']['Taxes'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$cPaxType = $childPrice['airPassengerType']['@attributes']['Code'];
					$cAirFareInfoKey = $airAirPricePoint['airAirPricingInfo'][1]['@attributes']['Key'];
					$cgoFareInfoRef = $airAirPricePoint['airAirPricingInfo'][1]['airFareInfoRef'][0]['@attributes']['Key'];
					$cbackFareInfoRef = $airAirPricePoint['airAirPricingInfo'][1]['airFareInfoRef'][1]['@attributes']['Key'];
					$childBreakDown = array("BaseFare"=> $cBasePrice,
											"Tax"=> $cPassengerTaxes,
											"PaxCount"=> $child,
											"PaxType"=> $cPaxType,
											"Discount"=> 0,
											"OtherCharges"=> 0,
											"ServiceFee"=> 0,
											"AirFareInfo"=> $cAirFareInfoKey,
											"goFareInfoRef"=>  $cgoFareInfoRef,
											"backFareInfoRef"=>  $cbackFareInfoRef );
											
					array_push($PriceBreakDown, $childBreakDown);
				}else if($adult  > 0 && $infants > 0 ){
					$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'][0];
					$airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption'];
					
					$adultPrice = $airAirPricePoint['airAirPricingInfo'][0];
					$aBasePrice = isset($adultPrice['@attributes']['BasePrice']) ? (int) filter_var(substr($adultPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT) : (int) filter_var(substr($adultPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT) ;
					$aPassengerTaxes = (int) filter_var(substr($adultPrice['@attributes']['Taxes'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$aPaxType = $adultPrice['airPassengerType']['@attributes']['Code'];
					$aAirFareInfoKey = $adultPrice['@attributes']['Key'];
					$agoFareInfoRef = $adultPrice['airFareInfoRef'][0]['@attributes']['Key'];
					$abackFareInfoRef = $adultPrice['airFareInfoRef'][1]['@attributes']['Key'];
					$adultBreakDown = array("BaseFare"=> $aBasePrice,
											"Tax"=> $aPassengerTaxes,
											"PaxCount"=> $adult,
											"PaxType"=> $aPaxType,
											"Discount"=> 0,
											"OtherCharges"=> 0,
											"ServiceFee"=> 0,
											"AirFareInfo"=> $aAirFareInfoKey,
											"goFareInfoRef"=>  $agoFareInfoRef,
											"backFareInfoRef"=>  $abackFareInfoRef );
					
					array_push($PriceBreakDown, $adultBreakDown);
						
								
					$infantsPrice = $airAirPricePoint['airAirPricingInfo'][1];
					$iBasePrice = isset($infantsPrice['@attributes']['BasePrice']) ? (int) filter_var(substr($infantsPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT) :  (int) filter_var(substr($infantsPrice['@attributes']['BasePrice'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$iPassengerTaxes = (int) filter_var(substr($infantsPrice['@attributes']['Fees'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$iPaxType = $infantsPrice['airPassengerType']['@attributes']['Code'];
					$iAirFareInfoKey = $infantsPrice['@attributes']['Key'];
					$igoFareInfoRef = $infantsPrice['airFareInfoRef'][0]['@attributes']['Key'];
					$ibackFareInfoRef = $infantsPrice['airFareInfoRef'][1]['@attributes']['Key'];
					$infantBreakDown = array("BaseFare"=> $iBasePrice,
											"Tax"=> $iPassengerTaxes,
											"PaxCount"=> $infants,
											"PaxType"=> $iPaxType,
											"Discount"=> 0,
											"OtherCharges"=> 0,
											"ServiceFee"=> 0,
											"AirFareInfo"=> $iAirFareInfoKey,
											"goFareInfoRef"=>  $igoFareInfoRef,
											"backFareInfoRef"=>  $ibackFareInfoRef );
											
					array_push($PriceBreakDown, $infantBreakDown);
				}else if($adult  > 0){
					
					$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'];
					$airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption'];


					$adultPrice = $airAirPricePoint['airAirPricingInfo'];
					$aBasePrice = $adultPrice['@attributes']['BasePrice']; 
					$aPassengerTaxes = (int) filter_var(substr($airPricePointOptions['@attributes']['Taxes'],0,-2), FILTER_SANITIZE_NUMBER_INT);
					$aPaxType = $airPricePointOptions['airPassengerType']['@attributes']['Code'];
					$aAirFareInfoKey = $airPricePointOptions['@attributes']['Key'];
					$agoFareInfoRef =  $airPricePointOptions['airFareInfoRef'][0]['@attributes']['Key'];
					$abackFareInfoRef =  $airPricePointOptions['airFareInfoRef'][1]['@attributes']['Key'];
					
					$adultBreakDown = array("BaseFare"=> $aBasePrice,
											"Tax"=> $aPassengerTaxes,
											"PaxCount"=> $adult,
											"PaxType"=> $aPaxType,
											"Discount"=> 0,
											"OtherCharges"=> 0,
											"ServiceFee"=> 0,
											"AirFareInfo"=> $aAirFareInfoKey,
											"goFareInfoRef"=>  $agoFareInfoRef,
											"backFareInfoRef"=>  $abackFareInfoRef);
											
					array_push($PriceBreakDown, $adultBreakDown);
				}

				
				if(isset($airPricePointOptions['@attributes']['Refundable'])){
					$Refundable = "Refundable";
				}else{
					$Refundable = "Nonrefundable";

				}


				$sgcount1 = 0;
				$sgcount2 = 0;

				if(isset($airPricePoint[0]['airOption']['airBookingInfo'][0])){
					$sgcount1 = count($airPricePoint[0]['airOption']['airBookingInfo']); 			
				}else if(isset($airPricePoint[0]['airOption']['airBookingInfo'])){
					$sgcount1 = 1; 			
				}
				
				if(isset($airPricePoint[1]['airOption']['airBookingInfo'][0])){							
					$sgcount2 = count($airPricePoint[1]['airOption']['airBookingInfo']);	
				}else if(isset($airPricePoint[1]['airOption']['airBookingInfo'])){
					$sgcount2 = 1;
				}

				//print_r($airPricePoint);				
									
				if($sgcount1 == 1 && $sgcount2 == 1){
				
					$goFareInfoRef = $airPricePoint[0]['airOption']['airBookingInfo']['@attributes']['FareInfoRef'];
					$goSegmentRef = $airPricePoint[0]['airOption']['airBookingInfo']['@attributes']['SegmentRef'];
					$goHostTokenRef = $airPricePoint[0]['airOption']['airBookingInfo']['@attributes']['HostTokenRef'];
					$goHostToken = $HostTokenList[$goHostTokenRef];
					
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

					$gosqlmk = mysqli_query($conn,"$Airportsql code='$gomarkettingCarrier' ");
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
					$goasql = mysqli_query($conn,"$Airportsql code='$goArrivalTo' ");
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
					$backHostTokenRef = $airPricePoint[1]['airOption']['airBookingInfo']['@attributes']['HostTokenRef'];
					$backHostToken = $HostTokenList[$backHostTokenRef];


					
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
					$backasql = mysqli_query($conn,"$Airportsql code='$backArrivalTo' ");
					$backarow = mysqli_fetch_array($backasql,MYSQLI_ASSOC);

					if(!empty($backarow)){
						$backaAirport = $backarow['name'];
						$backaCity = $backarow['cityName'];
						$backaCountry = $backarow['countryCode'];
					}
					
					
					$backBookingCode = $airPricePoint[1]['airOption']['airBookingInfo']['@attributes']['BookingCode'];
					$backSeat = $airPricePoint[1]['airOption']['airBookingInfo']['@attributes']['BookingCount'];
					$backCabinClass = $airPricePoint[1]['airOption']['airBookingInfo']['@attributes']['CabinClass'];

												

					$backTransit = array("transit1"=> '');

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
													"flightduration"=> "$goFlightTimeHm",
													"bookingcode"=> "$goBookingCode",
													"farebasis"=> $airFareInfo[$goFareInfoRef]["FareBasisCode"],
													"seat"=> "$goSeat",
													"bags" => "$goBags",
													"class" => "$goCabinClass",
													'HostToken' => $goHostToken,
													"segmentDetails"=> $airAirSegment[$goSegmentRef])),
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
												"flightduration"=> "$backFlightTimeHm",
												"bookingcode"=> "$backBookingCode",
												"farebasis"=> $airFareInfo[$backFareInfoRef]["FareBasisCode"],
												"seat"=> "$backSeat",
												"bags" => "$backBags",
												"class" => "$backCabinClass",
												"HostToken" => $backHostToken,
												"segmentDetails"=> $airAirSegment[$backSegmentRef])																							

										)
									);

					$basic = array("system" => "Indigo",
							"segment"=> "1",
							"triptype"=>$TripType,
							"career"=> "$vCarCode",
							"careerName" => "$CarrieerName",								
							"basePrice" => $BasePrice ,
							"taxes" => $Taxes,
							"price" => $AgentPrice,
							"clientPrice"=> $TotalPrice,
							"comission"=> $Commission,
							"comissiontype"=> $ComissionType,
							"comissionvalue"=> $comissionvalue,
							"farecurrency"=> $FareCurrency,
							"airlinescomref"=> $comRef,
							"pricebreakdown"=> $PriceBreakDown,
							"godeparture"=> "$From",                   
							"goDepartureTime" => $goDepartureTime,
							"godepartureDate" => $godpTime,
							"goarrival"=> "$To", 
							"goarrivalTime" => "$goArrivalTime1",
							"goarrivalDate" => "$goarrTime1",                
							"backdeparture"=> "$To",                   
							"backDepartureTime" => $backDepartureTime,
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


				}else if($sgcount1 == 2 && $sgcount2 == 2){
						
				//Go Leg1
				
				$goFareInfoRef = $airPricePoint[0]['airOption']['airBookingInfo'][0]['@attributes']['FareInfoRef'];
				$goSegmentRef = $airPricePoint[0]['airOption']['airBookingInfo'][0]['@attributes']['SegmentRef'];
				$goHostTokenRef = $airPricePoint[0]['airOption']['airBookingInfo'][0]['@attributes']['HostTokenRef'];
				$goHostToken = $HostTokenList[$goHostTokenRef];
				
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
				$goasql = mysqli_query($conn,"$Airportsql code='$goArrivalTo' ");
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
				$goHostTokenRef1 = $airPricePoint[0]['airOption']['airBookingInfo'][1]['@attributes']['HostTokenRef'];
				$goHostToken1 = $HostTokenList[$goHostTokenRef1];
				
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

				$gosql1 = mysqli_query($conn,"$Airportsql code='$gomarkettingCarrier1' ");
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
				$goasql1 = mysqli_query($conn,"$Airportsql code='$goArrivalTo1' ");
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
				$backHostTokenRef = $airPricePoint[1]['airOption']['airBookingInfo'][0]['@attributes']['HostTokenRef'];
				$backHostToken = $HostTokenList[$backHostTokenRef];


				
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

				$backsql = mysqli_query($conn,"$Airportsql code='$backmarkettingCarrier' ");
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
				$backasql = mysqli_query($conn,"$Airportsql code='$backArrivalTo' ");
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
				$backHostTokenRef1 = $airPricePoint[1]['airOption']['airBookingInfo'][1]['@attributes']['HostTokenRef'];
				$backHostToken1 = $HostTokenList[$backHostTokenRef1];

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

				$backsqlmk = mysqli_query($conn,"$Airportsql code='$backmarkettingCarrier1' ");
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
				$backasql1 = mysqli_query($conn,"$Airportsql code='$backArrivalTo1' ");
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
												"flightduration"=> "$goFlightTimeHm",
												"bookingcode"=> "$goBookingCode",
												"farebasis"=> $airFareInfo[$goFareInfoRef]["FareBasisCode"],
												"seat"=> "$goSeat",
												"bags" => "$goBags",
												"class" => "$goCabinClass",
												"HostToken" => $goHostToken,
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
												"flightduration"=> "$goFlightTimeHm1",
												"bookingcode"=> "$goBookingCode1",
												"farebasis"=> $airFareInfo[$goFareInfoRef1]["FareBasisCode"],
												"seat"=> "$goSeat1",
												"bags" => "$goBags1",
												"class" => "$goCabinClass1",
												"HostToken" => $goHostToken1,
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
													"flightduration"=> "$backFlightTimeHm",
													"bookingcode"=> "$backBookingCode",
													"farebasis"=> $airFareInfo[$backFareInfoRef]["FareBasisCode"],													
													"seat"=> "$backSeat",
													"bags" => "$backBags",
													"class" => "$backCabinClass",
													"HostToken" => $backHostToken,
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
													"flightduration"=> "$backFlightTimeHm1",
													"bookingcode"=> "$backBookingCode1",
													"farebasis"=> $airFareInfo[$backFareInfoRef1]["FareBasisCode"],
													"seat"=> "$backSeat1",
													"class" => "$backCabinClass1",
													"bags" => "$backBags1",
													"HostToken" => $backHostToken1,
													"segmentDetails"=> $airAirSegment[$backSegmentRef1])																									

									)
								);

						$basic = array("system" => "Indigo",
								"segment"=> "2",
								"triptype"=>$TripType,
								"career"=> "$vCarCode",
								"careerName" => "$CarrieerName",								
								"basePrice" => $BasePrice ,
								"taxes" => $Taxes,
								"price" => $AgentPrice,
								"clientPrice"=> $TotalPrice,
								"comission"=> $Commission,
								"comissiontype"=> $ComissionType,
								"comissionvalue"=> $comissionvalue,
								"farecurrency"=> $FareCurrency,
								"airlinescomref"=> $comRef,
								"pricebreakdown"=> $PriceBreakDown,
								"godeparture"=> "$From",                   
								"goDepartureTime" => $goDepartureTime,
								"godepartureDate" => $godpTime,
								"goarrival"=> "$To", 
								"goarrivalTime" => "$goArrivalTime1",
								"goarrivalDate" => "$goarrTime1",                
								"backdeparture"=> "$To",                   
								"backDepartureTime" => $backDepartureTime,
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
	
		}

    }		


}

echo json_encode($All);

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