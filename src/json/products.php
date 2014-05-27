<?php
	class JSONProducts extends JSON
	{		
		public function printJSON()
		{
			if($query = $this->coffee->checkPassword()) {
				$now = new DateTime();
				$time = mktime(0, 0, 0, $now->format("m"), 1, $now->format("Y"));
				$user = $this->coffee->getUser();
				$query = $this->coffee->db()->prepare("SELECT p.price AS price, p.name AS name, p.id AS id, COUNT(t.id) AS amount FROM Products p LEFT JOIN Transactions t ON t.product = p.id AND t.user = ? AND t.date >= ? GROUP BY p.id");
				$query->bind_param("ii", $user, $time);
				$query->execute();
				$query->bind_result($price, $name, $id, $amount);
				$arr = array();
				while($query->fetch()) {
					array_push($arr, array("name" => $name, "id" => $id, "amount" => $amount, "price" => $price));
				}
				echo(json_encode($arr));
				$query->close();
			}
		}
	}
?>
