<?php
/**
 * @copyright: @快游戏 2014
 * @description: 获取全部游戏的分类,并加密JSON内容进行输出返回
 * @file: game_category.php
 * @author: chengdongcai
 * @charset: UTF-8
 * @time: 2014-11-10  15:38
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['key'] = get_param('key'); //验证key
$mydata['insdcardunmount'] = intval(get_param('insdcardunmount')); //设置的内置存贮卡是否挂载（1是未挂载，0是已经挂载)
$request = $_SERVER['REQUEST_METHOD']; //请求方式

//key校验
$key_auth = kyx_authorize_key($mydata['key'],$request);
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

//查询分类数组
$sql = "SELECT A.`t_id` AS `tid`,A.`t_name_cn` AS `title`,A.`t_name_en`,A.`t_img_key` AS `icom`,A.`t_p_id` AS `type`,B.`img_path` AS `img`
        FROM `mzw_game_type` AS A
        LEFT JOIN `mzw_img_path` B ON A.t_img_key = B.img_key
        WHERE A.`t_status` = 1 AND (A.`t_type` = 1 OR A.`t_type` = 3) AND (B.size_id=0 OR B.size_id is NULL) ORDER BY A.`t_order_num` DESC";
$data = $conn->find($sql);

//数组初始化
$returnArr = array(
    'total' => count($data), //数据总数
    'rows'=>array() //数据数组
);

//分类映射
$type_map_sql = "SELECT `t_id`,`t_name_en` FROM `mzw_game_type` WHERE `t_status` = 1 AND (`t_type` = 1 OR `t_type` = 2)";
$type_map_data = $conn->find($type_map_sql);
$type_map = array();
if(!empty($type_map_data)){
    foreach($type_map_data as $val){
        $type_map[$val['t_name_en']] = intval($val['t_id']);
    }
}

//数据赋值
$temp_tid_arr = array();
if($data){
	foreach($data as $row){
		$json = array(
				'tid'=> (isset($row['t_name_en']) && isset($type_map[$row['t_name_en']])) ? intval($type_map[$row['t_name_en']]) : intval($row['tid']), //分类id
				'title'=>$row['title'], //分类标题
				'img'=> empty($row['img']) ? '' : LOCAL_URL_DOWN_IMG.$row['img'], //分类图标
                'type' => 0 //是否有二级分类（0：没有 1：有）
		);
        $temp_tid_arr[]=$json;
	}
}

//模拟器二级分类数组
$sdatas = array();
$s_tid_sql = "SELECT `i_id`,`i_name_cn` FROM `mzw_game_imitator` WHERE `i_status` = 1 ORDER BY `i_order_num` DESC";
$s_tid_data = $conn->find($s_tid_sql);
if(!empty($s_tid_data) && is_type($s_tid_data,'Array')){
    $sdatas[] = array(
        'sid' => 0,
        'title' => '全部'
    );
    foreach($s_tid_data as $s_tid_val){
        $sdatas[] = array(
            'sid' => intval($s_tid_val['i_id']),
            'title'=> $s_tid_val['i_name_cn']
        );
    }
}

//查最新游戏数据(-1)
$json_a = array(
    'tid' => -1,//分类ID
    'title' => '最新游戏',//分类名字
    'img' => LOCAL_URL_DOWN_IMG.'/article/2015/08/13/e63379cd3e00a07696d81147ac655c82.png', //分类手机图标
    'type' => 0, //是否有二级分类（0：没有 1：有）
    'sdatas' => array() //二级分类
);
//查大型游戏标签数据(-2)
$json_b = array(
    'tid'=>-2,//分类ID
    'title'=>'大型游戏',//分类名字
    'img'=>LOCAL_URL_DOWN_IMG.'/article/2015/08/11/fdd0de15024c2f8c23c197ac55d46608.png', //分类手机图标
    'type' => 0, //是否有二级分类（0：没有 1：有）
    'sdatas' => array() //二级分类
);
//查模拟器游戏标签数据(-3)
$json_c = array(
    'tid'=>-3,//分类ID
    'title'=>'模拟器',//分类名字
    'img'=>LOCAL_URL_DOWN_IMG.'/article/2015/08/11/5e977c917bd9748e8aa454197b442743.png', //分类手机图标
    'type' => 1, //是否有二级分类（0：没有 1：有）
    'sdatas' => $sdatas //二级分类
);
//查多人游戏游戏标签数据(-5)
$json_d = array(
    'tid'=>-5,//分类ID
    'title'=>'多人游戏',//分类名字
    'img'=>LOCAL_URL_DOWN_IMG.'/article/2015/08/11/7139bb23a67bb46217b1276fe03b31c9.png', //分类手机图标
    'type' => 0, //是否有二级分类（0：没有 1：有）
    'sdatas' => array() //二级分类
);
//查遥控器游戏游戏标签数据(-6)
$json_e = array(
    'tid'=>-6,//分类ID
    'title'=>'遥控器游戏',//分类名字
    'img'=>LOCAL_URL_DOWN_IMG.'/article/2015/08/11/568a960bec4ad9bce1612fc28b0e5531.png', //分类手机图标
    'type' => 0, //是否有二级分类（0：没有 1：有）
    'sdatas' => array() //二级分类
);

//属性类分类
$pro_arr[] =  $json_a;

//不显示大游戏（即不显示GPK的游戏）
//if($mydata['insdcardunmount']==0){
    $pro_arr[] = $json_b; //大型游戏标签数据(-2)
//}

$pro_arr[] = $json_c; //模拟器游戏 标签数据(-3)
$pro_arr[] = $json_d; //多人游戏 标签数据(-5)
//$pro_arr[] = $json_e; //遥控器游戏 标签数据(-6)

$returnArr['rows'] = array_merge($pro_arr,$temp_tid_arr);

//是否显示数据调试
$is_bug_show = intval(get_param('bug_show'));
if($is_bug_show==100){
    exit(responseJson($returnArr,false));
}
exit(responseJson($returnArr,true));
