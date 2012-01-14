<?php
	
	require_once 'mysql.php';
	
	class DB {
		
		static $driver;
		
		public static function getConnection() {
			if (!self::$driver)
				self::loadDriver();
			return self::$driver;
		} 
		
		private static function loadDriver(){
			$name = Model::$config['db.driver'];
			switch ($name){
				case 'mysql':
					self::$driver = new  MySQL( Model::$config['db.host'],
										Model::$config['db.name'],
										Model::$config['db.user'],
										Model::$config['db.password']);;
			}
		}
	}
?>