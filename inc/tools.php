<?php
/*-----------
* Functions:
* -----------
	Get_Books_data($dataCenter, &$do)
	Store_Search_Query_in_History(&$do)
	
 	Get_Header($do)
	Get_Footer($dataCenter, $do, $time_start, $neighbour_result_links = '')
	Get_PreviousNext_ChapterLinks($dataCenter, $do)
	Get_Language_Selection_Button($do, $grouped)
	
	getPageHeader_metaDescription($do, $action)
	
	convert_SiteOperationMode_valueToArray($do, $siteOperationMode)
	convert_SiteOperationMode_arrayToValue($do, $siteOperationMode_Values)
	convert_BookIdList_to_BookListNumber($book_id_list)
	convert_BookListNumber_to_BookIdList($searchBooksCode)
	
	to_utf8( $string )
	
	Encrypt($s)
	Decrypt($crypt_s)

	compress__word_book_ps_data($word_book_ps_data)
	uncompress__word_book_ps_data($word_book_ps_data)

	compress__word_phrases_data($word_phrases_data)
	uncompress__word_phrases_data($word_phrases_data)

	download_result_content_into_file($do, $str)
*/

	//--------------------------------------------------------------------------------------------------------------------
	// Get Books data (title, chapters, authors)
	//--------------------------------------------------------------------------------------------------------------------
	function Get_Books_data($dataCenter, &$do) {
		
		//-------------------------------------------------------------
		// Get Author id, Language id from Book id
		//-------------------------------------------------------------
		if (isset($do['searchResult_book_id']) && 
			(!isset($do['searchResult_author_id']) || !isset($do['searchResult_language_id']))) {
			
			$query = 'SELECT author_id, language_id'.
						' FROM ' . DB_TABLE_PREFIX_ACTION_TYPE . BOOK_TABLE.
						' WHERE book_id = ?';
			$res = $dataCenter->SQLite_DB->prepare($query);
			$query_bind_values = array($do['searchResult_book_id']);
			$res->execute($query_bind_values);
			$row = $res->fetch(PDO::FETCH_NUM);
			$do['searchResult_author_id']	= $row[0];
			$do['searchResult_language_id']	= $row[1];
		}
		
		$query_bind_values = array();
		
		//-------------------------------------------------------------
		// Get language data
		//-------------------------------------------------------------
		if (IS__SEARCH_IN_VIDEOS) {
			
			$do['languages'] = array();
			$tmp_lang = array();
			
			$query = 'SELECT language_id, lang_code_display, lang_original'.
						//' FROM ' . DB_TABLE_PREFIX_ACTION_TYPE . LANGUAGE_TABLE;
						' FROM ' . VIDEO_LANGUAGE_TABLE;
			$res = $dataCenter->SQLite_DB->prepare($query);
			$res->execute();
			while ($row = $res->fetch(PDO::FETCH_NUM)) {
				$tmp_lang[$row[1]] = $row[0];
				
				$do['languages'][$row[0]] = array(
					'lang_code_display'	=> $row[1],
					'lang_original'		=> $row[2]
				);
			}
			
			// lang id check
			if (($do['selected_language_id'] === NULL) or
				(count($do['languages']) < $do['selected_language_id'])) {
				$do['selected_language_id'] = (isset($do['searchResult_language_id']) ? $do['searchResult_language_id'] : $tmp_lang[DEFAULT_LANGUAGE_CODE]);
			}
			$do['urlData']['l']				= 'l='.$do['selected_language_id'];
			$do['urlKeyValuePairs']['l']	= $do['selected_language_id'];
		}
		
		// Query addon when language selected
		if ($do['selected_language_id'] !== NULL) {
			
			// Books data
			$query_addon_language_where_books		= '';
			$query_addon_language_where_videos		= ' WHERE b.language_id=?';
			
			// Chapters data
			$query_addon_language_innerJoin_books	= ' INNER JOIN '.BOOK_TABLE			.' AS b USING (book_id)';
			$query_addon_language_innerJoin_videos	= ' INNER JOIN '.VIDEO_BOOK_TABLE	.' AS b USING (book_id)';
			
			// Language id
			$query_bind_values_books				= array();
			$query_bind_values_videos				= array($do['selected_language_id']);
			
		// ..otherwise all data comes
		} else {
			$query_addon_language_innerJoin_books	= '';
			$query_addon_language_innerJoin_videos	= '';
			$query_addon_language_where_books		= '';
			$query_addon_language_where_videos		= '';
			$query_bind_values_books				= array();
			$query_bind_values_videos				= array();
		}
		
		
		//-------------------------------------------------------------
		// Get Books or Videos data
		//-------------------------------------------------------------
		
		// Books
		if (IS__SEARCH_IN_BOOKS) {
			
			$do['books'] = $do['authors'] = array();
			
			$query = 'SELECT b.book_id, b.book_name, b.font_code, a.author_id, a.author_full_name'.
						' FROM '		. BOOK_TABLE	. ' AS b'.
						' INNER JOIN '	. AUTHOR_TABLE	. ' AS a USING (author_id)'.
						$query_addon_language_where_books.
						' ORDER BY b.book_name_url';
			$res = $dataCenter->SQLite_DB->prepare($query);
			$res->execute($query_bind_values_books);
			while ($row = $res->fetch(PDO::FETCH_NUM)) {
				$do['books'][$row[0]] = array(	'book_title' 			=> $row[1], 
												'book_font_code' 		=> $row[2],
												'author_id'				=> $row[3],
												'chapters' 				=> array());
				$do['authors'][$row[3]]['full_name'] 				= $row[4];
				$do['authors'][$row[3]]['book_id_list'][$row[0]] 	= 1;
			}
		}
		
		// Videos
		if (IS__SEARCH_IN_VIDEOS) {
			
			$do[TABLE_NAME_COMPONENT__VIDEO.'books'] = $do[TABLE_NAME_COMPONENT__VIDEO.'authors'] = array();
			
			$query = 'SELECT b.book_id, b.book_name, b.font_code, a.author_id, a.author_full_name, b.author_book_equal'.
						' FROM '		. TABLE_NAME_COMPONENT__VIDEO . BOOK_TABLE		. ' AS b'.
						' INNER JOIN '	. TABLE_NAME_COMPONENT__VIDEO . AUTHOR_TABLE	. ' AS a USING (author_id)'.
						$query_addon_language_where_videos.
						' ORDER BY b.book_id';
			$res = $dataCenter->SQLite_DB->prepare($query);
			$res->execute($query_bind_values_videos);
			while ($row = $res->fetch(PDO::FETCH_NUM)) {
				$do[TABLE_NAME_COMPONENT__VIDEO . 'books'][$row[0]] = array('book_title' 			=> $row[1], 
																			'book_font_code' 		=> $row[2],
																			'author_id'				=> $row[3],
																			'chapters' 				=> array());
				$do[TABLE_NAME_COMPONENT__VIDEO . 'authors'][$row[3]]['full_name'] 				= $row[4];
				$do[TABLE_NAME_COMPONENT__VIDEO . 'authors'][$row[3]]['author_book_equal'] 		= $row[5];
				$do[TABLE_NAME_COMPONENT__VIDEO . 'authors'][$row[3]]['book_id_list'][$row[0]] 	= 1;
			}
		}
		
		
		// Books
		if (IS__SEARCH_IN_BOOKS) {
			if (!IS__SITE_SIMPLIFIED_LAYOUT) {
				
				//-------------------------------------------------------------
				// Check for all books selected
				//-------------------------------------------------------------
				if (($do['searchBookListCode'] == strval(ALL_BOOKS__BOOK_ID)) or 
					($do['searchBookListNum'] == count($do['books']))) {
					$do['searchBookListCode'] 		= ALL_BOOKS__BOOK_ID;
					$do['urlKeyValuePairs']['blc'] 	= $do['searchBookListCode'];
					$do['urlData']['blc'] 			= 'blc='.$do['searchBookListCode'];
					$do['urlDataTransfer'] 			= '&amp;'.implode('&amp;', $do['urlData']);
				
					//-------------------------------------------------------------
					// Books to search in -> All books => 0
					// no previous settings or All books
					//-------------------------------------------------------------
					$do['searchBooksPartial'] = false;

				//..selected books
				} else {
					$do['searchBooksPartial'] = true;
				}
			}
		}
		
		// Videos
		if (IS__SEARCH_IN_VIDEOS) {
			
			//-------------------------------------------------------------
			// Check for all books selected
			//-------------------------------------------------------------
			if (($do['searchVideoListCode'] == strval(ALL_BOOKS__BOOK_ID)) or 
				($do['searchVideoListNum'] == count($do[TABLE_NAME_COMPONENT__VIDEO . 'books']))) {
				$do['searchVideoListCode'] 		= ALL_BOOKS__BOOK_ID;
				$do['urlKeyValuePairs']['vlc'] 	= $do['searchVideoListCode'];
				$do['urlData']['vlc'] 			= 'vlc='.$do['searchVideoListCode'];
				$do['urlDataTransfer'] 			= '&amp;'.implode('&amp;', $do['urlData']);

				//-------------------------------------------------------------
				// Books to search in -> All books => 0
				// no previous settings or All books
				//-------------------------------------------------------------
				$do['searchVideosPartial'] = false;

			//..selected books
			} else {
				$do['searchVideosPartial'] = true;
			}
		}
		
		
		//-------------------------------------------------------------
		// Get Books, Videos Chapters' data
		//-------------------------------------------------------------
		
		// Books
		if (IS__SEARCH_IN_BOOKS) {
			
			$do['chapters'] = array();
			
			$query = 'SELECT c.book_id, c.book_chapter_id, c.chapter_number, c.chapter_title'.
						' FROM '.CHAPTER_TABLE. ' AS c'.
						$query_addon_language_innerJoin_books.
						$query_addon_language_where_books;
			$res = $dataCenter->SQLite_DB->prepare($query);
			$res->execute($query_bind_values_books);
			while ($row = $res->fetch(PDO::FETCH_NUM)) {
				$do['books'][$row[0]]['chapters'][$row[1]]['chapter_title']			= $row[3];
				$do['books'][$row[0]]['chapters'][$row[1]]['chapter_numbered']		= ($row[2] != 0 ? true : false);
			}
		}
		
		// Videos
		if (IS__SEARCH_IN_VIDEOS) {
			
			$do[TABLE_NAME_COMPONENT__VIDEO.'chapters'] = array();
			
			$query = 'SELECT c.book_id, c.book_chapter_id, c.chapter_number, c.chapter_title, c.video_id'.
						' FROM '.VIDEO_CHAPTER_TABLE.' AS c'.
						$query_addon_language_innerJoin_videos.
						$query_addon_language_where_videos;
			$res = $dataCenter->SQLite_DB->prepare($query);
			$res->execute($query_bind_values_videos);
			while ($row = $res->fetch(PDO::FETCH_NUM)) {
				$do[TABLE_NAME_COMPONENT__VIDEO.'books'][$row[0]]['chapters'][$row[1]]['chapter_title'] 	= $row[3];
				$do[TABLE_NAME_COMPONENT__VIDEO.'books'][$row[0]]['chapters'][$row[1]]['chapter_numbered'] 	= ($row[2] != 0 ? true : false);
				$do[TABLE_NAME_COMPONENT__VIDEO.'books'][$row[0]]['chapters'][$row[1]]['video_id'] 			= $row[4];
			}
		}
	} // end: Get_Books_data($dataCenter, &$do)
	
	
	//--------------------------------------------------------------------------------------------------------------------
	// Store Search query in History
	//--------------------------------------------------------------------------------------------------------------------
	function Store_Search_Query_in_History(&$do) {
		
		if ((DO_SEARCH_QUERY or DO_BOOK_READ) and IS__SITE_HISTORY_TYPE__ON and !empty($do['url_encoded_do_search_text'])) {
			$needSetCookie['c_sh'] = false;
			
			// Load search history
			// Store latest one if it's different from the stored ones
			// ..$needSetCookie from /inc/settings.php
			if (empty($do['search_history'])) {
				$do['search_history_pos'] = 0;
				$do['search_history'][$do['search_history_pos']] = $do['url_encoded_do_search_text'];
				$needSetCookie['c_sh'] = true;

			} else {
				$found 				= false;
				$curr_pos_history 	= 0;
				$max_pos_history	= count($do['search_history']) - 1;
				while (!$found and ($curr_pos_history <= $max_pos_history)) {
					if ($do['search_history'][$curr_pos_history] == $do['url_encoded_do_search_text']) {
						$found = true;
					} else {
						++$curr_pos_history;
					}
				}
				if (!$found) {
					++$do['search_history_pos'];
					$do['search_history'][$do['search_history_pos']] = $do['url_encoded_do_search_text'];
					$needSetCookie['c_sh'] = true;
				}
			}
			/*
			// Store latest one if it's different from the last stored one
			} else if ($do['search_history'][$do['search_history_pos']] != $do['url_encoded_do_search_text']) {
				++$do['search_history_pos'];
				$do['search_history'][$do['search_history_pos']] = $do['url_encoded_do_search_text'];
				$needSetCookie['c_sh'] = true;
			}*/
			//------------------------
			// Store the cookie
			//------------------------
			if ($needSetCookie['c_sh']) {
				$t = $do['search_history_pos'].SEARCH_HISTORY_POS_DATA_SEPARATOR.
						implode(SEARCH_HISTORY_DATA_ITEMS_SEPARATOR, $do['search_history']);
				setcookie('c_sh', $t, time() + 86400, '/', COOKIE_DOMAIN, false, true);
			}
		}
	} // end: Store_Search_Query_in_History(&$do)
	
	
	//--------------------------------------------------------------------------------------------------------------------
	// Header data, links
	//--------------------------------------------------------------------------------------------------------------------
	function Get_Header($do) {
		
		$urlData 					= (!empty($do['url_encoded_do_search_text']) ? '&amp;q='.$do['url_encoded_do_search_text'] : '').
										$do['urlDataTransfer'];
		$selected_chapter_id 		= (isset($do['searchResult_chapter_id']) ? $do['searchResult_chapter_id'] : -1);
		$selected_book_id			= (isset($do['searchResult_book_id']) ? $do['searchResult_book_id'] : -1);
		$selected_author_id			= (isset($do['searchResult_author_id']) ? $do['searchResult_author_id'] : -1);
		
		if (IS__SITE_BOOK_READING_LAYOUT) {
			// Videos
			
			if (IS_ACTION_VIDEO || $do['first_result_from_videos']) {
				$selected_book_book_id		= -1;
				$selected_book_author_id	= -1;
				$selected_video_book_id		= $selected_book_id;
				$selected_video_author_id	= $selected_author_id;
				
			// Books
			} else {
				$selected_book_book_id		= $selected_book_id;
				$selected_book_author_id	= $selected_author_id;
				$selected_video_book_id		= -1;
				$selected_video_author_id	= -1;
			}
		}
		
		$url_is_one_col_force_addon	= (IS__SITE_ONE_COLUMN_FORCE ? '&amp;one_col=1' : '');
		
		$url_postpart_index 		= (!empty($do['urlData']) ? '?'.implode('&amp;', $do['urlData']) : '');
		
		$url_is_video_books_addon	= (IS_ACTION_VIDEO ? '&amp;video=1' : '');
		$url_video_books_addon		= '&amp;video=1';
		
		$ajaxDataLoader_args_addon 	= IS__SEARCH_IN_VIDEOS ? ', result_list_video: 1' : '';
		
		//  class="blue"
		//  border:1px solid black;
		/*
			onmouseover="Tip(\'Enter your search terms here\', WIDTH, 200, FOLLOWMOUSE, false, 
								FIX, [\'search-text\', 184, 4], FIXINSIDE, false, ABOVE, false, PADDINGX, 12, PADDINGY, 10, FONTSIZE, \'1.4em\')" 
			onmouseout="UnTip()" 
		*/
		$header_links = 
		 	//'<div id="build-number">build: '.BUILD_NUMBER.' '.(USE_ENCRYPTION_TABLE_DATA ? 'crypt' : 'nocrypt').'</div>'.
		 	//'<div id="build-number">'.(IS__SITE_SIMPLIFIED_LAYOUT ? 'simplified' : '').
			//							(IS__SITE_VIDEO_CAPTIONS_IN_USE ? '<br/>video captions' : '').'</div>'.
		 	//'<div id="build-number"></div>'.
			
			'<div id="header-container" class="'.$do['layout_container_class_name']['north'].'">'.
			
				'<div id="header">'.
					
					'<div id="header-search-navigation-bar">'.
						
						getSiteSpecificContent('header_logo', array('index_file' => WEB_INDEX_FILE, 
																	'url_postpart_index' => $url_postpart_index)).
						
						'<div id="search-form-header-navigation">'.
							
							'<form id="search-form" method="get" action="'.WEB_INDEX_FILE.'">'.
								'<div id="search-form-items">'.
									'<div id="search-text-button">'.
										'<input type="text" id="search-text" name="q" maxlength="100" size="40"'.
												' autocomplete="off" title="Enter your search terms here"'.
												' value="'.str_replace(array('"', '_'), array('&quot;', ' '), $do['search_text']).'"'.
												' onkeypress="keyBoardNav_onKeyPress(event, \''.WEB_INDEX_FILE.'?'.'\', \'search-form\');"'.
												' onkeydown="keyBoardNav(event, this.id);"'.
												' onkeyup="autoSuggest(this.id, \'search-list-wrap\', \'search-list\', \'search-text\','.
														' event, \''.$do['selected_language_id'].'\','.
														'\''.AUTOSUGGEST_LIST_MAX_ITEM_NUMBER_SHOW.'\');" />'.
										
										'<input type="hidden" name="sq" value="'.SEARCH_BUTTON_TEXT.'" />'.
										
										'<div id="search-icon">'.
											'<a href="javascript:void(0)" class="button button-eye"'.
												' onclick="ajaxDataLoader({ url: \''.WEB_INDEX_FILE.'?'.'\' + $(\'#search-form\').serialize(), search: 1'.
																			$ajaxDataLoader_args_addon.' });">'.
												'<span class="icon icon-eye icon_eye"></span>'.
											'</a>'.
										'</div>'.
									'</div>'.
									'<div id="search-list-wrap">'.
										'<ul id="search-list"><li></li></ul>'.
									'</div>'.
								'</div>'.
								'<div id="header-url-data">';
									foreach ($do['urlData'] as $key => $value) {
										if ($key !== 'one_col') {
											$header_links .= '<input type="hidden" name="'.$key.'" value="'.$do['urlKeyValuePairs'][$key].'" />';
										}
									}
		$header_links .= '</div>'.
						'</form>';
						
		//------------------------------------------------------------------------------------
		// 1st row: Navigation buttons
		//------------------------------------------------------------------------------------
		$header_links .= '<div id="header-navigation" class="buttons">';
		
		
		if (IS__SITE_BOOK_READING_LAYOUT) {
			
			//---------------------------------------------------
			// Books, Videos with chapters
			//
			// button tooltips: flicker
			// 	onmouseover="Tip(\'Books\', WIDTH, 46, FONTSIZE, \'1.3em\', PADDINGX, 12, PADDINGY, 4, 
			//					FOLLOWMOUSE, false, FIX, [\'book-selector-a\', 76, 2], FIXINSIDE, false, ABOVE, false)" 
			//	onmouseout="UnTip()"
			//---------------------------------------------------
			
			//---------------------------------------------------
			// Books
			//---------------------------------------------------
			if (IS__SEARCH_IN_BOOKS) {
			
				$header_links .= 
					
					'<a href="javascript:void(0)" class="button left" id="book-selector-a" onclick="toggle(\'book-selector\');" title="Books">'.
						'<span class="icon icon_book" id="book-selector-span"></span>'.
					'</a>'.
					
					'<ul id="book-selector" style="display:none;">';
				
				foreach ($do['books'] as $book_id => $book_data) {

					if ($book_data['book_font_code'] != 0) {
						$book_font_class = ' class="'.$do['font_code__font_name'][$book_data['book_font_code']].'"';
						$book_font_class_name = ' '.$do['font_code__font_name'][$book_data['book_font_code']];
					} else {
						$book_font_class = $book_font_class_name = '';
					}
					$curr_book_is_selected = ($selected_book_book_id == $book_id);
					$header_links .= '<li id="bn'.$book_id.'">'.
											'<span onclick="toggle_book(\'bn'.$book_id.'\'); return false;"'.
													($curr_book_is_selected ? 
														' class="item-selected'.$book_font_class_name.'"' : $book_font_class).'>'.
													$book_data['book_title'].'</span>'.
											'<ul style="display:none;"'.$book_font_class.'>';

					//..Chapters of the book
					foreach ($do['books'][$book_id]['chapters'] as $chapter_id => $chapter_data) {
						$classData = array();
						if ($chapter_data['chapter_numbered']) 		{ $classData[] = 'chapter-numbered'; }
						if ($curr_book_is_selected && 
							($selected_chapter_id == $chapter_id)) 	{ $classData[] = 'item-selected'; }
						if (!empty($classData))						{ $classStr = ' class="'.implode(' ', $classData).'"'; } else { $classStr = ''; }
						$header_links .= '<li>'.
											'<a href="javascript:void(0);"'.
											' onclick="ajaxDataLoader({'.
													' url: \''.WEB_INDEX_FILE.'?b='.$book_id.'&amp;c='.$chapter_id.$urlData.
																					$url_is_one_col_force_addon.
													(IS__SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK ?
														'#c'.$chapter_id.'\''.
														', anchor: \'c'.$chapter_id.'\''
														:
														'\''
													).
													//(IS__SITE_ONE_COLUMN_FORCE ? ', one_col: \'1\'' : '').
											'});"'.
												$classStr.
												'>'.$chapter_data['chapter_title'].
											'</a>'.
										'</li>';
					}
					$header_links .= '</ul>'.
									'</li>';
				}
				$header_links .= '</ul>';
			} // Books
			
			//---------------------------------------------------
			// Video Books
			//---------------------------------------------------
			if (IS__SEARCH_IN_VIDEOS) {
				
				$header_links .= 
					
					'<a href="javascript:void(0)" class="button middle" id="video-selector-a" onclick="toggle(\'video-selector\');" title="Videos">'.
						'<span class="icon icon_video" id="video-selector-span"></span>'.
					'</a>'.
					
					'<ul id="video-selector" style="display:none;">';
				
				//--------------------------------------------------
				// Authors..
				//--------------------------------------------------
				foreach ($do[TABLE_NAME_COMPONENT__VIDEO.'authors'] as $author_id => $author_data) {
					
					$author_class = ($selected_video_author_id == $author_id ? ' class="item-selected"' : '');
					
					//--------------------------------------------------
					// Author name
					//--------------------------------------------------
					$header_links .= '<li id="van'.$author_id.'"'.$author_class.'>'.
										'<span onclick="toggle_book(\'van'.$author_id.'\'); return false;"'.
												(($selected_video_author_id == $author_id) ? ' class="item-selected"' : '').'>'.
												$author_data['full_name'].'</span>'.
											'<ul style="display:none;">';


					//--------------------------------------------------
					//..Author's Books
					//--------------------------------------------------
					foreach ($author_data['book_id_list'] as $book_id => $t) {
						
						$book_data	= $do[TABLE_NAME_COMPONENT__VIDEO.'books'][$book_id];
						
						// If (author != book name) => need book name here
						if (!$do[TABLE_NAME_COMPONENT__VIDEO.'authors'][$author_id]['author_book_equal']) {
							$book_class = ($selected_video_book_id == $book_id ? ' class="item-selected"' : '');
						
							$header_links .= '<li id="vbn'.$book_id.'"'.$book_class.'>'.
												'<span onclick="toggle_book(\'vbn'.$book_id.'\'); return false;"'.
														(($selected_video_book_id == $book_id) ? ' class="item-selected"' : '').'>'.
														$book_data['book_title'].'</span>'.
													'<ul style="display:none;">';
							$header_links_post = '</ul></li>';
						} else {
							$header_links_post = '';
						}
						
						//..Chapters of the book
						$pp_playlist_pos = 0;
						
						foreach ($do[TABLE_NAME_COMPONENT__VIDEO.'books'][$book_id]['chapters'] as $chapter_id => $chapter_data) {
							
							$classData = array();
							if ($chapter_data['chapter_numbered']) 		{ $classData[] = 'chapter-numbered'; }
							if ($selected_chapter_id == $chapter_id) 	{ $classData[] = 'item-selected'; }
							$header_links .= '<li>'.

								'<a href="javascript:void(0);" onclick="ajaxDataLoader({'.
										' url: \''.WEB_INDEX_FILE.'?b='.$book_id.'&amp;c='.$chapter_id.$urlData.
																		$url_is_one_col_force_addon.$url_video_books_addon.
										(IS__SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK ?
											'#c'.$chapter_id.'\''.
											', anchor: \'c'.$chapter_id.'\''
											:
											'\''
										).
										//(IS__SITE_ONE_COLUMN_FORCE ? ', one_col: \'1\'' : '').
										', video: \'1\''.
									'});"'.

									(!empty($chapter_data['video_id']) ?
										(!empty($classData) ? ' class="'.implode(' ', $classData).' prettyPhoto_link"' : 'class="prettyPhoto_link"').
										' rel="prettyPhoto['.$book_data['book_title'].']"'.
										' pp_href="http://www.youtube.com/watch?v='.$chapter_data['video_id'].'"'.
										' pp_video_begin="0"'.
										' pp_lang_code="'.$do['languages'][$do['selected_language_id']]['lang_code_display'].'"'.
										' pp_desc_1="'.htmlspecialchars( $chapter_data['chapter_title'], ENT_QUOTES ).'"'.
										' pp_desc_2="'.htmlspecialchars( 
												$do[TABLE_NAME_COMPONENT__VIDEO.'authors'][ $do[TABLE_NAME_COMPONENT__VIDEO.'books'][$book_id]['author_id'] ]['full_name']
											, ENT_QUOTES).'"'.
										' pp_playlist_text="'.htmlspecialchars( $chapter_data['chapter_title'], ENT_QUOTES ).'"'.
										' pp_playlist_pos="'.(++$pp_playlist_pos).'"'
										:
										(!empty($classData) ? ' class="'.implode(' ', $classData).'"' : '')
									).
									'>'.$chapter_data['chapter_title'].
								'</a>'.
							'</li>';
						}
						$header_links .= $header_links_post;
					}
					$header_links .= '</ul></li>';
				}
				$header_links .= '</ul>';
			} // Videos
			
			
			//---------------------------------------------------
			// Chapters
			//---------------------------------------------------
			$prev_chapter_url = (isset($do[$do['db_table_prefix'].'books'][$selected_book_id]['chapters'][$selected_chapter_id-1]) ?
										'b='.$selected_book_id.'&amp;c='.($selected_chapter_id-1).
											$urlData.
											$url_is_one_col_force_addon.$url_is_video_books_addon.
											(IS__SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK ? '#c'.($selected_chapter_id-1) : '') : '');
			$next_chapter_url = (isset($do[$do['db_table_prefix'].'books'][$selected_book_id]['chapters'][$selected_chapter_id+1]) ?
										'b='.$selected_book_id.'&amp;c='.($selected_chapter_id+1).
											$urlData.
											$url_is_one_col_force_addon.$url_is_video_books_addon.
											(IS__SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK ? '#c'.($selected_chapter_id+1) : '') : '');
			
			$book_font_class = (isset($do[$do['db_table_prefix'].'books'][$selected_book_id]['book_font_code']) ?
					($do[$do['db_table_prefix'].'books'][$selected_book_id]['book_font_code'] != 0 ?
						' class="'.$do['font_code__font_name'][$do[$do['db_table_prefix'].'books'][$selected_book_id]['book_font_code']].'"' : '')
					: '');
			
			$header_links .= 
					
					// Chapters
					'<a href="javascript:void(0)" class="button middle" id="chapter-selector-a" onclick="toggle(\'chapter-selector\');" title="Chapters">'.
						'<span class="icon icon_contents" id="chapter-selector-span"></span>'.
					'</a>'.
					
					'<ul id="chapter-selector" style="display:none;"'.$book_font_class.'>';
					
			if ($selected_book_id >= 0) {
				
				$pp_playlist_pos = 0;
				
				// Book title (chapters from)
				$header_links .= '<li>'.$do[$do['db_table_prefix'].'books'][$selected_book_id]['book_title'].'</li>';
				
				// Chapter titles
				foreach ($do[$do['db_table_prefix'].'books'][$selected_book_id]['chapters'] as $chapter_id => $chapter_data) {
					$classData = array();
					if ($chapter_data['chapter_numbered']) 		{ $classData[] = 'chapter-numbered'; }
					if ($selected_chapter_id == $chapter_id) 	{ $classData[] = 'item-selected'; }
					$header_links .= '<li class="'.(($selected_chapter_id == $chapter_id) ? 'item-selected' : '').'">'.
										
										'<a href="javascript:void(0);"'.
											' onclick="ajaxDataLoader({'.
													' url: \''.WEB_INDEX_FILE.'?b='.$selected_book_id.'&amp;c='.$chapter_id.$urlData.
																					$url_is_one_col_force_addon.
																					$url_is_video_books_addon.
													(IS__SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK ?
														'#c'.$chapter_id.'\''.
														', anchor: \'c'.$chapter_id.'\''
														:
														'\''
													).
													//(IS__SITE_ONE_COLUMN_FORCE ? ', one_col: \'1\'' : '').
													(IS_ACTION_VIDEO ? ', video: \'1\'' : '').
											'});"'.
											
											(IS__SEARCH_IN_VIDEOS && !empty($chapter_data['video_id']) ?
												(!empty($classData) ? ' class="'.implode(' ', $classData).' prettyPhoto_link"' : 'class="prettyPhoto_link"').
												' class="prettyPhoto_link"'.
												' rel="prettyPhoto[cl]"'.
												' pp_href="http://www.youtube.com/watch?v='.$chapter_data['video_id'].'"'.
												' pp_video_begin="0"'.
												' pp_lang_code="'.$do['languages'][$do['selected_language_id']]['lang_code_display'].'"'.
												' pp_desc_1="'.htmlspecialchars( $chapter_data['chapter_title'], ENT_QUOTES ).'"'.
												' pp_desc_2="'.htmlspecialchars( 
														$do[TABLE_NAME_COMPONENT__VIDEO.'authors'][ $do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_video_book_id]['author_id'] ]['full_name']
													, ENT_QUOTES).'"'.
												' pp_playlist_text="'.htmlspecialchars( $chapter_data['chapter_title'], ENT_QUOTES ).'"'.
												' pp_playlist_pos="'.(++$pp_playlist_pos).'"'
												:
												(!empty($classData) ? ' class="'.implode(' ', $classData).'"' : '')
											).
											'>'.$chapter_data['chapter_title'].
										'</a>'.
									'</li>';
				}
			} else {
				$header_links .= '<li style="text-align:center;">Select a book first.</li>';
			}
			$header_links .= '</ul>';
			
			// Prev/Next Chapter buttons
			$header_links .= 
					
					// Previous Chapter
					'<a class="button middle'.
							(IS__SEARCH_IN_VIDEOS && 
							!empty($prev_chapter_url) &&
							!empty($do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_video_book_id]['chapters'][$selected_chapter_id-1]['video_id']) ? 
								' prettyPhoto_link' : '').
							'" id="chapter-previous-link-a" title="Previous section"'.
						(!empty($prev_chapter_url) ? 
						' href="javascript:void(0);"'.
						' onclick="ajaxDataLoader({'.
								' url: \''.WEB_INDEX_FILE.'?'.$prev_chapter_url.'\''.
								(IS__SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK ? ', anchor: \'c'.($selected_chapter_id-1).'\'' : '').
								//(IS__SITE_ONE_COLUMN_FORCE ? ', one_col: \'1\'' : '').
								(IS_ACTION_VIDEO ? ', video: \'1\'' : '').
							'});"'.
							
							(IS__SEARCH_IN_VIDEOS && !empty($do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_video_book_id]['chapters'][$selected_chapter_id-1]['video_id']) ?
								' rel="prettyPhoto"'.
								' pp_href="http://www.youtube.com/watch?v='.$do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_video_book_id]['chapters'][$selected_chapter_id-1]['video_id'].'"'.
								' pp_video_begin="0"'.
								' pp_lang_code="'.$do['languages'][$do['selected_language_id']]['lang_code_display'].'"'.
								' pp_desc_1="'.htmlspecialchars( $do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_video_book_id]['chapters'][$selected_chapter_id-1]['chapter_title']
									, ENT_QUOTES).'"'.
								' pp_desc_2="'.htmlspecialchars( 
										$do[TABLE_NAME_COMPONENT__VIDEO.'authors'][ $do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_video_book_id]['author_id'] ]['full_name']
									, ENT_QUOTES).'"'
								:
								''
							)
							:
							''
						).
						'><span class="icon icon_arrow_left"></span>'.
					'</a>'.
					
					// Next Chapter
					'<a class="button right'.
							(IS__SEARCH_IN_VIDEOS && 
							!empty($next_chapter_url) &&
							!empty($do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_video_book_id]['chapters'][$selected_chapter_id+1]['video_id']) ? 
								' prettyPhoto_link' : '').
							'" id="chapter-next-link-a" title="Next section"'.
						(!empty($next_chapter_url) ? 
						' href="javascript:void(0);"'.
						' onclick="ajaxDataLoader({'.
								' url: \''.WEB_INDEX_FILE.'?'.$next_chapter_url.'\''.
								(IS__SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK ? ', anchor: \'c'.($selected_chapter_id+1).'\'' : '').
								//(IS__SITE_ONE_COLUMN_FORCE ? ', one_col: \'1\'' : '').
								(IS_ACTION_VIDEO ? ', video: \'1\'' : '').
							'});"'.
							
							(IS__SEARCH_IN_VIDEOS && !empty($do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_video_book_id]['chapters'][$selected_chapter_id+1]['video_id']) ?
								' rel="prettyPhoto"'.
								' pp_href="http://www.youtube.com/watch?v='.$do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_video_book_id]['chapters'][$selected_chapter_id+1]['video_id'].'"'.
								' pp_video_begin="0"'.
								' pp_lang_code="'.$do['languages'][$do['selected_language_id']]['lang_code_display'].'"'.
								' pp_desc_1="'.htmlspecialchars( $do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_video_book_id]['chapters'][$selected_chapter_id+1]['chapter_title']
									, ENT_QUOTES).'"'.
								' pp_desc_2="'.htmlspecialchars( 
										$do[TABLE_NAME_COMPONENT__VIDEO.'authors'][ $do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_video_book_id]['author_id'] ]['full_name']
									, ENT_QUOTES).'"'
								:
								''
							)
							:
							''
						).
						'><span class="icon icon_arrow_right"></span>'.
					'</a>';
					
			
			//---------------------------------------------------
			// Font size
			//---------------------------------------------------
			$header_links .= 
					
					// Font size
					'<a href="javascript:void(0)" class="button button-small left left-gap" id="font-size-selector-a"'.
						' onclick="toggle(\'font-size-selector\');" title="Font size">'.
						'<span class="icon icon-small icon_big_a" id="font-size-selector-span"></span>'.
					'</a>'.
					
					'<ul id="font-size-selector" style="display:none;">'.
						'<li><a href="javascript:void(0)" id="font-size-increase" onclick="changeSearchResultFontSize(1);">Increase font size</a></li>'.
						'<li><a href="javascript:void(0)" id="font-size-reset" onclick="changeSearchResultFontSize(0);">Reset font size</a></li>'.
						'<li><a href="javascript:void(0)" id="font-size-decrease" onclick="changeSearchResultFontSize(-1);">Decrease font size</a></li>'.
						
						'<li><a href="javascript:void(0)" id="width-increase" onclick="changeSearchResultWidth(20);">Increase text width</a></li>'.
						'<li><a href="javascript:void(0)" id="width-reset" onclick="changeSearchResultWidth(0);">Reset text width</a></li>'.
						'<li><a href="javascript:void(0)" id="width-decrease" onclick="changeSearchResultWidth(-20);">Decrease text width</a></li>'.
					'</ul>';
		// if (IS__SITE_BOOK_READING_LAYOUT)
		} else {
			/*
			$book_font_class = $do[$do['db_table_prefix'].'books'][$selected_book_id]['book_font_code'] != 0 ?
				' class="'.$do['font_code__font_name'][$do[$do['db_table_prefix'].'books'][$selected_book_id]['book_font_code']].'"' : '';
			 */
		}
		
		
		//---------------------------------------------------
		// Settings
		//---------------------------------------------------
		$header_links .= 
				
				// Settings
				'<a href="javascript:void(0)" class="button left left-gap" id="settings-selector-a" onclick="toggle(\'settings-selector\');" title="Settings">'.
					'<span class="icon icon_gear" id="settings-selector-span"></span>'.
				'</a>'.
				
				'<ul id="settings-selector" style="display:none;'.
						(IS__SITE_PERMISSION__DOWNLOAD_RESULTS ? 'width:175px;height:215px;' : '').'">'.
					
					(!IS__SITE_SIMPLIFIED_LAYOUT ?
					
						// not simplified layout
						'<li><a href="javascript:void(0);" onclick="ajaxDataLoader({'.' url: \''.WEB_INDEX_FILE.'?page=settings&amp;tab=book_list'.$urlData.'\''.
								', page: \'settings\''.', tab: \'tab-book-list\''.'});"'.'>Book list</a></li>'.
							
						'<li><a href="javascript:void(0);" onclick="ajaxDataLoader({'.' url: \''.WEB_INDEX_FILE.'?page=settings&amp;tab=video_list'.$urlData.'\''.
								', page: \'settings\''.', tab: \'tab-video-list\''.'});"'.'>Video list</a></li>'.
							
						'<li><a href="javascript:void(0);" onclick="ajaxDataLoader({'.' url: \''.WEB_INDEX_FILE.'?page=settings&amp;tab=preferences'.$urlData.'\''.
								', page: \'settings\''.', tab: \'tab-preferences\''.'});"'.'>Preferences</a></li>'
						
						:
						// simplified layout
						'<li><a href="#" id="settings-set-book-reading" style="border-bottom:1px solid #cccccc;" urldata="'.$urlData.'">Book reading ('.
								(IS__SITE_BOOK_READING_LAYOUT ? 'On' : 'Off').')</a></li>'
					).
					
					'<li><a href="javascript:void(0);" onclick="ajaxDataLoader({'.' url: \''.WEB_INDEX_FILE.'?page=settings&amp;tab=history'.$urlData.'\''.
							', page: \'settings\''.', tab: \'tab-history\''.'});"'.'>History</a></li>'.
						
					'<li><a href="javascript:void(0);" onclick="ajaxDataLoader({'.' url: \''.WEB_INDEX_FILE.'?page=settings&amp;tab=help'.$urlData.'\''.
							', page: \'settings\''.', tab: \'tab-help\''.'});"'.'>Help</a></li>'.
						
					'<li><a href="javascript:void(0);" onclick="ajaxDataLoader({'.' url: \''.WEB_INDEX_FILE.'?page=settings&amp;tab=about'.$urlData.'\''.
							', page: \'settings\''.', tab: \'tab-about\''.'});"'.'>About</a></li>'.
						
					'<li><a href="javascript:void(0);" onclick="ajaxDataLoader({'.' url: \''.WEB_INDEX_FILE.'?page=settings&amp;tab=contact-us'.$urlData.'\''.
							', page: \'settings\''.', tab: \'tab-contact-us\''.'});"'.'>Contact us</a></li>'.
					
					(IS__SITE_PERMISSION__DOWNLOAD_RESULTS ?
						'<li><a href="javascript:void(0);" onclick="ajaxDataLoader({'.' url: \''.WEB_INDEX_FILE.'?page=settings&amp;tab=download-results'.$urlData.'\''.
								', page: \'settings\''.', tab: \'tab-download-results\''.'});"'.'>Download results</a></li>'
						:
						''
					).
				'</ul>';
		
		//---------------------------------------------------
		// Languages
		//---------------------------------------------------
		/*
		if (IS__SEARCH_IN_VIDEOS) {
			$header_links .= Get_Language_Selection_Button($do, true);
		}
		*/
		
		//---------------------------------------------------
		// Toggle results
		//---------------------------------------------------
		/*
		$header_links .= '<a href="javascript:void(0)" class="button middle" title="Toggle results" 
								onclick="myLayout.toggle(\'west\')"><span class="icon icon_eye"></span>'.
						'</a>';
		*/
		
		if (IS__SITE_BOOK_READING_LAYOUT) {
			//---------------------------------------------------
			// Hide header
			// icon 48 (x) 65 (up) 131 (monitor)
			//---------------------------------------------------
			$header_links .= 
					'<a href="javascript:void(0)" id="hide-header" class="button right" onclick="closeAllLayoutPanes()" title="Hide all panels">'.
						'<span class="icon icon_arrow_up_strong"></span>'.
					'</a>';
		}
		
		
		//---------------------------------------------------
		// Display Search result book text info
		//---------------------------------------------------
		/*
		$header_links .= 
				'<a href="javascript:void(0)" id="show-book-text-info" class="button right right-gap" title="Display search result book text info">
					<span class="icon icon_arrow_down"></span>
				</a>';
		*/
		
		// Closing navigation bar, Preloader
		$header_links .= '</div></div>'. // header-navigation, search-form-header-navigation
					'<div id="search-preloader"></div>'.
				'</div>'; // header-search-navigation-bar
		
		//------------------------------------------------------------------------------------
		// Search result book text info
		//------------------------------------------------------------------------------------
		//$header_links .= '<div id="header-info-container"><div id="header-info">'.
		
		// Videos
		if (IS_ACTION_VIDEO || $do['first_result_from_videos']) {
			$book_name = (!$do[TABLE_NAME_COMPONENT__VIDEO.'authors'][$selected_video_author_id]['author_book_equal'] ? 
							' &gt; '.$do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_video_book_id]['book_title'] : '');
		// Books
		} else {
			if ($selected_book_id != -1) {
				$book_name = ' &gt; '.$do['books'][$selected_book_id]['book_title'];
			}
		}
		
		$header_links .= '<div id="header-info-container">'.
				
			(IS__SITE_SIMPLIFIED_LAYOUT ? 
				
				'<div id="header-tab-navigation-container">'.
					'<ul class="tab-navigation">'.
						'<li>'.
							(IS__SEARCH_IN_BOOKS ?
								'<a href="javascript:void(0)" id="tab-results-a" class="button left left-gap button-narrow-tab-navigation"'.
									' onclick="myLayout_OpenPanel_OthersClose({west: true});" >'.
											'<span class="label" style="color:#000111;">Books</span></a>'
								:
								'<a href="javascript:void(0)" id="tab-videos-a" class="button right right-gap button-narrow-tab-navigation"'.
									' onclick="myLayout_OpenPanel_OthersClose({west: true});" >'.
											'<span class="label" style="color:#000111;">Videos</span></a>'
							).
						'</li>'.
					'</ul>'.
				'</div>'
				
				:
				
				'<div id="header-info"'.$book_font_class.'>'.
				
					'<a href="javascript:void(0)" class="button button-small button-small-book left"'.
							' onclick="showResultListBooks();" title="Books">'.
								'<span class="icon icon_book_small"></span></a>'.

					'<a href="javascript:void(0)" class="button button-small button-small-video right right-gap"'.
							' onclick="showResultListVideos();" title="Videos">'.
								'<span class="icon icon_video_small"></span></a>'.
				
					'<div id="header-info-text">'.
				
					// Book title
					($selected_book_id != -1 ? 

						// Author name
						$do[$do['db_table_prefix'].'authors'][$do[$do['db_table_prefix'].'books'][$selected_book_id]['author_id']]['full_name'].

						// Book name
						$book_name.

						// Chapter title
						($selected_chapter_id != -1 ? 
							' &gt; '.$do[$do['db_table_prefix'].'books'][$selected_book_id]['chapters'][$selected_chapter_id]['chapter_title'] : '')
						:
						''
					).
					'</div>'.
				'</div>'.
				
				'<div id="header-tab-navigation-container">'.
					'<ul class="tab-navigation">'.
				
						'<li><a href="javascript:void(0)" class="button button-small button-small-book left"'.
								' onclick="showResultListBooks();" title="Books">'.
									'<span class="icon icon_book_small"></span></a></li>'.
								
						'<li><a href="javascript:void(0)" class="button button-small button-small-video middle"'.
								' onclick="showResultListVideos();" title="Videos">'.
									'<span class="icon icon_video_small"></span></a></li>'.
								
						'<li><a href="javascript:void(0)" id="tab-results-a" class="button middle"'.
								' onclick="myLayout_OpenPanel_OthersClose({west: true});" title="Results">'.
									'<span class="icon icon_eye"></span></a></li>'.
								
						'<li><a href="javascript:void(0)" id="tab-text-a" class="button middle"'.
								' onclick="myLayout_OpenPanel_OthersClose({center: true});" title="Text">'.
									'<span class="icon icon_text"></span></a></li>'.
								
						'<li><a href="javascript:void(0)" id="tab-references-a" class="button middle"'.
								' onclick="myLayout_OpenPanel_OthersClose({south: true});" title="References">'.
									'<span class="icon icon_reference"></span></a></li>'.
								
						'<li><a href="javascript:void(0)" id="tab-info-a" class="button right"'.
								' onclick="toggle(\'tab-info\');" title="Info">'.
									'<span class="icon icon_info" id="tab-info-span"></span></a></li>'.
							
							
							'<div id="tab-info" style="display:none;">'.
								
								// Book title
								($selected_book_id != -1 ? 
									
									// Author name
									$do[$do['db_table_prefix'].'authors'][$do[$do['db_table_prefix'].'books'][$selected_book_id]['author_id']]['full_name'].'<br />'.
									
									$do[$do['db_table_prefix'].'books'][$selected_book_id]['book_title'].
									
									// Chapter title
									($selected_chapter_id != -1 ? 
										'<br />'.$do[$do['db_table_prefix'].'books'][$selected_book_id]['chapters'][$selected_chapter_id]['chapter_title'] : '')
										:
										''
								).
								
							'</div>'.
						
					'</ul>'.
				'</div>'
			).
			'</div>'; // header-info-container
		
		// Videos label
		/*$header_links .= IS__SITE_SIMPLIFIED_LAYOUT ? 
							''
							:
								'<div id="header-videos-info">
								Videos
							</div>';*/
		
		// Closing header
		$header_links .= '</div></div>'; // header, header-container
		
		return $header_links;
	} // end: function Get_Header()
	
	
	//--------------------------------------------------------------------------------------------------------------------
	// Footer data, links
	//--------------------------------------------------------------------------------------------------------------------
	function Get_Footer($dataCenter, $do, $time_start, $neighbour_result_links = '') {
		
		//$ret_chapter_links = Get_PreviousNext_ChapterLinks($dataCenter, $do);
		
		//------------------------------------------------------------------------------------
		// Left side: buttons
		//------------------------------------------------------------------------------------
		$footer_left_side = '<div id="footer-result-links">'.
								'<table style="height:100%;border:none;"><tr><td style="vertical-align:middle">'.
									$neighbour_result_links.
								'</td></tr></table>'.
							'</div>';
		
		
		//------------------------------------------------------------------------------------
		// Right side: Reference info
		//------------------------------------------------------------------------------------
		$footer_right_side__content = array();
		
		if (DO_REFERENCE_INFO) {
			
			// Load all references by the current book
			$query = 'SELECT reference_info'.
						' FROM '.$do['db_table_prefix'].REFERENCE_INFO_TABLE.
						' WHERE book_id=?'.
						' ORDER BY reference_id';
			$query_bind_values = array($do['searchResult_book_id']);
			$res = $dataCenter->SQLite_DB->prepare($query);
			$res->execute($query_bind_values);
			while ($row = $res->fetch(PDO::FETCH_NUM)) {
				$footer_right_side__content[] = $row[0];//'<div class="reference">'.$row[0].'</div>';
			}
		}
		$footer_right_side__content = implode($footer_right_side__content);
		//$timeUsed = round(microtime(true) - $time_start, 3);
		
		$footer_right_side = '<div id="footer-right-side">'.
								'<div id="footer-right-side-container">'.
									'<div id="footer-right-side-content">'.
										//$timeUsed.' s'.$footer_right_side__content.
										$footer_right_side__content.
									'</div>'.
								'</div>'.
							'</div>';
		
		return '<div id="footer" class="'.$do['layout_container_class_name']['south'].'">'.
					$footer_left_side.
					$footer_right_side.
				'</div>';
	} // end: function Get_Footer()
	
	
	//--------------------------------------------------------------------------------------------------------------------
	// Chapter links in the text: previous, next
	//--------------------------------------------------------------------------------------------------------------------
	function Get_PreviousNext_ChapterLinks($dataCenter, $do) {
		
		if (isset($do['searchResult_chapter_id'])) {
			
			$urlData = 	(!empty($do['url_encoded_do_search_text']) ? '&amp;q='.$do['url_encoded_do_search_text'] : '').
						$do['urlDataTransfer'];
			$url_is_video_books_addon	= (IS_ACTION_VIDEO ? '&amp;video=1' : '');
			$selected_chapter_id 		= $do['searchResult_chapter_id'];
			
			// Book or Video result
			//$selected_book_id = (IS_ACTION_VIDEO || $do['first_result_from_videos'] ? 
			//						$do[TABLE_NAME_COMPONENT__VIDEO.'chapter_id__book_id'][$selected_chapter_id]
			//							:
			//						$do['chapter_id__book_id'][$selected_chapter_id]);
			$selected_book_id = (isset($do['searchResult_book_id']) ? $do['searchResult_book_id'] : -1);
			
			$prev_chapter_url = (isset($do[$do['db_table_prefix'].'books'][$selected_book_id]['chapters'][$selected_chapter_id-1]) ?
										'b='.$selected_book_id.'&amp;c='.($selected_chapter_id-1).
											$urlData.
											$url_is_video_books_addon.
											'&amp;#c'.($selected_chapter_id-1) : '');
			
			$next_chapter_url = (isset($do[$do['db_table_prefix'].'books'][$selected_book_id]['chapters'][$selected_chapter_id+1]) ?
										'b='.$selected_book_id.'&amp;c='.($selected_chapter_id+1).
											$urlData.
											$url_is_video_books_addon.
											'&amp;#c'.($selected_chapter_id+1) : '');
			
			// Previous
			$chapter_previous_link = 
					
					// Previous Chapter
					'<a class="button left left-gap'.
							(IS__SEARCH_IN_VIDEOS && 
							!empty($prev_chapter_url) &&
							!empty($do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_book_id]['chapters'][$selected_chapter_id-1]['video_id']) ? 
								' prettyPhoto_link' : '').
							'" title="Previous section"'.
						(!empty($prev_chapter_url) ? 
							' href="javascript:void(0);"'.
							' onclick="ajaxDataLoader({'.
									' url: \''.WEB_INDEX_FILE.'?'.$prev_chapter_url.'\''.
									', anchor: \'c'.($selected_chapter_id-1).'\''.
							'});"'.
							
							(IS__SEARCH_IN_VIDEOS && !empty($do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_book_id]['chapters'][$selected_chapter_id-1]['video_id']) ?
								' rel="prettyPhoto"'.
								' pp_href="http://www.youtube.com/watch?v='.$do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_book_id]['chapters'][$selected_chapter_id-1]['video_id'].'"'.
								' pp_video_begin="0"'.
								' pp_lang_code="'.$do['languages'][$do['selected_language_id']]['lang_code_display'].'"'.
								' pp_desc_1="'.htmlspecialchars( $do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_book_id]['chapters'][$selected_chapter_id-1]['chapter_title']
									, ENT_QUOTES).'"'.
								' pp_desc_2="'.htmlspecialchars( 
										$do[TABLE_NAME_COMPONENT__VIDEO.'authors'][ $do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_book_id]['author_id'] ]['full_name']
									, ENT_QUOTES).'"'
								:
								''
							)
							:
							''
						).
						'><span class="icon icon_arrow_left"></span>'.
					'</a>';
			
			// Next
			$chapter_next_link =
					
					// Next Chapter
					'<a class="button right right-gap'.
							(IS__SEARCH_IN_VIDEOS && 
							!empty($next_chapter_url) &&
							!empty($do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_book_id]['chapters'][$selected_chapter_id+1]['video_id']) ? 
								' prettyPhoto_link' : '').
							'" title="Next section"'.
						(!empty($next_chapter_url) ? 
							' href="javascript:void(0);"'.
							' onclick="ajaxDataLoader({'.
									' url: \''.WEB_INDEX_FILE.'?'.$next_chapter_url.'\''.
									', anchor: \'c'.($selected_chapter_id+1).'\''.
							'});"'.
							
							(IS__SEARCH_IN_VIDEOS && !empty($do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_book_id]['chapters'][$selected_chapter_id+1]['video_id']) ?
								' rel="prettyPhoto"'.
								' pp_href="http://www.youtube.com/watch?v='.$do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_book_id]['chapters'][$selected_chapter_id+1]['video_id'].'"'.
								' pp_video_begin="0"'.
								' pp_lang_code="'.$do['languages'][$do['selected_language_id']]['lang_code_display'].'"'.
								' pp_desc_1="'.htmlspecialchars( $do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_book_id]['chapters'][$selected_chapter_id+1]['chapter_title']
									, ENT_QUOTES).'"'.
								' pp_desc_2="'.htmlspecialchars( 
										$do[TABLE_NAME_COMPONENT__VIDEO.'authors'][ $do[TABLE_NAME_COMPONENT__VIDEO.'books'][$selected_book_id]['author_id'] ]['full_name']
									, ENT_QUOTES).'"'
								:
								''
							)
							:
							''
						).
						'><span class="icon icon_arrow_right"></span>'.
					'</a>';
		} else {
			$chapter_previous_link = $chapter_next_link = '';
		}
		
		return array('chapter_previous_link' => $chapter_previous_link, 'chapter_next_link' => $chapter_next_link);
	} // end: function Get_PreviousNext_ChapterLinks()
	
	
	//--------------------------------------------------------------------------------------------------------------------
	// Language selection button
	//--------------------------------------------------------------------------------------------------------------------
	function Get_Language_Selection_Button($do, $grouped) {
		
		$ret = '';
		
		$urlData = array();
		if (!empty($do['url_encoded_do_search_text'])) {
			$urlData[] = 'q='.$do['url_encoded_do_search_text'].'&amp;sq=Search';
		}
		
		if (!empty($do_urlData_copy)) {
			$do_urlData_copy = $do['urlData'];
			if (isset($do_urlData_copy['lang'])) {
				unset($do_urlData_copy['lang']);
			}
			$urlData[] = implode('&amp;', $do_urlData_copy);
		}
		$urlData = implode('&amp;', $urlData);
		if (!empty($urlData)) {
			$urlData .= '&amp;';
		}
		
		$selected_language_id = (isset($do['selected_language_id']) ? $do['selected_language_id'] : 1);
		
		//$ret = !$grouped ? '<div class="buttons" style="position:relative; text-align:center;">' : '';
		
		
		/*
			// Settings
			'<a href="javascript:void(0)" class="button left left-gap" id="settings-selector-a" onclick="toggle(\'settings-selector\');" title="Settings">'.
				'<span class="icon icon_contents" id="chapter-selector-span"></span>'.
			'</a>'.
		*/
		
		$a_href_begin = !$grouped ? 
			'<a href="javascript:void(0)" class="button" id="language-selector-a" style="float:none;" onclick="toggle(\'language-selector\');" title="Languages">'
			: '<a href="javascript:void(0)" class="button middle" id="language-selector-a" onclick="toggle(\'language-selector\');" title="Languages">';
		
		$language_list_class = $do['index_page'] ? 'index' : 'non-index';
		
		$ret .= $a_href_begin.
					'<span class="icon icon_language" id="language-selector-span"></span>'.
				'</a>'.

				'<ul id="language-selector" class="'.$language_list_class.'" style="display:none;">';

		// On index page
		if ($do['index_page']) {
			foreach ($do['languages'] as $lang_id => $lang_data) {
				if ($selected_language_id == $lang_id) { $classStr = ' class="item-selected"'; } else { $classStr = ''; }
				$ret .= '<li>'.
							'<a href="'.WEB_INDEX_FILE.'?'.$urlData.'l='.$lang_id.'"'.$classStr.'>'.
								$lang_data['lang_original'].'<span class="lang_code">'.$lang_data['lang_code_display'].'</span>'.
							'</a>'.
						'</li>';
			}
		// On other pages
		} else {
			foreach ($do['languages'] as $lang_id => $lang_data) {
				if ($selected_language_id == $lang_id) { $classStr = ' class="item-selected"'; } else { $classStr = ''; }
				$ret .= '<li>'.
							'<a href="javascript:void(0);"'.
								' onclick="ajaxDataLoader({'.
										' url: \''.WEB_INDEX_FILE.'?'.$urlData.'l='.$lang_id.'\', search: 1'.
								'});"'.
									$classStr.
									'>'.$lang_data['lang_original'].'<span class="lang_code">'.$lang_data['lang_code_display'].'</span>'.
							'</a>'.
						'</li>';
			}
		}
		$ret .= '</ul>';
		//$ret .= !$grouped ? '</div>' : '';
		
		return $ret;
	} // end: function Get_Language_Selection_Button()
	
	
	function getPageHeader_metaDescription($do, $action) {
		$ret = '';
		if (isset($do['searchResult_book_id']) and isset($do['searchResult_chapter_id'])) {
			switch ($action) {
				case 'read':
					$ret = 	'Text reading of '.
							$do[$do['db_table_prefix'].'authors'][$do[$do['db_table_prefix'].'books'][$do['searchResult_book_id']]['author_id']]['full_name'].', '.
							$do[$do['db_table_prefix'].'books'][$do['searchResult_book_id']]['book_title'].': '.
							$do[$do['db_table_prefix'].'books'][$do['searchResult_book_id']]['chapters'][$do['searchResult_chapter_id']]['chapter_title'];
					break;

				case 'search':
					$ret = 	'Search for '.$do['search_text'].
							' | Text reading of '.
							$do[$do['db_table_prefix'].'authors'][$do[$do['db_table_prefix'].'books'][$do['searchResult_book_id']]['author_id']]['full_name'].', '.
							$do[$do['db_table_prefix'].'books'][$do['searchResult_book_id']]['book_title'].': '.
							$do[$do['db_table_prefix'].'books'][$do['searchResult_book_id']]['chapters'][$do['searchResult_chapter_id']]['chapter_title'];
					break;
			}
		} else {
			$ret = getSiteSpecificContent('head_title');;
		}
		return $ret;
	}
	
	//------------------------------------------------------------------------------------------------------------------------
	// Site Operation Mode
	//------------------------------------------------------------------------------------------------------------------------
	function convert_SiteOperationMode_valueToArray($do, $siteOperationMode) {
		$m_str = str_pad(decbin($siteOperationMode), SITE_OPERATION_MODE_COMPONENT_NUM, '0', STR_PAD_LEFT);
		$ret = array();
		$i = -1;
		foreach ($do['siteOperationMode_Values'] as $om_type => $t) {
		    $ret[$om_type] = $m_str[++$i];
		}
		return $ret;
	}
	function convert_SiteOperationMode_arrayToValue($do, $siteOperationMode_Values) {
		$ret = 0;
		foreach ($do['siteOperationMode_Values'] as $om_type => $t) {
			$ret *= 2;
		    $ret += $siteOperationMode_Values[$om_type];
		}
		return $ret;
	}

	//------------------------------------------------------------------------------------------------------------------------
	// Book list to search in
	//------------------------------------------------------------------------------------------------------------------------
	// book_id_list => group(4) numbers => group(hex number) list
	// e.g. 1,2,3,4 => 0,1,2,3 => (1,2) => 6
	function convert_BookIdList_to_BookListNumber($book_id_list) {
		// 1,2,3,4,5,6,7,8,9	
		// 8,4,2,1,8,4,2,1,8	-1 & mod 4 & >>
		$max_book_group_id = 0;
		$searchBooksNum = 0;
		$book_id_group = array();
		foreach ($book_id_list as $book_id) {
			$book_id = intval($book_id);
			if ($book_id >= 1) {
				++$searchBooksNum;
				$group_id = floor(($book_id-1) / 4);
				if ($max_book_group_id < $group_id) { $max_book_group_id = $group_id; }
				if (!isset($book_id_group[$group_id])) { $book_id_group[$group_id] = 0; }
				$book_id_group[$group_id] += 8 >> (($book_id-1) % 4);
			}
		}
		$searchBooksCode = '';
		for ($i=0; $i <= $max_book_group_id; $i++) {
			$searchBooksCode .= (isset($book_id_group[$i]) ? dechex($book_id_group[$i]) : '0');
		}
		return array('searchBookListCode' => $searchBooksCode, 'searchBookListNum' => $searchBooksNum);
	}
	// A6 => [1,3 , 6,7]
	function convert_BookListNumber_to_BookIdList($searchBooksCode) {
		$l = strlen($searchBooksCode);
		$shift = 1;
		$book_id_list = array();
		for ($i=0; $i < $l; $i++) {
			$bin_str = str_pad(base_convert($searchBooksCode[$i], 16, 2), 4, '0', STR_PAD_LEFT);
			for ($j=0; $j<4; $j++) {
			    if ($bin_str[$j] == 1) {
					$book_id_list[$shift + $j] = $shift + $j;
				}
			}
			$shift += 4;
		}
		return $book_id_list;
	}
	
	function to_utf8( $string ) {
		// From http://w3.org/International/questions/qa-forms-utf-8.html
		// Use: $x = to_utf8( $_GET['myvar'] );
		if ( preg_match('%^(?:
				[\x09\x0A\x0D\x20-\x7E]            # ASCII
				| [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
				| \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
				| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
				| \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
				| \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
				| [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
				| \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
			)*$%xs', $string) ) {
			return $string;
		} else {
			return iconv( 'CP1252', 'UTF-8', $string);
		}
	}
	


	//------------------------------------------------------------------------------------------------------------------------
	// Encode / Decode text
	//------------------------------------------------------------------------------------------------------------------------
	// binary output
	function Encrypt($s) { 
		// Build a 256-bit $key which is a SHA256 hash of $salt and $password.
		//$key = hash('SHA256', $salt . $password, true);
		
		// Build $iv and $iv_base64.  We use a block size of 128 bits (AES compliant) and CBC mode.
		srand();
		$iv = mcrypt_create_iv(mcrypt_get_iv_size(ENCRYPT_DECRYPT_CIPHER, ENCRYPT_DECRYPT_MODE), MCRYPT_RAND);
		if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22) return false;
		
		// Encrypt $s and an MD5 of $s using $key.  MD5 is fine to use here because it's just to verify successful decryption.
		$crypt_s = base64_encode( mcrypt_encrypt(	ENCRYPT_DECRYPT_CIPHER, ENCRYPT_DECRYPT_KEY, 
													$s . md5($s), ENCRYPT_DECRYPT_MODE, $iv));
		
		return $iv_base64 . $crypt_s;
	} 

	function Decrypt($crypt_s) {
		// Build a 256-bit $key which is a SHA256 hash of $salt and $password.
		//$key = hash('SHA256', $salt . $password, true);
		
		// Retrieve $iv which is the first 22 characters plus ==, base64_decoded.
		$iv = base64_decode(substr($crypt_s, 0, 22) . '==');
		
		// Remove $iv from $crypt_s.
		$crypt_s = substr($crypt_s, 22);
		
		// Decrypt the data.  
		// rtrim won't corrupt the data because the last 32 characters are the md5 hash; 
		// thus any \0 character has to be padding.
		$s = rtrim(mcrypt_decrypt(	ENCRYPT_DECRYPT_CIPHER, ENCRYPT_DECRYPT_KEY, 
									base64_decode($crypt_s), ENCRYPT_DECRYPT_MODE, $iv), "\0\4");
		
		// Retrieve $hash which is the last 32 characters of $s.
		$hash = substr($s, -32);
		
		// Remove the last 32 characters from $s.
		$s = substr($s, 0, -32);
		
		// Integrity check.  If this fails, either the data is corrupted, or the password/salt was incorrect.
		if (md5($s) != $hash) return false;
		
		return $s;
	}	
	
	
	//------------------------------------------------------------------------------------------------------------------------
	// Compress / Uncompress array data
	//	$data[ key_1 ][ key_2 ] = value_1
	//------------------------------------------------------------------------------------------------------------------------
	function compress__word_book_ps_data($word_book_ps_data) {
		// in	: $data[ book_id ][ paragraph_id / sentence_id ] = relevance_paragraph / relevance_sentence;
		// out	: book_id:paragraph_id-relevance_paragraph;paragraph_id-relevance_paragraph|book_id:....
		if (empty($word_book_ps_data)) {
			$ret = '';
		} else {
			$container_all_books = array();
			foreach ($word_book_ps_data as $book_id => $book_data) {
				$container_book = array();
				foreach ($book_data as $ps_id => $relevance) {
					$container_book[] = $ps_id.SEPARATOR__WORD_BOOK_PS_DATA__PS_ID__RELEVANCE.$relevance;
				}
				$container_all_books[] = 	$book_id.
											SEPARATOR__WORD_BOOK_PS_DATA__BOOK_ID__BOOK_DATA.
											implode(SEPARATOR__WORD_BOOK_PS_DATA__PS_DATA__PS_DATA, $container_book);
			}
			$ret = implode(SEPARATOR__WORD_BOOK_PS_DATA__BOOK__BOOK, $container_all_books);
		}
		return $ret;
		//return (empty($data) ? null : gzcompress( serialize($data), GZCOMPRESS_LEVEL ));
	}
	function uncompress__word_book_ps_data($word_book_ps_data) {
		
		$ret = array();
		if (!empty($word_book_ps_data)) {
			$container_all_books = explode(SEPARATOR__WORD_BOOK_PS_DATA__BOOK__BOOK, $word_book_ps_data);
			foreach ($container_all_books as $book) {
				$book_parts = explode(SEPARATOR__WORD_BOOK_PS_DATA__BOOK_ID__BOOK_DATA, $book);
				$book_data = explode(SEPARATOR__WORD_BOOK_PS_DATA__PS_DATA__PS_DATA, $book_parts[1]);
				foreach ($book_data as $ps_data) {
					$ps_data_parts = explode(SEPARATOR__WORD_BOOK_PS_DATA__PS_ID__RELEVANCE, $ps_data);
					$ret[$book_parts[0]][$ps_data_parts[0]] = $ps_data_parts[1];
				}
			}
		}
		return $ret;
		//return (empty($data) ? array() : unserialize( gzuncompress($data) ));
	}
	
	//------------------------------------------------------------------------------------------------------------------------
	// Compress / Uncompress array data
	//	$data[ key_1 ][ ] = value_1
	//------------------------------------------------------------------------------------------------------------------------
	function compress__word_phrases_data($word_phrases_data) {
		// in	: $data[ word_phrase_group_code ][ ] = phrase;
		// out	: word_phrase_group_code:phrase;phrase;phrase|word_phrase_group_code-....
		if (empty($word_phrases_data)) {
			$ret = '';
		} else {
			$container_all_phrase_group = array();
			foreach ($word_phrases_data as $word_phrase_group_code => $phrase_group) {
				$container_all_phrase_group[] = $word_phrase_group_code.
												SEPARATOR__WORD_BOOK_PS_DATA__BOOK_ID__BOOK_DATA.
												implode(SEPARATOR__WORD_BOOK_PS_DATA__PS_DATA__PS_DATA, $phrase_group);
			}
			$ret = implode(SEPARATOR__WORD_BOOK_PS_DATA__BOOK__BOOK, $container_all_phrase_group);
		}
		return $ret;
		//return (empty($data) ? null : gzcompress( serialize($data), GZCOMPRESS_LEVEL ));
	}
	function uncompress__word_phrases_data($word_phrases_data) {
		
		$ret = array();
		if (!empty($word_phrases_data)) {
			$container_all_phrase_group = explode(SEPARATOR__WORD_BOOK_PS_DATA__BOOK__BOOK, $word_phrases_data);
			foreach ($container_all_phrase_group as $word_phrase_group) {
				$word_phrase_group_parts = explode(SEPARATOR__WORD_BOOK_PS_DATA__BOOK_ID__BOOK_DATA, $word_phrase_group);
				$phrase_group = explode(SEPARATOR__WORD_BOOK_PS_DATA__PS_DATA__PS_DATA, $word_phrase_group_parts[1]);
				$ret[$word_phrase_group_parts[0]] = $phrase_group;
			}
		}
		return $ret;
		//return (empty($data) ? array() : unserialize( gzuncompress($data) ));
	}
	/*
	function compress($data) {
		//return gzcompress( serialize($data), GZCOMPRESS_LEVEL );
		//return gzdeflate( serialize($data), GZCOMPRESS_LEVEL );
		return serialize($data);
	}
	function uncompress($data) {
		//writeOut(bin2hex($data));
		//return unserialize( gzinflate( substr($data, 11) ) );
		//return unserialize( gzuncompress( $data ) );
		//return unserialize( gzinflate($data) );
		return unserialize($data);
	}
	*/
	
	//------------------------------------------------------------------------------------------------------------------------
	// Download result content into file
	//------------------------------------------------------------------------------------------------------------------------
	function Download_Result_Content_Into_File($do, $str) {
		//$filename = 'results-'.$do['url_encoded_do_search_text'].'.txt';
		$filename = $do['url_encoded_do_search_text'].'.txt';
		
		header("Pragma: public");
		header("Pragma: no-cache");
		header("Expires: 0");
		header("Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0");
		header('Cache-Control: private', false); // required for certain browsers
		//header('Content-Type: application/octet-stream');
		//header("Content-Type: application/force-download; filename=".$filename);
		header("Content-Disposition: attachment; filename=".$filename);
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . strlen($str));
		header("Content-Description: File Transfer");
		
		flush();
		
		echo $str;
		ob_flush();
		flush();
	}
	
	//------------------------------------------------------------------------------------------------------------------------
	// Tools
	//------------------------------------------------------------------------------------------------------------------------
	// Flush
	function flushText() {
		//ob_implicit_flush(true);
	    echo(str_repeat(' ',256));
	    // check that buffer is actually set before flushing
	    if (ob_get_length()) {
	        @ob_end_flush();
	        @ob_flush();
	        @flush();
	    }
	    @ob_start();
	}
	function writeOut($s) {
		echo '<pre>';
		print_r($s);
		echo '</pre>';
	}
	function memoryUsage()	{
		$size	= memory_get_usage(true);
		$unit	= array('B','KB','MB','GB','TB','PB');
		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	}
	function sizeofvar($var) {
		$start_memory = memory_get_usage();
		$tmp = unserialize(serialize($var));
		return memory_get_usage() - $start_memory;
	}
	
	//------------------------------------------------------------------------------------------------------------------------
	// Base64 Encode / Decode
	//------------------------------------------------------------------------------------------------------------------------
	/*function Safe_Base64_Encode($s) {
		return str_replace(array('+', '/', '='), array('-', '_', ','), base64_encode($s));
	}
	function Safe_Base64_Decode($crypt_s) {
	    return base64_decode(str_replace(array('-', '_', ','), array('+', '/', '='), $crypt_s));
	}*/
?>