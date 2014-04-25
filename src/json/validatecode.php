<?php
	class JSONValidate extends JSON
	{		
		public function printJSON()
		{
			$user = $_GET["user"];
			$password = $_GET["code"];
			$query = $this->coffee->db()->prepare("SELECT id FROM Users WHERE id = ? AND password = ?");
			$query->bind_param("si", $user, $password);
			$query->execute();
			$query->bind_result($id);
			$okay = $query->fetch() != null;
			echo(json_encode(array("okay" => $okay)));
		}
	}
?>
