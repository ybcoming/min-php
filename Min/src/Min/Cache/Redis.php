<?php
namespace Min\Cache;

class Redis{

	const DISCONNECT = 'REDIS_DISCONNECT';
	private $active	= 'default';
	private $conf 	= [];
	private $pools 	= [];


	public function  __construct($key = '')
	{
		$this->conf = get_config('redis');;
	}
	 
	public function init($key)
	{
		if (!empty($key) && !empty($this->conf[$key])) {
			$this->active = $key;
		}
		return $this;
	}
	public function connect()
	{		
		if (empty($this->conf[$this->active])) {
			throw new \Min\MinException('redis info not found when type ='.$this->active, 10000);
		}			

		$info	= $this->conf[$this->active];
		$linkId = $info['host'].$info['port'];
		
		if (empty($this->pools[$linkId])) {		
			$redis = new \Redis(); 
			$redis->pconnect($info['host'], $info['port'],$info['timeout'],null,$info['delay']);	
			$redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);			
			$this->pools[$linkId] = $redis;	
		}
	
		if (!empty($info['auth'])) $this->pools[$linkId]->auth($info['auth']);
		if (!empty($info['db']))   $this->pools[$linkId]->select($info['db']); 
		return $this->pools[$linkId];
	}
	 
	
	/**
	 * 设置值
	 * @param string $key KEY名称
	 * @param string|array $value 获取得到的数据
	 * @param int $timeOut 时间
	 */
	public function set($key, $value, $timeOut = 0)
	{	
		$value = (is_object($value) || is_array($value)) ? safe_json_encode($value) : $value;
		try { 
			if ($timeOut > 0) {
				$retRes = $this->connect()->setEx($key, $timeOut, $value);
			} else {
				$retRes = $this->connect()->set($key, $value);
			}
			watchdog($retRes, 'redis', 'DEBUG', 'set key:'. $key. '@'. $value);
		} catch (\Throwable $t) {
			watchdog($t, 'redis', 'CRITICAL');
			return self::DISCONNECT;
		} 
	}

	/**
	 * 通过KEY获取数据
	 * @param string $key KEY名称
	 */
	public function get($key, $decode = false) 
	{	
		try { 
			$result = $this->connect()->get($key);
			
			watchdog($result, 'redis', 'DEBUG', 'get key:'. $key);
			
			if	(false !== $result && true === $decode) {
				$d = json_decode($result, true);
			}
			return ($d ?? $result);
		} catch (\Throwable $t) {
			watchdog($t, 'redis', 'CRITICAL');
			return self::DISCONNECT;
		} 
	}
	
	public function setTimeout($key, $ttl)
	{
		try { 
			$result = $this->connect()->setTimeout($key, $ttl);
			watchdog($result, 'redis', 'DEBUG', 'set Timeout:'. $key. '@'. $ttl );
			return $result;
		} catch (\Throwable $t) {
			watchdog($t, 'redis', 'CRITICAL');
			return self::DISCONNECT;
		} 
		
	}
	
	/**
	 * 删除一条数据
	 * @param string $key KEY名称
	 */
	public function delete($key) 
	{
		try { 
			$result = $this->connect()->delete($key);
			watchdog($result, 'redis', 'DEBUG', 'delete key:'. $key);
			return $result;
		} catch (\Throwable $t) {
			watchdog($t, 'redis', 'CRITICAL');
			return self::DISCONNECT;
		} 
	}
	
	/**
	 * 清空数据
	 */
	public function flushAll() 
	{
		try { 
			return $this->connect()->flushAll();
		} catch (\Throwable $t) {
			watchdog($t, 'redis', 'CRITICAL');
			return self::DISCONNECT;
		} 
	}
	
	/**
	 * 数据入队列
	 * @param string $key KEY名称
	 * @param string|array $value 获取得到的数据
	 * @param bool $right 是否从右边开始入
	 */
	public function push($key, $value, $right = true) 
	{
		try { 
			return $right ? $this->connect()->rPush($key, $value) : $this->connect()->lPush($key, $value);
		} catch (\Throwable $t) {
			watchdog($t, 'redis', 'CRITICAL');
			return self::DISCONNECT;
		} 
	}
	
	/**
	 * 数据出队列
	 * @param string $key KEY名称
	 * @param bool $left 是否从左边开始出数据
	 */
	public function pop($key, $left = true) 
	{
		try { 
			$val = $left ? $this->connect()->lPop($key) : $this->connect()->rPop($key);
			return $val;
		} catch (\Throwable $t) {
			watchdog($t, 'redis', 'CRITICAL');
			return self::DISCONNECT;
		} 
	}
	
	/**
	 * 数据自增
	 * @param string $key KEY名称
	 */
	public function incr($key) 
	{
		try { 
			$result = $this->connect()->incr($key);
			watchdog($result, 'redis', 'DEBUG', 'incr key:'. $key);
			return $result;
		} catch (\Throwable $t) {
			watchdog($t, 'redis', 'CRITICAL');
			return self::DISCONNECT;
		} 
	}

	/**
	 * 数据自减
	 * @param string $key KEY名称
	 */
	public function decr($key) 
	{
		try { 
			$result = $this->connect()->decr($key);
			watchdog($result, 'redis', 'DEBUG', 'decr key:'. $key);
			return $result;
		} catch (\Throwable $t) {
			watchdog($t, 'redis', 'CRITICAL');
			return self::DISCONNECT;
		} 
	}
	
	/**
	 * key是否存在，存在返回ture
	 * @param string $key KEY名称
	 */
	public function exists($key) 
	{
		try { 
			return $this->connect()->exists($key);
		} catch (\Throwable $t) {
			watchdog($t, 'redis', 'CRITICAL');
			return self::DISCONNECT;
		} 
	}
	
	public function getDisc() 
	{
		return self::DISCONNECT;
	}
	 
}