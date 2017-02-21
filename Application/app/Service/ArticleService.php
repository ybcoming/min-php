<?php
namespace App\Service;

use Min\App;

class ArticleService extends \Min\Service
{
	public function add($param)
	{
		if (!empty($param['id'])) {
			return $this->eidt($param);
		}
		
		$set = [
			'tag' 		=> intval($param['tag']),
			'start' 	=> intval($param['start']), 
			'end' 		=> intval($param['end']), 
			'region' 	=> intval($param['region']),
			'title' 	=> ':title', 
			'desc' 		=> ':desc',
			'icon' 		=> ':icon'
		];
		
		$bind = [
			':title' 	=> $param['title'], 
			':desc'		=> $param['desc'],
			':icon' 	=> $param['icon']
		];
		
		$sql = 'INSERT INTO {article} (`tag`, `start`, `end`, `region`, `title`, `desc`, `icon`) VALUES ('.
			implode(',', $set);

		try {
			$this->DBManager()->transaction_start();
			$this->DBManager()->inTransaction();
			$id = $this->query($sql, $bind);
			$sql_content = 'INSERT INTO {article_content} (id, content) values ('. intval($id). ', :content )';
			$this->query($sql_content, [':content' => $param['content']]);
			$this->DBManager()->transaction_commit();
			return $this->success();
		} catch (\Throwable $t) {
			watchdog($t);
			$this->DBManager()->transaction_rollback();
			return $this->error('失败', 1);
		}
	}	
	
	private function edit($param)
	{
		$param['id'] = intval($param['id']);
		
		if ($param['region'] < 100000000)  $param['region'] = $param['region'] * 1000;
		
		$set = [
			'tag' 		=> intval($param['tag']),
			'start' 	=> intval($param['start']), 
			'end' 		=> intval($param['end']), 
			'region' 	=> intval($param['region']),
			'title' 	=> ':title', 
			'desc' 		=> ':desc',
			'icon' 		=> ':icon'
		];
		
		$bind = [
			':title' 	=> $param['title'], 
			':desc'		=> $param['desc'],
			':icon' 	=> $param['icon']
		];
		
		$sql = 'UPDATE {article} SET ' . \plain_build_query($set, ', ') .' WHERE id = '. $param['id'];

		$result = $this->query($sql, $bind);
		
		$sql_content = 'UPDATE {article_content} SET content = :content  WHERE id = '. $param['id'];
		
		$result_content = $this->query($sql_content, [':content' => $param['content']]);
			 
		if ($result && $result_content) {
			return $this->success();
		} else {
			return $this->error('更新失败', 1);
		}
		
	}

	
	public function list($p)
	{
		//array_walk($p,'trim');
		$param = [];
		$param_processed = [];

		if (preg_match('/^([\d]+,)*[\d]+$/', $p['tag'])) {
			
			$source = article_tags();
			$tag 	= explode(',', $p['tag']);
			
			foreach ($tag as $key => $value) {
				if (!isset($source[$value]))  return $this->error('参数错误', 1);
			}
			if (is_numeric($p['tag'])) {
				$param['filter'][] = 'tag = ' . $p['tag'];
			} else {
				$param['filter'][] = 'tag  in  (' . $p['tag']. ')';	
			}
			$param_processed['tag'] = $tag;
			
		} else {
			return $this->error('参数错误', 1);
		} 
		
		$param_processed['region'][1] = 0;
		
		if (!empty($p['region']) && $region = intval($p['region']) && $region > 1) {
			
			$key = 'regionChain_'. $region;
			$cache = $this->cache('region');
			$region_chain = $cache->get($key);
			if (empty($region_chain)) {
				$region_service = new  \App\Service\Region;
				$region_chain = $region_service->nodeChain($region);
				if (!empty($region_chain)) $cache->set($key, $region_chain);
			}
			if (!empty($region_chain)) { 
				// 省级 
				if ( 0 == $region%10000000) {
					$param['filter'][] = '(region = 0 OR (region >=' . $region .' AND region < ' . ($region + 10000000) . ')';
					//市
				} elseif ( 0 == $region%100000) {	
					$param['filter'][] = '(region = 0 OR region = '. intval($region/10000000). ' OR (region  >= ' . $region .' AND region < ' . $region + 100000 .'))';
					// 倒2 
				} elseif ( 0 == $region%1000) {	
					$param['filter'][] = '(region = 0 OR region = '. intval($region/10000000). ' OR  region = '. intval($region/100000). ' OR  (region  > ' . $region .' AND region < ' . $region + 1000 .'))';
					// 倒一 
				} else {
					$param['filter'][] = '(region = 0 OR  region  =' . $region .')';
				}
				
				$level = 0;
				foreach($region_chain as $key => $value) {
					if (empty($value)) { 
						continue;
					} else {
						$param_processed['region'][++$level] = $value;
					}
				}	
			}  else {
				return $this->error('参数错误', 1);
			} 			
		}
		
		if (!empty($p['author'])) {
			$param['filter'][] = 'author = ' . intval(session_get('UID'));
		}
		
		$param['order'] = ' ';
		$param_processed['order'] = 1;
		if (!empty($p['order'])) {	
			switch (intval($p['order'])) {
				case 1 :
					$param['order'] = ' ORDER BY id DESC ';
					$param_processed['order'] = 1;
					break;
				case 2 :
					$param['order'] = ' ORDER BY ctime DESC ';
					$param_processed['order'] = 2;
					break;
				case 3 :
					$param['order'] = ' ORDER BY end DESC ';
					$param_processed['order'] = 3;
					break;					 
			}
		}
		
		$page		= max(intval($p['page'] ?? 1), 1) - 1;
		$page_size  = max(intval($p['page_size'] ?? 10), 0) ?: 10;
		$param['limit'] = ' LIMIT ' . $page * $page_size . ' ' .$page_size;
		
		$db = $this->DBManager();
		
		$sql_number = 'SELECT count(1) as number FROM {article} WHERE ' . implode(' AND ', $param['filter']); 
		$number = $db->query($sql_number);
		
		if (intval($number[0]['number']) > 0) { 
			$sql = 'SELECT * as number FROM {article} WHERE ' . implode(' AND ', $param['filter']) . $param['order'] . $param['limit'];
			$result['list'] = $db->query($sql);
		} else {
			$result['list'] = [];
		}
		
		$result['params'] 	= $param_processed;
		$result['page'] 	= \result_page($number[0]['number'], $page_size, $page);
		
		$this->success($result);
		
	}
	
	public function detail($id)
	{
		$sql = 'SELECT a.*, ac.content FROM {article} AS a LEFT JOIN {article_content} AS ac on ac.id = a.id  WHERE a.id = '. intval($id) . '  LIMIT 1';
		$result = $this->query($sql);
		if (empty($result)) {	
			return $this->error('数据不存在', 1);	
		} else { 
			return $this->success($result);	
		}
	}
	 
}