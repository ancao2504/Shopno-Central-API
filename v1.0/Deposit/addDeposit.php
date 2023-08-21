<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\SMTP;
// use PHPMailer\PHPMailer\Exception; 

// require("../vendor/autoload.php");
if (
    array_key_exists('agentId', $_GET) && array_key_exists('sender', $_GET) && array_key_exists('ref', $_GET)
    && array_key_exists('receiver', $_GET) && array_key_exists('way', $_GET) && array_key_exists('method', $_GET)
    && array_key_exists('transactionId', $_GET) && array_key_exists('amount', $_GET) && array_key_exists('staffId', $_GET)
) {

    $agentId = $_GET['agentId'];
    $sender = $_GET['sender'];
    $reciever = $_GET['receiver'];
    $way = $_GET['way'];
    $method = $_GET['method'];
    $transactionId = $_GET['transactionId'];
    $amount = $_GET['amount'];
    $ckDate = $_GET['ckDate'];
    $ref = $_GET['ref'];

    $time = date("Y-m-d H:i:s");
    $DateTime = date("D d M Y h:i A");

    $staffId = $_GET['staffId'];

    $data = json_decode(file_get_contents("php://input"), true);

    $fileName  =  $_FILES['file']['name'];
    $tempPath  =  $_FILES['file']['tmp_name'];
    $fileSize  =  $_FILES['file']['size'];



    $DepositId = "";
    $sql = "SELECT * FROM deposit_request ORDER BY depositId DESC LIMIT 1";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $outputString = preg_replace('/[^0-9]/', '', $row["depositId"]);
            $number = (int)$outputString + 1;
            $DepositId = "STD$number";
        }
    } else {
        $DepositId = "STD1000";
    }



    if (isset($agentId)) {
        $sql1 = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
        $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

        if (!empty($row1)) {
            $agentEmail = $row1['email'];
            $companyname = $row1['company'];
            $Agentname = $row1['name'];
        }
    }


    $lastAmount;
    $sql2 = mysqli_query($conn, "SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' 
            ORDER BY id DESC LIMIT 1");
    $row2 = mysqli_fetch_array($sql2, MYSQLI_ASSOC);
    if (!empty($row2)) {
        $lastAmount = $row2['lastAmount'];
    } else {
        $lastAmount = 0;
    }

    $newBalance = $lastAmount + $amount;


    $staffsql2 = mysqli_query($conn, "SELECT * FROM `staffList` where agentId = '$agentId' AND staffId='$staffId'");
    $staffrow2 = mysqli_fetch_array($staffsql2, MYSQLI_ASSOC);
    if (!empty($staffrow2)) {
        $staffName = $staffrow2['name'];
        $Message = "Deposit Request By: $staffName, $companyname";
    } else {
        $Message = "Deposit Request By: $companyname";
        $staffName = "Agent";
    }


    if (empty($fileName)) {
        $errorMSG = json_encode(array("message" => "please select image", "status" => false));
        echo $errorMSG;
    } else {
        $upload_path = "../../asset/Agent/$agentId/Deposit/"; // set upload folder path 

        if (!file_exists($upload_path)) {
            mkdir($upload_path, 0777, true);
        }

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension

        // valid image extensions
        $valid_extensions = array('jpeg', 'jpg', 'png', 'pdf', 'PDF', 'JPG', 'PNG', 'JPEG');

        $renameFile = "$way-$method-$time-$amount.$fileExt";

        $attach = "$upload_path/" . $renameFile;

        // allow valid image file formats
        if (in_array($fileExt, $valid_extensions)) {
            //check file not exist our upload folder path
            if (!file_exists($upload_path . $fileName)) {
                // check file size '5MB'
                if ($fileSize < 5000000) {
                    move_uploaded_file($tempPath, $upload_path . $renameFile);
                } else {
                    $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => false));
                    echo $errorMSG;
                }
            } else {
                // check file size '5MB'
                if ($fileSize < 5000000) {
                    move_uploaded_file($tempPath, $upload_path . $renameFile);
                } else {
                    $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => false));
                    echo $errorMSG;
                }
            }
        } else {
            $errorMSG = json_encode(array("message" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed", "status" => false));
            echo $errorMSG;
        }
    }

    // if no error caused, continue ....
    if (!isset($errorMSG)) {
        $attachment = $renameFile;

        $sql = "INSERT INTO `deposit_request`(
                `agentId`,
                `staffId`,
                `depositId`,
                `sender`,
                `reciever`,
                `paymentway`,
                `paymentmethod`,
                `transactionId`,
                `amount`,
                `ref`,
                `chequeIssueDate`,
                `attachment`,
                `platform`,
                `status`,
                `depositBy`,
                `createdAt`
                )
                VALUES( 
                '$agentId',
                '$staffId',
                '$DepositId',
                '$sender',
                '$reciever',
                '$way',
                '$method',
                '$transactionId',
                '$amount',
                '$ref',
                '$ckDate',
                '$attachment',
                'B2B',
                'pending',
                '$staffName',
                '$time')";

        if ($conn->query($sql) === TRUE) {


            $subjectAgent = $headerAgent = "Deposit Request Confirmation";
            $subjectAdmin = $headerAdmin = "New Deposit Request";
            $property = "Deposit ID: ";
            $data = $DepositId;
            $adminMessage = "We Send you new deposit request amount of $amount BDT.";
            $agentMessage = " Your new deposit request amount of '.$amount.' BDT has been placed, 
            please wait for while, for added your deposit amount into your wallet.";

            sendToAdmin($subjectAdmin, $adminMessage, $agentId, $headerAdmin, $property, $data);
            sendToAgent($subjectAgent, $agentMessage, $agentId, $headerAgent, $property, $data);

            $response['status'] = "success";
            $response['DepositId'] = "$DepositId";
            $response['message'] = "Deposit Request Successfully";
        }

        echo json_encode($response);
    }
}
