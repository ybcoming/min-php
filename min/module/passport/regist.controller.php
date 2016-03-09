<?php
namespace min\module\passport;
use min\inc\app;
class regist{ 
	public function __construct($action){
		
		if( isset($_SESSION['logined']) && TRUE === $_SESSION['logined'] ){
			redirect(MIN_SITE);
		}elseif ($action == 'sendsms'){		
			$this->send();		
		}elseif( isset($_POST['reg'] )){		
			$this->regWithPostData();		
		}else{
			$this->display();
		}
		exit;

	}
	
	private function display(){
	
		if( !isset($_SESSION) ){
			app::session_init(true);
		}
		app::layout('type-login');
	}
   
   
   	private function send(){
	
		if( true === $this->check() ){
			$phone = $_POST['phone'];
			$sms = new \min\inc\sms('reg');	
			
			$sc = $sms->get( $phone);
					
			if( false == $sc || 120 < ($_SERVER['REQUEST_TIME']-$sc['ctime']) ){
				
				$code = mt_rand(111111,999999);
				$result = $sms->reg_send(['code'=>$code,'phone'=>$phone ]);
				if( isset($result->code) ){
					if($result->code ==15)app::usrerror(0,'发送失败，每个号码每小时最多发送7次');
					app::usrerror(0,'发送失败，请重试');
				}else{
					$regmsm = ['code'=>$code,'ctime'=> $_SERVER['REQUEST_TIME']];
					$sms->set($phone,$regmsm);
					//迁移以前记录
					if(isset($sc['code']))	$sms->move($phone,$sc);

					app::response(1);
					 
				}
			}elseif( 121 > ($_SERVER['REQUEST_TIME']-$sc['ctime'])){
				app::usrerror( 0,'请稍等，验证码已发送' );
			}			
		}
	}
   
    private function regWithPostData(){
	
		if( true === $this->check() ){
		
			if(empty($_POST['mcode'])){
				app::usrerror( 101,'请输入手机验证码' );
			}else{
			
				$sms = new \min\inc\sms('reg');	
				 
				if( true === $sms->check($_POST['phone'],$_POST['mcode'])){

					if( validate('quotes',$_POST['pwd']) ){
						app::usrerror( 102,'密码格式错误' );
					}elseif($_POST['pwd'] != $_POST['pwd']){
						app::usrerror( 103,'两次密码不相同，请重新输入' );
					}

					$account = app::service('account');
					 
					if( $id = $account->adduserbyphone($_POST['phone'],$_POST['pwd'])){
						$sms->delete( $_POST['phone']);
						
						$account->inituser(['phone'=>$_POST['phone'],'uid'=>$id]);
						
						app::response(1);
						
					}else{
						app::response(0,'系统错误，请重试');
					}

				} 
			}
		
		}
	
	
	}
	// 验证手机和图片验证码
	private function check(){

		if( !validate('phone',$_POST['phone']) ){
			app::usrerror( 100,'手机号码格式错误');
		}else{
			$code = app::inc('captcha');
			if( true === $code->checkcode($_POST['code'],'reg')){
				$account = app::service('account');
				$result = $account->checkaccount($_POST['phone'],'phone');
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