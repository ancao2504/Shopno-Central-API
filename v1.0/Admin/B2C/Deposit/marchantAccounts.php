<?php
include "../config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,GET,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists('add', $_GET)) {

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $_POST = json_decode(file_get_contents('php://input'), true);

        $agentId = $_POST['agentId'];
        $operator = $_POST['operator'];
        $name = $_POST['name'];
        $number = $_POST['number'];
        $charge = $_POST['charge'];

        $DateTime = date("D d M Y h:i A");

        $checker = $conn->query("SELECT * FROM subagent WHERE agentId='$agentId' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
        if (!empty($checker)) {

                $validate = $conn->query("SELECT * FROM marchant_accounts where number= '$number'")->fetch_all(MYSQLI_ASSOC);
                if(!empty($validate)){
                    $response['status'] = "error";
                    $response['message'] = "account number is already existing";

                }else{
                    $sql = "INSERT INTO marchant_accounts(
                        `agentId`, `subagentId`, `operator`, `name`, `number`, `charge`,`createdAt`
                    ) VALUE(
                        '$agentId', ' ', '$operator', '$name', '$number', '$charge', '$DateTime'
                    )";
            
                        if ($conn->query($sql) == true) {
                            $response['status'] = 'success';
                            $response['message'] = 'merchant account add successfully';
                        } else {
                            $response['status'] = 'error';
                            $response['message'] = 'merchant account add  failed';
                        }
                }

    

        } else {
            $response['status'] = 'error';
            $response['message'] = "is invalid";
        }
        echo json_encode($response);

    }
}else if(array_key_exists('edit', $_GET)){

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $_POST = json_decode(file_get_contents('php://input'), true);

        $id = $_POST['id'];
        $operator = $_POST['operator'];
        $name = $_POST['name'];
        $number = $_POST['number'];
        $charge = $_POST['charge'];

        $DateTime = date("D d M Y h:i A");

        $checker = $conn->query("SELECT * FROM marchant_accounts WHERE id='$id'")->fetch_all(MYSQLI_ASSOC);
        if(!empty($checker)){

            $sql = "UPDATE marchant_accounts SET operator= '$operator', name = '$name', number ='$number', charge = '$charge', createdAt = '$DateTime' WHERE id='$id'";
            if ($conn->query($sql) == true) {
                $response['status'] = 'success';
                $response['message'] = 'merchant account update successfully';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'merchant account update failed';
            } 


        }else{
            $response['status'] = 'error';
            $response['message'] = "is invalid";
        }
        echo json_encode($response);
    }

}else if(array_key_exists('delete', $_GET)){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $_POST = json_decode(file_get_contents('php://input'), true);

        $id = $_POST['id'];

        $checker = $conn->query("SELECT * FROM marchant_accounts WHERE id='$id' ORDER BY id DESC LIMIT 1")->fetch_all(MYSQLI_ASSOC);
        if(!empty($checker)){

            $sql = "DELETE FROM marchant_accounts WHERE id='$id'";
            if ($conn->query($sql) == true) {
                $response['status'] = 'success';
                $response['message'] = 'merchant account delete successfully';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'merchant account delete  failed';
            }


        }else{
            $response['status'] = 'error';
            $response['message'] = "is invalid";
        }
        echo json_encode($response);
    }
}else if(array_key_exists('all', $_GET)){
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_POST = json_decode(file_get_contents('php://input'), true);

    $agentId = $_POST['agentId'];
    
    $MarchantAccounts = $conn->query("SELECT * FROM marchant_accounts WHERE agentId = '$agentId'")->fetch_all(MYSQLI_ASSOC);
    if(!empty($MarchantAccounts)){
        $response['status'] = 'success';
        $response['data'] = $MarchantAccounts;
    }else{
        $response['status'] = 'error';
        $response['message'] = "is invalid";
    }
    echo json_encode($response);
}
    

}