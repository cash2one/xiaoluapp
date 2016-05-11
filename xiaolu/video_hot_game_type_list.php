<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取视频热门游戏、热门分类列表
 * @file: video_hot_game_type_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-23  16:44
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
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

//今日日期
$today = strtotime(date('Ymd',time()));

$data_key = 'xl_hot_game_type_list_data';
$returnArr = $mem_obj->get($data_key);
if($returnArr === false){

    //定义回转的默认参数
    $returnArr = array(
        'rows' => array() //数据数组
    );

    //获取视频数前10的游戏列表
    $sql = "SELECT A.`id`,A.`gi_name`,A.`gi_logo`,COUNT(1) AS num,COUNT(if(B.`in_date` > ".$today.",true,null)) AS newnum
            FROM `video_game_info` AS A LEFT JOIN `video_video_list` AS B ON A.`id` = B.`vvl_game_id`
            WHERE A.id IN(23,16293,2,3,88,15,1,4) AND A.`gi_isshow` = 1 AND B.`va_isshow` = 1
            GROUP BY A.`id` ORDER BY `num` DESC LIMIT 8";
    $data = $conn->find($sql);

    if(!empty($data)){
        $temp_arr = array();
        foreach($data as $val){
            $temp_arr[] = array(
                'id' => intval($val['id']), //关联id
                'title' => $val['gi_name'], //关联标题
                'imgurl' => empty($val['gi_logo']) ? ('http://img.kuaiyouxi.com/game/2016/03/07/game.png') : (LOCAL_URL_DOWN_IMG.$val['gi_logo']), //游戏图片
                'type' => 1, //关联类型（1：游戏 2：游戏分类）
                'videonum' => intval($val['num']), //视频总数
                'newnum' => intval($val['newnum']) //今日新增
            );
        }
        $returnArr['rows'][] = array(
            'title' => '热门游戏',
            'type' => 1, //类型（1：热门游戏 2：热门分类）
            'row' => $temp_arr
        );
    }

    //获取所有有视频数的游戏列表
    $sql = "SELECT A.`id`,COUNT(1) AS num,COUNT(if(B.`in_date` > ".$today.",true,null)) AS newnum
            FROM `video_game_info` AS A LEFT JOIN `video_video_list` AS B ON A.`id` = B.`vvl_game_id`
            WHERE A.`gi_isshow` = 1 AND B.`va_isshow` = 1 GROUP BY A.`id` ORDER BY `num` DESC";
    $all_game_data = $conn->find($sql);

    //获取所有分类
    $all_type_game = array();
    $sql = "SELECT A.`t_id`,A.`t_name_cn`,A.`t_logo`,B.`id` FROM `video_game_type` AS A LEFT JOIN `video_game_info` AS B ON A.`t_id` = B.`gi_type_id`
            WHERE A.`t_status` = 1 AND B.`gi_isshow` = 1";
    $data = $conn->find($sql);
    if(!empty($data)){
        foreach($data as $val){
            $all_type_game[$val['t_id']]['id'] = intval($val['t_id']);
            $all_type_game[$val['t_id']]['title'] = $val['t_name_cn'];
            $all_type_game[$val['t_id']]['imgurl'] = empty($val['t_logo']) ? '' : (LOCAL_URL_DOWN_IMG.$val['t_logo']);
            $all_type_game[$val['t_id']]['type'] = 2;
            $all_type_game[$val['t_id']]['videonum'] = 0;
            $all_type_game[$val['t_id']]['newnum'] = 0;
            $all_type_game[$val['t_id']]['row'][] = intval($val['id']);
        }
    }

    //判断在游戏分类内的游戏数据
    foreach($all_game_data as $val){
        foreach($all_type_game as $key => $vval){
            if(in_array($val['id'],$vval['row'])){
                $all_type_game[$key]['videonum'] += intval($val['num']);
                $all_type_game[$key]['newnum'] += intval($val['newnum']);
            }
        }
    }
    unset($all_game_data);

    if(!empty($all_type_game)){
        foreach($all_type_game as $key => $val){
            unset($all_type_game[$key]['row']);
        }
    }
    $all_type_game = data_sort($all_type_game,'videonum','desc');
    $returnArr['rows'][] = array(
        'title' => '游戏分类',
        'type' => 2, //类型（1：热门游戏 2：热门分类）
        'row' => $all_type_game
    );

    $mem_obj->set($data_key,$returnArr,3600);
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);
