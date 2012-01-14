<?php

	include_once 'connection.php';
	
	$NEWS_HOST = "news.fing.edu.uy";

	class NNTP extends SocketConnection{
		
		private $host, $port;
		
		private $last_message;
		private $groups, $group_list;
		

		public function NNTP ($host, $port=119){
			
			$this->host = host;
			$this->port = port;
			
			$this->groups = Array();
			
			if (!$this->connect($host, $port))
				return null;
				
			$this->last_message = $this->read_line();
			
		}
		
		
		public function capabilities() {
			
			$this->send_command("CAPABILITIES");
			
		}
		
		
		public function authinfo($user, $pass){
			
			$this->send_command("AUTHINFO", "USER", $user);
			
			if ($this->read_response_code($this->read_line()) >=400){
				echo "ERROR:".$this->get_last_message()."<br>\n";
				return false;
			}else{

				$this->send_command("AUTHINFO", "PASS", $pass);
				if ($this->read_response_code($this->read_line()) >=300){
					echo "ERROR:".$this->get_last_message()."<br>\n";
					return false;
				}
			}
			return true;
		}
		
		
		public function newgroups($date = "19700101", $time = "000000"){
			
			$groups = Array();
			$i = 0;
			
			$this->send_command("NEWGROUPS", $date, $time);
			
			$this->read_line();
			
			while (($line =$this->read_line()) != "."){
				$regs = Array();
				preg_match("/(\S+)\s(\d+)\s(\d+)\s(\w+)/", $line, $regs);
				//echo "$line<br>";
				if ($regs){
					$groups[$i] = Array("name" => $regs[1], "high" => $regs[2], "low" => $regs[3], "flags" => $regs[4]);
					$i++;
				}
			}
						
			return $groups;
		}
		
		public function get_last_message(){
			
			return $this->last_message;
		}
		
		
		public function group($group_name){
			/*
			$this->groups[$group_name] = new Newsgroup($group_name, $this->get_sock());
			if (!$this->groups[$group_name]){
				
				throw new Exception("group inexistent", 404);
			}
			return $this->groups[$group_name];
			*/
			$this->send_command("GROUP", $group_name);
			$response = $this->read_line();
			
			$matches = Array();
			preg_match("/(\d\d\d)\s+(\d+)\s+(\d+)\s+(\d+)\s+([^\s\n]+)/", $response, $matches);	
			$data = Array(); 	
			if (intval($matches[1]) < 300){
				$data = Array("unread" => $matches[2], "first" => $matches[3], "last" => $matches[4]);
			}
			return new Response($matches[1], '', $data);
		}
		
		
		public function head($message_id){
			
			$this->send_command("HEAD", $message_id);
			
			//read stat data
			$this->read_line();
			
			$msg_data = Array();
			while (($line = $this->read_line()) != '.'){
				$matches = Array();
				if (preg_match("/([\w-]+):\s+([^\n]+)/", $line, $matches))
					$msg_data[$matches[1]] = $matches[2];
			}
			
			return $msg_data;
		}
		
		
		public function body($message_id) {
			
			$this->send_command("BODY", $message_id);
			$this->read_line();
			
			$body = '';
			while (($line = $this->read_line()) != '.'){
				$body .= "$line\r\n";
			}
			
			return $body;
		}
		
		
		public function article($message_id) {
			
			$this->send_command("ARTICLE", $message_id);
			$this->read_line();
			
		}
		public function next($message_id){
			
			$this->send_command("NEXT", $message_id);			
			//read stat data
			$data=$this->read_line();
			$match = Array();
			preg_match("/\d+\s+(\d+)\s+/", $data, $match);
			
			return $this->get_message_info($match[1]);
			
		}
		
		
		public function listgroup(){
			
			$this->send_command("LISTGROUP");
			preg_match("/(\d+)\s(\d+)\s\d+\s\d+\s\S+/", $this->read_line(), $regs);
			
			$messages = Array();
			if (intval($regs[1]) < 300 && intval($regs[2]) != 0){
				$i = 0;
				while (($msg_id = $this->read_line()) != "."){
					$messages[$i] = $this->get_message_info($msg_id);
					$i++;
				}
			}
			
			return $messages;
		}
		
		
		public function xover($start, $finish=''){
			
			$range = "$start-";
			if ($finish) $range .= $finish;
			
			$this->send_command("XOVER", $range);
			
			preg_match("/(\d+)\s(\d+-\d*)\s\w*/", $this->read_line(), $regs);
			
			/*
			 * id, subject, author, date,
   			 * message-id, references, byte count, and line count
   			 */
			$messages = Array();
			while(($line = $this->read_line()) != '.'){
				$headers = split("\t", $line);
				$messages[] = Array(
						"id" 		=> $headers[0],
						"subject"	=> $headers[1],
						"author"	=> $headers[2],
						"date"		=> $headers[3],
						"msgid"		=> $headers[6],
						"references"=> $headers[8]);
			}
			
			return $messages;
		}
		
		
		public  function quit(){
			
			$this->send_command("QUIT");
			$this->read_line();
			
			$this->close();
		}
		
	}
	
	
	class Response {
		
		private $code, $description, $data;
		
		public function Response($code, $description, $data) {
			$this->code = intval($code);
			$this->description = $description;
			$this->data = $data;
		}
		
		public function is_success(){
			return  $this->code < 400;
		}
		
		public function get_data(){
			return $this->data;
		}
		
		public function get_description() {
			return $this->description;
		}
	}

	
?>