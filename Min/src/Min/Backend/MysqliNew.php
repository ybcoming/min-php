<?php
/*****
数据库连接成功，但在查询时断掉。。。。。。

query(user')->;

****/
namespace Min\Backend;

use Min\MinException as MinException;

class MysqliNew
{
	private $ref; 
	private $conf = [];
	private $intrans = [];
	private $query_log = [];
	private $connections = [];
	private $active_db	= 'default';

	public function  __construct($db_key = '') 
	{	
		$this->conf = get_config('mysql');
		mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
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
		$linkid = $this->getLinkId($type);
		
		if (empty($this->connections[$linkid])) {
			$this->connections[$linkid] = $this->parse($type);
			$this->connections[$linkid]->set_charset('utf8'); 
		}
		return $this->connections[$linkid];
	}
	
	
	private function parse($type)
	{
		$info	= $this->conf[$this->active_db][$type];
		
		if (empty($info))  throw new \mysqli_sql_exception('mysql info not found when type ='.$type, 1);
		do {
			if (is_array($info)) {
				$db_index = mt_rand(0, count($info) - 1);
				$tmp =  array_splice($info, $db_index, 1);
				$selected_db =  $tmp[0];	
			} else {
				$selected_db = $info;
			}
			
			$selected_db = parse_url($selected_db);
			$selected_db['host'] = urldecode($selected_db['host']);
			$selected_db['user'] = urldecode($selected_db['user']);
			$selected_db['pass'] = isset($selected_db['pass']) ? urldecode($selected_db['pass']) : '';
			$selected_db['fragment'] = urldecode($selected_db['fragment']);
			$selected_db['port'] = $selected_db['port'] ?? null;
		 
			try {		
				$error_code = 0;
				$connect = new \mysqli($selected_db['host'], $selected_db['user'], $selected_db['pass'], $selected_db['fragment'], $selected_db['port']);
			} catch (\Throwable $t) {
				//watchdog($t);
				$error_code = 1;
			}	
		} while ($error_code != 0 && is_array($info) && !empty($info));
		
		if ($error_code != 0) {	
			throw new \mysqli_sql_exception('all mysql servers have gone away', 2);
		}
		return $connect;
	}
	 
	public function query($sql, $marker, $param)
	{

		$this->query_log[] = $sql = strtr($sql, ['{' => $this->conf[$this->active_db]['prefix'], '}' => '']);
		
		watchdog($sql);
		
		$sql_splite = explode(' ', preg_replace('/\s+|\t+|\n+/', ' ', $sql), 2);

		$action = strtolower($sql_splite[0]);
		
		if (!in_array($action, ['select', 'insert', 'update', 'delete'])) {
			throw new \mysqli_sql_exception('Can not recognize action in sql: '. $sql, -4);
		}
		
		$type = (empty($this->intrans[$this->active_db]) && !empty($this->conf[$this->active_db]['rw_separate']) && 'select' == $action) ? 'slave' : 'master'; 

		if (empty($marker)) {
			return $this->nonPrepareQuery($type, $sql, $action);
		} else {
			return $this->realQuery($type, $sql, $action, $marker, $param);
		}
	}
	
	private function realQuery($type, $sql, $action, $marker, $param)
	{
		$round = 5;
		while ($round > 0) {
			$round -- ;
			$on_error = false;	
			try {
				$stmt =  $this->connect($type)->prepare($sql); 
				$merge		= [$stmt, $marker];
				foreach ($param as $value) {
					$merge[] = &$value;		
				}
				if (empty($this->ref)) {
					$this->ref	= new \ReflectionFunction('mysqli_stmt_bind_param');		
				}
				
				$this->ref->invokeArgs($merge);
				$stmt->execute();
				
				switch ($action) {
					case 'update' :	
					case 'delete' :
						$result	= $stmt->affected_rows;
						break;	
					case 'insert' :
						$result	= $stmt->insert_id;
						break;
					case 'single' :	
					case 'couple' :
						if ($get_result = $stmt->get_result()) {
							$result	= $get_result->fetch_all(MYSQLI_ASSOC);
						}
						break;				
				}
				 
				return $result;	
				
			} catch (\Throwable $e) {
				$on_error = true;
				if (empty($this->intrans[$this->active_db]) && ($e instanceof \mysqli_sql_exception) && in_array($e->getCode(), [2006, 2013])) {
					continue; 
				} 
				throw $e;
				
			} finally {
				if (!empty($stmt)) $stmt->close();
				if (!empty($get_result)) $get_result->free();
				
				if (true === $on_error) {
					$this->close($type);
				}
			}
		}
	} 
	
	private function nonPrepareQuery($type, $sql, $action)
	{		
		$round = 5 ;
		while ($round > 0) {
			$round -- ;
			$on_error = false;
			try {
				$get_result	= $this->connect($type)->query($sql, MYSQLI_STORE_RESULT);
				switch ( $action ) {
					case 'update' :
					case 'delete' :
						$result	= $this->connect($type)->affected_rows;
						break;
					case 'insert' :
						$result	= $this->connect($type)->insert_id;
						break;
					case 'select' :		
						$result	= $get_result->fetch_all(MYSQLI_ASSOC);
						break;
				}
				return $result;
			} catch (\Throwable $e) {
				$on_error = true;
				if (empty($this->intrans[$this->active_db]) && ($e instanceof \mysqli_sql_exception) && in_array($e->getCode(), [2006, 2013])) {
					continue; 
				} 
				
				throw $e;
				
			} finally {
				if ($get_result instanceof \mysqli_result) $get_result->free();
				if (true === $on_error) {
					$this->close($type);
				}
			}
		}	
	}
	
	public function tStart() 
	{
		$type = 'master';
		if(empty($this->intrans[$this->active_db])) {
			$this->intrans[$this->active_db] = 1;
			$round = 5;
			while ($round > 0) {
				$round--;
				try {
					$this->connect($type)->begin_transaction();
					return true;
				} catch (\Throwable $e) {
					if (empty($this->intrans[$this->active_db]) && ($e instanceof \mysqli_sql_exception) && in_array($e->getCode(), [2006, 2013])) {
						continue; 
					} else {
						throw $e;
					}
				}
			}
		} else {
			$this->intrans[$this->active_db]++;
			return true;
		}	 
	}
	
	public function tCommit() 
	{	
		$type = 'master';
		if ($this->intrans[$this->active_db] == 1 ) {
			$this->connect($type)->commit(); 
		} 
		$this->intrans[$this->active_db]--;	 
	}
		 
	public function tRollback()
	{ 
		$type = 'master';
		if ($this->intrans[$this->active_db] == 1 ) {
			$this->connect($type)->rollback();
		} 
		$this->intrans[$this->active_db]--;
	}
	
	private function inTransaction(){
		return (!empty($this->intrans[$this->active_db]));
	}
	private function getLinkId($type){
		return $type.$this->active_db;
	}
	
	public function close($type)
	{
		$link_id = $this->getLinkId($type);
		if (!empty($this->connections[$link_id]) {
			$this->connections[$link_id]->close();
			unset($this->connections[$link_id]);
		}
		
	}
		
}