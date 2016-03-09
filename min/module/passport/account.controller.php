<?php
namespace min\module\passport;
use min\inc\app;
class account{    

    public function __construct($action){  
		switch($action){
		
			case 'phone':
				$this->phone();
				break;
			case 'email':
				$this->email();
				break;
			case 'name':
				$this->name();
				break;
			default:
				exit;
		
		}
		exit;
		 
    }
	
	private function phone(){
	
		if( !validate('phone',$_GET['phone'])){
		
			app::usrerror( 0,'手机号码格式错误');
			
		}else{

			$result = $this->callservice($_GET['phone'],'phone');
			
			app::response($result);
		}
	
	}
	
	
	private function callservice($name,$type){
	
		$service = app::service('account');
		return $service->checkaccount($name,$type);
	
	}
	
}