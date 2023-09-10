<?php

class OrderTransaction
{

    public function getRecordQuery($tran_id)
    {
        $sql = "select * from orders WHERE transaction_id='" . $tran_id . "'";
        return $sql;
    }

    public function saveTransactionQuery($post_data, $platform)
    {
        $name = $post_data['cus_name'];
        $email = $post_data['cus_email'];
        $phone = $post_data['cus_phone'];
        $transaction_amount = $post_data['total_amount'];
        $address = $post_data['cus_add1'];
        $transaction_id = $post_data['tran_id'];
        $currency = $post_data['currency'];

        if (array_key_exists("agentId", $post_data)) {
            $agentId = $post_data['agentId'];
            $sql = "INSERT INTO orders (agentId,name, email, phone, amount, address, status, transaction_id,currency, operator, platform)
                            VALUES ('$agentId','$name', '$email', '$phone','$transaction_amount','$address','Pending', '$transaction_id','$currency', 'SSLCOMMERZ', '$platform')";
        } else if (array_key_exists("userId", $post_data)) {
            $userId = $post_data['userId'];
            $sql = "INSERT INTO orders (userId, name, email, phone, amount, address, status, transaction_id,currency, operator, platform)
            VALUES ('$userId','$name', '$email', '$phone','$transaction_amount','$address','Pending', '$transaction_id','$currency', 'SSLCOMMERZ', '$platform')";
        }

        return $sql;
    }

    public function updateTransactionQuery($tran_id, $type = 'Success')
    {
        $sql = "UPDATE orders SET status='$type' WHERE transaction_id='$tran_id'";

        return $sql;
    }


    public function saveB2BTransaction($conn, $savedata)
    {
        $tran_id = $savedata['tran_id'];
        $bank_trxId = $savedata['bank_tran_id'];
        $amount = $savedata['store_amount'];
        $agentId = $savedata['agentId'];

        $DuplicateItem = $conn->query("SELECT * from deposit_request where transactionId='$tran_id'")->num_rows;

        // print($DuplicateItem);
        if ($DuplicateItem == 0) {

            $DepositId = "";
            $sql = "SELECT * FROM deposit_request ORDER BY depositId DESC LIMIT 1";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["depositId"]);
                    $number = (int) $outputString + 1;
                    $DepositId = "STD$number";
                }
            } else {
                $DepositId = "STD1000";
            }

            $agentdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'"), MYSQLI_ASSOC);

            if (!empty($agentdata)) {
                $CompanyName = $agentdata["company"];
                $CompanyEmail = $agentdata["email"];
            }

            $createdAt = date('Y-m-d H:i:s');

            //Last Amount     
            $amountsql = "SELECT lastAmount FROM `agent_ledger` WHERE agentId='$agentId' ORDER BY id DESC LIMIT 1";
            $result1 = mysqli_query($conn, $amountsql);
            $data1 = mysqli_fetch_array($result1);

            $lastAmount = 0;
            if (!empty($data1['lastAmount'])) {
                $lastAmount = $data1['lastAmount'];
            } else {
                $lastAmount = 0;
            }

            $newAmount = $lastAmount + $amount;
            echo ($agentId);

            $sql = "INSERT INTO `deposit_request`(
                    `agentId`,
                    `depositId`,
                    `sender`,
                    `reciever`,
                    `paymentway`,
                    `paymentmethod`,
                    `transactionId`,
                    `amount`,
                    `ref`,
                    `status`,
                    `createdAt`,
                    `platform`, 
                    `actionAt`)
                    VALUES( 
                    '$agentId',
                    '$DepositId',
                    '$CompanyName',
                    'ST Marchant',
                    'sslcommerce',
                    'SSL',
                    '$tran_id',
                    '$amount',
                    '$bank_trxId',
                    'approved',
                    '$createdAt',
                    'B2B',
                    '$createdAt')";
            // echo ($sql);
            if ($conn->query($sql) === TRUE) {
                $conn->query("INSERT INTO `agent_ledger`(`agentId`,`deposit`, `lastAmount`,`details`, `transactionId`,`reference`,`createdAt`, `platform`)
                        VALUES ('$agentId','$amount','$newAmount','$amount TK Deposit successfully SSL Commerce - PaymentId-$bank_trxId','$tran_id','$DepositId','$createdAt', 'B2B')");

                //send email
                $adminMessage = "We sent you new deposit request amount of $amount BDT, Which has been approved.";
                $agentMessage = "Your new deposit request amount of $amount BDT has been accepeted, Thank you";
                $subject = "Deposit Request Approved";
                $header = $subject;
                $property = "Deposit ID: ";
                $data = $DepositId;

                sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);
                sendToAgent($subject, $agentMessage, $agentId, $header, $property, $data);
                ///////////////////////////

                $response['status'] = 'success';
                $response['message'] = 'Deposit Successfully Done';
                echo json_encode($response);

                //redirect
                // header("Location: https://b2b.shopnotour.com/dashboard/depositreq/successful");
                header("Location: http://localhost:3002/dashboard/depositreq/successful");
                ////////////////////////
            }
        } else {
            // header("Location: https://b2b.shopnotour.com/dashboard/depositreq/fail");
            header("Location: http://localhost:3002/dashboard/depositreq/fail");
            exit();
        }
    }
    public function saveB2CTransaction($conn, $savedata)
    {
        $tran_id = $savedata['tran_id'];
        $bank_trxId = $savedata['bank_tran_id'];
        $amount = $savedata['store_amount'];
        $userId = $savedata['userId'];
        $createdAt = date('Y-m-d H:i:s');

        $DuplicateItem = $conn->query("SELECT * from deposit_request where transactionId='$tran_id'")->num_rows;

        // print($DuplicateItem);
        if ($DuplicateItem == 0) {

            $DepositId = "";
            $sql = "SELECT * FROM deposit_request ORDER BY depositId DESC LIMIT 1";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["depositId"]);
                    $number = (int) $outputString + 1;
                    $DepositId = "STD$number";
                }
            } else {
                $DepositId = "STD1000";
            }

            $userData = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM agent WHERE userId='$userId'"), MYSQLI_ASSOC);


            //Last Amount     
            $amountsql = "SELECT lastAmount FROM `agent_ledger` WHERE userId='$userId' ORDER BY id DESC LIMIT 1";
            $result1 = mysqli_query($conn, $amountsql);
            $data1 = mysqli_fetch_array($result1);

            $lastAmount = 0;
            if (!empty($data1['lastAmount'])) {
                $lastAmount = $data1['lastAmount'];
            } else {
                $lastAmount = 0;
            }

            $newAmount = $lastAmount + $amount;

            $sql = "INSERT INTO `deposit_request`(
                    `userId`,
                    `depositId`,
                    `sender`,
                    `reciever`,
                    `paymentway`,
                    `paymentmethod`,
                    `transactionId`,
                    `amount`,
                    `ref`,
                    `status`,
                    `createdAt`,
                    `platform`,
                    `actionAt`)
                    VALUES( 
                    '$userId',
                    '$DepositId',
                    '$userId',
                    'ST Marchant',
                    'sslcommerce',
                    'SSL',
                    '$tran_id',
                    '$amount',
                    '$bank_trxId',
                    'approved',
                    '$createdAt',
                    'B2C',
                    '$createdAt')";



            if ($conn->query($sql) === TRUE) {
                $conn->query("INSERT INTO `agent_ledger`(`userId`,`deposit`, `lastAmount`,`details`, `transactionId`,`reference`,`createdAt`, `platform`)
                        VALUES ('$userId','$amount','$newAmount','$amount TK Deposit successfully SSL Commerce - PaymentId-$bank_trxId','$tran_id','$DepositId','$createdAt', 'B2C')");

                //send email
                // $adminMessage = "We sent you new deposit request amount of $amount BDT, Which has been approved.";
                // $agentMessage = "Your new deposit request amount of $amount BDT has been accepeted, Thank you";
                // $subject = "Deposit Request Approved";
                // $header = $subject;
                // $property = "Deposit ID: ";
                // $data = $DepositId;

                // sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);
                // sendToAgent($subject, $agentMessage, $agentId, $header, $property, $data);
                ///////////////////////////

                $response['status'] = 'success';
                $response['message'] = 'Deposit Successfully Done';
                echo json_encode($response);

                //redirect
                // header("Location: https://shopnotour.com/dashboard/depositreq/successful");
                header("Location: http://localhost:3002/dashboard/depositreq/successful");
                ////////////////////////
            }
        } else {
            // header("Location: https://shopnotour.com/dashboard/depositreq/fail");
            header("Location: http://localhost:3002/dashboard/depositreq/fail");
            exit();
        }
    }
    public function saveB2CAppTransaction($conn, $savedata)
    {
        $tran_id = $savedata['tran_id'];
        $bank_trxId = $savedata['bank_tran_id'];
        $amount = $savedata['store_amount'];
        $userId = $savedata['userId'];
        $createdAt = date('Y-m-d H:i:s');

        $DuplicateItem = $conn->query("SELECT * from deposit_request where transactionId='$tran_id'")->num_rows;

        // print($DuplicateItem);
        if ($DuplicateItem == 0) {

            $DepositId = "";
            $sql = "SELECT * FROM deposit_request ORDER BY depositId DESC LIMIT 1";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $outputString = preg_replace('/[^0-9]/', '', $row["depositId"]);
                    $number = (int) $outputString + 1;
                    $DepositId = "STD$number";
                }
            } else {
                $DepositId = "STD1000";
            }

            $userData = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM agent WHERE userId='$userId'"), MYSQLI_ASSOC);


            //Last Amount     
            $amountsql = "SELECT lastAmount FROM `agent_ledger` WHERE userId='$userId' ORDER BY id DESC LIMIT 1";
            $result1 = mysqli_query($conn, $amountsql);
            $data1 = mysqli_fetch_array($result1);

            $lastAmount = 0;
            if (!empty($data1['lastAmount'])) {
                $lastAmount = $data1['lastAmount'];
            } else {
                $lastAmount = 0;
            }

            $newAmount = $lastAmount + $amount;

            $sql = "INSERT INTO `deposit_request`(
                    `userId`,
                    `depositId`,
                    `sender`,
                    `reciever`,
                    `paymentway`,
                    `paymentmethod`,
                    `transactionId`,
                    `amount`,
                    `ref`,
                    `status`,
                    `createdAt`,
                    `platform`,
                    `actionAt`)
                    VALUES( 
                    '$userId',
                    '$DepositId',
                    '$userId',
                    'ST Marchant',
                    'sslcommerce',
                    'SSL',
                    '$tran_id',
                    '$amount',
                    '$bank_trxId',
                    'approved',
                    '$createdAt',
                    'B2CApp',
                    '$createdAt')";



            if ($conn->query($sql) === TRUE) {
                $conn->query("INSERT INTO `agent_ledger`(`userId`,`deposit`, `lastAmount`,`details`, `transactionId`,`reference`,`createdAt`, `platform`)
                        VALUES ('$userId','$amount','$newAmount','$amount TK Deposit successfully SSL Commerce - PaymentId-$bank_trxId','$tran_id','$DepositId','$createdAt', 'B2CApp')");

                //send email
                // $adminMessage = "We sent you new deposit request amount of $amount BDT, Which has been approved.";
                // $agentMessage = "Your new deposit request amount of $amount BDT has been accepeted, Thank you";
                // $subject = "Deposit Request Approved";
                // $header = $subject;
                // $property = "Deposit ID: ";
                // $data = $DepositId;

                // sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);
                // sendToAgent($subject, $agentMessage, $agentId, $header, $property, $data);
                ///////////////////////////

                $response['status'] = 'success';
                $response['message'] = 'Deposit Successfully Done';
                echo json_encode($response);

                //redirect
                header("Location: https://shopno.api.flyfarint.com/v1.0/Paymentgateway/SSLCommerce/api.php?appredirect&success");
                // header("Location: http://localhost:3002/dashboard/depositreq/successful");
                ////////////////////////
            }
        } else {
            header("Location: https://shopno.api.flyfarint.com/v1.0/Paymentgateway/SSLCommerce/api.php?appredirect&failed");
            // header("Location: http://localhost:3002/dashboard/depositreq/fail");
            exit();
        }
    }
}
