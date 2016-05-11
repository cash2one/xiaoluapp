<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取指定游戏分类下面的指定条数的游戏（进行了GPU适配的）,并加密JSON内容进行输出返回
 * @file: game_category_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-06-08  16:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['key'] = get_param('key'); //验证key

//验证key是否正确
verify_key_kyx($mydata['key']);

$mydata['gpu'] = get_param('gpu');//CPU型号，字符串
$mydata['model'] = get_param('model'); //型号（在用）
$mydata['operation'] = intval(get_param('operation'));//操作类型 全部=1   手柄=2  遥控=3  空鼠=4
$mydata['language'] = intval(get_param('language'));//游戏语言 全部=1   中文=2   英文=3
$mydata['size'] = intval(get_param('size'));//游戏大小 全部=1  100M以下=2 300M以下=3 500M以下=4 1G以下=5 1G以上=6）
$mydata['datainstallsdcard'] = intval(get_param('datainstallsdcard'));//是否支持外置存储卡(全部 1 支持 2 不支持 3)
$mydata['order'] = intval(get_param('order')); //排序（1：更新时间 2：热度 3：好评）
$mydata['tid'] = intval(get_param('tid')); //分类id（属性分类 -1：最新游戏 -2:大型游戏 -3：模拟器游戏 -5：多人游戏 -6：遥控器游戏）
$mydata['s_tid'] = intval(get_param('s_tid')); //二级分类
$mydata['pagenum'] = intval(get_param('pagenum')); //当前页
$mydata['pagenum'] = empty($mydata['pagenum']) ? 1 : $mydata['pagenum'];
$mydata['pagesize'] = intval(get_param('pagesize'));//每页大小
$mydata['pagesize'] = empty($mydata['pagesize']) ? 12 : $mydata['pagesize'];

$mydata['kyxversion'] = intval(get_param('kyxversion'));//客户端版本
//设置的内置存贮卡是否挂载（1是未挂载，0是已经挂载)
$mydata['insdcardunmount'] = intval(get_param('insdcardunmount'));

//===========begin适配GPU

//查找适配的GPU
$tmp_find_gpu_id = " ( FIND_IN_SET(0,mgd_gpu_id)>0 ";
if(!empty($mydata['gpu'])){
    $tmp_sql_gpu = "SELECT gb_id FROM mzw_mobile_gpu_brand WHERE INSTR('".$mydata['gpu']."',gb_params)>0";
    $tmp_gpu_id_arr = $conn->find($tmp_sql_gpu,'gb_id');
    if(!empty($tmp_gpu_id_arr) && is_type($tmp_gpu_id_arr,'Array')){
        foreach ($tmp_gpu_id_arr as $tmp_gpu_id_val){
            $tmp_find_gpu_id .= " OR FIND_IN_SET(".$tmp_gpu_id_val["gb_id"].",mgd_gpu_id)>0 ";
        }
    }
}
$tmp_find_gpu_id .= " ) ";

//查找排除的型号mydata['model']
$tmp_find_model_id = "";
if(!is_empty($mydata['model'])){
    $tmp_sql_model_dis = "SELECT `model_id` FROM `mzw_mobile_model` WHERE INSTR('".$mydata['model']."',model_params)>0";
    $tmp_model_id_arr = $conn->find($tmp_sql_model_dis,'model_id');
    if(!is_empty($tmp_model_id_arr)){//如果有找到对应的型号
        foreach ($tmp_model_id_arr as $tmp_model_id_val){
            $tmp_find_model_id .= " AND FIND_IN_SET(".$tmp_model_id_val["model_id"].",mgd_shield_mobile)<1 ";
        }
    }else{//如果没有找到对应的型号，看下是否禁止了未知型号
        $tmp_find_model_id .= " AND FIND_IN_SET(25,mgd_shield_mobile)<1 ";
    }
}

//=============end 适配ＧＰＵ

//查询条件拼接
$temp_where = ' AND (gv.gv_status=1 OR gv.gv_id=764) ';

//不显示大游戏（即不显示GPK的游戏）
if($mydata['insdcardunmount']==1){
    $temp_where .= ' AND FIND_IN_SET(23,gv.g_tags_property)<1 ';
}

//分类
if($mydata['tid'] > 0){
    $temp_where .= " AND  gv.gv_type_id = ".$mydata['tid']." ";

    //分类名称
    $tid_sql = "SELECT `t_name_cn` FROM `mzw_game_type` WHERE `t_id` = ".$mydata['tid'];
    $tid_data = $conn->find($tid_sql);
    $tid_name = $tid_data[0]['t_name_cn'];
}else{
    switch($mydata['tid']){
        case '-2': //大型游戏
            $temp_where .= " AND  FIND_IN_SET(23,gv.g_tags_property)>0 ";
            $tid_name = '大型游戏';
            break;
        case '-3': //模拟器游戏
            if(!empty($mydata['s_tid'])){
                $temp_where .= " AND  FIND_IN_SET(".$mydata['s_tid'].",gv.gv_nes_property)>0 ";
            }else{
//                $temp_where .= " AND  (gv.gv_nes_property != '' AND gv.gv_nes_property != 0 AND gv.gv_nes_property != NULL) ";
                $temp_where .= "AND (FIND_IN_SET(1,gv.gv_nes_property)>0 OR FIND_IN_SET(2,gv.gv_nes_property)>0 OR FIND_IN_SET(3,gv.gv_nes_property)>0) ";
            }
            $tid_name = '模拟器游戏';
            break;
        case '-4': //如果是 支持自定义按键的游戏分类ID(支持自定义按键的 ID为：5)客户端说写死了。
            $temp_where .=  " AND  FIND_IN_SET(5,gv.gv_app_prop)>0 ";
            $tid_name = '支持自定义按键的游戏';
            break;
        case '-5': //多人游戏
            $temp_where .= " AND  FIND_IN_SET(24,gv.g_tags_property)>0 ";
            $tid_name = '多人游戏';
            break;
        case '-6': //遥控器游戏
            switch($mydata['s_tid']){
                case 1: //普通遥控器
                    $temp_where .= " AND  FIND_IN_SET(128,gv.gv_app_prop) ";
                    break;
                case 2: //空鼠遥控器
                    $temp_where .= " AND  FIND_IN_SET(64,gv.gv_app_prop) ";
                    break;
                default :
                    $temp_where .= " AND  (FIND_IN_SET(64,gv.gv_app_prop) OR FIND_IN_SET(128,gv.gv_app_prop)) ";
            }
            $tid_name = '遥控器游戏';
            break;
        case '-7': //模拟手柄游戏
                $temp_where .=  " AND  FIND_IN_SET(6,gv.gv_app_prop)>0 ";
                $tid_name = '支持模拟手柄游戏 ';
        break;
        default:
            $temp_where .= " ";
            $tid_name = '最新游戏';
            break;
    }
}

//游戏大小
if($mydata['size']!=0 && $mydata['size']!=1){
    switch ($mydata['size']){
        case 2://100M以下
            $mydata['size'] = '<104857601  ';
            break;
        case 3://300M以下
            $mydata['size'] = '<314572801  ';
            break;
        case 4://500M以下
            $mydata['size'] = '<524288001 ';
            break;
        case 5://1G以下
            $mydata['size'] = '<1073741825 ';
            break;
        case 6://1G以上
            $mydata['size'] = '>1073741824 ';
            break;
    }
    $tmp_sql_gpu_in = 'SELECT `gv_id` FROM `mzw_game_downlist` WHERE mgd_client_type!=2 AND mgd_package_type!=2 AND '.$tmp_find_gpu_id.$tmp_find_model_id.' GROUP BY gv_id HAVING max(mgd_game_size)'.$mydata['size'];
}else{
    $tmp_sql_gpu_in = 'SELECT DISTINCT `gv_id` FROM `mzw_game_downlist` WHERE mgd_client_type!=2 AND mgd_package_type!=2 AND '.$tmp_find_gpu_id.$tmp_find_model_id;
}

//操作类型
if($mydata['operation']!=0 && $mydata['operation']!=1){
    switch ($mydata['operation']){
        case 2://支持游戏手柄
            $mydata['operation'] = 32;
            break;
        case 3://普通遥控器
            $mydata['operation'] = 64;
            break;
        case 4://空鼠遥控器
            $mydata['operation'] = 128;
        break;
        case 5://支持模拟手柄 
            $mydata['operation'] = 6;
        break;
    }
    $temp_where .= ' AND FIND_IN_SET('.$mydata['operation'].',gv.gv_app_prop)>0 ';
}

//是否支持外置存储卡(全部 1, 支持 2,不支持 3)
if($mydata['datainstallsdcard']!=0 && $mydata['datainstallsdcard']!=1){
    switch ($mydata['datainstallsdcard']){
        case 2://支持
            $temp_where .= ' AND FIND_IN_SET(512,gv.gv_app_prop)>0 ';
            break;
        case 3://不支持
            $temp_where .= ' AND FIND_IN_SET(512,gv.gv_app_prop)<1 ';
            break;
    }
}

//语言
if($mydata['language']!=0 && $mydata['language']!=1){
    switch ($mydata['language']){
        case 2://中文
            $mydata['language'] = 1;
            break;
        case 3://英文
            $mydata['language'] = 0;
            break;
    }
    $temp_where .= ' AND gv.gv_lang='.$mydata['language'];
}

//不显示大游戏（即不显示GPK的游戏）
//if($mydata['insdcardunmount']==1){
//    $temp_where .= ' AND FIND_IN_SET(23,gv.g_tags_property)<1 ';
//}

//如果不是模拟器游戏列表，去除模拟器属性
//if($mydata['tid'] != '-3'){
//    $temp_where .=  " AND (gv.gv_nes_property = '' OR gv.gv_nes_property = 0 OR gv.gv_nes_property is null) ";
//}
if($mydata['tid'] == '-1' || empty($mydata['tid'])){
    $temp_where .=  " AND (gv.gv_nes_property = '' OR gv.gv_nes_property = 0 OR gv.gv_nes_property is null) ";
}

//排序条件拼接
if($mydata['order'] == 2){ //热度（总下载量）
    $temp_order = ' ORDER BY gv.gv_down_nums DESC,gv.gv_id DESC ';
}else if($mydata['order'] == 3){ //好评（好玩次数,未用）
    $temp_order = ' ORDER BY gv.gv_good_nums DESC,gv.gv_id DESC ';
}else{ //更新时间（上架时间）
    $temp_order = ' ORDER BY gv.gv_update_time DESC,gv.gv_id DESC ';
}

//LIMIT条件
$temp_limit = " LIMIT ".($mydata['pagenum'] - 1) * $mydata['pagesize'].",".$mydata['pagesize']." ";

//查找游戏条件下游戏数组
$returnArr = array(
    'total' => 0, //数据总数
    'pagenum' => $mydata['pagenum'], //当前页
    'pagesize' => $mydata['pagesize'], //每页显示数据条数
    'pagecount' => 0, //总页数
    'id' => $mydata['tid'], //分类id
    'sid' => $mydata['s_tid'], //二级分类id
    'title' => $tid_name, //分类名称
    'sdatas' => array(), //二级分类数组
    'error'=>'',
//    'update'=>time(), //更新时间
    'rows' => array() //数据
);

//判断是否需要查询二级分类数组
if($mydata['tid'] == '-3'){ //模拟器游戏
    $s_tid_sql = "SELECT `i_id`,`i_name_cn` FROM `mzw_game_imitator` WHERE `i_status` = 1 ORDER BY `i_order_num` DESC";
    $s_tid_data = $conn->find($s_tid_sql);
    if(!empty($s_tid_data) && is_type($s_tid_data,'Array')){
        $returnArr['sdatas'][] = array(
            'sid' => 0,
            'title' => '全部'
        );
        foreach($s_tid_data as $s_tid_val){
            $returnArr['sdatas'][] = array(
                'sid' => $s_tid_val['i_id'],
                'title'=> $s_tid_val['i_name_cn']
            );
        }
    }
}
//elseif($mydata['tid'] == '-6'){
//    $returnArr['sdatas'] = array(
//        0 => array(
//            'sid' => 0,
//            'title' => '全部'
//        ),
//        1 => array(
//            'sid' => 1,
//            'title' => '普通遥控器'
//        ),
//        2 => array(
//            'sid' => 2,
//            'title' => '空鼠遥控器'
//        )
//    );
//}

/* 新加匹配 start */
//查找符合查询条件的所有mgd_id
$mgd_tmp_sql = "SELECT MAX(gd.`mgd_id`) AS `mgd_id` FROM `mzw_game_version` AS gv
            LEFT JOIN `mzw_game_downlist` AS gd ON gv.`gv_id` = gd.`gv_id`
			WHERE gd.`mgd_client_type` != 2 AND gd.`mgd_package_type` != 2
			AND (FIND_IN_SET(1,gv.gv_client_type)>0 OR FIND_IN_SET(3,gv.gv_client_type)>0) ".$temp_where
            ." GROUP BY gv.`gv_id`";
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
    $gpu_id_sql = "SELECT DISTINCT `mgd_id` FROM mzw_game_downlist
                   WHERE mgd_client_type!=2 
                   AND `mgd_id` IN (".$tmp_downlist_mgd_str.") AND ".$tmp_find_gpu_id;
    $gpu_id_data = $conn->find($gpu_id_sql);
    if(!empty($gpu_id_data)){
        foreach($gpu_id_data as $gdval){
            $gpu_id_mgd[] = $gdval['mgd_id'];
        }
        $gd_id_str = implode(',',$gpu_id_mgd);
        $temp_where .= " AND gd.mgd_id IN (".$gd_id_str.") ";
    }
}
/* 新加匹配 end */

//查数据
$sql_data = "SELECT gv.gv_id as vid, gv.gv_type_id as tid,gv.gv_title as vtitle,gv.gv_version_no as versioncode,gv.gv_version_name as version,
             gv.gv_publish_time as published, gv.gv_update_time as edittime, gv.gv_package_name as package,gv.gv_ico_key as icon,
             gv.gv_down_nums as downcount ".
             " FROM `mzw_game_version` gv LEFT JOIN `mzw_game_downlist` gd ON gv.gv_id = gd.gv_id
             WHERE (FIND_IN_SET(1,gv.gv_client_type)>0 OR FIND_IN_SET(3,gv.gv_client_type)>0) AND gv.gv_id IN($tmp_sql_gpu_in) $temp_where $temp_order $temp_limit";
$game_data = $conn->find($sql_data);

if(!empty($game_data) && is_type($game_data,'Array')){
    //查这个分类下总数据的个数
    $sql_data_count = "SELECT count(*) as num FROM `mzw_game_version` gv LEFT JOIN `mzw_game_downlist` gd ON gv.gv_id = gd.gv_id
                       WHERE (FIND_IN_SET(1,gv.gv_client_type)>0 OR FIND_IN_SET(3,gv.gv_client_type)>0) AND gv.gv_id IN($tmp_sql_gpu_in) $temp_where ";
    $game_data_count = $conn->count($sql_data_count);
    $returnArr['total'] = $game_data_count;

    //计算分类下的总页数
    $returnArr['pagecount'] = ceil($game_data_count/$mydata['pagesize']); //总页数

    //查询游戏数组
    foreach ($game_data as $game_data_val){

        //查文件大小
        $tmp_sql = 'SELECT `mgd_id`,`mgd_package_file_size` as `size`,`mgd_package_type` as `type`
                    FROM `mzw_game_downlist`
			        WHERE mgd_client_type!=2 AND `gv_id` = '.$game_data_val["vid"]." AND `mgd_package_type` != 2
			        ORDER BY `mgd_package_type` DESC,`mgd_id` DESC";
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
        $tmp_sql = 'SELECT A.`img_key`,A.`path` as `src_path`,B.`img_path` as `path`,A.`type`
                    FROM `mzw_game_screenshot` A
			        LEFT JOIN `mzw_img_path` B ON A.`img_key` = B.`img_key`
			        WHERE A.`gv_id` = '.$game_data_val["vid"]." AND (B.`size_id` = 16 OR B.`size_id` = 17)
			        ORDER BY B.`id` DESC";
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

        $data_arr[]=array(
            'appid'=>$game_data_val["vid"],//游戏版本ID
            'title'=>$game_data_val['vtitle'],//游戏名字
            'packagename'=>$game_data_val['package'],//游戏包名
            'size'=>intval($size),//文件大小
            'icontvpath' =>isset($tmp_hot_arr[$game_data_val["vid"]][3]) ? $tmp_hot_arr[$game_data_val["vid"]][3] : ''//TV游戏大图
        );
    }

    $returnArr['rows'] = $data_arr;
}

$is_bug_show = intval(get_param('bug_show'));//是否显示数据调试
if($is_bug_show==100){
   exit(responseJson($returnArr,false));
}

$str_encode = responseJson($returnArr,true);
exit($str_encode);


