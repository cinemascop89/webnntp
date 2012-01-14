<?php

	include_once 'nntp.php';
	include_once 'article.php';
	include_once 'models/message.php';
	
	class Newsgroup{
		
		private $name, $unread, $messages, $first, $last;
		
		private $connection, $model;
		
		public function Newsgroup($name, $number, $low, $high, $conn, $model=null){
			
			$this->name =$name;
			
			$this->connection = $conn;
			$this->model = $model;
			
			$this->unread = $number;
			$this->first = $low;
			$this->last = $high;
			
		}
		
		public function get_name(){
			return $this->name;
		}
		
		public function get_message_count() {
			return $this->unread;
		}
		public function get_first_message() {
			return $this->first;
		}
		public function get_last_message() {
			return $this->last;
		}
		
		public function get_message_info($message_id){
			
			$this->connection->group($this->name);
			$msg_data = $this->connection->head($message_id);
		
			$msg_data['post-id'] = $message_id;
			$msg_data['group'] = $this->name;
			
			return $msg_data;
		}
		
		public function get_message_body($message_id) {
			/*
			$this->send_command("BODY", $message_id);
			$this->read_line();
			
			$body = '';
			while (($line = $this->read_line()) != '.'){
				$body .= "$line<br>";
			}
			*/
			$this->connection->group($this->name);
			return $this->connection->body($message_id);
		}
		

		public function load_messages(){
			
			$this->connection->group($this->name);
			$messages = Array();
			
			$saved_posts = $this->model->posts;
			$max_post = $this->first;
			foreach ($saved_posts as $post) {
				if ($max_post < $post->msgid)
					$max_post = $post->msgid;
				$messages[] = Article::from_xover($post->msgid, 
													$post->subject, 
													$post->author, 
													$post->date, 
													$post->messageid, 
													$post->xref, 
													$this);
			}
			
			//dont fetch if theres no more new articles
			if ($max_post >= intval($this->last)) 
				return $messages;
			
			$message_info = $this->connection->xover($max_post, $this->last);
			
			foreach ($message_info as $overview){
				$message = new Post();
				$message->msgid = $overview['id'];
				$message->subject = $overview['subject'];
				$message->author = $overview['author'];
				$message->date = $overview['date'];
				$message->messageid = $overview['msgid'];
				$message->xref = $overview['references'];
				$message->group_id = $this->model->id;
				$message->save();
				 
				$messages[] = Article::from_xover(	$overview['id'], 
													$overview['subject'], 
													$overview['author'], 
													$message->date, 
													$overview['msgid'], 
													$overview['references'], 
													$this); 
			}
			
			return $messages;
		}
	}
?>