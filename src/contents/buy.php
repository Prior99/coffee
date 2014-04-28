<?php
	class ContentBuy extends Content
	{		
		public function printHTML()
		{
			if(!$this->coffee->checkPassword($_GET["user"], $_GET["code"])) {
				?>
					<h1>Ähem!</h1>
					<p>Sie sollten nicht hier sein oder? Dürfte ich mal Ihren Ausweis sehen? Und jetzt gehen Sie, bevor ich mich vergesse!</p>
				<?php
			}
			else {
				?>
					<div id="products"></div>
					<div style="text-align: center;">
						<a href="index.php">Abbrechen</a> | 
						<a href="#" id="buy">Okay</a>
					</div>
					<script type="text/javascript">
						$.ajax({
						url : "?json=products&user=<?php echo($_GET["user"]); ?>&code=<?php echo($_GET["code"]); ?>"
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
									$("<div class='product'></div>").appendTo(products).append(counter).append($("<div>" + product.name + "</div>").click(function() {
										if(product.bought === undefined) product.bought = 0;
										product.bought++;
										updateCounter(counter, product.amount, product.bought);
									}));
								})(i);
							}
							$("#buy").click(function() {
								for(var i in result) {
									var product = result[i];
									if(product.bought !== undefined && product.bought > 0) {
										(function(p) {
											var f = function() {
												$.ajax({
													url:"?json=buy&user=<?php echo($_GET["user"]); ?>&code=<?php echo($_GET["code"]); ?>&product=" + p.id
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
