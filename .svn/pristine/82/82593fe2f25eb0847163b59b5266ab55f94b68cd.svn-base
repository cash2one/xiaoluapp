<?php
/**
 * @copyright: @快游戏 2014
* @description: 获取日志记录
* @file: count.php
* @author: chengdongcai
* @charset: UTF-8
* @time: 2014-12-02  15:38
* @version 1.0
**/
include_once("../config.inc.php");

//导入事件配置表
include_once("../tv_client.event.config.inc.php");


/*参数*/
$tmp_post_data = rtrim(stripcslashes(get_param('content')),",");//通过POST JSON的方式给传数据过来
$tmp_post_data = '['.$tmp_post_data .']';//附加[]，方便json编码解析
if(empty($tmp_post_data)){
    exit(json_encode(array('code'=>0)));
}
$arr = json_decode($tmp_post_data,TRUE);
if(empty($arr)){
     exit(json_encode(array('code'=>0)));
}

foreach ($arr as $value) {
    $value['ip'] =  get_onlineip();//获取客户端的IP
    $value['intime'] = time();//数据传过来的时间
    $value['in_date'] = date("Ymd");
    $event_id = isset($value['eventid'])?$value['eventid']:0;
    if(empty($event_id)){
        continue;
    }
    $data = json_encode($value) .chr(13).chr(10);
    $arr_merge_event = array();
    foreach ($arr_event_config as $dir=>$value) {
        $arr_event_id = $value['event_id'];
        $arr_merge_event  =array_merge($arr_event_id,$arr_merge_event );
        //根据事件ID将数据写入不同的文件
        if(in_array($event_id, $arr_event_id)){
            $log_count = isset($value['log_files_count'])?$value['log_files_count']:5;//随机文件数量
            write_tv_client_file_random($data,$dir,true,$log_count);
        }
    }
    //如果是其他事件，统一放到other文件夹里
    if(!in_array($event_id,$arr_merge_event)){
        write_tv_client_file_random($data,'tv_other',true);
    }
}
exit(json_encode(array('code'=>1)));

$mydata_kk['bug_show'] = intval(get_param('bug_show'));
if($mydata_kk['bug_show']==100){
	echo($tmp_post_data);
}
