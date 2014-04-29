<?php
	class ContentBuy extends Content
	{	
		public function printTitle() {
			?>
				Kaufen
			<?php
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
					<div id="products"></div>
					<div style="text-align: center;">
						<a href="#" id="logout">Abmelden</a> | 
						<a href="#" id="buy">Kaufen</a>
					</div>
					<script type="text/javascript">
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
										pressTime = new Date().getTime();
										timeout = setTimeout(function() {
											if(product.bought !== undefined && product.bought > 0) {
												product.bought--;
												updateCounter(counter, product.amount, product.bought);
											}
										}, 700);
									}
									function up() {
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
