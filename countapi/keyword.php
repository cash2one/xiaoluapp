<?php
/**
 * @copyright: @快游戏 2015
* @description: 获取搜索关键词搜索记录
* @file: keyword.php
* @author: chengdongcai
* @charset: UTF-8
* @time: 2015-01-13  21:16
* @version 1.0
**/
include_once("../config.inc.php");
/*参数*/
/*顺序不能搞错*/
$mydata['keyword'] = get_param('keyword');//搜索关键词
$mydata['success'] = intval(get_param('ok'));//搜索成功
$mydata['time'] = time();//搜索时间
$mydata['date'] = date("Ymd",$mydata['time']);//搜索日期
$mydata['md5'] = md5($mydata['keyword']);//搜索关键词的MD5值
$mydata['iscache'] = intval($mydata['iscache']);//搜索来源缓存

$tmp_str = '';
foreach ($mydata as $key => $val){
	if(!is_empty($val)){
		$tmp_str .= $val.'|';
	}else{
		$tmp_str .= '0|';
	}
}
$tmp_str = substr($tmp_str,0,-1).chr(13).chr(10);
//记录日志
write_file_random($tmp_str,"keyword",true);

echo('{"code":1}');

$mydata_kk['bug_show'] = intval(get_param('bug_show'));
if($mydata_kk['bug_show']==100){
	echo($tmp_str);
	var_dump($mydata);
}

