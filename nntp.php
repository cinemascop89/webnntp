<?php

	include_once 'newsgroup.php';
	include_once 'connection.php';
	include_once 'db.php';
	
	$NEWS_HOST = "news.fing.edu.uy";

	class NNTP extends SocketConnection{
		
		private $host, $port;
		
		private $last_message;
		private $groups, $group_list;
		private $db;
		

		public function NNTP ($host, $port=119){
			
			$this->host = host;
			$this->port = port;
			
			$this->groups = Array();
			
			if (!$this->connect($host, $port))
				return null;
				
			$this->last_message = $this->read_line();
			
			$this->db = new DataBase();
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
			
			$this->send_command("NEWGROUPS", "110220", "000000");
			
			$this->read_line();
			
			while (($line =$this->read_line()) != "."){
				$regs = Array();
				preg_match("/(\S+)\s(\d+)\s(\d+)\s(\w+)/", $line, $regs);
				//echo "$line<br>";
				if ($regs){
					$this->db->query("INSERT INTO groups (name, high, low, flags) VALUES ('$regs[1]', $regs[2], $regs[3], '$regs[4]')");
					$groups[$i] = Array("name" => $regs[1], "high" => $regs[2], "low" => $regs[3], "flags" => $regs[4]);
					$i++;
				}
			}
			
			$query=$this->db->query("SELECT name FROM groups");
			while ($groups[$i] = mysql_fetch_array($query)){
				$i++;
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