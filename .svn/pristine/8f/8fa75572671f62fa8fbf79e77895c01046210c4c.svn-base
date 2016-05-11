<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 用户订阅列表
 * @file: video_user_sub_index_nav.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2016-01-19  14:43
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['uid'] = intval(get_param('uid')); //用户id
$mydata['mac'] = get_param('mac');//用户mac地址
$mydata['imei'] = get_param('imei');//用户imei地址
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

$mem_obj = new kyx_memcache();

$check_where = '';
if(!empty($mydata['mac']) && $mydata['mac'] <> '00:00:00:00:00:00'){
    $check_where .= " AND `mac` = '".$mydata['mac']."'";
}
if(!empty($mydata['imei'])){
    $check_where .= " AND `imei` = '".$mydata['imei']."'";
}

//用户已订阅信息
$user_sub_info_key = 'user_sub_info_'.md5($mydata['uid'].$mydata['mac'].$mydata['imei']);
$sub_info = $mem_obj->get($user_sub_info_key);
if($sub_info === false){
    $sub_info = array();
    $sql = "SELECT `subid`,`subtype` FROM `video_user_sub_info` WHERE `status` = 1 ".$check_where;
    $data = $conn->find($sql);
    if(!empty($data)){
        foreach($data as $val){
            $sub_info[] = array(
                'subid' => intval($val['subid']),
                'subtype' => intval($val['subtype']) //订阅类型（1：标签 2：主播 3：游戏 4：游戏分类）
            );
        }
    }
    $mem_obj->set($user_sub_info_key,$sub_info,600);
}

//获取游戏关联名字缓存数据
$all_game_arr_key = 'all_game_arr';
$game_arr = $mem_obj->get($all_game_arr_key);
if($game_arr === false){
    $sql = "SELECT `id`,`gi_name` FROM `video_game_info` WHERE `gi_isshow` = 1";
    $game_arr = $conn->find($sql,'id');
    $mem_obj->set($all_game_arr_key,$game_arr,14400);
}

//定义回转的默认参数
$returnArr = array(
    'hassub' => 0, //是否有订阅信息（1：有 0：没有）
    'rows' => array()
);

if(!empty($sub_info)){ //有订阅信息
    $returnArr['hassub'] = 1;
    foreach($sub_info as $val){
        if($val['subtype'] == 3){
            $returnArr['rows'][] = array(
                'gameid' => intval($val['subid']),
                'gamename' => isset($game_arr[$val['subid']]['gi_name']) ? ($game_arr[$val['subid']]['gi_name']) : ''
            );
        }
    }
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);





