<?php
	
	require_once 'orm/model.php';
	require_once 'group.php';
	
	class Post extends Model{
		static $modelName = 'Post'; 
	}
	/*
	$g = Model::get('Group', array("id"=>2330));
	print_r($g->posts);
	*/
?>