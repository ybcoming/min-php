<?php

namespace Common;

class Session implements \SessionHandlerInterface{ 
	
	private  $pre = 'MG_SSID_';
	private  $store = null;
	private  $started = false;
	private  $lifetime = 2*24*3600;
	
	public function isStarted(){

		return $started == true;
	}
	
	public function getStore(){
		
		if(empty($this->stroe)){
			
			$memcache = \Yaf_Application::app()->getConfig()->memcache;
			$info = empty($memcache->session)?$memcache->common:$memcache->session;
			 
			$this->store = memcache_connect($info->host, $info->port);
			if(!$this->store) throw new \Min\MinException('Memcache连接失败');
			$this->started = true;
		}
		return  $this->store;
	}

	public function open($save_path, $session_name){

		return true;		 
	}

	public function read($id){
		 
		if (!isset($_COOKIE[session_name()])) {   
			return '';
		}

		return  memcache_get($this->getStore(),$this->pre.$id);
	
	}
	
	public function write($id,$data=''){
		
		if(!$this->started) return true;
		if ($id == '')  $id =session_id() ; 
		memcache_set($this->getStore(),$this->pre.$id,$data,0,time()+$this->lifetime);
	
	}
	
	public function close(){
		return true;
	}
	
	public function destroy($id = null){
		
		if (!$this->started) {
            return true;
        }
		
        if (is_null($id)) {
            $id = session_id();
        }
		
        $this->started = false;
		memcache_delete($this->getStore(), $this->pre.$id);
		
	}
	
	public function gc($maxlifetime){
		return true;
	}


}