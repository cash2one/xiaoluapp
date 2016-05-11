<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 返回客户端首页需要显示的游戏（进行了GPU适配的）
 * 设定要调传的传区ID为12
 * @file:home_hotgame.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-11-10  15:38
 * @version 1.0
 **/
include_once("../../config.inc.php");
include_once("../../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['key'] = get_param('key');
$mydata['pagenum'] = intval(get_param('pagenum'));
$mydata['pagesize'] = intval(get_param('pagesize'));
$mydata['pagesize'] = empty($mydata['pagesize'])?10:$mydata['pagesize'];
$mydata['gpu']=get_param('gpu');//CPU型号，字符串

$mydata['kyxversion'] = get_param('kyxversion');//整形 客户端版本

//设置的内置存贮卡是否挂载（1是未挂载，0是已经挂载)
$mydata['insdcardunmount'] = intval(get_param('insdcardunmount'));




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


//不显示大游戏（即不显示GPK的游戏）
$tmp_where = ' ';
if($mydata['insdcardunmount']==1){
	$tmp_where = ' AND FIND_IN_SET(23,gv.g_tags_property)<1 ';
}

//查询游戏专区中间表游戏信息
$m_area_sql = 'SELECT g_id,gv_id FROM mzw_game_m_a_relation WHERE ga_id=19 ORDER BY g_order DESC';
$m_area_data = $conn->find($m_area_sql);
$g_id_str = ''; //id存储字符串
$gv_id_str = ''; //gv_id字符串
if(!empty($m_area_data)){
    foreach($m_area_data as $m_a_val){
        $g_id_str .= $m_a_val['g_id'].',';
        $gv_id_str .= $m_a_val['gv_id'].',';
    }
    $g_id_str = rtrim($g_id_str,',');
    $gv_id_str = rtrim($gv_id_str,',');
}

$orderby = " ORDER BY FIND_IN_SET(gv.g_id,'".$g_id_str."') ";

//查总数据行数
//192,171,191,188,182,173,174,185,183,186
//$sql = "SELECT count(1) as num FROM `mzw_game` g,`mzw_game_version` gv WHERE gv.gv_id in(192,193,191,190,184,173,182,184,168,181) AND gv.gv_id in($tmp_sql_gpu_in) AND gv.gv_status=1 AND  g.g_id=gv.g_id  ";
$sql = "SELECT count(1) as num FROM `mzw_game` g,`mzw_game_version` gv
        WHERE (FIND_IN_SET(1,gv.gv_client_type)>0 OR FIND_IN_SET(3,gv.gv_client_type)>0) AND g.g_id=gv.g_id  AND gv.g_id in(".$g_id_str.") AND gv.gv_id in(".$gv_id_str.") AND gv.gv_id in($tmp_sql_gpu_in)".$tmp_where;
$data_count = $conn->count($sql);

$PageMax=ceil($data_count/$mydata['pagesize']);//最大页数
$mydata['pagenum'] > $PageMax && $mydata['pagenum']=$PageMax;//当前页
$mydata['pagenum']==0 && $mydata['pagenum']=1;
$ParamPage=($mydata['pagenum']-1)*($mydata['pagesize']);//查询从第几行开始查

$returnArr=array('total'=>$data_count,'pagecount'=>$mydata['pagesize'],'pagenum'=>$mydata['pagenum'],'rows'=>array(),'error'=>NULL,'update'=>time());


//查数据
/*$sql_data = "SELECT g.g_version_nums as historyVersionCount,gv.gv_id as appid, gv.gv_type_id as tid,gv.gv_title as title,gv.gv_version_no as versioncode,gv.gv_version_name as version,
		 gv.gv_publish_time as published, gv.gv_update_time as edittime, gv.gv_package_name as packagename,gv.gv_ico_key as icon,gv.gv_down_nums as downcount 
		FROM `mzw_game` g,`mzw_game_version` gv WHERE  gv.gv_id in(192,193,191,190,184,173,182,184,168,181)  AND gv.gv_id in($tmp_sql_gpu_in) AND gv.gv_status=1 AND  g.g_id=gv.g_id LIMIT ".$ParamPage.",".$mydata['pagesize'];
*/

$sql_data = "SELECT g.g_version_nums as historyVersionCount,gv.gv_id as appid, gv.gv_type_id as tid,gv.gv_title as title,gv.gv_version_no as versioncode,gv.gv_version_name as version,
gv.gv_publish_time as published, gv.gv_update_time as edittime, gv.gv_package_name as packagename,gv.gv_ico_key as icon,gv.gv_down_nums as downcount
FROM `mzw_game` g,`mzw_game_version` gv WHERE (FIND_IN_SET(1,gv.gv_client_type)>0 OR FIND_IN_SET(3,gv.gv_client_type)>0) AND g.g_id=gv.g_id AND gv.g_id in(".$g_id_str.") AND gv.gv_id in(".$gv_id_str.") AND gv.gv_id in($tmp_sql_gpu_in) $tmp_where $orderby LIMIT ".$ParamPage.",".$mydata['pagesize'];

$data = $conn->find($sql_data);

if($data){
	//$tmp_data_arr = array(192,193,191,190,184,173,182,184,168,181);
	$tmp_arr_return = array();
	foreach ($data as $val){
		
		//查文件大小
		$tmp_sql = 'SELECT mgd_package_file_size as size,mgd_package_type as type FROM mzw_game_downlist
			        WHERE mgd_client_type != 2 AND gv_id='.$val["appid"]." AND mgd_package_type!=2 ORDER BY mgd_package_type DESC,mgd_id DESC";
		$tmp_downlist = $conn->find($tmp_sql);
		if($tmp_downlist){
			foreach ($tmp_downlist as $val_downlist ){
				if(isset($val_downlist["mgd_package_type"]) && $val_downlist["mgd_package_type"]==1){//如果是GPK文件
					$filetype = 'gpk';//文件类型
					$size = $val_downlist["size"];//文件大小
				}else{//否则就是APK文件了
					$filetype = 'apk';//文件类型
					$size = $val_downlist["size"];//文件大小
				}
				break;
			}
		}else{
			$filetype = 'apk';//文件类型
			$size = 0;//文件大小
		}
/*		//查找这个游戏对应的相关图片
		$tmp_sql = 'SELECT img_key,path,type FROM mzw_game_screenshot WHERE gv_id='.$val["appid"]." ORDER BY id DESC ";
		$tmp_hot = $conn->find($tmp_sql);
		$tmp_hot_arr = array();//存放游戏对应的相关图片
		if($tmp_hot){
			foreach ($tmp_hot as $val_hot ){
				$tmp_hot_arr[$val["appid"]][$val_hot["type"]][] = LOCAL_URL_DOWN_IMG.$val_hot["path"];
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
				$tmp_hot_arr[$val["appid"]][$val_hot["type"]][] = $tmp_val_hot_img;
			}
		}
		
		
		$gv_game_ico = '';//ICO地址
		$tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
				LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$val["icon"]."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
		$tmp_game_ico_arr = $conn->find($tmp_sql);
		if($tmp_game_ico_arr){
			$tmp_game_ico = LOCAL_URL_DOWN_IMG.$tmp_game_ico_arr[0]["img_path"];
		}
		//查所属分类的名称
		$tmp_sql = 'SELECT t_name_cn as name FROM mzw_game_type WHERE t_id='.$val["appid"];
		$tmp_type = $conn->find($tmp_sql);
		if($tmp_type){
			$category = $tmp_type[0]["name"];
		}
		$online = true;
		
		$json = array(
				'appid'=>intval($val[   "appid"]),//游戏版本ID（game_version 的ID）
				'title'=>$val["title"],//游戏版本标题
				'filetype'=>$filetype,//文件类型（APK 或者是 GPK）
				'size'=>intval($size),//文件大小
				'category'=>isset($category) ? $category : '',//游戏分类名称
				'packagename'=>$val["packagename"],//游戏包名
				'version'=>$val["version"],//游戏版本名
				'versioncode'=>intval($val["versioncode"]),//游戏版本号
				'updatetime'=>$val["edittime"],//更新时间
				'downloadscount'=>intval($val["downcount"]),//下载次数
				'iconpath'=>$tmp_game_ico,//ICO图片
				'icontvpath' => isset($tmp_hot_arr[$val["appid"]][3][0]) ? $tmp_hot_arr[$val["appid"]][3][0] : '',//TV游戏大图
				'corver'=>isset($tmp_hot_arr[$val["appid"]][2][0]) ? $tmp_hot_arr[$val["appid"]][2][0] : '',//专题大图
				'historyVersionCount'=>$online?0:intval($val["historyVersionCount"] -1),//历史版本个数
				'indexflags'=>0,
				'updated'=>false,
				'newest'=>(time()- strtotime($val['published'])<3600*24*3)?1:0, //发布时间小于三天intval($val["historyVersionCount"])==1&&(time()-intval($val["published"])<3600*24*7),//是否本周游戏
				'online'=>$online,//是否网络游戏
				'icontvindex' =>isset($tmp_hot_arr[$val["appid"]][5][0]) ? $tmp_hot_arr[$val["appid"]][5][0] : '',//首页大图
				'icontvdetail' =>isset($tmp_hot_arr[$val["appid"]][6][0]) ? $tmp_hot_arr[$val["appid"]][6][0] : ''//详情页大图
		);
		//$tmp_arr_return[$val["appid"]] = $json;
		$returnArr['rows'][] = $json;
	}
}

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试

if($is_bug_show==100){
	echo($sql_data);
	var_dump($returnArr);
	exit;
}

$str_encode = responseJson($returnArr,true);
exit($str_encode);


