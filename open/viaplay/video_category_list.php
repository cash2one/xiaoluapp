<?php
/**
 * @copyright: @快游戏 2014
 * @description: 获取专辑列表
 * @file: video_category_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-23  11:42
 * @version 1.0
 **/
include_once("../../config.inc.php");
include_once("../../db.config.inc.php");
include_once("../../config.open.api.php");

/*参数*/
$mydata = array();
$mydata['pagenum'] = intval(get_param('pagenum'));//当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : intval($mydata['pagenum']);
$mydata['pagesize'] = intval(get_param('pagesize'));//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 20 : intval($mydata['pagesize']);
$mydata['gameid'] = intval(get_param('gameid'));//游戏ID
$mydata['typeid'] = intval(get_param('typeid'));//视频类型

if($mydata['gameid'] < 1){
	$mydata['gameid'] = 2; //我的世界
}

if($mydata['typeid'] < 1){
    $mydata['typeid'] = 4; //集锦
}

//key判断
$p_key = get_param('key');
$tmp_key = new_open_key_kyx();
if($p_key != $tmp_key){
    exit('key error');
}

$mem_obj = new kyx_memcache();
$via_video_category_list_key = 'via_video_category_list_data_'.md5($mydata['gameid'].$mydata['typeid'].$mydata['pagenum'].$mydata['pagesize']); //开放平台视频分类数据缓存key 'via_video_category_list_data_' + md5(游戏id.分类类型id.当前页每页显示数据)
$returnArr = $mem_obj->get($via_video_category_list_key);

if($returnArr === false){
    //查询条件
    $where = " WHERE `vc_recommend` = 1 AND `vc_isshow` = 1 AND `vc_type_id` = ".$mydata['typeid']." AND vc_game_id = ".$mydata['gameid'];

    //查询数据总数
    $sql = "SELECT count(1) as num FROM `video_category_info` ".$where;
    $data_count = $conn->count($sql);

    //最大页数
    $page_max = ceil($data_count/$mydata['pagesize']);

    //LIMIT条件
    $offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
    $limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

    //排序
    $orderby = " ORDER BY `vc_update_time` DESC ";


    //定义回转的默认参数
    $returnArr = array(
        'total' => $data_count, //数据总条数
        'pagecount' => $page_max, //总页数
        'pagesize' => $mydata['pagesize'], //每页显示数据
        'pagenum' => $mydata['pagenum'], //当前页
        'rows' => array() //数据数组
    );

    //获取游戏专辑列表
    $sql = "SELECT `id`,`vc_name`,`vc_game_id`,`vc_icon_get`,`vc_bicon`,`vc_update_time`,`in_date` FROM `video_category_info` ".$where.$orderby.$limit;
    $data = $conn->find($sql);
    if(!empty($data)){
        foreach($data as $val){

            //专辑图标
            $category_ico = !empty($val['vc_icon_get']) ? (LOCAL_URL_DOWN_IMG.$val['vc_icon_get']) : '';

            //专辑大图
            $category_img = !empty($val['vc_bicon']) ? (LOCAL_URL_DOWN_IMG.$val['vc_bicon']) : '';

            $json = array(
                'categoryid' => intval($val['id']), //专辑id
                'typeid' => intval($mydata['typeid']), //分类类型（1任务，2解说，3赛事战况，4集锦）
                'title' => filter_search(delete_html($val['vc_name'])), //专辑标题
                'gameid' => intval($val['vc_game_id']), //游戏id
                'icourl' => $category_ico, //专辑ico图标
                'imgurl' => $category_img, //专辑大图
                'time' => intval($val['in_date']), //采集时间
                'updatetime' => intval($val['vc_update_time']) //更新时间
            );
            $returnArr['rows'][] = $json;
        }
    }
    $mem_obj->set($via_video_category_list_key,$returnArr,1800);
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);

