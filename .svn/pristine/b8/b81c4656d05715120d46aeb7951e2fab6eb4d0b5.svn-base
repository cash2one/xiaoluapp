<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取用户正在玩游戏列表
 * @file: video_user_play_game_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-23  16:44
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['uid'] = intval(get_param('uid')); //用户id
$mydata['mac'] = get_param('mac');//用户mac地址
$mydata['imei'] = get_param('imei');//用户imei地址
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

$mem_obj = new kyx_memcache();

$check_where = '';
if(!empty($mydata['mac']) && $mydata['mac'] <> '00:00:00:00:00:00'){
    $check_where .= " AND `mac` = '".$mydata['mac']."'";
}
if(!empty($mydata['imei'])){
    $check_where .= " AND `imei` = '".$mydata['imei']."'";
}

//今日日期
$today = strtotime(date('Ymd',time()));

$data_key = md5('xl_user_play_game_list_data_'.$mydata['uid'].$mydata['mac'].$mydata['imei']);
$returnArr = $mem_obj->get($data_key);
if($returnArr === false){

    //定义回转的默认参数
    $returnArr = array(
        'rows' => array() //数据数组
    );

    //获取用户正在玩的游戏列表
    $sql = "SELECT `nav_now_game` FROM `video_user_nav_info` WHERE 1 ".$check_where;
    $data = $conn->find($sql);
    if(isset($data[0]['nav_now_game']) && !empty($data[0]['nav_now_game'])){
        $nav_arr = json_decode($data[0]['nav_now_game'],true);
        $id_arr = array();
        foreach($nav_arr as $val){
            $id_arr[] = intval($val['id']);
        }

        //获取正在玩的游戏相关信息
        if(!empty($id_arr)){
            $id_str = implode(',',$id_arr);
            $sql = "SELECT A.`id`,A.`gi_name`,A.`gi_logo`,COUNT(B.`id`) AS num FROM `video_video_list` AS B
                    LEFT JOIN `video_game_info` AS A ON A.`id` = B.`vvl_game_id`
                    WHERE B.vvl_game_id IN(".$id_str.") AND A.`gi_isshow` = 1 AND B.`va_isshow` = 1
                    GROUP BY B.`vvl_game_id` ORDER BY `num` DESC";
            $data = $conn->find($sql);
            if(!empty($data)){
                foreach($data as $dval){
                    $returnArr['rows'][] = array(
                        'id' => intval($dval['id']), //关联id
                        'title' => $dval['gi_name'], //关联标题
                        'imgurl' => empty($dval['gi_logo']) ? ('http://img.kuaiyouxi.com/game/2016/03/07/game.png') : (LOCAL_URL_DOWN_IMG.$dval['gi_logo']), //游戏图片
                        'type' => 1, //关联类型（1：游戏 2：游戏分类）
                        'videonum' => intval($dval['num']) //视频总数
                    );
                }
            }
        }
    }

    $mem_obj->set($data_key,$returnArr,300);
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);
