<?php
	/*
	 * See index.php first for more in-detail documentation
	 * As this pages functions quiet similiar but is not as well documented as index.php
	 */
	ob_start(); //Init outputbuffering
	date_default_timezone_set("Europe/Berlin"); //Prevent any timezonebugs/misconfigurations on productionenvironement
	require_once(__DIR__."/config.php"); //Load configuration for databaseaccess
	require_once(__DIR__."/src/coffee.php");
	$time = microtime(true); //Start of timemeasurement
	$coffee = new Coffee(); //Also the Verwaltung needs an instance of the Pagegenerator
	if(!isset($_GET["json"])) { //As well the Verwaltung has an API-Request-Option and should only print HTML when this is non API-request
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
<div class="container">
<?php
	if(isset($_COOKIE["control"]) && $_COOKIE["control"] == $GLOBALS["config"]["Controlpassword"]) { //Check whether the user is logged in
	//By checking if the password stored in the cookies (Please note, this makes stealing of the password possible by pages
	//from the same domain or an subdomain but this is not an highest-security-system and we are the only services on this domain
	//after all)
?>
	<h1>Verwaltung</h1>
	<div id="sum"></div>
	<table id="tbl">
	</table>
	<p>Klicken Sie hier um die Sitzung zu beenden und den Zugang zu sperren: <button id="logout">Abmelden</button></p>
	<script type="text/javascript">
		var sorting = "lastname";
		if(getCookie("sorting")) {
			sorting = getCookie("sorting");
		}

		function getColor(value) {
			if (value > 0) {
				return 'green';
			} else if (value < 0) {
				return 'red';
			} else {
				return 'blue';
			}
		}

		function init() { //Called when page has fully loaded
			$("#logout").click(function() { //If the logoutbutton is clicked,
				deleteCookie("control"); //Delete the cookie (this is the action necessary to log a user out)
				window.location.href = window.location.href; //And reload the page
			});

			$.ajax({ //Do an API-Request to load the global statistics and generate the initial list
				url : "?json=stats&order=" + sorting
			}).done(function(result) {
				//List is loaded
				var arr = JSON.parse(result); //arr now contains a list of all users and their statistics
				function changeSorting(sort) {
					setCookie("sorting", sort);
					sorting = sort;
					$("#tbl").html("");
					init();
				}
				$('<tr class="head"></tr>')
					.append($('<td style="width: 200px;">Vorname</td>').append($("<a href='#'>▲</a>").click(function() { changeSorting("firstname"); })))
					.append($('<td style="width: 200px;">Nachname</td>').append($("<a href='#'>▲</a>").click(function() { changeSorting("lastname"); })))
					.append($('<td style="width: 55px;">Krzl.</td>').append($("<a href='#'>▲</a>").click(function() { changeSorting("short"); })))
					.append('<td style="width: 100px;">E-Mail</td>')
					.append($('<td style="width: 100px;">Kontostand</td>').append($("<a href='#'>▲</a>").click(function() { changeSorting("sum"); })))
					.append('<td style="width: 200px;">Abrechnen</td>')
					.append('<td style="width: 110px;">Erinnern</td>')
				.appendTo($("#tbl")); //Head-line of the table
				/*
				 * Displays the popup that opens if you click on a user
				 * Blurs the background out and makes all other elements inaccessible
				 * Then loads their detailed statistics per month per API-Request and displays corresponding the table
				 */
				function showPopup(id) {
					var popup = $("<div class='popup'></div>").appendTo("div.blocker").click(function(e){e.stopPropagation();});
					$("div.blocker").show().click(function() {
						popup.remove(); //If clicked anywhere in the background, close the popup
						$("div.blocker").hide();
					});
					var warn = $("<div class='warn'><h1>Nicht möglich</h1><p>Der einzuzahlende Betrag ist niedriger als der ausstehende Betrag. Bitte mindestens die ausstehenden Schulden bezahlen um das Konto ins Positive zu rücken.</p></div>").hide();
					var input = $('<input type="text" value="0.00" />').appendTo(
						popup
							.append("<h1>Einzahlen</h1><p>Es muss mindestens der ausstehende Betrag eingezahlt werden.</p>")
							.append(warn)
					);
					var button = $('<button>Okay</button>').appendTo(popup);

					button.click(function() {
						var value = input.val().replace(",", ".");
						value = parseInt(parseFloat(value) * 100);
						$.ajax({ //Tell the API to delete saldo
							url : "?json=pay&user=" + id + "&value=" + value
						}).done(function(ok) {
							if(ok === "true") {
								popup.remove(); //Remove the popup
								$("div.blocker").hide();
								/*
								 * Refresh the table
								 */
								$("#tbl").html("");
								init();
							}
							else {
								warn.show();
							}
						});
					});
				}
				var sum = 0;
				for(var key in arr) { //Iterate over each and every user in the system
					var obj = arr[key]; //arr was previously loaded from the API (look above) and contains each user as an object
					(function(obj, index) {
						if(obj.pending == null || obj.pending == undefined || obj.pending == "null") {
							obj.pending = 0; //Ensure integer on null
						}
						else {
							obj.pending = parseInt(obj.pending);
						}
						var nudge = $("<button>E-Mail senden</button>").click(function() {
							nudge.attr("disabled", "disabled");
							nudge.html("Senden...");
							$.ajax({
								url : "?json=nudge&user=" + obj.id,
								success : function() {
									nudge.html("Versandt!");
								}
							})
						});
						sum += obj.pending;
						var row = $("<tr></tr>") //Create new row for each user
							.append("<td>" + obj.firstname + "</td>") //This should be self-explanary
							.append("<td>" + obj.lastname + "</td>")
							.append("<td>" + obj.short + "</td>")
							.append("<td>" + obj.mail + "</td>")
							.append("<td style='font-weight: bold; color: " + getColor(obj.pending) + "'>" + (obj.pending / 100).toFixed(2) + "€</td>")
							.append($("<td></td>")
								.append($("<button>Einzahlen</button>").click(function() {
									showPopup(obj.id); //On click display corresponding popup
								}))
							)
							.append($("<td></td>").append(nudge));
						$("#tbl").append(row);
					})(obj, key);
				}
				$("#sum").html("Kontostand: <span style='font-weight: bold; color: " + getColor(sum) + ";'>" + (sum / 100).toFixed(2) + "€</span><br />");
			});
		}
		init();
	</script>
<?php
	}
	else { //If not logged in, display loginmask
?>
	<h1>Zugang gesperrt!</h1>
	<p>Sie müssen angemeldet sein um auf diese Seite Zugriff zu erlangen.</p>
	<input name="password" type="password" />
	<button>Anmelden</button>
	<br />
	<br />
	<a href="index.php">Zurück</a>
	<script type="text/javascript">
		function login() {
			setCookie("control", $("input[name='password']").val()); //Just set the cookie,
			//regardless if it is correct or not. The page will check and verify itself on reload
			window.location.href = window.location.href; //Then reload to either display loginmask again or display verwaltung
		}
		$("input[name='password']").keyup(function(e) {
			if(e.which == 13) {
				login(); //Emulate send-on-enter in js-only mask
			}
		});
		$("button").click(login); //Also send-on-click
	</script>
<?php
	}
?>
</div>
</body>
<?php
	}
	else {
		$coffee->printJSON($_GET["json"]); //On API-Request, display response
	}
	ob_end_flush(); //End buffering
?>
