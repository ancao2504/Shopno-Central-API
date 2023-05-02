<?php

include_once('../config.php');
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$AmadeusList = array();

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://test.api.amadeus.com/v1/security/oauth2/token',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => 'client_id=8kt3la3SudclVxenjcNs74l4nTrpEZVE&client_secret=KnpjmyAcTAxnLZGZ&grant_type=client_credentials',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/x-www-form-urlencoded'
  ),
));

$response = curl_exec($curl);
curl_close($curl);

$toke_result = json_decode($response,JSON_PRETTY_PRINT );
$access_token_amadeus = $toke_result['access_token'];


// Search Flight

$curl1 = curl_init();

curl_setopt_array($curl1, array(
  CURLOPT_URL => 'https://test.api.amadeus.com/v2/shopping/flight-offers',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS =>'{
    "currencyCode": "BDT",
    "originDestinations": [
        {
            "id": "1",
            "originLocationCode": "'.$From.'",
            "destinationLocationCode": "'.$To.'",
            "departureDateTimeRange": {
                "date": "2022-11-01"
            }
        }
        
    ],
    "travelers": [
        {
            "id": "1",
            "travelerType": "ADULT",
            "fareOptions": [
                "STANDARD"
            ]
        }
    ],
    "sources": [
        "GDS"
    ],
    "searchCriteria": {
        "maxFlightOffers": 2,
        "flightFilters": {
            "cabinRestrictions": [
                {
                    "cabin": "BUSINESS",
                    "coverage": "MOST_SEGMENTS",
                    "originDestinationIds": [
                        "1"
                    ]
                }
            ],
            "carrierRestrictions": {
                "excludedCarrierCodes": [
                    "AA",
                    "TP",
                    "AZ"
                ]
            }
        }
    }
}',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'X-HTTP-Method-Override: GET',
    "Authorization: Bearer $access_token_amadeus"
  ),
));

$response1 = curl_exec($curl1);

curl_close($curl1);

$AmadeusSearchResult = json_decode($response1,JSON_PRETTY_PRINT );

$AmadeusFlightList = $AmadeusSearchResult['data'];

//print_r($AmadeusFlightList);


foreach($AmadeusFlightList as $amadeusFlight){

  $TotalFare = $amadeusFlight['price']['total'];
  $BasePrice = $amadeusFlight['price']['base'];
  $Validatingcarrier = $amadeusFlight['validatingAirlineCodes'][0];
  $sql = mysqli_query($conn,"SELECT name FROM airlines WHERE code='$Validatingcarrier' ");
  $row = mysqli_fetch_array($sql,MYSQLI_ASSOC);

  if(!empty($row)){
    $CarrieerName = $row['name'];		
  }
  $Segment= count($amadeusFlight['itineraries'][0]['segments']);
  $Seat = $amadeusFlight['numberOfBookableSeats'];
  $CabinClass= $amadeusFlight['travelerPricings'][0]['fareDetailsBySegment'][0]['cabin'];

  if(isset($amadeusFlight['travelerPricings'][0]['fareDetailsBySegment'][0]['includedCheckedBags']['weight'])){
    $quantity = $amadeusFlight['travelerPricings'][0]['fareDetailsBySegment'][0]['includedCheckedBags']['weight'];
    $Baggage = "$quantity KG";
  }else if (isset($amadeusFlight['travelerPricings'][0]['fareDetailsBySegment'][0]['includedCheckedBags']['quantity'])){
    $quantity = $amadeusFlight['travelerPricings'][0]['fareDetailsBySegment'][0]['includedCheckedBags']['quantity'];
    $Baggage = "$quantity Piece";

  }else{
    $Baggage = "0 KG";
  }

  $CabinClass= $amadeusFlight['travelerPricings'][0]['fareDetailsBySegment'][0]['cabin'];


  if($Segment == 1){

    // Departure Country
    $dAirportCode = $amadeusFlight['itineraries'][0]['segments'][0]['departure']['iataCode'];
    $sql1 = mysqli_query($conn,"SELECT name, cityName,countryCode FROM airports WHERE code='$dAirportCode' ");
    $row1 = mysqli_fetch_array($sql1,MYSQLI_ASSOC);

    if(!empty($row1)){
      $dAirport = $row1['name'];
      $dCity = $row1['cityName'];
      $dCountry = $row1['countryCode'];		
    }

    // Arrival Country
    $aAirportCode = $amadeusFlight['itineraries'][0]['segments'][0]['arrival']['iataCode'];
    $sql2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$aAirportCode' ");
    $row2 = mysqli_fetch_array($sql2,MYSQLI_ASSOC);

    if(!empty($row2)){
      $aAirport = $row2['name'];
      $aCity = $row2['cityName'];
      $aCountry = $row2['countryCode'];
    }

			$DepTime = $amadeusFlight['itineraries'][0]['segments'][0]['departure']['at'];
			$ArrTime = $amadeusFlight['itineraries'][0]['segments'][0]['arrival']['at'];

			$AirlineCode = $amadeusFlight['itineraries'][0]['segments'][0]['carrierCode'];
			$FlightNumber = $amadeusFlight['itineraries'][0]['segments'][0]['number'];
			$BookingClass = $amadeusFlight['travelerPricings'][0]['fareDetailsBySegment'][0]['class'];
			$OperatingCarrier = $amadeusFlight['itineraries'][0]['segments'][0]['operating']['carrierCode'];
			$Duration = substr($amadeusFlight['itineraries'][0]['duration'],2,8);


    $segment = array("0" =>
												array("marketingcareer"=> "$AirlineCode",
													"marketingflight"=> "$FlightNumber",
													"operatingcareer"=> "$OperatingCarrier",
													"operatingflight"=> "$FlightNumber",
													"departure"=> "$dAirportCode",
													"departureAirport"=> "$dAirport",
													"departureLocation"=> "$dCity , $dCountry",                    
													"departureTime" => "$DepTime",
													"arrival"=> "$aAirportCode",                   
													"arrivalTime" => "$ArrTime",
													"arrivalAirport"=> "$aAirport",
													"arrivalLocation"=> "$aCity , $aCountry",
                          "BookingClass"=> "$BookingClass",
													"flightduration"=> "$Duration",
													"seat"=> "$Seat")                                           

											);

     $basic = array("system"=>"Amadeus",
												"segment"=> "$Segment",
												"career"=> "$Validatingcarrier",
												"careerName" => "$CarrieerName",
												"price" => "$TotalFare",
												"departure"=> "$dAirportCode",                   
												"departureTime" => substr($DepTime,11,13),
												"departureDate" => "$DepTime",
												"arrival"=> "$aAirportCode",                   
												"arrivalTime" => substr($ArrTime,11,13),
												"arrivalDate" => "$ArrTime",
												"flightduration"=> "$Duration",
												"transit" => $Transits,
												"bags" => "$Baggage",
												"seat" => "$Seat",
												"class" => "$CabinClass",
												"refundable"=> "Refundable",
												"segments" => $segment																								
										);

	array_push($AmadeusList, $basic);


  }else if($Segment == 2){

    //Segment 1
    // Departure Country
    $dAirportCode = $amadeusFlight['itineraries'][0]['segments'][0]['departure']['iataCode'];
    $dsql = mysqli_query($conn,"SELECT name, cityName,countryCode FROM airports WHERE code='$dAirportCode' ");
    $drow = mysqli_fetch_array($dsql,MYSQLI_ASSOC);

    if(!empty($drow)){
      $dAirport = $drow['name'];
      $dCity = $drow['cityName'];
      $dCountry = $drow['countryCode'];		
    }

    // Arrival Country
    $aAirportCode = $amadeusFlight['itineraries'][0]['segments'][0]['arrival']['iataCode'];
    $asql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$aAirportCode' ");
    $arow = mysqli_fetch_array($asql,MYSQLI_ASSOC);

    if(!empty($arow)){
      $aAirport = $arow['name'];
      $aCity = $arow['cityName'];
      $aCountry = $arow['countryCode'];
    }

			$DepTime = $amadeusFlight['itineraries'][0]['segments'][0]['departure']['at'];
			$ArrTime = $amadeusFlight['itineraries'][0]['segments'][0]['arrival']['at'];

			$AirlineCode = $amadeusFlight['itineraries'][0]['segments'][0]['carrierCode'];
			$FlightNumber = $amadeusFlight['itineraries'][0]['segments'][0]['number'];
			$BookingClass = $amadeusFlight['travelerPricings'][0]['fareDetailsBySegment'][0]['class'];
			$OperatingCarrier = $amadeusFlight['itineraries'][0]['segments'][0]['operating']['carrierCode'];
			$Duration = substr($amadeusFlight['itineraries'][0]['duration'],2,8);

    //Segment 2
    // Departure Country
    $dAirportCode1 = $amadeusFlight['itineraries'][0]['segments'][1]['departure']['iataCode'];
    $dsql1 = mysqli_query($conn,"SELECT name, cityName,countryCode FROM airports WHERE code='$dAirportCode1' ");
    $drow1 = mysqli_fetch_array($dsql1,MYSQLI_ASSOC);

    if(!empty($drow1)){
      $dAirport1 = $drow1['name'];
      $dCity1= $drow1['cityName'];
      $dCountry1 = $drow1['countryCode'];		
    }

    // Arrival Country
    $aAirportCode1 = $amadeusFlight['itineraries'][0]['segments'][1]['arrival']['iataCode'];
    $asql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$aAirportCode1' ");
    $arow1 = mysqli_fetch_array($asql1,MYSQLI_ASSOC);

    if(!empty($arow1)){
      $aAirport1 = $arow1['name'];
      $aCity1 = $arow1['cityName'];
      $aCountry1 = $arow1['countryCode'];
    }

			$DepTime1 = $amadeusFlight['itineraries'][0]['segments'][1]['departure']['at'];
			$ArrTime1 = $amadeusFlight['itineraries'][0]['segments'][1]['arrival']['at'];

			$AirlineCode1 = $amadeusFlight['itineraries'][0]['segments'][1]['carrierCode'];
			$FlightNumber1 = $amadeusFlight['itineraries'][0]['segments'][1]['number'];
			$BookingClass1 = $amadeusFlight['travelerPricings'][0]['fareDetailsBySegment'][0]['class'];
			$OperatingCarrier1 = $amadeusFlight['itineraries'][0]['segments'][1]['operating']['carrierCode'];
			$Duration1 = substr($amadeusFlight['itineraries'][0]['duration'],2,8);


      $start_date = new DateTime($ArrTime);
      $since_start = $start_date->diff(new DateTime($DepTime1));

      $Hours =  $since_start->h;
      $Minutes =  $since_start->i;


      $Transit = "$Hours H $Minutes M";
      $Transits = array("0" => $Transit);


    $segment = array("0" =>
												array("marketingcareer"=> "$AirlineCode",
													"marketingflight"=> "$FlightNumber",
													"operatingcareer"=> "$OperatingCarrier",
													"operatingflight"=> "$FlightNumber",
													"departure"=> "$dAirportCode",
													"departureAirport"=> "$dAirport",
													"departureLocation"=> "$dCity , $dCountry",                    
													"departureTime" => "$DepTime",
													"arrival"=> "$aAirportCode",                   
													"arrivalTime" => "$ArrTime",
													"arrivalAirport"=> "$aAirport",
													"arrivalLocation"=> "$aCity , $aCountry",
                          "BookingClass"=> "$BookingClass",
													"flightduration"=> "$Duration",
													"seat"=> "$Seat"),                                           

											"1" =>
												array("marketingcareer"=> "$AirlineCode1",
													"marketingflight"=> "$FlightNumber1",
													"operatingcareer"=> "$OperatingCarrier1",
													"operatingflight"=> "$FlightNumber1",
													"departure"=> "$dAirportCode1",
													"departureAirport"=> "$dAirport1",
													"departureLocation"=> "$dCity1 , $dCountry1",                    
													"departureTime" => "$DepTime1",
													"arrival"=> "$aAirportCode1",                   
													"arrivalTime" => "$ArrTime1",
													"arrivalAirport"=> "$aAirport1",
													"arrivalLocation"=> "$aCity1 , $aCountry1",
                          "BookingClass"=> "$BookingClass1",
													"flightduration"=> "$Duration1",
													"seat"=> "$Seat")                                           

											);

     $basic = array("system"=>"Amadeus",
												"segment"=> "$Segment",
												"career"=> "$Validatingcarrier",
												"careerName" => "$CarrieerName",
												"price" => "$TotalFare",
												"departure"=> "$dAirportCode",                   
												"departureTime" => substr($DepTime,11,13),
												"departureDate" => "$DepTime",
												"arrival"=> "$aAirportCode",                   
												"arrivalTime" => substr($ArrTime1,11,13),
												"arrivalDate" => "$ArrTime1",
												"flightduration"=> "$Duration",
												"transit" => $Transits,
												"bags" => "$Baggage",
												"seat" => "$Seat",
												"class" => "$CabinClass",
												"refundable"=> "Refundable",
												"segments" => $segment																								
										);

	array_push($AmadeusList, $basic);

  }else if($Segment == 3){
    //Segment 1
    // Departure Country
    $dAirportCode = $amadeusFlight['itineraries'][0]['segments'][0]['departure']['iataCode'];
    $dsql = mysqli_query($conn,"SELECT name, cityName,countryCode FROM airports WHERE code='$dAirportCode' ");
    $drow = mysqli_fetch_array($dsql,MYSQLI_ASSOC);

    if(!empty($drow)){
      $dAirport = $drow['name'];
      $dCity = $drow['cityName'];
      $dCountry = $drow['countryCode'];		
    }

    // Arrival Country
    $aAirportCode = $amadeusFlight['itineraries'][0]['segments'][0]['arrival']['iataCode'];
    $asql = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$aAirportCode' ");
    $arow = mysqli_fetch_array($asql,MYSQLI_ASSOC);

    if(!empty($arow)){
      $aAirport = $arow['name'];
      $aCity = $arow['cityName'];
      $aCountry = $arow['countryCode'];
    }

			$DepTime = $amadeusFlight['itineraries'][0]['segments'][0]['departure']['at'];
			$ArrTime = $amadeusFlight['itineraries'][0]['segments'][0]['arrival']['at'];

			$AirlineCode = $amadeusFlight['itineraries'][0]['segments'][0]['carrierCode'];
			$FlightNumber = $amadeusFlight['itineraries'][0]['segments'][0]['number'];
			$BookingClass = $amadeusFlight['travelerPricings'][0]['fareDetailsBySegment'][0]['class'];
			$OperatingCarrier = $amadeusFlight['itineraries'][0]['segments'][0]['operating']['carrierCode'];
			$Duration = substr($amadeusFlight['itineraries'][0]['duration'],2,8);

    //Segment 2
    // Departure Country
    $dAirportCode1 = $amadeusFlight['itineraries'][0]['segments'][1]['departure']['iataCode'];
    $dsql1 = mysqli_query($conn,"SELECT name, cityName,countryCode FROM airports WHERE code='$dAirportCode1' ");
    $drow1 = mysqli_fetch_array($dsql1,MYSQLI_ASSOC);

    if(!empty($drow1)){
      $dAirport1 = $drow1['name'];
      $dCity1= $drow1['cityName'];
      $dCountry1 = $drow1['countryCode'];		
    }

    // Arrival Country
    $aAirportCode1 = $amadeusFlight['itineraries'][0]['segments'][1]['arrival']['iataCode'];
    $asql1 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$aAirportCode1' ");
    $arow1 = mysqli_fetch_array($asql1,MYSQLI_ASSOC);

    if(!empty($arow1)){
      $aAirport1 = $arow1['name'];
      $aCity1 = $arow1['cityName'];
      $aCountry1 = $arow1['countryCode'];
    }

			$DepTime1 = $amadeusFlight['itineraries'][0]['segments'][1]['departure']['at'];
			$ArrTime1 = $amadeusFlight['itineraries'][0]['segments'][1]['arrival']['at'];

			$AirlineCode1 = $amadeusFlight['itineraries'][0]['segments'][1]['carrierCode'];
			$FlightNumber1 = $amadeusFlight['itineraries'][0]['segments'][1]['number'];
			$BookingClass1 = $amadeusFlight['travelerPricings'][0]['fareDetailsBySegment'][0]['class'];
			$OperatingCarrier1 = $amadeusFlight['itineraries'][0]['segments'][1]['operating']['carrierCode'];
			$Duration1 = substr($amadeusFlight['itineraries'][0]['duration'],2,8);

    //Segment 3
    // Departure Country
    $dAirportCode2 = $amadeusFlight['itineraries'][0]['segments'][0]['departure']['iataCode'];
    $dsql2 = mysqli_query($conn,"SELECT name, cityName,countryCode FROM airports WHERE code='$dAirportCode' ");
    $drow2 = mysqli_fetch_array($dsql2,MYSQLI_ASSOC);

    if(!empty($drow2)){
      $dAirport2 = $drow2['name'];
      $dCity2 = $drow2['cityName'];
      $dCountry2 = $drow2['countryCode'];		
    }

    // Arrival Country
    $aAirportCode2 = $amadeusFlight['itineraries'][0]['segments'][2]['arrival']['iataCode'];
    $asql2 = mysqli_query($conn,"SELECT name, cityName, countryCode FROM airports WHERE code='$aAirportCode' ");
    $arow2 = mysqli_fetch_array($asql2,MYSQLI_ASSOC);

    if(!empty($arow2)){
      $aAirport2 = $arow2['name'];
      $aCity2 = $arow2['cityName'];
      $aCountry2 = $arow2['countryCode'];
    }

			$DepTime2 = $amadeusFlight['itineraries'][0]['segments'][2]['departure']['at'];
			$ArrTime2 = $amadeusFlight['itineraries'][0]['segments'][2]['arrival']['at'];

			$AirlineCode2 = $amadeusFlight['itineraries'][0]['segments'][2]['carrierCode'];
			$FlightNumber2 = $amadeusFlight['itineraries'][0]['segments'][2]['number'];
			$BookingClass2 = $amadeusFlight['travelerPricings'][0]['fareDetailsBySegment'][0]['class'];
			$OperatingCarrier2 = $amadeusFlight['itineraries'][0]['segments'][2]['operating']['carrierCode'];
			$Duration2 = substr($amadeusFlight['itineraries'][0]['duration'],2,8);

      //Segment 1
      $start_date = new DateTime($ArrTime);
      $since_start = $start_date->diff(new DateTime($DepTime1));
      $Hours =  $since_start->h;
      $Minutes =  $since_start->i;
      $Transit = "'$Hours'H '$Minutes'M";

      //Segment 2
      $start_date1 = new DateTime($ArrTime1);
      $since_start1 = $start_date->diff(new DateTime($DepTime2));

      $Hours1 =  $since_start1->h;
      $Minutes1 =  $since_start1->i;

      $Transit1 = "'$Hours1'H '$Minutes1'M";

      $Transits = array("0" => $Transit,
                        "1" => $Transit1);


    $segment = array("0" =>
												array("marketingcareer"=> "$AirlineCode",
													"marketingflight"=> "$FlightNumber",
													"operatingcareer"=> "$OperatingCarrier",
													"operatingflight"=> "$FlightNumber",
													"departure"=> "$dAirportCode",
													"departureAirport"=> "$dAirport",
													"departureLocation"=> "$dCity , $dCountry",                    
													"departureTime" => "$DepTime",
													"arrival"=> "$aAirportCode",                   
													"arrivalTime" => "$ArrTime",
													"arrivalAirport"=> "$aAirport",
													"arrivalLocation"=> "$aCity , $aCountry",
                          "BookingClass"=> "$BookingClass",
													"flightduration"=> "$Duration",
													"seat"=> "$Seat"),                                           

											"1" =>
												array("marketingcareer"=> "$AirlineCode1",
													"marketingflight"=> "$FlightNumber1",
													"operatingcareer"=> "$OperatingCarrier1",
													"operatingflight"=> "$FlightNumber1",
													"departure"=> "$dAirportCode1",
													"departureAirport"=> "$dAirport1",
													"departureLocation"=> "$dCity1 , $dCountry1",                    
													"departureTime" => "$DepTime1",
													"arrival"=> "$aAirportCode1",                   
													"arrivalTime" => "$ArrTime1",
													"arrivalAirport"=> "$aAirport1",
													"arrivalLocation"=> "$aCity1 , $aCountry1",
                          "BookingClass"=> "$BookingClass1",
													"flightduration"=> "$Duration1",
													"seat"=> "$Seat")                                           

											);

     $basic = array("system"=>"Amadeus",
												"segment"=> "$Segment",
												"career"=> "$Validatingcarrier",
												"careerName" => "$CarrieerName",
												"price" => "$TotalFare",
												"departure"=> "$dAirportCode",                   
												"departureTime" => substr($DepTime,11,13),
												"departureDate" => "$DepTime",
												"arrival"=> "$aAirportCode",                   
												"arrivalTime" => substr($ArrTime1,11,13),
												"arrivalDate" => "$ArrTime1",
												"flightduration"=> "$Duration",
												"transit" => $Transits,
												"bags" => "$Baggage",
												"seat" => "$Seat",
												"class" => "$CabinClass",
												"refundable"=> "Refundable",
												"segments" => $segment																								
										);

	array_push($AmadeusList, $basic);

  }



}

$json_string = json_encode($AmadeusList, JSON_PRETTY_PRINT);
print_r($json_string);




