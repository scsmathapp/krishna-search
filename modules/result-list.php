<?php
	//------------------------------------------------------------------------------------------------------------------------
	// Result list:
	// Evaluate search text
	// 	by paragraph, by sentence
	//	[ps] = paragraph or sentence
	//------------------------------------------------------------------------------------------------------------------------
	function resultList_evaluateSearchText($dataCenter, &$do, $search_text, $polishForm) {
		
		// Local values copied
		//$siteOperationMode 			= $do['siteOperationMode'];
		$siteSearchType 			= $do['siteOperationMode_Values']['search'];		// 0=relevance, 	1=linear
		$siteSearchUnit				= $do['siteOperationMode_Values']['unit'];			// 0=paragraph, 	1=sentence
		
		$download_results		= array();
		$download_results_num	= 0;
		$result_text			= $neighbourResultLinks = $paging = '';
		$first_ps_id			= $first_ps_chapter_id = null;
		//$resultTotalNum = 0;
		
		if ($do['is_books']) {
			$destinationIndexID 		= 'bdi';
			$ajaxDataLoader_args_addon 	= '';
		} else if ($do['is_videos']) {
			$destinationIndexID 		= 'vdi';
			//$ajaxDataLoader_args_addon 	= ' data_result_list_video="1"';
			$ajaxDataLoader_args_addon 	= ', result_list_video:1';
		}
		
//$tt = 0;
//writeOut('Cleaned search text: '.$search_text);
//writeOut('Cleaned search text standard: '.$do['search_text_standard']);
//writeOut($search_text);
		if (!empty($polishForm)) {
			
//$t[++$tt] = microtime(true);
	//writeOut($polishForm);
	//writeOut($search_text);
			
			$do['evaluatePolishForm_called'] = false;
			
			//-----------------------------------------------------------------------------
			// Search type cases
			//-----------------------------------------------------------------------------
			$search_text_copy = str_replace('_', ' ', $search_text);
			
			// ["word"] , [word]
			if (strpos($search_text_copy, ' ') === false) {
				// ["word"]
				if ((substr($search_text_copy, 0, 1) 	== '"') and 
					(substr($search_text_copy, -1, 1) 	== '"')) {
					$search_text_copy = substr($search_text_copy, 1, -1);
					$do['result_ps_data'] = getSearchResult($dataCenter, $do, array('search_text' => $search_text_copy, 'mode' => 'word_exact'));
				
				// [word]
				} else {
					$do['result_ps_data'] = getSearchResult($dataCenter, $do, array('search_text' => $search_text_copy, 'mode' => 'word_root'));
				}
				
			// ["word1 word2"] , [word1 word2]
			} else if (	(strpos($search_text_copy, '(') 		=== false) &&
						(strpos($search_text_copy, ')') 		=== false) &&
						(strpos($search_text_copy, ' and ') 	=== false) &&
						(strpos($search_text_copy, ' or ') 	=== false)) {
				
				// ["word1 word2"]
				if ((substr($search_text_copy, 0, 1) 	== '"') and 
					(substr($search_text_copy, -1, 1) 	== '"') and
					(strpos(substr($search_text_copy, 1, -1), '"') === false)) {
					$search_text_copy = substr($search_text_copy, 1, -1);
					$do['result_ps_data'] = getSearchResult($dataCenter, $do, array('search_text' => $search_text_copy, 'mode' => 'expression'));
					
				// [word1 word2]
				} else if (strpos($search_text_copy, '"') === false) {
					$do['result_ps_data'] = getSearchResult($dataCenter, $do, array('search_text' => $search_text_copy, 'mode' => 'words'));
					
				} else {
					// Evaluate Polish-form
					$do['evaluatePolishForm_called'] = true;
					$do['result_ps_data'] = evaluatePolishForm($dataCenter, $do, $polishForm);
				}
				
			// [word1 or word2], etc.
			} else {
				// Evaluate Polish-form
				$do['evaluatePolishForm_called'] = true;
				$do['result_ps_data'] = evaluatePolishForm($dataCenter, $do, $polishForm);
			}
//writeOut('evaluate search text: '.round(microtime(true) - $t[$tt], 4).'  (result num: '.count($do['result_ps_data']).')'); $t[$tt] = microtime(true) - $t[$tt];
//$t[++$tt] = microtime(true);
	//writeOut($do['highlight_words_search']);
			
			$do['emptyResultPSData'] = (empty($do['result_ps_data']) ? true : false);
			
//writeOut($do['emptyResultPSData'] ? '1' : '0');
//writeOut('res.count: '.count($do['result_ps_data']));
//if (DEBUG_OPERATION_SHOW) { writeOut('Evaluate Polish-form'); }
//writeOut($polishForm);
			
//if (DEBUG_OPERATION_RESULT_SHOW) { writeOut($do['result_ps_data']); }

			//------------------------------------------------------------------------------------------------------
			// There was a search: load very first chapter if there is a book loaded (otherwise: book reading)
			//------------------------------------------------------------------------------------------------------
			if (!$do['emptyResultPSData'] and DO_SEARCH_QUERY and
				!IS__SITE_RESULT_LIST_ITEMS_NUM__NEXT_BUNCH and
				!IS__SITE_RESULT_LIST_ITEMS_NUM__ALL/* or DO_SELECT_RESULT*/) {
				
				if (!isset($do['searchResult_psCurrentNumber'])) {
					$do['searchResult_psCurrentNumber'] = 1;
					$do['result_ps_data_CurrentNumber'] = 0;
				} else {
					$do['result_ps_data_CurrentNumber'] = $do['searchResult_psCurrentNumber'] - 1;
				}
				
				$do['searchResult_book_id'] = $do['result_ps_data'][$do['result_ps_data_CurrentNumber']]['book_id'];
				
				//----------------------------------------------------------------------------------
				// Chapter's data: author, book
				// Unit: paragraph
				//----------------------------------------------------------------------------------
				if (IS__SITE_UNIT_TYPE__PARAGRAPH) {
					//$query = 'SELECT ps.chapter_id, ps.paragraph_id'.
					//			' FROM '.$do['db_table_prefix'].PS_TABLE.' AS ps'.
					//			' WHERE ps.'.PS_ID_TABLE_FIELD.'=?';
					$query = 'SELECT c.book_chapter_id, p.book_paragraph_id'.
								' FROM '		.$do['db_table_prefix'].PARAGRAPH_TABLE	.' AS p'.
								' INNER JOIN '	.$do['db_table_prefix'].CHAPTER_TABLE	.' AS c ON p.chapter_id = c.id'.
								' WHERE p.id=?';
				
				//----------------------------------------------------------------------------------
				//.. unit: sentence
				//----------------------------------------------------------------------------------
				} else {
					//$query = 'SELECT p.chapter_id, ps.paragraph_id'.
					//			' FROM '.$do['db_table_prefix'].PS_TABLE.				' AS ps'.
					//			' INNER JOIN '.$do['db_table_prefix'].PARAGRAPH_TABLE.	' AS p ON ps.paragraph_id=p.paragraph_id'.
					//			' WHERE ps.'.PS_ID_TABLE_FIELD.'=?';
					$query = 'SELECT c.book_chapter_id, p.book_paragraph_id'.
								' FROM '		.$do['db_table_prefix'].SENTENCE_TABLE	.' AS s'.
								' INNER JOIN '	.$do['db_table_prefix'].PARAGRAPH_TABLE	.' AS p ON s.paragraph_id = p.id'.
								' INNER JOIN '	.$do['db_table_prefix'].CHAPTER_TABLE	.' AS c ON p.chapter_id = c.id'.
								' WHERE s.id=?';
				}
				$query_bind_values = array($do['result_ps_data'][$do['result_ps_data_CurrentNumber']][PS_ID_TABLE_FIELD]);
				$res = $dataCenter->SQLite_DB->prepare($query);
				$res->execute($query_bind_values);
				if ($row = $res->fetch(PDO::FETCH_NUM)) {
					$do['searchResult_chapter_id']	 = $row[0];
					$do['searchResult_paragraph_id'] = $row[1];
				}
			} // if (!$do['emptyResultPSData'])
		} // if (!empty($polishForm))
		
		
//writeOut('highlight_words');
//writeOut($do['highlight_words']);
//writeOut('highlight_words_search / ...replace');
//writeOut($do['highlight_words_search']);
//writeOut($do['highlight_words_replace']);
		
		//----------------------------------------------------------------------------------
		// Process results
		//----------------------------------------------------------------------------------
		
		//----------------------------------------------------
		// URL encode search text
		//----------------------------------------------------
		$do['url_encoded_do_search_text'] = $url_encoded_do_search_text = url_encode_SearchText($search_text);
		
		//----------------------------------------------------
		// We have result => display them
		//----------------------------------------------------
		if (!$do['emptyResultPSData']) {
			
			// Highlight words pattern build from found keywords in query
			highlight_words_pattern_build($dataCenter, $do);
			
			if (!empty($do['highlight_words_search'.$do['db_table_prefix']])) {
				$highlight_words_search_pattern_num = count($do['highlight_words_search'.$do['db_table_prefix']]);
			} else {
				$highlight_words_search_pattern_num = 0;
			}
			
			// PS total result number
			$firstResultIndex = 0;
			$lastResultIndex = $do['result_ps_loaded_num'] - 1;
			
			//-------------------------------------------------------------
			// Get search text relevant pss' text
			//-------------------------------------------------------------
			$c = 0;
			$total_relevant_pss_idA = array();
			$relevant_pss_data = array();
			if (IS__SITE_UNIT_TYPE__PARAGRAPH) {
				for ($i = $firstResultIndex; $i <= $lastResultIndex; $i++) {
					
					//if (isset($do['result_ps_data'][$i])) {
						//$ps_data 				= $do['result_ps_data'][$i];
						//$ps_id 					= $do['result_ps_data'][$i][PS_ID_TABLE_FIELD];
						$total_relevant_pss_idA[$c] = $do['result_ps_data'][$i][PS_ID_TABLE_FIELD];
/*
if (!isset($do[$do['db_table_prefix'].'books'][$do['result_ps_data'][$i]['book_id']]['book_first_chapter_id'])) {
writeOut($do['result_ps_data'][$i]['book_id']);
writeOut($do[$do['db_table_prefix'].'books'][$do['result_ps_data'][$i]['book_id']]);
}
*/
						$relevant_pss_data[$do['result_ps_data'][$i][PS_ID_TABLE_FIELD]] = array(
							'order_num'				=> $i,
							'book_id' 				=> $do['result_ps_data'][$i]['book_id'],
							'chapter_id' 			=> ''/*, // filled below
							'crop_begin'			=> 0,
							'crop_length'			=> 1000*/
						);
						++$c;
					//}
				}
			//.. sentence: no crop
			} else {
				for ($i = $firstResultIndex; $i <= $lastResultIndex; $i++) {
					
					if (isset($do['result_ps_data'][$i])) {
						
						//$ps_data 					= $do['result_ps_data'][$i];
						//$ps_id 						= $do['result_ps_data'][$i][PS_ID_TABLE_FIELD];
						$total_relevant_pss_idA[$c] = $do['result_ps_data'][$i][PS_ID_TABLE_FIELD];
						
						$relevant_pss_data[$do['result_ps_data'][$i][PS_ID_TABLE_FIELD]] = array(
							'order_num'				=> $i,
							'book_id'	 			=> $do['result_ps_data'][$i]['book_id'],
							'chapter_id' 			=> ''/*, // filled below
							'crop_begin'			=> 0,
							'crop_length'			=> 1000*/
						);
						++$c;
					}
				}
			}
			
			//-------------------------------------------------------------
			// Paragraph text (full chapter title/number)
			//-------------------------------------------------------------
			$relevant_pss_text = $relevant_pss_text_full = $relevant_pss_text_type_shloka = $relevant_pss_text_sentence_pos = array();
			
			$relevant_pss_idA_offset 	= 0;
			//$total_relevant_pss_id_num 	= count($relevant_pss_id);
			//$total_relevant_pss_idA 	= $relevant_pss_id;
			
			$diacritics = new Diacritics();
			$query_video_addon = $do['is_books'] ? '' : ', c.video_id, p.video_time';
			
			//-------------------------------------------------------------
			// Read Paragraph or Sentence data from DB in chunks
			//-------------------------------------------------------------
			while ($relevant_pss_idA_offset < $do['result_ps_loaded_num']) {
				
				$relevant_pss_idA = array_slice($total_relevant_pss_idA, $relevant_pss_idA_offset, SELECT_IN_VALUE_NUMBER_THRESHOLD_IN_SQL);
				
				$place_holders = implode(',', array_fill(1, count($relevant_pss_idA), '?'));
				
				switch ($siteSearchUnit) {
					
					// paragraph
					case SITE_UNIT_TYPE__PARAGRAPH:
						$query_s = 'SELECT p.id, c.book_id, c.book_chapter_id, p.book_paragraph_id, p.paragraph_text, pt.paragraph_type_shloka, p.paragraph_type_id'.
											$query_video_addon.
									' FROM '.		$do['db_table_prefix'].PARAGRAPH_TABLE.					' AS p'.
									' INNER JOIN '.						   PARAGRAPH_TYPE_TABLE.			' AS pt ON p.paragraph_type_id = pt.paragraph_type_id'.
									' INNER JOIN '.	$do['db_table_prefix'].CHAPTER_TABLE.					' AS c ON p.chapter_id = c.id'.
									' WHERE p.id IN ('.$place_holders.')';
						break;
						
					// sentence
					case SITE_UNIT_TYPE__SENTENCE:
						$query_s = 'SELECT s.id, c.book_id, c.book_chapter_id, s.book_sentence_id, p.paragraph_text, p.book_paragraph_id, pt.paragraph_type_shloka, p.paragraph_type_id'.
											$query_video_addon.
									' FROM '.		$do['db_table_prefix'].SENTENCE_TABLE.					' AS s'.
									' INNER JOIN '.	$do['db_table_prefix'].PARAGRAPH_TABLE.					' AS p ON s.paragraph_id = p.id'.
									' INNER JOIN '.						   PARAGRAPH_TYPE_TABLE.			' AS pt ON p.paragraph_type_id = pt.paragraph_type_id'.
									' INNER JOIN '.	$do['db_table_prefix'].CHAPTER_TABLE.					' AS c ON p.chapter_id = c.id'.
									' WHERE s.id IN ('.$place_holders.')';
						break;
				} // switch ($siteSearchUnit)
				
				$res_s = $dataCenter->SQLite_DB->prepare($query_s);
				$res_s->execute($relevant_pss_idA);
				
				
				//----------------------------------------------------------------
				// Paragraph or Sentence
				//----------------------------------------------------------------
				switch ($siteSearchUnit) {
					
					//----------------------------------------------------------------
					// paragraph
					//----------------------------------------------------------------
					case SITE_UNIT_TYPE__PARAGRAPH:
						
						while ($row_s = $res_s->fetch(PDO::FETCH_NUM)) {
							
							if (USE_ENCRYPTION_TABLE_DATA) {
								if (IS__TEXT_TYPE__DIACRITICS) {
									$pss_text = json_decode(Decrypt($row_s[4]), TRUE);
								} else {
									$pss_text = json_decode($diacritics->Remove_Diacritics_by_Rules(Decrypt($row_s[4])), TRUE);
								}
							} else {
								if (IS__TEXT_TYPE__DIACRITICS) {
									$pss_text = json_decode($row_s[4], TRUE);
								} else {
									$pss_text = json_decode($diacritics->Remove_Diacritics_by_Rules($row_s[4]), TRUE);
								}
							}
							
							// store data
							$relevant_pss_data[$row_s[0]]['book_id']			= $row_s[1];
							$relevant_pss_data[$row_s[0]]['chapter_id']			= $row_s[2];
							$relevant_pss_data[$row_s[0]]['paragraph_id']		= $row_s[3];
							$relevant_pss_text[$row_s[0]]						= $pss_text;
							$relevant_pss_text_full[$row_s[0]]					= $pss_text;
							$relevant_pss_text_type_shloka[$row_s[0]]			= $row_s[5];
							$relevant_pss_data[$row_s[0]]['paragraph_type_id'] 	= $row_s[6];
							
							if ($do['is_videos']) {
								$relevant_pss_data[$row_s[0]]['video_id']		= $row_s[7];
								$relevant_pss_data[$row_s[0]]['video_time']		= $row_s[8];
							}
						}
						break;
						
					//----------------------------------------------------------------
					// sentence
					//----------------------------------------------------------------
					case SITE_UNIT_TYPE__SENTENCE:
						
						while ($row_s = $res_s->fetch(PDO::FETCH_NUM)) {
						
							if (USE_ENCRYPTION_TABLE_DATA) {
								$pss_text = json_decode(Decrypt($row_s[4]), TRUE);
								if (IS__TEXT_TYPE__DIACRITICS) {
									$pss_text = $pss_text[$row_s[3]];
								} else {
									$pss_text = $diacritics->Remove_Diacritics_by_Rules($pss_text[$row_s[3]]);
								}
							} else {
								$pss_text = json_decode($row_s[4], TRUE);
								if (IS__TEXT_TYPE__DIACRITICS) {
									$pss_text = $pss_text[$row_s[3]];
								} else {
									$pss_text = $diacritics->Remove_Diacritics_by_Rules($pss_text[$row_s[3]]);
								}
							}
							
							// store data
							$relevant_pss_data[$row_s[0]]['book_id']			= $row_s[1];
							$relevant_pss_data[$row_s[0]]['chapter_id']			= $row_s[2];
							$relevant_pss_data[$row_s[0]]['paragraph_id']		= $row_s[5];
							$relevant_pss_data[$row_s[0]]['sentence_id']		= $row_s[3];
							$relevant_pss_text[$row_s[0]]						= array($row_s[0] => $pss_text);
							$relevant_pss_text_full[$row_s[0]]					= array($row_s[0] => $pss_text);
							$relevant_pss_text_type_shloka[$row_s[0]]			= $row_s[6];
							$relevant_pss_data[$row_s[0]]['paragraph_type_id'] 	= $row_s[7];
							
							if ($do['is_videos']) {
								$relevant_pss_data[$row_s[0]]['video_id'] 		= $row_s[8];
								$relevant_pss_data[$row_s[0]]['video_time'] 	= $row_s[9];
							}
						}
						break;
				} // switch ($siteSearchUnit)
				
				$relevant_pss_idA_offset += SELECT_IN_VALUE_NUMBER_THRESHOLD_IN_SQL;
			} // while read data from DB
//writeOut('get text: '.round(microtime(true) - $t[$tt], 4)); $t[$tt] = microtime(true) - $t[$tt];
//$t[++$tt] = microtime(true);
		
//writeOut($relevant_pss_data);
//writeOut($relevant_pss_text_sentence_pos);

			//----------------------------------------------------------------
			// Check on IDs
			//----------------------------------------------------------------
			if (empty($do['searchResult_psCurrentNumber']) or ($lastResultIndex + 1 < $do['searchResult_psCurrentNumber'])) {
				$do['searchResult_psCurrentNumber'] = 1;
				$do['result_ps_data_CurrentNumber'] = 0;
			} else {
				$do['result_ps_data_CurrentNumber'] = $do['searchResult_psCurrentNumber'] - 1;
			}
			if (DO_SEARCH_QUERY) {
				switch ($siteSearchUnit) {
					case SITE_UNIT_TYPE__PARAGRAPH:	$do['searchResult_paragraph_id']	= intval($relevant_pss_data[$do['result_ps_data'][$do['result_ps_data_CurrentNumber']][PS_ID_TABLE_FIELD]]['paragraph_id']); break;
					case SITE_UNIT_TYPE__SENTENCE:	$do['searchResult_sentence_id']		= intval($relevant_pss_data[$do['result_ps_data'][$do['result_ps_data_CurrentNumber']][PS_ID_TABLE_FIELD]]['sentence_id']); break;
				}
				//$do['searchResult_book_id']		= $do[$do['db_table_prefix'].'chapter_id__book_id'][$do['searchResult_chapter_id']];
				$do['searchResult_book_id']		= $relevant_pss_data[$do['result_ps_data'][$do['result_ps_data_CurrentNumber']][PS_ID_TABLE_FIELD]]['book_id'];
				$do['searchResult_chapter_id']	= $relevant_pss_data[$do['result_ps_data'][$do['result_ps_data_CurrentNumber']][PS_ID_TABLE_FIELD]]['chapter_id'];
			}
			//$chosen_chapter_id = $do['searchResult_chapter_id'];
			//$chosen_book_id = $do['searchResult_book_id'];
			
			
			//----------------------------------------------------------------
			// Compose output
			//----------------------------------------------------------------
			$previous_result_book_id 	= -1;
			$previous_result_chapter_id = -1;
			$first_ps_id 				= $total_relevant_pss_idA[0];
			$first_ps_chapter_id 		= $relevant_pss_data[$first_ps_id]['chapter_id'];
			$result_list_ul_class		= (IS__SITE_RESULT_LIST_TEXT_LENGTH_TYPE__LONG_TEXT ? 
											' class="multi-line"' : ' class="one-line"');
			
			if ($siteSearchType == SITE_SEARCH_TYPE__RELEVANCE) {
				$result_text .= '<ul'.$result_list_ul_class.'>';
			}
			
			//----------------------------
			// Highlight keywords search
			//----------------------------
			$html_tag_reserved_word__found__in_highlight_words = array();
			for ($i = 0; $i < $highlight_words_search_pattern_num; $i++) {
				
				$found = false;
				$word = TRUE;

				while (!$found and ($word !== NULL)) {
					$word = key($do['html_tag_reserved_words']);
					$value = current($do['html_tag_reserved_words']);
					next($do['html_tag_reserved_words']);

					if (strpos($do['highlight_words_search'.$do['db_table_prefix']][$i], '('.$word.')')) {
						$found = true;
					}
				}
				$html_tag_reserved_word__found__in_highlight_words[$i] = $found;
			}
			
			$pp_playlist_pos = 0;

//writeOut($do['highlight_words_search']);
//writeOut($do['highlight_words_replace']);
//writeOut($html_tag_reserved_word__found__in_highlight_words);

			//----------------------------
			// Each result item
			//----------------------------
			foreach ($total_relevant_pss_idA as $ps_id) {
				
				$result_item = $relevant_pss_data[$ps_id];
				
				$relevant_pss_data__ps_id__order_num__plus_1 = $result_item['order_num']+1;
				
				// All sentences
				if (!empty($relevant_pss_text[$ps_id])) {
					foreach ($relevant_pss_text[$ps_id] as $current_sentence_id => $current_sentence) {
						
						$current_sentence_full = $relevant_pss_text_full[$ps_id][$current_sentence_id];
/*
if (strpos($current_sentence, 'Later Sri Chaitanya') !== false) {
	writeOut($current_sentence);
	writeOut('---------');
	//writeOut($current_sentence_full);
	//writeOut('---------');
}*/
						
						//----------------------------------------------------------------
						// Highlight keywords
						//----------------------------------------------------------------
						for ($pattern_i = 0; $pattern_i < $highlight_words_search_pattern_num; $pattern_i++) {

							//----------------------------------------------------------------
							// Item
							//----------------------------------------------------------------
							// html tag words
							if ($html_tag_reserved_word__found__in_highlight_words[$pattern_i]) {
								
								$current_sentence = mb_ereg_replace(	
											'(^|[^'.REGEX__WORD_PART.'])(?<!<span |<a |<div |'.
																'<div id="p[0-9]{1}" |'.
																'<div id="p[0-9]{2}" |'.
																'<div id="p[0-9]{3}" |'.
																'<div id="p[0-9]{4}" |'.
																'<div id="p[0-9]{5}" |'.
																'<div id="p[0-9]{6}" |'.
																'<div id="p[0-9]{7}" )('.$word.')([^'.REGEX__WORD_PART.']|$)', 
											$do['highlight_words_replace'.$do['db_table_prefix']][$pattern_i], 
											$current_sentence,
											'i'); // case ignored
/*
if (strpos($current_sentence, 'Later Sri Chaitanya') !== false) {
	writeOut($current_sentence);
	writeOut('----HTML tag words-----');
}*/
							// other words
							} else {
								// item
								$current_sentence = mb_ereg_replace(
											$do['highlight_words_search'.$do['db_table_prefix']][$pattern_i], 
											$do['highlight_words_replace'.$do['db_table_prefix']][$pattern_i], 
											$current_sentence,
											'i'); // case ignored
/*								
if ($current_sentence === 'Human life') {
	writeOut($current_sentence.' '.$pattern_i.' '.$do['highlight_words_search'][$pattern_i].' '.$do['highlight_words_replace'][$pattern_i].' -> '.$current_sentence);
	writeOut($current_sentence.' -> '.$current_sentence);
}*/
/*
if (strpos($current_sentence, 'Later Sri Chaitanya') !== false) {
	writeOut('----');
	writeOut($do['highlight_words_search'][$pattern_i]);
	writeOut($do['highlight_words_replace'][$pattern_i]);
	writeOut($current_sentence);
	writeOut('----other words: '.$do['highlight_words_search'][$pattern_i].' -> '.$do['highlight_words_replace'][$pattern_i].'-----');
}*/
							}
							//----------------------------------------------------------------
							// Tooltip
							//----------------------------------------------------------------
							if (IS__SITE_RESULT_LIST_TOOLTIP__ON) {

								// html tag words
								if ($html_tag_reserved_word__found__in_highlight_words[$pattern_i]) {

									// tooltip
									$current_sentence_full = mb_ereg_replace(	
												'(^|[^'.REGEX__WORD_PART.'])(?<!<span |<a |<div |'.
																	'<div id="p[0-9]{1}" |'.
																	'<div id="p[0-9]{2}" |'.
																	'<div id="p[0-9]{3}" |'.
																	'<div id="p[0-9]{4}" |'.
																	'<div id="p[0-9]{5}" |'.
																	'<div id="p[0-9]{6}" |'.
																	'<div id="p[0-9]{7}" )('.$word.')([^'.REGEX__WORD_PART.']|$)', 
												$do['highlight_words_replace'.$do['db_table_prefix']][$pattern_i], 
												$current_sentence_full,
												'i'); // case ignored

								// other words
								} else {

									// tooltip
									$current_sentence_full = mb_ereg_replace(
												$do['highlight_words_search'.$do['db_table_prefix']][$pattern_i], 
												$do['highlight_words_replace'.$do['db_table_prefix']][$pattern_i], 
												$current_sentence_full,
												'i'); // case ignored
								}
								

							}
						} // end: highlight words
						
						// Write back updated text
						if ($current_sentence !== FALSE)		{ $relevant_pss_text[$ps_id][$current_sentence_id]		= $current_sentence; }
						if ($current_sentence_full !== FALSE)	{ $relevant_pss_text_full[$ps_id][$current_sentence_id] = $current_sentence_full; }
						
					} // end: all sentences in paragraph
					
					
					//----------------------------------------------------------------
					// Crop
					//----------------------------------------------------------------
					if (IS__SITE_UNIT_TYPE__PARAGRAPH) {

						// search for first highlight
						$first_sentence_idA = array_slice($relevant_pss_text[$ps_id], 0, 1, TRUE);
						$last_sentence_idA 	= array_slice($relevant_pss_text[$ps_id], -1, 1, TRUE);
						
						$first_sentence_id = key($first_sentence_idA);
						$t = current($first_sentence_idA);
						next($first_sentence_idA);

						$last_sentence_id = key($last_sentence_idA);
						$t = current($last_sentence_idA);
						next($last_sentence_idA);

						while (($first_sentence_id <= $last_sentence_id) && 
								(strpos($relevant_pss_text[$ps_id][$first_sentence_id], '<span class="highlight">') === FALSE)) {
							++$first_sentence_id;
						}
						if ($last_sentence_id < $first_sentence_id) {
							$first_sentence_id = $last_sentence_id;
						}

						// Build short paragraph from sentences
						$textA = array();
						for ($j=$first_sentence_id; $j<=$last_sentence_id; $j++) {
							$textA[] = $relevant_pss_text[$ps_id][$j];
						}

						$paragraph_text = implode(' ', $textA);

						// Build full paragraph from sentences
						$textA = array();
						foreach ($relevant_pss_text[$ps_id] as $sentence_id => $sentence_text) {
							$textA[] = $sentence_text;
						}
						$paragraph_full_text = implode(' ', $textA);

					} else {
						$paragraph_text			= $relevant_pss_text[$ps_id][$ps_id];
						$paragraph_full_text	= $relevant_pss_text[$ps_id][$ps_id];
					}

				} else {
					$paragraph_text = $paragraph_full_text = '';
				}				
				
				
				/*
				writeOut('book: '.$chosen_book_id.' '.$result_item['book_id']);
				writeOut('chapter: '.$chosen_chapter_id.' '.$result_item['chapter_id']);
				writeOut($item_shortcut_link);
				*/
				//----------------------------------------------------------------
				// Result
				//----------------------------------------------------------------
				// Linear: display changing book title/chapter
				if (!IS__SITE_SIMPLIFIED_LAYOUT && ($siteSearchType == SITE_SEARCH_TYPE__LINEAR)) {
					
					//----------------------------------------------------------------
					// Different book comes => write out header
					//----------------------------------------------------------------
					if ($previous_result_book_id != $result_item['book_id']) {
						
						$chosen_item = (isset($do['searchResult_book_id']) and 
										($result_item['book_id'] == $do['searchResult_book_id']));
						
						$result_text .= NEWLINE.'<div class="search-result-book-heading'.
								
								($chosen_item ? ' chosen-search-result-item' : '').'">'.
								
									'<a href="javascript:void(0);"'.
										' onclick="ajaxDataLoader({ url: \''.WEB_INDEX_FILE.'?q='.$url_encoded_do_search_text.
																				$do['urlDataTransfer'].
																				$do['url_is_video_books_addon'].
																				'&amp;b='.$result_item['book_id'].'&amp;c=1'.
																				(IS__SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK ? '#c1' : '').'"'.'\''.
																	', result_list_item_clicked: this,'.
																	' result_list_nth_item: '.$relevant_pss_data__ps_id__order_num__plus_1.
																	$ajaxDataLoader_args_addon.
																'});"'.
										($chosen_item ? ' id="'.$destinationIndexID.'"' : '').
										'>'.
										$do[$do['db_table_prefix'].'books'][$result_item['book_id']]['book_title'].
									'</a>'.
								
							'</div>';
						// save current book id
						$previous_result_book_id = $result_item['book_id'];
					}
					
					
					//----------------------------------------------------------------
					// Different chapter comes => write out header
					//----------------------------------------------------------------
					if ($previous_result_chapter_id != $result_item['chapter_id']) {
						
						$chosen_item = (isset($do['searchResult_chapter_id']) and 
									($result_item['chapter_id'] == $do['searchResult_chapter_id']));
						
						$result_text .= (($previous_result_chapter_id != -1) ? '</ul>' : '').
							NEWLINE.
							'<div class="search-result-chapter-heading'.
								
								($chosen_item ? ' chosen-search-result-item' : '').'">'.
								
									'<a href="javascript:void(0);"'.
										' onclick="ajaxDataLoader({ url: \''.WEB_INDEX_FILE.'?q='.$url_encoded_do_search_text.
																				$do['urlDataTransfer'].
																				$do['url_is_video_books_addon'].
																				'&amp;b='.$result_item['book_id'].
																				'&amp;c='.$result_item['chapter_id'].
																				(IS__SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK ? '#c'.$result_item['chapter_id'] : '').
																				'"'.'\''.
																	', result_list_item_clicked: this,'.
																	' result_list_nth_item: '.$relevant_pss_data__ps_id__order_num__plus_1.
																	$ajaxDataLoader_args_addon.
																'});"'.
										
										($chosen_item ? ' id="'.$destinationIndexID.'"' : '').
										'>'.
										$do[$do['db_table_prefix'].'books'][$result_item['book_id']]['chapters'][$result_item['chapter_id']]['chapter_title'].
									'</a>'.
								
							'</div><ul'.$result_list_ul_class.'>';
						// save current chapter id
						$previous_result_chapter_id = $result_item['chapter_id'];
					}
				}
				
				switch ($siteSearchUnit) {
					case SITE_UNIT_TYPE__PARAGRAPH:
						//$paragraph_id = $ps_id;
						$ps_link = '&amp;b='.$result_item['book_id'].
									'&amp;p='.$result_item['paragraph_id'].'#p'.$result_item['paragraph_id'];
						break;
						
					case SITE_UNIT_TYPE__SENTENCE:
						//$paragraph_id = $result_item['paragraph_id'];
						$ps_link = '&amp;b='.$result_item['book_id'].
									'&amp;s='.$result_item['sentence_id'].
									'&amp;p='.$result_item['paragraph_id'].'#p'.$result_item['paragraph_id'];
						break;
				}
				
				if (!IS__SITE_SIMPLIFIED_LAYOUT &&
					!IS__SITE_RESULT_LIST_ITEMS_NUM__NEXT_BUNCH &&
					!IS__SITE_RESULT_LIST_ITEMS_NUM__ALL) {
					$chosen_item = ($result_item['order_num'] == $do['result_ps_data_CurrentNumber']);
				} else {
					$chosen_item = false;
				}
				
				
				//----------------------------------------------------------------
				// List item with tooltip
				//----------------------------------------------------------------
				//$result_text .= NEWLINE.'<li'.($chosen_item ? ' class="chosen-search-result-item"' : '').
				//writeOut('db_table_prefix: '.$do['db_table_prefix']);
				//writeOut('result_item[book_id]: '.$result_item['book_id']);
				//writeOut($do);
				//writeOut($do['books']);
				//writeOut($do[$do['db_table_prefix'].'books']);
				$book_font_code = $do[$do['db_table_prefix'].'books'][$result_item['book_id']]['book_font_code'];
				
				$result_text .= NEWLINE.'<li'.(!IS__SITE_SIMPLIFIED_LAYOUT && $chosen_item ? 
												' class="chosen-search-result-item'.
													($book_font_code != 0 ? ' '.$do['font_code__font_name'][$book_font_code] : '').'"' 
												:
												($book_font_code != 0 ? ' class="'.$do['font_code__font_name'][$book_font_code].'"' : '')).
						//' class="b'.$relevant_pss_text_class[$ps_id].'"'.
										'>'.
						
						'<div class="cover"'.
						// Tooltip: jQuery: tipTip
						(IS__SITE_RESULT_LIST_TOOLTIP__ON && !IS__SITE_SIMPLIFIED_LAYOUT ?
							' onmouseover="Tip(\''.
							//' title="'.
								
								//-----------------------------------------------------------------------------------------------
								// addslashes():
								//------------------------
								// Returns a string with backslashes before characters that need to be quoted in database queries etc. 
								// characters are:
								//
								// single quote (')
								// double quote (")
								// backslash (\)
								// NUL (the NULL byte)
								//
								//------------------------
								// htmlspecialchars():
								//------------------------
								// '&' (ampersand) becomes '&amp;'
								// '"' (double quote) becomes '&quot;' when ENT_NOQUOTES is not set.
								// "'" (single quote) becomes '&#039;' (or &apos;) only when ENT_QUOTES is set.
								// '<' (less than) becomes '&lt;'
								// '>' (greater than) becomes '&gt;'
								//-----------------------------------------------------------------------------------------------
								
								addslashes( htmlspecialchars(
									($book_font_code != 0 ? '<span class="'.$do['font_code__font_name'][$book_font_code].'">' : '').
									($do['is_books'] ?
										'<b>'.
											$do[$do['db_table_prefix'].'books'][$result_item['book_id']]['book_title'].
										'</b>'.
										'<span class="tt_chapter">('.
											$do[$do['db_table_prefix'].'books'][$result_item['book_id']]['chapters'][$result_item['chapter_id']]['chapter_title'].
										')</span>'.
										'<span class="tt_icon icon icon_book_small"></span>'
										:
										'<b>'.
											$do[$do['db_table_prefix'].'books'][$result_item['book_id']]['chapters'][$result_item['chapter_id']]['chapter_title'].
										'</b>'.
										'<span class="tt_icon icon icon_video_small"></span>'
									).
									'<br /><span class="tt_author">'.
										$do[$do['db_table_prefix'].'authors'][ $do[$do['db_table_prefix'].'books'][$result_item['book_id']]['author_id'] ]['full_name'].
									'</span>'.
									'<br />'.($relevant_pss_text_type_shloka[$ps_id] ? 
												'<b>'.$paragraph_full_text.'</b>' : $paragraph_full_text).
									($book_font_code != 0 ? '</span>' : ''),
									ENT_COMPAT
								) ).
								'\', WIDTH, 500, MAXWIDTH, 1000, FIX, [\'search-result-book-text-container\', 4, 4])" onmouseout="UnTip()"'
								//'"'
							: ''
						).
						'></div>'.
						
						'<a href="javascript:void(0);"'.
							' onclick="ajaxDataLoader({ url: \''.WEB_INDEX_FILE.'?q='.$url_encoded_do_search_text.
																	$do['urlDataTransfer'].
																	$do['url_is_video_books_addon'].
																	'&amp;rn='.$relevant_pss_data__ps_id__order_num__plus_1.
																	$ps_link.'\''.
														', anchor: \'p'.$result_item['paragraph_id'].'\''.
														', result_list_item_clicked: this'.
														', result_list_nth_item: '.$relevant_pss_data__ps_id__order_num__plus_1.
														$ajaxDataLoader_args_addon.
													'});"'.
							
							($do['is_videos'] && !empty($result_item['video_id']) ? 
								' class="prettyPhoto_link"'.
								' rel="prettyPhoto[rl]"'.
								' pp_href="https://www.youtube.com/watch?v='.$result_item['video_id'].'"'.
								' pp_video_begin="'.$result_item['video_time'].'"'.
								' pp_lang_code="'.$do['languages'][$do['selected_language_id']]['lang_code_display'].'"'.
								' pp_desc_1="'.htmlspecialchars( 
										$do[$do['db_table_prefix'].'books'][$result_item['book_id']]['chapters'][$result_item['chapter_id']]['chapter_title']
									, ENT_QUOTES).'"'.
								' pp_desc_2="'.htmlspecialchars( 
										$do[$do['db_table_prefix'].'authors'][ $do[$do['db_table_prefix'].'books'][$result_item['book_id']]['author_id'] ]['full_name']
									, ENT_QUOTES).'"'.
								' pp_playlist_text="'.htmlspecialchars( $paragraph_text, ENT_QUOTES ).'"'.
								' pp_playlist_pos="'.(++$pp_playlist_pos).'"'
								: 
								''
							).
							' result_anchor="p'.$result_item['paragraph_id'].'"'.
							($chosen_item ? ' id="'.$destinationIndexID.'"' : '').
							'>'.
							// remove <a> from result list text (<span> is ok)
							($relevant_pss_text_type_shloka[$ps_id] ? 
								'<b>'.strip_tags($paragraph_text, '<span>').'</b>' 
								: 
								strip_tags($paragraph_text, '<span>')
							).
						'</a>'.
					'</li>';
				
				
				
				//-----------------------------------------------------------------------------------------------
				// Download result
				//-----------------------------------------------------------------------------------------------
				if (DO_DOWNLOAD_RESULTS) {
					$download_result_item__text 	= strip_tags($paragraph_full_text);
					$download_result_item__author 	= $do[$do['db_table_prefix'].'authors'][ $do[$do['db_table_prefix'].'books'][$result_item['book_id']]['author_id'] ]['full_name'];
					$download_result_item__book 	= $do[$do['db_table_prefix'].'books'][$result_item['book_id']]['book_title'];
					$download_result_item__chapter 	= $do[$do['db_table_prefix'].'books'][$result_item['book_id']]['chapters'][$result_item['chapter_id']]['chapter_title'];
					
					switch ($do['download_results_quote_template']) {
						
						case 1:
							$download_results[] = ++$download_results_num.'. '.$download_result_item__text;
							break;
							
						case 2:
							$download_results[] = ++$download_results_num.'. '.$download_result_item__text.
													NEWLINE.'('.$download_result_item__author.')';
							break;
							
						case 3:
							$download_results[] = ++$download_results_num.'. '.$download_result_item__text.
													NEWLINE.'('.$download_result_item__author.': '.$download_result_item__book.')';
							break;
							
						case 4:
							$download_results[] = ++$download_results_num.'. '.$download_result_item__text.
													NEWLINE.'('.$download_result_item__author.': '.$download_result_item__book.
																' ['.$download_result_item__chapter.'])';
							break;
					}
				} // if (DO_DOWNLOAD_RESULTS)
				
			} // foreach ($relevant_pss_id as $ps_id)
			
//writeOut('compose output, highlight: '.round(microtime(true) - $t[$tt], 4)); $t[$tt] = microtime(true) - $t[$tt];
//writeOut('result-list total: '.round(array_sum($t), 4));
			
			
			//----------------------------------------------------------------
			// Limited result list display
			//----------------------------------------------------------------
			$resultList_buttonNumber_atTheEnd = 0;
			$nextBunchButton = $allButton = false;
			
			// Next [n] items (1..m,1..n)
			if ($do['total_result_ps_loaded_num'] < $do['total_result_ps_num']) {
				++$resultList_buttonNumber_atTheEnd;
				$nextBunchButton = true;
			}
			// All the rest items
			if ($do['total_result_ps_loaded_num'] + SEARCH_RESULT_LIST__ITEM_NUM__LOAD_NEXT < $do['total_result_ps_num']) {
				++$resultList_buttonNumber_atTheEnd;
				$allButton = true;
			}
			
			// Next [n] items (1..m,1..n)
			if ($nextBunchButton) {
				// List item with button to load the rest of the results
				$result_text .= NEWLINE.'<li>'.
						'<a href="javascript:void(0);"'.
							' onclick="ajaxDataLoader({ url: \''.WEB_INDEX_FILE.'?q='.$url_encoded_do_search_text.
																	$do['urlDataTransfer'].
																	$do['url_is_video_books_addon'].
																	'&amp;rl_loaded_num='.$do['total_result_ps_loaded_num'].
																	'&amp;rl_next=1'.
																	'&amp;sq=Search'.'\''.
														', result_list_load_next: 1'.
														', result_list_button_number_at_the_end: '.$resultList_buttonNumber_atTheEnd.
														$ajaxDataLoader_args_addon.
													'});"'.
													
							' class="load-all">Load next '.
								(($do['total_result_ps_loaded_num'] + SEARCH_RESULT_LIST__ITEM_NUM__LOAD_NEXT <= $do['total_result_ps_num'])
									? SEARCH_RESULT_LIST__ITEM_NUM__LOAD_NEXT
									: $do['total_result_ps_num'] - $do['total_result_ps_loaded_num']).
								' results &raquo'.
						'</a>'.
					'</li>';
			}
			
			// All the rest items
			if ($allButton) {
				
				// List item with button to load the rest of the results
				$result_text .= NEWLINE.'<li>'.
						'<a href="javascript:void(0);"'.
							' onclick="ajaxDataLoader({ url: \''.WEB_INDEX_FILE.'?q='.$url_encoded_do_search_text.
																	$do['urlDataTransfer'].
																	$do['url_is_video_books_addon'].
																	'&amp;rl_loaded_num='.$do['total_result_ps_loaded_num'].
																	'&amp;rl_all=1'.
																	'&amp;sq=Search'.'\''.
														', result_list_load_all: 1'.
														', result_list_button_number_at_the_end: '.$resultList_buttonNumber_atTheEnd.
														$ajaxDataLoader_args_addon.
													'});"'.
							' class="load-all">Load all the rest '.($do['total_result_ps_num'] - $do['total_result_ps_loaded_num']).' results &raquo'.
						'</a>'.
					'</li>';
			}
			
			// last closing for list of urls
			$result_text .= '</ul>';
			
			
			//----------------------------------------------------------------
			// Prev/Next result links
			//----------------------------------------------------------------
			if ($do['need_neighbour_result_links']) {
				
				//----------------------------------------------------------------
				// Previous result link
				//----------------------------------------------------------------
				if (1 < $do['searchResult_psCurrentNumber']) {
					
					$new_num	= $do['searchResult_psCurrentNumber'] - 1;
					$curr_data	= $do['result_ps_data'][$new_num-1];
					
					if (!IS__SITE_UNIT_TYPE__PARAGRAPH) {
						$curr_data['paragraph_id'] = $relevant_pss_data[$do['result_ps_data'][$new_num-1][PS_ID_TABLE_FIELD]]['paragraph_id'];
					}
					
					$result_link_prev_url = WEB_INDEX_FILE.'?q='.$do['url_encoded_do_search_text'].
													$do['urlDataTransfer'].
													$do['url_is_video_books_addon'].
													'&amp;rn='.$new_num.
													
													($do['siteOperationMode_Values']['unit'] == SITE_UNIT_TYPE__PARAGRAPH ? 
							
														'&amp;b='.$curr_data['book_id'].
														'&amp;p='.$curr_data['paragraph_id'].
														'#p'.$curr_data['paragraph_id']
														: 
														'&amp;b='.$curr_data['book_id'].
														'&amp;s='.$curr_data['sentence_id'].
														//'#s'.$curr_data['sentence_id']
														'#p'.$curr_data['paragraph_id']
													);
					

				} else { $result_link_prev_url = $paragraph_id = $new_num = ''; }
				
				$result_link_prev = '<a id="result-previous-link-a" class="button left" title="Previous result"'.
										(!empty($result_link_prev_url) ?
											' href="javascript:void(0);"'.
											' onclick="ajaxDataLoader({ url: \''.$result_link_prev_url.'\''.
																		', anchor: \'p'.$curr_data['paragraph_id'].'\''.
																		', result_list_nth_item: \''.$new_num.'\''.
																		$ajaxDataLoader_args_addon.
																	' });"'
											:
											''
										).
										'><span class="icon icon_arrow_left"></span>'.
									'</a>';
				
				//----------------------------------------------------------------
				// Next result link
				//----------------------------------------------------------------
				if ($do['searchResult_psCurrentNumber'] < $do['total_result_ps_loaded_num']) {
					
					$new_num	= $do['searchResult_psCurrentNumber'] + 1;
					$curr_data	= $do['result_ps_data'][$new_num-1];
					
					if (!IS__SITE_UNIT_TYPE__PARAGRAPH) {
						$curr_data['paragraph_id'] = $relevant_pss_data[ $do['result_ps_data'][$new_num-1][PS_ID_TABLE_FIELD] ]['paragraph_id'];
					}
					
					$result_link_next_url = WEB_INDEX_FILE.'?q='.$do['url_encoded_do_search_text'].
													$do['urlDataTransfer'].
													$do['url_is_video_books_addon'].
													'&amp;rn='.$new_num.
													
													($do['siteOperationMode_Values']['unit'] == SITE_UNIT_TYPE__PARAGRAPH ? 
													
														'&amp;b='.$curr_data['book_id'].
														'&amp;p='.$curr_data['paragraph_id'].
														'#p'.$curr_data['paragraph_id']
														: 
														'&amp;b='.$curr_data['book_id'].
														'&amp;s='.$curr_data['sentence_id'].
														//'#s'.$curr_data['sentence_id']
														'#p'.$curr_data['paragraph_id']
													);

				} else { $result_link_next_url = $paragraph_id = $new_num = ''; }
				
				$result_link_next = '<a id="result-next-link-a" class="button right" title="Next result"'.
										(!empty($result_link_next_url) ?
											' href="javascript:void(0);"'.
											' onclick="ajaxDataLoader({ url: \''.$result_link_next_url.'\''.
																		', anchor: \'p'.$curr_data['paragraph_id'].'\''.
																		', result_list_nth_item: \''.$new_num.'\''.
																		$ajaxDataLoader_args_addon.
																	'});"'
											:
											''
										).
										'><span class="icon icon_arrow_right"></span>'.
									'</a>';
				
				$neighbourResultLinks = $result_link_prev.
											//'<span class="button middle no-hover" style="background-color:white;">'.
											//	'<span class="label">Results</span></span>'.
										$result_link_next;
			} // if ($do['need_neighbour_result_links'])

			
		} else {
			$result_text = NO_RELEVANT_PS_FOR_SEARCH_TEXT;
		}
		
		
		
		//--------------------------------------------------------------
		// Return data
		//--------------------------------------------------------------
		$ret = array(	'result_text' 			=> $result_text,
						'download_results_text'	=> implode(NEWLINE.NEWLINE, $download_results),
						//'paging' 				=> $paging,
						'neighbour_result_links'=> $neighbourResultLinks);
		return $ret;
	} // function resultList_evaluateSearchText(&$dataCenter, $do)
?>