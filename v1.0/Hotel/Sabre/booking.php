<?php

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
    } else {
        $response = [];
        $response['status'] = 'error';
        $response['message'] = 'Invalid Request';

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
