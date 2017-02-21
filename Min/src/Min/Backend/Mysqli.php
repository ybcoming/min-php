<?php
/*****
数据库连接成功，但在查询时断掉。。。。。。

query(user')->;

****/
namespace Min\Backend;

use Min\MinException as MinException;

class Mysqli
{
	private $ref; 
	private $conf = [];
	private $intrans = [];
	private $query_log = [];
	private $connections = [];
	private $active_db	= 'default';

	public function  __construct($db_key = '') 
	{
		$this->conf = get_config('mysqli');;
	}
	 
	public function init($active_db) 
	{
		if (!empty($active_db) && !empty($this->conf[$active_db])) {
			$this->active_db = $active_db;
		}
		return $this;		
	}
	
	private function connect($type = 'master')
	{
		$link_id = $this->getLinkId($type);
		
		if (empty($this->connections[$link_id])) {

			$this->connections[$link_id] = $this->parse($type);
			
			if (!$this->connections[$link_id]->set_charset('utf8')) {
                $this->genException($type);
            }	
		}
	
		return $this->connections[$link_id];
	}
	
	
	private function parse($type)
	{
		$info	= $this->conf[$this->active_db][$type];

		if (empty($info))  throw new MinException('mysql info not found when type ='.$type, -2);
		
		do {
			if (is_array($info)) {
				$db_index = mt_rand(0, count($info) - 1);
				$tmp =  array_splice($info, $db_index, 1);
				$selected_db =  $tmp[0];	
			} else {
				$selected_db = $info;
			}
			
			$selected_db = parse_url($selected_db);
			
			$selected_db['user'] = urldecode($selected_db['user']);
			$selected_db['pass'] = isset($selected_db['pass']) ? urldecode($selected_db['pass']) : '';
			$selected_db['host'] = urldecode($selected_db['host']);
			$selected_db['fragment'] = urldecode($selected_db['fragment']); //db
			$selected_db['port'] = $selected_db['port'] ?? null;
			
			$connect = new \mysqli($selected_db['host'], $selected_db['user'], $selected_db['pass'], $selected_db['fragment'], $selected_db['port']);
			
		} while ($connect->connect_error && is_array($info) && !empty($info));
		
		if ($connect->connect_error) {	
			throw new MinException('all mysql servers have gone away', -1);
		}
		return $connect;
	}
	
	private function retry($type)
	{
		if (empty($this->intrans[$this->active_db]) &&
			(in_array($this->connect($type)->errno, [2006, 2013]) || false == $this->connect($type)->ping())) {
				watchdog('retry true');
				$this->close($type);
				return true;
			}
 
		$this->genException($type);
	}
	
	public function query($sql, $marker = '', $param = [])
	{
		$this->query_log[] = $sql = strtr($sql, ['{' => $this->conf[$this->active_db]['prefix'], '}' => '']);
		
		watchdog($sql);
		
		$sql_splite = explode(' ', preg_replace('/\s+|\t+|\n+/', ' ', $sql), 2);

		$action = strtolower($sql_splite[0]);
		
		if (!in_array($action, ['select', 'insert', 'update', 'delete'])) {
			throw new MinException('Can not recognize action in sql: '. $sql, -4);
		}
		
		$type = (empty($this->intrans[$this->active_db]) && !empty($this->conf[$this->active_db]['rw_separate']) && 'select' == $action) ? 'slave' : 'master'; 

		if (empty($marker)) {
			return $this->nonPrepareQuery($type, $sql, $action);
		} else {
			return $this->realQuery($type, $sql, $action, $marker, $param);
		}	
	}
	
	public function realQuery($type, $sql, $action, $marker, $param)
	{
		$round = 5;
		while ($round > 0) {
		
			$round -- ;
			$result = false;
			
			if ($stmt = $this->connect($type)->prepare($sql)) {

				$merge		= [$stmt, $marker];
			
				foreach ($param as $key => $value) {
					$merge[] = &$value;		
				}
				 
				if (empty($this->ref)) {
					$this->ref	= new \ReflectionFunction('mysqli_stmt_bind_param');		
				}
					 
				if ($this->ref->invokeArgs($merge) && $stmt->execute()) {
			
					switch ($action) {
						case 'update' :	
						case 'delete' :
							$result	= $stmt->affected_rows;
							break;	
						case 'insert' :
							$result	= $stmt->insert_id;
							break;
						case 'select' :	
							if ($result_single = $stmt->get_result()) {	
								$result = $result_single->fetch_all(MYSQLI_ASSOC);
								$result_single->free_result();
							}  
							break;			
					}
				}
				
				$stmt->close();
			}
			
			watchdog($result, 'query_result');
			
			if (false === $result && $this->retry($type)) {
				continue;
			}
			return $result;
		}
	} 
	
	public function nonPrepareQuery($type, $sql, $action)
	{			
		$round = 5 ;
		while ($round > 0) {
		
			$round -- ;
			$result = false;
			
			if ($result_single	= $this->connect($type)->query($sql, MYSQLI_STORE_RESULT)) {
				switch ( $action ) {
					case 'update' :
					case 'delete' :
						$result	= $this->connect($type)->affected_rows;
						break;
					case 'insert' :
						$result	= $this->connect($type)->insert_id;
						break;
					case 'select' :
						$result	= $result_single->fetch_all(MYSQLI_ASSOC);
						$result_single->free_result();
						break;
				}
			}  
			watchdog($result, 'query_result');
			if (false === $result && $this->retry($type)) {
				continue;
			}	
			return $result;
		}	
	}
	
	public function tStart() 
	{
		$type = 'master'; // 只尝试一次
		if (empty($this->intrans[$this->active_db])) {
			if ($this->connect($type)->begin_transaction()) {
				$this->intrans[$this->active_db] = 1;
				return true;	
			} else {
				$this->genException($type);
			}
		} else {
			$this->intrans[$this->active_db]++;
			return true;
		}
	}
	
	public function tCommit() 
	{	
		$type = 'master';
		$result = true;
		if ($this->intrans[$this->active_db] == 1 ) {
			$result = $this->connect($type)->commit(); 
		}  
		$this->intrans[$this->active_db]--;	
		return $result;
	}
		 
	public function tRollback()
	{ 
		$type = 'master';
		$result = true;
		if ($this->intrans[$this->active_db] == 1 ) {
			$result = $this->connect($type)->rollback();
		} 
		$this->intrans[$this->active_db]--;
		return $result;
	}
	
	private function inTransaction()
	{
		return (!empty($this->intrans[$this->active_db]));
	}
	 
	public function genException($type)
	{
		$link_id = $this->getLinkId($type);
		watchdog($this->connections[$link_id]->error_list, 'msyql_error');
		throw new MinException('sql error', $this->connections[$link_id]->errno);
	}
	
	private function getLinkId($type)
	{
		return $type.$this->active_db;
	}
	
	public function close($type)
	{
		$link_id = $this->getLinkId($type);
		if (!empty($this->connections[$link_id])) {
			$this->connections[$link_id]->close();
			unset($this->connections[$link_id]);
		}	
	}		
}