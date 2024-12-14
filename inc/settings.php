<?php
	define ('SITEDATA_FILENAME', 	('sitedata-krsna_search.php')); // in /inc/sitedata/

	$_SERVER_HTTP_USER_AGENT = strtolower($_SERVER['HTTP_USER_AGENT']);
	if (strpos($_SERVER_HTTP_USER_AGENT, 'win') !== FALSE) {
		define ('OPERATING_SYSTEM_TYPE', ('WIN'));
	} else {
		define ('OPERATING_SYSTEM_TYPE', ('LINUX'));
	}
	
	define ('FILE_SYSTEM_ROOT',							(realpath(substr(__DIR__, 0, strrpos(__DIR__, DIRECTORY_SEPARATOR)))));
	define ('FS_ASSETS_DIR',							(FILE_SYSTEM_ROOT.DIRECTORY_SEPARATOR.'assets'));
	define ('FS_ASSETS_COMMON_ROOT',					(FS_ASSETS_DIR.DIRECTORY_SEPARATOR.'common'));
	
	//---------------------------------------------------------------------------------------------------------------------------
	// Site specific content
	//---------------------------------------------------------------------------------------------------------------------------
	define ('SITEDATA_FILE', 		(FILE_SYSTEM_ROOT.DIRECTORY_SEPARATOR.'inc/sitedata/'.SITEDATA_FILENAME));
	include_once (SITEDATA_FILE);

	//---------------------------------------------------------------------------------------------------------------------------
	// Go to production: Online, Other computers
	// 1. Set it (true)
	// 2. Admin full process to produce encrypted tables data
	// 3. Comment these lines out
	// 4. Comment lines out in <datacenter.php> -> SQLite_Connect()
	//---------------------------------------------------------------------------------------------------------------------------
	define ('FORCE_USAGE_ENCRYPTION_TABLE_DATA',	(false));
	
	//---------------------------------------------------------------------------------------------------------------------------
	define ('ENCODING', 	"utf-8");
	define ('ENCODING_STR', "utf8");
	
	// Encoding
	mb_internal_encoding(ENCODING);
	mb_regex_encoding(ENCODING);
	setlocale(LC_ALL, 'en_GB.utf8');
	setlocale(LC_CTYPE, 'en_GB.utf8');
	
	//---------------------------------------------------------------------------------------------------------------------------
	// Localhost - Remote host
	// data: script/root folder, DB
	//---------------------------------------------------------------------------------------------------------------------------
	define ('WEBSITE_ADDRESS', 	getSiteSpecificContent('website_address'));
	define ('WEBSITE_FOLDER', 	getSiteSpecificContent('website_folder'));
	

	//---------------
	// Localhost
	//---------------
	if (($_SERVER["SERVER_NAME"] == 'localhost') or ($_SERVER["SERVER_NAME"] == '127.0.0.1')) {
		
		// if website is in a subfolder of /krsna_search/
		if (($pos = strpos(FILE_SYSTEM_ROOT, WEBSITE_FOLDER)) !== false) {
			$website_sub_folder = substr(FILE_SYSTEM_ROOT, $pos);
		} else {
			echo "<br>Localhost";
			echo "<br>" . 'error: if website is not in a subfolder of /krsna_search/';
			echo "<br>" . FILE_SYSTEM_ROOT;
			echo "<br>" . WEBSITE_FOLDER;
			echo "<br>" . __FILE__.', '.__LINE__;
			exit;
		}
		
		$web_root_path 			= 'http://'.$_SERVER["HTTP_HOST"].'/'.$website_sub_folder;
		$cookieDomain			= '';
		$is_remotehost_dev		= false;
		$is_localhost 			= true;
		$is_download_results 	= getSiteSpecificContent('download_results_localhost');
		
		// Database connection
		$dbConnectionData		= getSiteSpecificContent('dbConnectionData_localhost', array('encoding_str' => ENCODING_STR));
		
	//---------------
	// Remote host
	//---------------
	} else {
		
		// if website is in a subfolder of krishnasearch.com
		$host_script = $_SERVER["HTTP_HOST"].$_SERVER["PHP_SELF"];
		$host_script = substr($host_script, 0, strrpos($host_script, '/'));

		if (($pos = strpos($host_script, WEBSITE_ADDRESS)) !== false) {
			$website_sub_folder = substr($host_script, $pos + strlen(WEBSITE_ADDRESS));
			/*echo "<br>" . WEBSITE_ADDRESS;
			echo "<br>" . $website_sub_folder;
			echo "<br>" . $_SERVER["HTTP_HOST"];
			echo "<br>" . strlen($_SERVER["HTTP_HOST"]);
			echo "<br>" . $pos;
			echo "<br>" . strlen(WEBSITE_ADDRESS);*/
		} else {
			/*echo "<br>Remote host";
			echo "<br>" . 'error: if website is not in a subfolder of krishnasearch.com';
			echo "<br>" . FILE_SYSTEM_ROOT;
			echo "<br>" . WEBSITE_ADDRESS;
			echo "<br>" . $website_sub_folder;
			//writeOut($_SERVER);
			//exit;
			exit;*/
		}
		
		if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
			$server_http_or_https = 'https';
		} else {
			$server_http_or_https = 'http';
		}

		$is_remotehost_dev		= strpos(FILE_SYSTEM_ROOT, getSiteSpecificContent('website_dev_folder_remotehost')) !== false;
		$web_root_path 			= $server_http_or_https.'://'.$_SERVER["HTTP_HOST"].$website_sub_folder;
		$cookieDomain			= '.'.WEBSITE_ADDRESS;
		$is_localhost 			= false;
		$is_download_results 	= getSiteSpecificContent('download_results_remotehost');
		
		// Database connection
		$dbConnectionData		= getSiteSpecificContent('dbConnectionData_remotehost', array('encoding_str' => ENCODING_STR));
	}
	
	
	//----------------------------
	// Files through filesystem
	//----------------------------
	define ('FS_ASSETS_DEV_ROOT',						(FILE_SYSTEM_ROOT.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'dev'));
	define ('FS_ASSETS_PROD_ROOT',						(FILE_SYSTEM_ROOT.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'prod'));
	
	//define ('FS_INCLUDE_DIR',							(FS_ASSETS_COMMON_ROOT.DIRECTORY_SEPARATOR.'inc'));
	define ('FS_INCLUDE_DIR',							(FILE_SYSTEM_ROOT.DIRECTORY_SEPARATOR.'inc'));
	define ('FS_MODULES_DIR',							(FILE_SYSTEM_ROOT.DIRECTORY_SEPARATOR.'modules'));
	define ('FS_PAGES_DIR',								(FILE_SYSTEM_ROOT.DIRECTORY_SEPARATOR.'pages'));
	define ('FS_PAGES_SITE_PAGES_DIR',					(FILE_SYSTEM_ROOT.DIRECTORY_SEPARATOR.'pages'.DIRECTORY_SEPARATOR.'site-pages'));
	define ('FS_NEWS_DIR',								(FILE_SYSTEM_ROOT.DIRECTORY_SEPARATOR.'news'));
	
	define ('FS_CSS_DEV_DIR',							(FS_ASSETS_DEV_ROOT.DIRECTORY_SEPARATOR.'css'));
	define ('FS_CSS_PROD_DIR',							(FS_ASSETS_PROD_ROOT.DIRECTORY_SEPARATOR.'css'));
	define ('FS_JS_DEV_DIR',							(FS_ASSETS_DEV_ROOT.DIRECTORY_SEPARATOR.'js'));
	define ('FS_JS_PROD_DIR',							(FS_ASSETS_PROD_ROOT.DIRECTORY_SEPARATOR.'js'));
	define ('FS_IMG_DEV_DIR',							(FS_ASSETS_DEV_ROOT.DIRECTORY_SEPARATOR.'img'));
	define ('FS_IMG_PROD_DIR',							(FS_ASSETS_PROD_ROOT.DIRECTORY_SEPARATOR.'img'));
	
	define ('FS_CONFIG_DIR',							(FS_INCLUDE_DIR.DIRECTORY_SEPARATOR.'config'));
	define ('FS_FILE_CONFIG_DATA',						(FS_CONFIG_DIR.DIRECTORY_SEPARATOR.'file-config-data.php'));
	
	define ('FS_FILE_HASH_SEPARATOR',					('_'));
	
	// SQLite DB file
	define ('SQLITE_DIR',								(FILE_SYSTEM_ROOT.DIRECTORY_SEPARATOR.'db'));
	define ('SQLITE_DB_DEFAULT', 						(getSiteSpecificContent('sqlite_db_default')));
	define ('SQLITE_FILE_NONCRYPTED_POSTFIX',			('-noncrypted.sqlite'));
	define ('SQLITE_FILE_ENCRYPTED_POSTFIX',			('-encrypted.sqlite'));
	
	
	//----------------------------
	// Files through HTML code
	//----------------------------
	define ('WEB_ROOT',									($web_root_path));
	//define ('ASSETS_SUB_DIR',							($is_localhost ? '/assets'.'/dev' : '/assets'.'/prod'));
	define ('WEB_ASSETS_DEV_ROOT',						(WEB_ROOT.'/assets'.'/dev'));
	define ('WEB_ASSETS_PROD_ROOT',						(WEB_ROOT.'/assets'.'/prod'));
	define ('WEB_ASSETS_ROOT',							(WEB_ROOT.($is_localhost ? '/assets'.'/dev' : '/assets/prod')));
	
	define ('WEB_INC_DIR',								(WEB_ROOT.'/inc'));
	define ('WEB_CSS_DIR',								(WEB_ASSETS_ROOT.'/css'));
	define ('WEB_CSS_DEV_DIR',							(WEB_ASSETS_DEV_ROOT.'/css'));
	define ('WEB_CSS_PROD_DIR',							(WEB_ASSETS_PROD_ROOT.'/css'));
	define ('WEB_JS_DIR',								(WEB_ASSETS_ROOT.'/js'));
	define ('WEB_JS_DEV_DIR',							(WEB_ASSETS_DEV_ROOT.'/js'));
	define ('WEB_JS_PROD_DIR',							(WEB_ASSETS_PROD_ROOT.'/js'));
	define ('WEB_IMG_DIR',								(WEB_ASSETS_ROOT.'/img'));
	define ('WEB_IMG_DEV_DIR',							(WEB_ASSETS_DEV_ROOT.'/img'));
	define ('WEB_IMG_PROD_DIR',							(WEB_ASSETS_PROD_ROOT.'/img'));
	//define ('WEB_CSS_CUSTOM_DIR',						(WEB_ROOT.'/css/custom'));
	
	define ('CSS_BOOK_TEXT_DIR',						('db'));
	// for admin
	//define ('CSS_BOOK_TEXT_SUB_DIR',					(FS_CSS_DEV_DIR.'/'.CSS_BOOK_TEXT_DIR)); // for /admin/
	define ('CSS_MIN_IN_DB_BOOK_TEXT_SUB_DIR',			(DIRECTORY_SEPARATOR.'db'.DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.'css')); // for /admin/
	define ('JS_MIN_IN_DB_BOOK_TEXT_SUB_DIR',			(DIRECTORY_SEPARATOR.'db'.DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.'js')); // for /admin/
	define ('CONFIG_IN_DB_BOOK_TEXT_SUB_DIR',			(DIRECTORY_SEPARATOR.'db'.DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.'config')); // for /admin/
	
	define ('WEB_INDEX_FILE',							(WEB_ROOT.'/index.php'));
	
	define ('COOKIE_DOMAIN',							($cookieDomain));
	define ('IS__LOCALHOST',							($is_localhost));
	define ('IS__REMOTEHOST_DEV',						($is_remotehost_dev));
	
	
	//------------------------------------------------------------------------------------------------------------------------------------------
	// Database: word tables
	//------------------------------------------------------------------------------------------------------------------------------------------
	// Word in diacritics
	define ('WORD_DIACRITICS_TABLE',					('word_diacritics'));				// words with diacritics from books
	
	//------------------------------------------------------------------------------------------------------------------------------------------
	// Database: book tables
	//------------------------------------------------------------------------------------------------------------------------------------------
	define ('AUTHOR_TABLE',								('author'));						// authors
	define ('BOOK_TABLE',								('book'));							// books
	define ('CHAPTER_TABLE',							('chapter'));						// chapters
	define ('LANGUAGE_TABLE',							('language'));						// books
	define ('PARAGRAPH_TYPE_TABLE',						('paragraph_type'));				// paragraph types
	
	// Plain tables
	define ('PARAGRAPH_NONCRYPTED_TABLE',				('paragraph'));						// paragraphs
	
	define ('SENTENCE_TABLE',							('sentence'));						// sentence-paragraph connection
	
	// References
	define ('REFERENCE_PARAGRAPH_ID_TABLE',				('reference_paragraph_id'));		// reference text => paragraph_id
	define ('REFERENCE_INFO_TABLE',						('reference_info'));				// reference text => paragraph_id
	
	// Words
	define ('WORD_BOOKS_LANGUAGES_TABLE',				('word_books_languages'));			// indexed words from books by languages
	define ('WORD_BOOKS_OCCURRENCE_TABLE',				('word_books_occurrence'));			// words data from books (CORE words index data)
	
	// Result sets
	define ('RESULT_SET_ROOT_WORD_TABLE',				('result_set_root_word'));			// root words result sets
	define ('RESULT_SET_CANONICAL_WORD_TABLE',			('result_set_canonical_word'));		// indexed words canonical form result sets
	
	//------------------------------------------------------------------------------------------------------------------------------------------
	// Database: video book tables
	//------------------------------------------------------------------------------------------------------------------------------------------
	define ('VIDEO_AUTHOR_TABLE',						('video_author'));						// authors
	define ('VIDEO_BOOK_TABLE',							('video_book'));						// books
	define ('VIDEO_CHAPTER_TABLE',						('video_chapter'));						// chapters
	define ('VIDEO_LANGUAGE_TABLE',						('video_language'));					// languages
	//define ('VIDEO_PARAGRAPH_TYPE_TABLE',				('video_paragraph_type'));				// paragraph types
	
	// Plain tables
	define ('VIDEO_PARAGRAPH_NONCRYPTED_TABLE',			('video_paragraph'));					// paragraphs
	
	define ('VIDEO_SENTENCE_TABLE',						('video_sentence'));					// sentence-paragraph connection
	
	// References
	define ('VIDEO_REFERENCE_PARAGRAPH_ID_TABLE',		('video_reference_paragraph_id'));		// reference text => paragraph_id
	
	// Words
	define ('VIDEO_WORD_BOOKS_LANGUAGES_TABLE',			('video_word_books_languages'));		// indexed words from books by languages
	define ('VIDEO_WORD_BOOKS_OCCURRENCE_TABLE',		('video_word_books_occurrence'));		// words data from books (CORE words index data)
	
	// Result sets
	define ('VIDEO_RESULT_SET_ROOT_WORD_TABLE',			('video_result_set_root_word'));		// root words result sets
	define ('VIDEO_RESULT_SET_CANONICAL_WORD_TABLE',	('video_result_set_canonical_word'));	// indexed words canonical form result sets
	
	//------------------------------------------------------------------------------------------------
	// Components for Table Fields depending on Site Operation Mode
	//------------------------------------------------------------------------------------------------
	define ('TABLE_NAME_COMPONENT__VIDEO',				('video_'));
	define ('TABLE_NAME_COMPONENT__ENCRYPTED',			('_encrypted'));
	
	//------------------------------------------------------------------------------------------------
	// Permissions
	//------------------------------------------------------------------------------------------------
	define ('IS__SITE_PERMISSION__DOWNLOAD_RESULTS', 	$is_download_results);
	
	
	
	//--------------------------------------------------------------------------------------------------------------------------------
	// Variables
	//--------------------------------------------------------------------------------------------------------------------------------
	// for TXT files only
	// New line character to force breaking the line in the same paragraph
	// Decimal code should be under 128
	// 124d, 174o, 7Ch, |, &#124;, Vertical bar
	define ('CHAR_NEWLINE_IN_PARAGRAPH',				('|'));	//chr(CODE_NEWLINE_IN_PARAGRAPH);
	define ('PARAGRAPH_TYPE_NAME_FOR_QUOTE', 			('Indented-Paragraph'));
	define ('PARAGRAPH_TYPE_SHLOKA', 					(1));
	
	define ('NEWLINE', 									("\r\n"));
	define ('NO_WORD_IN_SEARCH_TEXT', 					('This column displays search results.'));
	
	define ('SETTINGS_APPLY_BUTTON_TEXT',				('Apply'));
	define ('SEARCH_BUTTON_TEXT',						('Search'));
	define ('CLEAR_HISTORY_BUTTON_TEXT',				('Clear history'));
	
	// Word types
	define ('ROOT_WORD_WORD_TYPE',						(0));	// search for the word group with inflection
	define ('EXACT_WORD_WORD_TYPE',						(1));	// search for exactly the word
	
	// Book ID of all books in any table
	define ('NO_BOOKS__BOOK_ID',						('-'));	// Book ID of no books selected
	define ('ALL_BOOKS__BOOK_ID',						(0));	// Book ID of all books in any table
	define ('BOOK_LIST_CODE_ALLOWED_CHARS',				('0-9a-f\-')); // regex. chars accepted for book list code
	
	// Search
	define ('HIGHLIGHTED_WORDS_DATA_SEPARATOR',			(':'));
	define ('HIGHLIGHTED_WORDS_SEPARATOR',				('|'));	// soul:1|is:0|immortal:1
	
	// How many values can be inserted in SQL query at the same time with place holders (?)
	define ('SELECT_IN_VALUE_NUMBER_THRESHOLD_IN_SQL',	(100));
	
	// Word suggestion
	define ('WORD_SUGGESTION_BEGIN_CHAR_NUM_GOOD',		(3));
	
	// Search result list
	//define ('SEARCH_RESULT_CROPPED_MAX_CHAR_NUM_LONG',	(240));
	//define ('SEARCH_RESULT_CROPPED_MAX_CHAR_NUM_SHORT',	(60));
	define ('SEARCH_RESULT_LIST__ITEM_NUM__LOCALHOST__FEW',		(50));//(2));//(40));
	define ('SEARCH_RESULT_LIST__ITEM_NUM__LOCALHOST__MANY',	(200));
	define ('SEARCH_RESULT_LIST__ITEM_NUM__REMOTE__FEW',		(50));
	define ('SEARCH_RESULT_LIST__ITEM_NUM__REMOTE__MANY',		(200));
	
	// Video book chapter max paragraph number when loading full chapter text
	define ('VIDEO_BOOK_CHAPTER_MAX_PARAGRAPH_NUM__FULL_CHAPTER_LOAD',	(100));

	// Search process
	//define ('REGEX__WORD_PART',							('0-9a-zA-Z\p{L}\p{M}*'));
	define ('REGEX__WORD_PART',							('0-9a-zA-Z\p{L}\p{M}'));
	/*define ('REGEX__WORD_MB',							(
															'('.
																'['.REGEX__WORD_PART.'()"]+'.
																'(['.REGEX__WORD_PART.'\-_]*['.REGEX__WORD_PART.'()"]+)?'.
															')'.
															'(\'['.REGEX__WORD_PART.'()"]+)?'
														));*/
	define ('REGEX__WORD',								(
															'/('.
																'['.REGEX__WORD_PART.'()"]+'.
																'(['.REGEX__WORD_PART.'\-_]*['.REGEX__WORD_PART.'()"]+)?'.
															')'.
															'(\'['.REGEX__WORD_PART.'()"]+)?/u'
														));
	//define ('SEARCH_QUERY_ALLOWED_CHARS_MAIN',			('a-zA-Z\p{L}\p{M}()’\'.,;:?!+“”"„\—\-')); // regex. chars accepted in query
	//define ('SEARCH_QUERY_ALLOWED_CHARS_MAIN',			('\p{L}\p{M}*()’\'.,;:?!+“”"„\—\-')); // regex. chars accepted in query
	//define ('SEARCH_QUERY_ALLOWED_CHARS_MAIN',			('\p{L}\p{M}*()\-"')); // regex. chars accepted in query
	//define ('SEARCH_QUERY_ALLOWED_CHARS',				(SEARCH_QUERY_ALLOWED_CHARS_MAIN.'\s')); // regex. chars accepted in query
	//define ('SEARCH_QUERY_PROCESSING_ALLOWED_CHARS',	(SEARCH_QUERY_ALLOWED_CHARS_MAIN.'_')); // regex. chars allowed during processing query ( [_] is for in exact expressions instead of [ ])
	
	// Cross reference tag
	define ('CROSS_REFERENCE__TAG_ID', 					('id_link'));		// e.g. <a id_link="original_source__eternal_play">
	define ('CROSS_REFERENCE__TAG_VALUE_SEPARATOR', 	('#'));				// e.g. <a id_link="1#40">
	define ('CROSS_REFERENCE__ALLOWED_CHARS',			('a-zA-Z0-9_\-')); 	// regex. chars accepted for cross-reference
	
	// Reference info tag
	define ('REFERENCE_INFO__TAG_ID', 					('id_reference'));	// e.g. <a id_reference="srimad_bhagavatam">
	define ('REFERENCE_INFO__ALLOWED_CHARS',			('a-zA-Z0-9_\-')); 	// regex. chars accepted for reference info
	
	// Video tag
	define ('VIDEO_REFERENCE__TAG_ID', 					('id_video_link'));	// e.g. <a id_video_link="zKa-ttrvbjc|2s">
	define ('VIDEO_REFERENCE_VIDEO_ID_ALLOWED_CHARS',	('a-zA-Z0-9_\-')); 	// regex. chars accepted for video reference 	=> zKa-ttrvbjc
	define ('VIDEO_REFERENCE_VIDEO_BEGIN_ALLOWED_CHARS',('a-z0-9')); 		// regex. chars accepted for video time			=> 2s
	define ('VIDEO_BOOK_TEXT__VIDEOID_TIME_SEPARATOR',	('×'));
	
	// Cross reference tag and Video tag
	define ('CROSS_REFERENCE_VIDEO_REFERENCE__TAG_ID', 	('id_link_video_link'));		// e.g. <a id_link_video_link="original_source__eternal_play|zKa-ttrvbjc|2s">
	
	define('DIV_CLASS_NAME_BEGIN',						('b'));
	define('SPAN_CLASS_NAME_BEGIN',						('e'));
	
	
	// Autosuggest
	//define ('AUTOSUGGEST_ALLOWED_CHARS',				('a-z’\'.,;:?!“”"„\—\-\s')); // regex. chars accepted during processing autosuggestion
	//define ('AUTOSUGGEST_SHORTEST_SUBTEXT_LENGTH',		(1));
	//define ('AUTOSUGGEST_LONGEST_SUBTEXT_LENGTH',		(5));
	//define ('AUTOSUGGEST_SUBTEXT_END_CHAR',				('-'));
	//define ('AUTOSUGGEST_RESULTS_SEPARATOR',			('#'));
	
	define ('AUTOSUGGEST_LIST_MAX_ITEM_NUMBER',					(8));
	define ('AUTOSUGGEST_LIST_RELEVANCE_MAX_ITEM_NUMBER',		(8));
	define ('AUTOSUGGEST_LIST_LINEAR_MAX_ITEM_NUMBER',			(20));
	
	define ('AUTOSUGGEST_LIST_MAX_ITEM_NUMBER_SHOW',			(4));
	//define ('AUTOSUGGEST_LIST_RELEVANCE_MAX_ITEM_NUMBER_SHOW',	(4));
	//define ('AUTOSUGGEST_LIST_LINEAR_MAX_ITEM_NUMBER_SHOW',		(4));
	
	//define ('AUTOSUGGEST_SEARCH_FOR_WORDS_MIN_CHAR_NUM',		(2));	// min. characters number for searching for words during autosuggest
	//define ('AUTOSUGGEST_SEARCH_FOR_EXPRESSIONS_MIN_CHAR_NUM',	(4));	// min. characters number for searching for expressions during autosuggest
	
	// Search history
	define ('SEARCH_HISTORY_POS_DATA_SEPARATOR',		('#'));
	define ('SEARCH_HISTORY_DATA_ITEMS_SEPARATOR',		('|'));
	
	// Language
	define ('DEFAULT_LANGUAGE_CODE',							('en-GB')); // English
	define ('IS_ALL_ENGLISH_VARIATION_SAME_TEXT',				(true));	// English (United Kingdom) => English
	
	// Compression for word book paragraph/sentence data
	define ('GZCOMPRESS_LEVEL',									(6));
	define ('SEPARATOR__WORD_BOOK_PS_DATA__BOOK__BOOK',			('|'));
	define ('SEPARATOR__WORD_BOOK_PS_DATA__BOOK_ID__BOOK_DATA',	(':'));
	define ('SEPARATOR__WORD_BOOK_PS_DATA__PS_DATA__PS_DATA',	(';'));
	define ('SEPARATOR__WORD_BOOK_PS_DATA__PS_ID__RELEVANCE',	('-'));
	
	// Author name form in search results header
	$author_name_forms = array('author_full_name', 'author_short_name', 'author_abbreviated_name');
	define ('AUTHOR_NAME_FORM',									($author_name_forms[0]));
	
	// Books' links on Gaudiyadarshan
	//define ('BOOKS_EN_URL_BEGIN_ON_GAUDIYADARSHAN',		('http://www.gaudiyadarshan.com/shop/all_english_books/'));
	//define ('BOOKS_BY_SGOVM_URL_ON_GAUDIYADARSHAN',		('http://www.gaudiyadarshan.com/shop/product-category/books_by_bs_govinda_maharaj/'));
	//define ('BOOKS_BY_SGURUM_URL_ON_GAUDIYADARSHAN',		('http://www.gaudiyadarshan.com/shop/product-category/books_by_br_sridhar_maharaj/'));
	
	// Email
	define ('EMAIL_ADDRESS_CONTACT_US', 				(getSiteSpecificContent('email_address_contact')));
	define ('EMAIL_NEWLINE', 							("\n"));
	
	//--------------------------------------------------------------------------------------------------------------------------------
	// DEBUG
	//--------------------------------------------------------------------------------------------------------------------------------
	define ('DEBUG_OPERATION_SHOW',						(false));
	define ('DEBUG_OPERATION_RESULT_SHOW',				(false));
	
	
	
	//--------------------------------------------------------------------------------------------------------------------------------
	// GET -> manage only what is coming
	//--------------------------------------------------------------------------------------------------------------------------------
	// Contains values passed through url, used during operation (set default first)
	$do = array(
		
		'simplified_layout'				=> false,				// simplified layout and behaviour
		
		// simplified layout
		'book_reading_layout'			=> false,				// simplified layout and behaviour with book reading extend (buttons etc. from non-simplified layout)
		//'video_results'					=> true,				// simplified layout and behaviour with video results
		'update_header'					=> false,
		
		// video books
		'video_captions'				=> false,				// video: usage in general: search in video captions, display video books also
		'video'							=> false,				// video: action with link: begin with video captions / books selection, etc.

		'first_result_from_videos' => false,

		// books or video books variables
		'db_table_prefix'				=> '',					// books/video books DB
		'url_is_video_books_addon'		=> '',					// local usage: books: '',			video books: '&amp;video=1'
		
		// common variables
		'session_clear' 				=> 0,
		'search_query' 					=> false,
		'search_text' 					=> '',					// raw (from URL) and later cleaned search text
		//'search_text_standard' 			=> '',					// search text with standarized diacritic words
		'search_text_canonicalA' 		=> array(),				// search text with standarized diacritic words in array
		'url_encoded_do_search_text' 	=> '',					// search text encoded for transfering in URL
		'search_history' 				=> array(),				// search text history
		'search_history_pos' 			=> -1,					// search text history position of current viewed query
		
		'searchBookListCode' 			=> ALL_BOOKS__BOOK_ID,	// 0:all
		'searchBookListNum' 			=> 0,					// how many books are in the search pool
		'searchBooksIdList' 			=> null,				// books id list
		'searchVideoListCode' 			=> ALL_BOOKS__BOOK_ID,	// 0:all
		'searchVideoListNum' 			=> 0,					// how many books are in the search pool
		'searchVideosIdList' 			=> null,				// books id list
		//'searchResultList_showPage' => null,
		
		'searchBooksPartial'			=> false,				// all (false) or selected (true)  books to search in
		'searchVideosPartial'			=> false,				// all (false) or selected (true)  books to search in
		
		//'searchResult_language_code'	=> null,
		//'searchResult_book_name_url'	=> null,
		'searchResult_language_id' 		=> null,				// determine language
		'searchResult_author_id' 		=> null,				// determine author
		'searchResult_book_id' 			=> null,				// determine book
		'searchResult_chapter_id' 		=> null,				// book's chapter id
		'searchResult_paragraph_id' 	=> null,				// book's paragraph id
		'searchResult_sentence_id' 		=> null,				// book's sentence id
		'searchResult_psCurrentNumber' 	=> null,
		
		'searchResultList_loadAll'		=> false,
		'searchResultList_loadNext'		=> false,
		
		'highlight_words_search'		=> array(),
		'book_text_load__paragraph_before'	=> null,
		'book_text_load__paragraph_after'	=> null,
		
		'siteOperationMode' 			=> 0,
		'siteOperationMode_Values' 		=> array(
			'search_in_videos'				=> 0,	// 0=on,				1=off
			'search_in_books'				=> 0,	// 0=on,				1=off
			'button_tooltip'				=> 0,	// 0=off,				1=on
			'history' 						=> 0,	// 0=on,				1=off
			'book_text_load' 				=> 0,	// 0=by chapters, 		1=full book
			'result_text_length' 			=> 0,	// 0=long, 				1=short	(long: [book_text_load] amount of text) (short: target +-10 paragraph)
			'result_list_text_length' 		=> 0,	// 0=long,				1=short (long: few lines, max the paragraph text length) (short: 1 line)
			'result_list_items_number'		=> 0,	// 0=few				1=many	(set above)
			'result_list_tooltip' 			=> 0,	// 0=on, 				1=off
			'unit' 							=> 0,	// 0=paragraph, 		1=sentence
			'text' 							=> 0,	// 0=with diacritics, 	1=plain English
			'search' 						=> 0),//, 	// 0=relevance, 		1=linear
			//'autosuggest' 					=> 0), 	// 0=relevance, 		1=linear
		
		'siteOperationMode_SearchMode_Changes' => false,
		'siteRunningOnTouchDevice'		=> false,	// website is running on Touch device or not
		'siteRunningOnSmallDevice'		=> false,	// website is running on Small device or not (under 600px width & height)
		'fixpage' 						=> null,
		'one_col'						=> false,
		'cross_reference_id'			=> null,	// cross-reference link to other part of the books
		'reference_info_id'				=> null,	// reference info id for displaying additional information
		'urlData' 						=> array(),	// key/values necessary to transfer through pages in links
		'urlDataTransfer' 				=> '',		// key/values necessary to transfer through pages in links in prepared string
		'urlKeyValuePairs' 				=> array(),	// Contains [url-key] => [url-value] pairs
		'urlKeyValuePairs_indexPage' 	=> array(),	// Contains [url-key] => [url-value] pairs (Only on Index page)
		'urlData_indexPage' 			=> array(),	// key/values necessary to transfer through pages in links  (Only on Index page)
		'pageHeaderMetaDescription'		=> 'Text searching and reading',		// in <header.php> page description text
		'languages'						=> array(),	// languages used
		'selected_language_id'			=> NULL,
		
		// 'html_tag_reserved_words': words occurs in html tags during search/replace for highlight needs special care
		'html_tag_reserved_words'		=> array('name'=>1, 'class'=>1, 'id'=>1, 'div'=>1, 'span'=>1, 'a'=>1,
												'id_link'=>1, 'id_reference'=>1, 'id_video_link'=>1, 'id_link_video_link'=>1),
		
		// Font used in books (non-English)
		'font_code__font_name' 			=> array(0 => '', 1 => 'nadia'),
		'font_name__font_code' 			=> array('nadia' => 1)
	);
	// Site Operation Mode values
	define ('SITE_OPERATION_MODE_COMPONENT_NUM',				(count($do['siteOperationMode_Values'])));
	define ('SITE_OPERATION_MODE_MAX_NUM',						(pow(2, SITE_OPERATION_MODE_COMPONENT_NUM)-1));//(4095));	// pow(2, SITE_OPERATION_MODE_COMPONENT_NUM)-1
	//define ('SITE_AUTOSUGGEST_SEARCH_TYPE__RELEVANCE',			(0));	// autosuggest, search type
	//define ('SITE_AUTOSUGGEST_SEARCH_TYPE__LINEAR',				(1));	// autosuggest, search type
	define ('SITE_SEARCH_TYPE__RELEVANCE',						(0));	// search type
	define ('SITE_SEARCH_TYPE__LINEAR',							(1));	// search type
	//define ('SITE_LAYOUT_TYPE__ONE_COLUMN',						(0));	// layout type
	//define ('SITE_LAYOUT_TYPE__TWO_COLUMNS',					(1));	// layout type
	define ('SITE_TEXT_TYPE__DIACRITICS',						(0));	// text type
	define ('SITE_TEXT_TYPE__PLAIN',							(1));	// text type
	define ('SITE_UNIT_TYPE__PARAGRAPH',						(0));	// paragraph type
	define ('SITE_UNIT_TYPE__SENTENCE',							(1));	// sentence type
	define ('SITE_RESULT_LIST_TOOLTIP__ON',						(0));
	define ('SITE_RESULT_LIST_TOOLTIP__OFF',					(1));
	define ('SITE_RESULT_LIST_ITEMS_NUM__FEW',					(0)); // few pieces
	define ('SITE_RESULT_LIST_ITEMS_NUM__MANY',					(1)); // many pieces
	define ('SITE_RESULT_LIST_TEXT_LENGTH_TYPE__LONG_TEXT',		(0)); // few lines
	define ('SITE_RESULT_LIST_TEXT_LENGTH_TYPE__SHORT_TEXT',	(1)); // 1 line
	define ('SITE_RESULT_TEXT_LENGTH_TYPE__LONG_TEXT',			(0)); // [book_text_load] amount of text
	define ('SITE_RESULT_TEXT_LENGTH_TYPE__SHORT_TEXT',			(1)); // target +-10 paragraphs
	define ('SITE_BOOK_TEXT_LOAD_TYPE__BY_CHAPTERS',			(0));
	define ('SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK',				(1));
	define ('SITE_BUTTON_TOOLTIP__ON',							(1));
	define ('SITE_HISTORY_TYPE__ON',							(0));
	define ('SITE_HISTORY_TYPE__OFF',							(1));
	
	define ('SITE_SETTINGS_VALUE__ON',							(0));
	define ('SITE_SETTINGS_VALUE__OFF',							(1));
	
	define ('SITE_RESULT_TEXT_SHORT_NEIGHBOURS_PARAGRAPH_NUM',							(10)); // target +-10 paragraphs
	define ('SITE_RESULT_TEXT_SHORT_NEIGHBOURS_PARAGRAPH_NUM__SITE_SIMPLIFIED_LAYOUT',	(3)); // target +-3 paragraphs
	
	define ('IS__GET_MAGIC_QUOTES_GPC__ON',						(FALSE));//get_magic_quotes_gpc())); // for stripslashes() text
	
	// Need to set cookies
	$needSetCookie = array(/*'c_blc' => false, */'c_m' => false, 'c_sh' => false, 'remove_c_sh' => false);
	
	// Session clear
	/*
	if (isset($_GET['sc'])) {
		if (intval($_GET['sc']) == 1) {
			if (isset($_SESSION)) {
				foreach ($_SESSION as $session_key => $session_value) {
					unset($_SESSION[$session_key]);
				}
				session_destroy();
			}
		}
	}*/
	
	//--------------------------------------------------------------------------------------------------------------------------------
	// variables for
	//
	//  1. Video captions
	//		- video_captions=1			=> use video captions
	//		- video_results=1			=> show video results
	//
	//  2. Simplified layout
	//		- simplified_layout=1		=> build simplified layout (no UI, no resizer, etc.)
	//		- book_reading_layout=0		=> simple header
	//		- book_reading_layout=1		=> original header (book, chapter selection etc.)
	//
	//--------------------------------------------------------------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------------------------------------------------------------
	// Video books
	//--------------------------------------------------------------------------------------------------------------------------------
	// Search in video captions, display video books
	/*
	if (isset($_GET['video_captions']) or isset($_POST['video_captions'])) {
		$t = (isset($_GET['video_captions']) ? 
					$_GET['video_captions'] : (isset($_POST['video_captions']) ? $_POST['video_captions'] : ''));
		//$do['urlData']['video_captions'] 			= 'video_captions='.$do['video_captions'];
		//$do['urlKeyValuePairs']['video_captions'] 	= $do['video_captions'];
	}
	*/
	//if (IS__SEARCH_IN_VIDEOS) {
	//	$do['video_captions'] = (intval($t)==1 ? true : false);
	//}
	
	// Video captions / books selection, etc.
	if (isset($_GET['video'])) {
		$do['video'] = (intval($_GET['video']) == 1 ? true : false);
	}
	//--------------------------------------------------------------------------------------------------------------------------------
	
	//--------------------------------------------------------------------------------------------------------------------------------
	// Language
	//--------------------------------------------------------------------------------------------------------------------------------
	$do['selected_language_id'] = NULL;
	// New
	/*
	if (isset($_GET['new_lang']) or isset($_POST['new_lang'])) {
		$t = intval((isset($_GET['new_lang']) ? $_GET['new_lang'] : (isset($_POST['new_lang']) ? $_POST['new_lang'] : NULL)));
		if ($t < 1) { $t = NULL; }
		// max value check later in <tools.php> Get_Books_data()
		$do['selected_language_id'] = $t;
	
	}*/
	
	// Transferred
	if (isset($_GET['l']) or isset($_POST['l'])) {
		$t = intval((isset($_GET['l']) ? $_GET['l'] : (isset($_POST['l']) ? $_POST['l'] : NULL)));
		if ($t < 1) { $t = NULL; }
		// max value check later in <tools.php> Get_Books_data()
		$do['selected_language_id'] = $t;
	}
	
	
	
	//--------------------------------------------------------------------------------------------------------------------------------
	// Simplified website (layout, behaviour)
	//--------------------------------------------------------------------------------------------------------------------------------
	// Simplified layout
	//----------------------------------------
	if (isset($_GET['simplified_layout']) or isset($_POST['simplified_layout'])) {
		$t = (isset($_GET['simplified_layout']) ? 
					$_GET['simplified_layout'] : (isset($_POST['simplified_layout']) ? $_POST['simplified_layout'] : 0));
		$do['simplified_layout'] 						= (intval($t) == 1 ? true : false);
		$do['urlData']['simplified_layout']	 			= 'simplified_layout='.$do['simplified_layout'];
		$do['urlKeyValuePairs']['simplified_layout'] 	= $do['simplified_layout'];
	}
	
	//----------------------------------------
	// Video results
	//----------------------------------------
	/*$t1 = $t2 = null;
	if (isset($_COOKIE['video_results'])) {
		$t1 = intval($_COOKIE['video_results']) == 1 ? 1 : 0;
		$t2 = $t1;
	}
	if (isset($_GET['video_results']) or isset($_POST['video_results'])) {
		$t2 = intval(isset($_GET['video_results']) ? 
						$_GET['video_results'] : (isset($_POST['video_results']) ? 
								$_POST['video_results'] : 0)) == 1 ? 1 : 0;
	}
	if (isset($t2)) {
		$do['video_results'] 										= $t2;
		$do['urlData_indexPage']['video_results'] 					= 'video_results='.$do['video_results'];
		$do['urlKeyValuePairs_indexPage']['video_results'] 			= $do['video_results'];
		$needSetCookie['video_results'] 							= !isset($_COOKIE['video_results']) or ($t1 != $t2);
	}*/
	
	//----------------------------------------
	// Book reading layout
	//----------------------------------------
	$t1 = $t2 = null;
	if (isset($_COOKIE['book_reading_layout'])) {
		$t1 = intval($_COOKIE['book_reading_layout']) == 1 ? 1 : 0;
		$t2 = $t1;
	}
	if (isset($_GET['book_reading_layout']) or isset($_POST['book_reading_layout'])) {
		$t2 = intval(isset($_GET['book_reading_layout']) ? 
					$_GET['book_reading_layout'] : (isset($_POST['book_reading_layout']) ? 
							$_POST['book_reading_layout'] : 0)) == 1 ? 1 : 0;
	}
	if (isset($t2)) {
		$do['book_reading_layout'] 									= $t2;
		$do['urlData_indexPage']['book_reading_layout'] 			= 'book_reading_layout='.$do['book_reading_layout'];
		$do['urlKeyValuePairs_indexPage']['book_reading_layout'] 	= $do['book_reading_layout'];
		$needSetCookie['book_reading_layout'] 						= !isset($_COOKIE['book_reading_layout']) or ($t1 != $t2);
	}
	
	//----------------------------------------
	// Change layout (book reading or not)
	//----------------------------------------
	if (isset($_GET['update_header'])) {
		$do['update_header'] = (intval($_GET['update_header']) == 1 ? 1 : 0);
	}
	
	//--------------------------------------------------------------------------------------------------------------------------------
	// Download results
	//--------------------------------------------------------------------------------------------------------------------------------
	if (isset($_GET['download_results'])) {
		$do['download_results'] = (intval($_GET['download_results']) == 1 ? true : false);
	} else {
		$do['download_results'] = false;
	}
	
	
	//--------------------------------------------------------------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------------------------------------------------------------
	// Common variables
	//--------------------------------------------------------------------------------------------------------------------------------
	// Search button
	// [a-zA-Z] remains
	if (isset($_GET['sq']) or isset($_POST['sq'])) {
		$t = (isset($_GET['sq']) ? $_GET['sq'] : (isset($_POST['sq']) ? $_POST['sq'] : ''));
		$do['search_query'] = ($t==SEARCH_BUTTON_TEXT ? true : false);
		//$do['urlKeyValuePairs']['sq'] = SEARCH_BUTTON_TEXT;
	}
	if (isset($_GET['sq_x'])) {
		$do['search_query'] = true;
		//$do['urlKeyValuePairs']['sq'] = SEARCH_BUTTON_TEXT;
	}
	// Search text
	// [a-zA-Z"() ...] remains
	if (isset($_GET['q']) or isset($_POST['q'])) {
		$t = (isset($_GET['q']) ? $_GET['q'] : (isset($_POST['q']) ? $_POST['q'] : ''));
		
		$do['search_text'] = str_replace(array('+'), array(' '), 
								(IS__GET_MAGIC_QUOTES_GPC__ON ? stripslashes($t) : $t));
		//$do['search_text'] = str_replace(array('&quot;', '+'), array('"', ' '), $_GET['q']);		// &quot; => "
		//$do['search_text'] = mb_ereg_replace('[^'.SEARCH_QUERY_ALLOWED_CHARS.']', '', $do['search_text']);
		//$do['search_text'] = str_replace('"', '&quot;', $do['search_text']);	// " => &quot;
		$do['urlKeyValuePairs']['q'] = $do['search_text'];
	}
	
	/*
	// Search result list page to show
	// [0-9] remains
	if (isset($_GET['lp'])) {
		$do['searchResultList_showPage'] = $_GET['lp'];
		$do['searchResultList_showPage'] = intval($do['searchResultList_showPage']);
		if ($do['searchResultList_showPage'] < 0) { $do['searchResultList_showPage'] = 0; }
		$do['urlKeyValuePairs']['lp'] = $do['searchResultList_showPage'];
	}*/
	
	// Search result paragraph current order number
	if (isset($_GET['rn'])) {
		$do['searchResult_psCurrentNumber'] = intval($_GET['rn']);
		if ($do['searchResult_psCurrentNumber'] < 1) { $do['searchResult_psCurrentNumber'] = 1; }
		$do['urlKeyValuePairs']['rn'] = $do['searchResult_psCurrentNumber'];
	}
	
	/*
	// Search result Language code
	if (isset($_GET['lang'])) {
		$do['searchResult_language_code'] = $_GET['lang'];
		$do['urlKeyValuePairs']['l'] = $do['searchResult_language_code'];
	}
	
	// Search result Book url name
	if (isset($_GET['book'])) {
		$do['searchResult_book_name_url'] = $_GET['book'];
		$do['urlKeyValuePairs']['b'] = $do['searchResult_book_name_url'];
	}
	*/
	
	// Search result Book id
	if (isset($_GET['b'])) {
		$do['searchResult_book_id'] = intval($_GET['b']);
		if ($do['searchResult_book_id'] < 1) { $do['searchResult_book_id'] = 1; }
		$do['urlKeyValuePairs']['b'] = $do['searchResult_book_id'];
	}
	
	// Search result Book Chapter id
	if (isset($_GET['c'])) {
		$do['searchResult_chapter_id'] = intval($_GET['c']);
		if ($do['searchResult_chapter_id'] < 1) { $do['searchResult_chapter_id'] = 1; }
		$do['urlKeyValuePairs']['c'] = $do['searchResult_chapter_id'];
	}
	
	// Search result Paragraph id
	if (isset($_GET['p'])) {
		$do['searchResult_paragraph_id'] = intval($_GET['p']);
		if ($do['searchResult_paragraph_id'] < 1) { $do['searchResult_paragraph_id'] = 1; }
		$do['urlKeyValuePairs']['p'] = $do['searchResult_paragraph_id'];
	}
	
	// Search result Sentence id
	if (isset($_GET['s'])) {
		$do['searchResult_sentence_id'] = intval($_GET['s']);
		if ($do['searchResult_sentence_id'] < 1) { $do['searchResult_sentence_id'] = 1; }
		$do['urlKeyValuePairs']['s'] = $do['searchResult_sentence_id'];
	}
	
	// Search result list load all (override set limit)
	if (isset($_GET['rl_all'])) {
		$do['searchResultList_loadAll'] = intval($_GET['rl_all']);
		$do['searchResultList_loadAll'] = (($do['searchResultList_loadAll'] == 1) ? true : false);
	}
	// Search result list load next bunch
	if (isset($_GET['rl_next'])) {
		$do['searchResultList_loadNext'] = intval($_GET['rl_next']);
		$do['searchResultList_loadNext'] = (($do['searchResultList_loadNext'] == 1) ? true : false);
	}
	// Search result list loaded item nummbers
	if (isset($_GET['rl_loaded_num'])) {
		$do['searchResultList_loaded_num'] = intval($_GET['rl_loaded_num']);
		if ($do['searchResultList_loaded_num'] < 1) { $do['searchResultList_loaded_num'] = 0; }
	} else {
		$do['searchResultList_loaded_num'] = 0;
	}
	
	// Limited paragraph display: load locally paragraphs from the beginning of the chapter
	if (isset($_GET['p_before'])) {
		$do['book_text_load__paragraph_before'] = intval($_GET['p_before']);
		if ($do['book_text_load__paragraph_before'] < 2) { $do['book_text_load__paragraph_before'] = 2; }
	}
	
	// Limited paragraph display: load locally paragraphs from the end of the chapter
	if (isset($_GET['p_after'])) {
		$do['book_text_load__paragraph_after'] = intval($_GET['p_after']);
		if ($do['book_text_load__paragraph_after'] < 2) { $do['book_text_load__paragraph_after'] = 2; }
	}
	
	// Cross-reference links
	if (isset($_GET['cross_reference_id'])) {
		$do['cross_reference_id'] = mb_ereg_replace('[^'.CROSS_REFERENCE__ALLOWED_CHARS.']', '', $_GET['cross_reference_id']);
	}
	
	// Reference info id
	if (isset($_GET['reference_info_id'])) {
		$do['reference_info_id'] = mb_ereg_replace('[^'.REFERENCE_INFO__ALLOWED_CHARS.']', '', $_GET['reference_info_id']);
	}
	
	// One column force
	if (isset($_GET['one_col'])) {
		$do['one_col'] = (intval($_GET['one_col']) == 1 ? TRUE : FALSE);
		/*if ($do['one_col']) {
			$do['urlKeyValuePairs']['one_col'] = 1;
			$do['urlData']['one_col'] = 'one_col=1';
		}*/
	}
	
	// Fix page to show
	if (isset($_GET['page']) or isset($_POST['page'])) {
		$t = (isset($_GET['page']) ? $_GET['page'] : (isset($_POST['page']) ? $_POST['page'] : 'about'));
		$fixPage = mb_ereg_replace('[^a-z_]', '', $t);
		switch ($fixPage) {
			case 'settings': $do['fixpage'] = 'settings'; break;
		}
	}
	// Tab to show on a page
	if (isset($_GET['tab']) or isset($_POST['tab'])) {
		$t = (isset($_GET['tab']) ? $_GET['tab'] : (isset($_POST['tab']) ? $_POST['tab'] : ''));
		$tab = mb_ereg_replace('[^a-z_]', '', $t);
		switch ($tab) {
			case 'book_list'	: $do['tab'] = 'tab-book-list'; 		break;
			case 'video_list'	: $do['tab'] = 'tab-video-list'; 		break;
			case 'preferences'	: $do['tab'] = 'tab-preferences'; 		break;
			case 'history'		: $do['tab'] = 'tab-history'; 			break;
			case 'help'			: $do['tab'] = 'tab-help'; 				break;
			case 'about'		: $do['tab'] = 'tab-about'; 			break;
			case 'afterword'	: $do['tab'] = 'tab-afterword'; 		break;
			case 'contact_us'	: $do['tab'] = 'tab-contact-us'; 		break;
			// download results
			case 'download_results'	: $do['tab'] = IS__SITE_PERMISSION__DOWNLOAD_RESULTS ? 'tab-download-results' : 'tab-help'; 		break;
		}
	}
	
	
	//-----------------------------------------
	// Site operation mode values (settings)
	//-----------------------------------------
	//-------------------------
	// Site Operation mode
	//-------------------------
	if (isset($_COOKIE['c_m'])) {
		$cookie_c_m = intval($_COOKIE['c_m']);
		if (($cookie_c_m < 0) or (SITE_OPERATION_MODE_MAX_NUM < $cookie_c_m)) { $cookie_c_m = -1; }
	} else {
		$cookie_c_m = -1;
	}
	
	// Value reading (from operation mode compose the parts)
	//	m	: old op.m. value
	//  mn	: new op.m. value
	if (!isset($_GET['m']) and !isset($_POST['m'])) {
		if (!isset($_GET['mn'])) {
			// Cookie data
			if ($cookie_c_m >= 0) {
				$mn = $m = $cookie_c_m;
			} else {
				$mn = $m = 0;
			}
		} else {
			$mn = intval($_GET['mn']);
			if (($mn < 0) or (SITE_OPERATION_MODE_MAX_NUM < $mn)) { $mn = 0; }
			$m = $mn;
		}
		$do['siteOperationMode_SearchMode_Changes'] = true;
	} else {
		$m = intval(isset($_GET['m']) ? $_GET['m'] : (isset($_POST['m']) ? $_POST['m'] : 0));
		if (($m < 0) or (SITE_OPERATION_MODE_MAX_NUM < $m)) { $m = 0; $do['siteOperationMode_SearchMode_Changes'] = true; }
		if (!isset($_GET['mn'])) {
			$mn = $m;
		} else {
			$mn = intval($_GET['mn']);
			if (($mn < 0) or (SITE_OPERATION_MODE_MAX_NUM < $mn)) { $mn = 0; $do['siteOperationMode_SearchMode_Changes'] = true; }
		}
	}

	$v = convert_SiteOperationMode_valueToArray($do, $mn);


	// Settings (from parts compose the operation mode)
	if (isset($_POST['preferences']) and (intval($_POST['preferences']) == 1)) {

		//	m	: old op.m. value
		if (isset($_GET['m'])) {
			$m = intval($_GET['m']);
			if (($m < 0) or (SITE_OPERATION_MODE_MAX_NUM < $m)) { $m = 0; $do['siteOperationMode_SearchMode_Changes'] = true; }
		}

		//  mn	: new op.m. value

		if (isset($_POST['m_search_in_books'])) { $t = intval($_POST['m_search_in_books']); } else { $t = 0; }
		$v['search_in_books'] = (($t != 0) and ($t != 1)) ? 0 : $t;

		if (isset($_POST['m_search_in_videos'])) { $t = intval($_POST['m_search_in_videos']); } else { $t = 0; }
		$v['search_in_videos'] = (($t != 0) and ($t != 1)) ? 0 : $t;

		if (isset($_POST['m_button_tooltip'])) { $t = intval($_POST['m_button_tooltip']); } else { $t = 0; }
		$v['button_tooltip'] = (($t != 0) and ($t != 1)) ? 0 : $t;

		if (isset($_POST['m_history'])) { $t = intval($_POST['m_history']); } else { $t = 0; }
		$v['history'] = (($t != 0) and ($t != 1)) ? 0 : $t;

		//if (isset($_POST['m_book_chapter_tooltip'])) { $t = intval($_POST['m_book_chapter_tooltip']); } else { $t = 0; }
		//$v['book_chapter_tooltip'] = (($t != 0) and ($t != 1)) ? 0 : $t;

		if (isset($_POST['m_book_text_load'])) { $t = intval($_POST['m_book_text_load']); } else { $t = 0; }
		$v['book_text_load'] = (($t != 0) and ($t != 1)) ? 0 : $t;

		if (isset($_POST['m_result_text_length'])) { $t = intval($_POST['m_result_text_length']); } else { $t = 0; }
		$v['result_text_length'] = (($t != 0) and ($t != 1)) ? 0 : $t;

		if (isset($_POST['m_result_list_text_length'])) { $t = intval($_POST['m_result_list_text_length']); } else { $t = 0; }
		$v['result_list_text_length'] = (($t != 0) and ($t != 1)) ? 0 : $t;

		if (isset($_POST['m_result_list_items_number'])) { $t = intval($_POST['m_result_list_items_number']); } else { $t = 0; }
		$v['result_list_items_number'] = (($t != 0) and ($t != 1)) ? 0 : $t;

		if (isset($_POST['m_result_list_tooltip'])) { $t = intval($_POST['m_result_list_tooltip']); } else { $t = 0; }
		$v['result_list_tooltip'] = (($t != 0) and ($t != 1)) ? 0 : $t;

		if (isset($_POST['m_unit'])) { $t = intval($_POST['m_unit']);	$do['siteOperationMode_SearchMode_Changes'] = true;	} else { $t = 0; }
		$v['unit'] = (($t != 0) and ($t != 1)) ? 0 : $t;

		if (isset($_POST['m_text'])) { $t = intval($_POST['m_text']); } else { $t = 0; }
		$v['text'] = (($t != 0) and ($t != 1)) ? 0 : $t;

		if (isset($_POST['m_search'])) {	$t = intval($_POST['m_search']);	} else { $t = 0; }
		$v['search'] = (($t != 0) and ($t != 1)) ? 0 : $t;

		//if (isset($_POST['m_autosuggest'])) { $t = intval($_POST['m_autosuggest']); } else { $t = 0; }
		//$v['autosuggest'] = (($t != 0) and ($t != 1)) ? 0 : $t;

		$mn = convert_SiteOperationMode_arrayToValue($do, $v);

		// Clear history
		if ($v['history'] == SITE_HISTORY_TYPE__OFF) {
			$needSetCookie['remove_c_sh'] = true;
		}
	}

	// Store Site operation mode in cookie
	if ($cookie_c_m != $mn) {
		$needSetCookie['c_m'] = true;
	}

	// check if operation mode unit changes (paragraph <-> sentence)
	$v_old = convert_SiteOperationMode_valueToArray($do, $m);
	if ($v_old['unit'] != $v['unit']) {
		$operationModeChanges_unit 		= true;
		$do['urlKeyValuePairs']['rn'] 	= $do['searchResult_psCurrentNumber'] = 1;
		$do['urlKeyValuePairs']['p'] 	= $do['searchResult_paragraph_id'] = null;
		$do['urlKeyValuePairs']['s'] 	= $do['searchResult_sentence_id'] = null;
	}

	//.. old op.m. code
	$m_str = str_pad(decbin($m), SITE_OPERATION_MODE_COMPONENT_NUM, '0', STR_PAD_LEFT);
	//.. new op.m. code
	$mn_str = str_pad(decbin($mn), SITE_OPERATION_MODE_COMPONENT_NUM, '0', STR_PAD_LEFT);
	$do['urlKeyValuePairs']['m'] 	= $mn;
	$do['siteOperationMode'] 		= $mn;
	$do['siteOperationMode_Values'] = convert_SiteOperationMode_valueToArray($do, $mn);
	$do['urlData']['m'] 			= 'm='.$do['siteOperationMode'];

	//.. Search mode changes => Any data should be invalidated
	if ($mn_str[1] != $m_str[1]) {
		$do['siteOperationMode_SearchMode_Changes'] = true;
	}
	
	
	
	//------------------------------------------------------------------------------------------------
	// Books or videos
	//------------------------------------------------------------------------------------------------
	define ('IS__SEARCH_IN_BOOKS',									(getSiteSpecificContent('search_in_books') &&
																		$do['siteOperationMode_Values']['search_in_books']==SITE_SETTINGS_VALUE__ON));
	define ('IS__SEARCH_IN_VIDEOS',									(getSiteSpecificContent('search_in_videos') &&
																		$do['siteOperationMode_Values']['search_in_videos']==SITE_SETTINGS_VALUE__ON));
	define ('IS__SEARCH_IN_BOOKS_VIDEOS',							(IS__SEARCH_IN_BOOKS && IS__SEARCH_IN_VIDEOS));
	
	define ('IS__LANGUAGES_USED_IN_BOOKS',							getSiteSpecificContent('languages_used_in_books'));
	
	
	//-------------------------
	// Action
	//-------------------------
	// Simplified
	define ('IS__SITE_SIMPLIFIED_LAYOUT',							($do['simplified_layout']));
	define ('IS__SITE_BOOK_READING_LAYOUT',							(!IS__SITE_SIMPLIFIED_LAYOUT or $do['book_reading_layout']));
	
	// Video
	define ('IS_ACTION_VIDEO',										(IS__SEARCH_IN_VIDEOS and $do['video']));
	
	// Common
	define ('IS_SEARCH_TEXT',										(!empty($do['search_text'])));
	
	define ('DO_SEARCH_QUERY',										($do['search_query']));
	
	//define ('DO_SELECT_BOOK',										(	isset($do['searchResult_book_id'])));
	
	define ('DO_SELECT_CHAPTER',									(	!isset($fixPage) and !DO_SEARCH_QUERY and
																		isset($do['searchResult_book_id']) and
																		isset($do['searchResult_chapter_id'])
																	));
	define ('DO_SELECT_PARAGRAPH_SENTENCE',							(	!isset($fixPage) and !DO_SEARCH_QUERY and
																		isset($do['searchResult_book_id']) and
																		(isset($do['searchResult_paragraph_id']) or
																		isset($do['searchResult_sentence_id']))
																	));
	define ('DO_SELECT_RESULT',										(	DO_SELECT_PARAGRAPH_SENTENCE and 
																		isset($do['searchResult_psCurrentNumber'])
																	));
	define ('DO_BOOK_READ',											(	!isset($fixPage) and !DO_SEARCH_QUERY and
																		isset($do['searchResult_book_id']) and
																		(isset($do['searchResult_chapter_id']) or
																		isset($do['searchResult_paragraph_id']) or
																		isset($do['searchResult_sentence_id']))
																	));
	
	define ('DO_LOAD_INLINE_CHAPTER',								(	isset($do['book_text_load__paragraph_before']) or
																		isset($do['book_text_load__paragraph_after'])));
	
	define ('DO_CROSS_REFERENCE',									(	!empty($do['cross_reference_id']) and
																		isset($do['searchResult_book_id'])));
	
	define ('DO_REFERENCE_INFO',									(	!empty($do['reference_info_id'])));
	
	// Download results
	define ('DO_DOWNLOAD_RESULTS',									(	IS__SITE_PERMISSION__DOWNLOAD_RESULTS && 
																		DO_SEARCH_QUERY && 
																		IS_SEARCH_TEXT && 
																		$do['download_results']));
	
	if (!IS__SITE_SIMPLIFIED_LAYOUT) {
		
		if (IS__SEARCH_IN_BOOKS) {
			//-------------------------
			// Book list to search in
			//-------------------------
			if (isset($_POST['book_id_list_send'])) {
				
				// Settings for book list id
				if (isset($_POST['book_id']) or isset($_GET['book_id'])) {
					$t = (isset($_POST['book_id']) ? $_POST['book_id'] : (isset($_GET['book_id']) ? $_GET['book_id'] : ALL_BOOKS__BOOK_ID));
					
					$ret = convert_BookIdList_to_BookListNumber($t);
					$do['searchBookListCode'] = $ret['searchBookListCode'];
					$do['searchBookListNum'] = $ret['searchBookListNum'];
					if ($do['searchBookListCode'] !== strval(ALL_BOOKS__BOOK_ID)) {
						$do['searchBooksIdList'] = convert_BookListNumber_to_BookIdList($do['searchBookListCode']);
					}
				// empty selection
				} else {
					$do['searchBookListCode'] = NO_BOOKS__BOOK_ID;
					$do['searchBooksIdList'] = array();
					$do['searchBookListNum'] = 0;
				}
				
			} else {

				// ..Book list code transfer
				$t = (isset($_POST['blc']) ? $_POST['blc'] : (isset($_GET['blc']) ? $_GET['blc'] : ALL_BOOKS__BOOK_ID));
				
				$do['searchBookListCode'] = $t;
				
				if ($do['searchBookListCode'] == strval(NO_BOOKS__BOOK_ID)) {
					$do['searchBooksIdList'] = array();
					$do['searchBookListNum'] = 0;
					
				} else if ($do['searchBookListCode'] !== strval(ALL_BOOKS__BOOK_ID)) {
					$do['searchBookListCode'] = mb_ereg_replace('[^'.BOOK_LIST_CODE_ALLOWED_CHARS.']', '', $do['searchBookListCode']);
					$do['searchBooksIdList'] = convert_BookListNumber_to_BookIdList($do['searchBookListCode']);
					$do['searchBookListNum'] = count($do['searchBooksIdList']);
				}
			}
			$do['urlKeyValuePairs']['blc'] = $do['searchBookListCode'];
			$do['urlData']['blc'] = 'blc='.$do['searchBookListCode'];
		}
		if (IS__SEARCH_IN_VIDEOS) {
			//-------------------------
			// Video list to search in
			//-------------------------
			if (isset($_POST['video_id_list_send'])) {
				
				// Settings for video list id
				if (isset($_POST['video_id']) or isset($_GET['video_id'])) {
					$t = (isset($_POST['video_id']) ? $_POST['video_id'] : (isset($_GET['video_id']) ? $_GET['video_id'] : ALL_BOOKS__BOOK_ID));

					$ret = convert_BookIdList_to_BookListNumber($t);
					$do['searchVideoListCode'] = $ret['searchBookListCode'];
					$do['searchVideoListNum'] = $ret['searchBookListNum'];
					if ($do['searchVideoListCode'] !== strval(ALL_BOOKS__BOOK_ID)) {
						$do['searchVideosIdList'] = convert_BookListNumber_to_BookIdList($do['searchVideoListCode']);
					}
				// empty selection
				} else {
					$do['searchVideoListCode'] = NO_BOOKS__BOOK_ID;
					$do['searchVideosIdList'] = array();
					$do['searchVideoListNum'] = 0;
				}
				
			} else {
				
				// ..Video list code transfer
				$t = (isset($_POST['vlc']) ? $_POST['vlc'] : (isset($_GET['vlc']) ? $_GET['vlc'] : ALL_BOOKS__BOOK_ID));
				
				$do['searchVideoListCode'] = $t;
				
				if ($do['searchVideoListCode'] == strval(NO_BOOKS__BOOK_ID)) {
					$do['searchVideosIdList'] = array();
					$do['searchVideoListNum'] = 0;
					
				} else if ($do['searchVideoListCode'] !== strval(ALL_BOOKS__BOOK_ID)) {
					$do['searchVideoListCode'] = mb_ereg_replace('[^'.BOOK_LIST_CODE_ALLOWED_CHARS.']', '', $do['searchVideoListCode']);
					$do['searchVideosIdList'] = convert_BookListNumber_to_BookIdList($do['searchVideoListCode']);
					$do['searchVideoListNum'] = count($do['searchVideosIdList']);
				}
			}
			$do['urlKeyValuePairs']['vlc'] = $do['searchVideoListCode'];
			$do['urlData']['vlc'] = 'vlc='.$do['searchVideoListCode'];
		}
	} // end: if (!IS__SITE_SIMPLIFIED_LAYOUT)
	
	
	//-------------------------
	// Contact us
	//-------------------------
	if (isset($_POST['contact-us-send-message']) and (intval($_POST['contact-us-send-message'])==1)) {
		
		if (isset($_POST['contact-us-name']) and
			isset($_POST['contact-us-email']) and
			isset($_POST['contact-us-message'])) {
			
			// User
			$user_name 		= trim(strip_tags($_POST['contact-us-name']));
			$user_email 	= trim(strip_tags($_POST['contact-us-email']));
			$user_subject	= 'Contact us sent';
			$user_message 	= trim(strip_tags($_POST['contact-us-message']));
			$user_sendcopy	= (isset($_POST['contact-us-sendcopy']) ? true : false);
			$user_headers = 'From: '.EMAIL_ADDRESS_CONTACT_US.EMAIL_NEWLINE.
							'Reply-To: '.EMAIL_ADDRESS_CONTACT_US.EMAIL_NEWLINE;
			
			// Admin
			$admin_subject	= 'Contact us sent';
			$admin_message	= EMAIL_NEWLINE.'Name: '.$user_name.
								EMAIL_NEWLINE.'Email: '.$user_email.
								EMAIL_NEWLINE.$user_message;
			$admin_headers = 'From: '.EMAIL_ADDRESS_CONTACT_US.EMAIL_NEWLINE.
							'Reply-To: '.$user_email.EMAIL_NEWLINE;
			
			// Email address validation
			if (filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
				
				// User
				if ($user_sendcopy) {
					$is_success_send_email_to_user = mail($user_email, $user_subject, $user_message, $user_headers);
				}
				
				// Admin
				$is_success_send_email_to_admin = mail(EMAIL_ADDRESS_CONTACT_US, $admin_subject, $admin_message, $admin_headers);
				$do['contact-us']['message'] = "Email was sent successfully. Thank you.";
			} else {
				$do['contact-us']['error'] = "Given email address is not valid: ".$user_email;
				$do['contact-us']['data'] = array(
					'name' 		=> $user_name,
					'email'		=> $user_email,
					'message'	=> $user_message,
					'sendcopy'	=> $user_sendcopy
				);
			}			
		}
	} // Contact us
	
	
	//-------------------------
	// Touch device
	//-------------------------
	if (isset($_COOKIE['is_touch_device'])) {
		$do['siteRunningOnTouchDevice'] = intval($_COOKIE['is_touch_device']);
		if ($do['siteRunningOnTouchDevice'] == 1) { $do['siteRunningOnTouchDevice'] = true; }
	}
	
	//-------------------------
	// Small device
	//-------------------------
	if (isset($_COOKIE['is_small_device'])) {
		$do['siteRunningOnSmallDevice'] = intval($_COOKIE['is_small_device']);
		if ($do['siteRunningOnSmallDevice'] == 1) { $do['siteRunningOnSmallDevice'] = true; }
	}
	
	//-------------------------
	// Site Layout
	//-------------------------
	if (IS__SITE_SIMPLIFIED_LAYOUT) {
		$do['layout_container_class_name'] = array(
			'north' 	=> (!IS__SITE_BOOK_READING_LAYOUT ? 'simplified' : ''),
			'west' 		=> 'simplified',
			'center'	=> 'simplified'.(!IS__SEARCH_IN_VIDEOS ? ' no-video-captions' : ''),
			'south' 	=> 'simplified'
		);
	} else {
		$do['layout_container_class_name'] = array(
			'north' 	=> 'ui-layout-north',
			'west' 		=> 'ui-layout-west',
			'center'	=> 'ui-layout-center',
			'south' 	=> 'ui-layout-south'
		);
	}
	
	
	
	
	//------------------------------------------------
	// Download results
	//------------------------------------------------
	if (DO_DOWNLOAD_RESULTS) {
		
		// download_results_search_unit
		//
		//	<option value="0">paragraph</option>
		//	<option value="1">sentence</option>
		if (isset($_GET['download_results_search_unit'])) {
			$do['download_results_search_unit'] = intval($_GET['download_results_search_unit']);
			if ($do['download_results_search_unit'] < 0 || 
				1 < $do['download_results_search_unit']) { $do['download_results_search_unit'] = SITE_UNIT_TYPE__PARAGRAPH; }
			
			$do['siteOperationMode_Values']['unit'] = $do['download_results_search_unit'];
		} else {
			$do['download_results_search_unit'] = null;
		}
		
		// download_results_books_search_in
		//
		// <option value="0">selected</option>
		// <option value="1">all</option>
		if (isset($_GET['download_results_books_search_in'])) {
			$do['download_results_books_search_in'] = intval($_GET['download_results_books_search_in']);
			if ($do['download_results_books_search_in'] < 0 || 
				1 < $do['download_results_books_search_in']) { $do['download_results_books_search_in'] = ALL_BOOKS__BOOK_ID; }
			
			if ($do['download_results_books_search_in'] == ALL_BOOKS__BOOK_ID) {
				$do['searchBookListCode'] = ALL_BOOKS__BOOK_ID;
			}
		} else {
			$do['download_results_books_search_in'] = null;
		}
		
		// download_results_search_text_result_type
		//
		// <option value="0">with diacritics</option>
		// <option value="1">plain</option>
		if (isset($_GET['download_results_search_text_result_type'])) {
			$do['download_results_search_text_result_type'] = intval($_GET['download_results_search_text_result_type']);
			if ($do['download_results_search_text_result_type'] < 0 || 
				1 < $do['download_results_search_text_result_type']) { $do['download_results_search_text_result_type'] = SITE_TEXT_TYPE__DIACRITICS; }
			
			$do['siteOperationMode_Values']['text'] = ($do['download_results_search_text_result_type'] == SITE_TEXT_TYPE__DIACRITICS) ? 
														SITE_TEXT_TYPE__DIACRITICS : SITE_TEXT_TYPE__PLAIN;
		} else {
			$do['download_results_search_text_result_type'] = null;
		}
		
		// download_results_quote_template
		//
		//	<option value="1">quote</option>
		//	<option value="2">quote: author</option>
		//	<option value="3">quote: author, book</option>
		//	<option value="4">quote: author, book, chapter</option>
		if (isset($_GET['download_results_quote_template'])) {
			$do['download_results_quote_template'] = intval($_GET['download_results_quote_template']);
			if ($do['download_results_quote_template'] < 1 || 
				4 < $do['download_results_quote_template']) { $do['download_results_quote_template'] = 1; }
		} else {
			$do['download_results_quote_template'] = null;
		}
		
	}
	
	
	//------------------------------------------------
	// Define variables, fields
	//------------------------------------------------
	if (defined('FORCE_USAGE_ENCRYPTION_TABLE_DATA')) {
		$use_encryption = FORCE_USAGE_ENCRYPTION_TABLE_DATA;
	} else {
		$use_encryption = $is_localhost;
	}
	define ('USE_ENCRYPTION_TABLE_DATA', 					($use_encryption)); // encryption table data usage on/off
	// Cryptographic values
	if (USE_ENCRYPTION_TABLE_DATA) {
		define ('ENCRYPT_DECRYPT_PASSWORD',					('n%5/D!3d6(I3o8=A4t)2T5+n74u;2F?5e,4Dx.5S2*o3B5e'));
		define ('ENCRYPT_DECRYPT_SALT',						('Zj!p(X2l'));
		define ('ENCRYPT_DECRYPT_HASH',						(MHASH_SHA256));
		define ('ENCRYPT_DECRYPT_KEY',						(mhash_keygen_s2k(ENCRYPT_DECRYPT_HASH, ENCRYPT_DECRYPT_PASSWORD, ENCRYPT_DECRYPT_SALT, 32)));
		define ('ENCRYPT_DECRYPT_CIPHER',					(MCRYPT_RIJNDAEL_128));
		define ('ENCRYPT_DECRYPT_MODE',						(MCRYPT_MODE_CBC));
	}
	// Encrypted tables
	define ('PARAGRAPH_ENCRYPTED_TABLE',					(PARAGRAPH_NONCRYPTED_TABLE.TABLE_NAME_COMPONENT__ENCRYPTED));
	// ..video books
	define ('VIDEO_PARAGRAPH_ENCRYPTED_TABLE',				(VIDEO_PARAGRAPH_NONCRYPTED_TABLE.TABLE_NAME_COMPONENT__ENCRYPTED));
	
	// Final table in use (plain or encrypted)
	define ('OPTION_TABLE_NAME_COMPONENT__ENCRYPTED', 		(USE_ENCRYPTION_TABLE_DATA ? TABLE_NAME_COMPONENT__ENCRYPTED : ''));
	define ('PARAGRAPH_TABLE',								(PARAGRAPH_NONCRYPTED_TABLE.OPTION_TABLE_NAME_COMPONENT__ENCRYPTED));
	// ..video books
	define ('VIDEO_PARAGRAPH_TABLE',						(VIDEO_PARAGRAPH_NONCRYPTED_TABLE.OPTION_TABLE_NAME_COMPONENT__ENCRYPTED));
	
	// Different settings
	/*
	define ('IS__SITE_RUNNING_ON_TOUCH_DEVICE',						(	$do['siteRunningOnTouchDevice'] ?
																			true : false
																	));
	define ('IS__SITE_RUNNING_ON_SMALL_DEVICE',						(	$do['siteRunningOnSmallDevice'] ?
																			true : false
																	));
	define ('DB_TABLE_PREFIX_MEDIA_TYPE',							(	IS__SEARCH_IN_VIDEOS ? 
																			TABLE_NAME_COMPONENT__VIDEO : ''
																	));
	*/
	define ('DB_TABLE_PREFIX_ACTION_TYPE',							(	IS_ACTION_VIDEO ? 
																			TABLE_NAME_COMPONENT__VIDEO : ''
																	));
	define ('IS__SITE_SEARCH_TYPE__RELEVANCE', 						(	$do['siteOperationMode_Values']['search']==SITE_SEARCH_TYPE__RELEVANCE ?
																			true : false
																	));
	define ('IS__SITE_UNIT_TYPE__PARAGRAPH', 						(	$do['siteOperationMode_Values']['unit']==SITE_UNIT_TYPE__PARAGRAPH ?
																			true : false
																	));
	define ('IS__SITE_RESULT_LIST_ITEMS_NUM__ALL',					(	$do['searchResultList_loadAll'] ?
																			true : false
																	));
	define ('IS__SITE_RESULT_LIST_ITEMS_NUM__NEXT_BUNCH',			(	$do['searchResultList_loadNext'] ?
																			true : false
																	));
	define ('IS__SITE_RESULT_LIST_TEXT_LENGTH_TYPE__LONG_TEXT',		(	$do['siteOperationMode_Values']['result_list_text_length']==SITE_RESULT_LIST_TEXT_LENGTH_TYPE__LONG_TEXT ?
																			true : false
																	));
	define ('IS__SITE_RESULT_TEXT_LENGTH_TYPE__LONG_TEXT',			(	$do['siteOperationMode_Values']['result_text_length']==SITE_RESULT_TEXT_LENGTH_TYPE__LONG_TEXT ?
																			true : false
																	));
	define ('IS__SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK',				(	$do['siteOperationMode_Values']['book_text_load']==SITE_BOOK_TEXT_LOAD_TYPE__FULL_BOOK ?
																			true : false
																	));
	define ('IS__SITE_RESULT_LIST_TOOLTIP__ON', 					(	(($do['siteOperationMode_Values']['result_list_tooltip']==SITE_RESULT_LIST_TOOLTIP__ON) 
																			and !$do['siteRunningOnTouchDevice']
																			and !$do['siteRunningOnSmallDevice']) ?
																			true : false
																	));
	define ('IS__SITE_HISTORY_TYPE__ON',							(	$do['siteOperationMode_Values']['history']==SITE_HISTORY_TYPE__ON ?
																			true : false
																	));
	define ('IS__SITE_BUTTON_TOOLTIP__ON', 							(	(($do['siteOperationMode_Values']['button_tooltip']==SITE_BUTTON_TOOLTIP__ON)
																			and !$do['siteRunningOnTouchDevice']
																			and !$do['siteRunningOnSmallDevice']) ?
																			true : false
																	));
	define ('IS__TEXT_TYPE__DIACRITICS',							(	$do['siteOperationMode_Values']['text']==SITE_TEXT_TYPE__DIACRITICS ?
																			true : false
																	));
	define ('IS__SITE_ONE_COLUMN_FORCE',							(	(!DO_SEARCH_QUERY && isset($do['one_col']) && $do['one_col']) ?
																			true : false
																	));
	
	//define ('SHOW_RESULT_LIST_PANEL',								(	$do['searchBookListCode'] == NO_BOOKS__BOOK_ID ?
	//																		'videos' : 'books'
	//																));
	
	define ('DIACRITIC_OR_PLAIN', 									(	IS__TEXT_TYPE__DIACRITICS ?
																			'diacritic' : 'plain'
																	));
	
	define ('PARAGRAPH_OR_SENTENCE', 								(	IS__SITE_UNIT_TYPE__PARAGRAPH ?
																			'paragraph' : 'sentence'
																	));
	
	define ('PS_ID_TABLE_FIELD',									(	IS__SITE_UNIT_TYPE__PARAGRAPH ?
																			'paragraph_id' : 'sentence_id'
																	));
	define ('PS_TABLE',												(	IS__SITE_UNIT_TYPE__PARAGRAPH ?
																			PARAGRAPH_TABLE : SENTENCE_TABLE
																	));
	
	define ('SEARCH_RESULT_LIST__ITEM_NUM__FEW', 					(	$is_localhost ?
																			SEARCH_RESULT_LIST__ITEM_NUM__LOCALHOST__FEW 
																			: SEARCH_RESULT_LIST__ITEM_NUM__REMOTE__FEW
																	));
	define ('SEARCH_RESULT_LIST__ITEM_NUM__MANY', 					(	$is_localhost ?
																			SEARCH_RESULT_LIST__ITEM_NUM__LOCALHOST__MANY 
																			: SEARCH_RESULT_LIST__ITEM_NUM__REMOTE__MANY
																	));
	define ('SEARCH_RESULT_LIST__ITEM_NUM__LOAD_NEXT', 				(	$do['siteOperationMode_Values']['result_list_items_number']==SITE_RESULT_LIST_ITEMS_NUM__FEW ?
																			SEARCH_RESULT_LIST__ITEM_NUM__FEW 
																			: SEARCH_RESULT_LIST__ITEM_NUM__MANY
																	));
	
	define ('NO_RELEVANT_PS_FOR_SEARCH_TEXT', 				('No matches.Please try a different phrase.'));//('No matches.<br /><br />Please try a different phrase.'));
	define ('NO_RELEVANT_BOOKS_PS_FOR_SEARCH_TEXT', 		('No matches in books.<br /><br />Please try a different phrase.'));
	define ('NO_RELEVANT_VIDEOS_PS_FOR_SEARCH_TEXT', 		('No matches in videos.<br /><br />Please try a different phrase.'));
	define ('NOT_FOUND_PS',	 								(''));//('This column displays book text.'));//'No '.PARAGRAPH_OR_SENTENCE.' was found.'));
	
	
	// Clear history
	if (isset($_GET['clear_history']) or !IS__SITE_HISTORY_TYPE__ON) {
		$needSetCookie['remove_c_sh'] = true;
	
	// Search history, history pos
	} else if (IS__SITE_HISTORY_TYPE__ON and isset($_COOKIE['c_sh'])) {
		$search_history_all_data = explode(SEARCH_HISTORY_POS_DATA_SEPARATOR, ($_COOKIE['c_sh']));
		if (count($search_history_all_data) == 2) {
			
			// history pos
			$t = intval($search_history_all_data[0]);
			if ($t < -1) { $t = -1; }
			$do['search_history_pos'] = $t;
			
			// history data
			$t = explode(SEARCH_HISTORY_DATA_ITEMS_SEPARATOR, $search_history_all_data[1]);
			$do['search_history'] = $t;
			
			$maxpos = count($do['search_history']) - 1;
			if ($maxpos < $do['search_history_pos']) {
				$do['search_history_pos'] = $maxpos;
			}
			
			// H.Back or H.Forward button click override (step in history queue)
			if (isset($_GET['shp'])) {
				$t = intval($_GET['shp']);
				if ((0 <= $t) and ($t <= $maxpos)) {
					$do['search_history_pos'] = $t;
					$needSetCookie['c_sh'] = true;
				}
			}
		} else {
			$do['search_history'] = array();
			$do['search_history_pos'] = -1;
		}
	}
	
	//--------------------------------------------------------------------------------
	// Set Cookies
	//--------------------------------------------------------------------------------
	$nowPlusOneYear = time() + 365*86400;
	$nowPlusOneMonth = time() + 30*86400;
	
	//if ($needSetCookie['c_blc']) 				{ setcookie('c_blc', $do['searchBookListCode'], $nowPlusOneYear, '/', COOKIE_DOMAIN, false, true); }
	if ($needSetCookie['c_m']) 					{ setcookie('c_m', $mn, $nowPlusOneYear, '/', COOKIE_DOMAIN, false, true); }
	if ($needSetCookie['remove_c_sh']) 			{ setcookie('c_sh', '', time() - 86400, '/', COOKIE_DOMAIN, false, true); }
	
	if (isset($needSetCookie['book_reading_layout']) &&	$needSetCookie['book_reading_layout']) {
		setcookie('book_reading_layout', $do['book_reading_layout'], $nowPlusOneMonth, '/', COOKIE_DOMAIN, false, false);
	}
	/*if (isset($needSetCookie['video_results']) && $needSetCookie['video_results']) {
		setcookie('video_results', $do['video_results'], $nowPlusOneMonth, '/', COOKIE_DOMAIN, false, false);
	}*/
	
	
	//--------------------------------------------------------------------------------
	// Choose SQLite DB, CSS file
	//--------------------------------------------------------------------------------
	if (isset($_POST['db']) or isset($_GET['db'])) {
		
		$sqlite_db 	= isset($_POST['db']) ? $_POST['db'] : (isset($_GET['db']) ? $_GET['db'] : '');
		$sqlite_db 	= mb_ereg_replace('[^a-zA-Z0-9_\-\.]', '', $sqlite_db);
		$file 		= SQLITE_DIR . '/' . $sqlite_db . 
						(USE_ENCRYPTION_TABLE_DATA ? SQLITE_FILE_ENCRYPTED_POSTFIX : SQLITE_FILE_NONCRYPTED_POSTFIX);
		
		if (is_readable($file)) {
			$do['urlKeyValuePairs']['db'] = $sqlite_db;
			$do['urlData']['db'] = 'db='.$sqlite_db;
			$do['db']	= $sqlite_db;
			$do['css'] 	= $do['db'];
		} else {
			$do['db']	= SQLITE_DB_DEFAULT;
			$do['css'] 	= getSiteSpecificContent('css_default');
		}
	} else {
		$do['db']		= SQLITE_DB_DEFAULT;
		$do['css'] 		= getSiteSpecificContent('css_default');
	}
	
	//--------------------------------------------------------------------------------
	// SQLite DB to use (Crypt: Encrypted/Noncrypted, Edition: Official/...)
	//--------------------------------------------------------------------------------
	define ('SQLITE_FILE_TO_USE', 	(	SQLITE_DIR . '/' . $do['db'] . 
										(USE_ENCRYPTION_TABLE_DATA ? SQLITE_FILE_ENCRYPTED_POSTFIX : SQLITE_FILE_NONCRYPTED_POSTFIX)
									));
	//--------------------------------------------------------------------------------
	// SQLite DB prefix (books => '', videos => 'video_')
	// SQLite DB prefix = '' (books, videos selected according to need in place in code)
	//--------------------------------------------------------------------------------
	$do['db_table_prefix'] = (IS_ACTION_VIDEO ? TABLE_NAME_COMPONENT__VIDEO : '');
	
	//--------------------------------------------------------------------------------
	// Prepare key/values necessary to transfer through pages in links
	//--------------------------------------------------------------------------------
	$do['urlDataTransfer'] = !empty($do['urlData']) ? '&amp;'.implode('&amp;', $do['urlData']) : '';
	
	
	//writeOut($do['urlData']);
	//writeOut($do['urlDataTransfer']);
	
	// Load state of JS libraries
	//define ('LOAD_JS_TOOLTIP', 					(true));
	//define ('LOAD_JS_JQUERY', 					(true));//(LOAD_JS_TOOLTIP or LOAD_JS_JQUERY_TOOLS));
	
	
	//------------------------------------------------------------------------------------------------------------------------------------------
	// Memcache
	//------------------------------------------------------------------------------------------------------------------------------------------
    //define ('MEMCACHE_ENABLE', 	false);
	//define ('MEMCACHE_HOST', 	'localhost');
	//define ('MEMCACHE_PORT', 	11211);
?>