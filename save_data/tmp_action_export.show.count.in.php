<?php
/*=============================================================================
#     FileName: tmp_action_export.show.count.in.php
#         Desc: 每天凌晨将两天前的数据导入到对应月的表中 kyx_count_log_$month
#       Author: chengdongcai
#        Email: ddcai@163.com
#   LastChange: 2014-12-18 14:55:35
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
//$tmp_this_day = date("Y-m-d",THIS_DATETIME - 86400 * 2);
//$tmp_this_day = strtotime($tmp_this_day);

$sql   = "SELECT LEFT(cl_date,6) AS month FROM kyx_count_log GROUP BY LEFT(cl_date,6) ORDER BY cl_date DESC ";
$data = $conn->find($sql);

if($data && isset($data[0])){
	$row['month'] = $data[0]['month'];
    $str_check_sql = "CREATE TABLE IF NOT EXISTS `kyx_count_log_".$row['month']."` (
			  `cl_date` int(11) NOT NULL DEFAULT '0' COMMENT '记录日期(data(Ymd))',
			  `cl_rooted` int(11) NOT NULL DEFAULT '0' COMMENT '是否有root',
			  `cl_width` int(11) NOT NULL DEFAULT '0' COMMENT '宽',
			  `cl_height` int(11) NOT NULL DEFAULT '0' COMMENT '高',
			  `cl_model` varchar(100) NOT NULL DEFAULT '0' COMMENT '盒子型号',
			  `cl_brand` varchar(100) NOT NULL DEFAULT '0' COMMENT '盒子品牌',
			  `cl_density` int(11) NOT NULL DEFAULT '0' COMMENT '密度',
			  `cl_gpu` varchar(100) NOT NULL DEFAULT '0' COMMENT 'gpu信息',
			  `cl_systemversion` int(11) NOT NULL DEFAULT '0' COMMENT '系统版本',
			  `cl_softwareversion` int(11) NOT NULL DEFAULT '0' COMMENT '软件版本',
			  `cl_cpu` varchar(100) NOT NULL DEFAULT '0' COMMENT 'CPU信息',
			  `cl_firmwire` varchar(100) NOT NULL DEFAULT '0' COMMENT '固件',
			  `cl_mac` varchar(100) NOT NULL DEFAULT '0' COMMENT 'MAC地址',
			  `cl_time` int(11) NOT NULL DEFAULT '0' COMMENT '时间',
			  `cl_eventid` int(11) NOT NULL DEFAULT '0' COMMENT '事件ID',
			  `cl_numbder` varchar(30) NOT NULL DEFAULT '0' COMMENT '统计数据类型ID',
			  `cl_packagename` varchar(100) NOT NULL DEFAULT '0' COMMENT '游戏包名',
			  `cl_versionname` varchar(100) NOT NULL DEFAULT '0' COMMENT '游戏版本名',
			  `cl_versioncode` int(11) NOT NULL DEFAULT '0' COMMENT '游戏版本号',
			  `cl_code` int(11) NOT NULL DEFAULT '0' COMMENT '错误码',
			  `cl_title` varchar(100) NOT NULL DEFAULT '0' COMMENT '游戏名称',
			  `cl_path` varchar(200) NOT NULL DEFAULT '0' COMMENT '安装路径',
			  `cl_installdate` int(11) NOT NULL DEFAULT '0' COMMENT '安装日期',
			  `cl_url` varchar(200) NOT NULL DEFAULT '0' COMMENT '下载地址',
			  `cl_location` int(11) NOT NULL DEFAULT '0' COMMENT '首页大图位置',
			  `cl_appid` int(11) NOT NULL DEFAULT '0' COMMENT '游戏ID',
			  `cl_cateogryid` int(11) NOT NULL DEFAULT '0' COMMENT '分类ID',
			  `cl_ip` varchar(30) NOT NULL DEFAULT '0' COMMENT '客户端IP',
			  `cl_intime` int(11) NOT NULL DEFAULT '0' COMMENT '传数据的时间',
			  `cl_devicename` varchar(100) NOT NULL DEFAULT '0' COMMENT '手柄设置信息',
			  `cl_memorysize` bigint(20) DEFAULT '0' COMMENT '内存大小',
			  `cl_insdcardsize` bigint(20) DEFAULT '0' COMMENT '存储空间',
			  `cl_issdcard` tinyint(2) DEFAULT '0' COMMENT '支持存储卡',
    		  `cl_vid` varchar(30) DEFAULT '0' COMMENT '厂商ID',
			  `cl_pid` varchar(30) DEFAULT '0' COMMENT '产品ID',
			  `cl_mid` varchar(100) DEFAULT '0' COMMENT '产品描述',
	    	  `cl_name` varchar(100) DEFAULT '0' COMMENT '手柄设备名称',
	    	  `cl_keys` text COMMENT '手柄适配键值(json)',
              `cl_logsessionid` varchar(100) DEFAULT '0' COMMENT '唯一会话id',
              `cl_downloadpath` varchar(200) DEFAULT '0' COMMENT '下载保存路径',
              `cl_storagesize` bigint(20) DEFAULT '0' COMMENT '保存路径剩余空间大小',
              `cl_downloadpoint` int(11) DEFAULT '0' COMMENT '下载点类型',
              `cl_downloadurl` varchar(200) DEFAULT '0' COMMENT '下载url地址',
              `cl_backupurl` varchar(200) DEFAULT '0' COMMENT '备用下载点url',
              `cl_gamesize` bigint(20) DEFAULT '0' COMMENT '游戏大小',
              `cl_statuscode` int(11) DEFAULT '0' COMMENT '服务器返回状态码 ',
              `cl_serverip` varchar(30) DEFAULT '0' COMMENT '服务器ip地址 ',
              `cl_errormsg` text COMMENT '错误描述 ',
              `cl_timestr` varchar(100) DEFAULT '0' COMMENT '时间 年月日 时分秒 '
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='客户端日志表(这个表每天会清空转移数据的,数据会按月来记录)'
     ";
    //检查当用的表是否存在，不存在则建表
    $ts = $conn->query($str_check_sql);

     //不归档当天的数据
	$sql = "INSERT INTO `kyx_count_log_".$row['month']."` SELECT * FROM kyx_count_log WHERE cl_date<".$tmp_this_day." and LEFT(cl_date,6)='".$row['month']."'";
    $rs = $conn->query($sql);

    // 从临时表中删除对应月的数据
    if($rs){
		//不归档当天的数据
		$sql = "DELETE FROM kyx_count_log WHERE cl_date<".$tmp_this_day." AND LEFT(cl_date,6)='".$row['month']."'";
		$conn->Query($sql);
		echo("数据当月归档成功");
	}else{
		echo("数据当月归档失败");
	}
}else{
    echo("查询手柄数据出错");
}
?>