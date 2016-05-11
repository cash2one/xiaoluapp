<?php
/*=============================================================================
#     FileName: update_barcode_scanner_date.php
#         Desc: 定期统计模拟手柄扫描二维码下载信息（统计表kyx_barcode_scanner_log里的数据,存到kyx_barcode_scanner_time表里
#       Author: Chen Zhong
#        Email: chenzhong@kuaiyouxi.com
#   LastChange: 2015-07-08 15:32:48
#      History:
=============================================================================*/
include_once("../config.inc.php");

$tmp_ip = get_onlineip();//获取客户端的IP
sys_log_write_content('开始执行计划任务,IP：'.$tmp_ip,'barcode_log');

if(!in_array($tmp_ip, $GLOBALS['SYS_AUTO_ACTION_IP'])){
	echo($tmp_ip."已记录非法IP！");
	exit;
}

sys_log_write_content('开始加载redis','barcode_log');
include_once(WEBPATH_DIR."include".DS."redis.class.php");//redis处理类
sys_log_write_content('加载redis类成功','barcode_log');
$aa = include_once(WEBPATH_DIR."redis.config.inc.php"); //redis连接
sys_log_write_content('redis初始化成功','barcode_log');
include_once(WEBPATH_DIR."db.save.config.inc.php");
sys_log_write_content('加载save.config类成功','barcode_log');

//获取上一天的日期
$this_date = date('Ymd',THIS_DATETIME - 86400);

//获取昨天所有用户信息
$user_sql = "SELECT `bsl_mac`,`bsl_nip`,`bsl_ua` FROM `kyx_barcode_scanner_log` WHERE `bsl_in_date` = ".$this_date." GROUP BY `bsl_mac`,`bsl_nip`,`bsl_ua`";
sys_log_write_content($this_date.'-所有用户信息-'.$user_sql,'barcode_log');
$user_data = $conn->find($user_sql);
if(!empty($user_data)){
    $redis->select(3);//选择redis的第四个数据库来存放
    foreach($user_data as $uval){

        //查询数据库是否存在
        $nip = ''; //获取真实ip地址
        if(isset($uval['bsl_nip']) && !empty($uval['bsl_nip'])){
            $nip_arr = explode(',',$uval['bsl_nip']);
            $nip = isset($nip_arr[0]) ? $nip_arr[0] : '';
        }

        //检查是否有记录这个用户
        $redis_key = md5('baruser|'.$uval['bsl_mac'].$nip.$uval['bsl_ua']);
        $redis_val = md5($uval['bsl_mac'].$nip.$uval['bsl_ua']);
        $redis_ok = $redis->get($redis_key);
        //如果没有找到，则插入
        if(!$redis_ok && (!empty($uval['bsl_mac']) || !empty($nip) || !empty($uval['bsl_ua']))){

            $check_sql = "SELECT COUNT(1) as num FROM `kyx_barcode_scanner_user` WHERE `bsu_mac` = '".$uval['bsl_mac']."' AND `bsu_nip` = '".$nip."' AND `bsu_ua` = '".$uval['bsl_ua']."'";
            sys_log_write_content($this_date.'-检查用户是否存在-'.$user_sql,'barcode_log');
            $check_data = $conn->find($check_sql);

            if(isset($check_data[0]['num']) && empty($check_data[0]['num'])){
                //把数据插入渠道信息表
                $arr = array(
                    'bsu_in_date'=>$this_date, //记录日期,
                    'bsu_mac'=>$uval['bsl_mac'], //MAC地址,
                    'bsu_nip'=>$nip, //ip地址,
                    'bsu_ua'=>$uval['bsl_ua'] //浏览器ua,
                );
                $conn->save('kyx_barcode_scanner_user', $arr);

                //插入redis
                $redis->set($redis_key,$redis_val);
            }else{
                //插入redis
                $redis->set($redis_key,$redis_val);
            }
        }
    }
}

$sql = 'SELECT `bsl_in_date`,`bsl_pn`,`bsl_chl`,count(*) as num,count(DISTINCT `bsl_mac`) as ipnum FROM `kyx_barcode_scanner_log` WHERE bsl_in_date='.$this_date.' GROUP BY `bsl_chl`,`bsl_pn`';
sys_log_write_content($this_date.'-查询数据-'.$user_sql,'barcode_log');
$data = $conn->find($sql);

//获取当天非渠道topic的独立用户总数
$mac_sql = "SELECT COUNT(DISTINCT `bsl_mac`) AS num FROM `kyx_barcode_scanner_log` WHERE bsl_chl <> 'topic' AND bsl_in_date=".$this_date;
sys_log_write_content($this_date.'-非渠道独立用户数据-'.$user_sql,'barcode_log');
$mac_data = $conn->find($mac_sql);

//获取渠道topic的独立用户总数
$top_mac_sql = "SELECT COUNT(DISTINCT `bsl_nip`,`bsl_ua`) AS num FROM `kyx_barcode_scanner_log` WHERE bsl_chl = 'topic' AND bsl_in_date=".$this_date;
sys_log_write_content($this_date.'-渠道独立用户数据-'.$top_mac_sql,'barcode_log');
$top_mac_data = $conn->find($top_mac_sql);

//总的独立用户数
$temp_num = (isset($mac_data[0]['num']) ? intval($mac_data[0]['num']) : 0) + (isset($top_mac_data[0]['num']) ? intval($top_mac_data[0]['num']) : 0);

if(!empty($data)){
    foreach ($data as $val){

        //独立下载次数
        $mac_down_num = ($val['bsl_chl'] == 'topic') ? (isset($top_mac_data[0]['num']) ? intval($top_mac_data[0]['num']) : 0) : intval($val['ipnum']);

        $row = array(
            "in_date" => $this_date, //下载日期
            "game_pn" => $val['bsl_pn'], //游戏包名
            "chl_name" => $val['bsl_chl'], //渠道名称
            "down_num" => intval($val['num']), //下载次数
            "mac_down_num" => $mac_down_num, //独立下载次数
            "mac_user" => $temp_num //当天独立用户总数（不分渠道游戏包）
        );
        $conn->save('kyx_barcode_scanner_time', $row);
    }
    sys_log_write_content($this_date.'-统计完成','barcode_log');
    echo("成功更新模拟手柄扫描二维码下载数据");
}else{
    sys_log_write_content($this_date.'-无统计数据','barcode_log');
    echo("模拟手柄扫描二维码下载数据为空");
}

