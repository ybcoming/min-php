<?php

class CommonAction extends Action{
	
	protected  $options =  array();
	
	public function __construct(){
		
		include APP_PATH.'Vendor/wechat/wechat.class.php';

		if(preg_match('/^[a-z0-9]{6,60}$/',$_GET['appid'])) $this->error('参数错误');
		$conf = M('WeixinAccount')->field('authorizer_access_token,app_encodingAESKey')->where('authorizer_appid='.$_GET['appid'])->find();
		
		$this->options = array( 
			'token'=>$conf['authorizer_access_token'],  
			'encodingaeskey'=>$conf['app_encodingAESKey']  
		);
		
	}
	 
	
}