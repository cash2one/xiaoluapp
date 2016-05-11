<?php
/*=============================================================================
#     FileName: update_sdk_video_transcribe_date.php
#         Desc: 定期统计sdk视频录制信息（统计表kyx_sdk_video_transcribe_log里的数据,存到kyx_sdk_game_install_pos_time表里
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-06-01 11:45:48
#      History:
=============================================================================*/
include_once("../config.inc.php");

$tmp_ip = get_onlineip();//获取客户端的IP
sys_log_write_content('开始执行计划任务,IP：'.$tmp_ip,'sdk_video_transcribe');

if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！");
	exit;
}

sys_log_write_content('开始加载redis','sdk_video_transcribe');
include_once(WEBPATH_DIR."include".DS."redis.class.php");//redis处理类
sys_log_write_content('加载redis类成功','sdk_video_transcribe');
include_once(WEBPATH_DIR."redis.config.inc.php"); //redis连接
sys_log_write_content('redis初始化成功','sdk_video_transcribe');
include_once(WEBPATH_DIR."db.save.config.inc.php");
sys_log_write_content('加载save.config类成功','sdk_video_transcribe');

//获取上一天的日期
$this_date = isset($_SERVER['argv'][1]) ? intval($_SERVER['argv'][1]) : date('Ymd',THIS_DATETIME - 86400);

$sql = "SELECT count(if(`svtl_eid`=300001,true,null)) as ctnum,count(DISTINCT if(`svtl_eid`=300001,CONCAT(`svtl_mac`,`svtl_imei`),null)) as ctinum,
        count(if(`svtl_eid`=300006,true,null)) as cpnum,count(DISTINCT if(`svtl_eid`=300006,CONCAT(`svtl_mac`,`svtl_imei`),null)) as cpinum,
        count(if(`svtl_eid`=300003,true,null)) as csnum,count(DISTINCT if(`svtl_eid`=300003,CONCAT(`svtl_mac`,`svtl_imei`),null)) as csinum,
        count(if(`svtl_eid`=300004,true,null)) as savenum,count(DISTINCT if(`svtl_eid`=300004,CONCAT(`svtl_mac`,`svtl_imei`),null)) as saveinum,
        count(if(`svtl_eid`=300005,true,null)) as renum,count(DISTINCT if(`svtl_eid`=300005,CONCAT(`svtl_mac`,`svtl_imei`),null)) as reinum,
        count(if(`svtl_eid`=300007,true,null)) as osnum,count(DISTINCT if(`svtl_eid`=300007,CONCAT(`svtl_mac`,`svtl_imei`),null)) as osinum,
        count(if(`svtl_eid`=300008,true,null)) as ssnum,count(DISTINCT if(`svtl_eid`=300008,CONCAT(`svtl_mac`,`svtl_imei`),null)) as ssinum,
        MAX(`svtl_lt`) as max_time,AVG(if(`svtl_eid`=300002,`svtl_lt`,null)) as avg_time,`svtl_title`,`svtl_pn`,`svtl_vc`,`svtl_chl`,
        COUNT(if(`svtl_eid`=300002,CONCAT(`svtl_mac`,`svtl_imei`),null)) AS has_num,COUNT(DISTINCT if(`svtl_eid`=300002,CONCAT(`svtl_mac`,`svtl_imei`),null)) AS has_mac_num
        FROM `kyx_sdk_video_transcribe_log` WHERE `svtl_in_date` = ".$this_date." GROUP BY `svtl_chl`,`svtl_pn`,`svtl_vc`";
sys_log_write_content($this_date.'-统计信息-'.$sql,'sdk_video_transcribe');
$data = $conn->find($sql);

if(!empty($data)){
    sys_log_write_content($this_date.'-有查询到统计信息','sdk_video_transcribe');
    $redis->select(2);//选择redis的第三个数据库来存放
    foreach ($data as $val){

        //检查是否有记录这个渠道
        $redis_key = md5('kyxchl|'.$val['svtl_chl']);
        $redis_ok = $redis->get($redis_key);
        //如果没有找到，则插入
        if(!$redis_ok){
            //把数据插入渠道信息表
            $arr = array(
                'c_in_date'=>$this_date,//'记录日期',
                'c_chl'=>$val['svtl_chl'],//'渠道ID',
                'c_name'=>$val['svtl_chl'],//'渠道名称(需要在后台填写)',
                'c_order'=>0//'排序号',
            );
            $chl_id = $conn->save('kyx_channel_info', $arr);
            //插入redis
            $redis->set($redis_key,$chl_id);
        }else{//如果有找到，则读取ID
            $chl_id = $redis_ok;
        }

        //转义字符
        $val['svtl_title'] = mysql_real_escape_string($val['svtl_title']);

        //检查是否有记录这个游戏
        $redis_key = md5('kyxgame|'.$val['svtl_pn']);
        $redis_ok = $redis->get($redis_key);
        //如果没有找到，则插入
        if(!$redis_ok){
            //把数据插入游戏信息表
            $arr = array(
                'g_in_date'=>$this_date,//'记录日期',
                'g_pn'=>$val['svtl_pn'],//'游戏包名',
                'g_name'=>$val['svtl_title'],//'游戏名称',
                'g_order'=>0//'排序号',
            );
            $game_id = $conn->save('kyx_game_info', $arr);
            //插入redis
            $redis->set($redis_key,$game_id);
        }else{//如果有找到，则读取ID
            $game_id = $redis_ok;
        }

        $row = array(
            "in_date" => $this_date, //录制日期
            "game_id" => intval($game_id), //游戏id
            "chl_id" => intval($chl_id), //渠道id
            "ct_num" => intval($val['ctnum']), //点击录制次数
            "mac_ct_num" => intval($val['ctinum']), //独立点击录制次数
            "cp_num" => intval($val['cpnum']), //点击播放次数
            "mac_cp_num" => intval($val['cpinum']), //独立点击播放次数
            "os_num" => intval($val['osnum']), //打开分享页次数
            "mac_os_num" => intval($val['osinum']), //独立打开分享页次数
            "ss_num" => intval($val['ssnum']), //显示分享页次数
            "mac_ss_num" => intval($val['ssinum']), //独立显示分享页次数
            "cs_num" => intval($val['csnum']), //点击分享次数
            "mac_cs_num" => intval($val['csinum']), //独立点击分享次数
            "save_num" => intval($val['savenum']), //点击保存次数
            "mac_save_num" => intval($val['saveinum']), //独立点击保存次数
            "remove_num" => intval($val['renum']), //移除悬浮框次数
            "mac_remove_num" => intval($val['reinum']), //独立移除悬浮框次数
            "has_user_num" => intval($val['has_num']), //有录制视频的用户数
            "has_mac_user_num" => intval($val['has_mac_num']), //有录制视频的独立用户数
            "max_time" => intval($val['max_time']), //录制最大时长
            "avg_time" => intval($val['avg_time']) //录制平均时长（只包含300002录制时长时间平均时长）
        );
        $conn->save('kyx_sdk_video_transcribe_time', $row);
    }
    sys_log_write_content($this_date.'-统计信息完成','sdk_video_transcribe');
    echo("成功更新SDK视频录制数据");
}else{
    sys_log_write_content($this_date.'-没查询到统计信息','sdk_video_transcribe');
    echo("SDK视频录制数据为空");
}
