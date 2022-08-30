<?php
error_reporting(E_ERROR | E_PARSE);
    //**************DO NOT CHANGE ANYTHING HERE*********************************************************//
            $ipContent = file_get_contents('https://ipstack.com/');                                     //
            $dom = new DOMDocument();                                                                   //
            @$dom->loadHTML($ipContent);                                                                //
            $xpath = new DOMXPath($dom);                                                                //
            $IPdata = $xpath->query('//input[@name="client_ip"]/@value');                               //
            $ip = $IPdata[0]->nodeValue;                                                                //
            $new_arr[]= unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$ip));      //
            $lng = $new_arr[0]['geoplugin_longitude'];                                                  //
            $lat = $new_arr[0]['geoplugin_latitude'];                                                   //
    //**************************************************************************************************//
    

    // prjCode, ID, City, cat, pageTotal, pageCount,
    $prjCodeA = $_POST['prjCode'];
    $pointDBA = [$_POST['ID'], $_POST['City'], $_POST['cat'], $_POST['pageTotal'], $_POST['pageCount'] ];

    $locationA =  $pointDBA[1];
    $palaceA = $pointDBA[2];
    $pageCountA = $pointDBA[4];

    

    $dataArrA = array();


    getData($pageCountA, $dataArrA, $locationA, $palaceA, $prjCodeA, $pointDBA);



    function getData($pageCount, $dataArr, $location, $palace, $prjCode, $pointDB){
        global $lng, $lat;
        $itemCount = $pageCount * 20 ;

          $geoPoint = 0;
        if(strpos(json_encode($location), ",") !== false){
          $geoPoint = 1;
        } 


          $url = 'https://search-maps.yandex.ru/v1/';
          $url = $url. '?apikey=939c6dc1-0748-4960-b581-4525d7838f84';
          $url = $url. '&text='.$palace;
        if ($geoPoint == 0) {
          $url = $url. ' ' .$location;
        }
          $url = $url. '&type=biz';
          $url = $url. '&lang=en_US';
        if ($geoPoint == 1) {
          $url = $url. '&ll='.$location;
          $url = $url. '&spn=0.02, 0.02';  // PARAMETER : size of the search area (0.01 = 1.11 km)
        }
          //$url = $url. '&bbox=<search area coordinates>]';
          $url = $url. '&rspn=1';  // PARAMETER : 1 seach only inside, 0 seach also outside
          $url = $url. '&results=500';
          $url = $url. '&skip=0';
          //$url = $url. '&callback=<function name>]';




        curl_setopt_array($curl, array(
          CURLOPT_URL => $url,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.159 Safari/537.36',
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        $responseOriginal = $response;
        $txtt = "////////////////////////////// \r\n" . $url . "\r\n" .  $response . "\r\n  ////////////////////////////// ";
        writeUrlContentToFile("../Result/Result.txt", $txtt);


        //************** END OF CURL FROM GOOGLE MAP HERE  *************************************************//
      
        $address = '';
        $website = 'N/A';
        $latlong = '';
        $review = '';
        $name = '';
        $phone = 'N/A';
        $photo = '';
        $pincode = '';
        $state = '';
        $city = '';
        $rating = '';
        $category = ucfirst($palace);
        $subcategory = '';

        foreach($data as $d){






            array_push($dataArr, array("TotalData" => $totalData , "Name" => $name, "Category" =>$category, "SubCategory" => $subcategory, "Address" => $address, "State" => $state, "City" =>$city, "PinCode" => $pincode, "Website" =>$website, "Phone" =>$phone, "GeoLocation" =>$latlong, "Picture" => $photo, "Review" => $review, "Rating" =>$rating));

            // Save result in DB /////////////////////////////////////////////////////////////////
                $servername = "localhost";
                $username = "root";
                $password = "";
                $myDB = "map_y1";
                $conn = mysqli_connect($servername, $username, $password, $myDB);


                $name = $conn -> real_escape_string($name);
                $category = $conn -> real_escape_string($category);
                $subcategory = $conn -> real_escape_string($subcategory);
                $rating = $conn -> real_escape_string($rating);
                $review = $conn -> real_escape_string($review);
                $address = $conn -> real_escape_string($address);
                $state = $conn -> real_escape_string($state);
                $city = $conn -> real_escape_string($city);
                $pincode = $conn -> real_escape_string($pincode);
                $latlong = $conn -> real_escape_string($latlong);
//                $website = $conn -> real_escape_string($website);
                $phone = $conn -> real_escape_string($phone);
//                $photo = $conn -> real_escape_string($photo);

                $writeTable = "INSERT INTO ". $prjCode ."_results (Name, Category, Sub_Cat, Rating, Review, Address, State, City, Plus_Code, Geo_Location, Website, Phone, Picture, email)
                              VALUES ('$name', '$category', '$subcategory', '$rating', '$review', '$address', '$state', '$city', '$pincode', '$latlong', '$website', '$phone', '$photo', '')";
                if ($conn->query($writeTable) === FALSE) {
                echo "Error: " . $writeTable . "<br>" . $conn->error;
                };
        
          }
        
        

            // Update checked value
            $servername = "localhost";
            $username = "root";
            $password = "";
            $myDB = "map_y1";
            $conn = mysqli_connect($servername, $username, $password, $myDB);

              // CONTROLLO SUL RISULTATO ////////////////////////////////////////
              // PARAMETER: se $check=1 si attiva il loop sulla stessa pagina //////////////

              $txtt = "////////////////////////////// \r\n" . $url . "\r\n" . json_encode($dataArr) . "\r\n  //////////////////////////////  ";
              writeUrlContentToFile("../Result/ResultCleaned.txt", $txtt);

                $check = 0;

                if ($check == 1) {

                    //Check if protection CAPTCHA is active
                    $captchaActive = 0;
                    if(strpos( json_encode($responseOriginal), "captcha") !== false){
                      $captchaActive = 1;
                    } 

                  if (sizeof($dataArr) > 0  || $captchaActive == 0) {
                    $pageCount = $pageCount + 1;
                  }

                } else {
                  $pageCount = $pageCount + 1;
                }
              ///////////////////////////////////////////////////////////////////

              $sql = "UPDATE " . $prjCode . "_pointcollection SET pageCount = " . $pageCount .  " WHERE ID = " . $pointDB[0];
              if ($conn->query($sql) === FALSE) {
                echo  "Error updating record: " . $conn->error;
              };

              $reply = $reply . $pointDB[0] ."^?". $pointDB[1] ."^?". $pointDB[2] ."^?". $pointDB[3] ."^?". $pageCount;

            echo $reply;

    }


	function startsWith( $haystack, $needle ) {
		 $length = strlen( $needle );
		 return substr( $haystack, 0, $length ) === $needle;
	}




  function writeUrlContentToFile($ResultPath, $contentToWrite)
  {
      $destinationFile = fopen($ResultPath, "a") or die("Unable to open file!");
      fwrite($destinationFile, $contentToWrite . "\r\n");
      fclose($destinationFile);
  }
  
?>