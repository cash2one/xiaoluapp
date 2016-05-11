<?PHP
/**
 * @copyright: @快游戏 2015
 * @description: 通过外网IP从服务器获取内网信息
 * @file: home_page_game.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2015-01-13  12:08
 * @version 1.0
 **/
include_once("../../config.inc.php");
include_once("../../config.open.api.php");
include_once("../../db.config.inc.php");
/*参数*/

$mydata = array();
$mydata['time'] = time();
$mydata['outip'] = get_onlineip();//客户端外网IP
if(strstr($mydata['outip'],',')){
    $mydata['outip'] = strstr($mydata['outip'], ',', true);
}

//跨域
header("Access-Control-Allow-Origin: *");

//定义日志文件的名称
define('LOG_TYPE', 'get_weixin');
// $obj_log = Log::get_instance();

if(empty($mydata['outip'])){//如果外网IP为空，则出错
    $arr = array('version'=>1,'ret'=>false,'message'=>'外网IP不存在!');
//     $obj_log->log('warning',  '外网IP不存在!');
	$str_encode = responseJson($arr,false);
	$conn->close();//显式关闭
	unset($conn);
	exit($str_encode);
}

if(!filter_var($mydata['outip'],FILTER_VALIDATE_IP)){
    $arr = array('version'=>1,'ret'=>false,'message'=>'外网IP格式不正确!');
//     $obj_log->log('warning', $mydata['outip'] .  '|外网IP格式不正确!');
    $str_encode = responseJson($arr,false);
    $conn->close();//显式关闭
    unset($conn);
    exit($str_encode);
}


//查数据
$sql_data = "SELECT id,wan_ip,lan_ip,net_name,device_id,device_name,device_brand,device_model,device_gpu,created,weixin_key,down
		FROM `mzw_weixin_device` WHERE `wan_ip`='".$mydata['outip']."' ORDER BY `created` DESC" ;
//die($sql_data);
$data = $conn->find($sql_data);
if(count($data)>0){
	$tmp_arr = array();
	foreach ($data as $key=>$val){
	    if($key>=2){
	    	$created = $val['created'];
	    	if(time()-$created>=7*24*60*60){
	    		continue;
	    	}
	    }
		$tmp_arr[] = array(
			"id"=>$val['id'],//系统自增ID
			"output_ip"=>$val['wan_ip'],//外网IP
			"time"=>date('Y-m-d H:i:s',$val['created']),//上报时间
			"device_name"=>$val['device_name'],//设备名称
			"ip"=>$val['lan_ip'],//内网IP
			"device_id"=>$val['device_id'],//设备ID
			"wlan_name"=>$val['net_name'],//wiff名称
			"gpu"=>$val['device_gpu'],//GPU
			"brand"=>$val['device_brand'],//品牌
			"model"=>$val['device_model'],//型号
			"down"=> intval($val['down']),//业务标记，新版本还是旧版本
		);
	}
	$arr  = $tmp_arr;
}else{
     $arr = array('version'=>1,'ret'=>false,'message'=>'获取设备列表为空');
//      $obj_log->log('warning',  $mydata['outip'] . '|获取设备列表为空!');
}
$str_encode = responseJson($arr,false);
$conn->close();//显式关闭
unset($conn);
exit($str_encode);