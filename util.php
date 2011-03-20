<?php

	function organize_posts($posts) {
		for ($i=0;$i<count($posts);$i++)
			$posts[$i]['replies'] = Array();
			
		for ($i=count($posts);$i>=0;$i--){
			$j=0;
			while ($j<count($posts)){
				if ($posts[$j]['Subject'] == "Re: ".$posts[$i]['Subject']){
					$posts[$i]['replies'][] = $posts[$j];
					unset($posts[$j]);
				}else{
					$j++;
				}
			}
		}
		//print_r($posts);
		return $posts;
	}
	
	function render_posts($msgs, $offset='') {
		//print_r($msgs);
		$html = '';
		foreach ($msgs as $msg){
			//echo $msg['Subject']."<br>\n";
			$html .= '<tr><td>'.$msg['From']."</td>".
	      			"<td>$offset<a href='#' onClick=\"load_post('".$msg['group']."','".$msg['post-id']."');\">".$msg['Subject']."</a></td>".  	
	   				"<td>".$msg['Date']."</td></tr>\n".render_posts($msg['replies'], "+--$offset");
		}
		return $html;
	}
	
	
	function generate_posts(){
		
		return Array(Array( "Subject" => "Hi!"),
					Array( "Subject" => "Hi!"),
					Array( "Subject" => "Re: Hi!"),
					Array( "Subject" => "bye!"),
					Array( "Subject" => "Re: Hi!"),
					Array( "Subject" => "Re: Re: Hi!"));
	}
	
?>