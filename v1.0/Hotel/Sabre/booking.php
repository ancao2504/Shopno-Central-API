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

    $guestInfo = isset($_POST['guestInfo']) ? $_POST['guestInfo'] : [];
    $paymentInfo = isset($_POST['paymentInfo']) ? $_POST['paymentInfo'] : [];
    $agentId = isset($_POST['agentId']) ? $_POST['agentId'] : '';
    $staffId = isset($_POST['staffId']) ? $_POST['staffId'] : '';
    $subAgentId = isset($_POST['subAgentId']) ? $_POST['subAgentId'] : '';
    $userId = isset($_POST['userId']) ? $_POST['userId'] : '';
    $system = isset($_POST['system']) ? $_POST['system'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $bookingKey = isset($_POST['bookingKey']) ? $_POST['bookingKey'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $adultCount = isset($_POST['adultCount']) ? $_POST['adultCount'] : '';
    $childCount = isset($_POST['childCount']) ? $_POST['childCount'] : '';
    $totalPax = $adultCount + $childCount;
    $rooms = isset($_POST['rooms']) ? $_POST['rooms'] : '';
    $checkIn = isset($_POST['checkIn']) ? $_POST['checkIn'] : '';
    $checkOut = isset($_POST['checkOut']) ? $_POST['checkOut'] : '';
    $platform = isset($_POST['platform']) ? $_POST['platform'] : '';
    $refundable = isset($_POST['refundable']) ? $_POST['refundable'] : '';
    $uId = sha1(md5(time()) . '' . rand());
    $netCost = isset($_POST['netCost']) ? $_POST['netCost'] : 0;
    $hotelName = isset($_POST['hotelName']) ? $_POST['hotelName'] : '';
    $hotelCode = isset($_POST['hotelCode']) ? $_POST['hotelCode'] : '';
    $guestPassengerName = isset($_POST['guestPassengerName'])
        ? $_POST['guestPassengerName']
        : '';

    $pcc = '27YK';

    // $accessToken = getToken();
    $accessToken = getProdToken();

    $requestBody = sabreRequestBody(
        $pcc,
        $guestInfo,
        $paymentInfo,
        $phone,
        $bookingKey
    );

    // echo $requestBody;
    // echo $accessToken;

    $result = sabreHotelBooking($accessToken, $requestBody);

    // echo $result;

    // $filePath = './test.json';
    // $result = file_get_contents($filePath);

    $response = json_decode($result, true);
    $bookingPnr = isset(
        $response['CreatePassengerNameRecordRS']['ItineraryRef']['ID']
    )
        ? $response['CreatePassengerNameRecordRS']['ItineraryRef']['ID']
        : '';

    if (!empty($bookingPnr)) {
        // echo $result;
        // echo $requestBody

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
            $totalPax,
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
            $system,
            $hotelName,
            $hotelCode,
            $paymentInfo
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

function sabreRequestBody($pcc, $guestInfo, $paymentInfo, $phone, $bookingKey)
{
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
    $expiryMonth = ltrim(date('n', strtotime($expiryDate)), '0');
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
    $totalPax,
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
    $system,
    $hotelName,
    $hotelCode,
    $paymentInfo
) {
    if (!empty($bookingPnr)) {
        $bookingId = '';
        $query =
            'SELECT id, bookingId FROM hotel_booking ORDER BY id DESC LIMIT 1';
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $number = (int) filter_var(
                $row['bookingId'],
                FILTER_SANITIZE_NUMBER_INT
            );
            $newnumber = $number + 1;
            $bookingId = "STHB$newnumber";
        } else {
            $bookingId = 'STHB1000';
        }

        $bookedAt = (new DateTime())->format('Y-m-d\TH:i:s');
        $status = 'Booked';

        $sql = "INSERT INTO `hotel_booking` (
            `uid`,
            `bookingId`,
            `userId`,
            `agentId`,
            `staffId`,
            `subagentId`,
            `hotelName`,
            `hotelCode`,
            `email`,
            `phone`,
            `name`,
            `refundable`,
            `pnr`,
            `platform`,
            `adultCount`,
            `childCount`,
            `pax`,
            `rooms`,
            `checkin`,
            `checkout`,
            `netCost`,
            `bookedAt`,
            `status`,
            `system`
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?, ?)";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param(
                'ssssssssssssssssssssssss',
                $uId,
                $bookingId,
                $userId,
                $agentId,
                $staffId,
                $subAgentId,
                $hotelName,
                $hotelCode,
                $email,
                $phone,
                $guestPassengerName,
                $refundable,
                $bookingPnr,
                $platform,
                $adultCount,
                $childCount,
                $totalPax,
                $rooms,
                $checkIn,
                $checkOut,
                $netCost,
                $bookedAt,
                $status,
                $system
            );

            if ($stmt->execute()) {
                addPax($conn, $agentId, $bookingId, $bookingPnr, $guestInfo);
                savePaymentInfo($conn, $bookingId, $agentId, $paymentInfo);

                $response['status'] = 'success';
                $response['BookingId'] = $bookingId;
                $response['platform'] = $platform;
                $response['message'] = 'Booking Successfully';
                echo json_encode($response);
            } else {
                echo 'Error executing the statement: ' . $stmt->error;
                $response['status'] = 'error';
                $response['message'] = 'Booking Failed';
                $response['error_details'] = $stmt->error;
                echo json_encode($response);
            }

            $stmt->close();
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Booking Failed';
            echo json_encode($response);
        }
    }
}

function addPax($conn, $agentId, $bookingPnr, $bookingId, $guestInfo)
{
    $response = []; // Initialize an empty response array

    foreach ($guestInfo as $index => $guest) {
        $paxId = '';
        $lastPaxId = '';

        $stmt = $conn->prepare(
            'SELECT paxId FROM hotel_passengers ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $lastPaxId = $row['paxId'];
            $number = preg_replace('/[^0-9]/', '', $lastPaxId);
            $paxId = 'STHP' . ($number + 1);
        } else {
            $paxId = 'STHP1000';
        }

        $type = $guest['type'];
        $fName = $guest['fName'];
        $lName = $guest['lName'];
        $phone = $guest['phone'];
        $email = $guest['email'];

        $createdAt = (new DateTime())->format('Y-m-d\TH:i:s');

        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO `hotel_passengers` (`paxId`, `agentId`, `bookingId`, `pnr`, `type`, `fName`, `lName`, `phone`, `email`, `createdAt`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)");

        $stmt->bind_param(
            'ssssssssss',
            $paxId,
            $agentId,
            $bookingId,
            $bookingPnr,
            $type,
            $fName,
            $lName,
            $phone,
            $email,
            $createdAt
        );

        if ($stmt->execute()) {
            $response[$index]['status'] = 'success';
            $response[$index]['message'] = 'Traveler Added Successfully';
        } else {
            $response[$index]['status'] = 'error';
            $response[$index]['message'] = 'Traveler Added Failed';
        }
    }

    return $response;
}

// TODO:Function to insert data into HOTEL_PAYMENT_INFO
function savePaymentInfo($conn, $bookingId, $agentId, $paymentInfo)
{
    $response = [];

    // TODO:Variables for payment ID GENERATION
    $paymentId = '';
    $lastPaymentId = '';

    // TODO:Fetch the last payment ID from the DATABASE
    $stmt = $conn->prepare(
        'SELECT paymentId FROM hotel_payment_info ORDER BY id DESC LIMIT 1'
    );
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastPaymentId = $row['paymentId'];
        $number = preg_replace('/[^0-9]/', '', $lastPaymentId);
        $paymentId = 'STHPAY' . ($number + 1);
    } else {
        $paymentId = 'STHPAY1000';
    }

    //TODO: EXTRACT PAYMENT INFORMATION SECURELY
    $paymentType = isset($paymentInfo['paymentType'])
        ? $paymentInfo['paymentType']
        : '';
    $cardCode = isset($paymentInfo['cardCode']) ? $paymentInfo['cardCode'] : '';
    $cardNumber = isset($paymentInfo['cardNumber']) ? $paymentInfo['cardNumber'] : '';
    $expiryDate = isset($paymentInfo['expiryDate'])
        ? $paymentInfo['expiryDate']
        : '';
    $holderFName = isset($paymentInfo['holderFName'])
        ? $paymentInfo['holderFName']
        : '';
    $holderLName = isset($paymentInfo['holderLName'])
        ? $paymentInfo['holderLName']
        : '';
    $holderEmail = isset($paymentInfo['holderEmail'])
        ? $paymentInfo['holderEmail']
        : '';
    $holderPhone = isset($paymentInfo['holderPhone'])
        ? $paymentInfo['holderPhone']
        : '';
    $csc = isset($paymentInfo['csc']) ? $paymentInfo['csc'] : '';
    $address = isset($paymentInfo['address']) ? $paymentInfo['address'] : '';
    $cityName = isset($paymentInfo['cityName']) ? $paymentInfo['cityName'] : '';
    $streetNumber = isset($paymentInfo['streetNumber'])
        ? $paymentInfo['streetNumber']
        : '';
    $stateCode = isset($paymentInfo['stateCode'])
        ? $paymentInfo['stateCode']
        : '';
    $cityCode = isset($paymentInfo['cityCode']) ? $paymentInfo['cityCode'] : '';
    $postalCode = isset($paymentInfo['postalCode'])
        ? $paymentInfo['postalCode']
        : '';
    $countryCode = isset($paymentInfo['countryCode'])
        ? $paymentInfo['countryCode']
        : '';

    //TODO: GET THE CURRENT TIMESTAMP FOR CREATEDAT FIELD
    $createdAt = (new DateTime())->format('Y-m-d\TH:i:s');

    //TODO: PREPARE SQL STATEMENT TO INSERT DATA USING PREPARED STATEMENTS
    $sql = "INSERT INTO `hotel_payment_info` 
            (`paymentType`, `cardCode`,`cardNumber`, `expiryDate`, `holderFName`, `holderLName`, `holderEmail`, `holderPhone`, `csc`, `address`, `cityName`, `streetNumber`, `stateCode`, `cityCode`, `postalCode`, `countryCode`, `paymentId`, `bookingId`, `agentId`, `createdAt`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        'ssssssssssssssssssss',
        $paymentType,
        $cardCode,
        $cardNumber,
        $expiryDate,
        $holderFName,
        $holderLName,
        $holderEmail,
        $holderPhone,
        $csc,
        $address,
        $cityName,
        $streetNumber,
        $stateCode,
        $cityCode,
        $postalCode,
        $countryCode,
        $paymentId,
        $bookingId,
        $agentId,
        $createdAt
    );

   if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Payment Info Added Successfully';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Payment Info Added Failed';
        }

        return $response;
}
