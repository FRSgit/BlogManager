<?php
require_once 'header.php'; 
require_once 'lib.php';
?>
<body>
	<div id="wrapper">
		<?php
			require_once 'menu.php';
		?>
		<div id="main">
		<?php
		
		
		if(!empty($_POST)){
			$errorArr = array();
			$checkVars = array("blogName"=>"name","userName"=>"login","userPasswd"=>"passwd","blogDesc"=>"desc");
			foreach($checkVars as $key => $value){
				if(strlen($_POST[$key]))
					${$value} = my_escape_string($_POST[$key]);
				else
					$errorArr[$key]="This field must be filled.";
			}

			if(isset($name)&&file_exists('blogs/'.$name))
			    $errorArr["blogName"]="Blog with this name already exists. Please choose another one.";
			if(isset($login)&&check_user($login))
				 $errorArr["userName"]="User with this name already exists. Please choose another one.";
		}
		if(!empty($_POST)&&isset($errorArr)&&!count($errorArr)&&isset($name)&&isset($login)&&isset($passwd)&&isset($desc)){
			$semafor = sem_get(123);
			sem_acquire($semafor);
			my_create_dir('blogs/'.$name);
			my_check_privileges('blogs/'.$name);
			if(!file_exists('blogs/'.$name.'/info') && $fh = fopen('blogs/'.$name.'/info','w') or die ($error."#nowy004 - Failed to create file.")){
			    $content = $login."\r\n".md5($passwd)."\r\n".$desc;
			    if(flock($fh, LOCK_EX)) {
				    fwrite($fh, $content) or die ($error."#nowy005 - Failed to save file.");
				    fflush($fh) or die ($error."#nowy006 - Failed flush the buffer.");
				    flock($fh, LOCK_UN) or die ($error."#nowy009 - Failed to unlock the file.");
				    fclose($fh) or die ($error."#nowy007 - Failed to close file pointer.");
				} else
					die ($error."#nowy008 - Failed to lock the file.");
			}
			sem_release($semafor);
			?>
			<div class="positive">
				<h1>Blog successfully created!</h1>
				<br>
				You can see it <a href="./blog.php?nazwa=<?php echo $name ?>" title="<?php echo $name ?> blog">here!</a>
			</div>
		<?php
		}else{
		?>
			<h1>Create new blog</h1>
			<?php if(isset($errorArr)&&count($errorArr))
				  	echo '<h2 class="bad">Form contains some errors, please correct it.</h2>';
			?>
			<div class="formWrapper">
				<form method="POST">
					<div class="formElem">
						<?php if(isset($errorArr)&&strlen($errorArr["blogName"]))
							echo '<span class="bad">'.$errorArr["blogName"].'</span>';
						?>
						<label for="blogName">Blog name:</label>
						<input type="text" id="blogName" name="blogName" value="<?php echo $_POST['blogName'];?>" />
					</div>
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
						<input type="text" id="userPasswd" name="userPasswd" value="<?php echo $_POST['userPasswd'];?>" />
					</div>
					<div class="formElem">
						<?php if(isset($errorArr)&&strlen($errorArr["blogDesc"]))
							echo '<span class="bad">'.$errorArr["blogDesc"].'</span>';
						?>
						<label for="blogDesc">Blog description:</label>
						<textarea rows="4" cols="50" id="blogDesc" name="blogDesc"><?php echo $_POST['blogDesc'];?></textarea>
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
