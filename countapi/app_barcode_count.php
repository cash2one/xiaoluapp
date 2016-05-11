<?php
/**
 * @copyright: @快游戏 2015
* @description: 二维码扫描统计
* @file: app_barcode_count.php
* @author: Chen Zhong
* @charset: UTF-8
* @time: 2015-07-07  19:57
* @version 1.0
**/
include_once("../config.inc.php");
/*
1、	model：型号 字符串
2、	brand：厂商 字符串
3、 releaseversion：固件版本 字符串
4、	versioncode：游戏版本号 整型
5、	versionname：游戏版本名称 字符串
6、	title：游戏标题 字符串
7、	mac：设备MAC地址 字符串
8、	eventid：事件ID 字符串
9、	time：记录时间 long型数据，毫秒级

$mydata = array();
/*参数*/
$mydata['pn'] = get_param('packagename');//游戏包名 字符串
$mydata['sv'] = get_param('sdkversion');//SDK版本 整型
$mydata['ip'] = get_param('gameip');//获取客户端的IP
$mydata['mac'] = get_param('mac');//设备MAC地址 字符串
$mydata['port'] = get_param('gameport');//获取客户端的端口号
$mydata['chl'] = get_param('channel');//渠道 字符串
$mydata['nip'] = get_onlineip(); //客户端ip
$mydata['ua'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''; //浏览器UA

$tmp_str = json_encode($mydata).chr(13).chr(10);
write_file_random($tmp_str,"barcode_scanner",true,date('Ymd',time()));

$go_to_url = CDN_LANXUN_URL_DOWN.'/game/simple/KuaiyouxiController.apk';
header("Location: $go_to_url");
exit;