<?php

	class TableField {
		
		protected $value = null;
		
		public function toSQL($val = null) {
			$value = $val==null ? $this->value : $val;
			return $this->_toSQL($value);
		}
		public function set($value){
			$this->value = $value;
		}
		
		public function get(){
			return $this->value;
		}
		
		
	}
	
	class IntegerField extends TableField {
		
		public function __construct($value = 0) {
			$this->value = $value;
		}
		
		protected function _toSQL($value) {
			return strval($value);
		}
		
		public function set($value){
			$this->value = intval($value);
		}
	}
	
	class StringField extends TableField{
		
		private $max_len;
		
		public function __construct($max_len, $value = '') {
			$this->value = $value;
			$this->max_len = $max_len;
		}
		
		protected function _toSQL($value) {
			return "'".mysql_real_escape_string($value)."'";
		}
		
		public function set($value){
			$this->value = substr(strval($value), 0, $this->max_len);
		}
	}
	
	class BooleanField extends TableField{
		
		public function __construct($value = false) {
			$this->value = $value;
		}
		
		protected function _toSQL($value){
			return $value ? 'TRUE' : 'FALSE';
		}
		
		public function set($value){
			$this->value = $value == true;
		}
	}
	
	class DateTimeField extends TableField{
		
		public function __construct($value = null) {
			if (!$value)
				$this->value = new DateTime();
		}
		
		public function set($value) {
			if (is_string($value))
				$this->value = new DateTime($value);
			else
				$this->value = $value;
		}
		
		protected function _toSQL($value) {
			return date("'Y-m-d H:i:s'", $value->getTimestamp());
		}
	}
	
	
?>