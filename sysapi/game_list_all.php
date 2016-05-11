<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取所有游戏下载信息（用于定时扫描是否存在404的下载地址）
 * @file: game_list_all.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-06-10  15:46
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

$key = get_param('key'); //验证key
if($key != md5(URL_KYX_KEY.'_'.SYS_URL_KYX_KEY)){
    exit(responseJson(array('操作错误')));
}

$data_sql = "SELECT `gv_id`,`gv_title` FROM `mzw_game_version` WHERE `gv_status` = 1";
$data = $conn->find($data_sql);

$returnArr = array();
//获取游戏下载地址
if(!empty($data) && is_type($data,'Array')){
    foreach($data as $key => $val){
        //返回1个文件（APK或GPK[如果GPK有的话])
        $tmp_sql = 'SELECT `mgd_mzw_server_url`,`mgd_baidu_url`
                    FROM mzw_game_downlist
			        WHERE `gv_id` = '.$val["gv_id"].' AND `mgd_package_type`!=2
			        ORDER BY `mgd_package_type` DESC,`mgd_id` DESC LIMIT 1';
        $tmp_downlist = $conn->find($tmp_sql);//以类型作为key返回数据
        $returnArr[$key] = array(
            'id' => $val['gv_id'],
            'title' => $val['gv_title'],
            'url' => isset($tmp_downlist[0]['mgd_mzw_server_url']) ? (CDN_LESHI_URL_DOWN.$tmp_downlist[0]['mgd_mzw_server_url']) : '',
            'baidu_url' => isset($tmp_downlist[0]['mgd_baidu_url']) ? $tmp_downlist[0]['mgd_baidu_url'] : ''
        );
    }
}

exit(responseJson($returnArr));