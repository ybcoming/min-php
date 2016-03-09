<?php
namespace min\module\util;
use min\inc\app;
class captcha{

	public function __construct($action){
		if($action=='get'){
			$this->get();
		}elseif($action=='check'){
			$this->check();
		}
		exit;
	}
	
	private function get(){
		if( !preg_match('/^[a-z]+$/',$_GET['type']) ){
			trigger_error( 'captcha parameter error', E_USER_ERROR);
		}
		$code = new \min\inc\captcha;
		$code->getcode($_GET['type']);
	}
	
	
	private function check(){
	
		if( !is_numeric($_GET['callback']) || !preg_match('/^[a-z]+$/',$_GET['type']) ){
		
			trigger_error( 'captcha parameter error', E_USER_ERROR);
		}

		$code = new \min\inc\captcha;
		if( true === $code->checkcode($_GET['code'],$_GET['type']) ) {
			app::response(1);
		}

	}

}