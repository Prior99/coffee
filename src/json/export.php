<?php
	class JSONExport extends JSON
	{		
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) {
				date_default_timezone_set("Europe/Berlin");
				$month = $_GET["month"];
				$year = $_GET["year"];
				$timestart = mktime(0, 0, 0, $month, 1, $year);
				if($month < 12) {
					$timeend = mktime(0, 0, 0, $month + 1, 1, $year);
				}
				else {
					$timeend = mktime(0, 0, 0, 1, 1, $year + 1);
				}
				$ids = Array();
				$query = $this->coffee->db()->prepare("SELECT id FROM Users ORDER BY lastname, firstname");
				$query->execute();
				$query->bind_result($id);
				while($query->fetch()) {
					array_push($ids, $id);
				}
				$query->close();
				$query = $this->coffee->db()->prepare("SELECT name FROM Products ORDER BY name");
				$query->execute();
				$query->bind_result($product);
				echo("Mitarbeiter");
				while($query->fetch()) {
					echo(";".$product);
				}
				echo("\r\n");
				foreach($ids as $id) {
					$query = $this->coffee->db()->prepare("SELECT firstname, lastname FROM Users WHERE id = ?");
					$query->bind_param("i", $id);
					$query->execute();
					$query->bind_result($first, $last);
					$query->fetch();
					$query->close();
					echo($last . ", " . $first);
					$query = $this->coffee->db()->prepare("SELECT COUNT(t.id) AS amount FROM Products p LEFT JOIN Transactions t ON p.id = t.product AND t.user = ? AND t.date >= ? AND t.date <= ? GROUP BY p.id ORDER BY p.name");
					$query->bind_param("iii", $id, $timestart, $timeend);
					$query->execute();
					$query->bind_result($amount);
					while($query->fetch()) {
						echo(";".$amount);
					}
					$query->close();
					echo("\r\n");
				}
				header('Content-type: text/csv; charset=utf-8');
				//header('Content-Disposition: attachment; filename="coffeeconsumption_' . $year . '_' . $month . '.csv"');
			}
		}
	}
?>
