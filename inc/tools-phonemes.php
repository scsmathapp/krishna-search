<?php
	//--------------------------------------------------------------------------------
	// Word suggestion by Levenshtein, Metaphone
	//--------------------------------------------------------------------------------
	function word_suggestion($dataCenter, $do) {
		
		$out_word_suggestion = array();
		
		// Find first real search word
		$search_text__good_words = array();
		$word_searchedWord = '';
		
		//$ret_num = preg_match_all('/[^\s\-]+/u', $do['search_text_standard'], $words);
		$ret_num = preg_match_all('/['.REGEX__WORD_PART.']+/u', $do['search_text'], $words);
		$ret_num = (($ret_num===false) ? 0 : $ret_num);
		if ($ret_num) {
			foreach ($words[0] as $key => $word) {
				if (!isset($dataCenter->operator_and_priority[$word]) and (strlen($word) > 1)) {
					$search_text__good_words[] = $word;
				}
			}
		/*mb_ereg_search_init($do['search_text'], '['.REGEX__WORD_PART.']+');
		$words = mb_ereg_search();
		if ($words) {
			$words = mb_ereg_search_getregs(); // get first result
			do {
				$word = $words[0];
				if (!isset($dataCenter->operator_and_priority[$word]) and (mb_strlen($word) > 1)) {
					$search_text__good_words[] = $word;
				}
			} while($words = mb_ereg_search_regs()); // get next result
		*/	
			// First word
			if (isset($search_text__good_words[0])) {
				
				$word_searchedWord = $search_text__good_words[0];
				
				//-----------------------------------------------------------------------
				// Suggestion by levenshtein
				//-----------------------------------------------------------------------
				$str_len_searchedWord = strlen($word_searchedWord);
				$beginCharNumGood = (WORD_SUGGESTION_BEGIN_CHAR_NUM_GOOD < $str_len_searchedWord) ? 
										WORD_SUGGESTION_BEGIN_CHAR_NUM_GOOD : $str_len_searchedWord;
				$word_suggestion = null;
				if ($beginCharNumGood <= strlen($word_searchedWord)) {
					$ret_word_suggestion = word_suggestion_by_levenshtein($dataCenter, $do, $word_searchedWord, $beginCharNumGood);
					$maxPercentMatch = $ret_word_suggestion['percent_match'];
					$word_suggestion = $ret_word_suggestion['closest_word'];
					
					while (($ret_word_suggestion['percent_match'] < 0.6) and ($beginCharNumGood >= 2)) {
						$ret_word_suggestion = word_suggestion_by_levenshtein($dataCenter, $do, $word_searchedWord, --$beginCharNumGood);
						
						if ($maxPercentMatch <= $ret_word_suggestion['percent_match']) {
							$maxPercentMatch = $ret_word_suggestion['percent_match'];
							$word_suggestion = $ret_word_suggestion['closest_word'];
						} else {
							$beginCharNumGood = 0;
						}
					}
					if (!empty($word_suggestion)) {
						$out_word_suggestion[] = $word_suggestion;
					}
					
					//-----------------------------------------------------------------------
					// Suggestion by metaphone
					//-----------------------------------------------------------------------
					$word_suggestion_2 = null;
					$ret_word_suggestion = word_suggestion_by_metaphone($dataCenter, $do, $word_searchedWord);
					$word_suggestion_2 = $ret_word_suggestion['closest_word'];
					if (!empty($word_suggestion_2) and ($word_suggestion != $word_suggestion_2)) {
						$out_word_suggestion[] = $word_suggestion_2;
					}
				}
			}
		}
		return $out_word_suggestion; // out: $word_searchedWord.$out_word_suggestion	    
	}
	
	
	//--------------------------------------------------------------------------------
	// Word suggestion by levenshtein
	//--------------------------------------------------------------------------------
	function word_suggestion_by_levenshtein($dataCenter, $do, $word, $beginCharNumGood) {
		
		//$word_suggestion = null;
		$word_beginning = substr($word, 0, $beginCharNumGood);
		$word_suggestionA = array();
		
		//----------------------------------------------------
		// 1st round to get words close by
		//----------------------------------------------------
		if ($do['selected_language_id'] !== NULL) {
			$query_addon_where = ' AND language_id = ?';
			$query_bind_values = array($word_beginning.'%', $do['selected_language_id']);
		} else {
			$query_addon_where = '';
			$query_bind_values = array($word_beginning.'%');
		}
		
		$query = 'SELECT word'.
					' FROM '.$do['db_table_prefix'].WORD_BOOKS_LANGUAGES_TABLE.
					' WHERE word LIKE ?'.
					$query_addon_where;
		$res = $dataCenter->SQLite_DB->prepare($query);
		$res->execute($query_bind_values);
		while ($row = $res->fetch(PDO::FETCH_NUM)) {
			$word_suggestionA[] = $row[0];
		}
		
		//----------------------------------------------------
		// 2nd round to estimate the closest from 1st round
		//----------------------------------------------------
		$ret = closest_word_by_levenshtein($word, $word_suggestionA);
		
		return $ret;
	} // function word_suggestion_by_levenshtein($dataCenter, $do, $word, $beginCharNumGood)
	
	
	
	//--------------------------------------------------------------------------------
	// Word suggestion by metaphone
	//--------------------------------------------------------------------------------
	function word_suggestion_by_metaphone($dataCenter, $do, $word) {
		
		//$word_suggestion = null;
		
		// transliterated word
		//$word_metaphone = metaphone($word);
		
		// above PHP 5.4
		//$word_metaphone = metaphone( transliterator_transliterate( 'Any-Latin; Latin-ASCII; [:Punctuation:] Remove;', 
		//													$word ) );
		
		// with tables
		// Transliterator
		include_once (__DIR__.'/tools-transliterate.php');
		$transliterator = new JTransliteration();
		
		$word_metaphone = metaphone( $transliterator->transliterate( $word ) );
		
		$word_suggestionA = array();
		
		//----------------------------------------------------
		// 1st round to get words close by
		//----------------------------------------------------
		if ($do['selected_language_id'] !== NULL) {
			$query_addon_where = ' AND language_id = ?';
			$query_bind_values = array($word_metaphone.'%', $do['selected_language_id']);
		} else {
			$query_addon_where = '';
			$query_bind_values = array($word_metaphone.'%');
		}
		
		$query = 'SELECT word'.
					' FROM '.$do['db_table_prefix'].WORD_BOOKS_LANGUAGES_TABLE.
					' WHERE phonemes LIKE ?'.
					$query_addon_where;
		$res = $dataCenter->SQLite_DB->prepare($query);
		$res->execute($query_bind_values);
		while ($row = $res->fetch(PDO::FETCH_NUM)) {
			//$word_suggestionA[$row[0]] = $row[1];
			$word_suggestionA[] = $row[0];
		}
	
		//----------------------------------------------------
		// 2nd round to estimate the closest from 1st round
		//----------------------------------------------------
		$ret = closest_word_by_levenshtein($word, $word_suggestionA);
		
		return $ret;
	} // function word_suggestion_by_metaphone($dataCenter, $do, $word)
	
	
	
	//----------------------------------------------------------------------------------------------------
	// Closest word by levenshtein
	//
	// ($shortest == 0) => Exact match found
	//----------------------------------------------------------------------------------------------------
	function closest_word_by_levenshtein($input, $words) {
		
		// no shortest distance found, yet
		$shortest = -1;
		$closest = '';
		$key = TRUE;

		// loop through words to find the closest
		while (($shortest != 0) and ($key !== NULL)) {
			
			$key = key($words);
			$word = current($words);
			next($words);

			// check for an exact match
			if ($input == $word) {
				$closest = $word;
				$shortest = 0;
			} else {
				
				// calculate the distance between the input word and the current word
				$lev = levenshtein($input, $word); // 0.04 s
				
				// v2
				//similar_text($input, $word, $percent);	// 0.04s
				//$lev = 1 - $percent / 100;
				
				// if this distance is less than the next found shortest distance, 
				//   OR
				// if a next shortest word has not yet been found
				if ($lev <= $shortest || $shortest < 0) {
					// set the closest match, and shortest distance
					$closest  = $word;
					$shortest = $lev;
				}
			}
		}
		$percent = 1 - levenshtein($input, $closest) / max(strlen($input), strlen($closest));
		
		return array('shortest_distance' => $shortest, 'closest_word' => $closest, 'percent_match' => $percent);
	} // function closest_word_by_levenshtein($input, $words)
	
	
	/*
	//----------------------------------------------------------------------------------------------------
	// Closest word by metaphone
	//
	// ($shortest == 0) => Exact match found
	//----------------------------------------------------------------------------------------------------
	function closest_word_by_metaphone($input, $words) {
		
		// no shortest distance found, yet
		$shortest = -1;
		$closest = '';
		$word = TRUE;

		// loop through words to find the closest
		while (($shortest != 0) and ($word !== NULL)) {
			
			$word = key($words);
			$word_phonemes = current($words);
			next($words);

			// check for an exact match
			if ($input == $word) {
				$closest = $word;
				$shortest = 0;
			} else {
				
				// calculate the distance between the input word and the current word
				$lev = levenshtein($input, $word); // 0.04 s
				
				// v2
				//similar_text($input, $word, $percent);	// 0.04s
				//$lev = 1 - $percent / 100;
				
				// if this distance is less than the next found shortest distance, 
				//   OR
				// if a next shortest word has not yet been found
				if ($lev <= $shortest || $shortest < 0) {
					// set the closest match, and shortest distance
					$closest  = $word;
					$shortest = $lev;
				}
			}
		}
		
		$percent = 1 - levenshtein($input, $closest) / max(strlen($input), strlen($closest));
		
		return array('shortest_distance' => $shortest, 'closest_word' => $closest, 'percent_match' => $percent);
	} // function closest_word_by_metaphone($input, $words)
	*/
?>