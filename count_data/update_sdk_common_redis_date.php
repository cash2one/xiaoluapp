<?php
/*=============================================================================
#     FileName: update_sdk_common_redis_date.php
#         Desc: SDK定期统计信息
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-07-23 15:37:48
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

//获取特定需要执行统计的id下标
$tmp_id = isset($_SERVER['argv'][1]) ? intval($_SERVER['argv'][1]) : 0;

//获取上一天的日期
$this_date = isset($_SERVER['argv'][2]) ? intval($_SERVER['argv'][2]) : date('Ymd',THIS_DATETIME - 86400);

if(!empty($tmp_id) && isset($sdk_stati_redis_param[$tmp_id]) && !empty($sdk_stati_redis_param[$tmp_id])){
    $temp_sdk_param = $sdk_stati_redis_param[$tmp_id];
    $sdk_stati_redis_param = array();
    $sdk_stati_redis_param[] = $temp_sdk_param;
}

if(!empty($sdk_stati_redis_param)){
    foreach($sdk_stati_redis_param as $val){

        //拼接查询field字符串
        $sql = 'SELECT ';
        if(!empty($val['many_eid'])){ //多事件统计
            foreach($val['many_eid'] as $mkey => $mval){
                $sql .= "COUNT(if(`".$val['field_pre']."_".$val['eid_key']."` = ".$mkey.",true,null)) AS ".$mval.","."COUNT(DISTINCT if(`".$val['field_pre']."_".$val['eid_key']."` = ".$mkey.",CONCAT(`".$val['field_pre']."_mac`,`".$val['field_pre']."_imei`),null)) AS i".$mval.",";
            }
        }

        //其他维度sql
        if(!empty($val['other_sql'])){
            $sql .= $val['other_sql'].",";
        }

        //是否统计当天独立用户数
        if(!empty($val['today_num'])){
            //获取当天的独立用户总数
            $mac_sql = "SELECT COUNT(DISTINCT `".$val['field_pre']."_mac`,`".$val['field_pre']."_imei`) AS user_num FROM `".$val['log_db_name']."` WHERE `".$val['field_pre']."_in_date`=".$this_date;
            $mac_data = $conn->find($mac_sql);
        }

        //拼接其他sql
        $sql .= "`".$val['field_pre']."_title`,`".$val['field_pre']."_pn`,`".$val['field_pre']."_vc`,`".$val['field_pre']."_chl`
                FROM `".$val['log_db_name']."` WHERE `".$val['field_pre']."_in_date` = ".$this_date." GROUP BY ".$val['dimensionality'];

        $data = $conn->find($sql);
        if(!empty($data)){
            $redis->select(2);//选择redis的第三个数据库来存放
            foreach ($data as &$tval){

                //检查是否有记录这个渠道
                $redis_key = md5('kyxchl|'.$tval[$val['field_pre'].'_chl']);
                $redis_ok = $redis->get($redis_key);
                //如果没有找到，则插入
                if(!$redis_ok){
                    //把数据插入渠道信息表
                    $arr = array(
                        'c_in_date'=>$this_date,//'记录日期',
                        'c_chl'=>$tval[$val['field_pre'].'_chl'],//'渠道ID',
                        'c_name'=>$tval[$val['field_pre'].'_chl'],//'渠道名称(需要在后台填写)',
                        'c_order'=>0//'排序号',
                    );
                    $chl_id = $conn->save('kyx_channel_info', $arr);
                    //插入redis
                    $redis->set($redis_key,$chl_id);
                }else{//如果有找到，则读取ID
                    $chl_id = $redis_ok;
                }

                //转义字符
                $tval[$val['field_pre'].'_title'] = mysql_real_escape_string($tval[$val['field_pre'].'_title']);

                //检查是否有记录这个游戏
                $redis_key = md5('kyxgame|'.$tval[$val['field_pre'].'_pn']);
                $redis_ok = $redis->get($redis_key);
                //如果没有找到，则插入
                if(!$redis_ok){
                    //把数据插入游戏信息表
                    $arr = array(
                        'g_in_date'=>$this_date,//'记录日期',
                        'g_pn'=>$tval[$val['field_pre'].'_pn'],//'游戏包名',
                        'g_name'=>$tval[$val['field_pre'].'_title'],//'游戏名称',
                        'g_order'=>0//'排序号',
                    );
                    $game_id = $conn->save('kyx_game_info', $arr);
                    //插入redis
                    $redis->set($redis_key,$game_id);
                }else{//如果有找到，则读取ID
                    $game_id = $redis_ok;
                }

                $row = array(
                    "in_date" => $this_date, //录制日期
                    "game_id" => intval($game_id), //游戏id
                    "chl_id" => intval($chl_id) //渠道id
                );

                foreach($val['key_val'] as $kkey => $kval){
                    if($kval['name'] == 'user_num'){
                        $row[$kkey] = isset($mac_data[0]['user_num']) ? intval($mac_data[0]['user_num']) : 0;
                    }else{
                        $row[$kkey] = empty($kval['type']) ? intval($tval[$kval['name']]) : $tval[$kval['name']];
                    }
                }

                $conn->save($val['db_name'], $row);
            }

            echo("成功更新".$val['dis']."数据</br>");
        }else{
            echo($val['dis']."数据为空</br>");
        }
    }
}

