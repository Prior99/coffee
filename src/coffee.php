<?php
	require_once("content.php");
	require_once("json.php");
	require_once("contents/userlist.php");
	require_once("contents/404.php");
	require_once("contents/login.php");
	require_once("contents/buy.php");
	require_once("contents/settings.php");
	require_once("contents/admin.php");
	require_once("json/userlist.php");
	require_once("json/validatecode.php");
	require_once("json/empty.php");
	require_once("json/options.php");
	require_once("json/buy.php");
	require_once("json/products.php");
	require_once("json/import.php");
	require_once("json/export.php");
	class Coffee {
		private $dba; //Object to communicate with the database, please use Matse::db() instead for statistics!
		public $querys; //Number of query executed by now
		private $content;
		
		/*
		 * Set up configuration, user and databaseconnection
		 */
		public function __construct() {
			session_start(); //Start the session holding the logininformation (possibly)
			$this->dba = new mysqli($GLOBALS["config"]["Host"],$GLOBALS["config"]["User"],$GLOBALS["config"]["Password"]); //And setup the databaseconnection
			$this->dba -> select_db($GLOBALS["config"]["Database"]); //Select the database to read from
			echo($this->dba->error); //Echo any errors belonging to the databasesetup
			$this->checkAndInstallDatabase(); //If neccessary, generate all tables for the database
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
		 */
		private function checkAndInstallDatabase() {
			$this->db()->query(
				"CREATE TABLE IF NOT EXISTS Users(" .
					"id				INT NOT NULL AUTO_INCREMENT PRIMARY KEY," .
					"firstname		TEXT," . 
					"lastname		TEXT," .
					"password		INT," .
					"deleted		BOOLEAN DEFAULT FALSE) CHARACTER SET utf8"
			);
			$this->db()->query(
				"CREATE TABLE IF NOT EXISTS Products(" .
					"id				INT NOT NULL AUTO_INCREMENT PRIMARY KEY," .
					"name			TEXT,".
					"deleted		BOOLEAN DEFAULT FALSE) CHARACTER SET utf8"
			);
			$this->db()->query(
				"CREATE TABLE IF NOT EXISTS Transactions(" .
					"id				INT NOT NULL AUTO_INCREMENT PRIMARY KEY," .
					"user			INT," .
					"product		INT," .
					"date			INT) CHARACTER SET utf8"
			);
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
			else
				$json = new JSONEmpty($this);
			$json->printJSON();
		}
		/*
		 * Close the databaseconnection
		 */
		public function __destruct() {
			$this->dba->close();
		}
		
		public function selectContent($command) {
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
		
		public function checkPassword() {
			$user = $this->getUser();
			$password = $this->getCode();
			$query = $this->db()->prepare("SELECT id FROM Users WHERE id = ? AND (password = ? OR password IS NULL)");
			$query->bind_param("ii", $user, $password);
			$query->execute();
			$f = ($query->fetch() != null);
			$query->close();
			return $f;
		}
		
		public function getUsername() {
			$id = $this->getUser();
			if($id == -1) return null;
			else {
				$query = $this->db()->prepare("SELECT firstname, lastname FROM Users WHERE id = ?");
				$query->bind_param("i", $id);
				$query->execute();
				$query->bind_result($firstname, $lastname);
				$query->fetch();
				$query->close();
				return $firstname." ".$lastname;
			}
		}
		
		public function getCode() {
			if(isset($_GET["code"])) return $_GET["code"];
			if(!isset($_COOKIE["code"])) return -1;
			return $_COOKIE["code"];
		}
		public function getUser() {
			if(isset($_GET["user"])) return $_GET["user"];
			if(!isset($_COOKIE["user"])) return -1;
			return $_COOKIE["user"];
		}
	}
?>
