<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取视频主播详情游戏筛选列表
 * @file: video_user_filter_game.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-23  16:44
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['uid'] = intval(get_param('uid'));//当前登陆用户ID
$mydata['mac'] = get_param('mac');//用户mac地址
$mydata['imei'] = get_param('imei');//用户imei地址
$mydata['anchorid'] = intval(get_param('anchorid'));//主播id
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

//定义回转的默认参数
$returnArr = array(
    'rows' => array()
);

//获取主播有显示视频的游戏数组
$returnArr['rows'][] = array(
    'gameid' => 0,
    'title' => '全部'
);

$user_filter_game = "xl_user_filter_game_".$mydata['anchorid'];
$game_arr = $mem_obj->get($user_filter_game);
if($game_arr === false){
    $sql = "SELECT A.`vvl_game_id`,B.`gi_name` FROM `video_video_list` AS A LEFT JOIN `video_game_info` AS B ON A.`vvl_game_id` = B.`id`
            WHERE A.`va_isshow` = 1 AND A.`vvl_uid` = ".$mydata['anchorid']." AND A.`vvl_game_id` > 0 AND B.`gi_name` <> '' AND B.`gi_name` <> 'null'
            GROUP BY A.`vvl_game_id`";
    $game_arr = $conn->find($sql);
    $mem_obj->set($user_filter_game,$game_arr,3600);
}
if(!empty($game_arr)){
    foreach($game_arr as $val){
        $returnArr['rows'][] = array(
            'gameid' => intval($val['vvl_game_id']),
            'title' => $val['gi_name']
        );
    }
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);
