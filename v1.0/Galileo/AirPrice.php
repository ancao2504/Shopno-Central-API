<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$_POST = json_decode(file_get_contents('php://input'), true); //print_r($_POST);

$tripType = $_POST['tripType'];
$adult = $_POST['adultCount'];
$child = $_POST['childCount'];
$infants = $_POST['infantCount'];
$segment = $_POST['segment'];



$Gallpax= array();

if($adult > 0 && $child> 0 && $infants> 0){
		
	for($i = 1; $i <= $adult ; $i++){
		$adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" BookingTravelerRef="zZUdreFl5WGc0akZmcFZNWQ=='.$i.'" Key="zZUdreFl5WGc0akZmcFZNWQ=='.$i.'" />';
		array_push($Gallpax, $adultcount);
	}
	for($i = 1; $i <= $child ; $i++){
		$childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="CNN" Age="10" DOB="2012-06-04" BookingTravelerRef="xkZUdreFl5WGc0akZmcFZNWQ=='.$i.'" Key="xkZUdreFl5WGc0akZmcFZNWQ=='.$i.'" />';
		array_push($Gallpax,$childcount);
	}
	for($i = 1; $i <= $infants ; $i++){
		$infantscount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="INF" Age="1" DOB="2021-06-04" BookingTravelerRef="xdZUdreFl5WGc0akZmcFZNWQ=='.$i.'" Key="xdZUdreFl5WGc0akZmcFZNWQ=='.$i.'" />';
		array_push($Gallpax, $infantscount);
	
	}
			
}else if($adult > 0 && $child > 0){

	for($i = 1; $i <= $adult ; $i++){
		$adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" BookingTravelerRef="zZUdreFl5WGc0akZmcFZNWQ=='.$i.'" Key="zZUdreFl5WGc0akZmcFZNWQ=='.$i.'" />';
		array_push($Gallpax, $adultcount);
	}
	for($i = 1; $i <= $child ; $i++){
		$childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="CNN" Age="10" DOB="2012-06-04" BookingTravelerRef="aZUdreFl5WGc0akZmcFZNWQ=='.$i.'" Key="aZUdreFl5WGc0akZmcFZNWQ=='.$i.'" />';
		array_push($Gallpax,$childcount);
	}
	
}else if($adult > 0 && $infants > 0){
	
	for($i = 1; $i <= $adult ; $i++){
		$adultcount ='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" BookingTravelerRef="ZUdreFl5WGc0akZmcFZNWQ=='.$i.'" Key="ZUdreFl5WGc0akZmcFZNWQ=='.$i.'" />';
		array_push($Gallpax, $adultcount);
	}
	for($i = 1; 1 <= $infants ; $i++){
		$infantscount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="INF" Age="1" DOB="2021-06-04" BookingTravelerRef="KZUdreFl5WGc0akZmcFZNWQ=='.$i.'" Key="KZUdreFl5WGc0akZmcFZNWQ=='.$i.'" />';
		array_push($Gallpax, $infantscount);
	
	}

}else{

	for($i = 1; $i <= $adult ; $i++){
		$adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" BookingTravelerRef="KZUdreFl5WGcu0akZmcFZNWQ=='.$i.'" Key="KZUdreFl5WGcu0akZmcFZNWQ=='.$i.'" />';
		array_push($Gallpax, $adultcount);
	}

}

$Passenger = implode(" ",$Gallpax);

if($tripType == 1){
   if($segment == 1){

	$AirSegmentKey = $_POST['segments'][0]['AirSegmentKey'];
	$Group = $_POST['segments'][0]['Group'];
	$Carrier = $_POST['segments'][0]['Carrier'];
	$FareBasisCode = $_POST['segments'][0]['FareBasisCode'];
	$FlightNumber = $_POST['segments'][0]['FlightNumber'];
	$Origin = $_POST['segments'][0]['Origin'];
	$Destination = $_POST['segments'][0]['Destination'];
	$DepartureTime = $_POST['segments'][0]['DepartureTime'];
	$ArrivalTime = $_POST['segments'][0]['ArrivalTime'];
	$BookingCode = $_POST['segments'][0]['BookingCode'];

   $AirSegment = <<<EOM
   <AirSegment Key="$AirSegmentKey" Group="$Group" Carrier="$Carrier" FlightNumber="$FlightNumber" Origin="$Origin" Destination="$Destination" DepartureTime="$DepartureTime" ArrivalTime="$ArrivalTime" ProviderCode="1G" />				
   EOM;

   $AirSegmentPricingModifiers = <<<EOM
               <AirSegmentPricingModifiers AirSegmentRef="$AirSegmentKey" FareBasisCode="$FareBasisCode">
						<PermittedBookingCodes>
							<BookingCode Code="$BookingCode" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>
EOM;

		
	
}else if($segment == 2){
	$AirSegmentKey = $_POST['segments'][0]['AirSegmentKey'];
	$Group = $_POST['segments'][0]['Group'];
	$Carrier = $_POST['segments'][0]['Carrier'];
	$FareBasisCode = $_POST['segments'][0]['FareBasisCode'];
	$FlightNumber = $_POST['segments'][0]['FlightNumber'];
	$Origin = $_POST['segments'][0]['Origin'];
	$Destination = $_POST['segments'][0]['Destination'];
	$DepartureTime = $_POST['segments'][0]['DepartureTime'];
	$ArrivalTime = $_POST['segments'][0]['ArrivalTime'];
	$BookingCode = $_POST['segments'][0]['BookingCode'];

	//Leg 2

	$AirSegmentKey1 = $_POST['segments'][1]['AirSegmentKey'];
	$Group1 = $_POST['segments'][1]['Group'];
	$Carrier1 = $_POST['segments'][1]['Carrier'];
	$FareBasisCode1 = $_POST['segments'][1]['FareBasisCode'];
	$FlightNumber1 = $_POST['segments'][1]['FlightNumber'];
	$Origin1 = $_POST['segments'][1]['Origin'];
	$Destination1 = $_POST['segments'][1]['Destination'];
	$DepartureTime1 = $_POST['segments'][1]['DepartureTime'];
	$ArrivalTime1 =$_POST['segments'][1]['ArrivalTime'];
	$BookingCode1 = $_POST['segments'][1]['BookingCode'];

	


	$AirSegment =<<<EOM
                  <AirSegment Key="$AirSegmentKey" Group="$Group" Carrier="$Carrier" FlightNumber="$FlightNumber" Origin="$Origin" Destination="$Destination" DepartureTime="$DepartureTime" ArrivalTime="$ArrivalTime" ProviderCode="1G" />
					   <AirSegment Key="$AirSegmentKey1" Group="$Group1" Carrier="$Carrier1" FlightNumber="$FlightNumber1" Origin="$Origin1" Destination="$Destination1" DepartureTime="$DepartureTime1" ArrivalTime="$ArrivalTime1" ProviderCode="1G" />					   
EOM;


         $AirSegmentPricingModifiers =<<<EOM
               <AirSegmentPricingModifiers AirSegmentRef="$AirSegmentKey" FareBasisCode="$FareBasisCode">
						<PermittedBookingCodes>
							<BookingCode Code="$BookingCode" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>
					<AirSegmentPricingModifiers AirSegmentRef="$AirSegmentKey1" FareBasisCode="$FareBasisCode1">
						<PermittedBookingCodes>
							<BookingCode Code="$BookingCode1" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>
					
EOM;


}else if($segment == 3 ){
	$AirSegmentKey = $_POST['segments'][0]['AirSegmentKey'];
	$Group = $_POST['segments'][0]['Group'];
	$Carrier = $_POST['segments'][0]['Carrier'];
	$FareBasisCode = $_POST['segments'][0]['FareBasisCode'];
	$FlightNumber = $_POST['segments'][0]['FlightNumber'];
	$Origin = $_POST['segments'][0]['Origin'];
	$Destination = $_POST['segments'][0]['Destination'];
	$DepartureTime = $_POST['segments'][0]['DepartureTime'];
	$ArrivalTime = $_POST['segments'][0]['ArrivalTime'];
	$BookingCode = $_POST['segments'][0]['BookingCode'];

	//Leg 2

	$AirSegmentKey1 = $_POST['segments'][1]['AirSegmentKey'];
	$Group1 = $_POST['segments'][1]['Group'];
	$Carrier1 = $_POST['segments'][1]['Carrier'];
	$FareBasisCode1 = $_POST['segments'][1]['FareBasisCode'];
	$FlightNumber1 = $_POST['segments'][1]['FlightNumber'];
	$Origin1 = $_POST['segments'][1]['Origin'];
	$Destination1 = $_POST['segments'][1]['Destination'];
	$DepartureTime1 = $_POST['segments'][1]['DepartureTime'];
	$ArrivalTime1 = $_POST['segments'][1]['ArrivalTime'];
	$BookingCode1 = $_POST['segments'][1]['BookingCode'];


	//Leg 3

	$AirSegmentKey2 = $_POST['segments'][2]['AirSegmentKey'];
	$Group2 = $_POST['segments'][2]['Group'];
	$Carrier2 = $_POST['segments'][2]['Carrier'];
	$FareBasisCode2 = $_POST['segments'][2]['FareBasisCode'];
	$FlightNumber2 = $_POST['segments'][2]['FlightNumber'];
	$Origin2 = $_POST['segments'][2]['Origin'];
	$Destination2 = $_POST['segments'][2]['Destination'];
	$DepartureTime2 =$_POST['segments'][2]['DepartureTime'];
	$ArrivalTime2 = $_POST['segments'][2]['ArrivalTime'];
	$BookingCode2 = $_POST['segments'][2]['BookingCode'];


   $AirSegment =<<<EOM
                  <AirSegment Key="$AirSegmentKey" Group="$Group" Carrier="$Carrier" FlightNumber="$FlightNumber" Origin="$Origin" Destination="$Destination" DepartureTime="$DepartureTime" ArrivalTime="$ArrivalTime" ProviderCode="1G" />
					   <AirSegment Key="$AirSegmentKey1" Group="$Group1" Carrier="$Carrier1" FlightNumber="$FlightNumber1" Origin="$Origin1" Destination="$Destination1" DepartureTime="$DepartureTime1" ArrivalTime="$ArrivalTime1" ProviderCode="1G" />
					   <AirSegment Key="$AirSegmentKey2" Group="$Group2" Carrier="$Carrier2" FlightNumber="$FlightNumber2" Origin="$Origin2" Destination="$Destination2" DepartureTime="$DepartureTime2" ArrivalTime="$ArrivalTime2" ProviderCode="1G" /> 
EOM;


         $AirSegmentPricingModifiers =<<<EOM
               <AirSegmentPricingModifiers AirSegmentRef="$AirSegmentKey" FareBasisCode="$FareBasisCode">
						<PermittedBookingCodes>
							<BookingCode Code="$BookingCode" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>
					<AirSegmentPricingModifiers AirSegmentRef="$AirSegmentKey1" FareBasisCode="$FareBasisCode1">
						<PermittedBookingCodes>
							<BookingCode Code="$BookingCode1" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>
					<AirSegmentPricingModifiers AirSegmentRef="$AirSegmentKey2" FareBasisCode="$FareBasisCode2">
						<PermittedBookingCodes>
							<BookingCode Code="$BookingCode2" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>	
EOM;

	
}
  
}else if($tripType == 2){
   if($segment == 1){

    //Go
	$goAirSegmentKey = $_POST['segments']['go'][0]['AirSegmentKey'];
	$goGroup = $_POST['segments']['go'][0]['Group'];
	$goCarrier = $_POST['segments']['go'][0]['Carrier'];
	$goFareBasisCode = $_POST['segments']['go'][0]['FareBasisCode'];
	$goFlightNumber = $_POST['segments']['go'][0]['FlightNumber'];
	$goOrigin = $_POST['segments']['go'][0]['Origin'];
	$goDestination = $_POST['segments']['go'][0]['Destination'];
	$goDepartureTime = $_POST['segments']['go'][0]['DepartureTime'];
	$goArrivalTime = $_POST['segments']['go'][0]['ArrivalTime'];
	$goBookingCode = $_POST['segments']['go'][0]['BookingCode'];


    //Back

   $backAirSegmentKey = $_POST['segments']['back'][0]['AirSegmentKey'];
	$backGroup = $_POST['segments']['back'][0]['Group'];
	$backCarrier = $_POST['segments']['back'][0]['Carrier'];
	$backFareBasisCode = $_POST['segments']['back'][0]['FareBasisCode'];
	$backFlightNumber = $_POST['segments']['back'][0]['FlightNumber'];
	$backOrigin = $_POST['segments']['back'][0]['Origin'];
	$backDestination = $_POST['segments']['back'][0]['Destination'];
	$backDepartureTime = $_POST['segments']['back'][0]['DepartureTime'];
	$backArrivalTime = $_POST['segments']['back'][0]['ArrivalTime'];
	$backBookingCode = $_POST['segments']['back'][0]['BookingCode'];


   $AirSegment = <<<EOM
   <AirSegment Key="$goAirSegmentKey" Group="$goGroup" Carrier="$goCarrier" FlightNumber="$goFlightNumber" Origin="$goOrigin" Destination="$goDestination" DepartureTime="$goDepartureTime" ArrivalTime="$goArrivalTime" ProviderCode="1G" />
                    <AirSegment Key="$backAirSegmentKey" Group="$backGroup" Carrier="$backCarrier" FlightNumber="$backFlightNumber" Origin="$backOrigin" Destination="$backDestination" DepartureTime="$backDepartureTime" ArrivalTime="$backArrivalTime" ProviderCode="1G" />					
   EOM;


   $AirSegmentPricingModifiers =<<<EOM
               <AirSegmentPricingModifiers AirSegmentRef="$goAirSegmentKey" FareBasisCode="$goFareBasisCode">
						<PermittedBookingCodes>
							<BookingCode Code="$goBookingCode" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>
                    <AirSegmentPricingModifiers AirSegmentRef="$backAirSegmentKey" FareBasisCode="$backFareBasisCode">
						<PermittedBookingCodes>
							<BookingCode Code="$backBookingCode" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>	
EOM;

	
	$message = <<<EOM
	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
    <soapenv:Header/>
		<soapenv:Body>
			<AirPriceReq xmlns="http://www.travelport.com/schema/air_v42_0" TraceId="FFI-KayesFahim" AuthorizedBy="Travelport" TargetBranch="P7182044">
				<BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v42_0" OriginApplication="uAPI" />
				<AirItinerary>
					<AirSegment Key="$goAirSegmentKey" Group="$goGroup" Carrier="$goCarrier" FlightNumber="$goFlightNumber" Origin="$goOrigin" Destination="$goDestination" DepartureTime="$goDepartureTime" ArrivalTime="$goArrivalTime" ProviderCode="1G" />
                    <AirSegment Key="$backAirSegmentKey" Group="$backGroup" Carrier="$backCarrier" FlightNumber="$backFlightNumber" Origin="$backOrigin" Destination="$backDestination" DepartureTime="$backDepartureTime" ArrivalTime="$backArrivalTime" ProviderCode="1G" />					
				</AirItinerary>
				<AirPricingModifiers InventoryRequestType="DirectAccess">
					<BrandModifiers ModifierType="FareFamilyDisplay" />
				</AirPricingModifiers>
					$Passenger
				<AirPricingCommand>
					<AirSegmentPricingModifiers AirSegmentRef="$goAirSegmentKey" FareBasisCode="$goFareBasisCode">
						<PermittedBookingCodes>
							<BookingCode Code="$goBookingCode" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>
                    <AirSegmentPricingModifiers AirSegmentRef="$backAirSegmentKey" FareBasisCode="$backFareBasisCode">
						<PermittedBookingCodes>
							<BookingCode Code="$backBookingCode" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>				
				</AirPricingCommand>
				<FormOfPayment xmlns="http://www.travelport.com/schema/common_v42_0" Type="Credit" />
			</AirPriceReq>
		</soapenv:Body>
	</soapenv:Envelope> 
EOM;
	
	
}else if($segment == 2){
	
    //Go1
	$goAirSegmentKey = $_POST['segments']['go'][0]['AirSegmentKey'];
	$goGroup = $_POST['segments']['go'][0]['Group'];
	$goCarrier = $_POST['segments']['go'][0]['Carrier'];
	$goFareBasisCode = $_POST['segments']['go'][0]['FareBasisCode'];
	$goFlightNumber = $_POST['segments']['go'][0]['FlightNumber'];
	$goOrigin = $_POST['segments']['go'][0]['Origin'];
	$goDestination = $_POST['segments']['go'][0]['Destination'];
	$goDepartureTime = $_POST['segments']['go'][0]['DepartureTime'];
	$goArrivalTime = $_POST['segments']['go'][0]['ArrivalTime'];
	$goBookingCode = $_POST['segments']['go'][0]['BookingCode'];

    //Go2
	$goAirSegmentKey1 = $_POST['segments']['go'][1]['AirSegmentKey'];
	$goGroup1 = $_POST['segments']['go'][1]['Group'];
	$goCarrier1 = $_POST['segments']['go'][1]['Carrier'];
	$goFareBasisCode1 = $_POST['segments']['go'][1]['FareBasisCode'];
	$goFlightNumber1 = $_POST['segments']['go'][1]['FlightNumber'];
	$goOrigin1 = $_POST['segments']['go'][1]['Origin'];
	$goDestination1 = $_POST['segments']['go'][1]['Destination'];
	$goDepartureTime1 = $_POST['segments']['go'][1]['DepartureTime'];
	$goArrivalTime1 = $_POST['segments']['go'][1]['ArrivalTime'];
	$goBookingCode1 = $_POST['segments']['go'][1]['BookingCode'];


    //Back

    $backAirSegmentKey = $_POST['segments']['back'][0]['AirSegmentKey'];
	$backGroup = $_POST['segments']['back'][0]['Group'];
	$backCarrier = $_POST['segments']['back'][0]['Carrier'];
	$backFareBasisCode = $_POST['segments']['back'][0]['FareBasisCode'];
	$backFlightNumber = $_POST['segments']['back'][0]['FlightNumber'];
	$backOrigin = $_POST['segments']['back'][0]['Origin'];
	$backDestination = $_POST['segments']['back'][0]['Destination'];
	$backDepartureTime = $_POST['segments']['back'][0]['DepartureTime'];
	$backArrivalTime = $_POST['segments']['back'][0]['ArrivalTime'];
	$backBookingCode = $_POST['segments']['back'][0]['BookingCode'];

    //Back Leg 2
    $backAirSegmentKey1 = $_POST['segments']['back'][1]['AirSegmentKey'];
	$backGroup1 = $_POST['segments']['back'][1]['Group'];
	$backCarrier1 = $_POST['segments']['back'][1]['Carrier'];
	$backFareBasisCode1 = $_POST['segments']['back'][1]['FareBasisCode'];
	$backFlightNumber1 = $_POST['segments']['back'][1]['FlightNumber'];
	$backOrigin1 = $_POST['segments']['back'][1]['Origin'];
	$backDestination1 = $_POST['segments']['back'][1]['Destination'];
	$backDepartureTime1 = $_POST['segments']['back'][1]['DepartureTime'];
	$backArrivalTime1 = $_POST['segments']['back'][1]['ArrivalTime'];
	$backBookingCode1 = $_POST['segments']['back'][1]['BookingCode'];



   $AirSegment = <<<EOM
      <AirSegment Key="$goAirSegmentKey" Group="$goGroup" Carrier="$goCarrier" FlightNumber="$goFlightNumber" Origin="$goOrigin" Destination="$goDestination" DepartureTime="$goDepartureTime" ArrivalTime="$goArrivalTime" ProviderCode="1G" />
                    <AirSegment Key="$goAirSegmentKey1" Group="$goGroup1" Carrier="$goCarrier1" FlightNumber="$goFlightNumber1" Origin="$goOrigin1" Destination="$goDestination1" DepartureTime="$goDepartureTime1" ArrivalTime="$goArrivalTime1" ProviderCode="1G" />
                    <AirSegment Key="$backAirSegmentKey" Group="$backGroup" Carrier="$backCarrier" FlightNumber="$backFlightNumber" Origin="$backOrigin" Destination="$backDestination" DepartureTime="$backDepartureTime" ArrivalTime="$backArrivalTime" ProviderCode="1G" />
                    <AirSegment Key="$backAirSegmentKey1" Group="$backGroup1" Carrier="$backCarrier1" FlightNumber="$backFlightNumber1" Origin="$backOrigin1" Destination="$backDestination1" DepartureTime="$backDepartureTime1" ArrivalTime="$backArrivalTime1" ProviderCode="1G" />	 
   EOM;


   $AirSegmentPricingModifiers=<<<EOM
               <AirSegmentPricingModifiers AirSegmentRef="$goAirSegmentKey" FareBasisCode="$goFareBasisCode">
						<PermittedBookingCodes>
							<BookingCode Code="$goBookingCode" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>
                    <AirSegmentPricingModifiers AirSegmentRef="$goAirSegmentKey1" FareBasisCode="$goFareBasisCode1">
						<PermittedBookingCodes>
							<BookingCode Code="$goBookingCode1" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>
					<AirSegmentPricingModifiers AirSegmentRef="$backAirSegmentKey" FareBasisCode="$backFareBasisCode">
						<PermittedBookingCodes>
							<BookingCode Code="$backBookingCode" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>
                    <AirSegmentPricingModifiers AirSegmentRef="$backAirSegmentKey1" FareBasisCode="$backFareBasisCode1">
						<PermittedBookingCodes>
							<BookingCode Code="$backBookingCode1" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>
EOM;


   

}else if($segment == 12 ){
	//Go1
	$goAirSegmentKey = $_POST['segments']['go'][0]['AirSegmentKey'];
	$goGroup = $_POST['segments']['go'][0]['Group'];
	$goCarrier = $_POST['segments']['go'][0]['Carrier'];
	$goFareBasisCode = $_POST['segments']['go'][0]['FareBasisCode'];
	$goFlightNumber = $_POST['segments']['go'][0]['FlightNumber'];
	$goOrigin = $_POST['segments']['go'][0]['Origin'];
	$goDestination = $_POST['segments']['go'][0]['Destination'];
	$goDepartureTime = $_POST['segments']['go'][0]['DepartureTime'];
	$goArrivalTime = $_POST['segments']['go'][0]['ArrivalTime'];
	$goBookingCode = $_POST['segments']['go'][0]['BookingCode'];



    //Back

    $backAirSegmentKey = $_POST['segments']['back'][0]['AirSegmentKey'];
	$backGroup = $_POST['segments']['back'][0]['Group'];
	$backCarrier = $_POST['segments']['back'][0]['Carrier'];
	$backFareBasisCode = $_POST['segments']['back'][0]['FareBasisCode'];
	$backFlightNumber = $_POST['segments']['back'][0]['FlightNumber'];
	$backOrigin = $_POST['segments']['back'][0]['Origin'];
	$backDestination = $_POST['segments']['back'][0]['Destination'];
	$backDepartureTime = $_POST['segments']['back'][0]['DepartureTime'];
	$backArrivalTime = $_POST['segments']['back'][0]['ArrivalTime'];
	$backBookingCode = $_POST['segments']['back'][0]['BookingCode'];

    //Back Leg 2
    $backAirSegmentKey1 = $_POST['segments']['back'][1]['AirSegmentKey'];
	$backGroup1 = $_POST['segments']['back'][1]['Group'];
	$backCarrier1 = $_POST['segments']['back'][1]['Carrier'];
	$backFareBasisCode1 = $_POST['segments']['back'][1]['FareBasisCode'];
	$backFlightNumber1 = $_POST['segments']['back'][1]['FlightNumber'];
	$backOrigin1 = $_POST['segments']['back'][1]['Origin'];
	$backDestination1 = $_POST['segments']['back'][1]['Destination'];
	$backDepartureTime1 = $_POST['segments']['back'][1]['DepartureTime'];
	$backArrivalTime1 = $_POST['segments']['back'][1]['ArrivalTime'];
	$backBookingCode1 = $_POST['segments']['back'][1]['BookingCode'];



   $AirSegment = <<<EOM
      <AirSegment Key="$goAirSegmentKey" Group="$goGroup" Carrier="$goCarrier" FlightNumber="$goFlightNumber" Origin="$goOrigin" Destination="$goDestination" DepartureTime="$goDepartureTime" ArrivalTime="$goArrivalTime" ProviderCode="1G" />
                    <AirSegment Key="$backAirSegmentKey" Group="$backGroup" Carrier="$backCarrier" FlightNumber="$backFlightNumber" Origin="$backOrigin" Destination="$backDestination" DepartureTime="$backDepartureTime" ArrivalTime="$backArrivalTime" ProviderCode="1G" />
                    <AirSegment Key="$backAirSegmentKey1" Group="$backGroup1" Carrier="$backCarrier1" FlightNumber="$backFlightNumber1" Origin="$backOrigin1" Destination="$backDestination1" DepartureTime="$backDepartureTime1" ArrivalTime="$backArrivalTime1" ProviderCode="1G" />	 
   EOM;


   $AirSegmentPricingModifiers=<<<EOM
               <AirSegmentPricingModifiers AirSegmentRef="$goAirSegmentKey" FareBasisCode="$goFareBasisCode">
						<PermittedBookingCodes>
							<BookingCode Code="$goBookingCode" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>
					<AirSegmentPricingModifiers AirSegmentRef="$backAirSegmentKey" FareBasisCode="$backFareBasisCode">
						<PermittedBookingCodes>
							<BookingCode Code="$backBookingCode" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>
                    <AirSegmentPricingModifiers AirSegmentRef="$backAirSegmentKey1" FareBasisCode="$backFareBasisCode1">
						<PermittedBookingCodes>
							<BookingCode Code="$backBookingCode1" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>
EOM;

	
	
}else if($segments == 21){
      //Go1
	$goAirSegmentKey = $_POST['segments']['go'][0]['AirSegmentKey'];
	$goGroup = $_POST['segments']['go'][0]['Group'];
	$goCarrier = $_POST['segments']['go'][0]['Carrier'];
	$goFareBasisCode = $_POST['segments']['go'][0]['FareBasisCode'];
	$goFlightNumber = $_POST['segments']['go'][0]['FlightNumber'];
	$goOrigin = $_POST['segments']['go'][0]['Origin'];
	$goDestination = $_POST['segments']['go'][0]['Destination'];
	$goDepartureTime = $_POST['segments']['go'][0]['DepartureTime'];
	$goArrivalTime = $_POST['segments']['go'][0]['ArrivalTime'];
	$goBookingCode = $_POST['segments']['go'][0]['BookingCode'];

    //Go2
	$goAirSegmentKey1 = $_POST['segments']['go'][1]['AirSegmentKey'];
	$goGroup1 = $_POST['segments']['go'][1]['Group'];
	$goCarrier1 = $_POST['segments']['go'][1]['Carrier'];
	$goFareBasisCode1 = $_POST['segments']['go'][1]['FareBasisCode'];
	$goFlightNumber1 = $_POST['segments']['go'][1]['FlightNumber'];
	$goOrigin1 = $_POST['segments']['go'][1]['Origin'];
	$goDestination1 = $_POST['segments']['go'][1]['Destination'];
	$goDepartureTime1 = $_POST['segments']['go'][1]['DepartureTime'];
	$goArrivalTime1 = $_POST['segments']['go'][1]['ArrivalTime'];
	$goBookingCode1 = $_POST['segments']['go'][1]['BookingCode'];


    //Back

    $backAirSegmentKey = $_POST['segments']['back'][0]['AirSegmentKey'];
	$backGroup = $_POST['segments']['back'][0]['Group'];
	$backCarrier = $_POST['segments']['back'][0]['Carrier'];
	$backFareBasisCode = $_POST['segments']['back'][0]['FareBasisCode'];
	$backFlightNumber = $_POST['segments']['back'][0]['FlightNumber'];
	$backOrigin = $_POST['segments']['back'][0]['Origin'];
	$backDestination = $_POST['segments']['back'][0]['Destination'];
	$backDepartureTime = $_POST['segments']['back'][0]['DepartureTime'];
	$backArrivalTime = $_POST['segments']['back'][0]['ArrivalTime'];
	$backBookingCode = $_POST['segments']['back'][0]['BookingCode'];


   $AirSegment = <<<EOM
      <AirSegment Key="$goAirSegmentKey" Group="$goGroup" Carrier="$goCarrier" FlightNumber="$goFlightNumber" Origin="$goOrigin" Destination="$goDestination" DepartureTime="$goDepartureTime" ArrivalTime="$goArrivalTime" ProviderCode="1G" />
                    <AirSegment Key="$goAirSegmentKey1" Group="$goGroup1" Carrier="$goCarrier1" FlightNumber="$goFlightNumber1" Origin="$goOrigin1" Destination="$goDestination1" DepartureTime="$goDepartureTime1" ArrivalTime="$goArrivalTime1" ProviderCode="1G" />
                    <AirSegment Key="$backAirSegmentKey" Group="$backGroup" Carrier="$backCarrier" FlightNumber="$backFlightNumber" Origin="$backOrigin" Destination="$backDestination" DepartureTime="$backDepartureTime" ArrivalTime="$backArrivalTime" ProviderCode="1G" />
                   
   EOM;


   $AirSegmentPricingModifiers=<<<EOM
               <AirSegmentPricingModifiers AirSegmentRef="$goAirSegmentKey" FareBasisCode="$goFareBasisCode">
						<PermittedBookingCodes>
							<BookingCode Code="$goBookingCode" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>
                    <AirSegmentPricingModifiers AirSegmentRef="$goAirSegmentKey1" FareBasisCode="$goFareBasisCode1">
						<PermittedBookingCodes>
							<BookingCode Code="$goBookingCode1" />
						</PermittedBookingCodes>
					</AirSegmentPricingModifiers>
					<AirSegmentPricingModifiers AirSegmentRef="$backAirSegmentKey" FareBasisCode="$backFareBasisCode">
						<PermittedBookingCodes>
							<BookingCode Code="$backBookingCode" />
						</PermittedBookingCodes>
					
EOM;


    
}
   
 
}



$message = <<<EOM
	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
    <soapenv:Header/>
		<soapenv:Body>
			<AirPriceReq xmlns="http://www.travelport.com/schema/air_v42_0" TraceId="FFI-KayesFahim" AuthorizedBy="Travelport" TargetBranch="P7182044">
				<BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v42_0" OriginApplication="uAPI" />
				<AirItinerary>
					$AirSegment			
				</AirItinerary>
				<AirPricingModifiers InventoryRequestType="DirectAccess">
					<BrandModifiers ModifierType="FareFamilyDisplay" />
				</AirPricingModifiers>
					$Passenger
				<AirPricingCommand>
					$AirSegmentPricingModifiers			
				</AirPricingCommand>
				<FormOfPayment xmlns="http://www.travelport.com/schema/common_v42_0" Type="Credit" />
			</AirPriceReq>
		</soapenv:Body>
	</soapenv:Envelope> 
EOM;


//print_r($message);


    //Cert
    // $TARGETBRANCH = 'P7182044';
	//$CREDENTIALS = 'Universal API/uAPI5270664478-0c51bde6:2Td*m/F3M5'; 

	//Prod
	$TARGETBRANCH = 'P4218912';
	$CREDENTIALS = 'Universal API/uAPI4444837655-83fe5101:K/s3-5Sy4c';
	

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

    //print_r($xml);

    
	//print_r($xml);
	if(isset($xml->xpath('//airAirPriceRsp')[0])){
		$body = $xml->xpath('//airAirPriceRsp')[0];
		
	$result = json_decode(json_encode((array)$body), TRUE);

    //print_r($result);

    function recursive_change_key($arr, $set) {
        if (is_array($arr) && is_array($set)) {
    		$newArr = array();
    		foreach ($arr as $k => $v) {
    		    $key = array_key_exists( $k, $set) ? $set[$k] : $k;
    		    $newArr[$key] = is_array($v) ? recursive_change_key($v, $set) : $v;
    		}
    		return $newArr;
    	}
    	return $arr;    
    }


        //print_r($result);

        $NewArrray = recursive_change_key($result, array('@attributes' => 'attributes'));  
        $json_string = json_encode($NewArrray, JSON_PRETTY_PRINT);
            
            echo $json_string;
        
   }

         
?>