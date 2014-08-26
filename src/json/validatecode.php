<?php
	/*
	 * This API-Call tests the username and password supplied in the cookie
	 */
	class JSONValidate extends JSON
	{
		public function printJSON()
		{
			//Note: No security, invokable from global
			echo(json_encode(array("okay" => $this->coffee->checkPassword()))); //Tells the requester whether the login is okay
		}
	}
?>
