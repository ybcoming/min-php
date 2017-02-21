<?php
namespace Min;

use Min\App;

class Controller
{	
	protected $sharedService = [];
	protected $cache_key = 'default';
	
	final public function __construct($action)
	{	
		$csrf_key = implode('_', [App::getModule(), App::getController(), App::getAction()]);
		$this->validToken($csrf_key);
		
		if (method_exists($this, 'onConstruct') === true) {
            $this->onConstruct();
        }
		
		$key = $action.'_'.(strtolower($_SERVER['REQUEST_METHOD'])?:'get');
		
		if (method_exists($this, $key)){
			$this->{$key}();
		} else {
			$this->error('无效请求', 404);
		}
		exit; 
	}
	
	final public function request($server, $params = null, $init = null, $exit_on_error = true, $shared = false)
	{
		$concrete = explode('::',$server);
		
		if (empty($concrete[0])) {
			$this->error('服务请求参数错误', 20101);
		}
		
		$class	= $concrete[0] .'Service';
		if (isset($this->sharedService[$class])) {
			$obj = $this->sharedService[$class];
		} else {
			try {
				$obj = new $class;	
			} catch (\Throwable $t) {
				app_exception($t);
			}
			
			if (true === $shared) {
				$this->sharedService[$class] = $obj;
			}
		}
		
		if (!empty($init)) {
			$obj->init($init);	
		}
		
		if (empty($concrete[1])) {
			return $obj;
		}

		try {	
			record_time('service start:'. $server); 
			$result = $obj->{$concrete[1]}($params);
			record_time('service end:'. $server);
			if (0 !== $result['statusCode'] &&  true === $exit_on_error) {
				$this->response($result);
			}
			return $result;	
		} catch (\Throwable $t) {
			app_exception($t);
		}
	}
	final public function layout($layout = 'frame')
	{	
		require VIEW_PATH.'/layout/'.$layout.VIEW_EXT;
		exit;
	}
	
	final public function success($result = [], $layout = 'frame')
	{	
		if (is_string($result)) $result = ['message' => $result];
		$result['statusCode'] = 0;		
		$this->response($result, $layout);
	}

	final public function error($message, $code, $redirect = '')
	{	
		request_not_found($code, $message, $redirect);
	}
	
	final public function response($result = [], $layout = 'frame')
	{		
		IS_AJAX  && ajax_return($result); 		
		IS_JSONP && jsonp_return($result);
		
		require VIEW_PATH.'/layout/'.$layout.VIEW_EXT;
		exit;
	}
	
	final public function validToken($value)
	{	
		if (!IS_GET && (empty($_POST['csrf_token']) || !valid_token($_POST['csrf_token'], $value))) {
			$this->error('表单已过期', 30101);
		}
	}
	
	final public function cache($key = null)
	{
		if (!empty($key)) {
			$this->cache_key = $key;
		}
		
		$cache_setting = get_config('cache');
		$value = $cache_setting[$this->cache_key] ?? $cache_setting['default'];
		return App::getService($value['bin'], $value['key']);
	}

}