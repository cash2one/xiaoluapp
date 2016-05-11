<?php
/**
 * @copyright: @快游戏 2014
* @description: 记录每次下载时速度的日志
* @file: down_speed.php
* @author: chengdongcai
* @charset: UTF-8
* @time: 2014-11-26  13:38
* @version 1.0
**/
include_once("../config.inc.php");

/*参数*/
$mydata = array();
//appid brand model cpu gpu sysversion
$mydata['key'] = get_param('key');//验证KEY

$mydata['mac'] = get_param('mac');//mac地址
$mydata['brand'] = get_param('brand');//品牌
$mydata['model'] = get_param('model');//型号
$mydata['useTime'] = get_param('useTime');//下载使用时间
$mydata['title'] = get_param('title');//游戏名称
$mydata['packagename'] = get_param('packagename');//游戏包名
$mydata['cdnType'] = get_param('cdnType');//下载类型
$mydata['speed'] = get_param('speed');//速度
$mydata['downloadLength'] = get_param('downloadLength');//下载文件长度
$mydata['ip'] = get_param('ip');//IP
if(is_empty($mydata['ip'])|| strlen($mydata['ip'])<8){//如果没有传IP过来，则自己获取
	$mydata['ip'] = get_onlineip();//获取访问者的IP
}
$mydata['appid'] = intval(get_param('appid'));//当前游戏版本ＩＤ（gv_id)
if(is_empty($mydata['appid'])|| $mydata['appid']==0 ){
	echo('游戏版本ID为空！');
	exit;
}

//把下载速度日志记录到数据文件
$data = array(
		'gv_id'=>$mydata['appid'],
		'gdsl_ip'=>$mydata['ip'],
		'gdsl_mac'=>$mydata['mac'],
		'gdsl_title'=>$mydata['title'],
		'gdsl_in_time'=>time(),
		'gdsl_in_date'=>date('Ymd',time()),
		'gdsl_brand'=>$mydata['brand'],
		'gdsl_model'=>$mydata['model'],
		'gdsl_packname'=>$mydata['packagename'],
		'gdsl_use_time'=>$mydata['useTime'],
		'gdsl_cdn_type'=>$mydata['cdnType'],
		'gdsl_speed'=>intval($mydata['speed']),
		'gdsl_down_len'=>intval($mydata['downloadLength'])
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
write_file_random($tmp_str,"down_speed",true);

return true;
