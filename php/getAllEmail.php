<?php

set_time_limit(3600);

$servername = "localhost";
$username = "root";
$password = "";
$myDB = "map_y1";
$conn = mysqli_connect($servername, $username, $password, $myDB);

$prjCode =  $_POST['prjCode'];
$reply = '';
$emailResults = '';
$urlsArray = [];


$writeTable = "UPDATE ". $prjCode ."_results SET email = 'N/A' WHERE Website = 'N/A' AND email = '' ";
if ($conn->query($writeTable) === FALSE) {
echo "Error: " . $writeTable . "<br>" . $conn->error;
};

$iii = 1;
while (sizeof($urlsArray) == 0) {

    $sql = "SELECT Website FROM " . $prjCode ."_results WHERE Website != 'N/A' AND email = '' LIMIT ".$iii;
    $result = $conn->query($sql);

        while ($row = mysqli_fetch_array($result)){
            array_push($urlsArray, $row['Website']);  // IN ///////////////////////////////////////////////
        };

        $iii = $iii + 1;

}


    foreach ($urlsArray as $singleUrl) {

        $subUrlArray = array_unique(getAllURlsOnUrl($singleUrl)); //get sub-urls on the webpage and remove the duplicates
        sort($subUrlArray);

        //Reset Suburls Array
        unset($scrapedEmails);
        $scrapedEmails = array();

        //Extracting the emails from URLS that were crawled
        foreach ($subUrlArray as $key => $url) {
            $emails = get_emails_from_webpage($url);
            if ($emails != null) {
                foreach ($emails as $email) {
                    if (!in_array($email, $scrapedEmails)) {
                        array_push($scrapedEmails, $email);
                    } 
                }
            }
        }

        if (count($subUrlArray) == 0) {
            $emailsToWrite = $singleUrl . ';' . ' Website not crawlable due to security in-place on the website';
        } else
            $emailsToWrite = $singleUrl . ';' . (count($scrapedEmails) == 0 ? ' No Emails found in the first ' . count($subUrlArray) . ' sub-urls' : implode("; ", $scrapedEmails));
        

        $emailResults = $emailResults . count($scrapedEmails) == 0 ? 'N/A' : implode("; ", $scrapedEmails); // OUT ///////////////////////////////////////////////
        $reply = $reply . $singleUrl .': ' . $emailResults . '</br>';

        $writeTable = "UPDATE ". $prjCode .'_results SET email = "' . $emailResults . '" WHERE Website = "'. $singleUrl .'"';
        if ($conn->query($writeTable) === FALSE) {
        echo "Error: " . $writeTable . "<br>" . $conn->error;
        };
        
    }



    $sql = "SELECT Website FROM " . $prjCode ."_results WHERE email = '' ";
    $result = $conn->query($sql);
    $rowcountEmpty=mysqli_num_rows($result);

    $sql = "SELECT Website FROM " . $prjCode ."_results";
    $result = $conn->query($sql);
    $rowcount=mysqli_num_rows($result);
    $webDone = $rowcount - $rowcountEmpty;

    if ($rowcountEmpty == 0) {
        $reply = 'done';
    } else {
         $reply = '</br></br>' ."Total number of website: ". $rowcount . "   Number of website done: " . $webDone . '</br></br>' . $reply;
    };

    echo $reply;



    //Fetch All urls on the web page
    function getAllURlsOnUrl($strUrl)
    {
        //Crawling the inputted domain to get the URLS
        $urlContent = file_get_contents(urldecode($strUrl));

        //Reset subUrls Array
        unset($subUrlArrayList);
        $subUrlArrayList = array();


        if ($urlContent == '') {
            return $subUrlArrayList;
        }

        $dom = new DOMDocument();
        @$dom->loadHTML($urlContent);
        $xpath = new DOMXPath($dom);
        $hrefs = $xpath->evaluate("/html/body//a");

        array_push($subUrlArrayList, urldecode($strUrl));

        $maxNumberOfUrls = 20;
        $numberOfSubUrls = $hrefs->length > $maxNumberOfUrls ? $maxNumberOfUrls : $hrefs->length;

        for ($i = 0; $i < $numberOfSubUrls; $i++) {
            $href = $hrefs->item($i);
            $url = $href->getAttribute('href');
            $url = filter_var($url, FILTER_SANITIZE_URL);

            // validate url
            if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
                if (strpos($url, 'blog.') !== false || strpos($url, '/blog') !== false || strpos($url, 'mailto') !== false) {
                    continue;
                } else
                if (strpos($url, $strUrl) !== false) {
                    array_push($subUrlArrayList, $url);
                }
            }
        }
        return $subUrlArrayList;
    }

    //Regular expression function that scans individual pages for emails
    // questa parte deve essere migliorata nel caso di blocco. <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    function get_emails_from_webpage($url)
    {
        $text = @file_get_contents($url);

        $res = preg_match_all("/(?!\S*\.(?:jpg|png|gif|bmp|jpeg)(?:[\s\n\r]|$))[A-Z0-9._%+-]+@[A-Z0-9.-]{3,65}\.(?!jpg|png|gif|bmp|jpeg)[A-Z]{2,4}/i", $text, $matches);

        $txtt = "----------------------------\r\n" . $url . "\r\n" . $res . "\r\n";
        writeUrlContentToFile("../Result/email.txt", $txtt);

        if ($res) {
            return array_unique($matches[0]);
        } else {
            return null;
        }
    }

    function writeUrlContentToFile($ResultPath, $contentToWrite)
    {
        $destinationFile = fopen($ResultPath, "a") or die("Unable to open file!");
        fwrite($destinationFile, $contentToWrite . "\r\n");
        fclose($destinationFile);
    }


    ?>
