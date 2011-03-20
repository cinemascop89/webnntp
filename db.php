<?php
	
	include_once 'constants.php';

	class DataBase {
		/**
		 * Stores the host name
		 * @var string
		 */
		private $host;
		/**
		 * Stores the username
		 * @var string
		 */
		private $dbUser;
		/**
		 * Stores the password
		 * @var string
		 */
		private $dbPass;
		/**
		 * Stores the database name
		 * @var string
		 */
		private $dbName;
		/**
		 * Stores the connection status
		 * @var boolean
		 */
		public $dbConn;
		/**
		 * Stores the error details
		 * @var array
		 */
		public $error;
		
		/**
		 * Run when the class is created. Sets up the variables
		 * @param string $host Database host address
		 * @param string $dbUser Database username
		 * @param string $dbPass Database password
		 * @param string $dbName Database name
		 * @return void
		 */
		function __construct($host=DB_HOST,$dbUser=DB_USER,$dbPass=DB_PASS,$dbName=DB_NAME) {
			$this->host = $host;
			$this->dbUser = $dbUser;
			$this->dbPass = $dbPass;
			$this->dbName = $dbName;
			
			$this->connectToDb();
		}
		
		/**
		 * Internal command to connect to the database
		 * @return void
		 */
		private function connectToDb () {
			if (!$this->dbConn = @mysql_connect($this->host, $this->dbUser, $this->dbPass)) {
				$this->handleError();
				return false;
			}
			else if ( !@mysql_select_db($this->dbName, $this->dbConn) ) {
				$this->handleError();
				return false;
			}
		}
		
		/**
		 * Internal command to handle errors and populate the error variable
		 * @return void
		 */
		private function handleError () {
			$this->error = mysql_error($this->dbConn);
		}
		
		/**
		 * Command which runs queries. Returns a class on success and flase on error
		 * @param string $sql The SQL query to run
		 * @return class
		 * @uses lastfmApiDatabase_result
		 */
		public function query($sql) {
			if ( !$queryResource = mysql_query($sql, $this->dbConn) ) {
				echo mysql_error();
				$this->handleError();
				return false;
			}
			else {
				$return = $queryResource;
				return $return;
			}
		}
		
		public function fetch($query_result){
			return mysq_fetch_array($query_result);
		}
	}

?>