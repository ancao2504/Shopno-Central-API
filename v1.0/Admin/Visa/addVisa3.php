<?php 

require '../../config.php';
  
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $_POST = json_decode(file_get_contents('php://input'), true);


    if(!isset($_POST['country'])){
        echo json_encode(["error" => "Country not found"]);
        die();
    }
    if(!isset($_POST['category'])){
        echo json_encode(["error" => "Visa Type not found"]);
        die();
    }
    if(!isset($_POST['visaEN'])){
        echo json_encode(["error" => "Visa Details in English not Found"]);
        die();
    }
    if(!isset($_POST['visaBN'])){
        echo json_encode(["error" => "Visa Details in Bangla not Found"]);
        die();
    }

    if(isset($_POST['country']) && isset($_POST['category']) && isset($_POST['visaEN']) && isset($_POST['visaBN'])){
        $Time =  date("Y-m-d h:i:s");
        $country = $_POST['country'];
        $category = $_POST['category'];
        $visaEN = str_replace("'", "''",$_POST['visaEN']);
        $visaBN = str_replace("'", "''",$_POST['visaBN']);

        $visaSql = mysqli_query($conn,"SELECT * FROM visa WHERE country='$country' AND visatype='$category'");
        $visaRow = mysqli_fetch_array($visaSql,MYSQLI_ASSOC);

        if(!empty($visaRow)){
            $response['status']="error";
            $response['message']="Already Added"; 
        
        }else{
            
            $sqlquery = "INSERT INTO `visa`(`country`, `visatype`,`visaDetailsEN`,`visaDetailsBN`,`uploadedAt`)
                            VALUES ('$country','$category','$visaEN','$visaBN','$Time')";

            if ($conn->query($sqlquery) === TRUE) {
                $response['status']="success";
                $response['message']="Visa Added Successful";
            } else {
                $response['status']="success";
                $response['message']="Visa Added Failed";
            }
        }

        echo json_encode($response);
        
    }
      
}
                                            
                                      
            
            
?>