<?php

include '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require "../vendor/autoload.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_POST = json_decode(file_get_contents('php://input'), true);

    $PassengerData = $_POST['flightPassengerData'];
    $bookingInfo = $_POST['bookingInfo'];
    $saveBookingAarray = isset($_POST['saveBooking']) ? $_POST['saveBooking'] :'';
    $gdsSystem = isset($_POST['system']) ? $_POST['system'] : '';
    $agentId = isset($_POST['agentId'])? $_POST['agentId']:"";
    $subagentId = isset($_POST['subagentId']) ? $_POST['subagentId'] : "";
    $userId = isset($_POST['userId']) ? $_POST['userId'] : "";
    $Platform = isset($_POST['platform']) ? $_POST['platform'] : "";

    $uId = sha1(md5(time()));

    $adult = $PassengerData['adultCount']; //echo $adult;
    $child = $PassengerData['childCount']; //echo $child;
    $infants = $PassengerData['infantCount']; //echo $infants;

    $Email = $PassengerData['email'];
    $Phone = $PassengerData['phone'];

    $createdTimer = date('Y-m-d H:i:s');
    $now = new DateTime();

    $AllPerson = array();
    $AdvancePassnger = array();
    $AllSsr = array();
    $AllSecureFlight = array();
    $FlyHubPassenger = array();
    // print_r($_POST);
    if ($adult > 0 && $child > 0 && $infants > 0) {
        $paxRequest = '{
                        "Code": "ADT",
                        "Quantity": "' . $adult . '"
                    },
                    {
                        "Code": "C09",
                        "Quantity": "' . $child . '"
                    },
                    {
                        "Code": "INF",
                        "Quantity": "' . $infants . '"
                    }';

        //Adult Part
        $adultCount = 0;
        $totalCount = 0;
        for ($x = 0; $x < $adult; $x++) {
            $adultCount++;
            $totalCount++;

            ${'afName' . $x} = strtoupper($PassengerData['adult'][$x]["afName"]);
            ${'alName' . $x} = strtoupper($PassengerData['adult'][$x]["alName"]);
            ${'agender' . $x} = $PassengerData['adult'][$x]["agender"];
            ${'adob' . $x} = $PassengerData['adult'][$x]["adob"];
            ${'apassNo' . $x} = strtoupper($PassengerData['adult'][$x]["apassNo"]);
            ${'apassEx' . $x} = $PassengerData['adult'][$x]["apassEx"];
            ${'apassNation' . $x} = strtoupper($PassengerData['adult'][$x]["apassNation"]);

            if (${'agender' . $x} == 'Male') {
                ${'agender' . $x} = $gdsSystem === 'Sabre' ? 'M' : 'Male';
                ${'agender' . $x};
                ${'atitle' . $x} = 'MR';
            } else {
                ${'agender' . $x} = 'F';
                ${'atitle' . $x} = 'MS';
            }

            $Person = array(
                "NameNumber" => "$totalCount.1",
                "GivenName" => "${'afName' . $x} ${'atitle' . $x}",
                "Surname" => "${'alName' . $x}",
                "Infant" => false,
                "PassengerType" => "ADT",
                "NameReference" => "",
            );

            array_push($AllPerson, $Person);

            $AdvPax = array(
                "Document" => array(
                    "Number" => "${'apassNo' . $x}",
                    "IssueCountry" => "${'apassNation' . $x}",
                    "NationalityCountry" => "${'apassNation' . $x}",
                    "ExpirationDate" => "${'apassEx' . $x}",
                    "Type" => "P",
                ),
                "PersonName" => array(
                    "NameNumber" => "$totalCount.1",
                    "GivenName" => "${'afName' . $x}",
                    "MiddleName" => "",
                    "Surname" => "${'alName' . $x}",
                    "DateOfBirth" => "${'adob' . $x}",
                    "Gender" => "${'agender' . $x}",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AdvancePassnger, $AdvPax);

            $SSROThers = array(
                "SSR_Code" => "OTHS",
                "Text" => "CC ${'afName' . $x} ${'alName' . $x}",
                "PersonName" => array(
                    "NameNumber" => "$totalCount.1",
                ),
                "SegmentNumber" => "A",
            );
            array_push($AllSsr, $SSROThers);

            $SSRCTCM = array(
                "SSR_Code" => "CTCM",
                "Text" => "$Phone",
                "PersonName" => array(
                    "NameNumber" => "$totalCount.1",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AllSsr, $SSRCTCM);

            $SSRCTCE = array(
                "SSR_Code" => "CTCE",
                "Text" => "${'afName' . $x}//${'afName' . $x}.com",
                "PersonName" => array(
                    "NameNumber" => "$totalCount.1",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AllSsr, $SSRCTCE);

        }

        //Child Part

        $childCount = 0;
        for ($x = 0; $x < $child; $x++) {

            $adultCount++;
            $childCount++;
            $totalCount++;

            ${'cfName' . $x} = strtoupper($PassengerData['child'][$x]["cfName"]);
            ${'clName' . $x} = strtoupper($PassengerData['child'][$x]["clName"]);
            ${'cgender' . $x} = $PassengerData['child'][$x]["cgender"];
            ${'cdob' . $x} = $PassengerData['child'][$x]["cdob"];
            ${'cpassNo' . $x} = $PassengerData['child'][$x]["cpassNo"];
            ${'cpassEx' . $x} = $PassengerData['child'][$x]["cpassEx"];
            ${'cpassNation' . $x} = $PassengerData['child'][$x]["cpassNation"];

            //AgeCalculate
            ${'cdate' . $x} = date_create(${'cdob' . $x});
            ${'childSSR' . $x} = date_format(${'cdate' . $x}, "dMy");
            ${'dobCount' . $x} = new DateTime(${'cdob' . $x});
            ${'AgeCount' . $x} = $now->diff(${'dobCount' . $x});
            ${'age' . $x} = ${'AgeCount' . $x}->y;
            ${'cAge' . $x} = str_pad(${'age' . $x}, 2, '0', STR_PAD_LEFT); //print(${'cAge'.$x});

            if (${'cgender' . $x} == 'Male') {
                ${'cgender' . $x} = 'M';
                ${'ctitle' . $x} = 'MSTR';
            } else {
                ${'cgender' . $x} = 'F';
                ${'ctitle' . $x} = 'MISS';
            }

            $Person = array(
                "NameNumber" => "$totalCount.1",
                "GivenName" => "${'cfName' . $x} ${'ctitle' . $x}",
                "Surname" => "${'clName' . $x}",
                "Infant" => false,
                "PassengerType" => "C${'cAge' . $x}",
                "NameReference" => "C${'cAge' . $x}",
            );

            array_push($AllPerson, $Person);

            $AdvPax = array(
                "Document" => array(
                    "Number" => "${'cpassNo' . $x}",
                    "IssueCountry" => "${'cpassNation' . $x}",
                    "NationalityCountry" => "${'cpassNation' . $x}",
                    "ExpirationDate" => "${'cpassEx' . $x}",
                    "Type" => "P",
                ),
                "PersonName" => array(
                    "NameNumber" => "$totalCount.1",
                    "GivenName" => "${'cfName' . $x}",
                    "MiddleName" => "",
                    "Surname" => "${'clName' . $x}",
                    "DateOfBirth" => "${'cdob' . $x}",
                    "Gender" => "${'cgender' . $x}",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AdvancePassnger, $AdvPax);

            $SSROThers = array(
                "SSR_Code" => "CHLD",
                "Text" => "${'childSSR' . $x}",
                "PersonName" => array(
                    "NameNumber" => "$totalCount.1",
                ),
                "SegmentNumber" => "A",
            );
            array_push($AllSsr, $SSROThers);

        }

        //Infants Code

        $infantCount = 0;
        for ($x = 0; $x < $infants; $x++) {

            $adultCount++;
            $infantCount++;
            $totalCount++;

            ${'ifName' . $x} = strtoupper($PassengerData['infant'][$x]["ifName"]);
            ${'ilName' . $x} = strtoupper($PassengerData['infant'][$x]["ilName"]);
            ${'igender' . $x} = $PassengerData['infant'][$x]["igender"];
            ${'idob' . $x} = $PassengerData['infant'][$x]["idob"];
            ${'ipassNo' . $x} = $PassengerData['infant'][$x]["ipassNo"];
            ${'ipassEx' . $x} = $PassengerData['infant'][$x]["ipassEx"];
            ${'ipassNation' . $x} = $PassengerData['infant'][$x]["ipassNation"];

            //AgeCalculate
            ${'idate' . $x} = date_create(${'idob' . $x});
            ${'infantSSR' . $x} = date_format(${'idate' . $x}, "dMy");
            ${'dobCount' . $x} = new DateTime(${'idob' . $x});
            ${'AgeCount' . $x} = $now->diff(${'dobCount' . $x});
            ${'age' . $x} = (${'AgeCount' . $x}->y * 12) +${'AgeCount' . $x}->m;
            ${'iAge' . $x} = str_pad(${'age' . $x}, 2, '0', STR_PAD_LEFT);
            
            //
            if(${'iAge' . $x}=="00")
            {
                ${'iAge' . $x}=01;
            }
            
            if (${'igender' . $x} == 'Male') {
                ${'igender' . $x} = 'M';
                ${'ititle' . $x} = 'MSTR';
            } else {
                ${'igender' . $x} = 'F';
                ${'ititle' . $x} = 'MISS';
            }

            $Person = array(
                "NameNumber" => "$totalCount.1",
                "GivenName" => "${'ifName' . $x} ${'ititle' . $x}",
                "Surname" => "${'ilName' . $x}",
                "Infant" => true,
                "PassengerType" => "INF",
                "NameReference" => "I${'iAge' . $x}",
            );

            array_push($AllPerson, $Person);

            $AdvPax = array(
                "Document" => array(
                    "Number" => "${'ipassNo' . $x}",
                    "IssueCountry" => "${'ipassNation' . $x}",
                    "NationalityCountry" => "${'ipassNation' . $x}",
                    "ExpirationDate" => "${'ipassEx' . $x}",
                    "Type" => "P",
                ),
                "PersonName" => array(
                    "NameNumber" => "$infantCount.1",
                    "GivenName" => "${'ifName' . $x}",
                    "MiddleName" => "",
                    "Surname" => "${'ilName' . $x}",
                    "DateOfBirth" => "${'idob' . $x}",
                    "Gender" => "${'igender' . $x}I",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AdvancePassnger, $AdvPax);

            $SSROThers = array(
                "SSR_Code" => "INFT",
                "Text" => "${'ifName' . $x}/${'ilName' . $x} ${'ititle' . $x}/${'infantSSR' . $x}",
                "PersonName" => array(
                    "NameNumber" => "$infantCount.1",
                ),
                "SegmentNumber" => "A",
            );
            array_push($AllSsr, $SSROThers);

        }

    } else if ($adult > 0 && $child > 0) {

        $paxRequest = '{
                    "Code": "ADT",
                    "Quantity": "' . $adult . '"
                },
                {
                    "Code": "C09",
                    "Quantity": "' . $child . '"
                }';

        //Adult Part
        $adultCount = 0;
        for ($x = 0; $x < $adult; $x++) {

            $adultCount++;

            ${'afName' . $x} = strtoupper($PassengerData['adult'][$x]["afName"]);
            ${'alName' . $x} = strtoupper($PassengerData['adult'][$x]["alName"]);
            ${'agender' . $x} = $PassengerData['adult'][$x]["agender"];
            ${'adob' . $x} = $PassengerData['adult'][$x]["adob"];
            ${'apassNo' . $x} = $PassengerData['adult'][$x]["apassNo"];
            ${'apassEx' . $x} = $PassengerData['adult'][$x]["apassEx"];
            ${'apassNation' . $x} = $PassengerData['adult'][$x]["apassNation"];

            if (${'agender' . $x} == 'Male') {
                ${'agender' . $x} = 'M';
                ${'atitle' . $x} = 'MR';
            } else {
                ${'agender' . $x} = 'F';
                ${'atitle' . $x} = 'MS';
            }

            $Person = array(
                "NameNumber" => "$adultCount.1",
                "GivenName" => "${'afName' . $x} ${'atitle' . $x}",
                "Surname" => "${'alName' . $x}",
                "Infant" => false,
                "PassengerType" => "ADT",
                "NameReference" => "",
            );

            array_push($AllPerson, $Person);

            $AdvPax = array(
                "Document" => array(
                    "Number" => "${'apassNo' . $x}",
                    "IssueCountry" => "${'apassNation' . $x}",
                    "NationalityCountry" => "${'apassNation' . $x}",
                    "ExpirationDate" => "${'apassEx' . $x}",
                    "Type" => "P",
                ),
                "PersonName" => array(
                    "NameNumber" => "$adultCount.1",
                    "GivenName" => "${'afName' . $x}",
                    "MiddleName" => "",
                    "Surname" => "${'alName' . $x}",
                    "DateOfBirth" => "${'adob' . $x}",
                    "Gender" => "${'agender' . $x}",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AdvancePassnger, $AdvPax);

            $SSROThers = array(
                "SSR_Code" => "OTHS",
                "Text" => "CC ${'afName' . $x} ${'afName' . $x}",
                "PersonName" => array(
                    "NameNumber" => "$adultCount.1",
                ),
                "SegmentNumber" => "A",
            );
            array_push($AllSsr, $SSROThers);

            $SSRCTCM = array(
                "SSR_Code" => "CTCM",
                "Text" => "$Phone",
                "PersonName" => array(
                    "NameNumber" => "$adultCount.1",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AllSsr, $SSRCTCM);

            $SSRCTCE = array(
                "SSR_Code" => "CTCE",
                "Text" => "${'afName' . $x}//${'afName' . $x}.com",
                "PersonName" => array(
                    "NameNumber" => "$adultCount.1",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AllSsr, $SSRCTCE);

        }

        //Child Part

        $childCount = 0;
        for ($x = 0; $x < $child; $x++) {

            $adultCount++;
            $childCount++;

            ${'cfName' . $x} = strtoupper($PassengerData['child'][$x]["cfName"]);
            ${'clName' . $x} = strtoupper($PassengerData['child'][$x]["clName"]);
            ${'cgender' . $x} = $PassengerData['child'][$x]["cgender"];
            ${'cdob' . $x} = $PassengerData['child'][$x]["cdob"];
            ${'cpassNo' . $x} = $PassengerData['child'][$x]["cpassNo"];
            ${'cpassEx' . $x} = $PassengerData['child'][$x]["cpassEx"];
            ${'cpassNation' . $x} = $PassengerData['child'][$x]["cpassNation"];

            //AgeCalculate
            ${'cdate' . $x} = date_create(${'cdob' . $x});
            ${'childSSR' . $x} = date_format(${'cdate' . $x}, "dMy");
            ${'dobCount' . $x} = new DateTime(${'cdob' . $x});
            ${'AgeCount' . $x} = $now->diff(${'dobCount' . $x});
            ${'age' . $x} = ${'AgeCount' . $x}->y;
            ${'cAge' . $x} = str_pad(${'age' . $x}, 2, '0', STR_PAD_LEFT); //print(${'cAge'.$x});

            if (${'cgender' . $x} == 'Male') {
                ${'cgender' . $x} = 'M';
                ${'ctitle' . $x} = 'MSTR';
            } else {
                ${'cgender' . $x} = 'F';
                ${'ctitle' . $x} = 'MISS';
            }

            $Person = array(
                "NameNumber" => "$adultCount.1",
                "GivenName" => "${'cfName' . $x} ${'ctitle' . $x}",
                "Surname" => "${'clName' . $x}",
                "Infant" => false,
                "PassengerType" => "C${'cAge' . $x}",
                "NameReference" => "C${'cAge' . $x}",
            );

            array_push($AllPerson, $Person);

            $AdvPax = array(
                "Document" => array(
                    "Number" => "${'cpassNo' . $x}",
                    "IssueCountry" => "${'cpassNation' . $x}",
                    "NationalityCountry" => "${'cpassNation' . $x}",
                    "ExpirationDate" => "${'cpassEx' . $x}",
                    "Type" => "P",
                ),
                "PersonName" => array(
                    "NameNumber" => "$adultCount.1",
                    "GivenName" => "${'cfName' . $x}",
                    "MiddleName" => "",
                    "Surname" => "${'clName' . $x}",
                    "DateOfBirth" => "${'cdob' . $x}",
                    "Gender" => "${'cgender' . $x}",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AdvancePassnger, $AdvPax);

            $SSROThers = array(
                "SSR_Code" => "CHLD",
                "Text" => "${'childSSR' . $x}",
                "PersonName" => array(
                    "NameNumber" => "$adultCount.1",
                ),
                "SegmentNumber" => "A",
            );
            array_push($AllSsr, $SSROThers);

        }

    } else if ($adult > 0 && $infants > 0) {

        $paxRequest = '{
                "Code": "ADT",
                "Quantity": "' . $adult . '"
                },
                {
                    "Code": "INF",
                    "Quantity": "' . $infants . '"
                }';

        //Adult Part
        $adultCount = 0;
        for ($x = 0; $x < $adult; $x++) {

            $adultCount++;

            ${'afName' . $x} = strtoupper($PassengerData['adult'][$x]["afName"]);
            ${'alName' . $x} = strtoupper($PassengerData['adult'][$x]["alName"]);
            ${'agender' . $x} = $PassengerData['adult'][$x]["agender"];
            ${'adob' . $x} = $PassengerData['adult'][$x]["adob"];
            ${'apassNo' . $x} = $PassengerData['adult'][$x]["apassNo"];
            ${'apassEx' . $x} = $PassengerData['adult'][$x]["apassEx"];
            ${'apassNation' . $x} = $PassengerData['adult'][$x]["apassNation"];

            if (${'agender' . $x} == 'Male') {
                ${'agender' . $x} = 'M';
                ${'atitle' . $x} = 'MR';
            } else {
                ${'agender' . $x} = 'F';
                ${'atitle' . $x} = 'MS';
            }

            $Person = array(
                "NameNumber" => "$adultCount.1",
                "GivenName" => "${'afName' . $x} ${'atitle' . $x}",
                "Surname" => "${'alName' . $x}",
                "Infant" => false,
                "PassengerType" => "ADT",
                "NameReference" => "",
            );

            array_push($AllPerson, $Person);

            $AdvPax = array(
                "Document" => array(
                    "Number" => "${'apassNo' . $x}",
                    "IssueCountry" => "${'apassNation' . $x}",
                    "NationalityCountry" => "${'apassNation' . $x}",
                    "ExpirationDate" => "${'apassEx' . $x}",
                    "Type" => "P",
                ),
                "PersonName" => array(
                    "NameNumber" => "$adultCount.1",
                    "GivenName" => "${'afName' . $x}",
                    "MiddleName" => "",
                    "Surname" => "${'alName' . $x}",
                    "DateOfBirth" => "${'adob' . $x}",
                    "Gender" => "${'agender' . $x}",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AdvancePassnger, $AdvPax);

            $SSROThers = array(
                "SSR_Code" => "OTHS",
                "Text" => "CC ${'afName' . $x} ${'afName' . $x}",
                "PersonName" => array(
                    "NameNumber" => "$adultCount.1",
                ),
                "SegmentNumber" => "A",
            );
            array_push($AllSsr, $SSROThers);

            $SSRCTCM = array(
                "SSR_Code" => "CTCM",
                "Text" => "$Phone",
                "PersonName" => array(
                    "NameNumber" => "$adultCount.1",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AllSsr, $SSRCTCM);

            $SSRCTCE = array(
                "SSR_Code" => "CTCE",
                "Text" => "${'afName' . $x}//${'afName' . $x}.com",
                "PersonName" => array(
                    "NameNumber" => "$adultCount.1",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AllSsr, $SSRCTCE);

        }

        //Infant Part

        $infantCount = 0;
        for ($x = 0; $x < $infants; $x++) {

            $adultCount++;
            $infantCount++;

            ${'ifName' . $x} = strtoupper($PassengerData['infant'][$x]["ifName"]);
            ${'ilName' . $x} = strtoupper($PassengerData['infant'][$x]["ilName"]);
            ${'igender' . $x} = $PassengerData['infant'][$x]["igender"];
            ${'idob' . $x} = $PassengerData['infant'][$x]["idob"];
            ${'ipassNo' . $x} = $PassengerData['infant'][$x]["ipassNo"];
            ${'ipassEx' . $x} = $PassengerData['infant'][$x]["ipassEx"];
            ${'ipassNation' . $x} = $PassengerData['infant'][$x]["ipassNation"];

            //AgeCalculate
            ${'idate' . $x} = date_create(${'idob' . $x});
            ${'infantSSR' . $x} = date_format(${'idate' . $x}, "dMy");
            ${'dobCount' . $x} = new DateTime(${'idob' . $x});
            ${'AgeCount' . $x} = $now->diff(${'dobCount' . $x});
            ${'age' . $x} = (${'AgeCount' . $x}->y * 12) +${'AgeCount' . $x}->m;
            ${'iAge' . $x} = str_pad(${'age' . $x}, 2, '0', STR_PAD_LEFT);

            //
            if(${'iAge' . $x}=="00")
            {
                ${'iAge' . $x}=01;
            }

            if (${'igender' . $x} == 'Male') {
                ${'igender' . $x} = 'M';
                ${'ititle' . $x} = 'MSTR';
            } else {
                ${'igender' . $x} = 'F';
                ${'ititle' . $x} = 'MISS';
            }

            $Person = array(
                "NameNumber" => "$adultCount.1",
                "GivenName" => "${'ifName' . $x} ${'ititle' . $x}",
                "Surname" => "${'ilName' . $x}",
                "Infant" => true,
                "PassengerType" => "INF",
                "NameReference" => "I${'iAge' . $x}",
            );

            array_push($AllPerson, $Person);

            $AdvPax = array(
                "Document" => array(
                    "Number" => "${'ipassNo' . $x}",
                    "IssueCountry" => "${'ipassNation' . $x}",
                    "NationalityCountry" => "${'ipassNation' . $x}",
                    "ExpirationDate" => "${'ipassEx' . $x}",
                    "Type" => "P",
                ),
                "PersonName" => array(
                    "NameNumber" => "$adultCount.1",
                    "GivenName" => "${'ifName' . $x}",
                    "MiddleName" => "",
                    "Surname" => "${'ilName' . $x}",
                    "DateOfBirth" => "${'idob' . $x}",
                    "Gender" => "${'igender' . $x}I",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AdvancePassnger, $AdvPax);

            $SSROThers = array(
                "SSR_Code" => "INFT",
                "Text" => "${'ifName' . $x}/${'ilName' . $x} ${'ititle' . $x}/${'infantSSR' . $x}",
                "PersonName" => array(
                    "NameNumber" => "$adultCount.1",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AllSsr, $SSROThers);

        }

    } else if ($adult > 0) {

        $paxRequest = '{
                "Code": "ADT",
                "Quantity": "' . $adult . '"
            }';

        $adultCount = 0;
        for ($x = 0; $x < $adult; $x++) {
            $adultCount++;

            ${'afName' . $x} = strtoupper($PassengerData['adult'][$x]["afName"]);
            ${'alName' . $x} = strtoupper($PassengerData['adult'][$x]["alName"]);
            ${'agender' . $x} = $PassengerData['adult'][$x]["agender"];
            ${'adob' . $x} = $PassengerData['adult'][$x]["adob"];
            ${'apassNo' . $x} = strtoupper($PassengerData['adult'][$x]["apassNo"]);
            ${'apassEx' . $x} = $PassengerData['adult'][$x]["apassEx"];
            ${'apassNation' . $x} = strtoupper($PassengerData['adult'][$x]["apassNation"]);

            if (${'agender' . $x} == 'Male') {
                ${'agender' . $x} = 'M';
                ${'atitle' . $x} = 'MR';
            } else {
                ${'agender' . $x} = 'F';
                ${'atitle' . $x} = 'MS';
            }

            $Person = array(
                "NameNumber" => "$adultCount.1",
                "GivenName" => "${'afName' . $x} ${'atitle' . $x}",
                "Surname" => "${'alName' . $x}",
                "Infant" => false,
                "PassengerType" => "ADT",
                "NameReference" => "",
            );

            array_push($AllPerson, $Person);

            $AdvPax = array(
                "Document" => array(
                    "Number" => "${'apassNo' . $x}",
                    "IssueCountry" => "${'apassNation' . $x}",
                    "NationalityCountry" => "${'apassNation' . $x}",
                    "ExpirationDate" => "${'apassEx' . $x}",
                    "Type" => "P",
                ),
                "PersonName" => array(
                    "NameNumber" => "$adultCount.1",
                    "GivenName" => "${'afName' . $x}",
                    "MiddleName" => "",
                    "Surname" => "${'alName' . $x}",
                    "DateOfBirth" => "${'adob' . $x}",
                    "Gender" => "${'agender' . $x}",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AdvancePassnger, $AdvPax);

            $SSROThers = array(
                "SSR_Code" => "OTHS",
                "Text" => "CC ${'afName' . $x} ${'afName' . $x}",
                "PersonName" => array(
                    "NameNumber" => "$adultCount.1",
                ),
                "SegmentNumber" => "A",
            );
            array_push($AllSsr, $SSROThers);

            $SSRCTCM = array(
                "SSR_Code" => "CTCM",
                "Text" => "$Phone",
                "PersonName" => array(
                    "NameNumber" => "$adultCount.1",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AllSsr, $SSRCTCM);

            $SSRCTCE = array(
                "SSR_Code" => "CTCE",
                "Text" => "${'afName' . $x}//${'afName' . $x}.com",
                "PersonName" => array(
                    "NameNumber" => "$adultCount.1",
                ),
                "SegmentNumber" => "A",
            );

            array_push($AllSsr, $SSRCTCE);

        }

    }

    if ($gdsSystem == 'Sabre') {

        $Name = $PassengerData['adult'][0]['afName'] . ' ' . $PassengerData['adult'][0]['alName'];
        $SeatReq = $adult + $child;
        $tripType = $PassengerData['tripType'];
        $Segment = $PassengerData['segment'];

        if ($tripType == "1" || $tripType == "oneway") {
            $flightData = $saveBookingAarray['flightData'];
            if ($Segment == 1) {
                $departure = $flightData['segments'][0]['departure'];
                $arrival = $flightData['segments'][0]['arrival'];
                $dpTime = $flightData['segments'][0]['departureTime'];
                $arrTime = $flightData['segments'][0]['arrivalTime'];
                $bCode = $flightData['segments'][0]['bookingcode'];
                $mCarrier = $flightData['segments'][0]['marketingcareer'];
                $mCarrierFN = $flightData['segments'][0]['marketingflight'];
                $oCarrier = $flightData['segments'][0]['operatingcareer'];
                $oCarrierFN = $flightData['segments'][0]['operatingflight'];

                $FlightSegment = '[{
                        "DepartureDateTime":"' . $dpTime . '",
                        "ArrivalDateTime":"' . $arrTime . '",
                        "FlightNumber":"' . $mCarrierFN . '",
                        "NumberInParty":"' . $SeatReq . '",
                        "ResBookDesigCode":"' . $bCode . '",
                        "Status":"NN",
                        "OriginLocation":{
                            "LocationCode":"' . $departure . '"
                        },
                        "DestinationLocation":{
                            "LocationCode":"' . $arrival . '"
                        },
                        "MarketingAirline":{
                            "Code":"' . $mCarrier . '",
                            "FlightNumber":"' . $mCarrierFN . '"
                        }
                        }]';

            } else if ($Segment == 2) {

                $departure = $flightData['segments'][0]['departure'];
                $arrival = $flightData['segments'][0]['arrival'];
                $dpTime = $flightData['segments'][0]['departureTime'];
                $arrTime = $flightData['segments'][0]['arrivalTime'];
                $bCode = $flightData['segments'][0]['bookingcode'];
                $mCarrier = $flightData['segments'][0]['marketingcareer'];
                $mCarrierFN = $flightData['segments'][0]['marketingflight'];
                $oCarrier = $flightData['segments'][0]['operatingcareer'];
                $oCarrierFN = $flightData['segments'][0]['operatingflight'];

                $departure1 = $flightData['segments'][1]['departure'];
                $arrival1 = $flightData['segments'][1]['arrival'];
                $dpTime1 = $flightData['segments'][1]['departureTime'];
                $arrTime1 = $flightData['segments'][1]['arrivalTime'];
                $bCode1 = $flightData['segments'][1]['bookingcode'];
                $mCarrier1 = $flightData['segments'][1]['marketingcareer'];
                $mCarrierFN1 = $flightData['segments'][1]['marketingflight'];
                $oCarrier1 = $flightData['segments'][1]['operatingcareer'];
                $oCarrierFN1 = $flightData['segments'][1]['operatingflight'];

                $FlightSegment = '[{
                            "DepartureDateTime":"' . $dpTime . '",
                            "ArrivalDateTime":"' . $arrTime . '",
                            "FlightNumber":"' . $mCarrierFN . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $bCode . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $departure . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $arrival . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $mCarrier . '",
                                "FlightNumber":"' . $mCarrierFN . '"
                            }
                            },{
                            "DepartureDateTime":"' . $dpTime1 . '",
                            "ArrivalDateTime":"' . $arrTime1 . '",
                            "FlightNumber":"' . $mCarrierFN1 . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $bCode1 . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $departure1 . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $arrival1 . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $mCarrier1 . '",
                                "FlightNumber":"' . $mCarrierFN1 . '"
                            }
                            }]';

            } else if ($Segment == 3) {

                $departure = $flightData['segments'][0]['departure'];
                $arrival = $flightData['segments'][0]['arrival'];
                $dpTime = $flightData['segments'][0]['departureTime'];
                $arrTime = $flightData['segments'][0]['arrivalTime'];
                $bCode = $flightData['segments'][0]['bookingcode'];
                $mCarrier = $flightData['segments'][0]['marketingcareer'];
                $mCarrierFN = $flightData['segments'][0]['marketingflight'];
                $oCarrier = $flightData['segments'][0]['operatingcareer'];
                $oCarrierFN = $flightData['segments'][0]['operatingflight'];

                $departure1 = $flightData['segments'][1]['departure'];
                $arrival1 = $flightData['segments'][1]['arrival'];
                $dpTime1 = $flightData['segments'][1]['departureTime'];
                $arrTime1 = $flightData['segments'][1]['arrivalTime'];
                $bCode1 = $flightData['segments'][1]['bookingcode'];
                $mCarrier1 = $flightData['segments'][1]['marketingcareer'];
                $mCarrierFN1 = $flightData['segments'][1]['marketingflight'];
                $oCarrier1 = $flightData['segments'][1]['operatingcareer'];
                $oCarrierFN1 = $flightData['segments'][1]['operatingflight'];

                $departure2 = $flightData['segments'][2]['departure'];
                $arrival2 = $flightData['segments'][2]['arrival'];
                $dpTime2 = $flightData['segments'][2]['departureTime'];
                $arrTime2 = $flightData['segments'][2]['arrivalTime'];
                $bCode2 = $flightData['segments'][2]['bookingcode'];
                $mCarrier2 = $flightData['segments'][2]['marketingcareer'];
                $mCarrierFN2 = $flightData['segments'][2]['marketingflight'];
                $oCarrier2 = $flightData['segments'][2]['operatingcareer'];
                $oCarrierFN2 = $flightData['segments'][2]['operatingflight'];

                $FlightSegment = '[{
                            "DepartureDateTime":"' . $dpTime . '",
                            "ArrivalDateTime":"' . $arrTime . '",
                            "FlightNumber":"' . $mCarrierFN . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $bCode . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $departure . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $arrival . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $mCarrier . '",
                                "FlightNumber":"' . $mCarrierFN . '"
                            }
                            },{
                            "DepartureDateTime":"' . $dpTime1 . '",
                            "ArrivalDateTime":"' . $arrTime1 . '",
                            "FlightNumber":"' . $mCarrierFN1 . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $bCode1 . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $departure1 . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $arrival1 . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $mCarrier1 . '",
                                "FlightNumber":"' . $mCarrierFN1 . '"
                            }
                            },{
                            "DepartureDateTime":"' . $dpTime2 . '",
                            "ArrivalDateTime":"' . $arrTime2 . '",
                            "FlightNumber":"' . $mCarrierFN2 . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $bCode2 . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $departure2 . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $arrival2 . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $mCarrier2 . '",
                                "FlightNumber":"' . $mCarrierFN2 . '"
                            }
                            }]';
            }

        } else if ($tripType == "2" || $tripType == "return") {
            $flightData = $saveBookingAarray['roundData'];
            if ($Segment == 1) {
                $godeparture = $flightData['segments']['go'][0]['departure'];
                $goarrival = $flightData['segments']['go'][0]['arrival'];
                $godpTime = $flightData['segments']['go'][0]['departureTime'];
                $goarrTime = $flightData['segments']['go'][0]['arrivalTime'];
                $gobCode = $flightData['segments']['go'][0]['bookingcode'];
                $gomCarrier = $flightData['segments']['go'][0]['marketingcareer'];
                $gomCarrierFN = $flightData['segments']['go'][0]['marketingflight'];
                $gooCarrier = $flightData['segments']['go'][0]['operatingcareer'];
                $gooCarrierFN = $flightData['segments']['go'][0]['operatingflight'];

                $backdeparture = $flightData['segments']['back'][0]['departure'];
                $backarrival = $flightData['segments']['back'][0]['arrival'];
                $backdpTime = $flightData['segments']['back'][0]['departureTime'];
                $backarrTime = $flightData['segments']['back'][0]['arrivalTime'];
                $backbCode = $flightData['segments']['back'][0]['bookingcode'];
                $backmCarrier = $flightData['segments']['back'][0]['marketingcareer'];
                $backmCarrierFN = $flightData['segments']['back'][0]['marketingflight'];
                $backoCarrier = $flightData['segments']['back'][0]['operatingcareer'];
                $backoCarrierFN = $flightData['segments']['back'][0]['operatingflight'];

                $FlightSegment = '[{
                            "DepartureDateTime":"' . $godpTime . '",
                            "ArrivalDateTime":"' . $goarrTime . '",
                            "FlightNumber":"' . $gomCarrierFN . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $gobCode . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $godeparture . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $goarrival . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $gomCarrier . '",
                                "FlightNumber":"' . $gomCarrierFN . '"
                            }
                            },
                            {
                            "DepartureDateTime":"' . $backdpTime . '",
                            "ArrivalDateTime":"' . $backarrTime . '",
                            "FlightNumber":"' . $backmCarrierFN . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $backbCode . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $backdeparture . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $backarrival . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $backmCarrier . '",
                                "FlightNumber":"' . $backmCarrierFN . '"
                            }
                            }]';

            } else if ($Segment == 2) {

                $godeparture = $flightData['segments']['go'][0]['departure'];
                $goarrival = $flightData['segments']['go'][0]['arrival'];
                $godpTime = $flightData['segments']['go'][0]['departureTime'];
                $goarrTime = $flightData['segments']['go'][0]['arrivalTime'];
                $gobCode = $flightData['segments']['go'][0]['bookingcode'];
                $gomCarrier = $flightData['segments']['go'][0]['marketingcareer'];
                $gomCarrierFN = $flightData['segments']['go'][0]['marketingflight'];
                $gooCarrier = $flightData['segments']['go'][0]['operatingcareer'];
                $gooCarrierFN = $flightData['segments']['go'][0]['operatingflight'];

                $godeparture1 = $flightData['segments']['go'][1]['departure'];
                $goarrival1 = $flightData['segments']['go'][1]['arrival'];
                $godpTime1 = $flightData['segments']['go'][1]['departureTime'];
                $goarrTime1 = $flightData['segments']['go'][1]['arrivalTime'];
                $gobCode1 = $flightData['segments']['go'][1]['bookingcode'];
                $gomCarrier1 = $flightData['segments']['go'][1]['marketingcareer'];
                $gomCarrierFN1 = $flightData['segments']['go'][1]['marketingflight'];
                $gooCarrier1 = $flightData['segments']['go'][1]['operatingcareer'];
                $gooCarrierFN1 = $flightData['segments']['go'][1]['operatingflight'];

                $backdeparture = $flightData['segments']['back'][0]['departure'];
                $backarrival = $flightData['segments']['back'][0]['arrival'];
                $backdpTime = $flightData['segments']['back'][0]['departureTime'];
                $backarrTime = $flightData['segments']['back'][0]['arrivalTime'];
                $backbCode = $flightData['segments']['back'][0]['bookingcode'];
                $backmCarrier = $flightData['segments']['back'][0]['marketingcareer'];
                $backmCarrierFN = $flightData['segments']['back'][0]['marketingflight'];
                $backoCarrier = $flightData['segments']['back'][0]['operatingcareer'];
                $backoCarrierFN = $flightData['segments']['back'][0]['operatingflight'];

                $backdeparture1 = $flightData['segments']['back'][1]['departure'];
                $backarrival1 = $flightData['segments']['back'][1]['arrival'];
                $backdpTime1 = $flightData['segments']['back'][1]['departureTime'];
                $backarrTime1 = $flightData['segments']['back'][1]['arrivalTime'];
                $backbCode1 = $flightData['segments']['back'][1]['bookingcode'];
                $backmCarrier1 = $flightData['segments']['back'][1]['marketingcareer'];
                $backmCarrierFN1 = $flightData['segments']['back'][1]['marketingflight'];
                $backoCarrier1 = $flightData['segments']['back'][1]['operatingcareer'];
                $backoCarrierFN1 = $flightData['segments']['back'][1]['operatingflight'];

                $FlightSegment = '[{
                            "DepartureDateTime":"' . $godpTime . '",
                            "ArrivalDateTime":"' . $goarrTime . '",
                            "FlightNumber":"' . $gomCarrierFN . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $gobCode . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $godeparture . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $goarrival . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $gomCarrier . '",
                                "FlightNumber":"' . $gomCarrierFN . '"
                            }
                            },
                            {
                            "DepartureDateTime":"' . $godpTime1 . '",
                            "ArrivalDateTime":"' . $goarrTime1 . '",
                            "FlightNumber":"' . $gomCarrierFN1 . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $gobCode1 . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $godeparture1 . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $goarrival1 . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $gomCarrier1 . '",
                                "FlightNumber":"' . $gomCarrierFN1 . '"
                            }
                            },
                            {
                            "DepartureDateTime":"' . $backdpTime . '",
                            "ArrivalDateTime":"' . $backarrTime . '",
                            "FlightNumber":"' . $backmCarrierFN . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $backbCode . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $backdeparture . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $backarrival . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $backmCarrier . '",
                                "FlightNumber":"' . $backmCarrierFN . '"
                            }
                            },
                            {
                            "DepartureDateTime":"' . $backdpTime1 . '",
                            "ArrivalDateTime":"' . $backarrTime1 . '",
                            "FlightNumber":"' . $backmCarrierFN1 . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $backbCode1 . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $backdeparture1 . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $backarrival1 . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $backmCarrier1 . '",
                                "FlightNumber":"' . $backmCarrierFN1 . '"
                            }
                            }]';

            } else if ($Segment == 3) {
                $godeparture = $flightData['segments']['go'][0]['departure'];
                $goarrival = $flightData['segments']['go'][0]['arrival'];
                $godpTime = $flightData['segments']['go'][0]['departureTime'];
                $goarrTime = $flightData['segments']['go'][0]['arrivalTime'];
                $gobCode = $flightData['segments']['go'][0]['bookingcode'];
                $gomCarrier = $flightData['segments']['go'][0]['marketingcareer'];
                $gomCarrierFN = $flightData['segments']['go'][0]['marketingflight'];
                $gooCarrier = $flightData['segments']['go'][0]['operatingcareer'];
                $gooCarrierFN = $flightData['segments']['go'][0]['operatingflight'];

                $godeparture1 = $flightData['segments']['go'][1]['departure'];
                $goarrival1 = $flightData['segments']['go'][1]['arrival'];
                $godpTime1 = $flightData['segments']['go'][1]['departureTime'];
                $goarrTime1 = $flightData['segments']['go'][1]['arrivalTime'];
                $gobCode1 = $flightData['segments']['go'][1]['bookingcode'];
                $gomCarrier1 = $flightData['segments']['go'][1]['marketingcareer'];
                $gomCarrierFN1 = $flightData['segments']['go'][1]['marketingflight'];
                $gooCarrier1 = $flightData['segments']['go'][1]['operatingcareer'];
                $gooCarrierFN1 = $flightData['segments']['go'][1]['operatingflight'];

                $godeparture2 = $flightData['segments']['go'][2]['departure'];
                $goarrival2 = $flightData['segments']['go'][2]['arrival'];
                $godpTime2 = $flightData['segments']['go'][2]['departureTime'];
                $goarrTime2 = $flightData['segments']['go'][2]['arrivalTime'];
                $gobCode2 = $flightData['segments']['go'][2]['bookingcode'];
                $gomCarrier2 = $flightData['segments']['go'][2]['marketingcareer'];
                $gomCarrierFN2 = $flightData['segments']['go'][2]['marketingflight'];
                $gooCarrier2 = $flightData['segments']['go'][2]['operatingcareer'];
                $gooCarrierFN2 = $flightData['segments']['go'][2]['operatingflight'];

                $backdeparture = $flightData['segments']['back'][0]['departure'];
                $backarrival = $flightData['segments']['back'][0]['arrival'];
                $backdpTime = $flightData['segments']['back'][0]['departureTime'];
                $backarrTime = $flightData['segments']['back'][0]['arrivalTime'];
                $backbCode = $flightData['segments']['back'][0]['bookingcode'];
                $backmCarrier = $flightData['segments']['back'][0]['marketingcareer'];
                $backmCarrierFN = $flightData['segments']['back'][0]['marketingflight'];
                $backoCarrier = $flightData['segments']['back'][0]['operatingcareer'];
                $backoCarrierFN = $flightData['segments']['back'][0]['operatingflight'];

                $backdeparture1 = $flightData['segments']['back'][1]['departure'];
                $backarrival1 = $flightData['segments']['back'][1]['arrival'];
                $backdpTime1 = $flightData['segments']['back'][1]['departureTime'];
                $backarrTime1 = $flightData['segments']['back'][1]['arrivalTime'];
                $backbCode1 = $flightData['segments']['back'][1]['bookingcode'];
                $backmCarrier1 = $flightData['segments']['back'][1]['marketingcareer'];
                $backmCarrierFN1 = $flightData['segments']['back'][1]['marketingflight'];
                $backoCarrier1 = $flightData['segments']['back'][1]['operatingcareer'];
                $backoCarrierFN1 = $flightData['segments']['back'][1]['operatingflight'];

                $backdeparture2 = $flightData['segments']['back'][2]['departure'];
                $backarrival2 = $flightData['segments']['back'][2]['arrival'];
                $backdpTime2 = $flightData['segments']['back'][2]['departureTime'];
                $backarrTime2 = $flightData['segments']['back'][2]['arrivalTime'];
                $backbCode2 = $flightData['segments']['back'][2]['bookingcode'];
                $backmCarrier2 = $flightData['segments']['back'][2]['marketingcareer'];
                $backmCarrierFN2 = $flightData['segments']['back'][2]['marketingflight'];
                $backoCarrier2 = $flightData['segments']['back'][2]['operatingcareer'];
                $backoCarrierFN2 = $flightData['segments']['back'][2]['operatingflight'];

                $FlightSegment = '[{
                            "DepartureDateTime":"' . $godpTime . '",
                            "ArrivalDateTime":"' . $goarrTime . '",
                            "FlightNumber":"' . $gomCarrierFN . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $gobCode . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $godeparture . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $goarrival . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $gomCarrier . '",
                                "FlightNumber":"' . $gomCarrierFN . '"
                            }
                            },
                            {
                            "DepartureDateTime":"' . $godpTime1 . '",
                            "ArrivalDateTime":"' . $goarrTime1 . '",
                            "FlightNumber":"' . $gomCarrierFN1 . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $gobCode1 . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $godeparture1 . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $goarrival1 . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $gomCarrier1 . '",
                                "FlightNumber":"' . $gomCarrierFN1 . '"
                            }
                            },{
                            "DepartureDateTime":"' . $godpTime2 . '",
                            "ArrivalDateTime":"' . $goarrTime2 . '",
                            "FlightNumber":"' . $gomCarrierFN2 . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $gobCode2 . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $godeparture2 . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $goarrival2 . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $gomCarrier2 . '",
                                "FlightNumber":"' . $gomCarrierFN2 . '"
                            }
                            },
                            {
                            "DepartureDateTime":"' . $backdpTime . '",
                            "ArrivalDateTime":"' . $backarrTime . '",
                            "FlightNumber":"' . $backmCarrierFN . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $backbCode . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $backdeparture . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $backarrival . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $backmCarrier . '",
                                "FlightNumber":"' . $backmCarrierFN . '"
                            }
                            },
                            {
                            "DepartureDateTime":"' . $backdpTime1 . '",
                            "ArrivalDateTime":"' . $backarrTime1 . '",
                            "FlightNumber":"' . $backmCarrierFN1 . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $backbCode1 . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $backdeparture1 . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $backarrival1 . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $backmCarrier1 . '",
                                "FlightNumber":"' . $backmCarrierFN1 . '"
                            }
                            },
                            {
                            "DepartureDateTime":"' . $backdpTime2 . '",
                            "ArrivalDateTime":"' . $backarrTime2 . '",
                            "FlightNumber":"' . $backmCarrierFN2 . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $backbCode2 . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $backdeparture2 . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $backarrival2 . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $backmCarrier2 . '",
                                "FlightNumber":"' . $backmCarrierFN2 . '"
                            }
                            }]';
            } else if ($Segment == 21) {

                $godeparture = $flightData['segments']['go'][0]['departure'];
                $goarrival = $flightData['segments']['go'][0]['arrival'];
                $godpTime = $flightData['segments']['go'][0]['departureTime'];
                $goarrTime = $flightData['segments']['go'][0]['arrivalTime'];
                $gobCode = $flightData['segments']['go'][0]['bookingcode'];
                $gomCarrier = $flightData['segments']['go'][0]['marketingcareer'];
                $gomCarrierFN = $flightData['segments']['go'][0]['marketingflight'];
                $gooCarrier = $flightData['segments']['go'][0]['operatingcareer'];
                $gooCarrierFN = $flightData['segments']['go'][0]['operatingflight'];

                $godeparture1 = $flightData['segments']['go'][1]['departure'];
                $goarrival1 = $flightData['segments']['go'][1]['arrival'];
                $godpTime1 = $flightData['segments']['go'][1]['departureTime'];
                $goarrTime1 = $flightData['segments']['go'][1]['arrivalTime'];
                $gobCode1 = $flightData['segments']['go'][1]['bookingcode'];
                $gomCarrier1 = $flightData['segments']['go'][1]['marketingcareer'];
                $gomCarrierFN1 = $flightData['segments']['go'][1]['marketingflight'];
                $gooCarrier1 = $flightData['segments']['go'][1]['operatingcareer'];
                $gooCarrierFN1 = $flightData['segments']['go'][1]['operatingflight'];

                $backdeparture = $flightData['segments']['back'][0]['departure'];
                $backarrival = $flightData['segments']['back'][0]['arrival'];
                $backdpTime = $flightData['segments']['back'][0]['departureTime'];
                $backarrTime = $flightData['segments']['back'][0]['arrivalTime'];
                $backbCode = $flightData['segments']['back'][0]['bookingcode'];
                $backmCarrier = $flightData['segments']['back'][0]['marketingcareer'];
                $backmCarrierFN = $flightData['segments']['back'][0]['marketingflight'];
                $backoCarrier = $flightData['segments']['back'][0]['operatingcareer'];
                $backoCarrierFN = $flightData['segments']['back'][0]['operatingflight'];

                $FlightSegment = '[{
                            "DepartureDateTime":"' . $godpTime . '",
                            "ArrivalDateTime":"' . $goarrTime . '",
                            "FlightNumber":"' . $gomCarrierFN . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $gobCode . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $godeparture . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $goarrival . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $gomCarrier . '",
                                "FlightNumber":"' . $gomCarrierFN . '"
                            }
                            },
                            {
                            "DepartureDateTime":"' . $godpTime1 . '",
                            "ArrivalDateTime":"' . $goarrTime1 . '",
                            "FlightNumber":"' . $gomCarrierFN1 . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $gobCode1 . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $godeparture1 . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $goarrival1 . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $gomCarrier1 . '",
                                "FlightNumber":"' . $gomCarrierFN1 . '"
                            }
                            },
                            {
                            "DepartureDateTime":"' . $backdpTime . '",
                            "ArrivalDateTime":"' . $backarrTime . '",
                            "FlightNumber":"' . $backmCarrierFN . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $backbCode . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $backdeparture . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $backarrival . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $backmCarrier . '",
                                "FlightNumber":"' . $backmCarrierFN . '"
                            }
                            }]';

            } else if ($Segment == 12) {

                $godeparture = $flightData['segments']['go'][0]['departure'];
                $goarrival = $flightData['segments']['go'][0]['arrival'];
                $godpTime = $flightData['segments']['go'][0]['departureTime'];
                $goarrTime = $flightData['segments']['go'][0]['arrivalTime'];
                $gobCode = $flightData['segments']['go'][0]['bookingcode'];
                $gomCarrier = $flightData['segments']['go'][0]['marketingcareer'];
                $gomCarrierFN = $flightData['segments']['go'][0]['marketingflight'];
                $gooCarrier = $flightData['segments']['go'][0]['operatingcareer'];
                $gooCarrierFN = $flightData['segments']['go'][0]['operatingflight'];

                $backdeparture = $flightData['segments']['back'][0]['departure'];
                $backarrival = $flightData['segments']['back'][0]['arrival'];
                $backdpTime = $flightData['segments']['back'][0]['departureTime'];
                $backarrTime = $flightData['segments']['back'][0]['arrivalTime'];
                $backbCode = $flightData['segments']['back'][0]['bookingcode'];
                $backmCarrier = $flightData['segments']['back'][0]['marketingcareer'];
                $backmCarrierFN = $flightData['segments']['back'][0]['marketingflight'];
                $backoCarrier = $flightData['segments']['back'][0]['operatingcareer'];
                $backoCarrierFN = $flightData['segments']['back'][0]['operatingflight'];

                $backdeparture1 = $flightData['segments']['back'][1]['departure'];
                $backarrival1 = $flightData['segments']['back'][1]['arrival'];
                $backdpTime1 = $flightData['segments']['back'][1]['departureTime'];
                $backarrTime1 = $flightData['segments']['back'][1]['arrivalTime'];
                $backbCode1 = $flightData['segments']['back'][1]['bookingcode'];
                $backmCarrier1 = $flightData['segments']['back'][1]['marketingcareer'];
                $backmCarrierFN1 = $flightData['segments']['back'][1]['marketingflight'];
                $backoCarrier1 = $flightData['segments']['back'][1]['operatingcareer'];
                $backoCarrierFN1 = $flightData['segments']['back'][1]['operatingflight'];

                $FlightSegment = '[{
                            "DepartureDateTime":"' . $godpTime . '",
                            "ArrivalDateTime":"' . $goarrTime . '",
                            "FlightNumber":"' . $gomCarrierFN . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $gobCode . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $godeparture . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $goarrival . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $gomCarrier . '",
                                "FlightNumber":"' . $gomCarrierFN . '"
                            }
                            },
                            {
                            "DepartureDateTime":"' . $backdpTime . '",
                            "ArrivalDateTime":"' . $backarrTime . '",
                            "FlightNumber":"' . $backmCarrierFN . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $backbCode . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $backdeparture . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $backarrival . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $backmCarrier . '",
                                "FlightNumber":"' . $backmCarrierFN . '"
                            }
                            },
                            {
                            "DepartureDateTime":"' . $backdpTime1 . '",
                            "ArrivalDateTime":"' . $backarrTime1 . '",
                            "FlightNumber":"' . $backmCarrierFN1 . '",
                            "NumberInParty":"' . $SeatReq . '",
                            "ResBookDesigCode":"' . $backbCode1 . '",
                            "Status":"NN",
                            "OriginLocation":{
                                "LocationCode":"' . $backdeparture1 . '"
                            },
                            "DestinationLocation":{
                                "LocationCode":"' . $backarrival1 . '"
                            },
                            "MarketingAirline":{
                                "Code":"' . $backmCarrier1 . '",
                                "FlightNumber":"' . $backmCarrierFN1 . '"
                            }
                            }]';

            }
        } else if($tripType == "3" || $tripType == "multicity"){
            $flightData = $saveBookingAarray['flightData'];

            $Allsegments = array(); 

            foreach($flightData['segments'] as $sgflight){

                $departure = $sgflight['departure'];
                $arrival = $sgflight['arrival'];
                $dpTime = $sgflight['departureTime'];
                $arrTime = $sgflight['arrivalTime'];
                $bCode = $sgflight['bookingcode'];
                $mCarrier = $sgflight['marketingcareer'];
                $mCarrierFN = $sgflight['marketingflight'];
                $oCarrier = $sgflight['operatingcareer'];
                $oCarrierFN = $sgflight['operatingflight'];


                $SingleSegment = array(
                            "DepartureDateTime"=>$dpTime,
                            "ArrivalDateTime"=> $arrTime,
                            "FlightNumber"=> $mCarrierFN,
                            "NumberInParty"=> "$SeatReq",
                            "ResBookDesigCode"=> $bCode,
                            "Status"=>"NN",
                            "OriginLocation"=>array(
                                "LocationCode"=>$departure
                            ),
                            "DestinationLocation"=>array(
                                "LocationCode"=> $arrival
                            ),
                            "MarketingAirline"=> array(
                                "Code"=> $mCarrier,
                                "FlightNumber"=> $mCarrierFN
                            )
                            ); 
                            
                    array_push($Allsegments, $SingleSegment);
            }

                $FlightSegment = json_encode($Allsegments);
        }

        $PersonFinal = json_encode($AllPerson);
        $AdvPassengerFinal = json_encode($AdvancePassnger);
        $SSRFinal = json_encode($AllSsr);

        $Request = '{
                        "CreatePassengerNameRecordRQ":{
                        "targetCity":"27YK",
                        "haltOnAirPriceError":true,
                        "TravelItineraryAddInfo":{
                            "AgencyInfo":{
                            "Address":{
                                "AddressLine":"Fly Far International",
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
                                        "Phone":"' . $Phone . '",
                                        "PhoneUseType":"H"
                                    }
                                ]
                            },
                            "PersonName": ' . $PersonFinal . '
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
                            "FlightSegment": '.$FlightSegment.'
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
                                        "PassengerType":[' . $paxRequest . ']
                                    }
                                }
                            }
                            }
                        ],
                        "SpecialReqDetails":{
                            "SpecialService":{
                            "SpecialServiceInfo":{
                                "AdvancePassenger": ' . $AdvPassengerFinal . ',
                                "Service":' . $SSRFinal . '
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

        // print($Request);

        try {

          
            $client_id= base64_encode("V1:351640:27YK:AA");
            //$client_secret = base64_encode("280ff537"); //cert
            $client_secret = base64_encode("spt5164"); //prod

            $token = base64_encode($client_id . ":" . $client_secret);
            $data = 'grant_type=client_credentials';

            $headers = array(
                'Authorization: Basic ' . $token,
                'Accept: /',
                'Content-Type: application/x-www-form-urlencoded',
            );
            
            $ch = curl_init();
            //curl_setopt($ch,CURLOPT_URL,"https://api-crt.cert.havail.sabre.com/v2/auth/token");
            curl_setopt($ch, CURLOPT_URL, "https://api.platform.sabre.com/v2/auth/token");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $res = curl_exec($ch);
            curl_close($ch);
            $resf = json_decode($res, 1);
            $access_token = $resf['access_token'];
            // echo json_encode($access_token);

        } catch (Exception $e) {

        }

        //Curl start
        $curl = curl_init();
        // print_r($Request);
        curl_setopt_array(
            $curl,
            array(
                //CURLOPT_URL => 'https://api-crt.cert.havail.sabre.com/v2.4.0/passenger/records?mode=create',   //Testing
                CURLOPT_URL => 'https://api.platform.sabre.com/v2.4.0/passenger/records?mode=create',
                //Live
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
                    'Authorization: Bearer ' . $access_token,
                ),
            )
        );

        $SabrerResponse = curl_exec($curl);
        //echo $response;

        curl_close($curl);
        $result = json_decode($SabrerResponse, true);


        if (isset($result['CreatePassengerNameRecordRS']['ItineraryRef']['ID'])) {
            $BookingPNR = $result['CreatePassengerNameRecordRS']['ItineraryRef']['ID'];
            $AirlinesPNR = '';
            $UniversalPnr='';
            saveBooking($conn, $BookingPNR, $saveBookingAarray);
            addBookingQueue($conn, $BookingPNR, $AirlinesPNR, $UniversalPnr, $bookingInfo, $PassengerData, $saveBookingAarray);

        } else if (isset($result['CreatePassengerNameRecordRS']['ApplicationResults']['Error'])) {
            $BookingPNR='';
            $bookingId='';
            addPax($conn, $BookingPNR, $agentId, $subagentId, $userId, $bookingId, $PassengerData);
            $resResult = $result['CreatePassengerNameRecordRS']['ApplicationResults']['Error'][0]['SystemSpecificResults'];
            $resError = $result['CreatePassengerNameRecordRS']['ApplicationResults']['Warning'][0]['SystemSpecificResults'];
            $response1['status'] = "error";
            $response1['message'] = "Booking Failed";
            $response1['result'] = $resResult;
            $response1['warning'] = $resError;

            echo json_encode($response1);
            exit();
        }else {
            $BookingPNR='';
            addPax($conn, $BookingPNR, $agentId, $subagentId, $userId, $bookingId, $PassengerData);
            $response1['status'] = "error";
            $response1['message'] = "Booking Failed";
            echo json_encode($response1);
            exit();
        }

    }else if ($gdsSystem == 'FlyHub') { 
        
        $SearchID = $_POST['flightPassengerData']['SearchID'];
        $ResultID = $_POST['flightPassengerData']['ResultID'];

        $Passenger = array();
        if ($adult > 0 && $child > 0 && $infants > 0) {
            for ($x = 0; $x < $adult; $x++) {

                ${'afName' . $x} = $PassengerData['adult'][$x]["afName"];
                ${'alName' . $x} = $PassengerData['adult'][$x]["alName"];
                ${'agender' . $x} = $PassengerData['adult'][$x]["agender"];
                ${'adob' . $x} = $PassengerData['adult'][$x]["adob"];
                ${'apassNo' . $x} = $PassengerData['adult'][$x]["apassNo"];
                ${'apassEx' . $x} = $PassengerData['adult'][$x]["apassEx"];
                ${'apassNation' . $x} = $PassengerData['adult'][$x]["apassNation"];

                if ($x == 0) {
                    $leadPass = true;
                } else {
                    $leadPass = false;
                }

                if (${'agender' . $x} == 'Male') {
                    ${'aTitle' . $x} = "MR";
                } else {
                    ${'aTitle' . $x} = "MS";
                }

                $Adultbasic = array(
                    "Title" => ${'aTitle' . $x},
                    "FirstName" => ${'afName' . $x},
                    "LastName" => ${'alName' . $x},
                    "PaxType" => "Adult",
                    "DateOfBirth" => ${'adob' . $x},
                    "Gender" => ${'agender' . $x},
                    "PassportNumber" => ${'apassNo' . $x},
                    "PassportExpiryDate" => ${'apassEx' . $x},
                    "PassportNationality" => ${'apassNation' . $x},
                    "Address1" => null,
                    "Address2" => null,
                    "CountryCode" => "BD",
                    "Nationality" => "BD",
                    "ContactNumber" => "+8809606912912",
                    "Email" => "support@flyfarint.com",
                    "IsLeadPassenger" => $leadPass,
                    "FFAirline" => null,
                    "FFNumber" => null,
                    "Baggage" => [
                        array(
                            "BaggageID" => null,
                        ),
                    ],
                    "Meal" => [
                        array(
                            "MealID" => null,
                        ),
                    ],

                );

                array_push($Passenger, $Adultbasic);

            }

            for ($x = 0; $x < $child; $x++) {

                ${'cfname' . $x} = $PassengerData['child'][$x]["cfName"];
                ${'clname' . $x} = $PassengerData['child'][$x]["clName"];
                ${'cgender' . $x} = $PassengerData['child'][$x]["cgender"];
                ${'cdob' . $x} = $PassengerData['child'][$x]["cdob"];
                ${'cpassNo' . $x} = $PassengerData['child'][$x]["cpassNo"];
                ${'cpassNoEx' . $x} = $PassengerData['child'][$x]["cpassEx"];
                ${'cpassNation' . $x} = $PassengerData['child'][$x]["cpassNation"];

                if (${'cgender' . $x} == 'Male') {
                    ${'cTitle' . $x} = "MSTR";
                } else {
                    ${'cTitle' . $x} = "MISS";
                }

                $Childbasic = array(
                    "Title" => ${'cTitle' . $x},
                    "FirstName" => ${'cfname' . $x},
                    "LastName" => ${'clname' . $x},
                    "PaxType" => "Child",
                    "DateOfBirth" => ${'cdob' . $x},
                    "Gender" => ${'cgender' . $x},
                    "PassportNumber" => ${'cpassNo' . $x},
                    "PassportExpiryDate" => ${'cpassNoEx' . $x},
                    "PassportNationality" => ${'cpassNation' . $x},
                    "Address1" => null,
                    "Address2" => null,
                    "CountryCode" => "BD",
                    "Nationality" => "BD",
                    "ContactNumber" => "+8809606912912",
                    "Email" => "support@flyfarint.com",
                    "IsLeadPassenger" => false,
                    "FFAirline" => null,
                    "FFNumber" => null,
                    "Baggage" => [
                        array(
                            "BaggageID" => null,
                        ),
                    ],
                    "Meal" => [
                        array(
                            "MealID" => null,
                        ),
                    ],

                );

                array_push($Passenger, $Childbasic);

            }

            for ($x = 0; $x < $infants; $x++) {

                ${'ifname' . $x} = $PassengerData['infant'][$x]["ifName"];
                ${'ilname' . $x} = $PassengerData['infant'][$x]["ilName"];
                ${'igender' . $x} = $PassengerData['infant'][$x]["igender"];
                ${'idob' . $x} = $PassengerData['infant'][$x]["idob"];
                ${'ipassNo' . $x} = $PassengerData['infant'][$x]["ipassNo"];
                ${'ipassNoEx' . $x} = $PassengerData['infant'][$x]["ipassEx"];
                ${'ipassNation' . $x} = $PassengerData['infant'][$x]["ipassNation"];

                if (${'igender' . $x} == 'Male') {
                    ${'iTitle' . $x} = "MSTR";
                } else {
                    ${'iTitle' . $x} = "MISS";
                }

                $Infantbasic = array(
                    "Title" => ${'iTitle' . $x},
                    "FirstName" => ${'ifname' . $x},
                    "LastName" => ${'ilname' . $x},
                    "PaxType" => "Infant",
                    "DateOfBirth" => ${'idob' . $x},
                    "Gender" => ${'igender' . $x},
                    "PassportNumber" => ${'ipassNo' . $x},
                    "PassportExpiryDate" => ${'ipassNoEx' . $x},
                    "PassportNationality" => ${'ipassNation' . $x},
                    "Address1" => null,
                    "Address2" => null,
                    "CountryCode" => "BD",
                    "Nationality" => "BD",
                    "ContactNumber" => "+8809606912912",
                    "Email" => "support@flyfarint.com",
                    "IsLeadPassenger" => false,
                    "FFAirline" => null,
                    "FFNumber" => null,
                    "Baggage" => [
                        array(
                            "BaggageID" => null,
                        ),
                    ],
                    "Meal" => [
                        array(
                            "MealID" => null,
                        ),
                    ],

                );

                array_push($Passenger, $Infantbasic);

            }

            $FinalResponse = array(
                "SearchID" => $SearchID,
                "ResultID" => $ResultID,
                "Passengers" => $Passenger,
                "PromotionCode" => null,
            );

            $FlyHubBookingRequst = (json_encode($FinalResponse, JSON_PRETTY_PRINT));

        } else if ($adult > 0 && $child > 0) {
            for ($x = 0; $x < $adult; $x++) {
                ${'afName' . $x} = $PassengerData['adult'][$x]["afName"];
                ${'alName' . $x} = $PassengerData['adult'][$x]["alName"];
                ${'agender' . $x} = $PassengerData['adult'][$x]["agender"];
                ${'adob' . $x} = $PassengerData['adult'][$x]["adob"];
                ${'apassNo' . $x} = $PassengerData['adult'][$x]["apassNo"];
                ${'apassEx' . $x} = $PassengerData['adult'][$x]["apassEx"];
                ${'apassNation' . $x} = $PassengerData['adult'][$x]["apassNation"];

                if ($x == 0) {
                    $leadPass = true;
                } else {
                    $leadPass = false;
                }

                if (${'agender' . $x} == 'Male') {
                    ${'aTitle' . $x} = "MR";
                } else {
                    ${'aTitle' . $x} = "MRS";
                }

                $Adultbasic = array(
                    "Title" => ${'aTitle' . $x},
                    "FirstName" => ${'afName' . $x},
                    "LastName" => ${'alName' . $x},
                    "PaxType" => "Adult",
                    "DateOfBirth" => ${'adob' . $x},
                    "Gender" => ${'agender' . $x},
                    "PassportNumber" => ${'apassNo' . $x},
                    "PassportExpiryDate" => ${'apassEx' . $x},
                    "PassportNationality" => ${'apassNation' . $x},
                    "Address1" => null,
                    "Address2" => null,
                    "CountryCode" => "BD",
                    "Nationality" => "BD",
                    "ContactNumber" => "+8809606912912",
                    "Email" => "support@flyfarint.com",
                    "IsLeadPassenger" => $leadPass,
                    "FFAirline" => null,
                    "FFNumber" => null,
                    "Baggage" => [
                        array(
                            "BaggageID" => null,
                        ),
                    ],
                    "Meal" => [
                        array(
                            "MealID" => null,
                        ),
                    ],

                );

                array_push($Passenger, $Adultbasic);

            }

            for ($x = 0; $x < $child; $x++) {
                $paxId = "";
                $result = $conn->query("SELECT * FROM passengers ORDER BY id DESC LIMIT 1");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                        $number = (int) $outputString + 1;
                        $paxId = "STP$number";
                    }
                } else {
                    $paxId = "STP1000";
                }

                ${'cfname' . $x} = $PassengerData['child'][$x]["cfName"];
                ${'clname' . $x} = $PassengerData['child'][$x]["clName"];
                ${'cgender' . $x} = $PassengerData['child'][$x]["cgender"];
                ${'cdob' . $x} = $PassengerData['child'][$x]["cdob"];
                ${'cpassNo' . $x} = $PassengerData['child'][$x]["cpassNo"];
                ${'cpassNoEx' . $x} = $PassengerData['child'][$x]["cpassEx"];
                ${'cpassNation' . $x} = $PassengerData['child'][$x]["cpassNation"];

                if (${'cgender' . $x} == 'Male') {
                    ${'cTitle' . $x} = "MSTR";
                } else {
                    ${'cTitle' . $x} = "MISS";
                }

                $Childbasic = array(
                    "Title" => ${'cTitle' . $x},
                    "FirstName" => ${'cfname' . $x},
                    "LastName" => ${'clname' . $x},
                    "PaxType" => "Child",
                    "DateOfBirth" => ${'cdob' . $x},
                    "Gender" => ${'cgender' . $x},
                    "PassportNumber" => ${'cpassNo' . $x},
                    "PassportExpiryDate" => ${'cpassNoEx' . $x},
                    "PassportNationality" => ${'cpassNation' . $x},
                    "Address1" => null,
                    "Address2" => null,
                    "CountryCode" => "BD",
                    "Nationality" => "BD",
                    "ContactNumber" => "+8809606912912",
                    "Email" => "support@flyfarint.com",
                    "IsLeadPassenger" => false,
                    "FFAirline" => null,
                    "FFNumber" => null,
                    "Baggage" => [
                        array(
                            "BaggageID" => null,
                        ),
                    ],
                    "Meal" => [
                        array(
                            "MealID" => null,
                        ),
                    ],

                );

                array_push($Passenger, $Childbasic);

            }

            $FinalResponse = array(
                "SearchID" => $SearchID,
                "ResultID" => $ResultID,
                "Passengers" => $Passenger,
                "PromotionCode" => null,
            );

            $FlyHubBookingRequst = (json_encode($FinalResponse, JSON_PRETTY_PRINT));

        } else if ($adult > 0 && $infants > 0) {
            for ($x = 0; $x < $adult; $x++) {

                ${'afName' . $x} = $PassengerData['adult'][$x]["afName"];
                ${'alName' . $x} = $PassengerData['adult'][$x]["alName"];
                ${'agender' . $x} = $PassengerData['adult'][$x]["agender"];
                ${'adob' . $x} = $PassengerData['adult'][$x]["adob"];
                ${'apassNo' . $x} = $PassengerData['adult'][$x]["apassNo"];
                ${'apassEx' . $x} = $PassengerData['adult'][$x]["apassEx"];
                ${'apassNation' . $x} = $PassengerData['adult'][$x]["apassNation"];

                if ($x == 0) {
                    $leadPass = true;
                } else {
                    $leadPass = false;
                }

                if (${'agender' . $x} == 'Male') {
                    ${'aTitle' . $x} = "MR";
                } else {
                    ${'aTitle' . $x} = "MRS";
                }

                $Adultbasic = array(
                    "Title" => ${'aTitle' . $x},
                    "FirstName" => ${'afName' . $x},
                    "LastName" => ${'alName' . $x},
                    "PaxType" => "Adult",
                    "DateOfBirth" => ${'adob' . $x},
                    "Gender" => ${'agender' . $x},
                    "PassportNumber" => ${'apassNo' . $x},
                    "PassportExpiryDate" => ${'apassEx' . $x},
                    "PassportNationality" => ${'apassNation' . $x},
                    "Address1" => null,
                    "Address2" => null,
                    "CountryCode" => "BD",
                    "Nationality" => "BD",
                    "ContactNumber" => "+8809606912912",
                    "Email" => "support@flyfarint.com",
                    "IsLeadPassenger" => $leadPass,
                    "FFAirline" => null,
                    "FFNumber" => null,
                    "Baggage" => [
                        array(
                            "BaggageID" => null,
                        ),
                    ],
                    "Meal" => [
                        array(
                            "MealID" => null,
                        ),
                    ],

                );

                array_push($Passenger, $Adultbasic);

            }

            for ($x = 0; $x < $infants; $x++) {

                ${'ifname' . $x} = $PassengerData['infant'][$x]["ifName"];
                ${'ilname' . $x} = $PassengerData['infant'][$x]["ilName"];
                ${'igender' . $x} = $PassengerData['infant'][$x]["igender"];
                ${'idob' . $x} = $PassengerData['infant'][$x]["idob"];
                ${'ipassNo' . $x} = $PassengerData['infant'][$x]["ipassNo"];
                ${'ipassNoEx' . $x} = $PassengerData['infant'][$x]["ipassEx"];
                ${'ipassNation' . $x} = $PassengerData['infant'][$x]["ipassNation"];

                if (${'igender' . $x} == 'Male') {
                    ${'iTitle' . $x} = "MSTR";
                } else {
                    ${'iTitle' . $x} = "MISS";
                }

                $Infantbasic = array(
                    "Title" => ${'iTitle' . $x},
                    "FirstName" => ${'ifname' . $x},
                    "LastName" => ${'ilname' . $x},
                    "PaxType" => "Infant",
                    "DateOfBirth" => ${'idob' . $x},
                    "Gender" => ${'igender' . $x},
                    "PassportNumber" => ${'ipassNo' . $x},
                    "PassportExpiryDate" => ${'ipassNoEx' . $x},
                    "PassportNationality" => ${'ipassNation' . $x},
                    "Address1" => null,
                    "Address2" => null,
                    "CountryCode" => "BD",
                    "Nationality" => "BD",
                    "ContactNumber" => "+8809606912912",
                    "Email" => "support@flyfarint.com",
                    "IsLeadPassenger" => false,
                    "FFAirline" => null,
                    "FFNumber" => null,
                    "Baggage" => [
                        array(
                            "BaggageID" => null,
                        ),
                    ],
                    "Meal" => [
                        array(
                            "MealID" => null,
                        ),
                    ],

                );

                array_push($Passenger, $Infantbasic);

            }

            $FinalResponse = array(
                "SearchID" => $SearchID,
                "ResultID" => $ResultID,
                "Passengers" => $Passenger,
                "PromotionCode" => null,
            );

            $FlyHubBookingRequst = (json_encode($FinalResponse, JSON_PRETTY_PRINT));

        } else if ($adult > 0) {

            for ($x = 0; $x < $adult; $x++) {

                ${'afName' . $x} = $PassengerData['adult'][$x]["afName"];
                ${'alName' . $x} = $PassengerData['adult'][$x]["alName"];
                ${'agender' . $x} = $PassengerData['adult'][$x]["agender"];
                ${'adob' . $x} = $PassengerData['adult'][$x]["adob"];
                ${'apassNo' . $x} = $PassengerData['adult'][$x]["apassNo"];
                ${'apassEx' . $x} = $PassengerData['adult'][$x]["apassEx"];
                ${'apassNation' . $x} = $PassengerData['adult'][$x]["apassNation"];

                if ($x == 0) {
                    $leadPass = true;
                } else {
                    $leadPass = false;
                }

                if (${'agender' . $x} == 'Male') {
                    ${'aTitle' . $x} = "MR";
                } else {
                    ${'aTitle' . $x} = "MRS";
                }

                $Adultbasic = array(
                    "Title" => ${'aTitle' . $x},
                    "FirstName" => ${'afName' . $x},
                    "LastName" => ${'alName' . $x},
                    "PaxType" => "Adult",
                    "DateOfBirth" => ${'adob' . $x},
                    "Gender" => ${'agender' . $x},
                    "PassportNumber" => ${'apassNo' . $x},
                    "PassportExpiryDate" => ${'apassEx' . $x},
                    "PassportNationality" => ${'apassNation' . $x},
                    "Address1" => null,
                    "Address2" => null,
                    "CountryCode" => "BD",
                    "Nationality" => "BD",
                    "ContactNumber" => "+8809606912912",
                    "Email" => "support@flyfarint.com",
                    "IsLeadPassenger" => $leadPass,
                    "FFAirline" => null,
                    "FFNumber" => null,
                    "Baggage" => [
                        array(
                            "BaggageID" => null,
                        ),
                    ],
                    "Meal" => [
                        array(
                            "MealID" => null,
                        ),
                    ],

                );

                array_push($Passenger, $Adultbasic);

            }

            $FinalResponse = array(
                "SearchID" => $SearchID,
                "ResultID" => $ResultID,
                "Passengers" => $Passenger,
                "PromotionCode" => null,
            );

            $FlyHubBookingRequst = (json_encode($FinalResponse, JSON_PRETTY_PRINT));

        }

        $curlflyhubauth = curl_init();

        curl_setopt_array(
            $curlflyhubauth,
            array(
                CURLOPT_URL => 'https://api.flyhub.com/api/v1/Authenticate',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                    "username": "ceo@flyfarint.com",
                    "apikey": "ENex7c5Ge+0~SGc1t71iccr1xXacDPdK51g=iTm9SlL+de39HF"
                    }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                ),
            )
        );

        $response = curl_exec($curlflyhubauth);
        $TokenJson = json_decode($response, true);
        $FlyhubToken = $TokenJson['TokenId']; //echo $FlyhubToken;

        //Pre Booking
        $curlFlyHubPreBooking = curl_init();

        curl_setopt_array($curlFlyHubPreBooking,
            array(CURLOPT_URL => 'https://api.flyhub.com/api/v1/AirPreBook',
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
                    "Authorization: Bearer $FlyhubToken",),
            )
        );

        $flyhubresponse1 = curl_exec($curlFlyHubPreBooking);

        curl_close($curlFlyHubPreBooking);

        // echo  $flyhubresponse1;

        $resutPreBook = json_decode($flyhubresponse1, true);

        if (isset($resutPreBook['Error']['ErrorMessage'])) {
            $FlyHubRes['status'] = "error";
            $FlyHubRes['message'] = $resutPreBook['Error']['ErrorMessage'];
            echo json_encode($FlyHubRes);
            exit();
        } else {

            sleep(5);

            $curlFlyHubBooking = curl_init();

            curl_setopt_array($curlFlyHubBooking,
                array(
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
                        "Authorization: Bearer $FlyhubToken",),
                )
            );

            $flyhubresponse = curl_exec($curlFlyHubBooking);

            curl_close($curlFlyHubBooking);
            $flyhubResult = json_decode($flyhubresponse, true);

            if (isset($flyhubResult['Error'])) {
                $FlyHubRes['status'] = "error";
                $FlyHubRes['message'] = $flyhubResult['Error']['ErrorMessage'];
                echo json_encode($FlyHubRes);
                exit();
            } else {
                if (isset($flyhubResult['BookingID'])) {
                    $BookingPNR = $flyhubResult['BookingID'];
                    $AirlinesPNR = '';
                    $UniversalPnr='';
                    saveBooking($conn, $BookingPNR, $saveBookingAarray);
                    addBookingQueue($conn, $BookingPNR, $AirlinesPNR, $UniversalPnr, $bookingInfo, $PassengerData, $saveBookingAarray);
                }

            }
        }
    }else if($gdsSystem == 'Galileo'){
        GalileoBooking($conn, $saveBookingAarray, $PassengerData, $bookingInfo);
    }
}

function GalileoBooking($conn, $saveBookingAarray, $PassengerData, $bookingInfo){
    $_POST = $PassengerData;
    $tripType = $_POST['tripType'];
	$adult = $_POST['adultCount'];
    $child = $_POST['childCount'];
    $infants =  $_POST['infantCount'];
    $segment = $_POST['segment'];
    $tDate = $_POST['tDate'];
    $eDate = $_POST['eDate'];
    $FareBasis = isset($_POST['fbcode']) ? $_POST['fbcode'] :'';
    $goFareBasis = isset($saveBookingAarray['roundData']['goFareBasisCode']) ? $saveBookingAarray['roundData']['goFareBasisCode'] :'';
    $backFareBasis = isset($saveBookingAarray['roundData']['goFareBasisCode']) ? $saveBookingAarray['roundData']['goFareBasisCode'] :'';
    $AirPricingSolutionKey = $_POST['airPriceKey'];
	
    $AdultPassenger =array();
    $AdultPassengerType =array();

    $ChildPassenger =array();
    $ChildPassengerType =array();

    $InfantPassenger =array();
    $InfantPassengerType =array();

    $AllPassenger = array();

	
    if($tripType == 1 || $tripType == 'onewway'){

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
				${'atitle'.$x} = 'MR';
			}else{
				${'atitle'.$x} = 'MRS';
			}


			//Flight Info
			

			$AdultPassengerItem=<<<EOM
							<BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="ADT$x" TravelerType="ADT" Gender="${'agender'.$x}" Nationality="${'apassNation'.$x}">
								<BookingTravelerName Prefix="${'atitle'.$x}" First="${'afName'.$x} " Last="${'alName'.$x}" />
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
				${'ctitle'.$x} = 'MR';
			}else{
				${'ctitle'.$x} = 'MISS';
			}


			//Flight Info
			

			$ChildPassengerItem=
            <<<EOM
            <BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="CNN$x" TravelerType="CNN" Gender="${'cgender'.$x}" Nationality="${'cpassNation'.$x}">
                    <BookingTravelerName Prefix="${'ctitle'.$x}" First="${'cfName'.$x} " Last="${'clName'.$x}" />
                    <SSR Type="DOCS" Status="HK" FreeText="P/${'cpassNation'.$x}/${'cpassNo'.$x}/${'cpassNation'.$x}/${'cdob'.$x}/${'cgender'.$x}/${'cpassEx'.$x}/${'clName'.$x}/${'cfName'.$x} " Carrier="$Cr" />
                </BookingTraveler>
            EOM;

            array_push($AllPassenger, $ChildPassengerItem);


			$ChildPassengerTypeItem =
            <<<EOM
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
                
				${'ititle'.$x} = 'MSTR';
			}else{
				${'ititle'.$x} = 'MISS';
			}


			//Flight Info
			

			$InfantPassengerItem=<<<EOM
							<BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="INF$x" TravelerType="INF" Gender="${'igender'.$x}" Nationality="${'ipassNation'.$x}">
								<BookingTravelerName Prefix="${'ititle'.$x}" First="${'ifName'.$x} " Last="${'ilName'.$x}" />
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
				${'atitle'.$x} = 'MR';
			}else{
				${'atitle'.$x} = 'MRS';
			}


			//Flight Info
			

			$AdultPassengerItem=<<<EOM
							<BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="ADT$x" TravelerType="ADT" Gender="${'agender'.$x}" Nationality="${'apassNation'.$x}">
								<BookingTravelerName Prefix="${'atitle'.$x}" First="${'afName'.$x} " Last="${'alName'.$x}" />
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
				${'ctitle'.$x} = 'MSTR';
			}else{
				${'ctitle'.$x} = 'MISS';
			}


			//Flight Info
			

			$ChildPassengerItem=<<<EOM
							<BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="CNN$x" TravelerType="CNN" Gender="${'cgender'.$x}" Nationality="${'cpassNation'.$x}">
								<BookingTravelerName Prefix="${'ctitle'.$x}" First="${'cfName'.$x} " Last="${'clName'.$x}" />
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
				${'atitle'.$x} = 'MR';
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

		$FareInfoKey = $_POST['adult'][0]['FareInfoRef'];
		$AirPriceInfoKey = $_POST['adult'][0]['AirFareInfo'];


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
							<univ:AirCreateReservationReq xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:univ="http://www.travelport.com/schema/universal_v51_0" TraceId="FFI_KayesFahim" TargetBranch="P4218912" RuleName="COMM" RetainReservation="Both" RestrictWaitlist="true" xmlns="http://www.travelport.com/schema/common_v51_0">
								<BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v51_0" OriginApplication="UAPI" />
									$PassengerNumAll
								<AgencyContactInfo xmlns="http://www.travelport.com/schema/common_v51_0">
									<PhoneNumber Location="DAC" Number="08809606912912" Text="Fly Far International" />
								</AgencyContactInfo>
									$AirPricingSolution
								<ActionStatus xmlns="http://www.travelport.com/schema/common_v51_0" Type="TAW" TicketDate="$tDate" ProviderCode="1G" />
									$AirPricingTicketingModifiers
							</univ:AirCreateReservationReq>
						</soapenv:Body>
					</soapenv:Envelope>
					EOM;


    }else if($tripType == 2 || $tripType == 'return'){
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
				<AirSegment Key="$backAirSKey" Group="$backG" Carrier="$backCr" FlightNumber="$backFNo" ProviderCode="1G" Origin="$backDep" Destination="$backArr" DepartureTime="$backDepTime" ArrivalTime="$backArrTime"></AirSegment>
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
			
			$AirPriceInfoKey = $_POST['adult'][0]['AirFareInfo'];
			$AdultgoFareInfoKey = $_POST['adult'][0]['goFareInfoRef'];
			$AdultbackFareInfoKey = $_POST['adult'][0]['backFareInfoRef'];

			
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

			

			$AirPriceInfoKey1 = $_POST['child'][0]['AirFareInfo'];
			$ChildgoFareInfoKey = $_POST['child'][0]['goFareInfoRef'];
			$ChildbackFareInfoKey = $_POST['child'][0]['backFareInfoRef'];

			
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


			$AirPriceInfoKey2 = $_POST['infant'][0]['AirFareInfo'];
			$InfantgoFareInfoKey = $_POST['infant'][0]['goFareInfoRef'];
			$InfantbackFareInfoKey = $_POST['infant'][0]['backFareInfoRef'];

			
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
			
			$AirPriceInfoKey = $_POST['adult'][0]['AirFareInfo'];
			$AdultgoFareInfoKey = $_POST['adult'][0]['goFareInfoRef'];
			$AdultbackFareInfoKey = $_POST['adult'][0]['backFareInfoRef'];

			
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

			
			$AirPriceInfoKey1 = $_POST['child'][0]['AirFareInfo'];
			$ChildgoFareInfoKey = $_POST['child'][0]['goFareInfoRef'];
			$ChildbackFareInfoKey = $_POST['child'][0]['backFareInfoRef'];

			
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

			$AirPriceInfoKey = $_POST['adult'][0]['AirFareInfo'];
			$AdultgoFareInfoKey = $_POST['adult'][0]['goFareInfoRef'];
			$AdultbackFareInfoKey = $_POST['adult'][0]['backFareInfoRef'];

			
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
			$InfantgoFareInfoKey = $_POST['child'][0]['gocfInfoKeygo'];
			$InfantbackFareInfoKey = $_POST['child'][0]['backcfInfoKeyback'];

			
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

			$AirPriceInfoKey = $_POST['adult'][0]['AirFareInfo'];
			$AdultgoFareInfoKey = $_POST['adult'][0]['goFareInfoRef'];
			$AdultbackFareInfoKey = $_POST['adult'][0]['backFareInfoRef'];

			
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
									<PhoneNumber Location="DAC" Number="08809606912912" Text="Fly Far International" />
								</AgencyContactInfo>
									$AirPricingSolution
								<ActionStatus xmlns="http://www.travelport.com/schema/common_v51_0" Type="TAW" TicketDate="$tDate" ProviderCode="1G" />
									$AirPricingTicketingModifiers
							</univ:AirCreateReservationReq>
						</soapenv:Body>
					</soapenv:Envelope>
					EOM;
			
	}

   // echo $message;


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
		
        if(isset($result['universalUniversalRecord']['@attributes']['LocatorCode'])){
            $BookingPNR = $result['universalUniversalRecord']['universalProviderReservationInfo']['@attributes']['LocatorCode'];
            $UniversalPnr = $result['universalUniversalRecord']['@attributes']['LocatorCode'];
            $AirlinesPNR = '';
            saveBooking($conn, $BookingPNR, $saveBookingAarray);
            addBookingQueue($conn, $BookingPNR, $AirlinesPNR, $UniversalPnr, $bookingInfo, $PassengerData,  $saveBookingAarray);
        }else{
            $BookingPNR='';
            addPax($conn, $BookingPNR, $agentId, $subagentId, $userId, $bookingId, $PassengerData);
            $response1['status'] = "error";
            $response1['message'] = "Booking Failed";
            echo json_encode($response1);
            exit();
            
        }
	
	}
    
}

function saveBooking($conn, $BookingPNR, $saveBookingAarray){

    $Data = $saveBookingAarray;
    $type = $Data['tripType'];
    $pnr = $BookingPNR;
    $createdAt = date('Y-m-d H:i:s');
    $uId = sha1(md5(time()));

    if ($type == 'oneway' || $type == 1) {
        $_POST = $Data['flightData'];
        $system = $_POST["system"];
        $segment = $_POST["segment"];

        if (isset($_POST["SearchID"]) && isset($_POST["ResultID"])) {
            $searchId = $_POST["SearchID"];
            $resultId = $_POST["ResultID"];
        } else {
            $searchId = '';
            $resultId = '';
        }

        if ($segment == 1) {
            $departure1 = $_POST['segments'][0]["departure"];
            $arrival1 = $_POST['segments'][0]["arrival"];
            $departureTime1 = $_POST['segments'][0]["departureTime"];
            $arrivalTime1 = $_POST['segments'][0]["arrivalTime"];
            $flightDuration1 = $_POST['segments'][0]["flightduration"];
            $transit1 = '';
            $marketingCareer1 = $_POST['segments'][0]["marketingcareer"];
            $marketingCareerName1 = $_POST['segments'][0]["marketingcareerName"];
            $marketingFlight1 = $_POST['segments'][0]["marketingflight"];
            $operatingCareer1 = $_POST['segments'][0]["operatingcareer"];
            $operatingFlight1 = $_POST['segments'][0]["operatingflight"];
            $departureAirport1 = str_replace("'", "''", $_POST['segments'][0]["departureAirport"]);
            $arrivalAirport1 = str_replace("'", "''", $_POST['segments'][0]["arrivalAirport"]);
            // $departureTerminal1 = $_POST['segments'][0]["departureTerminal"];
            // $arrivalTerminal1 = $_POST['segments'][0]["arrivalTerminal"];
            $departureLocation1 = $_POST['segments'][0]["departureLocation"];
            $arrivalLocation1 = $_POST['segments'][0]["arrivalLocation"];
            $bookingCode1 = $_POST['segments'][0]["bookingcode"];

            $sql = "INSERT INTO `segment_one_way`(
                `system`,
                `segment`,
                `pnr`,
                `departure1`,
                `arrival1`,
                `departureTime1`,
                `arrivalTime1`,
                `flightDuration1`,
                `marketingCareer1`,
                `marketingCareerName1`,
                `marketingFlight1`,
                `operatingCareer1`,
                `operatingFlight1`,
                `departureAirport1`,
                `arrivalAirport1`,
                -- `departureTerminal1`,
                -- `arrivalTerminal1`,
                `departureLocation1`,
                `arrivalLocation1`,
                `bookingCode1`,
                `resultId`,
                `searchId`,
                `createdAt`,
                `uid`
            )
            VALUES(
                '$system',
                '$segment',
                '$pnr',
                '$departure1',
                '$arrival1',
                '$departureTime1',
                '$arrivalTime1',
                '$flightDuration1',
                '$marketingCareer1',
                '$marketingCareerName1',
                '$marketingFlight1',
                '$operatingCareer1',
                '$operatingFlight1',
                '$departureAirport1',
                '$arrivalAirport1',
                '$departureLocation1',
                '$arrivalLocation1',
                '$bookingCode1',
                '$searchId',
                '$resultId',
                '$createdAt',
                '$uId'

            )";

        } else if ($segment == 2) {
            $departure1 = $_POST['segments'][0]["departure"];
            $arrival1 = $_POST['segments'][0]["arrival"];
            $departureTime1 = $_POST['segments'][0]["departureTime"];
            $arrivalTime1 = $_POST['segments'][0]["arrivalTime"];
            $flightDuration1 = $_POST['segments'][0]["flightduration"];
            $transit1 = $_POST['transit']["transit1"];
            $marketingCareer1 = $_POST['segments'][0]["marketingcareer"];
            $marketingCareerName1 = $_POST['segments'][0]["marketingcareerName"];
            $marketingFlight1 = $_POST['segments'][0]["marketingflight"];
            $operatingCareer1 = $_POST['segments'][0]["operatingcareer"];
            $operatingFlight1 = $_POST['segments'][0]["operatingflight"];
            $departureAirport1 = str_replace("'", "''", $_POST['segments'][0]["departureAirport"]);
            $arrivalAirport1 = str_replace("'", "''", $_POST['segments'][0]["arrivalAirport"]);
            // $departureTerminal1 = $_POST['segments'][0]["departureTerminal"];
            // $arrivalTerminal1 = $_POST['segments'][0]["arrivalTerminal"];
            $departureLocation1 = $_POST['segments'][0]["departureLocation"];
            $arrivalLocation1 = $_POST['segments'][0]["arrivalLocation"];
            $bookingCode1 = $_POST['segments'][0]["bookingcode"];

            //segment 2
            $departure2 = $_POST['segments'][1]["departure"];
            $arrival2 = $_POST['segments'][1]["arrival"];
            $departureTime2 = $_POST['segments'][1]["departureTime"];
            $arrivalTime2 = $_POST['segments'][1]["arrivalTime"];
            $flightDuration2 = $_POST['segments'][1]["flightduration"];
            $marketingCareer2 = $_POST['segments'][1]["marketingcareer"];
            $marketingCareerName2 = $_POST['segments'][1]["marketingcareerName"];
            $marketingFlight2 = $_POST['segments'][1]["marketingflight"];
            $operatingCareer2 = $_POST['segments'][1]["operatingcareer"];
            $operatingFlight2 = $_POST['segments'][1]["operatingflight"];
            $departureAirport2 = str_replace("'", "''", $_POST['segments'][1]["departureAirport"]);
            $arrivalAirport2 = str_replace("'", "''", $_POST['segments'][1]["arrivalAirport"]);
            // $departureTerminal2 = $_POST['segments'][1]["departureTerminal"];
            // $arrivalTerminal2 = $_POST['segments'][1]["arrivalTerminal"];
            $departureLocation2 = $_POST['segments'][1]["departureLocation"];
            $arrivalLocation2 = $_POST['segments'][1]["arrivalLocation"];
            $bookingCode2 = $_POST['segments'][1]["bookingcode"];

            $sql = "INSERT INTO `segment_one_way`(
                `system`,
                `segment`,
                `pnr`,
                `departure1`,
                `departure2`,
                `arrival1`,
                `arrival2`,
                `departureTime1`,
                `departureTime2`,
                `arrivalTime1`,
                `arrivalTime2`,
                `flightDuration1`,
                `flightDuration2`,
                `transit1`,
                `marketingCareer1`,
                `marketingCareer2`,
                `marketingCareerName1`,
                `marketingCareerName2`,
                `marketingFlight1`,
                `marketingFlight2`,
                `operatingCareer1`,
                `operatingCareer2`,
                `operatingFlight1`,
                `operatingFlight2`,
                `departureAirport1`,
                `departureAirport2`,
                `arrivalAirport1`,
                `arrivalAirport2`,
                `departureLocation1`,
                `departureLocation2`,
                `arrivalLocation1`,
                `arrivalLocation2`,
                `bookingCode1`,
                `bookingCode2`,
                `resultId`,
                `searchId`,
                `createdAt`,
                `uid`
            )
            VALUES(
                '$system',
                '$segment',
                '$pnr',
                '$departure1',
                '$departure2',
                '$arrival1',
                '$arrival2',
                '$departureTime1',
                '$departureTime2',
                '$arrivalTime1',
                '$arrivalTime2',
                '$flightDuration1',
                '$flightDuration2',
                '$transit1',
                '$marketingCareer1',
                '$marketingCareer2',
                '$marketingCareerName1',
                '$marketingCareerName2',
                '$marketingFlight1',
                '$marketingFlight2',
                '$operatingCareer1',
                '$operatingCareer2',
                '$operatingFlight1',
                '$operatingFlight2',
                '$departureAirport1',
                '$departureAirport2',
                '$arrivalAirport1',
                '$arrivalAirport2',
                '$departureLocation1',
                '$departureLocation2',
                '$arrivalLocation1',
                '$arrivalLocation2',
                '$bookingCode1',
                '$bookingCode2',
                '$searchId',
                '$resultId',
                '$createdAt',
                '$uId'

            )";

        } else if ($segment == 3) {
            $departure1 = $_POST['segments'][0]["departure"];
            $arrival1 = $_POST['segments'][0]["arrival"];
            $departureTime1 = $_POST['segments'][0]["departureTime"];
            $arrivalTime1 = $_POST['segments'][0]["arrivalTime"];
            $flightDuration1 = $_POST['segments'][0]["flightduration"];
            $transit1 = $_POST['transit']["transit1"];
            $marketingCareer1 = $_POST['segments'][0]["marketingcareer"];
            $marketingCareerName1 = $_POST['segments'][0]["marketingcareerName"];
            $marketingFlight1 = $_POST['segments'][0]["marketingflight"];
            $operatingCareer1 = $_POST['segments'][0]["operatingcareer"];
            $operatingFlight1 = $_POST['segments'][0]["operatingflight"];
            $departureAirport1 = str_replace("'", "''", $_POST['segments'][0]["departureAirport"]);
            $arrivalAirport1 = str_replace("'", "''", $_POST['segments'][0]["arrivalAirport"]);
            // $departureTerminal1 = $_POST['segments'][0]["departureTerminal"];
            // $arrivalTerminal1 = $_POST['segments'][0]["arrivalTerminal"];
            $departureLocation1 = $_POST['segments'][0]["departureLocation"];
            $arrivalLocation1 = $_POST['segments'][0]["arrivalLocation"];
            $bookingCode1 = $_POST['segments'][0]["bookingcode"];

            //segment 2
            $departure2 = $_POST['segments'][1]["departure"];
            $arrival2 = $_POST['segments'][1]["arrival"];
            $departureTime2 = $_POST['segments'][1]["departureTime"];
            $arrivalTime2 = $_POST['segments'][1]["arrivalTime"];
            $flightDuration2 = $_POST['segments'][1]["flightduration"];
            $transit2 = $_POST['transit']["transit2"];
            $marketingCareer2 = $_POST['segments'][1]["marketingcareer"];
            $marketingCareerName2 = $_POST['segments'][1]["marketingcareerName"];
            $marketingFlight2 = $_POST['segments'][1]["marketingflight"];
            $operatingCareer2 = $_POST['segments'][1]["operatingcareer"];
            $operatingFlight2 = $_POST['segments'][1]["operatingflight"];
            $departureAirport2 = str_replace("'", "''", $_POST['segments'][1]["departureAirport"]);
            $arrivalAirport2 = str_replace("'", "''", $_POST['segments'][1]["arrivalAirport"]);
            // $departureTerminal2 = $_POST['segments'][1]["departureTerminal"];
            // $arrivalTerminal2 = $_POST['segments'][1]["arrivalTerminal"];
            $departureLocation2 = $_POST['segments'][1]["departureLocation"];
            $arrivalLocation2 = $_POST['segments'][1]["arrivalLocation"];
            $bookingCode2 = $_POST['segments'][1]["bookingcode"];

            //segment 3
            $departure3 = $_POST['segments'][2]["departure"];
            $arrival3 = $_POST['segments'][2]["arrival"];
            $departureTime3 = $_POST['segments'][2]["departureTime"];
            $arrivalTime3 = $_POST['segments'][2]["arrivalTime"];
            $flightDuration3 = $_POST['segments'][2]["flightduration"];
            $marketingCareer3 = $_POST['segments'][2]["marketingcareer"];
            $marketingCareerName3 = $_POST['segments'][2]["marketingcareerName"];
            $marketingFlight3 = $_POST['segments'][2]["marketingflight"];
            $operatingCareer3 = $_POST['segments'][2]["operatingcareer"];
            $operatingFlight3 = $_POST['segments'][2]["operatingflight"];
            $departureAirport3 = str_replace("'", "''", $_POST['segments'][2]["departureAirport"]);
            $arrivalAirport3 = str_replace("'", "''", $_POST['segments'][2]["arrivalAirport"]);
            // $departureTerminal3 = $_POST['segments'][2]["departureTerminal"];
            // $arrivalTerminal3 = $_POST['segments'][2]["arrivalTerminal"];
            $departureLocation3 = $_POST['segments'][2]["departureLocation"];
            $arrivalLocation3 = $_POST['segments'][2]["arrivalLocation"];
            $bookingCode3 = $_POST['segments'][2]["bookingcode"];

            $sql = "INSERT INTO `segment_one_way`(
                `system`,
                `segment`,
                 `pnr`,
                `departure1`,
                `departure2`,
                `departure3`,
                `arrival1`,
                `arrival2`,
                `arrival3`,
                `departureTime1`,
                `departureTime2`,
                `departureTime3`,
                `arrivalTime1`,
                `arrivalTime2`,
                `arrivalTime3`,
                `flightDuration1`,
                `flightDuration2`,
                `flightDuration3`,
                `transit1`,
                `transit2`,
                `marketingCareer1`,
                `marketingCareer2`,
                `marketingCareer3`,
                `marketingCareerName1`,
                `marketingCareerName2`,
                `marketingCareerName3`,
                `marketingFlight1`,
                `marketingFlight2`,
                `marketingFlight3`,
                `operatingCareer1`,
                `operatingCareer2`,
                `operatingCareer3`,
                `operatingFlight1`,
                `operatingFlight2`,
                `operatingFlight3`,
                `departureAirport1`,
                `departureAirport2`,
                `departureAirport3`,
                `arrivalAirport1`,
                `arrivalAirport2`,
                `arrivalAirport3`,
                `departureLocation1`,
                `departureLocation2`,
                `departureLocation3`,
                `arrivalLocation1`,
                `arrivalLocation2`,
                `arrivalLocation3`,
                `bookingCode1`,
                `bookingCode2`,
                `bookingCode3`,
                `resultId`,
                `searchId`,
                `createdAt`,
                `uid`
            )
            VALUES(
                '$system',
                '$segment',
                '$pnr',
                '$departure1',
                '$departure2',
                '$departure3',
                '$arrival1',
                '$arrival2',
                '$arrival3',
                '$departureTime1',
                '$departureTime2',
                '$departureTime3',
                '$arrivalTime1',
                '$arrivalTime2',
                '$arrivalTime3',
                '$flightDuration1',
                '$flightDuration2',
                '$flightDuration3',
                '$transit1',
                '$transit2',
                '$marketingCareer1',
                '$marketingCareer2',
                '$marketingCareer3',
                '$marketingCareerName1',
                '$marketingCareerName2',
                '$marketingCareerName3',
                '$marketingFlight1',
                '$marketingFlight2',
                '$marketingFlight3',
                '$operatingCareer1',
                '$operatingCareer2',
                '$operatingCareer3',
                '$operatingFlight1',
                '$operatingFlight2',
                '$operatingFlight3',
                '$departureAirport1',
                '$departureAirport2',
                '$departureAirport3',
                '$arrivalAirport1',
                '$arrivalAirport2',
                '$arrivalAirport3',
                '$departureLocation1',
                '$departureLocation2',
                '$departureLocation3',
                '$arrivalLocation1',
                '$arrivalLocation2',
                '$arrivalLocation3',
                '$bookingCode1',
                '$bookingCode2',
                '$bookingCode3',
                '$searchId',
                '$resultId',
                '$createdAt',
                '$uId'

            )";

        } elseif ($segment == 4) {
            $departure1 = $_POST['segments'][0]["departure"];
            $arrival1 = $_POST['segments'][0]["arrival"];
            $departureTime1 = $_POST['segments'][0]["departureTime"];
            $arrivalTime1 = $_POST['segments'][0]["arrivalTime"];
            $flightDuration1 = $_POST['segments'][0]["flightduration"];
            $transit1 = $_POST['transit']["transit1"];
            $marketingCareer1 = $_POST['segments'][0]["marketingcareer"];
            $marketingCareerName1 = $_POST['segments'][0]["marketingcareerName"];
            $marketingFlight1 = $_POST['segments'][0]["marketingflight"];
            $operatingCareer1 = $_POST['segments'][0]["operatingcareer"];
            $operatingFlight1 = $_POST['segments'][0]["operatingflight"];
            $departureAirport1 = str_replace("'", "''", $_POST['segments'][0]["departureAirport"]);
            $arrivalAirport1 = str_replace("'", "''", $_POST['segments'][0]["arrivalAirport"]);
            // $departureTerminal1 = $_POST['segments'][0]["departureTerminal"];
            // $arrivalTerminal1 = $_POST['segments'][0]["arrivalTerminal"];
            $departureLocation1 = $_POST['segments'][0]["departureLocation"];
            $arrivalLocation1 = $_POST['segments'][0]["arrivalLocation"];
            $bookingCode1 = $_POST['segments'][0]["bookingcode"];

            //segment 2
            $departure2 = $_POST['segments'][1]["departure"];
            $arrival2 = $_POST['segments'][1]["arrival"];
            $departureTime2 = $_POST['segments'][1]["departureTime"];
            $arrivalTime2 = $_POST['segments'][1]["arrivalTime"];
            $flightDuration2 = $_POST['segments'][1]["flightduration"];
            $transit2 = $_POST['transit']["transit2"];
            $marketingCareer2 = $_POST['segments'][1]["marketingcareer"];
            $marketingCareerName2 = $_POST['segments'][1]["marketingcareerName"];
            $marketingFlight2 = $_POST['segments'][1]["marketingflight"];
            $operatingCareer2 = $_POST['segments'][1]["operatingcareer"];
            $operatingFlight2 = $_POST['segments'][1]["operatingflight"];
            $departureAirport2 = str_replace("'", "''", $_POST['segments'][1]["departureAirport"]);
            $arrivalAirport2 = str_replace("'", "''", $_POST['segments'][1]["arrivalAirport"]);
            // $departureTerminal2 = $_POST['segments'][1]["departureTerminal"];
            // $arrivalTerminal2 = $_POST['segments'][1]["arrivalTerminal"];
            $departureLocation2 = $_POST['segments'][1]["departureLocation"];
            $arrivalLocation2 = $_POST['segments'][1]["arrivalLocation"];
            $bookingCode2 = $_POST['segments'][1]["bookingcode"];

            //segment 3
            $departure3 = $_POST['segments'][2]["departure"];
            $arrival3 = $_POST['segments'][2]["arrival"];
            $departureTime3 = $_POST['segments'][2]["departureTime"];
            $arrivalTime3 = $_POST['segments'][2]["arrivalTime"];
            $flightDuration3 = $_POST['segments'][2]["flightduration"];
            $transit3 = $_POST['transit']['transit1'];
            $marketingCareer3 = $_POST['segments'][2]["marketingcareer"];
            $marketingCareerName3 = $_POST['segments'][2]["marketingcareerName"];
            $marketingFlight3 = $_POST['segments'][2]["marketingflight"];
            $operatingCareer3 = $_POST['segments'][2]["operatingcareer"];
            $operatingFlight3 = $_POST['segments'][2]["operatingflight"];
            $departureAirport3 = str_replace("'", "''", $_POST['segments'][2]["departureAirport"]);
            $arrivalAirport3 = str_replace("'", "''", $_POST['segments'][2]["arrivalAirport"]);
            // $departureTerminal3 = $_POST['segments'][2]["departureTerminal"];
            // $arrivalTerminal3 = $_POST['segments'][2]["arrivalTerminal"];
            $departureLocation3 = $_POST['segments'][2]["departureLocation"];
            $arrivalLocation3 = $_POST['segments'][2]["arrivalLocation"];
            $bookingCode3 = $_POST['segments'][2]["bookingcode"];

            //segment 4
            $departure4 = $_POST['segments'][3]["departure"];
            $arrival4 = $_POST['segments'][3]["arrival"];
            $departureTime4 = $_POST['segments'][3]["departureTime"];
            $arrivalTime4 = $_POST['segments'][3]["arrivalTime"];
            $flightDuration4 = $_POST['segments'][3]["flightduration"];
            $marketingCareer4 = $_POST['segments'][3]["marketingcareer"];
            $marketingCareerName4 = $_POST['segments'][3]["marketingcareerName"];
            $marketingFlight4 = $_POST['segments'][3]["marketingflight"];
            $operatingCareer4 = $_POST['segments'][3]["operatingcareer"];
            $operatingFlight4 = $_POST['segments'][3]["operatingflight"];
            $departureAirport4 = str_replace("'", "''", $_POST['segments'][3]["departureAirport"]);
            $arrivalAirport4 = str_replace("'", "''", $_POST['segments'][3]["arrivalAirport"]);
            // $departureTerminal4 = $_POST['segments'][3]["departureTerminal"];
            // $arrivalTerminal4 = $_POST['segments'][3]["arrivalTerminal"] ;
            $departureLocation4 = $_POST['segments'][3]["departureLocation"];
            $arrivalLocation4 = $_POST['segments'][3]["arrivalLocation"];
            $bookingCode4 = $_POST['segments'][3]["bookingcode"];

            $sql = "INSERT INTO `segment_one_way`(
                `system`,
                `segment`,
                 `pnr`,
                `departure1`,
                `departure2`,
                `departure3`,
                `departure4`,
                `arrival1`,
                `arrival2`,
                `arrival3`,
                `arrival4`,
                `departureTime1`,
                `departureTime2`,
                `departureTime3`,
                `departureTime4`,
                `arrivalTime1`,
                `arrivalTime2`,
                `arrivalTime3`,
                `arrivalTime4`,
                `flightDuration1`,
                `flightDuration2`,
                `flightDuration3`,
                `flightDuration4`,
                `transit1`,
                `transit2`,
                `transit3`,
                `marketingCareer1`,
                `marketingCareer2`,
                `marketingCareer3`,
                `marketingCareer4`,
                `marketingCareerName1`,
                `marketingCareerName2`,
                `marketingCareerName3`,
                `marketingCareerName4`,
                `marketingFlight1`,
                `marketingFlight2`,
                `marketingFlight3`,
                `marketingFlight4`,
                `operatingCareer1`,
                `operatingCareer2`,
                `operatingCareer3`,
                `operatingCareer4`,
                `operatingFlight1`,
                `operatingFlight2`,
                `operatingFlight3`,
                `operatingFlight4`,
                `departureAirport1`,
                `departureAirport2`,
                `departureAirport3`,
                `departureAirport4`,
                `arrivalAirport1`,
                `arrivalAirport2`,
                `arrivalAirport3`,
                `arrivalAirport4`,
                `departureLocation1`,
                `departureLocation2`,
                `departureLocation3`,
                `departureLocation4`,
                `arrivalLocation1`,
                `arrivalLocation2`,
                `arrivalLocation3`,
                `arrivalLocation4`,
                `bookingCode1`,
                `bookingCode2`,
                `bookingCode3`,
                `bookingCode4`,
                `resultId`,
                `searchId`,
                `createdAt`,
                `uid`
            )
            VALUES(
                '$system',
                '$segment',
                '$pnr',
                '$departure1',
                '$departure2',
                '$departure3',
                '$departure4',
                '$arrival1',
                '$arrival2',
                '$arrival3',
                '$arrival4',
                '$departureTime1',
                '$departureTime2',
                '$departureTime3',
                '$departureTime4',
                '$arrivalTime1',
                '$arrivalTime2',
                '$arrivalTime3',
                '$arrivalTime4',
                '$flightDuration1',
                '$flightDuration2',
                '$flightDuration3',
                '$flightDuration4',
                '$transit1',
                '$transit2',
                '$transit3',
                '$marketingCareer1',
                '$marketingCareer2',
                '$marketingCareer3',
                '$marketingCareer4',
                '$marketingCareerName1',
                '$marketingCareerName2',
                '$marketingCareerName3',
                '$marketingCareerName4',
                '$marketingFlight1',
                '$marketingFlight2',
                '$marketingFlight3',
                '$marketingFlight4',
                '$operatingCareer1',
                '$operatingCareer2',
                '$operatingCareer3',
                '$operatingCareer4',
                '$operatingFlight1',
                '$operatingFlight2',
                '$operatingFlight3',
                '$operatingFlight4',
                '$departureAirport1',
                '$departureAirport2',
                '$departureAirport3',
                '$departureAirport4',
                '$arrivalAirport1',
                '$arrivalAirport2',
                '$arrivalAirport3',
                '$arrivalAirport4',
                '$departureLocation1',
                '$departureLocation2',
                '$departureLocation3',
                '$departureLocation4',
                '$arrivalLocation1',
                '$arrivalLocation2',
                '$arrivalLocation3',
                '$arrivalLocation4',
                '$bookingCode1',
                '$bookingCode2',
                '$bookingCode3',
                '$bookingCode4',
                '$searchId',
                '$resultId',
                '$createdAt',
                '$uId'

            )";
        }

        $conn->query($sql);

    } else if ($type == 'return' || $type == 2) {
        $_POST = $Data['roundData'];
        $system = $_POST["system"];
        $segment = $_POST["segment"];
        if (isset($_POST["SearchID"]) && isset($_POST["ResultID"])) {
            $searchId = $_POST["SearchID"];
            $resultId = $_POST["ResultID"];
        } else {
            $searchId = '';
            $resultId = '';
        }

        if ($segment == 1) {
            $goMarketingCareer1 = $_POST['segments']['go'][0]["marketingcareer"];
            $goMarketingCareerName1 = $_POST['segments']['go'][0]["marketingcareerName"];
            $goMarketingFlight1 = $_POST['segments']['go'][0]["marketingflight"];
            $goOperatingCareer1 = $_POST['segments']['go'][0]["operatingcareer"];
            $goOperatingFlight1 = $_POST['segments']['go'][0]["operatingflight"];
            $goDeparture1 = $_POST['segments']['go'][0]["departure"];
            $goDepartureAirport1 = $_POST['segments']['go'][0]["departureAirport"];
            $goDepartureLocation1 = $_POST['segments']['go'][0]["departureLocation"];
            $goDepartureTime1 = $_POST['segments']['go'][0]["departureTime"];
            // $goDepTerminal1 = $_POST['segments']['go'][0]["departureTerminal"];
            // $goArrTerminal1 = $_POST['segments']['go'][0]["arrivalTerminal"];
            $goArrival1 = $_POST['segments']['go'][0]["arrival"];
            $goArrivalAirport1 = str_replace("'", "''", $_POST['segments']['go'][0]["arrivalAirport"]);
            $goArrivalLocation1 = str_replace("'", "''", $_POST['segments']['go'][0]["arrivalLocation"]);
            $goArrivalTime1 = $_POST['segments']['go'][0]["arrivalTime"];
            $goFlightDuration1 = $_POST['segments']['go'][0]["flightduration"];
            $goBookingCode1 = $_POST['segments']['go'][0]["bookingcode"];

            $backMarketingCareer1 = $_POST['segments']['back'][0]["marketingcareer"];
            $backMarketingCareerName1 = $_POST['segments']['back'][0]["marketingcareerName"];
            $backMarketingFlight1 = $_POST['segments']['back'][0]["marketingflight"];
            $backOperatingCareer1 = $_POST['segments']['back'][0]["operatingcareer"];
            $backOperatingFlight1 = $_POST['segments']['back'][0]["operatingflight"];
            $backDeparture1 = $_POST['segments']['back'][0]["departure"];
            $backDepartureAirport1 = str_replace("'", "''", $_POST['segments']['back'][0]["departureAirport"]);
            // $backdepTerminal1 = $_POST['segments']['back'][0]["departureTerminal"];
            // $backArrTerminal1 = $_POST['segments']['back'][0]["arrivalTerminal"];
            $backDepartureLocation1 = $_POST['segments']['back'][0]["departureLocation"];
            $backDepartureTime1 = $_POST['segments']['back'][0]["departureTime"];
            $backArrival1 = $_POST['segments']['back'][0]["arrival"];
            $backArrivalAirport1 = str_replace("'", "''", $_POST['segments']['back'][0]["arrivalAirport"]);
            $backArrivalLocation1 = $_POST['segments']['back'][0]["arrivalLocation"];
            $backArrivalTime1 = $_POST['segments']['back'][0]["arrivalTime"];
            $backFlightDuration1 = $_POST['segments']['back'][0]["flightduration"];
            $backBookingCode1 = $_POST['segments']['back'][0]["bookingcode"];

            $sql = "INSERT INTO `segment_return_way`(
                    `system`,
                    `segment`,
                    `pnr`,
                    `goMarketingCareer1`,
                    `goMarketingCareerName1`,
                    `goMarketingFlight1`,
                    `goOperatingCareer1`,
                    `goOperatingFlight1`,
                    `goDeparture1`,
                    `goArrival1`,
                    `goDepartureAirport1`,
                    `goArrivalAirport1`,
                    -- `goDepTerminal1`,
                    -- `goArrTerminal1`,
                    `goDepartureLocation1`,
                    `goArrivalLocation1`,
                    `goDepartureTime1`,
                    `goArrivalTime1`,
                    `goFlightDuration1`,
                    `goBookingCode1`,
                    `backMarketingCareer1`,
                    `backMarketingCareerName1`,
                    `backMarketingFlight1`,
                    `backOperatingCareer1`,
                    `backOperatingFlight1`,
                    `backDeparture1`,
                    `backArrival1`,
                    `backDepartureAirport1`,
                    `backArrivalAirport1`,
                    -- `backdepTerminal1`,
                    -- `backArrTerminal1`,
                    `backDepartureLocation1`,
                    `backArrivalLocation1`,
                    `backDepartureTime1`,
                    `backArrivalTime1`,
                    `backFlightDuration1`,
                    `backBookingCode1`,
                    `searchId`,
                    `resultId`,
                    `createdAt`,
                    `uid`
                )
                VALUES(
                    '$system',
                    '$segment',
                    '$pnr',
                    '$goMarketingCareer1',
                    '$goMarketingCareerName1',
                    '$goMarketingFlight1',
                    '$goOperatingCareer1',
                    '$goOperatingFlight1',
                    '$goDeparture1',
                    '$goArrival1',
                    '$goDepartureAirport1',
                    '$goArrivalAirport1',
                    '$goDepartureLocation1',
                    '$goArrivalLocation1',
                    '$goDepartureTime1',
                    '$goArrivalTime1',
                    '$goFlightDuration1',
                    '$goBookingCode1',

                    '$backMarketingCareer1',
                    '$backMarketingCareerName1',
                    '$backMarketingFlight1',
                    '$backOperatingCareer1',
                    '$backOperatingFlight1',
                    '$backDeparture1',
                    '$backArrival1',
                    '$backDepartureAirport1',
                    '$backArrivalAirport1',
                    '$backDepartureLocation1',
                    '$backArrivalLocation1',
                    '$backDepartureTime1',
                    '$backArrivalTime1',
                    '$backFlightDuration1',
                    '$backBookingCode1',
                    '$searchId',
                    '$resultId',
                    '$createdAt',
                    '$uId'

                )";

        } else if ($segment == 2) {
            // segment 1
            $goTransit1 = $_POST['transit']['go']['transit1'];
            $backTransit1 = $_POST['transit']['back']['transit1'];

            $goMarketingCareer1 = $_POST['segments']['go'][0]["marketingcareer"];
            $goMarketingCareerName1 = $_POST['segments']['go'][0]["marketingcareerName"];
            $goMarketingFlight1 = $_POST['segments']['go'][0]["marketingflight"];
            $goOperatingCareer1 = $_POST['segments']['go'][0]["operatingcareer"];
            $goOperatingFlight1 = $_POST['segments']['go'][0]["operatingflight"];
            $goDeparture1 = $_POST['segments']['go'][0]["departure"];
            $goDepartureAirport1 = str_replace("'", "''", $_POST['segments']['go'][0]["departureAirport"]);
            $goDepartureLocation1 = $_POST['segments']['go'][0]["departureLocation"];
            $goDepartureTime1 = $_POST['segments']['go'][0]["departureTime"];
            $goArrival1 = $_POST['segments']['go'][0]["arrival"];
            $goArrivalAirport1 = str_replace("'", "''", $_POST['segments']['go'][0]["arrivalAirport"]);

            $goDepTerminal1 = $_POST['segments']['go'][0]["departureTerminal"];
            $goArrTerminal1 = $_POST['segments']['go'][0]["arrivalTerminal"];

            $goArrivalLocation1 = $_POST['segments']['go'][0]["arrivalLocation"];
            $goArrivalTime1 = $_POST['segments']['go'][0]["arrivalTime"];
            $goFlightDuration1 = $_POST['segments']['go'][0]["flightduration"];
            $goBookingCode1 = $_POST['segments']['go'][0]["bookingcode"];

            $backMarketingCareer1 = $_POST['segments']['back'][0]["marketingcareer"];
            $backMarketingCareerName1 = $_POST['segments']['back'][0]["marketingcareerName"];
            $backMarketingFlight1 = $_POST['segments']['back'][0]["marketingflight"];
            $backOperatingCareer1 = $_POST['segments']['back'][0]["operatingcareer"];
            $backOperatingFlight1 = $_POST['segments']['back'][0]["operatingflight"];
            $backDeparture1 = $_POST['segments']['back'][0]["departure"];
            $backDepartureAirport1 = str_replace("'", "''", $_POST['segments']['back'][0]["departureAirport"]);
            $backdepTerminal1 = $_POST['segments']['back'][0]["departureTerminal"];
            $backArrTerminal1 = $_POST['segments']['back'][0]["arrivalTerminal"];
            $backDepartureLocation1 = $_POST['segments']['back'][0]["departureLocation"];
            $backDepartureTime1 = $_POST['segments']['back'][0]["departureTime"];
            $backArrival1 = $_POST['segments']['back'][0]["arrival"];
            $backArrivalAirport1 = str_replace("'", "''", $_POST['segments']['back'][0]["arrivalAirport"]);
            $backArrivalLocation1 = $_POST['segments']['back'][0]["arrivalLocation"];
            $backArrivalTime1 = $_POST['segments']['back'][0]["arrivalTime"];
            $backFlightDuration1 = $_POST['segments']['back'][0]["flightduration"];
            $backBookingCode1 = $_POST['segments']['back'][0]["bookingcode"];

            // segment 2
            $goMarketingCareer2 = $_POST['segments']['go'][1]["marketingcareer"];
            $goMarketingCareerName2 = $_POST['segments']['go'][1]["marketingcareerName"];
            $goMarketingFlight2 = $_POST['segments']['go'][1]["marketingflight"];
            $goOperatingCareer2 = $_POST['segments']['go'][1]["operatingcareer"];
            $goOperatingFlight2 = $_POST['segments']['go'][1]["operatingflight"];
            $goDeparture2 = $_POST['segments']['go'][1]["departure"];
            $goDepartureAirport2 = str_replace("'", "''", $_POST['segments']['go'][1]["departureAirport"]);
            $goDepartureLocation2 = $_POST['segments']['go'][1]["departureLocation"];
            $goDepartureTime2 = $_POST['segments']['go'][1]["departureTime"];
            $goArrival2 = $_POST['segments']['go'][1]["arrival"];
            $goArrivalAirport2 = $_POST['segments']['go'][1]["arrivalAirport"];

            $goDepTerminal2 = $_POST['segments']['go'][0]["departureTerminal"];
            $goArrTerminal2 = $_POST['segments']['go'][0]["arrivalTerminal"];

            $goArrivalLocation2 = $_POST['segments']['go'][1]["arrivalLocation"];
            $goArrivalTime2 = $_POST['segments']['go'][1]["arrivalTime"];
            $goFlightDuration2 = $_POST['segments']['go'][1]["flightduration"];
            $goBookingCode2 = $_POST['segments']['go'][1]["bookingcode"];

            $backMarketingCareer2 = $_POST['segments']['back'][1]["marketingcareer"];
            $backMarketingCareerName2 = $_POST['segments']['back'][1]["marketingcareerName"];
            $backMarketingFlight2 = $_POST['segments']['back'][1]["marketingflight"];
            $backOperatingCareer2 = $_POST['segments']['back'][1]["operatingcareer"];
            $backOperatingFlight2 = $_POST['segments']['back'][1]["operatingflight"];
            $backDeparture2 = $_POST['segments']['back'][1]["departure"];
            $backDepartureAirport2 = str_replace("'", "''", $_POST['segments']['back'][1]["departureAirport"]);

            $backdepTerminal2 = $_POST['segments']['back'][0]["departureTerminal"];
            $backArrTerminal2 = $_POST['segments']['back'][0]["arrivalTerminal"];

            $backDepartureLocation2 = $_POST['segments']['back'][1]["departureLocation"];
            $backDepartureTime2 = $_POST['segments']['back'][1]["departureTime"];
            $backArrival2 = $_POST['segments']['back'][1]["arrival"];
            $backArrivalAirport2 = str_replace("'", "''", $_POST['segments']['back'][1]["arrivalAirport"]);
            $backArrivalLocation2 = $_POST['segments']['back'][1]["arrivalLocation"];
            $backArrivalTime2 = $_POST['segments']['back'][1]["arrivalTime"];
            $backFlightDuration2 = $_POST['segments']['back'][1]["flightduration"];
            $backBookingCode2 = $_POST['segments']['back'][1]["bookingcode"];

            $sql = "INSERT INTO `segment_return_way`(
                    `system`,
                    `segment`,
                    `pnr`,
                    `goMarketingCareer1`,
                    `goMarketingCareerName1`,
                    `goMarketingFlight1`,
                    `goOperatingCareer1`,
                    `goOperatingFlight1`,
                    `goDeparture1`,
                    `goArrival1`,
                    `goDepartureAirport1`,
                    `goArrivalAirport1`,
                    -- `goDepTerminal1`,
                    -- `goArrTerminal1`,
                    `goDepartureLocation1`,
                    `goArrivalLocation1`,
                    `goDepartureTime1`,
                    `goArrivalTime1`,
                    `goFlightDuration1`,
                    `goBookingCode1`,
                    `goTransit1`,

                    `backMarketingCareer1`,
                    `backMarketingCareerName1`,
                    `backMarketingFlight1`,
                    `backOperatingCareer1`,
                    `backOperatingFlight1`,
                    `backDeparture1`,
                    `backArrival1`,
                    `backDepartureAirport1`,
                    `backArrivalAirport1`,
                    -- `backdepTerminal1`,
                    -- `backArrTerminal1`,
                    `backDepartureLocation1`,
                    `backArrivalLocation1`,
                    `backDepartureTime1`,
                    `backArrivalTime1`,
                    `backFlightDuration1`,
                    `backBookingCode1`,
                    `backTransit1`,
                    `goMarketingCareer2`,
                    `goMarketingCareerName2`,
                    `goMarketingFlight2`,
                    `goOperatingCareer2`,
                    `goOperatingFlight2`,
                    `goDeparture2`,
                    `goArrival2`,
                    `goDepartureAirport2`,
                    `goArrivalAirport2`,
                    -- `goDepTerminal2`,
                    -- `goArrTerminal2`,
                    `goDepartureLocation2`,
                    `goArrivalLocation2`,
                    `goDepartureTime2`,
                    `goArrivalTime2`,
                    `goFlightDuration2`,
                    `goBookingCode2`,
                    `backMarketingCareer2`,
                    `backMarketingCareerName2`,
                    `backMarketingFlight2`,
                    `backOperatingCareer2`,
                    `backOperatingFlight2`,
                    `backDeparture2`,
                    `backArrival2`,
                    `backDepartureAirport2`,
                    `backArrivalAirport2`,
                    -- `backdepTerminal2`,
                    -- `backArrTerminal2`,
                    `backDepartureLocation2`,
                    `backArrivalLocation2`,
                    `backDepartureTime2`,
                    `backArrivalTime2`,
                    `backFlightDuration2`,
                    `backBookingCode2`,
                    `searchId`,
                    `resultId`,
                    `createdAt`,
                    `uid`
                )
                VALUES(
                    '$system',
                    '$segment',
                    '$pnr',
                    '$goMarketingCareer1',
                    '$goMarketingCareerName1',
                    '$goMarketingFlight1',
                    '$goOperatingCareer1',
                    '$goOperatingFlight1',
                    '$goDeparture1',
                    '$goArrival1',
                    '$goDepartureAirport1',
                    '$goArrivalAirport1',
                    -- '$goDepTerminal1',
                    -- '$goArrTerminal1',
                    '$goDepartureLocation1',
                    '$goArrivalLocation1',
                    '$goDepartureTime1',
                    '$goArrivalTime1',
                    '$goFlightDuration1',
                    '$goBookingCode1',
                    '$goTransit1',

                    '$backMarketingCareer1',
                    '$backMarketingCareerName1',
                    '$backMarketingFlight1',
                    '$backOperatingCareer1',
                    '$backOperatingFlight1',
                    '$backDeparture1',
                    '$backArrival1',
                    '$backDepartureAirport1',
                    '$backArrivalAirport1',
                    -- '$backdepTerminal1',
                    -- '$backdepTerminal2',
                    '$backDepartureLocation1',
                    '$backArrivalLocation1',
                    '$backDepartureTime1',
                    '$backArrivalTime1',
                    '$backFlightDuration1',
                    '$backBookingCode1',
                    '$backTransit1',

                    '$goMarketingCareer2',
                    '$goMarketingCareerName2',
                    '$goMarketingFlight2',
                    '$goOperatingCareer2',
                    '$goOperatingFlight2',
                    '$goDeparture2',
                    '$goArrival2',
                    '$goDepartureAirport2',
                    '$goArrivalAirport2',
                    -- '$goDepTerminal2',
                    -- '$goArrTerminal2',
                    '$goDepartureLocation2',
                    '$goArrivalLocation2',
                    '$goDepartureTime2',
                    '$goArrivalTime2',
                    '$goFlightDuration2',
                    '$goBookingCode2',

                    '$backMarketingCareer2',
                    '$backMarketingCareerName2',
                    '$backMarketingFlight2',
                    '$backOperatingCareer2',
                    '$backOperatingFlight2',
                    '$backDeparture2',
                    '$backArrival2',
                    '$backDepartureAirport2',
                    '$backArrivalAirport2',
                    -- '$backdepTerminal2',
                    -- '$backArrTerminal2',
                    '$backDepartureLocation2',
                    '$backArrivalLocation2',
                    '$backDepartureTime2',
                    '$backArrivalTime2',
                    '$backFlightDuration2',
                    '$backBookingCode2',

                    '$searchId',
                    '$resultId',
                    '$createdAt',
                    '$uId'

                )";

        } else if ($segment == 3) {
            // segment 1
            $goTransit1 = $_POST['transit']['go']['transit1'];
            $goTransit2 = $_POST['transit']['go']['transit1'];
            $backTransit1 = $_POST['transit']['back']['transit1'];
            $backTransit2 = $_POST['transit']['back']['transit1'];

            $goMarketingCareer1 = $_POST['segments']['go'][0]["marketingcareer"];
            $goMarketingCareerName1 = $_POST['segments']['go'][0]["marketingcareerName"];
            $goMarketingFlight1 = $_POST['segments']['go'][0]["marketingflight"];
            $goOperatingCareer1 = $_POST['segments']['go'][0]["operatingcareer"];
            $goOperatingFlight1 = $_POST['segments']['go'][0]["operatingflight"];
            $goDeparture1 = $_POST['segments']['go'][0]["departure"];
            $goDepartureAirport1 = str_replace("'", "''", $_POST['segments']['go'][0]["departureAirport"]);
            $goDepartureLocation1 = $_POST['segments']['go'][0]["departureLocation"];
            $goDepartureTime1 = $_POST['segments']['go'][0]["departureTime"];
            $goArrival1 = $_POST['segments']['go'][0]["arrival"];
            $goArrivalAirport1 = str_replace("'", "''", $_POST['segments']['go'][0]["arrivalAirport"]);
            // $goDepTerminal1 = $_POST['segments']['go'][0]["departureTerminal"];
            // $goArrTerminal1 = $_POST['segments']['go'][0]["arrivalTerminal"];
            $goArrivalLocation1 = $_POST['segments']['go'][0]["arrivalLocation"];
            $goArrivalTime1 = $_POST['segments']['go'][0]["arrivalTime"];
            $goFlightDuration1 = $_POST['segments']['go'][0]["flightduration"];
            $goBookingCode1 = $_POST['segments']['go'][0]["bookingcode"];

            $backMarketingCareer1 = $_POST['segments']['back'][0]["marketingcareer"];
            $backMarketingCareerName1 = $_POST['segments']['back'][0]["marketingcareerName"];
            $backMarketingFlight1 = $_POST['segments']['back'][0]["marketingflight"];
            $backOperatingCareer1 = $_POST['segments']['back'][0]["operatingcareer"];
            $backOperatingFlight1 = $_POST['segments']['back'][0]["operatingflight"];
            $backDeparture1 = $_POST['segments']['back'][0]["departure"];
            $backDepartureAirport1 = str_replace("'", "''", $_POST['segments']['back'][0]["departureAirport"]);
            $backDepartureLocation1 = $_POST['segments']['back'][0]["departureLocation"];
            $backDepartureTime1 = $_POST['segments']['back'][0]["departureTime"];
            $backArrival1 = $_POST['segments']['back'][0]["arrival"];
            $backArrivalAirport1 = str_replace("'", "''", $_POST['segments']['back'][0]["arrivalAirport"]);
            // $backdepTerminal1 = $_POST['segments']['back'][0]["departureTerminal"];
            // $backArrTerminal1 = $_POST['segments']['back'][0]["arrivalTerminal"];
            $backArrivalLocation1 = $_POST['segments']['back'][0]["arrivalLocation"];
            $backArrivalTime1 = $_POST['segments']['back'][0]["arrivalTime"];
            $backFlightDuration1 = $_POST['segments']['back'][0]["flightduration"];
            $backBookingCode1 = $_POST['segments']['back'][0]["bookingcode"];

            // segment 2
            $goMarketingCareer2 = $_POST['segments']['go'][1]["marketingcareer"];
            $goMarketingCareerName2 = $_POST['segments']['go'][1]["marketingcareerName"];
            $goMarketingFlight2 = $_POST['segments']['go'][1]["marketingflight"];
            $goOperatingCareer2 = $_POST['segments']['go'][1]["operatingcareer"];
            $goOperatingFlight2 = $_POST['segments']['go'][1]["operatingflight"];
            $goDeparture2 = $_POST['segments']['go'][1]["departure"];
            $goDepartureAirport2 = str_replace("'", "''", $_POST['segments']['go'][1]["departureAirport"]);
            $goDepartureLocation2 = $_POST['segments']['go'][1]["departureLocation"];
            $goDepartureTime2 = $_POST['segments']['go'][1]["departureTime"];
            $goArrival2 = $_POST['segments']['go'][1]["arrival"];
            $goArrivalAirport2 = str_replace("'", "''", $_POST['segments']['go'][1]["arrivalAirport"]);
            // $goDepTerminal2 = $_POST['segments']['go'][1]["departureTerminal"];
            // $goArrTerminal2 = $_POST['segments']['go'][1]["arrivalTerminal"];
            $goArrivalLocation2 = $_POST['segments']['go'][1]["arrivalLocation"];
            $goArrivalTime2 = $_POST['segments']['go'][1]["arrivalTime"];
            $goFlightDuration2 = $_POST['segments']['go'][1]["flightduration"];
            $goBookingCode2 = $_POST['segments']['go'][1]["bookingcode"];

            $backMarketingCareer2 = $_POST['segments']['back'][1]["marketingcareer"];
            $backMarketingCareerName2 = $_POST['segments']['back'][1]["marketingcareerName"];
            $backMarketingFlight2 = $_POST['segments']['back'][1]["marketingflight"];
            $backOperatingCareer2 = $_POST['segments']['back'][1]["operatingcareer"];
            $backOperatingFlight2 = $_POST['segments']['back'][1]["operatingflight"];
            $backDeparture2 = $_POST['segments']['back'][1]["departure"];
            $backDepartureAirport2 = str_replace("'", "''", $_POST['segments']['back'][1]["departureAirport"]);
            $backDepartureLocation2 = $_POST['segments']['back'][1]["departureLocation"];
            $backDepartureTime2 = $_POST['segments']['back'][1]["departureTime"];
            $backArrival2 = $_POST['segments']['back'][1]["arrival"];
            $backArrivalAirport2 = str_replace("'", "''", $_POST['segments']['back'][1]["arrivalAirport"]);
            // $backdepTerminal2 = $_POST['segments']['back'][1]["departureTerminal"];
            // $backArrTerminal2 = $_POST['segments']['back'][1]["arrivalTerminal"];
            $backArrivalLocation2 = $_POST['segments']['back'][1]["arrivalLocation"];
            $backArrivalTime2 = $_POST['segments']['back'][1]["arrivalTime"];
            $backFlightDuration2 = $_POST['segments']['back'][1]["flightduration"];
            $backBookingCode2 = $_POST['segments']['back'][1]["bookingcode"];

            // segment 3
            $goMarketingCareer3 = $_POST['segments']['go'][2]["marketingcareer"];
            $goMarketingCareerName3 = $_POST['segments']['go'][2]["marketingcareerName"];
            $goMarketingFlight3 = $_POST['segments']['go'][2]["marketingflight"];
            $goOperatingCareer3 = $_POST['segments']['go'][2]["operatingcareer"];
            $goOperatingFlight3 = $_POST['segments']['go'][2]["operatingflight"];
            $goDeparture3 = $_POST['segments']['go'][2]["departure"];
            $goDepartureAirport3 = str_replace("'", "''", $_POST['segments']['go'][2]["departureAirport"]);
            $goDepartureLocation3 = $_POST['segments']['go'][2]["departureLocation"];
            $goDepartureTime3 = $_POST['segments']['go'][2]["departureTime"];
            $goArrival3 = $_POST['segments']['go'][2]["arrival"];
            $goArrivalAirport3 = str_replace("'", "''", $_POST['segments']['go'][2]["arrivalAirport"]);
            // $goDepTerminal3 = $_POST['segments']['go'][2]["departureTerminal"];
            // $goArrTerminal3 = $_POST['segments']['go'][2]["arrivalTerminal"];
            $goArrivalLocation3 = $_POST['segments']['go'][2]["arrivalLocation"];
            $goArrivalTime3 = $_POST['segments']['go'][2]["arrivalTime"];
            $goFlightDuration3 = $_POST['segments']['go'][2]["flightduration"];
            $goBookingCode3 = $_POST['segments']['go'][2]["bookingcode"];

            $backMarketingCareer3 = $_POST['segments']['back'][2]["marketingcareer"];
            $backMarketingCareerName3 = $_POST['segments']['back'][2]["marketingcareerName"];
            $backMarketingFlight3 = $_POST['segments']['back'][2]["marketingflight"];
            $backOperatingCareer3 = $_POST['segments']['back'][2]["operatingcareer"];
            $backOperatingFlight3 = $_POST['segments']['back'][2]["operatingflight"];
            $backDeparture3 = $_POST['segments']['back'][2]["departure"];
            $backDepartureAirport3 = str_replace("'", "''", $_POST['segments']['back'][2]["departureAirport"]);
            $backDepartureLocation3 = $_POST['segments']['back'][2]["departureLocation"];
            $backDepartureTime3 = $_POST['segments']['back'][2]["departureTime"];
            $backArrival3 = $_POST['segments']['back'][2]["arrival"];
            $backArrivalAirport3 = str_replace("'", "''", $_POST['segments']['back'][2]["arrivalAirport"]);
            // $backdepTerminal3 = $_POST['segments']['back'][2]["departureTerminal"];
            // $backArrTerminal3 = $_POST['segments']['back'][2]["arrivalTerminal"];
            $backArrivalLocation3 = $_POST['segments']['back'][2]["arrivalLocation"];
            $backArrivalTime3 = $_POST['segments']['back'][2]["arrivalTime"];
            $backFlightDuration3 = $_POST['segments']['back'][2]["flightduration"];
            $backBookingCode3 = $_POST['segments']['back'][2]["bookingcode"];

            $sql = "INSERT INTO `segment_return_way`(
                    `system`,
                    `segment`,
                    `pnr`,
                    `goMarketingCareer1`,
                    `goMarketingCareerName1`,
                    `goMarketingFlight1`,
                    `goOperatingCareer1`,
                    `goOperatingFlight1`,
                    `goDeparture1`,
                    `goArrival1`,
                    `goDepartureAirport1`,
                    `goArrivalAirport1`,
                    -- `goDepTerminal1`,
                    -- `goArrTerminal1`,
                    `goDepartureLocation1`,
                    `goArrivalLocation1`,
                    `goDepartureTime1`,
                    `goArrivalTime1`,
                    `goFlightDuration1`,
                    `goBookingCode1`,
                    `goTransit1`,
                    `goTransit2`,

                    `backMarketingCareer1`,
                    `backMarketingCareerName1`,
                    `backMarketingFlight1`,
                    `backOperatingCareer1`,
                    `backOperatingFlight1`,
                    `backDeparture1`,
                    `backArrival1`,
                    `backDepartureAirport1`,
                    `backArrivalAirport1`,
                    -- `backdepTerminal1`,
                    -- `backArrTerminal1`,
                    `backDepartureLocation1`,
                    `backArrivalLocation1`,
                    `backDepartureTime1`,
                    `backArrivalTime1`,
                    `backFlightDuration1`,
                    `backBookingCode1`,
                    `backTransit1`,
                    `backTransit2`,

                    `goMarketingCareer2`,
                    `goMarketingCareerName2`,
                    `goMarketingFlight2`,
                    `goOperatingCareer2`,
                    `goOperatingFlight2`,
                    `goDeparture2`,
                    `goArrival2`,
                    `goDepartureAirport2`,
                    `goArrivalAirport2`,
                    -- `goDepTerminal2`,
                    -- `goArrTerminal2`,
                    `goDepartureLocation2`,
                    `goArrivalLocation2`,
                    `goDepartureTime2`,
                    `goArrivalTime2`,
                    `goFlightDuration2`,
                    `goBookingCode2`,

                    `backMarketingCareer2`,
                    `backMarketingCareerName2`,
                    `backMarketingFlight2`,
                    `backOperatingCareer2`,
                    `backOperatingFlight2`,
                    `backDeparture2`,
                    `backArrival2`,
                    `backDepartureAirport2`,
                    `backArrivalAirport2`,
                    -- `backdepTerminal2`,
                    -- `backArrTerminal2`,
                    `backDepartureLocation2`,
                    `backArrivalLocation2`,
                    `backDepartureTime2`,
                    `backArrivalTime2`,
                    `backFlightDuration2`,
                    `backBookingCode2`,

                    `goMarketingCareer3`,
                    `goMarketingCareerName3`,
                    `goMarketingFlight3`,
                    `goOperatingCareer3`,
                    `goOperatingFlight3`,
                    `goDeparture3`,
                    `goArrival3`,
                    `goDepartureAirport3`,
                    `goArrivalAirport3`,
                    -- `goDepTerminal3`,
                    -- `goArrTerminal3`,
                    `goDepartureLocation3`,
                    `goArrivalLocation3`,
                    `goDepartureTime3`,
                    `goArrivalTime3`,
                    `goFlightDuration3`,
                    `goBookingCode3`,

                    `backMarketingCareer3`,
                    `backMarketingCareerName3`,
                    `backMarketingFlight3`,
                    `backOperatingCareer3`,
                    `backOperatingFlight3`,
                    `backDeparture3`,
                    `backArrival3`,
                    `backDepartureAirport3`,
                    `backArrivalAirport3`,
                    -- `backdepTerminal3`,
                    -- `backArrTerminal3`,
                    `backDepartureLocation3`,
                    `backArrivalLocation3`,
                    `backDepartureTime3`,
                    `backArrivalTime3`,
                    `backFlightDuration3`,
                    `backBookingCode3`,

                    `searchId`,
                    `resultId`,
                    `createdAt`,
                    `uid`
                )
                VALUES(
                    '$system',
                    '$segment',
                    '$pnr',
                    '$goMarketingCareer1',
                    '$goMarketingCareerName1',
                    '$goMarketingFlight1',
                    '$goOperatingCareer1',
                    '$goOperatingFlight1',
                    '$goDeparture1',
                    '$goArrival1',
                    '$goDepartureAirport1',
                    '$goArrivalAirport1',
                    '$goDepartureLocation1',
                    '$goArrivalLocation1',
                    '$goDepartureTime1',
                    '$goArrivalTime1',
                    '$goFlightDuration1',
                    '$goBookingCode1',
                    '$goTransit1',
                    '$goTransit2',

                    '$backMarketingCareer1',
                    '$backMarketingCareerName1',
                    '$backMarketingFlight1',
                    '$backOperatingCareer1',
                    '$backOperatingFlight1',
                    '$backDeparture1',
                    '$backArrival1',
                    '$backDepartureAirport1',
                    '$backArrivalAirport1',
                    '$backDepartureLocation1',
                    '$backArrivalLocation1',
                    '$backDepartureTime1',
                    '$backArrivalTime1',
                    '$backFlightDuration1',
                    '$backBookingCode1',
                    '$backTransit1',
                    '$backTransit2',

                    '$goMarketingCareer2',
                    '$goMarketingCareerName2',
                    '$goMarketingFlight2',
                    '$goOperatingCareer2',
                    '$goOperatingFlight2',
                    '$goDeparture2',
                    '$goArrival2',
                    '$goDepartureAirport2',
                    '$goArrivalAirport2',
                    '$goDepartureLocation2',
                    '$goArrivalLocation2',
                    '$goDepartureTime2',
                    '$goArrivalTime2',
                    '$goFlightDuration2',
                    '$goBookingCode2',

                    '$backMarketingCareer2',
                    '$backMarketingCareerName2',
                    '$backMarketingFlight2',
                    '$backOperatingCareer2',
                    '$backOperatingFlight2',
                    '$backDeparture2',
                    '$backArrival2',
                    '$backDepartureAirport2',
                    '$backArrivalAirport2',
                    '$backDepartureLocation2',
                    '$backArrivalLocation2',
                    '$backDepartureTime2',
                    '$backArrivalTime2',
                    '$backFlightDuration2',
                    '$backBookingCode2',

                    '$goMarketingCareer3',
                    '$goMarketingCareerName3',
                    '$goMarketingFlight3',
                    '$goOperatingCareer3',
                    '$goOperatingFlight3',
                    '$goDeparture3',
                    '$goArrival3',
                    '$goDepartureAirport3',
                    '$goArrivalAirport3',
                    '$goDepartureLocation3',
                    '$goArrivalLocation3',
                    '$goDepartureTime3',
                    '$goArrivalTime3',
                    '$goFlightDuration3',
                    '$goBookingCode3',

                    '$backMarketingCareer3',
                    '$backMarketingCareerName3',
                    '$backMarketingFlight3',
                    '$backOperatingCareer3',
                    '$backOperatingFlight3',
                    '$backDeparture3',
                    '$backArrival3',
                    '$backDepartureAirport3',
                    '$backArrivalAirport3',
                    '$backDepartureLocation3',
                    '$backArrivalLocation3',
                    '$backDepartureTime3',
                    '$backArrivalTime3',
                    '$backFlightDuration3',
                    '$backBookingCode3',

                    '$searchId',
                    '$resultId',
                    '$createdAt',
                    '$uId'

                )";

        } elseif ($segment == 12) {
            //go 1 details
            $goMarketingCareer1 = $_POST['segments']['go'][0]["marketingcareer"];
            $goMarketingCareerName1 = $_POST['segments']['go'][0]["marketingcareerName"];
            $goMarketingFlight1 = $_POST['segments']['go'][0]["marketingflight"];
            $goOperatingCareer1 = $_POST['segments']['go'][0]["operatingcareer"];
            $goOperatingFlight1 = $_POST['segments']['go'][0]["operatingflight"];
            $goDeparture1 = $_POST['segments']['go'][0]["departure"];
            $goDepartureAirport1 = $_POST['segments']['go'][0]["departureAirport"];
            $goDepartureLocation1 = $_POST['segments']['go'][0]["departureLocation"];
            $goDepartureTime1 = $_POST['segments']['go'][0]["departureTime"];
            $goArrival1 = $_POST['segments']['go'][0]["arrival"];
            $goArrivalAirport1 = $_POST['segments']['go'][0]["arrivalAirport"];
            $goDepTerminal1 = $_POST['segments']['go'][0]["departureTerminal"];
            $goArrTerminal1 = $_POST['segments']['go'][0]["arrivalTerminal"];

            $goArrivalLocation1 = $_POST['segments']['go'][0]["arrivalLocation"];
            $goArrivalTime1 = $_POST['segments']['go'][0]["arrivalTime"];
            $goFlightDuration1 = $_POST['segments']['go'][0]["flightduration"];
            $goBookingCode1 = $_POST['segments']['go'][0]["bookingcode"];

            //back 1 details
            $backTransit1 = $_POST['transit']['back']['transit1'];
            $backMarketingCareer1 = $_POST['segments']['back'][0]["marketingcareer"];
            $backMarketingCareerName1 = $_POST['segments']['back'][0]["marketingcareerName"];
            $backMarketingFlight1 = $_POST['segments']['back'][0]["marketingflight"];
            $backOperatingCareer1 = $_POST['segments']['back'][0]["operatingcareer"];
            $backOperatingFlight1 = $_POST['segments']['back'][0]["operatingflight"];
            $backDeparture1 = $_POST['segments']['back'][0]["departure"];
            $backDepartureAirport1 = $_POST['segments']['back'][0]["departureAirport"];
            $backDepartureLocation1 = $_POST['segments']['back'][0]["departureLocation"];
            $backDepartureTime1 = $_POST['segments']['back'][0]["departureTime"];
            $backArrival1 = $_POST['segments']['back'][0]["arrival"];
            $backArrivalAirport1 = $_POST['segments']['back'][0]["arrivalAirport"];
            // $backdepTerminal1 = $_POST['segments']['back'][0]["departureTerminal"];
            // $backArrTerminal1 = $_POST['segments']['back'][0]["arrivalTerminal"];
            $backArrivalLocation1 = $_POST['segments']['back'][0]["arrivalLocation"];
            $backArrivalTime1 = $_POST['segments']['back'][0]["arrivalTime"];
            $backFlightDuration1 = $_POST['segments']['back'][0]["flightduration"];
            $backBookingCode1 = $_POST['segments']['back'][0]["bookingcode"];

            //back 2 details
            $backMarketingCareer2 = $_POST['segments']['back'][1]["marketingcareer"];
            $backMarketingCareerName2 = $_POST['segments']['back'][1]["marketingcareerName"];
            $backMarketingFlight2 = $_POST['segments']['back'][1]["marketingflight"];
            $backOperatingCareer2 = $_POST['segments']['back'][1]["operatingcareer"];
            $backOperatingFlight2 = $_POST['segments']['back'][1]["operatingflight"];
            $backDeparture2 = $_POST['segments']['back'][1]["departure"];
            $backDepartureAirport2 = $_POST['segments']['back'][1]["departureAirport"];
            $backDepartureLocation2 = $_POST['segments']['back'][1]["departureLocation"];
            $backDepartureTime2 = $_POST['segments']['back'][1]["departureTime"];
            $backArrival2 = $_POST['segments']['back'][1]["arrival"];
            $backArrivalAirport2 = $_POST['segments']['back'][1]["arrivalAirport"];
            // $backdepTerminal2 = $_POST['segments']['back'][1]["departureTerminal"];
            // $backArrTerminal2 = $_POST['segments']['back'][1]["arrivalTerminal"];
            $backArrivalLocation2 = $_POST['segments']['back'][1]["arrivalLocation"];
            $backArrivalTime2 = $_POST['segments']['back'][1]["arrivalTime"];
            $backFlightDuration2 = $_POST['segments']['back'][1]["flightduration"];
            $backBookingCode2 = $_POST['segments']['back'][1]["bookingcode"];

            $sql = "INSERT INTO `segment_return_way`(
                    `system`,
                    `segment`,
                    `pnr`,
                    `goMarketingCareer1`,
                    `goMarketingCareerName1`,
                    `goMarketingFlight1`,
                    `goOperatingCareer1`,
                    `goOperatingFlight1`,
                    `goDeparture1`,
                    `goArrival1`,
                    `goDepartureAirport1`,
                    `goArrivalAirport1`,
                    -- `goDepTerminal1`,
                    -- `goArrTerminal1`,
                    `goDepartureLocation1`,
                    `goArrivalLocation1`,
                    `goDepartureTime1`,
                    `goArrivalTime1`,
                    `goFlightDuration1`,
                    `goBookingCode1`,
                    `backMarketingCareer1`,
                    `backMarketingCareerName1`,
                    `backMarketingFlight1`,
                    `backOperatingCareer1`,
                    `backOperatingFlight1`,
                    `backDeparture1`,
                    `backArrival1`,
                    `backDepartureAirport1`,
                    `backArrivalAirport1`,
                    -- `backdepTerminal1`,
                    -- `backArrTerminal1`,
                    `backDepartureLocation1`,
                    `backArrivalLocation1`,
                    `backDepartureTime1`,
                    `backArrivalTime1`,
                    `backFlightDuration1`,
                    `backBookingCode1`,
                    `backTransit1`,
                    `backMarketingCareer2`,
                    `backMarketingCareerName2`,
                    `backMarketingFlight2`,
                    `backOperatingCareer2`,
                    `backOperatingFlight2`,
                    `backDeparture2`,
                    `backArrival2`,
                    `backDepartureAirport2`,
                    `backArrivalAirport2`,
                    -- `backdepTerminal2`,
                    -- `backArrTerminal2`,
                    `backDepartureLocation2`,
                    `backArrivalLocation2`,
                    `backDepartureTime2`,
                    `backArrivalTime2`,
                    `backFlightDuration2`,
                    `backBookingCode2`,
                    `searchId`,
                    `resultId`,
                    `createdAt`,
                    `uid`
                )
                VALUES(
                    '$system',
                    '$segment',
                    '$pnr',
                    '$goMarketingCareer1',
                    '$goMarketingCareerName1',
                    '$goMarketingFlight1',
                    '$goOperatingCareer1',
                    '$goOperatingFlight1',
                    '$goDeparture1',
                    '$goArrival1',
                    '$goDepartureAirport1',
                    '$goArrivalAirport1',
                    '$goDepartureLocation1',
                    '$goArrivalLocation1',
                    '$goDepartureTime1',
                    '$goArrivalTime1',
                    '$goFlightDuration1',
                    '$goBookingCode1',

                    '$backMarketingCareer1',
                    '$backMarketingCareerName1',
                    '$backMarketingFlight1',
                    '$backOperatingCareer1',
                    '$backOperatingFlight1',
                    '$backDeparture1',
                    '$backArrival1',
                    '$backDepartureAirport1',
                    '$backArrivalAirport1',
                    '$backDepartureLocation1',
                    '$backArrivalLocation1',
                    '$backDepartureTime1',
                    '$backArrivalTime1',
                    '$backFlightDuration1',
                    '$backBookingCode1',
                    '$backTransit1',

                    '$backMarketingCareer2',
                    '$backMarketingCareerName2',
                    '$backMarketingFlight2',
                    '$backOperatingCareer2',
                    '$backOperatingFlight2',
                    '$backDeparture2',
                    '$backArrival2',
                    '$backDepartureAirport2',
                    '$backArrivalAirport2',
                    '$backDepartureLocation2',
                    '$backArrivalLocation2',
                    '$backDepartureTime2',
                    '$backArrivalTime2',
                    '$backFlightDuration2',
                    '$backBookingCode2',

                    '$searchId',
                    '$resultId',
                    '$createdAt',
                    '$uId'

                )";

        } elseif ($segment == 21) {
            //go 1 details
            $goMarketingCareer1 = $_POST['segments']['go'][0]["marketingcareer"];
            $goMarketingCareerName1 = $_POST['segments']['go'][0]["marketingcareerName"];
            $goMarketingFlight1 = $_POST['segments']['go'][0]["marketingflight"];
            $goOperatingCareer1 = $_POST['segments']['go'][0]["operatingcareer"];
            $goOperatingFlight1 = $_POST['segments']['go'][0]["operatingflight"];
            $goDeparture1 = $_POST['segments']['go'][0]["departure"];
            $goDepartureAirport1 = $_POST['segments']['go'][0]["departureAirport"];
            $goDepartureLocation1 = $_POST['segments']['go'][0]["departureLocation"];
            $goDepartureTime1 = $_POST['segments']['go'][0]["departureTime"];
            $goArrival1 = $_POST['segments']['go'][0]["arrival"];
            $goArrivalAirport1 = $_POST['segments']['go'][0]["arrivalAirport"];
            // $goDepTerminal1 = $_POST['segments']['go'][0]["departureTerminal"];
            // $goArrTerminal1 = $_POST['segments']['go'][0]["arrivalTerminal"];
            $goArrivalLocation1 = $_POST['segments']['go'][0]["arrivalLocation"];
            $goArrivalTime1 = $_POST['segments']['go'][0]["arrivalTime"];
            $goFlightDuration1 = $_POST['segments']['go'][0]["flightduration"];
            $goBookingCode1 = $_POST['segments']['go'][0]["bookingcode"];
            $goTransit1 = $_POST['transit']['go']['transit1'];

            // go 2 details
            $goMarketingCareer2 = $_POST['segments']['go'][1]["marketingcareer"];
            $goMarketingCareerName2 = $_POST['segments']['go'][1]["marketingcareerName"];
            $goMarketingFlight2 = $_POST['segments']['go'][1]["marketingflight"];
            $goOperatingCareer2 = $_POST['segments']['go'][1]["operatingcareer"];
            $goOperatingFlight2 = $_POST['segments']['go'][1]["operatingflight"];
            $goDeparture2 = $_POST['segments']['go'][1]["departure"];
            $goDepartureAirport2 = $_POST['segments']['go'][1]["departureAirport"];
            $goDepartureLocation2 = $_POST['segments']['go'][1]["departureLocation"];
            $goDepartureTime2 = $_POST['segments']['go'][1]["departureTime"];
            $goArrival2 = $_POST['segments']['go'][1]["arrival"];
            $goArrivalAirport2 = $_POST['segments']['go'][1]["arrivalAirport"];
            // $goDepTerminal2 = $_POST['segments']['go'][1]["departureTerminal"];
            // $goArrTerminal2 = $_POST['segments']['go'][1]["arrivalTerminal"];
            $goArrivalLocation2 = $_POST['segments']['go'][1]["arrivalLocation"];
            $goArrivalTime2 = $_POST['segments']['go'][1]["arrivalTime"];
            $goFlightDuration2 = $_POST['segments']['go'][1]["flightduration"];
            $goBookingCode2 = $_POST['segments']['go'][1]["bookingcode"];

            //back 1 details
            $backTransit1 = $_POST['transit']['back']['transit1'];
            $backMarketingCareer1 = $_POST['segments']['back'][0]["marketingcareer"];
            $backMarketingCareerName1 = $_POST['segments']['back'][0]["marketingcareerName"];
            $backMarketingFlight1 = $_POST['segments']['back'][0]["marketingflight"];
            $backOperatingCareer1 = $_POST['segments']['back'][0]["operatingcareer"];
            $backOperatingFlight1 = $_POST['segments']['back'][0]["operatingflight"];
            $backDeparture1 = $_POST['segments']['back'][0]["departure"];
            $backDepartureAirport1 = $_POST['segments']['back'][0]["departureAirport"];
            $backDepartureLocation1 = $_POST['segments']['back'][0]["departureLocation"];
            $backDepartureTime1 = $_POST['segments']['back'][0]["departureTime"];
            $backArrival1 = $_POST['segments']['back'][0]["arrival"];
            $backArrivalAirport1 = $_POST['segments']['back'][0]["arrivalAirport"];
            // $backdepTerminal1 = $_POST['segments']['back'][0]["departureTerminal"];
            // $backArrTerminal1 = $_POST['segments']['back'][0]["arrivalTerminal"];
            $backArrivalLocation1 = $_POST['segments']['back'][0]["arrivalLocation"];
            $backArrivalTime1 = $_POST['segments']['back'][0]["arrivalTime"];
            $backFlightDuration1 = $_POST['segments']['back'][0]["flightduration"];
            $backBookingCode1 = $_POST['segments']['back'][0]["bookingcode"];

            $sql = "INSERT INTO `segment_return_way`(
                        `system`,
                        `segment`,
                        `pnr`,
                        `goMarketingCareer1`,
                        `goMarketingCareerName1`,
                        `goMarketingFlight1`,
                        `goOperatingCareer1`,
                        `goOperatingFlight1`,
                        `goDeparture1`,
                        `goArrival1`,
                        `goDepartureAirport1`,
                        `goArrivalAirport1`,
                        `goDepartureLocation1`,
                        `goArrivalLocation1`,
                        `goDepartureTime1`,
                        `goArrivalTime1`,
                        `goFlightDuration1`,
                        `goBookingCode1`,
                        `goTransit1`,

                        `goMarketingCareer2`,
                        `goMarketingCareerName2`,
                        `goMarketingFlight2`,
                        `goOperatingCareer2`,
                        `goOperatingFlight2`,
                        `goDeparture2`,
                        `goArrival2`,
                        `goDepartureAirport2`,
                        `goArrivalAirport2`,
                        `goDepartureLocation2`,
                        `goArrivalLocation2`,
                        `goDepartureTime2`,
                        `goArrivalTime2`,
                        `goFlightDuration2`,
                        `goBookingCode2`,

                        `backMarketingCareer1`,
                        `backMarketingCareerName1`,
                        `backMarketingFlight1`,
                        `backOperatingCareer1`,
                        `backOperatingFlight1`,
                        `backDeparture1`,
                        `backArrival1`,
                        `backDepartureAirport1`,
                        `backArrivalAirport1`,
                        `backDepartureLocation1`,
                        `backArrivalLocation1`,
                        `backDepartureTime1`,
                        `backArrivalTime1`,
                        `backFlightDuration1`,
                        `backBookingCode1`,

                        `searchId`,
                        `resultId`,
                        `createdAt`,
                        `uid`
                    )
                    VALUES(
                        '$system',
                        '$segment',
                        '$pnr',
                        '$goMarketingCareer1',
                        '$goMarketingCareerName1',
                        '$goMarketingFlight1',
                        '$goOperatingCareer1',
                        '$goOperatingFlight1',
                        '$goDeparture1',
                        '$goArrival1',
                        '$goDepartureAirport1',
                        '$goArrivalAirport1',
                        '$goDepartureLocation1',
                        '$goArrivalLocation1',
                        '$goDepartureTime1',
                        '$goArrivalTime1',
                        '$goFlightDuration1',
                        '$goBookingCode1',
                        '$goTransit1',

                        '$goMarketingCareer2',
                        '$goMarketingCareerName2',
                        '$goMarketingFlight2',
                        '$goOperatingCareer2',
                        '$goOperatingFlight2',
                        '$goDeparture2',
                        '$goArrival2',
                        '$goDepartureAirport2',
                        '$goArrivalAirport2',
                        '$goDepartureLocation2',
                        '$goArrivalLocation2',
                        '$goDepartureTime2',
                        '$goArrivalTime2',
                        '$goFlightDuration2',
                        '$goBookingCode2',

                        '$backMarketingCareer1',
                        '$backMarketingCareerName1',
                        '$backMarketingFlight1',
                        '$backOperatingCareer1',
                        '$backOperatingFlight1',
                        '$backDeparture1',
                        '$backArrival1',
                        '$backDepartureAirport1',
                        '$backArrivalAirport1',
                        '$backDepartureLocation1',
                        '$backArrivalLocation1',
                        '$backDepartureTime1',
                        '$backArrivalTime1',
                        '$backFlightDuration1',
                        '$backBookingCode1',

                        '$searchId',
                        '$resultId',
                        '$createdAt',
                        '$uId'

                    )";

        }
        $conn->query($sql);
    }

}

function addBookingQueue($conn, $BookingPNR, $AirlinesPNR, $UniversalPnr, $bookingInfo, $PassengerData, $saveBookingAarray){
    $SaveBookingData = $bookingInfo;

    if (!empty($BookingPNR)) {
        $BookingId = "";
        $sql1 = "SELECT id, bookingId FROM booking ORDER BY bookingId DESC LIMIT 1";
        $result = $conn->query($sql1);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $number = (int) filter_var($row["bookingId"], FILTER_SANITIZE_NUMBER_INT);
                $newnumber =  $number + 1;
                $BookingId = "STB$newnumber";
            }
        } else {
            $BookingId = "STB1000";
        }

        $AgentId = isset($SaveBookingData["agentId"])? $SaveBookingData["agentId"]:"";
        $staffId = isset($SaveBookingData["staffId"]) ? $SaveBookingData["staffId"] : "";
        $subagentId = isset($SaveBookingData['subagentId']) ? $SaveBookingData['subagentId'] : "";
        $userId = isset($SaveBookingData['userId']) ? $SaveBookingData['userId']:"";
        $System = $SaveBookingData["system"];
        $From = $SaveBookingData["from"];
        $To = $SaveBookingData["to"];
        $Airlines = $SaveBookingData["airlines"];
        $Type = $SaveBookingData["tripType"];
        $journeyType = isset($SaveBookingData["journeyType"]) ? $SaveBookingData["journeyType"] : $SaveBookingData["tripType"] ;
        $Name = strtoupper($SaveBookingData["name"]);
        $Phone = $SaveBookingData["phone"];
        $Email = $SaveBookingData["email"];
        $Pnr = $BookingPNR;
        $Pax = $SaveBookingData["pax"];
        $Refundable = $SaveBookingData["refundable"];
        $adultCount = $SaveBookingData["adultcount"];
        $childCount = $SaveBookingData["childcount"];
        $infantCount = $SaveBookingData["infantcount"];
        $adultBag = $SaveBookingData["adultbag"];
        $childBag = $SaveBookingData["childbag"];
        $infantBag = $SaveBookingData["infantbag"];
        $netCost = $SaveBookingData["netcost"];
        $subagentprice = isset($SaveBookingData["subagentprice"]) ? $SaveBookingData["subagentprice"] :'';
        $adultCostBase = $SaveBookingData["adultcostbase"];
        $childCostBase = $SaveBookingData["childcostbase"];
        $infantCostBase = $SaveBookingData["infantcostbase"];
        $adultCostTax = $SaveBookingData["adultcosttax"];
        $childCostTax = $SaveBookingData["childcosttax"];
        $infantCostTax = $SaveBookingData["infantcosttax"];
        $grossCost = $SaveBookingData["grosscost"];
        $BaseFare = $SaveBookingData["basefare"];
        $taxFare = $SaveBookingData["tax"];
        $Coupon = isset($SaveBookingData["coupon"]) ? $SaveBookingData["coupon"] :'';
        $Platform = isset($SaveBookingData["platform"]) ? $SaveBookingData["platform"] :"";
        $airlinesCode =  isset($saveBookingAarray['flightData']['career']) ? $saveBookingAarray['flightData']['career']  : $saveBookingAarray['roundData']['career'];

        //Com
        $currency = isset($saveBookingAarray['flightData']['farecurrency']) ? $saveBookingAarray['flightData']['farecurrency']  : $saveBookingAarray['roundData']['farecurrency'];
        $airlinescomref = isset($saveBookingAarray['flightData']['airlinescomref']) ? $saveBookingAarray['flightData']['airlinescomref']  : $saveBookingAarray['roundData']['airlinescomref'];
        $comissiontype = isset($saveBookingAarray['flightData']['comissiontype']) ?  $saveBookingAarray['flightData']['comissiontype']  : $saveBookingAarray['roundData']['comissiontype'];
        $comissionvalue = isset($saveBookingAarray['flightData']['comissionvalue']) ? $saveBookingAarray['flightData']['comissionvalue']  : $saveBookingAarray['roundData']['comissionvalue'];
        

        if (isset($SaveBookingData["uId"])) {
            $uId = $SaveBookingData["uId"];
        } else {
            $uId = '';
        }

        if (isset($SaveBookingData["travelDate"])) {
            $travelDate = $SaveBookingData["travelDate"];
        } else {
            $travelDate = date("Y-m-d H:i", strtotime("+6 hours"));
        }

        if (empty($SaveBookingData["timeLimit"])) {

            $JourneyDateTime = date_create($travelDate);
            $JourneyDTime = date_format($JourneyDateTime, "Y-m-d H:i");
            $diff_time = round(((strtotime($travelDate)) - strtotime(date("Y-m-d H:i"))) / 3600);

            
            $LastTicketTime = $SaveBookingData["timeLimit"];
        
            

        $DateTime = date("D d M Y h:i A");

        $dateTime = date('Y-m-d H:i:s');
        if (isset($SaveBookingData["SearchID"]) && $SaveBookingData["ResultID"]) {
            $searchId = $SaveBookingData["SearchID"];
            $resultId = $SaveBookingData["ResultID"];
        } else {
            $searchId = '';
            $resultId = '';
        }


        $couponRow = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM coupon WHERE coupon='$Coupon'"), MYSQLI_ASSOC);

        if (isset($AgentId)) {
            $row1 = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$AgentId'"), MYSQLI_ASSOC);
            if (!empty($row1)) {
                $agentEmail = $row1['email'];
                $agentName = $row1['name'];
                $companyname = $row1['company'];
                $Bonus = $row1['bonus'];
            }else{
                $companyname = '';
            }
        }
        
         //sub agent mail data
        $result = $conn->query("SELECT * FROM subagent where subagentId='$subagentId' AND agentId= '$AgentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
        
        if(!empty($result)){
            $subcompanyName = $result[0]['company'];
            $Email = $result[0]['email'];
        }else{
            $subcompanyName = '';
        }

        $staffrow2 = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `staffList` where agentId = '$AgentId' AND staffId='$staffId'"), MYSQLI_ASSOC);
        if (!empty($staffrow2)) {
            $staffName = $staffrow2['name'];
            $BookedBy = $staffrow2['name'];
        } else {
            $BookedBy = $companyname;
            $staffName = "Agent";
        }

        if (empty($staffId) && !empty($AgentId)) {
            $Message = "Dear $companyname,  you have been requested for $From to $To $Type air ticket on $DateTime, career Name: <b>$Airlines</b>. Your booking request has been accepted. Your booking time limit  $LastTicketTime. Please issue your ticket before giving time limit. Thank you for booking with Fly Far International ";
            $Booked = "Booked By: $companyname";
        } else if (!empty($staffId) && !empty($AgentId)) {
            $Message = "Dear $companyname, your staff $staffName has sent booking request $From to $To $Type  air ticket on $DateTime, Career Name: <b>$Airlines</b>. Your booking request has been accepted. Your need to immediately issue this ticket. Otherwise your booking request has been cancelled. Thank you for booking with Fly Far International";

            $Booked = "Booked By: $staffName,  $companyname";
        } else if (!empty($staffId) && !empty($AgentId) && !empty($LastTicketTime)) {

            $Message = "Dear $companyname, your staff $staffName has sent booking request $From to $To $Type  air ticket on $DateTime, Career Name: <b>$Airlines</b>. Your booking request has been accepted. Your booking time limit  $LastTicketTime. Please issue your ticket before giving time limit. Thank you for booking with Fly Far International";

            $Booked = "Booked By: $staffName,  $companyname";
        }
        if(!empty($subagentId)){
            $Message = "Dear $companyname,  your subagent $subcompanyName requested for $From to $To $Type air ticket on $DateTime, career Name: <b>$Airlines</b>. Your booking request has been accepted. Your booking time limit  $LastTicketTime. Please issue your ticket before giving time limit. Thank you for booking with Fly Far International ";
            $Booked = "Booked By: $companyname";
        }


        $createdTime = date("Y-m-d H:i:s");
        $sql = "INSERT INTO `booking` (
                          `uid`,
                          `bookingId`,
                          `userId`,
                          `agentId`,
                          `staffId`,
                          `subagentId`,
                          `email`,
                          `phone`,
                          `name`,
                          `refundable`,
                          `upPnr`,
                          `pnr`,
                          `tripType`,
                          `journeyType`,
                          `pax`,
                          `adultBag`,
                          `childBag`,
                          `infantBag`,
                          `adultCount`,
                          `childCount`,
                          `infantCount`,
                          `netCost`,
                          `subagentCost`,
                          `adultCostBase`,
                          `childCostBase`,
                          `infantCostBase`,
                          `adultCostTax`,
                          `childCostTax`,
                          `infantCostTax`,
                          `grossCost`,
                          `baseFare`,
                          `Tax`,
                          `deptFrom`,
                          `airlines`,
                          `arriveTo`,
                          `gds`,
                          `status`,
                          `coupon`,
                          `travelDate`,
                          `timeLimit`,
                          `bookedAt`,
                          `bookedBy`,
                          `searchId`,
                          `resultId`,
                          `lastUpdated`,
                          `platform`,
                          `airlinescode`,
                          `comissiontype`,
                          `comissionvalue`,
                          `airlinescomref`,
                          `currency`)

  VALUES('$uId','$BookingId','$userId','$AgentId','$staffId','$subagentId','$Email','$Phone','$Name','$Refundable','$UniversalPnr','$Pnr','$Type','$journeyType','$Pax','$adultBag','$childBag','$infantBag','$adultCount','$childCount','$infantCount',
        '$netCost','$subagentprice','$adultCostBase','$childCostBase','$infantCostBase','$adultCostTax','$childCostTax','$infantCostTax','$grossCost',
        '$BaseFare','$taxFare','$From','$Airlines','$To','$System','Hold','$Coupon','$travelDate','$LastTicketTime','$dateTime','$BookedBy','$searchId','$resultId','$createdTime','$Platform','$airlinesCode','$comissiontype','$comissionvalue','$airlinescomref','$currency')";

        if ($conn->query($sql) === true) {
            addPax($conn, $BookingPNR, $AgentId, $subagentId, $userId, $BookingId, $PassengerData);
            $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                    VALUES ('$BookingId','$AgentId','Hold','$subcompanyName','$BookedBy','$dateTime')");

//             $AgentMail = '
//                 <!DOCTYPE html>
//                 <html lang="en">
//                   <head>
//                     <meta charset="UTF-8" />
//                     <meta http-equiv="X-UA-Compatible" content="IE=edge" />
//                     <meta name="viewport" content="width=device-width, initial-scale=1.0" />
//                     <title>Deposit Request
//                 </title>
//                   </head>
//                   <body>
//                     <div
//                       class="div"
//                       style="
//                         width: 650px;
//                         height: 100vh;
//                         margin: 0 auto;
//                       "
//                     >
//                       <div
//                         style="
//                           width: 650px;
//                           height: 200px;
//                           background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
//                           border-radius: 20px 0px  20px  0px;

//                         "
//                       >
//                         <table
//                           border="0"
//                           cellpadding="0"
//                           cellspacing="0"
//                           align="center"
//                           style="
//                             border-collapse: collapse;
//                             border-spacing: 0;
//                             padding: 0;
//                             width: 650px;
//                             border-radius: 10px;

//                           "
//                         >
//                           <tr>
//                             <td
//                               align="center"
//                               valign="top"
//                               style="
//                                 border-collapse: collapse;
//                                 border-spacing: 0;
//                                 color: #000000;
//                                 font-family: sans-serif;
//                                 font-weight: bold;
//                                 font-size: 20px;
//                                 line-height: 38px;
//                                 padding-top: 30px;
//                                 padding-bottom: 10px;
//                               "
//                             >
//                               <a href="https://www.flyfarint.com/"
//                                 ><img
//                                 src="https://cdn.flyfarint.com/logo.png"
//                                   width="130px"
//                               /></a>

//                             </td>
//                           </tr>
//                         </table>

//                         <table
//                           border="0"
//                           cellpadding="0"
//                           cellspacing="0"
//                           align="center"
//                           bgcolor="white"
//                           style="
//                             border-collapse: collapse;
//                             border-spacing: 0;
//                             padding: 0;
//                             width: 550px;
//                           "
//                         >
//                           <tr>
//                             <td
//                               align="center"
//                               valign="top"
//                               style="
//                                 border-collapse: collapse;
//                                 border-spacing: 0;
//                                 color: #000000;
//                                 font-family: sans-serif;
//                                 text-align: left;
//                                 padding-left: 20px;
//                                 font-weight: bold;
//                                 font-size: 19px;
//                                 line-height: 38px;
//                                 padding-top: 20px;
//                                 background-color: white;


//                               "
//                             >
//                               Booking Request
//                             </td>
//                           </tr>
//                           <tr>
//                             <td
//                               align="center"
//                               valign="top"
//                               style="
//                                 border-collapse: collapse;
//                                 border-spacing: 0;
//                                 font-family: sans-serif;
//                                 text-align: left;
//                                 padding-left: 20px;
//                                 font-weight: bold;
//                                 padding-top: 15px;
//                                 font-size: 12px;
//                                 line-height: 18px;
//                                 color: #929090;
//                                 padding-right: 20px;
//                                 background-color: white;

//                               "
//                             >
//                              ' . $Message . '
//                             </td>
//                           </tr>

//                           <tr>
//                           <td
//                             align="center"
//                             valign="top"
//                             style="
//                               border-collapse: collapse;
//                               border-spacing: 0;
//                               color: #000000;
//                               font-family: sans-serif;
//                               text-align: left;
//                               padding-left: 20px;
//                               font-weight: bold;
//                               padding-top: 20px;
//                               font-size: 13px;
//                               line-height: 18px;
//                               color: #929090;
//                               padding-top: 20px;
//                               width: 100%;
//                               background-color: white;
//                             "
//                           >
//                           <span style="color:#003566 ;">  ' . $Booked . ' </span>
//                           </td>
//                         </tr>


//                           <tr>
//                             <td
//                               align="center"
//                               valign="top"
//                               style="
//                                 border-collapse: collapse;
//                                 border-spacing: 0;
//                                 color: #000000;
//                                 font-family: sans-serif;
//                                 text-align: left;
//                                 padding-left: 20px;
//                                 font-weight: bold;
//                                 padding-top: 20px;
//                                 font-size: 13px;
//                                 line-height: 18px;
//                                 color: #929090;
//                                 padding-top: 20px;
//                                 width: 100%;
//                               "
//                             >
//                               Booking Id:
//                               <a style="color: #003566" href="http://" target="_blank"
//                                 >' . $BookingId . '</a
//                               >
//                             </td>
//                           </tr>


//                                                                   <tr>
//                             <td
//                               align="center"
//                               valign="top"
//                               style="
//                                 border-collapse: collapse;
//                                 border-spacing: 0;
//                                 color: #000000;
//                                 font-family: sans-serif;
//                                 text-align: left;
//                                 padding-left: 20px;
//                                 font-weight: bold;
//                                 padding-top: 20px;
//                                 font-size: 13px;
//                                 line-height: 18px;
//                                 color: #929090;
//                                 padding-top: 20px;
//                                 width: 100%;
//                                 background-color: white;

//                               "
//                             >
//                                    If you have any questions, just contact us we are always happy to
//                               help you out.
//                             </td>
//                           </tr>


//                              <tr>
//                             <td
//                               align="center"
//                               valign="top"
//                               style="
//                                 border-collapse: collapse;
//                                 border-spacing: 0;
//                                 color: #000000;
//                                 font-family: sans-serif;
//                                 text-align: left;
//                                 padding-left: 20px;
//                                 font-weight: bold;
//                                 padding-top: 20px;
//                                 font-size: 13px;
//                                 line-height: 18px;
//                                 color: #929090;
//                                 padding-top: 20px;
//                                 width: 100%;
//                                 background-color: white;

//                               "
//                             >
//                                Sincerely,

//                             </td>
//                           </tr>

//                              <tr>
//                             <td
//                               align="center"
//                               valign="top"
//                               style="
//                                 border-collapse: collapse;
//                                 border-spacing: 0;
//                                 color: #000000;
//                                 font-family: sans-serif;
//                                 text-align: left;
//                                 padding-left: 20px;
//                                 font-weight: bold;
//                                 font-size: 13px;
//                                 line-height: 18px;
//                                 color: #929090;
//                                 width: 100%;
//                                 background-color: white;
//                                 padding-bottom: 20px

//                               "
//                             >
//                               Fly Far International

//                             </td>
//                           </tr>


//                           <tr>
//                             <td
//                               align="center"
//                               valign="top"
//                               style="
//                                 border-collapse: collapse;
//                                 border-spacing: 0;
//                                 color: #ffffff;
//                                 font-family: sans-serif;
//                                 text-align: center;
//                                 font-weight: 600;
//                                 font-size: 14px;
//                                 color: #ffffff;
//                                 padding-top: 15px;
//                                 background-color: #dc143c;
//                               "
//                             >
//                               Need more help?
//                             </td>
//                           </tr>

//                           <tr>
//                             <td
//                               align="center"
//                               valign="top"
//                               style="
//                                 border-collapse: collapse;
//                                 border-spacing: 0;
//                                 color: #ffffff;
//                                 font-family: sans-serif;
//                                 text-align: center;
//                                 font-size: 12px;
//                                 color: #ffffff;
//                                 padding-top: 8px;
//                                 padding-bottom: 20px;
//                                 padding-left: 30px;
//                                 padding-right: 30px;
//                                 background-color: #dc143c;


//                               "
//                             >
//                               Mail us at
//                               <a
//                                 style="color: white; font-size: 13px; text-decoration: none"
//                                 href="http://"
//                                 target="_blank"
//                                 >support@flyfarint.com
//                               </a>
//                               agency or Call us at 09606912912
//                             </td>
//                           </tr>

//                           <tr>
//                             <td
//                               valign="top"
//                               align="left"
//                               style="
//                                 border-collapse: collapse;
//                                 border-spacing: 0;
//                                 color: #000000;
//                                 font-family: sans-serif;
//                                 text-align: left;
//                                 font-weight: bold;
//                                 font-size: 12px;
//                                 line-height: 18px;
//                                 color: #929090;
//                               "
//                             >

//                             <p> <a
//                                 style="
//                                   font-weight: bold;
//                                   font-size: 12px;
//                                   line-height: 15px;
//                                   color: #222222;

//                                 "
//                                 href="https://www.flyfarint.com/terms"
//                                 >Terms & Conditions</a
//                               >
//                               <a
//                                 style="
//                                   font-weight: bold;
//                                   font-size: 12px;
//                                   line-height: 15px;
//                                   color: #222222;
//                                   padding-left: 10px;
//                                 "
//                                 href="https://www.flyfarint.com/privacy"
//                                 >Privacy Policy</a
//                               ></p>
//                             </td>
//                           </tr>
//                           <tr>
//                             <td
//                               align="center"
//                               valign="top"
//                               style="
//                                 border-collapse: collapse;
//                                 border-spacing: 0;
//                                 font-family: sans-serif;
//                                 text-align: center;
//                                 padding-left: 20px;
//                                 font-weight: bold;
//                                 font-size: 12px;
//                                 line-height: 18px;
//                                 color: #929090;
//                                 padding-right: 20px;
//                               "
//                             >
//                                 <a href="https://www.facebook.com/FlyFarInternational/ "
//                                 ><img
//                                   src="https://cdn.flyfarint.com/fb.png"
//                                   width="25px"
//                                   style="margin: 10px"
//                               /></a>
//                               <a href="http:// "
//                                 ><img
//                                   src="https://cdn.flyfarint.com/lin.png"
//                                   width="25px"
//                                   style="margin: 10px"
//                               /></a>
//                               <a href="http:// "
//                                 ><img
//                                   src="https://cdn.flyfarint.com/wapp.png "
//                                   width="25px"
//                                   style="margin: 10px"
//                               /></a>
//                             </td>
//                           </tr>

//                                     <tr>
//                             <td
//                               align="center"
//                               valign="top"
//                               style="
//                                 border-collapse: collapse;
//                                 border-spacing: 0;
//                                 color: #929090;
//                                 font-family: sans-serif;
//                                 text-align: center;
//                                 font-weight: 500;
//                                 font-size: 12px;
//                                 padding-top:5px;
//                                 padding-bottom:5px;
//                                 padding-left:10px;
//                                 padding-right: 10px;
//                               "
//                             >
//                 Ka 11/2A, Bashundhara R/A Road, Jagannathpur, Dhaka 1229
//                  </td>
//                           </tr>



//                         </table>


//                       </div>
//                     </div>
//                   </body>
//                 </html>

// ';

//             $mail = new PHPMailer();

//             try {
//                 $mail->isSMTP();
//                 $mail->Host = 'b2b.flyfarint.com';
//                 $mail->SMTPAuth = true;
//                 $mail->Username = 'booking@b2b.flyfarint.com';
//                 $mail->Password = '123Next2$';
//                 $mail->SMTPSecure = 'ssl';
//                 $mail->Port = 465;

//                 //Recipients
//                 $mail->setFrom('booking@b2b.flyfarint.com', 'Fly Far International');
//                 $mail->addAddress("$agentEmail", "AgentId : $AgentId");
//                 $mail->addCC('otaoperation@flyfarint.com');
//                 $mail->addCC('habib@flyfarint.com');
//                 $mail->addCC('afridi@flyfarint.com');

//                 $mail->isHTML(true);
//                 $mail->Subject = "Booking Request Confirmation by Fly Far International";
//                 $mail->Body = $AgentMail;

//                 if (!$mail->Send()) {
//                     echo "Mailer Error: " . $mail->ErrorInfo;
//                 }

//             } catch (Exception $e) {
//                 $response['status'] = "error";
//                 $response['message'] = "Mail Doesn't Send";
//             }

        if($userId != ""){

            //Information for Email Template
        // $data = $conn->query("SELECT `email`, `company_name`,`websitelink`, `address`,`phone`,`fb_link`,`linkedin_link`, `whatsapp_num` FROM `agent` WHERE userId='$userId'")->fetch_all(MYSQLI_ASSOC);
        // $agentEmail = $data[0]['email'];
        // $agentPhone = $data[0]['phone'];
        // $agentAddress = $data[0]['address'];
        // $agentCompany_name = $data[0]['company_name'];
        // $agentWebsiteLink = $data[0]['websitelink'];
        // $agentFbLink = $data[0]['fb_link'];
        // $agentLinkedInLink = $data[0]['linkedin_link'];
        // $agentWhatsappNum = $data[0]['whatsapp_num'];

        //     $userData = $conn->query("SELECT `name`,`email` FROM `subagent` WHERE agentId = '$AgentId' AND userId = '$userId'")->fetch_all(MYSQLI_ASSOC);
        //         $userName = $userData[0]['name'];
        //         $userEmail = $userData[0]['email'];
            
            // $AgentEmail ='
            //         <!DOCTYPE html>
            //         <html lang="en">
            //           <head>
            //             <meta charset="UTF-8" />
            //             <meta http-equiv="X-UA-Compatible" content="IE=edge" />
            //             <meta name="viewport" content="width=device-width, initial-scale=1.0" />
            //             <title>Deposit Request</title>
            //           </head>
            //           <body>
            //             <div class="div" style="width: 650px; height: 100vh; margin: 0 auto">
            //               <div style="width: 650px; height: 150px; background: #32d095">
            //                 <table
            //                   border="0"
            //                   cellpadding="0"
            //                   cellspacing="0"
            //                   align="center"
            //                   style="
            //                     border-collapse: collapse;
            //                     border-spacing: 0;
            //                     padding: 0;
            //                     width: 650px;
            //                     border-radius: 10px;
            //                   "
            //                 >
            //                   <tr>
            //                     <td
            //                       align="center"
            //                       valign="top"
            //                       style="
            //                         border-collapse: collapse;
            //                         border-spacing: 0;
            //                         color: #000000;
            //                         font-family: sans-serif;
            //                         font-weight: bold;
            //                         font-size: 20px;
            //                         line-height: 38px;
            //                         padding-top: 20px;
            //                         padding-bottom: 10px;
            //                       "
            //                     >
            //                       <a
            //                         style="
            //                           text-decoration: none;
            //                           color: #ffffff;
            //                           font-family: sans-serif;
            //                           font-size: 25px;
            //                           padding-top: 20px;
            //                         "
            //                         href="https://www.'.$agentWebsiteLink.'/"
            //                       >
            //                         '.$agentCompany_name.'</a
            //                       >
            //                     </td>
            //                   </tr>
            //                 </table>
                    
            //                 <table
            //                   border="0"
            //                   cellpadding="0"
            //                   cellspacing="0"
            //                   align="center"
            //                   bgcolor="white"
            //                   style="
            //                     border-collapse: collapse;
            //                     border-spacing: 0;
            //                     padding: 0;
            //                     width: 550px;
            //                   "
            //                 >
            //                   <tr>
            //                     <td
            //                       align="center"
            //                       valign="top"
            //                       style="
            //                         border-collapse: collapse;
            //                         border-spacing: 0;
            //                         color: #000000;
            //                         font-family: sans-serif;
            //                         text-align: left;
            //                         padding-left: 20px;
            //                         font-weight: bold;
            //                         font-size: 19px;
            //                         line-height: 38px;
            //                         padding-top: 10px;
            //                         background-color: white;
            //                       "
            //                     >
            //                     Booking Confirmation
            //                     </td>
            //                   </tr>
            //                   <tr>
            //                     <td
            //                       align="center"
            //                       valign="top"
            //                       style="
            //                         border-collapse: collapse;
            //                         border-spacing: 0;
            //                         font-family: sans-serif;
            //                         text-align: left;
            //                         padding-left: 20px;
            //                         font-weight: bold;
            //                         padding-top: 15px;
            //                         font-size: 12px;
            //                         line-height: 18px;
            //                         color: #929090;
            //                         padding-right: 20px;
            //                         background-color: white;
            //                       "
            //                     >
            //                     Dear '.$userName.', Your New Booking Request has been placed, please issue your ticket before time limit, otherwise your ticket will be cancel autometically.
            //                     </td>
            //                   </tr>
                    
            //                   <tr>
            //                     <td
            //                       align="center"
            //                       valign="top"
            //                       style="
            //                         border-collapse: collapse;
            //                         border-spacing: 0;
            //                         font-family: sans-serif;
            //                         text-align: left;
            //                         padding-left: 20px;
            //                         font-weight: bold;
            //                         padding-top: 15px;
            //                         font-size: 12px;
            //                         line-height: 18px;
            //                         color: #525371;
            //                         padding-right: 20px;
            //                         background-color: white;
            //                       "
            //                     >
            //                       Booking ID: <span>'.$BookingId.'</span>
            //                     </td>
            //                   </tr>
            //                   <tr>
            //                     <td
            //                       align="center"
            //                       valign="top"
            //                       style="
            //                         border-collapse: collapse;
            //                         border-spacing: 0;
            //                         color: #000000;
            //                         font-family: sans-serif;
            //                         text-align: left;
            //                         padding-left: 20px;
            //                         font-weight: bold;
            //                         padding-top: 20px;
            //                         font-size: 13px;
            //                         line-height: 18px;
            //                         color: #929090;
            //                         padding-top: 20px;
            //                         width: 100%;
            //                         background-color: white;
            //                       "
            //                     >
            //                       If you have any questions, just contact us we are always happy to
            //                       help you out.
            //                     </td>
            //                   </tr>
                    
            //                   <tr>
            //                     <td
            //                       align="center"
            //                       valign="top"
            //                       style="
            //                         border-collapse: collapse;
            //                         border-spacing: 0;
            //                         color: #000000;
            //                         font-family: sans-serif;
            //                         text-align: left;
            //                         padding-left: 20px;
            //                         font-weight: bold;
            //                         padding-top: 20px;
            //                         font-size: 13px;
            //                         line-height: 18px;
            //                         color: #929090;
            //                         padding-top: 20px;
            //                         width: 100%;
            //                         background-color: white;
            //                       "
            //                     >
            //                       Sincerely,
            //                     </td>
            //                   </tr>
                    
            //                   <tr>
            //                     <td
            //                       align="center"
            //                       valign="top"
            //                       style="
            //                         border-collapse: collapse;
            //                         border-spacing: 0;
            //                         color: #000000;
            //                         font-family: sans-serif;
            //                         text-align: left;
            //                         padding-left: 20px;
            //                         font-weight: bold;
            //                         font-size: 13px;
            //                         line-height: 18px;
            //                         color: #929090;
            //                         width: 100%;
            //                         background-color: white;
            //                         padding-bottom: 20px;
            //                       "
            //                     >
            //                      '.$agentCompany_name.'
            //                     </td>
            //                   </tr>
                    
            //                   <tr>
            //                     <td
            //                       align="center"
            //                       valign="top"
            //                       style="
            //                         border-collapse: collapse;
            //                         border-spacing: 0;
            //                         color: #ffffff;
            //                         font-family: sans-serif;
            //                         text-align: center;
            //                         font-weight: 600;
            //                         font-size: 14px;
            //                         color: #ffffff;
            //                         padding-top: 15px;
            //                         background-color: #525371;
            //                       "
            //                     >
            //                       Need more help?
            //                     </td>
            //                   </tr>
                    
            //                   <tr>
            //                     <td
            //                       align="center"
            //                       valign="top"
            //                       style="
            //                         border-collapse: collapse;
            //                         border-spacing: 0;
            //                         color: #ffffff;
            //                         font-family: sans-serif;
            //                         text-align: center;
            //                         font-size: 12px;
            //                         color: #ffffff;
            //                         padding-top: 8px;
            //                         padding-bottom: 20px;
            //                         padding-left: 30px;
            //                         padding-right: 30px;
            //                         background-color: #525371;
            //                       "
            //                     >
            //                       Mail us at
            //                       <a
            //                         style="color: white; font-size: 13px; text-decoration: none"
            //                         href="http://"
            //                         target="_blank"
            //                         >'.$agentEmail.'
            //                       </a>
            //                       agency or Call us at '.$agentPhone.'
            //                     </td>
            //                   </tr>
                    
            //                   <tr>
            //                     <td
            //                       valign="top"
            //                       align="left"
            //                       style="
            //                         border-collapse: collapse;
            //                         border-spacing: 0;
            //                         color: #000000;
            //                         font-family: sans-serif;
            //                         text-align: left;
            //                         font-weight: bold;
            //                         font-size: 12px;
            //                         line-height: 18px;
            //                         color: #929090;
            //                       "
            //                     >
            //                       <p>
            //                         <a
            //                           style="
            //                             font-weight: bold;
            //                             font-size: 12px;
            //                             line-height: 15px;
            //                             color: #222222;
            //                           "
            //                           href="https://www.'.$agentWebsiteLink.'/termsandcondition"
            //                           >Tearms & Conditions</a
            //                         >
            //                         <a
            //                           style="
            //                             font-weight: bold;
            //                             font-size: 12px;
            //                             line-height: 15px;
            //                             color: #222222;
            //                             padding-left: 10px;
            //                           "
            //                           href="https://www.'.$agentWebsiteLink.'/privacypolicy"
            //                           >Privacy Policy</a
            //                         >
            //                       </p>
            //                     </td>
            //                   </tr>
            //                   <tr>
            //                     <td
            //                       align="center"
            //                       valign="top"
            //                       style="
            //                         border-collapse: collapse;
            //                         border-spacing: 0;
            //                         font-family: sans-serif;
            //                         text-align: center;
            //                         padding-left: 20px;
            //                         font-weight: bold;
            //                         font-size: 12px;
            //                         line-height: 18px;
            //                         color: #929090;
            //                         padding-right: 20px;
            //                       "
            //                     >
            //                       <a href="'.$agentFbLink.' "
            //                         ><img
            //                           src="https://cdn.flyfarint.com/fb.png"
            //                           width="25px"
            //                           style="margin: 10px"
            //                       /></a>
            //                       <a href="'.$agentLinkedInLink.' "
            //                         ><img
            //                           src="https://cdn.flyfarint.com/lin.png"
            //                           width="25px"
            //                           style="margin: 10px"
            //                       /></a>
            //                       <a href="'.$agentWhatsappNum.' "
            //                         ><img
            //                           src="https://cdn.flyfarint.com/wapp.png "
            //                           width="25px"
            //                           style="margin: 10px"
            //                       /></a>
            //                     </td>
            //                   </tr>
                    
            //                   <tr>
            //                     <td
            //                       align="center"
            //                       valign="top"
            //                       style="
            //                         border-collapse: collapse;
            //                         border-spacing: 0;
            //                         color: #929090;
            //                         font-family: sans-serif;
            //                         text-align: center;
            //                         font-weight: 500;
            //                         font-size: 12px;
            //                         padding-top: 5px;
            //                         padding-bottom: 5px;
            //                         padding-left: 10px;
            //                         padding-right: 10px;
            //                       "
            //                     >
            //                       '.$agentAddress.'
            //                     </td>
            //                   </tr>
            //                 </table>
            //               </div>
            //             </div>
            //           </body>
            //         </html>        
            //         ';
      
            //         $mail1 = new PHPMailer();
      
            // try {
            //     $mail1->isSMTP();
            //     $mail1->Host = 'b2b.flyfarint.com';
            //     $mail1->SMTPAuth = true;
            //     $mail1->Username = 'bookingwl@mailservice.center';
            //     $mail1->Password = '123Next2$';
            //     $mail1->SMTPSecure = 'ssl';
            //     $mail1->Port = 465;
      
            //     //Recipients
            //     $mail1->setFrom("bookingwl@mailservice.center", $agentCompany_name);
            //     $mail1->addAddress("$userEmail", "AgentId : $AgentId");
            //     $mail1->addCC('habib@flyfarint.com');
            //     $mail1->addCC('afridi@flyfarint.com');
                
      
            //     $mail1->isHTML(true);
            //     $mail1->Subject = "New Booking Request Confirmation by $userName";
            //     $mail1->Body = $AgentEmail;
                
            //     //print_r($mail);
            //     if (!$mail1->Send()) {
            //         echo "Mailer Error: " . $mail1->ErrorInfo;
            //     }
      
            // } catch (Exception $e) {
      
            // } 
        }
            
            $response['status'] = "success";
                    $response['BookingId'] = "$BookingId";
                    $response['BookingPNR'] = $BookingPNR;
                    $response['platform'] = $Platform;
                    $response['message'] = "Booking Successfully";
                    echo json_encode($response);




        }
    }

}

function addPax($conn, $BookingPNR, $agentId, $subagentId,$userId, $bookingId, $PassengerData){
    $_POST = $PassengerData;

    $adult = $_POST['adultCount'];
    $child = $_POST['childCount'];
    $infants = $_POST['infantCount']; 
    $BookingId = $bookingId;
    $createdTimer = date('Y-m-d H:i:s');

    if ($adult > 0 && $child > 0 && $infants > 0) {

        for ($x = 0; $x < $adult; $x++) {

            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY id DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "STP$number";
                }
            } else {
                $paxId = "STP1000";
            }

            ${'afName' . $x} = strtoupper($_POST['adult'][$x]["afName"]);
            ${'alName' . $x} = strtoupper($_POST['adult'][$x]["alName"]);
            ${'agender' . $x} = $_POST['adult'][$x]["agender"];
            ${'adob' . $x} = $_POST['adult'][$x]["adob"];
            ${'apassNo' . $x} = strtoupper($_POST['adult'][$x]["apassNo"]);
            ${'apassEx' . $x} = $_POST['adult'][$x]["apassEx"];
            ${'apassNation' . $x} = strtoupper($_POST['adult'][$x]["apassNation"]);

            ${'sql' . $x} = "INSERT INTO `passengers`(
                            `paxId`,
                            `agentId`,
                            `subagentId`,
                            `userId`,
                            `bookingId`,
                            `fName`,
                            `lName`,
                            `dob`,
                            `gender`,
                            `type`,
                            `passNation`,
                            `passNo`,
                            `passEx`,
                            `created`
                        )
            VALUES('$paxId','$agentId','$subagentId','$userId', '$BookingId','${'afName' . $x}','${'alName' . $x}','${'adob' . $x}','${'agender' . $x}','ADT','${'apassNation' . $x}','${'apassNo' . $x}','${'apassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});

        }

        for ($x = 0; $x < $child; $x++) {

            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY id DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "STP$number";
                }
            } else {
                $paxId = "STP1000";
            }

            ${'cfName' . $x} = strtoupper($_POST['child'][$x]["cfName"]);
            ${'clName' . $x} = strtoupper($_POST['child'][$x]["clName"]);
            ${'cgender' . $x} = $_POST['child'][$x]["cgender"];
            ${'cdob' . $x} = $_POST['child'][$x]["cdob"];
            ${'cpassNo' . $x} = strtoupper($_POST['child'][$x]["cpassNo"]);
            ${'cpassEx' . $x} = $_POST['child'][$x]["cpassEx"];
            ${'cpassNation' . $x} = strtoupper($_POST['child'][$x]["cpassNation"]);

            ${'sql' . $x} = "INSERT INTO `passengers`(
                            `paxId`,`agentId`,`subagentId`,`userId`,`bookingId`,`fName`,`lName`, `dob`,`gender`,`type`,`passNation`,`passNo`,`passEx`,`created`)
                    VALUES('$paxId','$agentId','$subagentId','$userId','$BookingId','${'cfName' . $x}','${'clName' . $x}','${'cdob' . $x}','${'cgender' . $x}','CNN','${'cpassNation' . $x}',
                        '${'cpassNo' . $x}','${'cpassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});
        }

        for ($x = 0; $x < $infants; $x++) {

            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY id DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "STP$number";
                }
            } else {
                $paxId = "STP1000";
            }

            ${'ifName' . $x} = strtoupper($_POST['infant'][$x]["ifName"]);
            ${'ilName' . $x} = strtoupper($_POST['infant'][$x]["ilName"]);
            ${'igender' . $x} = $_POST['infant'][$x]["igender"];
            ${'idob' . $x} = $_POST['infant'][$x]["idob"];
            ${'ipassNo' . $x} = strtoupper($_POST['infant'][$x]["ipassNo"]);
            ${'ipassEx' . $x} = $_POST['infant'][$x]["ipassEx"];
            ${'ipassNation' . $x} = strtoupper($_POST['infant'][$x]["ipassNation"]);

            ${'sql' . $x} = "INSERT INTO `passengers`(
                            `paxId`,`agentId`,`subagentId`,`userId`,`bookingId`,`fName`,`lName`,`dob`,`gender`,`type`,`passNation`,`passNo`,`passEx`,`created`)
                    VALUES('$paxId','$agentId','$subagentId','$userId','$BookingId','${'ifName' . $x}','${'ilName' . $x}','${'idob' . $x}','${'igender' . $x}','INF','${'ipassNation' . $x}',
                        '${'ipassNo' . $x}','${'ipassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});
        }

    } else if ($adult > 0 && $child > 0) {

        for ($x = 0; $x < $adult; $x++) {
            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY id DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "STP$number";
                }
            } else {
                $paxId = "STP1000";
            }

            ${'afName' . $x} = strtoupper($_POST['adult'][$x]["afName"]);
            ${'alName' . $x} = strtoupper($_POST['adult'][$x]["alName"]);
            ${'agender' . $x} = $_POST['adult'][$x]["agender"];
            ${'adob' . $x} = $_POST['adult'][$x]["adob"];
            ${'apassNo' . $x} = strtoupper($_POST['adult'][$x]["apassNo"]);
            ${'apassEx' . $x} = $_POST['adult'][$x]["apassEx"];
            ${'apassNation' . $x} = strtoupper($_POST['adult'][$x]["apassNation"]);

            ${'sql' . $x} = "INSERT INTO `passengers`(
                `paxId`,
                            `agentId`,
                            `subagentId`,
                            `userId`,
                            `bookingId`,
                            `fName`,
                            `lName`,
                            `dob`,
                            `gender`,
                            `type`,
                            `passNation`,
                            `passNo`,
                            `passEx`,
                            `created`

                        )
                VALUES('$paxId','$agentId','$subagentId','$userId','$BookingId','${'afName' . $x}','${'alName' . $x}','${'adob' . $x}','${'agender' . $x}','ADT','${'apassNation' . $x}',
                        '${'apassNo' . $x}','${'apassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});

        }

        for ($x = 0; $x < $child; $x++) {

            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY id DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "STP$number";
                }
            } else {
                $paxId = "STP1000";
            }

            ${'cfName' . $x} = strtoupper($_POST['child'][$x]["cfName"]);
            ${'clName' . $x} = strtoupper($_POST['child'][$x]["clName"]);
            ${'cgender' . $x} = $_POST['child'][$x]["cgender"];
            ${'cdob' . $x} = $_POST['child'][$x]["cdob"];
            ${'cpassNo' . $x} = strtoupper($_POST['child'][$x]["cpassNo"]);
            ${'cpassEx' . $x} = $_POST['child'][$x]["cpassEx"];
            ${'cpassNation' . $x} = strtoupper($_POST['child'][$x]["cpassNation"]);

            ${'sql' . $x} = "INSERT INTO `passengers`(
                            `paxId`,
                            `agentId`,
                            `subagentId`,
                            `userId`,
                            `bookingId`,
                            `fName`,
                            `lName`,
                            `dob`,
                            `gender`,
                            `type`,
                            `passNation`,
                            `passNo`,
                            `passEx`,
                            `created`
                        )
                    VALUES('$paxId','$agentId','$subagentId','$userId','$BookingId','${'cfName' . $x}','${'clName' . $x}','${'cdob' . $x}','${'cgender' . $x}','CNN','${'cpassNation' . $x}',
                        '${'cpassNo' . $x}','${'cpassEx' . $x}','$createdTimer')";

            if ($conn->query(${'sql' . $x}) === true) {
                $response['status'] = "success";
                $response['message'] = "Traveler Added Successfully";
            } else {
                $response['status'] = "error";
                $response['message'] = "Traveler Added Failed";
            }

        }

    } else if ($adult > 0 && $infants > 0) {

        for ($x = 0; $x < $adult; $x++) {
            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY id DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "STP$number";
                }
            } else {
                $paxId = "STP1000";
            }

            ${'afName' . $x} = strtoupper($_POST['adult'][$x]["afName"]);
            ${'alName' . $x} = strtoupper($_POST['adult'][$x]["alName"]);
            ${'agender' . $x} = $_POST['adult'][$x]["agender"];
            ${'adob' . $x} = $_POST['adult'][$x]["adob"];
            ${'apassNo' . $x} = strtoupper($_POST['adult'][$x]["apassNo"]);
            ${'apassEx' . $x} = $_POST['adult'][$x]["apassEx"];
            ${'apassNation' . $x} = strtoupper($_POST['adult'][$x]["apassNation"]);

            ${'sql' . $x} = "INSERT INTO `passengers`(
                            `paxId`,
                            `agentId`,
                            `subagentId`,
                            `userId`,
                            `bookingId`,
                            `fName`,
                            `lName`,
                            `dob`,
                            `gender`,
                            `type`,
                            `passNation`,
                            `passNo`,
                            `passEx`,
                            `created`
                        )
                    VALUES('$paxId','$agentId','$subagentId','$userId','$BookingId','${'afName' . $x}','${'alName' . $x}','${'adob' . $x}','${'agender' . $x}','ADT','${'apassNation' . $x}',
                        '${'apassNo' . $x}','${'apassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});

        }

        for ($x = 0; $x < $infants; $x++) {

            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY id DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "STP$number";
                }
            } else {
                $paxId = "STP1000";
            }

            ${'ifName' . $x} = strtoupper($_POST['infant'][$x]["ifName"]);
            ${'ilName' . $x} = strtoupper($_POST['infant'][$x]["ilName"]);
            ${'igender' . $x} = $_POST['infant'][$x]["igender"];
            ${'idob' . $x} = $_POST['infant'][$x]["idob"];
            ${'ipassNo' . $x} = strtoupper($_POST['infant'][$x]["ipassNo"]);
            ${'ipassEx' . $x} = $_POST['infant'][$x]["ipassEx"];
            ${'ipassNation' . $x} = strtoupper($_POST['infant'][$x]["ipassNation"]);

            ${'sql' . $x} = "INSERT INTO `passengers`(
                            `paxId`,
                            `agentId`,
                            `subagentId`,
                            `userId`,
                            `bookingId`,
                            `fName`,
                            `lName`,
                            `dob`,
                            `gender`,
                            `type`,
                            `passNation`,
                            `passNo`,
                            `passEx`,
                            `created`
                        )
                    VALUES('$paxId','$agentId','$subagentId','$userId','$BookingId','${'ifName' . $x}','${'ilName' . $x}','${'idob' . $x}','${'igender' . $x}','INF','${'ipassNation' . $x}',
                        '${'ipassNo' . $x}','${'ipassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});

        }

    } else if ($adult > 0) {
        for ($x = 0; $x < $adult; $x++) {
            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY id DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "STP$number";
                }
            } else {
                $paxId = "STP1000";
            }

            ${'afName' . $x} = strtoupper($_POST['adult'][$x]["afName"]);
            ${'alName' . $x} = strtoupper($_POST['adult'][$x]["alName"]);
            ${'agender' . $x} = $_POST['adult'][$x]["agender"];
            ${'adob' . $x} = $_POST['adult'][$x]["adob"];
            ${'apassNo' . $x} = strtoupper($_POST['adult'][$x]["apassNo"]);
            ${'apassEx' . $x} = $_POST['adult'][$x]["apassEx"];
            ${'apassNation' . $x} = strtoupper($_POST['adult'][$x]["apassNation"]);

            ${'sql' . $x} = "INSERT INTO `passengers`(
                        `paxId`,
                        `agentId`,
                        `subagentId`,
                        `userId`,
                        `bookingId`,
                        `fName`,
                        `lName`,
                        `dob`,
                        `gender`,
                        `type`,
                        `passNation`,
                        `passNo`,
                        `passEx`,
                        `created`
                    )
                VALUES('$paxId','$agentId','$subagentId','$userId','$BookingId','${'afName' . $x}','${'alName' . $x}','${'adob' . $x}','${'agender' . $x}','ADT','${'apassNation' . $x}',
                    '${'apassNo' . $x}','${'apassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});

        }

    }

}