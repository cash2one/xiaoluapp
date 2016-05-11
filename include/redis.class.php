<?PHP
//
 /****================================
  * redis 操作类
  * @author ddcai
  * @email 252378189@qq.com
  * @package: redis.class.php
  $c = array(
  "port"=>'3306',//端口
  "host"=>'127.0.0.1'//主机IP
  );
 ==================================***/
class myredis extends redis{
	public function __construct($c){
		parent::__construct();
		if(!is_array($c)){
			return FALSE;
		}
		$this->connect($c['host'],$c['port']);
	}
	
	
	
}
