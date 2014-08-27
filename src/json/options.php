<?php
	/*
	 * This API-Call sets the code for a user
	 * or activates/deactivates it
	 */
	class JSONOptions extends JSON
	{
		public function printJSON()
		{
			if($this->coffee->checkPassword()) { //Handle security (If user is logged in etc.)
				$user = $this->coffee->getUser(); //Get current user
				if(isset($_GET["password"])) {
					if($_GET["password"] == "deactivated") { //If password is "deactivate" then deactivate it
						//Deactivating is done by setting the field to null
						$query = $this->coffee->db()->prepare("UPDATE Users SET password = NULL WHERE id = ?");
						$query->bind_param("i", $user);
						$query->execute();
						$query->close();
					}
					else { //Else a code should be set (if not, some guy tried to hack it and it's his own fault if his account is broken afterwards)
						//No, really, as i specified "ii" in the bind_param identification-string a string-value should be converted to a 0
						//And numbers longer than 999 should be truncated as the SQL-field has a maximum length of 3 digits
						$query = $this->coffee->db()->prepare("UPDATE Users SET password = ? WHERE id = ?");
						$query->bind_param("ii", $_GET["password"], $user);
						$query->execute();
						$query->close();
					}
				}
			}
		}
	}
?>
