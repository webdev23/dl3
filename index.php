#!/usr/bin/php

<?php

/* dl3 | web content to mp3 
 * REQUIRED unix tools in env
 * php cat tac awk youtube-dl xclip hostname tail lsof kill touch midori
 * 
 * Can run 
 * - in webserver, with folder write access
 * - from command line 
 * - from pipe
 * 
 * nk2018 @ https://github.com/webdev23
 * */

//~ $local = system("hostname -I | awk '{print $1}'");
$local = "";

//~ $external = system("dig +short myip.opendns.com @resolver1.opendns.com");
$external = "";


@ob_end_clean();

@ob_start();

@header("Access-Control-Allow-Origin: *");


$mode = "hosted";

$adress = "";

$link = "";

if ($mode == "private") {
  
  $adress = $local;

}

if ($mode == "public") {
  
  $adress = $external;

}

if ($mode == "hosted") {
  
  $adress = "dl3.ponyhacks.com";

}

 //~ $adress = "0.0.0.0";

/* if started from commandline, wrap parameters to $_POST and $_GET */

if (!isset($_SERVER["HTTP_HOST"])) {

  @parse_str($argv[1], $_GET);

  @parse_str($argv[1], $_POST);
  
  if (!isset($argv[1])){
   
    ob_end_clean();

    $link = readline("Youtube url: ");
    
    if ($link == ""){ 
    
      $link = system("xclip -selection clipboard -o");
    
     }
    
    //~ system("xdg-open http://$local:9898/?url=$link");
    
    echo "\nServer started!\n";
    
    //~ system("nohup php -S $adress:9898 ytmp3 &");
    system("php -S $adress:9898 ytmp3 > /dev/null &");

    system("midori -e show-navigationbar=false -a http://$adress:9898/?url=$link");

    
    exit;
    
  }
   
}
 
if (isset($_SERVER["HTTP_HOST"])) {

   @$link = $_GET["url"];

 }
  
echo "
  <html><body>
    <style>body{background-color:black;color:chartreuse}
           a,a:visited{color:white}
           #prog{width:100%}
	   #link{margin:0 0 -37px 17px}
	   span {}
      </style>
    <pre>
<span id='stime'>".$_SERVER['REQUEST_TIME']."</span> | <span id='runtime'></span> | <span id='runpid'></span>
<span><a href='javascript:void(window.open(\"//$adress/?url=\"+location.href));'>Bookmarklet</a></span>
<textarea id='check' spellcheck='false' 
style='filter:invert(100%);color:chartreuse;width:100%;height:17%'>
    </textarea>
  <script>
  
var tload = performance.now()

 function setInt(){
  setInterval(function(){ xhr() }, 3000)
 }
   function xhr(){
  // check.scrollTop = check.scrollHeight
    const xhr = new XMLHttpRequest()
    xhr.onreadystatechange = function(event) {
     if (this.readyState === XMLHttpRequest.DONE) {
      if (this.status === 200) {
          check.innerHTML = ''
          check.innerHTML = this.responseText

    }}}
  xhr.open('GET','//$adress/?log=progress', true);
  xhr.send(null)
  }

</script>
";

/* ###################### */

if (@$_GET["log"] == "full") {
   
  @system("cat ytmp3_log");
 
 }
 
if (@$_GET["log"] == "time") {

  ob_end_clean();

  echo $_SERVER['REQUEST_TIME'];
  
  exit;
 
 }

if (@$_GET["log"] == "link") {

  @system("tail ytmp3_log | grep mp3");
 
 }
 
if (@$_GET["log"] == "tail") {

  ob_end_clean();

  @system("tail -s ' ' ytmp3_log\n");

  echo "<script></script>";

  echo "<br><br>";
 
  exit;

}
 
if (@$_GET["log"] == "progress") {

  ob_end_clean();

  @system("cat ytmp3_log | awk 'NR <=1'");

  echo "<script>setInt();check.scrollTop = check.scrollHeight</script>";

  echo "<br><br>";

   exit;
}
 
if (@$_GET["log"] == "job") {
 
   ob_end_clean();
   
   $hasPID = system("lsof . | grep avconv | cut -c 11- | php -r \"echo substr(trim(fgets(STDIN)), 0, -44);\"");
   
   echo "<script>console.log('$hasPID')</script>";
   
   exit;
   
} 
 
if (@$_GET["log"] == "lsof") {
 
   ob_end_clean();
   
   echo "<pre>";
   
   system("lsof .");
   
   exit;
   
}
  
if (@$_GET["log"] == "pid") {
 
   ob_end_clean();
   
   $conv = system(" lsof . | grep avconv | cut -c 11- | php -r \"echo substr(trim(fgets(STDIN)), 0, -44);\"");
   
   $yton = system(" lsof . | grep youtube-d | cut -c 11- | php -r \"echo substr(trim(fgets(STDIN)), 0, -44);\"");
   
   $pyton = system(" lsof . | grep python | cut -c 11- | php -r \"echo substr(trim(fgets(STDIN)), 0, -44);\"");
   
   if (empty($conv) && empty($yt) && empty($pyton)){
    
     echo "Ready";
    
    }
   
   exit;
   
}  
 
if (@$_GET["log"] == "tac") {
 
   ob_end_clean();

   system("tac ytmp3_log | head -n 1; lsof .;");
   
   exit;
   
} 
 
if (@$_GET["id"] != "") {
   
   $ytsid = $_GET["id"];

   echo "<script>startDl('$ytsid');xhr()</script>";
 
}
 
if (@$_GET["get"] != null) {

   $me = $_GET["get"];

   $getmp3 = @system("cat ytmp3_log | grep $me | grep ffmpeg | cut -c 23-");
 
   $hasPID = system(" lsof . | grep avconv | cut -c 11- | php -r \"echo substr(trim(fgets(STDIN)), 0, -44);\"");
 
   $mp3 = "$getmp3";
   
   exist:
   
  if(file_exists($mp3)) {
   
    if(!file_exists("$me.'lock'")) {
    
      if (system("kill -0 $hasPID") == "") {
     
	header('Content-Type: audio/mpeg');

	header('Content-Disposition: attachment; filename="'.$getmp3.'"');

	header('Content-length: '. filesize($mp3));

	header('Cache-Control: no-cache');

	header('Content-Transfer-Encoding: chunked'); 

	readfile($mp3);
	
	exit;
     
       }
      
     } else  {
     
	 goto exist;
       
	}
     
    }
   
}

function job192(){
 
  @$link = $_GET["url"];

  $bads = ["<",">","'"];

  str_replace($bads, "", $link); 

  if ($_GET["list"] != ""){
   
   $link = $_GET["list"];

   }

  echo $link;

  system("touch $ytid.lock");

  @system("youtube-dl -t --extract-audio --audio-format mp3 --audio-quality 160K $link >> ytmp3_log &");

  @unlink("$ytid.lock");
 
}

if (isset($_GET["url"])){
 
   $ytid = $_GET["url"];
   
   $bads = ["<",">","'"];

   str_replace($bads, "", $ytid); 

   $parts = parse_url($ytid);
   
   parse_str($parts['query'], $query);
   
   $ytid = $query['v'];
  
   if ($ytid == ""){
    
      $bads = ["<",">","'"];

      $nope = str_replace($bads, "", $ytid); 
    
     $ytid = $nope;
    
   }
  
   job192();
  
   $locked = '$ytid.lock';
 
retry:
 
   if (file_exists($locked)) {
  
     sleep(1);
 
     goto retry;
       
    } else {
	
        echo "
  </progress>
    <div id='pbrog' style='width: 1%;
     height:8px;background:#4CAF50;'></div>
      <span id='dstatus'>Download begin!</span>
    ";
    
	echo "
    <a id='link' href='//$adress/?get=".$ytid."'>".$ytid."</a>";
    
    
        echo "
    <script>
    
      function randomColor() {
      var color = Math.floor(0x1000000 * Math.random()).toString(16);
      return '#' + ('000000' + color).slice(-6);
      }
    
      function move() {
	var elem = document.getElementById('pbrog');   
	var width = 1;
	var id = setInterval(frame, 180);
	function frame() {
	  if (width >= 100) {
	    clearInterval(id);
	    dstatus.innerHTML = 'Conversion to mp3. Hold on!'
	    pbrog.style.background = 'blue'
	    move()
	  } else {
	    width++
	    pbrog.style.background = randomColor()
	    elem.style.width = width + '%'
	  }
	}
      }
     
      move()
  
      function stats(){
       const xhr = new XMLHttpRequest()
       xhr.onreadystatechange = function(event) {
	if (this.readyState === XMLHttpRequest.DONE) {
	 if (this.status === 200) {
	     substring = 'youtube-dl';
	     check.innerHTML = ''
	     check.innerHTML = this.responseText
             var vload = runtime.innerText - stime.innerText
	     console.log(vload)
	     //~ alert(vload)
	     var pid = runpid.innerText
	     console.log(pid)
	    if (vload >= '50' && pid === 'Ready' && !check.innerHTML.includes('avconv') && !check.innerHTML.includes('youtube-d') && !check.innerHTML.includes('python') && check.contentText != ''){
	    //~ if (runpid.innerText == 'Ready'){
	      
		
		dstatus.innerHTML = 'Download ready!'
		
	        var url = new URL(document.location);

		if (url.searchParams.get('list')){
		
		shot()
		
	        }
	       }
	       
	       if (runpid.innerText === 'Ready'){
	      	oneShot()
		 }
	       
	       
	       
	  }}}
	xhr.open('GET','//$adress/?log=tac', true);
	xhr.send(null)
       setTimeout(stats, 1000);
       }
       
       stats()
     </script>
    ";
    
          echo "
    <script> 
    function runTime(){
       const xhr = new XMLHttpRequest()
       xhr.onreadystatechange = function(event) {
	if (this.readyState === XMLHttpRequest.DONE) {
	 if (this.status === 200) {
	     runtime.innerHTML = this.responseText
	  }}}
	xhr.open('GET','//$adress/?log=time', true);
	xhr.send(null)
       setTimeout(runTime, 500);
       }
       
       runTime()
       
    function runPID(){
       const xhr = new XMLHttpRequest()
       xhr.onreadystatechange = function(event) {
	if (this.readyState === XMLHttpRequest.DONE) {
	 if (this.status === 200) {
	     runpid.innerHTML = this.responseText
	  }}}
	xhr.open('GET','//$adress/?log=pid', true);
	xhr.send(null)
       setTimeout(runPID, 500);
       }
       
       runPID()       
    
    function shot(){
               link.setAttribute('target','blank')
    	       link.click()

    }
    
     oneShot = (function() {
	   var executed = false;
	   return function() {
	     if (!executed) {
	       executed = true;
	       
	       link.click()
			
	      var highestTimeoutId = setTimeout(';');
	      for (var i = 0 ; i < highestTimeoutId ; i++) {
		  clearTimeout(i); 
	       }
	     }
	   }
	 })()
       </script>
         ";
 
     }
     
}

if (@$_GET["log"] == "list") {
 
   ob_end_clean();
   
   $rows = scandir('.', 1);
   
   echo "<table id='lst'>";
   
   foreach ($rows as $row) {
    
      echo "<tr>";
      
      $ytid = explode(".",substr($row, -15));
   
      $ytid = $ytid[0];
   
      $extension = pathinfo($row, PATHINFO_EXTENSION);
   
      if ($extension == 'mp3') {
   
	 echo "<td><a href=?get=".$ytid.">".$row."</a> ".$ytid."</td>";
      
	 echo "</tr>";
   
	}
	
     }    
   
   echo "</table>";
      
   exit;
   
}


/* ####################### */ 

$rows = scandir('.', 1);

echo "<table id='lst'>";

foreach ($rows as $row) {
 
   echo "<tr>";
   
   $ytid = explode(".",substr($row, -15));

   $ytid = $ytid[0];

   $extension = pathinfo($row, PATHINFO_EXTENSION);

   if ($extension == 'mp3') {

      echo "<td><a href=?get=".$ytid.">".$row."</a> ".$ytid."</td>";
   
      echo "</tr>";

     }
     
  }    

echo "</table>";
 
echo "
<script>

    function list(){
       const xhr = new XMLHttpRequest()
       xhr.onreadystatechange = function(event) {
	if (this.readyState === XMLHttpRequest.DONE) {
	 if (this.status === 200) {
	     lst.innerHTML = this.responseText
	  }}}
	xhr.open('GET','//$adress/?log=list', true);
	xhr.send(null)
       setTimeout(list, 5000);
       }
       
       list()

</script>";


