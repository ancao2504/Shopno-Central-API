<?php

include "../config.php";
include "../functions.php";



header("Access-Control-Allow-Origin: *");

header("Content-Type: application/json; charset=UTF-8");

header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");

header("Access-Control-Max-Age: 3600");

header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $company_name = $_POST['companyname'];

    $companyaddress = $_POST['companyaddress'];

    $firstName = $_POST['fname'];

    $lastName = $_POST['lname'];

    $userEmail = $_POST['contactpersonemail'];

    $mypassword = $_POST['password'];

    $phone = $_POST['contactpersonphonenumber'];

    $country = $_POST['country'];

    $city = $_POST['city'];



    $name = $firstName . " " . $lastName;

    $uId = sha1(md5(time()));



    $createdAt = date("Y-m-d H:i:s");



    $AgentId = "";

    $sql = "SELECT * FROM agent ORDER BY agentId DESC LIMIT 1";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['agentId'] != "") {
            $outputString = preg_replace('/[^0-9]/', '', $row["agentId"]);
            $number = (int) $outputString + 1;
            $AgentId = "STA$number";
        } else {
            $AgentId = "STA1000";
        }
    } else {

        $AgentId = "STA1000";
    }



    $checkUser = "SELECT * FROM agent WHERE platform='B2B' AND email='$userEmail' OR phone ='$phone' OR company='$company_name'";

    $result = mysqli_query($conn, $checkUser);




    $checkStaff = "SELECT * FROM staffList WHERE email = '$userEmail'";

    $resultStaff = mysqli_query($conn, $checkStaff);



    if (mysqli_num_rows($result) <= 0 && mysqli_num_rows($resultStaff) > 0) {

        $response['status'] = "error";

        $response['message'] = "User Already Exists as Staff";
    } else if (mysqli_num_rows($result) > 0 && mysqli_num_rows($resultStaff) > 0) {

        $response['status'] = "error";

        $response['message'] = "User Already Exists As agent or may be on staffList";
    } else if (mysqli_num_rows($result) > 0 && mysqli_num_rows($resultStaff) <= 0) {

        while ($row = $result->fetch_assoc()) {

            $Phone = $row['phone'];

            $Company = $row['company'];

            if ($row['email'] == $userEmail) {

                $response['status'] = "error";

                $response['message'] = "Email Already Exists";
            } else if ($row['phone'] == $phone) {

                $response['status'] = "error";

                $response['message'] = "Phone Number Registered to Another User";
            } else if ($row['company'] == $company_name) {

                $response['status'] = "error";

                $response['message'] = "Company Name Already Registered";
            }
        }
    } else if (mysqli_num_rows($result) <= 0 && mysqli_num_rows($resultStaff) <= 0) {


        $tinImagename = "tinImg";
        $tinAcceptablesize = 5000000;
        $tinFolder = "Agent/$AgentId/tinImg";
        $tinFileName = $_FILES["tinImg"]["name"];
        $tinNewFileName = "tinImg";

        $bSImagename = "bankStatement";
        $bSAcceptablesize = 5000000;
        $bSFolder = "Agent/$AgentId/bankStatement";
        $bSFileName = $_FILES["bankStatement"]["name"];
        $bSNewFileName = "bankStatement";

        $nidImagename = "nid";
        $nidAcceptablesize = 5000000;
        $nidFolder = "Agent/$AgentId/nid";
        $nidFileName = $_FILES["nid"]["name"];
        $nidNewFileName = "nid";

        $tinUrl = uploadImage($tinImagename, $tinAcceptablesize, $tinFolder, $tinFileName, $tinNewFileName);
        $bSUrl = uploadImage($bSImagename, $bSAcceptablesize, $bSFolder, $bSFileName, $bSNewFileName);
        $nidUrl = uploadImage($nidImagename, $nidAcceptablesize, $nidFolder, $nidFileName, $nidNewFileName);

        $sql = "INSERT INTO `agent`(

                `agentId`,

                `agentUid`,

                `name`,

                `email`,

                `password`,

                `phone`,

                `country`, 

                `city`,

                `status`,

                `company`,

                `platform`,

                `companyadd`,

                `joinAt`,

                `tinImg`,

                `nidImg`,

                `bankStatement`

            )

            VALUES(

                '$AgentId',

                '$uId',

                '$name',

                '$userEmail',

                '$mypassword',

                '$phone',

                '$country',

                '$city',

                'pending',

                '$company_name',

                'B2B',

                '$companyaddress',

                '$createdAt',
                
                '$tinUrl',

                '$nidUrl',

                '$bSUrl'

            )";



        if ($conn->query($sql) === true) {

            $response['agentId'] = $AgentId;

            $response['status'] = "success";

            $response['message'] = "Registration Successful";
        } else {

            $response['status'] = "error";

            $response['message'] = "Registration Failed";
        }
    }



    echo json_encode($response);
}
