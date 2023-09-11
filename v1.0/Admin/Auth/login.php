 <?php

  require '../../config.php';

  header("Access-Control-Allow-Origin: *");
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
  header("Access-Control-Max-Age: 3600");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

  if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $_POST = json_decode(file_get_contents('php://input'), true);

    $email = $_POST['email'];
    $password = $_POST['password'];

    
    $adminSql = mysqli_query($conn, "SELECT `email`, `status` FROM admin WHERE email='$email'");
    $adminRow = mysqli_fetch_array($adminSql, MYSQLI_ASSOC);

    $staffSql = mysqli_query($conn, "SELECT `email`, `status` FROM admin_stafflist WHERE email='$email'");
    $staffRow = mysqli_fetch_array($staffSql, MYSQLI_ASSOC);
    // echo json_encode ($adminRow);
    // echo json_encode ($staffRow);


    // 
    if (empty($adminRow) && empty($staffRow)) {
      $response['status'] = "error";
      $response['message'] = "User does not exist";
      echo json_encode($response);
    } 
    else if (!empty($adminRow)) {
      $sql = "SELECT * FROM admin WHERE `email`='$email' and `password`='$password'";
      $result = mysqli_query($conn, $sql);
      if (mysqli_num_rows($result) > 0) {
        $response['user'] = $result->fetch_assoc();
        $response['status'] = "complete";
        $response['message'] = "success";
      } else {
        $response['status'] = "Incomplete";
        $response['message'] = "Wrong Password";
      }
      echo json_encode($response);
    } 
    else if (!empty($staffRow)) {
      $sql = "SELECT * FROM admin_stafflist WHERE `email`='$email' and `password`='$password'";
      $result = mysqli_query($conn, $sql);
      if (mysqli_num_rows($result) > 0) {
        $response['user'] = $result->fetch_assoc();
        $response['status'] = "complete";
        $response['message'] = "success";
      } else {
        $response['status'] = "Incomplete";
        $response['message'] = "Wrong Password";
      }
      echo json_encode($response);
    }
  }

  ?>