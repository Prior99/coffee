<?php
	/*
	 * See index.php first for more in-detail documentation
	 * As this pages functions quiet similiar but is not as well documented as index.php
	 */
	ob_start(); //Init outputbuffering
	date_default_timezone_set("Europe/Berlin"); //Prevent any timezonebugs/misconfigurations on productionenvironement
	require_once("config.php"); //Load configuration for databaseaccess
	require_once("src/coffee.php");
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
	<table id="tbl">
	</table>
	<p>Klicken Sie hier um die Sitzung zu beenden und den Zugang zu sperren: <button id="logout">Abmelden</button></p>
	<script type="text/javascript">
		var sorting = "lastname";
		if(getCookie("sorting")) {
			sorting = getCookie("sorting");
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
					.append($('<td style="width: 100px;">Ausstehend</td>').append($("<a href='#'>▲</a>").click(function() { changeSorting("sum"); })))
					.append('<td style="width: 200px;">Abrechnen</td>')
					.append('<td style="width: 110px;">Erinnern</td>')
				.appendTo($("#tbl")); //Head-line of the table
				/*
				 * Displays the popup that opens if you click on a user
				 * Blurs the background out and makes all other elements inaccessible
				 * Then loads their detailed statistics per month per API-Request and displays corresponding the table
				 */
				function showPopup(id, select) {
					var popup = $("<div class='popup'></div>").appendTo("div.blocker").click(function(e){e.stopPropagation();});
					$("div.blocker").show().click(function() {
						popup.remove(); //If clicked anywhere in the background, close the popup
						$("div.blocker").hide();
					});
					$.ajax({ //Load detailed per-month-statistics
						url : "?json=stats&user=" + id + "&month=" + select
					}).done(function(json) {
						var table = $("<table></table>").append($("<tr class='head'></tr>")
							.append("<td style='width: 200px;'>Monat</td>")
							.append("<td style='width: 100px;'>Ausstehend</td>")
							.append("<td style='width: 100px;'>Tilgen</td>"));
						popup.append(table); //Create table and headline
						var arr = JSON.parse(json); //Array of all months and their saldos
						for(var key in arr) { //Key is now the name of each month
							(function(obj) { //Scope out
								var betrag = obj.money;
								if(betrag == null || betrag == undefined || betrag == "null") betrag = 0; //To ensure an integer
								table.append($("<tr></tr>") //Add a new line to the table
									.append("<td>" + key + "</td>") //Key is the name of the month
									.append("<td>" + betrag.toFixed(2) + "€</td>") //Display the saldo properly formatted
									.append($("<td></td>") //Another column for the button
										.append($("<button>Tilgen</button>").click(function() {
											$.ajax({ //Tell the API to delete saldo
												url : "?json=pay&user=" + id + "&lower=" + obj.lower + "&upper=" + obj.upper
											}).done(function(e) {
												popup.remove(); //Remove the popup
												/*
												 * Refresh the table
												 */
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

				for(var key in arr) { //Iterate over each and every user in the system
					var obj = arr[key]; //arr was previously loaded from the API (look above) and contains each user as an object
					(function(obj, index) {
						if(obj.pending == null || obj.pending == undefined || obj.pending == "null") obj.pending = 0; //Ensure integer on null
						var select = $("<select size=1></select>");//The select as a dropdown
						for(var i = 1; i <= 12; i++) {
							//Add options for 1 to 12 months to review
							//Make 3 selected by default
							select.append("<option value='" + i + "' " + (i == 3 ? "selected='true'" : "") + ">" + i + " Monate</option>")
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
						var row = $("<tr></tr>") //Create new row for each user
							.append("<td>" + obj.firstname + "</td>") //This should be self-explanary
							.append("<td>" + obj.lastname + "</td>")
							.append("<td>" + obj.short + "</td>")
							.append("<td>" + obj.mail + "</td>")
							.append("<td>" + obj.pending.toFixed(2) + "€</td>")
							.append($("<td></td>")
								.append(select).append($("<button>Abrechnen</button>").click(function() {
									showPopup(obj.id, select.val()); //On click display corresponding popup
								}))
							)
							.append($("<td></td>").append(nudge));
						$("#tbl").append(row);
					})(obj, key);
				}
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
