<?php
	//----------------------------------------------------------------------------------------
	// Books: author_id => (author name, books_data => (book name, link_last_part_on_GD))
	//----------------------------------------------------------------------------------------
	function load_books_info($dataCenter) {
		$st_books = array();
		
		$query = 'SELECT a.author_id, a.'.AUTHOR_NAME_FORM.', b.book_name, b.link_last_part_on_GD'.
					' FROM '		.$do['db_table_prefix'].BOOK_TABLE	.' as b'.
					' INNER JOIN '	.$do['db_table_prefix'].AUTHOR_TABLE.' as a ON b.author_id=a.author_id'.
					' ORDER BY b.book_name';
		$res = $dataCenter->SQLite_DB->prepare($query);
		$res->execute();
		while ($row = $res->fetch(PDO::FETCH_NUM)) {
			$st_books[$row[0]]['author_name'] = $row[1];
			$st_books[$row[0]]['books'][] = array(	'book_name'				=> $row[2], 
													'link_last_part_on_GD'	=> $row[3]);
		}
		
		return $st_books;
	}
	
	//----------------------------------------------------------------------------------------
	// Display books data (author_id) => (author name, book name, book link)
	//----------------------------------------------------------------------------------------
	/*
	function get_books_info($st_books, $author_id) {
		if (!empty($st_books[$author_id])) {
			
			$author_books_data = $st_books[$author_id];
			
			$ret =  NEWLINE.'<h3>'.$author_books_data['author_name'].'\'s books</h3>'.
							NEWLINE.'<ul>';
							
			foreach ($author_books_data['books'] as $book_data) {
				$ret .= NEWLINE.'<li><a href="'.BOOKS_EN_URL_BEGIN_ON_GAUDIYADARSHAN.
								(!empty($book_data['link_last_part_on_GD']) ? $book_data['link_last_part_on_GD'].'/' : '').
								'" class="link_on_gd">'.$book_data['book_name'].'</a></li>';
			}
			$ret .= NEWLINE.'</ul>';
		} else {
			$ret = '';
		}
		return $ret;
	}*/
?>