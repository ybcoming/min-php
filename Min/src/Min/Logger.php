<?php 
namespace Min;
class Logger
{
	private $logs 				= [];
	private $allowed 			= [];
	private $default_file_size 	= '1024000';
	private $levels = [
        'DEBUG' => 100,
        'INFO' => 200,
        'NOTICE' => 250,
        'WARNING' => 300,
        'ERROR' => 400,
        'CRITICAL' => 500,
        'ALERT' => 550,
        'EMERGENCY' => 600 	// send sms
    ]; 
	
	public function __construct($option = [])
    {
		if (empty($option)) $option = get_config('logger');
        foreach ($this->levels as $key => $value) {
			if (!empty($option[$key])) $this->allowed[$key] = $option[$key];
		}
    }
	 
	public function log($message, $level = 'ERROR', $channel = 'default', $extra = [])
	{	
		//var_dump($message);
		$level = strtoupper($level);
		if (empty($this->allowed[$level])) return;
	
		$this->logs[] = ['level'=>$level, 'channel'=> $channel, 'message'=> $message, 'extra'=> $extra];
		
		if (isset($this->allowed[$level]['handler'])) {
			$handler = new $this->allowed[$level]['handler'];
			$handler->handler($message);
		}
	}
	
	public function record()
	{
		if(empty($this->logs)) return;
		
		$dest_file = LOG_PATH.date('/Y/m/');
		
		if (!is_dir($dest_file)) {
			mkdir($dest_file, 0777, true);
			touch($dest_file);
			chmod($dest_file, 0777);
		}
		
		$dest_file .= date('/Y-m-d').'.log';
		
		if (is_file($dest_file) && ($this->default_file_size < filesize($dest_file))) {
			rename($dest_file, $dest_file.'-BAK-'.time().'.log');
		}
		
		$records =  date('Y/m/d H:i:s', $_SERVER['REQUEST_TIME'])
				. ' [IP: '
				. long2ip(ip_address())
				. '] ['
				. $_SERVER['REQUEST_URI']
				. '] ['
				. ($_SERVER['HTTP_REFERER']??'')
				. '] [pid '
				. getmypid()
				. '] ['
				. session_id()
				. '] ['
				. (session_get('UID') ?: 0)
				. ']'
				. PHP_EOL;
				
		foreach ($this->logs as $log) {
			$records .= '[channel:' . $log['channel'] . '] [' . $log['level'] . '] [info:' . $log['message'] . ']';
			if (!empty($log['extra'])) {
				$records .= ' [extra: '. json_encode($log['extra'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).']';
			}
			$records .= PHP_EOL;	
		}
		$records .= PHP_EOL;
		error_log($records, 3, $dest_file, '');
	}
	
}