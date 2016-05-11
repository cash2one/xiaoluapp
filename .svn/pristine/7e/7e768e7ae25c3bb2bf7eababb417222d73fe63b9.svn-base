<?php
/*=============================================================================
#     FileName: tmp_action.video_vert_common.count.in.php
#         Desc: 视频广告定期把记录在文件中的记录数据存在数据库存中
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-09-17 11:20:48
#      History:
=============================================================================*/

include_once("../config.inc.php");
$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
    echo($tmp_ip."已记录非法IP！");
    exit;
}

include_once("../db.save.config.inc.php"); //数据库连接
include_once("../video_vert.event.config.inc.php"); //入库参数配置

//获取数据存储文档后缀
$tmp_aa = isset($_GET["myid"])?intval($_GET["myid"]):'';
if(empty($tmp_aa)){
    $tmp_aa = rand(1,5);
}

//昨天日期
$yes_date = date("Ymd",THIS_DATETIME - 86400);

//如果入库数组不等于空，开始入库操作
if(!empty($video_vert_param)){
    foreach($video_vert_param as $val){

        //组合要入库文件的路径
        $path = WEBPATH_DIR."data/".$val['dir_name']."/data".$yes_date."_".$tmp_aa.".dat";

        //随机取内容
        $tmp_arr = file($path);
        if(is_array($tmp_arr)){
            $tmp_table_name = $val['db_name'];//数据表名字
            $str_sql = "insert into ".$tmp_table_name."(`".$val['field_pre']."_in_date`, `".$val['field_pre']."_md`, `".$val['field_pre']."_bd`, `".$val['field_pre']."_sdkv`,
			            `".$val['field_pre']."_sdkbv`, `".$val['field_pre']."_vc`, `".$val['field_pre']."_vn`, `".$val['field_pre']."_pn`, `".$val['field_pre']."_title`, `".$val['field_pre']."_adtitle`, `".$val['field_pre']."_mac`, `".$val['field_pre']."_chl`,
			             `".$val['field_pre']."_eid`, `".$val['field_pre']."_ct`, `".$val['field_pre']."_st`, `".$val['field_pre']."_ip`, `".$val['field_pre']."_imei`, `".$val['field_pre']."_adid`, `".$val['field_pre']."_source`";

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
                    $tmp_sql_val .= ",'".$tval['md']."'";//型号model
                    $tmp_sql_val .= ",'".$tval['bd']."'";//品牌
                    $tmp_sql_val .= ",".intval($tval['sdkv']);//SDK版本
                    $tmp_sql_val .= ",".intval($tval['sdkbv']);//SDK基础版本
                    $tmp_sql_val .= ",".intval($tval['vc']);//游戏版本号
                    $tmp_sql_val .= ",'".$tval['vn']."'";//游戏版本名称
                    $tmp_sql_val .= ",'".$tval['pn']."'";//游戏包名
                    $tmp_sql_val .= ",'".$tval['title']."'";//APP名称或游戏名称
                    $tmp_sql_val .= ",'".$tval['adtitle']."'";//广告标题
                    $tmp_sql_val .= ",'".$tval['mac']."'";//设备MAC地址
                    $tmp_sql_val .= ",'".$tval['chl']."'";//渠道号
                    $tmp_sql_val .= ",'".$tval['eid']."'";//事件ID
                    $tmp_sql_val .= ",".intval($tval['ct']);//客户端记录时间 long型数据，毫秒级
                    $tmp_sql_val .= ",".$tval['st'];//日志记录时间 long型数据，毫秒级
                    $tmp_sql_val .= ",'".$tval['ip']."'";//获取客户端的IP
                    $tmp_sql_val .= ",'".$tval['imei']."'";//imei串号
                    $tmp_sql_val .= ",".intval($tval['aid']);//广告id
                    $tmp_sql_val .= ",".intval($tval['source']);//来源（1：APP 2：SDK）

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
            echo('导入'.$val['dir_name'].'数据成功<br/>');
        }else{
            echo('没有可导入的'.$val['dir_name'].'数据<br/>');
        }
        unset($tmp_arr);
    }
}