<?php
namespace App\Module\Www;

use Min\App;

class IndexController extends \Min\Controller
{
	 

	public function index_get()
	{
		$result = ['menu_active' => 'homepage', 'title' =>'首页'];
		$this->response($result);
	}
	
	public function test()
	{
		$this->response();
	}



}