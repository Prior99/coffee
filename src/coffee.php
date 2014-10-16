<?php
	/*
	 * Include everything
	 * This might look quiet dirty, but it is more secure then a glob()-include
	 * As only those modules we really know
	 */
	require_once(__DIR__."/content.php");
	require_once(__DIR__."/json.php");
	$files = glob(__DIR__."/contents/*.php"); foreach($files as $file) require_once($file);
	$files = glob(__DIR__."/json/*.php"); foreach($files as $file) require_once($file);
	class Coffee {
		private $dba; //Object to communicate with the database, please use Matse::db() instead for statistics!
		public $querys; //Number of query executed by now
		private $content;

		/*
		 * Set up configuration, user and databaseconnection
		 */
		public function __construct() {
			session_start(); //Start the session holding the logininformation (possibly)
			$this->dba = new mysqli($GLOBALS["config"]["Host"],$GLOBALS["config"]["User"],$GLOBALS["config"]["Password"], $GLOBALS["config"]["Database"], $GLOBALS["config"]["Port"]); //And setup the databaseconnection
			echo($this->dba->error); //Echo any errors belonging to the databasesetup
			$this->checkAndInstallDatabase(); //If neccessary, generate all tables for the database
			$this->backup();
		}

		/*
		 * Returns a pointer to the global mysql databaseconnection and handles statistics
		 */
		public function db() {
			$this -> querys++; //Increase the amount of querys executed on the database
			return $this -> dba;
		}

		/*
		 * Creates all necessary tables, if they are not created already
		 *
		 * Please note, this creates the overhead of 3 empty querys on each page-load
		 * But so does not need any installation-script to be run
		 */
		private function checkAndInstallDatabase() {
			$this->db()->query(
				"CREATE TABLE IF NOT EXISTS Users(" .
					"id				INT NOT NULL AUTO_INCREMENT PRIMARY KEY," .
					"short			TEXT,".
					"firstname		TEXT," .
					"lastname		TEXT," .
					"password		INT," .
					"mail			TEXT," .
					"send_mails		BOOLEAN DEFAULT TRUE," .
					"locked			BOOLEAN DEFAULT FALSE,".
					"login_failures INT DEFAULT 0) CHARACTER SET utf8"
			);
			$this->db()->query(
				"CREATE TABLE IF NOT EXISTS Products(" .
					"id				INT NOT NULL AUTO_INCREMENT PRIMARY KEY," .
					"name			TEXT,".
					"price			FLOAT,".
					"deleted		INT DEFAULT 0) CHARACTER SET utf8"
			);
			$this->db()->query(
				"CREATE TABLE IF NOT EXISTS Transactions(" .
					"id				INT NOT NULL AUTO_INCREMENT PRIMARY KEY," .
					"user			INT," .
					"product		INT," .
					"date			INT) CHARACTER SET utf8"
			);
			$this->db()->query(
				"CREATE TABLE IF NOT EXISTS Backups(" .
					"id				INT NOT NULL AUTO_INCREMENT PRIMARY KEY," .
					"created		INT," .
					"file			TEXT) CHARACTER SET utf8"
			);
		}

		/*
		 * Will perform a backup
		 */
		public function backup() {
			$time = time(); //Current time
			$query = $this->db()->prepare("SELECT created FROM Backups ORDER BY created DESC LIMIT 1");//Get the date of the last Backup performed
			$query->execute();
			$query->bind_result($last_date);
			if($query->fetch()) { //If there was at least one backup
				$diff = $time - $last_date;
				if($diff < $GLOBALS["config"]["Backupfrequency"]) { //And it is younger than configured, just return and do not backup
					$query->close();
					return;
				}
			}
			$query->close();

			$filename = "backups/backup_".date("y_m_d_H_i_s", $time).".sql"; //Filename to save backup to
			$file = fopen($filename, "w"); //Open file for writing
			if($file) { //IF file could be opened
				/*
				 * Backup Transactions
				 */
				$query = $this->db()->prepare("SELECT id, user, product, date FROM Transactions");
				$query->execute();
				$query->bind_result($id, $user, $product, $date);
				fwrite($file, "INSERT INTO Transactions(id, user, product, date) VALUES\n");
				if($query->fetch()) {
					fwrite($file, "($id, $user, $product, $date)");
					while($query->fetch()) {
						fwrite($file, ", \n($id, $user, $product, $date)");
					}
					fwrite($file, ";\n\n");
				}
				$query->close();
				/*
				 * Backup Users
				 */
				$query = $this->db()->prepare("SELECT id, short, firstname, lastname, IFNULL(password, 'NULL'), mail, send_mails, locked, login_failures FROM Users");
				$query->execute();
				$query->bind_result($id, $short, $firstname, $lastname, $password, $mail, $send_mails, $locked, $login_failures);
				fwrite($file, "INSERT INTO Users(id, short, firstname, lastname, password, mail, send_mails, locked, login_failures) VALUES\n");
				if($query->fetch()) {
					fwrite($file, "($id, '$short', '$firstname', '$lastname', $password, '$mail', $send_mails, $locked, $login_failures)");
					while($query->fetch()) {
						fwrite($file, ", \n($id, '$short', '$firstname', '$lastname', $password, '$mail', $send_mails, $locked, $login_failures)");
					}
					fwrite($file, ";\n\n");
				}
				$query->close();
				/*
				 * Backup Products
				 */
				$query = $this->db()->prepare("SELECT id, name, price, deleted FROM Products");
				$query->execute();
				$query->bind_result($id, $name, $price, $deleted);
				fwrite($file, "INSERT INTO Products(id, name, price, deleted) VALUES\n");
				if($query->fetch()) {
					fwrite($file, "($id, '$name', $price, $deleted)");
					while($query->fetch()) {
						fwrite($file, ", \n($id, '$name', $price, $deleted)");
					}
					fwrite($file, ";\n\n");
				}
				$query->close();
				fclose($file); //Close File

				$query = $this->db()->prepare("INSERT INTO Backups(created, file) VALUES(?, ?)"); //Create new entry in database for backup
				$query->bind_param("is", $time, $filename);
				$query->execute();
				$query->close();
				//Delete backups older than allowed
				$killdate = $time - $GLOBALS["config"]["Backuplifetime"]; //Calculate date
				$query = $this->db()->prepare("SELECT file FROM Backups WHERE created < ?");
				$query->bind_param("i", $killdate);
				$query->execute();
				$query->bind_result($killfile);
				while($query->fetch()) {
					@unlink($killfile); //Delete files
				}
				$query->close();
				$query = $this->db()->prepare("DELETE FROM Backups WHERE created < ?"); //Remove entries from database
				$query->bind_param("i", $killdate);
				$query->execute();
				$query->close();
			}
			else {
				$this->mail($GLOBALS["config"]["Mastermail"], "Backup gescheitert",
					"Hallo,\n\n".
					"ein geplantes Backup konnte nicht ausgeführt werden!\n\n".
					"Bis bald,\n".
					"Ihre Kaffee-Maschine");
			}
		}

		/*
		 * Prints the HTML generated by this class
		 */
		public function printHTML() {
			if(isset($_GET["help"]) && $_GET["help"] == true) {
				echo("<h1>Hilfe</h1>");
				$this->content->printHelp();
				echo("<br /><a href='?action=" . $_GET["action"] ."'>Zurück</a>");
			}
			else {
				$this->content->printHTML();
			}
		}

		/*
		 * Prints the HTML generated by this class
		 */
		public function printTitle() {

			$this->content->printTitle();
		}


		/*
		 * Prints the generated JSON
		 */
		public function printJSON($command) {
			/*
			 * Switch through all all API-Request-Codes
			 * And display the class corresponding to the respective code.
			 *
			 * A hashmap might have been also possibly but this opens up
			 * securityissues on has-collision-attacks
			 * Also it is not so much more code to write and more memory-efficient (ha-ha)
			 */
			if($command == "userlist")
				$json = new JSONUserlist($this);
			else if($command == "validate")
				$json = new JSONValidate($this);
			else if($command == "buy")
				$json = new JSONBuy($this);
			else if($command == "products")
				$json = new JSONProducts($this);

			else if($command == "options")
				$json = new JSONOptions($this);
			else if($command == "import")
				$json = new JSONImport($this);
			else if($command == "export")
				$json = new JSONExport($this);
			else if($command == "delete")
				$json = new JSONDelete($this);
			else if($command == "add")
				$json = new JSONAdd($this);
			else if($command == "codereset")
				$json = new JSONCodeReset($this);
			else if($command == "product_add")
				$json = new JSONProductAdd($this);
			else if($command == "product_delete")
				$json = new JSONProductDelete($this);
			else if($command == "stats")
				$json = new JSONStats($this);
			else if($command == "pay")
				$json = new JSONPay($this);
			else if($command == "saldo")
				$json = new JSONSaldo($this);
			else if($command == "send_mails")
				$json = new JSONSendMails($this);
			else if($command == "lock")
				$json = new JSONLock($this);
			else if($command == "unlock")
				$json = new JSONUnlock($this);
			else if($command == "code_everyone")
				$json = new JSONCodeEveryone($this);
			else if($command == "get_locked")
				$json = new JSONLockedUsers($this);
			else if($command == "get_backups")
				$json = new JSONBackups($this);
			else if($command == "download_backup")
				$json = new JSONDownloadBackup($this);
			else if($command == "backup_load")
				$json = new JSONBackupLoad($this);
			else if($command == "nudge")
				$json = new JSONNudge($this);
			else
				$json = new JSONEmpty($this);
			$json->printJSON();
		}
		/*
		 * Close the databaseconnection
		 */
		public function __destruct() {
			$this->dba->close(); //Close Databaseconenction on destructor
		}

		public function selectContent($command) {
			/*
			 * For more detailed documenattion look at selectJSON(), this does quiet the same but with the pages, not the API-Requests
			 */
			if($command == "userlist")
				$content = new ContentUserlist($this);
			else if($command == "login")
				$content = new ContentLogin($this);
			else if($command == "buy")
				$content = new ContentBuy($this);
			else if($command == "settings")
				$content = new ContentSettings($this);
			else if($command == "admin")
				$content = new ContentAdmin($this);
			else
				$content = new Content404($this);
			$this->content = $content;
		}

		/*
		 * Checks if the login supplied in the cookie is correct
		 */
		public function checkPassword() {
			$user = $this->getUser(); //Get userid cookie
			$password = $this->getCode(); //Get password cookie
			//Check if a user with this id and password is present
			$query = $this->db()->prepare("SELECT id FROM Users WHERE id = ? AND (password = ? OR password IS NULL) AND locked = false");
			$query->bind_param("ii", $user, $password);
			$query->execute();
			$f = ($query->fetch() != null);
			$query->close();
			return $f;
		}

		/*
		 * Will reset the amount of failed logins
		 */
		public function resetLoginFailures() {
			$user = $this->getUser(); //Get userid cookie
			//Reset failed loginattempts
			$query = $this->db()->prepare("UPDATE Users SET login_failures = 0 WHERE id = ?");
			$query->bind_param("i", $user);
			$query->execute();
			$query->close();
		}

		/*
		 * Called each time a user logs in with a wrong pin
		 */
		public function increaseLoginFailures() {
			$user = $this->getUser(); //Get userid cookie
			//Increase failed loginattempts by one
			$query = $this->db()->prepare("UPDATE Users SET login_failures = login_failures + 1 WHERE id = ?");
			$query->bind_param("i", $user);
			$query->execute();
			$query->close();
			//Check how often the login was attempted by now
			$query = $this->db()->prepare("SELECT login_failures FROM Users WHERE id = ?");
			$query->bind_param("i", $user);
			$query->execute();
			$query->bind_result($fails);
			$query->fetch();
			$query->close();
			//If greater than 3, lock the account
			if($fails >= 3) {
				$this->resetLoginFailures();
				$this->lockUser($this->getUser());
			}
			return $fails;
		}

		public function mail($to, $head, $body) {
			mail($to, $head, $body,
				"Content-type: text/plain; charset=utf-8\n".
				"From: Kaffee-Maschine <".$GLOBALS["config"]["Mastermail"].">"
			);
		}

		/*
		 * Will lock a user so he can no longer access his account
		 */
		public function lockUser($id) {
			$query = $this->db()->prepare("UPDATE Users SET locked = true WHERE id = ?");
			$query->bind_param("i", $id);
			$query->execute();
			$query->close();
			$this->mail($this->getMail($id), "Kaffee-Konto gesperrt",
				"Hallo,\n\n".
				"Ihr Kaffee-Konto wurde gesperrt.\n".
				"Bitte kontaktieren Sie ein Mitglied der Kaffee-AG.\n\n".
				"Bis bald,\n".
				"Ihre Kaffee-Maschine");
			$this->mail($GLOBALS["config"]["Mastermail"], "Kaffee-Konto gesperrt",
				"Hallo,\n\n".
				"Soeben wurde das folgende Kaffee-Konto gesperrt:\n".
				"Datenbank-ID:".$id."\n".
				"E-Mail:".$this->getMail($id)."\n".
				"Kuerzel:".$this->getShortageOf($id)."\n".
				"Name:".$this->getUsernameOf($id)."\n\n".
				"Bis bald,\n".
				"Ihre Kaffee-Maschine");
		}

		/*
		 * Returns the respect shortage to a users id
		 */
		public function getShortageOf($id) {
			$query = $this->db()->prepare("SELECT short FROM Users WHERE id = ?");
			$query->bind_param("i", $id);
			$query->execute();
			$query->bind_result($short);
			$query->fetch();
			$query->close();
			return $short;
		}

		/*
		 * Will unlock a user that was previously locked
		 */
		public function unlockUser($id) {
			$query = $this->db()->prepare("UPDATE Users SET locked = false WHERE id = ?");
			$query->bind_param("i", $id);
			$query->execute();
			$query->close();
			$this->mail($this->getMail($id), "Kaffee-Konto entsperrt",
				"Hallo,\n\n".
				"Ihr Kaffee-Konto wurde reaktiviert.\n".
				"Ihr Konto ist nun wieder verwendbar.\n\n".
				"Bis bald,\n".
				"Ihre Kaffee-Maschine");
		}

		/*
		 * Will check whether a user is currently locked or not
		 */
		public function isUserLocked($id) {
			$query = $this->db()->prepare("SELECT locked FROM Users WHERE id = ?");
			$query->bind_param("i", $id);
			$query->execute();
			$query->bind_result($f);
			$query->fetch();
			$query->close();
			return $f;
		}

		/*
		 * Returns the username based on the userid of the cookie supplied
		 */
		public function getUsername() {
			$id = $this->getUser(); //Get the userid from cookie
			if($id == -1) return null; //If no cookie is present, return null as noone is logged in
			else {
				//Select name corresponding to id
				$query = $this->db()->prepare("SELECT firstname, lastname FROM Users WHERE id = ?");
				$query->bind_param("i", $id);
				$query->execute();
				$query->bind_result($firstname, $lastname);
				$query->fetch();
				$query->close();
				return $firstname." ".$lastname;
			}
		}
		/*
		 * Returns the phoneticalname to a given database userid
		 */
		public function getUsernameOf($id) {
			//Select name corresponding to id
			$query = $this->db()->prepare("SELECT firstname, lastname FROM Users WHERE id = ?");
			$query->bind_param("i", $id);
			$query->execute();
			$query->bind_result($firstname, $lastname);
			$query->fetch();
			$query->close();
			return $firstname." ".$lastname;
		}

		/*
		 * Dirty reverse-resolve of userid for first- and lastname
		 */
		public function getUserIDOf($first, $last) {
			//Select id for supplied first- and lastname
			$query = $this->db()->prepare("SELECT id FROM Users WHERE firstname = ? AND lastname = ?");
			$query->bind_param("ss", $first, $last);
			$query->execute();
			$query->bind_result($id);
			if(!$query->fetch()) $id = -1;
			$query->close();
			return $id;
		}

		/*
		 * Dirty reverse-resolve of userid by name-shortage
		 */
		public function getUserIDOfShort($short) {
			//Select id by nameshortage from database
			$query = $this->db()->prepare("SELECT id FROM Users WHERE short = ?");
			$query->bind_param("s", $short);
			$query->execute();
			$query->bind_result($id);
			if(!$query->fetch()) $id = -1;
			$query->close();
			return $id;
		}

		/*
		 * Get mailadress by userid
		 */
		public function getMail($user) {
			$query = $this->db()->prepare("SELECT mail FROM Users WHERE id = ?");
			$query->bind_param("i", $user);
			$query->execute();
			$query->bind_result($mail);
			$query->fetch();
			$query->close();
			return $mail;
		}

		/*
		 * Will return the code (password) stored in the cookie
		 * As -1 is not a possibly code it will be returned if no
		 * cookie is set
		 */
		public function getCode() {
			if(isset($_GET["code"])) return $_GET["code"];
			if(!isset($_COOKIE["code"])) return -1;
			return $_COOKIE["code"];
		}
		/*
		 * Will return the userid stored in the cookie
		 * As -1 is not a possibly userid it will be returned if no
		 * cookie is set
		 */
		public function getUser() {
			if(isset($_GET["user"])) return $_GET["user"];
			if(!isset($_COOKIE["user"])) return -1;
			return $_COOKIE["user"];
		}
	}
?>
