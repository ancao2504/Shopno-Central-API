<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



	$_POST = json_decode(file_get_contents('php://input'), true);

	$tripType = $_POST['tripType'];
	$adult = $_POST['adultCount'];
    $child = $_POST['childCount'];
    $infants =  $_POST['infantCount'];
    $segment = $_POST['segment'];
    $tDate = $_POST['tDate'];
    $eDate = $_POST['eDate'];
    $FareBasis = $_POST['fbcode'];
    $AirPricingSolutionKey = $_POST['airPriceSKey'];
	
    $AdultPassenger =array();
    $AdultPassengerType =array();

    $ChildPassenger =array();
    $ChildPassengerType =array();

    $InfantPassenger =array();
    $InfantPassengerType =array();

    $AllPassenger = array();

	
    if($tripType == 1){

    if($segment == '1'){
    $Cr = $_POST['segments'][0]['cr'];
    $AirSKey = $_POST['segments'][0]['airSegKey'];
    $BCode = $_POST['segments'][0]['bcode'];
    $Dep = $_POST['segments'][0]['dep'];
    $Arr = $_POST['segments'][0]['arr'];
    $FNo = $_POST['segments'][0]['Fno'];
    $G = $_POST['segments'][0]['G'];	
    $DepTime = $_POST['segments'][0]['DepTime'];
    $ArrTime  = $_POST['segments'][0]['ArrTime'];

    $From = $_POST['segments'][0]['dep'];
    $To = $_POST['segments'][0]['arr'];


    $AirSegments = <<<EOM
        <AirSegment Key="$AirSKey" Group="$G" Carrier="$Cr" FlightNumber="$FNo" ProviderCode="1G" Origin="$Dep" Destination="$Arr" DepartureTime="$DepTime" ArrivalTime="$ArrTime"></AirSegment>
    EOM;



    }else if($segment == '2'){
    $Cr = $_POST['segments'][0]['cr'];
    $AirSKey = $_POST['segments'][0]['airSegKey'];
    $BCode = $_POST['segments'][0]['bcode'];
    $Dep = $_POST['segments'][0]['dep'];
    $Arr = $_POST['segments'][0]['arr'];
    $FNo = $_POST['segments'][0]['Fno'];
    $G = $_POST['segments'][0]['G'];
    $DepTime = $_POST['segments'][0]['DepTime'];
    $ArrTime  = $_POST['segments'][0]['ArrTime'];


    //Segment 2

    $Cr1 = $_POST['segments'][1]['cr'];
    $AirSKey1 = $_POST['segments'][1]['airSegKey'];
    $BCode1 = $_POST['segments'][1]['bcode'];
    $Dep1 = $_POST['segments'][1]['dep'];
    $Arr1 = $_POST['segments'][1]['arr'];
    $FNo1 = $_POST['segments'][1]['Fno'];
    $G1 = $_POST['segments'][1]['G'];
    $DepTime1 = $_POST['segments'][1]['DepTime'];
    $ArrTime1  = $_POST['segments'][1]['ArrTime'];

    $From = $_POST['segments'][0]['dep'];
    $To = $_POST['segments'][1]['arr'];


        
    $AirSegments = <<<EOM
        <AirSegment Key="$AirSKey" Group="$G" Carrier="$Cr" FlightNumber="$FNo" ProviderCode="1G" Origin="$Dep" Destination="$Arr" DepartureTime="$DepTime" ArrivalTime="$ArrTime"></AirSegment>
        <AirSegment Key="$AirSKey1" Group="$G1" Carrier="$Cr1" FlightNumber="$FNo1" ProviderCode="1G" Origin="$Dep1" Destination="$Arr1" DepartureTime="$DepTime1" ArrivalTime="$ArrTime1"></AirSegment>
    EOM;

    }


    if($adult > 0 && $child> 0 && $infants> 0){

    $FareInfoKey = $_POST['adult'][0]['fareInfoKey'];
    $AirPriceInfoKey = $_POST['adult'][0]['airPriceInfoKey'];


    for($x = 0 ; $x < $adult; $x++){

        ${'afName'.$x} = $_POST['adult'][$x]["afName"];
        ${'alName'.$x} = $_POST['adult'][$x]["alName"];
        ${'agender'.$x} = $_POST['adult'][$x]["agender"];
        ${'adob'.$x} = date("dMy", strtotime($_POST['adult'][$x]["adob"]));
        ${'apassNo'.$x} = $_POST['adult'][$x]["apassNo"];
        ${'apassEx'.$x} = date("dMy", strtotime($_POST['adult'][$x]["apassEx"]));
        ${'apassNation'.$x} = $_POST['adult'][$x]["apassNation"];
        if(${'agender'.$x} = 'M'){
            $atitle = 'Mr';
        }else{
            $atitle = 'Mrs';
        }


        //Flight Info
        

        $AdultPassengerItem=<<<EOM
                        <BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="ADT$x" TravelerType="ADT" Gender="${'agender'.$x}" Nationality="${'apassNation'.$x}">
                            <BookingTravelerName Prefix="$atitle" First="${'afName'.$x} " Last="${'alName'.$x}" />
                            <SSR Type="DOCS" Status="HK" FreeText="P/${'apassNation'.$x}/${'apassNo'.$x}/${'apassNation'.$x}/${'adob'.$x}/${'agender'.$x}/${'apassEx'.$x}/${'alName'.$x}/${'afName'.$x} " Carrier="$Cr" />
                        </BookingTraveler>
                    EOM;
        
        array_push($AllPassenger, $AdultPassengerItem);


        $AdultPassengerTypeItem =<<<EOM
                    <PassengerType Code="ADT"  BookingTravelerRef="ADT$x" />
        EOM;

        array_push($AdultPassengerType, $AdultPassengerTypeItem);
                
    }



    $FareInfoKey1 = $_POST['child'][0]['fareInfoKey'];
    $AirPriceInfoKey1 = $_POST['child'][0]['airPriceInfoKey'];


    for($x = 0 ; $x < $child; $x++){

        ${'cfName'.$x} = $_POST['child'][$x]["cfName"];
        ${'clName'.$x} = $_POST['child'][$x]["clName"];
        ${'cgender'.$x} = $_POST['child'][$x]["cgender"];
        ${'cdob'.$x} = date("dMy", strtotime($_POST['child'][$x]["cdob"]));
        ${'cpassNo'.$x} = $_POST['child'][$x]["cpassNo"];
        ${'cpassEx'.$x} = date("dMy", strtotime($_POST['child'][$x]["cpassEx"]));
        ${'cpassNation'.$x} = $_POST['child'][$x]["cpassNation"];

        if(${'cgender'.$x} = 'M'){
            $ctitle = 'Mr';
        }else{
            $ctitle = 'Miss';
        }


        //Flight Info
        

        $ChildPassengerItem=<<<EOM
                        <BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="CNN$x" TravelerType="CNN" Gender="${'cgender'.$x}" Nationality="${'cpassNation'.$x}">
                            <BookingTravelerName Prefix="$ctitle" First="${'cfName'.$x} " Last="${'clName'.$x}" />
                            <SSR Type="DOCS" Status="HK" FreeText="P/${'cpassNation'.$x}/${'cpassNo'.$x}/${'cpassNation'.$x}/${'cdob'.$x}/${'cgender'.$x}/${'cpassEx'.$x}/${'clName'.$x}/${'cfName'.$x} " Carrier="$Cr" />
                        </BookingTraveler>
                    EOM;
        
                    array_push($AllPassenger, $ChildPassengerItem);


        $ChildPassengerTypeItem =<<<EOM
                    <PassengerType Code="CNN" BookingTravelerRef="CNN$x" />
        EOM;

        array_push($ChildPassengerType, $ChildPassengerTypeItem);
            
    }



    $FareInfoKey2 = $_POST['infant'][0]['fareInfoKey'];
    $AirPriceInfoKey2 = $_POST['infant'][0]['airPriceInfoKey'];


    for($x = 0 ; $x < $infants; $x++){

        ${'ifName'.$x} = $_POST['infant'][$x]["ifName"];
        ${'ilName'.$x} = $_POST['infant'][$x]["ilName"];
        ${'igender'.$x} = $_POST['infant'][$x]["igender"];
        ${'idob'.$x} = date("dMy", strtotime($_POST['infant'][$x]["idob"]));
        ${'ipassNo'.$x} = $_POST['infant'][$x]["ipassNo"];
        ${'ipassEx'.$x} = date("dMy", strtotime($_POST['infant'][$x]["ipassEx"]));
        ${'ipassNation'.$x} = $_POST['infant'][$x]["ipassNation"];

        if(${'igender'.$x} = 'M'){
            $ititle = 'Master';
        }else{
            $ititle = 'Miss';
        }


        //Flight Info
        

        $InfantPassengerItem=<<<EOM
                        <BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="INF$x" TravelerType="INF" Gender="${'igender'.$x}" Nationality="${'ipassNation'.$x}">
                            <BookingTravelerName Prefix="$ctitle" First="${'ifName'.$x} " Last="${'ilName'.$x}" />
                            <SSR Type="DOCS" Status="HK" FreeText="P/${'ipassNation'.$x}/${'ipassNo'.$x}/${'ipassNation'.$x}/${'idob'.$x}/${'igender'.$x}/${'ipassEx'.$x}/${'ilName'.$x}/${'ifName'.$x} " Carrier="$Cr" />
                            <NameRemark Category="AIR">
                                <RemarkData>${'idob'.$x}</RemarkData>
                            </NameRemark>
                        </BookingTraveler>
                    EOM;
        
                    array_push($AllPassenger, $InfantPassengerItem);


        $InfantPassengerTypeItem =<<<EOM
                    <PassengerType Code="INF"  BookingTravelerRef="INF$x" />
        EOM;

        array_push($InfantPassengerType, $InfantPassengerTypeItem);
                
    }



        $AdultPassengerTypeAll = implode(" ",$AdultPassengerType); 
        $ChildPassengerTypeAll = implode(" ",$ChildPassengerType); 
        $InfantPassengerTypeAll = implode(" ",$InfantPassengerType);

        if($segment == 1){
            $AdultBookingCode=<<<EOM
                    <BookingInfo BookingCode="$BCode" FareInfoRef="$FareInfoKey" SegmentRef="$AirSKey" />
                EOM;
            $ChildBookingCode=<<<EOM
                <BookingInfo BookingCode="$BCode" FareInfoRef="$FareInfoKey1" SegmentRef="$AirSKey" />
            EOM;
            $InfantBookingCode=<<<EOM
                    <BookingInfo BookingCode="$BCode" FareInfoRef="$FareInfoKey2" SegmentRef="$AirSKey" />
                EOM;

            
        }else if($segment == 2){	

            $AdultBookingCode =<<<EOM
                    <BookingInfo BookingCode="$BCode" FareInfoRef="$FareInfoKey" SegmentRef="$AirSKey" />
                    <BookingInfo BookingCode="$BCode1" FareInfoRef="$FareInfoKey" SegmentRef="$AirSKey1" />
                EOM;
            
            $ChildBookingCode =<<<EOM
                    <BookingInfo BookingCode="$BCode" FareInfoRef="$FareInfoKey1" SegmentRef="$AirSKey" />
                    <BookingInfo BookingCode="$BCode1" FareInfoRef="$FareInfoKey1" SegmentRef="$AirSKey1" />
                EOM;
                
            $InfantBookingCode =<<<EOM
                    <BookingInfo BookingCode="$BCode" FareInfoRef="$FareInfoKey2" SegmentRef="$AirSKey" />
                    <BookingInfo BookingCode="$BCode1" FareInfoRef="$FareInfoKey2" SegmentRef="$AirSKey1" />
                EOM;
        }


        $AirPricingSolution = <<<EOM
        <AirPricingSolution Key="$AirPricingSolutionKey" xmlns="http://www.travelport.com/schema/air_v51_0">
                $AirSegments
            <AirPricingInfo Key="$AirPriceInfoKey" PricingMethod="Guaranteed" PlatingCarrier="$Cr" ProviderCode="1G">
                <FareInfo Key="$FareInfoKey" FareBasis="$FareBasis" PassengerTypeCode="ADT" Origin="$From" Destination="$To" EffectiveDate="$eDate"></FareInfo>
                    $AdultBookingCode
                    $AdultPassengerTypeAll
            </AirPricingInfo>
            <AirPricingInfo Key="$AirPriceInfoKey1" PricingMethod="Guaranteed" PlatingCarrier="$Cr" ProviderCode="1G">
                <FareInfo Key="$FareInfoKey1" FareBasis="$FareBasis" PassengerTypeCode="CNN" Origin="$From" Destination="$To" EffectiveDate="$eDate"></FareInfo>
                    $ChildBookingCode
                    $ChildPassengerTypeAll
            </AirPricingInfo>
            <AirPricingInfo Key="$AirPriceInfoKey2" PricingMethod="Guaranteed" PlatingCarrier="$Cr" ProviderCode="1G">
                <FareInfo Key="$FareInfoKey2" FareBasis="$FareBasis" PassengerTypeCode="INF" Origin="$From" Destination="$To" EffectiveDate="$eDate"></FareInfo>
                    $InfantBookingCode
                    $InfantPassengerTypeAll
            </AirPricingInfo>
        </AirPricingSolution>	
    EOM;

    $AirPricingTicketingModifiers=<<<EOM
        <AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
            <AirPricingInfoRef Key="$AirPriceInfoKey" />
            <TicketingModifiers>
            <Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
            </TicketingModifiers>
        </AirPricingTicketingModifiers>
        <AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
            <AirPricingInfoRef Key="$AirPriceInfoKey1" />
            <TicketingModifiers>
            <Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
            </TicketingModifiers>
        </AirPricingTicketingModifiers>
        <AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
            <AirPricingInfoRef Key="$AirPriceInfoKey2" />
            <TicketingModifiers>
            <Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
            </TicketingModifiers>
        </AirPricingTicketingModifiers>
    EOM;
        
    }else if($adult > 0 && $child > 0){

    $FareInfoKey = $_POST['adult'][0]['fareInfoKey'];
    $AirPriceInfoKey = $_POST['adult'][0]['airPriceInfoKey'];


    for($x = 0 ; $x < $adult; $x++){

        ${'afName'.$x} = $_POST['adult'][$x]["afName"];
        ${'alName'.$x} = $_POST['adult'][$x]["alName"];
        ${'agender'.$x} = $_POST['adult'][$x]["agender"];
        ${'adob'.$x} = date("dMy", strtotime($_POST['adult'][$x]["adob"]));
        ${'apassNo'.$x} = $_POST['adult'][$x]["apassNo"];
        ${'apassEx'.$x} = date("dMy", strtotime($_POST['adult'][$x]["apassEx"]));
        ${'apassNation'.$x} = $_POST['adult'][$x]["apassNation"];

        if(${'agender'.$x} = 'M'){
            $atitle = 'Mr';
        }else{
            $atitle = 'Mrs';
        }


        //Flight Info
        

        $AdultPassengerItem=<<<EOM
                        <BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="ADT$x" TravelerType="ADT" Gender="${'agender'.$x}" Nationality="${'apassNation'.$x}">
                            <BookingTravelerName Prefix="$atitle" First="${'afName'.$x} " Last="${'alName'.$x}" />
                            <SSR Type="DOCS" Status="HK" FreeText="P/${'apassNation'.$x}/${'apassNo'.$x}/${'apassNation'.$x}/${'adob'.$x}/${'agender'.$x}/${'apassEx'.$x}/${'alName'.$x}/${'afName'.$x} " Carrier="$Cr" />
                        </BookingTraveler>
                    EOM;
        
                    array_push($AllPassenger, $AdultPassengerItem);


        $AdultPassengerTypeItem =<<<EOM
                    <PassengerType Code="ADT"  BookingTravelerRef="ADT$x" />
        EOM;

        array_push($AdultPassengerType, $AdultPassengerTypeItem);
                
    }




    $FareInfoKey1 = $_POST['child'][0]['fareInfoKey'];
    $AirPriceInfoKey1 = $_POST['child'][0]['airPriceInfoKey'];


    for($x = 0 ; $x < $child; $x++){

        ${'cfName'.$x} = $_POST['child'][$x]["cfName"];
        ${'clName'.$x} = $_POST['child'][$x]["clName"];
        ${'cgender'.$x} = $_POST['child'][$x]["cgender"];
        ${'cdob'.$x} = date("dMy", strtotime($_POST['child'][$x]["cdob"]));
        ${'cpassNo'.$x} = $_POST['child'][$x]["cpassNo"];
        ${'cpassEx'.$x} = date("dMy", strtotime($_POST['child'][$x]["cpassEx"]));
        ${'cpassNation'.$x} = $_POST['child'][$x]["cpassNation"];

        if(${'cgender'.$x} = 'M'){
            $ctitle = 'MASTER';
        }else{
            $ctitle = 'Miss';
        }


        //Flight Info
        

        $ChildPassengerItem=<<<EOM
                        <BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="CNN$x" TravelerType="CNN" Gender="${'cgender'.$x}" Nationality="${'cpassNation'.$x}">
                            <BookingTravelerName Prefix="$ctitle" First="${'cfName'.$x} " Last="${'clName'.$x}" />
                            <SSR Type="DOCS" Status="HK" FreeText="P/${'cpassNation'.$x}/${'cpassNo'.$x}/${'cpassNation'.$x}/${'cdob'.$x}/${'cgender'.$x}/${'cpassEx'.$x}/${'clName'.$x}/${'cfName'.$x}" Carrier="$Cr" />
                        </BookingTraveler>
                    EOM;
        
                    array_push($AllPassenger, $ChildPassengerItem);


        $ChildPassengerTypeItem =<<<EOM
                    <PassengerType Code="CNN"  BookingTravelerRef="CNN$x" />
        EOM;

        array_push($ChildPassengerType, $ChildPassengerTypeItem);
                
    }



        $AdultPassengerTypeAll = implode(" ",$AdultPassengerType);
        $ChildPassengerTypeAll = implode(" ",$ChildPassengerType);

        if($segment == 1){
            $AdultBookingCode=<<<EOM
                    <BookingInfo BookingCode="$BCode"  FareInfoRef="$FareInfoKey"  SegmentRef="$AirSKey" />
                EOM;
            $ChildBookingCode=<<<EOM
                <BookingInfo BookingCode="$BCode"  FareInfoRef="$FareInfoKey1"  SegmentRef="$AirSKey" />
            EOM;

            
        }else if($segment == 2){	

            $AdultBookingCode =<<<EOM
                    <BookingInfo BookingCode="$BCode"  FareInfoRef="$FareInfoKey"  SegmentRef="$AirSKey" />
                    <BookingInfo BookingCode="$BCode1"  FareInfoRef="$FareInfoKey"  SegmentRef="$AirSKey1" />
                EOM;
            
            $ChildBookingCode =<<<EOM
                    <BookingInfo  BookingCode="$BCode"  FareInfoRef="$FareInfoKey1"  SegmentRef="$AirSKey" />
                    <BookingInfo  BookingCode="$BCode1"  FareInfoRef="$FareInfoKey1"  SegmentRef="$AirSKey1" />
                EOM;
                
        }


        $AirPricingSolution = <<<EOM
        <AirPricingSolution Key="$AirPricingSolutionKey" xmlns="http://www.travelport.com/schema/air_v51_0">
                $AirSegments
            <AirPricingInfo Key="$AirPriceInfoKey" PricingMethod="Guaranteed" PlatingCarrier="$Cr" ProviderCode="1G">
                <FareInfo Key="$FareInfoKey" FareBasis="$FareBasis" PassengerTypeCode="ADT" Origin="$From" Destination="$To" EffectiveDate="$eDate"></FareInfo>
                    $AdultBookingCode
                    $AdultPassengerTypeAll
            </AirPricingInfo>
            <AirPricingInfo Key="$AirPriceInfoKey1" PricingMethod="Guaranteed" PlatingCarrier="$Cr" ProviderCode="1G">
                <FareInfo Key="$FareInfoKey" FareBasis="$FareBasis" PassengerTypeCode="CNN" Origin="$From" Destination="$To" EffectiveDate="$eDate"></FareInfo>
                    $ChildBookingCode
                    $ChildPassengerTypeAll
            </AirPricingInfo>
        </AirPricingSolution>	
    EOM;

    $AirPricingTicketingModifiers=<<<EOM
        <AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
            <AirPricingInfoRef Key="$AirPriceInfoKey" />
            <TicketingModifiers>
            <Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
            </TicketingModifiers>
        </AirPricingTicketingModifiers>
        <AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
            <AirPricingInfoRef Key="$AirPriceInfoKey1" />
            <TicketingModifiers>
            <Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
            </TicketingModifiers>
        </AirPricingTicketingModifiers>
    EOM;



    }else if($adult > 0 && $infants > 0){

    $FareInfoKey = $_POST['child'][0]['fareInfoKey'];
    $AirPriceInfoKey = $_POST['child'][0]['airPriceInfoKey'];


    for($x = 0 ; $x < $adult; $x++){

        ${'afName'.$x} = $_POST['adult'][$x]["afName"];
        ${'alName'.$x} = $_POST['adult'][$x]["alName"];
        ${'agender'.$x} = $_POST['adult'][$x]["agender"];
        ${'adob'.$x} = date("dMy", strtotime($_POST['adult'][$x]["adob"]));
        ${'apassNo'.$x} = $_POST['adult'][$x]["apassNo"];
        ${'apassEx'.$x} = date("dMy", strtotime($_POST['adult'][$x]["apassEx"]));
        ${'apassNation'.$x} = $_POST['adult'][$x]["apassNation"];

        if(${'agender'.$x} = 'M'){
            $atitle = 'Mr';
        }else{
            $atitle = 'Mrs';
        }


        //Flight Info
        

        $AdultPassengerItem=<<<EOM
                        <BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="ADT$x" TravelerType="ADT" Gender="${'agender'.$x}" Nationality="${'apassNation'.$x}">
                            <BookingTravelerName Prefix="$atitle" First="${'afName'.$x}  "  Last="${'alName'.$x}" />
                            <SSR Type="DOCS" Status="HK" FreeText="P/${'apassNation'.$x}/${'apassNo'.$x}/${'apassNation'.$x}/${'adob'.$x}/${'agender'.$x}/${'apassEx'.$x}/${'alName'.$x}/${'afName'.$x} " Carrier="$Cr" />
                        </BookingTraveler>
                    EOM;
        
        array_push($AllPassenger, $AdultPassengerItem);


        $AdultPassengerTypeItem =<<<EOM
                    <PassengerType Code="ADT"  BookingTravelerRef="ADT$x" />
        EOM;

        array_push($AdultPassengerType, $AdultPassengerTypeItem);
                
    }





    //INFANTS

    $FareInfoKey1 = $_POST['infant'][0]['fareInfoKey'];
    $AirPriceInfoKey1 = $_POST['infant'][0]['airPriceInfoKey'];


    for($x = 0 ; $x < $infants; $x++){

        ${'ifName'.$x} = $_POST['infant'][$x]["ifName"];
        ${'ilName'.$x} = $_POST['infant'][$x]["ilName"];
        ${'igender'.$x} = $_POST['infant'][$x]["igender"];
        ${'idob'.$x} = date("dMy", strtotime($_POST['infant'][$x]["idob"]));
        ${'ipassNo'.$x} = $_POST['infant'][$x]["ipassNo"];
        ${'ipassEx'.$x} = date("dMy", strtotime($_POST['infant'][$x]["ipassEx"]));
        ${'ipassNation'.$x} = $_POST['infant'][$x]["ipassNation"];

        if(${'igender'.$x} = 'M'){
            $ititle = 'Master';
        }else{
            $ititle = 'Miss';
        }



        //Flight Info
        

        $InfantPassengerItem=<<<EOM
                        <BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="INF$x" TravelerType="INF" Gender="${'igender'.$x}" Nationality="${'ipassNation'.$x}">
                            <BookingTravelerName Prefix="$ititle" First="${'ifName'.$x} " Last="${'ilName'.$x}" />
                            <SSR Type="DOCS" Status="HK" FreeText="P/${'ipassNation'.$x}/${'ipassNo'.$x}/${'ipassNation'.$x}/${'idob'.$x}/${'igender'.$x}/${'ipassEx'.$x}/${'ilName'.$x}/${'ifName'.$x} " Carrier="$Cr" />
                            <NameRemark Category="AIR">
                                <RemarkData>${'idob'.$x}</RemarkData>
                            </NameRemark>
                        </BookingTraveler>
                    EOM;
        
        array_push($AllPassenger, $InfantPassengerItem);


        $InfantPassengerTypeItem =<<<EOM
                    <PassengerType Code="INF"  BookingTravelerRef="INF$x" />
        EOM;

        array_push($InfantPassengerType, $InfantPassengerTypeItem);
                
    }

        

        if($segment == 1){
            $AdultBookingCode=<<<EOM
                    <BookingInfo BookingCode="$BCode" FareInfoRef="$FareInfoKey" SegmentRef="$AirSKey" />
                EOM;


            $InfantBookingCode=<<<EOM
                    <BookingInfo BookingCode="$BCode" FareInfoRef="$FareInfoKey1" SegmentRef="$AirSKey" />
                EOM;

            
        }else if($segment == 2){	

            $AdultBookingCode =<<<EOM
                    <BookingInfo BookingCode="$BCode" FareInfoRef="$FareInfoKey" SegmentRef="$AirSKey" />
                    <BookingInfo BookingCode="$BCode1" FareInfoRef="$FareInfoKey" SegmentRef="$AirSKey1" />
                EOM;
            
                
            $InfantBookingCode =<<<EOM
                    <BookingInfo BookingCode="$BCode" FareInfoRef="$FareInfoKey1" SegmentRef="$AirSKey" />
                    <BookingInfo BookingCode="$BCode1" FareInfoRef="$FareInfoKey1" SegmentRef="$AirSKey1" />
                EOM;
        }


        $AdultPassengerTypeAll = implode(" ",$AdultPassengerType);
        $InfantPassengerTypeAll = implode(" ",$InfantPassengerType);


        $AirPricingSolution = <<<EOM
        <AirPricingSolution Key="$AirPricingSolutionKey" xmlns="http://www.travelport.com/schema/air_v51_0">
                $AirSegments
            <AirPricingInfo Key="$AirPriceInfoKey" PricingMethod="Guaranteed" PlatingCarrier="$Cr" ProviderCode="1G">
                <FareInfo Key="$FareInfoKey" FareBasis="$FareBasis" PassengerTypeCode="ADT" Origin="$From" Destination="$To" EffectiveDate="$eDate"></FareInfo>
                    $AdultBookingCode
                    $AdultPassengerTypeAll
            </AirPricingInfo>
            <AirPricingInfo Key="$AirPriceInfoKey1" PricingMethod="Guaranteed" PlatingCarrier="$Cr" ProviderCode="1G">
                <FareInfo Key="$FareInfoKey" FareBasis="$FareBasis" PassengerTypeCode="INF" Origin="$From" Destination="$To" EffectiveDate="$eDate"></FareInfo>
                    $InfantBookingCode
                    $InfantPassengerTypeAll
            </AirPricingInfo>
        </AirPricingSolution>	
    EOM;

    $AirPricingTicketingModifiers=<<<EOM
        <AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
            <AirPricingInfoRef Key="$AirPriceInfoKey" />
            <TicketingModifiers>
            <Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
            </TicketingModifiers>
        </AirPricingTicketingModifiers>
        <AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
            <AirPricingInfoRef Key="$AirPriceInfoKey1" />
            <TicketingModifiers>
            <Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
            </TicketingModifiers>
        </AirPricingTicketingModifiers>
    EOM;




    }else if($adult > 0){

    $FareInfoKey = $_POST['adult'][0]['fareInfoKey'];
    $AirPriceInfoKey = $_POST['adult'][0]['airPriceInfoKey'];


    for($x = 0 ; $x < $adult; $x++){

        ${'afName'.$x} = $_POST['adult'][$x]["afName"];
        ${'alName'.$x} = $_POST['adult'][$x]["alName"];
        ${'agender'.$x} = $_POST['adult'][$x]["agender"];
        ${'adob'.$x} = date("dMy", strtotime($_POST['adult'][$x]["adob"]));
        ${'apassNo'.$x} = $_POST['adult'][$x]["apassNo"];
        ${'apassEx'.$x} = date("dMy", strtotime($_POST['adult'][$x]["apassEx"]));
        ${'apassNation'.$x} = $_POST['adult'][$x]["apassNation"];

        if(${'agender'.$x} = 'M'){
            $atitle = 'Mr';
        }else{
            $atitle = 'Mrs';
        }


        //Flight Info
        
        $AdultPassengerItem=<<<EOM
                        <BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="ADT$x" TravelerType="ADT" Gender="${'agender'.$x}" Nationality="${'apassNation'.$x}">
                            <BookingTravelerName Prefix="$atitle" First="${'afName'.$x} " Last="${'alName'.$x}" />
                            <SSR Type="DOCS" Status="HK" FreeText="P/${'apassNation'.$x}/${'apassNo'.$x}/${'apassNation'.$x}/${'adob'.$x}/${'agender'.$x}/${'apassEx'.$x}/${'alName'.$x}/${'afName'.$x} " Carrier="$Cr" />
                        </BookingTraveler>
                    EOM;
        
        array_push($AllPassenger, $AdultPassengerItem);


        $AdultPassengerTypeItem =<<<EOM
                    <PassengerType Code="ADT"  BookingTravelerRef="ADT$x" />
        EOM;

        array_push($AdultPassengerType, $AdultPassengerTypeItem);
                
    }



        $AdultPassengerTypeAll = implode(" ",$AdultPassengerType);

        if($segment == 1){
            $BookingCode=<<<EOM
                    <BookingInfo BookingCode="$BCode" FareInfoRef="$FareInfoKey" SegmentRef="$AirSKey" />
                EOM;

            
        }else if($segment == 2){			
                $BookingCode =<<<EOM
                    <BookingInfo BookingCode="$BCode" FareInfoRef="$FareInfoKey" SegmentRef="$AirSKey" />
                    <BookingInfo BookingCode="$BCode1" FareInfoRef="$FareInfoKey" SegmentRef="$AirSKey1" />
                EOM;
        }


        $AirPricingSolution = <<<EOM
        <AirPricingSolution Key="$AirPricingSolutionKey" xmlns="http://www.travelport.com/schema/air_v51_0">
                $AirSegments
            <AirPricingInfo Key="$AirPriceInfoKey" PricingMethod="Guaranteed" PlatingCarrier="$Cr" ProviderCode="1G">
            <FareInfo Key="$FareInfoKey" FareBasis="$FareBasis" PassengerTypeCode="ADT" Origin="$From" Destination="$To" EffectiveDate="$eDate"></FareInfo>
                $BookingCode
                $AdultPassengerTypeAll
            </AirPricingInfo>
        </AirPricingSolution>	
    EOM;

    $AirPricingTicketingModifiers=<<<EOM
        <AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
            <AirPricingInfoRef Key="$AirPriceInfoKey" />
            <TicketingModifiers>
            <Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
            </TicketingModifiers>
        </AirPricingTicketingModifiers>
    EOM;


    }



    $PassengerNumAll = implode(" ",$AllPassenger);

    $message = <<<EOM
                    <soapenv:Envelope
                    xmlns:univ="http://www.travelport.com/schema/universal_v51_0"
                    xmlns:com="http://www.travelport.com/schema/common_v51_0"
                    xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
                    <soapenv:Header/>
                    <soapenv:Body
                        xmlns:univ="http://www.travelport.com/schema/universal_v51_0"
                        xmlns:com="http://www.travelport.com/schema/common_v51_0">
                        <univ:AirCreateReservationReq xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:univ="http://www.travelport.com/schema/universal_v51_0" TraceId="FFI_KayesFahim" TargetBranch="P7182044" RuleName="COMM" RetainReservation="Both" RestrictWaitlist="true" xmlns="http://www.travelport.com/schema/common_v51_0">
                            <BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v51_0" OriginApplication="UAPI" />
                                $PassengerNumAll
                            <AgencyContactInfo xmlns="http://www.travelport.com/schema/common_v51_0">
                                <PhoneNumber Location="DAC" Number="01322903298" Text="Flyway International" />
                            </AgencyContactInfo>
                                $AirPricingSolution
                            <ActionStatus xmlns="http://www.travelport.com/schema/common_v51_0" Type="TAW" TicketDate="$tDate" ProviderCode="1G" />
                                $AirPricingTicketingModifiers
                        </univ:AirCreateReservationReq>
                    </soapenv:Body>
                </soapenv:Envelope>
                EOM;


    }else if($tripType == 2){


	if($segment == '1'){
		$goCr = $_POST['segments']['go'][0]['cr'];
		$goAirSKey = $_POST['segments']['go'][0]['airSegKey'];
		$goBCode = $_POST['segments']['go'][0]['bcode'];
		$goDep = $_POST['segments']['go'][0]['dep'];
		$goArr = $_POST['segments']['go'][0]['arr'];
		$goFNo = $_POST['segments']['go'][0]['Fno'];
		$goG = $_POST['segments']['go'][0]['G'];	
		$goDepTime = $_POST['segments']['go'][0]['DepTime'];
		$goArrTime  = $_POST['segments']['go'][0]['ArrTime'];

		$backCr = $_POST['segments']['back'][0]['cr'];
		$backAirSKey = $_POST['segments']['back'][0]['airSegKey'];
		$backBCode = $_POST['segments']['back'][0]['bcode'];
		$backDep = $_POST['segments']['back'][0]['dep'];
		$backArr = $_POST['segments']['back'][0]['arr'];
		$backFNo = $_POST['segments']['back'][0]['Fno'];
		$backG = $_POST['segments']['back'][0]['G'];	
		$backDepTime = $_POST['segments']['back'][0]['DepTime'];
		$backArrTime  = $_POST['segments']['back'][0]['ArrTime'];

		$From = $_POST['segments']['go'][0]['dep'];
		$To = $_POST['segments']['go'][0]['arr'];

		
		$AirSegments = <<<EOM
			<AirSegment Key="$goAirSKey" Group="$goG" Carrier="$goCr" FlightNumber="$goFNo" ProviderCode="1G" Origin="$goDep" Destination="$goArr" DepartureTime="$goDepTime" ArrivalTime="$goArrTime"></AirSegment>
			<AirSegment Key="$backAirSKey1" Group="$backG" Carrier="$bakCr" FlightNumber="$backbackFNo" ProviderCode="1G" Origin="$backDep" Destination="$backArr" DepartureTime="$backDepTime" ArrivalTime="$backArrTime"></AirSegment>
		EOM;

		
		
	}else if($segment == '2'){

		//Go 1
		$goCr = $_POST['segments']['go'][0]['cr'];
		$goAirSKey = $_POST['segments']['go'][0]['airSegKey'];
		$goBCode = $_POST['segments']['go'][0]['bcode'];
		$goDep = $_POST['segments']['go'][0]['dep'];
		$goArr = $_POST['segments']['go'][0]['arr'];
		$goFNo = $_POST['segments']['go'][0]['Fno'];
		$goG = $_POST['segments']['go'][0]['G'];	
		$goDepTime = $_POST['segments']['go'][0]['DepTime'];
		$goArrTime  = $_POST['segments']['go'][0]['ArrTime'];


		//Go 2
		$goCr1 = $_POST['segments']['go'][1]['cr'];
		$goAirSKey1 = $_POST['segments']['go'][1]['airSegKey'];
		$goBCode1 = $_POST['segments']['go'][1]['bcode'];
		$goDep1 = $_POST['segments']['go'][1]['dep'];
		$goArr1 = $_POST['segments']['go'][1]['arr'];
		$goFNo1 = $_POST['segments']['go'][1]['Fno'];
		$goG1 = $_POST['segments']['go'][1]['G'];	
		$goDepTime1 = $_POST['segments']['go'][1]['DepTime'];
		$goArrTime1  = $_POST['segments']['go'][1]['ArrTime'];



		//Back 1
		$backCr = $_POST['segments']['back'][0]['cr'];
		$backAirSKey = $_POST['segments']['back'][0]['airSegKey'];
		$backBCode = $_POST['segments']['back'][0]['bcode'];
		$backDep = $_POST['segments']['back'][0]['dep'];
		$backArr = $_POST['segments']['back'][0]['arr'];
		$backFNo = $_POST['segments']['back'][0]['Fno'];
		$backG = $_POST['segments']['back'][0]['G'];	
		$backDepTime = $_POST['segments']['back'][0]['DepTime'];
		$backArrTime  = $_POST['segments']['back'][0]['ArrTime'];


		//Back 2
		$backCr1 = $_POST['segments']['back'][1]['cr'];
		$backAirSKey1 = $_POST['segments']['back'][1]['airSegKey'];
		$backBCode1 = $_POST['segments']['back'][1]['bcode'];
		$backDep1 = $_POST['segments']['back'][1]['dep'];
		$backArr1 = $_POST['segments']['back'][1]['arr'];
		$backFNo1 = $_POST['segments']['back'][1]['Fno'];
		$backG1 = $_POST['segments']['back'][1]['G'];	
		$backDepTime1 = $_POST['segments']['back'][1]['DepTime'];
		$backArrTime1  = $_POST['segments']['back'][1]['ArrTime'];

		$From = $_POST['segments']['go'][0]['dep'];
		$To = $_POST['segments']['go'][1]['arr'];
		
			
		$AirSegments = <<<EOM
			<AirSegment Key="$goAirSKey" Group="$goG" Carrier="$goCr" FlightNumber="$goFNo" ProviderCode="1G" Origin="$goDep" Destination="$goArr" DepartureTime="$goDepTime" ArrivalTime="$goArrTime"></AirSegment>
			<AirSegment Key="$goAirSKey1" Group="$goG1" Carrier="$goCr1" FlightNumber="$goFNo1" ProviderCode="1G" Origin="$goDep1" Destination="$goArr1" DepartureTime="$goDepTime1" ArrivalTime="$goArrTime1"></AirSegment>
			<AirSegment Key="$backAirSKey" Group="$backG" Carrier="$backCr" FlightNumber="$backFNo" ProviderCode="1G" Origin="$backDep" Destination="$backArr" DepartureTime="$backDepTime" ArrivalTime="$backArrTime"></AirSegment>
			<AirSegment Key="$backAirSKey1" Group="$backG1" Carrier="$backCr1" FlightNumber="$backFNo1" ProviderCode="1G" Origin="$backDep1" Destination="$backArr1" DepartureTime="$backDepTime1" ArrivalTime="$backArrTime1"></AirSegment>
	EOM;

	}else if($segments == '12'){
		//Go 1
		$goCr = $_POST['segments']['go'][0]['cr'];
		$goAirSKey = $_POST['segments']['go'][0]['airSegKey'];
		$goBCode = $_POST['segments']['go'][0]['bcode'];
		$goDep = $_POST['segments']['go'][0]['dep'];
		$goArr = $_POST['segments']['go'][0]['arr'];
		$goFNo = $_POST['segments']['go'][0]['Fno'];
		$goG = $_POST['segments']['go'][0]['G'];	
		$goDepTime = $_POST['segments']['go'][0]['DepTime'];
		$goArrTime  = $_POST['segments']['go'][0]['ArrTime'];


		//Back 1
		$backCr = $_POST['segments']['back'][0]['cr'];
		$backAirSKey = $_POST['segments']['back'][0]['airSegKey'];
		$backBCode = $_POST['segments']['back'][0]['bcode'];
		$backDep = $_POST['segments']['back'][0]['dep'];
		$backArr = $_POST['segments']['back'][0]['arr'];
		$backFNo = $_POST['segments']['back'][0]['Fno'];
		$backG = $_POST['segments']['back'][0]['G'];	
		$backDepTime = $_POST['segments']['back'][0]['DepTime'];
		$backArrTime  = $_POST['segments']['back'][0]['ArrTime'];


		//Back 2
		$backCr1 = $_POST['segments']['back'][1]['cr'];
		$backAirSKey1 = $_POST['segments']['back'][1]['airSegKey'];
		$backBCode1 = $_POST['segments']['back'][1]['bcode'];
		$backDep1 = $_POST['segments']['back'][1]['dep'];
		$backArr1 = $_POST['segments']['back'][1]['arr'];
		$backFNo1 = $_POST['segments']['back'][1]['Fno'];
		$backG1 = $_POST['segments']['back'][1]['G'];	
		$backDepTime1 = $_POST['segments']['back'][1]['DepTime'];
		$backArrTime1  = $_POST['segments']['back'][1]['ArrTime'];

		$From = $_POST['segments']['go'][0]['dep'];
		$To = $_POST['segments']['go'][0]['arr'];
		
			
		$AirSegments = <<<EOM
			<AirSegment Key="$goAirSKey" Group="$goG" Carrier="$goCr" FlightNumber="$goFNo" ProviderCode="1G" Origin="$goDep" Destination="$goArr" DepartureTime="$goDepTime" ArrivalTime="$goArrTime"></AirSegment>
			<AirSegment Key="$backAirSKey" Group="$backG" Carrier="$backCr" FlightNumber="$backFNo" ProviderCode="1G" Origin="$backDep" Destination="$backArr" DepartureTime="$backDepTime" ArrivalTime="$backArrTime"></AirSegment>
			<AirSegment Key="$backAirSKey1" Group="$backG1" Carrier="$backCr1" FlightNumber="$backFNo1" ProviderCode="1G" Origin="$backDep1" Destination="$backArr1" DepartureTime="$backDepTime1" ArrivalTime="$backArrTime1"></AirSegment>
	EOM;
	}else if($segments == '21'){
		//Go 1
		$goCr = $_POST['segments']['go'][0]['cr'];
		$goAirSKey = $_POST['segments']['go'][0]['airSegKey'];
		$goBCode = $_POST['segments']['go'][0]['bcode'];
		$goDep = $_POST['segments']['go'][0]['dep'];
		$goArr = $_POST['segments']['go'][0]['arr'];
		$goFNo = $_POST['segments']['go'][0]['Fno'];
		$goG = $_POST['segments']['go'][0]['G'];	
		$goDepTime = $_POST['segments']['go'][0]['DepTime'];
		$goArrTime  = $_POST['segments']['go'][0]['ArrTime'];


		//Go 2
		$goCr1 = $_POST['segments']['go'][1]['cr'];
		$goAirSKey1 = $_POST['segments']['go'][1]['airSegKey'];
		$goBCode1 = $_POST['segments']['go'][1]['bcode'];
		$goDep1 = $_POST['segments']['go'][1]['dep'];
		$goArr1 = $_POST['segments']['go'][1]['arr'];
		$goFNo1 = $_POST['segments']['go'][1]['Fno'];
		$goG1 = $_POST['segments']['go'][1]['G'];	
		$goDepTime1 = $_POST['segments']['go'][1]['DepTime'];
		$goArrTime1  = $_POST['segments']['go'][1]['ArrTime'];



		//Back 1
		$backCr = $_POST['segments']['back'][0]['cr'];
		$backAirSKey = $_POST['segments']['back'][0]['airSegKey'];
		$backBCode = $_POST['segments']['back'][0]['bcode'];
		$backDep = $_POST['segments']['back'][0]['dep'];
		$backArr = $_POST['segments']['back'][0]['arr'];
		$backFNo = $_POST['segments']['back'][0]['Fno'];
		$backG = $_POST['segments']['back'][0]['G'];	
		$backDepTime = $_POST['segments']['back'][0]['DepTime'];
		$backArrTime  = $_POST['segments']['back'][0]['ArrTime'];


		$From = $_POST['segments']['go'][0]['dep'];
		$To = $_POST['segments']['go'][1]['arr'];
		
			
		$AirSegments = <<<EOM
			<AirSegment Key="$goAirSKey" Group="$goG" Carrier="$goCr" FlightNumber="$goFNo" ProviderCode="1G" Origin="$goDep" Destination="$goArr" DepartureTime="$goDepTime" ArrivalTime="$goArrTime"></AirSegment>
			<AirSegment Key="$goAirSKey1" Group="$goG1" Carrier="$goCr1" FlightNumber="$goFNo1" ProviderCode="1G" Origin="$goDep1" Destination="$goArr1" DepartureTime="$goDepTime1" ArrivalTime="$goArrTime1"></AirSegment>
			<AirSegment Key="$backAirSKey" Group="$backG" Carrier="$backCr" FlightNumber="$backFNo" ProviderCode="1G" Origin="$backDep" Destination="$backArr" DepartureTime="$backDepTime" ArrivalTime="$backArrTime"></AirSegment>
			
	EOM;
	}


	if($adult > 0 && $child> 0 && $infants> 0){
		
		$AirPriceInfoKey = $_POST['adult'][0]['airPriceInfoKey'];
		$AdultgoFareInfoKey = $_POST['adult'][0]['afInfoKeygo'];
		$AdultbackFareInfoKey = $_POST['adult'][0]['afInfoKeyback'];

		
		for($x = 0 ; $x < $adult; $x++){

			${'afName'.$x} = $_POST['adult'][$x]["afName"];
			${'alName'.$x} = $_POST['adult'][$x]["alName"];
			${'agender'.$x} = $_POST['adult'][$x]["agender"];
			${'adob'.$x} = date("dMy", strtotime($_POST['adult'][$x]["adob"]));
			${'apassNo'.$x} = $_POST['adult'][$x]["apassNo"];
			${'apassEx'.$x} = date("dMy", strtotime($_POST['adult'][$x]["apassEx"]));
			${'apassNation'.$x} = $_POST['adult'][$x]["apassNation"];
			if(${'agender'.$x} = 'M'){
				$atitle = 'Mr';
			}else{
				$atitle = 'Mrs';
			}


			//Flight Info
			

			$AdultPassengerItem=<<<EOM
							<BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="ADT$x" TravelerType="ADT" Gender="${'agender'.$x}" Nationality="${'apassNation'.$x}">
								<BookingTravelerName Prefix="$atitle" First="${'afName'.$x} " Last="${'alName'.$x}" />
								<SSR Type="DOCS" Status="HK" FreeText="P/${'apassNation'.$x}/${'apassNo'.$x}/${'apassNation'.$x}/${'adob'.$x}/${'agender'.$x}/${'apassEx'.$x}/${'alName'.$x}/${'afName'.$x} " Carrier="$goCr" />
							</BookingTraveler>
						EOM;
			
			array_push($AllPassenger, $AdultPassengerItem);


			$AdultPassengerTypeItem =<<<EOM
						<PassengerType Code="ADT"  BookingTravelerRef="ADT$x" />
			EOM;

			array_push($AdultPassengerType, $AdultPassengerTypeItem);
					
		}

		

		$AirPriceInfoKey1 = $_POST['child'][0]['airPriceInfoKey'];
		$ChildgoFareInfoKey = $_POST['child'][0]['cfInfoKeygo'];
		$ChildbackFareInfoKey = $_POST['child'][0]['cfInfoKeyback'];

		
		for($x = 0 ; $x < $child; $x++){

			${'cfName'.$x} = $_POST['child'][$x]["cfName"];
			${'clName'.$x} = $_POST['child'][$x]["clName"];
			${'cgender'.$x} = $_POST['child'][$x]["cgender"];
			${'cdob'.$x} = $_POST['child'][$x]["cdob"];
			${'cpassNo'.$x} = $_POST['child'][$x]["cpassNo"];
			${'cpassEx'.$x} = $_POST['child'][$x]["cpassEx"];
			${'cpassNation'.$x} = $_POST['child'][$x]["cpassNation"];

			if(${'cgender'.$x} = 'M'){
				$ctitle = 'Mr';
			}else{
				$ctitle = 'Miss';
			}


			//Flight Info
			

			$ChildPassengerItem=<<<EOM
							<BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="CNN$x" TravelerType="CNN" Gender="${'cgender'.$x}" Nationality="${'cpassNation'.$x}">
								<BookingTravelerName Prefix="$ctitle" First="${'cfName'.$x} " Last="${'clName'.$x}" />
								<SSR Type="DOCS" Status="HK" FreeText="P/${'cpassNation'.$x}/${'cpassNo'.$x}/${'cpassNation'.$x}/${'cdob'.$x}/${'cgender'.$x}/${'cpassEx'.$x}/${'clName'.$x}/${'cfName'.$x} " Carrier="$goCr" />
							</BookingTraveler>
						EOM;
			
						array_push($AllPassenger, $ChildPassengerItem);


			$ChildPassengerTypeItem =<<<EOM
						<PassengerType Code="CNN"  BookingTravelerRef="CNN$x" />
			EOM;

			array_push($ChildPassengerType, $ChildPassengerTypeItem);
				
		}


		$AirPriceInfoKey2 = $_POST['infant'][0]['airPriceInfoKey'];
		$InfantgoFareInfoKey = $_POST['infant'][0]['ifInfoKeygo'];
		$InfantbackFareInfoKey = $_POST['infant'][0]['ifInfoKeyback'];

		
		for($x = 0 ; $x < $infants; $x++){

			${'ifName'.$x} = $_POST['infant'][$x]["ifName"];
			${'ilName'.$x} = $_POST['infant'][$x]["ilName"];
			${'igender'.$x} = $_POST['infant'][$x]["igender"];
			${'idob'.$x} = date("dMy", strtotime($_POST['infant'][$x]["idob"]));
			${'ipassNo'.$x} = $_POST['infant'][$x]["ipassNo"];
			${'ipassEx'.$x} = date("dMy", strtotime($_POST['infant'][$x]["ipassEx"]));
			${'ipassNation'.$x} = $_POST['infant'][$x]["ipassNation"];

			if(${'igender'.$x} = 'M'){
				$ititle = 'Master';
			}else{
				$ititle = 'Miss';
			}


			//Flight Info
			

			$InfantPassengerItem=<<<EOM
							<BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="INF$x" TravelerType="INF" Gender="${'igender'.$x}" Nationality="${'ipassNation'.$x}">
								<BookingTravelerName Prefix="$ctitle" First="${'ifName'.$x} " Last="${'ilName'.$x}" />
								<SSR Type="DOCS" Status="HK" FreeText="P/${'ipassNation'.$x}/${'ipassNo'.$x}/${'ipassNation'.$x}/${'idob'.$x}/${'igender'.$x}/${'ipassEx'.$x}/${'ilName'.$x}/${'ifName'.$x} " Carrier="$goCr" />
								<NameRemark Category="AIR">
									<RemarkData>${'idob'.$x}</RemarkData>
								</NameRemark>
							</BookingTraveler>
						EOM;
			
						array_push($AllPassenger, $InfantPassengerItem);


			$InfantPassengerTypeItem =<<<EOM
						<PassengerType Code="INF"  BookingTravelerRef="INF$x" />
			EOM;

			array_push($InfantPassengerType, $InfantPassengerTypeItem);
					
		}

		

			$AdultPassengerTypeAll = implode(" ",$AdultPassengerType); 
			$ChildPassengerTypeAll = implode(" ",$ChildPassengerType); 
			$InfantPassengerTypeAll = implode(" ",$InfantPassengerType);


			if($segment == 1){
				$AdultBookingCode=<<<EOM
						<BookingInfo BookingCode="$goBCode" FareInfoRef="$AdultgoFareInfoKey" SegmentRef="$goAirSKey" />
						<BookingInfo BookingCode="$backBCode" FareInfoRef="$AdultbackFareInfoKey" SegmentRef="$backAirSKey" />
					EOM;
				$ChildBookingCode=<<<EOM
						<BookingInfo BookingCode="$goBCode" FareInfoRef="$ChildgoFareInfoKey" SegmentRef="$goAirSKey" />
						<BookingInfo BookingCode="$backBCode" FareInfoRef="$ChildbackFareInfoKey" SegmentRef="$backAirSKey" />
				EOM;
				$InfantBookingCode=<<<EOM
						<BookingInfo BookingCode="$goBCode" FareInfoRef="$InfantgoFareInfoKey" SegmentRef="$goAirSKey" />
						<BookingInfo BookingCode="$backBCode" FareInfoRef="$InfantbackFareInfoKey" SegmentRef="$backAirSKey" />
					EOM;

				
			}else if($segment == 2){	

				$AdultBookingCode =<<<EOM
						<BookingInfo BookingCode="$goBCode" FareInfoRef="$AdultgoFareInfoKey" SegmentRef="$goAirSKey" />
						<BookingInfo BookingCode="$goBCode1" FareInfoRef="$AdultgoFareInfoKey" SegmentRef="$goAirSKey1" />
						<BookingInfo BookingCode="$backBCode" FareInfoRef="$AdultbackFareInfoKey" SegmentRef="$backAirSKey" />
						<BookingInfo BookingCode="$backBCode1" FareInfoRef="$AdultbackFareInfoKey" SegmentRef="$backAirSKey1" />
					EOM;
				
				$ChildBookingCode =<<<EOM
						<BookingInfo BookingCode="$goBCode" FareInfoRef="$ChildgoFareInfoKey" SegmentRef="$goAirSKey" />
						<BookingInfo BookingCode="$goBCode1" FareInfoRef="$ChildgoFareInfoKey" SegmentRef="$goAirSKey1" />
						<BookingInfo BookingCode="$backBCode" FareInfoRef="$ChildbackFareInfoKey" SegmentRef="$backAirSKey" />
						<BookingInfo BookingCode="$backBCode1" FareInfoRef="$ChildbackFareInfoKey" SegmentRef="$backAirSKey1" />
					EOM;
					
				$InfantBookingCode =<<<EOM
						<BookingInfo BookingCode="$goBCode" FareInfoRef="$InfantgoFareInfoKey" SegmentRef="$goAirSKey" />
						<BookingInfo BookingCode="$goBCode1" FareInfoRef="$InfantgoFareInfoKey" SegmentRef="$goAirSKey1" />
						<BookingInfo BookingCode="$backBCode" FareInfoRef="$InfantbackFareInfoKey" SegmentRef="$backAirSKey" />
						<BookingInfo BookingCode="$backBCode1" FareInfoRef="$InfantbackFareInfoKey" SegmentRef="$backAirSKey1" />
					EOM;
			}
		

			$AirPricingSolution = <<<EOM
			<AirPricingSolution Key="$AirPricingSolutionKey" xmlns="http://www.travelport.com/schema/air_v51_0">
					$AirSegments
					<AirPricingInfo Key="$AirPriceInfoKey" PricingMethod="Guaranteed" PlatingCarrier="$goCr" ProviderCode="1G">
					<FareInfo Key="$AdultgoFareInfoKey" FareBasis="$goFareBasis" PassengerTypeCode="ADT" Origin="$From" Destination="$To" EffectiveDate="$eDate"></FareInfo>
					<FareInfo Key="$AdultbackFareInfoKey" FareBasis="$backFareBasis" PassengerTypeCode="ADT" Origin="$To" Destination="$From" EffectiveDate="$eDate"></FareInfo>
						$AdultBookingCode
						$AdultPassengerTypeAll
				</AirPricingInfo>
				<AirPricingInfo Key="$AirPriceInfoKey1" PricingMethod="Guaranteed" PlatingCarrier="$goCr" ProviderCode="1G">
					<FareInfo Key="$ChildgoFareInfoKey" FareBasis="$goFareBasis" PassengerTypeCode="CNN" Origin="$From" Destination="$To" EffectiveDate="$eDate"></FareInfo>
					<FareInfo Key="$ChildbackFareInfoKey" FareBasis="$backFareBasis" PassengerTypeCode="CNN" Origin="$To" Destination="$From" EffectiveDate="$eDate"></FareInfo>
						$ChildBookingCode
						$ChildPassengerTypeAll
				</AirPricingInfo>
				<AirPricingInfo Key="$AirPriceInfoKey2" PricingMethod="Guaranteed" PlatingCarrier="$goCr" ProviderCode="1G">
					<FareInfo Key="$InfantgoFareInfoKey" FareBasis="$goFareBasis" PassengerTypeCode="INF" Origin="$From" Destination="$To" EffectiveDate="$eDate"></FareInfo>
					<FareInfo Key="$InfantbackFareInfoKey" FareBasis="$backFareBasis" PassengerTypeCode="INF" Origin="$To" Destination="$From" EffectiveDate="$eDate"></FareInfo>
						$InfantBookingCode
						$InfantPassengerTypeAll
				</AirPricingInfo>
			</AirPricingSolution>	
	EOM;

		$AirPricingTicketingModifiers=<<<EOM
			<AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
				<AirPricingInfoRef Key="$AirPriceInfoKey" />
				<TicketingModifiers>
				<Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
				</TicketingModifiers>
			</AirPricingTicketingModifiers>
			<AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
				<AirPricingInfoRef Key="$AirPriceInfoKey1" />
				<TicketingModifiers>
				<Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
				</TicketingModifiers>
			</AirPricingTicketingModifiers>
			<AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
				<AirPricingInfoRef Key="$AirPriceInfoKey2" />
				<TicketingModifiers>
				<Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
				</TicketingModifiers>
			</AirPricingTicketingModifiers>
		EOM;
			
	}else if($adult > 0 && $child > 0){
		
		$AirPriceInfoKey = $_POST['adult'][0]['airPriceInfoKey'];
		$AdultgoFareInfoKey = $_POST['adult'][0]['afInfoKeygo'];
		$AdultbackFareInfoKey = $_POST['adult'][0]['afInfoKeyback'];

		
		for($x = 0 ; $x < $adult; $x++){

			${'afName'.$x} = $_POST['adult'][$x]["afName"];
			${'alName'.$x} = $_POST['adult'][$x]["alName"];
			${'agender'.$x} = $_POST['adult'][$x]["agender"];
			${'adob'.$x} = date("dMy", strtotime($_POST['adult'][$x]["adob"]));
			${'apassNo'.$x} = $_POST['adult'][$x]["apassNo"];
			${'apassEx'.$x} = date("dMy", strtotime($_POST['adult'][$x]["apassEx"]));
			${'apassNation'.$x} = $_POST['adult'][$x]["apassNation"];

			if(${'agender'.$x} = 'M'){
				$atitle = 'Mr';
			}else{
				$atitle = 'Mrs';
			}


			//Flight Info
			

			$AdultPassengerItem=<<<EOM
							<BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="ADT$x" TravelerType="ADT" Gender="${'agender'.$x}" Nationality="${'apassNation'.$x}">
								<BookingTravelerName Prefix="$atitle" First="${'afName'.$x} " Last="${'alName'.$x}" />
								<SSR Type="DOCS" Status="HK" FreeText="P/${'apassNation'.$x}/${'apassNo'.$x}/${'apassNation'.$x}/${'adob'.$x}/${'agender'.$x}/${'apassEx'.$x}/${'alName'.$x}/${'afName'.$x} " Carrier="$goCr" />
							</BookingTraveler>
						EOM;
			
						array_push($AllPassenger, $AdultPassengerItem);


			$AdultPassengerTypeItem =<<<EOM
						<PassengerType Code="ADT"  BookingTravelerRef="ADT$x" />
			EOM;

			array_push($AdultPassengerType, $AdultPassengerTypeItem);
					
		}

		
		$AirPriceInfoKey1 = $_POST['child'][0]['airPriceInfoKey'];
		$ChildgoFareInfoKey = $_POST['child'][0]['cfInfoKeygo'];
		$ChildbackFareInfoKey = $_POST['child'][0]['cfInfoKeyback'];

		
		for($x = 0 ; $x < $child; $x++){

			${'cfName'.$x} = $_POST['child'][$x]["cfName"];
			${'clName'.$x} = $_POST['child'][$x]["clName"];
			${'cgender'.$x} = $_POST['child'][$x]["cgender"];
			${'cdob'.$x} = date("dMy", strtotime($_POST['child'][$x]["cdob"]));
			${'cpassNo'.$x} = $_POST['child'][$x]["cpassNo"];
			${'cpassEx'.$x} = date("dMy", strtotime($_POST['child'][$x]["cpassEx"]));
			${'cpassNation'.$x} = $_POST['child'][$x]["cpassNation"];

			if(${'cgender'.$x} = 'M'){
				$ctitle = 'MASTER';
			}else{
				$ctitle = 'Miss';
			}


			//Flight Info
			

			$ChildPassengerItem=<<<EOM
							<BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="CNN$x" TravelerType="CNN" Gender="${'cgender'.$x}" Nationality="${'cpassNation'.$x}">
								<BookingTravelerName Prefix="$ctitle" First="${'cfName'.$x} " Last="${'clName'.$x}" />
								<SSR Type="DOCS" Status="HK" FreeText="P/${'cpassNation'.$x}/${'cpassNo'.$x}/${'cpassNation'.$x}/${'cdob'.$x}/${'cgender'.$x}/${'cpassEx'.$x}/${'clName'.$x}/${'cfName'.$x}" Carrier="$goCr" />
							</BookingTraveler>
						EOM;
			
						array_push($AllPassenger, $ChildPassengerItem);


			$ChildPassengerTypeItem =<<<EOM
						<PassengerType Code="CNN"  BookingTravelerRef="CNN$x" />
			EOM;

			array_push($ChildPassengerType, $ChildPassengerTypeItem);
					
		}



			$AdultPassengerTypeAll = implode(" ",$AdultPassengerType);
			$ChildPassengerTypeAll = implode(" ",$ChildPassengerType);

			if($segment == 1){
				$AdultBookingCode=<<<EOM
						<BookingInfo BookingCode="$goBCode"  FareInfoRef="$AdultgoFareInfoKey"  SegmentRef="$goAirSKey" />
						<BookingInfo BookingCode="$backBCode"  FareInfoRef="$AdultbackFareInfoKey"  SegmentRef="$backAirSKey" />
					EOM;
				$ChildBookingCode=<<<EOM
					<BookingInfo BookingCode="$goBCode"  FareInfoRef="$ChildgoFareInfoKey"  SegmentRef="$goAirSKey" />
					<BookingInfo BookingCode="$backBCode"  FareInfoRef="$ChildbackFareInfoKey"  SegmentRef="$backAirSKey" />
				EOM;

				
			}else if($segment == 2){	

				$AdultBookingCode =<<<EOM
						<BookingInfo BookingCode="$goBCode"  FareInfoRef="$AdultgoFareInfoKey"  SegmentRef="$goAirSKey" />
						<BookingInfo BookingCode="$goBCode"  FareInfoRef="$AdultgoFareInfoKey"  SegmentRef="$goAirSKey" />
						<BookingInfo BookingCode="$backBCode"  FareInfoRef="$AdultbackFareInfoKey"  SegmentRef="$backAirSKey" />
						<BookingInfo BookingCode="$backBCode"  FareInfoRef="$AdultbackFareInfoKey"  SegmentRef="$backAirSKey" />
					EOM;
				
				$ChildBookingCode =<<<EOM
						<BookingInfo BookingCode="$goBCode"  FareInfoRef="$ChildgoFareInfoKey"  SegmentRef="$goAirSKey" />				
						<BookingInfo BookingCode="$goBCode"  FareInfoRef="$ChildgoFareInfoKey"  SegmentRef="$goAirSKey" />
						<BookingInfo BookingCode="$backBCode"  FareInfoRef="$ChildbackFareInfoKey"  SegmentRef="$backAirSKey" />
						<BookingInfo BookingCode="$backBCode"  FareInfoRef="$ChildbackFareInfoKey"  SegmentRef="$backAirSKey" />
					EOM;
					
			}
		

			$AirPricingSolution = <<<EOM
			<AirPricingSolution Key="$AirPricingSolutionKey" xmlns="http://www.travelport.com/schema/air_v51_0">
					$AirSegments
				<AirPricingInfo Key="$AirPriceInfoKey" PricingMethod="Guaranteed" PlatingCarrier="$goCr" ProviderCode="1G">
					<FareInfo Key="$AdultgoFareInfoKey" FareBasis="$goFareBasis" PassengerTypeCode="ADT" Origin="$From" Destination="$To" EffectiveDate="$eDate"></FareInfo>
					<FareInfo Key="$AdultbackFareInfoKey" FareBasis="$backFareBasis" PassengerTypeCode="ADT" Origin="$To" Destination="$From" EffectiveDate="$eDate"></FareInfo>
						$AdultBookingCode
						$AdultPassengerTypeAll
				</AirPricingInfo>
				<AirPricingInfo Key="$AirPriceInfoKey1" PricingMethod="Guaranteed" PlatingCarrier="$goCr" ProviderCode="1G">
					<FareInfo Key="$ChildgoFareInfoKey" FareBasis="$goFareBasis" PassengerTypeCode="CNN" Origin="$From" Destination="$To" EffectiveDate="$eDate"></FareInfo>
					<FareInfo Key="$ChildbackFareInfoKey" FareBasis="$backFareBasis" PassengerTypeCode="CNN" Origin="$To" Destination="$From" EffectiveDate="$eDate"></FareInfo>
						$ChildBookingCode
						$ChildPassengerTypeAll
				</AirPricingInfo>
			</AirPricingSolution>	
	EOM;

		$AirPricingTicketingModifiers=<<<EOM
			<AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
				<AirPricingInfoRef Key="$AirPriceInfoKey" />
				<TicketingModifiers>
				<Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
				</TicketingModifiers>
			</AirPricingTicketingModifiers>
			<AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
				<AirPricingInfoRef Key="$AirPriceInfoKey1" />
				<TicketingModifiers>
				<Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
				</TicketingModifiers>
			</AirPricingTicketingModifiers>
		EOM;

		
		
	}else if($adult > 0 && $infants > 0){

		$AirPriceInfoKey = $_POST['adult'][0]['airPriceInfoKey'];
		$AdultgoFareInfoKey = $_POST['adult'][0]['afInfoKeygo'];
		$AdultbackFareInfoKey = $_POST['adult'][0]['afInfoKeyback'];

		
		for($x = 0 ; $x < $adult; $x++){

			${'afName'.$x} = $_POST['adult'][$x]["afName"];
			${'alName'.$x} = $_POST['adult'][$x]["alName"];
			${'agender'.$x} = $_POST['adult'][$x]["agender"];
			${'adob'.$x} = date("dMy", strtotime($_POST['adult'][$x]["adob"]));
			${'apassNo'.$x} = $_POST['adult'][$x]["apassNo"];
			${'apassEx'.$x} = date("dMy", strtotime($_POST['adult'][$x]["apassEx"]));
			${'apassNation'.$x} = $_POST['adult'][$x]["apassNation"];

			if(${'agender'.$x} = 'M'){
				$atitle = 'Mr';
			}else{
				$atitle = 'Mrs';
			}


			//Flight Info
			

			$AdultPassengerItem=<<<EOM
							<BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="ADT$x" TravelerType="ADT" Gender="${'agender'.$x}" Nationality="${'apassNation'.$x}">
								<BookingTravelerName Prefix="$atitle" First="${'afName'.$x}  "  Last="${'alName'.$x}" />
								<SSR Type="DOCS" Status="HK" FreeText="P/${'apassNation'.$x}/${'apassNo'.$x}/${'apassNation'.$x}/${'adob'.$x}/${'agender'.$x}/${'apassEx'.$x}/${'alName'.$x}/${'afName'.$x} " Carrier="$goCr" />
							</BookingTraveler>
						EOM;
			
			array_push($AllPassenger, $AdultPassengerItem);


			$AdultPassengerTypeItem =<<<EOM
						<PassengerType Code="ADT"  BookingTravelerRef="ADT$x" />
			EOM;

			array_push($AdultPassengerType, $AdultPassengerTypeItem);
					
		}


		

		
		//INFANTS

		$AirPriceInfoKey1 = $_POST['child'][0]['airPriceInfoKey'];
		$ChildgoFareInfoKey = $_POST['child'][0]['gocfInfoKeygo'];
		$ChildbackFareInfoKey = $_POST['child'][0]['backcfInfoKeyback'];

		
		for($x = 0 ; $x < $infants; $x++){

			${'ifName'.$x} = $_POST['infant'][$x]["ifName"];
			${'ilName'.$x} = $_POST['infant'][$x]["ilName"];
			${'igender'.$x} = $_POST['infant'][$x]["igender"];
			${'idob'.$x} = date("dMy", strtotime($_POST['infant'][$x]["idob"]));
			${'ipassNo'.$x} = $_POST['infant'][$x]["ipassNo"];
			${'ipassEx'.$x} = date("dMy", strtotime($_POST['infant'][$x]["ipassEx"]));
			${'ipassNation'.$x} = $_POST['infant'][$x]["ipassNation"];

			if(${'igender'.$x} = 'M'){
				$ititle = 'Master';
			}else{
				$ititle = 'Miss';
			}



			//Flight Info
			

			$InfantPassengerItem=<<<EOM
							<BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="INF$x" TravelerType="INF" Gender="${'igender'.$x}" Nationality="${'ipassNation'.$x}">
								<BookingTravelerName Prefix="$ititle" First="${'ifName'.$x} " Last="${'ilName'.$x}" />
								<SSR Type="DOCS" Status="HK" FreeText="P/${'ipassNation'.$x}/${'ipassNo'.$x}/${'ipassNation'.$x}/${'idob'.$x}/${'igender'.$x}/${'ipassEx'.$x}/${'ilName'.$x}/${'ifName'.$x} " Carrier="$goCr" />
								<NameRemark Category="AIR">
									<RemarkData>${'idob'.$x}</RemarkData>
								</NameRemark>
							</BookingTraveler>
						EOM;
			
			array_push($AllPassenger, $InfantPassengerItem);


			$InfantPassengerTypeItem =<<<EOM
						<PassengerType Code="INF"  BookingTravelerRef="INF$x" />
			EOM;

			array_push($InfantPassengerType, $InfantPassengerTypeItem);
					
		}

		

			if($segment == 1){
				$AdultBookingCode=<<<EOM
						<BookingInfo BookingCode="$goBCode" FareInfoRef="$AdultgoFareInfoKey" SegmentRef="$goAirSKey" />
						<BookingInfo BookingCode="$backBCode" FareInfoRef="$AdultbackFareInfoKey" SegmentRef="$backAirSKey" />
					EOM;


				$InfantBookingCode=<<<EOM
						<BookingInfo BookingCode="$goBCode" FareInfoRef="$InfantgoFareInfoKey" SegmentRef="$goAirSKey" />
						<BookingInfo BookingCode="$backBCode" FareInfoRef="$InfantbackFareInfoKey" SegmentRef="$backAirSKey" />
					EOM;

				
			}else if($segment == 2){	

				$AdultBookingCode =<<<EOM
						<BookingInfo BookingCode="$goBCode" FareInfoRef="$AdultgoFareInfoKey" SegmentRef="$goAirSKey" />
						<BookingInfo BookingCode="$goBCode1" FareInfoRef="$AdultgoFareInfoKey" SegmentRef="$goAirSKey1" />
						<BookingInfo BookingCode="$backBCode" FareInfoRef="$AdultbackFareInfoKey" SegmentRef="$backAirSKey" />
						<BookingInfo BookingCode="$backBCode1" FareInfoRef="$AdultbackFareInfoKey" SegmentRef="$backAirSKey1" />
					EOM;
				
					
				$InfantBookingCode =<<<EOM
						<BookingInfo BookingCode="$goBCode" FareInfoRef="$InfantgoFareInfoKey" SegmentRef="$goAirSKey" />
						<BookingInfo BookingCode="$goBCode1" FareInfoRef="$InfantgoFareInfoKey" SegmentRef="$goAirSKey1" />
						<BookingInfo BookingCode="$backBCode" FareInfoRef="$InfantbackFareInfoKey" SegmentRef="$backAirSKey" />
						<BookingInfo BookingCode="$backBCode1" FareInfoRef="$InfantbackFareInfoKey" SegmentRef="$backAirSKey1" />
					EOM;
			}


			$AdultPassengerTypeAll = implode(" ",$AdultPassengerType);
			$InfantPassengerTypeAll = implode(" ",$InfantPassengerType);
		

			$AirPricingSolution = <<<EOM
			<AirPricingSolution Key="$AirPricingSolutionKey" xmlns="http://www.travelport.com/schema/air_v51_0">
					$AirSegments
				<AirPricingInfo Key="$AirPriceInfoKey" PricingMethod="Guaranteed" PlatingCarrier="$goCr" ProviderCode="1G">
					<FareInfo Key="$AdultgoFareInfoKey" FareBasis="$goFareBasis" PassengerTypeCode="ADT" Origin="$From" Destination="$To" EffectiveDate="$eDate"></FareInfo>
					<FareInfo Key="$AdultbackFareInfoKey" FareBasis="$backFareBasis" PassengerTypeCode="ADT" Origin="$To" Destination="$From" EffectiveDate="$eDate"></FareInfo>
						$AdultBookingCode
						$AdultPassengerTypeAll
				</AirPricingInfo>
				<AirPricingInfo Key="$AirPriceInfoKey1" PricingMethod="Guaranteed" PlatingCarrier="$goCr" ProviderCode="1G">
					<FareInfo Key="$InfantgoFareInfoKey" FareBasis="$goFareBasis" PassengerTypeCode="INF" Origin="$From" Destination="$To" EffectiveDate="$eDate"></FareInfo>
					<FareInfo Key="$InfantbackFareInfoKey" FareBasis="$backFareBasis" PassengerTypeCode="INF" Origin="$To" Destination="$From" EffectiveDate="$eDate"></FareInfo>
						$InfantBookingCode
						$InfantPassengerTypeAll
				</AirPricingInfo>
			</AirPricingSolution>	
	EOM;

		$AirPricingTicketingModifiers=<<<EOM
			<AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
				<AirPricingInfoRef Key="$AirPriceInfoKey" />
				<TicketingModifiers>
				<Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
				</TicketingModifiers>
			</AirPricingTicketingModifiers>
			<AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
				<AirPricingInfoRef Key="$AirPriceInfoKey1" />
				<TicketingModifiers>
				<Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
				</TicketingModifiers>
			</AirPricingTicketingModifiers>
		EOM;
		
		
		
		
	}else if($adult > 0){

		$AirPriceInfoKey = $_POST['adult'][0]['airPriceInfoKey'];
		$AdultgoFareInfoKey = $_POST['adult'][0]['afInfoKeygo'];
		$AdultbackFareInfoKey = $_POST['adult'][0]['afInfoKeyback'];

		
		for($x = 0 ; $x < $adult; $x++){

			${'afName'.$x} = $_POST['adult'][$x]["afName"];
			${'alName'.$x} = $_POST['adult'][$x]["alName"];
			${'agender'.$x} = $_POST['adult'][$x]["agender"];
			${'adob'.$x} = date("dMy", strtotime($_POST['adult'][$x]["adob"]));
			${'apassNo'.$x} = $_POST['adult'][$x]["apassNo"];
			${'apassEx'.$x} = date("dMy", strtotime($_POST['adult'][$x]["apassEx"]));
			${'apassNation'.$x} = $_POST['adult'][$x]["apassNation"];

			if(${'agender'.$x} = 'M'){
				$atitle = 'Mr';
			}else{
				$atitle = 'Mrs';
			}


			//Flight Info
			
			$AdultPassengerItem=<<<EOM
							<BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="ADT$x" TravelerType="ADT" Gender="${'agender'.$x}" Nationality="${'apassNation'.$x}">
								<BookingTravelerName Prefix="$atitle" First="${'afName'.$x} " Last="${'alName'.$x}" />
								<SSR Type="DOCS" Status="HK" FreeText="P/${'apassNation'.$x}/${'apassNo'.$x}/${'apassNation'.$x}/${'adob'.$x}/${'agender'.$x}/${'apassEx'.$x}/${'alName'.$x}/${'afName'.$x} " Carrier="$goCr" />
							</BookingTraveler>
						EOM;
			
			array_push($AllPassenger, $AdultPassengerItem);


			$AdultPassengerTypeItem =<<<EOM
						<PassengerType Code="ADT"  BookingTravelerRef="ADT$x" />
			EOM;

			array_push($AdultPassengerType, $AdultPassengerTypeItem);
					
		}

		

			$AdultPassengerTypeAll = implode(" ",$AdultPassengerType);

			if($segment == 1){
				$BookingCode=<<<EOM
						<BookingInfo BookingCode="$goBCode" FareInfoRef="$AdultgoFareInfoKey" SegmentRef="$goAirSKey" />
						<BookingInfo BookingCode="$backBCode" FareInfoRef="$AdultbackFareInfoKey" SegmentRef="$backAirSKey" />
					EOM;

				
			}else if($segment == 2){			
					$BookingCode =<<<EOM
						<BookingInfo BookingCode="$goBCode" FareInfoRef="$AdultgoFareInfoKey" SegmentRef="$goAirSKey" />
						<BookingInfo BookingCode="$goBCode1" FareInfoRef="$AdultgoFareInfoKey" SegmentRef="$goAirSKey1" />
						<BookingInfo BookingCode="$backBCode" FareInfoRef="$AdultbackFareInfoKey" SegmentRef="$backAirSKey" />
						<BookingInfo BookingCode="$backBCode1" FareInfoRef="$AdultbackFareInfoKey" SegmentRef="$backAirSKey1" />
					EOM;
			}
		

			$AirPricingSolution = <<<EOM
			<AirPricingSolution Key="$AirPricingSolutionKey" xmlns="http://www.travelport.com/schema/air_v51_0">
					$AirSegments
				<AirPricingInfo Key="$AirPriceInfoKey" PricingMethod="Guaranteed" PlatingCarrier="$goCr" ProviderCode="1G">
				<FareInfo Key="$AdultgoFareInfoKey" FareBasis="$goFareBasis" PassengerTypeCode="ADT" Origin="$From" Destination="$To" EffectiveDate="$eDate"></FareInfo>
				<FareInfo Key="$AdultbackFareInfoKey" FareBasis="$backFareBasis" PassengerTypeCode="ADT" Origin="$To" Destination="$From" EffectiveDate="$eDate"></FareInfo>
					$BookingCode
					$AdultPassengerTypeAll
				</AirPricingInfo>
			</AirPricingSolution>	
	EOM;

		$AirPricingTicketingModifiers=<<<EOM
			<AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
				<AirPricingInfoRef Key="$AirPriceInfoKey" />
				<TicketingModifiers>
				<Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
				</TicketingModifiers>
			</AirPricingTicketingModifiers>
		EOM;

		
	}



		$PassengerNumAll = implode(" ",$AllPassenger);
		
		$message = <<<EOM
						<soapenv:Envelope
						xmlns:univ="http://www.travelport.com/schema/universal_v51_0"
						xmlns:com="http://www.travelport.com/schema/common_v51_0"
						xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
						<soapenv:Header/>
						<soapenv:Body
							xmlns:univ="http://www.travelport.com/schema/universal_v51_0"
							xmlns:com="http://www.travelport.com/schema/common_v51_0">
							<univ:AirCreateReservationReq xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:univ="http://www.travelport.com/schema/universal_v51_0" TraceId="FFI-KayesFahim" TargetBranch="P4218912" RuleName="COMM" RetainReservation="Both" RestrictWaitlist="true" xmlns="http://www.travelport.com/schema/common_v51_0">
								<BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v51_0" OriginApplication="UAPI" />
									$PassengerNumAll
								<AgencyContactInfo xmlns="http://www.travelport.com/schema/common_v51_0">
									<PhoneNumber Location="DAC" Number="01322903298" Text="Flyway International" />
								</AgencyContactInfo>
									$AirPricingSolution
								<ActionStatus xmlns="http://www.travelport.com/schema/common_v51_0" Type="TAW" TicketDate="$tDate" ProviderCode="1G" />
									$AirPricingTicketingModifiers
							</univ:AirCreateReservationReq>
						</soapenv:Body>
					</soapenv:Envelope>
					EOM;
			
		}

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
		if(isset($xml->xpath('//universalAirCreateReservationRsp')[0])){
			$body = $xml->xpath('//universalAirCreateReservationRsp')[0];
			
		$result = json_decode(json_encode((array)$body), TRUE); 

		$json_string = json_encode($result, JSON_PRETTY_PRINT);
		
		echo $json_string;
		
		}
    

?>