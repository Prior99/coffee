<?php
	class ContentUserlist extends Content
	{		
		public function printTitle() {
			echo("Benutzer");
		}
		
		public function printHTML()
		{
			if($this->coffee->getUser() != -1 || $this->coffee->getCode() != -1) {
				setcookie("user", null, -3600, "/");
				setcookie("code", null, -3600, "/");
				unset($_COOKIE["user"]);
				unset($_COOKIE["code"]);
				header("Location: index.php");
			}
			?>
				<input 
					type="text" 
					name="search" 
					autocomplete="off" 
					value="Suchen" 
					style="margin-bottom: 10px;" 
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
										.append($("<a class='username'>" + user.firstname + " " + user.lastname + "</a>")
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
