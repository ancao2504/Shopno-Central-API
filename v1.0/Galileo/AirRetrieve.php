<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if (array_key_exists("BookingID",$_GET)){
    $BookingID = $_GET['BookingID'];

  $message = <<<EOM
	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
    <soapenv:Header/>
		<soapenv:Body>
			<UniversalRecordRetrieveReq xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" TraceId="FFI-KayesFahim" TargetBranch="P4218912" xmlns="http://www.travelport.com/schema/universal_v51_0">
                <BillingPointOfSaleInfo OriginApplication="UAPI" xmlns="http://www.travelport.com/schema/common_v51_0" />
                <UniversalRecordLocatorCode xmlns="http://www.travelport.com/schema/universal_v51_0">$BookingID</UniversalRecordLocatorCode>
            </UniversalRecordRetrieveReq>
		</soapenv:Body>
	</soapenv:Envelope> 
EOM;


//print_r($message);


    //Cert
    // $TARGETBRANCH = 'P7182044';
	//$CREDENTIALS = 'Universal API/uAPI5270664478-0c51bde6:2Td*m/F3M5'; 

	//Prod
	$TARGETBRANCH = 'P4218912';
	$CREDENTIALS = 'Universal API/uAPI4444837655-83fe5101:K/s3-5Sy4c';
	

	$auth = base64_encode("$CREDENTIALS"); 
	$soap_do = curl_init("https://apac.universal-api.travelport.com/B2BGateway/connect/uAPI/AirService");
	$header = array(
	"Content-Type: text/xml;charset=UTF-8", 
	"Accept: gzip,deflate", 
	"Cache-Control: no-cache", 
	"Pragma: no-cache", 
	"SOAPAction: \"\"",
	"Authorization: Basic $auth", 
	"Content-length: ".strlen($message),
	); 


	curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false); 
	curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false); 
	curl_setopt($soap_do, CURLOPT_POST, true ); 
	curl_setopt($soap_do, CURLOPT_POSTFIELDS, $message); 
	curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header); 
	curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
	$return = curl_exec($soap_do);
	curl_close($soap_do);

	//print_r($return);

	//$return = file_get_contents("res.xml") ;
	$response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $return);
	$xml = new SimpleXMLElement($response);

    //print_r($xml);

    
	//print_r($xml);
	if(isset($xml->xpath('//airAirPriceRsp')[0])){
		$body = $xml->xpath('//airAirPriceRsp')[0];
		
	$result = json_decode(json_encode((array)$body), TRUE);

    //print_r($result);

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


        //print_r($result);

        $NewArrray = recursive_change_key($result, array('@attributes' => 'attributes'));  
        $json_string = json_encode($NewArrray, JSON_PRETTY_PRINT);
            
            echo $json_string;
        
   }

         


}