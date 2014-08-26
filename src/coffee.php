<?php
	/*
	 * Include everything
	 * This might look quiet dirty, but it is more secure then a glob()-include
	 * As only those modules we really know
	 */
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
	require_once("json/delete.php");
	require_once("json/add.php");
	require_once("json/product_add.php");
	require_once("json/product_delete.php");
	require_once("json/codereset.php");
	require_once("json/stats.php");
	require_once("json/pay.php");
	require_once("json/saldo.php");
	require_once("json/send_mails.php");
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
					"send_mails		BOOLEAN," .
					"locked			INT,".
					"login_failures INT) CHARACTER SET utf8"
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
			$query = $this->db()->prepare("SELECT id FROM Users WHERE id = ? AND (password = ? OR password IS NULL)");
			$query->bind_param("ii", $user, $password);
			$query->execute();
			$f = ($query->fetch() != null);
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
