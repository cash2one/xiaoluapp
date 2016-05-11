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
$mydata['uid'] = intval(get_param('uid'));//用户ID
$mydata['mac'] = get_param('mac');//用户mac地址
$mydata['imei'] = get_param('imei');//用户imei地址
$mydata['pagenum'] = get_param('pagenum');//当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : intval($mydata['pagenum']);
$mydata['pagesize'] = get_param('pagesize');//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 20 : intval($mydata['pagesize']);
$mydata['searchkey'] = get_param('searchkey'); //搜索关键字
$mydata['searchtype'] = intval(get_param('searchtype')); //搜索类型 0:综合（主播、视频） 1：视频搜索 2：用户搜索 3：标签搜索 4:游戏搜索
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? 1 : 0;
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

//固定小鹿视频游戏id
$game_id = 18;

$mem_obj = new kyx_memcache();

$check_where = '';
if(!empty($mydata['mac']) && $mydata['mac'] <> '00:00:00:00:00:00'){
    $check_where .= " AND `mac` = '".$mydata['mac']."'";
}
if(!empty($mydata['imei'])){
    $check_where .= " AND `imei` = '".$mydata['imei']."'";
}

//查找用户订阅内容
$user_sub_info_key = 'user_sub_info_'.md5($mydata['uid'].$mydata['mac'].$mydata['imei']);
$sub_info = $mem_obj->get($user_sub_info_key);
if($sub_info === false){
    $sub_info = array();
    $sql = "SELECT `subid`,`subtype` FROM `video_user_sub_info` WHERE `status` = 1 ".$check_where;
    $data = $conn->find($sql);
    if(!empty($data)){
        foreach($data as $val){
            $sub_info[] = array(
                'subid' => intval($val['subid']),
                'subtype' => intval($val['subtype'])
            );
        }
    }
    $mem_obj->set($user_sub_info_key,$sub_info,600);
}

if(empty($mydata['searchtype'])){


    $returnArr['rows'][0] = array(
        'title' => '热门主播',
        'row' => array()
    );

    //搜索
    $search = new search();

    //搜索热门用户
    $param = array(
        'keyword' => strtolower($mydata['searchkey']), //搜索关键字
        'game_id' => $game_id, //游戏id（用于不同客户端过滤）
        'index_name' => 'mzw_user', //coreseek索引
        'weight' => '', //权重数组
        'filter' => 1, //获取视频的显示的属性（1：显示 2：隐藏 0：全部）
        'sph_config' => '', //配置数组
        'is_return' => 1 //是否返回数据（0：不返回 1：返回）
    );

    //获取热门主播4个
    $user_arr = $search->hot_user_retrieve($param);
    $count = count($user_arr);
    if(!empty($user_arr)){
        foreach($user_arr as $key => $val){

            $user_file_md5 = "user_file_md5_".$val['uid'];
            $md5file = $mem_obj->get($user_file_md5);
            if($md5file === false){
                //生产环境获取大头像md5的接口
                $get_img_url = UC_API . '/api/get_avatar_md5file.php';
                $arr_img = array('uid' => $val['uid']);

                //调用ucenter的头像处理接口
                $json = curl_post($get_img_url,$arr_img);
                $arr_return = json_decode($json,TRUE);

                $md5file = isset($arr_return['md5file']) ? $arr_return['md5file'] : '';
                $mem_obj->set($user_file_md5,$md5file,3600);
            }

            //检查用户是否订阅该游戏或游戏分类内容
            $subscribe = 0;
            if(!empty($sub_info)){
                foreach($sub_info as $sub){
                    if($sub['subid'] == $val['uid'] && $sub['subtype'] == 2){
                        $subscribe = 1;
                    }
                }
            }

            //用户描述转换
            $user_arr[$key]['authordesc'] = $val['desc'];
            unset($user_arr[$key]['desc']);

            //用户id转换
            $user_arr[$key]['anchorid'] = intval($val['uid']);
            unset($user_arr[$key]['uid']);

            $user_arr[$key]['authorimg'] = $val['authorimg'].'&md5file='.$md5file; //用户大头像
            $user_arr[$key]['md5file'] = $md5file; //获取ucenter中心的大图md5值
            $user_arr[$key]['subid'] = intval($val['uid']); //订阅id
            $user_arr[$key]['subtype'] = 2; //订阅类型（1：标签 2：主播 3：游戏 4：游戏分类）
            $user_arr[$key]['subscribe'] = $subscribe; //是否订阅（1：已订阅 0：未订阅）
        }
    }

    $returnArr['rows'][0]['row'] = $user_arr;

}else{

    $a = 'video_retrieve';
    $index_name = 'mzw_video';
    if($mydata['searchtype'] == 2){
        $a = 'user_retrieve';
        $index_name = 'mzw_user';
    }elseif($mydata['searchtype'] == 3){
        $a = 'video_tag_retrieve';
        $index_name = 'mzw_video_tag';
    }elseif($mydata['searchtype'] == 4){
        $a = 'video_game_retrieve';
        $index_name = 'mzw_video_game';
    }

    //搜索参数
    $param = array(
        'pagenum' => $mydata['pagenum'], //当前页
        'pagesize' => $mydata['pagesize'], //每页显示数据
        'keyword' => strtolower($mydata['searchkey']), //搜索关键字
        'game_id' => $game_id, //游戏id（用于不同客户端过滤）
        'index_name' => $index_name, //coreseek索引
        'weight' => '', //权重数组
        'filter' => 1, //获取视频的显示的属性（1：显示 2：隐藏 0：全部）
        'sph_config' => '', //配置数组
        'is_return' => 1 //是否返回数据（0：不返回 1：返回）
    );

    //搜索
    $search = new search();
    $returnArr = $search->$a($param);

    if(!empty($returnArr['rows']) && $mydata['searchtype'] == 2){
        foreach($returnArr['rows'] as $key => $val){

            $user_file_md5 = "user_file_md5_".$val['uid'];
            $md5file = $mem_obj->get($user_file_md5);
            if($md5file === false){
                //生产环境获取大头像md5的接口
                $get_img_url = UC_API . '/api/get_avatar_md5file.php';
                $arr_img = array('uid' => $val['uid']);

                //调用ucenter的头像处理接口
                $json = curl_post($get_img_url,$arr_img);
                $arr_return = json_decode($json,TRUE);

                $md5file = isset($arr_return['md5file']) ? $arr_return['md5file'] : '';
                $mem_obj->set($user_file_md5,$md5file,3600);
            }

            //检查用户是否订阅该游戏或游戏分类内容
            $subscribe = 0;
            if(!empty($sub_info)){
                foreach($sub_info as $sub){
                    if($sub['subid'] == $val['uid'] && $sub['subtype'] == 2){
                        $subscribe = 1;
                    }
                }
            }

            //用户描述转换
            $returnArr['rows'][$key]['authordesc'] = $val['desc'];
            unset($returnArr['rows'][$key]['desc']);

            //用户id转换
            $returnArr['rows'][$key]['anchorid'] = intval($val['uid']);
            unset($returnArr['rows'][$key]['uid']);

            $returnArr['rows'][$key]['authorimg'] = $val['authorimg'].'&md5file='.$md5file; //用户大头像
            $returnArr['rows'][$key]['md5file'] = $md5file; //获取ucenter中心的大图md5值
            $returnArr['rows'][$key]['subid'] = intval($val['uid']); //订阅id
            $returnArr['rows'][$key]['subtype'] = 2; //订阅类型（1：标签 2：主播 3：游戏 4：游戏分类）
            $returnArr['rows'][$key]['subscribe'] = $subscribe; //是否订阅（1：已订阅 0：未订阅）
        }
    }
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);
