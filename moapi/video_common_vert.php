<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 新版通用视频广告接口
 * @file: video_common_vert.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-11-23  11:09
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['gameid'] = intval(get_param('gameid'));//游戏id
$mydata['isorder'] = intval(get_param('isorder'));//是否按游戏排序（默认不排序）
$mydata['packagename'] = get_param('packagename');//包名，字符串类型
$mydata['appid'] = intval(get_param('appid'));//视频id（该参数用于视频播放详情页不同游戏广告筛选时使用）
$mydata['channel'] = get_param('channel');//渠道名称，字符串类型
$mydata['gpu'] = get_param('gpu');//字符串 gpu信息
$mydata['adpid'] = intval(get_param('adpid'));//广告位ID
$mydata['versioncode'] = intval(get_param('versioncode'));//客户端版本号

//key判断
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//广告位id判断
if(empty($mydata['adpid'])){
    exit('adpid error');
}

$mem_obj = new kyx_memcache();

//关联游戏id数组
$video_game_arr_data_key = 'video_game_arr_data_key';
$game_arr = $mem_obj->get($video_game_arr_data_key); //视频游戏数组
if($game_arr === false){
    $game_arr = array();
    $sql = "SELECT `id`,`gi_packname` FROM `video_game_info` WHERE `gi_isshow` = 1";
    $temp_arr = $conn->find($sql);
    if(!empty($temp_arr)){
        foreach($temp_arr as $val){
            $game_arr[$val['gi_packname']] = intval($val['id']);
        }
        $mem_obj->set($video_game_arr_data_key,$game_arr,7200);
    }
}

//sdk根据包名获取游戏id
if(!empty($mydata['packagename']) && empty($mydata['gameid'])){
    $mydata['gameid'] = isset($game_arr[$mydata['packagename']]) ? intval($game_arr[$mydata['packagename']]) : 0;
}

//获取当前播放视频所属游戏
if(!empty($mydata['appid'])){
    //获取当前视频播放所属游戏id
    $sql_data = "SELECT `vvl_game_id` FROM `video_video_list` WHERE `id` = ".$mydata['appid']." LIMIT 1";
    $video_info = $conn->get_one($sql_data);
    $mydata['gameid'] = isset($video_info['vvl_game_id']) ? intval($video_info['vvl_game_id']) : 0;
}

//查询条件
$tmp_where = ' WHERE A.`ad_status` = 1 AND A.`adp_id` = '.$mydata['adpid'];

//游戏id
if( !empty($mydata['gameid']) && empty($mydata['isorder'])){
	$tmp_where .= ' AND (A.ad_game_id='.$mydata['gameid']." OR (A.ad_game_id is Null OR A.ad_game_id='0' OR A.ad_game_id=''))";
}

//最低客户端版本限制
if( !empty($mydata['versioncode'])){
    $tmp_where .= ' AND (A.ad_pack_version <= '.$mydata['versioncode']." OR (A.ad_pack_version is Null OR A.ad_pack_version='0' OR A.ad_pack_version=''))";
}

//如果有传渠道过来，则调渠道对应的
if( !empty($mydata['channel'])){
    $tmp_where .= " AND (INSTR(A.ad_qudao,'".$mydata['channel']."') > 0 OR A.ad_qudao is Null OR A.ad_qudao='0' OR A.ad_qudao='') ";
}else{//如果没有传渠道过来，则调不限渠道的
    $tmp_where .= " AND (A.ad_qudao is Null OR A.ad_qudao='0' OR A.ad_qudao='') ";
}

//gpu条件
if(!empty($mydata['gpu'])){
    //查找适配的GPU
    $tmp_sql_gpu = "SELECT gb_id FROM mzw_mobile_gpu_brand WHERE INSTR('".$mydata['gpu']."',gb_params)>0";
    $tmp_gpu_id_arr = $conn->find($tmp_sql_gpu,'gb_id');
    $tmp_find_gpu_id = " AND ( FIND_IN_SET(0,ad_gpu_id)>0 ";
    foreach ($tmp_gpu_id_arr as $tmp_gpu_id_val){
        $tmp_find_gpu_id .= " OR FIND_IN_SET(".$tmp_gpu_id_val["gb_id"].",ad_gpu_id)>0 ";
    }
    $tmp_find_gpu_id .= " ) ";
    $tmp_where .= $tmp_find_gpu_id;
}

$video_common_vert_data_key = 'video_commom_vert_data_'.md5($tmp_where.$mydata['gameid']); //视频通用广告数据缓存key  'video_common_vert_data_' + MD5（查询条件）
$data = $mem_obj->get($video_common_vert_data_key); //视频通用广告数据
if($data === false){
    //获取广告数据
    $sql = "SELECT A.ad_id,A.ad_title,A.ad_img_key,A.ad_a_href,A.ad_des,A.ad_game_id,B.img_path FROM `mzw_ad` A
		    LEFT JOIN mzw_img_path B ON A.ad_img_key=B.img_key ".$tmp_where
            ."  AND (B.size_id=0 OR B.size_id is NULL) ORDER BY A.ad_dis_order DESC,A.ad_id DESC ";
    $data = $conn->find($sql);

    //如果设置了游戏排序，则先排序
    if($mydata['isorder'] > 0){
        $temp_data = array();
        foreach($data as $key => $val){
            if($val['ad_game_id'] == $mydata['gameid']){
                $temp_data[] = $val;
                unset($data[$key]);
            }
        }
        $data = array_merge($temp_data,$data);
    }

    //$mem_obj->set($video_common_vert_data_key,$data,600);
}else {

    exit(responseJson($data,false));
}

//返回数组初始化
$returnArr = array(
    'rows'=>array()
);

//数据组装
if(!empty($data)){
	foreach($data as $row){

        preg_match('/type=(.*?),/', $row['ad_a_href'],$h_arr);
        if($mydata['versioncode'] <= 22 && (isset($h_arr[1]) && $h_arr[1] == 12)){
            $row['ad_a_href'] = str_replace('show_type=2','show_type=1',$row['ad_a_href']);
            $row['ad_a_href'] = str_replace('show_type=3','show_type=1',$row['ad_a_href']);
        }

        $json = array(
            'id' => intval($row['ad_id']), //广告id
            'title' => $row['ad_title'],//广告名称
            'img' => LOCAL_URL_DOWN_IMG.$row['img_path'], //广告图标
            'desc' => $row['ad_des'], //广告描述
            'action' => $row['ad_a_href'] //广告链接地址
        );
        $returnArr['rows'][] = $json;
	}
    $mem_obj->set($video_common_vert_data_key,$returnArr,600);
}

exit(responseJson($returnArr,false));
