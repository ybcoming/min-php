<?php
namespace Min;

class Di 
{
    private $definitions = [];
    private $instances = [];

    public function getService($name, $params = null)
	{
		if (empty($name)) {
			return null;
		}
        if (isset($this->instances[$name])) {
			if (!empty($params)) {	
				$this->instances[$name]->init($params);
			}
            return $this->instances[$name];
        }

        if (!isset($this->definitions[$name])) {
			return null; 
        }
        
        $concrete = $this->definitions[$name]['class'];
        
        $obj = null;

        if ($concrete instanceof \Closure) {

			$obj = $concrete($params);
			
        } elseif (is_string($concrete)) {
			
			try {
				$obj = new $concrete;
				if (!empty($params)) {
					$obj->init($params);
				}
			} catch (\Throwable $t) {
				watchdog($t);
				$obj = null;
			}
		}
        if (true === $this->definitions[$name]['shared']  && !is_null($obj)) {
            $this->instances[$name] = $obj;
        }
        
        return $obj;
    }

    public function hasService($name)
	{
        return (!empty($name) && (isset($this->definitions[$name]) || isset($this->instances[$name])));
    }

    public function removeService($name)
	{	
		if (!empty($name)) {	
			unset($this->definitions[$name], $this->instances[$name]);
		}
    }

    public function setService($name,$class)
	{
        $this->registerService($name, $class);
    }

    public function setShared($name, $class)
	{
        $this->registerService($name, $class, true);
    }

    private function registerService($name, $class, $shared = false)
	{	
		if (empty($name)) {	
			throw new \Min\MinException('service name can not be empty');
		}
        $this->removeService($name);
        if (!($class instanceof \Closure) && is_object($class)) {
            $this->instances[$name] = $class;
        } else {
            $this->definitions[$name] = ['class'=>$class, 'shared'=>$shared];
        }
    }
	
}