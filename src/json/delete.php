<?php
	/*
	 * This API-Call (A Admin-API-Call and therefore secured by validating the admin-cookie)
	 * Will delete a specified user.
	 * A user is only deletable if he has no debts left.
	 * This Call will refuse to delete a user if he hasn't payed for a certain month
	 */
	class JSONDelete extends JSON
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
					$query = $this->coffee->db()->prepare("SELECT * FROM Transactions WHERE User = ?");
					$query->bind_param("i", $id);
					$query->execute();
					$f = $query->fetch();
					$query->close();
					//$f indicates if any debts are left
					if(!$f) { //No debts left? Cool. Now delete him!
						//Some more SQL-Magic and Whooooosh...
						$query = $this->coffee->db()->prepare("DELETE FROM Users WHERE id = ?");
						$query->bind_param("i", $id);
						$query->execute();
						$query->close();
						//...there - another human gone. We will miss you.
						$answer = Array("okay" => true);
					}
					else { //If the user still has debts to pay, we will not let him go. Hah!
						$answer = Array("okay" => false);
					}
					echo(json_encode($answer));
				}
			}
		}
	}
?>
