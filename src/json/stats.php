<?php
	/*
	 * This is an API-Call for the Verwaltung and returns a list of all users and their saldos
	 * Or, if invoked with a specific user and amount of months supplied, returns a detailed statistic of this month
	 */
	class JSONStats extends JSON
	{
		public function printJSON()
		{
			//Only enter if we are identified as a controller (logged in to verwaltung)
			if(isset($_COOKIE["control"]) && $_COOKIE["control"] == $GLOBALS["config"]["Controlpassword"]) {
				/*
				 * If invoked with month and user specified, will return a detailed statistic for the specified months and user instead
				 */
				if(isset($_GET["user"])) {
					$arr = array();
					//The "month" parameter means the amount of months we want to go back in time
					$query = $this->coffee->db()->prepare("SELECT SUM(t.price) FROM Users u LEFT JOIN Transactions t ON t.user = u.id WHERE u.id = ? GROUP BY(u.id)");
					$query->bind_param("i", $_GET["user"]);
					$query->execute();
					$query->bind_result($money);
					$query->fetch();
					$query->close();
					echo($money);
				}
				else {
					$order = "u.lastname, u.firstname";
					if(isset($_GET["order"])) {
						switch($_GET["order"]) {
							case "lastname": default:
								$order = "u.lastname, u.firstname ASC";
								break;
							case "firstname": default:
								$order = "u.firstname, u.lastname ASC";
								break;
							case "short":
								$order = "u.short ASC";
								break;
							case "sum":
								$order = "money ASC";
								break;
						}
					}
					/*
					 * Readable formatted version of SQL-statement used below
					 * 		SELECT
					 * 			u.id,
					 * 			u.firstname,
					 * 			u.lastname,
					 * 			u.short,
					 * 			COALESCE(SUM(p.price), 0)
					 * 		FROM Users u
					 * 		LEFT JOIN Transactions t
					 * 			ON t.user = u.id
					 * 		GROUP BY(u.id)
					 * 		ORDER BY u.lastname, u.firstname ASC
					 */
					$query = $this->coffee->db()->prepare("SELECT u.id, u.firstname, u.lastname, u.short, u.mail, COALESCE(SUM(t.price), 0) AS money FROM Users u LEFT JOIN Transactions t ON t.user = u.id GROUP BY(u.id) ORDER BY $order");
					$query->execute();
					$query->bind_result($id, $first, $last, $short, $mail, $money);
					$arr = array();
					while($query->fetch()) {
						//Now store amount in assoc-array to convert into json-objects lateron
						array_push($arr, array("id" => $id, "firstname" => $first, "lastname" => $last, "short" => $short, "pending" => $money, "mail" => $mail));
					}
					$query->close();
					echo(json_encode($arr));
				}
			}
		}
	}
?>
