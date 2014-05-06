<?php
	class JSONUserlist extends JSON
	{		
		public function printJSON()
		{
			$query = $this->coffee->db()->prepare("SELECT id, firstname, lastname FROM Users WHERE deleted = FALSE ORDER BY lastname, FIRSTNAME");
			$query->execute();
			$query->bind_result($id, $first, $last);
			$arr = array();
			while($query->fetch()) {
				array_push($arr, array("firstname" => $first, "lastname" => $last, "id" => $id));
			}
			$query->close();
			echo(json_encode($arr));
		}
	}
?>
