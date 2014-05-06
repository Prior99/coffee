<?php
	class JSONAdd extends JSON
	{		
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) {
				$query = $this->coffee->db()->prepare("INSERT INTO Users(firstname, lastname) VALUES(?, ?)");
				$query->bind_param("ss", $_GET["firstname"], $_GET["lastname"]);
				$query->execute();
				$query->close();
				$answer = Array("okay" => true);
				echo(json_encode($answer));
			}
		}
	}
?>
