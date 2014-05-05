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
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) {
			?>
				<p>Einstellungen, die Sie vornehmen werden sofort automatisch per XHTML-Request vorgenommen. Sie müssen nicht auf einen "Speichern"-Button klicken o.Ä.
				Bitte achten Sie entsprechend auf Ihre Änderungen, da diese also unwiederruflich sofort durchgeführt werden.</p>
				<a href="#" id="export_a"><h2>Exportieren</h2></a>
				<div id="export_div">
					<p>Sie können sich für jeden Monat eine Liste des Getränkeverbrauchs aller Benutzer als mit Semikolon getrennte CSV-Datei (Microsoft Excel) exportieren.</p>
					<select size="1" id="month">
						<option value="1">Januar</option>
						<option value="2">Februar</option>
						<option value="3">März</option>
						<option value="4">April</option>
						<option value="5">Mai</option>
						<option value="6">Juni</option>
						<option value="7">Juli</option>
						<option value="8">August</option>
						<option value="9">September</option>
						<option value="10">Oktober</option>
						<option value="11">November</option>
						<option value="12">Dezember</option>
					</select>
					<select size="1" id="year">
						
					</select>
					<button id="export">Exportieren</button>
				</div>
				
				<a href="#" id="import_a"><h2>Importieren</h2></a>
				<div id="import_div">
					<p>Sie können eine Liste mit Mitarbeitern aus einer CSV-Datei Importieren. 
						Die Datei muss mit Semikolon getrennt sein (Microsoft Excel) und exakt 2 Spalten besitzen. 
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
					<div id="invalid">
					
					</div>
				</div>
				
				<a href="#" id="open_a"><h2>Öffentliches Gerät</h2></a>
				<div id="open_div">
					<p>In öffentlichen Geräten werden Benutzer automatisch nach kurzer Zeit der Inaktivität abgemeldet. 
					Auf privaten Geräten führt ein Aufrufen der Wurzelseite nicht zur Benutzerliste sondern direkt zum Kaufen.</p>
					<p><input type="checkbox" id="open"/> Dies ist ein öffentliches Gerät</p>
				</div>
				
				<a href="#" id="delete_a"><h2>Benutzer löschen</h2></a>
				<div id="delete_div">
					<p>Bitte geben Sie einen Benutzer an, der gelöscht werden soll. Bitte überprüfen Sie Ihre Eingaben vor dem Bestätigen, da einmal gelöschte Benutzer nicht wiederhergestellt werden können.</p>
				</div>
				
				<a href="#" id="code_a"><h2>Code Zurücksetzen</h2></a>
				<div id="code_div">
					<p>Hat ein Benutzer seinen 3-Stelligen Code vergessen, können Sie hier den Code für diesen Benutzer entfernen.</p>
				</div>
				
				<a href="#" id="add_a"><h2>Benutzer ergänzen</h2></a>
				<div id="add_div">
					<p>Hier können Sie ohne großen Aufwand einen einzelnen Benutzer hinzufügen.</p>	
				</div>
				<a href="#" id="logout">Als Admin abmelden</a>
				<script type="text/javascript">
					$("#export_div").hide();
					$("#export_a").click(function() { $("#export_div").toggle(); });
					$("#import_div").hide();
					$("#import_a").click(function() { $("#import_div").toggle(); });
					$("#open_div").hide();
					$("#open_a").click(function() { $("#open_div").toggle(); });
					$("#delete_div").hide();
					$("#delete_a").click(function() { $("#delete_div").toggle(); });
					$("#code_div").hide();
					$("#code_a").click(function() { $("#code_div").toggle(); });
					$("#add_div").hide();
					$("#add_a").click(function() { $("#add_div").toggle(); });
					$("#logout").click(function() {
						deleteCookie("admin");
						location.href="index.php";
					});
					/*
					 * Export
					 */
					 
					var year = $("#year");
					var now = new Date();	
					for(var i = 0; i < 5; i++) {
						year.append("<option value=\"" + (now.getFullYear() - i) + "\">" + (now.getFullYear() - i) + "</option>");
					}
					$("#export").click(function() {
						location.href = "?json=export&month=" + $("#month").val() + "&year=" + $("#year").val();
					});
					
					/*
					 * Public device
					 */
					
					var open = $("#open");
					if(getCookie("open")) 
						open.prop({"checked" : true});
					open.click(function() {
						if(open.prop("checked")) {
							setCookie("open", true, 365);
						}
						else {
							deleteCookie("open");	
						}
					});
					
					/*
					 * Import
					 */
					 
					$("#import").click(function () {
						console.log("click");
						var form = new FormData($("#form")[0]);
						function progress(e) {
							if(e.lengthComputable) {
								var percent = parseInt((e.loaded/e.total)*80) +"%";
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
								$("#bar").css({
									width: "100%"
								}).html("100%");
								console.log("Done");				
								var obj = JSON.parse(html);
								$("#report")
									.append($("<tr></tr>").append("<td></td>").append("<td>Anzahl</td>"))
									.append($("<tr></tr>").append("<td>Datensätze</td>").append("<td>" + obj.total + "</td>"))
									.append($("<tr></tr>").append("<td>Eingefügt</td>").append("<td>" + obj.inserted + "</td>"))
									.append($("<tr></tr>").append("<td>Übersprungen</td>").append("<td>" + obj.skipped + "</td>"))
									.append($("<tr></tr>").append("<td>Ungültig</td>").append("<td>" + obj.invalid + "</td>"));
								var str = "";
								for(var i in obj.invalids) {
									str += "\"" + obj.invalids[i] + "\", "
								}
								if(str == "") str = "Keine"
								$("#invalid").html("Folgende zeilen waren ungültig: " + str);
							}
						});
					});
				</script>
			<?php
			}
			else {
				?>
					<p>Bitte geben Sie zuerst das Masterpassword ein, um sich auf diesem Gerät in dieser Session als Administrator anzumelden. Bitte vermeiden Sie es, sich an einem öffentlich zugänglichen Gerät als Administrator anzumelden um Sicherheitsproblemen vorzubeugen.</p>
					<input name="password" type="password" />
					<button>Anmelden</button>
					<script type="text/javascript">
						$("button").click(function() {
							setCookie("admin", $("input[name='password']").val());
							location.reload();
						});
					</script>
				<?php
			}
		}
	}
?>
