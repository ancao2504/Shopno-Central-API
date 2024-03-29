<?php

require '../../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $_POST = json_decode(file_get_contents('php://input'), true);

    $depositId = $_POST['depositId'];
    $userId = $_POST['userId'];
    $actionBy = $_POST['actionBy'];

    //Data

    $data = ($conn->query("SELECT * FROM deposit_request WHERE userId='$userId' AND depositId='$depositId' AND platform='B2C'")->fetch_all(MYSQLI_ASSOC))[0];

    if (!empty( $data)) {

        $id = $data['id'];
        $userId = $data['userId'];
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
        $currency = $data['currencyCode'];
        $currencyRate = $data['currencyRate'];
        $bdtAmount = $data['bdtAmount'];
        $chequeIssueDate = $data['chequeIssueDate'];
        $createdAt = date("Y-m-d H:i:s");


        if ($status == "pending") {
          

            //Agent Info
            $agentdata = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM `agent` where userId='$userId' AND platform='B2C'"));
            $companyName = $agentdata['company'];
            $companyEmail = $agentdata['email'];

            //staff Info
            $staffName = "";
            $staffsql = mysqli_query($conn, "SELECT * FROM `staffList` where staffId='$staffId' AND userId='$userId'");
            $staffdata = mysqli_fetch_array($staffsql);
            if (isset($staffdata['name'])) {
                $staffName = $staffdata['name'];
            } else {
                $staffName = "Agent";
            }

            if (empty($staffdata) && !empty($agentId)) {
                $Message = "Approved By: Shopno Tours & Travels";
            } else if (!empty($staffdata) && !empty($agentId)) {
                $Message = "Approved By: $actionBy, Shopno Tours & Travels";
            }

            $DateTime = date("D d M Y h:i A");

            //Last Amount
            $amountsql = "SELECT lastAmount, deposit FROM `agent_ledger` WHERE userId='$userId' AND platform='B2C' ORDER BY id DESC LIMIT 1";
            $result1 = mysqli_query($conn, $amountsql);
            $data1 = mysqli_fetch_array($result1);

            $deposit = isset($data1['deposit'])? $data1['deposit']: 0;

            $lastAmount = 0;
            if (!empty($data1['lastAmount'])) {
                $lastAmount = $data1['lastAmount'];
            } else {
                $lastAmount = 0;
            }

            // $lastAmount = $data1['lastAmount'];

            $afterDeposit = $lastAmount + $deposit;
            $newAmount = $lastAmount + $amount;

            if ($newAmount >= 0) {
                $conn->query("UPDATE `agent` SET `credit`='0' where userId='$userId' AND platform='B2C'");
            }

            $createdTime = date("Y-m-d H:i:s");

             $sql_query = "INSERT INTO `agent_ledger`(`userId`,`staffId`,`deposit`, `lastAmount`,`details`, `transactionId`,`currencyCode`,`currencyRate`,`bdtAmount`,`platform`,`reference`,`createdAt`, `actionBy`)
            VALUES ('$userId','$staffId','$amount','$newAmount','$amount $currency Deposit By $staffName successfully','$transactionId','$currency','$currencyRate','$bdtAmount','B2C','$depositId','$createdAt', '$actionBy')";

            if ($conn->query($sql_query) === true) {
                $conn->query("UPDATE deposit_request SET status='approved', approvedBy='$actionBy', actionAt='$createdTime'  WHERE userId='$userId' AND depositId='$depositId' ");
                $conn->query("INSERT INTO `activitylog`(`ref`,`userId`,`status`,`remarks`,`platform`,`actionBy`, `actionAt`)
              VALUES ('$depositId','$userId','Approved','Deposited $amount','B2C','$actionBy','$createdTime')");

                $html;
                if ($paymentway == 'bankTransfer') {
                    $html = '<tr>
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
         Send by:  <span style="color: #dc143c">' . $sender . '</span>
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
       Receive By:  <span style="color: #dc143c">' . $reciever . '</span>
    </td>
  </tr>';
                } else if ($paymentway == 'Cheque') {
                    $html = '<tr>
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
         Check Number:  <span style="color: #dc143c">' . $transactionId . '</span>
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
       Bank Name:  <span style="color: #dc143c">' . $paymentwaymethod . '</span>
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
     Check Issue Date:  <span style="color: #dc143c">' . $chequeIssueDate . '</span>
  </td>
</tr>
  ';
                } else if ($paymentway == 'Cash') {
                    $html = '
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
       Sender Name:  <span style="color: #dc143c">' . $sender . '</span>
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
     Receiver Name:  <span style="color: #dc143c">' . $reciever . '</span>
  </td>
</tr>
  ';
                } else if ($paymentway == 'mobileTransfer') {
                    $html = '
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
     Payment Method:  <span style="color: #dc143c">' . $paymentwaymethod . '</span>
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
Pay Using Account Number:  <span style="color: #dc143c">' . $sender . '</span>
</td>
</tr>
';
                }


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
