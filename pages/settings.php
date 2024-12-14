<div id="page-content">
	<div id="tab-navigation-container">
		<ul class="tab-navigation">
<?php
if (!IS__SITE_SIMPLIFIED_LAYOUT) {
?>

			<li><a href="#tab-book-list" id="tab-book-list-a" class="button left button-no-margin button-shrinkable"><span class="label" style="color:#000111;">Book list</span></a></li>
			<li><a href="#tab-video-list" id="tab-video-list-a" class="button middle button-shrinkable"><span class="label" style="color:#000111;">Video list</span></a></li>
			<li><a href="#tab-preferences" id="tab-preferences-a" class="button button-inside-wider middle"><span class="label" style="color:#000111;">Preferences</span></a></li>
<?php
	$boundary_button_class_name = 'middle';
	// only in not simplified layout
} else {
	$boundary_button_class_name = 'left';
}
?>

			<li><a href="#tab-history" id="tab-history-a" class="button <?php echo $boundary_button_class_name; ?> button-shrinkable"><span class="label" style="color:#000111;">History</span></a></li>
		</ul>
		<ul class="tab-navigation">
			<li><a href="#tab-help" id="tab-help-a" class="button middle button-shrinkable"><span class="label" style="color:#000111;">Help</span></a></li>
			<li><a href="#tab-about" id="tab-about-a" class="button middle button-shrinkable"><span class="label" style="color:#000111;">About</span></a></li>
			<li><a href="#tab-contact-us" id="tab-contact-us-a" class="button right button-no-margin button-shrinkable"><span class="label" style="color:#000111;">Contact us</span></a></li>
		</ul>
	</div>
	
<div class="tabs">
	
	
<?php
if (!IS__SITE_SIMPLIFIED_LAYOUT) {
?>

	<div id="tab-book-list" class="tab">
		<form id="book-list" name="book-list" method="post" action="<?php echo WEB_INDEX_FILE; ?>">
<?php
			//---------------------------------
			// Book list
			//---------------------------------
			$showBookList	= TRUE;
			$showVideoList	= FALSE;
			include (FS_MODULES_DIR.'/book-list.php');
			
			// Operational data transfer
			foreach ($do['urlKeyValuePairs'] as $key => $value) {
				echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
			}
?>
			<input type="hidden" name="page" value="settings" />
			<input type="hidden" name="tab" value="book_list" />
			<br />
			<a href="javascript:void(0)" onclick="ajaxDataLoader({ 
				url: '<?php echo WEB_INDEX_FILE.'?page=settings&amp;tab=book_list'.$urlData; ?>', 
				id_data: 'book-list', page: 'settings', tab: 'tab-book-list', apply: 1 });" 
				class="button" style="float:right;">
				<span class="label" style="color:#000111;">Apply</span>
			</a>
			<span class="button no-hover settings-message-container"></span>
		</form>
	</div>
	
	
	<div id="tab-video-list" class="tab">
		<form id="video-list" name="video-list" method="post" action="<?php echo WEB_INDEX_FILE; ?>">
<?php
			//---------------------------------
			// Book list
			//---------------------------------
			$showBookList	= FALSE;
			$showVideoList	= TRUE;
			include (FS_MODULES_DIR.'/book-list.php');
			
			// Operational data transfer
			foreach ($do['urlKeyValuePairs'] as $key => $value) {
				echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
			}
?>
			<input type="hidden" name="page" value="settings" />
			<input type="hidden" name="tab" value="video_list" />
			<br />
			<a href="javascript:void(0)" onclick="ajaxDataLoader({ 
				url: '<?php echo WEB_INDEX_FILE.'?page=settings&amp;tab=video_list'.$urlData; ?>', 
				id_data: 'video-list', page: 'settings', tab: 'tab-video-list', apply: 1 });" 
				class="button" style="float:right;">
				<span class="label" style="color:#000111;">Apply</span>
			</a>
			<span class="button no-hover settings-message-container"></span>
		</form>
	</div>
	
	
	<div id="tab-preferences" class="tab">
<?php
	//---------------------------------
	// Preferences data
	//---------------------------------
	$preferences_items = array();
	
	/*
	$m_item = 'autosuggest';
	if (isset($do['siteOperationMode_Values'][$m_item])) {
		$preferences_items[$m_item] = array(
			'title' 		=> 'Autosuggest word order',
			'group_name' 	=> 'm_'.$m_item,
			'data_name' 	=> $m_item,
			'data'			 => array(	0 => array(	'title' => 'Relevance',
													'desc' => 'Most relevant word comes first.'),
										1 => array(	'title' => 'Alphabetic',
													'desc' => 'Words come in alphabetic order.'))
		);
	}*/
	
	$m_item = 'search';
	if (isset($do['siteOperationMode_Values'][$m_item])) {
		$preferences_items[$m_item] = array(
			'title' 		=> 'Search result order',
			'group_name' 	=> 'm_'.$m_item,
			'data_name' 	=> $m_item,
			'data' 			=> array(	0 => array(	'title' => 'Relevance',
													'desc' => 'Most relevant result comes first.'),
										1 => array(	'title' => 'Linear',
													'desc' => 'Results come in the order as they appear in the books.'))
		);
	}
	
	$m_item = 'text';
	if (isset($do['siteOperationMode_Values'][$m_item])) {
		$preferences_items[$m_item] = array(
			'title' 		=> 'Search result text',
			'group_name' 	=> 'm_'.$m_item,
			'data_name' 	=> $m_item,
			'data' 			=> array(	0 => array(	'title' => 'with diacritics',
													'desc' => 'Appears with diacritics'),
										1 => array(	'title' => 'plain English',
													'desc' => 'Appears in plain English'))
		);
	}
	
	$m_item = 'unit';
	if (isset($do['siteOperationMode_Values'][$m_item])) {
		$preferences_items[$m_item] = array(
			'title' 		=> 'Search result unit',
			'group_name' 	=> 'm_'.$m_item,
			'data_name' 	=> $m_item,
			'data' 			=> array(	0 => array(	'title' => 'Paragraph',
													'desc' => 'Search result is a paragraph'),
										1 => array(	'title' => 'Sentence',
													'desc' => 'Search result is a sentence'))
		);
	}
	
	$m_item = 'result_list_tooltip';
	if (isset($do['siteOperationMode_Values'][$m_item])) {
		$preferences_items[$m_item] = array(
			'title' 		=> 'Search result tooltip in 2 columns mode',
			'group_name' 	=> 'm_'.$m_item,
			'data_name' 	=> $m_item,
			'data' 			=> array(	0 => array(	'title' => 'On',
													'desc' => 'Search result items have tooltips in 2 columns mode'),
										1 => array(	'title' => 'Off',
													'desc' => 'No tooltips'))
		);
	}
	
	$m_item = 'result_list_items_number';
	if (isset($do['siteOperationMode_Values'][$m_item])) {
		$preferences_items[$m_item] = array(
			'title' 		=> 'Search result list items number',
			'group_name' 	=> 'm_'.$m_item,
			'data_name' 	=> $m_item,
			'data' 			=> array(	0 => array(	'title' => SEARCH_RESULT_LIST__ITEM_NUM__FEW.' together',
													'desc' => SEARCH_RESULT_LIST__ITEM_NUM__FEW.' search result are displayed at once, more can be loaded later.'),
										1 => array(	'title' => SEARCH_RESULT_LIST__ITEM_NUM__MANY.' together',
													'desc' => SEARCH_RESULT_LIST__ITEM_NUM__MANY.' search result are displayed at once, more can be loaded later.'))
		);
	}
	
	$m_item = 'result_list_text_length';
	if (isset($do['siteOperationMode_Values'][$m_item])) {
		$preferences_items[$m_item] = array(
			'title' 		=> 'Search result list text length',
			'group_name' 	=> 'm_'.$m_item,
			'data_name' 	=> $m_item,
			'data' 			=> array(	0 => array(	'title' => 'Multi line text',
													'desc' => 'Shows a few lines of the paragraph'),
										1 => array(	'title' => 'One line text',
													'desc' => 'Shows one line of the paragraph'))
		);
	}
	
	$m_item = 'result_text_length';
	if (isset($do['siteOperationMode_Values'][$m_item])) {
		$preferences_items[$m_item] = array(
			'title' 		=> 'Result text length',
			'group_name' 	=> 'm_'.$m_item,
			'data_name' 	=> $m_item,
			'data' 			=> array(	0 => array(	'title' => 'Long text',
													'desc' => 'Shows [Book text load] amount of text: By chapter or Full book'),
										1 => array(	'title' => 'Short text',
													'desc' => 'Shows the found paragraph with +-' .
														SITE_RESULT_TEXT_SHORT_NEIGHBOURS_PARAGRAPH_NUM . ' paragraphs around'))
		);
	}
	
	$m_item = 'book_text_load';
	if (isset($do['siteOperationMode_Values'][$m_item])) {
		$preferences_items[$m_item] = array(
			'title' 		=> 'Book text load',
			'group_name' 	=> 'm_'.$m_item,
			'data_name' 	=> $m_item,
			'data' 			=> array(	0 => array(	'title' => 'By chapters',
													'desc' => 'Load a chapter from the book'),
										1 => array(	'title' => 'Full book',
													'desc' => 'Load the full book text'))
		);
	}
	/*
	$m_item = 'book_chapter_tooltip';
	if (isset($do['siteOperationMode_Values'][$m_item])) {
		$preferences_items[$m_item] = array(
			'title' 		=> 'Book\'s chapter tooltip in header',
			'group_name' 	=> 'm_'.$m_item,
			'data_name' 	=> $m_item,
			'data' 			=> array(	0 => array(	'title' => 'On',
													'desc' => 'Book\'s chapter tooltip shows up in the header'),
										1 => array(	'title' => 'Off',
													'desc' => 'No tooltips'))
		);
		$m_book_chapter_tooltip__group_name = $preferences_items[$m_item]['group_name'];
	} else {
		$m_book_chapter_tooltip__group_name = '';
	}*/
	
	$m_item = 'history';
	if (isset($do['siteOperationMode_Values'][$m_item])) {
		$preferences_items[$m_item] = array(
			'title' 		=> 'Search history',
			'group_name' 	=> 'm_'.$m_item,
			'data_name' 	=> $m_item,
			'data' 			=> array(	0 => array(	'title' => 'On',
													'desc' => 'Search history is recorded'),
										1 => array(	'title' => 'Off',
													'desc' => 'Search history is not recorded'))
		);
	}
	
	$m_item = 'button_tooltip';
	if (isset($do['siteOperationMode_Values'][$m_item])) {
		$preferences_items[$m_item] = array(
			'title' 		=> 'Buttons tooltip',
			'group_name' 	=> 'm_'.$m_item,
			'data_name' 	=> $m_item,
			'data' 			=> array(	0 => array(	'title' => 'Off',
													'desc' => 'Hide tooltip on buttons'),
										1 => array(	'title' => 'On',
													'desc' => 'Display tooltip on buttons'))
		);
	}
	
	$m_item = 'search_in_books';
	if (isset($do['siteOperationMode_Values'][$m_item])) {
		$preferences_items[$m_item] = array(
			'title' 		=> 'Search in books',
			'group_name' 	=> 'm_'.$m_item,
			'data_name' 	=> $m_item,
			'data' 			=> array(	0 => array(	'title' => 'On',
													'desc' => 'Search in books is enabled'),
										1 => array(	'title' => 'Off',
													'desc' => 'Search in books is disabled'))
		);
	}
	
	$m_item = 'search_in_videos';
	if (isset($do['siteOperationMode_Values'][$m_item])) {
		$preferences_items[$m_item] = array(
			'title' 		=> 'Search in videos',
			'group_name' 	=> 'm_'.$m_item,
			'data_name' 	=> $m_item,
			'data' 			=> array(	0 => array(	'title' => 'On',
													'desc' => 'Search in videos is enabled'),
										1 => array(	'title' => 'Off',
													'desc' => 'Search in videos is disabled'))
		);
	}
	
	
	$out = '<form id="preferences" name="preferences" method="post" action="'.WEB_INDEX_FILE.'">'.
				'<div id="preferences-data">';
					//'<table>'; // <caption style="padding-top:0px;">Search preferences</caption>
					
	//---------------------------------
	// Collect blocks of preferences
	//---------------------------------
	foreach ($preferences_items as $preferences_item) {
	    
		//$out .= '<tr><th scope="row"><div class="preferences-title">'.$preferences_item['title'].'</div></th>';
		$out .= '<div class="preferences-row">'.
					'<div class="preferences-title">'.$preferences_item['title'].'</div>'.
					'<div class="preferences-options">';
		
		// Each options in the group
		foreach ($preferences_item['data'] as $option_value => $option_data) {
			
		    $id = $preferences_item['group_name'].'_'.$option_value;
			$current_option = $do['siteOperationMode_Values'][$preferences_item['data_name']]==$option_value ? true : false;
			
			//$out .= NEWLINE.'<td id="'.$id.'" class="preferences-item'.($current_option ? ' preferences-item-checked' : '').'"'.
			$out .= NEWLINE.'<div id="'.$id.'" class="preferences-item'.($current_option ? ' preferences-item-checked' : '').'"'.
									' onclick="setFormRadioGroupItem(\'preferences\', \''.
																		$preferences_item['group_name'].'\', \''.
																		$option_value.'\', \''.
																		$id.'\')"'.
									' title="'.$option_data['desc'].'"'.
								'>'.
									'<label for="'.$id.'_input">
										<input type="radio" value="'.$option_value.'"'.
												($current_option ? ' checked="checked"' : '').
												' name="'.$preferences_item['group_name'].'"'.
												' id="'.$id.'_input" />'.
													$option_data['title'].'</label>'.
								//'</td>';
								'</div>';
		}
		//$out .= '</tr>';
		$out .= '</div><div style="clear:both;"></div></div>';
	}
	
	$out .= //'</table>'.
			'</div>';
	
	//---------------------------------
	// Operational data transfer
	//---------------------------------
	foreach ($do['urlKeyValuePairs'] as $key => $value) {
		$out .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
	}
	
	//---------------------------------
	// Write out
	//---------------------------------
	echo $out;
	
	/*  m_button_tooltip: $('input:radio[name=m_button_tooltip]:checked').val() */
?>		
		<input type="hidden" name="page" value="settings" />
		<input type="hidden" name="tab" value="preferences" />
		<input type="hidden" name="preferences" value="1" />
		<br />
<!-- 		<a href="javascript:submit_form('preferences')" class="button" style="float:right;"> -->
		<a href="javascript:void(0)" onclick="ajaxDataLoader({ 
					url: '<?php echo WEB_INDEX_FILE.'?page=settings&amp;tab=preferences'.$urlData; ?>', 
					id_data: 'preferences', 
					page: 'settings', 
					tab: 'tab-preferences', 
					apply: 1,
					m_button_tooltip: $('input:radio[name=m_button_tooltip]:checked').val()
				});" 
			class="button" style="float:right;">
			<span class="label" style="color:#000111;">Apply</span>
		</a>
		<span class="button no-hover settings-message-container"></span>
		
		<div style="border:1px solid #cccccc;padding:4px;clear:both;margin-top:48px;font-size:0.9em;">
			Bookmark link for the actual preferences:
			<br>
			<a href="<?php echo WEB_INDEX_FILE.'?m='.$do['siteOperationMode']; ?>"><?php echo WEB_INDEX_FILE.'?m='.$do['siteOperationMode']; ?></a>
		</div>
	</form>
	</div>
	
	
<?php
} // only in not simplified layout
?>
	
	
	<div id="tab-history" class="tab">
		<h2>Choose one of your recent searches to search it again</h2>
<?php
			//---------------------------------
			// Search History
			//---------------------------------
			echo '<ol id="search-history">';
			
			// Display searches
			foreach ($do['search_history'] as $search_history_pos => $search_text) {
				echo '<li><a href="'.WEB_INDEX_FILE.'?q='.$search_text.
										$do['urlDataTransfer'].
										'&amp;shp='.$search_history_pos.
										'&amp;sq='.SEARCH_BUTTON_TEXT.'">'.urldecode($search_text).'</a></li>';
			}
			echo '</ol>';
?>
		<form id="history" name="history" method="get" action="<?php echo WEB_INDEX_FILE; ?>">
<?php
			// Operational data transfer
			foreach ($do['urlKeyValuePairs'] as $key => $value) {
				echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
			}
?>
			<input type="hidden" name="page" value="settings" />
			<input type="hidden" name="tab" value="history" />
			<br />
			<input type="hidden" name="clear_history" value="1" />
			<a href="javascript:submit_form('history')" class="button" style="width:80px;float:right;">
				<span class="label" style="width:80px;color:#000111;">Clear history</span>
			</a>
		</form>
	</div>
	
	
	
	
	<div id="tab-help" class="tab">
		<p>You can use in the search query: <span class="emphasize">AND</span>, <span class="emphasize">OR</span>, <span class="emphasize">(</span>, 
			<span class="emphasize">)</span>, <span class="emphasize">"</span></p>
		
		<table cellspacing="0" cellpadding="0" align="center">
			<caption>Search query is evaluated from left to right, using the common <span class="emphasize">AND</span>, <span class="emphasize">OR</span> for joining words logically.</caption>
			<thead>
				<tr><th class="firstCol">Query</th><th>Result</th></tr>
			</thead>
			<tbody>
				<tr><td class="firstCol">accept and world</td><td>both words should be in the same paragraph</td></tr>
				<tr><td class="firstCol">accept or world</td><td>at least one of the words should be in the paragraph</td></tr>
				<tr><td class="firstCol">accept or world or harmony</td><td>at least one of the words should be in the paragraph</td></tr>
				<tr><td class="firstCol">accept and world and harmony</td><td>all the words should be in the same paragraph</td></tr>
				<tr><td class="firstCol">accept and world or harmony</td><td>both "accept" and "world" should be in the same paragraph, and possibly "harmony" as well, but not necessarily</td></tr>
			</tbody>
		</table>
		
		<table cellspacing="0" cellpadding="0" align="center">
			<caption>Using <span class="emphasize">"</span> you can set searching for the exact form of the word, or expression (words coming each after in the text)</caption>
			<thead>
				<tr><th class="firstCol">Query</th><th>Result</th></tr>
			</thead>
			<tbody>
				<tr><td class="firstCol">accept</td><td>searching for all the forms of [accept]</td></tr>
				<tr><td class="firstCol">"accept"</td><td>searching for exactly the word [accept]</td></tr>
				<tr><td class="firstCol">"accept the world"</td><td>searching for the expression [accept the world]</td></tr>
			</tbody>
		</table>
		
		<table cellspacing="0" cellpadding="0" align="center">
			<caption>Also with using brackets <span class="emphasize">(</span> and <span class="emphasize">)</span> you can group phrases:</caption>
			<thead>
				<tr><th class="firstCol">Query</th><th>Result</th></tr>
			</thead>
			<tbody>
				<tr><td class="firstCol">(accept or world) and harmony</td><td>paragraph should contain one of the words "accept" or "world", and at the same time "harmony"</td></tr>
			</tbody>
		</table>
		
		<table cellspacing="0" cellpadding="0" align="center">
			<caption>Using all in one:</caption>
			<thead>
				<tr><th class="firstCol">Query</th><th>Result</th></tr>
			</thead>
			<tbody>
				<tr><td class="firstCol">("accept" or world) and "ultimate harmony"</td><td>paragraph should contain 1. exact word [accept] or all the forms of [world], and 2. the expression [ultimate harmony]</td></tr>
			</tbody>
		</table>
		
		<br /><br /><br />
		<hr />
		<br />
		
		<table cellspacing="0" cellpadding="0" align="center">
			<caption>Hotkeys:</caption>
			<thead>
				<tr><th class="firstCol">Hotkey</th><th>Effect</th></tr>
			</thead>
			<tbody>
				<tr><td class="firstCol">Ctrl + Left</td><td>Previous chapter</td></tr>
				<tr><td class="firstCol">Ctrl + Right</td><td>Next chapter</td></tr>
				<tr><td class="firstCol">Shift + o</td><td>Previous result</td></tr>
				<tr><td class="firstCol">Shift + p</td><td>Next result</td></tr>
				<tr><td class="firstCol">Shift + h</td><td>Open [north] panel</td></tr>
				<tr><td class="firstCol">Shift + s</td><td>Open [west] panel</td></tr>
				<tr><td class="firstCol">Shift + f</td><td>Open [south] panel</td></tr>
				<tr><td class="firstCol">Shift + n</td><td>Close [north] panel</td></tr>
				<tr><td class="firstCol">Shift + x</td><td>Close [west] panel</td></tr>
				<tr><td class="firstCol">Shift + v</td><td>Close [south] panel</td></tr>
				<tr><td class="firstCol">Shift + a</td><td>Open [north] panel and [west,south] if they were opened before (Closed all with [Shift + y])</td></tr>
				<tr><td class="firstCol">Shift + y</td><td>Close all panels</td></tr>
				<tr><td class="firstCol">Shift + c</td><td>Clear text for Copy/Paste</td></tr>
				<tr><td class="firstCol">Shift + d</td><td>Reset to original text after Shift+c</td></tr>
<?php
	if (IS__LOCALHOST) {
		echo '
				<tr><td class="firstCol">Shift + l</td><td>Show permanent links to paragraphs (Ctrl+c to copy clicked link)</td></tr>
				<tr><td class="firstCol">Shift + k</td><td>Hide permanent links to paragraphs</td></tr>
		';
	}
?>
				<tr><td class="firstCol">1</td><td>Increase font size</td></tr>
				<tr><td class="firstCol">2</td><td>Reset font size</td></tr>
				<tr><td class="firstCol">3</td><td>Decrease font size</td></tr>
				<tr><td class="firstCol">4</td><td>Increase book text width</td></tr>
				<tr><td class="firstCol">5</td><td>Reset book text width</td></tr>
				<tr><td class="firstCol">6</td><td>Decrease book text width</td></tr>
			</tbody>
		</table>
	</div>
	
	
<?php
	//-----------------------------------------------
	// About, Afterword
	// Contact us
	//-----------------------------------------------
	include_once (FS_PAGES_SITE_PAGES_DIR . '/' . getSiteSpecificContent('settings_about_contact_us__filename'));
	
	
	//-----------------------------------------------
	// Download results
	//-----------------------------------------------
	if (IS__SITE_PERMISSION__DOWNLOAD_RESULTS) {
?>
		<div id="tab-download-results" class="tab">
			
			<form id="download-results" name="download-results" method="get" action="<?php echo WEB_INDEX_FILE; ?>">
				
				<input type="hidden" name="page" value="settings" />
				<input type="hidden" name="tab" value="tab-download-results" />
				<input type="hidden" name="download_results" value="1" />
				<input type="hidden" name="sq" value="<?php echo SEARCH_BUTTON_TEXT; ?>" />
				<?php
					foreach ($do['urlData'] as $key => $value) {
						echo '<input type="hidden" name="'.$key.'" value="'.$do['urlKeyValuePairs'][$key].'" />';
					}
				?>
				
				<table cellspacing="0" cellpadding="0" align="center">
				
					<caption>Download results</caption>
					
					<thead>
						<tr><th class="firstCol">Type</th><th>Options</th></tr>
					</thead>
					<tbody>
						
						<tr><td class="firstCol">Search text</td>
							<td><input 	type="text" id="search-text-download-results" name="q" maxlength="100" size="40" 
										autocomplete="off" 
										style="	width:350px; height:1.4em; font-size:1em;
												padding-left:0.4em; margin:0px;
												border:1px #cccccc solid; float:left; background-color:#fafafa;" />
							</td></tr>
						
						<tr><td class="firstCol">Search unit</td>
							<td><select name="download_results_search_unit">
									<option value="<?php echo SITE_UNIT_TYPE__PARAGRAPH; ?>" select="selected">paragraph</option>
									<option value="<?php echo SITE_UNIT_TYPE__SENTENCE; ?>">sentence</option>
								</select>
							</td></tr>
						
						<tr><td class="firstCol">Books to search in</td>
							<td><select name="download_results_books_search_in">
									<option value="<?php echo ALL_BOOKS__BOOK_ID; ?>" select="selected">all</option>
									<option value="1">selected</option>
								</select>
							</td></tr>
						
						<tr><td class="firstCol">Search text result type</td>
							<td><select name="download_results_search_text_result_type">
									<option value="<?php echo SITE_TEXT_TYPE__DIACRITICS; ?>" select="selected">with diacritics</option>
									<option value="<?php echo SITE_TEXT_TYPE__PLAIN; ?>">plain</option>
								</select>
							</td></tr>
						
						<tr><td class="firstCol">Quote template</td>
							<td><select name="download_results_quote_template">
									<option value="1">quote</option>
									<option value="2">quote: author</option>
									<option value="3">quote: author, book</option>
									<option value="4" selected="selected">quote: author, book, chapter</option>
								</select>
							</td></tr>
					</tbody>
				</table>
				<br />
				
				<input 	type="submit" name="submit" value="Download" 
						style="float:right;width:7em;height:2em;font-weight:normal;font-size:1.1em;cursor:pointer;" />
			</form>
			
			<script type="text/javascript">
				$("#search-text-download-results").val($("#search-text").val());
//				console.log($('#download-results').serialize());
			</script>
		</div>
<?php
	}
	/*<a href="javascript:void(0)" onclick="ajaxDataLoader({ 
				url: '<?php echo WEB_INDEX_FILE.'?'; ?>' + $('#download-results').serialize(), 
				page: 'settings', 
				tab: 'tab-download-results', 
				apply: 1
			});" 
		class="button" style="float:right;">
		<span class="label" style="color:#000111;">Start</span>
	</a>*/
?>

	
</div>
</div>