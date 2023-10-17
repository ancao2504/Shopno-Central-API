<?php

include "../AirSearch/Token.php";
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'POST'){
$_POST = json_decode(file_get_contents('php://input'), true);

$tripType = isset($_POST['tripType']) ? $_POST['tripType'] :'';
$Segment = isset($_POST['segment']) ? $_POST['segment'] : '';

    if($_POST['System'] == 'Sabre'){
        $SabreRequest = SabreAirPriceRequest();
        SabreAirPrice($SabreToken , $SabreRequest);
    }else if($_POST['System'] == 'Galileo'){
        
        $GalileoRequest = GalileoAirPriceRequest();
        $GalileoData = GalileoAirPrice($GalileoRequest);
        echo json_encode($GalileoData);
    }

        
}


function SabreAirPriceRequest(){

    $AdultCount = $_POST['PriceBreakDown'][0]['PaxCount'];
    $ChildCount = isset($_POST['PriceBreakDown'][1]['PaxCount']) ? $_POST['PriceBreakDown'][1]['PaxCount'] : 0;
    $InfantCount = isset($_POST['PriceBreakDown'][2]['PaxCount']) ? $_POST['PriceBreakDown'][2]['PaxCount'] : 0;

    $SabreRequestPax = array();
	if($AdultCount > 0){
		$PaxQualtity = array(
							"Code"=> "ADT",
							"Quantity"=> $AdultCount
						);
		array_push($SabreRequestPax, $PaxQualtity);
	}if($ChildCount > 0){
		$PaxQualtity = array(
							"Code"=> "C04",
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

    $AllLegs = $_POST['AllLegs'];
    $SeatReq = $AdultCount + $ChildCount;
    $RequestArray = array();
    $i =0;
    foreach($AllLegs as $legs){
        foreach ($legs['Segments'] as $segments) {
        $i++;
        $MarketingCarrier =  $segments['MarketingCarrier'];
        $MarketingFlightNumber =  $segments['MarketingFlightNumber'];
        $OperatingCarrier =  $segments['OperatingCarrier'];
        $DepFrom =  $segments['DepFrom'];
        $ArrTo =  $segments['ArrTo'];
        $DepTime =  substr($segments['DepTime'],0,19);
        $ArrTime =  substr($segments['ArrTime'],0,19);
        $BookingCode =  $segments['SegmentCode']['bookingCode'];


        $MultiRequest =
            array(
                "RPH" => "$i",
                "DepartureDateTime" => $DepTime,
                "OriginLocation" => array(
                    "LocationCode" => $DepFrom
                ),
                "DestinationLocation" => array(
                    "LocationCode" => $ArrTo,
                ),
                "TPA_Extensions" => array(
                    "SegmentType" => array(
                        "Code" => "O"
                    ),
                    "Flight" => array(
                        array(
                            "Number" => $MarketingFlightNumber,
                            "DepartureDateTime" => $DepTime,
                            "ArrivalDateTime" => $ArrTime,
                            "Type" => "A",
                            "ClassOfService" => $BookingCode,
                            "OriginLocation" => array(
                                "LocationCode" => $DepFrom
                            ),
                            "DestinationLocation" => array(
                                "LocationCode" => $ArrTo
                            ),
                            "Airline" => array(
                                "Operating" => $OperatingCarrier,
                                "Marketing" => $MarketingCarrier
                            )
                        )
                    )
                )
            );

        array_push($RequestArray, $MultiRequest);

        }
    }


    $SabreRequest ='{
            "OTA_AirLowFareSearchRQ": {
            "Version": "4",
            "TravelPreferences": {
                "TPA_Extensions": {
                    "VerificationItinCallLogic": {
                        "Value": "B"
                    }
                }
            },
            "TravelerInfoSummary": {
                "SeatsRequested": [
                        '.$SeatReq.'
                ],
                "AirTravelerAvail": [
                    {
                        "PassengerTypeQuantity": '.json_encode($SabreRequestPax).'
                    }
                ]
            },
            "POS": {
                "Source": [
                    {
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
            "OriginDestinationInformation":'.json_encode($RequestArray).',
            "TPA_Extensions": {
                "IntelliSellTransaction": {
                    "RequestType": {
                        "Name": "50ITINS"
                    }
                }
            }
        }
    }';

    return $SabreRequest;
    
}

function GalileoAirPriceRequest(){

    $adult = isset($_POST['PriceBreakDown'][0]['PaxCount']) ? $_POST['PriceBreakDown'][0]['PaxCount'] : 1;
    $child = isset($_POST['PriceBreakDown'][1]['PaxCount']) ? $_POST['PriceBreakDown'][1]['PaxCount'] : 0;
    $infants = isset($_POST['PriceBreakDown'][2]['PaxCount']) ? $_POST['PriceBreakDown'][2]['PaxCount'] : 0;
    $Plattingcarrier = isset($_POST['Carrier']) ? $_POST['Carrier'] :'BG';

    if($Plattingcarrier == '6E'){
        $PCode = 'ACH';
    }else{
         $PCode = '1G';
    }
    
    $Gallpax= array();

    if($adult > 0 && $child> 0 && $infants> 0){
            
        for($i = 1; $i <= $adult ; $i++){
            $adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v50_0" Code="ADT" BookingTravelerRef="ABfRef'.$i.'" Key="ABfKey'.$i.'" />';
            array_push($Gallpax, $adultcount);
        }
        for($i = 1; $i <= $child ; $i++){
            $childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v50_0" Code="CNN" Age="07" DOB="2012-06-04" BookingTravelerRef="CBfRef'.$i.'" Key="CBfKey'.$i.'" />';
            array_push($Gallpax,$childcount);
        }
        for($i = 1; $i <= $infants ; $i++){
            $infantscount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v50_0" Code="INF" Age="1" DOB="2021-06-04" BookingTravelerRef="IBfRef'.$i.'" Key="IBfKey'.$i.'" />';
            array_push($Gallpax, $infantscount);
        
        }
                
    }else if($adult > 0 && $child > 0){

        for($i = 1; $i <= $adult ; $i++){
            $adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v50_0" Code="ADT" BookingTravelerRef="ABRef'.$i.'" Key="ABKey'.$i.'" />';
            array_push($Gallpax, $adultcount);
        }
        for($i = 1; $i <= $child ; $i++){
            $childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v50_0" Code="CNN" Age="10" DOB="2012-06-04" BookingTravelerRef="CBRef'.$i.'" Key="CBKey'.$i.'" />';
            array_push($Gallpax,$childcount);
        }
        
    }else if($adult > 0 && $infants > 0){
        
        for($i = 1; $i <= $adult ; $i++){
            $adultcount ='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v50_0" Code="ADT" BookingTravelerRef="ABRef'.$i.'" Key="ABKey'.$i.'" />';
            array_push($Gallpax, $adultcount);
        }
        for($i = 1; 1 <= $infants ; $i++){
            $infantscount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v50_0" Code="INF" Age="1" DOB="2022-06-04" BookingTravelerRef="IBKey'.$i.'" Key="IBKey'.$i.'" />';
            array_push($Gallpax, $infantscount);
        
        }

    }else{

        for($i = 1; $i <= $adult ; $i++){
            $adultcount='<SearchPassenger xmlns="http://www.travelport.com/schema/common_v50_0" Code="ADT" BookingTravelerRef="ABRef'.$i.'" Key="ABKey'.$i.'" />';
            array_push($Gallpax, $adultcount);
        }

    }

    $AllAirSegment = array();
    $AllAirSegmentPricingModifiers = array();
    foreach($_POST['AllLegs'] as $alllegs){
        
        $AllSegments = $alllegs['Segments'];
        foreach($AllSegments as $segments){
            $AirSegmentKey = $segments['SegmentCode']['SegmentRef'];
            $Group =  $segments['Group'];
            $Carrier =  $segments['MarketingCarrier'];
            $FareBasisCode =  $segments['FareBasis'];
            $FlightNumber =  $segments['MarketingFlightNumber'];
            $Origin =  $segments['DepFrom'];
            $Destination =  $segments['ArrTo'];
            $DepartureTime =  $segments['DepTime'];
            $ArrivalTime =  $segments['ArrTime'];
            $BookingCode =  $segments['SegmentCode']['BookingCode'];

            $AirSegment = <<<EOM
            <AirSegment Key="$AirSegmentKey" Group="$Group" Carrier="$Carrier" FlightNumber="$FlightNumber" Origin="$Origin" Destination="$Destination" DepartureTime="$DepartureTime" ArrivalTime="$ArrivalTime" ProviderCode="$PCode" />				
            EOM;

            array_push($AllAirSegment, $AirSegment);

            $AirSegmentPricingModifiers = <<<EOM
                                <AirSegmentPricingModifiers AirSegmentRef="$AirSegmentKey" FareBasisCode="$FareBasisCode">
                                    <PermittedBookingCodes>
                                        <BookingCode Code="$BookingCode" />
                                    </PermittedBookingCodes>
                                </AirSegmentPricingModifiers>
            EOM;

           array_push($AllAirSegmentPricingModifiers, $AirSegmentPricingModifiers);
            
        }
        
    }
    
    $AirSegment  = implode(" ",$AllAirSegment);
    $PassengerData = implode(" ",$Gallpax);
    $AirSegmentPricingModifiers	= implode(" ",$AllAirSegmentPricingModifiers);

    $GalileoRequest = <<<EOM
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
        <soapenv:Header/>
            <soapenv:Body>
                <AirPriceReq xmlns="http://www.travelport.com/schema/air_v50_0" TraceId="FFI-KayesFahim" AuthorizedBy="Travelport" TargetBranch="P4218912">
                    <BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v50_0" OriginApplication="uAPI" />
                    <AirItinerary>
                        $AirSegment			
                    </AirItinerary>
                    <AirPricingModifiers InventoryRequestType="DirectAccess" PlatingCarrier="$Plattingcarrier" ETicketability="Required" FaresIndicator="PublicAndPrivateFares">
                        <BrandModifiers ModifierType="FareFamilyDisplay" />
                    </AirPricingModifiers>
                        $PassengerData
                    <AirPricingCommand>
                        $AirSegmentPricingModifiers			
                    </AirPricingCommand>
                </AirPriceReq>
            </soapenv:Body>
        </soapenv:Envelope> 
    EOM;

  return $GalileoRequest;

    
}
function SabreAirPrice($SabreToken, $SabreRequest){

    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.platform.sabre.com/v4/shop/flights/revalidate', //Live
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
        'Conversation-ID: {{conv_id}}',
        'Authorization: Bearer '.$SabreToken,
        ),
    ));

    $SabreResponseData = curl_exec($curl);
    curl_close($curl);
    echo $SabreResponseData;
    
}

function GalileoAirPrice($GalileoRequest){

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
    
    $GalileoResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $GalileoResponse);
	$xml = new SimpleXMLElement($GalileoResult);
	if(isset($xml->xpath('//airAirPriceRsp')[0])){
		$body = $xml->xpath('//airAirPriceRsp')[0];
		
        $GalileoResultData = json_decode(json_encode((array)$body), TRUE);
        $NewArrray = recursive_change_key($GalileoResultData, array('@attributes' => 'attributes'));  
            
        return $NewArrray;
        
   }

   
}

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


?>