<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 获取视频APP解说者游戏分类,并JSON内容进行输出返回
 * @file: video_game_category.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2015-10-25  16:05
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");
/*参数*/
$mydata = array();
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//定义回转的默认参数
$returnArr = array(
    'rows' => array()
);

//需要获取专辑游戏包名数组
$package_arr = array(
    2 => 'com.mojang.minecraftpe',
    12 => 'com.telltalegames.minecraft100'
);

$mem_obj = new kyx_memcache();

//视频解说游戏数据
$video_game_category_data_key = 'video_game_category_data'; //视频解说游戏数据缓存key
$returnArr = $mem_obj->get($video_game_category_data_key); //视频解说游戏数据
if($returnArr === false){
    foreach($package_arr as $key => $val){
        //根据包名获取游戏信息
        $sql = "SELECT gv_id,gv_title,gv_ico_key FROM mzw_game_version WHERE gv_status=1 AND gv_package_name = '".$val."' ORDER BY gv_id DESC LIMIT 1";
        $game_data = $conn->get_one($sql);

        //没有找到上架的游戏信息，取下架的最大游戏信息
        if(empty($game_data)){
            $sql = "SELECT gv_id,gv_title,gv_ico_key FROM mzw_game_version WHERE gv_package_name = '".$val."' ORDER BY gv_id DESC LIMIT 1";
            $game_data = $conn->get_one($sql);
        }

        if(!empty($game_data)) {
            //获取游戏ICO地址（175 * 175）
            $tmp_sql = 'SELECT A.img_key,A.path as src_path,B.img_path as path,A.type FROM mzw_game_screenshot A
                    LEFT JOIN mzw_img_path B ON A.img_key=B.img_key
                    WHERE A.gv_id = ' . $game_data['gv_id'] . ' AND A.type = 7 AND B.size_id = 22 ORDER BY B.id DESC';
            $tmp_game_ico_arr = $conn->get_one($tmp_sql);
            $iconpath = isset($tmp_game_ico_arr['path']) ? (LOCAL_URL_DOWN_IMG . $tmp_game_ico_arr['path']) : '';

            //如果没找到175*175的ICO图标，则去100*100的ICO图标
            if (empty($iconpath)) {
                $tmp_sql = "SELECT A.id,size_id,A.extension,img_path,A.status,B.width,B.height FROM mzw_img_path A
                        LEFT JOIN mzw_img_size B ON A.size_id = B.id WHERE A.img_key = '" . $game_data["gv_ico_key"]
                    . "' AND B.width=100 AND B.height=100 AND A.status = 1 ORDER BY A.size_id";
                $tmp_game_ico_arr = $conn->get_one($tmp_sql);
                if ($tmp_game_ico_arr) {
                    $iconpath = LOCAL_URL_DOWN_IMG . str_replace(LOCAL_IMG_PATH, "", $tmp_game_ico_arr["img_path"]);
                }
            }

            //入口描述
            $desc = '';
            if($key == 2){
                $desc = '我的世界我做主';
            }elseif($key == 12){
                $desc = '第一章震撼登场';
                $game_data['gv_title'] = str_replace(array('我的世界','：'),'',$game_data['gv_title']);
            }

            $returnArr['rows'][] = array(
                'gameid' => $key, //视频关联游戏id
                'gametitle' => $game_data['gv_title'], //游戏标题
                'iconpath' => $iconpath, //游戏图标
                'desc' => $desc //入口描述
            );
        }
    }
    $mem_obj->set($video_game_category_data_key,$returnArr,3600);
}

$str_encode = responseJson($returnArr,false);
exit($str_encode);





