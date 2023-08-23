<?php

require '../../config.php';
require '../../emailfunction.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");



if (array_key_exists("bookingId", $_GET)) {
  $reissueId = $_GET["bookingId"];

  $sql = "SELECT * FROM `reissue` where bookingId='$reissueId'";
  $result = $conn->query($sql);

  $return_arr = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

      $agentId = $row['agentId'];
      $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
      $data = mysqli_fetch_assoc($query);
      $companyname = $data['company'];

      $response = $row;
      $response['companyname'] = "$companyname";
      array_push($return_arr, $response);
    }
  }

  echo json_encode($return_arr);
} else if (array_key_exists('quotationsend', $_GET)) {
  if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $_POST = json_decode(file_get_contents('php://input'), true);

    $bookingId = $_POST['bookingId'];
    $agentId = $_POST['agentId'];
    $QuotationText = $_POST['text'];
    $Amount = $_POST['amount'];
    $ActionBy = isset($_POST['actionBy']) ? $_POST['actionBy'] : "";
    $createdTime = date("Y-m-d H:i:s");

    $remarkAmount = $QuotationText . "Amount: " . $Amount;

    if (isset($bookingId)) {
      $checker = $conn->query("SELECT * FROM reissue WHERE bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);
      if (!empty($checker)) {
        $status = $checker[0]['status'];
        if ($status == 'Reissue Quotation Send') {
          $response['status'] = 'error';
          $response['message'] = "Already Reissue Quotation Sending";
        } else {
          $sql = "UPDATE `booking` SET `status`='Reissue Quotation Send' WHERE bookingId='$bookingId' AND agentId='$agentId'";

          if ($conn->query($sql) == true) {
            $sql = "UPDATE `reissue` SET `status`='Reissue Quotation Send',`actionAt`='$createdTime', `quottext`='$QuotationText', `quotamount`='$Amount' WHERE bookingId='$bookingId' AND agentId='$agentId'";
            $conn->query($sql);
            $sql = "UPDATE `activitylog` SET `status`='Reissue Quotation Send', `actionBy`='$ActionBy' , `remarks`='$remarkAmount' WHERE ref='$bookingId' AND agentId='$agentId'";
            $conn->query($sql);

            $response['status'] = "success";
            $response['message'] = 'Reissue Quotation Sending Successfully';
          } else {
            $response['status'] = "error";
            $response['message'] = 'Query Failed';
          }
        }
      } else {
        $response['status'] = "error";
        $response['message'] = "booking not found";
      }
    }
    echo json_encode($response);
  }
} else if (array_key_exists("approved", $_GET)) {
  if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $reissueId = $_POST['reissueId'];
    $bookingId = $_POST['bookingId'];
    $agentId = $_POST['agentId'];
    $actionBy = $_POST['actionBy'];
    $reissuecharge = $_POST['reissuecharge'];

    $createdTime = date("Y-m-d H:i:s");


    $fileName  =  $_FILES['file']['name'];
    $tempPath  =  $_FILES['file']['tmp_name'];
    $fileSize  =  $_FILES['file']['size'];

    if (isset($bookingId)) {
      $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
      $rowTravelDate = mysqli_fetch_array($sqlTravelDate, MYSQLI_ASSOC);

      if (!empty($rowTravelDate)) {
        $travelDate = $rowTravelDate['travelDate'];
        $subagentId = $rowTravelDate['subagentId'];
        $pax = $rowTravelDate['pax'];
        $gds = $rowTravelDate['gds'];
        $pnr = $rowTravelDate['pnr'];
        $Type = $rowTravelDate['tripType'];
        $Airlines = $rowTravelDate['airlines'];
        $TicketId = $rowTravelDate['ticketId'];
        $TicketCost = $rowTravelDate['netCost'];
        $arriveTo = $rowTravelDate['arriveTo'];
        $deptFrom = $rowTravelDate['deptFrom'];
        $tripType = $rowTravelDate['tripType'];
        $status = $rowTravelDate['status'];
      }
    }



    if (empty($fileName)) {
      $errorMSG = json_encode(array("message" => "please select image", "status" => false));
      echo $errorMSG;
    } else {
      $upload_path = "../../../asset/Agent/$agentId/Reissue/"; // set upload folder path 

      if (!file_exists($upload_path)) {
        mkdir($upload_path, 0777, true);
      }

      $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); // get image extension

      // valid image extensions
      $valid_extensions = array('jpeg', 'jpg', 'png', 'pdf', 'PDF', 'JPG', 'PNG', 'JPEG');


      $renameFile = $reissueId . "." . $fileExt;



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

    if (isset($reissueId)) {
      $sqlvoid = mysqli_query($conn, "SELECT * FROM reissue WHERE bookingId='$bookingId'");
      $rowsqlvoid = mysqli_fetch_array($sqlvoid, MYSQLI_ASSOC);

      if (!empty($rowsqlvoid)) {
        $reissuerequestedBy = $rowsqlvoid['requestedBy'];
        $reissuerequestedAt = $rowsqlvoid['requestedAt'];

        $passengerDetails = $rowsqlvoid['passengerDetails'];
        $name = preg_replace('/[^A-Z]/', ' ', $passengerDetails);
        $ticketno = preg_replace('/[^0-9]/', '', $passengerDetails);
      }
    }

    $checkBalanced = mysqli_query($conn, "SELECT lastAmount FROM `agent_ledger` where agentId = '$agentId' ORDER BY id DESC LIMIT 1");
    $rowcheckBalanced = mysqli_fetch_array($checkBalanced, MYSQLI_ASSOC);
    if (!empty($rowcheckBalanced)) {
      $lastAmount = $rowcheckBalanced['lastAmount'];
    }

    if ($lastAmount < 1) {
      echo (json_encode(
        array(
          "status" => "error",
          "message" => "Balance is not enough"
        )
      )
      );
      exit;
    }


    $checker = $conn->query("SELECT * FROM `reissue` WHERE bookingId='$bookingId'")->fetch_all(MYSQLI_ASSOC);
    $status = $checker[0]['status'];
    if ($status == 'approved') {
      $response['status'] = 'error';
      $response['message'] = "Booking Reissue Already Approved";
    } else {

      $newBalance = $lastAmount - (int)$reissuecharge;
      $attachment = "shopno.api.flyfarint.com/asset/Agent/$agentId/Reissue/$renameFile";

      $sql = "UPDATE `reissue` SET `status`='approved',`charge`='',`actionBy`='$actionBy',`actionAt`='$createdTime', `attachment`='$attachment', `servicefee`='$reissuecharge' WHERE bookingId='$bookingId' AND agentId='$agentId'";

      if ($conn->query($sql) === true) {
        $details = "Reissue $TicketId Ticket Invoice $Type Air Ticket $deptFrom - $arriveTo with carrier $Airlines was Requested By $reissuerequestedBy";

        $conn->query("INSERT INTO `agent_ledger`(`agentId`,`reissue`, `lastAmount`, `transactionId`, `details`, `reference`, `actionBy`, `createdAt`)
            VALUES ('$agentId','$reissuecharge','$newBalance','$bookingId','$details','$reissueId','$actionBy','$createdTime')");

        $conn->query("UPDATE `booking` SET `status`='Reissued',`lastUpdated`='$createdTime', `reissueId`='$reissueId' where bookingId='$bookingId'");
        $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                VALUES ('$bookingId','$agentId','Reissued',' ','$actionBy','$createdTime')");

        $header = $subject = "New Booking Reissue Request Accept";
        $property = "Booking ID: ";
        $data = $bookingId;
        $adminMessage = "Our Booking Reissue Request has been Accepted.";
        $agentMessage = "Your Booking Reissue Request has been Accepted.";
        sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);
        sendToAgent($subject, $agentMessage, $agentId, $header, $property, $data);



        $response['status'] = "success";
        $response['message'] = "Booking Reissue Request Approved";
        $response['reissueId'] = $reissueId;
      }
    }

    echo json_encode($response);
  }
} else if (array_key_exists("reject", $_GET)) {

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_POST = json_decode(file_get_contents('php://input'), true);

    $bookingId = $_POST['bookingId'];
    $agentId = $_POST['agentId'];
    $actionBy = $_POST['actionBy'];
    $remarks = $_POST['remarks'];

    $createdTime = date("Y-m-d H:i:s");
    $DateTime = date("D d M Y h:i A");

    if (isset($bookingId)) {
      $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId' AND agentId='$agentId'");
      $rowTravelDate = mysqli_fetch_array($sqlTravelDate, MYSQLI_ASSOC);

      if (!empty($rowTravelDate)) {
        $travelDate = $rowTravelDate['travelDate'];
        $agentId = $rowTravelDate['agentId'];
        $staffId = $rowTravelDate['staffId'];
        $subAgentId = $rowTravelDate['subagentId'];
        $pax = $rowTravelDate['pax'];
        $gds = $rowTravelDate['gds'];
        $pnr = $rowTravelDate['pnr'];
        $Type = $rowTravelDate['tripType'];
        $Airlines = $rowTravelDate['airlines'];
        $TicketId = $rowTravelDate['ticketId'];
        $TicketCost = $rowTravelDate['netCost'];
        $arriveTo = $rowTravelDate['arriveTo'];
        $deptFrom = $rowTravelDate['deptFrom'];
        $tripType = $rowTravelDate['tripType'];
      }
    }

    $checker = $conn->query("SELECT * FROM reissue WHERE bookingId='$bookingId' AND agentId='$agentId'")->fetch_all(MYSQLI_ASSOC);
    $status = $checker[0]['status'];
    if ($status == 'rejected') {
      $response['status'] = 'error';
      $response['message'] = "Booking Reissue Already Rejected";
    } else {
      $sql = "UPDATE `reissue` SET `status`='rejected',`actionBy`='$actionBy',`actionAt`='$createdTime' WHERE bookingId='$bookingId' AND agentId='$agentId'";

      if ($conn->query($sql) === true) {

        $conn->query("UPDATE `booking` SET `status`='Reissue Rejected',`lastUpdated`='$createdTime' WHERE bookingId='$bookingId'");
        $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                      VALUES ('$bookingId','$agentId','Reissue Rejected','$remarks','$actionBy','$createdTime')");


        $header = $subject = "New Booking Reissue Request Accept";
        $property = "Booking ID: ";
        $data = $bookingId;
        $adminMessage = "Our Booking Reissue Request has been Rejected.";
        $agentMessage = "Your Booking Reissue Request has been Rejected.";
        sendToAdmin($subject, $adminMessage, $agentId, $header, $property, $data);
        sendToAgent($subject, $agentMessage, $agentId, $header, $property, $data);


        $response['status'] = "success";
        $response['message'] = "Booking Reissue Request Reject Successful";
      } else {
        $response['status'] = 'error';
        $response['message'] = "Query Failed";
      }
    }
    echo json_encode($response);
  }
} else if (array_key_exists("tobeconfirm", $_GET)) {

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_POST = json_decode(file_get_contents('php://input'), true);

    $bookingId = $_POST['bookingId'];
    $agentId = $_POST['agentId'];
    $staffId = $_POST["staffId"];
    $actionBy = $_POST['actionBy'];
    $remarks = $_POST['remarks'];

    $createdTime = date("Y-m-d H:i:s");
    $DateTime = date("D d M Y h:i A");

    if (isset($bookingId)) {
      $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
      $rowTravelDate = mysqli_fetch_array($sqlTravelDate, MYSQLI_ASSOC);

      if (!empty($rowTravelDate)) {
        $travelDate = $rowTravelDate['travelDate'];
        $pax = $rowTravelDate['pax'];
        $gds = $rowTravelDate['gds'];
        $pnr = $rowTravelDate['pnr'];
        $Type = $rowTravelDate['tripType'];
        $Airlines = $rowTravelDate['airlines'];
        $TicketId = $rowTravelDate['ticketId'];
        $TicketCost = $rowTravelDate['netCost'];
        $arriveTo = $rowTravelDate['arriveTo'];
        $deptFrom = $rowTravelDate['deptFrom'];
        $tripType = $rowTravelDate['tripType'];
      }
    }

    if (isset($agentId)) {
      $sql1 = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
      $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

      if (!empty($row1)) {
        $agentEmail = $row1['email'];
        $companyname = $row1['company'];
      }
    }

    $sql = "UPDATE `reissue` SET `status`='tobeconfirm',`actionBy`='$actionBy',`actionAt`='$createdTime' WHERE bookingId='$bookingId' AND agentId='$agentId'";

    if ($conn->query($sql) === true) {

      $conn->query("UPDATE `booking` SET `status`='Reissue To Be Corfirm',`lastUpdated`='$createdTime' WHERE bookingId='$bookingId'");
      $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                VALUES ('$bookingId','$agentId','Reissue To Be Corfirm','$remarks','$actionBy','$createdTime')");

      $response['status'] = "success";
      $response['InvoiceId'] = "$reissueId";
      $response['message'] = "Reissue Rejected Failed Successfully";
    }
  }
} else if (array_key_exists("confirmed", $_GET)) {

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_POST = json_decode(file_get_contents('php://input'), true);

    $bookingId = $_POST['bookingId'];
    $agentId = $_POST['agentId'];
    $staffId = $_POST["staffId"];
    $actionBy = $_POST['actionBy'];

    $createdTime = date("Y-m-d H:i:s");
    $DateTime = date("D d M Y h:i A");

    if (isset($bookingId)) {
      $sqlTravelDate = mysqli_query($conn, "SELECT * FROM booking WHERE bookingId='$bookingId'");
      $rowTravelDate = mysqli_fetch_array($sqlTravelDate, MYSQLI_ASSOC);

      if (!empty($rowTravelDate)) {
        $travelDate = $rowTravelDate['travelDate'];
        $pax = $rowTravelDate['pax'];
        $gds = $rowTravelDate['gds'];
        $pnr = $rowTravelDate['pnr'];
        $Type = $rowTravelDate['tripType'];
        $Airlines = $rowTravelDate['airlines'];
        $TicketId = $rowTravelDate['ticketId'];
        $TicketCost = $rowTravelDate['netCost'];
        $arriveTo = $rowTravelDate['arriveTo'];
        $deptFrom = $rowTravelDate['deptFrom'];
        $tripType = $rowTravelDate['tripType'];
      }
    }

    if (isset($agentId)) {
      $sql1 = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
      $row1 = mysqli_fetch_array($sql1, MYSQLI_ASSOC);

      if (!empty($row1)) {
        $agentEmail = $row1['email'];
        $companyname = $row1['company'];
      }
    }

    $sql = "UPDATE `reissue` SET `status`='tobeconfirm',`actionBy`='$actionBy',`actionAt`='$createdTime' WHERE bookingId='$bookingId' AND agentId='$agentId'";

    if ($conn->query($sql) === true) {

      $conn->query("UPDATE `booking` SET `status`='Reissue To Be Corfirmed',`lastUpdated`='$createdTime' WHERE bookingId='$bookingId'");
      $conn->query("INSERT INTO `activitylog`(`ref`,`agentId`,`status`,`remarks`,`actionBy`, `actionAt`)
                VALUES ('$bookingId','$agentId','Reissue To Be Corfirmed','$remarks','$actionBy','$createdTime')");


      $response['status'] = "success";
      $response['InvoiceId'] = "$reissueId";
      $response['message'] = "Void Rejected Successfully";
      $response['error'] = "Reissue Rejected Successfully";


      echo json_encode($response);
    }
  }
} else if (array_key_exists('getquotadata', $_GET)) {
  if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $_POST = json_decode(file_get_contents('php://input'), true);
    $agentId = $_POST['agentId'];
    $bookingId = $_POST['bookingId'];

    $data = $conn->query("SELECT quottext, quotamount,status FROM `reissue` WHERE `bookingId` = '$bookingId' AND agentId = '$agentId'")->fetch_all(MYSQLI_ASSOC);
    if (!empty($data)) {
      echo json_encode($data);
    } else {
      $response['status'] = "error";
      $response['message'] = "Data Not Found";
    }
  }
} else if (array_key_exists('option', $_GET)) {

  if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $_POST = json_decode(file_get_contents('php://input'), true);
    $Agent = $_POST['agentId'];
    $BookingId = $_POST['bookingId'];
    $Status = $_POST['option'];

    $checker = $conn->query("SELECT status FROM `reissue` WHERE `bookingId` = '$BookingId' AND `agentId`='$Agent'")->fetch_all(MYSQLI_ASSOC);
    $status = $checker[0]['status'];
    if ($status == "Reissue Quotation Confirm") {
      $response['status'] = "error";
      $response['message'] = "Quotation Already Confirmed";
      echo json_encode($response);
    } else if ($status == "Reissue Quotation Reject") {
      $response['status'] = "error";
      $response['message'] = "Quotation Already Reject";
      echo json_encode($response);
    } else if ($status == "Reissue Quotation Send") {
      if ($Status == "yes") {
        $sql = "UPDATE reissue SET status = 'Reissue Quotation Confirm' WHERE agentId = '$Agent' AND bookingId = '$BookingId'";
        if ($conn->query($sql)) {
          $sql2 = "UPDATE booking SET status = 'Reissue Quotation Confirm' WHERE agentId = '$Agent' AND bookingId = '$BookingId'";
          $conn->query($sql2);
          $sql3 = mysqli_query($conn, "SELECT quotamount FROM `reissue` where agentId = '$Agent'
              ORDER BY id DESC LIMIT 1");
          $row1 = mysqli_fetch_array($sql3, MYSQLI_ASSOC);

          if (!empty($row1)) {
            $quotamount = $row1['quotamount'];
          }

          if (!empty($quotamount)) {
            $sql4 = mysqli_query($conn, "SELECT lastAmount FROM `agent_ledger` where agentId = '$Agent'
                ORDER BY id DESC LIMIT 1");

            $row1 = mysqli_fetch_array($sql4, MYSQLI_ASSOC);
            if (!empty($row1)) {
              $lastAmount = $row1['lastAmount'];
            }
            $newBalance = $lastAmount - $quotamount;
            $sql = "UPDATE `agent_ledger` SET lastAmount='$newBalance' WHERE `agentId`='$Agent' AND `reference`= '$BookingId'";

            if ($conn->query($sql)) {

              $response['status'] = "success";
              $response['message'] = "Quotation Approved Successfully";
              echo json_encode($response);
            }
          }
        } else {
          echo json_encode(array('status' => 'error'));
        }
      }
      if ($Status == "no") {
        $sql = "UPDATE reissue SET status = 'Reissue Quotation Reject' WHERE bookingId = '$BookingId' AND agentId = '$Agent'";
        if ($conn->query($sql)) {
          $sql = "UPDATE booking SET status = 'Reissue Quotation Reject' WHERE agentId = '$Agent' AND bookingId = '$BookingId'";
          $conn->query($sql);
          $response['status'] = "success";
          $response['message'] = "Reissue Quotation Rejected Successfully";
        } else {
          $response['status'] = "error";
          $response['message'] = "Reissue Quotation Rejected Failed Successfully";
        }
        echo json_encode($response);
      }
    }
  }
} else if (array_key_exists('reissueId', $_GET)) {
  $reissueId = $_GET['reissueId'];
  $sql = "SELECT attachment FROM reissue WHERE reissueId='$reissueId'";

  $attachment = $conn->query($sql)->fetch_assoc();

  // $response['attachment'] 
  $url= $attachment['attachment'];
  echo json_encode($url);
  header("Location: https://$url");
}
