<?php
namespace App\Module\Www;

use Min\App;

class UploadController extends \Min\Controller
{
	public function index_post()
	{
		$upload = new \Min\Upload('imgFile');
		if ($upload->save()) {
			$this->response(['error' => 0, 'url' => $upload->getInfo('url')]);
		} else {
			$this->response(['error' => 1, 'message' => $upload->getError()]);
		}
	} 
}