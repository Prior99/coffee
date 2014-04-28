<?php
	class ContentLogin extends Content
	{		
		public function printHTML()
		{
			$userid = $_GET["user"];
			
			$query = $this->coffee->db()->prepare("SELECT firstname, lastname FROM Users WHERE id = ?");
			$query->bind_param("i", $userid);
			$query->execute();
			$query->bind_result($first, $last);
			$query->fetch();
			$name = $first." ".$last;
			$query->close();
			?>
				<!--<p>Als <?php echo($name); ?> anmelden:</p>-->
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
								url : "?json=validate&user=<?php echo($userid); ?>&code=" + code
							}).done(function(res) {
								var result = JSON.parse(res);
								if(result.okay) {
									location.href = "?action=buy&user=<?php echo($userid); ?>&code=" + code;
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
								var td = $("<td>" + x + "</td>").appendTo(tr).click(function() {
									selected(x);
								});
							})(j + 3*i);
							
						}
						table.append(tr);
					}
					var zero = $("<td>0</td>").click(function() {
						selected(0);
					});
					table.append($("<tr></tr>").append(zero).append($("<th colspan='2'> &larr; </th>").click(function() {
						if(index == 0) return;
						selection.pop();
						display[--index].html("");
						console.log(selection);
					})));
				</script>
			<?php
		}
	}
?>
