<?php
	//---------------------------------------------------------------------------------------------------
	// Get and clean value
	//---------------------------------------------------------------------------------------------------
	if (!isset($_POST['input']) or !isset($_POST['l'])) { exit; }
	
	include_once (__DIR__.'/tools.php');
	include_once (__DIR__.'/settings.php');
	
	//--------------------------------
	// Input string process
	//--------------------------------
	$input = ltrim(strtolower($_POST['input']));
	$ret = mb_ereg_replace('\s\s+', ' ', $input);
	if ($ret !== NULL) { $input = $ret; }
	
	if (empty($input)) { exit; }
	
	// space at the end
	if (substr($input, -1, 1) == ' ') {
		$spaceAtTheEnd = true;
	} else {
		$spaceAtTheEnd = false;
	}
	
	
	//--------------------------------------
	// for [word, expression] text search
	//--------------------------------------
	$wordsA = explode(' ', $input);
	$wordsNum = count($wordsA);
	
	
	// Nothing to search
	if ($spaceAtTheEnd) {
		exit;
	}

	// <input> beginning without the last word
	// during typing the whole line shows up in the list
	if ($wordsNum > 1) {
		$inputBeginning = substr($input, 0, strrpos($input, ' ')+1);
	} else {
		$inputBeginning = '';
	}

	// Search for only the last word in the query
	$inputWord = $wordsA[$wordsNum-1];


	//---------------------------------------------------------------------------------------------------
	// Search word
	//---------------------------------------------------------------------------------------------------
	include_once (FS_INCLUDE_DIR.'/datacenter.php');

	$dataCenter 	= new DataCenter($dbConnectionData);
	$noResult 		= true;
	$result_final 	= array();
	$result_num 	= $result_cache_num = $result_word_num = 0;
	$result_num_max = AUTOSUGGEST_LIST_LINEAR_MAX_ITEM_NUMBER;

	//-------------------------------------------------------
	// Search in books, videos words table
	//-------------------------------------------------------
	if (IS__SEARCH_IN_BOOKS) {
		
		$query_bind_values = array($inputWord.'%');
		
		$query = 'SELECT DISTINCT word'.
					' FROM ' . WORD_BOOKS_LANGUAGES_TABLE.
					' WHERE word LIKE ?'.
					' ORDER BY word ASC'.
					' LIMIT '.AUTOSUGGEST_LIST_LINEAR_MAX_ITEM_NUMBER;
		$res = $dataCenter->SQLite_DB->prepare($query);
		$res->execute($query_bind_values);
		while ($row = $res->fetch(PDO::FETCH_NUM)) {
			++$result_word_num;
			$result_final[$inputBeginning.$row[0]] = $inputBeginning.$row[0];
		}
	}
	
	if (IS__SEARCH_IN_VIDEOS) {
		
		if ($do['selected_language_id'] !== NULL) {
			$query_addon_where = ' AND language_id = ?';
			$query_bind_values = array($inputWord.'%', $do['selected_language_id']);
		} else {
			$query_addon_where = '';
			$query_bind_values = array($inputWord.'%');
		}
		
		$query = 'SELECT DISTINCT word'.
					' FROM '.VIDEO_WORD_BOOKS_LANGUAGES_TABLE.
					' WHERE word LIKE ?'.
					$query_addon_where.
					' ORDER BY word ASC'.
					' LIMIT '.AUTOSUGGEST_LIST_LINEAR_MAX_ITEM_NUMBER;
		$res = $dataCenter->SQLite_DB->prepare($query);
		$res->execute($query_bind_values);
		while ($row = $res->fetch(PDO::FETCH_NUM)) {
			++$result_word_num;
			$result_final[$inputBeginning.$row[0]] = $inputBeginning.$row[0];
		}
	}
	
	
	if ($result_word_num) {
		$noResult = false;
	} else {
		exit;
	}

	// Result num
	$result_num = $result_cache_num + $result_word_num;
	
	
	//--------------------------------------
	// Result num
	//--------------------------------------
	// Clip or fill to the desired item number
	if ($result_num) {
		// clip
		if ($result_num_max < $result_num) {
			$result_num = $result_num_max;
			array_splice($result_final, $result_num_max);
			
		//..fill
		} else if ($result_num < AUTOSUGGEST_LIST_MAX_ITEM_NUMBER_SHOW) {
			$result_final = array_pad($result_final, AUTOSUGGEST_LIST_MAX_ITEM_NUMBER_SHOW, '  ');
		}
	}
	
	
	//---------------------------------------------------------------------------------------------------
	// Response
	//---------------------------------------------------------------------------------------------------
	// headers
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Cache-Control: no-cache");
	header("Pragma: no-cache");
	header("Content-type: text/xml");
	
	// response
	$response = '<response>';
	foreach ($result_final as $text) {
		$response .= '<keywords>'.$text.'</keywords>';
	}
	$response .= '</response>';
	echo $response;
?>