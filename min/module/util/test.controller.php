<?php
namespace min\module\util;
use min\inc\app;
class test{

	public function __construct($action){
		if($action=='sql'){
			$this->sqltest();
		}elseif($action=='check'){
			$this->check();
		}
		exit;
	}
	
	private function sqltest(){
		
	 
		
	
		
		$sql = 'SELECT SQL_NO_CACHE  uid,name, email, pwd FROM user  WHERE name =?';
		$name='def';
		$sql_result	= app::mysqli('user#user')->pquery('single',$sql,'s',[$name]);

		 var_dump( $sql_result); 
		 
		 
		$sql = 'insert into user (email,name,pwd,phone) values (\'a@qq.com\',\'zhangsan\',\'abcefadfafa\',\'12323232323\')';
		$name='yb@qq.com';
		$sql_result	= app::mysqli()->query('insert',$sql);

		var_dump( $sql_result); 
		 
		 
 
		/*
		$sql = 'SELECT uid,name, email, pwd FROM user  WHERE name =:name';
		
		$driver_options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_SILENT;
		$driver_options[\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY] = false;
		$driver_options[\PDO::ATTR_EMULATE_PREPARES] = false;
		$dbh = new \PDO('mysql:dbname=qi_user;host=127.0.0.1','root','adolf',$driver_options);
		 
		$sth = $dbh->prepare($sql);
		
		
		
		$sth->bindParam(':name', $name, \PDO::PARAM_STR);
		$name ='yb';
		$sth->execute();
		
		$red = $sth->fetchAll();
		
		var_dump($red);
		$name ='yb2';
		 
		$sth->execute();
		sleep(10);
		$yellow = $sth->fetchAll();
		
		var_dump($yellow);
		*/
	}
	
	
	 

}