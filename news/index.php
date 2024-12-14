<?php
	include_once ('news-data.php');
	
	//---------------------------------------------------------------------------------
	// RSS
	//---------------------------------------------------------------------------------
	header("Content-Type: application/rss+xml; charset=utf-8");
	
	// Feed header
	$rssfeed = '<?xml version="1.0" encoding="utf-8"?>'.
				'<rss version="2.0">'.
					'<channel>'.
						'<title>Krsna Search RSS news</title>'.
						'<link>https://krishnasearch.com</link>'.
						'<description>News about the website: new books, new features</description>'.
						'<language>en-us</language>'.
						'<copyright>Copyright (C) '.date("Y").' krishnasearch.com</copyright>';
	
	// Feed items
	foreach ($news_data as $item) {
		$rssfeed .= '<item>'.
						'<title>'.$item['title'].'</title>'.
						'<description>'.$item['desc'].'</description>'.
						'<link>'.$item['link'].'</link>'.
						'<pubDate>'.$item['pubDate'].'</pubDate>'.
					'</item> ';
	}
	
	// Feed end
	$rssfeed .= '</channel>'.
			'</rss>';
	
	echo $rssfeed;
?>