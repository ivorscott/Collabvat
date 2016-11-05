<?php
class DB {
	public static $instance = null;

	private function __construct(){}

	public static function getInstance() {
	
		if( !isset( self::$instance ) ) 
		{
			self::$instance = new PDO( 

			// Database credentails

				'mysql:host=' . Config::get('mysql/host')
				 . ';dbname=' . Config::get('mysql/db'),
				 				Config::get('mysql/username'),
				 				Config::get('mysql/password'));
		}
		
		return self::$instance;
	}
}