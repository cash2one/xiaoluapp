<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取视频所有游戏列表
 * @file: video_all_game_list.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-09-23  16:44
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

ini_set('memory_limit','1024M');

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

//定义回转的默认参数
$returnArr = array(
    'rows' => array() //数据数组
);

//获取视频所有游戏列表
$data_key = "xl_all_game_list";
$data = $mem_obj->get($data_key);
if($data === false){
    $sql = "SELECT A.`id`,A.`gi_name`,A.`gi_pingyin`,A.`gi_logo`,COUNT(1) AS num FROM `video_game_info` AS A
            LEFT JOIN `video_video_list` AS B ON A.`id` = B.`vvl_game_id` WHERE A.`gi_isshow` = 1 AND B.`va_isshow` = 1
            GROUP BY A.`id` HAVING num > 500";
    $data = $conn->find($sql);
    $mem_obj->set($data_key,$data,1800);
}
if(!empty($data)){
    foreach($data as $val){

        //兼容拼音为空的情况下生成拼音
        $pinyin = ucfirst($val['gi_pingyin']);
        $name = $val['gi_name'];
        if(empty($pinyin) && !empty($val['gi_name'])){
            $pinyin_temp = '';
            if(preg_match('/[0-9A-Za-z]{1,}/', $val['gi_name'],$match)){
                $pinyin_temp = $match[0];
                $name = str_replace($pinyin_temp,'org',$val['gi_name']);
                $name = strtolower($name);
            }

            $pinyin = pinyin($name);
            $pinyin = str_replace('org',$pinyin_temp,$pinyin);
            $pinyin = ucfirst($pinyin);

            //更新拼音
            $sql = "UPDATE `video_game_info` SET `gi_pingyin`='{$pinyin}' WHERE `id` = ".intval($val['id']);
            $res = $conn->query($sql);
        }

        $returnArr['rows'][] = array(
            'id' => intval($val['id']), //关联id
            'title' => trim($val['gi_name']), //关联标题
            'imgurl' => empty($val['gi_logo']) ? ('http://img.kuaiyouxi.com/game/2016/03/07/game.png') : (LOCAL_URL_DOWN_IMG.$val['gi_logo']), //游戏图片
            'type' => 1, //关联类型（1：游戏 2：游戏分类）
            'pingyin' => $pinyin //游戏拼音
        );
    }
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);
