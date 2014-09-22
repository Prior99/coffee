<?php
	/*
	 * This API-Call (A Admin-API-Call and therefore secured by validating the admin-cookie)
	 * Will delete a specified user.
	 * A user is only deletable if he has no debts left.
	 * This Call will refuse to delete a user if he hasn't payed for a certain month
	 */
	class JSONCodeEveryone extends JSON
	{
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) { //Check and validate admin-cookie
				$query = $this->coffee->db()->prepare("SELECT id, mail FROM Users WHERE password IS NULL");
				$query->execute();
				$query->bind_result($id, $mail);
				$arr = Array();
				while($query->fetch()) {
					array_push($arr, Array("id" => $id, "mail" => $mail));
				}
				$query->close();
				foreach($arr as $user) {
					$query = $this->coffee->db()->prepare("UPDATE Users SET password = ? WHERE id = ?");
					$password = rand(0, 999);
					$query->bind_param("ii", $password, $user["id"]);
					$query->execute();
					$query->close();
					if($password < 10) $password = "00".$password;
					else if($password < 100) $password = "0".$password;
					echo("Set password of user with id ".$user["id"]. " to ".$password."<br>\n");
					$this->coffee->mail($user["mail"], "Kaffee-Konto wurde gesichert", 
					"Hallo,\n\n".
					"um die Sicherheit der Web-App zu erhöhen, wurden alle Konten automatisch mit einer zufälligen Pin gesichert.\n".
					"Sie haben die Möglichkeit, diese in den Optionen wieder zu entfernen oder eine andere Pin einzustellen.\n\n".
					"Ihre automatisch generierte Pin lautet: ".$password."\n\n".
					"Bis bald,\n".
					"Ihre Kaffee-Maschine");
				}
			}
		}
	}
?>
