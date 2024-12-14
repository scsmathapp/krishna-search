<?php
/*-----------
* Functions:
* -----------
 	load_book_chapter_paragraph_id($dataCenter, &$do)
 	
 	highlight_words_pattern_build($dataCenter, &$do, $highlight_words_TypeCategorised)
 	
 	save_resultList_data(&$do)
	restore_resultList_data(&$do)
	
	url_encode_SearchText($s)
 	clean_store_SearchText($dataCenter, &$do, $s)
	clean_SearchText($dataCenter, $do, $s)
	convertToPolishForm($dataCenter, $s)
	evaluatePolishForm($dataCenter, $polishForm)
	getWordRelevantParagraphSentenceData($dataCenter, &$porterStemmer, $do, $word)
*/
	//------------------------------------------------------------------------------------------------------------------------
	// Load (book_id, chapter_id, paragraph_id) if necessary
	//------------------------------------------------------------------------------------------------------------------------
	function load_book_chapter_paragraph_id($dataCenter, &$do) {
		
		//------------------------------------
		// Cross reference in the books
		//------------------------------------
		if (DO_CROSS_REFERENCE) {
			$query = 'SELECT book_id, book_paragraph_id'.
						' FROM ' . DB_TABLE_PREFIX_ACTION_TYPE . REFERENCE_PARAGRAPH_ID_TABLE.
						' WHERE reference=?';
			$query_bind_values = array($do['cross_reference_id']);
			$res = $dataCenter->SQLite_DB->prepare($query);
			$res->execute($query_bind_values);
			if ($row = $res->fetch(PDO::FETCH_NUM)) {
				$do['searchResult_book_id'] = $row[0];
				$do['searchResult_paragraph_id'] = $row[1];
			}
		}
		
		
		//------------------------------------
		// Reference info in the books
		//------------------------------------
		if (DO_REFERENCE_INFO) {
			$query = 'SELECT book_id'.
						' FROM ' . DB_TABLE_PREFIX_ACTION_TYPE . REFERENCE_INFO_TABLE.
						' WHERE reference_id=?';
			$query_bind_values = array($do['reference_info_id']);
			$res = $dataCenter->SQLite_DB->prepare($query);
			$res->execute($query_bind_values);
			if ($row = $res->fetch(PDO::FETCH_NUM)) {
				$do['searchResult_book_id'] = $row[0];
			}
		}
		
		
		//------------------------------------
		// Paragraph view or Cross reference
		//------------------------------------
		if (DO_SELECT_PARAGRAPH_SENTENCE or DO_CROSS_REFERENCE) {
			$query_video_book_addon = (IS_ACTION_VIDEO ? ', c.last_paragraph_id-c.first_paragraph_id' : '');
			
			// Paragraph view or cross-reference
			if (isset($do['searchResult_paragraph_id'])) {
				
				// Paragraph's data: chapter, book
				$query = 'SELECT c.book_chapter_id'.$query_video_book_addon.
							' FROM '		. DB_TABLE_PREFIX_ACTION_TYPE . PARAGRAPH_TABLE	. ' AS p'.
							' INNER JOIN '	. DB_TABLE_PREFIX_ACTION_TYPE . CHAPTER_TABLE	. ' AS c ON p.chapter_id = c.id'.
							' WHERE c.book_id=? and p.book_paragraph_id=?';
				$query_bind_values = array($do['searchResult_book_id'], $do['searchResult_paragraph_id']);
				$res = $dataCenter->SQLite_DB->prepare($query);
				$res->execute($query_bind_values);
				if ($row = $res->fetch(PDO::FETCH_NUM)) {
					$do['searchResult_chapter_id'] = $row[0];
					if (IS_ACTION_VIDEO) {
						$do['searchResult_video_books_chapter_paragraph_num'] = $row[1];
					}
				}
				
			// Paragraph view with Sentence focus
			} else if (isset($do['searchResult_sentence_id'])) {
				
				// Paragraph's data: author, book
				$query = 'SELECT c.book_chapter_id, p.book_paragraph_id'.$query_video_book_addon.
							' FROM '		. DB_TABLE_PREFIX_ACTION_TYPE . SENTENCE_TABLE	. ' AS s'.
							' INNER JOIN '	. DB_TABLE_PREFIX_ACTION_TYPE . PARAGRAPH_TABLE	. ' AS p ON s.paragraph_id = p.id'.
							' INNER JOIN '	. DB_TABLE_PREFIX_ACTION_TYPE . CHAPTER_TABLE	. ' AS c ON p.chapter_id = c.id'.
							' WHERE c.book_id=? and s.book_sentence_id=?';
				$query_bind_values = array($do['searchResult_book_id'], $do['searchResult_sentence_id']);
				$res = $dataCenter->SQLite_DB->prepare($query);
				$res->execute($query_bind_values);
				if ($row = $res->fetch(PDO::FETCH_NUM)) {
					$do['searchResult_chapter_id'] 				= $row[0];
					$do['searchResult_paragraph_id']			= $row[1];
					if (IS_ACTION_VIDEO) {
						$do['searchResult_video_books_chapter_paragraph_num'] = $row[2];
					}
				}
			}
		}
	} // end: load_book_chapter_paragraph_id()
	
	
	//------------------------------------------------------------------------------------------------------------------------
	// Highlight words pattern build from found keywords in query
	//------------------------------------------------------------------------------------------------------------------------
	function highlight_words_pattern_build($dataCenter, &$do) {
	    
		// mb_ereg_replace
		$ks_pre = '(^|[^'.REGEX__WORD_PART.'])';
		$ks_post = '([^'.REGEX__WORD_PART.']|$)';
//writeOut($highlight_words_TypeCategorised);
		
		//--------------------------------------
		// Highlight found words construction
		// words, expressions => type (ROOT, EXACT)
		//--------------------------------------
		foreach ($do['highlight_words_TypeCategorised'] as $keyword => $keyword_type) {
			
			switch ($keyword_type) {
				
				case EXACT_WORD_WORD_TYPE:
					
					//-----------------------------
					// keyword is expression here: e.g. "attract the positive", check for diacritics
					//-----------------------------
					if (strpos($keyword, ' ') !== FALSE) {
						
						//---------------------------------------------------
						// Plain English form (canonical form)
						//---------------------------------------------------
						if (!IS__TEXT_TYPE__DIACRITICS) {
							
							$do['highlight_words_search'.$do['db_table_prefix']][]	= $ks_pre.'('.$keyword.')'.$ks_post;
							$do['highlight_words_replace'.$do['db_table_prefix']][] = '\\1<span class="highlight">\\2</span>\\3';
							$do['highlight_words'.$do['db_table_prefix']][]			= $keyword;
							//$storedWords_lowerCase[$keyword] = 1;
						
						//---------------------------------------------------
						// Diacritic form
						//---------------------------------------------------
						} else {//if (IS__TEXT_TYPE__DIACRITICS) {
							
							$keyword_parts = explode(' ', $keyword);
							$keyword_diacritic = array();
							$keyword_diacritic_lowercase = $keyword_diacritic_uppercase = array();
							$n = 0;
							
							//..each part of expression check against diacritic words
							foreach ($keyword_parts as $keyword_part) {
								
								$query = 'SELECT lowercase_diacritics, uppercase_diacritics'.
												' FROM '.WORD_DIACRITICS_TABLE.
												' WHERE lowercase_plain_by_rules=?';
												//' WHERE lowercase_plain_by_rules=? OR lowercase_plain_by_substitution=?';
								//$query_bind_values = array($keyword_part, $keyword_part);
								$query_bind_values = array($keyword_part);
								$res = $dataCenter->SQLite_DB->prepare($query);
								$res->execute($query_bind_values);

								$exists_diacritic_word = false;
								while ($row = $res->fetch(PDO::FETCH_NUM)) {
									
									$keyword_diacritic_lowercase[$n][] = $row[0];
									$keyword_diacritic_uppercase[$n][] = $row[1];
									
									$exists_diacritic_word = true;
								}
								if (!$exists_diacritic_word) {
									$keyword_diacritic_lowercase[$n][] = $keyword_part;
									$keyword_diacritic_uppercase[$n][] = $keyword_part;
								}
								++$n;
							}
							
							// construct expressions
							$data = array($keyword_diacritic_lowercase, $keyword_diacritic_uppercase);

							foreach ($data as $current_data) {

								$matchA = array();

								// build combinations
								$pos_sub_sub = $part_sub_max = array();
								$part_max = count($current_data) - 1;
								foreach ($current_data as $key => $data_row) {
									$part_sub_max[$key] = count($data_row) - 1;
									$pos_sub_sub[$key] = 0;
								}
								// Found base
								$matchA[] = $pos_sub_sub;
								$pos = $part_max;

								$need_1 = TRUE;
								while ($need_1) {
									if ($pos_sub_sub[$pos] < $part_sub_max[$pos]) {
										++$pos_sub_sub[$pos];
										// Found
										$matchA[] = $pos_sub_sub;
									} else {
										$pos_sub_sub[$pos] = 0;
										--$pos;
										$need_2 = TRUE;
										while ($need_2) {
											if ($pos_sub_sub[$pos] < $part_sub_max[$pos]) {
												++$pos_sub_sub[$pos];
												$need_2 = FALSE;
												// Found
												$matchA[] = $pos_sub_sub;
												$pos = $part_max;
											} else {
												$pos_sub_sub[$pos] = 0;
												--$pos;
												// End
												if ($pos <= 0) {
													$need_1 = $need_2 = FALSE;
												}
											}
										}
									}
								}

								// expressions from combinations
								foreach ($matchA as $current_match) {
									$result = array();
									foreach ($current_match as $key => $value) {
										$result[] = $current_data[$key][$value];
									}
									$new_keyword = implode(' ', $result);
									if (!isset($keyword_diacritic[$new_keyword])) {
										$keyword_diacritic[$new_keyword] = $new_keyword;
										$do['highlight_words'.$do['db_table_prefix']][] = $new_keyword;
									}
								}
							} // foreach ($data as $current_data)
								
							$new_keyword = implode('|', $keyword_diacritic);
							$do['highlight_words_search'.$do['db_table_prefix']][]	= $ks_pre.'('.$new_keyword.')'.$ks_post;
							$do['highlight_words_replace'.$do['db_table_prefix']][] = '\\1<span class="highlight">\\2</span>\\3';
						}
						
						
					//-----------------------------
					// ..keyword is one word
					//-----------------------------
					} else {
						
						//---------------------------------------------------
						// Plain English form (canonical form)
						//---------------------------------------------------
						if (!IS__TEXT_TYPE__DIACRITICS) {
							
							$do['highlight_words_search'.$do['db_table_prefix']][]	= $ks_pre.'('.$keyword.')'.$ks_post;
							$do['highlight_words_replace'.$do['db_table_prefix']][] = '\\1<span class="highlight">\\2</span>\\3';
							$do['highlight_words'.$do['db_table_prefix']][]			= $keyword;
							//$storedWords_lowerCase[$keyword] = 1;
						
						//---------------------------------------------------
						// Diacritic form
						//---------------------------------------------------
						} else {//if (IS__TEXT_TYPE__DIACRITICS) {
							
							$keyword_diacritic = array();
							
							$query = 'SELECT lowercase_diacritics, uppercase_diacritics'.
											' FROM '.WORD_DIACRITICS_TABLE.
											' WHERE lowercase_plain_by_rules=?';
											//' WHERE lowercase_plain_by_rules=? OR lowercase_plain_by_substitution=?';
							//$query_bind_values = array($keyword, $keyword);
							$query_bind_values = array($keyword);
							$res = $dataCenter->SQLite_DB->prepare($query);
							$res->execute($query_bind_values);
							while ($row = $res->fetch(PDO::FETCH_NUM)) {
								
								if (!isset($keyword_diacritic[$row[0]])) {
									$keyword_diacritic[$row[0]] = $row[0];
									$do['highlight_words'.$do['db_table_prefix']][] = $row[0];
								}
								if (!isset($keyword_diacritic[$row[1]])) {
									$keyword_diacritic[$row[1]] = $row[1];
									$do['highlight_words'.$do['db_table_prefix']][] = $row[1];
								}
							}
							if (empty($keyword_diacritic)) {
								$keyword_diacritic[] = $keyword;
								$do['highlight_words'.$do['db_table_prefix']][] = $keyword;
							}
							
							$new_keyword = implode('|', $keyword_diacritic);
							$do['highlight_words_search'.$do['db_table_prefix']][]	= $ks_pre.'('.$new_keyword.')'.$ks_post;
							$do['highlight_words_replace'.$do['db_table_prefix']][] = '\\1<span class="highlight">\\2</span>\\3';
						}
					}
					break;
					
				case ROOT_WORD_WORD_TYPE:
					
					$keyword_list = array();
					
					//---------------------------------------------------
					// Plain English form (canonical form)
					//---------------------------------------------------
					$keyword_list[] = $keyword;
					$do['highlight_words'.$do['db_table_prefix']][] = $keyword;
					
					//---------------------------------------------------
					// Plain English forms (from canonical form)
					//  some diacritic can be here
					//---------------------------------------------------
					// Get all the inflected forms of this word
					if ($do['selected_language_id'] !== NULL) {
						$query_where_addon_language			= ' AND src.language_id = ? AND dest.language_id = ?';
						$query_where_addon_language_books	= '';
						$query_bind_values					= array($keyword, $do['selected_language_id'], $do['selected_language_id']);
						$query_bind_values_books			= array($keyword);
					} else {
						$query_where_addon_language			= '';
						$query_where_addon_language_books	= '';
						$query_bind_values					= array($keyword);
						$query_bind_values_books			= array($keyword);
					}

					//$query = 'SELECT dest_2.word'.
					$query = 'SELECT dest.word'.
								' FROM '		. $do['db_table_prefix'] . WORD_BOOKS_LANGUAGES_TABLE . ' AS src'.
								' INNER JOIN '	. $do['db_table_prefix'] . WORD_BOOKS_LANGUAGES_TABLE . ' AS dest ON src.root_word_id=dest.root_word_id'.
								//' INNER JOIN '	. $do['db_table_prefix'] . WORD_BOOKS_LANGUAGES_TABLE . ' AS dest_2 ON dest.canonical_word_id=dest_2.word_id'.
								' WHERE src.word=?'.
								($do['is_books'] ? $query_where_addon_language_books : $query_where_addon_language);

					$res = $dataCenter->SQLite_DB->prepare($query);
					$res->execute($do['is_books'] ? $query_bind_values_books : $query_bind_values);
					while ($row = $res->fetch(PDO::FETCH_NUM)) {

						if (!isset($keyword_list[$row[0]])) {
							$keyword_list[$row[0]] = $row[0];
							$do['highlight_words'.$do['db_table_prefix']][] = $row[0];
						}
					}
					
					
					//---------------------------------------------------
					// Diacritic words
					//---------------------------------------------------
					if (IS__TEXT_TYPE__DIACRITICS) {
						
						// Get all the inflected forms of this word
						$query = 'SELECT dest.lowercase_diacritics, dest.uppercase_diacritics'.
									' FROM '		. WORD_DIACRITICS_TABLE . ' AS src'.
									' INNER JOIN '	. WORD_DIACRITICS_TABLE . ' AS dest ON src.root_word_id=dest.root_word_id'.
									' WHERE src.lowercase_plain_by_rules=?';
						//$query_bind_values = array($keyword, $keyword, $keyword, $keyword);
						$query_bind_values = array($keyword);
						$res = $dataCenter->SQLite_DB->prepare($query);
						$res->execute($query_bind_values);
						while ($row = $res->fetch(PDO::FETCH_NUM)) {
							
							if (!isset($keyword_list[$row[0]])) {
								$keyword_list[$row[0]] = $row[0];
								$do['highlight_words'.$do['db_table_prefix']][] = $row[0];
							}
							if (!isset($keyword_list[$row[1]])) {
								$keyword_list[$row[1]] = $row[1];
								$do['highlight_words'.$do['db_table_prefix']][] = $row[1];
							}
						}
					}
					
					$new_keyword = implode('|', $keyword_list);
					$do['highlight_words_search'.$do['db_table_prefix']][]	= $ks_pre.'('.$new_keyword.')'.$ks_post;
					$do['highlight_words_replace'.$do['db_table_prefix']][] = '\\1<span class="highlight">\\2</span>\\3';
					break;
			} // switch ($keyword_type)
		} // foreach ($do['highlight_words']_TypeCategorised as $keyword => $keyword_type)
	} // function highlight_words_pattern_build($dataCenter, &$do, $highlight_words_TypeCategorised)
	
	
	//------------------------------------------------------------------------------------------------------------------------
	// Save values from books results
	// Restore saved values from books results for book text display
	//------------------------------------------------------------------------------------------------------------------------
	// Save values from books results
	function save_resultList_data(&$do) {
		$do['saved_values']['searchResult_book_id']			= $do['searchResult_book_id'];
		$do['saved_values']['searchResult_chapter_id']		= $do['searchResult_chapter_id'];
		$do['saved_values']['searchResult_paragraph_id']	= $do['searchResult_paragraph_id'];
	}
	// Restore saved values from books results for book text display
	function restore_resultList_data(&$do) {
		$do['searchResult_book_id'] 		= $do['saved_values']['searchResult_book_id'];
		$do['searchResult_chapter_id'] 		= $do['saved_values']['searchResult_chapter_id'];
		$do['searchResult_paragraph_id'] 	= $do['saved_values']['searchResult_paragraph_id'];
	}
	
	
	//------------------------------------------------------------------------------------------------------------------------
	// URL encode search text
	//------------------------------------------------------------------------------------------------------------------------
	function url_encode_SearchText($s) {
		$s = str_replace(array('_', ' '), '+', $s);
		$st_parts = explode('+', $s);
		foreach ($st_parts as &$st_part) {
			$st_part = urlencode($st_part);
		}
		return implode('+', $st_parts);
	}
	
	
	//------------------------------------------------------------------------------------------------------------------------
	// Clean and store search text
	//------------------------------------------------------------------------------------------------------------------------
	function clean_store_SearchText($dataCenter, &$do, $need_clean_text) {
		
		$s = str_replace('&quot;', '"', $do['search_text']);
		
		if (!empty($s)) {
			if ($need_clean_text) {
				// Full clean
				$ret = clean_SearchText($dataCenter, $do, ' '.mb_strtolower($s).' ');
//writeOut($ret);
			} else {
				$s = mb_strtolower($s);
				
				// Handle ["] for exact expressions
				$qnum = substr_count($s, '"');
				if ($qnum > 0) {
					if ($qnum == 1) {
						$s = str_replace('"', ' ', $s);
					} else {
						// find pairs
						$qpairnum = floor($qnum / 2);
						$curr_pos = 0;
						for ($i=1; $i<=$qpairnum; $i++) {
							$pos1 = strpos($s, '"', $curr_pos);
							$pos2 = strpos($s, '"', $pos1+1);
							for ($j=$pos1; $j<=$pos2; $j++) {
								if ($s[$j] == ' ') {
									$s[$j] = '_';
								}
							}
							$curr_pos = $pos2 + 1;
						}
					}
				}
				
				$ret = array('search_text' => $s, /*'search_text_standard' => $s,*/ 'search_text_canonicalA' => explode(' ', $s));
			}
			if (!empty($ret['search_text'])) {
				$do['search_text'] 					= $ret['search_text'];
				//$do['search_text_standard'] 		= $ret['search_text_standard'];
				$do['search_text_canonicalA']		= $ret['search_text_canonicalA'];
				$do['url_encoded_do_search_text'] 	= url_encode_SearchText($ret['search_text']);
			}
		}
		return true;
	}
	
	
	//------------------------------------------------------------------------------------------------------------------------
	// Clean search text
	// 	contains only [a-z"() ] already
	//------------------------------------------------------------------------------------------------------------------------
	function clean_SearchText($dataCenter, $do, $s) {
	    
		// More than 1 ["] joined
		$ret = mb_ereg_replace('""+', '', $s);
		if ($ret != NULL) { $s = $ret; }
		
		// Handle ["] for exact expressions
		$qnum = substr_count($s, '"');
		if ($qnum > 0) {
			if ($qnum == 1) {
				$s = str_replace('"', ' ', $s);
			} else {
				// find pairs
				$qpairnum = floor($qnum / 2);
				$curr_pos = 0;
				for ($i=1; $i<=$qpairnum; $i++) {
					$pos1 = strpos($s, '"', $curr_pos);
					$pos2 = strpos($s, '"', $pos1+1);
					for ($j=$pos1; $j<=$pos2; $j++) {
						if ($s[$j] == ' ') {
							$s[$j] = '_';
						}
					}
					$curr_pos = $pos2 + 1;
				}
			}
		}
		
		//------------------------------------------------------------------------------
		// Brackets ()
		//------------------------------------------------------------------------------
		// No more closing brackets before enough opening ones
		$lastPos = strlen($s);
		$bracketState = 0;
		for ($i=0; $i<$lastPos; $i++) {
			if ($s[$i] == '(') { ++$bracketState; }
			else if ($s[$i] == ')') { --$bracketState; }
			if ($bracketState < 0) { $s[$i] = ' '; ++$bracketState; }
		}
		
		// Check brackets [()], make it even in numbers
		$opening_bracket_num = substr_count($s, "(");
		$closing_bracket_num = substr_count($s, ")");
		if ($opening_bracket_num != $closing_bracket_num) {
			if ($opening_bracket_num > $closing_bracket_num) {
				$s .= str_repeat(")", $opening_bracket_num-$closing_bracket_num);
			} else {
				$s = str_repeat("(", $closing_bracket_num-$opening_bracket_num) . $s;
			}
		}
		
		// Separate [()] from text so they can be used
		$dividers 			= array("(", ")");
		$dividers_replace 	= array(" ( ", " ) ");
		$s = str_replace($dividers, $dividers_replace, $s);
		
		//------------------------------------------------------------------------------
		// Remove words that are not indexed, except [and], [or] => logical connection
		//------------------------------------------------------------------------------
		/*
		// split into words
		$s_new = array();
		$ret_num = preg_match_all('/['.SEARCH_QUERY_PROCESSING_ALLOWED_CHARS.']+/', $s, $words);
		$ret_num = (($ret_num==false) ? 0 : $ret_num);
		if ($ret_num) {
			foreach ($words[0] as $word) {
				$s_new[] = $word;
			}
		}
		$s = implode(' ', $s_new);
		*/
		
		// More than 1 [ ] joined (excess whitespace)
		//$ret = mb_ereg_replace('\s\s+', ' ', $s);
		//if ($ret != NULL) { $s = $ret; }
		
		//------------------------------------------------------------------------------
		// Remove unneccessary parts: [()] => [], [and|or and|or ...] => [and|or], [( and|or] => [(], [and|or )] => [)]
		// Needs to be cyclic as after some part's removal other forms may appear.
		//------------------------------------------------------------------------------
		$needReplacements = true;
		while ($needReplacements) {
			$patterns 		= array('/\(\s*\)/', '/\s+(and|or)\s+((and|or)\s+)+/', '/\(\s+(and|or)/', '/(and|or)\s+\)/');
			$replacements 	= array('', ' \1 ', '(', ')');
			$ret = preg_replace($patterns, $replacements, $s, -1, $needReplacements);
			if ($ret==null) {
				$needReplacements = false;
			} else {
				$s = $ret;
			}
		}
		
		// More than 1 [_] joined
		$ret = mb_ereg_replace('__+', '_', $s);
		if ($ret != NULL) { $s = $ret; }
		
		// More than 1 [ ] joined (excess whitespace)
		$ret = mb_ereg_replace('\s\s+', ' ', $s);
		if ($ret != NULL) { $s = $ret; }
		
		$s = trim($s);
		
		//------------------------------------------------------------------
		//	Make search_text in standard words:
		//	1. if word is in root form then find root word
		//	1. replace substituted diacritic forms into rules form
		//	2. use this form in DB
		//		e.g.
		//		'Siva' -> 'Shiva'
		//------------------------------------------------------------------
		$s_new = array();
		$ret_num = preg_match_all(REGEX__WORD, $s, $words);
		$ret_num = (($ret_num==false) ? 0 : $ret_num);
		if ($ret_num) {
			
			foreach ($words[0] as $word) {
/*		
		
		mb_ereg_search_init($s, REGEX__WORD_MB);
		$words = mb_ereg_search();
		if ($words) {
			$words = mb_ereg_search_getregs(); // get first result
			do {
				$word = $words[0];
*/				
				
				
				// root form => find root word
				if (strpos($word, '"') === FALSE) {
					
					// get the root word of the word
					if ($do['selected_language_id'] !== NULL) {
						$query_where_addon_language = ' AND src.language_id = ? AND dest.language_id = ? AND dest_2.language_id = ?';
						$query_bind_values = array($word, $do['selected_language_id'], $do['selected_language_id'], $do['selected_language_id']);
					} else {
						$query_where_addon_language = '';
						$query_bind_values = array($word);
					}
					
					$query = 'SELECT dest_2.word'.
								' FROM '		. $do['db_table_prefix'] . WORD_BOOKS_LANGUAGES_TABLE . ' AS src'.
								' INNER JOIN '	. $do['db_table_prefix'] . WORD_BOOKS_LANGUAGES_TABLE . ' AS dest ON src.root_word_id=dest.word_id'.
								' INNER JOIN '	. $do['db_table_prefix'] . WORD_BOOKS_LANGUAGES_TABLE . ' AS dest_2 ON dest.canonical_word_id=dest_2.word_id'.
								' WHERE src.word=?'.
								$query_where_addon_language;
					
					$res = $dataCenter->SQLite_DB->prepare($query);
					$res->execute($query_bind_values);
					if ($row = $res->fetch(PDO::FETCH_NUM)) {
						$word = $row[0];
					}
					$s_new[] = $word;
					
					/*
					// replace substituted diacritic forms into rules form
					$query = 'SELECT lowercase_plain_by_rules '.
									' FROM '.WORD_DIACRITICS_TABLE.
									' WHERE lowercase_plain_by_substitution=? OR lowercase_diacritics=?';
					$query_bind_values = array($word, $word);
					$res = $dataCenter->SQLite_DB->prepare($query);
					$res->execute($query_bind_values);
					if ($row = $res->fetch(PDO::FETCH_NUM)) {
						$s_new[] = $row[0];
					} else {
						$s_new[] = $word;
					}
					*/
					
				//..exact form
				} else {
					
					// replace substituted diacritic forms into rules form
					$word_no_quotes		= str_replace('"', '', $word);
					$word_no_quotesA	= explode('_', $word_no_quotes);
					$s_new_exact		= array();
					foreach ($word_no_quotesA as $word_no_quotes) {
						
						$keyword_diacritic = array();
						
						$query = 'SELECT lowercase_plain_by_rules '.
										' FROM '.WORD_DIACRITICS_TABLE.
										' WHERE lowercase_plain_by_substitution=? OR lowercase_diacritics=?';
						$query_bind_values = array($word_no_quotes, $word_no_quotes);
						
						$res = $dataCenter->SQLite_DB->prepare($query);
						$res->execute($query_bind_values);
						while ($row = $res->fetch(PDO::FETCH_NUM)) {
							$keyword_diacritic[$row[0]] = $row[0];
						}
						
						if (empty($keyword_diacritic)) {
							$keyword_diacritic[] = $word_no_quotes;
							
						} else if (count($keyword_diacritic) > 1) {
							
							// check for preferred canonical form
							// 
							// get the root word of the word
							if ($do['selected_language_id'] !== NULL) {
								$query_where_addon_language = ' AND src.language_id = ? AND dest.language_id = ? AND dest_2.language_id = ?';
								$query_bind_values = array($word_no_quotes, $do['selected_language_id'], $do['selected_language_id'], $do['selected_language_id']);
							} else {
								$query_where_addon_language = '';
								$query_bind_values = array($word_no_quotes);
							}

							$query = 'SELECT dest_2.word'.
										' FROM '		. $do['db_table_prefix'] . WORD_BOOKS_LANGUAGES_TABLE . ' AS src'.
										' INNER JOIN '	. $do['db_table_prefix'] . WORD_BOOKS_LANGUAGES_TABLE . ' AS dest ON src.root_word_id=dest.word_id'.
										' INNER JOIN '	. $do['db_table_prefix'] . WORD_BOOKS_LANGUAGES_TABLE . ' AS dest_2 ON dest.canonical_word_id=dest_2.word_id'.
										' WHERE src.word=?'.
										$query_where_addon_language;

							$res = $dataCenter->SQLite_DB->prepare($query);
							$res->execute($query_bind_values);
							if ($row = $res->fetch(PDO::FETCH_NUM)) {
								$word = $row[0];
							} else {
								$word = $word_no_quotes;
							}
							
							// if word found both places => use that only
							if (isset($keyword_diacritic[$word])) {
								$keyword_diacritic = array($word => $word);
							}
						}
						$s_new_exact[] = array_shift($keyword_diacritic);
					}
					$s_new[] = '"'.implode('_', $s_new_exact).'"';
				}
			//} while($words = mb_ereg_search_regs()); // get next result
			}
		}
		
		return array(	'search_text'				=> $s,
						//'search_text_standard'		=> implode(' ', $s_new),
						'search_text_canonicalA'	=> $s_new);
	} // function clean_SearchText($s)
	
	
	//------------------------------------------------------------------------------------------------------------------------
	// Convert to Polish-form (Lengyel-formára hozás)
	//	return: [polish_form, cleaned search text]
	//------------------------------------------------------------------------------------------------------------------------
	//function convertToPolishForm($dataCenter, $s) {
	function convertToPolishForm($dataCenter, $search_text_canonicalA) {
		
		$stack = array();
		$polishForm = array();
		$last_item = $item_before_last_item = '';
		$search_text = array();
		$search_textTopPos = -1;
		$opening_bracket_index = -100;
		$highlight_words = array();
		
		// Split into words
		//$ret_num = preg_match_all(REGEX__WORD, $s, $words, PREG_OFFSET_CAPTURE);
		
//writeOut('convertToPolishForm');
//writeOut($s);
//writeOut($words);
		
		//$ret_num = (($ret_num==false) ? 0 : $ret_num);
		//if ($ret_num) {
		if (!empty($search_text_canonicalA)) {
			
			// Convert to Polish-form (Lengyel-forma)
			$stackTopPos = -1;
			//while (list($current_index, $val) = each($words[0])) {
			foreach ($search_text_canonicalA as $current_index => $word) {
				
				// get word
				//$word = $val[0];
				
				// opening
				if ($word == '(') {
					
					// last item: [operand, )] => put an [and] operator between them
					if (($last_item == 'operand') or ($last_item == ')')) {
						
						// operator (same as above)
						while (!empty($stack) and ($stack[$stackTopPos] != '(') and 
								($dataCenter->operator_and_priority[$stack[$stackTopPos]] >= 
									$dataCenter->operator_and_priority[$dataCenter->operator_inject])) {
							$y = array_pop($stack);
							$polishForm[] = $y;
							--$stackTopPos;
						}
						
						// add operator between them
						$stack[] = $dataCenter->operator_inject;
						++$stackTopPos;
						$search_text[] = $dataCenter->operator_inject;
						++$search_textTopPos;
					}
					
					// deal with [(]
					$stack[] = $word;
					++$stackTopPos;
					$search_text[] = $word;
					++$search_textTopPos;
					$opening_bracket_index = $search_textTopPos;
					$item_before_last_item = $last_item;
					$last_item = $word;
					
				// operator
				} else if (isset($dataCenter->operator_and_priority[$word])) {
				    
					// last item: [operator, (] => neglect this operator
					// after [operand, )] => OK
					if (($last_item == 'operand') or ($last_item == ')')) {
						
						// deal with [operator]
						while (!empty($stack) and ($stack[$stackTopPos] != '(') and 
								($dataCenter->operator_and_priority[$stack[$stackTopPos]] >= 
									$dataCenter->operator_and_priority[$word])) {
							$y = array_pop($stack);
							$polishForm[] = $y;
							--$stackTopPos;
						}
						
						$stack[] = $word;
						++$stackTopPos;
						$search_text[] = $word;
						++$search_textTopPos;
						$item_before_last_item = $last_item;
						$last_item = 'operator';
					}
					
				// closing
				} else if ($word == ')') {
					
					// last item: [(] => remove [(] and neglect this [)], because it is empty
					if ($last_item == '(') {
						
						$y = array_pop($stack);
						--$stackTopPos;
						array_pop($search_text);
						--$search_textTopPos;
						$last_item = $item_before_last_item;
						
					} else {
						
						// last item: [operator] => remove [operator] because there is no other operand
						if ($last_item == 'operator') {
							
							$y = array_pop($stack);
							--$stackTopPos;
							array_pop($search_text);
							--$search_textTopPos;
						}
						
						// For search text: [( word )] => [word]
						if ($search_textTopPos - $opening_bracket_index == 1) {
							array_splice($search_text, $opening_bracket_index, 1);
							--$search_textTopPos;
						} else {
							$search_text[] = $word;
							++$search_textTopPos;
						}
						
						// deal with [)]
						$y = array_pop($stack);
						--$stackTopPos;
						while ($y != '(') {
							$polishForm[] = $y;
							$y = array_pop($stack);
							--$stackTopPos;
						}
						$item_before_last_item = $last_item;
						$last_item = $word;
					}
					
				// operand
				} else {
					
					// last item: [operand, )] => put an [and] operator between them
					if (($last_item == 'operand') or ($last_item == ')')) {
						
						// operator (same as above)
						while (!empty($stack) and ($stack[$stackTopPos] != '(') and 
								($dataCenter->operator_and_priority[$stack[$stackTopPos]] >= 
									$dataCenter->operator_and_priority[$dataCenter->operator_inject])) {
							$y = array_pop($stack);
							$polishForm[] = $y;
							--$stackTopPos;
						}
						
						$stack[] = $dataCenter->operator_inject;
						++$stackTopPos;
						$search_text[] = $dataCenter->operator_inject;
						++$search_textTopPos;
					}
					
					// deal with [operand]
					$polishForm[] = $word;
					$search_text[] = $word;
					++$search_textTopPos;
					$item_before_last_item = $last_item;
					$last_item = 'operand';
					
					//--------------------------------------------------------------------
					// Store operand for highlighting
					//--------------------------------------------------------------------
					//..exact type (word or expression)
					if (strpos($word, '"') !== false) {
						// for expressions ("soul_is" => soul is), for words ("soul" => soul)
						$word = str_replace(array('"', '_'), array('', ' '), $word);
						$highlight_words[$word] = EXACT_WORD_WORD_TYPE;
					//..root type (word)
					} else {
						$highlight_words[$word] = ROOT_WORD_WORD_TYPE;
					}
				}
//			} while($words = mb_ereg_search_regs()); // get next result
			}
		} else {
			$polishForm = '';
		}
		
		// Remove beginning [(] and end [)]
		if (($search_text[0] == '(') and $search_text[count($search_text)-1] == ')') {
			array_shift($search_text);
			array_pop($search_text);
		}
		
		// Clean [search_text] from the extra [and]s
		$search_text = str_replace(' and ', ' ', implode(' ', $search_text));
		
		return array(	'polish_form' 		=> $polishForm,
						'search_text' 		=> $search_text,
						'highlight_words' 	=> $highlight_words);
	} // function convertToPolishForm($s)
	
	
	//------------------------------------------------------------------------------------------------------------------------
	// Evaluate Polish-form
	//------------------------------------------------------------------------------------------------------------------------
	function evaluatePolishForm($dataCenter, &$do, $polishForm) {
		
		//include_once (FS_INCLUDE_DIR.'/porter_stemmer.php');
		//$porterStemmer = new Porter2Stemmer(); // eliminate suffixes
		
		$stack = array();
		$stackTopPos = -1;
		
		$pf_lastIndex = count($polishForm)-1;
		$pf_currIndex = 0;
		
		while ($pf_currIndex <= $pf_lastIndex) {
			
			$x = $polishForm[$pf_currIndex];
			++$pf_currIndex;
			
			// operator
			if (isset($dataCenter->operator_and_priority[$x])) {
				
				$tt = false;
				
				$word_a_data = array_pop($stack);
				$word_b_data = array_pop($stack);
				
				if (!is_array($word_a_data)) { $word_a_data = getWordRelevantParagraphSentenceData($dataCenter, /*$porterStemmer,*/ $do, $word_a_data); }
				if (!is_array($word_b_data)) { $word_b_data = getWordRelevantParagraphSentenceData($dataCenter, /*$porterStemmer,*/ $do, $word_b_data); }
				
				// Process (a operator b)
				$searchResult = array();
				
				switch ($x) {
					case 'and':
						foreach ($word_a_data as $ps_id => $data) {
						    if (isset($word_b_data[$ps_id])) {
								$searchResult[$ps_id] = array(
									'book_id' 			=> $data['book_id'],
									'relevance' 		=> $data['relevance'] + $word_b_data[$ps_id]['relevance']
								);
							}
						}
						break;
						
					case 'or':
						$searchResult = $word_a_data;
						foreach ($word_b_data as $ps_id => $data) {
						    if (!isset($word_a_data[$ps_id])) {
								$searchResult[$ps_id] = $data;
							} else {
								$searchResult[$ps_id]['relevance'] += $data['relevance'];
							}
						}
						break;
				}
				
				$stack[] = $searchResult;
				++$stackTopPos;
				
			// operand
			} else {
				$stack[] = $x;
				++$stackTopPos;
			}
		} // while
		
		$word_a_data = array_pop($stack);
		if (!is_array($word_a_data)) { $word_a_data = getWordRelevantParagraphSentenceData($dataCenter, /*$porterStemmer,*/ $do, $word_a_data); }
		
		
		//-----------------------------------------------------------------------------------
		// Sort final result set
		//-----------------------------------------------------------------------------------
		$do_sort = (IS__SITE_SEARCH_TYPE__RELEVANCE ? 'relevance' : 'linear');
		$word_a_data = sortResultSet($do_sort, $word_a_data);
		
		
		//-----------------------------------------------------------------------------------
		// Total result num
		//-----------------------------------------------------------------------------------
		$do['total_result_ps_num'] = count($word_a_data);
		
		
		//-----------------------------------------------------------------------------------
		// Trim to required amount of result
		//-----------------------------------------------------------------------------------
		// Load next [n] item
		if ($do['searchResultList_loadNext']) {
			$word_a_data = array_slice($word_a_data, $do['searchResultList_loaded_num'], SEARCH_RESULT_LIST__ITEM_NUM__LOAD_NEXT);
			// result num loaded now
			$do['result_ps_loaded_num'] = count($word_a_data);
			
		// Load all the rest
		} if ($do['searchResultList_loadAll']) {
			$word_a_data = array_slice($word_a_data, $do['searchResultList_loaded_num']);
			$do['result_ps_loaded_num'] = $do['total_result_ps_num'] - $do['searchResultList_loaded_num'];
			
		// Load standard way
		} else {
			$word_a_data = array_slice($word_a_data, 0, SEARCH_RESULT_LIST__ITEM_NUM__LOAD_NEXT);
			$do['result_ps_loaded_num'] = count($word_a_data);
		}
		// total result num loaded now and before
		$do['total_result_ps_loaded_num'] = $do['searchResultList_loaded_num'] + $do['result_ps_loaded_num'];
		
		return $word_a_data;
	} // function evaluatePolishForm($polishForm)
	
	
	//------------------------------------------------------------------------------------------------------------------------
	// Get word's relevant paragraph/sentence data
	//	$word : accept		-> search for all inflected form of [accept]
	//	$word : "accept"	-> search for only exact form [accept]
	//------------------------------------------------------------------------------------------------------------------------
	function getWordRelevantParagraphSentenceData($dataCenter, /*&$porterStemmer,*/ &$do, $word) {
		
		$ret_word_relevant_ps_data = array();
		$isWord 		= true;
		$wordType		= 'root';
		$isExpression 	= false;
		$word_id 		= null;
		
		// "accept" => exact word (expression) search
		if ((substr($word, 0, 1) == '"') and (substr($word, -1, 1) == '"')) {
			
			// get the word
			$word = substr($word, 1, -1);
			
			// contains [_] : more than 1 word
			if (strpos($word, '_') !== false) {
				
				$isWord 		= false;
				$isExpression 	= true;
				$search_text 	= str_replace('_', ' ', $word);
				
			//.. 1 word
			} else {
				$wordType = 'exact';
				$search_text = $word;
			}
		} else {
			$search_text = $word;
		}
		
		//----------------------------------------
		// Get word's/expression's relevant paragraphs
		//----------------------------------------
		if ($isWord) {
			$ret_word_relevant_ps_data = getSearchResult($dataCenter, $do, array('search_text' => $search_text, 'mode' => 'word_'.$wordType));
			
		} else if ($isExpression) {
//$t1 = microtime(true);
			$ret_word_relevant_ps_data = getSearchResult($dataCenter, $do, array('search_text' => $search_text, 'mode' => 'expression'));
//writeOut('total expression: '.(microtime(true) - $t1));
		}
		
		return $ret_word_relevant_ps_data;
	} // function getWordRelevantParagraphSentenceData($word)
	
	
	//-----------------------------------------------------------------------------------
	// Sort result set
	//-----------------------------------------------------------------------------------
	function sortResultSet($do_sort, $data) {
		
		switch ($do_sort) {
			
			case 'linear' :
				
				//..sort in reading book order
				ksort($data);
				
				$ret 		= array();
				$rpd_num 	= 0;
				foreach ($data as $ps_id => $ps_data) {
					$ret[++$rpd_num] = array(
						PS_ID_TABLE_FIELD	=> $ps_id,
						'book_id' 			=> $ps_data['book_id']
					);
				}
				break;
				
			case 'relevance' :
				
				// ..sort out in relevance groups
				$tmp = array();
				foreach ($data as $ps_id => $ps_data) {
					$tmp[$ps_data['relevance']*10][$ps_id] = $ps_data;
				}
				
				// ..sort by relevance
				krsort($tmp);
				
				// ..sort pss in relevance groups according to reading book order
				$ret 		= array();
				$rpd_num 	= 0;
				foreach ($tmp as $rel_group => $curr_rel_group_data) {
					ksort($curr_rel_group_data);
					
					// ..make them in linear array
					foreach ($curr_rel_group_data as $ps_id => $ps_data) {
						$ret[++$rpd_num] = array(
							PS_ID_TABLE_FIELD	=> $ps_id,
							'book_id' 			=> $ps_data['book_id']
						);
					}
				}
				break;
		}
		return $ret;
	} // sortResultSet($do_sort, $data)
	
	
	
	//-----------------------------------------------------------------------------------------------------------------
	// Get search results from (word_books, word_books_data) tables
	//-----------------------------------------------------------------------------------------------------------------
	function getSearchResult($dataCenter, &$do, $search_textA) {
		
	//$t2 = microtime(true);
		
		$isModeWord = $isWordRoot = $isWordExact = $isModeWords = $isModeWords_Expression = $isModeExpression = false;
		
		$search_text 	= $search_textA['search_text'];
		$result_set 	= array();
		
		//-----------------------------------------------------------------------------------
		// Get result set + Book restrictions
		//-----------------------------------------------------------------------------------
		if ($do['selected_language_id'] !== NULL) {
			$query_where_addon_language_rs	= ' AND wb.language_id = ?';
			$query_join_addon_language_ps	= ' INNER JOIN ' . $do['db_table_prefix'].BOOK_TABLE . ' AS b ON c.book_id = b.book_id';
			$query_where_addon_language_ps	= ' AND b.language_id = ?';
		} else {
			$query_where_addon_language_rs	= '';
			$query_join_addon_language_ps	= '';
			$query_where_addon_language_ps	= '';
		}
		
		switch ($search_textA['mode']) {
			
			case 'word_root':
				
				$isModeWord = true; 
				$query = 'SELECT rsrw.result_set_by_'.PARAGRAPH_OR_SENTENCE.
							' FROM '		.$do['db_table_prefix'].WORD_BOOKS_LANGUAGES_TABLE	.' AS wb'.
							' INNER JOIN '	.$do['db_table_prefix'].RESULT_SET_ROOT_WORD_TABLE	.' AS rsrw USING(root_word_id)'.
							' WHERE wb.word=?'.
							$query_where_addon_language_rs;
				break;
									
			case 'word_exact':
				
				$isModeWord = true;
				$query = 'SELECT rscw.result_set_by_'.PARAGRAPH_OR_SENTENCE.
							' FROM '		.$do['db_table_prefix'].WORD_BOOKS_LANGUAGES_TABLE		.' AS wb'.
							' INNER JOIN '	.$do['db_table_prefix'].RESULT_SET_CANONICAL_WORD_TABLE	.' AS rscw USING(canonical_word_id)'.
							' WHERE wb.word=?'.
							$query_where_addon_language_rs;
				break;
									
			case 'words':
				
				$isModeWords_Expression = true;
				$search_text_wordsA 	= explode(' ', $search_text);
				$query = 'SELECT rsrw.result_set_by_'.PARAGRAPH_OR_SENTENCE.
							' FROM '		.$do['db_table_prefix'].WORD_BOOKS_LANGUAGES_TABLE	.' AS wb'.
							' INNER JOIN '	.$do['db_table_prefix'].RESULT_SET_ROOT_WORD_TABLE	.' AS rsrw USING(root_word_id)'.
							' WHERE wb.word IN ('.implode(',', array_fill(1, count($search_text_wordsA), '?')).')'.
							$query_where_addon_language_rs;
				break;
									
			case 'expression':
				
				$isModeWords_Expression = $isModeExpression = true;
				$search_text_wordsA 	= explode(' ', $search_text);
				$query = 'SELECT rscw.result_set_by_sentence, wb.word, wb.canonical_word_id'.
							' FROM '		.$do['db_table_prefix'].WORD_BOOKS_LANGUAGES_TABLE		.' AS wb'.
							' INNER JOIN '	.$do['db_table_prefix'].RESULT_SET_CANONICAL_WORD_TABLE	.' AS rscw USING(canonical_word_id)'.
							' WHERE wb.word IN ('.implode(',', array_fill(1, count($search_text_wordsA), '?')).')'.
							$query_where_addon_language_rs;
				break;
		}
		//writeOut($query);
		//writeOut($search_text_wordsA);
		
		//----------------------------------------------------------
		// word_root, word_exact
		//----------------------------------------------------------
		if ($isModeWord) {
			
			if ($do['selected_language_id'] !== NULL) {
				$query_bind_values = array($search_text, $do['selected_language_id']);
			} else {
				$query_bind_values = array($search_text);
			}
			
			$res = $dataCenter->SQLite_DB->prepare($query);
			$res->execute($query_bind_values);
			if ($row = $res->fetch(PDO::FETCH_NUM)) {
				
				$data = uncompress__word_book_ps_data($row[0]);
				//writeOut($data);
				
				// Book restrictions
				if ($do['search_restrictions_Partial']) {
					foreach ($data as $book_id => $book_data) {
						if (isset($do[$do['search_restrictions_IdList']][$book_id])) {
							foreach ($book_data as $ps_id => $relevance) {
								$result_set[$book_id][$ps_id] = $relevance;
							}
						}
					}
					
				// All books
				} else {
					// Language selected
					if ($do['selected_language_id'] !== NULL) {
						foreach ($data as $book_id => $book_data) {
							if (isset($do[$do['db_table_prefix'] . 'books'][$book_id])) {
								foreach ($book_data as $ps_id => $relevance) {
									$result_set[$book_id][$ps_id] = $relevance;
								}
							}
						}
					// no language selected
					} else {
						foreach ($data as $book_id => $book_data) {
							foreach ($book_data as $ps_id => $relevance) {
								$result_set[$book_id][$ps_id] = $relevance;
							}
						}
					}
				}
			}
			
		//----------------------------------------------------------
		// words, expression
		//----------------------------------------------------------
		} else {
			
			if ($do['selected_language_id'] !== NULL) {
				$query_bind_values = array_merge($search_text_wordsA, array($do['selected_language_id']));
			} else {
				$query_bind_values = $search_text_wordsA;
			}
			$res = $dataCenter->SQLite_DB->prepare($query);
			$res->execute($query_bind_values);
			$result_set_num = 1;
			$search_text_words__word__word_id = array();
			while ($row = $res->fetch(PDO::FETCH_NUM)) {
				
				$data = uncompress__word_book_ps_data($row[0]);
				
				// word, word_id
				if (isset($row[1])) {
					$search_text_words__word__word_id[$row[1]] = $row[2];
				}
				
				if ($result_set_num == 1) {
					
					// Book restrictions
					if ($do['search_restrictions_Partial']) {
						foreach ($data as $book_id => $book_data) {
							if (isset($do[$do['search_restrictions_IdList']][$book_id])) {
								foreach ($book_data as $ps_id => $relevance) {
									$result_set[$book_id][$ps_id] = $relevance;
								}
							}
						}
						
					// All books
					} else {
						// Language selected
						if ($do['selected_language_id'] !== NULL) {
							foreach ($data as $book_id => $book_data) {
								if (isset($do[$do['db_table_prefix'] . 'books'][$book_id])) {
									foreach ($book_data as $ps_id => $relevance) {
										$result_set[$book_id][$ps_id] = $relevance;
									}
								}
							}
						// no language selected
						} else {
							foreach ($data as $book_id => $book_data) {
								foreach ($book_data as $ps_id => $relevance) {
									$result_set[$book_id][$ps_id] = $relevance;
								}
							}
						}
					}
				} else {
					
					$mergeResult = array();
					
					// Book restrictions
					if ($do['search_restrictions_Partial']) {
						foreach ($data as $book_id => $book_data) {
							if (isset($do[$do['search_restrictions_IdList']][$book_id])) {
								foreach ($book_data as $ps_id => $relevance) {
									if (isset($result_set[$book_id][$ps_id])) {
										$mergeResult[$book_id][$ps_id] = $result_set[$book_id][$ps_id] + $relevance;
									}
								}
							}
						}
						
					// All books
					} else {
						// Language selected
						if ($do['selected_language_id'] !== NULL) {
							foreach ($data as $book_id => $book_data) {
								if (isset($do[$do['db_table_prefix'] . 'books'][$book_id])) {
									foreach ($book_data as $ps_id => $relevance) {
										if (isset($result_set[$book_id][$ps_id])) {
											$mergeResult[$book_id][$ps_id] = $result_set[$book_id][$ps_id] + $relevance;
										}
									}
								}
							}
						// no language selected
						} else {
							foreach ($data as $book_id => $book_data) {
								foreach ($book_data as $ps_id => $relevance) {
									if (isset($result_set[$book_id][$ps_id])) {
										$mergeResult[$book_id][$ps_id] = $result_set[$book_id][$ps_id] + $relevance;
									}
								}
							}
						}
					}
					$result_set = $mergeResult;
				}
				++$result_set_num;
			}
		}
		//writeOut($result_set);
		
		
		//----------------------------------------------------------
		// Get absolute p.id, s.id
		//----------------------------------------------------------
		$isSentence_process = ($isModeExpression || !IS__SITE_UNIT_TYPE__PARAGRAPH);
		
		// Make data groups for MySQL reading
		$ps_idA_offset 				= 0;
		$total_ps_id_num 			= 0;
		$total_ps_id_numA			= array();
		foreach ($result_set as $book_id => $book_id_data) {
			$total_ps_id_numA[$book_id] = count($book_id_data);
			$total_ps_id_num			+= $total_ps_id_numA[$book_id];
		}
		asort($total_ps_id_numA);
		
		$total_ps_idA 				= array();
		$c = 0;
		$curr_value_number_in_sql	= 0;
		$curr_book_data_offset		= 0;
		reset($total_ps_id_numA);
		$curr_book_id = key($total_ps_id_numA);
		$curr_book_id_data_num = current($total_ps_id_numA);
		next($total_ps_id_numA);
		
		while ($ps_idA_offset < $total_ps_id_num) {

			if ($curr_value_number_in_sql + $curr_book_id_data_num <= SELECT_IN_VALUE_NUMBER_THRESHOLD_IN_SQL) {

				$curr_book_data_offset				= 0;
				if (isset($total_ps_idA[$c][$curr_book_id])) {
					$total_ps_idA[$c][$curr_book_id] = array_merge( $total_ps_idA[$c][$curr_book_id], array_keys($result_set[$curr_book_id]) );
				} else {
					$total_ps_idA[$c][$curr_book_id] = array_keys($result_set[$curr_book_id]);
				}
				$curr_value_number_in_sql			+= $curr_book_id_data_num + 20;	// 20 for : "(c.book_id = ? AND p.book_paragraph_id IN "
				$ps_idA_offset						+= $curr_book_id_data_num;
				$curr_book_id = key($total_ps_id_numA);
				$curr_book_id_data_num = current($total_ps_id_numA);
				next($total_ps_id_numA);

			} else {
				++$c;
				//writeOut("\n".$curr_book_id.' '.$curr_book_id_data_num.' '.$curr_book_data_offset);
				//writeOut($result_set[$curr_book_id]);
				$pd_idA = array_slice($result_set[$curr_book_id], $curr_book_data_offset, SELECT_IN_VALUE_NUMBER_THRESHOLD_IN_SQL, TRUE);
				//writeOut($pd_idA);
				$total_ps_idA[$c][$curr_book_id]	= array_keys($pd_idA);
				$curr_value_number_in_sql			= count($pd_idA);
				$curr_book_data_offset				+= $curr_value_number_in_sql;
				$curr_book_id_data_num				-= $curr_value_number_in_sql;
				$ps_idA_offset						+= $curr_value_number_in_sql;

				if ($curr_book_id_data_num == 0) {
					$curr_book_data_offset = 0;
					$curr_book_id = key($total_ps_id_numA);
					$curr_book_id_data_num = current($total_ps_id_numA);
					next($total_ps_id_numA);
				}
			}
		}
		//writeOut($total_ps_idA);
		
		$paragraph_id__data = $sentence_id__data = $ps_id_data = array();
		
		
		//------------------------
		// Sentence process
		//------------------------
		if ($isSentence_process) {
			
			$search_text_words__word_id__word_num = array();
			if ($isModeExpression) {
				$search_text_words_num = count($search_text_wordsA);
				for ($word_num = 0; $word_num < $search_text_words_num; $word_num++) {
					$search_text_words__word_id__word_num[$search_text_words__word__word_id[$search_text_wordsA[$word_num]]] = $word_num;
				}
			}
			
			$query_bind_values_extra = array();
			if ($do['selected_language_id'] !== NULL) {
				if ($isModeExpression) {
					$query_bind_values_extra = array_merge(array($do['selected_language_id']), array_values($search_text_words__word__word_id));
				} else {
					$query_bind_values_extra = array($do['selected_language_id']);
				}
			} else {
				if ($isModeExpression) {
					$query_bind_values_extra = array_values($search_text_words__word__word_id);
				}
			}
			
			
			foreach ($total_ps_idA as $pd_idA) {
				
				$query_where_addon = array();
				$query_bind_values = array();
				foreach ($pd_idA as $book_id => $book_id_data) {
					$query_where_addon[] = '(c.book_id = ? AND s.book_sentence_id IN ('.implode(',', array_fill(1, count($book_id_data), '?')).'))';
					$query_bind_values = array_merge($query_bind_values, array($book_id), array_values($book_id_data));
				}
				$query_where_addon = '('.implode(' OR ', $query_where_addon).')';
				
				$query = 'SELECT s.id, c.book_id, p.id, s.book_sentence_id'.($isModeExpression ? ', wbo.canonical_word_id, wbo.order_num' : '').
							' FROM '.		$do['db_table_prefix'].WORD_BOOKS_OCCURRENCE_TABLE		.	' AS wbo'.
							' INNER JOIN '.	$do['db_table_prefix'].SENTENCE_TABLE					.	' AS s ON wbo.sentence_id = s.id'.
							' INNER JOIN '.	$do['db_table_prefix'].PARAGRAPH_TABLE					.	' AS p ON s.paragraph_id = p.id'.
							' INNER JOIN '.	$do['db_table_prefix'].CHAPTER_TABLE					.	' AS c ON p.chapter_id = c.id'.
							$query_join_addon_language_ps.
							' WHERE '. $query_where_addon . $query_where_addon_language_ps . 
									($isModeExpression ? 
										' AND wbo.canonical_word_id IN ('.implode(',', array_fill(1, count($search_text_words__word__word_id), '?')).')'
										:
										''
									);
				
				if (!empty($query_bind_values_extra)) {
					$query_bind_values = array_merge($query_bind_values, $query_bind_values_extra);
				}
				
				$res = $dataCenter->SQLite_DB->prepare($query);
				$res->execute($query_bind_values);
				while ($row = $res->fetch(PDO::FETCH_NUM)) {
					
					$sentence_id__data[$row[0]] = array(
						'book_id'			=> $row[1],
						'paragraph_id'		=> $row[2],
						'book_sentence_id'	=> $row[3]
					);
					
					// sentence_id, order_num => word_num(word_id)
					if ($isModeExpression && isset($search_text_words__word_id__word_num[$row[4]])) {
						$ps_id_data[$row[0]][$row[5]] = $search_text_words__word_id__word_num[$row[4]];
					}
				}
				$ps_idA_offset += SELECT_IN_VALUE_NUMBER_THRESHOLD_IN_SQL;
			}
			
			
		//------------------------
		// Paragraph process
		//------------------------
		} else {
			
			foreach ($total_ps_idA as $pd_idA) {
				
				$query_where_addon = array();
				$query_bind_values = array();
				foreach ($pd_idA as $book_id => $book_id_data) {
					$query_where_addon[] = '(c.book_id = ? AND p.book_paragraph_id IN ('.implode(',', array_fill(1, count($book_id_data), '?')).'))';
					$query_bind_values = array_merge($query_bind_values, array($book_id), array_values($book_id_data));
				}
				$query_where_addon = implode(' OR ', $query_where_addon);
				
				$query = 'SELECT p.id, c.book_id, p.book_paragraph_id'.
							' FROM '.		$do['db_table_prefix'].PARAGRAPH_TABLE					.	' AS p'.
							' INNER JOIN '.	$do['db_table_prefix'].CHAPTER_TABLE					.	' AS c ON p.chapter_id = c.id'.
							$query_join_addon_language_ps.
							' WHERE '. $query_where_addon . $query_where_addon_language_ps;
				
				$res = $dataCenter->SQLite_DB->prepare($query);
				
				if ($do['selected_language_id'] !== NULL) {
					$query_bind_values = array_merge($query_bind_values, array($do['selected_language_id']));
				}
				$res->execute($query_bind_values);
				
				while ($row = $res->fetch(PDO::FETCH_NUM)) {
					
					$paragraph_id__data[$row[0]] = array(
						'book_id'			=> $row[1],
						'book_paragraph_id'	=> $row[2]
					);
				}
				$ps_idA_offset += SELECT_IN_VALUE_NUMBER_THRESHOLD_IN_SQL;
			}
		}
		
		
		//----------------------------------------------------------
		// expression: word order match
		// ONLY in sentences
		//----------------------------------------------------------
		if ($isModeExpression) {
			
			
	//writeOut($search_text_words__word_id);
	//writeOut($ps_id_data);
	//writeOut('row num: '.$num);
	//writeOut('ps num: '.count($ps_id_relevanceA));
	//writeOut('expression final select: '.(microtime(true) - $t1));
	//$t1 = microtime(true);
//writeOut($ps_id_data);
			
			$result_set_new = array();
			
			//------------------------------------------------
			// all sentences check
			//------------------------------------------------
			foreach ($ps_id_data as $sentence_id => $sentence_data) {
				
				ksort($sentence_data);
				
				$maxPos = count($sentence_data) - 1;
				$currentPos = 0;
				$found_word_num = -1;
				$needSearch = true;
				$saved_order_num = $saved_word_num = -1;
				
				while ($needSearch and ($currentPos <= $maxPos)) {
					
					// $order_num 	: 0., 1. place in the sentence
					// $word_num 	: 0., 1. word in the expression
					$order_num = key($sentence_data);
					$word_num = current($sentence_data);
					next($sentence_data);
					
					// first word comes
					if ($word_num == 0) {
						$found_word_num = 0;
						$saved_order_num = $order_num;
						$saved_word_num = $word_num;
					
					// next word comes in proper place (neighbour)
					} else if (($saved_order_num + 1 == $order_num) and ($saved_word_num + 1 == $word_num)) {
						
						// found full expression => finish check
						if ($found_word_num == $search_text_words_num-2) {
							$needSearch			= false;
							$book_id			= $sentence_id__data[$sentence_id]['book_id'];
							$book_sentence_id	= $sentence_id__data[$sentence_id]['book_sentence_id'];
							$result_set_new[$sentence_id] = array(
								//'book_id' 	=> $result_set[$sentence_id]['book_id'],
								//'relevance' => $result_set[$sentence_id]['relevance']
								'book_id' 	=> $book_id,
								'relevance' => $result_set[$book_id][$book_sentence_id]
							);
						// found next word
						} else {
							++$found_word_num;
							$saved_order_num = $order_num;
							$saved_word_num = $word_num;
						}
					} else {
					}
					++$currentPos;
				}
			}
			
			// sentence_id -> paragraph_id conversion if needs
			if (IS__SITE_UNIT_TYPE__PARAGRAPH) {
				$result_set = array();
				foreach ($result_set_new as $sentence_id => $data) {
					if (!isset($result_set[$sentence_id__data[$sentence_id]['paragraph_id']])) {
						$result_set[$sentence_id__data[$sentence_id]['paragraph_id']] = $data;
					} else {
						$result_set[$sentence_id__data[$sentence_id]['paragraph_id']]['relevance'] += $data['relevance'];
					}
				}
			} else {
				$result_set = $result_set_new;
			}
			
		// Sentence process
		} else if ($isSentence_process) {
			
			$result_set_new = array();
			
			// all sentences
			foreach ($sentence_id__data as $sentence_id => $sentence_data) {
				
				$book_id			= $sentence_data['book_id'];
				$book_sentence_id	= $sentence_data['book_sentence_id'];
				$result_set_new[$sentence_id] = array(
					'book_id' 	=> $book_id,
					'relevance' => $result_set[$book_id][$book_sentence_id]
				);
			}
			$result_set = $result_set_new;
			
		// Paragraph process
		} else {
			
			$result_set_new = array();
			$c = 0;
			// all paragraphs
			foreach ($paragraph_id__data as $paragraph_id => $paragraph_data) {
				
				$book_id			= $paragraph_data['book_id'];
				$book_paragraph_id	= $paragraph_data['book_paragraph_id'];
				
				$result_set_new[$paragraph_id] = array(
					'book_id' 	=> $book_id,
					'relevance' => $result_set[$book_id][$book_paragraph_id]
				);
			}
			$result_set = $result_set_new;
		}
		
		
		//-----------------------------------------------------------------------------------
		// Direct calling
		//-----------------------------------------------------------------------------------
		if (!$do['evaluatePolishForm_called']) {
			
			
			//-----------------------------------------------------------------------------------
			// Sort
			//-----------------------------------------------------------------------------------
			$do_sort = (IS__SITE_SEARCH_TYPE__RELEVANCE ? 'relevance' : 'linear');
			$result_set = sortResultSet($do_sort, $result_set);
			
			
			//-----------------------------------------------------------------------------------
			// Total result num for the query
			//-----------------------------------------------------------------------------------
			$do['total_result_ps_num'] = count($result_set);
			
			
			//-----------------------------------------------------------------------------------
			// Download results
			//-----------------------------------------------------------------------------------
			if (DO_DOWNLOAD_RESULTS) {
				
				// all the results
				//$result_set = array_slice($result_set, 0);
				$do['result_ps_loaded_num'] = $do['total_result_ps_num'];
				
				
			//-----------------------------------------------------------------------------------
			// Display results
			// Trim to required amount of result
			//  array_slice(): index (1..n)->(0..n-1)
			//-----------------------------------------------------------------------------------
			} else {
				
				// Load next [n] item
				if ($do['searchResultList_loadNext']) {
		//writeOut('load next');
					$result_set = array_slice($result_set, $do['searchResultList_loaded_num'], SEARCH_RESULT_LIST__ITEM_NUM__LOAD_NEXT);
					// result num loaded now
					$do['result_ps_loaded_num'] = count($result_set);
					
				// Load all the rest
				} if ($do['searchResultList_loadAll']) {
		//writeOut('load all');
					$result_set = array_slice($result_set, $do['searchResultList_loaded_num']);
					$do['result_ps_loaded_num'] = $do['total_result_ps_num'] - $do['searchResultList_loaded_num'];
					
				// Load standard way
				} else {
					$result_set = array_slice($result_set, 0, SEARCH_RESULT_LIST__ITEM_NUM__LOAD_NEXT);
					$do['result_ps_loaded_num'] = count($result_set);
				}
			}
			
			// total result num loaded now and before
			$do['total_result_ps_loaded_num'] = $do['searchResultList_loaded_num'] + $do['result_ps_loaded_num'];
		}		
		
		//writeOut($search_text_words);
		//writeOut($search_text_words_inflected_words);
		//writeOut('Result num: '.count($result_set));
		
		//writeOut(round(microtime(true) - $t2, 5));
		
		return $result_set;
	} // getSearchResult(&$do, $search_text)
?>