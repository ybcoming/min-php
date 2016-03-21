<?php
	error_reporting(E_ALL);
	
	//	$start=microtime(true);
 
	define('DOC_ROOT', __DIR__);
	define('MIN_ROOT', DOC_ROOT.'/../min');
	define('LOG_ROOT', DOC_ROOT.'/../log');

	define('MIN_SITE','http://www.min.com');
	define('ERROR_PAGE','http://www.annqi.com/error.html');

	require MIN_ROOT.'/inc/app.inc';
	
	//	$end	= microtime(true);
