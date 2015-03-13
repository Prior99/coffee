<?php
	/*
	 * This is the content for the userlist displayed when the webpage is loaded
	 * It displays a list of all users with a neat live-search
	 */
	class ContentUserlist extends Content
	{
		//See content.php for documentation of printTitle(), printHTML() and printHelp()
		public function printTitle() {
			echo("Benutzer");
		}

		public function printHelp() {
			?>
				<p>Dies ist eine Liste mit allen registrierten Mitarbeitern.</p>
				<p>Wählen Sie Ihren Namen aus der Liste um Ihre "Striche" machen zu können.</p>
				<p>Die Suche agiert Live und die Liste wird während der Eingabe oder bei drücken der Entertaste (Firefox) aktualisiert, so finden Sie Ihren Namen schnell.</p>
				<p>Sollte Ihr Name fehlen, so wenden Sie sich bitte an den Kaffeebeauftragten.</p>
			<?php
		}

		public function printHTML()
		{
			if($this->coffee->getUser() != -1 || $this->coffee->getCode() != -1) { //
				if(isset($_COOKIE["open"]) && $_COOKIE["open"] == true) {
					//If this is a public device, always clear all cookies on page load.
					//This is because many pages redirect here and this way I ensure there are no logininformation stored at all
					//Also if any user just closes his browser-tab and that browser does not delete those sessions cookie if
					//not the whole WINDOW closed (I am looking at you, IE, FF Mobile and Opera!) now his credentials are gone!
					setcookie("user", null, -3600, "/");//Delete cookies from HTTP
					setcookie("code", null, -3600, "/");
					setcookie("open", true, 365*24*60*60*1000, "/"); //Refresh the best-before-date of the cookie for autologoff
					unset($_COOKIE["user"]);//Delete cookies for this session
					unset($_COOKIE["code"]);
					header("Location: index.php");//Redirect as long as there are still cookies left (cached by JS or whyever)
				}
				else {
					header("Location: index.php?action=buy"); //Else if there are some cookies left and this is a private device,
					//The user might want to go directly to his buy-pages (from which he can directly logout or just stay there)
				}
			}
			?>
				<input
					type="text"
					name="search"
					autocomplete="off"
					value="Suchen"
					style="margin-bottom: 10px; width: 100%;"
				/>
				<div class="keyboard">
					<table id="keyboard" class="code"></table>
				</div>
				<div id="userlist">
				</div>
				<script type="text/javascript">
					var letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÜß"
					var search = $("input[name='search']");
					var userlist = $("#userlist"); //HTML-Element to display list in
					var keyboard = $("#keyboard");
					var keyboarddiv = $("div.keyboard");
					var kbinit = false;
					var row;
					for(var i = 0; i < letters.length; i++) {
						if(i % 8 == 0) {
							row = $("<tr></tr>").appendTo(keyboard);
						}
						(function(j) {
							var c = letters.charAt(j);
							search.keyup();
							row.append($("<td>" + c + "</td>").click(function() {
								if(!kbinit) {
									kbinit = true;
									search.val("");
								}
								search.val(search.val() + c);
								search.keyup();
							}));
						})(i);
					}
					row.append($("<th colspan='4'>&larr;</th>").click(function() {
						search.val(search.val().substring(0, search.val().length - 1));
						search.keyup();
					}));
					keyboarddiv.hide();
					$.ajax({//retrieve an array with all users from the server
						url : "?json=userlist",
					}).done(function(res) {
						var response = JSON.parse(res);
						/*
						 * This method generates a beautiful list out of an array with all users
						 */
						function generateList(arr) {
							userlist.html(""); //First of all, clear the list
							for(var index in arr) { //Now walk through every element in the array
								var user = arr[index]; //Grab the element
								(function(user) { //Scope out because of asynchroneous events
									userlist //Append a new link to the list...
										.append($("<a class='username'>" + user.firstname + " " + user.lastname + " (" + user.short + ")" + "</a>")
										.click(function () {
											setCookie("user", user.id, 1); //...that, once clicked sets the corresponding cookiie and redirects to login
											location.href = "?action=login"; //login.php will then use the cookie and redirect to buy.php or prompt the user for his code
										})
									);
								})(user);
							}
						};
						generateList(response); //Generate the initial list
						search.keyup(function() { //If the live-searchbox was used
							var value = search.val().toLowerCase();//get the content of the box
							var arr = []; //Create a new array
							for(var index in response) { //And store only those elements in this array that contain the entered value
								var user = response[index];
								//Check whether first- or lastname contain the entered phrase
								if(user.firstname.toLowerCase().indexOf(value) !== -1 || user.lastname.toLowerCase().indexOf(value) !== -1) {
									arr.push(user); //Then save them to the array
								}
							}
							generateList(arr);//The array now contains only those elements that have the entered phrase in them. Display it now!
						}).click(function() {
							kbinit = true;
							if(getCookie("osk")) {
								keyboarddiv.show();
							}
							search.val(""); //If clicked, delete the contents (Because it initially contains "Suchen")
						});
					});
				</script>
			<?php
		}
	}
?>
