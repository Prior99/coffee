<?php
	class JSONProductDelete extends JSON
	{		
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) {
				$time = time();
				$query = $this->coffee->db()->prepare("UPDATE Products SET deleted = ? WHERE name = ?");
				$query->bind_param("is", $time, $_GET["name"]);
				$query->execute();
				$query->close();
				$answer = Array("okay" => true);
				echo(json_encode($answer));
			}
		}
	}
?>
