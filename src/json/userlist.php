<?php
	/*
	 * This API-Call returns a list of all users
	 * with teir name and shortage sorted by lastname, firstname
	 */
	class JSONUserlist extends JSON
	{
		public function printJSON()
		{
			//Note: No security, invokable from global
			$query = $this->coffee->db()->prepare("SELECT id, firstname, lastname, short FROM Users ORDER BY lastname, firstname");
			$query->execute();
			$query->bind_result($id, $first, $last, $short);
			$arr = array(); //Create array for all users
			while($query->fetch()) {
				//Store each user in an associative array we can convert to a json-object after all users have been loaded
				array_push($arr, array("firstname" => $first, "lastname" => $last, "id" => $id, "short" => $short));
			}
			$query->close();
			echo(json_encode($arr));
		}
	}
?>
