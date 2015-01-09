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
				$blogId = $_GET["nazwa"];
			}
			if(!empty($_GET)&&isset($blogId)&&is_dir('blogs/'.$blogId)){
				$blogArr = file('blogs/'.$blogId.'/info',FILE_IGNORE_NEW_LINES) or die($error."#blog001 - Failed to open file.");
				$blogDesc = "";
				$blogDesc .= $blogArr[2];
				for($i=3;$i<count($blogArr);$i++) {
				    $blogDesc .= "<br>".$blogArr[$i];
				}
				?>
				<h1><?php echo $blogId; ?></h1>
				<h2>by <?php echo $blogArr[0]; ?></h2>
				<p class="desc"><?php echo $blogDesc; ?></p>
				<?php
					$postArr = find_all_posts($blogId);
					for($i=0;$i<count($postArr);$i++){
				?>
					<div class="article">
						<span class="text-right"><?php echo $postArr[$i]["dateTime"]; ?></span>
						<p class="text-right"><?php echo $postArr[$i]["content"]; ?></p>
						<div class="commentsWrapper">
							<a class="text-left" href="./koment.php?blog_id=<?php echo $blogId.'&post_id='.$postArr[$i]["id"]; ?>">Write a comment</a>
							<?php
							$comments = find_all_comments($blogId,$postArr[$i]["id"]);
							if(isset($comments)&&count($comments)>0&&$comments!=array()){
								echo '<div class="comments">';
								for($j=0;$j<count($comments);$j++){
								?>
								<div class="comment <?php echo $comments[$j]["type"]; ?>">
									<span class="text-left"><?php echo $comments[$j]["dateTime"]; ?></span>
									<p class="text-right"><?php echo $comments[$j]["content"]; ?></p>
									<span class="text-right">Author: <?php echo $comments[$j]["author"]; ?></span>
								</div>
								<?php
								}
								echo '</div>';
							}
							?>
						</div>
						<?php
						if(isset($postArr[$i]["files"])&&count($postArr[$i]["files"])>0){
							echo '<div class="attachments text-right"><h4>Attachments:</h4>';
							for($j=0;$j<count($postArr[$i]["files"]);$j++){
								?>
								<a title="Attachment no. <?php echo $j+1; ?>" href="./blogs/<?php echo $blogId.'/'.$postArr[$i]['files'][$j]; ?>">Attachment <?php echo $j+1; ?></a>
								<?php
							}
							echo '</div>';
						}
						?>
					</div>
				<?php
				}
			}else if(!empty($_GET)&&isset($blogId)&&!is_dir('blogs/'.$blogId)){
			?>
				<h1 class="bad">Oppps! Something went wrong!</h1>
				<p>Probably the blog you're looking for doesn't exist!</p>
				<p>Try operation again and if this issue still shows up, please contact administration.</p>
			<?php
			}else{
			?>
				<h1>List of all blogs:</h1>
				<?php
				$blogs = find_all_blogs();
				if(isset($blogs)&&count($blogs)>0&&$blogs!=array()){
				?>
					<ol>
						<?php
						foreach($blogs as $b){

						?>
							<li>
								<a href="./blog.php?nazwa=<?php echo $b['id']; ?>" title="<?php echo $b['id']; ?> blog"><?php echo $b['id'] . ' by ' . $b['user']; ?></a> 
								<p><?php echo $b[desc]; ?></p>
							</li>
						<?php
						}
						?>
					</ol>
				<?php
				}else{
				?>
					<h2>Unfortunately there are no blogs to show. Maybe you should try to create one...? :)</h2>
				<?php
				}
			}
			?>
		</div>
	</div>
</body>
</html>
