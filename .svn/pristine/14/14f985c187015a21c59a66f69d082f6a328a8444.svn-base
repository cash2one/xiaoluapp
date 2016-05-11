<?PHP
/**
 * @copyright: @快游戏 2015
 * @description: 视频搜索热词推荐
 * @file: video_search_word_recommend.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2016-02-16  18:37
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? 1 : 0;
$mydata['key'] = get_param('key');//验证KEY
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key校验
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

$mem_obj = new kyx_memcache();

//定义回转的默认参数
$returnArr = array(
    'total' => 0, //数据总数
    'rows' => array() //数据数组
);

//搜索热词
$data_key = "xl_search_word_recommend";
$data = $mem_obj->get($data_key);
if($data === false){
    $sql = "SELECT DISTINCT `mhw_hotword`,`mhw_char` FROM `mzw_hot_word`
            WHERE `mhw_status` = 1 AND `mhw_type` = 2 AND `mhw_source` <> 1
            ORDER BY `mhw_recommend` DESC,`mhw_weight` DESC LIMIT 50";
    $data = $conn->find($sql);
    $mem_obj->set($data_key,$data,3600);
}
if(!empty($data)){
    $returnArr['total'] = count($data);
	foreach ($data as $val) {
        $returnArr['rows'][] = array(
            'title' => $val['mhw_hotword'] //搜索关键字
        );
	}
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);