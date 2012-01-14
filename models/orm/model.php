<?php
	
	require_once 'schema.php';
	require_once 'db/driver.php';
	
	abstract class Model{
		
		private $schema = null, $db = null;
		private $attributes, $new;
		
		protected $foreignKey;
		protected $hasMany = array();

		
		static $config = array(
			'db.host'	=> 'localhost',
			'db.name' 	=> 'webnntp',
			'db.user'	=> 'dbuser',
			'db.password' => 'listenthat',
			'db.driver' => 'mysql'
		);
		
		
		public function  Model($new = true, $schema = null, $db = null){
			$model_name = get_class($this);
			$this->schema = $schema ? $schema : new ModelSchema($model_name);
			$this->db = $db ? $db : DB::getConnection();
			$this->attributes = $this->schema->fetchAttributes();
			$this->foreignKey = $this->schema->key();
			$this->new = $new;
		}
		
		
		public function __get($param) {
			try {
				if (!isset($this->attributes[$param])){
					$model=$this->_findModel($param);
					$has = Model::find($model, array(strtolower(get_class($this))."_id" => $this->{$this->foreignKey}));
					$this->attributes[$param] = array('field' => $has);
				}
				$field = $this->attributes[$param]['field'];
				if ($field instanceof TableField)
					return $field->get();
				else 
					return $field;					
			} catch (Exception $e) {
				throw new BadMethodCallException("Inexistent attribute '$param'");
			}
		}
		
		public function __set($name, $value){
			if (array_key_exists($name, $this->attributes))
				return $this->attributes[$name]['field']->set($value);
			else 
				throw new BadMethodCallException("Inexistent attribute $name");
		}
		
		

		public function save(){
			
			//$this->db->connectToDB();
			
			$table_name = $this->schema->table_name();
			$attrs = array();
			$values= array();
			//print_r($this->attributes);
			foreach ($this->attributes as $key => $value){
				if ($key != $this->foreignKey && $value['field'] instanceof TableField){
					if ($this->new){
						$attrs[] = $key;
						$values[] = $value['field']->toSQL();
					}else{
						$attrs[] = "`$key` = {$value['field']->toSQL()}";
					} 
				}
			}
			
			if ($this->new)
				$query = "INSERT INTO $table_name (".implode(',', $attrs).") VALUES (".implode(',', $values).")";
			else 
				$query = "UPDATE $table_name SET ".implode(',', $attrs)." WHERE {$this->foreignKey} = ".$this->{$this->foreignKey};
			
			if ($this->db->execute($query)){
				$this->new = false;
				return true;
			}else {
				echo "error saving, query: '$query'<br>";
				return false;
			}
		}
		
		
		public static function get($name, $criteria) {
			$result = self::find($name, $criteria);
			if (count($result)<1){
				return null;
			}else if (count($result) > 1){
				throw new BadFunctionCallException("Model::get found more than one record", 0);
			}else{
				return $result[0];
			}
		}
		
		
		public static function find($name, $criteria) {
			
			$schema = new ModelSchema($name);
			$attributes = $schema->fetchAttributes();
			if (is_string($criteria)){
				switch ($criteria){
					case 'all':
						$condition = '';
				}
			}else{
				$crit_arr = array();
				foreach ($criteria as $field => $val){
					$attr = $attributes[$field];
					$crit_arr[] = $attr['name']." = ".$attr['field']->toSQL($val);
				}
				$condition = "WHERE ".implode(' AND ', $crit_arr);
				
			}
			$query = "SELECT * FROM {$schema->table_name()} $condition";
			$db = DB::getConnection();
			$query_result = $db->execute($query);
			if ($query_result){
				$found = array();
				while ($result = $db->fetch_result($query_result)){
					
					$found[] = self::_fromResultSet($name, $result);
				}
			}
			return $found;
		}
		
		private function _findModel($name) {
			return preg_replace("/s$/", '', ucfirst($name));
		}

		
		protected static function _fromResultSet($name, $resultSet){
			$schema = new ModelSchema($name);
			$db = DB::getConnection();
			$model = new $name(false, $schema, $db);
			foreach ($resultSet as $key => $value){
				$attr = strtolower($key);
				$model->{$attr} = $value;
			}
			return $model;
		}
		
		
	}
?>