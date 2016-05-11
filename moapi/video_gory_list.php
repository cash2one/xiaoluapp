<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取视频标签分类数据列表,并JSON内容进行输出返回（用于APP上传视频选游戏标签）
 * @file: video_gory_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-12-11  14:36
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

$mem_obj = new kyx_memcache();

$video_gory_list_data_key = 'video_gory_list_data'; //所有视频标签分类数据缓存key  'video_gory_list_data'
$returnArr = $mem_obj->get($video_gory_list_data_key); //所有视频标签分类数据
if($returnArr === false){

    //定义回转的默认参数
    $returnArr = array(
        'rows' => array() //数据数组
    );

    $sql = "SELECT `id`,`gi_packname`,`gi_name`,`gi_logo` FROM `video_game_info` WHERE `gi_isshow` = 1 AND `id` IN (1,2,3,4,11,13,14,15)";
    $game_data = $conn->find($sql);
    if(!empty($game_data)){
        foreach($game_data as $val){
            $sql = "SELECT `vtc_id`,`vtc_name` FROM `mzw_video_tags_category` WHERE `vtc_status` = 1 AND `vtc_type` = 2 AND `vtc_game_id` = ".intval($val['id']);
            $category_data = $conn->find($sql);
            if(!empty($category_data)){
                $temp_arr = array();
                foreach($category_data as $cval){
                    $temp_arr[] = array(
                        'id' => intval($cval['vtc_id']),
                        'name' => $cval['vtc_name']
                    );
                }
                $returnArr['rows'][] = array(
                    'gameid' => intval($val['id']),
                    'packagename' => $val['gi_packname'],
                    'gamename' => $val['gi_name'],
                    'img' => LOCAL_URL_DOWN_IMG.$val['gi_logo'],
                    'row' => $temp_arr
                );
            }
        }
    }
    $mem_obj->set($video_gory_list_data_key,$returnArr,3600);
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);
