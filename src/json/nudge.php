<?php
	/*
	 * This is an API-Call for the Verwaltung will nudge a user to pay
	 */
	class JSONNudge extends JSON
	{
		public function printJSON()
		{
			//Only enter if we are identified as a controller (logged in to verwaltung)
			if(isset($_COOKIE["control"]) && $_COOKIE["control"] == $GLOBALS["config"]["Controlpassword"]) {
				if(isset($_GET["user"])) {
					$user = $_GET["user"];
					$mail = $this->coffee->getMail($user);
					$query = $this->coffee->db()->prepare("SELECT SUM(t.price) FROM Users u LEFT JOIN Transactions t ON t.user = u.id WHERE u.id = ? GROUP BY(u.id)");
					$query->bind_param("i", $user);
					$query->execute();
					$query->bind_result($sum);
					if(!$query->fetch()) {
						$sum = 0;
					}
					// Sum is negative...
					$sum = $sum * -1;
					$query->close();
					echo $sum;
					$this->coffee->mail($mail, "Zahlungserinnerung Kaffee", "Hallo,\n\n".
						"Wir müssen neuen Kaffee nachbestellen:\n".
						"Bitte bezahlen Sie möglichst bald die ausstehende Summe von ". number_format($sum / 100, 2) ."€ an der Anmeldung.\n\n".
						"Vielen Dank,\n".
						"Ihre Kaffeemaschine"
					);
				}
			}
		}
	}
?>
