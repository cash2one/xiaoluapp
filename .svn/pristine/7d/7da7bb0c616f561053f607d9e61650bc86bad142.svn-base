<?php
/**
 * @copyright: @快游戏 2014
 * @description: SDK视频列表页，返回请求类型的视频列表
 * @file: sdk_video_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-08-04  15:44
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");
include_once('../api/ucenter.config.inc.php');

/*参数*/
$mydata = array();
$mydata['pagenum'] = intval(get_param('pagenum'));//当前页
$mydata['pagesize'] = intval(get_param('pagesize'));//每页大小
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : $mydata['pagenum']; //默认第一页
$mydata['pagesize'] = empty($mydata['pagesize']) ? 30 : $mydata['pagesize']; //默认每页30条数据
$mydata['packagename'] = get_param('packagename'); //游戏包名
$mydata['typeid'] = intval(get_param('typeid'));//视频类型（1 ：视频解说者 2 ： 视频专辑）
$mydata['appid'] = intval(get_param('appid'));//视频类型ID
$mydata['uid'] = intval(get_param('uid'));//用户ID
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

if(empty($mydata['packagename'])){
	echo('error! packagename is empty!!');
	exit;
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
$where = " WHERE `va_isshow` = 1 AND `vvl_sourcetype` <> 11 AND `vvl_game_id` = ".$game_id;

//视频类型
if($mydata['typeid'] == 1){
    if(!empty($mydata['uid'])){
        $where .= " AND `vvl_uid` = ".$mydata['uid']." ";
    }elseif(!empty($mydata['appid'])){
        $where .= " AND `vvl_author_id` = ".$mydata['appid']." ";
    }
}elseif($mydata['typeid'] == 2 && !empty($mydata['appid'])){
    $where .= " AND `vvl_category_id` = ".$mydata['appid']." ";
}

//排序条件（按视频本地播放数排序）
$orderby = ' ORDER BY `vvl_count` DESC ';

//查总数据行数
$sql = "SELECT count(1) as num FROM `video_video_list` ".$where ;
$data_count = $conn->count($sql);

$page_max = ceil($data_count/$mydata['pagesize']);//最大页数
$param_page = ($mydata['pagenum']-1) * ($mydata['pagesize']);//查询从第几行开始查

//初始化要返回的数据
$returnArr = array(
    'total' => $data_count, //数据总条数
    'pagecount' => $page_max, //总页数
    'pagesize' => $mydata['pagesize'], //每页显示数据
    'pagenum' => $mydata['pagenum'], //当前页
    'rows' => array() //数据数组
);

//查数据
$sql_data = "SELECT `id`,`vvl_title`,`vvl_imgurl`,`vvl_imgurl_get`,`in_date`,`vvl_count`,`vvl_author_id`,`vvl_uid` ".
            " FROM `video_video_list` ". $where.$orderby." LIMIT ".$param_page .",".$mydata['pagesize'];
$data = $conn->find($sql_data);

//列出视频数据
foreach ($data as $val){
    //视频截图
    $tmp_img_url = empty($val['vvl_imgurl_get']) ? $val['vvl_imgurl'] : (LOCAL_URL_DOWN_IMG.$val['vvl_imgurl_get']);

    //获取视频缓存播放次数
    $a_play_key = 'video_play_num_'.intval($val['id']); //视频播放key
    $a_old_play_val = $mem_obj->get($a_play_key); //获取视频原始播放数

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

	$arr = array(
		'appid' => $val['id'],//视频ID
		'title' => $val['vvl_title'],//视频标题
        'authorname' => $author_name, //作者名称
        'authorimg' => $author_img, //作者头像
        'playnum' => intval($val['vvl_count']) + intval($a_old_play_val),//视频本地播放数 + 缓存播放次数
		'imgurl' => $tmp_img_url,//视频图片
		'time' => $val['in_date']//采集时间
	);
	$returnArr['rows'][]=$arr;
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);

