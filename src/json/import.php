<?php
	/*
	 * This API-Call imports users from an CSV-File
	 * It is an Admin-API-Call and therefore checks the admin-cookie
	 */
	class JSONImport extends JSON
	{
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) { //Security
				$file = $_FILES["file"]; //Grab the uploaded file
				$lines = file($file["tmp_name"]); //Open the file for reading
				$skipped = 0; //Amount of lines skipped (because user already existed)
				$inserted = 0; //Amounts of lines successfully inserted into database
				$total = 0; //Total amount of lines read
				$invalid = 0; //Invalid formatted lines
				$inv_arr = Array(); //Array containing the numbers of all invalid lines to give the user info to debug his (her) csv-file
				foreach($lines as $i => $line) {
					$total++;//A line was read so increase total
					if($total == 1) { //If this is the first row at all, skip it, as it is the headline
						continue;
					}
					$line = trim(str_replace("\r", "", str_replace("\n", "", $line))); //Trim the line (remove linebreaks and trailing spaces)
					$cols = explode(";", $line);//Now convert the ;-seperated file to an array
					if(count($cols) == 2) { //Must be exact 2 lines
						//TODO: Add parsing of shoartage and mail
						$firstname = trim($cols[1]);
						$lastname = trim($cols[0]);
						//Look if this user already exists
						$query = $this->coffee->db()->prepare("SELECT id FROM Users WHERE firstname = ? AND lastname = ?");
						$query->bind_param("ss", $firstname, $lastname);
						$query->execute();
						$f = $query->fetch();
						$query->close();
						//$f indicates whether the user already existed
						if(!$f) {
							$inserted++; //If not, we inserted it
							//Insert it
							$query = $this->coffee->db()->prepare("INSERT INTO Users(firstname, lastname, password) VALUES(?, ?, NULL)");
							$query->bind_param("ss", $firstname, $lastname);
							$query->execute();
							$query->close();
						}
						else {
							$skipped++; //If it existed, it was skipped
						}
					}
					else {
						$invalid++; //To much/few columns -> invalid line
						array_push($inv_arr, $line);
					}
				}
				//Echo the user info about the progress
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
