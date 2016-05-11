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
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['sysversion'] = get_param('sysversion');//安卓系统版本下限
$mydata['cpu']=get_param('cpu');//CPU参数
$mydata['gpu']=get_param('gpu');//GPU参数
$mydata['source']=get_param('source');//访问来源
$mydata['locale'] = get_param('locale');//言语版本
$mydata['density']=get_param('density');//分辨率
$mydata['brand']=get_param('brand');//品牌
$mydata['model']=get_param('model');//型号
$mydata['key'] = get_param('key'); //验证key
$mydata['appid'] = intval(get_param('appid'));//游戏版本ID（即game_version 的ID）
$mydata['kyxversion'] = intval(get_param('kyxversion'));//客户端版本

!is_numeric($mydata['sysversion']) && $mydata['sysversion']=0;
!is_numeric($mydata['cpu']) && $mydata['cpu']=31;
!is_numeric($mydata['density']) && $mydata['density']=15;
$request = $_SERVER['REQUEST_METHOD']; //请求方式

//key校验
$key_auth = kyx_authorize_key($mydata['key'],$request);
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//语言版本类型选择
switch (strtolower($mydata['locale'])) {
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

if(is_empty($mydata['appid'])){
	echo('游戏ID为空！');
	exit;
}

//初始化
$returnArr = array(
    'total' => 1, //数据总数
    'pagecount' => 1, //每页显示数据
    'pagenum' => 1, //当前页
    'rows' => array() //数据数组
);

//查找游戏类型（用于NES兼容判断）
$game_type_sql = "SELECT `gv_id` FROM `mzw_game_version` WHERE `gv_m_status` = 1 AND `gv_id` = ".$mydata['appid']." AND FIND_IN_SET(1,gv_nes_property)>0";
$game_type_data = $conn->get_one($game_type_sql);

//查找适配的GPU
$tmp_sql_gpu = "SELECT gb_id FROM mzw_mobile_gpu_brand WHERE INSTR('".$mydata['gpu']."',gb_params)>0";
$tmp_gpu_id_arr = $conn->find($tmp_sql_gpu,'gb_id');

//查游戏对应版本的信息
$sql = "SELECT gv_id as appid,g_id as gid,gv_type_id as tid,gv_title as title,gv_version_no as versioncode,gv_version_name as version,firm_id as firmid,
	    gv_update_time as updatetime,gv_package_name as  packagename,gv_ico_key as icon,gv_down_nums as downloadscount,gv_kyx_kdr as remoteset,
	    gv_description as description,gv_app_prop as prop,gv_notice as attention,gv_update_text as updateContent,gv_kyx_kdg as handleset,
	    case gv_lang when 0 then '英文' when 1 then '中文' when 2 then '日文' when 3 then '韩文' when 4 then '其它' end language
		FROM `mzw_game_version` WHERE gv_m_status = 1 AND (FIND_IN_SET(2,gv_client_type)>0 OR FIND_IN_SET(3,gv_client_type)>0) AND gv_id=".$mydata['appid'];
$data = $conn->get_one($sql);

//有适配到才查找
if(!empty($data)){

    //游戏属性
    $testInfo = explode(",", $data['prop']);
    $requireGooglePlay = in_array(1, $testInfo) ? true : false; //是否需市场
    $requireInternet = in_array(2, $testInfo) ? true : false; //是否需wifi
    $haveAd = in_array(4, $testInfo) ? true : false; //是否有广告
    $propsCharge = in_array(8, $testInfo) ? true : false; //道具是否收费
    $isRevision = in_array(16, $testInfo) ? true : false; //是否是修改版
    $propGameHandle = in_array(32, $testInfo) ? true : false;//是否支持游戏手柄
    $propMouseController = in_array(64, $testInfo) ? true : false;//是否支持空鼠遥控器
    $propCommonController = in_array(128, $testInfo) ? true : false;//是否支持普通遥控器
    $propCamera = in_array(256, $testInfo) ? true : false;//是否支持体感摄像头
    $propOutCart = in_array(512, $testInfo) ? true : false;//是否支持外置存贮卡
    $propCustomController = in_array(5, $testInfo) ? true : false;//是否支持自定义按键
    $propSimController = in_array(5, $testInfo) ? true : false;//是否支持模拟手柄
    $online = false;//是否网络游戏

    //遥感属性
    $remote = array(
        'LS_UP' => '左摇杆上',
        'LS_DOWN' => '左摇杆下',
        'LS_LEFT' => '左摇杆左',
        'LS_RIGHT' => '左摇杆右',
        'RS_UP' => '右摇杆上',
        'RS_DOWN' => '右摇杆下',
        'RS_LEFT' => '右摇杆左',
        'RS_RIGHT' => '右摇杆右'
    );

    //按键上下左右数据更改
    $dire = array(
        'UP' => '上(菜单)',
        'DOWN' => '下(菜单)',
        'LEFT' => '左(菜单)',
        'RIGHT' => '右(菜单)'
    );

    //手柄配置
    $handleset_str = '';
    $handleset_arr = json_decode($data['handleset'],true);
    if(isset($handleset_arr)&& isset($handleset_arr['descriptors'])){
        foreach ($handleset_arr['descriptors'] as $kdg_val){
            if(isset($kdg_val['keyName'])&& isset($kdg_val['operationName'])){
                if(isset($remote[$kdg_val['keyName']]) && !empty($remote[$kdg_val['keyName']])){
                    $handleset_str .= $remote[$kdg_val['keyName']].'：'.$kdg_val['operationName'];
                }else{
                    $handleset_str .= '按键'.$kdg_val['keyName'].'：';
                    if(isset($dire[$kdg_val['keyName']]) && !empty($dire[$kdg_val['keyName']]) && $kdg_val['operationName'] == $dire[$kdg_val['keyName']]){
                        $handleset_str .= str_replace('(菜单)','',$kdg_val['operationName']);
                    }else{
                        $handleset_str .= $kdg_val['operationName'];
                    }
                }
            }
            $handleset_str .= '<br>';
        }
    }elseif(isset($handleset_arr) && isset($handleset_arr['keys'])){
        foreach($handleset_arr['keys'] as $kdg_val){
            if(isset($kdg_val['keyName'])&& isset($kdg_val['operationName'])){
                if(isset($remote[$kdg_val['keyName']]) && !empty($remote[$kdg_val['keyName']])){
                    $handleset_str .= $remote[$kdg_val['keyName']].'：'.$kdg_val['operationName'];
                }else{
                    $handleset_str .= '按键'.$kdg_val['keyName'].'：';
                    if(isset($dire[$kdg_val['keyName']]) && !empty($dire[$kdg_val['keyName']]) && $kdg_val['operationName'] == $dire[$kdg_val['keyName']]){
                        $handleset_str .= str_replace('(菜单)','',$kdg_val['operationName']);
                    }else{
                        $handleset_str .= $kdg_val['operationName'];
                    }
                }
            }
            $handleset_str .= '<br>';
        }
    }

    //遥控器配置
    $remoteset_str = '';
    $remoteset_arr = json_decode($data['remoteset'],true);
    if(isset($remoteset_arr)&& isset($remoteset_arr['descriptors'])){
        foreach ($remoteset_arr['descriptors'] as $kdg_val){
            if(isset($kdg_val['keyName'])&& isset($kdg_val['operationName'])){
                if(isset($remote[$kdg_val['keyName']]) && !empty($remote[$kdg_val['keyName']])){
                    $remoteset_str .= $remote[$kdg_val['keyName']].'：'.$kdg_val['operationName'];
                }else{
                    $remoteset_str .= '按键'.$kdg_val['keyName'].'：';
                    if(isset($dire[$kdg_val['keyName']]) && !empty($dire[$kdg_val['keyName']]) && $kdg_val['operationName'] == $dire[$kdg_val['keyName']]){
                        $remoteset_str .= str_replace('(菜单)','',$kdg_val['operationName']);
                    }else{
                        $remoteset_str .= $kdg_val['operationName'];
                    }
                }
            }
            $remoteset_str .= '<br>';
        }
    }elseif(isset($remoteset_arr) && isset($remoteset_arr['keys'])){
        foreach($remoteset_arr['keys'] as $kdg_val){
            if(isset($kdg_val['keyName'])&& isset($kdg_val['operationName'])){
                if(isset($remote[$kdg_val['keyName']]) && !empty($remote[$kdg_val['keyName']])){
                    $remoteset_str .= $remote[$kdg_val['keyName']].'：'.$kdg_val['operationName'];
                }else{
                    $remoteset_str .= '按键'.$kdg_val['keyName'].'：';
                    if(isset($dire[$kdg_val['keyName']]) && !empty($dire[$kdg_val['keyName']]) && $kdg_val['operationName'] == $dire[$kdg_val['keyName']]){
                        $remoteset_str .= str_replace('(菜单)','',$kdg_val['operationName']);
                    }else{
                        $remoteset_str .= $kdg_val['operationName'];
                    }
                }
            }
            $remoteset_str .= '<br>';
        }
    }

	$downloadscount = $data['downloadscount']; //总下载数
	$updatetime = isset($data['updatetime']) ? date('Y-m-d', strtotime($data['updatetime'])) : ''; //更新时间
	$language = $data['language']; //语言
	$weburl = ''; //官方网站
	
	//查找这个游戏对应的相关图片（size_id=16 TV游戏上传的cweb格式原尺寸 size_id=17 游戏截图的cweb格式原尺寸 size_id=19 游戏截图详情页游戏截图(534 * 320)）
	$tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type,B.size_id FROM mzw_game_screenshot A 
			    LEFT JOIN mzw_img_path B ON A.img_key=B.img_key
			    WHERE A.gv_id='.$data["appid"]." AND (B.size_id=16 OR B.size_id=17 OR B.size_id=19) ORDER BY B.id DESC ";
	$tmp_hot = $conn->find($tmp_sql);
	$tmp_hot_arr = array();//存放游戏对应的相关图片
	$tmp_hot_arr_tmp = array();//存放游戏载图的临时信息
	if($tmp_hot){
		foreach ($tmp_hot as $val_hot ){
			if($val_hot['size_id']==16){ //TV游戏上传的cweb格式原尺寸
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
				$tmp_hot_arr_tmp[$val_hot['size_id']][] = array('path'=>$tmp_val_hot_img, 'img_key'=>$val_hot['img_key']);
			}
		}
	}

	//找出游戏截图的大图和小图
	$tmp_hot_arr_a = array();//游戏截图的大图
	$tmp_hot_arr_b = array();//游戏截图的小图
    $tmp_hot_arr_k = array(); //游戏截图key校验证（为了处理旧数据存在多张相同截图的问题）
	//先查大图（17的为游戏截图的大图）
    if(!empty($tmp_hot_arr_tmp[17])){
        foreach ($tmp_hot_arr_tmp[17] as $k=>$v){
            if(!isset($tmp_hot_arr_k[$v['img_key']])){
                //游戏截图的大图
                $tmp_hot_arr_k[$v['img_key']] = $v['path'];
                $tmp_hot_arr_a[] = $v['path'];

                //判断小图
                $tmp_small = false;
                foreach ($tmp_hot_arr_tmp[19] as $tmp_k=>$tmp_v){
                    //如果有找到小图
                    if($v['img_key']==$tmp_v['img_key']){
                        $tmp_small = $tmp_v['path'];
                        break;
                    }
                }

                //如果有找到小图，则用小图
                if($tmp_small!=false){
                    $tmp_hot_arr_b[] = $tmp_small;
                }else{//如果没有找到小较长，则小图也用大图的
                    $tmp_hot_arr_b[] = $v['path'];
                }
            }
        }
    }
	
	//查所属分类的名称
	$tmp_sql = 'SELECT t_name_cn as name FROM mzw_game_type WHERE t_id='.$data["tid"];
	$tmp_type = $conn->get_one($tmp_sql);
    $category = isset($tmp_type['name']) ? $tmp_type['name'] : '';


    //获取游戏ICO地址（175 * 175）
    $tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type FROM mzw_game_screenshot A
                LEFT JOIN mzw_img_path B ON A.img_key=B.img_key
                WHERE A.gv_id = '.$data["appid"].' AND A.type = 7 AND B.size_id = 22 ORDER BY B.id DESC';
    $tmp_game_ico_arr = $conn->get_one($tmp_sql);
    $gv_game_ico = isset($tmp_game_ico_arr['path']) ? (LOCAL_URL_DOWN_IMG.$tmp_game_ico_arr['path']) : '';

    //如果没找到175*175的ICO图标，则去100*100的ICO图标
    if(empty($gv_game_ico)){
        $tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
                    LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$data["icon"]
                    ."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
        $tmp_game_ico_arr = $conn->get_one($tmp_sql);
        if($tmp_game_ico_arr){
            $gv_game_ico = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$tmp_game_ico_arr["img_path"]);
        }
    }

	//适配GPU
	$tmp_find_gpu_id = " ( FIND_IN_SET(0,mgd_gpu_id)>0 OR mgd_gpu_id IS NULL ";
    if(!empty($tmp_gpu_id_arr) && is_type($tmp_gpu_id_arr,'Array')){
        foreach ($tmp_gpu_id_arr as $tmp_gpu_id_val){
            $tmp_find_gpu_id .= " OR FIND_IN_SET(".$tmp_gpu_id_val["gb_id"].",mgd_gpu_id)>0 ";
        }
    }
	$tmp_find_gpu_id .= " ) ";

    //获取下载地址相关信息
    $where_str = ' WHERE mgd_client_type != 1 AND gv_id='.$data["appid"].' AND mgd_package_type!=2 AND '.$tmp_find_gpu_id;
    if(isset($game_type_data['gv_id']) && !empty($game_type_data['gv_id'])){ //如果是NES游戏,则拿apk下载地址
        $order_str = " ORDER BY `mgd_package_type` ASC,`mgd_id` DESC ";
    }else{ //如果不是nes，则有gbk拿gbk，没有gbk拿apk
        $order_str = " ORDER BY mgd_package_type DESC,mgd_id DESC ";
    }

    //查文件大小及游戏是APK还是GPK
    $tmp_sql = 'SELECT mgd_id,mgd_package_file_size as `size`,mgd_package_type as `type`,mgd_mzw_server_url,mgd_baidu_url,mgd_apk_agsin,mgd_game_unzip_size as unzip_size
                FROM mzw_game_downlist ' .$where_str.$order_str.' LIMIT 1';//返回1个文件（APK或GPK[如果GPK有的话])
    $tmp_downlist = $conn->get_one($tmp_sql);//以类型作为key返回数据

	if($tmp_downlist && count($tmp_downlist)>0){

        $sign = $tmp_downlist["mgd_apk_agsin"];//游戏签名
		
		//如果查找成功，则查这个游戏是否有OBB，如果有则传OBB及APK，如果没有则传GPK的
		$tmp_sql_obb = 'SELECT id,mgd_id,apk_patch_size,patch_md5,sign,apk_patch_file,file_type,baidu_url FROM mzw_game_patch 
				        WHERE client_type != 1 AND gv_id = '.intval($data["appid"]).' AND mgd_id='.intval($tmp_downlist["mgd_id"]);
		$tmp_obb = $conn->find($tmp_sql_obb,"id");//以自增ID为KEY返回数据
		
		$size = 0;//文件大小
		$down_apk_gpk = '';//APK或者GPK的下载地址
		
		if(isset($tmp_downlist['type']) && $tmp_downlist["type"]==1){//如果是ＧＰＫ
			$filetype = 'gpk';//文件类型
			$adaptation = 1;//是GPK的适配文件
			$down_apk_gpk = $tmp_downlist['mgd_mzw_server_url']; //本地下载地址
			$down_apk_gpk_baidu = $tmp_downlist['mgd_baidu_url']; //百度网盘下载地址
			$size = $tmp_downlist['size']; //游戏大小

			if($tmp_obb && count($tmp_obb)>0){//如果有OBB文件
                $size = 0; //初始化游戏大小
                $filetype = 'obb';//文件类型
				foreach($tmp_obb as $sub){
					$size += $sub['apk_patch_size'];

                    //乐视地址组装
					$filepath2[] = array(
							'fileName' => end(explode(DS, $sub["apk_patch_file"])),
							'url' => CDN_LESHI_URL_DOWN.$sub["apk_patch_file"],//先定死是乐视CDN的先
							'totalLength' => $sub['apk_patch_size'],
							'fileType' => intval($sub['file_type']),
							'backup' =>CDN_LESHI_URL_DOWN.$sub["apk_patch_file"]
					);

					//百度地址组装，如果是GPK里的APK的文件，则用CDN来。
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
				$filepath2 = array();
				$filepath3 = array();
			}
		}else if(isset($tmp_downlist['type']) && $tmp_downlist["type"]==3){//如果是模拟器游戏
			$filetype = 'nes';//文件类型
			$adaptation = -2;//是NES的适配文件
			$down_apk_gpk = $tmp_downlist['mgd_mzw_server_url']; //本地下载地址
			$down_apk_gpk_baidu = $tmp_downlist['mgd_baidu_url']; //百度网盘下载地址
			$size = $tmp_downlist['size'];
			$filepath2 = array();
			$filepath3 = array();
		}else if(isset($tmp_downlist['type']) && ($tmp_downlist["type"]==4 || $tmp_downlist["type"]==5)){//如果是PSP或者MAME游戏
            $filetype = 'PSP';//文件类型
			$adaptation = -3;//是psp的适配文件
			$down_apk_gpk = $tmp_downlist['mgd_mzw_server_url']; //本地下载地址
			$down_apk_gpk_baidu = $tmp_downlist['mgd_baidu_url']; //百度网盘下载地址
			$size = $tmp_downlist['size'];
			$filepath2 = array();
			$filepath3 = array();
		}else{//如果是ＡＰＫ
			$filetype = 'apk';//文件类型
			$adaptation = -1;//是APK的适配文件
			$down_apk_gpk = $tmp_downlist['mgd_mzw_server_url']; //本地下载地址
			$down_apk_gpk_baidu = $tmp_downlist['mgd_baidu_url']; //百度网盘下载地址
			$size = $tmp_downlist['size'];
			$filepath2 = array();
			$filepath3 = array();
		}

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

		//组合百度网盘 相关下载地址,如果百度网盘的下载地址不为空，则加入百度网盘的下载地址
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
        $adaptation = -1;//是APK的适配文件
        $downloadPaths = array(); //游戏下载
	}

    //数据映射
	$json = array(
			'title' => $data["title"],//游戏标题题
			'appid' => intval($data["appid"]),//游戏版本ID（即game_version 的ID）
			'gid' => intval($data["gid"]),//游戏ID
			'packagename' => $data["packagename"],//游戏包名
			'iconpath' => $gv_game_ico,//ICO地址
			'corver' => isset($tmp_hot_arr[$data["appid"]][2]) ? $tmp_hot_arr[$data["appid"]][2][0] : NULL,//专题大图
			'icontvpath' => isset($tmp_hot_arr[$data["appid"]][3]) ? $tmp_hot_arr[$data["appid"]][3][0] : NULL,//TV游戏大图
			'icontvindex' => isset($tmp_hot_arr[$data["appid"]][5]) ? $tmp_hot_arr[$data["appid"]][5][0] : NULL,//首页大图
			'icontvdetail' =>isset($tmp_hot_arr[$data["appid"]][6]) ? $tmp_hot_arr[$data["appid"]][6][0] : NULL,//详情页大图
            'fileType' => $filetype,//文件类型
			'version' => $data["version"],//游戏版本名称
			'versioncode' => intval($data["versioncode"]),//游戏版本号
			'size' => intval($size),//文件大小
            'unzipsize' => intval($tmp_downlist['unzip_size']), //GBK解压后文件大小
			'category' => $category,//分类名称
			'updatetime' => (isset($data["updatetime"]) && !empty($data["updatetime"])) ? date('Y-m-d',strtotime($data["updatetime"])) : '',//更新时间
			'downloadscount' => intval($data["downloadscount"]),//下载次数
			'attention' => $data["attention"],//游戏注意事项
			'updateContent' => $data["updateContent"],//游戏更新信息
			'language' => $language ? $language : '英语',//游戏语言
			'description' => $data["description"],//游戏描述
			'downloadPaths' => $downloadPaths,//相关下载地址（数组来的）
			'screenshot' => $tmp_hot_arr_a,//游戏截图
			'smallscreenshot' => $tmp_hot_arr_b,//游戏小截图
			'weburl' => $weburl,//官方网站
            'requireGoogleMarket'=>$requireGooglePlay,//需市场
            'requireInternet' => $requireInternet,//需WIFI
            'haveAd' => $haveAd,//是否有广告
            'propsCharge' => $propsCharge,//道具是否收费
            'isRevision' => $isRevision,//是否是修改版
            'propGameHandle' => $propGameHandle,//是否支持游戏手柄
            'propMouseController' => $propMouseController,//是否支持空鼠遥控器
            'propCommonController' => $propCommonController,//是否支持普通遥控器
            'propCamera' => $propCamera, //是否支持体感摄像头
            'propOutCard' => $propOutCart, //是否支持外置存贮卡
            'propCustomController' => $propCustomController, //是否支持自定义按键
            'propSimController' => $propSimController, //是否支持模拟手柄
            'online' => $online,//是否网络游戏
			'adaptation' => $adaptation, //1GPK包，-1合适的APK包，-2没有合适的APK及GPK包
			'signature' => isset($sign) ? $sign : '', //游戏签名
            'remoteset' => $remoteset_str, //遥控器配置
            'handleset' => $handleset_str //手柄配置
	);
    unset($downloadPaths);

	//游戏相关（分类相关）
	if(!empty($data["tid"])){
	    $sql = "SELECT `gv_title` AS `title`,`gv_ico_key` as `icon`,`gv_id`,`gv_description` as description,`gv_package_name`,`gv_type_id` as tid FROM `mzw_game_version`
                WHERE `gv_m_status` = 1 AND (FIND_IN_SET(2,`gv_client_type`) > 0 OR FIND_IN_SET(3,`gv_client_type`) > 0)
                AND `gv_id` <> {$data['appid']} AND `gv_type_id`={$data['tid']}  ORDER BY RAND()  LIMIT 3 ";
        $tid_data = $conn->find($sql);
	    $arr_related = array();
	    if(!empty($tid_data)){
	        foreach ($tid_data as $value) {

                //获取游戏ICO地址（175 * 175）
                $tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type FROM mzw_game_screenshot A
			                LEFT JOIN mzw_img_path B ON A.img_key=B.img_key
                            WHERE A.gv_id = '.$value["gv_id"].' AND A.type = 7 AND B.size_id = 22 ORDER BY B.id DESC';
                $tmp_game_ico_arr = $conn->get_one($tmp_sql);
                $tmp_game_ico = isset($tmp_game_ico_arr['path']) ? (LOCAL_URL_DOWN_IMG.$tmp_game_ico_arr['path']) : '';

                //如果没找到175*175的ICO图标，则去100*100的ICO图标
                if(empty($tmp_game_ico)){
                    $tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
                                LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$value["icon"]
                                ."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
                    $tmp_game_ico_arr = $conn->get_one($tmp_sql);
                    if($tmp_game_ico_arr){
                        $tmp_game_ico = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$tmp_game_ico_arr["img_path"]);
                    }
                }

                //数据映射
                $arr_related[] = array(
                    'appid' => intval($value['gv_id']), //游戏版本id
                    'packagename' => !empty($value['gv_package_name']) ? trim($value['gv_package_name']) : '', //游戏包名
                    'iconpath' => $tmp_game_ico, //游戏ICO地址
                    'title' => trim($value['title']), //游戏标题
                    'description' => $value['description'], //游戏描述
                    'category' => $value['tid'] //分类id
                );
	        }
	    }
	    $about_data['related'] = $arr_related;
        unset($arr_related,$tid_data);
	}

    //游戏相关（开发商相关）
    if(!empty($data["firmid"])){
        $sql = "SELECT `gv_title` AS `title`,`gv_ico_key` as `icon`,`gv_id`,`gv_description` as description,`gv_package_name`,`gv_type_id` as tid FROM `mzw_game_version`
                WHERE `gv_m_status` = 1 AND (FIND_IN_SET(2,`gv_client_type`) > 0 OR FIND_IN_SET(3,`gv_client_type`) > 0)
                AND `gv_id` <> {$data['appid']} AND  `firm_id`={$data['firmid']}  ORDER BY `gv_down_nums` DESC LIMIT 3 ";
        $firm_data = $conn->find($sql);
        $arr_firm_game = array();
        if(!empty($firm_data)){
            foreach ($firm_data as $value) {

                //获取游戏ICO地址（175 * 175）
                $tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type FROM mzw_game_screenshot A
			                LEFT JOIN mzw_img_path B ON A.img_key=B.img_key
                            WHERE A.gv_id = '.$value["gv_id"].' AND A.type = 7 AND B.size_id = 22 ORDER BY B.id DESC';
                $tmp_game_ico_arr = $conn->get_one($tmp_sql);
                $tmp_game_ico = isset($tmp_game_ico_arr['path']) ? (LOCAL_URL_DOWN_IMG.$tmp_game_ico_arr['path']) : '';

                //如果没找到175*175的ICO图标，则去100*100的ICO图标
                if(empty($tmp_game_ico)){
                    $tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
                                LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$value["icon"]
                                ."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
                    $tmp_game_ico_arr = $conn->get_one($tmp_sql);
                    if($tmp_game_ico_arr){
                        $tmp_game_ico = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$tmp_game_ico_arr["img_path"]);
                    }
                }

                //数据映射
                $arr_firm_game[] = array(
                    'appid' => intval($value['gv_id']), //游戏版本id
                    'packagename' => !empty($value['gv_package_name']) ? trim($value['gv_package_name']) : '', //游戏包名
                    'iconpath' => $tmp_game_ico, //游戏ICO地址
                    'title' => trim($value['title']), //游戏标题
                    'description' => $value['description'], //游戏描述
                    'category' => $value['tid'] //分类id
                );
            }
        }
        $about_data['firmgame'] = $arr_firm_game;
        unset($arr_firm_game,$firm_data);
    }

    //热门手柄
    $sql = "SELECT `gv_title` AS `title`,`gv_ico_key` as `icon`,`gv_id`,`gv_description` as description,`gv_package_name`,`gv_type_id` as tid FROM `mzw_game_version`
            WHERE `gv_m_status` = 1 AND (FIND_IN_SET(2,`gv_client_type`) > 0 OR FIND_IN_SET(3,`gv_client_type`) > 0)
            AND `gv_id` <> {$data['appid']} ORDER BY `gv_down_nums` DESC LIMIT 3 ";
    $handle_data = $conn->find($sql);
    $arr_handle_game = array();
    if(!empty($handle_data)){
        foreach ($handle_data as $value) {

            //获取游戏ICO地址（175 * 175）
            $tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type FROM mzw_game_screenshot A
                        LEFT JOIN mzw_img_path B ON A.img_key=B.img_key
                        WHERE A.gv_id = '.$value["gv_id"].' AND A.type = 7 AND B.size_id = 22 ORDER BY B.id DESC';
            $tmp_game_ico_arr = $conn->get_one($tmp_sql);
            $tmp_game_ico = isset($tmp_game_ico_arr['path']) ? (LOCAL_URL_DOWN_IMG.$tmp_game_ico_arr['path']) : '';

            //如果没找到175*175的ICO图标，则去100*100的ICO图标
            if(empty($tmp_game_ico)){
                $tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
                            LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$value["icon"]
                            ."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
                $tmp_game_ico_arr = $conn->get_one($tmp_sql);
                if($tmp_game_ico_arr){
                    $tmp_game_ico = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$tmp_game_ico_arr["img_path"]);
                }
            }

            //数据映射
            $arr_handle_game[] = array(
                'appid' => intval($value['gv_id']), //游戏版本id
                'packagename' => !empty($value['gv_package_name']) ? trim($value['gv_package_name']) : '', //游戏包名
                'iconpath' => $tmp_game_ico, //游戏ICO地址
                'description' => $value['description'], //游戏描述
                'title' => trim($value['title']), //游戏标题
                'category' => $value['tid'] //分类id
            );
        }
    }
    $about_data['handlegame'] = $arr_handle_game;
    unset($arr_handle_game,$handle_data);

    //获取相关游戏下载地址数组
    if(!empty($about_data) && is_type($about_data,'Array')){
        foreach($about_data as $akey => $aval){
            if(!empty($aval)){
                foreach($aval as $kkey => $vval){

                    //查找游戏类型（用于NES兼容判断）
                    $game_type_sql = "SELECT `gv_id` FROM `mzw_game_version` WHERE `gv_m_status` = 1 AND `gv_id` = ".$vval['appid']." AND FIND_IN_SET(1,gv_nes_property)>0";
                    $game_type_data = $conn->get_one($game_type_sql);

                    //获取下载地址相关信息
                    $where_str = ' WHERE mgd_client_type != 1 AND gv_id='.$vval["appid"].' AND mgd_package_type!=2 AND '.$tmp_find_gpu_id;
                    if(isset($game_type_data['gv_id']) && !empty($game_type_data['gv_id'])){ //如果是NES游戏,则拿apk下载地址
                        $order_str = " ORDER BY mgd_id DESC ";
                    }else{ //如果不是nes，则有gbk拿gbk，没有gbk拿apk
                        $order_str = " ORDER BY mgd_package_type DESC,mgd_id DESC ";
                    }

                    //查所属分类的名称
                    $tmp_sql = 'SELECT t_name_cn as name FROM mzw_game_type WHERE t_id='.$vval["category"];
                    $tmp_type = $conn->get_one($tmp_sql);
                    $category = isset($tmp_type['name']) ? $tmp_type['name'] : '';

                    //查文件大小及游戏是APK还是GPK
                    $tmp_sql = 'SELECT mgd_id,mgd_package_file_size as `size`,mgd_package_type as `type`,mgd_mzw_server_url,mgd_baidu_url,mgd_apk_agsin,mgd_game_unzip_size as unzip_size
                                FROM mzw_game_downlist ' .$where_str.$order_str.' LIMIT 1';//返回1个文件（APK或GPK[如果GPK有的话])
                    $tmp_downlist = $conn->get_one($tmp_sql);//以类型作为key返回数据

                    if($tmp_downlist && count($tmp_downlist)>0){

                        $sign = $tmp_downlist["mgd_apk_agsin"];//游戏签名

                        //如果查找成功，则查这个游戏是否有OBB，如果有则传OBB及APK，如果没有则传GPK的
                        $tmp_sql_obb = 'SELECT id,mgd_id,apk_patch_size,patch_md5,sign,apk_patch_file,file_type,baidu_url FROM mzw_game_patch
				                        WHERE client_type != 1 AND gv_id = '.intval($vval["appid"]).' AND mgd_id='.intval($tmp_downlist["mgd_id"]);
                        $tmp_obb = $conn->find($tmp_sql_obb,"id");//以自增ID为KEY返回数据

                        $size = 0;//文件大小
                        $down_apk_gpk = '';//APK或者GPK的下载地址

                        if(isset($tmp_downlist['type']) && $tmp_downlist["type"]==1){//如果是ＧＰＫ
                            $filetype = 'gpk';//文件类型
                            $adaptation = 1;//是GPK的适配文件
                            $down_apk_gpk = $tmp_downlist['mgd_mzw_server_url']; //本地下载地址
                            $down_apk_gpk_baidu = $tmp_downlist['mgd_baidu_url']; //百度网盘下载地址
                            $size = $tmp_downlist['size']; //游戏大小

                            if($tmp_obb && count($tmp_obb)>0){//如果有OBB文件
                                $size = 0; //初始化游戏大小
                                $filetype = 'obb';//文件类型
                                foreach($tmp_obb as $sub){
                                    $size += $sub['apk_patch_size'];

                                    //乐视地址组装
                                    $filepath2[] = array(
                                        'fileName' => end(explode(DS, $sub["apk_patch_file"])),
                                        'url' => CDN_LESHI_URL_DOWN.$sub["apk_patch_file"],//先定死是乐视CDN的先
                                        'totalLength' => $sub['apk_patch_size'],
                                        'fileType' => intval($sub['file_type']),
                                        'backup' =>CDN_LESHI_URL_DOWN.$sub["apk_patch_file"]
                                    );
                                }
                            }else{//如果没有OBB文件
                                $filepath2 = array();
                            }
                        }else if(isset($tmp_downlist['type']) && $tmp_downlist["type"]==3){//如果是模拟器游戏
                            $filetype = 'nes';//文件类型
                            $adaptation = -2;//是NES的适配文件
                            $down_apk_gpk = $tmp_downlist['mgd_mzw_server_url']; //本地下载地址
                            $down_apk_gpk_baidu = $tmp_downlist['mgd_baidu_url']; //百度网盘下载地址
                            $size = $tmp_downlist['size'];
                            $filepath2 = array();
                        }else if(isset($tmp_downlist['type']) && ($tmp_downlist["type"]==4 || $tmp_downlist["type"]==5)){//如果是PSP或者MAME游戏
                            $filetype = 'PSP';//文件类型
                            $adaptation = -3;//是psp的适配文件
                            $down_apk_gpk = $tmp_downlist['mgd_mzw_server_url']; //本地下载地址
                            $down_apk_gpk_baidu = $tmp_downlist['mgd_baidu_url']; //百度网盘下载地址
                            $size = $tmp_downlist['size'];
                            $filepath2 = array();
                        }else{//如果是ＡＰＫ
                            $filetype = 'apk';//文件类型
                            $adaptation = -1;//是APK的适配文件
                            $down_apk_gpk = $tmp_downlist['mgd_mzw_server_url']; //本地下载地址
                            $down_apk_gpk_baidu = $tmp_downlist['mgd_baidu_url']; //百度网盘下载地址
                            $size = $tmp_downlist['size'];
                            $filepath2 = array();
                        }

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
                    }else{
                        $filetype = 'apk';//文件类型
                        $adaptation = -1;//是APK的适配文件
                        $size = 0;//文件大小
                        $downloadPaths = array(); //游戏下载
                    }

                    //分类相关
                    if($akey == 'related'){
                        $json[$akey][$kkey]['mid'] = intval($data["tid"]); //分类id
                        $json[$akey][$kkey]['tid'] = -1; //分类属性id
                        $json[$akey][$kkey]['category_title'] = '相关推荐';
                    }elseif($akey == 'firmgame'){ //厂商相关
                        $json[$akey][$kkey]['mid'] = intval($data["firmid"]); //厂商id
                        $json[$akey][$kkey]['tid'] = -2; //分类属性id
                        $json[$akey][$kkey]['category_title'] = '开发商其他游戏';
                    }else{ //手柄相关
                        $json[$akey][$kkey]['mid'] = 0;
                        $json[$akey][$kkey]['tid'] = -3; //分类属性id
                        $json[$akey][$kkey]['category_title'] = '热门手柄游戏';
                    }

                    $json[$akey][$kkey]['appid'] = intval($vval['appid']); //游戏版本id
                    $json[$akey][$kkey]['packagename'] = $vval['packagename']; //游戏包名
                    $json[$akey][$kkey]['iconpath'] = $vval['iconpath']; //游戏ICO地址
                    $json[$akey][$kkey]['title'] = $vval['title']; //游戏标题
                    $json[$akey][$kkey]['fileType'] = $filetype; //文件类型
                    $json[$akey][$kkey]['size'] = intval($size); //文件大小
                    $json[$akey][$kkey]['adaptation'] = $adaptation; //1GPK包，-1合适的APK包，-2没有合适的APK及GPK包
                    $json[$akey][$kkey]['category'] = $category; //分类名称
                    $json[$akey][$kkey]['description'] = $vval['description']; //游戏描述
                    $json[$akey][$kkey]['signature'] = $tmp_downlist["mgd_apk_agsin"]; //游戏签名
                    $json[$akey][$kkey]['unzipsize'] = intval($tmp_downlist['unzip_size']); //GBK解压后文件大小
                    $json[$akey][$kkey]['downloadPaths'] = $downloadPaths; //相关下载地址（数组来的）
                    unset($downloadPaths);
                }
            }
        }
    }

	$returnArr['rows'][] = $json;
}

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
    exit(responseJson($returnArr,false));
}

exit(responseJson($returnArr,true));



