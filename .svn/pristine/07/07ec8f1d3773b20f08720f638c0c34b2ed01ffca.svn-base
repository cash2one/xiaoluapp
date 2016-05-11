<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取视频搜索数据列表,并JSON内容进行输出返回
 * @file: video_search.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-10-28  10:09
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");
include('../api/ucenter.config.inc.php');
include_once(WEBPATH_DIR."include/search.class.php");//搜索操作
include_once(WEBPATH_DIR."include/search.model.php");//搜索数据操作

/*参数*/
$mydata = array();
$mydata['pagenum'] = get_param('pagenum');//当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : intval($mydata['pagenum']);
$mydata['pagesize'] = get_param('pagesize');//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 20 : intval($mydata['pagesize']);
$mydata['searchkey'] = get_param('searchkey'); //搜索关键字
$mydata['searchtype'] = get_param('searchtype'); //搜索类型 1：视频搜索 2：用户搜索 3：标签搜索
$mydata['gameid'] = intval(get_param('gameid'));//游戏id
$mydata['gamepackagename'] = get_param('gamepackagename'); //游戏包名
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//搜索关键字判断
if(empty($mydata['searchkey'])){
    exit('searchey empty');
}

if(empty($mydata['gameid'])){
    $mydata['gameid'] = 18;
}

$mem_obj = new kyx_memcache();

if(empty($mydata['gameid']) && !empty($mydata['gamepackagename'])){
    //获取包名对应游戏id
    $game_id_key = 'game_id_package_key_'.$mydata['packagename'];
    $game_id = $mem_obj->get($game_id_key);
    if($game_id === false){
        $game_sql = "SELECT `id` FROM `video_game_info` WHERE `gi_packname` = '".$mydata['packagename']."' LIMIT 1";
        $game_data = $conn->get_one($game_sql);
        $mydata['gameid'] = isset($game_data['id']) ? intval($game_data['id']) : 0;
        $mem_obj->set($game_id_key,$mydata['gameid'],3600);
    }
}

//游戏id判断
if(empty($mydata['gameid'])){
    exit('gameid empty');
}

$a = 'video_retrieve';
$index_name = 'mzw_video';
if($mydata['searchtype'] == 2){
    $a = 'user_retrieve';
    $index_name = 'mzw_user';
}elseif($mydata['searchtype'] == 3){
    $a = 'video_tag_retrieve';
    $index_name = 'mzw_video_tag';
}

//搜索参数
$param = array(
    'pagenum' => $mydata['pagenum'], //当前页
    'pagesize' => $mydata['pagesize'], //每页显示数据
    'keyword' => strtolower($mydata['searchkey']), //搜索关键字
    'game_id' => $mydata['gameid'], //游戏id（用于不同客户端过滤）
    'index_name' => $index_name, //coreseek索引
    'weight' => '', //权重数组
    'filter' => 1, //获取视频的显示的属性（1：显示 2：隐藏 0：全部）
    'sph_config' => '' //配置数组
);

//搜索
$search = new search();
$search->$a($param);
