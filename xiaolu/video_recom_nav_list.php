<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取编辑推荐导航列表
 * @file: video_recom_nav_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-22  10:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['uid'] = intval(get_param('uid')); //用户端id
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

$mem_obj = new kyx_memcache();
$data_key = "xl_recom_nav_list_".md5($mydata['uid'].$mydata['mac'].$mydata['imei']);
$returnArr = $mem_obj->get($data_key);
if($returnArr === false){
    //定义回转的默认参数
    $returnArr = array(
        'rows' => array()
    );

    //获取默认添加的新游预告专区
    $sql = "SELECT `rela_id`,`title` FROM `video_default_nav_info` WHERE `nav_type` = 3 AND `pos_type` = 3";
    $area = $conn->find($sql);
    if(!empty($area)){
        foreach($area as $aval){
            $returnArr['rows'][] = array(
                'id' => intval($aval['rela_id']),
                'title' => $aval['title'],
                'url' => URL_XIAOLU.'xiaolu/video_area_video_list.php?id='.$aval['rela_id'],
                'type' => 3
            );
        }
    }

    //添加最新导航（采用专区标识）
    $returnArr['rows'][] = array(
        'id' => -1,
        'title' => '最新',
        'url' => URL_XIAOLU.'xiaolu/video_area_video_list.php?id=-1',
        'type' => 3
    );

    //添加原创原创（采用专区标识）
    $returnArr['rows'][] = array(
        'id' => -2,
        'title' => '独家',
        'url' => URL_XIAOLU.'xiaolu/video_area_video_list.php?id=-2',
        'type' => 3
    );

    $mem_obj->set($data_key,$returnArr,600);
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);





