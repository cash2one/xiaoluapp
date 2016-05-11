<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 初始化用户推荐视频数据
 * @file: video_index_nav.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-22  10:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['navs'] = get_param('navs'); //用户选择导航json串
$mydata['packages'] = get_param('packages'); //用户本地包名json串
$mydata['uid'] = intval(get_param('uid')); //用户id
$mydata['mac'] = get_param('mac'); //用户mac地址
$mydata['imei'] = get_param('imei'); //用户imei地址
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//已选导航信息
$navs = json_decode(stripslashes($mydata['navs']),true);
$packages = json_decode(stripslashes($mydata['packages']),true);

//获取默认添加的新游预告专区
$default = array();
$sql = "SELECT `rela_id`,`title` FROM `video_default_nav_info` WHERE `nav_type` = 3 AND `pos_type` = 3 AND `status` = 1";
$area = $conn->find($sql);
if(!empty($area)){
    foreach($area as $aval){
        $default[] = array(
            'id' => intval($aval['rela_id']),
            'title' => $aval['title'],
            'type' => 3
        );
    }
}

//添加最新导航（采用专区标识）
$default[] = array(
    'id' => -1,
    'title' => '最新',
    'type' => 3
);

//添加原创原创（采用专区标识）
$default[] = array(
    'id' => -2,
    'title' => '原创',
    'type' => 3
);

$navs = array_merge($navs,$default);

$check_where = '';
if(!empty($mydata['mac']) && $mydata['mac'] <> '00:00:00:00:00:00'){
    $check_where .= " AND `mac` = '".$mydata['mac']."'";
}
if(!empty($mydata['imei'])){
    $check_where .= " AND `imei` = '".$mydata['imei']."'";
}

//检查用户是否已经存在导航信息
$sql = "SELECT `id` FROM `video_user_nav_info` WHERE 1 ".$check_where;
$check = $conn->find($sql);
if(isset($check[0]['id']) && !empty($check[0]['id'])){ //存在，更新
    $update_arr = array(
        'id' => intval($check[0]['id']),
        'nav_json' => json_encode($navs,JSON_UNESCAPED_UNICODE),
        'user_packages' => json_encode($packages,JSON_UNESCAPED_UNICODE),
        'update_time' => time()
    );
    $conn->update('video_user_nav_info',$update_arr);
}else{ //不存在，添加
    $save_arr = array(
        'uid' => 0,
        'mac' => $mydata['mac'],
        'imei' => $mydata['imei'],
        'nav_json' => json_encode($navs,JSON_UNESCAPED_UNICODE),
        'user_packages' => json_encode($packages,JSON_UNESCAPED_UNICODE),
        'create_time' => time()
    );
    $conn->save('video_user_nav_info',$save_arr);
}

//计算用户推荐表（mac+imei）
$crc_str = $mydata['mac'].$mydata['imei'];
$crc_num = abs(crc32($crc_str))%100;

//数据表名
$table_name = 'video_recom_video_list_'.$crc_num;

//清除已有的数据
$sql = "DELETE FROM `".$table_name."` WHERE 1 ".$check_where;
$conn->query($sql);

//检查推荐表是否创建，没有则创建
$str_check_sql = "CREATE TABLE IF NOT EXISTS `".$table_name."` (
                      `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
                      `in_date` int(11) NOT NULL DEFAULT '0' COMMENT '添加日期',
                      `v_id` int(11) NOT NULL DEFAULT '0' COMMENT '视频id',
                      `uid` int(11) NOT NULL DEFAULT '0' COMMENT '关联用户id',
                      `mac` varchar(32) NOT NULL DEFAULT '' COMMENT '关联用户mac地址',
                      `imei` varchar(20) NOT NULL DEFAULT '' COMMENT '关联用户imei串',
                      `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '阅读状态（1：未读 2：已读）',
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户视频推荐表';";
$conn->query($str_check_sql);

//生成初始化数据（规则未定，先取各类top50）
$temp_arr = array();
if(!empty($navs)){
    foreach($navs as $val){

        //查询条件选择
        if($val['type'] == 3){ //专区
            if($val['id'] > 0){
                $sql = "SELECT DISTINCT B.`id` AS v_id FROM `video_area_video_info` AS A RIGHT JOIN `video_video_list` AS B ON B.`id` = A.`vvl_id`
                        WHERE B.`va_isshow` = 1 AND A.`va_id` = ".intval($val['id'])." ORDER BY B.`vvl_video_score` DESC LIMIT 5";
                $data = $conn->find($sql);
            }
        }else{
            $tag_table_name = 'video_gt_recom_video_list';
            if($val['type'] == 1){
                $where = ' A.`game_id` = '.intval($val['id']);
            }elseif($val['type'] == 2){
                $where = ' A.`type_id` = '.intval($val['id']);
            }elseif($val['type'] == 4){
                $where = ' A.`t_id` = '.intval($val['id']);
                $tag_crc_num = abs(crc32($val['id']))%100;
                $tag_table_name = 'video_tag_recom_video_list_'.$tag_crc_num;
            }

            //获取数据
            $sql = "SELECT DISTINCT A.`v_id` FROM `".$tag_table_name."` AS A RIGHT JOIN `video_video_list` AS B ON A.`v_id` = B.`id`
                    WHERE ".$where." AND B.`va_isshow` = 1 ORDER BY B.`vvl_video_score` DESC LIMIT 100";
            echo $sql;
            $data = $conn->find($sql);
        }

        if(!empty($data)){
            foreach($data as $vval){
                $temp_arr[] = intval($vval['v_id']);
            }
        }
    }
}

//视频数据去重，打乱
$temp_arr = array_filter(array_unique($temp_arr));
shuffle($temp_arr);

//数据入库
if(!empty($temp_arr)){
    $str_sql = "insert into ".$table_name."(`in_date`, `v_id`, `uid`, `mac`, `imei`,`status`)values";

    $str_sql_2 = "";
    foreach($temp_arr as $val){
        $str_sql_2 .= "(";

        $tmp_sql_val = date('Ymd',time());//日期
        $tmp_sql_val .= ",".intval($val);//视频id
        $tmp_sql_val .= ",".$mydata['uid'];//用户id
        $tmp_sql_val .= ",'".$mydata['mac']."'";//用户mac
        $tmp_sql_val .= ",'".$mydata['imei']."'";//用户imei
        $tmp_sql_val .= ",1";//阅读状态

        $str_sql_2 .= $tmp_sql_val."),";
    }

    $tmp_sql_3 = $str_sql.substr($str_sql_2,0,strlen($str_sql_2)-1);
    echo $tmp_sql_3;
    $conn->query($tmp_sql_3);
}

$returnArr = array('code'=>200,'area' => $default,'msg'=>'初始化成功');
$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);