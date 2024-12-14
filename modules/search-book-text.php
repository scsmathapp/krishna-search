<?php
	//---------------------------------------------------------------------------------------------------------------
	// 2 columns layout - Search result list / Book list  +  Book text
	//---------------------------------------------------------------------------------------------------------------
	load_book_chapter_paragraph_id($dataCenter, $do);
	
	
	//---------------------------------------------------------------------------------------------------------------
	// Evaluate search text
	//---------------------------------------------------------------------------------------------------------------
	$out_neighbour_result_links = $out_wordSuggestionList = $out_resultlist = $out_video_resultlist = $out_video_player = '';
	$do['exists_result_list_from_books'] = $do['exists_result_list_from_video_books'] = false;
	$download_result_set = '';
	
	if (DO_SEARCH_QUERY or DO_SELECT_RESULT or DO_SELECT_CHAPTER) {
		
		// Save some data
		$do['saved_values']['selected_language_id'] = $do['selected_language_id'];
		$do['saved_values']['db_table_prefix']		= $do['db_table_prefix'];
		$do['selected_language_id']					= (IS__LANGUAGES_USED_IN_BOOKS ? $do['selected_language_id'] : NULL);
		
		include_once (FS_MODULES_DIR.'/result-list.php');
		
		//---------------------------------------------------------------------------------------------------------------
		// Search text process: Polish-form, Highlight patterns
		//---------------------------------------------------------------------------------------------------------------
		if (!empty($do['search_text'])) {
			
			//------------------------------
			// Convert to Polish-form
			//------------------------------
			//$ret = convertToPolishForm($dataCenter, '( '.$do['search_text'].' )');
			array_unshift($do['search_text_canonicalA'], '(');
			array_push($do['search_text_canonicalA'], ')');
			$ret = convertToPolishForm($dataCenter, $do['search_text_canonicalA']);
			
			$do['highlight_words_TypeCategorised'] = $ret['highlight_words'];
//writeOut($do['search_text_canonicalA']);
//writeOut($ret);
			
			//------------------------------
			// Highlight words pattern build from found keywords in query
			//------------------------------
			$do['highlight_words_search'] = $do['highlight_words_replace'] = $do['highlight_words'] = array();
			$do['highlight_words_search'.TABLE_NAME_COMPONENT__VIDEO] = 
				$do['highlight_words_replace'.TABLE_NAME_COMPONENT__VIDEO] = 
				$do['highlight_words'.TABLE_NAME_COMPONENT__VIDEO] = array();
			//highlight_words_pattern_build($dataCenter, $do, $do['highlight_words_TypeCategorised']);
			
		} else {
			$ret['search_text'] = '';
		}
		
		
		//---------------------------------------------------------------------------------------------------------------
		// No word in search
		//---------------------------------------------------------------------------------------------------------------
		if (empty($ret['search_text'])) {
			
			if (IS__SEARCH_IN_BOOKS_VIDEOS) {
				$out_resultlist			= '<h1 class="search-failure">'.NO_WORD_IN_SEARCH_TEXT.'</h1>';
				
			} else {
				if (IS__SEARCH_IN_BOOKS) {
					$out_resultlist 	= '<h1 class="search-failure">'.NO_WORD_IN_SEARCH_TEXT.'</h1>';
				}
				if (IS__SEARCH_IN_VIDEOS) {
					$out_video_resultlist 	= '<h1 class="search-failure">'.NO_WORD_IN_SEARCH_TEXT.'</h1>';
				}
			}			
		} else {
			
			//---------------------------------------------------------------------------------------------------------------
			// Get result list from Books
			//---------------------------------------------------------------------------------------------------------------
			if (IS__SEARCH_IN_BOOKS) {
				
				$do['db_table_prefix']				= '';
				$do['is_books']						= TRUE;
				$do['is_videos']					= FALSE;
				$do['need_neighbour_result_links']	= !IS_ACTION_VIDEO;
				$do['url_is_video_books_addon']		= '';
				$do['search_restrictions_Partial'] 	= $do['searchBooksPartial'];
				$do['search_restrictions_IdList'] 	= 'searchBooksIdList';
				
				//$time = microtime(TRUE);
				$ret_resultList = resultList_evaluateSearchText($dataCenter, $do, $ret['search_text'], $ret['polish_form']);
//writeOut(round(microtime(TRUE)-$time, 5));
				
				//----------------------------------------------------
				// No relevant paragraph
				//----------------------------------------------------
				if ($ret_resultList['result_text'] == NO_RELEVANT_PS_FOR_SEARCH_TEXT) {
					
					$out_resultlist = '<h1 class="search-failure">'.NO_RELEVANT_BOOKS_PS_FOR_SEARCH_TEXT.'</h1>';

					$do['exists_result_list_from_books'] = false;


				//----------------------------------------------------
				// ..Result list exists
				//----------------------------------------------------
				} else {
					
					// Result text list
					$out_resultlist = $ret_resultList['result_text'];
					// Paging
					//$ret_resultList_out_pagelinks = $ret_resultList['paging'];
					
					// Neighbour result links
					if (!IS_ACTION_VIDEO) {
						$out_neighbour_result_links = $ret_resultList['neighbour_result_links'];
					}
					
					// Result exists
					$do['exists_result_list_from_books'] = true;
					// Save first result, if video result exists, write these back, book result has priority
					$do['saved_values']['searchResult_book_id']			= $do['searchResult_book_id'];
					$do['saved_values']['searchResult_chapter_id']		= $do['searchResult_chapter_id'];
					$do['saved_values']['searchResult_paragraph_id']	= $do['searchResult_paragraph_id'];
					
					// Download
					if (DO_DOWNLOAD_RESULTS) {
						$download_result_set = $ret_resultList['download_results_text'];
					}
				} // end: No result => Did you mean suggestion
			}
			
			
			//---------------------------------------------------------------------------------------------------------------
			// Get result list from Video books
			//---------------------------------------------------------------------------------------------------------------
			if (IS__SEARCH_IN_VIDEOS) {
				
				// Restore language id
				$do['selected_language_id'] = $do['saved_values']['selected_language_id'];
				
				// Save values from books results
				if (DO_SEARCH_QUERY && $do['exists_result_list_from_books']) {
					save_resultList_data($do);
				}
				
				$do['db_table_prefix']				= TABLE_NAME_COMPONENT__VIDEO;
				$do['is_books']						= FALSE;
				$do['is_videos']					= TRUE;
				$do['need_neighbour_result_links']	= IS_ACTION_VIDEO;
				$do['url_is_video_books_addon']		= '&amp;video=1';
				$do['search_restrictions_Partial'] 	= $do['searchVideosPartial'];
				$do['search_restrictions_IdList'] 	= 'searchVideosIdList';
				$ret_resultList = resultList_evaluateSearchText($dataCenter, $do, $ret['search_text'], $ret['polish_form']);
				
				
				//----------------------------------------------------
				// No relevant paragraph
				//----------------------------------------------------
				if ($ret_resultList['result_text'] == NO_RELEVANT_PS_FOR_SEARCH_TEXT) {
					
					$out_video_resultlist = '<h1 class="search-failure">'.NO_RELEVANT_VIDEOS_PS_FOR_SEARCH_TEXT.'</h1>';
					
					$do['exists_result_list_from_video_books'] = false;
					
				//----------------------------------------------------
				// ..Result list exists
				//----------------------------------------------------
				} else  {
					
					$out_video_resultlist = $ret_resultList['result_text'];
					
					/*if (IS__SEARCH_IN_BOOKS_VIDEOS) {
						$out_video_resultlist = $ret_resultList['result_text'];
					} else {
						$out_video_resultlist = $ret_resultList['result_text'];
					}*/
					$out_video_player = '<div id="search-result-video-player-container">'.
											'<div id="search-result-video-player">'.
												'<div id="ytapiplayer">'.
													'You need Flash player 8+ and JavaScript enabled to view this video.'.
												'</div>'.
											'</div>'.
										'</div>';
					
					// Neighbour result links
					if (IS_ACTION_VIDEO) {
						$out_neighbour_result_links = $ret_resultList['neighbour_result_links'];
					}
					
					// Result exists
					$do['exists_result_list_from_video_books'] = true;
					
					// Write these back, book result has priority
					if ($do['exists_result_list_from_books']) {
						$do['searchResult_book_id']			= $do['saved_values']['searchResult_book_id'];
						$do['searchResult_chapter_id']		= $do['saved_values']['searchResult_chapter_id'];
						$do['searchResult_paragraph_id']	= $do['saved_values']['searchResult_paragraph_id'];
					}
					
					// Download
					if (DO_DOWNLOAD_RESULTS) {
						$download_result_set .= NEWLINE.NEWLINE.$ret_resultList['download_results_text'];
					}
				}
				
				// Restore saved values from books results for book text display
				if (DO_SEARCH_QUERY && $do['exists_result_list_from_books']) {
					restore_resultList_data($do);
				}
			}
			
			//----------------------------------------------------
			// No relevant paragraph => Did you mean suggestion
			//----------------------------------------------------
			if (!$do['exists_result_list_from_books'] && !$do['exists_result_list_from_video_books']) {
				
				// Levenshtein, Metaphone suggestions
				include_once (FS_INCLUDE_DIR.'/tools-phonemes.php');

				$ret_wordSuggestionList = word_suggestion($dataCenter, $do);
				$out_wordSuggestionList = array();
				if (!empty($ret_wordSuggestionList)) {
					foreach ($ret_wordSuggestionList as $suggested_word) {
						$out_wordSuggestionList[] = '<a href="'.WEB_INDEX_FILE.'?q='.url_encode_SearchText($suggested_word).
															$do['urlDataTransfer'].
															'&amp;sq=Search">'.$suggested_word.'</a>';
					}
					$out_wordSuggestionList = 'Did you mean: '.implode(' or ', $out_wordSuggestionList);
				} else {
					$out_wordSuggestionList = '';
				}
			}
		}
		
		// Restore some data
		$do['selected_language_id'] = $do['saved_values']['selected_language_id'];
		$do['db_table_prefix']		= $do['saved_values']['db_table_prefix'];
		
		/*
		if (!$do['exists_result_list_from_books'] && 
			$do['exists_result_list_from_video_books']) {
			$do['db_table_prefix'] = TABLE_NAME_COMPONENT__VIDEO;
		}
		*/
	} else {
		/*if (IS__SEARCH_IN_BOOKS_VIDEOS) {
			$out_resultlist 		= '<div id="search-result-items"></div>';
			$out_video_resultlist 	= '<div id="search-result-video-items"></div>';
		} else {
			if (IS__SEARCH_IN_BOOKS) {
				$out_resultlist = '<div id="search-result-items"></div>';
			}
			if (IS__SEARCH_IN_VIDEOS) {
				$out_resultlist = '<div id="search-result-items"></div>';
			}
		}*/
	}
	
	//---------------------------------------------------------------------------------------------------------------
	// Final result: Get DB prefix
	//---------------------------------------------------------------------------------------------------------------
	if (DO_SEARCH_QUERY) {
		
		if ($do['exists_result_list_from_books']) {
			$do['db_table_prefix']			= '';
			$do['first_result_from_books']	= TRUE;
			$do['first_result_from_videos']	= FALSE;
			
		} else if ($do['exists_result_list_from_video_books']) {
			
			$do['db_table_prefix']			= TABLE_NAME_COMPONENT__VIDEO;
			$do['first_result_from_books']	= FALSE;
			$do['first_result_from_videos']	= TRUE;
		} else {
			$do['db_table_prefix'] = '';
			$do['first_result_from_books']	= TRUE;
			$do['first_result_from_videos']	= FALSE;
		}
	} else {
		$do['db_table_prefix'] = DB_TABLE_PREFIX_ACTION_TYPE;
		if (IS_ACTION_VIDEO) {
			$do['first_result_from_books']	= FALSE;
			$do['first_result_from_videos']	= TRUE;
		} else {
			$do['first_result_from_books']	= TRUE;
			$do['first_result_from_videos']	= FALSE;
		}
	}
	
	
	//---------------------------------------------------------------------------------------------------------------
	// Chosen search result book text
	//---------------------------------------------------------------------------------------------------------------
	// test for ideal char num in a row
	// - good	: 45-75 char
	// - ideal	: 66 char
	//																			45					 66		  75
	//$test_line = '<div class="b24">123456789012345678901234567890123456789012  "  8901234567890123  *  9012  "  89012345678901234567890</div>';
	//$test_line = '<div class="b24">123 567 901 345 789 123 567 901 345 789 123 &lt; 789 123 567 901 34 * 890 23 &gt; 789 123 567 901 345 7890</div>';
	
	$panelButtons_for_touchScreens = 
			(IS__SITE_SIMPLIFIED_LAYOUT ?
			''
			:
			//------------------------------------------------------------------------------------
			// Open panel buttons for touch screens
			//------------------------------------------------------------------------------------
			'<div id="panel-buttons-container">'.
			
			'<div id="open-north">'.
				'<a href="javascript:void(0)" class="button"'.
					' onclick="panelOpenClose({north:\'open\'}); $(\'#open-north\').css(\'display\', \'none\');" title="Open panels">'.
					'<span class="icon icon_arrow_down"></span>'.
				'</a>'.
			'</div>'.
			'<div id="close-north">'.
				'<a href="javascript:void(0)" class="button"'.
					' onclick="panelOpenClose({north:\'close\'}); $(\'#close-north\').css(\'display\', \'none\');" title="Close header">'.
					'<span class="icon icon_arrow_up"></span>'.
				'</a>'.
			'</div>'.
			'<div id="open-west">'.
				'<a href="javascript:void(0)" class="button button-vertical"'.
					' onclick="panelOpenClose({west:\'open\'}); $(\'#open-west\').css(\'display\', \'none\');" title="Open west panel">'.
					'<span class="icon icon_arrow_right"></span>'.
				'</a>'.
			'</div>'.
			'<div id="close-west">'.
				'<a href="javascript:void(0)" class="button button-vertical"'.
					' onclick="panelOpenClose({west:\'close\'}); $(\'#open-west\').css(\'display\', \'none\');" title="Close west panel">'.
					'<span class="icon icon_arrow_left"></span>'.
				'</a>'.
			'</div>'.
			'<div id="open-south">'.
				'<a href="javascript:void(0)" class="button"'.
					' onclick="panelOpenClose({south:\'open\'}); $(\'#open-south\').css(\'display\', \'none\');" title="Open south panel">'.
					'<span class="icon icon_arrow_up"></span>'.
				'</a>'.
			'</div>'.
			'<div id="close-south">'.
				'<a href="javascript:void(0)" class="button"'.
					' onclick="panelOpenClose({south:\'close\'}); $(\'#open-south\').css(\'display\', \'none\');" title="Close south panel">'.
					'<span class="icon icon_arrow_down"></span>'.
				'</a>'.
			'</div>'.
			
			'</div>');
	
	
	//---------------------------------------------------------------------------------------------------------------
	// Book text
	//---------------------------------------------------------------------------------------------------------------
	//if (!IS__SITE_SIMPLIFIED_LAYOUT) {
		
		$do['url_is_video_books_addon']	= (IS_ACTION_VIDEO ? '&amp;video=1' : '');
		
		// From 'tools.php': Search result book text info
		/*
		// Videos
		if (IS_ACTION_VIDEO || $do['first_result_from_videos']) {
			$book_name = (!$do[TABLE_NAME_COMPONENT__VIDEO.'authors'][$selected_video_author_id]['author_book_equal'] ? 
							$do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_video_book_id]['book_title'] : '');
		// Books
		} else {
			$book_name = $do['books'][$selected_book_id]['book_title'];
		}
		*/
		$result_data =	' data-book-id="'.$do['searchResult_book_id'].$do['url_is_video_books_addon'].'"'.
						' data-chapter-id="'.$do['searchResult_chapter_id'].'"';
		/*
						
						// Book title
						($selected_book_id != -1 ? 

							// Author name
							' data-author-name="'.$do[$do['db_table_prefix'].'authors'][$do[$do['db_table_prefix'].'books'][$selected_book_id]['author_id']]['full_name'].'"'.

							// Book name
							' data-book-name="'.$book_name.'"'.

							// Chapter title
							($selected_chapter_id != -1 ? 
								' data-chapter-name="'.$do[$do['db_table_prefix'].'books'][$selected_book_id]['chapters'][$selected_chapter_id]['chapter_title'].'"' : '')
							:
							''
						);
		*/
		
		$search_result_book_text_container_wrapper_text__divs = '<div id="search-result-book-text-container" class="'.
																			$do['layout_container_class_name']['center'].'">'.
																	'<div id="search-result-book-text-wrapper">'.
																		'<div id="search-result-book-text"'.$result_data.
																				(IS_ACTION_VIDEO ? ' class="video-book"' : '').'>';

		if (!DO_REFERENCE_INFO) {

			include_once (FS_MODULES_DIR.'/result-book-text.php');
			
//$time = microtime(TRUE);
			$ret_resultBookText = resultBookText($dataCenter, $do);
//writeOut(round(microtime(TRUE)-$time, 5));
			
			switch ($ret_resultBookText['result_text']) {

				// Paragraph was not found
				case NOT_FOUND_PS:
					$out_book_text = $panelButtons_for_touchScreens.
										$search_result_book_text_container_wrapper_text__divs.
													//'<h1 class="search-failure">'.NOT_FOUND_PS.'</h1>'.
													'<h1 class="search-failure">'.$out_wordSuggestionList.'</h1>'.
										'</div></div></div>';
					break;

				// Result
				default:
					// Result text
					$out_book_text = $panelButtons_for_touchScreens.
										$search_result_book_text_container_wrapper_text__divs.
													//$test_line.
													$ret_resultBookText['result_text'].
										'</div></div></div>';
					
					// Result video
					if (IS_ACTION_VIDEO) {
						$out_video_player = '<div id="search-result-video-player-container">'.
												'<div id="search-result-video-player">'.
													'<div id="ytapiplayer">'.
														'You need Flash player 8+ and JavaScript enabled to view this video.'.
													'</div>'.
												'</div>'.
											'</div>';
					}
					break;
			} // switch ($ret_resultBookText['result_text'])
		} else {
			$out_book_text = $panelButtons_for_touchScreens.
								$search_result_book_text_container_wrapper_text__divs.
								'</div></div></div>';
		}
	/*} else {
		$out_book_text = '';
	}*/
	
	//---------------------------------------------------------------------------------------------------------------
	// Write out
	//---------------------------------------------------------------------------------------------------------------
	
	
	//-------------------------------------------------------------------------
	// Download results
	//-------------------------------------------------------------------------
	if (DO_DOWNLOAD_RESULTS) {
		
		Download_Result_Content_Into_File($do, $download_result_set);
		exit;
		
		
	//-------------------------------------------------------------------------
	// Display results
	//-------------------------------------------------------------------------
	} else {
		
		include_once (FS_INCLUDE_DIR.'/header.php');
		
		if (IS__SITE_SIMPLIFIED_LAYOUT) {
			
			echo 	Get_Header($do).			// id="header-navigation"
					
					'<div id="search-result-wrapper">'.
						'<div id="search-result-items-container" class="'.$do['layout_container_class_name']['west'].'">'.
							(IS__SEARCH_IN_BOOKS ? 
								'<div id="search-result-items">'.
									$out_resultlist.		// id="search-result-items"
								'</div>'
								: '').
							(IS__SEARCH_IN_VIDEOS ?
								'<div id="search-result-video-items">'.
									$out_video_resultlist.	// id="search-result-video-items"
								'</div>'
								: '').
						'</div>'.
						$out_book_text.			// id="search-result-book-text"
					'</div>'.
					$out_video_player;			// id="search-result-video-player"
			
		} else {
			echo 	Get_Header($do).		// id="header-navigation"
					
					'<div id="search-result-items-container" class="'.$do['layout_container_class_name']['west'].'">'.
						(IS__SEARCH_IN_BOOKS ? 
							'<div id="search-result-items">'.
								$out_resultlist.		// id="search-result-items"
							'</div>'
							: '').
						(IS__SEARCH_IN_VIDEOS ?
							'<div id="search-result-video-items">'.
								$out_video_resultlist.	// id="search-result-video-items"
							'</div>'
							: '').
					'</div>'.
					$out_book_text.			// id="search-result-book-text"
					$out_video_player.		// id="search-result-video-player"

					Get_Footer($dataCenter, $do, $time_start, $out_neighbour_result_links);	// id="footer-result-links" , id="footer-right-side"
		}
	}
?>