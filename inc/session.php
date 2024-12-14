<?php
	//--------------------------------------------------------------------------------------------------------------------------------
	// COOKIE
	//--------------------------------------------------------------------------------------------------------------------------------
	if (isset($_COOKIE)) {
		if (isset($_COOKIE["PHPSESSID"])) {
			$PHPSESSID = $_COOKIE["PHPSESSID"];
		}
		if (isset($_COOKIE["session_expTime"])) {
			$session_expTime = $_COOKIE["session_expTime"];
		}
	}
	
	//--------------------------------------------------------------------------------------------------------------------------------
	// SESSION setup
	//--------------------------------------------------------------------------------------------------------------------------------
	global $PHPSESSID;
	
	// Session time
	define('SESSION_TIME', 7200);
	
	// Create Session ID
	function generate_id() {
		/*
		$x = explode(' ', microtime());
		mt_srand(substr($x[0], 2));
		
		$new_id = '';
		for ( $i = 0; $i < 20; $i++ ) {
			$new_id .= base_convert(mt_rand(1, 32)*mt_rand(1, 10) + mt_rand(10, 1000), 10, 32);
		}
		return $new_id;
		*/
		return uniqid();
	}
	
	// 1. HTTP_REFERER and REMOTE_HOST, does it come from another website?
	
	session_start();
 	if (!session_is_registered("session_expTime")) {
		if (session_register("session_expTime")) {
			// Session creation successful
		} else {
			// 'Failure in the session system.';
		}
	}
	
	// If there is no cookie,...
	if (!isset($PHPSESSID)) {
		if (!isset($flash_session_id)) {
			session_unset();
			session_id(generate_id());
		} else {
			session_id($flash_session_id);
		}
		
	// ...if there is a cookie,...
	} else {
		
		session_id($PHPSESSID);
		
		if (isset($session_expTime)) {
			$expTime = $session_expTime;
			$s_expTime = $session_expTime;
		} else {
			$session_expTime = time() + SESSION_TIME;
			$expTime = $session_expTime;
			$c_s_expTime = $session_expTime;
		}
		$diff = $expTime - time();
		
		// If the cookie time has expired, set a new one,...
		if ($diff < 0) {
			session_unset();
			session_id(generate_id());
		
		// ...if it comes from a Flash object,...
		} else if (isset($flash_session_id)) {
			session_id($flash_session_id);
		}
	}
	
	$PHPSESSID = session_id();
	$expTime_future = time() + SESSION_TIME;
	
	setcookie('PHPSESSID', $PHPSESSID, 0, '/');
	setcookie('session_expTime', $expTime_future, 0, '/');

	//$session_expTime = $expTime_future;
?>