<?php
/*=============================================================================
#     FileName: tmp_action_export.sdk_common.count.in.php
#         Desc: SDK每天凌晨将两天前的数据导入到对应月的表中
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-07-21 14:41:35
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
include_once("../stati.config.inc.php"); //归档参数配置

//前两天取整
$tmp_this_day = date("Ymd",THIS_DATETIME - 86400 * 2);

//如果归档数组不等于空，开始归档操作
if(!empty($sdk_param)){
    foreach($sdk_param as $val){
        if(!empty($val['is_export'])){
            //获取当前归档月
            $sql = "SELECT LEFT(`".$val['field_pre']."_in_date`,6) AS `month` FROM ".$val['db_name']." GROUP BY LEFT(`".$val['field_pre']."_in_date`,6)";
            $data = $conn->find($sql);

            if($data && isset($data[0])){
                $row['month'] = $data[0]['month'];
                $str_check_sql = "CREATE TABLE IF NOT EXISTS `".$val['db_name']."_".$row['month']."` (
                  `".$val['field_pre']."_in_date` int(11) NOT NULL COMMENT '记录日期(Ymd)',
                  `".$val['field_pre']."_md` varchar(100) NOT NULL COMMENT '型号',
                  `".$val['field_pre']."_bd` varchar(100) NOT NULL COMMENT '厂商',
                  `".$val['field_pre']."_dc` varchar(100) NOT NULL COMMENT '手柄型号',
                  `".$val['field_pre']."_sdkv` int(11) DEFAULT '0' COMMENT 'SDK版本',
                  `".$val['field_pre']."_sdkbv` int(11) DEFAULT '0' COMMENT 'SDK基础版本',
                  `".$val['field_pre']."_vc` int(11) DEFAULT '0' COMMENT '游戏版本号',
                  `".$val['field_pre']."_vn` varchar(20) DEFAULT NULL COMMENT '游戏版本名称',
                  `".$val['field_pre']."_pn` varchar(100) DEFAULT NULL COMMENT '游戏包名',
                  `".$val['field_pre']."_title` varchar(100) DEFAULT NULL COMMENT '游戏标题',
                  `".$val['field_pre']."_mac` varchar(32) DEFAULT NULL COMMENT '设备MAC地址',
                  `".$val['field_pre']."_chl` varchar(20) DEFAULT NULL COMMENT '渠道号',
                  `".$val['field_pre']."_eid` varchar(10) DEFAULT NULL COMMENT '事件ID',
                  `".$val['field_pre']."_ct` bigint(20) DEFAULT '0' COMMENT '客户端记录时间',
                  `".$val['field_pre']."_ut` bigint(20) DEFAULT '0' COMMENT '使用时间',
                  `".$val['field_pre']."_st` bigint(20) DEFAULT '0' COMMENT '日志记录时间',
                  `".$val['field_pre']."_ip` varchar(30) DEFAULT NULL COMMENT '获取客户端的IP',
                  `".$val['field_pre']."_imei` varchar(20) DEFAULT NULL COMMENT '手机唯一标示',
                  `".$val['field_pre']."_nte` varchar(50) NOT NULL DEFAULT '' COMMENT '网络状态'
            ";

                //拼接带有私有参数的数据库字段
                if(!empty($val['pri_field'])){
                    foreach($val['pri_field'] as $pval){
                        if(empty($pval['type'])){ //整数
                            $str_check_sql .= ",`".$val['field_pre']."_".$pval['name']."` ".$pval['field_type']."(".$pval['field_len'].") DEFAULT '0' COMMENT '".$pval['field_comm']."'";
                        }else{ //非整数
                            $str_check_sql .= ",`".$val['field_pre']."_".$pval['name']."` ".$pval['field_type']."(".$pval['field_len'].") DEFAULT '' COMMENT '".$pval['field_comm']."'";
                        }
                    }
                }


                $str_check_sql .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='".$val['db_comm']."';";

                //检查当用的表是否存在，不存在则建表
                $conn->query($str_check_sql);

                //不归档当天的数据
                $sql = "INSERT INTO `".$val['db_name']."_".$row['month']."` SELECT * FROM ".$val['db_name']." WHERE `".$val['field_pre']."_in_date` < ".$tmp_this_day." and LEFT(`".$val['field_pre']."_in_date`,6)='".$row['month']."'";

                $rs = $conn->query($sql);

                // 从临时表中删除对应月的数据
                if($rs){
                    //不归档当天的数据
                    $sql = "DELETE FROM ".$val['db_name']." WHERE `".$val['field_pre']."_in_date` < ".$tmp_this_day." AND LEFT(`".$val['field_pre']."_in_date`,6)='".$row['month']."'";
                    $conn->Query($sql);
                    echo($val['db_comm']."数据当月归档成功<br/>");
                }else{
                    echo($val['db_comm']."数据当月归档失败<br/>");
                }
            }else{
                echo("查询".$val['db_comm']."数据出错<br/>");
            }
        }
    }
}

