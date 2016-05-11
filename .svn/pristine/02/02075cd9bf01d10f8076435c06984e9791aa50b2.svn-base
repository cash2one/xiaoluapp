<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取专辑列表,并加密JSON内容进行输出返回
 * @file: album_gamelist.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2015-04-14  16:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['key'] = get_param('key');//验证KEY
$mydata['pagenum'] = intval(get_param('pagenum'));//当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : intval($mydata['pagenum']);
$mydata['pagesize'] = get_param('pagesize');//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 64 : intval($mydata['pagesize']);
$mydata['channel'] = get_param('channel');//渠道名称，字符串类型

//验证key是否正确
verify_key_kyx($mydata['key']);

//LIMIT条件
$offset = ($mydata['pagenum']-1) * $mydata['pagesize'];
$limit = " LIMIT ".$offset." , ".$mydata['pagesize']." ";

//专区ID先，后面再按其它条件来查数据
$tmp_ga_id = intval(get_param('albumid'));
if($tmp_ga_id < 1){
    $tmp_ga_id = 28;//定死某个（客户端手机版编辑精选）专区ID先，后面再按其它条件来查数据
}

//查询条件
$tmp_where = ' ';

//排序条件
$tmp_order_by = ' A.gv_must_soft_order DESC,A.gv_id DESC ';

//如果有传渠道过来，排除渠道敌对的软件列表 (1：快游戏 2：奇珀 3：当贝 4：爱家 5：沙发 6：辣椒 7：飞智 8：厅游 9：KO)
if( !empty($mydata['channel'])){
    switch($mydata['channel']){
        case 'qipo': //奇珀
            $tmp_where .= ' AND A.gv_channel NOT IN(3,4,5) ';
            break;
        case 'dangbei': //当贝
            $tmp_where .= ' AND A.gv_channel NOT IN(2,4,5) ';
            break;
        case 'aijia': //爱家
            $tmp_where .= ' AND A.gv_channel NOT IN(2,3,5) ';
            break;
        case 'shafa': //沙发
            $tmp_where .= ' AND A.gv_channel NOT IN(2,3,4) ';
            break;
        default :
            $tmp_where .= '';
            break;
    }
}

//查数据条数
$sql_count = "SELECT count(*) as num
              FROM mzw_game_version A
              LEFT JOIN mzw_game_m_a_relation B ON A.g_id = B.g_id AND (A.`gv_id` = B.`gv_id` OR B.`gv_id` =0)
              WHERE (FIND_IN_SET(1,A.gv_client_type)>0 OR FIND_IN_SET(3,A.gv_client_type)>0) AND B.ga_id = ".$tmp_ga_id.$tmp_where;
$data_count = $conn->get_one($sql_count);

//定义回转的默认参数
$returnArr = array(
    'total' => $data_count['num'], //数据总数
    'pagecount' => $mydata['pagesize'], //每页显示数据
    'pagenum' => $mydata['pagenum'], //当前页
    'albumbg' => '', //专区背景大图
    'rows' => array(), //数据数组
    'error' => NULL, //错误信息
    'update' => time() //请求接口时间
);

//获取专区背景大图
$album_sql = "SELECT `ga_bg` FROM `mzw_game_mobile_area` WHERE `ga_id` = ".$tmp_ga_id;
$album_data = $conn->get_one($album_sql);
$returnArr['albumbg'] = (isset($album_data) && !empty($album_data['ga_bg'])) ? (LOCAL_URL_DOWN_IMG.$album_data['ga_bg']) : '';

//查专区的信息
$sql = "SELECT A.g_id as gid,A.gv_id as appid,A.gv_type_id as tid,A.gv_title as title,A.gv_version_name as version,A.gv_update_time as updatetime,A.gv_description as description,
		A.gv_publish_time as published,A.gv_package_name as packagename,A.gv_ico_key as icon,A.gv_down_nums as downloadscount,A.gv_version_no as versioncode,A.gv_update_time as updatetime "
        . "FROM mzw_game_version A LEFT JOIN mzw_game_m_a_relation B ON A.g_id = B.g_id AND (A.`gv_id` = B.`gv_id` OR B.`gv_id` =0) WHERE (FIND_IN_SET(1,A.gv_client_type)>0 OR FIND_IN_SET(3,A.gv_client_type)>0)
		AND B.ga_id =".$tmp_ga_id.$tmp_where." ORDER BY $tmp_order_by $limit";
$data = $conn->find($sql);

if(!empty($data)){
    foreach ($data as $val){

        //查所属分类的名称
        $tmp_sql = 'SELECT t_name_cn as name FROM mzw_game_type WHERE t_id='.$val["tid"];
        $tmp_type = $conn->get_one($tmp_sql);
        if($tmp_type){
            $category = isset($tmp_type["name"]) ? $tmp_type["name"] : '';
        }

        //获取游戏ICO地址（190 * 190）
        $tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type FROM mzw_game_screenshot A
			                LEFT JOIN mzw_img_path B ON A.img_key=B.img_key
                            WHERE A.gv_id = '.$val['appid'].' AND A.type = 7 AND B.size_id = 37 ORDER BY B.id DESC';
        $tmp_game_ico_arr = $conn->get_one($tmp_sql);
        $iconpath = isset($tmp_game_ico_arr['path']) ? (LOCAL_URL_DOWN_IMG.$tmp_game_ico_arr['path']) : '';

        //没有190 * 190 找512 * 512
        if(empty($iconpath)){
            $tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type FROM mzw_game_screenshot A
                        LEFT JOIN mzw_img_path B ON A.img_key=B.img_key
                        WHERE A.gv_id = '.$val['appid'].' AND A.type = 7 AND B.size_id = 0 ORDER BY B.id DESC';
            $tmp_game_ico_arr = $conn->get_one($tmp_sql);
            $iconpath = isset($tmp_game_ico_arr['path']) ? (LOCAL_URL_DOWN_IMG.$tmp_game_ico_arr['path']) : '';
        }

        //如果没找到512 * 512的ICO图标，则去100*100的ICO图标
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
        $where_str = ' WHERE `mgd_client_type` != 2 AND `gv_id` = '.$val['appid'].' AND `mgd_package_type` != 2 ';
        if(isset($game_type_data['gv_id']) && !empty($game_type_data['gv_id'])){ //如果是NES游戏
            $order_str = " ORDER BY `mgd_id` ASC ";
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
                    WHERE `client_type` != 2 AND `gv_id` = '.intval($val['appid']).' AND `mgd_id` = '.intval($tmp_downlist["mgd_id"]);
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

        //数据赋值
        $json = array(
            'appid' => $val["appid"],//游戏版本ID
            'title' => $val['title'],//游戏名字
            'version' => $val['version'], //游戏版本名称
            'description' => $val['description'], //游戏描述
            'packagename' => $val['packagename'], //游戏包名
            'updatetime' => date('Y-m-d',strtotime($val['updatetime'])), //游戏更新时间
            'iconpath' => $iconpath, //游戏图标
            'filetype' => $filetype, //文件类型
            'size' => intval($size),//文件大小
            'category' => $category,//游戏分类名
            'downloadscount' => $val['downloadscount'], //下载总数
            'unzipsize' => $tmp_downlist['unzip_size'],//解压后的大小
            'adaptation' => intval($adaptation), //1GPK包，-1合适的APK包，-2没有合适的APK及GPK包
            'signature' => isset($tmp_downlist["mgd_apk_agsin"]) ? $tmp_downlist["mgd_apk_agsin"] : '', //游戏签名
            'downloadPaths' => $downloadPaths //下载地址
        );
        $returnArr['rows'][]=$json;
        unset($downloadPaths);
    }
}

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
    exit(responseJson($returnArr,false));
}

exit(responseJson($returnArr,true));