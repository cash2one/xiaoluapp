<?php
/**
 * @copyright: @快游戏 2015
 * @description: 游戏视频APP记录
 * @file: video_app_count.php
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
$mydata['pn'] = get_param('packagename');//游戏包名 字符串
$mydata['title'] = get_param('title');//APP标题或游戏标题 字符串
$mydata['md'] = get_param('model');//型号 字符串
$mydata['bd'] = get_param('brand');//品牌 字符串
$mydata['sdkbv'] = get_param('systemversion');//系统版本号
$mydata['vc'] = get_param('versioncode');//游戏版本号 整型
$mydata['vn'] = get_param('versionname');//游戏版本名称 字符串
$mydata['mac'] = get_param('mac');//设备MAC地址 字符串
$mydata['chl'] = get_param('channel');//渠道号 字符串
$mydata['eid'] = get_param('eventid');//事件ID 字符串
$mydata['imei'] = get_param('imei'); //imei串号，在用户未开启wifi无法获取mac地址时用户识别用户唯一性（最长16位） 字符串
$mydata['ct'] = get_param('time');//客户端记录时间 long型数据，毫秒级
$mydata['st'] = microtime_float();//日志记录时间 long型数据，毫秒级
$mydata['ip'] = get_onlineip();//获取客户端的IP 字符串
$mydata['in_date'] = date('Ymd');//日期
$mydata['intime'] = time();//unix时间戳

//说明

//如果不是合法的数据，则返回失败标记
if( is_empty($mydata['md'])){
    echo('{"code":0}');
    exit;
}

//记录日志
if($mydata['eid']==600000 ){ //版块点击统计 

    $mydata['module'] = trim(get_param('module')); 

    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"video_app_section",true,date('Ymd',time()));
}elseif($mydata['eid']==600001 ){//首页点击统计 

    $mydata['videoid'] = trim(get_param('videoid')); //视频ID
    $mydata['videotitle'] = trim(get_param('videotitle')); //视频标题
    $mydata['videourl'] = trim(get_param('videourl')); //视频播放URL
    $mydata['module'] = trim(get_param('module')); //banner，最近更新，推荐热门
    $mydata['clickpos'] = trim(get_param('clickpos'));//点击位置，-1为更多
    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"video_app_home",true,date('Ymd',time()));
}elseif($mydata['eid']==600002 ){//视频作者点击统计

    $mydata['authorid'] = intval(get_param('authorid')); //作者ID
    $mydata['author'] = trim(get_param('author'));//作者名称
    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"video_app_author",true,date('Ymd',time()));
}elseif($mydata['eid']==600003 ){//专辑点击

    $mydata['topicid'] = intval(get_param('topicid')); //专辑ID
    $mydata['topictitle'] = trim(get_param('topictitle'));
    $mydata['authorid'] = trim(get_param('authorid'));
    $mydata['author'] = trim(get_param('author'));
    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"video_app_album",true,date('Ymd',time()));
}elseif($mydata['eid']==600004 ){//无法播放统计
    $mydata['videoid'] = intval(get_param('videoid')); //专辑ID
    $mydata['videotitle'] = trim(get_param('videotitle'));
    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"video_app_play",true,date('Ymd',time()));
}elseif($mydata['eid']==600005 ){//排行点击统计
    $mydata['videoid'] = intval(get_param('videoid')); //视频ID
    $mydata['videotitle'] = trim(get_param('videotitle'));
    $mydata['videourl'] = trim(get_param('videourl'));
    $mydata['clickpos'] = trim(get_param('clickpos'));
    $mydata['ranktype'] = trim(get_param('ranktype'));
    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"video_app_rank",true,date('Ymd',time()));
}elseif($mydata['eid']==600006){//相关推荐点击统计
    $mydata['videoid'] = intval(get_param('videoid')); //视频ID
    $mydata['videotitle'] = trim(get_param('videotitle'));
    $mydata['videourl'] = trim(get_param('videourl'));
    $mydata['clickpos'] = trim(get_param('clickpos'));
    $mydata['relatedvideoid'] = trim(get_param('relatedvideoid'));
    $mydata['relatedvideotitle'] = trim(get_param('relatedvideotitle'));
    $mydata['relatedvideourl'] = trim(get_param('relatedvideourl'));
    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"video_app_related",true,date('Ymd',time()));
}elseif($mydata['eid']== 600007 ){//设置点击

    $mydata['type'] = intval(get_param('type')); //0:点击进入设置 1:意见反馈 2: 更新 3：点击加群 4：点击微信 5：网络 ：2G/3G/4G网络
    $mydata['state'] = intval(get_param('state')); //1:开 2：关 0：默认（不是点击开关区域）
    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"video_app_set",true,date('Ymd',time()));
}elseif($mydata['eid']== 600008 ){//包名记录

    $mydata['apps'] = get_param('apps'); //base64包名加密字符串
    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"video_app_package",true,date('Ymd',time()));
}else{ //出错时传的
    $mydata['msg'] = get_param('message');//错误信息
    $tmp_str = json_encode($mydata).chr(13).chr(10);
    write_file_random($tmp_str,"video_app_error",true,date('Ymd',time()));
}

//释放变量
unset($tmp_str,$mydata);

//返回记录成功的标记
echo('{"code":1}');




