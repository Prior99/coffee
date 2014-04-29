<?php
	class ContentLogin extends Content
	{		
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
			$userid = $this->coffee->getUser();
			
			$query = $this->coffee->db()->prepare("SELECT firstname, lastname, password FROM Users WHERE id = ?");
			$query->bind_param("i", $userid);
			$query->execute();
			$query->bind_result($first, $last, $password);
			$query->fetch();
			$name = $first." ".$last;
			$query->close();
			if($password === null) {
				header("Location: index.php?action=buy&user=".$userid);
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
					var table = $("#code");
					var display = [];
					var index = 0;
					var selection = [];
					for(var i = 0; i < 3; i++) {
						display.push($("<div class='char'></div>").appendTo($("#display")));
					}
					
					function selected(x) {
						if(index == 3) return;
						selection.push(x);
						display[index++].html("*");
						if(index == 3) {
							var code = selection[0] * 100 + selection[1] * 10 + selection[2];
							$.ajax({
								url : "?json=validate&user=" + getCookie("user") + "&code=" + code
							}).done(function(res) {
								var result = JSON.parse(res);
								if(result.okay) {
									setCookie("code", code, 1); 
									location.href = "?action=buy";
								}
								else {
									for(var i in display) {
										(function() {
											var elem = display[i];
											var bcolor = elem.css("background-color");
											var color = elem.css("color");
											elem.css({"background-color" : "rgba(240, 120, 120, 0.9)"});
											elem.css({"color" : "red"});
											setTimeout(function() {
												elem.html("");
												elem.css({"background-color" : bcolor});
												elem.css({"color" : color});
											}, 200);
										})(i);
									}
									index = 0;
									selection = [];
								}
							});
						}
						console.log(selection);
					}
					
					for(var i = 0; i < 3; i++) {
						var tr = $("<tr></tr>").appendTo(table);
						for(var j = 1; j <= 3; j++) {
							(function (x) {
								var td = $("<td>" + x + "</td>").appendTo(tr)
									.on("mouseup", function() {
										selected(x);
									})
									.on("touchend", function(e) {
										selected(x);
										e.stopPropagation();
										e.preventDefault();
									});
							})(j + 3*i);
							
						}
						table.append(tr);
					}
					var zero = $("<td>0</td>")
						.on("mouseup", function() {
							selected(0);
						})
						.on("touchend", function(e) {
							selected(0);
							e.stopPropagation();
							e.preventDefault();
						});
					function backspace() {
						if(index == 0) return;
						selection.pop();
						display[--index].html("");
						console.log(selection);
					}
					table.append($("<tr></tr>").append(zero).append($("<th colspan='2'> &larr; </th>")
						.on("mouseup", function() {
							backspace();
						}).on("touchend", function(e) {
							backspace();
							e.stopPropagation();
							e.preventDefault();
						})));						
				</script>
			<?php
		}
	}
?>
