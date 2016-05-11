<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取热门视频列表
 * @file: video_hot_video_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-12-15  10:44
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
$mydata['pagesize'] = empty($mydata['pagesize']) ? 12 : intval($mydata['pagesize']);
$mydata['gameid'] = intval(get_param('gameid')); //游戏ID
$mydata['key'] = get_param('key'); //验证key

//key判断
$tmp_key = new_open_key_mzw();
if($mydata['key'] != $tmp_key){
    exit('key error');
}

$mem_obj = new kyx_memcache();

//查询条件
$where = " WHERE A.`va_isshow` = 1 AND CHAR_LENGTH(A.`vvl_title`) >= 5 ";
if(empty($mydata['gameid'])){
    $where .= " AND A.`vvl_game_id` <> 9 AND A.`vvl_game_id` > 0 ";
}elseif($mydata['gameid'] == 2){
    $where .= " AND (A.`vvl_game_id` = 12 OR A.`vvl_game_id` = 2) ";
}else{
    $where .= " AND A.`vvl_game_id` = ".$mydata['gameid'];
}

//查询总数
if(empty($mydata['gameid'])){
    $data_count = $mydata['pagesize']; //固定总数
}else{
    //查询数据总数
    $video_hot_video_list_count_key = 'mzw_video_hot_video_list_count_'.$mydata['gameid']; //热门视频总数缓存key  'mzw_video_hot_video_list_count_' + 游戏id
    $data_count = $mem_obj->get($video_hot_video_list_count_key); //最新视频总数
    if($data_count === false){
        $sql = "SELECT count(1) as num FROM `video_video_list` AS A ".$where ;
        $data_count = $conn->count($sql);
        $mem_obj->set($video_hot_video_list_count_key,$data_count,1800);
    }
}

//排序
$orderby = " ORDER BY A.`vvl_count` DESC,A.`id` DESC ";

//最大页数
$page_max = ceil($data_count/$mydata['pagesize']);

//LIMIT条件
$offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
$limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

//定义回转的默认参数
$returnArr = array(
    'total' => $data_count, //数据总条数
    'pagecount' => $page_max, //总页数
    'pagesize' => $mydata['pagesize'], //每页显示数据
    'pagenum' => $mydata['pagenum'], //当前页
    'rows' => array() //数据数组
);

//获取视频更多数据
$video_hot_video_list_data_key = 'mzw_video_hot_video_list_data_'.$mydata['gameid'].'_'.$mydata['pagenum'].'_'.$mydata['pagesize']; //热门视频数据缓存key  'mzw_video_hot_video_list_data_' + 游戏id + 当前页 + 每页显示数据
$data = $mem_obj->get($video_hot_video_list_data_key); //查看更多视频数据
if($data === false){
    $sql = "SELECT A.`id`,A.`vvl_title`,A.`vvl_imgurl`,A.`vvl_imgurl_get`,A.`vvl_category_id`,A.`vvl_game_id`,A.`vvl_type_id`,A.`in_date`,
            A.`vvl_tags`,A.`vvl_author_id`,A.`vvl_count`,B.`gi_name`,A.`vvl_uid`
            FROM `video_video_list` AS A LEFT JOIN `video_game_info` AS B ON A.`vvl_game_id` = B.`id` ".$where.$orderby.$limit;
    $data = $conn->find($sql);
    $mem_obj->set($video_hot_video_list_data_key,$data,1800);
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
            'gameid' => intval($val['vvl_game_id']), //游戏id
            'gamename' => $val['gi_name'], //游戏名称
            'typeid' => intval($val['vvl_type_id']), //视频类型（1任务，2解说，3赛事战况，4集锦）
            'title' => filter_search(delete_html($val['vvl_title'])), //视频标题
            'playnum' => intval($val['vvl_count']) + intval($a_old_play_val), //视频播放总数 + 缓存播放次数
            'imgurl' => $tmp_img_url, //视频图片
            'authorname' => $author_name, //作者名称
            'authorimg' => $author_img, //作者头像
            'tag' => $tag_name, //视频标签
            'tagcolour' => $tag_colour, //视频标签颜色
            'time' => $val['in_date'] //采集时间
        );
        $returnArr['rows'][] = $json;
    }
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);
