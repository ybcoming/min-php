<?php
namespace min\module\account;
use min\inc\app;
class regist extends base{    

	 public function __construct($action){  
		parent::__construct();
		
		if($action == 'success')
			$this->success();
		exit;
		
	 }
	private function success(){
		 
		app::layout(); 
	
	}
	
	
	 
	
}