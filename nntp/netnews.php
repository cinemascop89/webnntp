<?php
	
	require_once 'nntp.php';
	require_once 'newsgroup.php';
	require_once 'models/group.php';
	
	class NetNews {
		
		private $server;
		private $connection;
		
		public function NetNews($server) {
			$this->server = $server;
			$this->connection = new NNTP($server);
		}
		
		public function authenticate($user, $password){
			return $this->connection->authinfo($user, $password);
		}
		
		public function get_groups(){
			
			$groups = array();
			
			$last_modify = new DateTime("1970-01-01 00:00:00");
			$cached_groups = Model::find('Group', array('server' => $this->server));
			
			foreach ($cached_groups as $group) {
				if ($last_modify < $group->lastupdate)
					$last_modify = $group->lastupdate;
				$groups[] = new Newsgroup(	$group->name, 
											$group->count, 
											$group->low, 
											$group->high, 
											$this->connection);
			}
			
			$group_names = $this->connection->newgroups($this->nntpDate($last_modify),
														$this->nntpTime($last_modify));
			
			foreach ($group_names as $group){
				
				$cgroup = new Group();
				$cgroup->name = $group['name'];
				$cgroup->count = $group['number'];
				$cgroup->low = $group['low'];
				$cgroup->high = $group['high'];
				$cgroup->server = $this->server;
				$cgroup->save();
				
				$groups[] = new Newsgroup(	$group['name'], 
											$group['number'], 
											$group['low'], 
											$group['high'], 
											$this->connection);
			}
			return $groups;
		}
		
		private function nntpDate($date) {
			return date("Ymd", $date->getTimestamp());
		}
		
		private function nntpTime($time) {
			return date("His", $time->getTimestamp());
		}
		
		public function open_group($group_name) {
			
			$cached_group = Model::get('Group', array('name' => $group_name,
														'server' => $this->server));
			if ($cached_group){
				return new Newsgroup($group_name,
									 $cached_group->count,
									 $cached_group->low,
									 $cached_group->high,
									 $this->connection,
									 $cached_group);
			}else{
				$response = $this->connection->group($group_name);
				$data = $response->get_data();
				
				if ($response->is_success()){
					return new Newsgroup($group_name,
										 $data['unread'],
										 $data['first'],
										 $data['last'],
										 $this->connection,
										 $cached_group);
				}else{
					// thro exception
				}
			}
			
		}
		
		public function disconnect() {
			$this->connection->quit();
		}
		
	}
	
?>