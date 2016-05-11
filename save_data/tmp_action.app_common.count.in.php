<?php
/*=============================================================================
#     FileName: tmp_action.app_common.count.in.php
#         Desc: 定期把记录在文件中的数据记录(/countapi/app_handle_count.php)数据存在数据库存中（app模拟手柄通用统计）
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-07-29 14:52:48
#      History:
=============================================================================*/

include_once("../config.inc.php");
$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
    echo($tmp_ip."已记录非法IP！");
    exit;
}

include_once("../db.save.config.inc.php");
include_once("../stati.config.inc.php"); //入库参数配置

$tmp_aa = isset($_GET["myid"])?intval($_GET["myid"]):'';
if(empty($tmp_aa)){
    $tmp_aa = rand(1,5);
}

//昨天日期
$yes_date = date("Ymd",THIS_DATETIME - 86400);

//如果入库数组不等于空，开始入库操作
if(!empty($app_param)){
    foreach($app_param as $val){
        //组合要入库文件的路径
        $path = WEBPATH_DIR."data/".$val['dir_name']."/data".$yes_date."_".$tmp_aa.".dat";

        //随机取内容
        $tmp_arr = file($path);
        if(is_array($tmp_arr)){
            $tmp_table_name = $val['db_name'];//数据表名字
            $str_sql = "insert into ".$tmp_table_name."(`".$val['field_pre']."_in_date`, `".$val['field_pre']."_md`, `".$val['field_pre']."_bd`, `".$val['field_pre']."_rs`, `".$val['field_pre']."_vc`,
			            `".$val['field_pre']."_vn`, `".$val['field_pre']."_mac`, `".$val['field_pre']."_eid`, `".$val['field_pre']."_ct`, `".$val['field_pre']."_ip`, `".$val['field_pre']."_lip`";

            //检查是否游戏相关统计，添加游戏相关统计对应参数
            if(!empty($val['is_game'])){
                $str_sql .= ",`".$val['field_pre']."_gt`,`".$val['field_pre']."_pn`,`".$val['field_pre']."_sbv`,`".$val['field_pre']."_sv`
                            ,`".$val['field_pre']."_gvn`,`".$val['field_pre']."_gvc`,`".$val['field_pre']."_gchl`,`".$val['field_pre']."_gi`
                            ,`".$val['field_pre']."_gp`,`".$val['field_pre']."_gmac`";
            }

            //私有参数不等于空，拼接私有参数
            if(!empty($val['pri_field'])){
                foreach($val['pri_field'] as $pval){
                    $str_sql .= ",`".$val['field_pre']."_".$pval['name']."`";
                }
            }

            $str_sql .= ")values";

            $str_sql_2 = "";
            $i = 0;
            foreach($tmp_arr as $v){
                if(!empty($v)){
                    $tval = json_decode($v,true);

                    $str_sql_2 .= "(";

                    $tmp_sql_val = $yes_date;//日期
                    $tmp_sql_val .= ",'".$tval['md']."'";//型号
                    $tmp_sql_val .= ",'".$tval['bd']."'";//厂商
                    $tmp_sql_val .= ",'".$tval['rs']."'";//固件版本
                    $tmp_sql_val .= ",".intval($tval['vc'])."";//app版本号
                    $tmp_sql_val .= ",'".$tval['vn']."'";//app版本名称
                    $tmp_sql_val .= ",'".$tval['mac']."'";//设备MAC地址
                    $tmp_sql_val .= ",".intval($tval['eid'])."";//事件ID
                    $tmp_sql_val .= ",".intval($tval['ct'])."";//客户端记录时间
                    $tmp_sql_val .= ",'".$tval['ip']."'";//获取客户端的IP
                    $tmp_sql_val .= ",'".$tval['lip']."'";//本地ip

                    //游戏统计参数
                    if(!empty($val['is_game'])){
                        $tmp_sql_val .= ",'".$tval['gt']."'";//游戏标题
                        $tmp_sql_val .= ",'".$tval['pg']."'";//游戏包名
                        $tmp_sql_val .= ",".intval($tval['sbv'])."";//sdk基础版本号
                        $tmp_sql_val .= ",".intval($tval['sv'])."";//sdk版本号
                        $tmp_sql_val .= ",'".$tval['gvn']."'";//游戏版本名称
                        $tmp_sql_val .= ",".intval($tval['gvc'])."";//游戏版本号
                        $tmp_sql_val .= ",'".$tval['gchl']."'";//游戏渠道
                        $tmp_sql_val .= ",'".$tval['gi']."'";//游戏ip地址
                        $tmp_sql_val .= ",".intval($tval['gp'])."";//端口号
                        $tmp_sql_val .= ",'".$tval['gmac']."'";//游戏mac地址
                    }

                    //获取私有参数json值
                    if(!empty($val['pri_field'])){
                        foreach($val['pri_field'] as $pval){
                            if(empty($pval['type'])){ //整数
                                $tmp_sql_val .= ",".(isset($tval[$pval['param_name']]) ? intval($tval[$pval['param_name']]) : 0)."";
                            }else{ //非整数
                                $tmp_sql_val .= ",".(isset($tval[$pval['param_name']]) ? ("'".$tval[$pval['param_name']]."'") : "''")."";
                            }
                        }
                    }

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
            echo('导入'.$val['dir_name'].'数据成功<br/>');
        }else{
            echo('没有可导入的'.$val['dir_name'].'数据<br/>');
        }
    }
}