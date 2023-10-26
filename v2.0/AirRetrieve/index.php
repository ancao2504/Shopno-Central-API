<?php

include "../AirSearch/Token.php";
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(array_key_exists('BookingId', $_GET)) {
    $BookingId = $_GET['BookingId'];
    
    $Data = $conn->query("SELECT * FROM `booking` WHERE bookingId='$BookingId'")->fetch_all(MYSQLI_ASSOC);

    if(!empty($Data)) {
        $System = $Data[0]['gds'];
        $PNR = $Data[0]['pnr'];
        $UniversalPNR = $Data[0]['upPnr'];


        if($System == 'Sabre'){
            
            
            SabreAirRetrieve($SabreToken , $PNR);
            
        }else if($System == 'Galileo'){
            
            GalileoAirRetrieve($UniversalPNR);
            
        }else if($System == 'FlyHub'){
            
            FlyHubAirRetrieve($PNR);
            
        }
    }else{
        $response['status'] = "error";
        $response['message'] = "Invalid BookingId";
        echo json_encode($response);
        exit();  
    }

        
}


function SabreAirRetrieve($SabreToken, $PNR){

    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.platform.sabre.com/v1/trip/orders/getBooking',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
        "confirmationId": "'.$PNR.'",
        "retrieveBooking": true,
        "cancelAll": true,
        "errorHandlingPolicy": "ALLOW_PARTIAL_CANCEL"
    }',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Conversation-ID: 2021.01.DevStudio',
        "Authorization: Bearer $SabreToken"
    ),
    ));

    $SabreResponse = curl_exec($curl);
    echo $SabreResponse;

    // $SabreResponse = json_decode(curl_exec($curl), true);
    // $timeLimitTxt= $SabreResponse["specialServices"][0]["message"];
    //     if(isset($timeLimitTxt)){
    //         //TODO: Define a regular expression pattern to match the date and time
    //         $pattern = '/(\d{2}[A-Z]{3}\d{2}) AT (\d{4}) GMT/';
    //         if (preg_match($pattern, $timeLimitTxt, $matches)) {
    //             // TODO: Extract the date and time from the matches array
    //             $date = $matches[1];
    //             $time = $matches[2];
            
    //             // TODO:You can further process the date and time as needed
    //             $TimeLimit = $date . "T" . substr($time,0,2) . ":". substr($time,2,3) . ":00"; // TODO: Join Them  
                
    //         }
    //     }else{
    //         $TimeLimit = ""; // TODO: Make empty if it's not found
    //     }
    // // print_r(json_encode($SabreResponse["specialServices"][0]["message"]));
    // echo $timeLimitTxt;
    // echo $TimeLimit;
    
}

function GalileoAirRetrieve($UniversalPNR){

    $GalileoRequest = <<<EOM
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
        <soapenv:Header/>
            <soapenv:Body>
            <UniversalRecordRetrieveReq xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" TraceId="Fly-Far-Tech" TargetBranch="P4218912" xmlns="http://www.travelport.com/schema/universal_v51_0">
            <BillingPointOfSaleInfo OriginApplication="UAPI" xmlns="http://www.travelport.com/schema/common_v51_0" />
            <UniversalRecordLocatorCode xmlns="http://www.travelport.com/schema/universal_v51_0">$UniversalPNR</UniversalRecordLocatorCode>
            </UniversalRecordRetrieveReq>
        </soapenv:Body>
        </soapenv:Envelope>
        EOM;

    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://apac.universal-api.travelport.com/B2BGateway/connect/uAPI/UniversalRecordService',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $GalileoRequest,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/xml',
        'Authorization: Basic VW5pdmVyc2FsIEFQSS91QVBJNDQ0NDgzNzY1NS04M2ZlNTEwMTpLL3MzLTVTeTRj'
    ),
    ));

    $GalileoResponse = curl_exec($curl);
    curl_close($curl);
    
    $GalileoResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $GalileoResponse);
	$xml = new SimpleXMLElement($GalileoResult);
    $GalileoResultData = json_decode(json_encode((array)$xml), TRUE);

	if(isset($GalileoResultData['SOAPBody']['universalUniversalRecordRetrieveRsp'])){

        $NewArray = recursive_change_key($GalileoResultData, array('@attributes' => 'attributes'));  
            
        echo json_encode($NewArray);    
    }else{
        $response['status'] = "error";
        $response['message'] = "Invalid Bad Request";
        echo json_encode($response);
        exit();
    }
  
}

function FlyHubAirRetrieve($BookingID){
    $FlyHubRequest ='{
        "BookingID": "'.$BookingID.'"
      }';
      
      $curlflyhubauth = curl_init();
      
      curl_setopt_array($curlflyhubauth, array(
      CURLOPT_URL => 'https://api.flyhub.com/api/v1/Authenticate',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
      "username": "ceo@flyfarint.com",
      "apikey": "ENex7c5Ge+0~SGc1t71iccr1xXacDPdK51g=iTm9SlL+de39HF"
      }',
      CURLOPT_HTTPHEADER => array(
      'Content-Type: application/json'
      ),
      ));
      
      $response = curl_exec($curlflyhubauth);
      
      $TokenJson = json_decode($response,true);
      
      $FlyhubToken = $TokenJson['TokenId'];
      
      $curl = curl_init();
      
      curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.flyhub.com/api/v1/AirRetrieve',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $FlyHubRequest,
        CURLOPT_HTTPHEADER => array(
          'Content-Type: application/json',
          "Authorization: Bearer $FlyhubToken"
        ),
      ));
      
      $response1 = curl_exec($curl);
      $data = serialize(json_decode($response1, true));
      
      
      curl_close($curl);
      
       
      echo $response1;
}

function recursive_change_key($arr, $set) {
    if (is_array($arr) && is_array($set)) {
        $newArr = array();
        foreach ($arr as $k => $v) {
            $key = array_key_exists( $k, $set) ? $set[$k] : $k;
            $newArr[$key] = is_array($v) ? recursive_change_key($v, $set) : $v;
        }
        return $newArr;
    }
    return $arr;    
}


?>