<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if(array_key_exists("all", $_GET)){
   
    $sql = "SELECT * FROM `notice`";
    $result = $conn->query($sql);

    $return_arr = array();

    if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()){
            $response = $row;
            array_push($return_arr, $response);
            }
    }
    
echo json_encode($return_arr);

}else if(array_key_exists("add", $_GET)){
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $_POST = json_decode(file_get_contents("php://input"), true);
         $title = $_POST["title"];
         $overview = $_POST["overview"];
         $date = date("Y-m-d H:i:s");
          
         $sql = "INSERT INTO notice (title, overview, created_at) VALUES ('$title', '$overview', '$date')";

         if($conn->query($sql)){
            $response['status'] = "success";
            $response['message'] = "Notice added successfully";
         }else{
            $response['status'] = "error";
            $response['message'] = "Query Failed";
         }
         echo json_encode($response);
    }
}else if(array_key_exists("edit", $_GET)){
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $_POST = json_decode(file_get_contents("php://input"), true);
        $id = $_POST["id"];
         $title = $_POST["title"];
         $overview = $_POST["overview"];
         $date = date("Y-m- d H:i:s");
          
         $sql = "UPDATE notice SET title ='$title', overview='$overview', updated_at='$date' where id='$id'";

         if($conn->query($sql)){
            $response['status'] = "success";
            $response['message'] = "Notice updated successfully";
         }else{
            $response['status'] = "error";
            $response['message'] = "Query Failed";
         }  
         echo json_encode($response);
    }

}else if(array_key_exists("delete", $_GET)){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        $_POST = json_decode(file_get_contents("php://input"), true);

        $id = $_POST['id'];

        $sql = "DELETE FROM notice WHERE id='$id'";
        if($conn->query($sql)){
            $response['status'] = "success";
            $response['message'] = "Notice deleted successfully";
        }else{
            $response['status'] = "error";
            $response['message'] = "Query Failed";
        }

        echo json_encode($response);
    }

}