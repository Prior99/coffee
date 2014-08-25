<?php
	class Content404 extends Content
	{
		public function printTitle() {
			echo("404");
		}

		public function printHTML()
		{
			?>
				<h1>404 - Nicht gefunden</h1>
				<p>Die Resource die Sie angefragt haben konnte vom System nicht gefunden werden.</p>
			<?php
		}
	}
?>
