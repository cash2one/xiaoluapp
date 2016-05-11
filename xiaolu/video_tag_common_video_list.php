<?php
/**
 * @copyright: @快游戏 2014
 * @description: 获取游戏标签、作者标签、标签视频列表
 * @file: video_tag_common_video_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-23  11:42
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");
include('../api/ucenter.config.inc.php');

/*参数*/
$mydata = array();
$mydata['uid'] = intval(get_param('uid'));//用户ID
$mydata['mac'] = get_param('mac');//用户mac地址
$mydata['imei'] = get_param('imei');//用户imei地址
$mydata['pagenum'] = intval(get_param('pagenum'));//当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : intval($mydata['pagenum']);
$mydata['pagesize'] = intval(get_param('pagesize'));//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 20 : intval($mydata['pagesize']);
$mydata['id'] = intval(get_param('id'));//关联id
$mydata['type'] = intval(get_param('type'));//id类型（1：标签 2：主播 3：游戏 4：游戏分类）
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//type类型判断
if(!in_array($mydata['type'],array(1,2,3,4))){
    exit('type error');
}

$mem_obj = new kyx_memcache();

$check_where = '';
if(!empty($mydata['mac']) && $mydata['mac'] <> '00:00:00:00:00:00'){
    $check_where .= " AND `mac` = '".$mydata['mac']."'";
}
if(!empty($mydata['imei'])){
    $check_where .= " AND `imei` = '".$mydata['imei']."'";
}

//查找用户订阅内容
$user_sub_info_key = 'user_sub_info_'.md5($mydata['uid'].$mydata['mac'].$mydata['imei']);
$sub_info = $mem_obj->get($user_sub_info_key);
if($sub_info === false){
    $sub_info = array();
    $sql = "SELECT `subid`,`subtype` FROM `video_user_sub_info` WHERE `status` = 1 ".$check_where;
    $data = $conn->find($sql);
    if(!empty($data)){
        foreach($data as $val){
            $sub_info[] = array(
                'subid' => intval($val['subid']),
                'subtype' => intval($val['subtype'])
            );
        }
    }
    $mem_obj->set($user_sub_info_key,$sub_info,600);
}

//查询用户订阅总数
$sql = "SELECT COUNT(1) AS num FROM `video_user_sub_info` WHERE `status` = 1 ".$check_where;
$sub_num = $conn->count($sql);
$is_max = ($sub_num >= 10) ? 1 : 0;

//获取所有游戏id => 名称数据
$all_game_arr_key = 'all_game_arr';
$game_arr = $mem_obj->get($all_game_arr_key);
if($game_arr === false){
    $sql = "SELECT `id`,`gi_name` FROM `video_game_info` WHERE `gi_isshow` = 1";
    $game_arr = $conn->find($sql,'id');
    $mem_obj->set($all_game_arr_key,$game_arr,14400);
}

//数据总数
$data_count_key = 'xl_tag_common_video_list_count_'.$mydata['id'].$mydata['type'];
$data_count = $mem_obj->get($data_count_key);
if($data_count === false){
    if($mydata['type'] == 1){ //标签
        $sql = "SELECT COUNT(DISTINCT `v_id`) AS num FROM `video_tag_mapping` WHERE `vtc_tag_id` = ".$mydata['id'];
    }elseif($mydata['type'] == 2){ //主播
        $sql = "SELECT COUNT(`id`) AS num FROM `video_video_list` WHERE `va_isshow` = 1 AND `vvl_uid` = ".$mydata['id'];
    }elseif($mydata['type'] == 3){ //游戏
        $sql = "SELECT COUNT(`id`) AS num FROM `video_video_list` WHERE `va_isshow` = 1 AND `vvl_game_id` = ".$mydata['id'];
    }elseif($mydata['type'] == 4){ //游戏分类
        $sql = "SELECT COUNT(DISTINCT B.`id`) as num FROM `video_game_info` AS A
                LEFT JOIN `video_video_list` AS B ON A.`id` = B.`vvl_game_id`
                WHERE B.`va_isshow` = 1 AND A.`gi_isshow` = 1 AND A.`gi_type_id` = ".$mydata['id'];
    }
    $data_count = $conn->count($sql);
    $mem_obj->set($data_count_key,$data_count,1800);
}

//最大页数
$page_max = intval(ceil($data_count/$mydata['pagesize']));

//LIMIT条件
$offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
$limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

//定义回转的默认参数
$returnArr = array(
    'total' => intval($data_count), //数据总条数
    'pagecount' => $page_max, //总页数
    'pagesize' => $mydata['pagesize'], //每页显示数据
    'pagenum' => $mydata['pagenum'], //当前页
    'subid' => intval($mydata['id']), //订阅id
    'subtype' => intval($mydata['type']), //订阅类型（1：标签 2：主播 3：游戏 4：游戏分类）
    'subscribe' => 0, //是否订阅（1：已订阅 0：未订阅）
    'ismax' => $is_max, //订阅数是否已达最大数（1：是 0：否）
    'rows' => array() //数据数组
);

//检查用户是否订阅该内容
if(!empty($sub_info)){
    foreach($sub_info as $sub){
        if($sub['subid'] == $mydata['id'] && $sub['subtype'] == $mydata['type']){
            $returnArr['subscribe'] = 1;
        }
    }
}

//数据总数
$data_key = 'xl_tag_common_video_list_data_'.md5($mydata['id'].$mydata['type'].$limit);
$data = $mem_obj->get($data_key);
if($data === false){
    if($mydata['type'] == 1){
        $sql = "SELECT DISTINCT B.`id`,B.`vvl_upload_time`,B.`vvl_title`,B.`vvl_imgurl`,B.`vvl_imgurl_get`,B.`vvl_category_id`,B.`vvl_type_id`,B.`vvl_author_id`,B.`vvl_sourcetype`,
                B.`in_date`,B.`vvl_tags`,B.`vvl_count`,B.`vvl_uid`,B.`vvl_video_id`,B.`vvl_game_id`,B.`vvl_game_id`,B.`vvl_time`,B.`vvl_up_num`,B.`vvl_down_num`
                FROM `video_tag_mapping` AS A LEFT JOIN `video_video_list` AS B ON A.`v_id` = B.`id`
                WHERE B.`va_isshow` = 1 AND A.`vtc_tag_id` = ".$mydata['id']." ORDER BY B.`in_date` DESC ".$limit;
    }elseif($mydata['type'] == 2){
        $sql = "SELECT `id`,`vvl_title`,`vvl_upload_time`,`vvl_imgurl`,`vvl_imgurl_get`,`vvl_category_id`,`vvl_type_id`,`vvl_author_id`,`in_date`,`vvl_tags`,`vvl_sourcetype`,
                `vvl_count`,`vvl_uid`,`vvl_game_id`,`vvl_game_id`,`vvl_video_id`,`vvl_time`,`vvl_up_num`,`vvl_down_num` FROM `video_video_list`
                WHERE `va_isshow` = 1 AND `vvl_uid` = ".$mydata['id']." ORDER BY `in_date` DESC ".$limit;
    }elseif($mydata['type'] == 3){
        $sql = "SELECT `id`,`vvl_title`,`vvl_upload_time`,`vvl_imgurl`,`vvl_imgurl_get`,`vvl_category_id`,`vvl_type_id`,`vvl_author_id`,`in_date`,`vvl_tags`,`vvl_sourcetype`,
                `vvl_count`,`vvl_uid`,`vvl_game_id`,`vvl_game_id`,`vvl_video_id`,`vvl_time`,`vvl_up_num`,`vvl_down_num` FROM `video_video_list`
                WHERE `va_isshow` = 1 AND `vvl_game_id` = ".$mydata['id']." ORDER BY `in_date` DESC ".$limit;
    }elseif($mydata['type'] == 4){
        $sql = "SELECT B.`id`,B.`vvl_title`,B.`vvl_upload_time`,B.`vvl_imgurl`,B.`vvl_imgurl_get`,B.`vvl_category_id`,B.`vvl_type_id`,B.`vvl_author_id`,B.`vvl_sourcetype`,
                B.`in_date`,B.`vvl_tags`,B.`vvl_count`,B.`vvl_uid`,B.`vvl_video_id`,B.`vvl_game_id`,B.`vvl_game_id`,B.`vvl_time`,B.`vvl_up_num`,B.`vvl_down_num`
                FROM `video_game_info` AS A LEFT JOIN `video_video_list` AS B ON A.`id` = B.`vvl_game_id`
                WHERE B.`va_isshow` = 1 AND A.`gi_isshow` = 1 AND A.`gi_type_id` = ".$mydata['id']." ORDER BY B.`in_date` DESC ".$limit;
    }
    $data = $conn->find($sql);
    $mem_obj->set($data_key,$data,1800);
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
            $author_img = (isset($author_data['va_icon_get']) && !empty($author_data['va_icon_get'])) ? (LOCAL_URL_DOWN_IMG.$author_data['va_icon_get']) : (isset($author_data['va_icon']) ? $author_data['va_icon'] : (UC_API.'/avatar.php?uid=&type=real&size=big'));
        }

        //获取用户点赞点踩关联视频数组
        $user_up_arr_key = "xl_user_up_arr_".md5($check_where);
        $user_down_arr_key = "xl_user_down_arr_".md5($check_where);
        $user_up_arr = $mem_obj->get($user_up_arr_key); //点赞数组
        $user_down_arr = $mem_obj->get($user_down_arr_key); //点踩数组
        if($user_up_arr ===  false || $user_down_arr === false){
            $user_up_arr = array(); //点赞数组
            $user_down_arr = array(); //点踩数组

            $sql = "SELECT `v_id`,`oper_type` FROM `video_up_down` WHERE 1 ".$check_where;
            $user_ud_data = $conn->find($sql);

            if(!empty($user_ud_data)){
                foreach($user_ud_data as $ud_val){
                    if($ud_val['oper_type'] == 1){
                        $user_up_arr[] = intval($ud_val['v_id']);
                    }elseif($ud_val['oper_type'] == 2){
                        $user_down_arr[] = intval($ud_val['v_id']);
                    }
                }
            }

            //设置缓存
            $mem_obj->set($user_up_arr_key,$user_up_arr,3600);
            $mem_obj->set($user_down_arr_key,$user_down_arr,3600);
        }

        //判断用户是否点赞点踩
        $has_up = 0;
        $has_down = 0;
        if(in_array($val['id'],$user_up_arr)){
            $has_up = 1;
        }elseif(in_array($val['id'],$user_down_arr)){
            $has_down = 1;
        }

        //获取视频点赞点踩数
        $video_up_down_key = 'video_up_num_data_key_'.$val['id'];
        $video_up_down = $mem_obj->get($video_up_down_key);
        if($video_up_down === false){
            $sql = "SELECT vvl_up_num,vvl_down_num FROM `video_video_list` WHERE `id` = ".$val['id'];
            $video_up_down = $conn->find($sql);
            $mem_obj->set($video_up_down_key,$video_up_down,1800);
        }
        $up_num = intval($video_up_down[0]['vvl_up_num']);
        $down_num = intval($video_up_down[0]['vvl_down_num']);

        $json = array(
            'appid' => intval($val['id']), //视频id
            'title' => filter_search(delete_html($val['vvl_title'])), //视频标题
            'source' => isset($val['vvl_sourcetype']) ? (isset($GLOBALS['SOURCE_ARR'][$val['vvl_sourcetype']]) ? $GLOBALS['SOURCE_ARR'][$val['vvl_sourcetype']] : '') : '',
            'sourcetype' => isset($val['vvl_sourcetype']) ? intval($val['vvl_sourcetype']) : 0,
            'videoid' => isset($val['vvl_video_id']) ? $val['vvl_video_id'] : '',
            'imgurl' => $tmp_img_url, //视频图片
            'gamename' => isset($game_arr[$val['vvl_game_id']]['gi_name']) ? $game_arr[$val['vvl_game_id']]['gi_name'] : '', //游戏名称
            'gameid' => isset($val['vvl_game_id']) ? intval($val['vvl_game_id']) : 0, //游戏id
            'id' => intval($val['vvl_game_id']), //导航关联id
            'type' => 1, //导航类型（1：游戏 2：游戏分类）
            'anchorid' => intval($val['vvl_uid']), //主播id
            'authorname' => $author_name, //作者名称
            'authorimg' => $author_img, //作者头像
            'duration' => strstr($val['vvl_time'],':') ? $val['vvl_time'] : '',
            'playnum' => intval($val['vvl_count'])  + intval($a_old_play_val), //视频播放总数 + 缓存播放次数
            'tag' => $tag_name, //视频标签
            'tagcolour' => $tag_colour, //视频标签颜色
            'likenum' => $up_num, //视频点赞数
            'unlikenum' => $down_num, //视频点踩数
            'haslike' => $has_up, //是否点赞过该视频 1：是 0：否
            'hasunlike' => $has_down, //是否点踩过该视频 1：是 0：否
            'timestamp' => isset($val['vvl_upload_time']) ? $val['vvl_upload_time'] : 0, //时间戳
            'time' => isset($val['vvl_upload_time']) ? (empty($val['vvl_upload_time']) ? '' : date('Y-m-d',$val['vvl_upload_time'])) : date('Y-m-d',$val['in_date']) //采集时间
        );
        $returnArr['rows'][] = $json;
    }
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);

