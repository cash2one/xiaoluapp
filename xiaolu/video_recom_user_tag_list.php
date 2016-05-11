<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取小鹿游戏推荐用户列表
 * @file: video_recom_user_tag_list.php
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
$mydata['uid'] = intval(get_param('uid'));//用户ID
$mydata['mac'] = get_param('mac');//用户mac地址
$mydata['imei'] = get_param('imei');//用户imei地址
$mydata['id'] = intval(get_param('id'));  //关联游戏id
$mydata['type'] = intval(get_param('type')); //导航类型（1：游戏 2：游戏分类 3：专区 4：标签）
$mydata['datatype'] = intval(get_param('datatype')); //返回数据类型（1：标签 2：主播 3：游戏）
$mydata['datatype'] = empty($mydata['datatype']) ? 1 : $mydata['datatype'];
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//定义回转的默认参数
$returnArr = array(
    'rows' => array() //数据数组
);

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

if(!empty($mydata['id']) && $mydata['type'] == 1){ //游戏
    if($mydata['datatype'] == 1){
        //获取标签列表
        $temp_tag_arr_key = "xl_tag_rutl_".$mydata['id'].$mydata['type'];
        $temp_tag_arr = $mem_obj->get($temp_tag_arr_key);
        if($temp_tag_arr === false){
            $temp_tag_arr = array();
            $sql = "SELECT `vtc_id`,`vtc_name` FROM `video_game_tags`
                    WHERE `vtc_type` = 2 AND `vtc_start_recom` = 1 AND `vtc_status` = 1 ORDER BY rand() LIMIT 18";
            $data = $conn->find($sql);
            if(!empty($data)){
                foreach($data as $val){
                    $json = array(
                        'id' => intval($val['vtc_id']), //标签id
                        'title' => $val['vtc_name'], //标签名称
                        'type' => 1 //id类型（1：标签 2：主播 3：游戏 4：游戏分类）
                    );
                    $temp_tag_arr[] = $json;
                }
            }
            $mem_obj->set($temp_tag_arr_key,$temp_tag_arr,1800);
        }

        //过滤，6的倍数，不足6个不返回
        $return_arr = array();
        $count = count($temp_tag_arr);
        $basenum = $count/6;
        if(empty($basenum)){
            $return_arr = $temp_tag_arr;
        }else{
            for($i = 0;$i < ($basenum * 6);$i++){
                $return_arr[] = $temp_tag_arr[$i];
            }
        }

        $returnArr['rows'][] = array(
            'title' => '热门标签',
            'row' => $return_arr
        );
    }else{

        //获取推荐主播列表
        $where = " WHERE FIND_IN_SET(".$mydata['id'].",`video_game`) > 0 AND `is_show` = 1 AND (`source` = 2 OR `source` = 3) ";

        //排序
        $order = " ORDER BY `xl_recommed` DESC,video_num DESC ";

        //获取推荐用户列表数据
        $temp_user_arr = array();
        $data_key = "xl_user_rutl_data_".md5($where.$order);
        $data = $mem_obj->get($data_key);
        if($data === false){
            $sql = "SELECT `uid`,`nickname`,`gender`,`desc`,`source` FROM `uc_members` ".$where.$order.' LIMIT 16';
            $data = $uconn->find($sql);
            $mem_obj->set($data_key,$data,3600);
        }
        if(!empty($data)){
            foreach($data as $val){

                //生产环境获取大头像md5的接口
                $user_file_md5 = "user_file_md5_".$val['uid'];
                $md5file = $mem_obj->get($user_file_md5);
                if($md5file === false){
                    $get_img_url = UC_API . '/api/get_avatar_md5file.php';
                    $arr_img = array('uid' => $val['uid']);

                    //调用ucenter的头像处理接口
                    $json = curl_post($get_img_url,$arr_img);
                    $arr_return = json_decode($json,TRUE);

                    $md5file = isset($arr_return['md5file']) ? $arr_return['md5file'] : '';
                    $mem_obj->set($user_file_md5,$md5file,3600);
                }

                $json = array(
                    'anchorid' => intval($val['uid']), //主播id
                    'authorname' => $val['nickname'], //用户昵称
                    'subid' => intval($val['uid']), //订阅id
                    'subtype' => 2, //订阅类型（1：标签 2：主播 3：游戏 4：游戏分类）
                    'subscribe' => 0, //是否订阅（1：已订阅 0：未订阅）
                    'gender' => intval($val['gender']), //用户性别（1：男 2：女 3：未知）
                    'authordesc' => $val['desc'], //用户描述
                    'authorimg' => UC_API.'/avatar.php?uid='.intval($val['uid']).'&type=real&size=big&md5file='.$md5file, //用户大头像
                    'md5file' => $md5file //获取ucenter中心的大图md5值
                );

                //检查用户是否订阅该主播
                if(!empty($sub_info)){
                    foreach($sub_info as $sub){
                        if($sub['subid'] == $val['uid'] && $sub['subtype'] == 2){
                            $json['subscribe'] = 1;
                        }
                    }
                }

                $temp_user_arr[] = $json;
            }
        }

        //过滤，4的倍数，不足4个有多少个返回多少个
        $return_arr = array();
        $count = count($temp_user_arr);
        $basenum = $count/4;
        if(empty($basenum)){
            $return_arr = $temp_user_arr;
        }else{
            for($i = 0;$i < ($basenum * 4);$i++){
                $return_arr[] = $temp_user_arr[$i];
            }
        }

        $returnArr['rows'][] = array(
            'title' => '热门主播',
            'row' => $return_arr
        );
    }

}elseif(!empty($mydata['id']) && $mydata['type'] == 2){ //游戏分类

    if($mydata['datatype'] == 1){
        //获取标签列表
        $temp_tag_arr_key = "xl_tag_rutl_".$mydata['id'].$mydata['type'];
        $temp_tag_arr = $mem_obj->get($temp_tag_arr_key);
        if($temp_tag_arr === false){
            $temp_tag_arr = array();
            $sql = "SELECT `vtc_id`,`vtc_name` FROM `video_game_tags`
                    WHERE `vtc_type` = 2 AND `vtc_start_recom` = 1 AND `vtc_status` = 1 ORDER BY rand() LIMIT 18";
            $data = $conn->find($sql);
            if(!empty($data)){
                foreach($data as $val){
                    $json = array(
                        'id' => intval($val['vtc_id']), //标签id
                        'title' => $val['vtc_name'], //标签名称
                        'type' => 1 //id类型（1：标签 2：主播 3：游戏 4：游戏分类）
                    );
                    $temp_tag_arr[] = $json;
                }
            }
            $mem_obj->set($temp_tag_arr_key,$temp_tag_arr,1800);
        }

        //过滤，6的倍数，不足6个不返回
        $return_arr = array();
        $count = count($temp_tag_arr);
        $basenum = $count/6;
        if(empty($basenum)){
            $return_arr = $temp_tag_arr;
        }else{
            for($i = 0;$i < ($basenum * 6);$i++){
                $return_arr[] = $temp_tag_arr[$i];
            }
        }

        $returnArr['rows'][] = array(
            'title' => '热门标签',
            'row' => $return_arr
        );
    }else{
        //获取推荐主播列表
        $all_game_author = array();
        $temp_user_arr = array();
        $sql = "SELECT `video_game`,`uid`,`xl_recommed`,`video_num`,`nickname`,`gender`,`desc`,`source`
                FROM `uc_members` WHERE `video_game` <> '' AND FIND_IN_SET(".$mydata['id'].",`video_game_type`)
                AND `is_show` = 1 AND (`source` = 2 OR `source` = 3) ORDER BY `is_recommed` DESC,`video_num` DESC LIMIT 16";
        $data = $uconn->find($sql);

        if(!empty($data)){
            foreach($data as $val){

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

                //检查用户是否订阅该主播
                $subscribe = 0;
                if(!empty($sub_info)){
                    foreach($sub_info as $sub){
                        if($sub['subid'] == $val['uid'] && $sub['subtype'] == 2){
                            $subscribe = 1;
                        }
                    }
                }

                $json = array(
                    'anchorid' => intval($val['uid']), //主播id
                    'authorname' => $val['nickname'], //用户昵称
                    'gender' => intval($val['gender']), //用户性别（1：男 2：女 3：未知）
                    'subid' => intval($val['uid']), //订阅id
                    'subtype' => 2, //订阅类型（1：标签 2：主播 3：游戏 4：游戏分类）
                    'subscribe' => $subscribe, //是否订阅（1：已订阅 0：未订阅）
                    'authordesc' => $val['desc'], //用户描述
                    'authorimg' => UC_API.'/avatar.php?uid='.intval($val['uid']).'&type=real&size=big&md5file='.$md5file, //用户大头像
                    'md5file' => $md5file //获取ucenter中心的大图md5值
                );

                $temp_user_arr[] = $json;
            }
        }

        //过滤，4的倍数，不足4个有多少个返回多少个
        $return_arr = array();
        $count = count($temp_user_arr);
        $basenum = $count/4;
        if(empty($basenum)){
            $return_arr = $temp_user_arr;
        }else{
            for($i = 0;$i < ($basenum * 4);$i++){
                $return_arr[] = $temp_user_arr[$i];
            }
        }

        $returnArr['rows'][] = array(
            'title' => '热门主播',
            'row' => $return_arr
        );
    }
}elseif(!empty($mydata['id']) && $mydata['type'] == 4){ //标签

    //获取标签下视频数最多的游戏18个
    $temp_game_arr_key = "xl_tag_game_rutl_".$mydata['id'].$mydata['type'];
    $temp_game_arr = $mem_obj->get($temp_game_arr_key);
    if($temp_game_arr === false){
        $sql = "SELECT COUNT(1) AS num,B.`vvl_game_id` FROM `video_tag_mapping` AS A RIGHT JOIN `video_video_list` AS B ON A.`v_id` = B.`id`
                WHERE B.`va_isshow` = 1 AND B.`vvl_game_id` <> 0 AND A.`vtc_tag_id` = ".$mydata['id']." GROUP BY B.`vvl_game_id` ORDER BY num DESC LIMIT 16";
        $game = $conn->find($sql);

        if(!empty($game)){
            $temp_game_arr = array();
            foreach($game as $val){
                $temp_game_arr[] = intval($val['vvl_game_id']);
            }
            $game_str = implode(',',$temp_game_arr);

            $sql = "SELECT `id`,`gi_name`,`gi_logo` FROM `video_game_info` WHERE `id` IN (".$game_str.")";
            $data = $conn->find($sql);
            unset($temp_game_arr);
        }

        if(!empty($data)){
            $temp_game_arr = array();
            foreach($data as $dval){

                //检查用户是否订阅该游戏
                $subscribe = 0;
                if(!empty($sub_info)){
                    foreach($sub_info as $val){
                        if($val['subid'] == $dval['id'] && $val['subtype'] == 3){
                            $subscribe = 1;
                        }
                    }
                }

                //游戏ico
                $img = empty($dval['gi_logo']) ? '' : (LOCAL_URL_DOWN_IMG.$dval['gi_logo']);

                $json = array(
                    'id' => intval($dval['id']), //游戏id
                    'type' => 1, //导航类型类型（1：游戏 2：游戏分类 3：专区 4:标签）
                    'title' => $dval['gi_name'], //游戏名称
                    'subid' => intval($dval['id']), //订阅id
                    'subtype' => 3, //订阅类型（1：标签 2：主播 3：游戏 4：游戏分类）
                    'subscribe' => $subscribe, //是否订阅（1：已订阅 0：未订阅）
                    'imgurl' => $img //游戏图标
                );

                $temp_game_arr[] = $json;
            }
            $mem_obj->set($temp_game_arr_key,$temp_game_arr,1800);
        }
    }

    //过滤，4的倍数，不足4个有多少个返回多少个
    $return_arr = array();
    $count = count($temp_game_arr);
    $basenum = $count/4;
    if(empty($basenum)){
        $return_arr = $temp_game_arr;
    }else{
        for($i = 0;$i < ($basenum * 4);$i++){
            $return_arr[] = $temp_game_arr[$i];
        }
    }

    $returnArr['rows'][] = array(
        'title' => '热门游戏',
        'row' => $return_arr
    );
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);
