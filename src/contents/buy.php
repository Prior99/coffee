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
					<h1>Ähem!</h1>
					<p>Sie sollten nicht hier sein oder? Dürfte ich mal Ihren Ausweis sehen? </p>
				<?php
				setcookie("user", null, -3600, "/");
				setcookie("code", null, -3600, "/");
				unset($_COOKIE["user"]);
				unset($_COOKIE["code"]);
			}
			else {
				?>
					<div id="products"></div>
					<div style="text-align: center;">
						<a href="#" id="logout">Abmelden</a> | 
						<a href="#" id="buy">Kaufen</a>
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
									var pressTime = 0;
									var timeout;
									function down() {
										refreshTimeout();
										pressTime = new Date().getTime();
										timeout = setTimeout(function() {
											if(product.bought !== undefined && product.bought > 0) {
												product.bought--;
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
											updateCounter(counter, product.amount, product.bought);
											pressTime = 0;
										}
									}
									$("<a class='product'></a>")
										.attr('unselectable', 'on').css('user-select', 'none').on('selectstart', false)
										.appendTo(products).append(counter).append($("<div>" + product.name + "</div>")
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
										.on("mouseup", up)
									);
								})(i);
							}
							$("#logout").click(function() {
								deleteCookie("user");
								deleteCookie("code");
								location.href = "index.php";
							});
							$("#buy").click(function() {
								refreshTimeout();
								for(var i in result) {
									var product = result[i];
									if(product.bought !== undefined && product.bought > 0) {
										(function(p) {
											var f = function() {
												$.ajax({
													url:"?json=buy&product=" + p.id
												}).done(function() {
													updateCounter(p.div, ++p.amount, --p.bought);
													if(p.bought > 0)
														f();
												});
											};
											f();
										})(product);
									}
								}
							});
						});
					</script>
				<?php
			}
		}
	}
?>
