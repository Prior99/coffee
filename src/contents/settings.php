<?php
	/*
	 * This is the settingspage for the user where he can set a code and set whether mails should be sent or not
	 */
	class ContentSettings extends Content
	{
		//See content.php for documentation of printTitle(), printHTML() and printHelp()
		public function printTitle()
		{
			echo("Optionen");
		}

		public function printHelp() {
			?>
				<p>Hier können Sie Einstellungen für Ihren Account tätigen.</p>
				<p>Wenn Sie den Haken bei "Code Benutzen" setzen, so wird Ihr Account durch einen 3-Stelligen Zahlencode gesichert.</p>
				<p>Bitte stellen Sie dann den entsprechenden gewünschten Code ein und Speichern sie gegebenenfalls Ihre Änderungen.</p>
			<?php
		}

		public function printHTML()
		{
			if(!$this->coffee->checkPassword()) { //If the user is not logged in, display him (or her) a message
				?>
					<h1>Zugang nicht möglich</h1>
					<p>Die Benutzerauthentifizierung ist fehlgeschlagen. Bitte loggen Sie sich erneut ein.</p>
					<a href="index.php">Zurück</a>
				<?php
			}
			else {
				?>
					<p><input type="checkbox" id="mails"/> Bestätigungs-Mails nach Bestellung versenden</p>
					<p><input type="checkbox" id="check"/> Code benutzen</p>
					<div id="codewrapper">
						<div class="codedisplay">
							<div id="display" class="wrapper2">
							</div>
						</div>
						<table id="code" class="code">

						</table>
					</div>
					<br />
					<a href="#" class="button" id="okay">Speichern</a>
					<a href="?action=buy" class="button" id="back">Zurück</a>
					<script type="text/javascript">
						var check = $("#check");//The HTML-Element for the checkbox whether a code should be used or not
						var wrapper = $("#codewrapper"); //The wrapper around the pinpad to hide it when not needed
						/*
						 * Please skip the next line when reviewing the code
						 * This is DIRTY Black Magic!
						 */
						var code = <?php echo($this->coffee->getCode()); ?>; //...Please don't hit me. I did not want to create an API-Call just for this
						//Okay, you may continue reading from here
						if(code == null || code == -1) { //If no code was set, uncheck the checkbox
							check.prop({"checked": false});
							wrapper.hide(); //Hide the pinpad
							code = 0;
						}
						else {
							check.prop({"checked": true}); //Else check it
							wrapper.show();//And show the pinpad
						}
						check.click(function() { //If we change the value of the checkbox we will also have to...
							if(check.prop("checked")) {
								wrapper.show();//...show the pinpad or...
							}
							else {
								wrapper.hide();//...hide the pinpad
							}
						});
						/*
						 * Okay, this is basically the exact same thing as in login.php
						 * Only that the display will display the digits instead of the stars.
						 *
						 * But I will explain it again. Just for you :)
						 */
						var table = $("#code"); //Okay, this is the table we create the pinpad ing
						var display = []; //The divs used to display the digits in
						var index = 3; //We start at the last index (3) as the code is 000 per default or that one the user used before
						var selection = []; //An empty selection of digits (We will load them later on)
						/*
						 * This method will mark a box as selected
						 */
						function markSelected(box) {
							if(box===undefined) return;
							box.css({"border" : "4px solid rgb(106, 86, 71)"});
							box.css({"padding" : "6px"});
						}
						/*
						 * This method will mark a box as unselected
						 */
						function markUnselected(box) {
							if(box===undefined) return;
							box.css({"border" : "none"});
							box.css({"padding" : "10px"});
						}
						var pot = 100; //Exponent, used for converting the code from an integer to it's 3 digits later on
						var c = code; //Temporary copy of code to work with as we will use the code alter on
						for(var i = 0; i < 3; i++) { //Some weird mathstuff I must have coded when I was really tired.
							//I dont know how it works anymore, figure it out your self.
							//What it does: It converts an integer number (e.g. 876) to its digits 8 7 and 6. Also 0 to 0 0 and 0 and so on
							var t = parseInt(c/pot); //???
							c -= t * pot; //I don't know. I should have documented it right away when I invented it
							if(t < 0) t = "";
							display.push($("<div class='char'>" + t + "</div>").appendTo($("#display"))); //Display it in displays div :D
							selection.push(t); //Push calculated digit to digits-array
							pot /= 10; //Sometimes I am just smarter than other times. Now is not the time :(
						}
						markSelected(display[3]); //Select the 3rd one as we just filled out all digits.
						/*
						 * Called when the user entered a number and the cursor is moved
					     * Also directly checks if the password is correct if the 3rd was entered
						 */
						function selected(x) {
							if(index >= 3) return; //Don't do anything if the user enters more than 3 digits
							selection.push(x); //Save digit
							markUnselected(display[index]); //Update selection
							display[index++].html(x); //Update display and increase index
							markSelected(display[index]);
							//console.log(selection); //Nasty debugging output
						}
						/*
						 * Now, lets create the pinpad
						 */
						for(var i = 0; i < 3; i++) { //Count from 1 to 9 in a grid
							var tr = $("<tr></tr>").appendTo(table); //Create 3 rows
							for(var j = 1; j <= 3; j++) { //with 3 columns each
								(function (x) { //Scope out because of asynchroneous events
									var td = $("<td>" + x + "</td>").appendTo(tr) //Create new cell
										.on("mouseup", function() {
											selected(x); //Call selected with the number entered
										})
										.on("touchend", function(e) {
											selected(x);
											//Again, stop nasty browsers from calling everything twice by passing touchevents to mousevents.
											//Look at buy.php for more detailed grumbling about this problem
											e.stopPropagation();
											e.preventDefault();
										});
								})(j + 3*i);

							}
							table.append(tr);//Append row to table
						}
						//The zero and the backspace need to be created seperatly because the backspace spans two columns
						//I want explain this, just look in the loop above
						var zero = $("<td>0</td>")
							.on("mouseup", function() {
								selected(0);
							})
							.on("touchend", function(e) {
								selected(0);
								e.stopPropagation();
								e.preventDefault();
							});
						/*
						 * This method is called when backspace is pressed
						 */
						function backspace() {
							if(index == 0) return; //If we did not enter anything, do not delete it
							selection.pop();//Remove last digit
							markUnselected(display[index]); //Update selection
							display[--index].html("");//decrease index and clear element
							markSelected(display[index]);
							//console.log(selection); //Nasty debugging output
						}
						//Create the backspace, it's the same as for 1-9 and 0,
						//the only differences are, that it calls backspace() and spans two columns
						table.append($("<tr></tr>").append(zero).append($("<th colspan='2'> &larr; </th>")
							.on("mouseup", function() {
								backspace();
							}).on("touchend", function(e) {
								backspace();
								e.stopPropagation();
								e.preventDefault();
							})));
						/*
						 * The following now differs from login.php as it handles saving the code per API-Call
						 */
						$("#okay").click(function() { //If "Speichern" is clicked, save the code and the whether tos end mails or not
							if(mailsc.prop("checked")) { //Update whether the user wants to receive mails or whether not
								$.ajax({ //Call the API
									url: "index.php?json=send_mails&set=true"
								});
							}
							else {
								$.ajax({ //Call the API
									url: "index.php?json=send_mails&set=false"
								});
							}
							var code = selection[0] * 100 + selection[1] * 10 + selection[2]; //Calculate an integer from the digits
							if(check.prop("checked")) { //Figure out, if we should use a code at all or not
								var okay = true; //Flag that will be set to false if there is a digit missing, checked in loop below
								for(var i = 0; i < 3; i++) {
									(function(i) { //Scope out for asynchroneous timeout-event
										//If the selection is null in some kind or not a number, flash it red and set flag to false as the code is then invalid
										if(selection[i] == null || selection[i] == undefined || !(selection[i] >= 0 && selection[i] <= 9)) {
											var elem = display[i]; //Element to flash
											var bcolor = elem.css("background-color"); //Save color and backgroundcolor to restore them lateron
											var color = elem.css("color");
											elem.css({"background-color" : "rgba(240, 120, 120, 0.9)"}); //Set the color to red
											elem.css({"color" : "red"});
											//And slowly fade them back to their original colors.
											//This effect is done by CSS3-Transition and only works if they are supported.
											//So on IE and FF Mobile the elements will just become red for 0,2sec and then get back to
											//their original colors again.
											setTimeout(function() {
												elem.css({"background-color" : bcolor});
												elem.css({"color" : color});
											}, 200); //reset colors after 0,2sec
											okay = false;//Something went wrong, code unusable
										}
									})(i);
								}
								if(okay){ //Only if there were no problems with the code...
									var code = selection[0] * 100 + selection[1] * 10 + selection[2];
									$.ajax({ //...tell the API to store the new code
										url: "index.php?json=options&password=" + code
									}).done(function() {
										setCookie("code", code, 1);//Also update the cookie
										location.href = "index.php?action=buy"; //And reload the page
									});
								}
							}
							else { //The checkboy to determine whether to use a code or not was unchecked:
								$.ajax({ //Deactivate the code
									url: "index.php?json=options&password=deactivated"
								}).done(function() {
									location.href = "index.php?action=buy"; //Reload afterwards
								});
							}
						});
						/*
						 * Mails
						 */
						//Initially set or unset the checkbox for the mails
						//Depending on whether this feature was activated or deactivated
						var mailsc = $("#mails");
						$.ajax({
							url: "index.php?json=send_mails" //Ask the allmighty API
						}).done(function(res) {  //And update the HTML-Element an success
							if(res == "true") { //Yes, I know, JSON.parse() would have been more elegant
								//But I refuse to start up the whole Parsing-Engine just to determine
								//Whether a string is "true" or not
								mailsc.prop({"checked": true});
							}
							else {
								mailsc.prop({"checked": false});
							}
						});
					</script>
				<?php
			}
		}
	}
?>
