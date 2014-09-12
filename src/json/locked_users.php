<?php
	/*
	 * This API-Call (A Admin-API-Call and therefore secured by validating the admin-cookie)
	 * Will return a list of all users currently locked
	 */
	class JSONLockedUsers extends JSON
	{
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) { //Check and validate admin-cookie
				$query = $this->coffee->db()->prepare("SELECT firstname, lastname, short FROM Users WHERE locked = true");
				$query->execute();
				$query->bind_result($firstname, $lastname, $short);
				$arr = Array();
				while($query->fetch()) {
					$user = Array("firstname" => $firstname, "lastname" => $lastname, "short" => $short);
					array_push($arr, $user);
				}
				$query->close();
				echo(json_encode($arr));
			}
		}
	}
?>
