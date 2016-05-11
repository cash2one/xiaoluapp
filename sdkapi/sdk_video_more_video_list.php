<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取SDK视频查看更多视频列表
 * @file: sdk_video_more_video_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-23  16:44
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");
include_once('../api/ucenter.config.inc.php');

/*参数*/
$mydata = array();
$mydata['pagenum'] = get_param('pagenum');//当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : intval($mydata['pagenum']);
$mydata['pagesize'] = get_param('pagesize');//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 12 : intval($mydata['pagesize']);
$mydata['packagename'] = get_param('packagename'); //游戏包名
$mydata['appid'] = intval(get_param('appid')); //视频id
$mydata['tid'] = intval(get_param('tid')); //类型id（-1：首页最新更新 -2：首页推荐热门 -3：详情页相关推荐）
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//游戏包名判断
if(empty($mydata['packagename'])){
    exit('packagname error');
}

$mem_obj = new kyx_memcache();

//获取包名对应游戏id
$game_id_key = 'game_id_package_key_'.$mydata['packagename'];
$game_id = $mem_obj->get($game_id_key);
if($game_id === false){
    $game_sql = "SELECT `id` FROM `video_game_info` WHERE `gi_packname` = '".$mydata['packagename']."'";
    $game_data = $conn->get_one($game_sql);
    $game_id = isset($game_data['id']) ? intval($game_data['id']) : 0;
    $mem_obj->set($game_id_key,$game_id,3600);
}

//查询条件
$where = " WHERE va_isshow=1 AND vvl_sourcetype <> 11 AND CHAR_LENGTH(vvl_title)>=5 AND vvl_game_id = ".$game_id." ";

//详情相关视频
if($mydata['tid'] == -3 && !empty($mydata['appid'])){
    $sql_data = "SELECT `vvl_author_id`,`vvl_category_id` FROM `video_video_list` WHERE CHAR_LENGTH(vvl_title)>=5 AND `id` = ".$mydata['appid'] ;
    $video_info = $conn->get_one($sql_data);
    if(!empty($video_info['vvl_category_id'])){
        $where .= " AND `vvl_category_id` = ".$video_info['vvl_category_id'];
    }elseif(!empty($video_info['vvl_author_id'])){
        $where .= " AND `vvl_author_id` = ".$video_info['vvl_author_id'];
    }
}

//查询数据总数
$sql = "SELECT count(1) as num FROM `video_video_list` ".$where ;
$data_count = $conn->count($sql);

//最大页数
$page_max = ceil($data_count/$mydata['pagesize']);

//LIMIT条件
$offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
$limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

//排序
$orderby = '';
if($mydata['tid'] == -1){ //首页最新更新（采集时间排序）
    $orderby .= " ORDER BY in_date DESC,id DESC ";
}else{ //视频播放总数排序
    $orderby .= " ORDER BY vvl_count DESC,id DESC ";
}

//定义回转的默认参数
$returnArr = array(
    'total' => $data_count, //数据总条数
    'pagecount' => $page_max, //总页数
    'pagesize' => $mydata['pagesize'], //每页显示数据
    'pagenum' => $mydata['pagenum'], //当前页
    'rows' => array() //数据数组
);

//获取视频更多数据
$sql = "SELECT id,vvl_title,vvl_imgurl,vvl_imgurl_get,vvl_category_id,vvl_type_id,in_date,vvl_tags,vvl_count,vvl_author_id,vvl_uid
        FROM `video_video_list` ".$where.$orderby.$limit;
$data = $conn->find($sql);
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
            'typeid' => intval($val['vvl_type_id']), //视频类型（1任务，2解说，3赛事战况，4集锦）
            'title' => filter_search(delete_html($val['vvl_title'])), //视频标题
            'playnum' => intval($val['vvl_count']) + intval($a_old_play_val),//视频本地播放数 + 缓存播放次数
            'imgurl' => $tmp_img_url, //视频图片
            'authorname' => $author_name, //作者名称
            'authorimg' => $author_img, //作者头像
            'tag' => $tag_name, //视频标签
            'tagcolour' => $tag_colour, //视频标签颜色
            'time' => intval($val['in_date']) //采集时间
        );
        $returnArr['rows'][] = $json;
    }
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);





