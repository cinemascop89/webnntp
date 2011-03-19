<?php

	include_once 'newsgroup.php';
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
		
		
		public function authenticate($user, $pass){
			
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
		
		
		public function get_groups(){
			
			$groups = Array();
			$i = 0;
			$this->send_command("LIST");
			
			$this->read_line();
			
			while (($line =$this->read_line()) != "."){
				$regs = Array();
				preg_match("/(\S+)\s(\d+)\s(\d+)\s(\d+)/", $line, $regs);
				if ($regs){
					$groups[$i] = Array("name" => $regs[1], "count" => $regs[2], "high" => $regs[3], "low" => $regs[4]);
					$i++;
				}
			}
			$this->group_list = $groups;
			return $groups;
		}
		
		public function get_last_message(){
			
			return $this->last_message;
		}
		
		
		public function open_group($group_name){
			
			$this->groups[$group_name] = new Newsgroup($group_name, $this->get_sock());
			if (!$this->groups[$group_name]){
				
				throw new Exception("group inexistent", 404);
			}
			return $this->groups[$group_name];
		}
		
		
		public  function quit(){
			
			$this->send_command("QUIT");
			$this->read_line();
			
			$this->close();
		}
		
	}
	
?>