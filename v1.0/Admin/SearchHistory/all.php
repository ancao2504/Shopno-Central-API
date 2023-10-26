<?php

require '../../config.php';
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (array_key_exists("all", $_GET)) {

  $sql = "SELECT
    search_history.id,
    agent.company,
    agent.phone,
    search_history.searchId,
    search_history.agentId,
    search_history.searchtype,
    search_history.DepFrom,
    search_history.ArrTo,
    search_history.class,
    search_history.searchTime,
    search_history.depTime,
    search_history.adult,
    search_history.child,
    search_history.infant,
    search_history.returnTime,
    search_history.searchBy
FROM 
    search_history
JOIN agent ON search_history.agentId = agent.agentId ORDER BY id DESC LIMIT 300";
  $result = $conn->query($sql);

  $return_arr = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $response = $row;
      array_push($return_arr, $response);
    }
  }

  echo json_encode($return_arr);
} else if (array_key_exists("agentId", $_GET)) {

  $agentId = $_GET["agentId"];

  $sql = "SELECT
            search_history.id,
            agent.company,
            search_history.searchId,
            search_history.agentId,
            search_history.searchtype,
            search_history.DepFrom,
            search_history.ArrTo,
            search_history.class,
            search_history.searchTime,
            search_history.depTime,
            search_history.adult,
            search_history.child,
            search_history.infant,
            search_history.returnTime,
            search_history.searchBy
        FROM
            search_history
        JOIN agent ON search_history.agentId = agent.agentId
        where agentId='$agentId' ORDER BY id DESC";
  $result = $conn->query($sql);

  $return_arr = array();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $response = $row;
      array_push($return_arr, $response);
    }
  }

  echo json_encode($return_arr);
} else if (array_key_exists("b2b", $_GET)) {
  $sql="SELECT
    s.agentId,
    SUM(CASE WHEN s.searchtype = 'oneway' THEN 1 ELSE 0 END) AS onewaySearchCount,
    SUM(CASE WHEN s.searchtype = 'return' THEN 1 ELSE 0 END) AS roundwaySearchCount,
    a.company AS company,
    a.phone AS contact
    FROM
        search_history AS s, agent AS a
    WHERE 
      s.agentId=a.agentId
    GROUP BY
        s.agentId;";
  
  $result = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

  echo json_encode($result);
}
