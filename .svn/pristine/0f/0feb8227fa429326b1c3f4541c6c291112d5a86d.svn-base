<?PHP
/**
 * @copyright: @快游戏 2014
 * @description: 视频点赞点踩
 * @file: video_up_down.php
 * @author: Chen Zhong
 * @charset: UTF-8
 * @time: 2016-01-19  14:43
 * @version 1.0
 **/
include_once("../config.inc.php");
include_once("../db.config.inc.php");

/*参数*/
$mydata = array();
$mydata['uid'] = intval(get_param('uid')); //用户id
$mydata['mac'] = get_param('mac');//用户mac地址
$mydata['imei'] = get_param('imei');//用户imei地址
$mydata['appid'] = intval(get_param('appid'));//视频id
$mydata['opertype'] = intval(get_param('opertype')); //操作类型 1：赞 2：踩
$mydata['opertype'] = empty($mydata['opertype']) ? 1 : $mydata['opertype'];
$mydata['encrypt'] = get_param('encrypt');
$mydata['encrypt'] = empty($mydata['encrypt']) ? true : false;
$mydata['key'] = get_param('key'); //验证key
$request = $_SERVER['REQUEST_METHOD']; //请求方式
$key_auth = kyx_authorize_key($mydata['key'],$request);

//key判断
if(empty($key_auth) || empty($mydata['key'])){
    exit('key error');
}

if(empty($mydata['appid']) || (empty($mydata['mac']) && empty($mydata['imei']))){
    exit(ResponseJson(array('code'=>10001,'msg'=>'操作失败'),$mydata['encrypt']));
}

$mem_obj = new kyx_memcache();

//查看该用户是否赞过或踩过该视频（以当前操作相反操作）
$stype = ($mydata['opertype'] == 1) ? 2 : 1;
$check_where = '';
if(!empty($mydata['mac']) && $mydata['mac'] <> '00:00:00:00:00:00'){
    $check_where .= " AND `mac` = '".$mydata['mac']."'";
}
if(!empty($mydata['imei'])){
    $check_where .= " AND `imei` = '".$mydata['imei']."'";
}
$sql = "SELECT `id` FROM `video_up_down` WHERE `v_id` = ".$mydata['appid']." AND `oper_type` = ".$stype.$check_where;
$check = $conn->find($sql);

//赞跟踩更新
$video_up_down_key = 'video_up_num_data_key_'.$mydata['appid'];
$video_up_down = $mem_obj->get($video_up_down_key);
if($video_up_down === false){
    $sql = "SELECT vvl_up_num,vvl_down_num FROM `video_video_list` WHERE `id` = ".$mydata['appid'];
    $video_up_down = $conn->find($sql);
}

//有则修改上一状态
if(isset($check[0]['id']) && !empty($check[0]['id'])){
    $arr = array(
        'v_id' => $mydata['appid'],
        'oper_type' => $mydata['opertype']
    );

    $res = $conn->update('video_up_down',$arr,'v_id');
    $key = ($stype == 1) ? 'vvl_up_num' : 'vvl_down_num';
    $sql_update = "UPDATE video_video_list SET ".$key." = $key - 1 WHERE id=".$mydata['appid'];
    $res = $conn->query($sql_update);

    //更新视频点赞数点踩数缓存
    if($res){
        //赞缓存更新
        if($mydata['opertype'] == 1){ //若赞
            $video_up_down[0]['vvl_up_num'] += 1;
            $video_up_down[0]['vvl_down_num'] = empty($video_up_down[0]['vvl_down_num']) ? 0 : ($video_up_down[0]['vvl_down_num'] - 1);
        }else{
            $video_up_down[0]['vvl_down_num'] += 1;
            $video_up_down[0]['vvl_up_num'] = empty($video_up_down[0]['vvl_up_num']) ? 0 : ($video_up_down[0]['vvl_up_num'] - 1);
        }

        $mem_obj->set($video_up_down_key,$video_up_down,7200);
    }

}else{

    //相同操作是否存在，防止刷反复插数据
    $sql = "SELECT `id` FROM `video_up_down` WHERE `v_id` = ".$mydata['appid']." AND `oper_type` = ".$mydata['opertype'].$check_where;
    $check = $conn->find($sql);
    if(!isset($check[0]['id']) || empty($check[0]['id'])){
        $arr = array(
            'v_id' => $mydata['appid'],
            'uid' => $mydata['uid'],
            'mac' => $mydata['mac'],
            'imei' => $mydata['imei'],
            'oper_type' => $mydata['opertype'],
            'create_time' => time()
        );

        $res = $conn->save('video_up_down',$arr);

        //更新视频点赞数点踩数缓存
        if($res){
            //赞缓存更新
            if($mydata['opertype'] == 1){ //若赞
                $video_up_down[0]['vvl_up_num'] += 1;
            }else{
                $video_up_down[0]['vvl_down_num'] += 1;
            }

            $mem_obj->set($video_up_down_key,$video_up_down,7200);
        }

    }
}

if(isset($res) && !empty($res)){
    $key = ($mydata['opertype'] == 1) ? 'vvl_up_num' : 'vvl_down_num';
    $sql_update = "UPDATE video_video_list SET ".$key." = $key + 1 WHERE id=".$mydata['appid'];
    $res = $conn->query($sql_update);
    if($res){
        $returnArr = array('code'=>200,'msg'=>'操作成功');
    }else{
        $returnArr = array('code'=>10004,'msg'=>'操作失败');
    }
}else{
    $returnArr = array('code'=>10004,'msg'=>'操作失败');
}

//重新生成该用户的点赞点踩的视频数组
$sql = "SELECT `v_id`,`oper_type` FROM `video_up_down` WHERE 1 ".$check_where;
$user_ud_data = $conn->find($sql);
if(!empty($user_ud_data)){
    $user_up_arr_key = "xl_user_up_arr_".md5($check_where);
    $user_down_arr_key = "xl_user_down_arr_".md5($check_where);
    $user_up_arr = array(); //点赞数组
    $user_down_arr = array(); //点踩数组
    foreach($user_ud_data as $val){
        if($val['oper_type'] == 1){
            $user_up_arr[] = intval($val['v_id']);
        }elseif($val['oper_type'] == 2){
            $user_down_arr[] = intval($val['v_id']);
        }
    }

    //设置缓存
    $mem_obj->set($user_up_arr_key,$user_up_arr,3600);
    $mem_obj->set($user_down_arr_key,$user_down_arr,3600);
}

$str_encode = responseJson($returnArr,$mydata['encrypt']);
exit($str_encode);





