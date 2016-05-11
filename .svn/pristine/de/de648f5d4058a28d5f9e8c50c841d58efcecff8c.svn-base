<?php
/**
 * @copyright: @快游戏 2014
 * @description: 游戏详情页，返回该游戏的详细信息
 * @file: game_app_detail.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-11-13  15:38
 * @version 1.0
 **/
include_once("../../config.inc.php");
include_once("../../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['mzwversion'] = get_param('mzwversion');
$mydata['sysversion'] = get_param('sysversion');//安卓系统版本下限
$mydata['cpu']=get_param('cpu');//CPU参数
$mydata['gpu']=get_param('gpu');//GPU参数
$mydata['source']=get_param('source');//访问来源
$mydata['locale'] = get_param('locale');//言语版本
$mydata['density']=get_param('density');//分辨率
$mydata['brand']=get_param('brand');//品牌
$mydata['model']=get_param('model');//型号
$mydata['key'] = get_param('key');
$mydata['appid'] = intval(get_param('appid'));//游戏版本ID（即game_version 的ID）

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试

!is_numeric($mydata['sysversion']) && $mydata['sysversion']=0;
!is_numeric($mydata['cpu']) && $mydata['cpu']=31;
!is_numeric($mydata['density']) && $mydata['density']=15;

$_locale=0;
switch ($mydata['locale']) {
	case 'tw':
		$baidu = '百度網盤';
		$xunlei = '迅雷快傳';
		$cdn = '普通下載';
		$_locale=0;
		break;
	case 'cn':
		$baidu = '百度下载';
		$xunlei = '迅雷下载';
		$cdn = '普通下载';
		$_locale=0;
		break;
	case 'hk':
		$baidu = '百度網盤';
		$xunlei = '迅雷快傳';
		$cdn = '普通下載';
		$_locale=0;
		break;
	case 'ko':
		$baidu = 'Baidu Cloud';
		$xunlei = 'Thunder Drive';
		$cdn = 'Normal Download';
		$_locale=1;
		break;
	default:
		$baidu = 'Baidu Cloud';
		$xunlei = 'Thunder Drive';
		$cdn = 'Normal Download';
		$_locale=1;
}

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

//查找适配的GPU
$tmp_sql_gpu = "SELECT gb_id FROM mzw_mobile_gpu_brand WHERE INSTR('".$mydata['gpu']."',gb_params)>0";
$tmp_gpu_id_arr = $conn->find($tmp_sql_gpu,'gb_id');

$data = array();
//查游戏对应版本的信息
$sql = "SELECT gv_id as appid,g_id as gid,gv_type_id as tid,gv_title as title,gv_version_no as versioncode,gv_version_name as version,
	gv_update_time as updatetime,gv_package_name as  packagename,gv_ico_key as icon,gv_down_nums as downloadscount,
	gv_description as description,gv_app_prop as prop,gv_notice as attention,gv_update_text as updateContent, 
	case gv_lang when 0 then '英文' when 1 then '中文' when 2 then '日文' when 3 then '韩文' when 4 then '其它' end language
		FROM `mzw_game_version`  WHERE gv_status = 1 AND (FIND_IN_SET(1,gv_client_type)>0 OR FIND_IN_SET(3,gv_client_type)>0) AND gv_id=".$mydata['appid'];

$data_arr = $conn->find($sql);
//有适配到才查找
if(($tmp_gpu_id_arr && count($tmp_gpu_id_arr)>0) && $data_arr){
//if( $data_arr){
	$data = $data_arr[0];

	$downloadscount = $data['downloadscount'];
	//}
	$updatetime = date('Y-m-d', $data['updatetime']);
	$language = $data['language'];
	$havead = false;
	$requireInternet = false;
	$requireGooglePlay = false;
	$propGameHandle = false;//游戏手柄
	$propMouseController = false;//空鼠遥控器
	$propCommonController = false;//普通遥控器
	$propCamera = false;//体感摄像头
	$propOutCart = false;////支持外置存贮卡
	$propCustomController = false;//支持自定义按键
	$weburl = '';//URL_GAME_M.'/'.$packagename.'-'.$appid.'.html';
	//检测游戏
	//$testInfo = gettestInfo($data[0]['prop']);
	$testInfo = explode(",", $data['prop']);
	//if($testInfo[2]!=''){//有广告
	if(in_array(2, $testInfo)){
		$havead = true;
	}
	//if($testInfo[1]!=''){//需WIFI
	if(in_array(1, $testInfo)){
		$requireInternet = true;
	}
	//if($testInfo[0]!=''){//需市场
	if(in_array(0, $testInfo)){
		$requireGooglePlay = true;
	}
	if(in_array(32, $testInfo)){
		$propGameHandle = true;//游戏手柄
	}
	//if($testInfo[64]!=''){
	if(in_array(64, $testInfo)){
		$propMouseController = true;//空鼠遥控器
	}
	//if($testInfo[128]!=''){
	if(in_array(128, $testInfo)){
		$propCommonController = true;//普通遥控器
	}
	//if($testInfo[256]!=''){
	if(in_array(256, $testInfo)){
		$propCamera = true;//体感摄像头
	}
	//if($testInfo[512]!=''){
	if(in_array(512, $testInfo)){	
		$propOutCart = true;//支持外置存贮卡
	}
	if(in_array(5, $testInfo)){	
		$propCustomController = true;//支持自定义按键
	}
	
	$online = false;//是否网络游戏
	
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
				//$tmp_val_hot_img_small = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$val_hot["path"]);
				//$tmp_hot_arr_small[$data["appid"]][$val_hot["type"]][] = $tmp_val_hot_img_small;
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
	
	
	//查所属分类的名称
	$tmp_sql = 'SELECT t_name_cn as name FROM mzw_game_type WHERE t_id='.$data["tid"];
	$tmp_type = $conn->find($tmp_sql);
	if($tmp_type){
		$category = $tmp_type[0]["name"];
	}
	$gv_game_ico = '';//ICO地址
	$tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
				LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$data["icon"]."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
	$tmp_game_ico_arr = $conn->find($tmp_sql);
	if($tmp_game_ico_arr){
		$tmp_game_ico = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$tmp_game_ico_arr[0]["img_path"]);
	}
	
	//适配GPU
	$tmp_find_gpu_id = " ( FIND_IN_SET(0,mgd_gpu_id)>0 OR mgd_gpu_id IS NULL ";
	foreach ($tmp_gpu_id_arr as $tmp_gpu_id_val){
		$tmp_find_gpu_id .= " OR FIND_IN_SET(".$tmp_gpu_id_val["gb_id"].",mgd_gpu_id)>0 ";
	}
	$tmp_find_gpu_id .= " ) ";

    /* 新版获取下载地址 start*/
    $where_str = ' WHERE mgd_client_type!=2 AND gv_id='.$data["appid"].' AND mgd_package_type!=2 AND  '.$tmp_find_gpu_id;
    //如果是NES游戏
    if(isset($game_type_data[0]['gv_id']) && !empty($game_type_data[0]['gv_id'])){
        $order_str = " ORDER BY mgd_id DESC ";
    }else{
        $order_str = " ORDER BY mgd_package_type DESC,mgd_id DESC ";
    }
    //查文件大小及游戏是APK还是GPK
    $tmp_sql = 'SELECT mgd_id,mgd_package_file_size as size,mgd_package_type as type,mgd_mzw_server_url,mgd_baidu_url,mgd_apk_agsin FROM mzw_game_downlist '
                .$where_str.$order_str.' LIMIT 1';//返回1个文件（APK或GPK[如果GPK有的话])
    $tmp_downlist = $conn->find($tmp_sql);//以类型作为key返回数据
    /* 新版获取下载地址 end*/

	if($tmp_downlist && count($tmp_downlist)>0){
		
		$data['sign'] = $tmp_downlist[0]["mgd_apk_agsin"];//游戏签名
		
		//如果查找成功，则查这个游戏是否有OBB，如果有则传OBB及APK，如果没有则传GPK的
		$tmp_sql_obb = 'SELECT id,mgd_id,apk_patch_size,patch_md5,sign,apk_patch_file,file_type,baidu_url FROM mzw_game_patch 
				        WHERE client_type!=2 AND gv_id='.intval($data["appid"]).' AND mgd_id='.intval($tmp_downlist[0]["mgd_id"]);
		$tmp_obb = $conn->find($tmp_sql_obb,"id");//以自增ID为KEY返回数据
		
		$size = 0;//文件大小
		$down_apk_gpk = '';//APK或者GPK的下载地址
		
		if(isset($tmp_downlist[0]) && $tmp_downlist[0]["type"]==1){//如果是ＧＰＫ
			$filetype = 'gpk';//文件类型
			$adaptation = 1;//是GPK的适配文件
			$down_apk_gpk = $tmp_downlist[0]['mgd_mzw_server_url'];
			//百度网盘下载地址
			$down_apk_gpk_baidu = $tmp_downlist[0]['mgd_baidu_url'];
			
			$size = $tmp_downlist[0]['size'];
			if($tmp_obb && count($tmp_obb)>0){//如果有OBB文件
				$size = 0;
				foreach($tmp_obb as $sub){
					$size += $sub['apk_patch_size'];
					$filepath[] = array(
							'fileName' => end(explode(DS, $sub["apk_patch_file"])),
							'url' => CDN_LANXUN_URL_DOWN.$sub["apk_patch_file"],//先定死是蓝讯CDN的先
							'totalLength' => $sub['apk_patch_size'],
							'fileType' => intval($sub['file_type']),
							'backup' =>CDN_LANXUN_URL_DOWN.$sub["apk_patch_file"]
					);
					$filepath2[] = array(
							'fileName' => end(explode(DS, $sub["apk_patch_file"])),
							'url' => CDN_LESHI_URL_DOWN.$sub["apk_patch_file"],//先定死是乐视CDN的先
							'totalLength' => $sub['apk_patch_size'],
							'fileType' => intval($sub['file_type']),
							'backup' =>CDN_LESHI_URL_DOWN.$sub["apk_patch_file"]
					);
					//如果是GPK里的APK的文件，则用CDN来。
					if(substr($sub["apk_patch_file"],-4)=='.apk'){
						$filepath3[] = array(
								'fileName' => end(explode(DS, $sub["apk_patch_file"])),
								'url' => CDN_LESHI_URL_DOWN.$sub["apk_patch_file"],//先定死是乐视CDN的先
								'totalLength' => $sub['apk_patch_size'],
								'fileType' => intval($sub['file_type']),
								'backup' =>CDN_LESHI_URL_DOWN.$sub["apk_patch_file"]//备用的下载点（先定死是乐视的)
						);
					}else{//如果不是APK文件（即是OBB文件）则用百度网盘来下载
						$filepath3[] = array(
								'fileName' => end(explode(DS, $sub["apk_patch_file"])),
								'url' => $sub["baidu_url"],//用百度网盘的下载地址
								'totalLength' => $sub['apk_patch_size'],
								'fileType' => intval($sub['file_type']),
								'backup' =>CDN_LESHI_URL_DOWN.$sub["apk_patch_file"]//备用的下载点（先定死是乐视的)
						);
					}
				}
			}else{//如果没有OBB文件
				$filepath = Null;
				$filepath2 = Null;
				$filepath3 = Null;
			}
		}else if(isset($tmp_downlist[0]) && $tmp_downlist[0]["type"]==3){//如果是模拟器游戏
			$filetype = 'nes';//文件类型
			$adaptation = -2;//是NES的适配文件
			$down_apk_gpk = $tmp_downlist[0]['mgd_mzw_server_url'];
			//百度网盘下载地址
			$down_apk_gpk_baidu = $tmp_downlist[0]['mgd_baidu_url'];
			
			$size = $tmp_downlist[0]['size'];
			$filepath = Null;
			$filepath2 = Null;
			$filepath3 = Null;
		}else if(isset($tmp_downlist[0]) && ($tmp_downlist[0]["type"]==4 || $tmp_downlist[0]["type"]==5)){//如果是模拟器游戏
			if($tmp_downlist[0]["type"]==4){
				$filetype = 'ISO';//文件类型
			}else{
				$filetype = 'CSO';//文件类型
			}
			
			$adaptation = -3;//是psp的适配文件
			$down_apk_gpk = $tmp_downlist[0]['mgd_mzw_server_url'];
			//百度网盘下载地址
			$down_apk_gpk_baidu = $tmp_downlist[0]['mgd_baidu_url'];
				
			$size = $tmp_downlist[0]['size'];
			$filepath = Null;
			$filepath2 = Null;
			$filepath3 = Null;
		}else{//如果是ＡＰＫ
			$filetype = 'apk';//文件类型
			$adaptation = -1;//是APK的适配文件
			$down_apk_gpk = $tmp_downlist[0]['mgd_mzw_server_url'];
			//百度网盘下载地址
			$down_apk_gpk_baidu = $tmp_downlist[0]['mgd_baidu_url'];
			
			$size = $tmp_downlist[0]['size'];
			$filepath = Null;
			$filepath2 = Null;
			$filepath3 = Null;
		}
		
		//组合蓝讯CDN 相关下载地址
		/*
		$downloadPaths[] = array(
				'id' => -4,
				'name' => $cdn,
				'icon' => CDN_LANXUN_URL_DOWN.'/app420/cdn.png',
				'url' => CDN_LANXUN_URL_DOWN.$down_apk_gpk,//先定死蓝讯的CDN先
				'backup' =>'',
				'visible' =>1 ,
				'parse' =>false,
				'files' =>$filepath
		);
		*/
		//组合乐视CDN 相关下载地址
		$downloadPaths[] = array(
				'id' => -3,
				'name' => $cdn,
				'icon' => CDN_LESHI_URL_DOWN.'/app420/cdn.png',
				'url' => CDN_LESHI_URL_DOWN.$down_apk_gpk,//先定死蓝讯的CDN先
				'backup' =>'',
				'visible' =>1 ,
				'parse' =>false,
				'files' =>$filepath2
		);
		//组合百度网盘 相关下载地址
		//如果百度网盘的下载地址不为空，则加入百度网盘的下载地址
		if( !is_empty($down_apk_gpk_baidu) ){
			$downloadPaths[] = array(
					'id' => -2,
					'name' => $baidu,
					'icon' => CDN_LESHI_URL_DOWN.'/app420/baidu.png',
					'url' => $down_apk_gpk_baidu,//百度网盘的下载地址
					'backup' =>'',
					'visible' =>1 ,
					'parse' =>true,
					'files' =>$filepath3
			);
		}
		
	}else{
		$filetype = 'apk';//文件类型
		$size = 0;//文件大小
	}
	$sign = $data['sign'];//游戏签名
	
	$json = array(
			'title'=>$data["title"],//游戏标题题
			'appid'=>intval($data["appid"]),//游戏版本ID（即game_version 的ID）
			'gid' =>intval($data["gid"]),//游戏ID
			'packagename'=>$data["packagename"],//游戏包名
			'iconpath'=>$tmp_game_ico,//ICO地址
			'corver'=>$tmp_hot_arr[$data["appid"]][2][0],//专题大图
			'icontvpath' =>$tmp_hot_arr[$data["appid"]][3][0],//TV游戏大图
			'icontvindex' =>$tmp_hot_arr[$data["appid"]][5][0],//首页大图
			'icontvdetail' =>$tmp_hot_arr[$data["appid"]][6][0],//详情页大图
			'filetype'=>$filetype,//文件类型
			'fileType'=>$filetype,//文件类型
			'version'=>$data["version"],//游戏版本名称
			'versioncode'=>intval($data["versioncode"]),//游戏版本号
			'size'=>intval($size),//文件大小
			'category'=>$category,//分类名称
			'updatetime'=>$data["updatetime"],//更新时间
			'downloadscount'=> intval($data["downloadscount"]),//下载次数
			'historyVersionCount'=> $online?0:intval($data["historyVersionCount"] -1),//历史版本数量
			'attention'=>$data["attention"],//游戏注意事项
			'updateContent'=>$data["updateContent"],//游戏更新信息
			'language'=> $language?$language:'英语',//游戏语言
			'description'=>$data["description"],//游戏描述
			'downloadPaths'=>$downloadPaths,//相关下载地址（数组来的）
			'screenshot'=>isset($tmp_hot_arr_a)?$tmp_hot_arr_a:array(),//游戏截图
			'smallscreenshot' =>isset($tmp_hot_arr_b)?$tmp_hot_arr_b:array(),//游戏小截图
			'weburl'=>$weburl,//
			'havead'=>$havead,//是否有广告
			'requireInternet'=>$requireInternet,//需WIFI
			'requireGoogleMarket'=>$requireGooglePlay,//需市场
			'datainstallsdcard'=>$propOutCart,//支持外置存贮卡
			'adaptation' =>intval($adaptation),//1GPK包，-1合适的APK包，-2没有合适的APK及GPK包
			'online'=>$online,//是否网络游戏
			'signature' => $sign,//游戏签名
			'propGameHandle'=>$propGameHandle,
			'propMouseController'=>$propMouseController,
			'propCommonController'=>$propCommonController,
			'propCustomController'=>$propCustomController
	);
	$returnArr['rows'][]=$json;
}
if($is_bug_show==100){
	//echo($sql);
	//echo($tmp_sql);
	//echo($tmp_sql_obb);
	//var_dump($returnArr);
	//var_dump($tmp_hot_arr_tmp);
	//exit;
}
$str_encode = responseJson($returnArr,true);
exit($str_encode);



