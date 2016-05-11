<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 视频用户行为统计
 * @file: video_user_behavior_count.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-23  16:44
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['id'] = intval(get_param('id'));//关联id（游戏id、戏分类id、视频id）
$mydata['type'] = intval(get_param('type'));//关联操作类型（1：点击游戏 2：点击游戏分类 3：下载视频）
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//参数判断
if(empty($mydata['id']) || !in_array($mydata['type'],array(1,2,3))){
    exit('type error');
}

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//检测统计记录是否已存在
$sql = "SELECT `id` FROM `video_user_behavior_count` WHERE `id` = ".intval($mydata['id'])." AND `id_type` = ".intval($mydata['type']).' LIMIT 1';
$check = $conn->find($sql);
if(isset($check[0]['id']) && !empty($check[0]['id'])){
    $sql = "UPDATE video_user_behavior_count SET `weight` = `weight` + 1 WHERE `id` = ".intval($mydata['id'])." AND `id_type` = ".intval($mydata['type']);
    $conn->query($sql);
}else{
    $arr = array(
        'id' => intval($mydata['id']),
        'id_type' => intval($mydata['type']),
        'weight' => 1
    );
    $conn->save('video_user_behavior_count',$arr);
}

$returnArr = array('code'=>200,'msg'=>'记录成功');
$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);
