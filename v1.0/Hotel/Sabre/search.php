<?php

// include '../../../config.php';
include './utils.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE');
header('Access-Control-Max-Age: 3600');
header(
    'Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'
);

$allResponse = [];
$FlightType;

if (
    array_key_exists('location', $_GET) &&
    array_key_exists('checkin', $_GET) &&
    array_key_exists('checkout', $_GET) &&
    array_key_exists('adult', $_GET) &&
    array_key_exists('child', $_GET) &&
    array_key_exists('rooms', $_GET)
) {
    $location = $_GET['location'];
    $checkin = $_GET['checkin'];
    $checkout = $_GET['checkout'];
    $adult = $_GET['adult'];
    $child = $_GET['child'];
    $rooms = $_GET['rooms'];
    $countryCode = 'BD';
    $travelerCountryCode = 'BD';

    $url = 'https://api.platform.sabre.com/v4.1.0/get/hotelavail';

    $accessToken = getProdToken();
    // $accessToken = getCertToken();
    // echo json_encode($accessToken);
    $requestBody = sabreSearchRQ(
        $location,
        $checkin,
        $checkout,
        $adult,
        $child,
        $rooms
    );

    // echo $requestBody;

    $result = searchHotel(
        $url,
        $accessToken,
        $requestBody,
        $rooms,
        $adult,
        $child,
        $checkin,
        $checkout
    );

    if (isset($result)) {
        echo $result;
    } else {
        $response = [];
        $response['status'] = 'error';
        $response['message'] = 'No results found';

        echo json_encode($response);
    }
} else {
    $response = [];
    $response['status'] = 'error';
    $response['message'] = 'Invalid Request';

    echo json_encode($response);
}

function sabreSearchRQ($location, $checkin, $checkout, $adult, $child, $rooms)
{
    // Create an array of room configurations based on the number of rooms
    $room = [];

    for ($i = 0; $i < $rooms; $i++) {
        $room[] = [
            'Index' => $i + 1,
            'Adults' => intval($adult),
            'Children' => intval($child),
        ];

        if (intval($child) !== 0) {
            $room[$i]['ChildAges'] = '1';
        }
    }

    $requestBody =
        '{
    "GetHotelAvailRQ": {
      "SearchCriteria": {
        "OffSet": 1,
        "SortBy": "TotalRate",
        "SortOrder": "ASC",
        "PageSize": 200,
        "TierLabels": false,
        "GeoSearch": {
          "GeoRef": {
            "Radius": 20,
            "UOM": "MI",
            "RefPoint": {
              "Value": "' .
        $location .
        '",
              "ValueContext": "CODE",
              "RefPointType": "6"
            }
          }
        },
        "RateInfoRef": {
          "CurrencyCode": "BDT",
          "BestOnly": "1",
          "PrepaidQualifier": "IncludePrepaid",
          "RefundableOnly": false,
          "ConvertedRateInfoOnly": false,
          "StayDateTimeRange": {
            "StartDate": "' .
        $checkin .
        '",
            "EndDate": "' .
        $checkout .
        '"
          },
          "Rooms": {
            "Room": ' .
        json_encode($room) .
        '
          },
          "RateSource": ""
        },
        "ImageRef": {
          "Type": "MEDIUM",
          "LanguageCode": "EN"
        }
      }
    }
  }
  ';

    return $requestBody;
}

function searchHotel(
    $url,
    $accessToken,
    $requestBody,
    $rooms,
    $adult,
    $child,
    $checkin,
    $checkout
) {
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
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
            'Authorization: Bearer ' . $accessToken,
        ],
    ]);

    $response = curl_exec($curl);
    // TODO: Check for cURL errors
    if (curl_errno($curl)) {
        echo 'cURL Error: ' . curl_error($curl);
    }

    // TODO:Check the HTTP response code
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ($httpCode != 200) {
        echo 'HTTP Error: ' . $httpCode;
    } else {
        // TODO:Output the API response
        //TODO: Making AfterSearch Start

        $responseData = json_decode($response, true);

        // TODO: Marking Search Result
        if (
            isset(
                $responseData['GetHotelAvailRS']['HotelAvailInfos'][
                    'HotelAvailInfo'
                ]
            )
        ) {
            //TODO: all hotel Information are in the journeyList
            $journeyList =
                $responseData['GetHotelAvailRS']['HotelAvailInfos'][
                    'HotelAvailInfo'
                ];

            foreach ($journeyList as $singleHotel) {
                $hotelInfo = isset($singleHotel['HotelInfo'])
                    ? $singleHotel['HotelInfo']
                    : '';
                $locationInfo = isset($hotelInfo['LocationInfo'])
                    ? $hotelInfo['LocationInfo']
                    : '';
                $amenitiesInfo = isset($hotelInfo['Amenities']['Amenity'])
                    ? $hotelInfo['Amenities']['Amenity']
                    : '';
                $securityFeatures = isset(
                    $hotelInfo['SecurityFeatures']['SecurityFeature']
                )
                    ? $hotelInfo['SecurityFeatures']['SecurityFeature']
                    : '';
                $propertyTypeInfo = isset(
                    $hotelInfo['PropertyQualityInfo']['PropertyQuality']
                )
                    ? $hotelInfo['PropertyQualityInfo']['PropertyQuality']
                    : '';
                $hotelRateInfo = isset($singleHotel['HotelRateInfo'])
                    ? $singleHotel['HotelRateInfo']
                    : '';
                $rateInfo = isset($hotelRateInfo['RateInfos'])
                    ? $hotelRateInfo['RateInfos']
                    : '';
                $roomInfo = isset($hotelRateInfo['Rooms'])
                    ? $hotelRateInfo['Rooms']['Room']
                    : [];
                $refundable = '';

                if (
                    isset(
                        $roomInfo[0]['RatePlans']['RatePlan'][0]['RateInfo'][
                            'CancelPenalties'
                        ]['CancelPenalty'][0]['Refundable']
                    )
                ) {
                    $isRefundable =
                        $roomInfo[0]['RatePlans']['RatePlan'][0]['RateInfo'][
                            'CancelPenalties'
                        ]['CancelPenalty'][0]['Refundable'];

                    if ($isRefundable) {
                        $refundable = 'Refundable';
                    } else {
                        $refundable = 'Non Refundable';
                    }
                }
                $hotelImageInfo = isset($singleHotel['HotelImageInfo'])
                    ? $singleHotel['HotelImageInfo']['ImageItem']
                    : [];
                $system = 'sabre';
                $uId = sha1(md5(time()) . '' . rand());
                $hotelCode = isset($hotelInfo['HotelCode'])
                    ? $hotelInfo['HotelCode']
                    : '';
                $codeContext = isset($hotelInfo['CodeContext'])
                    ? $hotelInfo['CodeContext']
                    : '';
                $chainCode = isset($hotelInfo['ChainCode'])
                    ? $hotelInfo['ChainCode']
                    : '';
                $chainName = isset($hotelInfo['ChainName'])
                    ? $hotelInfo['ChainName']
                    : '';
                $brandCode = isset($hotelInfo['BrandCode'])
                    ? $hotelInfo['BrandCode']
                    : '';
                $brandName = isset($hotelInfo['BrandName'])
                    ? $hotelInfo['BrandName']
                    : '';
                $distance = isset($hotelInfo['Distance'])
                    ? $hotelInfo['Distance']
                    : '';
                $direction = isset($hotelInfo['Direction'])
                    ? $hotelInfo['Direction']
                    : '';
                $logo = isset($hotelInfo['Logo']) ? $hotelInfo['Logo'] : '';
                $name = isset($hotelInfo['HotelName'])
                    ? $hotelInfo['HotelName']
                    : '';
                    $location = [
                      'latitude' => isset($locationInfo['Latitude']) ? $locationInfo['Latitude'] : '',
                      'longitude' => isset($locationInfo['Longitude']) ? $locationInfo['Longitude'] : '',
                      'addressLine1' => isset($locationInfo['Address']['AddressLine1']) ? $locationInfo['Address']['AddressLine1'] : '',
                      'addressLine2' => isset($locationInfo['Address']['AddressLine2']) ? $locationInfo['Address']['AddressLine2'] : '',
                      'cityCode' => isset($locationInfo['Address']['CityName']['CityCode']) ? $locationInfo['Address']['CityName']['CityCode'] : '',
                      'cityName' => isset($locationInfo['Address']['CityName']['value']) ? $locationInfo['Address']['CityName']['value'] : '',
                      'postalCode' => isset($locationInfo['Address']['PostalCode']) ? $locationInfo['Address']['PostalCode'] : '',
                      'countryCode' => isset($locationInfo['Address']['CountryName']['Code']) ? $locationInfo['Address']['CountryName']['Code'] : '',
                      'countryName' => isset($locationInfo['Address']['CountryName']['value']) ? $locationInfo['Address']['CountryName']['value'] : '',
                      'phone' => isset($locationInfo['Contact']['Phone']) ? $locationInfo['Contact']['Phone'] : '',
                      'fax' => isset($locationInfo['Contact']['Fax']) ? $locationInfo['Contact']['Fax'] : '',
                  ];
                  
                $priceInfo = flattenObject($rateInfo['ConvertedRateInfo'][0]);
                $rating = isset($hotelInfo['SabreRating'])
                    ? $hotelInfo['SabreRating']
                    : 0;
                $amenities = convertKeysToCamelCase($amenitiesInfo);
                $imageInfo = isset($hotelImageInfo)
                    ? flattenObject($hotelImageInfo)
                    : '';

                $searchInfo = [
                    'rooms' => intval($rooms),
                    'adult' => intval($adult),
                    'child' => intVal($child),
                    'checkIn' => date('Y-m-d\TH:i', strtotime($checkin)),
                    'checkOut' => date('Y-m-d\TH:i', strtotime($checkout)),
                ];

                $allResponse[] = [
                    'system' => $system,
                    'uId' => $uId,
                    'searchInfo' => $searchInfo,
                    'hotelCode' => $hotelCode,
                    'codeContext' => $codeContext,
                    'chainCode' => $chainCode,
                    'chainName' => $chainName,
                    'brandCode' => $brandCode,
                    'brandName' => $brandName,
                    'distance' => $distance,
                    'direction' => $direction,
                    'logo' => $logo,
                    'name' => $name,
                    'refundable' => $refundable,
                    'locationInfo' => $location,
                    'priceInfo' => $priceInfo,
                    'rating' => $rating,
                    'amenities' => $amenities,
                    'imageInfo' => $imageInfo,
                    'securityFeatures' => $securityFeatures,
                    'propertyTypeInfo' => $propertyTypeInfo,
                    'roomInfo' => $roomInfo,
                ];
            }

            //Todo: Returning Search Result
            if (isset($allResponse)) {
                return json_encode($allResponse);
                // return json_encode($journeyList);
            } else {
                $response = [];
                $response['status'] = 'error';
                $response['message'] = 'no result found';

                return json_encode($response);
            }
        } else {
            return json_encode($responseData);
        }
    }

    curl_close($curl);
}

// Function to create a single object with error handling
function createSingleObject($locationInfo)
{
    $singleObject = [];

    // Check if essential keys are present
    if (
        isset($locationInfo['Latitude']) &&
        isset($locationInfo['Longitude']) &&
        isset($locationInfo['Address']) &&
        isset($locationInfo['Contact'])
    ) {
        $singleObject['Latitude'] = $locationInfo['Latitude'];
        $singleObject['Longitude'] = $locationInfo['Longitude'];

        $address = $locationInfo['Address'];
        if (
            isset($address['AddressLine1']) &&
            isset($address['AddressLine2']) &&
            isset($address['CityName']['CityCode']) &&
            isset($address['CityName']['value']) &&
            isset($address['PostalCode']) &&
            isset($address['CountryName']['Code']) &&
            isset($address['CountryName']['value'])
        ) {
            $singleObject['Address'] = $address;
        } else {
            // Handle missing address information error
            $singleObject['Address'] = 'Incomplete Address Information';
        }

        $contact = $locationInfo['Contact'];
        if (isset($contact['Phone']) && isset($contact['Fax'])) {
            $singleObject['Contact'] = $contact;
        } else {
            // Handle missing contact information error
            $singleObject['Contact'] = 'Incomplete Contact Information';
        }
    } else {
        // Handle missing essential keys error
        $singleObject = 'Incomplete Location Information';
    }

    return $singleObject;
}

function convertKeysToCamelCase($array)
{
    $result = [];

    foreach ($array as $item) {
        $camelCaseItem = [];

        foreach ($item as $key => $value) {
            // Convert the key to camelCase
            $camelCaseKey = lcfirst(
                str_replace(' ', '', ucwords(str_replace('_', ' ', $key)))
            );

            $camelCaseItem[$camelCaseKey] = $value;
        }

        $result[] = $camelCaseItem;
    }

    return $result;
}

function flattenObject($data, $prefix = '')
{
    $result = new stdClass();

    foreach ($data as $key => $value) {
        if (is_object($value) || is_array($value)) {
            // If the value is an object or an array, recursively flatten it
            $nestedData = flattenObject($value, $prefix . camelCase($key));
            foreach ($nestedData as $nestedKey => $nestedValue) {
                $result->{$nestedKey} = $nestedValue;
            }
        } else {
            // If the value is a scalar, add it to the result object with camelCase key
            $result->{camelCase($prefix . $key)} = $value;
        }
    }

    return $result;
}

function camelCase($str)
{
    // Remove underscores and convert to camel case
    $str = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $str))));
    return $str;
}
