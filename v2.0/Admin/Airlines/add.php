<?php
include("../../config.php");

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../../authorization.php';
if (authorization($conn) == true){   
    if ($_SERVER["REQUEST_METHOD"] == "POST"){
            
        $_POST = json_decode(file_get_contents('php://input'), true);

        $sql1 = "SELECT * FROM com_airlines_history ORDER BY id DESC LIMIT 1";
            $result = $conn->query($sql1);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $Ref_Id = $row['id'] + 1;
                }
            } else {
                $Ref_Id = "100000";
            }
            
            $code = $_POST['code'];    
            $name = $_POST['name'];
            $nameBangla = $_POST['nameBangla'];
            $commission = $_POST['commission'];

            $sabreaddamount = $_POST['sabreaddamount'];    
            $sabredomestic = $_POST['sabredomestic'];
            $sabresotto = $_POST['sabresotto'];
            $sabresotti = $_POST['sabresotti'];
            $sabresitti = $_POST['sabresitti'];

            $galileoaddamount = $_POST['galileoaddamount'];    
            $galileodomestic = $_POST['galileodomestic'];
            $galileosotto = $_POST['galileosotto'];
            $galileosotti = $_POST['galileosotti'];
            $galileositti = $_POST['galileositti'];

            $flyhubaddamount = $_POST['flyhubaddamount'];    
            $flyhubdomestic = $_POST['flyhubdomestic'];
            $flyhubsotto = $_POST['flyhubsotto'];
            $flyhubsotti = $_POST['flyhubsotti'];
            $flyhubsitti = $_POST['flyhubsitti'];

            $sottocurrency = $_POST['sottocurrency'];
            $sotticurrency = $_POST['sotticurrency'];
            $sitticurrency = $_POST['sitticurrency'];

            $created_at =date('Y-m-d H:i:s');
            $created_by = $_POST['created_by'];

            $sqlCr = mysqli_query($conn,"SELECT code, name FROM airlines WHERE code='$code' ");
            $rowCr = mysqli_fetch_array($sqlCr,MYSQLI_ASSOC);

            if(!empty($rowCr)){
                $response['status']="error";
                $response['message']="Airlines Code Already Added";							
            }else{
                
            $sql = "INSERT INTO `airlines`(`ref_id`,`code`,`name`, `nameBangla`,`commission`, `sabreaddamount`, `sabredomestic`, `sabresotto`, `sabresotti`, `sabresitti`,`galileoaddamount`, `galileodomestic`, `galileosotto`, `galileosotti`, `galileositti`,`flyhubaddamount`,`flyhubdomestic`,`flyhubsotto`,`flyhubsotti`,`flyhubsitti`,`sottocurrency`,`sotticurrency`,`sitticurrency`,`updated_by`,`updated_at`) VALUES ('$Ref_Id','$code','$name','$nameBangla','$commission','$sabreaddamount','$sabredomestic','$sabresotto','$sabresotti','$sabresitti','$galileoaddamount','$galileodomestic','$galileosotto','$galileosotti','$galileositti',' $flyhubaddamount','$flyhubdomestic','$flyhubsotto','$flyhubsotti','$flyhubsitti',$sottocurrency, $sotticurrency,  $sitticurrency,'$created_at','$created_by')";
            
            
            $sql = "INSERT INTO `airlines`(`ref_id`,`code`,`name`, `nameBangla`,`commission`, `sabreaddamount`, `sabredomestic`, `sabresotto`, `sabresotti`, `sabresitti`,`galileoaddamount`, `galileodomestic`, `galileosotto`, `galileosotti`, `galileositti`,`flyhubaddamount`,`flyhubdomestic`,`flyhubsotto`,`flyhubsotti`,`flyhubsitti`,`sottocurrency`,`sotticurrency`,`sitticurrency`) VALUES ('$Ref_Id','$code','$name','$nameBangla','$commission','$sabreaddamount','$sabredomestic','$sabresotto','$sabresotti','$sabresitti','$galileoaddamount','$galileodomestic','$galileosotto','$galileosotti','$galileositti',' $flyhubaddamount','$flyhubdomestic','$flyhubsotto','$flyhubsotti','$flyhubsitti',$sottocurrency, $sotticurrency,  $sitticurrency, '$created_at','$created_by')";
            
            if ($conn->query($sql) === TRUE) {
                $response['status']="sucess";
                $response['message']="Airlines Added Successfully";          
            } else {
                $response['status']="error";
                $response['message']="Airlines Added Failed";
            }
        
        }
            
        echo json_encode($response);
        
    }
}else{
  authorization($conn);
}

?>