<?php
	ob_start(); //Initialize Outputbuffering to prevent sending headers on startup. This way I can manipulate Cookies and redirect
	date_default_timezone_set("Europe/Berlin"); //Force timezone to circumvent possible bug/missconfiguration on productionenvironement
	require_once(__DIR__."/config.php"); //Load configuration as php-file containing info about passwords and database
	require_once(__DIR__."/src/coffee.php"); //Import Main class which will load any other necessary classes
	$time = microtime(true); //Start of timemeasurement for performance measurements
	$coffee = new Coffee(); //Init instance of main pagegenerating class
	if(!isset($_GET["json"])) { //If json is set, we have an API-Request instead of a normal HTML-Request an thous we skip all HTML-output
		if(!isset($_GET["action"])) { //action determines, which page should be loaded. If nothing is set, load the default page
			$_GET["action"] = "userlist";
		}
		$coffee->selectContent($_GET["action"]); //Tell the pagegenerator which page should be loaded
		//This invoke does not yet generate anything, it just instances the respective content-class
		/*
		 * Below: HTML
		 */
?>
<!DOCTYPE HTML>
<head>
	<link rel="icon" type="image/png" href="/favicon.png" />
	<meta name="viewport" content="width=device-width, initial-scale=1 maximum-scale=1, user-scalable=0" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="style/fantasque_sans_mono/stylesheet.css" />
	<link rel="stylesheet" type="text/css" href="style/style.css" />
	<?php
		if(isset($_COOKIE["hc"])) {
			?>
				<link rel="stylesheet" type="text/css" href="style/highcontrast.css" />
			<?php
		}
	?>
	<script src="lib/jquery.min.js"></script>
	<script src="lib/cookies.js"></script>
	<script src="lib/popup.js"></script>
</head>
<body>
	<div class="wrapper">
		<div class="title">

			<?php
				$coffee->printTitle(); //Print the title of the loaded content
			?>
		</div>
		<div class="content">
			<?php
				$coffee->printHTML(); //Print the page.
				//This invoke now generates everything and might run database-queries etc.
			?>
		</div>
		<div class="footer">
			<a style="left: 10px; position: absolute;" href="?action=<?php echo($_GET["action"]); ?>&help=true">
				<img src="style/help.svg" height=30/>
			</a>
			<?php
				$username = $coffee->getUsername(); //Load the username
				if($username === null) { //If username is null, no one is currently logged in and we display the default menu for admin/administration
					echo("<a href='?action=admin'>Admin</a>");
					echo(" | <a href='verwaltung.php'>Verwaltung</a>");
				}
				else {
					echo($username); //Print the username
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
	else { //json was set to true and this request is an API-Request, print the API instead of any HTML
		$coffee->printJSON($_GET["json"]);
	}
	ob_end_flush(); //As outputbuffering was switched on, we may switch headers and content of the HTTP-Response now.
?>
