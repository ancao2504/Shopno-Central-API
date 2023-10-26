<?php

include '../../config.php';
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE');
header('Access-Control-Max-Age: 3600');
header(
    'Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'
);

$response = [];
$FlightType;

$control = mysqli_query($conn, 'SELECT * FROM control where id=1');
$controlrow = mysqli_fetch_array($control, MYSQLI_ASSOC);

if (!empty($controlrow)) {
    $Sabre = $controlrow['sabre'];
}

$Airportsql = 'SELECT name, cityName, countryCode FROM airports WHERE';

if (
    array_key_exists('journeyfrom', $_GET) &&
    array_key_exists('journeyto', $_GET) &&
    array_key_exists('departuredate', $_GET) &&
    array_key_exists('adult', $_GET) &&
    array_key_exists('child', $_GET) &&
    array_key_exists('infant', $_GET)
) {
    $From = $_GET['journeyfrom'];
    $To = $_GET['journeyto'];
    $Date = $_GET['departuredate'];
    $ActualDate = $Date . 'T00:00:00';
    $adult = $_GET['adult'];
    $child = $_GET['child'];
    $infant = $_GET['infant'];

    // TODO: Find Out origin Details
    $fromsql = mysqli_query(
        $conn,
        "SELECT name, cityName, countryCode FROM airports WHERE code='$From'"
    );
    $fromrow = mysqli_fetch_array($fromsql, MYSQLI_ASSOC);

    if (!empty($fromrow)) {
        $fromCountry = $fromrow['countryCode'];
    }

    // TODO: Find Out Destination Details
    $tosql = mysqli_query(
        $conn,
        "SELECT name, cityName, countryCode FROM airports WHERE code='$To'"
    );
    $torow = mysqli_fetch_array($tosql, MYSQLI_ASSOC);

    if (!empty($torow)) {
        $toCountry = $torow['countryCode'];
    }

    //TODO: FInd Out IF it's inbound or outbound 

    if ($fromCountry == 'BD' && $toCountry == 'BD') {
        $TripType = 'Inbound';
    } else {
        $TripType = 'Outbound';
    }

    //TODO: Search Body
    $data = [
        "AdultCount" => $adult,
        "ChildCount" => $child,
        "InfantCount" => $infant,
        "JourneyType" => "OneWay",
        "PreferredAirlines" => [""],
        "CabinClass" => "Economy",
        "Segments" => [
            [
                "Origin" => $From,
                "Destination" => $To,
                "DepartureDate" => $ActualDate
            ]
        ]
    ];

    // TODO: API endpoint URL
    $url = 'https://test.services.travelomatix.com/webservices/index.php/flight/service/Search';

    // TODO: Headers for search results
    $headers = [
        'x-Password: test@304',
        'x-DomainKey: TMX7643041694504168',
        'x-Username: test304328',
        'x-system: test',
        'Content-Type: application/json',
    ];

    // TODO: Initialize cURL session
    $ch = curl_init();

    // TODO: Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); //TODO: Encode data as JSON
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // TODO: Execute cURL session and get the response
    $response = curl_exec($ch);

    // TODO: Check for cURL errors
    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch);
    }

    // TODO:Check the HTTP response code
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode != 200) {
        echo 'HTTP Error: ' . $httpCode;
    } else {
    // TODO:Output the API response
    //TODO: Making AfterSearch Start
        // echo $response;
        $responseData = json_decode($response, true);
        // TODO: Marking Search Result
        if (isset($responseData['Search']['FlightDataList']['JourneyList'])) {
            $journeyList = $responseData['Search']['FlightDataList']['JourneyList'][0];

            foreach($journeyList as $singleFlight){
                $system= "TX";

                $result = [
                    "system"=>"TX",
                    "segment"=>"1",
                    "uId"=>"uId",
                    "carrier"=>"carrier",
                ];
            }

            
            
            //Todo: Returning Search Result
            $response = json_encode($journeyList);
            print_r($response); 
        } else {
            $response = [];
            $response['status'] = 'error';
            $response['message'] = 'No Result Found';

            echo json_encode($response);
        }
    }

    // TODO: Close cURL session
    curl_close($ch);
} else {
    $response = [];
    $response['status'] = 'error';
    $response['message'] = 'Invalid Request';

    echo json_encode($response);
}

$conn->close();
?>
