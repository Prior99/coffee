<?php
	/*
	 * This is a 404 Not found content, displayed whenever the user tries to reach a site which does
	 * not exist or was removed or whatever.
	 */
	class Content404 extends Content
	{
		//See content.php for documentation of printTitle() and printHTML()
		public function printTitle() {
			echo("404");
		}

		public function printHTML()
		{
			//I hope you understand this code without the need of any documentation
			?>
				<h1>404 - Nicht gefunden</h1>
				<p>Die Resource die Sie angefragt haben konnte vom System nicht gefunden werden.</p>
			<?php
		}
	}
?>
