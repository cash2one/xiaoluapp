<?php
/**
 * @copyright: @快游戏 2014
 * @description: 根据游戏获取总视频数跟总播放数
 * @file: video_get_game_count.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-12-15  14:54
 * @version 1.0
 **/
include_once("../../config.inc.php");
include_once("../../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['gameids'] = get_param('gameids');//游戏ID，多个用，号隔开
$mydata['key'] = get_param('key'); //验证key

//key判断
$tmp_key = new_open_key_mzw();
if($mydata['key'] != $tmp_key){
    exit('key error');
}

//游戏id
if(empty($mydata['gameids'])){
	$mydata['gameids'] = 2;
}

$mem_obj = new kyx_memcache();

$video_game_count_key = 'mzw_video_game_count_data_'.$mydata['gameids']; //视频分类数据缓存key 'category_list_data_' + 游戏id + 分类类型id + 当前页 + 每页显示数据 + 版本 + 父id
$returnArr = $mem_obj->get($video_game_count_key);

if($returnArr === false){
    //查询条件
    $where = " WHERE `va_isshow` = 1 AND `vvl_game_id` > 0 AND `vvl_game_id` IN (".$mydata['gameids'].")";

    //定义回转的默认参数
    $returnArr = array(
        'rows' => array() //数据数组
    );

    //获取游戏视频播放总数跟视频总数
    $sql = "SELECT `vvl_game_id`,COUNT(1) AS num,SUM(`vvl_count`) AS pnum FROM `video_video_list` ".$where." GROUP BY `vvl_game_id`";
    $data = $conn->find($sql);
    if(!empty($data)){
        foreach($data as $val){
            $returnArr['rows'][] = array(
                'gameid' => intval($val['vvl_game_id']), //游戏id
                'playnum' => intval($val['pnum']), //播放总数
                'videonum' => intval($val['num']) //视频总数
            );
        }
    }
    $mem_obj->set($video_game_count_key,$returnArr,3600);
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);

