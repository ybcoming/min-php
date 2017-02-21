<?php
/**
 * 微信oAuth认证示例
 */
require APP_PATH.'/Vendor/wechat/wechat.class.php';
require APP_PATH.'/Vendor/wechat/TPWechat.class.php';

class AuthAction extends Action{

	public function get_openid(){
		 
		$token_time = es_session::get('wx_token_time');
		$openid = es_session::get('wx_openid');
		$access_token =  es_session::get('wx_access_token');

		if( $openid && $access_token){

  			if($token_time > time()){
				wx_record($_SESSION,' openid exit: ');
				return $openid; 
				
			}elseif(es_session::get('wx_refresh_token')){
				// 刷新   手册上说 refresh 可以用30天。。。。
				wx_record($_SESSION,' openid refresh: ');
				$appid = es_session::get('wx_appid');
				if(empty($appid)){
					$state = intval($_GET['state']);
					$state or exit('无法提供该公众号服务');
					$appid = M('WeixinAccount')->where(array('id'=>$state))->getField('appid');
					$appid or exit('无法提供该公众号服务');
					es_session::set('wx_appid',$appid);					
				}
				
				$options = array('appid'=>$appid); 
				
				$we_obj = new TPWechat($options);
				$json = $we_obj->getOauthRefreshToken(es_session::get('wx_refresh_token'));
				
				if ($json['openid']) {
					es_session::set('wx_openid',$json['openid']);
					es_session::set('wx_access_token',$json['access_token']);
					es_session::set('wx_token_time',$json['expires_in']+time()-100);
					es_session::delete('wx_refresh_token'); // 删除 不再调用刷新
					return $json['openid'];
				}
			}
			
		}
		

		// session 中未获取到，则走正常流程获取
		
		$code = isset($_GET['code'])?$_GET['code']:'';
		
		wx_record('SESSION',' openid  first get : ');
		
		if ($code) {
			$options = es_session::get('wx_options');
			if(!$options){
			
				$state = intval($_GET['state']);
				$conf = M('WeixinAccount')->field('id,user_id,type,app_token,app_encodingAESKey,appid,appsecret')->where(array('id'=>$state))->find();
				
				if(empty($conf)) exit('无法提供该公众号服务'); 

				$options = array( 
					'token'=>$conf['app_token'],  
					'appid'=>$conf['appid'], 
					'appsecret'=> $conf['appsecret'],
					'encodingaeskey'=> $conf['app_encodingAESKey']
				); 
				es_session::set('wx_options',$options);
			}
			
			$we_obj = new TPWechat($options);

			$json = $we_obj->getOauthAccessToken();
			if (!$json) return false;

			// 设置session
			
			es_session::set('wx_appid',$options['appid']);
			es_session::set('wx_openid',$json['openid']);
			es_session::set('wx_access_token',$json['access_token']);
			es_session::set('wx_token_time',$json['expires_in']+time()-100);
			es_session::set('wx_refresh_token',$json['refresh_token']);
			wx_record($_SESSION,' openid first session ');
			
			return $json['openid'];
			
		}else{
			return false;
		}
		
	}
}
