<?php 
/*
程序运行必须配置：
php.ini:   	session.gc_maxlifetime;
			session.use_cookies
*/

/* 
预定义通用状态代码 ：-1 到99
			
			系统错误 									-1 
			基本错误，如登录页面的用户名密码错误 		0 
			成功   										1
			图片 显示验证码或验证码错误  				2

自定义错误代码 100+


*/

namespace min\inc;
class app{
	
	protected static $module;
	protected static $controller;
	protected static $action;
	protected static $args;
	protected static $booted = FALSE;
	protected static $container;
	protected static $flash; 
	protected static $variables;
	
	protected static function setcontainer(){
		self::$container = new di();
	}
	protected static function dispatch(){
		// path info 在服务器完成配置
		if(empty($_SERVER['PATH_INFO'])|| !preg_match('/^(?:[a-zA-Z0-9]+\/)+[a-zA-Z0-9]+$/', $_SERVER['PATH_INFO'])) {	
			request_not_found();
		}else{	
			$pathinfo 	= explode('/',$_SERVER['PATH_INFO'],4);
			
			if( empty($pathinfo[3]) ) $pathinfo[3]	= '';
			if( empty($pathinfo[2]) ) $pathinfo[2]	= 'index';
			list(self::$module, self::$controller, self::$action, self::$args) 	= $pathinfo; 
		}
		
		$file	= MIN_ROOT.'/module/'.self::$module.'/'.self::$controller.'.controller.php';
		
		if(!is_file($file)){
			request_not_found();
		}else{
			require($file);
			$controller_name = '\\min\\module\\'.self::$module.'\\'.self::$controller;
 		
			new $controller_name(self::$action);
			
		}
	 
	}
	public static function autoload($class){
		// new \min\service\login;
		// new \min\module\passport\login;
		$list	= explode('\\',$class);
		if('min' != $list[0]) return;
		$type	= $list[1];
		$path	= implode($list,'/');
		if(	$type == 'inc'){
			$file	= MIN_ROOT.'/../'.$path.'.inc';
		}else{
			if($type == 'module') $type = 'controller';
			$file	= MIN_ROOT.'/../'.$path.'.'.$type.'.php';
		}
		require $file;
	}
	public static function tails(){
		// fatal errors 
		$error = error_get_last();
		$log = self::$container->log();
		if ( $error['type'] == E_ERROR ){
			$error['title'] = 'fatal error';
			$message = error_message($error);
			$log->log('fatal.error', $message, debug_backtrace());
		}
		$log->record();
	}
	
	public static function apperror($errno, $errstr, $errfile, $errline){
		
		$level = [  E_WARNING => 'warn',
					E_NOTICE => 'warn',
					E_USER_WARNING => 'warn',
					E_USER_NOTICE => 'warn',
					E_STRICT => 'warn',
					E_DEPRECATED => 'deprecated',
					E_USER_DEPRECATED => 'deprecated'
				];
				
		$type = isset( $level[$errno] ) ? $level[$errno] : 'error'; 

		$message = error_message([	'title'		=> 'unexpected error', 
									'message'	=> $errstr, 
									'file'		=> $errfile, 
									'line'		=> $errline, 
									'type'		=> $errno
								]);
		
		self::$container->log()->log('sys.'.$type, $message, debug_backtrace());
		
		if($type == 'error') {
			self::response(-1);
			exit();
		}
		
		return true;
	}

	public static function appexception($e){
		
		$message = error_message([	'title'		=> 'unexpected expection', 
									'message'	=> $e->getMessage(), 
									'file'		=> $e->getFile(), 
									'line'		=> $e->getLine(),
									'type'		=> $e->getCode()
								]);
		
		self::$container->log()->log('sys.expection', $message, debug_backtrace());
		
		self::response(-1);
		exit;
		 
	}
	
	
	public static function usrerror($code=0, $msg='', $extra=[]){

		self::$container->log()->log('usr.error', $msg, $extra );
		
		if($code == -999) return;
		
		self::response($code,$msg);
		exit();
 
	}
	
	public static function getcontainer(){
	
		return self::$container;		 
	}

	public static function getmodule(){
	
		return self::$module;		 
	}
	public static function getcontroller(){
	
		return self::$controller;		 
	}
	public static function getaction(){
	
		return self::$action;		 
	}
	public static function getargs(){
	
		return self::$args;		 
	}
	
	public static function mysqli($key=''){
	
		return self::$container->mysqli($key);
	}
	public static function service($name){
	
		return self::$container->service($name);
	}
	public static function inc($name){
	
		return self::$container->inc($name);
	}
	
	public static function cache($name=''){
	
		return self::$container->cache($name);
	}
	
	public static function flash( $type, $msg ){
	
		self::$flash[$type][] = $msg;		 
	}
	
	public static function getFlash($type){
	
		return empty($type) ? self::$flash : self::$flash[$type];		 
	}
	public static function setvar( $key, $value){
	
			self::$variables[$key] = $value;
	}
	public static function getvar($key){
		
		return empty(self::$variables[$key]) ? null : self::$variables[$key];
	
	}
	
	public static function view($name=''){
		if(empty($name)){
			include MIN_ROOT.'/view/'.self::$module.'/'.self::$controller.'/'.self::$action.'.tpl';
		}else{
			include MIN_ROOT.'/view/'.$name.'.tpl';
		}
	}
	
	public static function layout($name=''){
	
		if(empty($name)){
			include MIN_ROOT.'/view/layout/frame.tpl';
		}else{
			include MIN_ROOT.'/view/layout/'.$name.'.tpl';
		}
	
	}
	/* 
		@parameter
			$code return code int;
			$msg  return msg string
			$flag if 1 return direct
				else if not ajax and not jsonp  
				redirect to refer
		@result



	*/
	public static function response($code=0, $msg='', $flag=0){
	
		$result = ['status'=>$code];
		
		if($msg != '') $result['message'] = $msg;
		
		if( 1 === $flag ) return $result;

		defined('IS_AJAX') && IS_AJAX ) && ajax_return($result); 		
		defined('IS_JSONP') && IS_JSONP && jsonp_return($result);
		
		// 系统错误 跳转ERROR PAGE
		if( -1 == $code ){ 
			header('Location: '.ERROR_PAGE);
		}else{
			
			if( isset($_SERVER['HTTP_REFERER']) && preg_match('@^http[s]?://[a-z][a-z0-9\.]*\.'.explode('.',MIN_SITE,2)[1] .'(?:/[a-zA-Z0-9]+)+\.html@', $_SERVER['HTTP_REFERER'])){
				$flag = strip_tags($_SERVER['HTTP_REFERER']);
			}else{
				$flag = MIN_SITE;
			}
			header('Location: '.$flag);
		}
		
		exit;
	}
	
	public static function session_init($force=FALSE){
		// 与 未登陆不使用session
		$session_name = 'appid';
		if( empty($_COOKIE['logged']) && $force == false ) return;

		// Use session cookies, not transparent sessions that puts the session id in
  		// the query string.
  		ini_set('session.use_cookies', '1');
  		ini_set('session.use_only_cookies', '1');
  		ini_set('session.use_trans_sid', '0');
  		// Don't send HTTP headers using PHP's session handler.
  		// An empty string is used here to disable the cache limiter.
  		ini_set('session.cache_limiter', '');
  		// Use httponly session cookies.
  		ini_set('session.cookie_httponly', '1');
		// notice   session.cookie_domain is used in account.service.php  inituser() function 
		ini_set('session.gc_maxlifetime',3600);
  		//ini_set('session.cookie_secure', TRUE);
  		define('IS_HTTPS', ( isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') ? TRUE : FALSE);
		ini_set('session.cookie_domain', COOKIE_DOMAIN);
		ini_set('session.save_handler','redis');
		ini_set('session.save_path', 'tcp://127.0.0.1:6379?weight=2, tcp://127.0.0.1:6380?weight=1, tcp://127.0.0.1:6381?weight=2');
		session_name($session_name);
		session_start();
	
	}
	
	public static function run(){
		if(!self::$booted){
		
			self::$booted = TRUE;

			 define('IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($_POST['isajax']) || !empty($_GET['isajax'])) ? true : false);
			 
			 define('IS_JSONP', ( !empty($_POST['isjsonp']) || !empty($_GET['isjsonp'])) ? true : false);

			require MIN_ROOT.'/inc/common.inc';	
			
			spl_autoload_register('self::autoload');
			
			self::setcontainer();
	
			register_shutdown_function('\\min\\inc\\app::tails');
			set_error_handler('\\min\\inc\\app::apperror');
			set_exception_handler('\\min\\inc\\app::appexception');
			
			self::session_init();

			self::dispatch();
		}
	}
}

// run
namespace\app::run();