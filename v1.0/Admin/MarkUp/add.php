<?php
include "../config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $_POST = json_decode(file_get_contents('php://input'), true);
    if(array_key_exists('option', $_GET)){
        $Option = $_GET['option'];

        if($Option == 'imarkup'){

            $agentId = $_POST['agentId'];
            $markupAmount = $_POST['markup'];
            $markupType = $_POST['markuptype'];
        
            $agentRowChecker = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
            if (mysqli_num_rows($agentRowChecker) > 0) {
                $result = mysqli_query($conn, "UPDATE agent SET iMarkup= '$markupAmount', iMarkupType='$markupType', alliMarkup= '', alliMarkupType='', alldMarkup= '', alldMarkupType='' WHERE agentId='$agentId'");
                if ($result === true) {
                    $response['status'] = 'success';
                    $response['message'] = 'Markup Update Successfully';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Markup Update Failed';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Agent Not Found';
            }
            echo json_encode($response);

        }else if($Option == 'dmarkup'){

            $agentId = $_POST['agentId'];
            $markupAmount = $_POST['markup'];
            $markupType = $_POST['markuptype'];
        
            $agentRowChecker = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
            if (mysqli_num_rows($agentRowChecker) > 0) {
                $result = mysqli_query($conn, "UPDATE agent SET dMarkup= '$markupAmount', dMarkupType='$markupType',alliMarkup= '', alliMarkupType='' , alldMarkup= '', alldMarkupType='' WHERE agentId='$agentId'");
                if ($result === true) {
                    $response['status'] = 'success';
                    $response['message'] = 'Markup Update Successfully';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Markup Update Failed';
                }
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Agent Not Found';
            }
            echo json_encode($response);
        }


        if($Option == 'alliMarkup'){
 
            $markupAmount = $_POST['markup'];
            $markupType = $_POST['markuptype'];
        
            
                $result = mysqli_query($conn, "UPDATE agent SET alliMarkup= '$markupAmount', alliMarkupType='$markupType',iMarkupType='', iMarkup='', dMarkupType='',dMarkup='' ");

                if ($result === true) {
                    $response['status'] = 'success';
                    $response['message'] = 'Markup Update Successfully';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Markup Update Failed';
                }
            echo json_encode($response);

        }else if($Option == 'alldMarkup'){

            $markupAmount = $_POST['markup'];
            $markupType = $_POST['markuptype'];
        
            
                $result = mysqli_query($conn, "UPDATE agent SET alldMarkup= '$markupAmount', alldMarkupType='$markupType', iMarkupType='', iMarkup='' , dMarkupType='',dMarkup='' ");
                if ($result === true) {
                    $response['status'] = 'success';
                    $response['message'] = 'Markup Update Successfully';
                } else {
                    $response['status'] = 'error';
                    $response['message'] = 'Markup Update Failed';
                }
            echo json_encode($response);
        }
    }

   

}
