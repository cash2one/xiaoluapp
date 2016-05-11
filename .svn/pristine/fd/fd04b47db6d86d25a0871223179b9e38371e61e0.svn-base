<?php
/**
 * @copyright: @快游戏 2015
* @description: 获取日志记录
* @file: new_sdk_login_count.php
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
//200001 模拟手柄页面展示
//200002 模拟手柄连接成功
//200005 模拟手柄断开
//200006 模拟手柄重连
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

/*参数*/
$data = get_param('datas');
$data_arr = json_decode(stripslashes($data),true);
//var_dump($data_arr);die;
if(isset($data_arr['rows']) && !empty($data_arr['rows'])){

    //key生成
    $key = md5(json_encode($data_arr['rows']).'api.kuaiyouxi.com@youxikyxlaile');

    if(isset($data_arr['key']) && ($data_arr['key'] == $key)){
        foreach($data_arr['rows'] as $val){
            $mydata['md'] = isset($val['model']) ? $val['model'] : '';//型号 字符串
            $mydata['bd'] = isset($val['brand']) ? $val['brand'] : '';//厂商 字符串
            $mydata['dc'] = isset($val['device']) ? $val['device'] : '';//手柄型号字符串
            $mydata['sdkv'] = isset($val['sdkversion']) ? $val['sdkversion'] : 0;//SDK版本 整型
            $mydata['sdkbv'] = isset($val['sdkbaseversion']) ? $val['sdkbaseversion'] : 0;//SDK基础版本 整型
            $mydata['vc'] = isset($val['versioncode']) ? $val['versioncode'] : 0;//游戏版本号 整型
            $mydata['vn'] = isset($val['versionname']) ? $val['versionname'] : '';//游戏版本名称 字符串
            $mydata['pn'] = isset($val['packagename']) ? $val['packagename'] : '';//游戏包名 字符串
            $mydata['title'] = isset($val['title']) ? $val['title'] : '';//游戏标题 字符串
            $mydata['mac'] = isset($val['mac']) ? $val['mac'] : '';//设备MAC地址 字符串
            $mydata['chl'] = isset($val['channel']) ? $val['channel'] : '';//渠道号 字符串
            $mydata['eid'] = isset($val['eventid']) ? $val['eventid'] : 0;//事件ID 字符串
            $mydata['ct'] = isset($val['time']) ? $val['time'] : 0;//客户端记录时间 long型数据，毫秒级
            $mydata['ut'] = isset($val['usetime']) ? $val['usetime'] : 0;//使用时间，long型数据，毫秒级
            $mydata['st'] = microtime_float();//日志记录时间 long型数据，毫秒级
            $mydata['ip'] = get_onlineip();//获取客户端的IP
            $mydata['imei'] = isset($val['imei']) ? $val['imei'] : ''; //imei串号，在用户未开启wifi无法获取mac地址时用户识别用户唯一性（最长16位）
            $mydata['nte'] = isset($val['networkenv']) ? $val['networkenv'] : ''; //网络状态
            $mydata['sv'] = isset($val['systemversion']) ? $val['systemversion'] : '';//系统版本
            $mydata['gpu'] = isset($val['gpu']) ? $val['gpu'] : '';//gpu

            //如果是合法的数据，才进行入库操作
            if( !empty($mydata['md']) && !empty($mydata['bd'])){
                //记录日志
                if($mydata['eid']==100001 || $mydata['eid']==100002){//SDK的登入/退出

                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_login",true,date('Ymd',time()));
                }else if($mydata['eid']==100003){//游戏崩溃

                    $mydata['msg'] = isset($val['message']) ? $val['message'] : '';//错误信息

                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_game_crash",true,date('Ymd',time()));
                }else if($mydata['eid']==100004){//游戏中时传的

                    $mydata['fps'] = isset($val['fps']) ? $val['fps'] : '';//游戏频数
                    $mydata['tmem'] = isset($val['totalmem']) ? $val['totalmem'] : '';//总内存大小
                    $mydata['amem'] = isset($val['availmem']) ? $val['availmem'] : '';//剩余内存大小
                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_login_game",true,date('Ymd',time()));
                }else if($mydata['eid']==100005){//SDK内游戏展示次数

                    $mydata['adappid'] = isset($val['adAppid']) ? $val['adAppid'] : '';//推广游戏的gvid
                    $mydata['advc'] = isset($val['adVersionCode']) ? $val['adVersionCode'] : '';//推广游戏的版本号
                    $mydata['advn'] = isset($val['adVersionName']) ? $val['adVersionName'] : '';//推广游戏的版本名
                    $mydata['adpn'] = isset($val['adPakcageName']) ? $val['adPakcageName'] : '';//推广游戏的包名
                    $mydata['adtitle'] = isset($val['adTitle']) ? $val['adTitle'] : '';//推广游戏的名称

                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_game_show",true,date('Ymd',time()));
                }else if($mydata['eid']==100006){//SDK内游戏下载次数

                    $mydata['url'] = isset($val['url']) ? $val['url'] : '';//推广游戏下载地址
                    $mydata['adappid'] = isset($val['adAppid']) ? $val['adAppid'] : '';//推广游戏的gvid
                    $mydata['advc'] = isset($val['adVersionCode']) ? $val['adVersionCode'] : '';//推广游戏的版本号
                    $mydata['advn'] = isset($val['adVersionName']) ? $val['adVersionName'] : '';//推广游戏的版本名
                    $mydata['adpn'] = isset($val['adPakcageName']) ? $val['adPakcageName'] : '';//推广游戏的包名
                    $mydata['adtitle'] = isset($val['adTitle']) ? $val['adTitle'] : '';//推广游戏的名称

                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_game_down",true,date('Ymd',time()));
                }else if($mydata['eid']==100007){//SDK内游戏安装次数

                    $mydata['adappid'] = isset($val['adAppid']) ? $val['adAppid'] : '';//推广游戏的gvid
                    $mydata['advc'] = isset($val['adVersionCode']) ? $val['adVersionCode'] : '';//推广游戏的版本号
                    $mydata['advn'] = isset($val['adVersionName']) ? $val['adVersionName'] : '';//推广游戏的版本名
                    $mydata['adpn'] = isset($val['adPakcageName']) ? $val['adPakcageName'] : '';//推广游戏的包名
                    $mydata['adtitle'] = isset($val['adTitle']) ? $val['adTitle'] : '';//推广游戏的名称

                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_game_insert",true,date('Ymd',time()));
                }else if($mydata['eid']==100011){//SDK内游戏取消安装次数

                    $mydata['adappid'] = isset($val['adAppid']) ? $val['adAppid'] : '';//推广游戏的gvid
                    $mydata['advc'] = isset($val['adVersionCode']) ? $val['adVersionCode'] : '';//推广游戏的版本号
                    $mydata['advn'] = isset($val['adVersionName']) ? $val['adVersionName'] : '';//推广游戏的版本名
                    $mydata['adpn'] = isset($val['adPakcageName']) ? $val['adPakcageName'] : '';//推广游戏的包名
                    $mydata['adtitle'] = isset($val['adTitle']) ? $val['adTitle'] : '';//推广游戏的名称

                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_game_uninstall",true,date('Ymd',time()));
                }else if($mydata['eid']==100012){//SDK点游戏安装位置统计

                    $mydata['pos'] = isset($val['pos']) ? $val['pos'] : '';//点安装位置（字符串）

                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_game_install_pos",true,date('Ymd',time()));
                }else if($mydata['eid']==200001 || $mydata['eid']==200002 || $mydata['eid']==200005 ||$mydata['eid']==200006){//模拟手柄页面展示、连接成功、断开、重连

                    $mydata['nip'] = isset($val['ip']) ? $val['ip'] : '';//客户端传递ip地址（内网）
                    $mydata['port'] = isset($val['port']) ? $val['port'] : '';//端口号
                    $mydata['wifiname'] = isset($val['wifiname']) ? $val['wifiname'] : '';//wifi名称
                    $mydata['time'] = isset($val['show_time']) ? $val['show_time'] : '';//页面展示时长

                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_sim_handle",true,date('Ymd',time()));
                }else if($mydata['eid']==300001 || $mydata['eid']==300002 || $mydata['eid']==300006 ||
                    $mydata['eid']==300003 || $mydata['eid']==300004 || $mydata['eid']==300005 ||
                    $mydata['eid']==300007 || $mydata['eid']==300008){ //视频点击录制、录制时长、点击播放、视频点击分享、点击保存、移除悬浮窗、打开分享页面、显示分享页面

                    $mydata['lengthoftime'] = isset($val['lengthoftime']) ? $val['lengthoftime'] : '';  //录制时长
                    $mydata['vt'] = isset($val['videotitle']) ? $val['videotitle'] : '';  //视频标题

                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_video_transcribe",true,date('Ymd',time()));
                }else if($mydata['eid']==310001 || $mydata['eid']==310005){ //视频点击观看按钮、视频点击播放全屏

                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_video_play_click",true,date('Ymd',time()));
                }else if($mydata['eid']==310002){ //视频点击广告位

                    $mydata['flag'] = isset($val['flag']) ? $val['flag'] : '';  //0代表未安装（将会是安装动作），1代表已安装（将会是打开app）
                    $mydata['adid'] = isset($val['adid']) ? $val['adid'] : '';  //广告位id

                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_video_ad_click",true,date('Ymd',time()));
                }else if($mydata['eid']==310003){ //点击专题

                    $mydata['topicid'] = isset($val['topicid']) ? $val['topicid'] : '';  //专题id
                    $mydata['topicname'] = isset($val['topicname']) ? $val['topicname'] : '';  //专题名称

                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_video_topic_click",true,date('Ymd',time()));
                }else if($mydata['eid']==310004){ //点击播放

                    $mydata['videoid'] = isset($val['videoid']) ? $val['videoid'] : '';  //视频id
                    $mydata['videoname'] = isset($val['videoname']) ? $val['videoname'] : '';  //视频名称
                    $mydata['sourceid'] = isset($val['sourceid']) ? $val['sourceid'] : '';  //-1表示来自热门推荐，-2表示来自相关视频，1表示来自视频解说，2表示来自视频专辑

                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_video_click_play",true,date('Ymd',time()));
                }else if($mydata['eid']==310006 || $mydata['eid']==310007 || $mydata['eid']==310008 ||
                    $mydata['eid']==310009){ //分享统计（310006：开始分享 310007：分享成功 310008：分享失败 310009：取消分享）

                    $mydata['stype'] = isset($val['sharetype']) ? $val['sharetype'] : '';  //分享类型，字符串，0：微博，1：QQ，2：QZONE，3：优酷，4：微信、5：更多
                    $mydata['fs'] = isset($val['filesize']) ? $val['filesize'] : '';  //文件大小，长整型
                    $mydata['url'] = isset($val['url']) ? $val['url'] : '';  //视频资源url，经过base64编码

                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_video_share",true,date('Ymd',time()));
                }else if($mydata['eid']==310010 || $mydata['eid']==310011 || $mydata['eid']==310012 ||
                    $mydata['eid']==310013){ //上传统计（310010：开始上传 310011：上传成功 310012：上传失败 310013：取消上传）

                    $mydata['utype'] = isset($val['uploadtype']) ? $val['uploadtype'] : '';  //上传类型，整型，0为阿里百川，1为优酷
                    $mydata['fs'] = isset($val['filesize']) ? $val['filesize'] : '';  //文件大小，长整型
                    $mydata['pt'] = isset($val['playtitle']) ? $val['playtitle'] : ''; //视频标题，字符串
                    $mydata['pu'] = isset($val['playurl']) ? $val['playurl'] : ''; //视频url
                    $mydata['fm'] = isset($val['filemd5']) ? $val['filemd5'] : ''; //文件MD5
                    $mydata['tags'] = isset($val['tags']) ? $val['tags'] : ''; //标签分类id字符串
                    $mydata['uid'] = isset($val['uid']) ? intval($val['uid']) : 0; //用户id

                    $ext = pathinfo($mydata['pu'], PATHINFO_EXTENSION);
                    if($mydata['eid']==310011){
                        if($ext == 'mp4'){
                            $tmp_str = json_encode($mydata).chr(13).chr(10);
                            write_file_random($tmp_str,"sdk_video_upload",true,date('Ymd',time()));
                        }
                    }else{
                        $tmp_str = json_encode($mydata).chr(13).chr(10);
                        write_file_random($tmp_str,"sdk_video_upload",true,date('Ymd',time()));
                    }
                }else if($mydata['eid']==310014){ //阿里百川服务启动失败

                    $mydata['reason'] = isset($val['reason']) ? $val['reason'] : '';  //失败原因

                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_al_service",true,date('Ymd',time()));
                }else if($mydata['eid']==310015 || $mydata['eid']==310016){ //合成统计（310015 开始合成 310016 合成完毕）

                    $mydata['rt'] = isset($val['result']) ? intval($val['result']) : 0;  //合成结果，0为失败，1为成功，2为取消，int型

                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_video_syn",true,date('Ymd',time()));
                }else{//出错时传的

                    $mydata['msg'] = isset($val['message']) ? $val['message'] : '';//错误信息
                    $tmp_str = json_encode($mydata).chr(13).chr(10);
                    write_file_random($tmp_str,"sdk_error",true,date('Ymd',time()));
                }
                unset($tmp_str,$mydata);
            }
        }

        //返回记录成功的标记
        exit('{"code":1}');
    }
}
exit('{"code":0}');







