<?php
$error = "Sorry, we have encountered some technical issues.\r\nError code: ";
if(!empty($_POST)){
	require_once("config.php");

	$chatAuthor = $_POST["chatAuthor"];
	$chatContent = trim($_POST["chatContent"]);

	if(empty($chatAuthor)||$chatAuthor=="")
		die('{"error":'.json_encode($error."#chatSend002 :\r\nCan`t add new message. Author name is missing.").'}');
    else if(empty($chatContent)||$chatContent=="")
    	die('{"error":'.json_encode($error."#chatSend003 :\r\nCan`t add new message. Message content is missing.").'}');
	
	$chatAuthor = substr($chatAuthor,1,-1);
	$chatContent = substr($chatContent,1,-1);

	if(strlen(urldecode($chatContent)) > $GLOBALS["CHAT_MAXLENGTH_MSG"])
		die('{"error":'.json_encode($error."#chatSend004 :\r\nCan`t add new message. Message content is too long.").'}');
	else if(strlen(urldecode($chatAuthor)) > $GLOBALS["CHAT_MAXLENGTH_AUTHOR"])
		die('{"error":'.json_encode($error."#chatSend005 :\r\nCan`t add new message. Author name is too long.").'}');
	else{

		$lines = array();
		$oldDate = 0;

		if(file_exists($GLOBALS["CHAT_FILE_NAME"])){
	    	$lines = file($GLOBALS["CHAT_FILE_NAME"]);
	    	if($lines){
				$oldDate = explode(" ", $lines[count($lines)-1]);
				$oldDate = $oldDate[0];
			} else if(!$lines&&is_array($lines))
	    		$lines = array();
	    	else 
				die('{"error":'.json_encode($error."#chatSend003 :\r\nFailed to open chat file.").'}');
	    } 
		
		do $newDate = microtime(get_as_float);
		while($oldDate>$newDate);

    	$semafor = sem_get(234567, 1);
		if(sem_acquire($semafor)){
			
	    	if($linesLen = count($lines)){
	    		$lines[count($lines)-1] = $lines[count($lines)-1]."\r\n";
	    		while($linesLen >= $GLOBALS["CHAT_MAXIMUM_MESSAGES"]){
	    			unset($lines[$linesLen-$GLOBALS["CHAT_MAXIMUM_MESSAGES"]]);
	    			$linesLen--;
	    		}
		    }
		    
		    array_push($lines,$newDate." ".$chatAuthor." ".$chatContent);

		    file_put_contents($GLOBALS["CHAT_FILE_NAME"], $lines);

			sem_release($semafor);

			echo '{"msgAdd":true}';
		}else
			die('{"error":'.json_encode($error."#chatSend006 :\r\nCan`t acquire the semaphore.").'}');		
	}
}else
	echo '{"error":'.json_encode($error."#chatSend001 :\r\nNo data was sended.").'}';
?>