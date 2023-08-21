<?php

include "../../config.php";
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$All = array();
$FlightType;

$control = mysqli_query($conn, "SELECT * FROM control where id=1");
$controlrow = mysqli_fetch_array($control, MYSQLI_ASSOC);

if (!empty($controlrow)) {
    $Sabre = $controlrow['sabre'];
    $Galileo = 1;
    $FlyHub = $controlrow['flyhub'];
    $Amadeus = $controlrow['amadeus'];
    $gdsPrice = $controlrow['gdsPrice'];
    $farePrice = $controlrow['farePrice'];
}

$Airportsql = "SELECT name, cityName,countryCode FROM airports WHERE";

if (array_key_exists('tripType', $_GET)) {
    $Way = $_GET['tripType'];
    
    if ($Way == "oneway") {
        if (array_key_exists("journeyfrom", $_GET) && array_key_exists("journeyto", $_GET) && array_key_exists("departuredate", $_GET)) {
            $From = $_GET['journeyfrom'];
            $To = $_GET['journeyto'];
            $Date = $_GET['departuredate'];
            $ActualDate = $Date . "T00:00:00";

            // echo ("Hello");
            // Trip Type
            $fromsql = mysqli_query($conn, "SELECT countryCode FROM airports WHERE code='$From' ");
            $fromrow = mysqli_fetch_array($fromsql, MYSQLI_ASSOC);

            if (!empty($fromrow)) {

                $fromCountry = $fromrow['countryCode'];

            }

            $tosql = mysqli_query($conn, "SELECT countryCode FROM airports WHERE code='$To' ");
            $torow = mysqli_fetch_array($tosql, MYSQLI_ASSOC);

            if (!empty($torow)) {

                $toCountry = $torow['countryCode'];

            }

            if ($fromCountry == $toCountry) {
                $TripType = "Inbound";
            } else {
                $TripType = "Outbound";
            }

            if ((array_key_exists("adult", $_GET)) && (array_key_exists("child", $_GET) && array_key_exists("infant", $_GET))) {

                $adult = $_GET['adult'];
                $child = $_GET['child'];
                $infants = $_GET['infant'];

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

                $jsonreq = '{
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
                        "OriginDestinationInformation": [{
                                "RPH": "1",
                                "DepartureDateTime": "' . $ActualDate . '",
                                "OriginLocation": {
                                    "LocationCode": "' . $From . '"
                                },
                                "DestinationLocation": {
                                    "LocationCode": "' . $To . '"
                                }
                            }
                        ],
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

                //echo $jsonreq;

                if ($Sabre == 1) // Sabre Start {
                try {

                    $client_id = base64_encode("V1:593072:14KK:AA");
                    //$client_secret = base64_encode("280ff537"); //cert
                    $client_secret = base64_encode("f270395"); //prod

                    $token = base64_encode($client_id . ":" . $client_secret);

                    $data = 'grant_type=client_credentials';

                    $headers = array(
                        'Authorization: Basic ' . $token,
                        'Accept: /',
                        'Content-Type: application/x-www-form-urlencoded',
                    );

                    $ch = curl_init();
                    //curl_setopt($ch,CURLOPT_URL,"https://api-crt.cert.havail.sabre.com/v2/auth/token");
                    curl_setopt($ch, CURLOPT_URL, "https://api.platform.sabre.com/v2/auth/token");
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $res = curl_exec($ch);
                    curl_close($ch);
                    $resf = json_decode($res, 1);
                    $access_token = $resf['access_token'];

                    //print_r($resf);

                } catch (Exception $e) {

                }
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
                    CURLOPT_POSTFIELDS => $jsonreq,
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Conversation-ID: 2021.01.DevStudio',
                        'Authorization: Bearer ' . $access_token,
                    ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);
                $result = json_decode($response, true);

                if (isset($result['groupedItineraryResponse']['itineraryGroups'])) {
                    $SabreItenary = $result['groupedItineraryResponse']['itineraryGroups'];
                    //print_r($result);

                    if (array_key_exists('groupedItineraryResponse', $result)) {
                        if ($result['groupedItineraryResponse']['statistics']['itineraryCount'] > 0) {
                            if ($To == 'DXB' || $From == 'DXB') {
                                if (isset($SabreItenary[0]['itineraries']) && isset($SabreItenary[1]['itineraries'])) {
                                    if (count($SabreItenary[0]['itineraries']) > count($SabreItenary[1]['itineraries'])) {
                                        $flightListSabre = $SabreItenary[0]['itineraries'];
                                    } else {
                                        $flightListSabre = $SabreItenary[1]['itineraries'];
                                    }
                                } else {
                                    $flightListSabre = $SabreItenary[0]['itineraries'];
                                }

                            } else {
                                $flightListSabre = $SabreItenary[0]['itineraries'];
                                //echo count($flightList);
                            }

                            $scheduleDescs = $result['groupedItineraryResponse']['scheduleDescs'];
                            $legDescs = $result['groupedItineraryResponse']['legDescs'];

                            $Bag = $result['groupedItineraryResponse']['baggageAllowanceDescs'];

                        }
                    }
                }
            }
        }

        if (isset($flightListSabre)) {
            $i = 0;
            $s = 0;
            foreach ($flightListSabre as $var) {

                $i++;
                $idd = $var['id'];
                $pricingSource = $var['pricingSource'];
                $vCarCode = $var['pricingInformation'][0]['fare']['validatingCarrierCode'];

                if (isset($var['pricingInformation'][0]['fare']['lastTicketDate'])
                    && isset($var['pricingInformation'][0]['fare']['lastTicketTime'])) {

                    $lastTicketDate = $var['pricingInformation'][0]['fare']['lastTicketDate'];
                    $lastTicketTime = $var['pricingInformation'][0]['fare']['lastTicketTime'];
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

                $passengerInfo = $var['pricingInformation'][0]['fare']['passengerInfoList'][0]['passengerInfo'];
                $fareComponents = $passengerInfo['fareComponents'];

                $Class = $fareComponents[0]['segments'][0]['segment']['cabinCode'];

                $BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
                $Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                //$lessFare = floor((($baseFareAmount * 0.93) + $totalTaxAmount) + ($totalFare* 0.003));
                //$Commission = $totalFare - $Exact;

                $PriceInfo = $var['pricingInformation'][0]['fare']['passengerInfoList'];

                if ($fareRate == 7) {
                    if ($From != "DAC" && $vCarCode == "SV") {
                        $baseFareAmount = ceil(($var['pricingInformation'][0]['fare']['totalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                        $totalTaxAmount = ceil(($var['pricingInformation'][0]['fare']['totalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
                        $totalFare = ceil(($var['pricingInformation'][0]['fare']['totalFare']['totalPrice'] / $gdsPrice) * $farePrice);

                        $AgentPrice = floor((($baseFareAmount * 0.93) + $totalTaxAmount) + ($totalFare * 0.003));
                        $Commission = $totalFare - $AgentPrice;

                        //Price Break Down
                        if ($adult > 0 && $child > 0 && $infants > 0) {

                            $adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $childBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $childTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $infantBasePrice = ceil(($PriceInfo[2]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $infantTaxAmount = ceil(($PriceInfo[2]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0")
                                ,
                                "1" => array("BaseFare" => "$childBasePrice",
                                    "Tax" => "$childTaxAmount",
                                    "PaxCount" => $child,
                                    "PaxType" => "CNN",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),
                                "2" => array("BaseFare" => "$infantBasePrice",
                                    "Tax" => "$infantTaxAmount",
                                    "PaxCount" => $infants,
                                    "PaxType" => "INF",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),

                            );

                        } else if ($adult > 0 && $child > 0) {
                            $adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $childBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $childTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                        } else if ($adult > 0 && $infants > 0) {
                            $adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $infantBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $infantTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                        } else if ($adult > 0) {

                            $adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0"),

                            );
                        }

                    } else if ($From != "DAC" && $vCarCode == "SQ") {

                        $baseFareAmount = $var['pricingInformation'][0]['fare']['totalFare']['equivalentAmount'];
                        $totalTaxAmount = $var['pricingInformation'][0]['fare']['totalFare']['totalTaxAmount'];
                        $totalFare = $var['pricingInformation'][0]['fare']['totalFare']['totalPrice'];

                        $AgentPrice = $totalFare;
                        $Commission = 0;

                        if ($adult > 0 && $child > 0 && $infants > 0) {

                            $adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $childBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $childTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $infantBasePrice = ceil(($PriceInfo[2]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $infantTaxAmount = ceil(($PriceInfo[2]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0")
                                ,
                                "1" => array("BaseFare" => "$childBasePrice",
                                    "Tax" => "$childTaxAmount",
                                    "PaxCount" => $child,
                                    "PaxType" => "CNN",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),
                                "2" => array("BaseFare" => "$infantBasePrice",
                                    "Tax" => "$infantTaxAmount",
                                    "PaxCount" => $infants,
                                    "PaxType" => "INF",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),

                            );

                        } else if ($adult > 0 && $child > 0) {
                            $adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $childBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $childTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0")
                                ,
                                "1" => array("BaseFare" => "$childBasePrice",
                                    "Tax" => "$childTaxAmount",
                                    "PaxCount" => $child,
                                    "PaxType" => "CNN",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),

                            );

                        } else if ($adult > 0 && $infants > 0) {
                            $adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $infantBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $infantTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0")
                                ,
                                "1" => array("BaseFare" => "$infantBasePrice",
                                    "Tax" => "$infantTaxAmount",
                                    "PaxCount" => $infants,
                                    "PaxType" => "INF",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),

                            );

                        } else if ($adult > 0) {

                            $adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0"),

                            );
                        }

                    } else if ($From != "DAC" && $vCarCode == "EY") {

                        $baseFareAmount = $var['pricingInformation'][0]['fare']['totalFare']['equivalentAmount'];
                        $totalTaxAmount = $var['pricingInformation'][0]['fare']['totalFare']['totalTaxAmount'];
                        $totalFare = $var['pricingInformation'][0]['fare']['totalFare']['totalPrice'];

                        $AgentPrice = $totalFare;
                        $Commission = 0;

                        if ($adult > 0 && $child > 0 && $infants > 0) {

                            $adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $childBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $childTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $infantBasePrice = ceil(($PriceInfo[2]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $infantTaxAmount = ceil(($PriceInfo[2]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0")
                                ,
                                "1" => array("BaseFare" => "$childBasePrice",
                                    "Tax" => "$childTaxAmount",
                                    "PaxCount" => $child,
                                    "PaxType" => "CNN",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),
                                "2" => array("BaseFare" => "$infantBasePrice",
                                    "Tax" => "$infantTaxAmount",
                                    "PaxCount" => $infants,
                                    "PaxType" => "INF",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),

                            );

                        } else if ($adult > 0 && $child > 0) {
                            $adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $childBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $childTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0")
                                ,
                                "1" => array("BaseFare" => "$childBasePrice",
                                    "Tax" => "$childTaxAmount",
                                    "PaxCount" => $child,
                                    "PaxType" => "CNN",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),

                            );

                        } else if ($adult > 0 && $infants > 0) {
                            $adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $infantBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $infantTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0")
                                ,
                                "1" => array("BaseFare" => "$infantBasePrice",
                                    "Tax" => "$infantTaxAmount",
                                    "PaxCount" => $infants,
                                    "PaxType" => "INF",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),

                            );

                        } else if ($adult > 0) {

                            $adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                            $adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0"),

                            );
                        }

                    } else {
                        $baseFareAmount = $var['pricingInformation'][0]['fare']['totalFare']['equivalentAmount'];
                        $totalTaxAmount = $var['pricingInformation'][0]['fare']['totalFare']['totalTaxAmount'];
                        $totalFare = $var['pricingInformation'][0]['fare']['totalFare']['totalPrice'];

                        $AgentPrice = floor((($baseFareAmount * 0.93) + $totalTaxAmount) + ($totalFare * 0.003));
                        $Commission = $totalFare - $AgentPrice;

                        if ($adult > 0 && $child > 0 && $infants > 0) {

                            $adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                            $adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                            $childBasePrice = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                            $childTaxAmount = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                            $infantBasePrice = $PriceInfo[2]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                            $infantTaxAmount = $PriceInfo[2]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0")
                                ,
                                "1" => array("BaseFare" => "$childBasePrice",
                                    "Tax" => "$childTaxAmount",
                                    "PaxCount" => $child,
                                    "PaxType" => "CNN",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),
                                "2" => array("BaseFare" => "$infantBasePrice",
                                    "Tax" => "$infantTaxAmount",
                                    "PaxCount" => $infants,
                                    "PaxType" => "INF",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),

                            );

                        } else if ($adult > 0 && $child > 0) {
                            $adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                            $adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                            $childBasePrice = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                            $childTaxAmount = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0")
                                ,
                                "1" => array("BaseFare" => "$childBasePrice",
                                    "Tax" => "$childTaxAmount",
                                    "PaxCount" => $child,
                                    "PaxType" => "CNN",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),

                            );

                        } else if ($adult > 0 && $infants > 0) {
                            $adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                            $adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                            $infantBasePrice = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                            $infantTaxAmount = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0")
                                ,
                                "1" => array("BaseFare" => "$infantBasePrice",
                                    "Tax" => "$infantTaxAmount",
                                    "PaxCount" => $infants,
                                    "PaxType" => "INF",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),

                            );

                        } else if ($adult > 0) {

                            $adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                            $adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0"),

                            );
                        }
                    }
                } else if ($fareRate == 3) {
                    if ($vCarCode == "FZ" || $vCarCode == "EY") {
                        $baseFareAmount = $var['pricingInformation'][0]['fare']['totalFare']['equivalentAmount'];
                        $totalTaxAmount = $var['pricingInformation'][0]['fare']['totalFare']['totalTaxAmount'];
                        $totalFare = $var['pricingInformation'][0]['fare']['totalFare']['totalPrice'] + 2000;

                        $AgentPrice = $totalFare;
                        $Commission = $totalFare - $AgentPrice;

                        if ($adult > 0 && $child > 0 && $infants > 0) {

                            $adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                            $adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                            $childBasePrice = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                            $childTaxAmount = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                            $infantBasePrice = $PriceInfo[2]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                            $infantTaxAmount = $PriceInfo[2]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0")
                                ,
                                "1" => array("BaseFare" => "$childBasePrice",
                                    "Tax" => "$childTaxAmount",
                                    "PaxCount" => $child,
                                    "PaxType" => "CNN",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),
                                "2" => array("BaseFare" => "$infantBasePrice",
                                    "Tax" => "$infantTaxAmount",
                                    "PaxCount" => $infants,
                                    "PaxType" => "INF",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),

                            );

                        } else if ($adult > 0 && $child > 0) {
                            $adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                            $adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                            $childBasePrice = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                            $childTaxAmount = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0")
                                ,
                                "1" => array("BaseFare" => "$childBasePrice",
                                    "Tax" => "$childTaxAmount",
                                    "PaxCount" => $child,
                                    "PaxType" => "CNN",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),

                            );

                        } else if ($adult > 0 && $infants > 0) {
                            $adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                            $adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                            $infantBasePrice = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                            $infantTaxAmount = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0")
                                ,
                                "1" => array("BaseFare" => "$infantBasePrice",
                                    "Tax" => "$infantTaxAmount",
                                    "PaxCount" => $infants,
                                    "PaxType" => "INF",
                                    "Discount" => "0",
                                    "OtherCharges" => "0",
                                    "ServiceFee" => "0"),

                            );

                        } else if ($adult > 0) {

                            $adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                            $adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                                "Tax" => "$adultTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "ADT",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0"),

                            );
                        }

                    }
                } else {
                    $baseFareAmount = ceil(($var['pricingInformation'][0]['fare']['totalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                    $totalTaxAmount = ceil(($var['pricingInformation'][0]['fare']['totalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);
                    $totalFare = ceil(($var['pricingInformation'][0]['fare']['totalFare']['totalPrice'] / $gdsPrice) * $farePrice);

                    $AgentPrice = floor((($baseFareAmount * 0.93) + $totalTaxAmount) + ($totalFare * 0.003));
                    $Commission = $totalFare - $AgentPrice;

                    //Price Break Down
                    if ($adult > 0 && $child > 0 && $infants > 0) {

                        $adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                        $adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                        $childBasePrice = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                        $childTaxAmount = ceil(($PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                        $infantBasePrice = ceil(($PriceInfo[2]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                        $infantTaxAmount = ceil(($PriceInfo[2]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                        $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                            "Tax" => "$adultTaxAmount",
                            "PaxCount" => $adult,
                            "PaxType" => "ADT",
                            "Discount" => "0",
                            "OtherCharges" => "0",
                            "ServiceFee" => "0")
                            ,
                            "1" => array("BaseFare" => "$childBasePrice",
                                "Tax" => "$childTaxAmount",
                                "PaxCount" => $child,
                                "PaxType" => "CNN",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0"),
                            "2" => array("BaseFare" => "$infantBasePrice",
                                "Tax" => "$infantTaxAmount",
                                "PaxCount" => $infants,
                                "PaxType" => "INF",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0"),

                        );

                    } else if ($adult > 0 && $child > 0) {
                        $adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                        $adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                        $childBasePrice = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                        $childTaxAmount = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                        $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                            "Tax" => "$adultTaxAmount",
                            "PaxCount" => $adult,
                            "PaxType" => "ADT",
                            "Discount" => "0",
                            "OtherCharges" => "0",
                            "ServiceFee" => "0")
                            ,
                            "1" => array("BaseFare" => "$childBasePrice",
                                "Tax" => "$childTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "CNN",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0"),

                        );

                    } else if ($adult > 0 && $infants > 0) {
                        $adultBasePrice = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                        $adultTaxAmount = $PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                        $infantBasePrice = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['equivalentAmount'];
                        $infantTaxAmount = $PriceInfo[1]['passengerInfo']['passengerTotalFare']['totalTaxAmount'];

                        $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                            "Tax" => "$adultTaxAmount",
                            "PaxCount" => $adult,
                            "PaxType" => "ADT",
                            "Discount" => "0",
                            "OtherCharges" => "0",
                            "ServiceFee" => "0")
                            ,
                            "1" => array("BaseFare" => "$infantBasePrice",
                                "Tax" => "$infantTaxAmount",
                                "PaxCount" => $adult,
                                "PaxType" => "INF",
                                "Discount" => "0",
                                "OtherCharges" => "0",
                                "ServiceFee" => "0"),

                        );
                    } else if ($adult > 0) {

                        $adultBasePrice = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['equivalentAmount'] / $gdsPrice) * $farePrice);
                        $adultTaxAmount = ceil(($PriceInfo[0]['passengerInfo']['passengerTotalFare']['totalTaxAmount'] / $gdsPrice) * $farePrice);

                        $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                            "Tax" => "$adultTaxAmount",
                            "PaxCount" => $adult,
                            "PaxType" => "ADT",
                            "Discount" => "0",
                            "OtherCharges" => "0",
                            "ServiceFee" => "0"),

                        );
                    }
                }

                $Segment = $fareComponents[0]['segments'];

                $BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
                $Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];

                if (isset($passengerInfo['baggageInformation'][0]['allowance']['ref'])) {
                    $BegRef = $passengerInfo['baggageInformation'][0]['allowance']['ref'];
                    $BegId = $BegRef - 1;
                    if (isset($Bag[$BegId]['weight'])) {
                        $Bags = $Bag[$BegId]['weight'];
                    } else if (isset($Bag[$BegId]['pieceCount'])) {
                        $Bags = $Bag[$BegId]['pieceCount'];
                    } else {
                        $Bags = "0";
                    }
                } else {
                    $Bags = "0";
                }

                if ($Class == 'Y') {
                    $CabinClass = "Economy";
                }

                $nonRefundable = $passengerInfo['nonRefundable'];
                if ($nonRefundable == 1) {
                    $nonRef = "Nonrefundable";

                } else {
                    $nonRef = "Refundable";
                }

                $ref = $var['legs'][0]['ref'];
                $id = $ref - 1;

                $sgCount = count($legDescs[$id]['schedules']);

                $ElapedTime = $legDescs[$id]['elapsedTime'];
                $JourneyDuration = floor($ElapedTime / 60) . "H " . ($ElapedTime - ((floor($ElapedTime / 60)) * 60)) . "Min";

                //Agent MarkUP
                $agentMarksql = mysqli_query($conn, "SELECT alldMarkup,alliMarkup, alldMarkuptype,alliMarkuptype FROM agent WHERE platform='B2C' ");
                $agentmarkrow = mysqli_fetch_array($agentMarksql, MYSQLI_ASSOC);

                if (!empty($agentmarkrow)) {
                    $imarkuptype = $agentmarkrow['alliMarkuptype'];
                    $dmarkuptype = $agentmarkrow['alldMarkuptype'];
                    if ($imarkuptype == 'amount' || $dmarkuptype == 'amount') {
                        if ($TripType == 'Inbound') {
                            $markup = $agentmarkrow['alldMarkup'];
                        } else {
                            $markup = $agentmarkrow['alliMarkup'];
                        }
                        $WLAgentPrice = $AgentPrice + (float)$markup;
                        // echo $WLAgentPrice;

                    } else if ($imarkuptype == 'percentage' || $dmarkuptype == 'percentage') {
                        if ($TripType == 'Inbound') {
                            $markup = $agentmarkrow['alldMarkup'];
                        } else {
                            $markup = $agentmarkrow['alliMarkup'];
                        }
                        $WLAgentPrice = ceil($AgentPrice + ($AgentPrice * ($markup / 100)));

                    } else {
                        $WLAgentPrice = $AgentPrice;
                    }
                } else {
                    $WLAgentPrice = $AgentPrice;
                }

                $uId = sha1(md5(time()) . '' . rand());

                if ($sgCount == 1) {

                    $lf = $legDescs[$id]['schedules'][0]['ref'];
                    $legref = $lf - 1;

                    $ElapsedTime = $scheduleDescs[$legref]['elapsedTime'];
                    $TravelTime = floor($ElapsedTime / 60) . "H " . ($ElapsedTime - ((floor($ElapsedTime / 60)) * 60)) . "Min";

                    $ArrivalTime1 = $scheduleDescs[$legref]['arrival']['time'];
                    $arrAt2 = substr($ArrivalTime1, 0, 5);

                    $arrivalDate = 0;
                    if (isset($scheduleDescs[$legref]['arrival']['dateAdjustment'])) {
                        $arrivalDate += 1;
                    }

                    if ($arrivalDate == 1) {
                        $aDate = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $aDate = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $departureTime1 = $scheduleDescs[$legref]['departure']['time'];
                    $depAt1 = substr($departureTime1, 0, 5);

                    $fromTime1 = str_split($departureTime1, 8);
                    $dpTime1 = date("D d M Y", strtotime($Date . " " . $fromTime1[0]));

                    $toTime1 = str_split($ArrivalTime1, 8);
                    $arrTime2 = date("D d M Y", strtotime($aDate . " " . $toTime1[0]));

                    $ArrivalTo = $scheduleDescs[$legref]['arrival']['airport'];
                    $DepartureFrom = $scheduleDescs[$legref]['departure']['airport'];

                    $ArrivalTime = $scheduleDescs[$legref]['arrival']['time'];
                    $departureTime = $scheduleDescs[$legref]['departure']['time'];
                    $markettingCarrier = $scheduleDescs[$legref]['carrier']['marketing'];

                    $sql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                    $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

                    if (!empty($row)) {
                        $markettingCarrierName = $row['name'];
                    }

                    // Departure Country
                    $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                    $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                    if (!empty($row1)) {
                        $dAirport = $row1['name'];
                        $dCity = $row1['cityName'];
                        $dCountry = $row1['countryCode'];
                    }

                    // Arrival Country
                    $sql2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                    $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                    if (!empty($row2)) {
                        $aAirport = $row2['name'];
                        $aCity = $row2['cityName'];
                        $aCountry = $row2['countryCode'];

                    }

                    $markettingFN = $scheduleDescs[$legref]['carrier']['marketingFlightNumber'];
                    $operatingCarrier = $scheduleDescs[$legref]['carrier']['operating'];
                    $operatingFN = $scheduleDescs[$legref]['carrier']['operatingFlightNumber'];

                    $opsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$operatingCarrier' ");
                    $oprow = mysqli_fetch_array($opsql, MYSQLI_ASSOC);

                    if (!empty($oprow)) {
                        $operatingCarrierName = $oprow['name'];
                    }

                    if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {
                        $Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
                    } else if (!isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {
                        $Seat = "Available Seat Invisible";
                    }

                    $arrivalDate = 0;
                    if (isset($scheduleDescs[$legref]['arrival']['dateAdjustment'])) {
                        $arrivalDate += 1;
                    }

                    if ($arrivalDate == 1) {
                        $aDate = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $aDate = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $fromTime = str_split($departureTime, 8);
                    $dpTime = $Date . "T" . $fromTime[0];

                    $toTime = str_split($ArrivalTime, 8);
                    $arrTime = $aDate . "T" . $toTime[0];

                    //Array Push

                    $stopOverDetails = array();

                    if (isset($scheduleDescs[$legref]['hiddenStops'])) {
                        $hiddenStopOver = $scheduleDescs[$legref]['hiddenStops'];

                        foreach ($hiddenStopOver as $hidestop) {
                            $airpot = $hidestop['airport'];
                            $duration = $hidestop['elapsedLayoverTime'];
                            $time = date('H:i', mktime(0, $duration));
                            $stopoverdetails = array("airport" => $airpot,
                                "time" => $time);
                            array_push($stopOverDetails, $stopoverdetails);
                        }
                    }

                    $segment = array("0" => array("marketingcareer" => "$markettingCarrier",
                        "marketingcareerName" => "$markettingCarrierName",
                        "marketingflight" => "$markettingFN",
                        "operatingcareer" => "$operatingCarrier",
                        "operatingflight" => "$operatingFN",
                        "operatingCarrierName" => "$operatingCarrierName",
                        "departure" => "$DepartureFrom",
                        "departureAirport" => "$dAirport ",
                        "departureLocation" => "$dCity , $dCountry",
                        "departureTime" => "$dpTime",
                        "arrival" => "$ArrivalTo",
                        "arrivalTime" => "$arrTime",
                        "arrivalAirport" => "$aAirport",
                        "arrivalLocation" => "$aCity , $aCountry",
                        "flightduration" => "$TravelTime",
                        "bookingcode" => "$BookingCode",
                        "seat" => "$Seat"),

                    );

                    $transitDetails = array("transit1" => "0");

                    $basic = array("system" => "Sabre",
                        "segment" => "1",
                        "uId" => $uId,
                        "triptype" => $TripType,
                        "career" => "$vCarCode",
                        "careerName" => "$CarrieerName",
                        "lastTicketTime" => "$timelimit",
                        "BasePrice" => "$baseFareAmount",
                        "Taxes" => "$totalTaxAmount",
                        "netfare" => "$AgentPrice",
                        "agentprice" => "$WLAgentPrice",
                        //"subagentprice" => "$subAgentPrice",
                        "customerPrice" => $totalFare,
                        "pricebreakdown" => $PriceBreakDown,
                        "departure" => "$From",
                        "departureTime" => "$depAt1",
                        "departureDate" => "$dpTime1",
                        "arrival" => "$To",
                        "arrivalTime" => "$arrAt2",
                        "arrivalDate" => "$arrTime2",
                        "flightduration" => "$JourneyDuration",
                        "bags" => "$Bags",
                        "seat" => "$Seat",
                        "class" => "$CabinClass",
                        "refundable" => "$nonRef",
                        "segments" => $segment,
                        "transit" => $transitDetails,
                        "stopover" => $stopOverDetails,

                    );

                    array_push($All, $basic);

                }if ($sgCount == 2) {

                    $lf = $legDescs[$id]['schedules'][0]['ref'];
                    $legref = $lf - 1;

                    $ArrivalTime1 = $scheduleDescs[$legref]['arrival']['time'];
                    $arrAt2 = substr($ArrivalTime1, 0, 5);

                    $arrivalDate1 = 0;
                    if (isset($scheduleDescs[$legref]['arrival']['dateAdjustment'])) {
                        $arrivalDate1 += 1;
                    }

                    if ($arrivalDate1 == 1) {
                        $aDate = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $aDate = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $ElapsedTime1 = $scheduleDescs[$legref]['elapsedTime'];
                    $TravelTime1 = floor($ElapsedTime1 / 60) . "H " . ($ElapsedTime1 - ((floor($ElapsedTime1 / 60)) * 60)) . "Min";

                    $ArrivalTime1 = $scheduleDescs[$legref]['arrival']['time'];
                    $arrAt1 = substr($ArrivalTime1, 0, 5);

                    $departureTime1 = $scheduleDescs[$legref]['departure']['time'];
                    $depAt1 = substr($departureTime1, 0, 5);

                    $fromTime1 = str_split($departureTime1, 8);
                    $dpTime1 = date("D d M Y", strtotime($Date . " " . $fromTime1[0]));

                    $toTime1 = str_split($ArrivalTime1, 8);
                    $arrTime1 = date("D d M Y", strtotime($aDate . " " . $toTime1[0]));

                    $ArrivalTo = $scheduleDescs[$legref]['arrival']['airport'];
                    $DepartureFrom = $scheduleDescs[$legref]['departure']['airport'];

                    $ArrivalTime = $scheduleDescs[$legref]['arrival']['time'];
                    $departureTime = $scheduleDescs[$legref]['departure']['time'];

                    // Departure Country
                    $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                    $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                    if (!empty($row1)) {
                        $dAirport = $row1['name'];
                        $dCity = $row1['cityName'];
                        $dCountry = $row1['countryCode'];

                    }

                    // Departure Country
                    $sql2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                    $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                    if (!empty($row2)) {
                        $aAirport = $row2['name'];
                        $aCity = $row2['cityName'];
                        $aCountry = $row2['countryCode'];

                    }

                    $markettingCarrier = $scheduleDescs[$legref]['carrier']['marketing'];
                    $markettingFN = $scheduleDescs[$legref]['carrier']['marketingFlightNumber'];
                    $operatingCarrier = $scheduleDescs[$legref]['carrier']['operating'];
                    $operatingFN = $scheduleDescs[$legref]['carrier']['operatingFlightNumber'];

                    $carriersql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                    $carrierrow = mysqli_fetch_array($carriersql, MYSQLI_ASSOC);

                    if (!empty($carrierrow)) {
                        $markettingCarrierName = $carrierrow['name'];
                    }

                    $opsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$operatingCarrier' ");
                    $oprow = mysqli_fetch_array($opsql, MYSQLI_ASSOC);

                    if (!empty($oprow)) {
                        $operatingCarrierName = $oprow['name'];
                    }

                    if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {
                        $Seat1 = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
                    }

                    if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {
                        $BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
                    } else {
                        $BookingCode = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
                    }

                    $arrivalDate = 0;
                    if (isset($scheduleDescs[$legref]['arrival']['dateAdjustment'])) {
                        $arrivalDate += 1;
                    }

                    if ($arrivalDate == 1) {
                        $aDate = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $aDate = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $fromTime = str_split($departureTime, 8);
                    $dpTime = $Date . "T" . $fromTime[0];

                    $toTime = str_split($ArrivalTime, 8);
                    $arrTime = $aDate . "T" . $toTime[0];

                    //2nd Leg

                    $lf2 = $legDescs[$id]['schedules'][1]['ref'];
                    $legref1 = $lf2 - 1;

                    $dateAdjust2 = 0;
                    if (isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])) {
                        $dateAdjust2 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
                    }

                    //Store Data
                    if ($dateAdjust2 == 1) {
                        $NewDate2 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $NewDate2 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $ElapsedTime2 = $scheduleDescs[$legref1]['elapsedTime'];
                    $TravelTime2 = floor($ElapsedTime2 / 60) . "H " . ($ElapsedTime2 - ((floor($ElapsedTime2 / 60)) * 60)) . "Min";

                    $ArrivalTime2 = $scheduleDescs[$legref1]['arrival']['time'];
                    $arrAt2 = substr($ArrivalTime2, 0, 5);

                    $departureTime2 = $scheduleDescs[$legref1]['departure']['time'];
                    $depAt2 = substr($departureTime2, 0, 5);

                    $fromTime2 = str_split($departureTime2, 8);
                    $dpTime2 = date("D d M Y", strtotime($NewDate2 . " " . $fromTime2[0]));

                    $toTime2 = str_split($ArrivalTime2, 8);
                    $arrTime2 = date("D d M Y", strtotime($NewDate2 . " " . $toTime2[0]));

                    $TransitInt = $ElapedTime - ($ElapsedTime1 + $ElapsedTime2);
                    $Transit = floor($TransitInt / 60) . "H " . ($TransitInt - ((floor($TransitInt / 60)) * 60)) . "Min";

                    $dateAdjust2 = 0;
                    if (isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])) {
                        $dateAdjust2 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
                    }

                    //$descid = $scheduleDescs[$legref1]['id'];
                    $ArrivalTo1 = $scheduleDescs[$legref1]['arrival']['airport']; //echo $ArrivalTo1;
                    $DepartureFrom1 = $scheduleDescs[$legref1]['departure']['airport'];

                    $ArrivalTime1 = $scheduleDescs[$legref1]['arrival']['time'];
                    $departureTime1 = $scheduleDescs[$legref1]['departure']['time'];
                    $markettingCarrier1 = $scheduleDescs[$legref1]['carrier']['marketing'];

                    $carriersql1 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
                    $carrierrow1 = mysqli_fetch_array($carriersql1, MYSQLI_ASSOC);

                    if (!empty($carrierrow1)) {
                        $markettingCarrierName1 = $carrierrow1['name'];
                    }

                    // Departure Country
                    $sql3 = mysqli_query($conn, "$Airportsql code='$DepartureFrom1' ");
                    $row3 = mysqli_fetch_array($sql3, MYSQLI_ASSOC);

                    if (!empty($row3)) {
                        $dAirport1 = $row3['name'];
                        $dCity1 = $row3['cityName'];
                        $dCountry1 = $row3['countryCode'];
                    }

                    // Departure Country
                    $sql4 = mysqli_query($conn, "$Airportsql code='$ArrivalTo1' ");
                    $row4 = mysqli_fetch_array($sql4, MYSQLI_ASSOC);

                    if (!empty($row4)) {
                        $aAirport1 = $row4['name'];
                        $aCity1 = $row4['cityName'];
                        $aCountry1 = $row4['countryCode'];

                    }

                    $markettingFN1 = $scheduleDescs[$legref1]['carrier']['marketingFlightNumber'];
                    $operatingCarrier1 = $scheduleDescs[$legref1]['carrier']['operating'];
                    $operatingFN1 = $scheduleDescs[$legref1]['carrier']['operatingFlightNumber'];

                    $opsql1 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$operatingCarrier1' ");
                    $oprow1 = mysqli_fetch_array($opsql1, MYSQLI_ASSOC);

                    if (!empty($oprow)) {
                        $operatingCarrierName1 = $oprow1['name'];
                    }

                    if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {
                        $Seat2 = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
                    }

                    if (isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])) {
                        $BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
                    } else {
                        $BookingCode1 = $fareComponents[0]['segments'][1]['segment']['bookingCode'];
                    }

                    //Store Data
                    if ($dateAdjust2 == 1) {
                        $dDate2 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $dDate2 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $arrivalDate2 = 0;
                    if (isset($scheduleDescs[$legref1]['arrival']['dateAdjustment'])) {
                        $arrivalDate2 += 1;
                    }

                    if ($arrivalDate2 == 1) {
                        $aDate2 = date('Y-m-d', strtotime("+1 day", strtotime($dDate2)));
                    } else {
                        $aDate2 = date('Y-m-d', strtotime("+0 day", strtotime($dDate2)));
                    }

                    $fromTime1 = str_split($departureTime1, 8);
                    $depTime1 = $dDate2 . "T" . $fromTime1[0];

                    $toTime1 = str_split($ArrivalTime1, 8);
                    $arrTime1 = $aDate2 . "T" . $toTime1[0];

                    //Array

                    $transitDetails = array("transit1" => "$Transit");

                    $segment = array("0" => array("marketingcareer" => "$markettingCarrier",
                        "marketingcareerName" => "$markettingCarrierName",
                        "marketingflight" => "$markettingFN",
                        "operatingcareer" => "$operatingCarrier",
                        "operatingflight" => "$operatingFN",
                        "operatingCarrierName" => "$operatingCarrierName",
                        "departure" => "$DepartureFrom",
                        "departureAirport" => "$dAirport ",
                        "departureLocation" => "$dCity , $dCountry",
                        "departureTime" => "$dpTime",
                        "arrival" => "$ArrivalTo",
                        "arrivalTime" => "$arrTime",
                        "arrivalAirport" => "$aAirport",
                        "arrivalLocation" => "$aCity , $aCountry",
                        "flightduration" => "$TravelTime1",
                        "bookingcode" => "$BookingCode",
                        "seat" => "$Seat1"),
                        "1" => array("marketingcareer" => "$markettingCarrier1",
                            "marketingcareerName" => "$markettingCarrierName1",
                            "marketingflight" => "$markettingFN1",
                            "operatingcareer" => "$operatingCarrier1",
                            "operatingflight" => "$operatingFN1",
                            "operatingCarrierName" => "$operatingCarrierName1",
                            "departure" => "$DepartureFrom1",
                            "departureAirport" => "$dAirport1",
                            "departureLocation" => "$dCity1 , $dCountry1",
                            "departureTime" => "$depTime1",
                            "arrival" => "$ArrivalTo1",
                            "arrivalTime" => "$arrTime1",
                            "arrivalAirport" => "$aAirport1",
                            "arrivalLocation" => "$aCity1 , $aCountry1",
                            "flightduration" => "$TravelTime2",
                            "bookingcode" => "$BookingCode1",
                            "seat" => "$Seat2"),

                    );

                    $basic = array("system" => "Sabre",
                        "segment" => "2",
                        "uId" => $uId,
                        "triptype" => $TripType,
                        "career" => "$vCarCode",
                        "careerName" => "$CarrieerName",
                        "lastTicketTime" => "$timelimit",
                        "BasePrice" => "$baseFareAmount",
                        "Taxes" => "$totalTaxAmount",
                        "netfare" => "$AgentPrice",
                        "agentprice" => "$WLAgentPrice",
                        //"subagentprice" => "$subAgentPrice",
                        "customerPrice" => $totalFare,
                        "pricebreakdown" => $PriceBreakDown,
                        "departure" => "$From",
                        "departureTime" => "$depAt1",
                        "departureDate" => $dpTime1,
                        "arrival" => "$To",
                        "arrivalTime" => "$arrAt2",
                        "arrivalDate" => "$arrTime2",
                        "flightduration" => "$JourneyDuration",
                        "bags" => "$Bags",
                        "seat" => "$Seat",
                        "class" => "$CabinClass",
                        "refundable" => "$nonRef",
                        "segments" => $segment,
                        "transit" => $transitDetails,

                    );

                    array_push($All, $basic);

                }if ($sgCount == 3) {

                    $lf = $legDescs[$id]['schedules'][0]['ref'];
                    $legref = $lf - 1;

                    $ArrivalTime1 = $scheduleDescs[$legref]['arrival']['time'];
                    $arrAt2 = substr($ArrivalTime1, 0, 5);

                    $arrivalDate1 = 0;
                    if (isset($scheduleDescs[$legref]['arrival']['dateAdjustment'])) {
                        $arrivalDate1 += 1;
                    }

                    if ($arrivalDate1 == 1) {
                        $aDate = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $aDate = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $ElapsedTime1 = $scheduleDescs[$legref]['elapsedTime'];
                    $TravelTime1 = floor($ElapsedTime1 / 60) . "H " . ($ElapsedTime1 - ((floor($ElapsedTime1 / 60)) * 60)) . "Min";

                    $ArrivalTime1 = $scheduleDescs[$legref]['arrival']['time'];
                    $arrAt1 = substr($ArrivalTime1, 0, 5);

                    $departureTime1 = $scheduleDescs[$legref]['departure']['time'];
                    $depAt1 = substr($departureTime1, 0, 5);

                    $fromTime1 = str_split($departureTime1, 8);
                    $depTimedate1 = date("D d M Y", strtotime($Date . " " . $fromTime1[0]));

                    $toTime1 = str_split($ArrivalTime1, 8);
                    $arrTimedate1 = date("D d M Y", strtotime($aDate . " " . $toTime1[0]));

                    $ArrivalTo = $scheduleDescs[$legref]['arrival']['airport'];
                    $DepartureFrom = $scheduleDescs[$legref]['departure']['airport'];

                    $ArrivalTime = $scheduleDescs[$legref]['arrival']['time'];
                    $departureTime = $scheduleDescs[$legref]['departure']['time'];
                    $markettingCarrier = $scheduleDescs[$legref]['carrier']['marketing'];

                    $carriersql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                    $carrierrow = mysqli_fetch_array($carriersql, MYSQLI_ASSOC);

                    if (!empty($carrierrow)) {
                        $markettingCarrierName = $carrierrow['name'];
                    }

                    // Departure Country
                    $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                    $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                    if (!empty($row1)) {
                        $dAirport = $row1['name'];
                        $dCity = $row1['cityName'];
                        $dCountry = $row1['countryCode'];

                    }

                    // Departure Country
                    $sql2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                    $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                    if (!empty($row2)) {
                        $aAirport = $row2['name'];
                        $aCity = $row2['cityName'];
                        $aCountry = $row2['countryCode'];

                    }

                    $markettingFN = $scheduleDescs[$legref]['carrier']['marketingFlightNumber'];
                    $operatingCarrier = $scheduleDescs[$legref]['carrier']['operating'];
                    if (isset($scheduleDescs[$legref]['carrier']['operatingFlightNumber'])) {
                        $operatingFN = $scheduleDescs[$legref]['carrier']['operatingFlightNumber'];
                    } else {
                        $operatingFN = $scheduleDescs[$legref]['carrier']['marketingFlightNumber'];
                    }

                    $opsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$operatingCarrier' ");
                    $oprow = mysqli_fetch_array($opsql, MYSQLI_ASSOC);

                    if (!empty($oprow)) {
                        $operatingCarrierName = $oprow['name'];
                    }

                    if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {
                        $Seat1 = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
                    }

                    if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {
                        $BookingCode1 = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
                    } else {
                        $BookingCode1 = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
                    }

                    $arrivalDate = 0;
                    if (isset($scheduleDescs[$legref]['arrival']['dateAdjustment'])) {
                        $arrivalDate += 1;
                    }

                    if ($arrivalDate == 1) {
                        $aDate = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $aDate = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $fromTime = str_split($departureTime, 8);
                    $dpTime = $Date . "T" . $fromTime[0];

                    $toTime = str_split($ArrivalTime, 8);
                    $arrTime = $aDate . "T" . $toTime[0];

                    //2nd Leg

                    $lf2 = $legDescs[$id]['schedules'][1]['ref'];
                    $legref1 = $lf2 - 1;

                    $dateAdjust2 = 0;
                    if (isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])) {
                        $dateAdjust2 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
                    }

                    //Store Data
                    if ($dateAdjust2 == 1) {
                        $NewDate2 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $NewDate2 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $ElapsedTime2 = $scheduleDescs[$legref1]['elapsedTime'];
                    $TravelTime2 = floor($ElapsedTime2 / 60) . "H " . ($ElapsedTime2 - ((floor($ElapsedTime2 / 60)) * 60)) . "Min";

                    $ArrivalTime2 = $scheduleDescs[$legref1]['arrival']['time'];
                    $arrAt2 = substr($ArrivalTime2, 0, 5);

                    $departureTime2 = $scheduleDescs[$legref1]['departure']['time'];
                    $depAt2 = substr($departureTime2, 0, 5);

                    $fromTime2 = str_split($departureTime2, 8);
                    $depTimedate2 = date("D d M Y", strtotime($NewDate2 . " " . $fromTime2[0]));

                    $toTime2 = str_split($ArrivalTime2, 8);
                    $arrTimedate2 = date("D d M Y", strtotime($NewDate2 . " " . $toTime2[0]));

                    $dateAdjust2 = 0;
                    if (isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])) {
                        $dateAdjust2 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
                    }

                    $descid = $scheduleDescs[$legref1]['id'];
                    $ArrivalTo1 = $scheduleDescs[$legref1]['arrival']['airport'];
                    $DepartureFrom1 = $scheduleDescs[$legref1]['departure']['airport'];

                    $ArrivalTime1 = $scheduleDescs[$legref1]['arrival']['time'];
                    $departureTime1 = $scheduleDescs[$legref1]['departure']['time'];
                    $markettingCarrier1 = $scheduleDescs[$legref1]['carrier']['marketing'];

                    $carriersql1 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
                    $carrierrow1 = mysqli_fetch_array($carriersql1, MYSQLI_ASSOC);

                    if (!empty($carrierrow1)) {
                        $markettingCarrierName1 = $carrierrow1['name'];

                    }

                    // Departure Country
                    $sql3 = mysqli_query($conn, "$Airportsql code='$DepartureFrom1' ");
                    $row3 = mysqli_fetch_array($sql3, MYSQLI_ASSOC);

                    if (!empty($row3)) {
                        $dAirport1 = $row3['name'];
                        $dCity1 = $row3['cityName'];
                        $dCountry1 = $row3['countryCode'];

                    }

                    // Departure Country
                    $sql4 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo1' ");
                    $row4 = mysqli_fetch_array($sql4, MYSQLI_ASSOC);

                    if (!empty($row4)) {
                        $aAirport1 = $row4['name'];
                        $aCity1 = $row4['cityName'];
                        $aCountry1 = $row4['countryCode'];

                    }

                    $markettingFN1 = $scheduleDescs[$legref1]['carrier']['marketingFlightNumber'];
                    $operatingCarrier1 = $scheduleDescs[$legref1]['carrier']['operating'];

                    if (isset($scheduleDescs[$legref1]['carrier']['operatingFlightNumber'])) {
                        $operatingFN1 = $scheduleDescs[$legref1]['carrier']['operatingFlightNumber'];
                    } else {
                        $operatingFN1 = 0;
                    }

                    $opsql1 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$operatingCarrier1' ");
                    $oprow1 = mysqli_fetch_array($opsql1, MYSQLI_ASSOC);

                    if (!empty($oprow)) {
                        $operatingCarrierName1 = $oprow1['name'];
                    }

                    if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {
                        $Seat2 = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
                    }

                    if (isset($fareComponents[0]['segments'][1]['segment']['bookingCode'])) {
                        $BookingCode1 = $fareComponents[0]['segments'][1]['segment']['bookingCode'];
                    } else if (isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])) {
                        $BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
                    } else {
                        $BookingCode1 = $fareComponents[0]['segments'][1]['segment']['bookingCode'];
                    }

                    //Store Data
                    if ($dateAdjust2 == 1) {
                        $dDate2 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $dDate2 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $arrivalDate2 = 0;
                    if (isset($scheduleDescs[$legref1]['arrival']['dateAdjustment'])) {
                        $arrivalDate2 += 1;
                    }

                    if ($arrivalDate2 == 1) {
                        $aDate2 = date('Y-m-d', strtotime("+1 day", strtotime($dDate2)));
                    } else {
                        $aDate2 = date('Y-m-d', strtotime("+0 day", strtotime($dDate2)));
                    }

                    $fromTime1 = str_split($departureTime1, 8);
                    $dpTime1 = $dDate2 . "T" . $fromTime1[0];

                    $toTime1 = str_split($ArrivalTime1, 8);
                    $arrTime1 = $aDate2 . "T" . $toTime1[0];

                    // 3rd Leg

                    $lf3 = $legDescs[$id]['schedules'][2]['ref'];
                    $legref2 = $lf3 - 1;

                    $dateAdjust3 = 0;
                    if (isset($legDescs[$id]['schedules'][2]['departureDateAdjustment'])) {
                        $dateAdjust3 = $legDescs[$id]['schedules'][2]['departureDateAdjustment'];
                    }

                    //Store Data
                    if ($dateAdjust3 == 1) {
                        $NewDate3 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $NewDate3 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $ElapsedTime3 = $scheduleDescs[$legref2]['elapsedTime'];
                    $TravelTime3 = floor($ElapsedTime3 / 60) . "H " . ($ElapsedTime3 - ((floor($ElapsedTime3 / 60)) * 60)) . "Min";

                    $ArrivalTime3 = $scheduleDescs[$legref2]['arrival']['time'];
                    $arrAt3 = substr($ArrivalTime3, 0, 5);

                    $departureTime3 = $scheduleDescs[$legref2]['departure']['time'];
                    $depAt3 = substr($departureTime3, 0, 5);

                    $fromTime3 = str_split($departureTime3, 8);
                    $depTimedate3 = date("D d M Y", strtotime($NewDate3 . " " . $fromTime3[0]));

                    $toTime3 = str_split($ArrivalTime3, 8);
                    $arrTimedate3 = date("D d M Y", strtotime($NewDate3 . " " . $toTime3[0]));

                    $dateAdjust3 = 0;

                    if (isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])) {
                        $dateAdjust3 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
                    }

                    $ArrivalTo2 = $scheduleDescs[$legref2]['arrival']['airport'];
                    $DepartureFrom2 = $scheduleDescs[$legref2]['departure']['airport'];
                    $ArrivalTime2 = $scheduleDescs[$legref2]['arrival']['time'];
                    $departureTime2 = $scheduleDescs[$legref2]['departure']['time'];
                    $markettingCarrier2 = $scheduleDescs[$legref2]['carrier']['marketing'];

                    $carriersql2 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier2' ");
                    $carrierrow2 = mysqli_fetch_array($carriersql2, MYSQLI_ASSOC);

                    if (!empty($carrierrow2)) {
                        $markettingCarrierName2 = $carrierrow2['name'];
                    }

                    // Departure Country
                    $dsql3 = mysqli_query($conn, "$Airportsql code='$DepartureFrom2' ");
                    $drow3 = mysqli_fetch_array($dsql3, MYSQLI_ASSOC);

                    if (!empty($drow3)) {
                        $dAirport2 = $drow3['name'];
                        $dCity2 = $drow3['cityName'];
                        $dCountry2 = $drow3['countryCode'];

                    }

                    // Arrival Country
                    $asql4 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo2' ");
                    $arow4 = mysqli_fetch_array($asql4, MYSQLI_ASSOC);

                    if (!empty($arow4)) {
                        $aAirport2 = $arow4['name'];
                        $aCity2 = $arow4['cityName'];
                        $aCountry2 = $arow4['countryCode'];

                    }

                    $markettingFN2 = $scheduleDescs[$legref2]['carrier']['marketingFlightNumber'];
                    $operatingCarrier2 = $scheduleDescs[$legref2]['carrier']['operating'];

                    if (isset($scheduleDescs[$legref2]['carrier']['operatingFlightNumber'])) {
                        $operatingFN2 = $scheduleDescs[$legref2]['carrier']['operatingFlightNumber'];
                    } else {
                        $operatingFN2 = $scheduleDescs[$legref2]['carrier']['marketingFlightNumber'];
                    }

                    $opsql2 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$operatingCarrier2' ");
                    $oprow2 = mysqli_fetch_array($opsql2, MYSQLI_ASSOC);

                    if (!empty($oprow2)) {
                        $operatingCarrierName2 = $oprow2['name'];
                    }

                    if (isset($fareComponents[0]['segments'][2]['segment']['seatsAvailable'])) {
                        $Seat3 = $fareComponents[0]['segments'][2]['segment']['seatsAvailable'];
                    }

                    if (isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])) {
                        $BookingCode2 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
                    } else if (isset($fareComponents[1]['segments'][1]['segment']['bookingCode'])) {
                        $BookingCode2 = $fareComponents[1]['segments'][1]['segment']['bookingCode'];
                    } else {
                        $BookingCode2 = $fareComponents[0]['segments'][2]['segment']['bookingCode'];
                    }

                    //Store Data
                    if ($dateAdjust3 == 1) {
                        $dDate3 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $dDate3 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $arrivalDate3 = 0;
                    if (isset($scheduleDescs[$legref2]['arrival']['dateAdjustment'])) {
                        $arrivalDate3 += 1;
                    }

                    if ($arrivalDate3 == 1) {
                        $aDate3 = date('Y-m-d', strtotime("+1 day", strtotime($dDate3)));
                    } else {
                        $aDate3 = date('Y-m-d', strtotime("+0 day", strtotime($dDate3)));
                    }

                    $fromTime2 = str_split($departureTime2, 8);
                    $dpTime2 = $dDate3 . "T" . $fromTime2[0];

                    $toTime2 = str_split($ArrivalTime2, 8);
                    $arrTime2 = $aDate3 . "T" . $toTime2[0];

                    $segment = array("0" => array("marketingcareer" => "$markettingCarrier",
                        "marketingcareerName" => "$markettingCarrierName",
                        "marketingflight" => "$markettingFN",
                        "operatingcareer" => "$operatingCarrier",
                        "operatingflight" => "$operatingFN",
                        "operatingCarrierName" => "$operatingCarrierName",
                        "departure" => "$DepartureFrom",
                        "departureAirport" => "$dAirport ",
                        "departureLocation" => "$dCity , $dCountry",
                        "departureTime" => "$dpTime",
                        "arrival" => "$ArrivalTo",
                        "arrivalTime" => "$arrTime",
                        "arrivalAirport" => "$aAirport",
                        "arrivalLocation" => "$aCity , $aCountry",
                        "flightduration" => "$TravelTime1",
                        "bookingcode" => "$BookingCode",
                        "seat" => "$Seat"),
                        "1" => array("marketingcareer" => "$markettingCarrier1",
                            "marketingcareerName" => "$markettingCarrierName1",
                            "marketingflight" => "$markettingFN1",
                            "operatingcareer" => "$operatingCarrier1",
                            "operatingflight" => "$operatingFN1",
                            "operatingCarrierName1" => "$operatingCarrierName1",
                            "departure" => "$DepartureFrom1",
                            "departureAirport" => "$dAirport1",
                            "departureLocation" => "$dCity1 , $dCountry1",
                            "departureTime" => "$dpTime1",
                            "arrival" => "$ArrivalTo1",
                            "arrivalTime" => "$arrTime1",
                            "arrivalAirport" => "$aAirport1",
                            "arrivalLocation" => "$aCity1 , $aCountry1",
                            "flightduration" => "$TravelTime2",
                            "bookingcode" => "$BookingCode1",
                            "seat" => "$Seat1"),
                        "2" => array("marketingcareer" => "$markettingCarrier2",
                            "marketingcareerName" => "$markettingCarrierName2",
                            "marketingflight" => "$markettingFN2",
                            "operatingcareer" => "$operatingCarrier2",
                            "operatingflight" => "$operatingFN2",
                            "operatingCarrierName" => "$operatingCarrierName2",
                            "departure" => "$DepartureFrom2",
                            "departureAirport" => "$dAirport2",
                            "departureLocation" => "$dCity2 , $dCountry2",
                            "departureTime" => "$dpTime2",
                            "arrival" => "$ArrivalTo2",
                            "arrivalTime" => "$arrTime2",
                            "arrivalAirport" => "$aAirport2",
                            "arrivalLocation" => "$aCity1 , $aCountry2",
                            "flightduration" => "$TravelTime3",
                            "bookingcode" => "$BookingCode2",
                            "seat" => "$Seat2"),

                    );
                    $TransitTime = round(abs(strtotime($dpTime1) - strtotime($arrTime)) / 60, 2);
                    $TransitDuration = floor($TransitTime / 60) . "H " . ($TransitTime - ((floor($TransitTime / 60)) * 60)) . "Min";

                    $TransitTime1 = round(abs(strtotime($dpTime2) - strtotime($arrTime1)) / 60, 2);
                    $TransitDuration1 = floor($TransitTime1 / 60) . "H " . ($TransitTime1 - ((floor($TransitTime1 / 60)) * 60)) . "Min";

                    $transitDetails = array("transit1" => $TransitDuration,
                        "transit2" => $TransitDuration1);

                    $basic = array("system" => "Sabre",
                        "segment" => "3",
                        "uId" => $uId,
                        "triptype" => $TripType,
                        "career" => "$vCarCode",
                        "careerName" => "$CarrieerName",
                        "lastTicketTime" => "$timelimit",
                        "BasePrice" => "$baseFareAmount",
                        "Taxes" => "$totalTaxAmount",
                        "netfare" => "$AgentPrice",
                        "agentprice" => "$WLAgentPrice",
                        //"subagentprice" => "$subAgentPrice",
                        "customerPrice" => $totalFare,
                        "pricebreakdown" => $PriceBreakDown,
                        "departure" => "$From",
                        "departureTime" => "$depAt1",
                        "departureDate" => "$depTimedate1",
                        "arrival" => "$To",
                        "arrivalTime" => "$arrAt2",
                        "arrivalDate" => "$arrTimedate3",
                        "flightduration" => "$JourneyDuration",
                        "bags" => "$Bags",
                        "seat" => "$Seat",
                        "class" => "$CabinClass",
                        "refundable" => "$nonRef",
                        "segments" => $segment,
                        "transit" => $transitDetails,

                    );

                    array_push($All, $basic);

                } else if ($sgCount == 4) {

                    $lf = $legDescs[$id]['schedules'][0]['ref'];
                    $legref = $lf - 1;

                    $ArrivalTime1 = $scheduleDescs[$legref]['arrival']['time'];
                    $arrAt2 = substr($ArrivalTime1, 0, 5);

                    $arrivalDate1 = 0;
                    if (isset($scheduleDescs[$legref]['arrival']['dateAdjustment'])) {
                        $arrivalDate1 += 1;
                    }

                    if ($arrivalDate1 == 1) {
                        $aDate = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $aDate = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $ElapsedTime1 = $scheduleDescs[$legref]['elapsedTime'];
                    $TravelTime1 = floor($ElapsedTime1 / 60) . "H " . ($ElapsedTime1 - ((floor($ElapsedTime1 / 60)) * 60)) . "Min";

                    $ArrivalTime1 = $scheduleDescs[$legref]['arrival']['time'];
                    $arrAt1 = substr($ArrivalTime1, 0, 5);

                    $departureTime1 = $scheduleDescs[$legref]['departure']['time'];
                    $depAt1 = substr($departureTime1, 0, 5);

                    $fromTime1 = str_split($departureTime1, 8);
                    $dpTime1 = date("D d M Y", strtotime($Date . " " . $fromTime1[0]));

                    $toTime1 = str_split($ArrivalTime1, 8);
                    $arrTime1 = date("D d M Y", strtotime($aDate . " " . $toTime1[0]));

                    $ArrivalTo = $scheduleDescs[$legref]['arrival']['airport'];
                    $DepartureFrom = $scheduleDescs[$legref]['departure']['airport'];

                    $ArrivalTime = $scheduleDescs[$legref]['arrival']['time'];
                    $departureTime = $scheduleDescs[$legref]['departure']['time'];
                    $markettingCarrier = $scheduleDescs[$legref]['carrier']['marketing'];

                    $carriersql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                    $carrierrow = mysqli_fetch_array($carriersql, MYSQLI_ASSOC);

                    if (!empty($carrierrow)) {
                        $markettingCarrierName = $carrierrow['name'];
                    }

                    // Departure Country
                    $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                    $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                    if (!empty($row1)) {
                        $dAirport = $row1['name'];
                        $dCity = $row1['cityName'];
                        $dCountry = $row1['countryCode'];

                    }

                    // Departure Country
                    $sql2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                    $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                    if (!empty($row2)) {
                        $aAirport = $row2['name'];
                        $aCity = $row2['cityName'];
                        $aCountry = $row2['countryCode'];

                    }

                    $markettingFN = $scheduleDescs[$legref]['carrier']['marketingFlightNumber'];
                    $operatingCarrier = $scheduleDescs[$legref]['carrier']['operating'];
                    if (isset($scheduleDescs[$legref]['carrier']['operatingFlightNumber'])) {
                        $operatingFN = $scheduleDescs[$legref]['carrier']['operatingFlightNumber'];
                    } else {
                        $operatingFN = $scheduleDescs[$legref]['carrier']['marketingFlightNumber'];
                    }

                    if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {
                        $Seat = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
                    }

                    if (isset($fareComponents[0]['segments'][0]['segment']['bookingCode'])) {
                        $BookingCode1 = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
                    } else {
                        $BookingCode1 = $fareComponents[0]['segments'][0]['segment']['bookingCode'];
                    }

                    $arrivalDate = 0;
                    if (isset($scheduleDescs[$legref]['arrival']['dateAdjustment'])) {
                        $arrivalDate += 1;
                    }

                    if ($arrivalDate == 1) {
                        $aDate = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $aDate = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $fromTime = str_split($departureTime, 8);
                    $dpTime = $Date . "T" . $fromTime[0];

                    $toTime = str_split($ArrivalTime, 8);
                    $arrTime = $aDate . "T" . $toTime[0];

                    //2nd Leg

                    $lf2 = $legDescs[$id]['schedules'][1]['ref'];
                    $legref1 = $lf2 - 1;

                    $dateAdjust2 = 0;
                    if (isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])) {
                        $dateAdjust2 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
                    }

                    //Store Data
                    if ($dateAdjust2 == 1) {
                        $NewDate2 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $NewDate2 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $ElapsedTime2 = $scheduleDescs[$legref1]['elapsedTime'];
                    $TravelTime2 = floor($ElapsedTime2 / 60) . "H " . ($ElapsedTime2 - ((floor($ElapsedTime2 / 60)) * 60)) . "Min";

                    $ArrivalTime2 = $scheduleDescs[$legref1]['arrival']['time'];
                    $arrAt2 = substr($ArrivalTime2, 0, 5);

                    $departureTime2 = $scheduleDescs[$legref1]['departure']['time'];
                    $depAt2 = substr($departureTime2, 0, 5);

                    $fromTime2 = str_split($departureTime2, 8);
                    $dpTime2 = date("D d M Y", strtotime($NewDate2 . " " . $fromTime2[0]));

                    $toTime2 = str_split($ArrivalTime2, 8);
                    $arrTime2 = date("D d M Y", strtotime($NewDate2 . " " . $toTime2[0]));

                    $dateAdjust2 = 0;
                    if (isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])) {
                        $dateAdjust2 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
                    }

                    $descid = $scheduleDescs[$legref1]['id'];
                    $ArrivalTo1 = $scheduleDescs[$legref1]['arrival']['airport'];
                    $DepartureFrom1 = $scheduleDescs[$legref1]['departure']['airport'];

                    $ArrivalTime1 = $scheduleDescs[$legref1]['arrival']['time'];
                    $departureTime1 = $scheduleDescs[$legref1]['departure']['time'];
                    $markettingCarrier1 = $scheduleDescs[$legref1]['carrier']['marketing'];

                    $carriersql1 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
                    $carrierrow1 = mysqli_fetch_array($carriersql1, MYSQLI_ASSOC);

                    if (!empty($carrierrow1)) {
                        $markettingCarrierName1 = $carrierrow1['name'];

                    }

                    // Departure Country
                    $sql3 = mysqli_query($conn, "$Airportsql code='$DepartureFrom1' ");
                    $row3 = mysqli_fetch_array($sql3, MYSQLI_ASSOC);

                    if (!empty($row3)) {
                        $dAirport1 = $row3['name'];
                        $dCity1 = $row3['cityName'];
                        $dCountry1 = $row3['countryCode'];

                    }

                    // Departure Country
                    $sql4 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo1' ");
                    $row4 = mysqli_fetch_array($sql4, MYSQLI_ASSOC);

                    if (!empty($row4)) {
                        $aAirport1 = $row4['name'];
                        $aCity1 = $row4['cityName'];
                        $aCountry1 = $row4['countryCode'];

                    }

                    $markettingFN1 = $scheduleDescs[$legref1]['carrier']['marketingFlightNumber'];
                    $operatingCarrier1 = $scheduleDescs[$legref1]['carrier']['operating'];

                    if (isset($scheduleDescs[$legref1]['carrier']['operatingFlightNumber'])) {
                        $operatingFN1 = $scheduleDescs[$legref1]['carrier']['operatingFlightNumber'];
                    } else {
                        $operatingFN1 = 0;
                    }

                    $opsql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$operatingCarrier1' ");
                    $oprow = mysqli_fetch_array($opsql, MYSQLI_ASSOC);

                    if (!empty($oprow)) {
                        $operatingCarrierName1 = $oprow['name'];
                    }

                    if (isset($fareComponents[0]['segments'][0]['segment']['seatsAvailable'])) {
                        $Seat1 = $fareComponents[0]['segments'][0]['segment']['seatsAvailable'];
                    }

                    if (isset($fareComponents[0]['segments'][1]['segment']['bookingCode'])) {
                        $BookingCode1 = $fareComponents[0]['segments'][1]['segment']['bookingCode'];
                    } else if (isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])) {
                        $BookingCode1 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
                    } else {
                        $BookingCode1 = $fareComponents[0]['segments'][1]['segment']['bookingCode'];
                    }

                    //Store Data
                    if ($dateAdjust2 == 1) {
                        $dDate2 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $dDate2 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $arrivalDate2 = 0;
                    if (isset($scheduleDescs[$legref1]['arrival']['dateAdjustment'])) {
                        $arrivalDate2 += 1;
                    }

                    if ($arrivalDate2 == 1) {
                        $aDate2 = date('Y-m-d', strtotime("+1 day", strtotime($dDate2)));
                    } else {
                        $aDate2 = date('Y-m-d', strtotime("+0 day", strtotime($dDate2)));
                    }

                    $fromTime1 = str_split($departureTime1, 8);
                    $dpTime1 = $dDate2 . "T" . $fromTime1[0];

                    $toTime1 = str_split($ArrivalTime1, 8);
                    $arrTime1 = $aDate2 . "T" . $toTime1[0];

                    // 4rth Leg

                    $lf3 = $legDescs[$id]['schedules'][2]['ref'];
                    $legref2 = $lf3 - 1;

                    $dateAdjust3 = 0;
                    if (isset($legDescs[$id]['schedules'][2]['departureDateAdjustment'])) {
                        $dateAdjust3 = $legDescs[$id]['schedules'][2]['departureDateAdjustment'];
                    }

                    //Store Data
                    if ($dateAdjust3 == 1) {
                        $NewDate3 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $NewDate3 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $ElapsedTime3 = $scheduleDescs[$legref2]['elapsedTime'];
                    $TravelTime3 = floor($ElapsedTime3 / 60) . "H " . ($ElapsedTime3 - ((floor($ElapsedTime3 / 60)) * 60)) . "Min";

                    $ArrivalTime3 = $scheduleDescs[$legref2]['arrival']['time'];
                    $arrAt3 = substr($ArrivalTime3, 0, 5);

                    $departureTime3 = $scheduleDescs[$legref2]['departure']['time'];
                    $depAt3 = substr($departureTime3, 0, 5);

                    $fromTime3 = str_split($departureTime3, 8);
                    $depTime3 = date("D d M Y", strtotime($NewDate3 . " " . $fromTime3[0]));

                    $toTime3 = str_split($ArrivalTime3, 8);
                    $arrTime3 = date("D d M Y", strtotime($NewDate3 . " " . $toTime3[0]));

                    $dateAdjust3 = 0;

                    if (isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])) {
                        $dateAdjust3 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
                    }

                    $ArrivalTo2 = $scheduleDescs[$legref2]['arrival']['airport'];
                    $DepartureFrom2 = $scheduleDescs[$legref2]['departure']['airport'];
                    $ArrivalTime2 = $scheduleDescs[$legref2]['arrival']['time'];
                    $departureTime2 = $scheduleDescs[$legref2]['departure']['time'];
                    $markettingCarrier2 = $scheduleDescs[$legref2]['carrier']['marketing'];

                    $carriersql2 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier2' ");
                    $carrierrow2 = mysqli_fetch_array($carriersql2, MYSQLI_ASSOC);

                    if (!empty($carrierrow2)) {
                        $markettingCarrierName2 = $carrierrow2['name'];
                    }

                    // Departure Country
                    $dsql3 = mysqli_query($conn, "$Airportsql code='$DepartureFrom2' ");
                    $drow3 = mysqli_fetch_array($dsql3, MYSQLI_ASSOC);

                    if (!empty($drow3)) {
                        $dAirport2 = $drow3['name'];
                        $dCity2 = $drow3['cityName'];
                        $dCountry2 = $drow3['countryCode'];

                    }

                    // Arrival Country
                    $asql4 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo2' ");
                    $arow4 = mysqli_fetch_array($asql4, MYSQLI_ASSOC);

                    if (!empty($arow4)) {
                        $aAirport2 = $arow4['name'];
                        $aCity2 = $arow4['cityName'];
                        $aCountry2 = $arow4['countryCode'];

                    }

                    $markettingFN2 = $scheduleDescs[$legref2]['carrier']['marketingFlightNumber'];
                    $operatingCarrier2 = $scheduleDescs[$legref2]['carrier']['operating'];

                    if (isset($scheduleDescs[$legref2]['carrier']['operatingFlightNumber'])) {
                        $operatingFN2 = $scheduleDescs[$legref2]['carrier']['operatingFlightNumber'];
                    } else {
                        $operatingFN2 = $scheduleDescs[$legref2]['carrier']['marketingFlightNumber'];
                    }

                    if (isset($fareComponents[0]['segments'][2]['segment']['seatsAvailable'])) {
                        $Seat2 = $fareComponents[0]['segments'][2]['segment']['seatsAvailable'];
                    } else {
                        $Seat2 = $Seat1;
                    }

                    if (isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])) {
                        $BookingCode2 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
                    } else if (isset($fareComponents[1]['segments'][1]['segment']['bookingCode'])) {
                        $BookingCode2 = $fareComponents[1]['segments'][1]['segment']['bookingCode'];
                    } else {
                        $BookingCode2 = $fareComponents[0]['segments'][2]['segment']['bookingCode'];
                    }

                    //Store Data
                    if ($dateAdjust3 == 1) {
                        $dDate3 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $dDate3 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $arrivalDate3 = 0;
                    if (isset($scheduleDescs[$legref2]['arrival']['dateAdjustment'])) {
                        $arrivalDate3 += 1;
                    }

                    if ($arrivalDate3 == 1) {
                        $aDate3 = date('Y-m-d', strtotime("+1 day", strtotime($dDate3)));
                    } else {
                        $aDate3 = date('Y-m-d', strtotime("+0 day", strtotime($dDate3)));
                    }

                    $fromTime2 = str_split($departureTime2, 8);
                    $dpTime2 = $dDate3 . "T" . $fromTime2[0];

                    $toTime2 = str_split($ArrivalTime2, 8);
                    $arrTime2 = $aDate3 . "T" . $toTime2[0];

                    // 4rth Leg

                    $lf4 = $legDescs[$id]['schedules'][3]['ref'];
                    $legref3 = $lf4 - 1;

                    $dateAdjust4 = 0;
                    if (isset($legDescs[$id]['schedules'][3]['departureDateAdjustment'])) {
                        $dateAdjust3 = $legDescs[$id]['schedules'][3]['departureDateAdjustment'];
                    }

                    //Store Data
                    if ($dateAdjust4 == 1) {
                        $NewDate4 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $NewDate4 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $ElapsedTime4 = $scheduleDescs[$legref3]['elapsedTime'];
                    $TravelTime4 = floor($ElapsedTime4 / 60) . "H " . ($ElapsedTime4 - ((floor($ElapsedTime4 / 60)) * 60)) . "Min";

                    $ArrivalTime4 = $scheduleDescs[$legref3]['arrival']['time'];
                    $arrAt4 = substr($ArrivalTime4, 0, 5);

                    $departureTime4 = $scheduleDescs[$legref3]['departure']['time'];
                    $depAt4 = substr($departureTime4, 0, 5);

                    $fromTime4 = str_split($departureTime4, 8);
                    $dpTime4 = date("D d M Y", strtotime($NewDate4 . " " . $fromTime4[0]));

                    $toTime4 = str_split($ArrivalTime4, 8);
                    $arrTime4 = date("D d M Y", strtotime($NewDate4 . " " . $toTime4[0]));

                    $dateAdjust4 = 0;

                    if (isset($legDescs[$id]['schedules'][1]['departureDateAdjustment'])) {
                        $dateAdjust4 = $legDescs[$id]['schedules'][1]['departureDateAdjustment'];
                    }

                    $ArrivalTo3 = $scheduleDescs[$legref3]['arrival']['airport'];
                    $DepartureFrom3 = $scheduleDescs[$legref3]['departure']['airport'];
                    $ArrivalTime3 = $scheduleDescs[$legref3]['arrival']['time'];
                    $departureTime3 = $scheduleDescs[$legref3]['departure']['time'];
                    $markettingCarrier3 = $scheduleDescs[$legref3]['carrier']['marketing'];

                    $carriersql3 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier3' ");
                    $carrierrow3 = mysqli_fetch_array($carriersql3, MYSQLI_ASSOC);

                    if (!empty($carrierrow3)) {
                        $markettingCarrierName3 = $carrierrow3['name'];
                    }

                    // Departure Country
                    $dsql4 = mysqli_query($conn, "$Airportsql code='$DepartureFrom3' ");
                    $drow4 = mysqli_fetch_array($dsql4, MYSQLI_ASSOC);

                    if (!empty($drow4)) {
                        $dAirport3 = $drow4['name'];
                        $dCity3 = $drow4['cityName'];
                        $dCountry3 = $drow4['countryCode'];

                    }

                    // Arrival Country
                    $asql4 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo3' ");
                    $arow4 = mysqli_fetch_array($asql4, MYSQLI_ASSOC);

                    if (!empty($arow4)) {
                        $aAirport3 = $arow4['name'];
                        $aCity3 = $arow4['cityName'];
                        $aCountry3 = $arow4['countryCode'];

                    }

                    $markettingFN3 = $scheduleDescs[$legref3]['carrier']['marketingFlightNumber'];
                    $operatingCarrier3 = $scheduleDescs[$legref3]['carrier']['operating'];

                    if (isset($scheduleDescs[$legref3]['carrier']['operatingFlightNumber'])) {
                        $operatingFN3 = $scheduleDescs[$legref3]['carrier']['operatingFlightNumber'];
                    } else {
                        $operatingFN3 = $scheduleDescs[$legref3]['carrier']['marketingFlightNumber'];
                    }

                    if (isset($fareComponents[0]['segments'][3]['segment']['seatsAvailable'])) {
                        $Seat3 = $fareComponents[0]['segments'][3]['segment']['seatsAvailable'];
                    } else {
                        $Seat3 = $Seat1;
                    }

                    if (isset($fareComponents[1]['segments'][0]['segment']['bookingCode'])) {
                        $BookingCode3 = $fareComponents[1]['segments'][0]['segment']['bookingCode'];
                    } else if (isset($fareComponents[1]['segments'][1]['segment']['bookingCode'])) {
                        $BookingCode3 = $fareComponents[1]['segments'][1]['segment']['bookingCode'];
                    } else {
                        $BookingCode3 = $fareComponents[0]['segments'][3]['segment']['bookingCode'];
                    }

                    //Store Data
                    if ($dateAdjust3 == 1) {
                        $dDate4 = date('Y-m-d', strtotime("+1 day", strtotime($Date)));
                    } else {
                        $dDate4 = date('Y-m-d', strtotime("+0 day", strtotime($Date)));
                    }

                    $arrivalDate4 = 0;
                    if (isset($scheduleDescs[$legref3]['arrival']['dateAdjustment'])) {
                        $arrivalDate4 += 1;
                    }

                    if ($arrivalDate4 == 1) {
                        $aDate4 = date('Y-m-d', strtotime("+1 day", strtotime($dDate4)));
                    } else {
                        $aDate4 = date('Y-m-d', strtotime("+0 day", strtotime($dDate4)));
                    }

                    $fromTime3 = str_split($departureTime3, 8);
                    $dpTime3 = $dDate4 . "T" . $fromTime3[0];

                    $toTime3 = str_split($ArrivalTime3, 8);
                    $arrTime3 = $aDate4 . "T" . $toTime3[0];

                    $segment = array("0" => array("marketingcareer" => "$markettingCarrier",
                        "marketingcareerName" => "$markettingCarrierName",
                        "marketingflight" => "$markettingFN",
                        "operatingcareer" => "$operatingCarrier",
                        "operatingflight" => "$operatingFN",
                        "operatingCarrierName" => "$operatingCarrierName",
                        "departure" => "$DepartureFrom",
                        "departureAirport" => "$dAirport ",
                        "departureLocation" => "$dCity , $dCountry",
                        "departureTime" => "$dpTime",
                        "arrival" => "$ArrivalTo",
                        "arrivalTime" => "$arrTime",
                        "arrivalAirport" => "$aAirport",
                        "arrivalLocation" => "$aCity , $aCountry",
                        "flightduration" => "$TravelTime1",
                        "bookingcode" => "$BookingCode",
                        "seat" => "$Seat"),
                        "1" => array("marketingcareer" => "$markettingCarrier1",
                            "marketingcareerName" => "$markettingCarrierName1",
                            "marketingflight" => "$markettingFN1",
                            "operatingcareer" => "$operatingCarrier1",
                            "operatingflight" => "$operatingFN1",
                            "operatingCarrierName" => "$operatingCarrierName1",
                            "departure" => "$DepartureFrom1",
                            "departureAirport" => "$dAirport1",
                            "departureLocation" => "$dCity1 , $dCountry1",
                            "departureTime" => "$dpTime1",
                            "arrival" => "$ArrivalTo1",
                            "arrivalTime" => "$arrTime1",
                            "arrivalAirport" => "$aAirport1",
                            "arrivalLocation" => "$aCity1 , $aCountry1",
                            "flightduration" => "$TravelTime2",
                            "bookingcode" => "$BookingCode1",
                            "seat" => "$Seat1"),
                        "2" => array("marketingcareer" => "$markettingCarrier2",
                            "marketingcareerName" => "$markettingCarrierName2",
                            "marketingflight" => "$markettingFN2",
                            "operatingcareer" => "$operatingCarrier2",
                            "operatingflight" => "$operatingFN2",
                            "operatingCarrierName" => "$operatingCarrierName2",
                            "departure" => "$DepartureFrom2",
                            "departureAirport" => "$dAirport2",
                            "departureLocation" => "$dCity2 , $dCountry2",
                            "departureTime" => "$dpTime2",
                            "arrival" => "$ArrivalTo2",
                            "arrivalTime" => "$arrTime2",
                            "arrivalAirport" => "$aAirport2",
                            "arrivalLocation" => "$aCity1 , $aCountry2",
                            "flightduration" => "$TravelTime3",
                            "bookingcode" => "$BookingCode2",
                            "seat" => "$Seat2"),
                        "3" => array("marketingcareer" => "$markettingCarrier3",
                            "marketingcareerName" => "$markettingCarrierName3",
                            "marketingflight" => "$markettingFN3",
                            "operatingcareer" => "$operatingCarrier3",
                            "operatingflight" => "$operatingFN3",
                            "operatingCarrierName" => "$operatingCarrierName3",
                            "departure" => "$DepartureFrom3",
                            "departureAirport" => "$dAirport3",
                            "departureLocation" => "$dCity3 , $dCountry3",
                            "departureTime" => "$dpTime3",
                            "arrival" => "$ArrivalTo3",
                            "arrivalTime" => "$arrTime3",
                            "arrivalAirport" => "$aAirport3",
                            "arrivalLocation" => "$aCity3 , $aCountry3",
                            "flightduration" => "$TravelTime4",
                            "bookingcode" => "$BookingCode3",
                            "seat" => "$Seat3"),

                    );
                    $TransitTime = round(abs(strtotime($dpTime1) - strtotime($arrTime)) / 60, 2);
                    $TransitDuration = floor($TransitTime / 60) . "H " . ($TransitTime - ((floor($TransitTime / 60)) * 60)) . "Min";

                    $TransitTime1 = round(abs(strtotime($dpTime2) - strtotime($arrTime1)) / 60, 2);
                    $TransitDuration1 = floor($TransitTime1 / 60) . "H " . ($TransitTime1 - ((floor($TransitTime1 / 60)) * 60)) . "Min";

                    $TransitTime2 = round(abs(strtotime($dpTime3) - strtotime($arrTime2)) / 60, 2);
                    $TransitDuration2 = floor($TransitTime2 / 60) . "H " . ($TransitTime2 - ((floor($TransitTime2 / 60)) * 60)) . "Min";

                    $transitDetails = array("transit1" => $TransitDuration,
                        "transit2" => $TransitDuration1,
                        "transit3" => $TransitDuration2);

                    $basic = array("system" => "Sabre",
                        "segment" => "4",
                        "uId" => $uId,
                        "career" => "$vCarCode",
                        "careerName" => "$CarrieerName",
                        "BasePrice" => "$baseFareAmount",
                        "Taxes" => "$totalTaxAmount",
                        "netfare" => "$AgentPrice",
                        "agentprice" => "$WLAgentPrice",
                        //"subagentprice" => "$subAgentPrice",
                        "customerPrice" => $totalFare,
                        "pricebreakdown" => $PriceBreakDown,
                        "departure" => "$From",
                        "departureTime" => "$depAt1",
                        "departureDate" => "$dpTime1",
                        "arrival" => "$To",
                        "arrivalTime" => "$arrAt3",
                        "arrivalDate" => "$arrTime3",
                        "flightduration" => "$JourneyDuration",
                        "bags" => "$Bags",
                        "seat" => "$Seat",
                        "class" => "$CabinClass",
                        "refundable" => "$nonRef",
                        "segments" => $segment,
                        "transit" => $transitDetails,

                    );

                }

                //$All[$Exact] = $basic;

            }

        }
    }

} /// Sabre End

if ($Galileo == 1) { // Galileo Start
    $Gallpax = array();
    if ($adult > 0 && $child > 0 && $infants > 0) {

        for ($i = 1; $i <= $adult; $i++) {
            $adultcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" BookingTravelerRef="1' . $i . '" />';
            array_push($Gallpax, $adultcount);
        }
        for ($i = 1; $i <= $child; $i++) {
            $childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="CNN" Age="07" BookingTravelerRef="2' . $i . '" />';
            array_push($Gallpax, $childcount);
        }
        for ($i = 1; $i <= $infants; $i++) {
            $infantscount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="INF" Age="1" DOB="2021-06-04" BookingTravelerRef="3' . $i . '" />';
            array_push($Gallpax, $infantscount);

        }

    } else if ($adult > 0 && $child > 0) {

        for ($i = 1; $i <= $adult; $i++) {
            $adultcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" BookingTravelerRef="1' . $i . '" />';
            array_push($Gallpax, $adultcount);
        }
        for ($i = 1; $i <= $child; $i++) {
            $childcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="CNN" Age="07" BookingTravelerRef="2' . $i . '" />';
            array_push($Gallpax, $childcount);
        }

    } else if ($adult > 0 && $infants > 0) {

        for ($i = 1; $i <= $adult; $i++) {
            $adultcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" BookingTravelerRef="1' . $i . '" />';
            array_push($Gallpax, $adultcount);
        }
        for ($i = 1; $i <= $infants; $i++) {
            $infantscount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="INF" Age="1" BookingTravelerRef="3' . $i . '" />';
            array_push($Gallpax, $infantscount);

        }

    } else {

        for ($i = 1; $i <= $adult; $i++) {
            $adultcount = '<SearchPassenger xmlns="http://www.travelport.com/schema/common_v42_0" Code="ADT" BookingTravelerRef="1' . $i . '" />';
            array_push($Gallpax, $adultcount);
        }
    }

    //Galileo Api
    $Passenger = implode(" ", $Gallpax);

    //$TARGETBRANCH = 'P7182044';
    //$CREDENTIALS = 'Universal API/uAPI5270664478-0c51bde6:2Td*m/F3M5'; cert

    $TARGETBRANCH = 'P4218912';
    $CREDENTIALS = 'Universal API/uAPI4444837655-83fe5101:K/s3-5Sy4c'; //Prod
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

    $auth = base64_encode("$CREDENTIALS");
    $soap_do = curl_init("https://apac.universal-api.travelport.com/B2BGateway/connect/uAPI/AirService");
    $header = array(
        "Content-Type: text/xml;charset=UTF-8",
        "Accept: gzip,deflate",
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "SOAPAction: \"\"",
        "Authorization: Basic $auth",
        "Content-length: " . strlen($message),
    );

    curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($soap_do, CURLOPT_POST, true);
    curl_setopt($soap_do, CURLOPT_POSTFIELDS, $message);
    curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header);
    curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
    $return = curl_exec($soap_do);
    curl_close($soap_do);

    //print_r($return);

    //$return = file_get_contents("res.xml") ;
    $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $return);
    $xml = new SimpleXMLElement($response); /// to do

    if (isset($xml->xpath('//airLowFareSearchRsp')[0])) {
        $body = $xml->xpath('//airLowFareSearchRsp')[0];

        $result = json_decode(json_encode((array) $body), true);

        //print_r($result);

        $TraceId = $result['@attributes']['TraceId'];
        $airFlightDetailsList = $result['airFlightDetailsList']['airFlightDetails']; //print_r($airFlightDetailsList);
        $airAirSegmentList = $result['airAirSegmentList']['airAirSegment']; //print_r($airFlightDetailsList);
        $airFareInfoList = $result['airFareInfoList']['airFareInfo']; //print_r($airFareInfoList);
        $airAirPricePointList = $result['airAirPricePointList']['airAirPricePoint'];

        //print_r($airAirPricePointList);

        $flightList = array();
        $airAirSegment = array();
        $airFareInfo = array();
        $airList = array();

        if (isset($airFlightDetailsList[0])) {

            foreach ($airFlightDetailsList as $airFlightDetails) {
                $key = $airFlightDetails['@attributes']['Key'];
                $TravelTime = $airFlightDetails['@attributes']['TravelTime'];
                $Equipment = $airFlightDetails['@attributes']['Equipment'];
                $flightList[$key] = array('key' => "$key",
                    'TravelTime' => $TravelTime,
                    'Equipment' => $Equipment);
            }
        } else {
            $key = $airFlightDetailsList['@attributes']['Key'];
            $TravelTime = $airFlightDetailsList['@attributes']['TravelTime'];
            $Equipment = $airFlightDetailsList['@attributes']['Equipment'];
            $flightList[$key] = array('key' => "$key",
                'TravelTime' => $TravelTime,
                'Equipment' => $Equipment);
        }

        //print_r($flightList);

        if (isset($airFareInfoList[0])) {
            foreach ($airFareInfoList as $airFareInfos) {
                $key = $airFareInfos['@attributes']['Key'];
                $FareBasis = $airFareInfos['@attributes']['FareBasis'];

                if (isset($airFareInfos['airBaggageAllowance']['airNumberOfPieces'])) {
                    $Baggage = $airFareInfos['airBaggageAllowance']['airNumberOfPieces'];
                } else if ($airFareInfos['airBaggageAllowance']['airMaxWeight']) {
                    $Value = $airFareInfos['airBaggageAllowance']['airMaxWeight']['@attributes']['Value'];
                    $Unit = $airFareInfos['airBaggageAllowance']['airMaxWeight']['@attributes']['Unit'];
                    $Baggage = "$Value $Unit";
                } else {
                    $Baggage = "No Baggagge";
                }

                $airFareInfo[$key] = array('key' => $key,
                    'Bags' => $Baggage,
                    'FareBasisCode' => $FareBasis);
            }

        } else {
            $key = $airFareInfoList['@attributes']['Key'];
            $FareBasis = $airFareInfoList['@attributes']['FareBasis'];

            if (isset($airFareInfos['airBaggageAllowance']['airNumberOfPieces'])) {
                $Baggage = $airFareInfos['airBaggageAllowance']['airNumberOfPieces'];
            } else if (isset($airFareInfos['airBaggageAllowance']['airMaxWeight'])) {
                $Value = $airFareInfos['airBaggageAllowance']['airMaxWeight']['@attributes']['Value'];
                $Unit = $airFareInfos['airBaggageAllowance']['airMaxWeight']['@attributes']['Unit'];
                $Baggage = "$Value $Unit";
            } else {
                $Baggage = "No Baggagge";
            }

            $airFareInfo[$key] = array('key' => $key,
                'Bags' => $Baggage,
                'FareBasisCode' => $FareBasis);

        }

        if (isset($airAirSegmentList[0])) {
            foreach ($airAirSegmentList as $airSegment) {
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

                if (isset($airSegment['airFlightDetailsRef']['@attributes']['Key'])) {
                    $airFlightDetailsRef = $airSegment['airFlightDetailsRef']['@attributes']['Key'];
                    $TravelTime = $flightList[$airFlightDetailsRef]['TravelTime'];
                } else {
                    $TravelTime = 0;
                }

                $airAirSegment[$key] = array(
                    'key' => "$key",
                    'Carrier' => $Carrier,
                    'Origin' => "$Origin",
                    'Destination' => $Destination,
                    'DepartureTime' => "$DepartureTime",
                    'ArrivalTime' => $ArrivalTime,
                    'FlightNumber' => $FlightNumber,
                    'FlightTime' => $FlightTime,
                    'TravelTime' => $TravelTime,
                    'AvailabilitySource' => $AvailabilitySource,
                    'Distance' => $Distance,
                    'Equipment' => $Equipment,
                    'ParticipantLevel' => $ParticipantLevel,
                    'PolledAvailabilityOption' => $PolledAvailabilityOption,
                    'ChangeOfPlane' => $ChangeOfPlane,
                    'Group' => $Group,
                    'AvailabilityDisplayType' => $AvailabilityDisplayType,

                );
            }
        } else {
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

            if (isset($airAirSegmentList['airFlightDetailsRef']['@attributes']['Key'])) {
                $airFlightDetailsRef = $airAirSegmentList['airFlightDetailsRef']['@attributes']['Key'];
                $TravelTime = $flightList[$airFlightDetailsRef]['TravelTime'];
            } else {
                $TravelTime = 0;
            }

            $airAirSegment[$key] = array(
                'key' => "$key",
                'Carrier' => $Carrier,
                'Origin' => "$Origin",
                'Destination' => $Destination,
                'DepartureTime' => "$DepartureTime",
                'ArrivalTime' => $ArrivalTime,
                'FlightNumber' => $FlightNumber,
                'FlightTime' => $FlightTime,
                'TravelTime' => $TravelTime,
                'AvailabilitySource' => $AvailabilitySource,
                'Distance' => $Distance,
                'Equipment' => $Equipment,
                'ParticipantLevel' => $ParticipantLevel,
                'PolledAvailabilityOption' => $PolledAvailabilityOption,
                'ChangeOfPlane' => $ChangeOfPlane,
                'Group' => $Group,
                'AvailabilityDisplayType' => $AvailabilityDisplayType,

            );

        }

        //print_r($airAirSegment);

        if (isset($airAirPricePointList[0])) {

            foreach ($airAirPricePointList as $airAirPricePoint) {
                if ($adult > 0 && $child == 0) {
                    $airPricePointOptions = $airAirPricePoint['airAirPricingInfo'];
                } else {
                    $airPricePointOptions = $airAirPricePoint['airAirPricingInfo'][0];
                }

                $airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption']['airOption'];

                $key = $airAirPricePoint['@attributes']['Key'];
                $TotalPrice = $airAirPricePoint['@attributes']['TotalPrice'];
                $validatingCarrierCode = $airPricePointOptions['@attributes']['PlatingCarrier'];
                $Exact = (int) filter_var($TotalPrice, FILTER_SANITIZE_NUMBER_INT);

                if (isset($airAirPricePoint['@attributes']['EquivalentBasePrice'])) {
                    $BasePrice = (int) filter_var($airAirPricePoint['@attributes']['EquivalentBasePrice'], FILTER_SANITIZE_NUMBER_INT);
                } else {
                    $BasePrice = (int) filter_var($airAirPricePoint['@attributes']['BasePrice'], FILTER_SANITIZE_NUMBER_INT);
                }

                $Taxes = $airAirPricePoint['@attributes']['Taxes'];
                $LatestTicketingTime = $airPricePointOptions['@attributes']['LatestTicketingTime'];

                if (isset($airPricePointOptions['airFareInfoRef'][0])) {
                    $airFareInfoRef = $airPricePointOptions['airFareInfoRef'][0];
                } else {
                    $airFareInfoRef = $airPricePointOptions['airFareInfoRef']['@attributes']['Key'];
                }

                $airFareCalc = $airPricePointOptions['airFareCalc'];

                if (isset($airPricePointOptions['airChangePenalty']['airAmount']) == true) {
                    $airChangePenalty = $airPricePointOptions['airChangePenalty']['airAmount'];
                } else if (isset($airPricePointOptions['airChangePenalty']['airPercentage']) == true) {
                    $airChangePenalty = $airPricePointOptions['airChangePenalty']['airPercentage'];
                } else {
                    //print_r($airPricePointOptions['airChangePenalty']);
                }

                if (isset($airPricePointOptions['airCancelPenalty']['airAmount']) == true) {
                    $airCancelPenalty = $airPricePointOptions['airCancelPenalty']['airAmount'];
                } else if (isset($airPricePointOptions['airCancelPenalty']['airPercentage']) == true) {
                    $airCancelPenalty = $airPricePointOptions['airCancelPenalty']['airPercentage'];
                } else {
                    //print_r($airPricePointOptions['airCancelPenalty']);
                }

                if (isset($airPricePointOptions['@attributes']['Refundable'])) {
                    $Refundable = "Refundable";
                } else {
                    $Refundable = "Nonrefundable";

                }

                $From = $result['airRouteList']['airRoute']['airLeg']['@attributes']['Origin'];
                $To = $result['airRouteList']['airRoute']['airLeg']['@attributes']['Destination'];

                $sql = mysqli_query($conn, "SELECT nameBangla, name FROM airlines WHERE code='$validatingCarrierCode'");
                $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

                if (!empty($row)) {
                    $CarrieerName = $row['name'];
                }

                if (isset($airPricePoint[0]) == true) {
                    $itemcount = count($airPricePoint);

                    if (isset($airPricePoint[0]) == true) {

                        if (isset($airPricePoint[0]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == false) {
                            $FareInfoRef = $airPricePoint[0]['airBookingInfo']['@attributes']['FareInfoRef'];
                            $SegmentRef = $airPricePoint[0]['airBookingInfo']['@attributes']['SegmentRef'];
                            $Bags = $airFareInfo[$FareInfoRef]['Bags'];
                            $TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];
                            $FareBasisCode = $airFareInfo[$FareInfoRef]['FareBasisCode'];
                            $AvailabilitySource = $airAirSegment[$SegmentRef]['AvailabilitySource'];
                            $Distance = $airAirSegment[$SegmentRef]['Distance'];
                            $Equipment = $airAirSegment[$SegmentRef]['Equipment'];
                            $ParticipantLevel = $airAirSegment[$SegmentRef]['ParticipantLevel'];
                            $PolledAvailabilityOption = $airAirSegment[$SegmentRef]['PolledAvailabilityOption'];
                            $Group = $airAirSegment[$SegmentRef]['Group'];
                            $ChangeOfPlane = $airAirSegment[$SegmentRef]['ChangeOfPlane'];
                            $AvailabilityDisplayType = $airAirSegment[$SegmentRef]['AvailabilityDisplayType'];
                            $TravelTimeHm = floor($TravelTime / 60) . "H " . ($TravelTime - ((floor($TravelTime / 60)) * 60)) . "Min";

                            $ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
                            $DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

                            $ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
                            $DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

                            $dpTime = date("D d M Y", strtotime($DepartureTime));
                            $arrTime = date("D d M Y", strtotime($ArrivalTime));

                            $markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
                            $markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

                            $sql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                            $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

                            if (!empty($row)) {
                                $markettingCarrierName = $row['name'];
                            }

                            // Departure Country
                            $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                            $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport = $row1['name'];
                                $dCity = $row1['cityName'];
                                $dCountry = $row1['countryCode'];
                            }

                            // Departure Country
                            $sql2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                            $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport = $row2['name'];
                                $aCity = $row2['cityName'];
                                $aCountry = $row2['countryCode'];
                            }

                            $BookingCode = $airPricePoint[0]['airBookingInfo']['@attributes']['BookingCode'];
                            $Seat = $airPricePoint[0]['airBookingInfo']['@attributes']['BookingCount'];
                            $CabinClass = $airPricePoint[0]['airBookingInfo']['@attributes']['CabinClass'];

                            $Transit = array("Transit" => "0");

                            $segment = array(
                                array("marketingcareer" => "$markettingCarrier",
                                    "marketingflight" => "$markettingFN",
                                    "operatingcareer" => "$markettingCarrier",
                                    "operatingflight" => "$markettingFN",
                                    "departure" => "$DepartureFrom",
                                    "departureAirport" => "$dAirport ",
                                    "departureLocation" => "$dCity , $dCountry",
                                    "departureTime" => "$DepartureTime",
                                    "arrival" => "$ArrivalTo",
                                    "arrivalTime" => "$ArrivalTime",
                                    "arrivalAirport" => "$aAirport",
                                    "arrivalLocation" => "$aCity , $aCountry",
                                    "flightduration" => "$TravelTimeHm",
                                    "bookingcode" => "$BookingCode",
                                    "seat" => $Seat,
                                    'CabinClass' => $CabinClass,
                                    'FareInfoRef' => $FareInfoRef,
                                    'SegmentRef' => $SegmentRef,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef],

                                ),
                            );

                            $basic = array("system" => "Galileo",
                                "segment" => "1",
                                "career" => $validatingCarrierCode,
                                "careerName" => $CarrieerName,
                                "BasePrice" => $BasePrice,
                                "Taxes" => $Taxes,
                                "price" => "$Exact",
                                "airChangePenalty " => $airChangePenalty,
                                "airCancelPenalty" => $airCancelPenalty,
                                "airFareCalc " => $airFareCalc,
                                "FareBasisCode" => $airFareInfo[$FareInfoRef]['FareBasisCode'],
                                "airFareInfoRef" => $airFareInfoRef,
                                "LatestTicketingTime" => $LatestTicketingTime,
                                "departure" => $From,
                                "departureTime" => substr($DepartureTime, 11, 5),
                                "departureDate" => $dpTime,
                                "arrival" => "$To",
                                "arrivalTime" => substr($ArrivalTime, 11, 5),
                                "arrivalDate" => $arrTime,
                                "flightduration" => "$TravelTimeHm",
                                "bags" => $Bags,
                                "seat" => $Seat,
                                "class" => $CabinClass,
                                "refundable" => $Refundable,
                                "segments" => $segment,
                                "traceid" => $TraceId,
                            );

                            array_push($All, $basic);

                        } else if (isset($airPricePoint[0]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == true) {
                            //Leg1

                            $FareInfoRef = $airPricePoint[0]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
                            $SegmentRef = $airPricePoint[0]['airBookingInfo'][0]['@attributes']['SegmentRef'];

                            $Bags = $airFareInfo[$FareInfoRef]['Bags'];
                            $TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];

                            $TravelTimeHm = floor($TravelTime / 60) . "H " . ($TravelTime - ((floor($TravelTime / 60)) * 60)) . "Min";

                            $FlightTime = $airAirSegment[$SegmentRef]['FlightTime'];
                            $FlightTimeHm = floor($FlightTime / 60) . "H " . ($FlightTime - ((floor($FlightTime / 60)) * 60)) . "Min";

                            $ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
                            $DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

                            $ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
                            $DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

                            $dpTime = date("D d M Y", strtotime($DepartureTime));
                            $arrTime = date("D d M Y", strtotime($ArrivalTime));

                            $markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
                            $markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

                            $sql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                            $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

                            if (!empty($row)) {
                                $markettingCarrierName = $row['name'];
                            }

                            // Departure Country
                            $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                            $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport = $row1['name'];
                                $dCity = $row1['cityName'];
                                $dCountry = $row1['countryCode'];
                            }

                            // Departure Country
                            $sql2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                            $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport = $row2['name'];
                                $aCity = $row2['cityName'];
                                $aCountry = $row2['countryCode'];
                            }

                            $BookingCode = $airPricePoint[0]['airBookingInfo'][0]['@attributes']['BookingCode'];
                            $Seat = $airPricePoint[0]['airBookingInfo'][0]['@attributes']['BookingCount'];
                            $CabinClass = $airPricePoint[0]['airBookingInfo'][0]['@attributes']['CabinClass'];

                            //Leg 2

                            $FareInfoRef1 = $airPricePoint[0]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
                            $SegmentRef1 = $airPricePoint[0]['airBookingInfo'][1]['@attributes']['SegmentRef'];

                            $FlightTime1 = $airAirSegment[$SegmentRef1]['FlightTime'];
                            $FlightTimeHm1 = floor($FlightTime1 / 60) . "H " . ($FlightTime1 - ((floor($FlightTime1 / 60)) * 60)) . "Min";

                            $ArrivalTo1 = $airAirSegment[$SegmentRef1]['Destination'];
                            $DepartureFrom1 = $airAirSegment[$SegmentRef1]['Origin'];

                            $ArrivalTime1 = $airAirSegment[$SegmentRef1]['ArrivalTime'];
                            $DepartureTime1 = $airAirSegment[$SegmentRef1]['DepartureTime'];

                            $dpTime1 = date("D d M Y", strtotime($DepartureTime1));
                            $arrTime1 = date("D d M Y", strtotime($ArrivalTime1));

                            $markettingCarrier1 = $airAirSegment[$SegmentRef1]['Carrier'];
                            $markettingFN1 = $airAirSegment[$SegmentRef1]['FlightNumber'];

                            $sqlmk = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
                            $rowmk = mysqli_fetch_array($sqlmk, MYSQLI_ASSOC);

                            if (!empty($rowmk1)) {
                                $markettingCarrierName1 = $rowmk1['name'];
                            }

                            // Departure Country
                            $sqldp1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom1' ");
                            $rowdp1 = mysqli_fetch_array($sqldp1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport1 = $rowdp1['name'];
                                $dCity1 = $rowdp1['cityName'];
                                $dCountry1 = $rowdp1['countryCode'];
                            }

                            // Departure Country
                            $sqlar2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo1' ");
                            $rowar2 = mysqli_fetch_array($sqlar2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport1 = $rowar2['name'];
                                $aCity1 = $rowar2['cityName'];
                                $aCountry1 = $rowar2['countryCode'];
                            }

                            $BookingCode1 = $airPricePoint[0]['airBookingInfo'][1]['@attributes']['BookingCode'];
                            $Seat1 = $airPricePoint[0]['airBookingInfo'][1]['@attributes']['BookingCount'];
                            $CabinClass1 = $airPricePoint[0]['airBookingInfo'][1]['@attributes']['CabinClass'];

                            $Transits = $TravelTime - ($FlightTime + $FlightTime1);
                            $TransitHm = floor($Transits / 60) . "H " . ($Transits - ((floor($Transits / 60)) * 60)) . "Min";

                            $Transit = array("transit" => $TransitHm);

                            $segment = array(
                                array("marketingcareer" => "$markettingCarrier",
                                    "marketingflight" => "$markettingFN",
                                    "operatingcareer" => "$markettingCarrier",
                                    "operatingflight" => "$markettingFN",
                                    "departure" => "$DepartureFrom",
                                    "departureAirport" => "$dAirport ",
                                    "departureLocation" => "$dCity , $dCountry",
                                    "departureTime" => "$dpTime",
                                    "arrival" => "$ArrivalTo",
                                    "arrivalTime" => "$ArrivalTime1",
                                    "arrivalAirport" => "$aAirport",
                                    "arrivalLocation" => "$aCity , $aCountry",
                                    "flightduration" => $FlightTimeHm,
                                    "bookingcode" => "$BookingCode",
                                    "seat" => $Seat,
                                    'CabinClass' => $CabinClass,
                                    'FareInfoRef' => $FareInfoRef,
                                    'SegmentRef' => $SegmentRef,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef],

                                ), array("marketingcareer" => "$markettingCarrier1",
                                    "marketingflight" => "$markettingFN1",
                                    "operatingcareer" => "$markettingCarrier1",
                                    "operatingflight" => "$markettingFN1",
                                    "departure" => "$DepartureFrom1",
                                    "departureAirport" => "$dAirport1",
                                    "departureLocation" => "$dCity1 , $dCountry",
                                    "departureTime" => "$dpTime",
                                    "arrival" => "$ArrivalTo1",
                                    "arrivalTime" => "$arrTime1",
                                    "arrivalAirport" => "$aAirport1",
                                    "arrivalLocation" => "$aCity1 , $aCountry1",
                                    "flightduration" => "$FlightTimeHm1",
                                    "bookingcode" => "$BookingCode1",
                                    "seat" => "$Seat1",
                                    'CabinClass' => $CabinClass1,
                                    'FareInfoRef' => $FareInfoRef1,
                                    'SegmentRef' => $SegmentRef1,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef1],

                                ),
                            );
                            $basic = array("system" => "Galileo",
                                "segment" => "2",
                                "career" => $validatingCarrierCode,
                                "careerName" => "$CarrieerName",
                                "BasePrice" => $BasePrice,
                                "Taxes" => $Taxes,
                                "price" => "$Exact",
                                "airChangePenalty " => $airChangePenalty,
                                "airCancelPenalty" => $airCancelPenalty,
                                "airFareCalc " => $airFareCalc,
                                "FareBasisCode" => $airFareInfo[$FareInfoRef]['FareBasisCode'],
                                "airFareInfoRef" => $airFareInfoRef,
                                "LatestTicketingTime" => $LatestTicketingTime,
                                "departure" => "$From",
                                "departureTime" => substr($DepartureTime, 11, 5),
                                "departureDate" => "$dpTime",
                                "arrival" => "$To",
                                "arrivalTime" => substr($ArrivalTime1, 11, 5),
                                "arrivalDate" => "$arrTime1",
                                "flightduration" => $TravelTimeHm,
                                "bags" => $Bags,
                                "seat" => "$Seat",
                                "class" => "$CabinClass",
                                "refundable" => $Refundable,
                                "segments" => $segment,
                                "transit" => $Transit,
                                "traceid" => $TraceId,
                            );
                            array_push($All, $basic);
                        }

                    }
                    if (isset($airPricePoint[1]) == true) {
                        if (isset($airPricePoint[1]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == false) {
                            $FareInfoRef = $airPricePoint[1]['airBookingInfo']['@attributes']['FareInfoRef'];
                            $SegmentRef = $airPricePoint[1]['airBookingInfo']['@attributes']['SegmentRef'];
                            $Bags = $airFareInfo[$FareInfoRef]['Bags'];
                            $TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];
                            $TravelTimeHm = floor($TravelTime / 60) . "H " . ($TravelTime - ((floor($TravelTime / 60)) * 60)) . "Min";

                            $FlightTime = $airAirSegment[$SegmentRef]['FlightTime'];
                            $FlightTimeHm = floor($FlightTime / 60) . "H " . ($FlightTime - ((floor($FlightTime / 60)) * 60)) . "Min";

                            $ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
                            $DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

                            $ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
                            $DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

                            $dpTime = date("D d M Y", strtotime($DepartureTime));
                            $arrTime = date("D d M Y", strtotime($ArrivalTime));

                            $markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
                            $markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

                            $sql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                            $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

                            if (!empty($row)) {
                                $markettingCarrierName = $row['name'];
                            }

                            // Departure Country
                            $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                            $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport = $row1['name'];
                                $dCity = $row1['cityName'];
                                $dCountry = $row1['countryCode'];
                            }

                            // Departure Country
                            $sql2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                            $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport = $row2['name'];
                                $aCity = $row2['cityName'];
                                $aCountry = $row2['countryCode'];
                            }

                            $BookingCode = $airPricePoint[1]['airBookingInfo']['@attributes']['BookingCode'];
                            $Seat = $airPricePoint[1]['airBookingInfo']['@attributes']['BookingCount'];
                            $CabinClass = $airPricePoint[1]['airBookingInfo']['@attributes']['CabinClass'];

                            $Transit = array("Transit" => "0");
                            $segment = array(
                                array("marketingcareer" => "$markettingCarrier",
                                    "marketingflight" => "$markettingFN",
                                    "operatingcareer" => "$markettingCarrier",
                                    "operatingflight" => "$markettingFN",
                                    "departure" => "$DepartureFrom",
                                    "departureAirport" => "$dAirport ",
                                    "departureLocation" => "$dCity , $dCountry",
                                    "departureTime" => $DepartureTime,
                                    "arrival" => "$ArrivalTo",
                                    "arrivalTime" => "$ArrivalTime",
                                    "arrivalAirport" => "$aAirport",
                                    "arrivalLocation" => "$aCity , $aCountry",
                                    "flightduration" => "$TravelTimeHm",
                                    "bookingcode" => "$BookingCode",
                                    "seat" => "$Seat",
                                    'CabinClass' => $CabinClass,
                                    'FareInfoRef' => $FareInfoRef,
                                    'SegmentRef' => $SegmentRef,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef],

                                ),
                            );

                            $basic = array("system" => "Galileo",
                                "segment" => "1",
                                "career" => $validatingCarrierCode,
                                "careerName" => $CarrieerName,
                                "BasePrice" => $BasePrice,
                                "Taxes" => $Taxes,
                                "price" => "$Exact",
                                "airChangePenalty " => $airChangePenalty,
                                "airCancelPenalty" => $airCancelPenalty,
                                "airFareCalc " => $airFareCalc,
                                "FareBasisCode" => $airFareInfo[$FareInfoRef]['FareBasisCode'],
                                "airFareInfoRef" => $airFareInfoRef,
                                "LatestTicketingTime" => $LatestTicketingTime,
                                "departure" => $From,
                                "departureTime" => substr($DepartureTime, 11, 5),
                                "departureDate" => $dpTime,
                                "arrival" => "$To",
                                "arrivalTime" => substr($ArrivalTime, 11, 5),
                                "arrivalDate" => $arrTime,
                                "flightduration" => $TravelTimeHm,
                                "bags" => $Bags,
                                "seat" => $Seat,
                                "class" => $CabinClass,
                                "refundable" => $Refundable,
                                "segments" => $segment,
                                "traceid" => $TraceId,
                            );

                            array_push($All, $basic);

                        } else if (isset($airPricePoint[1]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == true) {
                            //Leg1

                            $FareInfoRef = $airPricePoint[1]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
                            $SegmentRef = $airPricePoint[1]['airBookingInfo'][0]['@attributes']['SegmentRef'];

                            $Bags = $airFareInfo[$FareInfoRef]['Bags'];
                            $TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];
                            $TravelTimeHm = floor($TravelTime / 60) . "H " . ($TravelTime - ((floor($TravelTime / 60)) * 60)) . "Min";

                            $FlightTime = $airAirSegment[$SegmentRef]['FlightTime'];
                            $FlightTimeHm = floor($FlightTime / 60) . "H " . ($FlightTime - ((floor($FlightTime / 60)) * 60)) . "Min";

                            $ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
                            $DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

                            $ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
                            $departureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

                            $fromTime = substr($departureTime, 11, 19);
                            $dpTime = date("D d M Y", strtotime(substr($departureTime, 0, 10) . " " . $fromTime));

                            $toTime = substr($ArrivalTime, 11, 19);
                            $arrTime = date("D d M Y", strtotime(substr($departureTime, 0, 10) . " " . $toTime));

                            $markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
                            $markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

                            $sql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                            $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

                            if (!empty($row)) {
                                $markettingCarrierName = $row['name'];
                            }

                            // Departure Country
                            $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                            $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport = $row1['name'];
                                $dCity = $row1['cityName'];
                                $dCountry = $row1['countryCode'];
                            }

                            // Departure Country
                            $sql2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                            $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport = $row2['name'];
                                $aCity = $row2['cityName'];
                                $aCountry = $row2['countryCode'];
                            }

                            $BookingCode = $airPricePoint[1]['airBookingInfo'][0]['@attributes']['BookingCode'];
                            $Seat = $airPricePoint[1]['airBookingInfo'][0]['@attributes']['BookingCount'];
                            $CabinClass = $airPricePoint[1]['airBookingInfo'][0]['@attributes']['CabinClass'];

                            //Leg 2

                            $FareInfoRef1 = $airPricePoint[1]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
                            $SegmentRef1 = $airPricePoint[1]['airBookingInfo'][1]['@attributes']['SegmentRef'];

                            $FlightTime1 = $airAirSegment[$SegmentRef1]['FlightTime'];
                            $FlightTimeHm1 = floor($FlightTime1 / 60) . "H " . ($FlightTime1 - ((floor($FlightTime1 / 60)) * 60)) . "Min";

                            $ArrivalTo1 = $airAirSegment[$SegmentRef1]['Destination'];
                            $DepartureFrom1 = $airAirSegment[$SegmentRef1]['Origin'];

                            $ArrivalTime1 = $airAirSegment[$SegmentRef1]['ArrivalTime'];
                            $departureTime1 = $airAirSegment[$SegmentRef1]['DepartureTime'];

                            $fromTime1 = substr($departureTime1, 11, 19);
                            $dpTime1 = date("D d M Y", strtotime(substr($departureTime1, 0, 10) . " " . $fromTime1));

                            $toTime1 = substr($ArrivalTime1, 11, 19);
                            $arrTime1 = date("D d M Y", strtotime(substr($departureTime1, 0, 10) . " " . $toTime1));

                            $markettingCarrier1 = $airAirSegment[$SegmentRef1]['Carrier'];
                            $markettingFN1 = $airAirSegment[$SegmentRef1]['FlightNumber'];

                            $sqlmk = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
                            $rowmk = mysqli_fetch_array($sqlmk, MYSQLI_ASSOC);

                            if (!empty($rowmk1)) {
                                $markettingCarrierName1 = $rowmk1['name'];
                            }

                            // Departure Country
                            $sqldp1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom1' ");
                            $rowdp1 = mysqli_fetch_array($sqldp1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport1 = $rowdp1['name'];
                                $dCity1 = $rowdp1['cityName'];
                                $dCountry1 = $rowdp1['countryCode'];
                            }

                            // Departure Country
                            $sqlar2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo1' ");
                            $rowar2 = mysqli_fetch_array($sqlar2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport1 = $rowar2['name'];
                                $aCity1 = $rowar2['cityName'];
                                $aCountry1 = $rowar2['countryCode'];
                            }

                            $BookingCode1 = $airPricePoint[1]['airBookingInfo'][1]['@attributes']['BookingCode'];
                            $Seat1 = $airPricePoint[1]['airBookingInfo'][1]['@attributes']['BookingCount'];
                            $CabinClass1 = $airPricePoint[1]['airBookingInfo'][1]['@attributes']['CabinClass'];

                            $Transits = $TravelTime - ($FlightTime + $FlightTime1);
                            $TransitHm = floor($Transits / 60) . "H " . ($Transits - ((floor($Transits / 60)) * 60)) . "Min";
                            $Transit = array("transit1" => $TransitHm);

                            $segment = array(
                                array("marketingcareer" => "$markettingCarrier",
                                    "marketingflight" => "$markettingFN",
                                    "operatingcareer" => "$markettingCarrier",
                                    "operatingflight" => "$markettingFN",
                                    "departure" => "$DepartureFrom",
                                    "departureAirport" => "$dAirport ",
                                    "departureLocation" => "$dCity , $dCountry",
                                    "departureTime" => "$dpTime",
                                    "arrival" => "$ArrivalTo",
                                    "arrivalTime" => "$arrTime",
                                    "arrivalAirport" => "$aAirport",
                                    "arrivalLocation" => "$aCity , $aCountry",
                                    "flightduration" => "$FlightTimeHm",
                                    "bookingcode" => "$BookingCode",
                                    "seat" => "$Seat",
                                    'CabinClass' => $CabinClass,
                                    'FareInfoRef' => $FareInfoRef,
                                    'SegmentRef' => $SegmentRef,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef],

                                ), array("marketingcareer" => "$markettingCarrier1",
                                    "marketingflight" => "$markettingFN1",
                                    "operatingcareer" => "$markettingCarrier1",
                                    "operatingflight" => "$markettingFN1",
                                    "departure" => "$DepartureFrom1",
                                    "departureAirport" => "$dAirport1",
                                    "departureLocation" => "$dCity1 , $dCountry",
                                    "departureTime" => "$dpTime",
                                    "arrival" => "$ArrivalTo1",
                                    "arrivalTime" => "$arrTime1",
                                    "arrivalAirport" => "$aAirport1",
                                    "arrivalLocation" => "$aCity1 , $aCountry1",
                                    "flightduration" => "$FlightTimeHm1",
                                    "bookingcode" => "$BookingCode1",
                                    "seat" => "$Seat1",
                                    'CabinClass' => $CabinClass1,
                                    'FareInfoRef' => $FareInfoRef1,
                                    'SegmentRef' => $SegmentRef1,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef1],

                                ),
                            );
                            $basic = array("system" => "Galileo",
                                "segment" => "2",
                                "career" => $validatingCarrierCode,
                                "careerName" => "$CarrieerName",
                                "BasePrice" => $BasePrice,
                                "Taxes" => $Taxes,
                                "price" => "$Exact",
                                "airChangePenalty " => $airChangePenalty,
                                "airCancelPenalty" => $airCancelPenalty,
                                "airFareCalc " => $airFareCalc,
                                "FareBasisCode" => $airFareInfo[$FareInfoRef]['FareBasisCode'],
                                "airFareInfoRef" => $airFareInfoRef,
                                "LatestTicketingTime" => $LatestTicketingTime,
                                "departure" => "$From",
                                "departureTime" => "$DepartureTime",
                                "departureDate" => "$dpTime",
                                "arrival" => "$To",
                                "arrivalTime" => "$ArrivalTime1",
                                "arrivalDate" => "$arrTime1",
                                "flightduration" => $TravelTimeHm,

                                "bags" => $Bags,
                                "seat" => "$Seat",
                                "class" => "$CabinClass",
                                "refundable" => $Refundable,
                                "segments" => $segment,
                                "transit" => $Transit,
                                "traceid" => $TraceId,
                            );
                            array_push($All, $basic);
                        }
                    }
                    if (isset($airPricePoint[2]) == true) {
                        if (isset($airPricePoint[2]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == false) {
                            $FareInfoRef = $airPricePoint[2]['airBookingInfo']['@attributes']['FareInfoRef'];
                            $SegmentRef = $airPricePoint[2]['airBookingInfo']['@attributes']['SegmentRef'];
                            $Bags = $airFareInfo[$FareInfoRef]['Bags'];
                            $TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];
                            $TravelTimeHm = floor($TravelTime / 60) . "H " . ($TravelTime - ((floor($TravelTime / 60)) * 60)) . "Min";

                            $ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
                            $DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

                            $ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
                            $departureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

                            $fromTime = substr($departureTime, 11, 19);
                            $dpTime = date("D d M Y", strtotime(substr($departureTime, 0, 10) . " " . $fromTime));

                            $toTime = substr($ArrivalTime, 11, 19);
                            $arrTime = date("D d M Y", strtotime(substr($departureTime, 0, 10) . " " . $toTime));

                            $markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
                            $markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

                            $sql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                            $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

                            if (!empty($row)) {
                                $markettingCarrierName = $row['name'];
                            }

                            // Departure Country
                            $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                            $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport = $row1['name'];
                                $dCity = $row1['cityName'];
                                $dCountry = $row1['countryCode'];
                            }

                            // Departure Country
                            $sql2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                            $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport = $row2['name'];
                                $aCity = $row2['cityName'];
                                $aCountry = $row2['countryCode'];
                            }

                            $BookingCode = $airPricePoint[2]['airBookingInfo']['@attributes']['BookingCode'];
                            $Seat = $airPricePoint[2]['airBookingInfo']['@attributes']['BookingCount'];
                            $CabinClass = $airPricePoint[2]['airBookingInfo']['@attributes']['CabinClass'];

                            $Transit = array("Transit" => "0");

                            $segment = array(
                                array("marketingcareer" => "$markettingCarrier",
                                    "marketingflight" => "$markettingFN",
                                    "operatingcareer" => "$markettingCarrier",
                                    "operatingflight" => "$markettingFN",
                                    "departure" => "$DepartureFrom",
                                    "departureAirport" => "$dAirport ",
                                    "departureLocation" => "$dCity , $dCountry",
                                    "departureTime" => "$departureTime",
                                    "arrival" => "$ArrivalTo",
                                    "arrivalTime" => "$ArrivalTime",
                                    "arrivalAirport" => "$aAirport",
                                    "arrivalLocation" => "$aCity , $aCountry",
                                    "flightduration" => "$TravelTimeHm",
                                    "bookingcode" => "$BookingCode",
                                    "seat" => "$Seat",
                                    'CabinClass' => $CabinClass,
                                    'FareInfoRef' => $FareInfoRef,
                                    'SegmentRef' => $SegmentRef,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef],

                                ),
                            );

                            $basic = array("system" => "Galileo",
                                "segment" => "1",
                                "career" => $validatingCarrierCode,
                                "careerName" => $CarrieerName,
                                "BasePrice" => $BasePrice,
                                "Taxes" => $Taxes,
                                "price" => "$Exact",
                                "airChangePenalty " => $airChangePenalty,
                                "airCancelPenalty" => $airCancelPenalty,
                                "airFareCalc " => $airFareCalc,
                                "FareBasisCode" => $airFareInfo[$FareInfoRef]['FareBasisCode'],
                                "airFareInfoRef" => $airFareInfoRef,
                                "LatestTicketingTime" => $LatestTicketingTime,
                                "departure" => $From,
                                "departureTime" => substr($DepartureTime, 11, 5),
                                "departureDate" => $dpTime,
                                "arrival" => "$To",
                                "arrivalTime" => substr($ArrivalTime, 11, 5),
                                "arrivalDate" => $arrTime,
                                "flightduration" => $TravelTimeHm,

                                "bags" => $Bags,
                                "seat" => $Seat,
                                "class" => $CabinClass,
                                "refundable" => $Refundable,
                                "segments" => $segment,
                                "traceid" => $TraceId,
                            );

                            array_push($All, $basic);

                        } else if (isset($airPricePoint[2]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == true) {
                            //Leg1

                            $FareInfoRef = $airPricePoint[1]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
                            $SegmentRef = $airPricePoint[1]['airBookingInfo'][0]['@attributes']['SegmentRef'];

                            $Bags = $airFareInfo[$FareInfoRef]['Bags'];
                            $TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];
                            $TravelTimeHm = floor($TravelTime / 60) . "H " . ($TravelTime - ((floor($TravelTime / 60)) * 60)) . "Min";

                            $ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
                            $DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

                            $ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
                            $DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

                            $dpTime = date("D d M Y", strtotime($DepartureTime));
                            $arrTime = date("D d M Y", strtotime($ArrivalTime));

                            $markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
                            $markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

                            $sql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                            $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

                            if (!empty($row)) {
                                $markettingCarrierName = $row['name'];
                            }

                            // Departure Country
                            $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                            $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport = $row1['name'];
                                $dCity = $row1['cityName'];
                                $dCountry = $row1['countryCode'];
                            }

                            // Departure Country
                            $sql2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                            $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport = $row2['name'];
                                $aCity = $row2['cityName'];
                                $aCountry = $row2['countryCode'];
                            }

                            $BookingCode = $airPricePoint[2]['airBookingInfo'][0]['@attributes']['BookingCode'];
                            $Seat = $airPricePoint[2]['airBookingInfo'][0]['@attributes']['BookingCount'];
                            $CabinClass = $airPricePoint[2]['airBookingInfo'][0]['@attributes']['CabinClass'];

                            //Leg 2

                            $FareInfoRef1 = $airPricePoint[2]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
                            $SegmentRef1 = $airPricePoint[2]['airBookingInfo'][1]['@attributes']['SegmentRef'];

                            $FlightTime1 = $airAirSegment[$SegmentRef1]['FlightTime'];
                            $FlightTimeHm1 = floor($FlightTime1 / 60) . "H " . ($FlightTime1 - ((floor($FlightTime1 / 60)) * 60)) . "Min";

                            $ArrivalTo1 = $airAirSegment[$SegmentRef1]['Destination'];
                            $DepartureFrom1 = $airAirSegment[$SegmentRef1]['Origin'];

                            $ArrivalTime1 = $airAirSegment[$SegmentRef1]['ArrivalTime'];
                            $DepartureTime1 = $airAirSegment[$SegmentRef1]['DepartureTime'];

                            $dpTime1 = date("D d M Y", strtotime($DepartureTime));
                            $arrTime1 = date("D d M Y", strtotime($ArrivalTime1));

                            $markettingCarrier1 = $airAirSegment[$SegmentRef1]['Carrier'];
                            $markettingFN1 = $airAirSegment[$SegmentRef1]['FlightNumber'];

                            $sqlmk = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
                            $rowmk = mysqli_fetch_array($sqlmk, MYSQLI_ASSOC);

                            if (!empty($rowmk1)) {
                                $markettingCarrierName1 = $rowmk1['name'];
                            }

                            // Departure Country
                            $sqldp1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom1' ");
                            $rowdp1 = mysqli_fetch_array($sqldp1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport1 = $rowdp1['name'];
                                $dCity1 = $rowdp1['cityName'];
                                $dCountry1 = $rowdp1['countryCode'];
                            }

                            // Departure Country
                            $sqlar2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo1' ");
                            $rowar2 = mysqli_fetch_array($sqlar2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport1 = $rowar2['name'];
                                $aCity1 = $rowar2['cityName'];
                                $aCountry1 = $rowar2['countryCode'];
                            }

                            $BookingCode1 = $airPricePoint[2]['airBookingInfo'][1]['@attributes']['BookingCode'];
                            $Seat1 = $airPricePoint[2]['airBookingInfo'][1]['@attributes']['BookingCount'];
                            $CabinClass1 = $airPricePoint[2]['airBookingInfo'][1]['@attributes']['CabinClass'];

                            $Transits = $TravelTime - ($FlightTime + $FlightTime1);
                            $TransitHm = floor($Transits / 60) . "H " . ($Transits - ((floor($Transits / 60)) * 60)) . "Min";

                            $Transit = array("transit1" => $TransitHm);

                            $segment = array(
                                array("marketingcareer" => "$markettingCarrier",
                                    "marketingflight" => "$markettingFN",
                                    "operatingcareer" => "$markettingCarrier",
                                    "operatingflight" => "$markettingFN",
                                    "departure" => "$DepartureFrom",
                                    "departureAirport" => "$dAirport ",
                                    "departureLocation" => "$dCity , $dCountry",
                                    "departureTime" => $DepartureTime,
                                    "arrival" => "$ArrivalTo",
                                    "arrivalTime" => $ArrivalTime,
                                    "arrivalAirport" => "$aAirport",
                                    "arrivalLocation" => "$aCity , $aCountry",
                                    "flightduration" => "$FlightTimeHm",
                                    "bookingcode" => "$BookingCode",
                                    "seat" => "$Seat",
                                    'CabinClass' => $CabinClass,
                                    'FareInfoRef' => $FareInfoRef,
                                    'SegmentRef' => $SegmentRef,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef],

                                ), array("marketingcareer" => "$markettingCarrier1",
                                    "marketingflight" => "$markettingFN1",
                                    "operatingcareer" => "$markettingCarrier1",
                                    "operatingflight" => "$markettingFN1",
                                    "departure" => "$DepartureFrom1",
                                    "departureAirport" => "$dAirport1",
                                    "departureLocation" => "$dCity1 , $dCountry",
                                    "departureTime" => $DepartureTime1,
                                    "arrival" => $ArrivalTo1,
                                    "arrivalTime" => $ArrivalTime1,
                                    "arrivalAirport" => "$aAirport1",
                                    "arrivalLocation" => "$aCity1 , $aCountry1",
                                    "flightduration" => "$FlightTimeHm1",
                                    "bookingcode" => "$BookingCode1",
                                    "seat" => "$Seat1",
                                    'CabinClass' => $CabinClass1,
                                    'FareInfoRef' => $FareInfoRef1,
                                    'SegmentRef' => $SegmentRef1,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef1],

                                ),
                            );
                            $basic = array("system" => "Galileo",
                                "segment" => "2",
                                "career" => $validatingCarrierCode,
                                "careerName" => "$CarrieerName",
                                "BasePrice" => $BasePrice,
                                "Taxes" => $Taxes,
                                "price" => "$Exact",
                                "airChangePenalty " => $airChangePenalty,
                                "airCancelPenalty" => $airCancelPenalty,
                                "airFareCalc " => $airFareCalc,
                                "FareBasisCode" => $airFareInfo[$FareInfoRef]['FareBasisCode'],
                                "airFareInfoRef" => $airFareInfoRef,
                                "LatestTicketingTime" => $LatestTicketingTime,
                                "departure" => "$From",
                                "departureTime" => substr($DepartureTime, 11, 5),
                                "departureDate" => "$dpTime",
                                "arrival" => "$To",
                                "arrivalTime" => substr($ArrivalTime, 11, 5),
                                "arrivalDate" => "$arrTime1",
                                "flightduration" => $TravelTimeHm,

                                "bags" => $Bags,
                                "seat" => "$Seat",
                                "class" => "$CabinClass",
                                "refundable" => $Refundable,
                                "segments" => $segment,

                                "transit" => $Transit,
                                "traceid" => $TraceId,
                            );
                            array_push($All, $basic);
                        }
                    }
                    if (isset($airPricePoint[3]) == true) {
                        if (isset($airPricePoint[3]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == false) {
                            $FareInfoRef = $airPricePoint[3]['airBookingInfo']['@attributes']['FareInfoRef'];
                            $SegmentRef = $airPricePoint[3]['airBookingInfo']['@attributes']['SegmentRef'];
                            $Bags = $airFareInfo[$FareInfoRef]['Bags'];
                            $TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];
                            $TravelTimeHm = floor($TravelTime / 60) . "H " . ($TravelTime - ((floor($TravelTime / 60)) * 60)) . "Min";

                            $ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
                            $DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

                            $ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
                            $DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

                            $fromTime = substr($DepartureTime, 11, 19);
                            $dpTime = date("D d M Y", strtotime(substr($DepartureTime, 0, 10) . " " . $fromTime));

                            $toTime = substr($ArrivalTime, 11, 19);
                            $arrTime = date("D d M Y", strtotime(substr($ArrivalTime, 0, 10) . " " . $toTime));

                            $markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
                            $markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

                            $sql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                            $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

                            if (!empty($row)) {
                                $markettingCarrierName = $row['name'];
                            }

                            // Departure Country
                            $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                            $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport = $row1['name'];
                                $dCity = $row1['cityName'];
                                $dCountry = $row1['countryCode'];
                            }

                            // Departure Country
                            $sql2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                            $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport = $row2['name'];
                                $aCity = $row2['cityName'];
                                $aCountry = $row2['countryCode'];
                            }

                            $BookingCode = $airPricePoint[3]['airBookingInfo']['@attributes']['BookingCode'];
                            $Seat = $airPricePoint[3]['airBookingInfo']['@attributes']['BookingCount'];
                            $CabinClass = $airPricePoint[3]['airBookingInfo']['@attributes']['CabinClass'];
                            $Transit = array("transit" => "0");

                            $segment = array(
                                array("marketingcareer" => "$markettingCarrier",
                                    "marketingflight" => "$markettingFN",
                                    "operatingcareer" => "$markettingCarrier",
                                    "operatingflight" => "$markettingFN",
                                    "departure" => "$DepartureFrom",
                                    "departureAirport" => "$dAirport ",
                                    "departureLocation" => "$dCity , $dCountry",
                                    "departureTime" => "$DepartureTime",
                                    "arrival" => "$ArrivalTo",
                                    "arrivalTime" => "$ArrivalTime",
                                    "arrivalAirport" => "$aAirport",
                                    "arrivalLocation" => "$aCity , $aCountry",
                                    "flightduration" => "$TravelTimeHm",
                                    "bookingcode" => "$BookingCode",
                                    "seat" => "$Seat",
                                    'CabinClass' => $CabinClass,
                                    'FareInfoRef' => $FareInfoRef,
                                    'SegmentRef' => $SegmentRef,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef],

                                ),
                            );

                            $basic = array("system" => "Galileo",
                                "segment" => "1",
                                "career" => $validatingCarrierCode,
                                "careerName" => $CarrieerName,
                                "BasePrice" => $BasePrice,
                                "Taxes" => $Taxes,
                                "price" => "$Exact",
                                "airChangePenalty " => $airChangePenalty,
                                "airCancelPenalty" => $airCancelPenalty,
                                "airFareCalc " => $airFareCalc,
                                "FareBasisCode" => $airFareInfo[$FareInfoRef]['FareBasisCode'],
                                "airFareInfoRef" => $airFareInfoRef,
                                "LatestTicketingTime" => $LatestTicketingTime,
                                "departure" => $From,
                                "departureTime" => substr($DepartureTime, 11, 5),
                                "departureDate" => $dpTime,
                                "arrival" => $To,
                                "arrivalTime" => substr($ArrivalTime, 11, 5),
                                "arrivalDate" => $arrTime,
                                "flightduration" => $TravelTimeHm,
                                "bags" => $Bags,
                                "seat" => $Seat,
                                "class" => $CabinClass,
                                "refundable" => $Refundable,
                                "segments" => $segment,
                                "traceid" => $TraceId,
                            );

                            array_push($All, $basic);

                        } else if (isset($airPricePoint[3]['airBookingInfo'][0]['@attributes']['FareInfoRef']) == true) {
                            //Leg1

                            $FareInfoRef = $airPricePoint[3]['airBookingInfo'][0]['@attributes']['FareInfoRef'];
                            $SegmentRef = $airPricePoint[3]['airBookingInfo'][0]['@attributes']['SegmentRef'];

                            $Bags = $airFareInfo[$FareInfoRef]['Bags'];
                            $TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];
                            $TravelTimeHm = floor($TravelTime / 60) . "H " . ($TravelTime - ((floor($TravelTime / 60)) * 60)) . "Min";

                            $ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
                            $DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

                            $ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
                            $DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

                            $fromTime = substr($departureTime, 11, 19);
                            $dpTime = date("D d M Y", strtotime(substr($DepartureTime, 0, 10) . " " . $fromTime));

                            $toTime = substr($ArrivalTime, 11, 19);
                            $arrTime = date("D d M Y", strtotime(substr($ArrivalTime, 0, 10) . " " . $toTime));

                            $markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
                            $markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

                            $sql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                            $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

                            if (!empty($row)) {
                                $markettingCarrierName = $row['name'];
                            }

                            // Departure Country
                            $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                            $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport = $row1['name'];
                                $dCity = $row1['cityName'];
                                $dCountry = $row1['countryCode'];
                            }

                            // Departure Country
                            $sql2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                            $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport = $row2['name'];
                                $aCity = $row2['cityName'];
                                $aCountry = $row2['countryCode'];
                            }

                            $BookingCode = $airPricePoint[3]['airBookingInfo'][0]['@attributes']['BookingCode'];
                            $Seat = $airPricePoint[3]['airBookingInfo'][0]['@attributes']['BookingCount'];
                            $CabinClass = $airPricePoint[3]['airBookingInfo'][0]['@attributes']['CabinClass'];

                            //Leg 2

                            $FareInfoRef1 = $airPricePoint[3]['airBookingInfo'][1]['@attributes']['FareInfoRef'];
                            $SegmentRef1 = $airPricePoint[3]['airBookingInfo'][1]['@attributes']['SegmentRef'];

                            $FlightTime1 = $airAirSegment[$SegmentRef1]['FlightTime'];
                            $FlightTimeHm1 = floor($FlightTime1 / 60) . "H " . ($FlightTime1 - ((floor($FlightTime1 / 60)) * 60)) . "Min";

                            $ArrivalTo1 = $airAirSegment[$SegmentRef1]['Destination'];
                            $DepartureFrom1 = $airAirSegment[$SegmentRef1]['Origin'];

                            $ArrivalTime1 = $airAirSegment[$SegmentRef1]['ArrivalTime'];
                            $DepartureTime1 = $airAirSegment[$SegmentRef1]['DepartureTime'];

                            $fromTime1 = substr($DepartureTime1, 11, 19);
                            $dpTime1 = date("D d M Y", strtotime($DepartureTime1));

                            $toTime1 = substr($ArrivalTime1, 11, 19);
                            $arrTime1 = date("D d M Y", strtotime($ArrivalTime1));

                            $markettingCarrier1 = $airAirSegment[$SegmentRef1]['Carrier'];
                            $markettingFN1 = $airAirSegment[$SegmentRef1]['FlightNumber'];

                            $sqlmk = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
                            $rowmk = mysqli_fetch_array($sqlmk, MYSQLI_ASSOC);

                            if (!empty($rowmk1)) {
                                $markettingCarrierName1 = $rowmk1['name'];
                            }

                            // Departure Country
                            $sqldp1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom1' ");
                            $rowdp1 = mysqli_fetch_array($sqldp1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport1 = $rowdp1['name'];
                                $dCity1 = $rowdp1['cityName'];
                                $dCountry1 = $rowdp1['countryCode'];
                            }

                            // Departure Country
                            $sqlar2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo1' ");
                            $rowar2 = mysqli_fetch_array($sqlar2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport1 = $rowar2['name'];
                                $aCity1 = $rowar2['cityName'];
                                $aCountry1 = $rowar2['countryCode'];
                            }

                            $BookingCode1 = $airPricePoint[3]['airBookingInfo'][1]['@attributes']['BookingCode'];
                            $Seat1 = $airPricePoint[3]['airBookingInfo'][1]['@attributes']['BookingCount'];
                            $CabinClass1 = $airPricePoint[3]['airBookingInfo'][1]['@attributes']['CabinClass'];

                            $Transits = $TravelTime - ($FlightTime + $FlightTime1);
                            $TransitHm = floor($Transits / 60) . "H " . ($Transits - ((floor($Transits / 60)) * 60)) . "Min";
                            $Transit = array("transit1" => $TransitHm);

                            $segment = array(
                                array("marketingcareer" => "$markettingCarrier",
                                    "marketingflight" => "$markettingFN",
                                    "operatingcareer" => "$markettingCarrier",
                                    "operatingflight" => "$markettingFN",
                                    "departure" => "$DepartureFrom",
                                    "departureAirport" => "$dAirport ",
                                    "departureLocation" => "$dCity , $dCountry",
                                    "departureTime" => $DepartureTime,
                                    "arrival" => "$ArrivalTo",
                                    "arrivalTime" => $ArrivalTime,
                                    "arrivalAirport" => "$aAirport",
                                    "arrivalLocation" => "$aCity , $aCountry",
                                    "flightduration" => "$FlightTimeHm",
                                    "bookingcode" => "$BookingCode",
                                    "seat" => "$Seat",
                                    'CabinClass' => $CabinClass,
                                    'FareInfoRef' => $FareInfoRef,
                                    'SegmentRef' => $SegmentRef,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef],

                                ), array("marketingcareer" => "$markettingCarrier1",
                                    "marketingflight" => "$markettingFN1",
                                    "operatingcareer" => "$markettingCarrier1",
                                    "operatingflight" => "$markettingFN1",
                                    "departure" => "$DepartureFrom1",
                                    "departureAirport" => "$dAirport1",
                                    "departureLocation" => "$dCity1 , $dCountry",
                                    "departureTime" => $DepartureTime1,
                                    "arrival" => "$ArrivalTo1",
                                    "arrivalTime" => $ArrivalTime1,
                                    "arrivalAirport" => "$aAirport1",
                                    "arrivalLocation" => "$aCity1 , $aCountry1",
                                    "flightduration" => "$FlightTimeHm1",
                                    "bookingcode" => "$BookingCode1",
                                    "seat" => "$Seat1",
                                    'CabinClass' => $CabinClass1,
                                    'FareInfoRef' => $FareInfoRef1,
                                    'SegmentRef' => $SegmentRef1,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef1],

                                ),
                            );
                            $basic = array("system" => "Galileo",
                                "segment" => "2",
                                "career" => $validatingCarrierCode,
                                "careerName" => "$CarrieerName",
                                "BasePrice" => $BasePrice,
                                "Taxes" => $Taxes,
                                "price" => "$Exact",
                                "airChangePenalty " => $airChangePenalty,
                                "airCancelPenalty" => $airCancelPenalty,
                                "airFareCalc " => $airFareCalc,
                                "FareBasisCode" => $airFareInfo[$FareInfoRef]['FareBasisCode'],
                                "airFareInfoRef" => $airFareInfoRef,
                                "LatestTicketingTime" => $LatestTicketingTime,
                                "departure" => "$From",
                                "departureTime" => "$fromTime",
                                "departureDate" => "$dpTime",
                                "arrival" => "$To",
                                "arrivalTime" => "$toTime1",
                                "arrivalDate" => "$arrTime1",
                                "flightduration" => $TravelTimeHm,
                                "bags" => $Bags,
                                "seat" => "$Seat",
                                "class" => "$CabinClass",
                                "refundable" => $Refundable,
                                "segments" => $segment,

                                "transit" => $Transit,
                                "traceid" => $TraceId,
                            );
                            array_push($All, $basic);
                        }
                    }

                } else if (isset($airPricePoint['airBookingInfo']) == true) {
                    if (isset($airPricePoint['airBookingInfo'][0]) == true) {
                        $sgcount = count($airPricePoint['airBookingInfo']);
                        if ($sgcount == 1) {

                            $FareInfoRef = $airPricePoint['airBookingInfo'][0]['@attributes']['FareInfoRef'];
                            $SegmentRef = $airPricePoint['airBookingInfo'][0]['@attributes']['SegmentRef'];
                            $Bags = $airFareInfo[$FareInfoRef]['Bags'];
                            $TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];
                            $TravelTimeHm = floor($TravelTime / 60) . "H " . ($TravelTime - ((floor($TravelTime / 60)) * 60)) . "Min";

                            $FlightTime = $airAirSegment[$SegmentRef]['FlightTime'];
                            $FlightTimeHm = floor($FlightTime / 60) . "H " . ($FlightTime - ((floor($FlightTime / 60)) * 60)) . "Min";

                            $ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
                            $DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

                            $ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
                            $DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

                            $dpTime = date("D d M Y", strtotime($DepartureTime));
                            $arrTime = date("D d M Y", strtotime($ArrivalTime));

                            $markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
                            $markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

                            $sql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                            $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

                            if (!empty($row)) {
                                $markettingCarrierName = $row['name'];
                            }

                            // Departure Country
                            $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                            $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport = $row1['name'];
                                $dCity = $row1['cityName'];
                                $dCountry = $row1['countryCode'];
                            }

                            // Departure Country
                            $sql2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                            $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport = $row2['name'];
                                $aCity = $row2['cityName'];
                                $aCountry = $row2['countryCode'];
                            }

                            $BookingCode = $airPricePoint['airBookingInfo'][0]['@attributes']['BookingCode'];
                            $Seat = $airPricePoint['airBookingInfo'][0]['@attributes']['BookingCount'];
                            $CabinClass = $airPricePoint['airBookingInfo'][0]['@attributes']['CabinClass'];

                            $segment = array(
                                array("marketingcareer" => "$markettingCarrier",
                                    "marketingflight" => "$markettingFN",
                                    "operatingcareer" => "$markettingCarrier",
                                    "operatingflight" => "$markettingFN",
                                    "departure" => "$DepartureFrom",
                                    "departureAirport" => "$dAirport ",
                                    "departureLocation" => "$dCity , $dCountry",
                                    "departureTime" => "$dpTime",
                                    "arrival" => "$ArrivalTo",
                                    "arrivalTime" => "$ArrivalTime",
                                    "arrivalAirport" => "$aAirport",
                                    "arrivalLocation" => "$aCity , $aCountry",
                                    "flightduration" => "$TravelTimeHm",
                                    "bookingcode" => "$BookingCode",
                                    "seat" => $Seat,
                                    'CabinClass' => $CabinClass,
                                    'FareInfoRef' => $FareInfoRef,
                                    'SegmentRef' => $SegmentRef,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef],

                                ),
                            );
                            $basic = array("system" => "Galileo",
                                "segment" => "$sgcount",
                                "career" => $validatingCarrierCode,
                                "careerName" => $CarrieerName,
                                "BasePrice" => $BasePrice,
                                "Taxes" => $Taxes,
                                "price" => "$Exact",
                                "airChangePenalty " => $airChangePenalty,
                                "airCancelPenalty" => $airCancelPenalty,
                                "airFareCalc " => $airFareCalc,
                                "FareBasisCode" => $airFareInfo[$FareInfoRef]['FareBasisCode'],
                                "airFareInfoRef" => $airFareInfoRef,
                                "LatestTicketingTime" => $LatestTicketingTime,
                                "departure" => $From,
                                "departureDate" => $dpTime,
                                "departureTime" => substr($DepartureTime, 11, 5),
                                "arrival" => "$To",
                                "arrivalTime" => substr($ArrivalTime, 11, 5),
                                "arrivalDate" => "$arrTime",
                                "flightduration" => $TravelTimeHm,
                                "bags" => $Bag,
                                "seat" => $Seat,
                                "class" => $CabinClass,
                                "refundable" => $Refundable,
                                "segments" => $segment,
                                "traceid" => $TraceId,
                            );

                        } else if ($sgcount == 2) {
                            //Leg1

                            $FareInfoRef = $airPricePoint['airBookingInfo'][0]['@attributes']['FareInfoRef'];
                            $SegmentRef = $airPricePoint['airBookingInfo'][0]['@attributes']['SegmentRef'];

                            $Bags = $airFareInfo[$FareInfoRef]['Bags'];
                            $TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];
                            $TravelTimeHm = floor($TravelTime / 60) . "H " . ($TravelTime - ((floor($TravelTime / 60)) * 60)) . "Min";

                            $FlightTime = $airAirSegment[$SegmentRef]['FlightTime'];
                            $FlightTimeHm = floor($FlightTime / 60) . "H " . ($FlightTime - ((floor($FlightTime / 60)) * 60)) . "Min";

                            $ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
                            $DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

                            $ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
                            $DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

                            $fromTime = substr($DepartureTime, 11, 19);
                            $dpTime = date("D d M Y", strtotime(substr($DepartureTime, 0, 10) . " " . $fromTime));

                            $toTime = substr($ArrivalTime, 11, 19);
                            $arrTime = date("D d M Y", strtotime(substr($ArrivalTime, 0, 10) . " " . $toTime));

                            $markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
                            $markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

                            $sql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                            $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

                            if (!empty($row)) {
                                $markettingCarrierName = $row['name'];
                            }

                            // Departure Country
                            $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                            $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport = $row1['name'];
                                $dCity = $row1['cityName'];
                                $dCountry = $row1['countryCode'];
                            }

                            // Departure Country
                            $sql2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                            $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport = $row2['name'];
                                $aCity = $row2['cityName'];
                                $aCountry = $row2['countryCode'];
                            }

                            $BookingCode = $airPricePoint['airBookingInfo'][0]['@attributes']['BookingCode'];
                            $Seat = $airPricePoint['airBookingInfo'][0]['@attributes']['BookingCount'];
                            $CabinClass = $airPricePoint['airBookingInfo'][0]['@attributes']['CabinClass'];

                            //Leg 2

                            $FareInfoRef1 = $airPricePoint['airBookingInfo'][1]['@attributes']['FareInfoRef'];
                            $SegmentRef1 = $airPricePoint['airBookingInfo'][1]['@attributes']['SegmentRef'];

                            $FlightTime1 = $airAirSegment[$SegmentRef1]['FlightTime'];
                            $FlightTimeHm1 = floor($FlightTime1 / 60) . "H " . ($FlightTime1 - ((floor($FlightTime1 / 60)) * 60)) . "Min";

                            $ArrivalTo1 = $airAirSegment[$SegmentRef1]['Destination'];
                            $DepartureFrom1 = $airAirSegment[$SegmentRef1]['Origin'];

                            $ArrivalTime1 = $airAirSegment[$SegmentRef1]['ArrivalTime'];
                            $DepartureTime1 = $airAirSegment[$SegmentRef1]['DepartureTime'];

                            $fromTime1 = substr($DepartureTime1, 11, 19);
                            $dpTime1 = date("D d M Y", strtotime(substr($DepartureTime1, 0, 10) . " " . $fromTime1));

                            $toTime1 = substr($ArrivalTime1, 11, 19);
                            $arrTime1 = date("D d M Y", strtotime(substr($ArrivalTime1, 0, 10) . " " . $toTime1));

                            $markettingCarrier1 = $airAirSegment[$SegmentRef1]['Carrier'];
                            $markettingFN1 = $airAirSegment[$SegmentRef1]['FlightNumber'];

                            $sqlmk = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
                            $rowmk = mysqli_fetch_array($sqlmk, MYSQLI_ASSOC);

                            if (!empty($rowmk1)) {
                                $markettingCarrierName1 = $rowmk1['name'];
                            }

                            // Departure Country
                            $sqldp1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom1' ");
                            $rowdp1 = mysqli_fetch_array($sqldp1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport1 = $rowdp1['name'];
                                $dCity1 = $rowdp1['cityName'];
                                $dCountry1 = $rowdp1['countryCode'];
                            }

                            // Departure Country
                            $sqlar2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo1' ");
                            $rowar2 = mysqli_fetch_array($sqlar2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport1 = $rowar2['name'];
                                $aCity1 = $rowar2['cityName'];
                                $aCountry1 = $rowar2['countryCode'];
                            }

                            $BookingCode1 = $airPricePoint['airBookingInfo'][1]['@attributes']['BookingCode'];
                            $Seat1 = $airPricePoint['airBookingInfo'][1]['@attributes']['BookingCount'];
                            $CabinClass1 = $airPricePoint['airBookingInfo'][1]['@attributes']['CabinClass'];

                            $Transits = $TravelTime - ($FlightTime + $FlightTime1);
                            $TransitHm = floor($Transits / 60) . "H " . ($Transits - ((floor($Transits / 60)) * 60)) . "Min";

                            $Transit = array("transit1" => $TransitHm);

                            $segment = array(
                                array("marketingcareer" => "$markettingCarrier",
                                    "marketingflight" => "$markettingFN",
                                    "operatingcareer" => "$markettingCarrier",
                                    "operatingflight" => "$markettingFN",
                                    "departure" => "$DepartureFrom",
                                    "departureAirport" => "$dAirport ",
                                    "departureLocation" => "$dCity , $dCountry",
                                    "departureTime" => "$dpTime",
                                    "arrival" => "$ArrivalTo",
                                    "arrivalTime" => "$arrTime",
                                    "arrivalAirport" => "$aAirport",
                                    "arrivalLocation" => "$aCity , $aCountry",
                                    "flightduration" => "$FlightTimeHm",
                                    "bookingcode" => "$BookingCode",
                                    "seat" => "$Seat",
                                    'CabinClass' => $CabinClass,
                                    'FareInfoRef' => $FareInfoRef,
                                    'SegmentRef' => $SegmentRef,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef],

                                ), array("marketingcareer" => "$markettingCarrier1",
                                    "marketingflight" => "$markettingFN1",
                                    "operatingcareer" => "$markettingCarrier1",
                                    "operatingflight" => "$markettingFN1",
                                    "departure" => "$DepartureFrom1",
                                    "departureAirport" => "$dAirport1",
                                    "departureLocation" => "$dCity1 , $dCountry1",
                                    "departureTime" => "$dpTime",
                                    "arrival" => "$ArrivalTo1",
                                    "arrivalTime" => "$arrTime1",
                                    "arrivalAirport" => "$aAirport1",
                                    "arrivalLocation" => "$aCity1 , $aCountry1",
                                    "flightduration" => "$FlightTimeHm1",
                                    "bookingcode" => "$BookingCode1",
                                    "seat" => "$Seat1",
                                    'CabinClass' => $CabinClass1,
                                    'FareInfoRef' => $FareInfoRef1,
                                    'SegmentRef' => $SegmentRef1,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef1],

                                ),
                            );
                            $basic = array("system" => "Galileo",
                                "segment" => "2",
                                "career" => $validatingCarrierCode,
                                "careerName" => "$CarrieerName",
                                "BasePrice" => $BasePrice,
                                "Taxes" => $Taxes,
                                "price" => "$Exact",
                                "airChangePenalty " => $airChangePenalty,
                                "airCancelPenalty" => $airCancelPenalty,
                                "airFareCalc " => $airFareCalc,
                                "FareBasisCode" => $airFareInfo[$FareInfoRef]['FareBasisCode'],
                                "airFareInfoRef" => $airFareInfoRef,
                                "LatestTicketingTime" => $LatestTicketingTime,
                                "departure" => "$From",
                                "departureTime" => substr($DepartureTime, 11, 5),
                                "departureDate" => "$dpTime",
                                "arrival" => "$To",
                                "arrivalTime" => $ArrivalTime1,
                                "arrivalDate" => "$arrTime1",
                                "flightduration" => $TravelTimeHm,
                                "bags" => $Bags,
                                "seat" => "$Seat",
                                "class" => "$CabinClass",
                                "refundable" => $Refundable,
                                "segments" => $segment,
                                "transit" => $Transit,
                                "traceid" => $TraceId,
                            );

                        } else if ($sgcount == 3) {

                            //Leg1

                            $FareInfoRef = $airPricePoint['airBookingInfo'][0]['@attributes']['FareInfoRef'];
                            $SegmentRef = $airPricePoint['airBookingInfo'][0]['@attributes']['SegmentRef'];

                            $TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];
                            $TravelTimeHm = floor($TravelTime / 60) . "H " . ($TravelTime - ((floor($TravelTime / 60)) * 60)) . "Min";

                            $Bags = $airFareInfo[$FareInfoRef]['Bags'];

                            $FlightTime = $airAirSegment[$SegmentRef]['FlightTime'];
                            $FlightTimeHm = floor($FlightTime / 60) . "H " . ($FlightTime - ((floor($FlightTime / 60)) * 60)) . "Min";

                            $ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
                            $DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

                            $ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
                            $DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

                            $fromTime = substr($DepartureTime, 11, 19);
                            $dpTime = date("D d M Y", strtotime(substr($DepartureTime, 0, 10) . " " . $fromTime));

                            $toTime = substr($ArrivalTime, 11, 19);
                            $arrTime = date("D d M Y", strtotime(substr($ArrivalTime, 0, 10) . " " . $toTime));

                            $markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
                            $markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

                            $sql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                            $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

                            if (!empty($row)) {
                                $markettingCarrierName = $row['name'];
                            }

                            // Departure Country
                            $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                            $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport = $row1['name'];
                                $dCity = $row1['cityName'];
                                $dCountry = $row1['countryCode'];
                            }

                            // Departure Country
                            $sql2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                            $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport = $row2['name'];
                                $aCity = $row2['cityName'];
                                $aCountry = $row2['countryCode'];
                            }

                            $BookingCode = $airPricePoint['airBookingInfo'][0]['@attributes']['BookingCode'];
                            $Seat = $airPricePoint['airBookingInfo'][0]['@attributes']['BookingCount'];
                            $CabinClass = $airPricePoint['airBookingInfo'][0]['@attributes']['CabinClass'];

                            //Leg 2

                            $FareInfoRef1 = $airPricePoint['airBookingInfo'][1]['@attributes']['FareInfoRef'];
                            $SegmentRef1 = $airPricePoint['airBookingInfo'][1]['@attributes']['SegmentRef'];

                            $FlightTime1 = $airAirSegment[$SegmentRef1]['FlightTime'];
                            $FlightTimeHm1 = floor($FlightTime1 / 60) . "H " . ($FlightTime1 - ((floor($FlightTime1 / 60)) * 60)) . "Min";

                            $ArrivalTo1 = $airAirSegment[$SegmentRef1]['Destination'];
                            $DepartureFrom1 = $airAirSegment[$SegmentRef1]['Origin'];

                            $ArrivalTime1 = $airAirSegment[$SegmentRef1]['ArrivalTime'];
                            $DepartureTime1 = $airAirSegment[$SegmentRef1]['DepartureTime'];

                            $fromTime1 = substr($DepartureTime1, 11, 19);
                            $dpTime1 = date("D d M Y", strtotime(substr($DepartureTime1, 0, 10) . " " . $fromTime1));

                            $toTime1 = substr($ArrivalTime1, 11, 19);
                            $arrTime1 = date("D d M Y", strtotime(substr($ArrivalTime1, 0, 10) . " " . $toTime1));

                            $markettingCarrier1 = $airAirSegment[$SegmentRef1]['Carrier'];
                            $markettingFN1 = $airAirSegment[$SegmentRef1]['FlightNumber'];

                            $sqlmk = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier1' ");
                            $rowmk = mysqli_fetch_array($sqlmk, MYSQLI_ASSOC);

                            if (!empty($rowmk1)) {
                                $markettingCarrierName1 = $rowmk1['name'];
                            }

                            // Departure Country
                            $sqldp1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom1' ");
                            $rowdp1 = mysqli_fetch_array($sqldp1, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport1 = $rowdp1['name'];
                                $dCity1 = $rowdp1['cityName'];
                                $dCountry1 = $rowdp1['countryCode'];
                            }

                            // Departure Country
                            $sqlar2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo1' ");
                            $rowar2 = mysqli_fetch_array($sqlar2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport1 = $rowar2['name'];
                                $aCity1 = $rowar2['cityName'];
                                $aCountry1 = $rowar2['countryCode'];
                            }

                            $BookingCode1 = $airPricePoint['airBookingInfo'][1]['@attributes']['BookingCode'];
                            $Seat1 = $airPricePoint['airBookingInfo'][1]['@attributes']['BookingCount'];
                            $CabinClass1 = $airPricePoint['airBookingInfo'][1]['@attributes']['CabinClass'];

                            //Leg 3

                            $FareInfoRef2 = $airPricePoint['airBookingInfo'][2]['@attributes']['FareInfoRef'];
                            $SegmentRef2 = $airPricePoint['airBookingInfo'][2]['@attributes']['SegmentRef'];

                            $FlightTime2 = $airAirSegment[$SegmentRef2]['FlightTime'];
                            $FlightTimeHm2 = floor($FlightTime2 / 60) . "H " . ($FlightTime2 - ((floor($FlightTime2 / 60)) * 60)) . "Min";

                            $ArrivalTo2 = $airAirSegment[$SegmentRef2]['Destination'];
                            $DepartureFrom2 = $airAirSegment[$SegmentRef2]['Origin'];

                            $ArrivalTime2 = $airAirSegment[$SegmentRef2]['ArrivalTime'];
                            $DepartureTime2 = $airAirSegment[$SegmentRef2]['DepartureTime'];

                            $fromTime2 = substr($DepartureTime2, 11, 19);
                            $dpTime2 = date("D d M Y", strtotime(substr($DepartureTime2, 0, 10) . " " . $fromTime2));

                            $toTime2 = substr($ArrivalTime2, 11, 19);
                            $arrTime2 = date("D d M Y", strtotime(substr($ArrivalTime2, 0, 10) . " " . $toTime2));

                            $markettingCarrier2 = $airAirSegment[$SegmentRef2]['Carrier'];
                            $markettingFN2 = $airAirSegment[$SegmentRef2]['FlightNumber'];

                            $sqlmk1 = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier2' ");
                            $rowmk1 = mysqli_fetch_array($sqlmk1, MYSQLI_ASSOC);

                            if (!empty($rowmk1)) {
                                $markettingCarrierName2 = $rowmk1['name'];
                            }

                            // Departure Country
                            $sqldp2 = mysqli_query($conn, "$Airportsql code='$DepartureFrom2' ");
                            $rowdp2 = mysqli_fetch_array($sqldp2, MYSQLI_ASSOC);

                            if (!empty($row1)) {
                                $dAirport2 = $rowdp2['name'];
                                $dCity2 = $rowdp2['cityName'];
                                $dCountry2 = $rowdp2['countryCode'];
                            }

                            // Departure Country
                            $sqlar2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo1' ");
                            $rowar2 = mysqli_fetch_array($sqlar2, MYSQLI_ASSOC);

                            if (!empty($row2)) {
                                $aAirport2 = $rowar2['name'];
                                $aCity2 = $rowar2['cityName'];
                                $aCountry2 = $rowar2['countryCode'];
                            }

                            $BookingCode2 = $airPricePoint['airBookingInfo'][2]['@attributes']['BookingCode'];
                            $Seat2 = $airPricePoint['airBookingInfo'][2]['@attributes']['BookingCount'];
                            $CabinClass2 = $airPricePoint['airBookingInfo'][2]['@attributes']['CabinClass'];

                            $since_start1 = (new DateTime($DepartureTime1))->diff(new DateTime($ArrivalTime));
                            $since_start2 = (new DateTime($DepartureTime2))->diff(new DateTime($ArrivalTime1));

                            $Transit = array("transit1" => "$since_start1->h H $since_start1->m Min",
                                "transit2" => "$since_start2->h H $since_start2->m Min");

                            $segment = array(
                                array("marketingcareer" => "$markettingCarrier",
                                    "marketingflight" => "$markettingFN",
                                    "operatingcareer" => "$markettingCarrier",
                                    "operatingflight" => "$markettingFN",
                                    "departure" => "$DepartureFrom",
                                    "departureAirport" => "$dAirport ",
                                    "departureLocation" => "$dCity , $dCountry",
                                    "departureTime" => $DepartureTime,
                                    "arrival" => "$ArrivalTo",
                                    "arrivalTime" => $ArrivalTime,
                                    "arrivalAirport" => "$aAirport",
                                    "arrivalLocation" => "$aCity , $aCountry",
                                    "flightduration" => "$FlightTimeHm",
                                    "bookingcode" => "$BookingCode",
                                    "seat" => "$Seat",
                                    'CabinClass' => $CabinClass,
                                    'FareInfoRef' => $FareInfoRef,
                                    'SegmentRef' => $SegmentRef,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef],

                                ), array("marketingcareer" => "$markettingCarrier1",
                                    "marketingflight" => "$markettingFN1",
                                    "operatingcareer" => "$markettingCarrier1",
                                    "operatingflight" => "$markettingFN1",
                                    "departure" => "$DepartureFrom1",
                                    "departureAirport" => "$dAirport1",
                                    "departureLocation" => "$dCity1 , $dCountry",
                                    "departureTime" => $DepartureTime1,
                                    "arrival" => "$ArrivalTo1",
                                    "arrivalTime" => $ArrivalTime1,
                                    "arrivalAirport" => "$aAirport1",
                                    "arrivalLocation" => "$aCity1 , $aCountry1",
                                    "flightduration" => "$FlightTimeHm1",
                                    "bookingcode" => "$BookingCode1",
                                    "seat" => "$Seat1",
                                    'CabinClass' => $CabinClass1,
                                    'FareInfoRef' => $FareInfoRef1,
                                    'SegmentRef' => $SegmentRef1,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef1],

                                ),
                                array("marketingcareer" => "$markettingCarrier2",
                                    "marketingflight" => "$markettingFN2",
                                    "operatingcareer" => "$markettingCarrier2",
                                    "operatingflight" => "$markettingFN2",
                                    "departure" => "$DepartureFrom2",
                                    "departureAirport" => "$dAirport2",
                                    "departureLocation" => "$dCity2 , $dCountry2",
                                    "departureTime" => $DepartureTime2,
                                    "arrival" => "$ArrivalTo2",
                                    "arrivalTime" => $ArrivalTime2,
                                    "arrivalAirport" => "$aAirport2",
                                    "arrivalLocation" => "$aCity2 , $aCountry2",
                                    "flightduration" => "$FlightTimeHm2",
                                    "bookingcode" => "$BookingCode2",
                                    "seat" => "$Seat2",
                                    'CabinClass' => $CabinClass2,
                                    'FareInfoRef' => $FareInfoRef2,
                                    'SegmentRef' => $SegmentRef2,
                                    'SegmentDetails' => $airAirSegment[$SegmentRef2],

                                ),
                            );
                            $basic = array("system" => "Galileo",
                                "segment" => "3",
                                "career" => $validatingCarrierCode,
                                "careerName" => "$CarrieerName",
                                "BasePrice" => $BasePrice,
                                "Taxes" => $Taxes,
                                "price" => "$Exact",
                                "airChangePenalty " => $airChangePenalty,
                                "airCancelPenalty" => $airCancelPenalty,
                                "airFareCalc " => $airFareCalc,
                                "FareBasisCode" => $airFareInfo[$FareInfoRef]['FareBasisCode'],
                                "airFareInfoRef" => $airFareInfoRef,
                                "LatestTicketingTime" => $LatestTicketingTime,
                                "departure" => "$From",
                                "departureTime" => "$fromTime",
                                "departureDate" => "$dpTime1",
                                "arrival" => "$To",
                                "arrivalTime" => "$toTime2",
                                "arrivalDate" => "$arrTime2",
                                "flightduration" => $TravelTimeHm,
                                "bags" => $Bags,
                                "seat" => "$Seat",
                                "class" => "$CabinClass",
                                "refundable" => $Refundable,
                                "segments" => $segment,
                                "transit" => $Transit,
                                "traceid" => $TraceId,
                            );

                        }

                        array_push($All, $basic);

                    } else if (isset($airPricePoint['airBookingInfo']['@attributes']['SegmentRef'])) {

                        $FareInfoRef = $airPricePoint['airBookingInfo']['@attributes']['FareInfoRef'];
                        $SegmentRef = $airPricePoint['airBookingInfo']['@attributes']['SegmentRef'];
                        $Bags = $airFareInfo[$FareInfoRef]['Bags'];

                        $FlightTime = $airAirSegment[$SegmentRef]['FlightTime'];
                        $FlightTimeHm = floor($FlightTime / 60) . "H " . ($FlightTime - ((floor($FlightTime / 60)) * 60)) . "Min";

                        $ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
                        $DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

                        $ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
                        $DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

                        $dpTime = date("D d M Y", strtotime($DepartureTime));

                        $arrTime = date("D d M Y", strtotime($ArrivalTime));

                        $markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
                        $markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

                        $sql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                        $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

                        if (!empty($row)) {
                            $markettingCarrierName = $row['name'];
                        }

                        // Departure Country
                        $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                        $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                        if (!empty($row1)) {
                            $dAirport = $row1['name'];
                            $dCity = $row1['cityName'];
                            $dCountry = $row1['countryCode'];
                        }

                        // Departure Country
                        $sql2 = mysqli_query($conn, "SELECT name, cityName, countryCode FROM airports WHERE code='$ArrivalTo' ");
                        $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                        if (!empty($row2)) {
                            $aAirport = $row2['name'];
                            $aCity = $row2['cityName'];
                            $aCountry = $row2['countryCode'];
                        }

                        $BookingCode = $airPricePoint['airBookingInfo']['@attributes']['BookingCode'];
                        $Seat = $airPricePoint['airBookingInfo']['@attributes']['BookingCount'];
                        $CabinClass = $airPricePoint['airBookingInfo']['@attributes']['CabinClass'];

                        $segment = array(
                            array("marketingcareer" => "$markettingCarrier",
                                "marketingflight" => "$markettingFN",
                                "operatingcareer" => "$markettingCarrier",
                                "operatingflight" => "$markettingFN",
                                "departure" => "$DepartureFrom",
                                "departureAirport" => "$dAirport ",
                                "departureLocation" => "$dCity , $dCountry",
                                "departureTime" => $DepartureTime,
                                "arrival" => "$ArrivalTo",
                                "arrivalTime" => "$ArrivalTime",
                                "arrivalAirport" => "$aAirport",
                                "arrivalLocation" => "$aCity , $aCountry",
                                "flightduration" => $FlightTimeHm,
                                "bookingcode" => "$BookingCode",
                                "seat" => "$Seat",
                                'CabinClass' => $CabinClass,
                                'FareInfoRef' => $FareInfoRef,
                                'SegmentRef' => $SegmentRef,
                                'SegmentDetails' => $airAirSegment[$SegmentRef],

                            ),
                        );
                        $basic = array("system" => "Galileo",
                            "segment" => "1",
                            "career" => $validatingCarrierCode,
                            "careerName" => $CarrieerName,
                            "BasePrice" => $BasePrice,
                            "Taxes" => $Taxes,
                            "price" => "$Exact",
                            "airChangePenalty " => $airChangePenalty,
                            "airCancelPenalty" => $airCancelPenalty,
                            "airFareCalc " => $airFareCalc,
                            "FareBasisCode" => $airFareInfo[$FareInfoRef]['FareBasisCode'],
                            "airFareInfoRef" => $airFareInfoRef,
                            "LatestTicketingTime" => $LatestTicketingTime,
                            "departure" => $From,
                            "departureTime" => substr($DepartureTime, 11, 5),
                            "departureDate" => $dpTime,
                            "arrival" => "$To",
                            "arrivalTime" => substr($ArrivalTime, 11, 5),
                            "arrivalDate" => $dpTime,
                            "flightduration" => $FlightTimeHm,
                            "bags" => $Bags,
                            "seat" => $Seat,
                            "class" => $CabinClass,
                            "refundable" => $Refundable,
                            "segments" => $segment,
                            "traceid" => $TraceId);

                        array_push($All, $basic);

                    }

                }

            }
        } else {
            if ($adult > 0 && $child == 0) {
                $airPricePointOptions = $airAirPricePointList['airAirPricingInfo'];
            } else {
                $airPricePointOptions = $airAirPricePointList['airAirPricingInfo'][0];
            }

            $airPricePoint = $airPricePointOptions['airFlightOptionsList']['airFlightOption']['airOption'];

            $key = $airPricePointOptions['@attributes']['Key'];
            $TotalPrice = $airPricePointOptions['@attributes']['TotalPrice'];
            $validatingCarrierCode = $airPricePointOptions['@attributes']['PlatingCarrier'];
            $Exact = (int) filter_var($TotalPrice, FILTER_SANITIZE_NUMBER_INT);

            if (isset($airAirPricePointList['@attributes']['EquivalentBasePrice'])) {
                $BasePrice = (int) filter_var($airAirPricePointList['@attributes']['EquivalentBasePrice'], FILTER_SANITIZE_NUMBER_INT);
            } else {
                $BasePrice = (int) filter_var($airAirPricePointList['@attributes']['BasePrice'], FILTER_SANITIZE_NUMBER_INT);
            }

            $Taxes = $airAirPricePointList['@attributes']['Taxes'];

            if (isset($airAirPricePointList['airFareInfoRef'][0])) {
                $airFareInfoRef = $airPricePointOptions['airFareInfoRef'][0];
            } else {
                $airFareInfoRef = $airPricePointOptions['airFareInfoRef']['@attributes']['Key'];
            }

            $airFareCalc = $airPricePointOptions['airFareCalc'];

            if (isset($airPricePointOptions['airChangePenalty']['airAmount']) == true) {
                $airChangePenalty = $airPricePointOptions['airChangePenalty']['airAmount'];
            } else if (isset($airPricePointOptions['airChangePenalty']['airPercentage']) == true) {
                $airChangePenalty = $airPricePointOptions['airChangePenalty']['airPercentage'];
            } else {
                //print_r($airPricePointOptions['airChangePenalty']);
            }

            if (isset($airPricePointOptions['airCancelPenalty']['airAmount']) == true) {
                $airCancelPenalty = $airPricePointOptions['airCancelPenalty']['airAmount'];
            } else if (isset($airPricePointOptions['airCancelPenalty']['airPercentage']) == true) {
                $airCancelPenalty = $airPricePointOptions['airCancelPenalty']['airPercentage'];
            } else {
                //print_r($airPricePointOptions['airCancelPenalty']);
            }

            if (isset($airPricePointOptions['@attributes']['Refundable'])) {
                $Refundable = "Refundable";
            } else {
                $Refundable = "Nonrefundable";

            }

            $From = $result['airRouteList']['airRoute']['airLeg']['@attributes']['Origin'];
            $To = $result['airRouteList']['airRoute']['airLeg']['@attributes']['Destination'];

            $sql = mysqli_query($conn, "SELECT nameBangla, name FROM airlines WHERE code='$validatingCarrierCode'");
            $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

            if (!empty($row)) {
                $CarrieerName = $row['name'];
            }

            $AirDetails = $airPricePoint['airBookingInfo']['@attributes'];

            if (isset($AirDetails)) {

                $FareInfoRef = $AirDetails['FareInfoRef'];
                $SegmentRef = $AirDetails['SegmentRef'];
                $TravelTime = $airAirSegment[$SegmentRef]['TravelTime'];
                $TravelTimeHm = floor($TravelTime / 60) . "H " . ($TravelTime - ((floor($TravelTime / 60)) * 60)) . "Min";

                $FlightTime = $airAirSegment[$SegmentRef]['FlightTime'];
                $FlightTimeHm = floor($FlightTime / 60) . "H " . ($FlightTime - ((floor($FlightTime / 60)) * 60)) . "Min";

                $ArrivalTo = $airAirSegment[$SegmentRef]['Destination'];
                $DepartureFrom = $airAirSegment[$SegmentRef]['Origin'];

                $ArrivalTime = $airAirSegment[$SegmentRef]['ArrivalTime'];
                $DepartureTime = $airAirSegment[$SegmentRef]['DepartureTime'];

                $dpTime = date("D d M Y", strtotime($DepartureTime));
                $arrTime = date("D d M Y", strtotime($ArrivalTime));

                $markettingCarrier = $airAirSegment[$SegmentRef]['Carrier'];
                $markettingFN = $airAirSegment[$SegmentRef]['FlightNumber'];

                $sql = mysqli_query($conn, "SELECT name FROM airlines WHERE code='$markettingCarrier' ");
                $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

                if (!empty($row)) {
                    $markettingCarrierName = $row['name'];
                }

                // Departure Country
                $sql1 = mysqli_query($conn, "$Airportsql code='$DepartureFrom' ");
                $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

                if (!empty($row1)) {
                    $dAirport = $row1['name'];
                    $dCity = $row1['cityName'];
                    $dCountry = $row1['countryCode'];
                }

                // Departure Country
                $sql2 = mysqli_query($conn, "$Airportsql code='$ArrivalTo' ");
                $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);

                if (!empty($row2)) {
                    $aAirport = $row2['name'];
                    $aCity = $row2['cityName'];
                    $aCountry = $row2['countryCode'];
                }

                $BookingCode = $AirDetails['BookingCode'];
                $Seat = $AirDetails['BookingCount'];
                $CabinClass = $AirDetails['CabinClass'];

                $segment = array(
                    array("marketingcareer" => "$markettingCarrier",
                        "marketingflight" => "$markettingFN",
                        "operatingcareer" => "$markettingCarrier",
                        "operatingflight" => "$markettingFN",
                        "departure" => "$DepartureFrom",
                        "departureAirport" => "$dAirport ",
                        "departureLocation" => "$dCity , $dCountry",
                        "departureTime" => "$dpTime",
                        "arrival" => "$ArrivalTo",
                        "arrivalTime" => "$ArrivalTime",
                        "arrivalAirport" => "$aAirport",
                        "arrivalLocation" => "$aCity , $aCountry",
                        "flightduration" => "$TravelTimeHm",
                        "bookingcode" => "$BookingCode",
                        "seat" => $Seat,
                        'CabinClass' => $CabinClass,
                        'FareInfoRef' => $FareInfoRef,
                        'SegmentRef' => $SegmentRef,
                        'SegmentDetails' => $airAirSegment[$SegmentRef],

                    ),
                );
                $basic = array("system" => "Galileo",
                    "segment" => "1",
                    "career" => $validatingCarrierCode,
                    "careerName" => $CarrieerName,
                    "BasePrice" => $BasePrice,
                    "Taxes" => $Taxes,
                    "price" => "$Exact",
                    "airChangePenalty " => $airChangePenalty,
                    "airCancelPenalty" => $airCancelPenalty,
                    "airFareCalc " => $airFareCalc,
                    "FareBasisCode" => $airFareInfo[$FareInfoRef]['FareBasisCode'],
                    "airFareInfoRef" => $airFareInfoRef,
                    "departure" => $From,
                    "departureDate" => $dpTime,
                    "departureTime" => substr($DepartureTime, 11, 5),
                    "arrival" => "$To",
                    "arrivalTime" => substr($ArrivalTime, 11, 5),
                    "arrivalDate" => "$arrTime",
                    "flightduration" => $TravelTimeHm,
                    "bags" => "No Baggae",
                    "seat" => $Seat,
                    "class" => $CabinClass,
                    "refundable" => $Refundable,
                    "segments" => $segment,
                    "traceid" => $TraceId,
                );

            }
            array_push($All, $basic);
        }
    }

}

if ($FlyHub == 1) {

    $FlyHubRequest = '{
        "AdultQuantity": "' . $adult . '",
        "ChildQuantity": "' . $child . '",
        "InfantQuantity": "' . $infants . '",
        "EndUserIp": "200.69.23.206",
        "JourneyType": "1",
        "Segments": [
            {
            "Origin": "' . $From . '",
            "Destination": "' . $To . '",
            "CabinClass": "Economy",
            "DepartureDateTime": "' . $Date . '"
            }
        ],
        "PreferredAirlines": [
            ""
        ]
        }';

  

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
            "Authorization: Bearer $FlyhubToken",
        ),
    ));

    $flyhubresponse = curl_exec($curlflyhusearch);

    curl_close($curlflyhusearch);

    // echo $flyhubresponse;

    // Decode the JSON file
    
    $Result = json_decode($flyhubresponse, true);
    $FlightListFlyHub = $Result['Results'];
    $SearchID = $Result['SearchId'];
    $FlyHubResponse = array();

    //print_r($FlyHubResponse);
    $f = 0;
    foreach ($FlightListFlyHub as $flight) {
        $f++;
        $Validatingcarrier = $flight['Validatingcarrier'];
        $segments = count($flight['segments']);
        $AgentPrice = $flight['TotalFare'];
        $Refundable = $flight['IsRefundable'];
        $Hold = $flight['HoldAllowed'];

        if ($adult > 0 && $child > 0 && $infants > 0) {
            $BasePrice = $flight['Fares'][0]['BaseFare'] * $adult + $flight['Fares'][1]['BaseFare'] * $child + $flight['Fares'][2]['BaseFare'] * $infants;
            $Taxes = $flight['Fares'][0]['Tax'] * $adult + $flight['Fares'][1]['Tax'] * $child + $flight['Fares'][2]['Tax'] * $infants;
            $Taxes += $flight['Fares'][0]['OtherCharges'] * $adult + $flight['Fares'][1]['OtherCharges'] * $child + $flight['Fares'][2]['OtherCharges'] * $infants;
            $Taxes += $flight['Fares'][0]['ServiceFee'] * $adult + $flight['Fares'][1]['ServiceFee'] * $child + $flight['Fares'][2]['ServiceFee'] * $infants;

            $adultBasePrice = $flight['Fares'][0]['BaseFare'] + $flight['Fares'][0]['ServiceFee'];
            $adultTaxAmount = $flight['Fares'][0]['Tax'] + $flight['Fares'][0]['OtherCharges'];

            $childBasePrice = $flight['Fares'][1]['BaseFare'] + $flight['Fares'][1]['ServiceFee'];
            $childTaxAmount = $flight['Fares'][1]['Tax'] + $flight['Fares'][1]['OtherCharges'];

            $infantBasePrice = $flight['Fares'][2]['BaseFare'] + $flight['Fares'][2]['ServiceFee'];
            $infantTaxAmount = $flight['Fares'][2]['Tax'] + $flight['Fares'][2]['OtherCharges'];

            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                "Tax" => "$adultTaxAmount",
                "PaxCount" => $adult,
                "PaxType" => "ADT",
                "Discount" => "0",
                "OtherCharges" => "0",
                "ServiceFee" => "0")
                ,
                "1" => array("BaseFare" => "$childBasePrice",
                    "Tax" => "$childTaxAmount",
                    "PaxCount" => $child,
                    "PaxType" => "CNN",
                    "Discount" => "0",
                    "OtherCharges" => "0",
                    "ServiceFee" => "0"),
                "2" => array("BaseFare" => "$infantBasePrice",
                    "Tax" => "$infantTaxAmount",
                    "PaxCount" => $infants,
                    "PaxType" => "INF",
                    "Discount" => "0",
                    "OtherCharges" => "0",
                    "ServiceFee" => "0"),
            );

        } else if ($adult > 0 && $child > 0) {
            $BasePrice = $flight['Fares'][0]['BaseFare'] * $adult + $flight['Fares'][1]['BaseFare'] * $child;
            $Taxes = $flight['Fares'][0]['Tax'] * $adult + $flight['Fares'][1]['Tax'] * $child;
            $Taxes += $flight['Fares'][0]['OtherCharges'] * $adult + $flight['Fares'][1]['OtherCharges'] * $child;
            $Taxes += $flight['Fares'][0]['ServiceFee'] * $adult + $flight['Fares'][1]['ServiceFee'] * $child;

            $adultBasePrice = $flight['Fares'][0]['BaseFare'] + $flight['Fares'][0]['ServiceFee'];
            $adultTaxAmount = $flight['Fares'][0]['Tax'] + $flight['Fares'][0]['OtherCharges'];

            $childBasePrice = $flight['Fares'][1]['BaseFare'] + $flight['Fares'][1]['ServiceFee'];
            $childTaxAmount = $flight['Fares'][1]['Tax'] + $flight['Fares'][1]['OtherCharges'];

            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                "Tax" => "$adultTaxAmount",
                "PaxCount" => $adult,
                "PaxType" => "ADT",
                "Discount" => "0",
                "OtherCharges" => "0",
                "ServiceFee" => "0")
                ,
                "1" => array("BaseFare" => "$childBasePrice",
                    "Tax" => "$childTaxAmount",
                    "PaxCount" => $child,
                    "PaxType" => "CNN",
                    "Discount" => "0",
                    "OtherCharges" => "0",
                    "ServiceFee" => "0"),
            );

        } else if ($adult > 0 && $infants > 0) {
            $BasePrice = $flight['Fares'][0]['BaseFare'] * $adult + $flight['Fares'][1]['BaseFare'] * $infants;
            $Taxes = $flight['Fares'][0]['Tax'] * $adult + $flight['Fares'][1]['Tax'] * $infants;
            $Taxes += $flight['Fares'][0]['OtherCharges'] * $adult + $flight['Fares'][1]['OtherCharges'] * $infants;
            $Taxes += $flight['Fares'][0]['ServiceFee'] * $adult + $flight['Fares'][1]['ServiceFee'] * $infants;

            $adultBasePrice = $flight['Fares'][0]['BaseFare'] + $flight['Fares'][0]['ServiceFee'];
            $adultTaxAmount = $flight['Fares'][0]['Tax'] + $flight['Fares'][0]['OtherCharges'];

            $infantBasePrice = $flight['Fares'][1]['BaseFare'] + $flight['Fares'][1]['ServiceFee'];
            $infantTaxAmount = $flight['Fares'][1]['Tax'] + $flight['Fares'][1]['OtherCharges'];

            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                "Tax" => "$adultTaxAmount",
                "PaxCount" => $adult,
                "PaxType" => "ADT",
                "Discount" => "0",
                "OtherCharges" => "0",
                "ServiceFee" => "0"),
                "1" => array("BaseFare" => "$infantBasePrice",
                    "Tax" => "$infantTaxAmount",
                    "PaxCount" => $infants,
                    "PaxType" => "INF",
                    "Discount" => "0",
                    "OtherCharges" => "0",
                    "ServiceFee" => "0"),
            );

        } else if (isset($flight['Fares'][0])) {
            $BasePrice = $flight['Fares'][0]['BaseFare'] * $adult;
            $Taxes = $flight['Fares'][0]['Tax'] * $adult;
            $Taxes += $flight['Fares'][0]['OtherCharges'] * $adult;
            $Taxes += $flight['Fares'][0]['ServiceFee'] * $adult;

            $adultBasePrice = $flight['Fares'][0]['BaseFare'] + $flight['Fares'][0]['ServiceFee'];
            $adultTaxAmount = $flight['Fares'][0]['Tax'] + $flight['Fares'][0]['OtherCharges'];
            $PriceBreakDown = array("0" => array("BaseFare" => "$adultBasePrice",
                "Tax" => "$adultTaxAmount",
                "PaxCount" => $adult,
                "PaxType" => "ADT",
                "Discount" => "0",
                "OtherCharges" => "0",
                "ServiceFee" => "0"),
            );

        }

        $customerPrice = $BasePrice + $Taxes;

        if ($flight['IsRefundable'] == 1) {
            $Refundable = "Refundable";
        } else {
            $Refundable = "Nonrefundable";
        }
        $Availabilty = $flight['Availabilty'];
        $ResultID = $flight['ResultID'];

        $sql = mysqli_query($conn, "SELECT name, commission FROM airlines WHERE code='$Validatingcarrier' ");
        $row = mysqli_fetch_array($sql, MYSQLI_ASSOC);

        if (!empty($row)) {
            $CarrieerName = $row['name'];
            $fareRate = $row['commission'];
        }

        //Agent MarkUP
        $agentMarksql = mysqli_query($conn, "SELECT dmarkup,imarkup, dmarkuptype,imarkuptype FROM B2C_wl_content WHERE agentId='$agentId' ");
        $agentmarkrow = mysqli_fetch_array($agentMarksql, MYSQLI_ASSOC);

        if (!empty($agentmarkrow)) {
            $imarkuptype = $agentmarkrow['imarkuptype'];
            $dmarkuptype = $agentmarkrow['dmarkuptype'];
            if ($imarkuptype == 'amount' || $dmarkuptype == 'amount') {
                if ($TripType == 'Inbound') {
                    $markup = $agentmarkrow['dmarkup'];
                } else {
                    $markup = $agentmarkrow['imarkup'];
                }
                $WLAgentPrice = $AgentPrice + $markup;

            } else if ($imarkuptype == 'percentage' || $dmarkuptype == 'percentage') {
                if ($TripType == 'Inbound') {
                    $markup = $agentmarkrow['dmarkup'];
                } else {
                    $markup = $agentmarkrow['imarkup'];
                }
                $WLAgentPrice = ceil($AgentPrice + ($AgentPrice * ($markup / 100)));

            } else {
                $WLAgentPrice = $AgentPrice;
            }
        } else {
            $WLAgentPrice = $AgentPrice;
        }

        //SubAgent Markup
        $subagentMarksql = mysqli_query($conn, "SELECT dmarkup, imarkup, markuptype FROM subagent WHERE agentId='$agentId' AND subagentId='$subagentId' ");
        $subagentmarkrow = mysqli_fetch_array($subagentMarksql, MYSQLI_ASSOC);

        //echo $TripType;

        if (!empty($subagentmarkrow)) {
            $markuptype = $subagentmarkrow['markuptype'];
            if ($markuptype == 'amount') {
                if ($TripType == 'Inbound') {
                    $markup = $subagentmarkrow['dmarkup'];
                } else {
                    $markup = $subagentmarkrow['imarkup'];
                }
                $subAgentPrice = $WLAgentPrice + $markup;

            } else if ($markuptype == 'percentage') {
                if ($TripType == 'Inbound') {
                    $markup = $subagentmarkrow['dmarkup'];
                } else {
                    $markup = $subagentmarkrow['imarkup'];
                }
                $subAgentPrice = ceil($WLAgentPrice + ($WLAgentPrice * ($markup / 100)));

            } else {
                $subAgentPrice = $WLAgentPrice;
            }
        } else {
            $subAgentPrice = $WLAgentPrice;
        }

        if ($WLAgentPrice < $subAgentPrice) {
            $ClientFare = $WLAgentPrice;
        } else {
            $ClientFare = $BasePrice + $Taxes;
        }

        $uId = sha1(md5(time()) . '' . rand());

        if ($segments == 1) {
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
            $CabinClass = $flight['segments'][0]['Airline']['CabinClass'];
            $OperatingCarrier = $flight['segments'][0]['Airline']['OperatingCarrier'];

            if (isset($flight['segments'][0]['baggageDetails'][0]['Checkin'])) {
                $Baggage = $flight['segments'][0]['baggageDetails'][0]['Checkin'];
            } else {
                $Baggage = 0;
            }

            //$Baggage = $flight['segments'][0]['baggageDetails'][0]['Checkin'];

            $JourneyDuration = $flight['segments'][0]['JourneyDuration'];
            $Duration = floor($JourneyDuration / 60) . "H " . ($JourneyDuration - ((floor($JourneyDuration / 60)) * 60)) . "Min";

            $transitDetails = array("transit1" => "0");

            $segment = array("0" => array("marketingcareer" => "$AirlineCode",
                "marketingcareerName" => "$AirlineName",
                "marketingflight" => "$FlightNumber",
                "operatingcareer" => "$OperatingCarrier",
                "operatingflight" => "$FlightNumber",
                "departure" => "$dAirportCode",
                "departureAirport" => "$dAirportName ",
                "departureLocation" => "$dCityName , $dCountryCode",
                "departureTime" => "$DepTime",
                "arrival" => "$aAirportCode",
                "arrivalTime" => "$ArrTime",
                "arrivalAirport" => "$aAirportName",
                "arrivalLocation" => "$aCityName , $aCountryCode",
                "flightduration" => "$Duration",
                "bookingcode" => "$BookingClass",
                "seat" => "$Availabilty"),
            );

            $basic = array("system" => "FlyHub",
                "segment" => "1",
                "uId" => $uId,
                "triptype" => $TripType,
                "career" => "$Validatingcarrier",
                "careerName" => "$CarrieerName",
                "BasePrice" => "$BasePrice",
                "Taxes" => "$Taxes",
                "netfare" => "$AgentPrice",
                "agentprice" => "$WLAgentPrice",
                //"subagentprice" => "$subAgentPrice",
                "customerPrice" => $customerPrice,
                "pricebreakdown" => $PriceBreakDown,
                "departure" => $dAirportCode,
                "departureTime" => substr($DepTime, 11, 5),
                "departureDate" => date("D d M Y", strtotime($DepTime)),
                "arrival" => "$aAirportCode",
                "arrivalTime" => substr($ArrTime, 11, 5),
                "arrivalDate" => date("D d M Y", strtotime($ArrTime)),
                "flightduration" => "$Duration",
                "transit" => $transitDetails,
                "bags" => "$Baggage",
                "seat" => "$Availabilty",
                "class" => "$CabinClass",
                "refundable" => "$Refundable",
                "segments" => $segment,
                "hold" => "$Hold",
                "SearchID" => $SearchID,
                "ResultID" => $ResultID,

            );
            array_push($FlyHubResponse, $basic);

        } else if ($segments == 2) {

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
            $CabinClass = $flight['segments'][0]['Airline']['CabinClass'];
            $OperatingCarrier = $flight['segments'][0]['Airline']['OperatingCarrier'];

            if (isset($flight['segments'][0]['baggageDetails'][0]['Checkin'])) {
                $Baggage = $flight['segments'][0]['baggageDetails'][0]['Checkin'];
            } else {
                $Baggage = 0;
            }
            $JourneyDuration = $flight['segments'][0]['JourneyDuration'];
            $Duration = floor($JourneyDuration / 60) . "H " . ($JourneyDuration - ((floor($JourneyDuration / 60)) * 60)) . "Min";

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
            $Duration1 = floor($JourneyDuration1 / 60) . "H " . ($JourneyDuration1 - ((floor($JourneyDuration1 / 60)) * 60)) . "Min";

            $segment = array("0" => array("marketingcareer" => "$AirlineCode",
                "marketingcareerName" => "$AirlineName",
                "marketingflight" => "$FlightNumber",
                "operatingcareer" => "$OperatingCarrier",
                "operatingflight" => "$FlightNumber",
                "departure" => "$dAirportCode",
                "departureAirport" => "$dAirportName ",
                "departureLocation" => "$dCityName , $dCountryCode",
                "departureTime" => "$DepTime",
                "arrival" => "$aAirportCode",
                "arrivalTime" => "$ArrTime",
                "arrivalAirport" => "$aAirportName",
                "arrivalLocation" => "$aCityName , $aCountryCode",
                "flightduration" => "$Duration",
                "bookingcode" => "$BookingClass",
                "seat" => "$Availabilty"),
                "1" => array("marketingcareer" => "$AirlineCode1",
                    "marketingcareerName" => "$AirlineName1",
                    "marketingflight" => "$FlightNumber1",
                    "operatingcareer" => "$OperatingCarrier1",
                    "operatingflight" => "$FlightNumber1",
                    "departure" => "$dAirportCode1",
                    "departureAirport" => "$dAirportName1",
                    "departureLocation" => "$dCityName1 , $dCountryCode1",
                    "departureTime" => "$DepTime1",
                    "arrival" => "$aAirportCode1",
                    "arrivalTime" => "$ArrTime1",
                    "arrivalAirport" => "$aAirportName1",
                    "arrivalLocation" => "$aCityName1 , $aCountryCode1",
                    "flightduration" => "$Duration1",
                    "bookingcode" => "$BookingClass1",
                    "seat" => "$Availabilty"),
            );

            $TransitTime = round(abs(strtotime($DepTime1) - strtotime($ArrTime)) / 60, 2);
            $TransitDuration = floor($TransitTime / 60) . "H " . ($TransitTime - ((floor($TransitTime / 60)) * 60)) . "Min";

            $JourneyTime = $JourneyDuration + $JourneyDuration1 + $TransitTime;
            $TotalDuration = floor($JourneyTime / 60) . "H " . ($JourneyTime - ((floor($JourneyTime / 60)) * 60)) . "Min";

            $transitDetails = array("transit1" => $TransitDuration);

            $basic = array("system" => "FlyHub",
                "segment" => "2",
                "uId" => $uId,
                "triptype" => $TripType,
                "career" => "$Validatingcarrier",
                "careerName" => "$CarrieerName",
                "BasePrice" => "$BasePrice",
                "Taxes" => "$Taxes",
                "netfare" => "$AgentPrice",
                "agentprice" => "$WLAgentPrice",
                //"subagentprice" => "$subAgentPrice",
                "customerPrice" => $customerPrice,
                "pricebreakdown" => $PriceBreakDown,
                "departure" => "$dAirportCode",
                "departureTime" => substr($DepTime, 11, 5),
                "departureDate" => date("D d M Y", strtotime($DepTime)),
                "arrival" => "$aAirportCode1",
                "arrivalTime" => substr($ArrTime1, 11, 5),
                "arrivalDate" => date("D d M Y", strtotime($ArrTime1)),
                "flightduration" => "$TotalDuration",
                "transit" => $transitDetails,
                "bags" => "$Baggage",
                "seat" => "$Availabilty",
                "class" => "$CabinClass",
                "refundable" => "$Refundable",
                "segments" => $segment,
                "hold" => "$Hold",
                "SearchID" => $SearchID,
                "ResultID" => $ResultID,

            );
            array_push($FlyHubResponse, $basic);

        } else if ($segments == 3) {

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
            $CabinClass = $flight['segments'][0]['Airline']['CabinClass'];
            $OperatingCarrier = $flight['segments'][0]['Airline']['OperatingCarrier'];

            if (isset($flight['segments'][0]['baggageDetails'][0]['Checkin'])) {
                $Baggage = $flight['segments'][0]['baggageDetails'][0]['Checkin'];
            } else {
                $Baggage = 0;
            }

            $JourneyDuration = $flight['segments'][0]['JourneyDuration'];
            $Duration = floor($JourneyDuration / 60) . "H " . ($JourneyDuration - ((floor($JourneyDuration / 60)) * 60)) . "Min";

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
            $Duration1 = floor($JourneyDuration1 / 60) . "H " . ($JourneyDuration1 - ((floor($JourneyDuration1 / 60)) * 60)) . "Min";

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
            $Duration2 = floor($JourneyDuration1 / 60) . "H " . ($JourneyDuration1 - ((floor($JourneyDuration1 / 60)) * 60)) . "Min";

            $segment = array("0" => array("marketingcareer" => "$OperatingCarrier",
                "marketingcareerName" => "$AirlineName",
                "marketingflight" => "$FlightNumber",
                "operatingcareer" => "$OperatingCarrier",
                "operatingflight" => "$FlightNumber",
                "departure" => "$dAirportCode",
                "departureAirport" => "$dAirportName ",
                "departureLocation" => "$dCityName , $dCountryCode",
                "departureTime" => "$DepTime",
                "arrival" => "$aAirportCode",
                "arrivalTime" => "$ArrTime",
                "arrivalAirport" => "$aAirportName",
                "arrivalLocation" => "$aCityName , $aCountryCode",
                "flightduration" => "$Duration",
                "bookingcode" => "$BookingClass",
                "seat" => "$Availabilty"),
                "1" => array("marketingcareer" => "$OperatingCarrier1",
                    "marketingcareerName" => "$AirlineName1",
                    "marketingflight" => "$FlightNumber1",
                    "operatingcareer" => "$OperatingCarrier1",
                    "operatingflight" => "$FlightNumber1",
                    "departure" => "$dAirportCode1",
                    "departureAirport" => "$dAirportName1 ",
                    "departureLocation" => "$dCityName1 , $dCountryCode1",
                    "departureTime" => "$DepTime1",
                    "arrival" => "$aAirportCode1",
                    "arrivalTime" => "$ArrTime1",
                    "arrivalAirport" => "$aAirportName1",
                    "arrivalLocation" => "$aCityName1 , $aCountryCode1",
                    "flightduration" => "$Duration1",
                    "bookingcode" => "$BookingClass1",
                    "seat" => "$Availabilty"),

                "2" => array("marketingcareer" => "$OperatingCarrier2",
                    "marketingcareerName" => "$AirlineName2",
                    "marketingflight" => "$FlightNumber2",
                    "operatingcareer" => "$OperatingCarrier2",
                    "operatingflight" => "$FlightNumber2",
                    "departure" => "$dAirportCode2",
                    "departureAirport" => "$dAirportName2",
                    "departureLocation" => "$dCityName2 , $dCountryCode2",
                    "departureTime" => "$DepTime2",
                    "arrival" => "$aAirportCode2",
                    "arrivalTime" => "$ArrTime2",
                    "arrivalAirport" => "$aAirportName2",
                    "arrivalLocation" => "$aCityName2 , $aCountryCode2",
                    "flightduration" => "$Duration2",
                    "bookingcode" => "$BookingClass2",
                    "seat" => "$Availabilty"),
            );

            $TransitTime = round(abs(strtotime($DepTime1) - strtotime($ArrTime)) / 60, 2);
            $TransitDuration = floor($TransitTime / 60) . "H " . ($TransitTime - ((floor($TransitTime / 60)) * 60)) . "Min";

            $TransitTime1 = round(abs(strtotime($DepTime2) - strtotime($ArrTime1)) / 60, 2);
            $TransitDuration1 = floor($TransitTime1 / 60) . "H " . ($TransitTime1 - ((floor($TransitTime1 / 60)) * 60)) . "Min";

            $JourneyTime = $JourneyDuration + $JourneyDuration1 + $JourneyDuration2 + $TransitTime + $TransitTime1;
            $TotalDuration = floor($JourneyTime / 60) . "H " . ($JourneyTime - ((floor($JourneyTime / 60)) * 60)) . "Min";

            $transitDetails = array("transit1" => $TransitDuration,
                "transit2" => $TransitDuration1);

            $basic = array("system" => "FlyHub",
                "segment" => "3",
                "uId" => $uId,
                "triptype" => $TripType,
                "career" => $Validatingcarrier,
                "careerName" => "$CarrieerName",
                "BasePrice" => "$BasePrice",
                "Taxes" => "$Taxes",
                "netfare" => "$AgentPrice",
                "agentprice" => "$WLAgentPrice",
                // "subagentprice" => "$subAgentPrice",
                "customerPrice" => $customerPrice,
                "pricebreakdown" => $PriceBreakDown,
                "departure" => "$dAirportCode",
                "departureTime" => substr($DepTime, 11, 5),
                "departureDate" => date("D d M Y", strtotime($DepTime)),
                "arrival" => "$aAirportCode2",
                "arrivalTime" => substr($ArrTime2, 11, 5),
                "arrivalDate" => date("D d M Y", strtotime($ArrTime2)),
                "flightduration" => "$TotalDuration",
                "transit" => $transitDetails,
                "bags" => "$Baggage",
                "seat" => "$Availabilty",
                "class" => "$CabinClass",
                "refundable" => "$Refundable",
                "segments" => $segment,
                "hold" => "$Hold",
                "SearchID" => $SearchID,
                "ResultID" => $ResultID,

            );
            array_push($FlyHubResponse, $basic);

        }
    }
}

if ($Sabre == 1 && $Galileo == 1 && $FlyHub == 1) {
    $AllItenary = array_merge($FlyHubResponse, $All);
    array_multisort(array_column($AllItenary, 'agentprice'), SORT_ASC, $AllItenary);
    $json_string = json_encode($AllItenary, JSON_PRETTY_PRINT);
    print_r($json_string);

} else if ($Sabre == 1 && $Galileo == 1) {
    //array_multisort(array_column($All, 'agentprice'), SORT_ASC, $All);
    $json_string = json_encode($All, JSON_PRETTY_PRINT);
    print_r($json_string);

} else if ($Sabre == 1 && $FlyHub == 1) {
    $AllItenary = array_merge($FlyHubResponse, $All);
    array_multisort(array_column($AllItenary, 'agentprice'), SORT_ASC, $AllItenary);
    $json_string = json_encode($AllItenary, JSON_PRETTY_PRINT);
    print_r($json_string);
} else if ($Galileo == 1 && $FlyHub == 1) {
    $AllItenary = array_merge($FlyHubResponse, $All);
    array_multisort(array_column($AllItenary, 'agentprice'), SORT_ASC, $AllItenary);
    $json_string = json_encode($AllItenary, JSON_PRETTY_PRINT);
    print_r($json_string);
} else if ($Sabre == 1 || $Galileo == 1) {
    $json_string = json_encode($All, JSON_PRETTY_PRINT);
    print_r($json_string);

} else if ($FlyHub == true) {
    $json_string = json_encode($FlyHubResponse, JSON_PRETTY_PRINT);
    print_r($json_string);

}
    $conn->close();

	?>