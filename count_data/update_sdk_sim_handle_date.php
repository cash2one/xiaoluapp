<?php
/*=============================================================================
#     FileName: update_sdk_sim_handle_date.php
#         Desc: 定期统计SDK模拟手柄统计信息（统计表kyx_sdk_sim_handle_log里的数据,存到kyx_sdk_sim_handle_time表里
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-07-11 14:41:48
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

$sql = "SELECT count(if(`sshl_eid`=200001,true,null)) as shnum,count(DISTINCT if(`sshl_eid`=200001,CONCAT(`sshl_mac`,`sshl_imei`),null)) as shinum,
        count(if(`sshl_eid`=200002,true,null)) as sunum,count(DISTINCT if(`sshl_eid`=200002,CONCAT(`sshl_mac`,`sshl_imei`),null)) as suinum,
        count(if(`sshl_eid`=200005,true,null)) as cnum,count(DISTINCT if(`sshl_eid`=200005,CONCAT(`sshl_mac`,`sshl_imei`),null)) as cinum,
        count(if(`sshl_eid`=200006,true,null)) as rnum,count(DISTINCT if(`sshl_eid`=200006,CONCAT(`sshl_mac`,`sshl_imei`),null)) as rinum,
        count(DISTINCT `sshl_mac`,`sshl_imei`) as cgv_num,`sshl_chl`,`sshl_title`,`sshl_pn`,`sshl_vc`
        FROM `kyx_sdk_sim_handle_log` WHERE `sshl_in_date` = ".$this_date." GROUP BY `sshl_chl`,`sshl_pn`";
$data = $conn->find($sql);

//获取当天的独立用户总数
$mac_sql = "SELECT COUNT(DISTINCT `sshl_mac`,`sshl_imei`) AS num FROM `kyx_sdk_sim_handle_log` WHERE `sshl_in_date`=".$this_date;
$mac_data = $conn->find($mac_sql);

if(!empty($data)){
    $redis->select(2);//选择redis的第三个数据库来存放
    foreach ($data as $val){

        //检查是否有记录这个渠道
        $redis_key = md5('kyxchl|'.$val['sshl_chl']);
        $redis_ok = $redis->get($redis_key);
        //如果没有找到，则插入
        if(!$redis_ok){
            //把数据插入渠道信息表
            $arr = array(
                'c_in_date'=>$this_date,//'记录日期',
                'c_chl'=>$val['sshl_chl'],//'渠道ID',
                'c_name'=>$val['sshl_chl'],//'渠道名称(需要在后台填写)',
                'c_order'=>0//'排序号',
            );
            $chl_id = $conn->save('kyx_channel_info', $arr);
            //插入redis
            $redis->set($redis_key,$chl_id);
        }else{//如果有找到，则读取ID
            $chl_id = $redis_ok;
        }

        //转义字符
        $val['sshl_title'] = mysql_real_escape_string($val['sshl_title']);

        //检查是否有记录这个游戏
        $redis_key = md5('kyxgame|'.$val['sshl_pn']);
        $redis_ok = $redis->get($redis_key);
        //如果没有找到，则插入
        if(!$redis_ok){
            //把数据插入游戏信息表
            $arr = array(
                'g_in_date'=>$this_date,//'记录日期',
                'g_pn'=>$val['sshl_pn'],//'游戏包名',
                'g_name'=>$val['sshl_title'],//'游戏名称',
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
            "show_num" => intval($val['shnum']), //模拟手柄页展示次数
            "mac_show_num" => intval($val['shinum']), //模拟手柄页独立展示次数
            "succ_num" => intval($val['sunum']), //模拟手柄连接成功次数
            "mac_succ_num" => intval($val['suinum']), //模拟手柄独立连接成功次数
            "off_num" => intval($val['cnum']), //模拟手柄断开次数
            "mac_off_num" => intval($val['cinum']), //模拟手柄独立断开次数
            "recon_num" => intval($val['rnum']), //模拟手柄重连次数
            "mac_recon_num" => intval($val['rinum']), //模拟手柄独立重连次数
            "mac_chl_game_num" => intval($val['cgv_num']), //当天渠道游戏独立MAC用户
            "user_num" => isset($mac_data[0]['num']) ? intval($mac_data[0]['num']) : 0 //当天独立MAC用户数
        );
        $conn->save('kyx_sdk_sim_handle_time', $row);
    }
    echo("成功更新模拟手柄连接游戏数据");
}else{
    echo("模拟手柄连接游戏数据为空");
}

