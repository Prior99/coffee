<?php
	class JSONImport extends JSON
	{		
		public function printJSON()
		{
			$file = $_FILES["file"];
			$lines = file($file["tmp_name"]);
			$skipped = 0;
			$inserted = 0;
			$total = 0;
			$invalid = 0;
			foreach($lines as $i => $line) {
				$total++;
				if($total == 1) {
					continue;
				}
				$cols = explode(";", $line);
				if(count($cols) == 2) {
					$firstname = $cols[1];
					$lastname = $cols[0];
					$query = $this->coffee->db()->prepare("SELECT id FROM Users WHERE firstname = ? AND lastname = ?");
					$query->bind_param("ss", $firstname, $lastname);
					$query->execute();
					$f = $query->fetch();
					$query->close();
					if(!$f) {
						$inserted++;
						$query = $this->coffee->db()->prepare("INSERT INTO Users(firstname, lastname, password) VALUES(?, ?, NULL)");
						$query->bind_param("ss", $firstname, $lastname);
						$query->execute();
						$query->close();
					}
					else {
						$skipped++;
					}
				}
				else {
					$invalid++;
				}
			}
			$arr = Array("total" => $total,
				"skipped" => $skipped,
				"inserted" => $inserted,
				"invalid" => $invalid
			);
			echo(json_encode($arr));
		}
	}
?>
