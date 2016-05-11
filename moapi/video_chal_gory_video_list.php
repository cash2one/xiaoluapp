<?php
/**
 * @copyright: @快游戏 2014
 * @description: 获取视频频道分类标签视频列表
 * @file: video_chal_gory_video_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-11-25  14:37
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");
include_once('../api/ucenter.config.inc.php');

/*参数*/
$mydata = array();
$mydata['pagenum'] = intval(get_param('pagenum'));//当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : intval($mydata['pagenum']);
$mydata['pagesize'] = intval(get_param('pagesize'));//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 20 : intval($mydata['pagesize']);
$mydata['gameid'] = intval(get_param('gameid'));//游戏ID
$mydata['channelid'] = intval(get_param('channelid'));//频道id
$mydata['categoryid'] = intval(get_param('categoryid'));//分类id
$mydata['tagid'] = intval(get_param('tagid'));//标签id
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

if($mydata['gameid'] < 1){
	$mydata['gameid'] = 2;
}

$mem_obj = new kyx_memcache();

//查询条件
$where = " WHERE B.`va_isshow` = 1 ";
if($mydata['gameid'] == 2){
    $where .= " AND (A.`game_id` = 12 OR A.`game_id` = 2) ";
}else{
    $where .= " AND A.`game_id` = ".$mydata['gameid'];
}

//频道
if(!empty($mydata['channelid'])){
    $where .= " AND A.`vtc_cha_id` = ".$mydata['channelid'];
}

//分类
if(!empty($mydata['categoryid'])){
    $where .= " AND A.`vtc_category_id` = ".$mydata['categoryid'];
}

//标签
if(!empty($mydata['tagid'])){
    $where .= " AND A.`vtc_tag_id` = ".$mydata['tagid'];
}

//查询数据总数
$video_chal_gory_video_list_count_key = 'video_chal_gory_video_list_count_'.md5($mydata['gameid'].'_'.$mydata['channelid'].'_'.$mydata['categoryid'].'_'.$mydata['tagid']); //频道分类标签视频总数缓存key  'video_chal_gory_video_list_count_' + md5(游戏id + 频道id + 分类id + 标签id)
$data_count = $mem_obj->get($video_chal_gory_video_list_count_key); //频道分类标签视频总数
if($data_count === false){
    $sql = "SELECT count(DISTINCT A.`v_id`) as num FROM `mzw_video_tag_mapping` AS A
            LEFT JOIN `video_video_list` AS B ON A.`v_id` = B.`id` ".$where;
    $data_count = $conn->count($sql);
    $mem_obj->set($video_chal_gory_video_list_count_key,$data_count,3600);
}

//最大页数
$page_max = ceil($data_count/$mydata['pagesize']);

//LIMIT条件
$offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
$limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

//排序
$orderby = " ORDER BY B.`in_date` DESC ";

//定义回转的默认参数
$returnArr = array(
    'total' => $data_count, //数据总条数
    'pagecount' => $page_max, //总页数
    'pagesize' => $mydata['pagesize'], //每页显示数据
    'pagenum' => $mydata['pagenum'], //当前页
    'rows' => array() //数据数组
);

//获取视频频道专辑标签视频列表
$video_chal_gory_video_list_key = 'video_chal_gory_video_list_'.md5($mydata['gameid'].'_'.$mydata['channelid'].'_'.$mydata['categoryid'].'_'.$mydata['tagid'].'_'.$mydata['pagenum'].'_'.$mydata['pagesize']); //视频频道标签分类数据缓存key 'video_chal_gory_video_list_' + md5(游戏id + 频道id + 分类id + 标签id + 当前页 + 每页显示数据)
$data = $mem_obj->get($video_chal_gory_video_list_key); //频道分类标签视频数据
if($data === false){
    $sql = "SELECT B.`id`,B.`vvl_title`,B.`vvl_imgurl`,B.`vvl_imgurl_get`,B.`vvl_category_id`,B.`vvl_type_id`,B.`vvl_author_id`,
            B.`in_date`,B.`vvl_tags`,B.`vvl_count`,B.`vvl_uid`,B.`vvl_game_id`,(SELECT `gi_name` FROM `video_game_info` WHERE `id` = B.`vvl_game_id`) AS gi_name
            FROM `mzw_video_tag_mapping` AS A LEFT JOIN `video_video_list` AS B ON A.`v_id` = B.`id` ".$where." GROUP BY A.`v_id` "
            .$orderby.$limit;
    $data = $conn->find($sql);
    //$mem_obj->set($video_chal_gory_video_list_key,$data,3600);
}else {

     $str_encode = responseJson($data,false);
     exit($str_encode);
}
if(!empty($data)){
    foreach($data as $val){

        //获取视频缓存播放次数
        $a_play_key = 'video_play_num_'.intval($val['id']); //视频播放key
        $a_old_play_val = $mem_obj->get($a_play_key); //获取视频原始播放数

        //视频图片
        $tmp_img_url = !empty($val['vvl_imgurl_get']) ? (LOCAL_URL_DOWN_IMG.$val['vvl_imgurl_get']) : $val['vvl_imgurl'];

        //视频标签
        $tag_data = array();
        if(!empty($val['vvl_tags'])){ //有设置标签
            $video_tag_name_key = 'video_tag_name_key_'.intval($val['vvl_tags']); //视频标签缓存key 'video_tag_name_key_' + 标签id
            $tag_data = $mem_obj->get($video_tag_name_key); //视频标签数组
            if($tag_data === false){
                $tag_sql = "SELECT `tag_name_cn`,`tag_colour` FROM `mzw_video_tags` WHERE `tag_id` = ".intval($val['vvl_tags']);
                $tag_data = $conn->get_one($tag_sql);
                $mem_obj->set($video_tag_name_key,$tag_data,3600);
            }
        }
        $tag_name = isset($tag_data['tag_name_cn']) ? $tag_data['tag_name_cn'] : '';
        $tag_colour = isset($tag_data['tag_colour']) ? $tag_data['tag_colour'] : '';

        //获取作者名称、头像
        $author_data = array();
        if(isset($val['vvl_uid']) && !empty($val['vvl_uid'])){
            if(!empty($val['vvl_uid'])){
                $author_data_key = 'user_data_'.$val['vvl_uid'];
                $author_data = $mem_obj->get($author_data_key);
                if($author_data === false){
                    $sql = "SELECT `nickname`,`source` FROM `uc_members` WHERE `uid` = ".intval($val['vvl_uid']);
                    $author_data = $uconn->get_one($sql);
                    $mem_obj->set($author_data_key,$author_data,3600);
                }
            }
            $author_name = isset($author_data['nickname']) ? $author_data['nickname'] : '网友';
            $author_img = UC_API.'/avatar.php?uid='.intval($val['vvl_uid']).'&type=real&size=big';
        }else{
            if(!empty($val['vvl_author_id'])){
                $author_data_key = 'author_data_'.$val['vvl_author_id'];
                $author_data = $mem_obj->get($author_data_key);
                if($author_data === false){
                    $sql = "SELECT `va_name`,`va_icon`,`va_icon_get` FROM `video_author_info` WHERE `va_isshow` = 1 AND `id` = ".intval($val['vvl_author_id']);
                    $author_data = $conn->get_one($sql);
                    $mem_obj->set($author_data_key,$author_data,3600);
                }
            }
            $author_name = isset($author_data['va_name']) ? $author_data['va_name'] : '网友';
            $author_img = (isset($author_data['va_icon_get']) && !empty($author_data['va_icon_get'])) ? (LOCAL_URL_DOWN_IMG.$author_data['va_icon_get']) : (isset($author_data['va_icon']) ? $author_data['va_icon'] : '');
        }

        $json = array(
            'appid' => intval($val['id']), //视频id
            'gameid' => isset($val['vvl_game_id']) ? intval($val['vvl_game_id']) : 0, //游戏id
            'gamename' => isset($val['gi_name']) ? $val['gi_name'] : '', //游戏名称
            'typeid' => intval($val['vvl_type_id']), //视频类型（1任务，2解说，3赛事战况，4集锦）
            'title' => filter_search(delete_html($val['vvl_title'])), //视频标题
            'playnum' => intval($val['vvl_count'])  + intval($a_old_play_val), //视频播放总数 + 缓存播放次数
            'authorname' => $author_name, //作者名称
            'authorimg' => $author_img, //作者头像
            'imgurl' => $tmp_img_url, //视频图片
            'tag' => $tag_name, //视频标签
            'tagcolour' => $tag_colour, //视频标签颜色
            'time' => date('Y-m-d H:i:s',$val['in_date']) //采集时间
        );
        $returnArr['rows'][] = $json;
    }
    $mem_obj->set($video_chal_gory_video_list_key, $returnArr, 3600);
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);

