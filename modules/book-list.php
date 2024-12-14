<?php
	//------------------------------------------------------------------------------------------------------------------------
	// Book, Video list
	//------------------------------------------------------------------------------------------------------------------------
	//------------------------------------------------------------------------------------------------------------------------
	// Book list
	//------------------------------------------------------------------------------------------------------------------------
	if ($showBookList && IS__SEARCH_IN_BOOKS) {
		
		echo '<input type="hidden" name="book_id_list_send" value="1" />';
		
		// Sort authors
		ksort($do['authors']);
		
		// Index page
		if ($index_page) {
			$urlAddon = '&amp;one_col=1';
			
			echo '<a href="javascript:void(0)" class="button" id="author-book-selector-a" style="float:none;margin-right:2em;" onclick="toggle(\'author-book-list\');">
					<span class="icon icon_book" id="author-book-selector-span"></span>
				</a>'.

				'<div id="author-book-list" style="display:none;">
					<ul id="author-book-selector">';
		// Settings page
		} else if ($settings_page) {
			$urlAddon = '&amp;video=1';
			
			echo '<div id="author-book-list-preferences">
					<ul id="author-book-selector">';
		}
		
		//--------------------------------------------------
		// All author checkbox
		//--------------------------------------------------
		$all_checked = (!$do['searchBooksPartial'] or ($do['searchBookListCode'] == strval(ALL_BOOKS__BOOK_ID)));
		$author_checked = $all_checked;
		$author_class = 'class="'.($author_checked ? 'on' : 'off').'"';
		
		$toggle_all = '';
		foreach ($do['authors'] as $author_id => $author_data) {
			$toggle_all .= 'toggle_book(\'an'.$author_id.'\'); ';
		}

		echo '<li '.$author_class.'>'.
				'<input type="checkbox" onclick="toggle_book_color(\'author-book-selector\', 0);"'.
							($author_checked ? ' checked="checked"' : '').' />'.
				'<span onclick="'.$toggle_all.' return false;">All</span>'.
			'</li>';

		//--------------------------------------------------
		// Authors..
		//--------------------------------------------------
		foreach ($do['authors'] as $author_id => $author_data) {


			//--------------------------------------------------
			// All books from author (checked or not)
			//--------------------------------------------------
			$author_books_checked = true;
			if (!$all_checked) {
				foreach ($do['authors'][$author_id]['book_id_list'] as $book_id => $t) {
					if (!isset($do['searchBooksIdList'][$book_id])) { $author_books_checked = false; }
				}
			}

			$author_class = 'class="'.($author_books_checked ? 'on' : 'off').'"';


			//--------------------------------------------------
			// Author name
			//--------------------------------------------------
			echo '<li id="an'.$author_id.'" '.$author_class.'>'.
						'<input type="checkbox" onclick="toggle_book_color(\'an'.$author_id.'\', 0);"'.
									($author_books_checked ? ' checked="checked"' : '').' />';

			// If (author != book name) => need book names here
			//if (!$do['authors'][$author_id]['author_book_equal']) {

				echo '<span onclick="toggle_book(\'an'.$author_id.'\'); return false;">'.$author_data['full_name'].'</span>'.
						'<ul style="display:none;">';


				//--------------------------------------------------
				//..Author's Books
				//--------------------------------------------------
				foreach ($author_data['book_id_list'] as $book_id => $t) {

					$book_checked = ($all_checked or isset($do['searchBooksIdList'][$book_id]));
					$book_class = 'class="'.($book_checked ? 'on' : 'off').'"';

					echo '<li id="bn'.$book_id.'" '.$book_class.'>'.
								'<input type="checkbox" name="book_id['.$book_id.']" value="'.$book_id.'"'.
										' onclick="toggle_book_color(\'bn'.$book_id.'\', 1);"'.
										($book_checked ? ' checked="checked"' : '').' />'.
								'<a href="'.WEB_INDEX_FILE.'?b='.$book_id.'&amp;c=1'.
										$do['urlDataTransfer'].
										$urlAddon.
										'#c1">'.
									$do['books'][$book_id]['book_title'].
								'</a>'.
						'</li>';
				}
				echo '</ul>';

			// If (author == book name) => no need book names here
			/*} else {
				$book_id = key($author_data['book_id_list']);
				$t = current($author_data['book_id_list']);
				next($author_data['book_id_list']);
				echo '<a href="'.WEB_INDEX_FILE.'?b='.$book_id.'&amp;c=1'.
										$do['urlDataTransfer'].
										$urlAddon.
										'#c1">'.
									$author_data['full_name'].
								'</a>';
			}*/
			echo '</li>';
		}
		echo '</ul></div>';
	}	
	
	
	
	//------------------------------------------------------------------------------------------------------------------------
	// Video list
	//------------------------------------------------------------------------------------------------------------------------
	if ($showVideoList && IS__SEARCH_IN_VIDEOS) {
		
		echo '<input type="hidden" name="video_id_list_send" value="1" />';
		
		// Sort authors
		ksort($do[TABLE_NAME_COMPONENT__VIDEO.'authors']);
		
		// Index page
		if ($index_page) {
			$urlAddon = '&amp;one_col=1&amp;video=1';
			
			echo '<a href="javascript:void(0)" class="button" id="author-video-selector-a" style="float:none;margin-right:2em;" onclick="toggle(\'author-video-list\');">
					<span class="icon icon_video" id="author-video-selector-span"></span>
				</a>'.

				'<div id="author-video-list" style="display:none;">
					<ul id="author-video-selector">';
		// Settings page
		} else if ($settings_page) {
			$urlAddon = '&amp;video=1';
			
			echo '<div id="author-video-list-preferences">
					<ul id="author-video-selector">';
		}

		//--------------------------------------------------
		// All author checkbox
		//--------------------------------------------------
		$all_checked = (!$do['searchVideosPartial'] or ($do['searchVideoListCode'] == strval(ALL_BOOKS__BOOK_ID)));
		$author_checked = $all_checked;
		$author_class = 'class="'.($author_checked ? 'on' : 'off').'"';

		$toggle_all = '';
		foreach ($do[TABLE_NAME_COMPONENT__VIDEO.'authors'] as $author_id => $author_data) {
			$toggle_all .= 'toggle_book(\'van'.$author_id.'\'); ';
		}

		echo '<li '.$author_class.'>'.
				'<input type="checkbox" onclick="toggle_book_color(\'author-video-selector\', 0);"'.
							($author_checked ? ' checked="checked"' : '').' />'.
				'<span onclick="'.$toggle_all.' return false;">All</span>'.
			'</li>';

		//--------------------------------------------------
		// Authors..
		//--------------------------------------------------
		foreach ($do[TABLE_NAME_COMPONENT__VIDEO.'authors'] as $author_id => $author_data) {


			//--------------------------------------------------
			// All books from author (checked or not)
			//--------------------------------------------------
			$author_books_checked = true;
			if (!$all_checked) {
				foreach ($do[TABLE_NAME_COMPONENT__VIDEO.'authors'][$author_id]['book_id_list'] as $book_id => $t) {
					if (!isset($do['searchVideosIdList'][$book_id])) { $author_books_checked = false; }
				}
			}
			
			$author_class = 'class="'.($author_books_checked ? 'on' : 'off').'"';


			//--------------------------------------------------
			// Author name
			//--------------------------------------------------
			echo '<li id="van'.$author_id.'" '.$author_class.'>';

			// If (author != book name) => need book names here
			if (!$do[TABLE_NAME_COMPONENT__VIDEO.'authors'][$author_id]['author_book_equal']) {

				echo '<input type="checkbox" onclick="toggle_book_color(\'van'.$author_id.'\', 0);"'.
									($author_books_checked ? ' checked="checked"' : '').' />';
				
				echo '<span onclick="toggle_book(\'van'.$author_id.'\'); return false;">'.$author_data['full_name'].'</span>'.
						'<ul style="display:none;">';


				//--------------------------------------------------
				//..Author's Books
				//--------------------------------------------------
				foreach ($author_data['book_id_list'] as $book_id => $t) {

					$book_checked = ($all_checked or isset($do['searchVideosIdList'][$book_id]));
					$book_class = 'class="'.($book_checked ? 'on' : 'off').'"';

					echo '<li id="vbn'.$book_id.'" '.$book_class.'>'.
								'<input type="checkbox" name="video_id['.$book_id.']" value="'.$book_id.'"'.
										' onclick="toggle_book_color(\'vbn'.$book_id.'\', 1);"'.
										($book_checked ? ' checked="checked"' : '').' />'.
								'<a href="'.WEB_INDEX_FILE.'?b='.$book_id.'&amp;c=1'.
										$do['urlDataTransfer'].
										$urlAddon.
										'#c1">'.
									$do[TABLE_NAME_COMPONENT__VIDEO.'books'][$book_id]['book_title'].
								'</a>'.
						'</li>';
				}
				echo '</ul>';

			// If (author == book name) => no need book names here
			} else {
				$book_id = key($author_data['book_id_list']);
				$t = current($author_data['book_id_list']);
				next($author_data['book_id_list']);
				
				echo '<input type="checkbox" onclick="toggle_book_color(\'van'.$author_id.'\', 0);"'.
									' name="video_id['.$book_id.']" value="'.$book_id.'"'.
									($author_books_checked ? ' checked="checked"' : '').' />';
				
				echo '<a href="'.WEB_INDEX_FILE.'?b='.$book_id.'&amp;c=1'.
										$do['urlDataTransfer'].
										$urlAddon.
										'#c1">'.
									$author_data['full_name'].
								'</a>';
			}
			echo '</li>';
		}
		echo '</ul></div>';
	}
?>