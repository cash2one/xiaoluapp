<?php
/*=============================================================================
#     FileName: tmp_action.app_game_connect.count.in.php
#         Desc: 定期把记录在文件中的数据记录(/countapi/app_handle_count.php)数据存在数据库存中（模拟手柄连接游戏统计）
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-07-09 16:05:48
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
$path = WEBPATH_DIR."data/app_game_connect/data".$yes_date."_".$tmp_aa.".dat";

//随机取内容
$tmp_arr = file($path);
if(is_array($tmp_arr)){
    $tmp_table_name = "kyx_app_game_connect_log";//数据表名字
    $str_sql = "insert into ".$tmp_table_name."(`agc_in_date`, `agc_md`, `agc_bd`, `agc_rs`, `agc_vc`,
			`agc_vn`, `agc_mac`,`agc_eid`,`agc_ct`,`agc_ip`,`agc_gt`,`agc_pn`,`agc_sbv`,`agc_sv`,`agc_gvn`,`agc_gvc`,
			`agc_gchl`,`agc_gi`,`agc_gp`,`agc_msg`,`agc_type`,`agc_lip`,`agc_game_mac`)values";

    $str_sql_2 = "";
    $i = 0;
    foreach($tmp_arr as $v){
        if(!empty($v)){
            $val = json_decode($v,true);

            //游戏标题特殊处理
            $is_true = strpos($val['ct'],'gametitle=');
            if($is_true){
                $ct = substr($val['ct'],0,$is_true);
                $game_title = str_replace($ct.'gametitle=','',$val['ct']);
            }else{
                $ct = $val['ct'];
                $game_title = $val['gt'];
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
            $tmp_sql_val .= ",".intval($ct)."";//客户端记录时间
            $tmp_sql_val .= ",'".$val['ip']."'";//获取客户端的IP
            $tmp_sql_val .= ",'".$game_title."'";//游戏标题
            $tmp_sql_val .= ",'".$val['pg']."'";//游戏包名
            $tmp_sql_val .= ",".intval($val['sbv'])."";//sdk基础版本号
            $tmp_sql_val .= ",".intval($val['sv'])."";//sdk版本号
            $tmp_sql_val .= ",'".$val['gvn']."'";//游戏版本名称
            $tmp_sql_val .= ",".intval($val['gvc'])."";//游戏版本号
            $tmp_sql_val .= ",'".$val['gchl']."'";//游戏渠道
            $tmp_sql_val .= ",'".$val['gi']."'";//游戏ip地址
            $tmp_sql_val .= ",".intval($val['gp'])."";//端口号
            $tmp_sql_val .= ",'".$val['msg']."'";//提示信息
            $tmp_sql_val .= ",".intval($val['type'])."";//扫描类型（0为通过扫描网络进入游戏 1为扫描二维码进入的 3为通过缓存重新连接）
            $tmp_sql_val .= ",".(isset($val['lip']) ? ("'".$val['lip']."'") : "''")."";//本地ip
            $tmp_sql_val .= ",".(isset($val['gmac']) ? ("'".$val['gmac']."'") : "''")."";//游戏mac地址

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
    echo('导入app_game_connect数据成功');
}else{
    echo('没有可导入的app_game_connect数据');
}
