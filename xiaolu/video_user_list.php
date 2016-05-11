<?php
/**
 * @copyright: @快游戏 2014
 * @description: 获取主播列表
 * @file: video_user_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-23  11:42
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");
include('../api/ucenter.config.inc.php');

/*参数*/
$mydata = array();
$mydata['uid'] = intval(get_param('uid'));//用户ID
$mydata['mac'] = get_param('mac');//用户mac地址
$mydata['imei'] = get_param('imei');//用户imei地址
$mydata['pagenum'] = intval(get_param('pagenum'));//当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : intval($mydata['pagenum']);
$mydata['pagesize'] = intval(get_param('pagesize'));//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 20 : intval($mydata['pagesize']);
$mydata['relaid'] = intval(get_param('relaid'));//关联id
$mydata['relatype'] = intval(get_param('relatype'));//关联id
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

//查找用户订阅内容
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

if($mydata['relatype'] == 2){ //游戏分类主播
    $where = " WHERE FIND_IN_SET(".$mydata['relaid'].",`video_game_type`) > 0 AND `video_num` > 0 AND `is_show` = 1 AND (`source` = 2 OR `source` = 3 OR `source` = 4) ";
}else{ //游戏主播
    $where = " WHERE FIND_IN_SET(".$mydata['relaid'].",`video_game`) > 0 AND `video_num` > 0 AND `is_show` = 1 AND (`source` = 2 OR `source` = 3 OR `source` = 4) ";
}

//查询数据总数
$data_count_key = "xl_user_list_".md5($where);
$data_count = $mem_obj->get($data_count_key);
if($data_count === false){
    $sql = "SELECT count(1) as num FROM `uc_members` ".$where;
    $data_count = $uconn->count($sql);
    $mem_obj->set($data_count_key,$data_count,1800);
}

//最大页数
$page_max = ceil($data_count/$mydata['pagesize']);

//LIMIT条件
$offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
$limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

//排序
$orderby = " ORDER BY `xl_recommed` DESC,`video_num` DESC ";

//定义回转的默认参数
$returnArr = array(
    'total' => $data_count, //数据总条数
    'pagecount' => $page_max, //总页数
    'pagesize' => $mydata['pagesize'], //每页显示数据
    'pagenum' => $mydata['pagenum'], //当前页
    'rows' => array() //数据数组
);

//获取解说列表
$data_key = "xl_user_list_".md5($where.$orderby.$limit);
$data = $mem_obj->get($data_key);
if($data === false){
    $sql = "SELECT `uid`,`nickname`,`gender`,`desc`,`source` FROM `uc_members` ".$where.$orderby.$limit;
    $data = $uconn->find($sql);
    $mem_obj->set($data_key,$data,1800);
}
if(!empty($data)){
    foreach($data as $val){

        $user_file_md5 = "user_file_md5_".$val['uid'];
        $md5file = $mem_obj->get($user_file_md5);
        if($md5file === false){
            //生产环境获取大头像md5的接口
            $get_img_url = UC_API . '/api/get_avatar_md5file.php';
            $arr_img = array('uid' => $val['uid']);

            //调用ucenter的头像处理接口
            $json = curl_post($get_img_url,$arr_img);
            $arr_return = json_decode($json,TRUE);

            $md5file = isset($arr_return['md5file']) ? $arr_return['md5file'] : '';
            $mem_obj->set($user_file_md5,$md5file,3600);
        }

        //检查用户是否订阅该主播
        $subscribe = 0;
        if(!empty($sub_info)){
            foreach($sub_info as $sub){
                if($sub['subid'] == $val['uid'] && $sub['subtype'] == 2){
                    $subscribe = 1;
                }
            }
        }

        $json = array(
            'anchorid' => intval($val['uid']), //主播id
            'authorname' => $val['nickname'], //用户昵称
            'gender' => intval($val['gender']), //用户性别（1：男 2：女 3：未知）
            'subid' => intval($val['uid']), //订阅id
            'subtype' => 2, //订阅类型（1：标签 2：主播 3：游戏 4：游戏分类）
            'subscribe' => $subscribe, //是否订阅（1：已订阅 0：未订阅）
            'authordesc' => $val['desc'], //用户描述
            'authorimg' => UC_API.'/avatar.php?uid='.intval($val['uid']).'&type=real&size=big&md5file='.$md5file, //用户大头像
            'md5file' => $md5file //获取ucenter中心的大图md5值
        );
        $returnArr['rows'][] = $json;
    }
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);

