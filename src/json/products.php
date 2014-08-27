<?php
	/*
	 * This API-Call returns a list of all buyable products and their price
	 * /!\ as well as (and this is notable) the amount of times the user already
	 * bought this product this month. This is why it is a protected call.
	 */
	class JSONProducts extends JSON
	{
		public function printJSON()
		{
			if($this->coffee->checkPassword()) { //Check whether the password matches
				$now = new DateTime(); //new timestamp to gather the current month
				$time = mktime(0, 0, 0, $now->format("m"), 1, $now->format("Y")); //Get corresponding unixtimestamp
				$user = $this->coffee->getUser();
				/*
				 * Readable version of SQL-Query used below
				 * SELECT
				 * 		p.price AS price,
				 * 		p.name AS name,
				 * 		p.id AS id,
				 * 		COUNT(t.id) AS amount
				 * FROM Products p
				 * LEFT JOIN Transactions t
				 * 		ON t.product = p.id AND t.user = ? AND t.date >= ?
				 * WHERE p.deleted = FALSE
				 * GROUP BY p.id
				 */
				$query = $this->coffee->db()->prepare("SELECT p.price AS price, p.name AS name, p.id AS id, COUNT(t.id) AS amount FROM Products p LEFT JOIN Transactions t ON t.product = p.id AND t.user = ? AND t.date >= ? WHERE p.deleted = FALSE GROUP BY p.id");
				$query->bind_param("ii", $user, $time);
				$query->execute();
				$query->bind_result($price, $name, $id, $amount);
				$arr = array();
				while($query->fetch()) {
					//The old array-of-structs php-to-json-object-converting-stuff you will find in allmost every API-Call in this software
					array_push($arr, array("name" => $name, "id" => $id, "amount" => $amount, "price" => $price));
				}
				echo(json_encode($arr));
				$query->close();
			}
		}
	}
?>
