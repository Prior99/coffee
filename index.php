<?php
	ob_start();
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
			Generated in  <?php echo(number_format((microtime(true)-$time)/1000, 3)); ?>ms | <?php echo($coffee->querys); ?> SQL-Queries
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
