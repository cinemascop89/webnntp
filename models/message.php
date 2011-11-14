<?php

	include_once 'model.php';
	
	class Message extends Model{
		
		public function Message(){
			
			$this->attributes('from',
								'subject',
								'body');
		}		
	}
	
?>