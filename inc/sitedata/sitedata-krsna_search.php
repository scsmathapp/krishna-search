<?php
	//-------------------------------------------------------------------------------------
	// Site specific content
	//
	// Krsna Search: krishnasearch.com
	//-------------------------------------------------------------------------------------
	// usage
	/*
	getSiteSpecificContent('id');
	getSiteSpecificContent('website_address');
	getSiteSpecificContent('website_dev_folder_remotehost');
	getSiteSpecificContent('sqlite_db_default');
	getSiteSpecificContent('search_in_books');
	getSiteSpecificContent('search_in_videos');
	getSiteSpecificContent('download_results_localhost');
	getSiteSpecificContent('download_results_remotehost');
	getSiteSpecificContent('email_address_contact');
	getSiteSpecificContent('dbConnectionData_localhost', 	array('encoding_str' => ENCODING_STR));
	getSiteSpecificContent('dbConnectionData_remotehost', 	array('encoding_str' => ENCODING_STR));
	getSiteSpecificContent('head_title');
	getSiteSpecificContent('main_custom_css');
	getSiteSpecificContent('header_logo',					array('index_file' => WEB_INDEX_FILE, 'url_postpart_index' => $url_postpart_index));
	getSiteSpecificContent('top_container');
	getSiteSpecificContent('main_logo');
	getSiteSpecificContent('label_under_search_bar');
	getSiteSpecificContent('bottom_content',				array('index_file' => WEB_INDEX_FILE, 'urlData' => $urlData));
	getSiteSpecificContent('settings_about_contact_us__filename');
	*/
	
	function getSiteSpecificContent($content_id, $args = array()) {
		$ret = '';
		
		switch ($content_id) {
			
			
			//-------------------------------------------------------------------------------------
			// ID
			//-------------------------------------------------------------------------------------
			case 'id':
				$ret = 'krishnasearch.com';
				break;


			//-------------------------------------------------------------------------------------
			// /inc/settings.php
			//-------------------------------------------------------------------------------------
			case 'website_folder':
				$ret = 'krsna_search';
				break;

			case 'website_address':
				$ret = 'krishnasearch.com';
				break;
				
			case 'website_dev_folder_remotehost':
				$ret = '/dev';
				break;
				
			// css
			case 'css_default':
				$ret = 'krsna_search-books';
				break;
			
			// sqlite db
			case 'sqlite_db_default':
				$ret = 'krsna_search-books';
				//$ret = 'krsna_search-videos';
				break;
				
			// search type
			case 'search_in_books':
				$ret = TRUE;
				//$ret = FALSE;
				break;
			case 'search_in_videos':
				//$ret = FALSE;
				$ret = TRUE;
				break;
			
			// languages used in books separate them in groups
			case 'languages_used_in_books':
				$ret = FALSE;
				break;
			
			// download results
			case 'download_results_localhost':
				$ret = FALSE;
				break;
			case 'download_results_remotehost':
				$ret = FALSE;
				break;
			
			case 'email_address_contact':
				$ret = 'math@scsmath.com';
				break;
				
			case 'dbConnectionData_localhost':
				$ret = array(
							'host'		=> 'localhost',
							'dbname' 	=> 'krsna_search',
							'mode' 		=> 'user',
							'username' 	=> 'ks_user',
							'password' 	=> 'ks_2j6l2n',
							'encoding' 	=> $args['encoding_str']
						);
				break;
			case 'dbConnectionData_remotehost':
				$ret = array(
							'host'		=> 'localhost',
							'dbname' 	=> 'krsna_search',
							'mode' 		=> 'user',
							'username' 	=> 'ks_user',
							'password' 	=> 'ks_2j6l2n',
							'encoding' 	=> $args['encoding_str']
						);
				break;
				
				
			//-------------------------------------------------------------------------------------
			// /inc/header.php
			//-------------------------------------------------------------------------------------
			case 'head_title':
				$ret = 'Krishna Search | Sri Chaitanya Saraswat Math’s Digital Library';
				break;
				
			case 'main_custom_css':
				$ret = '';
				break;
				
			case 'run_analytics_tracking':
				$ret = TRUE && !IS__LOCALHOST && !IS__REMOTEHOST_DEV;
				break;
				
			case 'analytics_tracking_filename':
				$ret = FS_CONFIG_DIR.'/analytics-tracking-krsna_search.php';
				break;
				
			case 'google-site-verification':
				$ret = 'OOuR5M1s6OjLCOA2nUskva0M9LMgB4B64_6XkzND_PA';
				break;
				
			//-------------------------------------------------------------------------------------
			// /inc/tools.php
			//-------------------------------------------------------------------------------------
			case 'header_logo':
				$ret = '<div id="header-logo">'.
							'<a href="'.$args['index_file'].$args['url_postpart_index'].'">'.
								'<span title="Home"></span>'.
							'</a>'.
						'</div>';
				break;
				
				
			//-------------------------------------------------------------------------------------
			// /modules/index-content.php
			//-------------------------------------------------------------------------------------
			case 'top_container':
				$ret = '';
				break;
				
			case 'main_logo':
				$ret = '<div id="index-main-logo" alt="Krishna Search"></div>';
				break;
				
			case 'label_under_search_bar':
				$ret = '<div id="index-digital-library-title">Digital Library of Sri Chaitanya Saraswat Math</div>';
				break;
				
			case 'bottom_content':
				$ret = '<div>'.
							'<a href="'.$args['index_file'].'?page=settings&amp;tab=about'.$args['urlData'].'" class="index-link">About</a>'.
						'</div>'.
						'<div>© '.date("Y").' <a href="http://www.scsmath.com" class="index-link">Sri Chaitanya Saraswat Math</a></div>';
				break;
				
				
			//-------------------------------------------------------------------------------------
			// /pages/settings.php
			//-------------------------------------------------------------------------------------
			case 'settings_about_contact_us__filename':
				$ret = 'krsna_search.php';
				break;
		} // switch ($content_id)
		
		return $ret;
	}
?>