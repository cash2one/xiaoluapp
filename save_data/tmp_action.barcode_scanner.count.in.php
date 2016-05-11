<?php
/*=============================================================================
#     FileName: tmp_action.barcode_scanner.count.in.php
#         Desc: 定期把记录在文件中的数据记录(/countapi/app_barcode_count.php)数据存在数据库存中（模拟手柄二维码扫描下载）
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-07-09 15:01:48
#      History:
=============================================================================*/
include_once("../config.inc.php");
$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
    echo($tmp_ip."已记录非法IP！");
    exit;
}

include_once("../db.save.config.inc.php");

$tmp_aa = isset($_GET["myid"])?intval($_GET["myid"]):'';
if(empty($tmp_aa)){
    $tmp_aa = rand(1,5);
}

//昨天日期
$yes_date = date("Ymd",THIS_DATETIME - 86400);

//组合要入库文件的路径
$path = WEBPATH_DIR."data/barcode_scanner/data".$yes_date."_".$tmp_aa.".dat";

//随机取内容
$tmp_arr = file($path);
if(is_array($tmp_arr)){
    $tmp_table_name = "kyx_barcode_scanner_log";//数据表名字
    $str_sql = "insert into ".$tmp_table_name."(`bsl_in_date`, `bsl_pn`, `bsl_sv`, `bsl_ip`, `bsl_mac`,
			`bsl_port`, `bsl_chl`,`bsl_nip`,`bsl_ua`)values";

    $str_sql_2 = "";
    $i = 0;
    foreach($tmp_arr as $v){
        if(!empty($v)){
            $val = json_decode($v,true);

            //获取真实ip地址
            $nip = '';
            if(isset($val['nip']) && !empty($val['nip'])){
                $nip_arr = explode(',',$val['nip']);
                $nip = isset($nip_arr[0]) ? $nip_arr[0] : '';
            }

            $str_sql_2 .= "(";

            $tmp_sql_val = $yes_date;//日期
            $tmp_sql_val .= ",'".$val['pn']."'";//游戏包名
            $tmp_sql_val .= ",".intval($val['sv'])."";//SDK版本
            $tmp_sql_val .= ",'".$val['ip']."'";//客户端ip
            $tmp_sql_val .= ",'".$val['mac']."'";//设备MAC地址
            $tmp_sql_val .= ",".intval($val['port'])."";//端口号
            $tmp_sql_val .= ",'".$val['chl']."'";//渠道名称
            $tmp_sql_val .= ",'".$nip."'";//服务端获取客户端ip
            $tmp_sql_val .= ",".(isset($val['ua']) ? ("'".$val['ua']."'") : "''")."";//客户端浏览器ua

            $str_sql_2 .= $tmp_sql_val."),";

            if($i!=500){//每500条数据插入一次
                $i++;
            }else{
                $tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
                $conn->query($tmp_sql_3);
                $i = 0;
                $str_sql_2 = "";
            }
        }
    }

    if($str_sql_2!=""){
        $tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
        $conn->query($tmp_sql_3);
    }
    unset($tmp_arr);
    echo('导入barcode_scanner数据成功');
}else{
    echo('没有可导入的barcode_scanner数据');
}
