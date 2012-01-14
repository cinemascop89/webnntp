<?php
	require_once 'db/driver.php';
	require_once 'model.php';
	require_once 'fields.php';
	
	class ModelSchema {
		
		private $model, $table, $db;
		private $fields, $key;
		
		public function ModelSchema($model) {
			$this->model = strtolower($model).'s';
			$db_name = Model::$config['db.name'];
			$this->table = "`$db_name`.`$this->model`";
			$this->db = DB::getConnection();
		}
		
		public function key() {
			return $this->key;
		}
		public function _type($real) {
			if (is_array($real)) {
				$col = $real['name'];
				if (isset($real['limit'])) {
					$col .= '(' . $real['limit'] . ')';
				}
				return $col;
			}
			
			$col = str_replace(')', '', $real);
			preg_match("/\w+\((\d+)/", $col, $m);
			$limit = $m[1] ? intval($m[1]) : 1;
			if (strpos($col, '(') !== false) {
				list($col, $vals) = explode('(', $col);
			}
			
			if (in_array($col, array('date', 'time', 'datetime', 'timestamp'))) {
				return new DateTimeField();
			}
			if (($col === 'tinyint' && $limit == 1) || $col === 'boolean') {
				return new BooleanField();
			}
			if (strpos($col, 'int') !== false) {
				return new IntegerField();
			}
			if (strpos($col, 'char') !== false || $col === 'tinytext') {
				return new StringField($limit); echo "limit: $limit";
			}
			if (strpos($col, 'text') !== false) {
				return 'text';
			}
			if (strpos($col, 'blob') !== false || $col === 'binary') {
				return 'binary';
			}
			if (strpos($col, 'float') !== false || strpos($col, 'double') !== false || strpos($col, 'decimal') !== false) {
				return 'float';
			}
			if (strpos($col, 'enum') !== false) {
				return "enum($vals)";
			}
			return new StringField(255);
		}
		
		public function _toAttribute($attr) {
			return array('name' => $attr['Field'],
						 'field' => $this->_type($attr['Type']));
		}
		
		public function fetchAttributes() {
			if ($this->fields)
				return $this->fields;
				
			$this->db->connect();
			$dbname = Model::$config['db.name'];
			$result_set = $this->db->execute("DESCRIBE `$dbname`.`$this->model`");
			$attributes = array();
			while ($field = $this->db->fetch_result($result_set)){
				$attr = $this->_toAttribute($field);
				$name = strtolower($attr['name']);
				if ($field['Key'] == 'PRI')
					$this->key = $name;
				$attributes[$name] = $attr; 
			}
			$this->fields = $attributes;
			return $attributes;
		}
		
		public function table_name() {
			return $this->table;
		}
	}
	
?>