<?php
	class JSONImport extends JSON
	{		
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) {
				$file = $_FILES["file"];
				$lines = file($file["tmp_name"]);
				$skipped = 0;
				$inserted = 0;
				$total = 0;
				$invalid = 0;
				$inv_arr = Array();
				foreach($lines as $i => $line) {
					$total++;
					if($total == 1) {
						continue;
					}
					$line = trim(str_replace("\r", "", str_replace("\n", "", $line)));
					$cols = explode(";", $line);
					if(count($cols) == 2) {
						$firstname = trim($cols[1]);
						$lastname = trim($cols[0]);
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
						array_push($inv_arr, $line);
					}
				}
				$arr = Array("total" => $total,
					"skipped" => $skipped,
					"inserted" => $inserted,
					"invalid" => $invalid,
					"invalids" => $inv_arr
				);
				echo(json_encode($arr));
			}
		}
	}
?>
