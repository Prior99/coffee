<?php
	/*
	 * This API-Call will reset the code for a certain user.
	 * It is an Admin-API-Call and therefore secured by checking and validating the admins cookie
	 */
	class JSONCodeReset extends JSON
	{
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) { //Verify this is the admin
				/*
				 * So. You forgot your... 3-digit... numeric... code -_-
				 * Are you kidding me?
				 */
				$id = $this->coffee->getUserIDOfShort($_GET["short"]); //As admin enters the users unique ITC-intern shortage
				//we identify the users real database-id using this method
				if($id == -1) { //The user did not exist
					$answer = Array("okay" => false); //Tell the admin about his fail (maybe a typo)
					echo(json_encode($answer));
				}
				else { //Okay, the user existed, reset his code
					//A null in the code-field indicates a deactivated code, so we will set it to null
					$query = $this->coffee->db()->prepare("UPDATE Users SET password = NULL WHERE id = ?");
					$query->bind_param("i", $id);
					$query->execute();
					$query->close();
					//RUN user RUN! Your account is now unsecured until you reached your desktop to set a new code
					$answer = Array("okay" => true);
					echo(json_encode($answer));
				}
			}
		}
	}
?>
