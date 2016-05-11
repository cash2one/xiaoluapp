<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取视频专区视频列表,并JSON内容进行输出返回
 * @file: video_area_video_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-11-04  12:02
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");
include_once('../api/ucenter.config.inc.php');

/*参数*/
$mydata = array();
$mydata['uid'] = intval(get_param('uid'));//用户ID
$mydata['mac'] = get_param('mac');//用户mac地址
$mydata['imei'] = get_param('imei');//用户imei地址
$mydata['pagenum'] = get_param('pagenum');//当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : intval($mydata['pagenum']);
$mydata['pagesize'] = get_param('pagesize');//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 20 : intval($mydata['pagesize']);
$mydata['flushtype'] = intval(get_param('flushtype')); //刷新类型（1：向上翻页 2：向下刷新）
$mydata['flushtype'] = empty($mydata['flushtype']) ? 1 : $mydata['flushtype'];
$mydata['id'] = intval(get_param('id')); //导航关联id（-1：最新视频 -2：独家视频）
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error !');
}

if(empty($mydata['id'])){
    exit('id error !');
}

$mem_obj = new kyx_memcache();

$check_where = '';
if(!empty($mydata['mac']) && $mydata['mac'] <> '00:00:00:00:00:00'){
    $check_where .= " AND `mac` = '".$mydata['mac']."'";
}
if(!empty($mydata['imei'])){
    $check_where .= " AND `imei` = '".$mydata['imei']."'";
}

//获取所有游戏id => 名称数据
$all_game_arr_key = 'all_game_arr';
$game_arr = $mem_obj->get($all_game_arr_key);
if($game_arr === false){
    $sql = "SELECT `id`,`gi_name` FROM `video_game_info` WHERE `gi_isshow` = 1";
    $game_arr = $conn->find($sql,'id');
    $mem_obj->set($all_game_arr_key,$game_arr,14400);
}

//当前时间到24:00相差时间（秒）
$tomo = date("Y-m-d",strtotime("+1 day"));
$seconds = strtotime($tomo) - time();
//$seconds = 600;

//记录用户刷新次数
$today_date = date('Ymd',time());
if($mydata['flushtype'] == 2){
    $sql = "SELECT `id` FROM `video_max_page_count` WHERE `rele_type` = 1 AND `oper_type` = 2 AND `create_time` = ".$today_date.$check_where." LIMIT 1";
    $check = $conn->find($sql);
    if(isset($check[0]['id']) && !empty($check[0]['id'])){
        $sql = "UPDATE video_max_page_count SET `weight` = `weight` + 1 WHERE id = ".intval($check[0]['id']);
        $conn->query($sql);
    }else{
        $count_arr = array(
            'create_time' => $today_date,
            'uid' => $mydata['uid'],
            'mac' => $mydata['mac'],
            'imei' => $mydata['imei'],
            'rele_id' => $mydata['id'],
            'rele_type' => 1,
            'oper_type' => 2,
            'max_page' => 0,
            'weight' => 1
        );
        $conn->save('video_max_page_count',$count_arr);
    }
}else{
    $count_max_page_key = md5('count_max_page_key_'.$check_where.$today_date);
    $max_page_arr = $mem_obj->get($count_max_page_key);
    if($max_page_arr === false){
        $sql = "SELECT `id`,`max_page` FROM `video_max_page_count` WHERE `rele_type` = 1 AND `oper_type` = 1 AND `create_time` = ".$today_date.$check_where." LIMIT 1";
        $check = $conn->find($sql);
        if(isset($check[0]['max_page']) && !empty($check[0]['max_page'])){
            $max_page_arr = array('id' => intval($check[0]['id']),'max_page' => $max_page);
        }else{
            $count_arr = array(
                'create_time' => $today_date,
                'uid' => $mydata['uid'],
                'mac' => $mydata['mac'],
                'imei' => $mydata['imei'],
                'rele_id' => $mydata['id'],
                'rele_type' => 1,
                'oper_type' => 1,
                'max_page' => 1,
                'weight' => 1
            );
            $max_page = $conn->save('video_max_page_count',$count_arr);
            $max_page_arr = array('id' => $max_page,'max_page' => 1);
        }
        $mem_obj->set($count_max_page_key,$max_page_arr,$seconds);
    }

    //如果当前页大于记录页
    if($mydata['pagenum'] > $max_page_arr['max_page']){
        $sql = "UPDATE video_max_page_count SET `max_page` = ".$mydata['pagenum']." WHERE id = ".intval($max_page_arr['id']);
        $conn->query($sql);
    }
}


//查询条件
if($mydata['id'] > 0){

    //下拉分页刷新
    $now_min_id_key = 'user_area_now_min_id_key_'.md5($mydata['uid'].$mydata['mac'].$mydata['imei'].$mydata['id']);
    $now_max_id_key = 'user_area_now_max_id_key_'.md5($mydata['uid'].$mydata['mac'].$mydata['imei'].$mydata['id']);
    $flush_sort_key = 'user_area_flush_sort_key_'.md5($mydata['uid'].$mydata['mac'].$mydata['imei'].$mydata['id']);
    $flush_is_max_key = 'user_area_flush_is_max_'.md5($mydata['uid'].$mydata['mac'].$mydata['imei'].$mydata['id']);
    if($mydata['flushtype'] == 2){
        $now_min_id = $mem_obj->get($now_min_id_key);
        $now_max_id = $mem_obj->get($now_max_id_key);
        if($now_max_id === false || $now_min_id === false){
            $orderby = ' ORDER BY B.`vvl_upload_time` DESC';
        }elseif($now_max_id > $now_min_id){
            $orderby = ' ORDER BY B.`vvl_upload_time` < '.$now_min_id.' DESC,B.`vvl_upload_time` DESC ';
        }else{
            $orderby = ' ORDER BY B.`vvl_upload_time` < '.$now_max_id.' DESC,B.`vvl_upload_time` DESC ';
        }
        $mydata['pagenum'] = 1;
        $mem_obj->set($flush_sort_key,$orderby,$seconds);
    }else{
        $orderby = $mem_obj->get($flush_sort_key);
        if($flush_sort === false){
            $orderby = ' ORDER BY B.`vvl_upload_time` DESC';
        }
    }

    //一个月前的时间戳
    $last_month = strtotime("-1 month");

    $where = " WHERE B.`va_isshow` = 1 AND A.`va_id` = ".$mydata['id']." AND B.`vvl_upload_time` > ".$last_month;
}else{
    $where = " WHERE `va_isshow` = 1 ";
    if($mydata['id'] == -2){ //独家
        $where .= " AND `vvl_sourcetype` = 14 ";
    }else{
        $where .= " AND `vvl_sourcetype` <> 14 ";
    }

    //排序
    $orderby = ' ORDER BY `vvl_upload_time` DESC';
}

//查询数据总数
$data_count_key = "xl_area_video_list_count_".md5($where);
$data_count = $mem_obj->get($data_count_key);
if($data_count === false){
    if($mydata['id'] > 0){
        $sql = "SELECT count(DISTINCT A.`vvl_id`) as num FROM `video_area_video_info` AS A RIGHT JOIN `video_video_list` AS B ON B.`id` = A.`vvl_id` ".$where;
    }else{
        $sql = "SELECT count(1) as num FROM `video_video_list` ".$where;
    }
    $data_count = $conn->count($sql);
    $mem_obj->set($data_count_key,$data_count,600);
}

//最大页数
$page_max = intval(ceil($data_count/$mydata['pagesize']));

//LIMIT条件
$offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
$limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

//定义回转的默认参数
$returnArr = array(
    'total' => $data_count, //数据总条数
    'hasnew' => (($mydata['id'] < 0) || $data_count > $mydata['pagesize']) ? 1 : 0,
    'pagecount' => $page_max, //总页数
    'flushtype' => ($mydata['id'] < 0) ? 2 : 1, //刷新方式（1：推荐刷新 2：下拉刷新）
    'pagesize' => $mydata['pagesize'], //每页显示数据
    'pagenum' => $mydata['pagenum'], //当前页
    'rows' => array() //数据数组
);

//获取专辑视频数据
$data_key = "xl_area_video_list_data_".md5($where.$orderby.$limit);
$data = $mem_obj->get($data_key);
if($data === false || ($mydata['id'] > 0 && $mydata['flushtype'] == 2)){
    if($mydata['id'] > 0){
        $sql = "SELECT DISTINCT B.`id`,B.`vvl_title`,B.`vvl_imgurl`,B.`vvl_imgurl_get`,B.`vvl_category_id`,B.`vvl_type_id`,B.`vvl_author_id`,B.`vvl_sourcetype`,
                B.`in_date`,B.`vvl_upload_time`,B.`vvl_tags`,B.`vvl_count`,B.`vvl_time`,B.`vvl_uid`,B.`vvl_video_id`,B.`vvl_game_id`,B.`vvl_up_num`,B.`vvl_down_num`
                FROM `video_area_video_info` AS A RIGHT JOIN `video_video_list` AS B ON B.`id` = A.`vvl_id` ".$where.$orderby.$limit;
    }else{
        $sql = "SELECT `id`,`vvl_title`,`vvl_imgurl`,`vvl_imgurl_get`,`vvl_category_id`,`vvl_type_id`,`vvl_author_id`,`vvl_sourcetype`,
                `in_date`,`vvl_upload_time`,`vvl_tags`,`vvl_count`,`vvl_time`,`vvl_uid`,`vvl_video_id`,`vvl_game_id`,`vvl_up_num`,`vvl_down_num`
                FROM `video_video_list` ".$where.$orderby.$limit;
    }
    $data = $conn->find($sql);
    $mem_obj->set($data_key,$data,600);
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
            'gameid' => isset($val['vvl_game_id']) ? intval($val['vvl_game_id']) : 2, //游戏id
            'id' => intval($val['vvl_game_id']), //导航关联id
            'type' => 1, //导航类型（1：游戏 2：游戏分类）
            'anchorid' => intval($val['vvl_uid']), //主播id
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
            'timestamp' => isset($val['vvl_upload_time']) ? $val['vvl_upload_time'] : 0, //时间戳
            'time' => isset($val['vvl_upload_time']) ? (empty($val['vvl_upload_time']) ? '' : date('Y-m-d',$val['vvl_upload_time'])) : date('Y-m-d',$val['in_date']) //采集时间
        );
        $returnArr['rows'][] = $json;
    }

    //获取当前用户翻到的最大时间值
    if($mydata['id'] > 0){
        $max_count = count($returnArr['rows']);
        $now_max_id = isset($returnArr['rows'][0]['timestamp']) ? intval($returnArr['rows'][0]['timestamp']) : 0;
        $now_min_id = isset($returnArr['rows'][$max_count-1]['timestamp']) ? intval($returnArr['rows'][$max_count-1]['timestamp']) : 0;
        $is_max = $mem_obj->get($flush_is_max_key);
        if(($now_max_id > $now_min_id) && $is_max === false){
            $mem_obj->set($now_min_id_key,$now_min_id,$seconds);
            $mem_obj->set($now_max_id_key,$now_max_id,$seconds);
        }else{
            if($is_max === false){
                $mem_obj->set($flush_is_max_key,1,$seconds);
            }
            $returnArr['hasnew'] = 0;
        }
    }
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);





