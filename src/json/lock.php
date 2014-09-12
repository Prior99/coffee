<?php
	/*
	 * This API-Call (A Admin-API-Call and therefore secured by validating the admin-cookie)
	 * Will lock a users Account
	 */
	class JSONLock extends JSON
	{
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) { //Check and validate admin-cookie
				$id = $this->coffee->getUserIDOfShort($_GET["short"]); //As the admin will enter the users shortage to delete him we need to fetch the database-id
				//(As shortages are unique in the ITC)
				if($id == -1) { //This user did not exist. return false and exit
					$answer = Array("okay" => false);
					echo(json_encode($answer));
				}
				else { //Okay, the user existed!
					//Now check if he has any debts left
					$query = $this->coffee->db()->prepare("UPDATE Users SET locked = TRUE WHERE id = ?");
					$query->bind_param("i", $id);
					$query->execute();
					$query->close();
					$answer = Array("okay" => true);
					echo(json_encode($answer));
				}
			}
		}
	}
?>
