<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include_once '../../authorization.php';
if (authorization($conn) == true){  

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        
        $data = json_decode(file_get_contents('php://input'), true);

        
        $reference =  $data['ref']; 
        $note = str_replace("'", "''",$data['note']);
        $category = $data['category'];
        $actionBy = $data['actionBy'];   
        $actionFrom = $data['actionFrom'];
        $actionAt = date('Y-m-d H:i:s'); 
    
        $sql = "INSERT INTO `notes`(
            `reference`,
            `note`,
            `category`,
            `actionBy`,
            `actionFrom`,
            `actionAt`
            )
        VALUES(
            '$reference',
            '$note',
            '$category',
            '$actionBy',
            '$actionFrom',
            '$actionAt'
        )";

        if($conn->query($sql) == true) {
            $response['status'] = 'success';
            $response['message'] = 'Notes Added successfully';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Notes Doesnt Added successfully';
        }

        echo json_encode($response);
    }

}else{
  authorization($conn);
}
  

?>