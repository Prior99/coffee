<?php
	/*
	 * This API-Call manages buying a product and sending a mail if the user requested so
	 */
	class JSONBuy extends JSON
	{
		public function printJSON()
		{
			if($this->coffee->checkPassword()) { //Check if the users login is valid
				$user = $this->coffee->getUser();
				$array = json_decode($_GET["info"]); //Info is a array of objects containing the name, id and amount of the product bought
				$string = "Hallo,\n\n".
					"Soeben wurde folgende Kaffee-Bestellung entgegengenommen:\n"; //Prepare the string for the mail to send
				$amount = 0;
				foreach($array as $p) { //Iterate over each product the user wants to buy
					$string .= $p->name . " x" . $p->bought."\n"; //Append line to mail
					/*
					 * The following seems a biiit stupid.
					 * And it really is. To a certain point.
					 * You see, when I first planned this software it was totally
					 * smart to have one row in the database for each product
					 * So if 5 coffees are bought, we add 5 rows, not 1 row
					 * with with amount=5 or similiar.
					 * This was because they told me "Yeah the user just presses the button and then it is inserted into the
					 * database. It's just that simple and you need to do it in less than 2 weeks"
					 *
					 * Then the same guy returned 1 day after I finished the design and half the implementation and told me
					 * it had to be possible to buy 3 coffees at once. But I wanted to keep this software transaction-safe,
					 * it still did make sense to a certain point as I called this API once for each coffee as I implemented
					 * the multiple-cofees in the client-side.
					 * I realised this caused very much network-traffic but at least I could check it the following way
					 * Call API -> Returned True -> Decrease Counter by one -> Call API -> Returned True -> Decrease ... and so on
					 * This way it was somehow transaction safe and I had no headaches rewriting 40% of the code.
					 *
					 * Time passed on, release came nearer and then out of sudden on week before release, weeks after testing everything
					 * The same guy came and told me some other guy wanted a mail sent to him each time he bought something.
					 * Okay, this really fucked everything up.
					 * As this API-Call was called for each product then he would receive 5 emails if he bought 5 coffees.
					 * So I had to send all coffees in ONE transaction.
					 * This time I did emotionally REFUSE to rewrite the WHOLE Database-design, half of the code and review
					 * and debug and verify everything again.
					 * And this is why I ended up with this loop.
					 *
					 * It is gross. I know. And I feel guilty about it.
					 */
					for($i = 0; $i < $p->bought; $i++) {
						$product = $this->coffee->getProduct($p->id);
						$query = $this->coffee->db()->prepare("INSERT INTO Transactions(user, product, date, price) VALUES(?, ?, ?, ?)");
						$time = time();
						$negprice = $product["price"] * -1.;
						$query->bind_param("iiid", $user, $p->id, $time, $negprice);
						$query->execute();
						$query->close();
						$amount++;
					}
				}
				$hour = date("H");
				if($hour < 6 || $hour >= 18) {
					$this->coffee->mail($GLOBALS["config"]["Mastermail"], "Kaffee-Kauf außerhalb Betriebszeiten",
					"Hallo,\n\n".
					"Soeben wurde ein Kaffee-Kauf außerhalb der Betriebszeiten der Kaffee-Maschine (vor 07:00 oder nach 18:00) getätigt.\n".
					"Der Käufer war: ".$this->coffee->getUsername()."\n\n".
					"Bis bald,\n".
					"Ihre Kaffee-Maschine");
				}
				//Nice greetings at end of mail
				if($amount > 0) {
					$string .= "\n".
					"Bis bald,\n".
					"Ihre Kaffeemaschine\n";
					//Look, if the user wants to receive mails
					$query = $this->coffee->db()->prepare("SELECT send_mails FROM Users WHERE id = ?");
					$query->bind_param("i", $user);
					$query->execute();
					$query->bind_result($send);
					$query->fetch();
					$query->close();
					if($send) { //If so, send it.
						$this->coffee->mail($this->coffee->getMail($user), "Ihr Kaffee-Kauf", $string);
					}
				}
			}
		}
	}
?>
