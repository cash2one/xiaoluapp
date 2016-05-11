<?php
/**
 * @copyright: @快游戏 2014
 * @description: 作者解说列表页，返回请求作者解说列表
 * @file: video_author_category_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-22  18:34
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
$mydata['pagesize'] = empty($mydata['pagesize']) ? 12 : intval($mydata['pagesize']);
$mydata['order'] = intval(get_param("order")); //排序类型
$mydata['gameid'] = intval(get_param('gameid'));//游戏ID
$mydata['uid'] = intval(get_param('uid'));//用户id
$mydata['authorid'] = intval(get_param('authorid'));//作者ID
$mydata['categoryid'] = intval(get_param('categoryid'));//视频小类型ID
$mydata['typeid'] = intval(get_param('typeid'));//视频类型
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

if($mydata['gameid']<1){
	$mydata['gameid'] = 1;
}

//定义回转的默认参数
$returnArr = array(
    'total' => 0, //数据总条数
    'pagecount' => 0, //总页数
    'pagesize' => $mydata['pagesize'], //每页显示数据
    'pagenum' => 0, //当前页
    'rows' => array() //数据数组
);

$mem_obj = new kyx_memcache();

$where = ' WHERE 1 '; //查询条件
if(!empty($mydata['categoryid']) && (!empty($mydata['uid']) || !empty($mydata['authorid']))){  //游戏作者对应的专辑视频列表
    //查询条件
    $category_where = $where." AND `va_isshow` = 1 AND `vvl_game_id` = ".$mydata['gameid']." AND `vvl_category_id` = ".$mydata['categoryid']." AND `vvl_type_id` = ".$mydata['typeid'];

    if(!empty($mydata['uid'])){
        $category_where .= " AND `vvl_uid` = ".$mydata['uid'];
    }elseif(!empty($mydata['authorid'])){
        $category_where .= " AND `vvl_author_id` = ".$mydata['authorid'];
    }

    //查询数据总数
    $sql = "SELECT count(1) as num FROM `video_video_list` ".$category_where ;
    $data_count = $conn->count($sql);
    $returnArr['total'] = $data_count;

    $page_max = ceil($data_count/$mydata['pagesize']);//最大页数
    $returnArr['pagecount'] = $page_max;
    $returnArr['pagenum'] = $mydata['pagenum'];

    //LIMIT条件
    $offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
    $limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

    //排序
    $orderby = " ORDER BY `in_date` DESC,`vvl_count` DESC ";

    $sql = "SELECT `id`,`vvl_title`,`vvl_imgurl`,`vvl_imgurl_get`,`vvl_tags`,`in_date`,`vvl_author_id`,`vvl_uid`,`vvl_game_id`,
            (SELECT `gi_name` FROM `video_game_info` WHERE `id` = `vvl_game_id`) AS gi_name
            FROM `video_video_list` ".$category_where.$orderby.$limit;
    $data = $conn->find($sql);
    if(!empty($data)){
        foreach($data as $val){

            $where = $category_where. " AND `id` = ".intval($val['id']);

            //获取专辑图片
            $video_img = !empty($val['vvl_imgurl_get']) ? (LOCAL_URL_DOWN_IMG.$val['vvl_imgurl_get']) : $val['vvl_imgurl'];

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

            //获取视频缓存播放次数
            $a_play_key = 'video_play_num_'.intval($val['id']); //视频播放key
            $a_old_play_val = $mem_obj->get($a_play_key); //获取视频原始播放数

            //视频播放总数
            $vac_play_count_key = 'vac_play_count_'.md5($where); //作者专辑视频播放总数缓存key 'vac_play_count_' + md5(查询条件)
            $temp_data = $mem_obj->get($vac_play_count_key);
            if($temp_data === false){
                $sql = "SELECT SUM(`vvl_count`) AS num FROM `video_video_list` ".$where;
                $temp_data = $conn->get_one($sql);
                $mem_obj->set($vac_play_count_key,$temp_data,300);
            }
            $playnum = isset($temp_data['num']) ? intval($temp_data['num']) : 0;

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
                'title' => filter_search(delete_html($val['vvl_title'])), //视频名称
                'gameid' => $mydata['gameid'], //游戏id
                'gamename' => isset($val['gi_name']) ? $val['gi_name'] : '', //游戏名称
                'authorid' => $mydata['authorid'], //作者id
                'authorname' => $author_name, //作者名称
                'authorimg' => $author_img, //作者头像
                'categoryid' => $mydata['categoryid'], //专辑id
                'playnum' => $playnum  + intval($a_old_play_val), //视频总播放数 + 缓存播放次数
                'imgurl' => $video_img, //视频图片
                'tag' => $tag_name, //视频标签
                'tagcolour' => $tag_colour, //视频标签颜色
                'time' => date('Y-m-d h:i',$val['in_date']) //采集时间
            );
            $returnArr['rows'][] = $json;
        }
    }
}elseif((!empty($mydata['authorid']) || !empty($mydata['uid'])) && empty($mydata['categoryid'])){ //游戏作者专辑列表
    //查询条件
    $where .= " AND `vc_isshow` = 1 AND `vc_game_id` = ".$mydata['gameid']." AND `vc_type_id` = ".$mydata['typeid'];

    if(!empty($mydata['uid'])){
        $where .= " AND `vc_uid` = ".$mydata['uid'];
    }elseif(!empty($mydata['authorid'])){
        $where .= " AND `vc_author_id` = ".$mydata['authorid'];
    }

    //查询数据总数
    $sql = "SELECT count(1) as num FROM `video_category_info` ".$where ;
    $data_count = $conn->count($sql);
    $returnArr['total'] = $data_count;

    $page_max = ceil($data_count/$mydata['pagesize']);//最大页数
    $returnArr['pagecount'] = $page_max;
    $returnArr['pagenum'] = $mydata['pagenum'];

    //LIMIT条件
    $offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
    $limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

    //排序
    $orderby = " ORDER BY `vc_order` DESC ";

    $sql = "SELECT `id`,`vc_name`,`vc_icon`,`vc_icon_get`,`vc_author_id`,`in_date` FROM `video_category_info` ".$where.$orderby.$limit;
    $data = $conn->find($sql);
    if(!empty($data)){
        foreach($data as $val){

            //获取专辑图片
            $category_img = !empty($val['vc_icon_get']) ? (LOCAL_URL_DOWN_IMG.$val['vc_icon_get']) : $val['vc_icon'];

            $temp_where = " WHERE va_isshow = 1 AND `vvl_game_id` = ".$mydata['gameid']." AND `vvl_author_id` = ".intval($val['vc_author_id'])." AND `vvl_category_id` = ".intval($val['id'])." AND `vvl_type_id` = ".$mydata['typeid'];

            //专辑视频总数
            $vac_category_video_num_key = 'vac_category_video_num_'.md5($temp_where); //专辑视频总数缓存key 'vac_category_video_num_' + MD5（查询条件）
            $temp_data = $mem_obj->get($vac_category_video_num_key); //专辑视频总数
            if($temp_data === false){
                $sql = "SELECT COUNT(1) AS num FROM `video_video_list` ".$temp_where;
                $temp_data = $conn->get_one($sql);
                $mem_obj->set($vac_category_video_num_key,$temp_data,300);
            }
            $videonum = isset($temp_data['num']) ? intval($temp_data['num']) : 0;

            $json = array(
                'title' => filter_search(delete_html($val['vc_name'])), //作者名称
                'uid' => $mydata['uid'], //用户id
                'gameid' => $mydata['gameid'], //游戏id
                'typeid' => $mydata['typeid'], //专辑类型
                'authorid' => intval($val['vc_author_id']), //作者id
                'categoryid' => intval($val['id']), //专辑id
                'videonum' => $videonum, //视频数
                'imgurl' => $category_img, //专辑图片
                'time' => intval($val['in_date']) //采集时间
            );
            $returnArr['rows'][] = $json;
        }
    }
}else{ //游戏作者列表
    //查询条件
    $where .= " AND `va_isshow` = 1 AND `va_game_id` = ".$mydata['gameid'];

    //查询数据总数
    $sql = "SELECT count(1) as num FROM `video_author_info` ".$where ;
    $data_count = $conn->count($sql);
    $returnArr['total'] = $data_count;

    $page_max = ceil($data_count/$mydata['pagesize']);//最大页数
    $returnArr['pagecount'] = $page_max;
    $returnArr['pagenum'] = $mydata['pagenum'];

    //LIMIT条件
    $offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
    $limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

    //排序
    $orderby = " ORDER BY `va_order` DESC ";

    $sql = "SELECT `id`,`va_name`,`va_icon`,`va_icon_get`,`in_date` FROM `video_author_info` ".$where.$orderby.$limit;
    $data = $conn->find($sql);
    if(!empty($data)){
        foreach($data as $val){

            //获取作者头像
            $author_img = !empty($val['va_icon_get']) ? (LOCAL_URL_DOWN_IMG.$val['va_icon_get']) : $val['va_icon'];

            //查询条件
            $temp_where = " WHERE va_isshow = 1 AND `vvl_game_id` = ".$mydata['gameid']." AND `vvl_author_id` = ".intval($val['id'])." AND `vvl_type_id` = ".$mydata['typeid'];

            //作者视频总数
            $video_num_key = 'video_num_'.intval($val['id']).'_'.$mydata['gameid'].'_'.$mydata['typeid']; //作者视频总数 'video_num_' + 作者id + 游戏id + 类型id
            $videonum = $mem_obj->get($video_num_key);
            if($videonum === false){
                $sql = "SELECT COUNT(1) AS num FROM `video_video_list` ".$temp_where;
                $temp_data = $conn->get_one($sql);
                $videonum = isset($temp_data['num']) ? intval($temp_data['num']) : 0;
                $mem_obj->set($video_num_key,$videonum,3600);
            }

            //作者视频播放总数
            $play_num_key = 'play_num_'.intval($val['id']).'_'.$mydata['gameid'].'_'.$mydata['typeid']; //作者视频播放总数 'play_num_' + 作者id + 游戏id + 类型id
            $playnum = $mem_obj->get($play_num_key);
            if($playnum === false){
                $sql = "SELECT SUM(`vvl_count`) AS num FROM `video_video_list` ".$temp_where;
                $temp_data = $conn->get_one($sql);
                $playnum = isset($temp_data['num']) ? intval($temp_data['num']) : 0;
                $mem_obj->set($play_num_key,$playnum,3600);
            }

            //获取作者下的所有适视频缓存播放总数
            $mem_play_num_key = 'mem_play_num_'.intval($val['id']).'_'.$mydata['gameid'].'_'.$mydata['typeid']; //作者视频缓存播放总数 'mem_play_num_' + 作者id + 游戏id + 类型id
            $a_old_play_val = $mem_obj->get($mem_play_num_key);
            if($a_old_play_val === false){
                $sql = "SELECT `id` FROM `video_video_list` ".$temp_where;
                $video_id_arr = $conn->find($sql);
                $a_old_play_val = 0;
                if(!empty($video_id_arr)){
                    foreach($video_id_arr as $vval){

                        //获取视频缓存播放次数
                        $a_play_key = 'video_play_num_'.intval($vval['id']); //视频播放key
                        $old_play_val = $mem_obj->get($a_play_key); //获取视频原始播放数
                        $a_old_play_val += intval($old_play_val);
                    }
                }
                $mem_obj->set($mem_play_num_key,$a_old_play_val,3600);
            }


            //最新作品标题拼接
            $des_title_key = 'des_title_'.intval($val['id']).'_'.$mydata['gameid'].'_'.$mydata['typeid']; //作者视频简介 'des_title_' + 作者id + 游戏id + 类型id
            $desc = $mem_obj->get($des_title_key);
            if($desc === false || $desc == null){
                $sql = "SELECT `vvl_title` FROM `video_video_list` ".$temp_where." ORDER BY `in_date` DESC LIMIT 3";
                $temp_data = $conn->find($sql);
                $desc = '';
                if(!empty($temp_data)){
                    $desc .= "最新作品：";
                    foreach($temp_data as $tval){
                        $desc .= filter_search(delete_html($tval['vvl_title']))."、";
                    }
                    $desc = rtrim($desc,'、');
                }else{
                    $desc .= "最新作品：无";
                }
                $mem_obj->set($des_title_key,$desc,3600);
            }

            $json = array(
                'authorid' => intval($val['id']), //作者id
                'videonum' => $videonum, //视频总数
                'playnum' => $playnum  + intval($a_old_play_val), //视频总播放数 + 缓存播放次数
                'typeid' => $mydata['typeid'], //类型
                'gameid' => $mydata['gameid'], //游戏id
                'title' => filter_search(delete_html($val['va_name'])), //作者名称
                'imgurl' => $author_img, //作者头像
                'desc' => $desc, //最新作品拼接描述
                'time' => intval($val['in_date']) //采集时间
            );
            $returnArr['rows'][] = $json;
        }
    }
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);

