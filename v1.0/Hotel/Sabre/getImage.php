<?php
include './utils.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE');
header('Access-Control-Max-Age: 3600');
header(
    'Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'
);

if (array_key_exists('hcode', $_GET) && array_key_exists('ccontext', $_GET)) {

    $hotelCode = $_GET['hcode']; //TODO: hotel code
    $ccontext = $_GET['ccontext']; //TODO: hotel code
    $accessToken = getProdToken(); //TODO: return token
    $requestBody = getImageRQ($hotelCode, $ccontext); //TODO: return

    // echo $accessToken;
    // echo $requestBody;

    if (isset($requestBody)) {
        $result = getImage($requestBody, $accessToken);

        $HotelImageInfos = count($result['GetHotelImageRS']['HotelImageInfos']) !== 0 ? $result['GetHotelImageRS']['HotelImageInfos']['HotelImageInfo'] : [];

        // TODO: Modify Result
        if (count($HotelImageInfos) !== 0) {
            $response = [];

            foreach ($HotelImageInfos as $hotelImageInfo) {
                $hotelInfo = $hotelImageInfo['HotelInfo'];
                $hotelCode = $hotelInfo['HotelCode'];
                $codeContext = $hotelInfo['CodeContext'];
                $chainCode = $hotelInfo['ChainCode'];
                $marketer = $hotelInfo['Marketer'];

                $imageItem = $hotelInfo['ImageItem'];
                $image = isset($imageItem['Image']) ? [
                    'url' => $imageItem['Image']['Url'],
                    'type' => $imageItem['Image']['Type'],
                    'height' => $imageItem['Image']['Height'],
                    'width' => $imageItem['Image']['Width'],
                ] : "";
                $category = isset($imageItem['Category']) ? [
                    'categoryCode' => $imageItem['Category']['CategoryCode'],
                    'language' => $imageItem['Category']['Description']['Text'][0]['Language'],
                    'content' => $imageItem['Category']['Description']['Text'][0]['content'],
                ] : "";
                $additionalInfo = isset($imageItem['AdditionalInfo']) ? [
                    'type' => $imageItem['AdditionalInfo']['Info'][0]['Type'],
                    'description' => $imageItem['AdditionalInfo']['Info'][0]['Description'][0]
                ] : "";

                $response[] = [
                    'hotelCode' => $hotelCode,
                    'codeContext' => $codeContext,
                    'chainCode' => $chainCode,
                    'marketer' => $marketer,
                    'image' => $image,
                    'category' => $category,
                    'additionalInfo' => $additionalInfo,
                ];
            };




            echo json_encode($response);
        } else {
            $response = [];
            $response['status'] = 'error';
            $response['message'] = 'No Image found';

            echo json_encode($response);
        }
    } else {
        $errMessage['status'] = 'error';
        $errMessage['message'] = 'Invalid Request';

        echo json_encode($errMessage);
    }
} else {
    $errMessage['status'] = 'error';
    $errMessage['message'] = 'Invalid Request';

    echo json_encode($errMessage);
}




// "CategoryCode": '.$categoryCode.',

function getImageRQ($hotelCode, $ccontext)
{
    $requestBody =
        '{
        "GetHotelImageRQ": {
          "ImageRef": {
            "LanguageCode": "EN",
            "Type": "ORIGINAL"
          },
          "HotelRefs": {
            "HotelRef": [
              {
                "HotelCode": "' .
        $hotelCode .
        '",
                "CodeContext": "' . $ccontext . '"
              }
            ]
          }
        }
      }';
    return $requestBody;
}

function getImage($requestBody, $accessToken)
{
    $url = 'https://api.platform.sabre.com/v1.0.0/shop/hotels/image?mode=image';

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $accessToken,
    ]);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $requestBody);

    $response = curl_exec($curl);

    $responseData = json_decode($response, true);


    // TODO:Check the HTTP response code
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if ($httpCode != 200) {
        $response = [];
        $response['status'] = 'error';
        $response['code'] = $httpCode;
        $response['message'] = 'Invalid Request';

        echo json_encode($response);
    } else {
        // TODO:Output the API response
        //TODO: Making Modify

        $responseData = json_decode($response, true);

        return $responseData;
    }

    curl_close($curl);
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
