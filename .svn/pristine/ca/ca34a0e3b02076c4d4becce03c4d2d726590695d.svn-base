<?php
/**
 * @copyright: @快游戏 2015
 * @description: 广告日志记录
 * @file: vert_count.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-15  16:08
 * @version 1.0
 **/
include_once("../config.inc.php");

/*
1、	adid：广告id int
2、 packagename 游戏包名 string
3、 title 游戏或app标题 string
4、	model：型号 字符串 string
5、	brand：品牌 字符串 string
6、 sdkversion SDK版本 int
7、 sdkbaseversion SDK基础版本 int
8、 versioncode 游戏版本号 int
9、 versionname 游戏版本名称 string
10、	mac：设备MAC地址 string
11、	channel：渠道号 string
12、	eventid：事件ID int
13、	time：客户端记录时间 long型数据，毫秒级
14、	source：访问来源 int（1：app 2:SDK）
15、 imei imei串号，在用户未开启wifi无法获取mac地址时用户识别用户唯一性（最长16位） string

/*参数*/
$mydata = array();
$mydata['aid'] = get_param('adid');//广告id 整型
$mydata['pn'] = get_param('packagename');//游戏包名 字符串
$mydata['title'] = get_param('title');//APP标题或游戏标题 字符串
$mydata['adtitle'] = get_param('adtitle');//广告标题 字符串
$mydata['md'] = get_param('model');//型号 字符串
$mydata['bd'] = get_param('brand');//品牌 字符串
$mydata['sdkv'] = get_param('sdkversion');//SDK版本 整型
$mydata['sdkbv'] = get_param('sdkbaseversion');//SDK基础版本 整型
$mydata['vc'] = get_param('versioncode');//游戏版本号 整型
$mydata['vn'] = get_param('versionname');//游戏版本名称 字符串
$mydata['mac'] = get_param('mac');//设备MAC地址 字符串
$mydata['chl'] = get_param('channel');//渠道号 字符串
$mydata['eid'] = get_param('eventid');//事件ID 字符串
$mydata['ct'] = get_param('time');//客户端记录时间 long型数据，毫秒级
$mydata['st'] = microtime_float();//日志记录时间 long型数据，毫秒级
$mydata['ip'] = get_onlineip();//获取客户端的IP 字符串
$mydata['source'] = get_param('source');//访问来源（1：app 2:SDK） 整型
$mydata['imei'] = get_param('imei'); //imei串号，在用户未开启wifi无法获取mac地址时用户识别用户唯一性（最长16位） 字符串

//说明
//片头广告图（10001）
//暂停广告图（10002）
//右下角按钮图广告（10003）
//不显示广告（已安装）（10004）
//开始下载（10005）
//安装成功（10006）
//下载成功（10007）

//如果不是合法的数据，则返回失败标记
if( is_empty($mydata['md'])){
    echo('{"code":0}');
    exit;
}

//记录日志
if($mydata['eid']==10001){ //片头广告统计（展示、点击、跳过）

    $mydata['otype'] = intval(get_param('otype')); //操作类型（1：展示广告 2：点击广告 3：跳过广告）

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"titles_video_vert",true,date('Ymd',time()));
}elseif($mydata['eid']==10002){//暂停广告统计（展示、点击、跳过）

    $mydata['otype'] = intval(get_param('otype')); //操作类型（1：展示广告 2：点击广告 3：跳过广告）

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"pause_video_vert",true,date('Ymd',time()));
}elseif($mydata['eid']==10003){//右下角广告统计（展示、点击、跳过）

    $mydata['otype'] = intval(get_param('otype')); //操作类型（1：展示广告 2：点击广告 3：跳过广告）

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"right_video_vert",true,date('Ymd',time()));
}elseif($mydata['eid']==10006){//安装成功

    $mydata['type'] = intval(get_param('type')); //安装位置（1：片头 2：暂停 3：右下角）

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"video_vert_install",true,date('Ymd',time()));
}elseif($mydata['eid']==10004 || $mydata['eid']==10005 || $mydata['eid']==10007){//不显示广告（已安装），开始下载，下载成功

    $mydata['type'] = intval(get_param('type')); //不显示广告（1：已安装 2：加载不到广告数据 3：加载不到广告图 4：友盟控制不显示）

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"video_vert_down",true,date('Ymd',time()));
}else{ //出错时传的
    $mydata['msg'] = get_param('message');//错误信息
    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"video_vert_error",true,date('Ymd',time()));
}

//释放变量
unset($tmp_str,$mydata);

//返回记录成功的标记
echo('{"code":1}');




