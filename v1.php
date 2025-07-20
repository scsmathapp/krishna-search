<?php
	//------------------------------------------------------------
	// Krsna Search: krishnasearch.com
	//------------------------------------------------------------
	// Developer Build Number (shows in the header)
	define ('BUILD_NUMBER', 		("21"));
	
	$time_start		= microtime(true);
	$include_dir	= 'inc/';
	
	include_once ($include_dir.'tools.php');
	include_once ($include_dir.'settings.php');
	include_once (FS_INCLUDE_DIR.'/datacenter.php');
	include_once (FS_INCLUDE_DIR.'/tools-evaluate.php');
	include_once (FS_INCLUDE_DIR.'/tools-cache.php');
	include_once (FS_INCLUDE_DIR.'/tools-base.php');
	include_once (FS_INCLUDE_DIR.'/tools-diacritics.php');

	//------------------------------------------------------------
  $dataCenter = new DataCenter($dbConnectionData);
	
    //writeOut($_SESSION);
    //writeOut($_GET);
    //writeOut($_POST);
	//writeOut($_SERVER);
    //writeOut($do);
	//writeOut($do['urlKeyValuePairs']);
	
	//-------------------------------------------------------------
	// Index, Settings, Search page
	// Load Result-list and Result-book-text at the same time or not
	//-------------------------------------------------------------
	$do['index_page']	= $index_page = $settings_page = false;
	$out_resultlist		= $out_pagelinks = $out_neighbour_result_links = '';
	
	//-------------------------------------------------------------
	// Get Books data (title, chapters, authors)
	//-------------------------------------------------------------
	Get_Books_data($dataCenter, $do);
	
	//-------------------------------------------------------------
	// Clean and store search text
	//-------------------------------------------------------------
	clean_store_SearchText($dataCenter, $do, DO_SEARCH_QUERY);
	
	//--------------------------------
	// Store Search query in history
	//--------------------------------
	Store_Search_Query_in_History($do);
	//writeOut($do['siteOperationMode_Values']);
	
	
	//---------------------------------------------------------------------------------------------------------------
	// Update header
	//---------------------------------------------------------------------------------------------------------------
	if (IS__SITE_SIMPLIFIED_LAYOUT && !DO_SEARCH_QUERY && isset($do['update_header']) && $do['update_header']) {
		include_once (FS_INCLUDE_DIR.'/header.php');
		echo Get_Header($do);
		exit;
		//Get_Footer($dataCenter, $do, $time_start, $out_neighbour_result_links);
	}
	
	//---------------------------------------------------------------------------------------------------------------
	// Do the search OR Show the chapter, paragraph, sentence (book text)
	//---------------------------------------------------------------------------------------------------------------
	if (DO_SEARCH_QUERY or DO_BOOK_READ or DO_LOAD_INLINE_CHAPTER or DO_CROSS_REFERENCE or DO_REFERENCE_INFO) {
		
		include_once (FS_MODULES_DIR.'/search-book-text.php');
		
		
	//---------------------------------------------------------------------------------------------------------------
	// .. Show fix pages
	//---------------------------------------------------------------------------------------------------------------
	} else if (isset($do['fixpage'])) {
		
		$head_title = $include_page_file = '';
		
		switch ($do['fixpage']) {
			
			case 'settings': 
				
				$head_title			= 'Settings';
				$settings_page		= true;
				$include_page_file	= 'settings.php';
				
				$urlData			= (!empty($do['url_encoded_do_search_text']) ? '&amp;q='.$do['url_encoded_do_search_text'] : '').
										$do['urlDataTransfer'];
				
				// Result list area: empty
				$out_resultlist = $out_video_resultlist = '';
				if (IS__SEARCH_IN_BOOKS_VIDEOS) {
					$out_resultlist 		= '<div id="search-result-items-container" class="'.
														$do['layout_container_class_name']['west'].'">'.
													'<div id="search-result-items"></div>'.
												'</div>';
					$out_video_resultlist 	= '<div id="search-result-video-items-container" class="'.
														$do['layout_container_class_name']['west'].'">'.
													'<div id="search-result-video-items"></div>'.
												'</div>';
				} else {
					if (IS__SEARCH_IN_BOOKS) {
						$out_resultlist 		= '<div id="search-result-items-container" class="'.
															$do['layout_container_class_name']['west'].'">'.
														'<div id="search-result-items"></div>'.
													'</div>';
					}
					if (IS__SEARCH_IN_VIDEOS) {
						$out_resultlist 	= '<div id="search-result-video-items-container" class="'.
															$do['layout_container_class_name']['west'].'">'.
														'<div id="search-result-video-items"></div>'.
													'</div>';
					}
				}
				
				$do['pageHeaderMetaDescription'] = 'Set the preferences for the text search site';
				
				// Display page
				include_once (FS_INCLUDE_DIR.'/header.php');
				
				echo 	Get_Header($do).
						$out_resultlist.
						'<div id="search-result-book-text-container" class="'.$do['layout_container_class_name']['center'].'">'.
							'<div id="search-result-book-text-wrapper">';
				
				if (!empty($include_page_file)) { include_once (FS_PAGES_DIR.'/'.$include_page_file); }
				
				echo	'</div></div>'.
						$out_resultlist.
						$out_video_resultlist.
					Get_Footer($dataCenter, $do, $time_start, $out_neighbour_result_links);
				break;
		}
		
	//---------------------------------------------------------------------------------------------------------------
	// .. Index page
	//---------------------------------------------------------------------------------------------------------------
	} else {
		
		$do['index_page'] = $index_page = true;
		include_once (FS_INCLUDE_DIR.'/header.php');
		include_once (FS_MODULES_DIR.'/index-content.php');
	}
	
	
	//---------------------------------------------------------------------------------------------------------------
	// Post javascript values
	//---------------------------------------------------------------------------------------------------------------
	/*
	 * var data_transfer = {is_settings_page:'true'};
	 */
	
	$postJSscript = array();
	$postJSscript[] = '<script type="text/javascript">';
	$postJSscript[] = 'var is_settings_page ='.($settings_page ? 'true' : 'false').';';
	$postJSscript[] = 'var settings_page_tab ='."'".(isset($do['tab']) ? $do['tab'] : 'tab-book-list')."';";

	// Search query, 2 cols view, result list items tooltips
	$postJSscript[] = 'var is_search_query_select_result ='.(((DO_SEARCH_QUERY or DO_SELECT_RESULT) && isset($do['searchResult_psCurrentNumber'])) ? 
															'1' : '0').';';
	$postJSscript[] = 'var searchResult_psCurrentNumber ='.(isset($do['searchResult_psCurrentNumber']) ? $do['searchResult_psCurrentNumber'] : '0').';';
	$postJSscript[] = 'var save_history__search_query_select_result ='."'".WEB_INDEX_FILE.'?q='.
												(!empty($do['url_encoded_do_search_text']) ? 
															$do['url_encoded_do_search_text'] : '').
												'&'.implode('&', $do['urlData']).
												(!empty($do['searchResult_book_id']) && !empty($do['searchResult_chapter_id']) ? 
															'&b='.$do['searchResult_book_id'].'&c='.$do['searchResult_chapter_id'] : '').
												'&rn='.$do['searchResult_psCurrentNumber'].
												(DO_SEARCH_QUERY ? '&sq=Search' : '').
											"';";
	$postJSscript[] = 'var url_data ='."'".implode('&', $do['urlData'])."';";

	// Select chapter, full text load
	$postJSscript[] = 'var is_select_chapter_full_text_load ='.((DO_SELECT_CHAPTER and IS__SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK) ? '1' : '0').';';
	$postJSscript[] = 'var save_history_select_chapter_full_text_load ='."'".WEB_INDEX_FILE.'?q='.
												(!empty($do['url_encoded_do_search_text']) ? 
															$do['url_encoded_do_search_text'] : '').
												'&'.implode('&', $do['urlData']).
												(!empty($do['searchResult_book_id']) && !empty($do['searchResult_chapter_id']) ? 
															'&b='.$do['searchResult_book_id'].'&c='.$do['searchResult_chapter_id'] : '').
												//'&rn='.$do['searchResult_psCurrentNumber'].
												//'&sq=Search'.
											"';";
	$postJSscript[] = 'var searchResult_chapter_id ='."'".(!empty($do['searchResult_chapter_id']) ? $do['searchResult_chapter_id'] : '')."';";
	$postJSscript[] = '</script>';
	
	// echo out
	echo implode('', $postJSscript);
	
	
	/*
	writeOut($do);
	writeOut($do['cross_reference_id']);
	writeOut($do['searchResult_paragraph_id']);
	writeOut($_COOKIE);
	*/
	//writeOut($do['siteRunningOnSmallDevice']);
	//writeOut($do);
?>
</body></html>