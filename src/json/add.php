<?php
	class JSONAdd extends JSON
	{		
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) {
				$query = $this->coffee->db()->prepare("INSERT INTO Users(firstname, lastname, short) VALUES(?, ?, ?)");
				$query->bind_param("sss", $_GET["firstname"], $_GET["lastname"], $_GET["short"]);
				$query->execute();
				$query->close();
				$answer = Array("okay" => true);
				echo(json_encode($answer));
			}
		}
	}
?>
