<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取所有视频列表（用于定时扫描不可播放的视频）
 * @file: video_list_all.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-11-09  14:54
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

$key = get_param('key'); //验证key
$pagenum = intval(get_param('pagenum')); //当前页
$pagenum = empty($pagenum) ? 1 : $pagenum;
$pagesize = intval(get_param('pagesize')); //每页显示数据
$pagesize = empty($pagesize) ? 100 : $pagesize;

if($key != md5(URL_KYX_KEY.'_'.SYS_URL_KYX_KEY)){
    exit(responseJson(array('操作错误')));
}

//LIMIT条件
$offset = ($pagenum - 1) * $pagesize;
$limit = " LIMIT ".$offset." , ".$pagesize." ";

$sql = "SELECT `id`,`vvl_title`,`vvl_server_url`,`vvl_sourcetype`,`vvl_playurl_get`
        FROM `video_video_list` WHERE `va_isshow` = 1 AND `vvl_server_url` != '' ".$limit;
$data = $conn->find($sql);

$returnArr = array();
//获取游戏下载地址
if(!empty($data) && is_type($data,'Array')){
    foreach($data as $key => $video_info){

        //如果本地视频地址不为空
        $url = '';
        if(!empty($video_info['vvl_server_url'])){
            if($video_info['vvl_sourcetype'] == 14){
                $url = $video_info['vvl_server_url'];
            }else{
                $url = CDN_LESHI_URL_DOWN.$video_info['vvl_server_url'];
            }
        }

        $returnArr[$key] = array(
            'id' => $video_info['id'], //视频id
            'title' => $video_info['vvl_title'],
            'url' => $url, //多个播放地址取第一个
        );
    }
}

exit(responseJson($returnArr));