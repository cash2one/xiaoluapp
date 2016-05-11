<?php
/*=============================================================================
#     FileName: update_app_show_click_date.php
#         Desc: 定期统计模拟手柄展示点击信息（统计表kyx_app_show_click_log里的数据,存到kyx_app_show_click_time表里
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-07-15 17:47:48
#      History:
=============================================================================*/
include_once("../config.inc.php");

$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！");
	exit;
}
include_once(WEBPATH_DIR."db.save.config.inc.php");

//获取上一天的日期
$this_date = date('Ymd',THIS_DATETIME - 86400);

//统计模拟手柄展示游戏列表次数
$show_sql = "SELECT COUNT(`agsc_mac`) AS snum,COUNT(DISTINCT `agsc_mac`) AS sinum FROM `kyx_app_game_show_click_log`
             WHERE `agsc_eid` = 300002 AND `agsc_in_date` = ".$this_date." GROUP BY `agsc_in_date`";
$show_data = $data = $conn->find($show_sql);
if(!empty($show_data)){
    foreach ($show_data as $val){
        $row = array(
            "in_date" => $this_date, //下载日期
            "sg_num" => intval($val['snum']), //展示游戏列表次数
            "mac_sg_num" => intval($val['sinum']) //独立展示游戏列表次数
        );
        $conn->save('kyx_app_show_list_time', $row);
    }
    echo("成功更新模拟手柄展示游戏列表数据<br/>");
}else{
    echo("模拟手柄展示游戏列表数据为空<br/>");
}
unset($show_sql,$show_data);

//统计模拟手柄点击手柄图次数
$click_handle_sql = "SELECT COUNT(`agsc_mac`) AS cnum,COUNT(DISTINCT `agsc_mac`) AS cinum,`agsc_gp` FROM `kyx_app_game_show_click_log`
                     WHERE `agsc_eid` = 30010 AND `agsc_in_date` = ".$this_date." GROUP BY `agsc_gp`";
$click_handle_data = $data = $conn->find($click_handle_sql);
if(!empty($click_handle_data)){
    foreach ($click_handle_data as $val){
        $row = array(
            "in_date" => $this_date, //下载日期
            "handle_type" => $val['agsc_gp'], //手柄图类型（二键、四键、八键）
            "ch_num" => intval($val['cnum']), //点击手柄图次数
            "mac_ch_num" => intval($val['cinum']) //模拟手柄点击手柄图统计表
        );
        $conn->save('kyx_app_click_handle_time', $row);
    }
    echo("成功更新模拟手柄点击手柄图数据<br/>");
}else{
    echo("模拟手柄点击手柄图数据为空<br/>");
}
unset($click_handle_sql,$click_handle_data);

//统计模拟手柄点击推荐游戏列表游戏次数
$click_game_sql = "SELECT COUNT(`agsc_mac`) AS ctnum,COUNT(DISTINCT `agsc_mac`) AS ctinum,`agsc_gn` FROM `kyx_app_game_show_click_log`
                     WHERE `agsc_eid` = 30011 AND `agsc_in_date` = ".$this_date." GROUP BY `agsc_gn`";
$click_game_data = $data = $conn->find($click_game_sql);
if(!empty($click_game_data)){
    foreach ($click_game_data as $val){
        $row = array(
            "in_date" => $this_date, //下载日期
            "game_name" => $val['agsc_gn'], //推广游戏名称
            "ctl_num" => intval($val['ctnum']), //模拟手柄点击游戏列表游戏次数
            "mac_ctl_num" => intval($val['ctinum']) //模拟手柄点击游戏列表游戏次数
        );
        $conn->save('kyx_app_click_rec_game_time', $row);
    }
    echo("成功更新模拟手柄点击推荐游戏列表游戏数据<br/>");
}else{
    echo("模拟手柄点击推荐游戏列表游戏数据为空<br/>");
}
unset($click_game_sql,$click_game_data);

