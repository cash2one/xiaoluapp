<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取SDK视频项目 首页数据列表,并JSON内容进行输出返回
 * @file: sdk_video_index_album.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-22  10:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");
include_once('../api/ucenter.config.inc.php');

/*参数*/
$mydata = array();
$mydata['packagename'] = get_param('packagename');//游戏包名
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

//定义回转的默认参数
$returnArr = array(
    'rows' => array()
);

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

//最新更新（根据采集时间降序）
$sql = "SELECT id,vvl_title,vvl_imgurl,vvl_imgurl_get,vvl_category_id,vvl_type_id,vvl_author_id,vvl_tags,vvl_count,in_date,vvl_uid
        FROM `video_video_list` WHERE va_isshow=1 AND vvl_sourcetype <> 14 AND CHAR_LENGTH(vvl_title)>=5 AND vvl_game_id = ".$game_id." ORDER BY in_date DESC LIMIT 10";
$data = $conn->find($sql);
$count = count($data);
if(!empty($data) && is_type($data,'Array') && $count == 10){
    $temp_arr = array();
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

        $temp_arr[] = array(
            'appid' => intval($val['id']), //视频id
            'typeid' => intval($val['vvl_type_id']), //视频类型（1任务，2解说，3赛事战况，4集锦）
            'playnum' => intval($val['vvl_count']) + intval($a_old_play_val),//视频本地播放数 + 缓存播放次数
            'title' => filter_search(delete_html($val['vvl_title'])), //视频标题
            'imgurl' => $tmp_img_url, //视频图片
            'authorname' => $author_name, //作者名称
            'authorimg' => $author_img, //作者头像
            'tag' => $tag_name,
            'tagcolour' => $tag_colour,
            'time' => intval($val['in_date']) //采集时间
        );
    }
    $returnArr['rows'][] = array(
        'type_title' => '编辑推荐', //显示标题
        'category_title' => '编辑推荐', //分类显示标题
        'type' => 1, //显示样式（1：10个5行两列）
        'tid' => -1, //查看更多用到id
        'packagename' => $mydata['packagename'], //游戏包名
        'row' => $temp_arr //游戏数据
    );
}

//推荐热门（根据播放数降序）
$sql = "SELECT id,vvl_title,vvl_imgurl,vvl_imgurl_get,vvl_category_id,vvl_type_id,vvl_author_id,vvl_tags,vvl_count,in_date,vvl_uid
        FROM `video_video_list` WHERE  va_isshow=1 AND vvl_sourcetype <> 14 AND CHAR_LENGTH(vvl_title)>=5 AND vvl_game_id = ".$game_id." ORDER BY vvl_count DESC LIMIT 10";
$data = $conn->find($sql);
$count = count($data);
if(!empty($data) && is_type($data,'Array') && $count == 10){
    $temp_arr = array();
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

        $temp_arr[] = array(
            'appid' => intval($val['id']), //视频id
            'typeid' => intval($val['vvl_type_id']), //视频类型（1任务，2解说，3赛事战况，4集锦）
            'playnum' => intval($val['vvl_count']) + intval($a_old_play_val),//视频本地播放数 + 缓存播放次数
            'title' => $val['vvl_title'], //视频标题
            'imgurl' => $tmp_img_url, //视频图片
            'authorname' => $author_name, //作者名称
            'authorimg' => $author_img, //作者头像
            'tag' => $tag_name,
            'tagcolour' => $tag_colour,
            'time' => intval($val['in_date']) //采集时间
        );
    }
    $returnArr['rows'][] = array(
        'type_title' => '推荐热门', //显示标题
        'category_title' => '推荐热门', //分类显示标题
        'type' => 1, //显示样式（1：10个5行两列）
        'tid' => -2, //查看更多用到id
        'packagename' => $mydata['packagename'], //游戏包名
        'row' => $temp_arr //游戏数据
    );
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);





