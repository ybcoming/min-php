<?php

class FxAction extends Action{
	
	public function index(){
		
		$openid = A('Auth')->get_openid();
		$uid = M('WeixinUser')->where(array('openid'=>$openid))->getField('user_id');
		$go = $_GET['go'];
		$to = $_GET['to'];
		
		if($go){ 
			$url = 'http://'.$_SERVER['HTTP_HOST'].'/mob/#!/'.str_replace('-','/',$go);
		}elseif($to){
			$url = $to;
		}else{
			$url = 'http://'.$_SERVER['HTTP_HOST'].'/mob/';
		}
		
		if(empty($uid)){
			es_session::set('returnurl',$url);
			$this->redirect('Bind/index'); 
			exit;	
		}

		if(!es_session::get('user_info')){
			$this->login($uid);
		}
		
		
		es_session::restart();
		header('Location: '.$url);
		exit;
	}
	
	public function test(){
		
		require_once APP_PATH.'/Vendor/wechat/wechat.class.php';
		require_once APP_PATH.'/Vendor/wechat/TPWechat.class.php';
		
		//$conf = M('WeixinAccount')->field('id,user_id,type,app_token,app_encodingAESKey,appid,appsecret')->where(array('appid'=>'wx8f3d87b7c2979d2a'))->find();
	 
		//if(empty($conf)) exit('无法提供该公众号服务');
 
		//$account = $conf['id'];
 
		// 缓存 一下
		$options = array( 
			'token'=>'U74D1xdDZMcCk4',  
			'encodingaeskey'=>'paYn3kX9Wdddj6zU74D1xdDZMcCk4CcnxmTHxMhOepQ',
			'appid'=>'wx6eb9561110349124', 
			'appsecret'=> 'c2a4901568152bff6971931cec7a684e'		
		); 
		$url = 'http://w2.mgxz.com/wx/?m=Fx&go=test2';
	 
		$weObj = new TPWechat($options);
		$url = $weObj->getOauthRedirect($url,$account,'snsapi_base');
		
		header("Location: $url"); 
		exit;
		
		
	}
	
	
	public function test2(){
		
		$openid = A('Auth')->get_openid();
		echo $openid;
		exit;
		
		
	}
	
	protected function login($uid){
		
		require_once APP_ROOT_PATH.'system/model/user.php';
		//$user_data = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user where  id =".$uid." and is_delete = 0");
		$user_model = M('User');
		$user_data = $user_model->where(array('id'=>$uid,'is_delete'=>0))->find();
	
		 
	/*
		//载入会员整合
		$integrate_code = strim(app_conf("INTEGRATE_CODE"));
		if($integrate_code!='' && $GLOBALS['request']['from'] != 'wap') //&& $GLOBALS['request']['from'] != 'wap' chenfq by add wap版本时,不做整合登陆
		{
			$integrate_file = APP_ROOT_PATH."system/integrate/".$integrate_code."_integrate.php";
			if(file_exists($integrate_file))
			{
				require_once $integrate_file;
				$integrate_class = $integrate_code."_integrate";
				$integrate_obj = new $integrate_class;
			}	
		}
		if($integrate_obj)
		{			
			$result = $integrate_obj->login($user_name_or_email,$user_pwd);	
							
		}
	*/	
	 
		if( empty( $user_data) ){			
			return false;
		}else{
			$result['user'] = $user_data;
			 
			if($user_data['is_effect'] != 1){
				$result['status'] = 0;
				$result['data'] = ACCOUNT_NO_VERIFY_ERROR;
				return $result;
			}else{

				//if(intval($result['status'])==0) //未整合，则直接成功
				//{
				$result['status'] = 1;
				//}
				
				//登录成功自动检测关于会员等级以及自动登录商家				
				$account_name = $user_data['merchant_name'];
				//$account = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."supplier_account where account_name = '".$account_name."' and is_effect = 1 and is_delete = 0");
				
				$account = M('SupplierAccount')->where(array('account_name'=>$account_name,'is_effect'=>1,'is_delete'=>0))->find();
				
				if($account){
					//$account_locations = $GLOBALS['db']->getAll("select location_id from ".DB_PREFIX."supplier_account_location_link where account_id = ".$account['id']);
					$account_locations = M('SupplierAccountLocationLink')->where(array('account_id'=>$account['id']))->getField('location_id');
					
					$account_location_ids = array(0);
					foreach($account_locations as $row)
					{
						$account_location_ids[] = $row['location_id'];	
					}
					$account['location_ids'] =  $account_location_ids;
					
					es_session::set('account_info',$account);
					
					//$GLOBALS['db']->query("update ".DB_PREFIX."supplier_account set login_ip = '".CLIENT_IP."' where id=".$account['id']);
					M('SupplierAccount')->where(array('id'=>$account['id']))->save(array('login_ip'=>CLIENT_IP));
				}	
				
				//$user_current_group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_group where id = ".intval($user_data['group_id']));
				$UG = M('UserGroup');
				$user_current_group=$UG->where(array('id' => intval($user_data['group_id'])))->find();
				//$user_group = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_group where score <=".intval($user_data['total_score'])." order by score desc");
				$user_group = $UG->where(' score <= '. intval($user_data['total_score']))->find();
				
				
				if($user_current_group['score'] < $user_group['score'])
				{
					$user_data['group_id'] = intval($user_group['id']);
					//$GLOBALS['db']->query("update ".DB_PREFIX."user set group_id = ".$user_data['group_id']." where id = ".$user_data['id']);
					$user_model->where(array('id'=>$user_data['id']))->save(array('group_id'=>$user_data['group_id']));
					$pm_content = '恭喜您，您已经成为'.$user_group['name'].'。';
					if($user_group['discount']<1)
					{
						$pm_content.='您将享有'.($user_group['discount']*10).'折的购物优惠';
					}	
					send_msg($user_data['id'], $pm_content, 'notify', 0);
				}
				
				
				
				//$user_current_level = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_level where id = ".intval($user_data['level_id']));
				//$user_level = $GLOBALS['db']->getRow("select * from ".DB_PREFIX."user_level where point <=".intval($user_data['point'])." order by point desc");
				
				$UL = M('UserLevel');
				$user_current_level= $UL->where( array('id' => intval($user_data['level_id'])))->find();
				$user_level = $UL->where( 'point <= '.intval($user_data['point']))->find();
				
				if($user_current_level['point']<$user_level['point'])
				{
					$user_data['level_id'] = intval($user_level['id']);
					//$GLOBALS['db']->query("update ".DB_PREFIX."user set level_id = ".$user_data['level_id']." where id = ".$user_data['id']);		
					$user_model->where(array('id'=> $user_data['id']))->save(array('level_id' => $user_data['level_id']))	;				
					$pm_content = '恭喜您，您已经成为'.$user_level['name'].'。';	
					send_msg($user_data['id'], $pm_content, 'notify', 0);
				}
				
				if($user_current_level['point']>$user_level['point']){
					$user_data['level_id'] = intval($user_level['id']);
					//$GLOBALS['db']->query("update ".DB_PREFIX."user set level_id = ".$user_data['level_id']." where id = ".$user_data['id']);
					$user_model ->where(array('id' =>$user_data['id']))->save(array('level_id' => $user_data['level_id'] ));
					$pm_content = '很报歉，您已经降为'.$user_level['name'].'。';	
					send_msg($user_data['id'], $pm_content, 'notify', 0);
				}
				
				
				send_system_msg($user_data['id']);
				$user_data = load_user($user_data['id'],true);
				es_session::set('user_info',$user_data);
				$GLOBALS['user_info'] = $user_data;
				es_session::set('user_logined', true);
				$GLOBALS['user_logined'] = true;
				es_session::set('user_logined_time', NOW_TIME);
				
				//$GLOBALS['db']->query("update ".DB_PREFIX."deal_cart set user_id = ".$GLOBALS['user_info']['id']." where session_id = '".es_session::id()."'");
				M('DealCart')->where(array('session_id' => es_session::id()))->save(array('user_id' => $GLOBALS['user_info']['id']));
				require_once APP_ROOT_PATH.'system/model/cart.php';
				load_cart_list(true);
				
				//检测勋章
				//$medal_list = $GLOBALS['db']->getAll("select * from ".DB_PREFIX."medal where is_effect = 1 and allow_check = 1");
				$medal_list = M('Medal')->where(' is_effect = 1 and allow_check = 1 ')->find();
				foreach($medal_list as $medal)
				{
					$file = APP_ROOT_PATH.'system/medal/'.$medal['class_name'].'_medal.php';
					$cls = $medal['class_name'].'_medal';					
					if(file_exists($file))
					{
						require_once $file;
						if(class_exists($cls))
						{
							$o = new $cls;
							$check_result = $o->check_medal();
							if($check_result['status']==0)
							{
								send_msg($user_data['id'], $check_result['info'], 'notify', 0);
							}
						}
					}
				}
				
				//签到
				$signin_result = signin($GLOBALS['user_info']['id']);
				if($signin_result['status'])
				{
					es_session::set("signin_result", $signin_result);
				}
				
				//$GLOBALS['db']->query("update ".DB_PREFIX."user set login_ip = '".CLIENT_IP."',login_time= ".NOW_TIME.",group_id=".intval($user_data['group_id'])." where id =".$user_data['id']);	
				$user_model->where(array('id' =>$user_data['id']))->save(array('login_ip' => CLIENT_IP,'login_time'=> time(),'group_id'=>intval($user_data['group_id'])));
			
				$s_api_user_info = es_session::get("api_user_info");
				
				if($s_api_user_info)
				{
					//$GLOBALS['db']->query("update ".DB_PREFIX."user set ".$s_api_user_info['field']." = '".$s_api_user_info['id']."' where id = ".$user_data['id']." and (".$s_api_user_info['field']." = 0 or ".$s_api_user_info['field']."='')");
					
					$user_model->where(  ' id = '.$user_data['id'].' and ('.$s_api_user_info['field'].' = 0 or '.$s_api_user_info['field'].'=\'\')' )->save(array(  		$s_api_user_info['field'] => $s_api_user_info['id'] ));
					
					
					es_session::delete("api_user_info");
				}
				
				$result['step'] = intval($user_data["step"]);
				
				return $result;
			}
		}
		
		
		
	}
	 
		 
	 
	
}