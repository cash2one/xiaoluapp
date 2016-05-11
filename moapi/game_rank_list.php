<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 返回客户端排行榜游戏列表（进行了GPU适配的）
 * @file:game_rank_list.php
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
$mydata['order'] = intval(get_param('order')); //排序（0：上周下载（默认） 1:月下载 2：总下载 ）
$mydata['pagenum'] = intval(get_param('pagenum')); //当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : $mydata['pagenum'];
$mydata['pagesize'] = intval(get_param('pagesize'));//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 10 : $mydata['pagesize'];
$mydata['insdcardunmount'] = intval(get_param('insdcardunmount')); //设置的内置存贮卡是否挂载（1是未挂载，0是已经挂载)
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

//查询条件拼接
$temp_where = ' WHERE (FIND_IN_SET(2,gv_client_type)>0 OR FIND_IN_SET(3,gv_client_type)>0) AND gv_m_status=1 ';

//不显示大游戏（即不显示GPK的游戏）
if($mydata['insdcardunmount']==1){
    $temp_where .= ' AND FIND_IN_SET(23,g_tags_property)<1 ';
}

//LIMIT条件
$temp_limit = " LIMIT ".($mydata['pagenum'] - 1) * $mydata['pagesize'].",".$mydata['pagesize']." ";

//排序条件
$order_str = '';
switch($mydata['order']){
    case 1: //月下载
        $order_str = " ORDER BY gv_down_nums_month DESC ";
        break;
    case 2: //总下载
        $order_str = " ORDER BY gv_down_nums DESC ";
        break;
    default :
        $order_str = " ORDER BY gv_down_nums_week DESC ";
        break;
}

//数据查询
$sql = "SELECT gv_id as appid,gv_type_id as tid,gv_title as title,gv_version_name as version,gv_update_time as updatetime,gv_version_no as versioncode,
		gv_ico_key as icon,gv_description as description,gv_package_name as packagename,gv_down_nums as downcount FROM `mzw_game_version`
		".$temp_where.$order_str.$temp_limit;
$data = $conn->find($sql);

//数据总条数
$count_sql = "SELECT COUNT(1) AS num FROM `mzw_game_version` ".$temp_where;
$count_data = $conn->count($count_sql);

//初始化返回数组
$returnArr = array(
    'total' => $count_data, //数据总数
    'pagenum' => $mydata['pagenum'], //当前页
    'pagesize' => $mydata['pagesize'], //每页显示数据条数
    'rows' => array() //数据
);

//数据赋值
if(!empty($data) && is_type($data,'Array')){
    foreach($data as $val){

        //查所属分类的名称
        $tmp_sql = 'SELECT `t_name_cn` as `name` FROM `mzw_game_type` WHERE `t_id`='.$val["tid"];
        $tmp_type = $conn->get_one($tmp_sql);
        $category = isset($tmp_type['name']) ? $tmp_type['name'] : '';

        //获取游戏ICO地址（175 *　175）
        $tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type FROM mzw_game_screenshot A
                    LEFT JOIN mzw_img_path B ON A.img_key=B.img_key
                    WHERE A.gv_id = '.$val['appid'].' AND A.type = 7 AND B.size_id = 22 ORDER BY B.id DESC';
        $tmp_game_ico_arr = $conn->get_one($tmp_sql);
        $iconpath = isset($tmp_game_ico_arr['path']) ? (LOCAL_URL_DOWN_IMG.$tmp_game_ico_arr['path']) : '';

        //如果没找到175*175的ICO图标，则去100*100的ICO图标
        if(empty($iconpath)){
            $tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
                        LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '".$val["icon"]
                        ."' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
            $tmp_game_ico_arr = $conn->get_one($tmp_sql);
            if($tmp_game_ico_arr){
                $iconpath = LOCAL_URL_DOWN_IMG.str_replace(LOCAL_IMG_PATH,"",$tmp_game_ico_arr["img_path"]);
            }
        }

        //判断游戏是否为NES属性
        $game_type_sql = "SELECT `gv_id` FROM `mzw_game_version` WHERE `gv_status` = 1 AND `gv_id` = ".$val['appid']." AND FIND_IN_SET(1,gv_nes_property)>0";
        $game_type_data = $conn->get_one($game_type_sql);

        //拼接查询下载地址条件
        $where_str = ' WHERE `mgd_client_type` != 1 AND `gv_id` = '.$val["appid"].' AND `mgd_package_type` != 2 AND  '.$tmp_find_gpu_id;
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
				                WHERE `client_type` != 1 AND `gv_id` = '.intval($val["appid"]).' AND `mgd_id` = '.intval($tmp_downlist["mgd_id"]);
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

        $returnArr['rows'][] = array(
            'appid' => $val['appid'], //游戏id
            'title' => $val['title'], //游戏标题
            'category' => $category, //游戏分类名称
            'version' => $val['version'], //游戏版本名称
            'versioncode' => intval($val['versioncode']), //版本号
            'description' => filter_search(delete_html($val['description'])), //游戏描述
            'packagename' => $val['packagename'], //游戏包名
            'updatetime' => date('Y-m-d',strtotime($val['updatetime'])), //游戏更新时间
            'iconpath' => $iconpath, //游戏图标
            'filetype' => $filetype, //文件类型
            'size' => $size, //文件大小
            'downloadscount' => $val['downcount'], //下载总数
            'unzipsize' => $tmp_downlist['unzip_size'], //解压后的大小
            'adaptation' => intval($adaptation), //1GPK包，-1合适的APK包，-2没有合适的APK及GPK包
            'signature' => isset($tmp_downlist["mgd_apk_agsin"]) ? $tmp_downlist["mgd_apk_agsin"] : '', //游戏签名
            'downloadPaths' => $downloadPaths //下载地址数组
        );

        unset($downloadPaths,$filepath2);
    }
}

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
    exit(responseJson($returnArr,false));
}

exit(responseJson($returnArr,true));


