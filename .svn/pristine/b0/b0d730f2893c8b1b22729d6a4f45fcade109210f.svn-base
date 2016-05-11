<?php
/**
 * @copyright: @快游戏 2014
 * @description: 获取专辑列表
 * @file: video_category_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-12-15  14:53
 * @version 1.0
 **/
include_once("../../config.inc.php");
include_once("../../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['pagenum'] = intval(get_param('pagenum'));//当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : intval($mydata['pagenum']);
$mydata['pagesize'] = intval(get_param('pagesize'));//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 20 : intval($mydata['pagesize']);
$mydata['gameid'] = intval(get_param('gameid'));//游戏ID
$mydata['pid'] = intval(get_param('pid'));//分类父级id
$mydata['typeid'] = intval(get_param('typeid'));//视频类型
$mydata['key'] = get_param('key'); //验证key

//key判断
$tmp_key = new_open_key_mzw();
if($mydata['key'] != $tmp_key){
    exit('key error');
}

//游戏id
if($mydata['gameid'] < 1){
	$mydata['gameid'] = 2;
}

//专辑类型
if($mydata['typeid'] < 1){
    $mydata['typeid'] = 4;
}

$mem_obj = new kyx_memcache();

$video_category_list_key = 'mzw_category_list_data_'.$mydata['gameid'].'_'.$mydata['typeid'].'_'.$mydata['pagenum'].'_'.$mydata['pagesize'].'_'.$mydata['pid']; //视频分类数据缓存key 'category_list_data_' + 游戏id + 分类类型id + 当前页 + 每页显示数据 + 父id
$returnArr = $mem_obj->get($video_category_list_key);

if($returnArr === false){
    //查询条件
    $where = " WHERE `vc_isshow` = 1 AND `vc_p_id` = ".$mydata['pid']." AND `vc_type_id` = ".$mydata['typeid'];

    //游戏筛选
    if($mydata['gameid'] == 2){
        $where .= " AND (vc_game_id = 12 OR vc_game_id = 2) ";
    }else{
        $where .= " AND `vc_game_id` = ".$mydata['gameid'];
    }

    //查询数据总数
    $sql = "SELECT count(1) as num FROM `video_category_info` ".$where;
    $data_count = $conn->count($sql);

    //最大页数
    $page_max = ceil($data_count/$mydata['pagesize']);

    //LIMIT条件
    $offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
    $limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

    //排序
    $orderby = " ORDER BY `vc_order` DESC,`id` DESC ";

    //定义回转的默认参数
    $returnArr = array(
        'total' => $data_count, //数据总条数
        'pagecount' => $page_max, //总页数
        'pagesize' => $mydata['pagesize'], //每页显示数据
        'pagenum' => $mydata['pagenum'], //当前页
        'rows' => array() //数据数组
    );

    //获取游戏专辑列表
    $sql = "SELECT `id`,`vc_name`,`vc_bicon`,`vc_game_id`,`in_date`,`vc_p_id`,`vc_intro` FROM `video_category_info` ".$where.$orderby.$limit;
    $data = $conn->find($sql);
    if(!empty($data)){
        foreach($data as $val){

            //专辑大图
            $tmp_img_url = !empty($val['vc_bicon']) ? (LOCAL_URL_DOWN_IMG.$val['vc_bicon']) : '';

            //判断是否有子级分类
            $rec_sql = "SELECT `id` FROM `video_category_info` WHERE `vc_isshow` = 1 AND `vc_p_id` = ".intval($val['id']).' LIMIT 1';
            $rec_data = $conn->get_one($rec_sql);
            $reclassify = isset($rec_data['id']) ? 1 : 0;

            $json = array(
                'categoryid' => intval($val['id']), //分类id
                'gameid' => intval($val['vc_game_id']), //专辑关联游戏id
                'typeid' => $mydata['typeid'], //分类类型（1任务，2解说，3赛事战况，4集锦）
                'title' => filter_search(delete_html($val['vc_name'])), //视频标题
                'desc' => filter_search(delete_html($val['vc_intro'])), //专辑描述
                'pid' => intval($val['id']), //父级id
                'reclassify' => $reclassify, //是否存在子级分类（1：存在 0：不存在）
                'imgurl' => $tmp_img_url, //视频图片
                'time' => intval($val['in_date']) //采集时间
            );
            $returnArr['rows'][] = $json;
        }
    }
    $mem_obj->set($video_category_list_key,$returnArr,1800);
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);

