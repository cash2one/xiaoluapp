<?php
/*=============================================================================
#     FileName: update_sdk_handle_connect_date.php
#         Desc: 定期统计SDK模拟手柄统计信息（统计表kyx_sdk_handle_connect_log里的数据,存到kyx_sdk_handle_connect_time表里
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-07-22 16:28:48
#      History:
=============================================================================*/
include_once("../config.inc.php");

$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！");
	exit;
}

include_once(WEBPATH_DIR."include".DS."redis.class.php");//redis处理类
include_once(WEBPATH_DIR."redis.config.inc.php"); //redis连接
include_once(WEBPATH_DIR."db.save.config.inc.php");

//获取上一天的日期
$this_date = date('Ymd',THIS_DATETIME - 86400);

$sql = "SELECT count(if(`shcl_type`=0,true,null)) as sdknum,count(DISTINCT if(`shcl_type`=0,CONCAT(`shcl_mac`,`shcl_imei`),null)) as sdkinum,
        count(if(`shcl_type`=1,true,null)) as gamenum,count(DISTINCT if(`shcl_type`=1,CONCAT(`shcl_mac`,`shcl_imei`),null)) as gameinum,
        count(DISTINCT `shcl_mac`,`shcl_imei`) as cgv_num,`shcl_chl`,`shcl_title`,`shcl_pn`,`shcl_vc`
        FROM `kyx_sdk_handle_connect_log` WHERE `shcl_in_date` = ".$this_date." GROUP BY `shcl_chl`,`shcl_pn`";
$data = $conn->find($sql);

//获取当天的独立用户总数
$mac_sql = "SELECT COUNT(DISTINCT `shcl_mac`,`shcl_imei`) AS num FROM `kyx_sdk_handle_connect_log` WHERE `shcl_in_date`=".$this_date;
$mac_data = $conn->find($mac_sql);

if(!empty($data)){
    $redis->select(2);//选择redis的第三个数据库来存放
    foreach ($data as $val){

        //检查是否有记录这个渠道
        $redis_key = md5('kyxchl|'.$val['shcl_chl']);
        $redis_ok = $redis->get($redis_key);
        //如果没有找到，则插入
        if(!$redis_ok){
            //把数据插入渠道信息表
            $arr = array(
                'c_in_date'=>$this_date,//'记录日期',
                'c_chl'=>$val['shcl_chl'],//'渠道ID',
                'c_name'=>$val['shcl_chl'],//'渠道名称(需要在后台填写)',
                'c_order'=>0//'排序号',
            );
            $chl_id = $conn->save('kyx_channel_info', $arr);
            //插入redis
            $redis->set($redis_key,$chl_id);
        }else{//如果有找到，则读取ID
            $chl_id = $redis_ok;
        }

        //转义字符
        $val['shcl_title'] = mysql_real_escape_string($val['shcl_title']);

        //检查是否有记录这个游戏
        $redis_key = md5('kyxgame|'.$val['shcl_pn']);
        $redis_ok = $redis->get($redis_key);
        //如果没有找到，则插入
        if(!$redis_ok){
            //把数据插入游戏信息表
            $arr = array(
                'g_in_date'=>$this_date,//'记录日期',
                'g_pn'=>$val['shcl_pn'],//'游戏包名',
                'g_name'=>$val['shcl_title'],//'游戏名称',
                'g_order'=>0//'排序号',
            );
            $game_id = $conn->save('kyx_game_info', $arr);
            //插入redis
            $redis->set($redis_key,$game_id);
        }else{//如果有找到，则读取ID
            $game_id = $redis_ok;
        }

        $row = array(
            "in_date" => $this_date, //下载日期
            "game_id" => intval($game_id), //游戏id
            "chl_id" => intval($chl_id), //渠道id
            "sdk_conn_num" => intval($val['sdknum']), //sdk里连接次数
            "mac_sdk_conn_num" => intval($val['sdkinum']), //sdk里独立连接次数
            "game_conn_num" => intval($val['gamenum']), //游戏里连接次数
            "mac_game_conn_num" => intval($val['gameinum']), //游戏里独立连接
            "mac_chl_game_num" => intval($val['cgv_num']), //当天渠道游戏独立MAC用户
            "user_num" => isset($mac_data[0]['num']) ? intval($mac_data[0]['num']) : 0 //当天独立MAC用户数
        );
        $conn->save('kyx_sdk_handle_connect_time', $row);
    }
    echo("成功更新实体手柄连接数据");
}else{
    echo("实体手柄连接数据为空");
}

