<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 记录每次下载时的日志
 * @file: game_down.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-11-10  15:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
//appid brand model cpu gpu sysversion
$mydata['key'] = get_param('key');//验证KEY

$mydata['brand'] = get_param('brand');//品牌
$mydata['model'] = get_param('model');//型号
$mydata['cpu'] = get_param('cpu');
$mydata['gpu'] = get_param('gpu');
$mydata['sysversion'] = get_param('sysversion');//系统版本
$mydata['ip'] = get_onlineip();//获取访问者的IP

$mydata['appid'] = intval(get_param('appid'));//当前游戏版本ＩＤ（gv_id)
if(is_empty($mydata['appid'])|| $mydata['appid']==0 ){
	echo('游戏版本ID为空！');
	exit;
}

$sql = 'UPDATE mzw_game_version SET gv_down_nums = gv_down_nums + 1 WHERE gv_id='.$mydata['appid'];
$rs = $conn->query($sql);
if($rs){
	//把访问日志记录到数据表
	$data = array(
		'gv_id'=>$mydata['appid'],
		'gdl_ip'=>$mydata['ip'],
		'gdl_gpu'=>$mydata['gpu'],
		'gdl_cpu'=>$mydata['cpu'],
		'gdl_in_time'=>time(),
		'gdl_in_date'=>date('Ymd',time()),
		'gdl_brand'=>$mydata['brand'],
		'gdl_model'=>$mydata['model'],
		'gdl_sysversion'=>$mydata['sysversion']
	);
	
	$tmp_str = '';
	foreach ($data as $key => $val){
		if(!is_empty($val)){
			$tmp_str .= $val.'|';
		}else{
			$tmp_str .= '0|';
		}
	}
	$tmp_str = substr($tmp_str,0,-1).chr(13).chr(10);
	//记录日志
	write_file_random($tmp_str,"game_down",true);
	
	//$conn->save('mzw_game_down_log', $data);
	return true;
}else{
	return false;
}