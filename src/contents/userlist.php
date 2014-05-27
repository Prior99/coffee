<?php
	class ContentUserlist extends Content
	{		
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
			if($this->coffee->getUser() != -1 || $this->coffee->getCode() != -1) {
				if(isset($_COOKIE["open"]) && $_COOKIE["open"] == true) {
					setcookie("user", null, -3600, "/");
					setcookie("code", null, -3600, "/");
					setcookie("open", true, 365*24*60*60*1000, "/");
					unset($_COOKIE["user"]);
					unset($_COOKIE["code"]);
					header("Location: index.php");
				}
				else {
					header("Location: index.php?action=buy");
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
				<div id="userlist">
				</div>
				<script type="text/javascript">
					$.ajax({
						url : "?json=userlist",
					}).done(function(res) {
						var userlist = $("#userlist");
						var response = JSON.parse(res);
						function generateList(arr) {			
							userlist.html("");
							for(var index in arr) {
								var user = arr[index];
								(function(user) {
									userlist
										.append($("<a class='username'>" + user.firstname + " " + user.lastname + " (" + user.short + ")" + "</a>")
										.click(function () {
											setCookie("user", user.id, 1);
											location.href = "?action=login";
										})
									);
								})(user);	
							}
						};
						generateList(response);
						var search = $("input[name='search']");
						search.keyup(function() {
							var value = search.val().toLowerCase();
							var arr = [];
							for(var index in response) {
								var user = response[index];
								if(user.firstname.toLowerCase().indexOf(value) !== -1 || user.lastname.toLowerCase().indexOf(value) !== -1) {
									arr.push(user);
								}
							}
							generateList(arr);
						}).click(function() {
							search.val("");
						});
					});
				</script>
			<?php
		}
	}
?>
