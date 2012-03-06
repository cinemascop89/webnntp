<?php

	ini_set("display_errors","1");
	
	session_start();

	include_once 'nntp/netnews.php';
	include_once 'util.php';
	
	$news = new NetNews($_SESSION['server']);
	if ($_SESSION['user'] != '' && !$news->authenticate($_SESSION['user'], $_SESSION['pass'])){
		echo 'error with authentication';
		exit();
	}
	
	$group_name = $_GET['gid'];
	$grupo = $news->open_group($group_name);
	$headers = $grupo->get_message_info($_GET['pid']);
	$encoding = 'ISO-8859-1';
	$ctype = 'plain';
	if (isset($headers['Content-Type'])){
		//Content-Type: text/plain; charset=windows-1252; format=flowed
		preg_match("/text\/(?<type>\w+);\scharset=(?<charset>[\w\-]+);/", $headers['Content-Type'], $matches);
		$encoding = $matches['charset'];
		$ctype = $matches['type'];
		
	}
	//header('Content-Type', "text/html; charset=$encoding;");
	//$article = $group->
	echo pretify_post(htmlentities($grupo->get_message_body($_GET['pid']), ENT_QUOTES, $encoding));
	
?>