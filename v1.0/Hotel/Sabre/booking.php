<?php

include '../../config.php';
include './utils.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE');
header('Access-Control-Max-Age: 3600');
header(
    'Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'
);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_POST = json_decode(file_get_contents('php://input'), true);

    $guestInfo = $_POST['guestInfo'];
    $paymentInfo = $_POST['paymentInfo'];
    $agentId = isset($_POST['agentId']) ? $_POST['agentId'] : "";
    $staffId = isset($_POST['staffId']) ? $_POST['staffId'] : "";
    $subAgentId = isset($_POST['subAgentId']) ? $_POST['subAgentId'] : "";
    $userId = isset($_POST['userId']) ? $_POST['userId'] : "";
    $phone = $_POST['phone'];
    $bookingKey = $_POST['bookingKey'];
    $email = $_POST['email'];
    $pcc = '27YK';
    // $accessToken = getToken();
    $accessToken = getProdToken();

    $requestBody = sabreRequestBody(
        $pcc,
        $guestInfo,
        $paymentInfo,
        $phone,
        $email,
        $bookingKey
    );
    // echo $requestBody;

    $result = sabreHotelBooking($accessToken, $requestBody);

    if (isset($result)) {
        echo $result;
        $bookingPnr = "123";
        $adultCount = 1;
        $childCount = 1;
        $totalPax = $adultCount + $childCount;
        $rooms = 1;
        $checkIn = '2023-12-01';
        $checkOut = '2023-12-03';
        $platform = 'B2B';
        $uId = sha1(md5(time()) . '' . rand());
        $refundable = 'refundable';
        $netCost = 200000;

        addPax($conn, $guestInfo);
        saveBooking($conn, $guestInfo, $bookingPnr, $agentId, $staffId, $subAgentId, $userId, $adultCount, $childCount, $rooms, $checkIn, $checkOut, $platform, $uId, $phone, $email, $refundable, $name, $netCost);
    } else {
        $response = [];
        $response['status'] = 'error';
        $response['message'] = 'Booking Failed';

        echo json_encode($response);
    }
} else {
    $response = [];
    $response['status'] = 'error';
    $response['message'] = 'Invalid Request';

    echo json_encode($response);
}

function sabreRequestBody(
    $pcc,
    $guestInfo,
    $paymentInfo,
    $phone,
    $email,
    $bookingKey
) {
    //TODO: agency Information
    $agencyName = 'Shopno Tours & Travel';
    $cityName = 'Dhaka';
    $countryCode = 'BD';
    $postalCode = '1215';
    $streetNumber = 'Dhaka, Bangladesh';
    $stateCode = 'BD';

    $paymentType = $paymentInfo['paymentType'];
    $cardCode = $paymentInfo['cardCode'];
    $cardNumber = $paymentInfo['cardNumber'];
    $expiryDate = $paymentInfo['expiryDate'];
    $expiryMonth = date('m', strtotime($expiryDate));
    $expiryYear = date('Y', strtotime($expiryDate));
    $holderFName = $paymentInfo['holderFName'];
    $holderLName = $paymentInfo['holderLName'];
    $holderEmail = $paymentInfo['holderEmail'];
    $csc = $paymentInfo['csc'];
    $address = $paymentInfo['address'];
    $cityCode = $paymentInfo['cityCode'];


    $personArray = [];
    foreach ($guestInfo as $key => $guest) {

        $personArray[] = [
            'NameNumber' => ($guest['type'] === 'ADT'
                ? '1'
                : ($guest['type'] === 'CNN'
                    ? '2'
                    : '3')) .
                '.' .
                ($key + 1),
            'NameReference' => $guest['type'] . '_' . ($key + 1),
            'PassengerType' => $guest['type'],
            'GivenName' => $guest['fName'],
            'Surname' => $guest['lName'],
        ];
    }

    $roomArray = [];


    $formattedGuests = [];
    foreach ($guestInfo as $index => $guest) {
        $formattedGuests[] = [
            'Contact' => [
                'Phone' => $guest['phone'],
            ],
            'FirstName' => $guest['fName'],
            'LastName' => $guest['lName'],
            'Index' => $index + 1,
            'LeadGuest' => $index === 0 ? true : false,
            'Type' => $guest['type'] === 'ADT' ? 10 : 8,
            'Email' => $guest['email'],
        ];
    }

    $roomArray[] = [
        'Guests' => [
            'Guest' => $formattedGuests,
        ],
        'RoomIndex' => 1,
    ];


    $requestBody =
        '{
        "CreatePassengerNameRecordRQ":{
           "haltOnAirPriceError":true,
           "TravelItineraryAddInfo":{
              "AgencyInfo":{
                "Address": {
                    "AddressLine": "' .
        $agencyName .
        '",
                    "CityName": "' .
        $cityName .
        '",
                    "CountryCode": "' .
        $countryCode .
        '",
                    "PostalCode": "' .
        $postalCode .
        '",
                    "StateCountyProv": {
                        "StateCode": "' .
        $stateCode .
        '"
                    },
                    "StreetNmbr": "' .
        $streetNumber .
        '"
                }
              },
              "CustomerInfo":{
                 "ContactNumbers":{
                    "ContactNumber":[
                       {
                          "NameNumber":"1.1",
                          "Phone":"' .
        $phone .
        '",
                          "PhoneUseType":"H"
                       }
                    ]
                 },
                 "PersonName":' .
        json_encode($personArray) .
        '
              }
           },
           "HotelBook":{
              "bookGDSviaCSL":true,
              "BookingInfo":{
                 "BookingKey":"' .
        $bookingKey .
        '"
              },
              "Rooms":{
                 "Room":' .
        json_encode($roomArray) .
        '
              },
              "PaymentInformation":{
                 "FormOfPayment":{
                    "PaymentCard":{
                       "PaymentType":"' .
        $paymentType .
        '",
                       "CardCode":"' .
        $cardCode .
        '",
                       "CardNumber":"' .
        $cardNumber .
        '",
                       "ExpiryMonth":' .
        $expiryMonth .
        ',
                       "ExpiryYear":"' .
        $expiryYear .
        '",
                       "FullCardHolderName":{
                          "FirstName":"' .
        $holderFName .
        '",
                          "LastName":"' .
        $holderLName .
        '",
                          "Email":"' .
        $holderEmail .
        '"
                       },
                       "CSC":"' .
        $csc .
        '",
                       "Address":{
                          "AddressLine":[
                             "' .
        $address .
        '"
                          ],
                          "CityName":"' .
        $cityName .
        '",
                          "StateProvince":{
                             "code":"' .
        $stateCode .
        '"
                          },
                          "StateProvinceCodes":{
                             "Code":[
                                {
                                   "content":"' .
        $stateCode .
        '"
                                }
                             ]
                          },
                          "PostCode":"' .
        $postalCode .
        '",
                          "CountryCodes":{
                             "Code":[
                                {
                                   "content":"' .
        $cityCode .
        '"
                                }
                             ]
                          }
                       },
                       "Phone":{
                          "PhoneNumber":"' .
        $phone .
        '"
                       }
                    }
                 },
                 "Type":"GUARANTEE"
              },
                 "POS": {
                     "Source": {
                         "RequestorID": {
                             "Id": "42339566"
                         },
                         "AgencyContact": {
                             "Mobile": "' .
        $phone .
        '"
                         },
                         "ISOCountryCode":"BD",
                         "PseudoCityCode": "' .
        $pcc .
        '"
                     }
                 }
           },
           "PostProcessing":{
              "RedisplayReservation":{
                 "waitInterval":100
              },
              "EndTransaction":{
                 "Source":{
                    "ReceivedFrom":"Sabre API"
                 }
              }
           }
        }
     }';

    return $requestBody;
}

function sabreHotelBooking($accessToken, $requestBody)
{
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.platform.sabre.com/v2.4.0/passenger/records?mode=create',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $requestBody,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Conversation-ID: 2021.01.DevStudio',
            'Authorization: Bearer ' . $accessToken,
        ],
    ]);

    $response = curl_exec($curl);

    $responseData = json_decode($response, true);

    // TODO: return Result

    return json_encode($responseData);
}


function saveBooking($conn, $guestInfo, $bookingPnr, $agentId, $staffId, $subAgentId, $userId, $adultCount, $childCount, $rooms, $checkIn, $checkOut, $platform, $uId, $email, $phone, $refundable, $name, $netCost)
{
    if (!empty($bookingPnr)) {
        $bookingId = "";
        $query = "SELECT id, bookingId FROM hotel_booking ORDER BY bookingId DESC LIMIT 1";
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $number = (int) filter_var($row["bookingId"], FILTER_SANITIZE_NUMBER_INT);
                $newnumber =  $number + 1;
                $bookingId = "STHB$newnumber";
            }
        } else {
            $bookingId = "STHB0001";
        }




        $createdTime = date("Y-m-d H:i:s");
        $sql = "INSERT INTO `hotel_booking` (
                          `uid`,
                          `bookingId`,
                          `userId`,
                          `agentId`,
                          `staffId`,
                          `subagentId`,
                          `email`,
                          `phone`,
                          `name`,
                          `refundable`,
                          `pnr`,
                          `platform`,
                          `adultCount`
                          `childCount`
                          `rooms`,
                          `checkin`
                          `checkout`,
                          `netCost`,
                          )

  VALUES('$uId','$bookingId','$userId','$agentId','$staffId','$subAgentId','$email','$phone','$name','$refundable','$bookingPnr','$platform','$adultCount','$childCount','$checkIn','$checkOut','$netCost')";

        if ($conn->query($sql) === true) {
            addPax($conn, $guestInfo);

            $response['status'] = "success";
            $response['BookingId'] = "$bookingId";
            $response['platform'] = $platform;
            $response['message'] = "Booking Successfully";
            echo json_encode($response);
        }
    }
};
function addPax($conn, $guestInfo)
{
    foreach ($guestInfo as $index => $guest) {
        $paxId = "";
        $result = $conn->query("SELECT * FROM hotel_passengers ORDER BY id DESC LIMIT 1");
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                $number = (int) $outputString + 1;
                $paxId = "STHP$number";
            }
        } else {
            $paxId = "STHP1000";
        }
        $type = $guest['type'];
        $fName = $guest['fName'];
        $lName = $guest['lName'];
        $phone = $guest['phone'];
        $email = $guest['email'];


        $query = "INSERT INTO `passengers_hotel` (`paxId`, `type`, `fName`, `lName`, `phone`, `email`) 
            VALUES ('$paxId', '$type', '$fName', '$lName', '$phone', '$email')";

        if ($conn->query($query) === TRUE) {
            $response['status'] = "success";
            $response['message'] = "Traveler Added Successfully";
        } else {
            $response['status'] = "error";
            $response['message'] = "Traveler Added Failed";
        }
    }
};
