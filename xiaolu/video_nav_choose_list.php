<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 首页启屏选择导航列表
 * @file: video_nav_choose_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-22  10:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['packages'] = get_param('packages'); //用户本地包名json串
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

//定义回转的默认参数
$returnArr = array(
    'rows' => array()
);

$temp_arr = array();

//根据本地包名匹配导航
$where = '';
$packages = json_decode(stripslashes($mydata['packages']),true);
if(!empty($packages)){
    $where .= "(";
    foreach($packages as $val){
        $where .= " INSTR(gi_packname,'".$val."') > 0 OR";
    }
    $where = rtrim($where,'OR');
    $where .= ")";

    $sql = "SELECT `gi_name`,`gi_type`,`gi_type_id`,`gi_game_id` FROM `mzw_game_package_info` WHERE 1 AND".$where;
    $game_data = $conn->find($sql);

    if(!empty($game_data)){
        foreach($game_data as $val){
            //游戏
            if(!empty($val['gi_game_id'])){
                $temp_arr['1'.$val['gi_game_id']] = array(
                    'id' => intval($val['gi_game_id']), //关联id
                    'title' => $val['gi_name'],
                    'type' => 1 //类型（1：游戏 2：游戏分类 3：专区 4:标签）
                );
            }

            //分类
            if(!empty($val['gi_type_id'])){
                $temp_arr['2'.$val['gi_type_id']] = array(
                    'id' => intval($val['gi_type_id']), //关联id
                    'title' => $val['gi_type'],
                    'type' => 2 //类型（1：游戏 2：游戏分类 3：专区 4:标签）
                );
            }
        }
    }
}

//过滤
$game_arr = array();
$type_arr = array();
if(!empty($temp_arr)){
    $game_id_arr = array();
    $type_id_arr = array();
    foreach($temp_arr as $key => $val){
        if($val['type'] == 1){
            //去除视频数小于50的游戏分类
            $sql = "SELECT COUNT(1) AS num FROM `video_video_list` WHERE `vvl_game_id` = ".$val['id']." AND `va_isshow` = 1";
            $count = $conn->count($sql);
            if($count > 50){
                $game_arr[] = array(
                    'videonum' => intval($count),
                    'id' => intval($val['id']),
                    'title' => $val['title'],
                    'type' => 1 //类型（1：游戏 2：游戏分类 3：专区 4:标签）
                );

                $game_id_arr[] = intval($val['id']);
            }
        }elseif($val['type'] == 2){
            $sql = "SELECT COUNT(1) AS num FROM `video_video_list` AS A LEFT JOIN `video_game_info` AS B ON A.`vvl_game_id` = B.`id`
                    WHERE B.`gi_type_id` = ".$val['id'];
            $count = $conn->count($sql);
            $type_arr[] = array(
                'videonum' => intval($count),
                'id' => intval($val['id']),
                'title' => $val['title'],
                'type' => 2 //类型（1：游戏 2：游戏分类 3：专区 4:标签）
            );

            $type_id_arr[] = intval($val['id']);
        }
    }
}
unset($temp_arr);

//游戏大于3个
$game_count = count($game_arr);
if($game_count > 3){
    $game_arr = data_sort($game_arr,'videonum','DESC');
    for($i=($game_count-1);$i>=3;$i--){
        unset($game_arr[$i]);
    }
}elseif($game_count < 3){
    $temp_where = '';
    if(!empty($game_id_arr)){
        $ids = implode(',',$game_id_arr);
        $temp_where .= " AND `rela_id` NOT IN (".$ids.")";
    }
    $limit = 3 - $game_count;
    $sql = "SELECT `title`,`rela_id` FROM `video_default_nav_info` WHERE `pos_type` = 1 AND `nav_type` = 1 ".$temp_where." ORDER BY rand() LIMIT ".$limit;
    $data = $conn->find($sql);
    if(!empty($data)){
        foreach($data as $val){
            $game_arr[] = array(
                'id' => intval($val['rela_id']),
                'title' => $val['title'],
                'type' => 1 //类型（1：游戏 2：游戏分类 3：专区 4:标签）
            );
        }
    }
}

//分类大于3个
$type_count = count($type_arr);
if($type_count > 3){
    $type_arr = data_sort($type_arr,'videonum','DESC');
    for($i=($type_count-1);$i>=3;$i--){
        unset($type_arr[$i]);
    }
}elseif($type_count < 3){
    $temp_where = '';
    if(!empty($type_id_arr)){
        $ids = implode(',',$type_id_arr);
        $temp_where .= " AND `rela_id` NOT IN (".$ids.")";
    }
    $limit = 3 - $type_count;
    $sql = "SELECT `title`,`rela_id` FROM `video_default_nav_info` WHERE `pos_type` = 1 ".$temp_where." AND `nav_type` = 2 ORDER BY rand() LIMIT ".$limit;
    $data = $conn->find($sql);
    if(!empty($data)){
        foreach($data as $val){
            $type_arr[] = array(
                'id' => intval($val['rela_id']),
                'title' => $val['title'],
                'type' => 2 //类型（1：游戏 2：游戏分类 3：专区 4:标签）
            );
        }
    }
}

//获取游戏ico
if(!empty($game_arr)){
    $game_ids = array();
    foreach($game_arr as $val){
        $game_ids[] = intval($val['id']);
    }
    $game_ids_str = implode(',',$game_ids);
    $sql = "SELECT `gi_logo`,`id` FROM `video_game_info` WHERE `id` IN (".$game_ids_str.")";
    $game_ico = $conn->find($sql,'id');
    foreach($game_arr as $key => $val){
        $game_arr[$key]['imgurl'] = isset($game_ico[$val['id']]['gi_logo']) ? (LOCAL_URL_DOWN_IMG.$game_ico[$val['id']]['gi_logo']) : '';
    }
}

//获取游戏分类ico
if(!empty($type_arr)){
    $type_ids = array();
    foreach($type_arr as $val){
        $type_ids[] = intval($val['id']);
    }
    $type_ids_str = implode(',',$type_ids);
    $sql = "SELECT `t_logo`,`t_id` FROM `video_game_type` WHERE `t_id` IN (".$type_ids_str.")";
    $type_ico = $conn->find($sql,'t_id');
    foreach($type_arr as $key => $val){
        $type_arr[$key]['imgurl'] = isset($type_ico[$val['id']]['t_logo']) ? (LOCAL_URL_DOWN_IMG.$type_ico[$val['id']]['t_logo']) : '';
    }
}

$temp_arr = array_merge($game_arr,$type_arr);
if(!empty($temp_arr)){
    foreach($temp_arr as $val){
        $returnArr['rows'][] = array(
            'id' => intval($val['id']),
            'title' => $val['title'],
            'type' => intval($val['type']),
            'imgurl' => $val['imgurl']
        );
    }
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);





