<?php
namespace App\Module\Www;

use Min\App;

class CaptchaController extends \Min\Controller
{
	public function get_get()
	{
		if (\validate('words', $_GET['type'])) {
			$code = new \Min\Captcha;
			$code->getCode($_GET['type']);
		}
	}
	
	public function check_get()
	{ 
		if (is_numeric($_GET['callback']) && \validate('words', $_GET['type'])) { 
			$code = new \Min\Captcha;
			if (true === $code->checkCode($_GET['captcha'], $_GET['type'], false)) {
				$this->success();
			}
		}
		$this->error('验证码错误', 30102);	
	}

}