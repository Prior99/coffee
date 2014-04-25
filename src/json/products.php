<?php
	class JSONProducts extends JSON
	{		
		public function printJSON()
		{
			$query = $this->coffee->db()->prepare("SELECT name, id FROM Products");
			$query->execute();
			$query->bind_result($name, $id);
			$arr = array();
			while($query->fetch()) {
				array_push($arr, array("name" => $name, "id" => $id));
			}
			echo(json_encode($arr));
			$query->close();
		}
	}
?>
