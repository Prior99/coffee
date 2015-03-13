<?php
	/*
	 * This content represents the admin-area
	 */
	class ContentAdmin extends Content
	{
		//See content.php for documentation of printTitle(), printHTML() and printHelp()
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
			//Check whether the user is really an administrator. Therefor check if the cookie is valid
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) {
			?>
				<p>Einstellungen, die Sie vornehmen werden sofort automatisch per AJAJ-Request vorgenommen. Sie müssen nicht auf einen "Speichern"-Button klicken o.Ä.
				Bitte achten Sie entsprechend auf Ihre Änderungen, da diese also unwiederruflich sofort durchgeführt werden.</p>
				<!-- Export statitics to CSV -->
				<a href="#" id="export_a"><h3>Exportieren</h3></a>
				<div id="export_div">
					<p>Sie können sich für jeden Monat eine Liste des Getränkeverbrauchs aller Benutzer als mit Semikolon getrennte CSV-Datei (Microsoft Excel) exportieren.</p>
					Vom:
					<p>
						<input style="width: 50px;" type="text" id="startday" value="1">.
						<input style="width: 50px;" type="text" id="startmonth" value="1">.
						<input style="width: 100px;" type="text" id="startyear" value="1970"> um
						<input style="width: 50px;" type="text" id="starthour" value="12"> :
						<input style="width: 50px;" type="text" id="startminute" value="00">
					</p>
					Bis zum:
					<p>
						<input style="width: 50px;" type="text" id="endday" value="31">.
						<input style="width: 50px;" type="text" id="endmonth" value="12">.
						<input style="width: 100px;" type="text" id="endyear" value="2130"> um
						<input style="width: 50px;" type="text" id="endhour" value="12"> :
						<input style="width: 50px;" type="text" id="endminute" value="00">
					</p>

					<button id="export">Exportieren</button>
				</div>
				<script type="text/javascript">
					var now = new Date();
					$("#endday").val(now.getDate());
					$("#endyear").val(now.getFullYear());
					$("#endmonth").val(now.getMonth() + 1);
					$("#endhour").val(now.getHours());
					$("#endminute").val(now.getMinutes());
					/*
					 * Export
					 */
					$("#export").click(function() { //If the corresponding button is pressed, call the API which will do the remaining work
						location.href = "?json=export&startmonth=" + $("#startmonth").val() + "&startyear=" + $("#startyear").val() + "&startday=" + $("#startday").val()
							+ "&endmonth=" + $("#endmonth").val() + "&endyear=" + $("#endyear").val() + "&endday=" + $("#endday").val()
							+ "&starthour=" + $("#starthour").val() + "&startminute=" + $("#startminute").val()+ "&endhour=" + $("#endhour").val() + "&endminute=" + $("#endminute").val();
					});
				</script>
				<!-- Import users from CSV -->
				<a href="#" id="import_a"><h3>Importieren</h3></a>
				<div id="import_div">
					<p>Sie können eine Liste mit Mitarbeitern aus einer CSV-Datei Importieren.
						Die Datei muss mit Semikolon getrennt sein (Microsoft Excel) und exakt 4 Spalten besitzen.
						Die erste Spalte enthält den Nach-, die zweite Spalte den Vornamen. Danach folgen Kürzel und E-Mail.
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
				<script type="text/javascript">
					/*
					 * Import
					 *
					 * Okay, read carefully as this one is a dirty one
					 * The problem and reason why this is so complex is that we need to fo a AJAX-Request
					 * Using an uploaded file which is not that trivial at all!
					 */

					$("#import").click(function () { //When the Import-Button is clicked
						//console.log("click");
						/*
						 * Create a new native FormData Element as jQuery
						 * Does not feature uploads. This is the reason why we have to convert between jQuery and native all the time#
						 * which makes it not more readable
						 */
						var form = new FormData($("#form")[0]);
						/*
						 * This method displays a cool progress bar while the upload and computing is done (How cool is that shit!?)
						 */
						function progress(e) {
							if(e.lengthComputable) { //Only if it is computable (Sorry, my dear IE-users...)
								var percent = parseInt((e.loaded/e.total)*80) +"%"; //Calculate the relative progress as percent
								$("#bar").css({
									width: percent
								}).html(percent); //Create the bar with css
							}
							//console.log(e); //Yeah nasty debugoutput!
						}
						//Here commes the FAAT Ajax-Request (Well, actually it is AJAJ, but who cares?)
						$.ajax({
							url: "?json=import",
							type: "POST", //To enable sending data as content
							xhr: function() { //Only if XML-HTTP-Rrequest is available Handle the progressbar (Sorry, IE-users...)
								var xhr = $.ajaxSettings.xhr();
								if(xhr.upload) {
									xhr.upload.addEventListener("progress", progress, false);
								}
								return xhr;
							},
							/*
							 * DO!NOT!CACHE! Goddamned!
							 * Who caches uploaded files after all WITHOUT CHECKING THE CHECKSUM?
							 * DO YOU KNOW HOW LONG IT TOOK ME TO FIGURE OUT JS DOES THIS!?
							 */
							cache: false,
							contentType: false, //No specific contenttype (To prevent jQuery from fucking up our CSV)
							data: form, //Data comes from the previously created FromData element
							processData: false, //Do not do anything with the data, just send it!
							success : function(html) { //And it is finally done
								$("#bar").css({ //For any browser that did not allow XHR at least set it to 100% now
									width: "100%"
								}).html("100%");
								//console.log("Done"); //Yeah, debug this nasty code!
								var obj = JSON.parse(html); //Parse the answer to display it
								$("#report") //Easy
									.append($("<tr></tr>").append("<td></td>").append("<td>Anzahl</td>"))
									.append($("<tr></tr>").append("<td>Datensätze</td>").append("<td>" + obj.total + "</td>"))
									.append($("<tr></tr>").append("<td>Eingefügt</td>").append("<td>" + obj.inserted + "</td>"))
									.append($("<tr></tr>").append("<td>Übersprungen</td>").append("<td>" + obj.skipped + "</td>"))
									.append($("<tr></tr>").append("<td>Ungültig</td>").append("<td>" + obj.invalid + "</td>"));
								var str = "";
								for(var i in obj.invalids) { //Echo all lines that could not be parsed
									str += "\"" + obj.invalids[i] + "\", "
								}
								if(str == "") str = "Keine"
								$("#invalid").html("Folgende zeilen waren ungültig: " + str);
							}
						});
					});
				</script>
				<!-- hc -->
				<a href="#" id="hc_a"><h3>High contrast</h3></a>
				<div id="hc_div">
					<p>Aktiviert das high-contrast theme für dieses Gerät.</p>
					<p><input type="checkbox" id="hc"/> High contrast theme</p>
				</div>
				<script type="text/javascript">
				var hc = $("#hc"); //The HTML-Element of the checkbox
				if(getCookie("hc")) { //Look if the devices is already public or not
					hc.prop({"checked" : true}); //And modify the value
				}
				hc.click(function() {
					if(hc.prop("checked")) { //Set the cookie if the user checked it
						setCookie("hc", true, 365);
					}
					else {
						deleteCookie("hc"); //or delete it if he unchecked it
					}
				});
				</script>
				<!-- osk -->
				<a href="#" id="osk_a"><h3>On screen keyboard</h3></a>
				<div id="osk_div">
					<p>Aktiviert das onscreenkeyboard für Geräte ohne Tastatur oder eigene OSK.</p>
						<p><input type="checkbox" id="osk"/> OSK aktivieren</p>
					</div>
					<script type="text/javascript">
						var osk = $("#osk"); //The HTML-Element of the checkbox
						if(getCookie("osk")) { //Look if the devices is already public or not
							osk.prop({"checked" : true}); //And modify the value
						}
						osk.click(function() {
							if(osk.prop("checked")) { //Set the cookie if the user checked it
								setCookie("osk", true, 365);
							}
							else {
								deleteCookie("osk"); //or delete it if he unchecked it
							}
						});
					</script>
					<!-- Set if this device is public or not (autologout and stuff depends on this) -->
					<a href="#" id="open_a"><h3>Öffentliches Gerät</h3></a>
					<div id="open_div">
						<p>In öffentlichen Geräten werden Benutzer automatisch nach kurzer Zeit der Inaktivität oder einem Kauf abgemeldet.
							Auf privaten Geräten führt ein Aufrufen der Wurzelseite nicht zur Benutzerliste sondern direkt zum Kaufen.</p>
							<p><input type="checkbox" id="open"/> Dies ist ein öffentliches Gerät</p>
						</div>
						<script type="text/javascript">
						/*
						* Public device
						*
						* The whole thing works device-based.
						* Either the cookie is set and autologout occurs or it is not set and the user stays logged in
						*/
						var open = $("#open"); //The HTML-Element of the checkbox
						if(getCookie("open")) { //Look if the devices is already public or not
							open.prop({"checked" : true}); //And modify the value
						}
						open.click(function() {
							if(open.prop("checked")) { //Set the cookie if the user checked it
								setCookie("open", true, 365);
							}
							else {
								deleteCookie("open"); //or delete it if he unchecked it
							}
						});
						</script>
				<!-- Delete user -->
				<a href="#" id="delete_a"><h3>Benutzer löschen</h3></a>
				<div id="delete_div">
					<p>Bitte beachten Sie, dass der Benutzer unwiederruflich gelöscht wird.</p>
					<p><label>Kürzel:</label><input name="delete_short" type="text" /></p>
					<p><button id="delete_perform">Okay!</button><span id="delete_response"></span></p>
				</div>
				<script type="text/javascript">
					/*
					 * Delete User
					 */
					$("#delete_perform").click(function() {
						var short = $("input[name='delete_short']").val(); //Name-shortage of user
						$.ajax({
							url : "?json=delete&short=" + short //Call to API
						}).done(function(html) {
							var response = JSON.parse(html); //Parse the response
							if(response.okay) { //Display the answer to the user
								$("#delete_response").html("Benutzer gelöscht!");
							}
							else {
								$("#delete_response").html("Benutzer nicht vorhanden oder kann nicht gelöscht werden.");
							}
							setTimeout(function() { //Display the answer only 2 seconds
								$("#delete_response").html("");
							}, 2000);
						});
					});
				</script>
				<!-- Reset code -->
				<a href="#" id="code_a"><h3>Code Zurücksetzen</h3></a>
				<div id="code_div">
					<p>Hat ein Benutzer seinen 3-Stelligen Code vergessen, können Sie hier den Code für diesen Benutzer entfernen.</p>
					<p><label>Kürzel:</label><input name="code_short" type="text" /></p>
					<p><button id="code_perform">Okay!</button><span id="code_response"></span></p>
				</div>
				<script type="text/javascript">
					/*
					 * Reset code
					 */
					$("#code_perform").click(function() {
						var short = $("input[name='code_short']").val();//Name-shortage of user
						$.ajax({
							url : "?json=codereset&short=" + short //Call the API
						}).done(function(html) {
							var response = JSON.parse(html);
							if(response.okay) { //Display response...
								$("#code_response").html("Code zurückgesetzt!");
							}
							else {
								$("#code_response").html("Benutzer nicht vorhanden.");
							}
							setTimeout(function() { //...but only 2 seconds
								$("#code_response").html("");
							}, 2000);
						});
					});
				</script>
				<!-- Add user -->
				<a href="#" id="add_a"><h3>Benutzer ergänzen</h3></a>
				<div id="add_div">
					<p>Hier können Sie ohne großen Aufwand einen einzelnen Benutzer hinzufügen.</p>
					<p><label>E-Mail:</label><input name="add_mail" type="text" /></p>
					<p><label>Kürzel:</label><input name="add_short" type="text" /></p>
					<p><label>Vorname:</label><input name="add_firstname" type="text" /></p>
					<p><label>Nachname:</label><input name="add_lastname" type="text" /></p>
					<p><button id="add_perform">Okay!</button><span id="add_response"></span></p>
				</div>
				<script type="text/javascript">
					/*
					 * Add user
					 */
					$("#add_perform").click(function() {
						var first, last, short, mail; //Get all entered values from the inputfields...
						first = $("input[name='add_firstname']").val();
						short = $("input[name='add_short']").val();
						last =  $("input[name='add_lastname']").val();
						mail =  $("input[name='add_mail']").val();
						$.ajax({ //...And send them to the API...
							url : "?json=add&firstname=" + first + "&lastname=" + last + "&short=" + short + "&mail=" + mail
						}).done(function(html) {
							var response = JSON.parse(html);//...display the response...
							if(response.okay) {
								$("#add_response").html("Benutzer ergänzt.");
							}
							else {
								$("#add_response").html("Ein Fehler ist aufgetreten.");
							}
							setTimeout(function() {//...but only 2 seconds and we are done
								$("#add_response").html("");
							}, 2000);
						});
					});
				</script>
				<!-- Add product -->
				<a href="#" id="product_add_a"><h3>Produkt ergänzen</h3></a>
				<div id="product_add_div">
					<p>Geben Sie den Namen des Produktes ein, das Sie verfügbar machen wollen:</p>
					<p><label>Produktname:</label><input name="product_add_name" type="text" /></p>
					<p><label>Preis:</label><input name="product_add_price" type="text" /></p>
					<p><button id="product_add_perform">Okay!</button><span id="product_add_response"></span></p>
				</div>
				<script type="text/javascript">
					/*
					 * Add product
					 */
					$("#product_add_perform").click(function() { //If you have read the previous <script>-tages, you should already be familiar with this.
						var name = $("input[name='product_add_name']").val(); //Gather the entered values...
						var price = $("input[name='product_add_price']").val().replace(",", ".");
						price = parseInt(parseFloat(price) * 100);
						$.ajax({
							url : "?json=product_add&name=" + name + "&price=" + price //...send to API...
						}).done(function(html) {
							var response = JSON.parse(html); //...display...
							if(response.okay) {
								$("#product_add_response").html("Produkt ergänzt.");
							}
							else {
								$("#product_add_response").html("Ein Fehler ist aufgetreten.");
							}
							setTimeout(function() {//..clear display after 2 seconds and done.
								$("#product_add_response").html("");
							}, 2000);
						});
					});
				</script>
				<!-- Delete product -->
				<a href="#" id="product_delete_a"><h3>Produkt löschen</h3></a>
				<div id="product_delete_div">
					<p>Bitte beachten Sie, dass das Produkt in den Monaten in denen es noch verkauft wurde weiterhin angezeigt wird. Es wird nicht mehr möglich sein, dieses Produkt zukünftig zu kaufen.</p>
					<p><label>Produktname:</label><input name="product_delete_name" type="text" /></p>
					<p><button id="product_delete_perform">Okay!</button><span id="product_delete_response"></span></p>
				</div>
				<script type="text/javascript">
					/*
					 * Delete product
					 */
					//Okay I am not going to explain this all over again. Just check on of the previous 4 <script>-tags' documentation
					//It's the same but the fields are named differently!
					$("#product_delete_perform").click(function() {
						var name = $("input[name='product_delete_name']").val();
						$.ajax({
							url : "?json=product_delete&name=" + name
						}).done(function(html) {
							var response = JSON.parse(html);
							if(response.okay) {
								$("#product_delete_response").html("Produkt gelöscht.");
							}
							else {
								$("#product_delete_response").html("Ein Fehler ist aufgetreten.");
							}
							setTimeout(function() {
								$("#product_delete_response").html("");
							}, 2000);
						});
					});
				</script>
				<!-- unlock users -->
				<a href="#" id="unlock_a"><h3>Benutzer entsperren</h3></a>
				<div id="unlock_div">
					<ul></ul>
				</div>
				<script type="text/javascript">
					/*
					 * Retrieve List of locked users
					 */
					(function reloadLocked() {
						$.ajax({
							url : "?json=get_locked"
						}).done(function(res) {
							var list = JSON.parse(res);
							var table = $("#unlock_div").find("ul");
							table.html("");
							for(var i = 0; i < list.length; i++) {
								(function(user) {
									$("<a href='#'>" + user.firstname + " " + user.lastname + " (" + user.short + ")</a>").appendTo($("<li></li>").appendTo(table)).click(function() {
										$.ajax({
											url : "?json=unlock&short=" + user.short
										}).done(function() {
											reloadLocked();
										});
									});
								})(list[i]);
							}
						});
					})();
				</script>
				<!-- backups -->
				<a href="#" id="backups_a"><h3>Backups</h3></a>
				<div id="backups_div">
					<ul></ul>
					<form enctype="multipart/form-data" id="backupupload" style="float: left;">
						<input type="file" name="file" />
					</form>
					<button id="backupuploadbutton" style="float: left;">Laden</button>
					<br style="clear: both;"/>
					<div id="backupupload_success"></div>
					<br />
				</div>
				<script type="text/javascript">
					$("#backupuploadbutton").click(function () { //When the Import-Button is clicked
						var form = new FormData($("#backupupload")[0]);
						$.ajax({
							url: "?json=backup_load",
							type: "POST", //To enable sending data as content
							xhr: function() { //Only if XML-HTTP-Rrequest is available Handle the progressbar (Sorry, IE-users...)
								var xhr = $.ajaxSettings.xhr();
								return xhr;
							},
							cache: false,
							contentType: false, //No specific contenttype (To prevent jQuery from fucking up our CSV)
							data: form, //Data comes from the previously created FromData element
							processData: false, //Do not do anything with the data, just send it!
							success : function(html) { //And it is finally done
								$("#backupupload_success").html("Upload done!");
							}
						});
					});
					/*
					 * Retrieve List of backups
					 */
					(function reloadLocked() {
						$.ajax({
							url : "?json=get_backups"
						}).done(function(res) {
							var list = JSON.parse(res);
							var table = $("#backups_div").find("ul");
							table.html("");
							for(var i = 0; i < list.length; i++) {
								(function(backup) {
									var d = new Date(backup.created*1000);
									$("<a href='?json=download_backup&id=" + backup.id + "'>" + d.toString() + "</a>").appendTo($("<li></li>").appendTo(table));
								})(list[i]);
							}
						});
					})();
				</script>
				<!-- logout as admin -->
				<a href="#" id="logout">Als Admin abmelden</a>
				<script type="text/javascript">
					/*
					 * Logout
					 */
					$("#logout").click(function() {
						deleteCookie("admin"); //Delete cookie
						location.href="index.php"; //And redirect to normal index.php
					});
				</script>
				<script type="text/javascript">
					//Hide all the divs to get this page more structured
					$("#export_div").hide();
					$("#export_a").click(function() { $("#export_div").toggle(); });
					$("#import_div").hide();
					$("#import_a").click(function() { $("#import_div").toggle(); });
					$("#open_div").hide();
					$("#open_a").click(function() { $("#open_div").toggle(); });
					$("#osk_div").hide();
					$("#osk_a").click(function() { $("#osk_div").toggle(); });
					$("#hc_div").hide();
					$("#hc_a").click(function() { $("#hc_div").toggle(); });
					$("#delete_div").hide();
					$("#delete_a").click(function() { $("#delete_div").toggle(); });
					$("#code_div").hide();
					$("#code_a").click(function() { $("#code_div").toggle(); });
					$("#add_div").hide();
					$("#add_a").click(function() { $("#add_div").toggle(); });
					$("#unlock_div").hide();
					$("#unlock_a").click(function() { $("#unlock_div").toggle(); });
					$("#product_add_div").hide();
					$("#product_add_a").click(function() { $("#product_add_div").toggle(); });
					$("#product_delete_div").hide();
					$("#product_delete_a").click(function() { $("#product_delete_div").toggle(); });
					$("#backups_div").hide();
					$("#backups_a").click(function() { $("#backups_div").toggle(); });
				</script>
			<?php
			}
			else { //This is displayed if the admins' login was not valid (Ask him to authenticate)
				?>
					<p>Bitte geben Sie zuerst das Masterpassword ein, um sich auf diesem Gerät in dieser Session als Administrator anzumelden. Bitte vermeiden Sie es, sich an einem öffentlich zugänglichen Gerät als Administrator anzumelden um Sicherheitsproblemen vorzubeugen.</p>
					<input name="password" type="password" />
					<button>Anmelden</button>
					<br />
					<br />
					<a href="index.php">Zurück</a>
					<script type="text/javascript">
						function login(){
							//Set the cookie. The value is not validated as the page will do this on the php-site on the next reload
							setCookie("admin", $("input[name='password']").val());
							location.reload(); //Reload the page
						}
						$("input[name='password']").keyup(function(e) {
							if(e.which == 13) { //Fake a submit-on-enter in javascript
								login();
							}
						});
						$("button").click(login); //submit-on-click
					</script>
				<?php
			}
		}
	}
?>
