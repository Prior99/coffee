<?php
	/*
	 * This content is used to log a player in. It is only display if the user secured his account.
	 * The userlist.php always refers to this site and if it is secured, the pin-code-box will be displayed
	 * If the account is unsecured, it just redirects directly to buy.php
	 */
	class ContentLogin extends Content
	{
		//See content.php for documentation of printTitle(), printHTML() and printHelp()
		public function printTitle() {
			echo("Anmelden");
		}

		public function printHelp() {
			?>
				<p>Dieser Benutzer wurde mit einem 3-Stelligen Zahlencode gesichert. Bitte geben Sie den korrekten Code ein, um sich anzumelden.</p>
				<p>Wenn Sie Ihren Code vergessen haben, so melden Sie sich bitte beim Kaffeebeauftragten.</p>
			<?php
		}

		public function printHTML()
		{
			$userid = $this->coffee->getUser(); //Read the cookie
			/*
			 * Now check if this account is secured at all by grabbing it's password
			 */
			if($this->coffee->isUserLocked($userid)) {
				?>
					<h1>Gesperrt</h1>
					<p>Dieses Konto wurde gesperrt. Bitte wenden Sie sich an einen Vertreter der Kaffee-AG.</p>
					<a href="index.php">Zurück</a>
				<?php
				return;
			}
			$query = $this->coffee->db()->prepare("SELECT firstname, lastname, password FROM Users WHERE id = ?");
			$query->bind_param("i", $userid);
			$query->execute();
			$query->bind_result($first, $last, $password);
			$query->fetch();
			$name = $first." ".$last; //Beautiful representation of the users name
			$query->close();
			if($password === null) { //If the password is null, it was unsecured and we just redirect
				header("Location: index.php?action=buy&user=".$userid); //Possibly because of ob_start()
			}
			?>
				<div class="codedisplay">
					<div id="display" class="wrapper2">
					</div>
				</div>
				<table id="code" class="code">

				</table>
				<br />
				<div style="text-align: center;">
					<a href="index.php">Abbrechen</a>
				</div>
				<script type="text/javascript">
					var table = $("#code"); //The tabe we create the pin-pad in
					var display = []; //The 3 digits to display in (those are HTML-Elements!)
					var index = 0; //At which digit we currently are
					var selection = []; //The actual 3 digits
					for(var i = 0; i < 3; i++) {
						display.push($("<div class='char'></div>").appendTo($("#display"))); //Create the 3 digit "display"
					}
					/*
					 * Called when the user entered a number and the cursor is moved
					 * Also directly checks if the password is correct if the 3rd was entered
					 */
					function selected(x) {
						if(index == 3) return; //Do not do anything if 3 numbers are already entered
						selection.push(x); //Push the new number to the actual digits
						display[index++].html("*"); //Draw the star into the respective div
						if(index == 3) { //If the 3rd digit was entered, validate the password
							var code = selection[0] * 100 + selection[1] * 10 + selection[2]; //Calculate a decimal number from the digits
							$.ajax({ //Ask the allmighty API
								url : "?json=validate&user=" + getCookie("user") + "&code=" + code
							}).done(function(res) {
								var result = JSON.parse(res);
								if(result.okay) { //If the code was okay, ...
									setCookie("code", code, 1); //...set the cookie...
									location.href = "?action=buy";//...and redirect to buy.php
								}
								else 
								if(result.locked) { //Account was locked
									location.href=location.href;
								}
								else { //If not, reset the pinpad and flash it red
									for(var i in display) {
										(function(i) { //Scope out
											var elem = display[i]; //Current element
											var bcolor = elem.css("background-color");//Save current background color for later restoring
											var color = elem.css("color");//Save current textcolor for later restoring
											elem.css({"background-color" : "rgba(240, 120, 120, 0.9)"}); //Set the red colors
											elem.css({"color" : "red"});
											//Please Note: I am using CSS-3 Transitions for the gradient effect.
											//On IE and old browser and FF Mobile no flashing will occur. The field will go red and after 0,2sec back
											//To the original color. Poor IE and FF Mobile users :(

											//After the elements were red for 0,2sec slowly fade them back their original colors (If CSS-3 Transitions are supported)
											setTimeout(function() {
												elem.html("");
												elem.css({"background-color" : bcolor});
												elem.css({"color" : color});
											}, 200);
										})(i);
									}
									index = 0; //Set index back to 0 as the user will have to type in the code all-over again
									selection = []; //Reset the digits already entered
								}
							});
						}
						//console.log(selection); //Nasty debugging outputs
					}
					/*
					 * Now for the fun-part: Create the pinpad
					 */
					for(var i = 0; i < 3; i++) {
						var tr = $("<tr></tr>").appendTo(table); //Three rows in the table
						for(var j = 1; j <= 3; j++) {
							(function (x) { //Scope out because of asynchroneous events
								//x is the number the user pressed
								var td = $("<td>" + x + "</td>").appendTo(tr) //And 3 columns
									.on("mouseup", function() {
										selected(x); //Call selected with the number entered
									})
									.on("touchend", function(e) {
										selected(x);
										//Again, stop nasty browsers from calling everything twice by passing touchevents to mousevents.
										//Look at buy.php for more detailed grumbling about this problem
										if(e.stopPropagation) e.stopPropagation();
										e.cancelBubble = true;
										e.stopImmediatePropagation();
										e.preventDefault();
									});
							})(j + 3*i);
						}
						table.append(tr); //Attach to table
					}
					//The zero and the backspace need to be created seperatly because the backspace spans two columns
					//I want explain this, just look in the loop above
					var zero = $("<td>0</td>")
						.on("mouseup", function() {
							selected(0);
						})
						.on("touchend", function(e) {
							selected(0);
							if(e.stopPropagation) e.stopPropagation();
							e.cancelBubble = true;
							e.stopImmediatePropagation();
							e.preventDefault();
						});
					/*
					 * This method is called when backspace is pressed
					 */
					function backspace() {
						if(index == 0) return; //If we did not enter anything, do not delete it
						selection.pop(); //Remove last digit
						display[--index].html(""); //decrease index and clear element
						//console.log(selection); //Nasty debugging output
					}
					//Create the backspace, it's the same as for 1-9 and 0,
					//the only differences are, that it calls backspace() and spans two columns
					table.append($("<tr></tr>").append(zero).append($("<th colspan='2'> &larr; </th>")
						.on("mouseup", function() {
							backspace();
						}).on("touchend", function(e) {
							backspace();
							if(e.stopPropagation) e.stopPropagation();
							e.cancelBubble = true;
							e.stopImmediatePropagation();
							e.preventDefault();
						})));
				</script>
			<?php
		}
	}
?>
