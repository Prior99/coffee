<?php
	/*
	 * This API-Call tests the username and password supplied in the cookie
	 */
	class JSONValidate extends JSON
	{
		public function printJSON()
		{
			$user = $this->coffee->getUser();
			if($this->coffee->isUserLocked($user)) {
				echo(json_encode(array("locked" => "true")));
			}
			else {
				//Note: No security, invokable from global
				$b = $this->coffee->checkPassword();
				if(!$b) {
					$fails = $this->coffee->increaseLoginFailures();
					$this->coffee->mail($this->coffee->getMail($user), "Kaffee-Pin Falsch eingegeben", 
						"Hallo,\n\n".
						"Soeben wurde versucht, sich in Ihr Konto mit einer falschen Pin anzumelden.\n".
						"Der Pin wurde bereits ".$fails."x falsch eingegeben.\n".
						"Nach der 4. falschen Eingabe wird ihr Konto gesperrt.\n".
						"Ihr Konto muss dann von einem Mitglied der Kaffee-AG freigeschaltet werden.\n\n".
						"Bis bald,\n".
						"Ihre Kaffee-Maschine");
				}
				else {
					$this->coffee->resetLoginFailures();
				}
				echo(json_encode(array("okay" => $b))); //Tells the requester whether the login is okay
			}
		}
	}
?>
