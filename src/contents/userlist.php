<?php
	class ContentUserlist extends Content
	{		
		public function printHTML()
		{
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
								userlist.append(
									$("<a class='username'></a>")
										.append("<div class='username'>" + user.firstname + " " + user.lastname + "</div>")
										.attr({
											href : "?action=login&user=" + user.id
										})
									);	
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
