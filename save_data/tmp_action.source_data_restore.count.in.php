#! /usr/local/php/bin/php -q
<?php
/*=============================================================================
#     FileName: tmp_action.source_data_restore.count.in.php
#         Desc: 统计源数据修复（用于统计数据不准确或未统计到）
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-09-26 14:52:48
#      History:
=============================================================================*/

//set_time_limit(0);
include_once(str_replace("save_data","",dirname(__FILE__))."config.inc.php");
$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
    echo($tmp_ip."已记录非法IP！");
    exit;
}

include_once(WEBPATH_DIR."db.save.config.inc.php");
include_once(WEBPATH_DIR."stati.config.inc.php"); //入库参数配置

//重新统计日期数组
$date_arr = array(
    8 => array(6,31),
    9 => array(1,18)
);

unset($sdk_param[12]);
foreach($sdk_param as $val){

    //删除的条件field
    $field = $val['field_pre']."_in_date";

    foreach($date_arr as $dkey => $dval){
        for($i=$dval[0];$i<=$dval[1];$i++){

            //操作数据库
            $tmp_table_name = ($val['db_name'] == 'kyx_sdk_service_exception_log') ? 'kyx_sdk_service_exception_log' : $val['db_name'].'_20150'.$dkey;

            //获取需要入库的日期
            $yes_date = ($i < 10) ? intval('20150'.$dkey.'0'.$i) : intval('20150'.$dkey.$i);

            //删除需要重新入库的数据
            $res = $conn->remove($tmp_table_name, $yes_date, $field);

            if($res){
                echo($val['db_comm'].'_'.$yes_date.'_数据删除成功<br/>'.chr(10).chr(13));

                //根据入库日期入库1-5的dat日志文件
                for($j=1;$j<6;$j++){
                    $tmp_aa = $j;

                    //组合要入库文件的路径
                    $path = WEBPATH_DIR."data/".$val['dir_name']."/data".$yes_date."_".$tmp_aa.".dat";

                    //随机取内容
                    $tmp_arr = file($path);
                    if(is_array($tmp_arr)){
                        $str_sql = "insert into ".$tmp_table_name."(`".$val['field_pre']."_in_date`, `".$val['field_pre']."_md`, `".$val['field_pre']."_bd`, `".$val['field_pre']."_dc`, `".$val['field_pre']."_sdkv`,
                                    `".$val['field_pre']."_sdkbv`, `".$val['field_pre']."_vc`, `".$val['field_pre']."_vn`, `".$val['field_pre']."_pn`, `".$val['field_pre']."_title`, `".$val['field_pre']."_mac`, `".$val['field_pre']."_chl`,
                                    `".$val['field_pre']."_eid`, `".$val['field_pre']."_ct`,`".$val['field_pre']."_ut`, `".$val['field_pre']."_st`, `".$val['field_pre']."_ip`, `".$val['field_pre']."_imei`";

                        //私有参数不等于空，拼接私有参数
                        if(!empty($val['pri_field'])){
                            foreach($val['pri_field'] as $pval){
                                $str_sql .= ",`".$val['field_pre']."_".$pval['name']."`";
                            }
                        }

                        $str_sql .= ")values";

                        $str_sql_2 = "";
                        $n = 0;
                        foreach($tmp_arr as $v){
                            if(!empty($v)){
                                $tval = json_decode($v,true);

                                $str_sql_2 .= "(";

                                $tmp_sql_val = $yes_date;//日期
                                $tmp_sql_val .= ",'".$tval['md']."'";//型号model
                                $tmp_sql_val .= ",'".$tval['bd']."'";//brand
                                $tmp_sql_val .= ",'".$tval['dc']."'";//手柄型号
                                $tmp_sql_val .= ",".intval($tval['sdkv']);//SDK版本
                                $tmp_sql_val .= ",".intval($tval['sdkbv']);//SDK基础版本
                                $tmp_sql_val .= ",".intval($tval['vc']);//游戏版本号
                                $tmp_sql_val .= ",'".$tval['vn']."'";//游戏版本名称
                                $tmp_sql_val .= ",'".$tval['pn']."'";//游戏包名
                                $tmp_sql_val .= ",'".$tval['title']."'";//游戏名称
                                $tmp_sql_val .= ",'".$tval['mac']."'";//设备MAC地址
                                $tmp_sql_val .= ",'".$tval['chl']."'";//渠道号
                                $tmp_sql_val .= ",'".$tval['eid']."'";//事件ID
                                $tmp_sql_val .= ",".intval($tval['ct']);//客户端记录时间 long型数据，毫秒级
                                $tmp_sql_val .= ",".intval($tval['ut']);//使用时间，long型数据，毫秒级
                                $tmp_sql_val .= ",".$tval['st'];//日志记录时间 long型数据，毫秒级
                                $tmp_sql_val .= ",'".$tval['ip']."'";//获取客户端的IP
                                $tmp_sql_val .= ",'".$tval['imei']."'";//imei串号

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

                                if($n!=500){//每500条数据插入一次
                                    $n++;
                                }else{
                                    $tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
                                    $add_res = $conn->query($tmp_sql_3);
                                    $n = 0;
                                    $str_sql_2 = "";
                                }
                            }
                        }

                        if($str_sql_2!=""){
                            $tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
                            $add_res = $conn->query($tmp_sql_3);
                        }

                        if($add_res){
                            echo($val['db_comm'].'_'.$yes_date.'_数据入库成功<br/>'.chr(10).chr(13));
                        }else{
                            echo($val['db_comm'].'_'.$yes_date.'_数据入库失败<br/>'.chr(10).chr(13));
                        }

                    }else{
                        echo($val['db_comm'].'_'.$yes_date.'_数据入库数据为空<br/>'.chr(10).chr(13));
                    }
                    unset($tmp_arr);
                }
            }else{
                echo($val['db_comm'].'_'.$yes_date.'_数据删除失败<br/>'.chr(10).chr(13));
            }
        }
    }
}