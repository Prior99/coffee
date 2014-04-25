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
						<a href="index.php">Fertig</a>
					</div>
					<script type="text/javascript">
						$.ajax({
						url : "?json=products"
						}).done(function(res) {
							var result = JSON.parse(res);
							var products = $("#products");
							for(var i in result) {
								(function() {
									var count = 0;
									var product = result[i];
									var counter = $("<div style='float:right;'></div>");
									$("<div class='product'></div>").appendTo(products).append(counter).append($("<div>" + product.name + "</div>").click(function() {
										count++;
										counter.html("OK");
										$.ajax({
											url:"?json=buy&user=<?php echo($_GET["user"]); ?>&code=<?php echo($_GET["code"]); ?>&product=" + product.id
										}).done(function() {
											counter.html("+"+count);
										});
									}));
								})(i);
							}
						});
					</script>
				<?php
			}
		}
	}
?>
