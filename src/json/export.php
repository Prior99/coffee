<?php
	/*
	 * This API-Call exmports statistics to an CSV-File that is then downloaded
	 * It is an Admin-API-Call and therefore checks the admin-cookie
	 */
	class JSONExport extends JSON
	{
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) { //Security
				date_default_timezone_set("Europe/Berlin"); //Prevent timezone-bugs
				$month = $_GET["month"]; //Get the month that should be exported
				$year = $_GET["year"]; //As well as the year
				$timestart = mktime(0, 0, 0, $month, 1, $year); //Generate first timestamp from parameters
				if($month < 12) {
					$timeend = mktime(0, 0, 0, $month + 1, 1, $year); //The end is calculated by adding 1 to the month
				}
				else { //But if we are in december, we also need to increase the year
					$timeend = mktime(0, 0, 0, 1, 1, $year + 1);
				}
				/*
				 * We store the userids in an temporary array to use later on
				 * This way we do not need to do so much joining and also do not need
				 * Multiple querys which might fuck up with MySQL depending on it's version and driver
				 */
				$ids = Array(); //This is an array with all userids
				$query = $this->coffee->db()->prepare("SELECT id FROM Users ORDER BY lastname, firstname");
				$query->execute();
				$query->bind_result($id);
				while($query->fetch()) {
					array_push($ids, $id);
				}
				$query->close();
				/*
				 * Now lets generate the first line (headline) with all buyable products
				 * Note, that only products deleted BEFORE this month will be removed
				 * This works as "deleted" is a timestamp, not a boolean
				 */
				$query = $this->coffee->db()->prepare("SELECT name FROM Products WHERE deleted = 0 OR deleted >= ? ORDER BY name");
				$query->bind_param("i", $timeend);
				$query->execute();
				$query->bind_result($product);
				echo("Mitarbeiter");
				while($query->fetch()) {
					echo(";".$product);
				}
				$query->close();
				echo("\r\n");
				/*
				 * Okay, headline generated, now we will generate the data for each user that is not deleted
				 */
				foreach($ids as $id) {
					/*
					 * Fetch this users name and whether he is deleted or not
					 * Yes, this could have been done using a LEFT JOIN, but this way it is more efficient as
					 * Multi-JOINs in one query are more hard to calculate and we generate less data to be send
					 */
					$query = $this->coffee->db()->prepare("SELECT firstname, lastname, deleted FROM Users WHERE id = ?");
					$query->bind_param("i", $id);
					$query->execute();
					$query->bind_result($first, $last, $deleted);
					$query->fetch();
					$query->close();
					/*
					 * Readable version of Query below:
					 *
					 * SELECT
					 * 		COUNT(t.id) AS amount
					 * FROM Products p
					 * LEFT JOIN Transactions t
					 * 		ON p.id = t.product AND t.user = ? AND t.date >= ? AND t.date <= ?
					 * WHERE p.deleted = 0 OR p.deleted >= ?
					 * GROUP BY p.id
					 * ORDER BY p.name
					 *
					 * This query selects the amount each product was bought by this user
					 */
					$query = $this->coffee->db()->prepare("SELECT COUNT(t.id) AS amount FROM Products p LEFT JOIN Transactions t ON p.id = t.product AND t.user = ? AND t.date >= ? AND t.date <= ? WHERE p.deleted = 0 OR p.deleted >= ? GROUP BY p.id ORDER BY p.name");
					$query->bind_param("iiii", $id, $timestart, $timeend, $timeend);
					$query->execute();
					$query->bind_result($amount);
					$sum = 0;
					$amounts = Array(); //Create an array to buffer the amounts in
					/*
					 * This is because I want to calculate the sum of all bought products overall
					 * Because if the sum is 0, the user did not buy anything at all
					 * And does not need to occur in the CSV-File at all
					 * This way we don't have Lines all-zero
					 */
					while($query->fetch()) {
						array_push($amounts, $amount);
						$sum+=$amount; //Calculate the sum
					}
					$query->close();
					if($sum != 0 || $deleted < 1) { //Only display if the user bought anything at all and is not deleted
						echo($last . ", " . $first); //Echo the name
						foreach($amounts as $amount) {
							echo(";".$amount); //The amount each product was bought
						}
						echo("\r\n");
					}
				}
				/*
				 * Fake the header to be a csv-file
				 * Also fake the filename
				 * This is the reason why we use ob_create()
				 */
				header('Content-type: text/csv; charset=utf-8');
				header('Content-Disposition: attachment; filename="coffeeconsumption_' . $year . '_' . $month . '.csv"');
				//Now the ob_end_flush() at the end of index.php will echo all data and we have faked a CSV-file
			}
		}
	}
?>
