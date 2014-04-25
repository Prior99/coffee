<?php
	/*
	 */
	class JSONUserlist extends JSON
	{		
		/*
		 * Render the page
		 */
		public function printJSON()
		{
			$query = $this->coffee->db()->prepare("SELECT id, firstname, lastname FROM Users");
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
