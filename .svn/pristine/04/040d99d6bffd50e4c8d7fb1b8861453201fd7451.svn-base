<?PHP
/**
 * @copyright: @快游戏 2015
 * @description: 搜索热词推荐
 * @file: search_word_recommend.php
 * @author: xiongjianbang
 * @charset: UTF-8
 * @time: 2015-06-04  15:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

$mydata['key'] = get_param('key');//验证KEY
$request = $_SERVER['REQUEST_METHOD']; //请求方式

//key校验
$key_auth = kyx_authorize_key($mydata['key'],$request);
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//客户端搜索界面推荐游戏专区里配置游戏
$sql = "SELECT `gv_id` FROM `mzw_game_m_a_relation` WHERE ga_id=24 ORDER BY `g_order` DESC";
$data = $conn->find($sql);
$arr_game = array();
if(!empty($data)){
	foreach ($data as $value) {
	    $temp = array();
	    $gv_id = intval($value['gv_id']);
	    $sql = "SELECT `gv_title` FROM  `mzw_game_version` WHERE `gv_id`={$gv_id} AND `gv_m_status`=1";
	    $res = $conn->get_one($sql);
	    if(empty($res)){
	    	continue;
	    }
	    $title = trim($res['gv_title']);
	    $temp = array('gv_id'=>$gv_id,'title'=>$title,'type'=>1);
	    $arr_game[] = $temp;
	    unset($temp);
	}
}
unset($data);

//搜索热词
$sql = "SELECT `mhw_hotword`,`mhw_char` FROM `mzw_hot_word` WHERE `mhw_status`=1 AND `mhw_recommend`=1 AND (`mhw_type`=2 OR `mhw_type`=3) ORDER BY `mhw_weight` DESC";
$data = $conn->find($sql);
$arr_hot = array();
if(!empty($data)){
	foreach ($data as $value) {
	    $temp = array();
	    $temp['simple_pinyin'] = strtoupper($value['mhw_char']);
	    $temp['title'] = $value['mhw_hotword'];
	    $temp['type'] =2;
	    $arr_hot[] = $temp;
	}
}
$arr = array_merge($arr_game,$arr_hot);

$returnArr = array(
    'total'=>count($arr), //数据总数
    'rows'=>$arr, //数据数组
);
$str_encode = responseJson($returnArr,true);

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
    exit(responseJson($returnArr,false));
}
exit($str_encode);