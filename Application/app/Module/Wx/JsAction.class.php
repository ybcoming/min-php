<?php

require APP_PATH.'/Vendor/wechat/wechat.class.php';
require APP_PATH.'/Vendor/wechat/TPWechat.class.php';

class JsAction extends Action{
	
	public function index(){
	
	
		// 获取平台 APPID   type =1 表示公司的 账号 ，
		 
		$conf = M('WeixinAccount')->field('id,user_id,type,app_token,app_encodingAESKey,appid,appsecret')->where(array('type'=>1))->find();
		
		$options = array( 
			'token'=>$conf['app_token'],  
			'encodingaeskey'=>$conf['app_encodingAESKey'],
			'appid'=>$conf['appid'], 
			'appsecret'=> $conf['appsecret']		
		); 
		
		$weObj = new TPWechat($options);
		$time = time();
	
		$noncestr = $this->rand_string(8);
		$url = $_SERVER['HTTP_REFERER'];
		 
	
		$js_ticket = $weObj->getJsSign($url,$time,$noncestr,$conf['appid']);
		 
		echo "
			
			window.wx.config({
			 
			appId: '".$js_ticket['appId']."', 
			timestamp: ".$js_ticket['timestamp'].", 
			nonceStr: '".$js_ticket['nonceStr']."', 
			signature: '".$js_ticket['signature']."',
			jsApiList: ['onMenuShareTimeline',
						'onMenuShareAppMessage',
						'onMenuShareQQ',
						'onMenuShareWeibo',
						'onMenuShareQZone'] 
		});
		
		";
	}
	
	
	public function rand_string( $length = 8 ) {  
 
			$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';  
			$str =	'';
				for ( $i = 0; $i < $length; $i++ )  
				{  
					$str .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
				}  
				return $str;  
		} 
	
	
	 
	
}