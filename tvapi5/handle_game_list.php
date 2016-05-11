<?php
/**
 * @copyright: @快游戏 2014
 * @description: 返回支持模拟按键的游戏列表
 * @file: handle_game_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-05-25  19:17
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['pagenum']=intval(get_param('pagenum'));//当前页
$mydata['pagesize']=intval(get_param('pagesize'));//每页大小
$mydata['pagesize']=empty($mydata['pagesize'])?12:$mydata['pagesize'];

$mydata['key'] = get_param('key');//验证KEY

//验证key是否正确
verify_key_kyx($mydata['key']);

$where = '';
$where .=  " AND FIND_IN_SET(6,gv.gv_app_prop) > 0 AND gv.gv_status = 1 AND g.g_id = gv.g_id ";
$orderby = ' ORDER BY gv.gv_id  desc ';

$data = array();
//查总数据行数
$sql = "SELECT count(1) as num FROM `mzw_game` g,`mzw_game_version` gv WHERE 1 $where ";
$data_count = $conn->count($sql);

$PageMax=ceil($data_count/$mydata['pagesize']);//最大页数
$mydata['pagenum'] > $PageMax && $mydata['pagenum']=$PageMax;//当前页
$mydata['pagenum']==0 && $mydata['pagenum']=1;
$ParamPage=($mydata['pagenum']-1)*($mydata['pagesize']);//查询从第几行开始查

//初始化要返回的数据
$returnArr=array(
    'total'=>$data_count,
    'pagecount'=>$mydata['pagesize'],
    'pagenum'=>$mydata['pagenum'],
    'rows'=>array(),
    'error'=>NULL,
    'update'=>time()
);

//查数据
$sql_data = "SELECT g.is_new as iffirst,g.g_version_nums as versioncount,gv.gv_id as vid, gv.gv_type_id as tid,gv.gv_title as vtitle,gv.gv_version_no as versioncode,gv.gv_description as description,
			 gv.gv_version_name as version, gv.gv_publish_time as published, gv.gv_update_time as edittime, gv.gv_package_name as package,gv.gv_ico_key as icon,gv.gv_down_nums as downcount ".
			" FROM `mzw_game` g,`mzw_game_version` gv WHERE 1 $where $orderby LIMIT $ParamPage,".$mydata['pagesize'];
$data = $conn->find($sql_data);

//查所属分类的名称
$tmp_sql = 'SELECT t_name_cn as name,t_id FROM mzw_game_type WHERE t_status = 1 ';
$tmp_type = $conn->find($tmp_sql,'t_id');

foreach ($data as $key =>$v){

	//查所属分类的名称
	$category = isset($tmp_type[$v['tid']]['name'])?$tmp_type[$v['tid']]['name']:'';
	
	//查文件大小
	$tmp_sql = 'SELECT mgd_id,mgd_game_size as size,mgd_package_type as type FROM mzw_game_downlist 
			WHERE gv_id='.$v["vid"]." AND mgd_package_type!=2 ORDER BY mgd_package_type DESC,mgd_id DESC";
	$tmp_downlist = $conn->find($tmp_sql);
	if($tmp_downlist){
		if(isset($tmp_downlist[0]) && $tmp_downlist[0]["type"]==1){//如果是ＧＰＫ
			$filetype = 'gpk';//文件类型
			$size = $tmp_downlist[0]['size'];
		}else{//如果是ＡＰＫ
			$filetype = 'apk';//文件类型
			$size = $tmp_downlist[0]['size'];
		}

	}else{
		$filetype = 'apk';//文件类型
		$size = 0;//文件大小
	}

	//查找这个游戏对应的相关图片
	$tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type FROM mzw_game_screenshot A
			LEFT JOIN mzw_img_path B ON A.img_key=B.img_key
			WHERE A.gv_id='.$v["vid"]." AND (B.size_id=16 OR B.size_id=17) ORDER BY B.id DESC";
	$tmp_hot = $conn->find($tmp_sql);
	$tmp_hot_arr = array();//存放游戏对应的相关图片
	if($tmp_hot){
		foreach ($tmp_hot as $val_hot ){
			if(substr($val_hot['src_path'],-3)=='jpg'){//如果源图就是jpg的，则返回源图
				$tmp_val_hot_img = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$val_hot["src_path"]);
			}else{
				$tmp_val_hot_img = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$val_hot["path"]);
			}
			$tmp_hot_arr[$v["vid"]][$val_hot["type"]] = $tmp_val_hot_img;
		}
	}

	$gv_game_ico = '';//ICO地址
	$tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A 
				LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$v["icon"]."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
	$tmp_game_ico_arr = $conn->find($tmp_sql);
	if($tmp_game_ico_arr){
		$tmp_game_ico = LOCAL_URL_DOWN_IMG.$tmp_game_ico_arr[0]["img_path"];
	}
	
	
	
	$arr = array('appid'=>intval($v['vid']),
			'title'=>$v['vtitle'],//游戏名字
			'filetype'=>$filetype,//文件类型
			'size'=>intval($size),//文件大小
			'category'=>$category,//分类名称
            'description' => $v['description'], //游戏描述
			'packagename'=>$v['package'],//游戏包名
			'version'=>$v['version'],//游戏版本名
			'versioncode'=>intval($v['versioncode']),//游戏版本号
			'updatetime'=>date('Y-m-d', strtotime($v['published'])),//发布时间
			'downloadscount'=>intval($v['downcount']),//下载次数
			'iconpath'=>$tmp_game_ico,//ICO地址
			'corver'=>isset($tmp_hot_arr[$v["vid"]][2]) ? $tmp_hot_arr[$v["vid"]][2] : '',//专题大图
			'icontvpath' =>isset($tmp_hot_arr[$v["vid"]][3]) ? $tmp_hot_arr[$v["vid"]][3] : '',//TV游戏大图
			'historyVersionCount'=>intval($v['versioncount'])-1,//游戏版本总个数
			'indexflags'=>0,
			'updated'=>false,	//版本数量大于1，是更新版本
			'newest'=>(time()- strtotime($v['published'])<3600*24*3)?1:0, //发布时间小于三天
			'online'=>isset($v['online']) ? $v['online'] : 0,//是否网络游戏
			'icontvindex' =>isset($tmp_hot_arr[$v["vid"]][5]) ? $tmp_hot_arr[$v["vid"]][5] : '',//首页大图
			'icontvdetail' =>isset($tmp_hot_arr[$v["vid"]][6]) ? $tmp_hot_arr[$v["vid"]][6] : ''//详情页大图
	);
	$returnArr['rows'][]=$arr;
}

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
	echo($sql);
	echo($sql_data);
	var_dump($returnArr);
	exit;
}

$str_encode = responseJson($returnArr,true);
exit($str_encode);

