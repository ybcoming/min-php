<?php
namespace min\module\www;
use min\inc\app;
class index{
	public function __construct($args){
		if($args=='index') {
			$this->index();
		}elseif($args=='test'){
			$this->test();
		}
	}



	private function index(){
 
		app::layout();

	}
	private function test(){
 
		app::layout();

	}



}