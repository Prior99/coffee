<?php
	/*
	 * Will add a new product to the selection of buyable products
	 * This API-Call is for admins and respectivly secured
	 */
	class JSONProductAdd extends JSON
	{
		public function printJSON()
		{
			//Check whether admins login is okay
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) {
				//I will not document the following as it should be selfexplanary
				$query = $this->coffee->db()->prepare("INSERT INTO Products(name, price) VALUES(?, ?)");
				$query->bind_param("ss", $_GET["name"], $_GET["price"]);
				$query->execute();
				$query->close();
				$answer = Array("okay" => true);
				echo(json_encode($answer));//Return as an json-object to be more consistent
				//And verbose, even though this creates ~250% overhead
				//This way it is more human-readable
			}
		}
	}
?>
