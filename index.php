<?php
	ob_start();
	date_default_timezone_set("Europe/Berlin");
	require_once("config.php");
	require_once("src/coffee.php");
	$time = microtime(true); //Start of timemeasurement
	$coffee = new Coffee();
	if(!isset($_GET["json"])) {
		if(!isset($_GET["action"])) {
			$_GET["action"] = "userlist";
		}
		$coffee->selectContent($_GET["action"]);
?>
<!DOCTYPE HTML>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1 maximum-scale=1, user-scalable=0" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="style/fantasque_sans_mono/stylesheet.css" />
	<link rel="stylesheet" type="text/css" href="style/style.css" />
	<script src="lib/jquery.min.js"></script>
	<script src="lib/cookies.js"></script>
</head>
<body>
	<div class="wrapper">
		<div class="title">
			
			<?php
				$coffee->printTitle();
			?>
		</div>
		<div class="content">
			<?php
				$coffee->printHTML();
			?>
		</div>
		<div class="footer">
			<a style="left: 10px; position: absolute;" href="?action=<?php echo($_GET["action"]); ?>&help=true">
				<img src="style/help.svg" height=30/>
			</a>
			<?php
				$username = $coffee->getUsername();
				if($username === null) {
					echo("<a href='?action=impressum'>Impressum</a>"); 
					echo(" | <a href='?action=admin'>Admin</a>"); 
				}
				else {
					echo($username);
					?>
						<a style="right: 10px; position: absolute;" href="?action=settings">
							<img src="style/settings.svg" height=30/>
						</a>
					<?php
				}
			?>
		</div>
	</div>
</body>
<?php
	}
	else {
		$coffee->printJSON($_GET["json"]);
	}
	ob_end_flush();
?>
