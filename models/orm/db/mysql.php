<?php
	
	class MySQL {
	
		private static $connection = null;
		
		private $host, $db, $user, $password;
		
		public function __construct() {
			$this->host = Model::$config['db.host'];
			$this->db = Model::$config['db.name'];
			$this->user = Model::$config['db.user'];
			$this->password = Model::$config['db.password'];
		}
		
		public function connect() {
			if (!$this->connection)
				$this->connection = mysql_connect ($this->host, 
													$this->user, 
													$this->password);	
		}
		
		public function select_db($name){
			return mysql_select_db($name, $this->connection);
		}
		
		public function execute($query){
			return mysql_query($query, $this->connection);
		}
		
		public function fetch_result($result){
			return mysql_fetch_array($result, MYSQL_ASSOC);
		}
		
		public function error() {
			return mysql_error($this->connection);
		}
		
		public function __destruct() {
			mysql_close($this->connection);
		}
	}
	
?>