<?php  
	function &drupal_static($name) {
		static $data = array();  
		if (isset($data[$name])) {
			return $data[$name];
		} 
		if (isset($name)) {
			$data[$name] = null;
			return $data[$name];
		}  
	}
	
	function ip_address() {
		static  $ip_address = '';
		Global $reverse_proxy_addresses;
		if (!isset($ip_address)) {
			$ip_address = $_SERVER['REMOTE_ADDR'];

			if ( $conf['reverse_proxy']==1 ) {
       
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
					$untrusted = array_diff($forwarded, $reverse_proxy_addresses);

					// The right-most IP is the most specific we can trust.
					$ip_address = array_pop($untrusted);
				}
			}
		}

		return $ip_address;
	}
	function error_message($error){
			$message = '{"';
			$message .= $error['title'];
			$message .= ': ';
			$message .= rtrim($error['message'],PHP_EOL);
			$message .= '" in file [';
			$message .= $error['file'];
			$message .= ']  at line [';
			$message .= $error['line'];
			$message .= '] [error code/type: ';
			$message .= $error['type'];
			$message .= '] }';
			
			return $message;
	
	}

	function request_not_found() {
	
		if ( defined('IS_AJAX') && IS_AJAX ){
			ajax_return(['status'=>0,'message'=>'请求错误']); 
		}elseif( defined('IS_JSONP') && IS_JSONP ){
			if(is_numeric($_GET['callback'])){
				echo 'callback'.$_GET['callback'].'(-2);';
			}
		}else{
			header('Location: http://www.annqi.com/error.html');
		}
		exit();
	}
	
	function request_error_found($arr) {
 
		if ( defined('IS_AJAX') && IS_AJAX ){
		
			ajax_return($arr);
			 
		}elseif( defined('IS_JSONP') && IS_JSONP ){
			if(is_numeric($_GET['callback'])){
				echo 'callback'.$_GET['callback'].'(-1);';
			}
		}else{
			header('Location: http://www.annqi.com/error.html');
		}
		exit();
	}
	 
	function redirect($url, $time=0, $msg='') {
    //多行URL地址支持
		$url        = str_replace(array("\n", "\r"), '', $url);
		$url 		= strip_tags($url);
		if (empty($msg))
			$msg   = "系统将在{$time}秒之后自动跳转到{$url}！";
		if (!headers_sent()) {
			 
			if (0 === $time) {
				header('Location: ' . $url);
			} else {
				header("refresh:{$time};url={$url}");
				echo($msg);
			}
			exit();
		} else {
			$str  = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
			if ($time != 0)
				$str .= $msg;
			exit($str);
		}
	}
	
	function save_gz($data,$filename){
		$gzdata = gzencode($data,6);
		$fp = fopen($filename, 'w');
		fwrite($fp, $gzdata);
		fclose($fp);	
	}

    function validate($type,$value){
	
		if(!validate_utf8($value)) return false;
		
		if('email' == $type )
		return (bool)filter_var($value, FILTER_VALIDATE_EMAIL);
		
		$pattern = [
			'quotes'=>'/["\'\s]+/u',
			'nickname'	=> '/^[a-zA-Z0-9\-_\x{4e00}-\x{9fa5}]{3,31}$/u',
			'username'=>'/^[a-zA-Z0-9\-_]{3,31}$/',
			'email' => '/^\w+([-.]\w+)*@[A-Za-z0-9]+([.-][A-Za-z0-9]+)*\.[A-Za-z0-9]+$/',
			'phone'		=> '/^(13|15|18|14|17)[\d]{9}$/',
			'alphabet'	=> '/^[a-z]+$/i',
		];
		
		return preg_match($pattern[$type],$value);

	}
	
	function ajax_return($arr){
		header('Content-Type:application/json; charset=utf-8');
		exit(json_encode($arr));	
	}
	function jsonp_return($arr){
	
		if( is_numeric($_GET['callback'])){
			header('Content-Type:application/html; charset=utf-8');
			echo 'callback',$_GET['callback'],'(',json_encode($arr),')';
		}
		exit;
	}
	function validate_utf8($text) {
		if (strlen($text) == 0) {
			return TRUE;
		}
		return (preg_match('/^./us', $text) == 1);
	}
	
	function check_plain($text) {
		return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
	}
	
	function t($string, array $args = array(), array $options = array()) {
	   
		  if (empty($args)) {
			return $string;
		  }
		  else {
			return format_string($string, $args);
		  }
	}


	function format_string($string, array $args = array()) {

		  foreach ($args as $key => $value) {
			switch ($key[0]) {
			  case '@':
				// Escaped only.
				$args[$key] = check_plain($value);
				break;

			  case '%':
			  default:
				// Escaped and placeholder.
				$args[$key] = drupal_placeholder($value);
				break;

			  case '!':
				// Pass-through.
			}
		  }
		  return strtr($string, $args);
	}

	function drupal_placeholder($text) {
		return '<em class="placeholder">' . check_plain($text) . '</em>';
	}