<?php
/*=============================================================================
#     FileName: tmp_action_export.app_scanning.count.in.php
#         Desc: 每天凌晨将两天前的数据导入到对应月的表中 kyx_app_scanning_log_$month
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-07-14 14:38:35
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

$sql   = "SELECT LEFT(`asl_in_date`,6) AS `month` FROM kyx_app_scanning_log GROUP BY LEFT(`asl_in_date`,6) ORDER BY `asl_in_date` DESC";
$data = $conn->find($sql);
if($data && isset($data[0])){
    $row['month'] = $data[0]['month'];
    $str_check_sql = "CREATE TABLE IF NOT EXISTS `kyx_app_scanning_log_".$row['month']."` (
          `asl_in_date` int(11) NOT NULL DEFAULT '0' COMMENT '记录日期(Ymd)',
          `asl_md` varchar(100) NOT NULL DEFAULT '' COMMENT '型号',
          `asl_bd` varchar(100) NOT NULL DEFAULT '' COMMENT '厂商',
          `asl_rs` varchar(100) NOT NULL DEFAULT '' COMMENT '固件版本',
          `asl_vc` int(11) NOT NULL DEFAULT '0' COMMENT 'app版本号',
          `asl_vn` varchar(20) NOT NULL DEFAULT '' COMMENT 'app版本名称',
          `asl_mac` varchar(32) NOT NULL DEFAULT '' COMMENT '设备MAC地址',
          `asl_eid` int(11) NOT NULL DEFAULT '0' COMMENT '事件ID',
          `asl_ct` bigint(20) NOT NULL DEFAULT '0' COMMENT '客户端记录时间',
          `asl_ip` varchar(30) NOT NULL DEFAULT '' COMMENT '获取客户端的IP',
          `asl_nip` varchar(30) NOT NULL DEFAULT '' COMMENT '本地ip'
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='模拟手柄扫描统计日志表'
     ";
    //检查当用的表是否存在，不存在则建表
    $conn->query($str_check_sql);

    //不归档当天的数据
    $sql = "INSERT INTO `kyx_app_scanning_log_".$row['month']."` SELECT * FROM kyx_app_scanning_log WHERE `asl_in_date` < ".$tmp_this_day." and LEFT(`asl_in_date`,6)='".$row['month']."'";
    $rs = $conn->query($sql);

    // 从临时表中删除对应月的数据
    if($rs){
        //不归档当天的数据
        $sql = "DELETE FROM kyx_app_scanning_log WHERE `asl_in_date`<".$tmp_this_day." AND LEFT(`asl_in_date`,6)='".$row['month']."'";
        $conn->Query($sql);
        echo("模拟手柄扫描统计数据当月归档成功");
    }else{
        echo("模拟手柄扫描统计数据当月归档失败");
    }
}else{
    echo("查询模拟手柄扫描统计数据出错");
}
?>