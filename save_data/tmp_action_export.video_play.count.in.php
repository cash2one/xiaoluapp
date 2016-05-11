<?php
/*=============================================================================
#     FileName: tmp_action_export.video_play.count.in.php
#         Desc: 每天凌晨将两天前的数据导入到对应月的表中 kyx_video_play_log_$month
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-05-21 11:13:35
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
//$tmp_this_day = strtotime($tmp_this_day);

$sql   = "SELECT LEFT(vp_in_date,6) AS month FROM kyx_video_play_log GROUP BY LEFT(vp_in_date,6)";
$data = $conn->find($sql);
if($data && isset($data[0])){
    $row['month'] = $data[0]['month'];
    $str_check_sql = "CREATE TABLE IF NOT EXISTS `kyx_video_play_log_".$row['month']."` (
		  `vp_in_date` int(11) NOT NULL COMMENT '播放记录日期（Ymd）',
		  `vp_id` int(11) NOT NULL COMMENT '视频ID(表video_video_list里的id)',
		  `vp_cpu` varchar(100) NOT NULL COMMENT '播放的CPU',
		  `vp_gpu` varchar(100) NOT NULL COMMENT '播放的GPU',
		  `vp_source` int(11) NOT NULL COMMENT '访问来源',
		  `vp_locale` char(10) NOT NULL COMMENT '语言版本',
		  `vp_density` varchar(100) NOT NULL COMMENT '分辨率',
		  `vp_brand` varchar(100) NOT NULL COMMENT '品牌',
		  `vp_model` varchar(100) NOT NULL COMMENT '型号',
		  `vp_mac` varchar(32) NOT NULL COMMENT 'MAX地址'
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='视频播放日志表'
     ";
    //检查当用的表是否存在，不存在则建表
    $conn->query($str_check_sql);

    //不归档当天的数据
    $sql = "INSERT INTO `kyx_video_play_log_".$row['month']."` SELECT * FROM kyx_video_play_log WHERE vp_in_date<".$tmp_this_day." and LEFT(vp_in_date,6)='".$row['month']."'";
    $rs = $conn->query($sql);

    // 从临时表中删除对应月的数据
    if($rs){
        //不归档当天的数据
        $sql = "DELETE FROM kyx_video_play_log WHERE vp_in_date<".$tmp_this_day." AND LEFT(vp_in_date,6)='".$row['month']."'";
        $conn->Query($sql);
        echo("视频播放数据当月归档成功");
    }else{
        echo("视频播放数据当月归档失败");
    }
}else{
    echo("查询播放数据出错");
}
?>