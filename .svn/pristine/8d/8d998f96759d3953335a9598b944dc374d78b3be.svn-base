<?php
/*=============================================================================
#     FileName: tmp_action_export.barcode_scanner.count.in.php
#         Desc: 每天凌晨将两天前的数据导入到对应月的表中 kyx_barcode_scanner_log_$month
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-07-09 15:54:35
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

$sql   = "SELECT LEFT(bsl_in_date,6) AS `month` FROM kyx_barcode_scanner_log GROUP BY LEFT(bsl_in_date,6) ORDER BY bsl_in_date DESC";
$data = $conn->find($sql);
if($data && isset($data[0])){
    $row['month'] = $data[0]['month'];
    $str_check_sql = "CREATE TABLE IF NOT EXISTS `kyx_barcode_scanner_log_".$row['month']."` (
          `bsl_in_date` int(11) NOT NULL DEFAULT '0' COMMENT '记录日期(Ymd)',
          `bsl_pn` varchar(100) NOT NULL DEFAULT '' COMMENT '游戏包名',
          `bsl_sv` int(11) NOT NULL DEFAULT '0' COMMENT 'SDK版本',
          `bsl_ip` varchar(30) NOT NULL DEFAULT '' COMMENT '客户端ip',
          `bsl_mac` varchar(32) NOT NULL DEFAULT '' COMMENT '设备MAC地址',
          `bsl_port` int(11) NOT NULL DEFAULT '0' COMMENT '端口号',
          `bsl_chl` varchar(30) NOT NULL DEFAULT '' COMMENT '渠道名称',
          `bsl_nip` varchar(30) NOT NULL DEFAULT '' COMMENT '服务端获取客户端ip',
          `bsl_ua` varchar(255) NOT NULL DEFAULT '' COMMENT '客户端浏览器ua'
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='模拟手柄二维码扫描下载统计日志表'
     ";
    //检查当用的表是否存在，不存在则建表
    $conn->query($str_check_sql);

    //不归档当天的数据
    $sql = "INSERT INTO `kyx_barcode_scanner_log_".$row['month']."` SELECT * FROM kyx_barcode_scanner_log WHERE bsl_in_date<".$tmp_this_day." and LEFT(bsl_in_date,6)='".$row['month']."'";
    $rs = $conn->query($sql);

    // 从临时表中删除对应月的数据
    if($rs){
        //不归档当天的数据
        $sql = "DELETE FROM kyx_barcode_scanner_log WHERE bsl_in_date<".$tmp_this_day." AND LEFT(bsl_in_date,6)='".$row['month']."'";
        $conn->Query($sql);
        echo("模拟手柄二维码扫描下载统计数据当月归档成功");
    }else{
        echo("模拟手柄二维码扫描下载统计数据当月归档失败");
    }
}else{
    echo("查询模拟手柄二维码扫描下载统计数据出错");
}
?>