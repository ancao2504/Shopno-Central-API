<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use PHPMailer\PHPMailer\PHPMailer;

require "../../vendor/autoload.php";

if (array_key_exists('request', $_GET)) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $_POST = json_decode(file_get_contents('php://input'), true);

        $reissueId = "";
        $sql1 = "SELECT * FROM reissue ORDER BY reissueId DESC LIMIT 1";
        $result = $conn->query($sql1);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $outputString = preg_replace('/[^0-9]/', '', $row["reissueId"]);
                $number = (int) $outputString + 1;
                $reissueId = "STRI$number";
            }
        } else {
            $reissueId = "STRI1000";
        }

        $paxDetails = $_POST['passengerData'];
        $userId = $_POST["userId"];
        $bookingId = $_POST["bookingId"];
        $Pax = count($_POST['passengerData']);
        $passengerData = $_POST["passengerData"];
        $requestedBy = $_POST["requestedBy"];
        $reissuedate = $_POST["date"];

        $PaxData = array();
        $passData = array();
        foreach ($paxDetails as $paxDet) {
            $name = $paxDet['name'];
            $ticket = $paxDet['ticket'];
            //$gender = $paxDet['gender'];
            //$passenType = $paxDet['type'];

            $passengerDataHTML = '
    <tr>
      <td
        style="
          padding-left: 20px;
          padding-bottom: 5px;
          vertical-align: top;
        "
      >
      ' . $name . '
      </td>
      <td
        style="
          padding-left: 20px;
          padding-bottom: 5px;
          vertical-align: top;
        "
      >
        ' . $ticket . '
      </td>
    </tr>';

            $data = "($name-$ticket)";
            array_push($PaxData, $data);
            array_push($passData, $passengerDataHTML);
        }

        $dataPax = implode('', $PaxData);
        $passengerDataTable = implode('', $passData);

        $createdTime = date('Y-m-d H:i:s');

        $DateTime = date("D d M Y h:i A");

        if (isset($bookingId)) {

            $rowTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);

            if (!empty($rowTravelDate)) {
                $travelDate = $rowTravelDate[0]['travelDate'];
                $pax = $rowTravelDate[0]['pax'];
                $Type = $rowTravelDate[0]['tripType'];
                $Airlines = $rowTravelDate[0]['airlines'];
                $gds = $rowTravelDate[0]['gds'];
                $pnr = $rowTravelDate[0]['pnr'];
                $status = $rowTravelDate[0]['status'];
                $TicketId = $rowTravelDate[0]['ticketId'];
                $TicketCost = $rowTravelDate[0]['netCost'];
                $arriveTo = $rowTravelDate[0]['arriveTo'];
                $deptFrom = $rowTravelDate[0]['deptFrom'];
                $tripType = $rowTravelDate[0]['tripType'];
            }
        }

        if ($status == "Reissue In Processing") {
            $response['status'] = "error";
            $response['message'] = "Ticket Already Reissue In Processing";

            echo json_encode($response);
            exit();
        } else {

            $sql = "INSERT INTO `reissue`(`reissueId`,`userId`, `bookingId`, `ticketId`,`passengerDetails`,`reissueDate`,`status`, `requestedBy`, `requestedAt`)
             VALUES ('$reissueId','$userId','$bookingId','$TicketId','$dataPax','$reissuedate','pending','$requestedBy','$createdTime')";

            if ($conn->query($sql) === true) {
                $conn->query("UPDATE `booking` SET `status`='Reissue In Processing',`reissueId`='$reissueId',`lastUpdated`='$createdTime' where bookingId='$bookingId'");
                $conn->query("INSERT INTO `activitylog`(`ref`,`userId`,`status`,`remarks`,`actionRef`,`actionBy`,`actionAt`)
            VALUES ('$bookingId','$userId','Reissue In Processing','$userId Send Reissue Request','$reissueId','$requestedBy','$createdTime')");

                $userData = $conn->query("SELECT `name`,`email` FROM `agent` WHERE userId = '$userId'")->fetch_all(MYSQLI_ASSOC);
                $userName = $userData[0]['name'];
                $userEmail = $userData[0]['email'];

                $response['status'] = "success";
                $response['InvoiceId'] = "$reissueId";
                $response['message'] = "Ticket Reissue Request Successfully";

                echo json_encode($response);

            }
        }
    }
} else if (array_key_exists('quotationconfirm', $_GET)) {
    $_POST = json_decode(file_get_contents('php://input'), true);
    $agentId = $_POST['agentId'];
    $bookingId = $_POST['bookingId'];
    $createdTime = date("Y-m-d H:i:s");
    $checker = $conn->query("SELECT * FROM reissue WHERE bookingId='$bookingId' AND agentId='$agentId'")->fetch_all(MYSQLI_ASSOC);
    if (!empty($checker)) {
        $status = $checker[0]['status'];
        if ($status == 'quotation confirm') {
            $response['status'] = 'error';
            $response['message'] = 'Already Quotation Confirm';
        } else {
            $sql = "UPDATE `reissue` SET `status`='quotation confirm',`actionBy`='$agentId',`actionAt`='$createdTime' WHERE bookingId='$bookingId' AND agentId='$agentId'";
            if ($conn->query($sql) == true) {
                $response['status'] = "success";
                $response['message'] = 'Quotation Confirm';
            } else {
                $response['status'] = "error";
                $response['message'] = 'Query Failed';
            }
        }
    } else {
        $response['status'] = "error";
        $response['message'] = 'Invalid Id';
    }
    echo json_encode($response);

} else if (array_key_exists('quotationrejected', $_GET)) {
    $_POST = json_decode(file_get_contents('php://input'), true);
    $agentId = $_POST['agentId'];
    $bookingId = $_POST['bookingId'];
    $createdTime = date("Y-m-d H:i:s");
    $checker = $conn->query("SELECT * FROM reissue WHERE bookingId='$bookingId' AND agentId='$agentId'")->fetch_all(MYSQLI_ASSOC);
    if (!empty($checker)) {
        $status = $checker[0]['status'];
        if ($status == 'quotation rejected') {
            $response['status'] = 'error';
            $response['message'] = 'Already Quotation Rejected';
        } else {
            $sql = "UPDATE `reissue` SET `status`='quotation rejected',`actionBy`='$agentId',`actionAt`='$createdTime' WHERE bookingId='$bookingId' AND agentId='$agentId'";
            if ($conn->query($sql) == true) {
                $response['status'] = "success";
                $response['message'] = 'Quotation Rejected Successfully';
            } else {
                $response['status'] = "error";
                $response['message'] = 'Query Failed';
            }
        }
    } else {
        $response['status'] = "error";
        $response['message'] = 'Invalid Id';
    }
    echo json_encode($response);

}
