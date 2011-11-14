<?php
	
	abstract class Model{
		
		public $db;
		
		public $attributes;
		
		public $new;
		
		
		public function  Model($new = false){
			
			$this->db = new DataBase();
			$this->attributes = array();
			$this->new = false;
		}
		
		public function __get($param) {
			try {
				return $this->attributes[$param];		 
					
			} catch (Exception $e) {
				throw new BadMethodCallException("Inexistent attribute $param");
			}
		}
		
		public function __set($name, $value){
			if (array_key_exists($name, $this->attributes))
				return $this->attributes[$name]=$value;
			else 
				throw new BadMethodCallException("Inexistent attribute $name");
		}
		
		public function attributes(){
			
			foreach (func_get_args() as $attr){
				
				if (is_string($attr)){
					$this->attributes[$attr] = '';
				}
			}
			
		}
		
		

		public function save(){
			
			//$this->db->connectToDB();
			
			$table_name = strtolower(get_class()).'s';
			$attrs = '';
			$values='';
			$first=true;
			foreach ($this->attributes as $name => $value){
				if ($first){
					$attrs="'$name'";
					$values="'$value'";
				}else{
					$attrs .= ",'$name'";
					$values .= ",'$value'";
				}
				$first=false;
				
			}
			
			$query = "INSERT INTO `$table_name` ($attrs) VALUES ($values)";
			echo "$query\n";
			
		}
	}
?>