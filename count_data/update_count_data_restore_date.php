<?php
/*=============================================================================
#     FileName: update_count_data_restore_date.php
#         Desc: 统计数据修复
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-09-21 17:03:48
#      History:
=============================================================================*/
include_once("../config.inc.php");

$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！");
	exit;
}

include_once(WEBPATH_DIR."include".DS."redis.class.php");//redis处理类
include_once(WEBPATH_DIR."redis.config.inc.php"); //redis连接
include_once(WEBPATH_DIR."db.save.config.inc.php");
include_once(WEBPATH_DIR."stati.config.inc.php"); //统计参数配置

//重新统计日期数组
$date_arr = array(
    8 => array(6,31),
    9 => array(1,18)
);

foreach($date_arr as $dkey => $dval){
    for($i=$dval[0];$i<=$dval[1];$i++){
        //获取需要统计的日期
        $this_date = ($i < 10) ? intval('20150'.$dkey.'0'.$i) : intval('20150'.$dkey.$i);

        //统计
        if(!empty($app_stati_redis_param)){
            foreach($app_stati_redis_param as $val){

                //拼接查询field字符串
                $sql = 'SELECT ';
                if(!empty($val['many_eid'])){ //多事件统计
                    foreach($val['many_eid'] as $mkey => $mval){
                        $sql .= "COUNT(if(`".$val['field_pre']."_".$val['eid_key']."` = ".$mkey.",true,null)) AS ".$mval.","."COUNT(DISTINCT if(`".$val['field_pre']."_".$val['eid_key']."` = ".$mkey.",`".$val['field_pre']."_mac`,null)) AS i".$mval.",";
                    }
                }

                //其他维度sql
                if(!empty($val['other_sql'])){
                    $sql .= $val['other_sql'].",";
                }

                //是否统计当天独立用户数
                if(!empty($val['today_num'])){
                    //获取当天的独立用户总数
                    $mac_sql = "SELECT COUNT(DISTINCT `".$val['field_pre']."_mac`) AS user_num FROM `".$val['log_db_name']."` WHERE `".$val['field_pre']."_in_date`=".$this_date;
                    $mac_data = $conn->find($mac_sql);
                }

                //拼接其他sql
                if(!empty($val['is_game'])){
                    $sql .= "`".$val['field_pre']."_pn`,`".$val['field_pre']."_vc`,`".$val['field_pre']."_gchl`,
                    `".$val['field_pre']."_gt`";
                }
                $sql .= " FROM `".$val['log_db_name']."` WHERE `".$val['field_pre']."_in_date` = ".$this_date." GROUP BY ".$val['dimensionality'];

                $data = $conn->find($sql);
                if(!empty($data)){
                    $redis->select(2);//选择redis的第三个数据库来存放
                    foreach ($data as &$tval){

                        $row = array(
                            "in_date" => $this_date //日期
                        );

                        //游戏相关统计
                        if(!empty($val['is_game'])){
                            //检查是否有记录这个渠道
                            $redis_key = md5('kyxchl|'.$tval[$val['field_pre'].'_gchl']);
                            $redis_ok = $redis->get($redis_key);
                            //如果没有找到，则插入
                            if(!$redis_ok){
                                //把数据插入渠道信息表
                                $arr = array(
                                    'c_in_date'=>$this_date,//'记录日期',
                                    'c_chl'=>$tval[$val['field_pre'].'_gchl'],//'渠道ID',
                                    'c_name'=>$tval[$val['field_pre'].'_gchl'],//'渠道名称(需要在后台填写)',
                                    'c_order'=>0//'排序号',
                                );
                                $chl_id = $conn->save('kyx_channel_info', $arr);
                                //插入redis
                                $redis->set($redis_key,$chl_id);
                            }else{//如果有找到，则读取ID
                                $chl_id = $redis_ok;
                            }

                            //转义字符
                            $tval[$val['field_pre'].'_gt'] = mysql_real_escape_string($tval[$val['field_pre'].'_gt']);

                            //检查是否有记录这个游戏
                            $redis_key = md5('kyxgame|'.$tval[$val['field_pre'].'_pn']);
                            $redis_ok = $redis->get($redis_key);
                            //如果没有找到，则插入
                            if(!$redis_ok){
                                //把数据插入游戏信息表
                                $arr = array(
                                    'g_in_date'=>$this_date,//'记录日期',
                                    'g_pn'=>$tval[$val['field_pre'].'_pn'],//'游戏包名',
                                    'g_name'=>$tval[$val['field_pre'].'_gt'],//'游戏名称',
                                    'g_order'=>0//'排序号',
                                );
                                $game_id = $conn->save('kyx_game_info', $arr);
                                //插入redis
                                $redis->set($redis_key,$game_id);
                            }else{//如果有找到，则读取ID
                                $game_id = $redis_ok;
                            }

                            $row['game_id'] = intval($game_id); //游戏id
                            $row['game_vc'] = intval($tval[$val['field_pre'].'_vc']); //游戏版本
                            $row['chl_id'] = intval($chl_id); //渠道id
                        }

                        foreach($val['key_val'] as $kkey => $kval){
                            if($kval['name'] == 'user_num'){
                                $row[$kkey] = isset($mac_data[0]['user_num']) ? intval($mac_data[0]['user_num']) : 0;
                            }else{
                                $row[$kkey] = empty($kval['type']) ? intval($tval[$kval['name']]) : $tval[$kval['name']];
                            }
                        }

                        $conn->save($val['db_name'], $row);
                    }

                    echo("成功更新".$this_date.$val['dis']."数据</br>");
                }else{
                    echo($this_date.$val['dis']."数据为空</br>");
                }
            }
        }

        //归档日期（前两天取整）
        $tmp_this_day = date("Ymd",strtotime($this_date) - 86400 * 2);

        //归档
        if(!empty($app_param)){
            foreach($app_param as $val){
                if(!empty($val['is_export'])){
                    //获取当前归档月
                    $sql = "SELECT LEFT(`".$val['field_pre']."_in_date`,6) AS `month` FROM ".$val['db_name']." GROUP BY LEFT(`".$val['field_pre']."_in_date`,6)";
                    $data = $conn->find($sql);

                    if($data && isset($data[0])){
                        $row['month'] = $data[0]['month'];
                        $str_check_sql = "CREATE TABLE IF NOT EXISTS `".$val['db_name']."_".$row['month']."` (
                          `".$val['field_pre']."_in_date` int(11) NOT NULL DEFAULT '0' COMMENT '记录日期(Ymd)',
                          `".$val['field_pre']."_md` varchar(100) NOT NULL DEFAULT '' COMMENT '型号',
                          `".$val['field_pre']."_bd` varchar(100) NOT NULL DEFAULT '' COMMENT '厂商',
                          `".$val['field_pre']."_rs` varchar(100) NOT NULL DEFAULT '' COMMENT '固件版本',
                          `".$val['field_pre']."_vc` int(11) NOT NULL DEFAULT '0' COMMENT 'app版本号',
                          `".$val['field_pre']."_vn` varchar(20) NOT NULL DEFAULT '' COMMENT 'app版本名称',
                          `".$val['field_pre']."_mac` varchar(32) NOT NULL DEFAULT '' COMMENT '设备MAC地址',
                          `".$val['field_pre']."_eid` varchar(10) NOT NULL DEFAULT '' COMMENT '事件ID',
                          `".$val['field_pre']."_ct` bigint(20) NOT NULL DEFAULT '0' COMMENT '客户端记录时间',
                          `".$val['field_pre']."_ip` varchar(30) NOT NULL DEFAULT '' COMMENT '获取客户端的IP',
                          `".$val['field_pre']."_lip` varchar(30) NOT NULL DEFAULT '' COMMENT '本地IP'
                        ";

                        //游戏相关统计参数
                        if(!empty($val['is_game'])){
                            $str_check_sql .= "
                              ,`".$val['field_pre']."_gt` varchar(100) NOT NULL DEFAULT '' COMMENT '游戏标题',
                              `".$val['field_pre']."_pn` varchar(100) NOT NULL DEFAULT '' COMMENT '游戏包名',
                              `".$val['field_pre']."_sbv` int(11) NOT NULL DEFAULT '0' COMMENT 'sdk基础版本号',
                              `".$val['field_pre']."_sv` int(11) NOT NULL DEFAULT '0' COMMENT 'sdk版本号',
                              `".$val['field_pre']."_gvn` varchar(20) NOT NULL DEFAULT '' COMMENT '游戏版本名称',


                              `".$val['field_pre']."_gvc` int(11) NOT NULL DEFAULT '0' COMMENT '游戏版本号',
                              `".$val['field_pre']."_gchl` varchar(30) NOT NULL DEFAULT '' COMMENT '游戏渠道',
                              `".$val['field_pre']."_gi` varchar(30) NOT NULL DEFAULT '' COMMENT '游戏ip地址',
                              `".$val['field_pre']."_gp` int(11) NOT NULL DEFAULT '0' COMMENT '端口号',
                              `".$val['field_pre']."_gmac` varchar(32) NOT NULL DEFAULT '' COMMENT '游戏的mac地址'
                            ";
                        }

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
                            echo($tmp_this_day.'之前_'.$val['db_comm']."数据当月归档成功<br/>");
                        }else{
                            echo($val['db_comm'].'之前_'."数据当月归档失败<br/>");
                        }
                    }else{
                        echo("查询".$val['db_comm'].'之前_'.$val['db_comm']."数据出错<br/>");
                    }
                }
            }
        }
    }
}

