<?php
/*=============================================================================
#     FileName: tmp_action_export.sdk_game_down.count.in.php
#         Desc: 每天凌晨将两天前的数据导入到对应月的表中 kyx_sdk_game_down_log_$month
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-05-29 17:04:35
#      History:
=============================================================================*/
//TODO
//这个程序需要注意可能会出现多天的数据

// 首先要查看有有哪几个月的数据（不排除可能出现多个月的数据）;
// 每个月的表需要判断是否需要建立；
// 导完对应月的数据后需要删除对应月的数据；
// 也有可能往临时表写数据的时候导出程序正在运行，所以需要控制并发的情况，可能会出现死锁的问题
// 需要考虑导指定月份的数据

include_once("../config.inc.php");
$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！");
	exit;
}

include_once("../db.save.config.inc.php");


//前两天取整
$tmp_this_day = date("Ymd",THIS_DATETIME - 86400 * 2);

$sql   = "SELECT LEFT(sgsl_in_date,6) AS month FROM kyx_sdk_game_down_log GROUP BY LEFT(sgsl_in_date,6)";
$data = $conn->find($sql);
if($data && isset($data[0])){
	$row['month'] = $data[0]['month'];
    $str_check_sql = "CREATE TABLE IF NOT EXISTS `kyx_sdk_game_down_log_".$row['month']."` (
          `sgsl_in_date` int(11) NOT NULL COMMENT '记录日期(Ymd)',
          `sgsl_md` varchar(100) NOT NULL COMMENT '型号',
          `sgsl_bd` varchar(100) NOT NULL COMMENT '厂商',
          `sgsl_dc` varchar(100) NOT NULL COMMENT '手柄型号',
          `sgsl_sdkv` int(11) DEFAULT '0' COMMENT 'SDK版本',
          `sgsl_sdkbv` int(11) DEFAULT '0' COMMENT 'SDK基础版本',
          `sgsl_vc` int(11) DEFAULT '0' COMMENT '游戏版本号',
          `sgsl_vn` varchar(20) DEFAULT NULL COMMENT '游戏版本名称',
          `sgsl_pn` varchar(100) DEFAULT NULL COMMENT '游戏包名',
          `sgsl_title` varchar(100) DEFAULT NULL COMMENT '游戏标题',
          `sgsl_mac` varchar(32) DEFAULT NULL COMMENT '设备MAC地址',
          `sgsl_chl` varchar(20) DEFAULT NULL COMMENT '渠道号',
          `sgsl_eid` varchar(10) DEFAULT NULL COMMENT '事件ID',
          `sgsl_ct` bigint(20) DEFAULT '0' COMMENT '客户端记录时间',
          `sgsl_ut` bigint(20) DEFAULT '0' COMMENT '使用时间',
          `sgsl_st` bigint(20) DEFAULT '0' COMMENT '日志记录时间',
          `sgsl_ip` varchar(30) DEFAULT NULL COMMENT '获取客户端的IP',
          `sgsl_adappid` int(11) DEFAULT '0' COMMENT '推广游戏的gvid',
          `sgsl_advc` int(11) DEFAULT '0' COMMENT '推广游戏的版本号',
          `sgsl_advn` varchar(20) DEFAULT NULL COMMENT '推广游戏的版本名',
          `sgsl_adpn` varchar(100) DEFAULT NULL COMMENT '推广游戏的包名',
          `sgsl_adtitle` varchar(100) DEFAULT NULL COMMENT '推广游戏的名称',
          `sgsl_url` varchar(200) DEFAULT NULL COMMENT '推广游戏的名称',
          `sgsl_nte` varchar(50) NOT NULL DEFAULT '' COMMENT '网络状态'
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='SDK内游戏下载次数日志表';
     ";
    //检查当用的表是否存在，不存在则建表
    $conn->query($str_check_sql);

     //不归档当天的数据
	$sql = "INSERT INTO `kyx_sdk_game_down_log_".$row['month']."` SELECT * FROM kyx_sdk_game_down_log WHERE sgsl_in_date<".$tmp_this_day." and LEFT(sgsl_in_date,6)='".$row['month']."'";
	$rs = $conn->query($sql);

    // 从临时表中删除对应月的数据
    if($rs){
		//不归档当天的数据
		$sql = "DELETE FROM kyx_sdk_game_down_log WHERE sgsl_in_date<".$tmp_this_day." AND LEFT(sgsl_in_date,6)='".$row['month']."'";
		$conn->Query($sql);
		echo("SDK游戏下载数据当月归档成功");
	}else{
		echo("SDK游戏下载数据当月归档失败");
	}
}else{
    echo("查询SDK游戏下载数据出错");
}
?>