<?php
	/*
	 * This API-Call will check whether the user wants to receive mails and set/resets this option
	 */
	class JSONSendMails extends JSON
	{
		public function printJSON()
		{
			if($this->coffee->checkPassword()) {//Only if user is logged in and password matches
				$user = $this->coffee->getUser();
				if(isset($_GET["set"])) { //If the "set" option is specified, we want to set it and not read it
					$set = $_GET["set"] == "true"; //Convert string into boolean. easy.
					$query = $this->coffee->db()->prepare("UPDATE Users SET send_mails = ? WHERE id = ?");
					$query->bind_param("ii", $set, $user);
					$query->execute();
					$query->fetch();
					echo("true"); //We need to return something, so we return true. There is no way this cannot have worked.
				}
				else { //No "set" option specfied, thous we want to fetch it
					//Following should be self-explanary
					$query = $this->coffee->db()->prepare("SELECT send_mails FROM Users WHERE id = ?");
					$query->bind_param("i", $user);
					$query->execute();
					$query->bind_result($send);
					$query->fetch();
					$query->close();
					echo($send ? "true" : "false"); //Encoded to json by hand :D how hardcore is this !?
				}
			}
		}
	}
?>
