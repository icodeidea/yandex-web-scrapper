<!Doctype html>
<html style="background: black">
    <head>
        <title>map yandex 1</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"type="text/javascript"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

        <!-- LOAD THE DATA INPUT FILE -->
        <script type="text/javascript" src="Source\List.js"></script>
        <script type="text/javascript" src="Source\CityData.js"></script>

    </head>
<body> 
    <div class= "jumbotron text-center" style="background: black; margin-bottom: 0.5px; ">
        <h1>MAP YANDEX 1</h1>
        <p>New projects will be based on the data saved at: <b>map3\Source\List.js</b></p>
        <button class="btn btn-info" onClick ="checkData ()">Check Data </button>
    </div>
        <div class="container-fluid"  style="background: black; ">
                <div class="row">
                    <div class="col-sm-4" style="width:80%"> 
                            <div class="form-group" style="width:400px">
                                </br>
                                <label>Enter Project Code:</label>
                                <input type="text" class="form-control" placeholder = "Enter prj Code - do not use space." name="prjCode" id="prjCode">
                            </div>

                            <table style="width:400px">
                            <tr>
                                <td>
                                    <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked" checked="">
                                    <label class="form-check-label" for="flexSwitchCheckChecked">Sprial search</label>
                                </td>
                                <td>
                                    <input type="text" class="form-control" placeholder = "Page per Point: 10" name="pageXpoint" id="pageXpoint">
                                </td>
                            </tr>
                            </table></br></br>
                            


                            <div class="form-group" style="display:flex;">
                                <button class="btn btn-info" onClick ="startPrj ()">Find Results </button> &nbsp;&nbsp;
                                <button class="btn btn-info" onClick ="getAllEmail ()">Find email </button> &nbsp;&nbsp;
                                <button class="btn btn-danger" onClick ="deletePRJ ()">Delete Project </button> &nbsp;&nbsp;
                            </div></br></br>


                            <div id="mytbody"></div>
                            <div id="mytbody2"></div>
                            <div id="mytbody3"></div>
                            <div style = "position: fixed; bottom: 0px; left: 0px; background: black" id="mytbody4"></div>
                    </div>

                    <div style="position: fixed; top: 0px; right: 0px; " >
                        <img src = "images\download.gif" id="loader1" style="width: 200px; height: 150px; display: none;">
                    </div>

                    

                </div>
        </div>

    <script>
        document.body.style.color = "white"
        initialize ()


        // 0 - leggi o crea DB: "map3" || 1 - Leggi o crea prj_Code_List  || 2 - Visualizza Prj List
        function initialize () {
        $.ajax({
                type: "POST",
                url: "php/readCreateDB.php" ,
                data: {},
                cache: false,
                success: function(reply) { 
                    $('#mytbody').html(reply);
                }
            });
        }

        // 3 - leggi prj_Code e controlla se esiste, se no    4 - salva prjCode, crea tabelle prj (punti e risultati), salva i punti
        var prjCode = ""
        var pointCollection =[]
        var IDtoCheck = []
        var pointDone = 0
        var pointNotDone = 0
        var checkPageDone = 0
        

        function startPrj () {
            prjCode = $('#prjCode').val().trim();
            pointCollection =[]

            // spiralPoint
            $('#flexSwitchCheckChecked').val().trim() == 'on' ? spiralPoint = 1 : spiralPoint = 0
            // PagePerPoint
            PagePerPoint = $('#pageXpoint').val().trim();
            if (PagePerPoint.length == 0) {PagePerPoint = 10}

            if(prjCode.length == 0){
                alert("Please enter Project Code");
            } else {
                $("#loader1").css("display", "block");  //loader1 start
                genPoint (spiralPoint, PagePerPoint)
                
                $.ajax({
                    type: "POST",
                    url: "php/checkPrjCode.php" ,
                    data: {'prjCode': prjCode, 'pointCollection': pointCollection},
                    cache: false,
                    success: function(reply) { 

                        if (reply != "prjEXIST") {
                            $('#mytbody').html(reply);
                        }

                       readIDtoCheck ()
                        
                    }
                });
            }
        }


        
        function genPoint (spiralPointS, PagePerPointS) {

            for (var City of Object.keys(List)) {
                for (var [Cat, Pag] of Object.entries(List [City])){
                    if (spiralPointS == 0) {
                        pointCollection.push ([City, Cat, Pag])
                    } else {
                        getDataPoints (City, Cat, Pag, PagePerPointS)
                    }
                }
            }
        }

        // Generate the spiral point
        function getDataPoints (CityP, CatP, PagT, PagePerPointSS){
            // PagP e' il numero di punti
            // PagT e' il numero di pagine

            PagP = Math.ceil (PagT / PagePerPointSS);
            pointCollection.push ([CityP, CatP, PagePerPoint])


            //1 - read Lat, Long, Area
                var latP = CityData [CityP].lat
                var longP = CityData [CityP].long
                var areaP = CityData [CityP].Area
            //2 - Calculate "dP"
                var dP = Math.min((areaP*10^6/PagP)^0.5, 1000)
            //3 - Calculate "bP"
                var bP = dP/(2*Math.PI)
            //4 - Assigned xP and yP
                var xP = longP
                var yP = latP
            //5 - for cycle
            for (var i=1; i < PagP+1 ; i++) {
                var Point = xP + ', ' + yP
                    pointCollection.push ([Point, CatP, PagePerPoint])       // points are saved here
                //5.1 - Calculate t, r, DxP, DyP, e new xP and yP
                    // t
                        if (i == 1) {
                            var tP = 4.49
                            var rP = 0
                        } else {
                            tP = tP + dP / rP
                        }
                    // r
                        rP = bP * tP
                    // DxP DyP xP yP
                        DxP = rP * Math.cos(tP) / 100000
                        DyP = rP * Math.sin(tP) / 100000
                        xP = longP + DxP
                        yP = latP + DyP
            }
        }

        // 5 - read ID with checked = 0
        function readIDtoCheck () {
            $.ajax({
                    type: "POST",
                    url: "php/readIDtoCheck.php" ,
                    data: {'prjCode': prjCode},
                    cache: false,
                    success: function(reply) {

                        reply=reply.split('/');
                        for (let i = 0; i < reply.length; i++){
                            reply [i]= reply [i].split("^'")
                        };

                        replyNum = reply.pop();

                        var textC = "</br></br><b> Cheking status </b></br>"
                        if (replyNum <= "0") {
                            $('#mytbody2').html(textC + "There are no other point to be checked for the project: <b>" + prjCode + "</b>");
                            $("#loader1").css("display", "none"); //loader1 end
                        } else {
                            
                            $('#mytbody2').html(textC + "Checking the points for the project: <b>" + prjCode + "</b></br>Total number of pages to be checked: " + replyNum );

                            startGetData (reply)


                        }
                    
                    }
                });
        }

        var activeID = []
        var maxActiveID = 10  // PARAMETER: Maximum number of contemporaneous runs //////////////////////////////
        var checkIndex = 0
        var numStop = 1
        

        function startGetData (pointDB) {
            // pointDB[i] : ID, City, cat, pageTotal, pageCount,
            let i =0
            let ia =0
            while (i < pointDB.length && ia < maxActiveID){
                    activeID.push (pointDB[i][0])
                    //console.log (pointDB[i][3] + " - " + pointDB[i][4])
                if(parseFloat(pointDB[i][3]) > parseFloat(pointDB[i][4])) {
                    ia +=1
                   getData (pointDB, i)
                }
                i +=1
            };
        };


        function getData(pointDB, i){         
                $.ajax({
                    type: "POST",
                    url: "php/map_y1.php", //dentro map_y1.php aumento di 1 pageCount >> pageCount = pageCount + 1
                    data: {'prjCode': prjCode, 'ID':pointDB[i][0], 'City':pointDB[i][1], 'cat':pointDB[i][2], 'pageTotal':pointDB[i][3], 'pageCount':pointDB[i][4]},
                    cache: false,
                    success: function(reply){

                        html = ''
                        totPage = 0
                        donePage = 0
                        pointDBiNEW=reply.split("^?");

                        let html0 = '';
                        let html2 = '';
                        let html3 = '';


                            html0 += '</br></br><table style="width:100%">'
                            html0 += '<tr>'
                            html0 +=    '<td><b>ID</b></td>'
                            html0 +=    '<td><b>City</b></td>'
                            html0 +=    '<td><b>Category</b></td>'
                            html0 +=    '<td><b>Page Total</b></td>'
                            html0 +=    '<td><b>Page Done</b></td>'
                            html0 +=    '<td><b>Status</b></td>'
                            html0 += '</tr>'


                        for (let ii = 0; ii < pointDB.length; ii++){
                            if (pointDB[ii][0]==pointDBiNEW[0]){
                                pointDB[ii][4]=pointDBiNEW[4];
                                i=ii;
                            };

                            if (parseFloat(pointDB[ii][3])>parseFloat(pointDB[ii][4])) { status= 'on going' } else { status= '<b>COMPLETED</b>' }
                            
                            html2 += '<tr>'
                            html2 +=    '<td>' + pointDB[ii][0] + '</td>'
                            html2 +=    '<td>' + pointDB[ii][1] + '</td>'
                            html2 +=    '<td>' + pointDB[ii][2] + '</td>'
                            html2 +=    '<td>' + pointDB[ii][3] + '</td>'
                            html2 +=    '<td>' + pointDB[ii][4] + '</td>'
                            html2 +=    '<td>' + status + '</td>'
                            html2 += '</tr>'
                            totPage = totPage + parseFloat(pointDB[ii][3])
                            donePage = donePage + parseFloat(pointDB[ii][4])
                        };

                        if(totPage>donePage){ status='on going' } else {status='<b>COMPLETED</b>'}
                            html3 += '<tr>'
                            html3 +=    '<td><b>TOTAL</b></td>'
                            html3 +=    '<td><b> - </b></td>'
                            html3 +=    '<td><b> - </b></td>'
                            html3 +=    '<td><b>' + totPage + '</b></td>'
                            html3 +=    '<td><b>' + donePage + '</b></td>'
                            html3 +=    '<td><b>' + status + '</b></td>'
                            html3 += '</tr>'

                        html = html0 + html3 + html2 + html3 + '</table></br></br>'


                        html += "Page done: " + pointDBiNEW
                        //console.log ("Page done: " + pointDBiNEW)


                        $('#mytbody3').html(html);
                        $('#mytbody4').html("</br><b>Page done: " + pointDBiNEW + "</b></br>");
                        if(totPage==donePage){$("#loader1").css("display", "none")} //loader1 end

                        
                        if (checkPageDone == donePage) {
                           checkIndex += 1    // PARAMETER: if 1 block run   if 0 never block run
                        } else {
                            checkIndex = 0
                        }
                            
                            if(checkIndex > 10) { // PARAMETER: Number of time with no result //////////////////////////////
                                timeout = numStop > 5 ? 2*60*60*1000 : 30*60*1000 // PARAMETER: Waiting time in case of no results millisecond /////////////

                                $('#mytbody4').html("</br><b> >>>   RUN IN PAUSE FOR: " + timeout/60000 + " min   ||   STOP status: " + numStop + "   <<< </b></br>")
                                
                                var data = new Date();
                                var Hh, Mm, Ss, mm;
                                Hh = data.getHours() + ":";
                                Mm = data.getMinutes() + ":";
                                Ss = data.getSeconds() + ":";
                                mm = data.getMilliseconds() + ":";
                                
                                console.log( "Stop at: " + Hh + Mm + Ss + mm + "  on tot Pages: " + donePage + " || timeout: " + timeout/60000 + " min   ||   STOP status: " + numStop )
                                
                                setTimeout(function() {

                                    if(parseFloat(pointDB[i][3]) > parseFloat(pointDB[i][4])) {
                                        getData (pointDB, i)
                                    } else if (pointDB.length > activeID.length) {
                                        getData (pointDB, activeID.length)
                                        activeID.push (activeID.length)
                                    }

                                    checkIndex = 0
                                    numStop > 5 ? numStop = 0 : numStop +=1

                                    $('#mytbody4').html("</br> RUN RE-STARTED </br>")

                                }, timeout);
                                

                            } else {

                                    if(parseFloat(pointDB[i][3]) > parseFloat(pointDB[i][4])) {
                                        getData (pointDB, i)
                                    } else if (pointDB.length > activeID.length) {
                                        getData (pointDB, activeID.length)
                                        activeID.push (activeID.length)
                                    }
                                    
                            }

                        checkPageDone = donePage



                    }
                })
            }



        // DELETE PRJ  
        function deletePRJ () {
            
            prjCode = $('#prjCode').val().trim();
            if(prjCode.length == 0){
                alert ("Please enter the project code to delete")
            } else {
                $("#loader1").css("display", "block");  //loader1 start
                if(confirm ("Are you sure to delete the project: " + prjCode + " ?")) {
                    $.ajax({
                        type: "POST",
                        url: "php/deletePrj.php" ,
                        data: {'prjCode': prjCode},
                        cache: false,
                        success: function(reply) { 
                        
                            initialize ()
                            $('#mytbody3').html(reply);
                            $("#loader1").css("display", "none"); //loader1 end

                            }
                    });
                }
            }
        }

        // CHECK DATA 
        function checkData (){
            //console.log (List)

            html = '</br></br><b>"LIST" currently loaded </b></br>'

            html += JSON.stringify(List)

            $('#mytbody3').html(html)           

        }


        function getAllEmail (){
            prjCode = $('#prjCode').val().trim();
            if(prjCode.length == 0){
                alert ("Please enter the project code to delete")
            } else {

            $("#loader1").css("display", "block");  //loader1 start
            $.ajax({
                    type: "POST",
                    url: "php/getAllEmail.php" ,
                    data: {'prjCode': prjCode},
                    cache: false,
                    success: function(reply) {

                        if (reply == 'done') {
                            $('#mytbody3').html("All website has been checked.");
                            $("#loader1").css("display", "none"); //loader1 end
                        } else {
                            $('#mytbody3').html(reply);
                            getAllEmail ()
                        }
                    
                    }
                });
            }
        }



    </script>
</body>
</html>