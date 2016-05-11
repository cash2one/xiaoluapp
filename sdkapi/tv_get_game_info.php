<?php
/**
 * @copyright: @快游戏 2015
 * @description: TV端根据游戏包名获取最高版本软件相关信息（不区分上下架）
 * @file: tv_get_game_info.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-08-20  15:49
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata['packagename'] = get_param('packagename'); //游戏包名
$mydata['key'] = get_param('key'); //验证key
$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
$request = $_SERVER['REQUEST_METHOD']; //请求方式

$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//参数判断
if(empty($mydata['packagename'])){
    exit('参数出错');
}

$where = " WHERE 1 ";
//游戏包名称跟中文标题
if(!empty($mydata['packagename'])){
    $where .= " AND `gv_package_name` = '".$mydata['packagename']."'";
}

$sql = "SELECT `gv_id`,`gv_title`,`gv_type_id`,`gv_package_name`,`gv_version_name`,`gv_version_no`,
        `gv_ico_key`,`gv_status`,`gv_ico_key`
        FROM `mzw_game_version` ".$where." ORDER BY `gv_version_no` DESC";
$data = $conn->get_one($sql);

$returnArr = array();
if(!empty($data)){
    //获取游戏ICO地址（64 * 64）
    $tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type FROM mzw_game_screenshot A
                LEFT JOIN mzw_img_path B ON A.img_key=B.img_key
                WHERE A.gv_id = '.$data["gv_id"].' AND A.type = 7 AND B.size_id = 21 ORDER BY B.id DESC';
    $tmp_game_ico_arr = $conn->get_one($tmp_sql);
    $gv_game_ico = isset($tmp_game_ico_arr['path']) ? (LOCAL_URL_DOWN_IMG.$tmp_game_ico_arr['path']) : '';

    //如果没找到64 * 64的ICO，则拿100 * 100的ICO
    if(empty($gv_game_ico)){
        $tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
				    LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$data["gv_ico_key"]
                    ."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
        $tmp_game_ico_arr = $conn->get_one($tmp_sql);
        if($tmp_game_ico_arr){
            $gv_game_ico = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$tmp_game_ico_arr["img_path"]);
        }
    }

    //获取下载地址相关信息
    $where_str = ' WHERE gv_id = '.$data["gv_id"].' AND mgd_package_type!=2 ';
    $order_str = " ORDER BY mgd_package_type DESC,mgd_id DESC ";

    //查文件大小及游戏是APK还是GPK
    $tmp_sql = 'SELECT mgd_id,mgd_package_file_size as `size`,mgd_package_type as `type`,mgd_mzw_server_url,mgd_baidu_url,mgd_apk_agsin,mgd_game_unzip_size as unzip_size
                FROM mzw_game_downlist ' .$where_str.$order_str.' LIMIT 1';//返回1个文件（APK或GPK[如果GPK有的话])
    $tmp_downlist = $conn->get_one($tmp_sql);//以类型作为key返回数据

    //组合乐视CDN 相关下载地址
    $downloadPaths[] = array(
        'id' => -3,
        'name' => '普通下载',
        'icon' => CDN_LESHI_URL_DOWN.'/app420/cdn.png',
        'url' => CDN_LESHI_URL_DOWN.$tmp_downlist['mgd_mzw_server_url'],
        'backup' =>'',
        'visible' =>1 ,
        'parse' =>false,
        'files' =>array()
    );

    $returnArr['rows'][] = array(
        'appid' => $data['gv_id'], //游戏id
        'title' => $data['gv_title'], //游戏标题
        'iconpath' => $gv_game_ico, //游戏ICO
        'fileType' => 'apk', //文件类型
        'packagename' => $data['gv_package_name'], //游戏包名
        'versioncode' => $data['gv_version_no'], //游戏版本号
        'version' => $data['gv_version_name'],  //游戏版本名称
        'size' => $tmp_downlist['size'], //游戏大小
        'downloadPaths' => $downloadPaths //游戏下载地址
    );
}

exit(responseJson($returnArr,true));
