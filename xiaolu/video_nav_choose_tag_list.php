<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 首页启屏选择导航列表
 * @file: video_nav_choose_tag_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-22  10:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['uid'] = intval(get_param('uid')); //用户id
$mydata['mac'] = get_param('mac'); //用户mac地址
$mydata['imei'] = get_param('imei'); //用户imei地址
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//定义回转的默认参数
$returnArr = array(
    'rows' => array()
);

$sql = "SELECT `rela_id`,`title` FROM `video_default_nav_info` WHERE `nav_type` = 4 AND `status` = 1 AND `pos_type` = 1";
$data = $conn->find($sql);
if(!empty($data)){
    foreach($data as $val){
        $returnArr['rows'][] = array(
            'id' => intval($val['rela_id']),
            'title' => $val['title'],
            'type' => 4 //类型（1：游戏 2：游戏分类 3：专区 4:标签）
        );
    }
}

//过滤，6的倍数，不足6个不返回
$count = count($returnArr['rows']);
$basenum = $count%6;
$pagenum = $count/6;
if(!empty($basenum)){
    $start = $pagenum * 6;
    for($i = $start;$i < $count;$i++){
        unset($returnArr['rows'][$i]);
    }
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);





