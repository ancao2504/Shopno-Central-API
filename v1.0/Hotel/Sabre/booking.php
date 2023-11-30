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
    $agentId = isset($_POST['agentId']) ? $_POST['agentId'] : '';
    $staffId = isset($_POST['staffId']) ? $_POST['staffId'] : '';
    $subAgentId = isset($_POST['subAgentId']) ? $_POST['subAgentId'] : '';
    $userId = isset($_POST['userId']) ? $_POST['userId'] : '';
    $system = isset($_POST['system']) ? $_POST['system']:"";
    $phone = $_POST['phone'];
    $bookingKey = $_POST['bookingKey'];
    $email = $_POST['email'];
    $adultCount = $_POST['adultCount'];
    $childCount = $_POST['childCount'];
    $totalPax = $adultCount + $childCount;
    $rooms = $_POST['rooms'];
    $checkIn = $_POST['checkIn'];
    $checkOut = $_POST['checkOut'];
    $platform = $_POST['platform'];
    $refundable = $_POST['refundable'];
    $uId = sha1(md5(time()) . '' . rand());
    $netCost = $_POST['netCost'];
    $hotelName = $_POST['hotelName'];
    $hotelCode = $_POST['hotelCode'];
    $guestPassengerName = $_POST['guestPassengerName'];

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
    // $result = sabreHotelBooking($accessToken, $requestBody);

    $filePath = './test.json';
    $jsonData = file_get_contents($filePath);
    $result = json_decode($jsonData, true);
    $bookingPnr = $result['CreatePassengerNameRecordRS']['ItineraryRef']['ID'];

    if (!empty($bookingPnr)) {
        // echo $result;
        // echo $requestBody
        // addPax($conn, $guestInfo);
        saveBooking(
            $conn,
            $guestInfo,
            $bookingPnr,
            $agentId,
            $staffId,
            $subAgentId,
            $userId,
            $adultCount,
            $childCount,
            $rooms,
            $checkIn,
            $checkOut,
            $platform,
            $uId,
            $phone,
            $email,
            $refundable,
            $guestPassengerName,
            $netCost,
            $system
        );
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
    $agencyPhone = '09606912912';
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
    $holderPhone = $paymentInfo['holderPhone'];
    $csc = $paymentInfo['csc'];
    $address = $paymentInfo['address'];
    $cityCode = $paymentInfo['cityCode'];

    $personArray = [];
    foreach ($guestInfo as $key => $guest) {
        $personArray[] = [
            'NameNumber' => $key + 1 . '.1',
            'NameReference' => '',
            'PassengerType' => $guest['type'],
            'GivenName' => $guest['fName'],
            'Surname' => $guest['lName'],
        ];
    }

    $roomArray = [];

    $leadGuests = array_filter($guestInfo, function ($guest) {
        return $guest['leadGuest'] === true;
    });

    $formattedGuests = [];
    foreach ($leadGuests as $index => $guest) {
        $formattedGuests[] = [
            'Contact' => [
                'Phone' => $guest['phone'],
            ],
            'FirstName' => $guest['fName'],
            'LastName' => $guest['lName'],
            'Index' => $index + 1,
            'LeadGuest' => $guest['leadGuest'],
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
            "version": "2.5.0",
            "targetCity": "' .
        $pcc .
        '",
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
        $holderPhone .
        '"
                       }
                    }
                 },
                 "Type":"GUARANTEE"
              },
                 "POS": {
                     "Source": {
                         "RequestorID": {
                            "Type": 5,
                            "Id": "42339566",
                            "IdContext": "IATA"
                         },
                         "AgencyAddress": {
                            "AddressLine1": "' .
        $agencyName .
        '",
                            "AddressLine2": "' .
        $streetNumber .
        '",
                            "PostalCode": "' .
        $postalCode .
        '",
                            "CityName": {
                                "CityCode": "' .
        $cityCode .
        '"
                            },
                            "CountryName": {
                                "Code": "' .
        $countryCode .
        '"
                            }
                        },
                        "AgencyName": "' .
        $agencyName .
        '",
                         "AgencyContact": {
                             "Mobile": "' .
        $agencyPhone .
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
        CURLOPT_URL => 'https://api.platform.sabre.com/v2.5.0/passenger/records?mode=create',
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

function saveBooking(
    $conn,
    $guestInfo,
    $bookingPnr,
    $agentId,
    $staffId,
    $subAgentId,
    $userId,
    $adultCount,
    $childCount,
    $rooms,
    $checkIn,
    $checkOut,
    $platform,
    $uId,
    $email,
    $phone,
    $refundable,
    $guestPassengerName,
    $netCost,
    $system
) {
    if (!empty($bookingPnr)) {
        $bookingId = '';
        $query =
            'SELECT id, bookingId FROM hotel_booking ORDER BY bookingId DESC LIMIT 1';
        $result = $conn->query($query);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $number = (int) filter_var(
                    $row['bookingId'],
                    FILTER_SANITIZE_NUMBER_INT
                );
                $newnumber = $number + 1;
                $bookingId = "STHB$newnumber";
            }
        } else {
            $bookingId = 'STHB1';
        }

        $bookedAt = date('Y-m-d H:i:s');
        $status = "Booked";
        // Assuming $conn is your database connection object

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
    `adultCount`,
    `childCount`,
    `rooms`,
    `checkin`,
    `checkout`,
    `netCost`,
    `bookedAt`,
    `status`,
    `system`
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?,?)";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Bind parameters to the prepared statement
            $stmt->bind_param(
                'ssssssssssssssssssiii',
                $uId,
                $bookingId,
                $userId,
                $agentId,
                $staffId,
                $subAgentId,
                $email,
                $phone,
                $guestPassengerName,
                $refundable,
                $bookingPnr,
                $platform,
                $adultCount,
                $childCount,
                $rooms,
                $checkIn,
                $checkOut,
                $netCost,
                $bookedAt,
                $status,
                $system
            );

            // Execute the prepared statement
            if ($stmt->execute()) {
                addPax($conn, $guestInfo);

                $response['status'] = 'success';
                $response['BookingId'] = $bookingId;
                $response['platform'] = $platform;
                $response['message'] = 'Booking Successfully';
                echo json_encode($response);
            } else {
                // Handle execution errors
                echo 'Error executing the statement: ' . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        } else {
            // Handle statement preparation error
            echo 'Error preparing the statement: ' . $conn->error;
        }
    }
}
function addPax($conn, $guestInfo)
{
    foreach ($guestInfo as $index => $guest) {
        $paxId = '';
        $result = $conn->query(
            'SELECT * FROM hotel_passengers ORDER BY id DESC LIMIT 1'
        );
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row['paxId']);
                $number = (int) $outputString + 1;
                $paxId = "STHP$number";
            }
        } else {
            $paxId = 'STHP1000';
        }
        $type = $guest['type'];
        $fName = $guest['fName'];
        $lName = $guest['lName'];
        $phone = $guest['phone'];
        $email = $guest['email'];

        $query = "INSERT INTO `hotel_passengers` (`paxId`, `type`, `fName`, `lName`, `phone`, `email`) 
            VALUES ('$paxId', '$type', '$fName', '$lName', '$phone', '$email')";

        if ($conn->query($query) === true) {
            $response['status'] = 'success';
            $response['message'] = 'Traveler Added Successfully';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Traveler Added Failed';
        }
    }
}
