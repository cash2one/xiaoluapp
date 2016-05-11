<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取视频主播详情列表
 * @file: video_user_info.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-23  16:44
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
include_once("../db.ucenter.config.inc.php");
include_once('../api/ucenter.config.inc.php');

/*参数*/
$mydata = array();
$mydata['uid'] = intval(get_param('uid'));//当前登陆用户ID
$mydata['mac'] = get_param('mac');//用户mac地址
$mydata['imei'] = get_param('imei');//用户imei地址
$mydata['anchorid'] = intval(get_param('anchorid'));//主播id
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

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

//定义回转的默认参数
$returnArr = array(
    'rows' => array()
);

//获取主播信息
$data_key = "xl_user_info_".$mydata['anchorid'];
$data = $mem_obj->get($data_key);
if($data === false){
    $sql = "SELECT `uid`,`nickname`,`gender`,`desc`,`source` FROM `uc_members` WHERE `uid` = ".$mydata['anchorid'];
    $data = $uconn->get_one($sql);
    $mem_obj->set($data_key,$data,1800);
}
if(!empty($data)){

    $user_file_md5 = "user_file_md5_".$val['uid'];
    $md5file = $mem_obj->get($user_file_md5);
    if($md5file === false){
        //生产环境获取大头像md5的接口
        $get_img_url = UC_API . '/api/get_avatar_md5file.php';
        $arr_img = array('uid' => $data['uid']);

        //调用ucenter的头像处理接口
        $json = curl_post($get_img_url,$arr_img);
        $arr_return = json_decode($json,TRUE);

        $md5file = isset($arr_return['md5file']) ? $arr_return['md5file'] : '';
        $mem_obj->set($user_file_md5,$md5file,3600);
    }

    //检查用户是否订阅该主播
    $subscribe = 0;
    if(!empty($sub_info)){
        foreach($sub_info as $sub){
            if($sub['subid'] == $data['uid'] && $sub['subtype'] == 2){
                $subscribe = 1;
            }
        }
    }

    $returnArr['rows'][0]['anchorid'] = intval($data['uid']); //主播id
    $returnArr['rows'][0]['authorname'] = $data['nickname']; //主播名称
    $returnArr['rows'][0]['gender'] = intval($data['gender']); //用户性别（1：男 2：女 3：未知）
    $returnArr['rows'][0]['subid'] = intval($data['uid']); //订阅id
    $returnArr['rows'][0]['subtype'] = 2; //订阅类型（1：标签 2：主播 3：游戏 4：游戏分类）
    $returnArr['rows'][0]['subscribe'] = $subscribe; //是否订阅（1：已订阅 0：未订阅）
    $returnArr['rows'][0]['authordesc'] = $data['desc']; //主播描述
    $returnArr['rows'][0]['authorimg'] = UC_API.'/avatar.php?uid='.intval($data['uid']).'&type=real&size=big&md5file='.$md5file; //用户头像
    $returnArr['rows'][0]['md5file'] = $md5file; //头像MD5

    //获取主播获取的点赞数
    $user_like_num_key = "xl_user_like_num_".$mydata['anchorid'];
    $likenum = $mem_obj->get($user_like_num_key);
    if($likenum === false){
        $sql = "SELECT COUNT(1) AS num FROM `video_up_down` AS A
                LEFT JOIN `video_video_list` AS B ON A.`v_id` = B.`id`
                WHERE B.`va_isshow` = 1 AND B.`vvl_uid` = ".$mydata['anchorid'];
        $likenum = $conn->count($sql);
        $mem_obj->set($user_like_num_key,$likenum,300);
    }
    $returnArr['rows'][0]['likenum'] = intval($likenum);

    //获取主播视频数
    $user_video_num = "xl_user_video_num_".$mydata['anchorid'];
    $videonum = $mem_obj->get($user_video_num);
    if($videonum === false){
        $sql = "SELECT COUNT(1) AS num FROM `video_video_list` WHERE `va_isshow` = 1 AND `vvl_uid` = ".$mydata['anchorid'];
        $videonum = $conn->count($sql);
        $mem_obj->set($user_video_num,$videonum,1800);
    }
    $returnArr['rows'][0]['videonum'] = intval($videonum);

    //获取主播专辑数
    $user_albunm_num = "xl_user_album_num_".$mydata['anchorid'];
    $albumnum = $mem_obj->get($user_albunm_num);
    if($albumnum === false){
        $sql = "SELECT COUNT(1) AS num FROM `video_category_info` WHERE `vc_isshow` = 1 AND `vc_p_id` = 0 AND `vc_type_id` = 6 AND `vc_uid` = ".$mydata['anchorid'];
        $albumnum = $conn->count($sql);
        $mem_obj->set($user_albunm_num,$albumnum,1800);
    }
    $returnArr['rows'][0]['albumnum'] = intval($albumnum);

    //获取主播粉丝数
    $user_fans_num = "xl_user_fans_num_".$mydata['anchorid'];
    $fansnum = $mem_obj->get($user_fans_num);
    if($fansnum === false){
        $sql = "SELECT COUNT(1) AS num FROM `video_user_sub_info` WHERE `status` = 1 AND `subtype` = 2 AND `subid` = ".$mydata['anchorid'];
        $fansnum = $conn->count($sql);
        $mem_obj->set($user_fans_num,$fansnum,300);
    }
    $returnArr['rows'][0]['fansnum'] = intval($fansnum);
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);
