<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取全部游戏分类下面的指定条数的游戏（进行了GPU适配的）,并加密JSON内容进行输出返回
 * @file: game_category_list.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-11-10  15:38
 * @version 1.0
 **/
include_once("../../config.inc.php");
include_once("../../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['gpu']=get_param('gpu');//CPU型号，字符串
$mydata['pagesize']=intval(get_param('pagesize'));//每页大小
$mydata['pagesize']=empty($mydata['pagesize'])?12:$mydata['pagesize'];

$mydata['kyxversion'] = intval(get_param('kyxversion'));//客户端版本
//设置的内置存贮卡是否挂载（1是未挂载，0是已经挂载)
$mydata['insdcardunmount'] = intval(get_param('insdcardunmount'));

if(is_empty($mydata['gpu'])){
	echo('gpu信息不能为空！');
	exit;
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
//查文件大小及游戏是APK还是GPK
$tmp_sql_gpu_in = 'SELECT DISTINCT gv_id FROM mzw_game_downlist
			WHERE mgd_package_type!=2 AND '.$tmp_find_gpu_id;
//=============end 适配ＧＰＵ

//查分类的信息
$sql = "SELECT t_id as tid,t_name_cn as title,t_img_key as icom,t_p_id AS type FROM mzw_game_type WHERE t_status = 1 ORDER BY t_order_num DESC";
$data = $conn->find($sql);
//查最新游戏数据(-1)
$json_a = array(
		'tid'=>-1,//分类ID
		'title'=>'最新游戏',//分类名字
		'icon'=>'' //分类手机图标
);
//查大型游戏标签数据(-2)
$json_b = array(
		'tid'=>-2,//分类ID
		'title'=>'大型游戏',//分类名字
		'icon'=>'', //分类手机图标
);
//查模拟器游戏标签数据(-3)
$json_c = array(
		'id'=>-3,//分类ID
		'title'=>'模拟器',//分类名字
		'icon'=>'', //分类手机图标
		'counts'=>0 //游戏个数
);
//查多人游戏游戏标签数据(-5)
$json_d = array(
		'id'=>-5,//分类ID
		'title'=>'多人游戏',//分类名字
		'icon'=>'', //分类手机图标
		'counts'=>0 //游戏个数
);
$data[]=$json_a;

//不显示大游戏（即不显示GPK的游戏）
if($mydata['insdcardunmount']==0){
$data[]=$json_b;
}
if($mydata['kyxversion']>100){
	$returnArr['rows'][]=$json_c; //模拟器游戏 标签数据(-3)
	$returnArr['rows'][]=$json_d; //多人游戏 标签数据(-5)
}
$returnArr["rows"]=array();
foreach ($data as $data_val){
	//查每个分类里的游戏个数
	$orderby = ' order by gv.gv_down_nums  desc ';
	if($data_val['tid']==-1){//如果是最新游戏的分类ID，则一定是按最新来排序
		$orderby = ' order by  gv.gv_id  desc ';
	}
	$where = ' AND gv.gv_status=1 ';
	if($data_val['tid']>0){//如果是合法的分类ID，则
		$where .=  " and  gv.gv_type_id=".$data_val['tid']." ";
	}
	if($data_val['tid']==-2){//如果是 大型游戏分类ID(大型游戏的标筌ID为：23)
		$where .=  " AND  FIND_IN_SET(23,gv.g_tags_property)>0 ";
	}
	//查数据
	$sql_data = "SELECT gv.gv_id as vid, gv.gv_type_id as tid,gv.gv_title as vtitle,gv.gv_version_no as versioncode,
			 gv.gv_version_name as version, gv.gv_publish_time as published, gv.gv_update_time as edittime, gv.gv_package_name as package,gv.gv_ico_key as icon,gv.gv_down_nums as downcount ".
			" FROM `mzw_game_version` gv WHERE 1 AND gv.gv_id IN($tmp_sql_gpu_in) 
			 $where  $orderby LIMIT 0,".$mydata['pagesize'];
	$game_data = $conn->find($sql_data);
	if($game_data){
		//查这个分类下总数据的个数
		$sql_data_count = "SELECT count(*) as num FROM `mzw_game_version` gv WHERE 1 AND gv.gv_id IN($tmp_sql_gpu_in) 
			 $where ";
		$game_data_count = $conn->count($sql_data_count);		
		$data_arr=array('total'=>$game_data_count,'id'=>$data_val['tid'],'title'=>$data_val['title'],'icon'=>LOCAL_URL_DOWN_IMG.$data_val['icom'],'datas'=>array());
		foreach ($game_data as $game_data_val){
			
			//查文件大小
			$tmp_sql = 'SELECT mgd_id,mgd_package_file_size as size,mgd_package_type as type FROM mzw_game_downlist
			WHERE gv_id='.$game_data_val["vid"]." AND mgd_package_type!=2 ORDER BY mgd_package_type DESC,mgd_id DESC";
			$tmp_downlist = $conn->find($tmp_sql);
			if($tmp_downlist){
				if(isset($tmp_downlist[0]) && $tmp_downlist[0]["type"]==1){//如果是ＧＰＫ
					//如果查找成功，则查这个游戏是否有OBB，如果有则传OBB及APK，如果没有则传GPK的
					$tmp_sql_obb = 'SELECT id,mgd_id,apk_patch_size,patch_md5,sign,apk_patch_file,file_type FROM mzw_game_patch
				WHERE gv_id='.intval($game_data_val["vid"]).' AND mgd_id='.intval($tmp_downlist[0]["mgd_id"]);
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
			
			//查找这个游戏对应的相关图片
			$tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type FROM mzw_game_screenshot A
			LEFT JOIN mzw_img_path B ON A.img_key=B.img_key
			WHERE A.gv_id='.$game_data_val["vid"]." AND (B.size_id=16 OR B.size_id=17) ORDER BY B.id DESC";
			$tmp_hot = $conn->find($tmp_sql);
			$tmp_hot_arr = array();//存放游戏对应的相关图片
			if($tmp_hot){
				foreach ($tmp_hot as $val_hot ){
					if(substr($val_hot['src_path'],-3)=='jpg'){//如果源图就是jpg的，则返回源图
						$tmp_val_hot_img = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$val_hot["src_path"]);
					}else{
						$tmp_val_hot_img = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$val_hot["path"]);
					}
					$tmp_hot_arr[$game_data_val["vid"]][$val_hot["type"]] = $tmp_val_hot_img;
				}
			}

			$data_arr['datas'][]=array(
				'appid'=>$game_data_val["vid"],//游戏版本ID
				'title'=>$game_data_val['vtitle'],//游戏名字
				'packagename'=>$game_data_val['package'],//游戏包名
				'size'=>intval($size),//文件大小
				'icontvpath' =>$tmp_hot_arr[$game_data_val["vid"]][3]//TV游戏大图
			);
		}
	}else{
		$data_arr=array('total'=>0,'id'=>$data_val['tid'],'title'=>$data_val['title'],'icon'=>'','datas'=>array());
	}
	$returnArr["rows"][] = $data_arr;
}

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
	echo($sql_data);
	var_dump($returnArr);
	exit;
}

$str_encode = responseJson($returnArr,true);
exit($str_encode);


