<?php
	class ContentBuy extends Content
	{
		public function printTitle() {
			echo("Kaufen");
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
			if(!$this->coffee->checkPassword()) {
				?>
					<h1>Zugang nicht möglich</h1>
					<p>Die Benutzerauthentifizierung ist fehlgeschlagen. Bitte loggen Sie sich erneut ein.</p>
				<?php
				setcookie("user", null, -3600, "/");
				setcookie("code", null, -3600, "/");
				unset($_COOKIE["user"]);
				unset($_COOKIE["code"]);
			}
			else {
				?>
					<div id="saldo"></div>
					<div id="products"></div>
					<div style="text-align: center;">
						<a href="#" class="button" id="buy">Kaufen</a>
						<a href="#" class="button" id="logout">Abmelden</a>
					</div>
					<script type="text/javascript">
						var _refreshTimeout;
						function refreshTimeout() {
							if(getCookie("open")) {
								clearTimeout(_refreshTimeout);
								_refreshTimeout = setTimeout(function() {
									deleteCookie("user");
									deleteCookie("code");
									location.href = "index.php";
								}, 2 * 60* 1000);
							}
						}
						refreshTimeout();
						function refreshSaldo() {
							$.ajax({
								url: "?json=saldo"
							}).done(function(res) {
								var obj = JSON.parse(res);
								$("#saldo").html("Ausstehend: " +obj.sum.toFixed(2) + "€")
							});
						}
						refreshSaldo();
						$.ajax({
						url : "?json=products"
						}).done(function(res) {
							function updateCounter(counter, real, pending) {
								if(pending > 0)
									counter.html(real + " <span style='font-size:16pt;'>+" + pending + "</span> ");
								else
									counter.html(real);
							}
							var result = JSON.parse(res);
							var products = $("#products");
							for(var i in result) {
								(function() {
									var count = 0;
									var product = result[i];
									var counter = $("<div style='float:right;'>" + product.amount + "</div>");
									product.div = counter;
									if(localStorage[product.name] !== undefined)  {
										product.bought = localStorage[product.name];
										updateCounter(counter, product.amount, product.bought);
									}
									var pressTime = 0;
									var timeout;
									function down() {
										refreshTimeout();
										pressTime = new Date().getTime();
										timeout = setTimeout(function() {
											if(product.bought !== undefined && product.bought > 0) {
												product.bought--;
												localStorage[product.name] = product.bought;
												updateCounter(counter, product.amount, product.bought);
											}
										}, 700);
									}
									function up() {
										refreshTimeout();
										var time = new Date().getTime() - pressTime;
										if(time < 700) {
											clearTimeout(timeout);
											if(product.bought === undefined) {
												product.bought = 0;
											}
											product.bought++;
											localStorage[product.name] = product.bought;
											updateCounter(counter, product.amount, product.bought);
											pressTime = 0;
											console.log(result);
										}
									}
									$("<a class='product'></a>")
										.attr('unselectable', 'on').css('user-select', 'none').on('selectstart', false)
										.appendTo(products).append(counter).append($("<div>" + product.price.toFixed(2) + "€ " + product.name + "</div>"))
										.on("touchstart", function(e) {
											down();
											e.stopPropagation();
											e.preventDefault();
										})
										.on("touchend", function(e) {
											up();
											e.stopPropagation();
											e.preventDefault();
										})
										.on("mousedown", down)
										.on("mouseup", up);
								})(i);
							}
							$("#logout").click(function() {
								for(var i in localStorage) {
									localStorage.removeItem(i);
								}
								deleteCookie("user");
								deleteCookie("code");
								location.href = "index.php";
							});
							$("#buy").click(function(e) {
								e.preventDefault();
								refreshTimeout();
								var objs = [];
								for(var i in result) {
									var product = result[i];
									if(product.bought !== undefined && product.bought > 0) {
										var obj = {};
										obj.bought = product.bought;
										obj.id = product.id;
										obj.name = product.name;
										objs.push(obj);
									}
								}
								$.ajax({
									url:"?json=buy&info=" + JSON.stringify(objs)
								}).done(function() {
									for(var i in result) {
										var p = result[i];
										if(p.bought !== undefined && p.bought > 0) {
											updateCounter(p.div, p.amount += parseInt(p.bought), p.bought = 0);
											localStorage[p.name] = 0;
											refreshSaldo();
										}
									}
								});
								console.log(objs);
							});
						});
					</script>
				<?php
			}
		}
	}
?>
