<?php

include '../config.php';
include "../AirSearch/Token.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require "../../vendor/autoload.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_POST = json_decode(file_get_contents('php://input'), true);

    $gdsSystem = isset($_POST['system']) ? $_POST['system'] : '';
    
    if ($gdsSystem == 'Sabre'){
        $SabreRequest = SabreRequest();
        SabreBooking($conn, $SabreToken, $SabreRequest);
        
    }else if($gdsSystem == 'Galileo'){
       $GalileoRequest = GalileoRequest();
       GalileoBooking($conn, $GalileoRequest);
       
    }
}

function SabreRequest(){
    $PassengerData = $_POST['flightPassengerData'];
    $adult = $PassengerData['adultCount'];
    $child = $PassengerData['childCount'];
    $infants = $PassengerData['infantCount']; 
    $Phone = $PassengerData["phone"];
    $flightData = $_POST['flightPassengerData']['segments'];
    $now = new DateTime();

    $AllPerson = array();
    $AdvancePassnger = array();
    $AllSsr = array();
    $AllSecureFlight = array();
       
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

    $Name = $PassengerData['adult'][0]['afName'] . ' ' . $PassengerData['adult'][0]['alName'];
    $Phone = $PassengerData['phone'];
    $Email = $PassengerData['email'];
    $SeatReq = $adult + $child;
    $Allsegments= array();
    foreach($flightData as $sgflight){

        foreach($sgflight as $flight){
            $DepFrom = $flight['DepFrom'];
            $ArrTo = $flight['ArrTo'];
            $DepTime = substr($flight['DepTime'],0,19);
            $ArrTime = substr($flight['ArrTime'],0,19);
            $BookingCode = $flight['SegmentCode']['bookingCode'];
            $MarketingCarrier = $flight['MarketingCarrier'];
            $MarketingFlightNumber = $flight['MarketingFlightNumber'];

            $SingleSegment = array(
                    "DepartureDateTime"=>$DepTime,
                    "ArrivalDateTime"=> $ArrTime,
                    "FlightNumber"=> "$MarketingFlightNumber",
                    "NumberInParty"=> "$SeatReq",
                    "ResBookDesigCode"=> $BookingCode,
                    "Status"=>"NN",
                    "OriginLocation"=>array(
                        "LocationCode"=>$DepFrom
                    ),
                    "DestinationLocation"=>array(
                        "LocationCode"=> $ArrTo
                    ),
                    "MarketingAirline"=> array(
                        "Code"=> $MarketingCarrier,
                        "FlightNumber"=> "$MarketingFlightNumber"
                    )
                    ); 
                    
            array_push($Allsegments, $SingleSegment);
        }
    }

    $FlightSegment = json_encode($Allsegments);
    

    $PersonFinal = json_encode($AllPerson);
    $AdvPassengerFinal = json_encode($AdvancePassnger); 
    $SSRFinal = json_encode($AllSsr);

    $SabreRequest = '{
                    "CreatePassengerNameRecordRQ":{
                    "targetCity":"14KK",
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

    return $SabreRequest;
    
}

function SabreBooking($conn, $SabreToken, $SabreRequest){

    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.platform.sabre.com/v2.4.0/passenger/records?mode=create', //Live
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $SabreRequest,
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Conversation-ID: {{conv_id}}',
        'Authorization: Bearer '.$SabreToken,
        ),
    ));

    $SabreResponseData = curl_exec($curl);
    curl_close($curl);
  
    $SabreResult = json_decode($SabreResponseData, true);

    if(isset($SabreResult['CreatePassengerNameRecordRS']['ItineraryRef']['ID'])) {
        $BookingPNR = $SabreResult['CreatePassengerNameRecordRS']['ItineraryRef']['ID'];
            
        $UniversalPnr='';
        addBookingQueue($conn, $BookingPNR, $UniversalPnr);
    }else if (isset($result['CreatePassengerNameRecordRS']['ApplicationResults']['Error'])) {
        $bookingId='';
        addPax($conn, $bookingId);
        $resResult = $SabreResult['CreatePassengerNameRecordRS']['ApplicationResults']['Error'][0]['SystemSpecificResults'];
        $resError = $SabreResult['CreatePassengerNameRecordRS']['ApplicationResults']['Warning'][0]['SystemSpecificResults'];
        $SabreResponse['status'] = "error";
        $SabreResponse['message'] = "Booking Failed";
        $SabreResponse['result'] = $resResult;
        $SabreResponse['warning'] = $resError;

        echo json_encode($SabreResponse);
        exit();
    }else {
        $bookingId='';
        addPax($conn, $bookingId);
        $response1['status'] = "error";
        $response1['message'] = "Booking Failed";
        echo json_encode($response1);
        exit();
    }
    
}

function GalileoRequest(){

    $PassengerData  = $_POST['flightPassengerData'];
    $AllSegments  = $PassengerData['segments'];
    $adult = $PassengerData['adultCount'];
    $child = $PassengerData['childCount'];
    $infants = $PassengerData['infantCount'];
    $TicketingDate = $PassengerData['tDate'];
    $EffectiveDate = $PassengerData['eDate'];    
    $AirPricingSolutionKey = $PassengerData['airPriceKey'];
    $PlattingCarrier = $AllSegments[0][0]['MarketingCarrier'];
  
    $AdultPassengerType =array();
    $ChildPassengerType =array();
    $InfantPassengerType =array();

    $AllPassenger = array();
    $AllAirSegments = array();
    $AllBookingInfo = array();

    $AirPricingSolution = array();
    $AirPricingTicketingModifiers = array();
    
    $AllFareinfoList = array();
    foreach($AllSegments as $SingleLegSegments){

        foreach($SingleLegSegments as $Segment){
            $AirSegmentKey = $Segment['SegmentCode']['SegmentRef'];
            $FareInfoSingleKey = $Segment['SegmentCode']['FareInfoRef'];
            $MarketingCarrier = $Segment['MarketingCarrier'];
            $MarketingFlightNumber = $Segment['MarketingFlightNumber'];
            $BookingCode = $Segment['SegmentCode']['BookingCode'];
            $FareBasisCode = $Segment['FareBasis'];
            $DepFrom = $Segment['DepFrom'];
            $ArrTo = $Segment['ArrTo'];
            $Group = $Segment['Group'];	
            $DepTime = $Segment['DepTime'];
            $ArrTime  = $Segment['ArrTime'];

            $FareinfoList = <<<EOM
            <FareInfo Key="$FareInfoSingleKey" FareBasis="$FareBasisCode" PassengerTypeCode="ADT" Origin="$DepFrom" Destination="$ArrTo" EffectiveDate="$EffectiveDate"></FareInfo>
            EOM;

            array_push($AllFareinfoList, $FareinfoList);
            
            $AirSegments = <<<EOM
                <AirSegment Key="$AirSegmentKey" Group="$Group" Carrier="$MarketingCarrier" FlightNumber="$MarketingFlightNumber" ProviderCode="1G" Origin="$DepFrom" Destination="$ArrTo" DepartureTime="$DepTime" ArrivalTime="$ArrTime" />
            EOM;

            array_push($AllAirSegments, $AirSegments);

            $BookingInfo =<<<EOM
                        <BookingInfo BookingCode="$BookingCode" FareInfoRef="$FareInfoSingleKey" SegmentRef="$AirSegmentKey" />
                    EOM;
            array_push($AllBookingInfo, $BookingInfo);
        }    
    }
    
    $AllFareinfoListKey = implode(" ",$AllFareinfoList);
    $BookingInfoCode = implode(" ",$AllBookingInfo);
    if($adult >  0){
        $AirPriceInfoKey = $PassengerData['adult'][0]['AirFareInfo'];

        for($x = 0 ; $x < $adult; $x++){

            ${'afName'.$x} = $PassengerData['adult'][$x]["afName"];
            ${'alName'.$x} = $PassengerData['adult'][$x]["alName"];
            ${'agender'.$x} = $PassengerData['adult'][$x]["agender"];
            ${'adob'.$x} = date("dMy", strtotime($PassengerData['adult'][$x]["adob"]));
            ${'apassNo'.$x} = $PassengerData['adult'][$x]["apassNo"];
            ${'apassEx'.$x} = date("dMy", strtotime($PassengerData['adult'][$x]["apassEx"]));
            ${'apassNation'.$x} = $PassengerData['adult'][$x]["apassNation"];
            if(${'agender'.$x} = 'M'){
                ${'atitle'.$x} = 'MR';
            }else{
                ${'atitle'.$x} = 'MRS';
            }


            $AdultPassengerItem=<<<EOM
                            <BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="ADT$x" TravelerType="ADT" Gender="${'agender'.$x}" Nationality="${'apassNation'.$x}">
                                <BookingTravelerName Prefix="${'atitle'.$x}" First="${'afName'.$x} " Last="${'alName'.$x}" />
                                <SSR Type="DOCS" Status="HK" FreeText="P/${'apassNation'.$x}/${'apassNo'.$x}/${'apassNation'.$x}/${'adob'.$x}/${'agender'.$x}/${'apassEx'.$x}/${'alName'.$x}/${'afName'.$x} " Carrier="$PlattingCarrier" />
                            </BookingTraveler>
                        EOM;
            
            array_push($AllPassenger, $AdultPassengerItem);


            $AdultPassengerTypeItem =<<<EOM
                        <PassengerType Code="ADT"  BookingTravelerRef="ADT$x" />
            EOM;

            array_push($AdultPassengerType, $AdultPassengerTypeItem);
                
        }
        
        $AdultPassengerTypeAll = implode(" ",$AdultPassengerType);
        $AdultAirPricingInfo =<<<EOM
        <AirPricingInfo Key="$AirPriceInfoKey" PricingMethod="Guaranteed" PlatingCarrier="$PlattingCarrier" ProviderCode="1G">
                    $AllFareinfoListKey
                    $BookingInfoCode
                    $AdultPassengerTypeAll
        </AirPricingInfo>
        EOM;

        array_push($AirPricingSolution, $AdultAirPricingInfo);

         $AirPricingTicketing =<<<EOM
                <AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
                    <AirPricingInfoRef Key="$AirPriceInfoKey" />
                    <TicketingModifiers>
                    <Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
                    </TicketingModifiers>
                </AirPricingTicketingModifiers>
            EOM;
            
        array_push($AirPricingTicketingModifiers, $AirPricingTicketing);

    }
    if($child >  0){
        $ChildAirPriceInfoKey = $PassengerData['child'][0]['AirFareInfo'];

        for($x = 0 ; $x < $child; $x++){

            ${'cfName'.$x} = $PassengerData['child'][$x]["cfName"];
            ${'clName'.$x} = $PassengerData['child'][$x]["clName"];
            ${'cgender'.$x} = $PassengerData['child'][$x]["cgender"];
            ${'cdob'.$x} = date("dMy", strtotime($PassengerData['child'][$x]["cdob"]));
            ${'cpassNo'.$x} = $PassengerData['child'][$x]["cpassNo"];
            ${'cpassEx'.$x} = date("dMy", strtotime($PassengerData['child'][$x]["cpassEx"]));
            ${'cpassNation'.$x} = $PassengerData['child'][$x]["cpassNation"];

            if(${'cgender'.$x} = 'M'){
                ${'ctitle'.$x} = 'MR';
            }else{
                ${'ctitle'.$x} = 'MISS';
            }


            $ChildPassengerItem=
            <<<EOM
            <BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="CNN$x" TravelerType="CNN" Gender="${'cgender'.$x}" Nationality="${'cpassNation'.$x}">
                    <BookingTravelerName Prefix="${'ctitle'.$x}" First="${'cfName'.$x} " Last="${'clName'.$x}" />
                    <SSR Type="DOCS" Status="HK" FreeText="P/${'cpassNation'.$x}/${'cpassNo'.$x}/${'cpassNation'.$x}/${'cdob'.$x}/${'cgender'.$x}/${'cpassEx'.$x}/${'clName'.$x}/${'cfName'.$x} " Carrier="$PlattingCarrier" />
                </BookingTraveler>
            EOM;

            array_push($AllPassenger, $ChildPassengerItem);


            $ChildPassengerTypeItem =
            <<<EOM
                        <PassengerType Code="CNN" BookingTravelerRef="CNN$x" />
            EOM;

            array_push($ChildPassengerType, $ChildPassengerTypeItem);
                
        }
 
        $ChildPassengerTypeAll = implode(" ",$ChildPassengerType); 
        $ChildAirPricingInfo =<<<EOM
        <AirPricingInfo Key="$ChildAirPriceInfoKey" PricingMethod="Guaranteed" PlatingCarrier="$PlattingCarrier" ProviderCode="1G">
                $AllFareinfoListKey
                $BookingInfoCode
                $ChildPassengerTypeAll
        </AirPricingInfo>
        EOM;
        
        array_push($AirPricingSolution, $ChildAirPricingInfo);

        $ChildAirPricingTicketing =<<<EOM
                <AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
                    <AirPricingInfoRef Key="$ChildAirPriceInfoKey" />
                    <TicketingModifiers>
                    <Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
                    </TicketingModifiers>
                </AirPricingTicketingModifiers>
            EOM;
            
        array_push($AirPricingTicketingModifiers, $ChildAirPricingTicketing);
    }
    
    if($infants >  0){
        $InfantAirPriceInfoKey = $PassengerData['infant'][0]['AirFareInfo'];

        for($x = 0 ; $x < $infants; $x++){

            ${'ifName'.$x} = $PassengerData['infant'][$x]["ifName"];
            ${'ilName'.$x} = $PassengerData['infant'][$x]["ilName"];
            ${'igender'.$x} = $PassengerData['infant'][$x]["igender"];
            ${'idob'.$x} = date("dMy", strtotime($PassengerData['infant'][$x]["idob"]));
            ${'ipassNo'.$x} = $PassengerData['infant'][$x]["ipassNo"];
            ${'ipassEx'.$x} = date("dMy", strtotime($PassengerData['infant'][$x]["ipassEx"]));
            ${'ipassNation'.$x} = $PassengerData['infant'][$x]["ipassNation"];

            if(${'igender'.$x} = 'M'){
                
                ${'ititle'.$x} = 'MSTR';
            }else{
                ${'ititle'.$x} = 'MISS';
            }

            $InfantPassengerItem=<<<EOM
                            <BookingTraveler xmlns="http://www.travelport.com/schema/common_v51_0" Key="INF$x" TravelerType="INF" Gender="${'igender'.$x}" Nationality="${'ipassNation'.$x}">
                                <BookingTravelerName Prefix="${'ititle'.$x}" First="${'ifName'.$x} " Last="${'ilName'.$x}" />
                                <SSR Type="DOCS" Status="HK" FreeText="P/${'ipassNation'.$x}/${'ipassNo'.$x}/${'ipassNation'.$x}/${'idob'.$x}/${'igender'.$x}/${'ipassEx'.$x}/${'ilName'.$x}/${'ifName'.$x} " Carrier="$PlattingCarrier" />
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

        
        $InfantPassengerTypeAll = implode(" ",$InfantPassengerType);
        $InfantAirPricingInfo =<<<EOM
        <AirPricingInfo Key="$InfantAirPriceInfoKey" PricingMethod="Guaranteed" PlatingCarrier="$PlattingCarrier" ProviderCode="1G">
                    $AllFareinfoListKey
                    $BookingInfoCode
                    $InfantPassengerTypeAll
        </AirPricingInfo>
        EOM;

        array_push($AirPricingSolution, $InfantAirPricingInfo);

        $InfantAirPricingTicketing =<<<EOM
                <AirPricingTicketingModifiers xmlns="http://www.travelport.com/schema/air_v51_0">
                    <AirPricingInfoRef Key="$InfantAirPriceInfoKey" />
                    <TicketingModifiers>
                    <Commission xmlns="http://www.travelport.com/schema/common_v51_0" Level="Fare" Type="PercentBase" Percentage="7.00" />
                    </TicketingModifiers>
                </AirPricingTicketingModifiers>
            EOM;
            
        array_push($AirPricingTicketingModifiers, $InfantAirPricingTicketing);
        
    }

    $AirPricingSolutionAll = implode(" ",$AirPricingSolution);
    $AirPricingTicketingModifiersAll = implode(" ",$AirPricingTicketingModifiers);

	$PassengerNumAll = implode(" ",$AllPassenger);
    $AllAirSegment = implode(" ",$AllAirSegments);
    $AirPricingSolution = <<<EOM
				<AirPricingSolution Key="$AirPricingSolutionKey" xmlns="http://www.travelport.com/schema/air_v51_0">
						$AllAirSegment
						$AirPricingSolutionAll
				</AirPricingSolution>	
		EOM;

    $GalileoRequest = <<<EOM
        <soapenv:Envelope xmlns:univ="http://www.travelport.com/schema/universal_v51_0" xmlns:com="http://www.travelport.com/schema/common_v51_0" xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
        <soapenv:Header/>
        <soapenv:Body
            xmlns:univ="http://www.travelport.com/schema/universal_v51_0"
            xmlns:com="http://www.travelport.com/schema/common_v51_0">
            <univ:AirCreateReservationReq xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                xmlns:univ="http://www.travelport.com/schema/universal_v51_0" TraceId="Fly_Far_Tech" TargetBranch="P4218912" RuleName="COMM"
                RetainReservation="Both" RestrictWaitlist="true" xmlns="http://www.travelport.com/schema/common_v51_0">
                <BillingPointOfSaleInfo xmlns="http://www.travelport.com/schema/common_v51_0" OriginApplication="UAPI" />
                    $PassengerNumAll
                <ContinuityCheckOverride Key="1T" xmlns="http://www.travelport.com/schema/common_v51_0">true</ContinuityCheckOverride>
                <AgencyContactInfo xmlns="http://www.travelport.com/schema/common_v51_0">
                    <PhoneNumber Location="DAC" Number="8809606912912" Text="Fly Far International" />
                </AgencyContactInfo>
                    $AirPricingSolution	    
                <ActionStatus xmlns="http://www.travelport.com/schema/common_v51_0" Type="TAW" TicketDate="$TicketingDate" ProviderCode="1G" />
                    $AirPricingTicketingModifiersAll
            </univ:AirCreateReservationReq>
        </soapenv:Body>
        </soapenv:Envelope>
        EOM;

    return $GalileoRequest;
}


function GalileoBooking($conn, $GalileoRequest){
    
    $curl = curl_init();
    curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://apac.universal-api.travelport.com/B2BGateway/connect/uAPI/AirService',
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

    $GalileoXMLResponse = curl_exec($curl);
    curl_close($curl);

	$GalileoXMLResult = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $GalileoXMLResponse);
	$xml = new SimpleXMLElement($GalileoXMLResult);
	if(isset($xml->xpath('//universalAirCreateReservationRsp')[0])){
		$ParseXMLbodyJSON = $xml->xpath('//universalAirCreateReservationRsp')[0];
		
		$GalileoResult = json_decode(json_encode((array)$ParseXMLbodyJSON), TRUE);
		
        if(isset($GalileoResult['universalUniversalRecord']['@attributes']['LocatorCode'])){
            $BookingPNR = $GalileoResult['universalUniversalRecord']['universalProviderReservationInfo']['@attributes']['LocatorCode'];
            $UniversalPnr = $GalileoResult['universalUniversalRecord']['@attributes']['LocatorCode'];
            addBookingQueue($conn, $BookingPNR, $UniversalPnr);
        }else{
            $bookingId='';
            addPax($conn, $bookingId);
            $GalileoResponse['status'] = "error";
            $GalileoResponse['message'] = "Booking Failed";
            echo json_encode($GalileoResponse);
            exit();  
        }
	
	}else{
        $bookingId='';
        addPax($conn, $bookingId);
        $GalileoResponse['status'] = "error";
        $GalileoResponse['message'] = "Booking Failed";
        echo json_encode($GalileoResponse);
        exit();
    }
}
    
function addBookingQueue($conn, $BookingPNR, $UniversalPnr){
    $createdTime = date("Y-m-d H:i:s");
    $BookingData = $_POST['bookingInfo'];
    $uId = sha1(md5(time())); 

    if (!empty($BookingPNR)) {
        $bookingId = "";
        $sql1 = "SELECT * FROM booking ORDER BY bookingId DESC LIMIT 1";
        $result = $conn->query($sql1);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["bookingId"]);
                $number = (int) $outputString + 1;
                $bookingId = "FFBB$number";
            }
        } else {
            $bookingId = "FFBB1000";
        }

        $agentId = $BookingData["agentId"];
        $staffId = isset($BookingData["staffId"]) ? $BookingData["staffId"] : "";
        $subagentId = isset($BookingData['subagentId']) ? $BookingData['subagentId'] : "";
        $System = $BookingData["system"];
        $From = $BookingData["from"];
        $To = $BookingData["to"];
        $Airlines = $BookingData["airlines"];
        $Type = $BookingData["tripType"];
        $journeyType = isset($BookingData["journeyType"]) ? $BookingData["journeyType"] : $BookingData["tripType"] ;
        $Name = strtoupper($BookingData["name"]);
        $Phone = $BookingData["phone"];
        $Email = $BookingData["email"];
        $Pnr = $BookingPNR;
        $Pax = $BookingData["pax"];
        $Refundable = $BookingData["refundable"];
        $adultCount = $BookingData["adultcount"];
        $childCount = $BookingData["childcount"];
        $infantCount = $BookingData["infantcount"];
        $netCost = $BookingData["netcost"];
        $subagentprice = isset($BookingData["subagentprice"]) ? $BookingData["subagentprice"] :'';
        $adultCostBase = $BookingData["adultcostbase"];
        $childCostBase = $BookingData["childcostbase"];
        $infantCostBase = $BookingData["infantcostbase"];
        $adultCostTax = $BookingData["adultcosttax"];
        $childCostTax = $BookingData["childcosttax"];
        $infantCostTax = $BookingData["infantcosttax"];
        $grossCost = $BookingData["grosscost"];
        $BaseFare = $BookingData["basefare"];
        $taxFare = $BookingData["tax"];
        $Platform = isset($BookingData["platform"]) ? $BookingData["platform"] :'';
        $travelDate= $_POST['flightPassengerData']['segments'][0][0]['DepTime'];
        $LastTicketTime = isset($BookingData["timeLimit"]) ? $BookingData["timeLimit"] :'';
        

        $DateTime = date("D d M Y h:i A");

        if (isset($agentId)) {
            $SubAgentData = $conn->query("SELECT * FROM agent WHERE agentId='$agentId'")->fetch_all(MYSQLI_ASSOC)[0];

            if(!empty($SubAgentData)) {
                $agentName = $SubAgentData['name'];
                $agentEmail = $SubAgentData['email'];
                $companyname = $SubAgentData['company'];
            }else{
                $companyname = '';
            }
        }else{
            $response['status'] = 'error';
            $response['message'] ='Agent Information Missing'; 
            echo  json_encode($response); 
            exit();
        }

        $staffsql2 = mysqli_query($conn, "SELECT * FROM `staffList` where agentId = '$agentId' AND staffId='$staffId'");
        $staffrow2 = mysqli_fetch_array($staffsql2, MYSQLI_ASSOC);
        if (!empty($staffrow2)) {
            $staffName = $staffrow2['name'];
            $BookedBy = $staffrow2['name'];
        } else {
            $BookedBy = $companyname;
            $staffName = $agentName;
        }

        if (empty($staffId) && !empty($agentId)) {
            $Message = "Dear $companyname,  you have been requested for $From to $To $Type air ticket on $DateTime, career Name: <b>$Airlines</b>. Your booking request has been accepted. Your booking time limit  $LastTicketTime. Please issue your ticket before giving time limit. Thank you for booking with Fly Far International ";
            $Booked = "Booked By: $companyname";
        } else if (!empty($staffId) && !empty($agentId)) {
            $Message = "Dear $companyname, your staff $staffName has sent booking request $From to $To $Type  air ticket on $createdTime, Career Name: <b>$Airlines</b>. Your booking request has been accepted. Your need to immediately issue this ticket. Otherwise your booking request has been cancelled. Thank you for booking with Fly Far International";

            $Booked = "Booked By: $staffName,  $companyname";
        } else if (!empty($staffId) && !empty($agentId) && !empty($LastTicketTime)) {

            $Message = "Dear $companyname, your staff $staffName has sent booking request $From to $To $Type  air ticket on $createdTime, Career Name: <b>$Airlines</b>. Your booking request has been accepted. Your booking time limit  $LastTicketTime. Please issue your ticket before giving time limit. Thank you for booking with Fly Far International";

            $Booked = "Booked By: $staffName,  $companyname";
        }

        //sub agent mail data
        $SubAgentData = $conn->query("SELECT * FROM subagent where subagentId='$subagentId' AND agentId= '$agentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC)[0];
        
        if(!empty($SubAgentData)){
            $subcompanyName = $SubAgentData['company'];
        }else{
            $subcompanyName = '';
        }


    $sql = "INSERT INTO `booking` (`uid`,`bookingId`,`agentId`,`staffId`,`subagentId`,`email`,`phone`,`name`,
                        `refundable`,`upPnr`,`pnr`,`tripType`,`journeyType`,`pax`,`adultCount`,`childCount`,
                        `infantCount`,`netCost`,`subagentCost`,`adultCostBase`,`childCostBase`,`infantCostBase`,
                        `adultCostTax`,`childCostTax`,`infantCostTax`,`grossCost`,`baseFare`,`Tax`,`deptFrom`,
                        `airlines`,`arriveTo`,`gds`,`status`,`travelDate`,`timeLimit`,`bookedAt`,`bookedBy`,
                        `lastUpdated`,`platform`)

  VALUES('$uId','$bookingId','$agentId','$staffId','$subagentId','$Email','$Phone','$Name','$Refundable','$UniversalPnr','$Pnr','$Type','$journeyType','$Pax','$adultCount','$childCount','$infantCount',
        '$netCost','$subagentprice','$adultCostBase','$childCostBase','$infantCostBase','$adultCostTax','$childCostTax','$infantCostTax','$grossCost',
        '$BaseFare','$taxFare','$From','$Airlines','$To','$System','Hold','$travelDate','$LastTicketTime','$createdTime','$BookedBy','$createdTime','$Platform')";

        if ($conn->query($sql) === true) {
            addPax($conn, $bookingId);
            $conn->query("INSERT INTO `activityLog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                            VALUES ('$bookingId','$agentId','Hold','$subcompanyName','$BookedBy','$createdTime')");
            
           AgentEmailSend($agentEmail, $bookingId, $Message, $Booked);
           OwnerEmailSend($companyname, $bookingId, $BookingPNR, $Booked);

            if(!empty($subagentId)){
                SubAgentEmailSend($conn, $bookingId);
            }

        }
    }

}

function addPax($conn, $bookingId){
    $PassengerData = $_POST['flightPassengerData'];
    $agentId = isset($_POST_['agentId']) ? $_POST['agentId'] :'';
    $subagentId = isset($_POST_['subagentId']) ? $_POST['subagentId'] :'';
    $adult = $PassengerData['adultCount'];
    $child = $PassengerData['childCount'];
    $infants = $PassengerData['infantCount']; 
    $createdTimer = date('Y-m-d H:i:s');

    if($adult > 0){

        for ($x = 0; $x < $adult; $x++) {

            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "FFP$number";
                }
            } else {
                $paxId = "FFP1000";
            }

            ${'afName' . $x} = strtoupper($PassengerData['adult'][$x]["afName"]);
            ${'alName' . $x} = strtoupper($PassengerData['adult'][$x]["alName"]);
            ${'agender' . $x} = $PassengerData['adult'][$x]["agender"];
            ${'adob' . $x} = $PassengerData['adult'][$x]["adob"];
            ${'apassNo' . $x} = strtoupper($PassengerData['adult'][$x]["apassNo"]);
            ${'apassEx' . $x} = $PassengerData['adult'][$x]["apassEx"];
            ${'apassNation' . $x} = strtoupper($PassengerData['adult'][$x]["apassNation"]);

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
                    VALUES('$paxId','$agentId','$subagentId','$bookingId','${'afName' . $x}','${'alName' . $x}','${'adob' . $x}','${'agender' . $x}','ADT','${'apassNation' . $x}',
                        '${'apassNo' . $x}','${'apassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});

        }
    }
    if($child > 0){

        for ($x = 0; $x < $child; $x++) {

            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "FFP$number";
                }
            } else {
                $paxId = "FFP1000";
            }

            ${'cfName' . $x} = strtoupper($PassengerData['child'][$x]["cfName"]);
            ${'clName' . $x} = strtoupper($PassengerData['child'][$x]["clName"]);
            ${'cgender' . $x} = $PassengerData['child'][$x]["cgender"];
            ${'cdob' . $x} = $PassengerData['child'][$x]["cdob"];
            ${'cpassNo' . $x} = strtoupper($_POST['child'][$x]["cpassNo"]);
            ${'cpassEx' . $x} = $PassengerData['child'][$x]["cpassEx"];
            ${'cpassNation' . $x} = strtoupper($_POST['child'][$x]["cpassNation"]);

            ${'sql' . $x} = "INSERT INTO `passengers`(
                            `paxId`,`agentId`,`subagentId`,`bookingId`,`fName`,`lName`, `dob`,`gender`,`type`,`passNation`,`passNo`,`passEx`,`created`)
                    VALUES('$paxId','$agentId','$subagentId','$bookingId','${'cfName' . $x}','${'clName' . $x}','${'cdob' . $x}','${'cgender' . $x}','CNN','${'cpassNation' . $x}',
                        '${'cpassNo' . $x}','${'cpassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});
        }
    }    
    if($infants > 0){

        for ($x = 0; $x < $infants; $x++) {

            $paxId = "";
            $result = $conn->query("SELECT * FROM passengers ORDER BY paxId DESC LIMIT 1");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["paxId"]);
                    $number = (int) $outputString + 1;
                    $paxId = "FFP$number";
                }
            } else {
                $paxId = "FFP1000";
            }

            ${'ifName' . $x} = strtoupper($PassengerData['infant'][$x]["ifName"]);
            ${'ilName' . $x} = strtoupper($PassengerData['infant'][$x]["ilName"]);
            ${'igender' . $x} = $PassengerData['infant'][$x]["igender"];
            ${'idob' . $x} = $PassengerData['infant'][$x]["idob"];
            ${'ipassNo' . $x} = strtoupper($_POST['infant'][$x]["ipassNo"]);
            ${'ipassEx' . $x} = $PassengerData['infant'][$x]["ipassEx"];
            ${'ipassNation' . $x} = strtoupper($PassengerData['infant'][$x]["ipassNation"]);

            ${'sql' . $x} = "INSERT INTO `passengers`(
                            `paxId`,`agentId`,`subagentId`,`bookingId`,`fName`,`lName`,`dob`,`gender`,`type`,`passNation`,`passNo`,`passEx`,`created`)
                    VALUES('$paxId','$agentId','$subagentId','$bookingId','${'ifName' . $x}','${'ilName' . $x}','${'idob' . $x}','${'igender' . $x}','INF','${'ipassNation' . $x}',
                        '${'ipassNo' . $x}','${'ipassEx' . $x}','$createdTimer')";

            $conn->query(${'sql' . $x});
        }
    }

}

function AgentEmailSend($agentEmail,  $bookingId, $Message, $Booked){
    $agentId = $_POST['agentId'];
    
    $AgentMail = '<!DOCTYPE html>
                <html lang="en">
                  <head>
                    <meta charset="UTF-8" />
                    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                    <title>Deposit Request
                </title>
                  </head>
                  <body>
                    <div
                      class="div"
                      style="
                        width: 650px;
                        height: 100vh;
                        margin: 0 auto;
                      "
                    >
                      <div
                        style="
                          width: 650px;
                          height: 200px;
                          background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
                          border-radius: 20px 0px  20px  0px;

                        "
                      >
                        <table
                          border="0"
                          cellpadding="0"
                          cellspacing="0"
                          align="center"
                          style="
                            border-collapse: collapse;
                            border-spacing: 0;
                            padding: 0;
                            width: 650px;
                            border-radius: 10px;

                          "
                        >
                          <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #000000;
                                font-family: sans-serif;
                                font-weight: bold;
                                font-size: 20px;
                                line-height: 38px;
                                padding-top: 30px;
                                padding-bottom: 10px;
                              "
                            >
                              <a href="https://www.flyfarint.com/"
                                ><img
                                src="https://cdn.flyfarint.com/logo.png"
                                  width="130px"
                              /></a>

                            </td>
                          </tr>
                        </table>

                        <table
                          border="0"
                          cellpadding="0"
                          cellspacing="0"
                          align="center"
                          bgcolor="white"
                          style="
                            border-collapse: collapse;
                            border-spacing: 0;
                            padding: 0;
                            width: 550px;
                          "
                        >
                          <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #000000;
                                font-family: sans-serif;
                                text-align: left;
                                padding-left: 20px;
                                font-weight: bold;
                                font-size: 19px;
                                line-height: 38px;
                                padding-top: 20px;
                                background-color: white;


                              "
                            >
                              Booking Request
                            </td>
                          </tr>
                          <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                font-family: sans-serif;
                                text-align: left;
                                padding-left: 20px;
                                font-weight: bold;
                                padding-top: 15px;
                                font-size: 12px;
                                line-height: 18px;
                                color: #929090;
                                padding-right: 20px;
                                background-color: white;

                              "
                            >
                             ' . $Message . '
                            </td>
                          </tr>

                          <tr>
                          <td
                            align="center"
                            valign="top"
                            style="
                              border-collapse: collapse;
                              border-spacing: 0;
                              color: #000000;
                              font-family: sans-serif;
                              text-align: left;
                              padding-left: 20px;
                              font-weight: bold;
                              padding-top: 20px;
                              font-size: 13px;
                              line-height: 18px;
                              color: #929090;
                              padding-top: 20px;
                              width: 100%;
                              background-color: white;
                            "
                          >
                          <span style="color:#003566 ;">  ' . $Booked . ' </span>
                          </td>
                        </tr>


                          <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #000000;
                                font-family: sans-serif;
                                text-align: left;
                                padding-left: 20px;
                                font-weight: bold;
                                padding-top: 20px;
                                font-size: 13px;
                                line-height: 18px;
                                color: #929090;
                                padding-top: 20px;
                                width: 100%;
                              "
                            >
                              Booking Id:
                              <a style="color: #003566" href="http://" target="_blank"
                                >' . $bookingId . '</a
                              >
                            </td>
                          </tr>


                                                                  <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #000000;
                                font-family: sans-serif;
                                text-align: left;
                                padding-left: 20px;
                                font-weight: bold;
                                padding-top: 20px;
                                font-size: 13px;
                                line-height: 18px;
                                color: #929090;
                                padding-top: 20px;
                                width: 100%;
                                background-color: white;

                              "
                            >
                                   If you have any questions, just contact us we are always happy to
                              help you out.
                            </td>
                          </tr>


                             <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #000000;
                                font-family: sans-serif;
                                text-align: left;
                                padding-left: 20px;
                                font-weight: bold;
                                padding-top: 20px;
                                font-size: 13px;
                                line-height: 18px;
                                color: #929090;
                                padding-top: 20px;
                                width: 100%;
                                background-color: white;

                              "
                            >
                               Sincerely,

                            </td>
                          </tr>

                             <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #000000;
                                font-family: sans-serif;
                                text-align: left;
                                padding-left: 20px;
                                font-weight: bold;
                                font-size: 13px;
                                line-height: 18px;
                                color: #929090;
                                width: 100%;
                                background-color: white;
                                padding-bottom: 20px

                              "
                            >
                              Fly Far International

                            </td>
                          </tr>


                          <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #ffffff;
                                font-family: sans-serif;
                                text-align: center;
                                font-weight: 600;
                                font-size: 14px;
                                color: #ffffff;
                                padding-top: 15px;
                                background-color: #dc143c;
                              "
                            >
                              Need more help?
                            </td>
                          </tr>

                          <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #ffffff;
                                font-family: sans-serif;
                                text-align: center;
                                font-size: 12px;
                                color: #ffffff;
                                padding-top: 8px;
                                padding-bottom: 20px;
                                padding-left: 30px;
                                padding-right: 30px;
                                background-color: #dc143c;


                              "
                            >
                              Mail us at
                              <a
                                style="color: white; font-size: 13px; text-decoration: none"
                                href="http://"
                                target="_blank"
                                >support@flyfarint.com
                              </a>
                              agency or Call us at 09606912912
                            </td>
                          </tr>

                          <tr>
                            <td
                              valign="top"
                              align="left"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #000000;
                                font-family: sans-serif;
                                text-align: left;
                                font-weight: bold;
                                font-size: 12px;
                                line-height: 18px;
                                color: #929090;
                              "
                            >

                            <p> <a
                                style="
                                  font-weight: bold;
                                  font-size: 12px;
                                  line-height: 15px;
                                  color: #222222;

                                "
                                href="https://www.flyfarint.com/terms"
                                >Terms & Conditions</a
                              >
                              <a
                                style="
                                  font-weight: bold;
                                  font-size: 12px;
                                  line-height: 15px;
                                  color: #222222;
                                  padding-left: 10px;
                                "
                                href="https://www.flyfarint.com/privacy"
                                >Privacy Policy</a
                              ></p>
                            </td>
                          </tr>
                          <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                font-family: sans-serif;
                                text-align: center;
                                padding-left: 20px;
                                font-weight: bold;
                                font-size: 12px;
                                line-height: 18px;
                                color: #929090;
                                padding-right: 20px;
                              "
                            >
                                <a href="https://www.facebook.com/FlyFarInternational/ "
                                ><img
                                  src="https://cdn.flyfarint.com/fb.png"
                                  width="25px"
                                  style="margin: 10px"
                              /></a>
                              <a href="http:// "
                                ><img
                                  src="https://cdn.flyfarint.com/lin.png"
                                  width="25px"
                                  style="margin: 10px"
                              /></a>
                              <a href="http:// "
                                ><img
                                  src="https://cdn.flyfarint.com/wapp.png "
                                  width="25px"
                                  style="margin: 10px"
                              /></a>
                            </td>
                          </tr>

                                    <tr>
                            <td
                              align="center"
                              valign="top"
                              style="
                                border-collapse: collapse;
                                border-spacing: 0;
                                color: #929090;
                                font-family: sans-serif;
                                text-align: center;
                                font-weight: 500;
                                font-size: 12px;
                                padding-top:5px;
                                padding-bottom:5px;
                                padding-left:10px;
                                padding-right: 10px;
                              "
                            >
                Ka 11/2A, Bashundhara R/A Road, Jagannathpur, Dhaka 1229
                 </td>
                          </tr>



                        </table>


                      </div>
                    </div>
                  </body>
                </html>';

            $mail = new PHPMailer();

            try {
                $mail->isSMTP();
                $mail->Host = 'b2b.flyfarint.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'booking@b2b.flyfarint.com';
                $mail->Password = '123Next2$';
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                //Recipients
                $mail->setFrom('booking@b2b.flyfarint.com', 'Fly Far International');
                $mail->addAddress("$agentEmail", "AgentId : $agentId");
                // $mail->addCC('otaoperation@flyfarint.com');
                // $mail->addCC('habib@flyfarint.com');
                // $mail->addCC('afridi@flyfarint.com');

                $mail->isHTML(true);
                $mail->Subject = "Booking Request Confirmation by Fly Far International";
                $mail->Body = $AgentMail;

                if (!$mail->Send()) {
                    
                }

            } catch (Exception $e) {
                $response['status'] = "error";
                $response['message'] = "Mail Doesn't Send";
            }
}
function SubAgentEmailSend($conn, $bookingId){

    $agentId = $_POST['agentId'];
    $subagentId  = $_POST['subagentId'];

    
    $WLSubAgentData = $conn->query("SELECT * FROM subagent where subagentId='$subagentId' AND agentId= '$agentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC)[0];
    
    if(!empty($result)){
        $subcompanyName = $WLSubAgentData['company'];
        $subcompanyEmail = $WLSubAgentData['email'];
    }else{
        $subcompanyName = '';
    }
    

    
    $WhiteLabelDataOwner = $conn->query("SELECT * FROM wl_content where agentId='$agentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC)[0];
    if(!empty($WhiteLabelDataOwner)){
        $agentCompanyName = $WhiteLabelDataOwner['company_name'];
       // $agentCompanyLogo = $WhiteLabelDataOwner['companyImage'];
        $agentCompanyEmail = $WhiteLabelDataOwner['email'];
        $agentCompanyPhone = $WhiteLabelDataOwner['phone'];
        $agentCompanyAddress = $WhiteLabelDataOwner['address'];
        $agentCompanyWebsiteLink = $WhiteLabelDataOwner['websitelink'];
        $agentCompanyFbLink = $WhiteLabelDataOwner['fb_link'];
        $agentCompanyLinkedinLink = $WhiteLabelDataOwner['linkedin_link'];
        $agentCompanyWhatsappNum = $WhiteLabelDataOwner['whatsapp_num'];
    }
         

            $subagentMail = '
            <!DOCTYPE html>
            <html lang="en">
                <head>
                <meta charset="UTF-8" />
                <meta http-equiv="X-UA-Compatible" content="IE=edge" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                <title>Deposit Request</title>
                </head>
                <body>
                <div class="div" style="width: 650px; height: 100vh; margin: 0 auto">
                    <div
                    style="
                        width: 650px;
                        height: 150px;
                        background: #FFA84D;
                    "
                    >
                    <table
                    border="0"
                    cellpadding="0"
                    cellspacing="0"
                    align="center"
                    style="
                    border-collapse: collapse;
                    border-spacing: 0;
                    padding: 0;
                    width: 650px;
                    border-radius: 10px;
                    "
                >
                    <tr>
                    <td
                        align="center"
                        valign="top"
                        style="
                        border-collapse: collapse;
                        border-spacing: 0;
                        color: #000000;
                        font-family: sans-serif;
                        font-weight: bold;
                        font-size: 20px;
                        line-height: 38px;
                        padding-top: 20px;
                        padding-bottom: 10px;

                        "
                    >
                        <a style="text-decoration: none; color:#ffffff; font-family: sans-serif; font-size: 25px; padding-top: 20px;" href="https://www.' . $agentCompanyWebsiteLink . '">' . $agentCompanyName . '</a>

                    </td>
                    </tr>
                </table>

                    <table
                        border="0"
                        cellpadding="0"
                        cellspacing="0"
                        align="center"
                        bgcolor="white"
                        style="
                        border-collapse: collapse;
                        border-spacing: 0;
                        padding: 0;
                        width: 550px;
                        "
                    >
                        <tr>
                        <td
                            align="center"
                            valign="top"
                            style="
                            border-collapse: collapse;
                            border-spacing: 0;
                            color: #000000;
                            font-family: sans-serif;
                            text-align: left;
                            padding-left: 20px;
                            font-weight: bold;
                            font-size: 19px;
                            line-height: 38px;
                            padding-top: 10px;
                            background-color: white;
                            "
                        >
                        Booking Confirmation
                        </td>
                        </tr>
                        <tr>
                        <td
                            align="center"
                            valign="top"
                            style="
                            border-collapse: collapse;
                            border-spacing: 0;
                            font-family: sans-serif;
                            text-align: left;
                            padding-left: 20px;
                            font-weight: bold;
                            padding-top: 15px;
                            font-size: 12px;
                            line-height: 18px;
                            color: #929090;
                            padding-right: 20px;
                            background-color: white;
                            "
                        >
                            Dear ' . $subcompanyName . ', Your New Booking Request has been placed, please issue your ticket before time limit, otherwise your ticket will be cancel autometically.
                        </td>
                        </tr>
                        <tr>
                        <td
                          align="center"
                          valign="top"
                          style="
                            border-collapse: collapse;
                            border-spacing: 0;
                            font-family: sans-serif;
                            text-align: left;
                            padding-left: 20px;
                            font-weight: bold;
                            padding-top: 5px;
                            font-size: 12px;
                            line-height: 18px;
                            color: #2564B8;
                            padding-right: 20px;
                            background-color: white;
                          "
                        >
                         Booking ID: <span>' . $bookingId . '</span>
                      </tr>

                        <tr>
                        <td
                            align="center"
                            valign="top"
                            style="
                            border-collapse: collapse;
                            border-spacing: 0;
                            color: #000000;
                            font-family: sans-serif;
                            text-align: left;
                            padding-left: 20px;
                            font-weight: bold;
                            padding-top: 20px;
                            font-size: 13px;
                            line-height: 18px;
                            color: #929090;
                            padding-top: 20px;
                            width: 100%;
                            background-color: white;
                            "
                        >
                            If you have any questions, just contact us we are always happy to
                            help you out.
                        </td>
                        </tr>

                        <tr>
                        <td
                            align="center"
                            valign="top"
                            style="
                            border-collapse: collapse;
                            border-spacing: 0;
                            color: #000000;
                            font-family: sans-serif;
                            text-align: left;
                            padding-left: 20px;
                            font-weight: bold;
                            padding-top: 20px;
                            font-size: 13px;
                            line-height: 18px;
                            color: #929090;
                            padding-top: 20px;
                            width: 100%;
                            background-color: white;
                            "
                        >
                            Sincerely,
                        </td>
                        </tr>

                        <tr>
                        <td
                            align="center"
                            valign="top"
                            style="
                            border-collapse: collapse;
                            border-spacing: 0;
                            color: #000000;
                            font-family: sans-serif;
                            text-align: left;
                            padding-left: 20px;
                            font-weight: bold;
                            font-size: 13px;
                            line-height: 18px;
                            color: #929090;
                            width: 100%;
                            background-color: white;
                            padding-bottom: 20px;
                            "
                        >
                        ' . $agentCompanyName . '
                        </td>
                        </tr>

                        <tr>
                        <td
                            align="center"
                            valign="top"
                            style="
                            border-collapse: collapse;
                            border-spacing: 0;
                            color: #ffffff;
                            font-family: sans-serif;
                            text-align: center;
                            font-weight: 600;
                            font-size: 14px;
                            color: #ffffff;
                            padding-top: 15px;
                            background-color: #2564B8;
                            "
                        >
                            Need more help?
                        </td>
                        </tr>

                        <tr>
                        <td
                            align="center"
                            valign="top"
                            style="
                            border-collapse: collapse;
                            border-spacing: 0;
                            color: #ffffff;
                            font-family: sans-serif;
                            text-align: center;
                            font-size: 12px;
                            color: #ffffff;
                            padding-top: 8px;
                            padding-bottom: 20px;
                            padding-left: 30px;
                            padding-right: 30px;
                            background-color: #2564B8;
                            "
                        >
                            Mail us at
                            <a
                            style="color: white; font-size: 13px; text-decoration: none"
                            href="http://"
                            target="_blank"
                            > ' . $agentCompanyEmail . '
                            </a>
                            agency or Call us at ' . $agentCompanyPhone . '
                        </td>
                        </tr>

                        <tr>
                        <td
                            valign="top"
                            align="left"
                            style="
                            border-collapse: collapse;
                            border-spacing: 0;
                            color: #000000;
                            font-family: sans-serif;
                            text-align: left;
                            font-weight: bold;
                            font-size: 12px;
                            line-height: 18px;
                            color: #929090;
                            "
                        >
                            <p>
                            <a
                                style="
                                font-weight: bold;
                                font-size: 12px;
                                line-height: 15px;
                                color: #222222;
                                "
                                href="https://www.' . $agentCompanyWebsiteLink . '/termsandcondition"
                                >Tearms & Conditions</a
                            >
                            <a
                                style="
                                font-weight: bold;
                                font-size: 12px;
                                line-height: 15px;
                                color: #222222;
                                padding-left: 10px;
                                "
                                href="https://www.' . $agentCompanyWebsiteLink . '/privacypolicy"
                                >Privacy Policy</a
                            >
                            </p>
                        </td>
                        </tr>
                        <tr>
                        <td
                            align="center"
                            valign="top"
                            style="
                            border-collapse: collapse;
                            border-spacing: 0;
                            font-family: sans-serif;
                            text-align: center;
                            padding-left: 20px;
                            font-weight: bold;
                            font-size: 12px;
                            line-height: 18px;
                            color: #929090;
                            padding-right: 20px;
                            "
                        >
                            <a href="' . $agentCompanyFbLink . ' "
                            ><img
                                src="https://cdn.flyfarint.com/fb.png"
                                width="25px"
                                style="margin: 10px"
                            /></a>
                            <a href="' . $agentCompanyLinkedinLink . ' "
                            ><img
                                src="https://cdn.flyfarint.com/lin.png"
                                width="25px"
                                style="margin: 10px"
                            /></a>
                            <a href="' . $agentCompanyWhatsappNum . ' "
                            ><img
                                src="https://cdn.flyfarint.com/wapp.png "
                                width="25px"
                                style="margin: 10px"
                            /></a>
                        </td>
                        </tr>

                        <tr>
                        <td
                            align="center"
                            valign="top"
                            style="
                            border-collapse: collapse;
                            border-spacing: 0;
                            color: #929090;
                            font-family: sans-serif;
                            text-align: center;
                            font-weight: 500;
                            font-size: 12px;
                            padding-top: 5px;
                            padding-bottom: 5px;
                            padding-left: 10px;
                            padding-right: 10px;
                            "
                        >
                            ' . $agentCompanyAddress . '
                        </td>
                        </tr>
                    </table>
                    </div>
                </div>
                </body>
            </html>

            ';

            $mail = new PHPMailer();

            try {
                $mail->isSMTP();
                $mail->Host = 'b2b.flyfarint.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'bookingwl@mailservice.center';
                $mail->Password = '123Next2$';
                $mail->SMTPSecure = 'ssl';
                $mail->Port = 465;

                //Recipients
                $mail->setFrom('bookingwl@mailservice.center', $agentCompanyName);
                $mail->addAddress("$subcompanyEmail", "SubAgentId : $subagentId");
                $mail->addCC('otaoperation@flyfarint.com');
                // $mail->addCC('habib@flyfarint.com');
                // $mail->addCC('afridi@flyfarint.com');

                $mail->isHTML(true);
                $mail->Subject = "Booking Confirmation by $agentCompanyName";
                $mail->Body = $subagentMail;
                if (!$mail->Send()) {
                    echo "Mailer Error: " . $mail->ErrorInfo;
                } else {
                }
            } catch (Exception $e) {
                $response['status'] = "error";
                $response['message'] = "Mail Doesn't Send";
            }

}

function OwnerEmailSend($companyname, $bookingId, $BookingPNR, $Booked){
    
    $createdTime  = date("Y-m-d H:i:s");

    $System = $_POST['bookingInfo']["system"];
    $From = $_POST['bookingInfo']["from"];
    $To = $_POST['bookingInfo']["to"];
    $Airlines = $_POST['bookingInfo']["airlines"];
    $Type = $_POST['bookingInfo']["tripType"];
    $Pnr = $BookingPNR;
    $Pax = $_POST['bookingInfo']["pax"];
    $netCost = $_POST['bookingInfo']["netcost"];
    $travelDate= $_POST['flightPassengerData']['segments'][0][0]['DepTime'];

    $OwnerMail = '
              <!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Deposit Request
</title>
  </head>
  <body>
    <div
      class="div"
      style="
        width: 650px;
        height: 70vh;
        margin: 0 auto;
      "
    >
      <div
        style="
          width: 650px;
          height: 200px;
          background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
          border-radius: 20px 0px  20px  0px;

        "
      >
        <table
          border="0"
          cellpadding="0"
          cellspacing="0"
          align="center"
          style="
            border-collapse: collapse;
            border-spacing: 0;
            padding: 0;
            width: 650px;
            border-radius: 10px;

          "
        >
          <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                font-weight: bold;
                font-size: 20px;
                line-height: 38px;
                padding-top: 30px;
                padding-bottom: 10px;
              "
            >
              <a href="https://www.flyfarint.com/"
                ><img
                src="https://cdn.flyfarint.com/logo.png"
                  width="130px"
              /></a>

            </td>
          </tr>
        </table>

        <table
          border="0"
          cellpadding="0"
          cellspacing="0"
          align="center"
          bgcolor="white"
          style="
            border-collapse: collapse;
            border-spacing: 0;
            padding: 0;
            width: 550px;
          "
        >
          <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                font-size: 19px;
                line-height: 38px;
                padding-top: 20px;
                background-color: white;


              "
            >

            Booking Request

        </td>
          </tr>
          <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 15px;
                font-size: 12px;
                line-height: 18px;
                color: #929090;
                padding-right: 20px;
                background-color: white;

              "
            >
            Dear Fly Far International, We send you new booking request at ' . $createdTime . '
         </td>
          </tr>

                    <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 20px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                padding-top: 20px;
                width: 100%;
                background-color: white;
              "
            >
            <span style="color:#003566 ;">  ' . $Booked . ' </span>
            </td>
          </tr>



            <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 20px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                padding-top: 20px;
                width: 100%;
                background-color: white;

              "
            >
              Booking Id:
              <a style="color: #003566; padding-right: 12px" href="http://" target="_blank"
                >' . $bookingId . '</a
              >
              System:
              <span style="color: #003566; padding-right: 12px" href="http://" target="_blank"
                >' . $System . '</span
              >
              System PNR:
              <span style="color: #003566" href="http://" target="_blank"
                >' . $Pnr . '</span
              >
            </td>
          </tr>

                              <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 20px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                padding-top: 20px;
                width: 100%;
                background-color: white;

              "
            >
               Destination: <span style="color: #dc143c">' . $From . ' to ' . $To . ' ' . $Type . '</span>
            </td>
          </tr>

                              <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 20px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                padding-top: 10px;
                width: 100%;
                background-color: white;

              "
            >
               Travel Date:  <span style="color: #dc143c">' . $travelDate . '	</span>
            </td>
          </tr>
                                        <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 20px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                padding-top: 10px;
                width: 100%;
                background-color: white;

              "
            >
               Airline: <span style="color: #dc143c">' . $Airlines . '</span>
            </td>
          </tr>

                                                  <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 20px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                padding-top: 10px;
                width: 100%;
                background-color: white;

              "
            >
               Pax:  <span style="color: #dc143c">' . $Pax . '</span>
            </td>
          </tr>

                                                            <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 20px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                padding-top: 10px;
                width: 100%;
                background-color: white;

              "
            >
               Cost:  <span style="color: #dc143c">' . $netCost . '
</span>
            </td>
          </tr>



             <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                padding-top: 20px;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                padding-top: 20px;
                width: 100%;
                background-color: white;

              "
            >
               Sincerely,

            </td>
          </tr>

             <tr>
            <td
              align="center"
              valign="top"
              style="
                border-collapse: collapse;
                border-spacing: 0;
                color: #000000;
                font-family: sans-serif;
                text-align: left;
                padding-left: 20px;
                font-weight: bold;
                font-size: 13px;
                line-height: 18px;
                color: #929090;
                width: 100%;
                background-color: white;
                padding-bottom: 20px

              "
            >
              ' . $companyname . '

            </td>
          </tr>

      </div>
    </div>
  </body>
</html>

';

            $mail1 = new PHPMailer();

            try {
                $mail1->isSMTP();
                $mail1->Host = 'b2b.flyfarint.com';
                $mail1->SMTPAuth = true;
                $mail1->Username = 'booking@b2b.flyfarint.com';
                $mail1->Password = '123Next2$';
                $mail1->SMTPSecure = 'ssl';
                $mail1->Port = 465;

                //Recipients
                $mail1->setFrom('booking@b2b.flyfarint.com', $companyname);
                $mail1->addAddress("otaoperation@flyfarint.com", "Booking");
                // $mail1->addCC('habib@flyfarint.com');
                // $mail1->addCC('afridi@flyfarint.com');

                $mail1->isHTML(true);
                $mail1->Subject = "New Booking Request Confirmation for $companyname";
                $mail1->Body = $OwnerMail;

                if (!$mail1->Send()) {
                    $response['status'] = "success";
                    $response['BookingId'] = "$bookingId";
                    $response['message'] = "Booking Successfully";
                    $response['error'] = "Email Not Send Successfully";
                    echo json_encode($response);
                } else {
                    $response['status'] = "success";
                    $response['BookingId'] = "$bookingId";
                    $response['BookingPNR'] = $BookingPNR;
                    $response['message'] = "Booking Successfully";
                    echo json_encode($response);
                }

            } catch (Exception $e) {
                $response['status'] = "error";
                $response['message'] = "Mail Doesn't Send";
            }
}