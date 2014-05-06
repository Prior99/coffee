<?php
	/*
	 * Prototype for all Contents
	 * Contains a pointer to the main class of the program
	 * And defines the default constructor
	 */
	class Content
	{
		protected $coffee;//Pointer to the main class of the program
		public function __construct($coffee) //Default constructor
		{
			$this->coffee = $coffee;//Save pointer
		}
	}
?>
