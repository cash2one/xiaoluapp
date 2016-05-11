<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 检查客户端是否要更新
 * @file: checkupdates.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-11-13  15:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
//currentVersion：int型，代表SDK当前版本
//baseVersion：int型，代表SDK打包版本
//mac：string类型，代表用户设备唯一标识

/*参数*/
$currentVersion = intval(get_param('currentVersion'));//代表SDK当前版本
$baseVersion = intval(get_param('baseVersion'));//代表SDK打包版本
$mac=get_param('mac');//代表用户设备唯一标识
$channel=get_param('channel');//渠道参数为空或"" ，代表为快游戏平台

$packagename=get_param('packagename');//包名

$key=get_param('key');//验证KEY

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试

if(is_empty($currentVersion)){
	echo('参数不正确');
	exit;
}

//验证key是否正确
verify_key_kyx($key);

$json = array();
$returnArr = array('rows'=>array());

if($currentVersion>0){
	$sql = "SELECT * from mzw_update WHERE `version` >".$currentVersion." AND base<=".$baseVersion." AND qudao='".$channel."' AND type=1 order by version desc limit 1";
	$data = $conn->find($sql);
	if($data)
	{
		// 必须大于设定的初始版本
		//if($base>=$data[0]['base']){
		$version = $data[0]['version'];
		$path = $data[0]['path'];
		$mymd5 = $data[0]['md5'];
		$myforce = $data[0]['force'];
		$json = array(
				'version'=>intval($version),
				'url'=>$path,
				'md5'=>$mymd5,
				'force'=>$myforce
		);
		$returnArr['rows'][]=$json;
		//}
	}
}
if($is_bug_show==100){
	var_dump($_GET);
	var_dump($_POST);
	echo($sql);
	var_dump($returnArr);
	exit;
}
$str_encode = responseJson($returnArr,false);
exit($str_encode);

