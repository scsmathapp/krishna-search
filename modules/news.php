<?php
	//---------------------------------------------------------------------------------
	// News for Index page
	//---------------------------------------------------------------------------------
	include_once (FS_NEWS_DIR.'/news-data.php');
	
	$item = $news_data[0];
	
	$out = '<h2 style="padding-bottom:0px;margin-bottom:0px;">News</h2><div style="padding:8px 16px 0px 16px;">';
	
	$out .= '<b><a href="'.$item['link'].'">'.$item['title'].'</a></b>'.
			'<br />'.$item['desc'].'</b>'.
			'<br />'.$item['date'].'</b>';
	
	echo $out.'</div>';
?>