<?php
	class JSONSaldo extends JSON
	{		
		public function printJSON()
		{
			if($query = $this->coffee->checkPassword()) {
				$now = new DateTime();
				$user = $this->coffee->getUser();
				$query = $this->coffee->db()->prepare("SELECT SUM(p.price) FROM Users u LEFT JOIN Transactions t ON t.user = u.id LEFT JOIN Products p ON t.product = p.id WHERE u.id = ? GROUP BY(u.id)");
				$query->bind_param("i", $user);
				$query->execute();
				$query->bind_result($sum);
				$arr = array();
				if($query->fetch()) {
					if($sum == null) $sum = 0;
					$arr = array("sum" => $sum);
				}
				echo(json_encode($arr));
				$query->close();
			}
		}
	}
?>
