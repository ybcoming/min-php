<?php
namespace App\Module\Www;

use Min\App;

class RegionController extends \Min\Controller
{
	public function id_get()
	{
		$id = App::getArgs();
		if (!is_numeric($id)) {
			$this->error(1, '参数错误');
		}
		if ($id > 100000000) {
			$result = [];
		} else { 
			$key = 'region_'. $id;
			$cache = $this->cache('region');
			$result = $cache->get($key);
			if (empty($result)) {
				$result = $this->request('\\App\\Service\\Region::node', $id);
				$cache->set($key, $result);
			}
		}		
		$this->success($result);
	}
 
}