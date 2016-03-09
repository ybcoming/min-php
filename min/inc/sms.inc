<?php
namespace min\inc;

/*


两个数据库，regsms  regsms_out

*/

class sms{
	
	private $appkey ='';
	private $secretkey ='' ;
	private $type;

	public function __construct($type){
		$this->type = $type;
	}
	
	public function reg_send($arr){
	
		if( empty($arr['code']) || empty($arr['phone']) ){
		
			trigger_error('parameter error' , E_USER_ERROR );

		}else{
			include MIN_ROOT . '/vendor/Alidayu/TopSdk.php';
			
			date_default_timezone_set('Asia/Shanghai'); 
			
			$c = new \TopClient($this->appkey,$this->secretkey);
			
			$req = new \AlibabaAliqinFcSmsNumSendRequest;
			//$req->setExtend("123456");
			$req->setSmsType('normal');
			$req->setSmsFreeSignName('注册验证');
			$p = json_encode(['code'=> (string) $arr['code'],'product'=>'【张三测试】']);
			$req->setSmsParam($p);
			$req->setRecNum($arr['phone']);
			$req->setSmsTemplateCode('SMS_5059050');
			
			return $c->execute($req);
		}
		
	}
	
	public function get($name){
	
		$regkey = '{sms:}'.$this->type.$name;
		
		return   app::cache('sms')->get($regkey);
	
	}
	
	public function set($name,$value){
	
		$regkey = '{sms:}'.$this->type.$name;
		return   app::cache('sms')->set( $regkey, $value);
	
	}
	public function move($name,$value){
		
		$regkey = '{sms:}'.$this->type.$name.':'.$value['ctime'];
		
		app::cache('sms_out')->set($regkey,$value['code']);
	
	}
	
	public function check($name,$code){
	
		$sc = $this->get($name);
				
		if( isset($sc['ctime']) && isset($sc['code'])){
			
			if( 600 < ($_SERVER['REQUEST_TIME']-$sc['ctime'] )){
				app::usrerror( 101,'验证码超时，请重新发送' );
			}elseif($sc['code'] == $code){
				return true;
			}else{
				app::usrerror( 101,'验证码错误，请重试' );
			}	
		}else{
			app::usrerror( 101,'验证码错误或超时，请重试' );
		}
	
	}
	
	public function delete($name){
		$sc = $this->get($name);
		$this->move($name,$sc);
		app::cache('sms')->delete('{sms:}'.$this->type.$name);
	}
	
}