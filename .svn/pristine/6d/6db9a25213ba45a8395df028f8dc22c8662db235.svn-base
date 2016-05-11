<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 用户订阅主页
 * @file: video_user_sub_index.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2016-01-19  14:43
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");
include_once('../api/ucenter.config.inc.php');

/*参数*/
$mydata = array();
$mydata['uid'] = intval(get_param('uid')); //用户id
$mydata['mac'] = get_param('mac');//用户mac地址
$mydata['imei'] = get_param('imei');//用户imei地址
$mydata['pagenum'] = get_param('pagenum');//当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : intval($mydata['pagenum']);
$mydata['pagesize'] = get_param('pagesize');//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 20 : intval($mydata['pagesize']);
$mydata['gameid'] = intval(get_param('gameid'));//游戏id（为空时订阅全部视频列表，相关订阅按时间排100条）
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//七天前的时间
//$begin_time_stamp = strtotime(date('Y-m-d', strtotime('-7 days'))); //上周一时间戳

$mem_obj = new kyx_memcache();

//获取所有游戏id => 名称数据
$all_game_arr_key = 'all_game_arr';
$game_arr = $mem_obj->get($all_game_arr_key);
if($game_arr === false){
    $sql = "SELECT `id`,`gi_name` FROM `video_game_info` WHERE `gi_isshow` = 1";
    $game_arr = $conn->find($sql,'id');
    $mem_obj->set($all_game_arr_key,$game_arr,14400);
}

//获取分类关联名字缓存数据
$game_type_id_name_all_key = 'game_type_id_name_all';
$game_type_id_name_all = $mem_obj->get($game_type_id_name_all_key);
if($game_type_id_name_all === false){
    $sql = "SELECT `t_id`,`t_name_cn` FROM `video_game_type` WHERE `t_status` = 1";
    $data = $conn->find($sql);
    $game_type_id_name_all = array();
    if(!empty($data)){
        foreach($data as $val){
            $game_type_id_name_all[$val['t_id']] = $val['t_name_cn'];
        }
    }
    unset($data);
    $mem_obj->set($game_type_id_name_all_key,$game_type_id_name_all,14400);
}

$check_where = '';
if(!empty($mydata['mac']) && $mydata['mac'] <> '00:00:00:00:00:00'){
    $check_where .= " AND `mac` = '".$mydata['mac']."'";
}
if(!empty($mydata['imei'])){
    $check_where .= " AND `imei` = '".$mydata['imei']."'";
}

//用户已订阅信息
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

//获取订阅数组
$sub_tag_arr = array();
$sub_user_arr = array();
$sub_type_arr = array();
$sub_game_arr = array();
foreach($sub_info as $val){
    if($val['subtype'] == 1){ //标签
        $sub_tag_arr[] = intval($val['subid']);
    }elseif($val['subtype'] == 2){ //主播
        $sub_user_arr[] = intval($val['subid']);
    }elseif($val['subtype'] == 3){ //游戏
        $sub_game_arr[] = intval($val['subid']);
    }elseif($val['subtype'] == 4){ //游戏分类
        $sub_type_arr[] = intval($val['subid']);
    }
}

if(empty($mydata['gameid'])){
    $data_count = 0;
}else{
    $where = "WHERE `va_isshow` = 1 AND `vvl_game_id` = ".$mydata['gameid'];
    $data_count_key = "xl_user_sub_index_count_".md5($where);
    $data_count = $mem_obj->get($data_count_key);
    if($data_count === false){
        $sql = "SELECT COUNT(1) AS num FROM `video_video_list` ".$where;
        $data_count = $conn->count($sql);
        $mem_obj->set($data_count_key,$data_count,1800);
    }
}

//最大页数
$page_max = intval(ceil($data_count/$mydata['pagesize']));

//LIMIT条件
$offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
$limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

//排序
$orderby = ' ORDER BY `vvl_upload_time` DESC';

//定义回转的默认参数
$returnArr = array(
    'total' => intval($data_count), //数据总条数
    'pagecount' => intval($page_max), //总页数
    'pagesize' => $mydata['pagesize'], //每页显示数据
    'pagenum' => $mydata['pagenum'], //当前页
    'rows' => array() //数据数组
);

if(!empty($sub_info)){ //有订阅信息
    if(empty($mydata['gameid'])){ //全部

        //数据存储数组
        $temp_arr = array();

        //获取订阅游戏最新的30数据
//        if(!empty($sub_game_arr)){
//            foreach($sub_game_arr as $gval){
//                $game_data_key = 'user_index_game_'.$gval;
//                $game_data = $mem_obj->get($game_data_key);
//                if($game_data === false){
//                    $sql = "SELECT `id`,`vvl_game_id`,`in_date`,`vvl_upload_time` FROM `video_video_list` WHERE `va_isshow` = 1 AND `vvl_game_id` = ".$gval." ORDER BY vvl_upload_time DESC LIMIT 20";
//                    $game_data = $conn->find($sql);
//                    $mem_obj->set($game_data_key,$game_data,600);
//                }
//                if(!empty($game_data)){
//                    foreach($game_data as $val){
//                        $temp_arr[$val['id']] = array(
//                            'id' => intval($val['id']),
//                            'time' => intval($val['vvl_upload_time']),
//                            'subword' => isset($game_arr[$val['vvl_game_id']]['gi_name']) ? ('订阅词：'.$game_arr[$val['vvl_game_id']]['gi_name']) : ''
//                        );
//                    }
//                }
//            }
//        }

        //获取订阅分类最新的30数据
        if(!empty($sub_type_arr)){
            foreach($sub_type_arr as $tval){
                $type_data_key = 'user_index_type_'.$tval;
                $type_data = $mem_obj->get($type_data_key);
                if($type_data === false){
                    $sql = "SELECT A.`id`,B.`gi_type_id`,A.`in_date`,A.`vvl_upload_time` FROM `video_video_list` AS A LEFT JOIN `video_game_info` AS B ON A.`vvl_game_id` = B.`id`
                            WHERE A.`va_isshow` = 1 AND B.`gi_type_id` = ".$tval." AND B.`gi_isshow` = 1 ORDER BY A.`vvl_upload_time` DESC LIMIT 25";
                    $type_data = $conn->find($sql);
                    $mem_obj->set($type_data_key,$type_data,600);
                }
                if(!empty($type_data)){
                    foreach($type_data as $val){
                        if(isset($temp_arr[$val['id']])){
                            $temp_word = isset($game_type_id_name_all[$val['gi_type_id']]) ? ('、'.$game_type_id_name_all[$val['gi_type_id']]) : '';
                            $temp_arr[$val['id']]['subword'] .= $temp_word;
                        }else{
                            $temp_arr[$val['id']] = array(
                                'id' => intval($val['id']),
                                'time' => intval($val['vvl_upload_time']),
                                'subword' => isset($game_type_id_name_all[$val['gi_type_id']]) ? ('订阅词：'.$game_type_id_name_all[$val['gi_type_id']]) : ''
                            );
                        }
                    }
                }
            }
        }

        //获取订阅主播最新的30数据
//        if(!empty($sub_user_arr)){
//            $str = implode(',',$sub_user_arr);
//            $user_arr = array();
//            $sql = "SELECT `uid`,`nickname` FROM `uc_members` WHERE `uid` IN (".$str.")";
//            $data = $uconn->find($sql);
//            if(!empty($data)){
//                foreach($data as $val){
//                    $user_arr[$val['uid']] = $val['nickname'];
//                }
//                unset($data);
//            }
//            foreach($sub_user_arr as $uval){
//                $user_data_key = 'user_index_user_'.$uval;
//                $user_data = $mem_obj->get($user_data_key);
//                if($user_data === false){
//                    $sql = "SELECT `id`,`vvl_uid`,`in_date`,`vvl_upload_time` FROM `video_video_list` WHERE `va_isshow` = 1 AND `vvl_uid` = ".$uval." ORDER BY `vvl_upload_time` DESC LIMIT 20";
//                    $user_data = $conn->find($sql);
//                    $mem_obj->set($user_data_key,$user_data,600);
//                }
//                if(!empty($user_data)){
//                    foreach($user_data as $val){
//                        if(isset($temp_arr[$val['id']])){
//                            $temp_word = isset($user_arr[$val['vvl_uid']]) ? ('、'.$user_arr[$val['vvl_uid']]) : '';
//                            $temp_arr[$val['id']]['subword'] .= $temp_word;
//                        }else{
//                            $temp_arr[$val['id']] = array(
//                                'id' => intval($val['id']),
//                                'time' => intval($val['vvl_upload_time']),
//                                'subword' => isset($user_arr[$val['vvl_uid']]) ? ('订阅词：'.$user_arr[$val['vvl_uid']]) : ''
//                            );
//                        }
//                    }
//                }
//            }
//        }

        //获取订阅标签最新的30数据
        if(!empty($sub_tag_arr)){
            $str = implode(',',$sub_tag_arr);
            $sql = "SELECT `vtc_id`,`vtc_name` FROM `video_game_tags` WHERE `vtc_type` = 2 AND `vtc_status` = 1 AND `vtc_id` IN (".$str.")";
            $data = $conn->find($sql);

            $tag_arr = array();
            if(!empty($data)){
                foreach($data as $val){
                    $tag_arr[$val['vtc_id']] = $val['vtc_name'];
                }
            }

            foreach($sub_tag_arr as $taval){
                $tag_data_key = 'user_index_tag_'.$taval;
                $tag_video_data = $mem_obj->get($tag_data_key);
                if($tag_video_data === false){
                    $sql = "SELECT DISTINCT A.`v_id`,A.`vtc_tag_id`,B.`in_date`,B.`vvl_upload_time` FROM `video_tag_mapping` AS A RIGHT JOIN `video_video_list` AS B
                            ON  A.`v_id` = B.`id` WHERE B.`va_isshow` = 1 AND A.`vtc_tag_id` = ".$taval." ORDER BY B.`vvl_upload_time` DESC LIMIT 25";
                    $tag_video_data = $conn->find($sql);
                    $mem_obj->set($tag_data_key,$tag_video_data,600);
                }
                if(!empty($tag_video_data)){
                    foreach($tag_video_data as $val){
                        if(isset($temp_arr[$val['v_id']])){
                            $temp_word = isset($tag_arr[$val['vtc_tag_id']]) ? ('、'.$tag_arr[$val['vtc_tag_id']]) : '';
                            $temp_arr[$val['v_id']]['subword'] .= $temp_word;
                        }else{
                            $temp_arr[$val['v_id']] = array(
                                'id' => intval($val['v_id']),
                                'time' => intval($val['vvl_upload_time']),
                                'subword' => isset($tag_arr[$val['vtc_tag_id']]) ? ('订阅词：'.$tag_arr[$val['vtc_tag_id']]) : ''
                            );
                        }
                    }
                }
            }
        }

        $data = array();
        if(!empty($temp_arr)){
            //数据转换
            $data_temp_arr = data_sort($temp_arr,'time','desc');

            //人工分页获取数据
            $count = count($data_temp_arr);
            $returnArr['total'] = $count;
            $returnArr['pagecount'] = intval($count/$returnArr['pagesize']);
            $ids_arr = array();
            $start = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
            $max_num = $mydata['pagenum'] * $mydata['pagesize'];
            $end = ($max_num > $count) ? $count : $max_num;
            for($i = $start;$i < $end;$i++){
                $ids_arr[] = intval($data_temp_arr[$i]['id']);
            }
            unset($data_temp_arr);

            //获取详细数据
            $str = implode(',',$ids_arr);
            if(!empty($str)){
                $data_key = "xl_user_sub_index_all_data_".md5($str);
                $data = $mem_obj->get($data_key);
                if($data === false){
                    $sql = "SELECT `id`,`vvl_title`,`vvl_imgurl`,`vvl_upload_time`,`vvl_imgurl_get`,`vvl_category_id`,`vvl_game_id`,`vvl_type_id`,`in_date`,
                            `vvl_tags`,`vvl_author_id`,`vvl_video_id`,`vvl_count`,`vvl_uid`,`vvl_tags`,`vvl_time`,`vvl_up_num`,`vvl_down_num`,`vvl_sourcetype`
                            FROM `video_video_list` WHERE `id` IN (".$str.") ".$orderby;
                    $data = $conn->find($sql);
                    $mem_obj->set($data_key,$data,600);
                }
            }
        }

    }else{ //订阅游戏视频列表

        //查询数据
        $data_key = "xl_user_sub_index_data_".md5($where.$orderby.$limit);
        $data = $mem_obj->get($data_key);
        if($data === false){
            $sql = "SELECT `id`,`vvl_title`,`vvl_imgurl`,`vvl_upload_time`,`vvl_imgurl_get`,`vvl_category_id`,`vvl_game_id`,`vvl_type_id`,`in_date`,
                    `vvl_tags`,`vvl_author_id`,`vvl_count`,`vvl_video_id`,`vvl_uid`,`vvl_tags`,`vvl_time`,`vvl_up_num`,`vvl_down_num`,`vvl_sourcetype`
                    FROM `video_video_list` ".$where.$orderby.$limit;
            $data = $conn->find($sql);
            $mem_obj->set($data_key,$data,600);
        }
    }
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

        //订阅词
        if(empty($mydata['gameid'])){
            $sub_word = isset($temp_arr[$val['id']]['subword']) ? $temp_arr[$val['id']]['subword'] : '';
        }else{
            $sub_word = isset($game_arr[$val['vvl_game_id']]['gi_name']) ? ('订阅词：'.$game_arr[$val['vvl_game_id']]['gi_name']) : '';
        }

        $json = array(
            'appid' => intval($val['id']), //视频id
            'subword' => $sub_word,
            'title' => filter_search(delete_html($val['vvl_title'])), //视频标题
            'source' => isset($val['vvl_sourcetype']) ? (isset($GLOBALS['SOURCE_ARR'][$val['vvl_sourcetype']]) ? $GLOBALS['SOURCE_ARR'][$val['vvl_sourcetype']] : '') : '',
            'sourcetype' => isset($val['vvl_sourcetype']) ? intval($val['vvl_sourcetype']) : 0,
            'videoid' => isset($val['vvl_video_id']) ? $val['vvl_video_id'] : '',
            'imgurl' => $tmp_img_url, //视频图片
            'gameid' => isset($val['vvl_game_id']) ? intval($val['vvl_game_id']) : 2, //游戏id
            'gamename' => isset($game_arr[$val['vvl_game_id']]['gi_name']) ? $game_arr[$val['vvl_game_id']]['gi_name'] : '', //游戏名称
            'authorname' => $author_name, //作者名称
            'authorimg' => $author_img, //作者头像
            'duration' => strstr($val['vvl_time'],':') ? $val['vvl_time'] : '',
            'playnum' => intval($val['vvl_count'])  + intval($a_old_play_val), //视频播放总数 + 缓存播放次数
            'tag' => $tag_name, //视频标签
            'tagcolour' => $tag_colour, //视频颜色
            'likenum' => $up_num, //视频点赞数
            'unlikenum' => $down_num, //视频点踩数
            'haslike' => $has_up, //是否点赞过该视频 1：是 0：否
            'hasunlike' => $has_down, //是否点踩过该视频 1：是 0：否
            'time' => isset($val['vvl_upload_time']) ? $val['vvl_upload_time'] : 0, //采集时间
            'timestamp' => isset($val['vvl_upload_time']) ? $val['vvl_upload_time'] : 0 //时间戳
        );
        $returnArr['rows'][] = $json;
    }
}


$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);





