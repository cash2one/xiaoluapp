<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取小鹿视频游戏分类导航列表
 * @file: video_game_nav.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-11-11  16:22
 * @version 1.0
 **/
include("../config.inc.php");
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");
include('../api/ucenter.config.inc.php');

/*参数*/
$mydata = array();
$mydata['uid'] = intval(get_param('uid'));//用户ID
$mydata['mac'] = get_param('mac');//用户mac地址
$mydata['imei'] = get_param('imei');//用户imei地址
$mydata['id'] = intval(get_param('id'));  //关联游戏id
$mydata['type'] = intval(get_param('type')); //导航类型（1：游戏 2：游戏分类）
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
    'title' => '', //游戏或分类标题
    'desc' => '', //游戏或分类描述
    'subid' => 0, //订阅id
    'gameid' => 0, //游戏id
    'subtype' => 3, //订阅类型（1：标签 2：主播 3：游戏 4：游戏分类）
    'subscribe' => 0, //是否订阅（1：已订阅 0：未订阅）
    'bgimage' => '', //游戏或分类背景图
    'rows' => array() //导航数据数组
);

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

//检查用户是否订阅该游戏或游戏分类内容
$check_type = ($mydata['type'] == 1) ? 3 : 4;
if(!empty($sub_info)){
    foreach($sub_info as $val){
        if($val['subid'] == $mydata['id'] && $val['subtype'] == $check_type){
            $returnArr['subscribe'] = 1;
        }
    }
}

//获取标签列表
$default_tag = array('攻略','竞技','搞笑');

if(!empty($mydata['id']) && $mydata['type'] == 1){ //游戏

    //获取游戏信息
    $game_info_key = "xl_game_nav_gi_".$mydata['id'];
    $data = $mem_obj->get($game_info_key);
    if($data === false){
        $sql = "SELECT `id`,`gi_name`,`gi_intro`,`gi_bg_img` FROM `video_game_info` WHERE `id` = ".$mydata['id'];
        $data = $conn->find($sql);
        $mem_obj->set($game_info_key,$data,600);
    }
    if(!empty($data)){
        $returnArr['title'] = $data[0]['gi_name'];
        $returnArr['desc'] = $data[0]['gi_intro'];
        $returnArr['subid'] = intval($data[0]['id']);
        $returnArr['gameid'] = intval($data[0]['id']);
        $returnArr['bgimage'] = empty($data[0]['gi_bg_img']) ? '' : (LOCAL_URL_DOWN_IMG.$data[0]['gi_bg_img']);
    }

    //获取游戏下各栏目导航
    $temp_arr = array();

//    //获取游戏下全部视频数
//    $game_video_count_key = "xl_game_nav_gvc_".$mydata['id'];
//    $count = $mem_obj->get($game_video_count_key);
//    if($count === false){
//        $sql = "SELECT COUNT(1) AS num FROM `video_video_list` WHERE `va_isshow` = 1 AND `vvl_game_id` = ".$mydata['id'];
//        $count = $conn->count($sql);
//        $mem_obj->set($game_video_count_key,$count,700);
//    }
//    $temp_arr[] = array(
//        'relaid' => $mydata['id'], //关联id
//        'relatype' => 1, //关联类型（1：游戏 2：游戏分类）
//        'title' => '全部',
//        'type' => 1, //类型（1：全部 2：标签 3：用户 4：专题）
//        'total' => intval($count)
//    );

//    //获取特定标签下视频数
//    $game_tag_data_key = "xl_game_nav_gtd_".$mydata['id'];
//    $data = $mem_obj->get($game_tag_data_key);
//    if($data === false){
//        $temp_where = '';
//        foreach($default_tag as $val){
//            $temp_where .= " `vtc_name` = '".$val."' OR";
//        }
//        $temp_where = rtrim($temp_where,'OR');
//
//        $sql = "SELECT `vtc_id`,`vtc_name` FROM `video_game_tags`
//                WHERE `vtc_type` = 2 AND `vtc_status` = 1 AND (".$temp_where.")";
//        $data = $conn->find($sql);
//
//        $mem_obj->set($game_tag_data_key,$data,800);
//    }
//    if(!empty($data)){
//        foreach($data as $val){
//            //获取标签下视频数
//            $game_tag_count_key = "xl_game_nav_gtc_".$mydata['id'].$val['vtc_id'];
//            $count = $mem_obj->get($game_tag_count_key);
//            if($count === false){
//                $sql = "SELECT COUNT(DISTINCT `v_id`) AS num FROM `video_tag_mapping` WHERE `vtc_tag_id` = ".$val['vtc_id'];
//                $count = $conn->count($sql);
//                $mem_obj->set($game_tag_count_key,$count,3600);
//            }
//            if(!empty($count)){
//                $temp_arr[] = array(
//                    'ids' => $val['vtc_id'], //关联id
//                    'relaid' => $mydata['id'], //关联id
//                    'relatype' => 1, //关联类型（1：游戏 2：游戏分类）
//                    'title' => $val['vtc_name'],
//                    'type' => 2, //类型（1：全部 2：标签 3：用户 4：专题）
//                    'total' => intval($count)
//                );
//            }
//        }
//    }

    //获取游戏下主播数
    $game_user_count_key = "xl_game_nav_guc_".$mydata['id'];
    $count = $mem_obj->get($game_user_count_key);
    if($count === false){
        $sql = "SELECT COUNT(1) AS num FROM `uc_members` WHERE FIND_IN_SET(".$mydata['id'].",`video_game`) > 0 AND `video_num` > 0 AND `is_show` = 1 AND (`source` = 2 OR `source` = 3 OR `source` = 4) ";
        $count = $uconn->count($sql);
        $mem_obj->set($game_user_count_key,$count,3600);
    }
    $temp_arr[] = array(
        'relaid' => $mydata['id'], //关联id
        'relatype' => 1, //关联类型（1：游戏 2：游戏分类）
        'title' => '主播',
        'type' => 3, //类型（1：全部 2：标签 3：用户 4：专题）
        'total' => intval($count)
    );

    //获取游戏专题数
    $game_album_count_key = "xl_game_nav_gac_".$mydata['id'];
    $count = $mem_obj->get($game_album_count_key);
    if($count === false){
        $sql = "SELECT COUNT(1) AS num FROM `video_category_info` WHERE `vc_type_id` = 6 AND `vc_isshow` = 1 AND `vc_p_id` = 0 AND `vc_game_id` = ".$mydata['id'];
        $count = $conn->count($sql);
        $mem_obj->set($game_album_count_key,$count,3600);
    }

    if(!empty($count)){
        $temp_arr[] = array(
            'relaid' => $mydata['id'], //关联id
            'relatype' => 1, //关联类型（1：游戏 2：游戏分类）
            'title' => '专题',
            'type' => 4, //类型（1：全部 2：标签 3：用户 4：专题）
            'total' => intval($count)
        );
    }

    $returnArr['rows'] = $temp_arr;

}elseif(!empty($mydata['id']) && $mydata['type'] == 2){ //游戏分类

    //获取分类信息
    $data_type_key = "xl_game_nav_gt_".$mydata['id'];
    $data = $mem_obj->get($data_type_key);
    if($data === false){
        $sql = "SELECT `t_id`,`t_name_cn`,`t_img`,`t_desc` FROM `video_game_type` WHERE `t_status` = 1 AND `t_id` = ".$mydata['id'];
        $data = $conn->find($sql);
        $mem_obj->set($data_type_key,$data,600);
    }
    if(!empty($data)){
        $returnArr['title'] = $data[0]['t_name_cn'];
        $returnArr['desc'] = $data[0]['t_desc'];
        $returnArr['subtype'] = 4;
        $returnArr['subid'] = intval($data[0]['t_id']);
        $returnArr['bgimage'] = empty($data[0]['t_img']) ? '' : (LOCAL_URL_DOWN_IMG.$data[0]['t_img']);;
    }

    //获取分类下各栏目导航
    $temp_arr = array();

//    //获取分类视频视频数
//    $type_video_count_key = "xl_gamz_nav_tvc_".$mydata['id'];
//    $count = $mem_obj->get($type_video_count_key);
//    if($count === false){
//        $sql = "SELECT COUNT(1) AS num FROM `video_game_info` AS A LEFT JOIN `video_video_list` AS B ON B.`vvl_game_id` = A.`id`
//                WHERE B.`va_isshow` = 1 AND A.`gi_isshow` = 1 AND A.`gi_type_id` = ".$mydata['id'];
//        $count = $conn->count($sql);
//        $mem_obj->set($type_video_count_key,$count,700);
//    }
//    $temp_arr[] = array(
//        'relaid' => $mydata['id'], //关联id
//        'relatype' => 2, //关联类型（1：游戏 2：游戏分类）
//        'title' => '全部',
//        'type' => 1, //类型（1：全部 2：标签 3：用户 4：专题）
//        'total' => intval($count)
//    );

//    //获取特定标签下视频数
//    $type_tag_data_key = "xl_game_nav_ttd_".$mydata['id'];
//    $tag_data = $mem_obj->get($type_tag_data_key);
//    if($data === false){
//        $temp_where = '';
//        foreach($default_tag as $val){
//            $temp_where .= " `vtc_name` = '".$val."' OR";
//        }
//        $temp_where = rtrim($temp_where,'OR');
//        $sql = "SELECT `vtc_id`,`vtc_name`,`vtc_game_id` FROM `mzw_video_tags_category`
//                WHERE `vtc_status` = 1 AND `vtc_type` = 3 AND (".$temp_where.")";
//        $tag_data = $conn->find($sql);
//
//        $mem_obj->set($type_tag_data_key,$tag_data,800);
//    }
//
//    //获取游戏分类关联多有游戏列表
//    $type_all_gt_key = "xl_game_nav_tagk_".$mydata['id'];
//    $all_type_game = $mem_obj->get($type_all_gt_key);
//    if($all_type_game === false){
//        $all_type_game = array();
//        $sql = "SELECT `id` FROM `video_game_info` WHERE `gi_type_id` = ".$mydata['id'];
//        $data = $conn->find($sql);
//        if(!empty($data)){
//            foreach($data as $val){
//                $all_type_game[] = intval($val['id']);
//            }
//        }
//        $mem_obj->set($type_all_gt_key,$all_type_game,900);
//    }
//
//    //过滤在游戏分类游戏内的标签
//    $temp_tag_arr = array();
//    if(!empty($tag_data)){
//        foreach($tag_data as $val){
//            if(in_array($val['vtc_game_id'],$all_type_game)){
//                $temp_tag_arr[$val['vtc_name']][] = $val['vtc_id'];
//            }
//        }
//    }
//
//    //获取标签下视频数
//    if(!empty($temp_tag_arr)){
//        foreach($temp_tag_arr as $key => $val){
//            $tag_in_where = implode(',',$val);
//            $type_tag_video_key = "xl_game_nav_ttv_".md5($tag_in_where);
//            $count = $mem_obj->get($type_tag_video_key);
//            if($count === false){
//                $sql = "SELECT COUNT(DISTINCT `v_id`) AS num FROM `mzw_video_tag_mapping` WHERE `vtc_tag_id` IN (".$tag_in_where.")";
//                $count = $conn->count($sql);
//                $mem_obj->set($type_tag_video_key,$count,1000);
//            }
//            if(!empty($count)){
//                $temp_arr[] = array(
//                    'ids' => $tag_in_where, //关联id
//                    'relaid' => $mydata['id'], //关联id
//                    'relatype' => 2, //关联类型（1：游戏 2：游戏分类）
//                    'title' => $key,
//                    'type' => 2, //类型（1：全部 2：标签 3：用户 4：专题）
//                    'total' => intval($count)
//                );
//            }
//        }
//    }

    //获取分类下主播数
    $type_user_count_key = "xl_game_nav_tuc_".$mydata['id'];
    $count = $mem_obj->get($type_user_count_key);
    if($count === false){
        $sql = "SELECT COUNT(1) AS num FROM `uc_members` WHERE FIND_IN_SET(".$mydata['id'].",`video_game_type`) > 0 AND `video_num` > 0 AND `is_show` = 1 AND (`source` = 2 OR `source` = 3 OR `source` = 4) ";
        $count = $uconn->count($sql);
        $mem_obj->set($type_user_count_key,$count,3600);
    }
    $temp_arr[] = array(
        'relaid' => $mydata['id'], //关联id
        'relatype' => 2, //关联类型（1：游戏 2：游戏分类）
        'title' => '主播',
        'type' => 3, //类型（1：全部 2：标签 3：用户 4：专题）
        'total' => intval($count)
    );

    //获取分类下的专辑数
    $type_album_count_key = "xl_game_nav_tac_".$mydata['id'];
    $count = $mem_obj->get($type_album_count_key);
    if($count === false){
        $sql = "SELECT COUNT(1) AS num FROM `video_category_info` AS A LEFT JOIN `video_game_info` AS B ON A.`vc_game_id` = B.`id`
                WHERE A.`vc_type_id` = 6 AND A.`vc_isshow` = 1 AND B.`gi_isshow` = 1 AND B.`gi_type_id` = ".$mydata['id'];
        $count = $conn->count($sql);
        $mem_obj->set($type_album_count_key,$count,3600);
    }

    if(!empty($count)){
        $temp_arr[] = array(
            'relaid' => $mydata['id'], //关联id
            'relatype' => 2, //关联类型（1：游戏 2：游戏分类）
            'title' => '专题',
            'type' => 4, //类型（1：全部 2：标签 3：用户 4：专题）
            'total' => intval($count)
        );
    }
    $returnArr['rows'] = $temp_arr;
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);
