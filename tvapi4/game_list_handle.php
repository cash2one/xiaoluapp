<?php
/**
 * @copyright: @快游戏 2014
 * @description: 返回游戏列表(进行了GPU适配）
 * @file: game_list_handle.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-11-10  15:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['mzwversion']=get_param('mzwversion');//拇指玩版本
$mydata['sysversion']=intval(get_param('sysversion'));//系统版本
$mydata['cpu']=get_param('cpu');//CPU型号，字符串
$mydata['gpu']=get_param('gpu');//GPU型号，字符串（在用）
$mydata['source']=get_param('source');//来源
$mydata['locale'] = get_param('locale');//语言
$mydata['density']=get_param('density');////屏幕密度，int类型
$mydata['brand']=get_param('brand'); //品牌（在用）
$mydata['model']=get_param('model'); //型号（在用）
$mydata['versioncode']=intval(get_param('versioncode'));//版本号
$mydata['versionname']=get_param('versionname');//版本名
$mydata['pagenum']=intval(get_param('pagenum'));//当前页
$mydata['pagesize']=intval(get_param('pagesize'));//每页大小
$mydata['pagesize']=empty($mydata['pagesize'])?12:$mydata['pagesize'];
//分类ID(-1设定为最新游戏，-2设定为 大型游戏分类ID(大型游戏的标筌ID为：23))
$mydata['category']=intval(get_param('category'));//（在用）
$mydata['type'] = intval(get_param("type"));//排序类型0最新，1最热(在用)

$mydata['controllerid'] = intval(get_param("controllerid"));//手柄ID
$mydata['controllerbid'] = intval(get_param("controllerbid"));//手柄品牌ID

$mydata['kyxversion'] = get_param('kyxversion');//整形 客户端版本

$mydata['pid'] = trim(get_param('pid'));//产品ID
$mydata['vid'] = trim(get_param('vid'));//厂商ID
$mydata['mid'] = trim(get_param('mid'));//设备描述
$mydata['cname'] = trim(get_param('cname'));//设备名称
$mydata['keys'] = trim(get_param('keys'));//按键信息

$mydata['key'] = get_param('key');//验证KEY
$mydata['language'] = get_param('language');//语言
$mydata['language'] = empty($mydata['language'])?'zh':get_param('language');

//设置的内置存贮卡是否挂载（1是未挂载，0是已经挂载)
$mydata['insdcardunmount'] = intval(get_param('insdcardunmount'));


//验证key是否正确
verify_key_kyx($mydata['key']);


$where = ' AND gv.gv_status=1 AND g.g_id=gv.g_id ';
//不显示大游戏（即不显示GPK的游戏）
if($mydata['insdcardunmount']==1){
	$where .= ' AND FIND_IN_SET(23,gv.g_tags_property)<1 ';
}

//如果是国际版本，英文标题为空的不返回
if($mydata['language']=='en'){
    $where .= ' AND   gv.gv_title_en!="" ';
}



//===========begin适配GPU
//查找适配的GPU
$tmp_sql_gpu = "SELECT gb_id FROM mzw_mobile_gpu_brand WHERE INSTR('".$mydata['gpu']."',gb_params)>0";
$tmp_gpu_id_arr = $conn->find($tmp_sql_gpu,'gb_id');
$tmp_find_gpu_id = " ( FIND_IN_SET(0,mgd_gpu_id)>0 ";
foreach ($tmp_gpu_id_arr as $tmp_gpu_id_val){
	$tmp_find_gpu_id .= " OR FIND_IN_SET(".$tmp_gpu_id_val["gb_id"].",mgd_gpu_id)>0 ";
}
$tmp_find_gpu_id .= " ) ";
//查找排除的型号$mydata['model']
$tmp_find_model_id = "";
if(!is_empty($mydata['model'])){
	$tmp_sql_model_dis = "SELECT model_id FROM mzw_mobile_model WHERE INSTR('".$mydata['model']."',model_params)>0";
	$tmp_model_id_arr = $conn->find($tmp_sql_model_dis,'model_id');
	if(!is_empty($tmp_model_id_arr)){
		foreach ($tmp_model_id_arr as $tmp_model_id_val){
			$tmp_find_model_id .= " AND FIND_IN_SET(".$tmp_model_id_val["model_id"].",mgd_shield_mobile)<1 ";
		}
	}else{//如果没有找到对应的型号，看下是否禁止了未知型号
		$tmp_find_model_id .= " AND FIND_IN_SET(25,mgd_shield_mobile)<1 ";
	}
}
//查文件大小及游戏是APK还是GPK
$tmp_sql_gpu_in = 'SELECT DISTINCT gv_id FROM mzw_game_downlist
			       WHERE mgd_client_type != 2 AND mgd_package_type!=2 AND '.$tmp_find_gpu_id.$tmp_find_model_id;
//=============end 适配ＧＰＵ

//===========begin适配手柄
if( !is_empty($mydata['pid']) && !is_empty($mydata['vid'])
		&& !is_empty($mydata['mid']) && !is_empty($mydata['cname'])
		&& !is_empty($mydata['keys']) ){
	//去掉 反引用
	$mydata['keys'] = stripslashes($mydata['keys']);
	//适配按键信息是否在四种模式之中的
	$tmp_pattern = get_handle_pattern($mydata['keys']);
	
	//查数据
	$sql_data_h = "SELECT id,mb_id,gh_pattern FROM `mzw_game_handle`
                   WHERE gh_state=1 AND INSTR(gh_pid,'".$mydata['pid']."')>0 AND INSTR(gh_vid,'".$mydata['vid']
                   ."')>0 AND INSTR(gh_mid,'".$mydata['mid']."')>0 AND INSTR(gh_device_name,'".$mydata['cname']."')>0";
	$data = $conn->get_one($sql_data_h);
	//如果按键的模式正确，而且有找到这个手柄的ID
	if(count($data)>1){
		$mydata['controllerid'] = intval($data['id']);
		$mydata['controllerbid'] = intval($data['mb_id']);
	}
}
//如果获取到的是手柄ID和手柄品牌ID，则
if($mydata['controllerbid']>0 && $mydata['controllerid']>0){
	
	$tmp_sql_handle_in = 'SELECT DISTINCT gv_id FROM mzw_game_handle_relation
			              WHERE mb_id='.$mydata['controllerbid'].' AND (gh_id='.$mydata['controllerid'].' OR gh_id=0)';

	//找到要排除的手柄数据，则
	$where .= " AND gv.gv_id NOT IN(".$tmp_sql_handle_in.") ";

	
	//查对应手柄支持的模式
	$sql_data = "SELECT gh_pattern FROM `mzw_game_handle` WHERE id=".$mydata['controllerid'];
	$data_handle = $conn->get_one($sql_data);
	if($data_handle && isset($data_handle['gh_pattern'])){
		$tmp_handle = explode(',', $data_handle['gh_pattern']);
		//如果有数据
		$tmp_pattern_where = '';
		if(count($tmp_handle)>0){
			if(is_array($tmp_handle)){
				foreach ($tmp_handle as $tmp_handle_v){
					if( $tmp_handle_v > 0 ){
						$tmp_pattern_where .= " AND FIND_IN_SET(".$tmp_handle_v.",gv.gv_mb_pattern)>0 ";
					}
				}
			}else{
				$tmp_pattern_where .= " AND FIND_IN_SET(".$tmp_handle.",gv.gv_mb_pattern)>0 ";
			}
		}
	}
	//如果有模式的条件，则
	if(!is_empty($tmp_pattern_where)){
		//去掉最前面的 AND
		$tmp_pattern_where = ' AND ('.substr($tmp_pattern_where,5,strlen($tmp_pattern_where));
		// 需求说支持自定义按键的游戏可以适配所有的手柄
		$tmp_pattern_where .=  " OR FIND_IN_SET(5,gv.gv_app_prop)>0) ";
	}else{
		// 需求说支持自定义按键的游戏可以适配所有的手柄
		$tmp_pattern_where .=  " AND FIND_IN_SET(5,gv.gv_app_prop)>0 ";
	}
	$where .= $tmp_pattern_where;
	//去掉模拟器游戏
	$where .= " AND (gv.gv_nes_property='' OR gv.gv_nes_property is null) ";
	

}else{//如果没有找到对应的手柄
	//如果有模式
	if( isset($tmp_pattern) && $tmp_pattern>0 ){
		$where .= " AND ( FIND_IN_SET(".$tmp_pattern.",gv.gv_mb_pattern)>0  OR FIND_IN_SET(5,gv.gv_app_prop)>0) ";
	}else{
		$where .= " AND FIND_IN_SET(5,gv.gv_app_prop)>0 ";
	}
	//去掉模拟器游戏
	$where .= " AND (gv.gv_nes_property='' OR gv.gv_nes_property is null) ";
}
$orderby = ' order by gv.gv_down_nums  desc ';
//===========end 适配手柄


$data = array();
//查总数据行数
$sql = "SELECT count(1) as num FROM `mzw_game` g,`mzw_game_version` gv
        WHERE (FIND_IN_SET(1,gv.gv_client_type)>0 OR FIND_IN_SET(3,gv.gv_client_type)>0) AND gv.gv_id IN($tmp_sql_gpu_in) $where ";
$data_count = $conn->count($sql);

$PageMax=ceil($data_count/$mydata['pagesize']);//最大页数
$mydata['pagenum'] > $PageMax && $mydata['pagenum']=$PageMax;//当前页
$mydata['pagenum']==0 && $mydata['pagenum']=1;
$ParamPage=($mydata['pagenum']-1)*($mydata['pagesize']);//查询从第几行开始查

//初始化要返回的数据
$returnArr=array('total'=>$data_count,'pagecount'=>$mydata['pagesize'],'pagenum'=>$mydata['pagenum'],'rows'=>array(),'error'=>NULL,'update'=>time());

//查数据
$sql_data = "SELECT g.is_new as iffirst,g.g_version_nums as versioncount,gv.gv_id as vid, gv.gv_type_id as tid,gv.gv_title as vtitle,gv.gv_title_en as vtitle_en,gv.gv_version_no as versioncode,
			 gv.gv_version_name as version, gv.gv_publish_time as published, gv.gv_update_time as edittime, gv.gv_package_name as package,gv.gv_ico_key as icon,gv.gv_down_nums as downcount ".
			" FROM `mzw_game` g,`mzw_game_version` gv WHERE (FIND_IN_SET(1,gv.gv_client_type)>0 OR FIND_IN_SET(3,gv.gv_client_type)>0) AND gv.gv_id IN($tmp_sql_gpu_in) $where $orderby LIMIT $ParamPage,".$mydata['pagesize'];
//echo($sql_data);exit;
$data = $conn->find($sql_data);

//查所属分类的名称
$tmp_sql = 'SELECT t_name_cn as name,t_name_en as name_en,t_id FROM mzw_game_type WHERE t_status=1 ';
$tmp_type = $conn->find($tmp_sql,'t_id');

foreach ($data as $key =>$v){

	//获取分类名称
	$category = isset($tmp_type[$v['tid']]['name'])?$tmp_type[$v['tid']]['name']:'';
	switch ($mydata['language']) {
		case 'zh':
			   $category = isset($tmp_type[$v['tid']]['name'])?$tmp_type[$v['tid']]['name']:'';
		break;
		case 'en':
		    $category = isset($tmp_type[$v['tid']]['name_en'])?$tmp_type[$v['tid']]['name_en']:'';
		break;
		default:
		    $category = isset($tmp_type[$v['tid']]['name'])?$tmp_type[$v['tid']]['name']:'';
		break;
	}
	
	//查文件大小
	$tmp_sql = 'SELECT mgd_id,mgd_game_size as size,mgd_package_type as type FROM mzw_game_downlist 
			    WHERE mgd_client_type != 2 AND gv_id='.$v["vid"]." AND mgd_package_type!=2 ORDER BY mgd_package_type DESC,mgd_id DESC";
	$tmp_downlist = $conn->find($tmp_sql);
	if($tmp_downlist){
		if(isset($tmp_downlist[0]) && $tmp_downlist[0]["type"]==1){//如果是ＧＰＫ
			$filetype = 'gpk';//文件类型
			$size = $tmp_downlist[0]['size'];

		}else if(isset($tmp_downlist[0]) && $tmp_downlist[0]["type"]==3){//如果是模拟器游戏
			$filetype = 'nes';//文件类型
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
			'packagename'=>$v['package'],//游戏包名
			'version'=>$v['version'],//游戏版本名
			'versioncode'=>intval($v['versioncode']),//游戏版本号
			'updatetime'=>date('Y-m-d', strtotime($v['published'])),//发布时间
			'downloadscount'=>intval($v['downcount']),//下载次数
			'iconpath'=>$tmp_game_ico,//ICO地址
			'corver'=>$tmp_hot_arr[$v["vid"]][2],//专题大图
			'icontvpath' =>$tmp_hot_arr[$v["vid"]][3],//TV游戏大图
			'historyVersionCount'=>intval($v['versioncount'])-1,//游戏版本总个数
			'indexflags'=>0,
			'updated'=>false,	//版本数量大于1，是更新版本
			'newest'=>(time()- strtotime($v['published'])<3600*24*3)?1:0, //发布时间小于三天
			'online'=>$v['online'],//是否网络游戏
			'icontvindex' =>$tmp_hot_arr[$v["vid"]][5],//首页大图
			'icontvdetail' =>$tmp_hot_arr[$v["vid"]][6]//详情页大图
	);
	switch ($mydata['language']) {
		case 'zh':
		    $arr['title'] = $v['vtitle'];//游戏名字
		    break;
		case 'en':
		    $arr['title'] = $v['vtitle_en'];//英文游戏名字
		    break;
		default:
		    $arr['title'] = $v['vtitle'];//游戏名字
		    break;
	}
	$returnArr['rows'][]=$arr;
}

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
	echo($mydata['keys']);
	echo($sql_data);
	echo($sql_data_h.chr(10));
	var_dump($returnArr);
	exit;
}

$str_encode = responseJson($returnArr,true);
exit($str_encode);

