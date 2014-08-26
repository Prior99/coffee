<?php
	/*
	 * This API-Call will delete a specified product.
	 * This is an admin-API-Call and is only executable if the admin-cookie is valid
	 */
	class JSONProductDelete extends JSON
	{
		public function printJSON()
		{
			//Check whether admins login is okay
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) {
				//I will not document the following as it should be selfexplanary
				$time = time();
				$query = $this->coffee->db()->prepare("UPDATE Products SET deleted = ? WHERE name = ?");
				$query->bind_param("is", $time, $_GET["name"]);
				$query->execute();
				$query->close();
				$answer = Array("okay" => true);
				echo(json_encode($answer));//Return as an json-object to be more consistent
				//And verbose, even though this creates ~250% overhead
				//How evil is this shit!?
				//Performance-loving-coders would have just returned "0" or "1"
				//But this way it is more human-readable
			}
		}
	}
?>
