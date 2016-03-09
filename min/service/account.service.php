<?php
namespace min\service;
use min\inc\app;
class account
{
	// 是否清理 checkaccount 产生的缓存
	private $cache_clean = false;
	
	/**
	* 检测账号是否存在
	*
	*
	* @param string $name 账号
	* @param string $type : phone/email/name				
	*  
	* @return int
	*   2 账号不存在
	*	1 账号存在
	*	错误: 直接EXIT
	*
	*
	*
	*/

	public function checkaccount($name,$type) {

		if( !in_array($type,['phone','email','name'])){
			trigger_error('parameter error' , E_USER_ERROR);
		}else{
		
			$key		= $type.md5($name);
			$result 	= app::cache('checkaccount')->get($key);
			$this->reg 	= true;
			
			if( empty($result) ){	
				$sql = "SELECT 1 FROM user  WHERE  $type = ? limit 1";
				$sql_result	= app::mysqli('user#user')->query('single',$sql,'s',[$name]);
				if( !empty($sql_result) ){
					$result = 1 ;
				}elseif( $sql_result === null){
					$result = 2 ;	
				}else{
					trigger_error('system error', E_USER_ERROR);
				}
				app::cache()->set($key,$result,3600);
			}
			return $result;
		}
	}

	public function adduserbyphone($phone,$pwd) {
	
		$pwd = password_hash($pwd, PASSWORD_BCRYPT,['cost'=>10]);
		$sql = 'insert into user (`phone`,`pwd`) values(? ,?)';
		return app::mysqli('user#user')->query('insert',$sql,'ss',[$phone,$pwd]);
	}
	
	public function checkpwd($name) {
	
		if(validate('phone',$name)){
			$sql = 'SELECT uid,name, email, phone, pwd FROM user  WHERE phone =?';
         }elseif(validate('email',$name)){
			$sql = 'SELECT uid,name, email, pwd , phone FROM user  WHERE email =?';
		 }elseif(validate('username',$name)){
			$sql = 'SELECT uid,name, email, phone, pwd FROM user  WHERE name =?';
		 }else{
			app::usrerror(0,'用户名或密码错误',['loginname'=>$name]);	
		 }

		$sql_result	= app::mysqli('user#user')->query('single',$sql,'s',[$name]);
		
		return $sql_result;	
	}
	
	public function inituser($user){
	
		if($user['uid']>0){
			// 每次登陆都需要更换session id ;
			session_regenerate_id();
			 
			$nickname = empty($user['name']) ? $user['phone'] : $user['name'];
			
			app::usrerror(-999,$nickname,$user);
			setcookie('nickname',$nickname,0,'/',COOKIE_DOMAIN);
			//app::usrerror(-999,ini_get('session.gc_maxlifetime'));
			// 此处应与 logincontroller islogged 相同
			
			setcookie('logged',1, time()+ ini_get('session.gc_maxlifetime')-10,'/',COOKIE_DOMAIN);
			$_SESSION['logined'] = true;
			$_SESSION['UID'] = $user['uid'];

			//清理 注册缓存
			if($this->cache_clean)
			app::cache('checkaccount')->delete('{phone:}'.md5($_user['phone']));
	
		}
	}



}