<?php
$startDate = time();
$error = "Sorry, we have encountered some technical issues.\r\nError code: ";

if(!empty($_POST)){
	$webDate = floatval($_POST["chatDate"]);
	if(!empty($webDate) && $webDate>0){
		include_once("config.php");

		if($GLOBALS["CHAT_TIME_TO_REFRESH"] > 13)
			$refreshTime = $GLOBALS["CHAT_TIME_TO_REFRESH"];
		else if($GLOBALS["CHAT_TIME_TO_REFRESH"] < 1.5)
			$refreshTime = $GLOBALS["CHAT_TIME_TO_REFRESH"]*18;
		else
			$refreshTime = 26 - $GLOBALS["CHAT_TIME_TO_REFRESH"];

		checkChat();
	}else
		die('{"error":'.json_encode($error."#chatRead002 :\r\nNo valid datetime was sended.").'}');
}else
	die('{"error":'.json_encode($error."#chatRead001 :\r\nNo data was sended.").'}');


function checkChat(){
	global $startDate, $webDate, $refreshTime;
	if(time() - $startDate < $refreshTime){
		if(file_exists($GLOBALS["CHAT_FILE_NAME"])){
	    	$lines = file($GLOBALS["CHAT_FILE_NAME"]);
	    	if($lines){
				$tmp = explode(" ", $lines[count($lines)-1]);
				if($webDate < $tmp[0]){
					$resContent = "";
					for($i=0;$i < count($lines);$i++){
						$tmpLine = explode(" ", $lines[$i]);
						if($webDate < floatval($tmpLine[0])){
					    	$resContent .= $tmpLine[1].$GLOBALS["CHAT_SEPARATOR"].$tmpLine[2];
					    	$webDate = $tmpLine[0];
					    }
					}
					echo '{"content":'.json_encode($resContent).',"date":'.$webDate.'}';
				} else
					sleepAMoment();
			} else if(!$lines&&is_array($lines)){
				if($webDate == 1)
					echo '{"content":"","date":2}';
				else
	    			sleepAMoment();
	    	} else
				die('{"error":'.json_encode($error."#chatRead003 :\r\nFailed to open chat file.").'}');
	    } else if($webDate == 1)
			echo '{"content":"","date":2}';
		else
    		sleepAMoment();
	} else 
		echo '{"noMsg":true}';
}

function sleepAMoment(){
	sleep($GLOBALS["CHAT_TIME_TO_REFRESH"]);
	checkChat();
}
?>