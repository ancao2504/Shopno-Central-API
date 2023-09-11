<?php

require '../../config.php';
require '../../emailfunction.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $_POST = json_decode(file_get_contents('php://input'), true);

    $depositId = $_POST['depositId'];
    $agentId = $_POST['agentId'];
    $actionBy = $_POST['actionBy'];

    //Data

    $data = ($conn->query("SELECT * FROM deposit_request WHERE agentId='$agentId' AND depositId='$depositId' AND platform='B2B'")->fetch_all(MYSQLI_ASSOC))[0];

    if (!empty( $data)) {

        $id = $data['id'];
        $agentId = $data['agentId'];
        $depositId = $data['depositId'];
        $transactionId = $data['transactionId'];
        $staffId = $data['staffId'];
        $ref = $data['ref'];
        $amount = $data['amount'];
        $paymentwaymethod = $data['paymentmethod'];
        $paymentway = $data['paymentway'];
        $sender = $data['sender'];
        $status = $data['status'];
        $reciever = $data['reciever'];
        $chequeIssueDate = $data['chequeIssueDate'];
        $createdAt = date("Y-m-d H:i:s");


        if ($status == "pending") {
          

            //Agent Info
            $agentdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `agent` where agentId='$agentId' AND platform='B2B'"));
            $companyName = $agentdata['company'];
            $companyEmail = $agentdata['email'];

            //staff Info
            $staffName = "";
            $staffsql = mysqli_query($conn, "SELECT * FROM `staffList` where staffId='$staffId' AND agentId='$agentId'");
            $staffdata = mysqli_fetch_array($staffsql);
            if (isset($staffdata['name'])) {
                $staffName = $staffdata['name'];
            } else {
                $staffName = "Agent";
            }

            if (empty($staffdata) && !empty($agentId)) {
                $Message = "Approved By: Flyway International";
            } else if (!empty($staffdata) && !empty($agentId)) {
                $Message = "Approved By: $actionBy, Flyway International";
            }

            $DateTime = date("D d M Y h:i A");

            //Last Amount
            $amountsql = "SELECT lastAmount, deposit FROM `agent_ledger` WHERE agentId='$agentId' AND platform='B2B' ORDER BY id DESC LIMIT 1";
            $result1 = mysqli_query($conn, $amountsql);
            $data1 = mysqli_fetch_array($result1);

            $deposit = $data1['deposit'];

            $lastAmount = 0;
            if (!empty($data1['lastAmount'])) {
                $lastAmount = $data1['lastAmount'];
            } else {
                $lastAmount = 0;
            }

            // $lastAmount = $data1['lastAmount'];

            $afterDeposit = $lastAmount + $deposit;
            $newAmount = $lastAmount + $amount;

            // if ($newAmount >= 0) {
            //     $conn->query("UPDATE `agent` SET `credit`='0' where agentId='$agentId' AND platform='B2B'");
            // }

            $createdTime = date("Y-m-d H:i:s");

            $sql_query = "INSERT INTO `agent_ledger`(`agentId`,`staffId`,`deposit`, `lastAmount`,`details`, `transactionId`,`platform`,`reference`,`createdAt`, `actionBy`)
                    VALUES ('$agentId','$staffId','$amount','$newAmount','$amount TK Deposit By $staffName successfully','$transactionId','B2B','$depositId','$createdAt', '$actionBy')";


            //echo($newAmount);
            if ($conn->query($sql_query) === true) {
                $conn->query("UPDATE deposit_request SET status='approved', approvedBy='$actionBy', actionAt='$createdTime'  WHERE agentId='$agentId' AND depositId='$depositId' ");
                $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`platform`,`actionBy`, `actionAt`)
              VALUES ('$depositId','$agentId','Approved','Deposited $amount','B2B','$actionBy','$createdTime')");

                $agentSubject = $agentHeader = "Deposit Request Approve";
                $adminSubject = $adminHeader = "Deposit Request Approved";
                $property = "Deposit ID: ";
                $data = $depositId;
                $adminMessage = "We Send you new deposit request amount of $amount BDT, 
                Which has been approved.";
                $agentMessage = " Your new deposit request amount of amount $amount BDT 
                has been accepeted, Thank you";

                sendToAdmin($adminSubject, $adminMessage, $agentId, $adminHeader, $property, $data);
                sendToAgent($agentSubject, $agentMessage, $agentId, $agentHeader, $property, $data);


                $response['status'] = "success";
                $response['message'] = "Deposit Approved Successful";
               
            }   

        } else if ($status == "approved") {
          $response['status'] = "error";
          $response['message'] = "Deposit Already Approved";
        }else if ($status == "rejected") {
          $response['status'] = "error";
          $response['message'] = "Deposit Already Rejected";
        }
    } else {
        $response['status'] = "error";
        $response['message'] = "Agent Not Found";
       

    }
    echo json_encode($response);

}
$conn->close();
?>



