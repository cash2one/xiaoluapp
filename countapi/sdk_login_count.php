<?php
/**
 * @copyright: @快游戏 2015
* @description: 获取日志记录
* @file: sdk_login_count.php
* @author: chengdongcai
* @charset: UTF-8
* @time: 2015-03-12  10:18
* @version 1.0
**/
include_once("../config.inc.php");
/*
1、	model：型号 字符串
2、	brand：厂商 字符串
3、	device：手柄型号字符串
4、	sdkversion：SDK版本 整型
5、	sdkbaseversion：SDK基础版本 整型
6、	versioncode：游戏版本号 整型
7、	versionname：游戏版本名称 字符串
8、	packagename：游戏包名 字符串
9、	title：游戏标题 字符串
10、	mac：设备MAC地址 字符串
11、	channel：渠道号 字符串
12、	eventid：事件ID 字符串
13、	time：记录时间 long型数据，毫秒级
14、networkenv 网络状态

一、SDK开启关闭统计
eventid定义：
1、100001：SDK开启
	无其它参数
2、100002：SDK关闭
	usetime：使用时间，long型数据，毫秒级
*/

$mydata = array();
/*参数*/
/*顺序不能搞错*/
$mydata['md'] = get_param('model');//型号 字符串
$mydata['bd'] = get_param('brand');//厂商 字符串
$mydata['dc'] = get_param('device');//手柄型号字符串
$mydata['sdkv'] = get_param('sdkversion');//SDK版本 整型
$mydata['sdkbv'] = get_param('sdkbaseversion');//SDK基础版本 整型
$mydata['vc'] = get_param('versioncode');//游戏版本号 整型
$mydata['vn'] = get_param('versionname');//游戏版本名称 字符串
$mydata['pn'] = get_param('packagename');//游戏包名 字符串
$mydata['title'] = get_param('title');//游戏标题 字符串
$mydata['mac'] = get_param('mac');//设备MAC地址 字符串
$mydata['chl'] = get_param('channel');//渠道号 字符串
//说明
//100001 登陆,
//100002 退出,
//100003 错误日志（游戏崩溃）,
//100004 游戏进行时的参数,
//100005 SDK内游戏展示次数
//100006 SDK内游戏下载次数
//100007 SDK内游戏安装次数
//100011 SDK内游戏取消安装次数
//100012 SDK点游戏安装位置统计
//100020 SDK进入游戏统计
//100021 SDK退出游戏统计
//200001 模拟手柄页面展示
//200002 模拟手柄连接成功
//200003 实体手柄连接
//200004 连接服务启动失败
//200005 模拟手柄断开
//200006 模拟手柄重连
//200008 连接服务关闭
//200009 一般异常统计
//300001 点击录制
//300002 录制时长
//300003 点击分享
//300004 点击保存
//300005 移除悬浮窗
//300006 点击播放
//300007 打开分享页面
//300008 显示分享页面
//310001 点击观看按钮
//310002 点击广告位
//310003 点击专题
//310004 点击播放
//310005 点击了播放全屏
//310006 分享统计（开始分享）
//310007 分享统计（分享成功）
//310008 分享统计（分享失败）
//310009 分享统计（取消分享）
//310010 上传统计（开始上传）
//310011 上传统计（上传成功）
//310012 上传统计（上传失败）
//310013 上传统计（取消上传）
//310014 阿里百川服务启动失败
//310015 合成统计（开始合成）
//310016 合成统计（合成完毕）

$mydata['eid'] = get_param('eventid');//事件ID 字符串
$mydata['ct'] = get_param('time');//客户端记录时间 long型数据，毫秒级
$mydata['ut'] = get_param('usetime');//使用时间，long型数据，毫秒级
//记录时间
$mydata['st'] = microtime_float();//日志记录时间 long型数据，毫秒级
$mydata['ip'] = get_onlineip();//获取客户端的IP
$mydata['imei'] = get_param('imei'); //imei串号，在用户未开启wifi无法获取mac地址时用户识别用户唯一性（最长16位）
$mydata['nte'] = get_param('networkenv'); //网络状态

//如果不是合法的数据，则返回失败标记
if( is_empty($mydata['md']) || is_empty($mydata['bd']) || is_empty($mydata['dc'])){
	echo('{"code":0}');
	exit;
}

//记录日志
if($mydata['eid']==100001 || $mydata['eid']==100002){//SDK的登入/退出

    $mydata['sv'] = get_param('systemversion');//系统版本
    $mydata['gpu'] = get_param('gpu');//gpu

	$tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_login",true,date('Ymd',time()));
}else if($mydata['eid']==100003){//游戏崩溃

    $mydata['msg'] = get_param('message');//错误信息
    $mydata['sv'] = get_param('systemversion');//系统版本
    $mydata['gpu'] = get_param('gpu');//gpu

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_game_crash",true,date('Ymd',time()));
}else if($mydata['eid']==100004){//游戏中时传的
	
	$mydata['fps'] = get_param('fps');//游戏频数
	$mydata['tmem'] = get_param('totalmem');//总内存大小
	$mydata['amem'] = get_param('availmem');//剩余内存大小
	$tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_login_game",true,date('Ymd',time()));
}else if($mydata['eid']==100005){//SDK内游戏展示次数

	$mydata['adappid'] = get_param('adAppid');//推广游戏的gvid
	$mydata['advc'] = get_param('adVersionCode');//推广游戏的版本号
	$mydata['advn'] = get_param('adVersionName');//推广游戏的版本名
	$mydata['adpn'] = get_param('adPakcageName');//推广游戏的包名
	$mydata['adtitle'] = get_param('adTitle');//推广游戏的名称
	
	$tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_game_show",true,date('Ymd',time()));
}else if($mydata['eid']==100006){//SDK内游戏下载次数

	$mydata['url'] = get_param('url');//推广游戏下载地址
	$mydata['adappid'] = get_param('adAppid');//推广游戏的gvid
	$mydata['advc'] = get_param('adVersionCode');//推广游戏的版本号
	$mydata['advn'] = get_param('adVersionName');//推广游戏的版本名
	$mydata['adpn'] = get_param('adPakcageName');//推广游戏的包名
	$mydata['adtitle'] = get_param('adTitle');//推广游戏的名称
	
	$tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_game_down",true,date('Ymd',time()));
}else if($mydata['eid']==100007){//SDK内游戏安装次数
	
	$mydata['adappid'] = get_param('adAppid');//推广游戏的gvid
	$mydata['advc'] = get_param('adVersionCode');//推广游戏的版本号
	$mydata['advn'] = get_param('adVersionName');//推广游戏的版本名
	$mydata['adpn'] = get_param('adPakcageName');//推广游戏的包名
	$mydata['adtitle'] = get_param('adTitle');//推广游戏的名称
	
	$tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_game_insert",true,date('Ymd',time()));
}else if($mydata['eid']==100011){//SDK内游戏取消安装次数

	$mydata['adappid'] = get_param('adAppid');//推广游戏的gvid
	$mydata['advc'] = get_param('adVersionCode');//推广游戏的版本号
	$mydata['advn'] = get_param('adVersionName');//推广游戏的版本名
	$mydata['adpn'] = get_param('adPakcageName');//推广游戏的包名
	$mydata['adtitle'] = get_param('adTitle');//推广游戏的名称
	
	$tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_game_uninstall",true,date('Ymd',time()));
}else if($mydata['eid']==100012){//SDK点游戏安装位置统计

    $mydata['pos'] = get_param('pos');//点安装位置（字符串）

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_game_install_pos",true,date('Ymd',time()));
}else if($mydata['eid']==100020 || $mydata['eid']==100021){//SDK进入游戏、SDK退出游戏

    $mydata['gt'] = get_param('gametime');//游戏时间
    $mydata['sv'] = get_param('systemversion');//系统版本
    $mydata['gpu'] = get_param('gpu');//gpu

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_into_out_game",true,date('Ymd',time()));
}else if($mydata['eid']==200001 || $mydata['eid']==200002 || $mydata['eid']==200005 ||$mydata['eid']==200006){//模拟手柄页面展示、连接成功、断开、重连

    $mydata['nip'] = get_param('ip');//客户端传递ip地址（内网）
    $mydata['port'] = get_param('port');//端口号
    $mydata['wifiname'] = get_param('wifiname');//wifi名称
    $mydata['time'] = get_param('show_time');//页面展示时长

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_sim_handle",true,date('Ymd',time()));
}else if($mydata['eid']==200003){//实体手柄连接

    $mydata['nip'] = get_param('ip');//客户端传递ip地址（内网）
    $mydata['port'] = get_param('port');//端口号
    $mydata['wifiname'] = get_param('wifiname');//wifi名称
    $mydata['type'] = get_param('type');//实体手柄连接位置（0:SDK 1:游戏里）

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_handle_connect",true,date('Ymd',time()));
}else if($mydata['eid']==200004 || $mydata['eid']==200008){//连接服务启动失败跟关闭

    $mydata['nip'] = get_param('ip');//客户端传递ip地址（内网）
    $mydata['port'] = get_param('port');//端口号
    $mydata['wifiname'] = get_param('wifiname');//wifi名称
    $mydata['msg'] = get_param('message');//错误信息

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_connect_server",true,date('Ymd',time()));
}else if($mydata['eid']==200009){//一般异常统计

    $mydata['nip'] = get_param('ip');//客户端传递ip地址（内网）
    $mydata['port'] = get_param('port');//端口号
    $mydata['wifiname'] = get_param('wifiname');//wifi名称
    $mydata['msg'] = get_param('message');//错误信息

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_server_exception",true,date('Ymd',time()));
}else if($mydata['eid']==300001 || $mydata['eid']==300002 || $mydata['eid']==300006 || 
         $mydata['eid']==300003 || $mydata['eid']==300004 || $mydata['eid']==300005 ||
         $mydata['eid']==300007 || $mydata['eid']==300008){ //视频点击录制、录制时长、点击播放、视频点击分享、点击保存、移除悬浮窗、打开分享页面、显示分享页面

    $mydata['lengthoftime'] = get_param('lengthoftime');  //录制时长
    $mydata['vt'] = get_param('videotitle');  //视频标题
    $mydata['nt'] = get_param('network'); //网络环境（3G、wifi）

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_video_transcribe",true,date('Ymd',time()));
}else if($mydata['eid']==310001 || $mydata['eid']==310005){ //视频点击观看按钮、视频点击播放全屏

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_video_play_click",true,date('Ymd',time()));
}else if($mydata['eid']==310002){ //视频点击广告位

    $mydata['flag'] = get_param('flag');  //0代表未安装（将会是安装动作），1代表已安装（将会是打开app）
    $mydata['adid'] = get_param('adid');  //广告位id

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_video_ad_click",true,date('Ymd',time()));
}else if($mydata['eid']==310003){ //点击专题

    $mydata['topicid'] = get_param('topicid');  //专题id
    $mydata['topicname'] = get_param('topicname');  //专题名称

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_video_topic_click",true,date('Ymd',time()));
}else if($mydata['eid']==310004){ //点击播放

    $mydata['videoid'] = get_param('videoid');  //视频id
    $mydata['videoname'] = get_param('videoname');  //视频名称
    $mydata['sourceid'] = get_param('sourceid');  //-1表示来自热门推荐，-2表示来自相关视频，1表示来自视频解说，2表示来自视频专辑

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_video_click_play",true,date('Ymd',time()));
}else if($mydata['eid']==310006 || $mydata['eid']==310007 || $mydata['eid']==310008 ||
         $mydata['eid']==310009){ //分享统计（310006：开始分享 310007：分享成功 310008：分享失败 310009：取消分享）

    $mydata['stype'] = get_param('sharetype');  //分享类型，字符串，0：微博，1：QQ，2：QZONE，3：优酷，4：微信、5：更多
    $mydata['fs'] = get_param('filesize');  //文件大小，长整型
    $mydata['url'] = get_param('url');  //视频资源url，经过base64编码

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_video_share",true,date('Ymd',time()));
}else if($mydata['eid']==310010 || $mydata['eid']==310011 || $mydata['eid']==310012 ||
         $mydata['eid']==310013){ //上传统计（310010：开始上传 310011：上传成功 310012：上传失败 310013：取消上传）

    $mydata['utype'] = get_param('uploadtype');  //上传类型，整型，0为阿里百川，1为优酷
    $mydata['fs'] = get_param('filesize');  //文件大小，长整型
    $mydata['pt'] = get_param('playtitle'); //视频标题，字符串
    $mydata['pu'] = get_param('playurl'); //视频url
    $mydata['fm'] = get_param('filemd5'); //文件MD5
    $mydata['tags'] = get_param('tags'); //标签分类id字符串
    $mydata['uid'] = intval(get_param('uid')); //用户id

    if($mydata['eid'] == 310011){
        $ext = pathinfo($mydata['pu'], PATHINFO_EXTENSION);
        if($ext == 'mp4'){
            $tmp_str = json_encode($mydata).chr(13).chr(10);
            write_file_random_sdk($tmp_str,"sdk_video_upload",true,date('Ymd',time()));
        }
    }else{
        $tmp_str = json_encode($mydata).chr(13).chr(10);
        write_file_random_sdk($tmp_str,"sdk_video_upload",true,date('Ymd',time()));
    }
}else if($mydata['eid']==310014){ //阿里百川服务启动失败

    $mydata['reason'] = get_param('reason');  //失败原因

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_al_service",true,date('Ymd',time()));
}else if($mydata['eid']==310015 || $mydata['eid']==310016){ //合成统计（310015 开始合成 310016 合成完毕）

    $mydata['rt'] = intval(get_param('result'));  //合成结果，0为失败，1为成功，2为取消，int型

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_video_syn",true,date('Ymd',time()));
}else{//出错时传的

	$mydata['msg'] = get_param('message');//错误信息
	$tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random_sdk($tmp_str,"sdk_error",true,date('Ymd',time()));
}
unset($tmp_str,$mydata);
//返回记录成功的标记
echo('{"code":1}');




