<?php

include '../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require "../vendor/autoload.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_POST = json_decode(file_get_contents('php://input'), true);
    $PassengerData = $_POST['flightPassengerData'];
    $bookingInfo = $_POST['bookingInfo'];
    $saveBookingAarray = $_POST['saveBooking'];
    $gdsSystem = $_POST['system'];
    $agentId = $_POST['agentId'];
    $subagentId = isset($_POST['subagentId']) ? $_POST['subagentId'] : "";

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
                echo ${'agender' . $x};
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
            ${'age' . $x} = ${'AgeCount' . $x}->m;
            ${'iAge' . $x} = str_pad(${'age' . $x}, 2, '0', STR_PAD_LEFT);

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
            ${'age' . $x} = ${'AgeCount' . $x}->y;
            ${'iAge' . $x} = str_pad(${'age' . $x}, 2, '0', STR_PAD_LEFT);

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

        //print_r($flightData = $saveBookingAarray['flightData']);

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

                $FlightSegment = '{
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
                        }';

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

                $FlightSegment = '{
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
                            }';

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

                $FlightSegment = '{
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
                            }';
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

                $FlightSegment = '{
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
                            }';

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

                $FlightSegment = '{
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
                            }';

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

                $FlightSegment = '{
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
                            }';
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

                $FlightSegment = '{
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
                            }';

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

                $FlightSegment = '{
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
                            }';

            }
        } 

        $PersonFinal = json_encode($AllPerson);
        $AdvPassengerFinal = json_encode($AdvancePassnger);
        $SSRFinal = json_encode($AllSsr);

        $Request = '{
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
                            "FlightSegment": [' . $FlightSegment . ']
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

        try{

            $client_id= base64_encode("V1:396724:FD3K:AA");
            $client_secret = base64_encode("FlWy967"); //prod
            
            $token = base64_encode($client_id.":".$client_secret);
            
            $curl = curl_init();
            
            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://api.platform.sabre.com/v2/auth/token',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>'grant_type=client_credentials',
              CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded',
                "Authorization: Basic $token"
              ),
            ));
            $Tokenres = curl_exec($curl);
            curl_close($curl);
            $resToken = json_decode($Tokenres, true);
            $access_token = $resToken['access_token'];
    
            //echo $access_token;
    
        }catch (Exception $e){ 
            
        }

        //Curl start
        $curl = curl_init();

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

        $response = curl_exec($curl);
       // echo $response;

        curl_close($curl);

        $result = json_decode($response, true);

        //print_r($result);

        if (isset($result['CreatePassengerNameRecordRS']['ItineraryRef']['ID'])) {
            $BookingPNR = $result['CreatePassengerNameRecordRS']['ItineraryRef']['ID'];
            saveBooking($conn, $BookingPNR, $saveBookingAarray);
            addBookingQueue($conn, $BookingPNR, $bookingInfo, $PassengerData);

        } else if (isset($result['CreatePassengerNameRecordRS']['ApplicationResults']['Error'])) {
            $resResult = $result['CreatePassengerNameRecordRS']['ApplicationResults']['Error'][0]['SystemSpecificResults'];
            $resError = $result['CreatePassengerNameRecordRS']['ApplicationResults']['Warning'][0]['SystemSpecificResults'];
            $response1['status'] = "error";
            $response1['message'] = "Booking Failed";
            $response1['result'] = $resResult;
            $response1['warning'] = $resError;

            echo json_encode($response1);
            exit();
        } else {
            $response1['status'] = "error";
            $response1['message'] = "Booking Failed";
            echo json_encode($response1);
            exit();
        }

    } else if ($gdsSystem == 'FlyHub') {

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
                $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                        $number = (int) $outputString + 1;
                        $paxId = "FWP$number";
                    }
                } else {
                    $paxId = "FWP1000";
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

        curl_setopt_array(
            $curlFlyHubPreBooking,
            array(
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
                    "Authorization: Bearer $FlyhubToken",
                ),
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

            curl_setopt_array(
                $curlFlyHubBooking,
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
                        "Authorization: Bearer $FlyhubToken",
                    ),
                )
            );

            $flyhubresponse = curl_exec($curlFlyHubBooking);

            curl_close($curlFlyHubBooking);

            $flyhubResult = json_decode($flyhubresponse, true);

            // print_r($flyhubResult);

            //$status = $flyhubResult['Error'];

            if (isset($flyhubResult['Error'])) {
                $FlyHubRes['status'] = "error";
                $FlyHubRes['message'] = $flyhubResult['Error']['ErrorMessage'];
                echo json_encode($FlyHubRes);
                exit();
            } else {
                if (isset($flyhubResult['BookingID'])) {
                    $BookingPNR = $flyhubResult['BookingID'];
                    saveBooking($conn, $BookingPNR, $saveBookingAarray);
                    addBookingQueue($conn, $BookingPNR, $bookingInfo, $PassengerData);
                }

            }

        }
    }
}

function saveBooking($conn, $BookingPNR, $saveBookingAarray)
{
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

function addBookingQueue($conn, $BookingPNR, $bookingInfo, $PassengerData)
{
    $_POST = $bookingInfo;

    if (!empty($BookingPNR)) {
        $BookingId = "";
        $sql1 = "SELECT * FROM booking ORDER BY bookingId DESC LIMIT 1";
        $result = $conn->query($sql1);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["bookingId"]);
                $number = (int) $outputString + 1;
                $BookingId = "FWB$number";
            }
        } else {
            $BookingId = "FWB1000";
        } 

        $agentId = $_POST["agentId"];
        $staffId = isset($_POST["staffId"]) ? $_POST["staffId"] : "";
        $subagentId = isset($_POST['subagentId']) ? $_POST['subagentId'] : "";
        $System = $_POST["system"];
        $From = $_POST["from"];
        $To = $_POST["to"];
        $Airlines = $_POST["airlines"];
        $Type = $_POST["tripType"];
        $journeyType = $_POST["journeyType"];
        $Name = strtoupper($_POST["name"]);
        $Phone = $_POST["phone"];
        $Email = $_POST["email"];
        $Pnr = $BookingPNR;
        $Pax = $_POST["pax"];
        $Refundable = $_POST["refundable"];
        $adultCount = $_POST["adultcount"];
        $childCount = $_POST["childcount"];
        $infantCount = $_POST["infantcount"];
        $adultBag = $_POST["adultbag"];
        $childBag = $_POST["childbag"];
        $infantBag = $_POST["infantbag"];
        $netCost = $_POST["netcost"];
        $subagentprice = $_POST["subagentprice"];
        $adultCostBase = $_POST["adultcostbase"];
        $childCostBase = $_POST["childcostbase"];
        $infantCostBase = $_POST["infantcostbase"];
        $adultCostTax = $_POST["adultcosttax"];
        $childCostTax = $_POST["childcosttax"];
        $infantCostTax = $_POST["infantcosttax"];
        $grossCost = $_POST["grosscost"];
        $BaseFare = $_POST["basefare"];
        $taxFare = $_POST["tax"];
        $Coupon = $_POST["coupon"];

        if (isset($_POST["uId"])) {
            $uId = $_POST["uId"];
        } else {
            $uId = '';
        }

        if (isset($_POST["travelDate"])) {
            $travelDate = $_POST["travelDate"];
        } else {
            $travelDate = date("Y-m-d H:i", strtotime("+6 hours"));
        }

        if (empty($_POST["timeLimit"])) {

            $JourneyDateTime = date_create($travelDate);
            $JourneyDTime = date_format($JourneyDateTime, "Y-m-d H:i");
            $diff_time = round(((strtotime($travelDate)) - strtotime(date("Y-m-d H:i"))) / 3600);

            if ($diff_time > 146) {
                $newTimeLimit = date("Y-m-d H:i", strtotime("+24 hours"));
            } else if ($diff_time > 122 && $diff_time < 146) {
                $newTimeLimit = date("Y-m-d H:i", strtotime("+22 hours"));
            } else if ($diff_time > 98 && $diff_time < 122) {
                $newTimeLimit = date("Y-m-d H:i", strtotime("+18 hours"));
            } else if ($diff_time > 84 && $diff_time < 98) {
                $newTimeLimit = date("Y-m-d H:i", strtotime("+16 hours"));
            } else if ($diff_time > 84 && $diff_time < 98) {
                $newTimeLimit = date("Y-m-d H:i", strtotime("+12 hours"));
            } else if ($diff_time > 72 && $diff_time < 84) {
                $newTimeLimit = date("Y-m-d H:i", strtotime("+6 hours"));
            } else if ($diff_time > 24 && $diff_time < 72) {
                $newTimeLimit = date("Y-m-d H:i", strtotime("+5 hours"));
            } else if ($diff_time > 12 && $diff_time < 24) {
                $newTimeLimit = date("Y-m-d H:i", strtotime("+3 hours"));
            } else if ($diff_time > 6 && $diff_time < 12) {
                $newTimeLimit = date("Y-m-d H:i", strtotime("+60 minutes"));
            } else if ($diff_time > 4 && $diff_time < 6) {
                $newTimeLimit = date("Y-m-d H:i", strtotime("+20 minutes"));
            } else if ($diff_time > 2 && $diff_time < 4) {
                $newTimeLimit = date("Y-m-d H:i", strtotime("+10 minutes"));
            } else if ($diff_time < 2) {
                $newTimeLimit = date("Y-m-d H:i", strtotime("+5 minutes"));
            }

            $LastTicketTime = $newTimeLimit;

        } else {
            $LastTicketTime = $_POST["timeLimit"];
        }

        $DateTime = date("D d M Y h:i A");

        $dateTime = date('Y-m-d H:i:s');
        if (isset($_POST["SearchID"]) && $_POST["ResultID"]) {
            $searchId = $_POST["SearchID"];
            $resultId = $_POST["ResultID"];
        } else {
            $searchId = '';
            $resultId = '';
        }

        $couponSql = mysqli_query($conn, "SELECT * FROM coupon WHERE coupon='$Coupon'");
        $couponRow = mysqli_fetch_array($couponSql, MYSQLI_ASSOC);

        if (isset($agentId)) {
            $sql1 = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
            $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

            if (!empty($row1)) {
                $agentEmail = $row1['email'];
                $companyname = $row1['company'];
                $Bonus = $row1['bonus'];
            }
        }

        $staffsql2 = mysqli_query($conn, "SELECT * FROM `staffList` where agentId = '$agentId' AND staffId='$staffId'");
        $staffrow2 = mysqli_fetch_array($staffsql2, MYSQLI_ASSOC);
        if (!empty($staffrow2)) {
            $staffName = $staffrow2['name'];
            $BookedBy = $staffrow2['name'];
        } else {
            $BookedBy = "Agent";
        }

        if (empty($staffId) && !empty($agentId)) {
            $Message = "Dear $companyname,  you have been requested for $From to $To $Type air ticket on $DateTime, career Name: <b>$Airlines</b>. Your booking request has been accepted. Your booking time limit  $LastTicketTime. Please issue your ticket before giving time limit. Thank you for booking with Flyway International ";
            $Booked = "Booked By: $companyname";
        } else if (!empty($staffId) && !empty($agentId)) {
            $Message = "Dear $companyname, your staff $staffName has sent booking request $From to $To $Type  air ticket on $DateTime, Career Name: <b>$Airlines</b>. Your booking request has been accepted. Your need to immediately issue this ticket. Otherwise your booking request has been cancelled. Thank you for booking with Flyway International";

            $Booked = "Booked By: $staffName,  $companyname";
        } else if (!empty($staffId) && !empty($agentId) && !empty($LastTicketTime)) {

            $Message = "Dear $companyname, your staff $staffName has sent booking request $From to $To $Type  air ticket on $DateTime, Career Name: <b>$Airlines</b>. Your booking request has been accepted. Your booking time limit  $LastTicketTime. Please issue your ticket before giving time limit. Thank you for booking with Flyway International";

            $Booked = "Booked By: $staffName,  $companyname";
        }

        $createdTime = date("Y-m-d H:i:s");

        $sql = "INSERT INTO `booking`(
                          `uid`,
                          `bookingId`,
                          `agentId`,
                          `staffId`,
                          `subagentId`,
                          `email`,
                          `phone`,
                          `name`,
                          `refundable`,
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
                          `lastUpdated`)

  VALUES('$uId','$BookingId','$agentId','$staffId','$subagentId','$Email','$Phone','$Name','$Refundable','$Pnr','$Type','$journeyType','$Pax','$adultBag','$childBag','$infantBag','$adultCount','$childCount','$infantCount',
        '$netCost','$subagentprice','$adultCostBase','$childCostBase','$infantCostBase','$adultCostTax','$childCostTax','$infantCostTax','$grossCost',
        '$BaseFare','$taxFare','$From','$Airlines','$To','$System','Hold','$Coupon','$travelDate','$LastTicketTime','$dateTime','$BookedBy','$searchId','$resultId','$createdTime')";

        if ($conn->query($sql) === true) {
            addPax($conn, $BookingPNR, $agentId, $subagentId, $BookingId, $PassengerData);
            $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`actionBy`, `actionAt`)
                            VALUES ('$BookingId','$agentId','Hold','$BookedBy','$dateTime')");

                    $response['status'] = "success";
                    $response['BookingId'] = "$BookingId";
                    $response['message'] = "Booking Successfully";

        }
    }else{
                    $response['status'] = "error";
                    $response['message'] = "Booking Failed";
    }

    echo json_encode($response);
}

function addPax($conn, $BookingPNR, $agentId, $subagentId, $bookingId, $PassengerData)
{
    $_POST = $PassengerData;

    $adult = $_POST['adultCount'];
    $child = $_POST['childCount']; 
    $infants = $_POST['infantCount'];
    $BookingId = $bookingId;
    $createdTimer = date('Y-m-d H:i:s');

    if ($adult > 0 && $child > 0 && $infants > 0) {

        for ($x = 0; $x < $adult; $x++) {

            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "FWP$number";
                }
            } else {
                $paxId = "FWP1000";
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
                    VALUES('$paxId','$agentId','$subagentId','$BookingId','${'afName' . $x}','${'alName' . $x}','${'adob' . $x}','${'agender' . $x}','ADT','${'apassNation' . $x}',
                        '${'apassNo' . $x}','${'apassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});

        }

        for ($x = 0; $x < $child; $x++) {

            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "FWP$number";
                }
            } else {
                $paxId = "FWP1000";
            }

            ${'cfName' . $x} = strtoupper($_POST['child'][$x]["cfName"]);
            ${'clName' . $x} = strtoupper($_POST['child'][$x]["clName"]);
            ${'cgender' . $x} = $_POST['child'][$x]["cgender"];
            ${'cdob' . $x} = $_POST['child'][$x]["cdob"];
            ${'cpassNo' . $x} = strtoupper($_POST['child'][$x]["cpassNo"]);
            ${'cpassEx' . $x} = $_POST['child'][$x]["cpassEx"];
            ${'cpassNation' . $x} = strtoupper($_POST['child'][$x]["cpassNation"]);

            ${'sql' . $x} = "INSERT INTO `passengers`(
                            `paxId`,`agentId`,`subagentId`,`bookingId`,`fName`,`lName`, `dob`,`gender`,`type`,`passNation`,`passNo`,`passEx`,`created`)
                    VALUES('$paxId','$agentId','$subagentId','$BookingId','${'cfName' . $x}','${'clName' . $x}','${'cdob' . $x}','${'cgender' . $x}','CNN','${'cpassNation' . $x}',
                        '${'cpassNo' . $x}','${'cpassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});
        }

        for ($x = 0; $x < $infants; $x++) {

            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "FWP$number";
                }
            } else {
                $paxId = "FWP1000";
            }

            ${'ifName' . $x} = strtoupper($_POST['infant'][$x]["ifName"]);
            ${'ilName' . $x} = strtoupper($_POST['infant'][$x]["ilName"]);
            ${'igender' . $x} = $_POST['infant'][$x]["igender"];
            ${'idob' . $x} = $_POST['infant'][$x]["idob"];
            ${'ipassNo' . $x} = strtoupper($_POST['infant'][$x]["ipassNo"]);
            ${'ipassEx' . $x} = $_POST['infant'][$x]["ipassEx"];
            ${'ipassNation' . $x} = strtoupper($_POST['infant'][$x]["ipassNation"]);

            ${'sql' . $x} = "INSERT INTO `passengers`(
                            `paxId`,`agentId`,`subagentId`,`bookingId`,`fName`,`lName`,`dob`,`gender`,`type`,`passNation`,`passNo`,`passEx`,`created`)
                    VALUES('$paxId','$agentId','$subagentId','$BookingId','${'ifName' . $x}','${'ilName' . $x}','${'idob' . $x}','${'igender' . $x}','INF','${'ipassNation' . $x}',
                        '${'ipassNo' . $x}','${'ipassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});
        }

    } else if ($adult > 0 && $child > 0) {

        for ($x = 0; $x < $adult; $x++) {
            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "FWP$number";
                }
            } else {
                $paxId = "FWP1000";
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
                VALUES('$paxId','$agentId','$subagentId','$BookingId','${'afName' . $x}','${'alName' . $x}','${'adob' . $x}','${'agender' . $x}','ADT','${'apassNation' . $x}',
                        '${'apassNo' . $x}','${'apassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});

        }

        for ($x = 0; $x < $child; $x++) {

            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "FWP$number";
                }
            } else {
                $paxId = "FWP1000";
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
                    VALUES('$paxId','$agentId','$subagentId','$BookingId','${'cfName' . $x}','${'clName' . $x}','${'cdob' . $x}','${'cgender' . $x}','CNN','${'cpassNation' . $x}',
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
            $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "FWP$number";
                }
            } else {
                $paxId = "FWP1000";
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
                    VALUES('$paxId','$agentId','$subagentId','$BookingId','${'afName' . $x}','${'alName' . $x}','${'adob' . $x}','${'agender' . $x}','ADT','${'apassNation' . $x}',
                        '${'apassNo' . $x}','${'apassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});

        }

        for ($x = 0; $x < $infants; $x++) {

            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "FWP$number";
                }
            } else {
                $paxId = "FWP1000";
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
                    VALUES('$paxId','$agentId','$subagentId','$BookingId','${'ifName' . $x}','${'ilName' . $x}','${'idob' . $x}','${'igender' . $x}','INF','${'ipassNation' . $x}',
                        '${'ipassNo' . $x}','${'ipassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});

        }

    } else if ($adult > 0) {
        for ($x = 0; $x < $adult; $x++) {
            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "FWP$number";
                }
            } else {
                $paxId = "FWP1000";
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
                VALUES('$paxId','$agentId','$subagentId','$BookingId','${'afName' . $x}','${'alName' . $x}','${'adob' . $x}','${'agender' . $x}','ADT','${'apassNation' . $x}',
                    '${'apassNo' . $x}','${'apassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});

        }

    }

}