<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取小鹿视频用户导航更新
 * @file: update_index_nav.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-22  10:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['packages'] = get_param('packages'); //增量游戏包名
$mydata['uid'] = intval(get_param('uid')); //用户id
$mydata['mac'] = get_param('mac'); //用户mac地址
$mydata['imei'] = get_param('imei'); //用户imei地址
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//增量包名
$packages = json_decode(stripslashes($mydata['packages']),true);
$check_where = '';
if(!empty($mydata['mac']) && $mydata['mac'] <> '00:00:00:00:00:00'){
    $check_where .= " AND `mac` = '".$mydata['mac']."'";
}
if(!empty($mydata['imei'])){
    $check_where .= " AND `imei` = '".$mydata['imei']."'";
}

//获取用户现有的游戏导航信息
$sql = "SELECT `id`,`nav_json`,`nav_now_game`,`user_packages` FROM `video_user_nav_info` WHERE 1 ".$check_where;
$user_data = $conn->find($sql);
if(!empty($user_data)){
    $save_id = intval($user_data[0]['id']); //更新id
    $nav_arr = json_decode($user_data[0]['nav_json'],true); //猜你喜欢频道数组
    $nav_play_arr = json_decode($user_data[0]['nav_now_game'],true); //正在玩频道数组
    $user_packages = json_decode($user_data[0]['user_packages'],true); //包名数组

    //匹配增量包名
    $where = '';
    if(!empty($packages)){
        $where .= "(";
        foreach($packages as $val){

            //检测包名是否存在
            if(!in_array($val,$user_packages)){
                $user_packages[] = $val;
            }

            $where .= " INSTR(gi_packname,'".$val."') > 0 OR";
        }
        $where = rtrim($where,'OR');
        $where .= ")";

        $sql = "SELECT `gi_name`,`gi_type`,`gi_type_id`,`gi_game_id` FROM `mzw_game_package_info` WHERE 1 AND".$where;
        $game_data = $conn->find($sql);

        if(!empty($game_data)){
            $temp_ids_arr = array();
            foreach($game_data as $val){
                if(!empty($val['gi_game_id'])){
                    $temp_ids_arr[] = intval($val['gi_game_id']);
                }
            }

            //获取游戏信息
            if(!empty($temp_ids_arr)){
                $temp_ids = implode(',',$temp_ids_arr);
                $sql = "SELECT `id`,`gi_name` FROM `video_game_info` WHERE `id` IN (".$temp_ids.")";
                $data = $conn->find($sql);
                if(!empty($data)){
                    foreach($data as $val){

                        //检测正在玩的游戏数组里是否存在该游戏
                        $is_has = false;
                        foreach($nav_play_arr as $nval){
                            if($nval['id'] == $val['id']){
                                $is_has = true;
                            }
                        }

                        if(!$is_has){
                            $nav_play_arr[] = array(
                                'id' => intval($val['id']),
                                'title' => $val['gi_name'],
                                'type' => 1 //类型（1：游戏 2：游戏分类 3：专区 4:标签）
                            );
                        }

                        //检测猜你喜欢的游戏数组里是否存在该游戏
                        foreach($nav_arr as $ckey => $cval){
                            if($cval['id'] == $val['id']){
                                unset($nav_arr[$ckey]);
                            }
                        }
                    }
                }
            }
        }
    }

    //更新信息
    $update_arr = array(
        'id' => $save_id,
        'nav_json' => json_encode($nav_arr,JSON_UNESCAPED_UNICODE),
        'nav_now_game' => json_encode($nav_play_arr,JSON_UNESCAPED_UNICODE),
        'user_packages' => json_encode($user_packages,JSON_UNESCAPED_UNICODE),
        'update_time' => time()
    );
    $conn->update('video_user_nav_info',$update_arr);
}

$returnArr = array('code'=>200,'msg'=>'更新成功');
$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);





