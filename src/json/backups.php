<?php
	/*
	 * This API-Call (A Admin-API-Call and therefore secured by validating the admin-cookie)
	 * Will return a list of all backups known to the system
	 */
	class JSONBackups extends JSON
	{
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) { //Check and validate admin-cookie
				$query = $this->coffee->db()->prepare("SELECT created, id FROM Backups ORDER BY created DESC");
				$query->execute();
				$query->bind_result($created, $id);
				$arr = Array();
				while($query->fetch()) {
					$backup = Array("created" => $created, "id" => $id);
					array_push($arr, $backup);
				}
				$query->close();
				echo(json_encode($arr));
			}
		}
	}
?>
