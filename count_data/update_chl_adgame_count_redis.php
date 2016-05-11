#! /usr/local/php/bin/php -q
<?php
/*=============================================================================
#     FileName: update_chl_adgame_count_redis.php
#         Desc: 每天更新统计 渠道 + 推广游戏ID + 推广游戏版本 + MAC 的展示、下载 及安装 数量
#       Author: Chen Zhong
#        Email: 342744276@qq.com
#   LastChange: 2015-04-22 16:00:48
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
echo('开始进行数据统计'.$mydata.chr(10));


//渠道.广告推广统计(统计 1、展示次数，2、安装次数，3、下载次数 4、独立展示次数(MAC去重) 5、独立安装次数(MAC去重) 6、独立下载次数(MAC去重))
//展示次数日志表统计
$show_sql = "SELECT `sgsl_chl`,`sgsl_adappid`,`sgsl_advc`,`sgsl_advn`,`sgsl_adpn`,`sgsl_adtitle`,COUNT(`sgsl_mac`) AS `show_num`,COUNT(DISTINCT `sgsl_mac`) AS `indep_show_num`
        FROM `kyx_sdk_game_show_log` WHERE `sgsl_in_date` = ".$mydata
        ." GROUP BY `sgsl_chl`,`sgsl_adappid`,`sgsl_advc`";
$show_data = $conn->find($show_sql);
$temp_data = array(); //三个统计数据表存储数组
if(!empty($show_data)){
    foreach($show_data as $sval){
        $show_md5_key = md5($sval['sgsl_chl'].$sval['sgsl_adappid'].$sval['sgsl_advc']);
        $temp_data[$show_md5_key] = $sval;
    }
}

//安装次数日志表统计
$insert_sql = "SELECT `sgsl_chl`,`sgsl_adappid`,`sgsl_advc`,`sgsl_advn`,`sgsl_adpn`,`sgsl_adtitle`,COUNT(`sgsl_mac`) AS `insert_num`,COUNT(DISTINCT `sgsl_mac`) AS `indep_insert_num`
        FROM `kyx_sdk_game_insert_log` WHERE `sgsl_in_date` = ".$mydata
        ." GROUP BY `sgsl_chl`,`sgsl_adappid`,`sgsl_advc`";
$insert_data = $conn->find($insert_sql);
if(!empty($insert_data)){
    foreach($insert_data as $ival){
        $insert_md5_key = md5($ival['sgsl_chl'].$ival['sgsl_adappid'].$ival['sgsl_advc']);
        if(isset($temp_data[$insert_md5_key])){ //存在该MD5键值数组
            $temp_data[$insert_md5_key]['insert_num'] = $ival['insert_num']; //安装次数
            $temp_data[$insert_md5_key]['indep_insert_num'] = $ival['indep_insert_num']; //独立安装次数
        }else{ //不存在该MD5键值数组
            $temp_data[$insert_md5_key] = $ival;
            $temp_data[$insert_md5_key]['show_num'] = 0; //展示次数
            $temp_data[$insert_md5_key]['indep_show_num'] = 0; //独立展示次数
        }
    }
}

//下载次数日志表统计
$down_sql = "SELECT `sgsl_chl`,`sgsl_adappid`,`sgsl_advc`,`sgsl_advn`,`sgsl_adpn`,`sgsl_adtitle`,COUNT(`sgsl_mac`) AS `down_num`,COUNT(DISTINCT `sgsl_mac`) AS `indep_down_num`
        FROM `kyx_sdk_game_down_log` WHERE `sgsl_in_date` = ".$mydata
        ." GROUP BY `sgsl_chl`,`sgsl_adappid`,`sgsl_advc`";
$down_data = $conn->find($down_sql);
if(!empty($down_data)){
    foreach($down_data as $dval){
        $down_md5_key = md5($dval['sgsl_chl'].$dval['sgsl_adappid'].$dval['sgsl_advc']);
        if(isset($temp_data[$down_md5_key])){ //存在该MD5键值数组
            $temp_data[$down_md5_key]['down_num'] = $dval['down_num']; //下载次数
            $temp_data[$down_md5_key]['indep_down_num'] = $dval['indep_down_num']; //独立下载次数
        }else{ //不存在该MD5键值数组
            $temp_data[$down_md5_key] = $dval;
            $temp_data[$down_md5_key]['show_num'] = 0; //展示次数
            $temp_data[$down_md5_key]['indep_show_num'] = 0; //独立展示次数
            $temp_data[$down_md5_key]['insert_num'] = 0; //安装次数
            $temp_data[$down_md5_key]['indep_insert_num'] = 0; //独立安装次数
        }
    }
}

if(!empty($temp_data)){
    $redis->select(2);//选择redis的第三个数据库来存放
    foreach($temp_data as $val){
        //检查是否有记录这个渠道
        $redis_key = md5('kyxchl|'.$val['sgsl_chl']);
        $redis_ok = $redis->get($redis_key);
        //如果没有找到，则插入
        if(!$redis_ok){
            //把数据插入渠道信息表
            $arr = array(
                'c_in_date'=>$mydata,//'记录日期',
                'c_chl'=>$val['sgsl_chl'],//'渠道ID',
                'c_name'=>$val['sgsl_chl'],//'渠道名称(需要在后台填写)',
                'c_order'=>0//'排序号',
            );
            $chl_id = $conn->save('kyx_channel_info', $arr);
            //插入redis
            $redis->set($redis_key,$chl_id);
        }else{//如果有找到，则读取ID
            $chl_id = $redis_ok;
        }

        //插入渠道，广告推广数据
        $arr = array(
            'in_date' => $mydata,
            'chl_id'  => intval($chl_id),
            'adappid' => intval($val['sgsl_adappid']),
            'advc'    => intval($val['sgsl_advc']),
            'advn'    => mysql_real_escape_string($val['sgsl_advn']),
            'adpn'    => mysql_real_escape_string($val['sgsl_adpn']),
            'adtitle' => mysql_real_escape_string($val['sgsl_adtitle']),
            'show_num'=> intval($val['show_num']),
            'insert_num' => intval($val['insert_num']),
            'down_num' => intval($val['down_num']),
            'show_mnum' => intval($val['indep_show_num']),
            'insert_mnum' => intval($val['indep_insert_num']),
            'down_mnum' => intval($val['indep_down_num'])
        );
        $conn->save('kyx_chl_adgame_time', $arr);
    }
    echo($mydata.'统计数据成功'.chr(10));
}else{
    echo($mydata.'没有查到统计数据！'.chr(10));
}