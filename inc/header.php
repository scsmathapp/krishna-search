<?php
header('Content-Type: text/html; charset=' . ENCODING);
// 	header('Accept-Encoding: gzip, deflate');
header('Accept-Encoding: gzip');
// 	header('Cache-Control: max-age=86400, public, must-revalidate');

$website_title = getSiteSpecificContent('head_title');

include_once(FS_FILE_CONFIG_DATA);

//--------------------------------------
// CSS files
//--------------------------------------
$filesA_CSS = array(
	'font',
	'font-extra',
	'main',
	'main-sub-1',
	'main-sub-2',
	'main-sub-3',
	'main-sub-4',
	'main-afterall',
	CSS_BOOK_TEXT_DIR . '/book-text-' . $do['css'],
	'tipTip'
);
if (IS__SITE_SIMPLIFIED_LAYOUT) {
	$filesA_CSS[] = 'main-simplified';
} else {
	$filesA_CSS[] = 'layout-default-latest';
}
if (IS__SEARCH_IN_VIDEOS) {
	$filesA_CSS[] = 'prettyPhoto';
}

//--------------------------------------
// JS files
//--------------------------------------
$filesA_JS = array(
	'jquery-2.2.4.min',
	'jquery.scrollTo.min',
	'jquery.localScroll.min',
	'hammer.min'
);
if (!IS__SITE_SIMPLIFIED_LAYOUT) {
	$filesA_JS[] = 'jquery-ui.min';
	$filesA_JS[] = 'jquery.layout-latest';
}
if (IS__SEARCH_IN_VIDEOS) {
	$filesA_JS[] = 'jquery.prettyPhoto';
}
$filesA_JS[] = 'jquery.tipTip';
$filesA_JS[] = 'jquery.hotkeys';
$filesA_JS[] = 'main';
$filesA_JS[] = 'autosuggest';

/*<link rel="alternate" href="<?php echo WEB_ROOT; ?>/news/" title="<?php echo $website_title; ?>" type="application/rss+xml" />*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
	<title><?php echo $website_title; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo ENCODING; ?>" />
	<meta http-equiv="Cache-control" content="public" />
	<meta http-equiv="content-language" content="en" />
	<meta name="description" content="<?php echo $do['pageHeaderMetaDescription']; ?>" />
	<?php
	$dataOut = array();

	// Development includes
	if (IS__LOCALHOST) {
		foreach ($filesA_CSS as $current_file) {
			$dataOut[] = '<link rel="stylesheet" type="text/css" href="' . WEB_CSS_DEV_DIR . '/' . $current_file . '.css?v=' . $config['development']['version'] . '" />';
		}
		// Production includes
	} else {
		foreach ($filesA_CSS as $current_file) {
			$dataOut[] = '<link rel="stylesheet" type="text/css" href="' . WEB_CSS_PROD_DIR . '/' .
				$current_file .
				(isset($config['production'][$current_file . '.css']['hash']) ?
					FS_FILE_HASH_SEPARATOR . $config['production'][$current_file . '.css']['hash'] : '') .
				'.css' . '" />';
		}
	}

	// Write out CSS file links
	echo implode("\n", $dataOut);
	?>
	<?php
	/*

// jQuery + jQuery Tools (all in one)
<script src="http://cdn.jquerytools.org/1.2.7/full/jquery.tools.min.js"></script>

// jQuery + jQuery Tools (separate)
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="http://cdn.jquerytools.org/1.2.7/all/jquery.tools.min.js"></script>
*/
	?>
	<?php
	//---------------------------------------------------------------------------------------------------------------
	// Post javascript values
	// for javascripts.js
	//---------------------------------------------------------------------------------------------------------------
	/*
	 * var data_transfer = {is_settings_page:'true'};
	 */

	$postJSscript = array();
	$postJSscript[] = '<script type="text/javascript">';

	// for javascripts.js
	$postJSscript[] = 'var is_button_tooltip_on =' . (IS__SITE_BUTTON_TOOLTIP__ON ? 'true' : 'false') . ';';
	$postJSscript[] = 'var is_video_captions_in_use =' . (IS__SEARCH_IN_VIDEOS ? 'true' : 'false') . ';';
	$postJSscript[] = 'var host_origin =' . "'" . (IS__LOCALHOST ? '' : urlencode(WEB_ROOT)) . "';";
	$postJSscript[] = 'var index_file =' . "'" . WEB_INDEX_FILE . "';";
	$postJSscript[] = 'var is_site_simplified =' . (IS__SITE_SIMPLIFIED_LAYOUT ? 'true' : 'false') . ';';
	//$postJSscript[] = 'var is_not_touch_device__add_css ='."'".WEB_CSS_DIR.'/font-extra'.
	//					(IS__LOCALHOST ? 
	//						'.css?v='.$config['development']['version'] : 
	//						FS_FILE_HASH_SEPARATOR.$config['production']['font-extra.css']['hash'].'.css')."';";
	$postJSscript[] = 'var is_action_video =' . (IS_ACTION_VIDEO ? 'true' : 'false') . ';';

	// Panels open/close
	$postJSscript[] = 'var is_site_one_column_force =' . (IS__SITE_ONE_COLUMN_FORCE ? 'true' : 'false') . ';';
	$postJSscript[] = 'var do_search_query =' . (DO_SEARCH_QUERY ? 'true' : 'false') . ';';
	$postJSscript[] = 'var do_select_result =' . (DO_SELECT_RESULT ? 'true' : 'false') . ';';
	$postJSscript[] = 'var show_video_results =' . (IS__SEARCH_IN_VIDEOS ? 'true' : 'false') . ';';
	$postJSscript[] = 'var is_index_page =' . ($index_page ? 'true' : 'false') . ';';
	//$postJSscript[] = 'var show_result_list_panel ='."'".SHOW_RESULT_LIST_PANEL."';";

	$postJSscript[] = '</script>';

	// echo out
	echo implode('', $postJSscript);

	?>
	<?php
	/*
<script type="text/javascript" src="<?php echo WEB_JS_DEV_DIR.'/plugins/hammer.fakemultitouch.js'); ?>"></script>
<script type="text/javascript" src="<?php echo WEB_JS_DEV_DIR.'/plugins/hammer.showtouches.js'); ?>"></script>
<!--[if !IE]> -->
<script>
	Hammer.plugins.showTouches();
</script>
<!-- <![endif]-->
*/
	$dataOut = array();

	// Development includes
	if (IS__LOCALHOST) {
		foreach ($filesA_JS as $current_file) {
			$dataOut[] = '<script type="text/javascript" src="' . WEB_JS_DEV_DIR . '/' . $current_file . '.js?v=' . $config['development']['version'] . '"></script>';
		}
		// Production includes
	} else {
		foreach ($filesA_JS as $current_file) {
			$dataOut[] = '<script type="text/javascript" src="' . WEB_JS_PROD_DIR . '/' .
				$current_file .
				(isset($config['production'][$current_file . '.js']['hash']) ?
					FS_FILE_HASH_SEPARATOR . $config['production'][$current_file . '.js']['hash'] : '') .
				'.js' . '"></script>';
		}
	}

	// Write out JS file links
	echo implode("\n", $dataOut);
	?>
	<link rel="shortcut icon" href="<?php echo WEB_IMG_DIR . '/favicon' . (IS__LOCALHOST ? '' : FS_FILE_HASH_SEPARATOR . $config['production']['favicon.png']['hash']) . '.png'; ?>" />
	<meta name="google-site-verification" content="<?php echo getSiteSpecificContent('google-site-verification'); ?>" />
	<?php
	if (
		getSiteSpecificContent('run_analytics_tracking') and
		getSiteSpecificContent('analytics_tracking_filename') != ''
	) {
		include_once(getSiteSpecificContent('analytics_tracking_filename'));
	}
	?>
</head>
<?php flush(); ?>

<body>
	<?php
	echo '<script type="text/javascript" src="' . WEB_JS_DIR . '/wz_tooltip' .
		(IS__LOCALHOST ?
			'.js?v=' . $config['development']['version'] :
			FS_FILE_HASH_SEPARATOR . $config['production']['wz_tooltip.js']['hash'] . '.js') . '"></script>';
	?>