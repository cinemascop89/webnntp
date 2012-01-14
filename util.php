<?php
	
	require_once 'nntp/netnews.php';
	
	function organize_posts($posts) {
			
		//print_r($posts);
		for ($i=count($posts);$i>0;$i--){
			$j=0;
			$k = 0;
			while ($j<count($posts)){
				//Xref: news.fing.edu.uy fing.cursos.metnum:4424
				if (!$posts[$j]){
					//print_r ($posts);
					break;
				}
				preg_match("/Xref:\s\S+\s\S+:(\d+)/", $posts[$j]->get_header('Xref'), $mid);
				//echo "$mid[1] == ".$posts[$i]['id']."\n";
				if ($mid[1] == $posts[$i-1]->get_id()){
					$posts[$i-1]->add_reference ($posts[$j]);
					unset($posts[$j]);
					//print_r ($posts);
				}else{
					$j++;
				}
			}
		}
		return $posts;
	}
	
	function decode_email($text){
		$with_entities = from_encoding($text);
		if(preg_match('/("(?<name>.+?)")?\s?<(?<email>.+?)>/', $with_entities, $matches)){
			$name = $matches['name'];
			if (!$name)
				$name = $matches['email'];
			return "<a href='mailto:{$matches['email']}'>$name</a>";
		}else 
			return $with_entities;	
	}
	
	function from_encoding($encoded) {
		if (preg_match("/=\?([\w\-]+)(\?\w\?)?([^\?]+)\?=/", $encoded, $matches)){
			$charset = $matches[1];
			if ($matches[2]=='?B?'){
				$text = base64_decode($matches[3]);
			}elseif ($matches[2]=='?Q?'){
				return preg_replace("/_/", " ", $matches[3]);
			}else{
				$text = $matches[2];
			}
			
			return htmlentities($text, ENT_QUOTES, $charset);
		}else{
			return $encoded;
		}
	}
	
	function render_posts($msgs, $offset='') {
		
		//print_r($msgs);
		if (empty($msgs))
			return '';
			
		$html = '';
		foreach ($msgs as $msg){
			//print_r ($msg->headers());//."<br>\n";
			//echo $msg->get_header('From')."\n";
			$html .= '<tr><td>'.decode_email($msg->get_header('From'))."</td>".
	      			"<td>$offset<a href='#' onClick=\"load_post('".$msg->get_group()."','".strval($msg->get_id())."');\">".from_encoding($msg->get_header('Subject'))."</a></td>".  	
	   				"<td>".date("m/d/Y", $msg->get_header('Date')->getTimestamp())."</td></tr>\n".render_posts($msg->get_references(), "+--$offset");
		}
		
		return $html;
	}
	
	function pretify_post($text) {
		$quoted = preg_replace("/^(&gt;.*)$/m", "<div class='quote'>$1</div>", $text);
		return str_replace("\r\n", "<br/>", $quoted);
	}
	
?>