<?php
	/*
	 * Wow! Most complex API-Call in the whole software
	 * I hope, you understand, what it does
	 */
	class JSONEmpty extends JSON
	{
		public function printJSON()
		{
			/*
			 * Global API-Call, needs no security
			 */
		}
	}

	//(No really, this prevents a 404 on an invalid API-Call-Id)
?>
