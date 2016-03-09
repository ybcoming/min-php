<?php
namespace min\inc;
class cache{
	
	private $active_db	= 'default';
	private $active_bin = 'redis';
	private $conf =array();
	private $connections = array();
	

	public function  __construct($key=''){
		if(!empty($key)) $this->set_active($key);
	}
	 
	public function set_active_bin($bin_key){
		
		if(!empty($bin_key)) $this->active_bin = $bin_key;;
	}
	
	public function set_active($key){
		
		if(!empty($key)){
			$bin_array	= explode('#',$key);
			if(!empty($bin_array[1])){
				$this->active_bin =  array_shift($bin_array);
			}
			$this->active_db = $bin_array[0];
			if(empty($this->conf[$this->active_bin][$this->active_db])){
				$db_array = explode('-',$bin_array[0]);
				$conf_key	= empty($db_array[1]) ? 'default': $db_array[0];
				include MIN_ROOT.'/conf/'.$this->active_bin.'/'.$conf_key.'.conf';	
			}
		}

	}
	 	
	
	public function connect_db(){
	
		if(empty($this->connections[$this->active_db])){
			$info	= $this->conf[$this->active_db];
			
			if(empty($info))  trigger_error('can not get active '.$this->bin.' conf info ['.$this->active_db.']',E_USER_ERROR);
			
			$this->connections[$this->active_db] = new $this->bin($info); 

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
		$retRes = $this->connect_db()->set($key, $value);
		if ($timeOut > 0) $this->connect_db()->setTimeout($key, $timeOut);
		return $retRes;
	}

	/**
	 * 通过KEY获取数据
	 * @param string $key KEY名称
	 */
	public function get($key) {
		$result = $this->connect_db()->get($key);
		return json_decode($result, TRUE);
	}
	
	/**
	 * 删除一条数据
	 * @param string $key KEY名称
	 */
	public function delete($key) {
		return $this->connect_db()->delete($key);
	}
	
	/**
	 * 清空数据
	 */
	public function flushAll() {
		return $this->connect_db()->flushAll();
	}
	
	/**
	 * 数据入队列
	 * @param string $key KEY名称
	 * @param string|array $value 获取得到的数据
	 * @param bool $right 是否从右边开始入
	 */
	public function push($key, $value ,$right = true) {
		//$value = json_encode($value);
		return $right ? $this->connect_db()->rPush($key, $value) : $this->connect_db()->lPush($key, $value);
	}
	
	/**
	 * 数据出队列
	 * @param string $key KEY名称
	 * @param bool $left 是否从左边开始出数据
	 */
	public function pop($key , $left = true) {
		$val = $left ? $this->connect_db()->lPop($key) : $this->connect_db()->rPop($key);
		//return json_decode($val);
		return $val;
	}
	
	/**
	 * 数据自增
	 * @param string $key KEY名称
	 */
	public function increment($key) {
		return $this->connect_db()->incr($key);
	}

	/**
	 * 数据自减
	 * @param string $key KEY名称
	 */
	public function decrement($key) {
		return $this->connect_db()->decr($key);
	}
	
	/**
	 * key是否存在，存在返回ture
	 * @param string $key KEY名称
	 */
	public function exists($key) {
		return $this->connect_db()->exists($key);
	}
	 
}