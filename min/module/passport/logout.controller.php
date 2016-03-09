<?php
namespace min\module\passport;
use min\inc\app;
class logout
{    
    public function __construct($action){

			$this->logout();	 
    }

    private function logout() {
	
		if( session_status() === PHP_SESSION_ACTIVE ){
			$_SESSION = array();
			if (ini_get('session.use_cookies')) {
				$params = session_get_cookie_params();
				setcookie(session_name(), '', time() - 42000,
				$params['path'], $params['domain'],
				$params['secure'], $params['httponly']);
			}
			session_destroy();
		}
		
		setcookie('logged',0, 0,'/',COOKIE_DOMAIN);
		setcookie('nickname','',time()-10,'/',COOKIE_DOMAIN);
		redirect(MIN_SITE);
		exit;
	}
}