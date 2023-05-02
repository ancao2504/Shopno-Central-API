<?php

include "../config.php";

header("Access-Control-Allow-Origin: *");

header("Content-Type: application/json; charset=UTF-8");

header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");

header("Access-Control-Max-Age: 3600");

header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$All = array();

$Airportsql = "SELECT name, cityName,countryCode FROM airports WHERE";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $_POST = json_decode(file_get_contents('php://input'), true);

    $agentId = $_POST['agentId'];

    $adult = $_POST['adultCount'];

    $child = $_POST['childCount'];

    $infants = $_POST['infantCount'];

    $time = date("Y-m-d H:i:s");

    $agentChecker = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'")->fetch_all(MYSQLI_ASSOC);

    if (!empty($agentChecker)) {

        $SeatReq = $adult + $child;

        if ($adult > 0 && $child > 0 && $infants > 0) {

            $SabreRequest = '{

								"Code": "ADT",

								"Quantity": ' . $adult . '

							},

							{

								"Code": "C09",

								"Quantity": ' . $child . '

							},

							{

								"Code": "INF",

								"Quantity": ' . $infants . '

							}';

        } else if ($adult > 0 && $child > 0) {

            $SabreRequest = '{

									"Code": "ADT",

									"Quantity": ' . $adult . '

								},

								{

									"Code": "C09",

									"Quantity": ' . $child . '

								}';

        } else if ($adult > 0 && $infants > 0) {

            $SabreRequest = '{

								"Code": "ADT",

								"Quantity": ' . $adult . '

								},

								{

									"Code": "INF",

									"Quantity": ' . $infants . '

								}';

        } else {

            $SabreRequest = '{

								"Code": "ADT",

								"Quantity": ' . $adult . '

							}';

        }

        //

        $CityCount = $_POST['CityCount'];

        if ($CityCount == '1') {

            print("You have to Chose More Than 2 City");

            exit();

        } else if ($CityCount == '2') {

            $City1DepDate = $_POST['segments'][0]['Date'];

            $City2DepDate = $_POST['segments'][1]['Date'];

            $City1DepFrom = $_POST['segments'][0]['DepFrom'];

            $City2DepFrom = $_POST['segments'][1]['DepFrom'];

            $City1ArrTo = $_POST['segments'][0]['ArrTo'];

            $City2ArrTo = $_POST['segments'][1]['ArrTo'];

            $DepCity = "$City1DepFrom , $City2DepFrom";

            $ArrCity = "$City1ArrTo , $City2ArrTo";

            $DepCityDate = "$City1DepDate, $City2DepDate";

            $SabreRequestFlight = '

								 {

									 "RPH": "1",

									 "DepartureDateTime": "' . $City1DepDate . 'T00:00:00",

									 "OriginLocation": {

										 "LocationCode": "' . $City1DepFrom . '"

									 },

									 "DestinationLocation": {

										 "LocationCode": "' . $City1ArrTo . '"

									 }

								 },{

									 "RPH": "2",

									 "DepartureDateTime": "' . $City2DepDate . 'T00:00:00",

									 "OriginLocation": {

										 "LocationCode": "' . $City2DepFrom . '"

									 },

									 "DestinationLocation": {

										 "LocationCode": "' . $City2ArrTo . '"

									 }

								 }';

            $FlyHubSegment = '[{

										 "Origin": "' . $City1DepFrom . '",

										 "Destination": "' . $City1ArrTo . '",

										 "CabinClass": "Economy",

										 "DepartureDateTime": "' . $City1DepDate . '"

									 },

									 {

										 "Origin": "' . $City2DepFrom . '",

										 "Destination": "' . $City2ArrTo . '",

										 "CabinClass": "Economy",

										 "DepartureDateTime": "' . $City2DepDate . '"

									 }]';

        } else if ($CityCount == '3') {

            $City1DepDate = $_POST['segments'][0]['Date'];

            $City2DepDate = $_POST['segments'][1]['Date'];

            $City3DepDate = $_POST['segments'][2]['Date'];

            $City1DepFrom = $_POST['segments'][0]['DepFrom'];

            $City2DepFrom = $_POST['segments'][1]['DepFrom'];

            $City3DepFrom = $_POST['segments'][2]['DepFrom'];

            $City1ArrTo = $_POST['segments'][0]['ArrTo'];

            $City2ArrTo = $_POST['segments'][1]['ArrTo'];

            $City3ArrTo = $_POST['segments'][2]['ArrTo'];

            $DepCity = "$City1DepFrom , $City2DepFrom, $City3DepFrom";

            $ArrCity = "$City1ArrTo , $City2ArrTo,  $City3ArrTo";

            $DepCityDate = "$City1DepDate, $City2DepDate, $City3DepDate";

            $SabreRequestFlight = '

								 {

									 "RPH": "1",

									 "DepartureDateTime": "' . $City1DepDate . 'T00:00:00",

									 "OriginLocation": {

										 "LocationCode": "' . $City1DepFrom . '"

									 },

									 "DestinationLocation": {

										 "LocationCode": "' . $City1ArrTo . '"

									 }

								 },{

									 "RPH": "2",

									 "DepartureDateTime": "' . $City2DepDate . 'T00:00:00",

									 "OriginLocation": {

										 "LocationCode": "' . $City2DepFrom . '"

									 },

									 "DestinationLocation": {

										 "LocationCode": "' . $City2ArrTo . '"

									 }

								 },

								 {

									 "RPH": "3",

									 "DepartureDateTime": "' . $City2DepDate . 'T00:00:00",

									 "OriginLocation": {

										 "LocationCode": "' . $City3DepFrom . '"

									 },

									 "DestinationLocation": {

										 "LocationCode": "' . $City3ArrTo . '"

									 }

								 }';

        } else if ($CityCount == '4') {

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

            $DepCity = "$City1DepFrom , $City2DepFrom, $City3DepFrom, $City4DepFrom";

            $ArrCity = "$City1ArrTo , $City2ArrTo,  $City3ArrTo, $City4ArrTo";

            $DepCityDate = "$City1DepDate, $City2DepDate, $City3DepDate, $City4DepDate";

            $SabreRequestFlight = '

								 {

									 "RPH": "1",

									 "DepartureDateTime": "' . $City1DepDate . 'T00:00:00",

									 "OriginLocation": {

										 "LocationCode": "' . $City1DepFrom . '"

									 },

									 "DestinationLocation": {

										 "LocationCode": "' . $City1ArrTo . '"

									 }

								 },{

									 "RPH": "2",

									 "DepartureDateTime": "' . $City2DepDate . 'T00:00:00",

									 "OriginLocation": {

										 "LocationCode": "' . $City2DepFrom . '"

									 },

									 "DestinationLocation": {

										 "LocationCode": "' . $City2ArrTo . '"

									 }

								 },

								 {

									 "RPH": "3",

									 "DepartureDateTime": "' . $City3DepDate . 'T00:00:00",

									 "OriginLocation": {

										 "LocationCode": "' . $City3DepFrom . '"

									 },

									 "DestinationLocation": {

										 "LocationCode": "' . $City3ArrTo . '"

									 }

								 },{

									 "RPH": "4",

									 "DepartureDateTime": "' . $City4DepDate . 'T00:00:00",

									 "OriginLocation": {

										 "LocationCode": "' . $City4DepFrom . '"

									 },

									 "DestinationLocation": {

										 "LocationCode": "' . $City4ArrTo . '"

									 }

								 }';

        }

        //save search history

        //$conn->query("INSERT INTO search_histories(`AgentId`,`TripType`,`DepFrom`,`ArrTo`,`GoDate`,`created_at`)VALUE('$agentId','multicity', '$DepCity','$ArrCity','$DepCityDate', '$time')");

        $SabreRequestJSON = '{

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

						"OriginDestinationInformation": [' . $SabreRequestFlight . '],

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

									"PassengerTypeQuantity": [' . $SabreRequest . ']

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

        // echo $SabreRequestJSON;

        // Sabre Start

        
	try{

		$client_id= base64_encode("V1:396724:FD3K:AA");
		$client_secret = base64_encode("FlWy967"); //prod
		
		$token = base64_encode($client_id.":".$client_secret);
		
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'https://api.platform.sabre.com/v2/auth/token',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS =>'grant_type=client_credentials',
		  CURLOPT_HTTPHEADER => array(
			'Content-Type: application/x-www-form-urlencoded',
			"Authorization: Basic $token"
		  ),
		));
		$Tokenres = curl_exec($curl);
		curl_close($curl);
		$resToken = json_decode($Tokenres, true);
		$access_token = $resToken['access_token'];

		//echo $access_token;

	}catch (Exception $e){ 
		
	}

        $curl = curl_init();

        if (isset($access_token)) {

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

                CURLOPT_POSTFIELDS => $SabreRequestJSON,

                CURLOPT_HTTPHEADER => array(

                    'Content-Type: application/json',

                    'Conversation-ID: 2021.01.DevStudio',

                    'Authorization: Bearer ' . $access_token)));

            $response = curl_exec($curl);

            //echo $response;

            curl_close($curl);

            $result = json_decode($response, true);

            //print_r($result);

            if ($result['groupedItineraryResponse']['statistics']['itineraryCount'] > 0) {

                $itineraryGroups = $result['groupedItineraryResponse']['itineraryGroups'][0]['itineraries'];

                //print_r($itineraryGroups);

                $scheduleDescs = $result['groupedItineraryResponse']['scheduleDescs'];

                $legDescs = $result['groupedItineraryResponse']['legDescs'];

                $Bag = $result['groupedItineraryResponse']['baggageAllowanceDescs'];

                foreach ($itineraryGroups as $itineraries) {

                    $BasePath = $itineraries['pricingInformation'][0]['fare'];

                    $vCarCode = $BasePath['validatingCarrierCode'];

                    $city1verningCarriers = $BasePath['governingCarriers'];

                    $totalPrice = $BasePath['totalFare']['totalPrice'];

                    $totalTaxAmount = $BasePath['totalFare']['totalTaxAmount'];

                    $totalBaseAmount = $BasePath['totalFare']['equivalentAmount'];

                    if ($BasePath['passengerInfoList'][0]['passengerInfo']['nonRefundable'] == 1) {

                        $nonRefundable = "nonRefundable";

                    } else {

                        $nonRefundable = "Refundable";

                    }

                    if (isset($itineraries['pricingInformation'][0]['fare']['lastTicketDate'])

                        && isset($itineraries['pricingInformation'][0]['fare']['lastTicketTime'])) {

                        $lastTicketDate = $itineraries['pricingInformation'][0]['fare']['lastTicketDate'];

                        $lastTicketTime = $itineraries['pricingInformation'][0]['fare']['lastTicketTime'];

                        $timelimit = "$lastTicketDate $lastTicketTime";

                    } else {

                        $timelimit = " ";

                    }

                    $sql = mysqli_query($conn, "SELECT nameBangla, name, commission FROM airlines WHERE code='$vCarCode' ");

                    $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

                    if (!empty($row)) {

                        $CarrieerName = $row['name'];

                        $fareRate = $row['commission'];

                    }

                    $agentPrice = floor((($totalBaseAmount * 0.93) + $totalTaxAmount) + ($totalPrice * 0.003));

                    $Commission = $totalPrice - $agentPrice;

                    //Price Breakdown

                    $adultPassengerInfo = $BasePath['passengerInfoList'][0]['passengerInfo'];

                    if ($adult > 0 && $child > 0 && $infants > 0) {

                        $childPassengerInfo = $BasePath['passengerInfoList'][1]['passengerInfo'];

                        $infantPassengerInfo = $BasePath['passengerInfoList'][2]['passengerInfo'];

                        $adultbaseFareAmount = $adultPassengerInfo['passengerTotalFare']['equivalentAmount'];

                        $adultTotalTaxAmount = $adultPassengerInfo['passengerTotalFare']['totalTaxAmount'];

                        $adultTotalFare = $adultPassengerInfo['passengerTotalFare']['totalFare'];

                        $childbaseFareAmount = $childPassengerInfo['passengerTotalFare']['equivalentAmount'];

                        $childTotalTaxAmount = $childPassengerInfo['passengerTotalFare']['totalTaxAmount'];

                        $childTotalFare = $childPassengerInfo['passengerTotalFare']['totalFare'];

                        $infantbaseFareAmount = $infantPassengerInfo['passengerTotalFare']['equivalentAmount'];

                        $infantTotalTaxAmount = $infantPassengerInfo['passengerTotalFare']['totalTaxAmount'];

                        $infantTotalFare = $infantPassengerInfo['passengerTotalFare']['totalFare'];

                        $PriceBreakDown = array("0" => array("BaseFare" => "$adultbaseFareAmount",

                            "Tax" => "$adultTotalTaxAmount",

                            "PaxCount" => $adult,

                            "PaxType" => "ADT")

                            ,

                            "1" => array("BaseFare" => "$childbaseFareAmount",

                                "Tax" => "$adultTotalTaxAmount",

                                "PaxCount" => $child,

                                "PaxType" => "CNN"),

                            "2" => array("BaseFare" => "$infantbaseFareAmount",

                                "Tax" => "$infantTotalTaxAmount",

                                "PaxCount" => $infants,

                                "PaxType" => "INF"),

                        );

                        if ($CityCount == 2) {

                            //City 1

                            $City1AdultBagRef = $adultPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            $City1ChildBagRef = $childPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            $City1InfantBagRef = $infantPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            //City 2

                            $City2AdultBagRef = $adultPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            $City2ChildBagRef = $childPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            $City2InfantBagRef = $infantPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            $City1AdultBag = isset($Bag[$City1AdultBagRef - 1]['weight']) ? $Bag[$City1AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City1ChildBag = isset($Bag[$City1ChildBagRef - 1]['weight']) ? $Bag[$City1ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1ChildBagRef - 1]['pieceCount'] . 'Piece';

                            $City1InfantBag = isset($Bag[$City1InfantBagRef - 1]['weight']) ? $Bag[$City1InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1InfantBagRef - 1]['pieceCount'] . 'Piece';

                            $City2AdultBag = isset($Bag[$City2AdultBagRef - 1]['weight']) ? $Bag[$City2AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City2ChildBag = isset($Bag[$City2ChildBagRef - 1]['weight']) ? $Bag[$City2ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2ChildBagRef - 1]['pieceCount'] . 'Piece';

                            $City2InfantBag = isset($Bag[$City2InfantBagRef - 1]['weight']) ? $Bag[$City2InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2InfantBagRef - 1]['pieceCount'] . 'Piece';

                            $Baggage = array("0" => array(

                                "adultBag" => $City1AdultBag,

                                "childBag" => $City1ChildBag,

                                "infantBag" => $City1InfantBag),

                                "1" => array(

                                    "adultBag" => $City2AdultBag,

                                    "childBag" => $City2ChildBag,

                                    "infantBag" => $City2InfantBag),

                            );

                        } else if ($CityCount == 3) {

                            //City 1

                            $City1AdultBagRef = $adultPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            $City1ChildBagRef = $childPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            $City1InfantBagRef = $infantPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            //City 2

                            $City2AdultBagRef = $adultPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            $City2ChildBagRef = $childPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            $City2InfantBagRef = $infantPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            // City 3

                            $City3AdultBagRef = $adultPassengerInfo['baggageInformation'][2]['allowance']['ref'];

                            $City3ChildBagRef = $childPassengerInfo['baggageInformation'][2]['allowance']['ref'];

                            $City3InfantBagRef = $infantPassengerInfo['baggageInformation'][2]['allowance']['ref'];

                            //City 1 Baggage Info

                            $City1AdultBag = isset($Bag[$City1AdultBagRef - 1]['weight']) ? $Bag[$City1AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City1ChildBag = isset($Bag[$City1ChildBagRef - 1]['weight']) ? $Bag[$City1ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1ChildBagRef - 1]['pieceCount'] . 'Piece';

                            $City1InfantBag = isset($Bag[$City1InfantBagRef - 1]['weight']) ? $Bag[$City1InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1InfantBagRef - 1]['pieceCount'] . 'Piece';

                            //City 2 Baggage Info

                            $City2AdultBag = isset($Bag[$City2AdultBagRef - 1]['weight']) ? $Bag[$City2AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City2ChildBag = isset($Bag[$City2ChildBagRef - 1]['weight']) ? $Bag[$City2ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2ChildBagRef - 1]['pieceCount'] . 'Piece';

                            $City2InfantBag = isset($Bag[$City2InfantBagRef - 1]['weight']) ? $Bag[$City2InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2InfantBagRef - 1]['pieceCount'] . 'Piece';

                            //City 3 Baggage Info

                            $City3AdultBag = isset($Bag[$City3AdultBagRef - 1]['weight']) ? $Bag[$City3AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City3AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City3ChildBag = isset($Bag[$City3ChildBagRef - 1]['weight']) ? $Bag[$City3ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City3ChildBagRef - 1]['pieceCount'] . 'Piece';

                            $City3InfantBag = isset($Bag[$City3InfantBagRef - 1]['weight']) ? $Bag[$City3InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City3InfantBagRef - 1]['pieceCount'] . 'Piece';

                            $Baggage = array("0" => array(

                                "adultBag" => $City1AdultBag,

                                "childBag" => $City1ChildBag,

                                "infantBag" => $City1InfantBag),

                                "1" => array(

                                    "adultBag" => $City2AdultBag,

                                    "childBag" => $City2ChildBag,

                                    "infantBag" => $City2InfantBag),

                                "2" => array(

                                    "adultBag" => $City3AdultBag,

                                    "childBag" => $City3ChildBag,

                                    "infantBag" => $City3InfantBag),

                            );

                        } else if ($CityCount == 4) {

                            //City 1

                            $City1AdultBagRef = $adultPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            $City1ChildBagRef = $childPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            $City1InfantBagRef = $infantPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            //City 2

                            $City2AdultBagRef = $adultPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            $City2ChildBagRef = $childPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            $City2InfantBagRef = $infantPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            // City 3

                            $City3AdultBagRef = $adultPassengerInfo['baggageInformation'][2]['allowance']['ref'];

                            $City3ChildBagRef = $childPassengerInfo['baggageInformation'][2]['allowance']['ref'];

                            $City3InfantBagRef = $infantPassengerInfo['baggageInformation'][2]['allowance']['ref'];

                            // City 4

                            $City4AdultBagRef = $adultPassengerInfo['baggageInformation'][3]['allowance']['ref'];

                            $City4ChildBagRef = $childPassengerInfo['baggageInformation'][3]['allowance']['ref'];

                            $City4InfantBagRef = $infantPassengerInfo['baggageInformation'][3]['allowance']['ref'];

                            //City 1 Baggage Info

                            $City1AdultBag = isset($Bag[$City1AdultBagRef - 1]['weight']) ? $Bag[$City1AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City1ChildBag = isset($Bag[$City1ChildBagRef - 1]['weight']) ? $Bag[$City1ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1ChildBagRef - 1]['pieceCount'] . 'Piece';

                            $City1InfantBag = isset($Bag[$City1InfantBagRef - 1]['weight']) ? $Bag[$City1InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1InfantBagRef - 1]['pieceCount'] . 'Piece';

                            //City 2 Baggage Info

                            $City2AdultBag = isset($Bag[$City2AdultBagRef - 1]['weight']) ? $Bag[$City2AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City2ChildBag = isset($Bag[$City2ChildBagRef - 1]['weight']) ? $Bag[$City2ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2ChildBagRef - 1]['pieceCount'] . 'Piece';

                            $City2InfantBag = isset($Bag[$City2InfantBagRef - 1]['weight']) ? $Bag[$City2InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2InfantBagRef - 1]['pieceCount'] . 'Piece';

                            //City 3 Baggage Info

                            $City3AdultBag = isset($Bag[$City3AdultBagRef - 1]['weight']) ? $Bag[$City3AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City3AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City3ChildBag = isset($Bag[$City3ChildBagRef - 1]['weight']) ? $Bag[$City3ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City3ChildBagRef - 1]['pieceCount'] . 'Piece';

                            $City3InfantBag = isset($Bag[$City3InfantBagRef - 1]['weight']) ? $Bag[$City3InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City3InfantBagRef - 1]['pieceCount'] . 'Piece';

                            //City 4 Baggage Info

                            $City4AdultBag = isset($Bag[$City4AdultBagRef - 1]['weight']) ? $Bag[$City4AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City4AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City4ChildBag = isset($Bag[$City4ChildBagRef - 1]['weight']) ? $Bag[$City4ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City4ChildBagRef - 1]['pieceCount'] . 'Piece';

                            $City4InfantBag = isset($Bag[$City4InfantBagRef - 1]['weight']) ? $Bag[$City4InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City4InfantBagRef - 1]['pieceCount'] . 'Piece';

                            $Baggage = array(

                                "0" => array(

                                    "adultBag" => $City1AdultBag,

                                    "childBag" => $City1ChildBag,

                                    "infantBag" => $City1InfantBag),

                                "1" => array(

                                    "adultBag" => $City2AdultBag,

                                    "childBag" => $City2ChildBag,

                                    "infantBag" => $City2InfantBag),

                                "2" => array(

                                    "adultBag" => $City3AdultBag,

                                    "childBag" => $City3ChildBag,

                                    "infantBag" => $City3InfantBag),

                                "3" => array(

                                    "adultBag" => $City4AdultBag,

                                    "childBag" => $City4ChildBag,

                                    "infantBag" => $City4InfantBag),

                            );

                        }

                    } else if ($adult > 0 && $child > 0) {

                        $childPassengerInfo = $BasePath['passengerInfoList'][1]['passengerInfo'];

                        $adultbaseFareAmount = $adultPassengerInfo['passengerTotalFare']['equivalentAmount'];

                        $adultTotalTaxAmount = $adultPassengerInfo['passengerTotalFare']['totalTaxAmount'];

                        $adultTotalFare = $adultPassengerInfo['passengerTotalFare']['totalFare'];

                        $childbaseFareAmount = $childPassengerInfo['passengerTotalFare']['equivalentAmount'];

                        $childTotalTaxAmount = $childPassengerInfo['passengerTotalFare']['totalTaxAmount'];

                        $childTotalFare = $childPassengerInfo['passengerTotalFare']['totalFare'];

                        $PriceBreakDown = array("0" => array("BaseFare" => "$adultbaseFareAmount",

                            "Tax" => "$adultTotalTaxAmount",

                            "PaxCount" => $adult,

                            "PaxType" => "ADT")

                            ,

                            "1" => array("BaseFare" => "$childbaseFareAmount",

                                "Tax" => "$adultTotalTaxAmount",

                                "PaxCount" => $child,

                                "PaxType" => "CNN"),

                        );

                        if ($CityCount == 2) {

                            //City 1

                            $City1AdultBagRef = $adultPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            $City1ChildBagRef = $childPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            //City 2

                            $City2AdultBagRef = $adultPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            $City2ChildBagRef = $childPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            $City1AdultBag = isset($Bag[$City1AdultBagRef - 1]['weight']) ? $Bag[$City1AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City1ChildBag = isset($Bag[$City1ChildBagRef - 1]['weight']) ? $Bag[$City1ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1ChildBagRef - 1]['pieceCount'] . 'Piece';

                            $City2AdultBag = isset($Bag[$City2AdultBagRef - 1]['weight']) ? $Bag[$City2AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City2ChildBag = isset($Bag[$City2ChildBagRef - 1]['weight']) ? $Bag[$City2ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2ChildBagRef - 1]['pieceCount'] . 'Piece';

                            $Baggage = array("0" => array(

                                "adultBag" => $City1AdultBag,

                                "childBag" => $City1ChildBag),

                                "1" => array(

                                    "adultBag" => $City1AdultBag,

                                    "childBag" => $City2ChildBag),

                            );

                        } else if ($CityCount == 3) {

                            //City 1

                            $City1AdultBagRef = $adultPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            $City1ChildBagRef = $childPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            //City 2

                            $City2AdultBagRef = $adultPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            $City2ChildBagRef = $childPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            // City 3

                            $City3AdultBagRef = $adultPassengerInfo['baggageInformation'][2]['allowance']['ref'];

                            $City3ChildBagRef = $childPassengerInfo['baggageInformation'][2]['allowance']['ref'];

                            //City 1 Baggage Info

                            $City1AdultBag = isset($Bag[$City1AdultBagRef - 1]['weight']) ? $Bag[$City1AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City1ChildBag = isset($Bag[$City1ChildBagRef - 1]['weight']) ? $Bag[$City1ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1ChildBagRef - 1]['pieceCount'] . 'Piece';

                            //City 2 Baggage Info

                            $City2AdultBag = isset($Bag[$City2AdultBagRef - 1]['weight']) ? $Bag[$City2AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City2ChildBag = isset($Bag[$City2ChildBagRef - 1]['weight']) ? $Bag[$City2ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2ChildBagRef - 1]['pieceCount'] . 'Piece';

                            //City 3 Baggage Info

                            $City3AdultBag = isset($Bag[$City3AdultBagRef - 1]['weight']) ? $Bag[$City3AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City3AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City3ChildBag = isset($Bag[$City3ChildBagRef - 1]['weight']) ? $Bag[$City3ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City3ChildBagRef - 1]['pieceCount'] . 'Piece';

                            $Baggage = array("0" => array(

                                "adultBag" => $City1AdultBag,

                                "childBag" => $City1ChildBag),

                                "1" => array(

                                    "adultBag" => $City2AdultBag,

                                    "childBag" => $City2ChildBag),

                                "2" => array(

                                    "adultBag" => $City2AdultBag,

                                    "childBag" => $City2ChildBag),

                            );

                        } else if ($CityCount == 4) {

                            //City 1

                            $City1AdultBagRef = $adultPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            $City1ChildBagRef = $childPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            //City 2

                            $City2AdultBagRef = $adultPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            $City2ChildBagRef = $childPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            // City 3

                            $City3AdultBagRef = $adultPassengerInfo['baggageInformation'][2]['allowance']['ref'];

                            $City3ChildBagRef = $childPassengerInfo['baggageInformation'][2]['allowance']['ref'];

                            // City 4

                            $City4AdultBagRef = $adultPassengerInfo['baggageInformation'][3]['allowance']['ref'];

                            $City4ChildBagRef = $childPassengerInfo['baggageInformation'][3]['allowance']['ref'];

                            //City 1 Baggage Info

                            $City1AdultBag = isset($Bag[$City1AdultBagRef - 1]['weight']) ? $Bag[$City1AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City1ChildBag = isset($Bag[$City1ChildBagRef - 1]['weight']) ? $Bag[$City1ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1ChildBagRef - 1]['pieceCount'] . 'Piece';

                            //City 2 Baggage Info

                            $City2AdultBag = isset($Bag[$City2AdultBagRef - 1]['weight']) ? $Bag[$City2AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City2ChildBag = isset($Bag[$City2ChildBagRef - 1]['weight']) ? $Bag[$City2ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2ChildBagRef - 1]['pieceCount'] . 'Piece';

                            //City 3 Baggage Info

                            $City3AdultBag = isset($Bag[$City3AdultBagRef - 1]['weight']) ? $Bag[$City3AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City3AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City3ChildBag = isset($Bag[$City3ChildBagRef - 1]['weight']) ? $Bag[$City3ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City3ChildBagRef - 1]['pieceCount'] . 'Piece';

                            //City 4 Baggage Info

                            $City4AdultBag = isset($Bag[$City4AdultBagRef - 1]['weight']) ? $Bag[$City4AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City4AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City4ChildBag = isset($Bag[$City4ChildBagRef - 1]['weight']) ? $Bag[$City4ChildBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City4ChildBagRef - 1]['pieceCount'] . 'Piece';

                            $Baggage = array(

                                "0" => array(

                                    "adultBag" => $City1AdultBag,

                                    "childBag" => $City1ChildBag),

                                "1" => array(

                                    "adultBag" => $City2AdultBag,

                                    "childBag" => $City2ChildBag),

                                "2" => array(

                                    "adultBag" => $City3AdultBag,

                                    "childBag" => $City3ChildBag),

                                "3" => array(

                                    "adultBag" => $City4AdultBag,

                                    "childBag" => $City4ChildBag,

                                ),

                            );

                        }

                    } else if ($adult > 0 && $infants > 0) {

                        $infantPassengerInfo = $BasePath['passengerInfoList'][1]['passengerInfo'];

                        $adultbaseFareAmount = $adultPassengerInfo['passengerTotalFare']['equivalentAmount'];

                        $adultTotalTaxAmount = $adultPassengerInfo['passengerTotalFare']['totalTaxAmount'];

                        $adultTotalFare = $adultPassengerInfo['passengerTotalFare']['totalFare'];

                        $infantbaseFareAmount = $infantPassengerInfo['passengerTotalFare']['equivalentAmount'];

                        $infantTotalTaxAmount = $infantPassengerInfo['passengerTotalFare']['totalTaxAmount'];

                        $infantTotalFare = $infantPassengerInfo['passengerTotalFare']['totalFare'];

                        $PriceBreakDown = array("0" => array("BaseFare" => "$adultbaseFareAmount",

                            "Tax" => "$adultTotalTaxAmount",

                            "PaxCount" => $adult,

                            "PaxType" => "ADT"),

                            "1" => array("BaseFare" => "$infantbaseFareAmount",

                                "Tax" => "$infantTotalTaxAmount",

                                "PaxCount" => $infants,

                                "PaxType" => "INF"),

                        );

                        if ($CityCount == 2) {

                            //City 1

                            $City1AdultBagRef = $adultPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            $City1InfantBagRef = $infantPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            //City 2

                            $City2AdultBagRef = $adultPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            $City2InfantBagRef = $infantPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            $City1AdultBag = isset($Bag[$City1AdultBagRef - 1]['weight']) ? $Bag[$City1AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City1InfantBag = isset($Bag[$City1InfantBagRef - 1]['weight']) ? $Bag[$City1InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1InfantBagRef - 1]['pieceCount'] . 'Piece';

                            $City2AdultBag = isset($Bag[$City2AdultBagRef - 1]['weight']) ? $Bag[$City2AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City2InfantBag = isset($Bag[$City2InfantBagRef - 1]['weight']) ? $Bag[$City2InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2InfantBagRef - 1]['pieceCount'] . 'Piece';

                            $Baggage = array("0" => array(

                                "adultBag" => $City1AdultBag,

                                "infantBag" => $City1InfantBag),

                                "1" => array(

                                    "adultBag" => $City2AdultBag,

                                    "infantBag" => $City2InfantBag),

                            );

                        } else if ($CityCount == 3) {

                            //City 1

                            $City1AdultBagRef = $adultPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            $City1InfantBagRef = $infantPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            //City 2

                            $City2AdultBagRef = $adultPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            $City2InfantBagRef = $infantPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            // City 3

                            $City3AdultBagRef = $adultPassengerInfo['baggageInformation'][2]['allowance']['ref'];

                            $City3InfantBagRef = $infantPassengerInfo['baggageInformation'][2]['allowance']['ref'];

                            //City 1 Baggage Info

                            $City1AdultBag = isset($Bag[$City1AdultBagRef - 1]['weight']) ? $Bag[$City1AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City1InfantBag = isset($Bag[$City1InfantBagRef - 1]['weight']) ? $Bag[$City1InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1InfantBagRef - 1]['pieceCount'] . 'Piece';

                            //City 2 Baggage Info

                            $City2AdultBag = isset($Bag[$City2AdultBagRef - 1]['weight']) ? $Bag[$City2AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City2InfantBag = isset($Bag[$City2InfantBagRef - 1]['weight']) ? $Bag[$City2InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2InfantBagRef - 1]['pieceCount'] . 'Piece';

                            //City 3 Baggage Info

                            $City3AdultBag = isset($Bag[$City3AdultBagRef - 1]['weight']) ? $Bag[$City3AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City3AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City3InfantBag = isset($Bag[$City3InfantBagRef - 1]['weight']) ? $Bag[$City3InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City3InfantBagRef - 1]['pieceCount'] . 'Piece';

                            $Baggage = array("0" => array(

                                "adultBag" => $City1AdultBag,

                                "infantBag" => $City1InfantBag),

                                "1" => array(

                                    "adultBag" => $City2AdultBag,

                                    "infantBag" => $City2InfantBag),

                                "2" => array(

                                    "adultBag" => $City3AdultBag,

                                    "infantBag" => $City3InfantBag),

                            );

                        } else if ($CityCount == 4) {

                            //City 1

                            $City1AdultBagRef = $adultPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            $City1InfantBagRef = $infantPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            //City 2

                            $City2AdultBagRef = $adultPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            $City2InfantBagRef = $infantPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            // City 3

                            $City3AdultBagRef = $adultPassengerInfo['baggageInformation'][2]['allowance']['ref'];

                            $City3InfantBagRef = $infantPassengerInfo['baggageInformation'][2]['allowance']['ref'];

                            // City 4

                            $City4AdultBagRef = $adultPassengerInfo['baggageInformation'][3]['allowance']['ref'];

                            $City4InfantBagRef = $infantPassengerInfo['baggageInformation'][3]['allowance']['ref'];

                            //City 1 Baggage Info

                            $City1AdultBag = isset($Bag[$City1AdultBagRef - 1]['weight']) ? $Bag[$City1AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City1InfantBag = isset($Bag[$City1InfantBagRef - 1]['weight']) ? $Bag[$City1InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1InfantBagRef - 1]['pieceCount'] . 'Piece';

                            //City 2 Baggage Info

                            $City2AdultBag = isset($Bag[$City2AdultBagRef - 1]['weight']) ? $Bag[$City2AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City2InfantBag = isset($Bag[$City2InfantBagRef - 1]['weight']) ? $Bag[$City2InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2InfantBagRef - 1]['pieceCount'] . 'Piece';

                            //City 3 Baggage Info

                            $City3AdultBag = isset($Bag[$City3AdultBagRef - 1]['weight']) ? $Bag[$City3AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City3AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City3InfantBag = isset($Bag[$City3InfantBagRef - 1]['weight']) ? $Bag[$City3InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City3InfantBagRef - 1]['pieceCount'] . 'Piece';

                            //City 4 Baggage Info

                            $City4AdultBag = isset($Bag[$City4AdultBagRef - 1]['weight']) ? $Bag[$City4AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City4AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City4InfantBag = isset($Bag[$City4InfantBagRef - 1]['weight']) ? $Bag[$City4InfantBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City4InfantBagRef - 1]['pieceCount'] . 'Piece';

                            $Baggage = array(

                                "0" => array(

                                    "adultBag" => $City1AdultBag,

                                    "infantBag" => $City1InfantBag),

                                "1" => array(

                                    "adultBag" => $City2AdultBag,

                                    "infantBag" => $City2InfantBag),

                                "2" => array(

                                    "adultBag" => $City3AdultBag,

                                    "infantBag" => $City3InfantBag),

                                "3" => array(

                                    "adultBag" => $City4AdultBag,

                                    "infantBag" => $City4InfantBag),

                            );

                        }

                    } else if ($adult > 0) {

                        $adultbaseFareAmount = $adultPassengerInfo['passengerTotalFare']['equivalentAmount'];

                        $adultTotalTaxAmount = $adultPassengerInfo['passengerTotalFare']['totalTaxAmount'];

                        $adultTotalFare = $adultPassengerInfo['passengerTotalFare']['totalFare'];

                        $PriceBreakDown = array("0" => array("BaseFare" => "$adultbaseFareAmount",

                            "Tax" => "$adultTotalTaxAmount",

                            "PaxCount" => $adult,

                            "PaxType" => "ADT"),

                        );

                        if ($CityCount == 2) {

                            //City 1

                            $City1AdultBagRef = $adultPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            //City 2

                            $City2AdultBagRef = $adultPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            $City1AdultBag = isset($Bag[$City1AdultBagRef - 1]['weight']) ? $Bag[$City1AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $City2AdultBag = isset($Bag[$City2AdultBagRef - 1]['weight']) ? $Bag[$City2AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $Baggage = array("0" => array(

                                "adultBag" => $City1AdultBag),

                                "1" => array(

                                    "adultBag" => $City1AdultBag),

                            );

                        } else if ($CityCount == 3) {

                            //City 1

                            $City1AdultBagRef = $adultPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            //City 2

                            $City2AdultBagRef = $adultPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            // City 3

                            $City3AdultBagRef = $adultPassengerInfo['baggageInformation'][1]['allowance']['ref'];

                            //City 1 Baggage Info

                            $City1AdultBag = isset($Bag[$City1AdultBagRef - 1]['weight']) ? $Bag[$City1AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1AdultBagRef - 1]['pieceCount'] . 'Piece';

                            //City 2 Baggage Info

                            $City2AdultBag = isset($Bag[$City2AdultBagRef - 1]['weight']) ? $Bag[$City2AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2AdultBagRef - 1]['pieceCount'] . 'Piece';

                            //City 3 Baggage Info

                            $City3AdultBag = isset($Bag[$City3AdultBagRef - 1]['weight']) ? $Bag[$City3AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City3AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $Baggage = array("0" => array(

                                "adultBag" => $City1AdultBag),

                                "1" => array(

                                    "adultBag" => $City2AdultBag),

                                "2" => array(

                                    "adultBag" => $City2AdultBag),

                            );

                        } else if ($CityCount == 4) {

                            //City 1

                            $City1AdultBagRef = $adultPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            //City 2

                            $City2AdultBagRef = isset($adultPassengerInfo['baggageInformation'][1]['allowance']['ref']) ?

                            $adultPassengerInfo['baggageInformation'][1]['allowance']['ref'] :

                            $adultPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            // City 3

                            $City3AdultBagRef = isset($adultPassengerInfo['baggageInformation'][2]['allowance']['ref']) ?

                            $adultPassengerInfo['baggageInformation'][2]['allowance']['ref'] :

                            $adultPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            // City 4

                            $City4AdultBagRef = isset($adultPassengerInfo['baggageInformation'][3]['allowance']['ref']) ?

                            $adultPassengerInfo['baggageInformation'][2]['allowance']['ref'] :

                            $adultPassengerInfo['baggageInformation'][0]['allowance']['ref'];

                            //City 1 Baggage Info

                            $City1AdultBag = isset($Bag[$City1AdultBagRef - 1]['weight']) ? $Bag[$City1AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City1AdultBagRef - 1]['pieceCount'] . 'Piece';

                            //City 2 Baggage Info

                            $City2AdultBag = isset($Bag[$City2AdultBagRef - 1]['weight']) ? $Bag[$City2AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City2AdultBagRef - 1]['pieceCount'] . 'Piece';

                            //City 3 Baggage Info

                            $City3AdultBag = isset($Bag[$City3AdultBagRef - 1]['weight']) ? $Bag[$City3AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City3AdultBagRef - 1]['pieceCount'] . 'Piece';

                            //City 4 Baggage Info

                            $City4AdultBag = isset($Bag[$City4AdultBagRef - 1]['weight']) ? $Bag[$City4AdultBagRef - 1]['weight'] . 'KG'

                            : $Bag[$City4AdultBagRef - 1]['pieceCount'] . 'Piece';

                            $Baggage = array(

                                "0" => array(

                                    "adultBag" => $City1AdultBag),

                                "1" => array(

                                    "adultBag" => $City2AdultBag),

                                "2" => array(

                                    "adultBag" => $City3AdultBag),

                                "3" => array(

                                    "adultBag" => $City4AdultBag),

                            );

                        }

                    }

                    //print_r($Baggage);

                    $City1segmentList = $adultPassengerInfo['fareComponents'][0]['segments'];

                    $City2segmentList = $adultPassengerInfo['fareComponents'][0]['segments'];

                    $City3segmentList = $adultPassengerInfo['fareComponents'][0]['segments'];

                    $City4segmentList = $adultPassengerInfo['fareComponents'][0]['segments'];

                    $passengerInfo = $itineraries['pricingInformation'][0]['fare']['passengerInfoList'][0]['passengerInfo'];

                    $fareComponents = $passengerInfo['fareComponents'];

                    if ($CityCount == 2) {

                        //City 1

                        $ref1 = $itineraries['legs'][0]['ref'];

                        $id1 = $ref1 - 1;

                        //Return

                        $ref2 = $itineraries['legs'][1]['ref'];

                        $id2 = $ref2 - 1;

                        //Segment Count

                        $sgCount1 = count($legDescs[$id1]['schedules']); //echo $sgCount1;

                        $sgCount2 = count($legDescs[$id2]['schedules']); //echo $sgCount2;

                        //Go Flight Duration 1

                        $city1TotalElapesd = $legDescs[$id1]['elapsedTime'];

                        //Back Flight Duration 1

                        $city2TotalElapesd = $legDescs[$id2]['elapsedTime'];

                        //City1

                        if ($sgCount1 == 1) {

                            //Go

                            $city1lf1 = $legDescs[$id1]['schedules'][0]['ref'];

                            $city1legrefs = $city1lf1 - 1;

                            $city1departureTime = substr($scheduleDescs[$city1legrefs]['departure']['time'], 0, 5);

                            $city1dpTime = date("D d M Y", strtotime($City1DepDate . " " . $city1departureTime));

                            $city1dpTimedate = $City1DepDate . "T" . $city1departureTime . ':00';

                            $city1ArrivalTime = substr($scheduleDescs[$city1legrefs]['arrival']['time'], 0, 5);

                            $city1arrivalDate = 0;

                            if (isset($scheduleDescs[$city1legrefs]['arrival']['dateAdjustment'])) {

                                $city1arrivalDate += 1;

                            }

                            if ($city1arrivalDate == 1) {

                                $city1aDate = date('Y-m-d', strtotime("+1 day", strtotime($City1DepDate)));

                            } else {

                                $city1aDate = $City1DepDate;

                            }

                            $city1arrTime = date("D d M Y", strtotime($city1aDate . " " . $city1ArrivalTime));

                            $city1arrTimedate = $city1aDate . "T" . $city1ArrivalTime . ':00';

                            $city1ArrivalTo = $scheduleDescs[$city1legrefs]['arrival']['airport'];

                            $city1DepartureFrom = $scheduleDescs[$city1legrefs]['departure']['airport'];

                            $city1markettingCarrier = $scheduleDescs[$city1legrefs]['carrier']['marketing'];

                            $city1Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city1markettingCarrier' ");

                            $city1Crrow = mysqli_fetch_array($city1Crsql, MYSQLI_ASSOC);

                            if (!empty($city1Crrow)) {

                                $city1markettingCarrierName = $city1Crrow['name'];

                            }

                            // Departure Country

                            $city1Deptsql = mysqli_query($conn, "$Airportsql code='$city1DepartureFrom' ");

                            $city1Deptrow = mysqli_fetch_array($city1Deptsql, MYSQLI_ASSOC);

                            if (!empty($city1Deptrow)) {

                                $city1dAirport = $city1Deptrow['name'];

                                $city1dCity = $city1Deptrow['cityName'];

                                $city1dCountry = $city1Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city1Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city1ArrivalTo' ");

                            $city1Arrrow = mysqli_fetch_array($city1Arrsql, MYSQLI_ASSOC);

                            if (!empty($city1Arrrow)) {

                                $city1aAirport = $city1Arrrow['name'];

                                $city1aCity = $city1Arrrow['cityName'];

                                $city1aCountry = $city1Arrrow['countryCode'];

                            }

                            $city1markettingFN = $scheduleDescs[$city1legrefs]['carrier']['marketingFlightNumber'];

                            $city1operatingCarrier = $scheduleDescs[$city1legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city1legrefs]['carrier']['operatingFlightNumber'])) {

                                $city1operatingFN = $scheduleDescs[$city1legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city1operatingFN = 1;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city1BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city1BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city1ElapsedTime = $legDescs[$id1]['elapsedTime'];

                            $city1TravelTime = floor($city1ElapsedTime / 60) . "H " . ($city1ElapsedTime - ((floor($city1ElapsedTime / 60)) * 60)) . "Min";

                            //transit Time

                            $transitDetails = array("0" => array("transit1" => "0"),

                                "1" => array("transit1" => "0"));

                            $City1Data = array("0" => array("marketingcareer" => "$city1markettingCarrier",

                                "marketingcareerName" => "$city1markettingCarrierName",

                                "marketingflight" => "$city1markettingFN",

                                "operatingcareer" => "$city1operatingCarrier",

                                "operatingflight" => "$city1operatingFN",

                                "departure" => "$city1DepartureFrom",

                                "departureAirport" => "$city1dAirport",

                                "departureLocation" => "$city1dCity , $city1dCountry",

                                "departureTime" => "$city1dpTimedate",

                                "arrival" => "$city1ArrivalTo",

                                "arrivalTime" => "$city1arrTimedate",

                                "arrivalAirport" => "$city1aAirport",

                                "arrivalLocation" => "$city1aCity , $city1aCountry",

                                "flightduration" => "$city1TravelTime",

                                "bookingcode" => "$city1BookingCode",

                                "seat" => "$city1Seat"),

                            );

                        } else if ($sgCount1 == 2) {

                            //Go 1

                            $city1lf1 = $legDescs[$id1]['schedules'][0]['ref'];

                            $city1legrefs = $city1lf1 - 1;

                            $city1departureTime = substr($scheduleDescs[$city1legrefs]['departure']['time'], 0, 5);

                            $city1dpTime = date("D d M Y", strtotime($City1DepDate . " " . $city1departureTime));

                            $city1dpTimedate = $City1DepDate . "T" . $city1departureTime . ':00';

                            $city1ArrivalTime = substr($scheduleDescs[$city1legrefs]['arrival']['time'], 0, 5);

                            $city1arrivalDate = 0;

                            if (isset($scheduleDescs[$city1legrefs]['arrival']['dateAdjustment'])) {

                                $city1arrivalDate += 1;

                            }

                            if ($city1arrivalDate == 1) {

                                $city1aDate = date('Y-m-d', strtotime("+1 day", strtotime($City1DepDate)));

                            } else {

                                $city1aDate = $City1DepDate;

                            }

                            $city1arrTime = date("D d M Y", strtotime($city1aDate . " " . $city1ArrivalTime));

                            $city1arrTimedate = $city1aDate . "T" . $city1ArrivalTime . ':00';

                            $city1ArrivalTo = $scheduleDescs[$city1legrefs]['arrival']['airport'];

                            $city1DepartureFrom = $scheduleDescs[$city1legrefs]['departure']['airport'];

                            $city1markettingCarrier = $scheduleDescs[$city1legrefs]['carrier']['marketing'];

                            $city1Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city1markettingCarrier' ");

                            $city1Crrow = mysqli_fetch_array($city1Crsql, MYSQLI_ASSOC);

                            if (!empty($city1Crrow)) {

                                $city1markettingCarrierName = $city1Crrow['name'];

                            }

                            // Departure Country

                            $city1Deptsql = mysqli_query($conn, "$Airportsql code='$city1DepartureFrom' ");

                            $city1Deptrow = mysqli_fetch_array($city1Deptsql, MYSQLI_ASSOC);

                            if (!empty($city1Deptrow)) {

                                $city1dAirport = $city1Deptrow['name'];

                                $city1dCity = $city1Deptrow['cityName'];

                                $city1dCountry = $city1Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city1Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city1ArrivalTo' ");

                            $city1Arrrow = mysqli_fetch_array($city1Arrsql, MYSQLI_ASSOC);

                            if (!empty($city1Arrrow)) {

                                $city1aAirport = $city1Arrrow['name'];

                                $city1aCity = $city1Arrrow['cityName'];

                                $city1aCountry = $city1Arrrow['countryCode'];

                            }

                            $city1markettingFN = $scheduleDescs[$city1legrefs]['carrier']['marketingFlightNumber'];

                            $city1operatingCarrier = $scheduleDescs[$city1legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city1legrefs]['carrier']['operatingFlightNumber'])) {

                                $city1operatingFN = $scheduleDescs[$city1legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city1operatingFN = $city1markettingFN;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city1BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city1BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city1ElapsedTime = $scheduleDescs[$city1legrefs]['elapsedTime'];

                            $city1TravelTime = floor($city1ElapsedTime / 60) . "H " . ($city1ElapsedTime - ((floor($city1ElapsedTime / 60)) * 60)) . "Min";

                            //Go 2

                            $city1lf2 = $legDescs[$id1]['schedules'][1]['ref'];

                            $city1legrefs2 = $city1lf2 - 1;

                            $city1DepartureDate1 = 0;

                            if (isset($legDescs[$id1]['schedules'][1]['departureDateAdjustment'])) {

                                $city1DepartureDate1 += 1;

                            }

                            if ($city1DepartureDate1 == 1) {

                                $city1depDate1 = date('Y-m-d', strtotime("+1 day", strtotime($City1DepDate)));

                            } else {

                                $city1depDate1 = $City1DepDate;

                            }

                            $city1departureTime1 = substr($scheduleDescs[$city1legrefs2]['departure']['time'], 0, 5);

                            $city1dpTime1 = date("D d M Y", strtotime($city1depDate1 . " " . $city1departureTime1));

                            $city1dpTimedate1 = $city1depDate1 . "T" . $city1departureTime1 . ':00';

                            $city1ArrivalTime1 = substr($scheduleDescs[$city1legrefs2]['arrival']['time'], 0, 5);

                            $city1arrivalDate1 = 0;

                            if (isset($scheduleDescs[$city1legrefs2]['arrival']['dateAdjustment'])) {

                                $city1arrivalDate1 += 1;

                            }

                            if ($city1arrivalDate1 == 1) {

                                $city1aDate1 = date('Y-m-d', strtotime("+1 day", strtotime($city1depDate1)));

                            } else {

                                $city1aDate1 = $city1depDate1;

                            }

                            $city1arrTime1 = date("D d M Y", strtotime($city1aDate1 . " " . $city1ArrivalTime1));

                            $city1arrTimedate1 = $city1aDate1 . "T" . $city1ArrivalTime1 . ':00';

                            $city1ArrivalTo1 = $scheduleDescs[$city1legrefs2]['arrival']['airport'];

                            $city1DepartureFrom1 = $scheduleDescs[$city1legrefs2]['departure']['airport'];

                            $city1markettingCarrier1 = $scheduleDescs[$city1legrefs2]['carrier']['marketing'];

                            $city1Crsql1 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city1markettingCarrier1' ");

                            $city1Crrow1 = mysqli_fetch_array($city1Crsql1, MYSQLI_ASSOC);

                            if (!empty($city1Crrow1)) {

                                $city1markettingCarrierName1 = $city1Crrow1['name'];

                            }

                            // Departure Country

                            $city1Deptsql1 = mysqli_query($conn, "$Airportsql code='$city1DepartureFrom1' ");

                            $city1Deptrow1 = mysqli_fetch_array($city1Deptsql1, MYSQLI_ASSOC);

                            if (!empty($city1Deptrow1)) {

                                $city1dAirport1 = $city1Deptrow1['name'];

                                $city1dCity1 = $city1Deptrow1['cityName'];

                                $city1dCountry1 = $city1Deptrow1['countryCode'];

                            }

                            // Arrival Country

                            $city1Arrsql1 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city1ArrivalTo1' ");

                            $city1Arrrow1 = mysqli_fetch_array($city1Arrsql1, MYSQLI_ASSOC);

                            if (!empty($city1Arrrow)) {

                                $city1aAirport1 = $city1Arrrow1['name'];

                                $city1aCity1 = $city1Arrrow1['cityName'];

                                $city1aCountry1 = $city1Arrrow1['countryCode'];

                            }

                            $city1markettingFN1 = $scheduleDescs[$city1legrefs2]['carrier']['marketingFlightNumber'];

                            $city1operatingCarrier1 = $scheduleDescs[$city1legrefs2]['carrier']['operating'];

                            if (isset($scheduleDescs[$city1legrefs2]['carrier']['operatingFlightNumber'])) {

                                $city1operatingFN1 = $scheduleDescs[$city1legrefs2]['carrier']['operatingFlightNumber'];

                            } else {

                                $city1operatingFN1 = $city1markettingFN1;

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat1 = $fareComponents[1]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat1 = "9";

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])) {

                                $city1BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city1BookingCode1 = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city1ElapsedTime1 = $scheduleDescs[$city1legrefs2]['elapsedTime'];

                            $city1TravelTime1 = floor($city1ElapsedTime1 / 60) . "H " . ($city1ElapsedTime1 - ((floor($city1ElapsedTime1 / 60)) * 60)) . "Min";

                            //Go Transit Time

                            $city1TransitTime = $city1TotalElapesd - ($city1ElapsedTime + $city1ElapsedTime1);

                            $city1TransitDuration = floor($city1TransitTime / 60) . "H " . ($city1TransitTime - ((floor($city1TransitTime / 60)) * 60)) . "Min";

                            $city1JourneyElapseTime = $city1TotalElapesd;

                            $city1JourneyDuration = floor($city1JourneyElapseTime / 60) . "H " . ($city1JourneyElapseTime - ((floor($city1JourneyElapseTime / 60)) * 60)) . "Min";

                            $City1Data = array(

                                "0" => array(

                                    "marketingcareer" => "$city1markettingCarrier",

                                    "marketingcareerName" => "$city1markettingCarrierName",

                                    "marketingflight" => "$city1markettingFN",

                                    "operatingcareer" => "$city1operatingCarrier",

                                    "operatingflight" => "$city1operatingFN",

                                    "departure" => "$city1DepartureFrom",

                                    "departureAirport" => "$city1dAirport",

                                    "departureLocation" => "$city1dCity , $city1dCountry",

                                    "departureTime" => "$city1dpTimedate",

                                    "arrival" => "$city1ArrivalTo",

                                    "arrivalTime" => "$city1arrTimedate",

                                    "arrivalAirport" => "$city1aAirport",

                                    "arrivalLocation" => "$city1aCity , $city1aCountry",

                                    "flightduration" => "$city1TravelTime",

                                    "bookingcode" => "$city1BookingCode",

                                    "seat" => "$city1Seat",

                                ),

                                "1" => array(

                                    "marketingcareer" => "$city1markettingCarrier1",

                                    "marketingcareerName" => "$city1markettingCarrierName1",

                                    "marketingflight" => "$city1markettingFN1",

                                    "operatingcareer" => "$city1operatingCarrier1",

                                    "operatingflight" => "$city1operatingFN1",

                                    "departure" => "$city1DepartureFrom1",

                                    "departureAirport" => "$city1dAirport1",

                                    "departureLocation" => "$city1dCity , $city1dCountry1",

                                    "departureTime" => "$city1dpTimedate1",

                                    "arrival" => "$city1ArrivalTo1",

                                    "arrivalTime" => "$city1arrTimedate1",

                                    "arrivalAirport" => "$city1aAirport1",

                                    "arrivalLocation" => "$city1aCity1 , $city1aCountry1",

                                    "flightduration" => "$city1TravelTime1",

                                    "bookingcode" => "$city1BookingCode1",

                                    "seat" => "$city1Seat1",

                                ),

                            );

                        }

                        //City 2

                        if ($sgCount2 == 1) {

                            //Go

                            $city2lf1 = $legDescs[$id2]['schedules'][0]['ref'];

                            $city2legrefs = $city2lf1 - 1;

                            $city2departureTime = substr($scheduleDescs[$city2legrefs]['departure']['time'], 0, 5);

                            $city2dpTime = date("D d M Y", strtotime($City2DepDate . " " . $city2departureTime));

                            $city2dpTimedate = $City2DepDate . "T" . $city2departureTime . ':00';

                            $city2ArrivalTime = substr($scheduleDescs[$city2legrefs]['arrival']['time'], 0, 5);

                            $city2arrivalDate = 0;

                            if (isset($scheduleDescs[$city2legrefs]['arrival']['dateAdjustment'])) {

                                $city2arrivalDate += 1;

                            }

                            if ($city2arrivalDate == 1) {

                                $city2aDate = date('Y-m-d', strtotime("+1 day", strtotime($City2DepDate)));

                            } else {

                                $city2aDate = $City2DepDate;

                            }

                            $city2arrTime = date("D d M Y", strtotime($city2aDate . " " . $city2ArrivalTime));

                            $city2arrTimedate = $city2aDate . "T" . $city2ArrivalTime . ':00';

                            $city2ArrivalTo = $scheduleDescs[$city2legrefs]['arrival']['airport'];

                            $city2DepartureFrom = $scheduleDescs[$city2legrefs]['departure']['airport'];

                            $city2markettingCarrier = $scheduleDescs[$city2legrefs]['carrier']['marketing'];

                            $city2Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city2markettingCarrier' ");

                            $city2Crrow = mysqli_fetch_array($city2Crsql, MYSQLI_ASSOC);

                            if (!empty($city2Crrow)) {

                                $city2markettingCarrierName = $city2Crrow['name'];

                            }

                            // Departure Country

                            $city2Deptsql = mysqli_query($conn, "$Airportsql code='$city2DepartureFrom' ");

                            $city2Deptrow = mysqli_fetch_array($city2Deptsql, MYSQLI_ASSOC);

                            if (!empty($city2Deptrow)) {

                                $city2dAirport = $city2Deptrow['name'];

                                $city2dCity = $city2Deptrow['cityName'];

                                $city2dCountry = $city2Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city2Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city2ArrivalTo' ");

                            $city2Arrrow = mysqli_fetch_array($city2Arrsql, MYSQLI_ASSOC);

                            if (!empty($city2Arrrow)) {

                                $city2aAirport = $city2Arrrow['name'];

                                $city2aCity = $city2Arrrow['cityName'];

                                $city2aCountry = $city2Arrrow['countryCode'];

                            }

                            $city2markettingFN = $scheduleDescs[$city2legrefs]['carrier']['marketingFlightNumber'];

                            $city2operatingCarrier = $scheduleDescs[$city2legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city2legrefs]['carrier']['operatingFlightNumber'])) {

                                $city2operatingFN = $scheduleDescs[$city2legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city2operatingFN = 1;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city2BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city2BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city2ElapsedTime = $legDescs[$id2]['elapsedTime'];

                            $city2TravelTime = floor($city2ElapsedTime / 60) . "H " . ($city2ElapsedTime - ((floor($city2ElapsedTime / 60)) * 60)) . "Min";

                            //transit Time

                            $transitDetails = array("0" => array("transit1" => "0"),

                                "1" => array("transit1" => "0"));

                            $City2Data = array("0" => array("marketingcareer" => "$city2markettingCarrier",

                                "marketingcareerName" => "$city2markettingCarrierName",

                                "marketingflight" => "$city2markettingFN",

                                "operatingcareer" => "$city2operatingCarrier",

                                "operatingflight" => "$city2operatingFN",

                                "departure" => "$city2DepartureFrom",

                                "departureAirport" => "$city2dAirport",

                                "departureLocation" => "$city2dCity , $city2dCountry",

                                "departureTime" => "$city2dpTimedate",

                                "arrival" => "$city2ArrivalTo",

                                "arrivalTime" => "$city2arrTimedate",

                                "arrivalAirport" => "$city2aAirport",

                                "arrivalLocation" => "$city2aCity , $city2aCountry",

                                "flightduration" => "$city2TravelTime",

                                "bookingcode" => "$city2BookingCode",

                                "seat" => "$city2Seat"),

                            );

                        } else if ($sgCount2 == 2) {

                            //Go 1

                            $city2lf1 = $legDescs[$id2]['schedules'][0]['ref'];

                            $city2legrefs = $city2lf1 - 1;

                            $city2departureTime = substr($scheduleDescs[$city2legrefs]['departure']['time'], 0, 5);

                            $city2dpTime = date("D d M Y", strtotime($City2DepDate . " " . $city2departureTime));

                            $city2dpTimedate = $City2DepDate . "T" . $city2departureTime . ':00';

                            $city2ArrivalTime = substr($scheduleDescs[$city2legrefs]['arrival']['time'], 0, 5);

                            $city2arrivalDate = 0;

                            if (isset($scheduleDescs[$city2legrefs]['arrival']['dateAdjustment'])) {

                                $city2arrivalDate += 1;

                            }

                            if ($city2arrivalDate == 1) {

                                $city2aDate = date('Y-m-d', strtotime("+1 day", strtotime($City2DepDate)));

                            } else {

                                $city2aDate = $City2DepDate;

                            }

                            $city2arrTime = date("D d M Y", strtotime($city2aDate . " " . $city2ArrivalTime));

                            $city2arrTimedate = $city2aDate . "T" . $city2ArrivalTime . ':00';

                            $city2ArrivalTo = $scheduleDescs[$city2legrefs]['arrival']['airport'];

                            $city2DepartureFrom = $scheduleDescs[$city2legrefs]['departure']['airport'];

                            $city2markettingCarrier = $scheduleDescs[$city2legrefs]['carrier']['marketing'];

                            $city2Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city2markettingCarrier' ");

                            $city2Crrow = mysqli_fetch_array($city2Crsql, MYSQLI_ASSOC);

                            if (!empty($city2Crrow)) {

                                $city2markettingCarrierName = $city2Crrow['name'];

                            }

                            // Departure Country

                            $city2Deptsql = mysqli_query($conn, "$Airportsql code='$city2DepartureFrom' ");

                            $city2Deptrow = mysqli_fetch_array($city2Deptsql, MYSQLI_ASSOC);

                            if (!empty($city2Deptrow)) {

                                $city2dAirport = $city2Deptrow['name'];

                                $city2dCity = $city2Deptrow['cityName'];

                                $city2dCountry = $city2Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city2Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city2ArrivalTo' ");

                            $city2Arrrow = mysqli_fetch_array($city2Arrsql, MYSQLI_ASSOC);

                            if (!empty($city2Arrrow)) {

                                $city2aAirport = $city2Arrrow['name'];

                                $city2aCity = $city2Arrrow['cityName'];

                                $city2aCountry = $city2Arrrow['countryCode'];

                            }

                            $city2markettingFN = $scheduleDescs[$city2legrefs]['carrier']['marketingFlightNumber'];

                            $city2operatingCarrier = $scheduleDescs[$city2legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city2legrefs]['carrier']['operatingFlightNumber'])) {

                                $city2operatingFN = $scheduleDescs[$city2legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city2operatingFN = $city2markettingFN;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city2BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city2BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city2ElapsedTime = $scheduleDescs[$city2legrefs]['elapsedTime'];

                            $city2TravelTime = floor($city2ElapsedTime / 60) . "H " . ($city2ElapsedTime - ((floor($city2ElapsedTime / 60)) * 60)) . "Min";

                            //Go 2

                            $city2lf2 = $legDescs[$id2]['schedules'][1]['ref'];

                            $city2legrefs2 = $city2lf2 - 1;

                            $city2DepartureDate1 = 0;

                            if (isset($legDescs[$id2]['schedules'][1]['departureDateAdjustment'])) {

                                $city2DepartureDate1 += 1;

                            }

                            if ($city2DepartureDate1 == 1) {

                                $City2DepDate1 = date('Y-m-d', strtotime("+1 day", strtotime($City2DepDate)));

                            } else {

                                $City2DepDate1 = $City2DepDate;

                            }

                            $city2departureTime1 = substr($scheduleDescs[$city2legrefs2]['departure']['time'], 0, 5);

                            $city2dpTime1 = date("D d M Y", strtotime($City2DepDate1 . " " . $city2departureTime1));

                            $city2dpTimedate1 = $City2DepDate1 . "T" . $city2departureTime1 . ':00';

                            $city2ArrivalTime1 = substr($scheduleDescs[$city2legrefs2]['arrival']['time'], 0, 5);

                            $city2arrivalDate1 = 0;

                            if (isset($scheduleDescs[$city2legrefs2]['arrival']['dateAdjustment'])) {

                                $city2arrivalDate1 += 1;

                            }

                            if ($city2arrivalDate1 == 1) {

                                $city2aDate1 = date('Y-m-d', strtotime("+1 day", strtotime($City2DepDate1)));

                            } else {

                                $city2aDate1 = $City2DepDate1;

                            }

                            $city2arrTime1 = date("D d M Y", strtotime($city2aDate1 . " " . $city2ArrivalTime1));

                            $city2arrTimedate1 = $city2aDate1 . "T" . $city2ArrivalTime1 . ':00';

                            $city2ArrivalTo1 = $scheduleDescs[$city2legrefs2]['arrival']['airport'];

                            $city2DepartureFrom1 = $scheduleDescs[$city2legrefs2]['departure']['airport'];

                            $city2markettingCarrier1 = $scheduleDescs[$city2legrefs2]['carrier']['marketing'];

                            $city2Crsql1 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city2markettingCarrier1' ");

                            $city2Crrow1 = mysqli_fetch_array($city2Crsql1, MYSQLI_ASSOC);

                            if (!empty($city2Crrow1)) {

                                $city2markettingCarrierName1 = $city2Crrow1['name'];

                            }

                            // Departure Country

                            $city2Deptsql1 = mysqli_query($conn, "$Airportsql code='$city2DepartureFrom1' ");

                            $city2Deptrow1 = mysqli_fetch_array($city2Deptsql1, MYSQLI_ASSOC);

                            if (!empty($city2Deptrow1)) {

                                $city2dAirport1 = $city2Deptrow1['name'];

                                $city2dCity1 = $city2Deptrow1['cityName'];

                                $city2dCountry1 = $city2Deptrow1['countryCode'];

                            }

                            // Arrival Country

                            $city2Arrsql1 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city2ArrivalTo1' ");

                            $city2Arrrow1 = mysqli_fetch_array($city2Arrsql1, MYSQLI_ASSOC);

                            if (!empty($city2Arrrow)) {

                                $city2aAirport1 = $city2Arrrow1['name'];

                                $city2aCity1 = $city2Arrrow1['cityName'];

                                $city2aCountry1 = $city2Arrrow1['countryCode'];

                            }

                            $city2markettingFN1 = $scheduleDescs[$city2legrefs2]['carrier']['marketingFlightNumber'];

                            $city2operatingCarrier1 = $scheduleDescs[$city2legrefs2]['carrier']['operating'];

                            if (isset($scheduleDescs[$city2legrefs2]['carrier']['operatingFlightNumber'])) {

                                $city2operatingFN1 = $scheduleDescs[$city2legrefs2]['carrier']['operatingFlightNumber'];

                            } else {

                                $city2operatingFN1 = $city2markettingFN1;

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat1 = $fareComponents[1]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat1 = "9";

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])) {

                                $city2BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city2BookingCode1 = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city2ElapsedTime1 = $scheduleDescs[$city2legrefs2]['elapsedTime'];

                            $city2TravelTime1 = floor($city2ElapsedTime1 / 60) . "H " . ($city2ElapsedTime1 - ((floor($city2ElapsedTime1 / 60)) * 60)) . "Min";

                            //Go Transit Time

                            $city2TransitTime = $city2TotalElapesd - ($city2ElapsedTime + $city2ElapsedTime1);

                            $city2TransitDuration = floor($city2TransitTime / 60) . "H " . ($city2TransitTime - ((floor($city2TransitTime / 60)) * 60)) . "Min";

                            $city2JourneyElapseTime = $city2TotalElapesd;

                            $city2JourneyDuration = floor($city2JourneyElapseTime / 60) . "H " . ($city2JourneyElapseTime - ((floor($city2JourneyElapseTime / 60)) * 60)) . "Min";

                            $City2Data = array(

                                "0" => array(

                                    "marketingcareer" => "$city2markettingCarrier",

                                    "marketingcareerName" => "$city2markettingCarrierName",

                                    "marketingflight" => "$city2markettingFN",

                                    "operatingcareer" => "$city2operatingCarrier",

                                    "operatingflight" => "$city2operatingFN",

                                    "departure" => "$city2DepartureFrom",

                                    "departureAirport" => "$city2dAirport",

                                    "departureLocation" => "$city2dCity , $city2dCountry",

                                    "departureTime" => "$city2dpTimedate",

                                    "arrival" => "$city2ArrivalTo",

                                    "arrivalTime" => "$city2arrTimedate",

                                    "arrivalAirport" => "$city2aAirport",

                                    "arrivalLocation" => "$city2aCity , $city2aCountry",

                                    "flightduration" => "$city2TravelTime",

                                    "bookingcode" => "$city2BookingCode",

                                    "seat" => "$city2Seat",

                                ),

                                "1" => array(

                                    "marketingcareer" => "$city2markettingCarrier1",

                                    "marketingcareerName" => "$city2markettingCarrierName1",

                                    "marketingflight" => "$city2markettingFN1",

                                    "operatingcareer" => "$city2operatingCarrier1",

                                    "operatingflight" => "$city2operatingFN1",

                                    "departure" => "$city2DepartureFrom1",

                                    "departureAirport" => "$city2dAirport1",

                                    "departureLocation" => "$city2dCity , $city2dCountry1",

                                    "departureTime" => "$city2dpTimedate1",

                                    "arrival" => "$city2ArrivalTo1",

                                    "arrivalTime" => "$city2arrTimedate1",

                                    "arrivalAirport" => "$city2aAirport1",

                                    "arrivalLocation" => "$city2aCity1 , $city2aCountry1",

                                    "flightduration" => "$city2TravelTime1",

                                    "bookingcode" => "$city2BookingCode1",

                                    "seat" => "$city2Seat1",

                                ),

                            );

                        }

                        $basic = array("system" => "Sabre",

                            "city" => "2",

                            "career" => "$vCarCode",

                            "careerName" => "$CarrieerName",

                            "lastTicketTime" => "$timelimit",

                            "BasePrice" => $totalBaseAmount,

                            "Taxes" => $totalTaxAmount,

                            "price" => "$agentPrice",

                            "clientPrice" => "$totalPrice",

                            "comission" => "$Commission",

                            "pricebreakdown" => $PriceBreakDown,

                            "transit" => $transitDetails,

                            "bags" => $Baggage,

                            "refundable" => $nonRefundable,

                            "segments" => array("0" => $City1Data, "1" => $City2Data),

                        );

                        array_push($All, $basic);

                    } else if ($CityCount == 3) {

                        //City 1

                        $ref1 = $itineraries['legs'][0]['ref'];

                        $id1 = $ref1 - 1;

                        //City 2

                        $ref2 = $itineraries['legs'][1]['ref'];

                        $id2 = $ref2 - 1;

                        //City 3

                        $ref3 = $itineraries['legs'][2]['ref'];

                        $id3 = $ref3 - 1;

                        //Segment Count

                        $sgCount1 = count($legDescs[$id1]['schedules']); //echo $sgCount1;

                        $sgCount2 = count($legDescs[$id2]['schedules']); //echo $sgCount2;

                        $sgCount3 = count($legDescs[$id3]['schedules']); //echo $sgCount3;

                        //City 1

                        $city1TotalElapesd = $legDescs[$id1]['elapsedTime'];

                        //City 2

                        $city2TotalElapesd = $legDescs[$id2]['elapsedTime'];

                        //City 3

                        $city3TotalElapesd = $legDescs[$id3]['elapsedTime'];

                        //City1

                        if ($sgCount1 == 1) {

                            //Go

                            $city1lf1 = $legDescs[$id1]['schedules'][0]['ref'];

                            $city1legrefs = $city1lf1 - 1;

                            $city1departureTime = substr($scheduleDescs[$city1legrefs]['departure']['time'], 0, 5);

                            $city1dpTime = date("D d M Y", strtotime($City1DepDate . " " . $city1departureTime));

                            $city1dpTimedate = $City1DepDate . "T" . $city1departureTime . ':00';

                            $city1ArrivalTime = substr($scheduleDescs[$city1legrefs]['arrival']['time'], 0, 5);

                            $city1arrivalDate = 0;

                            if (isset($scheduleDescs[$city1legrefs]['arrival']['dateAdjustment'])) {

                                $city1arrivalDate += 1;

                            }

                            if ($city1arrivalDate == 1) {

                                $city1aDate = date('Y-m-d', strtotime("+1 day", strtotime($City1DepDate)));

                            } else {

                                $city1aDate = $City1DepDate;

                            }

                            $city1arrTime = date("D d M Y", strtotime($city1aDate . " " . $city1ArrivalTime));

                            $city1arrTimedate = $city1aDate . "T" . $city1ArrivalTime . ':00';

                            $city1ArrivalTo = $scheduleDescs[$city1legrefs]['arrival']['airport'];

                            $city1DepartureFrom = $scheduleDescs[$city1legrefs]['departure']['airport'];

                            $city1markettingCarrier = $scheduleDescs[$city1legrefs]['carrier']['marketing'];

                            $city1Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city1markettingCarrier' ");

                            $city1Crrow = mysqli_fetch_array($city1Crsql, MYSQLI_ASSOC);

                            if (!empty($city1Crrow)) {

                                $city1markettingCarrierName = $city1Crrow['name'];

                            }

                            // Departure Country

                            $city1Deptsql = mysqli_query($conn, "$Airportsql code='$city1DepartureFrom' ");

                            $city1Deptrow = mysqli_fetch_array($city1Deptsql, MYSQLI_ASSOC);

                            if (!empty($city1Deptrow)) {

                                $city1dAirport = $city1Deptrow['name'];

                                $city1dCity = $city1Deptrow['cityName'];

                                $city1dCountry = $city1Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city1Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city1ArrivalTo' ");

                            $city1Arrrow = mysqli_fetch_array($city1Arrsql, MYSQLI_ASSOC);

                            if (!empty($city1Arrrow)) {

                                $city1aAirport = $city1Arrrow['name'];

                                $city1aCity = $city1Arrrow['cityName'];

                                $city1aCountry = $city1Arrrow['countryCode'];

                            }

                            $city1markettingFN = $scheduleDescs[$city1legrefs]['carrier']['marketingFlightNumber'];

                            $city1operatingCarrier = $scheduleDescs[$city1legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city1legrefs]['carrier']['operatingFlightNumber'])) {

                                $city1operatingFN = $scheduleDescs[$city1legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city1operatingFN = 1;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city1BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city1BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city1ElapsedTime = $legDescs[$id1]['elapsedTime'];

                            $city1TravelTime = floor($city1ElapsedTime / 60) . "H " . ($city1ElapsedTime - ((floor($city1ElapsedTime / 60)) * 60)) . "Min";

                            //transit Time

                            $transitDetails = array("0" => array("transit1" => "0"),

                                "1" => array("transit1" => "0"));

                            $City1Data = array("0" => array("marketingcareer" => "$city1markettingCarrier",

                                "marketingcareerName" => "$city1markettingCarrierName",

                                "marketingflight" => "$city1markettingFN",

                                "operatingcareer" => "$city1operatingCarrier",

                                "operatingflight" => "$city1operatingFN",

                                "departure" => "$city1DepartureFrom",

                                "departureAirport" => "$city1dAirport",

                                "departureLocation" => "$city1dCity , $city1dCountry",

                                "departureTime" => "$city1dpTimedate",

                                "arrival" => "$city1ArrivalTo",

                                "arrivalTime" => "$city1arrTimedate",

                                "arrivalAirport" => "$city1aAirport",

                                "arrivalLocation" => "$city1aCity , $city1aCountry",

                                "flightduration" => "$city1TravelTime",

                                "bookingcode" => "$city1BookingCode",

                                "seat" => "$city1Seat"),

                            );

                        } else if ($sgCount1 == 2) {

                            //Go 1

                            $city1lf1 = $legDescs[$id1]['schedules'][0]['ref'];

                            $city1legrefs = $city1lf1 - 1;

                            $city1departureTime = substr($scheduleDescs[$city1legrefs]['departure']['time'], 0, 5);

                            $city1dpTime = date("D d M Y", strtotime($City1DepDate . " " . $city1departureTime));

                            $city1dpTimedate = $City1DepDate . "T" . $city1departureTime . ':00';

                            $city1ArrivalTime = substr($scheduleDescs[$city1legrefs]['arrival']['time'], 0, 5);

                            $city1arrivalDate = 0;

                            if (isset($scheduleDescs[$city1legrefs]['arrival']['dateAdjustment'])) {

                                $city1arrivalDate += 1;

                            }

                            if ($city1arrivalDate == 1) {

                                $city1aDate = date('Y-m-d', strtotime("+1 day", strtotime($City1DepDate)));

                            } else {

                                $city1aDate = $City1DepDate;

                            }

                            $city1arrTime = date("D d M Y", strtotime($city1aDate . " " . $city1ArrivalTime));

                            $city1arrTimedate = $city1aDate . "T" . $city1ArrivalTime . ':00';

                            $city1ArrivalTo = $scheduleDescs[$city1legrefs]['arrival']['airport'];

                            $city1DepartureFrom = $scheduleDescs[$city1legrefs]['departure']['airport'];

                            $city1markettingCarrier = $scheduleDescs[$city1legrefs]['carrier']['marketing'];

                            $city1Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city1markettingCarrier' ");

                            $city1Crrow = mysqli_fetch_array($city1Crsql, MYSQLI_ASSOC);

                            if (!empty($city1Crrow)) {

                                $city1markettingCarrierName = $city1Crrow['name'];

                            }

                            // Departure Country

                            $city1Deptsql = mysqli_query($conn, "$Airportsql code='$city1DepartureFrom' ");

                            $city1Deptrow = mysqli_fetch_array($city1Deptsql, MYSQLI_ASSOC);

                            if (!empty($city1Deptrow)) {

                                $city1dAirport = $city1Deptrow['name'];

                                $city1dCity = $city1Deptrow['cityName'];

                                $city1dCountry = $city1Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city1Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city1ArrivalTo' ");

                            $city1Arrrow = mysqli_fetch_array($city1Arrsql, MYSQLI_ASSOC);

                            if (!empty($city1Arrrow)) {

                                $city1aAirport = $city1Arrrow['name'];

                                $city1aCity = $city1Arrrow['cityName'];

                                $city1aCountry = $city1Arrrow['countryCode'];

                            }

                            $city1markettingFN = $scheduleDescs[$city1legrefs]['carrier']['marketingFlightNumber'];

                            $city1operatingCarrier = $scheduleDescs[$city1legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city1legrefs]['carrier']['operatingFlightNumber'])) {

                                $city1operatingFN = $scheduleDescs[$city1legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city1operatingFN = $city1markettingFN;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city1BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city1BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city1ElapsedTime = $scheduleDescs[$city1legrefs]['elapsedTime'];

                            $city1TravelTime = floor($city1ElapsedTime / 60) . "H " . ($city1ElapsedTime - ((floor($city1ElapsedTime / 60)) * 60)) . "Min";

                            //Go 2

                            $city1lf2 = $legDescs[$id1]['schedules'][1]['ref'];

                            $city1legrefs2 = $city1lf2 - 1;

                            $city1DepartureDate1 = 0;

                            if (isset($legDescs[$id1]['schedules'][1]['departureDateAdjustment'])) {

                                $city1DepartureDate1 += 1;

                            }

                            if ($city1DepartureDate1 == 1) {

                                $city1depDate1 = date('Y-m-d', strtotime("+1 day", strtotime($City1DepDate)));

                            } else {

                                $city1depDate1 = $City1DepDate;

                            }

                            $city1departureTime1 = substr($scheduleDescs[$city1legrefs2]['departure']['time'], 0, 5);

                            $city1dpTime1 = date("D d M Y", strtotime($city1depDate1 . " " . $city1departureTime1));

                            $city1dpTimedate1 = $city1depDate1 . "T" . $city1departureTime1 . ':00';

                            $city1ArrivalTime1 = substr($scheduleDescs[$city1legrefs2]['arrival']['time'], 0, 5);

                            $city1arrivalDate1 = 0;

                            if (isset($scheduleDescs[$city1legrefs2]['arrival']['dateAdjustment'])) {

                                $city1arrivalDate1 += 1;

                            }

                            if ($city1arrivalDate1 == 1) {

                                $city1aDate1 = date('Y-m-d', strtotime("+1 day", strtotime($city1depDate1)));

                            } else {

                                $city1aDate1 = $city1depDate1;

                            }

                            $city1arrTime1 = date("D d M Y", strtotime($city1aDate1 . " " . $city1ArrivalTime1));

                            $city1arrTimedate1 = $city1aDate1 . "T" . $city1ArrivalTime1 . ':00';

                            $city1ArrivalTo1 = $scheduleDescs[$city1legrefs2]['arrival']['airport'];

                            $city1DepartureFrom1 = $scheduleDescs[$city1legrefs2]['departure']['airport'];

                            $city1markettingCarrier1 = $scheduleDescs[$city1legrefs2]['carrier']['marketing'];

                            $city1Crsql1 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city1markettingCarrier1' ");

                            $city1Crrow1 = mysqli_fetch_array($city1Crsql1, MYSQLI_ASSOC);

                            if (!empty($city1Crrow1)) {

                                $city1markettingCarrierName1 = $city1Crrow1['name'];

                            }

                            // Departure Country

                            $city1Deptsql1 = mysqli_query($conn, "$Airportsql code='$city1DepartureFrom1' ");

                            $city1Deptrow1 = mysqli_fetch_array($city1Deptsql1, MYSQLI_ASSOC);

                            if (!empty($city1Deptrow1)) {

                                $city1dAirport1 = $city1Deptrow1['name'];

                                $city1dCity1 = $city1Deptrow1['cityName'];

                                $city1dCountry1 = $city1Deptrow1['countryCode'];

                            }

                            // Arrival Country

                            $city1Arrsql1 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city1ArrivalTo1' ");

                            $city1Arrrow1 = mysqli_fetch_array($city1Arrsql1, MYSQLI_ASSOC);

                            if (!empty($city1Arrrow)) {

                                $city1aAirport1 = $city1Arrrow1['name'];

                                $city1aCity1 = $city1Arrrow1['cityName'];

                                $city1aCountry1 = $city1Arrrow1['countryCode'];

                            }

                            $city1markettingFN1 = $scheduleDescs[$city1legrefs2]['carrier']['marketingFlightNumber'];

                            $city1operatingCarrier1 = $scheduleDescs[$city1legrefs2]['carrier']['operating'];

                            if (isset($scheduleDescs[$city1legrefs2]['carrier']['operatingFlightNumber'])) {

                                $city1operatingFN1 = $scheduleDescs[$city1legrefs2]['carrier']['operatingFlightNumber'];

                            } else {

                                $city1operatingFN1 = $city1markettingFN1;

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat1 = $fareComponents[1]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat1 = "9";

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])) {

                                $city1BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city1BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];

                            }

                            $city1ElapsedTime1 = $scheduleDescs[$city1legrefs2]['elapsedTime'];

                            $city1TravelTime1 = floor($city1ElapsedTime1 / 60) . "H " . ($city1ElapsedTime1 - ((floor($city1ElapsedTime1 / 60)) * 60)) . "Min";

                            //Go Transit Time

                            $city1TransitTime = $city1TotalElapesd - ($city1ElapsedTime + $city1ElapsedTime1);

                            $city1TransitDuration = floor($city1TransitTime / 60) . "H " . ($city1TransitTime - ((floor($city1TransitTime / 60)) * 60)) . "Min";

                            $city1JourneyElapseTime = $city1TotalElapesd;

                            $city1JourneyDuration = floor($city1JourneyElapseTime / 60) . "H " . ($city1JourneyElapseTime - ((floor($city1JourneyElapseTime / 60)) * 60)) . "Min";

                            $City1Data = array(

                                "0" => array(

                                    "marketingcareer" => "$city1markettingCarrier",

                                    "marketingcareerName" => "$city1markettingCarrierName",

                                    "marketingflight" => "$city1markettingFN",

                                    "operatingcareer" => "$city1operatingCarrier",

                                    "operatingflight" => "$city1operatingFN",

                                    "departure" => "$city1DepartureFrom",

                                    "departureAirport" => "$city1dAirport",

                                    "departureLocation" => "$city1dCity , $city1dCountry",

                                    "departureTime" => "$city1dpTimedate",

                                    "arrival" => "$city1ArrivalTo",

                                    "arrivalTime" => "$city1arrTimedate",

                                    "arrivalAirport" => "$city1aAirport",

                                    "arrivalLocation" => "$city1aCity , $city1aCountry",

                                    "flightduration" => "$city1TravelTime",

                                    "bookingcode" => "$city1BookingCode",

                                    "seat" => "$city1Seat",

                                ),

                                "1" => array(

                                    "marketingcareer" => "$city1markettingCarrier1",

                                    "marketingcareerName" => "$city1markettingCarrierName1",

                                    "marketingflight" => "$city1markettingFN1",

                                    "operatingcareer" => "$city1operatingCarrier1",

                                    "operatingflight" => "$city1operatingFN1",

                                    "departure" => "$city1DepartureFrom1",

                                    "departureAirport" => "$city1dAirport1",

                                    "departureLocation" => "$city1dCity , $city1dCountry1",

                                    "departureTime" => "$city1dpTimedate1",

                                    "arrival" => "$city1ArrivalTo1",

                                    "arrivalTime" => "$city1arrTimedate1",

                                    "arrivalAirport" => "$city1aAirport1",

                                    "arrivalLocation" => "$city1aCity1 , $city1aCountry1",

                                    "flightduration" => "$city1TravelTime1",

                                    "bookingcode" => "$city1BookingCode1",

                                    "seat" => "$city1Seat1",

                                ),

                            );

                        }

                        //City 2

                        if ($sgCount2 == 1) {

                            //Go

                            $city2lf1 = $legDescs[$id2]['schedules'][0]['ref'];

                            $city2legrefs = $city2lf1 - 1;

                            $city2departureTime = substr($scheduleDescs[$city2legrefs]['departure']['time'], 0, 5);

                            $city2dpTime = date("D d M Y", strtotime($City2DepDate . " " . $city2departureTime));

                            $city2dpTimedate = $City2DepDate . "T" . $city2departureTime . ':00';

                            $city2ArrivalTime = substr($scheduleDescs[$city2legrefs]['arrival']['time'], 0, 5);

                            $city2arrivalDate = 0;

                            if (isset($scheduleDescs[$city2legrefs]['arrival']['dateAdjustment'])) {

                                $city2arrivalDate += 1;

                            }

                            if ($city2arrivalDate == 1) {

                                $city2aDate = date('Y-m-d', strtotime("+1 day", strtotime($City2DepDate)));

                            } else {

                                $city2aDate = $City2DepDate;

                            }

                            $city2arrTime = date("D d M Y", strtotime($city2aDate . " " . $city2ArrivalTime));

                            $city2arrTimedate = $city2aDate . "T" . $city2ArrivalTime . ':00';

                            $city2ArrivalTo = $scheduleDescs[$city2legrefs]['arrival']['airport'];

                            $city2DepartureFrom = $scheduleDescs[$city2legrefs]['departure']['airport'];

                            $city2markettingCarrier = $scheduleDescs[$city2legrefs]['carrier']['marketing'];

                            $city2Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city2markettingCarrier' ");

                            $city2Crrow = mysqli_fetch_array($city2Crsql, MYSQLI_ASSOC);

                            if (!empty($city2Crrow)) {

                                $city2markettingCarrierName = $city2Crrow['name'];

                            }

                            // Departure Country

                            $city2Deptsql = mysqli_query($conn, "$Airportsql code='$city2DepartureFrom' ");

                            $city2Deptrow = mysqli_fetch_array($city2Deptsql, MYSQLI_ASSOC);

                            if (!empty($city2Deptrow)) {

                                $city2dAirport = $city2Deptrow['name'];

                                $city2dCity = $city2Deptrow['cityName'];

                                $city2dCountry = $city2Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city2Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city2ArrivalTo' ");

                            $city2Arrrow = mysqli_fetch_array($city2Arrsql, MYSQLI_ASSOC);

                            if (!empty($city2Arrrow)) {

                                $city2aAirport = $city2Arrrow['name'];

                                $city2aCity = $city2Arrrow['cityName'];

                                $city2aCountry = $city2Arrrow['countryCode'];

                            }

                            $city2markettingFN = $scheduleDescs[$city2legrefs]['carrier']['marketingFlightNumber'];

                            $city2operatingCarrier = $scheduleDescs[$city2legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city2legrefs]['carrier']['operatingFlightNumber'])) {

                                $city2operatingFN = $scheduleDescs[$city2legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city2operatingFN = 1;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city2BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city2BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city2ElapsedTime = $legDescs[$id2]['elapsedTime'];

                            $city2TravelTime = floor($city2ElapsedTime / 60) . "H " . ($city2ElapsedTime - ((floor($city2ElapsedTime / 60)) * 60)) . "Min";

                            //transit Time

                            $transitDetails = array("0" => array("transit1" => "0"),

                                "1" => array("transit1" => "0"));

                            $City2Data = array("0" => array("marketingcareer" => "$city2markettingCarrier",

                                "marketingcareerName" => "$city2markettingCarrierName",

                                "marketingflight" => "$city2markettingFN",

                                "operatingcareer" => "$city2operatingCarrier",

                                "operatingflight" => "$city2operatingFN",

                                "departure" => "$city2DepartureFrom",

                                "departureAirport" => "$city2dAirport",

                                "departureLocation" => "$city2dCity , $city2dCountry",

                                "departureTime" => "$city2dpTimedate",

                                "arrival" => "$city2ArrivalTo",

                                "arrivalTime" => "$city2arrTimedate",

                                "arrivalAirport" => "$city2aAirport",

                                "arrivalLocation" => "$city2aCity , $city2aCountry",

                                "flightduration" => "$city2TravelTime",

                                "bookingcode" => "$city2BookingCode",

                                "seat" => "$city2Seat"),

                            );

                        } else if ($sgCount2 == 2) {

                            //Go 1

                            $city2lf1 = $legDescs[$id2]['schedules'][0]['ref'];

                            $city2legrefs = $city2lf1 - 1;

                            $city2departureTime = substr($scheduleDescs[$city2legrefs]['departure']['time'], 0, 5);

                            $city2dpTime = date("D d M Y", strtotime($City2DepDate . " " . $city2departureTime));

                            $city2dpTimedate = $City2DepDate . "T" . $city2departureTime . ':00';

                            $city2ArrivalTime = substr($scheduleDescs[$city2legrefs]['arrival']['time'], 0, 5);

                            $city2arrivalDate = 0;

                            if (isset($scheduleDescs[$city2legrefs]['arrival']['dateAdjustment'])) {

                                $city2arrivalDate += 1;

                            }

                            if ($city2arrivalDate == 1) {

                                $city2aDate = date('Y-m-d', strtotime("+1 day", strtotime($City2DepDate)));

                            } else {

                                $city2aDate = $City2DepDate;

                            }

                            $city2arrTime = date("D d M Y", strtotime($city2aDate . " " . $city2ArrivalTime));

                            $city2arrTimedate = $city2aDate . "T" . $city2ArrivalTime . ':00';

                            $city2ArrivalTo = $scheduleDescs[$city2legrefs]['arrival']['airport'];

                            $city2DepartureFrom = $scheduleDescs[$city2legrefs]['departure']['airport'];

                            $city2markettingCarrier = $scheduleDescs[$city2legrefs]['carrier']['marketing'];

                            $city2Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city2markettingCarrier' ");

                            $city2Crrow = mysqli_fetch_array($city2Crsql, MYSQLI_ASSOC);

                            if (!empty($city2Crrow)) {

                                $city2markettingCarrierName = $city2Crrow['name'];

                            }

                            // Departure Country

                            $city2Deptsql = mysqli_query($conn, "$Airportsql code='$city2DepartureFrom' ");

                            $city2Deptrow = mysqli_fetch_array($city2Deptsql, MYSQLI_ASSOC);

                            if (!empty($city2Deptrow)) {

                                $city2dAirport = $city2Deptrow['name'];

                                $city2dCity = $city2Deptrow['cityName'];

                                $city2dCountry = $city2Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city2Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city2ArrivalTo' ");

                            $city2Arrrow = mysqli_fetch_array($city2Arrsql, MYSQLI_ASSOC);

                            if (!empty($city2Arrrow)) {

                                $city2aAirport = $city2Arrrow['name'];

                                $city2aCity = $city2Arrrow['cityName'];

                                $city2aCountry = $city2Arrrow['countryCode'];

                            }

                            $city2markettingFN = $scheduleDescs[$city2legrefs]['carrier']['marketingFlightNumber'];

                            $city2operatingCarrier = $scheduleDescs[$city2legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city2legrefs]['carrier']['operatingFlightNumber'])) {

                                $city2operatingFN = $scheduleDescs[$city2legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city2operatingFN = $city2markettingFN;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city2BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city2BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city2ElapsedTime = $scheduleDescs[$city2legrefs]['elapsedTime'];

                            $city2TravelTime = floor($city2ElapsedTime / 60) . "H " . ($city2ElapsedTime - ((floor($city2ElapsedTime / 60)) * 60)) . "Min";

                            //Go 2

                            $city2lf2 = $legDescs[$id2]['schedules'][1]['ref'];

                            $city2legrefs2 = $city2lf2 - 1;

                            $city2DepartureDate1 = 0;

                            if (isset($legDescs[$id2]['schedules'][1]['departureDateAdjustment'])) {

                                $city2DepartureDate1 += 1;

                            }

                            if ($city2DepartureDate1 == 1) {

                                $City2DepDate1 = date('Y-m-d', strtotime("+1 day", strtotime($City2DepDate)));

                            } else {

                                $City2DepDate1 = $City2DepDate;

                            }

                            $city2departureTime1 = substr($scheduleDescs[$city2legrefs2]['departure']['time'], 0, 5);

                            $city2dpTime1 = date("D d M Y", strtotime($City2DepDate1 . " " . $city2departureTime1));

                            $city2dpTimedate1 = $City2DepDate1 . "T" . $city2departureTime1 . ':00';

                            $city2ArrivalTime1 = substr($scheduleDescs[$city2legrefs2]['arrival']['time'], 0, 5);

                            $city2arrivalDate1 = 0;

                            if (isset($scheduleDescs[$city2legrefs2]['arrival']['dateAdjustment'])) {

                                $city2arrivalDate1 += 1;

                            }

                            if ($city2arrivalDate1 == 1) {

                                $city2aDate1 = date('Y-m-d', strtotime("+1 day", strtotime($City2DepDate1)));

                            } else {

                                $city2aDate1 = $City2DepDate1;

                            }

                            $city2arrTime1 = date("D d M Y", strtotime($city2aDate1 . " " . $city2ArrivalTime1));

                            $city2arrTimedate1 = $city2aDate1 . "T" . $city2ArrivalTime1 . ':00';

                            $city2ArrivalTo1 = $scheduleDescs[$city2legrefs2]['arrival']['airport'];

                            $city2DepartureFrom1 = $scheduleDescs[$city2legrefs2]['departure']['airport'];

                            $city2markettingCarrier1 = $scheduleDescs[$city2legrefs2]['carrier']['marketing'];

                            $city2Crsql1 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city2markettingCarrier1' ");

                            $city2Crrow1 = mysqli_fetch_array($city2Crsql1, MYSQLI_ASSOC);

                            if (!empty($city2Crrow1)) {

                                $city2markettingCarrierName1 = $city2Crrow1['name'];

                            }

                            // Departure Country

                            $city2Deptsql1 = mysqli_query($conn, "$Airportsql code='$city2DepartureFrom1' ");

                            $city2Deptrow1 = mysqli_fetch_array($city2Deptsql1, MYSQLI_ASSOC);

                            if (!empty($city2Deptrow1)) {

                                $city2dAirport1 = $city2Deptrow1['name'];

                                $city2dCity1 = $city2Deptrow1['cityName'];

                                $city2dCountry1 = $city2Deptrow1['countryCode'];

                            }

                            // Arrival Country

                            $city2Arrsql1 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city2ArrivalTo1' ");

                            $city2Arrrow1 = mysqli_fetch_array($city2Arrsql1, MYSQLI_ASSOC);

                            if (!empty($city2Arrrow)) {

                                $city2aAirport1 = $city2Arrrow1['name'];

                                $city2aCity1 = $city2Arrrow1['cityName'];

                                $city2aCountry1 = $city2Arrrow1['countryCode'];

                            }

                            $city2markettingFN1 = $scheduleDescs[$city2legrefs2]['carrier']['marketingFlightNumber'];

                            $city2operatingCarrier1 = $scheduleDescs[$city2legrefs2]['carrier']['operating'];

                            if (isset($scheduleDescs[$city2legrefs2]['carrier']['operatingFlightNumber'])) {

                                $city2operatingFN1 = $scheduleDescs[$city2legrefs2]['carrier']['operatingFlightNumber'];

                            } else {

                                $city2operatingFN1 = $city2markettingFN1;

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat1 = $fareComponents[1]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat1 = "9";

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])) {

                                $city2BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city2BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];

                            }

                            $city2ElapsedTime1 = $scheduleDescs[$city2legrefs2]['elapsedTime'];

                            $city2TravelTime1 = floor($city2ElapsedTime1 / 60) . "H " . ($city2ElapsedTime1 - ((floor($city2ElapsedTime1 / 60)) * 60)) . "Min";

                            //Go Transit Time

                            $city2TransitTime = $city2TotalElapesd - ($city2ElapsedTime + $city2ElapsedTime1);

                            $city2TransitDuration = floor($city2TransitTime / 60) . "H " . ($city2TransitTime - ((floor($city2TransitTime / 60)) * 60)) . "Min";

                            $city2JourneyElapseTime = $city2TotalElapesd;

                            $city2JourneyDuration = floor($city2JourneyElapseTime / 60) . "H " . ($city2JourneyElapseTime - ((floor($city2JourneyElapseTime / 60)) * 60)) . "Min";

                            $City2Data = array(

                                "0" => array(

                                    "marketingcareer" => "$city2markettingCarrier",

                                    "marketingcareerName" => "$city2markettingCarrierName",

                                    "marketingflight" => "$city2markettingFN",

                                    "operatingcareer" => "$city2operatingCarrier",

                                    "operatingflight" => "$city2operatingFN",

                                    "departure" => "$city2DepartureFrom",

                                    "departureAirport" => "$city2dAirport",

                                    "departureLocation" => "$city2dCity , $city2dCountry",

                                    "departureTime" => "$city2dpTimedate",

                                    "arrival" => "$city2ArrivalTo",

                                    "arrivalTime" => "$city2arrTimedate",

                                    "arrivalAirport" => "$city2aAirport",

                                    "arrivalLocation" => "$city2aCity , $city2aCountry",

                                    "flightduration" => "$city2TravelTime",

                                    "bookingcode" => "$city2BookingCode",

                                    "seat" => "$city2Seat",

                                ),

                                "1" => array(

                                    "marketingcareer" => "$city2markettingCarrier1",

                                    "marketingcareerName" => "$city2markettingCarrierName1",

                                    "marketingflight" => "$city2markettingFN1",

                                    "operatingcareer" => "$city2operatingCarrier1",

                                    "operatingflight" => "$city2operatingFN1",

                                    "departure" => "$city2DepartureFrom1",

                                    "departureAirport" => "$city2dAirport1",

                                    "departureLocation" => "$city2dCity , $city2dCountry1",

                                    "departureTime" => "$city2dpTimedate1",

                                    "arrival" => "$city2ArrivalTo1",

                                    "arrivalTime" => "$city2arrTimedate1",

                                    "arrivalAirport" => "$city2aAirport1",

                                    "arrivalLocation" => "$city2aCity1 , $city2aCountry1",

                                    "flightduration" => "$city2TravelTime1",

                                    "bookingcode" => "$city2BookingCode1",

                                    "seat" => "$city2Seat1",

                                ),

                            );

                        }

                        //City 3

                        if ($sgCount3 == 1) {

                            //Go

                            $city3lf1 = $legDescs[$id3]['schedules'][0]['ref'];

                            $city3legrefs = $city3lf1 - 1;

                            $city3departureTime = substr($scheduleDescs[$city3legrefs]['departure']['time'], 0, 5);

                            $city3dpTime = date("D d M Y", strtotime($City3DepDate . " " . $city3departureTime));

                            $city3dpTimedate = $City3DepDate . "T" . $city3departureTime . ':00';

                            $city3ArrivalTime = substr($scheduleDescs[$city3legrefs]['arrival']['time'], 0, 5);

                            $city3arrivalDate = 0;

                            if (isset($scheduleDescs[$city3legrefs]['arrival']['dateAdjustment'])) {

                                $city3arrivalDate += 1;

                            }

                            if ($city3arrivalDate == 1) {

                                $city3aDate = date('Y-m-d', strtotime("+1 day", strtotime($City3DepDate)));

                            } else {

                                $city3aDate = $City3DepDate;

                            }

                            $city3arrTime = date("D d M Y", strtotime($city3aDate . " " . $city3ArrivalTime));

                            $city3arrTimedate = $city3aDate . "T" . $city3ArrivalTime . ':00';

                            $city3ArrivalTo = $scheduleDescs[$city3legrefs]['arrival']['airport'];

                            $city3DepartureFrom = $scheduleDescs[$city3legrefs]['departure']['airport'];

                            $city3markettingCarrier = $scheduleDescs[$city3legrefs]['carrier']['marketing'];

                            $city3Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city3markettingCarrier' ");

                            $city3Crrow = mysqli_fetch_array($city3Crsql, MYSQLI_ASSOC);

                            if (!empty($city3Crrow)) {

                                $city3markettingCarrierName = $city3Crrow['name'];

                            }

                            // Departure Country

                            $city3Deptsql = mysqli_query($conn, "$Airportsql code='$city3DepartureFrom' ");

                            $city3Deptrow = mysqli_fetch_array($city3Deptsql, MYSQLI_ASSOC);

                            if (!empty($city3Deptrow)) {

                                $city3dAirport = $city3Deptrow['name'];

                                $city3dCity = $city3Deptrow['cityName'];

                                $city3dCountry = $city3Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city3Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city3ArrivalTo' ");

                            $city3Arrrow = mysqli_fetch_array($city3Arrsql, MYSQLI_ASSOC);

                            if (!empty($city3Arrrow)) {

                                $city3aAirport = $city3Arrrow['name'];

                                $city3aCity = $city3Arrrow['cityName'];

                                $city3aCountry = $city3Arrrow['countryCode'];

                            }

                            $city3markettingFN = $scheduleDescs[$city3legrefs]['carrier']['marketingFlightNumber'];

                            $city3operatingCarrier = $scheduleDescs[$city3legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city3legrefs]['carrier']['operatingFlightNumber'])) {

                                $city3operatingFN = $scheduleDescs[$city3legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city3operatingFN = 1;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city3Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city3Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city3BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city3BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city3ElapsedTime = $legDescs[$id2]['elapsedTime'];

                            $city3TravelTime = floor($city3ElapsedTime / 60) . "H " . ($city3ElapsedTime - ((floor($city3ElapsedTime / 60)) * 60)) . "Min";

                            //transit Time

                            $transitDetails = array("0" => array("transit1" => "0"),

                                "1" => array("transit1" => "0"));

                            $City3Data = array("0" => array("marketingcareer" => "$city3markettingCarrier",

                                "marketingcareerName" => "$city3markettingCarrierName",

                                "marketingflight" => "$city3markettingFN",

                                "operatingcareer" => "$city3operatingCarrier",

                                "operatingflight" => "$city3operatingFN",

                                "departure" => "$city3DepartureFrom",

                                "departureAirport" => "$city3dAirport",

                                "departureLocation" => "$city3dCity , $city3dCountry",

                                "departureTime" => "$city3dpTimedate",

                                "arrival" => "$city3ArrivalTo",

                                "arrivalTime" => "$city3arrTimedate",

                                "arrivalAirport" => "$city3aAirport",

                                "arrivalLocation" => "$city3aCity , $city3aCountry",

                                "flightduration" => "$city3TravelTime",

                                "bookingcode" => "$city3BookingCode",

                                "seat" => "$city3Seat"),

                            );

                        } else if ($sgCount3 == 2) {

                            //Go 1

                            $city3lf1 = $legDescs[$id3]['schedules'][0]['ref'];

                            $city3legrefs = $city3lf1 - 1;

                            $city3departureTime = substr($scheduleDescs[$city3legrefs]['departure']['time'], 0, 5);

                            $city3dpTime = date("D d M Y", strtotime($City3DepDate . " " . $city3departureTime));

                            $city3dpTimedate = $City3DepDate . "T" . $city3departureTime . ':00';

                            $city3ArrivalTime = substr($scheduleDescs[$city3legrefs]['arrival']['time'], 0, 5);

                            $city3arrivalDate = 0;

                            if (isset($scheduleDescs[$city3legrefs]['arrival']['dateAdjustment'])) {

                                $city3arrivalDate += 1;

                            }

                            if ($city3arrivalDate == 1) {

                                $city3aDate = date('Y-m-d', strtotime("+1 day", strtotime($City3DepDate)));

                            } else {

                                $city3aDate = $City3DepDate;

                            }

                            $city3arrTime = date("D d M Y", strtotime($city3aDate . " " . $city3ArrivalTime));

                            $city3arrTimedate = $city3aDate . "T" . $city3ArrivalTime . ':00';

                            $city3ArrivalTo = $scheduleDescs[$city3legrefs]['arrival']['airport'];

                            $city3DepartureFrom = $scheduleDescs[$city3legrefs]['departure']['airport'];

                            $city3markettingCarrier = $scheduleDescs[$city3legrefs]['carrier']['marketing'];

                            $city3Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city3markettingCarrier' ");

                            $city3Crrow = mysqli_fetch_array($city3Crsql, MYSQLI_ASSOC);

                            if (!empty($city3Crrow)) {

                                $city3markettingCarrierName = $city3Crrow['name'];

                            }

                            // Departure Country

                            $city3Deptsql = mysqli_query($conn, "$Airportsql code='$city3DepartureFrom' ");

                            $city3Deptrow = mysqli_fetch_array($city3Deptsql, MYSQLI_ASSOC);

                            if (!empty($city3Deptrow)) {

                                $city3dAirport = $city3Deptrow['name'];

                                $city3dCity = $city3Deptrow['cityName'];

                                $city3dCountry = $city3Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city3Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city3ArrivalTo' ");

                            $city3Arrrow = mysqli_fetch_array($city3Arrsql, MYSQLI_ASSOC);

                            if (!empty($city3Arrrow)) {

                                $city3aAirport = $city3Arrrow['name'];

                                $city3aCity = $city3Arrrow['cityName'];

                                $city3aCountry = $city3Arrrow['countryCode'];

                            }

                            $city3markettingFN = $scheduleDescs[$city3legrefs]['carrier']['marketingFlightNumber'];

                            $city3operatingCarrier = $scheduleDescs[$city3legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city3legrefs]['carrier']['operatingFlightNumber'])) {

                                $city3operatingFN = $scheduleDescs[$city3legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city3operatingFN = $city3markettingFN;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city3Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city3Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city3BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city3BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city3ElapsedTime = $scheduleDescs[$city3legrefs]['elapsedTime'];

                            $city3TravelTime = floor($city3ElapsedTime / 60) . "H " . ($city3ElapsedTime - ((floor($city3ElapsedTime / 60)) * 60)) . "Min";

                            //Go 2

                            $city3lf2 = $legDescs[$id3]['schedules'][1]['ref'];

                            $city3legrefs2 = $city3lf2 - 1;

                            $city3DepartureDate1 = 0;

                            if (isset($legDescs[$id3]['schedules'][1]['departureDateAdjustment'])) {

                                $city3DepartureDate1 += 1;

                            }

                            if ($city3DepartureDate1 == 1) {

                                $City3DepDate1 = date('Y-m-d', strtotime("+1 day", strtotime($City3DepDate)));

                            } else {

                                $City3DepDate1 = $City3DepDate;

                            }

                            $city3departureTime1 = substr($scheduleDescs[$city3legrefs2]['departure']['time'], 0, 5);

                            $city3dpTime1 = date("D d M Y", strtotime($City3DepDate1 . " " . $city3departureTime1));

                            $city3dpTimedate1 = $City3DepDate1 . "T" . $city3departureTime1 . ':00';

                            $city3ArrivalTime1 = substr($scheduleDescs[$city3legrefs2]['arrival']['time'], 0, 5);

                            $city3arrivalDate1 = 0;

                            if (isset($scheduleDescs[$city3legrefs2]['arrival']['dateAdjustment'])) {

                                $city3arrivalDate1 += 1;

                            }

                            if ($city3arrivalDate1 == 1) {

                                $city3aDate1 = date('Y-m-d', strtotime("+1 day", strtotime($City3DepDate1)));

                            } else {

                                $city3aDate1 = $City3DepDate1;

                            }

                            $city3arrTime1 = date("D d M Y", strtotime($city3aDate1 . " " . $city3ArrivalTime1));

                            $city3arrTimedate1 = $city3aDate1 . "T" . $city3ArrivalTime1 . ':00';

                            $city3ArrivalTo1 = $scheduleDescs[$city3legrefs2]['arrival']['airport'];

                            $city3DepartureFrom1 = $scheduleDescs[$city3legrefs2]['departure']['airport'];

                            $city3markettingCarrier1 = $scheduleDescs[$city3legrefs2]['carrier']['marketing'];

                            $city3Crsql1 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city3markettingCarrier1' ");

                            $city3Crrow1 = mysqli_fetch_array($city3Crsql1, MYSQLI_ASSOC);

                            if (!empty($city3Crrow1)) {

                                $city3markettingCarrierName1 = $city3Crrow1['name'];

                            }

                            // Departure Country

                            $city3Deptsql1 = mysqli_query($conn, "$Airportsql code='$city3DepartureFrom1' ");

                            $city3Deptrow1 = mysqli_fetch_array($city3Deptsql1, MYSQLI_ASSOC);

                            if (!empty($city3Deptrow1)) {

                                $city3dAirport1 = $city3Deptrow1['name'];

                                $city3dCity1 = $city3Deptrow1['cityName'];

                                $city3dCountry1 = $city3Deptrow1['countryCode'];

                            }

                            // Arrival Country

                            $city3Arrsql1 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city3ArrivalTo1' ");

                            $city3Arrrow1 = mysqli_fetch_array($city3Arrsql1, MYSQLI_ASSOC);

                            if (!empty($city3Arrrow)) {

                                $city3aAirport1 = $city3Arrrow1['name'];

                                $city3aCity1 = $city3Arrrow1['cityName'];

                                $city3aCountry1 = $city3Arrrow1['countryCode'];

                            }

                            $city3markettingFN1 = $scheduleDescs[$city3legrefs2]['carrier']['marketingFlightNumber'];

                            $city3operatingCarrier1 = $scheduleDescs[$city3legrefs2]['carrier']['operating'];

                            if (isset($scheduleDescs[$city3legrefs2]['carrier']['operatingFlightNumber'])) {

                                $city3operatingFN1 = $scheduleDescs[$city3legrefs2]['carrier']['operatingFlightNumber'];

                            } else {

                                $city3operatingFN1 = $city3markettingFN1;

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city3Seat1 = $fareComponents[1]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city3Seat1 = "9";

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])) {

                                $city3BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city3BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];

                            }

                            $city3ElapsedTime1 = $scheduleDescs[$city3legrefs2]['elapsedTime'];

                            $city3TravelTime1 = floor($city3ElapsedTime1 / 60) . "H " . ($city3ElapsedTime1 - ((floor($city3ElapsedTime1 / 60)) * 60)) . "Min";

                            //Go Transit Time

                            $city3TransitTime = $city3TotalElapesd - ($city3ElapsedTime + $city3ElapsedTime1);

                            $city3TransitDuration = floor($city3TransitTime / 60) . "H " . ($city3TransitTime - ((floor($city3TransitTime / 60)) * 60)) . "Min";

                            $city3JourneyElapseTime = $city3TotalElapesd;

                            $city3JourneyDuration = floor($city3JourneyElapseTime / 60) . "H " . ($city3JourneyElapseTime - ((floor($city3JourneyElapseTime / 60)) * 60)) . "Min";

                            $transitDetails = "";

                            $City3Data = array(

                                "0" => array(

                                    "marketingcareer" => "$city3markettingCarrier",

                                    "marketingcareerName" => "$city3markettingCarrierName",

                                    "marketingflight" => "$city3markettingFN",

                                    "operatingcareer" => "$city3operatingCarrier",

                                    "operatingflight" => "$city3operatingFN",

                                    "departure" => "$city3DepartureFrom",

                                    "departureAirport" => "$city3dAirport",

                                    "departureLocation" => "$city3dCity , $city3dCountry",

                                    "departureTime" => "$city3dpTimedate",

                                    "arrival" => "$city3ArrivalTo",

                                    "arrivalTime" => "$city3arrTimedate",

                                    "arrivalAirport" => "$city3aAirport",

                                    "arrivalLocation" => "$city3aCity , $city3aCountry",

                                    "flightduration" => "$city3TravelTime",

                                    "bookingcode" => "$city3BookingCode",

                                    "seat" => "$city3Seat",

                                ),

                                "1" => array(

                                    "marketingcareer" => "$city3markettingCarrier1",

                                    "marketingcareerName" => "$city3markettingCarrierName1",

                                    "marketingflight" => "$city3markettingFN1",

                                    "operatingcareer" => "$city3operatingCarrier1",

                                    "operatingflight" => "$city3operatingFN1",

                                    "departure" => "$city3DepartureFrom1",

                                    "departureAirport" => "$city3dAirport1",

                                    "departureLocation" => "$city3dCity , $city3dCountry1",

                                    "departureTime" => "$city3dpTimedate1",

                                    "arrival" => "$city3ArrivalTo1",

                                    "arrivalTime" => "$city3arrTimedate1",

                                    "arrivalAirport" => "$city3aAirport1",

                                    "arrivalLocation" => "$city3aCity1 , $city3aCountry1",

                                    "flightduration" => "$city3TravelTime1",

                                    "bookingcode" => "$city3BookingCode1",

                                    "seat" => "$city3Seat1",

                                ),

                            );

                        }

                        $basic = array("system" => "Sabre",

                            "city" => "3",

                            "career" => "$vCarCode",

                            "careerName" => "$CarrieerName",

                            "lastTicketTime" => "$timelimit",

                            "BasePrice" => $totalBaseAmount,

                            "Taxes" => $totalTaxAmount,

                            "price" => "$agentPrice",

                            "clientPrice" => "$totalPrice",

                            "comission" => "$Commission",

                            "pricebreakdown" => $PriceBreakDown,

                            "transit" => $transitDetails,

                            "bags" => $Baggage,

                            "refundable" => $nonRefundable,

                            "segments" => array("0" => $City1Data, "1" => $City2Data, "2" => $City3Data),

                        );

                    } else if ($CityCount == 4) {

                        //City 1

                        $ref1 = $itineraries['legs'][0]['ref'];

                        $id1 = $ref1 - 1;

                        //City 2

                        $ref2 = $itineraries['legs'][1]['ref'];

                        $id2 = $ref2 - 1;

                        //City 3

                        $ref3 = $itineraries['legs'][2]['ref'];

                        $id3 = $ref3 - 1;

                        //City 4

                        $ref4 = $itineraries['legs'][3]['ref'];

                        $id4 = $ref4 - 1;

                        //Segment Count

                        $sgCount1 = count($legDescs[$id1]['schedules']); //echo $sgCount1;

                        $sgCount2 = count($legDescs[$id2]['schedules']); //echo $sgCount2;

                        $sgCount3 = count($legDescs[$id3]['schedules']); //echo $sgCount3;

                        $sgCount4 = count($legDescs[$id4]['schedules']); //echo $sgCount4;

                        //City 1

                        $city1TotalElapesd = $legDescs[$id1]['elapsedTime'];

                        //City 2

                        $city2TotalElapesd = $legDescs[$id2]['elapsedTime'];

                        //City 3

                        $city3TotalElapesd = $legDescs[$id3]['elapsedTime'];

                        //City 4

                        $city4TotalElapesd = $legDescs[$id4]['elapsedTime'];

                        //City1

                        if ($sgCount1 == 1) {

                            //Go

                            $city1lf1 = $legDescs[$id1]['schedules'][0]['ref'];

                            $city1legrefs = $city1lf1 - 1;

                            $city1departureTime = substr($scheduleDescs[$city1legrefs]['departure']['time'], 0, 5);

                            $city1dpTime = date("D d M Y", strtotime($City1DepDate . " " . $city1departureTime));

                            $city1dpTimedate = $City1DepDate . "T" . $city1departureTime . ':00';

                            $city1ArrivalTime = substr($scheduleDescs[$city1legrefs]['arrival']['time'], 0, 5);

                            $city1arrivalDate = 0;

                            if (isset($scheduleDescs[$city1legrefs]['arrival']['dateAdjustment'])) {

                                $city1arrivalDate += 1;

                            }

                            if ($city1arrivalDate == 1) {

                                $city1aDate = date('Y-m-d', strtotime("+1 day", strtotime($City1DepDate)));

                            } else {

                                $city1aDate = $City1DepDate;

                            }

                            $city1arrTime = date("D d M Y", strtotime($city1aDate . " " . $city1ArrivalTime));

                            $city1arrTimedate = $city1aDate . "T" . $city1ArrivalTime . ':00';

                            $city1ArrivalTo = $scheduleDescs[$city1legrefs]['arrival']['airport'];

                            $city1DepartureFrom = $scheduleDescs[$city1legrefs]['departure']['airport'];

                            $city1markettingCarrier = $scheduleDescs[$city1legrefs]['carrier']['marketing'];

                            $city1Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city1markettingCarrier' ");

                            $city1Crrow = mysqli_fetch_array($city1Crsql, MYSQLI_ASSOC);

                            if (!empty($city1Crrow)) {

                                $city1markettingCarrierName = $city1Crrow['name'];

                            }

                            // Departure Country

                            $city1Deptsql = mysqli_query($conn, "$Airportsql code='$city1DepartureFrom' ");

                            $city1Deptrow = mysqli_fetch_array($city1Deptsql, MYSQLI_ASSOC);

                            if (!empty($city1Deptrow)) {

                                $city1dAirport = $city1Deptrow['name'];

                                $city1dCity = $city1Deptrow['cityName'];

                                $city1dCountry = $city1Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city1Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city1ArrivalTo' ");

                            $city1Arrrow = mysqli_fetch_array($city1Arrsql, MYSQLI_ASSOC);

                            if (!empty($city1Arrrow)) {

                                $city1aAirport = $city1Arrrow['name'];

                                $city1aCity = $city1Arrrow['cityName'];

                                $city1aCountry = $city1Arrrow['countryCode'];

                            }

                            $city1markettingFN = $scheduleDescs[$city1legrefs]['carrier']['marketingFlightNumber'];

                            $city1operatingCarrier = $scheduleDescs[$city1legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city1legrefs]['carrier']['operatingFlightNumber'])) {

                                $city1operatingFN = $scheduleDescs[$city1legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city1operatingFN = 1;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city1BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city1BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city1ElapsedTime = $legDescs[$id1]['elapsedTime'];

                            $city1TravelTime = floor($city1ElapsedTime / 60) . "H " . ($city1ElapsedTime - ((floor($city1ElapsedTime / 60)) * 60)) . "Min";

                            //transit Time

                            $transitDetails = array("0" => array("transit1" => "0"),

                                "1" => array("transit1" => "0"));

                            $City1Data = array("0" => array("marketingcareer" => "$city1markettingCarrier",

                                "marketingcareerName" => "$city1markettingCarrierName",

                                "marketingflight" => "$city1markettingFN",

                                "operatingcareer" => "$city1operatingCarrier",

                                "operatingflight" => "$city1operatingFN",

                                "departure" => "$city1DepartureFrom",

                                "departureAirport" => "$city1dAirport",

                                "departureLocation" => "$city1dCity , $city1dCountry",

                                "departureTime" => "$city1dpTimedate",

                                "arrival" => "$city1ArrivalTo",

                                "arrivalTime" => "$city1arrTimedate",

                                "arrivalAirport" => "$city1aAirport",

                                "arrivalLocation" => "$city1aCity , $city1aCountry",

                                "flightduration" => "$city1TravelTime",

                                "bookingcode" => "$city1BookingCode",

                                "seat" => "$city1Seat"),

                            );

                        } else if ($sgCount1 == 2) {

                            //Go 1

                            $city1lf1 = $legDescs[$id1]['schedules'][0]['ref'];

                            $city1legrefs = $city1lf1 - 1;

                            $city1departureTime = substr($scheduleDescs[$city1legrefs]['departure']['time'], 0, 5);

                            $city1dpTime = date("D d M Y", strtotime($City1DepDate . " " . $city1departureTime));

                            $city1dpTimedate = $City1DepDate . "T" . $city1departureTime . ':00';

                            $city1ArrivalTime = substr($scheduleDescs[$city1legrefs]['arrival']['time'], 0, 5);

                            $city1arrivalDate = 0;

                            if (isset($scheduleDescs[$city1legrefs]['arrival']['dateAdjustment'])) {

                                $city1arrivalDate += 1;

                            }

                            if ($city1arrivalDate == 1) {

                                $city1aDate = date('Y-m-d', strtotime("+1 day", strtotime($City1DepDate)));

                            } else {

                                $city1aDate = $City1DepDate;

                            }

                            $city1arrTime = date("D d M Y", strtotime($city1aDate . " " . $city1ArrivalTime));

                            $city1arrTimedate = $city1aDate . "T" . $city1ArrivalTime . ':00';

                            $city1ArrivalTo = $scheduleDescs[$city1legrefs]['arrival']['airport'];

                            $city1DepartureFrom = $scheduleDescs[$city1legrefs]['departure']['airport'];

                            $city1markettingCarrier = $scheduleDescs[$city1legrefs]['carrier']['marketing'];

                            $city1Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city1markettingCarrier' ");

                            $city1Crrow = mysqli_fetch_array($city1Crsql, MYSQLI_ASSOC);

                            if (!empty($city1Crrow)) {

                                $city1markettingCarrierName = $city1Crrow['name'];

                            }

                            // Departure Country

                            $city1Deptsql = mysqli_query($conn, "$Airportsql code='$city1DepartureFrom' ");

                            $city1Deptrow = mysqli_fetch_array($city1Deptsql, MYSQLI_ASSOC);

                            if (!empty($city1Deptrow)) {

                                $city1dAirport = $city1Deptrow['name'];

                                $city1dCity = $city1Deptrow['cityName'];

                                $city1dCountry = $city1Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city1Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city1ArrivalTo' ");

                            $city1Arrrow = mysqli_fetch_array($city1Arrsql, MYSQLI_ASSOC);

                            if (!empty($city1Arrrow)) {

                                $city1aAirport = $city1Arrrow['name'];

                                $city1aCity = $city1Arrrow['cityName'];

                                $city1aCountry = $city1Arrrow['countryCode'];

                            }

                            $city1markettingFN = $scheduleDescs[$city1legrefs]['carrier']['marketingFlightNumber'];

                            $city1operatingCarrier = $scheduleDescs[$city1legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city1legrefs]['carrier']['operatingFlightNumber'])) {

                                $city1operatingFN = $scheduleDescs[$city1legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city1operatingFN = $city1markettingFN;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city1BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city1BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city1ElapsedTime = $scheduleDescs[$city1legrefs]['elapsedTime'];

                            $city1TravelTime = floor($city1ElapsedTime / 60) . "H " . ($city1ElapsedTime - ((floor($city1ElapsedTime / 60)) * 60)) . "Min";

                            //Go 2

                            $city1lf2 = $legDescs[$id1]['schedules'][1]['ref'];

                            $city1legrefs2 = $city1lf2 - 1;

                            $city1DepartureDate1 = 0;

                            if (isset($legDescs[$id1]['schedules'][1]['departureDateAdjustment'])) {

                                $city1DepartureDate1 += 1;

                            }

                            if ($city1DepartureDate1 == 1) {

                                $city1depDate1 = date('Y-m-d', strtotime("+1 day", strtotime($City1DepDate)));

                            } else {

                                $city1depDate1 = $City1DepDate;

                            }

                            $city1departureTime1 = substr($scheduleDescs[$city1legrefs2]['departure']['time'], 0, 5);

                            $city1dpTime1 = date("D d M Y", strtotime($city1depDate1 . " " . $city1departureTime1));

                            $city1dpTimedate1 = $city1depDate1 . "T" . $city1departureTime1 . ':00';

                            $city1ArrivalTime1 = substr($scheduleDescs[$city1legrefs2]['arrival']['time'], 0, 5);

                            $city1arrivalDate1 = 0;

                            if (isset($scheduleDescs[$city1legrefs2]['arrival']['dateAdjustment'])) {

                                $city1arrivalDate1 += 1;

                            }

                            if ($city1arrivalDate1 == 1) {

                                $city1aDate1 = date('Y-m-d', strtotime("+1 day", strtotime($city1depDate1)));

                            } else {

                                $city1aDate1 = $city1depDate1;

                            }

                            $city1arrTime1 = date("D d M Y", strtotime($city1aDate1 . " " . $city1ArrivalTime1));

                            $city1arrTimedate1 = $city1aDate1 . "T" . $city1ArrivalTime1 . ':00';

                            $city1ArrivalTo1 = $scheduleDescs[$city1legrefs2]['arrival']['airport'];

                            $city1DepartureFrom1 = $scheduleDescs[$city1legrefs2]['departure']['airport'];

                            $city1markettingCarrier1 = $scheduleDescs[$city1legrefs2]['carrier']['marketing'];

                            $city1Crsql1 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city1markettingCarrier1' ");

                            $city1Crrow1 = mysqli_fetch_array($city1Crsql1, MYSQLI_ASSOC);

                            if (!empty($city1Crrow1)) {

                                $city1markettingCarrierName1 = $city1Crrow1['name'];

                            }

                            // Departure Country

                            $city1Deptsql1 = mysqli_query($conn, "$Airportsql code='$city1DepartureFrom1' ");

                            $city1Deptrow1 = mysqli_fetch_array($city1Deptsql1, MYSQLI_ASSOC);

                            if (!empty($city1Deptrow1)) {

                                $city1dAirport1 = $city1Deptrow1['name'];

                                $city1dCity1 = $city1Deptrow1['cityName'];

                                $city1dCountry1 = $city1Deptrow1['countryCode'];

                            }

                            // Arrival Country

                            $city1Arrsql1 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city1ArrivalTo1' ");

                            $city1Arrrow1 = mysqli_fetch_array($city1Arrsql1, MYSQLI_ASSOC);

                            if (!empty($city1Arrrow)) {

                                $city1aAirport1 = $city1Arrrow1['name'];

                                $city1aCity1 = $city1Arrrow1['cityName'];

                                $city1aCountry1 = $city1Arrrow1['countryCode'];

                            }

                            $city1markettingFN1 = $scheduleDescs[$city1legrefs2]['carrier']['marketingFlightNumber'];

                            $city1operatingCarrier1 = $scheduleDescs[$city1legrefs2]['carrier']['operating'];

                            if (isset($scheduleDescs[$city1legrefs2]['carrier']['operatingFlightNumber'])) {

                                $city1operatingFN1 = $scheduleDescs[$city1legrefs2]['carrier']['operatingFlightNumber'];

                            } else {

                                $city1operatingFN1 = $city1markettingFN1;

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat1 = $fareComponents[1]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city1Seat1 = "9";

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])) {

                                $city1BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city1BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];

                            }

                            $city1ElapsedTime1 = $scheduleDescs[$city1legrefs2]['elapsedTime'];

                            $city1TravelTime1 = floor($city1ElapsedTime1 / 60) . "H " . ($city1ElapsedTime1 - ((floor($city1ElapsedTime1 / 60)) * 60)) . "Min";

                            //Go Transit Time

                            $city1TransitTime = $city1TotalElapesd - ($city1ElapsedTime + $city1ElapsedTime1);

                            $city1TransitDuration = floor($city1TransitTime / 60) . "H " . ($city1TransitTime - ((floor($city1TransitTime / 60)) * 60)) . "Min";

                            $city1JourneyElapseTime = $city1TotalElapesd;

                            $city1JourneyDuration = floor($city1JourneyElapseTime / 60) . "H " . ($city1JourneyElapseTime - ((floor($city1JourneyElapseTime / 60)) * 60)) . "Min";

                            $City1Data = array(

                                "0" => array(

                                    "marketingcareer" => "$city1markettingCarrier",

                                    "marketingcareerName" => "$city1markettingCarrierName",

                                    "marketingflight" => "$city1markettingFN",

                                    "operatingcareer" => "$city1operatingCarrier",

                                    "operatingflight" => "$city1operatingFN",

                                    "departure" => "$city1DepartureFrom",

                                    "departureAirport" => "$city1dAirport",

                                    "departureLocation" => "$city1dCity , $city1dCountry",

                                    "departureTime" => "$city1dpTimedate",

                                    "arrival" => "$city1ArrivalTo",

                                    "arrivalTime" => "$city1arrTimedate",

                                    "arrivalAirport" => "$city1aAirport",

                                    "arrivalLocation" => "$city1aCity , $city1aCountry",

                                    "flightduration" => "$city1TravelTime",

                                    "bookingcode" => "$city1BookingCode",

                                    "seat" => "$city1Seat",

                                ),

                                "1" => array(

                                    "marketingcareer" => "$city1markettingCarrier1",

                                    "marketingcareerName" => "$city1markettingCarrierName1",

                                    "marketingflight" => "$city1markettingFN1",

                                    "operatingcareer" => "$city1operatingCarrier1",

                                    "operatingflight" => "$city1operatingFN1",

                                    "departure" => "$city1DepartureFrom1",

                                    "departureAirport" => "$city1dAirport1",

                                    "departureLocation" => "$city1dCity , $city1dCountry1",

                                    "departureTime" => "$city1dpTimedate1",

                                    "arrival" => "$city1ArrivalTo1",

                                    "arrivalTime" => "$city1arrTimedate1",

                                    "arrivalAirport" => "$city1aAirport1",

                                    "arrivalLocation" => "$city1aCity1 , $city1aCountry1",

                                    "flightduration" => "$city1TravelTime1",

                                    "bookingcode" => "$city1BookingCode1",

                                    "seat" => "$city1Seat1",

                                ),

                            );

                        }

                        //City 2

                        if ($sgCount2 == 1) {

                            //Go

                            $city2lf1 = $legDescs[$id2]['schedules'][0]['ref'];

                            $city2legrefs = $city2lf1 - 1;

                            $city2departureTime = substr($scheduleDescs[$city2legrefs]['departure']['time'], 0, 5);

                            $city2dpTime = date("D d M Y", strtotime($City2DepDate . " " . $city2departureTime));

                            $city2dpTimedate = $City2DepDate . "T" . $city2departureTime . ':00';

                            $city2ArrivalTime = substr($scheduleDescs[$city2legrefs]['arrival']['time'], 0, 5);

                            $city2arrivalDate = 0;

                            if (isset($scheduleDescs[$city2legrefs]['arrival']['dateAdjustment'])) {

                                $city2arrivalDate += 1;

                            }

                            if ($city2arrivalDate == 1) {

                                $city2aDate = date('Y-m-d', strtotime("+1 day", strtotime($City2DepDate)));

                            } else {

                                $city2aDate = $City2DepDate;

                            }

                            $city2arrTime = date("D d M Y", strtotime($city2aDate . " " . $city2ArrivalTime));

                            $city2arrTimedate = $city2aDate . "T" . $city2ArrivalTime . ':00';

                            $city2ArrivalTo = $scheduleDescs[$city2legrefs]['arrival']['airport'];

                            $city2DepartureFrom = $scheduleDescs[$city2legrefs]['departure']['airport'];

                            $city2markettingCarrier = $scheduleDescs[$city2legrefs]['carrier']['marketing'];

                            $city2Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city2markettingCarrier' ");

                            $city2Crrow = mysqli_fetch_array($city2Crsql, MYSQLI_ASSOC);

                            if (!empty($city2Crrow)) {

                                $city2markettingCarrierName = $city2Crrow['name'];

                            }

                            // Departure Country

                            $city2Deptsql = mysqli_query($conn, "$Airportsql code='$city2DepartureFrom' ");

                            $city2Deptrow = mysqli_fetch_array($city2Deptsql, MYSQLI_ASSOC);

                            if (!empty($city2Deptrow)) {

                                $city2dAirport = $city2Deptrow['name'];

                                $city2dCity = $city2Deptrow['cityName'];

                                $city2dCountry = $city2Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city2Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city2ArrivalTo' ");

                            $city2Arrrow = mysqli_fetch_array($city2Arrsql, MYSQLI_ASSOC);

                            if (!empty($city2Arrrow)) {

                                $city2aAirport = $city2Arrrow['name'];

                                $city2aCity = $city2Arrrow['cityName'];

                                $city2aCountry = $city2Arrrow['countryCode'];

                            }

                            $city2markettingFN = $scheduleDescs[$city2legrefs]['carrier']['marketingFlightNumber'];

                            $city2operatingCarrier = $scheduleDescs[$city2legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city2legrefs]['carrier']['operatingFlightNumber'])) {

                                $city2operatingFN = $scheduleDescs[$city2legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city2operatingFN = 1;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city2BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city2BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city2ElapsedTime = $legDescs[$id2]['elapsedTime'];

                            $city2TravelTime = floor($city2ElapsedTime / 60) . "H " . ($city2ElapsedTime - ((floor($city2ElapsedTime / 60)) * 60)) . "Min";

                            //transit Time

                            $transitDetails = array("0" => array("transit1" => "0"),

                                "1" => array("transit1" => "0"));

                            $City2Data = array("0" => array("marketingcareer" => "$city2markettingCarrier",

                                "marketingcareerName" => "$city2markettingCarrierName",

                                "marketingflight" => "$city2markettingFN",

                                "operatingcareer" => "$city2operatingCarrier",

                                "operatingflight" => "$city2operatingFN",

                                "departure" => "$city2DepartureFrom",

                                "departureAirport" => "$city2dAirport",

                                "departureLocation" => "$city2dCity , $city2dCountry",

                                "departureTime" => "$city2dpTimedate",

                                "arrival" => "$city2ArrivalTo",

                                "arrivalTime" => "$city2arrTimedate",

                                "arrivalAirport" => "$city2aAirport",

                                "arrivalLocation" => "$city2aCity , $city2aCountry",

                                "flightduration" => "$city2TravelTime",

                                "bookingcode" => "$city2BookingCode",

                                "seat" => "$city2Seat"),

                            );

                        } else if ($sgCount2 == 2) {

                            //Go 1

                            $city2lf1 = $legDescs[$id2]['schedules'][0]['ref'];

                            $city2legrefs = $city2lf1 - 1;

                            $city2departureTime = substr($scheduleDescs[$city2legrefs]['departure']['time'], 0, 5);

                            $city2dpTime = date("D d M Y", strtotime($City2DepDate . " " . $city2departureTime));

                            $city2dpTimedate = $City2DepDate . "T" . $city2departureTime . ':00';

                            $city2ArrivalTime = substr($scheduleDescs[$city2legrefs]['arrival']['time'], 0, 5);

                            $city2arrivalDate = 0;

                            if (isset($scheduleDescs[$city2legrefs]['arrival']['dateAdjustment'])) {

                                $city2arrivalDate += 1;

                            }

                            if ($city2arrivalDate == 1) {

                                $city2aDate = date('Y-m-d', strtotime("+1 day", strtotime($City2DepDate)));

                            } else {

                                $city2aDate = $City2DepDate;

                            }

                            $city2arrTime = date("D d M Y", strtotime($city2aDate . " " . $city2ArrivalTime));

                            $city2arrTimedate = $city2aDate . "T" . $city2ArrivalTime . ':00';

                            $city2ArrivalTo = $scheduleDescs[$city2legrefs]['arrival']['airport'];

                            $city2DepartureFrom = $scheduleDescs[$city2legrefs]['departure']['airport'];

                            $city2markettingCarrier = $scheduleDescs[$city2legrefs]['carrier']['marketing'];

                            $city2Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city2markettingCarrier' ");

                            $city2Crrow = mysqli_fetch_array($city2Crsql, MYSQLI_ASSOC);

                            if (!empty($city2Crrow)) {

                                $city2markettingCarrierName = $city2Crrow['name'];

                            }

                            // Departure Country

                            $city2Deptsql = mysqli_query($conn, "$Airportsql code='$city2DepartureFrom' ");

                            $city2Deptrow = mysqli_fetch_array($city2Deptsql, MYSQLI_ASSOC);

                            if (!empty($city2Deptrow)) {

                                $city2dAirport = $city2Deptrow['name'];

                                $city2dCity = $city2Deptrow['cityName'];

                                $city2dCountry = $city2Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city2Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city2ArrivalTo' ");

                            $city2Arrrow = mysqli_fetch_array($city2Arrsql, MYSQLI_ASSOC);

                            if (!empty($city2Arrrow)) {

                                $city2aAirport = $city2Arrrow['name'];

                                $city2aCity = $city2Arrrow['cityName'];

                                $city2aCountry = $city2Arrrow['countryCode'];

                            }

                            $city2markettingFN = $scheduleDescs[$city2legrefs]['carrier']['marketingFlightNumber'];

                            $city2operatingCarrier = $scheduleDescs[$city2legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city2legrefs]['carrier']['operatingFlightNumber'])) {

                                $city2operatingFN = $scheduleDescs[$city2legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city2operatingFN = $city2markettingFN;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city2BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city2BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city2ElapsedTime = $scheduleDescs[$city2legrefs]['elapsedTime'];

                            $city2TravelTime = floor($city2ElapsedTime / 60) . "H " . ($city2ElapsedTime - ((floor($city2ElapsedTime / 60)) * 60)) . "Min";

                            //Go 2

                            $city2lf2 = $legDescs[$id2]['schedules'][1]['ref'];

                            $city2legrefs2 = $city2lf2 - 1;

                            $city2DepartureDate1 = 0;

                            if (isset($legDescs[$id2]['schedules'][1]['departureDateAdjustment'])) {

                                $city2DepartureDate1 += 1;

                            }

                            if ($city2DepartureDate1 == 1) {

                                $City2DepDate1 = date('Y-m-d', strtotime("+1 day", strtotime($City2DepDate)));

                            } else {

                                $City2DepDate1 = $City2DepDate;

                            }

                            $city2departureTime1 = substr($scheduleDescs[$city2legrefs2]['departure']['time'], 0, 5);

                            $city2dpTime1 = date("D d M Y", strtotime($City2DepDate1 . " " . $city2departureTime1));

                            $city2dpTimedate1 = $City2DepDate1 . "T" . $city2departureTime1 . ':00';

                            $city2ArrivalTime1 = substr($scheduleDescs[$city2legrefs2]['arrival']['time'], 0, 5);

                            $city2arrivalDate1 = 0;

                            if (isset($scheduleDescs[$city2legrefs2]['arrival']['dateAdjustment'])) {

                                $city2arrivalDate1 += 1;

                            }

                            if ($city2arrivalDate1 == 1) {

                                $city2aDate1 = date('Y-m-d', strtotime("+1 day", strtotime($City2DepDate1)));

                            } else {

                                $city2aDate1 = $City2DepDate1;

                            }

                            $city2arrTime1 = date("D d M Y", strtotime($city2aDate1 . " " . $city2ArrivalTime1));

                            $city2arrTimedate1 = $city2aDate1 . "T" . $city2ArrivalTime1 . ':00';

                            $city2ArrivalTo1 = $scheduleDescs[$city2legrefs2]['arrival']['airport'];

                            $city2DepartureFrom1 = $scheduleDescs[$city2legrefs2]['departure']['airport'];

                            $city2markettingCarrier1 = $scheduleDescs[$city2legrefs2]['carrier']['marketing'];

                            $city2Crsql1 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city2markettingCarrier1' ");

                            $city2Crrow1 = mysqli_fetch_array($city2Crsql1, MYSQLI_ASSOC);

                            if (!empty($city2Crrow1)) {

                                $city2markettingCarrierName1 = $city2Crrow1['name'];

                            }

                            // Departure Country

                            $city2Deptsql1 = mysqli_query($conn, "$Airportsql code='$city2DepartureFrom1' ");

                            $city2Deptrow1 = mysqli_fetch_array($city2Deptsql1, MYSQLI_ASSOC);

                            if (!empty($city2Deptrow1)) {

                                $city2dAirport1 = $city2Deptrow1['name'];

                                $city2dCity1 = $city2Deptrow1['cityName'];

                                $city2dCountry1 = $city2Deptrow1['countryCode'];

                            }

                            // Arrival Country

                            $city2Arrsql1 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city2ArrivalTo1' ");

                            $city2Arrrow1 = mysqli_fetch_array($city2Arrsql1, MYSQLI_ASSOC);

                            if (!empty($city2Arrrow)) {

                                $city2aAirport1 = $city2Arrrow1['name'];

                                $city2aCity1 = $city2Arrrow1['cityName'];

                                $city2aCountry1 = $city2Arrrow1['countryCode'];

                            }

                            $city2markettingFN1 = $scheduleDescs[$city2legrefs2]['carrier']['marketingFlightNumber'];

                            $city2operatingCarrier1 = $scheduleDescs[$city2legrefs2]['carrier']['operating'];

                            if (isset($scheduleDescs[$city2legrefs2]['carrier']['operatingFlightNumber'])) {

                                $city2operatingFN1 = $scheduleDescs[$city2legrefs2]['carrier']['operatingFlightNumber'];

                            } else {

                                $city2operatingFN1 = $city2markettingFN1;

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat1 = $fareComponents[1]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city2Seat1 = "9";

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])) {

                                $city2BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city2BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];

                            }

                            $city2ElapsedTime1 = $scheduleDescs[$city2legrefs2]['elapsedTime'];

                            $city2TravelTime1 = floor($city2ElapsedTime1 / 60) . "H " . ($city2ElapsedTime1 - ((floor($city2ElapsedTime1 / 60)) * 60)) . "Min";

                            //Go Transit Time

                            $city2TransitTime = $city2TotalElapesd - ($city2ElapsedTime + $city2ElapsedTime1);

                            $city2TransitDuration = floor($city2TransitTime / 60) . "H " . ($city2TransitTime - ((floor($city2TransitTime / 60)) * 60)) . "Min";

                            $city2JourneyElapseTime = $city2TotalElapesd;

                            $city2JourneyDuration = floor($city2JourneyElapseTime / 60) . "H " . ($city2JourneyElapseTime - ((floor($city2JourneyElapseTime / 60)) * 60)) . "Min";

                            $City2Data = array(

                                "0" => array(

                                    "marketingcareer" => "$city2markettingCarrier",

                                    "marketingcareerName" => "$city2markettingCarrierName",

                                    "marketingflight" => "$city2markettingFN",

                                    "operatingcareer" => "$city2operatingCarrier",

                                    "operatingflight" => "$city2operatingFN",

                                    "departure" => "$city2DepartureFrom",

                                    "departureAirport" => "$city2dAirport",

                                    "departureLocation" => "$city2dCity , $city2dCountry",

                                    "departureTime" => "$city2dpTimedate",

                                    "arrival" => "$city2ArrivalTo",

                                    "arrivalTime" => "$city2arrTimedate",

                                    "arrivalAirport" => "$city2aAirport",

                                    "arrivalLocation" => "$city2aCity , $city2aCountry",

                                    "flightduration" => "$city2TravelTime",

                                    "bookingcode" => "$city2BookingCode",

                                    "seat" => "$city2Seat",

                                ),

                                "1" => array(

                                    "marketingcareer" => "$city2markettingCarrier1",

                                    "marketingcareerName" => "$city2markettingCarrierName1",

                                    "marketingflight" => "$city2markettingFN1",

                                    "operatingcareer" => "$city2operatingCarrier1",

                                    "operatingflight" => "$city2operatingFN1",

                                    "departure" => "$city2DepartureFrom1",

                                    "departureAirport" => "$city2dAirport1",

                                    "departureLocation" => "$city2dCity , $city2dCountry1",

                                    "departureTime" => "$city2dpTimedate1",

                                    "arrival" => "$city2ArrivalTo1",

                                    "arrivalTime" => "$city2arrTimedate1",

                                    "arrivalAirport" => "$city2aAirport1",

                                    "arrivalLocation" => "$city2aCity1 , $city2aCountry1",

                                    "flightduration" => "$city2TravelTime1",

                                    "bookingcode" => "$city2BookingCode1",

                                    "seat" => "$city2Seat1",

                                ),

                            );

                        }

                        //City 3

                        if ($sgCount3 == 1) {

                            //Go

                            $city3lf1 = $legDescs[$id3]['schedules'][0]['ref'];

                            $city3legrefs = $city3lf1 - 1;

                            $city3departureTime = substr($scheduleDescs[$city3legrefs]['departure']['time'], 0, 5);

                            $city3dpTime = date("D d M Y", strtotime($City3DepDate . " " . $city3departureTime));

                            $city3dpTimedate = $City3DepDate . "T" . $city3departureTime . ':00';

                            $city3ArrivalTime = substr($scheduleDescs[$city3legrefs]['arrival']['time'], 0, 5);

                            $city3arrivalDate = 0;

                            if (isset($scheduleDescs[$city3legrefs]['arrival']['dateAdjustment'])) {

                                $city3arrivalDate += 1;

                            }

                            if ($city3arrivalDate == 1) {

                                $city3aDate = date('Y-m-d', strtotime("+1 day", strtotime($City3DepDate)));

                            } else {

                                $city3aDate = $City3DepDate;

                            }

                            $city3arrTime = date("D d M Y", strtotime($city3aDate . " " . $city3ArrivalTime));

                            $city3arrTimedate = $city3aDate . "T" . $city3ArrivalTime . ':00';

                            $city3ArrivalTo = $scheduleDescs[$city3legrefs]['arrival']['airport'];

                            $city3DepartureFrom = $scheduleDescs[$city3legrefs]['departure']['airport'];

                            $city3markettingCarrier = $scheduleDescs[$city3legrefs]['carrier']['marketing'];

                            $city3Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city3markettingCarrier' ");

                            $city3Crrow = mysqli_fetch_array($city3Crsql, MYSQLI_ASSOC);

                            if (!empty($city3Crrow)) {

                                $city3markettingCarrierName = $city3Crrow['name'];

                            }

                            // Departure Country

                            $city3Deptsql = mysqli_query($conn, "$Airportsql code='$city3DepartureFrom' ");

                            $city3Deptrow = mysqli_fetch_array($city3Deptsql, MYSQLI_ASSOC);

                            if (!empty($city3Deptrow)) {

                                $city3dAirport = $city3Deptrow['name'];

                                $city3dCity = $city3Deptrow['cityName'];

                                $city3dCountry = $city3Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city3Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city3ArrivalTo' ");

                            $city3Arrrow = mysqli_fetch_array($city3Arrsql, MYSQLI_ASSOC);

                            if (!empty($city3Arrrow)) {

                                $city3aAirport = $city3Arrrow['name'];

                                $city3aCity = $city3Arrrow['cityName'];

                                $city3aCountry = $city3Arrrow['countryCode'];

                            }

                            $city3markettingFN = $scheduleDescs[$city3legrefs]['carrier']['marketingFlightNumber'];

                            $city3operatingCarrier = $scheduleDescs[$city3legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city3legrefs]['carrier']['operatingFlightNumber'])) {

                                $city3operatingFN = $scheduleDescs[$city3legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city3operatingFN = 1;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city3Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city3Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city3BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city3BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city3ElapsedTime = $legDescs[$id2]['elapsedTime'];

                            $city3TravelTime = floor($city3ElapsedTime / 60) . "H " . ($city3ElapsedTime - ((floor($city3ElapsedTime / 60)) * 60)) . "Min";

                            //transit Time

                            $transitDetails = array("0" => array("transit1" => "0"),

                                "1" => array("transit1" => "0"));

                            $City3Data = array("0" => array("marketingcareer" => "$city3markettingCarrier",

                                "marketingcareerName" => "$city3markettingCarrierName",

                                "marketingflight" => "$city3markettingFN",

                                "operatingcareer" => "$city3operatingCarrier",

                                "operatingflight" => "$city3operatingFN",

                                "departure" => "$city3DepartureFrom",

                                "departureAirport" => "$city3dAirport",

                                "departureLocation" => "$city3dCity , $city3dCountry",

                                "departureTime" => "$city3dpTimedate",

                                "arrival" => "$city3ArrivalTo",

                                "arrivalTime" => "$city3arrTimedate",

                                "arrivalAirport" => "$city3aAirport",

                                "arrivalLocation" => "$city3aCity , $city3aCountry",

                                "flightduration" => "$city3TravelTime",

                                "bookingcode" => "$city3BookingCode",

                                "seat" => "$city3Seat"),

                            );

                        } else if ($sgCount3 == 2) {

                            //Go 1

                            $city3lf1 = $legDescs[$id3]['schedules'][0]['ref'];

                            $city3legrefs = $city3lf1 - 1;

                            $city3departureTime = substr($scheduleDescs[$city3legrefs]['departure']['time'], 0, 5);

                            $city3dpTime = date("D d M Y", strtotime($City3DepDate . " " . $city3departureTime));

                            $city3dpTimedate = $City3DepDate . "T" . $city3departureTime . ':00';

                            $city3ArrivalTime = substr($scheduleDescs[$city3legrefs]['arrival']['time'], 0, 5);

                            $city3arrivalDate = 0;

                            if (isset($scheduleDescs[$city3legrefs]['arrival']['dateAdjustment'])) {

                                $city3arrivalDate += 1;

                            }

                            if ($city3arrivalDate == 1) {

                                $city3aDate = date('Y-m-d', strtotime("+1 day", strtotime($City3DepDate)));

                            } else {

                                $city3aDate = $City3DepDate;

                            }

                            $city3arrTime = date("D d M Y", strtotime($city3aDate . " " . $city3ArrivalTime));

                            $city3arrTimedate = $city3aDate . "T" . $city3ArrivalTime . ':00';

                            $city3ArrivalTo = $scheduleDescs[$city3legrefs]['arrival']['airport'];

                            $city3DepartureFrom = $scheduleDescs[$city3legrefs]['departure']['airport'];

                            $city3markettingCarrier = $scheduleDescs[$city3legrefs]['carrier']['marketing'];

                            $city3Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city3markettingCarrier' ");

                            $city3Crrow = mysqli_fetch_array($city3Crsql, MYSQLI_ASSOC);

                            if (!empty($city3Crrow)) {

                                $city3markettingCarrierName = $city3Crrow['name'];

                            }

                            // Departure Country

                            $city3Deptsql = mysqli_query($conn, "$Airportsql code='$city3DepartureFrom' ");

                            $city3Deptrow = mysqli_fetch_array($city3Deptsql, MYSQLI_ASSOC);

                            if (!empty($city3Deptrow)) {

                                $city3dAirport = $city3Deptrow['name'];

                                $city3dCity = $city3Deptrow['cityName'];

                                $city3dCountry = $city3Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city3Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city3ArrivalTo' ");

                            $city3Arrrow = mysqli_fetch_array($city3Arrsql, MYSQLI_ASSOC);

                            if (!empty($city3Arrrow)) {

                                $city3aAirport = $city3Arrrow['name'];

                                $city3aCity = $city3Arrrow['cityName'];

                                $city3aCountry = $city3Arrrow['countryCode'];

                            }

                            $city3markettingFN = $scheduleDescs[$city3legrefs]['carrier']['marketingFlightNumber'];

                            $city3operatingCarrier = $scheduleDescs[$city3legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city3legrefs]['carrier']['operatingFlightNumber'])) {

                                $city3operatingFN = $scheduleDescs[$city3legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city3operatingFN = $city3markettingFN;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city3Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city3Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city3BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city3BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city3ElapsedTime = $scheduleDescs[$city3legrefs]['elapsedTime'];

                            $city3TravelTime = floor($city3ElapsedTime / 60) . "H " . ($city3ElapsedTime - ((floor($city3ElapsedTime / 60)) * 60)) . "Min";

                            //Go 2

                            $city3lf2 = $legDescs[$id3]['schedules'][1]['ref'];

                            $city3legrefs2 = $city3lf2 - 1;

                            $city3DepartureDate1 = 0;

                            if (isset($legDescs[$id2]['schedules'][1]['departureDateAdjustment'])) {

                                $city3DepartureDate1 += 1;

                            }

                            if ($city3DepartureDate1 == 1) {

                                $City3DepDate1 = date('Y-m-d', strtotime("+1 day", strtotime($City3DepDate)));

                            } else {

                                $City3DepDate1 = $City3DepDate;

                            }

                            $city3departureTime1 = substr($scheduleDescs[$city3legrefs2]['departure']['time'], 0, 5);

                            $city3dpTime1 = date("D d M Y", strtotime($City3DepDate1 . " " . $city3departureTime1));

                            $city3dpTimedate1 = $City3DepDate1 . "T" . $city3departureTime1 . ':00';

                            $city3ArrivalTime1 = substr($scheduleDescs[$city3legrefs2]['arrival']['time'], 0, 5);

                            $city3arrivalDate1 = 0;

                            if (isset($scheduleDescs[$city3legrefs2]['arrival']['dateAdjustment'])) {

                                $city3arrivalDate1 += 1;

                            }

                            if ($city3arrivalDate1 == 1) {

                                $city3aDate1 = date('Y-m-d', strtotime("+1 day", strtotime($City3DepDate1)));

                            } else {

                                $city3aDate1 = $City3DepDate1;

                            }

                            $city3arrTime1 = date("D d M Y", strtotime($city3aDate1 . " " . $city3ArrivalTime1));

                            $city3arrTimedate1 = $city3aDate1 . "T" . $city3ArrivalTime1 . ':00';

                            $city3ArrivalTo1 = $scheduleDescs[$city3legrefs2]['arrival']['airport'];

                            $city3DepartureFrom1 = $scheduleDescs[$city3legrefs2]['departure']['airport'];

                            $city3markettingCarrier1 = $scheduleDescs[$city3legrefs2]['carrier']['marketing'];

                            $city3Crsql1 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city3markettingCarrier1' ");

                            $city3Crrow1 = mysqli_fetch_array($city3Crsql1, MYSQLI_ASSOC);

                            if (!empty($city3Crrow1)) {

                                $city3markettingCarrierName1 = $city3Crrow1['name'];

                            }

                            // Departure Country

                            $city3Deptsql1 = mysqli_query($conn, "$Airportsql code='$city3DepartureFrom1' ");

                            $city3Deptrow1 = mysqli_fetch_array($city3Deptsql1, MYSQLI_ASSOC);

                            if (!empty($city3Deptrow1)) {

                                $city3dAirport1 = $city3Deptrow1['name'];

                                $city3dCity1 = $city3Deptrow1['cityName'];

                                $city3dCountry1 = $city3Deptrow1['countryCode'];

                            }

                            // Arrival Country

                            $city3Arrsql1 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city3ArrivalTo1' ");

                            $city3Arrrow1 = mysqli_fetch_array($city3Arrsql1, MYSQLI_ASSOC);

                            if (!empty($city3Arrrow)) {

                                $city3aAirport1 = $city3Arrrow1['name'];

                                $city3aCity1 = $city3Arrrow1['cityName'];

                                $city3aCountry1 = $city3Arrrow1['countryCode'];

                            }

                            $city3markettingFN1 = $scheduleDescs[$city3legrefs2]['carrier']['marketingFlightNumber'];

                            $city3operatingCarrier1 = $scheduleDescs[$city3legrefs2]['carrier']['operating'];

                            if (isset($scheduleDescs[$city3legrefs2]['carrier']['operatingFlightNumber'])) {

                                $city3operatingFN1 = $scheduleDescs[$city3legrefs2]['carrier']['operatingFlightNumber'];

                            } else {

                                $city3operatingFN1 = $city3markettingFN1;

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city3Seat1 = $fareComponents[1]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city3Seat1 = "9";

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])) {

                                $city3BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city3BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];

                            }

                            $city3ElapsedTime1 = $scheduleDescs[$city3legrefs2]['elapsedTime'];

                            $city3TravelTime1 = floor($city3ElapsedTime1 / 60) . "H " . ($city3ElapsedTime1 - ((floor($city3ElapsedTime1 / 60)) * 60)) . "Min";

                            //Go Transit Time

                            $city3TransitTime = $city3TotalElapesd - ($city3ElapsedTime + $city3ElapsedTime1);

                            $city3TransitDuration = floor($city3TransitTime / 60) . "H " . ($city3TransitTime - ((floor($city3TransitTime / 60)) * 60)) . "Min";

                            $city3JourneyElapseTime = $city3TotalElapesd;

                            $city3JourneyDuration = floor($city3JourneyElapseTime / 60) . "H " . ($city3JourneyElapseTime - ((floor($city3JourneyElapseTime / 60)) * 60)) . "Min";

                            $transitDetails = "";

                            $City3data = array(

                                "0" => array(

                                    "marketingcareer" => "$city3markettingCarrier",

                                    "marketingcareerName" => "$city3markettingCarrierName",

                                    "marketingflight" => "$city3markettingFN",

                                    "operatingcareer" => "$city3operatingCarrier",

                                    "operatingflight" => "$city3operatingFN",

                                    "departure" => "$city3DepartureFrom",

                                    "departureAirport" => "$city3dAirport",

                                    "departureLocation" => "$city3dCity , $city3dCountry",

                                    "departureTime" => "$city3dpTimedate",

                                    "arrival" => "$city3ArrivalTo",

                                    "arrivalTime" => "$city3arrTimedate",

                                    "arrivalAirport" => "$city3aAirport",

                                    "arrivalLocation" => "$city3aCity , $city3aCountry",

                                    "flightduration" => "$city3TravelTime",

                                    "bookingcode" => "$city3BookingCode",

                                    "seat" => "$city3Seat",

                                ),

                                "1" => array(

                                    "marketingcareer" => "$city3markettingCarrier1",

                                    "marketingcareerName" => "$city3markettingCarrierName1",

                                    "marketingflight" => "$city3markettingFN1",

                                    "operatingcareer" => "$city3operatingCarrier1",

                                    "operatingflight" => "$city3operatingFN1",

                                    "departure" => "$city3DepartureFrom1",

                                    "departureAirport" => "$city3dAirport1",

                                    "departureLocation" => "$city3dCity , $city3dCountry1",

                                    "departureTime" => "$city3dpTimedate1",

                                    "arrival" => "$city3ArrivalTo1",

                                    "arrivalTime" => "$city3arrTimedate1",

                                    "arrivalAirport" => "$city3aAirport1",

                                    "arrivalLocation" => "$city3aCity1 , $city3aCountry1",

                                    "flightduration" => "$city3TravelTime1",

                                    "bookingcode" => "$city3BookingCode1",

                                    "seat" => "$city3Seat1",

                                ),

                            );

                        }

                        //City 4

                        if ($sgCount4 == 1) {

                            //Go

                            $city4lf1 = $legDescs[$id4]['schedules'][0]['ref'];

                            $city4legrefs = $city4lf1 - 1;

                            $city4departureTime = substr($scheduleDescs[$city4legrefs]['departure']['time'], 0, 5);

                            $city4dpTime = date("D d M Y", strtotime($City4DepDate . " " . $city4departureTime));

                            $city4dpTimedate = $City4DepDate . "T" . $city4departureTime . ':00';

                            $city4ArrivalTime = substr($scheduleDescs[$city4legrefs]['arrival']['time'], 0, 5);

                            $city4arrivalDate = 0;

                            if (isset($scheduleDescs[$city4legrefs]['arrival']['dateAdjustment'])) {

                                $city4arrivalDate += 1;

                            }

                            if ($city4arrivalDate == 1) {

                                $city4aDate = date('Y-m-d', strtotime("+1 day", strtotime($City4DepDate)));

                            } else {

                                $city4aDate = $City4DepDate;

                            }

                            $city4arrTime = date("D d M Y", strtotime($city4aDate . " " . $city4ArrivalTime));

                            $city4arrTimedate = $city4aDate . "T" . $city4ArrivalTime . ':00';

                            $city4ArrivalTo = $scheduleDescs[$city4legrefs]['arrival']['airport'];

                            $city4DepartureFrom = $scheduleDescs[$city4legrefs]['departure']['airport'];

                            $city4markettingCarrier = $scheduleDescs[$city4legrefs]['carrier']['marketing'];

                            $city4Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city4markettingCarrier' ");

                            $city4Crrow = mysqli_fetch_array($city4Crsql, MYSQLI_ASSOC);

                            if (!empty($city4Crrow)) {

                                $city4markettingCarrierName = $city4Crrow['name'];

                            }

                            // Departure Country

                            $city4Deptsql = mysqli_query($conn, "$Airportsql code='$city4DepartureFrom' ");

                            $city4Deptrow = mysqli_fetch_array($city4Deptsql, MYSQLI_ASSOC);

                            if (!empty($city4Deptrow)) {

                                $city4dAirport = $city4Deptrow['name'];

                                $city4dCity = $city4Deptrow['cityName'];

                                $city4dCountry = $city4Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city4Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city4ArrivalTo' ");

                            $city4Arrrow = mysqli_fetch_array($city4Arrsql, MYSQLI_ASSOC);

                            if (!empty($city4Arrrow)) {

                                $city4aAirport = $city4Arrrow['name'];

                                $city4aCity = $city4Arrrow['cityName'];

                                $city4aCountry = $city4Arrrow['countryCode'];

                            }

                            $city4markettingFN = $scheduleDescs[$city4legrefs]['carrier']['marketingFlightNumber'];

                            $city4operatingCarrier = $scheduleDescs[$city4legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city4legrefs]['carrier']['operatingFlightNumber'])) {

                                $city4operatingFN = $scheduleDescs[$city4legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city4operatingFN = 1;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city4Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city4Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city4BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city4BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city4ElapsedTime = $legDescs[$id4]['elapsedTime'];

                            $city4TravelTime = floor($city4ElapsedTime / 60) . "H " . ($city4ElapsedTime - ((floor($city4ElapsedTime / 60)) * 60)) . "Min";

                            //transit Time

                            $transitDetails = array("0" => array("transit1" => "0"),

                                "1" => array("transit1" => "0"));

                            $City4Data = array("0" => array("marketingcareer" => "$city4markettingCarrier",

                                "marketingcareerName" => "$city4markettingCarrierName",

                                "marketingflight" => "$city4markettingFN",

                                "operatingcareer" => "$city4operatingCarrier",

                                "operatingflight" => "$city4operatingFN",

                                "departure" => "$city4DepartureFrom",

                                "departureAirport" => "$city4dAirport",

                                "departureLocation" => "$city4dCity , $city4dCountry",

                                "departureTime" => "$city4dpTimedate",

                                "arrival" => "$city4ArrivalTo",

                                "arrivalTime" => "$city4arrTimedate",

                                "arrivalAirport" => "$city4aAirport",

                                "arrivalLocation" => "$city4aCity , $city4aCountry",

                                "flightduration" => "$city4TravelTime",

                                "bookingcode" => "$city4BookingCode",

                                "seat" => "$city4Seat"),

                            );

                        } else if ($sgCount4 == 2) {

                            //Go 1

                            $city4lf1 = $legDescs[$id4]['schedules'][0]['ref'];

                            $city4legrefs = $city4lf1 - 1;

                            $city4departureTime = substr($scheduleDescs[$city4legrefs]['departure']['time'], 0, 5);

                            $city4dpTime = date("D d M Y", strtotime($City4DepDate . " " . $city4departureTime));

                            $city4dpTimedate = $City4DepDate . "T" . $city4departureTime . ':00';

                            $city4ArrivalTime = substr($scheduleDescs[$city4legrefs]['arrival']['time'], 0, 5);

                            $city4arrivalDate = 0;

                            if (isset($scheduleDescs[$city4legrefs]['arrival']['dateAdjustment'])) {

                                $city4arrivalDate += 1;

                            }

                            if ($city4arrivalDate == 1) {

                                $city4aDate = date('Y-m-d', strtotime("+1 day", strtotime($City4DepDate)));

                            } else {

                                $city4aDate = $City4DepDate;

                            }

                            $city4arrTime = date("D d M Y", strtotime($city4aDate . " " . $city4ArrivalTime));

                            $city4arrTimedate = $city4aDate . "T" . $city4ArrivalTime . ':00';

                            $city4ArrivalTo = $scheduleDescs[$city4legrefs]['arrival']['airport'];

                            $city4DepartureFrom = $scheduleDescs[$city4legrefs]['departure']['airport'];

                            $city4markettingCarrier = $scheduleDescs[$city4legrefs]['carrier']['marketing'];

                            $city4Crsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city4markettingCarrier' ");

                            $city4Crrow = mysqli_fetch_array($city4Crsql, MYSQLI_ASSOC);

                            if (!empty($city4Crrow)) {

                                $city4markettingCarrierName = $city4Crrow['name'];

                            }

                            // Departure Country

                            $city4Deptsql = mysqli_query($conn, "$Airportsql code='$city4DepartureFrom' ");

                            $city4Deptrow = mysqli_fetch_array($city4Deptsql, MYSQLI_ASSOC);

                            if (!empty($city4Deptrow)) {

                                $city4dAirport = $city4Deptrow['name'];

                                $city4dCity = $city4Deptrow['cityName'];

                                $city4dCountry = $city4Deptrow['countryCode'];

                            }

                            // Arrival Country

                            $city4Arrsql = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city4ArrivalTo' ");

                            $city4Arrrow = mysqli_fetch_array($city4Arrsql, MYSQLI_ASSOC);

                            if (!empty($city4Arrrow)) {

                                $city4aAirport = $city4Arrrow['name'];

                                $city4aCity = $city4Arrrow['cityName'];

                                $city4aCountry = $city4Arrrow['countryCode'];

                            }

                            $city4markettingFN = $scheduleDescs[$city4legrefs]['carrier']['marketingFlightNumber'];

                            $city4operatingCarrier = $scheduleDescs[$city4legrefs]['carrier']['operating'];

                            if (isset($scheduleDescs[$city4legrefs]['carrier']['operatingFlightNumber'])) {

                                $city4operatingFN = $scheduleDescs[$city4legrefs]['carrier']['operatingFlightNumber'];

                            } else {

                                $city4operatingFN = $city4markettingFN;

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city4Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {

                                $city4Seat = "9";

                            }

                            if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {

                                $city4BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city4BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];

                            }

                            $city4ElapsedTime = $scheduleDescs[$city4legrefs]['elapsedTime'];

                            $city4TravelTime = floor($city4ElapsedTime / 60) . "H " . ($city4ElapsedTime - ((floor($city4ElapsedTime / 60)) * 60)) . "Min";

                            //Go 2

                            $city4lf2 = $legDescs[$id4]['schedules'][1]['ref'];

                            $city4legrefs2 = $city4lf2 - 1;

                            $city4DepartureDate1 = 0;

                            if (isset($legDescs[$id4]['schedules'][1]['departureDateAdjustment'])) {

                                $city4DepartureDate1 += 1;

                            }

                            if ($city4DepartureDate1 == 1) {

                                $City4DepDate1 = date('Y-m-d', strtotime("+1 day", strtotime($City4DepDate)));

                            } else {

                                $City4DepDate1 = $City4DepDate;

                            }

                            $city4departureTime1 = substr($scheduleDescs[$city4legrefs2]['departure']['time'], 0, 5);

                            $city4dpTime1 = date("D d M Y", strtotime($City4DepDate1 . " " . $city4departureTime1));

                            $city4dpTimedate1 = $City4DepDate1 . "T" . $city4departureTime1 . ':00';

                            $city4ArrivalTime1 = substr($scheduleDescs[$city4legrefs2]['arrival']['time'], 0, 5);

                            $city4arrivalDate1 = 0;

                            if (isset($scheduleDescs[$city4legrefs2]['arrival']['dateAdjustment'])) {

                                $city4arrivalDate1 += 1;

                            }

                            if ($city4arrivalDate1 == 1) {

                                $city4aDate1 = date('Y-m-d', strtotime("+1 day", strtotime($City4DepDate1)));

                            } else {

                                $city4aDate1 = $City4DepDate1;

                            }

                            $city4arrTime1 = date("D d M Y", strtotime($city4aDate1 . " " . $city4ArrivalTime1));

                            $city4arrTimedate1 = $city4aDate1 . "T" . $city4ArrivalTime1 . ':00';

                            $city4ArrivalTo1 = $scheduleDescs[$city4legrefs2]['arrival']['airport'];

                            $city4DepartureFrom1 = $scheduleDescs[$city4legrefs2]['departure']['airport'];

                            $city4markettingCarrier1 = $scheduleDescs[$city4legrefs2]['carrier']['marketing'];

                            $city4Crsql1 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$city4markettingCarrier1' ");

                            $city4Crrow1 = mysqli_fetch_array($city4Crsql1, MYSQLI_ASSOC);

                            if (!empty($city4Crrow1)) {

                                $city4markettingCarrierName1 = $city4Crrow1['name'];

                            }

                            // Departure Country

                            $city4Deptsql1 = mysqli_query($conn, "$Airportsql code='$city4DepartureFrom1' ");

                            $city4Deptrow1 = mysqli_fetch_array($city4Deptsql1, MYSQLI_ASSOC);

                            if (!empty($city4Deptrow1)) {

                                $city4dAirport1 = $city4Deptrow1['name'];

                                $city4dCity1 = $city4Deptrow1['cityName'];

                                $city4dCountry1 = $city4Deptrow1['countryCode'];

                            }

                            // Arrival Country

                            $city4Arrsql1 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$city4ArrivalTo1' ");

                            $city4Arrrow1 = mysqli_fetch_array($city4Arrsql1, MYSQLI_ASSOC);

                            if (!empty($city4Arrrow)) {

                                $city4aAirport1 = $city4Arrrow1['name'];

                                $city4aCity1 = $city4Arrrow1['cityName'];

                                $city4aCountry1 = $city4Arrrow1['countryCode'];

                            }

                            $city4markettingFN1 = $scheduleDescs[$city4legrefs2]['carrier']['marketingFlightNumber'];

                            $city4operatingCarrier1 = $scheduleDescs[$city4legrefs2]['carrier']['operating'];

                            if (isset($scheduleDescs[$city4legrefs2]['carrier']['operatingFlightNumber'])) {

                                $city4operatingFN1 = $scheduleDescs[$city4legrefs2]['carrier']['operatingFlightNumber'];

                            } else {

                                $city4operatingFN1 = $city4markettingFN1;

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city4Seat1 = $fareComponents[1]['segments'][0]['segment']['seatsAvailable'];

                            } else if (!isset($fareComponents[1]['segments'][0]['segment']['seatsAvailable'])) {

                                $city4Seat1 = "9";

                            }

                            if (isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])) {

                                $city4BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];

                            } else {

                                $city4BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];

                            }

                            $city4ElapsedTime1 = $scheduleDescs[$city4legrefs2]['elapsedTime'];

                            $city4TravelTime1 = floor($city4ElapsedTime1 / 60) . "H " . ($city4ElapsedTime1 - ((floor($city4ElapsedTime1 / 60)) * 60)) . "Min";

                            //Go Transit Time

                            $city4TransitTime = $city4TotalElapesd - ($city4ElapsedTime + $city4ElapsedTime1);

                            $city4TransitDuration = floor($city4TransitTime / 60) . "H " . ($city4TransitTime - ((floor($city4TransitTime / 60)) * 60)) . "Min";

                            $city4JourneyElapseTime = $city4TotalElapesd;

                            $city4JourneyDuration = floor($city4JourneyElapseTime / 60) . "H " . ($city4JourneyElapseTime - ((floor($city4JourneyElapseTime / 60)) * 60)) . "Min";

                            $transitDetails = "";

                            $City4Data = array(

                                "0" => array(

                                    "marketingcareer" => "$city4markettingCarrier",

                                    "marketingcareerName" => "$city4markettingCarrierName",

                                    "marketingflight" => "$city4markettingFN",

                                    "operatingcareer" => "$city4operatingCarrier",

                                    "operatingflight" => "$city4operatingFN",

                                    "departure" => "$city4DepartureFrom",

                                    "departureAirport" => "$city4dAirport",

                                    "departureLocation" => "$city4dCity , $city4dCountry",

                                    "departureTime" => "$city4dpTimedate",

                                    "arrival" => "$city4ArrivalTo",

                                    "arrivalTime" => "$city4arrTimedate",

                                    "arrivalAirport" => "$city4aAirport",

                                    "arrivalLocation" => "$city4aCity , $city4aCountry",

                                    "flightduration" => "$city4TravelTime",

                                    "bookingcode" => "$city4BookingCode",

                                    "seat" => "$city4Seat",

                                ),

                                "1" => array(

                                    "marketingcareer" => "$city4markettingCarrier1",

                                    "marketingcareerName" => "$city4markettingCarrierName1",

                                    "marketingflight" => "$city4markettingFN1",

                                    "operatingcareer" => "$city4operatingCarrier1",

                                    "operatingflight" => "$city4operatingFN1",

                                    "departure" => "$city4DepartureFrom1",

                                    "departureAirport" => "$city4dAirport1",

                                    "departureLocation" => "$city4dCity , $city4dCountry1",

                                    "departureTime" => "$city4dpTimedate1",

                                    "arrival" => "$city4ArrivalTo1",

                                    "arrivalTime" => "$city4arrTimedate1",

                                    "arrivalAirport" => "$city4aAirport1",

                                    "arrivalLocation" => "$city4aCity1 , $city4aCountry1",

                                    "flightduration" => "$city4TravelTime1",

                                    "bookingcode" => "$city4BookingCode1",

                                    "seat" => "$city4Seat1",

                                ),

                            );

                        }

                        $basic = array("system" => "Sabre",

                            "city" => "4",

                            "career" => "$vCarCode",

                            "careerName" => "$CarrieerName",

                            "lastTicketTime" => "$timelimit",

                            "BasePrice" => $totalBaseAmount,

                            "Taxes" => $totalTaxAmount,

                            "price" => "$agentPrice",

                            "clientPrice" => "$totalPrice",

                            "comission" => "$Commission",

                            "pricebreakdown" => $PriceBreakDown,

                            "transit" => $transitDetails,

                            "bags" => $Baggage,

                            "refundable" => $nonRefundable,

                            "segments" => array("0" => $City1Data, "1" => $City2Data, "2" => $City3Data, "3" => $City4Data),

                        );

                    }

                }

                array_push($All, $basic);

            }

        }

        echo json_encode($All, JSON_PRETTY_PRINT);

    } else {

        $response['satus'] = "error";
        $response['message'] = "Agnet Not Found";

        echo json_encode($response);

    }

}
