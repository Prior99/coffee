<?php
	class JSONOptions extends JSON
	{		
		public function printJSON()
		{
			if($query = $this->coffee->checkPassword($_GET["user"], $_GET["code"])) {
				if(isset($_GET["password"])) {
					if($_GET["password"] == "deactivated") {
						$query = $this->coffee->db()->prepare("UPDATE Users SET password = NULL WHERE id = ?");
						$query->bind_param("i", $_GET["user"]);
						$query->execute();
						$query->close();
					}
					else {
						$query = $this->coffee->db()->prepare("UPDATE Users SET password = ? WHERE id = ?");
						$query->bind_param("ii", $_GET["password"], $_GET["user"]);
						$query->execute();
						$query->close();
					}
				}
			}
		}
	}
?>
	
