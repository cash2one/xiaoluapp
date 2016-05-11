<?php
/*=============================================================================
#     FileName: tmp_action_export.keyword.count.in.php
#         Desc: 每天凌晨将两天前的数据导入到对应月的表中 kyx_keyword_log_$month
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

$sql   = "SELECT FROM_UNIXTIME(kd_in_time,'%Y%m') AS month FROM kyx_keyword_log GROUP BY FROM_UNIXTIME(kd_in_time,'%Y%m')";
$data = $conn->find($sql);
if($data && isset($data[0])){
	$row['month'] = $data[0]['month'];
    $str_check_sql = "CREATE TABLE IF NOT EXISTS `kyx_keyword_log_".$row['month']."` (
			  `kd_in_date` int(11) NOT NULL COMMENT '记录日期',
			  `kd_keyword` varchar(100) NOT NULL COMMENT '关键词',
			  `kd_in_time` int(11) NOT NULL DEFAULT '0' COMMENT '搜索时间',
			  `kd_is_ok` tinyint(2) NOT NULL DEFAULT '1' COMMENT '是否搜索成功(1成功,2失败)',
			  `kd_key_md5` char(32) NOT NULL COMMENT '关键词MD5值',
			  `kd_is_cache` tinyint(2) NOT NULL DEFAULT '2' COMMENT '是否缓存返回(1缓存返回,2非缓存返回）',
			  `kd_source` tinyint(1) NOT NULL DEFAULT '1' COMMENT '搜索来源（1：游戏搜索 2：视频搜索）'
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='搜索关键词日志表'
     ";
    //检查当用的表是否存在，不存在则建表
    $conn->query($str_check_sql);

     //不归档当天的数据
	$sql = "INSERT INTO `kyx_keyword_log_".$row['month']."` SELECT * FROM kyx_keyword_log WHERE kd_in_time<".$tmp_this_day." and FROM_UNIXTIME(kd_in_time, '%Y%m')='".$row['month']."'";
	$rs = $conn->query($sql);

    // 从临时表中删除对应月的数据
    if($rs){
		//不归档当天的数据
		$sql = "DELETE FROM kyx_keyword_log WHERE kd_in_time<".$tmp_this_day." AND FROM_UNIXTIME(kd_in_time, '%Y%m')='".$row['month']."'";
		$conn->Query($sql);
		echo("关键词数据当月归档成功");
	}else{
		echo("关键词数据当月归档失败");
	}
}else{
    echo("查询关键词数据出错");
}
?>