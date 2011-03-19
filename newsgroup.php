<?php

	include_once 'nntp.php';
	include_once 'connection.php';
	
	class Newsgroup extends SocketConnection{
		
		private $name, $unread, $messages, $first, $last;
		
		private $news;
		
		public function Newsgroup($name, $sock){
			
			$this->name =$name;
			
			$this->set_sock($sock);
			
			$this->send_command("GROUP", $name);
			$response = $this->read_line();
			
			$matches = Array();
			preg_match("/(\d\d\d)\s+(\d+)\s+(\d+)\s+(\d+)\s+([^\s\n]+)/", $response, $matches);			
			if (intval($matches[1]) < 300){
				$this->unread = $matches[2];
				$this->first = $matches[3];
				$this->last = $matches[4];
			}else{
				return null;
			}
			
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
			
			$this->send_command("HEAD", $message_id);
			
			//read stat data
			$this->read_line();
			
			$msg_data = Array();
			while (($line = $this->read_line()) != '.'){
				$matches = Array();
				preg_match("/([\w-]+):\s+([^\n]+)/", $line, $matches);
				$msg_data[$matches[1]] = $matches[2];
			}
			$msg_data['post-id'] = $message_id;
			
			return $msg_data;
		}
		
		public function get_message_body($message_id) {
			
			$this->send_command("BODY", $message_id);
			
			$body = '';
			while (($line = $this->read_line()) != '.'){
				$body .= "$line<br>";
			}
			
			return $body;
		}
		
		public function get_next_message_info(){
			
			$this->send_command("NEXT", $message_id);			
			//read stat data
			$data=$this->read_line();
			$match = Array();
			preg_match("/\d+\s+(\d+)\s+/", $data, $match);
			
			return $this->get_message_info($match[1]);
			
		}
		public function load_messages(){
			
			$this->send_command("LISTGROUP");
			$i = 0;
			$messages = Array();
			while (($msg_id = $this->read_line()) != "."){
				$messages[$i] = $this->get_message_info($msg_id);
				$i++;
			}
			
		}
	}
?>