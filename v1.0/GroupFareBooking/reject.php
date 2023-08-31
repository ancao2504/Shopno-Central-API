<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $json_data = json_decode(file_get_contents("php://input"), true);

    $agentId = $json_data["agentId"];
    $gfId = $json_data["gfId"];
    $bookingId = $json_data["bookingId"];
    $updatedAt = date("Y-m-d H:i:s");

    $getBookingData = "SELECT `grossCost`, `pax`, `status` FROM `gf_booking` WHERE `bookingId`='$bookingId'";

    if ($bookingDataRow = $conn->query($getBookingData)->fetch_assoc()) {
        $status = $bookingDataRow["status"];
        $refundAmount = $bookingDataRow["grossCost"];
        $pax = $bookingDataRow["pax"];


        /* This code block is checking if the status of the booking is already "rejected". */
        if ($status == "rejected") {
            $response["status"] = "error";
            $response["message"] = "This Booking Is Already Rejected";
            echo json_encode($response);
            exit;
        }

        $getLastAmount = "SELECT lastAmount FROM agent_ledger WHERE agentId='$agentId' ORDER BY id DESC LIMIT 1";

        if ($lastAmountRow = $conn->query($getLastAmount)->fetch_assoc()) {

            $lastAmount = $lastAmountRow["lastAmount"];

            $newAmount = $lastAmount + $refundAmount;
            $details = "GFREFUND-$gfId" . "_PAX-$pax";

            $ledgerUpdate = "INSERT INTO agent_ledger (agentId, refund, lastAmount, transactionId, details, reference, actionBy, createdAt)
            VALUES ('$agentId', '$refundAmount', '$newAmount', '$gfId', '$details', '$gfId', '$agentId' ,'$updatedAt')";

            if ($conn->query($ledgerUpdate)) {

                $updateGF = "UPDATE groupfare SET availableSeat=availableSeat+'$pax', deactivated='true' WHERE groupFareId='$gfId'";

                if ($conn->query($updateGF)) {

                    $updateStatus = "UPDATE `gf_booking` SET `status`='rejected', `updatedAt`='$updatedAt' WHERE `groupFareId`='$gfId' AND `bookingId`='$bookingId'";

                    if ($conn->query($updateStatus)) {

                        $response["status"] = "success";
                        $response["message"] = "Booking Rejected Successfully";
                        echo json_encode($response);
                    } else {

                        $response["status"] = "error";
                        $response["message"] = "status update failed";
                        echo json_encode($response);
                    }
                } else {

                    $response["status"] = "error";
                    $response["message"] = "update failed";
                    echo json_encode($response);
                }
            } else {

                $response["status"] = "error";
                $response["message"] = "ledger update failed";
                echo json_encode($response);
            }
        } else {

            $response["status"] = "error";
            $response["message"] = "lastAmount not found";
            echo json_encode($response);
        }
    } else {

        $response["status"] = "error";
        $response["message"] = "Gross Cost not found";
        echo json_encode($response);
    }
} else {

    $response["status"] = "error";
    $response["message"] = "Method not allowed";
    echo json_encode($response);
}
