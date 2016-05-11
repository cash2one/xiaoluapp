<?php
/**
 * @copyright: @快游戏 2014
 * @description: 获取谷歌安装器地址
 * @file: get_erector_info.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2014-12-22  14:55
 * @version 1.0
 **/
include_once("../config.inc.php");

/*参数*/
$mydata = array();
$mydata['brand'] = get_param('brand'); //品牌
$mydata['model'] = get_param('model'); //型号
$mydata['versioncode'] = get_param('versioncode'); //版本号
$mydata['cpu'] = intval(get_param('cpu')); //cpu（32或者64）
$mydata['key'] = get_param('key'); //验证KEY
$request = $_SERVER['REQUEST_METHOD']; //请求方式

//key校验
$key_auth = kyx_authorize_key($mydata['key'],$request);
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//初始化返回数组
$returnArr = array(
    'rows' => array(
        'url' => ''
    )
);

if($mydata['versioncode'] == '5.1' && $mydata['cpu'] == 32){
    $returnArr['rows']['url'] = 'http://letv.cdn.gugeanzhuangqi.com/game/2016/03/21/L_32.zip';
    $returnArr['rows']['size'] = 41796126;
    $returnArr['rows']['md5'] = 'ECE414F615465DB178351F71F235D9CA';
}elseif($mydata['versioncode'] == '5.1' && $mydata['cpu'] == 64){
    $returnArr['rows']['url'] = 'http://letv.cdn.gugeanzhuangqi.com/game/2016/03/21/L_64.zip';
    $returnArr['rows']['size'] = 44203203;
    $returnArr['rows']['md5'] = '80C0FAE135B0FF48FAB8AC74CF07A8C6';
}elseif($mydata['versioncode'] == '6.0' && $mydata['cpu'] == 32){
    $returnArr['rows']['url'] = 'http://letv.cdn.gugeanzhuangqi.com/game/2016/03/21/M_32.zip';
    $returnArr['rows']['size'] = 41890027;
    $returnArr['rows']['md5'] = '5513959BF569F16080567A9609C5BD48';
}elseif($mydata['versioncode'] == '6.0' && $mydata['cpu'] == 64){
    $returnArr['rows']['url'] = 'http://letv.cdn.gugeanzhuangqi.com/game/2016/03/21/M_64.zip';
    $returnArr['rows']['size'] = 44180246;
    $returnArr['rows']['md5'] = '7E7983F60DDC81F12F72EC7C6951A5CE';
}else{
    $returnArr['rows']['url'] = 'http://letv.cdn.gugeanzhuangqi.com/game/2015/12/18/5.1_r2_op.zip';
    $returnArr['rows']['size'] = 41984766;
    $returnArr['rows']['md5'] = 'e95f934ced68f8b60c26eca957ea961b';
}

exit(responseJson($returnArr,false));