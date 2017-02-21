<?php
namespace App\Module\Www;

use Min\App;

class SmsController extends \Min\Controller
{
	private function send(){
	
		if (true === $this->check()){
			$phone = $_POST['phone'];
			$sms = new \Min\Sms('reg');	
			
			$sc = $sms->get($phone);
					
			if( false == $sc || 120 < ($_SERVER['REQUEST_TIME']-$sc['ctime']) ){
				
				$code = mt_rand(111111,999999);
				$result = $sms->reg_send(['code'=>$code,'phone'=>$phone ]);
				if (isset($result->code)) {
					if ($result->code ==15)app::usrerror(0,'发送失败，每个号码每小时最多发送7次');
					app::usrerror(0,'发送失败，请重试');
				} else {
					$regmsm = ['code'=>$code,'ctime'=> $_SERVER['REQUEST_TIME']];
					$sms->set($phone,$regmsm);
					//迁移以前记录
					if(isset($sc['code']))	$sms->move($phone,$sc);

					response(1);
					 
				}
			}elseif( 121 > ($_SERVER['REQUEST_TIME'] - $sc['ctime'])){
				usr_error( 0, '请稍等，验证码已发送' );
			}			
		}
	}
	
	private function check(){

		if(!validate('phone',$_POST['phone'])){
			usr_error(100, '手机号码格式错误');
		}else{
			$code = new \Min\Captcha;
			if( true === $code->checkCode($_POST['code'],'reg')){
				$account = app::service('account');
				$result = $account->checkAccount($_POST['phone'],'phone');
				if( 1 == $result ){
					app::usrerror(100,'手机号码已被注册');
				}elseif( 2 == $result ){
					return true;
				}	
			}
			
			return false;
		}
	}
   

}