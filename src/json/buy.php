<?php
	class JSONBuy extends JSON
	{		
		public function printJSON()
		{
			if($query = $this->coffee->checkPassword()) {
				$user = $this->coffee->getUser();
				$time = time();
				$query = $this->coffee->db()->prepare("INSERT INTO Transactions(user, product, date) VALUES(?, ?, ?)");
				$query->bind_param("iii", $user, $_GET["product"], $time);
				$query->execute();
				$query->close();	
			}
		}
	}
?>
