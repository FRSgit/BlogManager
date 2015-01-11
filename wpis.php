<?php 
require_once 'lib.php';
require_once 'header.php'; 
?>
<body onload="wpis();">
	<div id="wrapper">
		<?php
			require_once 'menu.php';
		?>
		<div id="main">
		<?php	
			
			if(!empty($_POST)){
				$errorArr = array();
				$checkVars = array("userName"=>"login","userPasswd"=>"passwd","postCont"=>"post");
				foreach($checkVars as $key => $value){
					if(strlen($_POST[$key]))
						${$value} = my_escape_string($_POST[$key]);
					else
						$errorArr[$key]="This field must be filled.";
				}

				if(isset($login)&&isset($passwd)){
					$dir=check_user($login,$passwd);
					if(!$dir)
						$errorArr["userName"]="Wrong login or/and password. Please try again.";
				}
				if(strlen($_POST["postDate"])){
					$dateSplit = explode("-",my_escape_string($_POST["postDate"]));
					if(validateDate(my_escape_string($_POST["postDate"]))&&isset($dateSplit[0])&&isset($dateSplit[1])&&isset($dateSplit[2]))
						$date=$dateSplit[0].$dateSplit[1].$dateSplit[2];
					else
						$errorArr["postDate"]="Bad date format. Should be YYYY-MM-DD.";
				}else
					$errorArr["postDate"]="Problem with date sending. Please contact administration.";

				if(strlen($_POST["postTime"])){
					$timeSplit = explode(":",my_escape_string($_POST["postTime"]));
					if(validateDate(my_escape_string($_POST["postTime"]),'H:i')&&isset($timeSplit[0])&&isset($timeSplit[1]))
						$time=$timeSplit[0].$timeSplit[1];
					else
						$errorArr["postTime"]="Bad time format. Should be HH:MM.";
				}else
					$errorArr["postTime"]="Problem with time sending. Please contact administration.";
				
				if(count($_FILES["postAttach"])&&count($_FILES["postAttach"]["name"])&&count($_FILES["postAttach"]["name"])>8){
					$errorArr["postAttach1"]= "Too much attachments!";
					$postAttachNo = 1;
				}else if(count($_FILES["postAttach"])&&count($_FILES["postAttach"]["name"])){
					$postAttachNo = count($_FILES["postAttach"]["name"]);
					for($j=0;$j<$postAttachNo;$j++){
						if(!$_FILES["postAttach"]["name"][0]||$_FILES["postAttach"]["name"][0]=="")
							$postAttachNo--;
						if($_FILES["postAttach"]["size"][$j]&&$_FILES["postAttach"]["size"][$j]>512000)
							$errorArr[$tmpFile]= "Sorry, file nr ".($j+1)." is too large.";
					}
				}else
					$postAttachNo=0;
			}
			
			if(!empty($_POST)&&!count($errorArr)&&isset($dir)&&isset($post)&&isset($date)&&isset($time)){
				$targetFile = "blogs/" . $dir . "/" . $date . $time . date("s") . substr(md5(uniqid(rand(), true)),2,2);
				if($postAttachNo){
					for($j=0;$j<$postAttachNo;$j++){
						$tmpName = $_FILES["postAttach"]["tmp_name"][$j];
						$fileName = $_FILES["postAttach"]["name"][$j];
						if(is_uploaded_file($tmpName) or die ($error."#wpis001 - Problem with uploading file.")){
							$targetFileTmp = $targetFile.($j+1);
							if(pathinfo($fileName, PATHINFO_EXTENSION)&&pathinfo($fileName, PATHINFO_EXTENSION)!="")
								$targetFileTmp.=".".pathinfo($fileName, PATHINFO_EXTENSION);
							move_uploaded_file($tmpName, $targetFileTmp) or die ($error."#wpis002 - Problem with moving uploaded file.");
					    }
					}
				}
				$semafor = sem_get(1234);
				sem_acquire($semafor);
				if(!file_exists($targetFile) && $fh = fopen($targetFile,'w') or die ($error."#wpis003 - Failed to create file.")){
				    if(flock($fh, LOCK_EX)) {
					    fwrite($fh, $post) or die ($error."#wpis004 - Failed to save file.");
					    fflush($fh) or die ($error."#wpis005 - Failed flush the buffer.");
					    flock($fh, LOCK_UN) or die ($error."#wpis008 - Failed to unlock the file.");
					    fclose($fh) or die ($error."#wpis006 - Failed to close file pointer.");
					} else
						die ($error."#wpis007 - Failed to lock the file.");
				}
				sem_release($semafor);
				?>
				<div class="positive">
					<h1>Post successfully added!</h1>
					<br>
					You can see it <a href="./blog.php?nazwa=<?php echo $dir ?>" title="<?php echo $dir ?> blog">here!</a>
				</div>
			<?php
			}else{
			?>
			<h1>Add post to blog:</h1>
			<?php if(isset($errorArr)&&count($errorArr))
				  	echo '<h2 class="bad">Form contains some errors, please correct it.</h2>';
			?>
			<div class="formWrapper">
				<form method="post" enctype="multipart/form-data" action="">
					<div class="formElem">
						<?php if(isset($errorArr)&&strlen($errorArr["userName"]))
							echo '<span class="bad">'.$errorArr["userName"].'</span>';
						?>
						<label for="userName">Username:</label>
						<input type="text" id="userName" name="userName" value="<?php echo $_POST['userName'];?>" />
					</div>
					<div class="formElem">
						<?php if(isset($errorArr)&&strlen($errorArr["userPasswd"]))
							echo '<span class="bad">'.$errorArr["userPasswd"].'</span>';
						?>
						<label for="userPasswd">Password:</label>
						<input type="password" id="userPasswd" name="userPasswd" />
					</div>
					<div class="formElem">
						<?php if(isset($errorArr)&&strlen($errorArr["postCont"]))
							echo '<span class="bad">'.$errorArr["postCont"].'</span>';
						?>
						<label for="postCont">Post content:</label>
						<textarea rows="4" cols="50" id="postCont" name="postCont"><?php echo $_POST['postCont'];?></textarea>
					</div>
					<div class="formElem">
						<?php if(isset($errorArr)&&strlen($errorArr["postDate"]))
							echo '<span class="bad">'.$errorArr["postDate"].'</span>';
						?>
						<label for="postDate">Date: (YYYY-MM-DD)</label>
						<input type="text" id="postDate" name="postDate" value="<?php echo $_POST["postDate"] ; ?>" />
					</div>
					<div class="formElem">
						<?php if(isset($errorArr)&&strlen($errorArr["postTime"]))
							echo '<span class="bad">'.$errorArr["postTime"].'</span>';
						?>
						<label for="postTime">Time: (HH:MM)</label>
						<input type="text" id="postTime" name="postTime" value="<?php echo $_POST["postTime"] ; ?>" />
					</div>
					<div class="formElem">
						<?php 
						if($postAttachNo&&isset($errorArr)){
							$errorString = '';
							while($postAttachNo){
								if(strlen($errorArr["postAttach".$postAttachNo]))
									$errorString .= $errorArr["postAttach".$postAttachNo]." ";
								$postAttachNo--;
							}
							if(strlen($errorString))
								echo '<span class="bad">'.$errorString.'</span>';
						}
						?>
						<label>Attach File:</label>
						<div>
							<input class="block" type="file" name="postAttach[]" />
							<button type="button" name="addAnotherFile">Add another file</button>
						</div>
					</div>
					<div class="formElem form-btns">
						<input type="reset" value="Reset Form" />
						<input type="submit" value="Submit" />
					</div>
				</form>
			</div>
			<?php
			}
			?>
		</div>
	</div>
	<?php
		require_once 'footer.php';
	?>
</body>
</html>
