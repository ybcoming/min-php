<?php 
namespace min\inc;
class log{

	private $default_file_size 	= '1024000';
	private $logs 				= array();
 
	
	public function record(){
		if(empty($this->logs)) return;
		$dest_file = LOG_ROOT.'/'.date('Y').'/'.date('m');
		
		if(!is_dir($dest_file)){
			mkdir($dest_file, 0777, true);
			touch($dest_file);
			chmod($dest_file, 0777);
		}
		
		$dest_file .='/';
		$dest_file .= date('Y-m-d').'.log';
		
		if(is_file($dest_file) && ($this->default_file_size < filesize($dest_file)) ) {
			rename($dest_file, $dest_file.'-BAK-'.time().'.log');
		}
		error_log($this->getlog(), 3, $dest_file, '');
		 
	}
	 
	public function log( $type, $message='', $extra){

		$this->logs[] = ['type'=>$type,'message'=> $message, 'extra'=> $extra]; 	
	}
	
	
	private function getlog(){
	
	// formatter = '{time} {level} {uri} [{url}] [{file}] """{message}"""';
	
		$content = date( 'Y/m/d H:i:s',$_SERVER['REQUEST_TIME']);
		$content .= '  [IP: ';
		$content .= ip_address();
		$content .= ']  [ ';
		$content .= $_SERVER['REQUEST_URI'];
		$content .= ' ] [RECORD: ';
		$content .=  count($this->logs);
		$content .= ' 条]';
		$content .= PHP_EOL;
		
		foreach($this->logs as $v){
			$content .= '[[[[========********  ';
			$content .= $v['type'];
			$content .= '  ********========]]]]]';
			$content .= PHP_EOL;
			$content .= '    SUMMARY: ';
			$content .= $v['message'];
			$content .= PHP_EOL;
			$content .= '    EXTRA: [ ';
			$content .= json_encode($v['extra']);
			$content .= ' ] ';
			$content .= PHP_EOL;
 
		}

		return  $content;
	}
	
	
}