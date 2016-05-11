<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取推荐用户列表
 * @file: recom_user_list.php
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
$mydata['gameid'] = intval(get_param('gameid'));
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

if(empty($mydata['gameid'])){
    $mydata['gameid'] = 2;
}

//查询条件
$where = " WHERE `is_recommed` = 2 AND `is_show` = 1 AND (`source` = 2 OR `source` = 3) ";
if($mydata['gameid'] == 18){
    $order = " ORDER BY `is_top` DESC,`uid` ASC ";
}else{
    $order = " ORDER BY `sort` DESC,`uid` ASC ";
    $where .= " AND FIND_IN_SET(".$mydata['gameid'].",`video_game`) > 0 ";
}

$mem_obj = new kyx_memcache();

//查询数据总数
$recom_user_list_count_key = 'recom_user_list_count_'.$mydata['gameid']; //推荐用户总数缓存key  'recom_user_list_count_' + 游戏id
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
$recom_user_list_data_key = 'recom_user_list_data_'.$mydata['pagenum'].'_'.$mydata['pagesize'].'_'.$mydata['gameid']; //推荐用户列表数据缓存key  'recom_user_list_data_' + 当前页 + 每页显示数据 + 游戏id
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
            //检测是否有专辑列表
            $author_source = 0;
            if(isset($val['source']) && $val['source'] == 3){
                $author_source_key = 'author_source_key_'.intval($val['uid']).'_'.$mydata['gameid']; //用户是否有专辑缓存key 'author_source_key_' + 用户id
                $author_source = $mem_obj->get($author_source_key); //用户是否有专辑
                if($author_source === false){
                    $source_where = " WHERE `vc_isshow` =1 AND `vc_uid` = ".$val['uid']." AND `vc_type_id` = 6 AND `vc_game_id` = ".$mydata['gameid'];
                    $sql = "SELECT `id` FROM `video_category_info` ".$source_where." LIMIT 1";
                    $source_data = $conn->get_one($sql);
                    $author_source = (isset($source_data['id'])) ? 1 : 0;
                    $mem_obj->set($author_source_key,$author_source,3600);
                }
            }

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
                'hascategory' => $author_source, //是否有专辑（1：有 0：没有）
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
