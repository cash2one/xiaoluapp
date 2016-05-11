<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 视频观看超过一半
 * @file: video_watch_exceed_count.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-23  16:44
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['vid'] = intval(get_param('appid'));//视频ID
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key']) || empty($mydata['vid'])){
    exit('key error');
}

//检测视频是否已存在
$sql = "SELECT `vid` FROM `video_watch_exceed_count` WHERE `vid` = ".intval($mydata['vid']).' LIMIT 1';
$check = $conn->find($sql);
if(isset($check[0]['vid']) && !empty($check[0]['vid'])){
    $sql = "UPDATE video_watch_exceed_count SET `weight` = `weight` + 1 WHERE vid = ".intval($mydata['vid']);
    $conn->query($sql);
}else{
    $conn->save('video_watch_exceed_count',array('vid' => intval($mydata['vid']),'weight' => 1));
}

$returnArr = array('code'=>200,'msg'=>'记录成功');
$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);
