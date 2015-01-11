<?php
require_once("config.php");

$error = "Sorry, we have encountered some technical issues. Error code: ";

/* -- INSTALLATION SECTION - REMOVE IF EXECUTED ONCE -- */
	if(!file_exists('blogs')||!is_dir('blogs')){
		$mainSem = sem_get(123456);
		sem_acquire($mainSem);
		my_create_dir('blogs');
		sem_release($mainSem);
	}
/* -- END -- */

/* -- WINDOWS SEM WORKAROUND -- 
if (!function_exists('sem_get')) {
    function sem_get($key) {
        return fopen(__FILE__ . '.sem.' . $key, 'w+');
    }
    function sem_acquire($sem_id) {
        return flock($sem_id, LOCK_EX);
    }
    function sem_release($sem_id) {
        return flock($sem_id, LOCK_UN);
    }
}
 -- END -- */

function my_create_dir($path){
	if(!file_exists($path)||!is_dir($path)){
		mkdir($path, 0755) or die ($error."#lib001 - Problem with creating directory. (path - ".$path.")");
		touch($path);
		chmod($path, 0755) or die ($error."#lib002 - Problem with setting directory's privileges. (path - ".$path.")");
	}
}

function my_check_privileges($path){
	$tmpMode = substr(sprintf('%o', fileperms($path)), -4);
	($tmpMode=="0755"||$tmpMode=="0777") or die ($error."#lib008 - Directory's privileges didn't change. Current dir mode: ".$tmpMode." (path - ".$path.")");
}

function my_escape_string($data){	 
	return trim((get_magic_quotes_gpc())?stripslashes($data):$data);
}

function validateDate($date, $format = 'Y-m-d'){
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) == $date;
}

function sort_md_arr_by_id($tmpArr){
	usort($tmpArr, function($a, $b) {;
	    return strtoupper($b["id"]) < strtoupper($a["id"]);
	}) or die ($error."#lib015 - Failed to sort array by id.");
	return $tmpArr;
}

function find_all_blogs(){
	if($h = opendir('blogs/') or die ($error."#lib011 - Failed to open directory. (path - ".'blogs/'.")")) {
	    $result = array();
	    while(false !== ($blogId = readdir($h))){
	        if($blogId != "." && $blogId != ".." && file_exists('blogs/'.$blogId) && is_dir('blogs/'.$blogId)){
	        	$tmpArr = array("id" => $blogId);
	        	if(file_exists('blogs/'.$blogId.'/info')){
			        $info = file('blogs/'.$blogId.'/info',FILE_IGNORE_NEW_LINES) or die ($error."#lib012 - Failed to create file. (path - ".'blogs/'.$blogId.'/info)');
			        $desc = "";
					for($i=2;$i<count($info);$i++){
					    $desc .= $info[$i]."<br>";
					}
					rtrim($desc, "<br>");
					$tmpArr["desc"] = $desc;
					$tmpArr["user"] = $info[0];
			    }
	        	array_push($result, $tmpArr);
	        }
	    }
	    closedir($h);
	    return sort_md_arr_by_id($result);
	}
	return false;
}

function find_all_posts($blog){
	if($h = opendir('blogs/'.$blog.'/') or die ($error."#lib009 - Failed to open directory. (path - ".'blogs/'.$blog.'/'.")")) {
	    $result = array();
	    
	    $posts = array();
	    while(false !== ($posts[] = readdir($h)));
	    rsort($posts)  or die ($error."#lib017 - Failed to posts files by name(publish time).");
	    closedir($h);
	    foreach($posts as $postId) {
	        if($postId != "." && $postId != ".." && strlen($postId)==16){
	        	$tmpArr = array("id" => $postId);
	        	$lines = file('blogs/'.$blog.'/'.$postId,FILE_IGNORE_NEW_LINES) or die($error."#lib010 - Failed to open file. (path - ".'blogs/'.$blog.'/'.$postId.")");
				$post = "";
				foreach($lines as $line_num => $line) {
				    $post .= $line."<br>";
				}
				rtrim($post, "<br>");
				$tmpArr["content"] = $post;
				$tmpArr["dateTime"] = substr($postId,8,2).":".substr($postId,10,2).":".substr($postId,12,2).", ".substr($postId,6,2)."-".substr($postId,4,2)."-".substr($postId,0,4);

				$i=1;
				$filesArr = array();
				while(($files = glob('blogs/'.$blog.'/'.$postId.$i."*"))){
					if(count($files)==1){
						foreach($files as $file){
							$info = pathinfo($file);
							$tmpFileName = $postId.$i;
							if($info["extension"]&&$info["extension"]!="")
								$tmpFileName .= ".".$info["extension"];
						    array_push($filesArr,$tmpFileName);
						}
					}else
						break;
					$i++;
				}

				if(count($filesArr)>0)
					$tmpArr["files"]=$filesArr;

				array_push($result, $tmpArr);
	        }
	    }
	    return $result;
	}
	return false;
}

function find_all_comments($blog,$postId){
	if(isset($blog)&&isset($postId)&&file_exists('blogs/'.$blog.'/'.$postId.'.k')&&is_dir('blogs/'.$blog.'/'.$postId.'.k')) {
	    $result = array();

	    for($i=0;file_exists('blogs/'.$blog.'/'.$postId.'.k/'.$i);$i++){
	    	$tmpArr = array();
	    	$lines = file('blogs/'.$blog.'/'.$postId.'.k/'.$i,FILE_IGNORE_NEW_LINES) or die($error."#lib013 - Failed to open file. (path - ".'blogs/'.$blog.'/'.$postId.'.k/'.$i.")");
	    	$comment = "";
	    	$tmpArr["type"]=$lines[0];
	    	$tmpArr["dateTime"]=$lines[1];
	    	$tmpArr["author"]=$lines[2];
			for($j=3;$j<count($lines);$j++) {
			    $comment .= $lines[$j]."<br>";
			}
			rtrim($comment, "<br>");
			$tmpArr["content"] = $comment;
			
			array_unshift($result, $tmpArr);
	    }
	    return $result;
	}
	return false;
}

/*
Function that finds blog with passed user's login, and then:
if password isn't passed, function return true if blog with login == $login was found
and if password is passed, function return blog's name if $login and $password match any of blog's accounts
*/
function check_user($login, $passwd=null){
	$result = false;
	if($h = opendir('blogs/') or die ($error."#lib003 - Failed to open directory.")) {
	    while(false !== ($dir = readdir($h))) {
	        if($dir != "." && $dir != ".." && is_dir('blogs/'.$dir) && file_exists('blogs/'.$dir.'/info')){
		        $file = fopen('blogs/'.$dir.'/info', "r") or die ($error."#lib004 - Failed to create file. (path - ".'blogs/'.$dir.'/info)');
		        $tmpLog = fgets($file) or die ($error."#lib005 - Failed to read login.");
	        	if($passwd!=null){
	        		$tmpPwd = fgets($file) or die ($error."#lib014 - Failed to read password.");
	        		fclose($file) or die ($error."#lib006 - Failed to close file.");
	        		if(strcmp(trim($tmpLog),$login)==0 && strcmp(trim($tmpPwd),md5($passwd))==0){
	        			$result = $dir;
	        			break;
	        		}
	        	}else{
	        		fclose($file) or die ($error."#lib007 - Failed to close file.");
	        		if(strcmp(trim($tmpLog),$login)==0){
	        			$result=true;
	        			break;
	        		}
	        	}
	        	       		
	        }
	    }
	    closedir($h);
	}
	return $result; 
}
?>