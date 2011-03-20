<?php 

	session_start();

	include 'template/header.php';
	 
	
	include_once 'nntp.php';
	include_once 'newsgroup.php';	
	
	
	
	if (isset($_POST['user'])){
		$_SESSION['user'] = $_POST['user'];
		$_SESSION['pass'] = $_POST['pass'];
		$_SESSION['server'] = $_POST['server'];
	}elseif (!isset($_SESSION['user'])){
		include('template/login_form.php');
		include('template/footer.php');
		exit();
	}
	
	$news = new NNTP($_SESSION['server']);
	
	if (!$news->authenticate($_SESSION['user'], $_SESSION['pass'])){
		echo 'error';
	}
?>
	<div id='groups'>
<?php
	$group_name = $_GET['gid'];
	if (!$group_name){
		$groups = $news->get_groups();
		foreach ($groups as $group)
			echo "<a href='/?gid=".$group['name']."'>".$group['name']."</a><br>";
			
	}else{
?>
</div>
<?php 
	
		$grupo = $news->open_group($group_name);
	
		echo "<div id='messages'><table>";
		
		$messages = $grupo->load_messages();
		foreach ($messages as $msg){
			//$msg = $grupo->get_next_message_info();
			echo '<tr><td>'.$msg['From']."</td>
				  <td><a href='#' onClick=\"load_post('$group_name','".$msg['post-id']."');\">".$msg['Subject']."</a></td>
				  <td>".$msg['Date']."</td></tr>\n";
		}
		echo '</table></div>';
	}
?>
	<div id='post'>
	</div>
	
	<script type="text/javascript">
//<!--
	function load_post(group, post){
		$.ajax({
		   type: "GET",
		   url: "/view_post.php?gid="+group+"&pid="+post,
		   success: function(msg){
		     $("#post").html(msg);
		   }
		 });
		 return false;
	}
//-->
</script>
<?php 
	
	$news->quit();
	
	include 'template/footer.php';
?>