<?php
include './utils.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE');
header('Access-Control-Max-Age: 3600');
header(
    'Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'
);

if (array_key_exists('ccode', $_GET) && array_key_exists('hcode', $_GET)&&array_key_exists('ccontext', $_GET)) {

    $categoryCode = $_GET['ccode']; //TODO: category code
    $hotelCode = $_GET['hcode']; //TODO: hotel code
    $ccontext = $_GET['ccontext']; //TODO: hotel code
    $accessToken = getProdToken(); //TODO: return token
    $requestBody = getImageRQ($categoryCode, $hotelCode, $ccontext); //TODO: return

    // echo $accessToken;
    // echo $requestBody;

    if (isset($requestBody)) {
        getImage($requestBody, $accessToken);
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

function getImageRQ($categoryCode, $hotelCode, $ccontext)
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
                "CodeContext": "'.$ccontext.'"
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

        $HotelImageInfos =$responseData['GetHotelImageRS']['HotelImageInfos']['HotelImageInfo'];

        // TODO: Modify Result
        if (isset($HotelImageInfos)) {
            $simpleArrayOfObjects = [];
            foreach ($HotelImageInfos as $item) {
                $simpleArrayOfObjects[] = (object)$item;
            }
            echo json_encode($simpleArrayOfObjects);
        } else {
            echo json_encode($responseData);
        }
    }

    curl_close($curl);
}
