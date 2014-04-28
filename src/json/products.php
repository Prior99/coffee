<?php
	class JSONProducts extends JSON
	{		
		public function printJSON()
		{
			if($query = $this->coffee->checkPassword($_GET["user"], $_GET["code"])) {
				$query = $this->coffee->db()->prepare("SELECT p.name AS name, p.id AS id, COUNT(t.id) AS amount FROM Products p LEFT JOIN Transactions t ON t.product = p.id AND t.user = ? GROUP BY p.id");
				$query->bind_param("i", $_GET["user"]);
				$query->execute();
				$query->bind_result($name, $id, $amount);
				$arr = array();
				while($query->fetch()) {
					array_push($arr, array("name" => $name, "id" => $id, "amount" => $amount));
				}
				echo(json_encode($arr));
				$query->close();
			}
		}
	}
?>
