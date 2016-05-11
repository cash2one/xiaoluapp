<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取视频APP排行榜数据列表,并JSON内容进行输出返回
 * @file: video_rank_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-12-15  10:18
 * @version 1.0
 **/
include_once("../../config.inc.php");
include_once("../../db.config.inc.php");
include_once("../../db.ucenter.config.inc.php");
include_once('../../api/ucenter.config.inc.php');

/*参数*/
$mydata = array();
$mydata['pagenum'] = get_param('pagenum');//当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : intval($mydata['pagenum']);
$mydata['pagesize'] = get_param('pagesize');//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 20 : intval($mydata['pagesize']);
$mydata['gameid'] = intval(get_param('gameid')); //游戏ID（默认0不区分游戏）
$mydata['order'] = intval(get_param("order")); //排序类型 0：周排行榜（上周视频进行排序） 1：月排行榜（上月视频进行排序） 2：总排行榜
$mydata['key'] = get_param('key'); //验证key

//key判断
$tmp_key = new_open_key_mzw();
if($mydata['key'] != $tmp_key){
    exit('key error');
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

//查询条件
$where = " WHERE `va_isshow` = 1 AND CHAR_LENGTH(`vvl_title`) >= 5 ";
if(empty($mydata['gameid'])){
    $where .= " AND `vvl_game_id` <> 9 AND `vvl_game_id` > 0 ";
}elseif($mydata['gameid'] == 2){
    $where .= " AND (`vvl_game_id` = 12 OR `vvl_game_id` = 2) ";
}else{
    $where .= " AND `vvl_game_id` = ".$mydata['gameid'];
}

//LIMIT条件
$offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
$limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

//排序
$orderby = '';
$time_where = '';
if($mydata['order'] == 1){ //月排行
    $orderby .= " ORDER BY vvl_last_month_plays DESC,id DESC ";
    $play_num_key = 'vvl_last_month_plays';

    //上传视频限制，周排行只取上周上传的视频进行排序，月排行只取上月上传的视频进行排序
    $begin_time = mktime(0, 0 , 0,date("m")-1,1,date("Y")); //上月第一天时间戳
    $end_time = mktime(23,59,59,date("m") ,0,date("Y")); //上月最后一天时间戳
    $time_where = " AND `in_date` >= ".$begin_time." AND `in_date` <= ".$end_time.' ';

}elseif($mydata['order'] == 2){ //总排行
    $orderby .= " ORDER BY vvl_count DESC,id DESC ";
    $play_num_key = 'vvl_count';
}else{ //周排行
    $orderby .= " ORDER BY vvl_last_week_plays DESC,id DESC ";
    $play_num_key = 'vvl_last_week_plays';

    //上传视频限制，周排行只去上周上传的视频进行排序，月排行只取上月上传的视频进行排序
    $begin_time = mktime(0,0,0,date('m'),date('d') - date('w') + 1 - 7,date('Y')); //上周一时间戳
    $end_time = mktime(23,59,59,date('m'),date('d') - date('w') + 7 - 7,date('Y')); //上周日时间戳
    $time_where = " AND `in_date` >= ".$begin_time." AND `in_date` <= ".$end_time.' ';
}

//查询数据总数
$limit_num = 5 * $mydata['pagesize'];
$video_rank_list_count_key = 'mzw_video_rank_list_count_'.$mydata['gameid'].'_'.$mydata['order']; //排行榜视频总数缓存key  'video_rank_list_count_' + 游戏id + 排序类型id
$data_count = $mem_obj->get($video_rank_list_count_key); //排行榜视频总数
if($data_count === false){
    $sql = "SELECT IF(COUNT(1) > ".$limit_num.",".$limit_num.",COUNT(1)) AS num FROM `video_video_list` ".$where.$time_where;
    $data_count = $conn->count($sql);
    if(empty($data_count)){
        $sql = "SELECT IF(COUNT(1) > ".$limit_num.",".$limit_num.",COUNT(1)) AS num FROM `video_video_list` ".$where;
        $data_count = $conn->count($sql);
    }else{
        $where .= $time_where;
    }
    $mem_obj->set($video_rank_list_count_key,$data_count,1800);
}

//最大页数
$page_max = ceil($data_count/$mydata['pagesize']);

//定义回转的默认参数
$returnArr = array(
    'total' => $data_count, //数据总条数
    'pagecount' => $page_max, //总页数
    'pagesize' => $mydata['pagesize'], //每页显示数据
    'pagenum' => $mydata['pagenum'], //当前页
    'rows' => array() //数据数组
);

//获取排行榜数据
if($mydata['pagenum'] <= 5){
    $video_rank_list_data_key = 'mzw_video_rank_list_data_'.$mydata['gameid'].'_'.$mydata['order'].'_'.$mydata['pagenum'].'_'.$mydata['pagesize']; //排行榜视频数据缓存key  'video_rank_list_data_' + 游戏id + 排序类型id + 当前页 + 每页显示数据
    $data = $mem_obj->get($video_rank_list_data_key); //排行榜视频数据
    if($data === false){
        $sql = "SELECT id,vvl_title,vvl_imgurl,vvl_imgurl_get,vvl_category_id,vvl_type_id,vvl_author_id,in_date,
                vvl_tags,vvl_last_week_plays,vvl_last_month_plays,vvl_count,vvl_uid,vvl_game_id
                FROM `video_video_list` ".$where.$orderby.$limit;
        $data = $conn->find($sql);
        $mem_obj->set($video_rank_list_data_key,$data,1800);
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
            $a_old_play_val = ($play_num_key == 'vvl_count') ? intval($a_old_play_val) : 0;


            $json = array(
                'appid' => intval($val['id']), //视频id
                'typeid' => intval($val['vvl_type_id']), //视频类型（1任务，2解说，3赛事战况，4集锦）
                'gameid' => intval($val['vvl_game_id']), //关联游戏id
                'gamename' => isset($game_arr[$val['vvl_game_id']]['gi_name']) ? $game_arr[$val['vvl_game_id']]['gi_name'] : '', //关联游戏名称
                'title' => filter_search(delete_html($val['vvl_title'])), //视频标题
                'playnum' => intval($val[$play_num_key]) + intval($a_old_play_val), //视频播放总数 + 缓存播放次数
                'authorname' => $author_name, //作者名称
                'authorimg' => $author_img, //作者头像
                'imgurl' => $tmp_img_url, //视频图片
                'tag' => $tag_name, //视频标签
                'tagcolour' => $tag_colour, //视频标签颜色
                'time' => intval($val['in_date']) //采集时间
            );
            $returnArr['rows'][] = $json;
        }
    }
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);
