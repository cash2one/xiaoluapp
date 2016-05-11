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
$mydata['key'] = get_param('key');
$mydata['kyxversion'] = intval(get_param('kyxversion'));//客户端版本（tvapi5之前有用到）

//验证key是否正确
verify_key_kyx($mydata['key']);

//设置的内置存贮卡是否挂载（1是未挂载，0是已经挂载)
$mydata['insdcardunmount'] = intval(get_param('insdcardunmount'));

$mydata['pagesize'] = intval(get_param('pagesize'));
$mydata['pagenum'] = intval(get_param('pagenum'));


$sql = "SELECT A.`t_id` AS `tid`,A.`t_name_cn` AS `title`,A.`t_img_key` AS `icom`,A.`t_p_id` AS `type`,B.`img_path` AS `img`
        FROM `mzw_game_type` AS A
        LEFT JOIN `mzw_img_path` B ON A.t_img_key = B.img_key
        WHERE A.`t_status` = 1 AND (A.`t_type` = 1 OR A.`t_type` = 2) AND (B.size_id=0 OR B.size_id is NULL) ORDER BY A.`t_order_num` DESC";
$data = $conn->find($sql);
$Appcount=count($data);

$returnArr = array(
    'total'=>$Appcount,
    'pagecount'=>$mydata['pagesize'],
    'pagenum'=>$mydata['pagenum'],
    'rows'=>array()
);

$temp_tid_arr = array();
if($data){
	foreach($data as $row){
		$json = array(
				'tid'=>intval($row['tid']),
				'title'=>$row['title'],
				'img'=> empty($row['img']) ? '' : LOCAL_URL_DOWN_IMG.$row['img'],
                'type' => 0 //是否有二级分类（0：没有 1：有）
		);
        $temp_tid_arr[]=$json;
	}
}

//查最新游戏数据(-1)
$json_a = array(
    'tid' => -1,//分类ID
    'title' => '最新游戏',//分类名字
    'img' => LOCAL_URL_DOWN_IMG.'/article/2015/06/19/e9569a4e0761d0358494d5c21cab5591.png', //分类手机图标
    'type' => 0 //是否有二级分类（0：没有 1：有）
);
//查大型游戏标签数据(-2)
$json_b = array(
    'tid'=>-2,//分类ID
    'title'=>'大型游戏',//分类名字
    'img'=>LOCAL_URL_DOWN_IMG.'/article/2015/06/11/4c7af89c9130d36c931b78a3e4b89c18.png', //分类手机图标
    'type' => 0 //是否有二级分类（0：没有 1：有）
);
//查模拟器游戏标签数据(-3)
$json_c = array(
    'tid'=>-3,//分类ID
    'title'=>'模拟器',//分类名字
    'img'=>LOCAL_URL_DOWN_IMG.'/article/2015/06/11/819c96dc5e971d458746416d99ed70f3.png', //分类手机图标
    'type' => 1 //是否有二级分类（0：没有 1：有）
);
//查多人游戏游戏标签数据(-5)
$json_d = array(
    'tid'=>-5,//分类ID
    'title'=>'多人游戏',//分类名字
    'img'=>LOCAL_URL_DOWN_IMG.'/article/2015/06/11/708a8f51fdfedc9989548245b77c5491.png', //分类手机图标
    'type' => 0 //是否有二级分类（0：没有 1：有）
);
//查遥控器游戏游戏标签数据(-6)
$json_e = array(
    'tid'=>-6,//分类ID
    'title'=>'遥控器游戏',//分类名字
    'img'=>LOCAL_URL_DOWN_IMG.'/article/2015/06/11/ebdff688c5c534b9362085681bc6503b.png', //分类手机图标
    'type' => 0 //是否有二级分类（0：没有 1：有）
);

//属性类分类
$pro_arr[] =  $json_a;

//不显示大游戏（即不显示GPK的游戏）
if($mydata['insdcardunmount']==0){
    $pro_arr[] = $json_b; //大型游戏标签数据(-2)
}

$pro_arr[] = $json_c; //模拟器游戏 标签数据(-3)
$pro_arr[] = $json_d; //多人游戏 标签数据(-5)
$pro_arr[] = $json_e; //遥控器游戏 标签数据(-6)

$returnArr['rows'] = array_merge($pro_arr,$temp_tid_arr);

$str_encode = responseJson($returnArr,true);
exit($str_encode);
