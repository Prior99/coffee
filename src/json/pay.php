<?php
	/*
	 * API-Call used from Verwaltung to remove the debt of certain users
	 * It is invoked if the press the "Bezahlen" button in the Verwaltungs-Backend
	 */
	class JSONPay extends JSON
	{
		public function printJSON()
		{
			//Check for valid control-cookie (If user is logged in in verwaltung and password is valid)
			if(isset($_COOKIE["control"]) && $_COOKIE["control"] == $GLOBALS["config"]["Controlpassword"]) {
				if(isset($_GET["user"]) && isset($_GET["lower"]) && isset($_GET["upper"])) { //Make sure all parameters are specified as needed
					//The following should REALLY not have to be documented
					$query = $this->coffee->db()->prepare("UPDATE Transactions SET user = NULL WHERE user = ? AND date > ? AND date < ?");
					$query->bind_param("iii", $_GET["user"], $_GET["lower"], $_GET["upper"]);
					$query->execute();
					$query->close();
				}
			}
		}
	}
?>
