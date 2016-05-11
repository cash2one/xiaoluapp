<?php
/*=============================================================================
#     FileName: tmp_action.app_socket_exception.count.in.php
#         Desc: 定期把记录在文件中的数据记录(/countapi/app_handle_count.php)数据存在数据库存中（模拟手柄异常统计）
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-07-20 14:16:48
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
$path = WEBPATH_DIR."data/app_socket_exception/data".$yes_date."_".$tmp_aa.".dat";

//随机取内容
$tmp_arr = file($path);
if(is_array($tmp_arr)){
    $tmp_table_name = "kyx_app_socket_exception_log";//数据表名字
    $str_sql = "insert into ".$tmp_table_name."(`ase_in_date`, `ase_md`, `ase_bd`, `ase_rs`, `ase_vc`,
			    `ase_vn`, `ase_mac`,`ase_eid`,`ase_ct`,`ase_ip`,`ase_nip`,`ase_msg`)values";

    $str_sql_2 = "";
    $i = 0;
    foreach($tmp_arr as $v){
        if(!empty($v)){
            $val = json_decode($v,true);

            //获取真实ip地址
            $ip = '';
            if(isset($val['ip']) && !empty($val['ip'])){
                $ip_arr = explode(',',$val['ip']);
                $ip = isset($ip_arr[0]) ? $ip_arr[0] : '';
            }

            $str_sql_2 .= "(";

            $tmp_sql_val = $yes_date;//日期
            $tmp_sql_val .= ",'".$val['md']."'";//型号
            $tmp_sql_val .= ",'".$val['bd']."'";//厂商
            $tmp_sql_val .= ",'".$val['rs']."'";//固件版本
            $tmp_sql_val .= ",".intval($val['vc'])."";//app版本号
            $tmp_sql_val .= ",'".$val['vn']."'";//app版本名称
            $tmp_sql_val .= ",'".$val['mac']."'";//设备MAC地址
            $tmp_sql_val .= ",".intval($val['eid'])."";//事件ID
            $tmp_sql_val .= ",".intval($val['ct'])."";//客户端记录时间
            $tmp_sql_val .= ",'".$ip."'";//获取客户端的IP
            $tmp_sql_val .= ",".(isset($val['lip']) ? ("'".$val['lip']."'") : "''")."";//本地ip
            $tmp_sql_val .= ",'".$val['msg']."'";//提示信息

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
    echo('导入app_socket_exception数据成功');
}else{
    echo('没有可导入的app_socket_exception数据');
}
