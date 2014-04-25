<?php
	class Content404 extends Content
	{		
		public function printHTML()
		{
			?>
				<h1>Unbekannter Request</h1>
				<p>Der Inhalt "<?php echo($_GET["action"]); ?>", den Sie versuchen aufzurufen, ist dem Server nicht bekannt. Bitte surfen Sie zurück in bekannte Gewässer.</p>
			<?php
		}
	}
?>
