<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if($_SERVER["REQUEST_METHOD"] == 'POST'){
    $_POST = json_decode(file_get_contents("php://input"), true);
    $UserId = $_POST["userId"];
    $CurrencyRate = $_POST["rate"];
    $ConverAmount = $_POST["amount"];
    $Code = $_POST["code"];
    $BdtAmount = $_POST["bdtamount"];
    $PreviousAmount = $_POST["previousamount"];
    $PreviousCurrency = $_POST["previouscurrency"];
    $createdAt = date("Y-m-d H:i:s");
   

    $agentChecker = $conn->query("SELECT * FROM agent WHERE userId = '$UserId'")->fetch_all(MYSQLI_ASSOC);
    if(!empty($agentChecker)){
        $currencyChecker = $conn->query("SELECT * FROM currency WHERE code = '$Code'")->fetch_all(MYSQLI_ASSOC);
        if(!empty($currencyChecker)){
            $agentLedger = $conn->query("SELECT * FROM agent_ledger WHERE userId = '$UserId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
            if(!empty($agentLedger)){

               $id = $agentLedger[0]['id'];
               $amount = $agentLedger[0]['lastAmount'];
                if($amount > 0) {
                   // $sql = "UPDATE agent_ledger SET lastAmount = '$ConverAmount', currencyCode='$Code', bdtAmount='$BdtAmount', currencyRate='$CurrencyRate' WHERE id='$id'";
                    
                    $sql = "INSERT INTO `agent_ledger`(`userId`,`lastAmount`,`details`,`currencyCode`,`currencyRate`,`bdtAmount`,`platform`,`previousamount`,`previouscurrency`,`createdAt`, `actionBy`)
                    VALUES ('$UserId','$ConverAmount','$ConverAmount $Code Converted by $UserId successfully','$Code','$CurrencyRate','$BdtAmount','B2C','$PreviousAmount','$PreviousCurrency','$createdAt', '$UserId')";

                    if($conn->query($sql)) {
                        echo json_encode([
                            "status" => "success",
                            "message" => "amount updated"
                        ]);
                    } else {
                        echo json_encode([
                            "status" => "error",
                            "message" => "query file"
                        ]);
                    }
                }else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "amount 0"
                    ]);
                }
            }else {
                echo json_encode([
                    "status" => "error",
                    "message"=> "user ledger is empty"
                ]);
            }
            
        }else{
          echo json_encode([
                "status" => "error",
                "message" => "currency not found",
            ]);
        }
    }
    else{

       echo json_encode([
            "status" => "error",
            "message" => "userId not found"
        ]);
    }

}





?>