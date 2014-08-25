<?php
	class JSONBuy extends JSON
	{
		public function printJSON()
		{
			if($query = $this->coffee->checkPassword()) {
				$user = $this->coffee->getUser();
				$array = json_decode($_GET["info"]);
				$string = "Hallo,\n\n".
					"Soeben wurde folgende Kaffee-Bestellung entgegengenommen:\n";
				foreach($array as $p) {
					$string .= $p->name . " x" . $p->bought."\n";
					for($i = 0; $i < $p->bought; $i++) {
						$query = $this->coffee->db()->prepare("INSERT INTO Transactions(user, product, date) VALUES(?, ?, ?)");
						$time = time();
						$query->bind_param("iii", $user, $p->id, $time);
						$query->execute();
						$query->close();
					}
				}
				$string .= "\n".
				"Lieber Gruss,\n".
				"Ihre Kaffeemaschine";
				$query = $this->coffee->db()->prepare("SELECT send_mails FROM Users WHERE id = ?");
				$query->bind_param("i", $user);
				$query->execute();
				$query->bind_result($send);
				$query->fetch();
				$query->close();
				if($send) {
					mail($this->coffee->getMail($user), "Ihr Kaffee-Kauf", $string);
				}
			}
		}
	}
?>
