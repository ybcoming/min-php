<?php
namespace min\inc;
class di{
	
	private $shared			= array();
	private $definitions	= array();	
	private $singletons		= array();
	
	public function mysqli($key=''){
	
		if(empty($this->singletons['mysqli-inc'])){		
			$this->singletons['mysqli-inc'] = new mysqlidb($key);
			$this->shared['mysqli-inc']	= true;
		}elseif(!empty($key)){
			$this->singletons['mysqli-inc']->set_active_db($key);
		}
		
		return $this->singletons['mysqli-inc'];		
	}
	
	public function cache($key=''){
	
		if(empty($this->singletons['cache-inc'])){		
			$this->singletons['cache-inc'] = new redis($key);
			$this->shared['cache-inc']	= true;
		}elseif(!empty($key)){
			$this->singletons['cache-inc']->set_active_db($key);
		}
		
		return $this->singletons['cache-inc'];		
	}
	public function log(){
	
		if(empty($this->singletons['log-inc'])){		
			$this->singletons['log-inc'] = new log;
			$this->shared['log-inc']	= true;
		}
	 
		return $this->singletons['log-inc'];
	}
	
	public function inc($name,$shared =true){	
		$libraryname	= $name.'-inc';
		if( empty($this->singletons[$libraryname]) ){
			$inc_name	= '\\min\\inc\\'.$name;
			$this->singletons[$libraryname] = new $inc_name;
			if($shared === true )
			$this->shared[$libraryname]	= true;
		} 
		 return $this->singletons[$libraryname];
	}
	public function service($name,$shared = true){
		$servicename	= $name.'-service';
		if( empty($this->singletons[$servicename]) ){
			$service_name	= '\\min\\service\\'.$name;
			$this->singletons[$servicename] = new $service_name;
			if($shared === true )
			$this->shared[$servicename] = true;
		} 
		return $this->singletons[$servicename];
		
	}
 
	public function set($name,$value,$shared = false){		 
		
		if( TRUE === $shared ) { 
			$this->shared[$name] = true;
		}
		
		$this->definitions[$name] = $value;
	}
	
	public function get($name,$parameters=''){

		if( isset($this->shared[$name]) && true === $this->shared[$name] ) {
			if( isset($this->singletons[$name]) ){
				return $this->singletons[$name];
			}
		}
		
		if(isset($this->definitions[$name])){
		
			$define = $this->definitions[$name];
			
	  		if($define instanceof Closure ){
				
				if (is_array($parameters) === true) {
                        $instance = call_user_func_array($define, $parameters);
                } else {
                        $instance = call_user_func($define);
                }
				
	  		}elseif( is_string($define) === true){		

					$instance = new $define();
					
					if (is_array($parameters) === true && count($parameters) > 0) {
						$mirror = new ReflectionClass($define);
						$instance = $mirror->newInstanceArgs($parameters);	 
					}

	  		} elseif (is_object($define)){
			
	  			$instance =  $define;
	  		} else {
			
				trigger_error('service can not be resolved', E_USER_ERROR);
	  		}
			
			if(isset($this->shared[$name]) && true === $this->shared[$name] ){
				$this->singletons[$name] = $instance;
			}
			
			return $instance;
			
		}else{
	  		trigger_error('undefined service', E_USER_ERROR);
		}
	}
	 
}
 