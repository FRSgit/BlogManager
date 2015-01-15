<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="application/xhtml+xml;charset=UTF-8" />
	<meta name="description" content="Projekt na przedmiot języki i technologie webowe" />
	<meta name="author" content="FRS" />
	<title>Project - "języki i technologie webowe"</title>
	<link rel="stylesheet" type="text/css" href="css/main.css" title="Orange" />
	<link rel="alternate stylesheet" type="text/css" href="css/blue.css" title="Blue" />
	<link rel="alternate stylesheet" type="text/css" href="css/green.css" title="Green" />
	<script type="text/javascript" src="js/main.js"></script>
	<?php require_once("config.php"); ?>
	<script type="text/javascript">
		var MAX_ATTACHMENTS = <?php echo $GLOBALS["MAXIUMUM_ATTACHMENTS"]; ?>;
		var CHAT_TIME_TO_REFRESH = <?php echo $GLOBALS["CHAT_TIME_TO_REFRESH"]; ?>;
		var CHAT_MAXLENGTH_AUTHOR = <?php echo $GLOBALS["CHAT_MAXLENGTH_AUTHOR"]; ?>;
		var CHAT_MAXLENGTH_MSG = <?php echo $GLOBALS["CHAT_MAXLENGTH_MSG"]; ?>;
	</script>
</head>