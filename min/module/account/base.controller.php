<?php
namespace min\module\account;
use min\inc\app;
class base{    

    public function __construct(){  
	
		if( empty( $_SESSION['logined']) ||TRUE != $_SESSION['logined'] ){
		 
			header('Location: http://passport.qi.com/login.html');
			exit;
		}
    }

}