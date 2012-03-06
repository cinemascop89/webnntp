<?php 

	session_start();

	include 'template/header.php';
	 
	
	include_once 'nntp/nntp.php';
	include_once 'nntp/newsgroup.php';
	include_once 'util.php';	
	
	
	
	if (isset($_POST['user'])){
		$_SESSION['user'] = $_POST['user'];
		$_SESSION['pass'] = $_POST['pass'];
		$_SESSION['server'] = $_POST['server'];
	}elseif (!isset($_SESSION['server'])){
		include('template/login_form.php');
		include('template/footer.php');
		
		exit();
	}
	
	$news = new NetNews($_SESSION['server']);
	
	if ($_SESSION['user'] != '' && !$news->authenticate($_SESSION['user'], $_SESSION['pass'])){
		echo 'error';
	}
	
	//$news->capabilities();
?>
	<div id='groups'>
<?php
	
	$groups = $news->get_groups();
	foreach ($groups as $group)
		echo "<a href='/?gid=".$group->get_name()."'>".$group->get_name()."</a><br>";

	$group_name = $_GET['gid'];
	if ($group_name){
?>
</div>
	<div id='viewer'>
		<div id='messages'>
			<table>
				<tr>
					<th>Author</th>
					<th>Subject</th>
					<th>Date</th>
				</tr>
<?php 
	
		$group = $news->open_group($group_name);
	
    	//echo render_posts(organize_posts($group->load_messages()));
    	$articles = $group->load_messages();
    	print_r (organize_posts($aticles));
    	echo render_posts($articles);

	}
?>
			</table>
		</div>
		<div id='post'>
		</div>
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
	
	$news->disconnect();
	
	include 'template/footer.php';
?>