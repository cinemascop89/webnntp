<?php

	abstract class SocketConnection {
		
		private $sock, $host, $port;
		
		public function connect($host, $port){
			
			//we have a host-port pair
			$this->host = $host;
			$this->port = $port;
			
			$this->sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			return socket_connect($this->sock, $this->host, $this->port);			
						
		}
		
		public function set_sock($sock){
			$this->sock = $sock;
		}
		
		public  function get_sock(){
			return  $this->sock;
		}
		
		
		public function send_command($command){
			$arguments = '';
			for($i=1; $i<func_num_args();$i++){
				$arguments .= " ".strval(func_get_arg($i));
			}
			
			socket_write($this->sock, "$command$arguments\r\n");
		}
		
		public function read_line(){
			$data = '';
			while (($char = socket_read($this->sock, 1)) != "\n"){
				$data .= $char;
			}
			
			return rtrim($data);
		}
		
		public function read_response_code ($message){
			
			return intval($message[0].$message[1].$message[2], 10);
		}
		
		public function close(){
			
			socket_shutdown($this->sock);
			socket_close($this->sock);
		}
	}
?>