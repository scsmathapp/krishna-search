<?php
	//------------------------------------------------------------------------------------------------------------------------
	// Chosen search result Book Text
	//------------------------------------------------------------------------------------------------------------------------
	function resultBookText($dataCenter, &$do) {
		
		$result_text = $result_text_before = $result_text_after = '';
		$is_video_book_full_chapter_load = false;
		
		// Final: Video book text is loaded or not
		if ($do['db_table_prefix'] == TABLE_NAME_COMPONENT__VIDEO) {
			
			$do['is_books']		= FALSE;
			$query_video_addon	= ', p.video_time';
			
			// Full text loaded 
			//	- no check for length of paragraph numbers inside
			//	- if needs, see: <tools-evaluate.php> => // Paragraph view or Cross reference
			$is_video_book_full_chapter_load = true;
			/*
			if (isset($do['searchResult_video_books_chapter_paragraph_num']) and
				($do['searchResult_video_books_chapter_paragraph_num'] <= VIDEO_BOOK_CHAPTER_MAX_PARAGRAPH_NUM__FULL_CHAPTER_LOAD)) {
				$is_video_book_full_chapter_load = true;
			}
			*/
		} else {
			$do['is_books']		= TRUE;
			$query_video_addon	= '';
		}
		
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
		
		
		//------------------------------------
		// Load book text
		//------------------------------------
		$is_book_text_load_success = false;
		
		
		
		
		//------------------------------------------------------------------------------------
		// Load book text inline inserted (begin, end)
		//------------------------------------------------------------------------------------
		if (DO_LOAD_INLINE_CHAPTER) {
			
			if (isset($do['searchResult_book_id']) && isset($do['searchResult_chapter_id'])) {
				
				// Before
				if (isset($do['book_text_load__paragraph_before'])) {
					$query_where_part		= 'p.book_paragraph_id<?';
					$query_bind_value_pid	= $do['book_text_load__paragraph_before'];
					$needLoad				= TRUE;
				// After
				} else if (isset($do['book_text_load__paragraph_after'])) {
					$query_where_part		= 'p.book_paragraph_id>?';
					$query_bind_value_pid	= $do['book_text_load__paragraph_after'];
					$needLoad				= TRUE;
				} else {
					$needLoad				= FALSE;
				}

				if ($needLoad) {
					$query = 'SELECT p.paragraph_text, p.book_paragraph_id, p.paragraph_type_id'.$query_video_addon.
								' FROM '.		$do['db_table_prefix'].CHAPTER_TABLE.	' AS c'.
								' INNER JOIN '.	$do['db_table_prefix'].PARAGRAPH_TABLE.	' AS p ON c.id = p.chapter_id'.
								' WHERE c.book_id=? AND c.book_chapter_id=? AND '. $query_where_part.
								' ORDER BY p.book_paragraph_id';
					$query_bind_values = array(	$do['searchResult_book_id'], $do['searchResult_chapter_id'], $query_bind_value_pid );
					$res = $dataCenter->SQLite_DB->prepare($query);
					$res->execute($query_bind_values);

					getChapter_FirstLast_paragraph_id($dataCenter, $do);

					buildParagraphText($do, $res, $result_text);

					if (!empty($result_text)) {
						$is_book_text_load_success = true;
					}
					$do['pageHeaderMetaDescription'] = getPageHeader_metaDescription($do, 'read');
				}
			}			
			
		//------------------------------------------------------------------------------------
		// [Book load text] amount of text (chapter, full book) or Chapter selection
		//------------------------------------------------------------------------------------
		} else if (IS__SITE_RESULT_TEXT_LENGTH_TYPE__LONG_TEXT or DO_SELECT_CHAPTER or $is_video_book_full_chapter_load) {
			//writeOut('long text---------------');
			
			// Book text load: Full book
			if (IS__SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK) {
				
				if (isset($do['searchResult_book_id'])) {
					
					$query = 'SELECT p.paragraph_text, p.book_paragraph_id, p.paragraph_type_id'.$query_video_addon.', c.book_chapter_id'.
								' FROM '.		$do['db_table_prefix'].CHAPTER_TABLE.	' AS c'.
								' INNER JOIN '.	$do['db_table_prefix'].PARAGRAPH_TABLE.	' AS p ON c.id = p.chapter_id'.
								' WHERE c.book_id=?'.
								' ORDER BY c.book_chapter_id, p.book_paragraph_id';
					$query_bind_values = array($do['searchResult_book_id']);
					$res = $dataCenter->SQLite_DB->prepare($query);
					$res->execute($query_bind_values);
					
					getBookChapters_FirstLast_paragraph_id($dataCenter, $do);
					
					buildParagraphText($do, $res, $result_text);
				}
				
			//.. Book text load: By chapter
			} else if ((isset($do['searchResult_book_id']) && isset($do['searchResult_chapter_id'])) or $is_video_book_full_chapter_load) {
				
				$query = 'SELECT p.paragraph_text, p.book_paragraph_id, p.paragraph_type_id'.$query_video_addon.
							' FROM '.		$do['db_table_prefix'].CHAPTER_TABLE.	' AS c'.
							' INNER JOIN '.	$do['db_table_prefix'].PARAGRAPH_TABLE.	' AS p ON c.id = p.chapter_id'.
							' WHERE c.book_id=? AND c.book_chapter_id=?'.
							' ORDER BY p.book_paragraph_id';
				$query_bind_values = array($do['searchResult_book_id'], $do['searchResult_chapter_id']);
				$res = $dataCenter->SQLite_DB->prepare($query);
				$res->execute($query_bind_values);
				
				getChapter_FirstLast_paragraph_id($dataCenter, $do);
				
				buildParagraphText($do, $res, $result_text);
			}
			
					
			if (!empty($result_text)) {
				$is_book_text_load_success = true;
			}
			$do['pageHeaderMetaDescription'] = getPageHeader_metaDescription($do, 'read');
			
		//------------------------------------------------------------------------------------
		// Target +-10 paragraphs (par.num. = SITE_RESULT_TEXT_SHORT_NEIGHBOURS_PARAGRAPH_NUM)
		//------------------------------------------------------------------------------------
		} else if (!IS__SITE_RESULT_TEXT_LENGTH_TYPE__LONG_TEXT and 
					(DO_SEARCH_QUERY or DO_SELECT_RESULT or DO_SELECT_PARAGRAPH_SENTENCE or DO_CROSS_REFERENCE)) {
			//writeOut('short text---------------: b:'.$do['searchResult_book_id'].' c:'.$do['searchResult_chapter_id'].' p:'.$do['searchResult_paragraph_id']);
			$neighbour_paragraph_num = !IS__SITE_SIMPLIFIED_LAYOUT ?
										SITE_RESULT_TEXT_SHORT_NEIGHBOURS_PARAGRAPH_NUM : 
										SITE_RESULT_TEXT_SHORT_NEIGHBOURS_PARAGRAPH_NUM__SITE_SIMPLIFIED_LAYOUT;
			
			// Get target paragraph with surrounding ones
			if (isset($do['searchResult_book_id']) && isset($do['searchResult_chapter_id']) && isset($do['searchResult_paragraph_id'])) {
				
				// First, last paragraph id
				$first_paragraph_id = $do['searchResult_paragraph_id'] - $neighbour_paragraph_num;
				$last_paragraph_id	= $do['searchResult_paragraph_id'] + $neighbour_paragraph_num;
				
				if ($first_paragraph_id < 1) {
					$first_paragraph_id = 1;
				}
				
				getChapter_FirstLast_paragraph_id($dataCenter, $do);
				
				if ($do['chapter_last_paragraph_id'] < $last_paragraph_id) {
					$last_paragraph_id = $do['chapter_last_paragraph_id'];
				}
				//writeOut('target: '.$do['searchResult_paragraph_id'].' +-:'.$neighbour_paragraph_num.' f:'.$first_paragraph_id.' l:'.$last_paragraph_id.'  '.$do['chapter_last_paragraph_id']);
				
				$query = 'SELECT p.paragraph_text, p.book_paragraph_id, p.paragraph_type_id'.$query_video_addon.
							' FROM '.		$do['db_table_prefix'].CHAPTER_TABLE.	' AS c'.
							' INNER JOIN '.	$do['db_table_prefix'].PARAGRAPH_TABLE.	' AS p ON c.id = p.chapter_id'.
							' WHERE c.book_id=? AND c.book_chapter_id=? AND p.book_paragraph_id>=? AND p.book_paragraph_id<=?'.
							' ORDER BY p.book_paragraph_id';
				$query_bind_values = array(	$do['searchResult_book_id'], $do['searchResult_chapter_id'],
											$first_paragraph_id, $last_paragraph_id);
				$res = $dataCenter->SQLite_DB->prepare($query);
				$res->execute($query_bind_values);
				
				
				buildParagraphText($do, $res, $result_text);
				
				if (!empty($result_text)) {
					$is_book_text_load_success = true;
				}
				$do['pageHeaderMetaDescription'] = getPageHeader_metaDescription($do, 'search');
			}
		}
		
		
		//$time_end = microtime(true);
		//$time_duration = 'Load book: '.round($time_end - $time_start, 4);
		//writeOut($time_duration);
		
		//------------------------------------------------------------------------------------
		// Book text load
		//------------------------------------------------------------------------------------
		if ($is_book_text_load_success) {
			
			
			//----------------------------------------------------------------------
			// Conversion to plain text if necessary
			//----------------------------------------------------------------------
			if (!IS__TEXT_TYPE__DIACRITICS) {
				//
				$diacritics = new Diacritics();
				$result_text = $diacritics->Remove_Diacritics_by_Rules($result_text);
				//$timeUsed = round(microtime(true) - $timeStart_this, 3);
				//writeOut('remove diacritics: '.$timeUsed);
			}
			
			
			//----------------------------------------------------------------------
			// Highlight keywords
			//----------------------------------------------------------------------
			if (isset($do['highlight_words_search'.$do['db_table_prefix']])) {
				
				$highlight_words_searchpattern_num = count($do['highlight_words_search'.$do['db_table_prefix']]);
				
				for ($i = 0; $i < $highlight_words_searchpattern_num; $i++) {
					
					$found = false;
					reset($do['html_tag_reserved_words']);
					$word = TRUE;

					while (!$found and ($word !== NULL)) {
						$word = key($do['html_tag_reserved_words']);
						$value = current($do['html_tag_reserved_words']);
						next($do['html_tag_reserved_words']);
						
						if ((strpos($do['highlight_words_search'.$do['db_table_prefix']][$i], '('.$word.'|') !== false) ||
							(strpos($do['highlight_words_search'.$do['db_table_prefix']][$i], '|'.$word.'|') !== false) ||
							(strpos($do['highlight_words_search'.$do['db_table_prefix']][$i], '|'.$word.')') !== false)) {
							$found = true;
						}
					}
					
					// html tag words
					if ($found) {
						$ret = mb_ereg_replace(	
									'(^|[^'.REGEX__WORD_PART.'])(?<!<span |<a |<|<div |'.
														'<div id="p[0-9]{1}" |'.
														'<div id="p[0-9]{2}" |'.
														'<div id="p[0-9]{3}" |'.
														'<div id="p[0-9]{4}" |'.
														'<div id="p[0-9]{5}" |'.
														'<div id="p[0-9]{6}" |'.
														'<div id="p[0-9]{7}" )('.$word.')(?!>)([^'.REGEX__WORD_PART.']|$)', 
									$do['highlight_words_replace'.$do['db_table_prefix']][$i], 
									$result_text,
									'i'); // case ignored
						
					// other words
					} else {
						$ret = mb_ereg_replace(
									$do['highlight_words_search'.$do['db_table_prefix']][$i], 
									$do['highlight_words_replace'.$do['db_table_prefix']][$i], 
									$result_text,
									'i'); // case ignored
					}
					if ($ret != NULL) { $result_text = $ret; }
				}
			}
			
			
			
			//----------------------------------------------------------------------
			// Highlight found paragraph / sentence
			//----------------------------------------------------------------------
			if (isset($do['searchResult_paragraph_id']) || isset($do['searchResult_sentence_id'])) {
				
				// paragraph found
				if (isset($do['searchResult_paragraph_id'])) {
					/*
					$foundPattern = array('<div id="p'.$do['searchResult_paragraph_id'].'" class="');
					$foundReplace = array('<div id="p'.$do['searchResult_paragraph_id'].'" class="found-paragraph ');
					$result_text = str_replace($foundPattern, $foundReplace, $result_text);
					*/
				// sentence found
				} else if (isset($do['searchResult_sentence_id'])) {
					$foundPattern = '<span id="s'.$do['searchResult_sentence_id'].'">';
					$foundReplace = '<span id="s'.$do['searchResult_sentence_id'].'" class="found-sentence">';
					$result_text = str_replace($foundPattern, $foundReplace, $result_text);
				}
			} // end: if (isset($do['searchResult_paragraph_id']) or isset($do['searchResult_sentence_id']))
			
			
			//----------------------------------------------------------------------
			// Update cross-reference links into
			//  clickable links
			// e.g.
			// 	<a id_link="40">  (<a id_link="original_source__eternal_play">)
			// 	<a id_link="1#40">  (book_id#paragraph_id)
			//   =>
			//	<a href="..." onclick="">
			//----------------------------------------------------------------------
			//$ret = mb_ereg_replace(	'<a '.CROSS_REFERENCE__TAG_ID.'="(['.CROSS_REFERENCE__ALLOWED_CHARS.']+)">', 
			$ret = mb_ereg_replace(	'<a '.CROSS_REFERENCE__TAG_ID.'="([^'.CROSS_REFERENCE__TAG_VALUE_SEPARATOR.']+)'.
																		CROSS_REFERENCE__TAG_VALUE_SEPARATOR.
																	'([^"]+)">', 
									'<a href="javascript:void(0)"'.
										' onclick="ajaxDataLoader({ url: \''.WEB_INDEX_FILE.'?q='.$do['url_encoded_do_search_text'].
																		$do['urlDataTransfer'].
																		$do['url_is_video_books_addon'].
																		'&amp;b=\\1&amp;p=\\2'.'\''.
																', anchor: \'p\\2\''.
																//(IS__SITE_ONE_COLUMN_FORCE ? ', one_col: \'1\'' : '').
															'});">', 
									$result_text
									);
			if ($ret != NULL) { $result_text = $ret; }
			
			
			//----------------------------------------------------------------------
			// Update reference-info links into
			//  clickable links
			// e.g.
			// 	<a id_reference="r1"> (<a id_reference="srimad_bhagavatam">)
			//   =>
			//	<a href="..." onclick="">
			//----------------------------------------------------------------------
			$ret = mb_ereg_replace(	'<a '.REFERENCE_INFO__TAG_ID.'="(['.REFERENCE_INFO__ALLOWED_CHARS.']+)">', 
									'<a href="javascript:void(0)"'.
										' onclick="ajaxDataLoader({ url: \''.WEB_INDEX_FILE.'?q='.$do['url_encoded_do_search_text'].
																		$do['urlDataTransfer'].
																		$do['url_is_video_books_addon'].
																		'&amp;reference_info_id=\\1'.'\''.
																', reference_info_id: \'\\1\''.
															'});">', 
									$result_text
									);
			if ($ret != NULL) { $result_text = $ret; }
			
			
			
			//----------------------------------------------------------------------
			// Video books text replacement only
			//----------------------------------------------------------------------
			if (IS_ACTION_VIDEO) {
				
				//----------------------------------------------------------------------
				// Update video links into
				//  clickable links to load the video
				// e.g.
				//	<a id_video_link="zKa-ttrvbjc×2s">Where does that come from?</a>
				//   =>
				//	<a href="..." ...>
				//----------------------------------------------------------------------
				// Manual search & replace
				//
				$pos_before 				= 0;
				$pos_crvr_begin 			= 0;
				$result_text_new 			= '';
				$crvr_id_begin 				= VIDEO_REFERENCE__TAG_ID.'="';
				$crvr_id_begin__length 		= strlen($crvr_id_begin);
				$crvr_id_end_tag_end 		= '</a>';
				$crvr_id_end_tag_end_length	= strlen($crvr_id_end_tag_end);
				$pp_playlist_pos			= 0;
				
				while (($pos_crvr_begin = strpos($result_text, $crvr_id_begin, $pos_crvr_begin)) !== false) {
					
					$pos_crvr_ref_tag_begin 	= $pos_crvr_begin + $crvr_id_begin__length;
					$pos_crvr_ref_tag_end 		= strpos($result_text, '"', $pos_crvr_ref_tag_begin);
					
					// L6NrMmQcenw×0s
					$reference_tag 				= substr($result_text, $pos_crvr_ref_tag_begin, $pos_crvr_ref_tag_end - $pos_crvr_ref_tag_begin);
					$reference_tag_dataA		= explode(VIDEO_BOOK_TEXT__VIDEOID_TIME_SEPARATOR, $reference_tag);
					
					$pos_crvr_a_tag_end 		= strpos($result_text, '>', $pos_crvr_ref_tag_end);
					$pos_crvr_end_tag_end		= strpos($result_text, $crvr_id_end_tag_end, $pos_crvr_a_tag_end);
					
					$reference_inside_text		= substr($result_text, $pos_crvr_a_tag_end + 1, $pos_crvr_end_tag_end - $pos_crvr_a_tag_end - 1);
					$reference_inside_only_text	= strip_tags($reference_inside_text);
					
					$result_text_new .= substr($result_text, $pos_before, $pos_crvr_begin - $pos_before).
											' href="javascript:void(0)"'.
											' class="prettyPhoto_link"'.
											' rel="prettyPhoto[sl]"'.
											' pp_href="http://www.youtube.com/watch?v='.$reference_tag_dataA[0].'"'.
											' pp_video_begin="'.$reference_tag_dataA[1].'"'.
											' pp_lang_code="'.$do['languages'][$do['selected_language_id']]['lang_code_display'].'"'.
											' pp_desc_1="'.htmlspecialchars( 
													$do[TABLE_NAME_COMPONENT__VIDEO.'books'][$do['searchResult_book_id']]['chapters'][$do['searchResult_chapter_id']]['chapter_title']
												, ENT_QUOTES).'"'.
											' pp_desc_2="'.htmlspecialchars( 
													$do[TABLE_NAME_COMPONENT__VIDEO.'authors'][ $do[TABLE_NAME_COMPONENT__VIDEO.'books'][$do['searchResult_book_id']]['author_id'] ]['full_name']
												, ENT_QUOTES).'"'.
											' pp_playlist_text="'.htmlspecialchars( $reference_inside_only_text, ENT_QUOTES ).'"'.
											' pp_playlist_pos="'.(++$pp_playlist_pos).'"'.
											'>'.
											$reference_inside_text.
										'</a>';
					
					$pos_before 	= $pos_crvr_end_tag_end + $crvr_id_end_tag_end_length;
					$pos_crvr_begin = $pos_crvr_end_tag_end + $crvr_id_end_tag_end_length;
				} // while
				
				// There was some references => use the rebuilt line
				if (!empty($result_text_new)) {
					$result_text_new .= substr($result_text, $pos_before);
					$result_text = $result_text_new;
				}
				
				
				//----------------------------------------------------------------------
				// Update [cross-reference links and video links] into
				//  clickable links to load the video
				// TOC (in contents of the book for chapters)
				// e.g.
				//	<a id_link_video_link="40×zKa-ttrvbjc×2s"><span class="Section-Link">Where does that come from?</span></a>
				//   =>
				//	<a href="..." ...>
				//----------------------------------------------------------------------
				// Manual search & replace
				//
				$pos_before 				= 0;
				$pos_crvr_begin 			= 0;
				$result_text_new 			= '';
				$crvr_id_begin 				= CROSS_REFERENCE_VIDEO_REFERENCE__TAG_ID.'="';
				$crvr_id_begin__length 		= strlen($crvr_id_begin);
				$crvr_id_end_tag_end 		= '</a>';
				$crvr_id_end_tag_end_length	= strlen($crvr_id_end_tag_end);
				$pp_playlist_pos			= 0;
				
				while (($pos_crvr_begin = strpos($result_text, $crvr_id_begin, $pos_crvr_begin)) !== false) {
					
					$pos_crvr_ref_tag_begin 	= $pos_crvr_begin + $crvr_id_begin__length;
					$pos_crvr_ref_tag_end 		= strpos($result_text, '"', $pos_crvr_ref_tag_begin);
					
					// 690×L6NrMmQcenw×0s	(paragraph id × video id × time)
					$reference_tag 				= substr($result_text, $pos_crvr_ref_tag_begin, $pos_crvr_ref_tag_end - $pos_crvr_ref_tag_begin);
					$reference_tag_dataA		= explode(VIDEO_BOOK_TEXT__VIDEOID_TIME_SEPARATOR, $reference_tag);
					
					$pos_crvr_a_tag_end 		= strpos($result_text, '>', $pos_crvr_ref_tag_end);
					$pos_crvr_end_tag_end		= strpos($result_text, $crvr_id_end_tag_end, $pos_crvr_a_tag_end);
					
					$reference_inside_text		= substr($result_text, $pos_crvr_a_tag_end + 1, $pos_crvr_end_tag_end - $pos_crvr_a_tag_end - 1);
					$reference_inside_only_text	= strip_tags($reference_inside_text);
					
					$result_text_new .= substr($result_text, $pos_before, $pos_crvr_begin - $pos_before).
										'href="javascript:void(0)"'.
											' onclick="ajaxDataLoader({ url: \''.WEB_INDEX_FILE.'?q='.$do['url_encoded_do_search_text'].
																			$do['urlDataTransfer'].
																			$do['url_is_video_books_addon'].
																			'&amp;b='.$do['searchResult_book_id'].
																			'&amp;p='.$reference_tag_dataA[0].'\''.
																	', anchor: \'p'.$reference_tag_dataA[0].'\''.
																	//(IS__SITE_ONE_COLUMN_FORCE ? ', one_col: \'1\'' : '').
																'});"'.
											' class="prettyPhoto_link"'.
											' rel="prettyPhoto[tl]"'.
											' pp_href="http://www.youtube.com/watch?v='.$reference_tag_dataA[1].'"'.
											' pp_video_begin="'.$reference_tag_dataA[2].'"'.
											' pp_lang_code="'.$do['languages'][$do['selected_language_id']]['lang_code_display'].'"'.
											' pp_desc_1="'.htmlspecialchars( 
													$reference_inside_only_text
												, ENT_QUOTES).'"'.
											' pp_desc_2="'.htmlspecialchars( 
													$do[TABLE_NAME_COMPONENT__VIDEO.'authors'][ $do[TABLE_NAME_COMPONENT__VIDEO.'books'][$do['searchResult_book_id']]['author_id'] ]['full_name']
												, ENT_QUOTES).'"'.
											' pp_playlist_text="'.htmlspecialchars( $reference_inside_only_text, ENT_QUOTES ).'"'.
											' pp_playlist_pos="'.(++$pp_playlist_pos).'"'.
											'>'.
											$reference_inside_text.
										'</a>';
					
					$pos_before 	= $pos_crvr_end_tag_end + $crvr_id_end_tag_end_length;
					$pos_crvr_begin = $pos_crvr_end_tag_end + $crvr_id_end_tag_end_length;
				} // while
				
				// There was some references => use the rebuilt line
				if (!empty($result_text_new)) {
					$result_text_new .= substr($result_text, $pos_before);
					$result_text = $result_text_new;
				}
			} // Video books
			
			
			
			//----------------------------------------------------------------------
			// Limited book text display
			//----------------------------------------------------------------------
			if (isset($first_paragraph_id) and isset($last_paragraph_id) and 
				!DO_LOAD_INLINE_CHAPTER and !IS__SITE_RESULT_TEXT_LENGTH_TYPE__LONG_TEXT and !DO_SELECT_CHAPTER) {
				
				// Link before chapter paragraph
				if ($do['chapter_first_paragraph_id'] < $first_paragraph_id) {
					$result_text_before = 
							'<div id="load-before-book-text-button">'.
								'<div style="margin:0px auto;width:120px;">'.
									'<a href="javascript:void(0)"'.
										' onclick="ajaxDataLoader({ url: \''.WEB_INDEX_FILE.'?q='.$do['url_encoded_do_search_text'].
														$do['urlDataTransfer'].
														$do['url_is_video_books_addon'].
														'&amp;b='.$do['searchResult_book_id'].
														'&amp;c='.$do['searchResult_chapter_id'].
														'&amp;p_before='.$first_paragraph_id.'\''.
												//', chapter_id: '.$do['searchResult_chapter_id'].'\''.
												//(IS__SITE_ONE_COLUMN_FORCE ? ', one_col: \'1\'' : '').
												', book_text_load_paragraph_before: \'p'.$first_paragraph_id.'\''.
												(isset($do['searchResult_psCurrentNumber']) ? 
														', result_list_nth_item: '.$do['searchResult_psCurrentNumber'] : '').
											'});"'.
										' class="button">'.
											'<span class="icon icon_arrow_up"></span>'.
									'</a>'.
								'</div>'.
							'</div>';
				}
				
				// Link after chapter paragraph
				if ($last_paragraph_id < $do['chapter_last_paragraph_id']) {
					$result_text_after = 
							'<div id="load-after-book-text-button">'.
								'<div style="margin:0px auto;width:120px;">'.
									'<a href="javascript:void(0)"'.
										' onclick="ajaxDataLoader({ url: \''.WEB_INDEX_FILE.'?q='.$do['url_encoded_do_search_text'].
															$do['urlDataTransfer'].
															$do['url_is_video_books_addon'].
															'&amp;b='.$do['searchResult_book_id'].
															'&amp;c='.$do['searchResult_chapter_id'].
															'&amp;p_after='.$last_paragraph_id.'\''.
												//', chapter_id: '.$do['searchResult_chapter_id'].'\''.
												//(IS__SITE_ONE_COLUMN_FORCE ? ', one_col: \'1\'' : '').
												', book_text_load_paragraph_after: \'p'.$last_paragraph_id.'\''.
												(isset($do['searchResult_psCurrentNumber']) ? 
														', result_list_nth_item: '.$do['searchResult_psCurrentNumber'] : '').
											'});"'.
										' class="button">'.
											'<span class="icon icon_arrow_down"></span></a>'.
							'</div></div>';
				}
			}
			
			
			//----------------------------------------------------------------------
			// Previous/Next Chapter buttons
			//----------------------------------------------------------------------
			if (!DO_LOAD_INLINE_CHAPTER && (IS__SITE_SIMPLIFIED_LAYOUT && IS__SITE_BOOK_READING_LAYOUT)) {
				$ret_chapter_links = Get_PreviousNext_ChapterLinks($dataCenter, $do);
				
				$book_text__chapter_prev_next_links = '<div class="chapter-buttons">'.
															$ret_chapter_links['chapter_previous_link'].
															$ret_chapter_links['chapter_next_link'].
													'</div>';
			} else {
				$book_text__chapter_prev_next_links = '';
			}
			
			
		//..no book text load
		} else {
			$result_text = NOT_FOUND_PS;
			$book_text__chapter_prev_next_links = '';
		}
		
		return array('result_text' 	=> 	$book_text__chapter_prev_next_links . 
										$result_text_before . 
										$result_text . 
										$result_text_after .
										$book_text__chapter_prev_next_links);
	} // function resultParagraph(&$dataCenter, $do)
	
	
	
	
	
	
	
	//------------------------------------------------------------------------------------------------------------------------
	// Paragraph text builders
	//------------------------------------------------------------------------------------------------------------------------
	// Chapter first, last paragraph_id
	function getChapter_FirstLast_paragraph_id($dataCenter, &$do) {
		$query = 'SELECT first_paragraph_id, last_paragraph_id'.
					' FROM '.$do['db_table_prefix'].CHAPTER_TABLE.
					' WHERE book_id=? AND book_chapter_id=?';
		$query_bind_values = array($do['searchResult_book_id'], $do['searchResult_chapter_id']);
		$res = $dataCenter->SQLite_DB->prepare($query);
		$res->execute($query_bind_values);
		$row = $res->fetch(PDO::FETCH_NUM);
		$do['chapter_first_paragraph_id']	= $row[0];
		$do['chapter_last_paragraph_id']	= $row[1];
		$do['book_chapters_first_paragraph_idA'] = array($row[0] => 1);
	}
	
	// Book chapters first, last paragraph_id
	function getBookChapters_FirstLast_paragraph_id($dataCenter, &$do) {
		$query = 'SELECT first_paragraph_id'.
					' FROM '.$do['db_table_prefix'].CHAPTER_TABLE.
					' WHERE book_id=?';
		$query_bind_values = array($do['searchResult_book_id']);
		$res = $dataCenter->SQLite_DB->prepare($query);
		$res->execute($query_bind_values);
		$do['book_chapters_first_paragraph_idA'] = array();
		while ($row = $res->fetch(PDO::FETCH_NUM)) {
			$do['book_chapters_first_paragraph_idA'][$row[0]] = 1;
		}
	}
	
	// Build full paragraph from sentences
	function buildParagraphText($do, $res, &$result_text) {
		
		$chapter_begins_str = $video_link_prefix = $video_link_postfix = '';
		
		while ($row = $res->fetch(PDO::FETCH_NUM)) {
			
			// Data from DB
			if (USE_ENCRYPTION_TABLE_DATA) {
				$pss_text	= json_decode(Decrypt($row[0]), TRUE);
			} else {
				$pss_text	= json_decode($row[0], TRUE);
			}
			$ps_id		= $row[1];
			$ps_type_id	= $row[2];

			// <div id="p2" class="b55"><span id="s1">All Glories to Śrī Śrī Guru-Gaurāṅga</span></div>
			// Text
			if (!empty($pss_text)) {
				
				// Join sentences
				$textA = array();
				foreach ($pss_text as $sentence_id => $sentence_text) {
					$textA[] = '<span id="s'.$sentence_id.'">'.$sentence_text.'</span>';
				}
				
				// Video link for paragraph
				if (!$do['is_books']) {
					$video_id = $do[TABLE_NAME_COMPONENT__VIDEO.'books'][ $do['searchResult_book_id'] ]['chapters'][ $do['searchResult_chapter_id'] ]['video_id'];
					if (!empty($video_id)) {
						$video_time = $row[3];

						$video_link_prefix = '<a '.VIDEO_REFERENCE__TAG_ID.'="'.
												$video_id.VIDEO_BOOK_TEXT__VIDEOID_TIME_SEPARATOR.
												$video_time.'">';
						$video_link_postfix = '</a>';
					} else {
						$video_link_prefix = $video_link_postfix = '';
					}
					if (/*DO_SELECT_CHAPTER || */IS__SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK) {
						$chapter_begins_str = (isset($do['book_chapters_first_paragraph_idA'][$ps_id]) ?
												'<a id="c'.$row[4].'"></a>' : '');
					}
				} else {
					if (/*DO_SELECT_CHAPTER || */IS__SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK) {
						$chapter_begins_str = (isset($do['book_chapters_first_paragraph_idA'][$ps_id]) ?
												'<a id="c'.$row[3].'"></a>' : '');
					}					
				}
				
				// Build Paragraph
				$result_text .=		// if chapter begins (parapgraph_id == first_parapgraph_id of chapter)
									$chapter_begins_str.
										
									// text
									'<div id="p'.$ps_id.'" class="'.DIV_CLASS_NAME_BEGIN.$ps_type_id.'">'.
										$video_link_prefix.
											implode(' ', $textA).
										$video_link_postfix.
									'</div>';
				
			// Empty
			} else {
				
				if (/*DO_SELECT_CHAPTER || */IS__SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK) {
					if (!$do['is_books']) {
						$chapter_begins_str = (isset($do['book_chapters_first_paragraph_idA'][$ps_id]) ?
												'<a id="c'.$row[4].'"></a>' : '');
					} else {
						$chapter_begins_str = (isset($do['book_chapters_first_paragraph_idA'][$ps_id]) ?
												'<a id="c'.$row[3].'"></a>' : '');
					}
				}
				
				$result_text .= 	// if chapter begins (parapgraph_id == first_parapgraph_id of chapter)
									$chapter_begins_str.
										
									// text
									'<div id="p'.$ps_id.'" class="'.DIV_CLASS_NAME_BEGIN.$ps_type_id.'">'.
									'</div>';
			}
		}
	}
?>