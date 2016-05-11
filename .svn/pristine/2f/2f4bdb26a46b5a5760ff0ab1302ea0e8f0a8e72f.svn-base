<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取游戏 TV 专辑游戏列表,并加密JSON内容进行输出返回
 * @file: album_gamelist.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-11-10  15:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['key'] = get_param('key');//验证KEY
$mydata['pagenum'] = get_param('pagenum');//当前页
$mydata['pagenum'] = empty($mydata['pagenum'])?1:$mydata['pagenum'];
$mydata['pagesize'] = get_param('pagesize');//每页大小
$mydata['pagesize'] = empty($mydata['pagesize'])?32:$mydata['pagesize'];

$mydata['name'] = get_param('name');//专辑名称
$mydata['id'] = intval(get_param('id'));//专辑ID

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试

$offset = ($mydata['pagenum']-1)*$mydata['pagesize'];

//验证key是否正确
verify_key_kyx($mydata['key']);

$limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";
if(is_empty($mydata['id']) || $mydata['id']==-1){
	$tmp_ga_id = 11;//定死某个专区ID先，后面再按其它条件来查数据
}else{
	$tmp_ga_id = $mydata['id'];
}

//查数据条数
$sql_count = "SELECT count(*) as num "
		. "FROM mzw_game_version A LEFT JOIN mzw_game_m_a_relation B ON A.g_id = B.g_id
		WHERE (FIND_IN_SET(1,A.gv_client_type)>0 OR FIND_IN_SET(3,A.gv_client_type)>0) AND A.gv_status=1 AND B.ga_id = $tmp_ga_id";
$data_count = $conn->find($sql_count);

//定义回转的默认参数
$returnArr = array('total'=>$data_count[0]['num'],'pagecount'=>$mydata['pagesize'],'pagenum'=>$mydata['pagenum'],'rows'=>array(),'error'=>NULL,'update'=>time());
//查专区的信息
//$sql = 'SELECT * FROM mzw_game_mobile_area WHERE ga_status=1 AND ga_id='.$tmp_ga_id;
//$tmp_data = $conn->find($sql);
//unset($sql);

$sql = "SELECT A.g_id as gid,A.gv_id as appid,A.gv_type_id as tid,A.gv_title as title,A.gv_version_name as version,A.gv_update_time as updatetime,
		A.gv_publish_time as published,A.gv_package_name as packagename,A.gv_ico_key as icon,A.gv_down_nums as downloadscount,A.gv_version_no as versioncode "
		. "FROM mzw_game_version A LEFT JOIN mzw_game_m_a_relation B ON A.g_id = B.g_id
		WHERE (FIND_IN_SET(1,A.gv_client_type)>0 OR FIND_IN_SET(3,A.gv_client_type)>0) AND A.gv_status=1 AND B.ga_id = $tmp_ga_id ORDER BY A.gv_id $limit";
$data = $conn->find($sql);
if($data && count($data)>0){
foreach ($data as $val){
	
	//查找这个游戏对应的相关图片
/*	$tmp_sql = 'SELECT img_key,path,type FROM mzw_game_screenshot WHERE gv_id='.$val["appid"]." ORDER BY id DESC ";
	$tmp_hot = $conn->find($tmp_sql);
	$tmp_hot_arr = array();//存放游戏对应的相关图片
	if($tmp_hot){
		foreach ($tmp_hot as $val_hot ){
			$tmp_hot_arr[$val["appid"]][$val_hot["type"]] = LOCAL_URL_DOWN_IMG.$val_hot["path"];
		}
	}
	*/
	//查找这个游戏对应的相关图片
	$tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type FROM mzw_game_screenshot A
			LEFT JOIN mzw_img_path B ON A.img_key=B.img_key
			WHERE A.gv_id='.$val["appid"]." AND (B.size_id=16 OR B.size_id=17) ORDER BY B.id DESC";
	$tmp_hot = $conn->find($tmp_sql);
	$tmp_hot_arr = array();//存放游戏对应的相关图片
	if($tmp_hot){
		foreach ($tmp_hot as $val_hot ){
			if(substr($val_hot['src_path'],-3)=='jpg'){//如果源图就是jpg的，则返回源图
				$tmp_val_hot_img = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$val_hot["src_path"]);
			}else{
				$tmp_val_hot_img = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$val_hot["path"]);
			}
			$tmp_hot_arr[$val["appid"]][$val_hot["type"]] = $tmp_val_hot_img;
		}
	}
	
	
	
	
	//查所属分类的名称
	$tmp_sql = 'SELECT t_name_cn as name FROM mzw_game_type WHERE t_id='.$val["tid"];
	$tmp_type = $conn->find($tmp_sql);
	if($tmp_type){
		$category = $tmp_type[0]["name"];
	}
	//查文件大小
	$tmp_sql = 'SELECT mgd_id,mgd_package_file_size as size,mgd_package_type as type FROM mzw_game_downlist 
			    WHERE mgd_client_type != 2 AND gv_id='.$val["appid"]." AND mgd_package_type!=2 ORDER BY mgd_package_type DESC,mgd_id DESC";
	$tmp_downlist = $conn->find($tmp_sql);
	
	if($tmp_downlist){
		if(isset($tmp_downlist[0]) && $tmp_downlist[0]["type"]==1){//如果是ＧＰＫ
			//如果查找成功，则查这个游戏是否有OBB，如果有则传OBB及APK，如果没有则传GPK的
			$tmp_sql_obb = 'SELECT id,mgd_id,apk_patch_size,patch_md5,sign,apk_patch_file,file_type FROM mzw_game_patch
				            WHERE client_type != 2 AND gv_id='.intval($val["appid"]).' AND mgd_id='.intval($tmp_downlist[0]["mgd_id"]);
			$tmp_obb = $conn->find($tmp_sql_obb,"id");//以自增ID为KEY返回数据
				
			$filetype = 'gpk';//文件类型
			$size = $tmp_downlist[0]['size'];
			if($tmp_obb && count($tmp_obb)>0){//如果有OBB文件
				$size = 0;
				foreach($tmp_obb as $sub){
					$size += $sub['apk_patch_size'];
				}
			}
		}else{//如果是ＡＰＫ
			$filetype = 'apk';//文件类型
			$size = $tmp_downlist[0]['size'];
		}
	
	}else{
		$filetype = 'apk';//文件类型
		$size = 0;//文件大小
	}
	
	//查游戏历史版本的个数
	$tmp_sql = 'SELECT g_version_nums as historyVersionCount FROM mzw_game WHERE g_id='.$val["gid"];
	$tmp_game = $conn->find($tmp_sql);
	if($tmp_game){
		$historyVersionCount = $tmp_game[0]["historyVersionCount"];
	}else{
		$historyVersionCount = 0;
	}
	
	$gv_game_ico = '';//ICO地址
	$tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
				LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$val["icon"]."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
	$tmp_game_ico_arr = $conn->find($tmp_sql);
	if($tmp_game_ico_arr){
		$tmp_game_ico = LOCAL_URL_DOWN_IMG.$tmp_game_ico_arr[0]["img_path"];
	}
	
	$online = true;
	$json = array(
			'appid'=>intval($val["appid"]),//游戏版本的ID
			'title'=>$val["title"],//游戏中文标题
			'filetype'=>$filetype,//文件类型（APK 或者 GPK）
			'size'=>intval($size),//文件大小
			'category'=>$category,//游戏分类名
			'packagename'=>$val["packagename"],//游戏包名
			'version'=>$val["version"],//游戏版本名
			'versioncode'=>intval($val["versioncode"]),//游戏版本号
			'updatetime'=>$val["updatetime"],//游戏更新时间
			'downloadscount'=>intval($val["downloadscount"]),//游戏下载次数
			'iconpath'=>$tmp_game_ico,//游戏的ICO文件
			'icontvpath' =>$tmp_hot_arr[$val["appid"]][3],//TV游戏大图
			'corver'=>isset($tmp_hot_arr[$val["appid"]][2])?$tmp_hot_arr[$val["appid"]][2]:"",//游戏专题图片
			'historyVersionCount'=>$online?0:intval($historyVersionCount -1),//游戏历史版本数量
			'indexflags'=>0,//
			'updated'=>false,//
			'newest'=>(time()- strtotime($val['published'])<3600*24*3)?1:0, //发布时间小于三天intval($historyVersionCount)==1&&(time()-intval($val["published"])<3600*24*7),//是否一周内发布的
			'online'=>true,//是否 网游分类
			'iconTvIndex' =>isset($tmp_hot_arr[$val["appid"]][5])?$tmp_hot_arr[$val["appid"]][5]:"",//首页大图
			'iconTvDetail' =>isset($tmp_hot_arr[$val["appid"]][6])?$tmp_hot_arr[$val["appid"]][6]:""//详情页大图
	);
	$returnArr['rows'][]=$json;
}
}
if($is_bug_show==100){
	echo($sql);
	
	var_dump($returnArr);
	exit;
}

$str_encode = responseJson($returnArr,true);
exit($str_encode);

?>