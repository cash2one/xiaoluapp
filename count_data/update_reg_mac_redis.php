#! /usr/local/php/bin/php -q
<?php
/*=============================================================================
#     FileName: update_reg_mac_redis.php
#         Desc: 定期统计 游戏 +版本+渠道 + MAC 注册信息
#       Author: chengdongcai
#        Email: ddcai@163.com
#   LastChange: 2015-03-27 10:37:48
#      History:
=============================================================================*/
//非命令行下 404
if(php_sapi_name() != 'cli') {
	header('HTTP/1.1 404 Not Found');
	header('status: 404 Not Found');
	exit;
}
include_once(str_replace("count_data","",dirname(__FILE__))."config.inc.php");
include_once(WEBPATH_DIR."include".DS."redis.class.php");//redis处理类
include_once(WEBPATH_DIR."redis.config.inc.php"); //redis连接

//redis连接
//$b = array('host'=>'127.0.0.1','port'=>6379);
//$redis = new myredis($b);

/*
$tmp_ip = get_onlineip();//获取客户端的IP
if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！".$tmp_ip);
	exit;
}
*/
include_once(WEBPATH_DIR."db.save.config.inc.php");
//如果有参数，则用参数的时间
$mydata = isset($_SERVER['argv'][1])?$_SERVER['argv'][1]:'';
$tmp_len = strlen($mydata);
if( !is_empty($mydata) && $tmp_len!=8 ){
	echo('输入的日期不对:'.$mydata);
	exit;
}else if(is_empty($mydata)){//如果没有参数，则获取上一天
	$mydata = date("Ymd",THIS_DATETIME - 86400);//20150317;
}
echo('开始进行更新'.$mydata.chr(10));

//设置昨天统计的数据
//$mydata = date("Ymd",THIS_DATETIME - 86400);//20150317;
//$mydata = 20150314;

//查当天有登陆的用户
// $sql = 'SELECT `sl_pn`, `sl_vc`,`sl_title`,`sl_in_date`, `sl_md`, `sl_bd`, `sl_dc`,`sl_mac`,
// 		`sl_chl`,sum(`sl_ut`) as `sl_ut`, `sl_ip` ,`sl_st`,count(sl_mac) as num
// 		FROM `kyx_sdk_login_log` WHERE sl_in_date='.$mydata.' group by sl_pn,sl_vc,sl_mac,sl_chl';
//$row = $conn->find($sql);

//每页显示数据
//$page_size = 50000;

//获取数据总数
//$count_sql = "SELECT COUNT(*) AS num FROM `kyx_sdk_login_log` WHERE sl_in_date= ".$mydata;
//$count_data = $conn->find($count_sql);
//$max_page = ceil($count_data[0]['num']/$page_size);//最大页数
//$row = array();
//if($max_page > 0){
//    for($i = 1;$i <= $max_page;$i++){
//        $sql = 'SELECT `sl_pn`, `sl_vc`,`sl_title`,`sl_in_date`, `sl_md`, `sl_bd`, `sl_dc`,`sl_mac`,
//		        `sl_chl`,`sl_ut`, `sl_ip` ,`sl_st` FROM `kyx_sdk_login_log` WHERE sl_in_date='.$mydata.' ORDER BY sl_st DESC
//		        LIMIT '.($i - 1) * $page_size.','.$page_size;
//        $data = $conn->find($sql);
//        foreach ($data as $key => $value) {
//            $key_value = md5($value['sl_pn'] . '-' . $value['sl_vc'] . '-' .  $value['sl_mac'] . '-' . $value['sl_chl']);
//            if(isset($row[$key_value])){
//                $row[$key_value]['sl_ut'] += $value['sl_ut'];
//                $row[$key_value]['num'] ++;
//            }else{
//                $row[$key_value]['sl_ut'] = $value['sl_ut'];
//                $row[$key_value]['num'] = 1;
//                $row[$key_value]['sl_pn'] = $value['sl_pn'];
//                $row[$key_value]['sl_vc'] = $value['sl_vc'];
//                $row[$key_value]['sl_title'] = $value['sl_title'];
//                $row[$key_value]['sl_in_date'] = $value['sl_in_date'];
//                $row[$key_value]['sl_md'] = $value['sl_md'];
//                $row[$key_value]['sl_bd'] = $value['sl_bd'];
//                $row[$key_value]['sl_dc'] = $value['sl_dc'];
//                $row[$key_value]['sl_mac'] = $value['sl_mac'];
//                $row[$key_value]['sl_chl'] = $value['sl_chl'];
//                $row[$key_value]['sl_ip'] = $value['sl_ip'];
//                $row[$key_value]['sl_st'] = $value['sl_st'];
//
//            }
//            unset($data[$key]);
//        }
//        unset($data);
//    }
//}

//设置memory_limit值为2048，防止因内存不足导致无法统计退出
ini_set('memory_limit','2048M');

//获取统计数据渠道数组
$sql = "SELECT `sl_chl` FROM `kyx_sdk_login_log` WHERE `sl_in_date` = ".$mydata." GROUP BY `sl_chl`";
$chl_arr = $conn->find($sql);

//如果渠道数不等于空，按每个渠道进行分批统计
if(!empty($chl_arr)){
    foreach($chl_arr as $val){
        $row = array();

        //每个渠道的数据
        $sql = "SELECT `sl_pn`, `sl_vc`,`sl_title`,`sl_in_date`, `sl_md`, `sl_bd`, `sl_dc`,`sl_mac`,
		        `sl_chl`,`sl_ut`, `sl_ip` ,`sl_st` FROM `kyx_sdk_login_log` WHERE sl_in_date=".$mydata." AND `sl_chl` = '".$val['sl_chl']."'";
        $data = $conn->find($sql);
        foreach ($data as $key => $value) {
            $key_value = md5($value['sl_pn'] . '-' . $value['sl_vc'] . '-' .  $value['sl_mac']);
            if(isset($row[$key_value])){
                $row[$key_value]['sl_ut'] += $value['sl_ut'];
                $row[$key_value]['num'] ++;
            }else{
                $row[$key_value]['sl_ut'] = $value['sl_ut'];
                $row[$key_value]['num'] = 1;
                $row[$key_value]['sl_pn'] = $value['sl_pn'];
                $row[$key_value]['sl_vc'] = $value['sl_vc'];
                $row[$key_value]['sl_title'] = $value['sl_title'];
                $row[$key_value]['sl_in_date'] = $value['sl_in_date'];
                $row[$key_value]['sl_md'] = $value['sl_md'];
                $row[$key_value]['sl_bd'] = $value['sl_bd'];
                $row[$key_value]['sl_dc'] = $value['sl_dc'];
                $row[$key_value]['sl_mac'] = $value['sl_mac'];
                $row[$key_value]['sl_chl'] = $value['sl_chl'];
                $row[$key_value]['sl_ip'] = $value['sl_ip'];
                $row[$key_value]['sl_st'] = $value['sl_st'];

            }
            unset($data[$key]);
        }
        unset($data);

        //对渠道数据进行统计
        if($row){
            //=====begin 每天登陆日志
            //初值
            $str_sql = "insert into kyx_game_reg_mac_login(`grml_login_date`, `grml_in_date`, `grml_vc`,
			            `grml_title`, `grml_pn`, `grml_mac`, `grml_chl`, `grml_time`, `grml_reg_time`,`grml_ip`, `grml_ut`,
			            `grml_num`,`grml_md`, `grml_bd`, `grml_dc`)values";
            $str_sql_2 = "";
            $i=0;
            //=====end 每天登陆日志

            //======begin 每天MAC注册
            //初值
            $str_sql_reg = "insert into `kyx_game_reg_mac`(`grm_in_date`, `grm_vc`, `grm_pn`, `grm_title`,
		                    `grm_mac`, `grm_chl`, `grm_time`, `grm_md`, `grm_bd`, `grm_dc`, `grm_ip`, `grm_ut`)values";
            $str_sql_2_reg = "";
            $i_reg=0;
            //======end 每天MAC注册
            $redis->select(1);//选择redis的第二个数据库来存放
            foreach ($row as $rkey => $val){

                //转义字符
                $val['sl_title'] = mysql_real_escape_string($val['sl_title']);

                //查下这个渠道下是否含有对应的MAC
                //组合唯一KEY（包名 + 版本号 + 渠道 + mac)
                $redis_key = md5($val['sl_pn'].$val['sl_vc'].$val['sl_chl'].$val['sl_mac']);
                //组合注册的时间_时段
                $redis_val = date('YmdH',$val['sl_st']);
                //组合登陆日时间_时段
                $redis_login_val = date('YmdH',$val['sl_st']);
                $is_ok = $redis->get($redis_key);
                //如果没有找到，则进行注册
                if(!$is_ok){
                    //把没有的key和值插入redis中去
                    $redis->set($redis_key,$redis_val);
                    $str_sql_2_reg.= "(";
                    $tmp_sql_val = substr($redis_val,0,8).",";
                    $tmp_sql_val .= intval($val['sl_vc']).",'";
                    $tmp_sql_val .= $val['sl_pn']."','";
                    $tmp_sql_val .= mysql_real_escape_string($val['sl_title'])."','";
                    $tmp_sql_val .= $val['sl_mac']."','";
                    $tmp_sql_val .= $val['sl_chl']."',";
                    $tmp_sql_val .= intval(substr($redis_val,-2)).",'";
                    $tmp_sql_val .= $val['sl_md']."','";
                    $tmp_sql_val .= $val['sl_bd']."','";
                    $tmp_sql_val .= $val['sl_dc']."','";
                    $tmp_sql_val .= $val['sl_ip']."',";
                    $tmp_sql_val .= $val['sl_ut'];

                    $str_sql_2_reg .= $tmp_sql_val."),";
                    if($i < 500){//每500条数据插入一次
                        $i_reg++;
                    }else{
                        $tmp_sql_3 = $str_sql_reg.substr($str_sql_2_reg,0,strlen($str_sql_2_reg)-1);
                        $conn->query($tmp_sql_3);
                        $i_reg = 0;
                        $str_sql_2_reg = '';
                    }

                }else{//如果有找到，则更新最后登陆时间
                    //注册时间
                    $redis_val = $is_ok;
                }
                //添加用户当天的登陆记录
                $str_sql_2 .= "(";
                $tmp_sql_val = $val['sl_in_date'].",";//登陆日期
                $tmp_sql_val .= substr($redis_val,0,8).",";//注册日期
                $tmp_sql_val .= intval($val['sl_vc']).",'";//版本号
                $tmp_sql_val .= $val['sl_title']."','";//游戏名称
                $tmp_sql_val .= $val['sl_pn']."','";//游戏包名
                $tmp_sql_val .= $val['sl_mac']."','";//设备MAC地址
                $tmp_sql_val .= $val['sl_chl']."',";//登陆渠道号
                $tmp_sql_val .= intval(substr($redis_login_val,-2)).",";//登陆时段
                $tmp_sql_val .= intval(substr($redis_val,-2)).",'";//注册时段
                $tmp_sql_val .= $val['sl_ip']."',";//登陆IP
                $tmp_sql_val .= intval($val['sl_ut']).",";//登陆游戏时长
                $tmp_sql_val .= intval($val['num']).",'";//登陆游戏次数
                $tmp_sql_val .= $val['sl_md']."','";//'型号',
                $tmp_sql_val .= $val['sl_bd']."','";//'厂商',
                $tmp_sql_val .= $val['sl_dc']."'";//'手柄型号'

                $str_sql_2 .= $tmp_sql_val."),";

                unset($row[$rkey]);

                if($i < 500){//每500条数据插入一次
                    $i++;
                }else{
                    $tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
                    $conn->query($tmp_sql_3);
                    $i = 0;
                    $str_sql_2 = '';
                }
            }

            //插入剩下的注册信息
            if($str_sql_2_reg!=""){
                $tmp_sql_3 = $str_sql_reg.substr($str_sql_2_reg,0,strlen($str_sql_2_reg)-1);
                $conn->query($tmp_sql_3);
                unset($str_sql_2_reg,$tmp_sql_3,$str_sql_reg);
            }
            //插入剩下的登陆信息
            if($str_sql_2!=""){
                $tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
                $conn->query($tmp_sql_3);
                unset($str_sql_2,$tmp_sql_3,$str_sql);
            }
            echo($mydata.' 渠道：'.$val['sl_chl'].' 更新数据成功'.chr(10));
        }else{
            echo($mydata.' 渠道：'.$val['sl_chl'].' 没有查到数据！'.chr(10));
        }
        unset($row);
    }
}