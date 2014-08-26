<?php
	/*
	 * This API-Call returns the current saldo of the user calculated from the database
	 */
	class JSONSaldo extends JSON
	{
		public function printJSON()
		{
			if($query = $this->coffee->checkPassword()) { //Security: Only execute if password matches
				$now = new DateTime();
				$user = $this->coffee->getUser();
				/*
				 * Readable version of SQL-query used below
				 * SELECT
				 * 		SUM(p.price)
				 * FROM Users u
				 * LEFT JOIN Transactions t
				 * 		ON t.user = u.id
				 * LEFT JOIN Products p
				 * 		ON t.product = p.id
				 * WHERE u.id = ?
				 * GROUP BY(u.id)
				 */
				$query = $this->coffee->db()->prepare("SELECT SUM(p.price) FROM Users u LEFT JOIN Transactions t ON t.user = u.id LEFT JOIN Products p ON t.product = p.id WHERE u.id = ? GROUP BY(u.id)");
				$query->bind_param("i", $user);
				$query->execute();
				$query->bind_result($sum);
				$arr = array();
				if($query->fetch()) {
					if($sum == null) $sum = 0;
					$arr = array("sum" => $sum); //In order to create a json-object-wrapper
				}
				/* I don't know why I did this, I could just have returned the raw-value
				 * But I liked to do it, it is more consistent :D
				 *
				 * Please Note:
				 * It creates additional 8 bytes of overhead which is approx. 200% to 300% overhead
				 * ...A spit-in-the-face for every performance-oriented coder out there
				 */
				echo(json_encode($arr));
				$query->close();
			}
		}
	}
?>
