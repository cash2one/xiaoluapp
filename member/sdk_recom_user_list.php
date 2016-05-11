<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取sdk推荐用户列表
 * @file: sdk_recom_user_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-11-11  16:22
 * @version 1.0
 **/
include("../config.inc.php");
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");
include('../api/ucenter.config.inc.php');

/*参数*/
$mydata = array();
$mydata['pagenum'] = get_param('pagenum');//当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : intval($mydata['pagenum']);
$mydata['pagesize'] = get_param('pagesize');//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 12 : intval($mydata['pagesize']);
$mydata['packagename'] = get_param('packagename'); //游戏包名
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//游戏包名
if(empty($mydata['packagename'])){
    exit('error! packagename is empty !');
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

//LIMIT条件
$temp_limit = " LIMIT ".($mydata['pagenum'] - 1) * $mydata['pagesize'].",".$mydata['pagesize']." ";

//查询条件
$where = " WHERE `is_recommed` = 2 AND `is_show` = 1 AND (`source` = 2 OR `source` = 3) ";
if($game_id == 18){
    $order = " ORDER BY `is_top` DESC,`uid` ASC ";
}else{
    $order = " ORDER BY `sort` DESC,`uid` ASC ";
    $where .= " AND FIND_IN_SET(".$game_id.",`video_game`) > 0 ";
}

//查询数据总数
$recom_user_list_count_key = 'sdk_recom_user_list_count_'.$game_id; //推荐用户总数缓存key  'sdk_recom_user_list_count_' + 游戏id
$data_count = $mem_obj->get($recom_user_list_count_key); //推荐用户总数
if($data_count === false){
    $sql = "SELECT COUNT(1) AS num FROM `uc_members` AS A ".$where;
    $data_count = $uconn->count($sql);
    $mem_obj->set($recom_user_list_count_key,$data_count,3600);
}

//最大页数
$page_max = ceil($data_count/$mydata['pagesize']);

//LIMIT条件
$offset = ($mydata['pagenum'] - 1) * $mydata['pagesize'];
$limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

//获取推荐用户列表数据
$recom_user_list_data_key = 'sdk_recom_user_list_data_'.$mydata['pagenum'].'_'.$mydata['pagesize'].'_'.$game_id; //推荐用户列表数据缓存key  'recom_user_list_data_' + 当前页 + 每页显示数据 + 游戏id
$returnArr = $mem_obj->get($recom_user_list_data_key); //推荐用户列表数据
if($returnArr === false){

    //定义回转的默认参数
    $returnArr = array(
        'total' => intval($data_count), //数据总条数
        'pagecount' => intval($page_max), //总页数
        'pagesize' => $mydata['pagesize'], //每页显示数据
        'pagenum' => $mydata['pagenum'], //当前页
        'rows' => array() //数据数组
    );

    $sql = "SELECT `uid`,`nickname`,`gender`,`desc`,`source` FROM `uc_members` ".$where.$order.$limit;
    $data = $uconn->find($sql);
    if(!empty($data)){
        foreach($data as $val){

            //生产环境获取大头像md5的接口
            $get_img_url = UC_API . '/api/get_avatar_md5file.php';
            $arr_img = array('uid' => $val['uid']);

            //调用ucenter的头像处理接口
            $json = curl_post($get_img_url,$arr_img);
            $arr_return = json_decode($json,TRUE);

            $md5file = isset($arr_return['md5file']) ? $arr_return['md5file'] : '';

            $json = array(
                'uid' => intval($val['uid']), //用户id
                'authorname' => $val['nickname'], //用户昵称
                'gender' => intval($val['gender']), //用户性别（1：男 2：女 3：未知）
                'authordesc' => $val['desc'], //用户描述
                'authorimg' => UC_API.'/avatar.php?uid='.intval($val['uid']).'&type=real&size=big&md5file='.$md5file, //用户大头像
                'md5file' => $md5file //获取ucenter中心的大图md5值
            );
            $returnArr['rows'][] = $json;
        }
    }

    $mem_obj->set($recom_user_list_data_key,$returnArr,3600);
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);
