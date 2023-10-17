<?php

include("../../config.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';

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

          $galileoaddamount = $_POST['sabreaddamount'];    
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

          $created_at = date('Y-m-d H:i:s');
          $created_by = $_POST['updated_by'];
          
                
          $sql = "UPDATE `airlines` SET `ref_id`='$Ref_Id', `code`='$code',`name`='$name',`nameBangla`='$nameBangla',`commission`='$commission', `sabreaddamount`='$sabreaddamount',`sabredomestic`='$sabredomestic',`sabresotto`='$sabresotto',`sabresotti`='$sabresotti',
          `sabresitti`='$sabresitti',`galileoaddamount`='$galileoaddamount',`galileodomestic`='$galileodomestic',`galileosotto`='$galileosotto',`galileosotti`='$galileosotti',`galileositti`='$galileositti',`flyhubaddamount`='$flyhubaddamount',`flyhubdomestic`='$flyhubdomestic',`flyhubsotto`='$flyhubsotto',`flyhubsotti`='$flyhubsotti',`flyhubsitti`='$flyhubsitti',`sottocurrency`='$sottocurrency', `sotticurrency`='$sotticurrency', `sitticurrency`='$sitticurrency', `updated_at`='$created_at',`updated_by`='$created_by' WHERE code='$code'";
          

          $sql1 = "INSERT INTO `com_airlines_history`(`ref_id`,`code`,`name`, `nameBangla`,`commission`, `sabreaddamount`, `sabredomestic`, `sabresotto`, `sabresotti`, `sabresitti`,`galileoaddamount`, `galileodomestic`, `galileosotto`, `galileosotti`, `galileositti`,`flyhubaddamount`,`flyhubdomestic`,`flyhubsotto`,`flyhubsotti`,`flyhubsitti`,`sottocurrency`,`sotticurrency`,`sitticurrency`,`created_at`,`created_by`) VALUES ('$Ref_Id','$code','$name','$nameBangla','$commission','$sabreaddamount','$sabredomestic','$sabresotto','$sabresotti','$sabresitti','$galileoaddamount','$galileodomestic','$galileosotto','$galileosotti','$galileositti',' $flyhubaddamount','$flyhubdomestic','$flyhubsotto','$flyhubsotti','$flyhubsitti','$sottocurrency','$sotticurrency','$sitticurrency','$created_at','$created_by')";

          
          $EmailTemplete = "<!DOCTYPE html>
  <html lang='en'>
    <head>
      <meta charset='UTF-8' />
      <meta http-equiv='X-UA-Compatible' content='IE=edge' />
      <meta name='viewport' content='width=device-width, initial-scale=1.0' />
      <title>Deposit Request</title>
    </head>
    <body>
      <div class='div' style='width: 650px; height: 100vh; margin: 0 auto'>
        <div
          style='
            width: 650px;
            height: 200px;
            background: linear-gradient(121.52deg, #5d7f9e 0%, #003566 77.49%);
            border-radius: 20px 0px 20px 0px;
          '
        >
          <table
            border='0'
            cellpadding='0'
            cellspacing='0'
            align='center'
            style='
              border-collapse: collapse;
              border-spacing: 0;
              padding: 0;
              width: 650px;
              border-radius: 10px;
            '
          >
            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #000000;
                  font-family: sans-serif;
                  font-weight: bold;
                  font-size: 20px;
                  line-height: 38px;
                  padding-top: 30px;
                  padding-bottom: 10px;
                '
              >
                <a href='https://www.flyfarint.com/'
                  ><img src='https://cdn.flyfarint.com/logo.png' width='130px'
                /></a>
              </td>
            </tr>
          </table>

          <table
            border='0'
            cellpadding='0'
            cellspacing='0'
            align='center'
            bgcolor='white'
            style='
              border-collapse: collapse;
              border-spacing: 0;
              padding: 0;
              width: 550px;
            '
          >
            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #000000;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  padding-right: 20px;
                  font-weight: bold;
                  font-size: 19px;
                  line-height: 38px;
                  padding-top: 20px;
                  background-color: white;
                '
              >
                Commission Setup ||  $name || By $created_by || 
              </td>
            </tr>

            <tr>
            <td
                align='center'
                valign='top'
                style='
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
                  padding-top: 20px;
                  width: 100%;
                '
              >
                Modified By:
                <a style='color: #003566' href='http://' target='_blank'
                  >$created_by</a
                >
              </td>
            </tr>
            <tr>
            <td
                align='center'
                valign='top'
                style='
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
                  padding-top: 20px;
                  width: 100%;
                '
              >
                Ref Id:
                <a style='color: #003566' href='http://' target='_blank'
                  >$Ref_Id</a
                >
              </td>
            </tr>
              <td
                align='center'
                valign='top'
                style='
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
                  padding-top: 20px;
                  width: 100%;
                '
              >
                Airlines Name:
                <a style='color: #003566' href='http://' target='_blank'
                  >$name</a
                >
              </td>
            </tr>

            <tr>
              <td
                align='center'
                valign='top'
                style='
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
                  padding-top: 20px;
                  width: 100%;
                '
              >
                Default Commission:
                <a style='color: #003566' href='http://' target='_blank'> $commission%</a>
              </td>
            </tr>

            <tr>
              <td
                align='center'
                valign='top'
                style='
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
                  padding-top: 20px;
                  width: 100%;
                '
              >
                SOTTO Currency:
                <a style='color: #003566' href='http://' target='_blank'>$sottocurrency</a>
              </td>
            </tr>
            <tr>
              <td
                align='center'
                valign='top'
                style='
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
                  padding-top: 20px;
                  width: 100%;
                '
              >
                SOTTI Currency:
                <a style='color: #003566' href='http://' target='_blank'>$sotticurrency</a>
              </td>
            </tr>

            <tr>
              <td
                align='center'
                valign='top'
                style='
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
                  padding-top: 20px;
                  width: 100%;
                '
              >
                SITTI Currency:
                <a style='color: #003566' href='http://' target='_blank'>$sitticurrency</a>
              </td>
            </tr>

            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #e81c1c;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  padding-top: 20px;
                  font-size: 13px;
                  line-height: 18px;
                  padding-top: 30px;
                  width: 100%;
                '
              >
                Sabre Add Amount:
                <a style='color: #003566' href='http://' target='_blank'>$sabreaddamount %</a>
              </td>
            </tr>
            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #e81c1c;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  padding-top: 20px;
                  font-size: 13px;
                  line-height: 18px;
                  padding-top: 20px;
                  width: 100%;
                '
              >
                Sabre Domestic:
                <a style='color: #003566' href='http://' target='_blank'>$sabredomestic %</a>
              </td>
            </tr>

            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #e81c1c;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  padding-top: 20px;
                  font-size: 13px;
                  line-height: 18px;
                  padding-top: 20px;
                  width: 100%;
                '
              >
                Sabre Sotto:
                <a style='color: #003566' href='http://' target='_blank'>$sabresotto %</a>
              </td>
            </tr>

            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #e81c1c;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  padding-top: 20px;
                  font-size: 13px;
                  line-height: 18px;
                  padding-top: 20px;
                  width: 100%;
                '
              >
                Sabre Sotti:
                <a style='color: #003566' href='http://' target='_blank'>$sabresotti %</a>
              </td>
            </tr>
            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #e81c1c;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  padding-top: 20px;
                  font-size: 13px;
                  line-height: 18px;
                  padding-top: 20px;
                  width: 100%;
                '
              >
                Sabre Sitti:
                <a style='color: #003566' href='http://' target='_blank'>$sabresitti %</a>
              </td>
            </tr>

            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #00722d;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  padding-top: 20px;
                  font-size: 13px;
                  line-height: 18px;
                  padding-top: 30px;
                  width: 100%;
                '
              >
                Galileo Add Amount:
                <a style='color: #003566' href='http://' target='_blank'> $galileoaddamount%</a>
              </td>
            </tr>
            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #00722d;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  padding-top: 20px;
                  font-size: 13px;
                  line-height: 18px;
                  padding-top: 20px;
                  width: 100%;
                '
              >
                Galileo Domestic:
                <a style='color: #003566' href='http://' target='_blank'>$galileodomestic%</a>
              </td>
            </tr>

            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #00722d;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  padding-top: 20px;
                  font-size: 13px;
                  line-height: 18px;
                  padding-top: 20px;
                  width: 100%;
                '
              >
                Galileo Sotto:
                <a style='color: #003566' href='http://' target='_blank'>$galileosotto %</a>
              </td>
            </tr>

            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #00722d;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  padding-top: 20px;
                  font-size: 13px;
                  line-height: 18px;
                  padding-top: 20px;
                  width: 100%;
                '
              >
                Galileo Sotti:
                <a style='color: #003566' href='http://' target='_blank'>$galileosotti %</a>
              </td>
            </tr>
            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #00722d;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  padding-top: 20px;
                  font-size: 13px;
                  line-height: 18px;
                  padding-top: 20px;
                  width: 100%;
                '
              >
                Galileo Sitti:
                <a style='color: #003566' href='http://' target='_blank'>$galileositti %</a>
              </td>
            </tr>

            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #003566;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  padding-top: 20px;
                  font-size: 13px;
                  line-height: 18px;
                  padding-top: 30px;
                  width: 100%;
                '
              >
                Flyhub Add Amount:
                <a style='color: #003566' href='http://' target='_blank'>$flyhubaddamount%</a>
              </td>
            </tr>
            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #003566;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  padding-top: 20px;
                  font-size: 13px;
                  line-height: 18px;
                  padding-top: 20px;
                  width: 100%;
                '
              >
                Flyhub Domestic:
                <a style='color: #003566' href='http://' target='_blank'>$flyhubdomestic%</a>
              </td>
            </tr>

            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #003566;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  padding-top: 20px;
                  font-size: 13px;
                  line-height: 18px;
                  padding-top: 20px;
                  width: 100%;
                '
              >
                Flyhub Sotto:
                <a style='color: #003566' href='http://' target='_blank'>$flyhubsotto%</a>
              </td>
            </tr>

            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #003566;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  padding-top: 20px;
                  font-size: 13px;
                  line-height: 18px;
                  padding-top: 20px;
                  width: 100%;
                '
              >
                Flyhub Sotti:
                <a style='color: #003566' href='http://' target='_blank'>$flyhubsotti%</a>
              </td>
            </tr>
            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #003566;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  padding-top: 20px;
                  font-size: 13px;
                  line-height: 18px;
                  padding-top: 20px;
                  width: 100%;
                '
              >
                Flyhub Sitti:
                <a style='color: #003566' href='http://' target='_blank'>$flyhubsitti%</a>
              </td>
            </tr>

            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #003566;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  padding-top: 20px;
                  font-size: 13px;
                  line-height: 18px;
                  color: #929090;
                  padding-top: 20px;
                  width: 100%;
                  background-color: white;
                '
              >
                Sincerely,
              </td>
            </tr>

            <tr>
              <td
                align='center'
                valign='top'
                style='
                  border-collapse: collapse;
                  border-spacing: 0;
                  color: #000000;
                  font-family: sans-serif;
                  text-align: left;
                  padding-left: 20px;
                  font-weight: bold;
                  font-size: 13px;
                  line-height: 18px;
                  color: #929090;
                  width: 100%;
                  background-color: white;
                  padding-bottom: 20px;
                '
              >
                Fly Far Tech Team
              </td>
            </tr>
          </table>

        </div>
      </div>
    </body>
  </html>";


          if ($conn->query($sql) === TRUE) {
            if ($conn->query($sql1) === TRUE) {
              
              $mail1 = new PHPMailer();
              $mail1->isSMTP();                                    
              $mail1->Host       = 'b2b.flyfarint.com';                     
              $mail1->SMTPAuth   = true;                                  
              $mail1->Username   = 'job@b2b.flyfarint.com';                    
              $mail1->Password   = '123Next2$';                            
              $mail1->SMTPSecure = 'ssl';            
              $mail1->Port       = 465;                                    
              $mail1->setFrom('job@b2b.flyfarint.com', 'Airlines Comission');
              $mail1->addAddress("otaoperation@flyfarint.com", "Airlines Comission");
              $mail1->addCC("fahim@flyfarint.com", "Airlines Comission");
              $mail1->addCC("afridi@flyfarint.com", "Airlines Comission");           
              $mail1->isHTML(true);                                  
              $mail1->Subject = "Airlines Comission";
              $mail1->Body    =  $EmailTemplete;

              if(!$mail1->Send()) {
                  echo "Mailer Error: " . $mail1->ErrorInfo;
              }
            }
              
              $response['status']="success";
              $response['message']="Airlines Updated Successfully";                     
          }else{
              $response['status']="error";
              $response['message']="Updated Failed Successfully";
          }
          
      echo json_encode($response);
      
  }

}else{
  authorization($conn);
}


?>