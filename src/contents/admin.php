<?php
	class ContentAdmin extends Content
	{		
		public function printTitle() {
			echo("Admin");
		}
		public function printHelp() {
			?>
				<p>Administratoren sollten nicht die Hilfe aufrufen müssen!</p>
			<?php
		}
		
		public function printHTML()
		{
			?>
				<p>Sie können eine Liste mit Mitarbeitern aus einer CSV-Datei Importieren. 
					Die Datei muss mit Semikolon getrennt sein (Microsoft-Excel) und exakt 2 Spalten besitzen. 
					Die erste Spalte enthält den Nach-, die zweite Spalte den Vornamen. 
					Bereits vorhandene Mitarbeiter werden <b>nicht</b> überschrieben.
					Die erste Zeile wird als Kopfzeile betrachtet und ignoriert (nicht Importiert).</p>
				<form enctype="multipart/form-data" id="form" style="float: left;">
					<input type="file" name="file" />
				</form>
				<button id="import" style="float: left;">Importieren</button>
				<br style="clear: both;"/>
				<br />
				<div id="progress">
					<div id="bar"></div>
					<div id="percent"></div>
				</div>
				<table id="report">
				
				</table>
				<script type="text/javascript">
					$("#import").click(function () {
						console.log("click");
						var form = new FormData($("#form")[0]);
						function progress(e) {
							if(e.lengthComputable) {
								var percent = parseInt((e.loaded/e.total)*100) +"%";
								$("#bar").css({
									width: percent
								}).html(percent);
							}
							console.log(e);
						}
						$.ajax({
							url: "?json=import",
							type: "POST",
							xhr: function() {
								var xhr = $.ajaxSettings.xhr();
								if(xhr.upload) {
									xhr.upload.addEventListener("progress", progress, false);
								}
								return xhr;
							},
							cache: false,
							contentType: false,
							data: form,
							processData: false,
							success : function(html) {
								console.log("Done");				
								var obj = JSON.parse(html);
								$("#report")
									.append($("<tr></tr>").append("<td></td>").append("<td>Anzahl</td>"))
									.append($("<tr></tr>").append("<td>Datensätze</td>").append("<td>" + obj.total + "</td>"))
									.append($("<tr></tr>").append("<td>Eingefügt</td>").append("<td>" + obj.inserted + "</td>"))
									.append($("<tr></tr>").append("<td>Übersprungen</td>").append("<td>" + obj.skipped + "</td>"))
									.append($("<tr></tr>").append("<td>Ungültig</td>").append("<td>" + obj.invalid + "</td>"));
								
							}
						});
					});
				</script>
			<?php
		}
	}
?>
