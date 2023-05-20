<?php
include("../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        
        $_POST = json_decode(file_get_contents('php://input'), true);
        
        $Search_Id = 
        $sql = "SELECT * FROM search_history ORDER BY id DESC LIMIT 1";
        $result = $conn->query($sql);
       if($result->num_rows > 0){
            while($row = $result->fetch_assoc()){
                $outputString = preg_replace('/[^0-9]/', '', $row["searchId"]);
                $number = (int) $outputString + 1;
                $Search_Id = "STS$number";
            }
       }else{
        $Search_Id = "STS100";
       }
        

        $agentId = $_POST['agentid'];

        $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
        $data = mysqli_fetch_assoc($query);
        $companyname = $data['company'];
        $compnayphone = $data['phone'];

        
        $searchBy = $_POST['searchBy'];
        $searchtype = $_POST['searchtype'];
        $DepFrom = $_POST['DepFrom'];
        $DepAirport =  str_replace("'", "''",$_POST['DepAirport']);
        $ArrTo = $_POST['ArrTo'];
        $ArrAirport =  str_replace("'", "''",$_POST['ArrAirport']);
        $Class = $_POST['class'];
        $depTime = $_POST['depTime'];
        $returnTime = $_POST['returnTime'];
        $adult = $_POST['adult'];
        $child = $_POST['child'];
        $infant = $_POST['infant'];
        $searchTime = date('Y-m-d H:i:s');

        $sql = "INSERT INTO `search_history`(
            `searchId`,
            `agentId`,
            `company`,
            `phone`,
            `searchBy`,
            `searchtype`,
            `DepFrom`,
            `DepAirport`,
            `ArrTo`,
            `ArrAirport`,
            `class`,
            `depTime`,
            `returnTime`,
            `adult`,
            `child`,
            `infant`,
            `searchTime`
        )
        VALUES(
            '$Search_Id',
            '$agentId',
            '$companyname',
            '$compnayphone',
            '$searchBy',
            '$searchtype',
            '$DepFrom',
            '$DepAirport',
            '$ArrTo',
            '$ArrAirport',
            '$Class',
            '$depTime',
            '$returnTime',
            '$adult',
            '$child',
            '$infant',
            '$searchTime'
        )";

        if ($conn->query($sql) === TRUE) {
            $response['status']="success";
            $response['message']="Saved Successfully";          
        } else {
            $response['status']="error";
            $response['message']="Saving failed";
        }

        echo json_encode($response);
    }
        
    
?>