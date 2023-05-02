<?php


header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER['REQUEST_METHOD'] == 'POST'){

$now = new DateTime();  


$AllPerson= array();
$AdvancePassnger= array();
$AllSsr = array();
$AllSecureFlight = array();


$_POST = json_decode(file_get_contents('php://input'), true);


if((array_key_exists("adultCount",$_POST)) && (array_key_exists("childCount",$_POST) 
      && array_key_exists("infantCount",$_POST)) && array_key_exists("tripType",$_POST)){

      $adult = $_POST['adultCount'];  //echo $adult;
      $child = $_POST['childCount'];  //echo $child;
      $infants = $_POST['infantCount'];  //echo $infants;
      $phone = $_POST['phone']; //echo $phone;

      if(array_key_exists("afName1",$_POST)){
         $Name = $_POST['afName1'];
      }

      if($adult > 0 && $child> 0 && $infants> 0){
      $paxRequest = '{
                "Code": "ADT",
                "Quantity": "'.$adult.'"
              },
              {
                "Code": "C09",
                "Quantity": "'.$child.'"
              },
              {
                "Code": "INF",
                "Quantity": "'.$infants.'"
              }';
                     

    }else if($adult > 0 && $child > 0){

      $paxRequest = '{
                    "Code": "ADT",
                    "Quantity": "'.$adult.'"
                  },
                  {
                    "Code": "C09",
                    "Quantity": "'.$child.'"
                  }';
    }else if($adult > 0 && $infants > 0){
      $paxRequest = '{
                  "Code": "ADT",
                  "Quantity": "'.$adult.'"
                  },
                  {
                    "Code": "INF",
                    "Quantity": "'.$infants.'"
                  }';

    }else{
      $paxRequest = '{
                "Code": "ADT",
                "Quantity": "'.$adult.'"
              }';

    }

      
      $SeatReq = $adult + $child;

   if($adult > 0 && $child> 0 && $infants> 0){

      //Adult Part
      $adultCount = 0;
      $totalCount= 0; 
      for($x=0; $x < $adult; $x++){
         $adultCount++;
         $totalCount++;  
         
         ${'afName'.$x} = strtoupper($_POST['adult'][$x]["afName"]);
         ${'alName'.$x} = strtoupper($_POST['adult'][$x]["alName"]);
         ${'agender'.$x} = $_POST['adult'][$x]["agender"];
         ${'adob'.$x} = $_POST['adult'][$x]["adob"];
         ${'apassNo'.$x} = $_POST['adult'][$x]["apassNo"];
         ${'apassEx'.$x} = $_POST['adult'][$x]["apassEx"];
         ${'apassNation'.$x} = $_POST['adult'][$x]["apassNation"];

         if(${'agender'.$x} == 'Male'){
            ${'agender'.$x} = 'M';
            $atitle = 'MR';
         }else{
            ${'agender'.$x} = 'F';
            $atitle = 'MS';
         }


            $Person = array(
                     "NameNumber"=>"$totalCount.1",
                     "GivenName"=>"${'afName'.$x} $atitle",
                     "Surname"=>"${'alName'.$x}",
                     "Infant"=>false,
                     "PassengerType"=>"ADT",
                     "NameReference"=>"");
                     
            array_push($AllPerson, $Person);

            $AdvPax = array(
                        "Document"=>array(
                           "Number"=>"${'apassNo'.$x}",
                           "IssueCountry"=>"${'apassNation'.$x}",
                           "NationalityCountry"=>"${'apassNation'.$x}",
                           "ExpirationDate"=>"${'apassEx'.$x}",
                           "Type"=>"P"
                        ),
                        "PersonName"=> array(
                           "NameNumber"=>"$totalCount.1",
                           "GivenName"=> "${'afName'.$x}",
                           "MiddleName"=>"",
                           "Surname"=>"${'alName'.$x}",
                           "DateOfBirth"=>"${'adob'.$x}",
                           "Gender"=>"${'agender'.$x}"
                        ),
                        "SegmentNumber"=>"A"
                     );
            
            array_push($AdvancePassnger, $AdvPax);

            $Secureflight =  array("PersonName"=> 
                              array("NameNumber"=>"$totalCount.1",
                              "GivenName"=>"${'afName'.$x}",
                              "Surname"=>$aSurName,
                              "DateOfBirth"=>"${'adob'.$x}",
                              "Gender"=>"${'agender'.$x}"
                        ),
                        "SegmentNumber"=>"A",
                        "VendorPrefs" => array("Airline"=>
                         array("Hosted"=>false)
                        )

                     );
         

         array_push($AllSecureflight, $Secureflight);


            $SSROThers = array(
                        "SSR_Code"=> "OTHS",
                        "Text"=> "CC ${'afName'.$x} ${'alName'.$x}",
                        "PersonName"=> array(
                           "NameNumber"=> "$totalCount.1"
                        ),
                        "SegmentNumber"=> "A"
                        );
            array_push($AllSsr, $SSROThers);
                     
            $SSRCTCM =  array(
                        "SSR_Code"=> "CTCM",
                        "Text"=> "$phone",
                        "PersonName"=> array(
                           "NameNumber"=> "$totalCount.1"
                        ),
                        "SegmentNumber"=> "A"
                        );

            array_push($AllSsr, $SSRCTCM);
            
            $SSRCTCE =  array(
                        "SSR_Code"=> "CTCE",
                        "Text"=> "${'afName'.$x}//${'afName'.$x}.com",
                        "PersonName"=> array(
                           "NameNumber"=> "$totalCount.1"
                        ),
                        "SegmentNumber"=> "A"
                     );
                     

            array_push($AllSsr, $SSRCTCE);
                               
      }  
      
      //Child Part

      $childCount=0;
      for($x=0; $x < $child; $x++){
       $adultCount++;
       $childCount++;
       $totalCount++;  
         
      ${'cfName'.$x} = strtoupper($_POST['child'][$x]["cfName"]);
		${'clName'.$x} = strtoupper($_POST['child'][$x]["clName"]);
		${'cgender'.$x} = $_POST['child'][$x]["cgender"];
		${'cdob'.$x} = $_POST['child'][$x]["cdob"];
		${'cpassNo'.$x} = $_POST['child'][$x]["cpassNo"];
		${'cpassEx'.$x} = $_POST['child'][$x]["cpassEx"];
		${'cpassNation'.$x} = $_POST['child'][$x]["cpassNation"];

      //AgeCalculate
      ${'cdate'.$x} = date_create(${'cdob'.$x});
      ${'childSSR'.$x} = date_format(${'cdate'.$x},"dMy");
      ${'dobCount'.$x} = new DateTime(${'cdob'.$x});           
      ${'AgeCount'.$x} = $now->diff(${'dobCount'.$x});        
      ${'age'.$x} = ${'AgeCount'.$x}->y;
      ${'cAge'.$x} = str_pad(${'age'.$x}, 2, '0', STR_PAD_LEFT); //print(${'cAge'.$x});
         

		if(${'cgender'.$x} == 'Male'){
         ${'cgender'.$x} = 'M';
			${'ctitle'.$x} = 'MSTR';
		}else{
         ${'cgender'.$x} = 'F';
			${'ctitle'.$x} = 'MISS';
		}


         $Person = array(
                   "NameNumber"=>"$totalCount.1",
                   "GivenName"=>"${'cfName'.$x} ${'ctitle'.$x}",
                   "Surname"=>"${'clName'.$x}",
                   "Infant"=>false,
                   "PassengerType"=>"C${'cAge'.$x}",
                   "NameReference"=>"C${'cAge'.$x}");
                   
         array_push($AllPerson, $Person);

         $AdvPax = array(
                      "Document"=>array(
                         "Number"=>"${'cpassNo'.$x}",
                         "IssueCountry"=>"${'cpassNation'.$x}",
                         "NationalityCountry"=>"${'cpassNation'.$x}",
                         "ExpirationDate"=>"${'cpassEx'.$x}",
                         "Type"=>"P"
                      ),
                      "PersonName"=> array(
                         "NameNumber"=>"$totalCount.1",
                         "GivenName"=> "${'cfName'.$x}",
                         "MiddleName"=>"",
                         "Surname"=>"${'clName'.$x}",
                         "DateOfBirth"=>"${'cdob'.$x}",
                         "Gender"=>"${'cgender'.$x}"
                      ),
                      "SegmentNumber"=>"A"
                   );
         
         array_push($AdvancePassnger, $AdvPax);

         $Secureflight =  array("PersonName"=> 
                     array("NameNumber"=>"$totalCount.1",
                     "GivenName"=> "${'cfName'.$x}",
                     "Surname"=>"${'clName'.$x}",
                     "DateOfBirth"=>"${'cdob'.$x}",
                     "Gender"=>"${'cgender'.$x}"
                        ),
                        "SegmentNumber"=>"A",
                        "VendorPrefs" => array("Airline"=>
                        array("Hosted"=>false)
                        )

                     );


            array_push($AllSecureflight, $Secureflight);



         $SSROThers = array(
                      "SSR_Code"=> "CHLD",
                      "Text"=> "${'childSSR'.$x}",
                      "PersonName"=> array(
                         "NameNumber"=> "$totalCount.1"
                      ),
                      "SegmentNumber"=> "A"
                     );
         array_push($AllSsr, $SSROThers);
                  
                  
         }

      //Infants Code

      $infantCount=0;
      for($x=0; $x < $infants; $x++){
       $adultCount++;
       $infantCount++;
       $totalCount++;  
         
      ${'ifName'.$x} = strtoupper($_POST['infant'][$x]["ifName"]);
		${'ilName'.$x} = strtoupper($_POST['infant'][$x]["ilName"]);
		${'igender'.$x} = $_POST['infant'][$x]["igender"];
		${'idob'.$x} = $_POST['infant'][$x]["idob"];
		${'ipassNo'.$x} = $_POST['infant'][$x]["ipassNo"];
		${'ipassEx'.$x} = $_POST['infant'][$x]["ipassEx"];
		${'ipassNation'.$x} = $_POST['infant'][$x]["ipassNation"];

      //AgeCalculate
      ${'idate'.$x} = date_create(${'idob'.$x});
      ${'infantSSR'.$x} = date_format(${'idate'.$x},"dMy");
      ${'dobCount'.$x} = new DateTime(${'idob'.$x});           
      ${'AgeCount'.$x} = $now->diff(${'dobCount'.$x});        
      ${'age'.$x} = ${'AgeCount'.$x}->m;
      ${'iAge'.$x} = str_pad(${'age'.$x}, 2, '0', STR_PAD_LEFT); 
         

		if(${'igender'.$x} == 'Male'){
         ${'igender'.$x} = 'M';
			${'ititle'.$x} = 'MSTR';
		}else{
         ${'igender'.$x} = 'F';
			${'ititle'.$x} = 'MISS';
		}


         $Person = array(
                   "NameNumber"=>"$infantCount.1",
                   "GivenName"=>"${'ifName'.$x} ${'ititle'.$x}",
                   "Surname"=>"${'ilName'.$x}",
                   "Infant"=>true,
                   "PassengerType"=>"INF",
                   "NameReference"=>"I${'iAge'.$x}");
                   
         array_push($AllPerson, $Person);

         $AdvPax = array(
                      "Document"=>array(
                         "Number"=>"${'ipassNo'.$x}",
                         "IssueCountry"=>"${'ipassNation'.$x}",
                         "NationalityCountry"=>"${'ipassNation'.$x}",
                         "ExpirationDate"=>"${'ipassEx'.$x}",
                         "Type"=>"P"
                      ),
                      "PersonName"=> array(
                         "NameNumber"=>"$infantCount.1",
                         "GivenName"=> "${'ifName'.$x}",
                         "MiddleName"=>"",
                         "Surname"=>"${'ilName'.$x}",
                         "DateOfBirth"=>"${'idob'.$x}",
                         "Gender"=>"${'igender'.$x}I"
                      ),
                      "SegmentNumber"=>"A"
                   );
         
         array_push($AdvancePassnger, $AdvPax);

         $Secureflight =  array("PersonName"=> 
                     array("NameNumber"=>"$infantCount.1",
                     "GivenName"=> "${'ifName'.$x}",
                     "Surname"=>"${'ilName'.$x}",
                     "DateOfBirth"=>"${'idob'.$x}",
                     "Gender"=>"${'igender'.$x}I"
                     ),
                     "SegmentNumber"=>"A",
                     "VendorPrefs" => array("Airline"=>
                     array("Hosted"=>false)
                     )
                  );


         array_push($AllSecureflight, $Secureflight);
         


         $SSROThers = array(
                      "SSR_Code"=> "INFT",
                      "Text"=> "${'ifName'.$x}/${'ilName'.$x} ${'ititle'.$x}/${'infantSSR'.$x}",
                      "PersonName"=> array(
                         "NameNumber"=> "$infantCount.1"
                      ),
                      "SegmentNumber"=> "A"
                     );
         array_push($AllSsr, $SSROThers);
                  
          
         }
      



    }else if($adult > 0 && $child > 0){

      //Adult Part
      $adultCount = 0;
      $totalCount = 0;
      for($x=0; $x < $adult; $x++){
         $adultCount++;
         $totalCount++;  
         
         ${'afName'.$x} = strtoupper($_POST['adult'][$x]["afName"]);
         ${'alName'.$x} = strtoupper($_POST['adult'][$x]["alName"]);
         ${'agender'.$x} = $_POST['adult'][$x]["agender"];
         ${'adob'.$x} = $_POST['adult'][$x]["adob"];
         ${'apassNo'.$x} = $_POST['adult'][$x]["apassNo"];
         ${'apassEx'.$x} = $_POST['adult'][$x]["apassEx"];
         ${'apassNation'.$x} = $_POST['adult'][$x]["apassNation"];

         if(${'agender'.$x} == 'Male'){
            ${'agender'.$x} = 'M';
            ${'atitle'.$x} = 'MR';
         }else{
            ${'agender'.$x} = 'F';
            ${'atitle'.$x} = 'MS';
         }


            $Person = array(
                     "NameNumber"=>"$adultCount.1",
                     "GivenName"=>"${'afName'.$x} ${'atitle'.$x}",
                     "Surname"=>"${'alName'.$x}",
                     "Infant"=>false,
                     "PassengerType"=>"ADT",
                     "NameReference"=>"");
                     
            array_push($AllPerson, $Person);

            $AdvPax = array(
                        "Document"=>array(
                           "Number"=>"${'apassNo'.$x}",
                           "IssueCountry"=>"${'apassNation'.$x}",
                           "NationalityCountry"=>"${'apassNation'.$x}",
                           "ExpirationDate"=>"${'apassEx'.$x}",
                           "Type"=>"P"
                        ),
                        "PersonName"=> array(
                           "NameNumber"=>"$adultCount.1",
                           "GivenName"=> "${'afName'.$x}",
                           "MiddleName"=>"",
                           "Surname"=>"${'alName'.$x}",
                           "DateOfBirth"=>"${'adob'.$x}",
                           "Gender"=>"${'agender'.$x}"
                        ),
                        "SegmentNumber"=>"A"
                     );
            
            array_push($AdvancePassnger, $AdvPax);

            $Secureflight =  array("PersonName"=> 
                        array("NameNumber"=>"$adultCount.1",
                        "GivenName"=>"${'afName'.$x}",
                        "Surname"=>"${'alName'.$x}",
                        "DateOfBirth"=>"${'adob'.$x}",
                        "Gender"=>"${'agender'.$x}"
                           ),
                           "SegmentNumber"=>"A",
                           "VendorPrefs" => array("Airline"=>
                           array("Hosted"=>false)
                           )
                        );


            array_push($AllSecureflight, $Secureflight);

            $SSROThers = array(
                        "SSR_Code"=> "OTHS",
                        "Text"=> "CC ${'afName'.$x} ${'afName'.$x}",
                        "PersonName"=> array(
                           "NameNumber"=> "$adultCount.1"
                        ),
                        "SegmentNumber"=> "A"
                        );
            array_push($AllSsr, $SSROThers);
                     
            $SSRCTCM =  array(
                        "SSR_Code"=> "CTCM",
                        "Text"=> "$phone",
                        "PersonName"=> array(
                           "NameNumber"=> "$adultCount.1"
                        ),
                        "SegmentNumber"=> "A"
                        );

            array_push($AllSsr, $SSRCTCM);
            
            $SSRCTCE =  array(
                        "SSR_Code"=> "CTCE",
                        "Text"=> "${'afName'.$x}//${'afName'.$x}.com",
                        "PersonName"=> array(
                           "NameNumber"=> "$adultCount.1"
                        ),
                        "SegmentNumber"=> "A"
                     );
                     

            array_push($AllSsr, $SSRCTCE);
                               
      }  
      
      //Child Part

      $childCount=0;
      for($x=0; $x < $child; $x++){
       $adultCount++;
       $childCount++;  
         
      ${'cfName'.$x} = strtoupper($_POST['child'][$x]["cfName"]);
		${'clName'.$x} = strtoupper($_POST['child'][$x]["clName"]);
		${'cgender'.$x} = $_POST['child'][$x]["cgender"];
		${'cdob'.$x} = $_POST['child'][$x]["cdob"];
		${'cpassNo'.$x} = $_POST['child'][$x]["cpassNo"];
		${'cpassEx'.$x} = $_POST['child'][$x]["cpassEx"];
		${'cpassNation'.$x} = $_POST['child'][$x]["cpassNation"];

      //AgeCalculate
      ${'cdate'.$x} = date_create(${'cdob'.$x});
      ${'childSSR'.$x} = date_format(${'cdate'.$x},"dMy");
      ${'dobCount'.$x} = new DateTime(${'cdob'.$x});           
      ${'AgeCount'.$x} = $now->diff(${'dobCount'.$x});        
      ${'age'.$x} = ${'AgeCount'.$x}->y;
      ${'cAge'.$x} = str_pad(${'age'.$x}, 2, '0', STR_PAD_LEFT); //print(${'cAge'.$x});
         

		if(${'cgender'.$x} == 'Male'){
         ${'cgender'.$x} = 'M';
			${'ctitle'.$x} = 'MSTR';
		}else{
         ${'cgender'.$x} = 'F';
			${'ctitle'.$x} = 'MISS';
		}


         $Person = array(
                   "NameNumber"=>"$adultCount.1",
                   "GivenName"=>"${'cfName'.$x} ${'ctitle'.$x}",
                   "Surname"=>"${'clName'.$x}",
                   "Infant"=>false,
                   "PassengerType"=>"C${'cAge'.$x}",
                   "NameReference"=>"C${'cAge'.$x}");
                   
         array_push($AllPerson, $Person);

         $AdvPax = array(
                      "Document"=>array(
                         "Number"=>"${'cpassNo'.$x}",
                         "IssueCountry"=>"${'cpassNation'.$x}",
                         "NationalityCountry"=>"${'cpassNation'.$x}",
                         "ExpirationDate"=>"${'cpassEx'.$x}",
                         "Type"=>"P"
                      ),
                      "PersonName"=> array(
                         "NameNumber"=>"$adultCount.1",
                         "GivenName"=> "${'cfName'.$x}",
                         "MiddleName"=>"",
                         "Surname"=>"${'clName'.$x}",
                         "DateOfBirth"=>"${'cdob'.$x}",
                         "Gender"=>"${'cgender'.$x}"
                      ),
                      "SegmentNumber"=>"A"
                   );
         
         array_push($AdvancePassnger, $AdvPax);

         $Secureflight =  array("PersonName"=> 
                     array("NameNumber"=>"$adultCount.1",
                     "GivenName"=> "${'cfName'.$x}",
                     "Surname"=>"${'clName'.$x}",
                     "DateOfBirth"=>"${'cdob'.$x}",
                     "Gender"=>"${'cgender'.$x}"
                        ),
                        "SegmentNumber"=>"A",
                        "VendorPrefs" => array("Airline"=>
                        array("Hosted"=>false)
                        )

                     );


            array_push($AllSecureflight, $Secureflight);


         $SSROThers = array(
                      "SSR_Code"=> "CHLD",
                      "Text"=> "${'childSSR'.$x}",
                      "PersonName"=> array(
                         "NameNumber"=> "$adultCount.1"
                      ),
                      "SegmentNumber"=> "A"
                     );
         array_push($AllSsr, $SSROThers);
                  
                  
         }
      
   

    }else if($adult > 0 && $infants > 0){

      //Adult Part
      $adultCount = 0;
      for($x=0; $x < $adult; $x++){
         $adultCount++;  
         
         ${'afName'.$x} = strtoupper($_POST['adult'][$x]["afName"]);
         ${'alName'.$x} = strtoupper($_POST['adult'][$x]["alName"]);
         ${'agender'.$x} = $_POST['adult'][$x]["agender"];
         ${'adob'.$x} = $_POST['adult'][$x]["adob"];
         ${'apassNo'.$x} = $_POST['adult'][$x]["apassNo"];
         ${'apassEx'.$x} = $_POST['adult'][$x]["apassEx"];
         ${'apassNation'.$x} = $_POST['adult'][$x]["apassNation"];

         if(${'agender'.$x} == 'Male'){
            ${'agender'.$x} = 'M';
            ${'atitle'.$x} = 'MR';
         }else{
            ${'agender'.$x} = 'F';
            ${'atitle'.$x} = 'MS';
         }


            $Person = array(
                     "NameNumber"=>"$adultCount.1",
                     "GivenName"=>"${'afName'.$x} ${'atitle'.$x}",
                     "Surname"=>"${'alName'.$x}",
                     "Infant"=>false,
                     "PassengerType"=>"ADT",
                     "NameReference"=>"");
                     
            array_push($AllPerson, $Person);

            $AdvPax = array(
                        "Document"=>array(
                           "Number"=>"${'apassNo'.$x}",
                           "IssueCountry"=>"${'apassNation'.$x}",
                           "NationalityCountry"=>"${'apassNation'.$x}",
                           "ExpirationDate"=>"${'apassEx'.$x}",
                           "Type"=>"P"
                        ),
                        "PersonName"=> array(
                           "NameNumber"=>"$adultCount.1",
                           "GivenName"=> "${'afName'.$x}",
                           "MiddleName"=>"",
                           "Surname"=> "${'alName'.$x}",
                           "DateOfBirth"=>"${'adob'.$x}",
                           "Gender"=>"${'agender'.$x}"
                        ),
                        "SegmentNumber"=>"A"
                     );
            
            array_push($AdvancePassnger, $AdvPax);

            $Secureflight =  array("PersonName"=> 
                     array("NameNumber"=>"$adultCount.1",
                     "GivenName"=> "${'afName'.$x}",
                     "Surname"=>"${'alName'.$x}",
                     "DateOfBirth"=>"${'adob'.$x}",
                     "Gender"=>"${'agender'.$x}I"
                        ),
                        "SegmentNumber"=>"A",
                        "VendorPrefs" => array("Airline"=>
                        array("Hosted"=>false)
                        )

                     );


            array_push($AllSecureflight, $Secureflight);


            $SSROThers = array(
                        "SSR_Code"=> "OTHS",
                        "Text"=> "CC ${'afName'.$x} ${'afName'.$x}",
                        "PersonName"=> array(
                           "NameNumber"=> "$adultCount.1"
                        ),
                        "SegmentNumber"=> "A"
                        );
            array_push($AllSsr, $SSROThers);
                     
            $SSRCTCM =  array(
                        "SSR_Code"=> "CTCM",
                        "Text"=> "$phone",
                        "PersonName"=> array(
                           "NameNumber"=> "$adultCount.1"
                        ),
                        "SegmentNumber"=> "A"
                        );

            array_push($AllSsr, $SSRCTCM);
            
            $SSRCTCE =  array(
                        "SSR_Code"=> "CTCE",
                        "Text"=> "${'afName'.$x}//${'afName'.$x}.com",
                        "PersonName"=> array(
                           "NameNumber"=> "$adultCount.1"
                        ),
                        "SegmentNumber"=> "A"
                     );
                     

            array_push($AllSsr, $SSRCTCE);
                               
      }  
      
      //Infant Part

      $infantCount=0;
      for($x=0; $x < $infants; $x++){
       $adultCount++;
       $infantCount++;  
         
      ${'ifName'.$x} = strtoupper($_POST['infant'][$x]["ifName"]);
		${'ilName'.$x} = strtoupper($_POST['infant'][$x]["ilName"]);
		${'igender'.$x} = $_POST['infant'][$x]["igender"];
		${'idob'.$x} = $_POST['infant'][$x]["idob"];
		${'ipassNo'.$x} = $_POST['infant'][$x]["ipassNo"];
		${'ipassEx'.$x} = $_POST['infant'][$x]["ipassEx"];
		${'ipassNation'.$x} = $_POST['infant'][$x]["ipassNation"];

      //AgeCalculate
      ${'idate'.$x} = date_create(${'idob'.$x});
      ${'infantSSR'.$x} = date_format(${'idate'.$x},"dMy");
      ${'dobCount'.$x} = new DateTime(${'idob'.$x});           
      ${'AgeCount'.$x} = $now->diff(${'dobCount'.$x});        
      ${'age'.$x} = ${'AgeCount'.$x}->y;
      ${'iAge'.$x} = str_pad(${'age'.$x}, 2, '0', STR_PAD_LEFT);
         

		if(${'igender'.$x} == 'Male'){
         ${'igender'.$x} = 'M';
			${'ititle'.$x} = 'MSTR';
		}else{
         ${'igender'.$x} = 'F';
			${'ititle'.$x} = 'MISS';
		}


         $Person = array(
                   "NameNumber"=>"$infantCount.1",
                   "GivenName"=>"${'ifName'.$x} ${'ititle'.$x}",
                   "Surname"=>"${'ilName'.$x}",
                   "Infant"=>true,
                   "PassengerType"=>"INF",
                   "NameReference"=>"I${'iAge'.$x}");
                   
         array_push($AllPerson, $Person);

         $AdvPax = array(
                      "Document"=>array(
                         "Number"=>"${'ipassNo'.$x}",
                         "IssueCountry"=>"${'ipassNation'.$x}",
                         "NationalityCountry"=>"${'ipassNation'.$x}",
                         "ExpirationDate"=>"${'ipassEx'.$x}",
                         "Type"=>"P"
                      ),
                      "PersonName"=> array(
                         "NameNumber"=>"$infantCount.1",
                         "GivenName"=> "${'ifName'.$x}",
                         "MiddleName"=>"",
                         "Surname"=>"${'ilName'.$x}",
                         "DateOfBirth"=>"${'idob'.$x}",
                         "Gender"=>"${'igender'.$x}I"
                      ),
                      "SegmentNumber"=>"A"
                   );
         
         array_push($AdvancePassnger, $AdvPax);

         $Secureflight =  array("PersonName"=> 
                     array("NameNumber"=>"$infantCount.1",
                     "GivenName"=> "${'ifName'.$x}",
                     "Surname"=>"${'ilName'.$x}",
                     "DateOfBirth"=>"${'idob'.$x}",
                     "Gender"=>"${'igender'.$x}I"
                        ),
                        "SegmentNumber"=>"A",
                        "VendorPrefs" => array("Airline"=>
                        array("Hosted"=>false)
                        )

                     );


            array_push($AllSecureflight, $Secureflight);


         $SSROThers = array(
                      "SSR_Code"=> "INFT",
                      "Text"=> "${'ifName'.$x}/${'ilName'.$x} ${'ititle'.$x}/${'infantSSR'.$x}",
                      "PersonName"=> array(
                         "NameNumber"=> "$infantCount.1"
                      ),
                      "SegmentNumber"=> "A"
                     );
                     
         array_push($AllSsr, $SSROThers);
                  
          
         }

   

    }else if($adult > 0){

      $adultCount = 0;
      for($x=0; $x < $adult; $x++){
       $adultCount++;  
         
         ${'afName'.$x} = strtoupper($_POST['adult'][$x]["afName"]);
         ${'alName'.$x} = strtoupper($_POST['adult'][$x]["alName"]);
         ${'agender'.$x} = $_POST['adult'][$x]["agender"];
         ${'adob'.$x} = $_POST['adult'][$x]["adob"];
         ${'apassNo'.$x} = $_POST['adult'][$x]["apassNo"];
         ${'apassEx'.$x} = $_POST['adult'][$x]["apassEx"];
         ${'apassNation'.$x} = $_POST['adult'][$x]["apassNation"];

         if(${'agender'.$x} == 'Male'){
            ${'agender'.$x} = 'M';
            ${'atitle'.$x} = 'MR';
         }else{
            ${'agender'.$x} = 'F';
            ${'atitle'.$x} = 'MS';
         }


         $Person = array(
                   "NameNumber"=>"$adultCount.1",
                   "GivenName"=>"${'afName'.$x} ${'atitle'.$x}",
                   "Surname"=>"${'alName'.$x}",
                   "Infant"=>false,
                   "PassengerType"=>"ADT",
                   "NameReference"=>"");
                   
         array_push($AllPerson, $Person);

         $AdvPax = array(
                      "Document"=>array(
                         "Number"=>"${'apassNo'.$x}",
                         "IssueCountry"=>"${'apassNation'.$x}",
                         "NationalityCountry"=>"${'apassNation'.$x}",
                         "ExpirationDate"=>"${'apassEx'.$x}",
                         "Type"=>"P"
                      ),
                      "PersonName"=> array(
                         "NameNumber"=>"$adultCount.1",
                         "GivenName"=> "${'afName'.$x}",
                         "MiddleName"=>"",
                         "Surname"=>"${'alName'.$x}",
                         "DateOfBirth"=>"${'adob'.$x}",
                         "Gender"=>"${'agender'.$x}"
                      ),
                      "SegmentNumber"=>"A"
                   );
         
            array_push($AdvancePassnger, $AdvPax);

         $Secureflight =  array("PersonName"=> 
                     array("NameNumber"=>"$adultCount.1",
                     "GivenName"=> "${'afName'.$x}",
                     "Surname"=>"${'alName'.$x}",
                     "DateOfBirth"=>"${'adob'.$x}",
                     "Gender"=>"${'agender'.$x}"
                        ),
                        "SegmentNumber"=>"A",
                        "VendorPrefs" => array("Airline"=>
                        array("Hosted"=>false)
                        )
                     );


            array_push($AllSecureFlight, $Secureflight);


         $SSROThers = array(
                      "SSR_Code"=> "OTHS",
                      "Text"=> "CC ${'afName'.$x} ${'afName'.$x}",
                      "PersonName"=> array(
                         "NameNumber"=> "$adultCount.1"
                      ),
                      "SegmentNumber"=> "A"
                     );
         array_push($AllSsr, $SSROThers);
                  
         $SSRCTCM =  array(
                      "SSR_Code"=> "CTCM",
                      "Text"=> "$phone",
                      "PersonName"=> array(
                         "NameNumber"=> "$adultCount.1"
                      ),
                      "SegmentNumber"=> "A"
                     );

         array_push($AllSsr, $SSRCTCM);
         
         $SSRCTCE =  array(
                      "SSR_Code"=> "CTCE",
                      "Text"=> "${'afName'.$x}//${'afName'.$x}.com",
                      "PersonName"=> array(
                         "NameNumber"=> "$adultCount.1"
                      ),
                      "SegmentNumber"=> "A"
                   );
                  

         array_push($AllSsr, $SSRCTCE);
         
                   
         }    
   
}

$tripType = $_POST['tripType'];
$Segment = $_POST['segment'];

    if($tripType == "1"){   
        if($Segment == 1){
            $departure = $_POST['segments'][0]['departure'];
            $arrival = $_POST['segments'][0]['arrival'];
            $dpTime = $_POST['segments'][0]['dpTime'];
            $arrTime = $_POST['segments'][0]['arrTime'];
            $bCode = $_POST['segments'][0]['bCode'];
            $mCarrier = $_POST['segments'][0]['mCarrier'];
            $mCarrierFN = $_POST['segments'][0]['mCarrierFN'];
            $oCarrier = $_POST['segments'][0]['oCarrier'];
            $oCarrierFN = $_POST['segments'][0]['oCarrierFN'];


        $FlightSegment ='{
                    "DepartureDateTime":"'.$dpTime.'",
                    "ArrivalDateTime":"'.$arrTime.'",
                    "FlightNumber":"'.$mCarrierFN.'",
                    "NumberInParty":"'.$SeatReq.'",
                    "ResBookDesigCode":"'.$bCode.'",
                    "Status":"NN",
                    "OriginLocation":{
                        "LocationCode":"'.$departure.'"
                    },
                    "DestinationLocation":{
                        "LocationCode":"'.$arrival.'"
                    },
                    "MarketingAirline":{
                        "Code":"'.$mCarrier.'",
                        "FlightNumber":"'.$mCarrierFN.'"
                    }
                    }';


            }else if($Segment == 2){

                $departure = $_POST['segments'][0]['departure'];
                $arrival = $_POST['segments'][0]['arrival'];
                $dpTime = $_POST['segments'][0]['dpTime'];
                $arrTime = $_POST['segments'][0]['arrTime'];
                $bCode = $_POST['segments'][0]['bCode'];
                $mCarrier = $_POST['segments'][0]['mCarrier'];
                $mCarrierFN = $_POST['segments'][0]['mCarrierFN'];
                $oCarrier = $_POST['segments'][0]['oCarrier'];
                $oCarrierFN = $_POST['segments'][0]['oCarrierFN'];

                $departure1 = $_POST['segments'][1]['departure'];
                $arrival1 = $_POST['segments'][1]['arrival'];
                $dpTime1 = $_POST['segments'][1]['dpTime'];
                $arrTime1 = $_POST['segments'][1]['arrTime'];
                $bCode1 = $_POST['segments'][1]['bCode'];
                $mCarrier1 = $_POST['segments'][1]['mCarrier'];
                $mCarrierFN1 = $_POST['segments'][1]['mCarrierFN'];
                $oCarrier1 = $_POST['segments'][1]['oCarrier'];
                $oCarrierFN1 = $_POST['segments'][1]['oCarrierFN'];

                
            $FlightSegment = '{
                        "DepartureDateTime":"'.$dpTime.'",
                        "ArrivalDateTime":"'.$arrTime.'",
                        "FlightNumber":"'.$mCarrierFN.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$bCode.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$departure.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$arrival.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$mCarrier.'",
                            "FlightNumber":"'.$mCarrierFN.'"
                        }
                        },{
                        "DepartureDateTime":"'.$dpTime1.'",
                        "ArrivalDateTime":"'.$arrTime1.'",
                        "FlightNumber":"'.$mCarrierFN1.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$bCode1.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$departure1.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$arrival1.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$mCarrier1.'",
                            "FlightNumber":"'.$mCarrierFN1.'"
                        }
                        }';


            }else if($Segment == 3){

                $departure = $_POST['segments'][0]['departure'];
                $arrival = $_POST['segments'][0]['arrival'];
                $dpTime = $_POST['segments'][0]['dpTime'];
                $arrTime = $_POST['segments'][0]['arrTime'];
                $bCode = $_POST['segments'][0]['bCode'];
                $mCarrier = $_POST['segments'][0]['mCarrier'];
                $mCarrierFN = $_POST['segments'][0]['mCarrierFN'];
                $oCarrier = $_POST['segments'][0]['oCarrier'];
                $oCarrierFN = $_POST['segments'][0]['oCarrierFN'];

                $departure1 = $_POST['segments'][1]['departure'];
                $arrival1 = $_POST['segments'][1]['arrival'];
                $dpTime1 = $_POST['segments'][1]['dpTime'];
                $arrTime1 = $_POST['segments'][1]['arrTime'];
                $bCode1 = $_POST['segments'][1]['bCode'];
                $mCarrier1 = $_POST['segments'][1]['mCarrier'];
                $mCarrierFN1 = $_POST['segments'][1]['mCarrierFN'];
                $oCarrier1 = $_POST['segments'][1]['oCarrier'];
                $oCarrierFN1 = $_POST['segments'][1]['oCarrierFN'];

                $departure2 = $_POST['segments'][2]['departure'];
                $arrival2 = $_POST['segments'][2]['arrival'];
                $dpTime2 = $_POST['segments'][2]['dpTime'];
                $arrTime2 = $_POST['segments'][2]['arrTime'];
                $bCode2 = $_POST['segments'][2]['bCode'];
                $mCarrier2 = $_POST['segments'][2]['mCarrier'];
                $mCarrierFN2 = $_POST['segments'][2]['mCarrierFN'];
                $oCarrier2 = $_POST['segments'][2]['oCarrier'];
                $oCarrierFN2 = $_POST['segments'][2]['oCarrierFN'];

                
                $FlightSegment = '{
                        "DepartureDateTime":"'.$dpTime.'",
                        "ArrivalDateTime":"'.$arrTime.'",
                        "FlightNumber":"'.$mCarrierFN.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$bCode.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$departure.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$arrival.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$mCarrier.'",
                            "FlightNumber":"'.$mCarrierFN.'"
                        }
                        },{
                        "DepartureDateTime":"'.$dpTime1.'",
                        "ArrivalDateTime":"'.$arrTime1.'",
                        "FlightNumber":"'.$mCarrierFN1.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$bCode1.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$departure1.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$arrival1.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$mCarrier1.'",
                            "FlightNumber":"'.$mCarrierFN1.'"
                        }
                        },{
                        "DepartureDateTime":"'.$dpTime2.'",
                        "ArrivalDateTime":"'.$arrTime2.'",
                        "FlightNumber":"'.$mCarrierFN2.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$bCode2.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$departure2.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$arrival2.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$mCarrier2.'",
                            "FlightNumber":"'.$mCarrierFN2.'"
                        }
                        }';
            }

    }else if($tripType == "2"){
            if($Segment == 1){           
                $godeparture = $_POST['segments']['go'][0]['departure'];
                $goarrival = $_POST['segments']['go'][0]['arrival'];
                $godpTime = $_POST['segments']['go'][0]['dpTime'];
                $goarrTime = $_POST['segments']['go'][0]['arrTime'];
                $gobCode = $_POST['segments']['go'][0]['bCode'];
                $gomCarrier = $_POST['segments']['go'][0]['mCarrier'];
                $gomCarrierFN = $_POST['segments']['go'][0]['mCarrierFN'];
                $gooCarrier = $_POST['segments']['go'][0]['oCarrier'];
                $gooCarrierFN = $_POST['segments']['go'][0]['oCarrierFN'];

                $backdeparture = $_POST['segments']['back'][0]['departure'];
                $backarrival = $_POST['segments']['back'][0]['arrival'];
                $backdpTime = $_POST['segments']['back'][0]['dpTime'];
                $backarrTime = $_POST['segments']['back'][0]['arrTime'];
                $backbCode = $_POST['segments']['back'][0]['bCode'];
                $backmCarrier = $_POST['segments']['back'][0]['mCarrier'];
                $backmCarrierFN = $_POST['segments']['back'][0]['mCarrierFN'];
                $backoCarrier = $_POST['segments']['back'][0]['oCarrier'];
                $backoCarrierFN = $_POST['segments']['back'][0]['oCarrierFN'];


                $FlightSegment ='{
                        "DepartureDateTime":"'.$godpTime.'",
                        "ArrivalDateTime":"'.$goarrTime.'",
                        "FlightNumber":"'.$gomCarrierFN.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$gobCode.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$godeparture.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$goarrival.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$gomCarrier.'",
                            "FlightNumber":"'.$gomCarrierFN.'"
                        }
                        },
                        {
                        "DepartureDateTime":"'.$backdpTime.'",
                        "ArrivalDateTime":"'.$backarrTime.'",
                        "FlightNumber":"'.$backmCarrierFN.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$backbCode.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$backdeparture.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$backarrival.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$backmCarrier.'",
                            "FlightNumber":"'.$backmCarrierFN.'"
                        }
                        }';


            }else if($Segment == 2){

                $godeparture = $_POST['segments']['go'][0]['departure'];
                $goarrival = $_POST['segments']['go'][0]['arrival'];
                $godpTime = $_POST['segments']['go'][0]['dpTime'];
                $goarrTime = $_POST['segments']['go'][0]['arrTime'];
                $gobCode = $_POST['segments']['go'][0]['bCode'];
                $gomCarrier = $_POST['segments']['go'][0]['mCarrier'];
                $gomCarrierFN = $_POST['segments']['go'][0]['mCarrierFN'];
                $gooCarrier = $_POST['segments']['go'][0]['oCarrier'];
                $gooCarrierFN = $_POST['segments']['go'][0]['oCarrierFN'];

                $godeparture1 = $_POST['segments']['go'][1]['departure'];
                $goarrival1 = $_POST['segments']['go'][1]['arrival'];
                $godpTime1 = $_POST['segments']['go'][1]['dpTime'];
                $goarrTime1 = $_POST['segments']['go'][1]['arrTime'];
                $gobCode1 = $_POST['segments']['go'][1]['bCode'];
                $gomCarrier1 = $_POST['segments']['go'][1]['mCarrier'];
                $gomCarrierFN1 = $_POST['segments']['go'][1]['mCarrierFN'];
                $gooCarrier1 = $_POST['segments']['go'][1]['oCarrier'];
                $gooCarrierFN1 = $_POST['segments']['go'][1]['oCarrierFN'];

                $backdeparture = $_POST['segments']['back'][0]['departure'];
                $backarrival = $_POST['segments']['back'][0]['arrival'];
                $backdpTime = $_POST['segments']['back'][0]['dpTime'];
                $backarrTime = $_POST['segments']['back'][0]['arrTime'];
                $backbCode = $_POST['segments']['back'][0]['bCode'];
                $backmCarrier = $_POST['segments']['back'][0]['mCarrier'];
                $backmCarrierFN = $_POST['segments']['back'][0]['mCarrierFN'];
                $backoCarrier = $_POST['segments']['back'][0]['oCarrier'];
                $backoCarrierFN = $_POST['segments']['back'][0]['oCarrierFN'];

                $backdeparture1 = $_POST['segments']['back'][1]['departure'];
                $backarrival1 = $_POST['segments']['back'][1]['arrival'];
                $backdpTime1 = $_POST['segments']['back'][1]['dpTime'];
                $backarrTime1 = $_POST['segments']['back'][1]['arrTime'];
                $backbCode1 = $_POST['segments']['back'][1]['bCode'];
                $backmCarrier1 = $_POST['segments']['back'][1]['mCarrier'];
                $backmCarrierFN1 = $_POST['segments']['back'][1]['mCarrierFN'];
                $backoCarrier1 = $_POST['segments']['back'][1]['oCarrier'];
                $backoCarrierFN1 = $_POST['segments']['back'][1]['oCarrierFN'];


                $FlightSegment ='{
                        "DepartureDateTime":"'.$godpTime.'",
                        "ArrivalDateTime":"'.$goarrTime.'",
                        "FlightNumber":"'.$gomCarrierFN.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$gobCode.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$godeparture.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$goarrival.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$gomCarrier.'",
                            "FlightNumber":"'.$gomCarrierFN.'"
                        }
                        },
                        {
                        "DepartureDateTime":"'.$godpTime1.'",
                        "ArrivalDateTime":"'.$goarrTime1.'",
                        "FlightNumber":"'.$gomCarrierFN1.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$gobCode1.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$godeparture1.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$goarrival1.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$gomCarrier1.'",
                            "FlightNumber":"'.$gomCarrierFN1.'"
                        }
                        },
                        {
                        "DepartureDateTime":"'.$backdpTime.'",
                        "ArrivalDateTime":"'.$backarrTime.'",
                        "FlightNumber":"'.$backmCarrierFN.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$backbCode.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$backdeparture.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$backarrival.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$backmCarrier.'",
                            "FlightNumber":"'.$backmCarrierFN.'"
                        }
                        },
                        {
                        "DepartureDateTime":"'.$backdpTime1.'",
                        "ArrivalDateTime":"'.$backarrTime1.'",
                        "FlightNumber":"'.$backmCarrierFN1.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$backbCode1.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$backdeparture1.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$backarrival1.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$backmCarrier1.'",
                            "FlightNumber":"'.$backmCarrierFN1.'"
                        }
                        }';


                }else if($Segment == 3){

                    
                $godeparture = $_POST['segments']['go'][0]['departure'];
                $goarrival = $_POST['segments']['go'][0]['arrival'];
                $godpTime = $_POST['segments']['go'][0]['dpTime'];
                $goarrTime = $_POST['segments']['go'][0]['arrTime'];
                $gobCode = $_POST['segments']['go'][0]['bCode'];
                $gomCarrier = $_POST['segments']['go'][0]['mCarrier'];
                $gomCarrierFN = $_POST['segments']['go'][0]['mCarrierFN'];
                $gooCarrier = $_POST['segments']['go'][0]['oCarrier'];
                $gooCarrierFN = $_POST['segments']['go'][0]['oCarrierFN'];

                $godeparture1 = $_POST['segments']['go'][1]['departure'];
                $goarrival1 = $_POST['segments']['go'][1]['arrival'];
                $godpTime1 = $_POST['segments']['go'][1]['dpTime'];
                $goarrTime1 = $_POST['segments']['go'][1]['arrTime'];
                $gobCode1 = $_POST['segments']['go'][1]['bCode'];
                $gomCarrier1 = $_POST['segments']['go'][1]['mCarrier'];
                $gomCarrierFN1 = $_POST['segments']['go'][1]['mCarrierFN'];
                $gooCarrier1 = $_POST['segments']['go'][1]['oCarrier'];
                $gooCarrierFN1 = $_POST['segments']['go'][1]['oCarrierFN'];
                

                $godeparture2 = $_POST['segments']['go'][2]['departure'];
                $goarrival2 = $_POST['segments']['go'][2]['arrival'];
                $godpTime2 = $_POST['segments']['go'][2]['dpTime'];
                $goarrTime2 = $_POST['segments']['go'][2]['arrTime'];
                $gobCode2 = $_POST['segments']['go'][2]['bCode'];
                $gomCarrier2 = $_POST['segments']['go'][2]['mCarrier'];
                $gomCarrierFN2 = $_POST['segments']['go'][2]['mCarrierFN'];
                $gooCarrier2 = $_POST['segments']['go'][2]['oCarrier'];
                $gooCarrierFN2 = $_POST['segments']['go'][2]['oCarrierFN'];
                

                $backdeparture = $_POST['segments']['back'][0]['departure'];
                $backarrival = $_POST['segments']['back'][0]['arrival'];
                $backdpTime = $_POST['segments']['back'][0]['dpTime'];
                $backarrTime = $_POST['segments']['back'][0]['arrTime'];
                $backbCode = $_POST['segments']['back'][0]['bCode'];
                $backmCarrier = $_POST['segments']['back'][0]['mCarrier'];
                $backmCarrierFN = $_POST['segments']['back'][0]['mCarrierFN'];
                $backoCarrier = $_POST['segments']['back'][0]['oCarrier'];
                $backoCarrierFN = $_POST['segments']['back'][0]['oCarrierFN'];

                $backdeparture1 = $_POST['segments']['back'][1]['departure'];
                $backarrival1 = $_POST['segments']['back'][1]['arrival'];
                $backdpTime1 = $_POST['segments']['back'][1]['dpTime'];
                $backarrTime1 = $_POST['segments']['back'][1]['arrTime'];
                $backbCode1 = $_POST['segments']['back'][1]['bCode'];
                $backmCarrier1 = $_POST['segments']['back'][1]['mCarrier'];
                $backmCarrierFN1 = $_POST['segments']['back'][1]['mCarrierFN'];
                $backoCarrier1 = $_POST['segments']['back'][1]['oCarrier'];
                $backoCarrierFN1 = $_POST['segments']['back'][1]['oCarrierFN'];

                $backdeparture2 = $_POST['segments']['back'][2]['departure'];
                $backarrival2 = $_POST['segments']['back'][2]['arrival'];
                $backdpTime2 = $_POST['segments']['back'][2]['dpTime'];
                $backarrTime2 = $_POST['segments']['back'][2]['arrTime'];
                $backbCode2 = $_POST['segments']['back'][2]['bCode'];
                $backmCarrier2 = $_POST['segments']['back'][2]['mCarrier'];
                $backmCarrierFN2 = $_POST['segments']['back'][2]['mCarrierFN'];
                $backoCarrier2 = $_POST['segments']['back'][2]['oCarrier'];
                $backoCarrierFN2 = $_POST['segments']['back'][2]['oCarrierFN'];


                   $FlightSegment ='{
                        "DepartureDateTime":"'.$godpTime.'",
                        "ArrivalDateTime":"'.$goarrTime.'",
                        "FlightNumber":"'.$gomCarrierFN.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$gobCode.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$godeparture.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$goarrival.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$gomCarrier.'",
                            "FlightNumber":"'.$gomCarrierFN.'"
                        }
                        },
                        {
                        "DepartureDateTime":"'.$godpTime1.'",
                        "ArrivalDateTime":"'.$goarrTime1.'",
                        "FlightNumber":"'.$gomCarrierFN1.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$gobCode1.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$godeparture1.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$goarrival1.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$gomCarrier1.'",
                            "FlightNumber":"'.$gomCarrierFN1.'"
                        }
                        },{
                        "DepartureDateTime":"'.$godpTime2.'",
                        "ArrivalDateTime":"'.$goarrTime2.'",
                        "FlightNumber":"'.$gomCarrierFN2.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$gobCode2.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$godeparture2.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$goarrival2.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$gomCarrier2.'",
                            "FlightNumber":"'.$gomCarrierFN2.'"
                        }
                        },
                        {
                        "DepartureDateTime":"'.$backdpTime.'",
                        "ArrivalDateTime":"'.$backarrTime.'",
                        "FlightNumber":"'.$backmCarrierFN.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$backbCode.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$backdeparture.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$backarrival.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$backmCarrier.'",
                            "FlightNumber":"'.$backmCarrierFN.'"
                        }
                        },
                        {
                        "DepartureDateTime":"'.$backdpTime1.'",
                        "ArrivalDateTime":"'.$backarrTime1.'",
                        "FlightNumber":"'.$backmCarrierFN1.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$backbCode1.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$backdeparture1.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$backarrival1.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$backmCarrier1.'",
                            "FlightNumber":"'.$backmCarrierFN1.'"
                        }
                        },
                        {
                        "DepartureDateTime":"'.$backdpTime2.'",
                        "ArrivalDateTime":"'.$backarrTime2.'",
                        "FlightNumber":"'.$backmCarrierFN2.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$backbCode2.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$backdeparture2.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$backarrival2.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$backmCarrier2.'",
                            "FlightNumber":"'.$backmCarrierFN2.'"
                        }
                        }';
            }else if($Segment == 21){

                $godeparture = $_POST['segments']['go'][0]['departure'];
                $goarrival = $_POST['segments']['go'][0]['arrival'];
                $godpTime = $_POST['segments']['go'][0]['dpTime'];
                $goarrTime = $_POST['segments']['go'][0]['arrTime'];
                $gobCode = $_POST['segments']['go'][0]['bCode'];
                $gomCarrier = $_POST['segments']['go'][0]['mCarrier'];
                $gomCarrierFN = $_POST['segments']['go'][0]['mCarrierFN'];
                $gooCarrier = $_POST['segments']['go'][0]['oCarrier'];
                $gooCarrierFN = $_POST['segments']['go'][0]['oCarrierFN'];

                $godeparture1 = $_POST['segments']['go'][1]['departure'];
                $goarrival1 = $_POST['segments']['go'][1]['arrival'];
                $godpTime1 = $_POST['segments']['go'][1]['dpTime'];
                $goarrTime1 = $_POST['segments']['go'][1]['arrTime'];
                $gobCode1 = $_POST['segments']['go'][1]['bCode'];
                $gomCarrier1 = $_POST['segments']['go'][1]['mCarrier'];
                $gomCarrierFN1 = $_POST['segments']['go'][1]['mCarrierFN'];
                $gooCarrier1 = $_POST['segments']['go'][1]['oCarrier'];
                $gooCarrierFN1 = $_POST['segments']['go'][1]['oCarrierFN'];

                $backdeparture = $_POST['segments']['back'][0]['departure'];
                $backarrival = $_POST['segments']['back'][0]['arrival'];
                $backdpTime = $_POST['segments']['back'][0]['dpTime'];
                $backarrTime = $_POST['segments']['back'][0]['arrTime'];
                $backbCode = $_POST['segments']['back'][0]['bCode'];
                $backmCarrier = $_POST['segments']['back'][0]['mCarrier'];
                $backmCarrierFN = $_POST['segments']['back'][0]['mCarrierFN'];
                $backoCarrier = $_POST['segments']['back'][0]['oCarrier'];
                $backoCarrierFN = $_POST['segments']['back'][0]['oCarrierFN'];


                $FlightSegment ='{
                        "DepartureDateTime":"'.$godpTime.'",
                        "ArrivalDateTime":"'.$goarrTime.'",
                        "FlightNumber":"'.$gomCarrierFN.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$gobCode.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$godeparture.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$goarrival.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$gomCarrier.'",
                            "FlightNumber":"'.$gomCarrierFN.'"
                        }
                        },
                        {
                        "DepartureDateTime":"'.$godpTime1.'",
                        "ArrivalDateTime":"'.$goarrTime1.'",
                        "FlightNumber":"'.$gomCarrierFN1.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$gobCode1.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$godeparture1.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$goarrival1.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$gomCarrier1.'",
                            "FlightNumber":"'.$gomCarrierFN1.'"
                        }
                        },
                        {
                        "DepartureDateTime":"'.$backdpTime.'",
                        "ArrivalDateTime":"'.$backarrTime.'",
                        "FlightNumber":"'.$backmCarrierFN.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$backbCode.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$backdeparture.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$backarrival.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$backmCarrier.'",
                            "FlightNumber":"'.$backmCarrierFN.'"
                        }
                        }';


                }else if($Segment == 12){

                $godeparture = $_POST['segments']['go'][0]['departure'];
                $goarrival = $_POST['segments']['go'][0]['arrival'];
                $godpTime = $_POST['segments']['go'][0]['dpTime'];
                $goarrTime = $_POST['segments']['go'][0]['arrTime'];
                $gobCode = $_POST['segments']['go'][0]['bCode'];
                $gomCarrier = $_POST['segments']['go'][0]['mCarrier'];
                $gomCarrierFN = $_POST['segments']['go'][0]['mCarrierFN'];
                $gooCarrier = $_POST['segments']['go'][0]['oCarrier'];
                $gooCarrierFN = $_POST['segments']['go'][0]['oCarrierFN'];


                $backdeparture = $_POST['segments']['back'][0]['departure'];
                $backarrival = $_POST['segments']['back'][0]['arrival'];
                $backdpTime = $_POST['segments']['back'][0]['dpTime'];
                $backarrTime = $_POST['segments']['back'][0]['arrTime'];
                $backbCode = $_POST['segments']['back'][0]['bCode'];
                $backmCarrier = $_POST['segments']['back'][0]['mCarrier'];
                $backmCarrierFN = $_POST['segments']['back'][0]['mCarrierFN'];
                $backoCarrier = $_POST['segments']['back'][0]['oCarrier'];
                $backoCarrierFN = $_POST['segments']['back'][0]['oCarrierFN'];

                $backdeparture1 = $_POST['segments']['back'][1]['departure'];
                $backarrival1 = $_POST['segments']['back'][1]['arrival'];
                $backdpTime1 = $_POST['segments']['back'][1]['dpTime'];
                $backarrTime1 = $_POST['segments']['back'][1]['arrTime'];
                $backbCode1 = $_POST['segments']['back'][1]['bCode'];
                $backmCarrier1 = $_POST['segments']['back'][1]['mCarrier'];
                $backmCarrierFN1 = $_POST['segments']['back'][1]['mCarrierFN'];
                $backoCarrier1 = $_POST['segments']['back'][1]['oCarrier'];
                $backoCarrierFN1 = $_POST['segments']['back'][1]['oCarrierFN'];


                $FlightSegment ='{
                        "DepartureDateTime":"'.$godpTime.'",
                        "ArrivalDateTime":"'.$goarrTime.'",
                        "FlightNumber":"'.$gomCarrierFN.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$gobCode.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$godeparture.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$goarrival.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$gomCarrier.'",
                            "FlightNumber":"'.$gomCarrierFN.'"
                        }
                        },
                        {
                        "DepartureDateTime":"'.$backdpTime.'",
                        "ArrivalDateTime":"'.$backarrTime.'",
                        "FlightNumber":"'.$backmCarrierFN.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$backbCode.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$backdeparture.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$backarrival.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$backmCarrier.'",
                            "FlightNumber":"'.$backmCarrierFN.'"
                        }
                        },
                        {
                        "DepartureDateTime":"'.$backdpTime1.'",
                        "ArrivalDateTime":"'.$backarrTime1.'",
                        "FlightNumber":"'.$backmCarrierFN1.'",
                        "NumberInParty":"'.$SeatReq.'",
                        "ResBookDesigCode":"'.$backbCode1.'",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"'.$backdeparture1.'"
                        },
                        "DestinationLocation":{
                            "LocationCode":"'.$backarrival1.'"
                        },
                        "MarketingAirline":{
                            "Code":"'.$backmCarrier1.'",
                            "FlightNumber":"'.$backmCarrierFN1.'"
                        }
                        }';


                }
    
    
}


$PersonFinal = json_encode($AllPerson);
$AdvPassengerFinal = json_encode($AdvancePassnger);
$FinalSecureFlight = json_encode($AllSecureFlight);
$SSRFinal = json_encode($AllSsr);


   $Request ='{
      "CreatePassengerNameRecordRQ":{
         "targetCity":"FD3K",
         "haltOnAirPriceError":true,
         "TravelItineraryAddInfo":{
            "AgencyInfo":{
               "Address":{
                  "AddressLine":"Flyway International",
                  "CityName":"Dhaka",
                  "CountryCode":"BD",
                  "PostalCode":"1215",
                  "StateCountyProv":{
                     "StateCode":"BD"
                  },
                  "StreetNmbr":"Dhaka"
               },
               "Ticketing":{
                  "TicketType":"7TAW"
               }
            },
            "CustomerInfo":{
               "ContactNumbers":{
                  "ContactNumber":[
                     {
                        "NameNumber":"1.1",
                        "Phone":"'.$phone.'",
                        "PhoneUseType":"H"
                     }
                  ]
               },
               "PersonName": '.$PersonFinal.'
            }
         },
         "AirBook":{
            "HaltOnStatus":[
               {
                  "Code":"HL"
               },
               {
                  "Code":"KK"
               },
               {
                  "Code":"LL"
               },
               {
                  "Code":"NN"
               },
               {
                  "Code":"NO"
               },
               {
                  "Code":"UC"
               },
               {
                  "Code":"US"
               }
            ],
            "OriginDestinationInformation":{
               "FlightSegment": ['.$FlightSegment.']
            },
            "RedisplayReservation":{
               "NumAttempts":10,
               "WaitInterval":300
            }
         },
         "AirPrice":[
            {
               "PriceRequestInformation":{
                  "Retain":true,
                  "OptionalQualifiers":{
                     "FOP_Qualifiers":{
                        "BasicFOP":{
                           "Type":"CASH"
                        }
                     },
                     "PricingQualifiers":{
                        "PassengerType":['.$paxRequest.']
                     }
                  }
               }
            }
         ],
         "SpecialReqDetails":{
            "SpecialService":{
               "SpecialServiceInfo":{
                  "AdvancePassenger": '.$AdvPassengerFinal.',
                  "SecureFlight": '.$FinalSecureFlight.',
                  "Service":'.$SSRFinal.'
               }
            }
         },
         "PostProcessing":{
            "EndTransaction":{
               "Source":{
                  "ReceivedFrom":"API WEB"
               }
            },
            "RedisplayReservation":{
               "waitInterval":100
            }
         }
      }
   }';

 //print_r($Request);

 	try{

	$client_id= base64_encode("V1:396724:FD3K:AA");
	//$client_secret = base64_encode("280ff537"); //cert
	$client_secret = base64_encode("FlWy967"); //prod

	$token = base64_encode($client_id.":".$client_secret);
	$data='grant_type=client_credentials';

		$headers = array(
			'Authorization: Basic '.$token,
			'Accept: /',
			'Content-Type: application/x-www-form-urlencoded'
		);

		$ch = curl_init();
		//curl_setopt($ch,CURLOPT_URL,"https://api-crt.cert.havail.sabre.com/v2/auth/token");
		curl_setopt($ch,CURLOPT_URL,"https://api.platform.sabre.com/v2/auth/token");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($ch);
		curl_close($ch);
		$resf = json_decode($res,1);
		$access_token = $resf['access_token'];

	}catch (Exception $e){
		
	}


//Curl start
$curl = curl_init();

curl_setopt_array($curl, array(
  //CURLOPT_URL => 'https://api-crt.cert.havail.sabre.com/v2.4.0/passenger/records?mode=create',   //Testing
  CURLOPT_URL => 'https://api.platform.sabre.com/v2.4.0/passenger/records?mode=create',   //Live 
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'POST',
  CURLOPT_POSTFIELDS => $Request,
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Bearer '.$access_token,
  ),
));


   $response = curl_exec($curl);
   //echo $response;

   curl_close($curl);

      $result = json_decode($response, true);
      if(isset($result['CreatePassengerNameRecordRS']['ApplicationResults']['status'])){
         $status = $result['CreatePassengerNameRecordRS']['ApplicationResults']['status'];

         if($status == 'Complete'){
            $SabreResponse['status']= "success";
            $SabreResponse['message']= $result;
            
         }else if($status == 'Incomplete'){
            $SabreResponse['status']= "error";
            $SabreResponse['message']= "InComplete";
            $SabreResponse['response']= $result;                     
         }  
         echo json_encode($SabreResponse); 
      }
   
   }
}


?>