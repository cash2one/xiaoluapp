<?php
/*=============================================================================
#     FileName: update_app_scanning_date.php
#         Desc: 定期统计模拟手柄连接游戏信息（统计表kyx_app_scanning_log里的数据,存到kyx_app_scanning_time表里
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-07-14 14:15:48
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

$sql = "SELECT count(if(`asl_eid`=300001,true,null)) as snum,count(DISTINCT if(`asl_eid`=300001,`asl_mac`,null)) as sinum,
        count(if(`asl_eid`=300005,true,null)) as tnum,count(DISTINCT if(`asl_eid`=300005,`asl_mac`,null)) as tinum,
        count(if(`asl_eid`=300006,true,null)) as bnum,count(DISTINCT if(`asl_eid`=300006,`asl_mac`,null)) as binum
        FROM `kyx_app_scanning_log` WHERE `asl_in_date` = ".$this_date." GROUP BY `asl_in_date`";
$data = $conn->find($sql);

if(!empty($data)){
    foreach ($data as $val){
        $row = array(
            "in_date" => $this_date, //扫描日期
            "scan_num" => intval($val['snum']), //扫描次数
            "mac_scan_num" => intval($val['sinum']), //独立扫描次数
            "timeout_num" => intval($val['tnum']), //扫描超时次数
            "mac_timeout_num" => intval($val['tinum']), //独立扫描超时次数
            "barcode_num" => intval($val['bnum']), //扫描二维码次数
            "mac_barcode_num" => intval($val['binum']) //独立扫描二维码次数
        );
        $conn->save('kyx_app_scanning_time', $row);
    }
    echo("成功更新模拟手柄扫描统计数据");
}else{
    echo("模拟手柄扫描统计数据为空");
}

