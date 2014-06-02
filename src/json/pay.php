<?php
	class JSONPay extends JSON
	{		
		public function printJSON()
		{
			if(isset($_COOKIE["control"]) && $_COOKIE["control"] == $GLOBALS["config"]["Controlpassword"]) {
				if(isset($_GET["user"]) && isset($_GET["lower"]) && isset($_GET["upper"]))
				$query = $this->coffee->db()->prepare("UPDATE Transactions SET user = NULL WHERE user = ? AND date > ? AND date < ?");
				$query->bind_param("iii", $_GET["user"], $_GET["lower"], $_GET["upper"]);
				$query->execute();
				$query->close();
			}
		}
	}
?>
