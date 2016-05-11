<?php
/**
 * @copyright: @快游戏 2014
 * @description: 视频分类列表，返回视频分类列表或者视频解说者列表
 * @file: sdk_video_author_category_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-08-12  11:16
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['pagenum'] = intval(get_param('pagenum')); //当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : $mydata['pagenum'];
$mydata['pagesize'] = intval(get_param('pagesize'));//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 20 : $mydata['pagesize'];
$mydata['typeid'] = intval(get_param('typeid'));//视频类型（1 ：视频解说者 2 ： 视频专辑）
$mydata['pid'] = intval(get_param('pid'));//分类父级id
$mydata['packagename'] = get_param('packagename'); //游戏包名
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//视频分类不在允许范围内
if(empty($mydata['typeid']) || !in_array($mydata['typeid'],array(1,2))){
	echo('error! typeid is error!!');
	exit;
}

//游戏包名
if(empty($mydata['packagename'])){
    echo('error! packagename is empty!!');
    exit;
}

$mem_obj = new kyx_memcache();

//LIMIT条件
$temp_limit = " LIMIT ".($mydata['pagenum'] - 1) * $mydata['pagesize'].",".$mydata['pagesize']." ";

$where = ' ';
$where .= ' AND vc_type_id='.$mydata['typeid'];

//初始化返回数组
$returnArr=array(
    'total' => 0,
    'pagecount' => $mydata['pagesize'],
    'pagenum' => $mydata['pagenum'],
    'rows' => array()
);

//获取包名对应游戏id
$game_id_key = 'game_id_package_key_'.$mydata['packagename'];
$game_id = $mem_obj->get($game_id_key);
if($game_id === false){
    $game_sql = "SELECT `id` FROM `video_game_info` WHERE `gi_packname` = '".$mydata['packagename']."'";
    $game_data = $conn->get_one($game_sql);
    $game_id = isset($game_data['id']) ? intval($game_data['id']) : 0;
    $mem_obj->set($game_id_key,$game_id,3600);
}

$reclassify = (($mydata['packagename'] == 'com.rayark.Cytus.full' || $mydata['packagename'] == 'com.telltalegames.minecraft100') && empty($mydata['pid'])) ? 1 : 0;

$mem_obj = new kyx_memcache();

//如果是作者解说
if($mydata['typeid'] == 1){

    //解说人(即作者)总数
    $tmp_count_sql = 'SELECT COUNT(1) AS num FROM video_author_info WHERE va_isshow=1 AND va_game_id='.$game_id;
    $tmp_count_type = $conn->get_one($tmp_count_sql);
    $returnArr['total'] = intval($tmp_count_type['num']);

	//查找解说人(即作者)的信息
	$tmp_sql = 'SELECT id,va_name FROM video_author_info WHERE va_isshow=1 AND va_game_id='.$game_id.' ORDER BY va_order DESC,id DESC '.$temp_limit;
	$tmp_type = $conn->find($tmp_sql);
	foreach ($tmp_type as $val){
        //获取分类下视频总数跟第一个视频截图、视频总播放数
        $videocount_key = 'videocount_'.$game_id.'_'.$val['id']; //作者视频总数key 'videocount_' + 游戏id + 作者id
        $playcount_key = 'playcount_'.$game_id.'_'.$val['id']; //作者视频播放总数key 'playcount_' + 游戏id + 作者id
        $imgurl_key = 'img_url_'.$game_id.'_'.$val['id']; //作者显示图片key 'img_url_' + 游戏id + 作者id
        $videocount = $mem_obj->get($videocount_key); //视频总数val
        $playcount = $mem_obj->get($playcount_key); //视频播放总数val
        $imgurl = $mem_obj->get($imgurl_key); //图片val
        if($videocount === false || $playcount === false || $imgurl === false){
            $tmp_video = "SELECT COUNT(1) AS num,SUM(vvl_count) AS play_count,vvl_imgurl,vvl_imgurl_get FROM `video_video_list`
                          WHERE `va_isshow` = 1 AND `vvl_game_id` = ".$game_id." AND `vvl_author_id` = ".$val['id'];
            $tmp_video = $conn->get_one($tmp_video);

            $mem_obj->set($videocount_key,$tmp_video['num'],3600); //设置作者视频总数缓存
            $mem_obj->set($playcount_key,$tmp_video['play_count'],3600); //设置作者视频播放数缓存

            //视频截图
            $tmp_img_url = empty($tmp_video['vvl_imgurl_get']) ? $tmp_video['vvl_imgurl'] : (LOCAL_URL_DOWN_IMG.$tmp_video['vvl_imgurl_get']);
            $mem_obj->set($imgurl_key,$tmp_img_url,3600); //设置作者显示图片缓存
        }

        $returnArr['rows'][] = array(
            'appid' => intval($val['id']),   //分类id
            'pid' => 0, //父级id
            'name' => $val['va_name'],  //分类名称
            'iconpath' => $imgurl, //分类图标（第一张视频截图）
            'reclassify' => 0,
            'videocount' => intval($videocount), //视频总数
            'playcount' => intval($playcount) //视频总播放数
        );
	}
}elseif($mydata['typeid'] == 2){

    //专辑分类总数（即集锦 ID：4）
    $tmp_count_sql = "SELECT COUNT(1) AS num FROM `video_category_info` WHERE vc_isshow=1 AND vc_type_id = 4 AND vc_p_id = ".$mydata['pid']." AND vc_game_id=".$game_id;
    $tmp_count_type = $conn->get_one($tmp_count_sql);
    $returnArr['total'] = intval($tmp_count_type['num']);

    //专辑分类列表（即集锦 ID：4）
    $tmp_sql = "SELECT id,vc_p_id,vc_name,vc_icon,vc_icon_get,vc_type_id,vc_game_id,in_date ".
                " FROM `video_category_info` WHERE vc_isshow=1 AND vc_type_id = 4 AND vc_p_id = ".$mydata['pid']." AND vc_game_id=".$game_id
                ." ORDER BY vc_order DESC,id DESC ".$temp_limit;
    $tmp_type = $conn->find($tmp_sql);
    foreach ($tmp_type as $val){

        //获取分类下视频总数跟视频总播放数
        $sdk_category_vc = 'sdk_category_vc_'.$game_id.'_'.$val['id']; //专辑视频总数key 'sdk_category_vc_' + 游戏id + 专辑id
        $sdk_category_pc = 'sdk_category_pc_'.$game_id.'_'.$val['id']; //专辑视频播放总数key 'sdk_category_pc_' + 游戏id + 专辑id
        $videocount = $mem_obj->get($sdk_category_vc); //专辑视频总数val
        $playcount = $mem_obj->get($sdk_category_pc); //专辑视频播放总数val
        if($videocount === false || $playcount === false){
            $video_where = " WHERE `va_isshow` = 1 AND `vvl_game_id` = ".$game_id;
            if(($mydata['packagename'] == 'com.rayark.Cytus.full' || $mydata['packagename'] == 'com.telltalegames.minecraft100') && empty($mydata['pid'])){
                $temp_sql = "SELECT `id` FROM `video_category_info` WHERE `vc_isshow` = 1 AND `vc_type_id` = 4 AND `vc_game_id` = ".$game_id." AND `vc_p_id` = ".$val['id'];
                $tmp_data = $conn->find($temp_sql);
                if(!empty($tmp_data)){
                    $tmp_str = '';
                    foreach($tmp_data as $tmp_val){
                        $tmp_str .= $tmp_val['id'].",";
                    }
                    $tmp_str = rtrim($tmp_str,',');
                    $video_where .= " AND `vvl_category_id` IN (".$tmp_str.")";
                }
            }else{
                $video_where .= " AND `vvl_category_id` = ".$val['id'];
            }
            $video_where .= " AND `vvl_sourcetype` <> 11 ";

            $tmp_video = "SELECT COUNT(1) AS num,SUM(vvl_count) AS play_count FROM `video_video_list` ".$video_where;
            $tmp_video = $conn->get_one($tmp_video);

            //获取分类下所有视频
            $mem_all_num = 0;
            $tmp_vid_sql = "SELECT `id` FROM `video_video_list` ".$video_where;
            $tmp_vid_arr = $conn->find($tmp_vid_sql);
            if(!empty($tmp_vid_arr)){
                foreach($tmp_vid_arr as $vval){
                    $a_play_key = 'video_play_num_'.intval($vval['id']); //视频播放key
                    $a_old_play_val = $mem_obj->get($a_play_key); //获取视频原始播放数
                    $mem_all_num += intval($a_old_play_val);
                }
            }

            $videocount = isset($tmp_video['num']) ? intval($tmp_video['num']) : 0;
            $playcount = isset($tmp_video['play_count']) ? (intval($tmp_video['play_count']) + $mem_all_num) : 0;

            $mem_obj->set($sdk_category_vc,$videocount,3600); //设置专辑视频总数缓存
            $mem_obj->set($sdk_category_pc,$playcount,3600); //设置专辑视频播放数缓存
        }

        //专辑截图
        $tmp_img_url = empty($val['vc_icon_get']) ? $val['vc_icon'] : (LOCAL_URL_DOWN_IMG.$val['vc_icon_get']);

        if(!empty($videocount)){
            $returnArr['rows'][] = array(
                'appid' => intval($val['id']),//专辑ID
                'name' => $val['vc_name'],//专辑标题
                'iconpath' => $tmp_img_url,//专辑图片
                'pid' => intval($val['id']), //父级id
                'reclassify' => $reclassify,
                'videocount' => $videocount, //视频总数
                'playcount' => $playcount //视频总播放数
            );
        }
    }
}

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
	echo($tmp_sql);
	var_dump($returnArr);
	exit;
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);

