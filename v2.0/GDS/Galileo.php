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
					$adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" BookingTravelerRef="1'.$i.'" />';
					array_push($Gallpax, $adultcount);
				}
				for($i = 1; $i <= $child ; $i++){
					$childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="CNN" Age="07" BookingTravelerRef="2'.$i.'" />';
					array_push($Gallpax,$childcount);
				}
				for($i = 1; $i <= $infants ; $i++){
					$infantscount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="INF" Age="1" DOB="2021-06-04" BookingTravelerRef="3'.$i.'" />';
					array_push($Gallpax, $infantscount);
				
				}

				
		}else if($adult > 0 && $child > 0){

				for($i = 1; $i <= $adult ; $i++){
					$adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" BookingTravelerRef="1'.$i.'" />';
					array_push($Gallpax, $adultcount);
				}
				for($i = 1; $i <= $child ; $i++){
					$childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="CNN" Age="07" BookingTravelerRef="2'.$i.'" />';
					array_push($Gallpax,$childcount);
				}
		
		}else if($adult > 0 && $infants > 0){
		
				for($i = 1; $i <= $adult ; $i++){
					$adultcount ='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" BookingTravelerRef="1'.$i.'" />';
					array_push($Gallpax, $adultcount);
				}
				for($i = 1; $i <= $infants ; $i++){
					$infantscount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="INF" Age="1" BookingTravelerRef="3'.$i.'" />';
					array_push($Gallpax, $infantscount);
				
				}

		}else{

			for($i = 1; $i <= $adult ; $i++){
				$adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" BookingTravelerRef="1'.$i.'" />';
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
			<LowFareSearchReq xmlns="http://www.travelport.com/schema/air_v42_0" TraceId="FFI-KayesFahim" TargetBranch="$TARGETBRANCH" ReturnUpsellFare="true">
					<BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v42_0" OriginApplication="uAPI" />
					<SearchAirLeg>
						<SearchOrigin>
							<CityOrAirport xmlns="http://www.travelport.com/schema/common_v42_0" Code="$From" PreferCity="true" />
						</SearchOrigin>
						<SearchDestination>
							<CityOrAirport xmlns="http://www.travelport.com/schema/common_v42_0" Code="$To" PreferCity="true" />
						</SearchDestination>
						<SearchDepTime PreferredTime="$Date" />
					</SearchAirLeg>
					<AirSearchModifiers>
						<PreferredProviders>
						<Provider xmlns="http://www.travelport.com/schema/common_v42_0" Code="1G" />
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

		
		//$return = file_get_contents("oneway.xml");
		if(isset($return)){
			$response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $return);
			$xml = new SimpleXMLElement($response); /// to do 
			
			if(isset($xml->xpath('//airLowFareSearchRsp')[0])){
				$body = $xml->xpath('//airLowFareSearchRsp')[0];
				
				$result = json_decode(json_encode((array)$body), TRUE); 

				$TraceId = $result['@attributes']['TraceId'];
				$airFlightDetailsList = $result['airFlightDetailsList']['airFlightDetails'];  //print_r($airFlightDetailsList);
				$airAirSegmentList =  $result['airAirSegmentList']['airAirSegment']; //print_r($airFlightDetailsList);
				$airFareInfoList = $result['airFareInfoList']['airFareInfo']; //print_r($airFareInfoList);
				$airAirPricePointList = $result['airAirPricePointList']['airAirPricePoint'];

				//print_r($airAirPricePointList);

				$flightList= array();
				$airAirSegment = array();
				$airFareInfo = array();
				$airList = array();

				if(isset($airFlightDetailsList[0])){

					foreach($airFlightDetailsList as $airFlightDetails){
						$key = $airFlightDetails['@attributes']['Key']; 
						$TravelTime = $airFlightDetails['@attributes']['TravelTime'];
						$Equipment = $airFlightDetails['@attributes']['Equipment'];
						$flightList[$key] = array('key'=> "$key",
												'TravelTime' => $TravelTime,
											'Equipment' => $Equipment);
					}
				}else{
					$key = $airFlightDetailsList['@attributes']['Key']; 
					$TravelTime = $airFlightDetailsList['@attributes']['TravelTime'];
					$Equipment = $airFlightDetailsList['@attributes']['Equipment'];
					$flightList[$key] = array('key'=> "$key",
											'TravelTime' => $TravelTime,
										'Equipment' => $Equipment);		
				}


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
						
						$airFareInfo[$key] = array('key'=> $key,
												'Bags' => $Baggage,
												'FareBasisCode' => $FareBasis);
					}
					
				}else{
						$key = $airFareInfoList['@attributes']['Key'];
						$FareBasis =  $airFareInfoList['@attributes']['FareBasis'];

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
											'AvailabilityDisplayType' => $AvailabilityDisplayType);
					}
				}else{
					$key = $airAirSegmentList['@attributes']['Key'];		
						$Carrier = $airAirSegmentList['@attributes']['Carrier'];
						$Origin = $airAirSegmentList['@attributes']['Origin'];
						$Destination = $airAirSegmentList['@attributes']['Destination'];
						$DepartureTime = $airAirSegmentList['@attributes']['DepartureTime'];
						$ArrivalTime = $airAirSegmentList['@attributes']['ArrivalTime'];
						$FlightNumber = $airAirSegmentList['@attributes']['FlightNumber'];
						$FlightTime = $airAirSegmentList['@attributes']['FlightTime'];
						$AvailabilitySource = $airAirSegmentList['@attributes']['AvailabilitySource'];
						$Distance = $airAirSegmentList['@attributes']['Distance'];
						$Equipment = $airAirSegmentList['@attributes']['Equipment'];
						$ParticipantLevel = $airAirSegmentList['@attributes']['ParticipantLevel'];
						$PolledAvailabilityOption = $airAirSegmentList['@attributes']['PolledAvailabilityOption'];
						$Group = $airAirSegmentList['@attributes']['Group'];
						$ChangeOfPlane = $airAirSegmentList['@attributes']['ChangeOfPlane'];
						$AvailabilityDisplayType = $airAirSegmentList['@attributes']['AvailabilityDisplayType'];
						

						if(isset($airAirSegmentList['airFlightDetailsRef']['@attributes']['Key'])){
							$airFlightDetailsRef = $airAirSegmentList['airFlightDetailsRef']['@attributes']['Key'];
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
				
				foreach($airAirPricePointList as $airAirPricePoint){

					$System = 'Galileo';

					$key = $airAirPricePoint['@attributes']['Key'];
					
					if(isset($airAirPricePoint['airAirPricingInfo']['@attributes']['PlatingCarrier'])){
						$vCarCode = trim($airAirPricePoint['airAirPricingInfo']['@attributes']['PlatingCarrier']);
					}else{
						$vCarCode = trim($airAirPricePoint['airAirPricingInfo'][0]['@attributes']['PlatingCarrier']);
					}
					

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

					
					if(isset($airAirPricePoint['@attributes']['EquivalentBasePrice'])){
						$BasePrice = (int) filter_var($airAirPricePoint['@attributes']['EquivalentBasePrice'], FILTER_SANITIZE_NUMBER_INT);
					}else{
						$BasePrice = (int) filter_var($airAirPricePoint['@attributes']['BasePrice'], FILTER_SANITIZE_NUMBER_INT);
					}

					$Taxes = (int) filter_var($airAirPricePoint['@attributes']['Taxes'] , FILTER_SANITIZE_NUMBER_INT);


					$AgentPrice = FareRulesPolicy($comissionvalue, $FareCurrency, $Ait, $BasePrice, $Taxes)+ $additional;
					$TotalPrice = (int) filter_var($airAirPricePoint['@attributes']['TotalPrice'], FILTER_SANITIZE_NUMBER_INT);
					
					$Commission = $TotalPrice - $AgentPrice;

					

					$PriceBreakDown = array();
					
					if($adult  > 0 && $child > 0 && $infants > 0 ){	
						$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'][0];
						$airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption'];
						
						$adultPrice = $airAirPricePoint['airAirPricingInfo'][0];
						$aEquivalentBasePrice = isset($airPricePointOptions['@attributes']['EquivalentBasePrice']) ? $airPricePointOptions['@attributes']['EquivalentBasePrice'] : $airPricePointOptions['@attributes']['BasePrice'];
						$aPassengerTaxes = $adultPrice['@attributes']['Taxes'];
						$aPaxType = isset($adultPrice['airPassengerType'][0]['@attributes']['Code']) ?
									$adultPrice['airPassengerType'][0]['@attributes']['Code'] :
									$adultPrice['airPassengerType']['@attributes']['Code'];
						$aAirFareInfoKey = $adultPrice['@attributes']['Key'];
						$aFareInfoRef = isset($adultPrice['airFareInfoRef']['@attributes']['Key']) ?
							$adultPrice['airFareInfoRef']['@attributes']['Key'] : $adultPrice['airFareInfoRef'][0]['@attributes']['Key'];
						$adultBreakDown = array("BaseFare"=> "$aEquivalentBasePrice",
												"Tax"=> "$aPassengerTaxes",
												"PaxCount"=> $adult,
												"PaxType"=> "$aPaxType",
												"Discount"=> "0",
												"OtherCharges"=> "$OtherCharges",
												"ServiceFee"=> "0",
												"AirFareInfo"=> $aAirFareInfoKey,
												"FareInfoRef"=>  $aFareInfoRef);
						
						array_push($PriceBreakDown, $adultBreakDown);
							
						$childPrice = $airAirPricePoint['airAirPricingInfo'][2];
						$cEquivalentBasePrice = isset($childPrice['@attributes']['EquivalentBasePrice'])?$childPrice['@attributes']['EquivalentBasePrice']:$childPrice['@attributes']['BasePrice'];
						$cPassengerTaxes = $childPrice['@attributes']['Taxes'];
						$cPaxType = isset($childPrice['airPassengerType'][0]['@attributes']['Code']) ?
									$childPrice['airPassengerType'][0]['@attributes']['Code'] :
									$childPrice['airPassengerType']['@attributes']['Code'];
						$cAirFareInfoKey = $airAirPricePoint['airAirPricingInfo'][2]['@attributes']['Key'];
						$cFareInfoRef = isset($childPrice['airFareInfoRef']['@attributes']['Key']) ?
							$childPrice['airFareInfoRef']['@attributes']['Key'] : $childPrice['airFareInfoRef'][0]['@attributes']['Key'];
						
						$childBreakDown = array("BaseFare"=> "$cEquivalentBasePrice",
												"Tax"=> "$cPassengerTaxes",
												"PaxCount"=> $child,
												"PaxType"=> "$cPaxType",
												"Discount"=> "0",
												"OtherCharges"=> "$OtherCharges",
												"ServiceFee"=> "0",
												"AirFareInfo"=> $cAirFareInfoKey,
												"FareInfoRef"=>  $cFareInfoRef);
												
						array_push($PriceBreakDown, $childBreakDown);
							
							
						$infantsPrice = $airAirPricePoint['airAirPricingInfo'][1];
						$iEquivalentBasePrice = isset($infantsPrice['@attributes']['EquivalentBasePrice'])?$infantsPrice['@attributes']['EquivalentBasePrice']:$infantsPrice['@attributes']['BasePrice'];
						$iPassengerTaxes = $infantsPrice['@attributes']['Taxes'];
						$iPaxType = isset($infantsPrice['airPassengerType'][0]['@attributes']['Code']) ?
									$infantsPrice['airPassengerType'][0]['@attributes']['Code'] :
									$infantsPrice['airPassengerType']['@attributes']['Code'];
						$iAirFareInfoKey = $airAirPricePoint['airAirPricingInfo'][1]['@attributes']['Key'];
						$iFareInfoRef = isset($infantsPrice['airFareInfoRef']['@attributes']['Key']) ?
							$infantsPrice['airFareInfoRef']['@attributes']['Key'] : $infantsPrice['airFareInfoRef'][0]['@attributes']['Key'];
						
						$infantBreakDown = array("BaseFare"=> "$iEquivalentBasePrice",
												"Tax"=> "$iPassengerTaxes",
												"PaxCount"=> $infants,
												"PaxType"=> "$iPaxType",
												"Discount"=> "0",
												"OtherCharges"=> "$OtherCharges",
												"ServiceFee"=> "0",
												"AirFareInfo"=> $iAirFareInfoKey,
												"FareInfoRef"=>  $iFareInfoRef);
												
						array_push($PriceBreakDown, $infantBreakDown);
						
						
					}else if($adult  > 0 && $child > 0){
						$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'][0];
						$airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption'];
						
						$adultPrice = $airAirPricePoint['airAirPricingInfo'][0];
						$aEquivalentBasePrice = isset($airPricePointOptions['@attributes']['EquivalentBasePrice']) ? $airPricePointOptions['@attributes']['EquivalentBasePrice'] : $airPricePointOptions['@attributes']['BasePrice'];
						$aPassengerTaxes = $adultPrice['@attributes']['Taxes'];
						$aPaxType = isset($adultPrice['airPassengerType'][0]['@attributes']['Code']) ?
									$adultPrice['airPassengerType'][0]['@attributes']['Code'] :
									$adultPrice['airPassengerType']['@attributes']['Code'];
						$aAirFareInfoKey = $airAirPricePoint['airAirPricingInfo'][0]['@attributes']['Key'];
						$aFareInfoRef = $airAirPricePoint['airAirPricingInfo'][0]['airFareInfoRef']['@attributes']['Key'];
						$adultBreakDown = array("BaseFare"=> "$aEquivalentBasePrice",
												"Tax"=> "$aPassengerTaxes",
												"PaxCount"=> $adult,
												"PaxType"=> "$aPaxType",
												"Discount"=> "0",
												"OtherCharges"=> "$OtherCharges",
												"ServiceFee"=> "0",
												"AirFareInfo"=> $aAirFareInfoKey,
												"FareInfoRef"=>  $aFareInfoRef);
						
						array_push($PriceBreakDown, $adultBreakDown);
							
						$childPrice = $airAirPricePoint['airAirPricingInfo'][1];
						$cEquivalentBasePrice = isset($childPrice['@attributes']['EquivalentBasePrice'])?$childPrice['@attributes']['EquivalentBasePrice']:$childPrice['@attributes']['BasePrice'];
						$cPassengerTaxes = $childPrice['@attributes']['Taxes'];
						$cPaxType = isset($childPrice['airPassengerType'][0]['@attributes']['Code']) ?
									$childPrice['airPassengerType'][0]['@attributes']['Code'] :
									$childPrice['airPassengerType']['@attributes']['Code'];
						$cAirFareInfoKey = $airAirPricePoint['airAirPricingInfo'][1]['@attributes']['Key'];
						$cFareInfoRef = $airAirPricePoint['airAirPricingInfo'][1]['airFareInfoRef']['@attributes']['Key'];
						
						$childBreakDown = array("BaseFare"=> "$cEquivalentBasePrice",
												"Tax"=> "$cPassengerTaxes",
												"PaxCount"=> $child,
												"PaxType"=> "$cPaxType",
												"Discount"=> "0",
												"OtherCharges"=> "$OtherCharges",
												"ServiceFee"=> "0",
												"AirFareInfo"=> $cAirFareInfoKey,
												"FareInfoRef"=>  $cFareInfoRef);
												
						array_push($PriceBreakDown, $childBreakDown);
					}else if($adult  > 0 && $infants > 0 ){
						$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'][0];
						$airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption'];
						
						$adultPrice = $airAirPricePoint['airAirPricingInfo'][0];
						$aEquivalentBasePrice = isset($airPricePointOptions['@attributes']['EquivalentBasePrice']) ? $airPricePointOptions['@attributes']['EquivalentBasePrice'] : $airPricePointOptions['@attributes']['BasePrice'];
						$aPassengerTaxes = $adultPrice['@attributes']['Taxes'];
						$aPaxType = isset($adultPrice['airPassengerType'][0]['@attributes']['Code']) ?
									$adultPrice['airPassengerType'][0]['@attributes']['Code'] :
									$adultPrice['airPassengerType']['@attributes']['Code'];
						$aAirFareInfoKey = $airAirPricePoint['airAirPricingInfo'][0]['@attributes']['Key'];
						$aFareInfoRef = isset($adultPrice['airFareInfoRef']['@attributes']['Key']) ?
							$adultPrice['airFareInfoRef']['@attributes']['Key'] : $adultPrice['airFareInfoRef'][0]['@attributes']['Key'];
						$adultBreakDown = array("BaseFare"=> "$aEquivalentBasePrice",
												"Tax"=> "$aPassengerTaxes",
												"PaxCount"=> $adult,
												"PaxType"=> "$aPaxType",
												"Discount"=> "0",
												"OtherCharges"=> "$OtherCharges",
												"ServiceFee"=> "0",
												"AirFareInfo"=> $aAirFareInfoKey,
												"FareInfoRef"=>  $aFareInfoRef);
						
						array_push($PriceBreakDown, $adultBreakDown);
							
									
						$infantsPrice = $airAirPricePoint['airAirPricingInfo'][1];
						$iEquivalentBasePrice = isset($infantsPrice['@attributes']['EquivalentBasePrice'])?$infantsPrice['@attributes']['EquivalentBasePrice']:$infantsPrice['@attributes']['BasePrice'];
						$iPassengerTaxes = $infantsPrice['@attributes']['Taxes'];
						$iPaxType = isset($infantsPrice['airPassengerType'][0]['@attributes']['Code']) ?
									$infantsPrice['airPassengerType'][0]['@attributes']['Code'] :
									$infantsPrice['airPassengerType']['@attributes']['Code'];
						$iAirFareInfoKey = $airAirPricePoint['airAirPricingInfo'][1]['@attributes']['Key'];
						$iFareInfoRef = isset($infantsPrice['airFareInfoRef']['@attributes']['Key']) ?
										$infantsPrice['airFareInfoRef']['@attributes']['Key'] :
										$infantsPrice['airFareInfoRef'][0]['@attributes']['Key'];
						$infantBreakDown = array("BaseFare"=> "$iEquivalentBasePrice",
												"Tax"=> "$iPassengerTaxes",
												"PaxCount"=> $infants,
												"PaxType"=> "$iPaxType",
												"Discount"=> "0",
												"OtherCharges"=> "$OtherCharges",
												"ServiceFee"=> "0",
												"AirFareInfo"=> $iAirFareInfoKey,
												"FareInfoRef"=>  $iFareInfoRef);
						array_push($PriceBreakDown, $infantBreakDown);
					}else if($adult  > 0){
						
						$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'];
						$airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption'];
						
						$adultPrice = $airAirPricePoint['airAirPricingInfo'];
						$aEquivalentBasePrice = isset($airPricePointOptions['@attributes']['EquivalentBasePrice']) ? $airPricePointOptions['@attributes']['EquivalentBasePrice'] : $airPricePointOptions['@attributes']['BasePrice'];
						$aPassengerTaxes = $airPricePointOptions['@attributes']['Taxes'];
						$aPaxType = isset($adultPrice['airPassengerType'][0]['@attributes']['Code']) ?
									$adultPrice['airPassengerType'][0]['@attributes']['Code'] :
									$adultPrice['airPassengerType']['@attributes']['Code'];
						$aAirFareInfoKey = $airPricePointOptions['@attributes']['Key'];
						$aFareInfoRef = isset($airPricePointOptions['airFareInfoRef']['@attributes']['Key']) ?
							$airPricePointOptions['airFareInfoRef']['@attributes']['Key'] : $airPricePointOptions['airFareInfoRef'][0]['@attributes']['Key'];
						
						$adultBreakDown = array("BaseFare"=> "$aEquivalentBasePrice",
											"Tax"=> "$aPassengerTaxes",
											"PaxCount"=> $adult,
											"PaxType"=> "$aPaxType",
											"Discount"=> "0",
											"OtherCharges"=> "$OtherCharges",
											"ServiceFee"=> "0",
											"AirFareInfo"=> $aAirFareInfoKey,
											"FareInfoRef"=>  $aFareInfoRef);
												
						array_push($PriceBreakDown, $adultBreakDown);
					}

									
					$LatestTicketingTime =  $airPricePointOptions['@attributes']['LatestTicketingTime'];
					
					if(isset($airPricePointOptions['airFareInfoRef'][0])){
						$airFareInfoRef =  $airPricePointOptions['airFareInfoRef'][0];
					}else{
						$airFareInfoRef =  $airPricePointOptions['airFareInfoRef']['@attributes']['Key'];
					}
					
					$airFareCalc =  $airPricePointOptions['airFareCalc'];

					if(isset($airPricePointOptions['airChangePenalty']['airAmount']) == TRUE){
						$airChangePenalty =  $airPricePointOptions['airChangePenalty']['airAmount'];
					}else if(isset($airPricePointOptions['airChangePenalty']['airPercentage']) == TRUE){
						$airChangePenalty =  $airPricePointOptions['airChangePenalty']['airPercentage'];
					}

					if(isset($airPricePointOptions['airCancelPenalty']['airAmount']) == TRUE){
						$airCancelPenalty =  $airPricePointOptions['airCancelPenalty']['airAmount'];
					}else if(isset($airPricePointOptions['airCancelPenalty']['airPercentage']) ==TRUE){
							$airCancelPenalty =  $airPricePointOptions['airCancelPenalty']['airPercentage'];
					}
					
								
					if(isset($airPricePointOptions['@attributes']['Refundable'])){
						$Refundable = "Refundable";
					}else{
						$Refundable = "Nonrefundable";

					}
					


					if(isset($airPricePoint['airOption'][0]) == TRUE){
						$sgcount = 1;
						if(isset($airPricePoint['airOption'][0]['airBookingInfo'])){
							$sgcount = count($airPricePoint['airOption'][0]['airBookingInfo']);
						}

						if($sgcount == 1){

							$FareInfoRef = $airPricePoint['airOption'][0]['airBookingInfo']['@attributes']['FareInfoRef'];
							$SegmentRef = $airPricePoint['airOption'][0]['airBookingInfo']['@attributes']['SegmentRef'];
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
							$carriersql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier' ");
							$carrierrow = mysqli_fetch_array($carriersql,MYSQLI_ASSOC);

							if(!empty($carrierrow)){
								$markettingCarrierName = $carrierrow['name'];		
							}

							// TODO: DEPARTURE COUNTRY DETAILS
							$sql1 = mysqli_query($conn,"$Airportsql code='$DepartureFrom' ");
							$row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);
							if(!empty($row1)){
								$dAirport = $row1['name'];
								$dCity = $row1['cityName'];
								$dCountry = $row1['countryCode'];		
							}

							// TODO ARRIVAL COUNTRY DETAILS
							$sql2 = mysqli_query($conn,"$Airportsql  code='$ArrivalTo' ");
							$row2 = mysqli_fetch_array($sql2,MYSQLI_ASSOC);
							if(!empty($row2)){
								$aAirport = $row2['name'];
								$aCity = $row2['cityName'];
								$aCountry = $row2['countryCode'];
							}
							
							$BookingCode = $airPricePoint['airOption'][0]['airBookingInfo']['@attributes']['BookingCode'];
							$Seat = $airPricePoint['airOption'][0]['airBookingInfo']['@attributes']['BookingCount'];
							$CabinClass = $airPricePoint['airOption'][0]['airBookingInfo']['@attributes']['CabinClass'];


							$segment = array(
											array("marketingcareer"=> "$markettingCarrier",
													"marketingcareerName"=> $markettingCarrierName,
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
													'SegmentDetails' => $airAirSegment[$SegmentRef]

											)
										);
							$basic = array("system" =>"Galileo",
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
							
							$FareInfoRef = $airPricePoint['airOption'][0]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$SegmentRef = $airPricePoint['airOption'][0]['airBookingInfo'][0]['@attributes']['SegmentRef'];
							
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
							$sql2 = mysqli_query($conn,"$Airportsql  code='$ArrivalTo' ");
							$row2 = mysqli_fetch_array($sql2,MYSQLI_ASSOC);

							if(!empty($row2)){
								$aAirport = $row2['name'];
								$aCity = $row2['cityName'];
								$aCountry = $row2['countryCode'];
							}
							
							
							$BookingCode = $airPricePoint['airOption'][0]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$Seat = $airPricePoint['airOption'][0]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$CabinClass = $airPricePoint['airOption'][0]['airBookingInfo'][0]['@attributes']['CabinClass'];


							//Leg 2
							
							$FareInfoRef1 = $airPricePoint['airOption'][0]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$SegmentRef1 = $airPricePoint['airOption'][0]['airBookingInfo'][1]['@attributes']['SegmentRef'];


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

							$carriersql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
							$carrierrow1 = mysqli_fetch_array($carriersql1,MYSQLI_ASSOC);

							if(!empty($carrierrow1)){
								$markettingCarrierName1 = $carrierrow1['name'];		
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
							
							
							$BookingCode1 = $airPricePoint['airOption'][0]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$Seat1 = $airPricePoint['airOption'][0]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$CabinClass1 = $airPricePoint['airOption'][0]['airBookingInfo'][1]['@attributes']['CabinClass'];
							
							$Transits = $TravelTime - ($FlightTime + $FlightTime1);
							$TransitHm = floor($Transits / 60)."H ".($Transits - ((floor($Transits / 60)) * 60))."Min";

							$Transit = array("transit1"=> $TransitHm);

							$segment = array(
											array("marketingcareer"=> "$markettingCarrier",
													"marketingcareerName"=> $markettingCarrierName,
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
													'SegmentDetails' => $airAirSegment[$SegmentRef]

											),array("marketingcareer"=> "$markettingCarrier1",
											"marketingcareerName"=> $markettingCarrierName1,
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
													'SegmentDetails' => $airAirSegment[$SegmentRef1]

											)
										);
							$basic = array("system" =>"Galileo",
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
							
							//TODO:Leg1 segment 1
							$FareInfoRef = $airPricePoint['airOption'][0]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$SegmentRef = $airPricePoint['airOption'][0]['airBookingInfo'][0]['@attributes']['SegmentRef'];
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
							$carriersql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier' ");
							$carrierrow = mysqli_fetch_array($carriersql,MYSQLI_ASSOC);
							if(!empty($carrierrow)){
								$markettingCarrierName = $carrierrow['name'];		
							}
							// TODO:DEPARTURE COUNTRY INFORMATION
							$sql1 = mysqli_query($conn,"$Airportsql code='$DepartureFrom' ");
							$row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);
							if(!empty($row1)){
								$dAirport = $row1['name'];
								$dCity = $row1['cityName'];
								$dCountry = $row1['countryCode'];		
							}
							// TODO: ARRIVAL COUNTRY INFORMATION
							$sql2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
							$row2 = mysqli_fetch_array($sql2,MYSQLI_ASSOC);
							if(!empty($row2)){
								$aAirport = $row2['name'];
								$aCity = $row2['cityName'];
								$aCountry = $row2['countryCode'];
							}
							$BookingCode = $airPricePoint['airOption'][0]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$Seat = $airPricePoint['airOption'][0]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$CabinClass = $airPricePoint['airOption'][0]['airBookingInfo'][0]['@attributes']['CabinClass'];

							//TODO: LEG2 segment 2
						
							$FareInfoRef1 = $airPricePoint['airOption'][0]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$SegmentRef1 = $airPricePoint['airOption'][0]['airBookingInfo'][1]['@attributes']['SegmentRef'];
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
							$carriersql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
							$carrierrow1 = mysqli_fetch_array($carriersql1,MYSQLI_ASSOC);

							if(!empty($carrierrow1)){
								$markettingCarrierName1 = $carrierrow1['name'];		
							}

							// TODO: DEPARTURE COUNTRY DETAILS
							$sqldp1 = mysqli_query($conn,"$Airportsql code='$DepartureFrom1' ");
							$rowdp1 = mysqli_fetch_array($sqldp1,MYSQLI_ASSOC);

							if(!empty($row1)){
								$dAirport1 = $rowdp1['name'];
								$dCity1 = $rowdp1['cityName'];
								$dCountry1 = $rowdp1['countryCode'];		
							}

							// TODO: ARRIVAL COUNTRY DETAILS
							$sqlar2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo1' ");
							$rowar2 = mysqli_fetch_array($sqlar2,MYSQLI_ASSOC);

							if(!empty($row2)){
								$aAirport1 = $rowar2['name'];
								$aCity1 = $rowar2['cityName'];
								$aCountry1 = $rowar2['countryCode'];
							}
							
							$BookingCode1 = $airPricePoint['airOption'][0]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$Seat1 = $airPricePoint['airOption'][0]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$CabinClass1 = $airPricePoint['airOption'][0]['airBookingInfo'][1]['@attributes']['CabinClass'];


							//TODO: LEG3 SEGMENT 3
							
							$FareInfoRef2 = $airPricePoint['airOption'][0]['airBookingInfo'][2]['@attributes']['FareInfoRef'];
							$SegmentRef2 = $airPricePoint['airOption'][0]['airBookingInfo'][2]['@attributes']['SegmentRef'];
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
							
							$carriersql2 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier2' ");
							$carrierrow2 = mysqli_fetch_array($carriersql2,MYSQLI_ASSOC);

							if(!empty($carrierrow2)){
								$markettingCarrierName2 = $carrierrow2['name'];		
							}

							//todo: Departure Country Details
							$sqldp2 = mysqli_query($conn,"$Airportsql code='$DepartureFrom2' ");
							$rowdp2 = mysqli_fetch_array($sqldp2,MYSQLI_ASSOC);

							if(!empty($rowdp2 )){
								$dAirport2 = $rowdp2['name'];
								$dCity2 = $rowdp2['cityName'];
								$dCountry2 = $rowdp2['countryCode'];		
							}

							// TODO: ARRIVAL COUNTRY DETAILS
							$sqlar2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo2' ");
							$rowar2 = mysqli_fetch_array($sqlar2,MYSQLI_ASSOC);

							if(!empty($rowar2)){
								$aAirport2 = $rowar2['name'];
								$aCity2 = $rowar2['cityName'];
								$aCountry2 = $rowar2['countryCode'];
							}
							
							
							$BookingCode2 = $airPricePoint['airOption'][0]['airBookingInfo'][2]['@attributes']['BookingCode'];
							$Seat2 = $airPricePoint['airOption'][0]['airBookingInfo'][2]['@attributes']['BookingCount'];
							$CabinClass2 = $airPricePoint['airOption'][0]['airBookingInfo'][2]['@attributes']['CabinClass'];
							$since_start1 =(new DateTime($DepartureTime1))->diff(new DateTime($ArrivalTime));
							$since_start2 =(new DateTime($DepartureTime2))->diff(new DateTime($ArrivalTime1));

							$Transit = array("transit1"=> "$since_start1->h H $since_start1->m Min",
												"transit2"=> "$since_start2->h H $since_start2->m Min");
							

							$segment = array(
											array("marketingcareer"=> "$markettingCarrier",
											"marketingcareerName"=> $markettingCarrierName,
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
													'SegmentDetails' => $airAirSegment[$SegmentRef]

											),array("marketingcareer"=> "$markettingCarrier1",
													"marketingcareerName"=> $markettingCarrierName1,
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
													'SegmentDetails' => $airAirSegment[$SegmentRef1]

										),
										array("marketingcareer"=> "$markettingCarrier2",
													"marketingcareerName"=> $markettingCarrierName2,
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
													'SegmentDetails' => $airAirSegment[$SegmentRef2]

											)
										);
							$basic = array("system" =>"Galileo",
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

								$carriersql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE  code='$markettingCarrier1' ");
								$carrierrow1 = mysqli_fetch_array($carriersql1,MYSQLI_ASSOC);

								if(!empty($carrierrow1)){
									$markettingCarrierName1 = $carrierrow1['name'];		
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
								$sqlar2 = mysqli_query($conn,"$Airportsql code='$ArrivalTo1' ");
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
												"marketingcareerName"=> $markettingCarrierName,
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
														'SegmentDetails' => $airAirSegment[$SegmentRef]

												),array("marketingcareer"=> "$markettingCarrier1",
														"marketingcareerName"=> $markettingCarrierName1,
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
														'SegmentDetails' => $airAirSegment[$SegmentRef1]

												)
											);
								$basic = array("system" =>"Galileo",
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

								//TODO:LEG1 SEGMENT1
								$FareInfoRef = $airPricePoint['airOption']['airBookingInfo'][0]['@attributes']['FareInfoRef'];
								$SegmentRef = $airPricePoint['airOption']['airBookingInfo'][0]['@attributes']['SegmentRef'];
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
								$carriersql = mysqli_query($conn,"SELECT name FROM airlines WHERE  code='$markettingCarrier' ");
								$carrierrow = mysqli_fetch_array($carriersql,MYSQLI_ASSOC);

								if(!empty($carrierrow)){
									$markettingCarrierName = $carrierrow['name'];		
								}

								// TODO: DEPARTURE COUNTRY DETAILS
								$sql1 = mysqli_query($conn,"$Airportsql code='$DepartureFrom' ");
								$row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);

								if(!empty($row1)){
									$dAirport = $row1['name'];
									$dCity = $row1['cityName'];
									$dCountry = $row1['countryCode'];		
								}

								// TODO: ARRIVAL COUNTRY DETAILS
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
								
								//TODO: LEG2 SEGMENT2
								$FareInfoRef1 = $airPricePoint['airOption']['airBookingInfo'][1]['@attributes']['FareInfoRef'];
								$SegmentRef1 = $airPricePoint['airOption']['airBookingInfo'][1]['@attributes']['SegmentRef'];
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
								$carriersql1 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
								$carrierrow1 = mysqli_fetch_array($carriersql1,MYSQLI_ASSOC);

								if(!empty($carrierrow1)){
									$markettingCarrierName1 = $carrierrow1['name'];		
								}

								// TODO:DEPARTURE COUNTRY DETAILS
								$sqldp1 = mysqli_query($conn,"$Airportsql code='$DepartureFrom1' ");
								$rowdp1 = mysqli_fetch_array($sqldp1,MYSQLI_ASSOC);

								if(!empty($row1)){
									$dAirport1 = $rowdp1['name'];
									$dCity1 = $rowdp1['cityName'];
									$dCountry1 = $rowdp1['countryCode'];		
								}

								// TODO: Arrival Country DETAILS
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

								//TODO:Leg3 Segment3
								
								$FareInfoRef2 = $airPricePoint['airOption']['airBookingInfo'][2]['@attributes']['FareInfoRef'];
								$SegmentRef2 = $airPricePoint['airOption']['airBookingInfo'][2]['@attributes']['SegmentRef'];
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
								$carriersql2 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier2' ");
								$carrierrow2 = mysqli_fetch_array($carriersql2,MYSQLI_ASSOC);

								if(!empty($carrierrow2)){
									$markettingCarrierName2 = $carrierrow2['name'];		
								}

								// Todo: Departure Country Details
								$sqldp2 = mysqli_query($conn,"$Airportsql code='$DepartureFrom2' ");
								$rowdp2 = mysqli_fetch_array($sqldp2,MYSQLI_ASSOC);

								if(!empty($rowdp2)){
									$dAirport2 = $rowdp2['name'];
									$dCity2 = $rowdp2['cityName'];
									$dCountry2 = $rowdp2['countryCode'];		
								}

								// Todo: Arrival Country Details
								$sqlar2 = mysqli_query($conn,"$Airportsql code='$ArrivalTo2' ");
								$rowar2 = mysqli_fetch_array($sqlar2,MYSQLI_ASSOC);

								if(!empty($rowar2)){
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
												"marketingcareerName"=> $markettingCarrierName,
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
														'SegmentDetails' => $airAirSegment[$SegmentRef]

												),array("marketingcareer"=> "$markettingCarrier1",
												"marketingcareerName"=> $markettingCarrierName1,
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
														'SegmentDetails' => $airAirSegment[$SegmentRef1]

											),
											array("marketingcareer"=> "$markettingCarrier2",
														"marketingcareerName"=> $markettingCarrierName2,
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
														'SegmentDetails' => $airAirSegment[$SegmentRef2]

												)
											);
								$basic = array("system" =>"Galileo",
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

							$carriersql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$markettingCarrier' ");
							$carrierrow = mysqli_fetch_array($carriersql,MYSQLI_ASSOC);

							if(!empty($carrierrow)){
								$markettingCarrierName = $carrierrow['name'];		
							}

							// TODO: DEPARTURE COUNTRY DETAILS
							$sql1 = mysqli_query($conn,"$Airportsql code='$DepartureFrom' ");
							$row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);

							if(!empty($row1)){
								$dAirport = $row1['name'];
								$dCity = $row1['cityName'];
								$dCountry = $row1['countryCode'];		
							}

							// TODO: ARRIVAL COUNTRY DETAILS
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
											"marketingcareerName"=> $markettingCarrierName,
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
													'SegmentDetails' => $airAirSegment[$SegmentRef]

											)
										);
							$basic = array("system" =>"Galileo",
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
        for($i = 1; $i <= $adult ; $i++){
            $adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" />';
            array_push($Gallpax, $adultcount);
        }
        for($i = 1; $i <= $child ; $i++){
            $childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="CNN" Age="09" />';
            array_push($Gallpax,$childcount);
        }
	
	}else if($adult > 0 && $infants > 0){
        for($i = 1; $i <= $adult ; $i++){
            $adultcount ='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" />';
            array_push($Gallpax, $adultcount);
        }
        for($i = 1; $i <= $infants ; $i++){
            $infantscount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="INF" Age="1" />';
            array_push($Gallpax, $infantscount);  
        }

	}else{
		for($i = 1; $i <= $adult ; $i++){
			$adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" />';
			array_push($Gallpax, $adultcount);
		}
	}
 
	

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


		//$return = file_get_contents("res.xml");
		if(isset($return)){
			$response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $return);
			$xml = new SimpleXMLElement($response);
			if(isset($xml->xpath('//airLowFareSearchRsp')[0])){
				$body = $xml->xpath('//airLowFareSearchRsp')[0];
				
				$result = json_decode(json_encode((array)$body), TRUE); 

				$TraceId = $result['@attributes']['TraceId'];
				$airFlightDetailsList = $result['airFlightDetailsList']['airFlightDetails'];  //print_r($airFlightDetailsList);
				$airAirSegmentList =  $result['airAirSegmentList']['airAirSegment']; //print_r($airFlightDetailsList);
				$airFareInfoList = $result['airFareInfoList']['airFareInfo']; //print_r($airFareInfoList);
				$airAirPricePointList = $result['airAirPricePointList']['airAirPricePoint']; // print_r($airFareInfoList);

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
		
				foreach($airAirPricePointList as $airAirPricePoint){
					//print_r($airAirPricePoint);
				
					$key = $airAirPricePoint['@attributes']['Key'];
					$TotalPrice = (int) filter_var($airAirPricePoint['@attributes']['TotalPrice'], FILTER_SANITIZE_NUMBER_INT);
					if(isset($airAirPricePoint['airAirPricingInfo'][0])){
						$vCarCode = $airAirPricePoint['airAirPricingInfo'][0]['@attributes']['PlatingCarrier'];
					}else{
						$vCarCode = $airAirPricePoint['airAirPricingInfo']['@attributes']['PlatingCarrier'];
					}

					if(isset($airAirPricePoint['@attributes']['EquivalentBasePrice'])){
						$BasePrice = (int) filter_var($airAirPricePoint['@attributes']['EquivalentBasePrice'], FILTER_SANITIZE_NUMBER_INT);
					}else{
						$BasePrice = (int) filter_var($airAirPricePoint['@attributes']['BasePrice'], FILTER_SANITIZE_NUMBER_INT);
					}
					$Taxes = (int) filter_var($airAirPricePoint['@attributes']['Taxes'], FILTER_SANITIZE_NUMBER_INT);


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
					$TotalPrice = (int) filter_var($airAirPricePoint['@attributes']['TotalPrice'], FILTER_SANITIZE_NUMBER_INT) + $additional;
					
					$Commission = $TotalPrice - $AgentPrice;

					$diff = 0;
					$OtherCharges = 0;
					if($AgentPrice > $TotalPrice){
						$diff = $AgentPrice - $TotalPrice;
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
						$aEquivalentBasePrice = isset($adultPrice['@attributes']['EquivalentBasePrice']) ?
						$adultPrice['@attributes']['EquivalentBasePrice'] : $adultPrice['@attributes']['BasePrice'];
						$aPassengerTaxes = $adultPrice['@attributes']['Taxes'];
						$aPaxType = isset($adultPrice['airPassengerType'][0]['@attributes']['Code']) ?
									$adultPrice['airPassengerType'][0]['@attributes']['Code'] :
									$adultPrice['airPassengerType']['@attributes']['Code'];
						$aAirFareInfoKey = $airAirPricePoint['airAirPricingInfo'][0]['@attributes']['Key'];
						$agoFareInfoRef = $airAirPricePoint['airAirPricingInfo'][0]['airFareInfoRef'][0]['@attributes']['Key'];
						$abackFareInfoRef = $airAirPricePoint['airAirPricingInfo'][0]['airFareInfoRef'][1]['@attributes']['Key'];
						
						$adultBreakDown = array("BaseFare"=> "$aEquivalentBasePrice",
												"Tax"=> "$aPassengerTaxes",
												"PaxCount"=> $adult,
												"PaxType"=> "$aPaxType",
												"Discount"=> "0",
												"OtherCharges"=> "0",
												"ServiceFee"=> "0",
												"AirFareInfo"=> $aAirFareInfoKey,
												"goFareInfoRef"=>  $agoFareInfoRef,
												"backFareInfoRef"=>  $abackFareInfoRef);
						
						array_push($PriceBreakDown, $adultBreakDown);
							
						$childPrice = $airAirPricePoint['airAirPricingInfo'][2];
						$cEquivalentBasePrice = isset($childPrice['@attributes']['EquivalentBasePrice']) ? $childPrice['@attributes']['EquivalentBasePrice'] : $childPrice['@attributes']['BasePrice'];
						$cPassengerTaxes = $childPrice['@attributes']['Taxes'];
						$cPaxType = isset($childPrice['airPassengerType'][0]['@attributes']['Code']) ?
									$childPrice['airPassengerType'][0]['@attributes']['Code'] :
									$childPrice['airPassengerType']['@attributes']['Code'];
						$cAirFareInfoKey = $airAirPricePoint['airAirPricingInfo'][2]['@attributes']['Key'];
						$cgoFareInfoRef = $airAirPricePoint['airAirPricingInfo'][2]['airFareInfoRef'][0]['@attributes']['Key'];
						$cbackFareInfoRef = $airAirPricePoint['airAirPricingInfo'][2]['airFareInfoRef'][1]['@attributes']['Key'];
						$childBreakDown = array("BaseFare"=> "$cEquivalentBasePrice",
												"Tax"=> "$cPassengerTaxes",
												"PaxCount"=> $child,
												"PaxType"=> "$cPaxType",
												"Discount"=> "0",
												"OtherCharges"=> "0",
												"ServiceFee"=> "0",
												"AirFareInfo"=> $cAirFareInfoKey,
												"goFareInfoRef"=>  $cgoFareInfoRef,
												"backFareInfoRef"=>  $cbackFareInfoRef );
												
						array_push($PriceBreakDown, $childBreakDown);
							
							
						$infantsPrice = $airAirPricePoint['airAirPricingInfo'][1];
						$iEquivalentBasePrice = isset($infantsPrice['@attributes']['EquivalentBasePrice']) ? $infantsPrice['@attributes']['EquivalentBasePrice'] :  $infantsPrice['@attributes']['BasePrice'];
						$iPassengerTaxes = $infantsPrice['@attributes']['Taxes'];
						$iPaxType = isset($infantsPrice['airPassengerType']['@attributes']['Code']) ?
									$infantsPrice['airPassengerType']['@attributes']['Code'] :
									$infantsPrice['airPassengerType'][0]['@attributes']['Code'];
						$iAirFareInfoKey = $infantsPrice['@attributes']['Key'];
						$igoFareInfoRef = $airAirPricePoint['airAirPricingInfo'][0]['airFareInfoRef'][0]['@attributes']['Key'];
						$ibackFareInfoRef = $airAirPricePoint['airAirPricingInfo'][0]['airFareInfoRef'][1]['@attributes']['Key'];
						$infantBreakDown = array("BaseFare"=> "$iEquivalentBasePrice",
												"Tax"=> "$iPassengerTaxes",
												"PaxCount"=> $infants,
												"PaxType"=> "$iPaxType",
												"Discount"=> "0",
												"OtherCharges"=> "0",
												"ServiceFee"=> "0",
												"AirFareInfo"=> $iAirFareInfoKey,
												"goFareInfoRef"=>  $igoFareInfoRef,
												"backFareInfoRef"=>  $ibackFareInfoRef );
												
						array_push($PriceBreakDown, $infantBreakDown);       
					}else if($adult  > 0 && $child > 0){
						$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'][0];
						$airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption'];
						
						$adultPrice = $airAirPricePoint['airAirPricingInfo'][0];
						$aEquivalentBasePrice = isset($adultPrice['@attributes']['EquivalentBasePrice']) ? $adultPrice['@attributes']['EquivalentBasePrice'] : $adultPrice['@attributes']['BasePrice'] ;
						$aPassengerTaxes = $adultPrice['@attributes']['Taxes'];
						$aPaxType = isset($adultPrice['airPassengerType']['@attributes']['Code']) ? 
									$adultPrice['airPassengerType']['@attributes']['Code'] :
									$adultPrice['airPassengerType'][0]['@attributes']['Code'];
						$aAirFareInfoKey = $airAirPricePoint['airAirPricingInfo'][0]['@attributes']['Key'];
						$agoFareInfoRef = $airAirPricePoint['airAirPricingInfo'][0]['airFareInfoRef'][0]['@attributes']['Key'];
						$abackFareInfoRef = $airAirPricePoint['airAirPricingInfo'][0]['airFareInfoRef'][1]['@attributes']['Key'];
						
						$adultBreakDown = array("BaseFare"=> "$aEquivalentBasePrice",
												"Tax"=> "$aPassengerTaxes",
												"PaxCount"=> $adult,
												"PaxType"=> "$aPaxType",
												"Discount"=> "0",
												"OtherCharges"=> "0",
												"ServiceFee"=> "0",
												"AirFareInfo"=> $aAirFareInfoKey,
												"goFareInfoRef"=>  $agoFareInfoRef,
												"backFareInfoRef"=>  $abackFareInfoRef );
						
						array_push($PriceBreakDown, $adultBreakDown);
							
						$childPrice = $airAirPricePoint['airAirPricingInfo'][1];
						$cEquivalentBasePrice = isset($childPrice['@attributes']['EquivalentBasePrice']) ? $childPrice['@attributes']['EquivalentBasePrice'] : $childPrice['@attributes']['BasePrice'];
						$cPassengerTaxes = $childPrice['@attributes']['Taxes'];
						$cPaxType = isset($childPrice['airPassengerType'][0]['@attributes']['Code']) ?
									$childPrice['airPassengerType'][0]['@attributes']['Code'] :
									$childPrice['airPassengerType']['@attributes']['Code'];
						$cgoFareInfoRef = $airAirPricePoint['airAirPricingInfo'][1]['airFareInfoRef'][0]['@attributes']['Key'];
						$cbackFareInfoRef = $airAirPricePoint['airAirPricingInfo'][1]['airFareInfoRef'][1]['@attributes']['Key'];
						$childBreakDown = array("BaseFare"=> "$cEquivalentBasePrice",
												"Tax"=> "$cPassengerTaxes",
												"PaxCount"=> $child,
												"PaxType"=> "$cPaxType",
												"Discount"=> "0",
												"OtherCharges"=> "0",
												"ServiceFee"=> "0",
												"AirFareInfo"=> $cAirFareInfoKey,
												"goFareInfoRef"=>  $cgoFareInfoRef,
												"backFareInfoRef"=>  $cbackFareInfoRef );
												
						array_push($PriceBreakDown, $childBreakDown);
					}else if($adult  > 0 && $infants > 0 ){
						$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'][0];
						$airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption'];
						
						$adultPrice = $airAirPricePoint['airAirPricingInfo'][0];
						$aEquivalentBasePrice = isset($adultPrice['@attributes']['EquivalentBasePrice']) ?
						$adultPrice['@attributes']['EquivalentBasePrice'] : $adultPrice['@attributes']['BasePrice'];
						$aPassengerTaxes = $adultPrice['@attributes']['Taxes'];
						$aPaxType = isset($adultPrice['airPassengerType']['@attributes']['Code']) ? 
									$adultPrice['airPassengerType']['@attributes']['Code'] :
									$adultPrice['airPassengerType'][0]['@attributes']['Code'];
						$aAirFareInfoKey = $airAirPricePoint['airAirPricingInfo'][0]['@attributes']['Key'];
						$agoFareInfoRef = $airAirPricePoint['airAirPricingInfo'][0]['airFareInfoRef'][0]['@attributes']['Key'];
						$abackFareInfoRef = $airAirPricePoint['airAirPricingInfo'][0]['airFareInfoRef'][1]['@attributes']['Key'];
						$adultBreakDown = array("BaseFare"=> "$aEquivalentBasePrice",
												"Tax"=> "$aPassengerTaxes",
												"PaxCount"=> $adult,
												"PaxType"=> "$aPaxType",
												"Discount"=> "0",
												"OtherCharges"=> "0",
												"ServiceFee"=> "0",
												"AirFareInfo"=> $aAirFareInfoKey,
												"goFareInfoRef"=>  $agoFareInfoRef,
												"backFareInfoRef"=>  $abackFareInfoRef );
						
						array_push($PriceBreakDown, $adultBreakDown);
							
									
						$infantsPrice = $airAirPricePoint['airAirPricingInfo'][1];
						$iEquivalentBasePrice = isset($infantsPrice['@attributes']['EquivalentBasePrice']) ? $infantsPrice['@attributes']['EquivalentBasePrice'] :  $infantsPrice['@attributes']['BasePrice'];
						$iPassengerTaxes = $infantsPrice['@attributes']['Taxes'];
						$iPaxType = isset($infantsPrice['airPassengerType']['@attributes']['Code']) ?
									$infantsPrice['airPassengerType']['@attributes']['Code'] :
									$infantsPrice['airPassengerType'][0]['@attributes']['Code'];
						$iAirFareInfoKey = $infantsPrice['@attributes']['Key'];
						$igoFareInfoRef = $infantsPrice['airFareInfoRef'][0]['@attributes']['Key'];
						$ibackFareInfoRef = $infantsPrice['airFareInfoRef'][1]['@attributes']['Key'];
						$infantBreakDown = array("BaseFare"=> "$iEquivalentBasePrice",
												"Tax"=> "$iPassengerTaxes",
												"PaxCount"=> $infants,
												"PaxType"=> "$iPaxType",
												"Discount"=> "0",
												"OtherCharges"=> "0",
												"ServiceFee"=> "0",
												"AirFareInfo"=> $iAirFareInfoKey,
												"goFareInfoRef"=>  $igoFareInfoRef,
												"backFareInfoRef"=>  $ibackFareInfoRef );
												
						array_push($PriceBreakDown, $infantBreakDown);
					}else if($adult  > 0){
						
						$airPricePointOptions = $airAirPricePoint['airAirPricingInfo'];
						$airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption'];

						$adultPrice = $airAirPricePoint['airAirPricingInfo'];
						$aEquivalentBasePrice = isset($adultPrice['@attributes']['EquivalentBasePrice']) ?
						$adultPrice['@attributes']['EquivalentBasePrice'] : $adultPrice['@attributes']['BasePrice'];
						$aPassengerTaxes = $airPricePointOptions['@attributes']['Taxes'];
						$aPaxType = isset($adultPrice['airPassengerType'][0]['@attributes']['Code']) ?
									$adultPrice['airPassengerType'][0]['@attributes']['Code'] :
									$adultPrice['airPassengerType']['@attributes']['Code'];
						$aAirFareInfoKey = $airPricePointOptions['@attributes']['Key'];
						$agoFareInfoRef =  $airPricePointOptions['airFareInfoRef'][0]['@attributes']['Key'];
						$abackFareInfoRef =  $airPricePointOptions['airFareInfoRef'][1]['@attributes']['Key'];
						
						$adultBreakDown = array("BaseFare"=> "$aEquivalentBasePrice",
												"Tax"=> "$aPassengerTaxes",
												"PaxCount"=> $adult,
												"PaxType"=> "$aPaxType",
												"Discount"=> "0",
												"OtherCharges"=> "0",
												"ServiceFee"=> "0",
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
					
					if(isset($airPricePoint[0]['airOption'][0]) == TRUE && isset($airPricePoint[1]['airOption'][0]) == TRUE){
						
						$op=0;
						$sgcount1 = 1;
						$sgcount2 = 1;

						if(isset($airPricePoint[0]['airOption'][0]['airBookingInfo'][0])){
							$sgcount1 = count($airPricePoint[1]['airOption'][0]['airBookingInfo']); 			
						}
						if(isset($airPricePoint[1]['airOption'][0]['airBookingInfo'][0])){							
							$sgcount2 = count($airPricePoint[1]['airOption'][0]['airBookingInfo']);	
						}
											
						if($sgcount1 == 1 && $sgcount2 == 1){
						
							//Go Leg1
							
							$goFareInfoRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo']['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo']['@attributes']['SegmentRef'];
							
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
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][$op]['airBookingInfo']['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][$op]['airBookingInfo']['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][$op]['airBookingInfo']['@attributes']['CabinClass'];


							//Back Leg 1

							$backFareInfoRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo']['@attributes']['FareInfoRef'];
							$backSegmentRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo']['@attributes']['SegmentRef'];
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
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][$op]['airBookingInfo']['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][$op]['airBookingInfo']['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][$op]['airBookingInfo']['@attributes']['CabinClass'];

														

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
															"seat"=> "$goSeat",
															"bags" => "$goBags",
															"class" => "$goCabinClass",
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
																"seat"=> "$backSeat",
																"bags" => "$backBags",
																"class" => "$backCabinClass",
																"segmentDetails"=> $airAirSegment[$backSegmentRef])																							

												)
											);

							$basic = array("system" => "Galileo",
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
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
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
						
						$goFareInfoRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
						$goSegmentRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['SegmentRef'];
						
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
						
						
						$goBookingCode = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCode'];
						$goSeat = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCount'];
						$goCabinClass = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['CabinClass'];


						//Go Leg2
						
						$goFareInfoRef1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
						$goSegmentRef1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['SegmentRef'];
						
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
						
						
						$goBookingCode1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCode'];
						$goSeat1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCount'];
						$goCabinClass1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['CabinClass'];



						//Back Leg 1

						$backFareInfoRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
						$backSegmentRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['SegmentRef'];
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
						
						
						$backBookingCode = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCode'];
						$backSeat = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCount'];
						$backCabinClass = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['CabinClass'];

						
						//Back Leg 2
						
						$backFareInfoRef1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
						$backSegmentRef1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['SegmentRef'];

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
						
						
						$backBookingCode1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCode'];
						$backSeat1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCount'];
						$backCabinClass1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['CabinClass'];
						
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
														"flightduration"=> "$goFlightTimeHm1",
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
															"flightduration"=> "$backFlightTimeHm",
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
															"flightduration"=> "$backFlightTimeHm1",
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
										"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
										"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
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


						}else if($sgcount1 == 3 && $sgcount2 == 3 ){

							//Go Leg 1
							
							$goFareInfoRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['SegmentRef'];
							
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
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['CabinClass'];


							//Go Leg 2
							
							$goFareInfoRef1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$goSegmentRef1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['SegmentRef'];
							
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
							$goasql1 = mysqli_query($conn,"$Airportsql code='$goArrivalTo1' ");
							$goarow1 = mysqli_fetch_array($goasql1,MYSQLI_ASSOC);

							if(!empty($goarow1)){
								$goaAirport1 = $goarow1['name'];
								$goaCity1 = $goarow1['cityName'];
								$goaCountry1 = $goarow1['countryCode'];
							}
							
							
							$goBookingCode1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$goSeat1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$goCabinClass1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['CabinClass'];


							//Go Leg 3
							
							$goFareInfoRef2 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][2]['@attributes']['FareInfoRef'];
							$goSegmentRef2 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][2]['@attributes']['SegmentRef'];
							
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

							$gosqlmk2 = mysqli_query($conn,"$Airportsql code='$gomarkettingCarrier2' ");
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
							$goasql2 = mysqli_query($conn,"$Airportsql code='$goArrivalTo2' ");
							$goarow2 = mysqli_fetch_array($goasql2,MYSQLI_ASSOC);

							if(!empty($goarow2)){
								$goaAirport2 = $goarow2['name'];
								$goaCity2 = $goarow2['cityName'];
								$goaCountry2 = $goarow2['countryCode'];
							}
							
							
							$goBookingCode2 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][2]['@attributes']['BookingCode'];
							$goSeat2 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][2]['@attributes']['BookingCount'];
							$goCabinClass2 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][2]['@attributes']['CabinClass'];

							
							//  Back Leg
							$backFareInfoRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$backSegmentRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['SegmentRef'];

							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];
							$backFlightTime = $airAirSegment[$backSegmentRef]['FlightTime'];
							$backFlightTimeHm = floor($backFlightTime / 60)."H ".($backFlightTime - ((floor($backFlightTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backfromTime = substr($backDepartureTime,11, 19);
							$backdpTime = date("D d M Y", strtotime(substr($backDepartureTime,0, 10)." ".$backfromTime));

							$backtoTime = substr($backArrivalTime,11, 19);
							$backarrTime = date("D d M Y", strtotime(substr($backArrivalTime,0, 10)." ".$backtoTime));

							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backsqlmk = mysqli_query($conn,"$Airportsql code='$backmarkettingCarrier' ");
							$backrowmk = mysqli_fetch_array($backsqlmk,MYSQLI_ASSOC);

							if(!empty($backrowmk)){
								$backmarkettingCarrierName = $backrowmk['name'];		
							}

							// Departure Country
							$backsqldp = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backrowdp = mysqli_fetch_array($backsqldp,MYSQLI_ASSOC);

							if(!empty($backrowdp)){
								$backdAirport = $backrowdp['name'];
								$backdCity = $backrowdp['cityName'];
								$backdCountry = $backrowdp['countryCode'];		
							}

							// Departure Country
							$backsqlar = mysqli_query($conn,"$Airportsql code='$backArrivalTo' ");
							$backrowar = mysqli_fetch_array($backsqlar,MYSQLI_ASSOC);

							if(!empty($backrowar)){
								$backaAirport = $backrowar['name'];
								$backaCity = $backrowar['cityName'];
								$backaCountry = $backrowar['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['CabinClass'];

							
							//Back Leg 1							
							$backFareInfoRef1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$backSegmentRef1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['SegmentRef'];

							$backBags1 = $airFareInfo[$backFareInfoRef1]['Bags'];
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

							$backsqlmk1 = mysqli_query($conn,"$Airportsql code='$backmarkettingCarrier1' ");
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

							// Arrival Country
							$backsqlar1 = mysqli_query($conn,"$Airportsql code='$backArrivalTo1' ");
							$backrowar1 = mysqli_fetch_array($backsqlar1,MYSQLI_ASSOC);

							if(!empty($backrowar1)){
								$backaAirport1 = $backrowar1['name'];
								$backaCity1 = $backrowar1['cityName'];
								$backaCountry1 = $backrowar1['countryCode'];
							}
							
							
							$backBookingCode1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$backSeat1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$backCabinClass1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['CabinClass'];
							

							//Back Leg 2
							
							$backFareInfoRef2 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][2]['@attributes']['FareInfoRef'];
							$backSegmentRef2 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][2]['@attributes']['SegmentRef'];

							$backBags2 = $airFareInfo[$backFareInfoRef2]['Bags'];
							$backFlightTime2 = $airAirSegment[$backSegmentRef2]['FlightTime'];
							$backFlightTimeHm2 = floor($backFlightTime2 / 60)."H ".($backFlightTime2 - ((floor($backFlightTime2 / 60)) * 60))."Min";


							$backArrivalTo2 = $airAirSegment[$backSegmentRef2]['Destination'];
							$backDepartureFrom2 = $airAirSegment[$backSegmentRef2]['Origin'];

							$backArrivalTime2 = $airAirSegment[$backSegmentRef2]['ArrivalTime'];
							$backDepartureTime2 = $airAirSegment[$backSegmentRef2]['DepartureTime'];

							$backfromTime2 = substr($backDepartureTime2,11, 19);
							$backdpTime2 = date("D d M Y", strtotime(substr($backDepartureTime2,0, 10)." ".$backfromTime2));

							$backtoTime2 = substr($backArrivalTime2,11, 19);
							$backarrTime2 = date("D d M Y", strtotime(substr($backArrivalTime2,0, 10)." ".$backtoTime2));

							
							$backmarkettingCarrier2 = $airAirSegment[$backSegmentRef2]['Carrier'];
							$backmarkettingFN2 = $airAirSegment[$backSegmentRef2]['FlightNumber'];

							$backsqlmk2 = mysqli_query($conn,"$Airportsql code='$backmarkettingCarrier2' ");
							$backrowmk2 = mysqli_fetch_array($backsqlmk2,MYSQLI_ASSOC);

							if(!empty($backrowmk2)){
								$backmarkettingCarrierName2 = $backrowmk2['name'];		
							}

							// Departure Country
							$backsqldp2 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom2' ");
							$backrowdp2 = mysqli_fetch_array($backsqldp2,MYSQLI_ASSOC);

							if(!empty($backrowdp2)){
								$backdAirport2 = $backrowdp2['name'];
								$backdCity2 = $backrowdp2['cityName'];
								$backdCountry2 = $backrowdp2['countryCode'];		
							}

							// Departure Country
							$backsqlar2 = mysqli_query($conn,"$Airportsql code='$backArrivalTo2' ");
							$backrowar2 = mysqli_fetch_array($backsqlar2,MYSQLI_ASSOC);

							if(!empty($backrowar2)){
								$backaAirport2 = $backrowar2['name'];
								$backaCity2 = $backrowar2['cityName'];
								$backaCountry2 = $backrowar2['countryCode'];
							}
							
							
							$backBookingCode2 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][2]['@attributes']['BookingCode'];
							$backSeat2 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][2]['@attributes']['BookingCount'];
							$backCabinClass2 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][2]['@attributes']['CabinClass'];
							
							$goTravelTimeHm = $airAirSegment[$goSegmentRef]['TravelTime'];
							$goTravelTime = floor($goTravelTimeHm / 60)."H ".($goTravelTimeHm - ((floor($goTravelTimeHm / 60)) * 60))."Min";
							
							$backTravelTimeHm = $airAirSegment[$backSegmentRef]['TravelTime'];
							$backTravelTime = floor($backTravelTimeHm / 60)."H ".($backTravelTimeHm - ((floor($backTravelTimeHm / 60)) * 60))."Min";

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
															"flightduration"=> "$goFlightTimeHm1",
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
															"flightduration"=> "$goFlightTimeHm2",
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
																"flightduration"=> "$backFlightTimeHm",
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
																"flightduration"=> "$backFlightTimeHm1",
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
																"flightduration"=> "$backFlightTimeHm2",
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
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
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
						
														
					}else if(isset($airPricePoint[0]['airOption'][1]) == TRUE && isset($airPricePoint[1]['airOption'][1]) == TRUE){
						
						$op=1;
						$sgcount1 = 1;
						$sgcount2 = 1;

						if(isset($airPricePoint[0]['airOption'][0]['airBookingInfo'][0])){
							$sgcount1 = count($airPricePoint[1]['airOption'][0]['airBookingInfo']); 			
						}
						if(isset($airPricePoint[1]['airOption'][0]['airBookingInfo'][0])){							
							$sgcount2 = count($airPricePoint[1]['airOption'][0]['airBookingInfo']);	
						}
											
						if($sgcount1 == 1 && $sgcount2 == 1){
						
							//Go Leg1
							
							$goFareInfoRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo']['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo']['@attributes']['SegmentRef'];
							
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
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][$op]['airBookingInfo']['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][$op]['airBookingInfo']['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][$op]['airBookingInfo']['@attributes']['CabinClass'];


							//Back Leg 1

							$backFareInfoRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo']['@attributes']['FareInfoRef'];
							$backSegmentRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo']['@attributes']['SegmentRef'];
							
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
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][$op]['airBookingInfo']['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][$op]['airBookingInfo']['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][$op]['airBookingInfo']['@attributes']['CabinClass'];

														

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
															"seat"=> "$goSeat",
															"bags" => "$goBags",
															"class" => "$goCabinClass",
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
																"seat"=> "$backSeat",
																"bags" => "$backBags",
																"class" => "$backCabinClass",
																"segmentDetails"=> $airAirSegment[$backSegmentRef])																							

												)
											);

							$basic = array("system" => "Galileo",
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
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
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
						
						$goFareInfoRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
						$goSegmentRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['SegmentRef'];
						
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
						
						
						$goBookingCode = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCode'];
						$goSeat = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCount'];
						$goCabinClass = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['CabinClass'];


						//Go Leg2
						
						$goFareInfoRef1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
						$goSegmentRef1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['SegmentRef'];
						
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
						
						
						$goBookingCode1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCode'];
						$goSeat1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCount'];
						$goCabinClass1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['CabinClass'];



						//Back Leg 1

						$backFareInfoRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
						$backSegmentRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['SegmentRef'];
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
						
						
						$backBookingCode = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCode'];
						$backSeat = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCount'];
						$backCabinClass = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['CabinClass'];

						
						//Back Leg 2
						
						$backFareInfoRef1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
						$backSegmentRef1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['SegmentRef'];

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
						
						
						$backBookingCode1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCode'];
						$backSeat1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCount'];
						$backCabinClass1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['CabinClass'];
						
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
															"flightduration"=> "$backFlightTimeHm",
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
										"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
										"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
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


						}else if($sgcount1 == 3 && $sgcount2 == 3 ){

							//Go Leg 1
							
							$goFareInfoRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['SegmentRef'];
							
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
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['CabinClass'];


							//Go Leg 2
							
							$goFareInfoRef1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$goSegmentRef1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['SegmentRef'];
							
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
							$goasql1 = mysqli_query($conn,"$Airportsql code='$goArrivalTo1' ");
							$goarow1 = mysqli_fetch_array($goasql1,MYSQLI_ASSOC);

							if(!empty($goarow1)){
								$goaAirport1 = $goarow1['name'];
								$goaCity1 = $goarow1['cityName'];
								$goaCountry1 = $goarow1['countryCode'];
							}
							
							
							$goBookingCode1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$goSeat1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$goCabinClass1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['CabinClass'];


							//Go Leg 3
							
							$goFareInfoRef2 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][2]['@attributes']['FareInfoRef'];
							$goSegmentRef2 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][2]['@attributes']['SegmentRef'];
							
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

							$gosqlmk2 = mysqli_query($conn,"$Airportsql code='$gomarkettingCarrier2' ");
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
							$goasql2 = mysqli_query($conn,"$Airportsql code='$goArrivalTo2' ");
							$goarow2 = mysqli_fetch_array($goasql2,MYSQLI_ASSOC);

							if(!empty($goarow2)){
								$goaAirport2 = $goarow2['name'];
								$goaCity2 = $goarow2['cityName'];
								$goaCountry2 = $goarow2['countryCode'];
							}
							
							
							$goBookingCode2 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][2]['@attributes']['BookingCode'];
							$goSeat2 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][2]['@attributes']['BookingCount'];
							$goCabinClass2 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][2]['@attributes']['CabinClass'];

							
							//  Back Leg
							$backFareInfoRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$backSegmentRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['SegmentRef'];

							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];
							$backFlightTime = $airAirSegment[$backSegmentRef]['FlightTime'];
							$backFlightTimeHm = floor($backFlightTime / 60)."H ".($backFlightTime - ((floor($backFlightTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backfromTime = substr($backDepartureTime,11, 19);
							$backdpTime = date("D d M Y", strtotime(substr($backDepartureTime,0, 10)." ".$backfromTime));

							$backtoTime = substr($backArrivalTime,11, 19);
							$backarrTime = date("D d M Y", strtotime(substr($backArrivalTime,0, 10)." ".$backtoTime));

							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backsqlmk = mysqli_query($conn,"$Airportsql code='$backmarkettingCarrier' ");
							$backrowmk = mysqli_fetch_array($backsqlmk,MYSQLI_ASSOC);

							if(!empty($backrowmk)){
								$backmarkettingCarrierName = $backrowmk['name'];		
							}

							// Departure Country
							$backsqldp = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backrowdp = mysqli_fetch_array($backsqldp,MYSQLI_ASSOC);

							if(!empty($backrowdp)){
								$backdAirport = $backrowdp['name'];
								$backdCity = $backrowdp['cityName'];
								$backdCountry = $backrowdp['countryCode'];		
							}

							// Departure Country
							$backsqlar = mysqli_query($conn,"$Airportsql code='$backArrivalTo' ");
							$backrowar = mysqli_fetch_array($backsqlar,MYSQLI_ASSOC);

							if(!empty($backrowar)){
								$backaAirport = $backrowar['name'];
								$backaCity = $backrowar['cityName'];
								$backaCountry = $backrowar['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['CabinClass'];

							
							//Back Leg 1							
							$backFareInfoRef1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$backSegmentRef1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['SegmentRef'];

							$backBags1 = $airFareInfo[$backFareInfoRef1]['Bags'];
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

							$backsqlmk1 = mysqli_query($conn,"$Airportsql code='$backmarkettingCarrier1' ");
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

							// Arrival Country
							$backsqlar1 = mysqli_query($conn,"$Airportsql code='$backArrivalTo1' ");
							$backrowar1 = mysqli_fetch_array($backsqlar1,MYSQLI_ASSOC);

							if(!empty($backrowar1)){
								$backaAirport1 = $backrowar1['name'];
								$backaCity1 = $backrowar1['cityName'];
								$backaCountry1 = $backrowar1['countryCode'];
							}
							
							
							$backBookingCode1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$backSeat1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$backCabinClass1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['CabinClass'];
							

							//Back Leg 2
							
							$backFareInfoRef2 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][2]['@attributes']['FareInfoRef'];
							$backSegmentRef2 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][2]['@attributes']['SegmentRef'];

							$backBags2 = $airFareInfo[$backFareInfoRef2]['Bags'];
							$backFlightTime2 = $airAirSegment[$backSegmentRef2]['FlightTime'];
							$backFlightTimeHm2 = floor($backFlightTime2 / 60)."H ".($backFlightTime2 - ((floor($backFlightTime2 / 60)) * 60))."Min";


							$backArrivalTo2 = $airAirSegment[$backSegmentRef2]['Destination'];
							$backDepartureFrom2 = $airAirSegment[$backSegmentRef2]['Origin'];

							$backArrivalTime2 = $airAirSegment[$backSegmentRef2]['ArrivalTime'];
							$backDepartureTime2 = $airAirSegment[$backSegmentRef2]['DepartureTime'];

							$backfromTime2 = substr($backDepartureTime2,11, 19);
							$backdpTime2 = date("D d M Y", strtotime(substr($backDepartureTime2,0, 10)." ".$backfromTime2));

							$backtoTime2 = substr($backArrivalTime2,11, 19);
							$backarrTime2 = date("D d M Y", strtotime(substr($backArrivalTime2,0, 10)." ".$backtoTime2));

							
							$backmarkettingCarrier2 = $airAirSegment[$backSegmentRef2]['Carrier'];
							$backmarkettingFN2 = $airAirSegment[$backSegmentRef2]['FlightNumber'];

							$backsqlmk2 = mysqli_query($conn,"$Airportsql code='$backmarkettingCarrier2' ");
							$backrowmk2 = mysqli_fetch_array($backsqlmk2,MYSQLI_ASSOC);

							if(!empty($backrowmk2)){
								$backmarkettingCarrierName2 = $backrowmk2['name'];		
							}

							// Departure Country
							$backsqldp2 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom2' ");
							$backrowdp2 = mysqli_fetch_array($backsqldp2,MYSQLI_ASSOC);

							if(!empty($backrowdp2)){
								$backdAirport2 = $backrowdp2['name'];
								$backdCity2 = $backrowdp2['cityName'];
								$backdCountry2 = $backrowdp2['countryCode'];		
							}

							// Departure Country
							$backsqlar2 = mysqli_query($conn,"$Airportsql WHERE code='$backArrivalTo2' ");
							$backrowar2 = mysqli_fetch_array($backsqlar2,MYSQLI_ASSOC);

							if(!empty($backrowar2)){
								$backaAirport2 = $backrowar2['name'];
								$backaCity2 = $backrowar2['cityName'];
								$backaCountry2 = $backrowar2['countryCode'];
							}
							
							
							$backBookingCode2 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][2]['@attributes']['BookingCode'];
							$backSeat2 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][2]['@attributes']['BookingCount'];
							$backCabinClass2 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][2]['@attributes']['CabinClass'];
							
							$goTravelTimeHm = $airAirSegment[$goSegmentRef]['TravelTime'];
							$goTravelTime = floor($goTravelTimeHm / 60)."H ".($goTravelTimeHm - ((floor($goTravelTimeHm / 60)) * 60))."Min";
							
							$backTravelTimeHm = $airAirSegment[$backSegmentRef]['TravelTime'];
							$backTravelTime = floor($backTravelTimeHm / 60)."H ".($backTravelTimeHm - ((floor($backTravelTimeHm / 60)) * 60))."Min";

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
																"flightduration"=> "$backFlightTimeHm",
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
																"flightduration"=> "$backFlightTimeHm1",
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
																"flightduration"=> "$backFlightTimeHm2",
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
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
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
						
														
					}else if(isset($airPricePoint[0]['airOption'][2]) == TRUE && isset($airPricePoint[1]['airOption'][2]) == TRUE){
						
						$op=2;
						$sgcount1 = 1;
						$sgcount2 = 1;

						if(isset($airPricePoint[0]['airOption'][0]['airBookingInfo'][0])){
							$sgcount1 = count($airPricePoint[1]['airOption'][0]['airBookingInfo']); 			
						}
						if(isset($airPricePoint[1]['airOption'][0]['airBookingInfo'][0])){							
							$sgcount2 = count($airPricePoint[1]['airOption'][0]['airBookingInfo']);	
						}
											
						if($sgcount1 == 1 && $sgcount2 == 1){
						
							//Go Leg1
							
							$goFareInfoRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo']['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo']['@attributes']['SegmentRef'];
							
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
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][$op]['airBookingInfo']['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][$op]['airBookingInfo']['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][$op]['airBookingInfo']['@attributes']['CabinClass'];


							//Back Leg 1

							$backFareInfoRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo']['@attributes']['FareInfoRef'];
							$backSegmentRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo']['@attributes']['SegmentRef'];
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
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][$op]['airBookingInfo']['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][$op]['airBookingInfo']['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][$op]['airBookingInfo']['@attributes']['CabinClass'];

														

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
															"seat"=> "$goSeat",
															"bags" => "$goBags",
															"class" => "$goCabinClass",
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
																"seat"=> "$backSeat",
																"bags" => "$backBags",
																"class" => "$backCabinClass",
																"segmentDetails"=> $airAirSegment[$backSegmentRef])																							

												)
											);

							$basic = array("system" => "Galileo",
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
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
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
						
						$goFareInfoRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
						$goSegmentRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['SegmentRef'];
						
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
						
						
						$goBookingCode = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCode'];
						$goSeat = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCount'];
						$goCabinClass = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['CabinClass'];


						//Go Leg2
						
						$goFareInfoRef1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
						$goSegmentRef1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['SegmentRef'];
						
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
						
						
						$goBookingCode1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCode'];
						$goSeat1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCount'];
						$goCabinClass1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['CabinClass'];



						//Back Leg 1

						$backFareInfoRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
						$backSegmentRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['SegmentRef'];
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
						
						
						$backBookingCode = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCode'];
						$backSeat = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCount'];
						$backCabinClass = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['CabinClass'];

						
						//Back Leg 2
						
						$backFareInfoRef1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
						$backSegmentRef1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['SegmentRef'];

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
						
						
						$backBookingCode1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCode'];
						$backSeat1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCount'];
						$backCabinClass1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['CabinClass'];
						
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
														"flightduration"=> "$goFlightTimeHm1",
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
															"flightduration"=> "$backFlightTimeHm",
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
										"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
										"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
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


						}else if($sgcount1 == 3 && $sgcount2 == 3 ){

							//Go Leg 1
							
							$goFareInfoRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$goSegmentRef = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['SegmentRef'];
							
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
							
							
							$goBookingCode = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$goSeat = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$goCabinClass = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][0]['@attributes']['CabinClass'];


							//Go Leg 2
							
							$goFareInfoRef1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$goSegmentRef1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['SegmentRef'];
							
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
							$goasql1 = mysqli_query($conn,"$Airportsql code='$goArrivalTo1' ");
							$goarow1 = mysqli_fetch_array($goasql1,MYSQLI_ASSOC);

							if(!empty($goarow1)){
								$goaAirport1 = $goarow1['name'];
								$goaCity1 = $goarow1['cityName'];
								$goaCountry1 = $goarow1['countryCode'];
							}
							
							
							$goBookingCode1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$goSeat1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$goCabinClass1 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][1]['@attributes']['CabinClass'];


							//Go Leg 3
							
							$goFareInfoRef2 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][2]['@attributes']['FareInfoRef'];
							$goSegmentRef2 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][2]['@attributes']['SegmentRef'];
							
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

							$gosqlmk2 = mysqli_query($conn,"$Airportsql code='$gomarkettingCarrier2' ");
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
							$goasql2 = mysqli_query($conn,"$Airportsql code='$goArrivalTo2' ");
							$goarow2 = mysqli_fetch_array($goasql2,MYSQLI_ASSOC);

							if(!empty($goarow2)){
								$goaAirport2 = $goarow2['name'];
								$goaCity2 = $goarow2['cityName'];
								$goaCountry2 = $goarow2['countryCode'];
							}
							
							
							$goBookingCode2 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][2]['@attributes']['BookingCode'];
							$goSeat2 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][2]['@attributes']['BookingCount'];
							$goCabinClass2 = $airPricePoint[0]['airOption'][$op]['airBookingInfo'][2]['@attributes']['CabinClass'];

							
							//  Back Leg
							$backFareInfoRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
							$backSegmentRef = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['SegmentRef'];

							$backBags = $airFareInfo[$backFareInfoRef]['Bags'];
							$backFlightTime = $airAirSegment[$backSegmentRef]['FlightTime'];
							$backFlightTimeHm = floor($backFlightTime / 60)."H ".($backFlightTime - ((floor($backFlightTime / 60)) * 60))."Min";


							$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
							$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

							$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
							$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

							$backfromTime = substr($backDepartureTime,11, 19);
							$backdpTime = date("D d M Y", strtotime(substr($backDepartureTime,0, 10)." ".$backfromTime));

							$backtoTime = substr($backArrivalTime,11, 19);
							$backarrTime = date("D d M Y", strtotime(substr($backArrivalTime,0, 10)." ".$backtoTime));

							
							$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
							$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

							$backsqlmk = mysqli_query($conn,"$Airportsql code='$backmarkettingCarrier' ");
							$backrowmk = mysqli_fetch_array($backsqlmk,MYSQLI_ASSOC);

							if(!empty($backrowmk)){
								$backmarkettingCarrierName = $backrowmk['name'];		
							}

							// Departure Country
							$backsqldp = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
							$backrowdp = mysqli_fetch_array($backsqldp,MYSQLI_ASSOC);

							if(!empty($backrowdp)){
								$backdAirport = $backrowdp['name'];
								$backdCity = $backrowdp['cityName'];
								$backdCountry = $backrowdp['countryCode'];		
							}

							// Departure Country
							$backsqlar = mysqli_query($conn,"$Airportsql code='$backArrivalTo' ");
							$backrowar = mysqli_fetch_array($backsqlar,MYSQLI_ASSOC);

							if(!empty($backrowar)){
								$backaAirport = $backrowar['name'];
								$backaCity = $backrowar['cityName'];
								$backaCountry = $backrowar['countryCode'];
							}
							
							
							$backBookingCode = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCode'];
							$backSeat = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['BookingCount'];
							$backCabinClass = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][0]['@attributes']['CabinClass'];

							
							//Back Leg 1							
							$backFareInfoRef1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
							$backSegmentRef1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['SegmentRef'];

							$backBags1 = $airFareInfo[$backFareInfoRef1]['Bags'];
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

							$backsqlmk1 = mysqli_query($conn,"$Airportsql code='$backmarkettingCarrier1' ");
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

							// Arrival Country
							$backsqlar1 = mysqli_query($conn,"$Airportsql code='$backArrivalTo1' ");
							$backrowar1 = mysqli_fetch_array($backsqlar1,MYSQLI_ASSOC);

							if(!empty($backrowar1)){
								$backaAirport1 = $backrowar1['name'];
								$backaCity1 = $backrowar1['cityName'];
								$backaCountry1 = $backrowar1['countryCode'];
							}
							
							
							$backBookingCode1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCode'];
							$backSeat1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['BookingCount'];
							$backCabinClass1 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][1]['@attributes']['CabinClass'];
							

							//Back Leg 2
							
							$backFareInfoRef2 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][2]['@attributes']['FareInfoRef'];
							$backSegmentRef2 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][2]['@attributes']['SegmentRef'];

							$backBags2 = $airFareInfo[$backFareInfoRef2]['Bags'];
							$backFlightTime2 = $airAirSegment[$backSegmentRef2]['FlightTime'];
							$backFlightTimeHm2 = floor($backFlightTime2 / 60)."H ".($backFlightTime2 - ((floor($backFlightTime2 / 60)) * 60))."Min";


							$backArrivalTo2 = $airAirSegment[$backSegmentRef2]['Destination'];
							$backDepartureFrom2 = $airAirSegment[$backSegmentRef2]['Origin'];

							$backArrivalTime2 = $airAirSegment[$backSegmentRef2]['ArrivalTime'];
							$backDepartureTime2 = $airAirSegment[$backSegmentRef2]['DepartureTime'];

							$backfromTime2 = substr($backDepartureTime2,11, 19);
							$backdpTime2 = date("D d M Y", strtotime(substr($backDepartureTime2,0, 10)." ".$backfromTime2));

							$backtoTime2 = substr($backArrivalTime2,11, 19);
							$backarrTime2 = date("D d M Y", strtotime(substr($backArrivalTime2,0, 10)." ".$backtoTime2));

							
							$backmarkettingCarrier2 = $airAirSegment[$backSegmentRef2]['Carrier'];
							$backmarkettingFN2 = $airAirSegment[$backSegmentRef2]['FlightNumber'];

							$backsqlmk2 = mysqli_query($conn,"$Airportsql code='$backmarkettingCarrier2' ");
							$backrowmk2 = mysqli_fetch_array($backsqlmk2,MYSQLI_ASSOC);

							if(!empty($backrowmk2)){
								$backmarkettingCarrierName2 = $backrowmk2['name'];		
							}

							// Departure Country
							$backsqldp2 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom2' ");
							$backrowdp2 = mysqli_fetch_array($backsqldp2,MYSQLI_ASSOC);

							if(!empty($backrowdp2)){
								$backdAirport2 = $backrowdp2['name'];
								$backdCity2 = $backrowdp2['cityName'];
								$backdCountry2 = $backrowdp2['countryCode'];		
							}

							// Departure Country
							$backsqlar2 = mysqli_query($conn,"$Airportsql WHERE code='$backArrivalTo2' ");
							$backrowar2 = mysqli_fetch_array($backsqlar2,MYSQLI_ASSOC);

							if(!empty($backrowar2)){
								$backaAirport2 = $backrowar2['name'];
								$backaCity2 = $backrowar2['cityName'];
								$backaCountry2 = $backrowar2['countryCode'];
							}
							
							
							$backBookingCode2 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][2]['@attributes']['BookingCode'];
							$backSeat2 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][2]['@attributes']['BookingCount'];
							$backCabinClass2 = $airPricePoint[1]['airOption'][$op]['airBookingInfo'][2]['@attributes']['CabinClass'];
							
							$goTravelTimeHm = $airAirSegment[$goSegmentRef]['TravelTime'];
							$goTravelTime = floor($goTravelTimeHm / 60)."H ".($goTravelTimeHm - ((floor($goTravelTimeHm / 60)) * 60))."Min";
							
							$backTravelTimeHm = $airAirSegment[$backSegmentRef]['TravelTime'];
							$backTravelTime = floor($backTravelTimeHm / 60)."H ".($backTravelTimeHm - ((floor($backTravelTimeHm / 60)) * 60))."Min";

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
															"flightduration"=> "$goFlightTimeHm1",
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
															"flightduration"=> "$goFlightTimeHm2",
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
																"flightduration"=> "$backFlightTimeHm",
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
																"flightduration"=> "$backFlightTimeHm1",
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
																"flightduration"=> "$backFlightTimeHm2",
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
									"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
									"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
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
																"flightduration"=> "$goFlightTimeHm",
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
																"flightduration"=> "$goFlightTimeHm1",
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
																	"flightduration"=> "$backFlightTimeHm",
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
										"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
										"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
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

								
								//  Back Leg
								$backFareInfoRef = $airPricePoint[1]['airOption']['airBookingInfo'][0]['@attributes']['FareInfoRef'];
								$backSegmentRef = $airPricePoint[1]['airOption']['airBookingInfo'][0]['@attributes']['SegmentRef'];

								$backBags = $airFareInfo[$backFareInfoRef]['Bags'];
								$backFlightTime = $airAirSegment[$backSegmentRef]['FlightTime'];
								$backFlightTimeHm = floor($backFlightTime / 60)."H ".($backFlightTime - ((floor($backFlightTime / 60)) * 60))."Min";


								$backArrivalTo = $airAirSegment[$backSegmentRef]['Destination'];
								$backDepartureFrom = $airAirSegment[$backSegmentRef]['Origin'];

								$backArrivalTime = $airAirSegment[$backSegmentRef]['ArrivalTime'];
								$backDepartureTime = $airAirSegment[$backSegmentRef]['DepartureTime'];

								$backfromTime = substr($backDepartureTime,11, 19);
								$backdpTime = date("D d M Y", strtotime(substr($backDepartureTime,0, 10)." ".$backfromTime));

								$backtoTime = substr($backArrivalTime,11, 19);
								$backarrTime = date("D d M Y", strtotime(substr($backArrivalTime,0, 10)." ".$backtoTime));

								
								$backmarkettingCarrier = $airAirSegment[$backSegmentRef]['Carrier'];
								$backmarkettingFN = $airAirSegment[$backSegmentRef]['FlightNumber'];

								$backsqlmk = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier' ");
								$backrowmk = mysqli_fetch_array($backsqlmk,MYSQLI_ASSOC);

								if(!empty($backrowmk)){
									$backmarkettingCarrierName = $backrowmk['name'];		
								}

								// Departure Country
								$backsqldp = mysqli_query($conn,"$Airportsql code='$backDepartureFrom' ");
								$backrowdp = mysqli_fetch_array($backsqldp,MYSQLI_ASSOC);

								if(!empty($backrowdp)){
									$backdAirport = $backrowdp['name'];
									$backdCity = $backrowdp['cityName'];
									$backdCountry = $backrowdp['countryCode'];		
								}

								// Departure Country
								$backsqlar = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo' ");
								$backrowar = mysqli_fetch_array($backsqlar,MYSQLI_ASSOC);

								if(!empty($backrowar)){
									$backaAirport = $backrowar['name'];
									$backaCity = $backrowar['cityName'];
									$backaCountry = $backrowar['countryCode'];
								}
								
								
								$backBookingCode = $airPricePoint[1]['airOption']['airBookingInfo'][0]['@attributes']['BookingCode'];
								$backSeat = $airPricePoint[1]['airOption']['airBookingInfo'][0]['@attributes']['BookingCount'];
								$backCabinClass = $airPricePoint[1]['airOption']['airBookingInfo'][0]['@attributes']['CabinClass'];

								
								//Back Leg 1							
								$backFareInfoRef1 = $airPricePoint[1]['airOption']['airBookingInfo'][1]['@attributes']['FareInfoRef'];
								$backSegmentRef1 = $airPricePoint[1]['airOption']['airBookingInfo'][1]['@attributes']['SegmentRef'];

								$backBags1 = $airFareInfo[$backFareInfoRef1]['Bags'];
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

								// Arrival Country
								$backsqlar1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo1' ");
								$backrowar1 = mysqli_fetch_array($backsqlar1,MYSQLI_ASSOC);

								if(!empty($backrowar1)){
									$backaAirport1 = $backrowar1['name'];
									$backaCity1 = $backrowar1['cityName'];
									$backaCountry1 = $backrowar1['countryCode'];
								}
								
								
								$backBookingCode1 = $airPricePoint[1]['airOption']['airBookingInfo'][1]['@attributes']['BookingCode'];
								$backSeat1 = $airPricePoint[1]['airOption']['airBookingInfo'][1]['@attributes']['BookingCount'];
								$backCabinClass1 = $airPricePoint[1]['airOption']['airBookingInfo'][1]['@attributes']['CabinClass'];
								
		
								//Back Leg 2
								
								$backFareInfoRef2 = $airPricePoint[1]['airOption']['airBookingInfo'][2]['@attributes']['FareInfoRef'];
								$backSegmentRef2 = $airPricePoint[1]['airOption']['airBookingInfo'][2]['@attributes']['SegmentRef'];

								$backBags2 = $airFareInfo[$backFareInfoRef2]['Bags'];
								$backFlightTime2 = $airAirSegment[$backSegmentRef2]['FlightTime'];
								$backFlightTimeHm2 = floor($backFlightTime2 / 60)."H ".($backFlightTime2 - ((floor($backFlightTime2 / 60)) * 60))."Min";


								$backArrivalTo2 = $airAirSegment[$backSegmentRef2]['Destination'];
								$backDepartureFrom2 = $airAirSegment[$backSegmentRef2]['Origin'];

								$backArrivalTime2 = $airAirSegment[$backSegmentRef2]['ArrivalTime'];
								$backDepartureTime2 = $airAirSegment[$backSegmentRef2]['DepartureTime'];

								$backfromTime2 = substr($backDepartureTime2,11, 19);
								$backdpTime2 = date("D d M Y", strtotime(substr($backDepartureTime2,0, 10)." ".$backfromTime2));

								$backtoTime2 = substr($backArrivalTime2,11, 19);
								$backarrTime2 = date("D d M Y", strtotime(substr($backArrivalTime2,0, 10)." ".$backtoTime2));

								
								$backmarkettingCarrier2 = $airAirSegment[$backSegmentRef2]['Carrier'];
								$backmarkettingFN2 = $airAirSegment[$backSegmentRef2]['FlightNumber'];

								$backsqlmk2 = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$backmarkettingCarrier2' ");
								$backrowmk2 = mysqli_fetch_array($backsqlmk2,MYSQLI_ASSOC);

								if(!empty($backrowmk2)){
									$backmarkettingCarrierName2 = $backrowmk2['name'];		
								}

								// Departure Country
								$backsqldp2 = mysqli_query($conn,"$Airportsql code='$backDepartureFrom2' ");
								$backrowdp2 = mysqli_fetch_array($backsqldp2,MYSQLI_ASSOC);

								if(!empty($backrowdp2)){
									$backdAirport2 = $backrowdp2['name'];
									$backdCity2 = $backrowdp2['cityName'];
									$backdCountry2 = $backrowdp2['countryCode'];		
								}

								// Departure Country
								$backsqlar2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$backArrivalTo2' ");
								$backrowar2 = mysqli_fetch_array($backsqlar2,MYSQLI_ASSOC);

								if(!empty($backrowar2)){
									$backaAirport2 = $backrowar2['name'];
									$backaCity2 = $backrowar2['cityName'];
									$backaCountry2 = $backrowar2['countryCode'];
								}
								
								
								$backBookingCode2 = $airPricePoint[1]['airOption']['airBookingInfo'][2]['@attributes']['BookingCode'];
								$backSeat2 = $airPricePoint[1]['airOption']['airBookingInfo'][2]['@attributes']['BookingCount'];
								$backCabinClass2 = $airPricePoint[1]['airOption']['airBookingInfo'][2]['@attributes']['CabinClass'];
								
								$goTravelTimeHm = $airAirSegment[$goSegmentRef]['TravelTime'];
								$goTravelTime = floor($goTravelTimeHm / 60)."H ".($goTravelTimeHm - ((floor($goTravelTimeHm / 60)) * 60))."Min";
								
								$backTravelTimeHm = $airAirSegment[$backSegmentRef]['TravelTime'];
								$backTravelTime = floor($backTravelTimeHm / 60)."H ".($backTravelTimeHm - ((floor($backTravelTimeHm / 60)) * 60))."Min";

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
																	"flightduration"=> "$backFlightTimeHm",
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
																	"flightduration"=> "$backFlightTimeHm1",
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
																	"flightduration"=> "$backFlightTimeHm2",
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
										"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
										"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
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

							}else if(isset($airPricePoint[0]['airOption']['airBookingInfo']['@attributes']['SegmentRef'])){
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
														"flightduration"=> "$goFlightTimeHm",
														"bookingcode"=> "$goBookingCode",
														"bags" => "$goBags",
														"seat" => "$goSeat",
														"class" => "$goCabinClass",
														"segmentDetails"=> $airAirSegment[$goSegmentRef]																										

														)																										

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
														"flightduration"=> "$backFlightTimeHm",
														"bookingcode"=> "$backBookingCode",
														"bags" => "$backBags",
														"seat" => "$backSeat",
														"class" => "$backCabinClass",
														"segmentDetails"=> $airAirSegment[$backSegmentRef])
													)
												);

						$basic = array("system" => "Galileo",
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
										"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
										"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
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
										"refundable"=> $Refundable,
										"segments" => $segment
									);
								
								array_push($All,$basic);
							
							}
						}
						if(isset($airPricePoint[0]['airOption']['airBookingInfo']['@attributes']) == TRUE 
									&& isset($airPricePoint[1]['airOption']['airBookingInfo']['@attributes']) == TRUE){
										
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
														"flightduration"=> "$goFlightTimeHm",
														"bookingcode"=> "$goBookingCode",
														"bags" => "$goBags",
														"seat" => "$goSeat",
														"class" => "$goCabinClass",
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
														"flightduration"=> "$backFlightTimeHm",
														"bookingcode"=> "$backBookingCode",
														"bags" => "$backBags",
														"seat" => "$backSeat",
														"class" => "$backCabinClass",
														"segmentDetails"=> $airAirSegment[$backSegmentRef])
													)
												);

						$basic = array("system" => "Galileo",
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
										"goFareBasisCode"=> $airFareInfo[$goFareInfoRef]['FareBasisCode'],
										"backFareBasisCode"=> $airFareInfo[$backFareInfoRef]['FareBasisCode'],
										"godeparture"=> "$From",                   
										"goDepartureTime" => $goDepartureTime,
										"godepartureDate" => $godpTime,
										"goarrival"=> "$To", 
										"goarrivalTime" => "$goArrivalTime",
										"goarrivalDate" => "$goarrTime",                
										"backdeparture"=> "$To",                   
										"backDepartureTime" => $backDepartureTime,
										"backdepartureDate" => $backdpTime,
										"backarrival"=> "$From", 
										"backarrivalTime" => "$backArrivalTime", 
										"backarrivalDate" => $backarrTime,  
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