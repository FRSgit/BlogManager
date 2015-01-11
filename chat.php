<?php
$error = "Sorry, we have encountered some technical issues.\r\nError code: ";
if(!empty($_POST)){
	if(!empty($_POST["chatDate"])){
		require_once("config.php");
		$webDate = $_POST["chatDate"];
		$newDate = $webDate;
		$addMsg = false;
		
		$chatIntDate = explode(".",$webDate);
		$chatIntDate = intval($chatIntDate[0]);
		$resContent = "";
	    $lines = array();

		
		if(file_exists($CHAT_FILE_NAME)&&intval(filemtime($CHAT_FILE_NAME))>=$chatIntDate){
	    	$lines = file($CHAT_FILE_NAME) or die('{"error":'.json_encode($error."#chat003 :\r\nFailed to open chat file.").'}');
			for($i=0;$i<count($lines);$i++){
				$tmpLine = explode(" ",$lines[$i]);
				if(floatval($webDate)<floatval($tmpLine[0])){
			    	$resContent .= $tmpLine[1].": ".$tmpLine[2];
			    	if(floatval($newDate)<floatval($tmpLine[0]))
			    		$newDate = $tmpLine[0];
			    }
			}

	    }
	    $chatAuthor = $_POST["chatAuthor"];
		$chatContent = $_POST["chatContent"];
	    if((!empty($chatContent)&&$chatContent!="")||(!empty($chatAuthor)&&$chatAuthor!="")){
	    	if(empty($chatAuthor)||$chatAuthor=="")
				die('{"error":'.json_encode($error."#chat004 :\r\nCan`t add new message. Author name is missing.").'}');
		    else if(empty($chatContent)||$chatContent=="")
		    	die('{"error":'.json_encode($error."#chat005 :\r\nCan`t add new message. Message content is missing.").'}');
		    else{
		    	$semafor = sem_get(234567, 1);
				if(sem_acquire($semafor)){
			    	if(strlen($chatContent)>$CHAT_MAXLENGTH_MSG)
			    		die('{"error":'.json_encode($error."#chat010 :\r\nCan`t add new message. Message content is too long.").'}');
			    	if(strlen($chatAuthor)>$CHAT_MAXLENGTH_AUTHOR)
			    		die('{"error":'.json_encode($error."#chat004 :\r\nCan`t add new message. Author name is too long.").'}');

			    	if(count($lines)){
			    		$lines[count($lines)-1] = $lines[count($lines)-1]."\r\n";
			    		$linesLen = count($lines);
			    		while($linesLen >= $CHAT_MAXIMUM_MESSAGES){
			    			unset($lines[$linesLen-$CHAT_MAXIMUM_MESSAGES]);
			    			$linesLen--;
			    		}
				    }
				    $chatAuthor = substr($chatAuthor,1,-1);
				    $chatContent = substr($chatContent,1,-1);
				    $newDate = microtime(get_as_float);

				    array_push($lines,$newDate." ".$chatAuthor." ".$chatContent);
				    $resContent .= "\r\n".$chatAuthor.": ".$chatContent;

				    file_put_contents($CHAT_FILE_NAME, $lines);

				    $chatIntDate = explode(".",$newDate);
					touch($CHAT_FILE_NAME,intval($chatIntDate[0]));

					$addMsg = true;
					sem_release($semafor);
				}else
					die('{"error":'.json_encode($error."#chat009 :\r\nCan`t acquire the semaphore.").'}');	
			}
		}
		echo '{"content":'.(($resContent)?json_encode($resContent):'""').',"date":'.(($newDate!=$webDate)?$newDate:$webDate).(($addMsg)?',"addMsg":"true"':'').'}';
		
	}else
		echo '{"error":'.json_encode($error."#chat002 :\r\nCan`t parse messages.").'}';
}else
	echo '{"error":'.json_encode($error."#chat001 :\r\nNo data was sended.").'}';
?>