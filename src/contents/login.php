<?php
	class ContentLogin extends Content
	{		
		public function printHTML()
		{
			$userid = $_GET["user"];
			?>
				<div class="codedisplay" id="display">
				
				</div>
				<table id="code" class="code">
				
				</table>
				<script type="text/javascript">
					var table = $("#code");
					var display = [];
					for(var i = 0; i < 3; i++) {
						display.push($("<div></div>").appendTo($("#display")));
					}
					for(var i = 0; i < 3; i++) {
						var tr = $("<tr></tr>").appendTo(table);
						for(var j = 1; j <= 3; j++) {
							var td = $("<td>" + (j + 3*i) + "</td>").appendTo(tr)
						}
						table.append(tr);
					}
					table.append($("<tr></tr>").append("<th colspan='3'> &larr; </th>"));
				
				</script>
			<?php
		}
	}
?>
