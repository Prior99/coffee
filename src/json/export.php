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
				if(isset($_GET["startmonth"]) && isset($_GET["startday"]) && isset($_GET["startyear"]) &&
					isset($_GET["endmonth"]) && isset($_GET["endday"]) && isset($_GET["endyear"])) {
					$timestart = mktime(0, 0, 0, $_GET["startmonth"], $_GET["startday"], $_GET["startyear"]);
					$timeend = mktime(0, 0, 0, $_GET["endmonth"], $_GET["endday"], $_GET["endyear"]);
					$query = $this->coffee->db()->prepare("SELECT p.name AS name, COUNT(t.id) AS result FROM Transactions t LEFT JOIN Products p ON p.id = t.product WHERE t.date >= ? AND t.date <= ? GROUP BY p.id");
					$query->bind_param("ii", $timestart, $timeend);
					$query->execute();
					$query->bind_result($name, $result);
					echo("Produkt;Summe\r\n");
					while($query->fetch()) {
						echo($name . ";" . $result . "\r\n");
					}
					$query->close();
					/*
					 * Fake the header to be a csv-file
					 * Also fake the filename
					 * This is the reason why we use ob_create()
					 */
					header('Content-type: text/csv; charset=utf-8');
					header('Content-Disposition: attachment; filename="coffeeconsumption.csv"');
					//Now the ob_end_flush() at the end of index.php will echo all data and we have faked a CSV-file
				}
			}
		}
	}
?>
