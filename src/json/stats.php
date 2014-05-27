<?php
	class JSONStats extends JSON
	{		
		public function printJSON()
		{
			if(isset($_GET["user"]) && isset($_GET["month"])) {
				$arr = array();
				$curMonth = date("n", time());
				$curYear = date("Y", time());
				for($i = 0; $i < $_GET["month"]; $i++) {
					$lower = mktime(0, 0, 0, $curMonth, 1, $curYear);
					$nMonth = $curMonth+1;
					$nYear = $curYear;
					if($nMonth > 12) {
						$nMonth = 1;
						$nYear++;
					}
					//echo($curMonth."-".$nMonth."<br>");
					$upper = mktime(0, 0, 0, $nMonth, 1, $nYear);
					$query = $this->coffee->db()->prepare("SELECT SUM(p.price) FROM Users u LEFT JOIN Transactions t ON t.user = u.id AND t.date > ? AND t.date < ? LEFT JOIN Products p ON t.product = p.id WHERE u.id = ? GROUP BY(u.id)");
					$query->bind_param("iii", $lower, $upper, $_GET["user"]);
					$query->execute();
					$query->bind_result($money);
					while($query->fetch()) {
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
				$query = $this->coffee->db()->prepare("SELECT u.id, u.firstname, u.lastname, u.short, SUM(p.price) FROM Users u LEFT JOIN Transactions t ON t.user = u.id LEFT JOIN Products p ON t.product = p.id GROUP BY(u.id) ORDER BY SUM(p.price) DESC");
				$query->execute();
				$query->bind_result($id, $first, $last, $short, $money);
				$arr = array();
				while($query->fetch()) {
					array_push($arr, array("id" => $id, "firstname" => $first, "lastname" => $last, "short" => $short, "pending" => $money));
				}
				$query->close();
				echo(json_encode($arr));
			}
		}
	}
?>
