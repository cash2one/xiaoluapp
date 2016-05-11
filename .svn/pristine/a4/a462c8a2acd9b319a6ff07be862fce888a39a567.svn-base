<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 返回客户端首页需要显示游戏列表（进行了GPU适配的）
 * @file:home_hotgame.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-08-10  17:36
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['gpu']=get_param('gpu');//CPU型号，字符串
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式

//key校验
$key_auth = kyx_authorize_key($mydata['key'],$request);
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//====== GPU适配 start =======//
//查找适配的GPU
$tmp_sql_gpu = "SELECT gb_id FROM mzw_mobile_gpu_brand WHERE INSTR('".$mydata['gpu']."',gb_params)>0";
$tmp_gpu_id_arr = $conn->find($tmp_sql_gpu,'gb_id');
$tmp_find_gpu_id = " ( FIND_IN_SET(0,mgd_gpu_id)>0 ";
if(!empty($tmp_gpu_id_arr)){
    foreach ($tmp_gpu_id_arr as $tmp_gpu_id_val){
        $tmp_find_gpu_id .= " OR FIND_IN_SET(".$tmp_gpu_id_val["gb_id"].",mgd_gpu_id)>0 ";
    }
}
$tmp_find_gpu_id .= " ) ";

//查文件大小及游戏是APK还是GPK
$tmp_sql_gpu_in = 'SELECT DISTINCT gv_id FROM mzw_game_downlist
                   WHERE mgd_client_type !=1 AND mgd_package_type!=2 AND '.$tmp_find_gpu_id;
//====== GPU适配 end =======//

$temp_where = " AND (FIND_IN_SET(2,gv.gv_client_type)>0 OR FIND_IN_SET(3,gv.gv_client_type)>0) AND gv.gv_m_status=1 ";

//初始化返回数组
$returnArr=array();

//获取编辑精选游戏列表
$sql = "SELECT gv.gv_id as appid,gv.gv_type_id as tid,gv.gv_title as title,gv.gv_version_name as version,gv.gv_update_time as updatetime,gv.gv_version_no as versioncode,
		gv.gv_ico_key as icon,gv.gv_description as description,gv.gv_package_name as packagename FROM mzw_game_version gv LEFT JOIN mzw_game_m_a_relation B ON gv.g_id = B.g_id AND gv.gv_id = B.gv_id
		WHERE B.ga_id = 28 $temp_where ORDER BY g_order DESC LIMIT 6";
$data = $conn->find($sql);
$count = count($data);
if(!empty($data) && is_type($data,'Array') && $count == 6){
    $temp_arr = array();
    foreach($data as $val){
        $temp_arr[] = array(
            'appid' => $val['appid'], //游戏id
            'title' => $val['title'], //游戏标题
            'category' => $val['tid'], //游戏分类名称
            'version' => $val['version'], //游戏版本名称
            'versioncode' => intval($val['versioncode']), //版本号
            'description' => filter_search(delete_html($val['description'])), //游戏描述
            'packagename' => $val['packagename'], //游戏包名
            'updatetime' => date('Y-m-d',strtotime($val['updatetime'])), //游戏更新时间
            'iconpath' => $val['icon'] //游戏图标
        );
    }
    $returnArr[] = array(
        'type_title' => '编辑精选', //显示标题
        'category_title' => '编辑精选', //分类显示标题
        'type' => 1, //显示样式（1：6个两行三列 2：6个六行一列 3：3个一行三列）
        'tid' => 28, //查看更多用到id
        'sid' => 0, //二级分类id
        'iscategory' => 0, //是否分类属性（1：是 0：不是）
        'row' => $temp_arr, //游戏数据
        'sdatas' => array() //二级分类
    );
}

//查找符合查询条件的所有mgd_id
$mgd_tmp_sql = "SELECT MAX(gd.`mgd_id`) AS `mgd_id` FROM `mzw_game_version` AS gv
                LEFT JOIN `mzw_game_downlist` AS gd ON gv.`gv_id` = gd.`gv_id`
			    WHERE gd.`mgd_client_type` != 1 AND gd.`mgd_package_type` != 2 GROUP BY gv.`gv_id`";
$mgd_tmp_downlist = $conn->find($mgd_tmp_sql);
$tmp_downlist_mgd = array();
if(!empty($mgd_tmp_downlist)){
    foreach($mgd_tmp_downlist as $tdval){
        $tmp_downlist_mgd[] = $tdval['mgd_id'];
    }
}

//如果有满足查找符合查询条件的所有mgd_id
if(!empty($tmp_downlist_mgd)){
    $tmp_downlist_mgd_str = implode(',',$tmp_downlist_mgd);

    //查询满足查询条件的mgd_id中满足gpu适配的mgd_id
    $gpu_id_sql = "SELECT DISTINCT `mgd_id` FROM mzw_game_downlist WHERE mgd_client_type!=1 AND `mgd_id` IN (".$tmp_downlist_mgd_str.") AND ".$tmp_find_gpu_id;
    $gpu_id_data = $conn->find($gpu_id_sql);
    if(!empty($gpu_id_data)){
        foreach($gpu_id_data as $gdval){
            $gpu_id_mgd[] = $gdval['mgd_id'];
        }
        $gd_id_str = implode(',',$gpu_id_mgd);
        $temp_where .= " AND gd.mgd_id IN (".$gd_id_str.") ";
    }
}

//最新更新
$sql = "SELECT gv.gv_id as appid,gv.gv_type_id as tid,gv.gv_title as title,gv.gv_version_name as version,gv.gv_update_time as updatetime,
		gv.gv_version_no as versioncode,gv.gv_ico_key as icon,gv.gv_description as description,gv.gv_package_name as packagename
		FROM `mzw_game_version` gv LEFT JOIN `mzw_game_downlist` gd ON gv.gv_id = gd.gv_id
		WHERE (gv.gv_nes_property = '' OR gv.gv_nes_property = 0 OR gv.gv_nes_property is null)
		AND gv.gv_id IN($tmp_sql_gpu_in) $temp_where
		ORDER BY updatetime DESC LIMIT 6";
$data = $conn->find($sql);
$count = count($data);
if(!empty($data) && is_type($data,'Array') && $count == 6){
    $temp_arr = array();
    foreach($data as $val){
        $temp_arr[] = array(
            'appid' => $val['appid'], //游戏id
            'title' => $val['title'], //游戏标题
            'category' => $val['tid'], //游戏分类名称
            'version' => $val['version'], //游戏版本名称
            'versioncode' => intval($val['versioncode']), //版本号
            'description' => filter_search(delete_html($val['description'])), //游戏描述
            'packagename' => $val['packagename'], //游戏包名
            'updatetime' => date('Y-m-d',strtotime($val['updatetime'])), //游戏更新时间
            'iconpath' => $val['icon'] //游戏图标
        );
    }
    $returnArr[] = array(
        'type_title' => '最新更新', //显示标题
        'category_title' => '最新更新', //分类显示标题
        'type' => 2, //显示样式（1：6个两行三列 2：6个六行一列 3：3个一行三列）
        'tid' => -1, //查看更多用到id
        'sid' => 0,//二级分类id
        'iscategory' => 1, //是否分类属性（1：是 0：不是）
        'row' => $temp_arr, //游戏数据
        'sdatas' => array() //二级分类
    );
}

//模拟器二级分类数组
$sdatas = array();
$s_tid_sql = "SELECT `i_id`,`i_name_cn` FROM `mzw_game_imitator` WHERE `i_status` = 1 ORDER BY `i_order_num` DESC";
$s_tid_data = $conn->find($s_tid_sql);
if(!empty($s_tid_data) && is_type($s_tid_data,'Array')){
    $sdatas[] = array(
        'sid' => 0,
        'title' => '全部'
    );
    foreach($s_tid_data as $s_tid_val){
        $sdatas[] = array(
            'sid' => intval($s_tid_val['i_id']),
            'title'=> $s_tid_val['i_name_cn']
        );
    }
}

//NES模拟器
$sql = "SELECT gv.gv_id as appid,gv.gv_type_id as tid,gv.gv_title as title,gv.gv_version_name as version,gv.gv_update_time as updatetime,
		gv.gv_version_no as versioncode,gv.gv_ico_key as icon,gv.gv_description as description,gv.gv_package_name as packagename
		FROM `mzw_game_version` gv LEFT JOIN `mzw_game_downlist` gd ON gv.gv_id = gd.gv_id
		WHERE FIND_IN_SET(1,gv.gv_nes_property)>0 AND gv.gv_id IN($tmp_sql_gpu_in) $temp_where
		ORDER BY updatetime DESC LIMIT 3";
$data = $conn->find($sql);
$count = count($data);
if(!empty($data) && is_type($data,'Array') && $count == 3){
    $temp_arr = array();
    foreach($data as $val){
        $temp_arr[] = array(
            'appid' => $val['appid'], //游戏id
            'title' => $val['title'], //游戏标题
            'category' => $val['tid'], //游戏分类名称
            'version' => $val['version'], //游戏版本名称
            'versioncode' => intval($val['versioncode']), //版本号
            'description' => filter_search(delete_html($val['description'])), //游戏描述
            'packagename' => $val['packagename'], //游戏包名
            'updatetime' => date('Y-m-d',strtotime($val['updatetime'])), //游戏更新时间
            'iconpath' => $val['icon'] //游戏图标
        );
    }
    $returnArr[] = array(
        'type_title' => 'NES模拟器', //显示标题
        'category_title' => '模拟器', //分类显示标题
        'type' => 3, //显示样式（1：6个两行三列 2：6个六行一列 3：3个一行三列）
        'tid' => -3, //查看更多用到id
        'sid' => 1, //二级分类id
        'iscategory' => 1, //是否分类属性（1：是 0：不是）
        'row' => $temp_arr, //游戏数据
        'sdatas' => $sdatas //二级分类
    );
}

//PSP模拟器
$sql = "SELECT gv.gv_id as appid,gv.gv_type_id as tid,gv.gv_title as title,gv.gv_version_name as version,gv.gv_update_time as updatetime,
		gv.gv_version_no as versioncode,gv.gv_ico_key as icon,gv.gv_description as description,gv.gv_package_name as packagename
		FROM `mzw_game_version` gv LEFT JOIN `mzw_game_downlist` gd ON gv.gv_id = gd.gv_id
		WHERE FIND_IN_SET(2,gv.gv_nes_property)>0 AND gv.gv_id IN($tmp_sql_gpu_in) $temp_where
		ORDER BY updatetime DESC LIMIT 3";
$data = $conn->find($sql);
$count = count($data);
if(!empty($data) && is_type($data,'Array') && $count == 3){
    $temp_arr = array();
    foreach($data as $val){
        $temp_arr[] = array(
            'appid' => $val['appid'], //游戏id
            'title' => $val['title'], //游戏标题
            'category' => $val['tid'], //游戏分类名称
            'version' => $val['version'], //游戏版本名称
            'versioncode' => intval($val['versioncode']), //版本号
            'description' => filter_search(delete_html($val['description'])), //游戏描述
            'packagename' => $val['packagename'], //游戏包名
            'updatetime' => date('Y-m-d',strtotime($val['updatetime'])), //游戏更新时间
            'iconpath' => $val['icon'] //游戏图标
        );
    }
    $returnArr[] = array(
        'type_title' => 'PSP模拟器', //显示标题
        'category_title' => '模拟器', //分类显示标题
        'type' => 3, //显示样式（1：6个两行三列 2：6个六行一列 3：3个一行三列）
        'tid' => -3, //查看更多用到id
        'sid' => 2, //二级分类id
        'iscategory' => 1, //是否分类属性（1：是 0：不是）
        'row' => $temp_arr, //游戏数据
        'sdatas' => $sdatas //二级分类
    );
}

//获取游戏大小，游戏图标（175 * 175），游戏下载地址
if(!empty($returnArr) && is_type($returnArr,'Array')){
    foreach($returnArr as $key => $val){
        if(!empty($val['row'])){
            foreach($val['row'] as $kkey => $vval){
                //查所属分类的名称
                $tmp_sql = 'SELECT `t_name_cn` as `name` FROM `mzw_game_type` WHERE `t_id`='.$vval["category"];
                $tmp_type = $conn->get_one($tmp_sql);
                $returnArr[$key]['row'][$kkey]['category'] = isset($tmp_type['name']) ? $tmp_type['name'] : '';

                //获取游戏ICO地址（175 * 175）
                $tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type FROM mzw_game_screenshot A
			                LEFT JOIN mzw_img_path B ON A.img_key=B.img_key
                            WHERE A.gv_id = '.$vval['appid'].' AND A.type = 7 AND B.size_id = 22 ORDER BY B.id DESC';
                $tmp_game_ico_arr = $conn->get_one($tmp_sql);
                $iconpath = isset($tmp_game_ico_arr['path']) ? (LOCAL_URL_DOWN_IMG.$tmp_game_ico_arr['path']) : '';

                //如果没找到175*175的ICO图标，则去100*100的ICO图标
                if(empty($iconpath)){
                    $tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
                                LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$vval["iconpath"]
                                ."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
                    $tmp_game_ico_arr = $conn->get_one($tmp_sql);
                    if($tmp_game_ico_arr){
                        $iconpath = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$tmp_game_ico_arr["img_path"]);
                    }
                }

                $returnArr[$key]['row'][$kkey]['iconpath'] = $iconpath;

                //判断游戏是否为NES属性
                $game_type_sql = "SELECT `gv_id` FROM `mzw_game_version` WHERE `gv_status` = 1 AND `gv_id` = ".$vval['appid']." AND FIND_IN_SET(1,gv_nes_property)>0";
                $game_type_data = $conn->get_one($game_type_sql);

                //拼接查询下载地址条件
                $where_str = ' WHERE `mgd_client_type` != 1 AND `gv_id` = '.$vval["appid"].' AND `mgd_package_type` != 2 AND  '.$tmp_find_gpu_id;
                if(isset($game_type_data['gv_id']) && !empty($game_type_data['gv_id'])){ //如果是NES游戏
                    $order_str = " ORDER BY `mgd_package_type` ASC,`mgd_id` DESC ";
                }else{
                    $order_str = " ORDER BY `mgd_package_type` DESC,`mgd_id` DESC ";
                }

                //查文件大小及游戏是APK还是GPK
                $tmp_sql = 'SELECT `mgd_id`,`mgd_package_file_size` as `size`,`mgd_package_type` as `type`,`mgd_mzw_server_url`,`mgd_baidu_url`,
                            `mgd_apk_agsin`,`mgd_game_unzip_size` as `unzip_size` FROM `mzw_game_downlist` '
                            .$where_str.$order_str.' LIMIT 1';//返回1个文件（APK或GPK[如果GPK有的话])
                $tmp_downlist = $conn->get_one($tmp_sql);//以类型作为key返回数据

                //如果查找成功，则查这个游戏是否有OBB，如果有则传OBB及APK，如果没有则传GPK的
                $tmp_sql = 'SELECT `id`,`mgd_id`,`apk_patch_size`,`patch_md5`,`sign`,`apk_patch_file`,`file_type`,`baidu_url` FROM `mzw_game_patch`
				                WHERE `client_type` != 1 AND `gv_id` = '.intval($vval["appid"]).' AND `mgd_id` = '.intval($tmp_downlist["mgd_id"]);
                $tmp_obb = $conn->find($tmp_sql,"id");//以自增ID为KEY返回数据

                //查文件大小以及游戏下载地址信息
                if(!empty($tmp_downlist)){
                    if(isset($tmp_downlist["type"]) && $tmp_downlist['type']==1){//如果是ＧＰＫ
                        $filetype = 'gpk';//文件类型
                        $adaptation = 1;//是GPK的适配文件
                        $down_apk_gpk = $tmp_downlist['mgd_mzw_server_url']; //下载地址
                        $down_apk_gpk_baidu = $tmp_downlist['mgd_baidu_url']; //百度网盘下载地址
                        $size = $tmp_downlist['size']; //大小

                        if($tmp_obb && count($tmp_obb)>0){//如果有OBB文件
                            $size = 0; //初始化大小
                            $filetype = 'obb';//文件类型
                            foreach($tmp_obb as $sub){
                                $size += $sub['apk_patch_size'];
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
                    }else if(isset($tmp_downlist['type']) && $tmp_downlist['type']==3){//如果是模拟器游戏
                        $filetype = 'nes';//
                        $adaptation = -2;//是NES的适文件类型配文件
                        $down_apk_gpk = $tmp_downlist['mgd_mzw_server_url']; //本地下载地址
                        $down_apk_gpk_baidu = $tmp_downlist['mgd_baidu_url']; //百度网盘下载地址
                        $size = $tmp_downlist['size']; //大小
                        $filepath2 = array();
                    }else if(isset($tmp_downlist['type']) && ($tmp_downlist[0]['type']==4 || $tmp_downlist[0]['type']==5)){//如果是模拟器游戏
                        $filetype = 'PSP';//文件类型
                        $adaptation = -3;//是psp的适配文件
                        $down_apk_gpk = $tmp_downlist['mgd_mzw_server_url']; //本地下载地址
                        $down_apk_gpk_baidu = $tmp_downlist['mgd_baidu_url']; //百度网盘下载地址
                        $size = $tmp_downlist['size']; //大小
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
                        'name' => '普通下载',
                        'icon' => CDN_LESHI_URL_DOWN.'/app420/cdn.png',
                        'url' => CDN_LESHI_URL_DOWN.$down_apk_gpk,//乐视CDN
                        'backup' =>'',
                        'visible' =>1 ,
                        'parse' =>false,
                        'files' =>$filepath2
                    );
                }else{
                    $filetype = 'apk';//文件类型
                    $size = 0;//文件大小
                    $downloadPaths = array();
                }

                //游戏大小、下载相关信息赋值
                $returnArr[$key]['row'][$kkey]['filetype'] = $filetype; //文件类型
                $returnArr[$key]['row'][$kkey]['size'] = $size; //文件大小
                $returnArr[$key]['row'][$kkey]['unzipsize'] = $tmp_downlist['unzip_size']; //解压后的大小
                $returnArr[$key]['row'][$kkey]['adaptation'] = intval($adaptation); //1GPK包，-1合适的APK包，-2没有合适的APK及GPK包
                $returnArr[$key]['row'][$kkey]['signature'] = isset($tmp_downlist["mgd_apk_agsin"]) ? $tmp_downlist["mgd_apk_agsin"] : ''; //游戏签名
                $returnArr[$key]['row'][$kkey]['downloadPaths'] = $downloadPaths; //下载地址

                unset($downloadPaths,$filepath2);
            }
        }
    }
}


//初始化返回数组
$returnJson=array(
    'total'=>count($returnArr),
    'rows'=>array()
);

$returnJson['rows'] = $returnArr;
$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
    exit(responseJson($returnJson,false));
}

exit(responseJson($returnJson,true));


