<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 用户订阅列表
 * @file: video_user_sub_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2016-01-19  14:43
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");
include_once('../api/ucenter.config.inc.php');

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
                'subtype' => intval($val['subtype'])
            );
        }
    }
    $mem_obj->set($user_sub_info_key,$sub_info,600);
}

//定义回转的默认参数
$returnArr = array(
    'total' => 0,
    'rows' => array()
);

if(!empty($sub_info)){ //有订阅信息

    //获取游戏关联名字缓存数据
    $all_game_arr_key = 'all_game_arr';
    $game_arr = $mem_obj->get($all_game_arr_key);
    if($game_arr === false){
        $sql = "SELECT `id`,`gi_name` FROM `video_game_info` WHERE `gi_isshow` = 1";
        $game_arr = $conn->find($sql,'id');
        $mem_obj->set($all_game_arr_key,$game_arr,14400);
    }

    //获取分类关联名字缓存数据
    $game_type_id_name_all_key = 'game_type_id_name_all';
    $game_type_id_name_all = $mem_obj->get($game_type_id_name_all_key);
    if($game_type_id_name_all === false){
        $sql = "SELECT `t_id`,`t_name_cn` FROM `video_game_type` WHERE `t_status` = 1";
        $data = $conn->find($sql);
        $game_type_id_name_all = array();
        if(!empty($data)){
            foreach($data as $val){
                $game_type_id_name_all[$val['t_id']] = $val['t_name_cn'];
            }
        }
        unset($data);
        $mem_obj->set($game_type_id_name_all_key,$game_type_id_name_all,14400);
    }

    //获取标签信息数组
    $tag_arr = array();
    foreach($sub_info as $val){
        $temp_tag_id_arr = array();
        if($val['subtype'] == 1){
            $temp_tag_id_arr[] = intval($val['subid']);
        }

        //获取用户已订阅标签数组
        if(!empty($temp_tag_id_arr)){
            $tag_id_str = implode(',',$temp_tag_id_arr);
            $sql = "SELECT `vtc_id`,`vtc_name` FROM `video_game_tags` WHERE `vtc_type` = 2 AND `vtc_status` = 1 AND `vtc_id` IN (".$tag_id_str.")";
            $data = $conn->find($sql);
            if(!empty($data)){
                foreach($data as $val){
                    $tag_arr[$val['vtc_id']] = $val['vtc_name'];
                }
            }
        }
    }

    //获取主播信息数组
    $user_arr = array();
    foreach($sub_info as $val){
        $temp_user_id_arr = array();
        if($val['subtype'] == 2){
            $temp_user_id_arr[] = intval($val['subid']);
        }

        //获取用户已订阅主播数组
        if(!empty($temp_user_id_arr)){
            $user_id_str = implode(',',$temp_user_id_arr);
            $sql = "SELECT `uid`,`nickname` FROM `uc_members` WHERE `uid` IN (".$user_id_str.")";
            $data = $uconn->find($sql);
            if(!empty($data)){
                foreach($data as $val){
                    $user_arr[$val['uid']] = $val['nickname'];
                }
            }
        }
    }


    //将游戏、游戏分类、主播、标签分类信息组装
    foreach($sub_info as $key => $val){
        if($val['subtype'] == 1){ //标签
            $title = isset($tag_arr[$val['subid']]) ? ('标签：'.$tag_arr[$val['subid']]) : '';
        }elseif($val['subtype'] == 2){ //主播
            $title = isset($user_arr[$val['subid']]) ? ('主播：'.$user_arr[$val['subid']]) : '';
        }elseif($val['subtype'] == 3){ //游戏
            $title = isset($game_arr[$val['subid']]['gi_name']) ? ('游戏：'.$game_arr[$val['subid']]['gi_name']) : '';
        }elseif($val['subtype'] == 4){ //游戏分类
            $title = isset($game_type_id_name_all[$val['subid']]) ? ('分类：'.$game_type_id_name_all[$val['subid']]) : '';
        }
        $sub_info[$key]['title'] = $title;
    }

    $returnArr['total'] = count($sub_info);
    $returnArr['rows'] = $sub_info;
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);





