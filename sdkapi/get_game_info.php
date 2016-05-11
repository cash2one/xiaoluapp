<?php
/**
 * @copyright: @快游戏 2015
 * @description: 根据游戏包名、版本获取游戏相关信息（版本可不传）
 * @file: get_game_info.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-07-29  10:46
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
//规则：游戏包名跟中文标题可同时传过来，匹配其中一个即可，跟英文标题是互斥状态
$mydata = array();
$mydata['packagename'] = get_param('packagename'); //游戏包名
$mydata['title'] = get_param('title'); //英文标题
$mydata['cntitle'] = get_param('cntitle'); //中文标题
$mydata['versioncode'] = intval(get_param('versioncode')); //游戏版本号，整型
$mydata['key'] = get_param('key'); //验证key
$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
$request = $_SERVER['REQUEST_METHOD']; //请求方式

$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}



//参数判断
if(empty($mydata['packagename']) && empty($mydata['cntitle']) && empty($mydata['title'])){
    exit('参数出错');
}

$where = " WHERE 1 ";
//游戏包名称跟中文标题
if(!empty($mydata['packagename']) && !empty($mydata['cntitle'])){
    $where .= " AND (`gv_package_name` = '".$mydata['packagename']."' OR `gv_title` like '%".$mydata['cntitle']."%')";
}elseif(!empty($mydata['packagename']) && empty($mydata['cntitle'])){
    $where .= " AND `gv_package_name` = '".$mydata['packagename']."'";
}elseif(!empty($mydata['cntitle']) && empty($mydata['packagename'])){
    $where .= " AND `gv_title` like '%".$mydata['cntitle']."%'";
}
//游戏英文标题
if(!empty($mydata['title'])){
    $where .= " AND `gv_title_en` = '".$mydata['title']."'";
}
//游戏版本
if(!empty($mydata['versioncode'])){
    $where .= " AND `gv_version_no` = ".$mydata['versioncode'];
}

$sql = "SELECT `gv_id`,`gv_title`,`gv_type_id`,`gv_package_name`,`gv_version_name`,`gv_version_no`,
        `gv_ico_key`,`gv_status`
        FROM `mzw_game_version` ".$where;
$data = $conn->find($sql);

$returnArr = array();
if(!empty($data)){
    foreach($data as $val){
        //查所属分类的名称
        $category = '';
        $tmp_sql = 'SELECT t_name_cn as name FROM mzw_game_type WHERE t_id='.$val["gv_type_id"];
        $tmp_type = $conn->find($tmp_sql);
        if($tmp_type){
            $category = $tmp_type[0]["name"];
        }

        $game_ico = '';//ICO地址
        $tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
				    LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$val['gv_ico_key']."' AND B.width=100 AND B.height=100 ORDER BY A.size_id";
        $tmp_game_ico_arr = $conn->find($tmp_sql);
        if($tmp_game_ico_arr){
            $game_ico = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$tmp_game_ico_arr[0]["img_path"]);
        }

        //游戏上下架状态
        $status = array(
            1 => '上架',
            2 => '下架',
            3 => '即将上线',
            4 => '隐藏'
        );

        $returnArr[] = array(
            'id' => $val['gv_id'],
            'title' => $val['gv_title'],
            'packname' => $val['gv_package_name'],
            'versioncode' => $val['gv_version_no'],
            'versionname' => $val['gv_version_name'],
            'icopath' => $game_ico,
            'statusname' => isset($status[$val['gv_status']]) ? $status[$val['gv_status']] : '',
            'status' => $val['gv_status']
        );
    }
}

//开启调试数据显示
if($is_bug_show == 100){
    echo($sql);
    var_dump($data);
    exit;
}

$str_encode = responseJson($returnArr);
exit($str_encode);
