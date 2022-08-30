<?php

  // Connection to DB
  $servername = "localhost";
  $username = "root";
  $password = "";
  $myDB = "map_y1";
  $conn = mysqli_connect($servername, $username, $password, $myDB);

    $prjCode =  $_POST['prjCode'];
    $reply="</br>";

    $delete = "DELETE FROM prj_code_list WHERE prj_Code_List = '" . $prjCode."'";
    if ($conn->query($delete) === TRUE) {
        
        $reply = $reply . "<b>" .$prjCode . "</b> project code deleted. </br> ";

        $delete2 = "RENAME TABLE " . $prjCode ."_pointCollection TO 000_TO_BE_DELETED_" . $prjCode ."_pointCollection";
        if ($conn->query($delete2) === TRUE) {
            $reply = $reply . "<b>" .$prjCode . "_pointCollection</b> table deleted. </br> ";
        } else {
            $reply = $reply . "Impossible to delete <b>" .$prjCode . "_pointCollection</b>. </br> ";
        }
    
        $delete3 = "RENAME TABLE " . $prjCode ."_results TO 000_TO_BE_DELETED_" . $prjCode ."_results";
        if ($conn->query($delete3) === TRUE) {
            $reply = $reply . "<b>" . $prjCode ."_results</b> table deleted. </br> ";
        } else {
            $reply = $reply . "Impossible to delete <b>" . $prjCode ."_results</b>. </br> ";
        }

        $delete4 = "RENAME TABLE " . $prjCode ."_results_cleaned TO 000_TO_BE_DELETED_" . $prjCode ."_results_cleaned";
        if ($conn->query($delete4) === TRUE) {
            $reply = $reply . "<b>" . $prjCode ."_results_cleaned</b> table deleted. </br> ";
        } else {
            $reply = $reply . "Impossible to delete <b>" . $prjCode ."_results_cleaned</b>. </br> ";
        }

    } else {
        $reply = $reply . "Impossible to delete <b>" .$prjCode . "</b>. Make sure the project code exist. </br> ";
    }





echo $reply;

?>