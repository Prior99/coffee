<?php
	class JSONProductAdd extends JSON
	{		
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) {
				$query = $this->coffee->db()->prepare("INSERT INTO Products(name, price) VALUES(?, ?)");
				$query->bind_param("ss", $_GET["name"], $_GET["price"]);
				$query->execute();
				$query->close();
				$answer = Array("okay" => true);
				echo(json_encode($answer));
			}
		}
	}
?>
