<?php
/**
 * @copyright: @快游戏 2014
 * @description: 游戏详情页，返回该游戏的详细信息
 * @file: 51kuaiapp_detail.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2015-04-14  17:12
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();

$mydata['cpu']=get_param('cpu');//CPU参数
$mydata['gpu']=get_param('gpu');//GPU参数
$mydata['source']=get_param('source');//访问来源
$mydata['locale'] = get_param('locale');//言语版本
$mydata['density']=get_param('density');//分辨率
$mydata['brand']=get_param('brand');//品牌
$mydata['model']=get_param('model');//型号
$mydata['key'] = get_param('key');
$mydata['appid'] = intval(get_param('appid'));//游戏版本ID（即game_version 的ID）
$mydata['kyxversion'] = intval(get_param('kyxversion'));//客户端版本

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试


//验证key是否正确
verify_key_kyx($mydata['key']);

if(is_empty($mydata['appid'])){
	echo('游戏ID为空！');
	exit;
}

$returnArr = array('total'=>1,'pagecount'=>1,'pagenum'=>1,'rows'=>array());

//查找游戏类型（用于NES兼容判断）
$game_type_sql = "SELECT `gv_id` FROM `mzw_game_version` WHERE `gv_status` = 1 AND `gv_id` = ".$mydata['appid']." AND FIND_IN_SET(1,gv_nes_property)>0";
$game_type_data = $conn->find($game_type_sql);

$data = array();
//查游戏对应版本的信息
$sql = "SELECT gv_id as appid,g_id as gid,gv_type_id as tid,gv_title as title,gv_version_no as versioncode,gv_version_name as version,
	gv_update_time as updatetime,gv_package_name as  packagename,gv_ico_key as icon,gv_down_nums as downloadscount,
	gv_description as description,gv_app_prop as prop,gv_notice as attention,gv_update_text as updateContent, 
	case gv_lang when 0 then '英文' when 1 then '中文' when 2 then '日文' when 3 then '韩文' when 4 then '其它' end language
		FROM `mzw_game_version`  WHERE gv_id=".$mydata['appid'];

$data_arr = $conn->find($sql);
//有适配到才查找
if($data_arr){
	$data = $data_arr[0];

	//查找这个游戏对应的相关图片
	$tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type,B.size_id FROM mzw_game_screenshot A 
			LEFT JOIN mzw_img_path B ON A.img_key=B.img_key 
			WHERE A.gv_id='.$data["appid"]." AND (B.size_id=16 OR B.size_id=17 OR B.size_id=19) ORDER BY B.id DESC ";
	$tmp_sql_bug = $tmp_sql;
	$tmp_hot = $conn->find($tmp_sql);
	$tmp_hot_arr = array();//存放游戏对应的相关图片
	$tmp_hot_arr_tmp = array();//存放游戏载图的临时信息
	if($tmp_hot){
		foreach ($tmp_hot as $val_hot ){
			if($val_hot['size_id']==16){
				if(substr($val_hot['src_path'],-3)=='jpg'){//如果源图就是jpg的，则返回源图
					$tmp_val_hot_img = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$val_hot["src_path"]);
				}else{
					$tmp_val_hot_img = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$val_hot["path"]);
				}
				$tmp_hot_arr[$data["appid"]][$val_hot["type"]][] = $tmp_val_hot_img;
			}else{//这个是游戏截图截小了之后的
				if(substr($val_hot['src_path'],-3)=='jpg' && $val_hot['src_path']==17){//如果源图就是jpg的，则返回源图
					$tmp_val_hot_img = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$val_hot["src_path"]);
				}else{
					$tmp_val_hot_img = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$val_hot["path"]);
				}
				$tmp_hot_arr_tmp[$val_hot['size_id']][] = array(
														'path'=>$tmp_val_hot_img,
														'img_key'=>$val_hot['img_key']
														);
			}
		}
	}
	//找出游戏截图的大图和小图
	$tmp_hot_arr_a = array();//游戏截图的大图
	$tmp_hot_arr_b = array();//游戏截图的小图
	//先查大图
	foreach ($tmp_hot_arr_tmp[17] as $k=>$v){
		//判断小图
		$tmp_small = false;
		foreach ($tmp_hot_arr_tmp[19] as $tmp_k=>$tmp_v){
			//如果有找到小图
			if($v['img_key']==$tmp_v['img_key']){
				$tmp_small = $tmp_v['path'];
				break;
			}
		}
		$tmp_hot_arr_a[] = $v['path'];//游戏截图的大图
		//如果有找到小图，则用小图
		if($tmp_small!=false){
			$tmp_hot_arr_b[] = $tmp_small;
		}else{//如果没有找到小较长，则小图也用大图的
			$tmp_hot_arr_b[] = $v['path'];
		}
	}

	$gv_game_ico = '';//ICO地址
	$tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
				LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$data["icon"]."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
	$tmp_game_ico_arr = $conn->find($tmp_sql);
	if($tmp_game_ico_arr){
		$tmp_game_ico = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$tmp_game_ico_arr[0]["img_path"]);
	}

    /* 新版获取下载地址 start*/
    $where_str = ' WHERE mgd_client_type!=2 AND gv_id='.$data["appid"].' AND mgd_package_type!=2 ';
    //如果是NES游戏
    if(isset($game_type_data[0]['gv_id']) && !empty($game_type_data[0]['gv_id'])){
        //如果客户端版本大于200
        if($mydata['kyxversion'] > 200){
            $order_str = " ORDER BY mgd_id DESC ";
        }else{
            $order_str = " ORDER BY mgd_id ASC ";
        }
    }else{
        $order_str = " ORDER BY mgd_package_type DESC,mgd_id DESC ";
    }
    //查文件大小及游戏是APK还是GPK
    $tmp_sql = 'SELECT mgd_id,mgd_package_file_size as size,mgd_package_type as type,mgd_mzw_server_url,mgd_baidu_url,mgd_apk_agsin,mgd_game_unzip_size as unzip_size FROM mzw_game_downlist '
        .$where_str.$order_str.' LIMIT 1';//返回1个文件（APK或GPK[如果GPK有的话])
    $tmp_downlist = $conn->find($tmp_sql);//以类型作为key返回数据
    /* 新版获取下载地址 end*/

	$downloadPaths = array();
	if($tmp_downlist && count($tmp_downlist)>0){
		
		$data['sign'] = $tmp_downlist[0]["mgd_apk_agsin"];//游戏签名

		$size = 0;//文件大小
		//APK或者GPK的下载地址
		$down_apk_gpk = CDN_LESHI_URL_DOWN.$tmp_downlist[0]['mgd_mzw_server_url'];
		$size = $tmp_downlist[0]['size'];
		//组合乐视CDN 相关下载地址
		$downloadPaths[] = array(
				'id' => -3,
				'name' => '普通下载',
				'icon' => CDN_LESHI_URL_DOWN.'/app420/cdn.png',
				'url' => $down_apk_gpk,
				'backup' =>'',
				'visible' =>1 ,
				'parse' =>false,
				'files' =>array()
		);
	}else{
		$down_apk_gpk = '';
		$size = 0;//文件大小
	}
	$sign = $data['sign'];//游戏签名
	
	$json = array(
			'title'=>$data["title"],//游戏标题题
			'appid'=>intval($data["appid"]),//游戏版本ID（即game_version 的ID）
			'packagename'=>$data["packagename"],//游戏包名
			'iconpath'=>$tmp_game_ico,//ICO地址
			'version'=>$data["version"],//游戏版本名称
			'versioncode'=>intval($data["versioncode"]),//游戏版本号
			'size'=>intval($size),//文件大小
			'downloadscount'=> intval($data["downloadscount"]),//下载次数
			'description'=>$data["description"],//游戏描述
			'downloadPaths'=>$downloadPaths,//相关下载地址（数组来的）
			'screenshot'=>isset($tmp_hot_arr_a)?$tmp_hot_arr_a:array(),//游戏截图
			'smallscreenshot' =>isset($tmp_hot_arr_b)?$tmp_hot_arr_b:array(),//游戏小截图
			'signature' => $sign//游戏签名
	);
	$returnArr['rows'][]=$json;
}
if($is_bug_show==100){
	echo($sql);
	var_dump($returnArr);
	exit;
}
$str_encode = responseJson($returnArr,true);
exit($str_encode);



