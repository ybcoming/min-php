<?php
namespace App\Module\Www;

use Min\App;

class RedisController extends \Min\Controller
{
	public function sms_get()
	{	
		$args = App::getArgs();
		if (empty($args)) exit('参数错误');
		$regkey = $this->cache()->get($args);
		var_dump($regkey);
	}
	public function set_get()
	{ 
		$args = App::getArgs();
		if (empty($args)) exit('参数错误');
		$tmp = explode('/', $args);
		$this->cache()->set($tmp[0], intval(1));
	}
	 
}