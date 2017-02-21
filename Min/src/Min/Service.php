<?php
namespace Min;

class Service
{	
	protected $db_key = 'default';
	protected $cache_key = 'default';
	protected $log_type = 'debug';
	protected $log_level = 'DEBUG';
	
	final public function success($body = [], $message = '操作成功')
	{	
		if (!empty($body)) $result['body'] = $body;
		$result['statusCode'] = 0;
		$result['message'] = $message;
		return $result;
	}

	final public function error($message, $code)
	{	
		return ['statusCode' => $code, 'message' => $message];
	}
	
	final public function queryi($sql, $marker = '', $param = [])
	{	
		record_time('query start');
		$result =  $this->DBManager()->query($sql, $marker, $param);
		record_time('query end');
		return $result;
	}
	
	final public function query($sql, $param = [], $throwable = true)
	{	
		record_time('query start');
		try {	
			$result = $this->DBManager()->query($sql, $param);
		} catch (\Throwable $t) {
			
			if ($param === false || $throwable === false) {
				$result = false;
			} else {
				throw $t;
			}
		}
		
		watchdog($result);
		record_time('query end');
		return $result;
	}
	
	final public function changeDB($key)
	{	
		if (!empty($key)) {
			$this->db_key = $key;
		}
		return $this;
	}
	
	final public function changeCache($key)
	{	
		if (!empty($key)) {
			$this->cache_key = $key;
		}
		return $this;
	}

	final public function DBManager()
	{		
		$db_setting = get_config('backend');
		$value = $db_setting[$this->db_key]?:$db_setting['default'];
		return \Min\App::getService($value['bin'], $value['key']);
	}

	final public function cache($key = null)
	{
		if (!empty($key)) {
			$this->cache_key = $key;
		}
		
		$cache_setting = get_config('cache');
		$value = $cache_setting[$this->cache_key]??$cache_setting['default'];
		return \Min\App::getService($value['bin'], $value['key']);
	}
	
	final public function watchdog($data, $extra = null)
	{	
		watchdog($data, $this->log_type, $this->log_level, $extra);
	}
}