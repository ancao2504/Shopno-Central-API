<?php

include "../config.php";
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$All= array();


$Airportsql =  "SELECT name, cityName,countryCode FROM airports WHERE";


if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $_POST = json_decode(file_get_contents('php://input'), true);

    $adult = $_POST['adultCount'];
    $child = $_POST['childCount'];
    $infants = $_POST['infantCount'];


    // 
    $CityCount = $_POST['CityCount'];
    
    if($CityCount== '1'){
        print("You have to Chose More Than 2 City");
		exit();
        
    }else if($CityCount== '2'){
		$City1DepDate = $_POST['segments'][0]['Date'];
		$City2DepDate = $_POST['segments'][1]['Date'];
		
		$City1DepFrom = $_POST['segments'][0]['DepFrom'];
		$City2DepFrom = $_POST['segments'][1]['DepFrom'];
		
		$City1ArrTo = $_POST['segments'][0]['ArrTo'];
		$City2ArrTo = $_POST['segments'][1]['ArrTo'];
		
		
        $FlyHubSegment ='[{
                            "Origin": "'.$City1DepFrom.'",
                            "Destination": "'.$City1ArrTo.'",
                            "CabinClass": "Economy",
                            "DepartureDateTime": "'.$City1DepDate.'"
                        },
                        {
                            "Origin": "'.$City2DepFrom.'",
                            "Destination": "'.$City2ArrTo.'",
                            "CabinClass": "Economy",
                            "DepartureDateTime": "'.$City2DepDate.'"
                        }]';
        
    }else if($CityCount== '3'){
		$City1DepDate = $_POST['segments'][0]['Date'];
		$City2DepDate = $_POST['segments'][1]['Date'];
		$City3DepDate = $_POST['segments'][2]['Date'];
		
		$City1DepFrom = $_POST['segments'][0]['DepFrom'];
		$City2DepFrom = $_POST['segments'][1]['DepFrom'];
		$City3DepFrom = $_POST['segments'][2]['DepFrom'];
		
		$City1ArrTo = $_POST['segments'][0]['ArrTo'];
		$City2ArrTo = $_POST['segments'][1]['ArrTo'];
		$City3ArrTo = $_POST['segments'][2]['ArrTo'];
		       

        $FlyHubSegment ='[{
                            "Origin": "'.$City1DepFrom.'",
                            "Destination": "'.$City1ArrTo.'",
                            "CabinClass": "Economy",
                            "DepartureDateTime": "'.$City1DepDate.'"
                        },
                        {
                            "Origin": "'.$City2DepFrom.'",
                            "Destination": "'.$City2ArrTo.'",
                            "CabinClass": "Economy",
                            "DepartureDateTime": "'.$City2DepDate.'"
                        },
                        {
                            "Origin": "'.$City3DepFrom.'",
                            "Destination": "'.$City3ArrTo.'",
                            "CabinClass": "Economy",
                            "DepartureDateTime": "'.$City3DepDate.'"
                        }]';
        
    }else if($CityCount== '4'){
		$City1DepDate = $_POST['segments'][0]['Date'];
		$City2DepDate = $_POST['segments'][1]['Date'];
		$City3DepDate = $_POST['segments'][2]['Date'];
		$City4DepDate = $_POST['segments'][3]['Date'];
		
		$City1DepFrom = $_POST['segments'][0]['DepFrom'];
		$City2DepFrom = $_POST['segments'][1]['DepFrom'];
		$City3DepFrom = $_POST['segments'][2]['DepFrom'];
		$City4DepFrom = $_POST['segments'][3]['DepFrom'];
		
		$City1ArrTo = $_POST['segments'][0]['ArrTo'];
		$City2ArrTo = $_POST['segments'][1]['ArrTo'];
		$City3ArrTo = $_POST['segments'][2]['ArrTo'];
		$City4ArrTo = $_POST['segments'][3]['ArrTo'];
		
		      
			$FlyHubSegment ='[{
				"Origin": "'.$City1DepFrom.'",
				"Destination": "'.$City1ArrTo.'",
				"CabinClass": "Economy",
				"DepartureDateTime": "'.$City1DepDate.'"
			},
			{
				"Origin": "'.$City2DepFrom.'",
				"Destination": "'.$City2ArrTo.'",
				"CabinClass": "Economy",
				"DepartureDateTime": "'.$City2DepDate.'"
			},
			{
				"Origin": "'.$City3DepFrom.'",
				"Destination": "'.$City3ArrTo.'",
				"CabinClass": "Economy",
				"DepartureDateTime": "'.$City3DepDate.'"
			},
			{
				"Origin": "'.$City4DepFrom.'",
				"Destination": "'.$City4ArrTo.'",
				"CabinClass": "Economy",
				"DepartureDateTime": "'.$City4DepDate.'"
			}]';
               
    }

  
    $FlyHubRequest ='{
	"AdultQuantity": "'.$adult.'",
	"ChildQuantity": "'.$child.'",
	"InfantQuantity": "'.$infants.'",
	"EndUserIp": "85.187.128.34",
	"JourneyType": "3",
	"Segments": '.$FlyHubSegment.',
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
    //echo $FlyhubToken;

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
	//print_r($Result);

	
	$FlightListFlyHub = $Result['Results'];
	$SearchID = $Result['SearchId'];
	$FlyHubResponse = array();

	//print_r($FlightListFlyHub);
	
	foreach($FlightListFlyHub as $flight){
		$vCarCode = $flight['Validatingcarrier'];
		$segments = count($flight['segments']);
		$TotalFare = $flight['TotalFare'];
		$Refundable = $flight['IsRefundable'];
		

		if(isset($flight['Fares'][0]) && isset($flight['Fares'][1]) && isset($flight['Fares'][2])){
			$BasePrice = $flight['Fares'][0]['BaseFare'] * $adult + $flight['Fares'][1]['BaseFare'] * $child + $flight['Fares'][2]['BaseFare'] * $infants;
			$Taxes = $flight['Fares'][0]['Tax'] * $adult+ $flight['Fares'][1]['Tax'] * $child + $flight['Fares'][2]['Tax'] * $infants;
			$Taxes += $flight['Fares'][0]['OtherCharges']* $adult + $flight['Fares'][1]['OtherCharges'] * $child + $flight['Fares'][2]['OtherCharges'] * $infants;		
			$Taxes +=  $flight['Fares'][0]['ServiceFee']* $adult + $flight['Fares'][1]['ServiceFee'] * $child + $flight['Fares'][2]['ServiceFee'] * $infants;
			
			
		}else if(isset($flight['Fares'][0]) && isset($flight['Fares'][1])){
			$BasePrice = $flight['Fares'][0]['BaseFare'] * $adult + $flight['Fares'][1]['BaseFare']* $child ;
			$Taxes = $flight['Fares'][0]['Tax']  * $adult + $flight['Fares'][1]['Tax']* $child;
			$Taxes += $flight['Fares'][0]['OtherCharges'] * $adult * $adult + $flight['Fares'][1]['OtherCharges'] * $child ;
			$Taxes +=  $flight['Fares'][0]['ServiceFee'] * $adult + $flight['Fares'][1]['ServiceFee'] * $child ;
			
		}else if(isset($flight['Fares'][0])){
			$BasePrice = $flight['Fares'][0]['BaseFare']  * $adult;
			$Taxes = $flight['Fares'][0]['Tax']  * $adult;					
			$Taxes += $flight['Fares'][0]['OtherCharges']  * $adult;
		    $Taxes +=  $flight['Fares'][0]['ServiceFee']  * $adult;
		
		}


		$ClientFare = $BasePrice + $Taxes;
		$Commission = $ClientFare - $TotalFare;

		$pricebreakdown = $flight['Fares'];

		

		if($flight['IsRefundable'] == 1){
			$Refundable = "Refundable";
		}else{
			$Refundable = "Nonrefundable";
		}
		$Availabilty = $flight['Availabilty'];		
		$ResultID = $flight['ResultID'];

		$sql = mysqli_query($conn,"SELECT name, commission FROM airlines WHERE code='$vCarCode' ");
		$row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

		if(!empty($row)){
			$CarrieerName = $row['name'];
			$fareRate = $row['commission'];                       
		}
		
		
        if($segments == 2 && $flight['segments'][0]['Origin']['Airport']['AirportCode'] == $City1DepFrom
			 && $flight['segments'][1]['Origin']['Airport']['AirportCode']  == $City2DepFrom){

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

			$Baggage = preg_replace('/[^0-9]/', '', $flight['segments'][0]['Baggage']);
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

			$Baggage1 = $flight['segments'][1]['Baggage'];
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

							$basic = array("system"=>"FlyHub",
											"segment"=> "2",
											"career"=> $vCarCode,
											"careerName" => "$CarrieerName",
											"BasePrice" => "$BasePrice",
											"Taxes" => "$Taxes",
											"price" => "$TotalFare",
											"clientPrice"=> "$ClientFare",
											"comission"=> "$Commission",
											"departure"=> "$dAirportCode",                   
											"departureTime" => substr($DepTime,11,5),
											"departureDate" => date("D d M Y", strtotime($DepTime)),
											"arrival"=> "$aAirportCode1",                   
											"arrivalTime" => substr($ArrTime1,11,5),
											"arrivalDate" => date("D d M Y", strtotime($ArrTime1)),
											"bags" => "$Baggage",
											"seat" => "$Availabilty",
											"refundable"=> "$Refundable",
											"segments" => $segment,											
											"SearchID"=> $SearchID,
											"ResultID"=> $ResultID
											
												
										);
						array_push($FlyHubResponse, $basic);

							


		}else if($segments == 3 && $flight['segments'][0]['Origin']['Airport']['AirportCode'] == $City1DepFrom
			 && $flight['segments'][1]['Origin']['Airport']['AirportCode']  == $City2DepFrom
			  && $flight['segments'][2]['Origin']['Airport']['AirportCode']  == $City3DepFrom){

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

			$Baggage = preg_replace('/[^0-9]/', '', $flight['segments'][0]['Baggage']);
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

			$Baggage1 = $flight['segments'][1]['Baggage'];
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

			$Baggag2 = $flight['segments'][2]['Baggage'];
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
										"seat"=> "$Availabilty")								
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
											"career"=> $vCarCode,
											"careerName" => "$CarrieerName",
											"BasePrice" => "$BasePrice",
											"Taxes" => "$Taxes",
											"price" => "$TotalFare",
											"clientPrice"=> "$ClientFare",
											"comission"=> "$Commission",
											"departure"=> "$dAirportCode",                   
											"departureTime" => substr($DepTime,11,5),
											"departureDate" => date("D d M Y", strtotime($DepTime)),
											"arrival"=> "$aAirportCode2",                   
											"arrivalTime" => substr($ArrTime2,11,5),
											"arrivalDate" => date("D d M Y", strtotime($ArrTime2)),
											"bags" => "$Baggage",
											"seat" => "$Availabilty",
											"refundable"=> "$Refundable",
											"segments" => $segment,											
											"SearchID"=> $SearchID,
											"ResultID"=> $ResultID
											
												
										);
						array_push($FlyHubResponse, $basic);


		}else if($segments == 4 && $flight['segments'][0]['Origin']['Airport']['AirportCode'] == $City1DepFrom
			 && $flight['segments'][1]['Origin']['Airport']['AirportCode']  == $City2DepFrom
			  && $flight['segments'][2]['Origin']['Airport']['AirportCode']  == $City3DepFrom
			  && $flight['segments'][2]['Origin']['Airport']['AirportCode']  == $City3DepFrom){

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

			$Baggage = preg_replace('/[^0-9]/', '', $flight['segments'][0]['Baggage']);
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

			$Baggage1 = $flight['segments'][1]['Baggage'];
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

			$Baggag2 = $flight['segments'][2]['Baggage'];
			$JourneyDuration2 = $flight['segments'][2]['JourneyDuration'];
			$Duration2 = floor($JourneyDuration1 / 60)."H ".($JourneyDuration1 - ((floor($JourneyDuration1 / 60)) * 60))."Min";

            // Leg 4
            $dAirportCode3 = $flight['segments'][3]['Origin']['Airport']['AirportCode'];
            $dAirportName3 = $flight['segments'][3]['Origin']['Airport']['AirportName'];
			$dCityName3 = $flight['segments'][3]['Origin']['Airport']['AirportCode'];
			$dCountryCode3 = $flight['segments'][3]['Origin']['Airport']['CountryCode'];
            

        
			$aAirportCode3 = $flight['segments'][3]['Destination']['Airport']['AirportCode'];
			$aAirportName3 = $flight['segments'][3]['Destination']['Airport']['AirportName'];
			$aCityName3 = $flight['segments'][3]['Destination']['Airport']['CityName'];
			$aCountryCode3 = $flight['segments'][3]['Destination']['Airport']['CountryCode'];


			$DepTime3 = $flight['segments'][3]['Origin']['DepTime'];
			$ArrTime3 = $flight['segments'][3]['Destination']['ArrTime'];

			$AirlineCode3 = $flight['segments'][3]['Airline']['AirlineCode'];
			$AirlineName3 = $flight['segments'][3]['Airline']['AirlineName'];
			$FlightNumber3 = $flight['segments'][3]['Airline']['FlightNumber'];
			$BookingClass3 = $flight['segments'][3]['Airline']['BookingClass'];
			$CabinClass3 = $flight['segments'][3]['Airline']['CabinClass'];
			$OperatingCarrier3 = $flight['segments'][3]['Airline']['OperatingCarrier'];

			$Baggag3 = $flight['segments'][3]['Baggage'];
			$JourneyDuration3 = $flight['segments'][3]['JourneyDuration'];
			$Duration3 = floor($JourneyDuration1 / 60)."H ".($JourneyDuration1 - ((floor($JourneyDuration1 / 60)) * 60))."Min";


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
                            "3" =>array("marketingcareer"=> "$OperatingCarrier3",
										"marketingcareerName"=> "$AirlineName3",
										"marketingflight"=> "$FlightNumber3",
										"operatingcareer"=> "$OperatingCarrier3",
										"operatingflight"=> "$FlightNumber3",
										"departure"=> "$dAirportCode3",
										"departureAirport"=> "$dAirportName3",
										"departureLocation"=> "$dCityName3 , $dCountryCode3",                    
										"departureTime" => "$DepTime3",
										"arrival"=> "$aAirportCode3",                   
										"arrivalTime" => "$ArrTime3",
										"arrivalAirport"=> "$aAirportName3",
										"arrivalLocation"=> "$aCityName3 , $aCountryCode3",
										"flightduration"=> "$Duration3",
										"bookingcode"=> "$BookingClass3",
										"seat"=> "$Availabilty")								
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
											"segment"=> "4",
											"career"=> $vCarCode,
											"careerName" => "$CarrieerName",
											"BasePrice" => "$BasePrice",
											"Taxes" => "$Taxes",
											"price" => "$TotalFare",
											"clientPrice"=> "$ClientFare",
											"comission"=> "$Commission",
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
											"SearchID"=> $SearchID,
											"ResultID"=> $ResultID
											
												
										);
						array_push($FlyHubResponse, $basic);


		}
		
		else if($segments == 5){

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

			$Baggage = preg_replace('/[^0-9]/', '', $flight['segments'][0]['Baggage']);
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

			$Baggage1 = $flight['segments'][1]['Baggage'];
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

			$Baggag2 = $flight['segments'][2]['Baggage'];
			$JourneyDuration2 = $flight['segments'][2]['JourneyDuration'];
			$Duration2 = floor($JourneyDuration1 / 60)."H ".($JourneyDuration1 - ((floor($JourneyDuration1 / 60)) * 60))."Min";

            // Leg 4
            $dAirportCode3 = $flight['segments'][3]['Origin']['Airport']['AirportCode'];
            $dAirportName3 = $flight['segments'][3]['Origin']['Airport']['AirportName'];
			$dCityName3 = $flight['segments'][3]['Origin']['Airport']['AirportCode'];
			$dCountryCode3 = $flight['segments'][3]['Origin']['Airport']['CountryCode'];
            

			$aAirportCode3 = $flight['segments'][3]['Destination']['Airport']['AirportCode'];
			$aAirportName3 = $flight['segments'][3]['Destination']['Airport']['AirportName'];
			$aCityName3 = $flight['segments'][3]['Destination']['Airport']['CityName'];
			$aCountryCode3 = $flight['segments'][3]['Destination']['Airport']['CountryCode'];


			$DepTime3 = $flight['segments'][3]['Origin']['DepTime'];
			$ArrTime3 = $flight['segments'][3]['Destination']['ArrTime'];

			$AirlineCode3 = $flight['segments'][3]['Airline']['AirlineCode'];
			$AirlineName3 = $flight['segments'][3]['Airline']['AirlineName'];
			$FlightNumber3 = $flight['segments'][3]['Airline']['FlightNumber'];
			$BookingClass3 = $flight['segments'][3]['Airline']['BookingClass'];
			$CabinClass3 = $flight['segments'][3]['Airline']['CabinClass'];
			$OperatingCarrier3 = $flight['segments'][3]['Airline']['OperatingCarrier'];

			$Baggag3 = $flight['segments'][3]['Baggage'];
			$JourneyDuration3 = $flight['segments'][3]['JourneyDuration'];
			$Duration3 = floor($JourneyDuration1 / 60)."H ".($JourneyDuration1 - ((floor($JourneyDuration1 / 60)) * 60))."Min";

			// Leg 5
            $dAirportCode4 = $flight['segments'][4]['Origin']['Airport']['AirportCode'];
            $dAirportName4 = $flight['segments'][4]['Origin']['Airport']['AirportName'];
			$dCityName4 = $flight['segments'][4]['Origin']['Airport']['AirportCode'];
			$dCountryCode4 = $flight['segments'][4]['Origin']['Airport']['CountryCode'];
            
			$aAirportCode4 = $flight['segments'][4]['Destination']['Airport']['AirportCode'];
			$aAirportName4 = $flight['segments'][4]['Destination']['Airport']['AirportName'];
			$aCityName4 = $flight['segments'][4]['Destination']['Airport']['CityName'];
			$aCountryCode4 = $flight['segments'][4]['Destination']['Airport']['CountryCode'];

			$DepTime4 = $flight['segments'][4]['Origin']['DepTime'];
			$ArrTime4 = $flight['segments'][4]['Destination']['ArrTime'];

			$AirlineCode4 = $flight['segments'][4]['Airline']['AirlineCode'];
			$AirlineName4 = $flight['segments'][4]['Airline']['AirlineName'];
			$FlightNumber4 = $flight['segments'][4]['Airline']['FlightNumber'];
			$BookingClass4 = $flight['segments'][4]['Airline']['BookingClass'];
			$CabinClass4 = $flight['segments'][4]['Airline']['CabinClass'];
			$OperatingCarrier4 = $flight['segments'][4]['Airline']['OperatingCarrier'];

			$Baggag4 = $flight['segments'][4]['Baggage'];
			$JourneyDuration4 = $flight['segments'][4]['JourneyDuration'];
			$Duration4 = floor($JourneyDuration1 / 60)."H ".($JourneyDuration1 - ((floor($JourneyDuration1 / 60)) * 60))."Min";


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
                            "3" =>array("marketingcareer"=> "$OperatingCarrier3",
										"marketingcareerName"=> "$AirlineName3",
										"marketingflight"=> "$FlightNumber3",
										"operatingcareer"=> "$OperatingCarrier3",
										"operatingflight"=> "$FlightNumber3",
										"departure"=> "$dAirportCode3",
										"departureAirport"=> "$dAirportName3",
										"departureLocation"=> "$dCityName3 , $dCountryCode3",                    
										"departureTime" => "$DepTime3",
										"arrival"=> "$aAirportCode3",                   
										"arrivalTime" => "$ArrTime3",
										"arrivalAirport"=> "$aAirportName3",
										"arrivalLocation"=> "$aCityName3 , $aCountryCode3",
										"flightduration"=> "$Duration3",
										"bookingcode"=> "$BookingClass3",
										"seat"=> "$Availabilty"),
                                        
                            "4" =>array("marketingcareer"=> "$OperatingCarrier4",
										"marketingcareerName"=> "$AirlineName4",
										"marketingflight"=> "$FlightNumber4",
										"operatingcareer"=> "$OperatingCarrier4",
										"operatingflight"=> "$FlightNumber4",
										"departure"=> "$dAirportCode4",
										"departureAirport"=> "$dAirportName4",
										"departureLocation"=> "$dCityName4 , $dCountryCode4",                    
										"departureTime" => "$DepTime4",
										"arrival"=> "$aAirportCode4",                   
										"arrivalTime" => "$ArrTime4",
										"arrivalAirport"=> "$aAirportName4",
										"arrivalLocation"=> "$aCityName4 , $aCountryCode4",
										"flightduration"=> "$Duration4",
										"bookingcode"=> "$BookingClass4",
										"seat"=> "$Availabilty")								
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
											"segment"=> "5",
											"career"=> $vCarCode,
											"careerName" => "$CarrieerName",
											"BasePrice" => "$BasePrice",
											"Taxes" => "$Taxes",
											"price" => "$TotalFare",
											"clientPrice"=> "$ClientFare",
											"comission"=> "$Commission",
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
											"SearchID"=> $SearchID,
											"ResultID"=> $ResultID
											
												
										);
						array_push($FlyHubResponse, $basic);


		}else if($segments == 6){

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

			$Baggage = preg_replace('/[^0-9]/', '', $flight['segments'][0]['Baggage']);
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

			$Baggage1 = $flight['segments'][1]['Baggage'];
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

			$Baggag2 = $flight['segments'][2]['Baggage'];
			$JourneyDuration2 = $flight['segments'][2]['JourneyDuration'];
			$Duration2 = floor($JourneyDuration1 / 60)."H ".($JourneyDuration1 - ((floor($JourneyDuration1 / 60)) * 60))."Min";

            // Leg 4
            $dAirportCode3 = $flight['segments'][3]['Origin']['Airport']['AirportCode'];
            $dAirportName3 = $flight['segments'][3]['Origin']['Airport']['AirportName'];
			$dCityName3 = $flight['segments'][3]['Origin']['Airport']['AirportCode'];
			$dCountryCode3 = $flight['segments'][3]['Origin']['Airport']['CountryCode'];
            

            
            

			$aAirportCode3 = $flight['segments'][3]['Destination']['Airport']['AirportCode'];
			$aAirportName3 = $flight['segments'][3]['Destination']['Airport']['AirportName'];
			$aCityName3 = $flight['segments'][3]['Destination']['Airport']['CityName'];
			$aCountryCode3 = $flight['segments'][3]['Destination']['Airport']['CountryCode'];


			$DepTime3 = $flight['segments'][3]['Origin']['DepTime'];
			$ArrTime3 = $flight['segments'][3]['Destination']['ArrTime'];

			$AirlineCode3 = $flight['segments'][3]['Airline']['AirlineCode'];
			$AirlineName3 = $flight['segments'][3]['Airline']['AirlineName'];
			$FlightNumber3 = $flight['segments'][3]['Airline']['FlightNumber'];
			$BookingClass3 = $flight['segments'][3]['Airline']['BookingClass'];
			$CabinClass3 = $flight['segments'][3]['Airline']['CabinClass'];
			$OperatingCarrier3 = $flight['segments'][3]['Airline']['OperatingCarrier'];

			$Baggag3 = $flight['segments'][3]['Baggage'];
			$JourneyDuration3 = $flight['segments'][3]['JourneyDuration'];
			$Duration3 = floor($JourneyDuration1 / 60)."H ".($JourneyDuration1 - ((floor($JourneyDuration1 / 60)) * 60))."Min";

			// Leg 5
            $dAirportCode4 = $flight['segments'][4]['Origin']['Airport']['AirportCode'];
            $dAirportName4 = $flight['segments'][4]['Origin']['Airport']['AirportName'];
			$dCityName4 = $flight['segments'][4]['Origin']['Airport']['AirportCode'];
			$dCountryCode4 = $flight['segments'][4]['Origin']['Airport']['CountryCode'];
            

            
            

			$aAirportCode4 = $flight['segments'][4]['Destination']['Airport']['AirportCode'];
			$aAirportName4 = $flight['segments'][4]['Destination']['Airport']['AirportName'];
			$aCityName4 = $flight['segments'][4]['Destination']['Airport']['CityName'];
			$aCountryCode4 = $flight['segments'][4]['Destination']['Airport']['CountryCode'];


			$DepTime4 = $flight['segments'][4]['Origin']['DepTime'];
			$ArrTime4 = $flight['segments'][4]['Destination']['ArrTime'];

			$AirlineCode4 = $flight['segments'][4]['Airline']['AirlineCode'];
			$AirlineName4 = $flight['segments'][4]['Airline']['AirlineName'];
			$FlightNumber4 = $flight['segments'][4]['Airline']['FlightNumber'];
			$BookingClass4 = $flight['segments'][4]['Airline']['BookingClass'];
			$CabinClass4 = $flight['segments'][4]['Airline']['CabinClass'];
			$OperatingCarrier4 = $flight['segments'][4]['Airline']['OperatingCarrier'];

			$Baggag4 = $flight['segments'][4]['Baggage'];
			$JourneyDuration4 = $flight['segments'][4]['JourneyDuration'];
			$Duration4 = floor($JourneyDuration1 / 60)."H ".($JourneyDuration1 - ((floor($JourneyDuration1 / 60)) * 60))."Min";

			// Leg 6
            $dAirportCode5 = $flight['segments'][5]['Origin']['Airport']['AirportCode'];
            $dAirportName5 = $flight['segments'][5]['Origin']['Airport']['AirportName'];
			$dCityName5 = $flight['segments'][5]['Origin']['Airport']['AirportCode'];
			$dCountryCode5 = $flight['segments'][5]['Origin']['Airport']['CountryCode'];
            

            
            

			$aAirportCode5 = $flight['segments'][5]['Destination']['Airport']['AirportCode'];
			$aAirportName5 = $flight['segments'][5]['Destination']['Airport']['AirportName'];
			$aCityName5 = $flight['segments'][5]['Destination']['Airport']['CityName'];
			$aCountryCode5 = $flight['segments'][5]['Destination']['Airport']['CountryCode'];


			$DepTime5 = $flight['segments'][5]['Origin']['DepTime'];
			$ArrTime5 = $flight['segments'][5]['Destination']['ArrTime'];

			$AirlineCode5 = $flight['segments'][5]['Airline']['AirlineCode'];
			$AirlineName5 = $flight['segments'][5]['Airline']['AirlineName'];
			$FlightNumber5 = $flight['segments'][5]['Airline']['FlightNumber'];
			$BookingClass5 = $flight['segments'][5]['Airline']['BookingClass'];
			$CabinClass5 = $flight['segments'][5]['Airline']['CabinClass'];
			$OperatingCarrier5 = $flight['segments'][5]['Airline']['OperatingCarrier'];

			$Baggag5 = $flight['segments'][5]['Baggage'];
			$JourneyDuration5 = $flight['segments'][5]['JourneyDuration'];
			$Duration5 = floor($JourneyDuration1 / 60)."H ".($JourneyDuration1 - ((floor($JourneyDuration1 / 60)) * 60))."Min";


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
                            "3" =>array("marketingcareer"=> "$OperatingCarrier3",
										"marketingcareerName"=> "$AirlineName3",
										"marketingflight"=> "$FlightNumber3",
										"operatingcareer"=> "$OperatingCarrier3",
										"operatingflight"=> "$FlightNumber3",
										"departure"=> "$dAirportCode3",
										"departureAirport"=> "$dAirportName3",
										"departureLocation"=> "$dCityName3 , $dCountryCode3",                    
										"departureTime" => "$DepTime3",
										"arrival"=> "$aAirportCode3",                   
										"arrivalTime" => "$ArrTime3",
										"arrivalAirport"=> "$aAirportName3",
										"arrivalLocation"=> "$aCityName3 , $aCountryCode3",
										"flightduration"=> "$Duration3",
										"bookingcode"=> "$BookingClass3",
										"seat"=> "$Availabilty"),
                                        
                            "4" =>array("marketingcareer"=> "$OperatingCarrier4",
										"marketingcareerName"=> "$AirlineName4",
										"marketingflight"=> "$FlightNumber4",
										"operatingcareer"=> "$OperatingCarrier4",
										"operatingflight"=> "$FlightNumber4",
										"departure"=> "$dAirportCode4",
										"departureAirport"=> "$dAirportName4",
										"departureLocation"=> "$dCityName4 , $dCountryCode4",                    
										"departureTime" => "$DepTime4",
										"arrival"=> "$aAirportCode4",                   
										"arrivalTime" => "$ArrTime4",
										"arrivalAirport"=> "$aAirportName4",
										"arrivalLocation"=> "$aCityName4 , $aCountryCode4",
										"flightduration"=> "$Duration4",
										"bookingcode"=> "$BookingClass4",
										"seat"=> "$Availabilty"),
							"5" =>array("marketingcareer"=> "$OperatingCarrier5",
										"marketingcareerName"=> "$AirlineName5",
										"marketingflight"=> "$FlightNumber5",
										"operatingcareer"=> "$OperatingCarrier5",
										"operatingflight"=> "$FlightNumber5",
										"departure"=> "$dAirportCode5",
										"departureAirport"=> "$dAirportName5",
										"departureLocation"=> "$dCityName5 , $dCountryCode5",                    
										"departureTime" => "$DepTime5",
										"arrival"=> "$aAirportCode5",                   
										"arrivalTime" => "$ArrTime5",
										"arrivalAirport"=> "$aAirportName5",
										"arrivalLocation"=> "$aCityName5 , $aCountryCode5",
										"flightduration"=> "$Duration5",
										"bookingcode"=> "$BookingClass5",
										"seat"=> "$Availabilty")								
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
											"segment"=> "6",
											"career"=> $vCarCode,
											"careerName" => "$CarrieerName",
											"BasePrice" => "$BasePrice",
											"Taxes" => "$Taxes",
											"price" => "$TotalFare",
											"clientPrice"=> "$ClientFare",
											"comission"=> "$Commission",
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
											"SearchID"=> $SearchID,
											"ResultID"=> $ResultID
											
												
										);
						array_push($FlyHubResponse, $basic);

		}                                       
			
	}
}


	echo json_encode($FlyHubResponse, JSON_PRETTY_PRINT);

                      
?>