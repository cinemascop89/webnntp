<?php

	class Article{
		
		private $group, $head, $body;
		private $id, $references;
		
		public function Article($newsgroup, $header=null, $body=null) {
			$this->group = $newsgroup;
			$this->head = $head;
			$this->body = $body;
			$this->references = Array();
		}
		
		public function get_id() {
			return $this->id;
		}
		
		public function get_header($name) {
			return $this->head[$name];
		}
		public function headers() {
			return $this->head;
		}
		public function get_group() {
			return $this->group->get_name();
		}
		
		public function add_reference($article){
			$this->references[] = $article;
		}
		public function get_references() {
			return $this->references;
		}
		
		public static function from_xover($id, $subject, $author, $date, $msgid, $references, $group){
			
			$article = new Article($group);
			//echo "$id, $subject, $author, $date, $msgid, $references\n";
			$article->id = $id;
			$article->head['Subject'] = $subject;
			$article->head['From'] = $author;
			$article->head['Date'] = $date;
			$article->head['Xref'] = $references;
			
			return $article;
		}
	}
	
?>