<?php
/**
 * @copyright: @快游戏 2015
 * @description: 根据游戏包名、渠道号获取不同的百度广告位信息
 * @file: get_baidu_vert_info.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2016-01-08  14:49
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['packagename'] = get_param('packagename'); //游戏包名
$mydata['channel'] = get_param('channel'); //渠道名称
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

$mem_obj = new kyx_memcache();

//默认下发广告位信息
$returnArr = array(
    'isopen' => 0, //是否开启 1：开启 0：关闭
    'appid' => '', //百度appid
    'id' => '' //百度id
);

$where = " WHERE (`abp_packagename` = '".$mydata['packagename']."' AND `abp_channel` = '".$mydata['channel']."')
           OR (`abp_packagename` = '".$mydata['packagename']."' AND `abp_channel` = '')
           OR (`abp_packagename` = '' AND `abp_channel` = '".$mydata['channel']."')
           OR (`abp_packagename` = '' AND `abp_channel` = '') ";

//后缀为_ad的渠道
if(substr($mydata['channel'],-3) == '_ad'){
    $where .= " OR (`abp_channel` = '_ad' && `abp_packagename` = '') ";
}

$baidu_vert_info_key = 'baidu_vert_info_key_'.$mydata['packagename'].$mydata['channel'];
$data = $mem_obj->get($baidu_vert_info_key);
if($data === false){
    $sql = "SELECT `abp_appid`,`abp_id`,`abp_status` FROM `mzw_baidu_position`".$where." ORDER BY `abp_weight` DESC LIMIT 1";
    $data = $conn->find($sql);
    $mem_obj->set($baidu_vert_info_key,$data,180);
}

//赋值
if(!empty($data)){
    $returnArr['isopen'] = ($data[0]['abp_status'] == 1) ? 1 : 0;
    $returnArr['appid'] = $data[0]['abp_appid'];
    $returnArr['id'] = $data[0]['abp_id'];
}

$str_encode = responseJson($returnArr);
exit($str_encode);
