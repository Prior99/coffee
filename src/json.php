
<?php
	/*
	 * Prototype for all JSON
	 * Contains a pointer to the main class of the program
	 * And defines the default constructor
	 */
	class JSON
	{
		protected $coffee;//Pointer to the main class of the program
		public function __construct($coffee) //Default constructor
		{
			$this->coffee = $coffee;//Save pointer
		}
	}
?>
