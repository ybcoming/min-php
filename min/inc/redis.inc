<?php
namespace min\inc;
class redis{
	
	private $active_db	= 'default';
	private $conf =array();
	private $connections = array();

	public function  __construct($db_key=''){
		$this->set_active_db($db_key);
	}
	 
	public function set_active_db($db_key){
		
		if(!empty($db_key)) $this->active_db = $db_key;;
		
		if(empty($this->conf[$this->active_db])){
			$conf_array	= explode('#',$this->active_db);
			$conf_key	= empty($conf_array[1]) ? 'default': $conf_array[0];
			include MIN_ROOT.'/conf/redis/'.$conf_key.'.conf';		
		}
	}
	public function connect(){
	
		if(empty($this->connections[$this->active_db])){
			$info	= $this->conf[$this->active_db];
			
			if(empty($info))  trigger_error('can not get active reids conf info',E_USER_ERROR);
			
			$this->connections[$this->active_db] = new \Redis(); 
			$this->connections[$this->active_db]->connect($info['host'], $info['port'],$info['timeout'],null,$info['delay']);	
			if(isset($info['auth'])) $this->connections[$this->active_db]->auth($info['auth']);
			if(isset($info['db']))   $this->connections[$this->active_db]->select($info['db']); 
			$this->connections[$this->active_db]->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);   
 
		}
	
		return $this->connections[$this->active_db];
	}
	 
	
	/**
	 * 设置值
	 * @param string $key KEY名称
	 * @param string|array $value 获取得到的数据
	 * @param int $timeOut 时间
	 */
	public function set($key, $value, $timeOut = 0) {
		//$value = json_encode($value);
		if ($timeOut > 0){
			$retRes = $this->connect()->set($key, $value, $timeOut);
		}else{
			$retRes = $this->connect()->set($key, $value);
		 }
		return $retRes;
	}

	/**
	 * 通过KEY获取数据
	 * @param string $key KEY名称
	 */
	public function get($key) {
		$result = $this->connect()->get($key);
		//return json_decode($result, TRUE);
		return $result;
	}
	
	public function setTimeout($key, $ttl){
		$result = $this->connect()->setTimeout($key, $ttl);
		 
		return $result;
	
	}
	
	/**
	 * 删除一条数据
	 * @param string $key KEY名称
	 */
	public function delete($key) {
		return $this->connect()->delete($key);
	}
	
	/**
	 * 清空数据
	 */
	public function flushAll() {
		return $this->connect()->flushAll();
	}
	
	/**
	 * 数据入队列
	 * @param string $key KEY名称
	 * @param string|array $value 获取得到的数据
	 * @param bool $right 是否从右边开始入
	 */
	public function push($key, $value ,$right = true) {
		//$value = json_encode($value);
		return $right ? $this->connect()->rPush($key, $value) : $this->connect()->lPush($key, $value);
	}
	
	/**
	 * 数据出队列
	 * @param string $key KEY名称
	 * @param bool $left 是否从左边开始出数据
	 */
	public function pop($key , $left = true) {
		$val = $left ? $this->connect()->lPop($key) : $this->connect()->rPop($key);
		//return json_decode($val);
		return $val;
	}
	
	/**
	 * 数据自增
	 * @param string $key KEY名称
	 */
	public function incr($key) {
		return $this->connect()->incr($key);
	}

	/**
	 * 数据自减
	 * @param string $key KEY名称
	 */
	public function decrement($key) {
		return $this->connect()->decr($key);
	}
	
	/**
	 * key是否存在，存在返回ture
	 * @param string $key KEY名称
	 */
	public function exists($key) {
		return $this->connect()->exists($key);
	}
	 
}