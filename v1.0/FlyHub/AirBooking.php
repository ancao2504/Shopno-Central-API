<?php

require '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'POST'){

$_POST = json_decode(file_get_contents('php://input'), true);

if((array_key_exists("adultCount",$_POST)) && (array_key_exists("childCount",$_POST) &&
    array_key_exists("infantCount",$_POST)) && array_key_exists("email",$_POST) && array_key_exists("phone",$_POST)){
      $adult = $_POST['adultCount'];  //echo $adult;
      $child = $_POST['childCount'];  //echo $child;
      $infants = $_POST['infantCount'];  //echo $infants;

      $SearchID = $_POST['SearchID'];
      $ResultID = $_POST['ResultID'];
      
      $Email = $_POST['email'];
      $Phone = $_POST['phone'];

      
      $Passenger = array();
      if($adult > 0 && $child> 0 && $infants> 0){
        for($x = 0 ; $x < $adult ; $x++){
            ${'afName'.$x} = $_POST['adult'][$x]["afName"];
            ${'alName'.$x} = $_POST['adult'][$x]["alName"];
            ${'agender'.$x} = $_POST['adult'][$x]["agender"];
            ${'adob'.$x} = $_POST['adult'][$x]["adob"];
            ${'apassNo'.$x} = $_POST['adult'][$x]["apassNo"];
            ${'apassEx'.$x} = $_POST['adult'][$x]["apassEx"];
            ${'apassNation'.$x} = $_POST['adult'][$x]["apassNation"];
            
            if($x==0){
              $leadPass = true;
            }else{
              $leadPass = false;
            }

            if(${'agender'.$x} == 'Male'){
              ${'aTitle'.$x} = "MR";
            }else{
              ${'aTitle'.$x} = "MS";
            }

            $Adultbasic = array("Title"=> ${'aTitle'.$x},
                          "FirstName"=> ${'afName'.$x},
                          "LastName"=> ${'alName'.$x},
                          "PaxType"=> "Adult",
                          "DateOfBirth"=> ${'adob'.$x},
                          "Gender"=> ${'agender'.$x},
                          "PassportNumber"=> ${'apassNo'.$x},
                          "PassportExpiryDate"=> ${'apassEx'.$x},
                          "PassportNationality"=> ${'apassNation'.$x},
                          "Address1"=> null,
                          "Address2"=> null,
                          "CountryCode"=> "BD",
                          "Nationality"=> "BD",
                          "ContactNumber"=> "+8809606912912",
                          "Email"=> "support@flyfarint.com",
                          "IsLeadPassenger"=> $leadPass,
                          "FFAirline"=> null,
                          "FFNumber"=> null,
                          "Baggage" => [array(
                              "BaggageID"=> null)],
                          "Meal" => [array(
                              "MealID"=> null)]
                          
            );
            
            array_push($Passenger, $Adultbasic);
            
           }

           for($x = 0 ; $x < $child ; $x++){
            ${'cfname'.$x} = $_POST['child'][$x]["cfName"];
            ${'clname'.$x} = $_POST['child'][$x]["clName"];
            ${'cgender'.$x} = $_POST['child'][$x]["cgender"];
            ${'cdob'.$x} = $_POST['child'][$x]["cdob"];
            ${'cpassNo'.$x} = $_POST['child'][$x]["cpassNo"];
            ${'cpassNoEx'.$x} = $_POST['child'][$x]["cpassEx"];
            ${'cpassNation'.$x} = $_POST['child'][$x]["cpassNation"];
            

            if(${'cgender'.$x} == 'Male'){
              ${'cTitle'.$x} = "MSTR";
            }else{
              ${'cTitle'.$x} = "MISS";
            }

            $Childbasic = array("Title"=> ${'cTitle'.$x},
                          "FirstName"=> ${'cfname'.$x},
                          "LastName"=> ${'clname'.$x},
                          "PaxType"=> "Child",
                          "DateOfBirth"=> ${'cdob'.$x},
                          "Gender"=> ${'cgender'.$x},
                          "PassportNumber"=> ${'cpassNo'.$x},
                          "PassportExpiryDate"=> ${'cpassNoEx'.$x},
                          "PassportNationality"=> ${'cpassNation'.$x},
                          "Address1"=> null,
                          "Address2"=> null,
                          "CountryCode"=> "BD",
                          "Nationality"=> "BD",
                          "ContactNumber"=> "+8809606912912",
                          "Email"=> "support@flyfarint.com",
                          "IsLeadPassenger"=> false,
                          "FFAirline"=> null,
                          "FFNumber"=> null,
                          "Baggage" => [array(
                              "BaggageID"=> null)],
                          "Meal" => [array(
                              "MealID"=> null)]
                          
            );
            
            array_push($Passenger, $Childbasic);
            
           }

           for($x = 0 ; $x < $infants ; $x++){
            ${'ifname'.$x} = $_POST['infant'][$x]["ifName"];
            ${'ilname'.$x} = $_POST['infant'][$x]["ilName"];
            ${'igender'.$x} = $_POST['infant'][$x]["igender"];
            ${'idob'.$x} = $_POST['infant'][$x]["idob"];
            ${'ipassNo'.$x} = $_POST['infant'][$x]["ipassNo"];
            ${'ipassNoEx'.$x} = $_POST['infant'][$x]["ipassEx"];
            ${'ipassNation'.$x} = $_POST['infant'][$x]["ipassNation"];
            

            if(${'igender'.$x} == 'Male'){
              ${'iTitle'.$x} = "MSTR";
            }else{
              ${'iTitle'.$x} = "MISS";
            }

            $Infantbasic = array("Title"=> ${'iTitle'.$x},
                          "FirstName"=> ${'ifname'.$x},
                          "LastName"=> ${'ilname'.$x},
                          "PaxType"=> "Infant",
                          "DateOfBirth"=> ${'idob'.$x},
                          "Gender"=> ${'igender'.$x},
                          "PassportNumber"=> ${'ipassNo'.$x},
                          "PassportExpiryDate"=> ${'ipassNoEx'.$x},
                          "PassportNationality"=> ${'ipassNation'.$x},
                          "Address1"=> null,
                          "Address2"=> null,
                          "CountryCode"=> "BD",
                          "Nationality"=> "BD",
                          "ContactNumber"=> "+8809606912912",
                          "Email"=> "support@flyfarint.com",
                          "IsLeadPassenger"=> false,
                          "FFAirline"=> null,
                          "FFNumber"=> null,
                          "Baggage" => [array(
                              "BaggageID"=> null)],
                          "Meal" => [array(
                              "MealID"=> null)]
                          
            );
            
            array_push($Passenger, $Infantbasic);
            
           }

           

           $FinalResponse = array("SearchID"=> $SearchID,
                                  "ResultID"=> $ResultID,
                                  "Passengers"=> $Passenger,
                                  "PromotionCode"=> null);
           
          
           $FlyHubBookingRequst = (json_encode($FinalResponse ,JSON_PRETTY_PRINT));
        
           
      }else if($adult > 0 && $child > 0){
        for($x = 0 ; $x < $adult ; $x++){
            ${'afName'.$x} = $_POST['adult'][$x]["afName"];
            ${'alName'.$x} = $_POST['adult'][$x]["alName"];
            ${'agender'.$x} = $_POST['adult'][$x]["agender"];
            ${'adob'.$x} = $_POST['adult'][$x]["adob"];
            ${'apassNo'.$x} = $_POST['adult'][$x]["apassNo"];
            ${'apassEx'.$x} = $_POST['adult'][$x]["apassEx"];
            ${'apassNation'.$x} = $_POST['adult'][$x]["apassNation"];
            
            if($x==0){
              $leadPass = true;
            }else{
              $leadPass = false;
            }

            if(${'agender'.$x} == 'Male'){
              ${'aTitle'.$x} = "MR";
            }else{
              ${'aTitle'.$x} = "MS";
            }

            $Adultbasic = array("Title"=> ${'aTitle'.$x},
                          "FirstName"=> ${'afName'.$x},
                          "LastName"=> ${'alName'.$x},
                          "PaxType"=> "Adult",
                          "DateOfBirth"=> ${'adob'.$x},
                          "Gender"=> ${'agender'.$x},
                          "PassportNumber"=> ${'apassNo'.$x},
                          "PassportExpiryDate"=> ${'apassEx'.$x},
                          "PassportNationality"=> ${'apassNation'.$x},
                          "Address1"=> null,
                          "Address2"=> null,
                          "CountryCode"=> "BD",
                          "Nationality"=> "BD",
                          "ContactNumber"=> "+8809606912912",
                          "Email"=> "support@flyfarint.com",
                          "IsLeadPassenger"=> $leadPass,
                          "FFAirline"=> null,
                          "FFNumber"=> null,
                          "Baggage" => [array(
                              "BaggageID"=> null)],
                          "Meal" => [array(
                              "MealID"=> null)]
                          
            );
            
            array_push($Passenger, $Adultbasic);
            
           }

           for($x = 0 ; $x < $child ; $x++){
            ${'cfname'.$x} = $_POST['child'][$x]["cfName"];
            ${'clname'.$x} = $_POST['child'][$x]["clName"];
            ${'cgender'.$x} = $_POST['child'][$x]["cgender"];
            ${'cdob'.$x} = $_POST['child'][$x]["cdob"];
            ${'cpassNo'.$x} = $_POST['child'][$x]["cpassNo"];
            ${'cpassNoEx'.$x} = $_POST['child'][$x]["cpassEx"];
            ${'cpassNation'.$x} = $_POST['child'][$x]["cpassNation"];
            

            if(${'cgender'.$x} == 'Male'){
              ${'cTitle'.$x} = "MSTR";
            }else{
             ${'cTitle'.$x} = "MISS";
            }

            $Childbasic = array("Title"=> ${'cTitle'.$x},
                          "FirstName"=> ${'cfname'.$x},
                          "LastName"=> ${'clname'.$x},
                          "PaxType"=> "Child",
                          "DateOfBirth"=> ${'cdob'.$x},
                          "Gender"=> ${'cgender'.$x},
                          "PassportNumber"=> ${'cpassNo'.$x},
                          "PassportExpiryDate"=> ${'cpassNoEx'.$x},
                          "PassportNationality"=> ${'cpassNation'.$x},
                          "Address1"=> null,
                          "Address2"=> null,
                          "CountryCode"=> "BD",
                          "Nationality"=> "BD",
                          "ContactNumber"=> "+8809606912912",
                          "Email"=> "support@flyfarint.com",
                          "IsLeadPassenger"=> false,
                          "FFAirline"=> null,
                          "FFNumber"=> null,
                          "Baggage" => [array(
                              "BaggageID"=> null)],
                          "Meal" => [array(
                              "MealID"=> null)]
                          
            );
            
            array_push($Passenger, $Childbasic);
            
           }

          
           $FinalResponse = array("SearchID"=> $SearchID,
                                  "ResultID"=> $ResultID,
                                  "Passengers"=> $Passenger,
                                  "PromotionCode"=> null);
           
          
           $FlyHubBookingRequst = (json_encode($FinalResponse ,JSON_PRETTY_PRINT));
        
        
      }else if($adult > 0 && $infants > 0){
        for($x = 0 ; $x < $adult ; $x++){
            ${'afName'.$x} = $_POST['adult'][$x]["afName"];
            ${'alName'.$x} = $_POST['adult'][$x]["alName"];
            ${'agender'.$x} = $_POST['adult'][$x]["agender"];
            ${'adob'.$x} = $_POST['adult'][$x]["adob"];
            ${'apassNo'.$x} = $_POST['adult'][$x]["apassNo"];
            ${'apassEx'.$x} = $_POST['adult'][$x]["apassEx"];
            ${'apassNation'.$x} = $_POST['adult'][$x]["apassNation"];
            
            if($x==0){
              $leadPass = true;
            }else{
              $leadPass = false;
            }

            if(${'agender'.$x} == 'Male'){
              ${'aTitle'.$x} = "MR";
            }else{
              ${'aTitle'.$x} = "MS";
            }

            $Adultbasic = array("Title"=> ${'aTitle'.$x},
                          "FirstName"=> ${'afName'.$x},
                          "LastName"=> ${'alName'.$x},
                          "PaxType"=> "Adult",
                          "DateOfBirth"=> ${'adob'.$x},
                          "Gender"=> ${'agender'.$x},
                          "PassportNumber"=> ${'apassNo'.$x},
                          "PassportExpiryDate"=> ${'apassEx'.$x},
                          "PassportNationality"=> ${'apassNation'.$x},
                          "Address1"=> null,
                          "Address2"=> null,
                          "CountryCode"=> "BD",
                          "Nationality"=> "BD",
                          "ContactNumber"=> "+8809606912912",
                          "Email"=> "support@flyfarint.com",
                          "IsLeadPassenger"=> $leadPass,
                          "FFAirline"=> null,
                          "FFNumber"=> null,
                          "Baggage" => [array(
                              "BaggageID"=> null)],
                          "Meal" => [array(
                              "MealID"=> null)]
                          
            );
            
            array_push($Passenger, $Adultbasic);
            
           }

           for($x = 0 ; $x < $infants ; $x++){
            ${'ifname'.$x} = $_POST['infant'][$x]["ifName"];
            ${'ilname'.$x} = $_POST['infant'][$x]["ilName"];
            ${'igender'.$x} = $_POST['infant'][$x]["igender"];
            ${'idob'.$x} = $_POST['infant'][$x]["idob"];
            ${'ipassNo'.$x} = $_POST['infant'][$x]["ipassNo"];
            ${'ipassNoEx'.$x} = $_POST['infant'][$x]["ipassEx"];
            ${'ipassNation'.$x} = $_POST['infant'][$x]["ipassNation"];
            

            if(${'igender'.$x} == 'Male'){
              ${'iTitle'.$x} = "MSTR";
            }else{
              ${'iTitle'.$x} = "MISS";
            }

            $Infantbasic = array("Title"=> ${'iTitle'.$x},
                          "FirstName"=> ${'ifname'.$x},
                          "LastName"=> ${'ilname'.$x},
                          "PaxType"=> "Infant",
                          "DateOfBirth"=> ${'idob'.$x},
                          "Gender"=> ${'igender'.$x},
                          "PassportNumber"=> ${'ipassNo'.$x},
                          "PassportExpiryDate"=> ${'ipassNoEx'.$x},
                          "PassportNationality"=> ${'ipassNation'.$x},
                          "Address1"=> null,
                          "Address2"=> null,
                          "CountryCode"=> "BD",
                          "Nationality"=> "BD",
                          "ContactNumber"=> "+8809606912912",
                          "Email"=> "support@flyfarint.com",
                          "IsLeadPassenger"=> false,
                          "FFAirline"=> null,
                          "FFNumber"=> null,
                          "Baggage" => [array(
                              "BaggageID"=> null)],
                          "Meal" => [array(
                              "MealID"=> null)]
                          
            );
            
            array_push($Passenger, $Infantbasic);
            
           }

           

           $FinalResponse = array("SearchID"=> $SearchID,
                                  "ResultID"=> $ResultID,
                                  "Passengers"=> $Passenger,
                                  "PromotionCode"=> null);
           
          
           $FlyHubBookingRequst = (json_encode($FinalResponse ,JSON_PRETTY_PRINT));
        
        
        
      }else if($adult > 0){
        
           for($x = 0 ; $x < $adult ; $x++){
            ${'afName'.$x} = $_POST['adult'][$x]["afName"];
            ${'alName'.$x} = $_POST['adult'][$x]["alName"];
            ${'agender'.$x} = $_POST['adult'][$x]["agender"];
            ${'adob'.$x} = $_POST['adult'][$x]["adob"];
            ${'apassNo'.$x} = $_POST['adult'][$x]["apassNo"];
            ${'apassEx'.$x} = $_POST['adult'][$x]["apassEx"];
            ${'apassNation'.$x} = $_POST['adult'][$x]["apassNation"];
            
            if($x==0){
              $leadPass = true;
            }else{
              $leadPass = false;
            }

            if(${'agender'.$x} == 'Male'){
              ${'aTitle'.$x} = "MR";
            }else{
              ${'aTitle'.$x}= "MS";
            }

            $Adultbasic = array("Title"=> ${'aTitle'.$x},
                          "FirstName"=> ${'afName'.$x},
                          "LastName"=> ${'alName'.$x},
                          "PaxType"=> "Adult",
                          "DateOfBirth"=> ${'adob'.$x},
                          "Gender"=> ${'agender'.$x},
                          "PassportNumber"=> ${'apassNo'.$x},
                          "PassportExpiryDate"=> ${'apassEx'.$x},
                          "PassportNationality"=> ${'apassNation'.$x},
                          "Address1"=> null,
                          "Address2"=> null,
                          "CountryCode"=> "BD",
                          "Nationality"=> "BD",
                          "ContactNumber"=> "+8809606912912",
                          "Email"=> "support@flyfarint.com",
                          "IsLeadPassenger"=> $leadPass,
                          "FFAirline"=> null,
                          "FFNumber"=> null,
                          "Baggage" => [array(
                              "BaggageID"=> null)],
                          "Meal" => [array(
                              "MealID"=> null)]
                          
            );
            
            array_push($Passenger, $Adultbasic);
            
           }

           $FinalResponse = array("SearchID"=> $SearchID,
                                  "ResultID"=> $ResultID,
                                  "Passengers"=> $Passenger,
                                  "PromotionCode"=> null);
           
          
           $FlyHubBookingRequst = (json_encode($FinalResponse ,JSON_PRETTY_PRINT));
                
      
      }


//echo $FlyHubBookingRequst;


//Fly Hub

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
$FlyhubToken = $TokenJson['TokenId']; //echo $FlyhubToken;


//Pre Booking

$curlFlyHubPreBooking = curl_init();

curl_setopt_array($curlFlyHubPreBooking, array(
CURLOPT_URL => 'https://api.flyhub.com/api/v1/AirPreBook',
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => '',
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 0,
CURLOPT_FOLLOWLOCATION => true,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => 'POST',
CURLOPT_POSTFIELDS => $FlyHubBookingRequst,
CURLOPT_HTTPHEADER => array(
'Content-Type: application/json',
"Authorization: Bearer $FlyhubToken"
),
));

$flyhubresponse1 = curl_exec($curlFlyHubPreBooking);

curl_close($curlFlyHubPreBooking);

//echo  $flyhubresponse1;

$resutPreBook = json_decode($flyhubresponse1,true);

if(isset($resutPreBook['Error']['ErrorMessage'])){
        $FlyHubRes['status']= "error";
        $FlyHubRes['message']= $resutPreBook['Error']['ErrorMessage'];           
        echo json_encode($FlyHubRes);
}else{

  sleep(5);

  $curlFlyHubBooking = curl_init();

  curl_setopt_array($curlFlyHubBooking, array(
  CURLOPT_URL => 'https://api.flyhub.com/api/v1/AirBook',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $FlyHubBookingRequst,
  CURLOPT_HTTPHEADER => array(
  'Content-Type: application/json',
  "Authorization: Bearer $FlyhubToken"
  ),
  ));

    $flyhubresponse = curl_exec($curlFlyHubBooking);

    curl_close($curlFlyHubBooking);

    $flyhubResult = json_decode($flyhubresponse, true);

    $status = $flyhubResult['Error'];

    if(isset($flyhubresponse['Error'])){
      $FlyHubRes['status']= "error";
      $FlyHubRes['message']= $flyhubresponse['Error']['ErrorMessage'];
        
        
    }else{
       $FlyHubRes['status']= "success";
       $FlyHubRes['message']= $flyhubResult;            
    }

    echo json_encode($FlyHubRes);
    
    
    }
    
  }else{
      echo json_encode("Passenger Details And Count Of Passenger Not Valid");
  }
}