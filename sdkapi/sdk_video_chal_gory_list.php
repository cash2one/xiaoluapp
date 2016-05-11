<?php
/**
 * @copyright: @快游戏 2014
 * @description: 获取视频标签分类列表
 * @file: sdk_video_chal_gory_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-11-25  14:37
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['pagenum'] = intval(get_param('pagenum'));//当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : intval($mydata['pagenum']);
$mydata['pagesize'] = intval(get_param('pagesize'));//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 20 : intval($mydata['pagesize']);
$mydata['packagename'] = get_param('packagename'); //游戏包名
$mydata['typeid'] = intval(get_param('typeid'));//获取列表类型（1：频道 2：标签分类）
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//游戏包名
if(empty($mydata['packagename'])){
    exit('error! packagename is empty!!');
}

$mem_obj = new kyx_memcache();

//获取包名对应游戏id
$game_id_key = 'game_id_package_key_'.$mydata['packagename'];
$game_id = $mem_obj->get($game_id_key);
if($game_id === false){
    $game_sql = "SELECT `id` FROM `video_game_info` WHERE `gi_packname` = '".$mydata['packagename']."'";
    $game_data = $conn->get_one($game_sql);
    $game_id = isset($game_data['id']) ? intval($game_data['id']) : 0;
    $mem_obj->set($game_id_key,$game_id,3600);
}

$sdk_video_chal_gory_list_key = 'sdk_video_chal_gory_list_'.$game_id.'_'.$mydata['typeid'].'_'.$mydata['pagenum'].'_'.$mydata['pagesize']; //视频频道标签分类数据缓存key 'sdk_video_chal_gory_list_' + 游戏id + 列表类型id + 当前页 + 每页显示数据
$returnArr = $mem_obj->get($sdk_video_chal_gory_list_key);

if($returnArr === false){
    //查询条件
    $where = " WHERE `vtc_status` = 1 AND `vtc_type` = ".$mydata['typeid']." AND `vtc_game_id` = ".$game_id;

    //查询数据总数
    $sql = "SELECT count(1) as num FROM `mzw_video_tags_category` ".$where ;
    $data_count = $conn->count($sql);

    //最大页数
    $page_max = ceil($data_count/$mydata['pagesize']);

    //LIMIT条件
    $offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
    $limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

    //排序
    $orderby = " ORDER BY `vtc_id` ASC ";

    //定义回转的默认参数
    $returnArr = array(
        'total' => $data_count, //数据总条数
        'pagecount' => $page_max, //总页数
        'pagesize' => $mydata['pagesize'], //每页显示数据
        'pagenum' => $mydata['pagenum'], //当前页
        'rows' => array() //数据数组
    );

    //获取视频频道专辑列表
    $sql = "SELECT `vtc_id`,`vtc_name` FROM `mzw_video_tags_category` ".$where.$orderby.$limit;
    $data = $conn->find($sql);

    //频道id还是标签分类id判断
    $return_key = ($mydata['typeid'] == 1) ? 'channelid' : 'categoryid';

    if(!empty($data)){
        foreach($data as $val){

            $json = array(
                'title' => $val['vtc_name'], //频道或标签分类名称
                $return_key => intval($val['vtc_id']), //频道或标签分类id
                'typeid' => $mydata['typeid'] //类型id
            );
            $returnArr['rows'][] = $json;
        }
    }
    $mem_obj->set($sdk_video_chal_gory_list_key,$returnArr,3600);
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);

