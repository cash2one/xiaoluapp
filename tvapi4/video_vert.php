<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取视频播放广告
 * @file: video_vert.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-15  17:28
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['channel'] = get_param('channel');//渠道名称，字符串类型
$mydata['adpid'] = intval(get_param('adpid'));//广告位ID
$mydata['gpu'] = get_param('gpu');//字符串 gpu信息

$tmp_where = '';
if( !is_empty($mydata['adpid']) && $mydata['adpid']!=0){
	$tmp_where = ' AND A.adp_id='.$mydata['adpid'];
}else{
	$tmp_where = ' AND A.adp_id=45 ';//默认广告位（视频播放广告位）
}

if(!is_empty($mydata['gpu'])){
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

//如果有传渠道过来，则调渠道对应的
if( !is_empty($mydata['channel'])){
	$tmp_where .= " AND (INSTR(A.ad_qudao,'".$mydata['channel']."')>0 OR A.ad_qudao is Null OR A.ad_qudao='0' OR A.ad_qudao='') ";
}else{//如果没有传渠道过来，则调不限渠道的
	$tmp_where .= " AND (A.ad_qudao is Null OR A.ad_qudao='0' OR A.ad_qudao='') ";
}

$mem_obj = new kyx_memcache();

//adp_id=45 视频播放广告位
$sdk_vert_data_key = 'sdk_vert_data_'.md5($tmp_where); //sdk广告数据缓存key  'sdk_vert_data_' + MD5（查询条件）
$data = $mem_obj->get($sdk_vert_data_key); //专区视频总数
if($data === false){
    $sql = "SELECT A.ad_id,A.ad_title,A.ad_img_key,A.ad_a_href,A.ad_des,B.img_path FROM `mzw_ad` A
		    LEFT JOIN mzw_img_path B ON A.ad_img_key=B.img_key
		    WHERE A.ad_status=1 ".$tmp_where."  AND (B.size_id=0 OR B.size_id is NULL) ";
    $data = $conn->find($sql);
    $mem_obj->set($sdk_vert_data_key,$data,600);
}

//返回数组初始化
$returnArr = array(
    'rows'=>array()
);

//数据组装
if($data){
	foreach($data as $row){
		$json = array(
                'id' => $row['ad_id'], //广告id
				'title' => $row['ad_title'],//广告名称
				'img' => LOCAL_URL_DOWN_IMG.$row['img_path'], //广告图标
				'desc'=>$row['ad_des'], //广告描述
				'action'=>$row['ad_a_href'] //广告链接地址
		);
		$returnArr['rows'][] = $json;
	}
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);
