<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 游戏列表条件选择接口,并加密JSON内容进行输出返回
 * @file: game_list_select_key.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-11-25  10:28
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式

//key校验
$key_auth = kyx_authorize_key($mydata['key'],$request);
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

$mydata['pagenum'] = intval(get_param('pagenum'));//当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : $mydata['pagenum'];
$mydata['pagesize'] = intval(get_param('pagesize'));//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 60 : $mydata['pagesize'];
$mydata['searchkey'] = get_param('searchkey');//字符串 搜索关键字
$mydata['model'] = get_param('model');//字符串 型号
$mydata['brand'] = get_param('brand');//字符串 品牌
$mydata['cpu'] = get_param('cpu');//字符串 cpu信息
$mydata['gpu'] = get_param('gpu');//字符串 gpu信息
$mydata['insdcardunmount'] = intval(get_param('insdcardunmount')); //设置的内置存贮卡是否挂载（1是未挂载，0是已经挂载)

//查询条件
$where = ' ';

//====== GPU适配 start =======//
$tmp_find_gpu_id = ' ( FIND_IN_SET(0,mgd_gpu_id)>0 ';
//查找适配的GPU
$tmp_sql_gpu = "SELECT gb_id FROM mzw_mobile_gpu_brand WHERE INSTR('".$mydata['gpu']."',gb_params)>0";
$tmp_gpu_id_arr = $conn->find($tmp_sql_gpu,'gb_id');
if(!empty($tmp_gpu_id_arr)){
    foreach ($tmp_gpu_id_arr as $tmp_gpu_id_val){
        $tmp_find_gpu_id .= " OR FIND_IN_SET(".$tmp_gpu_id_val["gb_id"].",mgd_gpu_id)>0 ";
    }
}
$tmp_find_gpu_id .= " ) ";

if(!empty($mydata['gpu'])){
    $where .= " AND gv.gv_id IN (SELECT DISTINCT gv_id FROM mzw_game_downlist
			    WHERE mgd_client_type != 1 AND ".$tmp_find_gpu_id.") ";
}
//====== GPU适配 end =======//

//搜索关键字
if(!is_empty($mydata['searchkey'])){
	$where .= " AND gv.gv_title like '%".$mydata['searchkey']."%' ";
}

//查找符合查询条件的所有mgd_id
$tmp_sql = "SELECT MAX(gd.`mgd_id`) AS `mgd_id` FROM `mzw_game_version` AS gv
            LEFT JOIN `mzw_game_downlist` AS gd ON gv.`gv_id` = gd.`gv_id`
			WHERE gd.`mgd_client_type` != 1 AND gd.`mgd_package_type` != 2 AND gv.`gv_title` LIKE '%".$mydata['searchkey']."%'
			GROUP BY gv.`gv_id`";
$tmp_downlist = $conn->find($tmp_sql);
$tmp_downlist_mgd = array();
if(!empty($tmp_downlist)){
    foreach($tmp_downlist as $tdval){
        $tmp_downlist_mgd[] = $tdval['mgd_id'];
    }
}

//如果有满足查找符合查询条件的所有mgd_id
if(!empty($tmp_downlist_mgd)){
    $tmp_downlist_mgd_str = implode(',',$tmp_downlist_mgd);

    //查询满足查询条件的mgd_id中满足gpu适配的mgd_id
    $gpu_id_sql = "SELECT DISTINCT `mgd_id` FROM mzw_game_downlist
                   WHERE `mgd_client_type` != 1 AND `mgd_id` IN (".$tmp_downlist_mgd_str.") AND ".$tmp_find_gpu_id;
    $gpu_id_data = $conn->find($gpu_id_sql);
    if(!empty($gpu_id_data)){
        foreach($gpu_id_data as $gdval){
            $gpu_id_mgd[] = $gdval['mgd_id'];
        }
        $gd_id_str = implode(',',$gpu_id_mgd);
        $where .= " AND gd.mgd_id IN (".$gd_id_str.") ";
    }
}

//不显示大游戏（即不显示GPK的游戏）
//if($mydata['insdcardunmount']==1){
//	$where .= ' AND FIND_IN_SET(23,gv.g_tags_property)<1 ';
//}

//排序
$orderby = ' ORDER BY gv.gv_down_nums DESC ';

//查总数据行数
$sql = "SELECT count(1) as num FROM `mzw_game_version` gv
        LEFT JOIN `mzw_game_downlist` gd ON gv.gv_id = gd.gv_id
        WHERE (FIND_IN_SET(2,gv.gv_client_type)>0 OR FIND_IN_SET(3,gv.gv_client_type)>0) AND gv.gv_m_status=1 $where ";
$data_count = $conn->count($sql);

$PageMax=ceil($data_count/$mydata['pagesize']);//最大页数
$mydata['pagenum'] > $PageMax && $mydata['pagenum']=$PageMax;//当前页
$mydata['pagenum']==0 && $mydata['pagenum']=1;
$ParamPage=($mydata['pagenum']-1)*($mydata['pagesize']);//查询从第几行开始查

//初始化要返回的数据
$returnArr = array(
    'total' => $data_count,
    'pagecount' => $mydata['pagesize'],
    'pagenum' => $mydata['pagenum'],
    'rows' => array()
);

//查数据
$sql_data = "SELECT gv.gv_id as appid, gv.gv_type_id as tid,gv.gv_title as title,gv.gv_version_no as versioncode,gv.gv_version_name as version,
             gv.gv_update_time as updatetime, gv.gv_package_name as packagename,gv.gv_ico_key as icon,gv_description as description,
             gv.gv_down_nums as downcount FROM `mzw_game_version` gv LEFT JOIN `mzw_game_downlist` gd ON gv.gv_id = gd.gv_id
             WHERE (FIND_IN_SET(2,gv.gv_client_type)>0 OR FIND_IN_SET(3,gv.gv_client_type)>0) AND gv.gv_m_status=1 $where $orderby
			 LIMIT $ParamPage,".$mydata['pagesize'];
$game_data = $conn->find($sql_data);

//数据不为空
foreach ($game_data as $game_data_val){

    //获取游戏ICO地址（175 * 175）
    $tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type FROM mzw_game_screenshot A
			                LEFT JOIN mzw_img_path B ON A.img_key=B.img_key
                            WHERE A.gv_id = '.$game_data_val['appid'].' AND A.type = 7 AND B.size_id = 22 ORDER BY B.id DESC';
    $tmp_game_ico_arr = $conn->get_one($tmp_sql);
    $iconpath = isset($tmp_game_ico_arr['path']) ? (LOCAL_URL_DOWN_IMG.$tmp_game_ico_arr['path']) : '';

    //如果没找到175*175的ICO图标，则去100*100的ICO图标
    if(empty($iconpath)){
        $tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
                    LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$game_data_val["icon"]
                    ."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
        $tmp_game_ico_arr = $conn->get_one($tmp_sql);
        if($tmp_game_ico_arr){
            $iconpath = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$tmp_game_ico_arr["img_path"]);
        }
    }

    //判断游戏是否为NES属性
    $game_type_sql = "SELECT `gv_id` FROM `mzw_game_version` WHERE `gv_status` = 1 AND `gv_id` = ".$game_data_val['appid']." AND FIND_IN_SET(1,gv_nes_property)>0";
    $game_type_data = $conn->get_one($game_type_sql);

    //拼接查询下载地址条件
    $where_str = ' WHERE `mgd_client_type` != 1 AND `gv_id` = '.$game_data_val['appid'].' AND `mgd_package_type` != 2 AND '.$tmp_find_gpu_id;
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
                WHERE `client_type` != 1 AND `gv_id` = '.intval($game_data_val['appid']).' AND `mgd_id` = '.intval($tmp_downlist["mgd_id"]);
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
        $adaptation = 0;
        $downloadPaths = array();
    }

    //数据赋值
    $returnArr['rows'][]=array(
        'appid' => $game_data_val['appid'],//游戏版本ID
        'title' => $game_data_val['title'],//游戏名字
        'version' => $game_data_val['version'], //游戏版本名称
        'versioncode' => intval($game_data_val['versioncode']), //版本号
        'description' => filter_search(delete_html($game_data_val['description'])), //游戏描述
        'packagename' => $game_data_val['packagename'], //游戏包名
        'updatetime' => date('Y-m-d',strtotime($game_data_val['updatetime'])), //游戏更新时间
        'iconpath' => $iconpath, //游戏图标
        'filetype' => $filetype, //文件类型
        'size' => intval($size),//文件大小
        'downloadscount' => $game_data_val['downcount'], //下载总数
        'unzipsize' => $tmp_downlist['unzip_size'],//解压后的大小
        'adaptation' => intval($adaptation), //1GPK包，-1合适的APK包，-2没有合适的APK及GPK包
        'signature' => isset($tmp_downlist["mgd_apk_agsin"]) ? $tmp_downlist["mgd_apk_agsin"] : '', //游戏签名
        'downloadPaths' => $downloadPaths //下载地址
    );
    unset($downloadPaths,$filepath2);
}

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
    exit(responseJson($returnArr,false));
}

exit(responseJson($returnArr,true));


