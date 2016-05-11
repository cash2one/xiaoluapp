<?php
/**
 * @copyright: @快游戏 2015
 * @description: 检查CPU平台的PSP模拟器插件是否有更新
 * @file: psp_plug_update.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-03-23  18:17
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['current_version'] = intval(get_param('currentVersion')); //本地版本号
$mydata['base_version'] = intval(get_param('baseVersion')); //基础版本号
$mydata['cpu'] = trim(get_param('cpu'));//CPU参数
$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试

/*参数判断*/
if(empty($mydata['cpu']) || empty($mydata['current_version'])){
    exit('参数出错');
}

//查找改cpu下是否有新版本psp插件
$sql = 'SELECT `mp_server_url`,`mp_version`,`mp_md5`
		FROM `mzw_psp_plug`
		WHERE `mp_cup` = '."'".$mydata['cpu']."'".' AND `mp_version` > '.$mydata['current_version'].'
		ORDER BY `mp_version` DESC';
$data = $conn->find($sql);

//开启调试数据显示
if($is_bug_show == 100){
    echo($sql);
    var_dump($data);
    exit;
}

//无相应数据返回404
if(empty($data)){
    header('HTTP/1.1 404 Not Found');
    exit;
}

//$go_to_url = 'http://test.admin.kuaiyouxi.com/uploads' . $data[0]['mp_server_url'];
$go_to_url = CDN_LESHI_URL_DOWN . $data[0]['mp_server_url']; //psp插件更新跳转链接
$version = $data[0]['mp_version']; //psp版本号
$file_md5 = $data[0]['mp_md5']; //文件的md5
header("md5: $file_md5");
header("version: $version");
header("Location: $go_to_url");
exit;

