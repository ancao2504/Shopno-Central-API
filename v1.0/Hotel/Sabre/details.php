<?php

include './utils.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE');
header('Access-Control-Max-Age: 3600');
header(
    'Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'
);

$FlightType;

if (
    array_key_exists('code', $_GET) &&
    array_key_exists('checkin', $_GET) &&
    array_key_exists('checkout', $_GET) &&
    array_key_exists('adult', $_GET) &&
    array_key_exists('child', $_GET) &&
    array_key_exists('rooms', $_GET)
) {
    $hotelCode = $_GET['code'];
    $checkin = $_GET['checkin'];
    $checkout = $_GET['checkout'];
    $adult = $_GET['adult'];
    $child = $_GET['child'];
    $rooms = $_GET['rooms'];
    // $accessToken = getCertToken();
    $accessToken = getProdToken();

    $url = 'https://api.platform.sabre.com/v3.0.0/get/hoteldetails';
    // $url = 'https://api.platform.sabre.com/v3.0.0/get/hoteldetails';
    $requestBody = sabreHotelDetailsRQ($hotelCode, $checkin, $checkout, $adult, $child, $rooms);

    $result = getHotelDetails($url, $accessToken, $requestBody);

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

function sabreHotelDetailsRQ($hotelCode, $checkin, $checkout, $adult, $child, $rooms)
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
            $room[$i]['ChildAges'] = "1";
        }
    }

    $requestBody =
        '{
    "GetHotelDetailsRQ": {
      "SearchCriteria": {
        "HotelRefs": {
          "HotelRef": {
            "HotelCode": "' .
        $hotelCode .
        '",
            "CodeContext": "GLOBAL"
          }
        },
        "RateInfoRef": {
          "CurrencyCode": "BDT",
          "PrepaidQualifier": "IncludePrepaid",
          "RefundableOnly": true,
          "ConvertedRateInfoOnly": true,
          "ShowNegotiatedRatesFirst": true,
          "StayDateTimeRange": {
            "StartDate": "' . $checkin . '",
            "EndDate": "' . $checkout . '"
          },
          "Rooms": {
            "Room": ' . json_encode($room) . '
          }
        },
        "HotelContentRef": {
          "DescriptiveInfoRef": {
            "PropertyInfo": true,
            "LocationInfo": true,
            "Amenities": true,
            "Descriptions": {
              "Description": [
                {
                  "Type": "ShortDescription"
                }
              ]
            },
            "SecurityFeatures": true
          },
          "MediaRef": {
            "MaxItems": "10",
            "MediaTypes": {
              "Images": {
                "Image": [
                  {
                    "Type": "MEDIUM"
                  }
                ]
              },
              "PanoramicMedias": {
                "PanoramicMedia": [
                  {
                    "Type": "HD360"
                  }
                ]
              },
              "Videos": {
                "Video": [
                  {
                    "Type": "VIDEO360"
                  }
                ]
              }
            },
            "Categories": {
              "Category": [
                {
                  "Code": 1
                }
              ]
            },
            "AdditionalInfo": {
              "Info": [
                {
                  "Type": "CAPTION",
                  "value": true
                },
                {
                  "Type": "ROOM_TYPE_CODE",
                  "value": false
                }
              ]
            },
            "Languages": {
              "Language": [
                {
                  "Code": "EN"
                }
              ]
            }
          }
        }
      }
    }
  }';

    return $requestBody;
}

function getHotelDetails($url, $accessToken, $requestBody)
{
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
            'Conversation-ID: 2021.01.DevStudio',
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


        // TODO: Marking Hotel Details
        if (
            $responseData['GetHotelDetailsRS']['ApplicationResults']['status'] === 'Complete' &&
            isset($responseData['GetHotelDetailsRS']['HotelDetailsInfo'])
        ) {
            $system = 'sabre';
            $uId = sha1(md5(time()) . '' . rand());
            $detailsInfo = isset(
                $responseData['GetHotelDetailsRS']['HotelDetailsInfo']
            )
                ? $responseData['GetHotelDetailsRS']['HotelDetailsInfo']
                : '';
            $descriptiveInfo = isset($detailsInfo['HotelDescriptiveInfo'])
                ? $detailsInfo['HotelDescriptiveInfo']
                : '';
            $hotelRateInfo = isset($detailsInfo['HotelRateInfo'])
                ? $detailsInfo['HotelRateInfo']
                : '';

            $hotelInfo = isset($detailsInfo['HotelInfo'])
                ? $detailsInfo['HotelInfo']
                : '';
            $propertyInfo = isset($descriptiveInfo['PropertyInfo'])
                ? $descriptiveInfo['PropertyInfo']
                : '';
            $locationInfo = isset($descriptiveInfo['LocationInfo'])
                ? $descriptiveInfo['LocationInfo']
                : '';
            $amenities = isset($descriptiveInfo['Amenities'])
                ? $descriptiveInfo['Amenities']
                : '';
            $securityFeatures = isset($descriptiveInfo['SecurityFeatures'])
                ? $descriptiveInfo['SecurityFeatures']
                : '';
            $descriptions = [];

            if (isset($descriptiveInfo['Descriptions']['Description'])) {
                foreach ($descriptiveInfo['Descriptions']['Description']
                    as $description) {
                    $descriptions[] = [
                        'type' => $description['Text']['Type'],
                        'value' => $description['Text']['value'],
                    ];
                }
            } else {
                $descriptions = '';
            }
            $rateInfos = isset($hotelRateInfo['RateInfos'])
                ? $hotelRateInfo['RateInfos']
                : [];
            $roomSets = [];
            if (isset($hotelRateInfo['Rooms']['Room'])) {
                $roomSets =  $hotelRateInfo['Rooms']['Room'];
            } elseif (isset($hotelRateInfo['RoomSets']['RoomSet'])) {
                $roomSets =  $hotelRateInfo['RoomSets']['RoomSet'];
            } else {
                $roomSets =  [];
            }
            $roomSet = [];

            if (isset($roomSets)) {
                foreach ($roomSets as $key => $singleRoomSet) {

                    $bedTypes = [];
                    if (isset($singleRoomSet['BedTypeOptions']['BedTypes'])) {
                        foreach ($singleRoomSet['BedTypeOptions']['BedTypes'][0]['BedType']
                            as $singleBedTypes) {
                            $bedTypes[] = flattenObject($singleBedTypes);
                        }
                    }

                    $ratePlans = [];

                    foreach ($singleRoomSet['RatePlans']['RatePlan']
                        as $ratePlan) {

                        $ratePlans[] = [
                            'ratePlanName' => isset(
                                $ratePlan['RatePlanName']
                            )
                                ? $ratePlan['RatePlanName']
                                : '',
                            'ratePlanCode' => isset(
                                $ratePlan['RatePlanCode']
                            )
                                ? $ratePlan['RatePlanCode']
                                : '',
                            'prepaidIndicator' => isset(
                                $ratePlan['PrepaidIndicator']
                            )
                                ? $ratePlan['PrepaidIndicator']
                                : '',
                            'limitedAvailability' => isset(
                                $ratePlan['LimitedAvailability']
                            )
                                ? $ratePlan['LimitedAvailability']
                                : '',
                            'rateSource' => isset($ratePlan['RateSource'])
                                ? $ratePlan['RateSource']
                                : '',
                            'rateKey' => isset($ratePlan['RateKey'])
                                ? $ratePlan['RateKey']
                                : '',
                            'productCode' => isset($ratePlan['ProductCode'])
                                ? $ratePlan['ProductCode']
                                : '',
                            'breakfast' => isset(
                                $ratePlan['MealsIncluded']['Breakfast']
                            )
                                ? $ratePlan['MealsIncluded']['Breakfast']
                                : '',
                            'lunch' => isset(
                                $ratePlan['MealsIncluded']['Lunch']
                            )
                                ? $ratePlan['MealsIncluded']['Lunch']
                                : '',
                            'dinner' => isset(
                                $ratePlan['MealsIncluded']['Dinner']
                            )
                                ? $ratePlan['MealsIncluded']['Dinner']
                                : '',
                            'mealPlanCode' => isset(
                                $ratePlan['MealsIncluded']['MealPlanCode']
                            )
                                ? $ratePlan['MealsIncluded']['MealPlanCode']
                                : '',
                            'mealPlanDescription' => isset(
                                $ratePlan['MealsIncluded']['MealPlanDescription']
                            )
                                ? $ratePlan['MealsIncluded']['MealPlanDescription']
                                : '',
                            'startDate' => isset(
                                $ratePlan['ConvertedRateInfo']['StartDate']
                            )
                                ? $ratePlan['ConvertedRateInfo']['StartDate']
                                : '',
                            'endDate' => isset(
                                $ratePlan['ConvertedRateInfo']['EndDate']
                            )
                                ? $ratePlan['ConvertedRateInfo']['EndDate']
                                : '',
                            'amountBeforeTax' => isset(
                                $ratePlan['ConvertedRateInfo']['AmountBeforeTax']
                            )
                                ? $ratePlan['ConvertedRateInfo']['AmountBeforeTax']
                                : '',
                            'amountAfterTax' => isset(
                                $ratePlan['ConvertedRateInfo']['AmountAfterTax']
                            )
                                ? $ratePlan['ConvertedRateInfo']['AmountAfterTax']
                                : '',
                            'averageNightlyRate' => isset(
                                $ratePlan['ConvertedRateInfo']['AverageNightlyRate']
                            )
                                ? $ratePlan['ConvertedRateInfo']['AverageNightlyRate']
                                : '',
                            'averageNightlyRateBeforeTax' => isset(
                                $ratePlan['ConvertedRateInfo']['AverageNightlyRateBeforeTax']
                            )
                                ? $ratePlan['ConvertedRateInfo']['AverageNightlyRateBeforeTax']
                                : '',
                            'currencyCode' => isset(
                                $ratePlan['ConvertedRateInfo']['CurrencyCode']
                            )
                                ? $ratePlan['ConvertedRateInfo']['CurrencyCode']
                                : '',
                            'taxInclusive' => isset(
                                $ratePlan['ConvertedRateInfo']['TaxInclusive']
                            )
                                ? $ratePlan['ConvertedRateInfo']['TaxInclusive']
                                : '',
                            'amount' => isset(
                                $ratePlan['ConvertedRateInfo']['Taxes']['Amount']
                            )
                                ? $ratePlan['ConvertedRateInfo']['Taxes']['Amount']
                                : '',
                            'taxCurrencyCode' => isset(
                                $ratePlan['ConvertedRateInfo']['Taxes']['CurrencyCode']
                            )
                                ? $ratePlan['ConvertedRateInfo']['Taxes']['CurrencyCode']
                                : '',
                            'refundable' => isset(
                                $ratePlan['ConvertedRateInfo']['CancelPenalties']['CancelPenalty'][0]['Refundable']
                            )
                                ? $ratePlan['ConvertedRateInfo']['CancelPenalties']['CancelPenalty'][0]['Refundable']
                                : '',
                            'absoluteDeadline' => isset(
                                $ratePlan['ConvertedRateInfo']['CancelPenalties']['CancelPenalty'][0]['Deadline']['AbsoluteDeadline']
                            )
                                ? $ratePlan['ConvertedRateInfo']['CancelPenalties']['CancelPenalty'][0]['Deadline']['AbsoluteDeadline']
                                : '',
                            // 'guaranteesAccepted' => isset(
                            //     $ratePlan['ConvertedRateInfo']['Guarantee']['GuaranteesAccepted']['GuaranteeAccepted']
                            // )
                            //     ? $ratePlan['ConvertedRateInfo']['Guarantee']['GuaranteesAccepted']['GuaranteeAccepted']
                            //     : '',
                            'paymentCard' => isset(
                                $ratePlan['ConvertedRateInfo']['Guarantee']['GuaranteesAccepted']['GuaranteeAccepted']
                            )
                                ? convertKeysToCamelCase($ratePlan['ConvertedRateInfo']['Guarantee']['GuaranteesAccepted']['GuaranteeAccepted'][0]['PaymentCards']['PaymentCard'])
                                : [],
                        ];
                    }

                    $roomSet[] = [
                        'roomIndex' => $key + 1,
                        'roomType' => isset($room['RoomType']) ? $singleRoomSet['RoomType'] : "",
                        'roomTypeCode' => isset($room['RoomTypeCode']) ? $singleRoomSet['RoomTypeCode'] : "",
                        'bedType' => $bedTypes,
                        'roomName' =>
                        $singleRoomSet['RoomDescription']['Name'],
                        'roomNameText' =>
                        $singleRoomSet['RoomDescription']['Text'][0],
                        'amenities' => isset($singleRoomSet['Amenities']) ? convertKeysToCamelCase($singleRoomSet['Amenities']['Amenity']) : [],
                        'ratePlan' => isset($ratePlans)
                            ? (object) call_user_func_array(
                                'array_merge',
                                $ratePlans
                            )
                            : [],
                    ];
                }
            } else {
                $roomSet = [];
            }

            $allResponse = [
                'system' => $system,
                'uId' => $uId,
                'hotelInfo' => isset($hotelInfo)
                    ? flattenObject($hotelInfo)
                    : '',
                'propertyInfo' => isset($propertyInfo)
                    ? [
                        'rooms' => isset($propertyInfo['Rooms'])
                            ? $propertyInfo['Rooms']
                            : '',
                        'floors' => isset($propertyInfo['Floors'])
                            ? $propertyInfo['Floors']
                            : '',
                        'propertyType' => isset(
                            $propertyInfo['PropertyTypeInfo']['PropertyType'][0]['Description']
                        )
                            ? $propertyInfo['PropertyTypeInfo']['PropertyType'][0]['Description']
                            : '',
                        'checkInTime' => isset(
                            $propertyInfo['Policies']['Policy'][0]['Text']['value']
                        )
                            ? $propertyInfo['Policies']['Policy'][0]['Text']['value']
                            : '',
                        'checkOutTime' => isset(
                            $propertyInfo['Policies']['Policy'][1]['Text']['value']
                        )
                            ? $propertyInfo['Policies']['Policy'][1]['Text']['value']
                            : '',
                        'propertyQuality' => isset(
                            $propertyInfo['PropertyQualityInfo']['PropertyQuality'][0]['Description']
                        )
                            ? $propertyInfo['PropertyQualityInfo']['PropertyQuality'][0]['Description']
                            : '',
                    ]
                    : '',
                'locationInfo' => isset($locationInfo)
                    ? [
                        'latitude' => isset($locationInfo['Latitude'])
                            ? $locationInfo['Latitude']
                            : '',
                        'longitude' => isset($locationInfo['Longitude'])
                            ? $locationInfo['Longitude']
                            : '',
                        'addressLine1' => isset(
                            $locationInfo['Address']['AddressLine1']
                        )
                            ? $locationInfo['Address']['AddressLine1']
                            : '',
                        'addressLine2' => isset(
                            $locationInfo['Address']['AddressLine2']
                        )
                            ? $locationInfo['Address']['AddressLine2']
                            : '',
                        'cityCode' => isset(
                            $locationInfo['Address']['CityName']['CityCode']
                        )
                            ? $locationInfo['Address']['CityName']['CityCode']
                            : '',
                        'value' => isset(
                            $locationInfo['Address']['CityName']['value']
                        )
                            ? $locationInfo['Address']['CityName']['value']
                            : '',
                        'postalCode' => isset(
                            $locationInfo['Address']['PostalCode']
                        )
                            ? $locationInfo['Address']['PostalCode']
                            : '',
                        'countryCode' => isset(
                            $locationInfo['Address']['CountryName']['Code']
                        )
                            ? $locationInfo['Address']['CountryName']['Code']
                            : '',
                        'countryName' => isset(
                            $locationInfo['Address']['CountryName']['value']
                        )
                            ? $locationInfo['Address']['CountryName']['value']
                            : '',
                        'phone' => isset($locationInfo['Contact']['Phone'])
                            ? $locationInfo['Contact']['Phone']
                            : '',
                        'fax' => isset($locationInfo['Contact']['Fax'])
                            ? $locationInfo['Contact']['Fax']
                            : '',
                    ]
                    : '',
                'amenities' => isset($amenities['Amenity'])
                    ? convertKeysToCamelCase($amenities['Amenity'])
                    : '',
                'securityFeature' => isset($securityFeatures['SecurityFeature'])
                    ? convertKeysToCamelCase(
                        $securityFeatures['SecurityFeature']
                    )
                    : '',
                'shortDescription' => isset($descriptions) ? $descriptions : '',
                'rateInfos' => isset($rateInfos['ConvertedRateInfo'])
                    ? convertKeysToCamelCase($rateInfos['ConvertedRateInfo'])
                    : [],
                'roomSet' => isset($roomSet) ? $roomSet : [],
            ];

            //Todo: Returning Search Result
            $response = json_encode($allResponse);


            return $response;
        } else {
            return json_encode($responseData);
        }
    }

    curl_close($curl);
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
