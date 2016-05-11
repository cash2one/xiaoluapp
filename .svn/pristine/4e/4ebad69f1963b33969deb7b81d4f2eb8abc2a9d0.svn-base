<?php
/*
*
* 查询可更新游戏
*
*/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['cpu']=get_param('cpu');//CPU型号，字符串
$mydata['gpu']=get_param('gpu');//GPU型号，字符串（在用）
$mydata['brand']=get_param('brand'); //品牌（在用）
$mydata['model']=get_param('model'); //型号（在用）
$mydata['key']=get_param('key'); //验证KEY
$mydata['apps']=get_param('apps'); //要检查更新的数据(游戏信息)
/*
 * apps内JSON数据项每一项的值：
 * {"packagename":"com.activision.skylanders.trapteam",
 * "sign":"9187cfa8ce1896d100c2be467baf81a7",
 * "title":"诱捕小队",
 * "versionname":"1.3.0",
 * "versioncode":27}
 */
/*
 * 要记录的日志格式
 * 记录日志JSON数据项每一项的值：
 * {"pname":"com.activision.skylanders.trapteam",//包名
 * "sign":"9187cfa8ce1896d100c2be467baf81a7",//包签名
 * "title":"诱捕小队",//应用名称
 * "VName":"1.3.0",//应用版本名称
 * "VCode":27,//应用版本号
 * "NVName":1.3.2//应用新版本名称
 * "NVCode":28//应用新版本号
 * }
 */
if(is_empty($mydata['apps'])){
	echo('数据出错');
	exit;
}

//验证key是否正确
verify_key_kyx($mydata['key']);

/*
$tmp_str = '<kkkkk>'.chr(10).chr(10);
$tmp_str .= $mydata['cpu'].chr(10).chr(10)
			.$mydata['gpu'].chr(10).chr(10)
			.$mydata['brand'].chr(10).chr(10)
			.$mydata['model'].chr(10).chr(10)
			.$mydata['key'].chr(10).chr(10)
			.stripslashes($mydata['apps']).chr(10).chr(10);

sys_log_write_content( $tmp_str ,"sys_log","applist_update_log");
*/

$cdn = '普通下载';

$game_list=json_decode(stripslashes($mydata['apps']),true) ;
//共计需要查询多少个游戏的更新情况
$game_count = count($game_list);

//共计需要更新多少个游戏
$update_num = 0;

$json = array();
$returnArr = array('total'=>$update_num,'rows'=>array());
if (count($game_list)>0){
    //获取用户手机基础信息
    $game_info = $game_list;
    //保存日志时的数据
    /*格式
     * array(
    	"pname"=>'',//包名
    	"sign"=>'',//包签名
    	"title"=>'',//应用名称
    	"VName"=>'',//应用版本名称
    	"VCode"=>'',//应用版本号
    	"NVName"=>'',//应用新版本名称
    	"NVCode"=>'',//应用新版本号
    	"time"=>''//当时时间
    	);
     */
    $save_game_info = Null;
    for ($i=0;$i< $game_count; $i++){
    	if($game_info[$i]['packagename']=='com.ratrodstudio.snowparty'){
    		continue;
    	}
    	$save_game_info[$i] = array(
    			"pname"=>$game_info[$i]['packagename'],//包名
    			"sign"=>$game_info[$i]['sign'],//包签名
    			"title"=>$game_info[$i]['title'],//应用名称
    			"VName"=>$game_info[$i]['versionname'],//应用版本名称
    			"VCode"=>$game_info[$i]['versioncode'],//应用版本号
    			"NVName"=>'0',//应用新版本名称
    			"NVCode"=>'0',//应用新版本号
    			"time"=>THIS_DATETIME//当时时间
    	);
    	
    	
        $PackageName=$game_info[$i]['packagename'];
        $VersionCode=$game_info[$i]['versioncode'];
        $sql = "SELECT * FROM `mzw_game_version` WHERE (FIND_IN_SET(1,gv_client_type)>0 OR FIND_IN_SET(3,gv_client_type)>0) AND gv_package_name='$PackageName' AND ((gv_version_no>$VersionCode AND gv_status=1) OR gv_version_no=$VersionCode) ORDER BY gv_version_no DESC";
        //echo($sql);
        //echo("<br>".chr(10));
        $_row = $conn->find($sql,'gv_id');
        //判断是否有新包(如果大于1条记录，则表示有更新包)
        if(count($_row)>1){
            //获取这个游戏的所有版本的gv_id
            $tmp_gv_id_list = "";
            foreach ($_row as $k=>$v){
            	$tmp_gv_id_list .= $v['gv_id'].",";
            }
            //重置数组
            reset($_row);
            $tmp_gv_id_list = rtrim($tmp_gv_id_list,",");
            
        	//===========begin适配GPU,查所有合适下载版本的游戏
        	//查找适配的GPU
        	$tmp_sql_gpu = "SELECT gb_id FROM mzw_mobile_gpu_brand WHERE INSTR('".$mydata['gpu']."',gb_params)>0";
        	$tmp_gpu_id_arr = $conn->find($tmp_sql_gpu,'gb_id');
        	$tmp_find_gpu_id = " ( FIND_IN_SET(0,mgd_gpu_id)>0 ";
        	foreach ($tmp_gpu_id_arr as $tmp_gpu_id_val){
        		$tmp_find_gpu_id .= " OR FIND_IN_SET(".$tmp_gpu_id_val["gb_id"].",mgd_gpu_id)>0 ";
        	}
        	$tmp_find_gpu_id .= " ) ";
        	$tmp_sql_gpu_in = 'SELECT * FROM mzw_game_downlist
			WHERE mgd_client_type!=2 AND gv_id IN ('.$tmp_gv_id_list.') AND mgd_package_type!=2 AND '.$tmp_find_gpu_id.' ORDER BY mgd_package_type DESC,mgd_package_up_v_code DESC';
        	//=============end 适配ＧＰＵ,查所有合适下载版本的游戏
        	//echo($tmp_sql_gpu_in);
        	
        	$_row2 = $conn->find($tmp_sql_gpu_in);
        	//如果没有找到对应合适下载的版本游戏，则表示不需要更新
        	if(count($_row2)<1){
        		continue;
        	}
        	//找出最新的适配的可升级版本
        	$tmp_new_game = '';
        	//旧版本游戏的gv_id
        	$tmp_old_gv_id = '';
        	//旧版本的适配版本
        	$tmp_old_game = '';
        	//临时的旧版本downlist数据
        	$tmp_tmp_old_game = '';
        	foreach ($_row as $tmp_k=>$tmp_v){
        		//查旧版本游戏的gv_id
        		if($tmp_v['gv_version_no']==$VersionCode){
        			$tmp_old_gv_id = $tmp_v['gv_id'];
        		}
        		foreach ($_row2 as $tmp_k2=>$tmp_v2){
        			//因为在查找$_row的时候已经按版本号从高到底排序了，
        			//所以第一个gv_id即为最新版本
        			//这时候的$_row2值则为可更新的最新的合适下载版本
        			//另外在查找$_row2时也按包的类型从高到底排了序的，
        			//所有，如果这个游戏有gpk的话一定会先找到gpk的下载包先的
        			if( $tmp_new_game=='' && $tmp_v['gv_id'] == $tmp_v2['gv_id'] ){
        				$tmp_new_game = $tmp_v2;
        			}
        			//查到旧版本的游戏下载作息
        			if( $tmp_old_gv_id!='' && $tmp_old_gv_id == $tmp_v2['gv_id'] ){
        				$tmp_tmp_old_game[] = $tmp_v2;
        			}
        		}
        		//重置数组
        		reset($_row2);
        	}
        	//因为GPK的游戏会多了一个apk的，所有需要选择是GPK里的数据
        	if(!is_empty($tmp_tmp_old_game) && count($tmp_tmp_old_game)>1){
	        	foreach ($tmp_tmp_old_game as $tmp_tmp_v2){
	        		//如果是有GPK的，则取
	        		if($tmp_tmp_v2['mgd_package_type']==1){
	        			$tmp_old_game = $tmp_tmp_v2;
	        			break;
	        		}
	        	}
        	}else if(!is_empty($tmp_tmp_old_game)){
        		$tmp_old_game = $tmp_tmp_old_game[0];
        	}else{
        		//如果没有找到旧版本，则跳出不找了
        		continue;
        	}
        	//如果没有找到新版本，则跳出不找了
        	if( $tmp_new_game==''){
        		continue;
        	}
        	
        	$tmp_diff_info = '';
        	//对比新->旧 游戏包的签名是否相同，如果相同则查增量包
        	if($tmp_new_game['mgd_apk_agsin']==$tmp_old_game['mgd_apk_agsin']){
        		//查游戏是否有增量包
        		$sql_diff = 'SELECT * FROM `mzw_game_patch_diff` WHERE client_type!=2 AND from_gv_id='.$tmp_old_game['gv_id'].' AND from_mgd_id='.$tmp_old_game['mgd_id']
        		.' AND to_gv_id='.$tmp_new_game['gv_id'].' AND to_mgd_id='.$tmp_new_game['mgd_id'];
        		$_diff = $conn->find($sql_diff);
        		//如果有找到更新包，则只返回更新包就好了
        		if(count($_diff)>0){
        			$tmp_diff_info = $_diff[0];
        		}
        	}
        	//初始化变量
        	$filepath = Null;
        	$filepath2 = Null;
        	$filepath_all = Null;//整包内容
        	$down_apk_gpk_all = Null;//整包内容
        	$size_all = 0;//整包内容大小
        	$down_apk_gpk = Null;
        	$size = 0;
        	
        	$tmp_obb_info = Null;
        	//如果新包是GPK的包，则要检查obb包以及apk文件的
        	//处理GPK包
        	if($tmp_new_game['mgd_package_type']==1){
        		$filetype = 'gpk';
        		//查旧版本的OBB之类的文件
        		$sql_obb_old = 'SELECT * FROM `mzw_game_patch` 
        				WHERE client_type !=2 AND gv_id='.$tmp_old_game['gv_id'].' AND mgd_id='.$tmp_old_game['mgd_id'];
        		$_row_obb_old = $conn->find($sql_obb_old);
        		
        		//查新版本的OBB之类的文件
        		$sql_obb_new = 'SELECT * FROM `mzw_game_patch`
        				WHERE client_type !=2 AND gv_id='.$tmp_new_game['gv_id'].' AND mgd_id='.$tmp_new_game['mgd_id'];
        		$_row_obb_new = $conn->find($sql_obb_new);
        		
        		//先拿整个GPK解压包的内容先
        		$down_apk_gpk_all = $tmp_new_game['mgd_mzw_server_url'];
        		foreach ($_row_obb_new as $tmp_v_obb){
        			//整包内容
        			$filepath_all[] = array(
        					'fileName' => end(explode(DS, $tmp_v_obb["apk_patch_file"])),
        					'url' => CDN_LESHI_URL_DOWN.$tmp_v_obb["apk_patch_file"],
        					'totalLength' => $tmp_v_obb['apk_patch_size'],
        					'fileType' => intval($tmp_v_obb['file_type']),
        					'backup' =>CDN_LESHI_URL_DOWN.$tmp_v_obb["apk_patch_file"]
        			);
        		}
        		//重置数组
        		reset($_row_obb_new);
        		
        		//如果新/旧版都有OBB，并且有增量包，即签名相等，才需要对比
        		if(count($_row_obb_old)>0 && count($_row_obb_new)>0 && !is_empty($tmp_diff_info)){
        			foreach ($_row_obb_new as $v_obb_new){
        				$tmp_obb_is_new = true;
        				foreach ($_row_obb_old as $v_obb_old){
        					//如果MD5值相等，则不需要更新了
        					if( $v_obb_new['patch_md5']==$v_obb_old['patch_md5']
        						&& $v_obb_new['file_type']==$v_obb_old['file_type']
    							){
        						$tmp_obb_is_new = false;
        						break;
        					}
        				}
        				//重置数组
        				reset($_row_obb_old);
        				//如果新旧版本的文件MD5值相等，则不需要了
        				if($tmp_obb_is_new==true){
        					$tmp_obb_info[] = $v_obb_new;
        				}
        			}
        		}else{
        			$tmp_obb_info = $_row_obb_new;
        		}
        		
        		//如果有增量包，则用增量包
        		if(!is_empty($tmp_diff_info)){
        			//如果有增量包，则用增量包来
        			$size = $tmp_diff_info['apk_patch_size'];
					
        			$filepath[] = array(
        					'fileName' => end(explode(DS, $tmp_diff_info['apk_patch_file'])),
        					'url' => CDN_LANXUN_URL_DOWN.$tmp_diff_info['apk_patch_file'],//蓝讯CDN的先
        					'totalLength' => $tmp_diff_info['apk_patch_size'],
        					'fileType' => 4,
        					'backup' =>CDN_LANXUN_URL_DOWN.$tmp_diff_info['apk_patch_file']
        			);
        			$filepath2[] = array(
        					'fileName' => end(explode(DS, $tmp_diff_info['apk_patch_file'])),
        					'url' => CDN_LESHI_URL_DOWN.$tmp_diff_info['apk_patch_file'],//乐视CDN的先
        					'totalLength' => $tmp_diff_info['apk_patch_size'],
        					'fileType' => 4,
        					'backup' =>CDN_LESHI_URL_DOWN.$tmp_diff_info['apk_patch_file']
        			);
        			
        		}else if(!is_empty($tmp_new_game)){//如果没有增量包，则用新游戏的APK包
        			$down_apk_gpk = $tmp_new_game['mgd_mzw_server_url'];
        			$size = $tmp_new_game['mgd_package_file_size'];
        		}else{
        			continue;
        		}
        		//在$filepath数据里的文件类型 1.apk ; 2.OBB ; 3.OBB PATCH ;4.apk patch
        		
        		foreach($tmp_obb_info as $sub){
        			//如果有增量包，则不需要APK文件了
        			if($sub['file_type']==1 && !is_empty($tmp_diff_info)){
        				continue;
        			}
        			//文件名
        			$file_name = end(explode(DS, $sub["apk_patch_file"]));
        			//如果OBB包名是以path开头的，则fileType值是3
        			if( $sub['file_type']==2 && 'patch.'==substr($file_name,0,6)){
        				$sub['file_type'] = 3;
        			}
        			$size += $sub['apk_patch_size'];
        			$filepath[] = array(
        					'fileName' => $file_name,
        					'url' => CDN_LANXUN_URL_DOWN.$sub["apk_patch_file"],//蓝讯CDN的先
        					'totalLength' => $sub['apk_patch_size'],
        					'fileType' => intval($sub['file_type']),
        					'backup' =>CDN_LANXUN_URL_DOWN.$sub["apk_patch_file"]
        			);
        			$filepath2[] = array(
        					'fileName' => end(explode(DS, $sub["apk_patch_file"])),
        					'url' => CDN_LESHI_URL_DOWN.$sub["apk_patch_file"],//乐视CDN的先
        					'totalLength' => $sub['apk_patch_size'],
        					'fileType' => intval($sub['file_type']),
        					'backup' =>CDN_LESHI_URL_DOWN.$sub["apk_patch_file"]
        			);
        		}
        	}else{//否则是APK包
        		$filetype = 'apk';
        		//游戏整包的下载地址
        		$down_apk_gpk_all = $tmp_new_game['mgd_mzw_server_url'];
        		
        		//如果有增量包，则用增量包
        		if(!is_empty($tmp_diff_info)){
        			$size = $tmp_diff_info['apk_patch_size'];
        			$filepath[] = array(
        					'fileName' => end(explode(DS, $tmp_diff_info['apk_patch_file'])),
        					'url' => CDN_LANXUN_URL_DOWN.$tmp_diff_info['apk_patch_file'],//蓝讯CDN的先
        					'totalLength' => $tmp_diff_info['apk_patch_size'],
        					'fileType' => 4,
        					'backup' =>CDN_LANXUN_URL_DOWN.$tmp_diff_info['apk_patch_file']
        			);
        			$filepath2[] = array(
        					'fileName' => end(explode(DS, $tmp_diff_info['apk_patch_file'])),
        					'url' => CDN_LESHI_URL_DOWN.$tmp_diff_info['apk_patch_file'],//乐视CDN的先
        					'totalLength' => $tmp_diff_info['apk_patch_size'],
        					'fileType' => 4,
        					'backup' =>CDN_LESHI_URL_DOWN.$tmp_diff_info['apk_patch_file']
        			);
        			
        		}else if(!is_empty($tmp_new_game)){//如果没有增量包，则用新游戏的APK包
        			$down_apk_gpk = $tmp_new_game['mgd_mzw_server_url'];
        			$size = $tmp_new_game['mgd_game_size'];
        		}else{
        			//echo('kkkkkkkkkkkkkkkkk'.chr(10).chr(10));
        			continue;
        		}
        	}
        	//初始化
        	$againDownloadPaths = Null;
        	//组合整包下载 相关下载地址
        	$againDownloadPaths[] = array(
        			'id' => -1,
        			'name' => $cdn,
        			'icon' => CDN_LESHI_URL_DOWN.'/app420/cdn.png',
        			'url' => CDN_LESHI_URL_DOWN.$down_apk_gpk_all,
        			'backup' =>'',
        			'visible' =>1 ,
        			'parse' =>false,
        			'files' =>$filepath_all
        	);
        	
        	
        	//在files数据里的文件类型 1.apk ; 2.OBB ; 3.OBB PATCH ;4.apk patch
        	//========begin 组合下载地址
        	$downloadPaths = Null;
        	//判断url地址是否需要
        	if(!is_empty($down_apk_gpk)){
        		$tmp_url_lanxun = CDN_LANXUN_URL_DOWN.$down_apk_gpk;
        		$tmp_url_leshi	= CDN_LESHI_URL_DOWN.$down_apk_gpk;
        	}else{
        		$tmp_url_lanxun = Null;
        		$tmp_url_leshi	= Null;
        	}
        	/*
	        //组合蓝讯CDN 相关下载地址
			$downloadPaths[] = array(
					'id' => -4,
					'name' => $cdn,
					'icon' => CDN_LANXUN_URL_DOWN.'/app420/cdn.png',
					'url' => $tmp_url_lanxun,
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
					'url' => $tmp_url_leshi,
					'backup' =>'',
					'visible' =>1 ,
					'parse' =>false,
					'files' =>$filepath2
			);
			//========end 组合下载地址

			$tmp_game_ico = '';//ICO地址
			$tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
				LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$_row[$tmp_new_game['gv_id']]["gv_ico_key"]."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
			//echo($tmp_sql.chr(10).chr(10));
			$tmp_game_ico_arr = $conn->find($tmp_sql);
			if($tmp_game_ico_arr){
				$tmp_game_ico = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$tmp_game_ico_arr[0]["img_path"]);
			}
			
            $json = array(
                    'title'=>$_row[$tmp_new_game['gv_id']]['gv_title'],//游戏名称
                    'iconpath'=>$tmp_game_ico,//ICO地址
                    'appid'=>intval($tmp_new_game['gv_id']),//新游戏ID
                    'packagename'=>$_row[$tmp_new_game['gv_id']]['gv_package_name'],//游戏包名
                    'version'=>$_row[$tmp_new_game['gv_id']]['gv_version_name'],//游戏版本名
                    'versioncode'=>$_row[$tmp_new_game['gv_id']]['gv_version_no'],//游戏版本号
            		'fileType'=>$filetype,//文件类型
                    'unzipsize' => intval($tmp_new_game['mgd_game_unzip_size']), //GPK文件解压后
                    'size'=>intval($tmp_new_game['mgd_game_size']),//游戏总大小
                    'pathsize'=>$size,//当次升级包的总大小
                    'downloadPaths'=>$downloadPaths,//整合增量包下载地址
            		'againDownloadPaths'=>$againDownloadPaths,//完整包的下载地址
                    'signature' => $tmp_new_game['mgd_apk_agsin']//游戏签名
                   );
            $returnArr['rows'][]=$json;
            $update_num++;
            //记录更新日志用的
            $save_game_info[$i]['NVName']=$json['version'];//新版本名称
            $save_game_info[$i]['NVCode']=$json['versioncode'];//新版本号
        }
    }
    //如果有日志数据，则记录
    if($save_game_info!=Null){
    	$tmp_str = '';
    	foreach ($save_game_info as $save_v){
    		if(is_array($save_v)){
    			$save_v['cpu'] = $mydata['cpu'];
    			$save_v['gpu'] = $mydata['gpu'];
    			$save_v['brand'] = $mydata['brand'];
    			$save_v['model'] = $mydata['model'];
    			$tmp_str .=json_encode($save_v).chr(13).chr(10);
    		}
    	}
    	//记录日志
    	write_file_random($tmp_str,"applist_update",true);
    	unset($save_game_info,$tmp_str);
    }
}
// $returnArr['total'] = $update_num;

//根据appid来排重
$returnArr['rows'] = assoc_unique($returnArr['rows'],'appid');
$returnArr['total'] = count($returnArr['rows']);

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
	echo($sql);
	print_r($returnArr);
	exit;
}

$str_encode = responseJson($returnArr,true);    
exit($str_encode);