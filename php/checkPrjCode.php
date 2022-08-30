<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $myDB = "map_y1";


    $reply ="</br> <b>MESSAGE: </b>";

    $prjCode =  $_POST['prjCode'];
    $pointCollection = $_POST['pointCollection'];

    $conn = mysqli_connect($servername, $username, $password, $myDB);

    $sql = "SELECT * FROM prj_code_list WHERE prj_Code_List = '$prjCode'";
        $result = $conn->query($sql);
        if($result->num_rows == 0) {
            
        // NEW PRJ ///////////////////////////////////////////////////////////
                // add prj_Code to prj_Code_List
                $savePrjName = "INSERT INTO prj_code_list VALUES ('$prjCode');";

                if ($conn->query($savePrjName) === TRUE) {
                    $reply = $reply . "Project created successfully   ||   ";
                } else {
                    $reply = $reply . "Error: " . $savePrjName . "<br>" . $conn->error . "   ||   " ;
                };


                //create Table for data point - point to be cheked
                    $createTable = "CREATE TABLE ". $prjCode ."_pointCollection (
                        ID int(255),
                        City varchar(255),
                        cat varchar(255),
                        pageTotal int(255),
                        pageCount int(255)
                    )";

                    if ($conn->query($createTable) === TRUE) {
                        $reply = $reply . "Table <b>". $prjCode ."_pointCollection </b> created successfully <br>";
                    } else {
                        $reply = $reply .  "Warning creating table: " . $conn->error . "<br>";
                    };

                    // Load point to be cheched
                    foreach ($pointCollection as $keyA => $point) {
                        $point_str = [];
                        foreach ($point as $key => $value) { $point_str [$key] = '"' . $value . '"'; };
                        $point_str = $keyA . ',' . implode (",", $point_str) . ',' . 0;
                        $writeTable = "INSERT INTO ". $prjCode ."_pointCollection
                        VALUES ($point_str);";

                        if ($conn->query($writeTable) === !TRUE) {
                            echo "Error: " . $writeTable . "<br>" . $conn->error;
                        }
                        if (count ($pointCollection)-1 == $keyA) {
                            $reply = $reply .  "Created and saved <b>" . count ($pointCollection) . "</b> lines in pointCollection.";
                        }
                    };


                //Create table results
                $createTable = "CREATE TABLE ". $prjCode ."_results (
                    Name        varchar(255),
                    Category    varchar(255),
                    Sub_Cat     varchar(255),
                    Rating      varchar(255),
                    Review      varchar(255),
                    Address     varchar(255),
                    State       varchar(255),
                    City        varchar(255),
                    Plus_Code   varchar(255),
                    Geo_Location varchar(255),
                    Website     varchar(255),
                    Phone       varchar(255),
                    Picture     varchar(255),
                    email       varchar(255)
                )";

                if ($conn->query($createTable) === TRUE) {
                    $reply = $reply . "Table <b>". $prjCode ."_results </b> created successfully <br>";
                } else {
                    $reply = $reply .  "Warning creating table: " . $conn->error . "<br>";
                };




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


        } else {
        // PRJ ALREADY EXIST ///////////////////////////////////////////////////////////
        $reply = "prjEXIST";
       };




echo $reply



?>