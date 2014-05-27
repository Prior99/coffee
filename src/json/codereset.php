<?php
	class JSONCodeReset extends JSON
	{		
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) {
				$id = $this->coffee->getUserIDOfShort($_GET["short"]);
				if($id == -1) {
					$answer = Array("okay" => false);
					echo(json_encode($answer));
				}
				else {
					$query = $this->coffee->db()->prepare("UPDATE Users SET password = NULL WHERE id = ?");
					$query->bind_param("i", $id);
					$query->execute();
					$query->close();
					$answer = Array("okay" => true);
					echo(json_encode($answer));
				}
			}
		}
	}
?>
