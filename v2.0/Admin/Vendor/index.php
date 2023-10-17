<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include_once '../../authorization.php';
if (authorization($conn) == true){  
    if(array_key_exists("all", $_GET)){
        $Data = $conn->query("SELECT * FROM vendor")->fetch_all(MYSQLI_ASSOC);
        if(!empty($Data)){
            $response['data'] = $Data;
        }else{
            $response['status'] ="error";
            $response['message'] ="Data Not Found";
        }
        echo json_encode($response);
    }else if(array_key_exists("add", $_GET)){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = json_decode(file_get_contents("php://input"), true);
            $Name = $_POST['name'];
            $Email = $_POST['email'];
            $Phone = $_POST['phone'];
            $Address = $_POST['address'];
            $PccNumber = $_POST['pccnumber'];
            $ItaNumber = $_POST['itanumber'];
            $System  = $_POST['system'];
            $DateTime =date("Y-m-d H:i:s");

            $VendorId = "";
            $sql = "SELECT * FROM vendor ORDER BY vendorId DESC LIMIT 1";
            $result = $conn->query($sql);
            if($result ->num_rows >0){
                    while($row = mysqli_fetch_array($result)){
                            $outputString = $row['id'];
                            $number = 1000 + $outputString;
                            $VendorId = "FFVN$number";
                    }
            }else{
                $VendorId = "FFVN1000";
            }
            $sql = "INSERT INTO vendor (vendorId,name, email, phone, address, pccnumber, itanumber, `system`, createdAt) VALUES('$VendorId', '$Name', '$Email', '$Phone','$Address','$PccNumber','$ItaNumber','$System','$DateTime')";

            if($conn->query($sql) === true){
                    $response['status'] = "success";
                    $response['vendorId'] = $VendorId;
                    $response['message'] ="Vendor Added Successfully";
            }else{
                $response['status'] = "error";
                $response['message'] ="Query Failed";
            }
            echo json_encode($response);
        }
    }else if(array_key_exists("delete", $_GET)){   
        if($_SERVER['REQUEST_METHOD'] == "POST"){
            $_POST = json_decode(file_get_contents("php://input"), true);
            $Id = $_POST['id'];
            $VendorId = $_POST['vendorId'];

            $checker = $conn->query("SELECT * FROM vendor WHERE vendorId = '$VendorId'")->fetch_all(MYSQLI_ASSOC);

            if(!empty($checker)){
                $sql = "DELETE FROM vendor WHERE vendorId = '$VendorId'";
                if($conn->query($sql) === true){
                    $response['status'] = "success";
                    $response['message'] = "Vendor Deleted";
                }else{
                    $response['status'] = "error";
                    $response['message'] = "Query Failed";
                }
            }else{
                $response['status'] = "error";
                $response['message'] = "Vendor Not Found";
            }
            echo json_encode($response);

        }
    }else if(array_key_exists("edit", $_GET)){
            if($_SERVER['REQUEST_METHOD'] =="POST"){
                $_POST = json_decode(file_get_contents("php://input"), true);
                $Id = $_POST["id"];
                $VendorId = $_POST['vendorId'];
                $Name = $_POST['name'];
                $Email = $_POST['email'];
                $Phone = $_POST['phone'];
                $Address = $_POST['address'];
                $PccNumber = $_POST['pccnumber'];
                $ItaNumber = $_POST['itanumber'];
                $System  = $_POST['system'];
                $DateTime =date("Y-m-d H:i:s");

                $checker = $conn->query("SELECT * FROM vendor WHERE vendorId='$VendorId' AND id='$Id'")->fetch_all(MYSQLI_ASSOC);
                if(!empty($checker)){
                    $sql = "UPDATE vendor SET name='$Name', email='$Email', phone='$Phone', pccnumber='$PccNumber', itanumber='$ItaNumber', `system` ='$System', updatedAt='$DateTime' WHERE vendorId='$VendorId' AND id='$Id'";
                    if($conn->query($sql) === true){
                    $response['status'] = "success";
                    $response['message'] = "Vendor updated successfully";
                    }else{
                    $response['status'] = "error";
                    $response['message'] = "Vendor Update Query Failed";
                    }

                }else{
                    $response['status'] = "error";
                    $response['message'] = "Vendor Not Found";
                }

                echo json_encode($response);
            }
    }

}else{
  authorization($conn);
}