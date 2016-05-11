<?PHP
/**
 * @copyright: @快游戏 2015
 * @description: 通过外网IP从服务器获取内网信息
 * @file: get_game.php
 * @author: xiongjianba
 * @charset: UTF-8
 * @time: 2015-01-13  12:08
 * @version 1.0
 **/
include_once("../../config.inc.php");
include_once("../../config.open.api.php");
include_once("../../db.config.inc.php");
/*参数*/

$mydata = array();

$mydata['key'] = trim(get_param('key'));//验证KEY


//验证key是否正确
verify_key_kyx($mydata['key']);


$mydata['time'] = time();
$mydata['outip'] = get_onlineip();//客户端外网IP
if(!empty($mydata['outip']) && strstr($mydata['outip'],',')){
    $mydata['outip'] = strstr($mydata['outip'], ',', true);
}

//定义日志文件的名称
define('LOG_TYPE', 'get_game');
// $obj_log = Log::get_instance();

if(empty($mydata['outip'])){//如果外网IP为空，则出错
	$arr['message']='外网IP为空!';
// 	$obj_log->log('warning',  '外网IP不能为空!');
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
$sql_data = "SELECT * FROM `mzw_game_device` WHERE `wan_ip`='".$mydata['outip']."'" ;
// die($sql_data);
$data = $conn->find($sql_data);
if(count($data)>0){
    $arr = array();
	foreach ($data as $val){
	    
	    $created = $val['created'];
	    if(time()-$created>15*60){ //15分钟的数据
	    	continue;
	    }
	    
	    $tmp_arr = array();
	    $package = $val['game_packagename'];
	    $game_version_code  = intval($val['game_version_code']);
	    $tmp_sql = "SELECT `gv_ico_key` FROM `mzw_game_version` WHERE `gv_version_no`={$game_version_code}  AND  `gv_package_name` = '{$package}'";
	    $res = $conn->get_one($tmp_sql);
	    $gv_ico_key = isset($res['gv_ico_key'])?$res['gv_ico_key']:NULL;
	    unset($res);
	    
	    
	    $gv_game_ico = '';//ICO地址
	    if(!empty($gv_ico_key)){
	        $tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
				LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$gv_ico_key."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
	        $res = $conn->get_one($tmp_sql);
	        $gv_game_ico = $res['img_path'];
	        unset($res);
	    }
		$tmp_arr = array(
			"id"=> intval($val['id']),//系统自增ID
			"output_ip"=>$val['wan_ip'],//外网IP
			"time"=>date('Y-m-d H:i:s',$created),//上报时间
			"ip"=>$val['lan_ip'],//内网IP
		    "port"=> intval($val['lan_port']),//内网端口
			"deviceid"=>$val['device_id'],//设备ID
			"devicebrand"=>$val['device_brand'],//设备品牌
			"devicemodel"=>$val['device_model'],//型号
			"gpu"=>$val['device_gpu'],//GPU
	        "title"=>$val['game_title'],//游戏名称
	        "versioncode"=> $game_version_code,//游戏版本号
		    "versionname"=> trim($val['game_version_name']),//游戏版本名称
		    "sdkbaseversion"=> trim($val['game_sdkbase_version']),//SDK版本号
		    "sdkversion"=> intval($val['game_sdkversion']),//SDK版本号
	        "packagename"=>$package,//游戏包名
		     "mac"=>$val['mac'],//MAC地址
		     "channel"=>$val['channel'],//渠道
			"wifiname"=>$val['net_name'],//wiff名称,
		);
		if(!empty($gv_game_ico)){
		   $tmp_arr['icon'] = LOCAL_URL_DOWN_IMG . $gv_game_ico;
		}
		$arr[]  = $tmp_arr;
	}
	$arr_new['rows'] = $arr;
}else{
    //定义返回的信息
    $arr_new = array('status'=>404,'ret'=>false,'message'=>'获取信息为空');
//     $obj_log->log('warning',  $mydata['outip']  .  '|获取信息为空!');
}
$str_encode = responseJson($arr_new,false);
$conn->close();//显式关闭
unset($conn);
exit($str_encode);

