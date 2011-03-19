<?php
	session_start();
	
	include_once 'nntp.php';
	include_once 'newsgroup.php';	
	
	include 'header.php';
	
	
	
	if (!$_SESSION['user']){
		include('login_form.php');
		include('footer.php');
		exit()
	}elseif ($_POST['user']){
		$_SESSION['user'] = $_POST['user'];
		$_SESSION['pass'] = $_POST['pass'];
		$_SESSION['server'] = $_POST['server'];
	}
	
	$news = new NNTP($_SESSION['server']);
	
	if (!$news->authenticate($_SESSION['user'], $_SESSION['pass'])){
		echo 'error';
	}
	/*$groups = $news->get_groups();
	
	echo "<table>";
	foreach ($groups as $group) {
		echo "<tr><td>".$group['name']."</td></tr>\n";
	}
	echo "</table>";*/
	$group_name = $_GET['gid'];
	$grupo = $news->open_group($group_name);
	
	echo "<div id='messages'><table>";
	for ($i=$grupo->get_first_message(); $i<$grupo->get_last_message(); $i++){
		$msg = $grupo->get_next_message_info();
		echo '<tr><td>'.$msg['From']."</td>
			  <td><a href='#' onClick=\"load_post('$group_name','".$msg['post-id']."');\">".$msg['Subject']."</a></td>
			  <td>".$msg['Date']."</td></tr>\n";
	}
	echo '</table></div>';
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
	
	include 'footer.php';
?>