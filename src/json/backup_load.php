<?php
	/*
	 * This API-Call imports users from an CSV-File
	 * It is an Admin-API-Call and therefore checks the admin-cookie
	 */
	class JSONBackupLoad extends JSON
	{
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) { //Security
				$file = $_FILES["file"]; //Grab the uploaded file
				$handle = fopen($file["tmp_name"], "r");
                if($handle) {
                    $sql = "";
                    while(($l = fgets($handle)) !== false) {
                        $sql .= $l;
                    }
                    fclose($handle);
                    $sql = explode("\n\n", $sql);
                    print_r($sql);
                    print_r("Importing Transactions...");
                    $this->coffee->db()->query($sql[0]);
                    print_r($this->coffee->db()->error);
                    print_r("Importing Users...");
                    $this->coffee->db()->query($sql[1]);
                    print_r($this->coffee->db()->error);
                    print_r("Importing Products...");
                    $this->coffee->db()->query($sql[2]);
                    print_r($this->coffee->db()->error);
                }
                else {
                    print_r("Error opening uploaded file for reading.");
                }
			}
		}
	}
?>
