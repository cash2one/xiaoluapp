<?PHP
/**
 * @copyright: @快游戏 2015
 * @description: 检查客户端是否要更新
 * @file: sdk_check_updates.php
 * @author: xiongjianbang
 * @charset: UTF-8
 * @time: 2016-02-19  12:10
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
//currentVersion：int型，代表SDK当前版本
//baseVersion：int型，代表SDK打包版本
//mac：string类型，代表用户设备唯一标识

// $mac=get_param('mac');//代表用户设备唯一标识
// $packagename=get_param('packagename');//包名

/*参数*/
$currentVersion = intval(get_param('currentVersion'));//代表SDK当前版本
$baseVersion = intval(get_param('baseVersion'));//代表SDK打包版本
$channel=get_param('channel');//渠道参数为空或"" ，代表为快游戏平台
$key=get_param('key');//验证KEY
$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
$type = get_param('type');
if(is_empty($currentVersion)){
	echo('参数不正确');
	exit;
}

//验证key是否正确
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($key,$request);
if(empty($key_auth) || empty($key)){
    exit(ResponseJson(array('code'=>9,'msg'=>'key error')));
}

$sql = 'SELECT * FROM mzw_update_interface_type';
$data = $conn->find($sql);
$arr_type = array();
if(!empty($data)){
	foreach ($data as $value) {
		$arr_type[$value['interface_type']] = $value['id'] ;
	}
}
$json = array();
$returnArr = array('rows'=>array());
$i_type = isset($arr_type[$type])?$arr_type[$type]:0;
if(empty($i_type)){
	exit(ResponseJson(array('code'=>9,'msg'=>'接口类型不正确')));
}

if($currentVersion<=0){
	exit(ResponseJson(array('code'=>9,'msg'=>'SDK当前版本号不正确')));
}
//优先判断渠道是否有对应的类型更新，渠道的类型更新优先级最高
$sql = "SELECT * from mzw_update WHERE `version` >{$currentVersion}  AND base<={$baseVersion} AND qudao='{$channel}'  
			AND i_type={$i_type} AND type=1 order by version desc limit 1";
$data = $conn->get_one($sql);
//没有渠道的类型更新，再判断单独的类型是否有更新
if(empty($data)){
	$sql = "SELECT * from mzw_update WHERE `version` >{$currentVersion}  AND base<={$baseVersion} AND qudao=''
	AND i_type={$i_type} AND type=1 order by version desc limit 1";
	$data = $conn->get_one($sql);
}
//将数据转换成字符串
$json = array(
		'version'=>intval($data['version']),
		'url'=>$data['path'],
		'md5'=>$data['md5'],
		'force'=>$data['force'],
);
unset($data);
$returnArr['rows'][]=$json;

if($is_bug_show==100){
	var_dump($_GET);
	var_dump($_POST);
	echo($sql);
	var_dump($returnArr);
	exit;
}
$str_encode = responseJson($returnArr,false);
exit($str_encode);

