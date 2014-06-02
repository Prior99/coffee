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
	</table>
	<script type="text/javascript">
		function init() {
			$.ajax({
				url : "?json=stats"
			}).done(function(result) {
				var arr = JSON.parse(result);
				$('<tr class="head"></tr>')
					.append('<td style="width: 200px;">Vorname</td>')
					.append('<td style="width: 200px;">Nachname</td>')
					.append('<td style="width: 50px;">Krzl.</td>')
					.append('<td style="width: 100px;">Ausstehend</td>')
					.append('<td style="width: 200px;">Abrechnen</td>')
				.appendTo($("#tbl"));
				function showPopup(id, select) {
					var popup = $("<div class='popup'></div>").appendTo("div.blocker").click(function(e){e.stopPropagation();});
					$("div.blocker").show().click(function() {
						popup.remove();
						$("div.blocker").hide();
					});
					$.ajax({
						url : "?json=stats&user=" + id + "&month=" + select
					}).done(function(json) {
						var table = $("<table></table>").append($("<tr class='head'></tr>")
							.append("<td style='width: 200px;'>Monat</td>")
							.append("<td style='width: 100px;'>Ausstehend</td>")
							.append("<td style='width: 100px;'>Tilgen</td>"));
						popup.append(table);
						var arr = JSON.parse(json);
						for(var key in arr) {
							(function(obj) {
								var betrag = obj.money;
								if(betrag == null || betrag == undefined || betrag == "null") betrag = 0;
								table.append($("<tr></tr>")
									.append("<td>" + key + "</td>")
									.append("<td>" + betrag.toFixed(2) + "€</td>")
									.append($("<td></td>")
										.append($("<button>Tilgen</button>").click(function() {
											$.ajax({
												url : "?json=pay&user=" + id + "&lower=" + obj.lower + "&upper=" + obj.upper
											}).done(function(e) {
												popup.remove();
												$("#tbl").html("");
												init();
												showPopup(id, select);
												/*******/
											});
										}))
									));
							})(arr[key]);
						}
					});
				}
				
				for(var key in arr) {
					var obj = arr[key];
					(function(obj, index) {
						if(obj.pending == null || obj.pending == undefined || obj.pending == "null") obj.pending = 0;
						var select = $("<select size=1></select>");
						for(var i = 1; i <= 12; i++) {
							select.append("<option value='" + i + "' " + (i == 3 ? "selected='true'" : "") + ">" + i + " Monate</option>")
						}
						var row = $("<tr></tr>")
							.append("<td>" + obj.firstname + "</td>")
							.append("<td>" + obj.lastname + "</td>")
							.append("<td>" + obj.short + "</td>")
							.append("<td>" + obj.pending.toFixed(2) + "€</td>")
							.append($("<td></td>")
								.append(select).append($("<button>Abrechnen</button>").click(function() {
									showPopup(obj.id, select.val());
								}))
							);
						$("#tbl").append(row);
					})(obj, key);
				}
			});
		}
		init();
	</script>
</body>
<?php
	}
	else {
		$coffee->printJSON($_GET["json"]);
	}
	ob_end_flush();
?>
