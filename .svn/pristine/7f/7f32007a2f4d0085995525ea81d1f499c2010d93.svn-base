<?php
/*=============================================================================
#     FileName: tmp_action_export.game_down.count.in.php
#         Desc: 每天凌晨将两天前的数据导入到对应月的表中 kyx_game_down_log_$month
#       Author: chengdongcai
#        Email: ddcai@163.com
#   LastChange: 2015-01-15 16:55:35
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
$tmp_this_day = date("Y-m-d",THIS_DATETIME - 86400 * 2);
$tmp_this_day = strtotime($tmp_this_day);

$sql   = "SELECT FROM_UNIXTIME(gdl_in_time,'%Y%m') AS month FROM kyx_game_down_log GROUP BY FROM_UNIXTIME(gdl_in_time,'%Y%m')";
$data = $conn->find($sql);
if($data && isset($data[0])){
	$row['month'] = $data[0]['month'];
    $str_check_sql = "CREATE TABLE IF NOT EXISTS `kyx_game_down_log_".$row['month']."` (
		  `gdl_in_date` int(11) DEFAULT NULL COMMENT '下载日期',
		  `gv_id` int(11) NOT NULL COMMENT '游戏版本ID(gv_id)',
		  `gdl_ip` char(15) DEFAULT NULL COMMENT '下载IP',
		  `gdl_gpu` varchar(100) DEFAULT NULL COMMENT '下载的GPU',
		  `gdl_cpu` varchar(100) DEFAULT NULL COMMENT '下载的CPU',
		  `gdl_in_time` int(11) DEFAULT NULL COMMENT '下载的时间',
		  `gdl_brand` varchar(100) DEFAULT NULL COMMENT '品牌',
		  `gdl_model` varchar(100) DEFAULT NULL COMMENT '型号',
		  `gdl_sysversion` varchar(30) DEFAULT NULL COMMENT '系统版本'
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='游戏下载日志表'
     ";
    //检查当用的表是否存在，不存在则建表
    $conn->query($str_check_sql);

     //不归档当天的数据
	$sql = "INSERT INTO `kyx_game_down_log_".$row['month']."` SELECT * FROM kyx_game_down_log WHERE gdl_in_time<".$tmp_this_day." and FROM_UNIXTIME(gdl_in_time, '%Y%m')='".$row['month']."'";
	$rs = $conn->query($sql);

    // 从临时表中删除对应月的数据
    if($rs){
		//不归档当天的数据
		$sql = "DELETE FROM kyx_game_down_log WHERE gdl_in_time<".$tmp_this_day." AND FROM_UNIXTIME(gdl_in_time, '%Y%m')='".$row['month']."'";
		$conn->Query($sql);
		echo("下载数据当月归档成功");
	}else{
		echo("下载数据当月归档失败");
	}
}else{
    echo("查询下载数据出错");
}
?>