<?php
namespace App\Module\Www;

use Min\App;

class LoginController extends \Min\Controller
{
	protected $max_error_time = 9;
	
	public function index_get()
	{
		if (PHP_SESSION_NONE === session_status()) {
			App::initSession(true);  
		} 
		$this->layout('type-login');
	}
	
	public function index_post()
	{	
		$name 		= $_POST['name'];
		$pwd 		= $_POST['pwd'];
		$captcha 	= $_POST['captcha'];
		if (empty($name) || empty($pwd)) {	
			$this->error('账号密码不能为空', 30208);		
		}

		if ($this->loginErrorTimes($name) > 2) {
		
			if (empty($captcha)) $this->error('请输入图片验证码', 30103);
			
			$cap = new \Min\Captcha;
			if (true !== $cap->checkCode($captcha, 'login1')) {
				$this->error('图片验证码错误', 30102);
			}
		}

		//$result = $this->request('\\App\\Service\\Account::checkAccount', ['name' => $name], false, true);
		$account =  $this->request('\\App\\Service\\Account', null, null, false, true);
		$result  = 	$account->checkAccount($name);

		if (0 === $result['statusCode']) {
			if (password_verify($pwd, $result['body']['pwd'])) {				
				session_set('loginerror', null);
				$account->initUser($result['body']);
				$this->success(['message'=>'登陆成功']);
			} else {
				unset($result);
				$result['message'] = '账号密码错误';
			}	 
		} 
		
		$error_times = $this->loginErrorInc($name);
		$result['statusCode'] = ($error_times > 3) ? 30202 : 30201;
		$this->response($result);
	}

	private function loginErrorTimes($key)
	{	 
		$var1 = intval(session_get('loginerror'));
		$key = 'loginerror:'. $key;
		$cache = $this->cache('login');
		$var2 = $cache->get($key);
		if ($var2 == false) {
			$cache->set($key, 0, 7200);
		}
		if ( $var2 > $this->max_error_time ) {
			$this->error('账户已锁定，请2小时后再登录', 30207);
		}
		
		return max($var1, $var2);
	}
	
	private function loginErrorInc($key)
	{	
		$var1 =session_inrc('loginerror');
		$var2 = $this->cache('login')->incr('loginerror:'. $key);
		return max($var1, $var2);
	}
 
}