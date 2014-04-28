<?php
	class Content404 extends Content
	{		
		public function printTitle() {
			echo("404");
		}
		
		public function printHTML()
		{
			?>
				<h1>Wie bitte?</h1>
				<p>Was wollen Sie von mir? Ich weiss nichts von einem "<?php echo($_GET["action"]); ?>" und selbst wenn, würde ich es Ihnen ganz bestimmt nicht verraten.</p>
			<?php
		}
	}
?>
