<?php
	/*
	 * API-Call used from Verwaltung to remove the debt of certain users
	 * It is invoked if the press the "Bezahlen" button in the Verwaltungs-Backend
	 */
	class JSONPay extends JSON
	{
		public function printJSON()
		{
			//Check for valid control-cookie (If user is logged in in verwaltung and password is valid)
			if(isset($_COOKIE["control"]) && $_COOKIE["control"] == $GLOBALS["config"]["Controlpassword"]) {
				if(isset($_GET["user"]) && isset($_GET["value"])) { //Make sure all parameters are specified as needed
					$value = $_GET["value"];
					//Fetch the old saldo
					$query = $this->coffee->db()->prepare("SELECT SUM(t.price) FROM Users u LEFT JOIN Transactions t ON t.user = u.id WHERE u.id = ? GROUP BY(u.id)");
					$query->bind_param("i", $user);
					$query->execute();
					$query->bind_result($sum);
					$query->close();
					if($value + $sum >= 0) {
						//Delete all entries
						$query = $this->coffee->db()->prepare("UPDATE Transactions SET user = NULL WHERE user = ?");
						$query->bind_param("iii", $_GET["user"]);
						$query->execute();
						$query->close();
						//Insert new saldo as transaction
						if($value + $sum > 0) {
							$saldo = $value + $sum;
							$query = $this->coffee->db()->prepare("INSERT INTO Transactions(user, product, price) VALUES(?, NULL, ?)");
							$query->bind_param("ii", $_GET["user"], $saldo);
							$query->execute();
							$query->close();
						}
						echo "true";
					}
					else {
						echo "false";
					}
				}
			}
			else {
				echo "false";
			}
		}
	}
?>
