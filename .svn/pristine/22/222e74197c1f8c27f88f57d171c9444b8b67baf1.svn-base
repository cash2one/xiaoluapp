<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 返回客户端首页需要显示的广告、排行榜游戏列表（进行了GPU适配的）
 * 设定要调传的广告位ID为38（客户端首页推荐广告位）
 * 设定排行榜游戏列表要调传的专区ID为25（TV首页推荐广告游戏排行榜）
 * @file:home_hotgame.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-06-05  14:21
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['key'] = get_param('key'); //验证key
$mydata['pagenum'] = intval(get_param('pagenum')); //页码
$mydata['pagesize'] = intval(get_param('pagesize')); //每页显示数据
$mydata['pagesize'] = empty($mydata['pagesize']) ? 15 : $mydata['pagesize'];  //默认10
$mydata['gpu']=get_param('gpu');//CPU型号，字符串
$mydata['kyxversion'] = intval(get_param('kyxversion'));//客户端版本

//设置的内置存贮卡是否挂载（1是未挂载，0是已经挂载)
$mydata['insdcardunmount'] = intval(get_param('insdcardunmount'));

//验证key是否正确
verify_key_kyx($mydata['key']);

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
                   WHERE mgd_client_type !=2 AND mgd_package_type!=2 AND '.$tmp_find_gpu_id;

//=============end 适配ＧＰＵ


//不显示大游戏（即不显示GPK的游戏）
$tmp_where = ' ';
//if($mydata['insdcardunmount']==1){
//	$tmp_where = ' AND FIND_IN_SET(23,gv.g_tags_property)<1 ';
//}

$orderby = " ORDER BY ad_dis_order ASC ";

//查总数据行数
$sql = "SELECT count(1) as num FROM `mzw_ad` WHERE `adp_id` = 38 AND `ad_status` = 1";
//$sql = "SELECT count(1) as num FROM `mzw_game` g,`mzw_game_version` gv WHERE gv.gv_status=1 AND g.g_id=gv.g_id  AND gv.g_id in(SELECT g_id FROM mzw_game_m_a_relation WHERE ga_id=12) AND gv.gv_id in($tmp_sql_gpu_in)".$tmp_where;
$data_count = $conn->count($sql);

$PageMax=ceil($data_count/$mydata['pagesize']); //最大页数
$mydata['pagenum'] > $PageMax && $mydata['pagenum']=$PageMax; //当前页
$mydata['pagenum'] == 0 && $mydata['pagenum'] = 1;
$ParamPage = ($mydata['pagenum'] - 1) * ($mydata['pagesize']);//查询从第几行开始查

//初始化返回数组
$returnArr=array(
    'total'=>$data_count,
    'pagecount'=>$mydata['pagesize'],
    'pagenum'=>$mydata['pagenum'],
    'rows'=>array(),
    'error'=>NULL
);


//查询对应广告位广告数据
$adp_id = 38; //定死广告位id
$comp_ad_id = 44; //客户端首页兼容广告位
$sql = "SELECT A.`ad_title`,A.`ad_a_href`,A.`ad_dis_order`,A.`ad_img_type`,B.`img_path`
        FROM `mzw_ad` A
		LEFT JOIN mzw_img_path B ON A.ad_img_key=B.img_key
		WHERE A.`adp_id` = $adp_id AND A.ad_status=1 AND (B.size_id=0 OR B.size_id is NULL) $orderby
		LIMIT ".$ParamPage.",".$mydata['pagesize'];
$data = $conn->find($sql);

if(!empty($data) && is_type($data,'Array')){
    $tmp_arr_return = array();
    foreach($data as $key => &$vval){

        //如果有排行榜类型的广告位，则获取排行榜位游戏top数据
        if($vval['ad_img_type'] == 4){
            //查询排行榜游戏专区中间表游戏信息
            $ga_id = 25; //定死专区id
            $m_area_sql = 'SELECT g_id,gv_id FROM mzw_game_m_a_relation WHERE ga_id='.$ga_id.' ORDER BY g_order DESC';
            $m_area_data = $conn->find($m_area_sql);
            $g_id_str = ''; //id存储字符串
            $gv_id_str = '';
            if(!empty($m_area_data)){
                foreach($m_area_data as $m_a_val){
                    $g_id_str .= $m_a_val['g_id'].',';
                    $gv_id_str .= $m_a_val['gv_id'].',';
                }
                $g_id_str = rtrim($g_id_str,',');
                $gv_id_str = rtrim($gv_id_str,',');
            }
            $orderby = " ORDER BY FIND_IN_SET(gv.g_id,'".$g_id_str."') ";

            //查找排行榜游戏数据
            $rank_sql = "SELECT g.g_version_nums as historyVersionCount,gv.gv_id as appid, gv.gv_type_id as tid,gv.gv_title as title,gv.gv_version_no as versioncode,gv.gv_version_name as version,
                         gv.gv_publish_time as published, gv.gv_update_time as edittime, gv.gv_package_name as packagename,gv.gv_ico_key as icon,gv.gv_down_nums as downcount
                         FROM `mzw_game` g,`mzw_game_version` gv
                         WHERE g.g_id = gv.g_id AND (FIND_IN_SET(1,gv.gv_client_type)>0 OR FIND_IN_SET(3,gv.gv_client_type)>0) AND gv.gv_status = 1 AND gv.g_id in(".$g_id_str.") AND gv.gv_id in(".$gv_id_str.") AND gv.gv_id in(".$tmp_sql_gpu_in.") $tmp_where $orderby
                         LIMIT 5";
            $rank_data = $conn->find($rank_sql);

            if(!empty($rank_data) && is_type($rank_data,'Array')){
                foreach($rank_data as $rkey => $val){
                    //查文件大小
                    $tmp_sql = 'SELECT mgd_package_file_size as `size`,mgd_package_type as `type`,`mgd_package_type`
                                FROM mzw_game_downlist
			                    WHERE mgd_client_type!=2 AND gv_id='.$val["appid"]." AND mgd_package_type!=2
			                    ORDER BY mgd_package_type DESC,mgd_id DESC";
                    $tmp_downlist = $conn->find($tmp_sql);
                    if($tmp_downlist){
                        foreach ($tmp_downlist as $val_downlist ){
                            if($val_downlist["mgd_package_type"]==1){//如果是GPK文件
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

                    //游戏ICO图片
                    $tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
				            LEFT JOIN mzw_img_size B ON A.size_id = B.id
				            WHERE A.img_key = '".$val["icon"]."' AND B.width=100 AND B.height=100 AND A.status = 1
				            ORDER BY A.size_id";
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

                    //组装数组
                    $temp_rank_data[$rkey] = array(
                        'appid'=>intval($val["appid"]),//游戏版本ID（game_version 的ID）
                        'title'=>$val["title"],//游戏版本标题
                        'filetype'=>$filetype,//文件类型（APK 或者是 GPK）
                        'size'=>intval($size),//文件大小
                        'category'=>isset($category) ? $category : '',//游戏分类名称
                        'packagename'=>$val["packagename"],//游戏包名
                        'version'=>$val["version"],//游戏版本名
                        'versioncode'=>intval($val["versioncode"]),//游戏版本号
                        'updatetime'=>$val["edittime"],//更新时间
                        'downloadscount'=>intval($val["downcount"]),//下载次数
                        'iconpath'=>isset($tmp_game_ico) ? $tmp_game_ico : '',//ICO图片
                        'icontvpath' => isset($tmp_hot_arr[$val["appid"]][3][0]) ? $tmp_hot_arr[$val["appid"]][3][0] : '',//TV游戏大图
                        'corver'=>isset($tmp_hot_arr[$val["appid"]][2][0]) ? $tmp_hot_arr[$val["appid"]][2][0] : '',//专题大图
                        'historyVersionCount'=>$online ? 0:intval($val["historyVersionCount"] -1),//历史版本个数
                        'indexflags'=>0,
                        'updated'=>false,
                        'newest'=>(time()- strtotime($val['published'])<3600*24*3)?1:0, //发布时间小于三天intval($val["historyVersionCount"])==1&&(time()-intval($val["published"])<3600*24*7),//是否本周游戏
                        'online'=>$online,//是否网络游戏
                        'icontvindex' =>isset($tmp_hot_arr[$val["appid"]][5][0]) ? $tmp_hot_arr[$val["appid"]][5][0] : '',//首页大图
                        'icontvdetail' =>isset($tmp_hot_arr[$val["appid"]][6][0]) ? $tmp_hot_arr[$val["appid"]][6][0] : ''//详情页大图
                    );
                }
            }
        }

        //检测版本是否为老版本（为老版本，则用兼容广告位替换）
        preg_match('/type=(.*?),/', $vval['ad_a_href'],$type_id);
        if($mydata['kyxversion'] < 202 && isset($type_id[1]) && $type_id[1] == 2){
            $sql = "SELECT A.`ad_title`,A.`ad_a_href`,A.`ad_dis_order`,A.`ad_img_type`,B.`img_path`
                    FROM `mzw_ad` A
                    LEFT JOIN mzw_img_path B ON A.ad_img_key=B.img_key
                    WHERE A.`adp_id` = $comp_ad_id AND A.ad_status = 1 AND (B.size_id=0 OR B.size_id is NULL)
                    AND A.`ad_img_type` = ".$vval['ad_img_type']." AND A.`ad_dis_order` = ".$vval['ad_dis_order'];
            $comp_data = $conn->get_one($sql);
            if(!empty($comp_data)){
                $vval['ad_title'] = $comp_data['ad_title'];
                $vval['ad_a_href'] = $comp_data['ad_a_href'];
                $vval['img_path'] = $comp_data['img_path'];
            }
        }

        $json_data = array(
            'title' => $vval['ad_title'], //广告标题
            'href' => $vval['ad_a_href'], //链接地址
            'order' => $vval['ad_dis_order'], //广告位置
            'type' => $vval['ad_img_type'], //广告位置类型（1：大图位 2：中图位 3：小图位 4：排行榜位）
            'img' => empty($vval['img_path']) ? '' : LOCAL_URL_DOWN_IMG.$vval['img_path'], //广告图片（TV首页图片）
            'rankarr' => (isset($temp_rank_data) && $vval['ad_img_type'] == 4) ? $temp_rank_data : array() //游戏排行榜数据
        );

        $tmp_arr_return[] = $json_data;
    }
}

$returnArr['rows'] = $tmp_arr_return;
$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试

if($is_bug_show==100){
    exit(responseJson($returnArr,false));
}

$str_encode = responseJson($returnArr,true);
exit($str_encode);


