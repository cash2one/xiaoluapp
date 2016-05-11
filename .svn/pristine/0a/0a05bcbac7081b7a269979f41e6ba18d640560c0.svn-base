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

//本地包名
$packages = json_decode(stripslashes($mydata['packages']),true);

//匹配本机游戏
$where = '';
$now_play_game = array();
$check_arr = array();
if(!empty($packages)){
    $where .= "(";
    foreach($packages as $val){
        $where .= " INSTR('".$val."',gi_packname) > 0 OR";
    }
    $where = rtrim($where,'OR');
    $where .= ")";

    $sql = "SELECT `id`,`gi_name`,`gi_type`,`gi_type_id`,`gi_game_id` FROM `mzw_game_package_info` WHERE 1 AND ".$where;
    $game_data = $conn->find($sql);

    if(!empty($game_data)){
        $temp_ids_arr = array();
        foreach($game_data as $val){
            if(!empty($val['gi_game_id'])){
                $temp_ids_arr[] = intval($val['gi_game_id']);
            }else{
                //匹配到没有视频的游戏
                $sql = "SELECT `pid` FROM `video_package_match_count` WHERE `pid` = ".intval($val['id'])." LIMIT 1";
                $check = $conn->find($sql);
                if(isset($check[0]['pid']) && !empty($check[0]['pid'])){
                    $sql = "UPDATE video_package_match_count SET `weight` = `weight` + 1 WHERE pid = ".intval($val['id']);
                    $conn->query($sql);
                }else{
                    $conn->save('video_package_match_count',array('pid' => intval($val['id']),'weight' => 1));
                }
            }
        }

        //获取游戏信息
        if(!empty($temp_ids_arr)){
            $temp_ids = implode(',',$temp_ids_arr);
            $sql = "SELECT `id`,`gi_name` FROM `video_game_info` WHERE `id` IN (".$temp_ids.")";
            $data = $conn->find($sql);
            if(!empty($data)){
                foreach($data as $val){
                    //正在玩的游戏
                    $now_play_game[] = array(
                        'id' => intval($val['id']), //关联id
                        'title' => $val['gi_name'],
                        'type' => 1 //类型（1：游戏 2：游戏分类 3：专区 4:标签）
                    );

                    //本机游戏检测数组
                    $check_arr[] = intval($val['id']);
                }
            }
        }
    }
}

//获取猜你喜欢游戏列表
$guess_game_arr = array(); //猜你喜欢游戏列表
$ids = empty($check_arr) ? '' : implode(',',$check_arr); //正在玩游戏列表
if(!empty($ids)){ //无正在玩游戏，匹配缺省游戏列表
    //获取正在玩游戏关联游戏标签
    $sql = "SELECT DISTINCT `vtc_tag_id` FROM `video_game_tag_mapping` WHERE `game_id` IN (".$ids.")";
    $data = $conn->find($sql);
    if(!empty($data)){
        $game_tag = array();
        foreach($data as $val){
            $game_tag[] = intval($val['vtc_tag_id']);
        }

        //获取非本地匹配到的游戏id
        if(!empty($game_tag)){
            $game_tag_str = implode(',',$game_tag);
            $temp_where = '';
            if(!empty($ids)){
                $temp_where .= " AND A.`game_id` NOT IN (".$ids.") ";
            }
            $sql = "SELECT DISTINCT A.`game_id`,B.`gi_name` FROM `video_game_tag_mapping` AS A LEFT JOIN `video_game_info` AS B ON A.`game_id` = B.`id`
                    WHERE A.`vtc_tag_id` IN (".$game_tag_str.") ".$temp_where." AND B.`gi_isshow` = 1 AND B.`gi_video_num` > 0";
            $data = $conn->find($sql);
            if(!empty($data)){
                foreach($data as $val){
                    $guess_game_arr[] = array(
                        'id' => intval($val['game_id']),
                        'title' => $val['gi_name'],
                        'type' => 1 //类型（1：游戏 2：游戏分类 3：专区 4:标签）
                    );
                }
            }
        }
    }
}

if(empty($guess_game_arr)){
    $sql = "SELECT `title`,`rela_id` FROM `video_default_nav_info` WHERE `pos_type` = 1 AND `nav_type` = 1 ORDER BY rand() LIMIT 5";
    $data = $conn->find($sql);
    if(!empty($data)){
        foreach($data as $val){
            $guess_game_arr[] = array(
                'id' => intval($val['rela_id']),
                'title' => $val['title'],
                'type' => 1 //类型（1：游戏 2：游戏分类 3：专区 4:标签）
            );
        }
    }
}

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
        'nav_json' => json_encode($guess_game_arr,JSON_UNESCAPED_UNICODE),
        'nav_now_game' => json_encode($now_play_game,JSON_UNESCAPED_UNICODE),
        'user_packages' => json_encode($packages,JSON_UNESCAPED_UNICODE),
        'update_time' => time()
    );
    $conn->update('video_user_nav_info',$update_arr);
}else{ //不存在，添加
    $save_arr = array(
        'uid' => 0,
        'mac' => $mydata['mac'],
        'imei' => $mydata['imei'],
        'nav_json' => json_encode($guess_game_arr,JSON_UNESCAPED_UNICODE),
        'nav_now_game' => json_encode($now_play_game,JSON_UNESCAPED_UNICODE),
        'user_packages' => json_encode($packages,JSON_UNESCAPED_UNICODE),
        'create_time' => time()
    );
    $conn->save('video_user_nav_info',$save_arr);
}

//计算用户推荐表（mac+imei）
$crc_str = $mydata['mac'].$mydata['imei'];
$crc_num = abs(crc32($crc_str))%100;

//数据表名
$table_name = 'video_recom_video_list_'.$crc_num; //猜你喜欢

//生成猜你喜欢初始化数据
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

//生成初始化数据
$temp_arr = array();
if(!empty($guess_game_arr)){
    $game_count = count($guess_game_arr); //游戏数
    $limit_str = intval(600/$game_count); //每个游戏获取多少个视频
    $data = array();
    foreach($guess_game_arr as $val){

        //获取数据
        $sql = "SELECT DISTINCT A.`v_id` FROM `video_gt_recom_video_list` AS A RIGHT JOIN `video_video_list` AS B ON A.`v_id` = B.`id`
                WHERE A.`game_id` = ".intval($val['id'])." AND B.`va_isshow` = 1 ORDER BY B.`vvl_video_score` DESC LIMIT ".$limit_str;
        $data = $conn->find($sql);

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
    $conn->query($tmp_sql_3);
}

//数据表名
$now_play_table_name = 'video_play_recom_video_list_'.$crc_num; //正在玩

//生成正在玩初始化数据
$sql = "DELETE FROM `".$now_play_table_name."` WHERE 1 ".$check_where;
$conn->query($sql);

//检查推荐表是否创建，没有则创建
$str_check_sql = "CREATE TABLE IF NOT EXISTS `".$now_play_table_name."` (
                      `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
                      `in_date` int(11) NOT NULL DEFAULT '0' COMMENT '添加日期',
                      `v_id` int(11) NOT NULL DEFAULT '0' COMMENT '视频id',
                      `uid` int(11) NOT NULL DEFAULT '0' COMMENT '关联用户id',
                      `mac` varchar(32) NOT NULL DEFAULT '' COMMENT '关联用户mac地址',
                      `imei` varchar(20) NOT NULL DEFAULT '' COMMENT '关联用户imei串',
                      `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '阅读状态（1：未读 2：已读）',
                      PRIMARY KEY (`id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户正在玩视频推荐表';";
$conn->query($str_check_sql);

//生成初始化数据（规则未定，先取各类top50）
$temp_arr = array();
if(!empty($now_play_game)){
    $game_count = count($now_play_game); //游戏数
    $limit_str = intval(600/$game_count); //每个游戏获取多少个视频
    $data = array();
    foreach($now_play_game as $val){

        //获取数据
        $sql = "SELECT DISTINCT A.`v_id` FROM `video_gt_recom_video_list` AS A RIGHT JOIN `video_video_list` AS B ON A.`v_id` = B.`id`
                WHERE A.`game_id` = ".intval($val['id'])." AND B.`va_isshow` = 1 ORDER BY B.`vvl_video_score` DESC LIMIT ".$limit_str;
        $data = $conn->find($sql);

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
shuffle($temp_arr);

//数据入库
$has_now_play = false;
if(!empty($temp_arr)){
    $has_now_play = true;
    $str_sql = "insert into ".$now_play_table_name."(`in_date`, `v_id`, `uid`, `mac`, `imei`,`status`)values";

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
    $conn->query($tmp_sql_3);
}

//获取默认添加的新游预告专区
$default = array();
$sql = "SELECT `rela_id`,`title` FROM `video_default_nav_info` WHERE `nav_type` = 3 AND `pos_type` = 3 AND `status` = 1 ORDER BY `sort` DESC";
$area = $conn->find($sql);
if(!empty($area)){
    $default[] = array(
        'id' => -1, //-1：跳猜你喜欢 -2：跳正在玩
        'title' => '猜你喜欢',
        'url' => URL_XIAOLU.'xiaolu/video_index_album.php',
        'type' => 5
    );
    foreach($area as $key =>$aval){
        if($key == 1){
            if($has_now_play){
                $default[] = array(
                    'id' => -2, //-1：跳猜你喜欢 -2：跳正在玩
                    'title' => '正在玩',
                    'url' => URL_XIAOLU.'xiaolu/video_index_play_album.php',
                    'type' => 5
                );
            }
        }
        $default[] = array(
            'id' => intval($aval['rela_id']),
            'title' => $aval['title'],
            'url' => URL_XIAOLU.'xiaolu/video_area_video_list.php?id='.$aval['rela_id'],
            'type' => 3
        );
    }
}

//添加最新导航（采用专区标识）
$default[] = array(
    'id' => -1,
    'title' => '最新',
    'url' => URL_XIAOLU.'xiaolu/video_area_video_list.php?id=-1',
    'type' => 3
);

//添加原创原创（采用专区标识）
$default[] = array(
    'id' => -2,
    'title' => '独家',
    'url' => URL_XIAOLU.'xiaolu/video_area_video_list.php?id=-2',
    'type' => 3
);

$returnArr = array('code'=>200,'area' => $default,'msg'=>'初始化成功');
$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);