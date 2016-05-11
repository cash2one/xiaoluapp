<?php
/*=============================================================================
#     FileName: update_app_game_connect_date.php
#         Desc: 定期统计模拟手柄连接游戏信息（统计表kyx_app_game_connect_log里的数据,存到kyx_app_game_connect_time表里
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-07-08 17:47:48
#      History:
=============================================================================*/
include_once("../config.inc.php");

$tmp_ip = get_onlineip();//获取客户端的IP
sys_log_write_content('开始执行计划任务,IP：'.$tmp_ip,'game_connect');

if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！");
	exit;
}

sys_log_write_content('开始加载redis','game_connect');
include_once(WEBPATH_DIR."include".DS."redis.class.php");//redis处理类
sys_log_write_content('加载redis类成功','game_connect');
include_once(WEBPATH_DIR."redis.config.inc.php"); //redis连接
sys_log_write_content('redis初始化成功','game_connect');
include_once(WEBPATH_DIR."db.save.config.inc.php");
sys_log_write_content('加载save.config类成功','game_connect');

//获取上一天的日期
$this_date = date('Ymd',THIS_DATETIME - 86400);

//获取昨天所有用户信息
$user_sql = "SELECT `agc_mac` FROM `kyx_app_game_connect_log` WHERE `agc_in_date` = ".$this_date." GROUP BY `agc_mac`";
sys_log_write_content($this_date.'-所有用户信息-'.$user_sql,'game_connect');
$user_data = $conn->find($user_sql);
if(!empty($user_data)){
    $redis->select(3);//选择redis的第四个数据库来存放
    foreach($user_data as $uval){

        //检查是否有记录这个用户
        $redis_key = md5('connuser|'.$uval['agc_mac']);
        $redis_val = md5($uval['agc_mac']);
        $redis_ok = $redis->get($redis_key);

        //如果没有找到，则插入
        if(!$redis_ok && !empty($uval['agc_mac'])){

            $check_sql = "SELECT COUNT(1) as num FROM `kyx_app_game_connect_user` WHERE `agc_mac` = '".$uval['agc_mac']."'";
            sys_log_write_content($this_date.'-检查用户是否存在-'.$check_sql,'game_connect');
            $check_data = $conn->find($check_sql);

            if(isset($check_data[0]['num']) && empty($check_data[0]['num'])){
                //把数据插入渠道信息表
                $arr = array(
                    'agc_in_date'=>$this_date, //记录日期,
                    'agc_mac'=>$uval['agc_mac'] //MAC地址,
                );
                $conn->save('kyx_app_game_connect_user', $arr);

                //插入redis
                $redis->set($redis_key,$redis_val);
            }else{
                //插入redis
                $redis->set($redis_key,$redis_val);
            }
        }
    }
}

$sql = "SELECT count(if(`agc_eid`=300003,true,null)) as snum,count(DISTINCT if(`agc_eid`=300003,`agc_mac`,null)) as sinum,
        count(if(`agc_eid`=300004,true,null)) as fnum,count(DISTINCT if(`agc_eid`=300004,`agc_mac`,null)) as finum,
        count(if(`agc_eid`=30009,true,null)) as cnum,count(DISTINCT if(`agc_eid`=30009,`agc_mac`,null)) as cinum,`agc_gchl`,
        count(DISTINCT `agc_mac`) as cgv_num,`agc_gt`,`agc_pn`,`agc_gvc`
        FROM `kyx_app_game_connect_log` WHERE `agc_in_date` = ".$this_date." GROUP BY `agc_gchl`,`agc_pn`,`agc_gvc`";
sys_log_write_content($this_date.'-统计数据-'.$sql,'game_connect');
$data = $conn->find($sql);

//获取当天的独立用户总数
$mac_sql = "SELECT COUNT(DISTINCT `agc_mac`) AS num FROM `kyx_app_game_connect_log` WHERE `agc_in_date`=".$this_date;
sys_log_write_content($this_date.'-当天独立用户数据-'.$sql,'game_connect');
$mac_data = $conn->find($mac_sql);

if(!empty($data)){
    foreach ($data as $val){
        $row = array(
            "in_date" => $this_date, //下载日期
            "game_title" => $val['agc_gt'], //游戏名称
            "game_pn" => $val['agc_pn'], //游戏包名
            "game_vc" => $val['agc_gvc'], //游戏版本号
            "chl_name" => $val['agc_gchl'], //渠道名称
            "succ_num" => intval($val['snum']), //连接游戏成功次数
            "mac_succ_num" => intval($val['sinum']), //独立连接游戏成功次数
            "fail_num" => intval($val['fnum']), //连接游戏失败次数
            "mac_fail_num" => intval($val['finum']), //独立连接游戏失败次数
            "off_num" => intval($val['cnum']), //断开游戏次数
            "mac_off_num" => intval($val['cinum']), //独立断开游戏次数
            "mac_chl_game_vc" => intval($val['cgv_num']), //当天渠道游戏版本独立MAC用户
            "user_num" => isset($mac_data[0]['num']) ? intval($mac_data[0]['num']) : 0 //当天独立MAC用户数
        );
        $conn->save('kyx_app_game_connect_time', $row);
    }
    echo("成功更新模拟手柄连接游戏数据");
}else{
    echo("模拟手柄连接游戏数据为空");
}

