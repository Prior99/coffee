<?php
	class ContentSettings extends Content
	{		
		public function printTitle() 
		{
			echo("Optionen");
		}
		
		public function printHTML()
		{
			if(!$this->coffee->checkPassword()) {
				?>
					<h1>Ähem!</h1>
					<p>Sie sollten nicht hier sein oder? Dürfte ich mal Ihren Ausweis sehen? </p>
				<?php
			}
			else {
				?>
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
					<a href="#" id="okay">Speichern</a> | 
					<a href="?action=buy" id="back">Zurück</a>
					<script type="text/javascript">
						var check = $("#check");
						var wrapper = $("#codewrapper");
						var code = <?php echo($this->coffee->getCode()); ?>;
						if(code == null) {
							check.prop({"checked": false});
							wrapper.hide();
						}
						else {
							check.prop({"checked": true});
							wrapper.show();
						}
						check.click(function() {
							if(check.prop("checked")) {
								wrapper.show();
							}
							else {
								wrapper.hide();
							}
						})
						var table = $("#code");
						var display = [];
						var index = 0;
						var selection = [];
						var pot = 100;
						var c = code;
						function markSelected(box) {
							if(box===undefined) return;
							box.css({"border" : "4px solid rgb(106, 86, 71)"});
							box.css({"padding" : "6px"});
						}
						function markUnselected(box) {
							if(box===undefined) return;
							box.css({"border" : "none"});
							box.css({"padding" : "10px"});
						}
						for(var i = 0; i < 3; i++) {
							var t = parseInt(c/pot);
							c -= t * pot;
							if(t <= 0) t = "";
							display.push($("<div class='char'>" + t + "</div>").appendTo($("#display")));
							pot /= 10;
						}
						markSelected(display[0]);
						
						function selected(x) {
							if(index >= 3) return;
							selection.push(x);
							markUnselected(display[index]);
							display[index++].html(x);
							markSelected(display[index]);
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
							markUnselected(display[index]);
							display[--index].html("");
							markSelected(display[index]);
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
						$("#okay").click(function() {
							var code = selection[0] * 100 + selection[1] * 10 + selection[2];
							if(check.prop("checked")) {
								if(index != 3) {
									for(var i in display) {
										(function() {
											var elem = display[i];
											var bcolor = elem.css("background-color");
											var color = elem.css("color");
											elem.css({"background-color" : "rgba(240, 120, 120, 0.9)"});
											elem.css({"color" : "red"});
											setTimeout(function() {
												elem.css({"background-color" : bcolor});
												elem.css({"color" : color});
											}, 200);
										})(i);
									}
								}
								else {
									var code = selection[0] * 100 + selection[1] * 10 + selection[2];
									$.ajax({
										url: "index.php?json=options&password=" + code
									}).done(function() {
										setCookie("code", code, 1);
										location.href = "index.php?action=buy";
									});
								}
							}
							else {
								$.ajax({
									url: "index.php?json=options&password=deactivated"
								}).done(function() {
									location.href = "index.php?action=buy";
								});
							}
						});					
					</script>
				<?php
			}
		}
	}
?>
