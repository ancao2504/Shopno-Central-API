<?php

include_once('../config.php');

$sql = "SELECT * FROM `search_history`";
  $result = $conn->query($sql);
  $count = 0;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()){
            $count++;
            $searchId = $row['searchId'];
            $agentId = $row['agentId'];
            $query = mysqli_query($conn, "SELECT * FROM agent WHERE agentId='$agentId'");
            $data = mysqli_fetch_assoc($query);
            $companyname = $data['company'];
            $compnayphone = $data['phone']; 

            $sqlUpdate = "UPDATE search_history SET company='$companyname', phone='$compnayphone' where agentId='$agentId'";

            if ($conn->query($sqlUpdate) === TRUE) {

                echo "$count. Upadated Successfully For SearchID-$searchId and AgentId-$agentId </br>";
            }else{
                echo "$count. Upadated Failed For SearchID-$searchId and AgentId-$agentId </br>";
            }
         
        }
    }
?>