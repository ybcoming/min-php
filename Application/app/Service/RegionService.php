<?php
namespace App\Service;

use Min\App;

class RegionService extends \Min\Service
{

	public function node($id)
	{
		$id = intval($id);
		$region = $this->childrenNode($id);
		return $this->success([$id =>$region]);
	}	
	
	private function allChildrenNode($id)
	{
		$sql = 'SELECT id, name, parent_id FROM {region} WHERE parent_id > '. ($id - 1) .' AND parent_id <' .($id + 10000) .' ORDER BY parent_id asc , sort asc';
		$result	= $this->query($sql);
		
		$region = [];
		foreach ($result as $key => &$value) {
			$parent_id = $value['parent_id'] ;
			unset($value['parent_id']);
			$region[$parent_id][] = $value;	
		}
		return $region;
	
	}
	private function childrenNode($id)
	{
		$sql = 'SELECT id, name FROM {region} WHERE parent_id  = '. $id. ' order by id asc';
		$result	= $this->query($sql);
		return $result;
	}	
	  
	public function nodeRange($id)
	{
		//  支持 4，3，2 级ID , 可能Bug： id > 100000000
		$id = intval($id);
		// 四级目录
		if ($id > 100000000) {
			$sql = 'SELECT e.id ,a.id AS pid, b.id AS ppid,  c.id AS pppid FROM yi_region e 
				LEFT JOIN yi_region a ON a.id = e.parent_id
				LEFT JOIN yi_region b ON b.id = a.parent_id  
				LEFT JOIN yi_region c ON c.id = b.parent_id   
				WHERE  e.id = ' . $id;	
		} elseif ($id%10000 > 0) {
			// 二三级目录
			$sql = 'SELECT a.id, a.name, max(b.id) AS max1, min(b.id) AS min1, max(c.id) AS max1,  min(c.id) AS min1 FROM {region} a 
				LEFT JOIN {region} b ON b.parent_id = a.id
				LEFT JOIN {region} c ON c.parent_id = b.id
				WHERE a.id =  ' . $id;
		} 
		
		$sql .= ' LIMIT 1'
		$result	= $this->query($sql);
		return $result;
	}
	
	public function nodeChain($id)
	{
		$id = intval($id);	
		
		$sql = 'SELECT d.id as id1, c.id as id2, b.id as id3, a.id  FROM {region} a
			LEFT JOIN {region} b ON a.parent_id = b.id
			LEFT JOIN {region} c ON b.parent_id = c.id
			LEFT JOIN {region} d ON c.parent_id = d.id
			WHERE a.id = ' . $id .' LIMIT 1';
			
		$result	= $this->query($sql);
		return $result;
	}
 
	
}