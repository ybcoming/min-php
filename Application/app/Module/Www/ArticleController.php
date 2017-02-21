<?php
namespace App\Module\Www;

use Min\App;

class ArticleController extends \Min\Controller
{
	public function onConstruct(){
		require CONF_PATH .'/keypairs.php';
	} 

	public function list_get()
	{
		$result = ['menu_active' => 'article', 'title' =>'文字列表'];
		if (!empty($_GET['collect'])) {
			$body = $this->request('\\App\\Service\\Article::collect', $_GET);			
		} else {
			$body = $this->request('\\App\\Service\\Article::list', $_GET);
		}
		
		$result['list'] = $body['body'];
		$this->response($result);
	}
	
	public function detail_get()
	{
		$id = short_int_convent(App::getArgs());
		
		if(!$id) {
			$this->error(1, '参数错误');
		}
		
		$result = $this->request('\\App\\Service\\Article::detail', $id);
	}
	
	public function edit_get()
	{
		$result = ['menu_active' => 'article_edit', 'title' =>'新增文字'];
		$this->response($result);
	}
	
	public function edit_post()
	{
		$param = [];
		$param['title'] = trim($_POST['title']);
		$param['desc'] 	= trim($_POST['desc']);
		$param['icon'] 	= $_POST['icon'];
		$param['content'] 	= $_POST['content'];
		
		if (!\validate('length', $param['title'], 32,1)) 	$this->error(1, '标题最多包含32个字符');
		if (!\validate('length', $param['desc'], 64,10)) 	$this->error(1, '简介最多包含64个字符');
		if (!\validate('img_url', $param['icon'], 128,20)) 	$this->error(1, '图像url不合法');
		if (!\validate('length', $param['content'], 60000,10)) $this->error(1, '文章内容长度超限制');
		 
		if (!empty($_POST['date_start']) && !\validate('date_Y-m-d', $_POST['date_start'])) {
			$this->error(1, '开始日期格式错误');
		}
		if (!empty($_POST['date_end']) && ! \validate('date_Y-m-d', $_POST['date_end'])) {
			$this->error(1, '结束日期格式错误');
		}
		
		$param['start'] 	= strtr($_POST['date_start'], ['-' =>'']);
		$param['end'] 		= strtr($_POST['date_end'], ['-' =>'']);
		$param['region'] 	= intval($_POST['region']);
		$param['tag'] 		= intval($_POST['tag']);
		$param['content'] 	= \Min\Xss::filter($_POST['content']);

		$this->response($this->request('\\App\\Service\\Article::add', $param));
	}
	
	 


}