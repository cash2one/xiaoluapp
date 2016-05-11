<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取游戏分类页的广告列表,并加密JSON内容进行输出返回
 * @file: game_category_ad.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-06-05  14:49
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
//channel：渠道名称 字符串类型
//adpid：广告位ID 整型 不传默认39（客户端分类页广告）
$mydata = array();
$mydata['channel'] = get_param('channel');//渠道名称，字符串类型
$mydata['adpid'] = intval(get_param('adpid'));//广告位ID
$mydata['adpid'] = empty($mydata['adpid']) ? 39 : $mydata['adpid'];
$mydata['isnew'] = intval(get_param('isnew')); //是否显示最新游戏广告位信息
$mydata['gpu'] = get_param('gpu');//CPU型号，字符串
$mydata['model'] = get_param('model'); //型号（在用）
$mydata['insdcardunmount'] = intval(get_param('insdcardunmount')); //设置的内置存贮卡是否挂载（1是未挂载，0是已经挂载)

$tmp_where = '';
//广告位ID
if( !is_empty($mydata['adpid']) && $mydata['adpid']!=0){
	$tmp_where = ' AND A.adp_id='.$mydata['adpid'];
}
//如果有传渠道过来，则调渠道对应的
if( !is_empty($mydata['channel'])){
	$tmp_where .= " AND A.ad_qudao='".$mydata['channel']."' ";
}else{//如果没有传渠道过来，则调不限渠道的
	$tmp_where .= " AND (A.ad_qudao is Null OR A.ad_qudao='0' OR A.ad_qudao='') ";
}

//查询广告位对应广告列表
$sql = "SELECT A.ad_id,A.ad_title,A.ad_img_key,A.ad_a_href,A.ad_des,B.img_path FROM `mzw_ad` A 
		LEFT JOIN mzw_img_path B ON A.ad_img_key=B.img_key 
		WHERE A.ad_status=1 ".$tmp_where."  AND (B.size_id=0 OR B.size_id is NULL) ORDER BY ad_dis_order DESC";
$data = $conn->find($sql);
$returnArr = array('rows'=>array());

//最新游戏列表（获取当前最新的一条游戏）
if(!empty($mydata['isnew'])){

    $new_temp_where = " AND (gv_status=1 OR gv_id=764) AND (gv_nes_property = '' OR gv_nes_property = 0 OR gv_nes_property is null) ";

    //不显示大游戏（即不显示GPK的游戏）
    if($mydata['insdcardunmount']==1){
        $new_temp_where .= ' AND FIND_IN_SET(23,g_tags_property)<1 ';
    }

    //====start 适配ＧＰＵ====//
    $tmp_find_gpu_id = " ( FIND_IN_SET(0,mgd_gpu_id)>0 ";
    if(!empty($mydata['gpu'])){
        $tmp_sql_gpu = "SELECT gb_id FROM mzw_mobile_gpu_brand WHERE INSTR('".$mydata['gpu']."',gb_params)>0";
        $tmp_gpu_id_arr = $conn->find($tmp_sql_gpu,'gb_id');
        if(!empty($tmp_gpu_id_arr) && is_type($tmp_gpu_id_arr,'Array')){
            foreach ($tmp_gpu_id_arr as $tmp_gpu_id_val){
                $tmp_find_gpu_id .= " OR FIND_IN_SET(".$tmp_gpu_id_val["gb_id"].",mgd_gpu_id)>0 ";
            }
        }
    }
    $tmp_find_gpu_id .= " ) ";

    //查找排除的型号mydata['model']
    $tmp_find_model_id = "";
    if(!is_empty($mydata['model'])){
        $tmp_sql_model_dis = "SELECT `model_id` FROM `mzw_mobile_model` WHERE INSTR('".$mydata['model']."',model_params)>0";
        $tmp_model_id_arr = $conn->find($tmp_sql_model_dis,'model_id');
        if(!is_empty($tmp_model_id_arr)){//如果有找到对应的型号
            foreach ($tmp_model_id_arr as $tmp_model_id_val){
                $tmp_find_model_id .= " AND FIND_IN_SET(".$tmp_model_id_val["model_id"].",mgd_shield_mobile)<1 ";
            }
        }else{//如果没有找到对应的型号，看下是否禁止了未知型号
            $tmp_find_model_id .= " AND FIND_IN_SET(25,mgd_shield_mobile)<1 ";
        }
    }
    //====end 适配ＧＰＵ====//

    $tmp_sql_gpu_in = 'SELECT DISTINCT `gv_id` FROM `mzw_game_downlist` 
                       WHERE mgd_package_type!=2 AND '.$tmp_find_gpu_id.$tmp_find_model_id;

    //查数据
    $sql_data = "SELECT gv_id,gv_title FROM `mzw_game_version` gv 
                 WHERE (FIND_IN_SET(1,gv.gv_client_type)>0 OR FIND_IN_SET(3,gv.gv_client_type)>0) AND gv_id IN($tmp_sql_gpu_in) $new_temp_where
                 ORDER BY gv_update_time DESC,gv_id DESC LIMIT 1";
    $game_data = $conn->find($sql_data);

    if(!empty($game_data)){

        //查找这个游戏对应的相关图片
        $tmp_sql = 'SELECT A.`img_key`,A.`path` as `src_path`,B.`img_path` as `path`,A.`type`
                    FROM `mzw_game_screenshot` A
			        LEFT JOIN `mzw_img_path` B ON A.`img_key` = B.`img_key`
			        WHERE A.`gv_id` = '.$game_data[0]["gv_id"]." AND (B.`size_id` = 16 OR B.`size_id` = 17)
			        ORDER BY B.`id` DESC";
        $tmp_hot = $conn->find($tmp_sql);
        $tmp_hot_arr = array();//存放游戏对应的相关图片
        if($tmp_hot){
            foreach ($tmp_hot as $val_hot ){
                if(substr($val_hot['src_path'],-3)=='jpg'){//如果源图就是jpg的，则返回源图
                    $tmp_val_hot_img = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$val_hot["src_path"]);
                }else{
                    $tmp_val_hot_img = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$val_hot["path"]);
                }
                $tmp_hot_arr[$game_data[0]["gv_id"]][$val_hot["type"]] = $tmp_val_hot_img;
            }
        }

        $returnArr['rows'][] = array(
            'title' => $game_data[0]['gv_title'],
            'img' => isset($tmp_hot_arr[$game_data[0]["gv_id"]][3]) ? $tmp_hot_arr[$game_data[0]["gv_id"]][3] : '', //TV游戏大图
            'desc' => '',
            'href' => $game_data[0]['gv_id']
        );
    }
}

if($data){
	foreach($data as $row){
		$json = array(
            'title'=>$row['ad_title'],//广告名称
            'img'=>empty($row['img_path']) ? '' : LOCAL_URL_DOWN_IMG.$row['img_path'],
            'desc'=>$row['ad_des'],
            'href'=>$row['ad_a_href']
		);
		$returnArr['rows'][]=$json;
	}
}

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
	echo($sql);
	var_dump($returnArr);
	exit;
}

$str_encode = responseJson($returnArr,true);
exit($str_encode);
