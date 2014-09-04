<?php
	/*
	 * This API-Call will add a single new user to the database
	 * It is an API-Call for the admin, so the adminpassword will be checked
	 */
	class JSONAdd extends JSON
	{
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) { //Check if admin is logged in
				//TODO: Add mail also
				//TODO: Check whether user already exists
				//Insert new user to database
				$query = $this->coffee->db()->prepare("INSERT INTO Users(firstname, lastname, short, mailato) VALUES(?, ?, ?, ?)");
				$query->bind_param("sss", $_GET["firstname"], $_GET["lastname"], $_GET["short"], $_GET["mail"]);
				$query->execute();
				$query->close();
				//return true
				$answer = Array("okay" => true);
				echo(json_encode($answer));
			}
		}
	}
?>
