<?php
include '../../../config.php';
include './utils.php';

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE');
header('Access-Control-Max-Age: 3600');
header(
    'Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'
);

if( array_key_exists('ccode',$_GET) && array_key_exists('hcode',$_GET)){

    $categoryCode = $_GET['ccode'];//TODO: category code
    $hotelCode = $_GET['hcode'];//TODO: hotel code
    $accessToken = getToken();//TODO: return token
    $requestBody = getImageRQ($categoryCode, $hotelCode); //TODO: return 
    
    if(isset($requestBody)){
        getImage($requestBody, $accessToken);
    }else{
        $errMessage['status'] = 'error';
        $errMessage['message'] = 'Invalid Request';

        echo json_encode($errMessage);
    }

}else{

    $errMessage['status'] = 'error';
    $errMessage['message'] = 'Invalid Request';

    echo json_encode($errMessage);
}

function getImageRQ($categoryCode, $hotelCode){
    $requestBody = '{
        "GetHotelImageRQ": {
          "ImageRef": {
            "CategoryCode": '.$categoryCode.',
            "LanguageCode": "EN",
            "Type": "ORIGINAL"
          },
          "HotelRefs": {
            "HotelRef": [
              {
                "HotelCode": '.$hotelCode.',
                "CodeContext": "Sabre"
              }
            ]
          }
        }
      }';
    return $requestBody; 
}

function getImage($requestBody, $accessToken){

    $curl = curl_init();

    curl_setopt_array($curl,[
        CURLOPT_URL => 'https://api.cert.platform.sabre.com/v1.0.0/shop/hotels/image',
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

    // echo $accessToken;

    $response = curl_exec($curl);

    // Check for cURL errors
    if(curl_errno($curl)){
        echo 'cURL Error: ' . curl_error($curl);
    }

    // Check the HTTP response code
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    if($httpCode != 200){
        echo 'HTTP Error: ' . $httpCode;
    } else {
        // Decode the response
        $responseData = json_decode($response, true);

        if($responseData === null){
            echo 'JSON Decode Error';
        } else {
            echo json_encode($responseData);
        }
    }
    
    curl_close($curl); 
}


?>