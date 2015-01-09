<?php
require_once 'lib.php';
require_once 'header.php';
?>
<body>
	<div id="wrapper">
		<?php
			require 'menu.php';
		?>
		<div id="main">
		<?php
			if(!empty($_GET)){
				$blogId = ($_GET["blog_id"])?$_GET["blog_id"]:false;
				$postId = ($_GET["post_id"])?$_GET["post_id"]:false;
			}
			if(isset($blogId)&&is_dir('blogs/'.$blogId)&&isset($postId)&&strlen($postId)==16){
				if(!empty($_POST)){
					$errorArr = array();
					$checkVars = array("comType"=>"type","comCont"=>"content","comAuthor"=>"name");
					foreach($checkVars as $key => $value){
						if(strlen($_POST[$key]))
							${$value} = my_escape_string($_POST[$key]);
						else
							$errorArr[$key]="This field must be filled.";
					}
				}
				
				if(!empty($_POST)&&isset($errorArr)&&!count($errorArr)&&isset($name)&&isset($content)&&isset($type)){
					$commentDir = 'blogs/'.$blogId.'/'.$postId.'.k';

					$semafor = sem_get(12345);
					sem_acquire($semafor);
					my_create_dir($commentDir);
					my_check_privileges($commentDir);
					$i=0;
					while(file_exists($commentDir.'/'.$i)&&!is_dir($commentDir.'/'.$i))
						$i++;

					if($fh = fopen($commentDir.'/'.$i,'w') or die ($error."#koment001 - Failed to create file.")){
					    $content = $type."\r\n"
					    		   .date('Y-m-d, H:i:s')."\r\n"
					    		   .$name."\r\n"
					    		   .$content;
					    if(flock($fh, LOCK_EX)) {
						    fwrite($fh, $content) or die ($error."#koment002 - Failed to save file.");
						    fflush($fh) or die ($error."#koment003 - Failed flush the buffer.");
						    flock($fh, LOCK_UN) or die ($error."#koment006 - Failed to unlock the file.");
						    fclose($fh) or die ($error."#koment004 - Failed to close file pointer.");
						} else
							die ($error."#koment005 - Failed to lock the file.");
					}
					sem_release($semafor);
					?>
					<div class="positive">
						<h1>Comment successfully added!</h1>
						<br>
						You can see it <a href="./blog.php?nazwa=<?php echo $blogId ?>" title="<?php echo $blogId ?> blog">here!</a>
					</div>
				<?php
				}else{
				?>
				<h1>Comment a post:</h1>
				<?php if(isset($errorArr)&&count($errorArr))
					  	echo '<h2 class="bad">Form contains some errors, please correct it.</h2>';
				?>
				<div class="formWrapper">
					<form method="POST">
						<div class="formElem">
							<?php if(isset($errorArr)&&strlen($errorArr["comType"]))
								echo '<span class="bad">'.$errorArr["comType"].'</span>';
							?>
							<label for="comType">Type of comment:</label>
							<select id="comType" name="comType">
							  <option value="positive"<?php if(!$_POST['comType']||$_POST['comType']=='positive') echo ' selected="selected"';?>>Positive</option>
							  <option value="neutral"<?php if($_POST['comType']=='neutral') echo ' selected="selected"';?>>Neutral</option>
							  <option value="negative"<?php if($_POST['comType']=='negative') echo ' selected="selected"';?>>Negative</option>
							</select>
						</div>
						<div class="formElem">
							<?php if(isset($errorArr)&&strlen($errorArr["comCont"]))
								echo '<span class="bad">'.$errorArr["comCont"].'</span>';
							?>
							<label for="comCont">Comment content:</label>
							<textarea rows="4" cols="50" id="comCont" name="comCont"><?php echo $_POST['comCont'];?></textarea>
						</div>
						<div class="formElem">
							<?php if(isset($errorArr)&&strlen($errorArr["comAuthor"]))
								echo '<span class="bad">'.$errorArr["comAuthor"].'</span>';
							?>
							<label name="comAuthor">Your name/pseudo:</label>
							<input type="text" id="comAuthor" name="comAuthor" value="<?php echo $_POST['comAuthor'];?>" />
						</div>
						<div class="formElem form-btns">
							<input type="reset" value="Reset Form" />
							<input type="submit" value="Submit" />
						</div>
					</form>
				</div>
				<?php
				}
		}else{
		?>
			<h1 class="bad">Oppps! Something went wrong!</h1>
			<p>You definitely shouldn't be here. Try operation again and if this issue still shows up, please contact administration.</p>
		<?php
		}
		?>
		</div>
	</div>
</body>
</html>
