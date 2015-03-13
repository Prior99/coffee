<?php
	/*
	 * This page is displayed when the user wants to buy something.
	 * It displays a list of all products, their prices, the amount the user already bought them and so on.
	 */
	class ContentBuy extends Content
	{
		//See content.php for documentation of printTitle(), printHTML() and printHelp()
		public function printTitle() {
			?>
				<div id="saldo"></div>
			<?php
		}

		public function printHelp() {
			?>
				<p>Auf dieser Seite können Sie "Striche" für Ihre konsumierten Getränke machen.</p>
				<p>Tippen Sie auf ein Getränk um die Zahl an "Strichen", die gemacht werden soll zu erhöhen.</p>
				<p>Drücken Sie lange auf ein getränk, um einen Strich wieder rückgängig zu machen.</p>
				<p>Erst wenn sie auf "Kaufen" tippen, werden Ihre Änderungen unwiederruflich gespeichert.</p>
				<p>Sie können sich jederzeit von hier aus abmelden und zur Benutzerauswahl zurückkehren.</p>
			<?php
		}

		public function printHTML() {
			if(!$this->coffee->checkPassword()) {//If the user did nor authenticate correctly, display him a message
				?>
					<h1>Zugang nicht möglich</h1>
					<p>Die Benutzerauthentifizierung ist fehlgeschlagen. Bitte loggen Sie sich erneut ein.</p>
					<a href="index.php">Zurück</a>
				<?php
				//Also delete everything he entered
				setcookie("user", null, -3600, "/"); //Delete the cookies in HTTP
				setcookie("code", null, -3600, "/");
				unset($_COOKIE["user"]); //Delete them for the current session
				unset($_COOKIE["code"]);
			}
			else { //If he did login correctly, display him the page.
				?>
					<div class="buttonswrapper">
						<div class="awrapper">
							<a href="#" class="button buy">Kaufen</a>
						</div>
						<div class="awrapper">
							<a href="#" class="button logout">Abmelden</a>
						</div>
					</div>
					<div id="products"></div>
					<div class="buttonswrapper">
						<div class="awrapper">
							<a href="#" class="button buy">Kaufen</a>
						</div>
						<div class="awrapper">
							<a href="#" class="button logout">Abmelden</a>
						</div>
					</div>
					<script type="text/javascript">
						/*
						 * This is actually pretty basic and simple javascript
						 * it just looks a bit scary as it is so long.
						 */
						/*
						 * Will log the user out
						 */
						function doLogout() {
							for(var i in localStorage) { //Delete all products the user wants to buy
								localStorage.removeItem(i);
							}
							deleteCookie("user"); //Delete both cookies from HTTP
							deleteCookie("code");
							location.href = "index.php"; //And redirect to startpage
						}
						/*
						 * This timeout is for auto-logoff
						 */
						var _refreshTimeout;
						/*
						 * This method is called everytime the user did something
						 * It resets the timeout. So when the user did nothing in 2 minutes
						 * The autologoff will occure, else it will be reset until the page is reloaded
						 * or the user once did nothing.
						 */
						function refreshTimeout() {
							if(getCookie("open")) { //Only if this a public device, else the user will stay logged in
								clearTimeout(_refreshTimeout); //Clear the timeout then
								_refreshTimeout = setTimeout(function() {
									doLogout();
								}, 2 * 60* 1000);
							}
						}
						refreshTimeout(); //Kick of the timeouting initially
						/*
						 * Refreshes the saldo displayd on top of the page
						 */
						function refreshSaldo() {
							$.ajax({
								url: "?json=saldo" //Ask the API
							}).done(function(res) {
								var obj = JSON.parse(res);
								$("#saldo").html("Konto:" + (obj.sum / 100).toFixed(2) + "€"); //Update the div
							});
						}
						refreshSaldo();//Initially refresh the saldo
						//Now download a list of all products, prices and the amount they were alredy bought
						$.ajax({
							url : "?json=products" //Call the API
						}).done(function(res) {
							/*
							 * This method refreshes the counter for one products element
							 * Explanation:
							 *
							 * The small number on the right side of the amount of bought products that
							 * increases everytime you tap the product and is reset when "Kaufen" is tapped
							 * will be updated by this method.
							 * Its a bit confusing, I know.
							 * maybe function updateTheCounterOnTheRightOfEveryProductWhereTemporaryAmountIsDisplayed() would
							 * have been a more speaking name.
							 */
							function updateCounter(counter, real, pending) { //Parameters:
								// 1. jQuery-HTML-Element to be written to,
								// 2. Amount the user has alredy bought,
								// 3. Amount of products he intends to buy
								if(pending > 0)
									counter.html(real + " <span style='font-size:16pt;'>+" + pending + "</span> ");
								else
									counter.html(real);
							}
							var result = JSON.parse(res); //Parse the result from the API-Call
							var products = $("#products"); //Get a HTML-Selector to the div to display the list in
							for(var i in result) {//Iterate over every product
								(function(i) { //Scope out to allow asynchroneous task being done in here!
									var count = 0; //Count for this product
									var product = result[i]; //The product
									//The div to display the amount of bought products and products intent to be bought
									var counter = $("<div style='float:right;'>" + product.amount + "</div>");
									product.div = counter;//Save this div inside the products object
									//If we have cached products to buy from an earlier session...
									if(localStorage[product.name] !== undefined)  {
										product.bought = localStorage[product.name];//...restore them now
										updateCounter(counter, product.amount, product.bought);//And update the counter
									}
									var pressTime = 0;//Measure how long the user pressed the button in order to emulate a longpress
									var timeout; //Timeout used to emulate the longpress
									/*
									 * Will be called when the user lowers the finger/mouse on a product
									 */
									function down() {
										refreshTimeout();//The user did something, so refresh the logout-timeout
										pressTime = new Date().getTime();//Start measuring how long the button was pressed
										timeout = setTimeout(function() { //Set a timeout to 0,7 sek.
											//If the button was not released in the meantime, it was a longpress
											if(product.bought !== undefined && product.bought > 0) { //If there was something bought at all
												product.bought--; //Decrease
												localStorage[product.name] = product.bought; //Update storage
												updateCounter(counter, product.amount, product.bought); //Update the div
											}
										}, 700);
									}
									/*
									 * Will be called when the user raises the finger/mouse on a product
									 */
									function up() {
										refreshTimeout(); //The user did something
										var time = new Date().getTime() - pressTime; //Measure how long the button was pressed
										if(time < 700) { //If it was less than 0,7sek it was a shortpress
											clearTimeout(timeout);//Clear the timeout that would be invoked at a longpress
											if(product.bought === undefined) { //If nothing was bought by now, product.bought will be undefined
												product.bought = 0; //But it need to be integer 0
											}
											product.bought++;//Increase
											localStorage[product.name] = product.bought; //Update storage
											updateCounter(counter, product.amount, product.bought); //Update div
											pressTime = 0; //Reset timemeasurement
											//console.log(result); //Nasty Debugprinting
										}
									}
									$("<a class='product'></a>") //Some css hacks and touch/mouse-handlers
										//To prevent ugly selectmarks on iOS and Android
										.attr('unselectable', 'on').css('user-select', 'none').on('selectstart', false)
										//Create the div and display the price
										.appendTo(products).append(counter).append($("<div>" + (product.price / 100).toFixed(2) + "€ " + product.name + "</div>"))
										//For touch-enabled devices
										.on("touchstart", function(e) {
											down();
											//Opera Mobile and Dolphin need this as they will call
											//"mousedown" otherwise and then everything will happen twice.
											e.stopPropagation();
											//IE and Firefox Mobile need this. It does not work if I do not do this.
											//I don't know why, but it's not working without.
											//(Events happen twice or not at all, I think somethings screwed up with elements that have both touch- and mousehandler)
											e.preventDefault();
										})
										.on("touchend", function(e) {
											up();
											//Opera Mobile and Dolphin need this as they will call
											//"mouseup" otherwise and then everything will happen twice.
											e.stopPropagation();
											//IE and Firefox Mobile need this. It does not work if I do not do this.
											//I don't know why, but it's not working without.
											//(Events happen twice or not at all, I think somethings screwed up with elements that have both touch- and mousehandler)
											e.preventDefault();
										})
										//For mouse devices
										.on("mousedown", down)
										.on("mouseup", up);
								})(i);
							}
							$(".logout").click(function() {//If the user clicked "Abmelden", log them out (obvious, isn't it?)
								doLogout();
							});
							$(".buy").click(function(e) { //Here comes the real stuff!
								var loading = displayPopup("Bitte warten", "Ihr Kaffee wird gebucht...");
								//First of all, stop IE and FF Mobile from screwing up!
								e.preventDefault();
								refreshTimeout(); //The user did something
								var objs = []; //Instance a new array to store all products in that were bought
								for(var i in result) { //Remeber, result was the array of all products read from the API-Call all above :D
									var product = result[i];//The product we are now viewing
									if(product.bought !== undefined && product.bought > 0) {//If the product was bought at least once
										var obj = {}; //Create a new, clean object we can send to the API (without any HTML-Elements or other stuff we attached to it)
										obj.bought = product.bought; //Copy only the relevant stuff
										obj.id = product.id;
										obj.name = product.name; //Yes, we also copy the name of the product, because the API wants to know the name in order to send
										//It per mail. I know, this is a bit lazy, I could also have read it from the database by id but as I am a lazy guy...
										//TODO: Maybe do what is explained in comment above
										objs.push(obj);
									}
								}
								$.ajax({
									url:"?json=buy&info=" + JSON.stringify(objs) //Call the API with previously generated array of products
								}).done(function() {
									for(var i in result) {//Remember, result was the array of all products read from the API-Call all above :D
										var p = result[i];
										if(p.bought !== undefined && p.bought > 0) { //Only if this product was bought at all
											updateCounter(p.div, p.amount += parseInt(p.bought), p.bought = 0); //Update the counter
											localStorage[p.name] = 0; //Update the local storage
											refreshSaldo(); //Refresh the saldo
										}
									}
									loading.remove();
									var success = displayPopup("Kauf erfolgreich", "Vielen Dank! Bitte kaufen Sie bald wieder einen Kaffee.");
									setTimeout(function() {
										success.remove();
										if (getCookie("open")) {
											doLogout();
										}
									}, 3000);
								});
								//console.log(objs); //Nasty debugoutput
							});
						});
					</script>
				<?php
			}
		}
	}
?>
