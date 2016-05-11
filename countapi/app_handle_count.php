<?php
/**
 * @copyright: @快游戏 2015
* @description: app手柄日志记录
* @file: app_handle_count.php
* @author: Chen Zhong
* @charset: UTF-8
* @time: 2015-06-17  17:09
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
/*顺序不能搞错*/
$mydata['md'] = get_param('model');//型号 字符串
$mydata['bd'] = get_param('brand');//厂商 字符串
$mydata['rs'] = get_param('releaseversion');//固件版本
$mydata['vc'] = get_param('versioncode');//app版本号 整型
$mydata['vn'] = get_param('versionname');//app版本名称 字符串
//$mydata['title'] = get_param('title');//app标题 字符串
$mydata['mac'] = get_param('mac');//设备MAC地址 字符串
$mydata['eid'] = get_param('eventid');//事件ID 字符串
$mydata['ct'] = get_param('time');//客户端记录时间 long型数据，毫秒级
$mydata['ip'] = get_onlineip();//获取客户端的IP
$mydata['lip'] = get_param('localip');//本地ip

//说明
//300001 开始扫描,
//300002 展示游戏列表,
//300003 连接游戏成功,
//300004 连接游戏失败,
//300005 扫描超时
//300006 扫描二维码
//30009 游戏连接断开
//30010 点击手柄图
//30011 点击推荐游戏列表
//30012 一般socketSerever异常
//400001 不足分钟数用户选择继续玩
//400002 不足分钟数用户选择退出
//400003 足分钟数用户选择抽奖
//400004 足分钟数用户选择退出
//400005 分享成功
//400006 点击分享按钮
//400007 用户玩游戏的时长

//如果不是合法的数据，则返回失败标记
if( is_empty($mydata['md']) || is_empty($mydata['bd']) || is_empty($mydata['rs'])){
	echo('{"code":0}');
	exit;
}

//记录日志
if($mydata['eid']==300001 || $mydata['eid']==300005 || $mydata['eid']==300006){//开始扫描、扫描超时、扫描二维码

	$tmp_str = json_encode($mydata).chr(13).chr(10);
	write_file_random($tmp_str,"app_scanning",true,date('Ymd',time()));
}else if($mydata['eid']==300003 || $mydata['eid']==300004 || $mydata['eid']==30009){//连接游戏（成功、失败、断开）

    $mydata['gt'] = get_param('gametitle'); //游戏标题
    $mydata['pg'] = get_param('packagename'); //游戏包名
    $mydata['sbv'] = get_param('sdkbaseversion'); //sdk基础版本号
    $mydata['sv'] = get_param('sdkversion'); //sdk版本号
    $mydata['gvn'] = get_param('gameversionname'); //游戏版本名称
    $mydata['gvc'] = get_param('gameversioncode'); //游戏版本号
    $mydata['gchl'] = get_param('gamechannel'); //渠道
    $mydata['gi'] = get_param('gameip'); //ip地址
    $mydata['gp'] = get_param('gameport'); //端口号
    $mydata['msg'] = get_param('message'); //提示信息
    $mydata['type'] = get_param('type');//扫描类型（0为通过扫描网络进入游戏 1为扫描二维码进入的 3为通过缓存重新连接）
    $mydata['gmac'] = get_param('gamemac');//游戏的mac地址（新加）

	$tmp_str = json_encode($mydata).chr(13).chr(10);
	write_file_random($tmp_str,"app_game_connect",true,date('Ymd',time()));
}else if($mydata['eid']==300002 || $mydata['eid']==30010 || $mydata['eid']==30011){//展示游戏列表、点击手柄图、点击推荐游戏列表

	$mydata['gn'] = get_param('gamename');//游戏名称
	$mydata['gp'] = get_param('gamepad');//手柄图类型（二键、四键、八键）
	
	$tmp_str = json_encode($mydata).chr(13).chr(10);
	write_file_random($tmp_str,"app_game_show_click",true,date('Ymd',time()));
}else if($mydata['eid']==30012){//一般socketSerever异常

    $mydata['msg'] = get_param('message');//提示信息

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"app_socket_exception",true,date('Ymd',time()));
}else if($mydata['eid']==400001 || $mydata['eid']==400002){//不足分钟数用户操作统计（模拟手柄活动结束，不做统计）

    $mydata['gt'] = get_param('gametitle'); //游戏标题
    $mydata['pg'] = get_param('packagename'); //游戏包名
    $mydata['sbv'] = get_param('sdkbaseversion'); //sdk基础版本号
    $mydata['sv'] = get_param('sdkversion'); //sdk版本号
    $mydata['gvn'] = get_param('gameversionname'); //游戏版本名称
    $mydata['gvc'] = get_param('gameversioncode'); //游戏版本号
    $mydata['gchl'] = get_param('gamechannel'); //渠道
    $mydata['gi'] = get_param('gameip'); //ip地址
    $mydata['gp'] = get_param('gameport'); //端口号
    $mydata['gametime'] = get_param('gametime');//游戏时间
    $mydata['gmac'] = get_param('gamemac');//游戏的mac地址（新加）

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"not_satisfy_user_oper",true,date('Ymd',time()));
}else if($mydata['eid']==400003 || $mydata['eid']==400004){//足分钟数用户操作统计（模拟手柄活动结束，不做统计）

    $mydata['gt'] = get_param('gametitle'); //游戏标题
    $mydata['pg'] = get_param('packagename'); //游戏包名
    $mydata['sbv'] = get_param('sdkbaseversion'); //sdk基础版本号
    $mydata['sv'] = get_param('sdkversion'); //sdk版本号
    $mydata['gvn'] = get_param('gameversionname'); //游戏版本名称
    $mydata['gvc'] = get_param('gameversioncode'); //游戏版本号
    $mydata['gchl'] = get_param('gamechannel'); //渠道
    $mydata['gi'] = get_param('gameip'); //ip地址
    $mydata['gp'] = get_param('gameport'); //端口号
    $mydata['gametime'] = get_param('gametime');//游戏时间
    $mydata['gmac'] = get_param('gamemac');//游戏的mac地址（新加）

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"satisfy_user_oper",true,date('Ymd',time()));
}else if($mydata['eid']==400005 || $mydata['eid'] == 400006){//分享（模拟手柄活动结束，不做统计）

    $mydata['sharetype'] = get_param('sharetype');//分享类型

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"game_share",true,date('Ymd',time()));
}else if($mydata['eid']==400007){//用户玩游戏的时长

    $mydata['gt'] = get_param('gametitle'); //游戏标题
    $mydata['pg'] = get_param('packagename'); //游戏包名
    $mydata['sbv'] = get_param('sdkbaseversion'); //sdk基础版本号
    $mydata['sv'] = get_param('sdkversion'); //sdk版本号、、
    $mydata['gvn'] = get_param('gameversionname'); //游戏版本名称
    $mydata['gvc'] = get_param('gameversioncode'); //游戏版本号
    $mydata['gchl'] = get_param('gamechannel'); //渠道
    $mydata['gi'] = get_param('gameip'); //ip地址
    $mydata['gp'] = get_param('gameport'); //端口号
    $mydata['gmac'] = get_param('gamemac');//游戏的mac地址
    $mydata['gtime'] = get_param('gametime');//游戏时间（分钟）

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"play_game_time",true,date('Ymd',time()));
}else{//出错时传的
	
	$mydata['msg'] = get_param('message');//错误信息
	$tmp_str = json_encode($mydata).chr(13).chr(10);
	write_file_random($tmp_str,"app_error",true,date('Ymd',time()));
}
unset($tmp_str,$mydata);
//返回记录成功的标记
echo('{"code":1}');




