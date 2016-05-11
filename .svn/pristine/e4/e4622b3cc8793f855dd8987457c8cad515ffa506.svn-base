<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取小鹿视频游戏、游戏分类推荐数据列表,并JSON内容进行输出返回
 * @file: video_index_game_album_recom.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-12-09  17:07
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
$mydata['pagenum'] = intval(get_param('pagenum')); //当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : intval($mydata['pagenum']);
$mydata['pagesize'] = intval(get_param('pagesize')); //每页显示数据
$mydata['pagesize'] = empty($mydata['pagesize']) ? 8 : intval($mydata['pagesize']);
$mydata['flushtype'] = intval(get_param('flushtype')); //刷新类型（1：向上翻页 2：向下刷新）
$mydata['flushtype'] = empty($mydata['flushtype']) ? 1 : $mydata['flushtype'];
$mydata['id'] = intval(get_param('id')); //导航关联id
$mydata['type'] = intval(get_param('type')); //导航类型类型（1：游戏 2：游戏分类 3：专区 4:标签）
$mydata['type'] = empty($mydata['type']) ? 1 : $mydata['type'];
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

if(!in_array($mydata['type'],array(1,2,3,4))){
    exit('type error');
}

$mem_obj = new kyx_memcache();

//获取所有游戏id => 名称数据
$all_game_arr_key = 'all_game_arr';
$game_arr = $mem_obj->get($all_game_arr_key);
if($game_arr === false){
    $sql = "SELECT `id`,`gi_name` FROM `video_game_info` WHERE `gi_isshow` = 1";
    $game_arr = $conn->find($sql,'id');
    $mem_obj->set($all_game_arr_key,$game_arr,14400);
}

//获取用户关联导航数据
$check_where = '';
$data = array();
if(!empty($mydata['mac']) && $mydata['mac'] <> '00:00:00:00:00:00'){
    $check_where .= " AND `mac` = '".$mydata['mac']."'";
}
if(!empty($mydata['imei'])){
    $check_where .= " AND `imei` = '".$mydata['imei']."'";
}

//获取每日视频推荐关联视频关联条件
$table_name = 'video_gt_recom_video_list';
$now_time = $date_time = date('Ymd',time());
$temp_where = ' WHERE A.`in_date` = '.$now_time;
if($mydata['type'] == 2){ //游戏分类
    $temp_where .= " AND A.`type_id` = ".$mydata['id'];
}elseif($mydata['type'] == 1){ //游戏
    $temp_where .= " AND A.`game_id` = ".$mydata['id'];
}elseif($mydata['type'] == 4){ //标签
    $temp_where .= " AND A.`t_id` = ".$mydata['id'];
    $crc_num = abs(crc32($mydata['id']))%100;
    $table_name = 'video_tag_recom_video_list_'.$crc_num;
}

//当前时间到24:00相差时间（秒）
$tomo = date("Y-m-d",strtotime("+1 day"));
$seconds = strtotime($tomo) - time();
//$seconds = 600;

//下拉分页刷新
$now_min_id_key = 'user_gtr_now_min_id_key_'.md5($mydata['uid'].$mydata['mac'].$mydata['imei'].$mydata['id'].$mydata['type']);
$now_max_id_key = 'user_gtr_now_max_id_key_'.md5($mydata['uid'].$mydata['mac'].$mydata['imei'].$mydata['id'].$mydata['type']);
$flush_sort_key = 'user_gtr_flush_sort_key_'.md5($mydata['uid'].$mydata['mac'].$mydata['imei'].$mydata['id'].$mydata['type']);
$flush_is_max_key = 'user_gtr_flush_is_max_'.md5($mydata['uid'].$mydata['mac'].$mydata['imei'].$mydata['id'].$mydata['type']);
if($mydata['flushtype'] == 2){
    $now_min_id = $mem_obj->get($now_min_id_key);
    $now_max_id = $mem_obj->get($now_max_id_key);
    if($now_max_id === false || $now_min_id === false){
        $flush_sort = '';
    }elseif($now_max_id > $now_min_id){
        $flush_sort = 'A.id > '.$now_max_id.' DESC';
    }else{
        $flush_sort = 'A.id > '.$now_min_id.' DESC';
    }
    $mydata['pagenum'] = 1;
    $mem_obj->set($flush_sort_key,$flush_sort,$seconds);
}

//获取视频总数
$data_count_key = "xl_index_game_album_recom_count_".md5($temp_where);
$count = $mem_obj->get($data_count_key);
if($count === false){
    if($mydata['type'] == 4){
        $count_sql = "SELECT COUNT(DISTINCT A.`v_id`) AS num FROM `".$table_name."` AS A RIGHT JOIN `video_video_list` AS B ON A.`v_id` = B.`id` ".$temp_where;
        $count_data = $conn->find($count_sql);
    }else{
        $count_sql = "SELECT COUNT(DISTINCT A.`v_id`) AS num FROM `".$table_name."` AS A RIGHT JOIN `video_video_list` AS B ON A.`v_id` = B.`id` ".$temp_where;
        $count_data = $conn->find($count_sql);
    }
    $count = isset($count_data[0]['num']) ? intval($count_data[0]['num']) : 0;
    $mem_obj->set($data_count_key,$count,600);
}

//最大页数
$page_max = intval(ceil($count/$mydata['pagesize']));
$page_max = empty($page_max) ? 1 : $page_max;

//定义回转的默认参数
$returnArr = array(
    'total' => $count, //数据总条数
    'hasnew' => 1, //是否有新数据（1：有 0：没有）
    'pagecount' => $page_max, //总页数
    'pagesize' => $mydata['pagesize'], //每页显示数据
    'pagenum' => $mydata['pagenum'], //当前页
    'rows' => array() //数据数组
);

if($mydata['pagenum'] <= $page_max){

    //LIMIT条件
    $offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
    $limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

    //排序
    $flush_sort = $mem_obj->get($flush_sort_key);
    if($flush_sort === false || empty($flush_sort)){
        $orderby = ' ORDER BY A.`id` ASC';
    }else{
        $orderby = ' ORDER BY '.$flush_sort.',A.`id` ASC';
    }

    //获取数据
    $data_key = "xl_index_game_album_recom_data_".md5($temp_where.$orderby.$limit);
    $data = $mem_obj->get($data_key);
    if($data === false || $mydata['flushtype'] == 2){
        $sql = "SELECT DISTINCT A.`id` AS mid,B.`id`,B.`vvl_title`,B.`vvl_imgurl`,B.`vvl_imgurl_get`,B.`vvl_category_id`,B.`vvl_up_num`,
                B.`vvl_down_num`,B.`vvl_type_id`,B.`vvl_game_id`,B.`vvl_author_id`,B.`vvl_upload_time`,B.`in_date`,B.`vvl_sourcetype`,
                B.`vvl_uid`,B.`vvl_tags`,B.`vvl_count`,B.`vvl_time`,B.`vvl_video_id`,B.`vvl_game_id`
                FROM `".$table_name."` AS A RIGHT JOIN `video_video_list` AS B ON A.`v_id` = B.`id` ".$temp_where.$orderby.$limit;
        $data = $conn->find($sql);
        $mem_obj->set($data_key,$data,600);
    }

    if(!empty($data)){
        foreach($data as $val){
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

            $play_key = 'video_play_num_'.intval($val['id']); //视频播放key
            $old_play_val = $mem_obj->get($play_key); //获取视频原始播放数

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

            $returnArr['rows'][] = array(
                'mid' => intval($val['mid']),
                'appid' => intval($val['id']), //视频id
                'title' => $val['vvl_title'], //视频标题
                'source' => isset($val['vvl_sourcetype']) ? (isset($GLOBALS['SOURCE_ARR'][$val['vvl_sourcetype']]) ? $GLOBALS['SOURCE_ARR'][$val['vvl_sourcetype']] : '') : '',
                'sourcetype' => isset($val['vvl_sourcetype']) ? intval($val['vvl_sourcetype']) : 0,
                'videoid' => isset($val['vvl_video_id']) ? $val['vvl_video_id'] : '',
                'imgurl' => $tmp_img_url, //视频图片
                'gamename' => isset($game_arr[$val['vvl_game_id']]['gi_name']) ? $game_arr[$val['vvl_game_id']]['gi_name'] : '',
                'gameid' => intval($val['vvl_game_id']), //视频关联游戏id
                'id' => intval($val['vvl_game_id']), //导航关联id
                'type' => 1, //导航类型（1：游戏 2：游戏分类）
                'anchorid' => intval($val['vvl_uid']), //主播id
                'authorname' => $author_name, //作者名称
                'authorimg' => $author_img, //作者头像
                'duration' => strstr($val['vvl_time'],':') ? $val['vvl_time'] : '',
                'playnum' => intval($val['vvl_count']) + intval($old_play_val),//视频本地播放数 + 缓存数
                'tag' => $tag_name,
                'tagcolour' => $tag_colour,
                'likenum' => $up_num, //视频点赞数
                'unlikenum' => $down_num, //视频点踩数
                'haslike' => $has_up, //是否点赞过该视频 1：是 0：否
                'hasunlike' => $has_down, //是否点踩过该视频 1：是 0：否
                'timestamp' => isset($val['vvl_upload_time']) ? $val['vvl_upload_time'] : 0, //时间戳
                'time' => isset($val['vvl_upload_time']) ? (empty($val['vvl_upload_time']) ? '' : date('Y-m-d',$val['vvl_upload_time'])) : date('Y-m-d',$val['in_date']) //采集时间
            );
        }
    }

    //获取当前用户翻到的最大id值
    $max_count = count($returnArr['rows']);
    $now_min_id = isset($returnArr['rows'][0]['mid']) ? intval($returnArr['rows'][0]['mid']) : 0;
    $now_max_id = isset($returnArr['rows'][$max_count-1]['mid']) ? intval($returnArr['rows'][$max_count-1]['mid']) : 0;
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

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);





