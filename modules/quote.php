<?php
	//------------------------------------------------------------------------------------------------------------------------
	// Quote
	//------------------------------------------------------------------------------------------------------------------------
	//--------------------------------------------------------
	// Paragraph id: good for quote
	//--------------------------------------------------------
	$paragraph_type_id__indented_paragraph = 12;
	
	$query = 'SELECT paragraph_type_id'.
				' FROM '.PARAGRAPH_TYPE_TABLE.
				' WHERE paragraph_type_name=?';
	$query_bind_values = array(PARAGRAPH_TYPE_NAME_FOR_QUOTE);
	$res = $dataCenter->SQLite_DB->prepare($query);
	$res->execute($query_bind_values);
	if ($row = $res->fetch(PDO::FETCH_NUM)) {
		$paragraph_type_id__indented_paragraph = $row[0];
	}
	
	
	//--------------------------------------------------------
	// Books data
	//--------------------------------------------------------
	$bookData = array();
	
	$query = 'SELECT b.book_id, b.book_name, a.'.AUTHOR_NAME_FORM.
				' FROM '		.$do['db_table_prefix'].BOOK_TABLE	.' as b'.
				' INNER JOIN '	.$do['db_table_prefix'].AUTHOR_TABLE.' as a ON b.author_id=a.author_id';
	$res = $dataCenter->SQLite_DB->prepare($query);
	$res->execute();
	while ($row = $res->fetch(PDO::FETCH_NUM)) {
		$bookData[$row[0]] = array('title' => $row[1], 'author' => $row[2]);
	}
	
	// Choose a book
	$quote_book_id_chosen = rand(1, count($bookData));
	
	
	//--------------------------------------------------------
	// Sentence IDs
	//--------------------------------------------------------
	$quote_sentence_id = array();
	$query = 'SELECT b.book_id, s.book_sentence_id'.
				' FROM '.$do['db_table_prefix'].SENTENCE_TABLE.	' AS s'.
				' INNER JOIN '.PARAGRAPH_TABLE.				' AS p ON s.book_paragraph_id=p.book_paragraph_id'.
				' INNER JOIN '.CHAPTER_TABLE.				' AS c ON c.id=p.chapter_id'.
				' WHERE c.book_id=? AND p.paragraph_type_id=?';
	$query_bind_values = array($quote_book_id_chosen, $paragraph_type_id__indented_paragraph);
	$res = $dataCenter->SQLite_DB->prepare($query);
	$res->execute($query_bind_values);
	while ($row = $res->fetch(PDO::FETCH_NUM)) {
		$quote_sentence_id[] = array('book_id' => $row[0], 'book_sentence_id' => $row[1]);
	}
	
	// Choose a sentence
	$quote_sentence_id_chosen = $quote_sentence_id[rand(0, count($quote_sentence_id)-1)];
	$quote_sentence_id = null;
	unset($quote_sentence_id);
	
	/*
	//--------------------------------------------------------
	// Sentence
	//--------------------------------------------------------
	$query = 'SELECT s.'.SENTENCE_TABLE_FIELD.', s.paragraph_id'.
				' FROM '.SENTENCE_TABLE.' as s'.
				' WHERE s.sentence_id=?';
	$query_bind_values = array($quote_sentence_id_chosen);
	$res = $dataCenter->SQLite_DB->prepare($query);
	$res->execute($query_bind_values);
	if ($row = $res->fetch(PDO::FETCH_NUM)) {
		
		$quote_text = USE_ENCRYPTION_TABLE_DATA ? Decrypt($row[0]) : $row[0];
		
		if (IS__SITE_UNIT_TYPE__PARAGRAPH) {
			$do['siteOperationMode_Values']['unit'] = SITE_UNIT_TYPE__SENTENCE;
			$do['siteOperationMode'] = convert_SiteOperationMode_arrayToValue($do, $do['siteOperationMode_Values']);
		}
		
		
		//--------------------------------------------------------
		// Output
		//--------------------------------------------------------
		echo '<div id="quote-wrapper">'.
				'<div id="quote-text">'.$quote_text.'</div>'.
				'<div id="quote-readmore">'.
					'<a href="'.WEB_INDEX_FILE.'?'.
								$do['urlDataTransfer'].
								'&amp;s='.$quote_sentence_id_chosen.
								'&amp;p='.$row[1].
								'#p'.$row[1].'" aria-describedby="quote-source">Read more...</a>'.
				'</div>'.
				'<div id="quote-source">('.
					'<span id="quote-book">'.$bookData[$quote_book_id_chosen]['title'].':</span>'.
					'<span id="quote-author">'.$bookData[$quote_book_id_chosen]['author'].'</span>'.
				')</div>'.
				'<div class="clear"></div>'.
			'</div>';
	}
	*/
?>