<?php
	/*
	 * This API-Call (A Admin-API-Call and therefore secured by validating the admin-cookie)
	 * Will return a list of all backups known to the system
	 */
	class JSONDownloadBackup extends JSON
	{
		public function printJSON()
		{
			if(isset($_COOKIE["admin"]) && $_COOKIE["admin"] == $GLOBALS["config"]["Masterpassword"]) { //Check and validate admin-cookie
				$id = $_GET["id"];
				if(isset($id)) {
					$query = $this->coffee->db()->prepare("SELECT created, file FROM Backups WHERE id = ?");
					$query->bind_param("i", $id);
					$query->execute();
					$query->bind_result($created, $filename);
					if($query->fetch() && file_exists($filename) && is_file($filename)) {	
						header('Content-type: text/sql; charset=utf-8');
						header('Content-Disposition: attachment; filename="coffee_backup_' . date("y_m_d_H_i_s", $created) . '.sql"');	
						readfile($filename);
					}
					$query->close();
				}
			}
		}
	}
?>
