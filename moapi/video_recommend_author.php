<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取金牌推荐作者列表,并JSON内容进行输出返回
 * @file: video_recommend_author.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-10-25  16:05
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

$mem_obj = new kyx_memcache();

$video_recommend_data_key = 'video_recommend_data'; //金牌推荐作者缓存key 'video_recommend_data'
$returnArr = $mem_obj->get($video_recommend_data_key); //视频标签数组
if($returnArr === false){
    //定义回转的默认参数
    $returnArr = array(
        'rows' => array()
    );

    $sql = "SELECT `id`,`va_name`,`va_game_id`,`va_icon`,`va_icon_get`,`va_intro` FROM `video_author_info` WHERE `va_isshow` = 1 AND `va_recom` = 1 ORDER BY `va_order` DESC";
    $data = $conn->find($sql);
    if(!empty($data)){
        foreach($data as $val){

            //作者头像
            $author_img = (isset($val['va_icon_get']) && !empty($val['va_icon_get'])) ? (LOCAL_URL_DOWN_IMG.$val['va_icon_get']) : (isset($val['va_icon']) ? $val['va_icon'] : '');

            $returnArr['rows'][] = array(
                'gameid' => intval($val['va_game_id']), //游戏id
                'authorid' => intval($val['id']), //作者id
                'authorname' => $val['va_name'], //作者名称
                'authorimg' => $author_img, //作者头像
                'authordesc' => $val['va_intro'] //作者简介
            );
        }
        $mem_obj->set($video_recommend_data_key,$returnArr,1800);
    }
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);





