<?php
	require_once("config.php");
	require_once("src/coffee.php");
	$time = microtime(true); //Start of timemeasurement
	$coffee = new Coffee();
	if(!$_GET["json"]) {
?>

<!DOCTYPE HTML>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="style/style.css" />
	<link href='http://fonts.googleapis.com/css?family=Share+Tech' rel='stylesheet' type='text/css' />
	<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js"></script>
</head>
<body>
	
	<div class="title">
		BENUTEZRWAHL
	</div>
	<div class="content">
		<?php
			$coffee->printHTML();
		?>
	</div>
	<div class="footer">
		Generated in  <?php echo(number_format((microtime(true)-$time)/1000, 3)); ?>ms | <?php echo($coffee->querys); ?> SQL-Queries
	</div>
</body>

<?php
	}
	else {
		$coffee->printJSON($_GET["json"]);
	}
?>
