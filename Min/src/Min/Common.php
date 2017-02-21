<?php

use Min\App;
	
function t($string, array $args = [], array $options = []) 
{ 
	if (empty($args)) {
		return $string;		
	} else {
		foreach ($args as $key => $value) {
			switch ($key[0]) {
				case '@':
					$value = check_plain($value);
					break;
				case ':':
					$value = check_url($value);
					break;
				case '%':
				default:
					$value = '<em class="placeholder">' . check_plain($value) . '</em>';
					break;

				case '!':
			}
		}
		return strtr($string, $args);
	}
}

function view($result = [], $path = '')
{
	if (empty($path)) {
		$path =  '/'.App::getModule().'/'.  App::getController().'/'.  App::getAction();
	}
	require VIEW_PATH.$path.VIEW_EXT;
	 
}

function autoload($class)
{
	// new \min\service\login;
	// new \min\module\passport\login;
	$path 	= strtr($class, '\\', '/');
	$path_info 	= explode('/', $path, 2);

	switch ($path_info[0]) {
		case 'App' :
			$file	= APP_PATH .'/'. $path_info[1] . PHP_EXT;
			break;
		case 'Min' :
			$file	= MIN_PATH . '/' . $path . PHP_EXT;
			break;
		default :
			return;
	}

	if (is_file($file)) {
		require $file;
	}else{
		throw new \Min\MinException($file.' can not be autoloaded');
	}	
}
function session_get($name)
{
	return $_SESSION[$name] ?? null;
}

function session_set($name, $value)
{	
	$_SESSION[$name] = $value;
}
function session_inrc($name){
	$value = intval(session_get($name));
	session_set($name, ++$value);
	return $value;
}
function session_derc($name){
	$value = intval(session_get($name));
	session_set($name, --$value);
	return $value;
}
function current_path() 
{
  return $_SERVER['PATH_INFO_ORIGIN'].'.html?'.http_build_query($_GET);
}
 
function ip_address() 
{
	static  $ip = null;
	
	if (!isset($ip)) {		
		$ip_address = $_SERVER['REMOTE_ADDR'];

		if (1 == get_config('reverse_proxy')) {
   
			if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				// If an array of known reverse proxy IPs is provided, then trust
				// the XFF header if request really comes from one of them.
				// $reverse_proxy_addresses = variable_get('reverse_proxy_addresses', array());

				// Turn XFF header into an array.
				$forwarded = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

				// Trim the forwarded IPs; they may have been delimited by commas and spaces.
				$forwarded = array_map('trim', $forwarded);

				// Tack direct client IP onto end of forwarded array.
				$forwarded[] = $ip_address;

				// Eliminate all trusted IPs.
				$untrusted = array_diff($forwarded, get_config('reverse_proxy_addresses', []));

				// The right-most IP is the most specific we can trust.
				$ip_address = array_pop($untrusted);
			}
		}
		
		$ip = ip2long($ip_address);
		if (false == $ip){
			watchdog('invalid ip address : '.$ip_address, 'USER_ABNORMAL_IP', 'NOTICE');
		}
	}

	return $ip;
}

function redirect($url, $time = 0, $msg = '') 
{
	$url = str_replace(array('\n', '\r'), '', $url);
	$url = check_url($url);
	$msg = $msg ?: '系统跳转中！';
	if (!headers_sent()) {
		if (0 == $time) {
			header('Location: ' . $url);
		} else {
			header('refresh:'. intval($time). ';url='. $url);
			echo($msg);
		}
		exit();
	} else {
		$str  = '<meta http-equiv="Refresh" content="'. intval($time). ';URL='. $url. '">';
		if ($time != 0) $str .= $msg;
		exit($str);
	}
}

function save_gz($data, $filename)
{
	$gzdata = gzencode($data,6);
	$fp 	= fopen($filename, 'w');
	fwrite($fp, $gzdata);
	fclose($fp);	
}

function short_int($value)
{	
	$value = intval($value);
	if ($value > 0 && $value < \PHP_INT_MAX) {	
		return base_convert($value, 10, 32);
	} else {
		throw new \Exception(1, '整型值错误');
	}
	 
}

function short_int_convent($value)
{
	if (validate('id_base32', $value) && strnatcmp('7vvvvvvvvvvvv', $value) > 0){
		return intval(base_convert($value, 32, 10));
	} else {
		return false;
	}
}

function validate($type, $value, $max = 0, $min = 1)
{
	if (!validate_utf8($value)) return false;
	
	$max = intval($max);
	$min = intval($min);
	
	$pattern = [
		'words' 		=> '/^[a-zA-Z0-9_]+$/',  			// 标准ascii字符串
		'quotes'		=>'/["\'\s]+/u',					// 引号空格
		'nickname'		=> '/^[a-zA-Z0-9\-_\x{4e00}-\x{9fa5}]{3,31}$/u',   // 含中文昵称
		'username'		=>'/^[a-zA-Z0-9\-_]{3,31}$/',						// 用户名
		'email' 		=>'/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',	// 邮箱
		'phone'			=> '/^(13|15|18|14|17)[\d]{9}$/',						// 手机
		'alphabet'		=> '/^[a-z]+$/i',										// 字母不区分大小写
		'date_Y-m-d' 	=> '/^20(1[789]|2[\d])\-(0[1-9]|1[012])\-(0[1-9]|[12][\d]|3[01])$/', //合法日期
		'img_url'	 	=> '@^(http[s]?:)?//[a-zA-Z0-9_.]+(/[a-zA-Z0-9_]+)+(\.(jpg|png|jpeg))?$@',  // 合法图片地址	
		'length'		=> '/^.{'. $min. ','. $max. '}$/us',
		'id_base32'			=> '/^[a-z1-9][a-z0-9]{0,11}$/'
	];
	/*
	if ($type != 'length' && $max > 0) {
		$length = '/^.{'. $min. ','. $max .'}$/us';
		$length_check =  (preg_match($length, $value) == 1);
	} else {
		$length_check = true;
	}
	*/
	 
	return ((preg_match($pattern[$type],$value) == 1) && (!($type != 'length' && $max > 0) || preg_match($pattern['length'], $value) == 1));
	 
}

function validate_utf8($text) 
{
	if (strlen($text) == 0) {
		return TRUE;
	}
	return (preg_match('/^./us', $text) == 1);
}
	
function ajax_return($arr)
{
	if (!headers_sent()) {
		header('Content-Type:application/json; charset=utf-8');
	}
	exit(safe_json_encode($arr));	
}

function jsonp_return($arr)
{ 
	if (is_numeric($_GET['callback'])) {
		if (!headers_sent()) {
			header('Content-Type:application/html; charset=utf-8');
		}
		echo 'callback',$_GET['callback'],'(',safe_json_encode($arr),')';
	}
	exit;
}

function get_token($value = '', $seed = false) 
{	
	if (empty($value)) {
		$value = implode('_', [App::getModule(), App::getController(), App::getAction()]);
	}
	
	$form_id = $value. '_FORM';
	
	if (false === $seed) {
		 $_SESSION[$form_id] = mt_rand(111111, 999999);
	}
	$key = session_id() . get_config('private_key') .$_SESSION[$form_id]. get_config('hash_salt');
	$hmac = base64_encode(hash_hmac('sha256', $value, $key, TRUE));
	return strtr($hmac, array('+' => '-', '/' => '_', '=' => ''));
}

function valid_token($token, $value) 
{
	$form_id = $value. '_FORM';
	if (empty($_SESSION[$form_id])) return false;
	return ($token === get_token($value, true));
}
// 处理url
function check_url($uri) 
{
    $uri = html_entity_decode($uri, ENT_QUOTES, 'UTF-8');
    return check_plain(strip_dangerous_protocols($uri));
}
// 安全的在html中输出字符串	
function check_plain($text) 
{
	return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
// 安全的在js中插入Php代码
function safe_json_encode($var) 
{ 
    return json_encode($var, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
// 从php, html 代码中提取文本
function check_plain_from_html($string) {
    return html_entity_decode(strip_tags($string));
}


function strip_dangerous_protocols($uri) 
{
	$allowed_protocols = array_flip(['http', 'https', 'tel']);
    //$allowed_protocols = array_flip(['ftp', 'http', 'https', 'irc', 'mailto', 'news', 'nntp', 'rtsp', 'sftp', 'ssh', 'tel', 'telnet', 'webcal']);
  
  // Iteratively remove any invalid protocol found.
	do {
		$before = $uri;
		$colonpos = strpos($uri, ':');
		if ($colonpos > 0) {
		// We found a colon, possibly a protocol. Verify.
			$protocol = substr($uri, 0, $colonpos);
			// If a colon is preceded by a slash, question mark or hash, it cannot
			// possibly be part of the URL scheme. This must be a relative URL, which
			// inherits the (safe) protocol of the base document.
			if (preg_match('![/?#]!', $protocol)) {
				break;
			}
			// Check if this is a disallowed protocol. Per RFC2616, section 3.2.3
			// (URI Comparison) scheme comparison must be case-insensitive.
			if (!isset($allowed_protocols[strtolower($protocol)])) {
				$uri = substr($uri, $colonpos + 1);
			}
		}
	} while ($before != $uri);

	return $uri;
}

function watchdog($msg, $channel = 'debug', $level = 'DEBUG',  $extra = [])
{
	if ($msg instanceof \Throwable) {
		$msg = error_message_format($msg);
	} elseif (is_resource($msg)) {
		$msg = 'this is a resource '. get_resource_type($msg);
	} else {
		$msg = json_encode($msg, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	}
	
	App::getService('logger')->log($msg, $level, $channel, $extra);		
}

function get_config($section, $default = null)
{
	static $conf;
	if(empty($conf)) {
		require CONF_PATH.'/settings.php';
	}
	return $conf[$section]?:$default;
}

function site_offline() 
{
    redirect(OFFLINE_PAGE);
}

function request_not_found($code, $message = '请求失败', $redirect = '') 
{	
	if (IS_AJAX || IS_JSONP) {
		$result['statusCode'] = $code;
		$result['message'] = $message;
		if (!empty($redirect)) $result['redirect'] = $redirect;
		IS_AJAX  && ajax_return($result); 		
		IS_JSONP && jsonp_return($result);
	}
	
	$url = empty($_SERVER['HTTP_REFERER'])? null : check_plain($_SERVER['HTTP_REFERER']);
	
	$result = (!empty($url) && preg_match('!^http[s]?:[a-z]+\.'.str_replace('.', '\.', SITE_DOMAIN).'!', $url)) ? ['url'=> $url, 'title'=> '上一页'] : ['url'=> HOME_PAGE, 'title'=> '首页'];
	if ($code == 500) {
		$result['message'] = '<p>
           <strong>服务器遇到一个问题...</strong>
         </p>
         <p>懵啦。。。麻烦您再来一次</p>
		<hr> ';
	} else {
		$result['message'] = ' <p>
           <strong>页面找不到了</strong>
         </p>
         <p>页面可能已经被移出，或者您请求的链接存在错误</p>
         <hr>';
	}	
	view($result, '/layout/404');
	exit;
}	

function app_tails()
{
	record_time('request end');
	// fatal errors 
	$error = error_get_last(); 
	if (isset($error['type'])) {
		$error['title'] = 'Fatal Error Catched By app_tails ';
		$message = error_message_format($error);
		watchdog($message, 'Fatal_Error', 'CRITICAL', debug_backtrace());
	}
	App::getService('logger')->record();
	if (isset($error['type'])) {
		request_not_found(500);
	}
}

function app_error($errno, $errstr, $errfile, $errline)
{	
	$level = [  E_WARNING => 1,
				E_NOTICE => 1,
				E_USER_WARNING => 1,
				E_USER_NOTICE => 1,
				E_STRICT => 1,
				E_DEPRECATED => 1,
				E_USER_DEPRECATED => 1
			];
			
	$type = isset($level[$errno]) ? 'WARNING' : 'ERROR'; 
	
	$message = rtrim($errstr,PHP_EOL)
		.	' in file '
		.	$errfile
		.	'  at line '
		.	$errline
		.	' error code/type: '
		.	$errno;
	
	watchdog($message, 'unexpected_error', $type);
	
	if ($type == 'ERROR') {
		request_not_found(500);
	}
	return true;
}

function app_exception($e, $channel = 'unexpected_expection')
{	
	if ($e instanceof \PDOException) {
		$channel = 'mysql_exception';
	} elseif ($e instanceof \Min\MinException) {
		$channel = 'catched_exception';
	}  
	
	watchdog(error_message_format($e), $channel, 'CRITICAL', $e->getTrace());
	request_not_found(500);
}

function error_message_format(\Throwable $e)
{
	$message =	rtrim($e->getMessage(),PHP_EOL)
		.	' in file '
		.	$e->getFile()
		.	'  at line '
		.	$e->getLine()
		.	' error code/type: '
		.	$e->getCode();
	
	return $message;
}

function  record_time($tag)
{	
	static $last_time;
	if (is_null($last_time)) $last_time = $_SERVER['REQUEST_TIME_FLOAT'];
	$now = microtime(true);
	watchdog($tag. ' total:'. ($now - $_SERVER['REQUEST_TIME_FLOAT']) * 1000 . '#this:'. ($now - $last_time) * 1000, 'timelog' );
	$last_time = $now;
}
function result_page($total, $page_size, $current_page){
	return array(
		'page_total' 	=> ceil($total/$page_size),
		'current_page' 	=> $current_page,
		'data_total' 	=> $total
	
	);
}
function plain_build_query($params, $separator){
	
	$joined = [];
	foreach($params as $key => $value) {
	   $joined[] = "$key=$value";
	}
	return implode($separator, $joined);
}
