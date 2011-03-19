<?php

	session_start();

	include_once 'nntp.php';
	
	//include 'header.php';
	
	$news = new NNTP($_SESSION['server']);
	if (!$news->authenticate($_SESSION['user'], $_SESSION['pass'])){
		echo 'error';
		exit();
	}
	/*$groups = $news->get_groups();
	
	echo "<table>";
	foreach ($groups as $group) {
		echo "<tr><td>".$group['name']."</td></tr>\n";
	}
	echo "</table>";*/
	$group_name = $_GET['gid'];
	$grupo = $news->open_group($group_name);
	
	echo $grupo->get_message_body($_GET['pid']);
	
	//include 'footer.php';
	
?>