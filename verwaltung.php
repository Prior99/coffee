<?php
	ob_start();
	date_default_timezone_set("Europe/Berlin");
	require_once("config.php");
	require_once("src/coffee.php");
	$time = microtime(true); //Start of timemeasurement
	$coffee = new Coffee();
	if(!isset($_GET["json"])) {
?>
<!DOCTYPE HTML>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1 maximum-scale=1, user-scalable=0" />
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="style/verwaltung.css" />
	<script src="lib/jquery.min.js"></script>
	<script src="lib/cookies.js"></script>
</head>
<body>
	<div class="blocker"></div>
	<table id="tbl">
		<tr class="head">
			<td style="width: 200px;">Vorname</td>
			<td style="width: 200px;">Nachname</td>
			<td style="width: 50px;">Krzl.</td>
			<td style="width: 100px;">Ausstehend</td>
			<td style="width: 200px;">Abrechnen</td>
		</tr>
	</table>
	<script type="text/javascript">
		$.ajax({
			url : "?json=stats"
		}).done(function(result) {
			var arr = JSON.parse(result);
			function showPopup(id, select) {
				$("div.blocker").show();
				var popup = $("<div class='popup'></div>").appendTo("div.blocker");
				$.ajax({
					url : "?json=stats&user=" + id + "&month=" + select.val()
				}).done(function(json) {
					var table = $("<table></table>").append($("<tr class='head'></tr>")
						.append("<td style='width: 200px;'>Monat</td>")
						.append("<td style='width: 100px;'>Ausstehend</td>")
						.append("<td style='width: 100px;'>Tilgen</td>"));
					popup.append(table);
					var arr = JSON.parse(json);
					for(var key in arr) {
						var betrag = arr[key];
						if(betrag == null || betrag == undefined || betrag == "null") betrag = 0;
						table.append($("<tr></tr>")
							.append("<td>" + key + "</td>")
							.append("<td>" + betrag.toFixed(2) + "€</td>")
							.append($("<td></td>")
								.append($("<button>Tilgen</button>").click(function() {
									
								}))
							));
					}
				});
			}
			for(var key in arr) {
				var obj = arr[key];
				(function(obj, index) {
					var select = $("<select size=1></select>")
						.append("<option value='1'>1 Monat</option>")
						.append("<option value='2'>2 Monate</option>")
						.append("<option value='3' selected='true'>3 Monate</option>")
						.append("<option value='4'>4 Monate</option>")
						.append("<option value='5'>5 Monate</option>")
						.append("<option value='6'>6 Monate</option>")
						.append("<option value='7'>7 Monate</option>")
						.append("<option value='8'>8 Monate</option>")
						.append("<option value='9'>9 Monate</option>")
						.append("<option value='10'>10 Monate</option>");
					var row = $("<tr></tr>")
						.append("<td>" + obj.firstname + "</td>")
						.append("<td>" + obj.lastname + "</td>")
						.append("<td>" + obj.short + "</td>")
						.append("<td>" + obj.pending.toFixed(2) + "€</td>")
						.append($("<td></td>")
							.append(select).append($("<button>Abrechnen</button>").click(function() {
								showPopup(obj.id, select);
							}))
						);
					$("#tbl").append(row);
				})(obj, key);
			}
		});
	
	</script>
</body>
<?php
	}
	else {
		$coffee->printJSON($_GET["json"]);
	}
	ob_end_flush();
?>
