<?php
include "../../config.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require '../../vendor/autoload.php';
include_once '../../authorization.php';
if (authorization($conn) == true){ 
  if (array_key_exists("add", $_GET)) {
      if ($_SERVER['REQUEST_METHOD'] == "POST") {
          $_POST = json_decode(file_get_contents("php://input"), true);
          $agency = $_POST['agency'];
          $flightType = $_POST['flighttype'];
          $depAir = $_POST['depair'];
          $arriAir = $_POST['arriair'];
          $journeyType = $_POST['journeytype'];
          $airlines = $_POST['airlines'];
          $flightNumber = $_POST['flightnumber'];
          $class = $_POST['class'];
          $grossFare = $_POST['grossfare'];
          $baseFare = $_POST['basefare'];
          $tax = $_POST['tax'];
          $ait = $_POST['ait'];
          $travelDate = $_POST['traveldate'];
          $date = date('Y-m-d H:i:s');

          for ($i = 0; $i < count($agency); $i++) {
              $agencyname = $_POST['agency'][$i]['agencyname'];
              $agencynetcost = $_POST['agency'][$i]['agencynetcost'];
              $agencycm = $_POST['agency'][$i]['agencycm'];

              $sql = "INSERT INTO flight_fair(agency, flight_type, depAir, arrivAir,journeytype, airlines, flightnumber, class, grossfare, basefare, tax, ait, traveldate, agencynetcost,agencycm, created_at)VALUES('$agencyname', '$flightType', '$depAir','$arriAir', '$journeyType','$airlines', '$flightNumber', '$class', '$grossFare', '$baseFare', '$tax','$ait','$travelDate', '$agencynetcost','$agencycm','$date')";
              if ($conn->query($sql)) {
                  $response['status'] = 'success';
                  $response['message'] = "New Fare Added";
              } else {
                  $response['status'] = 'error';
                  $response['message'] = "Query Failed";
              }

          }
          echo json_encode($response);
      }
  } else if (array_key_exists("edit", $_GET)) {
      if ($_SERVER['REQUEST_METHOD'] == "POST") {
          $_POST = json_decode(file_get_contents("php://input"), true);
          $id = $_POST['id'];
          $flightType = $_POST['flighttype'];
          $depAir = $_POST['depair'];
          $arriAir = $_POST['arriair'];
          $journeyType = $_POST['journeytype'];
          $airlines = $_POST['airlines'];
          $flightNumber = $_POST['flightnumber'];
          $class = $_POST['class'];
          $grossFare = $_POST['grossfare'];
          $baseFare = $_POST['basefare'];
          $tax = $_POST['tax'];
          $ait = $_POST['ait'];
          $travelDate = $_POST['traveldate'];
          $agencyname = $_POST['agencyname'];
          $agencynetcost = $_POST['agencynetcost'];
          $agencycm = $_POST['agencycm'];
          $date = date('Y-m-d H:i:s');

          $sql = "UPDATE flight_fair SET agency='$agencyname', flight_type='$flightType', depAir='$depAir', arrivAir='$arriAir',journeytype='$journeyType', airlines='$airlines', flightnumber='$flightNumber', class='$class', grossfare='$grossFare', basefare='$baseFare', tax='$tax', ait='$ait', traveldate='$travelDate', agencynetcost='$agencynetcost',agencycm='$agencycm', updated_at='$date' WHERE id = '$id'";
          if ($conn->query($sql)) {
              $response['status'] = 'success';
              $response['message'] = "Fare Updated";
          } else {
              $response['status'] = 'error';
              $response['message'] = "Query Failed";
          }

          echo json_encode($response);
      }
  } else if (array_key_exists("delete", $_GET)) {
      if ($_SERVER['REQUEST_METHOD'] == "POST") {
          $_POST = json_decode(file_get_contents("php://input"), true);
          $id = $_POST['id'];
          $checker = $conn->query("SELECT * FROM flight_fair WHERE id ='$id'")->fetch_all(MYSQLI_ASSOC);
          if (!empty($checker)) {
              $sql = "DELETE FROM flight_fair WHERE id = '$id'";
              if ($conn->query($sql)) {
                  $response['status'] = "success";
                  $response['message'] = "Fair Deleted";
              } else {
                  $response['status'] = "error";
                  $response['message'] = "Query Failed";
              }
          } else {
              $response['status'] = "error";
              $response['message'] = "Data not found";
          }
          echo json_encode($response);
      } else if (array_key_exists("searchInput", $_GET)) {
        $search = $_GET['searchInput'];
        $data = $conn->query("SELECT * FROM flight_fair WHERE agency ='$search' Or flight_type  = '$search' OR depAir = '$search' OR arrivAir= '$search' OR journeytype='$search' OR airlines = '$search' OR flightnumber='$search' OR class = '$search'")->fetch_all(MYSQLI_ASSOC);

        if (!empty($data)) {
            echo json_encode($data);
        } else {
            echo json_encode("Data Not Found");
        }
      }

  } else if (array_key_exists('all', $_GET)) {
      $getData = $conn->query("SELECT * FROM flight_fair")->fetch_all(MYSQLI_ASSOC);
      if (!empty($getData)) {
          echo json_encode($getData);
      } else {
          echo json_encode("Data not found");
      }
  } else if (array_key_exists('email', $_GET)) {
      $sql = "SELECT * FROM flight_fair";
      $row = $conn->query($sql);
      while($row = $conn->query($sql)) {

      $flightType = $row['flight_type'];
      $depAir = $row['depAir'];
      $arriAir = $row['arrivAir'];
      $airlines = $row['airlines'];
      $flightNumber = $row['flightnumber'];
      $class = $row['class'];
      $travelDate = $row['traveldate'];
      $grossFare = $row['grossfare'];
      $baseFare = $row['basefare'];
      $tax = $row['tax'];
      $agencyname = $row['agency'];
      $netcost = $row['agencynetcost'];
      $agencycm = $row['agencycm'];
      $journeyType = $row['journeytype'];

      $Date = date('Y-m-d');


      if ($journeyType == 'sotto') {
          $sotto = '
            <div>
            <p
              style="
                font-weight: bold;
                color: #003566;
                font-family: sans-serif;
                font-size: 12px;
              "
            >
              Fare Compare Report SOTTO
            </p>
          </div>

          <div>
            <table
              style="
                width: 100%;
                text-align: left;
                border-collapse: collapse;
                font-family: sans-serif;
              "
            >
              <tr
                style="
                  font-size: 12px;
                  color: #222222;
                  height: 33px;
                  font-weight: 400;
                  font-family: sans-serif;
                  font-size: 8px;
                "
              >
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Flight Type
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Departure
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Arrival
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Airline
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Flight Number
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Class
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Travel Date
                </th>

                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Gross Fare
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Base Fare
                </th>

                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Tax
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Agency Name
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Net Cost
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  CM (%)
                </th>
              </tr>

              <tr style="font-size: 8px; border-bottom: 1px solid#c7c7c7">
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
                  Round Way
                </td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
                  ' . $arriAir . '
                </td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
                  ' . $depAir . '
                </td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
                  ' . $airlines . '
                </td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
                  ' . $flightNumber . '
                </td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
                ' . $class . '
                </td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
                  ' . $travelDate . '
                </td>

                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
                ' . $grossFare . ' BDT
                </td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
                  ' . $baseFare . ' BDT
                </td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
                  ' . $tax . ' BDT
                </td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
                  ' . $agencyname . '
                </td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
                  ' . $netcost . ' BDT
                </td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
                  ' . $agencycm . '%
                </td>
              </tr>
            </table>
          </div>
          ';
      }if ($journeyType == 'sotti') {
          $sotti = '
          <div>
          <p
            style="
              font-weight: bold;
              color: #003566;
              font-family: sans-serif;
              font-size: 12px;
            "
          >
            Fare Compare Report SOTTI
          </p>
        </div>

        <div>
          <table
            style="
              width: 100%;
              text-align: left;
              border-collapse: collapse;
              font-family: sans-serif;
            "
          >
            <tr
              style="
                font-size: 12px;
                color: #222222;
                height: 33px;
                font-weight: 400;
                font-family: sans-serif;
                font-size: 8px;
              "
            >
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Flight Type
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Departure
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Arrival
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Airline
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Flight Number
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Class
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Travel Date
              </th>

              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Gross Fare
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Base Fare
              </th>

              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Tax
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Agency Name
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Net Cost
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                CM (%)
              </th>
            </tr>

            <tr style="font-size: 8px; border-bottom: 1px solid#c7c7c7">
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $flightType . '
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $depAir . '
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $arriAir . '
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $airlines . '
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $flightNumber . '
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $class . '
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $flightNumber . '
              </td>

              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $grossFare . ' BDT
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $baseFare . ' BDT
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $tax . ' BDT
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $agencyname . '
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $netcost . ' BDT
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
              ' . $agencycm . '%
              </td>
            </tr>
          </table>
        </div>
          ';
      } if ($flightType == 'sitti') {
          $sitti = '
          <div>
          <p
            style="
              font-weight: bold;
              color: #003566;
              font-family: sans-serif;
              font-size: 12px;
            "
          >
            Fare Compare Report SITTI
          </p>
        </div>

        <div>
          <table
            style="
              width: 100%;
              text-align: left;
              border-collapse: collapse;
              font-family: sans-serif;
            "
          >
            <tr
              style="
                font-size: 12px;
                color: #222222;
                height: 33px;
                font-weight: 400;
                font-family: sans-serif;
                font-size: 8px;
              "
            >
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Flight Type
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Departure
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Arrival
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Airline
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Flight Number
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Class
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Travel Date
              </th>

              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Gross Fare
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Base Fare
              </th>

              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Tax
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Agency Name
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                Net Cost
              </th>
              <th
                style="
                  background: #d7e3ee;
                  padding-left: 10px;
                  text-align: left;
                  font-family: sans-serif;
                "
              >
                CM (%)
              </th>
            </tr>

            <tr style="font-size: 8px; border-bottom: 1px solid#c7c7c7">
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
              ' . $flightType . '
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $depAir . '
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >

              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
              ' . $arriAir . '
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $flightNumber . '
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
              ' . $class . '
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $flightNumber . '
              </td>

              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
              ' . $grossFare . ' BDT
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $baseFare . ' BDT
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $tax . ' BDT
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
              ' . $agencyname . '
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $netcost . ' BDT
              </td>
              <td
                style="
                  text-align: left;
                  padding-top: 5px;
                  padding-left: 10px;
                  padding-bottom: 10px;
                  vertical-align: top;
                  font-family: sans-serif;
                  font-weight: 600;
                  color: #222222;
                "
              >
                ' . $agencycm . '%
              </td>
            </tr>
          </table>
        </div>
          ';
      }

      $email = '<!DOCTYPE html>
      <html lang="en">
        <head>
          <meta charset="UTF-8" />
          <meta http-equiv="X-UA-Compatible" content="IE=edge" />
          <meta name="viewport" content="width=device-width, initial-scale=1.0" />
          <title>Document</title>
        </head>
        <body
          style="
            width: 100%;
            height: 100vh;
            font-family: sans-serif;
            overflow-x: hidden;
          "
        >
          <table style="width: 100%; font-family: sans-serif">
            <tr>
              <td>
                <span
                  style="font-size: 15px; font-weight: 600; font-family: sans-serif"
                  >Fly Far International</span
                >
                <p
                  style="
                    font-size: 11px;
                    width: 280px;
                    font-weight: 600;
                    font-family: sans-serif;
                    color: #8c8c8c;
                  "
                >
                  Ka 11/2A, Bashundhara R/A, Jagannathpur, Dhaka
                </p>
                <p
                  style="
                    font-size: 11px;
                    margin-top: -10px;
                    font-weight: 600;
                    font-family: sans-serif;
                    color: #8c8c8c;
                  "
                >
                  <span style="color: #222222">Email: </span>support@flyfarint.com
                </p>
                <p
                  style="
                    font-size: 11px;
                    margin-top: -10px;
                    font-weight: 600;
                    font-family: sans-serif;
                    color: #8c8c8c;
                  "
                >
                  <span style="color: #222222">Phone: </span> 09606912912
                </p>
              </td>
              <td>
                <p
                  style="
                    text-align: right;
                    font-size: 25px;
                    font-weight: 600;
                    color: #ece8e8;
                    opacity: 20%;
                    font-family: sans-serif;
                    margin-right: 10px;
                  "
                >
                  Daily Fare Compare
                </p>
                <p
                  style="
                    text-align: right;
                    font-size: 12px;
                    font-weight: 700;
                    color: #003566;
                    font-family: sans-serif;
                    margin-right: 10px;
                  "
                >
            ' . $Date . '
                </p>
              </td>
            </tr>
          </table>

          <div>
            <p
              style="
                font-weight: bold;
                color: #003566;
                font-family: sans-serif;
                font-size: 12px;
              "
            >
              Fare Compare Report Domestic
            </p>
          </div>

          <div>
            <table
              style="
                width: 100%;
                text-align: left;
                border-collapse: collapse;
                font-family: sans-serif;
              "
            >
              <tr
                style="
                  font-size: 12px;
                  color: #222222;
                  height: 33px;
                  font-weight: 400;
                  font-family: sans-serif;
                  font-size: 8px;
                "
              >
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Flight Type
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Departure
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Arrival
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Airline
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Flight Number
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Class
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Travel Date
                </th>

                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Gross Fare
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Base Fare
                </th>

                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Tax
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Agency Name
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  Net Cost
                </th>
                <th
                  style="
                    background: #d7e3ee;
                    padding-left: 10px;
                    text-align: left;
                    font-family: sans-serif;
                  "
                >
                  CM (%)
                </th>
              </tr>

              <tr style="font-size: 8px; border-bottom: 1px solid#c7c7c7">
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
  ' . $flightType . '</td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
  ' . $arriAir . '</td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
  ' . $depAir . '</td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
  ' . $airlines . '</td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
  ' . $flightNumber . '</td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
  ' . $class . '</td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
  ' . $travelDate . '</td>

                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
  ' . $grossFare . ' BDT </td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
  ' . $baseFare . ' BDT</td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
  ' . $tax . 'BDT</td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
  ' . $agencyname . '</td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
  ' . $netcost . 'BDT</td>
                <td
                  style="
                    text-align: left;
                    padding-top: 5px;
                    padding-left: 10px;
                    padding-bottom: 10px;
                    vertical-align: top;
                    font-family: sans-serif;
                    font-weight: 600;
                    color: #222222;
                  "
                >
  ' . $agencycm . '</td>
              </tr>
            </table>
          </div>
            ' . $sitti . '
            ' . $sotto . '
            ' . $sotti . '
        </body>
      </html>
  ';

      $mail = new PHPMailer();
      try {
          $mail->isSMTP();
          $mail->Host = 'b2b.flyfarint.com';
          $mail->SMTPAuth = true;
          $mail->Username = 'job@b2b.flyfarint.com';
          $mail->Password = '123Next2$';
          $mail->SMTPSecure = 'ssl';
          $mail->Port = 465;

          //Recipients
          $mail->setFrom('warning@b2b.flyfarint.com', 'Fare Compare Report');
          $mail->addAddress("$companyEmail", "Fly Far International");
          $mail->addCC('habib@flyfarint.com');
          $mail->addCC('afridi@flyfarint.com');

          $mail->isHTML(true);
          $mail->Subject = "$bookingId Expire Warning";
          $mail->Body = $email;
          if (!$mail->Send()) {
              echo "Mailer Error: " . $mail->ErrorInfo;
          } else {

          }

      } catch (Exception $e) {
          $response['status'] = "error";
          $response['message'] = "Mail Doesn't Send";
      }
  }

  }
}else{
  authorization($conn);
}