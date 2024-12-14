<?php
	//-------------------------------------------------------------------------------------------------------------
	// Index page content
	//-------------------------------------------------------------------------------------------------------------
	$urlData = (!empty($do['url_encoded_do_search_text']) ? '&amp;q='.$do['url_encoded_do_search_text'] : '').$do['urlDataTransfer'];
	//$selected_book_id = (isset($do['searchResult_chapter_id']) ? $do[$do['db_table_prefix'].'chapter_id__book_id'][$do['searchResult_chapter_id']] : -1);
	//$selected_book_id = (isset($do['searchResult_book_id']) ? $do['searchResult_book_id'] : -1);
?>

<div id="index-top-container">
	<?php echo getSiteSpecificContent('top_container'); ?>
	
	<div id="index-top-bar"></div>
	
	<div id="index-top-content">
		
		<?php echo getSiteSpecificContent('main_logo', array()); ?>
		
		<form id="search-form" method="post" action="<?php echo WEB_INDEX_FILE; ?>">
			
			<div id="index-search-text-button">
				<div id="search-text-button">
					
					<div id="search-icon">
						<a href="javascript:submit_form('search-form')" class="button button-eye">
							<span class="icon icon-eye icon_eye"></span>
						</a>
					</div>
					
					<input 	type="text" id="search-text" name="q" maxlength="100" size="40" 
							autocomplete="off" value="" 
							onkeydown="keyBoardNav(event, this.id);" 
							onkeyup="autoSuggest(this.id, 'search-list-wrap', 'search-list', 'search-text', 
									event, '<?php echo $do['selected_language_id']; ?>',
									'<?php echo AUTOSUGGEST_LIST_MAX_ITEM_NUMBER_SHOW; ?>');" />
					
					<input type="hidden" name="sq" value="<?php echo SEARCH_BUTTON_TEXT; ?>" />
					
<?php
				foreach ($do['urlData'] as $key => $value) {
					echo '<input type="hidden" name="'.$key.'" value="'.$do['urlKeyValuePairs'][$key].'" />';
				}
				foreach ($do['urlData_indexPage'] as $key => $value) {
					echo '<input type="hidden" name="'.$key.'" value="'.$do['urlKeyValuePairs_indexPage'][$key].'" />';
				}
?>
				</div>
				<div id="search-list-wrap">
					<ul id="search-list"><li></li></ul>
				</div>
			</div>
			
			<div style="clear:both;"></div>
			
			<?php
				// Label under search bar
				echo getSiteSpecificContent('label_under_search_bar', array());
				
				//---------------------------------
				// Book, Video list
				//---------------------------------
				echo '<div class="buttons" style="position:relative; text-align:center;">';
				
				if (!IS__SITE_SIMPLIFIED_LAYOUT) {
					$showBookList = $showVideoList = TRUE;
					include_once (FS_MODULES_DIR.'/book-list.php');
				}
				
				// Languages
				if (IS__SEARCH_IN_VIDEOS) {
					echo Get_Language_Selection_Button($do, false);
				}
				
				echo '</div>';
			?>
		</form>
		
	</div>
</div>
<div id="index-bottom-container">
	<div id="index-bottom-content">
		<?php echo getSiteSpecificContent('bottom_content', array('index_file' => WEB_INDEX_FILE, 'urlData' => $urlData)); ?>
	</div>
</div>