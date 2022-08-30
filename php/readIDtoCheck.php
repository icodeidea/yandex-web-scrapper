<?php

  // Connection to DB
  $servername = "localhost";
  $username = "root";
  $password = "";
  $myDB = "map_y1";
  $conn = mysqli_connect($servername, $username, $password, $myDB);

$prjCode =  $_POST['prjCode'];


// 5 - Calculate balance pages and read ID to check for the prj

    $result = $conn->query("SELECT SUM(pageTotal) FROM ".$prjCode."_pointCollection");
    while($rowB = mysqli_fetch_array($result)){
        $totPoint = $rowB['SUM(pageTotal)'];
    }
    $result = $conn->query("SELECT SUM(pageCount) FROM ".$prjCode."_pointCollection");
    while($rowB = mysqli_fetch_array($result)){
        $srcPoint = $rowB['SUM(pageCount)'];
    }

    //$reply = $totPoint - $srcPoint;

// read ID to check for the prj
    $reply = '';

    $sql = "SELECT * FROM ".$prjCode."_pointCollection";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $reply = $reply . $row["ID"] ."^'". $row["City"] ."^'". $row["cat"] ."^'". $row["pageTotal"] ."^'". $row["pageCount"] . "/";
    }} else {
    $reply = "Please check the prj data. It seems to be empty.";
    }

    $num = json_encode ($totPoint - $srcPoint);
    $reply = $reply . $num;


echo $reply;

?>