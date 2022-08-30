<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $myDB = "map_y1";


    $reply ="<b>MESSAGE: </b>";


// Read or Create DB

        $conn = mysqli_connect($servername, $username, $password);
        $createDB = "CREATE DATABASE IF NOT EXISTS map_y1";

        if ($conn->query($createDB) === TRUE) {
            $conn = mysqli_connect($servername, $username, $password, $myDB);
            $reply = $reply .  "I am connected to DB: " . $myDB  .  "  </br>";
        } else {
            $reply = $reply .  "Error: " . $createDB . "<br>" . $conn->error;
        };



// Read or Create prj_Code_List
    $conn = mysqli_connect($servername, $username, $password, $myDB);
    $createTable = "CREATE TABLE prj_Code_List (
        prj_Code_List varchar(255)
    )";

    if ($conn->query($createTable) === TRUE) {
        $reply = $reply .  "prj_Code_List table created successfully. ";
    } else {
        $reply = $reply .  "prj_Code_List table already exist. ";

        // Read list of prj  -  this is the same also in readCreateDB.php and in checkPrjCode.php
        $sql = "SELECT prj_Code_List FROM prj_Code_List";
        $result = $conn->query($sql);
        $reply = $reply . "</br></br>";

        if($result-> num_rows > 0){

            $reply = $reply .  '<table style="width:100%"><tr>';
            $reply = $reply .     '<td><b>PROJECT</b></td>';
            $reply = $reply .     '<td><b>Spiral</b></td>';
            $reply = $reply .     '<td><b>Page x Point</b></td>';
            $reply = $reply .     '<td><b>Pages TOT</b></td>';
            $reply = $reply .     '<td><b>Pages DONE</b></td>';
            $reply = $reply .     '<td><b>Pages BALANCE</b></td>';
            $reply = $reply .     '<td><b>SHOP FOUND</b></td>';
            $reply = $reply .     '<td><b>Website TOT</b></td>';
            $reply = $reply .     '<td><b>Website DONE</b></td>';
            $reply = $reply .     '<td><b>Website BALANCE</b></td>';
            $reply = $reply .     '<td><b>EMAIL FOUND</b></td>';
            $reply = $reply .  '</tr>';

            while ($row = $result->fetch_assoc()){

                $resultB = $conn->query("SELECT COUNT(DISTINCT City) FROM ".$row ["prj_Code_List"]."_pointCollection WHERE City like '%.%,%.%'" );
                    if (mysqli_fetch_row($resultB)[0] > 0) {
                        $spiral = "yes";
                        $resultB = $conn->query("SELECT pageTotal FROM ".$row ["prj_Code_List"]."_pointCollection WHERE ID = 0");
                        $PagePerPoint = mysqli_fetch_row($resultB)[0];
                    } else {
                        $spiral = "no";
                        $PagePerPoint = "n/a";
                    }

                $resultB = $conn->query("SELECT SUM(pageTotal) FROM ".$row ["prj_Code_List"]."_pointCollection");
                while($rowB = mysqli_fetch_array($resultB)){
                    $PagesTOT = $rowB['SUM(pageTotal)'];
                }
                $resultB = $conn->query("SELECT SUM(pageCount) FROM ".$row ["prj_Code_List"]."_pointCollection");
                while($rowB = mysqli_fetch_array($resultB)){
                    $PagesDONE = $rowB['SUM(pageCount)'];
                }

                if ( $PagesTOT == $PagesDONE){
                    $PagesBALANCE = "<b>COMPLETED</b>";
                } else {
                    $PagesBALANCE = $PagesTOT - $PagesDONE;
                }

                $resultB = $conn->query("SELECT COUNT(*) FROM ".$row ["prj_Code_List"]."_results");
                $SHOPFOUND = mysqli_fetch_row($resultB)[0];
                
                if ($SHOPFOUND == 0) {
                    $WebsiteTOT = 0;
                    $WebsiteDONE = 0;
                    $WebsiteBALANCE = 0;
                    $EMAILFOUND = 0;
                } else {

                    $resultB = $conn->query("SELECT COUNT(DISTINCT Website) FROM ".$row ["prj_Code_List"]."_results" );
                    $WebsiteTOT = mysqli_fetch_row($resultB)[0];

                    
                    $resultB = $conn->query("SELECT COUNT(DISTINCT Website) FROM ".$row ["prj_Code_List"]."_results WHERE email = ''" );
                    $WebsiteDONE = $WebsiteTOT - mysqli_fetch_row($resultB)[0];
                    

                    if ( $WebsiteTOT == $WebsiteDONE){
                        $WebsiteBALANCE = "<b>COMPLETED</b>";
                    } else {
                        $WebsiteBALANCE = $WebsiteTOT - $WebsiteDONE;
                    }

                    $resultB = $conn->query("SELECT COUNT(DISTINCT email) FROM ".$row ["prj_Code_List"]."_results" );
                        $EMAILFOUND = mysqli_fetch_row($resultB)[0];
                
                }


                $reply = $reply .  '<tr>';
                $reply = $reply .     "<td><b>" . $row ["prj_Code_List"] . "</b></td>";
                $reply = $reply .     "<td>" . $spiral . "</td>";
                $reply = $reply .     "<td>" . $PagePerPoint . "</td>";
                $reply = $reply .     "<td>" . $PagesTOT . "</td>";
                $reply = $reply .     "<td>" . $PagesDONE . "</td>";
                $reply = $reply .     "<td>" . $PagesBALANCE . "</td>";
                $reply = $reply .     "<td>" . $SHOPFOUND . "</td>";
                $reply = $reply .     "<td>" . $WebsiteTOT . "</td>";
                $reply = $reply .     "<td>" . $WebsiteDONE . "</td>";
                $reply = $reply .     "<td>" . $WebsiteBALANCE . "</td>";
                $reply = $reply .     "<td>" . $EMAILFOUND . "</td>";
                $reply = $reply .  '</tr>';
            
            }; 

            $reply = $reply .  '</table>';
        
        } else {
                $reply = $reply .  "Project Code List is empty. ";
        };
        // END of Read list of prj


    };

    echo $reply


?>