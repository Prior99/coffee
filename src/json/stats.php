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
				if(isset($_GET["user"]) && isset($_GET["month"])) {
					$arr = array();
					//The "month" parameter means the amount of months we want to go back in time
					$curMonth = date("n", time()); //Get current month and year
					$curYear = date("Y", time());
					for($i = 0; $i < $_GET["month"]; $i++) { //Go back in time "month" months
						/*
						 * This is date/time-calculating and thous is complex.
						 * I hope it is udnerstandable documented!
						 *
						 * We need two timestamps. A lower one and an upper one.
						 * We now calculate the lower one which is just on month less than the upper one.
						 * The lower one is the one generated from the loopiteration. The upper one is
						 * Always generated relative to the lower one
						 */
						$lower = mktime(0, 0, 0, $curMonth, 1, $curYear);
						$nMonth = $curMonth + 1; //So increase the monthvalue by one to get next month
						$nYear = $curYear;
						if($nMonth > 12) { //If we exceeded 12, we need to increase the year (If the lower one was december)
							$nMonth = 1; //And reset month to 1 (january)
							$nYear++;
						}
						//echo($curMonth."-".$nMonth."<br>");
						$upper = mktime(0, 0, 0, $nMonth, 1, $nYear); //Now generate the upper one
						/*
						 * Documentation of more-complex SQL-Statement used below:
						 * 		SELECT
						 *	    	SUM(p.price) //This is the amount of money for this month
						 *		FROM Users u
						 *		LEFT JOIN Transactions t
						 *			ON t.user = u.id AND t.date > ? AND t.date < ? //userid must match and date must be in range
						 *		LEFT JOIN Products p
						 *			ON t.product = p.id
						 *		WHERE u.id = ?
						 *		GROUP BY(u.id)
						 */
						$query = $this->coffee->db()->prepare("SELECT SUM(p.price) FROM Users u LEFT JOIN Transactions t ON t.user = u.id AND t.date > ? AND t.date < ? LEFT JOIN Products p ON t.product = p.id WHERE u.id = ? GROUP BY(u.id)");
						$query->bind_param("iii", $lower, $upper, $_GET["user"]);
						$query->execute();
						$query->bind_result($money);
						while($query->fetch()) {
							//Now store amount in assoc-array to convert into json-objects lateron
							$arr[date("F Y", $lower)] = array("money" =>$money , "lower" => $lower, "upper" => $upper);
						}
						$query->close();
						if(--$curMonth < 1) {
							$curMonth = 12;
							$curYear--;
						}
					}
					echo(json_encode($arr));
				}
				else {
					/*
					 * Readable formatted version of SQL-statement used below
					 * 		SELECT
					 * 			u.id,
					 * 			u.firstname,
					 * 			u.lastname,
					 * 			u.short,
					 * 			SUM(p.price)
					 * 		FROM Users u
					 * 		LEFT JOIN Transactions t
					 * 			ON t.user = u.id
					 * 		LEFT JOIN Products p
					 * 			ON t.product = p.id
					 * 		GROUP BY(u.id)
					 * 		ORDER BY u.lastname, u.firstname ASC
					 */
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
								$order = "SUM(p.price) DESC";
								break;
						}
					}
					$query = $this->coffee->db()->prepare("SELECT u.id, u.firstname, u.lastname, u.short, u.mail, SUM(p.price) FROM Users u LEFT JOIN Transactions t ON t.user = u.id LEFT JOIN Products p ON t.product = p.id GROUP BY(u.id) ORDER BY $order");
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
