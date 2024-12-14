<?php
	//------------------------------------------------------------------------------------------------------------------------
	// News
	//------------------------------------------------------------------------------------------------------------------------
	if (!isset($index_page)) {
		include_once ('../inc/tools.php');
		include_once ('../inc/settings.php');
	}
	
	// place [&amp;] in the link instead of [&]
	$news_data = array();
	$news_data[] = array(	'title' => 'New book: Golden Reflections',
							'desc' => 'New book: Golden Reflections by Śrīla Bhakti Rakṣak Śrīdhar Dev-Goswāmī Mahārāj',
							'link' => WEB_INDEX_FILE.'?c=7#c7',
							'date' => '10 Oct 2012',
							'pubDate' => 'Wed, 10 Oct 2012 16:00:00 +0200');
	
	$news_data[] = array(	'title' => 'New book: Home Comfort',
							'desc' => 'Beautiful new small book: Home Comfort by Śrīla Bhakti Rakṣak Śrīdhar Dev-Goswāmī Mahārāj',
							'link' => WEB_INDEX_FILE.'c=16#c16',
							'date' => '08 Oct 2012',
							'pubDate' => 'Mon, 08 Oct 2012 16:00:00 +0200');
	
	$news_data[] = array(	'title' => 'New feature: cropping fixed',
							'desc' => 'Cropping on left side in 2 col view is fixed.',
							'link' => '',
							'date' => '06 Oct 2012',
							'pubDate' => 'Sat, 06 Oct 2012 16:00:00 +0200');
	
	
?>