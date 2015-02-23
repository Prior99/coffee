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
					$value = str_replace(",", ".", $_GET["value"]);
					//Fetch the old saldo
					$query = $this->coffee->db()->prepare("SELECT SUM(t.price) FROM Users u LEFT JOIN Transactions t ON t.user = u.id WHERE u.id = ? GROUP BY(u.id)");
					$query->bind_param("i", $_GET["user"]);
					$query->execute();
					$query->bind_result($sum);
					$query->fetch();
					$query->close();
					//echo $value.":".$sum;
					if($value + $sum >= 0) {
						//Delete all entries
						$query = $this->coffee->db()->prepare("UPDATE Transactions SET user = NULL WHERE user = ?");
						$query->bind_param("i", $_GET["user"]);
						$query->execute();
						$query->close();
						//Insert new saldo as transaction
						if($value + $sum > 0) {
							$saldo = $value + $sum;
							$query = $this->coffee->db()->prepare("INSERT INTO Transactions(user, product, price, date) VALUES(?, NULL, ?, ?)");
							$time = time();
							$query->bind_param("idi", $_GET["user"], $saldo, $time);
							$query->execute();
							$query->close();
						}
						echo json_encode(true);
					}
					else {
						echo json_encode(false);
					}
				}
			}
			else {
				echo json_encode(false);
			}
		}
	}
?>
