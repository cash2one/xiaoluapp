<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取不能播视频状态
 * @file: update_video_status.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-22  10:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['appid'] = intval(get_param('appid')); //视频id
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

if(empty($mydata['appid'])){
    exit('appid empty!');
}

//更新信息
$update_arr = array(
    'id' => $mydata['appid'],
    'va_isshow' => 2
);
$conn->update('video_video_list',$update_arr);

$returnArr = array('code'=>200,'msg'=>'更新成功');
$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);





