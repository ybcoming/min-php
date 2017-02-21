<?php

class BindAction extends Action{

	public function index(){

		$openid =  A('Auth')->get_openid();
		$this->display();
		exit;
			
	}
	 
	public function bind_commit(){
		
		$vcode = $_POST['sms'];
		$phone = $_POST['mob'];
		$openid = A('Auth')->get_openid();

		 
		if(!preg_match('/^[a-zA-Z0-9_\-]{10,64}$/',$openid)){
			$this->error('参数错误.');
		}
		if(empty($vcode)){
			$this->error('手机验证码不能为空');
		}
		if(!preg_match('/^(13|15|18|14|17)[\d]{9}$/',$phone)){
			$this->error('手机号码格式错误');
		}
		
		// 获取手机验证码
		$sms = M('SmsMobileVerify')->where(array('mobile_phone'=>$phone))->order('add_time desc')->find();
		 
		// 验证是否过期  
		
		if( NOW_TIME - SMS_EXPIRESPAN > $sms['add_time'] ){
			$this->error('验证码过期');
		}
		
		if( $vcode === $sms['code'] ){
			
			// 进行绑定 
			$user = M('User')->where(array('mobile'=>$phone))->find();
			 
			if($user === false) {
				$this->error('操作失败，请重试');
			}elseif(empty($user)){
				require_once APP_ROOT_PATH.'system/model/user.php';
				$u = auto_create(array('mobile'=>$phone,'user_name'=>$phone),1);
				if($u['status']){
					$user = $u['user_data'];
				}else{
					$this->error($u['info']);
				}
			}
		
			$wxu = M('WeixinUser');
			$record = $wxu->where(array('openid'=>$openid))->find();
			 
			if( false === $record ){
				$this->error('操作失败,请重试');
			}elseif(empty($record)){
				$this->error('请先关注我们，再进行帐号绑定');
			}elseif($record['user_id'] === $user['id']){
				$this->success('用户已绑定');
			}else{ 
				$result = $wxu->where('id= '.$record['id'] )->save(array('user_id'=>$user['id']));
				if($result){
					// 删除session 原来的账户信息 
					es_session::delete('user_info');
					es_session::delete('account_info');
					es_session::delete('user_logined');
					es_session::delete('user_logined_time');
					es_session::delete('signin_result');
					$url = 	es_session::get('returnurl');
					if(empty($url)) $url = 'http://'.$_SERVER['HTTP_HOST'].'/mob/';
					//$this->redirect($url);
					header('Location: '.$url);
					exit;
				}else{
					$this->error('绑定失败,请重试');
				}
			}

		}else{
			$this->error('手机验证码错误');
		}
		
		
	}
	 
		 
	 
	
}