<?php
	class JSONValidate extends JSON
	{		
		public function printJSON()
		{
			echo(json_encode(array("okay" => $this->coffee->checkPassword($_GET["user"], $_GET["code"]))));
		}
	}
?>
